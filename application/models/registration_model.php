<?php
class Registration_model extends MY_Model
{

	protected $_table = 'registration';
	protected $_primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	public function get_registrations($page_item_no = 0, $query_where = array())
	{
		$return_val['total_row'] = 0;

		$where = '';
		$where_txt = '';
		$order_by = " ORDER BY IF(r.type='b',r.comp_name,r.name) ASC ";

		//$query_where is array with search param
		if (isset($query_where['txt_search'])) {
			if (!empty($query_where['txt_search'])) {
				$where_txt .= " AND (
				r.reg_no LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				OR r.name LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				OR r.icno LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				OR r.phone LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%'
				OR r.email LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				OR r.comp_name LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				OR r.bill_unit_no LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				OR r.ins_unit_no LIKE '%" . $this->db->escape_str($query_where['txt_search']) . "%' 
				)";
			}
		}

		if (isset($query_where['sel_status'])) {
			if (!empty($query_where['sel_status']) && $query_where['sel_status'] != 'all') {
				$where_txt .= " AND r.`status` = '".$this->db->escape_str($query_where['sel_status'])."' ";
			}
		}

		if (!empty($query_where['sel_installation']) && $query_where['sel_installation'] != 'all') {

			switch ($query_where['sel_installation']) {

				case 'upcoming':
					$where_txt .= " AND DATE_ADD(r.preferred_install_datetime, INTERVAL 2 HOUR) > NOW() ";
					$order_by = " ORDER BY r.preferred_install_datetime ASC, IF(r.type='b',r.comp_name,r.name) ASC ";
					break;

				case 'overdue':
					$where_txt .= " AND DATE_ADD(r.preferred_install_datetime, INTERVAL 2 HOUR) < NOW() ";
					$order_by = "  ORDER BY r.preferred_install_datetime DESC, IF(r.type='b',r.comp_name,r.name) ASC ";
					break;

				case 'unscheduled':
					$where_txt .= " AND r.preferred_install_datetime IS NULL ";
					$order_by = "  ORDER BY IF(r.type='b',r.comp_name,r.name) ASC ";
					break;
			}
		}

		if (isset($query_where['sel_building'])) {
			if ( $query_where['sel_building'] != 'all' && !empty( $query_where['sel_building'] ) ){
				$where_txt .= "AND r.building_no = '".$query_where['sel_building']."' ";
			}
		}

		//probably need to enhance this for multi field search
		$query_str = "SELECT count(*) as total_row FROM registration r 
		LEFT JOIN building b ON (r.building_no = b.building_no) " .
			"WHERE 1=1 " . $where_txt . " " . $where;

		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;


		/*$query_str = "SELECT c.customer_no, c.name, IF(c.activated_date = 0, '', c.activated_date) as activated_date,
								c.package_name, c.monthly_charge, sas.name as status, scc.name as category ,
								c.email_1,c.email_2, c.mobile_num
									FROM customer c 
									INNER JOIN sys_account_status sas ON c.status = sas.status_code 
									INNER JOIN sys_customer_category scc ON c.category = scc.category_code 
									WHERE (c.customer_no LIKE '%$txt_search%' 
											OR c.name LIKE '%$txt_search%' 
											OR c.nric LIKE '%$txt_search%'
											OR c.login_username LIKE '%$txt_search%'
										  ) 
									$query_where 
									ORDER BY c.customer_no  " . 
									"LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];*/

		$query_str = "
		SELECT r.*, b.name AS building_name  
		FROM registration r 
		LEFT JOIN building b ON (r.building_no = b.building_no)
		WHERE 1=1 " . $where_txt . " " . $where . " " . $order_by . "" .
		"LIMIT $page_item_no, " . $_SESSION['config']['max_page_item'];

