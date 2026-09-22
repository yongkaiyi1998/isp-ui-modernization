<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The ONLY class business modules talk to. cron.php, ticket_model.php and
 * the admin controller call notify_customer() and nothing else - they never
 * see a token, an HTTP status, or the word "FCM".
 *
 *   $this->load->library('push_service');
 *   $this->push_service->notify_customer($customer_no, 'bill_overdue', array(
 *       'CUSTOMER_NO' => $customer_no,
 *       'BALANCE'     => number_format($updated_balance, 2),
 *       'DUE_DATE'    => $due_date,
 *   ), array('related_id' => $bill_no));
 */
class Push_service
{

	/** @var CI_Controller */
	protected $CI;
	/** @var array transport config, after the cfg-table overlay */
	protected $cfg = array();
	/** @var bool overlay applied once per request */
	protected $overlay_done = FALSE;
	/** @var float wall-clock seconds spent sending in this process */
	protected $spent = 0.0;
	/** @var bool set once the cron time budget is blown */
	protected $budget_blown = FALSE;

	// Runtime config table - the kill switch that needs no deploy
	protected $t_cfg      = 'sys_config';
	protected $cfg_cat    = 'category';
	protected $cfg_key    = 'key';
	protected $cfg_val    = 'val';
	protected $cfg_catval = 'push';

	public function __construct()
	{
		$this->CI = &get_instance();

		$this->CI->config->load('push', TRUE, TRUE);
		$this->cfg = (array) $this->CI->config->item('push', 'push');

		$this->CI->load->library('push_template');
		$this->CI->load->library('fcm_http_client');
		$this->CI->load->model('push_model');
	}

	// ==================================================================
	// PUBLIC API
	// ==================================================================

	/**
	 * Send a notification to every eligible device belonging to the profile
	 * that owns $customer_no.
	 *
	 * @param  string $customer_no
	 * @param  string $type  registry key from config/push_types.php
	 * @param  array  $vars  %PLACEHOLDER% values for the template
	 * @param  array  $opts  related_id, route, lang, title, body, data, dry_run
	 * @return array  sent, failed, pruned, devices, skipped
	 */
	public function notify_customer($customer_no, $type, array $vars = array(), array $opts = array())
	{
		try {
			if (($gate = $this->gate($type, $customer_no)) !== NULL) {
				return $gate;
			}

			$profile_id = $this->CI->push_model->get_profile_id($customer_no);

			if ($profile_id === NULL) {
				return $this->result('no_profile');
			}

			// Carried into data so the app can deep-link to the right service - a profile may own several customer_no.
			if (! isset($opts['customer_no'])) {
				$opts['customer_no'] = $customer_no;
			}

			return $this->dispatch($profile_id, $type, $vars, $opts, $customer_no);
		} catch (Exception $e) {
			return $this->fatal($type, $customer_no, $e);
		}
	}

	/**
	 * Same as notify_customer() but when the caller already holds the
	 * profile id - saves a lookup in loops.
	 */
	public function notify_profile($profile_id, $type, array $vars = array(), array $opts = array())
	{
		try {
			$customer_no = isset($opts['customer_no']) ? $opts['customer_no'] : NULL;

			if (($gate = $this->gate($type, $customer_no)) !== NULL) {
				return $gate;
			}

			return $this->dispatch((int) $profile_id, $type, $vars, $opts, $customer_no);
		} catch (Exception $e) {
			return $this->fatal($type, $profile_id, $e);
		}
	}

	/**
	 * Batch helper for cron loops.
	 *
	 * @param  array    $customer_nos
	 * @param  string   $type
	 * @param  callable $vars_fn  function($customer_no) => array
	 * @param  array    $opts     opts_fn (callable) may override per customer
	 * @return array    aggregated counters
	 */
	public function notify_customers(array $customer_nos, $type, $vars_fn, array $opts = array())
	{
		$total = $this->result(NULL);

		foreach ($customer_nos as $customer_no) {
			if ($this->budget_blown) {
				break;
			}

			$vars = is_callable($vars_fn) ? call_user_func($vars_fn, $customer_no) : array();

			$row_opts = $opts;
			if (isset($opts['opts_fn']) && is_callable($opts['opts_fn'])) {
				$row_opts = array_merge($opts, (array) call_user_func($opts['opts_fn'], $customer_no));
				unset($row_opts['opts_fn']);
			}

			$r = $this->notify_customer($customer_no, $type, (array) $vars, $row_opts);

			$total['sent']    += $r['sent'];
			$total['failed']  += $r['failed'];
			$total['pruned']  += $r['pruned'];
			$total['devices'] += $r['devices'];
		}

		return $total;
	}

