<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function _debug_array($arr) {
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

class MY_Controller extends CI_Controller {

	protected $user;
	protected $vars = array();
	protected $msg;
	protected $tax_rate;

	function __construct() 
	{
        parent::__construct();
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $this->user = $this->session->userdata("user");

        //customer portal
        $this->cuser = $this->session->userdata("cuser");
		
        //~ $this->tax_rate = 0.06; //need to save into config file
        
        //by using defualt php session
        if( empty($this->user) && empty($this->cuser) && !$this->input->is_cli_request() ) {
			if(session_id() != '') {
				session_destroy();
			}
        	exit("<script type='text/javascript'>window.location.href = '".$this->config->item('base_url')."auth/login/';</script>");
        } else {
	       	/*
	       	require_once(APPPATH.'controllers/menu_vars.php');
			*/

	       	if (!empty($this->cuser)) {
	       		$this->load->helper('url');
	       		//customer portal, restrict cannot access other pages in itelco except what is whitelisted
	       		$whitelist = array(
	       			'auth',
	       			'auth/login',
	       			'auth/clogin',
	       			'auth/logout',
	       			'auth/clogout',
	       			'chome',
	       			'chome/error',
	       			'chome/termination_form',
	       			'chome/update_is_termination_terms_accepted',
	       			'chome/upload_signature',
	       			'chome/reject_form',
	       		);
	       		$uri_string = uri_string();
	       		if (!in_array($uri_string, $whitelist)) {
	       			//log_message('error', $uri_string);
	       			redirect('chome');
	       		}
	       	}

	        $this->vars = array(
				'metadatas'=> array(
				),
				'cssfiles' => array(
				),
				'jsfiles' => array(
					'js/debug.js',
				),
				'jscripts' => array(
					'var base_url="'.$this->config->item('base_url').'";',
				),
			);
			
			$this->menu = array(
				'base'		=> $this->config->item('base_url'),
				'logout'	=> 'auth/logout',
				'user'		=> $this->user,
				//'menuitems'	=> $menu_items,
			);
			
			$this->msg['msg'] = '';
			$this->msg['error_msg'] = '';
			$this->msg['warning_msg'] = '';
		}
    }
    
    function get_segment()
	{		
		$seg = 1;
		$segment = array();
		while ($seg != 0)
		{							
			$seg_info 	= '';
				
			$segment[] = $seg_info = $this->uri->segment($seg);
							
			if($seg_info != ''){
				$seg++;	
			}
			else
			{	
				$seg = 0;
				return $segment;
			}				
		}
	}
	
	function form_validation_doc() {
		$this->parser->parse('form_validation_doc',$data);
	}

	/*function check_acl($page, $redirect=true)
	{
		foreach ($_SESSION['acl'][$page]['actions'] as $row)
		{
			$acl_act[$row] = true;
		}

		if (!$acl_act['V'] && $redirect) {
			$this->session->set_flashdata("warning_msg", "You don't have permission!");
			//~ redirect($_SERVER['HTTP_REFERER']);  //BUG: unable to generate flash data
			redirect('home');
		}

		return $acl_act;
	}*/

}
?>
