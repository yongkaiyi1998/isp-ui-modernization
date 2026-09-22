<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Email extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload','email'));
		$this->load->model('email_model');
		$this->load->model('common_model');

		$this->account_status = array('P' => 'Signup', 'A' => 'Activated', 'S' => 'Suspended', 'T' => 'Terminated', 'C' => 'Cancelled');

    }

	function index()
    {
		redirect('email/mailing_list');
	}

	public function mailing_list()
    {
		check_acl('email');
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'sel_category' => 'all',
			'sel_status' => 'all'
		];
		$return = get_filtered_ajax_data('mailing_list_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->mailing_list_rows(1);
		
		/*
		
		if($sel_month == 'this_month')
		{
			$month_s = date("Y-m-d", strtotime("first day of this month"));
			$month_e = date("Y-m-d", strtotime("last day of this month"));
			$query_where .= " AND last_email_sent >= '$month_s' AND last_email_sent <= '$month_e'";
		}
		if($sel_month == 'last_month')
		{
			$month_s = date("Y-m-d", strtotime("first day of previous month"));
			$month_e = date("Y-m-d", strtotime("last day of previous month"));
			$query_where .= " AND last_email_sent >= '$month_s' AND last_email_sent <= '$month_e' ";
		}

		if($sel_month == 'null')
		{
			$query_where .= " AND last_email_sent is null ";
		}
		
		if($sel_month == 'empty')
		{
			$query_where .= " AND flag_email = '2' ";
		}

		if($sel_month == 'invalid')
		{
			$query_where .= " AND flag_email = '1' ";
		}
		*/
		
		$sel_month_list = array(
							'0' => array( 'month_code' => 'null', 		'name' => 'Have Not Being Sent' ),
							'1' => array( 'month_code' => 'last_month', 'name' => 'Sent Last Month' ),
							'2' => array( 'month_code' => 'this_month', 'name' => 'Sent This Month' ),
							'3' => array( 'month_code' => 'invalid', 	'name' => 'That is invalid' ),
							'4' => array( 'month_code' => 'empty', 		'name' => 'That is blank' ),
						  );
		
		$panel['panel_title'] 			= 'Mailing List';
		$data['form_action'] 			= base_url('email/mailing_list');

		$data['sel_month_list'] 		= $sel_month_list;
		$data['sel_status_list']		= $this->common_model->get_customer_status_list();
		$data['sel_category_list'] 		= $this->common_model->get_category_list();
		
		//$data['attachment_folder'] 	= $this->email_model->set_attachment_at();
		$data['attachment_folder'] = $this->config->item('upload_url')."/temp/pdf";
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->load->view('templates/panel_header',$panel);
		$this->parser->parse('maintenance/mailing_list',$data);
		$this->load->view('templates/panel_footer');
		$this->load->view('templates/footer');
	}

	public function mailing_list_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'sel_category' => 'all',
			'sel_status' => 'all'
		];
		$return = get_filtered_ajax_data('mailing_list_filter', $default_data, $post_data);

		$data = $return['data'];

		$query_where = '';
		if ($data['sel_category'] != 'all' && !empty($data['sel_category'])) {
			$query_where .= " AND c.category = '" . $data['sel_category'] . "' ";
		}

		if ($data['sel_status'] != 'all' && !empty($data['sel_status'])) {
			$query_where .= " AND cs.status = '" . $data['sel_status'] . "' ";
		}

		if (empty($data['page_item_no'])) $data['page_item_no'] = 0;

		$result 	= $this->email_model->get_customer_email_status($query_where,$data['txt_search'],$data['page_item_no']);
		$row 		= $result['row'];
		$total_row	= $result['total_row'] ?? 0;

		foreach ($row as $key => $val){
			$row[$key]['last_email_sent'] = date_toggle($row[$key]['last_email_sent'],$_SESSION['config']['date_format']);
		}

		$data['row_data'] = $row;
		$data['pagination'] = paginationSettingsAjax('', $total_row, $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['attachment_folder'] = $this->config->item('upload_url')."/temp/pdf";

		$html = $this->parser->parse('maintenance/mailing_list_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}
	
	function error(){
		echo flash_data_helper($this->msg);
	}

	//to generate LAST BILL
	function generate_bill_statement($customer_no = '')
	{
		$timestamp		= time();
		$date_stamp 	= date("Y-m-d_h:i:sa", $timestamp);
		$date 			= date("MY", $timestamp);
		$filename 		= '['.$row['bill_no'].']'.'bill_'.$customer_no;
		
		$this->load->model('bill_model');
		$row = $this->bill_model->get_last_bill( $customer_no ) ;
		$genPDF = $this->bill_model->generate_bill_pdf( 'bill', $row['bill_no'], 1 , $filename );
		
		if($genPDF == 1)
		{
			$attachments = $filename.'.pdf';
			$this->email_model->customer_update_last_file_gen($attachments,$customer_no);
			$this->msg['msg']='pdf successful generated';
			redirect('email/mailing_list');
		}
		else
		{
			$this->msg['msg']='pdf not able to generate';
			redirect('email/mailing_list');
		}
	}

	function email_suspended_account($customer_no = '')
	{
		$this->load->model(['email_model']);

		$escaped_customer_no = $this->db->escape_str($customer_no);
		$customer = $this->common_model->get_table('customer', 'profile_id,name', "`customer_no` = '$escaped_customer_no'");
		$customer_name = (empty($customer[0]['name'])) ? 'Valued Customer' : $customer[0]['name'];
		$profile_id = (empty($customer[0]['profile_id'])) ? '' : $customer[0]['profile_id'];

		$escaped_profile_id = $this->db->escape_str($profile_id);
		$profile = $this->common_model->get_table('profile', 'pic_email_1,pic_email_2', "`acc_id` = '$escaped_profile_id'");
		$email_1 = (empty($profile[0]['pic_email_1'])) ? '' : $profile[0]['pic_email_1'];
		$email_2 = (empty($profile[0]['pic_email_2'])) ? '' : $profile[0]['pic_email_2'];
		
		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name','company_full_name','company_phone','company_email')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$isp_name      = $cfg['isp_name'] ?? '';
		$company_name  = $cfg['company_full_name'] ?? '';
		$company_phone = $cfg['company_phone'] ?? '';
		$company_email = $cfg['company_email'] ?? '';

		$emailInfo = $this->email_model->get_email_template_detail(" AND template_name = 'SUSPENSION REMINDER'");

		if (empty($emailInfo)) {
			$this->session->set_flashdata("error_msg", 'Email unable to sent! Suspension reminder email template not found.');
			redirect('email/mailing_list');
			return;
		}

		$suspension_title   = 'Immediate payment is required to avoid account suspension.';
		$suspension_content = '<p>This is a final reminder that your account has an outstanding balance that remains unpaid, and <strong>immediate payment is required</strong> to prevent disruption of your internet service.<br> If payment is not received promptly, your account may be suspended without further notice.</p> <p>To avoid any inconvenience, kindly arrange for payment as soon as possible.<br> If you have already made the payment, please disregard this notice.</p>';

		$replacements = [
			'%SUBSCRIBER_NO%' => $customer_no,
			'%CUSTOMER_NAME%' => $customer_name,
			'%ISP_NAME%' => $isp_name,
			'%COMPANY_NAME%' => $company_name,
			'%COMPANY_PHONE%' => $company_phone,
			'%COMPANY_EMAIL%' => $company_email,
			'%SUSPENSION_REMINDER_CONTENT%' => $suspension_content
		];

		$email_msg = str_replace(array_keys($replacements), array_values($replacements), $emailInfo['email_msg']);
		$email_title = str_replace('%SUSPENSION_REMINDER_TITLE%', $suspension_title, $emailInfo['email_title']);

		$recipients = array_filter([$email_1, $email_2]);
		
		if (!empty($recipients)) {
			$recipient_email = implode(',', $recipients);

			$this->email_model->add_email_schedule([
				'recipient_emails'  => $recipient_email,
				'scheduler_id'      => '',
				'send_by'           => '',
				'email_title'       => $email_title,
				'email_msg'         => $email_msg,
				'email_cust_status' => 1,
				'email_attachment'  => '',
				'email_schedule_on' => date("Y-m-d H:i:s", strtotime("+30 minutes"))
			]);

			$this->session->set_flashdata("msg", 'Email has been scheduled to be sent out in 30 minutes, you may check your email progress in progress report!');
			redirect('email/mailing_list');
			return;
		}

		$this->session->set_flashdata("error_msg", 'email unable to sent!');
		redirect('email/mailing_list');
		return;
	}

	function email_reactivate_account($customer_no = '')
	{
		$this->load->model(['email_model']);

		$escaped_customer_no = $this->db->escape_str($customer_no);
		$customer = $this->common_model->get_table('customer', 'profile_id,name', "`customer_no` = '$escaped_customer_no'");
		$customer_name = (empty($customer[0]['name'])) ? 'Valued Customer' : $customer[0]['name'];
		$profile_id = (empty($customer[0]['profile_id'])) ? '' : $customer[0]['profile_id'];

		$escaped_profile_id = $this->db->escape_str($profile_id);
		$profile = $this->common_model->get_table('profile', 'pic_email_1,pic_email_2', "`acc_id` = '$escaped_profile_id'");
		$email_1 = (empty($profile[0]['pic_email_1'])) ? '' : $profile[0]['pic_email_1'];
		$email_2 = (empty($profile[0]['pic_email_2'])) ? '' : $profile[0]['pic_email_2'];
		
		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name','company_full_name','company_phone','company_email')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$isp_name      = $cfg['isp_name'] ?? '';
		$company_name  = $cfg['company_full_name'] ?? '';
		$company_phone = $cfg['company_phone'] ?? '';
		$company_email = $cfg['company_email'] ?? '';

		$emailInfo = $this->email_model->get_email_template_detail(" AND template_name = 'REACTIVATION EMAIL'");

		if (empty($emailInfo)) {
			$this->session->set_flashdata("error_msg", 'Email unable to sent! Reactivation email template not found.');
			redirect('email/mailing_list');
			return;
		}

		$replacements = [
			'%SUBSCRIBER_NO%' => $customer_no,
			'%CUSTOMER_NAME%' => $customer_name,
			'%ISP_NAME%' => $isp_name,
			'%COMPANY_NAME%' => $company_name,
			'%COMPANY_PHONE%' => $company_phone,
			'%COMPANY_EMAIL%' => $company_email
		];

		$email_msg = str_replace(array_keys($replacements), array_values($replacements), $emailInfo['email_msg']);
		$email_title = str_replace('%ISP_NAME%', $isp_name, $emailInfo['email_title']);

		$recipients = array_filter([$email_1, $email_2]);
		
		if (!empty($recipients)) {
			$recipient_email = implode(',', $recipients);

			$this->email_model->add_email_schedule([
				'recipient_emails'  => $recipient_email,
				'scheduler_id'      => '',
				'send_by'           => '',
				'email_title'       => $email_title,
				'email_msg'         => $email_msg,
				'email_cust_status' => 1,
				'email_attachment'  => '',
				'email_schedule_on' => date("Y-m-d H:i:s", strtotime("+30 minutes"))
			]);

			$this->session->set_flashdata("msg", 'Email has been scheduled to be sent out in 30 minutes, you may check your email progress in progress report!');
			redirect('email/mailing_list');
			return;
		}

		$this->session->set_flashdata("error_msg", 'email unable to sent!');
		redirect('email/mailing_list');
		return;
	}

	function email_terminated_account($customer_no = '')
	{
		$where_query	= "`customer_no` = '$customer_no'";
		$customer		= $this->common_model->get_table('customer','email_1,name', $where_query);
		$email	 		= (empty($customer[0]['email_1']))?'':$customer[0]['email_1'];
		$customer_name	= (empty($customer[0]['name']))?'Valued Customer':$customer[0]['name'];

		if(!empty($email))
		{
			$subject		= 'penangFon | Account has been Terminated | '.$customer_no;
			$body 			= 'Dear '.$customer_name.' ,<br><br>';
			$body 			.= 'The account that you had subscribed with Penangfon has been terminated.<br>';
			$body 			.= 'Further enquiries, please call us at : <br>04-227 4521 / 013-428 8680 or <br>email us at : <br>support@fiberhome.net.<br><br><br><br>';
			$body 			.= 'Thanks and Regards,<br>';
			$body 			.= 'PenangFon';

			$email_result = $this->email_model->generate_email($subject, $body, '', $email);

			if($email_result == '1')
			{
				$this->session->set_flashdata("msg", 'email sent!');
				redirect('email/mailing_list');
			}
			else
			{
				$this->session->set_flashdata("error_msg", 'email unable to sent!');
				redirect('email/mailing_list');
			}
		}
	}

	function email_bill_statement($customer_no = '')
	{
		$this->load->model('customer_model');
		$where_query	= "`customer_no` = '$customer_no'";
		//$customer		= $this->common_model->get_table('customer','name,email_1,last_file_gen,mobile_num,', $where_query);
		$customer = $this->customer_model->get_customer($customer_no);
		//print_r($customer); exit;
		$email	 		= (empty($customer['pic_email_1']))?'':$customer['pic_email_1'];
		$attachment 	= (empty($customer['last_file_gen']))?'':$customer['last_file_gen'];
		$timestamp		= time();
		$date 			= date("Y-m-d", $timestamp);

		if(empty($email))
		{
			$this->email_model->customer_update_flag_email($customer_no,'2');
			$this->session->set_flashdata("error_msg", 'email is not defined.');
			redirect('email/mailing_list');
		}
		else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->email_model->customer_update_flag_email($customer_no,'1');
			$this->session->set_flashdata("warning_msg", 'email is invalid.');
			redirect('email/mailing_list');
		}
		else
		{
			$this->email_model->customer_update_flag_email($customer_no,'0');

			if(empty($attachment))
			{
				$this->session->set_flashdata("warning_msg", 'Attachment is needed to send bill statement.');
				redirect('email/mailing_list');
			}
			else
			{
				
				//get email subject & body from email template
				$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'AUTO BILLING EMAIL' AND is_default = 1 " );
				$subject = $emailInfo['email_title'];
				$body    = $emailInfo['email_msg'];
				
				$this->load->model('bill_model');
				$bill = $this->bill_model->get_last_bill( $customer_no );
				
				$subject = str_replace( "%MONTH%", date("M") , $subject );
				$subject = str_replace( "%SUBSCRIBER_NO%", $customer_no , $subject );
				$subject = str_replace( "%CUSTOMER_NAME%", $customer['acc_name'] , $subject );
				$post_data['email_title']	= $subject ;
				
				$body = str_replace( "%THIS_MONTH%", date('M Y'), $body );
				$body = str_replace( "%SUBSCRIBER_NO%", $customer_no, $body );
				$body = str_replace( "%CUSTOMER_NAME%", $customer['acc_name'], $body );
				$body = str_replace( "%BILL_DATE%", date("Y-m-d", strtotime($bill['bill_date']) ), $body );
				$body = str_replace( "%DUE_DATE%" , date("Y-m-d", strtotime($bill['bill_date']) ), $body );
				$body = str_replace( "%BALANCE%"  , number_format( $bill['balance'], 2 , '.' , ',' ), $body );
				$post_data['email_msg']	= $body ;
				
				//$this->load->model('email_model');
				//$email_result 	= $this->email_model->generate_email($subject, $body, $attachment, $email);
				
				$post_data['email_cust_status'] = 1;
				$post_data['email_attachment']	= $attachment ;
				$post_data['email_schedule_on'] = date("Y-m-d H:i:s") ;
				$post_data['recipient_emails'] = $customer['pic_email_1'];
				$this->email_model->add_email_schedule( $post_data );
				
				//~ if($email_result == '1')
				//~ {

					$this->email_model->customer_update_last_email_sent($customer_no,$date);

					if( $customer[0]['mobile_num'] != '' ){
						
						/*$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
						$comp_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
						
						$this->load->model('sms_scheduler_model');
						
						$smsInfo = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Auto Billing SMS' AND is_default = 1 " );
						$default_sms_title = $smsInfo['sms_title'];
						$default_sms_msg = $smsInfo['sms_msg'];
						$default_sms_msg = str_replace( "%COMP_NAME%", $comp_name, $default_sms_msg ) ; 
						$default_sms_msg = str_replace( "%THIS_MONTH%", date('M Y'), $default_sms_msg );
						
						//$sms_msg = 	"Your e-invoice for ". date("M Y") ." has been email to you. Please access your email to view it.Call us at 04-6838888 (Press 2) or email to icare@penangfon.net for any query.\nRegards from ".$comp_name;
						$sms_msg = $default_sms_msg;
						
						$post_data['sms_scheduler'] = 	array(	'sms_to' => $customer[0]['mobile_num'],
																'sms_schedule_on' => date("Y-m-d 12:00:00") , 
																'sms_title'	=> 'Auto Billing SMS Notification for ' . $customer_no . ' #' . $bill['bill_no'] ,
																'sms_msg'	=> $sms_msg,
																'is_building'	=> '0',
																'sms_building'	=> '',
																'sms_cust_cat'	=> '',
																'sms_cust_status'	=> '',
																'created_date'	=> date("Y-m-d h:i:s"),
																'created_by'	=> 0,
																'modified_date'	=> date("Y-m-d h:i:s"),
																'modified_by'	=> 0,
														);
						$this->sms_scheduler_model->sms_add( $post_data['sms_scheduler'] );*/
						
					}

					$this->session->set_flashdata("msg", 'Email has been scheduled to be sent out immediately, you may check your email progress in progress report!');
					redirect('email/mailing_list');
				//~ }
				/*
				else
				{
					
					$this->session->set_flashdata("error_msg", 'email unable to sent!');
					redirect('email/mailing_list');
				}
				*/
			}
		}
	}
	
    public function schedule_list()
    {
		$data= array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => '',
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('schedule_list_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->schedule_list_rows(1);

		$data['page_title'] 	= 'Email Scheduler';
		$data['form_action'] 	= base_url('email/schedule_list');
		$data['msg'] 			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('email/email_scheduler',$data);
		$this->load->view('templates/footer');
	}

	public function schedule_list_rows($returnOnly = 0)
	{
		$total_row = 0;
		$data= array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('schedule_list_filter', $default_data, $post_data);

		$data = $return['data'];

		if (empty($data['page_item_no'])) $data['page_item_no'] = 0;

		$result = $this->email_model->get_scheduled_email_list($data['txt_search'],'','',$data['page_item_no']);

		foreach($result['row'] as $r_key => $r_val){
			$result['row'][$r_key]['email_schedule_on'] = datetime_toggle($result['row'][$r_key]['email_schedule_on'],$_SESSION['config']['datetime_format']);
			$result['row'][$r_key]['modified_date'] = datetime_toggle($result['row'][$r_key]['modified_date'],$_SESSION['config']['datetime_format']);
		}

		$data['row_data'] = $result['row'];
		$data['pagination'] = paginationSettingsAjax('', $result['total_row'],$data['page_item_no'], $_SESSION['config']['max_page_item']);

		$html = $this->parser->parse('email/email_scheduler_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}
	
	public function add_email($scheduler_id = '')
    {
		
		$input_data 	= $this->email_model->get_email_scheduler_detail($scheduler_id);
		$building_opt 	= $this->common_model->get_building_list();
		$cust_cat	 	= $this->common_model->get_category_list();
		$area_opt	 	= $this->common_model->get_area_list();
		// $cust_status	= $this->common_model->get_acc_status_list();
		// $cust_status	= array_column($cust_status, 'name', 'status_code');

		$cust_status = $this->account_status;

		if(empty($input_data))
		{
			$this->session->set_flashdata("warning_msg", 'Invalid Access.');
			redirect('email_scheduler');
		}

		$email_building = explode(',', $input_data['email_building']);
		$input_data['email_building'] = $email_building;

		$email_area = explode(',', $input_data['email_area']);
		$input_data['email_area'] = $email_area;

		$options = array();
		foreach ($building_opt as $bo_key => $bo_val){
			$options[$building_opt[$bo_key]['building_no']] = $building_opt[$bo_key]['name'];
		}

		$areas = array();
		foreach ($area_opt as $ao_key => $ao_val){
			$areas[$area_opt[$ao_key]['id']] = $area_opt[$ao_key]['name'];
		}
		
		$data['input'] 				  = $input_data ;
		$data['input']['is_building'] = $input_data['is_building'] != '' ? $input_data['is_building'] : 1 ;
		
		if( $data['input']['is_building'] == '1' || $data['input']['is_area'] == '1' || $data['input']['is_all'] == '1'){ //hide email_to if is_building = 1
			$data['input']['email_to'] = '' ;
		}

		if ($data['input']['is_building'] == 1) {
			$data['by'] = 'building';
		} else if ($data['input']['is_individual'] == 1) {
			$data['by'] = 'individual';
		} else if ($data['input']['is_area'] == 1) {
			$data['by'] = 'area';
		} else if ($data['input']['is_all'] == 1) {
			$data['by'] = 'all';
		}

		if(empty($input_data['scheduler_id'])) {
			$is_edit = 0;
		} else {
			$is_edit = 1;
		}
		
		$data['disabled_input']		= '0';
		
		if(!empty($is_edit)){
 			//~ $minit_from_now			= ((time())-(60)); //(time()-(60*60*24)) // a day from now
 			// $now					= time(); //(time()-(60*60*24)) // a day from now
			// $data['disabled_input']	= ($now < strtotime($input_data['email_schedule_on']))?'0':'1';
			$data['disabled_input']	= ($input_data['email_status'] == 'P' && empty($input_data['email_attempt']))?'0':'1';
		}
		
		$data['building_opt'] 		= $options;
		$data['area_opt'] 			= $areas;
		$data['cust_cat'] 			= $cust_cat;
		$data['cust_status'] 		= $cust_status;
		$data['template_list'] 		= $this->email_model->get_email_template_list();
		$data['page_title'] 		= (empty($is_edit))?'Add Email':'Edit Email';
		$data['is_edit'] 			= (empty($is_edit))?'0':'1';
		$data['form_action'] 		= base_url('email/save_email_schedule');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('email/email_detail',$data);
		$this->load->view('templates/footer');
	}
	

	public function save_email_schedule()
    {
		if(!$_POST) redirect('email_scheduler');

		if ((!empty($_POST['btDelete'])) &&  (!empty($_POST['scheduler_id'])))
		{
			$result 	= '0';
			$delete_id 	= $_POST['scheduler_id'];
			$now 		= time();	

			if($now < strtotime($_POST['email_schedule_on']))
			{
				$result = $this->email_model->delete_email_scheduler($delete_id);
			} else {
				$this->session->set_flashdata("warning_msg", 'Cannot delete emails that are scheduled on earlier date.');
				redirect('email/add_email/'.$delete_id);
			}

			if(!empty($result))
			{
				if(!empty($result)) $this->session->set_flashdata('msg','Email schedule is deleted!');
				redirect('email/schedule_list');
			}
			else
			{
				$this->session->set_flashdata("warning_msg", 'Invalid action.');
				redirect('email/add_email/'.$delete_id);
			}
		}
		else
		{
			//_debug_array($_POST); _debug_array($_FILES); exit;
			$this->set_form_validation('save_email', 1);
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1">','</label><br>');

			if($this->form_validation->run() == false)
			{
				$this->msg['error_msg'] 	= validation_errors();
				if(empty($_POST['scheduler_id']))
					$this->add_email();
				else
					$this->add_email($_POST['scheduler_id']);
			}else{
				$post_data = $this->input->post();
				$post_data['email_attachment'] = '';
				$post_data['email_cust_cat'] = '';
				$post_data['email_cust_status'] = '';
				
				$file = isset( $_FILES['email_attachment'] ) ? $_FILES['email_attachment'] : array("name" => "");
				if( $file['name'] != '' ) {

					$file_name = substr($_FILES['email_attachment']['name'],0,strrpos( $_FILES['email_attachment']['name'],"."))."_".date('Ymd');
											
					$this->load->library('upload');
					//$upload['upload_path']          = './temp/email_scheduler/';
					$upload['upload_path'] = $this->config->item('upload_path').'/temp/pdf/';
					$upload['allowed_types']        = 'txt|xls|xlsx|csv|pdf|jpg|jpeg|png';
					$upload['max_size']				= '2048';//2mb only
					$upload['file_name']			= $file_name;
					$this->upload->initialize($upload);	
					
					if( !$this->upload->do_upload('email_attachment') ){
						$upload_msg  = "Failed to upload file.";
						$upload_msg .= $this->upload->display_errors('<p>', '</p>');
						$this->session->set_flashdata("error_msg", $upload_msg);
						//redirect('email/add_email');

						$this->add_email($post_data['scheduler_id']);
						return false;
					}else{
						$file = $this->upload->data();
						$file_name = $file['file_name'];
						$post_data['email_attachment'] = $file_name;
					}
					
				} else {
					//no file attached, but might need to auto attach relevant document if a template is chosen
					if(!empty($post_data['template_list'])) {
						$email_template = $this->email_model->get_email_template_detail("AND e.template_id = ". $post_data['template_list'] . " AND is_default = 1");
						if ($email_template['template_name'] == 'AUTO BILLING EMAIL') {
							$post_data['auto_attach_file'] = true;
						}
					}

					//if edit, do not remove attachment if have attachment
					if (!empty($post_data['scheduler_id'])) {
						$scheduler_info = $this->email_model->get_email_scheduler_detail($post_data['scheduler_id']);
						$post_data['email_attachment'] = $scheduler_info['email_attachment'];
					}
				}
				
				$return_data = $this->email_model->add_email_schedule( $post_data );
				$additional_msg = '';
				if(!empty($return_data['failed_customer'])) {
					$additional_msg .= 'Bills of Account [';
					$additional_msg .= implode(',',$return_data['failed_customer']);
					$additional_msg .= '] failed to be sent';
				}
				$this->session->set_flashdata("msg", 'Email has been scheduled.' . $additional_msg);
				redirect('email/schedule_list');

				//autocomplete to populate previous sms
			}
		}
	}
	
	private function set_form_validation($mode = 'save_email', $auto_set_rules = '1')
	{
		//add/edit tag
		$config['save_email'][] = array('field' => 'email_title'		, 'label' => 'Title ' 				,'rules' => 'trim|required');
		$config['save_email'][] = array('field' => 'email_schedule_on'	, 'label' => 'Schedule On ' 		,'rules' => 'trim|required|callback_validate_schedule_on');
		$config['save_email'][] = array('field' => 'email_msg'			, 'label' => 'Email Message '	    ,'rules' => 'trim|required');
		$config['save_email'][] = array('field' => 'email_cust_cat'		, 'label' => 'Customer category'    ,'rules' => 'trim');
		$config['save_email'][] = array('field' => 'email_cust_status'	, 'label' => 'Customer status' 		,'rules' => 'trim');
		$config['save_email'][] = array('field' => 'email_building[]'	, 'label' => 'Building selections'	,'rules' => 'trim');
		$config['save_email'][] = array('field' => 'email_area[]'		, 'label' => 'Area selections'		,'rules' => 'trim');

		$config['save_email'][] = array('field' => 'send_by', 'label' => 'send_by', 'rules' => 'trim');
		$config['save_email'][] = array('field' => 'recipient_emails', 'label' => 'recipient_emails', 'rules' => 'trim');
		
		// need if u need to set rules outside this function, set $auto_set_rules = 0
		if($auto_set_rules == '1') $this->form_validation->set_rules($config[$mode]);
		else return $config[$mode];
	}

	public function validate_schedule_on()
    {
		$error_msg	= ucwords('schedule must be greater than current date and time');
		$now 		= time();

		$return_val = false;
		if(($_POST && (!empty($_POST['email_schedule_on']))) && ($now < strtotime($_POST['email_schedule_on'])))
			$return_val = true;

		if($return_val == false)
			$this->form_validation->set_message('validate_schedule_on', $error_msg);

		return $return_val;
	}
	
	public function ajax_get_customer_email()
	{
		$return_val = array();
		
		$this->load->model('customer_model');
		$result = $this->customer_model->get_customer_listing( $this->input->get('customer_no') , 0, '');
		$return_val = $result ; 
		$return_val['customer_no'] = $this->input->get('customer_no');
		
		echo json_encode( $return_val );
	}

	function ajax_parse_email_content() {
		$email_title = $this->input->post('email_title');
		$email_msg = $this->input->post('email_msg');
		$customer = json_decode($this->input->post('customer'), true);

		$this->load->model('common_model');
		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN (
				'isp_name',
				'company_full_name',
				'company_phone',
				'company_email'
			)"
		);
		$data = array_merge($customer ?? [], array_column($config_data, 'val', 'key'));

		$this->load->helper('email_helper');

		$parsed_email_content = '';
		if(!empty($customer)) {
			$parsed_email_content = parse_email_template($email_title, $email_msg, $data);
		}

		echo json_encode($parsed_email_content);
	}
	
	function email_report(){
		check_acl('email');
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'date_from' => date("Y-m-d"),
			'date_to' => date('Y-m-d'),
			'email_title' => '',
			'scheduler_id' => ''
		];
		$return = get_filtered_ajax_data('email_report_filter', $default_data, $post_data);
		$data = $return['data'];		
		
		$data['page_title'] = 'Email Report';
		$data['form_action']= base_url('email/email_report');
		$data['msg'] 		= $this->msg;
		$row_html = $this->email_report_rows(1);
		$data['row_html'] = $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('email/email_report',$data);
		$this->load->view('templates/footer');
		
	}

	function email_report_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'date_from' => date("Y-m-d"),
			'date_to' => date('Y-m-d'),
			'email_title' => '',
			'scheduler_id' => ''
		];
		$return = get_filtered_ajax_data('email_report_filter', $default_data, $post_data);
		$data = $return['data'];

		$reports = $this->email_model->get_email_report( $data['date_from'], $data['date_to'], $data['scheduler_id'] );

		$stat['P'] = 0 ;
		$stat['S'] = 0 ;
		$stat['F'] = 0 ;
		$this->load->model('customer_model');
		foreach( $reports AS $key => $row ){			
			//get statistic
			if( $row['email_status'] != '' )
				$stat[ $row['email_status'] ]++;
		}
		$data['reports']	= $reports;
		$data['statistic'] 	= $stat;
		$data['page_title'] = 'Email Report';

		$html = $this->parser->parse('email/email_report_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}		
	}
	
	public function autocomplete_load_email_title(){
		
		$return_val = array();
		
		$result = $this->email_model->get_scheduled_email_list(	$this->input->post('keyword'),
																$this->input->post('date_from'),
																$this->input->post('date_to'),
																0, 1000 );

		$return_val = $result['row'];
		
		echo json_encode( $return_val );		
		
	}
	
	public function email_template( $template_id = '' ){
		
		$input_data 				= $this->email_model->get_email_template_detail( " AND template_id = '".$template_id."' " );
		
		$data['template_list']		= $this->email_model->get_email_template_list();
		
		$data['input'] 				= $input_data ;
		$data['page_title'] 		= ( $template_id == '' )?'Add Template':'Edit Template';
		$data['form_action'] 		= base_url('email/save_email_template');
		$data['msg'] 				= $this->msg;
		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('email/email_template',$data);
		$this->load->view('templates/footer');
		
	}
	
	public function ajax_get_email_template(){
		$return_val = array();
		
		$template_id = $this->input->post('template_id');
		
		$template_details = $this->email_model->get_email_template_detail( " AND template_id = '".$template_id."' " );
		
		$return_val = $template_details;
		
		echo json_encode( $return_val );
	}
	
	public function save_email_template(){
		
		if(!$_POST) redirect('email_template');

		if ((!empty($_POST['btDelete'])) &&  (!empty($_POST['template_id'])))
		{
			$result 	= '0';
			$delete_id 	= $_POST['template_id'];
			$result = $this->email_model->delete_email_template($delete_id);
			
			if(!empty($result))
			{
				if(!empty($result)) $this->session->set_flashdata('msg','Email template has been deleted!');
				redirect('email/email_template');
			}else{
				$this->session->set_flashdata("warning_msg", 'Invalid action.');
				redirect('email/email_template/'.$delete_id);
			}
		}else{
			
			//add or edit
			$this->email_model->add_email_template( $this->input->post() );
			$this->session->set_flashdata("msg", 'Template has been saved.');
			redirect('email/email_template');
			
		}
		
	}
	
	public function remove_attachment( $scheduler_id ){
		if( $scheduler_id != '' ){
			
			$email = $this->email_model->get_email_scheduler_detail($scheduler_id);
			
			if( file_exists( "temp/email_scheduler/" . $email['email_attachment'] ) ){
				UNLINK( "temp/email_scheduler/" . $email['email_attachment'] ) ; 
				$this->email_model->remove_email_attachment( $scheduler_id );
			}
			
			redirect('email/add_email/' . $scheduler_id) ;
			
		}
	}
		
}
