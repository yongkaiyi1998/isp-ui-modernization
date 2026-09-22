<?php

class Ticket_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}


	function get_single_trouble_ticket( $tt_no='' ){
		
		$query_str 	= "SELECT tt.*, tt.tt_id AS temp_id, tt.tt_status AS prev_status,
						CASE WHEN tt.parent_tt_id IS NOT NULL AND tt.parent_tt_id != '' THEN ptt.tt_no ELSE '' END AS parent_tt_no,
						CASE WHEN tt.datetime_close = NULL THEN '' ELSE tt.datetime_close END AS datetime_close, comp.tt_complaint_name, sof.tt_sof_name, cof.tt_cof_name, c.profile_id, 
						CONCAT( c.customer_no , ' ' , c.name ) AS customer_no_name , u.username AS created_by_name, scc.name AS customer_category_name
					FROM trouble_ticket tt
					LEFT JOIN tt_sof sof ON tt.tt_sof_id = sof.tt_sof_id
					LEFT JOIN tt_cof cof ON tt.tt_cof_id = cof.tt_cof_id
					LEFT JOIN tt_complaint comp ON tt.tt_complaint_id = comp.tt_complaint_id
					LEFT JOIN trouble_ticket ptt ON tt.parent_tt_id = ptt.tt_id
					LEFT JOIN customer c ON tt.customer_no = c.customer_no 
					LEFT JOIN user u ON tt.created_by = u.idx
					LEFT JOIN sys_customer_category scc ON tt.customer_category = scc.category_code
					WHERE tt.tt_no = '".$this->db->escape_str( $tt_no )."'; " ;
		$query = $this->db->query($query_str);	
		
		if($query->num_rows() > 0){
			$return_val = $query->row_array();
		}else{
			$return_val['tt_id'] = '';
			$return_val['parent_tt_id'] = '';
			$return_val['parent_tt_no'] = '';
			$return_val['tt_no'] = '<< NEW >>';
			$return_val['tt_status'] = '1';
			$return_val['customer_no'] = '';
			$return_val['contact_no'] = '';
			$return_val['datetime_open'] = date('Y-m-d H:i:s');
			$return_val['datetime_close'] = '';
			$return_val['tt_duration'] = '';
			$return_val['tt_complaint_id'] = '';
			$return_val['tt_sof_id'] = '';
			$return_val['tt_cof_id'] = '';
			$return_val['tt_remark'] = '';
			$return_val['tt_assign_to'] = [];
			$return_val['tt_email'] = '';
			$return_val['created_on'] = '';
			$return_val['created_by'] = '';
			$return_val['created_by_name'] = '';
			$return_val['updated_on'] = '';
			$return_val['updated_by'] = '';
			$return_val['tt_sof_name'] = '';
			$return_val['tt_cof_name'] = '';
			$return_val['customer_no_name']	= '';
			$return_val['customer_category']	= '';
			$return_val['customer_category_name']	= '';
			$return_val['prev_status']	= '';
			$return_val['temp_id'] = rand(10000, 99999);
		}
		
		return $return_val;
		
	}
	
	function get_single_trouble_ticket_by_id( $tt_id='' ){
		
		$query_str 	= "SELECT tt.*, tt.tt_id AS temp_id, tt.tt_status AS prev_status,
						CASE WHEN tt.parent_tt_id IS NOT NULL AND tt.parent_tt_id != '' THEN ptt.tt_no ELSE '' END AS parent_tt_no,
						CASE WHEN tt.datetime_close = NULL THEN '' ELSE tt.datetime_close END AS datetime_close, comp.tt_complaint_name, sof.tt_sof_name, cof.tt_cof_name, c.profile_id,
						CONCAT( c.customer_no , ' ' , c.name ) AS customer_no_name , u.username AS created_by_name, scc.name AS customer_category_name
					FROM trouble_ticket tt
					LEFT JOIN tt_sof sof ON tt.tt_sof_id = sof.tt_sof_id
					LEFT JOIN tt_cof cof ON tt.tt_cof_id = cof.tt_cof_id
					LEFT JOIN tt_complaint comp ON tt.tt_complaint_id = comp.tt_complaint_id
					LEFT JOIN trouble_ticket ptt ON tt.parent_tt_id = ptt.tt_id
					LEFT JOIN customer c ON tt.customer_no = c.customer_no 
					LEFT JOIN user u ON tt.created_by = u.idx
					LEFT JOIN sys_customer_category scc ON tt.customer_category = scc.category_code
					WHERE tt.tt_id = '".$this->db->escape_str( $tt_id )."'; " ;
		$query = $this->db->query($query_str);	
		
		if($query->num_rows() > 0){
			$return_val = $query->row_array();
		}else{
			$return_val['tt_id'] = '';
			$return_val['parent_tt_id'] = '';
			$return_val['parent_tt_no'] = '';
			$return_val['tt_no'] = '<< NEW >>';
			$return_val['tt_status'] = '1';
			$return_val['customer_no'] = '';
			$return_val['contact_no'] = '';
			$return_val['datetime_open'] = date('Y-m-d H:i:s');
			$return_val['datetime_close'] = '';
			$return_val['tt_duration'] = '';
			$return_val['tt_complaint_id'] = '';
			$return_val['tt_sof_id'] = '';
			$return_val['tt_cof_id'] = '';
			$return_val['tt_remark'] = '';
			$return_val['tt_assign_to'] = '';
			$return_val['tt_email'] = '';
			$return_val['created_on'] = '';
			$return_val['created_by'] = '';
			$return_val['created_by_name'] = '';
			$return_val['updated_on'] = '';
			$return_val['updated_by'] = '';
			$return_val['tt_sof_name'] = '';
			$return_val['tt_cof_name'] = '';
			$return_val['customer_no_name']	= '';
			$return_val['customer_category']	= '';
			$return_val['customer_category_name']	= '';
			$return_val['prev_status']	= '';
			$return_val['temp_id'] = rand(10000, 99999);
		}
		
		return $return_val;
		
	}
	
	
	function get_trouble_ticket_listing($user_id='', $txt_search='',$page_item_no=0,$row_per_page='',$query_where='',$priority_sort='')
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = 0;		
		
		$qwhere="";
		if( $user_id != '' ){
			$qwhere .= " AND ( 	tt.created_by = '".$user_id."' 
								OR tt.tt_assign_to LIKE '%\"".$user_id."\"%' ) ";
		}
		
		if( $txt_search != '' ){
			$qwhere .= " AND (	tt.tt_no LIKE '%$txt_search%'
								OR tt.customer_no LIKE '%$txt_search%' 
								OR c.name LIKE '%$txt_search%' 
								OR p.pic_email_1 LIKE '%$txt_search%' 
								OR p.pic_name LIKE '%$txt_search%' ) ";
		}

		$sort_priority = '';
		if($priority_sort != '') {
			$sort_priority = "priority ASC, ";
		}
		
		$query_str 	= "	SELECT count(tt.tt_no) AS total_row 
						FROM trouble_ticket tt
						LEFT JOIN customer c on c.customer_no = tt.customer_no 
						LEFT JOIN profile p ON c.profile_id = p.acc_id 
						WHERE 1 = 1 " . $query_where . $qwhere ;
		
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		if( $row_per_page == '' )
			$row_per_page = $_SESSION['config']['max_page_item'];
		
		$query_str 	= "	SELECT tt.*, 
							   c.name AS pic_name , 
							   $priority_sort
							   CASE 
							   WHEN tt.tt_status = 1 THEN 'OPEN' 
							   WHEN tt.tt_status = 2 THEN 'ASSIGNED'
							   WHEN tt.tt_status = 3 THEN 'IN PROGRESS'
							   WHEN tt.tt_status = 4 THEN 'SOLVED'
							   WHEN tt.tt_status = 5 THEN 'NOT RELATED'
							   ELSE 'CLOSE' END AS tt_status,
							   CASE WHEN tt.datetime_close = NULL THEN '-' ELSE tt.datetime_close END AS datetime_close,
							   comp.tt_complaint_name, sof.tt_sof_name, cof.tt_cof_name, u.username AS prepared_by_name 
						FROM trouble_ticket tt 
						LEFT JOIN customer c ON c.customer_no = tt.customer_no 
						LEFT JOIN tt_sof sof ON tt.tt_sof_id = sof.tt_sof_id
						LEFT JOIN tt_cof cof ON tt.tt_cof_id = cof.tt_cof_id
						LEFT JOIN tt_complaint comp ON tt.tt_complaint_id = comp.tt_complaint_id 
						LEFT JOIN user u ON u.idx = tt.created_by  
						LEFT JOIN profile p ON c.profile_id = p.acc_id 
						WHERE 1 = 1 $query_where $qwhere 
						ORDER BY $sort_priority tt.created_on DESC, tt.tt_no DESC 
						LIMIT $page_item_no, $row_per_page ";
		$query = $this->db->query($query_str);
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ;
		return $return_val;
	}
		
	function get_new_tt_no(){
		
		$length = 14 ;
		$con = " SELECT `key`, `val` FROM sys_config 
					WHERE `key` = 'tt_no_length' AND `category` = 'ticket' ; " ;
		$config = $this->db->query($con);
		$config = $config->row_array();

		$con2 = " SELECT `key`, `val` FROM sys_config 
					WHERE `key` = 'tt_no_prefix' AND `category` = 'ticket' ; " ;
		$config2 = $this->db->query($con2);
		$config2 = $config2->row_array();
		
		$str = " SELECT * FROM trouble_ticket
				 WHERE DATE_FORMAT( created_on , '%Y-%m-%d' ) = '".date('Y-m-d')."'
				 ORDER BY created_on DESC LIMIT 1 ;" ;
		$query = $this->db->query($str);
		if( $query->num_rows() > 0 ){
			
			$tt = $query->row_array();
			$tt_prefix = substr( $tt['tt_no'], 0, $length-1 ) . "-" ;
			$tt_no = substr( $tt['tt_no'], $length-1 );
			$tt_suffix = str_pad( ( abs($tt_no) +1), $config['val'], 0, STR_PAD_LEFT);
			$tt_no = $tt_prefix . $tt_suffix ;
		}else{
			$tt_prefix = "1(".$config2['val'].")-" . date('dmY') . "-";
			$tt_suffix = str_pad( 1, $config['val'], 0, STR_PAD_LEFT);
			$tt_no = $tt_prefix . $tt_suffix ;
		}
				
		return $tt_no ; 
		
	}
	
	function get_tt_level( $level=1 , $product_code='' ){
		$return_val = array();
		$qwhere = "";
		if( $product_code != '' ){
			$qwhere .= " AND tt_product_code = '".$product_code."' ";
		}else{
			$qwhere .= " AND ( tt_product_code IS NULL OR tt_product_code = '' OR tt_product_code = 0 ) ";
		}
		
		$str = " SELECT * FROM tt_setting WHERE tt_level = '".$level."' $qwhere  ";
		$query = $this->db->query($str);
		
		if( $query->num_rows() > 0 ){
			$return_val = $query->row_array();
		}else{
			$return_val['tt_setting_id'] = '';
			$return_val['tt_level'] = '';
			$return_val['tt_pic'] = '';
			$return_val['tt_email'] = '';
			$return_val['tt_contact'] = '';
			$return_val['tt_interval'] = '0';
		}
		return $return_val;
	}

	function update_ticket_status($tt_id, $prev_status, $status, $prev_status_name, $status_name, $tt_sof_id='', $tt_cof_id='', $post_data) {
		$query_str = "UPDATE trouble_ticket SET tt_status = ?, tt_sof_id = ?, tt_cof_id = ? WHERE tt_id = ?";
		$query = $this->db->query($query_str,[$status,$tt_sof_id,$tt_cof_id,$tt_id]);

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method;

		$action_desc	= "the status of service ticket ($tt_id) has been changed from $prev_status_name to $status_name";
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($post_data['customer_no']));	

		if( $this->db->affected_rows() ){
			$return = 1 ;
		}else{
			$return = 0 ;
		}
		
		/**
		 * Push notification to the customer's mobile app.
		 */
		$this->notify_customer_of_status_change(
			$post_data,
			$prev_status,
			$status,
			$prev_status_name,
			$status_name
		);

		/**
		 * Save Job Tracking Details
		 * This will save the job tracking details and send notifications to the users involved.
		 */
		if (
			isset($post_data['new_comment'], $post_data['new_comment_date'], $post_data['new_comment_rec_id'], $post_data['reply_to']) &&
			!empty($post_data['new_comment'])
		) {
			$tt_id     = $post_data['tt_id'];
			$tt_no     = $post_data['tt_no'];
			$rec_id    = $post_data['new_comment_rec_id'];
			$date      = $post_data['new_comment_date'];
			$remark    = $post_data['new_comment'];
			$reply_raw = $post_data['reply_to'];
			$created_by = $this->user['idx'];

			$reply_to = array_filter($reply_raw);

			if (!empty($reply_to) && $reply_to[0] === 'all') {
				$reply_to = [];
			}

			$admin_list = $this->common_model->get_admin_list();
			$tt_assign_to = $post_data['tt_assign_to'];
			// $tt_assign_to = explode(",", str_replace( '"', '', $post_data['tt_assign_to'])) ?? [];

			$admins = array_column($admin_list, 'user_id');
			$all_user_ids = array_unique(array_map('intval', array_merge($reply_to, $admins)));

			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');

			$prepend = '';
			if (!empty($reply_to)) {
				$names = array_filter(array_map(fn($id) => $user_list[$id] ?? null, $reply_to));
				$prepend = $this->user['display_name'] . ' replied to ' . implode(', ', $names) . ":\n";
			}
			$comment_text = $prepend . $remark;

			$this->save_job_tracking_detail($tt_id, $rec_id, $comment_text, $created_by, $reply_to);

			$params = [
				'tt_id' => $tt_id,
				'tt_no' => $tt_no,
				'remark' => $remark,
				'date' => $date
			];

			$this->send_comment_notification($params, $tt_assign_to, $reply_to, 'service_ticket');
		}

		return $return;
	}

	/**
	 * Notify the customer (push + email + telegram + whatsapp) that their ticket's status has changed.
	 * Only fires for the statuses that should reach the customer: Assigned(2), In Progress(3), Solved(4), Not Related(5).
	 * Silent: Closed(0) and Opened(1).
	 * 
	 * @param array $post_data must contain tt_no, tt_id, customer_no
	 */
	public function notify_customer_of_status_change($post_data, $prev_status, $status, $prev_status_name = '', $status_name = '') {
		$notifiable_statuses = [2,3,4,5];

		if ($prev_status == $status || !in_array((int) $status, $notifiable_statuses, true)) {
			return;
		}

		$tt_no = $post_data['tt_no'];
		$tt_id = $post_data['tt_id'];
		$customer_no = $post_data['customer_no'];

		if ($status == 2) {
			$push_type = 'ticket_assigned';
			$template_name = 'SERVICE TICKET ASSIGNED - CUSTOMER';
		} elseif ($status == 5) {
			$push_type = 'ticket_not_related';
			$template_name = 'SERVICE TICKET NOT RELATED - CUSTOMER';
		} else {
			$push_type = 'ticket_status';
			$template_name = 'SERVICE TICKET STATUS UPDATE - CUSTOMER';
		}

		// --- Push Notification ---
		try {
			$this->load->library('push_service');

			$this->push_service->notify_customer(
				$customer_no,
				$push_type,
				array(
					'TT_NO' => $tt_no,
					'STATUS_CODE' => (string) $status,
				),
				array('related_id' => $tt_no)
			);
		} catch (Exception $e) {
			log_message('error', '[push] ticket status notification failed for tt_id ' . $tt_id . ': ' . $e->getMessage());
		}

		// --- Email + Telegram + WhatsApp ---
		$this->load->model('customer_model');
		$this->load->model('profile_model');
		$this->load->model('email_model');

		$customer = $this->customer_model->get_customer($customer_no);
		$notif_info = $this->profile_model->get_user_profile_notification_info($customer['profile_id']);

		$email_list = array_filter([$notif_info['acc_email'] ?? '']);
		$telegram_list = array_filter([$notif_info['acc_telegram'] ?? '']);
		$whatsapp_list = array_filter([$notif_info['acc_phone'] ?? '']);

		if (empty($email_list) && empty($telegram_list) && empty($whatsapp_list)) {
			return;
		}

		$email_template = $this->email_model->get_email_template_detail(
			"AND template_name = '{$template_name}' AND is_default = 1"
		);

		if (empty($email_template['email_title']) && empty($email_template['email_msg'])) {
			log_message('error', '[notify] missing email template "' . $template_name . '" for customer status notification, tt_id ' . $tt_id);
			return;
		}

		$replacements = [
			'%tt_no%' => $tt_no,
			'%prev_status_name' => $prev_status_name,
			'%status_name%' => $status_name,
		];

		$header = str_replace(array_keys($replacements), array_values($replacements), $email_template['email_title']);
		$body = str_replace(array_keys($replacements), array_values($replacements), $email_template['email_msg']);

		$this->load->library('notification_service');
		$contacts = $this->notification_service->buildContacts($email_list, $whatsapp_list, $telegram_list);

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			$template_name,
			[
				'tt_no' => !empty($tt_no) ? $tt_no : '-',
				'prev_status_name' => !empty($prev_status_name) ? $prev_status_name : '-',
				'status_name' => !empty($status_name) ? $status_name : '-',
			],
			$customer_no,
			[
				'controller' => 'ticket',
				'doc_id' => $tt_id,
				'send_method' => 'auto',
				'email_starter' => 'Service Ticket [' . $tt_no . '] status updated',
				'doc_type' => '[Service Ticket Status Update - Customer]',
			]
		);
	}
	
	function save_trouble_ticket( $post_data, $save_by ){
		
		if(!empty($post_data['tt_assign_to'])) { 
			if( is_array( $post_data['tt_assign_to'] ) ){
				$assign_to = '"' . implode( '","' , $post_data['tt_assign_to'] ) . '"' ;
			}else{
				$assign_to = "'" . $post_data['tt_assign_to'] . "'" ; 
			}
			$assign_to = '"'.$this->db->escape_str($assign_to).'"';
		} else {
			$assign_to = 'NULL';
		}

		$parent_tt_id = $post_data['parent_tt_id'];
		if (empty($parent_tt_id)) {
			$parent_tt_id = 0;
		}

		$tt_complaint_id = $post_data['tt_complaint_id'];
		if (empty($tt_complaint_id)) {
			$tt_complaint_id = 0;
		}

		$tt_sof_id = $post_data['tt_sof_id'];
		if (empty($tt_sof_id)) {
			$tt_sof_id = 0;
		}

		$tt_cof_id = $post_data['tt_cof_id'];
		if (empty($tt_cof_id)) {
			$tt_cof_id = 0;
		}

		$datetime_close = $post_data['datetime_close'];
		if (empty($datetime_close)) {
			$datetime_close = 'NULL';
		} else {
			$datetime_close = "'".$this->db->escape_str($post_data['datetime_close'])."'";
		}

		$isNew = false;
		if( $post_data['tt_id'] == '' ){
			//get new TT no
			$new_tt_no = $this->get_new_tt_no();
			
			//insert
			$str = " INSERT INTO trouble_ticket 
						SET parent_tt_id = ".$parent_tt_id." , 
							tt_no = '".$new_tt_no."' ,
							tt_status = '".$post_data['status']."' ,
							customer_no = '".$this->db->escape_str($post_data['customer_no'])."' ,
							customer_category = '".$this->db->escape_str($post_data['customer_category'])."' ,
							pic_name = '".$this->db->escape_str($post_data['pic_name'])."',
							contact_no = '".$this->db->escape_str($post_data['contact_no'])."' ,
							tt_category = '".$this->db->escape_str($post_data['tt_category'] ?? 0)."' ,
							datetime_open = '".$this->db->escape_str($post_data['datetime_open'])."' , 
							datetime_close = ".$datetime_close." , 
							tt_duration = '".$this->db->escape_str($post_data['duration'])."' , 
							tt_complaint_id =  '".$tt_complaint_id."' ,
							tt_sof_id =  '".$tt_sof_id."' ,
							tt_cof_id =  '".$tt_cof_id."' ,
							tt_assign_to =  ".$assign_to." ,
							tt_remark =  '".$this->db->escape_str($post_data['tt_remark'])."' ,
							tt_email  =  '".$this->db->escape_str($post_data['tt_email'] ?? NULL)."' ,
							created_by = '".$save_by."' ,
							created_on = NOW() ; ";
			$isNew = true;
			
		}else{
			//update
			$str = " UPDATE trouble_ticket 
						SET parent_tt_id = ".$parent_tt_id." , 
							tt_status = '".$post_data['status']."' ,
							customer_no = '".$this->db->escape_str($post_data['customer_no'])."' ,
							customer_category = '".$this->db->escape_str($post_data['customer_category'])."' ,
							pic_name = '".$this->db->escape_str($post_data['pic_name'])."',
							contact_no = '".$this->db->escape_str($post_data['contact_no'])."' ,
							tt_category = '".$this->db->escape_str($post_data['tt_category'] ?? 0)."' ,
							datetime_open = '".$this->db->escape_str($post_data['datetime_open'])."' , 
							datetime_close = ".$datetime_close." , 
							tt_duration = '".$this->db->escape_str($post_data['duration'])."' , 
							tt_complaint_id =  '".$tt_complaint_id."' ,
							tt_sof_id =  '".$tt_sof_id."' ,
							tt_cof_id =  '".$tt_cof_id."' ,
							tt_assign_to =  ".$assign_to." ,
							tt_remark =  '".$this->db->escape_str($post_data['tt_remark'])."' ,
							tt_email  =  '".$this->db->escape_str($post_data['tt_email'] ?? NULL)."' ,
							updated_by = '".$save_by."' ,
							updated_on = NOW() 
						WHERE tt_id = '".$post_data['tt_id']."' ; ";
		}
		
		$query = $this->db->query($str);

		if (empty($post_data['tt_id'])) {
			$post_data['tt_id'] = $this->db->insert_id();
		}

		//action log
		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($str);
		$method 		= $this->router->method;
		if($isNew) {
			$action_desc	= 'a new trouble ticket (' . $this->db->escape_str($post_data['tt_id']) . ') has been added';
			$action_category = 'insert';
		} else {
			$action_desc	= 'trouble ticket (' . $this->db->escape_str($post_data['tt_id']) . ') has been edited';
			$action_category = 'update';
		}
		$action_log =  $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category, $post_data['customer_no']);

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($post_data['customer_no']));

		if($isNew) {
			$this->save_job_tracking_detail($post_data['tt_id'], 0, 'Service Ticket Created', $save_by);
			$isNew = false;

			$query = $this->db->get_where('trouble_ticket', ['tt_id' => $post_data['tt_id']]);
			if ($query->num_rows() > 0) {
				$row = $query->row();
				$post_data['tt_no'] = $row->tt_no;
			}
		}

		/**
		 * Save Job Tracking Details
		 * This will save the job tracking details and send notifications to the users involved.
		 */
		if (
			isset($post_data['new_comment'], $post_data['new_comment_date'], $post_data['new_comment_rec_id'], $post_data['reply_to']) &&
			!empty($post_data['new_comment'])
		) {
			$tt_id     = $post_data['tt_id'];
			$tt_no     = $post_data['tt_no'];
			$rec_id    = $post_data['new_comment_rec_id'];
			$date      = $post_data['new_comment_date'];
			$remark    = $post_data['new_comment'];
			$reply_raw = $post_data['reply_to'];
			$created_by = $this->user['idx'];

			$reply_to = array_filter($reply_raw);

			if (!empty($reply_to) && $reply_to[0] === 'all') {
				$reply_to = [];
			}

			$admin_list = $this->common_model->get_admin_list();
			$tt_assign_to = $post_data['tt_assign_to'];
			// $tt_assign_to = explode(",", str_replace( '"', '', $post_data['tt_assign_to'])) ?? [];

			$admins = array_column($admin_list, 'user_id');
			$all_user_ids = array_unique(array_map('intval', array_merge($reply_to, $admins)));

			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');

			$prepend = '';
			if (!empty($reply_to)) {
				$names = array_filter(array_map(fn($id) => $user_list[$id] ?? null, $reply_to));
				$prepend = $this->user['display_name'] . ' replied to ' . implode(', ', $names) . ":\n";
			}
			$comment_text = $prepend . $remark;

			$this->save_job_tracking_detail($tt_id, $rec_id, $comment_text, $created_by, $reply_to);

			$params = [
				'tt_id' => $tt_id,
				'tt_no' => $tt_no,
				'remark' => $remark,
				'date' => $date
			];

			$this->send_comment_notification($params, $tt_assign_to, $reply_to, 'service_ticket');
		}

		return $post_data; 
	}

	/**
	 * Sends notifications to users when a comment is added to a ticket.
	 * Notifies all assigned users if `reply_to` is empty, or only the specific users.
	 *
	 * @param array  $tt_comment_info Comment details.
	 * @param array  $tt_assign_to    Assigned user IDs.
	 * @param array  $reply_to        Optional user IDs to notify specifically.
	 * @param string $type            Ticket type (default: 'service_ticket').
	 * @return mixed Result from notification system.
	 */
	public function send_comment_notification($tt_comment_info, $tt_assign_to, $reply_to = [], $type = 'service_ticket') {
		$tt_id = $tt_comment_info['tt_id'];
		$tt_no = $tt_comment_info['tt_no'];
		$remark = $tt_comment_info['remark'];
		$date = $tt_comment_info['date'];
		$created_by_name = $this->user['display_name'];

		$ticket_type = $type === 'trouble_ticket'
			? 'Trouble Ticket'
			: 'Service Ticket';

		$email_list = [];
		$whatsapp_list = [];
		$telegram_list = [];

		$collect_contacts = function (?array $users) use (&$email_list, &$whatsapp_list, &$telegram_list) {
			if (empty($users)) {
				return;
			}

			foreach ($users as $user) {

				if (!empty($user['email'])) {
					$email_list[] = $user['email'];
				}

				if (
					!empty($user['whatsapp']) &&
					($user['allow_whatsapp'] ?? 0) == 1
				) {
					$whatsapp_list[] = $user['whatsapp'];
				}

				if (
					!empty($user['telegram']) &&
					($user['allow_telegram'] ?? 0) == 1
				) {
					$telegram_list[] = $user['telegram'];
				}
			}
		};

		if (empty($reply_to)) {

			$notification = "{$ticket_type} details and comments have been updated.";

			$email_body = "{$ticket_type} No: {$tt_no}" .
				"<br>{$ticket_type} Job Details and Comments:" .
				"<br>Updated On: {$date}" .
				"<br>Comment: {$remark}" .
				"<br>Updated By: {$created_by_name}";

			$all_technical_list = $this->common_model->get_technical_list(
				'trouble_ticket'
			);

			$all_assigned_and_pic = $this->get_service_ticket_reply_list(
				$all_technical_list,
				$tt_assign_to,
				'doc_do_send'
			);

			$collect_contacts($all_assigned_and_pic);

		} else {

			$notification = "{$created_by_name} replied to you.";

			$email_body = "{$ticket_type} No: {$tt_no}" .
				"<br><br>{$created_by_name} replied to you:" .
				"<br>{$remark}" .
				"<br><br>Updated On: {$date}";

			$reply_to_ids = array_map('strval', $reply_to);

			$all_user_info = $this->get_service_ticket_reply_list(
				$this->common_model->get_technical_list('trouble_ticket'),
				$tt_assign_to,
				'doc_do_send_custom',
				[
					'reply_to' => $reply_to_ids
				]
			);

			$collect_contacts($all_user_info);
		}

		$email_list = array_unique(array_filter($email_list));
		$whatsapp_list = array_unique(array_filter($whatsapp_list));
		$telegram_list = array_unique(array_filter($telegram_list));

		$this->load->library('notification_service');
		$contacts = $this->notification_service->buildContacts( $email_list, $whatsapp_list, $telegram_list );

		$header = "{$ticket_type} [{$tt_no}] Job Details and Comments Updated";

		$this->notification_service->queue(
			$contacts,
			$header,
			$email_body,
			'TICKET JOB TRACKING',
			[
				'ticket_type' 	=> !empty($ticket_type) ? $ticket_type : '-',
				'ticket_no' 	=> !empty($tt_no) ? $tt_no : '-',
				'notification' 	=> !empty($notification) ? $notification : '-',
				'remark' 		=> !empty($remark) ? $remark : '-',
				'updated_on' 	=> !empty($date) ? $date : '-',
				'updated_by' 	=> !empty($created_by_name) ? $created_by_name : 'N/A',
			],
			'',
			[
				'acc_id'        => 'ticket',
				'user_id'       => $this->user['idx'],
				'controller'    => 'ticket',
				'doc_id'        => $tt_id,
				'send_method'   => 'manual',
				'email_starter' => $header,
				'doc_type'      => "[{$ticket_type} Job Details and Comments Updated]",
			]
		);

		return true;
	}
	
	function get_tt_email_template(){
		$return_val = array();
		$str = " SELECT `val` FROM sys_config WHERE `key` = 'sms_email_template' ;";
		$query = $this->db->query($str);
		if( $query->num_rows() > 0 ){
			$result = $query->row_array();
			$return_val['sms_email_template'] = $result['val'];
			
		}else{
			$return_val['sms_email_template'] = '';
		}
		return $return_val['sms_email_template'];
	}
	
	function get_overdue_trouble_ticket( $overdue_minutes = 30 ){
		$return_val = array();
		$str = " SELECT tt_no , warning_level, tt_category, tt_email, created_by,
					CASE WHEN last_warning_on IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, last_warning_on,NOW())
					ELSE TIMESTAMPDIFF(MINUTE, datetime_open,NOW()) END AS overdue
					FROM trouble_ticket 
					WHERE tt_status = 1 AND warning_level <= 4
					HAVING overdue >= ".$overdue_minutes." ";
										
		$query = $this->db->query($str);
		if( $query->num_rows() > 0 ){
			$return_val = $query->result_array();
		}
		return $return_val;
	}
	
	function update_warning_level( $tt_no, $level ){
		
			$str = " UPDATE trouble_ticket 
						SET warning_level = '".$level."',
							last_warning_on = NOW(),
							updated_on = NOW() 
						WHERE tt_no = '".$tt_no."' ; ";
			
			$query = $this->db->query($str);
		
	}
	
	function save_settings( $post_data ){

		if(!empty($post_data['pic_name'])) {
			$pic_list_insert = [];
			$pic_list_update = [];
			for ($i=0; $i < count($post_data['pic_name']); $i++) {
				if(!empty($post_data['pic_name'][$i])) {
					if(empty($post_data['tt_setting_id'][$i])) {
						$pic_list_insert[] = [
						'tt_level' => 1,
						'tt_pic' => $post_data['pic_name'][$i],
						'tt_email' => $post_data['pic_email'][$i],
						'tt_contact' => $post_data['pic_contact'][$i],
						'tt_telegram' => $post_data['pic_telegram'][$i],
						'tt_user_id' => $post_data['pic_id'][$i],
						'tt_interval' => 0
						];
					} else {
						$pic_list_update[] = [
						'tt_setting_id' => $post_data['tt_setting_id'][$i],
						'tt_level' => 1,
						'tt_pic' => $post_data['pic_name'][$i],
						'tt_email' => $post_data['pic_email'][$i],
						'tt_contact' => $post_data['pic_contact'][$i],
						'tt_telegram' => $post_data['pic_telegram'][$i],
						'tt_user_id' => $post_data['pic_id'][$i],
						'tt_interval' => 0
						];
					}
				}
			}
			if(!empty($pic_list_insert)) {
				$this->db->insert_batch('tt_setting', $pic_list_insert);
			}
			if(!empty($pic_list_update)) {
				$this->db->update_batch('tt_setting', $pic_list_update,'tt_setting_id');
			}
		}

		//save 2nd / 3rd / 4th
		$post_data['product_code'] = $post_data['product_code'] ?? [];
		foreach( $post_data['product_code'] AS $key => $val ){
			$qwhere = "";
			if( $val == '' )
				$qwhere .= " AND ( tt_product_code = '' OR tt_product_code IS NULL ) ";
			else
				$qwhere .= " AND ( tt_product_code = '".$val."' ) ";
				
			for( $z = 2 ; $z <= 4 ; $z++ ){
				$level = array();
				$level = $this->get_tt_level( $z, $val );
				if( $level['tt_setting_id'] !=''  ){
					$str = " UPDATE tt_setting 
								SET tt_product_code = '".(!empty($val)?$val:0)."' ,
									tt_pic	 = '". $this->db->escape_str( $post_data['level'.$z.'_pic'][$key] ) ."' , 
									tt_email = '". $this->db->escape_str( $post_data['level'.$z.'_email'][$key] ) ."' ,
									tt_contact = '". $this->db->escape_str( $post_data['level'.$z.'_contact'][$key] ) ."'
								WHERE tt_level = '".$z."' 
								$qwhere ";
				}else{
					$str = " INSERT INTO tt_setting 
								SET tt_product_code = '".(!empty($val)?$val:0)."',
									tt_pic	 = '". $this->db->escape_str( $post_data['level'.$z.'_pic'][$key] ) ."' , 
									tt_email = '". $this->db->escape_str( $post_data['level'.$z.'_email'][$key] ) ."' ,
									tt_contact = '". $this->db->escape_str( $post_data['level'.$z.'_contact'][$key] ) ."',
									tt_level = '".$z."', 
									tt_interval = '0' 
							";
				}
				
				
				
				$query = $this->db->query($str);
			}
		
		}

		if (isset($post_data['enable_email'])) {
			$str = "UPDATE sys_config SET `val` = '" . $this->db->escape_str($post_data['enable_email']) . "' WHERE `key` = 'enable_email';";
			$this->db->query($str);
		}

		if (isset($post_data['enable_sms'])) {
			$str = "UPDATE sys_config SET `val` = '" . $this->db->escape_str($post_data['enable_sms']) . "' WHERE `key` = 'enable_sms';";
			$this->db->query($str);
		}

		if (isset($post_data['sms_email_template'])) {
			$str = "UPDATE sys_config SET `val` = '" . $this->db->escape_str($post_data['sms_email_template']) . "' WHERE `key` = 'sms_email_template';";
			$this->db->query($str);
		}

		if (isset($post_data['sms_email_overdue_template'])) {
			$str = "UPDATE sys_config SET `val` = '" . $this->db->escape_str($post_data['sms_email_overdue_template']) . "' WHERE `key` = 'sms_email_overdue_template';";
			$this->db->query($str);
		}
	}
	
	function get_job_tracking_detail( $tt_id ){
		
		$return_val = array();
		
		$sql = " SELECT  tt_job_tracking.* , user.display_name FROM tt_job_tracking 
					LEFT JOIN user ON user.idx = tt_job_tracking.created_by
					WHERE tt_id = '".$tt_id."' ";
		$query = $this->db->query($sql);
		
		if( $query->num_rows() > 0 ){
			$return_val = $query->result_array();
		}
		
		return $return_val;
		
	}
	
	function save_job_tracking_detail($tt_id, $rec_id, $remark, $created_by, $reply_to = []){
		
		$return = array();
		$return['success'] = 0 ;
		$return['rec_id']  = '' ;
		$return['sql'] = '';
		if( $tt_id != '' ){
			if( $rec_id == '' ){
				
				$sql = " INSERT INTO tt_job_tracking 
							SET tt_id = '".$tt_id."' ,
								tt_date = NOW() ,
								tt_remark =  '".$this->db->escape_str( $remark )."' ,
								reply_to = '".serialize($reply_to)."' ,
								created_on = NOW() ,
								created_by = '".$created_by."' ;" ;
				$query = $this->db->query($sql);
				if( $this->db->affected_rows() ){
					$return['success'] = 1;
					$return['rec_id']  = $this->db->insert_id();
				}else{
					$return['success'] = 0;
					$return['rec_id']  = '';
				}
								
			}else{
				$sql = " UPDATE tt_job_tracking 
							SET tt_remark =  '".$this->db->escape_str( $remark )."' ,
								reply_to = '".serialize($reply_to)."' ,
								updated_on = NOW() ,
								updated_by = '".$created_by."' 
							WHERE tt_id = '".$tt_id."' AND rec_id = '".$rec_id."' ;" ;
				$query = $this->db->query($sql);
				
				if( $this->db->affected_rows() ){
					$return['success'] = 1;
					$return['rec_id']  = $rec_id;
				}else{
					$return['success'] = 0;
					$return['rec_id']  = $rec_id;
				}
				
			}
			
			$return['sql'] = $sql;
			return $return ; 
			
		}else{
			$return['success'] = 0 ;
			$return['success'] = $rec_id;
			return $return; 
		}
	}
	
	function delete_job_tracking_detail( $tt_id, $rec_id ){
		$sql = "DELETE FROM tt_job_tracking WHERE tt_id = '".$tt_id."' AND tt_job_id = '".$rec_id."' ";
		$query = $this->db->query($sql);
		if( $this->db->affected_rows() ){
			return 1 ;
		}else{
			return 0 ;
		}
	}
	
	function ticket_delete($tt_id, $attachments = []) {
		$this->db->where('tt_id', $tt_id)->delete('trouble_ticket');

		if ($this->db->affected_rows() > 0) {
			$this->db->where('tt_id', $tt_id)->delete('tt_job_tracking');

			if (!empty($attachments)) {
				$this->load->model('common_model');
				foreach ($attachments as $attach) {
					$this->common_model->remove_attachment($attach, $tt_id);
				}
			}

			return 1;
		}

		return 0;
	}
	
	function get_tt_issues_listing($txt_search='',$page_item_no=0,$row_per_page='',$query_where='')
	{

		$txt_search = $this->db->escape_str($txt_search);
		$return_val['total_row'] = 0;		
		
		$qwhere="";
		
		if( $txt_search != '' ){
			$qwhere = " AND "
					. "$query_where" . "_name "
					. " LIKE '%$txt_search%'";
		}
		
		$query_str 	= "	SELECT count(*) AS total_row 
						FROM $query_where
						WHERE 1 = 1 " . $qwhere ;
		
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		if( $row_per_page == '' )
			$row_per_page = $_SESSION['config']['max_page_item'];
		
		$query_str 	= "	SELECT *, "
					. "$query_where" . "_id AS id,"
					. "$query_where" . "_name AS name"
					. "	FROM $query_where
						WHERE 1 = 1 $qwhere "
					. "ORDER BY $query_where" . "_name ASC "
					. "LIMIT $page_item_no, $row_per_page";
		$query = $this->db->query($query_str);
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ; 
		return $return_val;
	}

	function get_single_ticket_issues( $category='', $id=0 ) {
		$return_val = [];
		if(!empty($category) && !empty($id)) {
			$query_str 	= "SELECT "
						. "$category" . "_id AS temp_id,"
						. "$category" . "_id AS id,"
						. "$category" . "_name AS name,"
						. "$category" . "_desc AS `desc` "
						. "FROM $category WHERE "
						. "$category" . "_id = "
						. $this->db->escape_str($id);
			$query = $this->db->query($query_str);

			if($query->num_rows() > 0){
				$return_val = $query->row_array();
				$return_val['btn_delete']='enabled';
			}
		}
		
		if(empty($return_val)) {
			$return_val['id'] = '0';
			$return_val['name'] = '';
			$return_val['desc'] = '';		
			$return_val['temp_id'] = rand(10000, 99999);
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
		
	}

	function save_ticket_issues( $category, $post_data, $userId ) {

		if(empty($post_data['id'])) {
			$str = "INSERT INTO $category ("
				. "$category" . "_id,"
				. "$category" . "_name,"
				. "$category" . "_desc,"
				. "created_on,created_by"
				. ") VALUES (?,?,?,NOW(),?)";
			$query = $this->db->query($str,[0,$post_data['name'],$post_data['desc'],$userId]);
		} else {
			$str = "UPDATE $category SET "
				. "$category" . "_name = ?,"
				. "$category" . "_desc = ?,"
				. "updated_on = NOW(),"
				. "updated_by = ? WHERE "
				. "$category" . "_id = ?";
			$query = $this->db->query($str,[$post_data['name'],$post_data['desc'],$userId,$post_data['id']]);
		}

		return $post_data;
	}

	function delete_ticket_issues( $category, $post_data ) {
		$query_str = " DELETE FROM $category WHERE "
			. "$category" . "_id = ?";
		$query = $this->db->query($query_str,[$post_data['id']]);

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 	
		
		$categoryText = '';
		$name = $post_data['name'];
		if($category == 'tt_complaint') {
			$categoryText = 'complaint type';
		} else if ($category == 'tt_sof'){
			$categoryText = 'source of fault';
		} else {
			$categoryText = 'cause of fault';
		}
		
		$action_desc	= "a $categoryText ($name) has been deleted";
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
	}

	function get_tt_pic () {
		$query_str = "SELECT tts.*, u.allow_whatsapp, u.allow_telegram FROM tt_setting tts LEFT JOIN user u ON (tts.tt_user_id = u.idx) WHERE tts.tt_product_code IS NULL AND tts.tt_level = 1";
		$query = $this->db->query($query_str);

		return $query->result_array();
	}

	function get_technical_user($type='customer_support') {
		$sql = "SELECT tu.*, u.display_name, u.allow_whatsapp, u.allow_telegram
				FROM technical_user tu
				LEFT JOIN user u ON tu.user_id = u.idx
				WHERE tu.doc_type = ?
				";
		$query = $this->db->query($sql, [$type]);

		return $query->result_array();
	}

	function delete_tt_pic ($tt_pic_id, $tt_pic_name) {
		$query_str = "DELETE FROM tt_setting WHERE tt_setting_id = ?";
		$query = $this->db->query($query_str,[$tt_pic_id]);

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method;

		$action_desc	= "a pic of service ticket ($tt_pic_name) has been deleted";
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		if( $this->db->affected_rows() ){
			return 1 ;
		}else{
			return 0 ;
		}
	}

	function delete_techinical_user ($id, $user_id, $type='service_ticket') {
		$query_str = "DELETE FROM technical_user WHERE id = ? AND user_id = ? AND technical_type = ?";
		$query = $this->db->query($query_str,[$id, $user_id, $type]);

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method;

		$action_desc = "A technical user of " . ($type === 'service_ticket' ? 'service ticket' : 'trouble ticket') . " (User ID = {$user_id}) has been deleted";
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		if( $this->db->affected_rows() ){
			return 1 ;
		}else{
			return 0 ;
		}
	}

	function get_assignee_details ($tt_assign_to) {
		$tt_assign_to_str = implode(",", $tt_assign_to);
		$query_str = "SELECT username, mobile_no, telegram_id, allow_whatsapp, allow_telegram FROM user WHERE idx IN ($tt_assign_to_str)";

		$query = $this->db->query($query_str);

		return $query->result_array() ?? [];
	}

	/**
	 * Returns a list of users to notify based on assigned users and/or reply targets.
	 *
	 * @param array $sel_user List of selected technical users.
	 * @param array $tt_assign_to Assigned user IDs.
	 * @param string $mode Mode of operation: 'option', 'doc_do_send', or 'doc_do_send_custom'.
	 * @param array $custom Optional custom data like reply_to list.
	 * @return array Either formatted user labels (mode 'option') or user contact info (other modes).
	 */
	public function get_service_ticket_reply_list($sel_user, $tt_assign_to, $mode = 'option', $custom = []) {
		$assigned_user_ids = array_map('strval', $tt_assign_to);
		$assigned_user = array_intersect_key(
			array_column($sel_user, 'display_name', 'idx'),
			array_flip($assigned_user_ids)
		);

		$user_list = $this->common_model->get_user_list() ?? [];
		$tech_list = $this->common_model->get_technical_list('trouble_ticket');

		$main_pic = array_intersect_key(
			array_column($user_list, 'display_name', 'idx'),
			array_column($tech_list, 'display_name', 'user_id')
		);

		if ($mode === 'option') {
			$all = [];

			foreach ($assigned_user as $id => $name) {
				$all[$id] = ['name' => $name, 'src' => ['Assigned']];
			}

			foreach ($main_pic as $id => $name) {
				if (isset($all[$id])) {
					$all[$id]['src'][] = 'Main PIC';
				} else {
					$all[$id] = ['name' => $name, 'src' => ['Main PIC']];
				}
			}

			$reply_list = array_map(
				fn($user) => "{$user['name']} (" . implode(', ', $user['src']) . ")",
				$all
			);

			return $reply_list;
		}

		$all_main_pic_list = $this->common_model->get_technical_list();
		$all_technical_list = $this->common_model->get_technical_list('trouble_ticket');

		$extract_user_info = function ($user) {
			return [
				'user_id'  => $user['user_id'],
				'email'    => $user['email'] ?? null,
				'whatsapp' => $user['mobile_no'] ?? null,
				'telegram' => $user['telegram_id'] ?? null,
				'allow_whatsapp' => $user['allow_whatsapp'] ?? 0,
				'allow_telegram' => $user['allow_telegram'] ?? 0,
			];
		};

		if ($mode === 'doc_do_send_custom') {
			$reply_to_ids = array_map('strval', $custom['reply_to'] ?? []);

			$technical_user_info = array_values(array_filter(array_map(
				fn($user) => $extract_user_info($user),
				array_filter($all_technical_list, fn($user) => in_array((string)$user['user_id'], $reply_to_ids))
			)));

			$main_pic_info = array_values(array_filter(array_map(
				fn($user) => $extract_user_info($user),
				array_filter($all_main_pic_list, fn($user) => in_array((string)$user['idx'], $reply_to_ids))
			)));
		} else {
			$technical_user_info = array_values(array_filter(array_map(
				fn($user) => $extract_user_info($user),
				array_filter($all_technical_list, fn($user) => in_array((string)$user['user_id'], $assigned_user_ids))
			)));

			$main_pic_info = array_values(array_filter(array_map(
				fn($user) => $extract_user_info($user),
				array_filter($all_main_pic_list, fn($user) => in_array((string)$user['idx'], $tech_list))
			)));
		}

		$technical_ids = array_column($technical_user_info, 'user_id');
		$main_pic_info = array_filter(
			$main_pic_info,
			fn($user) => !in_array($user['user_id'], $technical_ids)
		);

		return array_merge($technical_user_info, array_values($main_pic_info));
	}

	function send_reply_to_customer($tt_id, $tt_no, $customer_no, $customer_reply) {
		$this->load->model('email_model');
		$this->load->model('message_scheduler_model');
		$this->load->library('whatsapp_template');

		$sql = "SELECT p.* FROM `profile` p LEFT JOIN customer c ON c.profile_id = p.acc_id WHERE c.customer_no = ?";
		$query = $this->db->query($sql, $customer_no);

		$profile = $query->row_array();

		if(!empty($profile['acc_email'])) {
			$this->email_model->add_email_schedule([
				'recipient_emails'  => $profile['acc_email'],
				'scheduler_id'      => '',
				'send_by'           => '',
				'email_title'       => "Service Ticket $tt_no",
				'email_msg'         => $customer_reply,
				'email_cust_status' => 1,
				'email_attachment'  => '',
				'email_schedule_on' => date("Y-m-d H:i:s")
			]);
		}

		if(!empty($profile['acc_mobileno']) && !empty($profile['allow_whatsapp'])) {

			$meta_template = $this->whatsapp_template->build(
				'SERVICE TICKET REPLY',
				[
					'tt_no' => !empty($tt_no) ? $tt_no : '-',
					'customer_no' => !empty($customer_no) ? $customer_no : '-',
					'tt_reply' => !empty($customer_reply) ? $customer_reply : '-',
				]
			);
			
			$whatsapp_data = [
				'message' => "Service Ticket $tt_no\n".$customer_reply,
				'msg_type' => 'whatsapp',
				'msg_to' => $profile['acc_mobileno'],
				'customer_no' => $customer_no,
				'msg_schedule_on' => date('Y-m-d H:i:s')
			];

			$send_array = [
				'acc_id' =>  $profile['acc_id'] ?? 0,
				'customer_no' =>  $customer_no ?? 0,
				'user_id' =>  $this->user['idx'] ?? 0,
				'controller' =>  'ticket',
				'doc_id' =>  $tt_id,
				'send_type' =>  'custom',
				'remark' =>  'Whatsapp Service Ticket Reply #'.$tt_no.' to customer no' . $customer_no,
				'send_method' => 'auto',
				'subject' => "Service Ticket $tt_no",
				'body' => $customer_reply,
				'whatsapp_list' => [$profile['acc_mobileno']],
				'meta_template' => $meta_template['meta_template_name'] ?? '',
				'meta_vars' => $meta_template['meta_variable'] ?? []
			];

			$this->message_scheduler_model->insert_new_scheduled_message($whatsapp_data, $send_array);
		}

		if(!empty($profile['telegram_id']) && !empty($profile['allow_telegram'])) {
			$telegram_data = [
				'message' => "Service Ticket $tt_no\n".$customer_reply,
				'msg_type' => 'telegram',
				'msg_to' => $profile['telegram_id'],
				'customer_no' => $customer_no,
				'msg_schedule_on' => date('Y-m-d H:i:s')
			];

			$send_array = [
				'acc_id' =>  $profile['acc_id'] ?? 0,
				'customer_no' =>  $customer_no ?? 0,
				'user_id' =>  $this->user['idx'] ?? 0,
				'controller' =>  'ticket',
				'doc_id' =>  $tt_id,
				'send_type' =>  'custom',
				'remark' =>  'Telegram Service Ticket Reply #'.$tt_no.' to customer no' . $customer_no,
				'send_method' => 'auto',
				'subject' => "Service Ticket $tt_no",
				'body' => $customer_reply,
				'telegram_list' => [$profile['telegram_id']]
			];

			$this->message_scheduler_model->insert_new_scheduled_message($telegram_data, $send_array);
		}

		/**
		 * Push notification to the customer's mobile app.
		 * No opt-in flag is checked here because push has its own: profile_app.allow_push
		 */
		try {
			$this->load->library('push_service');
			$this->push_service->notify_customer(
				$customer_no,
				'ticket_reply',
				array('TT_NO' => $tt_no),
				array('related_id' => $tt_no)
			);
		} catch (Exception $e) {
			log_message('error', '[push] ticket reply notification failed for ticket_no ' . $tt_no . ': ' . $e->getMessage());
		}
	}
}
