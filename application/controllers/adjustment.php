<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Adjustment extends DataPage_Controller {

	//~ public $adj_data;

	function __construct() {
        parent::__construct();

		check_acl('adjustment');
		$this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->model('adjustment_model');
    }

    public function index()
    {
		$row_html = $this->adjustment_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_date_start'] = $post_data['txt_date_start'];
			$data['txt_date_end'] = $post_data['txt_date_end'];
		} else {
			$adjustment_filter = get_session_filter('adjustment_filter');
			$data['page_item_no'] = $adjustment_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $adjustment_filter['txt_search'] ?? '';
			$data['txt_date_start'] = $adjustment_filter['txt_date_start'] ?? date('Y-m-01');
			$data['txt_date_end'] = $adjustment_filter['txt_date_end'] ?? date('Y-m-t');
		}

		$data['page_title'] 	= 'Adjustment';
		$data['form_action'] 	= base_url('adjustment');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('adjustment/index',$data);
		$this->load->view('templates/footer');
	}

	function adjustment_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$adjustment_filter = get_session_filter('adjustment_filter');
			$post_data['page_item_no'] = $adjustment_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $adjustment_filter['txt_search'] ?? '';
			$post_data['txt_date_start'] = $adjustment_filter['txt_date_start'] ?? date('Y-m-1');
			$post_data['txt_date_end'] = $adjustment_filter['txt_date_end'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['txt_date_start'])) $post_data['txt_date_start'] = date('Y-m-1');

		if (!isset($post_data['txt_date_end'])) $post_data['txt_date_end'] = date('Y-m-t');

		$total_row 		= 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search      = $post_data['txt_search'];
		$txt_date_start  = $post_data['txt_date_start'];
		$txt_date_end    = $post_data['txt_date_end'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'txt_date_start' => $txt_date_start,
			'txt_date_end' => $txt_date_end
		);
		set_session_filter('adjustment_filter', $session_array);

		if ($page_item_no == '') $page_item_no = 0;
		if (empty($txt_date_start)) $txt_date_start = date('Y-m-1');
		if (empty($txt_date_end)) $txt_date_end = date('Y-m-t');

		$query_where = "";
		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		if( $aclInfo['role_name'] == 'BPO' ){
			$query_where .= " AND c.category IN ( 'b' , 'r' ) ";
		}

		$total_row 	= $this->adjustment_model->get_adj_total_row($txt_search, $txt_date_start, $txt_date_end, $query_where);
		$row 		= $this->adjustment_model->get_adj_listing($txt_search, $txt_date_start, $txt_date_end, $query_where, $page_item_no);

		foreach ($row as $key => $val) {
			$row[$key]['tranx_date'] 		= date_toggle($row[$key]['tranx_date'],$_SESSION['config']['date_format']);
			$row[$key]['status_icon'] 		= $this->createStatusIcon($row[$key]['status']);
			$row[$key]['highlight_class'] 	= $row[$key]['is_approver'] == 1 ? 'highlighted' : '';
		}

		$data['pagination'] = paginationSettingsAjax('bill_adjustment', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('adjustment/adjustment_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	private function createStatusIcon($status) {

		switch ($status) {
			case 'A':
				return "<span class='badge badge-info'>Pending 1</span>";
			case 'B':
				return "<span class='badge badge-primary'>Pending 2</span>";
			case 'C':
				return "<span class='badge badge-success'>Completed</span>";
			case 'D':
				return "<span class='badge badge-secondary'>Draft</span>";
			default:
				return "<span class='badge badge-light'>Unknown</span>";
		}
	}

	function set_form_validation($mode = 'search', $extra_rules = [])
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'adj_no', 'label' => 'Adjustment No', 'rules' => 'trim'),
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
						array('field' => 'adjust_by', 'label' => 'Adjust By', 'rules' => 'trim'),
						array('field' => 'customer_no', 'label' => 'Customer No', 'rules' => 'trim'),
						array('field' => 'customer_name', 'label' => 'Customer Name', 'rules' => 'trim'),
						array('field' => 'tranx_date', 'label' => 'Trans Date', 'rules' => 'trim|required'),
						array('field' => 'bill_type', 'label' => '', 'rules' => 'trim'),
						array('field' => 'amount', 'label' => '', 'rules' => 'trim|required|numeric'),
						array('field' => 'remark', 'label' => '', 'rules' => 'trim'),
					),
				);

		if ($mode == 'save' ) {
			if ( $this->input->post('adjust_by') == 'i' ) {
				$config[$mode][] = array('field' => 'customer_no', 'label' => 'Customer', 'rules' => 'trim|required');
			}
		}

		$rules = isset($config[$mode]) ? $config[$mode] : [];
		if (!empty($extra_rules)) {
			$rules = array_merge($rules, $extra_rules);
		}

		$this->form_validation->set_rules($rules);
	}

	function add_adjustment()
	{
		$this->load->model('common_model');

		$adjustment								= $this->adjustment_model->get_adj();

		$data['input'] 							= isset($adjustment) ? $adjustment['adj_info'] : [];
		$data['input']['doc_lvl_1_approver']	= isset($adjustment['doc_lvl_1_approver']) ? $adjustment['doc_lvl_1_approver'] : [];
		$data['input']['doc_lvl_2_approver']	= isset($adjustment['doc_lvl_2_approver']) ? $adjustment['doc_lvl_2_approver'] : [];
		$data['page_title'] 					= 'Add Adjustment';
		$data['form_action'] 					= base_url('adjustment/save_adjustment');
		$data['sel_building_list'] 				= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['sel_bill_type_list'] 			= $this->common_model->get_bill_type_list();
		$data['sel_area_list'] 					= $this->common_model->get_area_list();

		$data['msg'] 							= $this->msg;

		$this->prepare_adjustment_approval_context($data);

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('adjustment/adjustment_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_adjustment($adj_no='')
	{
		$this->load->model('common_model');

		$adjustment = $this->adjustment_model->get_adj($adj_no);

		if ($adjustment['adj_info']['adj_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Adjustment not found!');
			redirect('adjustment');
		}

		$data['input'] 							= isset($adjustment['adj_info']) ? $adjustment['adj_info'] : [];
		$data['input']['doc_lvl_1_approver']	= isset($adjustment['doc_lvl_1_approver']) ? $adjustment['doc_lvl_1_approver'] : [];
		$data['input']['doc_lvl_2_approver']	= isset($adjustment['doc_lvl_2_approver']) ? $adjustment['doc_lvl_2_approver'] : [];
		$data['page_title'] 					= 'Edit Adjustment';
		$data['form_action'] 					= base_url('adjustment/save_adjustment');
		$data['sel_building_list'] 				= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['sel_bill_type_list'] 			= $this->common_model->get_bill_type_list();
		$data['sel_area_list'] 					= $this->common_model->get_area_list();

		$data['msg'] 							= $this->msg;

		$this->prepare_adjustment_approval_context($data);

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('adjustment/adjustment_detail',$data);
		$this->load->view('templates/footer');
	}

	private function prepare_adjustment_approval_context(&$data)
	{
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'adjustment_approval' ");
		$data['adjustment_approval'] = (int)$config[0]['val'];

		$is_new = ($data['input']['adj_no'] === '');
		$data['approval_required'] = $is_new
			? $data['adjustment_approval']
			: (int)$data['input']['approval_required'];

		if ($data['approval_required'] == 1) {
			$data['lvl1_approvers'] = $this->adjustment_model->get_sys_approvers(1);
			$data['lvl2_approvers'] = $this->adjustment_model->get_sys_approvers(2);
		} else {
			$data['lvl1_approvers'] = [];
			$data['lvl2_approvers'] = [];
		}

		if ($is_new) {
			$data['selected_lvl1_approver'] = array_column($data['lvl1_approvers'], 'user_id');
			$data['selected_lvl2_approver'] = array_column($data['lvl2_approvers'], 'user_id');
		} else {
			$data['selected_lvl1_approver'] = array_column($data['input']['doc_lvl_1_approver'], 'user_id');
			$data['selected_lvl2_approver'] = array_column($data['input']['doc_lvl_2_approver'], 'user_id');
		}

		if ($data['approval_required'] == 1) {
			if (count($data['lvl1_approvers']) === 0 && $data['input']['status'] === 'A') {
				$data['msg']['error_msg'] =
					'All Level 1 Approvers have been removed, so the adjustment status has been changed to Draft.';

				$data['input']['status'] = 'D';
			}

			if (count($data['lvl2_approvers']) === 0 && $data['input']['status'] === 'B') {
				$data['msg']['error_msg'] =
					'All Level 2 Approvers have been removed, so the adjustment status has been changed to Draft.';

				$data['input']['status'] = 'D';
			}
		}

		$is_lvl1 = (
			$data['approval_required'] == 1 &&
			$data['input']['status'] === 'A' &&
			in_array($this->user['idx'], $data['selected_lvl1_approver'])
		);
		$is_lvl2 = (
			$data['approval_required'] == 1 &&
			$data['input']['status'] === 'B' &&
			in_array($this->user['idx'], $data['selected_lvl2_approver'])
		);
		$data['is_approver'] = ($is_lvl1 || $is_lvl2) ? 1 : 0;

		$can_edit = (
			$data['approval_required'] == 0 ||
			$data['is_approver'] ||
			$data['input']['status'] !== 'C'
		) ? 1 : 0;

		$data['js'] = $data['js'] ?? '';
		$data['js'] .= "\nvar canEdit = '$can_edit';";
	}

	function save_adjustment()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'adjustment', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('adj_no') != '') {

			$this->adjustment_model->delete_adj($this->input->post('adj_no'), $this->input->post('customer_no'));

			$this->session->set_flashdata("msg", 'Adjustment deleted!');

			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'adjustment';
			echo json_encode($ajax_return);
			return false;
		}

		$is_new = ($this->input->post('adj_no') == '');

		if ($is_new) {
			$config = $this->db
				->query("SELECT val FROM sys_config WHERE category = 'bills' AND `key` = 'adjustment_approval' LIMIT 1")
				->row_array();
			$approval_required = (int)$config['val'];
			$adjustment = null;
		} else {
			$adjustment = $this->adjustment_model->get_adj($this->input->post('adj_no'));

			if (empty($adjustment['adj_info']) || $adjustment['adj_info']['adj_no'] == '') {
				$ajax_return['err_msg'] = 'Adjustment not found.';
				echo json_encode($ajax_return);
				return false;
			}

			$approval_required = (int)$adjustment['adj_info']['approval_required'];

			if ($approval_required == 1 && $adjustment['adj_info']['status'] === 'C') {
				$ajax_return['err_msg'] = 'Completed adjustment cannot be edited.';
				echo json_encode($ajax_return);
				return false;
			}
		}

		$extra = [];
		if ($approval_required == 1) {
			if (count($this->adjustment_model->get_sys_approvers(1)) > 0) {
				$extra[] = array('field' => 'lvl1_approver', 'label' => 'Level 1 Approver', 'rules' => 'required');
			}
			if (count($this->adjustment_model->get_sys_approvers(2)) > 0) {
				$extra[] = array('field' => 'lvl2_approver', 'label' => 'Level 2 Approver', 'rules' => 'required');
			}
		}

		$this->set_form_validation('save', $extra);
		if ($this->form_validation->run() == false) {
			$ajax_return['err_msg'] = validation_errors();
			$ajax_return['error_keys'] = validation_error_array();
			echo json_encode($ajax_return);
			return false;
		}

		if ($is_new) {
			$adjust_no = $this->adjustment_model->insert_adj(
				$this->input->post('adjust_by'),
				$this->input->post('customer_no'),
				$this->input->post('building'),
				$this->input->post('area'),
				$this->input->post('tranx_date'),
				$this->input->post('bill_type'),
				$this->input->post('adjust_type'),
				$this->input->post('amount'),
				$this->input->post('remark'),
				0,
				$this->input->post('lvl1_approver'),
				$this->input->post('lvl2_approver'),
				$approval_required
			);
		} else {
			$input_post = $this->input->post();
			$current_status = $adjustment['adj_info']['status'];
			$task = $input_post['task'];
			$input_post['approval_required'] = $approval_required;

			if ($approval_required == 0) {
				$input_post['changed_status'] = 'C';
			} else {
				$lvl1_approval_exist = count($this->adjustment_model->get_sys_approvers(1)) > 0;
				$lvl2_approval_exist = count($this->adjustment_model->get_sys_approvers(2)) > 0;

				if ($current_status === 'A' && !$lvl1_approval_exist) {
					$current_status = 'D';
				}
				if ($current_status === 'B' && !$lvl2_approval_exist) {
					$current_status = 'D';
				}

				$input_post['changed_status'] = $current_status;

				if ($task === 'btSendForReview') {
					$input_post['changed_status'] = $lvl1_approval_exist ? 'A' : ($lvl2_approval_exist ? 'B' : 'C');
				} else if ($task === 'btApprove') {
					$input_post['changed_status'] = $current_status == 'A' ? ($lvl2_approval_exist ? 'B' : 'C') : 'C';
				} else if ($task === 'btReject') {
					$input_post['changed_status'] = 'D';
				} else if ($task === 'btComplete') {
					$input_post['changed_status'] = 'C';
				}
			}

			$this->adjustment_model->update_adj($input_post);
			$adjust_no = $this->input->post('adj_no');
		}

		$ajax_return['status'] = 'SUCC';
		$ajax_return['url'] = 'adjustment';
		echo json_encode($ajax_return);
		return false;
	}

	
    public function auto_adjustment()
    {
		$total_row 		= 0;
		$page_item_no 	= $this->input->post('page_item_no');
		$txt_search 	= $this->input->post('txt_search');
		$txt_date_start	= $this->input->post('txt_date_start');
		$txt_date_end 	= $this->input->post('txt_date_end');

		if ($page_item_no == '') {
			$page_item_no = 0;
		}

		if ( empty($txt_date_start) ) {
			$txt_date_start = date('Y-m-1');
		}
		if ( empty($txt_date_end) ) {
			$txt_date_end = date('Y-m-t');
		}
		
		$row 		= $this->adjustment_model->get_auto_adj_listing($txt_search, $txt_date_start, $txt_date_end, $page_item_no);

		foreach ($row as $key => $val) {
			$row[$key]['tranx_date'] = date_toggle($row[$key]['tranx_date'],$_SESSION['config']['date_format']);
		}

		$data['page_title'] 	= 'Adjustment';
		$data['form_action'] 	= base_url('adjustment');
		$data['row_data'] 		= $row;
		$data['txt_search'] 	= $txt_search;
		$data['txt_date_start']	= $txt_date_start;
		$data['txt_date_end'] 	= $txt_date_end;

		// for paginations
		$data['pagination'] = paginationSettings('bill_adjustment', $total_row);
		$data['msg'] 		= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('adjustment/auto_adjustment',$data);
		$this->load->view('templates/footer');
	}
	
	
	
}