	/**
	 * Recipient handles for the push channel, in the same flat shape
	 * Notification_service::buildContacts() expects for the other three.
	 *
	 * @param  string $customer_no
	 * @return array  list of token strings, empty when the customer has no
	 *                app installed (the common case during rollout)
	 */
	public function get_tokens($customer_no)
	{
		try {
			$this->apply_overlay();

			if (! $this->item('push_enabled', FALSE)) {
				return array();
			}

			$whitelist = (array) $this->item('whitelist', array());

			if (! empty($whitelist) && ! in_array((string) $customer_no, $whitelist, TRUE)) {
				return array();
			}

			$profile_id = $this->CI->push_model->get_profile_id($customer_no);

			if (empty($profile_id)) {
				return array();
			}

			$tokens = array();

			foreach ($this->CI->push_model->get_active_tokens($profile_id) as $device) {
				$tokens[] = $device['firebase_token'];
			}

			return $tokens;
		} catch (Exception $e) {
			log_message('error', '[push] get_tokens failed for customer ' . $customer_no . ': ' . $e->getMessage());

			return array();
		}
	}

	/**
	 * Send to ONE already-resolved token.
	 *
	 * @param  string $token
	 * @param  string $type  registry key
	 * @param  array  $vars  %PLACEHOLDER% values
	 * @param  array  $opts  related_id, route, lang, customer_no, dry_run
	 * @return array  sent, failed, pruned, devices, skipped
	 */
	public function send_to_token($token, $type, array $vars = array(), array $opts = array())
	{
		// NOTE - THIS PATH BYPASSES THE QUEUE
		$started     = microtime(TRUE);
		$customer_no = isset($opts['customer_no']) ? $opts['customer_no'] : NULL;

		try {
			if (($gate = $this->gate($type, $customer_no)) !== NULL) {
				return $gate;
			}

			if ($token === NULL or $token === '') {
				return $this->result('no_token');
			}

			$rendered = $this->CI->push_template->render($type, $vars, $opts);

			if ($rendered === NULL) {
				return $this->result('render_failed');
			}

			$message = $this->build_message(
				array('title' => $rendered['title'], 'body' => $rendered['body']),
				array(
					'data'     => $rendered['data'],
					'channel'  => $rendered['channel'],
					'ttl'      => $rendered['ttl'],
					'collapse' => isset($rendered['collapse']) ? $rendered['collapse'] : NULL,
				)
			);

			if ($message === NULL) {
				// Empty copy - build_message() has already logged why.
				return $this->result('render_failed');
			}

			if (! empty($opts['dry_run'])) {
				return array(
					'sent'    => 0,
					'failed'  => 0,
					'pruned'  => 0,
					'devices' => 1,
					'skipped' => 'dry_run',
					'preview' => $message,
				);
			}

			if ($this->over_budget()) {
				return $this->result('budget_exceeded');
			}

			$r = $this->CI->fcm_http_client->send($token, $message);

			$this->spent += ($r['ms'] / 1000);

			$sent = $failed = $pruned = 0;

			if ($r['ok']) {
				$sent = 1;
			} else {
				$failed = 1;

				if ($r['prunable']) {
					$pruned = $this->CI->push_model->prune_token($token, $r['code']);
				} else {
					log_message('error', '[push] send_to_token failed type=' . $type
						. ' token=' . $this->CI->fcm_http_client->mask($token)
						. ' http=' . $r['http'] . ' code=' . $r['code'] . ' err=' . $r['error']);
				}
			}

			$this->log_result($type, $customer_no, NULL, 1, $sent, $failed, $pruned, $started, 'via_notification_service');

			return array(
				'sent'    => $sent,
				'failed'  => $failed,
				'pruned'  => $pruned,
				'devices' => 1,
				'skipped' => NULL,
			);
		} catch (Exception $e) {
			return $this->fatal($type, $customer_no, $e);
		}
	}

	/**
	 * Global switch AND per-type switch.
	 */
	public function is_enabled($type = NULL)
	{
		$this->apply_overlay();

		if (! $this->item('push_enabled', FALSE)) {
			return FALSE;
		}

		if ($type === NULL) {
			return TRUE;
		}

		$def = $this->CI->push_template->get_type($type);

		return ($def !== NULL) && ! empty($def['enabled']);
	}

