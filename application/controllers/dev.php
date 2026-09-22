<?php

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Dev extends DataPage_Controller {

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set('Asia/Kuala_Lumpur');
        $this->load->model('common_model');
        $this->load->model('email_model');
        $this->load->model('bill_model');
        $this->load->helper('custom_helper');
		$this->load->library('whatsapp_template');
    }
	
	function production_only_cli() {
		if(ENVIRONMENT === 'production' && !$this->input->is_cli_request()) {
			show_error('Direct access is not allowed');
		}
	}

    function test_html_email()
    {
			$sent_mail = $this->email_model->generate_html_email(
				'test by infonal',
				'test by infonal', 
				'',
				'valerie@infonal.com.my',
				'Itelco',
				'',
				'',
				''
			);
    }

    function test_get_cycle_month_array($first_activated_date, $bill_cycle_month)
    {
    	$this->load->model('bill_model');
    	$return = $this->bill_model->get_cycle_month_array($first_activated_date, $bill_cycle_month);

    	print_r($return);
    }

    public function test_bill_prep($customer_no = '')
	{
		$this->load->model('bill_model');

		$date_arr = [
			'last_month_ini' => '2026-08-01',
			'last_month_end' => '2026-08-31',
			'month_ini'      => '2026-09-01',
			'month_end'      => '2026-09-30',
		];

		$cust_bill = $this->bill_model->bill_preparation($customer_no, $date_arr);

		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">

			<title>Bill Preparation Test</title>

			<link
				href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
				rel="stylesheet"
			>

			<style>
				body {
					background: #f5f6f8;
					font-size: 14px;
				}

				.customer-card {
					border: 0;
					border-radius: 12px;
				}

				.summary-box {
					background: #f8f9fa;
					border-radius: 8px;
					padding: 12px 15px;
					height: 100%;
				}

				.summary-label {
					color: #6c757d;
					font-size: 12px;
					margin-bottom: 4px;
				}

				.summary-value {
					font-size: 17px;
					font-weight: 600;
				}

				.section-title {
					font-size: 14px;
					font-weight: 600;
					margin-bottom: 12px;
				}

				.info-label {
					color: #6c757d;
					font-size: 12px;
				}

				.debug-pre {
					background: #212529;
					color: #f8f9fa;
					padding: 15px;
					border-radius: 8px;
					max-height: 500px;
					overflow: auto;
					font-size: 12px;
				}
			</style>
		</head>

		<body>

		<div class="container-fluid px-4 py-4">

			<div class="mb-4">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<h3 class="mb-1">Bill Preparation Test</h3>

						<div class="text-muted">
							<?= count($cust_bill) ?> customer(s)
						</div>
					</div>

					<div class="text-end">
						<div class="text-muted small">Bill Month</div>

						<div class="fw-bold">
							<?= htmlspecialchars($date_arr['month_ini']) ?>
							-
							<?= htmlspecialchars($date_arr['month_end']) ?>
						</div>
					</div>
				</div>
			</div>

			<?php foreach ($cust_bill as $customer_no => $bill): ?>

				<?php
				$extra = $bill['extra_detail'] ?? [];
				$charges = $bill['charge_detail'] ?? [];
				$payments = $bill['payment_num'] ?? [];
				?>

				<div class="card customer-card shadow-sm mb-4">

					<!-- Header -->
					<div class="card-header bg-white py-3">
						<div class="d-flex justify-content-between align-items-start">

							<div>
								<div class="d-flex align-items-center gap-2 mb-1">

									<h5 class="mb-0">
										<?= htmlspecialchars($customer_no) ?>
									</h5>

									<?php if (!empty($bill['bill_by_email'])): ?>
										<span class="badge bg-success">
											Email Billing
										</span>
									<?php endif; ?>

								</div>

								<div class="text-muted">
									<?= htmlspecialchars($bill['pic_name'] ?? '-') ?>

									<?php if (!empty($bill['email_1'])): ?>
										&nbsp;·&nbsp;
										<?= htmlspecialchars($bill['email_1']) ?>
									<?php endif; ?>
								</div>
							</div>

							<div class="text-end">
								<div class="small text-muted">
									Bill Period
								</div>

								<strong>
									<?= htmlspecialchars($bill['bill_period'] ?? '-') ?>
								</strong>
							</div>

						</div>
					</div>

					<div class="card-body">

						<!-- Financial Summary -->
						<div class="row g-3 mb-4">

							<div class="col-md">
								<div class="summary-box">
									<div class="summary-label">
										Previous Balance
									</div>

									<div class="summary-value">
										RM <?= number_format((float) ($bill['previous_balance'] ?? 0), 2) ?>
									</div>
								</div>
							</div>

							<div class="col-md">
								<div class="summary-box">
									<div class="summary-label">
										Charges
									</div>

									<div class="summary-value">
										RM <?= number_format((float) ($bill['charges'] ?? 0), 2) ?>
									</div>
								</div>
							</div>

							<div class="col-md">
								<div class="summary-box">
									<div class="summary-label">
										Tax
									</div>

									<div class="summary-value">
										RM <?= number_format((float) ($bill['tax_charges'] ?? 0), 2) ?>
									</div>
								</div>
							</div>

							<div class="col-md">
								<div class="summary-box">
									<div class="summary-label">
										Payment Received
									</div>

									<div class="summary-value">
										RM <?= number_format((float) ($bill['payment_received'] ?? 0), 2) ?>
									</div>
								</div>
							</div>

							<div class="col-md">
								<div class="summary-box">
									<div class="summary-label">
										Balance
									</div>

									<div class="summary-value">
										RM <?= number_format((float) ($bill['balance'] ?? 0), 2) ?>
									</div>
								</div>
							</div>

						</div>


						<!-- Customer / Package Info -->
						<div class="section-title">
							Billing Information
						</div>

						<div class="row g-3 mb-4">

							<div class="col-md-4">
								<div class="info-label">Package</div>

								<div>
									<?= htmlspecialchars($extra['package_name'] ?? '-') ?>
								</div>
							</div>

							<div class="col-md-2">
								<div class="info-label">Package ID</div>

								<div>
									<?= htmlspecialchars($extra['package'] ?? '-') ?>
								</div>
							</div>

							<div class="col-md-2">
								<div class="info-label">Bill Cycle</div>

								<div>
									<?= htmlspecialchars($extra['bill_cycle_month'] ?? '-') ?>
									month(s)
								</div>
							</div>

							<div class="col-md-2">
								<div class="info-label">Payment Term</div>

								<div>
									<?= htmlspecialchars($extra['payment_term'] ?? '-') ?>
									days
								</div>
							</div>

							<div class="col-md-2">
								<div class="info-label">Unit</div>

								<div>
									<?= htmlspecialchars($extra['bill_unit_no'] ?? '-') ?>
								</div>
							</div>

						</div>


						<!-- Address -->
						<div class="row mb-4">

							<div class="col-md-6">
								<div class="info-label mb-1">
									Billing Address
								</div>

								<div>
									<?= htmlspecialchars($extra['bill_unit_no'] ?? '') ?>
									<?= htmlspecialchars($extra['bill_addr_1'] ?? '') ?>
									<?= htmlspecialchars($extra['bill_addr_2'] ?? '') ?>
									<?= htmlspecialchars($extra['bill_addr_3'] ?? '') ?>

									<br>

									<?= htmlspecialchars($extra['bill_postcode'] ?? '') ?>
									<?= htmlspecialchars($extra['bill_city'] ?? '') ?>,
									<?= htmlspecialchars($extra['bill_state'] ?? '') ?>
								</div>
							</div>

							<div class="col-md-6">
								<div class="info-label mb-1">
									Installation Address
								</div>

								<div>
									<?= htmlspecialchars($extra['inst_unit_no'] ?? '') ?>
									<?= htmlspecialchars($extra['inst_addr1'] ?? '') ?>
									<?= htmlspecialchars($extra['inst_addr2'] ?? '') ?>
									<?= htmlspecialchars($extra['inst_addr3'] ?? '') ?>

									<br>

									<?= htmlspecialchars($extra['inst_postcode'] ?? '') ?>
									<?= htmlspecialchars($extra['inst_city'] ?? '') ?>,
									<?= htmlspecialchars($extra['inst_state'] ?? '') ?>
								</div>
							</div>

						</div>


						<!-- Charge Detail -->
						<div class="section-title">
							Charge Detail
						</div>

						<div class="table-responsive mb-4">

							<table class="table table-sm table-bordered table-hover align-middle mb-0">

								<thead class="table-light">
									<tr>
										<th>Date</th>
										<th>Description</th>
										<th>Remark</th>
										<th>Tax Code</th>
										<th class="text-end">Tax %</th>
										<th class="text-end">Amount</th>
										<th class="text-end">Tax</th>
									</tr>
								</thead>

								<tbody>

								<?php if (!empty($charges)): ?>

									<?php foreach ($charges as $charge): ?>

										<tr>
											<td>
												<?= htmlspecialchars($charge['tranx_date'] ?? '-') ?>
											</td>

											<td>
												<?= htmlspecialchars($charge['adjust_desc'] ?? '-') ?>
											</td>

											<td>
												<?= htmlspecialchars($charge['remark'] ?? '-') ?>
											</td>

											<td>
												<?= htmlspecialchars($charge['tax_code'] ?? '-') ?>
											</td>

											<td class="text-end">
												<?= number_format((float) ($charge['tax_percent'] ?? 0), 2) ?>%
											</td>

											<td class="text-end">
												RM <?= number_format((float) ($charge['amount'] ?? 0), 2) ?>
											</td>

											<td class="text-end">
												RM <?= number_format((float) ($charge['tax_amount'] ?? 0), 2) ?>
											</td>
										</tr>

									<?php endforeach; ?>

								<?php else: ?>

									<tr>
										<td colspan="7" class="text-center text-muted">
											No charge detail
										</td>
									</tr>

								<?php endif; ?>

								</tbody>

							</table>

						</div>


						<!-- Payment -->
						<div class="mb-4">
							<div class="section-title">
								Payment
							</div>

							<?php if (!empty($payments)): ?>

								<?php foreach ($payments as $payment_no): ?>
									<span class="badge bg-secondary me-1">
										#<?= htmlspecialchars($payment_no) ?>
									</span>
								<?php endforeach; ?>

							<?php else: ?>

								<span class="text-muted">
									No payment
								</span>

							<?php endif; ?>

						</div>


						<!-- Raw Data -->
						<details>

							<summary class="text-muted" style="cursor:pointer;">
								Show Raw Data
							</summary>

							<pre class="debug-pre mt-3"><?= htmlspecialchars(print_r($bill, true)) ?></pre>

						</details>

					</div>

				</div>

			<?php endforeach; ?>

		</div>

		</body>
		</html>

		<?php
	}

    function test_lhdn_connection() {
    	$this->load->model('Einvoice_config_model');
    	$login = $this->Einvoice_config_model->login_myinvois_portal();
    	print_r($login);
    }

	function test_doc_send(){

		$this->load->model('docs_log_model');

		$smtp_user	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'from_name' ");
		
		$from_name = ( isset( $smtp_user[0]['val'] ) && $smtp_user[0]['val'] != '' ) ? $smtp_user[0]['val'] : 'no_reply@itelco.net';

		//to_customer
		$send_array = array();
		$send_array['send_type'] = 'to_backend_user'; /* to_customer, to_backend_user or technician */
		$send_array['acc_id'] = 0; /* technically one should be enough, acc_id will point to profile, or if any product will also just join with profile to get customer details */
		$send_array['customer_no'] = 0;
		$send_array['user_id'] = 7;
		$send_array['controller'] = 'cron'; /* which controller calling this function */
		$send_array['doc_id'] = 0; /* if there is attachment or document related, then the id is here */
		$send_array['send_method'] = 'manual'; /* either auto or manual */
		$send_array['acc_name'] = 'Valerie';
		//attachcment related
		$send_array['attachment'] = 'C:\wamp64\www\itelco_files\upload\20250322145904_test2.pdf'; /* attachcment in full path */
		//email
		$send_array['subject'] = 'Test Email'; /* email subject */
		$send_array['body'] = 'Hello World'; /* email body */
		$send_array['from'] = $from_name; /* from field */
		$send_array['email_starter'] = 'This is a Test Email'; /* email body header */
		$send_array['doc_type'] = 'Test Email'; /* what kind of email is this */
		
		//to_backend_user

		//to_technician

		$send_result = $this->docs_log_model->do_send($send_array);

		if ($send_result['status'] == 'succ') {
			echo 'Done send.';
		} else {
			echo $send_result['msg'];
		}
	}

	function test_telegram(){
		$this->load->model('telegram_model');

		$send_array = array();
		$send_array['chat_id'] = 1042845939;
		$send_array['text'] = 'Hello World';
		$send_array['attachment'] = 'C:\wamp64\www\itelco_files\upload\20250324141126_logo-kvwin79.vip.png';
		$send_array['mimetype'] = mime_content_type($send_array['attachment']);

		$pathinfo = pathinfo($send_array['attachment']);
		$send_array['filename'] = $pathinfo['filename'];

		$send_result = $this->telegram_model->send($send_array);

		print_r($send_result);
	}
	
	function test_sms(){
		$this->load->model('sms_outgoing_model');
		$sent_sms = $this->sms_outgoing_model->sms_send( '0165234881', 'testing', 'pgfon_sms_service', 'I1ovemy!sp' );
		_debug_array($sent_sms) ; 
	}

	function run_create_activation_fee($customer_no,$package){
		$this->load->model('customer_model');
		$this->customer_model->create_activation_fee($customer_no,'infonal',$package);
	}
	
	function void_einvoice( $bill_no ) {

		$result = $this->bill_model->void_einvoice($bill_no);
		print_r($result);
		echo "<br>";
		echo "OK";

	}

	function dompdf_test() {

		$this->load->helper(array('dompdf', 'file', 'url'));

		$html = "<html><head><link rel='stylesheet' href='".base_url("css/theme/bootstrap.css?1")."'/></head><body>Hello World</body></html>";

		$data = pdf_create($html, '', false);

		//if(empty($pdf_name)) $pdf_name = 'temp_bill_statement_'.date("Y-m-d_h:i:sa", time());
		$result = write_file('temp/email_scheduler/dompdf_test.pdf', $data);
		return $result;
	}

	function puppet_test() {
		$this->load->helper('puppeteer_helper');
		puppeteer_print_preview('https://google.com', $this->config->item('upload_path').'/temp/pdf/google.pdf', $this->config->item('proj_path'), $this->config->item('chrome_loc'));
	}

	function puppet_landscape_test() {
		$this->load->helper('puppeteer_helper');
		puppeteer_print_preview('https://google.com', $this->config->item('upload_path').'/temp/pdf/google.pdf', $this->config->item('proj_path'), $this->config->item('chrome_loc'), 'landscape');
	}

	public function test_send_whatsapp () 
	{
		$this->load->model('message_scheduler_model');

		$meta_template = $this->whatsapp_template->build(
			'REACTIVATE ACCOUNT',
			[
				'customer_no' => '870000803',
				'customer_name' => 'xiao ming'
			]
		);

		$send_array = [
			'send_type' => 'custom',
			'acc_id' => 0,
			'customer_no' => '870000803',
			'user_id' => 0,
			'controller' => 'cron',
			'doc_id' => 0,
			'send_method' => 'auto',
			'acc_name' => 'Itelco User',
			'subject' => '[FOLLOW WHATSAPP META TEMPLATE]',
			'body' => '[FOLLOW WHATSAPP META TEMPLATE]',
			'from' => 'ITELCO',
			'email_starter' => '',
			'doc_type' => "[REACTIVATE ACCOUNT]",
			'whatsapp_list' => ['601112872896'],
			'meta_template' => $meta_template['meta_template_name'] ?? '',
			'meta_vars' => $meta_template['meta_variable'] ?? []
		];

		$message_data = [
			'message' => "[REACTIVATE ACCOUNT]",
			'msg_type' => 'whatsapp',
			'msg_to' => '601112872896',
			'customer_no' => '870000803',
			'msg_schedule_on' => date('Y-m-d H:i:s')
		];

		$this->message_scheduler_model->insert_new_scheduled_message($message_data, $send_array);

	}

	// TODO: Future enhancement:
	// Add a reminder delivery report for ITELCO users to identify
	// customers whose reminder emails were not sent as expected.
	public function check_overdue_report($secret = '', $run_date = '', $run_customer_no = '')
	{

		if ($secret != 'infonal') {
			show_404();
        	return;
		}

		$this->load->model(['email_model', 'common_model']);

		$run_date  = $run_date ?: date('Y-m-d');
		$run_time  = strtotime($run_date . ' 12:00:00');
		$next_date = date('Y-m-d', strtotime($run_date . ' +1 day'));
		$month     = date('Y-m', strtotime($run_date));
		$prev_month = date('Y-m', strtotime($run_date . ' -1 month'));

		if (date('Y-m-d', strtotime($run_date)) !== $run_date) {
			show_error('Invalid date. Use YYYY-MM-DD.');
		}

		// =========================================================
		// CONFIG
		// =========================================================
		$cfg = array_column(
			$this->common_model->get_table(
				'sys_config',
				'*',
				"`key` IN ('isp_name','reminder_notice','reminder_overdue',
				'suspension_grace_period','reminder_notice2','reminder_overdue2',
				'bill_generate_day')"
			),
			'val',
			'key'
		);

		$isp   = $cfg['isp_name'] ?? '';
		$rn1   = (int)($cfg['reminder_notice'] ?? 0);
		$rn2   = (int)($cfg['reminder_notice2'] ?? 0);
		$ro1   = (int)($cfg['reminder_overdue'] ?? 0);
		$ro2   = (int)($cfg['reminder_overdue2'] ?? 0);
		$grace = (int)($cfg['suspension_grace_period'] ?? 0);
		$bill_day = (int)($cfg['bill_generate_day'] ?? 1);

		// =========================================================
		// EMAIL TITLES
		// =========================================================
		$templates = [
			1 => 'REMINDER BEFORE DUE',
			3 => 'OVERDUE REMINDER',
			6 => 'REMINDER ABOUT SUSPENSION'
		];

		$titles = [];

		foreach ($templates as $stage => $name) {
			$t = $this->email_model->get_email_template_detail(
				" AND template_name = '{$name}' AND is_default = 1 "
			);

			$titles[$stage] = isset($t['email_title'])
				? str_replace('%ISP_NAME%', $isp, $t['email_title'])
				: '';
		}

		$titles[2] = $titles[1];
		$titles[4] = $titles[3];

		// =========================================================
		// EMAIL SCHEDULERS FOR RUN DATE
		// =========================================================
		$scheduler_index = [];

		$schedulers = $this->db->query("
			SELECT scheduler_id, LOWER(TRIM(email_to)) email_to, email_title
			FROM email_scheduler
			WHERE created_date >= ?
			AND created_date < ?
			ORDER BY scheduler_id
		", [
			$run_date . ' 00:00:00',
			$next_date . ' 00:00:00'
		])->result_array();

		foreach ($schedulers as $s) {
			$scheduler_index[$s['email_to']][$s['email_title']][] = $s['scheduler_id'];
		}

		// =========================================================
		// CUSTOMERS
		// =========================================================
		$where = '';
		$bind  = [];

		if ($run_customer_no !== '') {
			$where = ' AND c.customer_no = ? ';
			$bind[] = $run_customer_no;
		}

		$customers = $this->db->query("
			SELECT
				c.profile_id,
				c.customer_no,
				IF(c.category != 'r', p.comp_name, p.acc_name) customer_name,
				p.pic_email_1,
				p.pic_email_2,
				b.bill_date,
				b.balance,
				b.bill_due_date,
				(b.balance - IFNULL(up.unprocessed_amount,0)) updated_balance,
				IFNULL(pay.pay_date,'') last_payment_date,
				c.payment_term

			FROM customer c
			LEFT JOIN profile p ON p.acc_id = c.profile_id

			LEFT JOIN (
				SELECT b1.*
				FROM bill b1
				JOIN (
					SELECT customer_no, MAX(bill_date) max_date
					FROM bill
					GROUP BY customer_no
				) x ON x.customer_no = b1.customer_no
				AND x.max_date = b1.bill_date
			) b ON b.customer_no = c.customer_no

			LEFT JOIN (
				SELECT p1.*
				FROM payment p1
				JOIN (
					SELECT customer_no, MAX(idx) max_idx
					FROM payment
					WHERE bill_type NOT IN (4,11,14)
					GROUP BY customer_no
				) x ON x.customer_no = p1.customer_no
				AND x.max_idx = p1.idx
			) pay ON pay.customer_no = c.customer_no

			LEFT JOIN (
				SELECT customer_no, SUM(amount) unprocessed_amount
				FROM payment
				WHERE processed = 0
				AND bill_type NOT IN (4,11,14)
				GROUP BY customer_no
			) up ON up.customer_no = c.customer_no

			LEFT JOIN (
				SELECT cs1.*
				FROM customer_status cs1
				JOIN (
					SELECT customer_no, MAX(status_id) max_id
					FROM customer_status
					GROUP BY customer_no
				) x ON x.max_id = cs1.status_id
			) cs ON cs.customer_no = c.customer_no

			WHERE c.category IN ('r','b')
			AND IFNULL(cs.status,'P') = 'A'
			{$where}

			ORDER BY c.customer_no
		", $bind)->result_array();

		// =========================================================
		// HELPERS
		// =========================================================
		$bill_anchor = function ($bill_date) use ($bill_day) {
			$ym = date('Y-m', strtotime($bill_date));
			$max = (int)date('t', strtotime($ym . '-01'));
			return $ym . '-' . str_pad(min(max($bill_day, 1), $max), 2, '0', STR_PAD_LEFT);
		};

		$get_bill = function ($customer_no, $month = '', $first_all = false) {
			$sql = "
				SELECT bill_no, bill_date, bill_due_date, balance, payment_received
				FROM bill
				WHERE customer_no = ?
				AND balance > 0
			";

			$bind = [$customer_no];

			if ($month !== '') {
				$sql .= " AND DATE_FORMAT(bill_date,'%Y-%m') = ? ";
				$bind[] = $month;
			}

			$sql .= " ORDER BY bill_date ASC LIMIT 1";

			return $this->db->query($sql, $bind)->row_array();
		};

		$emails_for = function ($r) {
			$emails = [];

			foreach ([$r['pic_email_1'], $r['pic_email_2']] as $email) {
				if ($email && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
					$emails[strtolower(trim($email))] = trim($email);
				}
			}

			$field = $this->db->field_exists('profile_id', 'profile_pic') ? 'profile_id'
				: ($this->db->field_exists('acc_id', 'profile_pic') ? 'acc_id'
				: ($this->db->field_exists('customer_no', 'profile_pic') ? 'customer_no' : ''));

			if ($field) {
				$value = $field === 'customer_no' ? $r['customer_no'] : $r['profile_id'];

				$pics = $this->db->query(
					"SELECT pic_email_1, pic_email_2 FROM profile_pic WHERE {$field} = ?",
					[$value]
				)->result_array();

				foreach ($pics as $p) {
					foreach ([$p['pic_email_1'], $p['pic_email_2']] as $email) {
						if ($email && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
							$emails[strtolower(trim($email))] = trim($email);
						}
					}
				}
			}

			return $emails ?: ['' => ''];
		};

		$report = [];

		// =========================================================
		// REPORT
		// =========================================================
		foreach ($customers as $r) {

			$customer = $r['customer_no'];
			$term     = (int)$r['payment_term'];

			$c1 = $rn1;
			$c2 = $rn1 + $rn2;
			$c3 = $term + $ro1;
			$c4 = $term + $ro1 + $ro2;
			$c5 = $term + $grace;

			$expected = [];
			$expected_bills = [];

			$last_payment = $r['last_payment_date']
				? date('Y-m-d', strtotime($r['last_payment_date']))
				: '';

			// =====================================================
			// EXPECTED STAGE
			// =====================================================
			if ((float)$r['updated_balance'] > 0) {

				if ($last_payment) {

					$last_ts = strtotime($last_payment . ' 12:00:00');

					$days_payment = floor(($run_time - $last_ts) / 86400);

					$prev_start = $prev_month . '-' . str_pad($bill_day, 2, '0', STR_PAD_LEFT);
					$curr_start = $month . '-' . str_pad($bill_day, 2, '0', STR_PAD_LEFT);

					$paid_prev =
						$last_ts >= strtotime($prev_start . ' 00:00:00')
						&& $last_ts <= $run_time;

					$paid_current =
						$last_ts >= strtotime($curr_start . ' 00:00:00')
						&& $last_ts <= $run_time;

					$current_bill = $get_bill($customer, $month);

					$received = $current_bill
						? (float)($current_bill['payment_received'] ?? 0)
						: 0;

					if (!$paid_current) {

						if ($days_payment >= $c3 && !$paid_prev && $received <= 0) {

							$bill = $get_bill($customer, $prev_month);

							if ($bill) {

								$days = floor(
									($run_time - strtotime($bill_anchor($bill['bill_date']) . ' 12:00:00'))
									/ 86400
								);

								if ($days >= $c4) {
									$expected[] = 4;
								} elseif ($days >= $c3) {
									$expected[] = 3;
								}

								if ($days >= $c5) {
									$expected[] = 6;
								}

								$expected_bills[] = $bill['bill_no'];
							}

						} elseif ($current_bill) {

							$days = floor(
								($run_time - strtotime($bill_anchor($current_bill['bill_date']) . ' 12:00:00'))
								/ 86400
							);

							if ($days >= $c1 && $days < $c2) {
								$expected[] = 1;
							} elseif ($days >= $c2 && $days < $c3) {
								$expected[] = 2;
							}

							if ($expected) {
								$expected_bills[] = $current_bill['bill_no'];
							}
						}
					}

				} else {

					$bill = $get_bill($customer);

					if ($bill) {

						$days = floor(
							($run_time - strtotime($bill_anchor($bill['bill_date']) . ' 12:00:00'))
							/ 86400
						);

						if ($days >= $c4) {
							$expected[] = 4;
						} elseif ($days >= $c3) {
							$expected[] = 3;
						} elseif ($days >= $c2) {
							$expected[] = 2;
						} elseif ($days >= $c1) {
							$expected[] = 1;
						}

						if ($days >= $c5) {
							$expected[] = 6;
						}

						if ($expected) {
							$expected_bills[] = $bill['bill_no'];
						}
					}
				}
			}

			$expected = array_values(array_unique($expected));
			sort($expected);

			// =====================================================
			// ACTUAL STAGE FOR RUN DATE
			// =====================================================
			$actual_rows = $this->db->query("
				SELECT bill_no, reminder_type
				FROM reminder_status
				WHERE customer_no = ?
				AND (
					(created_at >= ? AND created_at < ?)
					OR
					(updated_at >= ? AND updated_at < ?)
				)
				ORDER BY reminder_type
			", [
				$customer,
				$run_date . ' 00:00:00',
				$next_date . ' 00:00:00',
				$run_date . ' 00:00:00',
				$next_date . ' 00:00:00'
			])->result_array();

			$actual = array_values(array_unique(array_map(
				'intval',
				array_column($actual_rows, 'reminder_type')
			)));

			sort($actual);

			$actual_bills = array_values(array_unique(
				array_column($actual_rows, 'bill_no')
			));

			// =====================================================
			// RULE 2 + RULE 3
			// =====================================================
			if ($expected === $actual) {

				$status = 'MATCH';

			} elseif (
				empty($expected)
				&& !empty($actual)
				&& $last_payment
				&& $last_payment >= $run_date
			) {

				// Expected "-" but payment was made on/after run date.
				$status = 'PAID';

			} elseif (
				!empty($expected)
				&& empty($actual)
				&& array_intersect($expected, [4, 6])
			) {

				// If Stage 4 / 6 was already recorded before, ignore.
				$history = $this->db->query("
					SELECT id
					FROM reminder_status
					WHERE customer_no = ?
					AND reminder_type IN (4,6)
					LIMIT 1
				", [$customer])->row_array();

				$status = $history ? 'IGNORED' : 'MISMATCH';

			} else {

				$status = 'MISMATCH';
			}

			// =====================================================
			// BILL
			// =====================================================
			$display_bills = $actual_bills ?: $expected_bills;
			$bill_details = [];

			if ($display_bills) {
				$placeholders = implode(',', array_fill(0, count($display_bills), '?'));

				$bill_details = $this->db->query("
					SELECT bill_no, balance
					FROM bill
					WHERE customer_no = ?
					AND bill_no IN ({$placeholders})
				", array_merge([$customer], $display_bills))->result_array();
			}

			// =====================================================
			// PIC + EMAIL SCHEDULER
			// =====================================================
			foreach ($emails_for($r) as $email) {

				$scheduler_ids = [];
				$stages = $actual ?: $expected;

				foreach ($stages as $stage) {

					$title = $titles[$stage] ?? '';
					$key   = strtolower(trim($email));

					if ($key && $title && isset($scheduler_index[$key][$title])) {
						$scheduler_ids = array_merge(
							$scheduler_ids,
							$scheduler_index[$key][$title]
						);
					}
				}

				$scheduler_ids = array_values(array_unique($scheduler_ids));
				sort($scheduler_ids);

				$report[] = [
					'customer_no'      => $customer,
					'name'             => $r['customer_name'],
					'expected'         => $expected,
					'actual'           => $actual,
					'status'           => $status,
					'bills'            => $bill_details,
					'last_payment'     => $last_payment,
					'scheduler_ids'    => $scheduler_ids,
					'email'            => $email
				];
			}
		}

		// =========================================================
		// HTML
		// =========================================================
		echo '
		<!doctype html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Overdue Debug Report</title>
			<style>
				body{font-family:Arial;font-size:13px;padding:20px;background:#f7f7f7}
				h2{margin-bottom:5px}
				.meta{margin-bottom:20px}
				table{width:100%;border-collapse:collapse;background:#fff}
				th,td{border:1px solid #ddd;padding:8px;vertical-align:top}
				th{background:#eee;text-align:left;position:sticky;top:0}
				tr.MATCH{background:#f5fff5}
				tr.MISMATCH{background:#fff1f1}
				tr.PAID{background:#f3f8ff}
				tr.IGNORED{background:#fffbe9}
				.stage,.MISMATCH td:nth-child(4){font-weight:bold}
				a{text-decoration:none}
				a:hover{text-decoration:underline}
			</style>
		</head>
		<body>

		<h2>Overdue Debug Report</h2>

		<div class="meta">
			Run Date: <strong>' . html_escape($run_date) . '</strong>
			&nbsp; | &nbsp;
			Customers: <strong>' . count($customers) . '</strong>
			&nbsp; | &nbsp;
			Rows: <strong>' . count($report) . '</strong>
		</div>

		<table>
		<thead>
			<tr>
				<th>Customer No</th>
				<th>Expected Stage</th>
				<th>Actual Stage</th>
				<th>Status</th>
				<th>Name</th>
				<th>Bill No</th>
				<th>Bill Balance</th>
				<th>Last Payment Date</th>
				<th>Email Scheduler ID</th>
				<th>PIC Email</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($report as $r) {

			$bill_links = [];
			$balances   = [];

			foreach ($r['bills'] as $b) {

				$bill_links[] =
					'<a target="_blank" href="' .
					base_url('bill/bill_detail/' . rawurlencode($r['customer_no'])) .
					'">' . html_escape($b['bill_no']) . '</a>';

				$balances[] =
					html_escape($b['bill_no']) .
					': ' .
					number_format((float)$b['balance'], 2);
			}

			$scheduler_links = [];

			foreach ($r['scheduler_ids'] as $id) {
				$scheduler_links[] =
					'<a target="_blank" href="' .
					base_url('email/add_email/' . $id) .
					'">' . (int)$id . '</a>';
			}

			echo '
			<tr class="' . $r['status'] . '">
				<td>' . html_escape($r['customer_no']) . '</td>
				<td class="stage">' . ($r['expected'] ? implode(', ', $r['expected']) : '-') . '</td>
				<td class="stage">' . ($r['actual'] ? implode(', ', $r['actual']) : '-') . '</td>
				<td>' . $r['status'] . '</td>
				<td>' . html_escape($r['name']) . '</td>
				<td>' . ($bill_links ? implode('<br>', $bill_links) : '-') . '</td>
				<td>' . ($balances ? implode('<br>', $balances) : '-') . '</td>
				<td>' . ($r['last_payment'] ?: '-') . '</td>
				<td>' . ($scheduler_links ? implode('<br>', $scheduler_links) : '-') . '</td>
				<td>' . ($r['email'] ? html_escape($r['email']) : '-') . '</td>
			</tr>';
		}

		echo '
		</tbody>
		</table>
		</body>
		</html>';
	}

	public function dealer_commission_report($secret = '', $run_date = '')
	{
		if ($secret != 'infonal') {
			show_404();
			return;
		}

		$this->load->model([
			'dealer_model',
			'customer_model',
		]);

		$run_date = $run_date ?: date('Y-m-d');

		$dt = DateTime::createFromFormat('Y-m-d', $run_date);

		if (!$dt || $dt->format('Y-m-d') !== $run_date) {
			show_error('Invalid date. Use YYYY-MM-DD.');
			return;
		}

		$month_start = $dt->format('Y-m-01');
		$month_end   = $dt->format('Y-m-t');

		// Commission generated this month is based on package
		// information at the end of previous month.
		$package_reference_date = date(
			'Y-m-d',
			strtotime($month_start . ' -1 day')
		);

		$package_period_start = date(
			'Y-m-01',
			strtotime($package_reference_date)
		);

		$package_rows = $this->db->query("
			SELECT
				package_no,
				name,
				dealer_comm_type,
				dealer_monthly_comm,
				dealer_monthly_times,
				dealer_onetime_comm
			FROM package
		")->result_array();

		$packages = [];

		foreach ($package_rows as $p) {
			$packages[(int)$p['package_no']] = $p;
		}

		$dealer_rows = $this->db->query("
			SELECT
				dealer_no,
				name,
				status,
				monthly_pct_deduct,
				one_time_amt_deduct,
				is_commission_set,
				upline,
				upline_tree
			FROM dealer
		")->result_array();

		$dealers = [];

		foreach ($dealer_rows as $d) {
			$dealers[(int)$d['dealer_no']] = $d;
		}

		$dealer_comm_rows = $this->db->query("
			SELECT *
			FROM dealer_comm
		")->result_array();

		$dealer_comms = [];

		foreach ($dealer_comm_rows as $dc) {
			$dealer_comms[(int)$dc['dealer_no']][(int)$dc['package']] = $dc;
		}

		/*
		* Important:
		*
		* Use the production commission resolution function ONCE.
		* Do not call get_agent_pkg_setting() hundreds of times because
		* it reloads dealer/package/commission data every call.
		*/
		$resolved_comm_map =
			$this->dealer_model->get_dealer_comm_array_map('', '', true);

		$bills = $this->db->query("
			SELECT
				b.bill_no,
				b.customer_no,
				b.bill_date,
				b.is_manual,

				c.name AS customer_name,
				c.dealer,
				c.package,
				c.monthly_charge

			FROM bill b

			INNER JOIN customer c
				ON c.customer_no = b.customer_no

			WHERE b.bill_date >= ?
			AND b.bill_date <= ?
			AND b.is_manual = 0
			AND c.dealer != 0

			ORDER BY c.dealer, b.customer_no, b.bill_no
		", [
			$month_start,
			$month_end
		])->result_array();

		/*
		* Bulk version of get_customer_monthly_charge().
		*
		* Example:
		* August commission
		* -> package_reference_date = 2026-07-31
		*/
		$history_rows = $this->db->query("
			SELECT h.*
			FROM customer_package_history h

			INNER JOIN (
				SELECT
					customer_no,
					MAX(start_date) AS max_start_date
				FROM customer_package_history
				WHERE start_date <= ?
				AND (
					end_date IS NULL
					OR end_date >= ?
				)
				GROUP BY customer_no
			) x
				ON x.customer_no = h.customer_no
				AND x.max_start_date = h.start_date

			WHERE h.start_date <= ?
			AND (
				h.end_date IS NULL
				OR h.end_date >= ?
			)
		", [
			$package_reference_date,
			$package_reference_date,
			$package_reference_date,
			$package_reference_date
		])->result_array();

		$package_history = [];

		foreach ($history_rows as $h) {
			$package_history[$h['customer_no']] = $h;
		}

		/*
		* IMPORTANT:
		*
		* The production functions:
		*
		* chk_monthly_comm_times()
		* chk_one_time_comm()
		*
		* count ALL dealer_comm_record for customer + dealer.
		*
		* But this report must calculate Expected AS OF the beginning
		* of the commission month.
		*
		* Otherwise the current month's actual record would make
		* monthly_times / one-time calculation wrong.
		*
		* Intentionally DO NOT filter:
		* - is_void
		* - is_manual
		* - package_no
		*
		* because the current production CRON does not filter them.
		*/
		$previous_count_rows = $this->db->query("
			SELECT
				customer_no,
				dealer_no,
				COUNT(*) AS comm_times

			FROM dealer_comm_record

			WHERE comm_date < ?

			GROUP BY customer_no, dealer_no
		", [
			$month_start
		])->result_array();

		$previous_counts = [];

		foreach ($previous_count_rows as $pc) {
			$previous_counts[$pc['customer_no']][(int)$pc['dealer_no']] =
				(int)$pc['comm_times'];
		}

		/*
		* Match Actual using the bill itself rather than comm_date.
		*
		* This also helps us catch an incorrectly dated commission
		* record instead of making it disappear from the report.
		*
		* Manual commission is deliberately excluded.
		*/
		$actual_rows = $this->db->query("
			SELECT dcr.*

			FROM dealer_comm_record dcr

			INNER JOIN bill b
				ON b.bill_no = dcr.bill_no

			WHERE b.bill_date >= ?
			AND b.bill_date <= ?
			AND b.is_manual = 0
			AND dcr.is_manual = 0

			ORDER BY
				dcr.bill_no,
				dcr.dealer_no,
				dcr.idx
		", [
			$month_start,
			$month_end
		])->result_array();

		$actual_index = [];

		foreach ($actual_rows as $a) {

			$bill_no   = $a['bill_no'];
			$dealer_no = (int)$a['dealer_no'];
			$amount    = (float)$a['amount'];

			if (!isset($actual_index[$bill_no][$dealer_no])) {
				$actual_index[$bill_no][$dealer_no] = [
					'amount'      => 0,
					'void_amount' => 0,
					'rows'        => []
				];
			}

			$actual_index[$bill_no][$dealer_no]['rows'][] = $a;

			if ((int)$a['is_void'] === 1) {
				$actual_index[$bill_no][$dealer_no]['void_amount'] += $amount;
			} else {
				$actual_index[$bill_no][$dealer_no]['amount'] += $amount;
			}
		}

		/*
		* Build hierarchy exactly around upline_tree used by CRON.
		*
		* Also append current dealer if old data has an incomplete
		* upline_tree.
		*/
		$get_chain = function ($dealer_no) use ($dealers) {

			$dealer_no = (int)$dealer_no;

			if (!isset($dealers[$dealer_no])) {
				return [$dealer_no];
			}

			$dealer = $dealers[$dealer_no];

			if (empty($dealer['upline'])) {
				return [$dealer_no];
			}

			$chain = array_values(array_filter(
				array_map(
					'intval',
					explode(',', (string)$dealer['upline_tree'])
				)
			));

			if (!in_array($dealer_no, $chain, true)) {
				$chain[] = $dealer_no;
			}

			if (!$chain) {
				$chain = [$dealer_no];
			}

			return array_values(array_unique($chain));
		};

		/*
		* Production top-level logic is slightly different from
		* downline logic.
		*
		* Top-level:
		* dealer override when follow_package = 0,
		* otherwise package default.
		*/
		$get_top_level_setting = function (
			$dealer_no,
			$package_no
		) use ($packages, $dealer_comms) {

			$package_no = (int)$package_no;
			$dealer_no  = (int)$dealer_no;

			$p = $packages[$package_no] ?? null;

			if (!$p) {
				return [
					'comm_type'     => '',
					'monthly'       => 0,
					'monthly_times' => 0,
					'onetime'       => 0
				];
			}

			$dc = $dealer_comms[$dealer_no][$package_no] ?? null;

			// No dealer override => follow package.
			if (!$dc || (int)$dc['follow_package'] === 1) {
				return [
					'comm_type'     => $p['dealer_comm_type'],
					'monthly'       => (int)$p['dealer_monthly_comm'],
					'monthly_times' => (int)$p['dealer_monthly_times'],
					'onetime'       => (float)$p['dealer_onetime_comm']
				];
			}

			return [
				'comm_type'     => $dc['comm_type'],
				'monthly'       => (int)$dc['monthly'],
				'monthly_times' => (int)$dc['monthly_times'],
				'onetime'       => (float)$dc['onetime']
			];
		};

		/*
		* Calculate commission eligibility + amount.
		*/
		$calculate_expected = function (
			$setting,
			$monthly_charge,
			$previous_count
		) {

			$type           = $setting['comm_type'] ?? '';
			$monthly_charge = (float)$monthly_charge;
			$previous_count = (int)$previous_count;

			if ($type === 'm') {

				$rate  = (int)($setting['monthly'] ?? 0);
				$times = (int)($setting['monthly_times'] ?? 0);

				if ($rate <= 0) {
					return [
						'amount' => 0,
						'reason' => 'Monthly rate is 0'
					];
				}

				if ($times > 0 && $previous_count >= $times) {
					return [
						'amount' => 0,
						'reason' =>
							'Monthly limit reached (' .
							$previous_count .
							'/' .
							$times .
							')'
					];
				}

				return [
					'amount' => ($monthly_charge * $rate) / 100,
					'reason' => ''
				];
			}

			if ($type === 'o') {

				$amount = (float)($setting['onetime'] ?? 0);

				if ($amount <= 0) {
					return [
						'amount' => 0,
						'reason' => 'One-time amount is 0'
					];
				}

				if ($previous_count > 0) {
					return [
						'amount' => 0,
						'reason' => 'One-time commission already received'
					];
				}

				return [
					'amount' => $amount,
					'reason' => ''
				];
			}

			return [
				'amount' => 0,
				'reason' => 'No commission setting'
			];
		};

		$get_status = function (
			$expected,
			$actual,
			$void_amount,
			$reason = ''
		) {

			$expected    = (float)$expected;
			$actual      = (float)$actual;
			$void_amount = (float)$void_amount;

			$epsilon = 0.005;

			if (
				abs($expected) <= $epsilon &&
				abs($actual) <= $epsilon
			) {
				return 'NO COMM';
			}

			if (
				$expected > $epsilon &&
				abs($actual) <= $epsilon &&
				$void_amount > $epsilon
			) {
				return 'VOID';
			}

			if (
				$expected > $epsilon &&
				abs($actual) <= $epsilon
			) {
				return 'MISSING';
			}

			if (
				abs($expected) <= $epsilon &&
				$actual > $epsilon
			) {
				return 'EXTRA';
			}

			if (abs($expected - $actual) <= $epsilon) {
				return 'MATCH';
			}

			return 'MISMATCH';
		};

		// =========================================================
		// BUILD REPORT
		// =========================================================
		$report = [];

		foreach ($bills as $bill) {

			$customer_no    = $bill['customer_no'];
			$bill_no        = $bill['bill_no'];
			$customer_dealer = (int)$bill['dealer'];

			// -----------------------------------------------------
			// Historical package
			// -----------------------------------------------------
			$history = $package_history[$customer_no] ?? null;

			if ($history) {

				$package_no = !empty($history['package_no'])
					? (int)$history['package_no']
					: (int)$bill['package'];

				$monthly_charge = $history['monthly_charge'] !== ''
					? (float)$history['monthly_charge']
					: (float)$bill['monthly_charge'];

				$package_name = $history['package_name'] ?: (
					$packages[$package_no]['name'] ?? ''
				);

			} else {

				$package_no     = (int)$bill['package'];
				$monthly_charge = (float)$bill['monthly_charge'];
				$package_name   = $packages[$package_no]['name'] ?? '';
			}

			$chain = $get_chain($customer_dealer);

			$top_dealer_no = !empty($chain)
				? (int)$chain[0]
				: $customer_dealer;

			$direct_branch_dealer =
				isset($chain[1]) ? (int)$chain[1] : null;

			$lines = [];

			foreach ($chain as $level => $dealer_no) {

				$dealer_no = (int)$dealer_no;

				// Top level follows the special CRON logic.
				if ($level === 0) {

					$setting = $get_top_level_setting(
						$dealer_no,
						$package_no
					);

				} else {

					$setting =
						$resolved_comm_map[$dealer_no]
							['dealer_comm'][$package_no]
						?? [
							'comm_type'     => '',
							'monthly'       => 0,
							'monthly_times' => 0,
							'onetime'       => 0
						];
				}

				$previous_count =
					$previous_counts[$customer_no][$dealer_no]
					?? 0;

				$expected_info = $calculate_expected(
					$setting,
					$monthly_charge,
					$previous_count
				);

				$expected_amount = (float)$expected_info['amount'];

				$actual_info =
					$actual_index[$bill_no][$dealer_no]
					?? [
						'amount'      => 0,
						'void_amount' => 0,
						'rows'        => []
					];

				$actual_amount = (float)$actual_info['amount'];
				$void_amount   = (float)$actual_info['void_amount'];

				$status = $get_status(
					$expected_amount,
					$actual_amount,
					$void_amount,
					$expected_info['reason']
				);

				$type = $setting['comm_type'] ?? '';

				if ($type === 'm') {

					$rate = (int)($setting['monthly'] ?? 0);

					$deduct = $level === 0
						? null
						: (int)(
							$dealers[$dealer_no]['monthly_pct_deduct']
							?? 0
						);

				} elseif ($type === 'o') {

					$rate = null;

					$deduct = $level === 0
						? null
						: (float)(
							$dealers[$dealer_no]['one_time_amt_deduct']
							?? 0
						);

				} else {

					$rate   = null;
					$deduct = null;
				}

				$lines[$dealer_no] = [
					'level'          => $level,
					'dealer_no'      => $dealer_no,
					'dealer_name'    => $dealers[$dealer_no]['name'] ?? '',
					'type'           => $type,
					'rate'           => $rate,
					'deduct'         => $deduct,
					'onetime'        => (float)($setting['onetime'] ?? 0),
					'previous_count' => $previous_count,

					'expected'       => $expected_amount,
					'actual'         => $actual_amount,
					'void_amount'    => $void_amount,

					'status'         => $status,
					'reason'         => $expected_info['reason'],
					'actual_rows'    => $actual_info['rows']
				];
			}

			if (isset($actual_index[$bill_no])) {

				foreach ($actual_index[$bill_no] as $dealer_no => $actual_info) {

					$dealer_no = (int)$dealer_no;

					if (isset($lines[$dealer_no])) {
						continue;
					}

					$actual_amount = (float)$actual_info['amount'];
					$void_amount   = (float)$actual_info['void_amount'];

					$status = $get_status(
						0,
						$actual_amount,
						$void_amount,
						'Dealer is not in expected hierarchy'
					);

					$lines[$dealer_no] = [
						'level'          => null,
						'dealer_no'      => $dealer_no,
						'dealer_name'    => $dealers[$dealer_no]['name'] ?? '',
						'type'           => '',
						'rate'           => null,
						'deduct'         => null,
						'onetime'        => 0,
						'previous_count' => 0,

						'expected'       => 0,
						'actual'         => $actual_amount,
						'void_amount'    => $void_amount,

						'status'         => $status,
						'reason'         =>
							'Actual commission dealer is not in expected hierarchy',

						'actual_rows'    => $actual_info['rows']
					];
				}
			}

			// -----------------------------------------------------
			// Bill totals
			// -----------------------------------------------------
			$bill_expected = 0;
			$bill_actual   = 0;
			$bill_has_issue = false;

			foreach ($lines as $line) {

				$bill_expected += $line['expected'];
				$bill_actual   += $line['actual'];

				if (in_array(
					$line['status'],
					['MISMATCH', 'MISSING', 'EXTRA', 'VOID'],
					true
				)) {
					$bill_has_issue = true;
				}
			}

			// -----------------------------------------------------
			// Top-level report bucket
			// -----------------------------------------------------
			if (!isset($report[$top_dealer_no])) {

				$report[$top_dealer_no] = [
					'dealer_no' => $top_dealer_no,
					'name'      => $dealers[$top_dealer_no]['name'] ?? '',

					'customers' => [],
					'bills'     => [],

					'own_expected'    => 0,
					'own_actual'      => 0,

					'branch_expected' => 0,
					'branch_actual'   => 0,

					'issue_bills'     => 0
				];
			}

			$report[$top_dealer_no]['customers'][$customer_no] = true;

			$report[$top_dealer_no]['branch_expected'] += $bill_expected;
			$report[$top_dealer_no]['branch_actual']   += $bill_actual;

			if (isset($lines[$top_dealer_no])) {
				$report[$top_dealer_no]['own_expected'] +=
					$lines[$top_dealer_no]['expected'];

				$report[$top_dealer_no]['own_actual'] +=
					$lines[$top_dealer_no]['actual'];
			}

			if ($bill_has_issue) {
				$report[$top_dealer_no]['issue_bills']++;
			}

			$report[$top_dealer_no]['bills'][] = [
				'bill_no'       => $bill_no,
				'bill_date'     => $bill['bill_date'],

				'customer_no'   => $customer_no,
				'customer_name' => $bill['customer_name'],

				'package_no'    => $package_no,
				'package_name'  => $package_name,
				'monthly_charge'=> $monthly_charge,

				'chain'         => $chain,
				'direct_branch' => $direct_branch_dealer,
				'customer_dealer' => $customer_dealer,

				'lines'         => $lines,

				'expected'      => $bill_expected,
				'actual'        => $bill_actual,
				'has_issue'     => $bill_has_issue
			];
		}

		ksort($report);

		// =========================================================
		// GLOBAL TOTALS
		// =========================================================
		$total_customers = [];
		$total_bills = 0;

		$total_expected = 0;
		$total_actual   = 0;

		$total_issue_bills = 0;

		foreach ($report as $top) {

			foreach ($top['customers'] as $customer_no => $unused) {
				$total_customers[$customer_no] = true;
			}

			$total_bills += count($top['bills']);

			$total_expected += $top['branch_expected'];
			$total_actual   += $top['branch_actual'];

			$total_issue_bills += $top['issue_bills'];
		}

		// =========================================================
		// HTML HELPERS
		// =========================================================
		$e = function ($value) {
			return html_escape((string)$value);
		};

		$money = function ($value) {
			return 'RM ' . number_format((float)$value, 2);
		};

		$dealer_tag = function ($dealer_no) use ($dealers, $e) {

			if (!$dealer_no) {
				return '-';
			}

			$dealer_no = (int)$dealer_no;
			$name = $dealers[$dealer_no]['name'] ?? '';

			return
				'<a class="dealer-tag" target="_blank" href="' .
				base_url('dealer/edit_dealer/' . $dealer_no) .
				'">' .
				$e($dealer_no . ' · ' . $name) .
				'</a>';
		};

		// =========================================================
		// HTML
		// =========================================================
		echo '
		<!doctype html>
		<html>
		<head>
			<meta charset="utf-8">

			<title>Dealer Commission Audit Report</title>

			<style>

				* {
					box-sizing: border-box;
				}

				body {
					margin: 0;
					padding: 24px;
					background: #f5f6f8;
					font-family: Arial, sans-serif;
					font-size: 13px;
					color: #222;
				}

				h1,
				h2,
				h3 {
					margin-top: 0;
				}

				.container {
					max-width: 1700px;
					margin: 0 auto;
				}

				.header {
					background: white;
					border: 1px solid #ddd;
					border-radius: 8px;
					padding: 20px;
					margin-bottom: 18px;
				}

				.meta {
					line-height: 1.8;
					color: #555;
				}

				.summary-cards {
					display: flex;
					flex-wrap: wrap;
					gap: 12px;
					margin: 18px 0;
				}

				.card {
					min-width: 160px;
					padding: 14px;
					background: white;
					border: 1px solid #ddd;
					border-radius: 7px;
				}

				.card .label {
					font-size: 11px;
					color: #777;
					text-transform: uppercase;
					margin-bottom: 6px;
				}

				.card .value {
					font-size: 20px;
					font-weight: bold;
				}

				table {
					width: 100%;
					border-collapse: collapse;
					background: white;
				}

				th,
				td {
					border: 1px solid #ddd;
					padding: 8px;
					text-align: left;
					vertical-align: top;
				}

				th {
					background: #eee;
				}

				.num {
					text-align: right;
					white-space: nowrap;
				}

				.dealer-tag {
					display: inline-block;
					padding: 4px 8px;
					border-radius: 12px;
					background: #e8eefc;
					color: #224b9b;
					text-decoration: none;
					font-weight: bold;
				}

				.dealer-tag:hover {
					text-decoration: underline;
				}

				.top-section {
					margin-top: 20px;
					border: 1px solid #ccc;
					border-radius: 8px;
					background: white;
					overflow: hidden;
				}

				.top-section > summary {
					cursor: pointer;
					padding: 16px;
					font-weight: bold;
					font-size: 15px;
					background: #eef2f7;
				}

				.top-content {
					padding: 16px;
				}

				.top-stats {
					margin-bottom: 16px;
				}

				.bill {
					border: 1px solid #ddd;
					border-radius: 6px;
					margin: 12px 0;
					overflow: hidden;
				}

				.bill > summary {
					padding: 12px;
					cursor: pointer;
					background: #fafafa;
				}

				.bill.issue > summary {
					background: #fff0f0;
				}

				.bill-content {
					padding: 15px;
				}

				.bill-meta {
					display: grid;
					grid-template-columns:
						repeat(auto-fit, minmax(220px, 1fr));
					gap: 10px;
					margin-bottom: 15px;
				}

				.meta-box {
					padding: 10px;
					border: 1px solid #ddd;
					border-radius: 5px;
					background: #fafafa;
				}

				.meta-box .label {
					color: #777;
					font-size: 11px;
					margin-bottom: 5px;
				}

				.hierarchy {
					padding: 10px;
					margin: 10px 0 15px;
					border: 1px solid #ddd;
					background: #fafafa;
					border-radius: 5px;
				}

				.arrow {
					margin: 0 7px;
					color: #888;
				}

				.status {
					font-weight: bold;
					white-space: nowrap;
				}

				.MATCH {
					color: #15803d;
				}

				.MISMATCH,
				.MISSING,
				.EXTRA {
					color: #b91c1c;
				}

				.VOID {
					color: #a16207;
				}

				.NO-COMM {
					color: #777;
				}

				tr.row-MISMATCH,
				tr.row-MISSING,
				tr.row-EXTRA {
					background: #fff4f4;
				}

				tr.row-VOID {
					background: #fffbea;
				}

				.reason {
					margin-top: 4px;
					font-size: 11px;
					color: #777;
				}

				.issue-count {
					color: #b91c1c;
					font-weight: bold;
				}

				.ok-count {
					color: #15803d;
					font-weight: bold;
				}

				a {
					color: #215fa6;
				}

			</style>
		</head>

		<body>

		<div class="container">

			<div class="header">

				<h1>Dealer Commission Audit Report</h1>

				<div class="meta">

					Commission Month:
					<strong>' . $e(date('F Y', strtotime($month_start))) . '</strong>

					<br>

					Bill Period:
					<strong>' . $e($month_start) . '</strong>
					to
					<strong>' . $e($month_end) . '</strong>

					<br>

					Package Reference:
					<strong>' . $e($package_reference_date) . '</strong>

					<br>

					Package Commission Period:
					<strong>' . $e($package_period_start) . '</strong>
					to
					<strong>' . $e($package_reference_date) . '</strong>

				</div>

			</div>

			<div class="summary-cards">

				<div class="card">
					<div class="label">Top Level Agents</div>
					<div class="value">' . count($report) . '</div>
				</div>

				<div class="card">
					<div class="label">Customers</div>
					<div class="value">' . count($total_customers) . '</div>
				</div>

				<div class="card">
					<div class="label">Bills</div>
					<div class="value">' . $total_bills . '</div>
				</div>

				<div class="card">
					<div class="label">Expected Payout</div>
					<div class="value">' . $money($total_expected) . '</div>
				</div>

				<div class="card">
					<div class="label">Actual Payout</div>
					<div class="value">' . $money($total_actual) . '</div>
				</div>

				<div class="card">
					<div class="label">Bills With Issues</div>
					<div class="value">' . $total_issue_bills . '</div>
				</div>

			</div>

			<h2>Top Level Summary</h2>

			<table>

				<thead>
					<tr>
						<th>Top Level Agent</th>
						<th class="num">Customers</th>
						<th class="num">Bills</th>
						<th class="num">Expected Own</th>
						<th class="num">Actual Own</th>
						<th class="num">Expected Branch</th>
						<th class="num">Actual Branch</th>
						<th class="num">Issue Bills</th>
					</tr>
				</thead>

				<tbody>';

		foreach ($report as $top) {

			$issue_class = $top['issue_bills'] > 0
				? 'issue-count'
				: 'ok-count';

			echo '
				<tr>

					<td>
						<a href="#top-' . (int)$top['dealer_no'] . '">
							' .
							$dealer_tag($top['dealer_no']) .
							'
						</a>
					</td>

					<td class="num">' .
						count($top['customers']) .
					'</td>

					<td class="num">' .
						count($top['bills']) .
					'</td>

					<td class="num">' .
						$money($top['own_expected']) .
					'</td>

					<td class="num">' .
						$money($top['own_actual']) .
					'</td>

					<td class="num">' .
						$money($top['branch_expected']) .
					'</td>

					<td class="num">' .
						$money($top['branch_actual']) .
					'</td>

					<td class="num ' . $issue_class . '">' .
						(int)$top['issue_bills'] .
					'</td>

				</tr>';
		}

		echo '
				</tbody>

			</table>';

		// =========================================================
		// TOP LEVEL DETAIL
		// =========================================================
		foreach ($report as $top) {

			echo '

			<details
				class="top-section"
				id="top-' . (int)$top['dealer_no'] . '"
				' . ($top['issue_bills'] > 0 ? 'open' : '') . '
			>

				<summary>

					' . $dealer_tag($top['dealer_no']) . '

					&nbsp; | &nbsp;

					Own:
					' . $money($top['own_actual']) . '

					&nbsp; | &nbsp;

					Branch:
					' . $money($top['branch_actual']) . '

					&nbsp; | &nbsp;

					Issues:
					' . (int)$top['issue_bills'] . '

				</summary>

				<div class="top-content">

					<div class="top-stats">

						Expected Own:
						<strong>' . $money($top['own_expected']) . '</strong>

						&nbsp; | &nbsp;

						Actual Own:
						<strong>' . $money($top['own_actual']) . '</strong>

						&nbsp; | &nbsp;

						Expected Branch:
						<strong>' . $money($top['branch_expected']) . '</strong>

						&nbsp; | &nbsp;

						Actual Branch:
						<strong>' . $money($top['branch_actual']) . '</strong>

					</div>';

			foreach ($top['bills'] as $bill) {

				$bill_link =
					base_url(
						'bill/bill_detail/' .
						rawurlencode($bill['customer_no'])
					);

				echo '

					<details class="bill ' .
						($bill['has_issue'] ? 'issue' : '') .
					'">

						<summary>

							Customer:
							<strong>' .
								$e($bill['customer_no']) .
								' - ' .
								$e($bill['customer_name']) .
							'</strong>

							&nbsp; | &nbsp;

							Bill:
							<strong>' .
								$e($bill['bill_no']) .
							'</strong>

							&nbsp; | &nbsp;

							Expected:
							<strong>' .
								$money($bill['expected']) .
							'</strong>

							&nbsp; | &nbsp;

							Actual:
							<strong>' .
								$money($bill['actual']) .
							'</strong>

							&nbsp; | &nbsp;

							<span class="status ' .
								($bill['has_issue'] ? 'MISMATCH' : 'MATCH') .
							'">' .
								$bill_status .
							'</span>

							&nbsp; | &nbsp;

							' .
							$dealer_tag($bill['customer_dealer']) .

						'</summary>

						<div class="bill-content">

							<div class="bill-meta">

								<div class="meta-box">

									<div class="label">
										Customer
									</div>

									' .
									$e($bill['customer_no']) .
									'<br>' .
									$e($bill['customer_name']) .

								'</div>

								<div class="meta-box">

									<div class="label">
										Bill
									</div>

									<a
										target="_blank"
										href="' . $bill_link . '"
									>
										' . $e($bill['bill_no']) . '
									</a>

									<br>

									' . $e($bill['bill_date']) . '

								</div>

								<div class="meta-box">

									<div class="label">
										Package
									</div>

									' .
									$e(
										$bill['package_no'] .
										' - ' .
										$bill['package_name']
									) .

								'</div>

								<div class="meta-box">

									<div class="label">
										Historical Monthly Charge
									</div>

									<strong>' .
										$money($bill['monthly_charge']) .
									'</strong>

									<br>

									As at ' .
									$e($package_reference_date) .

								'</div>

								<div class="meta-box">

									<div class="label">
										Direct Branch
									</div>

									' .
									(
										$bill['direct_branch']
										? $dealer_tag($bill['direct_branch'])
										: '-'
									) .

								'</div>

								<div class="meta-box">

									<div class="label">
										Customer Direct Dealer
									</div>

									' .
									$dealer_tag(
										$bill['customer_dealer']
									) .

								'</div>

							</div>

							<div class="hierarchy">

								<strong>Hierarchy:</strong>

								&nbsp;';

				foreach ($bill['chain'] as $i => $dealer_no) {

					if ($i > 0) {
						echo '<span class="arrow">→</span>';
					}

					echo $dealer_tag($dealer_no);
				}

				echo '

								<span class="arrow">→</span>

								Customer ' .
								$e($bill['customer_no']) .

							'</div>

							<table>

								<thead>

									<tr>
										<th>Level</th>
										<th>Dealer</th>
										<th>Type</th>
										<th class="num">Configured Deduct</th>
										<th class="num">Effective Rate / Amount</th>
										<th class="num">Previous Count</th>
										<th class="num">Expected</th>
										<th class="num">Actual</th>
										<th class="num">Void</th>
										<th>Status</th>
									</tr>

								</thead>

								<tbody>';

				foreach ($bill['lines'] as $line) {

					// Do not show commission lines with no expected commission
					// Keep EXTRA actual records visible for audit
					if ((float)$line['expected'] <= 0
						&& (float)$line['actual'] <= 0
						&& (float)$line['void_amount'] <= 0) 
					{
						continue;
					}

					$status_class =
						str_replace(' ', '-', $line['status']);

					if ($line['level'] === 0) {
						$level_name = 'Top';
					} elseif ($line['level'] === null) {
						$level_name = 'Extra';
					} else {
						$level_name = 'L' . $line['level'];
					}

					if ($line['type'] === 'm') {

						$type_text = 'Monthly';

						$deduct_text =
							$line['level'] === 0
							? '-'
							: number_format(
								(float)$line['deduct'],
								0
							) . ' pp';

						$rate_text =
							number_format(
								(float)$line['rate'],
								0
							) . '%';

					} elseif ($line['type'] === 'o') {

						$type_text = 'One-Time';

						$deduct_text =
							$line['level'] === 0
							? '-'
							: $money($line['deduct']);

						$rate_text =
							$money($line['onetime']);

					} else {

						$type_text   = '-';
						$deduct_text = '-';
						$rate_text   = '-';
					}

					echo '

									<tr class="row-' .
										$status_class .
									'">

										<td>' .
											$e($level_name) .
										'</td>

										<td>' .
											$dealer_tag(
												$line['dealer_no']
											) .
										'</td>

										<td>' .
											$e($type_text) .
										'</td>

										<td class="num">' .
											$e($deduct_text) .
										'</td>

										<td class="num">' .
											$e($rate_text) .
										'</td>

										<td class="num">' .
											(int)$line['previous_count'] .
										'</td>

										<td class="num">' .
											$money(
												$line['expected']
											) .
										'</td>

										<td class="num">' .
											$money(
												$line['actual']
											) .
										'</td>

										<td class="num">' .
											(
												$line['void_amount'] > 0
												? $money(
													$line['void_amount']
												)
												: '-'
											) .
										'</td>

										<td>

											<span class="status ' .
												$status_class .
											'">' .
												$e($line['status']) .
											'</span>';

					if ($line['reason']) {

						echo '
							<div class="reason">' .
								$e($line['reason']) .
							'</div>';
					}

					echo '

										</td>

									</tr>';
				}

				echo '

								</tbody>

								<tfoot>

									<tr>

										<th colspan="6">
											Bill Total
										</th>

										<th class="num">' .
											$money(
												$bill['expected']
											) .
										'</th>

										<th class="num">' .
											$money(
												$bill['actual']
											) .
										'</th>

										<th colspan="2"></th>

									</tr>

								</tfoot>

							</table>

						</div>

					</details>';
			}

			echo '

				</div>

			</details>';
		}

		echo '

		</div>

		</body>
		</html>';
	}
}

?>
