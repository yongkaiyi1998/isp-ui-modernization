<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

include_once(APPPATH . 'core/DataPage_Controller.php');
class User extends DataPage_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->helper('custom_helper');
		$this->load->library(array('form_validation', 'session', 'upload'));
		$this->load->helper(array('url', 'html', 'form'));
		check_acl('user');
		$this->load->model('user_model');
		$this->load->model('common_model');

		//SELECT b.name FROM acl_profile a INNER JOIN acl_list b ON a.acl_list LIKE CONCAT('%,', b.idx, ',%') WHERE a.username = 'admin';
	}

	function save_user($parameter = '')
	{
		$username = $this->db->escape_str($this->input->post('username'));
		$password = $this->db->escape_str($this->input->post('password'));
		$display_name = $this->db->escape_str($this->input->post('display_name'));
		$mobile_no = $this->db->escape_str($this->input->post('mobile_no'));
		$email = $this->db->escape_str($this->input->post('email'));
		$active = $this->db->escape_str($this->input->post('active'));
		$acc_type = $this->db->escape_str($this->input->post('acc_type'));
		$acc_exist = $this->input->post('acc_exist');
		$acl_role = $this->input->post('acl_role');
		$allow_whatsapp = $this->db->escape_str($this->input->post('allow_whatsapp'));
		$telegram_id = $this->db->escape_str($this->input->post('telegram_id'));
		$allow_telegram = $this->db->escape_str($this->input->post('allow_telegram'));
		$allow_admin_notification = $this->db->escape_str($this->input->post('allow_admin_notification'));
		$allow_tech_notification = $this->db->escape_str($this->input->post('allow_tech_notification'));
		$trouble_ticket_assign = $this->db->escape_str($this->input->post('trouble_ticket_assign'));
		$customer_support_assign = $this->db->escape_str($this->input->post('customer_support_assign'));
		$mb_lvl1_approver = $this->db->escape_str($this->input->post('mb_lvl1_approver'));
		$mb_lvl2_approver = $this->db->escape_str($this->input->post('mb_lvl2_approver'));
		$ba_lvl1_approver = $this->db->escape_str($this->input->post('ba_lvl1_approver'));
		$ba_lvl2_approver = $this->db->escape_str($this->input->post('ba_lvl2_approver'));

		if (($username != '') && ($password != '' || $acc_exist)) {

			//check phone number format
			$phone_number_validation_regex = "/^\\+?[1-9][0-9]{7,14}$/";
			$mobile_no = str_replace('+', '', $mobile_no);
			if (!preg_match($phone_number_validation_regex, $mobile_no)) {
				$this->save_form_data_to_session();
				$this->session->set_flashdata("error_msg", "Invalid phone number. Format must be e.g 6013xxxxxxx");
				if ($acc_exist) {
					return base_url('user/user_detail/' . urlencode($username));
				}
				return base_url('user/user_detail');
			}

			if ($acc_exist) {
				if ($password != '')
					$change_pass = "password=md5('" . $password . "'),";
				else
					$change_pass = "";

				$user_id = $this->user_model->user_update($change_pass, $display_name, $mobile_no, $email, $active, $acc_type, $acl_role, $allow_whatsapp, $telegram_id, $allow_telegram, $this->user['username'], $username, $allow_admin_notification, $allow_tech_notification);

				//~ $query_str = "UPDATE user SET ".
				//~ 	"" . $change_pass . "" .
				//~ 	"display_name='" . $display_name . "', " .
				//~ 	"active='" . $active . "', " .
				//~ 	"acc_type='" . $acc_type . "', " .
				//~ 	"acl_role='" . $acl_role . "', " .
				//~ 	"modified_by='" . $this->user['username'] . "', " .
				//~ 	"modified_date=now() " .
				//~ 	"WHERE username='" . $username . "'";
				//~
				//~ $model			= 'action_log_model';
				//~ $this->load->model($model);
				//~ $ctrl			= $this->router->fetch_class();
				//~ $esc_query_str	= $this->db->escape_str($query_str);
				//~ $method 		= $this->router->method;
				//~ $action_desc	= 'user details of "'.$display_name.'" have been updated';
				//~ $action_cat		= 'update';
				//~ $cust_no		= '';
				//~ $action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_cat,$cust_no);
				//~ $this->db->query($query_str);
			} else {
				$user_id = $this->user_model->user_replace($username, $password, $display_name, $mobile_no, $email, $active, $acc_type, $acl_role, $allow_whatsapp, $telegram_id, $allow_telegram, $username, $allow_admin_notification, $allow_tech_notification);

				// ~ $query_str = "REPLACE INTO user (username, password, display_name, active, acc_type, acl_role, created_by, created_date) VALUES ( ".
				// ~ 	" '$username',  ".
				// ~ 	" md5('$password'), ".
				// ~ 	" '$display_name', ".
				// ~ 	" $active, ".
				// ~ 	" '$acc_type', ".
				// ~ 	" '$acl_role', " .
				// ~ 	" '". $this->user['username']."', ".
				// ~ 	" now() ".
				// ~ 	" )";
				// ~
				// ~ $model			= 'action_log_model';
				// ~ $this->load->model($model);
				// ~ $ctrl			= $this->router->fetch_class();
				// ~ $esc_query_str	= $this->db->escape_str($query_str);
				// ~ $method 		= $this->router->method;
				// ~ $action_desc	= 'new user has been created, username:"'.$display_name.'".';
				// ~ $action_cat		= 'replace into';
				// ~ $cust_no		= '';
				// ~ $action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_cat,$cust_no);

				// ~ $this->db->query($query_str);
			}

			$user_id = $this->user_model->get_user_id_by_username($username);

			if (!empty($user_id)) {
				// save manual billing approver
				$this->user_model->save_approver($user_id, $display_name, 'mb', $mb_lvl1_approver, $mb_lvl2_approver);
				// save bill adjustment approver
				$this->user_model->save_approver($user_id, $display_name, 'ba', $ba_lvl1_approver, $ba_lvl2_approver);

				$tech_doc_type = ['trouble_ticket' => $trouble_ticket_assign, 'customer_support' => $customer_support_assign];
				$this->user_model->save_tech_user($user_id, $display_name, $tech_doc_type);
			}

			$this->session->set_flashdata("msg", "Account saved!");
		} else {
			$this->save_form_data_to_session();
			$this->session->set_flashdata("error_msg", "Please key in the Password / Username!");
			if ($acc_exist) {
				return base_url('user/user_detail/' . urlencode($username));
			}
			return base_url('user/user_detail');
		}
		echo base_url('user');
		exit;
	}

	function bulk_update()
	{
		$users = $this->input->post('users');

		if (empty($users) || !is_array($users)) {
			echo json_encode(array(
				'success' => 0
			));
			return;
		}

		$data = array(
			'allow_admin_notification' => $this->input->post('allow_admin_notification'),
			'allow_tech_notification' => $this->input->post('allow_tech_notification'),
			'customer_support_assign' => $this->input->post('customer_support_assign'),
			'trouble_ticket_assign' => $this->input->post('trouble_ticket_assign'),
			'mb_lvl1_approver' => $this->input->post('mb_lvl1_approver'),
			'mb_lvl2_approver' => $this->input->post('mb_lvl2_approver'),
			'ba_lvl1_approver' => $this->input->post('ba_lvl1_approver'),
			'ba_lvl2_approver' => $this->input->post('ba_lvl2_approver'),
		);

		$result = $this->user_model->users_bulk_update($users, $data, $this->user['username']);

		echo json_encode(array(
			'success' => $result
		));
	}

	function bulk_update_status()
	{
		$users = $this->input->post('users');
		$status = $this->input->post('status');

		if (empty($users) || !is_array($users)) {
			echo json_encode(array('success' => 0));
			return;
		}

		$status = ($status == 1) ? 1 : 0;
		$result = $this->user_model->users_bulk_update_status(
			$users,
			$status,
			$this->user['username']
		);

		echo json_encode(array(
			'success' => $result
		));
	}

	function delete_user($username = '')
	{
		if (empty($username)) {
			$username = $this->input->post('username');
		}

		$username = urldecode($username);

		$result = '';
		if ($username == $this->user['username']) {
			$this->session->set_flashdata("error_msg", "Cannnot delete own acccount!");
		} else if ($username == '') {
			$this->session->set_flashdata("error_msg", "ID missing!");
		} else {
			//~ $query_str 		= "DELETE FROM users WHERE username='".$this->db->escape_str($username)."'";
			//~ $model			= 'action_log_model';
			//~ $this->load->model($model);
			//~ $ctrl			= $this->router->fetch_class();
			//~ $esc_query_str	= $this->db->escape_str($query_str);
			//~ $method 		= $this->router->method;
			//~ $action_desc	= 'username:"'.$this->db->escape_str($username).'" has been deleted.';
			//~ $action_cat		= 'delete';
			//~ $cust_no		= '';
			//~ $action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_cat,$cust_no);
			//~ $result 		= $this->db->query($query_str);

			$result = $this->user_model->user_delete($username);
			if ($result == 1)
				$this->session->set_flashdata("msg", "User - $username deleted!");
			else
				$this->session->set_flashdata("error_msg", "Having issue delete account.");
		}

		if ($this->input->is_ajax_request())
			return base_url('user/index');
		else
			redirect('user');
	}

	function user_detail($username = '')
	{
		$username = urldecode($username);

		$data = $this->user_model->get_user_detail($username);
		$form_data = $this->session->flashdata('user_form_data');

		if (!empty($form_data)) {

			foreach ($form_data as $key => $value) {

				if (array_key_exists($key, $data)) {
					$data[$key] = $value;
				}
			}

			// password should never be repopulated
			$data['password'] = '';

			// readonly username when editing
			if (!empty($form_data['acc_exist'])) {
				$data['username_readonly'] = 'readonly';
			}

			// checkboxes
			$checkboxes = [
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
				'ba_lvl2_approver',
			];

			foreach ($checkboxes as $cb) {
				$data[$cb] = !empty($form_data[$cb]) ? 'checked' : '';
			}

			// account type
			$data['acc_typeA'] = ($data['acc_type'] == 'A') ? 'selected' : '';
			$data['acc_typeU'] = ($data['acc_type'] == 'U') ? 'selected' : '';

			foreach ($data['sel_acl_role_list'] as &$role) {
				$role['selected'] = ($role['role_no'] == $data['acl_role']) ? 'selected' : '';
			}
			unset($role);
		}
		$data['sel_acl_role_list'] = $this->common_model->get_acl_role_list();
		$data['page_title'] = 'User Detail';
		$data['form_action'] = base_url('user/user_detail');
		$data['telegram_bot_url'] = $this->config->item('telegram_bot_url');

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('user/user_detail', $data);
		$this->load->view('templates/footer');

	}

	function set_status($username = '')
	{
		$username = urldecode($username);
		$this->user_model->toggle_status($username);
		redirect('user');
	}

	function set_form_validation($mode = 'password')
	{
		$config = array(
			'password' => array(
				array('field' => 'input[password]', 'label' => 'Password', 'rules' => 'trim|required|matches[retype_password]'),
				array('field' => 'retype_password', 'label' => 'Retype-Password', 'rules' => 'trim|required'),
			),
		);
		$this->form_validation->set_rules($config[$mode]);
	}

	function save_password()
	{
		$sess = $this->session->userdata;
		if ((empty($sess['user']['username'])) || (!empty($_POST['processtype']['cancel'])) || (!$_POST)) {
			$this->session->set_flashdata("error_msg", 'Access Denied.');
			redirect(base_url());
		}

		if (!empty($_POST['processtype']['save'])) {
			$this->set_form_validation('password');

			$query = '';
			if ($this->form_validation->run() == false) {
				$this->msg['error_msg'] = validation_errors();
			} else {
				$post_back = $this->input->post(NULL, TRUE);
				$query = $this->user_model->password_update($post_back, $sess['user']['username']);

				if ($query == '1')
					$this->msg['msg'] = 'Record saved!';
				else
					$this->msg['error_msg'] = 'Issue saving record.';
			}
		}
		$this->own_view($sess['user']['username']);
	}

	function own_view($username = '')
	{
		$sess = $this->session->userdata;
		$row = array();
		if ($username != $sess['user']['username']) {
			$this->session->set_flashdata("error_msg", "Access Denied.");
			redirect(base_url('home'));
		} else {
			$row = $this->user_model->get_user_by('username', $username);
		}

		$data['data'] = $row;
		$data['msg'] = $this->msg;
		$data['data'][0]['page_title'] = 'Account Settings';
		$data['data'][0]['form_action'] = base_url('user/save_password');

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('user/own_detail', $data);
		$this->load->view('templates/footer');
	}

	public function index($id = null, $index = null)
	{
		$cfg_keys = array(
			'admin_notification',
			'tech_notification',
			'customer_support_assign',
			'trouble_ticket_assign',
			'mb_lvl1_approver',
			'mb_lvl2_approver',
			'ba_lvl1_approver',
			'ba_lvl2_approver'
		);

		$default_filter = array(
			'txt_search' => '',
			'sel_role' => 'all',
		);
		foreach ($cfg_keys as $key) {
			$default_filter['cfg_' . $key] = 'all';
		}

		$post_data = array();
		if (!empty($this->input->post())) {
			$post_data['txt_search'] = trim($this->input->post('txt_search'));
			$post_data['sel_role'] = $this->input->post('sel_role');

			foreach ($cfg_keys as $key) {
				$val = $this->input->post('cfg_' . $key);
				$post_data['cfg_' . $key] = in_array($val, array('yes', 'no')) ? $val : 'all';
			}
		}

		$filter = get_filtered_ajax_data('user', $default_filter, $post_data);
		$f = $filter['data'];

		$txt_search = $f['txt_search'];
		$sel_role = $f['sel_role'];

		$cfg_filter = array();
		foreach ($cfg_keys as $key) {
			$cfg_filter[$key] = isset($f['cfg_' . $key]) ? $f['cfg_' . $key] : 'all';
		}

		$row = $this->user_model->get_user_left_join_acl_role(
			$txt_search,
			$sel_role,
			$cfg_filter
		);

		foreach ($row as $key => $val) {
			$row[$key]['last_login'] =
				datetime_toggle(
					$row[$key]['last_login'],
					$_SESSION['config']['datetime_format']
				);
		}

		$data['page_title'] = 'User';
		$data['form_action'] = base_url('user');
		$data['row_data'] = $row;

		$data['txt_search'] = $txt_search;
		$data['sel_role'] = $sel_role;
		$data['cfg_filter'] = $cfg_filter;

		$data['pagination'] = paginationSettings('user', count($row));
		$data['sel_acl_role_list'] = $this->common_model->get_acl_role_list();

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->load->view('user/index', $data);
		$this->load->view('templates/footer');
	}

	private function save_form_data_to_session()
	{
		$this->session->set_flashdata('user_form_data', $this->input->post());
	}
}
