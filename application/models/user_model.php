<?php

class User_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}
	function user_replace($username, $password, $display_name, $mobile_no, $email, $active, $acc_type, $acl_role, $allow_whatsapp, $telegram_id, $allow_telegram, $cur_username, $allow_admin_notification, $allow_tech_notification)
	{
		$allow_whatsapp = normalize_input($allow_whatsapp, 'int', 0);
		$allow_telegram = normalize_input($allow_telegram, 'int', 0);
		$active = normalize_input($active, 'int', 0);
		$allow_admin_notification = normalize_input($allow_admin_notification, 'int', 0);
		$allow_tech_notification = normalize_input($allow_tech_notification, 'int', 0);

		$query_str = "REPLACE INTO user (username, password, display_name,mobile_no,email,active, acc_type, acl_role, allow_whatsapp, telegram_id, allow_telegram, created_by, created_date, allow_admin_notification, allow_tech_notification) VALUES ( " .
			" '$username',  " .
			" md5('$password'), " .
			" '$display_name', " .
			" '$mobile_no', " .
			" '$email', " .
			" $active, " .
			" '$acc_type', " .
			" '$acl_role', " .
			" '$allow_whatsapp', " .
			" '$telegram_id', " .
			" '$allow_telegram', " .
			" '" . $cur_username . "', " .
			" now(), " .
			" '" . $allow_admin_notification . "', " .
			" '" . $allow_tech_notification . "' " .
			" )";

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = 'new user has been created, username:"' . $display_name . '".';
		$action_cat = 'replace into';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, $cust_no);
		$this->db->query($query_str);
	}

	function user_update($change_pass, $display_name, $mobile_no, $email, $active, $acc_type, $acl_role, $allow_whatsapp, $telegram_id, $allow_telegram, $cur_username, $username, $allow_admin_notification, $allow_tech_notification)
	{

		$allow_whatsapp = normalize_input($allow_whatsapp, 'int', 0);
		$allow_telegram = normalize_input($allow_telegram, 'int', 0);
		$active = normalize_input($active, 'int', 0);
		$allow_admin_notification = normalize_input($allow_admin_notification, 'int', 0);
		$allow_tech_notification = normalize_input($allow_tech_notification, 'int', 0);

		$query_str = "UPDATE user SET " .
			"" . $change_pass . "" .
			"display_name='" . $display_name . "', " .
			"mobile_no='" . $mobile_no . "', " .
			"email='" . $email . "', " .
			"active='" . $active . "', " .
			"acc_type='" . $acc_type . "', " .
			"acl_role='" . $acl_role . "', " .
			"allow_whatsapp='" . $allow_whatsapp . "', " .
			"telegram_id='" . $telegram_id . "', " .
			"allow_telegram='" . $allow_telegram . "', " .
			"modified_by='" . $cur_username . "', " .
			"modified_date=now() " . ", " .
			"allow_admin_notification='" . $allow_admin_notification . "', " .
			"allow_tech_notification='" . $allow_tech_notification . "' " .
			"WHERE username='" . $username . "'";

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = 'user details of "' . $display_name . '" have been updated';
		$action_cat = 'update';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, $cust_no);

		$this->db->query($query_str);
	}

	function users_bulk_update($users, $data, $modified_by)
	{
		$this->db->trans_begin();

		$query_str = '';

		foreach ($users as $username) {

			$username = $this->db->escape_str($username);

			// Update user table
			$update_field = array();

			if ($data['allow_admin_notification'] !== '') {
				$update_field[] = "allow_admin_notification='" . $this->db->escape_str($data['allow_admin_notification']) . "'";
			}

			if ($data['allow_tech_notification'] !== '') {
				$update_field[] = "allow_tech_notification='" . $this->db->escape_str($data['allow_tech_notification']) . "'";
			}


			if (!empty($update_field)) {

				$update_field[] = "modified_by='" . $this->db->escape_str($modified_by) . "'";
				$update_field[] = "modified_date=now()";

				$sql = "UPDATE user SET "
					. implode(", ", $update_field)
					. " WHERE username='" . $username . "'";

				$this->db->query($sql);

				$query_str .= $this->db->last_query() . ";\n";
			}


			$user_id = $this->get_user_id_by_username($username);

			if (!empty($user_id)) {
				$user = $this->get_user_detail($username);
				$display_name = '';
				if (!empty($user)) {
					$display_name = $user['display_name'];
				}

				$tech_doc_type = array();
				if ($data['trouble_ticket_assign'] !== '') {
					$tech_doc_type['trouble_ticket'] = $data['trouble_ticket_assign'];
				}
				if ($data['customer_support_assign'] !== '') {
					$tech_doc_type['customer_support'] = $data['customer_support_assign'];
				}
				if (!empty($tech_doc_type)) {
					$this->save_tech_user(
						$user_id,
						$display_name,
						$tech_doc_type
					);
				}
				// Manual Billing Approver
				if (
					$data['mb_lvl1_approver'] !== '' ||
					$data['mb_lvl2_approver'] !== ''
				) {
					$this->bulk_update_approver(
						$user_id,
						'mb',
						1,
						$data['mb_lvl1_approver'],
					);

					$this->bulk_update_approver(
						$user_id,
						'mb',
						2,
						$data['mb_lvl2_approver'],
					);
				}
			}
		}


		// save bulk action log
		if (!empty($query_str)) {

			$model = 'action_log_model';
			$this->load->model($model);

			$ctrl = $this->router->fetch_class();
			$method = $this->router->method;

			$esc_query_str = $this->db->escape_str($query_str);

			$action_desc = 'bulk update user setting';
			$action_cat = 'update';
			$cust_no = '';

			$this->$model->save_action(
				$ctrl,
				$method,
				$esc_query_str,
				$action_desc,
				$action_cat,
				$cust_no
			);
		}


		if ($this->db->trans_status() === FALSE) {

			$this->db->trans_rollback();
			return 0;

		} else {

			$this->db->trans_commit();
			return 1;
		}
	}

	function users_bulk_update_status($users, $status, $modified_by)
	{
		$this->db->trans_begin();

		$query_str = '';
		foreach ($users as $username) {
			$username = $this->db->escape_str($username);
			if ($username == $modified_by)
				continue;
			$sql = "
				UPDATE user
				SET
					active = '" . $status . "',
					modified_by = '" . $this->db->escape_str($modified_by) . "',
					modified_date = NOW()
				WHERE username = '" . $username . "'
			";
			$this->db->query($sql);
			$query_str .= $this->db->last_query() . ";\n";
		}

		$model = "action_log_model";
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$method = $this->router->method;
		$esc_query_str = $this->db->escape_str($query_str);
		$action_desc = ($status) ? 'bulk activate user' : 'bulk deactivate user';
		$action_cat = 'update';
		$this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, '');
		if ($this->db->trans_status() == FALSE) {
			$this->db->trans_rollback();
			return 0;
		} else {
			$this->db->trans_commit();
			return 1;
		}
	}

	function user_delete($username = '')
	{

		$this->db->trans_begin();

		$query = $this->db->query("SELECT idx FROM user WHERE username = ?", [$username]);
		$user = $query->row_array();

		if ($user) {
			$this->delete_tech_user($user['idx'], '', false);
			$this->delete_approver($user['idx'], null, '', false);
		}

		$query_str = "DELETE FROM user WHERE username='" . $this->db->escape_str($username) . "'";

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = 'username:"' . $this->db->escape_str($username) . '" has been deleted.';
		$action_cat = 'delete';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, $cust_no);

		$this->db->query($query_str);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		return true;
	}

	public function get_user_detail($username = '')
	{
		if ($username !== '') {
			$query = $this->db->query("SELECT * FROM user WHERE username = ? LIMIT 1", [$username]);
			if ($query->num_rows() === 0) {
				return [];
			}

			if ($query->num_rows() === 0) {
				return [];
			}

			$row = $query->row_array();

			// Default values
			$row['trouble_ticket_assign'] = 0;
			$row['customer_support_assign'] = 0;

			foreach (['mb', 'ba'] as $doc_type) {
				$row["{$doc_type}_lvl1_approver"] = 0;
				$row["{$doc_type}_lvl2_approver"] = 0;
			}

			// Approver
			$approvers = $this->db->query("SELECT * FROM sys_approver WHERE user_id = ? ORDER BY level ASC", [$row['idx']])->result_array();

			foreach ($approvers as $approver) {
				$key = "{$approver['doc_type']}_lvl{$approver['level']}_approver";
				$row[$key] = 1;
			}

			// Technical User
			$tech_users = $this->db->query("SELECT * FROM technical_user WHERE user_id = ? ORDER BY id ASC", [$row['idx']])->result_array();

			foreach ($tech_users as $tech) {
				$row[$tech['doc_type'] . '_assign'] = 1;
			}

			$row['username_readonly'] = 'readonly';
			$row['acc_exist'] = 1;

		} else {

			$row = [
				'username' => '',
				'display_name' => '',
				'mobile_no' => '',
				'email' => '',
				'telegram_id' => '',

				'active' => 1,
				'acc_type' => 'U',
				'acc_exist' => 0,
				'acl_role' => 0,

				'allow_whatsapp' => 1,
				'allow_telegram' => 1,
				'allow_admin_notification' => 0,
				'allow_tech_notification' => 0,
				'trouble_ticket_assign' => 0,
				'customer_support_assign' => 0
			];

			$doc_type = ['mb', 'ba'];
			foreach ($doc_type as $val) {
				$row[$val . '_lvl1' . '_approver'] = 0;
				$row[$val . '_lvl2' . '_approver'] = 0;
			}

		}

		$checkbox_fields = [
			'active',
			'allow_whatsapp',
			'allow_telegram',
			'allow_admin_notification',
			'allow_tech_notification',
			'trouble_ticket_assign',
			'customer_support_assign',
			'mb_lvl1_approver',
			'mb_lvl2_approver',
			'ba_lvl1_approver',
			'ba_lvl2_approver'
		];

		foreach ($checkbox_fields as $field) {
			$row[$field] = !empty($row[$field]) ? 'checked' : '';
		}

		$row['acc_typeA'] = ($row['acc_type'] === 'A') ? 'selected' : '';
		$row['acc_typeU'] = ($row['acc_type'] === 'U') ? 'selected' : '';

		return $row;
	}

	function toggle_status($username = '')
	{
		if ($username != '') {
			$username = $this->db->escape_str($username);
			//~ $this->db->query("UPDATE user SET active= !active WHERE username='$username'");

			$query_str = "UPDATE user SET active= !active WHERE username='$username'";

			$model = 'action_log_model';
			$this->load->model($model);
			$ctrl = $this->router->fetch_class();
			$esc_query_str = $this->db->escape_str($query_str);
			$method = $this->router->method;
			$action_desc = 'username:"' . $this->db->escape_str($username) . '" status has changed.';
			$action_cat = 'update';
			$cust_no = '';
			$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, $cust_no);

			$this->db->query($query_str);

			$this->session->set_flashdata("msg", "Update success!");
		}
	}

	function password_update($post_back, $username)
	{
		$sess['user']['username'] = $username;

		$query_str = "UPDATE user SET " .
			"password=md5('" . $post_back['input']['password'] . "')," .
			"modified_by='" . $sess['user']['username'] . "', " .
			"modified_date=now() " .
			"WHERE username='" . $sess['user']['username'] . "'";


		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = $sess['user']['username'] . ' has recently changed his/her password.';
		$action_category = 'update';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category, $cust_no);

		return $this->db->query($query_str);
	}

	function get_user_left_join_acl_role($search = '', $acl_role = '', $cfg_filter = array())
	{
		$where = array();

		if ($search != '') {
			$search = $this->db->escape_like_str($search);

			$where[] = "(
				u.username LIKE '%{$search}%'
				OR u.display_name LIKE '%{$search}%'
			)";
		}

		if ($acl_role != '' && $acl_role != 'all')
			$where[] = "u.acl_role = '" . $this->db->escape_str($acl_role) . "'";

		$cfg_conditions = array(
			'admin_notification' => array(
				'yes' => "u.allow_admin_notification = 1",
				'no' => "IFNULL(u.allow_admin_notification, 0) <> 1",
			),
			'tech_notification' => array(
				'yes' => "u.allow_tech_notification = 1",
				'no' => "IFNULL(u.allow_tech_notification, 0) <> 1",
			),
			'customer_support_assign' => array(
				'yes' => "EXISTS (SELECT 1 FROM technical_user tu WHERE tu.user_id = u.idx AND tu.doc_type = 'customer_support')",
				'no' => "NOT EXISTS (SELECT 1 FROM technical_user tu WHERE tu.user_id = u.idx AND tu.doc_type = 'customer_support')",
			),
			'trouble_ticket_assign' => array(
				'yes' => "EXISTS (SELECT 1 FROM technical_user tu WHERE tu.user_id = u.idx AND tu.doc_type = 'trouble_ticket')",
				'no' => "NOT EXISTS (SELECT 1 FROM technical_user tu WHERE tu.user_id = u.idx AND tu.doc_type = 'trouble_ticket')",
			),
			'mb_lvl1_approver' => array(
				'yes' => "EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'mb' AND sa.level = 1)",
				'no' => "NOT EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'mb' AND sa.level = 1)",
			),
			'mb_lvl2_approver' => array(
				'yes' => "EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'mb' AND sa.level = 2)",
				'no' => "NOT EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'mb' AND sa.level = 2)",
			),
			'ba_lvl1_approver' => array(
				'yes' => "EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'ba' AND sa.level = 1)",
				'no' => "NOT EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'ba' AND sa.level = 1)",
			),
			'ba_lvl2_approver' => array(
				'yes' => "EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'ba' AND sa.level = 2)",
				'no' => "NOT EXISTS (SELECT 1 FROM sys_approver sa WHERE sa.user_id = u.idx AND sa.doc_type = 'ba' AND sa.level = 2)",
			),
		);

		foreach ($cfg_conditions as $attr => $conditions) {
			$val = isset($cfg_filter[$attr]) ? $cfg_filter[$attr] : 'all';

			if ($val == 'yes' || $val == 'no') {
				$where[] = "({$conditions[$val]})";
			}
		}

		$where_sql = '';

		if (!empty($where))
			$where_sql = 'WHERE ' . implode(' AND ', $where);

		$query = $this->db->query("
      SELECT u.*, ar.name AS role_name
      FROM user u
      LEFT JOIN acl_role ar
      ON u.acl_role = ar.role_no
      {$where_sql}
      ORDER BY u.username
    ");

		return ($query->num_rows() > 0) ? $query->result_array() : array();
	}

	function get_user_by($field = '', $val = '')
	{
		$return_val = array();
		if ($field == 'username' && $val != '') {
			$select_u_field = " u.username, ";
			//~ $select_u_field .=" u.password, ";
			$select_u_field .= " u.display_name ";

			$query_str = "SELECT " . $select_u_field . " FROM user u WHERE u.username='" . $this->db->escape_str($val) . "' LIMIT 1";
			//~ $query_str = "SELECT * FROM user u WHERE u.username='".$this->db->escape_str($val)."' LIMIT 1";

			$query = $this->db->query($query_str);
			if ($query->num_rows() > 0)
				$return_val = $query->result_array();
		}
		return $return_val;
	}

	function get_user_by_id($id)
	{

		$return_val = array();

		$query_str = "SELECT * FROM user u WHERE u.idx='" . $id . "' ;";
		$query = $this->db->query($query_str);

		if ($query->num_rows() > 0)
			$return_val = $query->row_array();

		return $return_val;

	}

	public function save_tech_user($user_id, $display_name, $tech_doc_type)
	{
		$query_str = '';
		foreach ($tech_doc_type as $doc_type => $val) {
			$delete_sql = "DELETE FROM `technical_user` WHERE `doc_type` = ? AND `user_id` = ?";
			$this->db->query($delete_sql, [$doc_type, $user_id]);
			$query_str .= $this->db->last_query() . ";\n";

			if (!empty($val)) {
				$this->db->insert('technical_user', ['user_id' => $user_id, 'doc_type' => $doc_type]);
				$query_str .= $this->db->last_query() . ";\n";
			}
		}

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = 'technical config of user "' . $display_name . '" have been updated';
		$action_cat = 'update';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, $cust_no);
	}

	public function delete_tech_user($user_id, $display_name = '', $save_action_log = true)
	{
		$delete_sql = "DELETE FROM `technical_user` WHERE `user_id` = ?";
		$this->db->query($delete_sql, [$user_id]);

		if ($save_action_log) {
			$query_str = $this->db->last_query();

			$model = 'action_log_model';
			$this->load->model($model);

			$ctrl = $this->router->fetch_class();
			$method = $this->router->method;
			$esc_query_str = $this->db->escape_str($query_str);

			$action_desc = 'technical config of user "' . $display_name . '" have been deleted';
			$action_cat = 'delete';
			$cust_no = '';

			$this->$model->save_action(
				$ctrl,
				$method,
				$esc_query_str,
				$action_desc,
				$action_cat,
				$cust_no
			);
		}
	}

	public function save_approver($user_id, $display_name, $doc_type, $level1, $level2)
	{
		$query_str = '';
		$delete_sql = "DELETE FROM `sys_approver` WHERE `doc_type` = ? AND `user_id` = ?";

		$this->db->query($delete_sql, [$doc_type, $user_id]);
		$query_str .= $this->db->last_query() . ";\n";

		if (!empty($level1)) {
			$this->db->insert('sys_approver', ['doc_type' => $doc_type, 'level' => 1, 'user_id' => $user_id]);
			$query_str .= $this->db->last_query() . ";\n";
		}
		if (!empty($level2)) {
			$this->db->insert('sys_approver', ['doc_type' => $doc_type, 'level' => 2, 'user_id' => $user_id]);
			$query_str .= $this->db->last_query() . ";\n";
		}

		$model = 'action_log_model';
		$this->load->model($model);
		$ctrl = $this->router->fetch_class();
		$esc_query_str = $this->db->escape_str($query_str);
		$method = $this->router->method;
		$action_desc = 'approver config of user "' . $display_name . '" have been updated';
		$action_cat = 'update';
		$cust_no = '';
		$action_log = $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_cat, $cust_no);
	}

	public function bulk_update_approver($user_id, $doc_type, $level, $value)
	{
		if ($value === '') {
			// No Change
			return;
		}

		// Remove existing level only
		$this->db->query(
			"DELETE FROM sys_approver
         WHERE user_id = ?
         AND doc_type = ?
         AND level = ?",
			array($user_id, $doc_type, $level)
		);

		// Enable
		if ($value == '1') {
			$this->db->insert('sys_approver', array(
				'user_id' => $user_id,
				'doc_type' => $doc_type,
				'level' => $level
			));
		}
	}

	public function delete_approver($user_id, $doc_type, $display_name = '', $save_action_log = true)
	{

		$delete_sql = "DELETE FROM `sys_approver` WHERE `user_id` = ?";
		$binds = [$user_id];

		if ($doc_type !== null && $doc_type !== '') {
			$delete_sql .= " AND `doc_type` = ?";
			$binds[] = $doc_type;
		}

		$this->db->query($delete_sql, $binds);

		$query_str = $this->db->last_query();

		if ($save_action_log) {
			$model = 'action_log_model';
			$this->load->model($model);

			$ctrl = $this->router->fetch_class();
			$method = $this->router->method;
			$esc_query_str = $this->db->escape_str($query_str);

			$action_desc = 'approver config of user "' . $display_name . '" have been deleted';
			$action_cat = 'delete';
			$cust_no = '';

			$this->$model->save_action(
				$ctrl,
				$method,
				$esc_query_str,
				$action_desc,
				$action_cat,
				$cust_no
			);
		}

		return $query_str;
	}

	public function get_user_id_by_username($username)
	{
		$sql = "SELECT u.idx FROM user u WHERE u.username = ?";
		$query = $this->db->query($sql, [$username]);

		$result = $query->row_array();
		return $result['idx'] ?? 0;
	}
}
