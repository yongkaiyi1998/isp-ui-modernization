<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The ONLY class in this codebase that speaks HTTP to Google.
 *
 * Responsibilities:
 *   - Mint and cache an OAuth2 access token from the service account JSON
 *   - POST a single message to FCM HTTP v1
 *   - Retry retryable failures with exponential backoff
 *   - Classify every failure as permanent/retryable and prunable/not
 *
 * That separation is the point: swapping FCM for another provider later
 * means reimplementing send() against the same return contract and changing
 * nothing else.
 */
class Fcm_http_client
{

	/** @var CI_Controller */
	protected $CI;
	/** @var array transport config from config/push.php */
	protected $cfg = array();
	/** @var array|null parsed service account JSON (lazy) */
	protected $sa = NULL;
	/** @var string|null in-process access token, avoids re-reading cache per message */
	protected $access_token = NULL;
	/** @var int unix ts when $access_token stops being usable */
	protected $access_token_expires = 0;
	/** @var string|null last error from a configuration/credential problem */
	protected $last_error = NULL;
	/** @var array per-call transport overrides, reset on each send() */
	protected $override = array();

	// FCM error codes we care about
	protected $retryable_codes = array('UNAVAILABLE', 'INTERNAL', 'QUOTA_EXCEEDED', 'RESOURCE_EXHAUSTED');
	protected $prunable_codes  = array('UNREGISTERED', 'SENDER_ID_MISMATCH');

	public function __construct()
	{
		$this->CI = &get_instance();
		$this->CI->config->load('push', TRUE, TRUE);
		$this->cfg = $this->CI->config->item('push', 'push');

		if (! is_array($this->cfg)) {
			$this->cfg = array();
			log_message('error', '[push] config/push.php missing or malformed');
		}
	}

	// ==================================================================
	// PUBLIC API
	// ==================================================================

	/**
	 * Send one message to one device token.
	 *
	 * Never throws. Every failure path returns the same array shape so
	 * Push_service can act on it without try/catch.
	 *
	 * @param  string $token    FCM registration token
	 * @param  array  $message  FCM v1 message body, minus "token"
	 * @param  array  $override per-call transport overrides:
	 *                          max_attempts, timeout, connect_timeout.
	 *                          Used by the immediate-dispatch path, which
	 *                          wants one fast attempt rather than the
	 *                          cron's patient backoff - a payment callback
	 *                          must not sit waiting on Google.
	 * @return array {
	 *     ok        bool    delivered (HTTP 200)
	 *     http      int     last HTTP status, 0 on transport failure
	 *     code      string  FCM error code, '' on success
	 *     error     string  human-readable error, '' on success
	 *     permanent bool    do not retry this message as-is
	 *     prunable  bool    the TOKEN is dead - caller should null it out
	 *     attempts  int     how many HTTP requests were made
	 *     ms        int     total wall-clock time
	 * }
	 */
	public function send($token, array $message, array $override = array())
	{
		$started = microtime(TRUE);

		$this->override = $override;

		if ($token === NULL || $token === '') {
			return $this->result(FALSE, 0, 'EMPTY_TOKEN', 'No token supplied', TRUE, FALSE, 0, $started);
		}

		$access_token = $this->get_access_token();
		if ($access_token === NULL) {
			// Credential problem
			return $this->result(FALSE, 0, 'NO_ACCESS_TOKEN', (string) $this->last_error, FALSE, FALSE, 0, $started);
		}

		$message['token'] = $token;
		$body = json_encode(array('message' => $message));

		if ($body === FALSE) {
			return $this->result(FALSE, 0, 'ENCODE_FAILED', 'json_encode failed: ' . json_last_error_msg(), TRUE, FALSE, 0, $started);
		}

		$url          = sprintf($this->item('endpoint'), $this->item('project_id'));
		$max_attempts = max(1, (int) $this->item('max_attempts', 3));

		if (isset($override['max_attempts'])) {
			$max_attempts = max(1, (int) $override['max_attempts']);
		}
		$backoff      = (array) $this->item('retry_backoff_ms', array(500, 1000, 2000));
		$refreshed    = FALSE;
		$attempts     = 0;
		$last         = NULL;

		for ($i = 0; $i < $max_attempts; $i++) {
			$attempts++;
			$last = $this->post($url, $body, $access_token);

			if ($last['http'] === 200) {
				return $this->result(TRUE, 200, '', '', FALSE, FALSE, $attempts, $started);
			}

			$class = $this->classify($last['http'], $last['body'], $last['curl_error']);

			// 401: our access token expired early or was revoked.
			if ($last['http'] === 401 && ! $refreshed) {
				$refreshed    = TRUE;
				$access_token = $this->get_access_token(TRUE);
				if ($access_token === NULL) {
					// Retryable for the same reason as above.
					return $this->result(FALSE, 401, 'NO_ACCESS_TOKEN', (string) $this->last_error, FALSE, FALSE, $attempts, $started);
				}
				continue;
			}

			if ($class['permanent']) {
				return $this->result(FALSE, $last['http'], $class['code'], $class['error'], TRUE, $class['prunable'], $attempts, $started);
			}

			// Retryable. Sleep unless this was the final attempt.
			if ($i < $max_attempts - 1) {
				$sleep_ms = isset($backoff[$i]) ? (int) $backoff[$i] : 2000;
				usleep($sleep_ms * 1000);
			}
		}

		$class = $this->classify($last['http'], $last['body'], $last['curl_error']);
		return $this->result(FALSE, $last['http'], $class['code'], $class['error'], FALSE, FALSE, $attempts, $started);
	}

