<?php

class Adjustment_model extends CI_Model{

	public function __construct()
	{
		parent::__construct();
	}

	public function get_adj_total_row($txt_search, $txt_date_start, $txt_date_end, $query_where)
	{

		$txt_search = $this->db->escape_str($txt_search);
		
		if ( !empty($txt_date_start) ) {
			$query_where .= " AND ba.tranx_date >= '$txt_date_start' ";
		}
		if ( !empty($txt_date_end) ) {
			$query_where .= " AND ba.tranx_date <= '$txt_date_end' ";
		}

		$query_str = "SELECT count(ba.adj_no) AS total_row 
					FROM bill_adjustment ba 
					LEFT JOIN customer c ON ba.customer_no = c.customer_no 
					LEFT JOIN building b On ba.building = b.building_no 
					LEFT JOIN area a On ba.area = a.id 
					WHERE ( c.name LIKE '%$txt_search%' " . 
						"OR b.name LIKE '%$txt_search%' " .
						"OR c.customer_no LIKE '%$txt_search%' " .
						"OR a.name LIKE '%$txt_search%' " .
						") " .
					$query_where;
		$query = $this->db->query($query_str);
		$total_row = $query->row(0)->total_row;

		return $total_row;
	}

	public function get_adj_listing($txt_search, $txt_date_start, $txt_date_end, $query_where, $page_item_no)
	{

		$txt_search = $this->db->escape_str($txt_search);
		
		if ( !empty($txt_date_start) ) {
			$query_where .= " AND ba.tranx_date >= '$txt_date_start' ";
		}
		if ( !empty($txt_date_end) ) {
			$query_where .= " AND ba.tranx_date <= '$txt_date_end' ";
		}

		$query_str = "SELECT 
						ba.adj_no,
						ba.adjust_by,
						ba.tranx_date,
						ba.amount,
						ba.adjust_type,
						ba.bill_no,
						bas.status,

						(ba.approval_required = 1 AND EXISTS (
							SELECT 1
							FROM docs_approver da
							WHERE da.doc_ref = ba.adj_no
								AND da.doc_type = 'ba'
								AND da.user_id = ?
								AND da.level = CASE
									WHEN bas.status = 'B' THEN 2
									WHEN bas.status = 'A' THEN 1
									ELSE 0
								END
						)) AS is_approver,

						CASE
							WHEN ba.adjust_by = 'i' THEN 'Individual'
							WHEN ba.adjust_by = 'b' THEN 'Building'
							WHEN ba.adjust_by = 'a' THEN 'Area'
							ELSE ''
						END AS adjust_by_name,

						CASE
							WHEN ba.adjust_by = 'i' THEN c.name
							WHEN ba.adjust_by = 'b' THEN b.name
							WHEN ba.adjust_by = 'a' THEN a.name
							ELSE ''
						END AS customer_name,

						ba.customer_no

					FROM bill_adjustment ba
					LEFT JOIN customer c
						ON ba.customer_no = c.customer_no
					LEFT JOIN building b
						ON ba.building = b.building_no
					LEFT JOIN area a
						ON ba.area = a.id
					LEFT JOIN bill_adjustment_status bas
						ON bas.id = (
							SELECT MAX(bas2.id)
							FROM bill_adjustment_status bas2
							WHERE bas2.adj_no = ba.adj_no
						)

					WHERE (
						c.name LIKE '%$txt_search%'
						OR b.name LIKE '%$txt_search%'
						OR c.customer_no LIKE '%$txt_search%'
						OR a.name LIKE '%$txt_search%'
					)
					" . $query_where . "
					ORDER BY ba.adj_no DESC
					LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];

		$query = $this->db->query($query_str, array($this->session->userdata("user")['idx'] ?? 0));
		if ($query->num_rows() > 0)	{
			$row = $query->result_array();
		}
		else {
			$row = array();
		}

		return $row;
	}