		$query = $this->db->query($query_str);

		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str;
		return $return_val;
	}

	public function get_registration($id = '')
	{
		$query = $this->db->query("SELECT r.*,d.name as agent_name, r.reg_no AS temp_id  
							FROM registration r LEFT JOIN `dealer` d ON (r.agent_id = d.dealer_no)
							WHERE r.id='" . $this->db->escape_str($id) . "'
							LIMIT 1");

		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();

			/*if($return_val['signup_date'] == 0){
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
			}*/
			$return_val['btn_delete'] = 'enabled';
		} else {
			//Asign blank data for add new customer
			$return_val['id'] = 0;
			$return_val['reg_no'] = '';
			$return_val['name'] = '';
			$return_val['icno'] = '';
			$return_val['phone'] = '';
			$return_val['email'] = '';
			$return_val['type'] = 'r';
			$return_val['comp_name'] = '';
			$return_val['ssm'] = '';
			$return_val['tin'] = '';
			$return_val['bill_unit_no'] = '';
			$return_val['bill_addr_1'] = '';
			$return_val['bill_addr_2'] = '';
			$return_val['bill_addr_3'] = '';
			$return_val['bill_postcode'] = '';
			$return_val['bill_city'] = '';
			$return_val['bill_state'] = '';
			$return_val['ins_unit_no'] = '';
			$return_val['ins_addr_1'] = '';
			$return_val['ins_addr_2'] = '';
			$return_val['ins_addr_3'] = '';
			$return_val['ins_postcode'] = '';
			$return_val['ins_city'] = '';
			$return_val['ins_state'] = '-';
			$return_val['building_no'] = 0;
			$return_val['interested'] = json_encode(array());
			$return_val['preferred_contact_date_time'] = '';
			$return_val['remark'] = '';
			$return_val['agent_id'] = '0';
			$return_val['preferred_install_datetime'] = '';
			$return_val['login_username'] = '';
			$return_val['login_password'] = '';

			$return_val['status'] = 'P';

			$return_val['temp_id'] = rand(10000, 99999);

			$return_val['btn_delete'] = 'disabled';
		}

		return $return_val;
	}

	public function registration_insert($post_back)
	{
		$this->load->helper('change_log');

		$this->load->model('package_model');

		$new = 1;
		$change_text = '';
		if (!empty($post_back['id'])) {
			$regInfo = $this->get_registration($post_back['id']);
			if (!empty($regInfo)) {
				$new = 0;
				$type_arr   	= ['r' => 'Residential', 'b' => 'Commercial', 'o' => 'Commercial - Others'];
				$status_arr 	= ['P' => 'Pending', 'F' => 'Follow Up', 'S' => 'Sales Order'];
				$state_arr    	= array_column($this->common_model->get_state_list() ?? [], 'name', 'state_code');
				$package_arr   	= array_column($this->package_model->get_package_listing_all() ?? [], 'name', 'pkg_no');
				$dealer_arr		= array_column($this->common_model->get_dealer_list() ?? [], 'name', 'dealer_no');

				$change_text .= compare_field_change('Name', $regInfo['name'], $this->db->escape_str($post_back['name']));
				$change_text .= compare_field_change('IC No.', $regInfo['icno'], $this->db->escape_str($post_back['icno']));
				$change_text .= compare_field_change('Phone No.', $regInfo['phone'], $this->db->escape_str($post_back['phone']));
				$change_text .= compare_field_change('Email', $regInfo['email'], $this->db->escape_str($post_back['email']));
				$change_text .= compare_field_change('Registration Product Type', $regInfo['type'], $this->db->escape_str($post_back['type']), $type_arr);
				$change_text .= compare_field_change('Registration Status', $regInfo['status'], $this->db->escape_str($post_back['status']), $status_arr);
				$change_text .= compare_field_change('Company Name', $regInfo['comp_name'], $this->db->escape_str($post_back['comp_name']));
				$change_text .= compare_field_change('SSM#', $regInfo['ssm'], $this->db->escape_str($post_back['ssm']));
				$change_text .= compare_field_change('Billing Address Line 1', $regInfo['bill_addr_1'], $this->db->escape_str($post_back['bill_addr_1']));
				$change_text .= compare_field_change('Billing Address Line 2', $regInfo['bill_addr_2'], $this->db->escape_str($post_back['bill_addr_2']));
				$change_text .= compare_field_change('Billing Address Line 3', $regInfo['bill_addr_3'], $this->db->escape_str($post_back['bill_addr_3']));
				$change_text .= compare_field_change('Billing Postcode', $regInfo['bill_postcode'], $this->db->escape_str($post_back['bill_postcode']));
				$change_text .= compare_field_change('Billing City', $regInfo['bill_city'], $this->db->escape_str($post_back['bill_city']));
				$change_text .= compare_field_change('Billing State', $regInfo['bill_state'], $this->db->escape_str($post_back['bill_state']), $state_arr);
				$change_text .= compare_field_change('Installation Address Line 1', $regInfo['ins_addr_1'], $this->db->escape_str($post_back['ins_addr_1']));
				$change_text .= compare_field_change('Installation Address Line 2', $regInfo['ins_addr_2'], $this->db->escape_str($post_back['ins_addr_2']));
				$change_text .= compare_field_change('Installation Address Line 3', $regInfo['ins_addr_3'], $this->db->escape_str($post_back['ins_addr_3']));
				$change_text .= compare_field_change('Installation Post Code', $regInfo['ins_postcode'], $this->db->escape_str($post_back['ins_postcode']));
				$change_text .= compare_field_change('Installation City', $regInfo['ins_city'], $this->db->escape_str($post_back['ins_city']));
				$change_text .= compare_field_change('Installation State', $regInfo['ins_state'], $this->db->escape_str($post_back['ins_state']), $state_arr);
				$change_text .= compare_field_change('Agent', $regInfo['agent_id'], $this->db->escape_str($post_back['agent_id']), $dealer_arr);
				$change_text .= compare_field_change('Preferred Installation Datetime', $regInfo['preferred_install_datetime'], $this->db->escape_str($post_back['preferred_install_datetime']));
				$change_text .= compare_field_change('Radius Username', $regInfo['login_username'] ?? '', $this->db->escape_str($post_back['login_username'] ?? ''));
				$change_text .= compare_field_change('Radius Password', $regInfo['login_password'] ?? '', $this->db->escape_str($post_back['login_password'] ?? ''));

				$change_text .= compare_json_change('Interested Products', $regInfo['interested'], $post_back['interested'], $package_arr );
			}
		}

		$post_back['reg_no'] = normalize_input($post_back['reg_no'], 'int', 0);
		$post_back['preferred_install_datetime'] = normalize_input($post_back['preferred_install_datetime']);

		$sql = "INSERT INTO `registration` (
			id, reg_no, `name`, icno, phone, email, `type`, comp_name, ssm, tin, bill_unit_no, bill_addr_1, bill_addr_2, bill_addr_3, bill_postcode, bill_city, bill_state, ins_unit_no, ins_addr_1, ins_addr_2, ins_addr_3, ins_postcode, ins_city, ins_state, building_no, interested, preferred_contact_date_time, remark, agent_id, preferred_install_datetime, `login_username`, `login_password`, `status`, created_at, created_by
		) VALUES (
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?   
		) ON DUPLICATE KEY UPDATE 
			`name` = ?, 
			icno = ?,
			phone = ?,
			email = ?,
			`type` = ?,
			comp_name = ?,
			ssm = ?,
			tin = ?,
			bill_unit_no = ?,
			bill_addr_1 = ?,
			bill_addr_2 = ?,
			bill_addr_3 = ?,
			bill_postcode = ?,
			bill_city = ?,
			bill_state = ?, 
			ins_unit_no = ?,
			ins_addr_1 = ?,
			ins_addr_2 = ?,
			ins_addr_3 = ?,
			ins_postcode = ?,
			ins_city = ?,
			ins_state = ?,
			building_no = ?,
			interested = ?,
			preferred_contact_date_time = ?,
			remark = ?,
			agent_id = ?,
			preferred_install_datetime = ?,
			login_username = ?,
			login_password = ?,
			`status`=?, 
			updated_at=?,
			updated_by=? 
		";

		// handle empty values
		if($post_back['building_no'] == '') $post_back['building_no'] = 0;

		$values_array = array(
			/*insert*/
			$post_back['id'],
			$post_back['reg_no'],
			$post_back['name'],
			$post_back['icno'],
			$post_back['phone'],
			$post_back['email'],
			$post_back['type'],
			$post_back['comp_name'],
			$post_back['ssm'],
			$post_back['tin'],
			$post_back['bill_unit_no'],
			$post_back['bill_addr_1'],
			$post_back['bill_addr_2'],
			$post_back['bill_addr_3'],
			$post_back['bill_postcode'],
			$post_back['bill_city'],
			$post_back['bill_state'],
			$post_back['ins_unit_no'],
			$post_back['ins_addr_1'],
			$post_back['ins_addr_2'],
			$post_back['ins_addr_3'],
			$post_back['ins_postcode'],
			$post_back['ins_city'],
			$post_back['ins_state'],
			$post_back['building_no'],
			$post_back['interested'],
			$post_back['preferred_contact_date_time'],
			$post_back['remark'],
			$post_back['agent_id'],
			$post_back['preferred_install_datetime'],
			$post_back['login_username'] ?? '',
			$post_back['login_password'] ?? '',
			$post_back['status'],
			date('Y-m-d H:i:s'),
			$post_back['user_idx'],
			/*update*/
			$post_back['name'],
			$post_back['icno'],
			$post_back['phone'],
			$post_back['email'],
			$post_back['type'],
			$post_back['comp_name'],
			$post_back['ssm'],
			$post_back['tin'],
			$post_back['bill_unit_no'],
			$post_back['bill_addr_1'],
			$post_back['bill_addr_2'],
			$post_back['bill_addr_3'],
			$post_back['bill_postcode'],
			$post_back['bill_city'],
			$post_back['bill_state'],
			$post_back['ins_unit_no'],
			$post_back['ins_addr_1'],
			$post_back['ins_addr_2'],
			$post_back['ins_addr_3'],
			$post_back['ins_postcode'],
			$post_back['ins_city'],
			$post_back['ins_state'],
			$post_back['building_no'],
			$post_back['interested'],
			$post_back['preferred_contact_date_time'],
			$post_back['remark'],
			$post_back['agent_id'],
			$post_back['preferred_install_datetime'],
			$post_back['login_username'] ?? '',
			$post_back['login_password'] ?? '',
			$post_back['status'],
			date('Y-m-d H:i:s'),
			$post_back['user_idx'],
		);

		$this->db->query($sql, $values_array);
		$return_id['id'] 		= $this->db->insert_id();

		if(empty($post_back['id']) && !empty($return_id['id'])) {
			$this->db->query('UPDATE registration r SET r.reg_no = ? WHERE r.id = ?', [$return_id['id'] + 10000000, $return_id['id']]);
		}
		$return_id['reg_no'] 	= $return_id['id'] + 10000000;

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.print_r($values_array, true));
		$method 		= $this->router->method;
		if ($new == 1) {
			$action_desc	= 'a new registration (' . $this->db->escape_str($post_back['name']) . ') has been added';
			$action_category = 'insert';
		} else {
			$action_desc	= 'a registration(' . $this->db->escape_str($post_back['name']) . ') has been edited.' . $change_text;
			$action_category = 'update';
		}
		$action_log =  $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category);

		return $return_id;
	}

	function update_registration_status_to_so($id)
	{
		$this->db->query("UPDATE registration SET `status` = 'S' WHERE id = ? ", array($id));
	}
}
