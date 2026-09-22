<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
//~ include_once( APPPATH . 'controllers/package.php' );

class Audit_docs extends DataPage_Controller {

	private $e_key = "1nf0n4l";

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		check_acl('audit');
		$this->load->model('common_model');
		$this->load->helper('image_helper');
		$this->load->helper('puppeteer_helper');
		$this->load->model('docs_log_model');
		$this->load->model('email_model');
    }

    public function index()
    {

    	//check permissions first - TODO!!! Important

		$total_row 		= 0;
		$page_item_no 	= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');

		$txt_search 	= $this->input->post('txt_search');
		$sel_file_type 	= $this->input->post('sel_file_type') ?: ($this->session->userdata('sel_file_type')?:'');
		
		$query_where = (!empty($sel_file_type)) ? $sel_file_type : '';

		$result = $this->common_model->get_audit_files($txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where);

		foreach ($result['row'] as &$value) {
			//$value['cs_type'] = ($query_where == 'cs_service') ? 'cs_service' : 'cs_problem';
		}

		$data['page_title'] 		= 'Audit Files';
		$data['page_desc'] 			= 'Audit Files';
		$data['form_action'] 		= base_url('audit_docs');
		$data['row_data'] 			= $result['row'];
		$data['txt_search'] 		= $txt_search;		
		$data['sel_file_type'] 		= $sel_file_type;
		$data['pagination'] 		= paginationSettings('', $result['total_row']);	
		$data['msg'] 				= $this->msg;

		$this->vars['jsfiles'][] = 'js/itelco/audit_doc.js';

		$this->session->set_userdata('last_sel_file_type', $sel_file_type);

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('audit/file_listing',$data);
		$this->load->view('templates/footer');
    }

    public function add() {

    	check_acl('audit','M');

    	$chk_temp_id = $this->input->post('temp_id');

    	$data['input'] = $this->common_model->audit_new_rec();

		$data['page_title'] 		= 'Upload a file to audit folder';
		$data['form_action'] 		= base_url('audit_docs/save_doc');
		$data['msg'] 				= $this->msg;

		if (!empty($chk_temp_id)) {
			$data['input']['temp_id'] = $chk_temp_id;
		}

		$data['input']['customer_no_display'] = '';
		$data['input']['bill_no_display'] = '';

		//$file_result	= ($data['input']['cs_id'] != '') ? $this->common_model->get_file_attachment('support', $data['input']['cs_id']) : [];

		$file_result = [];

		//shud only be temp for audit... if success move temp to audit file permanently
		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('audit', $chk_temp_id, '', 0); 
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

		$this->vars['jsfiles'][] = 'js/itelco/audit_doc.js';
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('audit/upload_doc',$data);
		$this->load->view('templates/footer');

    }

	public function autocomplete_load_customer()
	{
		$this->load->model('customer_model');
		$return_val = array();
		$customer = $this->customer_model->get_autocomplete_load_customer($this->input->post('keyword'));
		
		$return_val = $customer;
		
		echo json_encode($return_val);
	}

	public function autocomplete_load_bill()
	{
		$this->load->model('bill_model');
		$customer_no = $this->input->post('customer_no');
		$return_val = array();
		$bills = $this->bill_model->get_autocomplete_load_bill($customer_no, $this->input->post('keyword'));
		
		$return_val = $bills;

		echo json_encode($return_val);
	}

	function set_form_validation($mode = 'save_doc', $file_type = 'bill')
	{

		$customer_no_rule = 'trim';
		$bill_no_rule = 'trim';

		if ($file_type == 'bill') {
			$customer_no_rule = 'trim|required';
			$bill_no_rule = 'trim|required';
		} else {
			//fallback
		}

		$config = array(
					'save_doc' => array (
						array('field' => 'customer_no', 'label' => 'Customer Number', 'rules' => $customer_no_rule),
						array('field' => 'bill_no', 'label' => 'Bill Number', 'rules' => $bill_no_rule),
						array('field' => 'link_doc', 'label' => 'File Type', 'rules' => 'trim|required'),
					),
		);
		
		$this->form_validation->set_rules($config[$mode]);
	}

	public function save_doc(){

		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'audit_docs', 'error_keys' => array());
		$main_return_msg = 'Audit doc added.';

		$post_back 		= $this->input->post(NULL, TRUE);

		$audit_acl = check_acl('audit','M', false);
		if (!$audit_acl) {
			$ajax_return['err_msg'] = array('No permissions to upload audit file.');
			$ajax_return['error_keys'] = array();
			echo json_encode($ajax_return);
			return false;
		}

		$this->set_form_validation('save_doc',$post_back['link_doc']); //set rules for form validation

		//set error message template if form_validation run false
		$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1">','</label>');	
		if($this->form_validation->run() == false) 
		{
			//AJAX - if ajax, then no need gen_post, just display err to user
			$ajax_return['err_msg'] = validation_errors();
			$ajax_return['error_keys'] = validation_error_array();
			echo json_encode($ajax_return);
			return false;

		} else {

			$this->load->library('session');
			$user_data 	= $this->session->userdata('user');
			$username 	= $user_data['username'];

			$chk_temp_id = $this->input->post('temp_id');
			$file_type = $post_back['link_doc'];
			if (!empty($chk_temp_id)) {
				$leftover_attach = $this->common_model->get_temp_file_attachment('audit', $chk_temp_id, '', 0, 0); 
				foreach ($leftover_attach as $val) {

					$val['local_path'] = stripslashes($val['local_path']);
					$val['local_path'] = str_replace("%", "%25", $val['local_path']);

					$tmp_file_info = pathinfo($val['local_path']);

					$tmp_path = $this->config->item('upload_path').$val['local_path'];

					$file_name 		= $tmp_file_info['filename'].'.'.$tmp_file_info['extension'];
					$prefix_n_file_name = $file_name;
					//$file_path 		= $this->config->item('upload_path')."/upload/" . $file_name;
					$audit_path = $this->config->item('upload_path').'/audit/'.$file_type.'/'.basename($file_name);
					$local_path 	= "/audit/" . $file_type . "/" . $file_name;
					$remark 		= $val['remark'];
					$is_print		= $val['is_print'];

					if (copy($tmp_path, $audit_path)) {
						$file_info = pathinfo($local_path);
						$extension = strtolower($file_info['extension']);
						
						if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
							list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
							resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
						} else if (in_array($extension, ['pdf'])) {
							//do pdf conversion, each page to one image and save it in /res
							if (extension_loaded('imagick')){
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/audit/".$file_type."/");
							}
						}
						
						//TODO change to audit file insert
						//$this->common_model->insert_file_attachment($file_name , $local_path, 'support', $result, $remark, $is_print, '', 0, 0, $this->user['idx']);

						//do a record insert for each auditable file
						$audit_data = array();
						$audit_data['file_name'] = $file_name;
						$audit_data['local_path'] = $local_path;
						$audit_data['link_doc'] = $file_type;
						$audit_data['link_doc_ref'] = '';
						$audit_data['remark'] = $post_back['remark'] ?? '';
						$audit_data['reg_no'] = '0';
						$audit_data['so_id'] = '0';
						$audit_data['bill_no'] = $post_back['bill_no'] ?? '';
						$audit_data['customer_no'] = $post_back['customer_no'] ?? '';
						$this->common_model->insert_audit_record($audit_data);

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
					$prefix_n_file_name = $file_name;
					//$file_path 		= $this->config->item('upload_path')."/upload/" . $prefix_n_file_name;
					$audit_path = $this->config->item('upload_path').'/audit/'.$file_type.'/'.basename($file_name);
					$local_path 	= "/audit/" . $file_type . "/" . $file_name;
					$remark 		= $attach_attachment_remark[$i];
					$is_print		= (isset($attach_attachment_print[$i])?$attach_attachment_print[$i]:0);

						if (move_uploaded_file($tmp_path, $audit_path)) {
							$file_info = pathinfo($local_path);
							$extension = strtolower($file_info['extension']);
							
							if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
								list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
								resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
							} else if (in_array($extension, ['pdf'])) {
								//do pdf conversion, each page to one image and save it in /res
								if (extension_loaded('imagick')){
									_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/audit/".$file_type."/");
								}
							}
							
							//TODO change to audit file insert
							//$this->common_model->insert_file_attachment($file_name , $local_path, 'support', $result, $remark, $is_print, '', '', 0, $this->user['idx']);

							//do a record insert for each auditable file
							$audit_data = array();
							$audit_data['file_name'] = $file_name;
							$audit_data['local_path'] = $local_path;
							$audit_data['link_doc'] = $file_type;
							$audit_data['link_doc_ref'] = '';
							$audit_data['remark'] = $post_back['remark'] ?? '';
							$audit_data['reg_no'] = '0';
							$audit_data['so_id'] = '0';
							$audit_data['bill_no'] = $post_back['bill_no'] ?? '';
							$audit_data['customer_no'] = $post_back['customer_no'] ?? '';
							$this->common_model->insert_audit_record($audit_data);

						}
					}
				}
			}

			$this->session->set_flashdata("msg", $main_return_msg);
			
			// redirect(base_url( 'customer/customer_support_list') );
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'audit_docs';
			echo json_encode($ajax_return);
			return false;

		}

	}

}