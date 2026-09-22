<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );

class Building extends DataPage_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));	
		check_acl('building');		
		$this->load->model('building_model');
		$this->load->model('router_model');
		$this->load->model('common_model');
    }
    
    public function index()
    {
		$row_html = $this->building_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_date_start'] = $post_data['txt_date_start'];
			$data['txt_date_end'] = $post_data['txt_date_end'];
		} else {
			$building_filter          = get_session_filter('building_filter');

			$data['page_item_no'] = $building_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $building_filter['txt_search'] ?? '';
			$data['txt_date_start'] = $building_filter['txt_date_start'] ?? date('Y-m-01');
			$data['txt_date_end'] = $building_filter['txt_date_end'] ?? date('Y-m-t');
		}

		$data['page_title'] 	= 'Building';
		$data['form_action'] 	= base_url('building');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;	

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('building/index',$data);
		$this->load->view('templates/footer');
	}

	public function building_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$building_filter          = get_session_filter('building_filter');
			$post_data['page_item_no'] = $building_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $building_filter['txt_search'] ?? '';
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
		set_session_filter('building_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$building_list = $this->building_model->get_building_list($txt_search, $page_item_no);
		$total_row     = $building_list['total_row'];
		$row           = $building_list['row'];

		foreach ($row as $key => $list) {
			$results = $this->building_model->get_building_total_users($list['building_no']);
			
			$total_customer = 0;
			$total = [
				'P' => 0,
				'C' => 0,
				'A' => 0,
				'S' => 0,
				'T' => 0,
			];

			foreach ($results as $result) {
				$status = $result['latest_status'];
				$count = $result['total_customer'];
				$total[$status] = $count;
				$total_customer += $count;
			}

			$row[$key]['p'] = $total['P'];
			$row[$key]['c'] = $total['C'];
			$row[$key]['a'] = $total['A'];
			$row[$key]['s'] = $total['S'];
			$row[$key]['t'] = $total['T'];
			$row[$key]['total_customer'] = $total_customer;
		}

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']   = $row;

		$html = $this->parser->parse('building/building_rows',$data, true);

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
						array('field' => 'name', 'label' => 'Building Name', 'rules' => 'trim|required'),
						array('field' => 'total_unit', 'label' => 'Total Units', 'rules' => 'trim|is_natural'),
						array('field' => 'pppoe', 'label' => 'PPPoE', 'rules' => 'trim'),
						array('field' => 'private', 'label' => 'Private', 'rules' => 'trim'),
						array('field' => 'dealer', 'label' => 'Agent', 'rules' => 'trim'),
						array('field' => 'addr_1', 'label' => 'Address', 'rules' => 'trim|required'),
						array('field' => 'city', 'label' => 'City', 'rules' => 'trim|required'),
						array('field' => 'state', 'label' => 'State', 'rules' => 'trim|required'),
						array('field' => 'postcode', 'label' => 'Postcode', 'rules' => 'trim|required')
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}
	
	function add_building()
	{
		$data['input']				= $this->building_model->get_building();
		$data['page_title'] 		= 'Add Building';
		$data['form_action'] 		= base_url('building/save_building');		
		$data['sel_dealer_list']	= $this->common_model->get_dealer_list();		
		$data['msg'] 				= $this->msg;
		$data['sel_state_list'] 	= $this->common_model->get_state_list();
		$data['sel_router_list']	= $this->router_model->get_routers();
		$data['sel_area_list']		= $this->common_model->get_area_list();
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('building/building_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function edit_building($building_no='')
	{
		$building = $this->building_model->get_building($building_no);
		
		if ($building['building_no'] == '') {
			$this->session->set_flashdata("warning_msg", 'Building not found!');
			redirect('building');
		}
		
		$data['input'] 				= $building;
		$data['page_title'] 		= 'Edit Building';
		$data['form_action'] 		= base_url('building/save_building');		
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();
		$data['msg'] 				= $this->msg;
		$data['sel_state_list'] 	= $this->common_model->get_state_list();
		$data['sel_router_list']	= $this->router_model->get_routers();
		$data['sel_area_list']		= $this->common_model->get_area_list();

		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('building/building_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function save_building()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'building', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('building_no') != '') 
		{			
			$this->building_model->building_delete($this->input->post('building_no'));			
			$this->session->set_flashdata("msg", 'Building deleted!');
			// redirect('building');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'building';
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
				if ( $this->input->post('building_no') == '' ) 
				{	
					$this->building_model->building_insert(
								$this->db->escape_str($this->input->post('name')),
								$this->db->escape_str($this->input->post('total_unit')),
								$this->db->escape_str($this->input->post('pppoe')),
								$this->db->escape_str($this->input->post('private')),
								$this->db->escape_str($this->input->post('dealer')),
								$this->db->escape_str($this->input->post('router_id')),
								$this->db->escape_str($this->input->post('addr_1')),
								$this->db->escape_str($this->input->post('addr_2')),
								$this->db->escape_str($this->input->post('addr_3')),
								$this->db->escape_str($this->input->post('city')),
								$this->db->escape_str($this->input->post('state')),
								$this->db->escape_str($this->input->post('postcode')),
								$this->db->escape_str($this->input->post('area_id')),
								$this->db->escape_str($this->input->post('status')),
								$this->user['username']);		
				}
				else 
				{	
					$this->building_model->building_update($this->input->post(), $this->user['username']);
				}

				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'building';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
}