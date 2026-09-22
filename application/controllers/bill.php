<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Bill extends DataPage_Controller {
	protected $temp_form_data = [];

	private $e_key = "1nf0n4l";

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload','zip'));
		check_acl('bill');
		$this->load->model('bill_model');
		$this->load->model('common_model');
		$this->load->model('docs_log_model');

		$this->load->helper('puppeteer_helper');

		$this->einvoice_status = array('W' => 'Unsubmitted', 'P' => 'Submitted', 'F' => 'Failed/Invalid', 'S' => 'Valid/Submitted', 'C' => 'Cancelled');

		$this->account_status = array('P' => 'Signup', 'A' => 'Activated', 'S' => 'Suspended', 'T' => 'Terminated', 'C' => 'Cancelled');
    }

    function bill_generate()
    {
		//DELETE FROM  `itelco`.`bill` WHERE  `bill`.`bill_date` =  '2016-11-1'
        $this->load->model('bill_model');
        $cust_bill = $this->bill_model->bill_generate();

		//~ $error_array = array();
		//~
		//~
		//~ if(empty($cust_bill))
		//~ {
			//~ $error_array[] = 'No customer to email.';
		//~ }
		//~ else
		//~ {
			//~ foreach ($cust_bill as $customer_no => $row)
			//~ {
				//~ $attachments 	= '';
				//~ $error_msg		= array();
								//~
				//~ if(empty($cust_bill[$customer_no]['email_1']))
				//~ {
					//~ $query_str = "UPDATE customer SET `flag_email` = '2' WHERE customer_no = '" . $customer_no . "' ";
					//~ $this->db->query($query_str);
					//~ $error_msg[] = 'email is not defined :'.$customer_no;
				//~ }
				//~ else if(!filter_var($cust_bill[$customer_no]['email_1'], FILTER_VALIDATE_EMAIL))
				//~ {
					//~ $query_str = "UPDATE customer SET `flag_email` = '1' WHERE customer_no = '" . $customer_no . "' ";
					//~ $this->db->query($query_str);
					//~ $error_msg[] = 'email is invalid :'.$customer_no;
				//~ }
				//~ elseif($cust_bill[$customer_no]['bill_by_email'] != 1)
				//~ {
					//~ $error_msg[] = 'bill by email is not checked:'.$customer_no;
				//~ }
				//~ else
				//~ {
					//~ $query_str = "UPDATE customer SET `flag_email` = '0' WHERE customer_no = '" . $customer_no . "' ";
					//~ $this->db->query($query_str);
					//~
					//~ $timestamp		= time();
					//~ $date 			= date("Y-m-d", $timestamp);
					//~ $datetime 		= date("Y-m-d_h:i:sa", $timestamp);
					//~ $filename 		= '['.$date.']'.'bill_statement_'.$customer_no.'_'.$datetime;
									//~
					//~ $generate_pdf 	= $this->bill_statement('latest_bill_by',$customer_no,1,$filename);
										//~
					//~ if($generate_pdf != 1)
					//~ {
						//~ $error_msg[] = 'fail to generated pdf :'.$customer_no;
					//~ }
					//~ else
					//~ {
						//~ $attachments	= $filename.'.pdf';
						//~ $query_str		= "UPDATE customer SET last_file_gen = '".$attachments."' WHERE customer_no = '" . $customer_no . "' ";
						//~ $this->db->query($query_str);
					//~ }
					//~
					//~ $subject		= 'penangFon Bill Statement';
					//~ $email_from 	= 'penangFon';
					//~ $pic_name 		= (empty($cust_bill[$customer_no]['pic_name']))?'User':$cust_bill[$customer_no]['pic_name'];
					//~ $message		= 'Dear '.$pic_name.', <br> The following attachment are the Bill Statement for '.$date.'.<br><br> Regards,<br>Penangfon.';
					//~ $email_to		= $cust_bill[$customer_no]['email_1'];
					//~ $email_subject 	= $subject;
					//~ if(!empty($row['charge_detail'][0]['remark']))
					//~ {
						//~ $email_subject .= ' | '.$row['charge_detail'][0]['remark'];
					//~ }
					//~
					//~ if(empty($attachments))
					//~ {
						//~ $error_msg[] = 'Attachment is needed to send bill statement.'.$customer_no;
					//~ }
					//~ else
					//~ {
						//~ $config_query	= "`category` = 'email'";
						//~ $table_config	= $this->common_model->get_table('sys_config','*', $config_query);
						//~ $email_settings = array();
						//~ foreach ($table_config as $tc_key => $t_arr) $email_settings[$t_arr['key']] = $t_arr['val'];
						//~ $fullpath = $this->config->item('proj_path').$email_settings['set_attachment_at'].'/';
						//~ $att_path = $fullpath.$attachments;
						//~ if(!file_exists($att_path))
						//~ {
							//~ $error_msg[] = '['.$customer_no.']Attachment not found:'.$att_path;
						//~ }
						//~ else
						//~ {
							//~ $this->load->model('email_model');
							//~ $sent_mail 	= $this->email_model->generate_email($email_subject, $message, $attachments, $email_to,$email_from);
							//~
							//~ if($sent_mail != 1)
							//~ {
								//~ $error_msg[] = 'failed to send email: <br>'.print_r($sent_mail);
							//~ }
							//~ else
							//~ {
								//~ $query_str = "UPDATE customer SET last_email_sent = '". $date ."' WHERE customer_no = '" . $customer_no . "' ";
								//~ $this->db->query($query_str);
							//~ }
						//~ }
					//~ }
				//~ }
							//~
				//~ if(!empty($error_msg))
				//~ {
					//~ $error_array[$customer_no][] = $error_msg;
				//~ }
			//~ }
		//~ }
			//~
		//~ if(!empty($error_array))
		//~ {
			//~ echo count($error_array).'/'.count($cust_bill).'email have not sent.<br>';
			//~ _debug_array($error_array);
		//~ }
		//~ else
		//~ {
			//~ echo 'All email had been successfully sent!';
		//~ }
	}

    function bill_generate_bk_20160310()
	{
		// prevent fatal error Fatal error: Maximum execution time of 300 seconds exceeded in /var/www/html/ip/itelco/application/controllers/bill.php on line 433
		ini_set('max_execution_time', 0); //~ set_time_limit(0); ini_set('max_execution_time', -1);


		//get attachment path
		$subject		= 'penangFon Bill Statement';
		$email_from 	= 'penangFon';
		$message		= '';
		$attachments	= '';
		$email_to		= '';
		$config_query	= "`category` = 'email'";
		$table_config	= $this->common_model->get_table('sys_config','*', $config_query);

		$email_settings = array();
		foreach ($table_config as $tc_key => $t_arr) $email_settings[$t_arr['key']] = $t_arr['val'];
		$fullpath = $this->config->item('proj_path').$email_settings['set_attachment_at'].'/'.$attachments;

		//~ $query_str = "SELECT bill_no FROM bill WHERE bill_date >= '" . date("Y-m-1") . "' LIMIT 1";
		//~ $query = $this->db->query($query_str);
		$this_month_bill_count = $this->bill_model->this_month_bill_count();

		if ($this_month_bill_count >0) {
			exit("Same bill date existed!");
		}

		$cust_bill = $this->bill_model->bill_preparation();

		// $query_str 	= "SELECT MAX(b.bill_no) as bill_no FROM bill b";
		// $query 		= $this->db->query($query_str);
		$get_max_bill_no 	= $this->bill_model->get_max_bill_no();
		$new_bill_no 		= $get_max_bill_no+ 1;

		$query_insert_header = '';
		$query_insert_detail = '';

		foreach ($cust_bill as $customer_no => $row) {
			$date_first_day	= date('Y-m-1');
			$date_last_day	= date('Y-m-t');

			$query_insert_header .= "(" .
								"'" . $new_bill_no . "', " .
								"'" . $customer_no . "', " .
								"'" . $date_first_day . "', " .
								"'" . $date_last_day . "', " .
								"'" . $row['previous_balance'] . "', " .
								"'" . $row['payment_received'] . "', " .
								"'" . $row['charges'] . "', " .
								"'" . $row['tax_charges'] . "', " .
								"'" . $row['amount'] . "', " .
								"'" . $row['balance'] . "' " .
								"),";

			foreach ($row['charge_detail'] as $row_charge) {
				$query_insert_detail .= "(" .
									"'" . $new_bill_no . "', " .
									"'" . $row_charge['tranx_date'] . "', " .
									"'" . $row_charge['bill_type'] . "', " .
									"'" . $row_charge['amount'] . "', " .
									"'" . $row_charge['remark'] . "' " .
									")," ;
			}
			$new_bill_no ++;
		}

		//If no record insert, immediate exit;
		if ( empty($query_insert_header) || empty($query_insert_detail) ) {
			return 0;
		}

		//*** Insert the bill header by batch insert
		// $query_insert_header = rtrim($query_insert_header, ',');
		// $query_str = "INSERT INTO bill (bill_no, customer_no, bill_date, bill_due_date, previous_balance, payment_received, charges, tax_charges, amount, balance) VALUES " .
					// $query_insert_header;
		// $this->db->query($query_str);

		//temp comment out
		//$this->bill_model->generate_bill_header($query_insert_header);


		//*** Insert the bill detail by batch insert
		// $query_insert_detail = rtrim($query_insert_detail, ',');
		// $query_str = "INSERT INTO bill_detail (bill_no, tranx_date, bill_type, amount, remark) VALUES " .
					// $query_insert_detail;

		// $this->db->query($query_str);
		//$this->bill_model->generate_bill_detail($query_insert_detail);


		//Update next bill date
		// $query_str = "UPDATE customer SET next_bill_date = NULL WHERE next_bill_date = '" . date('Y-m-1') . "' AND status = 't' ";
		// $this->db->query($query_str);
		// $query_str = "UPDATE customer SET next_bill_date = DATE_ADD(next_bill_date, INTERVAL bill_cycle_month MONTH) WHERE next_bill_date = '" . date('Y-m-1') . "' ";
		// $this->db->query($query_str);
		$this->bill_model->update_next_bill_date();

		//Update payment processed
		// $query_str = "UPDATE payment SET processed = 1 WHERE pay_date < '" . date('Y-m-1') . "' ";
		// $this->db->query($query_str);
		$this->bill_model->update_payment_processed();

		//*** Lock the adjustment and payment
		// $query_str = "UPDATE bill_adjustment SET is_lock = 1 WHERE tranx_date < '" . date("Y-m-1") . "'";
		// $this->db->query($query_str);
		//
		// $query_str = "UPDATE payment SET is_lock = 1 WHERE pay_date < '" . date("Y-m-1") . "'";
		// $this->db->query($query_str);
		$this->bill_model->lock_adjustment_and_payment();

		//Generate the pdf and email to customer
		/*
		*/

		//create email object
		//~ include_once( APPPATH . 'controllers/email.php' );


		$error_array = array();
		if(empty($cust_bill))
		{
			$error_array[] = 'No customer to email.';
		}
		else
		{
			foreach ($cust_bill as $customer_no => $row)
			{
				$attachments 	= '';
				$error_msg		= array();
				// $obj_email 		= new Email();

				if(empty($cust_bill[$customer_no]['email_1']))
				{
					$query_str = "UPDATE customer SET `flag_email` = '2' WHERE customer_no = '" . $customer_no . "' ";
					$this->db->query($query_str);
					$error_msg[] = 'email is not defined :'.$customer_no;
				}
				else if(!filter_var($cust_bill[$customer_no]['email_1'], FILTER_VALIDATE_EMAIL))
				{
					$query_str = "UPDATE customer SET `flag_email` = '1' WHERE customer_no = '" . $customer_no . "' ";
					$this->db->query($query_str);
					$error_msg[] = 'email is invalid :'.$customer_no;
				}
				elseif($cust_bill[$customer_no]['bill_by_email'] != 1)
				{
					$error_msg[] = 'bill by email is not checked:'.$customer_no;
				}
				else
				{
					$query_str = "UPDATE customer SET `flag_email` = '0' WHERE customer_no = '" . $customer_no . "' ";
					$this->db->query($query_str);

					$timestamp		= time();
					$date 			= date("Y-m-d", $timestamp);
					$datetime 		= date("Y-m-d_h:i:sa", $timestamp);
					$filename 		= '['.$date.']'.'bill_statement_'.$customer_no.'_'.$datetime;

					$generate_pdf 	= $this->bill_statement('latest_bill_by',$customer_no,1,$filename);

					if($generate_pdf != 1)
					{
						$error_msg[] = 'fail to generated pdf :'.$customer_no;
					}
					else
					{
						$attachments = $filename.'.pdf';
						$query_str = "UPDATE customer SET last_file_gen = '".$attachments."' WHERE customer_no = '" . $customer_no . "' ";
						$this->db->query($query_str);
					}

					$pic_name 		= (empty($cust_bill[$customer_no]['pic_name']))?'User':$cust_bill[$customer_no]['pic_name'];
					$message		= 'Dear '.$pic_name.', \n The following attachment are the Bill Statement for '.$date.'.\n\n Regards,\nPenangfon.';
					$email_to		= $cust_bill[$customer_no]['email_1'];
					$email_subject 	= $subject;
					if(!empty($row['charge_detail'][0]['remark']))
					{
						$email_subject .= ' | '.$row['charge_detail'][0]['remark'];
					}

					if(empty($attachments))
					{
						$error_msg[] = 'Attachment is needed to send bill statement.'.$customer_no;
					}
					else
					{
						$att_path = $fullpath.$attachments;
						if(!file_exists($att_path))
						{
							$error_msg[] = '['.$customer_no.']Attachment not found:'.$att_path;
						}
						else
						{
							$this->load->model('email_model');
							$sent_mail 	= $this->email_model->generate_email($email_subject, $message, $attachments, $email_to,$email_from);
							//sent email
							//~ $sent_mail = $obj_email->generate_email($email_subject,$message,$attachments,$email_to,$email_from);

							//sent_mail = 1 means success
							if($sent_mail != 1)
							{
								$error_msg[] = 'failed to send email: <br>'.print_r($sent_mail);
							}
							else
							{
								$query_str = "UPDATE customer SET last_email_sent = '". $date ."' WHERE customer_no = '" . $customer_no . "' ";
								$this->db->query($query_str);
							}
						}
					}
				}

				if(!empty($error_msg))
				{
					$error_array[$customer_no][] = $error_msg;
				}
			}
		}

		if(!empty($error_array))
		{
			echo count($error_array).'/'.count($cust_bill).'email have not sent.<br>';
			_debug_array($error_array);
		}
		else
		{
			echo 'All email had been successfully sent!';
		}

	}

    public function index()
    {

    	$row_html = $this->bill_rows(1);

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();

			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['sel_category'] = $post_data['sel_category'];
			$data['sel_status'] = $post_data['sel_status'];
			$data['sel_bill_by'] = $post_data['sel_bill_by'];
			$data['txt_bill_date'] = $post_data['txt_bill_date'];

		} else {

			$bill_filter              = get_session_filter('bill_filter');
			$data['page_item_no'] = $bill_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $bill_filter['txt_search'] ?? '';
			$data['sel_category'] = $bill_filter['sel_category'] ?? 'all';
			$data['sel_status'] = $bill_filter['sel_status'] ?? 'all';
			$data['sel_bill_by'] = $bill_filter['sel_bill_by'] ?? 'all';
			$data['txt_bill_date'] = $bill_filter['txt_bill_date'] ?? '';

		}

		$data['page_title'] = 'Billing';
		$data['form_action'] = base_url('bill');

		$data['sel_status_list'] = $this->common_model->get_customer_status_list();
		$data['sel_category_list'] = $this->common_model->get_category_list();

		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		if( $aclInfo['role_name'] == 'BPO' ){
			foreach( $data['sel_category_list'] AS $row => $res ){
				if( $res['category_code'] != 'r' && $res['category_code'] != 'b' ) UNSET( $data['sel_category_list'][$row] ) ;
			}
		}
				
		$data['msg'] = $this->msg;

		$data['row_html'] = $row_html;

		$this->vars['cssfiles'][] = 'css/theme/bootstrap-grid.css';
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('bill/index',$data);
		$this->load->view('templates/footer');
	}

	public function bill_rows($returnOnly = 0)
	{

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$bill_filter              = get_session_filter('bill_filter');
			$post_data['page_item_no'] = $bill_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $bill_filter['txt_search'] ?? '';
			$post_data['sel_category'] = $bill_filter['sel_category'] ?? 'all';
			$post_data['sel_status'] = $bill_filter['sel_status'] ?? 'all';
			$post_data['sel_bill_by'] = $bill_filter['sel_bill_by'] ?? 'all';
			$post_data['txt_bill_date'] = $bill_filter['txt_bill_date'] ?? date('Y-m-t', strtotime('last day of this month'));
		}

		//generate rows html here
		if (!isset($post_data['page_item_no'])) {
			$post_data['page_item_no'] = 0;
		}

		if (!isset($post_data['txt_search'])) {
			$post_data['txt_search'] = '';
		}

		if (!isset($post_data['sel_category'])) {
			$post_data['sel_category'] = 'all';
		}

		if (!isset($post_data['sel_status'])) {
			$post_data['sel_status'] = 'all';
		}

		if (!isset($post_data['sel_bill_by'])) {
			$post_data['sel_bill_by'] = 'all';
		}

		if (!isset($post_data['txt_bill_date'])) {
			$post_data['txt_bill_date'] = date('Y-m-t', strtotime('last day of this month'));
		}

		//post values , must also run by session values
		$total_row = 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search = $post_data['txt_search'];
		$sel_category = $post_data['sel_category'];
		$sel_status = $post_data['sel_status'];
		$sel_bill_by = $post_data['sel_bill_by'];
		$txt_bill_date = $post_data['txt_bill_date'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_category' => $sel_category,
			'sel_status' => $sel_status,
			'sel_bill_by' => $sel_bill_by,
			'txt_bill_date' => $txt_bill_date,
		);
		set_session_filter('bill_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;
		if ( empty($sel_category)) $sel_category = 'all';
		if ( empty($sel_status)) $sel_status = 'all';
		if ($txt_bill_date == '') $txt_bill_date = '';

		$query_where = '';
		if ($sel_category != 'all' && !empty($sel_category)) {
			$query_where = "AND c.category = '$sel_category' ";
		}
		if ($sel_status != 'all' && !empty($sel_status)) {
			$query_where = $query_where."AND cls.status = '$sel_status' ";
		}
		if ($sel_bill_by != 'all' && !empty($sel_bill_by)) {
			if ($sel_bill_by == 'p') {
				$query_where .= "AND c.bill_by_post = '1' ";
			}
			if ($sel_bill_by == 'e') {
				$query_where .= "AND c.bill_by_email = '1' ";
			}
		}
		if (!empty($txt_bill_date)){
			$query_where .= " AND ( b.bill_date <= '".date("Y-m-d",strtotime($txt_bill_date))."' ) " ;			
		}

		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		if( $aclInfo['role_name'] == 'BPO' ){
			$query_where .= " AND c.category IN ( 'b' , 'r' ) ";
		}

		$get_list 	= $this->bill_model->get_bill_list($txt_search,$page_item_no,$query_where);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		foreach ($row as $key => $val) {
			$row[$key]['activated_date'] = date_toggle($row[$key]['activated_date'],$_SESSION['config']['date_format']);
			$row[$key]['last_bill_date'] = date_toggle($row[$key]['last_bill_date'],$_SESSION['config']['date_format']);
			$row[$key]['latest_status_name'] = (isset($this->account_status[$row[$key]['latest_status']]) ? $this->account_status[$row[$key]['latest_status']] : '');
		}

		// for paginations
		$data['pagination'] = paginationSettingsAjax('bill', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data'] 			= $row;

		$html = $this->parser->parse('bill/bill_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}

	function set_form_validation($mode = 'search', $extra_rules = [])
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'name', 'label' => 'Customer Name', 'rules' => 'trim|required'),
					),
					'bill_manual_save' => array (
						array('field' => 'customer_no', 'label' => 'Customer No', 'rules' => 'trim|required'),
						array('field' => 'bill_date', 'label' => 'Bill Date', 'rules' => 'trim|required'),
					),
				);

		if ($mode === 'bill_manual_save') {
			$row_order = $this->input->post('tblAppendGrid_rowOrder');
			
			if (!empty($row_order)) {
				$rows = array_filter(explode(',', $row_order), 'strlen');

				foreach ($rows as $id) {
					$fields = [
						'date'     => 'Date',
						'category' => 'Category',
						'charge'   => 'Charge'
					];

					foreach ($fields as $key => $label) {
						$rules = 'trim|required';
						if ($key === 'charge') {
							$rules .= '|numeric';
						}

						$config['bill_manual_save'][] = [
							'field' => "tblAppendGrid_{$key}_{$id}",
							'label' => "Bill Item #{$id} {$label}",
							'rules' => $rules
						];
					}
				}
			}
		}

		$rules = isset($config[$mode]) ? $config[$mode] : [];
		if (!empty($extra_rules)) {
			$rules = array_merge($rules, $extra_rules);
		}

		$this->form_validation->set_rules($rules);
	}

	function bill_detail($customer_no='')
	{
		$bill = $this->bill_model->get_bill($customer_no);
		if ($bill['customer_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Customer not found!');
			redirect('bill');
		}

		//~ echo "<pre>";
		//~ print_r($bill);
		//~ echo "</pre>";
		
		$bill['activated_date'] 	= (empty($bill['activated_date']))?'':date_toggle($bill['activated_date'],$_SESSION['config']['date_format']);
		$bill['next_bill_date'] 	= (empty($bill['next_bill_date']))?'':date_toggle($bill['next_bill_date'],$_SESSION['config']['date_format']);
		$bill['last_billed_date'] 	= (empty($bill['last_billed_date']))?'':date_toggle($bill['last_billed_date'],$_SESSION['config']['date_format']);
		$bill['bill_due_date'] 		= (empty($bill['bill_due_date']))?'':date_toggle($bill['bill_due_date'],$_SESSION['config']['date_format']);

		$bill['status_name'] = (isset($this->account_status[$bill['latest_status']]) ? $this->account_status[$bill['latest_status']] : '');
		
		if(!empty($bill['charge_detail']))
		{
			foreach ($bill['charge_detail'] as $bill_key => $bill_arr)
			{
				$bill['charge_detail'][$bill_key]['tranx_date'] =
				date_toggle($bill['charge_detail'][$bill_key]['tranx_date'],$_SESSION['config']['date_format']);
			}
		}

		//row details
		$row_data = $this->bill_model->get_bill_list_by_cust_no($customer_no);
		$last_bill = $this->bill_model->get_last_bill($customer_no,1);

		$last_payment = $this->bill_model->get_last_payment($customer_no);

		//$unpayment = $this->bill_model->get_last_payment($customer_no,1);
		$unpayment = $this->bill_model->get_unprocessed_payment_by_customer($customer_no);

		foreach ($row_data as $key => $val) {
			$row_data[$key]['bill_date'] = date_toggle($row_data[$key]['bill_date'],$_SESSION['config']['date_format']);

			$row_data[$key]['bill_due_date'] = date_toggle($row_data[$key]['bill_due_date'],$_SESSION['config']['date_format']);

			$row_data[$key]['last_printed_date'] = 
			date_toggle($row_data[$key]['last_printed_date'],$_SESSION['config']['date_format']);
			
			if( $row_data[$key]['is_void'] == 1 )
			{
				$row_data[$key]['tr_class'] = "is_void";
				$row_data[$key]['void_msg'] = "VOIDED";
				$row_data[$key]['print_invoice_action'] = "";
				$row_data[$key]['print_bill_action'] = "";
				$row_data[$key]['detail_action'] = "";
			}else{
				$row_data[$key]['tr_class'] = "";
				$row_data[$key]['void_msg'] = "";
				$row_data[$key]['print_invoice_action'] = 
				"<a href='".base_url('bill/print_invoice/no')."/".$row_data[$key]['bill_no']."'>
					<i class='menu-icon fa fa-files-o grey' ".tooltip_helper('Print Invoice')."></i>
				</a>";
				
				$row_data[$key]['print_bill_action'] =
				"<a href='".base_url('bill/bill_statement/bill/')."/".$row_data[$key]['bill_no']."'>
					<i class='menu-icon fa fa-print light-red' ".tooltip_helper('Print Bill Statement')."></i>
				</a>";
				
				$row_data[$key]['detail_action'] =
				"<a href='#' onclick='show_popup(\"bill/bill_itemized/\",".$row_data[$key]['bill_no'].");'>
					<i class='menu-icon fa fa-list grey' ".tooltip_helper('Details')."></i>
				</a>";
			}

			if ($row_data[$key]['einvoice_status'] != '' && $row_data[$key]['einvoice_status'] != 'S' && $row_data[$key]['einvoice_status'] != 'P') {
				$row_data[$key]['einvoice_action'] = "<a href='#' onclick='show_popup(\"bill/bill_einvoice/\",".$row_data[$key]['bill_no'].");'>
					<i class='menu-icon fa fa-files-o blue' ".tooltip_helper('E-Invoice')."></i>
				</a>";
			} else {
				$bill_no   = $row_data[$key]['bill_no'];
    			$bill_type = $row_data[$key]['bill_type'];

				$row_data[$key]['einvoice_action'] =
				"<a href='#' onclick='submit_einvoice(\"".$row_data[$key]['bill_no']."\", \"".$row_data[$key]['bill_type']."\", this); return false;'>
					<i class='menu-icon fa fa-paper-plane green' ".tooltip_helper('Submit E-Invoice')."></i>
				</a>";
			}

			//checking is last bill, to add VOID button
			if ( ( $row_data[$key]['bill_no'] == $last_bill['bill_no'] ) && (check_acl('bill', 'M', false)) ){
				$row_data[$key]['void_action'] = "<a href='#' onclick='prep_void_bill(".$last_bill['bill_no'].")'>
													<i class='menu-icon fa fa-remove grey' ".tooltip_helper('Void')." ></i>
												  </a>";
			}else{
				$row_data[$key]['void_action'] = "";
			}
		}
		
		if(isset($unpayment) && !empty($unpayment)){
			$data['unpayment_data']	= $unpayment; 
		}

		if(isset($last_payment) && !empty($last_payment)){
			
			$last_paydate = $last_payment['pay_date'];

			$data['paydate'] 	= $last_paydate;
		}
		

		$data['input'] 		= $bill;

	
		$data['row_data'] 	= $row_data;
		$data['page_title'] = 'Bill Details';
		$data['msg'] 		= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('bill/bill_detail',$data);
		$this->load->view('templates/footer');
	}

	function ajax_void_bill(){
		$msg = array();
		list( $msg['success'] , $msg['fail'] ) = $this->bill_model->void_bill( $this->input->post('bill_no'), $this->input->post('void_reason') );
		echo json_encode( $msg );
	}

	function ajax_get_last_bill(){
		$return = array();
		$return = $this->bill_model->get_last_bill( $this->input->post('customer_no') , 1 );
		echo json_encode( $return );
	}
	
	function ajax_get_customer_bill(){
		$return = array();
		
		$qwhere = '';
		if( $this->input->post('customer_no') )
			$qwhere .= " AND b.customer_no = '".$this->input->post('customer_no')."' ";
			
		if( $this->input->post('bill_date') )
			$qwhere .= " AND b.bill_date = '".$this->input->post('bill_date')."' ";
		
		$bills = $this->bill_model->get_export_filtered( $qwhere );
		
		$return['exist'] = !empty( $bills ) == true ? '1'  : '0' ; 
		
		echo json_encode( $return );
	}

	function ajax_get_invoice_detail(){
		$return = array();
		$qwhere = '';

		if( $this->input->post('bill_no') )
			$qwhere .= " AND bill_no = '".$this->input->post('bill_no')."' ";
		
		$bill_details = $this->bill_model->get_bill_detail($qwhere);
		
		echo json_encode( $bill_details );
	}
	
	//get_payment_filtered
	function ajax_get_unprocess_payment(){
		$return = array();
		
		$qwhere = "";
		$customer_no = $this->input->post('customer_no');
		$payment_no = $this->input->post('payment_no');
		if( $customer_no != '' && $payment_no != '' ){
			$qwhere .= " AND p.customer_no = '".$customer_no."' 
						 AND p.payment_no LIKE '%".$payment_no."%'
						 AND p.is_lock = 0 
						 AND p.processed = 0 ";

			$this->load->model('payment_model');
			$return = $this->payment_model->get_payment_filtered($qwhere);
		}

		echo json_encode( $return );
	}
	
	function ajax_update_po_no(){
		$msg = array();
		list( $msg['success'] , $msg['fail'] ) = $this->bill_model->update_po_no( $this->input->post('bill_no') , 
																				  $this->input->post('po_no') );
		echo json_encode( $msg );
	}

	function bill_itemized($bill_no='')
	{
		$data['row_data'] = $this->bill_model->get_bill_itemized($bill_no);
		$data['page_title'] = 'Bill Details';
		$this->parser->parse('bill/bill_itemized',$data);
	}

	function bill_einvoice($bill_no='')
	{
		$data['row_data'] = $this->bill_model->get_einvoice_status($bill_no);
		$data['page_title'] = 'E-Invoice Status';
		$data['einvoice_status'] = $this->einvoice_status;
		$this->load->view('bill/bill_einvoice',$data);
	}

	function bill_statement($statement_by, $statement_key, $gen_pdf = 0 , $pdf_name = '', $filter_date='')
	{

		$contact_list = $this->input->post('contact_list');
		
		$this->load->model('bill_model');
		$bill_model = $this->bill_model->bill_statement($statement_by, $statement_key, $gen_pdf, $pdf_name, $filter_date);
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
		
		$content_data['data'][0]['customer'][0]['display_name'] .= $content_data['data'][0]['customer'][0]['category'] != 'r' 
																		? ' (' . $content_data['data'][0]['customer'][0]['comp_ssm'] . ')'
																		: '';

		$data['msg'] 				= $this->msg;
		$gen_pdf 					= $bill_model['gen_pdf'];

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			/*$header_data 				= $this->vars;
			$header_data['title'] 		= 'print preview | invoice';
			$header_data['description']	= 'to print invoice';
			$content_data['data']		= $invoice;

			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);
			write_file($this->config->item('upload_path').'/temp/pdf/bill_'.str_replace(' ', '', $invoice[$param_2]['doc_title']).'_'.$type_val.'.pdf', $pdf_data);*/

			$scode = md5($statement_key . $this->e_key);
			puppeteer_print_preview(
				$this->config->item('base_url').'pdfapi/bill_statement/'.$statement_by.'/'.$statement_key.'/'.$scode.'/'.$filter_date, 
				$this->config->item('upload_path').'/temp/pdf/statement_'.$statement_key.'.pdf', 
				$this->config->item('proj_path'), 
				$this->config->item('chrome_loc'));

			//send pdf
			$this->load->library('whatsapp_template');
			$meta_template = $this->whatsapp_template->build('ITELCO DOCS', ['doc_type' => 'Bill Statement']);

			$email_list = $json_contact_list[0];
			$whatsapp_list = $json_contact_list[1];
			$telegram_list = $json_contact_list[2];

			$send_array = array();
			$send_array['send_type'] = 'custom';
			$send_array['acc_id'] = 0;
			$send_array['customer_no'] = 0;
			$send_array['user_id'] = 0;
			$send_array['controller'] = 'bill';
			$send_array['doc_id'] = $statement_key;
			$send_array['send_method'] = 'manual';
			$send_array['acc_name'] = 'Itelco User';
			$send_array['attachment'] = $this->config->item('upload_path').'/temp/pdf/statement_'.$statement_key.'.pdf';
			$send_array['subject'] = 'Bill Statement - '.$statement_key;
			$send_array['body'] = 'Attached herewith is the bill statement for document ' . $statement_key;
			$send_array['email_starter'] = 'Bill Statement - '.$statement_key;
			$send_array['doc_type'] = '[Bill Statement Document]';
			$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
			$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];
			$send_array['email_list'] = $email_list;
			$send_array['whatsapp_list'] = $whatsapp_list;
			$send_array['telegram_list'] = $telegram_list;

			$send_result = $this->docs_log_model->do_send($send_array);

			//$job_order_send = 1;
			//@unlink($this->config->item('upload_path').'/temp/pdf/customer_support_form_'.$cs_no.'.pdf');
		}

		$html  = '';
		if($gen_pdf == '1')
		{
			$doc_template = $this->config->item('doc_template');

			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);

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
			
			$this->load->helper(array('dompdf', 'file'));
			$data = pdf_create($html, '', false);

			//if(empty($pdf_name)) $pdf_name = 'temp_bill_statement_'.date("Y-m-d_h:i:sa", time());
			if(empty($pdf_name)) $pdf_name = 'bill_statement_'.date("Y-m-d");
			$result = write_file('temp/'.$pdf_name.'.pdf', $data);
			return $result;
		}
		else
		{
			$menu_data = array();
			$menu_data['send_btn'] = 1;

			// auto add customer notification info
			$this->load->model('customer_model');
			$profile_id = $this->customer_model->get_acc_id_by_customer_no($statement_key);
			if(!empty($profile_id['profile_id'])) {
				$this->load->model('profile_model');
				$profile = $this->profile_model->get_user_profile_notification_info($profile_id['profile_id']);
			}
			$menu_data['default_send_user'] = !empty($profile) ? json_encode($profile) : '';

			$menu_data['form_action'] = base_url('bill/bill_statement/'.$statement_by.'/'.$statement_key.'/'.$gen_pdf.'/'.$pdf_name.'/'.$filter_date);

			/*
			echo "<pre>";
			print_r($content_data);
			echo "</pre>";
			exit;
			*/

			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', $menu_data, true);

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
	}

	function bill_statement_manual($statement_by, $statement_key, $gen_pdf = 0 , $pdf_name = '')
	{
		$this->load->model('bill_model');
		$bill_model = $this->bill_model->bill_statement_manual($statement_by, $statement_key, $gen_pdf, $pdf_name);
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
		
		return $bill_model;
	}

	function bill_statement_bk($statement_by, $statement_key, $gen_pdf = 0 , $pdf_name = '')
	{
		$seg 			= $this->get_segment();
		$controller		= $seg[0];
		$method 		= $seg[1];
		$param_1		= (empty($statement_by)?$seg[2]:$statement_by);
		$param_2		= (empty($statement_key)?$seg[3]:$statement_key);
		$param_3		= (empty($seg[4]))?'':$seg[4];
		$input			= array();

		if ( !empty($param_3) )  {
			$this->session->set_flashdata("warning_msg", 'Bill Statement not found!');
			redirect('bill');
		}

		if((!empty($_POST['btPrintFiltered'])) ||  ($param_1 == 'latest_bill_by'))
		{
			if(!is_array($statement_key)){
				$make_array[0] = $statement_key;
				$statement_key = $make_array;
			}

			$input_data_arr = array();

			//~ $statement_arr  = array();
			$input['data'] = $this->bill_model->get_statement('latest_bill_by',$statement_key);
			foreach ($input['data'] as $key => $val)
			{
				foreach ($val['bill_detail'] as $key2 => $val2)
				$input['data'][$key]['bill_detail'][$key2]['bill_type_name'] = get_bill_type_name_by_id($input['data'][$key]['bill_detail'][$key2]['bill_type']);
			}
		}
		else
		{
			if ( empty($param_1) || empty($param_2) || (($param_1 != 'bill' ) && ($param_1 != 'customer')  && ($param_1 != 'latest_bill_by')) )  {
			$this->session->set_flashdata("warning_msg", 'Bill Statement not found!');
				redirect('bill');
			}

			$bill_statement = $this->bill_model->get_statement($param_1,$param_2);

			$statement_arr = array();

			foreach ($bill_statement as $bs_key => $bs_arr){
				$statement_arr['customer'][] = $bill_statement[$bs_key]['customer'];
				$statement_arr['bill'][] = $bill_statement[$bs_key]['bill'];

				$statement_arr['bill_detail'] = array();
				if(!empty($bill_statement[$bs_key]['bill_detail']))
				{
					foreach ($bill_statement[$bs_key]['bill_detail'] as $bd_key => $bd_arr){
						$bill_statement[$bs_key]['bill_detail'][$bd_key]['bill_type_name'] = get_bill_type_name_by_id($bill_statement[$bs_key]['bill_detail'][$bd_key]['bill_type']);
					}
					$statement_arr['bill_detail'] = $bill_statement[$bs_key]['bill_detail'];
				}
				$input['data'][] = $statement_arr;
				$statement_arr = array();
			}
		}

        //get tax value from default tax code
		//~ $query_str 			= "SELECT `val` FROM `sys_config` WHERE `key` = 'default_tax'";
		//~ $result 			= $this->db->query($query_str)->result_array();
        //~ $default_tax_code	= (empty($result[0]['val']))?'SR':$result[0]['val'];
        //~ $query_str 			= "SELECT percent FROM sys_tax_type WHERE code = '".$default_tax_code."'";
		//~ $gst_amount 		= $this->db->query($query_str)->result_array();
        $gst_amount	= $this->common_model->get_default_tax_amount();

		foreach($input['data'] as $i_data_key => $i_data_val)
		{
			//Get gst amount
			$input['data'][$i_data_key]['customer'][0]['default_tax'] =
			$gst_amount[0]['percent'];

			$check_cat = $input['data'][$i_data_key]['customer'][0]['category'];
			if($check_cat != 'w')
			{
				$input['data'][$i_data_key]['customer'][0]['pay_on_or_before'] =
				date_toggle($input['data'][$i_data_key]['bill'][0]['bill_date'],$_SESSION['config']['date_format']);
			}
			else
			{
				$input['data'][$i_data_key]['customer'][0]['pay_on_or_before'] =
				date_toggle($input['data'][$i_data_key]['bill'][0]['bill_due_date'],$_SESSION['config']['date_format']);
			}

			$input['data'][$i_data_key]['customer'][0]['next_bill_date'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['next_bill_date']);

			$input['data'][$i_data_key]['customer'][0]['date_of_birth'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['date_of_birth']);

			$input['data'][$i_data_key]['customer'][0]['signup_date'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['signup_date']);

			$input['data'][$i_data_key]['customer'][0]['activated_date'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['activated_date']);

			$input['data'][$i_data_key]['customer'][0]['suspended_date'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['suspended_date']);

			$input['data'][$i_data_key]['customer'][0]['terminated_date'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['terminated_date']);

			$input['data'][$i_data_key]['customer'][0]['created_date'] =
			convert_date_1($input['data'][$i_data_key]['customer'][0]['created_date']);

			$input['data'][$i_data_key]['customer'][0]['modified_date'] =
			convert_date_2($input['data'][$i_data_key]['customer'][0]['modified_date']);

			$input['data'][$i_data_key]['bill'][0]['bill_date'] =
			convert_date_1($input['data'][$i_data_key]['bill'][0]['bill_date']);

			$input['data'][$i_data_key]['bill'][0]['bill_due_date'] =
			convert_date_1($input['data'][$i_data_key]['bill'][0]['bill_due_date']);

			$input['data'][$i_data_key]['bill'][0]['last_printed_date'] =
			convert_date_1($input['data'][$i_data_key]['bill'][0]['last_printed_date']);

			$input['data'][$i_data_key]['bill'][0]['bill_no'] =
			add_zero($input['data'][$i_data_key]['bill'][0]['bill_no']);



			$input['data'][$i_data_key]['customer'][0]['display_name'] 		= $input['data'][$i_data_key]['customer'][0]['name'];
			$input['data'][$i_data_key]['customer'][0]['display_addr1'] 	= $input['data'][$i_data_key]['customer'][0]['inst_addr1'];
			$input['data'][$i_data_key]['customer'][0]['display_addr2'] 	= $input['data'][$i_data_key]['customer'][0]['inst_addr2'];
			$input['data'][$i_data_key]['customer'][0]['display_city'] 		= $input['data'][$i_data_key]['customer'][0]['inst_city'];
			$input['data'][$i_data_key]['customer'][0]['display_postcode'] 	= $input['data'][$i_data_key]['customer'][0]['inst_postcode'];
			$input['data'][$i_data_key]['customer'][0]['display_state'] 	= $input['data'][$i_data_key]['customer'][0]['inst_state'];

			//overide display_sets if bill_addr1 is not empty
			if(!empty($input['data'][$i_data_key]['customer'][0]['bill_addr1']))
			{
				$input['data'][$i_data_key]['customer'][0]['display_name'] 		= $input['data'][$i_data_key]['customer'][0]['bill_name'];
				$input['data'][$i_data_key]['customer'][0]['display_addr1'] 	= $input['data'][$i_data_key]['customer'][0]['bill_addr1'];
				$input['data'][$i_data_key]['customer'][0]['display_addr2'] 	= $input['data'][$i_data_key]['customer'][0]['bill_addr2'];
				$input['data'][$i_data_key]['customer'][0]['display_city'] 		= $input['data'][$i_data_key]['customer'][0]['bill_city'];
				$input['data'][$i_data_key]['customer'][0]['display_postcode'] 	= $input['data'][$i_data_key]['customer'][0]['bill_postcode'];
				$input['data'][$i_data_key]['customer'][0]['display_state'] 	= $input['data'][$i_data_key]['customer'][0]['bill_state'];
			}

			foreach ($input['data'][$i_data_key]['bill_detail'] as $b_d_key => $b_d_val)
			{
				$input['data'][$i_data_key]['bill_detail'][$b_d_key]['tranx_date'] =
				convert_date_1($input['data'][$i_data_key]['bill_detail'][$b_d_key]['tranx_date']);
			}
		}


		//get default tax code
        //~ $query_str 			= "SELECT `val` FROM `sys_config` WHERE `key` = 'gst_reg_no'";
		//~ $result 			= $this->db->query($query_str)->result_array();
        //~ $gst_reg_no			= (empty($result[0]['val']))?'SR':$result[0]['val'];
        $gst_reg_no	 =  $this->common_model->get_gst_reg_no();

		$this->load->helper('form');
		$header_data 					= $this->vars;
		$header_data['title']			= 'print preview';
		$header_data['description']		= 'to print bill statements';
		$content_data['data']			= (empty($input['data']))?'':$input['data'];
		$content_data['gst_reg_no']		= (empty($gst_reg_no))?'':$gst_reg_no;
		$data['msg'] = $this->msg;


		_debug_array($header_data);
		exit;
		$html  = '';
		if($gen_pdf == '1')
		{

			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('bill/bill_statement', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));

			$data = pdf_create($html, '', false);

			if(empty($pdf_name)) $pdf_name = 'temp_bill_statement_'.date("Y-m-d_h:i:sa", time());
			$result = write_file('temp/'.$pdf_name.'.pdf', $data);
			return $result;
		}
		else
		{

			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', '', true);
			$html .= $this->parser->parse('bill/bill_statement', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);
			echo $html;
		}
	}

	function print_invoice($type = '', $type_val = '', $gen_pdf = 0 , $pdf_name = '')
	{
		$seg 			= $this->get_segment();
		$controller		= $seg[0];
		$method 		= $seg[1];
		$param_1		= (empty($type))?$seg[2]:$type;
		$param_2		= (empty($type_val))?$seg[3]:$type_val;
		$param_3		= (empty($seg[4]))?'':$seg[4];

		if ( empty($param_1) || empty($param_2) || !empty($param_3) || (($param_1 != 'no' )&&($param_1 != 'date')))  {
			$this->session->set_flashdata("warning_msg", 'Bill Invoice not found!');
			redirect('bill');
		}

		$contact_list = $this->input->post('contact_list');

		if ($param_1 == 'no') $invoice = $this->bill_model->get_invoice($param_2,'');
		else if ($param_1 == 'date') $invoice = $this->bill_model->get_invoice('',$param_2);

		foreach($invoice as $i_key => $i_val)
		{
			// convert money format text back to float
			$invoice[$i_key]['actual_amount']		= (float) str_replace(",", "", $invoice[$i_key]['amount']); 
			$invoice[$i_key]['bill_no'] 			= add_zero($invoice[$i_key]['bill_no']);
			$invoice[$i_key]['bill_date'] 			= date_toggle($invoice[$i_key]['bill_date'],$_SESSION['config']['date_format_printing']);
			$invoice[$i_key]['bill_due_date']		= date_toggle($invoice[$i_key]['bill_due_date'],$_SESSION['config']['date_format_printing']);
			$invoice[$i_key]['display_name'] = $invoice[$i_key]['customer_name'] . 
    			($invoice[$i_key]['customer_category'] != 'r' ? ' (' . $invoice[$i_key]['comp_ssm'] . ')' : '');
		}

		//get default tax code
        //~ $query_str 			= "SELECT `val` FROM `sys_config` WHERE `key` = 'gst_reg_no'";
		//~ $result 			= $this->db->query($query_str)->result_array();
        //~ $gst_reg_no			= (empty($result[0]['val']))?'SR':$result[0]['val'];

		$header_data 				= $this->vars;
		$header_data['title'] 		= 'print preview | invoice';
		$header_data['description']	= 'to print invoice';
		$content_data['data']		= $invoice;

		/*
		echo "<pre>";
		print_r($invoice);
		echo "</pre>";
		exit;
		*/

		$content_data['bill_no'] = $param_2;
		
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

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'comp_bank_acc' ");
		$content_data['comp_bank_acc'] = $config[0]['val'];
		
		$data['msg'] 				= $this->msg;

		//_debug_array($content_data); exit;

		//einvoice qr code
		$content_data['einvoice_qr'] = '';
		if (!empty($invoice[$param_2]['einvoice_longid']) && !empty($invoice[$param_2]['einvoice_uuid'])) {
			$this->load->model('einvoice_api_model');
			$qr_data = array();
			$qr_data['portal'] = $this->config->item('einvoice_portal_url');
			$qr_data['uuid'] = $invoice[$param_2]['einvoice_uuid'];
			$qr_data['longid'] = $invoice[$param_2]['einvoice_longid'];
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

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			/*$header_data 				= $this->vars;
			$header_data['title'] 		= 'print preview | invoice';
			$header_data['description']	= 'to print invoice';
			$content_data['data']		= $invoice;

			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);
			write_file($this->config->item('upload_path').'/temp/pdf/bill_'.str_replace(' ', '', $invoice[$param_2]['doc_title']).'_'.$type_val.'.pdf', $pdf_data);*/

			$scode = md5($type_val . $this->e_key);
			puppeteer_print_preview(
				$this->config->item('base_url').'pdfapi/bill_invoice/'.$type.'/'.$type_val.'/'.$scode, 
				$this->config->item('upload_path').'/temp/pdf/bill_'.str_replace(' ', '', $invoice[$param_2]['doc_title']).'_'.$type_val.'.pdf', 
				$this->config->item('proj_path'), 
				$this->config->item('chrome_loc'));

			//send pdf
			$this->load->library('whatsapp_template');
			$meta_template = $this->whatsapp_template->build('ITELCO DOCS', ['doc_type' => 'Invoice']);

			$email_list = $json_contact_list[0];
			$whatsapp_list = $json_contact_list[1];
			$telegram_list = $json_contact_list[2];

			$send_array = array();
			$send_array['send_type'] = 'custom';
			$send_array['acc_id'] = 0;
			$send_array['customer_no'] = 0;
			$send_array['user_id'] = 0;
			$send_array['controller'] = 'bill';
			$send_array['doc_id'] = $type_val;
			$send_array['send_method'] = 'manual';
			$send_array['acc_name'] = 'Itelco User';
			$send_array['attachment'] = $this->config->item('upload_path').'/temp/pdf/bill_'.str_replace(' ', '', $invoice[$param_2]['doc_title']).'_'.$type_val.'.pdf';
			$send_array['subject'] = $invoice[$param_2]['doc_title'].' - '.$type_val;
			$send_array['body'] = 'Attached herewith is the '.$invoice[$param_2]['doc_title'].' document - ' . $type_val;
			$send_array['email_starter'] = $invoice[$param_2]['doc_title'].' - '.$type_val;
			$send_array['doc_type'] = '[Bill Document]';
			$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
			$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];
			$send_array['email_list'] = $email_list;
			$send_array['whatsapp_list'] = $whatsapp_list;
			$send_array['telegram_list'] = $telegram_list;

			$send_result = $this->docs_log_model->do_send($send_array);

			//$job_order_send = 1;
			//@unlink($this->config->item('upload_path').'/temp/pdf/customer_support_form_'.$cs_no.'.pdf');
		}

		$html = '';
		if($gen_pdf == '1')
		{
			$html .= $this->load->view('templates/print_header_genpdf', '', true);
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

			$this->load->helper(array('dompdf', 'file'));
			$data = pdf_create($html, '', false);

			if(empty($pdf_name)) $pdf_name = 'temp_invoice_'.date("Y-m-d_h:i:sa", time());
			$result = write_file('temp/'.$pdf_name.'.pdf', $data);
			return $result;
		}
		else
		{

			$menu_data = array();
			$menu_data['send_btn'] = 1;
			//$menu_data['include_customer'] = 1;
			$menu_data['form_action'] = base_url('bill/print_invoice/'.$type.'/'.$type_val.'/'.$gen_pdf.'/'.$pdf_name);

			$this->load->view('templates/print_header', $header_data);
			$this->load->view('templates/print_menu', $menu_data);
			//$this->parser->parse('bill/bill_invoice',$content_data);

			$doc_template = $this->config->item('doc_template');

			if (!empty($doc_template)) {

				if (file_exists(FCPATH.'application/views/bill/template/'.$doc_template.'/bill_invoice.php')) {
					$this->parser->parse('bill/template/'.$doc_template.'/bill_invoice', $content_data);
				} else {
					$this->parser->parse('bill/bill_invoice', $content_data);
				}

			} else {
				$this->parser->parse('bill/bill_invoice', $content_data);
			}

			$this->load->view('templates/print_footer');
		}

	}

	private function table_template($template = '')
	{
		$this->load->library('table');

        if($template != '')
        {
			if($template == 'main')
			{
				$tmpl['table_open'] 		= '<table class="table table-striped table-bordered">';
				$tmpl['heading_row_start'] 	= '<tr>';
				$tmpl['heading_row_end'] 	= '<tr>';
				$tmpl['heading_cell_start'] = '<th>';
				$tmpl['heading_cell_end'] 	= '</th>';
				$tmpl['row_start'] 			= '<tr>';
				$tmpl['row_end'] 			= '</tr>';
				$tmpl['cell_start'] 		= '<td>';
				$tmpl['cell_end'] 			= '</td>';
				$tmpl['row_alt_start'] 		= '<tr>';
				$tmpl['row_alt_end'] 		= '</tr>';
				$tmpl['cell_alt_start'] 	= '<td>';
				$tmpl['cell_alt_end'] 		= '</td>';
				$tmpl['table_close'] 		= '</table>';
			}

			if(!empty($tmpl)) $this->table->set_template($tmpl);
		}
	}

	//~ private function get_segment()
	//~ {
		//~ $seg = 1;
		//~ $segment = array();
		//~ while ($seg != 0)
		//~ {
			//~ $seg_info 	= '';
				//~
			//~ $segment[] = $seg_info = $this->uri->segment($seg);
							//~
			//~ if($seg_info != ''){
				//~ $seg++;
			//~ }
			//~ else
			//~ {
				//~ $seg = 0;
				//~ return $segment;
			//~ }
		//~ }
	//~ }

	public function print_filtered( $layout = 'bill' )
	{
		if(!$_POST)
		{
			$this->session->set_flashdata("warning_msg", 'invalid access');
			redirect('bill');
		}
		else
		{
			$total_row 		= 0;
			$page_item_no 	= $this->input->post('page_item_no');
			$txt_search 	= $this->input->post('txt_search');
			$sel_category 	= $this->input->post('sel_category');
			$sel_status 	= $this->input->post('sel_status');
			$sel_bill_by 	= $this->input->post('sel_bill_by');
			$txt_bill_date  = $this->input->post('txt_bill_date');

			if ($page_item_no == '')	$page_item_no 	= 0;
			if ( empty($sel_category) )	$sel_category 	= 'r';
			if ( empty($sel_status) )	$sel_status 	= 'r';
			//if ($txt_bill_date == '' )	$txt_bill_date 	= date("Y-m-d");

			$query_where = '';
			if ($sel_category != 'all' && !empty($sel_category))
			{
				$query_where .= "AND c.category = '$sel_category' ";
			}
			if ($sel_status != 'all' && !empty($sel_status))
			{
				$query_where .= "AND c.status = '$sel_status' ";
			}
			if ($sel_bill_by != 'all' && !empty($sel_bill_by))
			{
				if ($sel_bill_by == 'p') $query_where .= "AND c.bill_by_post = '1' ";
				if ($sel_bill_by == 'e') $query_where .= "AND c.bill_by_email = '1' ";
			}
			
			if (!empty($txt_bill_date)){
				$query_where .= " AND ( b.bill_date BETWEEN '".date("Y-m-01",strtotime($txt_bill_date))."'
										AND '".date("Y-m-d",strtotime($txt_bill_date))."' ) " ;
			}



			$isNotBtPrintFiltered = (!empty($_POST['btPrintFiltered']))? '1' : '0';
			$result = $this->bill_model->get_print_filtered($txt_search,$page_item_no,$query_where,$isNotBtPrintFiltered);
			
			
			
			if(!empty($_POST['btPrintFiltered']))
			{
				if( $layout == 'bill' ){					
					$this->bill_statement('filtered_bill',$result['print_cust_no_arr'],"0","",$txt_bill_date);
					exit;
				}
				elseif( $layout == 'invoice' ){
					$this->print_invoice('no',$result['print_bill_no_arr']);
				}
			}
			else
			{
				$this->session->set_flashdata("warning_msg", 'Record not found!');
				redirect('bill');
			}
		}
	}

	public function xml_filtered( $xml_type = 'bill' )
	{
		if(!$_POST)
		{
			$this->session->set_flashdata("warning_msg", 'invalid access');
			redirect('bill');
		}
		else
		{
			$total_row 		= 0;
			$page_item_no 	= $this->input->post('page_item_no');
			$txt_search 	= $this->input->post('txt_search');
			$sel_category 	= $this->input->post('sel_category');
			$sel_status 	= $this->input->post('sel_status');
			//$sel_bill_by 	= $this->input->post('sel_bill_by');
			$txt_bill_date_from  = $this->input->post('txt_bill_date_from');
			$txt_bill_date_to  = $this->input->post('txt_bill_date_to');

			if ($page_item_no == '')	$page_item_no 	= 0;
			if ( empty($sel_category) )	$sel_category 	= 'r';
			if ( empty($sel_status) )	$sel_status 	= 'r';
			if ($txt_bill_date_from == '' )	$txt_bill_date_from = date("Y-m-01");
			if ($txt_bill_date_to == '' )	$txt_bill_date_to = date("Y-m-t");

			$query_where = '';
			if ($txt_search != '' ){
				$query_where .= " AND (	c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%'
										OR c.nric LIKE '%$txt_search%') ";
			}

			if ($sel_category != 'all' && !empty($sel_category)){
				$query_where .= "AND c.category = '$sel_category' ";
			}
			if ($sel_status != 'all' && !empty($sel_status)){
				$query_where .= "AND c.status = '$sel_status' ";
			}
			if (!empty($txt_bill_date_from)){
				$query_where .= " AND b.bill_date >= '".date("Y-m-d",strtotime($txt_bill_date_from))."'  " ;
			}
			if (!empty($txt_bill_date_to)){
				$query_where .= " AND b.bill_date <= '".date("Y-m-d",strtotime($txt_bill_date_to))."'  " ;
			}

			$this->load->model('xml_model');
			$this->xml_model->xml_invoice( $query_where ) ;

		}
	}


    public function export_bill()
    {
		$page_item_no 	= $this->input->post('page_item_no');
		$txt_search		= $this->input->post('txt_search');
		$sel_category 	= $this->input->post('sel_category');
		$sel_status 	= $this->input->post('sel_status');
		$txt_bill_date_from  = $this->input->post('txt_bill_date_from');
		$txt_bill_date_to    = $this->input->post('txt_bill_date_to');

		if ($page_item_no == '') $page_item_no = 0;
		if ( empty($sel_category)) $sel_category = 'r';
		if ( empty($sel_status)) $sel_status = 'r';
		if ($txt_bill_date_from == '') $txt_bill_date_from = date('Y-m-01');;
		if ($txt_bill_date_to == '') $txt_bill_date_to = date('Y-m-t');

		$query_where = '';
		if ($sel_category != 'all' && !empty($sel_category)) {
			$query_where = " AND c.category = '$sel_category' ";
		}
		if ($sel_status != 'all' && !empty($sel_status)) {
			$query_where = $query_where." AND c.status = '$sel_status' ";
		}

		if (!empty($txt_bill_date_from)){
			$query_where .= " AND b.bill_date >= '".date("Y-m-d",strtotime($txt_bill_date_from))."'  " ;
		}

		if (!empty($txt_bill_date_to)){
			$query_where .= " AND b.bill_date <= '".date("Y-m-d",strtotime($txt_bill_date_to))."'  " ;
		}

		$get_list 	= $this->bill_model->get_export_bill_list($txt_search,$page_item_no,$query_where);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		foreach ($row as $key => $val) {
			// $row[$key]['bill_date'] = convert_date_2($row[$key]['bill_date']);
			$row[$key]['bill_date'] = date_toggle($row[$key]['bill_date'],$_SESSION['config']['date_format']);
		}

		$data['page_title'] = 'Export Bills';
		$data['form_action'] = base_url('bill/export_bill');
		$data['row_data'] = $row;
		$data['txt_search'] = $txt_search;
		$data['sel_category'] = $sel_category;
		$data['sel_status'] = $sel_status;
		$data['txt_bill_date_from'] = $txt_bill_date_from;
		$data['txt_bill_date_to']   = $txt_bill_date_to;

		$data['sel_status_list'] = $this->common_model->get_acc_status_list();
		$data['sel_category_list'] = $this->common_model->get_category_list();

		// for paginations
		$data['pagination'] = paginationSettings('bill', $total_row);
		$data['msg'] = $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('bill/bill_export',$data);
		$this->load->view('templates/footer');
	}

    public function bill_manual()
    {
		$row_html = $this->bill_manual_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['sel_category'] = $post_data['sel_category'];
			$data['sel_status'] = $post_data['sel_status'];
			$data['sel_bill_by'] = $post_data['sel_bill_by'];
			$data['txt_bill_date'] = $post_data['txt_bill_date'];
		} else {
			$bill_manual_filter       = get_session_filter('bill_manual_filter');
			$data['page_item_no'] = $bill_manual_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $bill_manual_filter['txt_search'] ?? '';
			$data['sel_category'] = $bill_manual_filter['sel_category'] ?? 'all';
			$data['sel_status'] = $bill_manual_filter['sel_status'] ?? 'all';
			$data['sel_bill_by'] = $bill_manual_filter['sel_bill_by'] ?? 'all';
			$data['txt_bill_date'] = $bill_manual_filter['txt_bill_date'] ?? '';
		}

		$data['page_title'] = 'Manual Billing';
		$data['form_action'] = base_url('bill');

		$data['sel_status_list'] = $this->common_model->get_bill_status_list();
		$data['sel_category_list'] = $this->common_model->get_category_list();
		$data['bill_type'] = $this->bill_model->get_bill_type();
		$data['category_opt'] = " 0 : 'Please select ...' , ";
		foreach ($data['bill_type'] as $key => $value){
			$data['category_opt'] .= ($key+1) . ": \"" . $value['name'] . "\" , ";
		}

		$data['msg'] = $this->msg;
		$data['row_html'] = $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('bill/bill_manual_list',$data);
		$this->load->view('templates/footer');
	}

	function bill_manual_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$bill_manual_filter       = get_session_filter('bill_manual_filter');
			$post_data['page_item_no'] = $bill_manual_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $bill_manual_filter['txt_search'] ?? '';
			$post_data['sel_category'] = $bill_manual_filter['sel_category'] ?? 'all';
			$post_data['sel_status'] = $bill_manual_filter['sel_status'] ?? 'all';
			$post_data['sel_bill_by'] = $bill_manual_filter['sel_bill_by'] ?? 'all';
			$post_data['txt_bill_date'] = $bill_manual_filter['txt_bill_date'] ?? date('Y-m-t', strtotime('last day of this month'));
		}
		
		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['sel_category'])) $post_data['sel_category'] = 'all';

		if (!isset($post_data['sel_status'])) $post_data['sel_status'] = 'all';

		if (!isset($post_data['sel_bill_by'])) $post_data['sel_bill_by'] = 'all';

		if (!isset($post_data['txt_bill_date'])) $post_data['txt_bill_date'] = date('Y-m-t', strtotime('last day of this month'));

		//post values , must also run by session values
		$total_row = 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search = $post_data['txt_search'];
		$sel_category = $post_data['sel_category'];
		$sel_status = $post_data['sel_status'];
		$sel_bill_by = $post_data['sel_bill_by'];
		$txt_bill_date = $post_data['txt_bill_date'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_category' => $sel_category,
			'sel_status' => $sel_status,
			'sel_bill_by' => $sel_bill_by,
			'txt_bill_date' => $txt_bill_date,
		);
		set_session_filter('bill_manual_filter', $session_array);
		
		if ($page_item_no == '') $page_item_no = 0;
		if ( empty($sel_category)) $sel_category = 'all';
		if ( empty($sel_status)) $sel_status = 'all';
		if ($txt_bill_date == '') $txt_bill_date = '';

		$query_where = '';
		// if ($sel_category != 'all' && !empty($sel_category)) {
		// 	$query_where = "AND c.category = '$sel_category' ";
		// }
		if ($sel_status != 'all' && !empty($sel_status)) {
			if($sel_status == 'C') {
				$query_where .= "AND bs.status = 'C' AND b2.is_void = '0' ";
			}else if($sel_status == 'V') {
				$query_where .= "AND bs.status = 'C' AND b2.is_void = '1' ";
			}else{
				$query_where .= "AND bs.status = '$sel_status' ";
			}
		}
		// if ($sel_bill_by != 'all' && !empty($sel_bill_by)) {
		// 	if ($sel_bill_by == 'p') {
		// 		$query_where .= "AND c.bill_by_post = '1' ";
		// 	}
		// 	if ($sel_bill_by == 'e') {
		// 		$query_where .= "AND c.bill_by_email = '1' ";
		// 	}
		// }
		if (!empty($txt_bill_date)){
			$query_where .= " AND ( b.bill_date BETWEEN '".date("Y-m-01",strtotime($txt_bill_date))."'
									AND '".date("Y-m-d",strtotime($txt_bill_date))."' ) " ;
		}

		$get_list 	= $this->bill_model->get_manual_bill_list($txt_search,$page_item_no,$query_where);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		foreach ($row as $key => $val) {
			$row[$key]['activated_date'] = date_toggle($row[$key]['activated_date'],$_SESSION['config']['date_format']);
			$row[$key]['last_bill_date'] = date_toggle($row[$key]['last_bill_date'],$_SESSION['config']['date_format']);
			$row[$key]['status_icon'] = $this->createStatusIcon($row[$key]['status'], $row[$key]['is_void'] ?? 0);
			$row[$key]['row_style'] = $row[$key]['is_void'] == '1' ? 'red cross' : '';
		}

		// for paginations
		$data['pagination'] = paginationSettingsAjax('bill_draft', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('bill/bill_manual_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function bill_manual_detail($bill_draft_no = "") {
		$vars = $this->varSettings();
		$this->load->model('setting_model');
	
		$data = [
			'page_title' => "Manual Billing",
			'form_action' => base_url('bill/save_bill_manual'),
			'new_bill_no' => $this->bill_model->get_max_bill_no() + 1,
			'msg' => $this->msg,
			'js' => "var baseUrl = '" . base_url() . "' ;",
		];
	
		$bill_info = $this->bill_model->get_bill_manual_detail($bill_draft_no);
		$bill = $bill_info['bill_info'];
		$data['bill_info'] = $bill;
		$data['bill_type'] = $bill['bill_type'];
	
		// Set previous balance
		if ($bill['bill_status'] !== 'C' && $bill['customer_no']) {
			$last_bill = $this->bill_model->get_last_bill($bill['customer_no']);
			$data['bill_info']['previous_balance'] = $last_bill ? $last_bill['balance'] : 0;
		}
	
		$data['bill_dtl'] = $bill_info['bill_dtl'];
	
		// Build bill type options and JavaScript
		$bill_types = $this->setting_model->get_bill_type_listing();
		$bill_type_opt = ['0' => 'Please Select'];
		$tax_js = ["var tax = {};"];

		foreach ($bill_types['row'] as $type) {
			$id = (string)$type['bill_type_id'];
			$bill_type_opt[$id] = $type['name'];

			$tax[$id] = [
				'tax_code'    => $type['tax_code'],
				'tax_percent' => $type['percent'],
				'adjust_type' => $type['is_debit'] === 'Debit' ? 'dr' : 'cr',
				'plus_minus'  => $type['is_debit'] === 'Debit' ? '+' : '-',
			];
		}

		$data['bill_type_opt'] = json_encode($bill_type_opt, JSON_UNESCAPED_UNICODE);
		$data['bill_type_js'] = implode("\n", $tax_js);
	
		// Print buttons
		$bill_no = $bill['bill_no'] ?? '';
		if ($bill_no) {
			$data['print_invoice'] = "<a target='_blank' href='" . base_url("bill/print_invoice/no/$bill_no") . "'>
				<i class='menu-icon fa fa-files-o grey' " . tooltip_helper('Print Invoice') . "></i></a>";
			$data['print_bill'] = "<a target='_blank' href='" . base_url("bill/bill_statement/bill/$bill_no") . "'>
				<i class='menu-icon fa fa-print light-red' " . tooltip_helper('Print Bill Statement') . "></i></a>";
		} else {
			$data['print_invoice'] = '';
			$data['print_bill'] = '';
		}
	
		// Approvers and permissions
		$data['lvl1_approvers'] = $this->bill_model->get_sys_approvers(1);
		$data['lvl2_approvers'] = $this->bill_model->get_sys_approvers(2);
		
		if($data['bill_info']['idx'] === ''){
			$data['selected_lvl1_approver'] = array_column($data['lvl1_approvers'], 'user_id') ?? [];
			$data['selected_lvl2_approver'] = array_column($data['lvl2_approvers'], 'user_id') ?? [];
		}else{
			$data['selected_lvl1_approver'] = array_column($bill_info['doc_lvl_1_approver'], 'user_id') ?? [];
			$data['selected_lvl2_approver'] = array_column($bill_info['doc_lvl_2_approver'], 'user_id') ?? [];
		}
		

		if(count($data['lvl1_approvers']) == 0 && $bill['bill_status'] == 'A')
		{
			$data['msg']['error_msg'] = 'All Level 1 Approvers have been removed, so the manual bill status has been changed to Draft.';
			$data['bill_info']['bill_status'] = 'D';
			$bill['bill_status'] = 'D';
		}
		if(count($data['lvl2_approvers']) == 0 && $bill['bill_status'] == 'B')
		{
			$data['msg']['error_msg'] = 'All Level 2 Approvers have been removed, so the manual bill status has been changed to Draft.';
			$data['bill_info']['bill_status'] = 'D';
			$bill['bill_status'] = 'D';
		}
			
		$is_lvl1 = $bill['bill_status'] === 'A' && in_array($this->user['idx'], $data['selected_lvl1_approver']);
		$is_lvl2 = $bill['bill_status'] === 'B' && in_array($this->user['idx'], $data['selected_lvl2_approver']);
		$data['is_approver'] = ($is_lvl1 || $is_lvl2) ? 1 : 0;
	
		$can_edit = ($data['is_approver'] || $bill['bill_status'] !== 'C' || $bill['bill_status'] === 'D') ? 1 : 0;
		$data['js'] .= "\nvar canEdit = '$can_edit';";

		if(!empty($this->temp_form_data))
		{
			$temp_form_data = $this->temp_form_data;
			$data['bill_info']['bill_status'] = $temp_form_data['current_status'];
			$data['bill_type'] = $temp_form_data['bill_type'];
			$data['bill_info']['bill_draft_no'] = $temp_form_data['bill_draft_no'];
			$data['bill_info']['idx'] = $temp_form_data['idx'];
			$data['bill_info']['customer_no'] = $temp_form_data['customer_no'];
			$data['bill_info']['customer_name'] = $temp_form_data['customer_name'];
			$data['bill_info']['package_name'] = $temp_form_data['package_name'];
			$data['bill_info']['monthly_charge'] = $temp_form_data['monthly_charge'];
			$data['bill_info']['previous_balance'] = $temp_form_data['prev_balance'];
			$data['bill_info']['related_bill_no'] = $temp_form_data['related_bill_no'] ?? '';
			$data['bill_info']['reason'] = $temp_form_data['reason'] ?? '';

			$bill_dtl = [];
			if (!empty($temp_form_data['tblAppendGrid_rowOrder'])) {
				$rows = explode(',', $temp_form_data['tblAppendGrid_rowOrder']);
				foreach ($rows as $row) {
					$bill_dtl[] = [
						'idx'          => isset($temp_form_data["tblAppendGrid_idx_$row"]) ? $temp_form_data["tblAppendGrid_idx_$row"] : '',
						'bill_draft_no'=> isset($temp_form_data['bill_draft_no']) ? $temp_form_data['bill_draft_no'] : '',
						'tranx_date'   => isset($temp_form_data["tblAppendGrid_date_$row"]) ? $temp_form_data["tblAppendGrid_date_$row"] : '',
						'bill_type'    => isset($temp_form_data["tblAppendGrid_category_$row"]) ? $temp_form_data["tblAppendGrid_category_$row"] : '',
						'tax_code'     => isset($temp_form_data["tblAppendGrid_tax_code_$row"]) ? $temp_form_data["tblAppendGrid_tax_code_$row"] : '',
						'tax_percent'  => isset($temp_form_data["tblAppendGrid_tax_percent_$row"]) ? $temp_form_data["tblAppendGrid_tax_percent_$row"] : '',
						'amount'       => isset($temp_form_data["tblAppendGrid_charge_$row"]) ? $temp_form_data["tblAppendGrid_charge_$row"] : '',
						'tax_amount'   => isset($temp_form_data["tblAppendGrid_tax_amount_$row"]) ? $temp_form_data["tblAppendGrid_tax_amount_$row"] : '',
						'adj_type'     => isset($temp_form_data["tblAppendGrid_adjust_type_$row"]) ? $temp_form_data["tblAppendGrid_adjust_type_$row"] : '',
						'plus_minus'   => isset($temp_form_data["tblAppendGrid_plus_minus_$row"]) ? $temp_form_data["tblAppendGrid_plus_minus_$row"] : '',
						'remark'       => isset($temp_form_data["tblAppendGrid_desc_$row"]) ? $temp_form_data["tblAppendGrid_desc_$row"] : '',
						'total_amount' => isset($temp_form_data["tblAppendGrid_amount_$row"]) ? $temp_form_data["tblAppendGrid_amount_$row"] : '',
					];
				}
			}
			$data['bill_dtl'] = $bill_dtl;

			$data['selected_lvl1_approver'] = $temp_form_data['lvl1_approver'] ?? [];
			$data['selected_lvl2_approver'] = $temp_form_data['lvl2_approver'] ?? [];
			$this->temp_form_data = [];
		}

		$data['related_invoices'] = $this->bill_model->get_invoice_bill(" AND customer_no = '" . $data['bill_info']['customer_no'] . "'  AND is_void = 0");

		// Render views
		$this->load->view('templates/header', $vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('bill/bill_manual_detail', $data);
		$this->load->view('templates/footer');
	}
	

	function save_bill_manual(){
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'bill/bill_manual', 'error_keys' => array());
		
		$this->temp_form_data = $this->input->post();
		if ($this->input->post('btDelete') != '' && $this->input->post('payment_no') != '') { // DELETE
			//$this->payment_model->payment_delete($this->input->post('customer_no'),$this->input->post('payment_no'));
			$this->session->set_flashdata("msg", 'Bills deleted!');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'bill/bill_manual';
			echo json_encode($ajax_return);
			return false;
		}else {
			$extra = [];
			if($this->input->post('bill_type') != 'INV'){
				$extra[] = array('field' => 'related_bill_no', 'label' => 'Invoice Bill No', 'rules' => 'trim|required');
				$extra[] = array('field' => 'reason', 'label' => 'Reason', 'rules' => 'trim|required');
			}
			if(count($this->bill_model->get_sys_approvers(1)) > 0){
				$extra[] = array('field' => 'lvl1_approver', 'label' => 'Level 1 Approver', 'rules' => 'required');
			}
			if(count($this->bill_model->get_sys_approvers(2)) > 0){
				$extra[] = array('field' => 'lvl2_approver', 'label' => 'Level 2 Approver', 'rules' => 'required');
			}
			$this->set_form_validation('bill_manual_save', $extra);
			if($this->form_validation->run() == false) {
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}else{
				$data = $this->input->post();

				$rows = explode(",", $data['tblAppendGrid_rowOrder']);
				$data['tblAppendGrid_rowOrder'] = implode(',', array_filter($rows, function($row) use ($data) {
					$key = 'tblAppendGrid_category_' . $row;
					return isset($data[$key]) && $data[$key] != 0;
				}));

				if($data['tblAppendGrid_rowOrder'] == '') {
					$ajax_return['err_msg'] = 'No item was selected';
				}

				if (!empty($ajax_return['err_msg'])) {
					echo json_encode($ajax_return);
					return false;
				} else {
					$current_status = $this->input->post('current_status');
					$task = $this->input->post('task');
					$lvl1_approval_exist = count($this->bill_model->get_sys_approvers(1)) > 0;
					$lvl2_approval_exist = count($this->bill_model->get_sys_approvers(2)) > 0;
					$bill_draft_no = $this->input->post('bill_draft_no');

					$data['changed_status'] = $current_status;
					if($task === 'btSendForReview') {
						$data['changed_status'] = $lvl1_approval_exist ? 'A' : ($lvl2_approval_exist ? 'B' : 'C');
					} else if($task === 'btApprove') {
						$data['changed_status'] = $current_status == 'A' ? ($lvl2_approval_exist ? 'B' : 'C') : 'C';
					} else if($task === 'btReject') {
						$data['changed_status'] = 'D';
					} else if($task === 'btComplete') {
						$data['changed_status'] = 'C';
					}
					
					$data['created_by'] = $this->user['idx'];

					$this->bill_model->save_bill_manual($data);

					if($task === 'btReject') {
						$this->send_message_to_manual_billing_owner($bill_draft_no, 'REJECTED', 'rejected');
					}else if($data['changed_status'] == 'A' && $task != 'btSave') {
						$this->send_message_to_approver($bill_draft_no, 1);
					}else if($data['changed_status'] == 'B' && $task != 'btSave') {
						$this->send_message_to_approver($bill_draft_no, 2);
						if($current_status == 'A') {
							$this->send_message_to_manual_billing_owner($bill_draft_no, 'APPROVED LVL1', 'approved LVL1');
						}
					}else if($data['changed_status'] == 'C' && $task != 'btSave') {
						$this->send_message_to_manual_billing_owner($bill_draft_no, 'COMPLETED', $current_status == 'A' ? 'approved LVL1' : 'approved LVL2');
					}

					$msg = empty($this->input->post('bill_draft_no')) ? 'Manual bill inserted!' : 'Manual bill updated!';
					$this->session->set_flashdata("msg", $msg);
					$ajax_return['status'] = 'SUCC';
					$ajax_return['url'] = 'bill/bill_manual';
					echo json_encode($ajax_return);
				}
			}
		}
	}

	private function send_message_to_approver($bill_draft_no, $level = 1)
	{
		// PREPARE CONTACTS
		$bill = $this->bill_model->get_bill_manual_detail($bill_draft_no);
		$user_list = $this->common_model->get_user_list();
		$approvers = $this->bill_model->get_sys_approvers($level);

		$user_email_list = array_column($user_list, 'email', 'idx');
		$user_mobile_list = array_column($user_list, 'mobile_no', 'idx');
		$user_telegram_list = array_column($user_list, 'telegram_id', 'idx');

		$selected_approver = array_column($bill[$level == 1 ? 'doc_lvl_1_approver' : 'doc_lvl_2_approver'], 'user_id') ?? [];
		$allow_whatsapp_approver = array_filter(array_column($approvers, 'allow_whatsapp', 'user_id'), fn($v) => $v == 1);
		$allow_telegram_approver = array_filter(array_column($approvers, 'allow_telegram', 'user_id'), fn($v) => $v == 1);

		$emails = array_intersect_key($user_email_list, array_flip($selected_approver));
		$whatsapps = array_intersect_key($user_mobile_list, $allow_whatsapp_approver);
		$telegrams = array_intersect_key($user_telegram_list, $allow_telegram_approver);

		$contacts = [];
	
		foreach ([
			'email'    => $emails,
			'whatsapp' => $whatsapps,
			'telegram' => $telegrams,
		] as $type => $values) {
			foreach (array_filter($values) as $value) {
				$contacts[] = [
					'type'  => $type,
					'value' => $value,
				];
			}
		}

		// PREPARE NOTIFICATION
		$header = "[REQUESTING APPROVAL LEVEL {$level}]";

		$body = "From: {$this->config->item('proj_name')}" .
			"<br>Customer: {$bill['bill_info']['customer_name']}" .
			"<br>Manual Billing No: {$bill['bill_info']['bill_draft_no']}" .
			"<br>Please view and approve Manual Billing #{$bill['bill_info']['bill_draft_no']} with the link below." .
			"<br>" . base_url('bill/bill_manual_detail/' . $bill['bill_info']['bill_draft_no']);

		$whatsapp_template_name = 'MANUAL BILL APPROVAL';

		// QUEUE NOTIFICATION
		$this->load->library('notification_service');

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			$whatsapp_template_name,
			[
				'approval_level' => !empty($level) ? $level : '-',
				'customer_name' => !empty($bill['bill_info']['customer_name']) ? $bill['bill_info']['customer_name'] : 'N/A',
				'bill_draft_no' => !empty($bill['bill_info']['bill_draft_no']) ? $bill['bill_info']['bill_draft_no'] : '-',
			],
			$bill['bill_info']['customer_no'] ?? '',
			[
				'controller'    => 'bill',
				'doc_id'        => $bill_draft_no,
				'user_id'       => $this->user['idx'],
				'send_method'   => 'manual',
				'doc_type'      => '[Manual Billing Approval]',
			]
		);
	}

	private function send_message_to_manual_billing_owner($bill_draft_no, $changed_status, $action)
	{
		$bill = $this->bill_model->get_bill_manual_detail($bill_draft_no);

		$user = $this->common_model->get_table(
			'user',
			'email, mobile_no, telegram_id, allow_whatsapp, allow_telegram',
			'idx = ' . ($bill['bill_info']['created_by'] ?? 0)
		);

		if (empty($user)) {
			return;
		}

		$user = $user[0];

		// PREPARE CONTACTS
		$contacts = [];

		if (!empty($user['email'])) {
			$contacts[] = [
				'type'  => 'email',
				'value' => $user['email'],
			];
		}

		if (!empty($user['mobile_no']) && $user['allow_whatsapp'] == 1) {
			$contacts[] = [
				'type'  => 'whatsapp',
				'value' => $user['mobile_no'],
			];
		}

		if (!empty($user['telegram_id']) && $user['allow_telegram'] == 1) {
			$contacts[] = [
				'type'  => 'telegram',
				'value' => $user['telegram_id'],
			];
		}

		// PREPARE NOTIFICATION
		$header = "[MANUAL BILLING {$changed_status}]";

		$body = "From: {$this->config->item('proj_name')}" .
			"<br>Customer: {$bill['bill_info']['customer_name']}" .
			"<br>Document: Manual Billing #{$bill['bill_info']['bill_draft_no']}" .
			"<br>This document has been {$action} by {$this->user['display_name']}" .
			"<br>" . base_url('bill/bill_manual_detail/' . $bill['bill_info']['bill_draft_no']);

		$whatsapp_template_name = 'MANUAL BILL STATUS';

		// QUEUE NOTIFICATION
		$this->load->library('notification_service');

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			$whatsapp_template_name,
			[
				'status' => !empty($changed_status) ? $changed_status : '-',
				'customer_name' => !empty($bill['bill_info']['customer_name']) ? $bill['bill_info']['customer_name'] : 'N/A',
				'bill_draft_no' => !empty($bill['bill_info']['bill_draft_no']) ? $bill['bill_info']['bill_draft_no'] : '-',
				'performed_by' => !empty($this->user['display_name']) ? $this->user['display_name'] : 'N/A',
			],
			$bill['bill_info']['customer_no'] ?? '',
			[
				'send_type'     => 'custom',
				'user_id'       => $this->user['idx'],
				'controller'    => 'bill',
				'doc_id'        => $bill_draft_no,
				'send_method'   => 'manual',
				'doc_type'      => '[Manual Billing Status]',
			]
		);
	}

	private function queue_approval_notice( array $contact_list = [], $title, $msg, $template_name = '', $template_variable = [], $customer_no = '' ) {

		if (empty($contact_list) || empty($template_name)) {
			return false;
		}

		$this->load->model('email_model');
		$this->load->model('message_scheduler_model');
		$this->load->library('whatsapp_template');

		$smtp_user = $this->common_model->get_table(
			'sys_config',
			'*',
			"`category` = 'email' AND `key` = 'from_name'"
		);

		$from_name = $smtp_user[0]['val'] ?? 'no_reply@itelco.net';

		$meta_template = $this->whatsapp_template->build($template_name, $template_variable);

		foreach ($contact_list as $contact) {

			$type  = $contact['type'];
			$value = $contact['value'];

			if ($type === 'email') {

				$this->email_model->add_email_schedule([
					'recipient_emails'   => $value,
					'scheduler_id'       => '',
					'send_by'            => '',
					'email_title'        => $title,
					'email_msg'          => $msg,
					'email_cust_status'  => 1,
					'email_attachment'   => '',
					'email_schedule_on'  => date('Y-m-d H:i:s')
				]);

				continue;
			}

			$list_key = $type . '_list';

			$send_array = [
				'send_type'      => 'custom',
				'acc_id'         => 0,
				'customer_no'    => $customer_no,
				'user_id'        => 0,
				'controller'     => 'bill',
				'doc_id'         => 0,
				'send_method'    => 'auto',
				'acc_name'       => 'Itelco User',
				'subject'        => $type === 'whatsapp' ? '[FOLLOW WHATSAPP META TEMPLATE]' : $title,
				'body'           => $type === 'whatsapp' ? '[FOLLOW WHATSAPP META TEMPLATE]' : $msg,
				'from'           => $from_name,
				'email_starter'  => '',
				'doc_type'       => "[$template_name]",
				$list_key        => [$value],
				'meta_template'  => $meta_template['meta_template_name'] ?? '',
				'meta_vars'      => $meta_template['meta_variable'] ?? []
			];

			$message_data = [
				'message'         => $template_name,
				'msg_type'        => $type,
				'msg_to'          => $value,
				'customer_no'     => $customer_no,
				'msg_schedule_on' => date('Y-m-d H:i:s')
			];

			$this->message_scheduler_model->insert_new_scheduled_message($message_data, $send_array);
		}
	}

	private function varSettings(){
		
		$vars=array_merge($this->vars,array(
			'cssfiles' => array(
				base_url('css/jquery-ui-theme/black-tie/jquery-ui.css'),
				base_url('css/appendGrid/jquery.appendGrid-1.6.2.css'),
			),
			'jsfiles' => array(
				'js/jquery-ui.js',
				'js/appendGrid/jquery.appendGrid-1.6.2.js',				
				"js/bootstrap-datepicker.js"
			),
			'jscripts' => array(
				'var base_url="'.$this->config->item('base_url').'";',
			),
			
			)
		);
        return $vars;
	}

	private function createStatusIcon($status, $is_void) {
		if ($is_void == 1) return "<span class='badge badge-danger'>Void</span>";

		switch ($status) {
			case 'A':
				return "<span class='badge badge-info'>Pending 1</span>";
			case 'B':
				return "<span class='badge badge-primary'>Pending 2</span>";
			case 'C':
				return "<span class='badge badge-success'>Completed</span>";
			case 'D':
				return "<span class='badge badge-secondary'>Draft</span>";
			default:
				return "<span class='badge badge-light'>Unknown</span>";
		}
	}

	public function check_bill_file_exist() {
		$ajax_return = ['success' => 'ER'];
		$bill_file_name = $this->input->post('bill_file_name');

		if(!empty($bill_file_name) && file_exists($this->config->item('upload_path').'/temp/pdf/'.$bill_file_name)) {
			$ajax_return['success'] = 'SUCC';
		}

		echo json_encode($ajax_return);
	}
 }
