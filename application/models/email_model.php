<?php

class Email_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}

	function generate_html_email( $title = '', $body = '', $attachments = '', $emails = '' , $email_name ='Reminder', $recipient_name='', $header='', $full_path_attachment = '' ) {

		$this->load->model('common_model');
		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$company_email = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN (
				'isp_name'
			)"
		);

		$config_values = array_column($config_data, 'val', 'key');

		//load doc template view
		$content = array();
		$content['title'] = $header;
		$content['recipient_name'] = $recipient_name;
		$content['email_content'] = $body;
		$content['sender_name'] = $email_name;
		$content['comp_logo'] = $this->config->item('base_url').'images/'.$this->config->item('comp_logo');
		$content['cid'] = $this->config->item('comp_logo');
		$content['logo_css'] = $this->config->item('logo_css') ?? '';
		$content['company_email'] = $company_email;

		$content['isp_name'] = $config_values['isp_name'] ?? '';

		$html_body = '';

		$html_body = $this->load->view('templates/email_html_template',$content, TRUE	);

		//replace variables into view

		//run regular generate_email function
		//echo $html_body; exit;
		$res = $this->generate_email($title, $html_body, $attachments, $emails, $email_name, '', $full_path_attachment);
		return $res;

	}
	
	function generate_email($title = '', $body = '', $attachments = '', $emails = '' , $email_name ='penangFon',$this_error_msg = '', $full_path_attachment = '')
    {

		$test_email_to = ''; // leave it empty if go live
		
		$config_query	= "`category` = 'email'";
		$table_config	= $this->common_model->get_table('sys_config','*', $config_query);	
		
		$email_settings = array();
		foreach ($table_config as $tc_key => $t_arr)
		{
			$email_settings[$t_arr['key']] = $t_arr['val'];
			if(empty($email_settings[$t_arr['key']]))
			{
				$msg =  '- email configurations is not properly set.';			 
				$this_error_msg .= $msg.'<br>';				
				log_message('error', $msg);
			}
		}

		$email_newline_setting = $this->config->item('email_newline_setting');
		
		// preset
		$config['charset'] 			= 'utf-8'; 
		$config['newline'] 			= (!empty($email_newline_setting) ? $email_newline_setting : "\r\n"); 
		
		//pull from db
		$config['protocol'] 		= $email_settings['protocol'];  //eg 'smtp';
		$config['smtp_host'] 		= $email_settings['smtp_host']; //eg 'ssl://smtp.gmail.com'; 
		$config['smtp_port'] 		= $email_settings['smtp_port']; //eg '465';
		$config['mailtype'] 		= $email_settings['mailtype'];  //eg 'html';
		$config['smtp_user'] 		= $email_settings['smtp_user']; //eg 'alex@infonal.com.my';
		$config['smtp_pass'] 		= $email_settings['smtp_pass'];	//eg 'password';
		$config['smtp_crypto']		= $email_settings['smtp_crypto']; // ssl , tls
		$config['smtp_timeout'] 	= 30;
		$emails = trim( $emails );
		
		//check if email/emails is valid
		if(!is_array($emails))
		{
			if (!filter_var($emails, FILTER_VALIDATE_EMAIL)) 
			{
				$msg = 'This email ('.$emails.') is not valid.';
				$this_error_msg .= $msg.'<br>';
				log_message('error', $msg);
			}
		}
		elseif(is_array($emails))		
		{
			$invalid_emails = '';
			foreach ($emails as $e_key => $e_val)
			{
				if (!filter_var($e_val, FILTER_VALIDATE_EMAIL))
				{
					$invalid_emails .= '- '.$e_val.'<br>';
					unset($emails[$e_key]);
				}				
			}
			// OPTIONAL : throw error message if some email is invalid					
			//~ if($invalid_emails != '')
			//~ {				
				//~ $msg =  'the following emails is not valid:<br>'.$invalid_emails ;	 
				//~ $this_error_msg .= $msg.'<br>';				
				//~ log_message('error', $msg);
			//~ }
		}
		
		if(empty($emails))
		{			
			$msg = 'Unable to send. There is no email list.';
			$this_error_msg .= $msg.'<br>';				
			log_message('error', $msg);
		}				
		if($title == '')
		{
			$msg = 'Unable to send. Subject of the email must be specified.';
			$this_error_msg .= $msg.'<br>';				
			log_message('error', $msg);
		}
		
		//~ if($body == '') 
		//~ {			
			//~ $msg = 'unable to send. Body of the email must be specified.';
			//~ $this_error_msg .= $msg.'<br>';				
			//~ log_message('error', $msg);
		//~ }
		if(empty($test_email_to)) $email_list = $emails;
		else $email_list = $test_email_to;
		
		$ci = get_instance();
		$ci->load->library('email');
		$ci->email->initialize($config);
		$ci->email->clear(TRUE); 
		$ci->email->from($config['smtp_user'], $email_name);		
		$ci->email->to($email_list);
		$ci->email->subject($title);	
		
		if(!empty($attachments))
		{
			//attachment change to full path so can get file from anywhere
			$fullpath = $this->config->item('proj_path').$email_settings['set_attachment_at'].'/'.$attachments;	
			if(file_exists($fullpath))
			{
				$this->email->attach($fullpath);
			}
			else 
			{
				$msg = 'attachment not exist: '.$attachments ; //$fullpath.' not exist!<br>';				
				$this_error_msg .= $msg.'<br>';				
				log_message('error', $msg);
			}
		}

		if (!empty($full_path_attachment)) 
		{
			if(is_array($full_path_attachment)) {
				foreach ($full_path_attachment as $file) {
					if(file_exists($file))
					{
						$this->email->attach($file);
					}
					else 
					{
						$msg = 'attachment not exist: '.$full_path_attachment ; //$fullpath.' not exist!<br>';				
						$this_error_msg .= $msg.'<br>';				
						log_message('error', $msg);
					}
				}
			} else {
				if(file_exists($full_path_attachment))
				{
					$this->email->attach($full_path_attachment);
				}
				else 
				{
					$msg = 'attachment not exist: '.$full_path_attachment ; //$fullpath.' not exist!<br>';				
					$this_error_msg .= $msg.'<br>';				
					log_message('error', $msg);
				}
			}
		}
        
        //company logo
        $company_logo = $this->config->item('proj_path').'images/'.$this->config->item('comp_logo');
        $logo_css = $this->config->item('logo_css') ?? '';
        if (file_exists($company_logo)) {
        	$ci->email->attach($company_logo);
        	$cid = $this->email->attachment_cid($company_logo);

        	$image_html = '<img src="cid:'.$cid.'" style="'.$logo_css.'" alt="'.$cid.'">';

        	$body = str_replace("%%CID_LOGO%%", $image_html, $body);
        } else {
        	$body = str_replace("%%CID_LOGO%%", '', $body);
        }
		$ci->email->message($body);	
		
		if(empty($this_error_msg))
		{		
			if($ci->email->send())
			{					
				return 1;								
			}
			else		
			{
				$arr[] = 0;
				$arr[] = $this->email->print_debugger();
				return $arr;
			}
		}
		else
		{
			return $this_error_msg;
		}
	}
	
	function customer_update_last_email_sent($customer_no,$date)
	{		
		$query_str = "UPDATE customer SET last_email_sent = '". $date ."' WHERE customer_no = '" . $customer_no . "' ";
		$this->db->query($query_str);
	}
	function customer_update_flag_email($customer_no,$flag_no)
	{
		$query_str = "UPDATE customer SET `flag_email` = '".$flag_no."' WHERE customer_no = '" . $customer_no . "' ";
		$this->db->query($query_str);
	}
	function customer_update_last_file_gen($attachments,$customer_no)
	{
		//~ $attachments = $filename.'.pdf';					
		$query_str = "UPDATE customer SET last_file_gen = '".$attachments."' WHERE customer_no = '" . $customer_no . "' ";
		$this->db->query($query_str);
	}
	
	function set_attachment_at()
	{
		$query_str	= "SELECT * FROM `sys_config` " .
					"WHERE `key` = 'set_attachment_at'";
		$result		= $this->db->query($query_str)->result_array();	
		return (empty($result[0]['val']))?'':$result[0]['val'];
	}
	
	function get_customer_email_status($query_where,$txt_search,$page_item_no)
	{
		$txt_search = $this->db->escape_str($txt_search);
		$query_str= "SELECT count(*) as total_row FROM customer c 
						LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
						LEFT JOIN ( SELECT customer_no, status FROM customer_status cs1 WHERE cs1.status_id = ( SELECT MAX(cs2.status_id) FROM customer_status cs2 WHERE cs2.customer_no = cs1.customer_no ) ) cs ON cs.customer_no = c.customer_no " .
					"WHERE (c.customer_no LIKE '%$txt_search%' OR p.pic_email_1 LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR p.icno LIKE '%$txt_search%') ".
					$query_where;
		// if($_POST){ echo $query_str; exit();}
		$query = $this->db->query($query_str);	
			
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$query = $this->db->query("SELECT c.customer_no, c.name, IF(c.activated_date = 0, '', c.activated_date) as activated_date, c.package_name, c.monthly_charge, 
									c.name as customer_name,
									c.flag_email,
									p.pic_email_1 AS email_1, 
									p.pic_email_2 AS email_2, 
									c.bill_by_email,
									c.last_email_sent, 
									c.last_file_gen, 
									cs.status,
									sas.name as status, 
									scc.name as category 
									FROM customer c 
									INNER JOIN sys_account_status sas ON c.status = sas.status_code 
									INNER JOIN sys_customer_category scc ON c.category = scc.category_code 
									LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
									LEFT JOIN (
										SELECT 
											customer_no, 
											status
										FROM customer_status cs1
										WHERE cs1.status_id = (
											SELECT MAX(cs2.status_id)
											FROM customer_status cs2
											WHERE cs2.customer_no = cs1.customer_no
										)
									) cs ON cs.customer_no = c.customer_no
									WHERE (c.customer_no LIKE '%$txt_search%' OR p.pic_email_1 LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR c.nric LIKE '%$txt_search%') 
									$query_where 
									ORDER BY c.customer_no  " . 
									"LIMIT $page_item_no, ".$_SESSION['config']['max_page_item']);
			
		$row = array();					
		if ($query->num_rows() > 0)	{
			$row = $query->result_array();	
			foreach($row as $rkey => $r_arr){
				$row[$rkey]['flag_email'] = flag_email_icon($row[$rkey]['flag_email']);
				$row[$rkey]['bill_by_email'] = convert_boolean_to_words($row[$rkey]['bill_by_email']);
			}		
		}
		$return_val['row'] = $row;
		return $return_val;
	}
	
	function get_scheduled_email_list($txt_search='', $date_from='', $date_to='', $page_item_no=0, $row_per_page='')
	{
		$txt_search = $this->db->escape_str($txt_search);
		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$qwhere = "";
		if( $date_from != '' )
			$qwhere .= " AND e.email_schedule_on >= '".date('Y-m-d 00:00:00' , strtotime( $date_from ) )."' ";
		
		if( $date_to != '' )
			$qwhere .= " AND e.email_schedule_on <= '".date('Y-m-d 23:59:59' , strtotime( $date_to ) )."' ";
		
		if( $row_per_page == '' ) 
			$row_per_page = $_SESSION['config']['max_page_item'];
		
		
		$query_str = "SELECT count(e.scheduler_id) AS total_row 
						FROM email_scheduler e 
						WHERE e.email_title LIKE '%".$txt_search."%' $qwhere ";
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query_str = "	SELECT e.* , 
								IF( e.modified_date = NULL , e.created_date , e.modified_date ) AS modified_date
						FROM email_scheduler e
						WHERE e.email_title LIKE '%".$txt_search."%'  $qwhere
						ORDER BY e.email_schedule_on DESC, e.scheduler_id DESC
						LIMIT ".$page_item_no." , ". $row_per_page ;
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}
	
	function get_email_scheduler_detail( $scheduler_id ){
		
		$return_val = array();

		$query_str = "	SELECT e.*, o.email_status, o.email_attempt 
						FROM email_scheduler e
						LEFT JOIN email_outgoing o ON (e.scheduler_id = o.scheduler_id)
						AND o.email_attempt = (SELECT MAX(email_attempt) FROM email_outgoing 
						WHERE scheduler_id = e.scheduler_id) 
						WHERE e.scheduler_id = '".$scheduler_id."' 
						ORDER BY e.email_schedule_on DESC
						LIMIT 1";
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
		}
		else{
			//if all field default is "", can do like this
			//~ $fields = $this->db->list_fields($this->_table);			
			//~ foreach ($fields as $f_key => $f_data) $return_val[$f_data] = '';	
			$fields = $this->db->list_fields('email_scheduler');			
			foreach ($fields as $f_key => $f_data){ 
				$return_val[$f_data] = '';
			}
		}
		
		return $return_val;
		
	}
	
	function delete_email_scheduler( $scheduler_id ){
		$return_val = '0';
		
		$query_str = "DELETE FROM email_scheduler WHERE scheduler_id = '".$scheduler_id."' ;";
		
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'an email schedule (ID : ' . $scheduler_id .') has been deleted';
		$cust_no			= '';//$customer_no;
		$action_category 	= 'delete';
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);	
		
		$model	= 'sms_outgoing_model';
		$this->load->model($model);
		
		//delete email scheduler
		$this->db->query($query_str);
		if( $this->db->affected_rows() ){
			//delete outgoing
			$this->delete_email_outgoing( $scheduler_id );
			$return_val = 1 ; 
		}
		
		return $return_val;
	}
	
	function insert_bulk_email_outgoing( $scheduler_id, $email_str, $scheduled_time, $email_msg, $attachment ){
		
		$sql = " INSERT INTO email_outgoing 
				 ( 	`scheduler_id`,`email_scheduled_on`,`email_sent_on`,`email_to`,`email_msg`,`email_attempt`,
					`email_status`,`email_protocol`,`email_smtp_host`,`email_smtp_port`,`email_smtp_user`, `email_attachment` ) 
				 VALUES";
		
		$z = 0 ;
		$val = ""; 
		$email_str = str_replace( " " , "" , $email_str ) ;
		$email_str = str_replace( "\n" , "" , $email_str ) ;
		$email_arr = explode( ";" , $email_str );
		$email_arr = array_unique( $email_arr );

		foreach( $email_arr AS $email ){
			$val .= "( '".$scheduler_id."', '".$scheduled_time."', NULL, '".trim($email)."', '".$this->db->escape_str($email_msg)."', '0', 'P', '', '', '', '', '".$attachment."' ),";
			$z++;
		}
		
		$val = rtrim( $val, "," );
		$sql = $sql . $val ;
		$this->db->query( $sql );
		
	}
	
	function delete_email_outgoing( $scheduler_id ){
		$query_str = "DELETE FROM email_outgoing WHERE scheduler_id = '".$scheduler_id."' ;";
		$this->db->query($query_str);
	}

	// when select email scheduler as auto billing email 
	// it will regenerate the bill and resend it to the selected customer by building or area
	function auto_billing_email_scheduler($cust_info = [], $post_data) 
	{
		$failed_customer = [];
		if($post_data['send_by'] == 'individual') {
			$cust_info[] = ['customer_no' => $post_data['customer_no'],'pic_email_1' => $post_data['recipient_emails']];
		}

		foreach ($cust_info as $key => $cust) {
			$customer_no = $cust['customer_no'];

			// generate last bill
			$timestamp		= time();
			$date_stamp 	= date("Y-m-d_h:i:sa", $timestamp);
			$date 			= date("MY", $timestamp);
			$filename 		= '['.$row['bill_no'].']'.'bill_'.$customer_no;
			
			// initialize attachment
			$post_data['email_attachment'] = '';
			
			$this->load->model('bill_model');
			$row = $this->bill_model->get_last_bill( $customer_no );
			if(!empty($row['bill_no'])) {
				$genPDF = $this->bill_model->generate_bill_pdf( 'bill', $row['bill_no'], 1 , $filename );
				
				if($genPDF == 1)
				{
					$attachments = $filename.'.pdf';
					$this->email_model->customer_update_last_file_gen($attachments,$customer_no);

					$this->load->model('customer_model');
					$customer = $this->customer_model->get_customer($customer_no);

					// generate email text
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

					$post_data['email_attachment'] = $attachments;
				}
			} else {
				$failed_customer[] = $cust['customer_no'];
			}

			if( $post_data['scheduler_id'] == '' && !empty($post_data['email_attachment']) ) {

				$sql =  " INSERT INTO email_scheduler 
							SET email_title       	= '".$this->db->escape_str( $post_data['email_title'] )."' ,
								email_msg         	= '".$this->db->escape_str( $post_data['email_msg'] )."' ,
								email_schedule_on 	= '".$post_data['email_schedule_on']."' ,
								is_building       	= 0 ,
								is_individual       = 1 ,
								is_area		       	= 0 ,
								email_building    	= '' ,
								email_area	    	= '' ,
								email_to			= '".$cust['pic_email_1']."' ,
								email_cust_cat		= '',
								email_cust_status 	= '' , 
								email_attachment	= '".$this->db->escape_str( $post_data['email_attachment'] )."' ,
								created_by			= '' , 
								created_date 		= NOW() ; ";
			
				$this->db->query($sql);

				if( $this->db->affected_rows() ) {
				
					$scheduler_id = $this->db->insert_id();
					
					$this->insert_bulk_email_outgoing( 	$scheduler_id, 
														$cust['pic_email_1'],
														$post_data['email_schedule_on'],
														$post_data['email_msg'],
														$post_data['email_attachment']);
					
				}
			}
		}
		$post_data['failed_customer'] = $failed_customer;
		if(!empty($post_data['failed_customer'])) {
			log_message('error', "\nAuto Billing Failed Customer: \n" . print_r($post_data['failed_customer'], true));
		}
		return $post_data;
	}
	
	function add_email_schedule( $post_data ){

		$is_building = 0;
		$is_individual = 0;
		$is_area = 0;
		$is_all = 0;
		if ($post_data['send_by'] == 'building') {
			$is_building = 1;
		} else if ($post_data['send_by'] == 'area') {
			$is_area = 1;
		} else if ($post_data['send_by'] == 'all') {
			$is_all = 1;
		} else {
			$is_individual = 1;
		} 

		$email_building = "";
		$email_area = "";
		$email_to = "";
		$qwhere = "";
		$cust_info = [];
		if( $is_building == 1 ) {
						
			if( isset( $post_data['email_building'] ) && is_array( $post_data['email_building'] ) && !empty( $post_data['email_building'] ) ) {
				$email_building =  implode(", ", $post_data['email_building']) ;
				//get emails from building

				if(isset($post_data['email_cust_cat_building']) || isset($post_data['email_cust_status_building'])) {
					$post_data['email_cust_cat'] = $post_data['email_cust_cat_building'];
					$post_data['email_cust_status'] = $post_data['email_cust_status_building'];
				}
				
				$qwhere .= " AND c.building IN ( ". $email_building ." ) " ;
				
				if( $post_data['email_cust_cat'] != '' ){
					$email_cust_cat_str = "";
					$email_cust_cat = rtrim( $post_data['email_cust_cat'] , "," ) ;
					$email_cust_cat_str = "'" . str_replace( "," , "','" , $email_cust_cat ) . "'" ;
					$qwhere .= " AND c.category IN ( ".$email_cust_cat_str." ) ";
				}
				
				if( $post_data['email_cust_status'] != '' ){
					$email_cust_status_str = "";
					$email_cust_status = rtrim( $post_data['email_cust_status'] , "," ) ;
					$email_cust_status_str = "'" . str_replace( "," , "','" , $email_cust_status ) . "'" ;
					$qwhere .= " AND cs.status IN ( ".$email_cust_status_str." ) ";
				}
				
				$this->load->model('customer_model');
				$cust_info = $this->customer_model->filtered_customer("", $qwhere );

				foreach( $cust_info AS $row ){
					if( $row['pic_email_1'] != '' )
						$email_to .=  $row['pic_email_1'] . ";"  ;
						
					if( $row['pic_email_2'] != '' )
						$email_to .=  $row['pic_email_2'] . ";"  ;						
				}				
			}
		} else if ($is_individual == 1) {//get individual email
			$email_to = $post_data['recipient_emails'];

		} else if ($is_area == 1) {
			if( isset( $post_data['email_area'] ) && is_array( $post_data['email_area'] ) && !empty( $post_data['email_area'] ) ) {
				$email_area =  implode(", ", $post_data['email_area']) ;
				//get emails from area

				if(isset($post_data['email_cust_cat_area']) || isset($post_data['email_cust_status_area'])) {
					$post_data['email_cust_cat'] = $post_data['email_cust_cat_area'];
					$post_data['email_cust_status'] = $post_data['email_cust_status_area'];
				}

				$this->load->model('building_model');
				$building = $this->building_model->get_building_no_by_areas_id($email_area);
				$building_no_list = implode(",", array_column($building, 'building_no'));
				
				$qwhere .= " AND c.building IN ( ". $building_no_list ." ) " ;
				
				if( $post_data['email_cust_cat'] != '' ){
					$email_cust_cat_str = "";
					$email_cust_cat = rtrim( $post_data['email_cust_cat'] , "," ) ;
					$email_cust_cat_str = "'" . str_replace( "," , "','" , $email_cust_cat ) . "'" ;
					$qwhere .= " AND c.category IN ( ".$email_cust_cat_str." ) ";
				}

				if( $post_data['email_cust_status'] != '' ){
					$email_cust_status_str = "";
					$email_cust_status = rtrim( $post_data['email_cust_status'] , "," ) ;
					$email_cust_status_str = "'" . str_replace( "," , "','" , $email_cust_status ) . "'" ;
					$qwhere .= " AND cs.status IN ( ".$email_cust_status_str." ) ";
				}
				
				$this->load->model('customer_model');
				$cust_info = $this->customer_model->filtered_customer("", $qwhere );

				foreach( $cust_info AS $row ){
					if( $row['pic_email_1'] != '' )
						$email_to .=  $row['pic_email_1'] . ";"  ;
						
					if( $row['pic_email_2'] != '' )
						$email_to .=  $row['pic_email_2'] . ";"  ;
				}
			}
		} else if ($is_all == 1) {
			if(isset($post_data['email_cust_cat_all']) || isset($post_data['email_cust_status_all'])) {
				$post_data['email_cust_cat'] = $post_data['email_cust_cat_all'];
				$post_data['email_cust_status'] = $post_data['email_cust_status_all'];
			}

			if( $post_data['email_cust_cat'] != '' ){
				$email_cust_cat_str = "";
				$email_cust_cat = rtrim( $post_data['email_cust_cat'] , "," ) ;
				$email_cust_cat_str = "'" . str_replace( "," , "','" , $email_cust_cat ) . "'" ;
				$qwhere .= " AND c.category IN ( ".$email_cust_cat_str." ) ";
			}

			if( $post_data['email_cust_status'] != '' ){
				$email_cust_status_str = "";
				$email_cust_status = rtrim( $post_data['email_cust_status'] , "," ) ;
				$email_cust_status_str = "'" . str_replace( "," , "','" , $email_cust_status ) . "'" ;
				$qwhere .= " AND cs.status IN ( ".$email_cust_status_str." ) ";
			}
			
			$this->load->model('customer_model');
			$cust_info = $this->customer_model->filtered_customer("", $qwhere );

			foreach( $cust_info AS $row ){
				if( $row['pic_email_1'] != '' )
					$email_to .=  $row['pic_email_1'] . ";"  ;
					
				if( $row['pic_email_2'] != '' )
					$email_to .=  $row['pic_email_2'] . ";"  ;
			}
		}
		
		$email_to = rtrim( $email_to ,  ";" );

		if(isset($post_data['auto_attach_file']) && $post_data['auto_attach_file']) {
			$post_data = $this->auto_billing_email_scheduler($cust_info, $post_data);
		} else {
			//add
			if( $post_data['scheduler_id'] == '' ){	

				$sql =  " INSERT INTO email_scheduler 
							SET email_title       	= '".$this->db->escape_str( $post_data['email_title'] )."' ,
								email_msg         	= '".$this->db->escape_str( $post_data['email_msg'] )."' ,
								email_schedule_on 	= '".$post_data['email_schedule_on']."' ,
								is_building       	= '".$is_building."' ,
								is_individual       = '".$is_individual."' ,
								is_area		       	= '".$is_area."' ,
								is_all		       	= '".$is_all."' ,
								email_building    	= '".$email_building."' ,
								email_area	    	= '".$email_area."' ,
								email_to			= '".$email_to."' ,
								email_cust_cat		= '".($post_data['email_cust_cat'] ?? '')."',
								email_cust_status 	= '".$post_data['email_cust_status']."' , 
								email_attachment	= '".$this->db->escape_str( $post_data['email_attachment'] ?? '' )."' ,
								created_by			= '' , 
								created_date 		= NOW() ; ";
			
				$this->db->query($sql);

				if( $this->db->affected_rows() ){
				
					$scheduler_id = $this->db->insert_id();
					
					$this->insert_bulk_email_outgoing( 	$scheduler_id, 
														$email_to,
														$post_data['email_schedule_on'],
														$post_data['email_msg'],
														$post_data['email_attachment'] ?? '');
					
				}
			}else{//edit

				$sql =  "UPDATE email_scheduler 
							SET email_title       	= '".$post_data['email_title']."' ,
								email_msg         	= '".$this->db->escape_str( $post_data['email_msg'] )."' ,
								email_schedule_on 	= '".$post_data['email_schedule_on']."' ,
								is_building       	= '".$is_building."' ,
								is_individual       = '".$is_individual."' ,
								is_area		       	= '".$is_area."' ,
								is_all		       	= '".$is_all."' ,
								email_building    	= '".$email_building."' ,
								email_area	    	= '".$email_area."' ,
								email_to			= '".$email_to."' ,
								email_cust_cat		= '".$post_data['email_cust_cat']."', 
								email_cust_status 	= '".$post_data['email_cust_status']."' , 
								email_attachment	= '".$this->db->escape_str( $post_data['email_attachment'] )."' ,
								modified_by			= '' , 
								modified_date 		= NOW() 
						WHERE scheduler_id = '".$post_data['scheduler_id']."' ";
				$this->db->query($sql);
			
				if( $this->db->affected_rows() ){
					$this->delete_email_outgoing( $post_data['scheduler_id'] ) ;
					
					$this->insert_bulk_email_outgoing( 	$post_data['scheduler_id'], 
														$email_to,
														$post_data['email_schedule_on'],
														$post_data['email_msg'], 
														$post_data['email_attachment'] );
				}
			}
		}		
		return $post_data;	
	}
	
	function get_email_report( $date_from='', $date_to='', $scheduler_id='' ){
		
		$return_val = array();
		
		$qwhere = "";
		if( $date_from != '' )
			$qwhere .= " AND e.email_scheduled_on >= '".date( 'Y-m-d 00:00:00', strtotime( $date_from ) )."' ";
			
		if( $date_to != '' )
			$qwhere .= " AND e.email_scheduled_on <= '".date( 'Y-m-d 23:59:59', strtotime( $date_to ) )."' ";
			
		if( $scheduler_id != '' )
			$qwhere .= " AND scheduler.scheduler_id = '".$scheduler_id."' ";
		
		$sql = " 	SELECT	e.outgoing_id, e.scheduler_id, scheduler.email_title AS email_title, 
							e.email_scheduled_on, e.email_sent_on, e.email_to, e.email_msg, e.email_attempt, 
							e.email_status, e.email_protocol, e.email_smtp_host, e.email_smtp_port, 
							e.email_smtp_user 
					FROM email_outgoing e
					INNER JOIN email_scheduler scheduler ON e.scheduler_id = scheduler.scheduler_id
					WHERE 1=1 $qwhere ";
		$query = $this->db->query($sql);
		if( $query->num_rows() > 0 ) 
			$return_val = $query->result_array();
			
		return $return_val;	
		
	}
	
	function get_pending_outgoing_email(){
	
		$return_val = array();
	
		$sql = " SELECT o.*, s.email_title
				 FROM email_outgoing o
				 LEFT JOIN email_scheduler s ON o.scheduler_id = s.scheduler_id
				 WHERE UNIX_TIMESTAMP( o.email_scheduled_on ) <= UNIX_TIMESTAMP( NOW() ) 
				 AND o.email_status = 'P' AND o.email_attempt <= 3 ";
				
		$query = $this->db->query($sql);
		if( $query->num_rows() > 0 ) 
			$return_val = $query->result_array();
			
		return $return_val;	
		
	}
	
	function get_email_template_list(){
	
		$return_val = array();

		$query_str = "	SELECT e.template_id, e.template_name, e.email_title, e.is_default 
						FROM email_template e WHERE `status` = 1 ORDER BY e.is_default DESC , e.email_title ASC ";
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val = $query->result_array();
		}else{
			$fields = $this->db->list_fields('email_template');
			foreach ($fields as $f_key => $f_data){ 
				$return_val[$f_data] = '';
			}
		}
		
		return $return_val;
	
	}
	
	function get_email_template_detail( $qwhere ){
		
		$return_val = array();

		$query_str = "	SELECT e.* 
						FROM email_template e 
						WHERE 1 = 1 $qwhere
						ORDER BY e.template_id ";
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
		}
		else{
			$fields = $this->db->list_fields('email_template');
			foreach ($fields as $f_key => $f_data){ 
				$return_val[$f_data] = '';
			}
		}
		
		return $return_val;
		
	}
	
	
	function update_email_outgoing( $outgoing_id, $attempt, $status ){
		
		if( $status == 1 ){
			
			$smtp_host	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'smtp_host' ");

			$smtp_port	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'smtp_port' ");

			$smtp_user	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'smtp_user' ");
			
			$sql = " UPDATE email_outgoing 
						SET email_status = 'S' ,
							email_sent_on = NOW() ,
							email_protocol  = 'SMTP' ,
							email_smtp_host = '".$smtp_host[0]['val']."' ,
							email_smtp_port = '".$smtp_port[0]['val']."' ,
							email_smtp_user = '".$smtp_user[0]['val']."'
						WHERE outgoing_id = '".$outgoing_id."' ";
			
		}else{
			
			if( $attempt <= 3 ){

			$sql = " UPDATE email_outgoing SET email_status = 'P', email_attempt = email_attempt + 1 
					 WHERE outgoing_id = '".$outgoing_id."' ";
				
			}elseif( $attemp > 3 ){
				
			$sql = " UPDATE email_outgoing SET email_status = 'F', email_attempt = 4 
					 WHERE outgoing_id = '".$outgoing_id."' ";
				
			}
			
		}
		
		$query = $this->db->query($sql);
		
	}
	
	function add_email_template( $post_data ){
		
		if( $post_data['template_id'] == '' ){
			$sql =  " INSERT INTO email_template 
							SET template_name = '".$this->db->escape_str( $post_data['template_name'] )."',
								email_title	= '".$this->db->escape_str( $post_data['email_title'] )."' ,
								email_msg   = '".$this->db->escape_str( $post_data['email_msg'] )."' ,
								created_by	= '' , 
								created_date= NOW() ; ";
			
			$action_desc		= 'an email template ( ' . $this->db->escape_str( $post_data['template_name'] ) .') has been inserted';
			$action_category 	= 'insert';
								
		}else{
			$sql =  " UPDATE email_template 
							SET template_name = '".$this->db->escape_str( $post_data['template_name'] )."',
								email_title	= '".$this->db->escape_str( $post_data['email_title'] )."' ,
								email_msg   = '".$this->db->escape_str( $post_data['email_msg'] )."' ,
								modified_by	= '' , 
								modified_date= NOW() 
							WHERE template_id = '".($post_data['template_id'])."'
						";
			
			$action_desc		= 'an email template ( ' . $this->db->escape_str( $post_data['template_name'] ) .') has been updated';
			$action_category 	= 'update';
		}
		
			$model				= 'action_log_model';
			$this->load->model($model);
			$ctrl				= $this->router->fetch_class();
			$method 			= $this->router->method;
			$cust_no			= '';//$customer_no;
			$esc_query_str		= $this->db->escape_str($sql);
					
			$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
		
		$this->db->query($sql);
		if( $this->db->affected_rows() ){
			return 1 ;
		}else{
			return 0 ; 
		}

	}
	
	
	function delete_email_template( $template_id ){
		$return_val = '0';
		
		$query_str = "DELETE FROM email_template WHERE template_id = '".$template_id."' ;";
		
		$model				= 'action_log_model';
		$this->load->model($model);
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'an email template (ID : ' . $template_id .') has been deleted';
		$cust_no			= '';//$customer_no;
		$action_category 	= 'delete';
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);	
		
		//delete email template
		$this->db->query($query_str);
		
		if( $this->db->affected_rows() ){
			$return_val = 1 ; 
		}
		
		return $return_val;
	}
	
	function update_cron_email_status( $status = '1' ){	
		$query_str =" UPDATE `sys_config` SET `val` = '".$status."' 
						WHERE `key` = 'generate_monthly_bs' AND `category` = 'cron' ";

		$this->db->query($query_str);
	}
	
	function remove_email_attachment( $scheduler_id ){
		$query_str = " UPDATE email_scheduler SET email_attachment = '' WHERE scheduler_id = '".$scheduler_id."' ;" ; 
		$this->db->query($query_str);
		
		$query_str = " UPDATE email_outgoing SET email_attachment = '' WHERE scheduler_id = '".$scheduler_id."' ;" ; 
		$this->db->query($query_str);
	}
	
}
