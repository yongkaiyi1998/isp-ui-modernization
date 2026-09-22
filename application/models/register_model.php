<?php

class Ticket_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}

	public function package_update($post_back,$username,$package_no)
	{	
		$query_str = "UPDATE package SET " .
					"name = '" . $this->db->escape_str($post_back['name']) . "', " .
					"category = '" . $this->db->escape_str($post_back['category']) . "', " .
					"status = '" . $this->db->escape_str($post_back['status']) . "', " .
					"domain = '" . $this->db->escape_str($post_back['domain']) . "', " .
					"bandwidth = '" . $this->db->escape_str($post_back['bandwidth']) . "', " .
					"no_of_fixed_ip = '" . $this->db->escape_str($post_back['no_of_fixed_ip']) . "', " .
					"no_of_email = '" . $this->db->escape_str($post_back['no_of_email']) . "', " .
					"deposit = '" . $this->db->escape_str($post_back['deposit']) . "', " .
					"stamp_duty = '" . $this->db->escape_str($post_back['stamp_duty']) . "', " .
					"monthly_charge = '" . $this->db->escape_str($post_back['monthly_charge']) . "', " .
					"yearly_charge = '" . $this->db->escape_str($post_back['yearly_charge']) . "', " .
					"installation = '" . $this->db->escape_str($post_back['installation']) . "', " .
					"installation_auto = '" . $this->db->escape_str($post_back['installation_auto']) . "', " .
					"package_month = '" . $this->db->escape_str($post_back['package_month']) . "', " .
					"stop_service_after = '" . $this->db->escape_str($post_back['stop_service_after']) . "', " .
					"post_charge = '".$this->db->escape_str($post_back['post_charge'])  . "', " .
					"yearly_billing = '" . $this->db->escape_str($post_back['yearly_billing']) . "', " .
					"dealer_comm_type = '" . $this->db->escape_str($post_back['dealer_comm_type']) . "', " .
					"dealer_monthly_comm = '" . $this->db->escape_str($post_back['dealer_monthly_comm']) . "', " .
					"dealer_onetime_comm = '" . $this->db->escape_str($post_back['dealer_onetime_comm']) . "', " .
					"modified_by = '" . $username . "', " .
					"modified_date = now() ".
					"WHERE package_no = '" . $this->db->escape_str($post_back['package_no']) . "' ";
		
		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);
		$method 		= $this->router->method;
		$action_desc	= 'a package has been edited';
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		
		$this->db->query($query_str);
		if( $this->db->affected_rows() > 0 ){
			$str = " UPDATE customer SET package_name = '".$post_back['']."' 
						WHERE package = '".$this->db->escape_str($post_back['package_no'])."' ";
			$this->db->query($query_str);
		}
		
		
		
		$package_no = $this->db->escape_str($package_no);
		return $package_no;
	}

	
	function package_insert($post_back,$username)
	{								
		$query_str 		= "SELECT MAX(p.package_no) as package_no FROM package p";
		$query 			= $this->db->query($query_str);
		$package_no 	= $return_val['package_no'] = $query->row()->package_no + 1;
		
		if(empty($post_back['stop_service_after'])) $post_back['stop_service_after'] = '';
		if(empty($post_back['yearly_billing'])) $post_back['yearly_billing'] = '';
		
		$query_str 		= "INSERT INTO package (package_no, name, category, status, domain, bandwidth, no_of_fixed_ip,
								no_of_email, deposit, stamp_duty, monthly_charge, 
								yearly_charge, installation, installation_auto,
								package_month, stop_service_after, yearly_billing, post_charge,
								dealer_comm_type, dealer_monthly_comm, dealer_onetime_comm, 
								created_by, created_date) VALUES (".
								"'" . $package_no . "', ".
								"'" . $this->db->escape_str($post_back['name']) . "', ".
								"'" . $this->db->escape_str($post_back['category']) . "', ".
								"'" . $this->db->escape_str($post_back['status']) . "', ".
								"'" . $this->db->escape_str($post_back['domain']) . "', ".
								"'" . $this->db->escape_str($post_back['bandwidth']) . "', ".
								"'" . $this->db->escape_str($post_back['no_of_fixed_ip']) . "', ".
								"'" . $this->db->escape_str($post_back['no_of_email']) . "', ".
								"'" . $this->db->escape_str($post_back['deposit']) . "', ".
								"'" . $this->db->escape_str($post_back['stamp_duty']) . "', ".
								"'" . $this->db->escape_str($post_back['monthly_charge']) . "', ".
								"'" . $this->db->escape_str($post_back['yearly_charge']) . "', ".
								"'" . $this->db->escape_str($post_back['installation']) . "', ".
								"'" . $this->db->escape_str($post_back['installation_auto']) . "', ".
								"'" . $this->db->escape_str($post_back['package_month']) . "', ".
								"'" . $this->db->escape_str($post_back['stop_service_after']) . "', ".
								"'" . $this->db->escape_str($post_back['yearly_billing']) . "', ".
								"'" . $this->db->escape_str($post_back['post_charge']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_comm_type']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_monthly_comm']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_onetime_comm']) . "', ".
								"'" . $username . "', now() )";										
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new package has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
		return $return_val;
	}
	
	function package_delete($package_no = '', $username)
	{
		if (empty($package_no)) {
			return false;
		}

		$query_str = "
			UPDATE package 
			SET deleted_date = NOW(), deleted_by = " . $this->db->escape($username) . " 
			WHERE package_no = " . $this->db->escape($package_no);

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$method = $this->router->method;
		$esc_query_str = $this->db->escape_str($query_str);
		$action_desc = 'A package has been soft deleted';
		$action_category = 'delete';

		$this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category);

		return $this->db->query($query_str);
	}
	
	function get_trouble_ticket( $tt_no='' ){
		
		$query_str 	= "SELECT tt.*, 
							CASE WHEN tt.parent_tt_id IS NOT NULL AND tt.parent_tt_id != '' THEN ptt.tt_no ELSE '' END AS parent_tt_no,
							CASE WHEN tt.datetime_close = '0000-00-00 00:00:00' THEN '' ELSE tt.datetime_close END AS datetime_close, comp.tt_complaint_name, sof.tt_sof_name, cof.tt_cof_name
					FROM trouble_ticket tt
					LEFT JOIN tt_sof sof ON tt.tt_sof_id = sof.tt_sof_id
					LEFT JOIN tt_cof cof ON tt.tt_cof_id = cof.tt_cof_id
					LEFT JOIN tt_complaint comp ON tt.tt_complaint_id = comp.tt_complaint_id
					LEFT JOIN trouble_ticket ptt ON tt.parent_tt_id = ptt.tt_id
					WHERE tt.tt_no = '".$tt_no."'; " ;
		$query = $this->db->query($query_str);	
		
		if($query->num_rows() > 0){
			$return_val = $query->row_array();
		}else{
			$return_val['tt_id'] = '';
			$return_val['parent_tt_id'] = '';
			$return_val['parent_tt_no'] = '';
			$return_val['tt_no'] = '';
			$return_val['tt_status'] = '0';
			$return_val['customer_no'] = '';
			$return_val['contact_no'] = '';
			$return_val['datetime_open'] = '';
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
			$return_val['updated_on'] = '';
			$return_val['updated_by'] = '';
			$return_val['tt_sof_name'] = '';
			$return_val['tt_cof_name'] = '';			
		}
		
		return $return_val;
		
	}
	
	function get_trouble_ticket_listing($txt_search,$page_item_no,$query_where)
	{
		$return_val['total_row'] = 0;		
		$query_str 	= "SELECT count(tt.tt_no) AS total_row 
					FROM trouble_ticket tt
					WHERE tt.tt_no LIKE '%$txt_search%' " .
					$query_where;
					
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$query_str 	= "	SELECT tt.*, CASE WHEN tt.tt_status = 1 THEN 'OPEN' ELSE 'CLOSE' END AS tt_status,
								comp.tt_complaint_name, sof.tt_sof_name, cof.tt_cof_name
						FROM trouble_ticket tt
						LEFT JOIN tt_sof sof ON tt.tt_sof_id = sof.tt_sof_id
						LEFT JOIN tt_cof cof ON tt.tt_cof_id = cof.tt_cof_id
						LEFT JOIN tt_complaint comp ON tt.tt_complaint_id = comp.tt_complaint_id
						WHERE tt.tt_no LIKE '%$txt_search%' $query_where 
						ORDER BY tt.tt_no  
						LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		$query = $this->db->query($query_str);
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		
		return $return_val;
	}
		
	function get_new_tt_no(){
		
		$length = 11 ;
		$con = " SELECT `key`, `val` FROM sys_config 
					WHERE `key` = 'tt_no_length' AND `category` = 'ticket' ; " ;
		$config = $this->db->query($con);
		$config = $config->row_array();
		
		$str = " SELECT tt_no FROM trouble_ticket
				 WHERE DATE_FORMAT( created_on , '%Y-%m-%d' ) = '".date('Y-m-d')."'
				 ORDER BY created_on DESC LIMIT 1 ;" ;
		$query = $this->db->query($str);
		if( $query->num_rows() > 0 ){
			$tt = $query->row_array();
			$tt_prefix = substr( $tt['tt_no'], 0, $length-1 );
			$tt_no = substr( $tt['tt_no'], $length-1 );
			$tt_suffix = str_pad( ($tt_no+1), $config['val'], 0, STR_PAD_LEFT);
			$tt_no = $tt_prefix . $tt_suffix ;
		}else{
			$tt_prefix = "1-" . date('dmY') . "-";
			$tt_suffix = str_pad( 1, $config['val'], 0, STR_PAD_LEFT);
			$tt_no = $tt_prefix . $tt_suffix ;
		}
		
		return $tt_no ; 
		
	}
	
	
}
