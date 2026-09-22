<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

include_once(APPPATH . 'core/DataPage_Controller.php');
class Push extends DataPage_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->helper('custom_helper');
		$this->load->library(array('form_validation', 'session', 'upload'));
		$this->load->helper(array('url', 'html', 'form'));

		check_acl('push');
		$this->load->model('push_model');
		$this->load->library('push_service');
		$this->load->library('push_template');
	}

	public function index()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'date_from' => '',
			'date_to' => '',
			'push_status' => 'all',
			'push_type' => 'all',
			'source' => 'all',
		];
		$return = get_filtered_ajax_data('push_filter', $default_data, $post_data);

		$data = $return['data'];

		$data['page_title'] 	= 'Push Notification';
		$data['form_action'] 	= base_url('push');
		$data['row_html'] 		= $this->push_rows(1);
		$data['type_list'] 		= $this->enabled_types();
		$data['can_manage'] 	= check_acl('push', 'M', false);
		$data['msg'] 			= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('push/index', $data);
		$this->load->view('templates/footer');
	}

	public function push_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'date_from' => '',
			'date_to' => '',
			'push_status' => 'all',
			'push_type' => 'all',
			'source' => 'all',
		];
		$return = get_filtered_ajax_data('push_filter', $default_data, $post_data);

		$data = $return['data'];

		$result = $this->push_model->get_push_list($data['txt_search'], $data['date_from'], $data['date_to'], $data['push_status'], $data['push_type'], $data['source'], $data['page_item_no']);

		foreach ($result['row'] as $key => &$val) {
			$val['created_date'] = datetime_toggle($val['created_date'], $_SESSION['config']['datetime_format']);

			//copy is null until the worker renders it, customer portal rows arrive that way
			$result['row'][$key]['preview'] = ($val['push_title'] == '')
				? '<span class="text-muted">- (Not rendered yet)</span>'
				: htmlspecialchars($val['push_title'], ENT_QUOTES, 'UTF-8');

			//delivered/total so a partial success is visible, eg 1/2
			$result['row'][$key]['delivery'] = $val['delivered'] . '/' . $val['deliveries'];

			if ($val['push_status'] == 'S') {
				$result['row'][$key]['status'] = "<span class='badge badge-success'>Success</span>";
			} else if ($val['push_status'] == 'F') {
				$result['row'][$key]['status'] = "<span class='badge badge-red'>Failed</span>";
			} else if ($val['push_status'] == 'R') {
				$result['row'][$key]['status'] = "<span class='badge badge-grey'>Sending</span>";
			} else {
				$result['row'][$key]['status'] = "<span class='badge badge-blue'>Pending</span>";
			}
		}

		$data['pagination'] 	= paginationSettingsAjax('push', $result['total_row'], $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['msg'] 			= $this->msg;
		$data['row_data'] 		= $result['row'];

		// Prevent filter values from overriding row values in the parser
		unset(
			$data['push_type'],
			$data['source'],
			$data['push_status'],
			$data['txt_search'],
			$data['date_from'],
			$data['date_to']
		);

		$html = $this->parser->parse('push/push_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}
	}

	public function push_detail($scheduler_id = 0)
	{
		$input_data = $this->push_model->get_detail($scheduler_id);

		if (empty($input_data)) {
			$this->session->set_flashdata("warning_msg", 'Invalid Access.');
			redirect('push');
		}

		$input_data['created_date'] = datetime_toggle($input_data['created_date'], $_SESSION['config']['datetime_format']);

		if ($input_data['push_status'] == 'S') {
			$data['status'] = "<span class='badge badge-success'>Success</span>";
		} else if ($input_data['push_status'] == 'F') {
			$data['status'] = "<span class='badge badge-red'>Failed</span>";
		} else if ($input_data['push_status'] == 'R') {
			$data['status'] = "<span class='badge badge-grey'>Sending</span>";
		} else {
			$data['status'] = "<span class='badge badge-blue'>Pending</span>";
		}

		foreach ($input_data['deliveries'] as $key => $val) {
			// Device: first 12 char + ...
			$input_data['deliveries'][$key]['device_uuid'] = ($val['device_uuid'] == '') ? '-' : substr($val['device_uuid'], 0, 12) . '...';
			// Dates
			$input_data['deliveries'][$key]['push_sent_on'] = ($val['push_sent_on'] == '') ? '-' : datetime_toggle($val['push_sent_on'], $_SESSION['config']['datetime_format']);
			$input_data['deliveries'][$key]['next_attempt_on'] = ($val['next_attempt_on'] == '') ? '-' : datetime_toggle($val['next_attempt_on'], $_SESSION['config']['datetime_format']);

			if ($val['push_status'] == 'S') {
				$input_data['deliveries'][$key]['status'] = $result['row'][$key]['status'] = "<span class='badge badge-success'>Success</span>";
			} else if ($val['push_status'] == 'F') {
				$input_data['deliveries'][$key]['status'] = $result['row'][$key]['status'] = "<span class='badge badge-red'>Failed</span>";
			} else if ($val['push_status'] == 'R') {
				$input_data['deliveries'][$key]['status'] = $result['row'][$key]['status'] = "<span class='badge badge-grey'>Sending</span>";
			} else {
				$input_data['deliveries'][$key]['status'] = $result['row'][$key]['status'] = "<span class='badge badge-blue'>Pending</span>";
			}
		}

		$data['input'] 		= $input_data;
		$data['can_manage'] = check_acl('push', 'M', false);
		$data['page_title'] = 'Push Notification Detail';
		$data['msg'] 		= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->load->view('push/push_detail', $data);
		$this->load->view('templates/footer');
	}

	private function enabled_types()
	{
		$out = array();

		foreach ((array) $this->push_template->get_types() as $key => $def) {
			if (! empty($def['enabled'])) {
				$out[$key] = $def;
			}
		}

		return $out;
	}

	// ==================================================================
	// TEST
	// ==================================================================
	/**
	 * TESTING ONLY
	 * The real end-to-end send, throught the same entry point other modules will use.
	 */
	public function test_notify($customer_no = '', $type = 'bill_reminder')
	{
		if ($customer_no === '') {
			echo 'Usage: php index.php push_test notify <customer_no> [type]' . '<br><br>';
			return;
		}

		echo '=== Live send: ' . $type . ' -> ' . $customer_no . ' ===' . '<br><br>';

		$def = $this->push_template->get_type($type);
		$vars = array();

		$samples = array(
			'CUSTOMER_NO' => $customer_no,
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

		foreach ((isset($def['vars']) ? $def['vars'] : array()) as $var) {
			if (isset($samples[$var])) {
				$vars[$var] = $samples[$var];
			} else {
				log_message(
					'error',
					'[push_test] no sample value for %' . $var . '% - add one to test_notify()'
				);

				$vars[$var] = 'SAMPLE_' . $var;
			}
		}

		// Live send
		$r = $this->push_service->notify_customer(
			$customer_no,
			$type,
			$vars,
			array('related_id' => 'SAMPLE1')
		);

		// Result
		echo 'devices : ' . (isset($r['devices']) ? $r['devices'] : 0) . '<br>';
		echo 'sent    : ' . (isset($r['sent']) ? $r['sent'] : 0) . '<br>';
		echo 'failed  : ' . (isset($r['failed']) ? $r['failed'] : 0) . '<br>';
		echo 'pruned  : ' . (isset($r['pruned']) ? $r['pruned'] : 0) . '<br>';
		echo 'skipped : ' . (
			isset($r['skipped']) && $r['skipped'] !== NULL
			? $r['skipped']
			: '(none)'
		) . '<br><br>';

		// Explain skip reason inline
		if (! empty($r['skipped'])) {
			$reasons = array(
				'disabled' => "Push is globally off. Set push.enabled = TRUE in config/push.php,\nor insert a sys_config row: category='push', key='push_enabled', val='1'.",
				'type_disabled' => "This type has 'enabled' => FALSE in config/push_types.php.",
				'unknown_type' => "No such type in config/push_types.php - check the spelling.",
				'not_whitelisted' => "push.whitelist is non-empty and does not include this customer_no.\nThat is rollout Phase 2 behaviour. Empty the whitelist to send to everyone.",
				'no_profile' => "customer_no did not resolve to a profile_id.\nRun: php index.php push_test devices <customer_no>",
				'no_devices' => "The profile has no push-eligible device. Expected when the app is not\ninstalled - this is the cheap early return that keeps cron cost near zero.",
				'render_failed' => "The template did not render. Run: php index.php push_test types",
				'budget_exceeded' => "The per-process time budget is spent. Only meaningful inside a long cron.",
				'duplicate' => "A row with the same dedupe_key already exists. For the dedupe test\nthis is the PASS condition.",
				'db_error' => "The enqueue transaction rolled back. Check application/logs for the\nSQL error - most likely push_scheduler or push_outgoing is missing a\ncolumn from the Step 11 migration.",
				'exception' => "Something threw inside enqueue(). Check application/logs.",
			);

			$reason = isset($reasons[$r['skipped']])
				? $reasons[$r['skipped']]
				: 'Unrecognised skip reason: ' . $r['skipped'];

			echo PHP_EOL;
			echo $reason . '<br><br>';
		}

		// Pruned token notice
		if (isset($r['pruned']) && $r['pruned'] > 0) {
			echo PHP_EOL;
			echo 'A token was pruned. That is correct behaviour if the device' . '<br><br>';
			echo 'was reinstalled or logged out - re-open the app to re-register.' . '<br><br>';
		}
	}

	public function test_types()
	{
		$types = $this->push_template->get_types();
		if (empty($types)) {
			echo 'FAILED: type registry is empty - check config/push_types.php' . '<br><br>';
			return;
		}

		//effective configured languages
		$cfg = (array) $this->config->item('push', 'push');
		$map = isset($cfg['lang_map']) ? (array) $cfg['lang_map'] : array();

		$idioms = array_values(array_unique(array_values($map)));

		if (empty($idioms)) {
			$idioms = array(
				isset($cfg['default_lang'])
					? $cfg['default_lang']
					: 'english'
			);
		}

		echo '<h2> === Type Registry (' . count($types) . ' types x ' . count($idioms) . ' languages) ===</h2>';

		echo '<p><strong>Languages:</strong> '
			. implode(', ', $idioms)
			. '</p>';

		$problems = 0;

		/*
	 * Sample values for every possible template variable.
	 *
	 * CUSTOMER_NO is deliberately C10023 here because types() is a
	 * render-only test and does not receive a customer_no argument.
	 */
		$samples = array(
			'CUSTOMER_NO' => '8900000592',
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

		foreach ($types as $key => $def) {
			/*
		 * Build sample vars directly from the type's declared vars.
		 */
			$vars = array();

			foreach ((isset($def['vars']) ? $def['vars'] : array()) as $var) {
				if (isset($samples[$var])) {
					$vars[$var] = $samples[$var];
				} else {
					//Never silently render an undeclared variable as empty.
					log_message(
						'error',
						'[push_test] no sample value for %' . $var . '% - add one to types()'
					);

					$vars[$var] = 'SAMPLE_' . $var;
				}
			}

			echo '<h3>[' . $key . ']</h3>'
				. (empty($def['enabled']) ? 'DISABLED' : 'enabled')
				. '  channel=' . (isset($def['channel']) ? $def['channel'] : '?')
				. '  ttl=' . (isset($def['ttl']) ? $def['ttl'] : '?')
				. '<br><br>';

			/*
		 * Types with label_key need to be rendered once for every
		 * available status code.
		 */
			$codes = array();

			if (! empty($def['label_key'])) {
				foreach ($idioms as $idiom) {
					foreach (
						$this->push_template->get_status_labels(
							$def['label_key'],
							$idiom
						) as $code => $unused
					) {
						$codes[(string) $code] = TRUE;
					}
				}

				$codes = array_keys($codes);
				sort($codes);

				if (empty($codes)) {
					$problems++;

					echo '  !! label_key "' . $def['label_key']
						. '" declared but no labels found' . '<br><br>';

					continue;
				}
			}

			/*
		 * Normal types render once.
		 * Status-label types render once per discovered status code.
		 */
			$passes = empty($codes) ? array(NULL) : $codes;

			foreach ($passes as $code) {
				$pass_vars = $vars;

				if ($code !== NULL) {
					$pass_vars['STATUS_CODE'] = $code;

					echo '<br><strong>status code: ' . $code . '</strong><br>';
				}

				foreach ($idioms as $idiom) {
					$r = $this->push_template->render(
						$key,
						$pass_vars,
						array(
							'customer_no' => '8900000592',
							'related_id'  => 'SAMPLE1',
							'lang'        => $idiom,
						)
					);

					$indent = ($code === NULL) ? 2 : 4;
					$label  = str_repeat(' ', $indent)
						. str_pad($idiom, 20);

					$cont = str_repeat(
						' ',
						$indent + 20 + 2
					);

					if ($r === NULL) {
						$problems++;

						echo $label
							. '!! render returned NULL'
							. (
								$code === NULL
								? ' - missing language file or keys'
								: ' - missing label for code ' . $code
							)
							. '<br>';

						continue;
					}

					/*
				 * UTF-8-safe length check.
				 */
					$title_len = function_exists('mb_strlen')
						? mb_strlen($r['title'], 'UTF-8')
						: strlen($r['title']);

					$body_len = function_exists('mb_strlen')
						? mb_strlen($r['body'], 'UTF-8')
						: strlen($r['body']);

					echo '<strong>' . $label . '</strong>'
						. '<br>'
						. $r['title']
						. '  (' . $title_len . ')'
						. '<br>';

					echo $cont
						. $r['body']
						. '  (' . $body_len . ')'
						. '<br>';

					/*
				 * Empty body is always a problem.
				 */
					if (trim($r['body']) === '') {
						$problems++;

						echo $cont . '!! EMPTY BODY' . '<br>';
					}

					/*
				 * Validate configured title/body limits.
				 */
					$max_t = isset($cfg['title_max_length'])
						? (int) $cfg['title_max_length']
						: 50;

					$max_b = isset($cfg['body_max_length'])
						? (int) $cfg['body_max_length']
						: 200;

					if ($title_len > $max_t || $body_len > $max_b) {
						$problems++;

						echo $cont
							. '!! OVER LENGTH - will be truncated with "..."'
							. '<br>';
					}
				}
			}
		}

		echo PHP_EOL;

		if ($problems === 0) {
			echo 'PASS - every type renders cleanly in every language.'
				. '<br>';
		} else {
			echo 'FAILED - ' . $problems . ' problem(s) above.'
				. '<br>';
		}
	}
}