	/**
	 * "today at HH:MM:SS" - for a cron job that should queue now but deliver at a fixed time of day.
	 * 
	 * @param string|null $time HH:MM:SS, defaults to push.billing_schedule_time
	 * @return string
	 */
	public function today_at($time = NULL)
	{
		$time = ($time !== NULL && $time !== '') ? $time : $this->item('billing_schedule_time', '12:00:00');

		return date('Y-m-d') . ' ' . $time;
	}

	/**
	 * Reset the per-process time budget.
	 */
	public function reset_budget()
	{
		$this->spent        = 0.0;
		$this->budget_blown = FALSE;
	}

	// ==================================================================
	// INTERNALS
	// ==================================================================

	/**
	 * All the reasons to stop before doing any work. Returns a result array
	 * to hand straight back, or NULL to proceed.
	 */
	protected function gate($type, $customer_no = NULL)
	{
		$this->apply_overlay();

		if (! $this->item('push_enabled', FALSE)) {
			return $this->result('disabled');
		}

		if (! $this->CI->push_template->type_exists($type)) {
			// A developer bug, not a configuration state - loud on purpose.
			log_message('error', '[push] unknown notification type "' . $type . '"');
			return $this->result('unknown_type');
		}

		if (! $this->is_enabled($type)) {
			return $this->result('type_disabled');
		}

		// Restrict live sends to internal accounts while running against production data.
		$whitelist = (array) $this->item('whitelist', array());

		if (! empty($whitelist) && $customer_no !== NULL && ! in_array((string) $customer_no, $whitelist, TRUE)) {
			return $this->result('not_whitelisted');
		}

		if ($this->budget_blown) {
			return $this->result('budget_exceeded');
		}

		return NULL;
	}

	/**
	 * Resolve devices, render once, send per device, prune the dead.
	 */
	/**
	 * Queue the notification, then try to deliver it immediately.
	 */
	protected function dispatch($profile_id, $type, array $vars, array $opts, $customer_no = NULL)
	{
		$started = microtime(TRUE);

		// queue only, let the worker deliver
		$defer = ! empty($opts['defer']) || ! empty($opts['send_at']);
		unset($opts['defer']);

		// Directly source it as router name
		$source = isset($opts['source']) ? $opts['source'] : NULL;

		if ($source === NULL && isset($this->CI->router)) {
			$class  = $this->CI->router->fetch_class();
			$source = ($class !== '' && $class !== NULL) ? substr($class, 0, 30) : 'unknown';
		}
		$created_by = isset($opts['created_by']) ? $opts['created_by'] : NULL;
		unset($opts['source'], $opts['created_by']);

		if ($profile_id !== NULL) {
			$opts['profile_id'] = $profile_id;
		}

		$queued = $this->CI->push_model->enqueue(
			$type,
			$customer_no,
			$vars,
			$opts,
			$source,
			$created_by
		);

		if (empty($queued['scheduler_id'])) {
			// no_devices, no_profile, duplicate, render_failed, db_error
			return $this->result($queued['skipped']);
		}

		// DEFER: queue without attempting delivery here
		if ($defer) {
			$this->log_result(
				$type,
				$customer_no,
				$profile_id,
				$queued['devices'],
				0,
				0,
				0,
				$started,
				'queued scheduler_id=' . $queued['scheduler_id'] . ' (deferred)'
			);

			return array(
				'sent'         => 0,
				'failed'       => 0,
				'pruned'       => 0,
				'retrying'     => 0,
				'devices'      => $queued['devices'],
				'scheduler_id' => $queued['scheduler_id'],
				'skipped'      => NULL,
				'deferred'     => TRUE,
			);
		}

		$counters = $this->run_scheduler($queued['scheduler_id']);

		$this->log_result(
			$type,
			$customer_no,
			$profile_id,
			$queued['devices'],
			$counters['sent'],
			$counters['failed'],
			$counters['pruned'],
			$started,
			'scheduler_id=' . $queued['scheduler_id']
				. ($counters['retrying'] > 0 ? ' retrying=' . $counters['retrying'] : '')
		);

		return array(
			'sent'         => $counters['sent'],
			'failed'       => $counters['failed'],
			'pruned'       => $counters['pruned'],
			'retrying'     => $counters['retrying'],
			'devices'      => $queued['devices'],
			'scheduler_id' => $queued['scheduler_id'],
			'skipped'      => NULL,
		);
	}

