<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Dealer extends DataPage_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		
		check_acl('dealer');
		$this->load->model('dealer_model');
		$this->load->model('common_model');	
    }
    
	function add_dealer()
	{
		$data['input'] 					= $this->dealer_model->get_dealer();
		$data['page_title'] 			= 'Add Agent';
		$data['form_action'] 			= base_url('dealer/save_dealer');		
		$data['sel_status_list']		= $this->common_model->get_acc_status_list();
		$data['sel_state_list']			= $this->common_model->get_state_list();
		$data['sel_building_list']		= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['sel_dealer_type']		= ['A' => 'Agent', 'R' => 'Reseller/ASP', 'D' => 'Direct Sales'];
		$data['msg']					= $this->msg;
		$data['selected_building']		= [];
		$data['disabled_upline_option'] = false;


		$dealer_downline_level = $this->common_model->get_table('sys_config','*', "`key` = 'dealer_downline_level' ")[0]['val'] ?? 1;
		$upline_dealer_list = array_filter(
			$this->common_model->get_dealer_list() ?? [],
			function ($dealer) use ($dealer_downline_level) {
				return substr_count($dealer['upline_tree'], ',') <= $dealer_downline_level;
			}
		);
		$data['upline_list'] = array_column($upline_dealer_list, 'name', 'dealer_no');

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('dealer/dealer_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function edit_dealer($dealer_no='')
	{
		$dealer = $this->dealer_model->get_dealer($dealer_no);
		
		if ($dealer['dealer_no']=='') {
			$this->session->set_flashdata("warning_msg", 'agent not found!');
			redirect('dealer');
		}
		
		$dealer_downline_level = $this->common_model->get_table('sys_config','*', "`key` = 'dealer_downline_level' ")[0]['val'] ?? 1;
		$current_dealer_no = $dealer['dealer_no'];
		$dealer_list = $this->common_model->get_dealer_list() ?? [];
		$upline_dealer_list = array_filter(
			$dealer_list,
			function ($dlr) use ($dealer_downline_level, $current_dealer_no) {
				return substr_count($dlr['upline_tree'], ',') <= $dealer_downline_level
					&& array_search($current_dealer_no, array_filter(explode(',', $dlr['upline_tree']))) === false;
			}
		);

		$data['input'] 					= $dealer;
		$data['page_title'] 			= 'Edit Agent';
		$data['form_action'] 			= base_url('dealer/save_dealer');		
		$data['sel_status_list'] 		= $this->common_model->get_acc_status_list();
		$data['sel_state_list'] 		= $this->common_model->get_state_list();
		$data['sel_building_list'] 		= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['sel_dealer_type']		= ['A' => 'Agent', 'R' => 'Reseller/ASP', 'D' => 'Direct Sales'];
		$data['msg'] 					= $this->msg;
		$data['selected_building']		= !empty($dealer['building']) ? explode(',', rtrim($dealer['building'], ',')) : [];
		$data['disabled_upline_option'] = in_array($current_dealer_no, array_column($dealer_list, 'upline'));
		$data['upline_list'] 			= array_column($upline_dealer_list, 'name', 'dealer_no');

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('dealer/dealer_detail',$data);
		$this->load->view('templates/footer');
	}

	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'dealer_no', 'label' => 'Agent No', 'rules' => 'trim'),
						array('field' => 'name', 'label' => 'Agent Name', 'rules' => 'trim|required'),
						array('field' => 'reg_no', 'label' => 'Company No', 'rules' => 'trim|required'),
						array('field' => 'status', 'label' => 'Status', 'rules' => 'trim'),
						array('field' => 'dealer_type', 'label' => 'Agent Type', 'rules' => 'trim'),
						array('field' => 'pic_name', 'label' => 'PIC Name', 'rules' => 'trim|required'),
						array('field' => 'addr1', 'label' => 'Address 1', 'rules' => 'trim|required'),
						array('field' => 'addr2', 'label' => 'Address 2', 'rules' => 'trim'),
						array('field' => 'addr3', 'label' => 'Address 3', 'rules' => 'trim'),
						array('field' => 'city', 'label' => 'City', 'rules' => 'trim|required'),
						array('field' => 'postcode', 'label' => 'Postcode', 'rules' => 'trim|is_natural'),
						array('field' => 'state', 'label' => 'State', 'rules' => 'trim'),
						array('field' => 'tel_num', 'label' => 'Tel Num', 'rules' => 'trim'),
						array('field' => 'fax_num', 'label' => 'Fax Num', 'rules' => 'trim'),
						array('field' => 'mobile_num', 'label' => 'Mobile Num', 'rules' => 'trim|required'),
						array('field' => 'email', 'label' => 'Email', 'rules' => 'trim|valid_email'),
						array('field' => 'signup_date', 'label' => 'Sign Up Date', 'rules' => 'trim|trim|callback_valid_date|required'),
						array('field' => 'terminated_date', 'label' => 'Terminated Date', 'rules' => 'trim|callback_valid_date'),
						array('field' => 'login', 'label' => 'Preferred Login', 'rules' => 'trim'),
					),
				);
		
		if ($mode == 'comm_save') {
			$package_list = $this->common_model->get_package_list('', 'a');
			foreach ( $package_list as $val ) {
				$package_no = $val['package_no'];
				$config['comm_save'][] = array('field' => "comm_type[$package_no]", 'label' => '', 'rules' => 'trim');
				$config['comm_save'][] = array('field' => "monthly[$package_no]", 'label' => '', 'rules' => 'trim');
				$config['comm_save'][] = array('field' => "onetime[$package_no]", 'label' => '', 'rules' => 'trim');
				$config['comm_save'][] = array('field' => "follow_package[$package_no]", 'label' => '', 'rules' => 'trim');
			}
		}
		$this->form_validation->set_rules($config[$mode]);
	}	
	
	function dealer_comm($dealer_no='')
	{
		if ( empty($dealer_no) ) {
			$dealer_no = $this->input->post('dealer_no');
		}
		
		$dealer_comm 							= $this->dealer_model->get_dealer_comm($dealer_no);
		$dealer_comm['dealer'] 					= $this->dealer_model->get_dealer($dealer_no);
		$dealer_comm['upline']					= $this->dealer_model->get_dealer_by(' AND dealer_no = '.$dealer_comm['dealer']['upline']);
		$upline_comm_list 						= $dealer_comm['dealer']['upline'] != 0 
													? $this->dealer_model->get_dealer_comm($dealer_comm['dealer']['upline'])
													: [];
										
		$upline_comm_array 			= array();
		foreach($upline_comm_list as $category_code => $upline_comm){
			foreach($upline_comm as $val)
				$upline_comm_array[$category_code][$val['package_no']] = $val;
		}

		$dealer_comm['upline_comm'] = $upline_comm_array;

		if ($dealer_comm['dealer']['dealer_no']=='') {
			$this->session->set_flashdata("warning_msg", 'agent not found!');
			redirect('dealer');
		}

		$data 					= $dealer_comm;
		$data['page_title'] 	= 'Agent Commission';
		$data['form_action'] 	= base_url('dealer/dealer_comm_save');
		$data['return_link'] 	= base_url('dealer/edit_dealer/'.$dealer_no);
		$data['msg'] 			= $this->msg;

		$data['category_list']	= array_column($this->common_model->get_category_list(), 'name', 'category_code');
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('dealer/dealer_comm',$data);
		$this->load->view('templates/footer');
	}
	
	function save_dealer()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'dealer', 'error_keys' => array());

		if($this->check_save_validation() == false) {
			$ajax_return['err_msg'] = 'This dealer cannot be reassigned because they currently have agents under them. Please refresh the page and try again.';
			echo json_encode($ajax_return);
			return false;
		}
		
		if ($this->input->post('btDelete') != '' && $this->input->post('dealer_no') != '') 
		{						
			$this->dealer_model->dealer_delete($this->input->post('dealer_no'));			
			$this->session->set_flashdata("msg", 'Agent deleted!');
			// redirect('dealer');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'dealer';
			echo json_encode($ajax_return);
			return false;
		}
		else {
			$new = 0;
			$this->set_form_validation('save');

			$user_chk = true;

			$post_back = $this->input->post();

			$ignore_acc_id = 0;
			// if edit, ignore current acc_id
			if($this->input->post('dealer_no') != ''){
				$dealer = $this->dealer_model->get_dealer($this->input->post('dealer_no'));
				$ignore_acc_id = $dealer['acc_id'] ?? 0;
			}

			$this->load->model('profile_model');
			$chk_return_acc_id = $this->profile_model->check_profile_by_username($post_back['login'], $ignore_acc_id);

			if (!empty($chk_return_acc_id)) {
				$user_chk = false;
			}

			if($this->form_validation->run() == false) {
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else if(!$user_chk) {
				$ajax_return['err_msg'] = 'Login name already exists. Pick another name.';
				echo json_encode($ajax_return);
				return false;
			}
			else {
				if ( $this->input->post('dealer_no') == '' ) {
					$new = 1;
					$dealer_no = $this->dealer_model->dealer_insert($this->user['username']);
				} else {
					$this->dealer_model->dealer_update($this->user['username']);
					$dealer_no = $this->db->escape_str($this->input->post('dealer_no'));
				}

				//if new dealer, create profile auth and record
				if ($new == 1) {
					$post_back = $this->input->post();
					$user_data = array();
					$user_data['login'] = $post_back['login'];
					$user_data['password'] = $post_back['password'];
					$user_data['name'] = $post_back['name'];
					$user_data['type'] = 'a';
					$user_data['mobile'] = $post_back['mobile_num'];
					$user_data['email'] = $post_back['email'];

					$user_data['created_by'] = $this->user['idx'];

					$this->load->model('profile_model');
					$new_user_result = $this->profile_model->register($user_data);

					if (!empty($new_user_result['err'])) {
						//error creating agent profile
					} else {
						//success

						//update acc_id to dealer record
						$this->dealer_model->update_acc_id($dealer_no, $new_user_result['acc_id']);
					}
				}
				
				//$this->msg['msg'] = "Dealer Saved!";
				//$this->edit_dealer($dealer_no);
				//$this->index();
				$this->session->set_flashdata("msg", 'Agent Saved!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'dealer/edit_dealer/'.$dealer_no;
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
	
    public function index()
    {
		$row_html = $this->dealer_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
		} else {
			$dealer_filter            = get_session_filter('dealer_filter');
			$data['page_item_no'] = $dealer_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $dealer_filter['txt_search'] ?? '';
		}

		$data['page_title'] 	= 'Agent';
		$data['form_action'] 	= base_url('dealer');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('dealer/index',$data);
		$this->load->view('templates/footer');
	}

	public function dealer_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$dealer_filter            = get_session_filter('dealer_filter');
			$post_data['page_item_no'] = $dealer_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $dealer_filter['txt_search'] ?? '';
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		$total_row 		= 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search      = $post_data['txt_search'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
		);
		set_session_filter('dealer_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$result = $this->dealer_model->get_dealer_listing($txt_search, $page_item_no);
		$building = array_column(
			$this->common_model->get_building_list('building_no, name', '', 'AND `status` = "a"'),
			'name',
			'building_no'
		);

		foreach ($result['row'] as &$dealer)
		{
			$building_nos = array_filter(explode(',', rtrim($dealer['building'], ',')));
			$dealer['building_names'] = [];

			if (empty($building_nos) && $dealer['upline'] == 0) {
				$dealer['building_names'][] = 'All Buildings';
			}else{
				foreach ($building_nos as $b_no) {
					if (isset($building[$b_no])) {
						$dealer['building_names'][] = $building[$b_no];
					}
				}
			}
		}

		$total_row		= $result['total_row'];
		$row			= $result['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->load->view('dealer/dealer_rows', $data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}
	
	function dealer_comm_save()
	{
		$this->set_form_validation('comm_save');
		if($this->form_validation->run() == false) 
		{
			$this->msg['error_msg'] = validation_errors();
			$this->dealer_comm($this->input->post('dealer_no'));
		}
		else 
		{
			$dealer_no 		= $this->db->escape_str($this->input->post('dealer_no'));
			$this->dealer_model->dealer_comm_insert($this->user['username'],$dealer_no );
			
			//~ $comm_type 		= $this->input->post('comm_type');
			//~ $monthly 		= $this->input->post('monthly');
			//~ $onetime 		= $this->input->post('onetime');
			//~ $follow_package = $this->input->post('follow_package');			
			//~ $dealer_no 		= $this->db->escape_str($this->input->post('dealer_no'));
			//~ $query_str 		= "DELETE FROM dealer_comm WHERE dealer_no = '$dealer_no' ";
						//~ 
			//~ $model				= 'action_log_model';			
			//~ $this->load->model($model);			
			//~ $ctrl				= $this->router->fetch_class();
			//~ $esc_query_str		= $this->db->escape_str($query_str);	
			//~ $method 			= $this->router->method; 				
			//~ $action_desc		= 'a dealer has been deleted';	
			//~ $action_category	= 'delete';		
			//~ $cust_no			= '';//$customer_no; 
			//~ $action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
		//~ 
			//~ $this->db->query($query_str);
			//~ 
			//~ $query_str = "INSERT INTO dealer_comm (dealer_no, package, comm_type, monthly, onetime, follow_package,  
						//~ created_by, created_date) VALUES ";
			//~ foreach ( $comm_type as $key => $val ) {
				//~ $query_str .= "( ".
						//~ "'" . $dealer_no . "', ".
						//~ "'" . $key . "', ".
						//~ "'" . $val . "', ".
						//~ "'" . $monthly[$key] . "', ".
						//~ "'" . $onetime[$key] . "', ".
						//~ "'" . (isset($follow_package[$key]) ? '1':'0' ) . "', ".
						//~ "'" . $this->user['username'] . "', now() ),";
			//~ }					
					//~ 
			//~ $model				= 'action_log_model';
			//~ $this->load->model($model);			
			//~ $ctrl				= $this->router->fetch_class();
			//~ $esc_query_str		= $this->db->escape_str($query_str);	
			//~ $method 			= $this->router->method; 				
			//~ $action_desc		= 'a new dealer comm has been added';	
			//~ $action_category	= 'insert';		
			//~ $cust_no			= '';//$customer_no; 
			//~ $action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
		//~ 
			//~ 
			//~ $this->db->query($query_str);
			
			$this->msg['msg'] = "Agent Commission Saved!";
			$this->edit_dealer($dealer_no);
		}
	}

	function check_save_validation()
	{
		if($this->input->post('dealer_no') == '') {
			return true;
		}

		$original_upline = $this->common_model->get_table('dealer','*', "`dealer_no` = '".$this->input->post('dealer_no')."' ")[0]['upline'] ?? '';
		$new_upline = $this->input->post('upline');

		if ($original_upline != $new_upline) {
			$dealer_list 		= $this->common_model->get_dealer_list() ?? [];
			$current_dealer_no 	= $this->input->post('dealer_no');
			$downline_exist 	= in_array($current_dealer_no, array_column($dealer_list, 'upline'));
			if(!$downline_exist) {
				return true;
			}
			return false;
		}
		return true;
	}

	public function valid_date($date)
	{
		if (empty($date)) {
			return true; // allow empty
		}

		$d = DateTime::createFromFormat('Y-m-d', $date);

		if ($d && $d->format('Y-m-d') === $date) {
			return true;
		}

		$this->form_validation->set_message('valid_date', 'Invalid date format.');
		return false;
	}
}

