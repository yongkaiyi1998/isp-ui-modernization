<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
require_once(APPPATH . 'controllers/bill.php');

class Pdfapi extends CI_Controller {

	protected $vars = [];
	protected $msg = [];

	//~ public $adj_data;
	private $e_key = "1nf0n4l";
	function __construct() {
        parent::__construct();
		//header('Content-Type: application/json');

		//$this->load->library(array('form_validation','session','upload'));
		//$this->load->helper(array('url','html','form'));
		$this->load->helper('custom_helper');
		$this->load->library('parser');
		$this->load->helper('url');

		$this->load->helper('process_helper');
		$this->load->helper('puppeteer_helper');

		$this->load->model('common_model');
		$this->load->model('report_model');

		$this->load->model('app_config_model');

    }

	public function verify_serial( $concat_var, $md5_concat_var ){
		$private_key = $this->e_key;					
		if( md5( $concat_var . $private_key ) == $md5_concat_var ){
			return 1;
		}else{
			return 0;
		}
	}

    public function index($id)
    {
		if(empty($id)) {
			echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
		}else{
			$random_str = substr(md5(uniqid(mt_rand(), true)), 0, 10);
			$pdf_filename = 'pdf_' . $random_str;

			$result = $this->bill_statement('bill', $id, 1, $pdf_filename);
			if($result == 'Bill not found.'){
				echo json_encode(array("status" => "error", "msg" => $result));
			}else{
				echo json_encode(array("pdf_link" => $result));
			}
		}
    }

    public function test($id)
    {
			if(empty($id))
			{
				echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
			}else{
				$random_str = substr(md5(uniqid(mt_rand(), true)), 0, 10);
				$pdf_filename = 'pdf_' . $random_str;

				$result = $this->bill_statement('bill', $id, 1, $pdf_filename);
				if($result == 'Bill not found.'){
					echo json_encode(array("status" => "error", "msg" => $result));
				}else{
					echo json_encode(array("pdf_link" => $result));
				}
			}
    }

