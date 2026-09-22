<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
//~ include_once( APPPATH . 'controllers/package.php' );

class Customer_support extends DataPage_Controller {

	private $e_key = "1nf0n4l";
	
	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		check_acl('customer_support');
		$this->load->model('customer_model');
		$this->load->model('common_model');
		$this->load->helper('image_helper');
		$this->load->helper('puppeteer_helper');
		$this->load->model('docs_log_model');
		$this->load->model('email_model');
    }
	
	function set_form_validation($mode = 'search', $is_escalated = '', $change_package = false)
	{
		$cs_email_required = 'trim';
		if (!empty($is_escalated) && $is_escalated == 2) {
			$cs_email_required = 'trim|required';
		}

		$config = array(
					'save_support' => array (
						array('field' => 'customer_no', 'label' => 'Customer Number', 'rules' => 'trim|required'),
						array('field' => 'contact_no', 'label' => 'Contact Number', 'rules' => 'trim|required'),
						array('field' => 'customer_address', 'label' => 'Address', 'rules' => 'trim|required'),
						array('field' => 'service_by', 'label' => 'Service By', 'rules' => 'trim|required'),						
						array('field' => 'status', 'label' => 'Status', 'rules' => 'trim'),						
						array('field' => 'email_to_user_id', 'label' => 'Email', 'rules' => $cs_email_required)
					),
					'save_customer_support_service' => array(
						array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|required'),
						array('field' => 'desc', 'label' => 'Description', 'rules' => 'trim|required'),
						array('field' => 'sel_cs_type', 'label' => 'Category', 'rules' => 'trim|required'),
					),	
					'save_customer_support_setting' => array (
						array('field' => 'cs_no_prefix', 'label' => 'Prefix', 'rules' => 'trim|required'),
						array('field' => 'cs_no_length', 'label' => 'Running Number length', 'rules' => 'trim|required|numeric'),
						array('field' => 'cs_no', 'label' => 'Running Number', 'rules' => 'trim|required'),
					),
		);
		
		$this->form_validation->set_rules($config[$mode]);
	}
    
    public function index()
    {
		$total_row 		= 0;
		$page_item_no 	= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		
		$txt_search 	= $this->input->post('txt_search');
		$sel_premises 	= $this->input->post('sel_premises');
		$sel_status 	= $this->input->post('sel_status');
		
		$sess = $this->session->userdata;
	
		$query_where = "";
		if ( $sel_premises != '' && $sel_status !== '' ) {
			$query_where .= "AND cs.premises_type = '".$sel_premises."' ";
		}
		
		if ( $sel_status != '' ) {
			$query_where .= "AND cs.cs_status = '".$sel_status."' ";
		}
		
		$this->load->model('customer_model');
		$result = $this->customer_model->get_customer_support_listing( $txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where );
		
		$data['page_title'] 		= 'Trouble Ticket List';
		$data['form_action'] 		= base_url('customer_support');
		$data['row_data'] 			= $result['row'];
		$data['txt_search'] 		= $txt_search;
		$data['sel_premises'] 		= $sel_premises;
		$data['sel_status'] 		= $sel_status;
		$data['pagination'] 		= paginationSettings('', $result['total_row']);

		$data['msg'] 				= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/customer_support_list',$data);
		$this->load->view('templates/footer');
	}

	function add_customer_support($cs_no='',$convert_tt='') {
		$chk_temp_id = $this->input->post('temp_id');
		if( empty($convert_tt) ){
			$data['input'] = $this->customer_model->get_single_customer_support($cs_no);
		}else{
			$data['input'] = $this->customer_model->get_single_customer_support('');
			
			$tt_id = $cs_no;
			$this->load->model('ticket_model');
			$ticket = $this->ticket_model->get_single_trouble_ticket_by_id($tt_id);
			$cust   = $this->customer_model->get_customer($ticket['customer_no']);
			
			//$data['input']['cs_no']	= $ticket['tt_no'];
			$data['input']['premises_type'] = $cust['category'];
			$data['input']['package_name']  = $cust['package_name'];
			$data['input']['customer_no'] 	= $ticket['customer_no'];
			$data['input']['customer_no_name'] = $ticket['customer_no_name']; 
			$data['input']['contact_no'] 	= $ticket['contact_no'];
			$data['input']['customer_addr'] = $cust['inst_addr1'] . ", " . $cust['inst_addr2'] . ", " . $cust['inst_postcode'] . " " . $cust['inst_city'] . ", " . ( isset($_SESSION['state'][$cust['inst_state']]) ? $_SESSION['state'][$cust['inst_state']] : '' );
		}		
		
		if( $data['input']['cs_no'] == '' ){
			$cs_no = $this->customer_model->get_new_cs_no();
			$data['input']['cs_no'] = $cs_no['cs_no_full'];
			$data['input']['created_by'] = $this->user['idx'];
			$data['input']['created_by_name'] = $this->user['display_name'];
		}

		$user_list = $this->common_model->get_technical_list('customer_support');
		$user_list_arr = [];

		foreach ($user_list as $key => $user) {
			$user_list_arr[] = [
				'value' => $user['display_name'] . ' - [' . $user['email'] . ']',
				'label' => $user['display_name'],
				'idx' => $user['idx']
			];
		}

		$data['input']['email'] = '';
		foreach ($user_list as $key => $user) {
			if($user['idx'] == $data['input']['email_to_user_id']) {
				$data['input']['email'] = $user['display_name'] . ' - [' . $user['email'] . ']';
				break;
			}
		}

		$data['user_list'] = json_encode($user_list_arr);
		$data['job_tracking'] = $this->customer_model->get_job_tracking_detail($data['input']['cs_id']);

		$data['page_title'] 		= 'Add Trouble Ticket';
		$data['form_action'] 		= base_url('customer_support/save_customer_support');
		$data['sel_user']			= $this->common_model->get_technical_list('customer_support');
		$data['cs_service_list']	= $this->common_model->get_cs_service_list();
		$data['cs_problem_list']	= $this->common_model->get_cs_problem_list();
		$data['msg'] 				= $this->msg;

		if (!empty($chk_temp_id)) {
			$data['input']['temp_id'] = $chk_temp_id;
		}

		$data['sel_reply'] = $this->customer_model->get_service_ticket_reply_list($data['sel_user'], [$data['input']['service_by'] ?? 0]);

		$file_result	= ($data['input']['cs_id'] != '') ? $this->common_model->get_file_attachment('support', $data['input']['cs_id']) : [];

		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('ticket', $chk_temp_id, '', 0); 
			foreach ($leftover_attach as $attach) {
				array_push($file_result, $attach);
			}
		}
		if(!empty($file_result)) {
			foreach ($file_result as $key => $val) {

				$val['local_path'] = stripslashes($val['local_path']);
				$val['local_path'] = str_replace("%", "%25", $val['local_path']);

				$file_info = pathinfo($val['local_path']);
				$extension = strtolower($file_info['extension']);

				$file_result[$key]['extension'] = $extension;
			
				if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
					$file_result[$key]['file_type'] = 'image';

					$thumbnail_filename = 'thumbnail_' . $file_info['filename'] . '.jpeg';

					$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail_filename;

					$file_result[$key]['thumbnail_path'] = (file_exists($this->config->item('upload_path').$thumbnail_path)) ? $this->config->item('upload_url').$thumbnail_path : base_url("images/image.png");
				} elseif (in_array($extension, ['pdf'])) {
					$file_result[$key]['file_type'] = 'pdf';

					$thumbnail_filename = $file_info['filename'] . '-0.jpg';

					$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail_filename;

					$file_result[$key]['thumbnail_path'] = (file_exists($this->config->item('upload_path').$thumbnail_path)) ? $this->config->item('upload_url').$thumbnail_path : base_url("images/pdf.png");
				} elseif (in_array($extension, ['mp4', 'avi', 'mkv', 'mov', 'webm'])) {
					$file_result[$key]['file_type'] = 'video';
				} else {
					$file_result[$key]['file_type'] = 'doc';
				}
				
				$file_result[$key]['local_path'] = $this->config->item('upload_url').$val['local_path'];

				$file_result[$key]['created_by'] = $val['created_by'];
				$file_result[$key]['file_id'] = $val['file_id'];

				$file_result[$key]['is_temp'] = $val['is_temp'];
			}
			$data['attachment'] = $file_result;
		}

		$this->vars['jsfiles'][] = 'js/itelco/customer_support.js';
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/customer_support_detail',$data);
		$this->load->view('templates/footer');
	}	
	

	function print_customer_support( $cs_no )
	{
		
		$cs = $this->customer_model->get_single_customer_support($cs_no);

		$this->load->helper('form');
		$header_data 				= $this->vars;
		$header_data['title']		= 'Print Preview';
		$header_data['description']	= 'To print Trouble Ticket Form';
		$content_data['data']		= (empty($cs))?'':$cs;
		$data['msg'] 				= $this->msg;

		$menu_data = array();
		$menu_data['send_btn'] = 1;
		$menu_data['form_action'] = base_url('customer_support/send_customer_support_pdf/'.$cs_no);
		$menu_data['job_order_send'] = 0;

		//company name and address

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$content_data['company_addr_1'] = $_SESSION['config']['company_addr_1'];
		$content_data['company_addr_2'] = $_SESSION['config']['company_addr_2'];
		$content_data['company_addr_3'] = $_SESSION['config']['company_addr_3'];
		$content_data['company_postal'] = $_SESSION['config']['company_postal'];
		$content_data['company_city'] = $_SESSION['config']['company_city'];
		$content_data['company_phone'] = $_SESSION['config']['company_phone'];
		
		$html  = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		$html .= $this->load->view('templates/print_menu', $menu_data);
		$html .= $this->parser->parse('customer/customer_support_form', $content_data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;
		
	}

	function send_customer_support_pdf($cs_no) 
	{

		$cs = $this->customer_model->get_single_customer_support($cs_no);

		$contact_list = $this->input->post('contact_list');

		$header_data 				= $this->vars;
		$header_data['title']		= 'Print Preview';
		$header_data['description']	= 'To print Trouble Ticket Form';
		$content_data['data']		= (empty($cs))?'':$cs;

		$job_order_send = 0;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			/*$header_data = array();
			$header_data['title']		= 'Customer Support';
			$header_data['description']	= 'Job Order Form' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('customer/customer_support_form', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);
			write_file('temp/pdf/'.$cs['cs_no'].'.pdf', $pdf_data);*/

			$scode = md5($cs_no . $this->e_key);
			puppeteer_print_preview($this->config->item('base_url').'pdfapi/customer_support_form/'.$cs_no.'/'.$scode, $this->config->item('upload_path').'/temp/pdf/customer_support_form_'.$cs_no.'.pdf', $this->config->item('proj_path'), $this->config->item('chrome_loc'));

			//send pdf
			$this->load->library('whatsapp_template');
			$meta_template = $this->whatsapp_template->build('ITELCO DOCS', ['doc_type' => 'Trouble Ticket']);
			
			$smtp_user	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'from_name' ");
		
			$from_name = ( isset( $smtp_user[0]['val'] ) && $smtp_user[0]['val'] != '' ) ? $smtp_user[0]['val'] : 'no_reply@itelco.net';

			$email_list = $json_contact_list[0];
			$whatsapp_list = $json_contact_list[1];
			$telegram_list = $json_contact_list[2];

			$send_array = array();
			$send_array['send_type'] = 'custom';
			$send_array['acc_id'] = 0;
			$send_array['customer_no'] = $cs['customer_no'];
			$send_array['user_id'] = 0;
			$send_array['controller'] = 'customer';
			$send_array['doc_id'] = $cs['cs_id'];
			$send_array['send_method'] = 'manual';
			$send_array['acc_name'] = 'Itelco User';
			$send_array['attachment'] = $this->config->item('upload_path').'/temp/pdf/customer_support_form_'.$cs_no.'.pdf';
			$send_array['subject'] = 'Trouble Ticket Job Order Form';
			$send_array['body'] = 'Attached herewith is the Job Order Form for Trouble Ticket - ' . $cs['cs_no'];
			$send_array['from'] = $from_name;
			$send_array['email_starter'] = 'Trouble Ticket';
			$send_array['doc_type'] = '[Trouble Ticket Job Order Form]';
			$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
			$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];
			$send_array['email_list'] = $email_list;
			$send_array['whatsapp_list'] = $whatsapp_list;
			$send_array['telegram_list'] = $telegram_list;

			$send_result = $this->docs_log_model->do_send($send_array);

			$job_order_send = 1;
			@unlink($this->config->item('upload_path').'/temp/pdf/customer_support_form_'.$cs_no.'.pdf');
		}

		$header_data 				= $this->vars;
		$header_data['title']		= 'Print Preview';
		$header_data['description']	= 'To print Trouble Ticket Form';
		$content_data['data']		= (empty($cs))?'':$cs;
		$data['msg'] 				= $this->msg;

		$menu_data = array();
		$menu_data['send_btn'] = 1;
		$menu_data['form_action'] = base_url('customer_support/send_customer_support_pdf/'.$cs_no);
		$menu_data['job_order_send'] = $job_order_send;
		
		$html  = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		$html .= $this->load->view('templates/print_menu', $menu_data);
		$html .= $this->parser->parse('customer/customer_support_form', $content_data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;
	}

	function customer_support_service() {
		$total_row 		= 0;
		$page_item_no 	= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');

		$txt_search 	= $this->input->post('txt_search');
		$sel_cs_type 	= $this->input->post('sel_cs_type') ?: ($this->session->userdata('last_sel_cs_type')?:'cs_service');
		
		$query_where = (!empty($sel_cs_type)) ? $sel_cs_type : 'cs_service';

		$result = $this->customer_model->get_cs_service_listing($txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where);

		foreach ($result['row'] as &$value) {
			$value['cs_type'] = ($query_where == 'cs_service') ? 'cs_service' : 'cs_problem';
		}

		$data['page_title'] 		= 'Trouble Ticket Service Config';
		$data['page_desc'] 			= ($sel_cs_type == 'cs_service') ? 'TT Service' : 'TT Problem';
		$data['form_action'] 		= base_url('customer_support/customer_support_service');
		$data['row_data'] 			= $result['row'];
		$data['txt_search'] 		= $txt_search;		
		$data['sel_cs_type'] 		= $sel_cs_type;
		$data['pagination'] 		= paginationSettings('', $result['total_row']);	
		$data['msg'] 				= $this->msg;

		$this->session->set_userdata('last_sel_cs_type', $sel_cs_type);

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/customer_support_service',$data);
		$this->load->view('templates/footer');
	}

	public function add_cs_service($cs_type='',$id=0)
    {
		$chk_temp_id = $this->input->post('temp_id');

		$data['input'] 				= $this->customer_model->get_single_cs_service($cs_type,$id);
		$data['input']['created_by'] 	  = $this->user['idx'];
		$data['sel_cs_type']		= $cs_type;
		$data['page_title'] 		= (empty($id)) ? 'Add Trouble Ticket Config' : 'Edit Trouble Ticket Config';
		$data['form_action'] 		= base_url('customer_support/save_cs_service');
		$data['msg'] 				= $this->msg;

		if (!empty($chk_temp_id)) {
			$data['input']['temp_id'] = $chk_temp_id;
		}

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/customer_support_service_details',$data);
		$this->load->view('templates/footer');
	}

	public function save_cs_service()
    {
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'customer_support/customer_support_service', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && !empty($this->input->post('id'))) {
			
			$this->customer_model->delete_cs_service($this->input->post('sel_cs_type'),$this->input->post());			
			
			$this->session->set_flashdata("msg", 'CS Config Deleted!');
			$this->session->set_userdata('last_sel_cs_type', $this->input->post('sel_cs_type'));
			// redirect('customer/customer_support_service');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'customer_support/customer_support_service';
			echo json_encode($ajax_return);
			return false;
		} else {
			$this->set_form_validation('save_customer_support_service');
			if($this->form_validation->run() == false) 
			{
				$this->msg['error_msg'] = validation_errors();

				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				$this->add_cs_service();
			} else {
				$userId = $this->user['idx'];
				$result = $this->customer_model->save_cs_service($this->input->post('sel_cs_type'),$this->input->post(),$userId);

				$this->session->set_flashdata("msg", 'CS Config Saved!');
				$this->session->set_userdata('last_sel_cs_type', $this->input->post('sel_cs_type'));

				// redirect('customer/customer_support_service');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'customer_support/customer_support_service';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
	
	function customer_support_setting(){
		
		$data['page_title'] 		= 'Trouble Ticket Setting';
		$data['form_action'] 		= base_url('customer_support/save_cs_setting');
		$data['msg'] 				= $this->msg;
		//default		
		
		$configs = $this->common_model->get_table("sys_config", "*", "`category` = 'customer_supports'");
		foreach( $configs AS $config ){
			$data[$config['key']] = $config['val'];
		} //key = enable_email , enable_sms , sms_email_template

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/customer_support_setting',$data);
		$this->load->view('templates/footer');

	}

	function save_customer_support(){
		//AJAX
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'customer_support', 'error_keys' => array());
		$main_return_msg = 'Trouble Ticket added';

		if(!$_POST || (empty( $_POST['customer_no'])) ) 
		{
			//AJAX - if ajax, then no need gen_post, just display err to user
			$ajax_return['err_msg'] = 'Invalid Access';
			$ajax_return['error_keys'] = [];
			echo json_encode($ajax_return);
			return false;
        }
        $customer_no 	= $_POST['customer_no'];        
		$post_back 		= $this->input->post(NULL, TRUE);

		if (isset($_POST['btDelete']) && $this->input->post('cs_id') != '') {
			
			$result = $this->customer_model->customer_support_delete($this->input->post('cs_id'),$this->input->post('existing_attach'));			
			if($result == 1){
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'customer_support';
			}else{
				$ajax_return['err_msg'] = 'Trouble ticket not found, please refresh your page.';
			}
			echo json_encode($ajax_return);
			return false;
		}else if(isset($_POST['btCancel'])){
			$this->msg['error_msg'] = 'Invalid Access';
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'customer_support';
			echo json_encode($ajax_return);
			return false;
		}
        else if(isset($_POST['btSave'])){
			
			//continue to run below's code
			$this->set_form_validation('save_support',$this->input->post('status')); //set rules for form validation
			
			//set error message template if form_validation run false
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1">','</label>');	
			if($this->form_validation->run() == false) 
			{
				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				//fail to pass validation	
				$this->msg['error_msg'] = validation_errors();
				$this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));	
				$this->add_customer_support($this->input->post('cs_id'));
			}
			else
			{	
				$this->load->library('session');
				$user_data 	= $this->session->userdata('user');
				$username 	= $user_data['username'];

				if($post_back['status'] != 2) {
					$post_back['email_to_user_id'] = 0;
				}
				
				if( $post_back['cs_id'] == '' ) {
					$result 	= $this->customer_model->customer_support_insert ($post_back,$username);
				} else {
					$result 	= $this->customer_model->customer_support_update($post_back,$username);
					$main_return_msg = 'Trouble Ticket updated';
				}
				
				$chk_temp_id = $this->input->post('temp_id');
				if (!empty($chk_temp_id)) {
					$leftover_attach = $this->common_model->get_temp_file_attachment('support', $chk_temp_id, '', 0, 0); 
					foreach ($leftover_attach as $val) {

						$val['local_path'] = stripslashes($val['local_path']);
						$val['local_path'] = str_replace("%", "%25", $val['local_path']);

						$tmp_file_info = pathinfo($val['local_path']);

						$tmp_path = $this->config->item('upload_path').$val['local_path'];

						$file_name 		= $tmp_file_info['filename'].'.'.$tmp_file_info['extension'];
						$prefix_n_file_name = $file_name;
						$file_path 		= $this->config->item('upload_path')."/upload/" . $file_name;
						$local_path 	= "/upload/" . $file_name;
						$remark 		= $val['remark'];
						$is_print		= $val['is_print'];

						if (copy($tmp_path, $file_path)) {
							$file_info = pathinfo($local_path);
							$extension = strtolower($file_info['extension']);
							
							if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
								list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
								resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
							} else if (in_array($extension, ['pdf'])) {
								//do pdf conversion, each page to one image and save it in /res
								if (extension_loaded('imagick')){
									_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/upload/");
								}
							}
							
							$this->common_model->insert_file_attachment($file_name , $local_path, 'support', $result, $remark, $is_print, '', 0, 0, $this->user['idx']);

							//delete from temp db

							//unlink file
							$this->common_model->remove_temp_attachment($val['file_id']);
						}

					}
				}

				//handling attachments	
				if (isset($_FILES['attach_attachment'])) {
					$total = count($_FILES['attach_attachment']['name']);
					$attach_attachment_remark = $this->input->post('attach_attachment_remark');
					//$attach_attachment_print = $this->input->post('po_attachment_print');
					$attach_attachment_print = array();

					foreach ($_FILES['attach_attachment'] as $key => $val) {
						$_FILES['attach_attachment'][$key] = array_values($val);
					}

					//create initial values for checkbox that are not ticked so array_values will not screw up the ordering
					foreach ($attach_attachment_remark as $key => $val) {
						//foreach value
						if (!isset($attach_attachment_print[$key])) {
							$attach_attachment_print[$key] = 0;
						}
					}
					ksort($attach_attachment_print);

					//log_message('error', print_r($po_attachment_remark, true));
					//log_message('error', print_r($po_attachment_print, true));

					$attach_attachment_remark = array_values($attach_attachment_remark);
					$attach_attachment_print = array_values($attach_attachment_print);
					
					// Loop through each file
					for( $i=0 ; $i < $total ; $i++ ) {
						//Get the temp file path
						$tmp_path = $_FILES['attach_attachment']['tmp_name'][$i];

						//Make sure we have a file path
						if ($tmp_path != "") {
							$prefix 		= date('YmdHis');
						$file_name 		= $_FILES['attach_attachment']['name'][$i];
						$prefix_n_file_name = $prefix . "_" . $file_name;
						$file_path 		= $this->config->item('upload_path')."/upload/" . $prefix_n_file_name;
						$local_path 	= "/upload/" . $prefix_n_file_name;
						$remark 		= $attach_attachment_remark[$i];
						$is_print		= (isset($attach_attachment_print[$i])?$attach_attachment_print[$i]:0);

							if (move_uploaded_file($tmp_path, $file_path)) {
								$file_info = pathinfo($local_path);
								$extension = strtolower($file_info['extension']);
								
								if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
									list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
									resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
								} else if (in_array($extension, ['pdf'])) {
									//do pdf conversion, each page to one image and save it in /res
									if (extension_loaded('imagick')){
										_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/upload/");
									}
								}
								
								$this->common_model->insert_file_attachment($file_name , $local_path, 'support', $result, $remark, $is_print, '', '', 0, $this->user['idx']);
							}
						}
					}
				}

				if($post_back['cs_id'] == '') {
					$this->customer_model->save_job_tracking_detail( $result, 0, "Trouble Ticket Created", $this->user['idx'] );
					if(!empty($post_back['service_by'])) {
						$all_user_list = $this->common_model->get_user_list();
						$user_list = array_column($all_user_list, 'display_name', 'idx');
						$to_user = $user_list[$post_back['service_by']] ?? 'Unknown';
						$this->customer_model->save_job_tracking_detail( $result, 0, "Trouble Ticket Service By [$to_user]", $this->user['idx'] );
					}
				}else if($this->input->post('prev_status') != $this->input->post('status')) {
					if($this->input->post('status') == '3') {
						$this->customer_model->save_job_tracking_detail( $post_back['cs_id'], 0, "Trouble Ticket Closed", $this->user['idx'] );
					} else {
						$prev_status_name = $this->get_status_name($this->input->post('prev_status'));
						$status_name = $this->get_status_name($this->input->post('status'));
						$this->customer_model->save_job_tracking_detail( $post_back['cs_id'], 0, "Ticket Status Changed From [$prev_status_name] To [$status_name]", $this->user['idx'] );
					}
				}

				/**
				 * Push notification to the customer's mobile app.
				 */
				if (
					$this->input->post('cs_id') != ''
					&& $this->input->post('prev_status') != $this->input->post('status')
				) {
					try {
						$this->load->library('push_service');

						$push_status = (string) $this->input->post('status');

						if ($push_status === '3') {
							$this->push_service->notify_customer(
								$customer_no,
								'cs_closed',
								array('CS_NO' => $post_back['cs_no']),
								array('related_id' => $post_back['cs_no'])
							);
						} elseif (in_array($push_status, array('0', '1', '2'), TRUE)) {
							$this->push_service->notify_customer(
								$customer_no,
								'cs_status',
								array(
									'CS_NO'       => $post_back['cs_no'],
									'STATUS_CODE' => $push_status,
								),
								array('related_id' => $post_back['cs_no'])
							);
						}
					} catch (Exception $e) {
						log_message('error', '[push] customer support notification failed for cs_id '
							. $post_back['cs_id'] . ': ' . $e->getMessage());
					}
				}

				if ($this->input->post('prev_service_by') != $this->input->post('service_by')) {
					$all_user_list = $this->common_model->get_user_list();
					$user_list = array_column($all_user_list, 'display_name', 'idx');
					$from_user = $user_list[$this->input->post('prev_service_by')] ?? 'Unknown';
					$to_user = $user_list[$this->input->post('service_by')] ?? 'Unknown';
					$this->customer_model->save_job_tracking_detail( $post_back['cs_id'], 0, "Trouble Ticket Service By Changed From [$from_user] To [$to_user]", $this->user['idx'] );
				}
				
				$additional_msg = '';
				if(!empty($this->input->post('email_to_user_id')) && (($this->input->post('email_to_user_id') != $this->input->post('prev_email_to_user_id')) || ($this->input->post('resend_email') == 1))) {
					$all_technical_list = $this->customer_model->get_technical_user();
					$escalated_to = array_filter($all_technical_list, function($user) {
						return $user['user_id'] == $this->input->post('email_to_user_id');
					});
					$escalated_to = reset($escalated_to); // Get the first

					if(!empty($escalated_to)) {
						$email_list = [$escalated_to['email']];
						$whatsapp_list = $escalated_to['allow_whatsapp'] == 1 ? [$escalated_to['contact']] : [];
						$telegram_list = $escalated_to['allow_telegram'] == 1 ? [$escalated_to['telegram']] : [];
						$this->send_customer_support($result, $email_list, $whatsapp_list, $telegram_list);
						$additional_msg = ' and Email Sent';
					}
				}
				
				$this->session->set_flashdata("msg", $main_return_msg.$additional_msg.'!');
				
				// redirect(base_url( 'customer/customer_support_list') );
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'customer_support';
				echo json_encode($ajax_return);
				return false;
			}
		}		
	}

	function get_status_name($status) {
		if($status == 0) {
			return 'Open';
		} else if ($status == 1) {
			return 'In Progress';
		} else if ($status == 2) {
			return 'Escalated to 3rd Level';
		} else if ($status == 3) {
			return 'Closed';
		}
	}

	public function send_customer_support($cs_id, $email_list, $whatsapp_list, $telegram_list)
	{
		$this->load->library('notification_service');

		$cs = $this->customer_model->get_single_customer_support_by_id($cs_id);

		$email_template = $this->email_model->get_email_template_detail(
			"AND template_name = 'CUSTOMER SUPPORT ESCALATION' AND is_default = 1"
		);

		$service_type = $this->common_model->get_cs_service_by_id($cs['service_type']);
		$service_problem = $this->common_model->get_cs_problem_by_id($cs['service_problem']);

		$replacements = [
			'%cs_no%'            => $cs['cs_no'],
			'%report_on%'        => $cs['report_on'],
			'%onsite_on%'        => $cs['onsite_on'],
			'%service_type%'     => $service_type['cs_service_name'] ?? '',
			'%service_remark%'   => $cs['service_remark'],
			'%service_problem%'  => $service_problem['cs_problem_name'] ?? '',
			'%action_remark%'    => $cs['action_remark'],
		];

		$header = str_replace(
			'%cs_no%',
			$cs['cs_no'],
			$email_template['email_title']
		);

		$body = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$email_template['email_msg']
		);

		$contacts = $this->notification_service->buildContacts($email_list, $whatsapp_list, $telegram_list);

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			'TROUBLE TICKET ESCALATION',
			[
				'cs_no' 			=> !empty($cs['cs_no']) ? $cs['cs_no'] : '-',
				'report_on' 		=> !empty($cs['report_on']) ? $cs['report_on'] : '-',
				'onsite_on' 		=> !empty($cs['onsite_on']) ? $cs['onsite_on'] : '-',
				'service_type' 		=> !empty($service_type['cs_service_name']) ? $service_type['cs_service_name'] : '-',
				'service_remark' 	=> !empty($cs['service_remark']) ? $cs['service_remark'] : '-',
				'service_problem' 	=> !empty($service_problem['cs_problem_name']) ? $service_problem['cs_problem_name'] : '-',
				'action_remark' 	=> !empty($cs['action_remark']) ? $cs['action_remark'] : '-',
			],
			$cs['customer_no'],
			[
				'acc_id'         => 0,
				'user_id'        => $this->user['idx'],
				'controller'     => 'customer',
				'doc_id'         => $cs['cs_id'],
				'send_method'    => 'manual',
				'email_starter'  => 'Trouble Ticket Escalation [' . $cs['cs_no'] . ']',
				'doc_type'       => '[Trouble Ticket Escalation]',
			]
		);
	}
	
	public function save_cs_setting(){
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'customer_support/customer_support_setting', 'error_keys' => array());

		if( $this->input->post() ) {
			$this->set_form_validation('save_customer_support_setting');
			if($this->form_validation->run() == false) 
			{
				$this->msg['error_msg'] = validation_errors();
				// $this->session->set_flashdata("error_msg", $this->msg['error_msg']);
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else 
			{
				$post_data = $this->input->post();
				$this->customer_model->save_customer_support_settings( $post_data );
				$this->session->set_flashdata("msg", 'Setting Saved!');

				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'customer_support/customer_support_service';
				echo json_encode($ajax_return);
				return false;
			}
			
		}else{
			$ajax_return['err_msg'] = 'Failed to update setting';
			$this->msg['error_msg'] = "Failed to update setting";
			$ajax_return['status'] = 'ER';
			$ajax_return['url'] = 'customer_support/customer_support_service';
			echo json_encode($ajax_return);
			return false;
		}
		
		// redirect('customer/customer_support_setting');
		
	}

	private function gen_post_attachments($file_arr=array())
	{

		$this->load->model('common_model');

		if (!empty($file_arr)) {	

			$post_back_attach = array();

			$total = count($file_arr['name']);
			$attach_attachment_remark = $this->input->post('attach_attachment_remark');
			$attach_attachment_print = array();

			$temp_id = $this->input->post('temp_id');

			foreach ($file_arr as $key => $val) {
				$file_arr[$key] = array_values($val);
			}

			$attach_attachment_remark = array_values($attach_attachment_remark);
			//$po_attachment_print = array_values($po_attachment_print);

			// Loop through each file
			for( $i=0 ; $i < $total ; $i++ ) {
			  	//Get the temp file path
			  	$tmp_path = $file_arr['tmp_name'][$i];

			  	//Make sure we have a file path
			  	if ($tmp_path != "") {
					$prefix 		= date('YmdHis');
					$file_name 		= $file_arr['name'][$i];;
					$prefix_n_file_name = $prefix . "_" . $file_name;
					$file_path 		= $this->config->item('upload_path')."/temp/" . $prefix_n_file_name;
					$local_path 	= "/temp/" . $prefix_n_file_name;
					$remark 		= $attach_attachment_remark[$i];
					$is_print		= (isset($attach_attachment_print[$i])?$attach_attachment_print[$i]:0);

					if (move_uploaded_file($tmp_path, $file_path)) {
						$file_info = pathinfo($local_path);
						$extension = strtolower($file_info['extension']);
						
						if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
							list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
							resize_image_tmp($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
						} else if (in_array($extension, ['pdf'])) {
							//do pdf conversion, each page to one image and save it in /res
							if (extension_loaded('imagick')){
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/temp/");
							}
						}
						
						$this->common_model->insert_tmp_file_attachment($file_name , $local_path, 'support', $temp_id, $remark, $is_print, '', 0, 0, $this->user['idx']);
					}

		    	}
			}
		}

	}

	/**
	 * Delete job tracking detail
	 * @return void
	 */
	function ajax_delete_job_tracking(){
		$cs_id  = $this->input->post('cs_id');
		$rec_id = $this->input->post('rec_id');
	
		$return_val['success'] = $this->customer_model->delete_job_tracking_detail($cs_id, $rec_id);
		echo json_encode($return_val);
	}
}

