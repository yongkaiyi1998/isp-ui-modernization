<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Device_setting extends DataPage_Controller {
	
	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		check_acl('customer');
		$this->load->model('device_setting_model');
		$this->load->model('common_model');			
    }
	
    public function index()
	{
		$txt_search 		= $this->input->post('txt_search');
		$status 			= $this->input->post('status');
		$page_item_no 		= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		$result				= $this->device_setting_model->get_all_device($txt_search, $status, $page_item_no);		
		$row				= $result['row'];
	
		foreach ($row as $key => $val) {
			if($row[$key]['dev_active'] == 1) $row[$key]['dev_active'] = 'Active';
			else $row[$key]['dev_active'] = 'Inactive';
		}
		
		$data['page_title'] 		= 'Device Setting';
		$data['row_data'] 			= $row;
		$data['txt_search'] 		= $txt_search;
		$data['pagination'] 		= paginationSettings('', $result['total_row']);		
		$data['msg'] 				= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('device/index',$data);
		$this->load->view('templates/footer');
	}
	
	public function add_device()
    {
    	$device = $this->device_setting_model->get_device_by_number();

    	$data['input'] 			= $device;
    	$data['page_title'] 	= 'Device Setting';
    	$data['form_action'] 	= base_url('device_setting/save_new_device');
    	$data['msg'] 			= $this->msg;
    	$data['readonly']		= '';

    	$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('device/device_details',$data);
		$this->load->view('templates/footer');
    }

    public function edit_device($dev_number='', $url_after_save = '')
    {

    	$hidden['url_after_save'] = '';
		if($url_after_save != ''){
			$hidden['url_after_save'] = base_url($url_after_save);
		}
	
		$device = $this->device_setting_model->get_device_by_number($dev_number);

		//check the device number is being use or not, if yes, cannot edit the device number
		$dev_enable = '';
		$row = $this->device_setting_model->check_device_outgoing($dev_number);
		if($row > 0) $dev_enable = 'readonly';

    	$data['input'] 			= $device;
		$data['hidden']			= $hidden;
    	$data['page_title'] 	= 'Edit Device';
    	$data['form_action'] 	= base_url('device_setting/update_device');
    	$data['msg'] 			= $this->msg;
    	$data['readonly']		= $dev_enable;

    	$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('device/device_details',$data);
		$this->load->view('templates/footer');
    }

    function save_new_device()
	{
		$this->set_form_validation('save_device'); 
		if($this->form_validation->run() == false) 
		{
			$this->msg['error_msg'] = validation_errors();
			if ($this->input->post('dev_number')=='')  $this->add_device();				
			else  $this->edit_device($this->input->post('dev_number'));
		}
		else{

			//check duplicate device number
			$row = $this->device_setting_model->check_duplicate_device_number($this->input->post('dev_number'));

			if($row > 0)
			{
				$this->session->set_flashdata("msg", 'Device number exist!');
				redirect("device_setting");
			}
			else{
				$this->device_setting_model->add_device($this->input->post('dev_number'), $this->input->post('status'));

				if(empty($_POST['url_after_save']))
				{
					$this->session->set_flashdata("msg", 'Device Saved!');
					redirect("device_setting");
				}
				else
				{
					redirect($_POST['url_after_save']);
				}
			}
		}
	}

	function update_device()
	{
		if ($this->input->post('btDelete') != '' && $this->input->post('dev_number') != '') 
		{			
			$this->device_setting_model->delete_device($this->input->post('dev_number'));			
			$this->session->set_flashdata("msg", 'Device deleted!');
			redirect('device_setting');
		}
		else{
			$this->set_form_validation('save_device'); 
			if($this->form_validation->run() == false) 
			{
				$this->msg['error_msg'] = validation_errors();
				if ($this->input->post('dev_number')=='')  $this->add_device();				
				else  $this->edit_device($this->input->post('dev_number'));
			}
			else{
				$this->device_setting_model->update_device($this->input->post(), $this->input->post('dev_number_h'));

				if(empty($_POST['url_after_save']))
				{
					$this->session->set_flashdata("msg", 'Device Saved!');
					redirect("device_setting");
				}
				else
				{
					redirect($_POST['url_after_save']);
				}
			}
		}
	}

	function set_form_validation($mode = 'search'){
		$config = array(
					'save_device' => array (
						array('field' => 'dev_number', 'label' => 'Device Number', 'rules' => 'trim|required|numeric')
					),
		);
		$this->form_validation->set_rules($config[$mode]);
	}
}	

