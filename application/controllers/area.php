<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );

class Area extends DataPage_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));	
		check_acl('area');		
		$this->load->model('area_model');
		$this->load->model('common_model');
    }
    
    public function index()
    {
		$row_html = $this->area_rows(1);
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('area_filter', $default_data, $post_data);

		$data = $return['data'];

		$data['page_title'] 	= 'Area';
		$data['form_action'] 	= base_url('area');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;	

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('area/index',$data);
		$this->load->view('templates/footer');
	}

	public function area_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('area_filter', $default_data, $post_data);

		$data = $return['data'];

		$total_row 		= 0;

		$area_list = $this->area_model->get_area_list($data['txt_search'], $data['page_item_no']);
		$total_row     = $area_list['total_row'];
		$row           = $area_list['row'];

		$state_list 	= $this->common_model->get_state_list();
		foreach ($row as $key => &$area) {
			foreach ($state_list as $key => $state) {
				if($area['state'] == $state['state_code']) {
					$area['state_name'] = $state['name'];
				}
			}
		}

		$data['pagination'] = paginationSettingsAjax('', $total_row, $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['row_data']   = $row;

		$html = $this->parser->parse('area/area_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}
	
	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'name', 'label' => 'Area Name', 'rules' => 'trim|required'),
						array('field' => 'postcode', 'label' => 'Postcode', 'rules' => 'trim|required'),
						array('field' => 'city', 'label' => 'City', 'rules' => 'trim|required'),
						array('field' => 'state', 'label' => 'State', 'rules' => 'trim|required'),
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}
	
	function add_area()
	{
		$data['input']				= $this->area_model->get_area();
		$data['page_title'] 		= 'Add Area';
		$data['form_action'] 		= base_url('area/save_area');
		$data['msg'] 				= $this->msg;
		$data['sel_state_list'] 	= $this->common_model->get_state_list();
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('area/area_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function edit_area($id='')
	{
		$area = $this->area_model->get_area($id);
		
		if ($area['id'] == '') {
			$this->session->set_flashdata("warning_msg", 'Area not found!');
			redirect('area');
		}
		
		$data['input'] 				= $area;
		$data['page_title'] 		= 'Edit Area';
		$data['form_action'] 		= base_url('area/save_area');
		$data['msg'] 				= $this->msg;
		$data['sel_state_list'] 	= $this->common_model->get_state_list();

		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('area/area_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function save_area()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'area', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('id') != '') 
		{			
			$this->area_model->area_delete($this->input->post('id'),$this->input->post('name'));			
			$this->session->set_flashdata("msg", 'Area deleted!');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'area';
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{
			$this->set_form_validation('save');
			if($this->form_validation->run() == false) 
			{
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else 
			{
				if ( $this->input->post('id') == 0 ) 
				{	
					$this->area_model->area_insert(
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('addr_1')),
								$this->db->escape_str($this->input->post('addr_2')),
								$this->db->escape_str($this->input->post('addr_3')),
								$this->db->escape_str($this->input->post('city')),
								$this->db->escape_str($this->input->post('state')),
								$this->db->escape_str($this->input->post('postcode')),
								$this->user['idx']);		
				}
				else 
				{					
					$this->area_model->area_update(
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('addr_1')),
								$this->db->escape_str($this->input->post('addr_2')),
								$this->db->escape_str($this->input->post('addr_3')),
								$this->db->escape_str($this->input->post('city')),
								$this->db->escape_str($this->input->post('state')),
								$this->db->escape_str($this->input->post('postcode')),
								$this->user['idx'],
								$this->db->escape_str($this->input->post('id')));
				}

				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'area';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
}