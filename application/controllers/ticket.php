<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Ticket extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		
		check_acl('trouble_ticket');
		$this->load->model('ticket_model');
		$this->load->model('common_model');	
		$this->load->helper('image_helper');
		$this->load->model('docs_log_model');
		$this->load->model('email_model');
    }
	
	function ajax_save_job_tracking(){
		
		$return_val = array();
		
		$tt_id  = $this->input->post('tt_id') ;
		$rec_id = $this->input->post('rec_id');
		$date   = $this->input->post('date')  ;
		$remark = $this->input->post('remark');
		$reply_to = $this->input->post('reply_to');
		$created_by = $this->user['idx'];

		if(empty($reply_to) || $reply_to[0] === 'all') $reply_to = [];

		$prepend = '';
		if (!empty($reply_to)) {
			$users = array_column($this->common_model->get_user_list(), 'display_name', 'idx');
			$names = array_map(fn($id) => $users[$id] ?? null, $reply_to);
			$names = array_filter($names);
			$prepend = $this->user['display_name'] . ' replied to ' . implode(', ', $names) . ":\n";
		}

		$prepend_with_comment = $prepend . $remark;

		$return_val = $this->ticket_model->save_job_tracking_detail( $tt_id, $rec_id, $prepend_with_comment, $created_by, $reply_to);
		$return_val['tt_id'] = $tt_id ;
		
		echo json_encode( $return_val );
		
	}

	public function send_job_tracking()
	{
		$rec_id = $this->input->post('rec_id');
		$tt_id  = $this->input->post('tt_id');
		$remark = $this->input->post('remark');
		$tt_no  = $this->input->post('tt_no');
		$date   = $this->input->post('date');

		$tt_job_tracking = $this->common_model->get_table(
			'tt_job_tracking',
			'*',
			"`tt_job_id` = '$rec_id'"
		);

		$reply_to_id = [];

		if (!empty($tt_job_tracking)) {
			$reply_to_id = unserialize($tt_job_tracking[0]['reply_to']);
		}

		/*
		* Get notification users
		*/
		if (empty($reply_to_id)) {

			/*
			* Get Main PIC + Assigned User IDs here.
			*/
			$user_ids = [];

			$notification = 'Service Ticket Job Details and Comments have been updated.';

		} else {

			$user_ids = $reply_to_id;

			$notification = $this->user['display_name'] . ' replied to you';
		}

		/*
		* Build contacts
		*/
		$email_list = [];
		$whatsapp_list = [];
		$telegram_list = [];

		foreach ($user_ids as $user_id) {

			$user = $this->user_model->get_user_by_id($user_id);

			if (empty($user)) {
				continue;
			}

			if (!empty($user['email'])) {
				$email_list[] = $user['email'];
			}

			if (
				!empty($user['mobile_no']) &&
				($user['allow_whatsapp'] ?? 0) == 1
			) {
				$whatsapp_list[] = $user['mobile_no'];
			}

			if (
				!empty($user['telegram_id']) &&
				($user['allow_telegram'] ?? 0) == 1
			) {
				$telegram_list[] = $user['telegram_id'];
			}
		}

		$this->load->library('notification_service');
		$contacts = $this->notification_service->buildContacts( $email_list, $whatsapp_list, $telegram_list );

		/*
		* Email content
		*/
		if (empty($reply_to_id)) {

			$body = "ST No: {$tt_no}" .
				"<br>Service Ticket Job Details and Comments:" .
				"<br>Updated On: {$date}" .
				"<br>Comment: {$remark}" .
				"<br>Updated By: {$this->user['display_name']}";

		} else {

			$body = "ST No: {$tt_no}" .
				"<br><br>" .
				"{$notification}:" .
				"<br>{$remark}" .
				"<br><br>" .
				"Updated On: {$date}";
		}

		$header = "Service Ticket [{$tt_no}] Job Details and Comments Updated";

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			'TICKET JOB TRACKING',
			[
				'ticket_type' 	=> 'Service Ticket',
				'ticket_no' 	=> !empty($tt_no) ? $tt_no : '-',
				'notification' 	=> !empty($notification) ? $notification : '-',
				'remark' 		=> !empty($remark) ? $remark : '-',
				'updated_on' 	=> !empty($date) ? $date : '-',
				'updated_by' 	=> !empty($this->user['display_name']) ? $this->user['display_name'] : 'N/A',
			],
			'',
			[
				'controller'    => 'ticket',
				'doc_id'        => $tt_id,
				'user_id'       => $this->user['idx'],
				'send_method'   => 'manual',
				'email_starter' => $header,
				'doc_type'      => '[Service Ticket Job Details and Comments Updated]',
			]
		);

		$this->session->set_flashdata(
			'msg',
			'Job Details Updated!'
		);
	}
	
	function ajax_delete_job_tracking(){

		$tt_id  = $this->input->post('tt_id')  ;
		$rec_id = $this->input->post('rec_id') ;
	
		$return_val['success'] = $this->ticket_model->delete_job_tracking_detail( $tt_id, $rec_id );
		
		echo json_encode( $return_val );
		
	}
	
	public function ajax_get_user_emails()
	{
		$return_val = array();
		$email_arr = array();
		$email_str = "";
		
		$this->load->model('user_model');
		foreach( $this->input->post('tt_assign_to') AS $val ){
			$result = $this->user_model->get_user_by_id( $val );
			if( trim($result['email']) != '' ){
				$email_arr[] = trim($result['email']);
			}
		}
		$email_arr = array_unique( $email_arr );
		$email_str = implode( " ; " , $email_arr ) ;
		
		$return_val['emails'] = $email_str;
		
		echo json_encode( $return_val );
	}

	//Ajax - Function
	public function autocomplete_load_customer()
	{
		$this->load->model('customer_model');
		$return_val = array();
		$customer = $this->customer_model->get_autocomplete_load_customer($this->input->post('keyword'));
		
		$return_val = $customer;
		
		//~ $customer_pic_1[] = array(	
									//~ 'pic_id' => '',
									//~ 'customer_no' => $customer['customer_no'], 
									//~ 'pic_name' => $customer['pic_name'], 
									//~ 'tel_num' => $customer['tel_num'], 
									//~ 'fax_num' => $customer['fax_num'], 
									//~ 'pic_name' => $customer['pic_name'], 
									//~ 'mobile_num' => $customer['mobile_num'], 
									//~ 'email_1' => $customer['email_1'], 
									//~ 'email_2' => $customer['email_2'], 
									//~ 'nric' => $customer['nric'], 
									//~ 'passport' => $customer['passport'], 
									//~ 'date_of_birth' => $customer['date_of_birth'], 
									//~ 'race' => $customer['race'], 
									//~ 'gender' => $customer['gender']
							//~ );
		
		//~ $customer_pic_2 = $this->customer_model->get_customer_pic( $this->input->post('keyword') );
		
		//~ $return_val['pic'] = array_merge( $customer_pic_1 , $customer_pic_2 );
		
		echo json_encode($return_val);
	}

	public function ajax_get_customer_detail(){

		$this->load->model('profile_model');
		$this->load->model('customer_model');
		
		$return_val = array();
		
		$customer_no = $this->input->post('customer_no');
		$pic_name    = $this->input->post('pic_name');
		
		$customer = $this->customer_model->get_customer($customer_no);
		
		$customer_pic_1[] = array(
									'pic_id' => '',
									'customer_no' => $customer['customer_no'], 
									'pic_name' => $customer['pic_name'] == '' ? $customer['name'] : $customer['pic_name'] , 
									'tel_num' => $customer['tel_num'], 
									'fax_num' => $customer['fax_num'], 
									'pic_mobile' => $customer['pic_mobile'], 
									'pic_email_1' => $customer['email_1'], 
									'pic_email_2' => $customer['email_2'], 
									'nric' => $customer['nric'], 
									'passport' => $customer['passport'], 
									'date_of_birth' => $customer['date_of_birth'], 
									'race' => $customer['race'], 
									'gender' => $customer['gender']
								);
		
		//$customer_pic_2 = $this->customer_model->get_customer_pic( $customer_no );
		$customer_pic_2 = $this->profile_model->get_profile_pic( $customer['profile_id'] );
		$customer_pic 	= array_merge( $customer_pic_1 , $customer_pic_2 );
		
		$sel_html = "";
		foreach( $customer_pic AS $key => $row ){
			$selected = "";
			if( $row['pic_name'] == $pic_name ){
				$selected = "selected='selected'";
			}
			
			$sel_html .= "<option ".$selected." value='".$row['pic_name']."' data-set='".$row['pic_mobile']."'>".$row['pic_name']."</option>";
		}

		$customer_category = $this->customer_model->get_customer_category( $customer['category']);
		
		$return_val['pic'] = $sel_html ; 
		$return_val['contact_no'] = $customer_pic[0]['pic_mobile'];
		$return_val['customer_category'] = $customer['category'];
		$return_val['customer_category_name'] = $customer_category['name'];
		
		echo json_encode( $return_val );
	}
	
	function autocomplete_load_trouble_ticket()
	{
		$return_val = array();
		//load OPEN tt, and exclude itself
		$tickets = $this->ticket_model->get_trouble_ticket_listing('',$this->input->post('keyword'),0,999999," AND tt.tt_id != '".$this->input->post('tt_id')."' ");
		$return_val = $tickets['row'];
		echo json_encode( $return_val );
	}
	
	function add_trouble_ticket()
	{
		$chk_temp_id = $this->input->post('temp_id');
				
		$data['input'] = $this->ticket_model->get_single_trouble_ticket('');
		$data['input']['created_by'] 	  = $this->user['idx'];
		$data['input']['created_by_name'] = $this->user['display_name'];
		$data['input']['this_user'] 	  = $this->user['display_name'];
		$data['tt_no'] = $this->ticket_model->get_new_tt_no();
		
		$data['page_title'] 		= 'Add Service Ticket';
		$data['form_action'] 		= base_url('ticket/save_ticket');
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();
		$data['sel_sof']			= $this->common_model->get_tt_sof_list();
		$data['sel_cof']			= $this->common_model->get_tt_cof_list();
		$data['sel_complaint']		= $this->common_model->get_tt_complaint_list();
		$data['sel_user']			= $this->common_model->get_technical_list('trouble_ticket');
		$data['mode'] 				= 'owner';
		$data['msg'] 				= $this->msg;
		$data['full_access']		= check_acl('trouble_ticket','A',false);

		$data['sel_reply'] = $this->ticket_model->get_service_ticket_reply_list($data['sel_user'], []);

		if (!empty($chk_temp_id)) {
			$data['input']['temp_id'] = $chk_temp_id;
		}

		$file_result = [];
		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('ticket', $chk_temp_id, '', 0);
			//debugarr($leftover_attach);
			//unset($_SESSION['po']['attachments']);
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
		$this->parser->parse('ticket/ticket_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function edit_trouble_ticket($tt_id='')
	{
		$chk_temp_id = $this->input->post('temp_id');
		$ticket = $this->ticket_model->get_single_trouble_ticket_by_id($tt_id);
		
		if ($ticket['tt_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Ticket not found!');
			redirect('ticket');
		}

		if (!empty($chk_temp_id)) {
			$ticket['temp_id'] = $chk_temp_id;
		}
		
		$data['input'] = $ticket;
				
		$data['input']['tt_assign_to'] = explode( "," , str_replace( '"' , '' , $data['input']['tt_assign_to'] ) ) ;
		
		if( $this->user['idx'] == $data['input']['created_by'] || check_acl('trouble_ticket','A',false)){
			$data['mode'] = 'owner';
			$data['form_action'] = base_url('ticket/save_ticket');
		}elseif( in_array( $this->user['idx'] , $data['input']['tt_assign_to'] )  ){
			$data['mode'] = 'assign';
			$data['form_action'] = base_url('ticket/update_ticket_status');
		}else{
			$data['mode'] = 'view';
			$data['form_action'] = '';
		}
		
		$data['input']['this_user'] = $this->user['username'];
		$data['job_tracking'] = $this->ticket_model->get_job_tracking_detail( $ticket['tt_id'] );
		
		$data['page_title'] 		= 'Edit Ticket';
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();
		$data['sel_sof']			= $this->common_model->get_tt_sof_list();
		$data['sel_cof']			= $this->common_model->get_tt_cof_list();
		$data['sel_complaint']		= $this->common_model->get_tt_complaint_list();
		$data['sel_user']			= $this->common_model->get_technical_list('trouble_ticket');
		
		$data['msg'] 				= $this->msg;
		$data['full_access']		= check_acl('trouble_ticket','A',false);

		$data['sel_reply'] = $this->ticket_model->get_service_ticket_reply_list($data['sel_user'], $data['input']['tt_assign_to']);

		$file_result	= $this->common_model->get_file_attachment('ticket', $tt_id);

		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('ticket', $chk_temp_id, '', 0);
			//debugarr($leftover_attach);
			//unset($_SESSION['po']['attachments']);
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
		}

		$data['attachment'] = $file_result;
			
		// $this->vars['jsfiles'][] = 'js/itelco/attachments.js';
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('ticket/ticket_detail',$data);
		$this->load->view('templates/footer');
	}

	function get_status_name($status) {
		if($status == 0) {
			return 'Closed';
		} else if ($status == 1) {
			return 'Opened';
		} else if ($status == 2) {
			return 'Assigned';
		} else if ($status == 3) {
			return 'In Progress';
		} else if ($status == 4) {
			return 'Solved';
		} else if ($status == 5) {
			return 'Not Related';
		}
	}

	function get_fault_name($list, $id, $keyname) {
		$name = '';
		if($id != '') {
			foreach ($list as $val) {
				if($val[$keyname.'_id'] == $id) {
					$name = $val[$keyname.'_name'];
					break;
				}
			}
		}
		return $name;
	}
	
	function update_ticket_status() 
	{
		$post_data = $this->input->post();
		$tt_id = $this->input->post('tt_id');
		$tt_no = $this->input->post('tt_no');
		$prev_status = $this->input->post('prev_status');
		$status = $this->input->post('status');
		$prev_status_name = $this->get_status_name($prev_status);
		$status_name = $this->get_status_name($status);
		$tt_sof_id = $this->input->post('tt_sof_id');
		$tt_cof_id = $this->input->post('tt_cof_id');
		$tt_sof_list = $this->common_model->get_tt_sof_list();
		$tt_cof_list = $this->common_model->get_tt_cof_list();
		$tt_sof_name = $this->get_fault_name($tt_sof_list,$tt_sof_id,'tt_sof');
		$tt_cof_name = $this->get_fault_name($tt_cof_list,$tt_cof_id,'tt_cof');
		$result = $this->ticket_model->update_ticket_status($tt_id, $prev_status, $status, $prev_status_name, $status_name, $tt_sof_id, $tt_cof_id, $post_data);

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
						
						$this->common_model->insert_file_attachment($file_name , $local_path, 'ticket', $tt_id, $remark, $is_print, '', '', 0, $this->user['idx']);
					}
				}
			}
		}

		if($result == 1 && ($prev_status != $status)) {
			$tt_main_pic = $this->ticket_model->get_tt_pic();

			$email_list = [];
			$whatsapp_list = [];
			$telegram_list = [];

			foreach ($tt_main_pic as $value) {

				if (!empty($value['tt_email'])) {
					$email_list[] = $value['tt_email'];
				}

				if (!empty($value['tt_contact'])) {
					$whatsapp_list[] = $value['tt_contact'];
				}

				if (!empty($value['tt_telegram'])) {
					$telegram_list[] = $value['tt_telegram'];
				}
			}

			$this->load->library('notification_service');
			$contacts = $this->notification_service->buildContacts($email_list, $whatsapp_list, $telegram_list);

			$email_template = $this->email_model->get_email_template_detail(
				"AND template_name = 'SERVICE TICKET STATUS UPDATE' AND is_default = 1"
			);

			$meta_template_name = 'SERVICE TICKET STATUS UPDATE';

			$replacements = [
				'%tt_no%'            => $tt_no,
				'%prev_status_name%' => $prev_status_name,
				'%status_name%'      => $status_name,
				'%tt_sof_name%'      => $tt_sof_name,
				'%tt_cof_name%'      => $tt_cof_name,
			];

			$header = str_replace(
				array_keys($replacements),
				array_values($replacements),
				$email_template['email_title']
			);

			$body = str_replace(
				array_keys($replacements),
				array_values($replacements),
				$email_template['email_msg']
			);

			$this->notification_service->queue(
				$contacts,
				$header,
				$body,
				$meta_template_name,
				[
					'tt_no' 			=> !empty($tt_no) ? $tt_no : '-',
					'prev_status_name' 	=> !empty($prev_status_name) ? $prev_status_name : '-',
					'status_name' 		=> !empty($status_name) ? $status_name : '-',
					'tt_sof_name' 		=> !empty($tt_sof_name) ? $tt_sof_name : '-',
					'tt_cof_name' 		=> !empty($tt_cof_name) ? $tt_cof_name : '-',
				],
				'',
				[
					'controller'    => 'ticket',
					'doc_id'        => $tt_id,
					'user_id'       => $this->user['idx'],
					'send_method'   => 'manual',
					'email_starter' => 'Service Ticket [' . $tt_no . '] status updated',
					'doc_type'      => '[Service Ticket Status Update]',
				]
			);

			$this->ticket_model->save_job_tracking_detail( $tt_id, 0, "Ticket Status Changed From [$prev_status_name] To [$status_name]", $this->user['idx'] );
		}
		
		$this->session->set_flashdata("msg", 'Ticket Status Updated!');
		// redirect('ticket/index');
		$ajax_return['status'] = 'SUCC';
		$ajax_return['url'] = 'ticket/index';
		echo json_encode($ajax_return);
		return false;
	}
	
	function save_ticket()
	{
		//AJAX
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'ticket', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('tt_id') != '') {
			
			$result = $this->ticket_model->ticket_delete($this->input->post('tt_id'),$this->input->post('existing_attach'));			
			if($result == 1){
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'ticket/index';
			}else{
				$ajax_return['err_msg'] = 'Service ticket not found, please refresh your page.';
			}
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{
			$this->set_form_validation('save');
			if($this->form_validation->run() == false) 
			{
				$this->msg['error_msg'] = validation_errors();

				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
				
				$this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));

				if ($this->input->post('tt_id')=='' ) {
					$this->add_trouble_ticket();
				} else {					
					$this->edit_trouble_ticket($this->input->post('tt_id'));
				}
			}
			else 
			{
				$result = $this->ticket_model->save_trouble_ticket($this->input->post(),$this->user['idx']);
				$post_back = $result;	

				$chk_temp_id = $this->input->post('temp_id');
				if (!empty($chk_temp_id)) {
					$leftover_attach = $this->common_model->get_temp_file_attachment('ticket', $chk_temp_id, '', 0, 0); 
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
							
							$this->common_model->insert_file_attachment($file_name , $local_path, 'ticket', $post_back['tt_id'], $remark, $is_print, '', 0, 0, $this->user['idx']);

							//delete from temp db

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
								
								$this->common_model->insert_file_attachment($file_name , $local_path, 'ticket', $post_back['tt_id'], $remark, $is_print, '', '', 0, $this->user['idx']);
							}
						}
					}
				}

				if($this->input->post('prev_status') != $this->input->post('status')) {
					if($this->input->post('status') == '0') {
						$this->ticket_model->save_job_tracking_detail( $post_back['tt_id'], 0, "Service Ticket Close", $this->user['idx'] );
					} else if ($this->input->post('status') != '1' && $this->input->post('status') != '2') {
						$prev_status_name = $this->get_status_name($this->input->post('prev_status'));
						$status_name = $this->get_status_name($this->input->post('status'));
						$this->ticket_model->save_job_tracking_detail( $post_back['tt_id'], 0, "Ticket Status Changed From [$prev_status_name] To [$status_name]", $this->user['idx'] );
					}
				}
				
				if (
					$this->input->post('tt_id') != ''
					&& $this->input->post('prev_status') != $this->input->post('status')
				) {
					$this->ticket_model->notify_customer_of_status_change(
						[
							'tt_no'       => $post_back['tt_no'],
							'tt_id'       => $post_back['tt_id'],
							'customer_no' => $this->input->post('customer_no'),
						],
						$this->input->post('prev_status'),
						$this->input->post('status'),
						$this->get_status_name($this->input->post('prev_status')),
						$this->get_status_name($this->input->post('status'))
					);
				}

				$additional_msg = '';
				//email for NEW ticket only / resend
				if(($this->input->post('tt_id') == '' && $this->input->post('tt_assign_to')) || $this->input->post('resend_email') == 1 || ($this->input->post('status') == '2' &&  $this->input->post('prev_status') == '1')) {

					$email_list  = explode( ";" , $post_back['tt_email'] );
					$whatsapp_list = [];
					$telegram_list = [];

					$tt_assign_to = $post_back['tt_assign_to'];

					if(!empty($tt_assign_to)) {
						$assignee = $this->ticket_model->get_assignee_details($tt_assign_to);
						$assignee_name = array_column($assignee, 'username');
						$assignee_name = implode(",",$assignee_name);
						
						foreach ($assignee as $value) {
						
							if($value['allow_whatsapp'] == 1 && !empty($value['mobile_no'])) {
								$whatsapp_list[] = $value['mobile_no'];
							}

							if($value['allow_telegram'] == 1 && !empty($value['telegram_id'])) {
								$telegram_list[] = $value['telegram_id'];
							}
						}
					}

					$this->send_trouble_ticket( $post_back['tt_no'], $post_back['tt_id'], $email_list, $whatsapp_list, $telegram_list, $assignee_name);

					$additional_msg = 'Email has been sent to recipients.';
					
				} 
				
				/**
				 * If ticket is not new and no assignee, then send email to main PICs
				 * This is to notify that ticket is saved and no assignee is selected.
				 * This is to avoid sending email to all assignees when ticket is saved without assignee.
				 * If ticket is new and no assignee, then send email to main PICs
				 */
				
				else if ($this->input->post('tt_id') == '' && check_acl('trouble_ticket','A',false) == false) {

					$tt_main_pic = $this->ticket_model->get_tt_pic();
					$email_list = [];
					$whatsapp_list = [];
					$telegram_list = [];

					foreach ($tt_main_pic as $key => $value) {

						$email_list[] = $value['tt_email'];
						
						if($value['allow_telegram'] == 1 && !empty($value['tt_contact'])) {
							$whatsapp_list[] = $value['tt_contact'];
						}

						if($value['allow_telegram'] == 1 && !empty($value['tt_telegram'])) {
							$telegram_list[] = $value['tt_telegram'];
						}
					}
					$this->send_trouble_ticket( $post_back['tt_no'], $post_back['tt_id'], $email_list, $whatsapp_list, $telegram_list);

					$additional_msg = 'Email has been sent to recipients.';
				}
				
				$this->session->set_flashdata("msg", 'Ticket Saved! ' . $additional_msg);
				// redirect('ticket/index');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'ticket/index';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

    
    public function index()
    {
		
		$total_row 		= 0;
		$page_item_no 	= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		$txt_search 	= $this->input->post('txt_search');
		$sel_category 	= $this->input->post('sel_category');
		$sel_status 	= ($this->input->post('sel_status') == '')? 'all' : $this->input->post('sel_status');
		$date_from		= $this->input->post('date_from');
		$date_to		= $this->input->post('date_to');
		
		$sess = $this->session->userdata;
	
		$query_where = "";
		if ( $sel_category != 'all' && !empty($sel_category) ) {
			$query_where = "AND c.category = '$sel_category' ";
		}
		
		if ( $sel_status != 'all' ) {
			$query_where .= "AND tt.tt_status = '$sel_status' ";
		}

		if ( !empty($date_from) && !empty($date_to) ) {
			$query_where .= "AND tt.datetime_open >= '$date_from' AND tt.datetime_open <= '$date_to' ";
		}
		
		$full_access = check_acl('trouble_ticket','A',false) == true ? 1 : 0 ;
		
		$priority_sort = '';
		if( $full_access == 1 ){
			$priority_sort = "CASE WHEN tt.tt_status = 4 THEN 1
					WHEN tt.tt_status = 1 THEN 2
					WHEN tt.tt_status = 3 THEN 3
					WHEN tt.tt_status = 2 THEN 4
					WHEN tt.tt_status = 5 THEN 5
					ELSE 6 END AS priority,";
			$result = $this->ticket_model->get_trouble_ticket_listing( '' ,$txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where,$priority_sort);
		}else{
			$priority_sort = "CASE WHEN tt.tt_status = 3 THEN 1
					WHEN tt.tt_status = 2 THEN 2
					WHEN tt.tt_status = 4 THEN 3
					WHEN tt.tt_status = 1 THEN 4
					WHEN tt.tt_status = 5 THEN 5
					ELSE 6 END AS priority,";
			$result = $this->ticket_model->get_trouble_ticket_listing( $sess['user']['idx'] ,$txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where,$priority_sort);
		}

		foreach ($result['row'] as $key => &$value) {
			$tt_assign_to = str_replace('"', '', $value['tt_assign_to']);
			$assignee = explode(',', $tt_assign_to);
			if(in_array($this->user['idx'], $assignee) && $value['tt_status'] != 'CLOSE' && $value['tt_status'] != 'SOLVED') {
				$value['row_style'] = 'class="highlighted"';
			}
			$value['status_badge'] = $this->createStatusIcon($value['tt_status']);
		}
		
		$data['page_title'] 		= 'Service Ticket';
		$data['form_action'] 		= base_url('ticket/index');
		$data['row_data'] 			= $result['row'];
		$data['txt_search'] 		= $txt_search;
		$data['sel_category'] 		= $sel_category;
		$data['sel_status'] 		= $sel_status;
		$data['date_from']			= $date_from;
		$data['date_to']			= $date_to;
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_sof']			= $this->common_model->get_tt_sof_list();
		$data['sel_cof']			= $this->common_model->get_tt_cof_list();
		$data['sel_complaint']		= $this->common_model->get_tt_complaint_list();
		$data['pagination'] 		= paginationSettings('', $result['total_row']);
		$data['msg'] 				= $this->msg;

		$this->vars['cssfiles'][] = 'css/theme/bootstrap-grid.css';
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('ticket/index',$data);
		$this->load->view('templates/footer');
	}

	private function createStatusIcon($tt_status) {
		switch ($tt_status) {
			case 'ASSIGNED':
				return "<span class='badge badge-purple'>ASSIGNED</span>";
			case 'IN PROGRESS':
				return "<span class='badge badge-warning'>IN PROGRESS</span>";
			case 'SOLVED':
				return "<span class='badge badge-success'>SOLVED</span>";
			case 'CLOSE':
				return "<span class='badge badge-grey'>CLOSE</span>";
			case 'NOT RELATED':
				return "<span class='badge badge-brown'>NOT RELATED</span>";
			default:
				return "<span class='badge badge-blue'>OPEN</span>";
		}
	}
	
    public function setting()
    {
				
		$data['page_title'] 		= 'Service Ticket Setting';
		$data['form_action'] 		= base_url('ticket/save_tt_setting');
		$data['msg'] 				= $this->msg;
		//default
		$data['id'][0] 	   = '';
		$data['name'][0]   = 'Default';
		
		$categories = $this->common_model->get_product_category_list();
		$data['tt_pic'] = $this->ticket_model->get_tt_pic();
		$data['technical_list'] = $this->common_model->get_technical_list('trouble_ticket');
		$user_list = $this->common_model->get_user_list();
		$user_list_arr = [];

		foreach ($user_list as $key => $user) {
			$user_list_arr[] = [
				'value' => $user['display_name'],
				'label' => $user['display_name'],
				'idx' => $user['idx'],
				'email' => $user['email'],
				'mobile_no' => $user['mobile_no'],
				'telegram_id' => $user['telegram_id']
			];
		}

		$data['user_list'] = json_encode($user_list_arr);

		$techincal_user_list = $this->common_model->get_user_list();
		$techincal_user_list_arr = [];

		foreach ($techincal_user_list as $key => $user) {
			$techincal_user_list_arr[] = [
				'value' => $user['display_name'],
				'label' => $user['display_name'],
				'idx' => $user['idx'],
				'email' => $user['email'],
				'mobile_no' => $user['mobile_no'],
				'telegram_id' => $user['telegram_id']
			];
		}

		$data['techincal_user_list'] = json_encode($techincal_user_list_arr);

		$this->vars['jsfiles'][] = 'js/itelco/ticket_setting.js';
		$this->vars['jscripts'][] = 'var technical_user_list = ' . json_encode($techincal_user_list_arr) . ';';


		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('ticket/setting',$data);
		$this->load->view('templates/footer');
	}
	
	public function save_tt_setting()
	{
		$post_data = $this->input->post();
		if( !isset($post_data['enable_sms']) )
			$post_data['enable_sms'] = 0 ;

		if( !isset($post_data['enable_email']) )
			$post_data['enable_email'] = 0 ;

		$this->ticket_model->save_settings( $post_data );
		$this->session->set_flashdata("msg", 'Setting Saved!');
		
		redirect('ticket/setting');
	}

	function ajax_delete_tt_pic(){

		$pic_id = $this->input->post('pic_id');
		$pic_name = $this->input->post('pic_name');
	
		$return_val['success'] = $this->ticket_model->delete_tt_pic( $pic_id, $pic_name);
		
		echo json_encode( $return_val );
		
	}

	function ajax_delete_technical_user(){

		$pic_id = $this->input->post('pic_id');
		$user_id = $this->input->post('user_id');
	
		$return_val['success'] = $this->ticket_model->delete_techinical_user( $pic_id, $user_id, 'service_ticket');
		
		echo json_encode( $return_val );
		
	}

	public function ticket_issues()
    {
		$total_row 		= 0;
		$page_item_no 	= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		$txt_search 	= $this->input->post('txt_search');
		$sel_category 	= $this->input->post('sel_category') ?: ($this->session->userdata('last_sel_category')?:'tt_complaint');
		
		$query_where = (!empty($sel_category)) ? $sel_category : 'tt_complaint';

		$result = $this->ticket_model->get_tt_issues_listing($txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where);

		foreach ($result['row'] as &$value) {
			$value['category'] = ($query_where == 'tt_complaint') ? 'tt_complaint' : (($query_where == 'tt_sof') ? 'tt_sof' : 'tt_cof');
		}

		$data['page_title'] 		= 'Ticket Issues';
		$data['page_desc'] 			= ($sel_category == 'tt_complaint') ? 'Complaint Types' : (($sel_category == 'tt_sof') ? 'Source of Fault' : 'Cause of Fault');
		$data['form_action'] 		= base_url('ticket/ticket_issues');
		$data['row_data'] 			= $result['row'];
		$data['txt_search'] 		= $txt_search;		
		$data['sel_category'] 		= $sel_category;
		$data['pagination'] 		= paginationSettings('', $result['total_row']);	
		$data['msg'] 				= $this->msg;

		$this->session->set_userdata('last_sel_category', $sel_category);

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('ticket/ticket_issues',$data);
		$this->load->view('templates/footer');
	}

	public function add_ticket_issues($category='',$id=0)
    {
		$chk_temp_id = $this->input->post('temp_id');

		$data['input'] 				= $this->ticket_model->get_single_ticket_issues($category,$id);
		$data['input']['created_by'] 	  = $this->user['idx'];
		$data['sel_category']		= $category;
		$data['page_title'] 		= (empty($id)) ? 'Add Ticket Issues' : 'Edit Ticket Issues';
		$data['form_action'] 		= base_url('ticket/save_ticket_issues');
		$data['msg'] 				= $this->msg;

		if (!empty($chk_temp_id)) {
			$data['input']['temp_id'] = $chk_temp_id;
		}

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('ticket/ticket_issues_details',$data);
		$this->load->view('templates/footer');
	}

	public function save_ticket_issues()
    {
		//AJAX
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'registration', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && !empty($this->input->post('id'))) {
			
			$this->ticket_model->delete_ticket_issues($this->input->post('sel_category'),$this->input->post());			
			
			$this->session->set_flashdata("msg", 'Ticket Issues Deleted!');
			$this->session->set_userdata('last_sel_category', $this->input->post('sel_category'));
			// redirect('ticket/ticket_issues');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'ticket/ticket_issues';
			echo json_encode($ajax_return);
			return false;
		} else {
			$this->set_form_validation('save_ticket_issues');
			if($this->form_validation->run() == false) 
			{
				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				$this->msg['error_msg'] = validation_errors();
				$this->add_ticket_issues();
			} else {
				$userId = $this->user['idx'];
				$result = $this->ticket_model->save_ticket_issues($this->input->post('sel_category'),$this->input->post(),$userId);

				$this->session->set_flashdata("msg", 'Ticket Issues Saved!');
				$this->session->set_userdata('last_sel_category', $this->input->post('sel_category'));
				// redirect('ticket/ticket_issues');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'ticket/ticket_issues';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
	
	public function send_trouble_ticket($tt_no, $tt_id, $email_list, $whatsapp_list, $telegram_list, $assignee_name = '')
	{
		if ($tt_no != '<< NEW >>') {

			$template_name = 'SERVICE TICKET ASSIGN';

			$email_template = $this->email_model->get_email_template_detail(
				"AND template_name = '{$template_name}' AND is_default = 1"
			);

			$tickets = $this->ticket_model->get_single_trouble_ticket($tt_no);

			$this->ticket_model->save_job_tracking_detail(
				$tickets['tt_id'],
				0,
				'Service Ticket had been assigned to ' . $assignee_name,
				$this->user['idx']
			);

			$email_starter = 'Service Ticket [' . $tickets['tt_no'] . '] had been assigned to you';
			$doc_type = '[Service Ticket Assign]';

			$notification = 'This service ticket has been assigned to you.';

		} else {

			$template_name = 'SERVICE TICKET CREATED';

			$email_template = $this->email_model->get_email_template_detail(
				"AND template_name = '{$template_name}' AND is_default = 1"
			);

			$tickets = $this->ticket_model->get_single_trouble_ticket_by_id($tt_id);

			$email_starter = 'New Service Ticket Created [' . $tickets['tt_no'] . ']';
			$doc_type = '[New Service Ticket Created]';

			$notification = 'A new service ticket has been created.';
		}

		$this->load->library('notification_service');
		$contacts = $this->notification_service->buildContacts($email_list, $whatsapp_list, $telegram_list);

		$replacements = [
			'%tt_no%'             => $tickets['tt_no'],
			'%customer_no%'       => $tickets['customer_no'],
			'%pic_name%'          => $tickets['pic_name'],
			'%datetime_open%'     => $tickets['datetime_open'],
			'%tt_complaint_name%' => $tickets['tt_complaint_name'],
			'%tt_remark%'         => $tickets['tt_remark'],
		];

		$header = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$email_template['email_title']
		);

		$body = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$email_template['email_msg']
		);

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			$template_name,
			[
				'notification' 		=> !empty($notification) ? $notification : '-',
				'tt_no' 			=> !empty($tickets['tt_no']) ? $tickets['tt_no'] : '-',
				'customer_no' 		=> !empty($tickets['customer_no']) ? $tickets['customer_no'] : '-',
				'pic_name' 			=> !empty($tickets['pic_name']) ? $tickets['pic_name'] : 'N/A',
				'datetime_open' 	=> !empty($tickets['datetime_open']) ? $tickets['datetime_open'] : '-',
				'tt_complaint_name' => !empty($tickets['tt_complaint_name']) ? $tickets['tt_complaint_name'] : '-',
				'tt_remark' 		=> !empty($tickets['tt_remark']) ? $tickets['tt_remark'] : '-',
			],
			$tickets['customer_no'],
			[
				'acc_id'         => $tickets['profile_id'],
				'user_id'        => $this->user['idx'],
				'controller'     => 'ticket',
				'doc_id'         => $tickets['tt_id'],
				'send_method'    => 'manual',
				'email_starter'  => $email_starter,
				'doc_type'       => $doc_type,
			]
		);
	}
	
	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'tt_no', 'label' => 'TT #', 'rules' => 'trim'),
						array('field' => 'tt_id', 'label' => 'TT ID', 'rules' => 'trim'),
						array('field' => 'parent_tt_no', 'label' => 'Parent TT #', 'rules' => 'trim'),
						array('field' => 'parent_tt_id', 'label' => 'Parent TT ID', 'rules' => 'trim'),
						array('field' => 'customer_no_name', 'label' => 'Customer', 'rules' => 'trim|required'),
						array('field' => 'customer_no', 'label' => 'Customer', 'rules' => 'trim|required'),
						array('field' => 'contact_no', 'label' => 'Contact', 'rules' => 'trim|required'),
						array('field' => 'datetime_open' , 'label' => 'Date open', 'rules' => 'trim|required'),
						array('field' => 'datetime_close', 'label' => 'Date closed', 'rules' => 'trim'),
						array('field' => 'status', 'label' => 'Status', 'rules' => 'trim|required'),
						array('field' => 'duration', 'label' => 'Duration', 'rules' => 'trim'),
						array('field' => 'tt_complaint_id', 'label' => 'Complaint', 'rules' => 'trim'),
						array('field' => 'tt_sof_id', 'label' => 'SOF', 'rules' => 'trim'),
						array('field' => 'tt_cof_id', 'label' => 'COF', 'rules' => 'trim'),
						array('field' => 'tt_category', 'label' => 'Category', 'rules' => 'trim'),
						array('field' => 'tt_remark', 'label' => 'Remark', 'rules' => 'trim'),
						array('field' => 'tt_email', 'label' => 'Email', 'rules' => 'trim'),
					),
					'save_setting' => array (
						//~ array('field' => 'level2_pic', 'label' => '2nd Level PIC', 'rules' => 'trim|required'),
						//~ array('field' => 'level3_pic', 'label' => '3rd Level PIC', 'rules' => 'trim|required'),
						//~ array('field' => 'level4_pic', 'label' => '4th Level PIC', 'rules' => 'trim|required'),
						//~ array('field' => 'level2_email', 'label' => '2nd Level email', 'rules' => 'trim'),
						//~ array('field' => 'level3_email', 'label' => '3rd Level email', 'rules' => 'trim'),
						//~ array('field' => 'level4_email', 'label' => '4th Level email', 'rules' => 'trim'),
						//~ array('field' => 'level2_contact', 'label' => '2nd Level contact#', 'rules' => 'trim'),
						//~ array('field' => 'level3_contact', 'label' => '3rd Level contact#', 'rules' => 'trim'),
						//~ array('field' => 'level4_contact', 'label' => '4th Level contact#', 'rules' => 'trim'),
						// array('field' => 'sms_email_template', 'label' => 'SMS / Email Template', 'rules' => 'trim|required'),
						// array('field' => 'sms_email_overdue_template', 'label' => 'SMS / Email Overdue Template', 'rules' => 'trim|required'),
					),
					'save_ticket_issues' => array(
						array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|required'),
						array('field' => 'desc', 'label' => 'Description', 'rules' => 'trim|required'),
						array('field' => 'sel_category', 'label' => 'Category', 'rules' => 'trim|required')
					)
					
					
				);
		
		$this->form_validation->set_rules($config[$mode]);
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
			//$po_attachment_print = array_values($po_attachment_print);

			// Loop through each file
			for( $i=0 ; $i < $total ; $i++ ) {
			  	//Get the temp file path
			  	$tmp_path = $file_arr['tmp_name'][$i];

			  	//Make sure we have a file path
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
							//do pdf conversion, each page to one image and save it in /res
							if (extension_loaded('imagick')){
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/temp/");
							}
						}
						
						$this->common_model->insert_tmp_file_attachment($file_name , $local_path, 'ticket', $temp_id, $remark, $is_print, '', 0, 0, $this->user['idx']);
					}

		    	}
			}
		}
	}

	public function ajax_send_reply_to_customer() {
		$tt_id = $this->input->post('tt_id');
		$tt_no = $this->input->post('tt_no');
		$customer_no = $this->input->post('customer_no');
		$customer_reply = $this->input->post('customer_reply');

		$this->ticket_model->send_reply_to_customer($tt_id, $tt_no, $customer_no, $customer_reply);

		$return_val = ['status' => 'SUCC', 'message' => 'Reply sent to customer successfully.'];

		echo json_encode($return_val);
	}
}

