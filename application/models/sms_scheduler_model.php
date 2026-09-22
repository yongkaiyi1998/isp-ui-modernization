<?php
class Sms_scheduler_model extends MY_Model{

	protected $_table 			= 'sms_scheduler';
	protected $_primary_key 	= 'scheduler_id';
	protected $api_url = 'https://api.esms.com.my/sms/send';
    protected $api_key;
    protected $api_secret;

	public function __construct()
	{
		parent::__construct();
		$this->api_key = $this->config->item('sms_apikey');
        $this->api_secret = $this->config->item('sms_apisecret');
	}
	
	function sms_add($data)
	{
		
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= '';//$this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'a new sms schedule has been added';	
		$cust_no			= '';//$customer_no; 
		$action_category 	= 'insert';
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);					
		
		$scheduler_id = $this->sms_scheduler_model->add($data);	
		
		$model	= 'sms_outgoing_model';
		$this->load->model($model);
		$this->sms_outgoing_model->insert_sms_to_outgoing($data,$scheduler_id);		
	}
	
	function sms_edit($data,$scheduler_id)
	{
		
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= ''; //$this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'sms schedule (ID : '.$scheduler_id.') has been edited';	
		$cust_no			= '';//$customer_no; 
		$action_category 	= 'update';
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);	
				
		
		$this->sms_scheduler_model->edit($data,$scheduler_id);
		
		$model	= 'sms_outgoing_model';
		$this->load->model($model);
		$this->sms_outgoing_model->insert_sms_to_outgoing($data,$scheduler_id);
	}
	
	function sms_delete($id)
	{
		$query_str = "DELETE FROM ".$this->_table." WHERE ".$this->_primary_key."=".$id;			
		
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'an sms schedule (ID : ' . $id .') has been deleted';	
		$cust_no			= '';//$customer_no; 
		$action_category 	= 'delete';
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);	
		
		
		$model	= 'sms_outgoing_model';
		$this->load->model($model);
		
		$is_record_empty = $this->sms_outgoing_model->get_outgoing_by(array('scheduler_id',$id));	
		$is_delete 		 = $this->sms_outgoing_model->delete_outgoing_by(array('scheduler_id',$id));		
		
		$return_val = '';
		if(($is_delete == '1') || (empty($is_record_empty))) $return_val = $this->db->query($query_str);
				
		return $return_val;
	}
	
	function get_sms_list($txt_search='',$date_from='',$date_to='',$page_item_no=0,$row_per_page='')
	{
		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$qwhere = "";
		if( $date_from != '' )
			$qwhere .= " AND s.sms_schedule_on >= '".date('Y-m-d 00:00:00' , strtotime( $date_from ) )."' ";
		
		if( $date_to != '' )
			$qwhere .= " AND s.sms_schedule_on <= '".date('Y-m-d 23:59:59' , strtotime( $date_to ) )."' ";
		
		if( $row_per_page == '' ) 
			$row_per_page = $_SESSION['config']['max_page_item'];
		
		
		$query_str 					= "SELECT count(s.scheduler_id) AS total_row FROM ".$this->_table." s 
										WHERE s.sms_title LIKE '%".$txt_search."%' $qwhere ";
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	 = " SELECT * ";
		$query_str	.= " FROM ".$this->_table. " s "; 
		$query_str	.= " WHERE s.sms_title LIKE '%".$txt_search."%' $qwhere ";
		$query_str	.= " ORDER BY s.scheduler_id DESC " ;
		$query_str	.= " LIMIT ".$page_item_no.", ".$row_per_page ;
		
		//~ _debug_array($query_str); exit;
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}
	
	function get_detail($id,$is_edit)
	{		
		$return_val = array();
		if(!empty($id) && (!empty($is_edit)))
		{
			$query_str  = "SELECT * FROM `".$this->_table."` WHERE `".$this->_primary_key."` = '".$id."' ";
			$query		= $this->db->query($query_str);			
			if( $query->num_rows() > 0 ) $return_val = $query->row_array();
		}		
		else if(empty($id))
		{						
			//if all field default is "", can do like this
			//~ $fields = $this->db->list_fields($this->_table);			
			//~ foreach ($fields as $f_key => $f_data) $return_val[$f_data] = '';	
					
			$return_val['scheduler_id'] ='';
			$return_val['sms_title']='';
			$return_val['sms_msg'] ='';
			//~ $return_val['sms_schedule_on']= date('Y-m-d H:i:00');
			//~ $return_val['sms_schedule_on']= date('Y-m-d H:i:00', strtotime('+1 hour'));
			$return_val['sms_schedule_on']= date('Y-m-d H:i:00', strtotime('+30 minutes'));
			$return_val['is_building'] = 1 ;
			$return_val['sms_building'] ='';
			$return_val['sms_cust_cat']='';
			$return_val['sms_cust_status'] ='';
			$return_val['sms_to'] ='';
			$return_val['created_by'] ='';
			$return_val['created_date'] ='';
			$return_val['modified_by'] ='';
			$return_val['modified_date'] ='';
		}
		return $return_val;	
	}
	
	function get_sms_report( $date_from='', $date_to='', $title='' ){
		
		$return_val = array();
		if( $date_from == '' )
			$date_from = date("Y-m-d");
		
		if( $date_to   == '' )
			$date_to   = date("Y-m-d");
		
		$qwhere = "";
		if( $date_from != '' )
			$qwhere .= " AND s.scheduled_time >= '".date( 'Y-m-d 00:00:00', strtotime( $date_from ) )."' ";
			
		if( $date_to != '' )
			$qwhere .= " AND s.scheduled_time <= '".date( 'Y-m-d 23:59:59', strtotime( $date_to ) )."' ";
			
		if( $title != '' )
			$qwhere .= " AND scheduler.sms_title LIKE '%".$title."%' ";
		
		$sql = " 	SELECT z.sms_id, z.sms_title, z.acc_id, z.scheduled_time, z.sms_phone, z.sms_msg, 
							z.sms_timestamp, z.sms_processed, z.sms_status, z.sms_total, z.sms_transport,
							z.sms_attempt FROM (
		
					SELECT	s.sms_id, scheduler.sms_title AS sms_title , s.acc_id, s.scheduled_time, s.sms_phone, s.sms_msg, 
							s.sms_timestamp, s.sms_processed, s.sms_status, s.sms_total, s.sms_transport,
							s.sms_attempt
					FROM sms_outgoing s
					INNER JOIN sms_scheduler scheduler ON s.scheduler_id = scheduler.scheduler_id
					WHERE 1=1 $qwhere  
					
					UNION
					
					SELECT 	s.sms_id, scheduler.sms_title AS sms_title, s.acc_id, s.scheduled_time, s.sms_phone, s.sms_msg, 
							s.sms_timestamp, s.sms_processed, s.sms_status, s.sms_total, s.sms_transport,
							s.sms_attempt
					FROM sms_outarchive s
					INNER JOIN sms_scheduler scheduler ON s.scheduler_id = scheduler.scheduler_id
					WHERE 1=1 $qwhere 
					
					) z ORDER BY z.scheduled_time, z.sms_title, z.sms_processed, z.sms_phone
				";
		$query = $this->db->query($sql);
		if( $query->num_rows() > 0 ) 
			$return_val = $query->result_array();
			
		return $return_val;	
		
	}
	
	function get_sms_template_list(){
	
		$return_val = array();

		$query_str = "	SELECT e.template_id, e.template_name, e.sms_title, e.is_default 
						FROM sms_template e WHERE `status` = 1 ORDER BY e.is_default DESC , e.sms_title ASC ";
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
	
	
	function get_sms_template_detail( $qwhere ){
		
		$return_val = array();

		$query_str = "	SELECT s.* 
						FROM sms_template s
						WHERE 1 = 1 $qwhere
						ORDER BY s.template_id ";
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
		}
		else{
			$fields = $this->db->list_fields('sms_template');
			foreach ($fields as $f_key => $f_data){ 
				$return_val[$f_data] = '';
			}
		}
		
		return $return_val;
		
	}
	
	function add_sms_template($post_data) {
		if ($post_data['template_id'] == '') {
			$sql = "INSERT INTO sms_template 
						SET template_name = '" . $this->db->escape_str($post_data['template_name']) . "',
							sms_title     = '" . $this->db->escape_str($post_data['sms_title']) . "' ,
							sms_msg       = '" . $this->db->escape_str($post_data['sms_msg']) . "' ,
							created_by    = '' , 
							created_date  = NOW();";
			
			$action_desc     = 'a sms template ( ' . $this->db->escape_str($post_data['template_name']) . ') has been inserted';
			$action_category = 'insert';
		} else {
			$sql = "UPDATE sms_template 
						SET template_name = '" . $this->db->escape_str($post_data['template_name']) . "',
							sms_title     = '" . $this->db->escape_str($post_data['sms_title']) . "' ,
							sms_msg       = '" . $this->db->escape_str($post_data['sms_msg']) . "' ,
							modified_by   = '' , 
							modified_date = NOW() 
						WHERE template_id = '" . $this->db->escape_str($post_data['template_id']) . "'";

			$action_desc     = 'a sms template ( ' . $this->db->escape_str($post_data['template_name']) . ') has been updated';
			$action_category = 'update';
		}

		$model           = 'action_log_model';
		$this->load->model($model);
		$ctrl            = $this->router->fetch_class();
		$method          = $this->router->method;
		$cust_no         = '';
		$esc_query_str   = $this->db->escape_str($sql);
		$action_log      = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category, $cust_no);

		$this->db->query($sql);

		if ($this->db->affected_rows()) {
			if ($post_data['template_id'] == '') {
				return $this->db->insert_id();
			} else {
				return $post_data['template_id'];
			}
		} else {
			return 0;
		}
	}

	
	function delete_sms_template( $template_id ){
		$return_val = '0';
		
		$query_str = "DELETE FROM sms_template 
						WHERE template_id = '".$template_id."' AND is_default = 0 ; ";
		
		$model				= 'action_log_model';
		$this->load->model($model);
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'a sms template (ID : ' . $template_id .') has been deleted';
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

	function esms_send($phone, $msg) {

		$post_data = [
			'user'	=> $this->api_key,
			'pass'	=> $this->api_secret,
			'to'	=> $phone,
			'msg'	=> $msg,
			'type'	=> '0'
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, 'Content-type: application/x-www-form-urlencoded');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200;

        //example of return
        //{"status":0,"creditDeducted":1,"message":"ok","id":"88ce9c6e-1707-41b8-95a2-91d228001fc7","parts":1,"type":0}

        return $status;
    }
		
}

