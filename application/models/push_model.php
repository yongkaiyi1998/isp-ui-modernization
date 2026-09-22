<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Push_model extends CI_Model
{

    protected $t_app = 'profile_app';
    protected $t_customer = 'customer';
    protected $t_scheduler = 'push_scheduler';
    protected $t_outgoing = 'push_outgoing';
    protected $t_cfg = 'sys_config';
    /** @var array|null cached sys_config overlay for this request */
    protected $cfg = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('push_template');
    }

    // ==================================================================
	// DEVICES / FIREBASE TOKEN
	// ==================================================================

    /**
     * customer_no -> profile_id (= profile_app.acc_id = profile.acc_id)
     * 
     * @param   string      $customer_no
     * @return  int|null    NULL when the customer does not exist or has no profile
     */
    public function get_profile_id($customer_no)
    {
        if ($customer_no === NULL or $customer_no === '') {
            return NULL;
        }

        $row = $this->db
            ->query("
                SELECT profile_id
                FROM `{$this->t_customer}`
                WHERE customer_no = ?
                LIMIT 1
            ", array($customer_no))
            ->row_array();

        if (empty($row['profile_id']))
            return NULL;

        return (int) $row['profile_id'];
    }

    /**
     * Every device beloging to a profile that is eligible to receive push.
     * 
     * Eligibility is ALL of:
     *      - has a token
     *      - allow_push = 1        (customer opted in / has not logged out)
     *      - deleted_at IS NULL    (device not soft-deletd)
     * 
     * @param int $profile_id
     * @return array list of array(device_uuid, firebase_token, platform, lang)
     */
    public function get_active_tokens($profile_id)
    {
        $profile_id = (int) $profile_id;

        if ($profile_id <= 0) {
            return array();
        }

        $rows = $this->db->query(
            "SELECT device_uuid, firebase_token, platform, lang
			   FROM `{$this->t_app}`
			  WHERE acc_id = ?
			    AND allow_push = 1
			    AND firebase_token IS NOT NULL
			    AND firebase_token != ''
			    AND deleted_at IS NULL",
            array($profile_id)
        )->result_array();

        $seen = array();
        $out  = array();

        foreach ($rows as $row) {
            if (isset($seen[$row['firebase_token']])) {
                log_message('error', '[push] duplicate token across devices, profile=' . $profile_id
                    . ' device=' . $row['device_uuid'] . ' - claim_token() may not be deployed');
                continue;
            }

            $seen[$row['firebase_token']] = TRUE;
            $out[] = $row;
        }

        return $out;
    }

    /**
     * Retire a token FCM has told us is dead.
     * 
     * @param string $firebase_token
     * @param string $reason    FCM error code, fore the log line
     * @return int rows affected
     */
    public function prune_token($firebase_token, $reason = '')
    {
        if ($firebase_token === NULL or $firebase_token === '')
            return 0;

        $this->db->query(
            "UPDATE `{$this->t_app}`
            SET firebase_token = NULL,
                allow_push = 0,
                updated_at = NOW()
            WHERE firebase_token = ?",
            array($firebase_token)
        );

        $affected = (int) $this->db->affected_rows();

        if ($affected > 0)
            log_message('info', '[push] pruned token=' . substr($firebase_token, 0, 12) . '...' . ' rows=' . $affected . ' reason=' . $reason);

        return $affected;
    }
    
	// ==================================================================
	// ENQUEUE
	// ==================================================================

    /**
     * Queue a notification for every eligible device of a customer.
     *
     * @param  string $type        key in config/push_types.php
     * @param  string $customer_no
     * @param  array  $vars        %PLACEHOLDER% values
     * @param  array  $opts        related_id, title, body, data, dedupe_key
     * @param  string $source      cron | ticket | customer_support | admin | customer_portal
     * @param  int    $created_by  admin user id, NULL for automatic
     * @return array  scheduler_id, devices, skipped
     */
    public function enqueue($type, $customer_no, array $vars = array(), array $opts = array(), $source = NULL, $created_by = NULL)
    {
        try {
            // The caller may already hold the profile id
            $profile_id = isset($opts['profile_id'])
                ? (int) $opts['profile_id']
                : $this->get_profile_id($customer_no);

            unset($opts['profile_id']);

            if (empty($profile_id)) {
                return $this->enqueue_result(NULL, 0, 'no_profile');
            }

            $devices = $this->get_active_tokens($profile_id);

            // No app installed
            if (empty($devices)) {
                if (! $this->config_flag('queue_zero_device_rows', FALSE)) {
                    return $this->enqueue_result(NULL, 0, 'no_devices');
                }
            }

            $dedupe_key = isset($opts['dedupe_key']) ? $opts['dedupe_key'] : NULL;
            unset($opts['dedupe_key']);   // stored in its own column, not in push_opts

            //send_at schedules first delivery for later (billing cron); NULL = send now
            $send_at = isset($opts['send_at']) ? $opts['send_at'] : NULL;
            unset($opts['send_at']);

            if ($send_at !== NULL && strtotime($send_at) === FALSE) {
                log_message('error', '[push] invalid send_at "' . $send_at . '" for type=' . $type . ' - sending now instead');
                $send_at = NULL;
            }

            // Dedupe check before insert
            if ($dedupe_key !== NULL && ($existing = $this->find_by_dedupe($dedupe_key)) !== NULL) {
                return $this->enqueue_result($existing, 0, 'duplicate');
            }

            // Render BEFORE inserting anything
            $default_copy = $this->push_template->render($type, $vars, $opts);

            if ($default_copy === NULL) {
                log_message('error', '[push] enqueue aborted, render failed type=' . $type
                    . ' customer=' . $customer_no);

                return $this->enqueue_result(NULL, 0, 'render_failed');
            }

            $this->db->trans_start();

            $this->db->query(
                "INSERT INTO `{$this->t_scheduler}`
				    (push_type, customer_no, profile_id, push_title, push_msg,
				     push_vars, push_opts, dedupe_key, push_status, devices,
				     source, created_by, created_date)
				 VALUES (?,?,?,?,?,?,?,?,'P',?,?,?,NOW())",
                array(
                    $type,
                    $customer_no,
                    $profile_id,
                    $default_copy['title'],
                    $default_copy['body'],
                    json_encode($vars),
                    json_encode($opts),
                    $dedupe_key,
                    count($devices),
                    $source,
                    $created_by,
                )
            );

            $scheduler_id = (int) $this->db->insert_id();

            foreach ($devices as $device) {
                // Per-device copy
                $idiom  = $this->idiom_for($device);
                $copy   = $default_copy;

                if ($idiom !== NULL) {
                    $device_copy = $this->push_template->render($type, $vars, array_merge($opts, array('lang' => $idiom)));

                    if ($device_copy !== NULL) {
                        $copy = $device_copy;
                    } else {
                        log_message('error', '[push] no copy for type=' . $type
                            . ' idiom=' . $idiom . ' - falling back to default language');
                    }
                }

                $this->db->query(
                    "INSERT INTO `{$this->t_outgoing}`
					    (scheduler_id, device_uuid, firebase_token, lang,
					     push_title, push_msg,
					     push_status, push_attempt, next_attempt_on, created_date)
					 VALUES (?,?,?,?,?,?,'P',0,?,NOW())",
                    array(
                        $scheduler_id,
                        $device['device_uuid'],
                        $device['firebase_token'],
                        isset($device['lang']) ? $device['lang'] : NULL,
                        $copy['title'],
                        $copy['body'],
                        $send_at,
                    )
                );
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                log_message('error', '[push] enqueue rolled back type=' . $type . ' customer=' . $customer_no);
                return $this->enqueue_result(NULL, 0, 'db_error');
            }

            // Zero devices with queueing enabled: nothing to deliver, so the row is final immediately
            if (empty($devices)) {
                $this->set_scheduler_status($scheduler_id, 'S');
            }

            return $this->enqueue_result($scheduler_id, count($devices), NULL);
        } catch (Exception $e) {
            log_message('error', '[push] enqueue failed type=' . $type
                . ' customer=' . $customer_no . ': ' . $e->getMessage());

            return $this->enqueue_result(NULL, 0, 'exception');
        }
    }

    /**
     * Queue id for a dedupe key, or NULL.
     */
    public function find_by_dedupe($dedupe_key)
    {
        $row = $this->db
            ->query("SELECT scheduler_id FROM `{$this->t_scheduler}` WHERE dedupe_key = ? LIMIT 1", array($dedupe_key))
            ->row_array();

        return empty($row['scheduler_id']) ? NULL : (int) $row['scheduler_id'];
    }

	// ==================================================================
	// CLAIMING
	// ==================================================================

    /**
     * Atomically claim due deliveries for this worker.
     *
     * Two statements, in this order, and the order matters:
     *
     *   1. UPDATE ... SET push_status='R', claim_id=<uuid>  (atomic)
     *   2. SELECT ... WHERE claim_id=<uuid>
     *
     * Without claim_id a second overlapping cron run could SELECT the same
     * 'R' rows and send the same notification twice.
     *
     * @param  int   $limit
     * @param  int   $scheduler_id  optional - claim one notification's rows only,
     *                          used by the immediate-dispatch path
     * @return array rows joined with their push_scheduler parent
     */
    public function claim($limit = 50, $scheduler_id = NULL)
    {
        $limit    = max(1, (int) $limit);
        $claim_id = uniqid('c', TRUE);

        $sql = "UPDATE `{$this->t_outgoing}`
		           SET push_status = 'R', claim_id = ?, modified_date = NOW()
		         WHERE push_status = 'P'
		           AND (next_attempt_on IS NULL OR next_attempt_on <= NOW())";

        $binds = array($claim_id);

        if ($scheduler_id !== NULL) {
            $sql    .= " AND scheduler_id = ?";
            $binds[] = (int) $scheduler_id;
        }

        // LIMIT is inlined, not bound
        $sql .= " ORDER BY outgoing_id LIMIT {$limit}";

        $this->db->query($sql, $binds);

        if ((int) $this->db->affected_rows() === 0) {
            return array();
        }

        return $this->db->query(
            "SELECT o.*, q.push_type, q.customer_no, q.profile_id,
			        q.push_vars, q.push_opts, q.source
			   FROM `{$this->t_outgoing}` o
			   JOIN `{$this->t_scheduler}` q ON q.scheduler_id = o.scheduler_id
			  WHERE o.claim_id = ?
			  ORDER BY o.outgoing_id",
            array($claim_id)
        )->result_array();
    }

    /**
     * Return rows stuck in 'R' to 'P'.
     *
     * A row is stuck when the process that claimed it died mid-send - PHP
     * timeout, fatal, server restart. Without this they sit in 'R' forever
     * and the customer never gets the notification.
     *
     * @return int rows released
     */
    public function release_stuck($minutes = 10)
    {
        $minutes = max(1, (int) $minutes);

        $this->db->query(
            "UPDATE `{$this->t_outgoing}`
			    SET push_status = 'P', claim_id = NULL, modified_date = NOW()
			  WHERE push_status = 'R'
			    AND modified_date < DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            array($minutes)
        );

        $released = (int) $this->db->affected_rows();

        if ($released > 0) {
            log_message('error', '[push] released ' . $released . ' stuck delivery row(s) - a worker died mid-send');
        }

        return $released;
    }

	// ==================================================================
	// RESULTS
	// ==================================================================

    /**
     * Record the outcome of one delivery attempt.
     *
     * Decides retry vs give up, using the schedule from sys_config.
     *
     * @param  array $row       the claimed row
     * @param  array $result    Fcm_http_client::send() result
     * @return string           new status: S, P (will retry) or F
     */
    public function record_attempt(array $row, array $result)
    {
        $outgoing_id = (int) $row['outgoing_id'];
        $attempt     = (int) $row['push_attempt'] + 1;

        if (! empty($result['ok'])) {
            $this->db->query(
                "UPDATE `{$this->t_outgoing}`
				    SET push_status = 'S', push_attempt = ?, push_sent_on = NOW(),
				        claim_id = NULL, error_code = NULL, error_msg = NULL,
				        modified_date = NOW()
				  WHERE outgoing_id = ?",
                array($attempt, $outgoing_id)
            );

            $this->roll_up((int) $row['scheduler_id']);

            return 'S';
        }

        $code = isset($result['code']) ? substr((string) $result['code'], 0, 50) : '';
        $msg  = isset($result['error']) ? substr((string) $result['error'], 0, 255) : '';

        // Permanent failures never retry
        $permanent = ! empty($result['permanent']);
        $exhausted = $attempt >= $this->max_attempts();

        if ($permanent or $exhausted) {
            $this->db->query(
                "UPDATE `{$this->t_outgoing}`
				    SET push_status = 'F', push_attempt = ?, claim_id = NULL,
				        error_code = ?, error_msg = ?, modified_date = NOW()
				  WHERE outgoing_id = ?",
                array($attempt, $code, $msg, $outgoing_id)
            );

            $this->roll_up((int) $row['scheduler_id']);

            return 'F';
        }

        $delay = $this->retry_delay($attempt);

        $this->db->query(
            "UPDATE `{$this->t_outgoing}`
			    SET push_status = 'P', push_attempt = ?, claim_id = NULL,
			        next_attempt_on = DATE_ADD(NOW(), INTERVAL ? MINUTE),
			        error_code = ?, error_msg = ?, modified_date = NOW()
			  WHERE outgoing_id = ?",
            array($attempt, $delay, $code, $msg, $outgoing_id)
        );

        return 'P';
    }

    /**
     * Persist copy rendered lazily at dispatch time.
     *
     * Only for rows enqueued by another application (the Customer Portal),
     * which cannot render because the registry and language files live
     * here. Writing it back means a retry sends identical content and
     * push_msg stays an exact record of what was delivered.
     */
    public function store_copy($outgoing_id, $scheduler_id, $title, $body)
    {
        $this->db->query(
            "UPDATE `{$this->t_outgoing}`
			    SET push_title = ?, push_msg = ?, modified_date = NOW()
			  WHERE outgoing_id = ?",
            array($title, $body, (int) $outgoing_id)
        );

        $this->db->query(
            "UPDATE `{$this->t_scheduler}`
			    SET push_title = ?, push_msg = ?, modified_date = NOW()
			  WHERE scheduler_id = ?
			    AND (push_title IS NULL OR push_title = '')",
            array($title, $body, (int) $scheduler_id)
        );
    }

    /**
     * Recompute a scheduler row's status from its deliveries.
     *
     *   any device still P or R  -> P  (work outstanding)
     *   at least one S           -> S  (the customer was reached)
     *   otherwise                -> F  (every device failed)
     */
    public function roll_up($scheduler_id)
    {
        $row = $this->db->query(
            "SELECT
			    SUM(push_status IN ('P','R')) AS outstanding,
			    SUM(push_status = 'S')        AS succeeded
			  FROM `{$this->t_outgoing}` WHERE scheduler_id = ?",
            array((int) $scheduler_id)
        )->row_array();

        if (empty($row)) {
            return;
        }

        if ((int) $row['outstanding'] > 0) {
            $status = 'P';
        } elseif ((int) $row['succeeded'] > 0) {
            $status = 'S';
        } else {
            $status = 'F';
        }

        $this->set_scheduler_status($scheduler_id, $status);
    }

    protected function set_scheduler_status($scheduler_id, $status)
    {
        $this->db->query(
            "UPDATE `{$this->t_scheduler}` SET push_status = ?, modified_date = NOW() WHERE scheduler_id = ?",
            array($status, (int) $scheduler_id)
        );
    }

	// ==================================================================
	// RETRY POLICY
	// ==================================================================

    /**
     * Minutes to wait before the given attempt number.
     *
     * Schedule comes from sys_config (category 'push', key 'retry_schedule'),
     * default '1,5,15,60'.
     */
    public function retry_delay($attempt)
    {
        $schedule = $this->config_value('retry_schedule', '1,5,15,60');
        $parts = array();

        foreach (explode(',', (string) $schedule) as $part) {
            $part = trim($part);

            if ($part !== '' && is_numeric($part)) {
                $parts[] = max(0, (int) $part);
            }
        }

        if (empty($parts)) {
            $parts = array(1, 5, 15, 60);
        }

        $index = max(0, (int) $attempt - 1);

        return isset($parts[$index]) ? $parts[$index] : end($parts);
    }

    public function max_attempts()
    {
        return max(1, (int) $this->config_value('max_attempts', 4));
    }

	// ==================================================================
	// REPORTING
	// ==================================================================

    /**
     * One notification and every delivery attempt for it.
     */
    public function get_detail($scheduler_id)
    {
        $scheduler = $this->db
            ->query("SELECT * FROM `{$this->t_scheduler}` WHERE scheduler_id = ?", array((int) $scheduler_id))
            ->row_array();

        if (empty($scheduler)) {
            return NULL;
        }

        $scheduler['deliveries'] = $this->db
            ->query(
                "SELECT 
					po.outgoing_id, 
					LEFT(po.device_uuid, 12) AS device_uuid,
					po.lang,
					po.push_title,
					po.push_msg,
					po.push_status,
					po.push_attempt,
					po.next_attempt_on,
					po.push_sent_on,
					po.error_code,
					po.error_msg,
					po.created_date,
					po.modified_date,
					pa.platform
				FROM `{$this->t_outgoing}` po
				LEFT JOIN `profile_app` pa
					ON po.device_uuid = pa.device_uuid
				WHERE po.scheduler_id = ? 
				ORDER BY po.outgoing_id",
                array((int) $scheduler_id)
            )
            ->result_array();

        return $scheduler;
    }

	// ==================================================================
	// LISTING
	// ==================================================================

    /**
     * Get push notification list for the scheduler screen.
     *
     * @return array row, total_row
     */
    public function get_push_list($txt_search = '', $date_from = '', $date_to = '', $push_status = 'all', $push_type = 'all', $source = 'all', $page_item_no = 0)
    {
        $where = '';
        $binds = array();

        if ($txt_search != '') {
            $search = '%' . $this->escape_like($txt_search) . '%';

            $where .= " AND (
				s.customer_no LIKE ? ESCAPE '\\\\'
				OR s.push_type LIKE ? ESCAPE '\\\\'
				OR s.push_title LIKE ? ESCAPE '\\\\'
			) ";

            $binds[] = $search;
            $binds[] = $search;
            $binds[] = $search;
        }

        if ($push_type != '' && $push_type != 'all') {
            $where  .= " AND s.push_type = ? ";
            $binds[] = $push_type;
        }

        if ($push_status != '' && $push_status != 'all') {
            $where  .= " AND s.push_status = ? ";
            $binds[] = $push_status;
        }

        if ($source != '' && $source != 'all') {
            $where  .= " AND s.source = ? ";
            $binds[] = $source;
        }

        if ($date_from != '') {
            $where  .= " AND s.created_date >= ? ";
            $binds[] = $date_from . ' 00:00:00';
        }

        if ($date_to != '') {
            $where  .= " AND s.created_date <= ? ";
            $binds[] = $date_to . ' 23:59:59';
        }

        $query_str = "SELECT COUNT(*) AS total_row FROM `{$this->t_scheduler}` s WHERE 1=1 {$where} ";
        $query     = $this->db->query($query_str, $binds);
        $total_row = $query->row_array();

        $limit  = (int) ($_SESSION['config']['max_page_item'] ?? 20);
        $offset = (int) $page_item_no;

        $query_str = "SELECT s.*,
						(SELECT COUNT(*) FROM `{$this->t_outgoing}` o WHERE o.scheduler_id = s.scheduler_id) AS deliveries,
						(SELECT COUNT(*) FROM `{$this->t_outgoing}` o WHERE o.scheduler_id = s.scheduler_id AND o.push_status = 'S') AS delivered,
						(SELECT COUNT(*) FROM `{$this->t_outgoing}` o WHERE o.scheduler_id = s.scheduler_id AND o.push_status IN ('P','R')) AS outstanding
					FROM `{$this->t_scheduler}` s
					WHERE 1=1 {$where}
					ORDER BY s.scheduler_id DESC
					LIMIT {$limit} OFFSET {$offset} ";

        $query = $this->db->query($query_str, $binds);

        return array(
            'row'       => $query->result_array(),
            'total_row' => (int) $total_row['total_row'],
        );
    }

    //escape LIKE wildcard so searching "50%" does not match everything
    protected function escape_like($value)
    {
        return str_replace(array('\\', '%', '_'), array('\\\\', '\%', '\_'), (string) $value);
    }

	// ==================================================================
	// INTERNALS
	// ==================================================================

    /**
     * sys_config overlay, cached per request. Falls back silently so a
     * missing table or row cannot stop the queue working.
     */
    protected function config_value($key, $default = NULL)
    {
        if ($this->cfg === NULL) {
            $this->cfg = array();

            try {
                $rows = $this->db
                    ->query("SELECT `key`, `val` FROM `{$this->t_cfg}` WHERE `category` = 'push'")
                    ->result_array();

                $this->cfg = array_column($rows, 'val', 'key');
            } catch (Exception $e) {
                log_message('error', '[push] could not read sys_config: ' . $e->getMessage());
            }
        }

        return (isset($this->cfg[$key]) && $this->cfg[$key] !== '') ? $this->cfg[$key] : $default;
    }

    protected function config_flag($key, $default = FALSE)
    {
        $this->config->load('push', TRUE, TRUE);
        $cfg = (array) $this->config->item('push', 'push');

        return isset($cfg[$key]) ? (bool) $cfg[$key] : $default;
    }

    /**
     * CodeIgniter language folder for a device, or NULL to use the default.
     */
    protected function idiom_for(array $device)
    {
        if (empty($device['lang'])) {
            return NULL;
        }

        $this->config->load('push', TRUE, TRUE);
        $cfg = (array) $this->config->item('push', 'push');
        $map = isset($cfg['lang_map']) ? (array) $cfg['lang_map'] : array();

        $locale = trim($device['lang']);

        if (isset($map[$locale])) {
            return $map[$locale];
        }

        $base = strtok($locale, '_-');

        return isset($map[$base]) ? $map[$base] : NULL;
    }

    protected function enqueue_result($scheduler_id, $devices, $skipped)
    {
        return array(
            'scheduler_id' => $scheduler_id,
            'devices'  => (int) $devices,
            'skipped'  => $skipped,
        );
    }
}
