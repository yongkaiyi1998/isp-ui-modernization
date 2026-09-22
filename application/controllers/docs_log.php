<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Docs_log extends DataPage_Controller 
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
			'description' => '',
			'page_item_no' => 0
		];
		$return = get_filtered_ajax_data('docs_log_filter', $default_data, $post_data);
		$data = $return['data'];		
		$data['msg'] 			= $this->msg;
		$row_html = $this->docs_log_rows(1);
		$data['row_html'] = $row_html;		
		
		$data['page_title']		= 'Send Log';
		$data['form_action']	= base_url('docs_log');
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('docs_log/index',$data);
		$this->load->view('templates/footer');
    }

	function docs_log_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'from_date' => '',
			'to_date' => '',
			'description' => ''
		];
		$return = get_filtered_ajax_data('docs_log_filter', $default_data, $post_data);
		$data = $return['data'];

		$query_where = '';
		$page_item_no = empty($post_data['page_item_no'])? 0 : $post_data['page_item_no'];
		if((!empty($data['from_date'])) || (!empty($data['to_date']))) 
		{
			$from_date = (!empty($data['from_date'])) ? $data['from_date'] : date('Y-m-d');
			$to_date = (!empty($data['to_date'])) ? $data['to_date'] : date('Y-m-d');
			$query_where .= " AND ( a.date_created BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' )";
		}

		$model		= 'docs_log_model';
		
		$this->load->model($model);	
		$docs_log 	= $this->$model->get_docs_log($this->db->escape_str($data['description']), $page_item_no, $query_where);

		$data['row_data'] 		= $docs_log['data'];
		$total_row 				= $docs_log['num_rows'];		
		$data['pagination'] 	= paginationSettingsAjax('docs_log', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);

		$html = $this->parser->parse('docs_log/docs_log_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	
}