    public function payment_receipt($payment_no, $scode)
    {
    	//check scode correct
		if( $this->verify_serial( $payment_no , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
			return false;
		}

    	//call puppeteer link to function to generate pdf
		$scode = md5($payment_no . $this->e_key);
		puppeteer_print_preview($this->config->item('base_url').'pdfapi/payment_receipt_pdf/'.$payment_no.'/'.$scode, $this->config->item('upload_path').'/temp/pdf/payment_receipt_'.$payment_no.'.pdf', $this->config->item('proj_path'), $this->config->item('chrome_loc'));

    	//return pdf link to front end
    	echo json_encode(array("pdf_path" => $this->config->item('upload_path').'/temp/pdf/payment_receipt_'.$payment_no.'.pdf', "pdf_link" => $this->config->item('upload_url').'temp/pdf/payment_receipt_'.$payment_no.'.pdf'));
    }

    public function payment_receipt_pdf($payment_no, $scode)
    {

    	//check scode correct
		if( $this->verify_serial( $payment_no , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
			return false;
		}

		$this->load->helper('custom_helper');
		$this->load->model('payment_model');

    	//payment receipt code and view here
    	$get_payment	= $this->payment_model->get_payment($payment_no);
		if(!empty($get_payment['amount'])){
			$amount_split = explode('.', $get_payment['amount']);
			$cents = ' ';
			if(!empty($amount_split[1]) && $amount_split[1] != '00'){
				$cent_amount 	= (int)$amount_split[1];
				$cents 			.= 'and '.convert_number_to_words($cent_amount).' cent ';
			}
			$get_payment['amount_in_words'] = convert_number_to_words($amount_split[0]).$cents.'only';

			$use_malay = $this->config->item('use_malay');
			if ($use_malay) {
				$get_payment['amount_in_words_malay'] = ucwords(convert_number_to_words_malay($get_payment['amount']).' sahaja');
			} else {
				$get_payment['amount_in_words_malay'] = $get_payment['amount_in_words'];
			}
		}

		$bill_details = array();
		$bill_detail_query 	= "bill_no = '".$get_payment['ref_no']."'";
		$bill_detail 		= $this->common_model->get_table('bill_detail','*, amount as item_amount, remark as item_remark',$bill_detail_query);
		if (!empty($bill_detail)) {
			$bill_type_list = $this->app_config_model->load_sys_bill_type();
			$cnt = 1;
			foreach ($bill_detail as $b_key => $b_val) {
				$bill_detail[$b_key]['item_count'] = $cnt;
				$bill_detail[$b_key]['bill_type_name'] = $bill_type_list[$b_val['bill_type']] ?? '';
				$bill_detail[$b_key]['bill_type_name_with_remark'] = ($bill_type_list[$b_val['bill_type']] ?? '') . ( !empty($b_val['remark']) ? ' - '.$b_val['remark'] : '' );
				$cnt++;
			}
		}
		$get_payment['bill_details'] = $bill_detail;

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'date_format_printing' ");
		$date_format_printing = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'payment' AND `key` = 'pay_no_prefix' ");
		$pay_no_prefix = $config[0]['val'];

		$get_payment['tranx_date'] = date_toggle($get_payment['tranx_date'],$date_format_printing);

		$get_payment['pay_date'] = date_toggle($get_payment['pay_date'],$date_format_printing);

		$pay_no_prefix = $pay_no_prefix ;
		$get_payment['payment_no'] = $pay_no_prefix . $get_payment['payment_no'] ;
		
		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$content_data['company_email'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$content_data['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$content_data['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$content_data['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$content_data['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$content_data['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_brn' ");
		$content_data['company_brn'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_state' ");
		$company_state = $config[0]['val'];
		$content_data['company_state'] = $this->common_model->get_state_name_from_einvoice_code($company_state);

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_fax' ");
		$content_data['company_fax'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$content_data['company_email'] = $config[0]['val'];

		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$input['data'][] = $get_payment;

		$header_data = $this->vars;
		$header_data['title']			= 'print preview | payment receipt';
		$header_data['description']		= 'to print receipt';
		$content_data['data']			= (empty($input['data']))?'':$input['data'];

		$data['msg'] = $this->msg;

		$html  = '';
		$html .= $this->load->view('templates/print_header_genpdf_half', $header_data, true);
		//$html .= $this->parser->parse('payment/payment_receipt_pdf', $content_data, true);

		$doc_template = $this->config->item('doc_template');

		if (!empty($doc_template)) {

			if (file_exists(FCPATH.'application/views/payment/template/'.$doc_template.'/payment_receipt_pdf.php')) {
				$html .= $this->parser->parse('payment/template/'.$doc_template.'/payment_receipt_pdf', $content_data, true);
			} else {
				$html .= $this->parser->parse('payment/payment_receipt_pdf', $content_data, true);
			}

		} else {
			$html .= $this->parser->parse('payment/payment_receipt_pdf', $content_data, true);
		}

		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;
		return false;

    }

    public function get_bill()
    {
		if(!$_POST)
		{
			echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
		}else{
			$id = $this->input->post('id');
			$scode = $this->input->post('scode');

			if(empty($id) || empty($scode))
			{
				echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
			}else{
				//$random_str = substr(md5(uniqid(mt_rand(), true)), 0, 10);
				//$pdf_filename = 'pdf_' . $random_str;

				puppeteer_print_preview(
				$this->config->item('base_url').'pdfapi/bill_statement/bill/'.$id.'/'.$scode, 
				$this->config->item('upload_path').'/temp/pdf/statement_'.$id.'.pdf', 
				$this->config->item('proj_path'), 
				$this->config->item('chrome_loc'));

				/*$result = $this->bill_statement('bill', $id, $scode, '');
				if($result == 'Bill not found.'){
					echo json_encode(array("status" => "error", "msg" => $result));
				}else{
					echo json_encode(array("pdf_link" => $result));
				}*/

				//echo json_encode(array("pdf_link" => $result));
				echo json_encode(array("pdf_path" => $this->config->item('upload_path').'/temp/pdf/statement_'.$id.'.pdf', "pdf_link" => $this->config->item('upload_url').'temp/pdf/statement_'.$id.'.pdf'));
			}
		}
    }

	public function get_commission()
    {
		if(!$_POST)
		{
			echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
		}else{
			$acc_id = $this->input->post('acc_id');			
			$agent_name = $this->input->post('agent_name') ?? '';			
			$filename_agent_name = substr(preg_replace('/[^a-z0-9_\-]/', '', str_replace(' ', '_', strtolower($agent_name))), 0, 50) ?: 'agent';
			$scode = $this->input->post('scode');
			$sel_date = $this->input->post('sel_date') ?? 'all';
			$customer_no = $this->input->post('customer_no') ?? '';
			$upline = $this->input->post('upline') ?? 0;
			$dealer_no = $this->input->post('dealer_no') ?? 0;

			if(empty($acc_id) || empty($scode)){
				echo json_encode(array("status" => "error", "msg" => "Invalid Access"));
			}else{
				$html = $this->dealer_commission($acc_id, $scode, $sel_date, $customer_no, $dealer_no, $upline);
				$this->load->helper(array('dompdf', 'file'));
				$pdf_data = pdf_create($html, '', false);

				$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/commission_'.$acc_id.'.pdf', $pdf_data);

				echo json_encode(array("pdf_path" => $this->config->item('upload_path').'/temp/pdf/commission_'.$acc_id.'.pdf', "pdf_link" => $this->config->item('upload_url').'temp/pdf/commission_'.$acc_id.'.pdf'));
			}
		}
    }

	function installation_form( $customer_no, $scode ){

		if( $this->verify_serial( $customer_no , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "Scode Error";
			return false;
		}

		$this->load->model('customer_model');
		$this->load->model('common_model');

		$customer = $this->customer_model->get_customer( $customer_no );
		
		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print installation form';
		
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$customer['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		
		if( $customer['pic_name'] == '' ){
			$customer['pic_name'] = $customer['name'];
		}
		
		if( $customer['mobile_num'] != '' ){
			$customer['contact_no'] = $customer['mobile_num'];
		}elseif( $customer['tel_num'] != '' ){
			$customer['contact_no'] = $customer['tel_num'];
		}
		
		$this->load->model('building_model');
		$building = $this->building_model->get_building( $customer['building'] );
		$customer['building_name'] = $building['name'];
		
		if( $customer['category'] == 'r' ){
			$customer['residential_check'] = '/';
			$customer['business_check'] = '';
		}else{
			$customer['residential_check'] = '';
			$customer['business_check'] = '/';			
		}
		
		if( $customer['contract_month'] != '' && $customer['contract_month'] >= 0 ){
			$customer['contract_month'] = $customer['contract_month'] . ' months';
		}

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$customer['company_addr_1'] = $config[0]['val'];

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$customer['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$customer['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$customer['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$customer['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$customer['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$customer['company_phone'] = $config[0]['val'];

		$frontend_url = $this->config->item('frontend_url') ?? false;
		$customer['tnc_url'] = $frontend_url 
									? 
										$customer['category'] == 'r' 
											?
												$frontend_url . $this->config->item('residential_tnc_endpoint') ?? 'tnc'
											:
												$frontend_url . $this->config->item('commercial_tnc_endpoint') ?? 'CommercialTnc'
									:
										'';

		$customer['print_color'] = 1;
		$customer['print_mode'] = 1;

		$signature = $this->common_model->get_file_attachment_with_signature_form_info('customer', $customer_no, 'customer_signature', 0);

		if (!empty($signature)) {
			$customer['signature'] = $this->config->item('upload_url') . $signature[0]['local_path'];
			$customer['sign_date'] = $signature[0]['created_date'];
			$customer['signer_name'] 	= $signature[0]['signer_name'];
			$customer['signer_ic'] 		= $signature[0]['signer_ic'];
		} else {
			$select = 'acc_name, icno, pic_name, pic_nric_passport, acc_type';
			$where = ['acc_id ' => $customer['profile_id']];
			$profile = $this->common_model->get_table('profile', $select, $where);

			$customer['signature'] 		= '';
			$customer['sign_date'] 		= '';
			if($profile[0]['acc_type'] === 'r'){
				$signer_name = $profile[0]['acc_name'];
				$signer_ic = $profile[0]['icno'];
			}else{
				$signer_name = $profile[0]['pic_name'];
				$signer_ic = $profile[0]['pic_nric_passport'];
			}
			$customer['signer_name'] 	= $signer_name;
			$customer['signer_ic'] 		= $signer_ic;
		}

		//Currently selected equipment
		$customer['equipment_list'] = $this->customer_model->get_equipment_record($customer_no);

		$html = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		//$html .= $this->load->view('templates/print_menu', $menu_data, true);
		$html .= $this->parser->parse('customer/installation_form', $customer, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function termination_form( $customer_no, $scode ) {

		if( $this->verify_serial( $customer_no , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "Scode Error";
			return false;
		}

		$this->load->model('customer_model');
		$this->load->model('common_model');

		$customer = $this->customer_model->get_customer( $customer_no );

		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print termination form';
		$header_data['cssfiles'][] 	= 'css/theme/bootstrap-grid.css';
		
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$customer['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		
		if( $customer['pic_name'] == '' ){
			$customer['pic_name'] = $customer['name'];
		}
		
		if( $customer['mobile_num'] != '' ){
			$customer['contact_no'] = $customer['mobile_num'];
		}elseif( $customer['tel_num'] != '' ){
			$customer['contact_no'] = $customer['tel_num'];
		}
		
		$this->load->model('building_model');
		$building = $this->building_model->get_building( $customer['building'] );
		$customer['building_name'] = $building['name'];
		
		if( $customer['category'] == 'r' ){
			$customer['residential_check'] = '/';
			$customer['business_check'] = '';
		}else{
			$customer['residential_check'] = '';
			$customer['business_check'] = '/';			
		}
		
		if( $customer['contract_month'] != '' && $customer['contract_month'] >= 0 ){
			$customer['contract_month'] = $customer['contract_month'] . ' month(s)';
		}

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$customer['company_addr_1'] = $config[0]['val'];

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$customer['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$customer['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$customer['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$customer['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$customer['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$customer['company_phone'] = $config[0]['val'];
	
		// signature
		$signature = $this->common_model->get_file_attachment_with_signature_form_info('customer', $customer_no, 'termination_signature', 0, 'termination form', NULL, NULL);

		if (!empty($signature)) {
			$customer['signature'] 		= $this->config->item('upload_url') . $signature[0]['local_path'];
			$customer['sign_date'] 		= $signature[0]['created_date'];
			$customer['signer_name'] 	= $signature[0]['signer_name'];
			$customer['signer_ic'] 		= $signature[0]['signer_ic'];
		} else {

			$select = 'acc_name, icno, pic_name, pic_nric_passport, acc_type';
			$where = ['acc_id ' => $customer['profile_id']];
			$profile = $this->common_model->get_table('profile', $select, $where);

			$customer['signature'] 		= '';
			$customer['sign_date'] 		= '';
			if($profile[0]['acc_type'] === 'r'){
				$signer_name = $profile[0]['acc_name'];
				$signer_ic = $profile[0]['icno'];
			}else{
				$signer_name = $profile[0]['pic_name'];
				$signer_ic = $profile[0]['pic_nric_passport'];
			}
			$customer['signer_name'] 	= $signer_name;
			$customer['signer_ic'] 		= $signer_ic;
		}

		//Equipment list
		$customer['equipment_types'] = $this->common_model->get_equipment_type_list();

		//Currently selected equipment
		$customer['equipment_list'] = $this->customer_model->get_equipment_record($customer_no);

		$frontend_url = $this->config->item('frontend_url') ?? false;
		$customer['tnc_url'] = $frontend_url 
									? 
										$customer['category'] == 'r' 
											?
												$frontend_url . $this->config->item('residential_tnc_endpoint') ?? 'tnc'
											:
												$frontend_url . $this->config->item('commercial_tnc_endpoint') ?? 'CommercialTnc'
									:
										'';

		$customer['customer_sign'] = $this->customer_model->check_termination_sign($customer_no);

		$customer['sel_state_list'] 			= $this->common_model->get_state_list();
		$customer['sel_building_list'] 	= $this->common_model->get_building_list();

		$latest_termination_flow = $this->customer_model->check_latest_termination_flow($customer_no);
		$customer['current_termination_flow'] = $latest_termination_flow['termination_status'] ?? 'A';

		$customer['termination_init'] = false;
		$customer['termination_confirm'] = false;
		$customer['is_customer'] = false;

		$customer['termination_data'] = $this->customer_model->get_latest_termination_info($customer_no);

		$customer['customer_no'] = $customer_no;

		$html = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		//$html .= $this->load->view('templates/print_menu', $menu_data, true);
		$html .= $this->parser->parse('customer/termination_form', $customer, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function bill_invoice($type = '', $type_val = '', $scode) {

		if( $this->verify_serial( $type_val , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "Scode Error";
			return false;
		}

		$this->load->model('bill_model');
		$this->load->model('common_model');
		$this->load->model('app_config_model');

		if ( empty($type) || empty($type_val) || (($type != 'no' )&&($type != 'date')))  {
			echo 'Bill Invoice not found!';
			return false;
		}

		$_SESSION['cust_category'] = $this->app_config_model->load_sys_customer_category();
		$_SESSION['bill_type'] = $this->app_config_model->load_sys_bill_type();
		$_SESSION['state'] = $this->app_config_model->load_sys_state();	

		if ($type == 'no') $invoice = $this->bill_model->get_invoice($type_val,'');
		else if ($type == 'date') $invoice = $this->bill_model->get_invoice('',$type_val);

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'date_format_printing' ");
		$date_format_printing = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		foreach($invoice as $i_key => $i_val)
		{
			$invoice[$i_key]['bill_no'] 			= add_zero($invoice[$i_key]['bill_no']);
			$invoice[$i_key]['bill_date'] 			= date_toggle($invoice[$i_key]['bill_date'],$date_format_printing);
			$invoice[$i_key]['display_name'] 		= (empty($invoice[$i_key]['bill_name']))?$invoice[$i_key]['customer_name']:$invoice[$i_key]['bill_name'];
		}

		$header_data 				= $this->vars;
		$header_data['title'] 		= 'print preview | invoice';
		$header_data['description']	= 'to print invoice';
		$content_data['data']		= $invoice;

		$content_data['bill_no'] = $type_val;

		$gst_reg_no	 =  $this->common_model->get_gst_reg_no();
		$content_data['gst_reg_no']	= $gst_reg_no;

        $gst_amount	= $this->common_model->get_default_tax_amount();
		$content_data['default_tax'] = empty($gst_amount) ? '0.0' : $gst_amount[0]['percent'];
		
		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['bills_footer'] = $footer[0]['val'];
		
		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
		$content_data['company_tax_number'] = $tax[0]['val'];
		
		$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
		$content_data['jompay_biller_code'] = $jompay[0]['val'];

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$content_data['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$content_data['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$content_data['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$content_data['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$content_data['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_brn' ");
		$content_data['company_brn'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_state' ");
		$company_state = $config[0]['val'];
		$content_data['company_state'] = $this->common_model->get_state_name_from_einvoice_code($company_state);

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_fax' ");
		$content_data['company_fax'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$content_data['company_email'] = $config[0]['val'];

		//einvoice qr code
		$content_data['einvoice_qr'] = '';
		if (!empty($invoice[$type_val]['einvoice_longid']) && !empty($invoice[$type_val]['einvoice_uuid'])) {
			$this->load->model('einvoice_api_model');
			$qr_data = array();
			$qr_data['portal'] = $this->config->item('einvoice_portal_url');
			$qr_data['uuid'] = $invoice[$type_val]['einvoice_uuid'];
			$qr_data['longid'] = $invoice[$type_val]['einvoice_longid'];
			$qr_link = $this->einvoice_api_model->return_qr_link($qr_data);

			if (file_exists(FCPATH.'phpqrcode/qrlib.php')) {
				include_once(FCPATH.'phpqrcode/qrlib.php'); 
				ob_start();
				QRCode::png($qr_link, null);
				$imageString = base64_encode( ob_get_contents() );
				ob_end_clean();

				$einvoice_html = '
				<img src="data:image/png;base64,'.$imageString.'" style="width:80px;" />
				';

				$content_data['einvoice_qr'] = $einvoice_html;
			}

		}

		$html  = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		//$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);

		$doc_template = $this->config->item('doc_template');

		if (!empty($doc_template)) {

			if (file_exists(FCPATH.'application/views/bill/template/'.$doc_template.'/bill_invoice.php')) {
				$html .= $this->parser->parse('bill/template/'.$doc_template.'/bill_invoice', $content_data, true);
			} else {
				$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);
			}

		} else {
			$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);
		}

		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function bill_statement($statement_by, $statement_key, $scode, $filter_date='') {

		if( $this->verify_serial( $statement_key , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "Scode Error";
			return false;
		}

		$this->load->model('bill_model');
		$this->load->model('common_model');

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'date_format_printing' ");
		$_SESSION['config']['date_format_printing'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'inv_no_prefix' ");
		$_SESSION['config']['inv_no_prefix'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		
		$this->load->model('bill_model');
		$bill_model = $this->bill_model->bill_statement($statement_by, $statement_key, 0, '', $filter_date);
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

		$this->load->helper('form');
		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print bill statements';
		$content_data['data']		= (empty($bill_model['input']['data']))?'':$bill_model['input']['data'];
		$content_data['gst_reg_no']	= (empty($bill_model['gst_reg_no']))?'':$bill_model['gst_reg_no'];
		
		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['bills_footer'] = $footer[0]['val'];
		
		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
		$content_data['company_tax_number'] = $tax[0]['val'];
		
		$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
		$content_data['jompay_biller_code'] = $jompay[0]['val'];

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$content_data['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$content_data['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$content_data['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$content_data['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$content_data['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_brn' ");
		$content_data['company_brn'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_state' ");
		$company_state = $config[0]['val'];
		$content_data['company_state'] = $this->common_model->get_state_name_from_einvoice_code($company_state);

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_fax' ");
		$content_data['company_fax'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$content_data['company_email'] = $config[0]['val'];
		
		$data['msg'] 				= $this->msg;
		$gen_pdf 					= $bill_model['gen_pdf'];

		$html  = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);

		$doc_template = $this->config->item('doc_template');

		if (!empty($doc_template)) {

			if (file_exists(FCPATH.'application/views/bill/template/'.$doc_template.'/bill_statement.php')) {
				$html .= $this->parser->parse('bill/template/'.$doc_template.'/bill_statement', $content_data, true);
			} else {
				$html .= $this->parser->parse('bill/bill_statement', $content_data, true);
			}

		} else {
			$html .= $this->parser->parse('bill/bill_statement', $content_data, true);
		}

		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function dealer_commission($acc_id, $scode, $sel_date='', $customer_no='', $dealer_no = 0, $upline = 0) {
		if ($this->verify_serial($acc_id, $scode) !== 1) {
			echo "Invalid Access.";
			return false;
		}

		$this->load->model('dealer_model');

		$sel_date 	= $sel_date ?? 'all';

		$data = [
			'is_postback'    	=> 0,
			'sel_building'   	=> 'all',
			'sel_category'   	=> 'all',
			'sel_status'     	=> 'all',
			'sel_customer'   	=> $customer_no ?: 'all',
			'sel_date' 			=> $sel_date,
			'dealer_no'		 	=> $dealer_no,
			'upline'		 	=> $upline ?: 0
		];

		$data['data_row']  = $this->dealer_model->api_dealer_comm_listing(
			$data['sel_building'],
			$data['sel_category'],
			$data['sel_status'],
			$data['sel_date'],
			$data['sel_customer'],
			$data['dealer_no'],
			$data['upline']
		);

		$data['page_title'] 	= empty($upline) ? 'Agent Commission' : 'Downline Commission';
		$data['sel_date'] 		= $sel_date;
		$data['isprint']    	= 1;
		$data['sel_date_text'] 	= $sel_date == 'all' ? 'All Time' : date("F Y", strtotime(substr($sel_date, 0, 4)."-".substr($sel_date, 4, 2)."-01"));
		$data['row_html']   	= $this->parser->parse('report/dealer_commission_rows', $data, true);

		$header_data = [
			'title'       => empty($upline) ? 'Agent Commission Report' : 'Downline Commission Report',
			'description' => empty($upline) ? 'Agent Commission Report' : 'Downline Commission Report',
		];

		$html  = $this->load->view('templates/print_header_genpdf', $header_data, true);
		$html .= $this->parser->parse('report/dealer_commission', $data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		return $html;
	}

	function customer_support_form($cs_no, $scode) {

		if( $this->verify_serial( $cs_no , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "Scode Error";
			return false;
		}

		$this->load->model('customer_model');
		$this->load->model('common_model');

		$cs = $this->customer_model->get_single_customer_support($cs_no);

		$this->load->helper('form');
		$header_data 				= $this->vars;
		$header_data['title']		= 'Print Preview';
		$header_data['description']	= 'To print Customer Support Form';
		$content_data['data']		= (empty($cs))?'':$cs;

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$content_data['company_addr_1'] = $config[0]['val'];

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$content_data['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$content_data['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$content_data['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$content_data['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$content_data['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$content_data['print_color'] = 1;

		$html  = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		//$html .= $this->load->view('templates/print_menu', $menu_data);
		$html .= $this->parser->parse('customer/customer_support_form', $content_data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function customer_listing_report() {

		$scode_str = $_POST['scode_str'];
		$scode = $_POST['scode'];
		if( $this->verify_serial( $scode_str , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "ERROR";
			return false;
		}

		$data = array();
		$is_postback 	= $_POST['is_postback'];		
		$sel_category 	= $_POST['sel_category'] ?? '';
		$sel_status 	= $_POST['sel_status'] ?? '';
		$sel_status_date= $_POST['sel_status_date'] ?? '';
		$sel_activated 	= $_POST['sel_activated'] ?? '';
		$sel_building 	= $_POST['sel_building'] ?? '';
		$sel_package 	= $_POST['sel_package'] ?? '';
		$sel_dealer 	= $_POST['sel_dealer'] ?? '';
		$txt_search 	= $_POST['txt_search'] ?? '';
		$txt_customer_no= $_POST['txt_customer_no'] ?? '';
		$date_from		= $_POST['date_from'] ?? '';
		$date_to		= $_POST['date_to'] ?? '';
		$order_by		= $_POST['order_by'] ?? '';
		$order_type		= $_POST['order_type'] ?? '';

		if (empty($sel_category)) $sel_category = 'r';
		
		$data['sel_category'] 		= $sel_category;
		$data['sel_status'] 		= $sel_status;
		$data['sel_status_date']	= $sel_status_date;
		$data['sel_activated'] 		= $sel_activated;
		$data['sel_building'] 		= $sel_building;
		$data['sel_package'] 		= $sel_package;
		$data['sel_dealer'] 		= $sel_dealer;
		$data['txt_search'] 		= $txt_search;
		$data['txt_customer_no']    = $txt_customer_no;
		$data['date_from']			= $date_from;
		$data['date_to']			= $date_to;
		$data['page_title'] 		= 'Customer Listing';
		$data['form_action'] 		= base_url('report/customer_listing');
		$data['order_by']			= $order_by;
		$data['order_type']			= $order_type;

		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_status_list'] 	= $this->common_model->get_acc_status_list();
		
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list'] 	= $this->common_model->get_package_list();
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();
		
		$sess_cust_category 		= $this->app_config_model->load_sys_customer_category();
		$sess_acc_status			= $this->app_config_model->load_sys_account_status();	
		$sess_state 				= $this->app_config_model->load_sys_state();
		$sess_building 				= $this->app_config_model->load_building();
		$filter_text 				= 'Search : ' . $txt_search;
		$query_where 				= "AND c.category = '$sel_category' ";
		$filter_text 				.= '<br>Category : ' . $sess_cust_category[$sel_category];

		if($sel_status != 'all' && !empty($sel_status) && $sel_status != 'signup' && $sel_status != 'activated' ){
			$query_where = $query_where." AND c.status = '".$this->db->escape_str($sel_status)."' ";
			$filter_text .= '<br />Status : ' . $sess_acc_status[$sel_status];
		}
		
		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_to'] ;
		}
		
		
		if( $date_from != '' )
			$query_where .= " AND cs.transact_date >= '".( date('Y-m-d', strtotime($this->db->escape_str($date_from))) )."' ";
		if( $date_to != '' )
			$query_where .= " AND cs.transact_date <= '".( date('Y-m-d', strtotime($this->db->escape_str($date_to))) )."' ";

		if($sel_status_date != 'all' && !empty($sel_status_date)){
			$query_where .= " AND cs.status = '".$this->db->escape_str($sel_status_date)."' ";
		}


		if ($sel_activated != 'all' && !empty($sel_activated)) {
			if ($sel_activated == 'yes')
				$query_where = $query_where."AND cs.status IN ('A', 'S', 'T') ";
			else
				$query_where = $query_where."AND (cs.status IN ('P', 'C') OR cs.status IS NULL) ";
			$filter_text .= '<br>Activated : ' . $sel_activated;
		}

		if ($sel_building != 'all' && !empty($sel_building)) {
			$query_where = $query_where."AND c.building = '".$this->db->escape_str($sel_building)."' ";
			$filter_text .= '<br>Building : ' . $sess_building[$sel_building];
		}
		if ($sel_package != 'all' && !empty($sel_package)) {
			$query_where = $query_where."AND c.package = '".$this->db->escape_str($sel_package)."' ";
			//$filter_text .= '<br>Package : ' . $sess_package[$sel_package];
		}

		if ($sel_package != 'all' && !empty($sel_package)) {
			$query_where = $query_where."AND c.package = '".$this->db->escape_str($sel_package)."' ";
			//$filter_text .= '<br>Package : ' . $sess_package[$sel_package];
		}

		if ($sel_dealer != 'all' && !empty($sel_dealer)) {
			$query_where = $query_where."AND c.dealer = '".$this->db->escape_str($sel_dealer)."' ";
			//$filter_text .= '<br>Package : ' . $sess_package[$sel_package];
		}

		$customer = array();
		//if ( !empty($is_postback) ) 
		//{

			$query_order =  "";
			if( $order_by != "" && $order_type != "" ){

				$sel_order_by = "";
				if ($order_by == 'status') {
					$sel_order_by = 'cs.status';
				} else if ($order_by == 'status_date') {
					$sel_order_by = 'cs.transact_date';
				} else if ($order_by == 'dealer') {
					$sel_order_by = 'c.dealer';
				} else {
					$sel_order_by = $order_by;
				}

				$query_order .= " ORDER BY ".$sel_order_by." ". $this->db->escape_str($order_type) ;
			}
			
			$result = $this->report_model->get_customer_join_building($txt_search,$query_where, $query_order);	
			$no = 0;
			//~ foreach ($query->result_array() as $row) 			
			foreach ($result['result_array'] as $row) 
			{
				$no++;
				$customer[] = array(
						'no' => $no,
						'customer_no' => $row['customer_no'],
						'name' => $row['profile_name'],
						'login_username' => $row['login_username'],
						'gender' => strtoupper($row['gender']),
						'status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
						'status_date' => (!empty($row['latest_status_date'])?date('Y-m-d', strtotime($row['latest_status_date'])):''),
						'category' => $sess_cust_category[$row['category']],
						'building' => $row['building_name'],
						'package_name' => $row['package_name'],
						'monthly_charge' => $row['monthly_charge'],
						'mobile_num' => $row['pic_mobile'],
						'email_1' => $row['pic_email_1'],
						'email_2' => $row['pic_email_2'],
						'dealer' => $row['dealer'],
						'inst_addr1' => $row['inst_addr1'],
						'inst_addr2' => $row['inst_addr2'],
						'inst_city' => $row['inst_city'],
						'inst_postcode' => $row['inst_postcode'],
						'inst_state' => isset( $row['inst_state'] ) ? (isset($sess_state[$row['inst_state']]) ? $sess_state[$row['inst_state']] : '') : '',  
						'package_start' => ( $row['package_changed_date'] == '0000-00-00' ? ( $row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date'] ) : $row['package_changed_date'] ),
				);
			}
		//}
		
		$data['data_row'] 		= $customer;
		$data['filter_text'] 	= $filter_text;
		$data['msg'] 			= $this->msg;

		$data['print_color'] = 1;

		//can consider refactor into file path so its faster
		$data['logo_1'] = $this->config->item('logo_img');	
		$data['logo_2'] = $this->config->item('logo_img_2');
		$data['proj_name'] = $this->config->item('proj_name');
		$data['logo_1_width'] = $this->config->item('logo_img_width');
		$data['logo_2_width'] = $this->config->item('logo_img_2_width');

		$html = '';
		$html .= $this->load->view('templates/header', $this->vars, true);
		//$this->load->view('templates/menu',$this->menu);
		$html .= $this->parser->parse('report/customer_listing_print',$data, true);
		$html .= $this->load->view('templates/footer', '', true);

		echo $html;

	}

	function sales_report() {

		if (!$this->verify_serial($_POST['scode_str'], $_POST['scode'])) {
			echo "ERROR";
			return false;
		}

		$sel_category  = $_POST['sel_category'] ?? 'all';
		$sel_building  = $_POST['sel_building'] ?? 'all';
		$txt_date_start= $_POST['txt_date_start'] ?? '';
		$txt_date_end  = $_POST['txt_date_end'] ?? '';
		$order_by      = $_POST['order_by'] ?? '';
		$order_type    = $_POST['order_type'] ?? '';

		$query_where = " ";

		if ($sel_building != 'all') {
			$query_where .= " AND c.building = " . $this->db->escape($sel_building);
		}
		if ($sel_category != 'all') {
			$query_where .= " AND c.category = " . $this->db->escape($sel_category);
		}
		if (!empty($txt_date_start) && !empty($txt_date_end)) {
			$query_where .= " AND (csa.transact_date BETWEEN " .
				$this->db->escape($txt_date_start) . " AND " .
				$this->db->escape($txt_date_end) . ")";
		}

		$results = $this->report_model->sales_report($query_where, $order_by, $order_type);

		$building_list = $this->common_model->get_building_list() ?? [];

		$data = [
			'page_title'         	=> 'Sales Report',
			'description'        	=> 'Sales Report',
			'form_action'        	=> base_url('report/sales'),
			
			'sel_category_list'  	=> $this->common_model->get_category_list(),
			'sel_status_list'    	=> $this->common_model->get_customer_status_list(),
			'sel_building_list'  	=> $building_list,

			'building_list'      	=> array_column($building_list, 'name', 'building_no'),

			'date_start'         	=> $txt_date_start,
			'date_end'           	=> $txt_date_end,
			'order_by'           	=> $order_by,
			'order_type'		 	=> $order_type,
			'data_row'           	=> $results,
			'msg'                	=> $this->msg,
			'isprint'            	=> 1,
		];

		$data['category_list'] = [
			'r' => 'Residential',
			'b' => 'Business',
			's' => 'Business(R)',
			'w' => 'Wholesale',
			'd' => 'DIA',
			'e' => 'DIA(R)'
		];

		$data['pkg_list'] = $this->report_model->sales_report_pkg_count($query_where,$data['order_by'],$data['order_type']);

		$data['row_html'] = $this->parser->parse('report/sales_rows', $data, true);

		$html  = $this->load->view('templates/header', $data, true);
		$html .= $this->parser->parse('report/sales', $data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function detail_of_billing_report() {

		if (!$this->verify_serial($_POST['scode_str'], $_POST['scode'])) {
			echo "ERROR";
			return false;
		}

		$sel_category  = $_POST['sel_category'] ?? 'all';
		$sel_status    = $_POST['sel_status'] ?? 'all';
		$sel_bill_type = $_POST['sel_bill_type'] ?? 'all';
		$sel_building  = $_POST['sel_building'] ?? 'all';
		$txt_date_start= $_POST['txt_date_start'] ?? date('Y-m-01');
		$txt_date_end  = $_POST['txt_date_end'] ?? date('Y-m-t');
		$order_by      = $_POST['order_by'] ?? '';
		$order_type    = $_POST['order_type'] ?? '';

		$query_where = " WHERE b.is_void = '0'";

		if ($sel_building != 'all') {
			$query_where .= " AND c.building = " . $this->db->escape($sel_building);
		}
		if ($sel_category != 'all') {
			$query_where .= " AND c.category = " . $this->db->escape($sel_category);
		}
		if ($sel_status != 'all') {
			$query_where .= " AND cs.status = " . $this->db->escape($sel_status);
		}
		if ($sel_bill_type != 'all') {
			$query_where .= " AND bd.bill_type = " . $this->db->escape($sel_bill_type);
		}
		if (!empty($txt_date_start) && !empty($txt_date_end)) {
			$query_where .= " AND (b.bill_date BETWEEN " .
				$this->db->escape($txt_date_start) . " AND " .
				$this->db->escape($txt_date_end) . ")";
		}

		$results = $this->report_model->detail_of_billing($query_where, $order_by, $order_type);

		$building_list = $this->common_model->get_building_list() ?? [];

		$data = [
			'page_title'         	=> 'Details of Billing',
			'description'        	=> 'Details of Billing Report',
			'form_action'        	=> base_url('report/detail_of_billing'),
			
			'sel_category_list'  	=> $this->common_model->get_category_list(),
			'sel_status_list'    	=> $this->common_model->get_customer_status_list(),
			'sel_bill_type_list' 	=> $this->common_model->get_bill_type_list(),
			'sel_building_list'  	=> $building_list,

			'building_list'      	=> array_column($building_list, 'name', 'building_no'),

			'date_start'         	=> $txt_date_start,
			'date_end'           	=> $txt_date_end,
			'order_by'           	=> $order_by,
			'order_type'		 	=> $order_type,
			'data_row'           	=> $results,
			'msg'                	=> $this->msg,
			'isprint'            	=> 1,
		];

		$data['row_html'] = $this->parser->parse('report/detail_of_billing_rows', $data, true);

		$html  = $this->load->view('templates/header', $data, true);
		$html .= $this->parser->parse('report/detail_of_billing', $data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;
	}


	function print_quotation($so_id, $scode) {
		if( $this->verify_serial( $so_id , $scode ) == 1 ){
			//echo "OK!";
		} else {
			echo "Scode Error";
			return false;
		}

		$this->load->model('salesorder_model');
		$this->load->model('common_model');

		$so = $this->salesorder_model->get_so($so_id);

		$header_data 				= $this->vars;
		$header_data['title'] 		= 'print preview | quotation';
		$header_data['description']	= 'to print quotation';
		//_debug_array($so); exit;
		$content_data['data']		= $so;

		$content_data['quotation_no'] = $so['so_head']['so_num'];
		
		$gst_reg_no	 =  $this->common_model->get_gst_reg_no();
		$content_data['gst_reg_no']	= $gst_reg_no;

        $gst_amount	= $this->common_model->get_default_tax_amount();
		$content_data['default_tax'] = empty($gst_amount) ? '0.0' : $gst_amount[0]['percent'];
		
		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['bills_footer'] = $footer[0]['val'];
		
		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
		$content_data['company_tax_number'] = $tax[0]['val'];
		
		$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
		$content_data['jompay_biller_code'] = $jompay[0]['val'];

		$data['msg'] 				= $this->msg;

		$content_data['einvoice_qr'] = '';
		$content_data['display_name'] = $so['so_head']['cust_name'];
		$content_data['doc_title']	= "Quotation";

		$state_list = $this->common_model->get_state_list();
		$state_map = array_column($state_list, 'name', 'state_code');

		$content_data['bill_actual_state']	= $state_map[$so['so_head']['bill_state']] ?? 'Pulau Pinang';
		$content_data['del_actual_state']	= $state_map[$so['so_head']['del_state']] ?? 'Pulau Pinang';

		$address_keys = [
			'company_addr_1',
			'company_addr_2',
			'company_addr_3',
			'company_postal',
			'company_city',
			'company_state'
		];
		
		$company_address_parts = [];
		
		foreach ($address_keys as $key) {
			$result = $this->common_model->get_table('sys_config', '*', "`key` = '$key'");
			$val = $result[0]['val'] ?? null;
			if (!empty($val)) {
				$company_address_parts[] = is_array($val) ? implode(' ', $val) : $val;
			}
		}
		
		$content_data['company_address'] = implode(', ', $company_address_parts);

		$content_data['company_tel'] = $this->common_model->get_table('sys_config','*', "`key` = 'company_phone' ")[0]['val'] ?? null;

		$html = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		//$html .= $this->load->view('templates/print_menu', $menu_data, true);
		$html .= $this->parser->parse('salesorder/print', $content_data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	/*function test_post_send() {
		$this->load->helper('puppeteer_helper');
		puppeteer_print_preview_forpost('http://localhost/itelco/pdfapi/test_post', 'test_post.pdf', json_encode(array('a' => 1, 'b' => 2, 'c' => 3)));
	}*/

	function test_post( ){
		echo "<pre>";
		var_dump($_POST);
		echo "</pre>";
	}
}