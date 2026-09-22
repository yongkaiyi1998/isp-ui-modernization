<?php

class Dealer_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	//~ function dealer_update($package_no){}
	//~ function dealer_update($package_no){}
	//~ function dealer_update($package_no){}
	function dealer_comm_insert($username,$dealer_no)
	{
		$comm_type = $this->input->post('comm_type') ?? [];
		$monthly = $this->input->post('monthly') ?? [];
		$monthly_times = $this->input->post('monthly_times') ?? [];
		$onetime = $this->input->post('onetime') ?? [];
		$follow_package = $this->input->post('follow_package') ?? [];

		$query_str = "SELECT * FROM dealer_comm WHERE dealer_no = ".$this->db->escape($dealer_no);
		$result = $this->db->query($query_str)->result_array();

		$old_comm_type = array_column($result, 'comm_type', 'package');
		$old_monthly = array_column($result, 'monthly', 'package');
		$old_monthly_times = array_column($result, 'monthly_times', 'package');
		$old_onetime = array_column($result, 'onetime', 'package');
		$old_follow_package = array_column($result, 'follow_package', 'package');

		$changes = [];

		foreach ($comm_type as $package => $new_comm_type) {

			$old = [
				'comm_type' => $old_comm_type[$package] ?? null,
				'monthly' => $old_monthly[$package] ?? null,
				'monthly_times' => $old_monthly_times[$package] ?? null,
				'onetime' => $old_onetime[$package] ?? null,
				'follow_package' => $old_follow_package[$package] ?? null,
			];

			$new = [
				'comm_type' => $new_comm_type,
				'monthly' => $monthly[$package] ?? null,
				'monthly_times' => $monthly_times[$package] ?? null,
				'onetime' => $onetime[$package] ?? null,
				'follow_package' => isset($follow_package[$package]) ? 1 : 0,
			];

			foreach ($new as $field => $new_value) {
				if ((string)$old[$field] !== (string)$new_value) {
					$changes[] = [
						'dealer' => $dealer_no,
						'package' => $package,
						'field' => $field,
						'old' => $old[$field],
						'new' => $new_value
					];
				}
			}
		}

		$change_desc = empty($changes) ? 'No changes detected' : json_encode($changes);

		$this->db->query("DELETE FROM dealer_comm WHERE dealer_no = ".$this->db->escape($dealer_no));

		$dealer = $this->db->query("SELECT name FROM dealer WHERE dealer_no = ?", [$dealer_no])->row_array();

		$query_str = "INSERT INTO dealer_comm (dealer_no, package, comm_type, monthly, monthly_times, onetime, follow_package, created_by, created_date) VALUES ";
		$total = count($comm_type);
		$count = 1;

		foreach ($comm_type as $key => $val) {
			$query_str .= "(".
				$this->db->escape($dealer_no).",".
				$this->db->escape($key).",".
				$this->db->escape($val).",".
				$this->db->escape(normalize_input($monthly[$key] ?? null, 'int', 0)).",".
				$this->db->escape(normalize_input($monthly_times[$key] ?? null, 'int', 1)).",".
				$this->db->escape(normalize_input($onetime[$key] ?? null, 'float', 0.00)).",".
				$this->db->escape(isset($follow_package[$key]) ? 1 : 0).",".
				$this->db->escape($username).",NOW())";

			$query_str .= ($count < $total) ? ',' : ';';
			$count++;
		}

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $change_desc;
		$method = $this->router->method;
		$action_desc = $this->user['display_name'] . " updated dealer (".$dealer['name'].") commission rate.";
		$action_category = 'insert';
		$cust_no = '';
		$this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);

		if (!empty($comm_type)) {
			$this->db->query($query_str);
		}
	}
	
	function dealer_update($username)
	{
		$dealer_no = $this->input->post('dealer_no');
		$dealerInfo = $this->get_dealer($dealer_no);

		$building = !empty($this->input->post('building'))
						? $this->db->escape_str(implode(',', (array)$this->input->post('building'))) . ','
						: '';

		$building = $this->input->post('upline') != 0 ? '' : $building;

		$monthly_pct_deduct = normalize_input($this->input->post('monthly_pct_deduct'), 'int', 0);
		$one_time_amt_deduct = normalize_input($this->input->post('one_time_amt_deduct'), 'float', 0.00);

		$is_commission_set = 0;

		// EITHER ONE IS SET GREATER THAN 0
		if ($monthly_pct_deduct > 0 || $one_time_amt_deduct > 0) {
			$is_commission_set = 1;
		}

		$date_of_birth = ($tmp = $this->db->escape_str($this->input->post('date_of_birth'))) ? "'$tmp'" : "NULL";
		$signup_date = ($tmp = $this->db->escape_str($this->input->post('signup_date'))) ? "'$tmp'" : "NULL";
		$terminated_date = ($tmp = $this->db->escape_str($this->input->post('terminated_date'))) ? "'$tmp'" : "NULL";
		$building = ($tmp = $this->db->escape_str($building)) ? "'$tmp'" : "NULL";

		$change_text = '';

		if (!empty($dealerInfo)) {

			$this->load->model('common_model');

			$status_arr = array_column($this->common_model->get_acc_status_list() ?? [], 'name', 'status_code');
			$state_arr = array_column($this->common_model->get_state_list() ?? [], 'name', 'state_code');

			$change_text .= compare_field_change('Name', $dealerInfo['name'], $this->input->post('name'), [], true, $this->db);
			$change_text .= compare_field_change('Company No.', $dealerInfo['reg_no'], $this->input->post('reg_no'), [], true, $this->db);
			$change_text .= compare_field_change('PIC', $dealerInfo['pic_name'], $this->input->post('pic_name'), [], true, $this->db);
			$change_text .= compare_field_change('Status', $dealerInfo['status'], $this->input->post('status'), $status_arr, true, $this->db);
			$change_text .= compare_field_change('Address Line 1', $dealerInfo['addr1'], $this->input->post('addr1'), [], true, $this->db);
			$change_text .= compare_field_change('Address Line 2', $dealerInfo['addr2'], $this->input->post('addr2'), [], true, $this->db);
			$change_text .= compare_field_change('Address Line 3', $dealerInfo['addr3'], $this->input->post('addr3'), [], true, $this->db);
			$change_text .= compare_field_change('City', $dealerInfo['city'], $this->input->post('city'), [], true, $this->db);
			$change_text .= compare_field_change('Postcode', $dealerInfo['postcode'], $this->input->post('postcode'), [], true, $this->db);
			$change_text .= compare_field_change('State', $dealerInfo['state'], $this->input->post('state'), $state_arr, true, $this->db);
			$change_text .= compare_field_change('Tel Num', $dealerInfo['tel_num'], $this->input->post('tel_num'), [], true, $this->db);
			$change_text .= compare_field_change('Fax Num', $dealerInfo['fax_num'], $this->input->post('fax_num'), [], true, $this->db);
			$change_text .= compare_field_change('Email', $dealerInfo['email'], $this->input->post('email'), [], true, $this->db);
			$change_text .= compare_field_change('NRIC', $dealerInfo['nric'], $this->input->post('nric'), [], true, $this->db);
			$change_text .= compare_field_change('Passport', $dealerInfo['passport'], $this->input->post('passport'), [], true, $this->db);
			$change_text .= compare_field_change('DOB', $dealerInfo['date_of_birth'], $this->input->post('date_of_birth'), [], true, $this->db);
			$change_text .= compare_field_change('Gender', $dealerInfo['gender'], $this->input->post('gender'), [], true, $this->db);
			$change_text .= compare_field_change('Signup Date', $dealerInfo['signup_date'], $this->input->post('signup_date'), [], true, $this->db);
			$change_text .= compare_field_change('Terminated Date', $dealerInfo['terminated_date'], $this->input->post('terminated_date'), [], true, $this->db);
			$change_text .= compare_field_change('Remark', $dealerInfo['remark'], $this->input->post('remark'), [], true, $this->db);
			$change_text .= compare_field_change('Upline', $dealerInfo['upline'], $this->input->post('upline'), [], true, $this->db);
			$change_text .= compare_field_change('Monthly Percentage Deduction', $dealerInfo['monthly_pct_deduct'], $monthly_pct_deduct, [], true, $this->db);
			$change_text .= compare_field_change('One Time Amount Deduction', $dealerInfo['one_time_amt_deduct'], $one_time_amt_deduct, [], true, $this->db);
			$change_text .= compare_field_change('Building', $dealerInfo['building'], $building, [], true, $this->db);

			$login_text = compare_field_change('Login ID', $dealerInfo['login'], $this->input->post('login'), [], true, $this->db);
			$login_changed = $login_text !== '' ? ($change_text .= $login_text) || 1 : 0;
		}

		$query_str = "UPDATE dealer SET " .
					"name = '" . $this->db->escape_str($this->input->post('name')) . "', " .
					"reg_no = '" . $this->db->escape_str($this->input->post('reg_no')) . "', " .
					"pic_name = '" . $this->db->escape_str($this->input->post('pic_name')) . "', " .
					"status = '" . $this->db->escape_str($this->input->post('status')) . "', " .
					"dealer_type = '" . $this->db->escape_str($this->input->post('dealer_type')) . "', " .
					"addr1 = '" . $this->db->escape_str($this->input->post('addr1')) . "', " .
					"addr2 = '" . $this->db->escape_str($this->input->post('addr2')) . "', " .
					"addr3 = '" . $this->db->escape_str($this->input->post('addr3')) . "', " .
					"city = '" . $this->db->escape_str($this->input->post('city')) . "', " .
					"postcode = '" . $this->db->escape_str($this->input->post('postcode')) . "', " .
					"state = '" . $this->db->escape_str($this->input->post('state')) . "', " .
					"tel_num = '" . $this->db->escape_str($this->input->post('tel_num')) . "', " .
					"fax_num = '" . $this->db->escape_str($this->input->post('fax_num')) . "', " .
					"mobile_num = '" . $this->db->escape_str($this->input->post('mobile_num')) . "', " .
					"email = '" . $this->db->escape_str($this->input->post('email')) . "', " .
					"nric = '" . $this->db->escape_str($this->input->post('nric')) . "', " .
					"passport = '" . $this->db->escape_str($this->input->post('passport')) . "', " .
					"date_of_birth = " . $date_of_birth . ", " .
					"gender = '" . $this->db->escape_str($this->input->post('gender')) . "', " .
					"signup_date = " . $signup_date . ", " .
					"terminated_date = " . $terminated_date . ", " .
					"monthly_pct_deduct = '" . $this->db->escape_str($monthly_pct_deduct) . "', " .
					"one_time_amt_deduct = '" . $this->db->escape_str($one_time_amt_deduct) . "', " .
					"remark = '" . $this->db->escape_str($this->input->post('remark')) . "', " .
					"login = '" . $this->db->escape_str($this->input->post('login')) . "', " .
					"is_commission_set = '" . $is_commission_set . "', " .
					"building = " . $building . ", " .
					"upline = '" . $this->db->escape_str($this->input->post('upline')) . "', " .
					"upline_tree = '" . $this->generate_upline_tree($this->input->post('upline'), $this->input->post('dealer_no')) . "', " .
					"modified_by = '" . $username . "', " .
					"modified_date = now() " .
					"WHERE dealer_no = '" . $this->db->escape_str($this->input->post('dealer_no')) . "' ";

		$model = 'action_log_model';
		$this->load->model($model);

		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = 'dealer no('.$this->input->post('dealer_no').') has been updated.'.$change_text;
		$action_category = 'update';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category, $cust_no);

		// change login id if got change
		if ($login_changed == 1) {
			if (!empty($dealerInfo['acc_id'])) {
				$this->load->model('profile_model');
				$this->profile_model->change_login_id($dealerInfo['acc_id'], $this->input->post('login'));
			}
		}

		$post_password = $this->input->post('password');

		if (!empty($post_password)) {
			$this->load->model('profile_model');
			$this->profile_model->change_password($dealerInfo['acc_id'], $post_password);
		}

		if ($dealerInfo['status'] != $this->input->post('status')) {
			$this->load->model('profile_model');
			$this->profile_model->update_dealer_profile_acc_status($dealerInfo['acc_id'], $this->input->post('status'));
		}

		$this->db->query($query_str);
	}
	
	function dealer_insert($username)
	{
		$query = $this->db->select_max('dealer_no')->get('dealer');

		$dealer_no = (int)$query->row()->dealer_no + 1;

		$building = !empty($this->input->post('building'))
						? implode(',', (array)$this->input->post('building')) . ','
						: '';

		$building = $this->input->post('upline') != 0 ? '' : $building;

		$monthly_pct_deduct = normalize_input( $this->input->post('monthly_pct_deduct'), 'int', 0 );
		$one_time_amt_deduct = normalize_input( $this->input->post('one_time_amt_deduct'), 'float', 0.00 );

		$is_commission_set = 0;

		// EITHER ONE IS SET GREATER THAN 0
		if ($monthly_pct_deduct > 0 || $one_time_amt_deduct > 0) {
			$is_commission_set = 1;
		}

		$data = array(
			'dealer_no'            			=> $dealer_no,
			'name'                 			=> $this->input->post('name'),
			'reg_no'               			=> $this->input->post('reg_no'),
			'pic_name'             			=> $this->input->post('pic_name'),
			'status'               			=> $this->input->post('status'),
			'dealer_type'          			=> $this->input->post('dealer_type'),
			'addr1'                			=> $this->input->post('addr1'),
			'addr2'                			=> $this->input->post('addr2'),
			'addr3'                			=> $this->input->post('addr3'),
			'city'                 			=> $this->input->post('city'),
			'postcode'             			=> $this->input->post('postcode'),
			'state'                			=> $this->input->post('state'),
			'tel_num'              			=> $this->input->post('tel_num'),
			'fax_num'              			=> $this->input->post('fax_num'),
			'mobile_num'           			=> $this->input->post('mobile_num'),
			'email'                			=> $this->input->post('email'),
			'nric'                 			=> $this->input->post('nric'),
			'passport'             			=> $this->input->post('passport'),
			'date_of_birth'        			=> $this->input->post('date_of_birth') ?: NULL,
			'gender'               			=> $this->input->post('gender'),
			'signup_date'          			=> $this->input->post('signup_date') ?: NULL,
			'terminated_date'      			=> $this->input->post('terminated_date') ?: NULL,
			'monthly_pct_deduct'   			=> $monthly_pct_deduct,
			'one_time_amt_deduct'  			=> $one_time_amt_deduct,
			'remark'						=> $this->input->post('remark'),
			'building'             			=> $building ?: NULL,
			'upline'               			=> $this->input->post('upline'),
			'upline_tree'          			=> $this->generate_upline_tree($this->input->post('upline'), $dealer_no),
			'login'                			=> $this->input->post('login'),
			'is_commission_set'				=> $is_commission_set,
			'created_by'           			=> $username,
			'created_date'         			=> date('Y-m-d H:i:s')
		);

		$this->db->insert('dealer', $data);
		
		$model = 'action_log_model';
		$this->load->model($model);

		$ctrl = $this->router->fetch_class();
		$method = $this->router->method;
		$action_desc = 'a new dealer ('.$this->input->post('name').') has been added';
		$action_category = 'insert';
		$cust_no = '';
		$esc_query_str = $this->db->escape_str($this->db->last_query());
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category, $cust_no);

		return $dealer_no;
	}
	
	
	function dealer_delete($dealer_no)
	{
		$query_str = "DELETE FROM dealer WHERE dealer_no=".$dealer_no;			
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a dealer has been deleted';	
		$cust_no		= '';//$customer_no; 
		$action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);	
			
		$this->db->query($query_str);
	}
	
	function get_dealer_comm($dealer_no)
	{
		$upline = $this->db
			->query('SELECT upline_tree FROM dealer WHERE dealer_no = ?', [$dealer_no])
			->row_array();

		$tree_array = array_filter(explode(',', $upline['upline_tree']));
		$effective_dealer_no = !empty($tree_array) ? array_values($tree_array)[0] : null;

		$effective_building = $this->db
			->query('SELECT building FROM dealer WHERE dealer_no = ?', [$effective_dealer_no])
			->row_array();

		$building_str = $effective_building['building'] ?? '';
		$buildings = array_filter(array_map('trim', explode(',', $building_str)));

		$building_conditions = empty($buildings)
			? ['1=1']
			: array_merge(
				['p.building IS NULL'],
				array_map(
					fn($b) => "CONCAT(',', p.building, ',') LIKE " . $this->db->escape('%,' . $b . ',%'),
					$buildings
				)
			);

		$query = "
			SELECT p.package_no, p.name, p.category
			FROM package p
			WHERE (" . implode(' OR ', $building_conditions) . ")
			ORDER BY p.category, p.package_no
		";

		$dealer_comm_map = $this->get_dealer_comm_array_map($dealer_no);

		$result = [];
		foreach ($this->db->query($query)->result_array() as $p) {
			$comm = $dealer_comm_map['dealer_comm'][$p['package_no']] ?? [];

			$result[$p['category']][] = [
				'package_no'      => $p['package_no'],
				'name'            => $p['name'],
				'comm_type'       => $comm['comm_type'] ?? 'm',
				'monthly'         => $comm['monthly'] ?? 0,
				'onetime'         => $comm['onetime'] ?? 0,
				'follow_package'  => $comm['follow_package'] ?? 0,
				'monthly_times'   => $comm['monthly_times'] ?? 0,
			];
		}

		return $result;
	}

	public function get_dealer_comm_array_map($dealer_no = '', $package_no = '', $force_calc_all = false)
	{
		$packages     = $this->db->query("SELECT package_no,name,dealer_comm_type,dealer_monthly_comm,dealer_monthly_times,dealer_onetime_comm FROM package")->result_array();
		$dealers      = $this->db->query("SELECT * FROM dealer")->result_array();
		$dealer_comms = $this->db->query("SELECT * FROM dealer_comm")->result_array();

		$package_arr = [];
		foreach ($packages as $p) {
			$package_arr[$p['package_no']] = [
				'package_no'            => $p['package_no'],
				'name'                  => $p['name'],
				'package_comm_type'     => $p['dealer_comm_type'],
				'package_monthly_comm'  => $p['dealer_monthly_comm'],
				'package_monthly_times' => $p['dealer_monthly_times'],
				'package_onetime_comm'  => $p['dealer_onetime_comm'],
			];
		}

		$dealer_comm_arr = [];
		foreach ($dealers as $d) {
			$dealer_comm_arr[$d['dealer_no']] = [
				'dealer_comm'         		=> $package_arr,
				'monthly_pct_deduct'  		=> $d['monthly_pct_deduct'],
				'one_time_amt_deduct' 		=> $d['one_time_amt_deduct'],
				'is_commission_set' 		=> $d['is_commission_set'],
				'upline_tree'         		=> $d['upline_tree'],
				'top_level_dealer'    		=> $d['upline'] == '0' ? '1' : '0',
			];
		}

		foreach ($dealer_comms as $dc) {
			$dNo = $dc['dealer_no'];
			$pNo = $dc['package'] ?? null;

			if ($pNo !== null && isset($dealer_comm_arr[$dNo]['dealer_comm'][$pNo])) {
				$dealer_comm_arr[$dNo]['dealer_comm'][$pNo] += [
					'comm_type'      => $dc['comm_type'],
					'monthly'        => $dc['monthly'],
					'onetime'        => $dc['onetime'],
					'follow_package' => $dc['follow_package'],
					'monthly_times'  => $dc['monthly_times'],
				];
			}
		}

		$target_packages = $package_no ? [$package_no] : array_keys($package_arr);

		foreach ($dealer_comm_arr as $dNo => $dealer) {
			$upline = array_reverse(array_filter(explode(',', $dealer['upline_tree'])));

			foreach ($target_packages as $pNo) {
				if (!isset($dealer['dealer_comm'][$pNo])) continue;

				if ($dealer['is_commission_set'] == 0 && !isset($dealer['dealer_comm'][$pNo]['comm_type'])) {
					$dealer_comm_arr[$dNo]['dealer_comm'][$pNo] = [
						'comm_type'      => 'm',
						'monthly'        => '0',
						'onetime'        => '0.00',
						'follow_package' => '0',
						'monthly_times'  => '0',
					];

					continue;
				}

				if (!$force_calc_all) {
					if (!isset($dealer['dealer_comm'][$pNo]['comm_type'])) {
						$dealer_comm_arr[$dNo]['dealer_comm'][$pNo] =
							$this->recalculate_commission_rate($dealer_comm_arr, $upline, $pNo);
					}
				} else {
					$dealer_comm_arr[$dNo]['dealer_comm'][$pNo] =
						$this->recalculate_commission_rate($dealer_comm_arr, $upline, $pNo);
				}
			}
		}

		if ($dealer_no) {
			if ($package_no) {
				return $dealer_comm_arr[$dealer_no]['dealer_comm'][$package_no] ?? null;
			}
			return $dealer_comm_arr[$dealer_no];
		}

		return $dealer_comm_arr;
	}

	function recalculate_commission_rate($dealer_comm_arr, $upline, $package_no)
	{
		$monthlyDeduct = 0;
		$oneTimeDeduct = 0;
		
		foreach ($upline as $dNo) {
			if (!isset($dealer_comm_arr[$dNo])) continue;

			$comm = $dealer_comm_arr[$dNo]['dealer_comm'][$package_no];

			if (isset($comm['comm_type'])) {
				$follow = $comm['follow_package'] ?? 0;

				$monthly = $follow ? $comm['package_monthly_comm'] : $comm['monthly'];
				$onetime = $follow ? $comm['package_onetime_comm'] : $comm['onetime'];

				return [
					'comm_type'      => $follow ? $comm['package_comm_type'] : $comm['comm_type'],
					'monthly_times'  => $follow ? $comm['package_monthly_times'] : $comm['monthly_times'],
					'monthly'        => max(0, intval($monthly) - $monthlyDeduct),
					'onetime'        => max(0, floatval($onetime) - $oneTimeDeduct),
					'follow_package' => '0',
				];
			}

			$monthlyDeduct += intval($dealer_comm_arr[$dNo]['monthly_pct_deduct']);
			$oneTimeDeduct += floatval($dealer_comm_arr[$dNo]['one_time_amt_deduct']);
		}

		return [
			'comm_type'      => 'm',
			'monthly'        => '0',
			'onetime'        => '0.00',
			'follow_package' => '0',
			'monthly_times'  => '0',
		];
	}

	function get_dealer_listing($txt_search, $page_item_no, $query_where = '')
	{

		$txt_search = $this->db->escape_str($txt_search);

		$query_str = "
			SELECT COUNT(d.dealer_no) AS total_row
			FROM dealer d
			WHERE d.name LIKE '%$txt_search%'
		";
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query_str = "
			SELECT d.dealer_no, d.name, d.upline, d.upline_tree, d.building, upline_d.name AS upline_name
			FROM dealer d
			LEFT JOIN dealer upline_d ON d.upline = upline_d.dealer_no
			WHERE d.name LIKE '%$txt_search%'
			ORDER BY d.upline_tree
			LIMIT $page_item_no, ".$_SESSION['config']['max_page_item']."
		";
		$query = $this->db->query($query_str);
		$rows = ($query->num_rows() > 0) ? $query->result_array() : array();

		foreach ($rows as &$row) {
			$depth = substr_count(rtrim($row['upline_tree'], ','), ',');
			$indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
			$row['display_name'] = $indent . ($depth > 0 ? '↳ ' : '') . $row['name'];
		}

		$return_val['row'] = $rows;
		return $return_val;
	}

	function dealer_listing($txt_dealer, $building, $category, $status, $payment_source, $date_start, $date_end)
	{
		$txt_dealer = $this->db->escape_str($txt_dealer);
		$return_val = array();
		$query_where = '';
		$amount = 0;
		
		$payment_source_list = $_SESSION['payment_source'];
		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		
		if ( !empty($building) && $building !== 'all' ) {
			$query_where .= "AND b.name = '$building' ";
		}
		if ( !empty($category) && $category !== 'all' ) {
			$query_where .= "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}
		if ( !empty($payment_source) && $payment_source !== 'all' ) {
			$query_where .= "AND p.payment_source = '$payment_source' ";
		}
			
		$query_str = "	SELECT 
							MIN(c.category) AS category,
							c.customer_no,
							c.name AS customer_name,
							MIN(b.name) AS building_name,
							MIN(d.name) AS dealer_name,
							MIN(d.upline_tree) AS dealer_upline_tree,
							MIN(
								CASE 
									WHEN top_d.name = d.name THEN '-' 
									ELSE top_d.name 
								END
							) AS top_level_agent_name,
							p.pay_date,
							MIN(p.remark) AS remark,
							p.payment_no,
							MIN(p.cheque_no) AS cheque_no,
							MIN(p.amount) AS `amount`,
							MIN(p.bill_type) AS bill_type,
							MIN(p.payment_source) AS payment_source,
							MIN(spb.bank_displayname) AS paynet_bank
						FROM payment p
						INNER JOIN customer c ON c.customer_no = p.customer_no
						INNER JOIN dealer d ON c.dealer = d.dealer_no
						LEFT JOIN dealer top_d 
							ON top_d.dealer_no = SUBSTRING_INDEX(d.upline_tree, ',', 1)
						LEFT JOIN paynet_ac ac ON ac.fpx_sellerExOrderNo = p.cheque_no
						LEFT JOIN sys_paynet_banklist spb ON spb.bank_code = ac.fpx_buyerBankId
						LEFT JOIN building b ON b.building_no = c.building
						WHERE d.name LIKE '%$txt_dealer%' 
						AND p.pay_date >= '$date_start' 
						AND p.pay_date <= '$date_end'
						$query_where
						GROUP BY c.customer_no, c.name, p.pay_date, p.payment_no
						ORDER BY c.name ASC, p.pay_date, p.payment_no DESC";

		$query = $this->db->query($query_str);
		foreach ( $query->result_array() as $row ) {
		
			$category_name = $customer_category_list[$row['category']];
			$payment_source_name = $payment_source_list[$row['payment_source']];
			$bill_type_name = $bill_type_list[$row['bill_type']];
			
			$return_val[$category_name][$bill_type_name][] = 
			    array( 
						"pay_date" 				=> $row['pay_date'],
						"customer_no" 			=> $row['customer_no'],
						"customer_name" 		=> $row['customer_name'],
						"building_name" 		=> $row['building_name'],
						"remark" 				=> $row['remark'],
						"payment_no" 			=> $row['payment_no'],
						"payment_source_name" 	=> $payment_source_name,
						"cheque_no"	 			=> $row['cheque_no'],
						"amount" 				=> $row['amount'],
						"dealer_name" 			=> $row['dealer_name'],
						"top_level_agent_name" 	=> $row['top_level_agent_name'],
						"paynet_bank" 			=> $row['paynet_bank'],
				     );
		}

		return $return_val;
	}

	function api_dealer_comm_listing($building, $category, $status, $sel_date, $customer_no = '', $dealer_no = 0, $upline = 0)
	{

		$return_val = array();
		$query_where = '';
		$amount = 0;

		$customer_category_list = $_SESSION['cust_category'] ?? '';
		$bill_type_list = $_SESSION['bill_type'] ?? '';
		
		if ( !empty($building) && $building !== 'all' ) {
			$query_where .= "AND b.name = '$building' ";
		}
		if ( !empty($category) && $category !== 'all' ) {
			$query_where .= "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}
		if ( !empty($customer_no) && $customer_no !== 'all' ) {
			$query_where .= "AND c.customer_no = '$customer_no' ";
		}
		if (!empty($upline)) {
			$all_downline = $this->db->query("SELECT d.dealer_no FROM dealer d WHERE d.upline = ?", [$upline])->result_array();
			$downline_arr = array_column($all_downline, 'dealer_no');
			$query_where .= "AND dc.dealer_no IN (" . implode(',', $downline_arr) . ") ";
		}
		if(!empty($dealer_no)) {
			$query_where .= "AND dc.dealer_no = $dealer_no ";
		}

		if ($sel_date !== 'all') {
			$date_start = date("Y-m-01", strtotime(substr($sel_date, 0, 4) . "-" . substr($sel_date, 4, 2) . "-01"));
			$date_end = date("Y-m-t", strtotime(substr($sel_date, 0, 4) . "-" . substr($sel_date, 4, 2) . "-01"));

			$like_date = date("Y-m", strtotime(
				"first day of previous month",
				strtotime(date("Y-m-01", strtotime(substr($sel_date, 0, 4) . "-" . substr($sel_date, 4, 2) . "-01")))
			));

			$date_where = "
				AND dc.comm_date >= '$date_start'
				AND dc.comm_date <= '$date_end'
				AND csa.transact_date < '$date_start'
			";
		} else {
			$date_where = '';
		}

		if ($sel_date === 'all') {
			$query_union = '';
		} else {
			$query_union = "
				UNION ALL (
					SELECT 
						dc.*,
						c.name AS customer_name,
						b.name AS building_name,
						d.name AS dealer_name,
						c.dealer AS customer_dealer_no,
						c.package_name,
						bi.amount AS bill_amount,
						csa.transact_date AS activated_date,
						CASE 
							WHEN top_d.name = d.name THEN '-' 
							ELSE top_d.name 
						END AS top_level_agent_name
					FROM dealer_comm_record dc 
					INNER JOIN customer c ON c.customer_no = dc.customer_no
					LEFT JOIN building b ON b.building_no = c.building
					LEFT JOIN dealer d ON c.dealer = d.dealer_no
					LEFT JOIN dealer top_d 
						ON top_d.dealer_no = SUBSTRING_INDEX(d.upline_tree, ',', 1)
					LEFT JOIN bill bi ON dc.bill_no = bi.bill_no
					LEFT JOIN (
						SELECT acs.* 
						FROM customer_status acs 
						JOIN (
							SELECT customer_no, MIN(status_id) AS max_status_id 
							FROM customer_status 
							WHERE `status` = 'A' 
							GROUP BY customer_no
						) bcs ON acs.status_id = bcs.max_status_id
					) csa ON csa.customer_no = c.customer_no 
					WHERE true
					AND csa.transact_date LIKE '$like_date%' 
					AND dc.comm_date < '$date_start' 
					$query_where
				)
			";
		}

		$query_str = "	SELECT * FROM (
							SELECT 
								dc.*,
								c.name AS customer_name,
								b.name AS building_name,
								d.name AS dealer_name,
								c.dealer AS customer_dealer_no,
								c.package_name,
								bi.amount AS bill_amount,
								csa.transact_date AS activated_date,
								CASE 
									WHEN top_d.name = d.name THEN '-' 
									ELSE top_d.name 
								END AS top_level_agent_name
							FROM dealer_comm_record dc
							INNER JOIN customer c ON c.customer_no = dc.customer_no
							LEFT JOIN building b ON b.building_no = c.building
							LEFT JOIN dealer d ON c.dealer = d.dealer_no
							LEFT JOIN dealer top_d 
								ON top_d.dealer_no = SUBSTRING_INDEX(d.upline_tree, ',', 1)
							LEFT JOIN bill bi ON dc.bill_no = bi.bill_no
							LEFT JOIN (
								SELECT acs.* 
								FROM customer_status acs 
								JOIN (
									SELECT customer_no, MIN(status_id) AS max_status_id 
									FROM customer_status 
									WHERE `status` = 'A' 
									GROUP BY customer_no
								) bcs ON acs.status_id = bcs.max_status_id
							) csa ON csa.customer_no = c.customer_no
							WHERE true
							$date_where
							$query_where
							$query_union
						) z 

						ORDER BY z.customer_name ASC, z.comm_date DESC";

		$query = $this->db->query($query_str);

		$subtotal = [];

		foreach ($query->result_array() as $row) {
			$return_val[] = $row;
			$return_val['rows'][] = $row;

			$dealer_no = $row['customer_dealer_no'];

			$subtotal[$dealer_no]['subtotal'] = ($subtotal[$dealer_no]['subtotal'] ?? 0) + (float)$row['amount'];
			$subtotal[$dealer_no]['name'] = $row['dealer_name'];
		}

		$return_val['subtotal'] = $subtotal;
		$return_val['grandtotal'] = array_sum(array_column($subtotal, 'subtotal'));

		return $return_val;
	}
	
	function dealer_comm_listing($txt_dealer, $building, $category, $status, $sel_date, $customer_no = '')
	{

		$return_val = array();
		$query_where = '';
		$amount = 0;

		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		
		if ( !empty($building) && $building !== 'all' ) {
			$query_where .= "AND b.name = '$building' ";
		}
		if ( !empty($category) && $category !== 'all' ) {
			$query_where .= "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}
		if ( !empty($customer_no) && $customer_no !== 'all' ) {
			$query_where .= "AND c.customer_no = '$customer_no' ";
		}

		$dealer_where = "1 = 1";
		if (!empty($txt_dealer)) {
			$values = is_array($txt_dealer) ? $txt_dealer : [$txt_dealer];
			$dealer_string = "'" . implode("','", array_map('addslashes', $values)) . "'";
			$dealer_where = "d.dealer_no IN ($dealer_string)";
		}

		if ($sel_date !== 'all') {
			$date_start = date("Y-m-01", strtotime(substr($sel_date, 0, 4)."-".substr($sel_date, 4, 2)."-01"));
			$date_end = date("Y-m-t", strtotime(substr($sel_date, 0, 4)."-".substr($sel_date, 4, 2)."-01"));

			$like_date = date("Y-m", strtotime( "first day of previous month", strtotime(date("Y-m-01", strtotime(substr($sel_date, 0, 4)."-".substr($sel_date, 4, 2)."-01")))));
		}

		if ($sel_date === 'all') {
			$date_where = '';
		} else {
			$date_where = "
				AND dc.comm_date >= '$date_start'
				AND dc.comm_date <= '$date_end'
				AND csa.transact_date < '$date_start'
			";
		}

		if ($sel_date === 'all') {
			$query_union = '';
		} else {
			$query_union = "
				UNION ALL (
					SELECT 
						dc.*,
						c.name AS customer_name,
						b.name AS building_name,
						d.name AS dealer_name,
						c.dealer AS customer_dealer_no,
						c.package_name,
						bi.amount AS bill_amount,
						csa.transact_date AS activated_date,
						CASE 
							WHEN top_d.name = d.name THEN '-' 
							ELSE top_d.name 
						END AS top_level_agent_name
					FROM dealer_comm_record dc 
					INNER JOIN customer c ON c.customer_no = dc.customer_no
					LEFT JOIN building b ON b.building_no = c.building
					LEFT JOIN dealer d ON c.dealer = d.dealer_no
					LEFT JOIN dealer top_d 
						ON top_d.dealer_no = SUBSTRING_INDEX(d.upline_tree, ',', 1)
					LEFT JOIN bill bi ON dc.bill_no = bi.bill_no
					LEFT JOIN (
						SELECT acs.* 
						FROM customer_status acs 
						JOIN (
							SELECT customer_no, MIN(status_id) AS max_status_id 
							FROM customer_status 
							WHERE `status` = 'A' 
							GROUP BY customer_no
						) bcs ON acs.status_id = bcs.max_status_id
					) csa ON csa.customer_no = c.customer_no 
					WHERE $dealer_where 
					AND csa.transact_date LIKE '$like_date%' 
					AND dc.comm_date < '$date_start' 
					AND dc.dealer_no = top_d.dealer_no 
					$query_where 
				)
			";
		}

		$query_str = "	SELECT * FROM (
							SELECT 
								dc.*,
								c.name AS customer_name,
								b.name AS building_name,
								d.name AS dealer_name,
								c.dealer AS customer_dealer_no,
								c.package_name,
								bi.amount AS bill_amount,
								csa.transact_date AS activated_date,
								CASE 
									WHEN top_d.name = d.name THEN '-' 
									ELSE top_d.name 
								END AS top_level_agent_name
							FROM dealer_comm_record dc
							INNER JOIN customer c ON c.customer_no = dc.customer_no
							LEFT JOIN building b ON b.building_no = c.building
							LEFT JOIN dealer d ON c.dealer = d.dealer_no
							LEFT JOIN dealer top_d 
								ON top_d.dealer_no = SUBSTRING_INDEX(d.upline_tree, ',', 1)
							LEFT JOIN bill bi ON dc.bill_no = bi.bill_no
							LEFT JOIN (
								SELECT acs.* 
								FROM customer_status acs 
								JOIN (
									SELECT customer_no, MIN(status_id) AS max_status_id 
									FROM customer_status 
									WHERE `status` = 'A' 
									GROUP BY customer_no
								) bcs ON acs.status_id = bcs.max_status_id
							) csa ON csa.customer_no = c.customer_no
							WHERE $dealer_where
							AND dc.dealer_no = top_d.dealer_no
							$date_where
							$query_where 
							$query_union
						) z 

						ORDER BY z.customer_name ASC, z.comm_date DESC";

		$query = $this->db->query($query_str);

		$subtotal = [];

		foreach ($query->result_array() as $row) {
			$return_val['rows'][] = $row;

			$dealer_no = $row['customer_dealer_no'];

			$subtotal[$dealer_no]['subtotal'] = ($subtotal[$dealer_no]['subtotal'] ?? 0) + (float)$row['amount'];
			$subtotal[$dealer_no]['name'] = $row['dealer_name'];
		}

		$return_val['subtotal'] = $subtotal;
		$return_val['grandtotal'] = array_sum(array_column($subtotal, 'subtotal'));

		return $return_val;
	}

	function get_date_range()
	{
		$return = array();
		$sql = "select distinct comm_date from dealer_comm_record order by comm_date desc";
		$query = $this->db->query($sql);

		foreach ($query->result_array() as $row) {
			$arr_key = date('Ym', strtotime($row['comm_date']));
			$arr_value = date('F Y', strtotime($row['comm_date']));
			$return[$arr_key] = $arr_value;
		}
		//get all the remaining dates until now
		$date_from = max(array_keys($return));
		$max_date = date('Ym');
		$current_date = $date_from;
		for ($i = $current_date; $i <= $max_date; $i++) {
			$year = substr($i,0,4);
			$month = substr($i,4);
			if ($month > 12) {
				continue;
			}
			if (!isset($return[$i])) {
				$return[$i] = date('F Y', strtotime($year.'-'.$month.'-01'));
			}
		}

		//include next month option as well
		$arr_key = date('Ym',strtotime('first day of +1 month'));
		$arr_value = date('F Y',strtotime('first day of +1 month'));
		$return[$arr_key] = $arr_value;

		krsort($return);
		return $return;
	}

	function dealer_comm_summary($txt_dealer, $date_start, $date_end)
	{
		$txt_dealer = $this->db->escape_str($txt_dealer);
		$query_str = "
			SELECT d.name AS dealer_name, 
				COUNT(DISTINCT c.customer_no) AS total_customers,
				COUNT(dc.bill_no) AS total_bills,
				SUM(bi.amount) AS total_bill_amount,
				SUM(dc.amount) AS total_commission
			FROM dealer_comm_record dc
			INNER JOIN customer c ON c.customer_no = dc.customer_no
			LEFT JOIN dealer d ON dc.dealer_no = d.dealer_no
			LEFT JOIN bill bi ON dc.bill_no = bi.bill_no
			WHERE d.name LIKE '%$txt_dealer%' 
			AND d.upline = 0 
			AND dc.comm_date >= '$date_start' 
			AND dc.comm_date <= '$date_end'
			GROUP BY d.name
			ORDER BY d.name ASC
		";
		$query = $this->db->query($query_str);
		return $query->result_array();
	}

    function get_autocomplete_load_dealer($keyword)
	{		
		$keyword = $this->db->escape_str($keyword);
		$query_str = "SELECT d.name 
		              FROM dealer d
		              WHERE d.name LIKE '%$keyword%' 
		              ORDER BY d.dealer_no LIMIT 10;";
					
		$query = $this->db->query($query_str);
		return $query->result_array(); 
	}

	function get_dealer($dealer_no='')
	{		
		$query = $this->db->query("SELECT d.*
							FROM dealer d 
							WHERE d.dealer_no='".$this->db->escape_str($dealer_no)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	
		{
			$return_val = $query->row_array();
			
			if ($return_val['signup_date'] == 0) {
				$return_val['signup_date'] = '';
			}
			if ($return_val['terminated_date'] == 0) {
				$return_val['terminated_date'] = '';
			}
			
			$return_val['btn_delete']='enabled';
		}
		else 
		{
			$return_val['dealer_no'] = '';
			$return_val['name'] = '';
			$return_val['reg_no'] = '';
			$return_val['status'] = 'r';
			$return_val['pic_name'] = '';
			$return_val['addr1'] = '';
			$return_val['addr2'] = '';
			$return_val['addr3'] = '';
			$return_val['city'] = '';
			$return_val['postcode'] = '';
			$return_val['state'] = 'pg';
			$return_val['tel_num'] = '';
			$return_val['fax_num'] = '';
			$return_val['mobile_num'] = '';
			$return_val['email'] = '';

			$return_val['dealer_type']='A';

			$return_val['login'] = '';
			$return_val['is_commission_set'] = '';

			$return_val['upline'] = 0;
			$return_val['building'] = '';
			$return_val['monthly_pct_deduct'] = 0;
			$return_val['one_time_amt_deduct'] = 0.00;
			
			$return_val['signup_date'] = date('Y-m-d');
			$return_val['terminated_date'] = '';
			
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function get_dealer_by($where = '', $select = '*')
	{
		$query_str = "SELECT $select FROM dealer WHERE 1=1 $where LIMIT 1";
		$query = $this->db->query($query_str);
		if ($query->num_rows() > 0)	
		{
			$return_val = $query->row_array();
		}
		else 
		{
			$return_val = array();
		}
		
		return $return_val;
	}

	function update_acc_id($dealer_no, $acc_id) {
		$this->db->query("UPDATE `dealer` SET acc_id = ? WHERE dealer_no = ? ", array($acc_id, $dealer_no));
	}

	function this_month_comm_count(){
		$return_val = 0;
		$query_str = "SELECT bill_no FROM dealer_comm_record WHERE comm_date >= '" . date("Y-m-1") . "' AND is_manual = 0 LIMIT 1";
		$query = $this->db->query($query_str);
		if ($query->num_rows() >0) {
			$return_val = $query->num_rows();
		}
		return $return_val;
	}

	function chk_one_time_comm($customer_no, $dealer) {
		$return_val = 0;
		$query_str = "SELECT * FROM dealer_comm_record WHERE customer_no = ? AND dealer_no = ? ";
		$query = $this->db->query($query_str, array($customer_no, $dealer));
		if ($query->num_rows() >0) {
			$return_val = $query->num_rows();
		}
		return $return_val;
	}

	function chk_monthly_comm_times($customer_no, $dealer) {
		$return_val = 0;
		$query_str = "SELECT COUNT(*) AS comm_times FROM dealer_comm_record WHERE customer_no = ? AND dealer_no = ? ";
		$query = $this->db->query($query_str, array($customer_no, $dealer));
		if ($query->num_rows() >0) {
			$return_val = $query->row_array();
		}
		return $return_val['comm_times'];
	}

	public function dealer_comm_record_insert($post_back) {

		$sql = "INSERT INTO `dealer_comm_record` (
			customer_no, `bill_no`, comm_no, dealer_no, package_no, comm_date, `comm_due_date`, comm_period, comm_desc, ref_no, amount, tax_charges, is_void, is_manual, created_on, created_by
		) VALUES (
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?  
		)";

		$values_array = array(
			$post_back['customer_no'],
			$post_back['bill_no'],
			$post_back['comm_no'],
			$post_back['dealer_no'],
			$post_back['package_no'],
			$post_back['comm_date'],
			$post_back['comm_due_date'],
			$post_back['comm_period'],
			$post_back['comm_desc'],
			$post_back['ref_no'],
			$post_back['amount'],
			$post_back['tax_charges'],
			$post_back['is_void'],
			$post_back['is_manual'],
			$post_back['created_on'],
			$post_back['created_by'] 
		);

		$this->db->query($sql, $values_array);

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.print_r($values_array, true));
		$method 		= $this->router->method;
		$action_desc	= 'a new dealer commission record (' . $this->db->escape_str($post_back['comm_no']) . ') has been added';
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category);

	}
	
	function generate_upline_tree($upline_dealer_no, $dealer_no) {
		$query = $this->db->query("SELECT dealer_no, upline FROM dealer ORDER BY dealer_no ASC");
		$dealer_list = array_column($query->result_array() ?? [], 'upline', 'dealer_no');

		$tree = [$dealer_no];
		$seen = [$dealer_no]; // prevent infinite loop

		while ($upline_dealer_no != 0) {
			if (in_array($upline_dealer_no, $seen)) break;
			$tree[] = $upline_dealer_no;
			$seen[] = $upline_dealer_no;
			$upline_dealer_no = $dealer_list[$upline_dealer_no] ?? 0;
		}

		return implode(',', array_reverse($tree)) . ',';
	}

	function get_agent_pkg_setting($dealer_no, $package_no) {
		$dealer_comm_array = $this->get_dealer_comm_array_map($dealer_no, $package_no, true);

		$return_val = [
			'comm_type'       => 'm',
			'monthly_comm'    => 0,
			'monthly_times'   => 0,
			'onetime_comm'    => 0
		];

		if (!empty($dealer_comm_array)) {
			$pkg_comm = $dealer_comm_array;
			$return_val['comm_type']      = $pkg_comm['comm_type'] ?? 'm';
			$return_val['monthly_comm']   = $pkg_comm['monthly'] ?? 0;
			$return_val['monthly_times']  = $pkg_comm['monthly_times'] ?? 0;
			$return_val['onetime_comm']   = $pkg_comm['onetime'] ?? 0;
		}

		return $return_val;
	}

	function do_recalc_agent_comm($run_date) {

		$this->load->model('customer_model');

    	$run_month_start = date("Y-m-1", strtotime($run_date));
    	$run_month_end = date("Y-m-t", strtotime($run_date));

    	//check if bill for this month has run
    	$this->load->model('bill_model');
    	$this_month_bill_count = $this->bill_model->this_month_bill_count_with_rundate($run_month_start);
    	if ($this_month_bill_count <= 0) {
    		return "Billing cycle not yet run!";
    	}

    	if (empty($run_date)) {
    		return "No run date.";
    	}

    	//delete all records for this date period first
    	$this->db->trans_begin();
    	$this->db->query("DELETE FROM dealer_comm_record WHERE comm_date = ?", [$run_month_start]);

		$deleted_count = $this->db->affected_rows();

		// count newly generated commission
		$generated_count = 0;

    	$query_str = "SELECT b.*, c.dealer, c.package, c.name, c.monthly_charge, ddd.upline, ddd.upline_tree,   
    	p.dealer_comm_type AS p_comm_type, 
    	p.dealer_monthly_comm AS p_monthly_comm, 
    	p.dealer_monthly_times AS p_monthly_times, 
    	p.dealer_onetime_comm AS p_onetime_comm,  
      	dd.comm_type AS dd_comm_type, 
    	dd.monthly AS dd_monthly_comm, 
    	dd.monthly_times AS dd_monthly_times, 
    	dd.onetime AS dd_onetime_comm, 
    	IFNULL(dd.follow_package,1) AS follow_package  
    	FROM bill b 
    	LEFT JOIN dealer_comm_record d ON (b.bill_no = d.bill_no) 
    	LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
    	LEFT JOIN package p ON (c.package = p.package_no) 
    	LEFT JOIN dealer_comm dd ON (dd.dealer_no = c.dealer AND dd.package = c.package) 
    	LEFT JOIN dealer ddd ON (c.dealer = ddd.dealer_no) 
    	WHERE b.bill_date >= '" . $run_month_start . "' AND b.bill_date <= '".$run_month_end."' AND d.comm_no IS NULL AND c.dealer != 0 AND b.is_manual = 0 ";
    	$query		= $this->db->query($query_str);

   		$get_max_comm_no = sprintf( "%06d", 0 );
		$get_max_comm_no = date("ym", strtotime($run_date)) . $get_max_comm_no;
		$new_comm_no = $get_max_comm_no + 1;

		//for comm date and comm due date, not sure if needed
		$date_first_day	= date("Y-m-1", strtotime($run_date));
		$date_last_day	= date('Y-m-t', strtotime($run_date));

		//for package charges, check the customer package of last month's end
		$new_datetime = new DateTime($run_date);
		$last_month_ini = $new_datetime->modify('first day of last month');
		$new_datetime = new DateTime($run_date);
		$last_month_end = $new_datetime->modify('last day of last month');

		$records = $query->result_array();

		//company payout to first level
		foreach ($records as $row) {

			/*
			echo "<pre>";
    		print_r($row);
    		echo "</pre>";
			*/

			//monthly charge taken wrongly, should be taken from customer_package_history
			$package_amount = 0;
			$cur_package_info = $this->customer_model->get_customer_monthly_charge( $row['customer_no'] , $last_month_end->format( 'Y-m-d' ) );
			if( $cur_package_info['monthly_charge'] != '' ){
				$package_amount = $cur_package_info['monthly_charge'];
			} else {
				$package_amount = $row['monthly_charge'];
			}

			$package_no = $row['package'];
			if( isset($cur_package_info['package_no']) ){
				if( $cur_package_info['package_no'] != '' ){
					$package_no = $cur_package_info['package_no'];
				}
			}

    		//if got dealer, then take this customer's package and check if shud follow package commission or dealer set commission
    		//have to check who is the main upline - and get from their setting
    		if (!empty($row['upline'])) {
    			$upline_tree = explode(",", $row['upline_tree']);
    			$setting_info = $this->get_agent_pkg_setting($upline_tree[0], $package_no);
   	    		$comm_type = $setting_info['comm_type'];
	    		$monthly_comm = $setting_info['monthly_comm'];
	    		$monthly_times = $setting_info['monthly_times'];
	    		$onetime_comm = $setting_info['onetime_comm'];

	    		$dealer_no = $upline_tree[0];
    		} else {
    			//if upline not equal empty, means just follow the query
    			$dealer_no = $row['dealer'];
	    		if ($row['follow_package'] == '1') {
	    			//follow package
	    			$comm_type = $row['p_comm_type'];
	    			$monthly_comm = $row['p_monthly_comm'];
	    			$monthly_times = $row['p_monthly_times'];
	    			$onetime_comm = $row['p_onetime_comm'];
	    		} else {
	    			//dont follow package
	       			$comm_type = $row['dd_comm_type'];
	    			$monthly_comm = $row['dd_monthly_comm'];
	    			$monthly_times = $row['dd_monthly_times'];
	    			$onetime_comm = $row['dd_onetime_comm'];
	    		}
    		}

    		$dealer_comm_rec = array();
    		$comm_desc = '';

    		//check if values are filled out
    		if ($comm_type == 'm') {
    			if (empty($monthly_comm)) {
    				continue;
    			}

    			//check if collected times have exceeded
    			//if exceeded, dont give anymore
    			if (!empty($monthly_times)) {
	    			$chk_monthly = $this->chk_monthly_comm_times($row['customer_no'], $dealer_no);
	    			if ($chk_monthly >= $monthly_times) {
	    				continue;
	    			}
    			}

    			//follow monthly rate if got set, otherwise check if one time has been paid out
    			$comm = (($package_amount * $monthly_comm) / 100);
    			$comm_desc = 'Monthly Commission For Period '.$last_month_ini->format( 'Y-m-d' ).' to '.$last_month_end->format( 'Y-m-d' ).' Customer: '.$row['name'];

    			//if monthly, calc monthly charge (after tax or before tax?) with % and deposit to agent

    		} else if ($comm_type == 'o') {
    			if (empty($onetime_comm)) {
    				continue;
    			}

    			//check if one time for this customer has already been distribute to the agent
    			//if change package then how? shud be only one time based on customer_no
    			$chk_one_time = $this->chk_one_time_comm($row['customer_no'], $dealer_no);

    			//if one time, then just distribute one time to agent
    			if ($chk_one_time <= 0) {
    				$comm = $onetime_comm;

    				$comm_desc = 'One-Time Commission For Customer: '.$row['name'];
    			}
    		} else {
    			continue;
    			//unknown comm type
    		}

    		//if got this far then insert into dealer_comm_rec
    		$dealer_comm_rec['customer_no'] = $row['customer_no'];
    		$dealer_comm_rec['bill_no'] = $row['bill_no'];
    		$dealer_comm_rec['comm_no'] = $new_comm_no;
    		$dealer_comm_rec['dealer_no'] = $dealer_no;
    		$dealer_comm_rec['package_no'] = $package_no;
    		$dealer_comm_rec['comm_date'] = $date_first_day;
    		$dealer_comm_rec['comm_due_date'] = $date_last_day;
    		//empty for some reason
    		$dealer_comm_rec['comm_period'] = '';
    		$dealer_comm_rec['comm_desc'] = $comm_desc;
    		//for if payment is made, update this field
    		$dealer_comm_rec['ref_no'] = '';
    		$dealer_comm_rec['amount'] = $comm;
    		//0 for now
    		$dealer_comm_rec['tax_charges'] = 0;
    		$dealer_comm_rec['is_void'] = 0;
    		$dealer_comm_rec['is_manual'] = 0;
    		$dealer_comm_rec['created_on'] = date('Y-m-d H:i:s');
    		//system
    		$dealer_comm_rec['created_by'] = 0;

			if($comm <= 0) continue;

			$this->dealer_comm_record_insert($dealer_comm_rec);
			
			$generated_count++;
    		//should we auto submit self billed e-invoice for this?

    		//log
    		log_message('error', print_r($dealer_comm_rec, true));

    		$new_comm_no++;
    	}

    	//do for downline
    	foreach ($records as $row) {
    		if (empty($row['upline'])) {
    			//not downline
    			continue;
    		}
			
			//monthly charge taken wrongly, should be taken from customer_package_history
			$package_amount = 0;
			$cur_package_info = $this->customer_model->get_customer_monthly_charge( $row['customer_no'] , $last_month_end->format( 'Y-m-d' ) );

			if( $cur_package_info['monthly_charge'] != '' ){
				$package_amount = $cur_package_info['monthly_charge'];
			} else {
				$package_amount = $row['monthly_charge'];
			}

			$package_no = $row['package'];
			if( isset($cur_package_info['package_no']) ){
				if( $cur_package_info['package_no'] != '' ){
					$package_no = $cur_package_info['package_no'];
				}
			}

			//determine how many downline to pay for 1 transaction
			$downlines = array();
			$upline_tree = array_filter(explode(",", $row['upline_tree'])); // array_filter can clear the empty value in array
			//start from 1 because topmost already done above
			for ($k = 1; $k < count($upline_tree); $k++) {
    			$setting_info = $this->get_agent_pkg_setting($upline_tree[$k], $package_no);
   	    		$comm_type = $setting_info['comm_type'];
	    		$monthly_comm = $setting_info['monthly_comm'];
	    		$monthly_times = $setting_info['monthly_times'];
	    		$onetime_comm = $setting_info['onetime_comm'];

	    		$comm = 0;
	    		$comm_desc = '';
	    		$dealer_no = $upline_tree[$k];

	    		if ($comm_type == 'm') {
	    			if (empty($monthly_comm)) {
	    				continue;
	    			}

	    			//check if collected times have exceeded
	    			//if exceeded, dont give anymore
	    			if (!empty($monthly_times)) {
		    			$chk_monthly = $this->chk_monthly_comm_times($row['customer_no'], $dealer_no);
		    			if ($chk_monthly >= $monthly_times) {
		    				continue;
		    			}
	    			}

	    			//follow monthly rate if got set, otherwise check if one time has been paid out
	    			$comm = (($package_amount * $monthly_comm) / 100);
	    			$comm_desc = 'Monthly Commission For Period '.$last_month_ini->format( 'Y-m-d' ).' to '.$last_month_end->format( 'Y-m-d' ).' Customer: '.$row['name'];

	    			//if monthly, calc monthly charge (after tax or before tax?) with % and deposit to agent

	    		} else if ($comm_type == 'o') {
	    			if (empty($onetime_comm)) {
	    				continue;
	    			}

	    			//check if one time for this customer has already been distribute to the agent
	    			//if change package then how? shud be only one time based on customer_no
	    			$chk_one_time = $this->chk_one_time_comm($row['customer_no'], $dealer_no);

	    			//if one time, then just distribute one time to agent
	    			if ($chk_one_time <= 0) {
	    				$comm = $onetime_comm;

	    				$comm_desc = 'One-Time Commission For Customer: '.$row['name'];
	    			}
	    		} else {
	    			continue;
	    			//unknown comm type
	    		}

	    		$downlines[$dealer_no]['desc'] = $comm_desc;
	    		$downlines[$dealer_no]['comm'] = $comm;

			}

			foreach ($downlines as $downline => $downline_info) {

				if (empty($downline)) {
					log_message('error', 'Something went wrong in downline loop.');
					continue;
				}

				$dealer_comm_rec = array();

	    		//if got this far then insert into dealer_comm_rec
	    		$dealer_comm_rec['customer_no'] = $row['customer_no'];
	    		$dealer_comm_rec['bill_no'] = $row['bill_no'];
	    		$dealer_comm_rec['comm_no'] = $new_comm_no;
	    		$dealer_comm_rec['dealer_no'] = $downline;
	    		$dealer_comm_rec['package_no'] = $package_no;
	    		$dealer_comm_rec['comm_date'] = $date_first_day;
	    		$dealer_comm_rec['comm_due_date'] = $date_last_day;
	    		//empty for some reason
	    		$dealer_comm_rec['comm_period'] = '';
	    		$dealer_comm_rec['comm_desc'] = $downline_info['desc'];
	    		//for if payment is made, update this field
	    		$dealer_comm_rec['ref_no'] = '';
	    		$dealer_comm_rec['amount'] = $downline_info['comm'];
	    		//0 for now
	    		$dealer_comm_rec['tax_charges'] = 0;
	    		$dealer_comm_rec['is_void'] = 0;
	    		$dealer_comm_rec['is_manual'] = 0;
	    		$dealer_comm_rec['created_on'] = date('Y-m-d H:i:s');
	    		//system
	    		$dealer_comm_rec['created_by'] = 0;

				if($downline_info['comm'] <= 0) continue;

				$this->dealer_comm_record_insert($dealer_comm_rec);

				$generated_count++;
	    		//log
	    		log_message('error', print_r($dealer_comm_rec, true));

	    		$new_comm_no++;

    		}
    	}

		//db commit or rollback
		if ($this->db->trans_status() === true)
		{
			$this->db->trans_commit();

			//action log
			$model = 'action_log_model';
			$this->load->model($model);

			$ctrl = $this->router->fetch_class();
			$method = $this->router->method;

			$query_str = 'Recalculate agent commission for run date: ' . $run_date;
			$esc_query_str = $this->db->escape_str($query_str);

			$action_desc = 'Agent commission recalculated for ' . $run_month_start .
				'. Deleted: ' . $deleted_count .
				', Generated: ' . $generated_count;

			$action_category = 'update';

			$this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		}else{
			$this->db->trans_rollback();
			$db_error = $this->db->error();

			return $db_error;
		}

    	return '1';

	}

}
