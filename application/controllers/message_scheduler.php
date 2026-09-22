<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Message_scheduler extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));

		check_acl('customer');
		$this->load->model('message_scheduler_model');
		$this->load->model('common_model');
    }

    public function index()
    {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'date_from' => '',
			'date_to' => '',
			'msg_status' => 'all',
		];
		$return = get_filtered_ajax_data('message_scheduler_filter', $default_data, $post_data);

		$data = $return['data'];

		$data['page_title'] 	= 'Message Scheduler';
		$data['form_action'] 	= base_url('message_scheduler');
		$data['row_html'] 		= $this->messaging_rows(1);
		$data['msg'] 			= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('message/index',$data);
		$this->load->view('templates/footer');
	}

	public function messaging_rows($returnOnly = 0) {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'date_from' => '',
			'date_to' => '',
			'msg_status' => 'all',
		];
		$return = get_filtered_ajax_data('message_scheduler_filter', $default_data, $post_data);

		$data = $return['data'];

		$result = $this->message_scheduler_model->get_message_list($data['txt_search'], $data['date_from'], $data['date_to'], $data['msg_status'], $data['page_item_no']);

		foreach($result['row'] as $key => &$val){
			$val['msg_schedule_on'] = datetime_toggle($val['msg_schedule_on'],$_SESSION['config']['datetime_format']);
			$val['modified_date'] = datetime_toggle($val['modified_date'],$_SESSION['config']['datetime_format']);
			$val['substring_preview'] = substr(explode("\n", $val['message'])[0], 0, 256) . '<small class="text-muted">&nbsp;&nbsp;<a href="' . base_url("message_scheduler/add_message") . '/' . $val['scheduler_id'] . '" title="More">[More]</a></small>';
			
			if ($val['msg_type'] == 'whatsapp') {
				$result['row'][$key]['type'] = "<span class='badge badge-success'>Whatsapp</span>";
			} else if ($val['msg_type'] == 'telegram') {
				$result['row'][$key]['type'] = "<span class='badge badge-purple'>Telegram</span>";
			} else {
				$result['row'][$key]['type'] = "<span class='badge badge-grey'>Undefined</span>";
			}

			if ($val['msg_status'] == 'S') {
				$result['row'][$key]['status'] = "<span class='badge badge-success'>Success</span>";
			} else if ($val['msg_status'] == 'F') {
				$result['row'][$key]['status'] = "<span class='badge badge-red'>Failed</span>";
			} else {
				$result['row'][$key]['status'] = "<span class='badge badge-blue'>Pending</span>";
			}
		}

		$data['pagination'] 	= paginationSettingsAjax('message_scheduler', $result['total_row'], $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['msg'] 			= $this->msg;
		$data['row_data'] 		= $result['row'];

		$html = $this->parser->parse('message/message_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}
	}

	public function add_message($scheduler_id = 0)
    {
		$input_data 	= $this->message_scheduler_model->get_message_details($scheduler_id);

		if(empty($input_data))
		{
			$this->session->set_flashdata("warning_msg", 'Invalid Access.');
			redirect('message_scheduler');
		}

		$data['input'] = $input_data;

		$log_data = json_decode($input_data['remark'], true);
		$data['title'] = $log_data['subject'];
		$data['message'] = $log_data['body'];

		if ($input_data['msg_type'] == 'whatsapp') {
			$data['type'] = "<span class='badge badge-success'>Whatsapp</span>";
		} else if ($input_data['msg_type'] == 'telegram') {
			$data['type'] = "<span class='badge badge-purple'>Telegram</span>";
		} else {
			$data['type'] = "<span class='badge badge-grey'>Undefined</span>";
		}

		if ($input_data['msg_status'] == 'S') {
			$data['status'] = "<span class='badge badge-success'>Success</span>";
		} else if ($input_data['msg_status'] == 'F') {
			$data['status'] = "<span class='badge badge-red'>Failed</span>";
		} else {
			$data['status'] = "<span class='badge badge-blue'>Pending</span>";
		}

		$data['disabled_input'] = '0';
		if(!empty($scheduler_id)) {
			$data['disabled_input']	= ($input_data['msg_status'] == 'P') ? '0' : '1';
		}
		$data['page_title'] = 'Edit Message';
		$data['form_action'] = base_url('message_scheduler/save_doc');
		$data['msg'] = $this->msg;
		
		$this->load->view('templates/header',$this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('message/message_detail',$data);
		$this->load->view('templates/footer');
	}

	public function save_doc()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'message_scheduler', 'error_keys' => array());
		log_message('error', print_r($_POST, true));

		if ((!empty($_POST['btDelete'])) && (!empty($_POST['scheduler_id'])) && (!empty($_POST['outgoing_id'])))
		{
			$scheduler_id = $_POST['scheduler_id'];
			$db_result = $this->message_scheduler_model->get_message_details($scheduler_id);

			if(!empty($db_result))
			{
				if($db_result['msg_status'] == 'P')
				{
					$result = $this->message_scheduler_model->delete_scheduled_message($scheduler_id);
				}
			}

			if(!empty($result))
			{
				if(!empty($result)) $this->session->set_flashdata('msg','Scheduled message is deleted!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'message_scheduler';
				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				$this->session->set_flashdata("warning_msg", 'Invalid action.');
				redirect('message_scheduler/add_message/'.$scheduler_id);
			}
		}
		else
		{
			$this->set_form_validation('save_message', 1);
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1">','</label><br>');

			if($this->form_validation->run() == false)
			{
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				$post_data 	= $this->input->post(NULL, TRUE);
				$msg_data = $this->message_scheduler_model->get_message_details($post_data['scheduler_id']);

				$remark = json_decode($msg_data['remark'], true);
				$remark['subject'] = $post_data['title'];
				$remark['body'] = $post_data['message'];
				$post_data['remark'] = json_encode($remark);

				$this->message_scheduler_model->update_scheduled_message($post_data);

				$this->session->set_flashdata("msg", 'Message is being scheduled.');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'message_scheduler';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	private function set_form_validation($mode = 'save_message', $auto_set_rules = '1')
	{
		$config['save_message'][] = array('field' => 'msg_schedule_on', 'label' => 'Schedule On ', 'rules' => 'trim|required');
		$config['save_message'][] = array('field' => 'title', 'label' => 'Message Title', 'rules' => 'trim|required');
		$config['save_message'][] = array('field' => 'message', 'label' => 'Message Content ', 'rules' => 'trim|required');

		// need if u need to set rules outside this function, set $auto_set_rules = 0
		if($auto_set_rules == '1') $this->form_validation->set_rules($config[$mode]);
		else return $config[$mode];
	}
}
