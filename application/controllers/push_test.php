<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Push_test CLI-only smoke test for the FCM transport layer.
 */
class Push_test extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		//hard CLI gate
		if (! $this->input->is_cli_request() or php_sapi_name() !== 'cli') {
			show_404();
		}

		$this->load->library('fcm_http_client');
	}


	public function index()
	{
		$this->out('Transport (Step 2):');
		$this->out('  php index.php push_test health');
		$this->out('  php index.php push_test token');
		$this->out('  php index.php push_test send <fcm_token> ["Title"] ["Body"]');
		$this->out('  php index.php push_test bad_token');
		$this->out('');
		$this->out('Template / service (Steps 3-4):');
		$this->out('  php index.php push_test doctor [customer_no]  # is the deployed code current?');
		$this->out('  php index.php push_test types');
		$this->out('  php index.php push_test config');
		$this->out('  php index.php push_test stats');
		$this->out('  php index.php push_test devices <customer_no>');
		$this->out('  php index.php push_test preview <customer_no> [type]');
		$this->out('  php index.php push_test notify  <customer_no> [type]');
		$this->out('  php index.php push_test dedupe  <customer_no>          # T11');
		$this->out('  php index.php push_test seed    <customer_no> [count]  # T13 setup');
	}

	/**
	 * Configuration and credential check.
	 */
	public function health()
	{
		$this->out('=== FCM health check ===');

		$check = $this->fcm_http_client->health_check();

		if ($check['ok']) {
			$this->out('OK - configuration looks valid.');
			$this->out('Next: php index.php push_test token');
			return;
		}

		$this->out('FAILED:');
		foreach ($check['errors'] as $error) {
			$this->out('  - ' . $error);
		}
	}

	/**
	 * Mint an access token.
	 */
	public function token()
	{
		$this->out('=== OAuth2 access token ===');

		$started = microtime(TRUE);
		$token   = $this->fcm_http_client->get_access_token();
		$ms      = (int) round((microtime(TRUE) - $started) * 1000);

		if ($token === NULL) {
			$this->out('FAILED: ' . $this->fcm_http_client->last_error());
			$this->out('Run "php index.php push_test health" first.');
			return;
		}

		$this->out('OK  token=' . $this->fcm_http_client->mask($token) . '  (' . $ms . 'ms)');
		$this->out($ms < 50
			? 'That was a cache hit.'
			: 'That was a fresh exchange. Run again - it should be much faster.');
	}

	/**
	 * Send one real notification to one device.
	 *
	 * @param string $token FCM registration token from the Flutter app
	 * @param string $title
	 * @param string $body
	 */
	public function send($token = '', $title = 'Test Notification', $body = 'If you can read this, the transport layer works.')
	{
		if ($token === '') {
			$this->out('Missing token.');
			$this->out('Usage: php index.php push_test send <fcm_token> ["Title"] ["Body"]');
			return;
		}

		$this->out('=== Sending ===');
		$this->out('to: ' . $this->fcm_http_client->mask($token));

		$result = $this->fcm_http_client->send($token, array(
			'notification' => array(
				'title' => $title,
				'body'  => $body,
			),
			//all data values must be strings - FCM v1 rejects ints and bools outright with a 400 on the whole
			'data' => array(
				'type'  => 'admin_triggered',
				'route' => '/home',
				'ts'    => (string) time(),
			),
			'android' => array(
				'priority'     => 'high',
				'ttl'          => '86400s',
				'notification' => array(
					//no click_action - see the note in Push_service::build_message()
					'channel_id' => 'general',
				),
			),
			'apns' => array(
				'headers' => array('apns-priority' => '10'),
				'payload' => array('aps' => array('sound' => 'default', 'badge' => 1)),
			),
		));

		$this->print_result($result);
	}

	/**
	 * Deliberately invalid token.
	 */
	public function bad_token()
	{
		$this->out('=== Invalid token classification ===');
		$this->out('Expecting: ok=NO  permanent=YES  prunable=YES');

		$result = $this->fcm_http_client->send(
			'this-is-definitely-not-a-valid-fcm-registration-token-000000',
			array('notification' => array('title' => 'Should fail', 'body' => 'Should fail'))
		);

		$this->print_result($result);

		$this->out($result['prunable']
			? 'PASS - the classifier would prune this token.'
			: 'CHECK - not flagged prunable. Inspect the error above before wiring Step 3.');
	}


	/**
	 * Render every registered type with sample vars.
	 */
	/**
	 * Report which push features are present in the DEPLOYED code.
	 */
	public function doctor($customer_no = '')
	{
		$this->out('=== Deployed push feature check ===');
		$this->out('');

		$problems = 0;

		$structural = array(
			//step => array(label, class, method|NULL for class-exists)
			'2  transport'        => array('Fcm_http_client', 'send'),
			'4  templates'        => array('Push_template', 'render'),
			'11 scheduler model'  => array('Push_model', 'enqueue'),
			'11 claiming'         => array('Push_model', 'claim'),
			'11 stuck recovery'   => array('Push_model', 'release_stuck'),
			'12 dispatcher'       => array('Push_service', 'run'),
			'12 immediate send'   => array('Push_service', 'run_scheduler'),
			'13 status labels'    => array('Push_template', 'resolve_labels'),
			'13 label listing'    => array('Push_template', 'get_status_labels'),
			'14 lazy render'      => array('Push_service', 'copy_for_row'),
			'14 copy persistence' => array('Push_model', 'store_copy'),
			'14 internal api'     => array('api', NULL),
		);

		//load everything so the classes exist to inspect
		foreach (array('fcm_http_client', 'push_template', 'push_service') as $lib) {
			@$this->load->library($lib);
		}

		foreach (array('push_model') as $model) {
			@$this->load->model($model);
		}

		@include_once(APPPATH . 'controllers/api.php');

		foreach ($structural as $label => $target) {
			list($class, $method) = $target;

			if (! class_exists($class)) {
				$problems++;
				$this->out('  MISSING  ' . str_pad($label, 22) . $class . ' not found');
				continue;
			}

			if ($method !== NULL && ! method_exists($class, $method)) {
				$problems++;
				$this->out('  STALE    ' . str_pad($label, 22) . $class . '::' . $method . '() missing');
				continue;
			}

			$this->out('  ok       ' . $label);
		}

		$this->out('');
		$this->out('--- behavioural ---');

		if (! $defer_supported) {
			//do NOT run the live check when the capability is already known missing: the old code path would
			$this->out('  skipped  defer check - Push_service reports no defer support.');
			$this->out('             Running it would deliver a real notification to prove it.');
		} elseif ($customer_no === '') {
			$this->out('  skipped  pass a customer_no to run these:');
			$this->out('             php index.php push_test doctor C10023');
		} else {
			$problems += $this->doctor_defer($customer_no);
		}

		$this->out('');

		if ($problems === 0) {
			$this->out('PASS - the deployed code has every feature this build expects.');
			return;
		}

		$this->out('FAILED - ' . $problems . ' problem(s).');
		$this->out('');
		$this->out('STALE means the class is deployed but an older version of it.');
		$this->out('Re-upload that file. This is almost always the cause when');
		$this->out('behaviour disagrees with the source you are reading.');
	}

	/**
	 * Does dispatch() honour $opts['defer']?
	 */
	protected function doctor_defer($customer_no)
	{
		$this->load->library('push_service');
		$this->load->model('push_model');

		$r = $this->push_service->notify_customer(
			$customer_no,
			'admin_triggered',
			array(),
			array(
				'title'  => 'Doctor check',
				'body'   => 'Queued by push_test doctor. Should never be delivered.',
				'defer'  => TRUE,
			)
		);

		if (! empty($r['skipped'])) {
			$this->out('  skipped  defer check: ' . $r['skipped']);
			$this->out('             ' . $this->explain_skip($r['skipped']));
			return 0;
		}

		$scheduler_id = isset($r['scheduler_id']) ? (int) $r['scheduler_id'] : 0;
		$problems     = 0;

		if ((int) $r['sent'] > 0) {
			$problems++;
			$this->out('  STALE    15 deferred dispatch  sent=' . $r['sent'] . ' - defer was IGNORED');
			$this->out('             features() reports defer, but dispatch() did not honour it.');
			$this->out('             A notification WAS delivered by this check.');
		} elseif (empty($r['deferred'])) {
			$problems++;
			$this->out('  STALE    15 deferred dispatch  no "deferred" flag in the result');
		} else {
			$this->out('  ok       15 deferred dispatch');
		}

		//clean up whatever was queued, delivered or not
		if ($scheduler_id > 0) {
			$this->db->query("DELETE FROM push_outgoing WHERE scheduler_id = ?", array($scheduler_id));
			$this->db->query("DELETE FROM push_scheduler WHERE scheduler_id = ?", array($scheduler_id));
			$this->out('           (test row ' . $scheduler_id . ' removed)');
		}

		return $problems;
	}

	public function types()
	{
		$this->load->library('push_template');
		$this->config->load('push', TRUE, TRUE);

		$types = $this->push_template->get_types();

		if (empty($types)) {
			$this->out('FAILED: type registry is empty - check config/push_types.php');
			return;
		}

		//render EVERY type in EVERY configured language
		$cfg    = (array) $this->config->item('push', 'push');
		$map    = isset($cfg['lang_map']) ? (array) $cfg['lang_map'] : array();
		$idioms = array_values(array_unique(array_values($map)));

		if (empty($idioms)) {
			$idioms = array(isset($cfg['default_lang']) ? $cfg['default_lang'] : 'english');
		}

		$this->out('=== Type registry (' . count($types) . ' types x ' . count($idioms) . ' languages) ===');
		$this->out('languages: ' . implode(', ', $idioms));

		$problems = 0;

		foreach ($types as $key => $def) {
			$vars = array();
			foreach ((isset($def['vars']) ? $def['vars'] : array()) as $var) {
				$vars[$var] = $this->sample_var($var);
			}

			$this->out('');
			$this->out('[' . $key . ']  ' . (empty($def['enabled']) ? 'DISABLED' : 'enabled')
				. '  channel=' . (isset($def['channel']) ? $def['channel'] : '?')
				. '  ttl=' . (isset($def['ttl']) ? $def['ttl'] : '?'));

			//types with a label_key print a status word, and that word is looked up per code per language
			$codes = array();

			if (! empty($def['label_key'])) {
				foreach ($idioms as $idiom) {
					foreach ($this->push_template->get_status_labels($def['label_key'], $idiom) as $code => $unused) {
						$codes[(string) $code] = TRUE;
					}
				}

				$codes = array_keys($codes);
				sort($codes);

				if (empty($codes)) {
					$problems++;
					$this->out('  !! label_key "' . $def['label_key'] . '" declared but no labels found');
					continue;
				}
			}

			$passes = empty($codes) ? array(NULL) : $codes;

			foreach ($passes as $code) {
				$pass_vars = $vars;

				if ($code !== NULL) {
					$pass_vars['STATUS_CODE'] = $code;
					$this->out('  status code ' . $code . ':');
				}

				foreach ($idioms as $idiom) {
					$r = $this->push_template->render($key, $pass_vars, array(
						'customer_no' => 'C10023',
						'related_id'  => 'SAMPLE1',
						'lang'        => $idiom,
					));

					$indent = ($code === NULL) ? 2 : 4;
					$label  = str_repeat(' ', $indent) . str_pad($idiom, 20);
					$cont   = str_repeat(' ', $indent + 20 + 2);

					if ($r === NULL) {
						$problems++;
						$this->out($label . '!! render returned NULL'
							. ($code === NULL ? ' - missing language file or keys' : ' - missing label for code ' . $code));
						continue;
					}

					$this->out($label . $r['title'] . '  (' . $this->len($r['title']) . ')');
					$this->out($cont . $r['body'] . '  (' . $this->len($r['body']) . ')');

					if (trim($r['body']) === '') {
						$problems++;
						$this->out($cont . '!! EMPTY BODY');
					}

					$max_t = isset($cfg['title_max_length']) ? (int) $cfg['title_max_length'] : 50;
					$max_b = isset($cfg['body_max_length']) ? (int) $cfg['body_max_length'] : 200;

					if ($this->len($r['title']) > $max_t or $this->len($r['body']) > $max_b) {
						$problems++;
						$this->out($cont . '!! OVER LENGTH - will be truncated with "..."');
					}
				}
			}
		}

		$this->out('');
		$this->out($problems === 0
			? 'PASS - every type renders cleanly in every language.'
			: 'FAILED - ' . $problems . ' problem(s) above.');
	}

	/**
	 * T11 - dedupe.
	 */
	public function dedupe($customer_no = '')
	{
		if ($customer_no === '') {
			$this->out('Usage: php index.php push_test dedupe <customer_no>');
			return;
		}

		$this->load->library('push_service');

		$key = 'test:' . $customer_no . ':' . date('Y-m-d-H-i-s');

		$this->out('=== Dedupe test ===');
		$this->out('dedupe_key: ' . $key);

		foreach (array(1, 2) as $pass) {
			$r = $this->push_service->notify_customer(
				$customer_no,
				'bill_reminder',
				$this->sample_vars('bill_reminder', $customer_no),
				array('related_id' => 'SAMPLE1', 'dedupe_key' => $key)
			);

			$this->out('');
			$this->out('pass ' . $pass . ':');
			$this->out('  scheduler_id : ' . (isset($r['scheduler_id']) ? $r['scheduler_id'] : '(none)'));
			$this->out('  sent         : ' . $r['sent']);
			$this->out('  skipped      : ' . ($r['skipped'] === NULL ? '(none)' : $r['skipped']));
		}

		$this->out('');
		$this->out('PASS looks like: pass 1 has a scheduler_id, pass 2 shows');
		$this->out('skipped = duplicate and no scheduler_id.');
		$this->out('');
		$this->out('Confirm no duplicates ever reached the table:');
		$this->out('  SELECT dedupe_key, COUNT(*) FROM push_scheduler');
		$this->out('   WHERE dedupe_key IS NOT NULL GROUP BY dedupe_key HAVING COUNT(*) > 1;');
	}

	/**
	 * T13 support - queue N notifications without sending them, so two
	 * dispatch processes have something to race over.
	 */
	public function seed($customer_no = '', $count = 10)
	{
		if ($customer_no === '') {
			$this->out('Usage: php index.php push_test seed <customer_no> [count]');
			return;
		}

		//enqueue through the MODEL, not Push_service
		$this->load->model('push_model');

		$count   = max(1, min(100, (int) $count));
		$made    = 0;
		$skipped = array();

		for ($i = 1; $i <= $count; $i++) {
			$r = $this->push_model->enqueue(
				'admin_triggered',
				$customer_no,
				array(),
				array(
					'title' => 'Concurrency test ' . $i,
					'body'  => 'Delivery ' . $i . ' of ' . $count . '. Safe to ignore.',
				),
				'push_test'
			);

			if (! empty($r['scheduler_id'])) {
				$made++;
			} else {
				$reason = ($r['skipped'] === NULL) ? 'unknown' : $r['skipped'];
				$skipped[$reason] = (isset($skipped[$reason]) ? $skipped[$reason] : 0) + 1;
			}
		}

		$this->out('Queued ' . $made . ' of ' . $count . ' notification(s), all pending.');

		if (! empty($skipped)) {
			$this->out('');
			$this->out('Not queued:');

			foreach ($skipped as $reason => $n) {
				$this->out('  ' . str_pad($reason, 16) . $n);
				$this->out('    ' . $this->explain_skip($reason));
			}
		}

		if ($made === 0) {
			return;
		}

		$this->out('');
		$this->out('Due now: ' . $this->push_model->get_due_count());
		$this->out('');
		$this->out('Now run two workers at once (Windows) - back to back:');
		$this->out('  start "w1" cmd /c php index.php push_cron dispatch');
		$this->out('  start "w2" cmd /c php index.php push_cron dispatch');
		$this->out('');
		$this->out('Across BOTH windows the counters must total exactly ' . $made . ' sent.');
		$this->out('Your handset must show exactly ' . $made . ' notifications, no repeats.');
		$this->out('');
		$this->out('  SELECT push_status, COUNT(*) FROM push_outgoing o');
		$this->out('    JOIN push_scheduler s ON s.scheduler_id = o.scheduler_id');
		$this->out("   WHERE s.customer_no = '" . $customer_no . "' AND s.source = 'push_test'");
		$this->out('   GROUP BY push_status;');
	}

	/**
	 * customer_no -> profile_id -> tokens.
	 */
	public function devices($customer_no = '')
	{
		if ($customer_no === '') {
			$this->out('Usage: php index.php push_test devices <customer_no>');
			return;
		}

		$this->load->model('push_model');

		$this->out('=== Device resolution for ' . $customer_no . ' ===');

		$profile_id = $this->push_model->get_profile_id($customer_no);

		if (empty($profile_id)) {
			$this->out('No profile_id found.');
			$this->out('Either the customer_no does not exist, or push_model::$t_customer');
			$this->out('("c") is not the right table name. Check that first.');
			return;
		}

		$this->out('profile_id : ' . $profile_id);

		$devices = $this->push_model->get_active_tokens($profile_id);

		$this->out('devices    : ' . count($devices));

		if (empty($devices)) {
			$this->out('');
			$this->out('No push-eligible devices. Expected if the app is not installed.');
			$this->out('Otherwise check the `app` row: allow_push must be 1, firebase_token');
			$this->out('not null, deleted_at null.');
			return;
		}

		$this->load->library('fcm_http_client');

		foreach ($devices as $i => $d) {
			$this->out('  [' . ($i + 1) . '] ' . str_pad((string) $d['platform'], 8)
				. ' ' . $d['device_uuid']
				. '  ' . $this->fcm_http_client->mask($d['firebase_token']));
		}
	}

	/**
	 * Full Push_service path with dry_run - gate, overlay, resolve, render,
	 * build envelope - printing the exact JSON that would reach FCM.
	 */
	public function preview($customer_no = '', $type = 'bill_reminder')
	{
		if ($customer_no === '') {
			$this->out('Usage: php index.php push_test preview <customer_no> [type]');
			return;
		}

		$this->load->library('push_service');

		$this->out('=== Dry run: ' . $type . ' -> ' . $customer_no . ' ===');

		$r = $this->push_service->notify_customer(
			$customer_no,
			$type,
			$this->sample_vars($type, $customer_no),
			array('related_id' => 'SAMPLE1', 'dry_run' => TRUE)
		);

		$this->out('devices : ' . (isset($r['devices']) ? $r['devices'] : 0));
		$this->out('skipped : ' . ($r['skipped'] === NULL ? '(none)' : $r['skipped']));

		if (! empty($r['skipped']) && $r['skipped'] !== 'dry_run') {
			$this->out('');
			$this->out($this->explain_skip($r['skipped']));
			return;
		}

		if (empty($r['preview'])) {
			$this->out('No payload produced.');
			return;
		}

		$this->out('');
		$this->out('--- FCM message body ---');
		$this->out(json_encode($r['preview'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		$this->out('');
		$this->out('Check: every value under "data" must be a STRING. FCM v1 rejects');
		$this->out('the whole message with a 400 if any of them is an int or bool.');
	}

	/**
	 * The real end-to-end send, through the same entry point cron.php and
	 * ticket_model.php will use.
	 */
	public function notify($customer_no = '', $type = 'bill_reminder')
	{
		if ($customer_no === '') {
			$this->out('Usage: php index.php push_test notify <customer_no> [type]');
			return;
		}

		$this->load->library('push_service');

		$this->out('=== Live send: ' . $type . ' -> ' . $customer_no . ' ===');

		$r = $this->push_service->notify_customer(
			$customer_no,
			$type,
			$this->sample_vars($type, $customer_no),
			array('related_id' => 'SAMPLE1')
		);

		$this->out('devices : ' . (isset($r['devices']) ? $r['devices'] : 0));
		$this->out('sent    : ' . $r['sent']);
		$this->out('failed  : ' . $r['failed']);
		$this->out('pruned  : ' . $r['pruned']);
		$this->out('skipped : ' . ($r['skipped'] === NULL ? '(none)' : $r['skipped']));

		if (! empty($r['skipped'])) {
			$this->out('');
			$this->out($this->explain_skip($r['skipped']));
		}

		if ($r['pruned'] > 0) {
			$this->out('');
			$this->out('A token was pruned. That is correct behaviour if the device');
			$this->out('reinstalled or logged out - re-open the app to re-register.');
		}
	}

	/**
	 * Effective config AFTER the sys_config overlay.
	 */
	public function config()
	{
		$this->load->library('push_service');

		$check = $this->push_service->health_check();

		$this->out('=== Effective push config ===');
		$this->out('enabled   : ' . ($check['enabled'] ? 'YES' : 'NO'));
		$this->out('whitelist : ' . (empty($check['whitelist']) ? '(empty - everyone)' : implode(', ', $check['whitelist'])));
		$this->out('transport : ' . ($check['ok'] ? 'OK' : 'PROBLEMS'));

		foreach ((isset($check['errors']) ? $check['errors'] : array()) as $e) {
			$this->out('  - ' . $e);
		}

		$this->out('');
		$this->out('If "enabled" does not change when you edit sys_config, the overlay');
		$this->out('is not reading the right table. Check Push_service::$t_cfg.');
		$this->out('To enable without a deploy:');
		$this->out("  INSERT INTO sys_config (category, `key`, val) VALUES ('push', 'enabled', '1');");
	}

	/**
	 * Fleet counters.
	 */
	public function stats()
	{
		$this->load->model('push_model');

		$stats = $this->push_model->get_stats();

		if (empty($stats)) {
			$this->out('No stats returned - check the `app` table name.');
			return;
		}

		$this->out('=== Device fleet ===');

		foreach ($stats as $key => $value) {
			$this->out('  ' . str_pad($key, 16) . ': ' . (int) $value);
		}

		$this->out('');
		$this->out('with_token much higher than push_enabled means logout or opt-out');
		$this->out('handling is leaving tokens behind. A large "stale" count is what');
		$this->out('the Step 11 weekly prune cron is for.');
	}


	/**
	 * Plausible values for a type's declared vars, so the harness works for
	 * any type without juggling CLI arguments.
	 */
	protected function sample_vars($type, $customer_no)
	{
		$this->load->library('push_template');

		$def  = $this->push_template->get_type($type);
		$vars = array();

		foreach ((isset($def['vars']) ? $def['vars'] : array()) as $var) {
			$vars[$var] = ($var === 'CUSTOMER_NO') ? $customer_no : $this->sample_var($var);
		}

		return $vars;
	}

	protected function len($str)
	{
		return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);
	}

	protected function sample_var($var)
	{
		$samples = array(
			'CUSTOMER_NO' => 'C10023',
			'BALANCE'     => '89.00',
			'DUE_DATE'    => date('d/m/Y', strtotime('+3 days')),
			'DAYS_LEFT'   => '3',
			'TT_NO'       => 'TT2026080001',
			'CS_NO'       => 'CS2026080001',
			'STATUS_NAME' => 'In Progress',
			'STATUS_CODE' => '1',
			'AMOUNT'      => '89.00',
			'BILL_NO'     => 'B2026080001',
		);

		if (isset($samples[$var])) {
			return $samples[$var];
		}

		//an undeclared var falls back to a visible marker rather than an empty string
		log_message('error', '[push_test] no sample value for %' . $var . '% - add one to sample_var()');

		return 'SAMPLE_' . $var;
	}

	protected function explain_skip($skipped)
	{
		$reasons = array(
			'disabled'        => "Push is globally off. Set push.enabled = TRUE in config/push.php,\nor insert a sys_config row: category='push', key='enabled', val='1'.",
			'type_disabled'   => "This type has 'enabled' => FALSE in config/push_types.php.",
			'unknown_type'    => "No such type in config/push_types.php - check the spelling.",
			'not_whitelisted' => "push.whitelist is non-empty and does not include this customer_no.\nThat is rollout Phase 2 behaviour. Empty the whitelist to send to everyone.",
			'no_profile'      => "customer_no did not resolve to a profile_id.\nRun: php index.php push_test devices <customer_no>",
			'no_devices'      => "The profile has no push-eligible device. Expected when the app is not\ninstalled - this is the cheap early return that keeps cron cost near zero.",
			'render_failed'   => "The template did not render. Run: php index.php push_test types",
			'budget_exceeded' => "The per-process time budget is spent. Only meaningful inside a long cron.",
			'duplicate'       => "A row with the same dedupe_key already exists. For the dedupe test\nthis is the PASS condition.",
			'db_error'        => "The enqueue transaction rolled back. Check application/logs for the\nSQL error - most likely push_scheduler or push_outgoing is missing a\ncolumn from the Step 11 migration.",
			'exception'       => "Something threw inside enqueue(). Check application/logs.",
		);

		return isset($reasons[$skipped]) ? $reasons[$skipped] : ('Unrecognised skip reason: ' . $skipped);
	}

	protected function print_result(array $r)
	{
		$this->out('ok        : ' . ($r['ok'] ? 'YES' : 'NO'));
		$this->out('http      : ' . $r['http']);
		$this->out('attempts  : ' . $r['attempts']);
		$this->out('elapsed   : ' . $r['ms'] . 'ms');

		if (! $r['ok']) {
			$this->out('code      : ' . $r['code']);
			$this->out('error     : ' . $r['error']);
			$this->out('permanent : ' . ($r['permanent'] ? 'YES' : 'NO'));
			$this->out('prunable  : ' . ($r['prunable'] ? 'YES' : 'NO'));
		}
	}

	protected function out($line)
	{
		echo $line . PHP_EOL;
	}
}
