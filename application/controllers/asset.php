<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );

class Asset extends DataPage_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));	
		check_acl('asset');	
		$this->load->model('common_model');	
		$this->load->model('asset_model');

		$this->load->helper('image_helper');

		$this->service_record_status = array('P' => 'Pending', 'D' => 'Done');
    }

    public function index()
    {
		$row_html = $this->asset_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
		} else {
			$asset_filter             = get_session_filter('asset_filter');
			$data['page_item_no'] = $asset_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $asset_filter['txt_search'] ?? '';
		}

		$data['page_title'] 	= 'Asset';
		$data['form_action'] 	= base_url('asset');
		$data['msg'] 			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/index',$data);
		$this->load->view('templates/footer');
	}

	function asset_rows($returnOnly=0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$asset_filter             = get_session_filter('asset_filter');
			$post_data['page_item_no'] = $asset_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $asset_filter['txt_search'] ?? '';
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		$total_row 		= 0;
		$page_item_no = (empty($post_data['page_item_no'])) ? 0 : $post_data['page_item_no'];
		$txt_search   = $post_data['txt_search'];
		
		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search
		);

		set_session_filter('asset_filter', $session_array);
		
		$asset_list			= $this->asset_model->get_asset_list($txt_search,$page_item_no,$_SESSION['config']['max_page_item']);
		$total_row 			= $asset_list['total_row'];
		$row 				= $asset_list['row'];

		$data['pagination'] = paginationSettingsAjax('asset', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('asset/asset_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	public function planned()
	{
		$row_html = $this->planned_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_date'] = $post_data['txt_date'];
		} else {
			$planned_filter           = get_session_filter('planned_filter');
			$data['page_item_no'] = $planned_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $planned_filter['txt_search'] ?? '';
			$data['txt_date'] = $planned_filter['txt_date'] ?? date('Y-m-t');
		}

		$data['page_title'] 	= 'Planned Maintenance';
		$data['form_action'] 	= base_url('asset/planned');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/planned',$data);
		$this->load->view('templates/footer');
	}

	public function planned_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$planned_filter           = get_session_filter('planned_filter');
			$post_data['page_item_no'] = $planned_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $planned_filter['txt_search'] ?? '';
			$post_data['txt_date'] = $planned_filter['txt_date'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;
		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';
		if (!isset($post_data['txt_date'])) $post_data['txt_date'] = date('Y-m-t');

		$total_row 		= 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search      = $post_data['txt_search'];
		$txt_date    		= $post_data['txt_date'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'txt_date' => $txt_date
		);
		set_session_filter('planned_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;
		if (empty($txt_date)) $txt_date = date('Y-m-t');

		$asset_list			= $this->asset_model->get_upcoming_maint($txt_search,$txt_date,$page_item_no,$_SESSION['config']['max_page_item']);

		foreach ($asset_list['row'] as $key => &$val) {
			if(!empty($val['service_date_time'])) {
				$val['service_date_time'] = date_format(date_create($val['service_date_time']), 'Y-m-d');
			}
			$val['status_icon'] = $this->createStatusIcon($val['status']);
		}

		$total_row 			= $asset_list['total_row'];
		$row 				= $asset_list['row'];		

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('asset/planned_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	private function createStatusIcon($status) {
		switch ($status) {
			case 'P':
				return "<span class='badge badge-warning'>PENDING</span>";
			case 'D':
				return "<span class='badge badge-success'>DONE</span>";
			default:
				return "<span class='badge badge-blue'>NOT SCHEDULED</span>";
		}
	}

	public function service_list()
	{
		$row_html = $this->service_list_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_date_start'] = $post_data['txt_date_start'];
			$data['txt_date_end'] = $post_data['txt_date_end'];
		} else {
			$service_list_filter      = get_session_filter('service_list_filter');
			$data['page_item_no'] = $service_list_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $service_list_filter['txt_search'] ?? '';
			$data['txt_date_start'] = $service_list_filter['txt_date_start'] ?? date('Y-m-01');
			$data['txt_date_end'] = $service_list_filter['txt_date_end'] ?? date('Y-m-t');
		}

		$data['page_title'] 	= 'Service Record List';
		$data['form_action'] 	= base_url('asset/service_list');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/service_list',$data);
		$this->load->view('templates/footer');
	}

	function service_list_rows($returnOnly=0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$service_list_filter      = get_session_filter('service_list_filter');
			$post_data['page_item_no'] = $service_list_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $service_list_filter['txt_search'] ?? '';
			$post_data['txt_date_start'] = $service_list_filter['txt_date_start'] ?? date('Y-m-01');
			$post_data['txt_date_end'] = $service_list_filter['txt_date_end'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['txt_date_start'])) $post_data['txt_date_start'] = date('Y-m-1');

		if (!isset($post_data['txt_date_end'])) $post_data['txt_date_end'] = date('Y-m-t');

		$total_row 		= 0;
		$page_item_no    = (empty($post_data['page_item_no'])) ? 0 : $post_data['page_item_no'];
		$txt_search      = $post_data['txt_search'];
		$txt_date_start  = $post_data['txt_date_start'];
		$txt_date_end    = $post_data['txt_date_end'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'txt_date_start' => $txt_date_start,
			'txt_date_end' => $txt_date_end
		);
		set_session_filter('service_list_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;

		$record_list			= $this->asset_model->get_service_record_list($txt_search,$txt_date_start,$txt_date_end,$page_item_no,$_SESSION['config']['max_page_item']);
		$row 					= $record_list['row'];
		$total_row				= $record_list['total_row'];

		foreach ($row as $key => $val) {
			$row[$key]['status_text'] = isset($this->service_record_status[$val['status']]) ? $this->service_record_status[$val['status']] : '';
			$row[$key]['service_date_time'] = !empty($val['service_date_time']) ? date('Y-m-d', strtotime($val['service_date_time'])) : '';
			$row[$key]['maint_cost'] = number_format(10, 2, '.', ''); // Always sets to 10.00
		}

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('asset/service_list_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	public function transfer_list()
	{
		$row_html = $this->transfer_list_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_date_start'] = $post_data['txt_date_start'];
			$data['txt_date_end'] = $post_data['txt_date_end'];
		} else {
			$transfer_list_filter     = get_session_filter('transfer_list_filter');
			$data['page_item_no'] = $transfer_list_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $transfer_list_filter['txt_search'] ?? '';
			$data['txt_date_start'] = $transfer_list_filter['txt_date_start'];
			$data['txt_date_end'] = $transfer_list_filter['txt_date_end'];
		}

		$data['page_title'] 	= 'Transfer Record List';
		$data['form_action'] 	= base_url('asset/transfer_list');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/transfer_list',$data);
		$this->load->view('templates/footer');
	}

	function transfer_list_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$transfer_list_filter     = get_session_filter('transfer_list_filter');
			$post_data['page_item_no'] = $transfer_list_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $transfer_list_filter['txt_search'] ?? '';
			$post_data['txt_date_start'] = $transfer_list_filter['txt_date_start'] ?? date('Y-m-1');
			$post_data['txt_date_end'] = $transfer_list_filter['txt_date_end'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['txt_date_start'])) $post_data['txt_date_start'] = date('Y-m-1');

		if (!isset($post_data['txt_date_end'])) $post_data['txt_date_end'] = date('Y-m-t');

		$total_row 		= 0;
		$page_item_no 	= $post_data['page_item_no'];
		$txt_search 	= $post_data['txt_search'];		
		$txt_date_start = $post_data['txt_date_start'];
		$txt_date_end 	= $post_data['txt_date_end'];
		
		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'txt_date_start' => $txt_date_start,
			'txt_date_end' => $txt_date_end
		);

		set_session_filter('transfer_list_filter', $session_array);

		$record_list			= $this->asset_model->get_transfer_record_list($txt_search,$txt_date_start,$txt_date_end,$page_item_no,$_SESSION['config']['max_page_item']);
		$total_row 				= $record_list['total_row'];
		$row					= $record_list['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('asset/transfer_list_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}


	public function add_service($asset_id, $maint_id='')
	{
		$chk_temp_id = $this->input->post('temp_id');

		$data['input']				= $this->asset_model->get_maint_record($maint_id);
		$data['page_title'] 		= 'Add Service Record';
		$data['form_action'] 		= base_url('asset/save_service_record');
		$data['msg'] 				= $this->msg;

		$data['input']['asset_id'] = $asset_id;
		$data['asset_info']			= $this->asset_model->get_asset($asset_id);

		$data['input']['service_date_time'] = (!empty($data['input']['service_date_time']) ? date('Y-m-d', strtotime($data['input']['service_date_time'])) : '');

		if (!empty($chk_temp_id)) $data['input']['temp_id'] = $chk_temp_id;

		$file_result = [];

		if (!empty($maint_id)) {
			$file_result	= $this->common_model->get_file_attachment('service', $maint_id);
		}

		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('service', $chk_temp_id, '', 0);
			//file_id, local_path, file_name, remark, created_by, is_print  
			foreach ($leftover_attach as $attach) {
				array_push($file_result, $attach);
			}
		}

		if(!empty($file_result)) {
			foreach ($file_result as $key => $val) {

				$val['local_path'] = stripslashes($val['local_path']);
				$val['local_path'] = str_replace("%", "%25", $val['local_path']);

				$file_info = pathinfo($val['local_path']);
				$extension = strtolower($file_info['extension']);

				$file_result[$key]['extension'] = $extension;
			
				if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
					$file_result[$key]['file_type'] = 'image';

					$thumbnail_filename = 'thumbnail_' . $file_info['filename'] . '.jpeg';

					$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail_filename;

					$file_result[$key]['thumbnail_path'] = (file_exists($this->config->item('upload_path').$thumbnail_path)) ? $this->config->item('upload_url').$thumbnail_path : base_url("images/image.png");
				} elseif (in_array($extension, ['pdf'])) {
					$file_result[$key]['file_type'] = 'pdf';

					$thumbnail_filename = $file_info['filename'] . '-0.jpg';

					$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail_filename;

					$file_result[$key]['thumbnail_path'] = (file_exists($this->config->item('upload_path').$thumbnail_path)) ? $this->config->item('upload_url').$thumbnail_path : base_url("images/pdf.png");
				} elseif (in_array($extension, ['mp4', 'avi', 'mkv', 'mov', 'webm'])) {
					$file_result[$key]['file_type'] = 'video';
				} else {
					$file_result[$key]['file_type'] = 'doc';
				}
				
				$file_result[$key]['local_path'] = $this->config->item('upload_url').$val['local_path'];

				$file_result[$key]['created_by'] = $val['created_by'];
				$file_result[$key]['file_id'] = $val['file_id'];

				$file_result[$key]['is_temp'] = $val['is_temp'];
			}
			$data['attachment'] = $file_result;
		}
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/service_record',$data);
		$this->load->view('templates/footer');
	}

	public function add_transfer($asset_id, $transfer_id='')
	{
		$data['input']				= $this->asset_model->get_transfer_record_by_id($transfer_id);
		$data['page_title'] 		= 'Add Transfer Record';
		$data['form_action'] 		= base_url('asset/save_transfer_record');
		$data['msg'] 				= $this->msg;

		$data['input']['asset_id'] = $asset_id;
		$data['asset_info']			= $this->asset_model->get_asset($asset_id);

		$data['input']['from'] = $data['asset_info']['site'];

		$data['asset_site_list'] = $this->common_model->get_asset_site_list();

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/transfer_record',$data);
		$this->load->view('templates/footer');

	}

	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'serial_no', 'label' => 'Serial No.', 'rules' => 'trim'),
						array('field' => 'vendor', 'label' => 'Vendor', 'rules' => 'trim'),
						array('field' => 'asset_name', 'label' => 'Asset Name', 'rules' => 'trim|required'),
						array('field' => 'asset_code', 'label' => 'Asset Code', 'rules' => 'trim|required'),
						array('field' => 'asset_tag', 'label' => 'Asset Tag', 'rules' => 'trim|required'),
						array('field' => 'site', 'label' => 'Site', 'rules' => 'trim|required'),
						array('field' => 'location', 'label' => 'Location', 'rules' => 'trim'),
						array('field' => 'category_idx', 'label' => 'Category', 'rules' => 'trim'),
						array('field' => 'department', 'label' => 'Department', 'rules' => 'trim'),
						array('field' => 'brand', 'label' => 'Brand', 'rules' => 'trim'),
						array('field' => 'model_no', 'label' => 'Model No.', 'rules' => 'trim'),
						array('field' => 'origin_country', 'label' => 'Country of Origin', 'rules' => 'trim'),
						array('field' => 'purchase_date', 'label' => 'Purchase Date', 'rules' => 'trim'),
						array('field' => 'purchase_price', 'label' => 'Purchase Price', 'rules' => 'trim'),
						array('field' => 'warranty', 'label' => 'Warranty #', 'rules' => 'trim'),
						array('field' => 'warranty_expiry', 'label' => 'Warranty Expiry', 'rules' => 'trim'),
						array('field' => 'owner', 'label' => 'Owner', 'rules' => 'trim'),
						array('field' => 'use_life', 'label' => 'Use Life', 'rules' => 'trim'),
						array('field' => 'depreciation_rate', 'label' => 'Depreciation Rate', 'rules' => 'trim'),
						array('field' => 'current_asset_value', 'label' => 'Current Asset Value', 'rules' => 'trim'),
						array('field' => 'asset_status', 'label' => 'Asset Status', 'rules' => 'trim'),
						array('field' => 'details', 'label' => 'Details', 'rules' => 'trim'),
						array('field' => 'customer_no', 'label' => 'Account', 'rules' => 'trim'),
					),
					'save_service_record' => array(
						array('field' => 'maint_id', 'label' => 'Maint ID', 'rules' => 'trim'),
						array('field' => 'asset_id', 'label' => 'Asset ID', 'rules' => 'trim'),
						array('field' => 'vendor', 'label' => 'Vendor', 'rules' => 'trim|required'),
						array('field' => 'request_date', 'label' => 'Request Date', 'rules' => 'trim|required'),
						array('field' => 'service_date_time', 'label' => 'Service Date', 'rules' => 'trim|required'),
						array('field' => 'maint_cost', 'label' => 'Maint Cost', 'rules' => 'trim|required'),
						array('field' => 'status', 'label' => 'Status', 'rules' => 'trim'),
						array('field' => 'doc_ref', 'label' => 'Doc Ref', 'rules' => 'trim'),
						array('field' => 'description', 'label' => 'Description', 'rules' => 'trim'),
					),
					'save_transfer_record' => array(
						array('field' => 'from', 'label' => 'From Site', 'rules' => 'trim|required'),
						array('field' => 'to', 'label' => 'To Site', 'rules' => 'trim|required'),
						array('field' => 'transfer_date', 'label' => 'Transfer Date', 'rules' => 'trim|required'),
						array('field' => 'description', 'label' => 'Description', 'rules' => 'trim'),
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}

	function add_asset()
	{
		$data['input']				= $this->asset_model->get_asset();
		$data['page_title'] 		= 'Add Asset';
		$data['form_action'] 		= base_url('asset/save_asset');
		$data['msg'] 				= $this->msg;

		$data['asset_category_list'] 		= $this->common_model->get_asset_category_list();
		$data['asset_site_list'] = $this->common_model->get_asset_site_list();
		$data['service_record'] = array();
		$data['transfer_record'] = array();

		$data['contacts_pic'] = array();
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/asset_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_asset($asset_id='')
	{
		$asset = $this->asset_model->get_asset($asset_id);
		
		if ($asset['asset_id'] == '') {
			$this->session->set_flashdata("warning_msg", 'Asset not found!');
			redirect('asset');
		}
		
		$data['input'] 				= $asset;
		$data['page_title'] 		= 'Edit Asset';
		$data['form_action'] 		= base_url('asset/save_asset');		
		$data['msg'] 				= $this->msg;

		$data['asset_category_list'] 		= $this->common_model->get_asset_category_list();
		$data['asset_site_list'] = $this->common_model->get_asset_site_list();
		$data['service_record'] = $this->asset_model->get_service_record($asset['asset_id']);
		$data['transfer_record'] = $this->asset_model->get_transfer_record($asset['asset_id']);

		$data['input']['purchase_date'] = (!empty($data['input']['purchase_date']) ? date('Y-m-d', strtotime($data['input']['purchase_date'])) : '');

		foreach ($data['service_record'] as $key => $val) { 
			$data['service_record'][$key]['status_text'] = (isset($this->service_record_status[$val['status']]) ? $this->service_record_status[$val['status']] : '');

			$data['service_record'][$key]['service_date_time'] = (!empty($val['service_date_time']) ? date('Y-m-d', strtotime($val['service_date_time'])) : '');

		}

		$data['contacts_pic'] = $this->asset_model->get_asset_pic($asset['asset_id']);

		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('asset/asset_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_asset()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'asset', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('asset_id') != '0') 
		{			
			$this->asset_model->asset_delete($this->input->post('asset_id'));			
			$this->session->set_flashdata("msg", 'Asset deleted!');
			// redirect('router');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'asset';
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{
			//check if asset tag exists
			$error = false;

			$chk = $this->asset_model->chk_existing_asset($this->input->post('asset_tag'), $this->input->post('asset_id'));

			if ($chk) {
				$this->msg['error_msg'] = 'Asset tag already exists.';
				$error = true;
			}

			$this->set_form_validation('save');
			$form_validate = $this->form_validation->run();

			if ($form_validate == false) {
				$this->msg['error_msg'] = validation_errors();
				$error = true;

				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}

			if($error) 
			{
				if ($this->input->post('asset_id')=='0') 
				{
					$this->add_asset();
				}
				else 
				{
					$this->edit_asset($this->input->post('asset_id'));
				}
			}
			else 
			{
				$asset_id = 0;
				if ( $this->input->post('asset_id') == '0' ) 
				{	
					$post_data = $this->input->post();
					$post_data['created_by'] = $this->user['idx'];
					$post_data['created_at'] = date('Y-m-d H:i:s');
					$asset_id = $this->asset_model->asset_insert($post_data);		
				}
				else 
				{			
					$post_data = $this->input->post();
					$post_data['updated_by'] = $this->user['idx'];
					$post_data['updated_at'] = date('Y-m-d H:i:s');		
					$this->asset_model->asset_update($post_data);		
					$asset_id = $this->input->post('asset_id');		
				}

				$arr['asset_id'] = $asset_id;
				$arr['start_date'] = !empty($this->input->post('start_date'))
					? $this->input->post('start_date')
					: '1970-01-01';
				$arr['next_maint_date'] = !empty($this->input->post('next_maint_date'))
					? $this->input->post('next_maint_date')
					: '1970-01-01';
				$arr['end_date'] = !empty($this->input->post('end_date'))
					? $this->input->post('end_date')
					: '1970-01-01';
				$arr['interval_value'] = is_numeric($this->input->post('interval_value'))
					? (int)$this->input->post('interval_value')
					: 0;
				$arr['interval_unit'] = $this->input->post('interval_unit') ?: 'd';
				
				$this->asset_model->asset_update_planned($arr);

				//maintenance PIC
				$this->asset_model->delete_contacts_setting_asset($asset_id);
				$post_data = $this->input->post();
				if (isset($post_data['user_id'])) {
					foreach ($post_data['user_id'] as $key => $val) {
						$arr = array();
						$arr['asset_id'] = $asset_id;
						$arr['user_id'] = $post_data['user_id'][$key];
						$arr['email'] = $post_data['email'][$key];
						$arr['phone'] = $post_data['phone'][$key];
						$arr['telegram_id'] = $post_data['telegram_id'][$key];
						$this->asset_model->save_contacts_setting_asset($arr);
					}
				}

				// $this->session->set_flashdata("msg", 'Asset Saved!');
				// redirect('asset');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'asset';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function save_service_record() {
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'asset/service_list', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('maint_id') != '0') {

			$this->asset_model->service_record_delete($this->input->post('asset_id'),$this->input->post('maint_id'));
			// $this->session->set_flashdata("msg", 'Service record deleted!');

			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'asset/service_list';
			echo json_encode($ajax_return);
			return false;
			// redirect('asset');
		} else {
			$this->set_form_validation('save_service_record');
			if($this->form_validation->run() == false) {
				$this->msg['error_msg'] = validation_errors();

				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				// $this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));

				// $this->add_service($this->input->post('asset_id'), $this->input->post('maint_id'));
			} else {
				//validated success

				//_debug_array($_FILES); exit;

				$maint_id = '';
				$post_back = $this->input->post();
				
				if ( $this->input->post('maint_id') == '0' ) {
					$post_back['created_by'] = $this->user['idx'];
					$maint_id = $this->asset_model->service_record_insert($post_back);
				}else{
					$post_back['updated_by'] = $this->user['idx'];
					$this->asset_model->service_record_update($post_back);
					
					$maint_id = $this->db->escape_str($this->input->post('maint_id'));
				}

				$chk_temp_id = $this->input->post('temp_id');
				if (!empty($chk_temp_id)) {
					$leftover_attach = $this->common_model->get_temp_file_attachment('service', $chk_temp_id, '', 0, 0); 
					foreach ($leftover_attach as $val) {

						$val['local_path'] = stripslashes($val['local_path']);
						$val['local_path'] = str_replace("%", "%25", $val['local_path']);

						$tmp_file_info = pathinfo($val['local_path']);

						$tmp_path = $this->config->item('upload_path').$val['local_path'];

						$file_name 		= $tmp_file_info['filename'].'.'.$tmp_file_info['extension'];
						$prefix_n_file_name = $file_name;
						$file_path 		= $this->config->item('upload_path')."/upload/" . $file_name;
						$local_path 	= "/upload/" . $file_name;
						$remark 		= $val['remark'];
						$is_print		= $val['is_print'];

						if (copy($tmp_path, $file_path)) {
							$file_info = pathinfo($local_path);
							$extension = strtolower($file_info['extension']);
							
							if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
								list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
								resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
							} else if (in_array($extension, ['pdf'])) {
								//do pdf conversion, each page to one image and save it in /res
								if (extension_loaded('imagick')){
									_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/upload/");
								}
							}
							
							$this->common_model->insert_file_attachment($file_name , $local_path, 'service', $maint_id, $remark, $is_print, '', 0, 0, $this->user['idx']);

							//unlink file
							$this->common_model->remove_temp_attachment($val['file_id']);
						}

					}
				}

				//handling attachments	
				if (isset($_FILES['attach_attachment'])) {
					$total = count($_FILES['attach_attachment']['name']);
					$attach_attachment_remark = $this->input->post('attach_attachment_remark');
					//$attach_attachment_print = $this->input->post('po_attachment_print');
					$attach_attachment_print = array();

					foreach ($_FILES['attach_attachment'] as $key => $val) {
						$_FILES['attach_attachment'][$key] = array_values($val);
					}

					//create initial values for checkbox that are not ticked so array_values will not screw up the ordering
					foreach ($attach_attachment_remark as $key => $val) {
						//foreach value
						if (!isset($attach_attachment_print[$key])) {
							$attach_attachment_print[$key] = 0;
						}
					}
					ksort($attach_attachment_print);

					//log_message('error', print_r($po_attachment_remark, true));
					//log_message('error', print_r($po_attachment_print, true));

					$attach_attachment_remark = array_values($attach_attachment_remark);
					$attach_attachment_print = array_values($attach_attachment_print);
					
					// Loop through each file
					for( $i=0 ; $i < $total ; $i++ ) {
						//Get the temp file path
						$tmp_path = $_FILES['attach_attachment']['tmp_name'][$i];

						//Make sure we have a file path
						if ($tmp_path != "") {
							$prefix 		= date('YmdHis');
						$file_name 		= $_FILES['attach_attachment']['name'][$i];
						$prefix_n_file_name = $prefix . "_" . $file_name;
						$file_path 		= $this->config->item('upload_path')."/upload/" . $prefix_n_file_name;
						$local_path 	= "/upload/" . $prefix_n_file_name;
						$remark 		= $attach_attachment_remark[$i];
						$is_print		= (isset($attach_attachment_print[$i])?$attach_attachment_print[$i]:0);

							if (move_uploaded_file($tmp_path, $file_path)) {
								$file_info = pathinfo($local_path);
								$extension = strtolower($file_info['extension']);
								
								if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
									list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
									resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
								} else if (in_array($extension, ['pdf'])) {
									//do pdf conversion, each page to one image and save it in /res
									if (extension_loaded('imagick')){
										_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/upload/");
									}
								}
								
								$this->common_model->insert_file_attachment($file_name , $local_path, 'service', $maint_id, $remark, $is_print, '', '', 0, $this->user['idx']);
							}
						}
					}
				}

				//if status is done
				if ($post_back['status'] == 'D') {
					$this->asset_model->set_last_maint_date($post_back['asset_id'], $post_back['service_date_time']);

					$asset = $this->asset_model->get_asset($post_back['asset_id']);
					if(!empty($asset['interval_value']) && !empty($asset['interval_unit'])) {
						$date_add_string = '';
						if($asset['interval_unit'] == 'd') {
							$date_add_string = $asset['interval_value'] . ' ' . 'days';
						} else if($asset['interval_unit'] == 'm') {
							$date_add_string = $asset['interval_value'] . ' ' . 'months';
						} else if($asset['interval_unit'] == 'y') {
							$date_add_string = $asset['interval_value'] . ' ' . 'years';
						}
						$service_date_time = new DateTime($post_back['service_date_time']);
						$next_maint_date =  date_format(date_add($service_date_time,date_interval_create_from_date_string($date_add_string)), 'Y-m-d');
						$this->asset_model->set_next_maint_date($post_back['asset_id'], $next_maint_date);
					}
				}

				// $this->session->set_flashdata("msg", 'Service Record Saved!');
				// redirect('asset/service_list');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'asset/service_list';
				echo json_encode($ajax_return);
				return false;
			}
		}	

	}

	function save_transfer_record()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'asset/transfer_list', 'error_keys' => array());

		$this->set_form_validation('save_transfer_record');
		if($this->form_validation->run() == false) {
			// $this->msg['error_msg'] = validation_errors();
			// $this->add_transfer($this->input->post('asset_id'), $this->input->post('transfer_id'));
			$ajax_return['err_msg'] = validation_errors();
			$ajax_return['error_keys'] = validation_error_array();
			echo json_encode($ajax_return);
			return false;

		} else {

			//validated success
			$post_back = $this->input->post();
			$post_back['created_by'] = $this->user['idx'];

			//validation
			if ($post_back['from'] == $post_back['to']) {
				$ajax_return['err_msg'] = 'Cannot be same location for From and To';
				echo json_encode($ajax_return);
				return false;
			}

			$transfer_id = $this->asset_model->transfer_record_insert($post_back);

			//change current site detail on asset
			$this->asset_model->update_asset_site($this->input->post('asset_id'), $this->input->post('to'));

			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'asset/transfer_list';
			echo json_encode($ajax_return);
			return false;

		}
	}

	private function gen_post_attachments($file_arr=array())
	{
		$this->load->model('common_model');

		if (!empty($file_arr)) {	

			$post_back_attach = array();

			$total = count($file_arr['name']);
			$attach_attachment_remark = $this->input->post('attach_attachment_remark');
			$attach_attachment_print = array();

			$temp_id = $this->input->post('temp_id');

			foreach ($file_arr as $key => $val) {
				$file_arr[$key] = array_values($val);
			}

			$attach_attachment_remark = array_values($attach_attachment_remark);

			for( $i=0 ; $i < $total ; $i++ ) {
			  	$tmp_path = $file_arr['tmp_name'][$i];

			  	if ($tmp_path != "") {
					$prefix 		= date('YmdHis');
					$file_name 		= $file_arr['name'][$i];;
					$prefix_n_file_name = $prefix . "_" . $file_name;
					$file_path 		= $this->config->item('upload_path')."/temp/" . $prefix_n_file_name;
					$local_path 	= "/temp/" . $prefix_n_file_name;
					$remark 		= $attach_attachment_remark[$i];
					$is_print		= (isset($attach_attachment_print[$i])?$attach_attachment_print[$i]:0);

					if (move_uploaded_file($tmp_path, $file_path)) {
						$file_info = pathinfo($local_path);
						$extension = strtolower($file_info['extension']);
						
						if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
							list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
							resize_image_tmp($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
						} else if (in_array($extension, ['pdf'])) {
							if (extension_loaded('imagick')){
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/temp/");
							}
						}
						
						$this->common_model->insert_tmp_file_attachment($file_name , $local_path, 'service', $temp_id, $remark, $is_print, '', 0, 0, $this->user['idx']);
					}

		    	}
			}
		}

	}

}