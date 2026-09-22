<?php
class Customer_model extends MY_Model{

	protected $_table = 'customer';
	protected $_primary_key = 'customer_no';

	private $e_key = "1nf0n4l";

	public function __construct()
	{
		parent::__construct();
	}
	
	function create_activation_fee($customer_no,$username,$package)
	{

		$this->load->model('common_model');
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];
		$current_day = date('j');

		$tranx_date = date('Y-m-d');

		//Insert Installatin, deposit, & stamp_duty
		$query_str = "SELECT p.installation, p.deposit, p.stamp_duty, p.name as package_name , p.installation_auto, p.one_time_charge, p.one_time_charge_auto 
						FROM package p 
						WHERE p.package_no = '" . $package . "' LIMIT 1";
		$query = $this->db->query($query_str);
		
		if ( $query->num_rows() > 0 ) 
		{
			$row = $query->row_array();
			$this->load->model('adjustment_model');	
			$this->load->model('payment_model');
			
			$remark = "INSTALLATION";
			$this->adjustment_model->insert_adj('i', $customer_no, '', '', $tranx_date, '2', 'dr', $row['installation'], $remark, 1);

			$this->adjustment_model->insert_adj('i', $customer_no, '', '', $tranx_date, '2', 'dr', $row['one_time_charge'], "ONE TIME CHARGE", 1);

			$query_str = " SELECT sys_tax_type.percent 
							FROM sys_bill_type 
							LEFT JOIN sys_tax_type ON sys_bill_type.tax_code = sys_tax_type.code
							WHERE sys_bill_type.name = 'Installation Fee' ";
			$tax = $this->db->query($query_str);
			$taxes = $tax->row_array();
			$taxed_installation =  number_format( $row['installation'] + $row['installation'] * $taxes['percent'] / 100 , 2 , "." , "" ) ;

			$query_str_otc = "SELECT stt.percent FROM sys_bill_type sbt LEFT JOIN sys_tax_type stt ON sbt.tax_code = stt.code WHERE sbt.name = 'One Time Charge'";
			$one_time_charge_tax = $this->db->query($query_str_otc)->row_array();
			$taxed_one_time_charge = number_format($row['one_time_charge'] + $row['one_time_charge'] * $taxes['percent'] / 100, 2, ".", "");
			
			if( $row['installation_auto'] == '1' ){
				//~ echo $customer_no; exit;
				$this->payment_model->payment_insert('1', '2', $customer_no, $tranx_date, $tranx_date, $taxed_installation, '', $remark, $username);
			}

			if($row['one_time_charge_auto'] == '1') {
				$this->payment_model->payment_insert('1', '2', $customer_no, $tranx_date, $tranx_date, $taxed_one_time_charge, '', 'ONE TIME CHARGE', $username);
			}
		}
	}
	
	function get_autocomplete_load_customer($keyword)
	{
		$keyword = $this->db->escape_str($keyword);
		$query_str = "	SELECT c.customer_no, c.name, c.tel_num, c.mobile_num,  c.package, c.category , 
						pkg.name as package_name, pkg.monthly_charge, c.product_category , 
						CONCAT( c.inst_addr1,' ', c.inst_addr2,' ',c.inst_postcode,' ',c.inst_city,' ',s.name ) AS cust_address , c.pic_name, c.tel_num, c.fax_num, c.mobile_num, c.email_1, c.email_2,
						c.nric, c.passport, c.date_of_birth , c.gender, c.race , cs.status AS latest_status, c.inst_phone,
						CASE WHEN c.status = 'r' THEN 'Registered' 
							 WHEN c.status = 't' THEN 'Terminated'
							 WHEN c.status = 'c' THEN 'Cancelled' 
							 WHEN c.status = 'p' THEN 'Pending' 
							 WHEN c.status = 's' THEN 'Suspended' 
							 ELSE '' END AS account_status
						FROM customer c 
						LEFT JOIN package pkg ON c.package = pkg.package_no 
						LEFT JOIN sys_state s ON c.inst_state = s.state_code 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						LEFT JOIN profile p ON c.profile_id = p.acc_id 
						WHERE c.customer_no LIKE '%$keyword%' OR c.name LIKE '%$keyword%' OR p.pic_email_1 LIKE '%$keyword%' 
						ORDER BY c.customer_no LIMIT 10 ";
		$query = $this->db->query($query_str);
		return $query->result_array(); 

	}
	
	function get_deposit_invoice($idx)
	{
		$select_c_field =" d.customer_no, ";
		$select_c_field .=" d.idx, ";
		$select_c_field .=" d.deposit_date, ";
		$select_c_field .=" d.deposit, ";
		$select_c_field .=" d.remark ";

		$query_str_1= "SELECT ".$select_c_field." FROM customer_deposit d WHERE d.idx='".$this->db->escape_str($idx)."' ";
		$query_1 = $this->db->query($query_str_1);

		if ($query_1->num_rows() > 0)	{
			$return_val['deposit'] = $query_1->result_array();
			$return_val['deposit'][0]['bill_no'] = add_zero($return_val['deposit'][0]['idx']);
			$return_val['deposit'][0]['deposit_date'] = convert_date_1($return_val['deposit'][0]['deposit_date']);
			$return_val['deposit'][0]['charge'] = $return_val['deposit'][0]['deposit'];
			$return_val['deposit'][0]['amount'] = $return_val['deposit'][0]['deposit'];
			$return_val['deposit'][0]['tax_charge'] = '0';
		}		
		$customer_no = $return_val['deposit'][0]['customer_no'];		
		$query_str_2= "SELECT d.inst_unit_no, d.inst_addr1, d.inst_addr2, d.inst_addr3, d.inst_city, d.inst_postcode, d.inst_state, p.acc_name AS name, p.bill_unit_no, p.bill_addr_1, p.bill_addr_2, p.bill_addr_3, p.bill_postcode, p.bill_city, p.bill_state, p.pic_name, d.payment_term, d.reg_no, d.package_name, d.tel_num, d.fax_num, p.acc_email, d.currency_code FROM customer d LEFT JOIN profile p ON p.acc_id = d.profile_id WHERE d.customer_no='".$customer_no."' ";
		$query_2 = $this->db->query($query_str_2);
		
		if ($query_2->num_rows() > 0)	{
			$return_val['customer'] = $query_2->result_array();
			
			$return_val['deposit'][0]['display_name'] 		= $return_val['customer'][0]['name'];
			$return_val['deposit'][0]['display_unit_no'] 		= $return_val['customer'][0]['inst_unit_no'];
			$return_val['deposit'][0]['display_addr1'] 		= $return_val['customer'][0]['inst_addr1'];
			$return_val['deposit'][0]['display_addr2'] 		= $return_val['customer'][0]['inst_addr2'];
			$return_val['deposit'][0]['display_addr3'] 		= $return_val['customer'][0]['inst_addr3'];
			$return_val['deposit'][0]['display_city'] 		= $return_val['customer'][0]['inst_city'];
			$return_val['deposit'][0]['display_postcode'] 	= $return_val['customer'][0]['inst_postcode'];
			$return_val['deposit'][0]['display_state'] 		= $return_val['customer'][0]['inst_state'];
			
			
			if(!empty( $return_val['customer'][0]['bill_addr_1'] )){				
				$return_val['deposit'][0]['display_name'] 		= $return_val['customer'][0]['name'];
				$return_val['deposit'][0]['display_unit_no'] 		= $return_val['customer'][0]['bill_unit_no'];
				$return_val['deposit'][0]['display_addr1'] 		= $return_val['customer'][0]['bill_addr_1'];
				$return_val['deposit'][0]['display_addr2'] 		= $return_val['customer'][0]['bill_addr_2'];
				$return_val['deposit'][0]['display_addr3'] 		= $return_val['customer'][0]['bill_addr_3'];
				$return_val['deposit'][0]['display_city'] 		= $return_val['customer'][0]['bill_city'];
				$return_val['deposit'][0]['display_postcode'] 	= $return_val['customer'][0]['bill_postcode'];
				$return_val['deposit'][0]['display_state'] 		= $return_val['customer'][0]['bill_state'];
			}
			
			$return_val['deposit'][0]['pic_name'] 		= $return_val['customer'][0]['pic_name'];
			$return_val['deposit'][0]['payment_term'] 	= $return_val['customer'][0]['payment_term'];
			$return_val['deposit'][0]['reg_no'] 		= $return_val['customer'][0]['reg_no'];
			
			
			$return_val['deposit'][0]['bill_detail'][0]['item_count'] 		= '1';
			$return_val['deposit'][0]['bill_detail'][0]['bill_type'] 		= '1';
			$return_val['deposit'][0]['bill_detail'][0]['bill_type_name'] 	= $return_val['deposit'][0]['remark'];
			$return_val['deposit'][0]['bill_detail'][0]['item_amount'] 		= $return_val['deposit'][0]['deposit'];			
			$return_val['deposit'][0]['bill_date'] 							= $return_val['deposit'][0]['deposit_date'];
			$return_val['deposit'][0]['bill_detail'][0]['remark'] 			= $return_val['customer'][0]['package_name'];
			
			$return_val['deposit'][0]['tel_num'] 							= $return_val['customer'][0]['tel_num'];
			$return_val['deposit'][0]['fax_num']							= $return_val['customer'][0]['fax_num'];
			$return_val['deposit'][0]['package_name']						= $return_val['customer'][0]['package_name'];

			$return_val['deposit'][0]['email_addr']						= $return_val['customer'][0]['acc_email'];
			$return_val['deposit'][0]['currency_code']						= $return_val['customer'][0]['currency_code'];
			
			
		}	
		
		return $return_val;
	}
	
	function get_deposit_receipt($idx){
		
		$select_c_field =" d.customer_no, ";
		$select_c_field .=" d.idx, ";
		$select_c_field .=" d.deposit_date, ";
		$select_c_field .=" d.deposit, ";
		$select_c_field .=" d.remark, ";
		$select_c_field .=" p.name AS payment_source_name ";
				
		$query_str_1= "	SELECT ".$select_c_field." 
						FROM customer_deposit d 
						LEFT JOIN sys_payment_source p ON d.payment_source = p.payment_source_id
						WHERE d.idx='".$this->db->escape_str($idx)."'; ";
	
		$query_1 = $this->db->query($query_str_1);
		$return_val = array();
		$return_val['deposit'] = array();
		if ($query_1->num_rows() > 0)	{
			$return_val['deposit'] = $query_1->result_array();
		}
		$customer_no = $return_val['deposit'][0]['customer_no'];		
		$query_str_2= "SELECT * FROM customer d WHERE d.customer_no='".$customer_no."' ";
		$query_2 = $this->db->query($query_str_2);
		
		if ($query_2->num_rows() > 0)	{
			$return_val['customer'] = $query_2->result_array();
		}
		
		return $return_val;
	}
	
	function get_deposit_history($customer_no)
	{
		
		$query_str= " 	SELECT 	d.customer_no, d.idx, d.deposit_date, d.deposit, d.remark, 
								sps.name AS payment_source , d.payment_source AS payment_source_id, d.payment_info,
								0 AS void, 'customer' AS origin, 0 AS is_lock   
						FROM customer_deposit d 
						LEFT JOIN sys_payment_source sps ON sps.payment_source_id = d.payment_source 
						WHERE d.customer_no = '".$this->db->escape_str($customer_no)."' 
						ORDER BY d.deposit_date ASC ";
		$query = $this->db->query($query_str);
		$a = $query->result_array();
		
		$query_str2 = "	(
							SELECT  ba.customer_no, ba.tranx_date as deposit_date, 
									CASE WHEN ba.adjust_type = 'dr' THEN ba.amount 
										 WHEN ba.adjust_type = 'cr' THEN -(ba.amount) END AS deposit, 
									sbt.name AS remark , '' AS payment_source , '' AS payment_source_id , 
									ba.remark AS payment_info , 
									CASE WHEN b.bill_no IS NOT NULL THEN is_void 
									ELSE 0 END AS void, 'adjustment' AS origin, ba.adj_no AS idx, ba.is_lock  
							FROM bill_adjustment ba 
							LEFT JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
							LEFT JOIN bill b ON ( ba.bill_no = b.bill_no )
							WHERE ba.customer_no = '".$this->db->escape_str($customer_no)."' 
							AND ba.bill_type IN ( '4' , '11' , '14' ) 
							HAVING void = 0
							ORDER BY ba.tranx_date ASC 
						)
						UNION
						(
							SELECT  p.customer_no, p.pay_date as deposit_date, 
									p.amount AS deposit, 
									sbt.name AS remark , sps.name AS payment_source , 
									p.payment_source AS payment_source_id , 
									p.remark AS payment_info , 
									CASE WHEN b.bill_no IS NOT NULL THEN is_void 
									ELSE 0 END AS void, 'payment' AS origin, p.idx, p.is_lock  
							FROM payment p
							INNER JOIN sys_bill_type sbt ON p.bill_type = sbt.bill_type_id 
							LEFT JOIN bill b ON ( p.bill_no = b.bill_no ) 
							LEFT JOIN sys_payment_source sps ON sps.payment_source_id = p.payment_source 
							WHERE p.customer_no = '".$this->db->escape_str($customer_no)."' 
							AND sbt.is_debit = 1 AND p.bill_type IN ( '4', '11', '14' ) 
							ORDER BY p.tranx_date ASC 
						) ";
		
		$query2 = $this->db->query($query_str2);
		$b = $query2->result_array();
		
		//add idx to arrays that came from bill_adj table
		//foreach($b as $b_key => $b_arr) $b[$b_key]['idx'] = ''; //if is adjustment, no print invoice receipt
		
		//merge arrays
		$result = array_merge($a,$b);
		
		//sort both merged array according to datetime ASC;	
		function cmp($a,$b)
		{
			return strtotime($a['deposit_date'])>strtotime($b['deposit_date'])?1:-1;
		}
		uasort($result,'cmp');
		
		$return_val = array();
		if(!empty($result))
		{
			foreach ($result as $r_key => $r_arr) $merge_result[] = $result[$r_key];
			$return_val = (!empty($merge_result) && count($merge_result) > 0)?$merge_result:'';
		}
		
		return $return_val;
	}

	public function get_deposit_history_on_id($idx){
		$query_str= "SELECT d.* , sps.name AS payment_source
						FROM customer_deposit d 
						LEFT JOIN sys_payment_source sps ON d.payment_source = sps.payment_source_id
						WHERE d.idx='".$this->db->escape_str($idx)."' ";
		$query = $this->db->query($query_str);
		$return_val = $query->row_array();
		return $return_val ; 
	}

	public function customer_update($post_back,$package,$username,$customer_no)
	{
		//fix variables
		$product_category     		= normalize_input($post_back['product_category'] ?? null, 'int', 0);
		$bill_by_post         		= normalize_input($post_back['bill_by_post'] ?? null, 'int', 0);
		$bill_by_email        		= normalize_input($post_back['bill_by_email'] ?? null, 'int', 0);
		$yearly_charge        		= normalize_input($post_back['yearly_charge'] ?? null, 'int', 0);
		$stop_service_after   		= normalize_input($post_back['stop_service_after'] ?? null, 'int', 0);
		$delay_trial_start    		= normalize_input($post_back['delay_trial_start'] ?? null, 'int', 0);
		$dealer               		= normalize_input($post_back['dealer'] ?? null, 'int', 0);
		$package_month        		= normalize_input($post_back['package_month'] ?? null, 'int', 0);
		$payment_term         		= normalize_input($post_back['payment_term'] ?? null, 'int', 0);
		$monthly_charge       		= normalize_input($post_back['monthly_charge'] ?? null, 'float', 0.00);
		$bill_waive_period    		= normalize_input($post_back['bill_waive_period'] ?? null, 'int', 0);
		$contract_month       		= normalize_input($post_back['contract_month'] ?? null, 'int', 0);
		$free_package_upgrade 		= normalize_input($post_back['free_package_upgrade'] ?? null, 'int', 0);

		$signup_date     			= normalize_input($post_back['signup_date'] ?? null, 'date', null);
		$activated_date  			= normalize_input($post_back['activated_date'] ?? null, 'date', null);
		$terminated_date 			= normalize_input($post_back['terminated_date'] ?? null, 'date', null);
		$suspended_date  			= normalize_input($post_back['suspended_date'] ?? null, 'date', null);
		$bill_cycle_start_date      = normalize_input($bill_cycle_start_date ?? null, 'date', null);
		$preferred_install_datetime = normalize_input($post_back['preferred_install_datetime'] ?? null, 'date', null);
		
		$post_back['dia_vars'] 		= json_decode( $post_back['dia_vars'] ?? '[]', true );

		$custInfo = $this->get_customer($customer_no);

		//log text
		$change_text = '';
		$bill_cycle_start_date = null;
		if (!empty($custInfo)) {
			$this->load->helper('change_log');

			$this->load->model('common_model');
			$this->load->model('package_model');
			$this->load->model('router_model');

			$status_arr   = array_column($this->common_model->get_acc_status_list() ?? [], 'name', 'status_code');
			$category_arr = array_column($this->common_model->get_category_list() ?? [], 'name', 'category_code');
			$building_arr = array_column($this->common_model->get_building_list() ?? [], 'name', 'building_no');
			$dealer_arr   = array_column($this->common_model->get_dealer_list() ?? [], 'name', 'dealer_no');
			$package_arr  = array_column($this->package_model->get_package_listing_all() ?? [], 'name', 'package_no');
			$router_arr = array_column($this->router_model->get_routers() ?? [], 'name', 'id');
			$profile_arr = [
				$custInfo['profile_id'] => $custInfo['acc_name'] ?? '',
				$post_back['profile_id'] => $post_back['acc_name'] ?? ''
			];
			
			$change_text .= compare_field_change('Status', $custInfo['status'], $post_back['status'], $status_arr, true, $this->db);
			$change_text .= compare_field_change('Category', $custInfo['category'], $post_back['category'], $category_arr, true, $this->db);
			$change_text .= compare_field_change('Currency Code', $custInfo['currency_code'], $post_back['currency_code'], [], true, $this->db);
			$change_text .= compare_field_change('Package', $custInfo['package_name'], $post_back['package_name'], [], true, $this->db);
			$change_text .= compare_field_change('New Package Effective Date', $custInfo['new_package_effective_date'], $post_back['new_package_effective_date'], [], true, $this->db);
			$change_text .= compare_field_change('Framed IP Address', $custInfo['framed_ip_address'], $post_back['framed_ip_address'], [], true, $this->db);
			$change_text .= compare_field_change('Framed Route', $custInfo['framed_route'], $post_back['framed_route'], [], true, $this->db);
			$change_text .= compare_field_change('Monthly Charges', $custInfo['monthly_charge'], $monthly_charge, [], true, $this->db);
			$change_text .= compare_field_change('Contract Month', $custInfo['contract_month'], $contract_month, [], true, $this->db);
			$change_text .= compare_field_change('Login Username', $custInfo['login_username'], $post_back['login_username'], [], true, $this->db);
			$change_text .= compare_field_change('Login Password', $custInfo['login_password'], $post_back['login_password'], [], true, $this->db);
			$change_text .= compare_field_change('Project Name', $custInfo['project_name'], $post_back['project_name'], [], true, $this->db);
			$change_text .= compare_field_change('Unit Name', $custInfo['unit_name'], $post_back['unit_name'], [], true, $this->db);
			$change_text .= compare_field_change('Name', $custInfo['name'], $post_back['name'], [], true, $this->db);

			if (($post_back['category'] ?? '') === 'b') {
				$change_text .= compare_field_change('Reg No.', $custInfo['reg_no'], $post_back['reg_no'], [], true, $this->db);
				$change_text .= compare_field_change('Tax No.', $custInfo['gst_no'], $post_back['gst_no'], [], true, $this->db);
			}

			$change_text .= compare_field_change('Addr1', $custInfo['inst_addr1'], $post_back['inst_addr1'], [], true, $this->db);
			$change_text .= compare_field_change('Addr2', $custInfo['inst_addr2'], $post_back['inst_addr2'], [], true, $this->db);
			$change_text .= compare_field_change('Addr City', $custInfo['inst_city'], $post_back['inst_city'], [], true, $this->db);
			$change_text .= compare_field_change('Addr Postcode', $custInfo['inst_postcode'], $post_back['inst_postcode'], [], true, $this->db);
			$change_text .= compare_field_change('Addr State', $custInfo['inst_state'], $post_back['inst_state'], [], true, $this->db);
			$change_text .= compare_field_change('Building', $custInfo['building'], $post_back['building'], $building_arr, true, $this->db);
			$change_text .= compare_field_change('Agent', $custInfo['dealer'], $dealer ?? '', $dealer_arr, true, $this->db);
			$change_text .= compare_field_change('Installer Name', $custInfo['installer_name'], $post_back['installer_name'], [], true, $this->db);
			$change_text .= compare_field_change('Remark', $custInfo['remark'], $post_back['remark'], [], true, $this->db);
			$change_text .= compare_field_change('Caller ID', $custInfo['caller_id'], $post_back['caller_id'], [], true, $this->db);
			$change_text .= compare_field_change('Bill Waive Period', $custInfo['bill_waive_period'], $bill_waive_period, [], true, $this->db);
			$change_text .= compare_field_change('Delay Trial Start', $custInfo['delay_trial_start'], $delay_trial_start, ['0' => 'Off', '1' => 'On'], true, $this->db);
			$change_text .= compare_field_change('Preferred Install Datetime', $custInfo['preferred_install_datetime'], $post_back['preferred_install_datetime'], [], true, $this->db);
			$change_text .= compare_field_change('Free Package Upgrade', $custInfo['free_package_upgrade'], $free_package_upgrade, [], true, $this->db);
			$change_text .= compare_field_change('Upgrade Package', $custInfo['upgrade_package_id'] ?? '', $post_back['upgrade_package_id'] ?? '', $package_arr, true, $this->db);
			$change_text .= compare_field_change('Profile', $custInfo['profile_id'] ?? '', $post_back['profile_id'] ?? '', $profile_arr, true, $this->db);

			// HANDLE TECHNICAL CONFIG FIELD CHANGE
			$technical_config_change_text = '';
			$technical_config_change_field = [];

			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Framed IP Address', $custInfo['framed_ip_address'] ?? '', $post_back['framed_ip_address'] ?? '', [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Framed IPv6 Address', $custInfo['framed_ipv6_address'] ?? '', $post_back['framed_ipv6_address'] ?? '', [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Framed Route', $custInfo['framed_route'] ?? '', $post_back['framed_route'] ?? '', [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Framed IPv6 Route', $custInfo['framed_ipv6_route'] ?? '', $post_back['framed_ipv6_route'] ?? '', [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Framed Netmask', $custInfo['framed_netmask'] ?? '', $post_back['framed_netmask'] ?? '', [], true);

			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Router', $custInfo['router_id'] ?? '', $post_back['router_id'] ?? '', $router_arr, true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Core Router ID', $custInfo['core_router_id'], $post_back['core_router_id'], [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Interface ID', $custInfo['interface_id'], $post_back['interface_id'], [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Core Router Interface ID', $custInfo['core_router_interface_id'], $post_back['core_router_interface_id'], [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Edge Router ID', $custInfo['edge_router_id'], $post_back['edge_router_id'], [], true);
			$this->append_field_change($technical_config_change_text, $technical_config_change_field, 'Edge Router Interface ID', $custInfo['edge_router_interface_id'], $post_back['edge_router_interface_id'], [], true);

			$change_text .= $technical_config_change_text;

			$technical_config_change_field = implode(', ', $technical_config_change_field);

			$config_record = $this->common_model->get_table('sys_config', '*', "`category` = 'bills' AND `key` = 'bill_generate_day'");
			$bill_cycle_day = $config_record[0]['val'] ?? '';
			$bill_cycle_month = $post_back['bill_cycle_month'];
			if (($bill_cycle_month > 1) && ($custInfo['bill_cycle_month'] != $bill_cycle_month)) {
				$bill_cycle_start_date = $custInfo['next_bill_date'];
			}
		}

		if (($post_back['relocation_action'] ?? 0) == 1) {
			$post_back['building']      = $custInfo['building'];
			$post_back['inst_unit_no']  = $custInfo['inst_unit_no'];
			$post_back['inst_addr1']    = $custInfo['inst_addr1'];
			$post_back['inst_addr2']    = $custInfo['inst_addr2'];
			$post_back['inst_addr3']    = $custInfo['inst_addr3'];
			$post_back['inst_city']     = $custInfo['inst_city'];
			$post_back['inst_postcode'] = $custInfo['inst_postcode'];
			$post_back['inst_state']    = $custInfo['inst_state'];
		}
		
		if((($post_back['relocation_action'] ?? 0) == 1 || $custInfo['package'] != $package) && $custInfo['current_status'] == 'A') {
			$new_package_effective_date = normalize_input($post_back['new_package_effective_date'] ?? null, 'date', null);
			$new_package_id             = $package;
			$old_package                = $this->package_model->get_package($custInfo['package']);
			$package_name               = $old_package['name'];
			$monthly_charge             = $old_package['monthly_charge'];
			$new_contract_month         = $contract_month;
			$old_bill_waive_period      = $custInfo['new_package_effective_date'] != NULL ? $custInfo['old_bill_waive_period'] : $custInfo['bill_waive_period'];
		} else {
			$new_package_effective_date = null;
			$new_package_id             = null;
			$package_name               = normalize_input($post_back['package_name'] ?? null, 'string', '');
			$monthly_charge             = $monthly_charge;
			$new_contract_month         = null;
			$old_bill_waive_period      = 0;

			if($custInfo['current_status'] != 'A') {
				$contract_month = $contract_month;
			}
		}
	
		$pic_name = $this->getProfileNameById($post_back['profile_id'] ?? null);

		$data = [
			'status'                     => $post_back['status'],
			'category'                   => $post_back['category'],
			'currency_code'              => $post_back['currency_code'],
			'product_category'           => $product_category, 
			'package'                    => $post_back['package'],
			'new_package_effective_date' => $new_package_effective_date,
			'new_package_id'             => $new_package_id,
			'package_name'               => $package_name,
			'framed_ip_address'          => $post_back['framed_ip_address'],
			'framed_route'               => $post_back['framed_route'],
			'package_month'              => $package_month,
			'monthly_charge'             => $monthly_charge,
			'bill_cycle_month'           => $post_back['bill_cycle_month'],
			'contract_month'             => $contract_month,
			'new_contract_month'         => $new_contract_month,
			'old_bill_waive_period'      => $old_bill_waive_period,
			'login_username'             => $post_back['login_username'],
			'login_password'             => $post_back['login_password'],
			'core_router_id'             => $post_back['core_router_id'],
			'core_router_interface_id'   => $post_back['core_router_interface_id'],
			'edge_router_id'             => $post_back['edge_router_id'],
			'edge_router_interface_id'   => $post_back['edge_router_interface_id'],
			'project_name'               => $post_back['project_name'],
			'unit_name'                  => $post_back['unit_name'],
			'signup_date'                => $signup_date,
			'activated_date'             => $activated_date,
			'terminated_date'            => $terminated_date,
			'suspended_date'             => $suspended_date,
			'name'                       => $post_back['name'],
			'reg_no'                     => $post_back['reg_no'],
			'gst_no'                     => $post_back['gst_no'],
			'pic_name'                   => $pic_name,
			'inst_unit_no'               => $post_back['inst_unit_no'],
			'inst_addr1'                 => $post_back['inst_addr1'],
			'inst_addr2'                 => $post_back['inst_addr2'],
			'inst_addr3'                 => $post_back['inst_addr3'],
			'inst_city'                  => $post_back['inst_city'],
			'inst_postcode'              => $post_back['inst_postcode'],
			'inst_state'                 => $post_back['inst_state'],
			'inst_phone'                 => $post_back['inst_phone'],
			'inst_email'                 => $post_back['inst_email'],
			'building'                   => $post_back['building'],
			'bill_name'                  => $post_back['bill_name'] ?? '',
			'bill_addr1'                 => $post_back['bill_addr1'] ?? '',
			'bill_addr2'                 => $post_back['bill_addr2'] ?? '',
			'bill_city'                  => $post_back['bill_city'] ?? '',
			'bill_postcode'              => $post_back['bill_postcode'] ?? '',
			'bill_state'                 => $post_back['bill_state'] ?? '',
			'bill_by_post'               => $bill_by_post,
			'bill_by_email'              => $bill_by_email,
			'marital_status'             => $post_back['marital_status'],
			'household'                  => $post_back['household'],
			'no_of_employee'             => $post_back['no_of_employee'],
			'no_of_branches'             => $post_back['no_of_branches'],
			'dealer'                     => $dealer,
			'payment_term'               => $payment_term,
			'serial_num'                 => $post_back['serial_num'],
			'installer_name'             => $post_back['installer_name'],
			'caller_id'                  => $post_back['caller_id'],
			'dia_vars'                   => json_encode($post_back['dia_vars']),
			'nationality'                => $post_back['nationality'],
			'remark'                     => $post_back['remark'],
			'yearly_charge'              => $yearly_charge,
			'stop_service_after'         => $stop_service_after,
			'ownership_type'             => $post_back['ownership_type'] ?? '',
			'bill_waive_period'          => $bill_waive_period,
			'delay_trial_start'          => $delay_trial_start,
			'preferred_install_datetime' => $preferred_install_datetime,
			'free_package_upgrade'       => $free_package_upgrade,
			'upgrade_package_id'         => $post_back['upgrade_package_id'],
			'router_id'                  => $post_back['router_id'],
			'interface_id'               => $post_back['interface_id'],
			'framed_netmask'             => $post_back['framed_netmask'],
			'framed_ipv6_address'        => $post_back['framed_ipv6_address'],
			'framed_ipv6_route'          => $post_back['framed_ipv6_route'],
			'bill_cycle_start_date'      => $bill_cycle_start_date,
			'modified_by'                => $username,
		];

		$this->db->set('modified_date', 'NOW()', FALSE)
				->where('customer_no', $customer_no)
				->update('customer', $data);

		$query_str = $this->db->last_query();

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'customer no('.$this->db->escape_str($this->input->post('customer_no')).') has been updated.'.$change_text;	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->input->post('customer_no'));

		//customer action log
		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($this->input->post('customer_no')));

		// send notification to technical users when customer technical config settings changed
		if (!empty($technical_config_change_text)) {
			$this->notify_technical_config_changed(
				$technical_config_change_text,
				$technical_config_change_field
			);
		}
		
		//update status
		$status_chk = $this->get_latest_status_by_customer_no($customer_no);
		
		//empty search
		$insert_latest_status = 0;
		if (!isset($status_chk['status'])) {
			//empty record so insert
			$insert_latest_status = 1;

		} else {
			if ($status_chk['status'] != $post_back['current_status'])  {
				$insert_latest_status = 1;
			}
		}

		if ($insert_latest_status == 1) {
			$this->create_status_record($customer_no, $post_back['current_status'], (isset($post_back['transact_date'])?$post_back['transact_date']:''), (isset($post_back['user_idx'])?$post_back['user_idx']:0));
		} else if (isset($status_chk['status_id']) && isset($post_back['transact_date'])) {
			//just updating time
			$this->update_status_record_time($status_chk['status_id'], $post_back['transact_date'], $customer_no, $status_chk['status']);
		}

		//do nothing if same package , 
		//do update if not same package
		//if ( $custInfo['package'] !== $this->input->post('package') ){
		
		//Check whether package got change or not.get last package
		//where last package start date != today , as assume history for start_date = today will be deleted later 
		/*$query_str = "SELECT cph.idx, cph.package_name, cph.monthly_charge, cph.start_date " .
					"FROM customer_package_history cph " .
					"WHERE cph.customer_no = '$customer_no' " .
					"AND start_date != '".date('Y-m-d')."' " .
					"ORDER BY cph.idx DESC LIMIT 1";*/

		//package check logic is buggy, should check based on last record compare to current setting
		// when save, check if status is activated and later
		// if yes , then see if record is inserted

		$query_str = "SELECT cph.idx, cph.package_name, cph.monthly_charge, cph.start_date " .
					"FROM customer_package_history cph " .
					"WHERE cph.customer_no = '$customer_no' " . 
					"ORDER BY cph.idx DESC LIMIT 1";

		// then check if package has changed based on last record

		$query = $this->db->query($query_str);
		
		if ( $query->num_rows() > 0 ) 
		{
			$row = $query->row_array();

			if ( $custInfo['package'] != $this->input->post('package') ) 
			{
				if ($status_chk['status'] == 'P' && $post_back['current_status'] == 'P') {
					$delete_str = "DELETE FROM customer_package_history cph WHERE cph.idx = ?";
					$delete = $this->db->query($delete_str, [$row['idx']]);

					if($this->db->affected_rows() > 0) {
						$update_str = "UPDATE customer c SET c.package = ?, c.package_name = ?, c.monthly_charge = ? WHERE c.customer_no = ?";
						$update = $this->db->query($update_str, [$package, $post_back['package_name'], $post_back['monthly_charge'], $customer_no]);

						$insert_str = "INSERT INTO customer_package_history (customer_no, package_name, monthly_charge, start_date, package_no, building, inst_unit_no, inst_addr1, inst_addr2, inst_addr3, inst_city, inst_postcode, inst_state) VALUES ( " .
									"'" . $customer_no . "', " .
									"'" . $post_back['package_name'] . "', " .
									"'" . $post_back['monthly_charge'] . "', " .
									"NOW(), " .
									"'" . $this->db->escape_str($package) . "', " .
									"'" . $this->db->escape_str($post_back['building']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_unit_no']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_addr1']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_addr2']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_addr3']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_city']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_postcode']) . "', " .
									"'" . $this->db->escape_str($post_back['inst_state']) . "' " .
									")";
						$insert = $this->db->query($insert_str);

						$package_info = $this->package_model->get_package($package);

						$control_by = 'cisco';
						if($control_by == 'radius') {						
							$this->radius_usergroup_upsert($post_back['login_username'], $package_info['bandwidth']);
						} else if ($control_by == 'cisco') {
							$this->cisco_usergroup_upsert($post_back['login_username'], $package_info['bandwidth'], $custInfo['login_username']);
						}
					}
				}

				//check if package change on same day, if same day, then just remove the current one and replace with the new one... somehow this is placed in the logic here maybe got use

				// $query_str_x = "SELECT cph.idx, cph.package_name, cph.monthly_charge, cph.start_date " .
				// 	"FROM customer_package_history cph " .
				// 	"WHERE cph.customer_no = '$customer_no' " .
				// 	"AND start_date = '".date('Y-m-d')."' " .
				// 	"ORDER BY cph.idx DESC LIMIT 1";
				// $query_x = $this->db->query($query_str_x);
				
				// if ( $query_x->num_rows() > 0 ) {
				// 	//clear history with same start date b4 insert a new history
				// 	$history_str = "DELETE FROM customer_package_history 
				// 					WHERE customer_no = '".$this->db->escape_str($this->input->post('customer_no'))."'
				// 					AND start_date = '".date('Y-m-d')."' ";
				// 	$this->db->query($history_str);
				// 	//do some checking here , is_autogen == 1 AND created_date == today 
				// 	//AND bill_type  == 1 or 10 
				// 	//THEN delete those adjustment
				// 	$reset_str = "	DELETE 
				// 					FROM bill_adjustment  
				// 					WHERE customer_no = '".$customer_no."' 
				// 					AND ( bill_type = 1 OR bill_type = 10 )
				// 					AND is_autogen = 1
				// 					AND DATE_FORMAT( created_date , '%Y-%m-%d' ) = '".date('Y-m-d')."' ";	
				// 	$reset = $this->db->query($reset_str);
				// }

				// $latest_activated_date = $this->get_status_transact_date($customer_no, 'A');

				/**
				 * Rules for handling user status and package changes:
				 *
				 * 1. When status is SIGNUP:
				 *    - Creating package history: create a new entry.
				 *    - Changing to ACTIVATED: insert contract date.
				 *    - Changing to SUSPENDED / TERMINATED / CANCELLED: do nothing.
				 *    - Changing package on the same date: replace the package history.
				 *    - Changing package on a different date: insert a new package history.
				 *
				 * 2. When status is ACTIVATED:
				 *    - Changing to SIGNUP: invalid action.
				 *    - Changing to SUSPENDED / TERMINATED / CANCELLED: do nothing.
				 *    - Changing package on the same date: replace the package history.
				 *    - Changing package on a different date: insert a new package history.
				 *
				 * 3. When status is SUSPENDED / TERMINATED / CANCELLED:
				 *    - Changing to SIGNUP: invalid action.
				 *    - SUSPENDED → ACTIVATED: do nothing.
				 *    - TERMINATED → ACTIVATED: insert contract date.
				 *    - Switching between SUSPENDED / TERMINATED / CANCELLED: do nothing.
				 *    - Changing package on the same date:
				 *        - replace the package history
				 *        - remove contract date
				 *    - Changing package on a different date: insert a new package history.
				 * 
				 * Note:
				 * → (alt + numpad 26)
				 */

				/** 
				 * no need insert to customer_package_change history first need wait for itelco user to perform action
				 * whether to continue with existing contract or start a new contract
				*/

				// if($post_back['current_status'] == 'A' && !empty($latest_activated_date)) {
				// 	//must have activated date
				// 	$used_until_date = date("Y-m-d", strtotime("-1 day"));
				// 	$used_until_date = strtotime($used_until_date) < strtotime($row['start_date']) ? $row['start_date'] : $used_until_date ; 
				// 	$query_str1 = "UPDATE customer_package_history SET " .
				// 				"start_date = ". (!empty($latest_activated_date) ? "'".$this->db->escape_str( $latest_activated_date )."'" : "NULL") .", " .
				// 				"end_date = '" . $used_until_date . "' " .
				// 				"WHERE idx = '" . $row['idx'] . "'"; 
					
				// 	$this->db->query($query_str1);
					
				// 	$query_str2 = "INSERT INTO customer_package_history (customer_no, package_name, monthly_charge, start_date, contract_start_date, contract_end_date, package_no) VALUES ( " .
				// 				"'" . $customer_no . "', " .
				// 				"'" . $this->db->escape_str($this->input->post('package_name')) . "', " .
				// 				"'" . $this->db->escape_str($this->input->post('monthly_charge')) . "', " .
				// 				"NOW(), " .
				// 				"NOW(), '" . date('Y-m-d', strtotime("+" . $post_back['contract_month'] . " months")) . 
				// 				"', " . $this->db->escape_str($this->input->post('package')) . 
				// 				")";
			
					
				// 	$this->db->query($query_str2);
				// } else {
				// 	$sql1 = "UPDATE customer_package_history SET end_date = ? WHERE idx = ?";
				// 	$this->db->query($sql1, [date('Y-m-d'), $row['idx']]);

				// 	$query_str2 = "INSERT INTO customer_package_history (customer_no, package_name, monthly_charge, start_date, package_no) VALUES ( " .
				// 				"'" . $customer_no . "', " .
				// 				"'" . $this->db->escape_str($this->input->post('package_name')) . "', " .
				// 				"'" . $this->db->escape_str($this->input->post('monthly_charge')) . "', " .
				// 				"NOW()," . $this->db->escape_str($this->input->post('package')) . ")";
					
				// 	$this->db->query($query_str2);
				// }
				
				//**************
				//* Not auto generate the auto adjustment for wholesale
				//**************
				if ($status_chk['status'] == 'A' && $post_back['current_status'] == 'A') {
					// change package when new effective date is not reach

					if ((!empty($custInfo['new_package_id']) && !empty($custInfo['new_package_effective_date'])) && ($custInfo['new_package_id'] != $post_back['package'])) {
						$select_idx_qry = $this->db->query("SELECT idx FROM customer_package_history WHERE customer_no = ? AND package_no = ? ORDER BY idx DESC LIMIT 1", [$customer_no, $custInfo['new_package_id']]);
						$idx = $select_idx_qry->row_array()['idx'] ?? 0;

						if(!empty($idx)) {
							$delete_cph_str = "DELETE FROM customer_package_history cph WHERE cph.idx = ?";
							$delete_cph = $this->db->query($delete_cph_str, [$idx]);

							if($this->db->affected_rows() > 0) {
								$select_adj_no_qry = $this->db->query("SELECT adj_no FROM bill_adjustment WHERE customer_no = ? AND remark LIKE 'Package changed on%' ORDER BY adj_no DESC LIMIT 1", [$customer_no]);
								$adj_no = $select_adj_no_qry->row_array()['adj_no'] ?? 0;

								if(!empty($adj_no)) {
									$delete_bill_adj_str = "DELETE FROM bill_adjustment WHERE adj_no = ?";
									$delete_bill_adj = $this->db->query($delete_bill_adj_str, [$adj_no]);
								}								
							}
						}						
					}

					if ($this->input->post('category') != 'w') {
						$query = $this->db->query(
							"SELECT cph.idx, cph.package_name, cph.monthly_charge, cph.start_date
							FROM customer_package_history cph
							WHERE cph.customer_no = ?
							ORDER BY cph.idx DESC
							LIMIT 2",
							[$customer_no]
						);
						$result = $query->result_array();

						if (count($result) < 2) return;

						$last_package = $result[1];

						$previous_package_waive_period = empty($custInfo['old_bill_waive_period']) ? $custInfo['bill_waive_period'] : $custInfo['old_bill_waive_period'];

						$chk_result = $this->chk_customer_valid_waiver_period(
							$customer_no,
							$previous_package_waive_period
						);

						$new_effective = new DateTime($post_back['new_package_effective_date']);

						$old_charge = (float)$last_package['monthly_charge'];
						$new_charge = (float)$this->input->post('monthly_charge');

						$waiver_end = new DateTime($chk_result['waiver_end_date']);
						$waiver_end_month_end = new DateTime($waiver_end->format('Y-m-t'));

						//if package effective date still within waiver , then the charges will be 0
						$amount = 0;

						// Enable/disable legacy method
						$use_old_method = true;

						if ($use_old_method && $new_effective > $waiver_end && $new_effective <= $waiver_end_month_end) {
							$start_date = $this->get_status_transact_date($customer_no, 'A');
							$start_old = new DateTime($start_date);
							$month_end = new DateTime(date('Y-m-t', strtotime($start_date)));
							$total_days   = (int)$month_end->format('t');
							$balance_days = (int)$start_old->diff($month_end)->format('%a') + 1;

							$amount = ($new_charge - $old_charge) / $total_days * $balance_days;

						}
						else if ($chk_result['in_waiver_period']) {

							if ($waiver_end < $new_effective) { 
								$last_day_of_waiver_month = (clone $waiver_end)->modify('last day of this month');
								$total_days   = (int)$last_day_of_waiver_month->format('t');
								$balance_days = (int)$waiver_end->diff($last_day_of_waiver_month)->format('%a');
								$amount = ($new_charge - $old_charge) / $total_days * $balance_days;
							} else {
								$amount = 0;
							}

						}
						else {
							$month_end = new DateTime("last day of this month");
							$total_days = (int)$month_end->format('t');

							$start_old = new DateTime($last_package['start_date']);
							$month_start = new DateTime($month_end->format('Y-m-01'));

							if ($start_old < $month_start) {
								$balance_days = (int)$new_effective->diff($month_end)->format('%a') + 1;
							} else {
								$balance_days = (int)$start_old->diff($new_effective)->format('%a');
							}
							$amount = ($new_charge - $old_charge) / $total_days * $balance_days;
						}

						if ($amount != 0) {

							$this->load->model('adjustment_model');
							$adj_remark = "Package changed on " . date('d M Y');
							if ($amount > 0) {
								$this->adjustment_model->insert_adj(
									'i', $customer_no, '', '', date('Y-m-d'),
									'41', 'dr', $amount, $adj_remark, 1
								);
							} else {
								$this->adjustment_model->insert_adj(
									'i', $customer_no, '', '', date('Y-m-d'),
									'10', 'cr', abs($amount), $adj_remark, 1
								);
							}

						}

						//valerie - for pacakge change, insert a record into customer_action_log
						$customer_action_model			= 'customer_action_log_model';
						$this->load->model($customer_action_model);			
						$ctrl			= $this->router->fetch_class();
						$esc_query_str	= '';	
						$method 		= $this->router->method; 		
						$temp_package_info = $this->package_model->get_package($package);		
						$action_desc	= 'customer_package_history updated (Changed to Package '.$temp_package_info['name'].')';	
						$action_category = 'update';
						$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$customer_no);

					}
				}

				/**
				 * CHANGE PACKAGE NEED INSERT CONTRACT DATE WHEN
				 * Suspend to Activate
				 * Terminate to Activate
				 * Cancelled to Activate
				 */
				// if (($status_chk['status'] == 'T' || $status_chk['status'] == 'C' || $status_chk['status'] == 'S') && $post_back['current_status'] == 'A') {
				// 	$activated_date = $this->get_status_transact_date($customer_no, 'A');
				// 	$contract_start_date = $activated_date;
				// 	$contract_end_date = date('Y-m-d', strtotime("+" . $post_back['contract_month'] . " months", strtotime($activated_date)));

				// 	$restore_str = "UPDATE customer_package_history 
				// 					SET monthly_charge = '". $this->input->post('monthly_charge') ."' , 
				// 						end_date = NULL, contract_start_date = '". $this->db->escape_str($contract_start_date) . "', contract_end_date = '" .$this->db->escape_str($contract_end_date). "'
				// 					WHERE idx = '" . $row['idx'] . "'"; 
				// 	$this->db->query($restore_str);
				// }
			}
			else{
				/**
				 * NO CHANGE PACKAGE BUT INSERT CONTRACT DATE WHEN
				 * Signup to Activate
				 * Terminate to Activate
				 */
				if (($status_chk['status'] == 'P' || $status_chk['status'] == 'T') && $post_back['current_status'] == 'A') {
					$activated_date = $this->get_status_transact_date($customer_no, 'A');

					if (!empty($post_back['contract_month'])) {
						$contract_start_date = "'" . $this->db->escape_str($activated_date) . "'";
						$contract_end_date = "'" . $this->db->escape_str(date('Y-m-d', strtotime("+" . $post_back['contract_month'] . " months", strtotime($activated_date)))) . "'";
					} else {
						$contract_start_date = "NULL";
						$contract_end_date = "NULL";
					}

					$restore_str = "UPDATE customer_package_history 
									SET monthly_charge = '". $post_back['monthly_charge'] ."' , 
										end_date = NULL, contract_start_date = ". $contract_start_date . ", contract_end_date = " . $contract_end_date. "
									WHERE idx = '" . $row['idx'] . "'"; 
					$this->db->query($restore_str);
				}
			}
		}else{//is empty history, insert new 

			//empty history only insert if activated date has been decided, otherwise start date would be null
			$activated_date = $this->get_status_transact_date($customer_no, 'A');
			
			if (!empty($activated_date)) {

				if (!empty($post_back['contract_month'])) {
					$contract_start_date = "'" . $this->db->escape_str($activated_date) . "'";
					$contract_end_date = "'" . $this->db->escape_str(date('Y-m-d', strtotime("+" . $post_back['contract_month'] . " months", strtotime($activated_date)))) . "'";
				} else {
					$contract_start_date = "NULL";
					$contract_end_date = "NULL";
				}

				$str = "INSERT INTO customer_package_history (customer_no, package_name, monthly_charge, start_date, contract_start_date, contract_end_date, package_no, building, inst_unit_no, inst_addr1, inst_addr2, inst_addr3, inst_city, inst_postcode, inst_state) 
							VALUES ( '" . $customer_no . "', 
									 '" . $this->db->escape_str($this->input->post('package_name')) . "', 
									 '" . $this->db->escape_str($this->input->post('monthly_charge')) . "', 
									 '" . $this->db->escape_str($activated_date) . "', 
									 " . $contract_start_date . ", 
									 " . $contract_end_date . ", " . 
									 $this->db->escape_str($this->input->post('package')) . ",  
									 '".$this->db->escape_str($this->input->post('building'))."',
									 '".$this->db->escape_str($this->input->post('inst_unit_no'))."',
									 '".$this->db->escape_str($this->input->post('inst_addr1'))."',
									 '".$this->db->escape_str($this->input->post('inst_addr2'))."',
									 '".$this->db->escape_str($this->input->post('inst_addr3'))."',
									 '".$this->db->escape_str($this->input->post('inst_city'))."',
									 '".$this->db->escape_str($this->input->post('inst_postcode'))."',
									 '".$this->db->escape_str($this->input->post('inst_state'))."' )";
				
				$this->db->query($str);
				
				$model			= 'action_log_model';
				$this->load->model($model);			
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str($str);	
				$method 		= $this->router->method; 				
				$action_desc	= 'new customer_package_history ('.$this->db->escape_str($this->input->post('package_name')).') of customer has been added';	
				$action_category = 'insert';
				$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$customer_no);

				$customer_action_model			= 'customer_action_log_model';
				$this->load->model($customer_action_model);
				$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));

			}
			
		}
	}

	public function customer_update_next_bill_date($customer_no,$next_bill_date)
	{
		if( $next_bill_date == '' )
			$update = " next_bill_date = NULL ";
		else
			$update = " next_bill_date = '".$next_bill_date."' ";

		$query_str = " UPDATE customer SET $update
						WHERE customer_no = '".$customer_no."' ";
		$query = $this->db->query($query_str);
		
		$aff_rows = $this->db->affected_rows() ;
		
		if( $aff_rows > 0 )
		{
			$model			= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 		
			$action_desc	= 'Customer Next Bill Date has been updated.';
			$action_category =  'update';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$customer_no);
			$this->db->query($query_str);	
		}
		
		return $aff_rows ;
	}

	public function customer_insert($post_back,$package,$username)
	{
		$return_val = array();
		$query = $this->db->query("SELECT MAX(c.customer_no) as customer_no FROM customer c");
		$customer_no = $return_val['customer_no'] = ($query->row()->customer_no ?? 0) + 1;

		$signup_date = empty($post_back['signup_date']) ? "NULL" : "'".$this->db->escape_str($post_back['signup_date'])."'";
		$activated_date = empty($post_back['activated_date']) ? "NULL" : "'".$this->db->escape_str($post_back['activated_date'])."'";
		$terminated_date = empty($post_back['terminated_date']) ? "NULL" : "'".$this->db->escape_str($post_back['terminated_date'])."'";
		$suspended_date = empty($post_back['suspended_date']) ? "NULL" : "'".$this->db->escape_str($post_back['suspended_date'])."'";
		
		$post_back['product_category']    			= normalize_input($post_back['product_category'] ?? 0, 'int', 0);
		$post_back['bill_by_post']        			= normalize_input($post_back['bill_by_post'] ?? 0, 'int', 0);
		$post_back['bill_by_email']        			= normalize_input($post_back['bill_by_email'] ?? 0, 'int', 0);
		$post_back['stop_service_after']  			= normalize_input($post_back['stop_service_after'] ?? 0, 'int', 0);
		$post_back['delay_trial_start']   			= normalize_input($post_back['delay_trial_start'] ?? 0, 'int', 0);
		$post_back['no_of_employee']      			= normalize_input($post_back['no_of_employee'] ?? 0, 'int', 0);
		$post_back['no_of_branches']      			= normalize_input($post_back['no_of_branches'] ?? 0, 'int', 0);
		$post_back['dealer']              			= normalize_input($post_back['dealer'] ?? 0, 'int', 0);
		$post_back['package_month']       			= normalize_input($post_back['package_month'] ?? 0, 'int', 0);
		$post_back['monthly_charge']      			= normalize_input($post_back['monthly_charge'] ?? 0.00, 'float', 0.00);
		$post_back['yearly_charge']       			= normalize_input($post_back['yearly_charge'] ?? 0.00, 'float', 0.00);
		$post_back['contract_month']        		= normalize_input($post_back['contract_month'] ?? 0, 'int', 0);
		$post_back['bill_waive_period']     		= normalize_input($post_back['bill_waive_period'] ?? 0, 'int', 0);
		$post_back['free_package_upgrade']  		= normalize_input($post_back['free_package_upgrade'] ?? 0, 'int', 0);
		$post_back['payment_term']  				= normalize_input($post_back['payment_term'] ?? 0, 'int', 0);

		$this->load->model('common_model');
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'] ?? '3';

		$bill_cycle_start_date = null;
		if (($post_back['bill_cycle_month'] ?? 0) > 1) {
			$bill_cycle_start_date = date("Y-m-1", strtotime("+32 day", strtotime(date('Y-m-1'))));
		}

		$preferred_install_datetime = !empty($post_back['preferred_install_datetime'])
									? "'" . $this->db->escape_str($post_back['preferred_install_datetime']) . "'"
									: "NULL";

		$post_back['pic_name'] 	= $this->getProfileNameById($post_back['profile_id']);

		$keys = ['tel_num', 'fax_num', 'mobile_num', 'email_1', 'email_2', 'nric', 'passport', 'gender', 'race'];
		$post_back = array_merge($post_back, array_fill_keys($keys, ''));
	
		$query_str = "INSERT INTO customer (
						profile_id, name, currency_code, reg_no, gst_no, pic_name, status, category,
						product_category, building, package, package_name, framed_ip_address,
						framed_route, monthly_charge, yearly_charge,
						bill_cycle_month, package_month, stop_service_after, contract_month, login_username,
						login_password, inst_unit_no, inst_addr1, inst_addr2, inst_addr3, inst_city, inst_postcode, inst_state, inst_phone, inst_email, 
						bill_name, bill_addr1, bill_addr2, bill_city, bill_postcode, bill_state, 
						bill_by_post, bill_by_email, 
						tel_num, fax_num, mobile_num, email_1, email_2, nric, passport, gender, race,
						marital_status, household, ownership_type, 
						no_of_employee, no_of_branches, 
						signup_date, activated_date, suspended_date, terminated_date, 
						dealer, remark, payment_term, serial_num, installer_name, caller_id, dia_vars, nationality,
						created_by, created_date, bill_waive_period, delay_trial_start, preferred_install_datetime, router_id, interface_id, core_router_id, core_router_interface_id, edge_router_id, edge_router_interface_id, project_name, unit_name, free_package_upgrade, upgrade_package_id, framed_netmask, framed_ipv6_address, framed_ipv6_route, bill_cycle_start_date, modified_by, modified_date
					) VALUES (
						'" . $this->db->escape_str($post_back['profile_id']) . "', 
						'" . $this->db->escape_str($post_back['name']) . "', 
						'" . $this->db->escape_str($post_back['currency_code']) . "', 
						'" . $this->db->escape_str($post_back['reg_no']) . "', 
						'" . $this->db->escape_str($post_back['gst_no']) . "', 
						'" . $this->db->escape_str($post_back['pic_name']) . "', 
						'" . $this->db->escape_str($post_back['status']) . "', 
						'" . $this->db->escape_str($post_back['category']) . "', 
						" . $this->db->escape_str($post_back['product_category']) . ", 
						'" . $this->db->escape_str($post_back['building']) . "', 
						'" . $this->db->escape_str($package) . "', 
						'" . $this->db->escape_str($post_back['package_name']) . "', 
						'" . $this->db->escape_str($post_back['framed_ip_address']) . "', 
						'" . $this->db->escape_str($post_back['framed_route']) . "', 
						" . $this->db->escape_str($post_back['monthly_charge']) . ", 
						" . $this->db->escape_str($post_back['yearly_charge']) . ", 
						'" . $this->db->escape_str($post_back['bill_cycle_month'] ?? 1) . "', 
						" . $this->db->escape_str($post_back['package_month']) . ", 
						" . $this->db->escape_str($post_back['stop_service_after']) . ", 
						'" . $this->db->escape_str($post_back['contract_month'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['login_username']) . "', 
						'" . $this->db->escape_str($post_back['login_password']) . "', 
						'" . $this->db->escape_str($post_back['inst_unit_no']) . "', 
						'" . $this->db->escape_str($post_back['inst_addr1']) . "', 
						'" . $this->db->escape_str($post_back['inst_addr2']) . "', 
						'" . $this->db->escape_str($post_back['inst_addr3']) . "', 
						'" . $this->db->escape_str($post_back['inst_city']) . "', 
						'" . $this->db->escape_str($post_back['inst_postcode']) . "', 
						'" . $this->db->escape_str($post_back['inst_state']) . "', 
						'" . $this->db->escape_str($post_back['inst_phone']) . "', 
						'" . $this->db->escape_str($post_back['inst_email']) . "', 
						'" . $this->db->escape_str($post_back['bill_name'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['bill_addr1'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['bill_addr2'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['bill_city'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['bill_postcode'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['bill_state'] ?? '') . "', 
						" . $this->db->escape_str($post_back['bill_by_post']) . ", 
						" . $this->db->escape_str($post_back['bill_by_email']) . ", 
						'" . $this->db->escape_str($post_back['tel_num'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['fax_num'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['mobile_num'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['email_1'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['email_2'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['nric'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['passport'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['gender'] ?? '') . "',
						'" . $this->db->escape_str($post_back['race'] ?? '') . "',
						'" . $this->db->escape_str($post_back['marital_status'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['household'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['ownership_type'] ?? '') . "', 
						" . $this->db->escape_str($post_back['no_of_employee']) . ", 
						" . $this->db->escape_str($post_back['no_of_branches']) . ", 
						$signup_date, 
						$activated_date, 
						$suspended_date, 
						$terminated_date, 
						" . $this->db->escape_str($post_back['dealer']) . ", 
						'" . $this->db->escape_str($post_back['remark'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['payment_term'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['serial_num'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['installer_name'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['caller_id'] ?? '') . "', 
						'" . $this->db->escape_str(!empty($post_back['dia_vars']) ? $post_back['dia_vars'] : '{}') . "',
						'" . $this->db->escape_str($post_back['nationality'] ?? '') . "', 
						'" . $this->db->escape_str($username) . "', 
						now(), 
						'" . $this->db->escape_str($post_back['bill_waive_period'] ?? 0) . "', 
						" . $this->db->escape_str($post_back['delay_trial_start']) . ", 
						" . $preferred_install_datetime . ", 
						" . (int)($post_back['router_id'] ?? 0) . ", 
						'" . $this->db->escape_str($post_back['interface_id'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['core_router_id'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['core_router_interface_id'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['edge_router_id'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['edge_router_interface_id'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['project_name'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['unit_name'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['free_package_upgrade'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['upgrade_package_id'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['framed_netmask'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['framed_ipv6_address'] ?? '') . "', 
						'" . $this->db->escape_str($post_back['framed_ipv6_route'] ?? '') . "', 
						" . ($bill_cycle_start_date ? "'$bill_cycle_start_date'" : "NULL") . ", 
						'', 
						NULL) ";

		//echo $query_str;
		
		if($this->db->query($query_str)){
			$model			= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 				
			$action_desc	= 'a new account No:'.$customer_no.' Name:'.$this->db->escape_str($post_back['name']).' has been added';	
			$action_category = 'insert';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
							
			$status_chk = $this->get_latest_status_by_customer_no($customer_no);

			if (!isset($status_chk['status'])) {
				$this->create_status_record($customer_no, $post_back['current_status'], (isset($post_back['transact_date'])?$post_back['transact_date']:''), (isset($post_back['user_idx'])?$post_back['user_idx']:0));
			}

			//customer action log
			$customer_action_model			= 'customer_action_log_model';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$customer_no);

			return $return_val;
		}else{
			return false;
		}
	}
	
	public function customer_deposit_insert($post_back,$username){
		$query_str = "INSERT INTO customer_deposit (customer_no, deposit_date, remark, deposit, 
						payment_source, payment_info, created_by, created_date, modified_by, modified_date ) VALUES (".
								"'" . $post_back['customer_deposit']['customer_no'] . "', ".
								"'" . $post_back['customer_deposit']['deposit_date'] . "', ".
								"'" . $post_back['customer_deposit']['remark'] . "', ".
								"'" . $post_back['customer_deposit']['deposit'] . "', ".
								"'" . $post_back['customer_deposit']['payment_source'] . "', ".
								"'" . $post_back['customer_deposit']['payment_info'] . "', ".
								"'" . $username . "', ".
								" NOW(), ".
								"'" . $username . "', ".
								" NOW()) ";
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method;
		$action_desc	= 'new customer deposit has been added to username : '.$username;	
		$action_category= 'insert';
		$action_log 	=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		
		return $this->db->query($query_str);
	}
	
	
	public function customer_deposit_update($deposit,$username){
		$updated = 0 ;
		//print_r($deposit); exit;
		for( $z = 0 ; $z < $deposit['total_row'] ; $z++ ){
			if( $deposit['idx'][$z] != '' ){

				if ($deposit['origin'][$z] == 'customer') {
					$query_str = " UPDATE customer_deposit 
									SET payment_source = '".$deposit['payment_source'][$z]."' ,
										payment_info = '".$this->db->escape_str( $deposit['payment_info'][$z])."' , 
										deposit = '".$this->db->escape_str( $deposit['payment_amount'][$z] )."'
									WHERE idx = '".$deposit['idx'][$z]."' ";
					
					$model			= 'action_log_model';
					$this->load->model($model);			
					$ctrl			= $this->router->fetch_class();
					$esc_query_str	= $this->db->escape_str($query_str);	
					$method 		= $this->router->method;
					$action_desc	= 'customer deposit has been updated by '.$username;	
					$action_category= 'update';
					$action_log 	=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
					
					$this->db->query($query_str);
					$updated = 1;
				} else if ($deposit['origin'][$z] == 'adjustment') {
					//adjustment
					$query_str = " UPDATE bill_adjustment  
									SET remark = '".$this->db->escape_str( $deposit['payment_info'][$z])."' , 
										amount = '".$this->db->escape_str( $deposit['payment_amount'][$z] )."'
									WHERE adj_no = '".$deposit['idx'][$z]."' ";
					
					$model			= 'action_log_model';
					$this->load->model($model);			
					$ctrl			= $this->router->fetch_class();
					$esc_query_str	= $this->db->escape_str($query_str);	
					$method 		= $this->router->method;
					$action_desc	= 'bill adjustment has been updated by '.$username.' via customer deposit page.';	
					$action_category= 'update';
					$action_log 	=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
					
					$this->db->query($query_str);
					$updated = 1;
				} else if ($deposit['origin'][$z] == 'payment') {
					//payment
					$query_str = " UPDATE payment  
									SET payment_source = '".$deposit['payment_source'][$z]."' ,
										remark = '".$this->db->escape_str( $deposit['payment_info'][$z])."' , 
										amount = '".$this->db->escape_str( $deposit['payment_amount'][$z] )."'
									WHERE idx = '".$deposit['idx'][$z]."' ";
					
					$model			= 'action_log_model';
					$this->load->model($model);			
					$ctrl			= $this->router->fetch_class();
					$esc_query_str	= $this->db->escape_str($query_str);	
					$method 		= $this->router->method;
					$action_desc	= 'payment has been updated by '.$username.' via customer deposit page.';	
					$action_category= 'update';
					$action_log 	=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
					
					$this->db->query($query_str);
					$updated = 1;
				}
			}
		}
		return $updated ; 
	}
	
	public function get_package_history($customer_no = '')
	{
		$return_val['row'] = array();
		$query_str = "SELECT cph.idx, cph.package_name, cph.monthly_charge, cph.start_date, cph.end_date, cph.contract_start_date, cph.contract_end_date, cph.package_no, cph.building, cph.inst_unit_no, cph.inst_addr1, cph.inst_addr2, cph.inst_addr3, cph.inst_city, cph.inst_postcode, cph.inst_state, b.name AS building_name, ss.name AS state_name " . 
					"FROM customer_package_history cph LEFT JOIN building b ON (cph.building = b.building_no) LEFT JOIN sys_state ss ON (ss.state_code = cph.inst_state)  " . 
					"WHERE cph.customer_no = '" . $customer_no . "' " .
					"ORDER BY cph.start_date ";		
		$query = $this->db->query($query_str);
		if ($query->num_rows() > 0)	$return_val['row'] = $query->result_array();
		
		return $return_val;
	}

	public function get_customer_history($customer_no = '')
	{
		$return_val['row'] = array();
		$query_str = "SELECT cs.*, IF(cs.created_by != 0, u.display_name, 'System') AS display_name " . 
					"FROM customer_status cs " . 
					"LEFT JOIN user u ON (cs.created_by = u.idx) " . 
					"WHERE cs.customer_no = '" . $customer_no . "' " .
					"ORDER BY cs.transact_date DESC ";		
		$query = $this->db->query($query_str);
		if ($query->num_rows() > 0)	$return_val['row'] = $query->result_array();
		
		return $return_val;
	}

	public function get_termination_history($customer_no = '')
	{
		$return_val['row'] = array();
		$query_str = "SELECT cs.*, IF(cs.created_by != 0, u.display_name, 'System') AS display_name " . 
					"FROM customer_termination_status cs " . 
					"LEFT JOIN user u ON (cs.created_by = u.idx) " . 
					"WHERE cs.customer_no = '" . $customer_no . "' " .
					"ORDER BY cs.transact_date DESC ";		
		$query = $this->db->query($query_str);
		if ($query->num_rows() > 0)	$return_val['row'] = $query->result_array();
		
		return $return_val;
	}
	
	public function get_customer_listing($txt_search,$page_item_no,$query_where,$query_order="")
	{
		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = 0;

		$query_str = "SELECT count(*) as total_row FROM customer c " . 
					" LEFT JOIN (
						SELECT acs.* 
						FROM customer_status acs 
						JOIN (
							SELECT customer_no, MAX(status_id) AS max_status_id 
							FROM customer_status 
							GROUP BY customer_no
						) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) 
					LEFT JOIN profile p ON (p.acc_id = c.profile_id) ".
					"WHERE (
							c.customer_no LIKE '%$txt_search%' 
							OR c.name LIKE '%$txt_search%' 
							OR p.icno LIKE '%$txt_search%' 
							OR c.login_username LIKE '%$txt_search%' 
							OR c.inst_unit_no LIKE '%$txt_search%'
							OR p.pic_email_1 LIKE '%$txt_search%'
							OR p.pic_email_2 LIKE '%$txt_search%'
							OR p.acc_mobileno LIKE '%$txt_search%'
						) ".
					$query_where;
					
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		if ($query_order == "") {
			$query_order = " ORDER BY c.customer_no "; 
		}
		
		$query_str = "SELECT 
						c.customer_no, 
						c.name, 
						IF(c.activated_date = 0, '', c.activated_date) as activated_date,
						c.package_name, 
						c.monthly_charge, 
						sas.name as status, 
						scc.name as category,
						c.email_1, 
						c.email_2, 
						c.mobile_num, 
						c.preferred_install_datetime, 
						cs.status AS latest_status, 
						cs.transact_date AS transact_date, 
						p.pic_email_1, 
						p.pic_email_2,
						p.acc_mobileno,
						-- this one use to show badge
						CASE 
							WHEN '$txt_search' != '' 
							AND (
								p.pic_email_1 LIKE '%$txt_search%' 
								OR p.pic_email_2 LIKE '%$txt_search%'
							)
							THEN 1 
							ELSE 0 
						-- this one use to show badge
						END AS matched_pic_email,
						CASE 
							WHEN '$txt_search' != '' 
							AND p.acc_mobileno LIKE '%$txt_search%'
							THEN 1 
							ELSE 0 
						END AS matched_pic_phone,
						COALESCE(
							(
								SELECT DATE(MIN(transact_date)) 
								FROM customer_status 
								WHERE customer_no = c.customer_no 
								AND status = 'A'
							), 
							''
						) AS first_activate
						FROM customer c 
						INNER JOIN sys_account_status sas 
							ON c.status = sas.status_code 
						INNER JOIN sys_customer_category scc 
							ON c.category = scc.category_code 
						LEFT JOIN (
							SELECT acs.* 
							FROM customer_status acs 
							JOIN (
								SELECT customer_no, MAX(status_id) AS max_status_id 
								FROM customer_status 
								GROUP BY customer_no
							) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
						WHERE (
							c.customer_no LIKE '%$txt_search%' 
							OR c.name LIKE '%$txt_search%' 
							OR p.icno LIKE '%$txt_search%'
							OR c.login_username LIKE '%$txt_search%' 
							OR c.inst_unit_no LIKE '%$txt_search%'
							OR p.pic_email_1 LIKE '%$txt_search%'
							OR p.pic_email_2 LIKE '%$txt_search%'
							OR p.acc_mobileno LIKE '%$txt_search%'
						) 
						$query_where" . $query_order .  
						" LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		
		$query = $this->db->query($query_str);
		
		$return_val['row'] = ($query->num_rows() > 0) 
			? $query->result_array() 
			: array();

		$return_val['sql'] = $query_str;

		return $return_val;
	}

	public function get_customer_listing_by_profile($acc_id)
	{
		$query_str = "SELECT c.customer_no, c.name, IF(c.activated_date = 0, '', c.activated_date) as activated_date,
								c.package_name, c.monthly_charge, c.category as category_id, sas.name as status, scc.name as category ,
								c.email_1,c.email_2, c.mobile_num, cs.status AS latest_status, cs.transact_date AS transact_date 
									FROM customer c 
									INNER JOIN sys_account_status sas ON c.status = sas.status_code 
									INNER JOIN sys_customer_category scc ON c.category = scc.category_code 
									LEFT JOIN (
										SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
									) cs ON (cs.customer_no = c.customer_no) 
									WHERE profile_id = ? 
									ORDER BY c.customer_no  ";
		$query = $this->db->query($query_str, array($acc_id));
		
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ;
		return $return_val;
	}

	public function get_latest_status_by_customer_no($customer_no='')
	{
		$query_str = "SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) WHERE acs.customer_no = ? ";
		$query = $this->db->query($query_str, array($customer_no));
		$return_val = $query->row_array();
		return $return_val;
	}

	public function get_customer($customer_no='')
	{
		$query = $this->db
					->query("SELECT 
								c.*, 
								p.acc_name, p.acc_type, p.comp_name, p.pic_mobile, p.pic_email_1,
								cs.transact_date, 
								cs.status AS current_status,
								cph.building as new_building, 
								cph.inst_unit_no as new_unit_no, 
								cph.inst_addr1 as new_addr1, 
								cph.inst_addr2 as new_addr2, 
								cph.inst_addr3 as new_addr3, 
								cph.inst_city as new_city, 
								cph.inst_postcode as new_postcode, 
								cph.inst_state as new_state,
								pck.dia_vars as selected_dia_vars,
								(SELECT DATE(MIN(transact_date)) 
								FROM customer_status 
								WHERE customer_no = c.customer_no AND status = 'A'
								) AS first_activate,
								ci.display_name AS installer_name, 
								cd.name AS dealer_name  
							FROM customer c 
							LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
							LEFT JOIN (
								SELECT acs.* FROM customer_status acs 
								JOIN (
									SELECT customer_no, MAX(status_id) AS max_status_id 
									FROM customer_status 
									GROUP BY customer_no
								) bcs ON (acs.status_id = bcs.max_status_id) 
							) cs ON (cs.customer_no = c.customer_no) 
							LEFT JOIN user ci ON (ci.idx = c.installer_name) 
							LEFT JOIN dealer cd ON (cd.dealer_no = c.dealer) 
							LEFT JOIN (
								SELECT acph.* FROM customer_package_history acph
								JOIN (
									SELECT customer_no, MAX(idx) AS max_idx 
									FROM customer_package_history 
									GROUP BY customer_no
								) bcph ON (acph.idx = bcph.max_idx)
							) cph ON (cph.customer_no = c.customer_no)
							LEFT JOIN package pck ON (pck.package_no = c.package)
							WHERE c.customer_no = '".$this->db->escape_str($customer_no)."'
							LIMIT 1
						");

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			if($return_val['signup_date'] == 0){
				$return_val['signup_date'] = '';
			}
			if($return_val['activated_date'] == 0){
				$return_val['activated_date'] = '';
			}
			if($return_val['suspended_date'] == 0){
				$return_val['suspended_date'] = '';
			}
			if($return_val['terminated_date'] == 0){
				$return_val['terminated_date'] = '';
			}
			if($return_val['next_bill_date'] == 0){
				$return_val['next_bill_date'] = '';
			}
			$return_val['btn_delete']='enabled';

			$return_val['contract_expiry'] = '';
		}
		else 
		{
			//Asign blank data for add new customer
			$return_val['customer_no']='';
			$return_val['name']='';
			$return_val['currency_code']='MYR';
			$return_val['reg_no']='';
			$return_val['gst_no']='';
			$return_val['pic_name']='';
			$return_val['pic_designation']='';
			$return_val['status']='r';
			$return_val['category']='r';
			$return_val['product_category']='0';
			$return_val['building']='0';
			$return_val['package']='';
			$return_val['package_name']='';
			$return_val['monthly_charge']='';
			$return_val['yearly_charge']='';
			$return_val['bill_cycle_month'] = '1';
			
			$return_val['login_username']='';
			$return_val['login_password']='';
			$return_val['framed_ip_address'] = '';
			$return_val['framed_route'] = '';
			$return_val['core_router_id'] = '';
			$return_val['core_router_interface_id'] = '';
			$return_val['edge_router_id'] = '';
			$return_val['edge_router_interface_id'] = '';
			$return_val['project_name'] = '';
			$return_val['unit_name'] = '';
			$return_val['package_month']='';
			$return_val['stop_service_after']='';
			$return_val['contract_month']='0';
			
			$return_val['inst_unit_no']= '';
			$return_val['inst_addr1']= '';
			$return_val['inst_addr2']= '';
			$return_val['inst_addr3']= '';
			$return_val['inst_city']= '';
			$return_val['inst_postcode']= '';
			$return_val['inst_state']= 'pg';
			$return_val['inst_phone']= '';
			$return_val['inst_email']= '';
			$return_val['bill_name']= '';
			$return_val['bill_addr1']= '';
			$return_val['bill_addr2']= '';
			$return_val['bill_city']= '';
			$return_val['bill_postcode']= '';
			$return_val['bill_state']= 'pg';
			
			$return_val['bill_by_post']='';
			$return_val['bill_by_email']='1';
			
			$return_val['tel_num']= '';
			$return_val['fax_num']= '';
			$return_val['mobile_num']= '';
			$return_val['email_1']= '';
			$return_val['email_2']= '';
			$return_val['nric']= '';
			$return_val['passport']= '';
			$return_val['date_of_birth']= '';
			$return_val['gender'] = 'm';
			$return_val['race'] = 'm';
			$return_val['marital_status'] = 's';
			$return_val['household'] = '';
			
			$return_val['ownership_type'] = '';
			$return_val['no_of_employee'] = '';
			$return_val['no_of_branches'] = '';
			
			$return_val['signup_date'] = date('Y-m-d');
			$return_val['activated_date'] = '';
			$return_val['suspended_date'] = '';
			$return_val['terminated_date'] = '';
			$return_val['next_bill_date'] = '';
			$return_val['first_activate'] = '';
			$return_val['dealer'] = '';
			$return_val['remark'] = '';
			$return_val['payment_term'] = '30';
			$return_val['serial_num'] = '';
			$return_val['installer_name'] = '';
			$return_val['nationality'] = '';

			$return_val['caller_id'] = '';

			$return_val['dia_vars'] = '';

			$return_val['profile_id'] = '0';
			$return_val['acc_name'] = '';
			$return_val['acc_type'] = 'r';
			$return_val['comp_name'] = '';

			$return_val['contract_expiry'] = '';

			$return_val['pic_mobile'] = '';

			$return_val['current_status'] = 'P';
			$return_val['transact_date'] = date('Y-m-d');

			$return_val['bill_waive_period'] = 0;
			$return_val['delay_trial_start'] = 0;
			$return_val['free_package_upgrade'] = 0;
			$return_val['upgrade_package_id'] = 0;
			$return_val['new_package_effective_date'] = '';
			$return_val['new_package_id'] = '';

			$return_val['router_id'] = 0;
			$return_val['interface_id'] = "";

			$return_val['agent_name'] = "";
			$return_val['installer_name'] = "";

			$return_val['framed_netmask'] = '';
			$return_val['framed_ipv6_address'] = '';
			$return_val['framed_ipv6_route'] = '';
			
			$return_val['preferred_install_datetime'] = '';

			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}
	
	function get_customer_pic( $customer_no ){
		
		$return_val = array();
		
		$sql = " SELECT * FROM customer_pic WHERE customer_no = '".$customer_no."' ";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$return_val = $query->result_array();
		}
	
		return $return_val;
		
	}

	function get_customer_pic_by_pic_id($customer_no, $pic_id) {
		$return_val = array();
		
		$sql = " SELECT * FROM customer_pic WHERE customer_no = '".$customer_no."' and pic_id = '".$pic_id."' ";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
		}
	
		return $return_val;
	}

	public function myisp_get_customer($customer_no='')
	{

		$this->db_myisp = $this->load->database('myisp', true, false);
		
		$query = $this->db_myisp->query("SELECT c.* 
							FROM customer c
							WHERE c.customer_no='".$this->db_myisp->escape_str($customer_no)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			if($return_val['signup_date'] == 0){
				$return_val['signup_date'] = '';
			}
			if($return_val['activated_date'] == 0){
				$return_val['activated_date'] = '';
			}
			if($return_val['suspended_date'] == 0){
				$return_val['suspended_date'] = '';
			}
			if($return_val['terminated_date'] == 0){
				$return_val['terminated_date'] = '';
			}
			if($return_val['next_bill_date'] == 0){
				$return_val['next_bill_date'] = '';
			}
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			//Asign blank data for add new customer
			$return_val['customer_no']='';
			$return_val['name']='';
			$return_val['currency_code']='MYR';
			$return_val['reg_no']='';
			$return_val['gst_no']='';
			$return_val['pic_name']='';
			$return_val['pic_designation']='';
			$return_val['status']='r';
			$return_val['category']='r';
			$return_val['product_category']='0';
			$return_val['building']='0';
			$return_val['package']='';
			$return_val['package_name']='';
			$return_val['monthly_charge']='';
			$return_val['yearly_charge']='';
			$return_val['bill_cycle_month'] = '1';
			
			$return_val['login_username']='';
			$return_val['login_password']='';
			$return_val['framed_ip_address'] = '';
			$return_val['framed_route'] = '';
			$return_val['package_month']='';
			$return_val['stop_service_after']='';
			$return_val['contract_month']='0';
			
			$return_val['inst_unit_no']= '';
			$return_val['inst_addr1']= '';
			$return_val['inst_addr2']= '';
			$return_val['inst_addr3']= '';
			$return_val['inst_city']= '';
			$return_val['inst_postcode']= '';
			$return_val['inst_state']= '';
			$return_val['inst_phone']= '';
			$return_val['inst_email']= '';
			$return_val['bill_name']= '';
			$return_val['bill_addr1']= '';
			$return_val['bill_addr2']= '';
			$return_val['bill_city']= '';
			$return_val['bill_postcode']= '';
			$return_val['bill_state']= '';
			
			$return_val['bill_by_post']='';
			$return_val['bill_by_email']='1';
			
			$return_val['tel_num']= '';
			$return_val['fax_num']= '';
			$return_val['mobile_num']= '';
			$return_val['email_1']= '';
			$return_val['email_2']= '';
			$return_val['nric']= '';
			$return_val['passport']= '';
			$return_val['date_of_birth']= '';
			$return_val['gender'] = 'm';
			$return_val['race'] = 'm';
			$return_val['marital_status'] = 's';
			$return_val['household'] = '';
			
			$return_val['ownership_type'] = '';
			$return_val['no_of_employee'] = '';
			$return_val['no_of_branches'] = '';
			
			$return_val['signup_date'] = date('Y-m-d');
			$return_val['activated_date'] = '';
			$return_val['suspended_date'] = '';
			$return_val['terminated_date'] = '';
			$return_val['next_bill_date'] = '';
			$return_val['dealer'] = '';
			$return_val['remark'] = '';
			$return_val['payment_term'] = '30';
			$return_val['serial_num'] = '';
			$return_val['installer_name'] = '';
			$return_val['nationality'] = '';

			$return_val['caller_id'] = '';

			$return_val['profile_id'] = '0';
			$return_val['acc_name'] = '';

			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}
	
	function myisp_get_customer_pic( $customer_no ){

		$this->db_myisp = $this->load->database('myisp', true, false);
		
		$return_val = array();
		
		$sql = " SELECT * FROM customer_pic WHERE customer_no = '".$customer_no."' ";
		$query = $this->db_myisp->query($sql);
		if ($query->num_rows() > 0) {
			$return_val = $query->result_array();
		}
	
		return $return_val;
		
	}
	
	
	function payment_of_deposit($category, $status, $payment_source, $dealer, $date_start, $date_end,$order_by='')
	{
		$return_val = array();
		$query_where = '';
		$amount = 0;
		
		$payment_source_list = $_SESSION['payment_source'];
		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		
		if ( !empty($category) && $category !== 'all' ) {
			$query_where = "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}
		if ( !empty($payment_source) && $payment_source !== 'all' ) {
			$query_where .= "AND cd.payment_source = '$payment_source' ";
		}
		if ( !empty($dealer) && $dealer !== 'all' ) {
			$query_where .= " AND c.dealer =  '".$dealer."' ";
		}
		
		
		$order_where = "";
		if ( $order_by != "" ){
			if( $order_by == "customer" )
				$order_where .= " , cd.customer_no , cd.deposit_date ";
			elseif( $order_by == "date" )
				$order_where .= " , cd.deposit_date, cd.customer_no ";
		}
			
		$query_str = "SELECT c.category, c.customer_no, c.name as customer_name,  b.name as building_name, 
						DATE_FORMAT(cd.deposit_date,'%Y-%m-%d') AS pay_date, cd.remark, 'DEPOSIT' AS payment_no, 
						cd.deposit AS amount, '4' AS bill_type, cd.payment_source , cd.payment_info AS cheque_no , 
						sps.name AS payment_source_name 
						FROM customer_deposit cd 
						INNER JOIN customer c ON c.customer_no = cd.customer_no 
						LEFT JOIN building b ON b.building_no = c.building 
						LEFT JOIN sys_payment_source sps ON sps.payment_source_id = cd.payment_source
						WHERE cd.deposit_date >= '$date_start' AND cd.deposit_date <= '$date_end' 
							$query_where
						ORDER BY c.category $order_where ";
		$query = $this->db->query($query_str);
		foreach ( $query->result_array() as $row ) {
			$category_name = $customer_category_list[$row['category']];
			$payment_source_name = $row['payment_source_name'];
			$bill_type_name = $bill_type_list[$row['bill_type']];
			
			$return_val[$category_name][$bill_type_name][] = array(
															"pay_date" => $row['pay_date'],
															"customer_no" => $row['customer_no'],
															"customer_name" => $row['customer_name'],
															"building_name" => $row['building_name'],
															"remark" => $row['remark'],
															"payment_no" => $row['payment_no'],
															"payment_source_name" => $payment_source_name,
															"cheque_no" => $row['cheque_no'],
															"amount" => $row['amount'],
															);
		}
		return $return_val;
	}
	
	function get_customer_category( $category_code ){
		$query_str = " SELECT * FROM sys_customer_category WHERE category_code = '".$category_code."' ";
		$query = $this->db->query($query_str);
		if( $query->num_rows() ){
			$row = $query->row_array();
		}else{
			$row['category_code']='';
			$row['name']='';
			$row['ledger_account_code']='';
		}		
		return $row;
	}
	
	function get_customer_monthly_charge( $customer_no , $date='' ){
		
		if( $date == '' ){
			$date = date("Y-m-d");
		}else{
			$date = date("Y-m-d" , strtotime($date) );
		}
		
		//~ $str = 	"	SELECT monthly_charge, UNIX_TIMESTAMP(start_date) AS start_date , 
							//~ UNIX_TIMESTAMP( IFNULL( end_date , NOW() ) ) AS end_date ,
							//~ UNIX_TIMESTAMP('".$date."') AS last_month_end
					//~ FROM customer_package_history 
					//~ WHERE customer_no = '".$customer_no."' 
					//~ HAVING last_month_end BETWEEN start_date AND end_date ";
					
		$str = 	"	SELECT package_no, monthly_charge, UNIX_TIMESTAMP(start_date) AS start_date , 
							UNIX_TIMESTAMP( IFNULL( end_date , '".date('Y-m-d', strtotime( $date ))."' ) ) AS end_date ,
							UNIX_TIMESTAMP('".$date."') AS last_month_end, package_name
					FROM customer_package_history 
					WHERE customer_no = '".$customer_no."' 
					HAVING last_month_end BETWEEN start_date AND end_date 
					ORDER BY start_date DESC LIMIT 1";

		//echo $str; exit;
					
		$query = $this->db->query($str);
		if( $query->num_rows() ){
			$row = $query->row_array();
		}else{
			$row['monthly_charge']='';
		}	
		
		return $row;

	}
	

	function get_deposit_export($category, $date_start, $date_end)
	{
		$return_val = array();
		$query_where = '';
		$subquery_where =  '';
		$amount = 0;
		$return_val['total_row'] = 0;
		$return_val['row'] = array();
		
		$payment_source_list = $_SESSION['payment_source'];
		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		
		if ( !empty($category) && $category !== 'all' ) {
			$query_where = "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}
		if ( !empty($payment_source) && $payment_source !== 'all' ) {
			$subquery_where .= "AND payment_source = '$payment_source' ";
		}
		/*
		$query_str = "SELECT c.category, c.customer_no, c.name as customer_name,  b.name as building_name, 
						DATE_FORMAT(cd.deposit_date,'%Y-%m-%d') AS pay_date, cd.remark, 'DEPOSIT' AS payment_no, 
						cd.deposit AS amount, '4' AS bill_type, cd.payment_source , cd.payment_info AS cheque_no , 
						sps.name AS payment_source_name 
						FROM customer_deposit cd 
						INNER JOIN customer c ON c.customer_no = cd.customer_no 
						LEFT JOIN building b ON b.building_no = c.building 
						LEFT JOIN sys_payment_source sps ON sps.payment_source_id = cd.payment_source
						WHERE cd.deposit_date >= '$date_start' AND cd.deposit_date <= '$date_end' 
							$subquery_where
						ORDER BY c.category, cd.deposit_date DESC, cd.customer_no ";
		*/
		$query_str = " 	SELECT 
							c.category, c.customer_no, c.name as customer_name,  b.name as building_name,
							DATE_FORMAT(z.deposit_date,'%Y-%m-%d') AS pay_date, 
							sps.name AS payment_source_name ,z.* 
						FROM (
		
						(
							SELECT 	cd.customer_no , 
									cd.deposit_date , cd.remark, 
									'DEPOSIT' AS payment_no, 
									cd.deposit AS amount, 
									'4' AS bill_type, cd.payment_source , 
									cd.payment_info AS cheque_no ,
									0 AS void
							FROM customer_deposit cd 
							WHERE cd.deposit_date BETWEEN '$date_start' AND '$date_end' $subquery_where 
						)
						UNION 
						(
							SELECT  ba.customer_no, 
									ba.tranx_date as deposit_date, 
									ba.remark, 
									'DEPOSIT' AS payment_no,
									CASE WHEN ba.adjust_type = 'dr' THEN ba.amount 
										 WHEN ba.adjust_type = 'cr' THEN -(ba.amount) END AS amount, 
									ba.bill_type AS bill_type, 
									'' AS payment_source , 
									'' AS cheque_no ,
									CASE WHEN b.bill_no IS NOT NULL THEN is_void ELSE 0 END AS void 
							FROM bill_adjustment ba 
							LEFT JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
							LEFT JOIN bill b ON ( ba.bill_no = b.bill_no )
							WHERE ba.tranx_date BETWEEN '$date_start' AND '$date_end' 
							AND ba.bill_type IN ( '4' , '11' , '14' ) 
							HAVING void = 0
						)
						UNION
						(
						
							SELECT  p.customer_no, 
									p.pay_date as deposit_date, 
									p.remark, 
									p.payment_no,
									p.amount AS amount, 
									p.bill_type,
									p.payment_source , 
									p.cheque_no,
									CASE WHEN b.bill_no IS NOT NULL THEN is_void ELSE 0 END AS void
							FROM payment p
							INNER JOIN sys_bill_type sbt ON p.bill_type = sbt.bill_type_id 
							LEFT JOIN bill b ON ( p.bill_no = b.bill_no ) 
							WHERE p.pay_date BETWEEN '$date_start' AND '$date_end' $subquery_where 
							AND sbt.is_debit = 1 AND p.bill_type IN ( '4', '11', '14' ) 
						
						)
					) z
					INNER JOIN customer c ON c.customer_no = z.customer_no 
					LEFT JOIN building b ON b.building_no = c.building 
					LEFT JOIN sys_payment_source sps ON sps.payment_source_id = z.payment_source							
					$query_where 
					ORDER BY c.category, pay_date DESC, c.customer_no ";
		
		$query = $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		return $return_val;
	}

	function customer_detail($customer_no,$gen_pdf = 0 ,$pdf_name = ''){

		$return_val = '';

		$query_str  = "SELECT * 
					FROM customer c 
					WHERE c.customer_no = '$customer_no'";

		$query = $this->db->query($query_str);



		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->row_array();
		}

		
		$return_val['gen_pdf'] 		= $gen_pdf;

		return $return_val;

	}	


	function filtered_customer($search,$query_where){

		$return_val = '';

		$query_str = "SELECT 
				CASE WHEN (c.bill_addr1 is not null AND c.bill_addr1 !='') THEN (c.bill_name) ELSE c.name END AS customer_name,
		        CASE WHEN (c.bill_addr1 is not null AND c.bill_addr1 !='')  THEN (c.bill_addr1) ELSE c.inst_addr1 END AS addr1,
				CASE WHEN (c.bill_addr1 is not null AND c.bill_addr1 !='') THEN (c.bill_addr2) ELSE c.inst_addr2 END AS addr2,
		        CASE WHEN (c.bill_addr1 is not null AND c.bill_addr1 !='') THEN (c.bill_city) ELSE c.inst_city END AS city,
		        CASE WHEN (c.bill_addr1 is not null AND c.bill_addr1 !='') THEN (c.bill_postcode) ELSE c.inst_postcode END AS postcode,
				CASE WHEN (c.bill_addr1 is not null AND c.bill_addr1 !='') THEN (st.name) ELSE ss.name END AS state ,
				c.email_1, c.email_2 , c.mobile_num, c.customer_no, p.pic_email_1, p.pic_email_2  
		        FROM customer c 
		        LEFT JOIN sys_state ss ON c.inst_state = ss.state_code
				LEFT JOIN sys_state st ON c.bill_state = st.state_code 
				LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
				LEFT JOIN (
					SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
				) cs ON (cs.customer_no = c.customer_no) 
		        WHERE (c.customer_no LIKE '%$search%' OR c.name LIKE '%$search%' OR p.icno LIKE '%$search%' OR p.acc_name LIKE '%$search%') ".
					$query_where;
					// var_dump($query_str);
					// die();
		$querys = $this->db->query($query_str)->result_array();

		return $querys;
	}

	function get_customer_no_by_phone() {
		$customer = array();
		$querys = $this->db->query("SELECT * FROM customer")->result_array();
		foreach ($querys as $query) {
			$customer[$query['mobile_num']] = $query['customer_no'];
		}

		return $customer;
	}
	
	function get_customer_support_listing($txt_search='',$page_item_no=0,$row_per_page='',$query_where=''){
		
		$txt_search = $this->db->escape_str($txt_search);
		$return_val['total_row'] = 0;
		
		$query_str 	= "SELECT count(cs.cs_id) AS total_row 
						FROM customer_support cs
						WHERE cs.cs_no LIKE '%$txt_search%' " . $query_where ;
		
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		if( $row_per_page == '' )
			$row_per_page = $_SESSION['config']['max_page_item'];
		
		$query_str 	= "	SELECT cs.* , 
							CASE
								WHEN cs.cs_status = 0 THEN 'Open'
								WHEN cs.cs_status = 1 THEN 'In Progress'
								WHEN cs.cs_status = 2 THEN 'Escalated to 3rd Level'
								WHEN cs.cs_status = 3 THEN 'Closed'
							END AS cs_status_name,
							IF( cs.report_on = NULL, '-', cs.report_on ) AS report_on,
							IF( cs.onsite_on = NULL, '-', cs.onsite_on ) AS onsite_on,
							IF( cs.closed_on = NULL, '-', cs.closed_on ) AS closed_on,
							IF( cs.created_on = NULL, '-', cs.created_on ) AS created_on,
							IF( cs.updated_on = NULL, '-', cs.updated_on ) AS updated_on,
							u.username AS service_by_name
						FROM customer_support cs 
						LEFT JOIN user u ON cs.service_by = u.idx 
						WHERE cs.cs_no LIKE '%$txt_search%' $query_where 
						ORDER BY cs.created_on DESC, cs.cs_no DESC
						LIMIT $page_item_no, $row_per_page ";
						
		$query = $this->db->query($query_str);
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ; 
		return $return_val;
		
	}

	function get_single_customer_support_by_id($cs_id) {
		$return_val = [];

		$query_str = "SELECT * FROM customer_support WHERE cs_id = ?";

		$query = $this->db->query($query_str,[$cs_id]);

		$return_val = $query->row_array();

		return $return_val;
	}
	
	function get_single_customer_support( $cs_no = '' ){
		
		$return_val = array();
		
		$query_str 	= "	SELECT cs.* , cs.cs_id AS temp_id, cs.email_to_user_id AS  prev_email_to_user_id, cs.cs_status as prev_status, cs.service_by as prev_service_by,
								c.name AS customer_name ,
								CONCAT( c.customer_no , ' ' , c.name ) AS customer_no_name, 
								CASE
									WHEN cs.cs_status = 0 THEN 'Open'
									WHEN cs.cs_status = 1 THEN 'In Progress'
									WHEN cs.cs_status = 2 THEN 'Escalated to 3rd Level'
									WHEN cs.cs_status = 3 THEN 'Closed'
								END AS cs_status_name,
								IF( cs.report_on = NULL, '-', cs.report_on ) AS report_on,
								IF( cs.onsite_on = NULL, '-', cs.onsite_on ) AS onsite_on,
								IF( cs.closed_on = NULL, '-', cs.closed_on ) AS closed_on,
								IF( cs.created_on = NULL, '-', cs.created_on ) AS created_on,
								IF( cs.updated_on = NULL, '-', cs.updated_on ) AS updated_on,
								u.display_name AS service_by_name ,
								u2.display_name AS created_by_name
						FROM customer_support cs 
						LEFT JOIN customer c ON c.customer_no = cs.customer_no
						LEFT JOIN user u ON cs.service_by = u.idx 
						LEFT JOIN user u2 ON cs.created_by = u2.username
						WHERE cs.cs_no = '".$cs_no."'  " ;
		$query = $this->db->query($query_str);
		
		if ( $query->num_rows() > 0 ){
			$return_val = $query->row_array();
		}else{
			$return_val['cs_id'] = '' ;
			$return_val['cs_no'] = '';
			$return_val['report_on'] = '';
			$return_val['onsite_on'] = '';
			$return_val['customer_no'] = '';
			$return_val['customer_no_name'] = '';
			$return_val['contact_no'] = '';
			$return_val['package_name'] = '';
			$return_val['package'] = '';
			$return_val['customer_addr'] = '';
			$return_val['premises_type'] = '';
			$return_val['service_type'] = '';
			$return_val['service_problem'] = '';
			$return_val['service_remark'] = '';
			$return_val['action_remark'] = '';
			$return_val['test_download'] = '';
			$return_val['test_upload'] = '';
			$return_val['test_latency'] = '';
			$return_val['open_website'] = '';
			$return_val['cs_status'] = '';
			$return_val['cs_status_name'] = '';	
			$return_val['email_to_user_id'] = '';	
			$return_val['prev_email_to_user_id'] = '';
			$return_val['closed_on'] = '';
			$return_val['customer_comment'] = '';
			$return_val['customer_remark'] = '';
			$return_val['service_by'] = '';
			$return_val['service_by_name'] = '';
			$return_val['created_on'] = '';
			$return_val['created_by'] = '';
			$return_val['created_by_name'] = '';
			$return_val['prev_status']	= '';
			$return_val['prev_service_by']	= '';
			$return_val['temp_id'] = rand(10000, 99999);
		}
		
		
		return $return_val ; 
		
	}
	
	function get_new_cs_no(){
		$return_val['cs_no'] = "";
		$this->load->model('common_model');
		$results = $this->common_model->get_table( '`sys_config`', '`val`', 'key = "cs_no" AND category = "customer_supports " ' );
		
		$return_val['cs_no'] = ( !empty($results) ) ? $results[0]['val'] : "1" ;
		
		$results = $this->common_model->get_table( '`sys_config`', '`val`', 'key = "cs_no_prefix" AND category = "customer_supports " ' );
		
		$return_val['cs_no_prefix'] = ( !empty($results) ) ? $results[0]['val'] : "TT" ;
		
		$results = $this->common_model->get_table( '`sys_config`', '`val`', 'key = "cs_no_length" AND category = "customer_supports " ' );
		$return_val['cs_no_length'] = ( !empty($results) ) ? $results[0]['val'] : "6" ;
		
		$return_val['cs_no'] = str_pad( ( abs( $return_val['cs_no'] ) + 1 ), ( $return_val['cs_no_length'] ), 0, STR_PAD_LEFT );
		
		$return_val['cs_no_full'] = $return_val['cs_no_prefix'] . $return_val['cs_no'];
		return $return_val;
	}
	
	function update_cs_no( $cs_no ){
		
		$return_val = 0 ;
		
		$sql = "	UPDATE sys_config SET `val` = '".$cs_no."' 
					WHERE `key` = 'cs_no' AND `category` = 'customer_supports' ;  ";
		$this->db->query($sql);
		if( $this->db->affected_rows() > 0 ){
			$return_val = 1;
		}else{
			
			$sql = " INSERT INTO `sys_config` 
						SET `val` = '".$cs_no."' , `key` = 'cs_no', `category` = 'customer_supports' ";
			$this->db->query($sql);
			if( $this->db->affected_rows() > 0 ){
				$return_val = 1;
			}
		}
		
		return $return_val ; 
		
	}
	
	function customer_support_insert( $post_back, $created_by ){

		$on_site_date = $this->db->escape_str( $post_back['on_site_date'] );
		if (empty($on_site_date)) {
			$on_site_date = 'NULL';
		} else {
			$on_site_date = "'".$this->db->escape_str( $post_back['on_site_date'] )."'";
		}

		$package_id = $this->db->escape_str( $post_back['package_id'] );
		if (empty($package_id)) {
			$package_id = '0';
		}

		$closed_on = $this->db->escape_str( $post_back['closed_on'] );
		if (empty($closed_on)) {
			$closed_on = 'NULL';
		} else {
			$closed_on = "'".$this->db->escape_str( $post_back['closed_on'] )."'";
		}

		$test_download = $this->db->escape_str( $post_back['test_download'] );
		if (empty($test_download)) {
			$test_download = '0';
		}

		$test_upload = $this->db->escape_str( $post_back['test_upload'] );
		if (empty($test_upload)) {
			$test_upload = '0';
		}

		$test_latency = $this->db->escape_str( $post_back['test_latency'] );
		if (empty($test_latency)) {
			$test_latency = '0';
		}
		
		$return_val = 0;
		$cs_no = $this->get_new_cs_no();
		$sql = " INSERT INTO customer_support 
						SET	cs_no = '".$cs_no['cs_no_full']."' , 
							report_on = '".$this->db->escape_str( $post_back['report_date'] )."' ,
							onsite_on = ".$on_site_date." ,
							customer_no = '". $this->db->escape_str( $post_back['customer_no'] ) ."' ,
							contact_no = '". $this->db->escape_str( $post_back['contact_no'] ) ."' ,    
							package_name = '". $this->db->escape_str( $post_back['package_name'] ) ."' ,
							package = ".$package_id." ,
							customer_addr = '". $this->db->escape_str( $post_back['customer_address'] ) ."' ,
							premises_type = '". $this->db->escape_str( $post_back['premises_type'] ) ."' ,
							service_type = '". $this->db->escape_str( $post_back['service_type'] ) ."' ,  
							service_problem = '".$this->db->escape_str( $post_back['service_problem'] )."' , 
							service_remark = '". $this->db->escape_str( $post_back['service_remark'] ) ."' ,
							action_remark = '". $this->db->escape_str( $post_back['service_action_taken'] ) ."' ,
							test_download = '". $test_download ."' ,
							test_upload = '". $test_upload ."' , 
							test_latency = '". $test_latency ."' ,
							open_website = '". $this->db->escape_str( $post_back['test_website'] ) ."' ,
							cs_status = '". $this->db->escape_str( $post_back['status'] ) ."' ,
							email_to_user_id = '". $this->db->escape_str( $post_back['email_to_user_id'] ) ."' ,
							closed_on = ".$closed_on." ,
							customer_comment = '". $this->db->escape_str( $post_back['customer_rating'] ) ."' ,
							customer_remark = '". $this->db->escape_str( $post_back['customer_remark'] ) ."' ,
							service_by = '". $this->db->escape_str( $post_back['service_by'] ) ."' ,
							created_on = NOW() ,
							created_by = '".$created_by."', updated_on = NULL, updated_by = '' ;" ;

		$this->db->query($sql);
		if (empty($post_back['cs_id'])) {
			$cs_id = $this->db->insert_id();
		}

		if( $this->db->affected_rows() > 0 ){
			$return_val = 1 ; 
			$this->update_cs_no( $cs_no['cs_no'] );
		}
		
		return $cs_id ; 
		
	}
	
	function customer_support_update( $post_back, $created_by ){

		$return_val = 0;

		$on_site_date = $this->db->escape_str( $post_back['on_site_date'] );
		if (empty($on_site_date)) {
			$on_site_date = 'NULL';
		} else {
			$on_site_date = "'".$this->db->escape_str( $post_back['on_site_date'] )."'";
		}

		$package_id = $this->db->escape_str( $post_back['package_id'] );
		if (empty($package_id)) {
			$package_id = '0';
		}

		$closed_on = $this->db->escape_str( $post_back['closed_on'] );
		if (empty($closed_on)) {
			$closed_on = 'NULL';
		} else {
			$closed_on = "'".$this->db->escape_str( $post_back['closed_on'] )."'";
		}

		$test_download = $this->db->escape_str( $post_back['test_download'] );
		if (empty($test_download)) {
			$test_download = '0';
		}

		$test_upload = $this->db->escape_str( $post_back['test_upload'] );
		if (empty($test_upload)) {
			$test_upload = '0';
		}

		$test_latency = $this->db->escape_str( $post_back['test_latency'] );
		if (empty($test_latency)) {
			$test_latency = '0';
		} 
		
		$sql = " UPDATE customer_support 
						SET	report_on = '".$this->db->escape_str( $post_back['report_date'] )."' ,
							onsite_on = ".$on_site_date." ,
							customer_no = '". $this->db->escape_str( $post_back['customer_no'] ) ."' ,
							contact_no = '". $this->db->escape_str( $post_back['contact_no'] ) ."' ,
							package_name = '". $this->db->escape_str( $post_back['package_name'] ) ."' ,
							package = ".$package_id." ,
							customer_addr = '". $this->db->escape_str( $post_back['customer_address'] ) ."' ,
							premises_type = '". $this->db->escape_str( $post_back['premises_type'] ) ."' ,
							service_type = '". $this->db->escape_str( $post_back['service_type'] ) ."' ,
							service_problem = '". $this->db->escape_str( $post_back['service_problem'] ) ."' ,
							service_remark = '". $this->db->escape_str( $post_back['service_remark'] ) ."' ,
							action_remark = '". $this->db->escape_str( $post_back['service_action_taken'] ) ."' ,
							test_download = '". $test_download ."' ,
							test_upload = '". $test_upload ."' ,
							test_latency = '". $test_latency ."' ,
							open_website = '". $this->db->escape_str( $post_back['test_website'] ) ."' ,
							cs_status = '". $this->db->escape_str( $post_back['status'] ) ."' ,
							email_to_user_id = '". $this->db->escape_str( $post_back['email_to_user_id'] ) ."' ,
							closed_on = ".$closed_on." ,
							customer_comment = '". $this->db->escape_str( $post_back['customer_rating'] ) ."' ,
							customer_remark = '". $this->db->escape_str( $post_back['customer_remark'] ) ."' ,
							service_by = '". $this->db->escape_str( $post_back['service_by'] ) ."' ,
							updated_on = NOW(),
							updated_by = '". $created_by ."' 
					WHERE 	cs_id = '".$post_back['cs_id']."' ; " ;

		$this->db->query($sql);

		if( $this->db->affected_rows() > 0 ){
			$return_val = 1 ; 
		}
		
		/**
		 * Save Job Tracking Details
		 * This will save the job tracking details and send notifications to the users involved.
		 */
		if (
			isset($post_back['new_comment'], $post_back['new_comment_date'], $post_back['new_comment_rec_id'], $post_back['reply_to']) &&
			!empty($post_back['new_comment'])
		) {
			$cs_id     = $post_back['cs_id'];
			$cs_no     = $post_back['cs_no'];
			$rec_id    = $post_back['new_comment_rec_id'];
			$date      = $post_back['new_comment_date'];
			$remark    = $post_back['new_comment'];
			$reply_raw = $post_back['reply_to'];
			$created_by = $this->user['idx'];

			$reply_to = array_filter($reply_raw);

			if (!empty($reply_to) && $reply_to[0] === 'all') {
				$reply_to = [];
			}

			$admin_list = $this->common_model->get_admin_list();
			$cs_assign_to = [$post_back['service_by']];
			// $cs_assign_to = explode(",", str_replace( '"', '', $post_back['cs_assign_to'])) ?? [];

			$admins = array_column($admin_list, 'user_id');
			$all_user_ids = array_unique(array_map('intval', array_merge($reply_to, $admins)));

			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');

			$prepend = '';
			if (!empty($reply_to)) {
				$names = array_filter(array_map(fn($id) => $user_list[$id] ?? null, $reply_to));
				$prepend = $this->user['display_name'] . ' replied to ' . implode(', ', $names) . ":\n";
			}
			$comment_text = $prepend . $remark;

			$this->save_job_tracking_detail($cs_id, $rec_id, $comment_text, $created_by, $reply_to);

			$params = [
				'cs_id' => $cs_id,
				'cs_no' => $cs_no,
				'remark' => $remark,
				'date' => $date
			];

			$this->send_comment_notification($params, $cs_assign_to, $reply_to, 'trouble_ticket');
		}

		return $post_back['cs_id']; 
	}

	function customer_support_delete($cs_id, $attachments = []) {
		$this->db->where('cs_id', $cs_id)->delete('customer_support');

		if ($this->db->affected_rows() > 0) {
			$this->db->where('cs_id', $cs_id)->delete('cs_job_tracking');

			if (!empty($attachments)) {
				$this->load->model('common_model');
				foreach ($attachments as $attach) {
					$this->common_model->remove_attachment($attach, $cs_id);
				}
			}

			return 1;
		}

		return 0;
	}

	/**
	 * Sends notifications to users when a comment is added to a Ticket.
	 * Notifies all assigned users if `reply_to` is empty, or only the specific users.
	 *
	 * @param array  $cs_comment_info Comment details.
	 * @param array  $cs_assign_to    Assigned user IDs.
	 * @param array  $reply_to        Optional user IDs to notify specifically.
	 * @param string $type            Ticket type.
	 * @return mixed
	 */
	public function send_comment_notification($cs_comment_info, $cs_assign_to, $reply_to = [], $type = 'trouble_ticket') {
		$cs_id = $cs_comment_info['cs_id'];
		$cs_no = $cs_comment_info['cs_no'];
		$remark = $cs_comment_info['remark'];
		$date = $cs_comment_info['date'];

		$created_by_name = $this->user['display_name'];

		$ticket_type = $type === 'trouble_ticket'
			? 'Trouble Ticket'
			: 'Service Ticket';

		$email_list = [];
		$whatsapp_list = [];
		$telegram_list = [];

		$collect_contacts = function ($users) use (&$email_list, &$whatsapp_list, &$telegram_list) {
			foreach ($users ?? [] as $user) {

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

			$email_body = "{$ticket_type} No: {$cs_no}" .
				"<br>{$ticket_type} Details and Comments:" .
				"<br>Updated On: {$date}" .
				"<br>Comment: {$remark}" .
				"<br>Updated By: {$created_by_name}";

			$all_technical_list = $this->common_model->get_technical_list('customer_support');

			$all_assigned_and_pic = $this->get_service_ticket_reply_list(
				$all_technical_list,
				$cs_assign_to,
				'doc_do_send'
			);

			$collect_contacts($all_assigned_and_pic);

		} else {

			$notification = "{$created_by_name} replied to you.";

			$email_body = "{$ticket_type} No: {$cs_no}" .
				"<br><br>{$created_by_name} replied to you:" .
				"<br>{$remark}" .
				"<br><br>Updated On: {$date}";

			$reply_to_ids = array_map('strval', $reply_to);

			$all_user_info = $this->get_service_ticket_reply_list(
				$this->common_model->get_technical_list('customer_support'),
				$cs_assign_to,
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

		$header = "{$ticket_type} [{$cs_no}] Details and Comments Updated";

		$this->notification_service->queue(
			$contacts,
			$header,
			$email_body,
			'TICKET JOB TRACKING',
			[
				'ticket_type' 	=> !empty($ticket_type) ? $ticket_type : '-',
				'ticket_no' 	=> !empty($cs_no) ? $cs_no : '-',
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
				'doc_id'        => $cs_id,
				'send_method'   => 'manual',
				'email_starter' => $header,
				'doc_type'      => '[Ticket Details and Comments Updated]',
			]
		);

		return true;
	}

	function get_cs_service_listing($txt_search='',$page_item_no=0,$row_per_page='',$query_where='')
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

	function get_single_cs_service( $cs_type='', $id=0 ) {
		$return_val = [];
		if(!empty($cs_type) && !empty($id)) {
			$query_str 	= "SELECT "
						. "$cs_type" . "_id AS temp_id,"
						. "$cs_type" . "_id AS id,"
						. "$cs_type" . "_name AS name,"
						. "$cs_type" . "_desc AS `desc` "
						. "FROM $cs_type WHERE "
						. "$cs_type" . "_id = "
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

	function save_cs_service( $cs_type, $post_data, $userId ) {

		if(empty($post_data['id'])) {
			$str = "INSERT INTO $cs_type ("
				. "$cs_type" . "_id,"
				. "$cs_type" . "_name,"
				. "$cs_type" . "_desc,"
				. "created_on,created_by"
				. ") VALUES (?,?,?,NOW(),?)";
			$query = $this->db->query($str,[0,$post_data['name'],$post_data['desc'],$userId]);
		} else {
			$str = "UPDATE $cs_type SET "
				. "$cs_type" . "_name = ?,"
				. "$cs_type" . "_desc = ?,"
				. "updated_on = NOW(),"
				. "updated_by = ? WHERE "
				. "$cs_type" . "_id = ?";
			$query = $this->db->query($str,[$post_data['name'],$post_data['desc'],$userId,$post_data['id']]);
		}

		return $post_data;
	}

	function delete_cs_service( $cs_type, $post_data ) {
		$query_str = " DELETE FROM $cs_type WHERE "
			. "$cs_type" . "_id = ?";
		$query = $this->db->query($query_str,[$post_data['id']]);

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 	
		
		$csTypeText = '';
		$name = $post_data['name'];
		if($cs_type == 'cs_service') {
			$csTypeText = 'cs service type';
		} else {
			$csTypeText = 'cs problem type';
		}
		
		$action_desc	= "a $csTypeText ($name) has been deleted";
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
	}
	
	function save_customer_support_settings( $post_data ){
		
		foreach( $post_data AS $key => $val ){
			
			$sql = " SELECT * FROM `sys_config` WHERE `key` = '".$key."' ; ";
			$query = $this->db->query($sql);
			
			if( $query->num_rows() > 0 ){
				$sql = " UPDATE `sys_config` SET `val` = '".$val."' WHERE `key` = '".$key."' ";
			}else{
				$sql = " INSERT INTO `sys_config` SET 	`key` = '".$key."', 
														`val` = '".$val."', 
														`category` = 'customer_supports' ; ";
			}
			
			$this->db->query($sql);
		}
		
	}
	
	function delete_customer_pic( $customer_no, $customer_pic ){
		$return_val = array();
		if( $customer_no !='' ){
			if( $customer_pic == '' ){
				$pic_sql = "UPDATE customer 
								SET pic_name = '', tel_num  = '', fax_num	 = '', mobile_num = '',
									email_1 = '', email_2 = '', nric = '', passport = '',
									date_of_birth = NULL, gender = '', race = '' ,
									modified_date = NOW()
							WHERE customer_no = '".$customer_no."' ";
			}elseif( $customer_pic != '' ){
				$pic_sql = "DELETE FROM customer_pic 
							WHERE customer_no = '".$customer_no."' AND pic_id = '".$customer_pic."' ";
			}
			
			$this->db->query($pic_sql);
			if( $this->db->affected_rows() > 0 ){
				$return_val['success'] = 1;
			}else{
				$return_val['success'] = 0;
			}
			$return_val['sql'] = $pic_sql ; 
		}else{
			$return_val['success'] = 0;
			$return_val['sql'] = "";
		}
		return $return_val;
	}
	
	function radius_account_exist( $username, $not_username="" ){
		
		$return_val['exist'] = 0;
		$not_sql = "";
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		$connected = $this->radius_db->initialize();
		if( $connected ){

			if (!empty($not_username)) {
				$not_sql = " AND `UserName` != '".$this->radius_db->escape_str( $not_username )."'";
			}
		
			$check = " SELECT * FROM `radcheck` WHERE `UserName` = '".$this->radius_db->escape_str( $username )."' ".$not_sql."; ";
			$query = $this->radius_db->query($check);
			
			$return_val['sql'] = $check;
			if ( $query->num_rows() > 0 ) 
			{
				$return_val['exist'] = 1 ;
			}
		
		}
		
		return $return_val;
	}
	
	function radius_account_username_update( $old_username, $new_username ){
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		$connected = $this->radius_db->initialize();
		if( $connected ){
		
		if( $old_username != '' ){
		
			$check = " SELECT * FROM `radcheck` WHERE `username` = '".$this->radius_db->escape_str( $old_username )."'; ";
			$query = $this->radius_db->query($check);
			
			if ( $query->num_rows() > 0 ) 
			{
				$update = " UPDATE `radcheck` SET `username` = '".$this->radius_db->escape_str( $new_username )."'  
							WHERE `username` = '".$this->radius_db->escape_str( $old_username )."' ; ";
				$this->radius_db->query($update);
			}

			$check = " SELECT * FROM `radusergroup` WHERE `username` = '".$this->radius_db->escape_str( $old_username )."'; ";
			$query = $this->radius_db->query($check);

			if ( $query->num_rows() > 0 ) 
			{
				$update = " UPDATE `radusergroup` SET `username` = '".$this->radius_db->escape_str( $new_username )."'  
							WHERE `username` = '".$this->radius_db->escape_str( $old_username )."' ; ";
				$this->radius_db->query($update);
			}

		}
		}
	}
	
	function radius_account_status( $username, $status ){
		$this->radius_db = $this->load->database('radius',true,false);
		$connected = $this->radius_db->initialize();
		if( $connected ){
		$upsert = " INSERT INTO `radcheck` ( `UserName`, `Attribute`, `op`,`Value` ) 
					VALUES( '".$this->radius_db->escape_str( $username )."' , 'Auth-Type', ':=', '".$this->radius_db->escape_str( $status )."') ON DUPLICATE KEY UPDATE `Value` = '".$this->db->escape_str( $status )."'; " ;
		$this->radius_db->query($upsert);
		}
	}
	
	function radius_account_password( $username, $password ){
		$this->radius_db = $this->load->database('radius',true,false);
		$connected = $this->radius_db->initialize();
		if( $connected ){
		$upsert = " INSERT INTO `radcheck` ( `UserName`, `Attribute`, `op`,`Value` ) 
					VALUES( '".$this->radius_db->escape_str( $username )."' , 'User-Password', '==', '".$this->radius_db->escape_str( $password )."') ON DUPLICATE KEY UPDATE `Value` = '".$this->db->escape_str( $password )."'; " ;
		$this->radius_db->query($upsert);
		}
	}
	
	/*
	function radius_account_upsert( $username, $status='', $password=''){
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		if( $status != "" ){
			
			$upsert = " INSERT INTO `radcheck` ( `UserName`, `Attribute`, `op`,`Value` ) 
						VALUES( '".$this->radius_db->escape_str( $username )."' , 'Auth-Type', ':=', '".$this->radius_db->escape_str( $status )."') ON DUPLICATE KEY UPDATE `Value` = '".$this->db->escape_str( $status )."'; " ;
			$this->radius_db->query($upsert);
		}
		
		if( $password != "" ){
			
			$upsert = " INSERT INTO `radcheck` ( `UserName`, `Attribute`, `op`,`Value` ) 
						VALUES( '".$this->radius_db->escape_str( $username )."' , 'User-Password', '==', '".$this->radius_db->escape_str( $password )."') ON DUPLICATE KEY UPDATE `Value` = '".$this->db->escape_str( $password )."'; " ;
			$this->radius_db->query($upsert);
		}

	}
	*/
	
	function radius_usergroup_upsert( $username, $bandwidth ){
		
		$this->radius_db = $this->load->database('radius',true,false);
	
		$connected = $this->radius_db->initialize();
		if( $connected ){
	
		$upsert = " INSERT INTO `radusergroup` ( `username`, `groupname`, `priority` ) 
					VALUES( '".$this->radius_db->escape_str( $username )."' , '".$this->radius_db->escape_str( $bandwidth )."', '1') ON DUPLICATE KEY UPDATE `groupname` = '".$this->db->escape_str( $bandwidth )."'; " ;
		$this->radius_db->query($upsert);
		}

	}
	
	function cisco_account_upsert( $username, $password, $prev_username = '' ){
		$this->radius_db = $this->load->database('radius',true,false);
		
		$connected = $this->radius_db->initialize();
		if( $connected ){
		
			if( $prev_username != '' ){
				
				$check = "	SELECT `username` FROM `radcheck` 
							WHERE `username` = '".$this->radius_db->escape_str( $prev_username )."' ; " ;
				$query = $this->radius_db->query($check);
			
				if ($query->num_rows() > 0) {
					$upsert = "	UPDATE `radcheck` 
									SET `username` = '".$this->radius_db->escape_str( $username )."' , 
										`value` = '".$this->radius_db->escape_str( $password )."' 
								WHERE `username` = '".$this->radius_db->escape_str( $prev_username )."' ; ";
				}else{

					$upsert = "	INSERT INTO `radcheck` 
									SET	`username`	= '".$this->radius_db->escape_str( $username )."' , 
										`attribute` = 'Cleartext-Password' , 
										`op` 		= ':=' , 
										`value` 	= '".$this->radius_db->escape_str( $password )."' ; " ;

				}
								
			}else{
				
				$upsert = "	INSERT INTO `radcheck` 
								SET	`username`	= '".$this->radius_db->escape_str( $username )."' , 
									`attribute` = 'Cleartext-Password' , 
									`op` 		= ':=' , 
									`value` 	= '".$this->radius_db->escape_str( $password )."' ; " ;
				
			}
		
			$this->radius_db->query($upsert);
			
			$model				= 'action_log_model';
			$this->load->model($model);			
			$ctrl				= $this->router->fetch_class();
			$esc_query_str		= $this->db->escape_str($upsert);	
			$method 			= $this->router->method; 				
			$action_desc		= 'a Radius Account (Radcheck)  (Username: '.$this->radius_db->escape_str( $username ).', Password: '.$this->radius_db->escape_str( $password ).') has been upsert';	
			$action_category 	= 'insert';
			$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		}
	}
	
	function cisco_fixed_ip_upsert($username, $framed_ip_address, $framed_route, $framed_netmask = '', $framed_ipv6_address = '', $framed_ipv6_route = '', $prev_username = '') {
		$this->radius_db = $this->load->database('radius', true, false);
		if (!$this->radius_db->initialize()) return;

		$attributes = [
			'Framed-IP-Address'    => $framed_ip_address,
			'Framed-Route'         => $framed_route,
			'Framed-IP-Netmask'    => $framed_netmask,
			'Framed-IPv6-Address'  => $framed_ipv6_address,
			'Framed-IPv6-Route'    => $framed_ipv6_route
		];

		foreach ($attributes as $attr => $value) {
			$target_username = $prev_username ?: $username;
			
			if ($value === '') {
				$this->radius_db->query(
					"DELETE FROM `radreply` 
					WHERE `username` = ? AND `attribute` = ?",
					[$this->radius_db->escape_str($target_username), $attr]
				);
				continue;
			}

			$exists = $this->radius_db->query(
				"SELECT 1 FROM `radreply` 
				WHERE `username` = ? AND `attribute` = ?",
				[$this->radius_db->escape_str($target_username), $attr]
			)->num_rows() > 0;

			//temporary split out the attr, not sure if all attr will follow this pattern
			if ($attr == 'Framed-IP-Address') {

				$available_attr = [];
				$check_attr = "	SELECT * FROM `radreply` 
							WHERE `username` = ? AND `attribute` = ? ; " ;
				$check_query = $this->radius_db->query($check_attr, [$username, $attr]);
				if ($check_query->num_rows() > 0) {
					$available_attr = $check_query->result_array();
				}

				$split_addr = explode(";", $value);
				foreach ($split_addr as $skey => $saddr) {
					$insert = false;
					if (isset($available_attr[$skey])) {
						if (isset($available_attr[$skey]['id'])) {
							if (!empty($available_attr[$skey]['id'])) {
								$this->radius_db->query(
									"UPDATE `radreply` 
									SET `username` = ?, `value` = ? 
									WHERE `id` = ? AND `attribute` = ?",
									[
										$username,
										$saddr,
										$available_attr[$skey]['id'],
										$attr
									]
								);
								continue;
							}
						}
					}

					$insert = true;

					if ($insert) {
						$this->radius_db->query(
							"INSERT INTO `radreply` 
							SET `username` = ?, `attribute` = ?, `op` = ':=', `value` = ?",
							[
								$username,
								$attr,
								$saddr
							]
						);
					}
				}

			} else {

				if ($exists) {
					$this->radius_db->query(
						"UPDATE `radreply` 
						SET `username` = ?, `value` = ? 
						WHERE `username` = ? AND `attribute` = ?",
						[
							$this->radius_db->escape_str($username),
							$this->radius_db->escape_str($value),
							$this->radius_db->escape_str($target_username),
							$attr
						]
					);
				} else {
					$this->radius_db->query(
						"INSERT INTO `radreply` 
						SET `username` = ?, `attribute` = ?, `op` = ':=', `value` = ?",
						[
							$this->radius_db->escape_str($username),
							$attr,
							$this->radius_db->escape_str($value)
						]
					);
				}
				
			}
		}
	}
	
	function cisco_usergroup_upsert( $username, $bandwidth, $prev_username='' ){
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		$connected = $this->radius_db->initialize();
		if( $connected ){
			
			$check = "	SELECT `username` FROM `radusergroup` 
						WHERE `username` = '".$this->radius_db->escape_str( $prev_username )."' ; " ;
			$query = $this->radius_db->query($check);
			
			if ($query->num_rows() > 0) {
				$upsert = " UPDATE `radusergroup` 
							SET `username` = '".$this->radius_db->escape_str( $username )."' , 
								`groupname` = '".$this->radius_db->escape_str( $bandwidth )."' 
							WHERE `username` = '".$this->db->escape_str( $prev_username )."' ; ";
			}else{
				$upsert = " INSERT INTO `radusergroup` 
								SET	`username` = '".$this->radius_db->escape_str( $username )."'  ,
									`groupname` = '".$this->radius_db->escape_str( $bandwidth )."' ,
									`priority` = 0 ; ";
			}
			/*
			$upsert = " INSERT INTO `radusergroup` ( `username`, `groupname`, `priority` ) 
						VALUES( '".$this->radius_db->escape_str( $username )."' , '".$this->radius_db->escape_str( $bandwidth )."', '0') 
						ON DUPLICATE KEY UPDATE `groupname` = '".$this->db->escape_str( $bandwidth )."'; " ;
			*/
			$this->radius_db->query($upsert);

			$model				= 'action_log_model';
			$this->load->model($model);			
			$ctrl				= $this->router->fetch_class();
			$esc_query_str		= $this->db->escape_str($upsert);	
			$method 			= $this->router->method; 				
			$action_desc		= 'a Radius User Group  ('.$this->radius_db->escape_str( $username ).') has been upsert';	
			$action_category 	= 'insert';
			$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		}

	}
	
	function cisco_account_remove( $username ){
		$this->radius_db = $this->load->database('radius',true,false);
		
		$connected = $this->radius_db->initialize();
		if( $connected ){					
			$del = " DELETE FROM `radcheck` WHERE username = '".$this->db->escape_str( $username )."' ; ";
			$this->radius_db->query($del);
		}
	}
	
	function cisco_fixed_ip_remove( $username ){
		$this->radius_db = $this->load->database('radius',true,false);
		$connected = $this->radius_db->initialize();
		if( $connected ){
			$del = " DELETE FROM `radreply` WHERE username = '".$this->db->escape_str( $username )."' ; ";
			$this->radius_db->query($del);
		}
	}
	
	function cisco_usergroup_remove( $username ){
		$this->radius_db = $this->load->database('radius',true,false);
		$connected = $this->radius_db->initialize();
		if( $connected ){
			$del = " DELETE FROM `radusergroup` WHERE username = '".$this->db->escape_str( $username )."' ; ";
			$this->radius_db->query($del);		
		}
	}
	
	function insert_cisco(){
		
		$cust = " SELECT c.customer_no, c.name, c.login_username , c.login_password, 
						 c.framed_ip_address, c.framed_route , c.framed_netmask, c.framed_ipv6_address, c.framed_ipv6_route, 
						 CASE WHEN c.category = 'r' THEN p.bandwidth
								WHEN c.category = 'b' THEN CONCAT( 'BIZ', p.bandwidth ) 
								ELSE p.bandwidth END AS bandwidth 
					FROM customer c 
					LEFT JOIN package p ON c.package = p.package_no 
					WHERE c.category IN ( 'r' , 'b' ) AND c.status = 'r'; ";

		$query = $this->db->query($cust);
		if ( $query->num_rows() > 0 ){
			
			$results = $query->result_array();
			foreach( $results AS $key => $val ){
				
				//insert radcheck
				$this->cisco_account_upsert( $val['login_username'] , $val['login_password'] );
				//insert usergroup
				$this->cisco_usergroup_upsert( $val['login_username'] , $val['bandwidth'] ) ;
				//insert ip
				$this->cisco_fixed_ip_upsert( $val['login_username'] , $val['framed_ip_address'], $val['framed_route'], $val['framed_netmask'], $val['framed_ipv6_address'], $val['framed_ipv6_route'] ) ;
				
			}
			
		}

	}

	function do_dc($customer_no, $nas_id) {

		$this->load->model('setting_model');

		$customer = $this->get_customer($customer_no);

		$nas_address = $this->setting_model->get_nas_address($nas_id);

		$query = sprintf("User-Name=%s", escapeshellarg($customer['login_username']));
		$cmd = sprintf('echo "%s" | radclient -x %s:%s disconnect %s > /dev/null 2>&1 &', escapeshellcmd($query), $nas_address['nasname'], $nas_address['ports'], $nas_address['secret']);

		log_message('error', $cmd);

		$result = shell_exec($cmd);

		log_message('error', $result);

		return true;
	}

	function update_terminated_date($customer_no, $date='') {
		if (empty($date)) {
			$date = date('Y-m-d');
		}

		$this->db->query("UPDATE customer SET terminated_date = ? WHERE customer_no = ?", [$date, $customer_no]);
	}

	function create_status_record($customer_no, $status, $date='', $by=0) {

		if (empty($date)) {
			$date = date('Y-m-d H:i:s');
		}

		$sql = "INSERT INTO customer_status (customer_no, `status`, transact_date, created_on, created_by) VALUES (?,?,?,?,?)";
		$vars = array($customer_no, $status, $date, date('Y-m-d H:i:s'), $by);
		$this->db->query($sql, $vars);

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.','.print_r($vars, true));	
		$method 		= $this->router->method; 				
		$action_desc	= 'account('.$this->db->escape_str($customer_no).') status record ('.$this->db->escape_str($status).') has been added.';	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));	

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));

	}

	function update_status_record_time($status_id, $transact_date, $customer_no, $status) {
		$sql = "UPDATE `customer_status` SET transact_date = ? WHERE status_id = ? ";
		$vars = array($transact_date, $status_id);
		$this->db->query($sql, $vars);

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.','.print_r($vars, true));	
		$method 		= $this->router->method; 				
		$action_desc	= 'account('.$this->db->escape_str($customer_no).') status record ('.$this->db->escape_str($status).') time has been amended.';	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->input->post('customer_no'));

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
	}

	function get_status_transact_date($customer_no, $status, $order="DESC") {
		$sql = "SELECT transact_date FROM `customer_status` WHERE customer_no = ? AND status = ? ORDER BY status_id $order LIMIT 1";
		$query = $this->db->query($sql, array($customer_no, $status));
		$row = $query->row_array();

		if (!empty($row['transact_date'])) {
			return date('Y-m-d', strtotime($row['transact_date']));
		} else {
			return '';
		}
	}

	function varied_suspend_account($customer_no, $force=0) {
		//get customer details
		$cust_details = $this->get_customer( $customer_no );

		$login_username = $cust_details['login_username'];
		$password = $cust_details['login_password'];

		//send point of disconnect to all nas ip
		$this->load->model('setting_model');
		$nas_address	 					= $this->setting_model->get_nas_setting_listing('');
		foreach ($nas_address['row'] as $nas_row) {
			$this->do_dc($customer_no, $nas_row['id']);
		}

		//remove radius records
		$this->cisco_account_remove( $login_username );
		$this->cisco_fixed_ip_remove( $login_username );
		$this->cisco_usergroup_remove( $login_username );

		//for DIA customer, activate sh script to deactivate their port
		 if ( 
		 	( ($cust_details['category'] == 'd' || $cust_details['category'] == 'e') ) && 
		 	( ($cust_details['interface_id'] != '') ) &&
		 	(!empty($cust_details['router_id'])) 
		 	) {

		 	if ($force == 1) {

		 		$this->customer_model->dia_jumpstart($customer_no, 'down');

		 	}

		 }

		return true;
	}
	
	function varied_dia_suspend_account($customer_no) {
		
		$cust_details = $this->get_customer( $customer_no );

		if ( 
			( ($cust_details['category'] == 'd' || $cust_details['category'] == 'e') ) && 
			( ($cust_details['interface_id'] != '') ) &&
			(!empty($cust_details['router_id'])) 
		) {
			$this->load->model('setting_model');

			$login_username = $cust_details['login_username'];
			$password 		= $cust_details['login_password'];
			$nas_address	= $this->setting_model->get_nas_setting_listing('');

			foreach ($nas_address['row'] as $nas_row) {
				$this->do_dc($customer_no, $nas_row['id']);
			}

			$this->cisco_account_remove( $login_username );
			$this->cisco_fixed_ip_remove( $login_username );
			$this->cisco_usergroup_remove( $login_username );
			// $this->dia_jumpstart($customer_no, 'down');
		}

		return true;
	}

	/**
	 * Restore customer RADIUS and network configurations.
	 *
	 * Steps:
	 * 1. Recreate Cisco account credentials.
	 * 2. Restore package bandwidth group.
	 * 3. Reapply fixed IP configuration (IPv4/IPv6).
	 * 4. Reactivate DIA customer port if applicable.
	 *
	 * @param int $customer_no Customer unique ID
	 * @return bool True if restored successfully, false if customer not found
	 */
	function varied_restore_account($customer_no)
	{
		$this->load->model('package_model');
		$this->load->model('adjustment_model');
		$this->load->model('common_model');

		// Get customer details
		$cust = $this->get_customer($customer_no);
		if (empty($cust)) return false;

		$username = $cust['login_username'];
		$password = $cust['login_password'];

		// 1. Restore Cisco account credentials
		$this->customer_model->cisco_account_upsert($username, $password, '');

		// 2. Restore package group (bandwidth)
		$package = $this->package_model->get_package($cust['package'] ?? null);
		if (!empty($package['bandwidth'])) {
			$this->customer_model->cisco_usergroup_upsert($username, $package['bandwidth'], '');
		}

		// 3. Restore fixed IP configuration if any IP fields exist
		$has_ip = array_filter([
			$cust['framed_ip_address'] ?? null,
			$cust['framed_route'] ?? null,
			$cust['framed_netmask'] ?? null,
			$cust['framed_ipv6_address'] ?? null,
			$cust['framed_ipv6_route'] ?? null,
		]);

		if (!empty($has_ip)) {
			$this->customer_model->cisco_fixed_ip_upsert(
				$username,
				$cust['framed_ip_address'] ?? '',
				$cust['framed_route'] ?? '',
				$cust['framed_netmask'] ?? '',
				$cust['framed_ipv6_address'] ?? '',
				$cust['framed_ipv6_route'] ?? '',
				''
			);
		}

		// 4. Reactivate DIA customers' port if applicable
		$is_dia = in_array($cust['category'], ['d', 'e']);
		$has_interface = !empty($cust['interface_id']) || !empty($cust['edge_router_interface_id']);
		$has_router = !empty($cust['router_id']);

		// if ($is_dia && $has_interface && $has_router) {
		// 	$this->customer_model->dia_jumpstart($customer_no, 'up');
		// }

		return true;
	}

	/**
	 * Generate prorated bill for restored customer if billing cycle day has passed.
	 *
	 * @param int $customer_no
	 * @return void
	 */
	function generate_prorated_bill($customer_no)
	{
		$this->load->model('adjustment_model');
		$this->load->model('common_model');

		$cust = $this->get_customer($customer_no);
		if (empty($cust)) return false;

		$result = $this->common_model->get_table('sys_config', 'val', [
			'category' => 'bills',
			'key' => 'bill_generate_day'
		]);

		$bill_cycle_day = $result[0]['val'] ?? 3;
		if (date('d') >= $bill_cycle_day) {
			$this->adjustment_model->create_prorated_bill(
				$customer_no,
				$cust['monthly_charge'] ?? 0,
				date('Y-m-d'),
				'',
				''
			);
		}

		return true;
	}


	function set_suspend_date($customer_no, $date) {
		$this->db->query('UPDATE `customer` SET suspended_date = ? WHERE customer_no = ?', array($date, $customer_no));
	}

	function initiate_send_installation_form($customer_no, $installer)
	{
		if (empty($customer_no) || empty($installer)) {
			return false;
		}

		$scode = md5($customer_no . $this->e_key);

		$file_path = $this->config->item('upload_path')
			. '/temp/pdf/installation_form_'
			. $customer_no
			. '.pdf';

		puppeteer_print_preview(
			$this->config->item('base_url')
				. 'pdfapi/installation_form/'
				. $customer_no
				. '/'
				. $scode,
			$file_path,
			$this->config->item('proj_path'), 
			$this->config->item('chrome_loc')
		);

		// Get installer details
		$this->load->model('user_model');
		$installer_info = $this->user_model->get_user_by_id($installer);

		if (empty($installer_info)) {
			return false;
		}

		$email_list = [];

		if (!empty($installer_info['email'])) {
			$email_list[] = $installer_info['email'];
		}

		$whatsapp_list = [];

		if (
			!empty($installer_info['mobile_no']) &&
			($installer_info['allow_whatsapp'] ?? 0) == 1
		) {
			$whatsapp_list[] = $installer_info['mobile_no'];
		}

		$telegram_list = [];

		if (
			!empty($installer_info['telegram_id']) &&
			($installer_info['allow_telegram'] ?? 0) == 1
		) {
			$telegram_list[] = $installer_info['telegram_id'];
		}

		$this->load->library('notification_service');
		$contacts = $this->notification_service->buildContacts( $email_list, $whatsapp_list, $telegram_list );

		$this->notification_service->queue(
			$contacts,
			'Installation Form',
			'Attached herewith is the installation form.',
			'ITELCO DOCS',
			[
				'doc_type' => 'Installation Form',
			],
			$customer_no,
			[
				'acc_id'        => 0,
				'user_id'       => 0,
				'controller'    => 'customer',
				'doc_id'        => 0,
				'send_method'   => 'manual',
				'acc_name'      => 'Installer/Customer',
				'attachment'    => $file_path,
				'email_starter' => 'Installation Form',
				'doc_type'      => '[Installation Form]',
			]
		);

		return true;
	}

	function enable_free_upgrade($customer_no, $upgrade_package_id) {
		$cust_details = $this->get_customer( $customer_no );
		$this->load->model('package_model');
		$package_info = $this->package_model->get_package( $upgrade_package_id );
		if (!empty($package_info['bandwidth'])) {
			$this->cisco_usergroup_upsert( $cust_details['login_username'], $package_info['bandwidth'], $cust_details['login_username'] );
		}
	}

	function revert_free_upgrade($customer_no, $package_id) {
		$cust_details = $this->get_customer( $customer_no );
		$this->load->model('package_model');
		$package_info = $this->package_model->get_package( $package_id );
		$this->cisco_usergroup_upsert( $cust_details['login_username'], $package_info['bandwidth'], $cust_details['login_username'] );
	}

	function check_radius_group($username, $upgrade_package_id) {
		$chk = true;

		$this->load->model('package_model');
		$package_info = $this->package_model->get_package( $upgrade_package_id );

		$this->radius_db = $this->load->database('radius',true,false);
		
		$connected = $this->radius_db->initialize();
		if( $connected ){
			$check = "	SELECT `groupname` FROM `radusergroup` 
						WHERE `username` = '".$this->radius_db->escape_str( $username )."' ; " ;
			$query = $this->radius_db->query($check);
			
			if ($query->num_rows() > 0) {
				$row = $query->row_array();

				if ($row['groupname'] == $package_info['bandwidth']) {
					$chk = false;
				}

			}
		}

		return $chk;
	}

	function dia_jumpstart($customer_no, $activate) {

		$this->load->model('router_model');
		$cust_info = $this->get_customer($customer_no);
		$router_info = $this->router_model->get_router($cust_info['router_id']);

		if (!in_array($activate, array('up', 'down')) || empty($router_info['ssh_enabled'])) {
			return false;
		}
		
		$cmd = $this->config->item('sh_bin_folder')."manage-juniper-intt.sh ".$router_info['ip']." ".$router_info['ssh_port']." ".$router_info['login_id']." ".$router_info['login_key']." ".$cust_info['interface_id']." ".$activate." > /dev/null 2>&1 & ";

		log_message('error', $cmd);
		$result = shell_exec($cmd);
		log_message('error', $result);

		return true;
	}

	public function get_dia_customer_list() {
		$sql = "SELECT 
					c.customer_no, 
					c.login_username, 
					c.interface_id,
					r.ip AS router_ip
				FROM customer c
				INNER JOIN (
					SELECT 
						acs.* 
					FROM customer_status acs 
					JOIN (
						SELECT 
							customer_no, 
							MAX(status_id) AS max_status_id 
						FROM customer_status 
						GROUP BY customer_no
					) bcs ON acs.status_id = bcs.max_status_id 
				) cs ON cs.customer_no = c.customer_no 
				LEFT JOIN router r ON c.router_id = r.id
				WHERE c.category IN ('d', 'e') 
				AND cs.status = 'A'";

		$query = $this->db->query($sql);
		return $query->result_array() ?? [];
	}

	public function get_acc_id_by_customer_no($customer_no) {
		$profile_id = 0;
		$sql = "SELECT profile_id FROM customer WHERE customer_no = ?";
		$query = $this->db->query($sql, [$customer_no]);
		$profile_id = $query->row_array();

		return $profile_id;
	}

	function get_job_tracking_detail( $cs_id ){
		
		$return_val = array();
		
		$sql = " SELECT  cs_job_tracking.* , user.display_name FROM cs_job_tracking 
					LEFT JOIN user ON user.idx = cs_job_tracking.created_by
					WHERE cs_id = '".$cs_id."' ";
		$query = $this->db->query($sql);
		
		if( $query->num_rows() > 0 ){
			$return_val = $query->result_array();
		}
		
		return $return_val;
	}

	/**
	 * delete_job_tracking_detail
	 * Deletes a job tracking detail record based on the provided tracking ID and record ID.
	 * @param int $cs_id The tracking ID of the job tracking detail to delete.
	 * @param int $rec_id The record ID of the job tracking detail to delete.
	 * @return int Returns 1 if the record was successfully deleted, otherwise returns 0
	 */
	function delete_job_tracking_detail($cs_id, $rec_id){
		$sql = "DELETE FROM `cs_job_tracking` WHERE `cs_id` = '".$cs_id."' AND `cs_job_id` = '".$rec_id."' ";
		$query = $this->db->query($sql);
		if( $this->db->affected_rows() ){
			return 1 ;
		}else{
			return 0 ;
		}
	}

	/**
	 * save_job_tracking_detail
	 * Saves or updates a job tracking detail record.
	 *
	 * @param int $cs_id The tracking ID of the job tracking detail.
	 * @param int $rec_id The record ID of the job tracking detail. If empty, a new record will be created.
	 * @param string $remark The remark for the job tracking detail.
	 * @param int $created_by The user ID of the creator or updater.
	 * @param array $reply_to An array of user IDs to reply to.
	 * @return array Returns an array with the success status, record ID, and SQL query executed.
	 */
	function save_job_tracking_detail($cs_id, $rec_id, $remark, $created_by, $reply_to = []) {
		$return = [
			'success' => 0,
			'rec_id'  => '',
			'sql'     => ''
		];

		if ($cs_id === '') {
			return $return;
		}

		$remark = $this->db->escape_str($remark);
		$reply_to_serialized = serialize($reply_to);

		if (empty($rec_id)) {
			$sql = "
				INSERT INTO cs_job_tracking (
					cs_id, cs_date, cs_remark, reply_to, created_on, created_by
				) VALUES (
					'{$cs_id}', NOW(), '{$remark}', '{$reply_to_serialized}', NOW(), '{$created_by}'
				);
			";
			$this->db->query($sql);
			$insert_id = $this->db->insert_id();

			$return['success'] = $this->db->affected_rows() ? 1 : 0;
			$return['rec_id']  = $insert_id;
		} else {
			$sql = "
				UPDATE cs_job_tracking SET
					cs_remark = '{$remark}',
					reply_to = '{$reply_to_serialized}',
					updated_on = NOW(),
					updated_by = '{$created_by}'
				WHERE cs_id = '{$cs_id}' AND rec_id = '{$rec_id}';
			";
			$this->db->query($sql);

			$return['success'] = $this->db->affected_rows() ? 1 : 0;
			$return['rec_id']  = $rec_id;
		}

		$return['sql'] = $sql;
		return $return;
	}

	function get_technical_user($type='trouble_ticket') {
		$sql = "SELECT tu.*, u.display_name, u.allow_whatsapp, u.allow_telegram
				FROM technical_user tu
				LEFT JOIN user u ON tu.user_id = u.idx
				WHERE tu.doc_type = ?
				";
		$query = $this->db->query($sql, [$type]);

		return $query->result_array();
	}

	function get_cs_pic () {
		$query_str = "SELECT tts.*, u.allow_whatsapp, u.allow_telegram FROM tt_setting tts LEFT JOIN user u ON (tts.tt_user_id = u.idx) WHERE tts.tt_product_code IS NULL AND tts.tt_level = 1";
		$query = $this->db->query($query_str);

		return $query->result_array();
	}
	
	/**
	 * Returns a list of users to notify based on assigned users and/or reply targets.
	 *
	 * @param array $sel_user List of selected technical users.
	 * @param array $service_by Service user IDs.
	 * @param string $mode Mode of operation: 'option', 'doc_do_send', or 'doc_do_send_custom'.
	 * @param array $custom Optional custom data like reply_to list.
	 * @return array Either formatted user labels (mode 'option') or user contact info (other modes).
	 */
	public function get_service_ticket_reply_list($sel_user, $service_by, $mode = 'option', $custom = []) {
		$assigned_user_ids = array_map('strval', $service_by);
		$assigned_user = array_intersect_key(
			array_column($sel_user, 'display_name', 'idx'),
			array_flip($assigned_user_ids)
		);

		$user_list = $this->common_model->get_user_list() ?? [];
		$tech_list = $this->common_model->get_technical_list('customer_support');

		$main_pic = array_intersect_key(
			array_column($user_list, 'display_name', 'idx'),
			array_column($tech_list, 'display_name', 'user_id')
		);

		if ($mode === 'option') {
			$all = [];

			foreach ($assigned_user as $id => $name) {
				$all[$id] = ['name' => $name, 'src' => ['Service By']];
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
		$all_technical_list = $this->common_model->get_technical_list('customer_support');

		$extract_user_info = function ($user) {
			return [
				'user_id'  			=> $user['user_id'],
				'email'    			=> $user['email'] ?? null,
				'whatsapp' 			=> $user['mobile_no'] ?? null,
				'telegram' 			=> $user['telegram_id'] ?? null,
				'allow_whatsapp' 	=> $user['allow_whatsapp'] ?? 0,
				'allow_telegram' 	=> $user['allow_telegram'] ?? 0,
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

	public function update_other_charges($customer_no, $other_charge_arr) {
		$this->db->query("DELETE FROM `customer_additional_charge` WHERE customer_no = ? ",[$customer_no]);

		foreach ($other_charge_arr as $arr_key => $arr_value) {
			$this->db->query("INSERT INTO `customer_additional_charge` (ac_name, customer_no, bill_type, amount, remark, end_date) VALUES (?,?,?,?,?,?) ", [
				$arr_value['charge_name'],
				$customer_no,
				$arr_value['charge_type'],
				$arr_value['charge_amount'],
				$arr_value['charge_remark'],
				$arr_value['charge_end_date'],
			]);
		}
	}

	public function clear_other_charges($customer_no) {
		$this->db->query("DELETE FROM `customer_additional_charge` WHERE customer_no = ? ",[$customer_no]);
	}

	public function get_other_charges($customer_no) {
		$query_str = "	SELECT * FROM `customer_additional_charge` WHERE customer_no = ? ";
		$query = $this->db->query($query_str, array($customer_no));
		return $query->result_array(); 
	}

	public function get_package_change_customer() {
		$query_str = "SELECT c.* FROM `customer` c WHERE c.`new_package_effective_date` IS NOT NULL AND c.`new_package_id` IS NOT NULL ";
		$query = $this->db->query($query_str);
		return $query->result_array() ?? [];
	}

	public function update_package_change_customer($customer, $package_info, $last_package_used, $new_inserted_record = []) 
	{

		$address_arr = [
            $new_inserted_record['building'] ?? '',
            $new_inserted_record['inst_unit_no'] ?? '',
            $new_inserted_record['inst_addr1'] ?? '',
            $new_inserted_record['inst_addr2'] ?? '',
            $new_inserted_record['inst_addr3'] ?? '',
            $new_inserted_record['inst_city'] ?? '',
            $new_inserted_record['inst_postcode'] ?? '',
            $new_inserted_record['inst_state'] ?? ''
        ];

		$this->db->trans_start();

		$query_str = "UPDATE customer c 
						SET c.new_package_effective_date = NULL, 
							c.new_package_id = NULL, 
							c.new_contract_month = NULL, 
							c.old_bill_waive_period = 0, 
							c.building = ?, 
							c.inst_unit_no = ?, 
							c.inst_addr1 = ?, 
							c.inst_addr2 = ?, 
							c.inst_addr3 = ?, 
							c.inst_city = ?, 
							c.inst_postcode = ?, 
							c.inst_state = ? 
						WHERE c.customer_no = ?";
				
		$this->db->query($query_str, [...array_values($address_arr), $customer['customer_no']]);

		$query_str2 = "UPDATE customer c SET c.package = ?, c.package_name = ?, c.monthly_charge = ?, c.contract_month = ? WHERE c.customer_no = ?";
		$this->db->query($query_str2, array(
			$package_info['package_no'], 
			$package_info['name'], 
			$package_info['monthly_charge'], 
			$customer['new_contract_month'], 
			$customer['customer_no']
		));

		$query_str3 = "UPDATE customer_package_history cph SET cph.end_date = ? WHERE cph.idx = ? AND cph.customer_no = ?";
		$this->db->query($query_str3, array(
			date('Y-m-d', strtotime('-1 day')), 
			$last_package_used['idx'], 
			$customer['customer_no']
		));

		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			log_message('error', 'Package change transaction failed for Customer No: ' . $customer['customer_no']);
			return false;
		}

		return true;
	}

	public function chk_customer_bill_waiver($customer_no) {
		$chk = false;

		$check = "	SELECT * FROM `customer_bill_waiver` 
					WHERE `customer_no` = '".$this->db->escape_str( $customer_no )."' LIMIT 1 ; " ;
		$query = $this->db->query($check);
		
		if ($query->num_rows() > 0) {
			$row = $query->row_array();

			if (isset($row['waiver_start_date'])) {
				$chk = true;
			}

		}

		return $chk;
	}

	public function chk_customer_free_upgrade($customer_no) {
		$chk = false;

		$check = " SELECT * FROM `customer_free_upgrade` 
				WHERE `customer_no` = '" . $this->db->escape_str($customer_no) . "' 
				LIMIT 1; ";

		$query = $this->db->query($check);
		
		if ($query->num_rows() > 0) {
			$row = $query->row_array();

			if (isset($row['free_upgrade_start'])) {
				$chk = true;
			}
		}

		return $chk;
	}

	public function chk_customer_free_upgrade_period($customer_no)
	{
		$check = "
			SELECT *
			FROM `customer_free_upgrade`
			WHERE `customer_no` = ?
			ORDER BY `idx` DESC
			LIMIT 1
		";

		$query = $this->db->query($check, [$customer_no]);

		if ($query->num_rows() <= 0) {
			return false;
		}

		$row = $query->row_array();

		if (empty($row['free_upgrade_start']) || empty($row['free_upgrade_end'])) {
			return false;
		}

		$today = date('Y-m-d');
		
		return ($today >= $row['free_upgrade_start'] && $today <= $row['free_upgrade_end']);
	}

	public function get_customer_bill_waiver($customer_no) {
		$return = array();

		$check = "
				SELECT *
				FROM `customer_bill_waiver`
				WHERE `customer_no` = '".$this->db->escape_str($customer_no)."'
				ORDER BY `id` DESC
				LIMIT 1;
			";
		$query = $this->db->query($check);
		
		if ($query->num_rows() > 0) {
			$row = $query->row_array();

			if (isset($row['waiver_start_date'])) {
				$return = $row;
			}

		}

		return $return;
	}

	public function xxxadd_bill_waiver($customer_no, $package, $start_date, $waiver_months) {

		$waiver_start = $start_date;

		$date = new DateTime($waiver_start);
		$date->modify('+'.$waiver_months.' month'); // or you can use '-90 day' for deduct
		$date->modify('-1 day');
		$end_date = $date->format('Y-m-d');

		$date = new DateTime($waiver_start);
		$date->modify('+'.$waiver_months.' month'); // or you can use '-90 day' for deduct
		$start_charge = $date->format('Y-m-d');

		$waiver_end = $end_date;

		$query_str2 = "INSERT INTO customer_bill_waiver (customer_no, package_no, waiver_start_date, waiver_end_date) VALUES ( " .
		 				"'" . $customer_no . "', " .
		 				"'" . $this->db->escape_str($package) . "', " .
		 				"'" . $this->db->escape_str($waiver_start) . "', " .
		 				"'" . $this->db->escape_str($waiver_end) . "' " .
		 				")";

		$this->db->query($query_str2);

		return array('start' => $waiver_start, 'end' => $waiver_end, 'start_charge' => $start_charge);

	}

	public function add_bill_waiver($customer_no, $package, $start_date, $waiver_months) {

		$original_start = $start_date;
		$customer_info = $this->get_customer($customer_no);
		$delay_trial_start = $customer_info['delay_trial_start'] ?? 0;

		$date = new DateTime($start_date);

		if ($date->format('d') != '01') {
			$date->modify('first day of next month');
		}

		if($delay_trial_start == 1){
			$date->modify('first day of next month');
		}

		$waiver_start = $date->format('Y-m-d');

		$end_date = new DateTime($waiver_start);
    	$end_date->modify('+' . (int)$waiver_months . ' month'); // or you can use '-90 day' for deduct
		$end_date->modify('-1 day');
    	$waiver_end = $end_date->format('Y-m-d');

		$start_charge = new DateTime($waiver_start);
    	$start_charge->modify('+' . (int)$waiver_months . ' month'); // or you can use '-90 day' for deduct
		$start_charge = $start_charge->format('Y-m-d');

		$query_str = "INSERT INTO customer_bill_waiver (customer_no, package_no, waiver_start_date, waiver_end_date) VALUES ( " .
		 				"'" . $customer_no . "', " .
		 				"'" . $this->db->escape_str($package) . "', " .
		 				"'" . $this->db->escape_str($waiver_start) . "', " .
		 				"'" . $this->db->escape_str($waiver_end) . "' " .
		 				")";

		$this->db->query($query_str);

		return array('start' => $waiver_start, 'end' => $waiver_end, 'start_charge' => $start_charge);

	}

	public function delete_latest_bill_waiver($customer_no) {
		$this->db->where('customer_no', $customer_no)
				->order_by('id', 'DESC')
				->limit(1)
				->delete('customer_bill_waiver');

		return $this->db->affected_rows();
	}

	public function add_free_upgrade($customer_no, $package, $start_date, $upgrade_months) {

		$upgrade_start = $start_date;

		$date = new DateTime($upgrade_start);
		$date->modify('+' . $upgrade_months . ' month');
		$date->modify('-1 day');
		$upgrade_end = $date->format('Y-m-d');

		$this->db->insert('customer_free_upgrade', [
			'customer_no' => $customer_no,
			'package_no' => $package,
			'free_upgrade_start' => $upgrade_start,
			'free_upgrade_end' => $upgrade_end
		]);

		return array('start' => $upgrade_start, 'end' => $upgrade_end);
	}

	public function delete_latest_free_upgrade($customer_no) {
		$this->db->where('customer_no', $customer_no)
				->order_by('id', 'DESC')
				->limit(1)
				->delete('customer_free_upgrade');

		return $this->db->affected_rows();
	}

	public function create_new_contract($customer_no, $new_package_id, $new_package_effective_date, $contract_month, $address_arr = array())
	{
		$this->db->select('name, monthly_charge');
		$this->db->from('package');
		$this->db->where('status', 'a');
		$this->db->where('package_no', $new_package_id);
		$package = $this->db->get()->row_array();

		if (!$package) return ['status' => 'error', 'err_msg' => 'Invalid Action'];

		$this->db->select('ph.package_no AS last_package_no');
		$this->db->from('customer c');
		$this->db->join('(SELECT ph1.*
                FROM customer_package_history ph1
                WHERE ph1.idx = (
                    SELECT MAX(ph2.idx)
                    FROM customer_package_history ph2
                    WHERE ph2.customer_no = ph1.customer_no
                )
                ) ph',
                'ph.customer_no = c.customer_no',
                'left');
		$this->db->where('c.customer_no', $customer_no);
		$customer = $this->db->get()->row_array();

		//if its a relocate action, package will be the same
		/*if (!$customer || $new_package_id == $customer['last_package_no']) {
            return ['status' => 'error', 'err_msg' => 'Invalid Action'];
        }*/

		$start_date = $end_date = NULL;

		if($contract_month != '0'){
			$start_date = $new_package_effective_date;
        	$end_date = date('Y-m-d', strtotime("-1 day", strtotime("+" . $contract_month . " months", strtotime($start_date))));
		}

        $data = [
            'customer_no' => $customer_no,
            'package_name' => $package['name'],
            'monthly_charge' => $package['monthly_charge'],
            'package_no' => $new_package_id,
            'start_date' => $new_package_effective_date,
            'contract_start_date' => $start_date,
            'contract_end_date' => $end_date,
            'building' => $address_arr['building'] ?? 0,
            'inst_unit_no' => $address_arr['inst_unit_no'] ?? '',
            'inst_addr1' => $address_arr['inst_addr1'] ?? '',
            'inst_addr2' => $address_arr['inst_addr2'] ?? '',
            'inst_addr3' => $address_arr['inst_addr3'] ?? '',
            'inst_city' => $address_arr['inst_city'] ?? '',
            'inst_postcode' => $address_arr['inst_postcode'] ?? '',
            'inst_state' => $address_arr['inst_state'] ?? '',
        ];

        $this->db->insert('customer_package_history', $data);
        return $this->db->affected_rows() > 0
            ? ['status' => 'success', 'err_msg' => 'Success']
            : ['status' => 'error', 'err_msg' => 'Invalid Action'];
	}

	public function continue_contract($customer_no, $new_package_id, $new_package_effective_date, $contract_month, $address_arr = array())
	{
		$this->db->select('name, monthly_charge');
		$this->db->from('package');
		$this->db->where('status', 'a');
		$this->db->where('package_no', $new_package_id);
		$package = $this->db->get()->row_array();

		if (!$package) return ['status' => 'error', 'err_msg' => 'Invalid Action'];

		$this->db->select(
			'ph.contract_start_date AS last_contract_start_date,
			ph.contract_end_date AS last_contract_end_date,
			ph.package_no AS last_package_no');
		$this->db->from('customer c');
		$this->db->join(
			'(SELECT ph1.*
			FROM customer_package_history ph1
			WHERE ph1.idx = (
				SELECT MAX(ph2.idx)
				FROM customer_package_history ph2
				WHERE ph2.customer_no = ph1.customer_no AND ph2.package_no = ph1.package_no
			)
			) ph',
			'ph.customer_no = c.customer_no AND ph.package_no = c.package',
			'left'
		);
		$this->db->where('c.customer_no', $customer_no);
		$customer = $this->db->get()->row_array();

		//if its a relocate action, package will be the same
		/*if (!$customer || $new_package_id == $customer['last_package_no']) {
            return ['status' => 'error', 'err_msg' => 'Invalid Action'];
        }*/

		$start_date = $new_package_effective_date;
        $contract_start_date = $customer['last_contract_start_date'] ?? $start_date;

        $end_date = $customer['last_contract_end_date'] 
                    ?? date('Y-m-d', strtotime("-1 day", strtotime('+' . $contract_month . ' months', strtotime($start_date))));

        $data = [
            'customer_no' => $customer_no,
            'package_name' => $package['name'],
            'monthly_charge' => $package['monthly_charge'],
            'package_no' => $new_package_id,
            'start_date' => $start_date,
            'contract_start_date' => $contract_start_date,
            'contract_end_date' => $end_date,
            'building' => $address_arr['building'] ?? 0,
            'inst_unit_no' => $address_arr['inst_unit_no'] ?? '',
            'inst_addr1' => $address_arr['inst_addr1'] ?? '',
            'inst_addr2' => $address_arr['inst_addr2'] ?? '',
            'inst_addr3' => $address_arr['inst_addr3'] ?? '',
            'inst_city' => $address_arr['inst_city'] ?? '',
            'inst_postcode' => $address_arr['inst_postcode'] ?? '',
            'inst_state' => $address_arr['inst_state'] ?? '',
        ];

        $this->db->insert('customer_package_history', $data);
        return $this->db->affected_rows() > 0
            ? ['status' => 'success', 'err_msg' => 'Success']
            : ['status' => 'error', 'err_msg' => 'Invalid Action'];
	}

	public function renew_contract($customer_no, $package_id, $new_package_effective_date, $contract_month, $address_arr = []) {
		$this->db->select('name, monthly_charge');
		$this->db->from('package');
		$this->db->where('status', 'a');
		$this->db->where('package_no', $package_id);
		$package = $this->db->get()->row_array();

		if (!$package) return ['status' => 'error', 'err_msg' => 'Invalid Action'];

		$this->db->select('ph.contract_end_date');
		$this->db->from('customer c');
		$this->db->join('(SELECT ph1.*
                FROM customer_package_history ph1
                WHERE ph1.idx = (
                    SELECT MAX(ph2.idx)
                    FROM customer_package_history ph2
                    WHERE ph2.customer_no = ph1.customer_no
                )
                ) ph',
                'ph.customer_no = c.customer_no',
                'left');
		$this->db->where('c.customer_no', $customer_no);
		$package_history = $this->db->get()->row_array();

		if($package_history && strtotime($new_package_effective_date) <= strtotime($package_history['contract_end_date'])) {
			return ['status' => 'error', 'err_msg' => 'New contract start date must be after last contract end date'];
		}

		$start_date = $new_package_effective_date;
        $end_date = date('Y-m-d', strtotime("-1 day", strtotime("+" . $contract_month . " months", strtotime($start_date))));

        $data = [
            'customer_no' => $customer_no,
            'package_name' => $package['name'],
            'monthly_charge' => $package['monthly_charge'],
            'package_no' => $package_id,
            'start_date' => $start_date,
            'contract_start_date' => $start_date,
            'contract_end_date' => $end_date,
			'building' => $address_arr['building'] ?? 0,
            'inst_unit_no' => $address_arr['inst_unit_no'] ?? '',
            'inst_addr1' => $address_arr['inst_addr1'] ?? '',
            'inst_addr2' => $address_arr['inst_addr2'] ?? '',
            'inst_addr3' => $address_arr['inst_addr3'] ?? '',
            'inst_city' => $address_arr['inst_city'] ?? '',
            'inst_postcode' => $address_arr['inst_postcode'] ?? '',
            'inst_state' => $address_arr['inst_state'] ?? '',
        ];

        $this->db->insert('customer_package_history', $data);
        return $this->db->affected_rows() > 0
            ? ['status' => 'success', 'err_msg' => 'Success']
            : ['status' => 'error', 'err_msg' => 'Invalid Action'];
	}

	public function get_last_package($customer_no, $package_no)
	{
		/*$this->db->select('*');
		$this->db->from('customer_package_history');
		$this->db->where('customer_no', $customer_no);
		$this->db->where('package_no', $package_no);
		$this->db->order_by('idx', 'desc');
		$this->db->limit(1);
		$last_package_used = $this->db->get()->row_array();*/

		$last_package_used = [];
		$sql = "SELECT p.*, 
		IFNULL(p.building,c.building) AS building, 
		IFNULL(p.inst_unit_no,c.inst_unit_no) AS inst_unit_no, 
		IFNULL(p.inst_addr1,c.inst_addr1) AS inst_addr1, 
		IFNULL(p.inst_addr2,c.inst_addr2) AS inst_addr2, 
		IFNULL(p.inst_addr3,c.inst_addr3) AS inst_addr3, 
		IFNULL(p.inst_city,c.inst_city) AS inst_city, 
		IFNULL(p.inst_postcode,c.inst_postcode) AS inst_postcode, 
		IFNULL(p.inst_state,c.inst_state) AS inst_state 
		FROM customer_package_history p LEFT JOIN customer c ON (p.customer_no = c.customer_no) 
		WHERE p.customer_no = ? AND p.package_no = ? order by p.idx desc limit 1 
		";

		$query = $this->db->query($sql, [$customer_no, $package_no]);
		
		if ($query->num_rows() > 0) {
			$last_package_used = $query->row_array();
		}

		return $last_package_used ?? [];
	}

	// CASE 1: GOT waive period, first change package
	// CASE 2: GOT waive period, second and above times change package before effect
	// CASE 3: ZERO waive period, first change package
	// CASE 4: ZERO waive period, second above times change package before effect
	public function chk_customer_valid_waiver_period($customer_no, $bill_waive_period)
	{
		$activatedDate = $this->get_status_transact_date($customer_no, 'A', 'ASC');		
		$activated = new \DateTime($activatedDate);
		$today = new \DateTime();
		$waiver_end = (clone $activated)->modify("+{$bill_waive_period} months");

		$result['waiver_end_date'] = $waiver_end->format('Y-m-d');
		$result['in_waiver_period'] = ($today >= $activated && $today <= $waiver_end);

		return $result;
	}

	public function save_form_signatures (array $data, $customer_no, $form_type) {
		$this->db->where('customer_no', $customer_no);
		$this->db->where('form_type', $data['form_type']);

		$exists = $this->db->get('form_signatures')->row();

		if ($exists) {
			$this->db->where('id', $exists->id);
			$this->db->update('form_signatures', [
				'signer_name' => $data['signer_name'],
				'signer_ic' => $data['signer_ic'],
				'signature_file_id' => $data['signature_file_id']
			]);
		} else {
			$this->db->insert('form_signatures', $data);
		}
	}

	public function save_equipment_info($equipment_list, $customer_no) {
		$this->db->trans_start();

		$this->db->where('customer_no', $customer_no);
		$this->db->delete('customer_equipment');

		if (!empty($equipment_list)) {
			foreach ($equipment_list as $row) {
				if (!empty($row['type_id'])) {
					$this->db->insert('customer_equipment', [
						'customer_no' => $customer_no,
						'equipment_type_id' => $row['type_id'],
						'serial_no' => $row['serial_no']
					]);
				}
			}
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function get_equipment_record($customer_no) {
		$check = "SELECT a.customer_no, b.`name` AS equipment_name, a.serial_no 
				FROM `customer_equipment` a 
				LEFT JOIN sys_equipment_type b ON (a.equipment_type_id = b.equipment_type_id) 
				WHERE a.`customer_no` = '".$this->db->escape_str($customer_no)."' 
				ORDER BY a.id ASC"; 
		return $this->db->query($check)->result_array();
	}

	public function update_terms_status($customer_no, $is_checked) {
        $data = ['is_terms_accepted' => $is_checked];

		$this->db->where('customer_no', $customer_no);
        $this->db->update('customer', $data);

        return ($this->db->affected_rows() >= 0);
    }

    public function check_termination_sign($customer_no) {
		$return = array();

		$sql = "
		SELECT is_terms_accepted FROM `customer_termination_tnc` WHERE customer_no = ? 
		";
		$query = $this->db->query($sql, array($customer_no));

		if ($query->num_rows() > 0){
			$return = $query->row_array();
		}

		if (isset($return['is_terms_accepted'])) {
			if (!empty($return['is_terms_accepted'])) {
				return true;
			}
		}

		return false;
    }

    public function update_termination_terms_status($customer_no, $is_checked) {
    	$sql = "REPLACE INTO `customer_termination_tnc` (customer_no, is_terms_accepted) VALUES (?,?)";

    	$this->db->query($sql, array($customer_no, $is_checked));

    	return true;
    }

    public function termination_wipe_tnc($customer_no) {
    	$this->db->query("DELETE FROM `customer_termination_tnc` WHERE customer_no = ? ", array($customer_no));
    }

	public function update_radius_on_package_change($customer_no) {
		$this->load->model('package_model');

		$cust = $this->common_model->get_table('customer', '*', "`customer_no` = '$customer_no' AND `new_package_effective_date` IS NOT NULL AND `new_package_id` IS NOT NULL ");
		$cust = $cust[0] ?? [];

		if (empty($cust)) {
			return false;
		}

		$history_result = $this->get_package_history($cust['customer_no']);
		$package_history_record = $history_result['row'] ?? [];
		
		if (!empty($package_history_record) && count($package_history_record) >= 2) {
			$new_inserted_record = $package_history_record[count($package_history_record) - 1] ?? [];
			$last_package_used = $package_history_record[count($package_history_record) - 2] ?? [];

			if (isset($new_inserted_record['package_no']) && $new_inserted_record['package_no'] == $cust['new_package_id'] && !empty($cust['new_package_effective_date'])) {
				if (date('Y-m-d', strtotime($cust['new_package_effective_date'])) <= date('Y-m-d')) {
					$package_info = $this->package_model->get_package($cust['new_package_id']);

					$this->customer_model->cisco_usergroup_upsert($cust['login_username'], $package_info['bandwidth'], $cust['login_username']);

					// if (($cust['category'] == 'd' || $cust['category'] == 'e') && !empty($cust['interface_id']) && !empty($cust['router_id'])) {
					// 	$this->customer_model->dia_jumpstart($cust['customer_no'], 'up');
					// }

					$this->customer_model->update_package_change_customer($cust, $package_info, $last_package_used, $new_inserted_record);
					
					return true;
				}
			}
		}

		return false;
	}

	public function process_bill_waiver ($cust) {
		$this->load->model('adjustment_model');
		$this->load->model('package_model');
		
		if (
			empty($cust)
			|| !is_array($cust)
			|| empty($cust['new_package_effective_date'])
			|| empty($cust['new_package_id'])
			|| strtotime($cust['transact_date']) <= 0
		) {
			return false;
		}
		
		$package = $this->package_model->get_package($cust['new_package_id']);

		if (empty($package)) {
			return false;
		}

		$this->db->trans_begin();
	
		if (!empty($cust['bill_waive_period'])) {
			// INSERT CUSTOMER BILL WAIVER
			$this->add_bill_waiver($cust['customer_no'], $cust['new_package_id'], $cust['transact_date'], $cust['bill_waive_period']);
		}

		// INSERT UPGRADE PACKAGE
		$this->process_upgrade_package($cust);
		
		if (date('Y-m-d', strtotime($cust['new_package_effective_date'])) == date('Y-m-d', strtotime($cust['transact_date']))){
			// DELETE PERVIOUS PRORATE BILL
			$this->adjustment_model->delete_prorate_bill($cust['customer_no']);

			if (!empty($cust['bill_waive_period'])) {
				// CREATE PRORATE BILL
				if (date("d", strtotime($cust['transact_date'])) != '01') {
					$delay_trial_start = $cust['delay_trial_start'] ?? 0;
					$transact_ts = strtotime($cust['transact_date']);

					$ts = $delay_trial_start == 1
								? $transact_ts
								: strtotime("+{$cust['bill_waive_period']} months", $transact_ts);

					$after_waiver_date = date('Y-m-d', date('d', (int)$ts) != date('d', $transact_ts)
															? strtotime('last day of previous month', (int)$ts)
															: (int)$ts);

					$this->create_prorated_bill_for_waiver($cust['customer_no'], $package['monthly_charge'], $cust['transact_date'], '', '', $after_waiver_date);
				}
			} else {
				$transact_md = date("ym", strtotime($cust['transact_date']));
				if ((int)$transact_md == (int)date("ym")) {
					$this->create_prorated_bill($cust['customer_no'], $package['monthly_charge'], $cust['transact_date'], '', '');
				}
			}
			
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return false;
		} 

		$this->db->trans_commit();
		return true;
	}

	public function process_upgrade_package($cust)
	{
		if (
			empty($cust)
			|| !is_array($cust)
			|| empty($cust['free_package_upgrade'])
			|| empty($cust['upgrade_package_id'])
			|| strtotime($cust['transact_date']) <= 0
		) {
			return false;
		}

		$this->add_free_upgrade(
			$cust['customer_no'],
			$cust['upgrade_package_id'],
			$cust['transact_date'],
			$cust['free_package_upgrade']
		);

		return true;
	}

	function create_prorated_bill($customer_no, $monthly_charge, $activated_date,$suspended_date,$terminated_date)
	{
		if ( !empty($activated_date) ) {
			
			$end_date = "";
			if( $suspended_date != '0000-00-00' && $suspended_date != '' )
				$end_date = $suspended_date; 
			elseif( $terminated_date != '0000-00-00' && $terminated_date != '' )
				$end_date = $terminated_date; 
			
			//Pro-rate
			$month_ini = new DateTime(date("Y-m-1", strtotime($activated_date)));

			if( $end_date != "" )
				$month_end = new DateTime( date( 'Y-m-d' , strtotime($end_date) ) );							
			else
				$month_end = new DateTime(date("Y-m-t", strtotime($activated_date)));	

			//check if activated_date is less than this month's end
			if (strtotime($activated_date) < strtotime($month_end->format('Y-m-d'))) {		

				$total_days = $month_ini->format('t');
				$total_used_days = $month_end->diff( new datetime($activated_date) )->format('%a') + 1; // + 1 because need to including the day start using
				$amount = ($monthly_charge / $total_days * $total_used_days );
				
				//$obj_adj = new Adjustment();
				$adj_remark = 'Prorated Bill From ' . date('Y-m-d',strtotime($activated_date)) . ' UNTIL ' . $month_end->format('Y-m-d');
				
				$this->load->model('adjustment_model');
				$this->adjustment_model->insert_adj('i', $customer_no, '', '', $activated_date, '41', 'dr', $amount, $adj_remark, 1);

			}
			
		}
	}
	
	function create_prorated_bill_for_waiver($customer_no, $monthly_charge, $activated_date, $suspended_date,$terminated_date, $charge_date)
	{
		if ( !empty($activated_date) ) {
			
			$end_date = "";
			if( $suspended_date != '0000-00-00' && $suspended_date != '' )
				$end_date = $suspended_date; 
			elseif( $terminated_date != '0000-00-00' && $terminated_date != '' )
				$end_date = $terminated_date; 
			
			//Pro-rate
			$month_ini = new DateTime(date("Y-m-1", strtotime($activated_date)));

			if( $end_date != "" )
				$month_end = new DateTime( date( 'Y-m-d' , strtotime($end_date) ) );							
			else
				$month_end = new DateTime(date("Y-m-t", strtotime($activated_date)));	

			//check if activated_date is less than this month's end
			if (strtotime($activated_date) <= strtotime($month_end->format('Y-m-d'))) {		

				$total_days = $month_ini->format('t');
				$total_used_days = $month_end->diff( new datetime($activated_date) )->format('%a') + 1; // + 1 because need to including the day start using
				$amount = ($monthly_charge / $total_days * $total_used_days );
				
				$adj_remark = 'Prorated Bill From ' . date('Y-m-d',strtotime($activated_date)) . ' UNTIL ' . $month_end->format('Y-m-d');
				
				$this->load->model('adjustment_model');
				$this->adjustment_model->insert_adj('i', $customer_no, '', '', $charge_date, '41', 'dr', $amount, $adj_remark, 1);

			}
		}
	}

	public function reverse_relocation_process($customer_no) {
    
		$this->load->model('adjustment_model');

		$this->db->trans_start();

		$this->delete_latest_bill_waiver($customer_no);
		$this->delete_latest_free_upgrade($customer_no);
		$this->adjustment_model->delete_prorate_bill($customer_no);
		
		$this->db->where('customer_no', $customer_no)
				->update('customer', [
					'new_package_effective_date' => NULL,
					'new_package_id'             => NULL,
					'new_contract_month'         => NULL,
					'old_bill_waive_period'      => NULL
				]);

		$this->db->where('customer_no', $customer_no)
				->order_by('idx', 'DESC')
				->limit(1)
				->delete('customer_package_history');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function check_latest_termination_flow($customer_no) {

		$return = array();

		$sql = "SELECT c.*, csa.status AS termination_status, csa.created_on, csa.remarks AS rejection_remarks   
		FROM customer c 
		LEFT JOIN (
			SELECT acs.* FROM customer_termination_status acs 
			JOIN (
				SELECT customer_no, MAX(idx) AS max_status_id 
				FROM customer_termination_status 
				GROUP BY customer_no 
			) bcs ON (acs.idx = bcs.max_status_id) 
		) csa ON (csa.customer_no = c.customer_no) 
		WHERE c.customer_no = ? 
		ORDER BY csa.created_on DESC LIMIT 1  
		";
		$query = $this->db->query($sql, array($customer_no));

		if ($query->num_rows() > 0){
			$return = $query->row_array();
		}

		return $return;
	}

	function create_termination_status_record($customer_no, $status, $date='', $by=0, $remarks='') {

		if (empty($date)) {
			$date = date('Y-m-d H:i:s');
		}

		$sql = "INSERT INTO customer_termination_status (customer_no, `status`, transact_date, remarks, created_on, created_by) VALUES (?,?,?,?,?,?)";
		$vars = array($customer_no, $status, $date, $remarks, date('Y-m-d H:i:s'), $by);
		$this->db->query($sql, $vars);

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.','.print_r($vars, true));	
		$method 		= $this->router->method; 				
		$action_desc	= 'account('.$this->db->escape_str($customer_no).') termination status record ('.$this->db->escape_str($status).') has been added.';	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));	

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));

	}

	function init_termination_record($customer_no) {
		$sql = "INSERT INTO customer_termination_data (customer_no) VALUES (?)";
		$vars = array($customer_no);
		$this->db->query($sql, $vars);
	}

	function get_latest_termination_info($customer_no) {

		$return = array();

		$sql = "SELECT c.*, 
		csa.pic_name AS data_pic_name, 
		csa.pic_email AS data_pic_email,
		csa.unbilled_months AS data_unbilled_months,
		csa.unbilled_amt AS data_unbilled_amt, 
		csa.penalty AS data_penalty,
		csa.pic_mobile AS data_pic_mobile,
		csa.effective_date AS data_effective_date,
		cph.package_name AS data_package_name, 
		cph.monthly_charge AS data_monthly_charge,
		cph.contract_start_date, 
		cph.contract_end_date, 
		csa.idx AS latest_idx, 
		p.acc_id AS profile_id, 
		p.acc_name AS profile_name, 
		p.pic_email_1 AS profile_email_1,
		p.pic_email_2 AS profile_email_2,
		p.acc_mobileno AS profile_mobile_num  
		FROM customer c 
		LEFT JOIN (
			SELECT acs.* FROM customer_termination_data acs 
			JOIN (
				SELECT customer_no, MAX(idx) AS max_data_id  
				FROM customer_termination_data 
				GROUP BY customer_no 
			) bcs ON (acs.idx = bcs.max_data_id) 
		) csa ON (csa.customer_no = c.customer_no) 
		LEFT JOIN (
			SELECT acph.* FROM customer_package_history acph
			JOIN (
				SELECT customer_no, MAX(idx) AS max_idx 
				FROM customer_package_history 
				GROUP BY customer_no
			) bcph ON (acph.idx = bcph.max_idx)
		) cph ON (cph.customer_no = c.customer_no) 
		LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
		WHERE c.customer_no = ? 
		ORDER BY csa.idx DESC LIMIT 1  
		";
		$query = $this->db->query($sql, array($customer_no));

		if ($query->num_rows() > 0){
			$return = $query->row_array();
		}

		return $return;

	}

	function save_termination_info($post_data) {
		$current_row = $this->get_latest_termination_info($post_data['customer_no']);
		$customer_no = $post_data['customer_no'];
		$this_id = $current_row['latest_idx'];

		$sql = "UPDATE customer_termination_data 
		SET pic_name = ?, 
		pic_email = ?,
		pic_mobile = ?,
		unbilled_months = ?, 
		unbilled_amt = ?,
		penalty = ?,
		effective_date = ?, 
		updated_by = ? WHERE idx = ? 
		";

		$vars = array(
			($post_data['pic_name'] ?? ''), 
			($post_data['pic_email'] ?? ''), 
			($post_data['pic_mobile'] ?? ''), 
			($post_data['unbilled_months'] ?? '0'), 
			($post_data['unbilled_amt'] ?? '0.00'), 
			($post_data['penalty'] ?? '0'), 
			($post_data['effective_date'] ?? ''), 
			($post_data['updated_by'] ?? ''), 
			$this_id
		);

		$this->db->query($sql, $vars);

		//Log the action
		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.','.print_r($vars, true));	
		$method 		= $this->router->method; 				
		$action_desc	= 'account('.$this->db->escape_str($customer_no).') termination flow saved data.';	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));	

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
	}

	function send_termination_form_to_customer($customer_no, $send_data) {

		$this->load->helper('custom_helper');
		$this->load->model(['docs_log_model', 'email_model', 'sms_scheduler_model', 'whatbot_model', 'message_scheduler_model']);
		$this->load->library('whatsapp_template');

		$return = ['status' => 'error', 'msg' => array()];

		$current_row = $this->get_latest_termination_info($customer_no);
		$this_id = $current_row['latest_idx'];

		//validation first
		$email_regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		if (!preg_match($email_regex, $current_row['data_pic_email'])) {
			$return['msg'][] = 'Invalid email.';
			return $return;
		}

		//clear any before data if admin resend
		$this->db->query("DELETE FROM customer_termination_signature WHERE term_data_idx = ?", [$this_id]);

		$sql = "
		INSERT INTO customer_termination_signature (term_data_idx, customer_no, temp_password, expiry) VALUES (?,?,?,?)
		";

		$vars = array(
			$this_id, 
			$customer_no, 
			$send_data['temp_password'],
			$send_data['expiry']
		);

		$this->db->query($sql, $vars);

		//send out
		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name','company_full_name','company_phone','company_email','from_name')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$isp_name      = $cfg['isp_name'] ?? '';
		$company_name  = $cfg['company_full_name'] ?? '';
		$company_phone = $cfg['company_phone'] ?? '';
		$company_email = $cfg['company_email'] ?? '';
		$from_name     = $cfg['from_name'] ?? 'no_reply@isp.com';

		$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'TERMINATION SIGNATURE' AND is_default = 1 " );
		$smsInfo = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'TERMINATION SIGNATURE' AND is_default = 1 " );

		$email_msg = $emailInfo['email_msg'];
		$email_title = $emailInfo['email_title'];
		$whatsapp_message = $smsInfo['sms_msg'];
		$whatsapp_title = $smsInfo['sms_title'];

		$temp_pic_login = $this->config->item('base_url').'auth/clogin';

		$email_title = str_replace('%ISP_NAME%', $isp_name, $email_title);
		$email_msg = str_replace('%ISP_NAME%', $isp_name, $email_msg);
		$email_msg = str_replace('%NAME%', $current_row['data_pic_name'], $email_msg);
		$email_msg = str_replace('%PACKAGE_NAME%', $current_row['data_package_name'], $email_msg);
		$email_msg = str_replace('%TEMP_PASSWORD%', $send_data['temp_password'], $email_msg);
		$email_msg = str_replace('%TERM_LOGIN%', $temp_pic_login, $email_msg);
		$email_msg = str_replace('%COMPANY_NAME%', $company_name, $email_msg);
		$email_msg = str_replace('%COMPANY_EMAIL%', $company_email, $email_msg);
		$email_msg = str_replace('%CUSTOMER_NO%', $customer_no, $email_msg);

		$whatsapp_title = str_replace('%ISP_NAME%', $isp_name, $whatsapp_title);
		$whatsapp_message = str_replace('%ISP_NAME%', $isp_name, $whatsapp_message);
		$whatsapp_message = str_replace('%CUSTOMER_EMAIL%', $current_row['data_pic_email'], $whatsapp_message);
		$whatsapp_message = str_replace('%CUSTOMER_NO%', $customer_no, $whatsapp_message);

		$this->email_model->add_email_schedule([
			'recipient_emails'  => $current_row['data_pic_email'],
			'scheduler_id'        => '',
			'send_by'             => '',
			'email_title'       => $email_title,
			'email_msg'         => $email_msg,
			'email_cust_status' => 1,
			'email_attachment'    => '',
			'email_schedule_on' => date("Y-m-d H:i:00")
		]);

		//check see if whatsapp notification is on and if phone number is valid
		$send_whatsapp = 0;
		if (!empty($current_row['data_pic_mobile'])) {
			if(isValidGlobalPhone($current_row['data_pic_mobile'])) {
				$send_whatsapp = 1;
			}
		}

		if ($send_whatsapp == 1) {
			$chat_id = $current_row['data_pic_mobile'];

			$meta_template = $this->whatsapp_template->build(
				'TERMINATION SIGNATURE',
				[
					'customer_no' => !empty($customer_no) ? $customer_no : '-',
					'customer_email' => !empty($current_row['data_pic_email']) ? $current_row['data_pic_email'] : 'N/A',
				]
			);

			$whatsapp_data = [
				'message' => $whatsapp_title."\n".$whatsapp_message,
				'msg_type' => 'whatsapp',
				'msg_to' => $chat_id,
				'customer_no' => $customer_no,
				'msg_schedule_on' => date('Y-m-d H:i:00')
			];

			$log_data = [
				'acc_id' =>  $current_row['profile_id'] ?? 0,
	            'customer_no' =>  $customer_no,
	            'user_id' =>  0,
	            'controller' =>  'cron',
	            'doc_id' =>  0,
	            'send_type' =>  'custom',
	            'remark' =>  'Whatsapp Termination Pending Customer Action to ' . $current_row['data_pic_name'] . '('.$chat_id.')',
	            'send_method' => 'auto',
				'subject' => $whatsapp_title,
				'body' => $whatsapp_message,
				'whatsapp_list' => [$chat_id],
				'attachment' => [],
				'send_bill_messaging' => false,
				'meta_template' => $meta_template['meta_template_name'] ?? '',
				'meta_vars' => $meta_template['meta_variable'] ?? []
			];

			$this->message_scheduler_model->insert_new_scheduled_message($whatsapp_data, $log_data);
		}

		//update termination status
		$latest_termination_flow = $this->check_latest_termination_flow($customer_no);
		$current_termination_flow = $latest_termination_flow['termination_status'] ?? 'A';

		if ($current_termination_flow != 'S') {
			$this->create_termination_status_record($customer_no, 'S', date('Y-m-d H:i:s'), $this->user['idx']);
		}

		//log this action
		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.','.print_r($vars, true));	
		$method 		= $this->router->method; 				
		$action_desc	= 'account('.$this->db->escape_str($customer_no).') termination flow send to customer for futher action.';	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));	

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));

		$return = ['status' => 'success', 'msg' => array()];
		return $return;
	}

	public function termination_update_file_idx($customer_no, $file_idx) {
		$this->db->query("UPDATE customer_termination_signature SET form_signature_idx = ? WHERE customer_no = ?", array($file_idx, $customer_no));
	}

	private function notify_technical_config_changed($technical_config_change_text, $technical_config_change_field)
	{
		$this->load->model('common_model');
		$this->load->model('email_model');
		$this->load->model('message_scheduler_model');
		$this->load->library('whatsapp_template');

		$technical_users = $this->common_model->get_technical_list();

		$config = $this->common_model->get_table('sys_config', 'val', "`key` = 'from_name'");
		$from_name = $config['val'] ?? 'no_reply@isp.com';
		$customer_no = $this->db->escape_str($this->input->post('customer_no'));

		$title = 'Customer No. ' . $customer_no . ' has changed the technical config settings';
		$body = str_replace('. ', ".<br>", $technical_config_change_text);

		foreach ($technical_users as $tech) {

			if (empty($tech['allow_tech_notification'])) {
				continue;
			}

			// Email
			if (!empty($tech['email'])) {

				$this->email_model->add_email_schedule([
					'recipient_emails'  => $tech['email'],
					'scheduler_id'      => '',
					'send_by'           => '',
					'email_title'       => $title,
					'email_msg'         => $body,
					'email_cust_status' => 1,
					'email_attachment'  => '',
					'email_schedule_on' => date('Y-m-d H:i:s')
				]);
			}

			// WhatsApp
			if (!empty($tech['mobile_no']) && !empty($tech['allow_whatsapp'])) {

				$meta_template = $this->whatsapp_template->build(
					'TECHNICAL CONFIG UPDATED',
					[
						'customer_no' => !empty($customer_no) ? $customer_no : '-',
						'changed_column' => !empty($technical_config_change_field) ? $technical_config_change_field : '-',
					]
				);

				$message_data = [
					'message' => '[Technical Config Updated] - ' . $customer_no,
					'msg_type' => 'whatsapp',
					'msg_to' => $tech['mobile_no'],
					'customer_no' => $customer_no,
					'msg_schedule_on' => date('Y-m-d H:i:s')
				];

				$send_array = [
					'send_type' => 'custom',
					'acc_id' => 0,
					'customer_no' => $customer_no,
					'user_id' => 0,
					'controller' => 'cron',
					'doc_id' => 0,
					'send_method' => 'auto',
					'acc_name' => 'Itelco User',
					'subject' => '[FOLLOW WHATSAPP META TEMPLATE]',
					'body' => '[FOLLOW WHATSAPP META TEMPLATE]',
					'from' => $from_name,
					'email_starter' => '',
					'doc_type' => '[Customer technical config updated]',
					'whatsapp_list' => [$tech['mobile_no']],
					'meta_template' => $meta_template['meta_template_name'] ?? '',
					'meta_vars' => $meta_template['meta_variable'] ?? []
				];

				$this->message_scheduler_model->insert_new_scheduled_message(
					$message_data,
					$send_array
				);
			}

			// Telegram
			if (!empty($tech['telegram_id']) && !empty($tech['allow_telegram'])) {

				$telegram_data = [
					'message' => $body,
					'msg_type' => 'telegram',
					'msg_to' => $tech['telegram_id'],
					'customer_no' => $customer_no,
					'msg_schedule_on' => date('Y-m-d H:i:s')
				];

				$send_array = [
					'send_type' => 'custom',
					'acc_id' => 0,
					'customer_no' => $customer_no,
					'user_id' => 0,
					'controller' => 'cron',
					'doc_id' => 0,
					'send_method' => 'auto',
					'acc_name' => 'Itelco User',
					'subject' => $title,
					'body' => $body,
					'from' => $from_name,
					'doc_type' => '[Customer technical config updated]',
					'telegram_list' => [$tech['telegram_id']]
				];

				$this->message_scheduler_model->insert_new_scheduled_message(
					$telegram_data,
					$send_array
				);
			}
		}
	}

	private function getProfileNameById($profile_id) {
		$this->db->select('acc_name');
		$this->db->from('profile');
		$this->db->where('acc_id', $profile_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row()->acc_name;
		}
		return '';
	}

	private function append_field_change( &$change_text, &$changed_fields, $label, $oldValue, $newValue, $lookup = [], $escape = false) {
		$text = compare_field_change($label, $oldValue, $newValue, $lookup, $escape, $this->db);
		if ($text !== '') {
			$change_text .= $text;
			$changed_fields[] = $label;
		}
	}

}