	public function get_adj($adj_no = '')
	{
		$return_val = [
			'adj_info' 				=> [],
			'doc_lvl_1_approver' 	=> [],
			'doc_lvl_2_approver' 	=> []
		];

		$query = $this->db->query("SELECT ba.adj_no, ba.adjust_by, ba.customer_no, ba.building, ba.area, ba.tranx_date, 
							ba.bill_type, ba.adjust_type, ba.amount, ba.remark, ba.is_lock, ba.approval_required, bas.status,
							c.name as customer_name
							FROM bill_adjustment ba 
							LEFT JOIN customer c ON ba.customer_no = c.customer_no 
							LEFT JOIN building b ON ba.building = b.building_no 
							LEFT JOIN area a ON ba.area = a.id  
							LEFT JOIN bill_adjustment_status bas ON bas.id = (
								SELECT MAX(bas2.id)
								FROM bill_adjustment_status bas2
								WHERE bas2.adj_no = ba.adj_no
							)
							WHERE ba.adj_no='".$this->db->escape_str($adj_no)."'
							LIMIT 1");

		if ($query->num_rows() > 0)	{
			$return_val['adj_info'] = $query->row_array();
			
			$return_val['adj_info']['txt_search_autocomplete']='';
			$return_val['adj_info']['btn_delete']='enabled';

			// Approvers
			$return_val['doc_lvl_1_approver'] = $this->db
				->query("SELECT user_id FROM docs_approver WHERE level = 1 AND doc_type = 'ba' AND doc_ref = ?", [$adj_no])
				->result_array();

			$return_val['doc_lvl_2_approver'] = $this->db
				->query("SELECT user_id FROM docs_approver WHERE level = 2 AND doc_type = 'ba' AND doc_ref = ?", [$adj_no])
				->result_array();
		}
		else {
			$return_val['adj_info']['adjust_by'] = 'i';
			$return_val['adj_info']['adj_no'] = '';
			$return_val['adj_info']['txt_search_autocomplete'] = '';
			$return_val['adj_info']['customer_no'] = '';
			$return_val['adj_info']['customer_name'] = '';
			$return_val['adj_info']['building'] = '';
			$return_val['adj_info']['area'] = 0;
			$return_val['adj_info']['tranx_date'] = date('Y-m-d');
			$return_val['adj_info']['bill_type'] = '1';
			$return_val['adj_info']['adjust_type'] = 'cr';
			$return_val['adj_info']['amount'] = '0';
			$return_val['adj_info']['remark'] = '';
			$return_val['adj_info']['is_lock'] = '';
			$return_val['adj_info']['status'] = 'D';
			$return_val['adj_info']['approval_required'] = 0;
			
			$return_val['adj_info']['btn_delete']='disabled';
		}

		return $return_val;
	}

	public function update_adj($input_post)
	{
		
		if( !isset($input_post['building']) || $input_post['building'] == '' ) $input_post['building'] = 0 ;
		$sess =  $this->session->userdata; 

		$change_text = '';

		$adjustment 	= $this->get_adj( $input_post['adj_no'] );
		$adjustmentInfo = $adjustment['adj_info'];

		if (!empty($adjustmentInfo)) {

			$this->load->model('common_model');

			$bill_type_arr = array();
			$bill_type_list = $this->common_model->get_bill_type_list();
			foreach ($bill_type_list as $bill_type_rec) {
				$bill_type_arr[$bill_type_rec['bill_type_id']] = $bill_type_rec['name'];
			}

			if(empty($input_post['customer_no'])) {
				$input_post['customer_no'] = 0;
			}

			if(empty($input_post['building'])) {
				$input_post['building'] = 0;
			}

			if(empty($input_post['area'])) {
				$input_post['area'] = 0;
			}

			if ($adjustmentInfo['customer_no'] != $input_post['customer_no']) {
				$change_text .= compare_field_change('Customer No.', $adjustmentInfo['customer_no'], $input_post['customer_no'], [], true, $this->db);
			}

			if ($adjustmentInfo['tranx_date'] != $input_post['tranx_date']) {
				$change_text .= compare_field_change('Transaction Date', $adjustmentInfo['tranx_date'], $input_post['tranx_date'], [], true, $this->db);
			}

			try {
				if ($adjustmentInfo['bill_type'] != $input_post['bill_type']) {
					$change_text .= compare_field_change('Bill Type', $adjustmentInfo['bill_type'], $input_post['bill_type'], $bill_type_arr, true, $this->db);
				}
			} catch (Exception $e) {
				log_message('error', 'Adjustment Edit: '.$e->getMessage());
			}

			if ($adjustmentInfo['amount'] != $input_post['amount']) {
				$change_text .= compare_field_change('Amount', $adjustmentInfo['amount'], $input_post['amount'], [], true, $this->db);
			}

			$area_arr = [];
			$area_list = $this->common_model->get_area_list();
			if($adjustmentInfo['area'] != $input_post['area']) {
				foreach ($area_list as $key => $area) {
					$area_arr[$area['id']] = $area['name'];
				}
				if($adjustmentInfo['area'] != 0 && $input_post['area'] != 0) {
					$change_text .= compare_field_change('Area', $adjustmentInfo['area'], $input_post['area'], $area_arr, true, $this->db);

					$change_text .= $area_text;
				} else if($area_id != 0) {
					$area_text = 'Area had been added ' . $area_arr[$input_post['area']];

					$change_text .= $area_text;
				}
			} 

		}

		$query_str = "UPDATE bill_adjustment SET " .
					"adjust_by = '" . $this->db->escape_str($input_post['adjust_by']) . "', " .
					"customer_no = '" . $this->db->escape_str($input_post['customer_no'] ?? 0) . "', " .
					"building = '" . $this->db->escape_str($input_post['building']) . "', " .
					"area = '" . $this->db->escape_str($input_post['area']) . "', " .
					"tranx_date = '" . $this->db->escape_str($input_post['tranx_date']) . "', " .
					"bill_type = '" . $this->db->escape_str($input_post['bill_type']) . "', " .
					"adjust_type = '" . $this->db->escape_str($input_post['adjust_type']) . "', " .
					"amount = '" . $this->db->escape_str($input_post['amount']) . "', " .
					"remark = '" . $this->db->escape_str($input_post['remark']) . "', " .
					"modified_by = '" . $sess['user']['username'] . "', " .
					"modified_date = now() " .
					"WHERE adj_no = ' " . $this->db->escape_str($input_post['adj_no']) . "' ";
		
		// approver
		if (!empty($input_post['approval_required'])) {
			$this->db->delete('docs_approver', ['doc_type' => 'ba', 'doc_ref' => $input_post['adj_no']]);

			if (!empty($input_post['lvl1_approver'])) {
				foreach ($input_post['lvl1_approver'] as $approver) {
					$data = [
						'doc_type' => 'ba',
						'doc_ref' => $input_post['adj_no'],
						'level' => 1,
						'user_id' => $approver
					];
					$this->db->insert('docs_approver', $data);
				}
			}

			if (!empty($input_post['lvl2_approver'])) {
				foreach ($input_post['lvl2_approver'] as $approver) {
					$data = [
						'doc_type' => 'ba',
						'doc_ref' => $input_post['adj_no'],
						'level' => 2,
						'user_id' => $approver
					];
					$this->db->insert('docs_approver', $data);
				}
			}
		}

		$task = $input_post['task'];
		$userdata = $this->session->userdata;
		$user_display = (!empty($userdata)) ? ' by ' . $userdata['user']['display_name'] : '';

		switch ($task) {
			case 'btSendForReview':
				$action_log_desc = ' and sent for review' . $user_display;
				break;
			case 'btApprove':
				$action_log_desc = ' and approved (Level ' . $input_post['changed_status'] == 'B'?'1':'2' .')'. $user_display;
				break;
			case 'btReject':
				$action_log_desc = ' and rejected' . $user_display;
				break;
		}

		if ($adjustmentInfo['status'] != $input_post['changed_status']) {
			$data = [
				"adj_no" 		=> $input_post['adj_no'],
				"status"  		=> $input_post['changed_status'],
				"date"  		=> date('Y-m-d H:i:s'),
				"created_on"  	=> date('Y-m-d H:i:s'),
				"created_by"	=> $userdata['user']['idx']
			];
			$this->db->insert('bill_adjustment_status', $data);
		}

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 		
		$action_desc	= "Adjustment #".$this->db->escape_str($input_post['adj_no'])." has been updated. ".$change_text;
		$action_category =  'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$input_post['customer_no'] ?? 0);		

		$this->db->query($query_str);

		if (!empty($customer_no)) {
			$customer_action_model			= 'customer_action_log_model';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$input_post['customer_no'] ?? 0);
		}

		// comment it cause this function will update the customer next_bill_date when adjustment tranx_date is made after CRONJOB_DATE
		if( $this->db->affected_rows() ){
			// $this->load->model('customer_model');	
			// $customer = $this->customer_model->get_customer($input_post['customer_no'] ?? 0);
			// $tranx_date = $input_post['tranx_date'];
			// $next_bill_date = date( 'Y-m-01' , strtotime( " +1 month " , strtotime( $tranx_date ) ) ) ;
			// $cronjob_date = CRONJOB_DATE;
			
			
			// if( ( strtotime($tranx_date) > strtotime($cronjob_date) 
			// 	&& strtotime($next_bill_date) > strtotime($customer['next_bill_date']) )
			// 	|| ( $customer['next_bill_date'] == '' || $customer['next_bill_date'] == '0000-00-00' )
			// )
			// {
			// 	$this->customer_model->customer_update_next_bill_date($input_post['customer_no'],$next_bill_date);
			// }
		}

		return true;
	}

