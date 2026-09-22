<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Einvoice extends DataPage_Controller {
	protected $temp_form_data = [];

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload','zip'));
		check_acl('einvoice');
		$this->load->model('einvoice_model');
		$this->load->model('bill_model');
		$this->load->model('common_model');
		$this->load->model('docs_log_model');

		$this->einvoice_status = array('W' => 'Unsubmitted', 'P' => 'Submitted', 'F' => 'Failed/Invalid', 'S' => 'Valid/Submitted', 'C' => 'Cancelled');
    }

    public function index()
    {
		$row_html = $this->einvoice_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_bill_date'] = $post_data['txt_bill_date'];
			$data['sel_status'] = $post_data['sel_status'];
		} else {
			$einvoice_filter          = get_session_filter('einvoice_filter');
			$data['page_item_no'] = $einvoice_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $einvoice_filter['txt_search'] ?? '';
			$data['txt_bill_date'] = $einvoice_filter['txt_bill_date'] ?? date('Y-m-1');
			$data['sel_status'] = $einvoice_filter['sel_status'] ?? 'all';
		}

		$data['page_title'] 	= 'E-invoice List';
		$data['form_action'] 	= base_url('einvoice');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$data['sel_status_list'] = array(
			'all' => 'All',
			'P' => 'Submitted',
			'S' => 'Valid/Submitted',
			'F' => 'Failed/Invalid',
			'U' => 'Unsubmitted'
		);

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('einvoice/index',$data);
		$this->load->view('templates/footer');

	}

	public function einvoice_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$einvoice_filter          = get_session_filter('einvoice_filter');
			$post_data['page_item_no'] = $einvoice_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $einvoice_filter['txt_search'] ?? '';
			$post_data['txt_bill_date'] = $einvoice_filter['txt_bill_date'] ?? date('Y-m-1');
			$post_data['sel_status'] = $einvoice_filter['sel_status'] ?? 'all';
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['txt_bill_date'])) $post_data['txt_bill_date'] = date('Y-m-1');

		if (!isset($post_data['sel_status'])) $post_data['sel_status'] = 'all';

		$total_row       	= 0;
		$page_item_no    	= $post_data['page_item_no'];
		$txt_search      	= $post_data['txt_search'];
		$txt_bill_date   	= $post_data['txt_bill_date'];
		$sel_status   		= $post_data['sel_status'];

		$session_array = array(
			'page_item_no' 		=> $page_item_no,
			'txt_search' 		=> $txt_search,
			'txt_bill_date' 	=> $txt_bill_date,
			'sel_status' 		=> $sel_status,
		);
		set_session_filter('einvoice_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;
		if (empty($txt_bill_date)) $txt_bill_date = date('Y-m-1');

		$get_list 	= $this->einvoice_model->get_einvoice_list($txt_search,$page_item_no,$txt_bill_date,$sel_status);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		foreach ($row as $key => $val) {
			$row[$key]['activated_date'] = date_toggle($row[$key]['activated_date'],$_SESSION['config']['date_format']);
			$row[$key]['last_bill_date'] = date_toggle($row[$key]['last_bill_date'],$_SESSION['config']['date_format']);

			$row[$key]['einvoice_status_text'] = (isset($this->einvoice_status[$val['einvoice_status']]) ? $this->einvoice_status[$val['einvoice_status']] : 'Unsubmitted');
		}

		$data['pagination'] = paginationSettingsAjax('einvoice', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('einvoice/einvoice_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	public function consolidate()
	{
		$row_html = $this->consolidate_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_bill_date'] = $post_data['txt_bill_date'];
		} else {
			$consolidate_filter       = get_session_filter('consolidate_filter');
			$data['page_item_no'] = $consolidate_filter['page_item_no'] ?? 0;
			$data['txt_bill_date'] = $consolidate_filter['txt_bill_date'] ?? date('Y-m-t');
		}

		$data['page_title'] 	= 'E-invoice List (Consolidated)';
		$data['form_action'] 	= base_url('einvoice/consolidate');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('einvoice/consolidate',$data);
		$this->load->view('templates/footer');

	}

	public function consolidate_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$consolidate_filter       = get_session_filter('consolidate_filter');
			$post_data['page_item_no'] = $consolidate_filter['page_item_no'] ?? 0;
			$post_data['txt_bill_date'] = $consolidate_filter['txt_bill_date'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;
		if (!isset($post_data['txt_bill_date'])) $post_data['txt_bill_date'] = date('Y-m-t');

		$total_row 		= 0;
		$page_item_no    = $post_data['page_item_no'];
		$txt_bill_date    = $post_data['txt_bill_date'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_bill_date' => $txt_bill_date
		);
		set_session_filter('consolidate_filter', $session_array);

		$get_list 	= $this->einvoice_model->get_consolidated_einvoice_list($page_item_no,$txt_bill_date);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		foreach ($row as $key => $val) {
			$row[$key]['einvoice_status_text'] = (isset($this->einvoice_status[$val['einvoice_status']]) ? $this->einvoice_status[$val['einvoice_status']] : 'Unsubmitted');
		}

		$data['pagination'] = paginationSettingsAjax('einvoice', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('einvoice/consolidate_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	public function submit_consolidate()
	{

		$row_html = $this->submit_consolidate_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
		} else {
			$adjustment_filter = get_session_filter('submit_consolidate_filter');
			$data['page_item_no'] = $submit_consolidate_filter['page_item_no'] ?? 0;
		}

		$data['page_title'] 	= 'Submit Consolidated';
		$data['form_action'] 	= base_url('einvoice/submit_consolidate');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('einvoice/submit_consolidate',$data);
		$this->load->view('templates/footer');

	}

	public function submit_consolidate_rows($returnOnly = 1)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$submit_consolidate_filter = get_session_filter('submit_consolidate_filter');
			$post_data['page_item_no'] = $submit_consolidate_filter['page_item_no'] ?? 0;
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		$total_row 		= 0;
		$page_item_no    = $post_data['page_item_no'];

		$session_array = array(
			'page_item_no' => $page_item_no,
		);
		set_session_filter('submit_consolidate_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$get_list 	= $this->einvoice_model->get_unsubmitted_bills($page_item_no);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		$data['pagination'] = paginationSettingsAjax('submit_consolidate', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('einvoice/submit_consolidate_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	public function search_taxpayer_tin()
	{
		$name 		= $this->input->post('name');

		if(empty($name)) {
			echo json_encode(array('is_valid' => false));
			return;
		}
		
		$this->load->model('einvoice_api_model');
		$this->load->model('einvoice_config_model');

		$login = $this->einvoice_config_model->login_myinvois_portal();

		$data = [
			'name' => $name,
			'api_path' => $this->config->item('einvoice_api'),
			'token' => $_SESSION['einvoice']['access_token']
		];

		$is_valid = $this->einvoice_api_model->search_taxpayer_tin($data);

		echo json_encode(array('is_valid' => $is_valid));
	}
}