<?php

class Package_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}

	public function package_update($post_back,$username,$package_no)
	{	
		$this->load->model('router_model');
		$this->load->model('common_model');
		$this->load->model('package_model');

		$this->load->helper('change_log');

		$stamp_duty        	= !empty($post_back['stamp_duty']) ? $this->db->escape_str($post_back['stamp_duty']) : 0.00;
		$stop_service_after	= !empty($post_back['stop_service_after']) ? $this->db->escape_str($post_back['stop_service_after']) : 0;
		$yearly_billing    	= !empty($post_back['yearly_billing']) ? $this->db->escape_str($post_back['yearly_billing']) : 0;
		$yearly_charge     	= !empty($post_back['yearly_charge']) ? $this->db->escape_str($post_back['yearly_charge']) : 0;
		$domain            	= !empty($post_back['domain']) ? $this->db->escape_str($post_back['domain']) : 0;
		$building 		   	= !empty($post_back['building']) ? "'" . $this->db->escape_str($post_back['building']) . "'" : 'NULL';
		$product_category  	= !empty($post_back['product_category']) ? $this->db->escape_str($post_back['product_category']) : 0;
		$router_id         	= !empty($post_back['router_id']) ? $this->db->escape_str($post_back['router_id']) : 0;
		$delay_trial_start 	= !empty($post_back['delay_trial_start']) ? $this->db->escape_str($post_back['delay_trial_start']) : 0;
		$installation_auto 	= !empty($post_back['installation_auto']) ? $this->db->escape_str($post_back['installation_auto']) : 0;
		$one_time_charge_auto = !empty($post_back['one_time_charge_auto']) ? $this->db->escape_str($post_back['one_time_charge_auto']) : 0;
		$private		   	= !empty($post_back['private']) ? $this->db->escape_str($post_back['private']) : 0;
		$no_of_fixed_ip		= !empty($post_back['no_of_fixed_ip']) ? $this->db->escape_str($post_back['no_of_fixed_ip']) : 0;
		$no_of_email		= !empty($post_back['no_of_email']) ? $this->db->escape_str($post_back['no_of_email']) : 0;
		$lineno         	= !empty($post_back['lineno']) ? $this->db->escape_str($post_back['lineno']) : 0;

		if(empty($post_back['deposit'])) $post_back['deposit'] = 0.00;
		if(empty($post_back['monthly_charge'])) $post_back['monthly_charge'] = 0.00;
		if(empty($post_back['reactivate_fee'])) $post_back['reactivate_fee'] = 0.00;
		if(empty($post_back['installation'])) $post_back['installation'] = 0.00;
		if(empty($post_back['one_time_charge'])) $post_back['one_time_charge'] = 0.00;
		if(empty($post_back['yearly_charge'])) $post_back['yearly_charge'] = 0.00;
		if(empty($post_back['dom_rate'])) $post_back['dom_rate'] = 0.00;
		if(empty($post_back['int_rate'])) $post_back['int_rate'] = 0.00;
		if(empty($post_back['post_charge'])) $post_back['post_charge'] = 0.00;
		if(empty($post_back['bill_waive_period'])) $post_back['bill_waive_period'] = 0;
		if(empty($post_back['free_package_upgrade'])) $post_back['free_package_upgrade'] = 0;

		$packageInfo = $this->get_package($post_back['package_no']);

		$change_text = '';
		if (!empty($packageInfo)) {

			//prep
			$category_arr         = array_column($this->common_model->get_category_list() ?? [], 'name', 'category_code');
			$status_arr           = ['a' => 'Active', 'i' => 'Inactive'];
			$domain_arr           = array_column($this->common_model->get_domain_list() ?? [], 'name', 'domain_id');
			$bill_type_arr        = array_column($this->common_model->get_bill_type_list() ?? [], 'name', 'bill_type_id');
			$dealer_comm_type_arr = ['m' => 'Monthly', 'o' => 'One Time'];
			$router_arr           = array_column($this->router_model->get_routers() ?? [], 'name', 'id');
			$package_arr 		  = array_column($this->package_model->get_package_listing_all() ?? [], 'name', 'pkg_no');

			// action log
			$change_text .= compare_field_change('Name', $packageInfo['name'], $post_back['name'], [], true, $this->db);
			$change_text .= compare_field_change('Category', $packageInfo['category'], $post_back['category'], $category_arr, true, $this->db);
			$change_text .= compare_field_change('Status', $packageInfo['status'], $post_back['status'], $status_arr, true, $this->db);
			$change_text .= compare_field_change('Package', $packageInfo['private'], $private, ['0' => 'Public', '1' => 'Private'], true, $this->db);
			$change_text .= compare_field_change('Domain', $packageInfo['domain'], $post_back['domain'], $domain_arr, true, $this->db);
			$change_text .= compare_field_change('Bandwidth (Group)', $packageInfo['bandwidth'], $post_back['bandwidth'], [], true, $this->db);
			$change_text .= compare_field_change('No. of Fixed IP', $packageInfo['no_of_fixed_ip'], $no_of_fixed_ip, [], true, $this->db);
			$change_text .= compare_field_change('No. of Email', $packageInfo['no_of_email'], $no_of_email, [], true, $this->db);
			$change_text .= compare_field_change('Deposit', $packageInfo['deposit'], $post_back['deposit'], [], true, $this->db);
			$change_text .= compare_field_change('Stamp Duty', $packageInfo['stamp_duty'], $post_back['stamp_duty'], [], true, $this->db);
			$change_text .= compare_field_change('Monthly Charge', $packageInfo['monthly_charge'], $post_back['monthly_charge'], [], true, $this->db);
			$change_text .= compare_field_change('Yearly Charge', $packageInfo['yearly_charge'], $post_back['yearly_charge'], [], true, $this->db);
			$change_text .= compare_field_change('Installation Fees', $packageInfo['installation'], $post_back['installation'], [], true, $this->db);
			$change_text .= compare_field_change('Delay Trial Start', $packageInfo['delay_trial_start'], $delay_trial_start, ['0'=>'Off','1'=>'On'], true, $this->db);
			$change_text .= compare_field_change('Installation Auto Pay', $packageInfo['installation_auto'], $installation_auto, ['0'=>'Off','1'=>'On'], true, $this->db);
			$change_text .= compare_field_change('One Time Charge Auto Pay', $packageInfo['one_time_charge_auto'], $one_time_charge_auto, ['0'=>'Off','1'=>'On'], true, $this->db);
			$change_text .= compare_field_change('Package Months', $packageInfo['package_month'], $post_back['package_month'], [], true, $this->db);
			$change_text .= compare_field_change('Stop Service After Package Month', $packageInfo['stop_service_after'], $stop_service_after, ['0'=>'Off','1'=>'On'], true, $this->db);
			$change_text .= compare_field_change('Post Charges', $packageInfo['post_charge'], $post_back['post_charge'], [], true, $this->db);
			$change_text .= compare_field_change('Bill Type', $packageInfo['bill_type'], $post_back['bill_type'], $bill_type_arr, true, $this->db);
			$change_text .= compare_field_change('Yearly Billing', $packageInfo['yearly_billing'], $yearly_billing, ['0'=>'Off','1'=>'On'], true, $this->db);
			$change_text .= compare_field_change('Agent Comm. Type', $packageInfo['dealer_comm_type'], $post_back['dealer_comm_type'], $dealer_comm_type_arr, true, $this->db);
			$change_text .= compare_field_change('Agent Monthly Comm.', $packageInfo['dealer_monthly_comm'], $post_back['dealer_monthly_comm'], [], true, $this->db);
			$change_text .= compare_field_change('Agent Monthly Times', $packageInfo['dealer_monthly_times'], $post_back['dealer_monthly_times'], [], true, $this->db);
			$change_text .= compare_field_change('Agent One Time Comm.', $packageInfo['dealer_onetime_comm'], $post_back['dealer_onetime_comm'], [], true, $this->db);
			$change_text .= compare_field_change('Radius Framepool', $packageInfo['radius_framepool'], $post_back['radius_framepool'], [], true, $this->db);
			$change_text .= compare_field_change('Radius Ingress', $packageInfo['radius_ingress'], $post_back['radius_ingress'], [], true, $this->db);
			$change_text .= compare_field_change('Radius Egress', $packageInfo['radius_egress'], $post_back['radius_egress'], [], true, $this->db);
			$change_text .= compare_field_change('Radius Mikrotik', $packageInfo['radius_mikrotik'], $post_back['radius_mikrotik'], [], true, $this->db);
			$change_text .= compare_field_change('Domestic Rate', $packageInfo['dom_rate'], $post_back['dom_rate'], [], true, $this->db);
			$change_text .= compare_field_change('International Rate', $packageInfo['int_rate'], $post_back['int_rate'], [], true, $this->db);
			$change_text .= compare_field_change('Bill Waive Period', $packageInfo['bill_waive_period'], $post_back['bill_waive_period'], [], true, $this->db);
			$change_text .= compare_field_change('Free Package Upgrade', $packageInfo['free_package_upgrade'], $post_back['free_package_upgrade'], [], true, $this->db);
			$change_text .= compare_field_change('Reactivation Fee', $packageInfo['reactivate_fee'], $post_back['reactivate_fee'], [], true, $this->db);
			$change_text .= compare_field_change( 'Router', $packageInfo['router_id'], $this->input->post('router_id'), $router_arr, true, $this->db );
			$change_text .= compare_json_change('DIA Fields', $packageInfo['dia_vars'], $post_back['dia_vars']);
			$change_text .= compare_field_change('Upgrade Package', $packageInfo['upgrade_package_id'], $post_back['upgrade_package_id'], $package_arr, true, $this->db );
			$change_text .= compare_field_change('One Time Charge', $packageInfo['one_time_charge'], $post_back['one_time_charge'], [], true, $this->db);
		}

		$query_str = "UPDATE package SET " .
					"name = '" . $this->db->escape_str($post_back['name']) . "', " .
					"category = '" . $this->db->escape_str($post_back['category']) . "', " .
					"status = '" . $this->db->escape_str($post_back['status']) . "', " .
					"private = '" . $private . "', " .
					"domain = " . $domain . ", " .
					"bandwidth = '" . $this->db->escape_str($post_back['bandwidth']) . "', " .
					"no_of_fixed_ip = '" . $this->db->escape_str($no_of_fixed_ip) . "', " .
					"no_of_email = '" . $this->db->escape_str($no_of_email) . "', " .
					"deposit = '" . $this->db->escape_str($post_back['deposit']) . "', " .
					"stamp_duty = " . $stamp_duty . ", " .
					"monthly_charge = '" . $this->db->escape_str($post_back['monthly_charge']) . "', " .
					"yearly_charge = " . $yearly_charge . ", " .
					"installation = '" . $this->db->escape_str($post_back['installation']) . "', " .
					"installation_auto = '" . $installation_auto . "', " .
					"one_time_charge_auto = '" . $one_time_charge_auto . "', " .
					"delay_trial_start = '" . $delay_trial_start . "', " .
					"package_month = '" . $this->db->escape_str($post_back['package_month']) . "', " .
					"stop_service_after = " . $stop_service_after . ", " .
					"post_charge = '".$this->db->escape_str($post_back['post_charge'])  . "', " .
					"bill_type = '".$this->db->escape_str($post_back['bill_type'])  . "', " .
					"yearly_billing = " . $yearly_billing . ", " .
					"dealer_comm_type = '" . $this->db->escape_str($post_back['dealer_comm_type']) . "', " .
					"dealer_monthly_comm = '" . $this->db->escape_str($post_back['dealer_monthly_comm']) . "', " .
					"dealer_monthly_times = '" . $this->db->escape_str($post_back['dealer_monthly_times']) . "', " .
					"dealer_onetime_comm = '" . $this->db->escape_str($post_back['dealer_onetime_comm']) . "', " .
					"modified_by = '" . $username . "', " .
					"modified_date = now(), ".
					"radius_framepool = '" . $this->db->escape_str($post_back['radius_framepool']) . "', " .
					"radius_ingress = '" . $this->db->escape_str($post_back['radius_ingress']) . "', " .
					"radius_egress = '" . $this->db->escape_str($post_back['radius_egress']) . "', " .
					"radius_mikrotik = '" . $this->db->escape_str($post_back['radius_mikrotik']) . "', " .
					"dia_vars = '" . $this->db->escape_str($post_back['dia_vars']) . "', " . 
					"dom_rate = '" . $this->db->escape_str($post_back['dom_rate']) . "', " .
					"int_rate = '" . $this->db->escape_str($post_back['int_rate']) . "', " .
					"building = " . $building . ", " .
					"product_category = '" . $product_category . "', " . 
					"bill_waive_period = '" . $this->db->escape_str($post_back['bill_waive_period']) . "', " . 
					"free_package_upgrade = '" . $this->db->escape_str($post_back['free_package_upgrade']) . "', " . 
					"upgrade_package_id = '" . $this->db->escape_str($post_back['upgrade_package_id']) . "', " .
					"reactivate_fee = '" . $this->db->escape_str($post_back['reactivate_fee']) . "', " .  
					"router_id = '" . $router_id . "', lineno = '".$lineno."' " .  
					"WHERE package_no = '" . $this->db->escape_str($post_back['package_no']) . "' ";
		
		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);
		$method 		= $this->router->method;
		$action_desc	= 'A package('.$this->db->escape_str($post_back['name']).') has been edited.'.$change_text;
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		
		$this->db->query($query_str);
		if( $this->db->affected_rows() > 0 ){
			$str = " UPDATE customer SET package_name = '".$this->db->escape_str($post_back['name'])."' 
						WHERE package = '".$this->db->escape_str($post_back['package_no'])."' ";
			$this->db->query($str);
		}
		
		$package_no = $this->db->escape_str($package_no);
		return $package_no;
	}
	
	function package_insert($post_back,$username)
	{								
		$query_str 		= "SELECT MAX(p.package_no) as package_no FROM package p";
		$query 			= $this->db->query($query_str);
		$package_no 	= $return_val['package_no'] = $query->row()->package_no + 1;

		$stamp_duty        	= !empty($post_back['stamp_duty']) ? $this->db->escape_str($post_back['stamp_duty']) : 0.00;
		$stop_service_after	= !empty($post_back['stop_service_after']) ? $this->db->escape_str($post_back['stop_service_after']) : 0;
		$yearly_billing    	= !empty($post_back['yearly_billing']) ? $this->db->escape_str($post_back['yearly_billing']) : 0;
		$yearly_charge     	= !empty($post_back['yearly_charge']) ? $this->db->escape_str($post_back['yearly_charge']) : 0;
		$domain            	= !empty($post_back['domain']) ? $this->db->escape_str($post_back['domain']) : 0;
		$building			= !empty($post_back['building']) ? "'".$this->db->escape_str($post_back['building'])."'" : 'NULL';
		$product_category  	= !empty($post_back['product_category']) ? $this->db->escape_str($post_back['product_category']) : 0;
		$router_id         	= !empty($post_back['router_id']) ? $this->db->escape_str($post_back['router_id']) : 0;
		$installation_auto 	= !empty($post_back['installation_auto']) ? $this->db->escape_str($post_back['installation_auto']) : 0;
		$one_time_charge_auto = !empty($post_back['one_time_charge_auto']) ? $this->db->escape_str($post_back['one_time_charge_auto']) : 0;
		$delay_trial_start 	= !empty($post_back['delay_trial_start']) ? $this->db->escape_str($post_back['delay_trial_start']) : 0;
		$private		   	= !empty($post_back['private']) ? $this->db->escape_str($post_back['private']) : 0;
		$no_of_fixed_ip		= !empty($post_back['no_of_fixed_ip']) ? $this->db->escape_str($post_back['no_of_fixed_ip']) : 0;
		$no_of_email		= !empty($post_back['no_of_email']) ? $this->db->escape_str($post_back['no_of_email']) : 0;
		$lineno         	= !empty($post_back['lineno']) ? $this->db->escape_str($post_back['lineno']) : 0;
		
		if(empty($post_back['deposit'])) $post_back['deposit'] = 0.00;
		if(empty($post_back['monthly_charge'])) $post_back['monthly_charge'] = 0.00;
		if(empty($post_back['reactivate_fee'])) $post_back['reactivate_fee'] = 0.00;
		if(empty($post_back['installation'])) $post_back['installation'] = 0.00;
		if(empty($post_back['one_time_charge'])) $post_back['one_time_charge'] = 0.00;
		if(empty($post_back['yearly_charge'])) $post_back['yearly_charge'] = 0.00;
		if(empty($post_back['dom_rate'])) $post_back['dom_rate'] = 0.00;
		if(empty($post_back['int_rate'])) $post_back['int_rate'] = 0.00;
		if(empty($post_back['post_charge'])) $post_back['post_charge'] = 0.00;
		if(empty($post_back['bill_waive_period'])) $post_back['bill_waive_period'] = 0;
		if(empty($post_back['free_package_upgrade'])) $post_back['free_package_upgrade'] = 0;
		
		$query_str 		= "INSERT INTO package (package_no, name, category, status, private, domain, bandwidth, no_of_fixed_ip,
								no_of_email, deposit, stamp_duty, monthly_charge, 
								yearly_charge, installation, one_time_charge, installation_auto, one_time_charge_auto, delay_trial_start,
								package_month, stop_service_after, yearly_billing, post_charge, bill_type,
								dealer_comm_type, dealer_monthly_comm, dealer_monthly_times, dealer_onetime_comm, 
								created_by, created_date, radius_framepool, radius_ingress, radius_egress, radius_mikrotik, dia_vars, dom_rate, int_rate, building, product_category, bill_waive_period, free_package_upgrade, upgrade_package_id, reactivate_fee, router_id, lineno) VALUES (".
								"'" . $package_no . "', ".
								"'" . $this->db->escape_str($post_back['name']) . "', ".
								"'" . $this->db->escape_str($post_back['category']) . "', ".
								"'" . $this->db->escape_str($post_back['status']) . "', ".
								"'" . $private . "', ".
								"" . $domain . ", ".
								"'" . $this->db->escape_str($post_back['bandwidth']) . "', ".
								"'" . $this->db->escape_str($no_of_fixed_ip) . "', ".
								"'" . $this->db->escape_str($no_of_email) . "', ".
								"'" . $this->db->escape_str($post_back['deposit']) . "', ".
								"" . $stamp_duty . ", ".
								"'" . $this->db->escape_str($post_back['monthly_charge']) . "', ".
								"" . $yearly_charge . ", ".
								"'" . $this->db->escape_str($post_back['installation']) . "', ".
								"'" . $this->db->escape_str($post_back['one_time_charge']) . "', ".
								"'" . $installation_auto . "', ".
								"'" . $one_time_charge_auto . "', ".
								"'" . $delay_trial_start . "', ".
								"'" . $this->db->escape_str($post_back['package_month']) . "', ".
								"" . $stop_service_after . ", ".
								"" . $yearly_billing . ", ".
								"'" . $this->db->escape_str($post_back['post_charge']) . "', ".
								"'" . $this->db->escape_str($post_back['bill_type']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_comm_type']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_monthly_comm']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_monthly_times']) . "', ".
								"'" . $this->db->escape_str($post_back['dealer_onetime_comm']) . "', ".
								"'" . $username . "', now(), '" 
								. $this->db->escape_str($post_back['radius_framepool']) . "', '" 
								. $this->db->escape_str($post_back['radius_ingress']) . "', '" 
								. $this->db->escape_str($post_back['radius_egress']) . "', '" 
								. $this->db->escape_str($post_back['radius_mikrotik']) . "', 
								'".$this->db->escape_str($post_back['dia_vars'])."', 
								'".$this->db->escape_str($post_back['dom_rate'])."',
								'".$this->db->escape_str($post_back['int_rate'])."',
								".$building.",
								'".$product_category."',
								'".$this->db->escape_str($post_back['bill_waive_period'])."',
								'".$this->db->escape_str($post_back['free_package_upgrade'])."',
								'".$this->db->escape_str($post_back['upgrade_package_id'])."', 
								'".$this->db->escape_str($post_back['reactivate_fee'])."',										
								'".$router_id."', '".$lineno."')";										
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new package ('.$this->db->escape_str($post_back['name']).') has been added';	
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
	
	function get_package_listing($txt_search,$page_item_no,$max_page_item_no,$query_where)
	{		

		$txt_search = $this->db->escape_str($txt_search);
		
		$return_val['total_row'] = 0;		
		$query_str 	= "SELECT count(p.package_no) AS total_row 
					FROM package p 
					WHERE deleted_date IS NULL AND p.name LIKE '%$txt_search%' " .
					$query_where;
					
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$query_str 	= "SELECT p.* , scc.name as category_name 
					FROM package p 
					INNER JOIN sys_customer_category scc ON p.category = scc.category_code 
					WHERE deleted_date IS NULL AND p.name LIKE '%$txt_search%' " .
					$query_where .
					"ORDER BY CASE 
       	 				WHEN p.lineno=0 THEN 99999 ELSE p.lineno 
    					END ASC, p.monthly_charge ASC, p.name ASC " . 
					"LIMIT $page_item_no, ". $max_page_item_no;
		$query = $this->db->query($query_str);
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		
		return $return_val;
	}

	function get_package_listing_interested($package_type, $building = null)
	{
		if (empty($package_type)) {
			return array();
		}

		$where = "";
		if ( $package_type == 'r' ) {
			//load all residential package
			$where .= " AND category = 'r' ";
		} else if ($package_type == 'b') {
			//load all business package
			$where .= " AND category = 'b' ";
		} else if ($package_type == 'o') {
			//load all business and DIA etc package
			$where .= " AND category != 'r' ";
		} else {
			return array();
		}

		if(!empty($building)){
			$where .= " AND (building LIKE '%".$this->db->escape_str($building).",%'  OR building IS NULL ) ";
		}else{
			$where .= " AND building IS NULL ";
		}

		$sql = "SELECT * FROM `package` WHERE `status` = 'a' AND `private` != 1 AND `deleted_by` IS NULL AND `deleted_date` IS NULL ".$where . " ORDER BY CASE WHEN `lineno` = 0 THEN 9999 ELSE `lineno` END ASC, `monthly_charge` ASC, `name` ASC";
		$query = $this->db->query( $sql );
		$results = $query->result_array();

		return $results;
	}

	function get_package_listing_all()
	{
		$sql = "SELECT p.*, stt.percent AS tax_percent 
		FROM `package` p 
		LEFT JOIN sys_bill_type sbt ON (p.bill_type = sbt.bill_type_id) 
		LEFT JOIN sys_tax_type stt ON (sbt.tax_code = stt.code) 
		WHERE p.`status` = 'a' ";

		$query = $this->db->query( $sql ) ;
		$results = $query->result_array();

		$return = array();
		foreach ($results as $row) {
			$return[$row['package_no']] = $row;
		}

		return $return;
	}

	function get_package_listing_all_active()
	{
		$sql = "SELECT * FROM `package` WHERE `status` = 'a' ";

		$query = $this->db->query( $sql ) ;
		$results = $query->result_array();

		$return = array();
		foreach ($results as $row) {
			$return[$row['package_no']] = $row;
		}

		return $return;
	}

	/*function get_radius_info($bandwidth)
	{
		$return = array();
		$return['radius_framepool'] = '';
		$return['radius_mikrotik'] = '';
		$this->radius_db = $this->load->database('radius',true,false);
		$sql = " SELECT * FROM `radgroupreply` WHERE groupname = '".$this->db->escape_str($bandwidth)."'";
		$query = $this->radius_db->query( $sql ) ;
		$results = $query->result_array();
		foreach ($results as $result) {
			if ($result['attribute'] == 'Frame-Pool') {
				$return['radius_framepool'] = $result['value'];
			} else if ($result['attribute'] == 'Mikrotik-Rate-Limit') {
				$return['radius_mikrotik'] = $result['value'];
			}
		}

		return $return;
	}*/
	
	function get_package($package_no='')
	{
		$query = $this->db->query("SELECT * FROM package 
									WHERE package_no='".$this->db->escape_str($package_no)."' LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';

			//$radius = $this->get_radius_info($return_val['bandwidth']);

		}else{
			$return_val['package_no'] = '';
			$return_val['name'] = '';
			$return_val['category'] = 'r';
			$return_val['status'] = 'r';
			$return_val['private'] = '0';
			$return_val['domain'] = '';
			$return_val['bandwidth'] = '';
			$return_val['no_of_fixed_ip'] = '0';
			$return_val['no_of_email'] = '0';
			$return_val['deposit'] = '';
			$return_val['stamp_duty'] = '';
			$return_val['monthly_charge'] = '';
			$return_val['yearly_charge'] = '';
			$return_val['reactivate_fee'] = '';
			$return_val['installation'] = '';
			$return_val['one_time_charge'] = '';
			$return_val['delay_trial_start'] = '0';
			$return_val['installation_auto'] = '1';
			$return_val['package_month'] = '12';
			$return_val['stop_service_after'] = '';
			$return_val['yearly_billing'] = '';
			$return_val['post_charge'] = '0.00';
			$return_val['dealer_comm_type'] = 'm';
			$return_val['dealer_monthly_comm'] = '0';
			$return_val['dealer_monthly_times'] = '0';
			$return_val['dealer_onetime_comm'] = '0';
			$return_val['bill_type'] = '';
			$return_val['dia_vars'] = json_encode(array());
			$return_val['btn_delete']='disabled';

			$return_val['product_category'] = '0';
			$return_val['building'] = '';

			$return_val['dom_rate'] = $_SESSION['config']['default_dom_call_rate'];
			$return_val['int_rate'] = $_SESSION['config']['default_int_call_rate'];

			$return_val['bill_waive_period'] = 0;
			$return_val['free_package_upgrade'] = 0;
			$return_val['upgrade_package_id'] = 0;
			$return_val['router_id'] = '';

			$return_val['radius_framepool'] = '';
			$return_val['radius_ingress'] = '';
			$return_val['radius_egress'] = '';
			$return_val['radius_mikrotik'] = '';

			$return_val['lineno'] = 0;
		}
		
		return $return_val;
	}
	
	function cisco_package_update(){
		$this->radius_db = $this->load->database('radius',true,false);
		
		$local_packages = array();
		$package = 	"	SELECT MIN(package_no) AS package_no, MIN(name) AS `name`, category, 
								CASE WHEN category = 'r' THEN bandwidth
									 WHEN category = 'b' THEN CONCAT( 'BIZ', bandwidth ) 
									 ELSE bandwidth END AS bandwidth 
						FROM package 
						WHERE bandwidth != '' AND deleted_date IS NULL
						GROUP BY category, bandwidth ; ";						
		$query = $this->db->query( $package ) ;
		
		if( $query->num_rows() > 0 ){
			$results = $query->result_array();
			foreach( $results AS $key => $val ){
				$local_packages[] = $val['bandwidth'];
			}
		}
		
		$cisco_package = array();
		$radius_package = " SELECT DISTINCT( groupname ) AS groupname FROM radgroupreply ; "; 
		$query2 = $this->radius_db->query( $radius_package ) ;

		if( $query2->num_rows() > 0 ){
			$results2 = $query2->result_array();
			foreach( $results2 AS $key => $val ){
				$cisco_packages[] = $val['groupname'];
			}
		}
		
		//remove cisco packages if not found in local packages
		/*
		if( !empty( $cisco_packages ) ){
			foreach( $cisco_packages AS $key => $val ){
				if( !in_array( $val, $local_packages ) ){
					$del = " DELETE FROM `radgroupreply` WHERE `groupname` = '".$this->radius_db->escape_str( $val )."' ; ";
					$this->radius_db->query( $del );
				}
			}
		}
		*/
		
		//insert local packages into cisco packages		
		if( !empty( $local_packages ) ){
			
			foreach( $local_packages AS $key => $val ){
				
				$insert_values = '';
				
				/*$check1 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val)."' 
							AND `attribute` = 'Framed-Compression' ; " ;
				$query1 = $this->radius_db->query( $check1 ) ;
				if( $query1->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val )."' , 
										'Framed-Compression' , ':=', 'Van-Jacobsen-TCP-IP' ) " ;
				}*/

				$check2 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val)."' 
							AND `attribute` = 'Framed-Protocol' ; " ;				
				$query2 = $this->radius_db->query( $check2 ) ;
				if( $query2->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val )."' , 
										'Framed-Protocol' , ':=', 'PPP' ) " ;
				}
				
				$check3 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val)."' 
							AND `attribute` = 'Service-Type' ; " ;
				$query3 = $this->radius_db->query( $check3 ) ;
				if( $query3->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val )."' , 
										'Service-Type' , ':=', 'Framed-User' ) " ;
				}
				
				$check4 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val)."' 
							AND `attribute` = 'Framed-MTU' ; " ;
				$query4 = $this->radius_db->query( $check4 ) ;
				if( $query4->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val )."' , 
										'Framed-MTU' , ':=', '1500' ) " ;
				}
				
				
				$check5 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val)."' 
							AND `attribute` = 'Cisco-AVPair' ; " ;
				$query5 = $this->radius_db->query( $check5 ) ;
				if( $query5->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val )."' , 
										'Cisco-AVPair' , ':=', 
										'lcp:interface-config=service-policy input ".$this->radius_db->escape_str( $val )."' ),
										( '".$this->radius_db->escape_str( $val )."' , 
										'Cisco-AVPair' , ':=', 
										'lcp:interface-config=service-policy output ".$this->radius_db->escape_str( $val )."' )
									  " ;
				}
				
				if( $insert_values != '' ){

					$insert = " INSERT INTO radgroupreply ( `groupname`, `attribute`, `op`, `value` )
								VALUES " . $insert_values ; 
					$this->radius_db->query( $insert ) ;
					
				}
				
			}
		}

				
	}

	function radius_package_update(){
		$this->radius_db = $this->load->database('radius',true,false);

		$local_packages = array();
		$package = 	"	SELECT MIN(package_no) AS package_no, MIN(name) AS `name`, category, bandwidth, radius_framepool, radius_mikrotik  
						FROM package 
						WHERE bandwidth != '' AND deleted_date IS NULL
						GROUP BY package_no, `name`, category, bandwidth, radius_framepool, radius_mikrotik ; ";						
		$query = $this->db->query( $package ) ;
		
		if( $query->num_rows() > 0 ){
			$results = $query->result_array();
			foreach( $results AS $key => $val ){
				$local_packages[$val['bandwidth']]['bandwidth'] = $val['bandwidth'];
				$local_packages[$val['bandwidth']]['radius_framepool'] = $val['radius_framepool'];
				$local_packages[$val['bandwidth']]['radius_mikrotik'] = $val['radius_mikrotik'];
			}
		}

		$cisco_package = array();
		$radius_package = " SELECT DISTINCT( groupname ) AS groupname FROM radgroupreply ; "; 
		$query2 = $this->radius_db->query( $radius_package ) ;

		if( $query2->num_rows() > 0 ){
			$results2 = $query2->result_array();
			foreach( $results2 AS $key => $val ){
				$radius_packages[] = $val['groupname'];
			}
		}

		//supposed to remove any that is not in use but was commented out

		if( !empty( $local_packages ) ){
			
			foreach( $local_packages AS $bandwidth => $val ){
				
				$insert_values = '';

				$check2 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
							AND `attribute` = 'Session-Timeout' ; " ;				
				$query2 = $this->radius_db->query( $check2 ) ;
				if( $query2->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
										'Session-Timeout' , ':=', '864000' ) " ;
				}

				$check3 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
							AND `attribute` = 'Framed-Pool' ; " ;				
				$query3 = $this->radius_db->query( $check3 ) ;
				if( $query3->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
										'Framed-Pool' , ':=', '".$this->radius_db->escape_str( $val['radius_framepool'] )."' ) " ;
				}

				$check4 = "	SELECT `groupname`,`attribute`,`op`,`value`
							FROM radgroupreply 
							WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
							AND `attribute` = 'Mikrotik-Rate-Limit' ; " ;				
				$query4 = $this->radius_db->query( $check4 ) ;
				if( $query4->num_rows() == 0 ){
					$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
					$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
										'Mikrotik-Rate-Limit' , ':=', '".$this->radius_db->escape_str( $val['radius_mikrotik'] )."' ) " ;
				}

				if( $insert_values != '' ){

					$insert = " INSERT INTO radgroupreply ( `groupname`, `attribute`, `op`, `value` )
								VALUES " . $insert_values ; 
					$this->radius_db->query( $insert ) ;
					
				}

			}
		}

	}

	function varied_package_update() {
		$this->radius_db = $this->load->database('radius',true,false);

		$local_packages = array();
		$package = 	"	SELECT MIN(package_no) AS package_no, MIN(name) AS `name`, category, router_id, bandwidth, radius_framepool, radius_mikrotik, radius_ingress, radius_egress  
						FROM package 
						WHERE bandwidth != '' AND deleted_date IS NULL
						GROUP BY package_no, `name`, category, router_id, bandwidth, radius_framepool, radius_mikrotik ; ";						
		$query = $this->db->query( $package ) ;
		
		if( $query->num_rows() > 0 ){
			$results = $query->result_array();
			foreach( $results AS $key => $val ){
				$local_packages[$val['bandwidth']]['bandwidth'] = $val['bandwidth'];
				$local_packages[$val['bandwidth']]['radius_framepool'] = $val['radius_framepool'];
				$local_packages[$val['bandwidth']]['radius_mikrotik'] = $val['radius_mikrotik'];
				$local_packages[$val['bandwidth']]['router_id'] = $val['router_id'];
				$local_packages[$val['bandwidth']]['radius_ingress'] = $val['radius_ingress'] ?? '';
				$local_packages[$val['bandwidth']]['radius_egress'] = $val['radius_egress'] ?? '';
			}
		}

		if( !empty( $local_packages ) ){
			
			foreach( $local_packages AS $bandwidth => $val ){

				$insert_values = '';

				$update_queries = array();

				$delete_queries = array();

				if (!empty($val['router_id'])) {
					//get router properties
					$this->load->model('router_model');
					$router_properties = $this->router_model->get_router_params($val['router_id']);

					//based on router properties
					//_debug_array(json_decode($router_properties));

					$router_properties = json_decode($router_properties, true);

					if (empty($router_properties) || !is_array($router_properties)) {
						log_message('error', 'Invalid router properties for router_id: ' . $val['router_id']);
						continue;
					}

					$group_attribute = array();

					foreach ($router_properties ?? [] as $property) {

						$the_value = $property['value'];

						//fixed params are coded in [] eg [FRAMEPOOL]

						$the_value = str_replace("[FRAMEPOOL]", $val['radius_framepool'], $the_value);
						$the_value = str_replace("[INGRESS]", $val['radius_ingress'] ?? '', $the_value);
						$the_value = str_replace("[EGRESS]", $val['radius_egress'] ?? '', $the_value);
						$the_value = str_replace("[MIKROTIK]", $val['radius_mikrotik'], $the_value);

						if (trim($the_value) === '') continue;

						$checkx = "	SELECT `groupname`,`attribute`,`op`,`value`
									FROM radgroupreply 
									WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
									AND `attribute` = '".$this->radius_db->escape_str( $property['attribute'] )."' ; " ;				
						$queryx = $this->radius_db->query( $checkx ) ;

						if( $queryx->num_rows() == 0 ){
							$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
							$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
												'".$this->radius_db->escape_str( $property['attribute'] )."' , '".$this->radius_db->escape_str( $property['operation'] )."', '".$this->radius_db->escape_str( $the_value )."' ) " ;
						} else {
							//got record, do update
							$update_queries[] = "UPDATE `radgroupreply` SET `op` = '".$this->radius_db->escape_str( $property['operation'] )."', `value` = '".$this->radius_db->escape_str( $the_value )."' WHERE groupname = '".$this->radius_db->escape_str($val['bandwidth'])."' AND attribute = '".$this->radius_db->escape_str( $property['attribute'] )."'";
						}

						$group_attribute[] = "'".$property['attribute']."'";

					}

					if (!empty($group_attribute)) {
						$delete_queries[] = "
							DELETE FROM `radgroupreply`
							WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
							AND (`attribute` NOT IN (".implode(",", $group_attribute)."));
						";
					}
				} else {
					//default

					$check2 = "	SELECT `groupname`,`attribute`,`op`,`value`
								FROM radgroupreply 
								WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
								AND `attribute` = 'Session-Timeout' ; " ;				
					$query2 = $this->radius_db->query( $check2 ) ;
					if( $query2->num_rows() == 0 ){
						$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
						$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
											'Session-Timeout' , ':=', '864000' ) " ;
					}

					$check3 = "	SELECT `groupname`,`attribute`,`op`,`value`
								FROM radgroupreply 
								WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
								AND `attribute` = 'Framed-Pool' ; " ;				
					$query3 = $this->radius_db->query( $check3 ) ;
					if( $query3->num_rows() == 0 ){
						$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
						$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
											'Framed-Pool' , ':=', '".$this->radius_db->escape_str( $val['radius_framepool'] )."' ) " ;
					}

					$check4 = "	SELECT `groupname`,`attribute`,`op`,`value`
								FROM radgroupreply 
								WHERE `groupname` = '".$this->radius_db->escape_str($val['bandwidth'])."' 
								AND `attribute` = 'Mikrotik-Rate-Limit' ; " ;				
					$query4 = $this->radius_db->query( $check4 ) ;
					if( $query4->num_rows() == 0 ){
						$insert_values = $insert_values != '' ? $insert_values . "," : $insert_values ;
						$insert_values .= " ( '".$this->radius_db->escape_str( $val['bandwidth'] )."' , 
											'Mikrotik-Rate-Limit' , ':=', '".$this->radius_db->escape_str( $val['radius_mikrotik'] )."' ) " ;
					}

				}

				if( $insert_values != '' ){

					$insert = " INSERT INTO radgroupreply ( `groupname`, `attribute`, `op`, `value` )
								VALUES " . $insert_values ; 
					$this->radius_db->query( $insert ) ;
					
				}

				if (!empty($update_queries)) {

					foreach ($update_queries as $sql_query) {
						try { 
							$this->radius_db->query( $sql_query ) ;
						} catch (Exception $e) {
							log_message('error', 'Package Edit, Radius Params: '.$e->getMessage());
						}
					}

				}

				if (!empty($delete_queries)) {

					foreach ($delete_queries as $sql_query) {
						try { 
							$this->radius_db->query( $sql_query ) ;
						} catch (Exception $e) {
							log_message('error', 'Package Edit, Radius Params: '.$e->getMessage());
						}
					}

				}

			}

		}

	}
	
	function check_customer_package_in_use($package_no, $exclude_customer_no = [])
	{
		$package_no = $this->db->escape_str($package_no);

		$sql = "
			SELECT *
			FROM customer
			WHERE (package = '{$package_no}' OR new_package_id = '{$package_no}')
		";

		if (!empty($exclude_customer_no)) {
			$escaped_list = array_map(function($cust_no) {
				return "'" . $this->db->escape_str($cust_no) . "'";
			}, $exclude_customer_no);

			$sql .= " AND customer_no NOT IN (" . implode(",", $escaped_list) . ")";
		}

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result_array();
		}

		return false;
	}

	function moveup($package_no)
	{
		//$key = $package_no

		$this->db->trans_begin();

		$qry="select lineno from `package` where true and package_no = ?";
        $query=$this->db->query($qry, array($package_no));
        $return_val = $query->row_array();

        //return false if lineno=0
        if (!is_array($return_val) || !isset($return_val['lineno']) || ($return_val['lineno']<=1))
        	return false;

	    $this->db->query("set @package_no=?", array($package_no));

	    $this->db->query("select c1.package_no,c1.lineno,c2.package_no,c2.lineno into @package_no1,@line1,@package_no2,@line2 from package c1 
	    	join (
	    		select package_no,lineno from package 
	    		where lineno<(
	    			select lineno from 
	        		package where true and package_no = @package_no
	        	) order by lineno desc limit 1
	        ) c2 
	        where true and c1.package_no = @package_no");

        $this->db->query("insert into `package` (
        	package_no,lineno,`name`,category,`status`,domain,bandwidth,no_of_fixed_ip,no_of_email,deposit,stamp_duty,monthly_charge,yearly_charge,installation,reactivate_fee,installation_auto,package_month,stop_service_after,yearly_billing,post_charge,bill_type,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm,dia_vars,dom_rate,int_rate,building,created_by,created_date,modified_by,modified_date,deleted_by,deleted_date,radius_framepool,radius_egress,radius_ingress,radius_mikrotik,product_category,bill_waive_period,free_package_upgrade,upgrade_package_id,router_id,private
        	) values 
        	(@package_no1,@line2,`name`,category,`status`,domain,bandwidth,no_of_fixed_ip,no_of_email,deposit,stamp_duty,monthly_charge,yearly_charge,installation,reactivate_fee,installation_auto,package_month,stop_service_after,yearly_billing,post_charge,bill_type,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm,dia_vars,dom_rate,int_rate,building,created_by,created_date,modified_by,modified_date,deleted_by,deleted_date,radius_framepool,radius_egress,radius_ingress,radius_mikrotik,product_category,bill_waive_period,free_package_upgrade,upgrade_package_id,router_id,private),
        	(@package_no2,@line1,`name`,category,`status`,domain,bandwidth,no_of_fixed_ip,no_of_email,deposit,stamp_duty,monthly_charge,yearly_charge,installation,reactivate_fee,installation_auto,package_month,stop_service_after,yearly_billing,post_charge,bill_type,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm,dia_vars,dom_rate,int_rate,building,created_by,created_date,modified_by,modified_date,deleted_by,deleted_date,radius_framepool,radius_egress,radius_ingress,radius_mikrotik,product_category,bill_waive_period,free_package_upgrade,upgrade_package_id,router_id,private) 
        	on duplicate key update lineno=values(lineno)");

		//db commit or rollback
		if ($this->db->trans_status() === true)
		{
			$this->db->trans_commit();
		}else{
			$this->db->trans_rollback();
			$db_error = $this->db->error();
			log_message('error', 'Move Up Package Failed: '.$db_error);
		}

	}

	function movedown($package_no)
	{
		//$key = $package_no

		$this->db->trans_begin();

        $qry="select c1.lineno,maxline from `package` c1 
        join (select ifnull(max(lineno),0) as maxline from package) cm 
        where true and c1.`package_no`=?";
        $query=$this->db->query($qry, array($package_no));
        $return_val = $query->row_array();

        //return false if lineno=0
        if (!is_array($return_val) || !isset($return_val['lineno']) || ($return_val['lineno']==$return_val['maxline']))
        	return false;

	    $this->db->query("set @package_no=?", array($package_no));

	    $this->db->query("select c1.package_no,c1.lineno,c2.package_no,c2.lineno into @package_no1,@line1,@package_no2,@line2 from package c1 
	    	join (
	    		select package_no,lineno from package 
	    		where lineno>(
	    			select lineno from 
	        		package where true and package_no = @package_no
	        	) order by lineno limit 1
	        ) c2 
	        where true and c1.package_no = @package_no");

        $this->db->query("insert into `package` (
        	package_no,lineno,`name`,category,`status`,domain,bandwidth,no_of_fixed_ip,no_of_email,deposit,stamp_duty,monthly_charge,yearly_charge,installation,reactivate_fee,installation_auto,package_month,stop_service_after,yearly_billing,post_charge,bill_type,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm,dia_vars,dom_rate,int_rate,building,created_by,created_date,modified_by,modified_date,deleted_by,deleted_date,radius_framepool,radius_egress,radius_ingress,radius_mikrotik,product_category,bill_waive_period,free_package_upgrade,upgrade_package_id,router_id,private
        	) values 
        	(@package_no1,@line2,`name`,category,`status`,domain,bandwidth,no_of_fixed_ip,no_of_email,deposit,stamp_duty,monthly_charge,yearly_charge,installation,reactivate_fee,installation_auto,package_month,stop_service_after,yearly_billing,post_charge,bill_type,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm,dia_vars,dom_rate,int_rate,building,created_by,created_date,modified_by,modified_date,deleted_by,deleted_date,radius_framepool,radius_egress,radius_ingress,radius_mikrotik,product_category,bill_waive_period,free_package_upgrade,upgrade_package_id,router_id,private),
        	(@package_no2,@line1,`name`,category,`status`,domain,bandwidth,no_of_fixed_ip,no_of_email,deposit,stamp_duty,monthly_charge,yearly_charge,installation,reactivate_fee,installation_auto,package_month,stop_service_after,yearly_billing,post_charge,bill_type,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm,dia_vars,dom_rate,int_rate,building,created_by,created_date,modified_by,modified_date,deleted_by,deleted_date,radius_framepool,radius_egress,radius_ingress,radius_mikrotik,product_category,bill_waive_period,free_package_upgrade,upgrade_package_id,router_id,private) 
        	on duplicate key update lineno=values(lineno)");

		//db commit or rollback
		if ($this->db->trans_status() === true)
		{
			$this->db->trans_commit();
		}else{
			$this->db->trans_rollback();
			$db_error = $this->db->error();
			log_message('error', 'Move Up Package Failed: '.$db_error);
		}
	}
	
}
