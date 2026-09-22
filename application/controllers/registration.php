<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

include_once(APPPATH . 'core/DataPage_Controller.php');
//~ include_once( APPPATH . 'controllers/package.php' );

class Registration extends DataPage_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->helper('custom_helper');
		$this->load->library(array('form_validation', 'session', 'upload'));
		$this->load->helper(array('url', 'html', 'form'));

		check_acl('registration');
		$this->load->model('registration_model');
		$this->load->model('common_model');
		$this->load->model('building_model');
		$this->load->model('docs_log_model');
		$this->load->model('email_model');

		$this->load->helper('image_helper');

		$this->registration_status = array('P' => 'Pending', 'F' => 'Follow Up', 'S' => 'Sales Order', 'C' => 'Cancelled');
	}

	public function index()
	{

		$row_html = $this->registration_rows(1);

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();

			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['sel_status'] = $post_data['sel_status'];
			$data['sel_installation'] = $post_data['sel_installation'];
			$data['sel_building'] = $post_data['sel_building'];
		} else {
			$registration_filter = get_session_filter('registration_filter');
			$data['page_item_no'] = $registration_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $registration_filter['txt_search'] ?? '';
			$data['sel_status'] = $registration_filter['sel_status'] ?? 'P';
			$data['sel_installation'] = $registration_filter['sel_installation'] ?? 'all';
			$data['sel_building'] = $registration_filter['sel_building'] ?? 'all';
		}

		$data['page_title'] = 'Registration';
		$data['form_action'] = base_url('registration');
		$status_arr = $this->registration_status;
		$data['sel_status_list'] = $status_arr;

		$data['msg'] = $this->msg;

		//access to view or modify
		$access_view = check_acl('registration', 'V', false) == true ? 1 : 0;
		$access_modify = check_acl('registration', 'M', false) == true ? 1 : 0;
		$data['view_only'] = 0;
		if ($access_view == 1 && $access_modify == 0) {
			$data['view_only'] = 1;
		}

		$data['row_html'] = $row_html;

		$data['sel_building_list'] = $this->common_model->get_building_list();

		$this->vars['jscripts'][] = 'var inDetail = false;';

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('registration/index', $data);
		$this->load->view('templates/footer');
	}

	public function registration_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$registration_filter = get_session_filter('registration_filter');
			$post_data['page_item_no'] = $registration_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $registration_filter['txt_search'] ?? '';
			$post_data['sel_status'] = $registration_filter['sel_status'] ?? 'P';
			$post_data['sel_installation'] = $registration_filter['sel_installation'] ?? 'all';
			$post_data['sel_building'] = $registration_filter['sel_building'] ?? 'all';
		}


		//post values , must also run by session values
		$total_row = 0;
		$page_item_no = ($post_data['page_item_no'] == '') ? 0 : $post_data['page_item_no'];
		$txt_search = $post_data['txt_search'];
		$sel_status = $post_data['sel_status'];
		$sel_installation = $post_data['sel_installation'];
		$sel_building = $post_data['sel_building'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_status' => $sel_status,
			'sel_installation' => $sel_installation,
			'sel_building' => $sel_building,
		);
		set_session_filter('registration_filter', $session_array);

		//where query params should be in an array pass to model
		$query_where = array();
		$query_where['txt_search'] = $txt_search;
		$query_where['sel_status'] = $sel_status;
		$query_where['sel_installation'] = $sel_installation;
		$query_where['sel_building'] = $sel_building;

		$result = $this->registration_model->get_registrations($page_item_no, $query_where);

		//parse before view
		$status_arr = $this->registration_status;
		$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');
		$dealer_list = array_column($this->common_model->get_dealer_list(), 'name', 'dealer_no');
		foreach ($result['row'] as $key => $row) {
			$result['row'][$key]['status_text'] = (isset($status_arr[$row['status']]) ? $status_arr[$row['status']] : '');

			$source_text = '';
			if ($row['created_by'] == 0) {
				if ($row['agent_id'] != 0) {
					$source_text = $dealer_list[$row['agent_id']] . " (Agent)";
				} else {
					$source_text = 'Self Registration';
				}
			} else {
				$source_text = $user_list[$row['created_by']];
			}
			$result['row'][$key]['source_text'] = $source_text;

			$result['row'][$key]['preferred_installation'] = '-';

			if (!empty($row['preferred_install_datetime'])) {
				$datetime = new DateTime($row['preferred_install_datetime']);
				$end = clone $datetime;
				$end->modify('+2 hours');
				$result['row'][$key]['preferred_installation'] =
					$datetime->format('d/m/Y')
					. '<br><small class="pink">'
					. $datetime->format('g:i A')
					. ' - '
					. $end->format('g:i A')
					. '</small>';
			}

			if ($row['type'] == 'b' && !empty($row['comp_name'])) {
				$result['row'][$key]['name'] = $row['comp_name'];
			}

		}

		$data['row_data'] = $result['row'];
		$data['pagination'] = paginationSettingsAjax('', $result['total_row'], $page_item_no, $_SESSION['config']['max_page_item']);

		//access to view or modify
		$access_view = check_acl('registration', 'V', false) == true ? 1 : 0;
		$access_modify = check_acl('registration', 'M', false) == true ? 1 : 0;
		$data['view_only'] = 0;
		if ($access_view == 1 && $access_modify == 0) {
			$data['view_only'] = 1;
		}

		$html = $this->parser->parse('registration/registration_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}

	function add_registration($id = 0)
	{
		$chk_temp_id = $this->input->post('temp_id');
		$customer = $this->registration_model->get_registration($id);

		if (!empty($chk_temp_id)) {
			$customer['temp_id'] = $chk_temp_id;
		}

		$data['inputlength'] = [
			'name' => 100,
			'icno' => 20,
			'phone' => 15,
			'email' => 50,
			'comp_name' => 100,
			'ssm' => 20,
			'tin' => 80,
			'bill_unit_no' => 20,
			'bill_addr_1' => 100,
			'bill_addr_2' => 100,
			'bill_addr_3' => 100,
			'bill_postcode' => 7,
			'bill_city' => 20,
			'ins_unit_no' => 20,
			'ins_addr_1' => 100,
			'ins_addr_2' => 100,
			'ins_addr_3' => 100,
			'ins_postcode' => 7,
			'ins_city' => 20,
		];

		$data['input'] = $customer;
		$data['page_title'] = 'Add Registration';
		$data['form_action'] = base_url('registration/save_registration');
		$hidden['url_after_save'] = '';
		$data['hidden'] = $hidden;

		/*$data['sel_package_list'] 			= $this->common_model->get_package_list($data['input']['category']);
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
		$data['sel_category_list'] 			= $this->common_model->get_category_list();
		$data['sel_state_list'] 			= $this->common_model->get_state_list();
		$data['sel_building_list'] 			= $this->common_model->get_building_list();
		$data['sel_marital_status_list'] 	= $this->common_model->get_marital_status_list();
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();*/

		$data['sel_state_list'] = $this->common_model->get_state_list();
		$data['sel_dealer_list'] = format_dealer_list_hierarchy($this->common_model->get_dealer_list() ?? []);

		$data['msg'] = $this->msg;

		$data['not_same_addr'] = 0;

		$data['buildings'] = $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');

		$preferredDateTime = explode(",", $customer['preferred_contact_date_time']);
		$data['day'] = '';
		$data['time'] = '';

		if (!empty($customer['preferred_contact_date_time'])) {
			$data['day'] = ucfirst($preferredDateTime[0]);
			$timeRange = $preferredDateTime[1];
			list($from, $to) = explode('-', $timeRange);
			$data['time'] = strtoupper(
				preg_replace('/(\d+)(am|pm)/i', '$1:00$2', $from)
			);
			$data['time'] .= ' - ';
			$data['time'] .= strtoupper(
				preg_replace('/(\d+)(am|pm)/i', '$1:00$2', $to)
			);
		}
		$data['specific_date'] = $preferredDateTime[2] ?? '';

		$data['preferred_install_date'] = '';
		$data['preferred_install_time'] = '';
		if (!empty($customer['preferred_install_datetime'])) {
			$datetime = new \DateTime($customer['preferred_install_datetime']);
			$data['preferred_install_date'] = $datetime->format('Y/m/d');
			$data['preferred_install_time'] = $datetime->format('H:i');
		}

		if (!empty($customer['reg_no'])) {
			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');
			if ($customer['created_by'] == 0) {
				if ($customer['agent_id'] != 0) {
					$dealer_list = array_column($this->common_model->get_dealer_list(), 'name', 'dealer_no');
					$data['created_by'] = $dealer_list[$customer['agent_id']] . " (Agent)";
				} else {
					$data['created_by'] = '<span style="text-decoration: underline;">Self Registration</span>';
				}
			} else {
				$data['created_by'] = $user_list[$customer['created_by']];
			}
			if ($customer['updated_by'] == 0) {
				$data['updated_by'] = '-';
				$data['updated_at'] = '-';
			} else {
				$data['updated_by'] = $user_list[$customer['updated_by']];
				$data['updated_at'] = $customer['updated_at'] ?? '-';
			}
			$data['created_at'] = $customer['created_at'] ?? '-';
		}

		//attachments
		$file_result = ($customer['reg_no'] != '') ? $this->common_model->get_file_attachment('profile', 0, '', 0, $data['input']['reg_no']) : [];

		//post back attachments
		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('profile', 0, '', 0, $chk_temp_id);
			//debugarr($leftover_attach);
			//unset($_SESSION['po']['attachments']);
			//file_id, local_path, file_name, remark, created_by, is_print  
			foreach ($leftover_attach as $attach) {
				array_push($file_result, $attach);
			}
		}

		if (!empty($file_result)) {
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

					$file_result[$key]['thumbnail_path'] = (file_exists($this->config->item('upload_path') . $thumbnail_path)) ? $this->config->item('upload_url') . $thumbnail_path : base_url("images/image.png");
				} elseif (in_array($extension, ['pdf'])) {
					$file_result[$key]['file_type'] = 'pdf';

					$thumbnail_filename = $file_info['filename'] . '-0.jpg';

					$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail_filename;

					$file_result[$key]['thumbnail_path'] = (file_exists($this->config->item('upload_path') . $thumbnail_path)) ? $this->config->item('upload_url') . $thumbnail_path : base_url("images/pdf.png");
				} elseif (in_array($extension, ['mp4', 'avi', 'mkv', 'mov', 'webm'])) {
					$file_result[$key]['file_type'] = 'video';
				} else {
					$file_result[$key]['file_type'] = 'doc';
				}

				$file_result[$key]['local_path'] = $this->config->item('upload_url') . $val['local_path'];

				$file_result[$key]['created_by'] = $val['created_by'];
				$file_result[$key]['file_id'] = $val['file_id'];

				$file_result[$key]['is_temp'] = $val['is_temp'];
			}
		}

		foreach ($file_result as $file) {
			if (strpos($file['local_path'], $this->config->item('upload_url') . '/upload') === 0) {
				if (strpos($file['local_path'], $this->config->item('upload_url') . '/upload/IC_') === 0) {
					$data['ic_attachment'][] = $file;
				} else if (strpos($file['local_path'], $this->config->item('upload_url') . '/upload/SSM_') === 0) {
					$data['ssm_attachment'][] = $file;
				} else if (strpos($file['local_path'], $this->config->item('upload_url') . '/upload/AUTH_') === 0) {
					$data['auth_attachment'][] = $file;
				} else {
					$data['others_attachment'][] = $file;
				}
			} else {
				$data['attachment'][] = $file;
			}
		}

		if (empty($customer['reg_no'])) {
			$this->vars['cssfiles'][] = '../css/theme/bootstrap-grid.css';
		} else {
			$this->vars['cssfiles'][] = '../../css/theme/bootstrap-grid.css';
		}

		$data['auto_skip_so'] = 0;
		if (!empty($customer)) {
			$config = $this->common_model->get_table('sys_config', '*', "`key` = 'auto_skip_so'");
			$data['auto_skip_so'] = !empty($config) ? $config[0]['val'] : 0;
		}

		$this->vars['jscripts'][] = 'var inDetail = true;';
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('registration/registration_detail', $data);
		$this->load->view('templates/footer');
	}

	function save_registration()
	{

		/*echo "<pre>";
		print_r($_POST);
		print_r($_FILES);
		echo "</pre>";
		exit;*/

		//AJAX
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'registration', 'error_keys' => array());

		//for delete
		/*if ($this->input->post('btDelete') != '' && $this->input->post('customer_no') != '')
		{
			$this->session->set_flashdata("warning_msg", 'System not allow to delete customer');
			//~ $this->edit_customer($this->input->post('customer_no'));
			redirect("customer/edit_customer/".$this->input->post('customer_no'));
		}else 
		{*/
		$post_data = $this->input->post();
		$validation_mode = 'save';

		$return_reg_no = 0;
		$return_id = 0;

		if (!empty($post_data['preferred_contact_date_time'])) {
			$validation_mode = 'update';
		}

		$this->set_form_validation($validation_mode, $this->input->post('type'));
		if ($this->form_validation->run() == false) {
			$this->msg['error_msg'] = validation_errors();
			//if ($this->input->post('customer_no')=='')

			//AJAX - if ajax, then no need gen_post, just display err to user
			$ajax_return['err_msg'] = validation_errors();
			$ajax_return['error_keys'] = validation_error_array();
			echo json_encode($ajax_return);
			return false;

			$this->gen_post_attachments((isset($_FILES['attach_attachment']) ? $_FILES['attach_attachment'] : array()));

			$id = $this->input->post('id');
			$this->add_registration($id);
			//else  $this->edit_customer($this->input->post('customer_no'));				
		} else {
			$post_data['user_idx'] = $this->user['idx'];

			if ($this->input->post('preferred_contact_date_time') == '' && $this->input->post('day') != '' && $this->input->post('from_time') != '' && $this->input->post('to_time') != '') {
				$preferredTime = $this->input->post('from_time') . $this->input->post('from_block') . '-' . $this->input->post('to_time') . $this->input->post('to_block');
				$post_data['preferred_contact_date_time'] = $this->input->post('day') . ',' . $preferredTime . ',' . $this->input->post('specific_date');
			}

			$post_data['preferred_install_datetime'] = '';
			if (!empty($post_data['preferred_install_date']) && !empty($post_data['preferred_install_time'])) {
				$post_data['preferred_install_datetime'] = str_replace('/', '-', $post_data['preferred_install_date']) . ' ' . $post_data['preferred_install_time'] . ':00';
			}

			if ($this->input->post('id') == 0 && $this->input->post('type') == 'r') {
				$post_data['comp_name'] = '';
				$post_data['ssm'] = '';
			}

			//_debug_array($post_data); _debug_array($_FILES); exit;

			$return_val = $this->registration_model->registration_insert($post_data);
			$return_reg_no = $return_val['reg_no'];
			$return_id = $return_val['id'];

			//file success, move any temp attachments to main
			$chk_temp_id = $this->input->post('temp_id');
			if (!empty($chk_temp_id)) {
				$leftover_attach = $this->common_model->get_temp_file_attachment('profile', 0, '', 0, $chk_temp_id);
				foreach ($leftover_attach as $val) {

					$val['local_path'] = stripslashes($val['local_path']);
					$val['local_path'] = str_replace("%", "%25", $val['local_path']);

					$tmp_file_info = pathinfo($val['local_path']);

					$tmp_path = $this->config->item('upload_path') . $val['local_path'];

					$file_name = $tmp_file_info['filename'] . '.' . $tmp_file_info['extension'];
					$prefix_n_file_name = $file_name;
					$file_path = $this->config->item('upload_path') . "/upload/" . $file_name;
					$local_path = "/upload/" . $file_name;
					$remark = $val['remark'];
					$is_print = $val['is_print'];

					if (copy($tmp_path, $file_path)) {
						$file_info = pathinfo($local_path);
						$extension = strtolower($file_info['extension']);

						if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
							list($width, $height) = getimagesize($this->config->item('upload_path') . $local_path);
							resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
						} else if (in_array($extension, ['pdf'])) {
							//do pdf conversion, each page to one image and save it in /res
							if (extension_loaded('imagick')) {
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path') . "/upload/");
							}
						}

						$this->common_model->insert_file_attachment($file_name, $local_path, 'profile', 0, $remark, $is_print, '', $return_reg_no, 0, $this->user['idx']);

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
				for ($i = 0; $i < $total; $i++) {
					//Get the temp file path
					$tmp_path = $_FILES['attach_attachment']['tmp_name'][$i];

					//Make sure we have a file path
					if ($tmp_path != "") {
						$prefix = date('YmdHis');
						$file_name = $_FILES['attach_attachment']['name'][$i];
						$prefix_n_file_name = $fileCtg[$i] . $prefix . "_" . $file_name;
						$file_path = $this->config->item('upload_path') . "/upload/" . $prefix_n_file_name;
						$local_path = "/upload/" . $prefix_n_file_name;
						$remark = $fileCtg[$i] . $attach_attachment_remark[$i];
						$is_print = (isset($attach_attachment_print[$i]) ? $attach_attachment_print[$i] : 0);

						if (move_uploaded_file($tmp_path, $file_path)) {
							$file_info = pathinfo($local_path);
							$extension = strtolower($file_info['extension']);

							if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
								list($width, $height) = getimagesize($this->config->item('upload_path') . $local_path);
								resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
							} else if (in_array($extension, ['pdf'])) {
								//do pdf conversion, each page to one image and save it in /res
								if (extension_loaded('imagick')) {
									_create_preview_images($prefix_n_file_name, $this->config->item('upload_path') . "/upload/");
								}
							}

							$this->common_model->insert_file_attachment($fileCtg[$i] . $file_name, $local_path, 'profile', 0, $remark, $is_print, '', $return_reg_no, 0, $this->user['idx']);
						}
					}
				}
			}

			// notify admin when new registration created
			if ($this->input->post('id') == 0) {

				$this->load->library('notification_service');

				$notify_list = $this->get_notify_admin();

				$reg_type = $this->get_reg_type_desc($post_data['type']);

				$package_name = [];
				$all_packages = $this->common_model->get_package_list($post_data['type'], 'a');

				$interested = json_decode($post_data['interested'], true);
				if (is_array($interested)) {
					foreach ($interested as $val) {
						foreach ($all_packages as $pack) {
							if ($val == $pack['package_no']) {
								$package_name[] = $pack['name'];
							}
						}
					}
				}

				$building_name = $this->building_model->get_building($post_data['building_no'])['name'];
				if (empty($building_name)) {
					$building_name = 'N/A';
				}

				$package = implode(",", $package_name) ?: "-";

				$contacts = $this->notification_service->buildContacts($notify_list['email_list'], $notify_list['whatsapp_list'], $notify_list['telegram_list']);

				$email_template = $this->email_model->get_email_template_detail("AND template_name = 'NEW REGISTRATION' AND is_default = 1");

				$meta_template_name = 'NEW REGISTRATION';

				$header = str_replace('%reg_no%', $return_reg_no, $email_template['email_title']);

				$replacements = [
					'%reg_no%'            => $return_reg_no,
					'%type%'              => $reg_type,
					'%name%'              => $post_data['name'],
					'%icno%'              => $post_data['icno'],
					'%phone%'             => $post_data['phone'],
					'%interested%'        => $package,
					'%contact_date_time%' => strtoupper($post_data['preferred_contact_date_time']),
					'%building%'          => $building_name,
					'%comp_name%'         => $post_data['type'] != 'r' ? $post_data['comp_name'] : 'N/A',
					'%agent%'             => 'N/A',
					'%url%'               => base_url('registration/add_registration/' . $return_id),
				];

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
						'reg_no' 			=> !empty($return_reg_no) ? $return_reg_no : '-',
						'reg_type' 			=> !empty($reg_type) ? $reg_type : '-',
						'comp_name' 		=> $post_data['type'] != 'r'
												? (!empty($post_data['comp_name']) ? $post_data['comp_name'] : 'N/A')
												: 'N/A',
						'building' 			=> !empty($building_name) ? $building_name : 'N/A',
						'name' 				=> !empty($post_data['name']) ? $post_data['name'] : 'N/A',
						'icno' 				=> !empty($post_data['icno']) ? $post_data['icno'] : 'N/A',
						'phone' 			=> !empty($post_data['phone']) ? $post_data['phone'] : 'N/A',
						'interested' 		=> !empty($package) ? $package : '-',
						'contact_date_time' => !empty($post_data['preferred_contact_date_time'])
												? strtoupper($post_data['preferred_contact_date_time'])
												: '-',
						'agent' 			=> 'N/A',
						'reg_url' 			=> !empty($return_id)
												? base_url('registration/add_registration/' . $return_id)
												: '-',
					],
					'',
					[
						'controller'    	=> 'registration',
						'doc_id'        	=> $return_id,
						'send_method'   	=> 'manual',
						'doc_type'      	=> '[New Registration]',
						'email_starter' 	=> 'New Registration [' . $return_reg_no . ']',
					]
				);
			}

			//if convert to so
			if ($post_data['task'] == 'btSO') {

				$config = $this->common_model->get_table('sys_config', '*', "`key` = 'default_payment_term'");

				//get reistration details
				$register_info = $this->registration_model->get_registration($post_data['id']);

				$this->load->model('salesorder_model');
				$so_post = $this->salesorder_model->get_so(0);

				//detail rows keep empty? as products need to be decided by backend user
				//$so_post['so_detail_rows'] = json_encode(array());

				//if ($register_info['type'] != 'r') {
				$so_post['so_head']['comp_name'] = $register_info['comp_name'];
				$so_post['so_head']['comp_num'] = $register_info['ssm'];
				$so_post['so_head']['comp_tin'] = $register_info['tin'];
				//} else {
				$so_post['so_head']['cust_name'] = $register_info['name'];
				//}

				$so_post['so_head']['date'] = date('Y-m-d');
				$so_post['so_head']['del_attn'] = $so_post['so_head']['comp_name'];
				$so_post['so_head']['del_company_name'] = $so_post['so_head']['comp_name'];
				$so_post['so_head']['del_unit_no'] = $register_info['ins_unit_no'];
				$so_post['so_head']['del_addr_1'] = $register_info['ins_addr_1'];
				$so_post['so_head']['del_addr_2'] = $register_info['ins_addr_2'];
				$so_post['so_head']['del_addr_3'] = $register_info['ins_addr_3'];
				$so_post['so_head']['del_postcode'] = $register_info['ins_postcode'];
				$so_post['so_head']['del_city'] = $register_info['ins_city'];
				$so_post['so_head']['del_tel'] = $register_info['phone'];
				$so_post['so_head']['del_state'] = $register_info['ins_state'];
				$so_post['so_head']['bill_attn'] = $so_post['so_head']['comp_name'];
				$so_post['so_head']['bill_unit_no'] = $register_info['bill_unit_no'];
				$so_post['so_head']['bill_addr_1'] = $register_info['bill_addr_1'];
				$so_post['so_head']['bill_addr_2'] = $register_info['bill_addr_2'];
				$so_post['so_head']['bill_addr_3'] = $register_info['bill_addr_3'];
				$so_post['so_head']['bill_postcode'] = $register_info['bill_postcode'];
				$so_post['so_head']['bill_city'] = $register_info['bill_city'];
				$so_post['so_head']['bill_tel'] = $register_info['phone'];
				$so_post['so_head']['bill_state'] = $register_info['bill_state'];
				$so_post['so_head']['bill_email'] = $register_info['email'];
				$so_post['so_head']['register_type'] = strtolower($register_info['type']);

				$so_post['so_head']['reg_no'] = $register_info['reg_no'];
				$so_post['so_head']['reg_type'] = strtolower($register_info['type']);
				$so_post['so_head']['icno'] = $register_info['icno'];

				$so_post['so_head']['payment_term'] = (isset($config[0]['val']) && $config[0]['val'] != '')
					? $config[0]['val']
					: 30;

				$so_post['so_head']['agent_id'] = $register_info['agent_id'];

				$so_post['so_head']['building_no'] = $register_info['building_no'];

				$datetime = date("Y-m-d H:i:s");
				$so_post['so_head']['created_date'] = $datetime;
				$so_post['so_head']['modified_date'] = $datetime;

				$so_post['so_head']['preferred_install_datetime'] = $register_info['preferred_install_datetime'];

				$so_post['user_idx'] = $this->user['idx'];
				$so_post['so_head']['preferred_login'] = $register_info['login_username'];
				$so_post['so_head']['preferred_password'] = $register_info['login_password'];


				$interested_list = json_decode($post_data['interested'], true);
				if (count($interested_list) > 0) {
					$package_list = array_column($this->common_model->get_package_list('', 'a') ?? [], 'name', 'package_no');
					$first_interest_product_id = $interested_list[0];

					$so_post['so_details']['prod_id'] = $first_interest_product_id;
					$so_post['so_details']['item_name'] = $package_list[$first_interest_product_id] ?? '';
				}

				$so_id = $this->salesorder_model->salesorder_insert($so_post);

				//update so id to existing attachment
				$this->common_model->update_so_id_to_attachment($so_id, $register_info['reg_no']);

				//update registration status
				$this->registration_model->update_registration_status_to_so($post_data['id']);

				// notify admin when new so created

				$building_name = $this->building_model->get_building($register_info['building_no'])['name'];
				if (empty($building_name)) {
					$building_name = 'N/A';
				}

				$notify_list = $this->get_notify_admin();

				$this->load->library('notification_service');

				$contacts = $this->notification_service->buildContacts($notify_list['email_list'], $notify_list['whatsapp_list'], $notify_list['telegram_list']);

				$reg_type = $this->get_reg_type_desc($register_info['type']);

				$email_template = $this->email_model->get_email_template_detail(
					"AND template_name = 'NEW SALES ORDER' AND is_default = 1"
				);

				$meta_template_name = 'NEW SALES ORDER';

				$replacements = [
					'%so_id%'     => $so_id,
					'%reg_type%'  => $reg_type,
					'%comp_name%' => $register_info['type'] != 'r'
										? $register_info['comp_name']
										: 'N/A',
					'%cust_name%' => $register_info['name'],
					'%building%'  => $building_name,
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

				if ($register_info['type'] == 'r') {
					$body = str_replace('Company Name: N/A', '', $body);
				}

				$this->notification_service->queue(
					$contacts,
					$header,
					$body,
					$meta_template_name,
					[
						'so_id' 		=> !empty($so_id) ? $so_id : '-',
						'reg_type' 		=> !empty($reg_type) ? $reg_type : '-',
						'comp_name' 	=> !empty($register_info['type']) && $register_info['type'] != 'r'
											? (!empty($register_info['comp_name']) ? $register_info['comp_name'] : 'N/A')
											: 'N/A',
						'name' 			=> !empty($register_info['name']) ? $register_info['name'] : 'N/A',
						'building' 		=> !empty($building_name) ? $building_name : 'N/A',
					],
					'',
					[
						'controller'    => 'registration',
						'doc_id'        => $post_data['id'],
						'send_method'   => 'manual',
						'email_starter' => 'New Sales Order [' . $so_id . ']',
						'doc_type'      => '[New Sales Order]',
					]
				);

				$this->session->set_flashdata("msg", 'Registration Saved and converted to Sales Order!');

				//redirect("salesorder");

				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'salesorder';
				echo json_encode($ajax_return);
				return false;

			} else if ($post_data['task'] == 'btSOProfile') {
				$register_info = $this->registration_model->get_registration($post_data['id']);
				return $this->convert_to_sales_order_and_profile($register_info, $ajax_return);
			}

			if (empty($_POST['url_after_save'])) {
				$this->session->set_flashdata("msg", 'Registration Saved!');
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

	private function get_reg_type_desc($type)
	{
		$reg_type = '';
		if ($type == 'r') {
			$reg_type = 'Residential';
		} else if ($type == 'b') {
			$reg_type = 'Commercial Broadband';
		} else if ($type == 'o') {
			$reg_type = 'Commercial Others';
		}
		return $reg_type;
	}

	private function get_notify_admin()
	{
		$admin_list = $this->common_model->get_admin_list();
		$email_list = [];
		$whatsapp_list = [];
		$telegram_list = [];
		foreach ($admin_list as $value) {
			$email_list[] = $value['email'];
			if ($value['allow_whatsapp'] == 1) {
				$whatsapp_list[] = $value['mobile_no'];
			}
			if ($value['allow_telegram'] == 1) {
				$telegram_list[] = $value['telegram_id'];
			}
		}
		return ['email_list' => $email_list, 'whatsapp_list' => $whatsapp_list, 'telegram_list' => $telegram_list];
	}

	function set_form_validation($mode = 'search', $reg_type = 'r')
	{
		$business_required = 'trim';
		if ($reg_type == 'b' || $reg_type == 'o') {
			$business_required = 'trim|required';
		}

		$config = array(
			'search' => array(
				array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
				array('field' => 'sel_status', 'label' => 'Status', 'rules' => ''),
				array('field' => 'sel_category', 'label' => 'Category', 'rules' => ''),
			),
			'save' => array(
				array('field' => 'id', 'label' => 'ID', 'rules' => 'trim'),
				array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|required'),
				array('field' => 'icno', 'label' => 'IC No.', 'rules' => 'trim|required'),
				array('field' => 'phone', 'label' => 'Phone No', 'rules' => 'trim|required'),
				array('field' => 'email', 'label' => 'Email', 'rules' => 'trim'),
				array('field' => 'type', 'label' => 'Type', 'rules' => 'trim'),

				array('field' => 'reg_no', 'label' => 'Reg No', 'rules' => 'trim'),
				array('field' => 'status', 'label' => 'Status', 'rules' => 'trim'),

				array('field' => 'remark', 'label' => 'Remark', 'rules' => 'trim'),

				array('field' => 'bill_unit_no', 'label' => 'Bill Address Unit No', 'rules' => 'trim'),
				array('field' => 'bill_addr_1', 'label' => 'Bill Address Line 1', 'rules' => 'trim|required'),
				array('field' => 'bill_addr_2', 'label' => 'Bill Address Line 2', 'rules' => 'trim'),
				array('field' => 'bill_addr_3', 'label' => 'Bill Address Line 3', 'rules' => 'trim'),
				array('field' => 'bill_postcode', 'label' => 'Bill Postcode', 'rules' => 'trim|required'),
				array('field' => 'bill_city', 'label' => 'Bill City', 'rules' => 'trim|required'),
				array('field' => 'bill_state', 'label' => 'Bill State', 'rules' => 'trim|required'),

				array('field' => 'ins_unit_no', 'label' => 'Installation Address Unit No', 'rules' => 'trim'),
				array('field' => 'ins_addr_1', 'label' => 'Installation Address Line 1', 'rules' => 'trim|required'),
				array('field' => 'ins_addr_2', 'label' => 'Installation Address Line 2', 'rules' => 'trim'),
				array('field' => 'ins_addr_3', 'label' => 'Installation Address Line 3', 'rules' => 'trim'),
				array('field' => 'ins_postcode', 'label' => 'Installation Postcode', 'rules' => 'trim|required'),
				array('field' => 'ins_city', 'label' => 'Installation City', 'rules' => 'trim|required'),
				array('field' => 'ins_state', 'label' => 'Installation State', 'rules' => 'trim|required'),

				array('field' => 'comp_name', 'label' => 'Company Name', 'rules' => $business_required),
				array('field' => 'ssm', 'label' => 'SSM#', 'rules' => $business_required),
				array('field' => 'tin', 'label' => 'Tin No.', 'rules' => 'trim'),

				array('field' => 'interested', 'label' => 'Interested Products', 'rules' => 'trim|required'),
				array('field' => 'agent_id', 'label' => 'Agent ID', 'rules' => 'trim'),

			),

		);

		if ($mode == 'save') {
			$config['save'][] = array('field' => 'day', 'label' => 'Preferred Day', 'rules' => 'trim|required');
			$config['save'][] = array('field' => 'from_time', 'label' => 'Preferred From Time', 'rules' => 'trim|required');
			$config['save'][] = array('field' => 'to_time', 'label' => 'Preferred To Time', 'rules' => 'trim|required');
			$config['save'][] = array('field' => 'specific_date', 'label' => 'Specific Date', 'rules' => 'trim');
			$config['save'][] = array('field' => 'time', 'label' => 'Time', 'rules' => 'trim|callback_check_time');
		} else if ($mode == 'update') {
			$mode = 'save';
		}

		$this->form_validation->set_rules($config[$mode]);
	}

	function check_time()
	{
		$fromTime = $this->input->post('from_time');
		$fromBlock = $this->input->post('from_block');
		$toTime = $this->input->post('to_time');
		$toBlock = $this->input->post('to_block');

		if ($fromBlock == 'pm' && $fromTime != 12) {
			$fromTime += 12;
		}

		if ($toBlock == 'pm' && $toTime != 12) {
			$toTime += 12;
		}

		if ($fromTime == 12 && $fromBlock == 'am') {
			$fromTime = 0;
		}
		if ($toTime == 12 && $toBlock == 'am') {
			$toTime = 0;
		}

		if ($fromTime > $toTime) {
			$this->form_validation->set_message('check_time', "Invalid Time");
			return false;
		}
		return true;
	}

	function load_package_list()
	{
		$return_val = array();
		$package_type = $this->input->post("package_type");
		$building = $this->input->post("building") ?? null;
		$this->load->model('package_model');
		$return_val = $this->package_model->get_package_listing_interested($package_type, $building);

		echo json_encode($return_val);
	}

	private function gen_post_attachments($file_arr = array())
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
			for ($i = 0; $i < $total; $i++) {
				//Get the temp file path
				$tmp_path = $file_arr['tmp_name'][$i];

				//Make sure we have a file path
				if ($tmp_path != "") {
					$prefix = date('YmdHis');
					$file_name = $file_arr['name'][$i];
					;
					$prefix_n_file_name = $fileCtg[$i] . $prefix . "_" . $file_name;
					$file_path = $this->config->item('upload_path') . "/temp/" . $prefix_n_file_name;
					$local_path = "/temp/" . $prefix_n_file_name;
					$remark = $fileCtg[$i] . $attach_attachment_remark[$i];
					$is_print = (isset($attach_attachment_print[$i]) ? $attach_attachment_print[$i] : 0);

					if (move_uploaded_file($tmp_path, $file_path)) {
						$file_info = pathinfo($local_path);
						$extension = strtolower($file_info['extension']);

						if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
							list($width, $height) = getimagesize($this->config->item('upload_path') . $local_path);
							resize_image_tmp($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));
						} else if (in_array($extension, ['pdf'])) {
							//do pdf conversion, each page to one image and save it in /res
							if (extension_loaded('imagick')) {
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path') . "/temp/");
							}
						}

						$this->common_model->insert_tmp_file_attachment($fileCtg[$i] . $file_name, $local_path, 'profile', 0, $remark, $is_print, '', $temp_id, 0, $this->user['idx']);
					}

				}
			}
		}

	}

	public function convert_to_sales_order_and_profile($reg_info, $ajax_return)
	{

		$this->db->trans_begin();

		$this->load->model('salesorder_model');
		$this->load->model('customer_model');
		$this->load->model('package_model');
		$this->load->model('bill_model');
		$this->load->model('profile_model');

		$additional_msg = '';

		if (empty($reg_info['login_username'])) {
			$ajax_return['err_msg'] = 'Username is required for conversion.';
			$ajax_return['error_keys'] = array('login_username');
			echo json_encode($ajax_return);
			return false;
		}

		$exist = 0;
		$radius_login_username = $reg_info['login_username'];

		if (!empty($_SESSION['config']['radius_suffix'])) {
			if (strstr($reg_info['login_username'], $_SESSION['config']['radius_suffix']) === false) {
				$radius_login_username = $reg_info['login_username'] . $_SESSION['config']['radius_suffix'];
			}
		}

		$local = $this->customer_model->filtered_customer("", " AND c.login_username = '" . $this->db->escape_str($radius_login_username) . "' ");
		$exist = empty($local) ? 0 : 1;

		$exist2 = 0;
		$result2 = $this->customer_model->radius_account_exist($radius_login_username);
		$exist2 = $result2['exist'];

		if ($exist != 0) {
			$ajax_return['err_msg'] = 'Account username already exists.';
			$ajax_return['error_keys'] = array('login_username');
			echo json_encode($ajax_return);
			$this->db->trans_rollback();
			return false;
		}

		if ($exist2 != 0) {
			$ajax_return['err_msg'] = 'Router username already exists.';
			$ajax_return['error_keys'] = array('login_username');
			$this->db->trans_rollback();
			echo json_encode($ajax_return);
			return false;
		}

		$radius_login_password = $reg_info['login_password'];

		if (empty($radius_login_password)) {
			$radius_login_password = generateRandomString(8);
		}

		$config = $this->common_model->get_table('sys_config', '*', "`key` = 'default_payment_term'");
		$so_post = $this->salesorder_model->get_so(0);
		$so_post['so_head']['comp_name'] = $reg_info['comp_name'];
		$so_post['so_head']['comp_num'] = $reg_info['ssm'];
		$so_post['so_head']['comp_tin'] = $reg_info['tin'];
		$so_post['so_head']['cust_name'] = $reg_info['name'];

		$so_post['so_head']['date'] = date('Y-m-d');
		$so_post['so_head']['del_attn'] = $so_post['so_head']['comp_name'];
		$so_post['so_head']['del_company_name'] = $so_post['so_head']['comp_name'];
		$so_post['so_head']['del_unit_no'] = $reg_info['ins_unit_no'];
		$so_post['so_head']['del_addr_1'] = $reg_info['ins_addr_1'];
		$so_post['so_head']['del_addr_2'] = $reg_info['ins_addr_2'];
		$so_post['so_head']['del_addr_3'] = $reg_info['ins_addr_3'];
		$so_post['so_head']['del_postcode'] = $reg_info['ins_postcode'];
		$so_post['so_head']['del_city'] = $reg_info['ins_city'];
		$so_post['so_head']['del_tel'] = $reg_info['phone'];
		$so_post['so_head']['del_state'] = $reg_info['ins_state'];
		$so_post['so_head']['bill_attn'] = $so_post['so_head']['comp_name'];
		$so_post['so_head']['bill_unit_no'] = $reg_info['bill_unit_no'];
		$so_post['so_head']['bill_addr_1'] = $reg_info['bill_addr_1'];
		$so_post['so_head']['bill_addr_2'] = $reg_info['bill_addr_2'];
		$so_post['so_head']['bill_addr_3'] = $reg_info['bill_addr_3'];
		$so_post['so_head']['bill_postcode'] = $reg_info['bill_postcode'];
		$so_post['so_head']['bill_city'] = $reg_info['bill_city'];
		$so_post['so_head']['bill_tel'] = $reg_info['phone'];
		$so_post['so_head']['bill_state'] = $reg_info['bill_state'];
		$so_post['so_head']['bill_email'] = $reg_info['email'];
		$so_post['so_head']['register_type'] = strtolower($reg_info['type']);

		$so_post['so_head']['reg_no'] = $reg_info['reg_no'];
		$so_post['so_head']['reg_type'] = strtolower($reg_info['type']);
		$so_post['so_head']['icno'] = $reg_info['icno'];

		$so_post['so_head']['payment_term'] = (isset($config[0]['val']) && $config[0]['val'] != '')
			? $config[0]['val']
			: 30;

		$so_post['so_head']['agent_id'] = $reg_info['agent_id'];
		$so_post['so_head']['building_no'] = $reg_info['building_no'];

		$so_post['so_head']['preferred_install_datetime'] = $reg_info['preferred_install_datetime'];

		$datetime = date("Y-m-d H:i:s");
		$so_post['so_head']['created_date'] = $datetime;
		$so_post['so_head']['modified_date'] = $datetime;

		$so_post['user_idx'] = $this->user['idx'];
		$so_post['so_head']['preferred_login'] = $reg_info['login_username'];
		$so_post['so_head']['preferred_password'] = $radius_login_password;

		$interested_list = json_decode($reg_info['interested'], true);

		if (!empty($interested_list) && is_array($interested_list)) {
			$package_list = array_column($this->common_model->get_package_list('', 'a') ?? [], 'name', 'package_no');
			$first_interest_product_id = $interested_list[0];

			$so_post['so_details']['prod_id'] = $first_interest_product_id;
			$so_post['so_details']['item_name'] = $package_list[$first_interest_product_id] ?? '';
		}

		$so_id = $this->salesorder_model->salesorder_insert($so_post);

		$this->common_model->update_so_id_to_attachment($so_id, $reg_info['reg_no']);
		$this->registration_model->update_registration_status_to_so($reg_info['id']);

		// PROFILE
		$so_data = $this->salesorder_model->get_so($so_id);

		$new_profile = 0;

		if ($so_data['so_head']['reg_type'] == 'r') {

			$chk = $this->profile_model->check_profile_by_ic_and_type($so_data['so_head']['icno'], 'r', 0);

			if (!empty($chk)) {
				$acc_id = $chk;
			} else {
				$chk2 = $this->profile_model->check_profile_by_username($so_data['so_head']['bill_email']);
				if (!empty($chk2)) {
					$ajax_return['err_msg'] = 'Email already exist in profile.';
					$ajax_return['error_keys'] = array('preferred_login');
					$this->db->trans_rollback();
					echo json_encode($ajax_return);
					return false;
				} else {
					$new_profile = 1;
				}
			}

		} else if ($so_data['so_head']['reg_type'] == 'b' || $so_data['so_head']['reg_type'] == 'o') {

			$chk = $this->profile_model->check_profile_by_ssm($so_data['so_head']['comp_num']);

			if (!empty($chk)) {
				$acc_id = $chk;
			} else {
				$chk2 = $this->profile_model->check_profile_by_username($so_data['so_head']['bill_email']);
				if (!empty($chk2)) {
					$ajax_return['err_msg'] = 'Username already chosen.';
					$ajax_return['error_keys'] = array('preferred_login');
					$this->db->trans_rollback();
					echo json_encode($ajax_return);

					return false;
				} else {
					$new_profile = 1;
				}
			}

		}

		if ($new_profile == 1) {

			$profile_post = $this->profile_model->get_profile(0);

			$profile_post['acc_id'] = 0;
			$profile_post['acc_name'] = $so_data['so_head']['cust_name'];
			$profile_post['acc_type'] = strtolower($so_data['so_head']['reg_type']);
			$profile_post['grp_id'] = '';
			$profile_post['icno'] = $so_data['so_head']['icno'];
			$profile_post['acc_mobileno'] = $so_data['so_head']['bill_tel'];
			$profile_post['acc_email'] = $so_data['so_head']['bill_email'];
			$profile_post['comp_name'] = $so_data['so_head']['comp_name'];
			$profile_post['ssm'] = $so_data['so_head']['comp_num'];
			$profile_post['tin'] = $so_data['so_head']['reg_type'] == 'r' ? $so_data['so_head']['tin'] : $so_data['so_head']['comp_tin'];
			$profile_post['acc_lang'] = 'EN';
			$profile_post['acc_config'] = '';
			$profile_post['bill_unit_no'] = $so_data['so_head']['bill_unit_no'];
			$profile_post['bill_addr_1'] = $so_data['so_head']['bill_addr_1'];
			$profile_post['bill_addr_2'] = $so_data['so_head']['bill_addr_2'];
			$profile_post['bill_addr_3'] = $so_data['so_head']['bill_addr_3'];
			$profile_post['bill_postcode'] = $so_data['so_head']['bill_postcode'];
			$profile_post['bill_city'] = $so_data['so_head']['bill_city'];
			$profile_post['bill_state'] = $so_data['so_head']['bill_state'];

			$profile_post['reg_no'] = $so_data['so_head']['reg_no'];
			$profile_post['so_id'] = $so_data['so_head']['so_id'];

			$profile_post['acc_username'] = $so_data['so_head']['bill_email'];
			$profile_post['acc_password'] = $so_data['so_head']['preferred_password'];

			//fill first PIC
			$profile_post['pic_id'] = array();
			$profile_post['pic_name'] = array();
			$profile_post['pic_designation'] = array();
			$profile_post['pic_mobile'] = array();
			$profile_post['pic_email_1'] = array();
			$profile_post['pic_email_2'] = array();
			$profile_post['pic_nric_passport'] = array();
			$profile_post['pic_dob'] = array();
			$profile_post['pic_gender'] = array();
			$profile_post['pic_race'] = array();

			$profile_post['pic_id'][0] = 0;
			$profile_post['pic_name'][0] = $so_data['so_head']['cust_name'];
			$profile_post['pic_designation'][0] = '';
			$profile_post['pic_mobile'][0] = $so_data['so_head']['bill_tel'];
			$profile_post['pic_email_1'][0] = $so_data['so_head']['bill_email'];
			$profile_post['pic_email_2'][0] = '';
			$profile_post['pic_nric_passport'][0] = $so_data['so_head']['icno'];
			$profile_post['pic_dob'][0] = NULL;
			$profile_post['pic_gender'][0] = '';
			$profile_post['pic_race'][0] = '';

			$profile_post['agent_id'] = $so_data['so_head']['agent_id'];

			$profile_post['user_idx'] = $this->user['idx'];

			$acc_id = $this->profile_model->profile_insert($profile_post);

			$additional_msg = 'and new profile created.';

			// SEND
			$this->load->library('notification_service');

			$email_template = $this->email_model->get_email_template_detail(
				"AND template_name = 'PROFILE CREATED' AND is_default = 1"
			);

			$meta_template_name = 'PROFILE CREATED';

			$isp_name = $_SESSION['config']['isp_name'];
			$frontend_url = $this->config->item('frontend_url') ?? '';

			$replacements = [
				'%frontend_url%' => $frontend_url,
				'%company_name%' => $isp_name,
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

			$contacts = $this->notification_service->buildContacts( [$profile_post['acc_email']] );

			$this->notification_service->queue(
				$contacts,
				$header,
				$body,
				$meta_template_name,
				[],
				'',
				[
					'acc_id'         => $acc_id,
					'user_id'        => $this->user['idx'],
					'controller'     => 'salesorder',
					'doc_id'         => $so_data['so_head']['so_id'],
					'send_method'    => 'manual',
					'email_starter'  => $header,
					'doc_type'       => '[Profile Login Credential]',
				]
			);
		} else {
			$additional_msg = 'and profile already exist, new account may created.';
		}

		$this->common_model->update_acc_id_to_attachment($acc_id, $so_data['so_head']['so_id']);

		if (!empty($so_data['item_list']['prod_id']) && !empty($acc_id)) {
			$account_data = $this->customer_model->get_customer('');
			$package_data = $this->package_model->get_package($so_data['item_list']['prod_id']);

			$config = $this->common_model->get_table('sys_config', '*', "`key` = 'default_payment_term'");

			if (!empty($package_data)) {
				$name = $so_data['so_head']['cust_name'];

				if (strtoupper($package_data['category']) != 'R') {
					$name = $so_data['so_head']['comp_name'];
				}

				$account_data['customer_no'] = '0';
				$account_data['name'] = $name;
				$account_data['currency_code'] = 'MYR';
				$account_data['reg_no'] = $so_data['so_head']['comp_num'];
				$account_data['gst_no'] = $so_data['so_head']['gst_num'];
				$account_data['pic_name'] = '';
				$account_data['pic_designation'] = '';
				$account_data['status'] = 'r';
				$account_data['category'] = $package_data['category'];
				$account_data['product_category'] = $package_data['product_category'];
				$account_data['building'] = $so_data['so_head']['building_no'];
				$account_data['package'] = $package_data['package_no'];
				$account_data['package_name'] = $package_data['name'];
				$account_data['monthly_charge'] = $package_data['monthly_charge'];
				$account_data['yearly_charge'] = $package_data['yearly_charge'];

				$account_data['bill_cycle_month'] = '1';

				$account_data['login_username'] = $radius_login_username;
				$account_data['login_password'] = $radius_login_password;
				$account_data['framed_ip_address'] = '';
				$account_data['framed_route'] = '';
				$account_data['package_month'] = $package_data['package_month'];
				$account_data['stop_service_after'] = $package_data['stop_service_after'];
				$account_data['router_id'] = $package_data['router_id'];
				$account_data['contract_month'] = $package_data['package_month'];

				$account_data['inst_unit_no'] = $so_data['so_head']['del_unit_no'];
				$account_data['inst_addr1'] = $so_data['so_head']['del_addr_1'];
				$account_data['inst_addr2'] = $so_data['so_head']['del_addr_2'];
				$account_data['inst_addr3'] = $so_data['so_head']['del_addr_3'];
				$account_data['inst_city'] = $so_data['so_head']['del_city'];
				$account_data['inst_postcode'] = $so_data['so_head']['del_postcode'];
				$account_data['inst_state'] = $so_data['so_head']['del_state'];
				$account_data['inst_phone'] = $so_data['so_head']['del_tel'];
				$account_data['inst_email'] = $so_data['so_head']['bill_email'];

				$account_data['bill_name'] = $so_data['so_head']['bill_attn'];
				$account_data['bill_addr1'] = $so_data['so_head']['bill_addr_1'];
				$account_data['bill_addr2'] = $so_data['so_head']['bill_addr_2'];
				$account_data['bill_city'] = $so_data['so_head']['bill_city'];
				$account_data['bill_postcode'] = $so_data['so_head']['bill_postcode'];
				$account_data['bill_state'] = $so_data['so_head']['bill_state'];

				$account_data['bill_by_post'] = '';
				$account_data['bill_by_email'] = '1';

				$account_data['tel_num'] = $so_data['so_head']['del_tel'];
				$account_data['fax_num'] = $so_data['so_head']['del_fax'];
				$account_data['mobile_num'] = $so_data['so_head']['del_tel'];
				$account_data['email_1'] = $so_data['so_head']['bill_email'];
				$account_data['email_2'] = '';
				$account_data['nric'] = $so_data['so_head']['icno'];
				$account_data['passport'] = '';
				$account_data['date_of_birth'] = '';
				$account_data['gender'] = 'm';
				$account_data['race'] = 'm';
				$account_data['marital_status'] = 's';
				$account_data['household'] = '';

				$account_data['ownership_type'] = '';
				$account_data['no_of_employee'] = '';
				$account_data['no_of_branches'] = '';

				$account_data['signup_date'] = date('Y-m-d');
				$account_data['activated_date'] = '';
				$account_data['suspended_date'] = '';
				$account_data['terminated_date'] = '';

				$account_data['next_bill_date'] = '';
				$account_data['dealer'] = $so_data['so_head']['agent_id'];
				$account_data['remark'] = '';
				$account_data['payment_term'] = empty($so_data['so_head']['payment_term'])
					? ((isset($config[0]['val']) && $config[0]['val'] != '')
						? $config[0]['val']
						: 30)
					: $so_data['so_head']['payment_term'];
				$account_data['serial_num'] = '';
				$account_data['installer_name'] = '';
				$account_data['nationality'] = '';

				$account_data['profile_id'] = $acc_id;
				$account_data['acc_name'] = $name;

				$account_data['user_idx'] = $this->user['idx'];
				$account_data['transact_date'] = date('Y-m-d');
				$account_data['current_status'] = 'P';

				$account_data['bill_waive_period'] = $package_data['bill_waive_period'];
				$account_data['delay_trial_start'] = $package_data['delay_trial_start'];
				$account_data['free_package_upgrade'] = $package_data['free_package_upgrade'];
				$account_data['upgrade_package_id'] = $package_data['upgrade_package_id'];

				$account_data['preferred_install_datetime'] = $reg_info['preferred_install_datetime'];

				$customer_return = $this->customer_model->customer_insert($account_data, $package_data['package_no'], $this->user['username'], '');
				if ($customer_return == false || empty($customer_return['customer_no'])) {
					$ajax_return['err_msg'] = 'Failed to insert account. please try again later.';
					$this->db->trans_rollback();
					echo json_encode($ajax_return);
					return false;
				}
				$customer_no = (empty($customer_return['customer_no'])) ? '' : $customer_return['customer_no'];

				$this->customer_model->create_activation_fee($customer_no, $this->user['username'], $package_data['package_no']);
				$this->customer_model->cisco_account_upsert($radius_login_username, $radius_login_password);
			}
		}

		$this->salesorder_model->update_so_customer_info($so_data['so_head']['so_id'], $acc_id, $customer_no);

		//update status to approved/profile
		$this->salesorder_model->update_status_to_approved($so_data['so_head']['so_id']);

		if ($this->db->trans_status() === true) {
			$this->db->trans_commit();
		} else {
			$this->db->trans_rollback();
			$db_error = $this->db->error();
			$ajax_return['err_msg'] = $db_error;
			$ajax_return['error_keys'] = array();
			echo json_encode($ajax_return);

			return false;
		}

		$this->session->set_flashdata("msg", 'Sales Order created ' . $additional_msg);

		$ajax_return['status'] = 'SUCC';
		$ajax_return['url'] = 'profile';
		echo json_encode($ajax_return);
		return true;
	}


}