	public function delete_adj($adj_no, $customer_no=0)
	{
		$query_str = "DELETE FROM bill_adjustment WHERE adj_no=".$adj_no;
		$this->db->query($query_str);
		
		$model					= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);
		$method 		= $this->router->method; 
		$action_desc	= 'an adjustment has been deleted';
		$action_category =  'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);			
			
		if (!empty($customer_no)) {
			$customer_action_model			= 'customer_action_log_model';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
		}

		return true;
	}

	function create_prorated_bill($customer_no, $monthly_charge, $activated_date, $suspended_date, $terminated_date)
	{
		if (empty($activated_date)) return;

		$end_date = ($suspended_date && $suspended_date != '0000-00-00') ? $suspended_date :
					(($terminated_date && $terminated_date != '0000-00-00') ? $terminated_date : '');

		$month_start = new DateTime(date('Y-m-01', strtotime($activated_date)));
		$month_end   = new DateTime($end_date ?: date('Y-m-t', strtotime($activated_date)));

		if (strtotime($activated_date) < strtotime($month_end->format('Y-m-d'))) {
			$total_days      = $month_start->format('t');
			$used_days       = $month_end->diff(new DateTime($activated_date))->days + 1;
			$amount          = $monthly_charge / $total_days * $used_days;
			$remark          = "Prorated Bill From $activated_date UNTIL " . $month_end->format('Y-m-d');

			$this->insert_adj('i', $customer_no, '', '', $activated_date, '41', 'dr', $amount, $remark, 1);
		}
	}

	public function delete_prorate_bill($customer_no)
	{
		$query_str = "
			DELETE FROM bill_adjustment
			WHERE adj_no = (
				SELECT adj_no
				FROM (
					SELECT adj_no
					FROM bill_adjustment
					WHERE customer_no = '".$this->db->escape_str($customer_no)."'
					AND bill_type = '41'
					AND adjust_type = 'dr'
					AND remark LIKE 'Prorated Bill From%'
					ORDER BY adj_no DESC
					LIMIT 1
				) x
			)
		";

		$model = 'action_log_model';
		$this->load->model($model);

		$ctrl = $this->router->fetch_class();
		$method = $this->router->method;
		$esc_query_str = $this->db->escape_str($query_str);

		$action_desc = 'Deleted prorated bill adjustment';
		$action_category = 'delete';

		$this->$model->save_action(
			$ctrl,
			$method,
			$esc_query_str,
			$action_desc,
			$action_category,
			$customer_no
		);

		$this->db->query($query_str);

		return $this->db->affected_rows();
	}

	public function insert_adj($adjust_by, $customer_no, $building, $area, $tranx_date, $bill_type, $adjust_type, $amount, $remark, $is_autogen=0, $lvl1_approvers = [], $lvl2_approvers = [], $approval_required = null)
	{
		if ( !empty($customer_no) || !empty($building) || !empty($area) ) {
			
			if( $building == '' ) $building = 0 ;
			if( $area == '' ) $area = 0 ;
			
			$query_str = "SELECT MAX(ba.adj_no) as adj_no FROM bill_adjustment ba";
			$query = $this->db->query($query_str);
			$adj_no = $query->row()->adj_no + 1;
			
			//~ _debug_array($this->session->userdata);exit;
			$sess =  $this->session->userdata;

			if ($is_autogen == 1) {
				$approval_required = 0;
			} else if ($approval_required === null) {
				$config = $this->db->query("SELECT val FROM sys_config WHERE category = 'bills' AND `key` = 'adjustment_approval' LIMIT 1")->row_array();
				$approval_required = (int)$config['val'];
			} else {
				$approval_required = (int)$approval_required;
			}

			$query_str = "INSERT INTO bill_adjustment (adj_no, adjust_by, customer_no, building, area, tranx_date, bill_type, adjust_type, amount, remark, is_autogen, approval_required, created_by, created_date, modified_by, modified_date) VALUES ( " .
						"'" . $this->db->escape_str($adj_no) . "', " .
						"'" . $this->db->escape_str($adjust_by) . "', " .
						"'" . $this->db->escape_str($customer_no) . "', " .
						"'" . $this->db->escape_str($building) . "', " .
						"'" . $this->db->escape_str($area) . "', " .
						"'" . $this->db->escape_str($tranx_date) . "', " .
						"'" . $this->db->escape_str($bill_type) . "', " .
						"'" . $this->db->escape_str($adjust_type) . "', " .
						"'" . $this->db->escape_str($amount) . "', " .
						"'" . $this->db->escape_str($remark) . "', " .
						"'" . $is_autogen . "', " .
						"'" . $approval_required . "', " .
						"'" . $sess['user']['username'] . "', now(), '" . $sess['user']['username'] . "', now() )";

			// approver
			if ($approval_required == 1) {
				$this->db->delete('docs_approver', ['doc_type' => 'ba', 'doc_ref' => $adj_no]);

				if (!empty($lvl1_approvers)) {
					foreach ($lvl1_approvers as $approver) {
						$data = [
							'doc_type' => 'ba',
							'doc_ref' => $adj_no,
							'level' => 1,
							'user_id' => $approver
						];
						$this->db->insert('docs_approver', $data);
					}
				}

				if (!empty($lvl2_approvers)) {
					foreach ($lvl2_approvers as $approver) {
						$data = [
							'doc_type' => 'ba',
							'doc_ref' => $adj_no,
							'level' => 2,
							'user_id' => $approver
						];
						$this->db->insert('docs_approver', $data);
					}
				}
			}

			// status
			$status = ($approval_required == 1) ? 'D' : 'C';
			$this->db->insert('bill_adjustment_status', [
				"adj_no" => $adj_no,
				"status"  => $status,
				"date"  => date('Y-m-d H:i:s'),
				"created_on"  => date('Y-m-d H:i:s'),
			]);

			$model					= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 		
			$action_desc	= 'New adjustment #'.$this->db->escape_str($adj_no).' has been added';
			$action_category =  'insert';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$customer_no);
			$this->db->query($query_str);

			if (!empty($customer_no)) {
				$customer_action_model			= 'customer_action_log_model';
				$this->load->model($customer_action_model);
				$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
			}
			
			return $adj_no;
		}
	}

	function get_sys_approvers($level, $doc_type = 'ba')
	{
		$query = "SELECT sa.`user_id`, u.`display_name`, u.`allow_whatsapp`, u.`allow_telegram` FROM `sys_approver` sa LEFT JOIN `user` u ON sa.`user_id` = u.`idx` WHERE sa.`level` = ? AND sa.`doc_type` = ?";
		$query = $this->db->query($query, [$level, $doc_type]);
		$return_val = $query->num_rows() > 0 ? $query->result_array() : [];
		
		return $return_val;
	}
}