	// ==================================================================
	// DISPATCHER
	// ==================================================================
	/**
	 * Cron worker. Claims whatever is due and sends it.
	 *
	 * @param  int   $limit deliveries per run
	 * @return array counters
	 */
	public function run($limit = NULL)
	{
		$limit = ($limit === NULL) ? (int) $this->item('dispatch_batch', 50) : (int) $limit;

		$released = $this->CI->push_model->release_stuck(
			(int) $this->item('stuck_after_minutes', 10)
		);

		$rows = $this->CI->push_model->claim($limit);

		$result = $this->send_rows($rows);

		$result['released'] = $released;

		return $result;
	}

	/**
	 * Immediate dispatch for one notification, called straight after
	 * enqueue while the customer is still looking at their screen.
	 *
	 * @param  int   $scheduler_id
	 * @return array counters
	 */
	public function run_scheduler($scheduler_id)
	{
		$rows = $this->CI->push_model->claim(
			(int) $this->item('immediate_max_devices', 10),
			$scheduler_id
		);

		return $this->send_rows($rows, array(
			'max_attempts' => 1,
			'timeout'      => (int) $this->item('immediate_timeout', 4),
		));
	}

	// ==================================================================
	// THE SEND LOOP
	// ==================================================================

	/**
	 * @param  array $rows     claimed rows joined with their scheduler row
	 * @param  array $override per-send transport overrides
	 * @return array counters
	 */
	protected function send_rows(array $rows, array $override = array())
	{
		$counters = array(
			'claimed'  => count($rows),
			'sent'     => 0,
			'failed'   => 0,
			'retrying' => 0,
			'pruned'   => 0,
			'released' => 0,
		);

		if (empty($rows)) {
			return $counters;
		}

		foreach ($rows as $row) {
			// Stop once the process has psnet its push budget, the rest stay claimed for the next run
			if ($this->over_budget()) {
				break;
			}

			$this->send_one($row, $override, $counters);
		}

		return $counters;
	}

	protected function send_one(array $row, array $override, array &$counters)
	{
		try {
			$token = $row['firebase_token'];

			// The token was pruned between enqueue and now - the customer logged out
			if ($token === NULL or $token === '') {
				$this->CI->push_model->record_attempt($row, array(
					'ok'        => FALSE,
					'permanent' => TRUE,
					'code'      => 'TOKEN_GONE',
					'error'     => 'Token was removed after this notification was queued',
				));

				$counters['failed']++;
				return;
			}

			if (! $this->is_enabled($row['push_type'])) {
				$this->CI->push_model->record_attempt($row, array(
					'ok'        => FALSE,
					'permanent' => TRUE,
					'code'      => 'TYPE_DISABLED',
					'error'     => 'Type "' . $row['push_type'] . '" is switched off',
				));

				$counters['failed']++;
				return;
			}

			$message = $this->build_message_for_row($row);

			if ($message === NULL) {
				// build_message_for_now() returns NULL for an unknown type, a failed render, or empty copy
				$this->CI->push_model->record_attempt($row, array(
					'ok'        => FALSE,
					'permanent' => TRUE,
					'code'      => 'NO_MESSAGE',
					'error'     => 'Could not build a message for type "' . $row['push_type'] . '" - see the log',
				));

				$counters['failed']++;
				return;
			}

			$result = $this->CI->fcm_http_client->send($token, $message, $override);

			$this->spent += ($result['ms'] / 1000);

			// Prune BEFORE recording the attempt
			if (! $result['ok'] && ! empty($result['prunable'])) {
				$counters['pruned'] += $this->CI->push_model->prune_token($token, $result['code']);
			}

			$status = $this->CI->push_model->record_attempt($row, $result);

			if ($status === 'S') {
				$counters['sent']++;
			} elseif ($status === 'F') {
				$counters['failed']++;
			} else {
				$counters['retrying']++;
			}
		} catch (Exception $e) {
			// Never let on e bad row stop the run
			log_message('error', '[push] dispatch error on outgoing_id '
				. $row['outgoing_id'] . ': ' . $e->getMessage());

			try {
				$this->CI->push_model->record_attempt($row, array(
					'ok'        => FALSE,
					'permanent' => FALSE,
					'code'      => 'DISPATCH_ERROR',
					'error'     => $e->getMessage(),
				));

				$counters['retrying']++;
			} catch (Exception $inner) {
				log_message('error', '[push] could not record failure for outgoing_id '
					. $row['outgoing_id'] . ': ' . $inner->getMessage());
			}
		}
	}

