<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Action_log extends DataPage_Controller 
{	
	var $current_class;
		
	function __construct()
	{
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));		
		check_acl('action_log');		
		$this->current_class = $this->router->fetch_class();
    }

    function index()
    {	
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'from_date' => '',
			'to_date' => '',
			'customer_no' => '',
			'description' => '',
			'action_by' => 'all'
		];
		$return = get_filtered_ajax_data('action_log_filter', $default_data, $post_data);
		$data = $return['data'];

		$model_2	= 'common_model';
		$this->load->model($model_2);

		$sel_user_list	=  $this->$model_2->get_user_list();	
		$data['msg'] 			= $this->msg;
		$data['sel_user_list'] 	= $sel_user_list;
		$row_html = $this->action_log_rows(1);
		$data['row_html'] = $row_html;	
		
		$data['page_title']		= 'Action Log';
		$data['form_action']	= base_url('action_log');
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('action_log/index',$data);
		$this->load->view('templates/footer');
	}

	function action_log_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'from_date' => '',
			'to_date' => '',
			'customer_no' => '',
			'description' => '',
			'action_by' => 'all'
		];
		$return = get_filtered_ajax_data('action_log_filter', $default_data, $post_data);
		$data = $return['data'];

		$query_where = '';
		$page_item_no = empty($post_data['page_item_no'])? 0 : $post_data['page_item_no'];
		if ($data['action_by'] != 'all' && !empty($data['action_by'])) {
			$query_where .= " AND a.action_by = '" . $data['action_by'] . "'";
		}
		
		if ($data['customer_no'] != '' && !empty($data['customer_no'])) {
			$query_where .= " AND a.customer_no = '" . $data['customer_no'] . "'";
		}
		
		if((!empty($data['from_date'])) || (!empty($data['to_date']))) 
		{
			$from_date = (!empty($data['from_date'])) ? $data['from_date'] : date('Y-m-d');
			$to_date = (!empty($data['to_date'])) ? $data['to_date'] : date('Y-m-d');
			$query_where .= " AND ( a.date_modified BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' )";
		}
		
		$model		= 'action_log_model';
		
		$this->load->model($model);			
		$action_log = $this->$model->get_action_log($this->db->escape_str($data['description']), $page_item_no, $query_where);
		
		foreach( $action_log['data'] as  $key => $val){
			$action_log['data'][$key]['customer_no'] = (empty($action_log['data'][$key]['customer_no']))?'':$action_log['data'][$key]['customer_no'];
		}

		$data['row_data'] 		= $action_log['data'];
		$total_row 				= $action_log['num_rows'];		
		$data['pagination'] 	= paginationSettingsAjax('action_log', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		unset($data['action_by']);
		$html = $this->parser->parse('action_log/action_log_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}
}