<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Api extends CI_Controller {

	//~ public $adj_data;
	private $e_key = "1nf0n4l";
	private $bearer_token = "f5a7c6d2-b9c4-497e-91de-8c56db9077fa";
	private $push_cfg = array();

	function __construct() {
        parent::__construct();
		$this->load->helper('custom_helper');
		$this->load->model('common_model');
		// Push API configuration
		$this->config->load('push', TRUE, TRUE);
		$this->push_cfg = (array) $this->config->item('push', 'push');
		header('Content-Type: application/json');
    }
	
	public function errs($code)
	{
	//message code
		switch($code)
		{
			case 0 : return 'Success';
			case 1 : return 'Invalid safety code';
			case 2 : return 'Mandatory field(s) missing';
			case 3 : return 'Empty result';
			case 4 : return 'Send Fail';
		}
	}

	public function verify_serial( $concat_var, $md5_concat_var ){
		$private_key = $this->e_key;
		//~ echo md5( $concat_var . $private_key ) . '<Br />';
		//~ echo $md5_concat_var . '<Br />';						
		if( md5( $concat_var . $private_key ) == $md5_concat_var ){
			return 1;
		}else{
			return 0;
		}
	}

	public function test($cust_no, $scode)
	{
		if( $this->verify_serial( $cust_no , $scode ) == 1 ){
			echo "OK!";
		} else {
			echo "Error";
		}
	}

	public function restore_connection($cust_no, $scode)
	{
		if( $this->verify_serial( $cust_no , $scode ) == 1 ){
			$this->load->model('customer_model');
			$restore_result = $this->customer_model->varied_restore_account($cust_no);
			if ($restore_result) {
				// $this->customer_model->generate_prorated_bill($cust_no);
				$return_val = array('msg' => $this->errs(0));
			} else {
				$return_val = array('msg' => $this->errs(3));
			}

			echo json_encode( $return_val );
		} else {
			$return_val = array('msg' => $this->errs(1));
			echo json_encode( $return_val );
		}
	}

	public function send_doc()
	{
		$return_val['errcode'] = 0;
		$return_val['errtext'] = $this->errs(0);
		$return_val['result']  = 0;

		$send_data_str = filter_var( $this->input->post('send_data')  ,  FILTER_SANITIZE_STRING);
		$send_data = json_decode($send_data_str);
		$scode   = filter_var( $this->input->post('scode') ,  FILTER_SANITIZE_STRING);

		//check serial, using timestamp to the minute
		if( $this->verify_serial( date('YmdHi') , $scode ) == 0 ){
			$return_val['errcode'] = 1;
			$return_val['errtext'] = $this->errs(1);
			echo json_encode( $return_val );
			exit;
		}

		if( $send_data_str == '' || $scode == '' ){
			$return_val['errcode'] = 2;
			$return_val['errtext'] = $this->errs(2);
		}else{

			$this->load->model('docs_log_model');

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

			$send_result = $this->docs_log_model->do_send($send_array);

			if ($send_result['status'] == 'succ') {
				//if done send
				$return_val['result'] = 1;
			} else {
				$return_val['result'] = 0;
				$return_val['errtext'] = $this->errs(4);
			}

		}

		echo json_encode( $return_val );
	}
	
    public function chk_acc( )
    {
		
		$return_val['errcode'] = 0;
		$return_val['errtext'] = $this->errs(0);
		$return_val['result']  = 0;
		
		$cust_no = filter_var( $this->input->post('cnum')  ,  FILTER_SANITIZE_STRING);
		$scode   = filter_var( $this->input->post('scode') ,  FILTER_SANITIZE_STRING);
				
		//check serial
		if( $this->verify_serial( $cust_no , $scode ) == 0 ){
			$return_val['errcode'] = 1;
			$return_val['errtext'] = $this->errs(1);
			echo json_encode( $return_val );
			exit;
		}
		
		if( $cust_no == '' || $scode == '' ){
			$return_val['errcode'] = 2;
			$return_val['errtext'] = $this->errs(2);
		}else{
			$this->load->model('customer_model');
			$custInfo = $this->customer_model->get_customer( $cust_no );
			if( $custInfo['customer_no'] != '' ){
				$return_val['result'] = 1;
			}
		}
		
		echo json_encode( $return_val );
	}

	public function info_acc( $cust_no = '' ){
		
		$return_val['errcode'] = 0;
		$return_val['errtext'] = $this->errs(0);
		$return_val['result']  = 0;
		
		$cust_no = filter_var( $this->input->post('cnum')  ,  FILTER_SANITIZE_STRING);
		$scode   = filter_var( $this->input->post('scode') ,  FILTER_SANITIZE_STRING);
		
		//check serial
		if( $this->verify_serial( $cust_no , $scode ) == 0 ){
			$return_val['errcode'] = 1;
			$return_val['errtext'] = $this->errs(1);
			echo json_encode( $return_val );
			exit;
		}
		
		if( $cust_no == '' || $scode == '' ){
			$return_val['errcode'] = 2;
			$return_val['errtext'] = $this->errs(2);
		}else{
			$this->load->model('customer_model');
			$custInfo = $this->customer_model->get_customer( $cust_no );
			if( $custInfo['customer_no'] != '' ){
				
				$this->load->model('setting_model');
				$state = $this->setting_model->get_sys_state( $custInfo['inst_state'] );
				$bill_state = $this->setting_model->get_sys_state( $custInfo['bill_state'] );
				
				$status = "";
				switch( $custInfo['status'] ){
					case 'r' :
						$status = "Registered";
						break;
					case 'p' :
						$status = "Pending";
						break;
					case 'c' :
						$status = "Cancelled";
						break;
					case 't' :
						$status = "Terminated";
						break;
					case 's' :
						$status = "Suspended";
						break;
					default :
						$status = "";
				}
				
				$category = "";
				switch( $custInfo['category'] ){
					case 'r' :
						$category = "Resident";
						break;
					case 'b' :
						$category = "Business";
						break;
					case 'w' :
						$category = "Wholesale";
						break;
					default :
						$category = "";
				}
				
				
				$return_val['result'] = array( 'cnum' 			=> $custInfo['customer_no'],
											   'cname' 			=> $custInfo['name'],
											   'status' 		=> $status,
											   'category'		=> $category,
											   'email_1'		=> $custInfo['email_1'],
											   'email_2'		=> $custInfo['email_2'],
											   'phone_num'		=> $custInfo['tel_num'],
											   'handphone_num' 	=> $custInfo['mobile_num'],
											   'fax_num' 		=> $custInfo['fax_num'],
											   'addr1' 			=> $custInfo['inst_addr1'],
											   'addr2'			=> $custInfo['inst_addr2'],
											   'city'			=> $custInfo['inst_city'],
											   'postcode'		=> $custInfo['inst_postcode'],
											   'state'			=> strtoupper( $state['name'] ),
											   'bill_to'		=> $custInfo['bill_name'],
											   'bill_addr1'		=> $custInfo['bill_addr1'],
											   'bill_addr2'		=> $custInfo['bill_addr2'],
											   'bill_city'		=> $custInfo['bill_city'],
											   'bill_postcode'	=> $custInfo['bill_postcode'],
											   'bill_state'		=> strtoupper( $bill_state['name'] ),
											   'package'		=> $custInfo['package_name'],
											   'package_monthly_charge' => $custInfo['monthly_charge']
											   //'package_yearly_charge'  => $custInfo['yearly_charge']
											);
			}else{
				$return_val['result'] = array();
				$return_val['errcode'] = 3;
				$return_val['errtext'] = $this->errs( 3 );
			}
		}
		echo json_encode( $return_val );
	}
	
	
	//show the last 5 only
	public function billing_detail(){
		
		$return_val['errcode'] = 0;
		$return_val['errtext'] = $this->errs(0);
		$return_val['result']  = array();
		
		$cust_no = filter_var( $this->input->post('cnum')  ,  FILTER_SANITIZE_STRING);
		$scode   = filter_var( $this->input->post('scode') ,  FILTER_SANITIZE_STRING);
		
		//check serial
		if( $this->verify_serial( $cust_no , $scode ) == 0 ){
			$return_val['errcode'] = 1;
			$return_val['errtext'] = $this->errs(1);
			echo json_encode( $return_val );
			exit;
		}
		
		if( $cust_no == '' || $scode == '' ){
			$return_val['errcode'] = 2;
			$return_val['errtext'] = $this->errs(2);
		}else{
			
			$this->load->model('bill_model');
			$billInfo = $this->bill_model->get_bill_list_by_cust_no( $cust_no, 5 );
			foreach( $billInfo AS $key => $bill ){
				$return_val['result']['bills_detail'][$key] = array('bill_no' 	=> $bill['bill_no'] ,
																'bill_date' => $bill['bill_date'] ,
																'bill_due_date' => $bill['bill_due_date'] ,
																'previous_balance' => $bill['previous_balance'],
																'payment_received' => $bill['payment_received'],
																'charges' 		=> $bill['charges'],
																'tax_charges' 	=> $bill['tax_charges'],
																'total_charges'	=> $bill['amount'],
																'balance' 		=> $bill['balance']
																);
			}
		}
	
	
		echo json_encode( $return_val );
	
	}
	
	public function payment_history(){
		
		$return_val['errcode'] = 0;
		$return_val['errtext'] = $this->errs(0);
		$return_val['result']  = array();
		
		$cust_no = filter_var( $this->input->post('cnum')  ,  FILTER_SANITIZE_STRING);
		$scode   = filter_var( $this->input->post('scode') ,  FILTER_SANITIZE_STRING);
		
		//check serial
		if( $this->verify_serial( $cust_no , $scode ) == 0 ){
			$return_val['errcode'] = 1;
			$return_val['errtext'] = $this->errs(1);
			echo json_encode( $return_val );
			exit;
		}
		
		if( $cust_no == '' || $scode == '' ){
			$return_val['errcode'] = 2;
			$return_val['errtext'] = $this->errs(2);
		}else{
			
			$this->load->model('payment_model');
			$qwhere = " AND c.customer_no = '".$cust_no."' ";
			
			$payments = $this->payment_model->get_payment_filtered( $qwhere );
			
			if( !empty( $payments ) ){
				foreach( $payments AS $key => $payment ){
					$return_val['result']['payments'][$key] = array('payment_no' 	=> $payment['payment_no'],
																	'pay_date' 		=> $payment['pay_date'],
																	'remark' 		=> $payment['remark'],
																	'amount' 		=> $payment['amount'],
																	'payment_source' => $payment['payment_source_name']
																);
				}
			
			}else{
				$return_val['errcode'] = 3;
				$return_val['errtext'] = $this->errs(3);
			}
			
		}
		
		echo json_encode( $return_val );
		
	}
	
	
	public function generatePDF(){
				
		$return_val['errcode'] = 0;
		$return_val['errtext'] = $this->errs(0);
		$return_val['result']  = array();
		
		$cust_no = filter_var( $this->input->post('cnum')  ,  FILTER_SANITIZE_STRING);
		$bill_no = filter_var( $this->input->post('bnum')  ,  FILTER_SANITIZE_STRING);
		$scode   = filter_var( $this->input->post('scode') ,  FILTER_SANITIZE_STRING);
		
		//check serial
		if( $this->verify_serial( $cust_no . $bill_no , $scode ) == 0 ){
			$return_val['errcode'] = 1;
			$return_val['errtext'] = $this->errs(1);
			echo json_encode( $return_val );
			exit;
		}
		
		if( $cust_no == '' || $scode == '' || $bill_no == '' ){
			$return_val['errcode'] = 2;
			$return_val['errtext'] = $this->errs(2);
		}else{
			$this->load->model('bill_model');
			$bill_model = $this->bill_model->bill_statement('bill', $bill_no);

			$y=0;
			foreach( $bill_model['input']['data'] AS $bill ){
				$z=0;
				foreach( $bill['customer'] AS $cust ){
					$display_state = $this->common_model->get_table('sys_state', '*', 'state_code = "'.$cust['display_state'].'" ') ;
					if( isset($display_status[0]['name']) ){
						$bill_model['input']['data'][$y]['customer'][$z]['display_state'] = strtoupper($display_state[0]['name']) ;
					}
					$z++;
				}
				$y++;
			}

			//$this->load->helper('form');
			//$header_data 				= $this->vars;
			$header_data['title']		= 'Bills';
			$header_data['description']	= 'Print a bill statement';
			$content_data['data']		= (empty($bill_model['input']['data']))?'':$bill_model['input']['data'];
			$content_data['gst_reg_no']	= (empty($bill_model['gst_reg_no']))?'':$bill_model['gst_reg_no'];
			//$data['msg'] 				= $this->msg;
			//$gen_pdf 					= $bill_model['gen_pdf'];
			
			$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
			$content_data['bills_footer'] = $footer[0]['val'];
			
			$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
			$content_data['company_full_name'] = $comp[0]['val'];

			$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
			$content_data['company_tax_number'] = $tax[0]['val'];
			
			$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
			$content_data['jompay_biller_code'] = $jompay[0]['val'];
						
			$gen_pdf = 1;
			$html  = '';
			if($gen_pdf == '1')
			{
				$this->load->library('parser');
				$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
				$html .= $this->parser->parse('bill/bill_statement', $content_data, true);
				$html .= $this->load->view('templates/print_footer', '', true);

				$this->load->helper(array('dompdf', 'file'));
				$data = pdf_create($html, '', false);

				//if(empty($pdf_name)) $pdf_name = 'temp_bill_statement_'.date("Y-m-d_h:i:sa", time());
				if(empty($pdf_name)) $pdf_name =  $cust_no.'_bill_statement_'.date("Y-m-d");
				$file_path = 'temp/'.$pdf_name.'.pdf';
				$result = write_file( $file_path, $data);
				$return_val['result']['file_path'] = base_url() . $file_path;
				
			}
		}
		
		echo json_encode( $return_val );

	}

	public function get_profile_bills() {
		$acc_id = $this->input->post('acc_id');

		$this->load->model('report_model');
		
		$bills = $this->report_model->get_customer_aging_report('', '', '', $acc_id);

		echo json_encode($bills);
	}

	public function push_notification_dispatch()
	{
		if (! $this->authenticate_push()) {
			header('HTTP/1.1 401 Unauthorized');
			echo json_encode(array('status' => 'error', 'msg' => 'Unauthorised'));
			return;
		}

		$this->load->library('push_service');

		if (! $this->push_service->is_enabled()) {
			echo json_encode(array('status' => 'ok', 'msg' => 'disabled', 'sent' => 0));
			return;
		}

		$result = $this->push_service->run();

		echo json_encode(array(
			'status'   => 'ok',
			'claimed'  => $result['claimed'],
			'sent'     => $result['sent'],
			'failed'   => $result['failed'],
			'retrying' => $result['retrying'],
			'pruned'   => $result['pruned'],
			'released' => $result['released']
		));
	}

	protected function authenticate_push()
	{
		if (empty($this->push_cfg['internal_api_enabled'])) {
			log_message('error', '[push] internal API called while disabled');
			return FALSE;
		}

		$secret = isset($this->push_cfg['internal_api_secret']) ? (string) $this->push_cfg['internal_api_secret'] : '';

		// An empty or placeholder secret must fail closed. Otherwise a
		// half-finished deploy leaves the endpoint open to anyone.
		if ($secret === '' or strpos($secret, 'CHANGE_ME') === 0) {
			log_message('error', '[push] internal API secret is not configured - refusing');
			return FALSE;
		}

		$timestamp = $this->header('X-Push-Timestamp');
		$nonce     = $this->header('X-Push-Nonce');
		$signature = $this->header('X-Push-Signature');

		if ($timestamp === '' or $nonce === '' or $signature === '') {
			return FALSE;
		}

		// Bounds replay without storing nonces. A captured request is
		// useless once the window closes.
		$window = isset($this->push_cfg['internal_api_window']) ? (int) $this->push_cfg['internal_api_window'] : 300;

		if (abs(time() - (int) $timestamp) > $window) {
			log_message('error', '[push] internal API timestamp outside the ' . $window . 's window');
			return FALSE;
		}

		$expected = hash_hmac('sha256', $timestamp . '.' . $nonce, $secret);

		// hash_equals, not ==. A timing-safe comparison costs nothing here
		// and a naive one leaks the signature a byte at a time.
		if (! hash_equals($expected, $signature)) {
			log_message('error', '[push] internal API signature mismatch');
			return FALSE;
		}

		return TRUE;
	}

	protected function header($name)
	{
		$key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

		if (isset($_SERVER[$key])) {
			return trim((string) $_SERVER[$key]);
		}

		if (function_exists('getallheaders')) {
			foreach ((array) getallheaders() as $header => $value) {
				if (strcasecmp($header, $name) === 0) {
					return trim((string) $value);
				}
			}
		}

		return '';
	}
}
