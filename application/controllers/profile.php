<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
//~ include_once( APPPATH . 'controllers/package.php' );

class Profile extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		check_acl('profile');
		$this->load->model('profile_model');
		$this->load->model('common_model');

		$this->load->helper('image_helper');

		$this->account_status = array('P' => 'Signup', 'A' => 'Activated', 'S' => 'Suspended', 'T' => 'Terminated', 'C' => 'Cancelled');
    }

    public function index()
    {

    	$row_html = $this->profile_rows(1);

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();

			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			//$data['sel_status'] = $post_data['sel_status'];
		} else {
			$profile_filter = get_session_filter('profile_filter');
			$data['page_item_no'] = $profile_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $profile_filter['txt_search'] ?? '';
			//$data['sel_status'] = $profile_filter['sel_status'] ?? 'P';
		}

		$data['page_title'] 		= 'Customer Profile';
		$data['form_action'] 		= base_url('profile');

		$data['msg'] 				= $this->msg;

		//access to view or modify
		$access_view   = check_acl('profile', 'V', false ) == true ? 1 : 0 ;
		$access_modify = check_acl('profile', 'M', false ) == true ? 1 : 0 ;
		$data['view_only'] = 0 ; 
		if( $access_view == 1 && $access_modify == 0 ){
			$data['view_only'] = 1 ;
		}

		$data['row_html'] = $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('profile/index',$data);
		$this->load->view('templates/footer');

    }

    public function profile_rows($returnOnly = 0)
    {
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$profile_filter = get_session_filter('profile_filter');
			$post_data['page_item_no'] = $profile_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $profile_filter['txt_search'] ?? '';
			//$post_data['sel_status'] = $profile_filter['sel_status'] ?? 'P';
		}

    	$total_row = 0;
		$page_item_no  = empty($post_data['page_item_no']) ? 0 : $post_data['page_item_no'];
		$txt_search    = $post_data['txt_search'] ?? '';
		//$sel_status = $post_data['sel_status'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			//'sel_status' => $sel_status,
		);
		set_session_filter('profile_filter', $session_array);

		//where query params should be in an array pass to model
		$query_where = array();
		$query_where['txt_search'] = $txt_search;
		//$query_where['sel_status'] = $sel_status;

		$result = $this->profile_model->get_profile_listing($page_item_no,$query_where);

		$data['row_data'] 			= $result['row'];

		$data['pagination'] 		= paginationSettingsAjax('', $result['total_row'], $page_item_no, $_SESSION['config']['max_page_item']);

		//access to view or modify
		$access_view   = check_acl('profile', 'V', false ) == true ? 1 : 0 ;
		$access_modify = check_acl('profile', 'M', false ) == true ? 1 : 0 ;
		$data['view_only'] = 0 ; 
		if( $access_view == 1 && $access_modify == 0 ){
			$data['view_only'] = 1 ;
		}

		$html = $this->load->view('profile/profile_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

    }

    function add_profile($acc_id = 0)
	{
		$chk_temp_id = $this->input->post('temp_id');
		$profile_data = $this->profile_model->get_profile($acc_id);
		$profile_data['acc_type'] = $profile_data['acc_type'];

		$profile_pic_1[] = [
			'pic_id'            => '',
			'pic_acc_id'        => $acc_id,
			'pic_designation'   => $profile_data['pic_designation'],
			'pic_name'          => $profile_data['pic_name'],
			'pic_mobile'        => $profile_data['pic_mobile'],
			'pic_email_1'       => $profile_data['pic_email_1'],
			'pic_email_2'       => $profile_data['pic_email_2'],
			'pic_nric_passport' => $profile_data['pic_nric_passport'],
			'pic_dob'           => $profile_data['pic_dob'],
			'pic_gender'        => $profile_data['pic_gender'],
			'pic_race'          => $profile_data['pic_race']
		];

		$profile_pic_2 = $this->profile_model->get_profile_pic($acc_id);
		$data['profile_pic'] = array_merge($profile_pic_1, $profile_pic_2);

		$pic_id = $this->input->post('pic_id');
		if (!empty($pic_id)) {
			$fields = [
				'pic_id', 'acc_id', 'pic_designation', 'pic_name', 'pic_mobile',
				'pic_email_1', 'pic_email_2', 'pic_nric_passport', 'pic_dob',
				'pic_gender', 'pic_race'
			];

			$temp_profile_pic = [];
			foreach ($pic_id as $i => $id) {
				$entry = [];
				foreach ($fields as $field) {
					$entry[$field] = $this->input->post($field)[$i] ?? '';
				}
				$temp_profile_pic[] = [
					'pic_id'            => $entry['pic_id'],
					'pic_acc_id'        => $entry['acc_id'],
					'pic_designation'   => $entry['pic_designation'],
					'pic_name'          => $entry['pic_name'],
					'pic_mobile'        => $entry['pic_mobile'],
					'pic_email_1'       => $entry['pic_email_1'],
					'pic_email_2'       => $entry['pic_email_2'],
					'pic_nric_passport' => $entry['pic_nric_passport'],
					'pic_dob'           => $entry['pic_dob'],
					'pic_gender'        => $entry['pic_gender'],
					'pic_race'          => $entry['pic_race'],
				];
			}
			$data['profile_pic'] = $temp_profile_pic;
		}

		$data['input'] = $profile_data;
		$data['create_login_alert'] = ($profile_data['acc_type'] == 'b' || $profile_data['acc_type'] == 'o' || $profile_data['acc_type'] == 'r') ? '1' : '0';
		$data['page_title'] = 'Customer Profile';
		$data['form_action'] = base_url('profile/save_profile');
		$data['hidden'] = ['url_after_save' => ''];

		$data['sel_state_list'] = $this->common_model->get_state_list();
		$data['sel_profile_list'] = $this->common_model->get_profile_type_list();
		$data['sel_dealer_list'] = $this->common_model->get_dealer_list();
		$data['sel_marital_status_list'] = $this->common_model->get_marital_status_list();
		$data['profile_pic_count'] = $this->common_model->get_table('sys_config', 'val', "key = 'profile_pic_count'")[0]['val'];

		$this->load->model('customer_model');
		$accounts_result = $this->customer_model->get_customer_listing_by_profile($acc_id);
		$data['account_rows'] = empty($acc_id) ? [] : $accounts_result['row'];

		$this->load->model('asset_model');
		$assets_result = $this->asset_model->get_assets_by_customer($acc_id);
		$data['asset_rows'] = empty($acc_id) ? [] : $assets_result['row'];

		$data['account_status'] = $this->account_status;
		$data['msg'] = $this->msg;
		$data['not_same_addr'] = 0;
		$data['profile_linked'] = ($rows = $this->common_model->get_table('profile_auth', 'is_activate', 'acc_id = "'.$acc_id.'" '))
									&& isset($rows[0]['is_activate'])
									? $rows[0]['is_activate']
									: 0;

		$data['category_map'] = [
			'b' => 'label-warning',
			'd' => 'label-danger',
			'e' => 'label-warning',
			'f' => 'label-primary',
			'r' => 'label-success', 
			's' => 'label-info',
			'w' => 'label-default',
		];

		$data['status_colors'] = [
			'P' => 'grey',
			'A' => 'green',
			'S' => 'orange',
			'T' => 'red',
			'C' => 'pink',
		];

		if(!empty($profile_data['acc_id'])) {
			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');
			$data['created_by'] = $user_list[$profile_data['created_by']] ?? '-';
			$data['created_at'] = $profile_data['created_at'] ?? '-';
			$data['updated_by'] = $user_list[$profile_data['updated_by']] ?? '-';
			$data['updated_at'] = $profile_data['updated_at'] ?? '-';	
		}

		$file_result = ($profile_data['acc_id'] != 0)
			? $this->common_model->get_file_attachment('profile', $acc_id)
			: [];

		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('profile', $chk_temp_id);
			foreach ($leftover_attach as $attach) {
				$file_result[] = $attach;
			}
		}

		if (!empty($file_result)) {
			foreach ($file_result as $key => $val) {
				$val['local_path'] = str_replace("%", "%25", stripslashes($val['local_path']));
				$file_info = pathinfo($val['local_path']);
				$extension = strtolower($file_info['extension']);
				$file_result[$key]['extension'] = $extension;

				if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
					$file_result[$key]['file_type'] = 'image';
					$thumbnail = 'thumbnail_' . $file_info['filename'] . '.jpeg';
				} elseif ($extension === 'pdf') {
					$file_result[$key]['file_type'] = 'pdf';
					$thumbnail = $file_info['filename'] . '-0.jpg';
				} elseif (in_array($extension, ['mp4', 'avi', 'mkv', 'mov', 'webm'])) {
					$file_result[$key]['file_type'] = 'video';
					$thumbnail = '';
				} else {
					$file_result[$key]['file_type'] = 'doc';
					$thumbnail = '';
				}

				$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail;
				$file_result[$key]['thumbnail_path'] = (!empty($thumbnail) && file_exists($this->config->item('upload_path') . $thumbnail_path))
					? $this->config->item('upload_url') . $thumbnail_path
					: base_url("images/" . ($file_result[$key]['file_type'] === 'pdf' ? 'pdf.png' : 'image.png'));

				$file_result[$key]['local_path'] = $this->config->item('upload_url') . $val['local_path'];
				$file_result[$key]['created_by'] = $val['created_by'];
				$file_result[$key]['file_id'] = $val['file_id'];
				$file_result[$key]['is_temp'] = $val['is_temp'];
			}
		}

		foreach ($file_result as $file) {
			$upload_url = $this->config->item('upload_url') . '/upload';
			if (strpos($file['local_path'], $upload_url) === 0) {
				if (strpos($file['local_path'], $upload_url . '/IC_') === 0) {
					$data['ic_attachment'][] = $file;
				} elseif (strpos($file['local_path'], $upload_url . '/SSM_') === 0) {
					$data['ssm_attachment'][] = $file;
				} elseif (strpos($file['local_path'], $upload_url . '/AUTH_') === 0) {
					$data['auth_attachment'][] = $file;
				} else {
					$data['others_attachment'][] = $file;
				}
			} else {
				$data['attachment'][] = $file;
			}
		}

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('profile/profile_detail', $data);
		$this->load->view('templates/footer');
	}

    function save_profile()
    {
		//for delete
		/*if ($this->input->post('btDelete') != '' && $this->input->post('customer_no') != '')
		{
			$this->session->set_flashdata("warning_msg", 'System not allow to delete customer');
			//~ $this->edit_customer($this->input->post('customer_no'));
			redirect("customer/edit_customer/".$this->input->post('customer_no'));
		}else 
		{*/
		//AJAX
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'profile', 'error_keys' => array());

		$post_data = $this->input->post();
		//_debug_array($post_data); exit;

		$this->set_form_validation('save', count($post_data['pic_id']),$post_data['acc_type']);
		if ($this->form_validation->run() == false) {
			$this->msg['error_msg'] = validation_errors();
			//if ($this->input->post('customer_no')=='')

			//AJAX - if ajax, then no need gen_post, just display err to user
			$ajax_return['err_msg'] = validation_errors();
			$ajax_return['error_keys'] = validation_error_array();
			echo json_encode($ajax_return);
			return false;
			
			//AJAX - if ajax, then no need gen_post, just display err to user
			$ajax_return['err_msg'] = validation_errors();
			$ajax_return['error_keys'] = validation_error_array();
			echo json_encode($ajax_return);
			return false;

			$this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));

			$this->add_profile($post_data['acc_id']);
			//else  $this->edit_customer($this->input->post('customer_no'));				
		} else {

			//check if username exists
			if (empty($post_data['acc_id'])) {
				$current_username = '';
			} else {
				$profile_data = $this->profile_model->get_profile($post_data['acc_id']);
				$current_username = $profile_data['acc_username'];
			}

			//chk username exists
			$username_chk = $this->profile_model->chk_username_exists($post_data['acc_username'], $current_username);

			if ($username_chk) {
				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = 'Profile Username already exists.';
				$ajax_return['error_keys'] = array('acc_username');
				echo json_encode($ajax_return);
				return false;
			}

			$post_data['user_idx'] = $this->user['idx'];

			$result_acc_id = $this->profile_model->profile_insert($post_data);

			$chk_temp_id = $this->input->post('temp_id');
			if (!empty($chk_temp_id)) {
				$leftover_attach = $this->common_model->get_temp_file_attachment('profile', $chk_temp_id); 
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
						
						$this->common_model->insert_file_attachment($file_name , $local_path, 'profile', $result_acc_id, $remark, $is_print, '', 0, 0, $this->user['idx']);

						//delete from temp db

						//unlink file
						$this->common_model->remove_temp_attachment($val['file_id']);
					}

				}
			}

			//handling attachments	
			if (isset($_FILES['attach_attachment'])) {
				log_message('error',print_r($_FILES['attach_attachment'],true));
				$total = count($_FILES['attach_attachment']['name']);
				$attach_attachment_remark = $this->input->post('attach_attachment_remark');
				$fileCtg = $this->input->post('fileCtg');
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
				$fileCtg = is_array($fileCtg) ? array_values($fileCtg) : array($fileCtg);
				
				// Loop through each file
				for( $i=0 ; $i < $total ; $i++ ) {
				  	//Get the temp file path
				  	$tmp_path = $_FILES['attach_attachment']['tmp_name'][$i];

				  	//Make sure we have a file path
				  	if ($tmp_path != "") {
						$prefix 		= date('YmdHis');
						$file_name 		= $_FILES['attach_attachment']['name'][$i];
						$prefix_n_file_name = $fileCtg[$i] . $prefix . "_" . $file_name;
						$file_path 		= $this->config->item('upload_path')."/upload/" . $prefix_n_file_name;
						$local_path 	= "/upload/" . $prefix_n_file_name;
						$remark 		= $fileCtg[$i] . $attach_attachment_remark[$i];
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
							
							$this->common_model->insert_file_attachment($fileCtg[$i] .$file_name , $local_path, 'profile', $result_acc_id, $remark, $is_print, '', '', 0, $this->user['idx']);
						}
			    	}
				}
			}

			/*if (empty($_POST['url_after_save'])) {
				$this->session->set_flashdata("msg", 'Profile Saved!');
				// redirect("profile");
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'profile';
				echo json_encode($ajax_return);
				return false;
			} else {
				// redirect($_POST['url_after_save']);
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = $_POST['url_after_save'];
				echo json_encode($ajax_return);
				return false;
			}*/

			if (empty($_POST['url_after_save'])) {
				$this->session->set_flashdata("msg", 'Profile Saved!');
				//redirect("registration");
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
			} else {
				//redirect($_POST['url_after_save']);
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = $_POST['url_after_save'];
				echo json_encode($ajax_return);
			}
		}

		//}
    }

	function set_form_validation($mode = 'search', $pic_count=0, $acc_type = 'r')
	{
		$business_required = 'trim';
		if ($acc_type == 'b' || $acc_type == 'o') {
			$business_required = 'trim|required';
		}
		
		$residential_required = 'trim';
		if ($acc_type == 'r') {
			$residential_required = 'trim|required';
		}

		$config = array(
			'search' => array(
				array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
				array('field' => 'sel_status', 'label' => 'Status', 'rules' => ''),
				array('field' => 'sel_category', 'label' => 'Category', 'rules' => ''),
			),
			'save' => array(
				array('field' => 'acc_id', 'label' => 'ID', 'rules' => 'trim'),
				array('field' => 'acc_name', 'label' => 'Name', 'rules' => 'trim|required'),
				array('field' => 'icno', 'label' => 'IC No.', 'rules' => $residential_required),
				array('field' => 'acc_mobileno', 'label' => 'Mobile No.', 'rules' => 'trim'),
				array('field' => 'acc_email', 'label' => 'Email', 'rules' => 'trim|required'),
				array('field' => 'acc_type', 'label' => 'Type', 'rules' => 'trim|required'),

				array('field' => 'bill_unit_no', 'label' => 'Unit No', 'rules' => 'trim'),
				array('field' => 'bill_addr_1', 'label' => 'Bill Address Line 1', 'rules' => 'trim|required'),
				array('field' => 'bill_addr_2', 'label' => 'Bill Address Line 2', 'rules' => 'trim'),
				array('field' => 'bill_addr_3', 'label' => 'Bill Address Line 3', 'rules' => 'trim'),
				array('field' => 'bill_postcode', 'label' => 'Bill Postcode', 'rules' => 'trim|required'),
				array('field' => 'bill_city', 'label' => 'Bill City', 'rules' => 'trim|required'),
				array('field' => 'bill_state', 'label' => 'Bill State', 'rules' => 'trim|required'),

				array('field' => 'comp_name', 'label' => 'Company Name', 'rules' => $business_required),
				array('field' => 'ssm', 'label' => 'SSM#', 'rules' => $business_required),
				array('field' => 'tin', 'label' => 'Tin', 'rules' => 'trim'),
				array('field' => 'sst', 'label' => 'SSM#', 'rules' => 'trim'),
				array('field' => 'ttx', 'label' => 'TTX#', 'rules' => 'trim'),

				array('field' => 'so_id', 'label' => 'SO ID', 'rules' => 'trim'),

				array('field' => 'acc_username', 'label' => 'User Login', 'rules' => 'trim|required'),
				array('field' => 'acc_password', 'label' => 'Password', 'rules' => 'trim'),

				array('field' => 'marital_status', 'label' => 'Marital Status', 'rules' => 'trim'),
				array('field' => 'household', 'label' => 'Household', 'rules' => 'trim'),
				array('field' => 'nationality', 'label' => 'Nationality', 'rules' => 'trim'),
				array('field' => 'no_of_employee', 'label' => 'No. of Employees', 'rules' => 'trim'),
				array('field' => 'no_of_branches', 'label' => 'No. of Branches', 'rules' => 'trim'),
			),
		);

		for ($i = 0; $i < $pic_count; $i++) {
			$config['save'][] = array('field' => 'pic_id['.$i.']', 'label' => 'PIC ID', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_acc_id['.$i.']', 'label' => 'Acc ID', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_name['.$i.']', 'label' => 'Person In Charge', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_nric_passport['.$i.']', 'label' => 'NRIC/Passport', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_dob['.$i.']', 'label' => 'Date Of Birth', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_race['.$i.']', 'label' => 'Race', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_gender['.$i.']', 'label' => 'Gender', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_designation['.$i.']', 'label' => 'PIC Designation', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_mobile['.$i.']', 'label' => 'Mobile Phone', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_email_1['.$i.']', 'label' => 'Email 1', 'rules' => 'trim');
			$config['save'][] = array('field' => 'pic_email_2['.$i.']', 'label' => 'Email 2', 'rules' => 'trim');
			$config['save'][] = array('field' => 'delete['.$i.']', 'label' => 'Delete', 'rules' => 'trim');
		}

		$this->form_validation->set_rules($config[$mode]);
	}

	public function mobile_no_check($mobile) {
		if($this->input->post('nationality_dd') == 'Malaysian' && (substr($mobile, 0, 3) != '601' || !in_array(strlen($mobile), [11, 12]))) {
			$this->form_validation->set_message('mobile_no_check', 'Please enter a valid Malaysian\'s mobile no.');
			return false;
		}
		return true;
	}

	private function gen_post_attachments($file_arr=array())
	{

		$this->load->model('common_model');

		if (!empty($file_arr)) {	

			$post_back_attach = array();

			$total = count($file_arr['name']);
			$attach_attachment_remark = $this->input->post('attach_attachment_remark');
			$fileCtg = $this->input->post('fileCtg');
			$attach_attachment_print = array();

			$temp_id = $this->input->post('temp_id');

			foreach ($file_arr as $key => $val) {
				$file_arr[$key] = array_values($val);
			}

			$attach_attachment_remark = array_values($attach_attachment_remark);
			//$po_attachment_print = array_values($po_attachment_print);
			$fileCtg = is_array($fileCtg) ? array_values($fileCtg) : array($fileCtg);

			// Loop through each file
			for( $i=0 ; $i < $total ; $i++ ) {
			  	//Get the temp file path
			  	$tmp_path = $file_arr['tmp_name'][$i];

			  	//Make sure we have a file path
			  	if ($tmp_path != "") {
					log_message('error',$fileCtg[$i]);
					$prefix 		= date('YmdHis');
					$file_name 		= $file_arr['name'][$i];;
					$prefix_n_file_name = $fileCtg[$i] . $prefix . "_" . $file_name;
					$file_path 		= $this->config->item('upload_path')."/temp/" . $prefix_n_file_name;
					$local_path 	= "/temp/" . $prefix_n_file_name;
					$remark 		= $fileCtg[$i] . $attach_attachment_remark[$i];
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
						
						$this->common_model->insert_tmp_file_attachment($fileCtg[$i] . $file_name , $local_path, 'profile', $temp_id, $remark, $is_print, '', 0, 0, $this->user['idx']);
					}

		    	}
			}
		}

	}

	function ajax_check_icno(){
		$icno = $this->input->post('icno');
		$acc_id = $this->input->post('acc_id');
		$result = $this->profile_model->check_profile_by_ic_and_type($icno, 'r', $acc_id);
		
		//if acc_id is not 0, means this is an edit
		//if($acc_id != 0){
			//echo json_encode(['exist' => $result != $acc_id && $result != 0]); 
		//}else{
			echo json_encode(['exist' => $result != 0]); 
		//}
		exit;
	}

	function ajax_check_ssm(){
		$ssm = $this->input->post('ssm');
		$acc_id = $this->input->post('acc_id');
		$result1 = $this->profile_model->check_profile_by_ssm_and_type($ssm, 'b', $acc_id);
		$result2 = $this->profile_model->check_profile_by_ssm_and_type($ssm, 'o', $acc_id);
		
		//if acc_id is not 0, means this is an edit
		//if($acc_id != 0){
			//echo json_encode(['exist' => $result != $acc_id && $result != 0]); 
		//}else{
			echo json_encode(['exist' => ($result1 != 0 || $result2 != 0)]); 
		//}
		exit;
	}

	function ajax_check_email(){
		$email = $this->input->post('email');
		$acc_id = $this->input->post('acc_id');
		$result = $this->profile_model->check_profile_by_email($email, $acc_id);

		//if acc_id is not 0, means this is an edit
		//if($acc_id != 0){
			//echo json_encode(['exist' => $result != $acc_id && $result != 0]); 
		//}else{
			echo json_encode(['exist' => $result != 0]); 
		//}
		exit;
	}

}