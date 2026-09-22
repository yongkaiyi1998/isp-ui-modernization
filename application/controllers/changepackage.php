<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );

class Changepackage extends DataPage_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));	
		// check_acl('building');		
		$this->load->model('change_package_model');
		$this->load->model('common_model');
    }
    
    public function index()
    {
		$row_html = $this->changepackage_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
		} else {
			$changepackage_filter     = get_session_filter('changepackage_filter');
			$data['page_item_no'] = $changepackage_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $changepackage_filter['txt_search'] ?? '';
		}

		$data['page_title'] 	= 'Package Chaging Request';
		$data['form_action'] 	= base_url('changepackage');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('changepackage/index',$data);
		$this->load->view('templates/footer');
	}

	public function changepackage_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$changepackage_filter     = get_session_filter('changepackage_filter');
			$post_data['page_item_no'] = $changepackage_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $changepackage_filter['txt_search'] ?? '';
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
		set_session_filter('changepackage_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$cp_request_list			= $this->change_package_model->get_cp_request_list($txt_search,$page_item_no);
		$total_row					= $cp_request_list['total_row'];
		$row						= $cp_request_list['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('changepackage/changepackage_rows',$data, true);

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
						array('field' => 'dealer', 'label' => 'Agent', 'rules' => 'trim'),
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}

	function view_cp_request($id='')
	{

		$cprequest = $this->change_package_model->view_cp_request($id);

		if (empty($cprequest)) {
			$this->session->set_flashdata("warning_msg", 'Change Package Request Not Found!');
			redirect('changepackage');
		}		
		
		$data['input'] 				= $cprequest;
		$data['page_title'] 		= 'Package Changing Request';
		$data['form_action'] 		= base_url('changepackage/convert_so');
		$data['msg'] 				= $this->msg;
		$data['sel_state_list'] 	= $this->common_model->get_state_list();

		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('changepackage/changepackage_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function convert_so()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'changepackage', 'error_keys' => array());

		$cp_id = $this->input->post('id');
		// when generate form for customer to sign means status in progress ?
		// when sales order created means status completed ?
		if ($this->input->post('btPrint')) 
		{			
			$this->change_package_model->update_cp_status($cp_id, 1);
			$ajax_return['status'] = 'SUCC';
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{		
			$cprequest = $this->change_package_model->view_cp_request($cp_id);
			
			$this->load->model('salesorder_model');
			$so_post = $this->salesorder_model->get_so(0);

			$so_post['so_head']['comp_name'] 			= $cprequest['comp_name'];
			$so_post['so_head']['comp_num'] 			= $cprequest['ssm'];
			$so_post['so_head']['comp_tin'] 			= $cprequest['tin'];
			$so_post['so_head']['cust_name'] 			= $cprequest['acc_name'];

			$so_post['so_head']['date'] 				= date('Y-m-d');
			$so_post['so_head']['del_attn'] 			= $so_post['so_head']['comp_name'];
			$so_post['so_head']['del_company_name'] 	= $so_post['so_head']['comp_name'];
			$so_post['so_head']['del_unit_no'] 			= $cprequest['old_pkg']['inst_unit_no'];
			$so_post['so_head']['del_addr_1'] 			= $cprequest['old_pkg']['inst_addr1'];
			$so_post['so_head']['del_addr_2'] 			= $cprequest['old_pkg']['inst_addr2'];
			$so_post['so_head']['del_addr_3'] 			= $cprequest['old_pkg']['inst_addr3'];
			$so_post['so_head']['del_postcode'] 		= $cprequest['old_pkg']['inst_postcode'];
			$so_post['so_head']['del_city'] 			= $cprequest['old_pkg']['inst_city'];
			$so_post['so_head']['del_tel'] 				= $cprequest['old_pkg']['inst_phone'];
			$so_post['so_head']['del_state'] 			= $cprequest['old_pkg']['inst_state'];
			$so_post['so_head']['bill_attn'] 			= $so_post['so_head']['comp_name'];
			$so_post['so_head']['bill_unit_no'] 		= $cprequest['bill_unit_no'];
			$so_post['so_head']['bill_addr_1'] 			= $cprequest['bill_addr_1'];
			$so_post['so_head']['bill_addr_2'] 			= $cprequest['bill_addr_2'];
			$so_post['so_head']['bill_addr_3'] 			= $cprequest['bill_addr_3'];
			$so_post['so_head']['bill_postcode'] 		= $cprequest['bill_postcode'];
			$so_post['so_head']['bill_city'] 			= $cprequest['bill_city'];
			$so_post['so_head']['bill_tel'] 			= $cprequest['acc_mobileno'];
			$so_post['so_head']['bill_state'] 			= $cprequest['bill_state'];
			$so_post['so_head']['bill_email'] 			= $cprequest['acc_email'];
			$so_post['so_head']['register_type'] 		= strtoupper($cprequest['acc_type'] == 'Business'? 'b' : 'r');
			$so_post['so_head']['reg_type'] 			= strtoupper($cprequest['acc_type'] == 'Business'? 'b' : 'r');
			$so_post['so_head']['icno']					= $cprequest['icno'];
			$so_post['so_head']['building_no']			= $cprequest['old_pkg']['building'];

			$datetime 									= date("Y-m-d H:i:s");
			$so_post['so_head']['created_date'] 		= $datetime;
			$so_post['so_head']['modified_date'] 		= $datetime;

			$so_post['user_idx'] 						= $this->user['idx'];

			$so_id = $this->salesorder_model->salesorder_insert($so_post);

			$this->change_package_model->update_cp_status($cp_id, 2); 

			$this->session->set_flashdata("msg", 'Sales Order Created!');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = "salesorder/add_so/$so_id";
			echo json_encode($ajax_return);
			return false;
		}
	}
}

