<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Register extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		
		check_acl('trouble_ticket');
		$this->load->model('ticket_model');
		$this->load->model('common_model');	
    }
	    
    public function index()
    {
		$data['page_title'] 		= 'Registration Form';
		$data['form_action'] 		= base_url('register/index');
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list']	= $this->common_model->get_package_list('', 'a');
		$data['msg'] 				= $this->msg;

		//~ _debug_array( $data['sel_building_list'] );
		//~ _debug_array( $data['sel_package_list'] );

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('register/index',$data);
		$this->load->view('templates/footer');
	}
		
}