	// ==================================================================
	// MESSAGE ASSEMBLY
	// ==================================================================

	/**
	 * Build the FCM message for a claimed delivery row.
	 *
	 * @return array|null NULL when the type has left the registry
	 */
	protected function build_message_for_row(array $row)
	{
		$def = $this->CI->push_template->get_type($row['push_type']);

		if ($def === NULL) {
			return NULL;
		}

		$opts = json_decode((string) $row['push_opts'], TRUE);
		$opts = is_array($opts) ? $opts : array();

		$data = array(
			'type'  => (string) $row['push_type'],
			'route' => (string) (isset($opts['route']) ? $opts['route'] : (isset($def['route']) ? $def['route'] : '/home')),
			'ts'    => (string) time(),
		);

		if (isset($opts['related_id']) && $opts['related_id'] !== NULL) {
			$data['related_id'] = (string) $opts['related_id'];
		}

		if (! empty($row['customer_no'])) {
			$data['customer_no'] = (string) $row['customer_no'];
		}

		// Caller-supplied extras.
		if (! empty($opts['data']) && is_array($opts['data'])) {
			foreach ($opts['data'] as $k => $v) {
				if (is_scalar($v)) {
					$data[(string) $k] = (string) $v;
				}
			}
		}

		$copy = $this->copy_for_row($row, $opts);

		if ($copy === NULL) {
			return NULL;
		}

		return $this->build_message(
			$copy,
			array(
				'data'     => $data,
				'channel'  => isset($def['channel']) ? $def['channel'] : 'general',
				'ttl'      => isset($def['ttl']) ? $def['ttl'] : '86400s',
				'collapse' => isset($def['collapse']) ? $def['collapse'] : NULL,
			)
		);
	}

	/**
	 * The copy to send for this delivery.
	 *
	 * @return array|null title/body, or NULL when rendering fails
	 */
	protected function copy_for_row(array $row, array $opts)
	{
		if ($row['push_title'] !== NULL && $row['push_title'] !== '') {
			return array('title' => $row['push_title'], 'body' => $row['push_msg']);
		}

		$vars = json_decode((string) $row['push_vars'], TRUE);
		$vars = is_array($vars) ? $vars : array();

		$idiom = $this->idiom_for($row['lang']);

		if ($idiom !== NULL) {
			$opts['lang'] = $idiom;
		}

		$rendered = $this->CI->push_template->render($row['push_type'], $vars, $opts);

		if ($rendered === NULL) {
			log_message('error', '[push] lazy render failed for outgoing_id ' . $row['outgoing_id']
				. ' type=' . $row['push_type'] . ' lang=' . (string) $row['lang']);

			return NULL;
		}

		// Persist, so a retry sends identical content and the audit trail records what was actually delivered.
		$this->CI->push_model->store_copy(
			(int) $row['outgoing_id'],
			(int) $row['scheduler_id'],
			$rendered['title'],
			$rendered['body']
		);

		return array('title' => $rendered['title'], 'body' => $rendered['body']);
	}

	/**
	 * Locale (en_US) to language folder (english), via push.lang_map.
	 */
	protected function idiom_for($locale)
	{
		if (empty($locale)) {
			return NULL;
		}

		$map    = (array) $this->item('lang_map', array());
		$locale = trim((string) $locale);

		if (isset($map[$locale])) {
			return $map[$locale];
		}

		$base = strtok($locale, '_-');

		return isset($map[$base]) ? $map[$base] : NULL;
	}

	/**
	 * Assemble an FCM v1 message body.
	 */
	public function build_message(array $copy, array $meta)
	{
		$title = isset($copy['title']) ? trim((string) $copy['title']) : '';
		$body  = isset($copy['body']) ? trim((string) $copy['body']) : '';

		// Refuse to send an empty notification
		if ($title === '' or $body === '' or $title === NULL or $body === NULL) {
			log_message('error', '[push] refusing to send empty copy'
				. ' (title=' . ($title === '' ? 'EMPTY' : 'ok')
				. ', body=' . ($body === '' ? 'EMPTY' : 'ok') . ')'
				. ' - is Push_service::copy_for_row() deployed?');

			return NULL;
		}

		$message = array(
			'notification' => array(
				'title' => $title,
				'body'  => $body,
			),
			'data' => isset($meta['data']) ? $meta['data'] : array(),
			'android' => array(
				'priority'     => 'high',
				'ttl'          => $this->normalize_ttl(isset($meta['ttl']) ? $meta['ttl'] : NULL),
				'notification' => array(
					'channel_id' => isset($meta['channel']) ? $meta['channel'] : 'general',
					// DO NOT ADD click_action HERE
				),
			),
			'apns' => array(
				'headers' => array('apns-priority' => '10'),
				'payload' => array('aps' => array('sound' => 'default', 'badge' => 1)),
			),
		);

		if (! empty($meta['collapse'])) {
			$message['android']['collapse_key']             = $meta['collapse'];
			$message['apns']['headers']['apns-collapse-id'] = $meta['collapse'];
		}

		return $message;
	}