	/**
	 * Mint or read a cached OAuth2 access token.
	 *
	 * @param  bool $force skip the cache
	 * @return string|null NULL on any credential/transport failure
	 */
	public function get_access_token($force = FALSE)
	{
		$now = time();

		if (! $force && $this->access_token !== NULL && $this->access_token_expires > $now) {
			return $this->access_token;
		}

		if (! $force) {
			$cached = $this->cache_read();
			if ($cached !== NULL) {
				$this->access_token         = $cached['token'];
				$this->access_token_expires = $cached['expires_at'];
				return $this->access_token;
			}
		}

		$sa = $this->service_account();
		if ($sa === NULL) {
			return NULL;
		}

		$assertion = $this->build_assertion($sa);
		if ($assertion === NULL) {
			return NULL;
		}

		$response = $this->post(
			isset($sa['token_uri']) ? $sa['token_uri'] : $this->item('oauth_token_url'),
			http_build_query(array(
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion'  => $assertion,
			)),
			NULL,
			'application/x-www-form-urlencoded'
		);

		if ($response['http'] !== 200) {
			$this->last_error = 'OAuth token exchange failed (HTTP ' . $response['http'] . '): '
				. ($response['curl_error'] !== '' ? $response['curl_error'] : substr((string) $response['body'], 0, 300));
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		$decoded = json_decode($response['body'], TRUE);
		if (! isset($decoded['access_token'])) {
			$this->last_error = 'OAuth response contained no access_token';
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		$ttl = (int) $this->item('access_token_ttl', 3300);
		$this->access_token         = $decoded['access_token'];
		$this->access_token_expires = $now + $ttl;
		$this->cache_write($this->access_token, $this->access_token_expires);

		return $this->access_token;
	}

	/**
	 * Configuration sanity check. Used by push_test and by the admin screen
	 * so a misconfiguration surfaces as a clear message rather than a
	 * silent stream of failed sends.
	 *
	 * @return array ok => bool, errors => string[]
	 */
	public function health_check()
	{
		$errors = array();

		if (! function_exists('curl_init')) {
			$errors[] = 'php_curl extension is not loaded';
		}

		if (! function_exists('openssl_sign')) {
			$errors[] = 'php_openssl extension is not loaded';
		}

		$project_id = (string) $this->item('project_id');
		if ($project_id === '' || strpos($project_id, 'CHANGE_ME') === 0) {
			$errors[] = 'push.project_id is not set in config/push.php';
		}

		$path = (string) $this->item('service_account_path');
		if ($path === '') {
			$errors[] = 'push.service_account_path is not set';
		} elseif (! is_file($path)) {
			$errors[] = 'Service account file not found at: ' . $path;
		} elseif (! is_readable($path)) {
			$errors[] = 'Service account file is not readable by the PHP user: ' . $path;
		} else {
			$sa = $this->service_account();
			if ($sa === NULL) {
				$errors[] = (string) $this->last_error;
			} elseif (isset($sa['project_id']) && $sa['project_id'] !== $project_id) {
				$errors[] = 'push.project_id ("' . $project_id . '") does not match the service account project_id ("' . $sa['project_id'] . '")';
			}

			// Not fatal, but a credential readable by the world is a finding.
			if (DIRECTORY_SEPARATOR === '/' && ($perms = @fileperms($path)) !== FALSE && ($perms & 0044)) {
				$errors[] = 'WARNING: service account file is group/world readable (' . substr(sprintf('%o', $perms), -4) . '); chmod 600 it';
			}
		}

		return array('ok' => empty($errors), 'errors' => $errors);
	}

	/**
	 * Safe-to-log form of a token.
	 */
	public function mask($token)
	{
		$len = (int) $this->item('log_token_prefix', 12);
		return substr((string) $token, 0, $len) . '...';
	}

	public function last_error()
	{
		return $this->last_error;
	}

	// ==================================================================
	// INTERNALS
	// ==================================================================

	/**
	 * Load and validate the service account JSON. Cached per request.
	 */
	protected function service_account()
	{
		if ($this->sa !== NULL) {
			return $this->sa;
		}

		$path = (string) $this->item('service_account_path');

		if ($path === '' || ! is_file($path) || ! is_readable($path)) {
			$this->last_error = 'Service account file missing or unreadable: ' . $path;
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		$raw = @file_get_contents($path);
		if ($raw === FALSE) {
			$this->last_error = 'Could not read service account file';
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		$json = json_decode($raw, TRUE);
		unset($raw);

		if (! is_array($json)) {
			$this->last_error = 'Service account file is not valid JSON';
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		foreach (array('client_email', 'private_key') as $field) {
			if (empty($json[$field])) {
				$this->last_error = 'Service account JSON is missing "' . $field . '"';
				log_message('error', '[push] ' . $this->last_error);
				return NULL;
			}
		}

		$this->sa = $json;
		return $this->sa;
	}

	/**
	 * Build the RS256 JWT bearer assertion Google exchanges for an access token.
	 */
	protected function build_assertion(array $sa)
	{
		$now = time();
		$aud = isset($sa['token_uri']) ? $sa['token_uri'] : $this->item('oauth_token_url');

		$header = array('alg' => 'RS256', 'typ' => 'JWT');
		$claims = array(
			'iss'   => $sa['client_email'],
			'scope' => $this->item('oauth_scope'),
			'aud'   => $aud,
			'iat'   => $now,
			'exp'   => $now + 3600,
		);

		$input = $this->b64url(json_encode($header)) . '.' . $this->b64url(json_encode($claims));

		$key = @openssl_pkey_get_private($sa['private_key']);
		if ($key === FALSE) {
			$this->last_error = 'Service account private_key could not be parsed by OpenSSL';
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		$signature = '';
		$signed    = @openssl_sign($input, $signature, $key, 'sha256WithRSAEncryption');

		// PHP 8 frees keys automatically; 7.4 does not.
		if (PHP_VERSION_ID < 80000) {
			@openssl_free_key($key);
		}

		if (! $signed) {
			$this->last_error = 'openssl_sign() failed while building the JWT assertion';
			log_message('error', '[push] ' . $this->last_error);
			return NULL;
		}

		return $input . '.' . $this->b64url($signature);
	}

	/**
	 * One HTTP POST.
	 */
	protected function post($url, $body, $bearer = NULL, $content_type = 'application/json')
	{
		$headers = array(
			'Content-Type: ' . $content_type,
			'Content-Length: ' . strlen($body),
		);

		if ($bearer !== NULL) {
			$headers[] = 'Authorization: Bearer ' . $bearer;
		}

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $url,
			CURLOPT_POST           => TRUE,
			CURLOPT_POSTFIELDS     => $body,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_CONNECTTIMEOUT => (int) $this->transport('connect_timeout', 5),
			CURLOPT_TIMEOUT        => (int) $this->transport('timeout', 10),
			CURLOPT_SSL_VERIFYPEER => (bool) $this->item('verify_ssl', TRUE),
			CURLOPT_SSL_VERIFYHOST => $this->item('verify_ssl', TRUE) ? 2 : 0,
		));

		// Local-dev escape hatch for WAMP's missing CA bundle.
		$ca = (string) $this->item('ca_bundle_path');
		if ($ca !== '' && is_file($ca)) {
			curl_setopt($ch, CURLOPT_CAINFO, $ca);
		}

		$response   = curl_exec($ch);
		$http       = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);

		return array(
			'http'       => $http,
			'body'       => ($response === FALSE) ? '' : $response,
			'curl_error' => $curl_error,
		);
	}

	/**
	 * Turn an HTTP status + FCM error body into a decision.
	 */
	protected function classify($http, $body, $curl_error)
	{
		if ($curl_error !== '') {
			return array('code' => 'CURL_ERROR', 'error' => $curl_error, 'permanent' => FALSE, 'prunable' => FALSE);
		}

		$decoded = json_decode((string) $body, TRUE);
		$status  = isset($decoded['error']['status']) ? $decoded['error']['status'] : '';
		$message = isset($decoded['error']['message']) ? $decoded['error']['message'] : '';

		// FCM puts the precise reason in error.details[].errorCode
		$fcm_code = '';
		if (! empty($decoded['error']['details']) && is_array($decoded['error']['details'])) {
			foreach ($decoded['error']['details'] as $detail) {
				if (! empty($detail['errorCode'])) {
					$fcm_code = $detail['errorCode'];
					break;
				}
			}
		}

		$code  = ($fcm_code !== '') ? $fcm_code : ($status !== '' ? $status : 'HTTP_' . $http);
		$error = ($message !== '') ? $message : substr((string) $body, 0, 300);

		if (in_array($code, $this->prunable_codes, TRUE)) {
			return array('code' => $code, 'error' => $error, 'permanent' => TRUE, 'prunable' => TRUE);
		}

		if (in_array($code, $this->retryable_codes, TRUE) || $http === 429 || $http >= 500) {
			return array('code' => $code, 'error' => $error, 'permanent' => FALSE, 'prunable' => FALSE);
		}

		if ($http === 404) {
			return array('code' => ($code !== '' ? $code : 'UNREGISTERED'), 'error' => $error, 'permanent' => TRUE, 'prunable' => TRUE);
		}

		if ($http === 400) {
			// INVALID_ARGUMENT covers both "your token is malformed" and
			// "your payload is malformed".
			$token_related = (stripos($message, 'registration token') !== FALSE)
				|| (stripos($message, 'not a valid FCM registration token') !== FALSE)
				|| (preg_match('/\btoken\b/i', $message) && stripos($message, 'auth') === FALSE);

			return array('code' => ($code !== '' ? $code : 'INVALID_ARGUMENT'), 'error' => $error, 'permanent' => TRUE, 'prunable' => (bool) $token_related);
		}

		// Unknown. Stop retrying, but never delete a token.
		return array('code' => $code, 'error' => $error, 'permanent' => TRUE, 'prunable' => FALSE);
	}

	protected function result($ok, $http, $code, $error, $permanent, $prunable, $attempts, $started)
	{
		return array(
			'ok'        => (bool) $ok,
			'http'      => (int) $http,
			'code'      => (string) $code,
			'error'     => (string) $error,
			'permanent' => (bool) $permanent,
			'prunable'  => (bool) $prunable,
			'attempts'  => (int) $attempts,
			'ms'        => (int) round((microtime(TRUE) - $started) * 1000),
		);
	}

	// ------------------------------------------------------------------
	// Access token cache
	//
	// Deliberately a plain file rather than CI's cache driver: this must
	// work identically under CLI (cron) and web SAPI with no driver setup,
	// and the file lives under application/ which is never web-served.
	// The cached value is a bearer credential, so it is written 0600.
	// ------------------------------------------------------------------

	protected function cache_path()
	{
		$dir = $this->item('access_token_cache_path');

		if (empty($dir) || ! is_dir($dir) || ! is_writable($dir)) {
			$dir = APPPATH . 'cache';
		}

		if (! is_dir($dir) || ! is_writable($dir)) {
			$dir = sys_get_temp_dir();
		}

		$key = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $this->item('access_token_cache_key', 'fcm_access_token'));

		return rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $key . '.json';
	}

	protected function cache_read()
	{
		$path = $this->cache_path();

		if (! is_file($path) || ! is_readable($path)) {
			return NULL;
		}

		$data = json_decode((string) @file_get_contents($path), TRUE);

		if (! is_array($data) || empty($data['token']) || empty($data['expires_at'])) {
			return NULL;
		}

		// 60s safety margin so a token cannot expire mid-run.
		if ((int) $data['expires_at'] <= time() + 60) {
			return NULL;
		}

		return array('token' => $data['token'], 'expires_at' => (int) $data['expires_at']);
	}

	protected function cache_write($token, $expires_at)
	{
		$path = $this->cache_path();
		$ok   = @file_put_contents($path, json_encode(array('token' => $token, 'expires_at' => (int) $expires_at)), LOCK_EX);

		if ($ok === FALSE) {
			// Non-fatal: we just pay for a token exchange on every send.
			log_message('error', '[push] could not write access token cache to ' . $path);
			return;
		}

		@chmod($path, 0600);
	}

	protected function item($key, $default = NULL)
	{
		return isset($this->cfg[$key]) ? $this->cfg[$key] : $default;
	}

	/**
	 * Config value, unless this send() call overrode it.
	 */
	protected function transport($key, $default = NULL)
	{
		if (isset($this->override[$key])) {
			return $this->override[$key];
		}

		return $this->item($key, $default);
	}

	protected function b64url($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}
