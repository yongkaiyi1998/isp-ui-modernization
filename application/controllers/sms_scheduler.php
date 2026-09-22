<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Sms_scheduler extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));

		check_acl('customer');
		$this->load->model('sms_scheduler_model');
		$this->load->model('sms_outgoing_model');
		$this->load->model('common_model');
    }

	public function ajax_set_data()
	{
		$table = 'sms_scheduler';
		$this->db->from($table);

		$query = '';
		if(!empty($_GET['query']))
		{
			$keyword =$this->db->escape_str($_GET['query']);
			$this->db->where("`sms_title` LIKE '%$keyword%'");

			$q = $this->db->get();
			$query = $q->result_array();
		}
		echo json_encode($query);
	}

    public function index()
    {
		$total_row 		= 0;
		$page_item_no 	= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		$txt_search 	= $this->input->post('txt_search');


		$result = $this->sms_scheduler_model->get_sms_list($txt_search,'','',$page_item_no);

		foreach($result['row'] as $r_key => $r_val){
			$result['row'][$r_key]['sms_schedule_on'] = datetime_toggle($result['row'][$r_key]['sms_schedule_on'],$_SESSION['config']['datetime_format']);
			$result['row'][$r_key]['modified_date'] = datetime_toggle($result['row'][$r_key]['modified_date'],$_SESSION['config']['datetime_format']);
		}
		// _debug_array($result['row']); exit;

		$data['page_title'] 	= 'SMS Scheduler';
		$data['form_action'] 	= base_url('sms_scheduler');
		$data['row_data'] 		= $result['row'];
		$data['txt_search']		= $txt_search;

		// for paginations
		$data['pagination'] 	= paginationSettings('', $result['total_row'],$_SESSION['config']['max_page_item']);
		$data['msg'] 			= $this->msg;



		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('sms/index',$data);
		$this->load->view('templates/footer');
	}

	function edit_sms($scheduler_id='')
	{
		if(empty($scheduler_id))
		{
			$this->session->set_flashdata("warning_msg", 'Invalid Access.');
			redirect('sms_scheduler');
		}
		$is_edit = '1';
		$this->add_sms($scheduler_id,$is_edit);
	}

	public function add_sms($scheduler_id = '',$is_edit='0')
    {
		$input_data 	= $this->sms_scheduler_model->get_detail($scheduler_id,$is_edit);
		$building_opt 	= $this->common_model->get_building_list();
		$cust_cat	 	= $this->common_model->get_category_list();
		$cust_status	= $this->common_model->get_acc_status_list();

		if(empty($input_data))
		{
			$this->session->set_flashdata("warning_msg", 'Invalid Access.');
			redirect('sms_scheduler');
		}

		$sms_building = explode(',', $input_data['sms_building']);
		$input_data['sms_building'] = $sms_building;

		$options = array();
		foreach ($building_opt as $bo_key => $bo_val){
			$options[$building_opt[$bo_key]['building_no']] = $building_opt[$bo_key]['name'];
		}

		$data['input'] 				= $input_data;
		$data['disabled_input']		= '0';
		if(!empty($is_edit)){
 			//~ $minit_from_now			= ((time())-(60)); //(time()-(60*60*24)) // a day from now
 			$now					= time(); //(time()-(60*60*24)) // a day from now
			$data['disabled_input']	= ($now < strtotime($input_data['sms_schedule_on']))?'0':'1';
		}
		$data['building_opt'] 		= $options;
		$data['cust_cat'] 			= $cust_cat;
		$data['cust_status'] 		= $cust_status;
		$data['page_title'] 		= (empty($is_edit))?'Add SMS':'Edit SMS';
		$data['is_edit'] 			= (empty($is_edit))?'0':'1';
		$data['form_action'] 		= base_url('sms_scheduler/save_doc');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('sms/sms_detail',$data);
		$this->load->view('templates/footer');
	}

	public function save_doc()
    {
		if(!$_POST) redirect('sms_scheduler');

		if ((!empty($_POST['btDelete'])) &&  (!empty($_POST['scheduler_id'])))
		{
			$result 	= '0';
			$delete_id 	= $_POST['scheduler_id'];
			$db_result 	= $this->sms_scheduler_model->get_detail($delete_id,1);
			$now 		= time();
			//~ $minit_from_now	= ((time())-(60));

			if( !empty($db_result) )
			{
				if($now < strtotime($db_result['sms_schedule_on']))
				{
					$result = $this->sms_scheduler_model->sms_delete($delete_id);
				}
			}

			if(!empty($result))
			{
				if(!empty($result)) $this->session->set_flashdata('msg','SMS schedule is deleted!');
				redirect('sms_scheduler');
			}
			else
			{
				$this->session->set_flashdata("warning_msg", 'Invalid action.');
				redirect('sms_scheduler/edit_sms/'.$delete_id);
			}
		}
		else
		{
			$this->set_form_validation('save_sms', 1);
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1">','</label><br>');

			if($this->form_validation->run() == false)
			{
				$this->msg['error_msg'] 	= validation_errors();
				if(empty($_POST['scheduler_id']))
					$this->add_sms();
				else
					$this->edit_sms($_POST['scheduler_id']);
			}
			else
			{
				$post_data 	= $this->input->post(NULL, TRUE);
				
				$table 		= 'sms_scheduler';
				$submit_arr = $this->data_to_submit($post_data,$table);

				$scheduler_id 	= $submit_arr[$table]['scheduler_id'];
				unset($submit_arr[$table]['scheduler_id']);
				
				//add or edit
				if( empty($scheduler_id) ){
					$this->sms_scheduler_model->sms_add($submit_arr[$table]);
				}else{
					$this->sms_scheduler_model->sms_edit($submit_arr[$table],$scheduler_id);
				}

				$this->session->set_flashdata("msg", 'Sms is being sheduled.');
				redirect('sms_scheduler');

				//autocomplete to populate previous sms
			}
		}
	}

	public function data_to_submit($data ='',$table = '')
	{
		$post_data 	= array();
		$is_edit	= '0';
		$user		= $this->session->userdata('user');
		if($table != '' && $data != '')
		{
			if($table == 'sms_scheduler')
			{
				//user input data
				$post_data[$table]['scheduler_id'] 		= (empty($data['scheduler_id']))?'0':$data['scheduler_id'];
				$post_data[$table]['sms_schedule_on'] 	= $data['sms_schedule_on'];
				$post_data[$table]['sms_title'] 		= $data['sms_title'];
				$post_data[$table]['sms_msg'] 			= html_escape($data['sms_msg']);
				$post_data[$table]['is_building']		= $data['send_by'] == 'building' ? 1 : 0 ;
				if( $data['send_by'] == 'building' ){
					$post_data[$table]['sms_building'] 		= implode(", ", $data['sms_building']);
					$post_data[$table]['sms_cust_cat'] 		= $data['sms_cust_cat'];
					$post_data[$table]['sms_cust_status'] 	= $data['sms_cust_status'];
					$post_data[$table]['sms_to']			= "";
				}else{
					$post_data[$table]['sms_building'] 		= "";
					$post_data[$table]['sms_cust_cat'] 		= "";
					$post_data[$table]['sms_cust_status'] 	= "";
					$post_data[$table]['sms_to']			= $data['recipient_mobile_num'];
				}
				
				//generated data
				if(empty($data['scheduler_id']))
				{
					$post_data[$table]['created_date'] 	= date("Y-m-d H:i:s");
					$post_data[$table]['created_by'] 	= $user['idx'];  //$user['username'];
				}
				
				$post_data[$table]['modified_date'] 	= date("Y-m-d H:i:s");
				$post_data[$table]['modified_by'] 		= $user['idx']; //$user['username'];
			}
		}
		return $post_data;
	}

	private function set_form_validation($mode = 'save_sms', $auto_set_rules = '1')
	{
		//add/edit tag
		$config['save_sms'][] = array('field' => 'sms_title'		, 'label' => 'Title ' 				,'rules' => 'trim|required');
		$config['save_sms'][] = array('field' => 'sms_schedule_on'	, 'label' => 'Schedule On ' 		,'rules' => 'trim|required|callback_validate_schedule_on');
		$config['save_sms'][] = array('field' => 'sms_msg'			, 'label' => 'SMS Message '			,'rules' => 'trim|required');
		$config['save_sms'][] = array('field' => 'sms_cust_cat'		, 'label' => 'sms_cust_cat'			,'rules' => 'trim');
		$config['save_sms'][] = array('field' => 'sms_cust_status'	, 'label' => 'sms_cust_cat' 		,'rules' => 'trim');
		
		//~ $config['save_sms'][] = array('field' => 'sms_title'	, 'label' => 'Title ' ,'rules' => 'trim|required|callback_validate_name_exist');

		// need if u need to set rules outside this function, set $auto_set_rules = 0
		if($auto_set_rules == '1') $this->form_validation->set_rules($config[$mode]);
		else return $config[$mode];
	}

	public function validate_schedule_on()
    {
		$error_msg	= 'Scheduled time must be greater than current date and time';
		$now 		= time();

		$return_val = false;
		if( ( $_POST && (!empty($_POST['sms_schedule_on'])) ) && ( $now < strtotime($_POST['sms_schedule_on'] ) ) )
			$return_val = true;

		if($return_val == false)
			$this->form_validation->set_message('validate_schedule_on', $error_msg);

		return $return_val;
	}
	
	function sms_report(){
		
		$date_from = $this->input->post('date_from') == '' ? date("Y-m-d") : $this->input->post('date_from') ;
		$date_to   = $this->input->post('date_to') == '' ? date("Y-m-d") : $this->input->post('date_to') ;
		$title     = $this->input->post('sms_title');
		
		$reports = $this->sms_scheduler_model->get_sms_report( $date_from, $date_to, $title );
		
		$stat['N'] = 0 ;
		$stat['P'] = 0 ;
		$stat['S'] = 0 ;
		$stat['F'] = 0 ;
		$stat['C'] = 0 ;
		$this->load->model('customer_model');

		$customer_batch = $this->customer_model->get_customer_no_by_phone();

		foreach( $reports AS $key => $row ){
			$reports[$key]['subscriber_number'] = "";
			//get subscriber #
			/*if( $row['sms_phone'] != '' ){
				$results=$this->customer_model->filtered_customer(""," AND c.mobile_num = '".$row['sms_phone']."' ");
				if( !empty( $results ) ){
					$reports[$key]['subscriber_number'] = $results[0]['customer_no'];
				}
			}*/

			if (isset($customer_batch[$row['sms_phone']])) {
				$reports[$key]['subscriber_number'] = $customer_batch[$row['sms_phone']];
			}
			
			//get statistic
			if( $row['sms_status'] != '' )
				$stat[ $row['sms_status'] ]++;
		}
		
		
		$data['page_title'] = 'SMS Report';
		$data['form_action']= base_url('sms_scheduler/sms_report');
		$data['sms_title']	= $title;
		$data['date_from']	= $date_from;
		$data['date_to']	= $date_to;
		$data['reports']	= $reports;
		$data['statistic'] 	= $stat;
		$data['msg'] 		= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('sms/sms_report',$data);
		$this->load->view('templates/footer');
		
	}
	
	public function ajax_get_customer_detail()
	{
		$return_val = array();
		
		$this->load->model('customer_model');
		$result = $this->customer_model->get_customer_listing( $this->input->get('customer_no') , 0, '');
		$return_val = $result ; 
		$return_val['customer_no'] = $this->input->get('customer_no');
		
		echo json_encode( $return_val );
		
	}
	
	public function autocomplete_load_sms(){
		
		$return_val = array();
		
		$result = $this->sms_scheduler_model->get_sms_list( $this->input->post('keyword'), 
															$this->input->post('date_from'),
															$this->input->post('date_to'),
															0, 1000 );
		
		$return_val = $result['row'];
		
		echo json_encode( $return_val );
		
		
	}
	
	public function message_template( $template_id = '' ){
		
		$input_data 				= $this->sms_scheduler_model->get_sms_template_detail( " AND template_id = '".$template_id."' " );
		
		$data['template_list']		= $this->sms_scheduler_model->get_sms_template_list();
		
		$data['input'] 				= $input_data ;
		$data['page_title'] 		= ( $template_id == '' )?'Add Template':'Edit Template';
		$data['form_action'] 		= base_url('sms_scheduler/save_sms_template');
		$data['msg'] 				= $this->msg;
		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('sms/sms_template',$data);
		$this->load->view('templates/footer');
		
	}
	
	public function save_sms_template(){
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => '/message_template', 'error_keys' => array());
		
		if(!$_POST) {
			$ajax_return['err_msg'] = 'Invalid Access';
			echo json_encode($ajax_return);
			return false;
		}

		if ((!empty($_POST['btDelete'])) &&  (!empty($_POST['template_id'])))
		{
			$result 	= '0';
			$delete_id 	= $_POST['template_id'];
			$result = $this->sms_scheduler_model->delete_sms_template($delete_id);
			
			if(!empty($result))
			{
				$this->session->set_flashdata('msg','Email template has been deleted!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}else{
				$ajax_return['err_msg'] = 'Invalid action.';
				$ajax_return['url'] = '/message_template/'.$delete_id;
				echo json_encode($ajax_return);
				return false;
			}
		}else{
			//add or edit
			$template_id = $this->sms_scheduler_model->add_sms_template( $this->input->post() );
			if( empty($template_id) ){
				$ajax_return['err_msg'] = 'Error saving template.';
				$ajax_return['error_keys'] = $this->form_validation->error_array();
				$ajax_return['url'] = '';
				echo json_encode($ajax_return);
				return false;
			}
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'message_template/'.$template_id;
			echo json_encode($ajax_return);
			return false;
		}
		
	}
	
	public function ajax_get_sms_template(){
		$return_val = array();
		
		$template_id = $this->input->post('template_id');
		
		$template_details = $this->sms_scheduler_model->get_sms_template_detail( " AND template_id = '".$template_id."' " );
		
		$return_val = $template_details;
		
		echo json_encode( $return_val );
	}
	
	
}