	/**
	 * Coerce a ttl into the protobuf Duration format FCM demands.
	 */
	public function normalize_ttl($ttl)
	{
		if ($ttl === NULL or $ttl === '') {
			return '86400s';
		}

		if (is_int($ttl) or is_float($ttl)) {
			return ((int) $ttl) . 's';
		}

		$ttl = trim((string) $ttl);

		if (preg_match('/^\d+(\.\d+)?s$/', $ttl)) {
			return $ttl;
		}

		if (preg_match('/^\d+$/', $ttl)) {
			log_message('error', '[push] ttl "' . $ttl . '" is missing its "s" suffix - '
				. 'fix config/push_types.php; coercing to "' . $ttl . 's"');

			return $ttl . 's';
		}

		log_message('error', '[push] unrecognised ttl "' . $ttl . '" - falling back to 86400s');

		return '86400s';
	}

	/**
	 * Hard wall-clock cap on push per process.
	 */
	protected function over_budget()
	{
		$budget = (int) $this->item('cron_time_budget', 120);

		if ($budget <= 0 or $this->spent < $budget) {
			return FALSE;
		}

		if (! $this->budget_blown) {
			$this->budget_blown = TRUE;
			log_message('error', '[push] time budget of ' . $budget . 's exhausted - suspending push for the rest of this run');
		}

		return TRUE;
	}

	/**
	 * Read runtime overrides from the `sys_cfg` table on top of the config file.
	 */
	protected function apply_overlay()
	{
		if ($this->overlay_done) {
			return;
		}

		$this->overlay_done = TRUE;

		try {
			if (! $this->CI->db->table_exists($this->t_cfg)) {
				return;
			}

			$rows = $this->CI->db->query(
				"SELECT `{$this->cfg_key}` AS k, `{$this->cfg_val}` AS v
				   FROM `{$this->t_cfg}`
				  WHERE `{$this->cfg_cat}` = ?",
				array($this->cfg_catval)
			)->result_array();

			foreach ($rows as $row) {
				switch ($row['k']) {
					case 'push_enabled':
						$this->cfg['push_enabled'] = (trim($row['v']) === '1');
						break;

					case 'whitelist':
						$list = array_filter(array_map('trim', explode(',', (string) $row['v'])));
						$this->cfg['whitelist'] = array_values($list);
						break;
				}
			}
		} catch (Exception $e) {
			log_message('error', '[push] cfg overlay failed, using config file values: ' . $e->getMessage());
		}
	}

	/**
	 * THE PERSISTENCE SEAM.
	 */
	protected function log_result($type, $customer_no, $profile_id, $devices, $sent, $failed, $pruned, $started, $note = '')
	{
		$line = '[push] type=' . $type
			. ' cust=' . ($customer_no === NULL ? '-' : $customer_no)
			. ' profile=' . $profile_id
			. ' devices=' . $devices
			. ' sent=' . $sent
			. ' failed=' . $failed
			. ' pruned=' . $pruned
			. ' ms=' . (int) round((microtime(TRUE) - $started) * 1000);

		if ($note !== '') {
			$line .= ' note=' . $note;
		}

		log_message(($failed > 0 ? 'error' : 'info'), $line);
	}

	/**
	 * Last line of defence. Anything that escapes the inner handling is
	 * swallowed here so a push problem can never surface as a billing
	 * failure.
	 */
	protected function fatal($type, $ref, Exception $e)
	{
		log_message('error', '[push] EXCEPTION type=' . $type . ' ref=' . $ref
			. ' msg=' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());

		return $this->result('exception');
	}

	protected function result($skipped)
	{
		return array(
			'sent'    => 0,
			'failed'  => 0,
			'pruned'  => 0,
			'devices' => 0,
			'skipped' => $skipped,
		);
	}

	protected function item($key, $default = NULL)
	{
		return isset($this->cfg[$key]) ? $this->cfg[$key] : $default;
	}
}
