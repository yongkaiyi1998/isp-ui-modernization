<?php
class Asset_model extends MY_Model{

	protected $_table = 'asset';
	protected $_primary_key = 'asset_id';

	public function __construct()
	{
		parent::__construct();
	}

	function get_asset_list($txt_search='',$page_item_no=0,$row_per_page='')
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$query_str 					= "SELECT count(a.asset_id) AS total_row FROM asset a WHERE a.asset_name LIKE '%$txt_search%' ";
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT a.*, sac.category_name, sas.site_name   
						FROM asset a 
						LEFT JOIN sys_asset_category sac ON (sac.category_idx = a.category_idx) 
						LEFT JOIN sys_asset_site sas ON (a.site = sas.site_code) 
						WHERE a.asset_name LIKE '%$txt_search%' 
						ORDER BY a.asset_tag 
						LIMIT $page_item_no, ".$row_per_page;
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}

	function get_service_record_list($txt_search='',$txt_date_start='',$txt_date_end='',$page_item_no=0,$row_per_page='')
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = '0';
		$return_val['row'] = array();

		$query_where = "";
		if ( !empty($txt_date_start) ) {
			$query_where = "AND am.request_date >= '$txt_date_start' ";
		}
		if ( !empty($txt_date_end) ) {
			$query_where .= "AND am.request_date <= '$txt_date_end' ";
		}

		$query_str 					= "
		SELECT count(am.maint_id) AS total_row 
		FROM asset_maint am 
		LEFT JOIN asset a ON (am.asset_id = a.asset_id) 
		WHERE a.asset_name LIKE '%$txt_search%' ".$query_where;
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT am.*, a.asset_name, a.asset_tag, 
						CASE WHEN am.status = 'P' THEN 1
						ELSE 0 END AS status_priority    
						FROM asset_maint am  
						LEFT JOIN asset a ON (am.asset_id = a.asset_id) 
						WHERE a.asset_name LIKE '%$txt_search%' ".$query_where." 
						ORDER BY status_priority desc, am.request_date asc, am.service_date_time asc 
						LIMIT $page_item_no, ".$row_per_page;
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;	

	}

	function get_transfer_record_list($txt_search='',$txt_date_start='',$txt_date_end='',$page_item_no=0,$row_per_page='')
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = '0';
		$return_val['row'] = array();

		$query_where = "";
		if ( !empty($txt_date_start) ) {
			$query_where = "AND at.transfer_date >= '$txt_date_start' ";
		}
		if ( !empty($txt_date_end) ) {
			$query_where .= "AND at.transfer_date <= '$txt_date_end' ";
		}

		$query_str 					= "
		SELECT count(at.transfer_id) AS total_row 
		FROM asset_transfer at 
		LEFT JOIN asset a ON (at.asset_id = a.asset_id) 
		WHERE a.asset_name LIKE '%$txt_search%' ".$query_where;
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT at.*, a.asset_name, sas1.site_name AS from_name, sas2.site_name AS to_name, a.asset_tag    
						FROM asset_transfer at  
						LEFT JOIN asset a ON (at.asset_id = a.asset_id) 
						LEFT JOIN sys_asset_site sas1 ON (at.from = sas1.site_code) 
						LEFT JOIN sys_asset_site sas2 ON (at.to = sas2.site_code) 
						WHERE a.asset_name LIKE '%$txt_search%' ".$query_where." 
						ORDER BY a.asset_tag 
						LIMIT $page_item_no, ".$row_per_page;
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;	

	}

	function get_asset($asset_id='')
	{
		$query = $this->db->query("SELECT a.*, pam.start_date, pam.interval_value, pam.interval_unit, pam.next_maint_date, pam.end_date   
							FROM asset a 
							LEFT JOIN planned_asset_maint pam ON (a.asset_id = pam.asset_id) 
							WHERE a.asset_id='".$this->db->escape_str($asset_id)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['asset_id'] = 0;
			$return_val['vendor'] = '';
			$return_val['asset_name'] = '';		
			$return_val['asset_code'] = '';
			$return_val['asset_tag'] = '';
			$return_val['site'] = '';
			$return_val['location'] = '';
			$return_val['category_idx'] = 0;
			$return_val['department'] = '';
			$return_val['brand'] = '';
			$return_val['details'] = '';
			$return_val['model_no'] = '';
			$return_val['origin_country'] = '';
			$return_val['serial_no'] = '';
			$return_val['purchase_date'] = '';
			$return_val['purchase_price'] = '';
			$return_val['warranty'] = '';
			$return_val['warranty_expiry'] = '';
			$return_val['manufacturer'] = '';
			$return_val['owner'] = '';
			$return_val['use_life'] = '1';
			$return_val['depreciation_rate'] = 0;
			$return_val['asset_status'] = 1;
			$return_val['current_asset_value'] = 0;
			$return_val['non_capitalize'] = 0;
			$return_val['reminder_expiry'] = 6;
			$return_val['reminder_cnt'] = 0;
			$return_val['qty'] = 1;

			$return_val['start_date'] = '';
			$return_val['interval_value'] = '';
			$return_val['interval_unit'] = '';
			$return_val['next_maint_date'] = '';
			$return_val['end_date'] = '';

			$return_val['customer_no'] = 0;
			
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_maint_record($maint_id='') {
		$query = $this->db->query("SELECT am.*, am.maint_id AS temp_id  
							FROM asset_maint am  
							WHERE am.maint_id='".$this->db->escape_str($maint_id)."' 
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {

			$return_val['maint_id'] = 0;
			$return_val['asset_id'] = 0;
			$return_val['vendor'] = '';
			$return_val['request_date'] = '';
			$return_val['service_date_time'] = '';
			$return_val['maint_cost'] = 0;
			$return_val['status'] = 'P';
			$return_val['doc_ref'] = '';
			$return_val['is_planned'] = 0;
			$return_val['description'] = '';

			$return_val['temp_id'] = rand(10000, 99999);

			$return_val['btn_delete']='disabled';
		}

		return $return_val;
	}

	function get_transfer_record_by_id($transfer_id='') {
		$query = $this->db->query("SELECT at.* 
							FROM asset_transfer at  
							WHERE at.transfer_id='".$this->db->escape_str($transfer_id)."' 
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {

			$return_val['transfer_id'] = 0;
			$return_val['asset_id'] = 0;
			$return_val['from'] = 0;
			$return_val['to'] = 0;
			$return_val['transfer_date'] = '';
			$return_val['description'] = '';

			$return_val['btn_delete']='disabled';
		}

		return $return_val;
	}

	function get_autocomplete_asset_code($keyword) {
		$keyword = $this->db->escape_str($keyword);
		$query_str = "	SELECT sac.* 
						FROM sys_asset_code sac 
						WHERE sac.asset_code LIKE '%$keyword%' OR sac.short_name LIKE '%$keyword%' 
						ORDER BY sac.short_name LIMIT 10 ";
		$query = $this->db->query($query_str);
		return $query->result_array(); 
	}

	function asset_insert($data)
	{								
		if (empty($data['non_capitalize'])) {
			$data['non_capitalize'] = 0;
		}
		$data['purchase_date'] = !empty($data['purchase_date']) && strtotime($data['purchase_date']) ? $data['purchase_date'] : '1970-01-01 00:00:00';
		$data['warranty_expiry'] = !empty($data['warranty_expiry']) && strtotime($data['warranty_expiry']) ? $data['warranty_expiry'] : '1970-01-01';
		$data['purchase_price'] = !empty($data['purchase_price']) && is_numeric($data['purchase_price']) ? $data['purchase_price'] : 1.0000;

		$query_str = "INSERT INTO asset (
			vendor, asset_name, asset_code, asset_tag, site, location, category_idx, department, brand, details,
			model_no, origin_country, serial_no, purchase_date, purchase_price, warranty, warranty_expiry, manufacturer, owner,
			use_life, depreciation_rate, current_asset_value, asset_status, non_capitalize, customer_no, created_by
		) VALUES (
			?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
			?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
			?, ?, ?, ?, ?, ?
		)";
		$this->db->query($query_str, array(
			$data['vendor'],
			$data['asset_name'],
			$data['asset_code'],
			$data['asset_tag'],
			$data['site'],
			$data['location'],
			$data['category_idx'],
			$data['department'],
			$data['brand'],
			$data['details'],
			$data['model_no'],
			$data['origin_country'],
			$data['serial_no'],
			$data['purchase_date'],
			$data['purchase_price'],
			$data['warranty'],
			$data['warranty_expiry'],
			$data['manufacturer'],
			$data['owner'],
			$data['use_life'],
			$data['depreciation_rate'],
			$data['current_asset_value'],
			$data['asset_status'],
			$data['non_capitalize'],
			$data['customer_no'],
			$data['created_by']
		));

		$asset_id = $this->db->insert_id();

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new asset ('.$data['asset_name'].') has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	

		if (!empty($data['customer_no'])) {
			$customer_action_model			= 'customer_action_log_model';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($data['customer_no']));
		}

		return $asset_id;
		
	}

	function asset_update($data)
	{

			$change_text = '';

			$assetInfo = $this->get_asset( $data['asset_id'] );

			if (!empty($assetInfo)) {

				$this->load->model('common_model');

				$status_arr = array('1' => 'Active', '2' => 'To Scrap', '3' => 'Disposed');

				$category_arr = array();
				$category_list = $this->common_model->get_asset_category_list();
				foreach ($category_list as $category_rec) {
					$category_arr[$category_rec['category_idx']] = $category_rec['category_name'];
				}

				if ($assetInfo['vendor'] != $data['vendor']) {
					$change_text .= compare_field_change('Vendor', $assetInfo['vendor'], $data['vendor'], [], true, $this->db); 
				}

				if ($assetInfo['asset_name'] != $data['asset_name']) {
					$change_text .= compare_field_change('Asset Name', $assetInfo['asset_name'], $data['asset_name'], [], true, $this->db);
				}

				if ($assetInfo['asset_code'] != $data['asset_code']) {
					$change_text .= compare_field_change('Asset Code', $assetInfo['asset_code'], $data['asset_code'], [], true, $this->db);
				}

				if ($assetInfo['asset_tag'] != $data['asset_tag']) {
					$change_text .= compare_field_change('Asset Tag', $assetInfo['asset_tag'], $data['asset_tag'], [], true, $this->db);
				}

				if ($assetInfo['location'] != $data['location']) {
					$change_text .= compare_field_change('Location', $assetInfo['location'], $data['location'], [], true, $this->db);
				}

				try {
					if ($assetInfo['category_idx'] != $data['category_idx']) {
						$change_text .= compare_field_change('Category', $assetInfo['category_idx'], $data['category_idx'], $category_arr, true, $this->db);
					}
				} catch (Exception $e) {
					log_message('error', 'Asset Edit: '.$e->getMessage());
				}

				if ($assetInfo['department'] != $data['department']) {
					$change_text .= compare_field_change('Department', $assetInfo['department'], $data['department'], [], true, $this->db);
				}

				if ($assetInfo['brand'] != $data['brand']) {
					$change_text .= compare_field_change('Brand', $assetInfo['brand'], $data['brand'], [], true, $this->db);
				}

				if ($assetInfo['details'] != $data['details']) {
					$change_text .= compare_field_change('Details', $assetInfo['details'], $data['details'], [], true, $this->db);
				}

				if ($assetInfo['model_no'] != $data['model_no']) {
					$change_text .= compare_field_change('Model No.', $assetInfo['model_no'], $data['model_no'], [], true, $this->db);
				}

				if ($assetInfo['origin_country'] != $data['origin_country']) {
					$change_text .= compare_field_change('Country of Origin', $assetInfo['origin_country'], $data['origin_country'], [], true, $this->db);
				}

				if ($assetInfo['serial_no'] != $data['serial_no']) {
					$change_text .= compare_field_change('Serial No.', $assetInfo['serial_no'], $data['serial_no'], [], true, $this->db);
				}

				if (date('Y-m-d', strtotime($assetInfo['purchase_date'])) != $data['purchase_date']) {
					$change_text .= compare_field_change('Purchase Date', date('Y-m-d', strtotime($assetInfo['purchase_date'])), $data['purchase_date'], [], true, $this->db);
				}

				if ($assetInfo['purchase_price'] != $data['purchase_price']) {
					$change_text .= compare_field_change('Purchase Price', $assetInfo['purchase_price'], $data['purchase_price'], [], true, $this->db);
				}

				if ($assetInfo['warranty'] != $data['warranty']) {
					$change_text .= compare_field_change('Warranty No.', $assetInfo['warranty'], $data['warranty'], [], true, $this->db);
				}

				if ($assetInfo['warranty_expiry'] != $data['warranty_expiry']) {
					$change_text .= compare_field_change('Warranty Expiry', $assetInfo['warranty_expiry'], $data['warranty_expiry'], [], true, $this->db);
				}

				if ($assetInfo['manufacturer'] != $data['manufacturer']) {
					$change_text .= compare_field_change('Manufacturer', $assetInfo['manufacturer'], $data['manufacturer'], [], true, $this->db);
				}

				if ($assetInfo['owner'] != $data['owner']) {
					$change_text .= compare_field_change('Owner', $assetInfo['owner'], $data['owner'], [], true, $this->db);
				}

				if ($assetInfo['use_life'] != $data['use_life']) {
					$change_text .= compare_field_change('Use Life', $assetInfo['use_life'], $data['use_life'], [], true, $this->db);
				}

				if ($assetInfo['depreciation_rate'] != $data['depreciation_rate']) {
					$change_text .= compare_field_change('Depreciation Rate', $assetInfo['depreciation_rate'], $data['depreciation_rate'], [], true, $this->db);
				}

				if ($assetInfo['current_asset_value'] != $data['current_asset_value']) {
					$change_text .= compare_field_change('Current Asset Value', $assetInfo['current_asset_value'], $data['current_asset_value'], [], true, $this->db);
				}

				if ($assetInfo['asset_status'] != $data['asset_status']) {
					$status_text = ' Status Changed. ';
					if (isset($status_arr[$assetInfo['asset_status']]) && isset($status_arr[$data['asset_status']])) {
						$status_text = compare_field_change('Status', $assetInfo['asset_status'], $data['asset_status'], $status_arr, true, $this->db);
					}

					$change_text .= $status_text;
				}

				if ($assetInfo['customer_no'] != $data['customer_no']) {
					$change_text .= ' Customer Possession has changed.';
				}

			}

			if (empty($data['non_capitalize'])) {
				$data['non_capitalize'] = 0;
			}

			$data['purchase_date'] = !empty($data['purchase_date']) && strtotime($data['purchase_date']) ? $data['purchase_date'] : '1970-01-01 00:00:00';
			$data['warranty_expiry'] = !empty($data['warranty_expiry']) && strtotime($data['warranty_expiry']) ? $data['warranty_expiry'] : '1970-01-01';
			$data['purchase_price'] = !empty($data['purchase_price']) && is_numeric($data['purchase_price']) ? $data['purchase_price'] : 1.0000;

			$query_str = "UPDATE asset SET 
			vendor = ?, 
			asset_name = ?, 
			asset_code = ?, 
			asset_tag = ?, 
			site = ?, 
			location = ?, 
			category_idx = ?, 
			department = ?, 
			brand = ?, 
			details = ?,
			model_no = ?, 
			origin_country = ?, 
			serial_no = ?, 
			purchase_date = ?, 
			purchase_price = ?, 
			warranty = ?, 
			warranty_expiry = ?, 
			manufacturer = ?, 
			owner = ?, 
			use_life = ?, 
			depreciation_rate = ?, 
			current_asset_value = ?, 
			asset_status = ?, 
			non_capitalize = ?, 
			customer_no = ?
			WHERE asset_id = ? ";

			$this->db->query($query_str, array(
			$data['vendor'],
			$data['asset_name'],
			$data['asset_code'],
			$data['asset_tag'],
			$data['site'],
			$data['location'],
			$data['category_idx'],
			$data['department'],
			$data['brand'],
			$data['details'],
			$data['model_no'],
			$data['origin_country'],
			$data['serial_no'],
			$data['purchase_date'],
			$data['purchase_price'],
			$data['warranty'],
			$data['warranty_expiry'],
			$data['manufacturer'],
			$data['owner'],
			$data['use_life'],
			$data['depreciation_rate'],
			$data['current_asset_value'],
			$data['asset_status'],
			$data['non_capitalize'],
			$data['customer_no'], 
			$data['asset_id'] 
			));
			
			$model			= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 				
			$action_desc	= 'an asset ('.$data['asset_name'].') has been updated. '.$change_text;	
			$action_category = 'update';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	

			if (!empty($data['customer_no'])) {
				$customer_action_model			= 'customer_action_log_model';
				$this->load->model($customer_action_model);
				$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($data['customer_no']));
			}
			
	}

	function asset_delete($asset_id='')
	{
		$query_str = "DELETE FROM asset WHERE asset_id=".$asset_id;
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'an asset ('.$asset_id.') has been deleted.';
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}

	function service_record_insert($data)
	{
		if (!isset($data['is_planned'])) {
			$data['is_planned'] = 0;
		}

		$query_str = "INSERT INTO asset_maint (asset_id, vendor, request_date, service_date_time, maint_cost, `status`, doc_ref, is_planned, description, created_at, created_by) VALUES (
			?,?,?,?,?,?,?,?,?,?,
			? 
		)";	
		$this->db->query($query_str, array(
			$data['asset_id'],
			$data['vendor'],
			$data['request_date'],
			$data['service_date_time'],
			$data['maint_cost'],
			$data['status'],
			$data['doc_ref'],
			$data['is_planned'],
			$data['description'],
			date('Y-m-d H:i:s'),
			$data['created_by'],
		));

		$maint_id = $this->db->insert_id();
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new service record (Asset ID:'.$data['asset_id'].') has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	

		return $maint_id;
	}

	function service_record_update($data)
	{

			$change_text = '';

			$maintInfo = $this->get_maint_record($data['maint_id']);

			if (!empty($maintInfo)) {

				$status_arr = array('P' => 'Pending', 'D' => 'Done');

				if ($maintInfo['vendor'] != $data['vendor']) {
					$change_text .= compare_field_change('Vendor', $maintInfo['vendor'], $data['vendor'], [], true, $this->db);
				}

				if ($maintInfo['request_date'] != $data['request_date']) {
					$change_text .= compare_field_change('Request Date', $maintInfo['request_date'], $data['request_date'], [], true, $this->db);
				}

				if (date('Y-m-d', strtotime($maintInfo['service_date_time'])) != $data['service_date_time']) {
					$change_text .= compare_field_change('Service Date', date('Y-m-d', strtotime($maintInfo['service_date_time'])), $data['service_date_time'], [], true, $this->db);
				}

				if ($maintInfo['maint_cost'] != $data['maint_cost']) {
					$change_text .= compare_field_change('Maintenance Cost', $maintInfo['maint_cost'], $data['maint_cost'], [], true, $this->db);
				}

				if ($maintInfo['status'] != $data['status']) {
					$status_text = ' Status Changed. ';
					if (isset($status_arr[$maintInfo['status']]) && isset($status_arr[$data['status']])) {
						$status_text = compare_field_change('Status', $maintInfo['status'], $data['status'], $status_arr, true, $this->db);
					}

					$change_text .= $status_text;
				}

			}

			$query_str = "UPDATE asset_maint SET 
			vendor = ?, 
			request_date = ?, 
			service_date_time = ?, 
			maint_cost = ?, 
			`status` = ?, 
			doc_ref = ?, 
			description = ?, 
			updated_at = ?, 
			updated_by = ?  
			WHERE asset_id = ? AND maint_id = ? ";

			$this->db->query($query_str, array(
			$data['vendor'],
			$data['request_date'],
			$data['service_date_time'],
			$data['maint_cost'],
			$data['status'],
			$data['doc_ref'],
			$data['description'],
			date('Y-m-d H:i:s'),
			$data['updated_by'],
			$data['asset_id'], 
			$data['maint_id']
			));
			
			$model			= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 				
			$action_desc	= 'a service record ('.$data['maint_id'].') has been updated. '.$change_text;	
			$action_category = 'update';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
	}

	function service_record_delete($asset_id='',$maint_id='')
	{
		$query_str = "DELETE FROM asset_maint WHERE asset_id=".$asset_id." AND maint_id=".$maint_id;
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a service record ('.$maint_id.') has been deleted.';
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}

	public function asset_update_planned($data) {
		$query = $this->db->query("SELECT * FROM `planned_asset_maint` WHERE asset_id = ? ", array($data['asset_id']));
		$row = $query->row_array();
		if (isset($row['asset_id'])) {
			$query_str = "UPDATE planned_asset_maint SET start_date = ?, interval_value = ?, interval_unit = ?, next_maint_date = ?, end_date = ? WHERE asset_id = ? ";	
			$this->db->query($query_str, array(
				$data['start_date'],
				$data['interval_value'],
				$data['interval_unit'],
				$data['next_maint_date'],
				$data['end_date'],
				$data['asset_id'] 
			));
		} else {
			$query_str = "INSERT INTO planned_asset_maint (asset_id, start_date, interval_value, interval_unit, next_maint_date, end_date) VALUES (
				?,?,?,?,?,? 
			)";	
			$this->db->query($query_str, array(
				$data['asset_id'],
				$data['start_date'],
				$data['interval_value'],
				$data['interval_unit'],
				$data['next_maint_date'],
				$data['end_date'] 
			));
		}
	}

	function transfer_record_insert($data)
	{
		$query_str = "INSERT INTO asset_transfer (asset_id, `from`, `to`, transfer_date, description, created_at, created_by) VALUES (
			?,?,?,?,?,?,? 
		)";	
		$this->db->query($query_str, array(
			$data['asset_id'],
			$data['from'],
			$data['to'],
			$data['transfer_date'],
			$data['description'],
			date('Y-m-d H:i:s'),
			$data['created_by'],
		));

		$transfer_id = $this->db->insert_id();
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new transfer record (Asset ID:'.$data['asset_id'].') has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	

		return $transfer_id;
	}

	function update_asset_site($asset_id, $site)
	{
		$this->db->query("UPDATE `asset` SET `site` = ? WHERE asset_id = ? ", array($site, $asset_id));
	}

	public function chk_existing_asset($asset_tag, $asset_id) {

		$found = false;

		$query = $this->db->query('SELECT asset_tag FROM asset WHERE asset_tag = ? AND asset_id != ? LIMIT 1', array(
			$asset_tag, 
			$asset_id
		));

		if (!empty($query->row_array())) {
			$found = true;
		}

		return $found;

	}

	public function get_service_record($asset_id) {
		$query2 = $this->db->query('SELECT am.* FROM asset_maint am WHERE asset_id = ? ORDER BY maint_id DESC', array($asset_id));
		$result = $query2->result_array();

		return $result;
	}

	public function get_transfer_record($asset_id) {
		$query2 = $this->db->query('
			SELECT at.*, sas1.site_name AS from_name, sas2.site_name AS to_name  
			FROM asset_transfer at 
			LEFT JOIN sys_asset_site sas1 ON (at.from = sas1.site_code) 
			LEFT JOIN sys_asset_site sas2 ON (at.to = sas2.site_code) 
			WHERE asset_id = ? ORDER BY transfer_id DESC', array($asset_id));
		$result = $query2->result_array();

		return $result;
	}

	public function get_upcoming_maint($txt_search='',$txt_date='',$page_item_no,$row_per_page='') {

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		$query_where = '';

		if (!empty($txt_search)) {
			$query_where .= " AND z.asset_name LIKE '%".$txt_search."%' ";
		}

		if (!empty($txt_date)){
			$query_where .= " AND ( z.next_maint <= '".date("Y-m-d",strtotime($txt_date))."' ) " ;			
		}

		$query_str= "
		SELECT COUNT(*) AS total_row FROM (
		select pam.*, a.asset_tag, a.asset_name, a.asset_status, 
		CASE 
			WHEN interval_unit = 'd' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value DAY) 
			WHEN interval_unit = 'm' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value MONTH)
			WHEN interval_unit = 'y' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value YEAR) 
		END AS next_maint 
		from planned_asset_maint pam LEFT JOIN `asset` a ON (pam.asset_id = a.asset_id) ) z 
		left join (SELECT am.maint_id, am.asset_id, am.request_date, am.service_date_time, am.status, CURDATE() FROM asset_maint am WHERE am.request_date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 30 DAY) amt on (amt.asset_id = z.asset_id ) 
		WHERE 1=1 AND z.next_maint < z.end_date AND z.asset_status = 1 " . $query_where;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query = $this->db->query("
		SELECT *, 
		CASE WHEN amt.status = 'P' then 1
		ELSE 0 END AS status_priority 
		FROM (
		select pam.*, a.asset_tag, a.asset_name, a.asset_status, 
		CASE 
			WHEN interval_unit = 'd' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value DAY) 
			WHEN interval_unit = 'm' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value MONTH)
			WHEN interval_unit = 'y' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value YEAR) 
		END AS next_maint 
		from planned_asset_maint pam LEFT JOIN `asset` a ON (pam.asset_id = a.asset_id) ) z 
		left join (SELECT am.maint_id, am.asset_id, am.request_date, am.service_date_time, am.status, CURDATE() FROM asset_maint am WHERE am.request_date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 30 DAY) amt on (amt.asset_id = z.asset_id ) 
		WHERE 1=1 AND z.next_maint < z.end_date AND z.asset_status = 1 " . $query_where." ORDER BY status_priority DESC, z.next_maint ASC, amt.request_date ASC LIMIT $page_item_no, ".$row_per_page);
		if ($query->num_rows() > 0){
			$return_val['row'] = $query->result_array();
		}
		return $return_val;
	}

	public function set_last_maint_date($asset_id, $datetime) {
		$this->db->query('UPDATE `planned_asset_maint` SET last_maint_date = ? WHERE asset_id = ? ', array($datetime, $asset_id));
	}

	public function set_next_maint_date($asset_id, $datetime) {
		$this->db->query('UPDATE `planned_asset_maint` SET next_maint_date = ? WHERE asset_id = ? ', array($datetime, $asset_id));
	}

	public function get_category_pic($category_idx) {
		$sql = "SELECT acs.*, u.username  
				FROM asset_contact_setting acs 
				LEFT JOIN `user` u ON (acs.user_id = u.idx) 
				WHERE acs.category_idx = ? ORDER BY acs.user_id";

		$query = $this->db->query($sql, array($category_idx));
		$result = $query->result_array();

		return $result;
	}

	public function delete_contacts_setting($category_idx) {
		$this->db->query("DELETE FROM `asset_contact_setting` WHERE category_idx = ?", array($category_idx));
	}

	public function save_contacts_setting($data) {
		$query_str = "INSERT INTO asset_contact_setting (category_idx, `user_id`, `phone`, email, telegram_id) VALUES (
			?,?,?,?,? 
		)";	
		$this->db->query($query_str, array(
			$data['category_idx'],
			$data['user_id'],
			$data['phone'],
			$data['email'],
			$data['telegram_id'] 
		));
	}

	public function get_asset_pic($asset_id) {
		$sql = "SELECT ac.*, u.username  
				FROM asset_contact ac 
				LEFT JOIN `user` u ON (ac.user_id = u.idx) 
				WHERE ac.asset_id = ? ORDER BY ac.user_id";

		$query = $this->db->query($sql, array($asset_id));
		$result = $query->result_array();

		return $result;
	}

	public function delete_contacts_setting_asset($asset_id) {
		$this->db->query("DELETE FROM `asset_contact` WHERE asset_id = ?", array($asset_id));
	}

	public function save_contacts_setting_asset($data) {
		$query_str = "INSERT INTO asset_contact (asset_id, `user_id`, `phone`, email, telegram_id) VALUES (
			?,?,?,?,? 
		)";	
		$this->db->query($query_str, array(
			$data['asset_id'],
			$data['user_id'],
			$data['phone'],
			$data['email'],
			$data['telegram_id'] 
		));
	}

	public function chk_service_record_close($asset_id) {
		$found = false;

		//search 30 days ago and 30 days in future

		$query = $this->db->query('SELECT *, CURDATE() FROM asset_maint WHERE service_date_time BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 30 DAY AND asset_id = ? LIMIT 1', array(
			$asset_id
		));

		if (!empty($query->row_array())) {
			$found = true;
		}

		return $found;
	}

	public function chk_pending_maint_record($asset_id) {
		$found = false;

		//search 30 days ago and 30 days in future

		$query = $this->db->query('SELECT * FROM asset_maint WHERE `status` = "P" AND asset_id = ? AND request_date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 30 DAY LIMIT 1', array(
			$asset_id
		));

		if (!empty($query->row_array())) {
			$found = true;
		}

		return $found;
	}

	public function has_notified_pic($asset_id) {
		$found = false;

		//search 30 days ago and 30 days in future

		$query = $this->db->query('SELECT *, CURDATE() FROM asset_notify_pic WHERE last_notified BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 30 DAY AND asset_id = ? LIMIT 1', array(
			$asset_id
		));

		if (!empty($query->row_array())) {
			$found = true;
		}

		return $found;
	}

	public function insert_notification_record($asset_id) {
		$query_str = "INSERT INTO asset_notify_pic (asset_id, `last_notified`) VALUES (
			?,?  
		)";	
		$this->db->query($query_str, array(
			$asset_id,
			date('Y-m-d'),
		));
	}

	public function get_assets_by_customer($acc_id)
	{
		$query_str = "SELECT a.*, sac.category_name, sas.site_name  
									FROM asset a 
									LEFT JOIN customer c ON (a.customer_no = c.customer_no) 
									LEFT JOIN profile p ON (c.profile_id = p.acc_id)  
									LEFT JOIN sys_asset_category sac ON (sac.category_idx = a.category_idx) 
									LEFT JOIN sys_asset_site sas ON (a.site = sas.site_code) 
									WHERE p.acc_id = ? 
									ORDER BY a.asset_tag ASC  ";
		$query = $this->db->query($query_str, array($acc_id));
		
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ;
		return $return_val;
	}

}