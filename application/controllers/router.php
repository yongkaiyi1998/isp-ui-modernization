<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );

class Router extends DataPage_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));	
		check_acl('router');		
		$this->load->model('router_model');
    }
    
    public function index()
    {
		$row_html = $this->router_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
		} else {
			$router_filter            = get_session_filter('router_filter');
			$data['page_item_no'] = $router_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $router_filter['txt_search'] ?? '';
		}

		$data['page_title'] 	= 'Router';
		$data['form_action'] 	= base_url('router');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('router/index',$data);
		$this->load->view('templates/footer');
	}

	public function router_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$router_filter            = get_session_filter('router_filter');
			$post_data['page_item_no'] = $router_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $router_filter['txt_search'] ?? '';
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;
		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		$total_row 		= 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search      = $post_data['txt_search'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search
		);
		set_session_filter('router_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$router_list			= $this->router_model->get_router_list($txt_search,$page_item_no);
		$total_row 				= $router_list['total_row'];
		$row 					= $router_list['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('router/router_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}
	
	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save_router' => array (
						array('field' => 'name', 'label' => 'Router Name', 'rules' => 'trim|required'),
						array('field' => 'ip', 'label' => 'IP Address', 'rules' => 'trim'),
						array('field' => 'ssh_port', 'label' => 'SSH Port', 'rules' => 'trim'),
						array('field' => 'login_id', 'label' => 'Login ID', 'rules' => 'trim'),
						array('field' => 'login_password', 'label' => 'Login Password', 'rules' => 'trim'),
						array('field' => 'login_key', 'label' => 'Login SSH Key', 'rules' => 'trim'),
						array('field' => 'type', 'label' => 'Router Type', 'rules' => 'trim|required'),
						array('field' => 'pppoe', 'label' => 'PPPoE', 'rules' => 'trim'),
						array('field' => 'description', 'label' => 'Description', 'rules' => 'trim')
					),
					'save_router_type' => array (
						array('field' => 'name', 'label' => 'Router Name', 'rules' => 'trim|required'),
						array('field' => 'description', 'label' => 'Description', 'rules' => 'trim')
					)
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}
	
	function add_router()
	{
		$data['input']				= $this->router_model->get_router();
		$data['page_title'] 		= 'Add Router';
		$data['form_action'] 		= base_url('router/save_router');
		$data['msg'] 				= $this->msg;
		$data['router_list']		= $this->router_model->get_all_router_type();
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('router/router_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function edit_router($id='')
	{
		$router = $this->router_model->get_router($id);
		
		if ($router['id'] == '') {
			$this->session->set_flashdata("warning_msg", 'Router not found!');
			redirect('router');
		}
		
		$data['input'] 				= $router;
		$data['page_title'] 		= 'Edit Router Config';
		$data['form_action'] 		= base_url('router/save_router');		
		$data['msg'] 				= $this->msg;
		$data['router_list']		= $this->router_model->get_all_router_type();

		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('router/router_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function save_router()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'router', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('id') != '') 
		{			
			$this->router_model->router_delete($this->input->post('id'), $this->input->post('name'));			
			$this->session->set_flashdata("msg", 'Router deleted!');

			$ajax_return['status'] = 'SUCC';
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{
			$this->set_form_validation('save_router');
			if($this->form_validation->run() == false) 
			{
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else 
			{
				if ( $this->input->post('id') == '' ) 
				{	
					$this->router_model->router_insert(
								$this->db->escape_str($this->input->post('ip')),
								$this->db->escape_str($this->input->post('ssh_port')),
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('description')),
								$this->db->escape_str($this->input->post('login_id')),
								$this->db->escape_str($this->input->post('login_password')),
								$this->db->escape_str($this->input->post('login_key')),
								$this->db->escape_str($this->input->post('type')),
								$this->db->escape_str($this->input->post('ssh_enabled')),
								$this->db->escape_str($this->input->post('pppoe')),
								$this->user['idx']);		
				}
				else 
				{					
					$this->router_model->router_update(
								$this->db->escape_str($this->input->post('ip')),
								$this->db->escape_str($this->input->post('ssh_port')),
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('description')),
								$this->db->escape_str($this->input->post('login_id')),
								$this->db->escape_str($this->input->post('login_password')),
								$this->db->escape_str($this->input->post('login_key')),
								$this->db->escape_str($this->input->post('type')),
								$this->db->escape_str($this->input->post('ssh_enabled')),
								$this->db->escape_str($this->input->post('pppoe')),
								$this->user['idx'],
								$this->db->escape_str($this->input->post('id')));				
				}

				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	public function type()
    {
		$row_html = $this->router_type_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
		} else {
			$router_type_filter       = get_session_filter('router_type_filter');
			$data['page_item_no'] = $router_type_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $router_type_filter['txt_search'] ?? '';
		}

		$data['page_title'] 	= 'Router Type';
		$data['form_action'] 	= base_url('router/type');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('router/type',$data);
		$this->load->view('templates/footer');
	}

	public function router_type_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$router_type_filter       = get_session_filter('router_type_filter');
			$post_data['page_item_no'] = $router_type_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $router_type_filter['txt_search'] ?? '';
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
		set_session_filter('router_type_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$router_list			= $this->router_model->get_router_type_list($txt_search,$page_item_no);
		$total_row 				= $router_list['total_row'];
		$row 					= $router_list['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('router/router_type_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_router_type()
	{
		$data['input']				= $this->router_model->get_router_type();
		$data['page_title'] 		= 'Add New Router Type';
		$data['form_action'] 		= base_url('router/save_router_type');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('router/router_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_router_type($id = '')
	{
		$router_type = $this->router_model->get_router_type($id);
		
		if ($router_type['id'] == '') {
			$this->session->set_flashdata("warning_msg", 'Router not found!');
			redirect('router/type');
		}

		$data['input']				= $router_type;
		$data['page_title'] 		= 'Edit Router Type';
		$data['form_action'] 		= base_url('router/save_router_type');
		$data['msg'] 				= $this->msg;
		$data['configurations']		= json_decode($data['input']['configuration'], true);
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('router/router_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_router_type()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'router/type', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('id') != '') 
		{
			$this->router_model->router_type_delete($this->input->post('id'), $this->input->post('name'));			
			$this->session->set_flashdata("msg", 'Router Type deleted!');
			$ajax_return['status'] = 'SUCC';
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{
			$this->set_form_validation('save_router_type');
			if($this->form_validation->run() == false) 
			{
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else 
			{
				$configuration_arr = [];
				$attribute_arr = $this->input->post('attribute');
				$operation_arr = $this->input->post('operation');
				$value_arr = $this->input->post('value');
				if(!empty($attribute_arr)) {
					for ($i=0; $i < count($attribute_arr); $i++) { 
						$conf_arr = [];
						if(!empty($attribute_arr[$i])) {
							$conf_arr['attribute'] = $attribute_arr[$i];
							$conf_arr['operation'] = $operation_arr[$i];
							$conf_arr['value'] = $value_arr[$i];
							$configuration_arr[] = $conf_arr;
						}
					}
				}

				$configuration_json = json_encode($configuration_arr);

				if ( $this->input->post('id') == '' ) 
				{	
					$this->router_model->router_type_insert(
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('description')),
								$this->db->escape_str($configuration_json),
								$this->user['idx']);		
					
				}
				else 
				{					
					$this->router_model->router_type_update(
								$this->db->escape_str($this->input->post('id')),
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('description')),
								$this->db->escape_str($configuration_json),
								$this->user['idx']);
				
				}

				//update radius
				$this->load->model('package_model');
				$control_by = 'varied';
				if ( $control_by == 'varied' ) {
					$this->package_model->varied_package_update();
				}

				$this->session->set_flashdata("msg", 'Router Type Saved!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;	
			}
		}
	}
}

