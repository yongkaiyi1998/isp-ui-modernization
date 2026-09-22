<?php

class Setting_model extends MY_Model{

	public function __construct()
	{
		parent::__construct();
	}

	function config_update($post_data,$insert_to_table)
	{	
		$result 	= array();
		$query_str 	="";
		if(!empty($post_data['insert_data'][$insert_to_table]))
		{
			$query_str .=" UPDATE `sys_config` SET `val` = CASE ";
			foreach($post_data['insert_data'][$insert_to_table] as $key => $val)
			{
				//~ echo $key.'=>'.$val.'<br>';
				$query_str .=" WHEN `key` = '".$key."' THEN '".$val."' ";
			}
			$query_str .=" ELSE `val` ";
			$query_str .=" END; ";
		}

		$model				= 'action_log_model';
		$this->load->model($model);
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);
		$method 			= $this->router->method;
		$action_desc		= 'sys config has been updated';
		$action_category	= 'update';
		$cust_no			= '';
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
		$result 			= (empty($query_str))?'':$this->db->query($query_str);

		return $result;
	}

	function tax_update($post_data,$insert_to_table)
	{
		$return_val = array();
		$query_str 	= "";
		if(!empty($post_data['insert_data'][$insert_to_table]))
		{
			$query_str .=" UPDATE `sys_tax_type` SET `percent` = CASE ";
			foreach($post_data['insert_data'][$insert_to_table] as $code => $percent)
			{
				//~ echo $key.'=>'.$val.'<br>';
				$query_str .=" WHEN `code` = '".$code."' THEN '".$percent."' ";
			}
			$query_str .=" ELSE `percent` ";
			$query_str .=" END; ";
		}

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);
		$method 		= $this->router->method;
		$action_desc	= 'tax config has been updated';
		$action_category= 'update';
		$cust_no		= '';
		$action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
		$return_val		= (empty($query_str))?'':$this->db->query($query_str);
		return $return_val;
	}

	function tax_insert($post_data,$insert_to_table)
	{
		if(!empty($post_data['insert_data']['new_tax_code']))
		{
			$new_tax_code = $post_data['insert_data']['new_tax_code'];
			$new_tax_percent = $post_data['insert_data']['new_tax_percent'];
			$new_tax_code_arr = [];
			for ($i=0; $i < count($post_data['insert_data']['new_tax_code']); $i++) {
				if(!empty($post_data['insert_data']['new_tax_code'][$i])) {
					$new_tax_code_arr[] = [
						'code' => $new_tax_code[$i],
						'percent' => $new_tax_percent[$i]
					];
				}
			}

			if(!empty($new_tax_code_arr)) {
				$this->db->insert_batch('sys_tax_type', $new_tax_code_arr);
				$query_str = $this->db->last_query();

				$model			= 'action_log_model';
				$this->load->model($model);
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str($query_str);
				$method 		= $this->router->method;
				$action_desc	= 'new tax config has been added [' . implode(',',$new_tax_code) . ']';
				$action_category= 'insert';
				$cust_no		= '';
				$action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
			}
		}
	}

	function technical_users_update($post_data) {
		$post_data = $post_data['insert_data'];
		$this->db->truncate('technical_user');

		if(!empty($post_data['technical_users'])) {
			if (is_string($post_data['technical_users'])) 
				$post_data['technical_users'] = json_decode($post_data['technical_users'], true);

			if (is_array($post_data['technical_users'])) {
				foreach ($post_data['technical_users'] as $tech) {
					if(!empty($tech['name']) && !empty($tech['email'])) {
						$batch_data[] = array(
							'user_id'			=> $tech['user_id'],
							'name'      		=> $tech['name'],
							'email'				=> $tech['email'],
							'contact'		  	=> $tech['contact'],
							'telegram'   		=> $tech['telegram'],
							'technical_type'	=> $tech['technical_type']
						);
					}
				}
			}
			if (!empty($batch_data)) {
				$return_val = $this->db->insert_batch('technical_user', $batch_data);
			}
		}
	}

	/*
	function ledger_update( $post_back ){

		foreach( $this->input->post('payment') AS $key => $val ){
			$update_str = " UPDATE sys_payment_source
								SET ledger_account_code = '".$this->db->escape_str($val)."'
								WHERE payment_source_id = '".$key."' ";
			$this->db->query($update_str) ;
		}

		foreach( $this->input->post('bill') AS $key => $val ){
			$update_str = " UPDATE sys_bill_type
								SET ledger_account_code = '".$this->db->escape_str($val)."'
								WHERE bill_type_id = '".$key."' ";
			$this->db->query($update_str) ;
		}

		foreach( $this->input->post('customer') AS $key => $val ){
			$update_str = " UPDATE sys_customer_category
								SET ledger_account_code = '".$this->db->escape_str($val)."'
								WHERE category_code = '".$key."' ";
			$this->db->query($update_str) ;
		}


	}
	*/
	function ledger_update( $acc_code, $customer_category_code, $refer_type , $refer_id , $credit_debit ){
		$check_str = "SELECT * FROM sys_ledger_account 
						WHERE customer_category_code = '".$customer_category_code."'
						AND refer_type = '".$refer_type."'
						AND refer_id = '".$refer_id."' 
						AND credit_debit = '".$credit_debit."'; ";
		$query = $this->db->query($check_str);
		
		if($query->num_rows() > 0){
			$str = "UPDATE sys_ledger_account 
					SET ledger_account_code = '".$acc_code."'
					WHERE customer_category_code = '".$customer_category_code."'
					AND refer_type = '".$refer_type."'
					AND refer_id = '".$refer_id."'
					AND credit_debit = '".$credit_debit."'; ";
		}else{
			$str = "INSERT INTO sys_ledger_account 
					SET ledger_account_code = '".$acc_code."',
						customer_category_code = '".$customer_category_code."',
						refer_type = '".$refer_type."',
						refer_id = '".$refer_id."',
						credit_debit = '".$credit_debit."'; ";
		}
		
		$this->db->query($str);
	}

	function get_bill_type_listing($txt_search='', $adjust_type='', $tax_code='', $page_item_no='', $max_page_item_no='')
	{
		$return_val = ['total_row' => 0, 'row' => []];
		$query_where = "";

		if ($adjust_type != '' && $adjust_type != 'all') {
			$query_where .= " AND is_debit = $adjust_type ";
		}
		if (!empty($tax_code) && $tax_code != 'all') {
			$query_where .= " AND tax_code = '$tax_code' ";
		}

		$base_sql = "FROM sys_bill_type 
					LEFT JOIN sys_tax_type 
						ON sys_bill_type.tax_code = sys_tax_type.code 
					WHERE (
						bill_type_id LIKE '%$txt_search%' 
						OR name LIKE '%$txt_search%' 
						OR is_debit LIKE '%$txt_search%' 
						OR tax_code LIKE '%$txt_search%' 
					)
					$query_where";

		$count_sql = "SELECT COUNT(*) AS total_row $base_sql";
		$count_query = $this->db->query($count_sql);
		$return_val['total_row'] = $count_query->row()->total_row ?? 0;

		$data_sql = "SELECT 
						bill_type_id, 
						name, 
						IF(is_debit = 0, 'Credit', 'Debit') AS is_debit, 
						tax_code, 
						IFNULL(sys_tax_type.percent, '0.00') AS percent
					$base_sql
					ORDER BY 
						CASE bill_type_id 
							WHEN '1' THEN 1 
							ELSE 999 
						END ASC, 
						name ASC";

		if ($max_page_item_no !== "" && $page_item_no !== "") {
			$data_sql .= " LIMIT $page_item_no, $max_page_item_no";
		} elseif ($max_page_item_no !== "") {
			$data_sql .= " LIMIT $max_page_item_no";
		}

		$data_query = $this->db->query($data_sql);
		$return_val['row'] = $data_query->result_array();

		return $return_val;
	}

	function get_tax_code_list()
	{
		$query = $this->db->query('SELECT * FROM sys_tax_type ORDER BY code');
		return $query->result_array();
	}

	function get_bill_type($bill_type_id='')
	{
		$sql = "SELECT bill_type_id, name, is_debit, tax_code, exclude_from_bill_calculation, class_codes, tax_type  
				FROM sys_bill_type 
				WHERE bill_type_id = '".$this->db->escape_str($bill_type_id)."' 
				LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['bill_type_id']='';
			$return_val['name']='';
			$return_val['is_debit']='';
			$return_val['tax_code']='';
			$return_val['btn_delete']='disabled';
			$return_val['exclude_from_bill_calculation']=0;
			$return_val['class_codes']='';
			$return_val['tax_type']='06';
		}
		
		return $return_val;
	}

	function insert_bill_type($post_data)
	{
		$sql = "INSERT INTO sys_bill_type (name, is_debit, tax_code, exclude_from_bill_calculation, class_codes, tax_type) 
				VALUES ('".$this->db->escape_str($post_data['name'])."', ".$this->db->escape_str($post_data['is_debit']).", 
				'".$this->db->escape_str($post_data['tax_code'])."' , '".$this->db->escape_str( $post_data['exclude_from_bill_calculation'] )."', '".$this->db->escape_str( implode(",", $post_data['class_codes']) )."', '".$this->db->escape_str( $post_data['tax_type'] )."' )";
		return $this->db->query($sql);
	}

	function update_bill_type($post_data)
	{
		$sql = "UPDATE sys_bill_type 
				SET name = '".$this->db->escape_str($post_data['name'])."', 
				is_debit = ".$this->db->escape_str($post_data['is_debit']).", 
				tax_code = '".$this->db->escape_str($post_data['tax_code'])."' ,
				exclude_from_bill_calculation = '".$this->db->escape_str( $post_data['exclude_from_bill_calculation'] )."', 
				class_codes = '".$this->db->escape_str( implode(",", $post_data['class_codes']) )."', 
				tax_type = '".$this->db->escape_str( $post_data['tax_type'] )."' 
				WHERE bill_type_id = ".$this->db->escape_str($post_data['bill_type_id']);
		return $this->db->query($sql);
	}

	function validate_being_used_type($bill_type_id)
	{
		$total_row = 0;

		$sql_1 = "SELECT * FROM bill_adjustment WHERE bill_type = ".$bill_type_id;
		$query_1 = $this->db->query($sql_1);
		$total_row = $query_1->num_rows();
		if($total_row == 0)
		{
			$sql_2 = "SELECT * FROM bill_detail WHERE bill_type = ".$bill_type_id;
			$query_2 = $this->db->query($sql_2);
			$total_row += $query_2->num_rows();

			if($total_row == 0)
			{
				$sql_3 = "SELECT * FROM payment WHERE bill_type = ".$bill_type_id;
				$query_3 = $this->db->query($sql_3);
				$total_row += $query_3->num_rows();
			}
		}
		
		return $total_row;
	}

	function delete_bill_type($post_data)
	{
		$sql = "DELETE FROM sys_bill_type  
				WHERE bill_type_id = ".$this->db->escape_str($post_data['bill_type_id']);
		return $this->db->query($sql);
	}

	function get_nas_setting_listing($txt_search='')
	{
		$this->radius_db = $this->load->database('radius',true,false);

		$return_val = array();
		$return_val['total_row'] = 0;
		$return_val['row'] = array();
		$query_where = "";

		$sql = "SELECT * 
				FROM nas  
				WHERE (`nasname` LIKE '%$txt_search%') ORDER BY `id`";
				
		$query = $this->radius_db->query($sql);
		
		$return_val['total_row'] = $query->num_rows();
		
		if ($query->num_rows() > 0)	
		{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;
	}

	function get_nas_address($id='')
	{

		$this->radius_db = $this->load->database('radius',true,false);

		$sql = "SELECT * 
				FROM nas 
				WHERE id = '".$this->db->escape_str($id)."' 
				LIMIT 1";
		$query = $this->radius_db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['id']='';
			$return_val['nasname']='';
			$return_val['shortname']='';
			$return_val['type']='';
			$return_val['ports']='';
			$return_val['secret']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_payment_type_listing($txt_search='')
	{
		$return_val = array();
		$return_val['total_row'] = 0;
		$return_val['row'] = array();
		$query_where = "";

		$sql = "SELECT payment_source_id, name , status
				FROM sys_payment_source 
				WHERE (payment_source_id LIKE '%$txt_search%' OR name LIKE '%$txt_search%') ORDER BY name";
				
		$query = $this->db->query($sql);
		
		$return_val['total_row'] = $query->num_rows();
		
		if ($query->num_rows() > 0)	
		{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;
	}

	function get_equipment_type_listing($txt_search='')
	{
		$return_val = array();
		$return_val['total_row'] = 0;
		$return_val['row'] = array();
		$query_where = "";

		$sql = "SELECT equipment_type_id, name , status
				FROM sys_equipment_type 
				WHERE (equipment_type_id LIKE '%$txt_search%' OR `name` LIKE '%$txt_search%') ORDER BY `name`";
				
		$query = $this->db->query($sql);
		
		$return_val['total_row'] = $query->num_rows();
		
		if ($query->num_rows() > 0)	
		{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;
	}

	function get_asset_code_list($txt_search='')
	{
		$return_val = array();
		$return_val['total_row'] = 0;
		$return_val['row'] = array();
		$query_where = "";

		$sql = "SELECT * 
				FROM sys_asset_code 
				WHERE (short_name LIKE '%$txt_search%' OR asset_code LIKE '%$txt_search%') ORDER BY short_name";
				
		$query = $this->db->query($sql);
		
		$return_val['total_row'] = $query->num_rows();
		
		if ($query->num_rows() > 0)	
		{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;
	}

	function get_payment_type($payment_source_id='')
	{
		$sql = "SELECT payment_source_id, name
				FROM sys_payment_source 
				WHERE payment_source_id = '".$this->db->escape_str($payment_source_id)."' 
				LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['payment_source_id']='';
			$return_val['name']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_equipment_type($equipment_type_id='')
	{
		$sql = "SELECT equipment_type_id, name
				FROM sys_equipment_type 
				WHERE equipment_type_id = '".$this->db->escape_str($equipment_type_id)."' 
				LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['equipment_type_id']='';
			$return_val['name']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_asset_category($category_idx='')
	{
		$sql = "SELECT category_idx, category_code, category_name
				FROM sys_asset_category
				WHERE category_idx = '".$category_idx."' LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['category_idx']=0;
			$return_val['category_code']='';
			$return_val['category_name']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_asset_code($asset_code_idx='')
	{
		$sql = "SELECT * 
				FROM sys_asset_code
				WHERE asset_code_idx = '".$asset_code_idx."' LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['asset_code_idx']=0;
			$return_val['asset_code']='';
			$return_val['category_idx']=0;
			$return_val['barcode']='';
			$return_val['brand']='';
			$return_val['model_no']='';
			$return_val['manufacturer']='';
			$return_val['origin_country']='';
			$return_val['uom']='';
			$return_val['short_name']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_asset_site($site_code='')
	{
		$sql = "SELECT site_code, site_name
				FROM sys_asset_site
				WHERE site_code = '".$site_code."' LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['site_code']='';
			$return_val['site_name']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_product_category($product_code='')
	{
		$sql = "SELECT product_code, product_name
				FROM sys_product_category
				WHERE product_code = '".$product_code."' LIMIT 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['product_code']='';
			$return_val['product_name']='';
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function validate_being_used_asset_site($site_code)
	{
		$total_row = 0;
		return $total_row;
	}

	function validate_being_used_product_category($product_code)
	{
		$total_row = 0;

		$sql_1 = "SELECT * FROM trouble_ticket WHERE tt_category = '". $product_code ."' ;" ;
		$query_1 = $this->db->query($sql_1);
		$total_row = $query_1->num_rows();

		return $total_row;
		
	}

	function validate_being_used_asset_category($category_idx)
	{
		$total_row = 0;
		return $total_row;
	}

	function validate_being_used_asset_code($asset_code_idx)
	{
		$total_row = 0;
		return $total_row;
	}

	function validate_being_used_payment($payment_source_id)
	{
		$total_row = 0;

		$sql_1 = "SELECT * FROM payment WHERE payment_source = ".$payment_source_id;
		$query_1 = $this->db->query($sql_1);
		$total_row = $query_1->num_rows();

		return $total_row;
	}

	function validate_being_used_equipment_type($equipment_type_id)
	{
		//probably no need to validate for delete as this is only kept for recording purpose, but might have orphaned records
		$total_row = 0;
		return $total_row;
	}

	function insert_payment_type($post_data)
	{
		$sql = "INSERT INTO sys_payment_source (name) 
				VALUES ('".$this->db->escape_str($post_data['name'])."')";
		return $this->db->query($sql);
	}

	function update_payment_type($post_data)
	{
		$sql = "UPDATE sys_payment_source 
				SET name = '".$this->db->escape_str($post_data['name'])."' 
				WHERE payment_source_id = ".$this->db->escape_str($post_data['payment_source_id']);
		return $this->db->query($sql);
	}

	function insert_equipment_type($post_data)
	{
		$sql = "INSERT INTO sys_equipment_type (name) 
				VALUES ('".$this->db->escape_str($post_data['name'])."')";
		return $this->db->query($sql);
	}

	function update_equipment_type($post_data)
	{
		$sql = "UPDATE sys_equipment_type 
				SET name = '".$this->db->escape_str($post_data['name'])."' 
				WHERE equipment_type_id = ".$this->db->escape_str($post_data['equipment_type_id']);
		return $this->db->query($sql);
	}

	function insert_nas_address($post_data)
	{
		$this->radius_db = $this->load->database('radius',true,false);

		if (!isset($post_data['ports'])) {
			$post_data['ports'] = 0;
		}	

		if (empty($post_data['ports'])) {
			$ports = 0;
		} else {
			$ports = $this->db->escape_str($post_data['ports']);
		}

		$sql = "INSERT INTO nas (`nasname`, `shortname`, `type`, `ports`, `secret`) 
				VALUES ('".$this->db->escape_str($post_data['nasname'])."', '".$this->db->escape_str($post_data['shortname'])."', '".$this->db->escape_str($post_data['type'])."', '".$this->db->escape_str($ports)."', '".$this->db->escape_str($post_data['secret'])."')";

		$return = $this->radius_db->query($sql);

		$this->radius_restart();

		return $return; 
	}

	function update_nas_address($post_data)
	{
		$this->radius_db = $this->load->database('radius',true,false);

		if (!isset($post_data['ports'])) {
			$post_data['ports'] = 0;
		}	

		if (empty($post_data['ports'])) {
			$ports = 0;
		} else {
			$ports = $this->db->escape_str($post_data['ports']);
		}

		$sql = "UPDATE nas 
				SET `nasname` = '".$this->db->escape_str($post_data['nasname'])."', 
				`shortname` = '".$this->db->escape_str($post_data['shortname'])."', 
				`type` = '".$this->db->escape_str($post_data['type'])."',
				`ports` = '".$ports."',
				`secret` = '".$this->db->escape_str($post_data['secret'])."'  
				WHERE id = ".$this->db->escape_str($post_data['id']);

		$return = $this->radius_db->query($sql);

		$this->radius_restart();

		return $return;
	}

	function delete_payment_type($post_data)
	{
		$sql = "DELETE FROM sys_payment_source  
				WHERE payment_source_id = ".$this->db->escape_str($post_data['payment_source_id']);
		return $this->db->query($sql);
	}

	function delete_equipment_type($post_data)
	{
		$sql = "DELETE FROM sys_equipment_type  
				WHERE equipment_type_id = ".$this->db->escape_str($post_data['equipment_type_id']);
		return $this->db->query($sql);
	}

	function delete_nas_address($post_data)
	{
		$this->radius_db = $this->load->database('radius',true,false);

		$sql = "DELETE FROM nas 
				WHERE id = ".$this->db->escape_str($post_data['id']);

		$return = $this->radius_db->query($sql);

		$this->radius_restart();

		return $return;
	}
	
	function update_payment_type_status($payment_source_id, $status)
	{
		$sql = "UPDATE sys_payment_source 
				SET status = '". $status . "'
				WHERE payment_source_id = ".$this->db->escape_str($payment_source_id);
		return $this->db->query($sql);
	}

	function update_equipment_type_status($equipment_type_id, $status)
	{
		$sql = "UPDATE sys_equipment_type 
				SET status = '". $status . "'
				WHERE equipment_type_id = ".$this->db->escape_str($equipment_type_id);
		return $this->db->query($sql);
	}

	function update_nas_address_status($nas_id, $status)
	{
		$sql = "UPDATE sys_nas_address 
				SET status = '". $status . "'
				WHERE nas_id = ".$this->db->escape_str($nas_id);
		return $this->db->query($sql);
	}
	
	function insert_product_category($post_data)
	{
		$sql = "INSERT INTO sys_product_category 
				SET product_name = '". ( $this->db->escape_str( $post_data['name'] ) ) ."' ; " ;
		return $this->db->query($sql);
	}

	function update_product_category($post_data)
	{
		$sql = "UPDATE sys_product_category 
				SET product_name = '".$this->db->escape_str($post_data['name'])."' 
				WHERE product_code = ".$this->db->escape_str($post_data['product_code']);
		return $this->db->query($sql);
	}
	
	function delete_product_category($post_data)
	{
		$sql = "DELETE FROM sys_product_category
				WHERE product_code = ".$this->db->escape_str($post_data['product_code']);
		return $this->db->query($sql);
	}

	function insert_asset_category($post_data)
	{
		$sql = "INSERT INTO sys_asset_category  
				SET category_code = '". ( $this->db->escape_str( $post_data['category_code'] ) ) ."', 
				category_name = '". ( $this->db->escape_str( $post_data['category_name'] ) ) ."' ; " ;
		return $this->db->query($sql);
	}

	function update_asset_category($post_data)
	{
		$sql = "UPDATE sys_asset_category 
				SET category_code = '".$this->db->escape_str($post_data['category_code'])."', 
				category_name = '".$this->db->escape_str($post_data['category_name'])."'  
				WHERE category_idx = ".$this->db->escape_str($post_data['category_idx']);
		return $this->db->query($sql);
	}

	function insert_asset_code($post_data)
	{
		$sql = "INSERT INTO sys_asset_code  
				SET asset_code = '". ( $this->db->escape_str( $post_data['asset_code'] ) ) ."', 
				category_idx = '". ( $this->db->escape_str( $post_data['category_idx'] ) ) ."', 
				barcode = '". ( $this->db->escape_str( $post_data['barcode'] ) ) ."', 
				brand = '". ( $this->db->escape_str( $post_data['brand'] ) ) ."', 
				model_no = '". ( $this->db->escape_str( $post_data['model_no'] ) ) ."', 
				manufacturer = '". ( $this->db->escape_str( $post_data['manufacturer'] ) ) ."',
				origin_country = '". ( $this->db->escape_str( $post_data['origin_country'] ) ) ."', 
				short_name = '". ( $this->db->escape_str( $post_data['short_name'] ) ) ."' ; " ;
		return $this->db->query($sql);
	}

	function update_asset_code($post_data)
	{
		$sql = "UPDATE sys_asset_code  
				SET asset_code = '".$this->db->escape_str($post_data['asset_code'])."', 
				category_idx = '".$this->db->escape_str($post_data['category_idx'])."', 
				barcode = '".$this->db->escape_str($post_data['barcode'])."', 
				brand = '".$this->db->escape_str($post_data['brand'])."', 
				model_no = '".$this->db->escape_str($post_data['model_no'])."', 
				manufacturer = '".$this->db->escape_str($post_data['manufacturer'])."', 
				origin_country = '".$this->db->escape_str($post_data['origin_country'])."', 
				short_name = '".$this->db->escape_str($post_data['short_name'])."' 
				WHERE asset_code_idx = ".$this->db->escape_str($post_data['asset_code_idx']);
		return $this->db->query($sql);
	}

	function delete_asset_category($post_data)
	{
		$sql = "DELETE FROM sys_asset_category
				WHERE category_idx = ".$this->db->escape_str($post_data['category_idx']);
		return $this->db->query($sql);
	}

	function delete_asset_code($post_data)
	{
		$sql = "DELETE FROM sys_asset_code
				WHERE asset_code_idx = ".$this->db->escape_str($post_data['asset_code_idx']);
		return $this->db->query($sql);
	}

	function insert_asset_site($post_data)
	{
		$sql = "INSERT INTO sys_asset_site 
				SET site_name = '". ( $this->db->escape_str( $post_data['site_name'] ) ) ."' ; " ;
		return $this->db->query($sql);
	}

	function update_asset_site($post_data)
	{
		$sql = "UPDATE sys_asset_site 
				SET site_name = '".$this->db->escape_str($post_data['site_name'])."' 
				WHERE site_code = ".$this->db->escape_str($post_data['site_code']);
		return $this->db->query($sql);
	}

	function delete_asset_site($post_data)
	{
		$sql = "DELETE FROM sys_asset_site
				WHERE site_code = ".$this->db->escape_str($post_data['site_code']);
		return $this->db->query($sql);
	}
	
	function customer_category_ledger_update( $category_code , $account_code ){
		$sql = " UPDATE sys_customer_category SET ledger_account_code = '".$this->db->escape_str($account_code)."' 
					WHERE category_code = '".$category_code."';";
		return $this->db->query($sql);
	}
	
	function get_sys_state($state_code){
		
		$sql = "SELECT state_code, name
				FROM sys_state
				WHERE state_code = '".$this->db->escape_str($state_code)."' ";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
		}
		else 
		{
			$return_val['state_code']='';
			$return_val['name']='';
			
		}
		
		return $return_val;
		
		
	}

	function radius_restart() {

		//$cmd = "sudo systemctl restart radiusd";
		$cmd = $this->config->item('sh_bin_folder')."restart-radiuss.sh";

		log_message('error', $cmd);

		$result = shell_exec($cmd);

		log_message('error', $result);

		return true;
	}
	
	
}
