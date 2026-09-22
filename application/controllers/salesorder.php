<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
//~ include_once( APPPATH . 'controllers/package.php' );

class Salesorder extends DataPage_Controller {

	private $e_key = "1nf0n4l";

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		check_acl('salesorder');
		$this->load->model('salesorder_model');
		$this->load->model('common_model');
		$this->load->model('docs_log_model');
		$this->load->model('email_model');

		$this->load->helper('image_helper');
		$this->load->helper('puppeteer_helper');

		$this->so_status = array('1' => 'Pending', '2' => 'Approved/Profile', '3' => 'Cancelled');
    }

    public function index()
    {
    	$row_html = $this->salesorder_rows(1);

   		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();

			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['sel_status'] = $post_data['sel_status'];
			$data['sel_installation'] = $post_data['sel_installation'];
		} else {
			$salesorder_filter = get_session_filter('salesorder_filter');
			$data['page_item_no'] = $salesorder_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $salesorder_filter['txt_search'] ?? '';
			$data['sel_status'] = $salesorder_filter['sel_status'] ?? '1';
			$data['sel_installation'] = $salesorder_filter['sel_installation'] ?? '1';
		}

		$data['page_title'] 		= 'Sales Order';
		$data['form_action'] 		= base_url('salesorder');
		$status_arr = $this->so_status;
		$data['sel_status_list'] = $status_arr;

		$data['msg'] 				= $this->msg;

		//access to view or modify
		$access_view   = check_acl('salesorder', 'V', false ) == true ? 1 : 0 ;
		$access_modify = check_acl('salesorder', 'M', false ) == true ? 1 : 0 ;
		$data['view_only'] = 0 ; 
		if( $access_view == 1 && $access_modify == 0 ){
			$data['view_only'] = 1 ;
		}

		$data['row_html'] = $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('salesorder/index',$data);
		$this->load->view('templates/footer');

    }

    public function salesorder_rows($returnOnly = 0)
    {

   		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$salesorder_filter = get_session_filter('salesorder_filter');
			$post_data['page_item_no'] = $salesorder_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $salesorder_filter['txt_search'] ?? '';
			$post_data['sel_status'] = $salesorder_filter['sel_status'] ?? '1';
			$post_data['sel_installation'] = $salesorder_filter['sel_installation'] ?? 'all';
		}

    	$total_row = 0;
		$page_item_no = ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		$txt_search = $post_data['txt_search'];
		$sel_status = $post_data['sel_status'];
		$sel_installation = $post_data['sel_installation'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_status' => $sel_status,
			'sel_installation' => $sel_installation,
		);
		set_session_filter('salesorder_filter', $session_array);

		$query_where = array();
		//where query params should be in an array pass to model
		$query_where['txt_search'] = $txt_search;
		$query_where['sel_status'] = $sel_status;
		$query_where['sel_installation'] = $sel_installation;

		$result = $this->salesorder_model->get_sales_order_listing($page_item_no,$query_where);

		//parse before view
		$status_arr = $this->so_status;
		foreach ($result['row'] as $key => $row) {
			$result['row'][$key]['status_text'] = (isset($status_arr[$row['status']]) ? $status_arr[$row['status']] : '');

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
		}

		$data['row_data'] 			= $result['row'];
    	$data['pagination'] 		= paginationSettingsAjax('', $result['total_row'], $page_item_no, $_SESSION['config']['max_page_item']);

		//access to view or modify
		$access_view   = check_acl('salesorder', 'V', false ) == true ? 1 : 0 ;
		$access_modify = check_acl('salesorder', 'M', false ) == true ? 1 : 0 ;
		$data['view_only'] = 0 ; 
		if( $access_view == 1 && $access_modify == 0 ){
			$data['view_only'] = 1 ;
		}

		$html = $this->parser->parse('salesorder/salesorder_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

    }

    function add_so($so_id=0)
    {
    	$chk_temp_id = $this->input->post('so_head')['temp_id'] ?? '';
    	$so_data = $this->salesorder_model->get_so($so_id);

		if (!empty($chk_temp_id)) {
			$so_data['so_head']['temp_id'] = $chk_temp_id;
		}

		$data['input']				= $so_data;
		$data['page_title'] 		= 'Sales Order';
		$data['form_action'] 		= base_url('salesorder/save_salesorder');
		$hidden['url_after_save'] 	= '';
		$data['hidden'] 			= $hidden;

		$data['sel_state_list'] 	= $this->common_model->get_state_list();
		$data['sel_dealer_list']	= format_dealer_list_hierarchy($this->common_model->get_dealer_list() ?? []);

		$data['msg']				= $this->msg;
		$data['not_same_addr'] 		= 0 ;

		$product_arr = array();
		$this->load->model('package_model');
		$package_list = $this->package_model->get_package_listing_all();
		//package should show all the affiliated prices
        foreach ($package_list as $pkg_no => $pkg_row) {
            $product_arr[] = [
            	'value'=>$pkg_row['name'],
            	'label'=>$pkg_row['name'], 
            	'id'=>$pkg_no, 
            	'name'=>$pkg_row['name'], 
            	'category' => $pkg_row['category'], 
            	'dia_vars' => $pkg_row['dia_vars'], 
            	'deposit' => $pkg_row['deposit'],
            	'monthly_charge' => $pkg_row['monthly_charge'],
            	'installation' => $pkg_row['installation'],
            	'one_time_charge' => $pkg_row['one_time_charge'],
            	'tax_amount' => number_format(($pkg_row['monthly_charge']*$pkg_row['tax_percent'])/100, 2, '.', ','),
            ];
            $product_arr[] = [
            	'value'=>$pkg_row['name'],
            	'label'=>$pkg_no, 
            	'id'=>$pkg_no, 
            	'name'=>$pkg_row['name'], 
            	'category' => $pkg_row['category'], 
            	'dia_vars' => $pkg_row['dia_vars'],
            	'deposit' => $pkg_row['deposit'],
            	'monthly_charge' => $pkg_row['monthly_charge'],
            	'installation' => $pkg_row['installation'],
            	'one_time_charge' => $pkg_row['one_time_charge'],
            	'tax_amount' => number_format(($pkg_row['monthly_charge']*$pkg_row['tax_percent'])/100, 2, '.', ','),
            ];
        }

        $data['product_arr']		= $product_arr;

		//attachments
		$file_result	= ($so_id != 0) ? $this->common_model->get_file_attachment('profile', 0, '', 0, $data['input']['so_head']['reg_no'], $data['input']['so_head']['so_id']) : [];

		//post back attachments
		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('profile', 0, '', 0, $data['input']['so_head']['reg_no'], $chk_temp_id);
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

		foreach ($file_result as $file) {
			if(strpos($file['local_path'],$this->config->item('upload_url').'/upload') === 0) {
				if(strpos($file['local_path'],$this->config->item('upload_url').'/upload/IC_') === 0) {
					$data['ic_attachment'][] = $file;
				} else if (strpos($file['local_path'],$this->config->item('upload_url').'/upload/SSM_') === 0) {
					$data['ssm_attachment'][] = $file;
				} else if (strpos($file['local_path'],$this->config->item('upload_url').'/upload/AUTH_') === 0) {
					$data['auth_attachment'][] = $file;
				} else {
					$data['others_attachment'][] = $file;
				}
			} else {
				$data['attachment'][] = $file;
			}
		}

		$this->load->model('building_model');
		$data['buildings'] = $this->common_model->get_building_list('building_no', '', 'AND status = "a"');

		$data['account_status'] = array_column($this->common_model->get_customer_status_list(), 'name', 'status_code');

		$data['preferred_install_date'] = '';
		$data['preferred_install_time'] = '';
		if (!empty($so_data['so_head']['preferred_install_datetime'])) {
			$datetime = new \DateTime($so_data['so_head']['preferred_install_datetime']);
			$data['preferred_install_date'] = $datetime->format('Y/m/d');
			$data['preferred_install_time'] = $datetime->format('H:i');
		}
		
		if(!empty($so_data['so_head']['so_id'])) {
			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');
			$data['created_by'] = $user_list[$so_data['so_head']['created_by']] ?? '-';
			$data['created_at'] = $so_data['so_head']['created_date'] ?? '-';
			$data['updated_by'] = $user_list[$so_data['so_head']['modified_by']] ?? '-';
			$data['updated_at'] = $so_data['so_head']['modified_date'] ?? '-';	
		}
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('salesorder/salesorder_detail',$data);
		$this->load->view('templates/footer');
    }

    function save_salesorder()
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
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'registration', 'error_keys' => array());

			$post_data = $this->input->post();
			//_debug_array($post_data); exit;

			$this->set_form_validation('save', $post_data['so_head']['reg_type']);
			if($this->form_validation->run() == false) 
			{
				$this->msg['error_msg'] = validation_errors();
				//if ($this->input->post('customer_no')=='')  

				//$this->gen_post_details($post_back['so_details']);

				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				$this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));

				$this->add_so($post_data['so_head']['so_id']);				
				//else  $this->edit_customer($this->input->post('customer_no'));				
			}
			else 
			{

				$post_data['user_idx'] = $this->user['idx'];

				if (empty($post_data['so_head']['preferred_password'])) {
						$post_data['so_head']['preferred_password'] = generateRandomString(8);
				}

				$post_data['so_head']['preferred_install_datetime'] = '';
				if (!empty($post_data['so_head']['preferred_install_date']) && !empty($post_data['so_head']['preferred_install_time'])) {
					$post_data['so_head']['preferred_install_datetime'] = str_replace('/', '-', $post_data['so_head']['preferred_install_date']) . ' ' . $post_data['so_head']['preferred_install_time'] . ':00';
				}

				$return_id = $this->salesorder_model->salesorder_insert($post_data);

				//file success, move any temp attachments to main
				$chk_temp_id = $this->input->post('so_head')['temp_id'];
				if (!empty($chk_temp_id)) {
					$leftover_attach = $this->common_model->get_temp_file_attachment('profile', 0, '', 0, $post_data['so_head']['reg_no'], $chk_temp_id);
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
							
							$this->common_model->insert_file_attachment($file_name , $local_path, 'profile', 0, $remark, $is_print, '', $post_data['so_head']['reg_no'], $return_id, $this->user['idx']);

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
					for( $i=0 ; $i < $total ; $i++ ) {
					  	//Get the temp file path
					  	$tmp_path = $_FILES['attach_attachment']['tmp_name'][$i];

					  	//Make sure we have a file path
					  	if ($tmp_path != "") {
							$prefix 		= date('YmdHis');
							$file_name 		= $_FILES['attach_attachment']['name'][$i];;
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
								
								$this->common_model->insert_file_attachment($fileCtg[$i] .$file_name , $local_path, 'profile', 0, $remark, $is_print, '', $post_data['so_head']['reg_no'], $return_id, $this->user['idx']);
							}
				    	}
					}
				}

				//if convert to profile
				if ($post_data['task'] == 'btProfile') {
					$additional_msg = '';
					//should maybe use db transaction/rollback
					$this->db->trans_begin();

					$this->load->model('customer_model');
					$this->load->model('package_model');
					$this->load->model('bill_model');

					$so_data = $this->salesorder_model->get_so($post_data['so_head']['so_id']);
					$profile_for_so = $so_data['so_head']['profile_id'];
					$customer_for_so = $so_data['so_head']['customer_no'];

					//before start, check if radius and preferred username taken first

					if (empty($so_data['so_head']['preferred_login'])) {
						//$this->session->set_flashdata("msg", 'Preferred login must be filled in.');
						//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

						$ajax_return['err_msg'] = 'Preferred login must be filled in.';
						$ajax_return['error_keys'] = array('preferred_login');
						echo json_encode($ajax_return);

						return false;
					}

					$radius_login_username = $so_data['so_head']['preferred_login'];
					$radius_login_password = $so_data['so_head']['preferred_password'];

					if (!empty($_SESSION['config']['radius_suffix'])) {
						if( strstr( $so_data['so_head']['preferred_login'], $_SESSION['config']['radius_suffix'] ) === false ){
							$radius_login_username = $so_data['so_head']['preferred_login'] . $_SESSION['config']['radius_suffix'];
						}
					}

					if (empty($customer_for_so)) {
						$exist = 0;

						$local = $this->customer_model->filtered_customer( "", " AND c.login_username = '".$radius_login_username."' ");
						$exist = empty( $local ) ? 0 : 1 ;

						$exist2 = 0;
						$result2 = $this->customer_model->radius_account_exist( $radius_login_username ) ;
						$exist2 = $result2['exist'];

						if( $exist != 0  ){
							//$this->session->set_flashdata("msg", 'Preferred login name already exists.');
							//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

							$ajax_return['err_msg'] = 'Preferred login name already exists.';
							$ajax_return['error_keys'] = array('email');
							echo json_encode($ajax_return);

							return false;
						}

						if( $exist2 != 0  ){
							//$this->session->set_flashdata("msg", 'Router login ID already exists.');
							//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

							$ajax_return['err_msg'] = 'Router login ID already exists.';
							$ajax_return['error_keys'] = array('preferred_login');
							echo json_encode($ajax_return);

							return false;
						}

					}

					//check if account creation exists, if exists , tie to that specific account, but if not fulfill right conditions, then reject
					//Residential - check if IC exists, if IC exists , use back same account, if IC no exists, check if username taken, if taken reject
					//Commercial and other - check if SSM or TIN exists, use back same account, if SSM or TIN no exists, check if username taken, if taken reject

					//_debug_array($so_data); exit;

					$this->load->model('profile_model');

					//check if account creation exists, if exists , tie to that specific account, but if not fulfill right conditions, then reject
					//Residential - check if IC exists, if IC exists , use back same account, if IC no exists, check if username taken, if taken reject
					//Commercial and other - check if SSM or TIN exists, use back same account, if SSM or TIN no exists, check if username taken, if taken reject
					$new_profile = 0;

					if ($so_data['so_head']['reg_type'] == 'r') {

						$chk = $this->profile_model->check_profile_by_ic_and_type($so_data['so_head']['icno'], 'r', 0);

						if (!empty($chk)) {
							$acc_id = $chk;
						} else {
							//not found, check if username exists
							$chk2 = $this->profile_model->check_profile_by_username($so_data['so_head']['bill_email']);
							if (!empty($chk2)) {
								//$this->session->set_flashdata("msg", 'Username already chosen.');
								//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

								$ajax_return['err_msg'] = 'Email already chosen by other profile as a login username.';
								$ajax_return['error_keys'] = array('bill_email');
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
							//not found, check if username exists
							$chk2 = $this->profile_model->check_profile_by_username($so_data['so_head']['preferred_login']);
							if (!empty($chk2)) {
								//$this->session->set_flashdata("msg", 'Username already chosen.');
								//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

								$ajax_return['err_msg'] = 'Username already chosen.';
								$ajax_return['error_keys'] = array('preferred_login');
								echo json_encode($ajax_return);

								return false;
							} else {
								$new_profile = 1;
							}
						}

					} else {
						//$this->session->set_flashdata("msg", 'Unknown Reg Type.');
						//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

						$ajax_return['err_msg'] = 'Unknown Reg Type.';
						$ajax_return['error_keys'] = array('reg_type');
						echo json_encode($ajax_return);

						return false;
					}

					//how to decide if profile already created? 

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
						
						$profile_post['acc_id'] = empty($profile_for_so) ? '' : $profile_for_so;
						
						$acc_id = $this->profile_model->profile_insert($profile_post);

						if (empty($profile_for_so)) {

							$additional_msg = 'and new profile created.';

							$this->sendProfileCreatedEmail($acc_id, $so_data['so_head']['so_id'], $profile_post['acc_email']);
						}
						
					} else {
						//straight away get acc_id
						$additional_msg = 'and profile already exist, new account may created.';
					}

					//update so id to existing attachment
					$this->common_model->update_acc_id_to_attachment($acc_id, $so_data['so_head']['so_id']);

					//so details need to be converted to account? how?
					if (!empty($so_data['item_list']['prod_id']) && !empty($acc_id)) {

						$prev_cust = $customer_for_so != '' ? $this->customer_model->get_customer($customer_for_so) : [];
						$prev_cust_status = isset($prev_cust['current_status']) ? $prev_cust['current_status'] : false;

						if($prev_cust_status != 'A'){
							//become account
							$account_data = $this->customer_model->get_customer('');
							$package_data = $this->package_model->get_package($so_data['item_list']['prod_id']);

							$config = $this->common_model->get_table( 'sys_config', '*', "`key` = 'default_payment_term'" );

							$name = $so_data['so_head']['cust_name'];

							if (strtoupper($package_data['category']) != 'R') {
								$name = $so_data['so_head']['comp_name'];
							}

							//Asign blank data for add new customer
							$account_data['customer_no']='0';
							$account_data['name']=$name;
							$account_data['currency_code']='MYR';
							$account_data['reg_no']=$so_data['so_head']['comp_num'];
							$account_data['gst_no']=$so_data['so_head']['gst_num'];
							$account_data['pic_name']='';
							$account_data['pic_designation']='';
							$account_data['status']='r';
							$account_data['category']=$package_data['category'];
							$account_data['product_category']=$package_data['product_category'];
							//if got tied to building
							$account_data['building']=$so_data['so_head']['building_no'];
							$account_data['package']=$package_data['package_no'];
							$account_data['package_name']=$package_data['name'];
							$account_data['monthly_charge']=$package_data['monthly_charge'];
							$account_data['yearly_charge']=$package_data['yearly_charge'];

							$account_data['bill_cycle_month'] = '1';
							
							//radius login, might need to check availability
							$account_data['login_username']=$radius_login_username;
							//PPPoE password
							$account_data['login_password']= $radius_login_password;
							$account_data['framed_ip_address'] = '';
							$account_data['framed_route'] = '';
							$account_data['package_month']=$package_data['package_month'];
							$account_data['stop_service_after']=$package_data['stop_service_after'];
							$account_data['router_id']=$package_data['router_id'];
							//maybe need to set in package
							//default follow package month
							$account_data['contract_month']=$package_data['package_month'];
							
							//address should only need installation, billing shud be profile level
							$account_data['inst_unit_no']= $so_data['so_head']['del_unit_no'];
							$account_data['inst_addr1']= $so_data['so_head']['del_addr_1'];
							$account_data['inst_addr2']= $so_data['so_head']['del_addr_2'];
							$account_data['inst_addr3']= $so_data['so_head']['del_addr_3'];
							$account_data['inst_city']= $so_data['so_head']['del_city'];
							$account_data['inst_postcode']= $so_data['so_head']['del_postcode'];
							// $account_data['inst_state']= $this->common_model->get_state_code_by_einvoice_code($so_data['so_head']['del_state']);
							$account_data['inst_state']= $so_data['so_head']['del_state'];
							$account_data['inst_phone']= $so_data['so_head']['del_tel'];
							$account_data['inst_email']= $so_data['so_head']['bill_email'];

							$account_data['bill_name']= $so_data['so_head']['bill_attn'];
							$account_data['bill_addr1']= $so_data['so_head']['bill_addr_1'];
							$account_data['bill_addr2']= $so_data['so_head']['bill_addr_2'];
							$account_data['bill_city']= $so_data['so_head']['bill_city'];
							$account_data['bill_postcode']= $so_data['so_head']['bill_postcode'];
							// $account_data['bill_state']= $this->common_model->get_state_code_by_einvoice_code($so_data['so_head']['bill_state']);
							$account_data['bill_state']= $so_data['so_head']['bill_state'];
							
							$account_data['bill_by_post']='';
							$account_data['bill_by_email']='1';
							
							//pic info already moved to profile
							$account_data['tel_num']= $so_data['so_head']['del_tel'];
							$account_data['fax_num']= $so_data['so_head']['del_fax'];
							$account_data['mobile_num']= $so_data['so_head']['del_tel'];
							$account_data['email_1']= $so_data['so_head']['bill_email'];
							$account_data['email_2']= '';
							$account_data['nric']= $so_data['so_head']['icno'];
							$account_data['passport']= '';
							$account_data['date_of_birth']= '';
							$account_data['gender'] = 'm';
							$account_data['race'] = 'm';
							$account_data['marital_status'] = 's';
							$account_data['household'] = '';
							
							$account_data['ownership_type'] = '';
							$account_data['no_of_employee'] = '';
							$account_data['no_of_branches'] = '';
							
							//dates need to be revamped
							//date and status need to turn into a flow/cycle, probably call a model, then insert a record into a sub table, keep track of the status , who changed it and the date, other parts read status read from latest customer status record
							$account_data['signup_date'] = date('Y-m-d');
							$account_data['activated_date'] = '';
							$account_data['suspended_date'] = '';
							$account_data['terminated_date'] = '';

							$account_data['next_bill_date'] = '';
							$account_data['dealer'] = $so_data['so_head']['agent_id'];
							$account_data['remark'] = '';
							$account_data['payment_term'] = empty($so_data['so_head']['payment_term']) 
															? ((isset( $config[0]['val']) && $config[0]['val'] != '') 
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

							$account_data['bill_waive_period']=$package_data['bill_waive_period'];
							$account_data['delay_trial_start']=$package_data['delay_trial_start'];
							$account_data['free_package_upgrade'] = $package_data['free_package_upgrade'];
							$account_data['upgrade_package_id'] = $package_data['upgrade_package_id'];

							$account_data['preferred_install_datetime'] = $so_data['so_head']['preferred_install_datetime'];

							//customer insert implies that the customer/account record is new, so will have to by default create status record if need
							if (empty($customer_for_so)){
								$customer_return = $this->customer_model->customer_insert($account_data, $package_data['package_no'], $this->user['username'],'');
								$customer_no = (empty($customer_return['customer_no']))?'':$customer_return['customer_no'];
							} else {
								$customer_return = $this->customer_model->customer_update($account_data, $package_data['package_no'], $this->user['username'], $customer_for_so);
								$customer_no = $customer_for_so;
							}

							//create activation fee
					
							//this is installation fee, will add upon create customer account
							$this->customer_model->create_activation_fee($customer_no,$this->user['username'], $package_data['package_no']);

							//create appropriate radius records
							//$login_username = $post_data['login_username'];
							//$status 		= $post_data['status'] == 'r' ? 'accept' : 'reject' ; 
							$radius_status = 'accept';
							//$password 		= $post_data['login_password'];

							/*$this->customer_model->radius_account_status( $radius_login_username, $radius_status );
							$this->customer_model->radius_account_password( $radius_login_username, $radius_login_password );
							
							$this->customer_model->radius_usergroup_upsert( $radius_login_username, $package_data['bandwidth'] );*/

							//val - boss say dont auto create if not yet activated
							//$this->customer_model->cisco_account_upsert( $radius_login_username, $radius_login_password, $prev_cust['login_username'] ?? '' );
						} else {
							$ajax_return['err_msg'] = 'Profile recreation is not available for ACTIVATED accounts.';
							echo json_encode($ajax_return);

							return false;
						}
					}

					$this->salesorder_model->update_so_customer_info($so_data['so_head']['so_id'], $acc_id, $customer_no);

					//update status to approved/profile
					$this->salesorder_model->update_status_to_approved($so_data['so_head']['so_id']);

					//save copy to audit
					$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'audit_copy' ");
					$audit_copy = $config_record[0]['val'];
					if ($audit_copy == '1') {

						if (!empty($customer_no)) {

							$so_id = $so_data['so_head']['so_id'];
							$audit_file_path = $this->config->item('upload_path').'/temp/pdf/so_'.$so_id.'.pdf';
							$audit_file_name = '['.$customer_no.']_so_'.$so_id.'.pdf';

							$this->load->helper('puppeteer_helper');
							$scode = md5($so_id . $this->e_key);
							puppeteer_print_preview(
								$this->config->item('base_url').'pdfapi/print_quotation/'.$so_id.'/'.$scode, 
								$this->config->item('upload_path').'/temp/pdf/so_'.$so_id.'.pdf', 
								$this->config->item('proj_path'), 
								$this->config->item('chrome_loc'));

							//save to audit folder
							$this->common_model->perform_audit(
								$audit_file_path, 
								'customer_so', 
								$audit_file_name, 
								$customer_no, 
								'Customer No:'.$customer_no.' SO Document', 
								'', 
								'', 
								$customer_no
							);
						} else {
							log_message('error', 'Unable to create SO in audit folder. Customer No Missing or some other issue.');
						}
					}

					//db commit or rollback
					if ($this->db->trans_status() === true)
					{
						$this->db->trans_commit();
					}else{
						$this->db->trans_rollback();
						$db_error = $this->db->error();
						//$this->session->set_flashdata("msg", 'DB error ('.$db_error.').');
						//redirect("salesorder/add_so/".$post_data['so_head']['so_id']);

						$ajax_return['err_msg'] = $db_error;
						$ajax_return['error_keys'] = array();
						echo json_encode($ajax_return);

						return false;
					}

					$this->session->set_flashdata("msg", 'Sales Order Saved '.$additional_msg);
					//redirect("profile");

					$ajax_return['status'] = 'SUCC';
					$ajax_return['url'] = 'profile';
					echo json_encode($ajax_return);
					return false;

				}

				if(empty($_POST['url_after_save']))
				{
					$this->session->set_flashdata("msg", 'Sales Order Saved!');
					//redirect("salesorder");

					$ajax_return['status'] = 'SUCC';
					$ajax_return['url'] = 'salesorder';
					echo json_encode($ajax_return);
					return false;

				}
				else
				{
					//redirect($_POST['url_after_save']);

					$ajax_return['status'] = 'SUCC';
					$ajax_return['url'] = $_POST['url_after_save'];
					echo json_encode($ajax_return);
					return false;

				}
			}

		//}
    }

	/*function print_salesorder($so_id) {
    	$salesorder = $this->salesorder_model->get_print_so($so_id);

		$this->load->helper('form');
		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print bill statements';

		if(empty($salesorder)) {
			$this->session->set_flashdata("msg", 'Sales Order not found.');
			redirect("salesorder");
			return false;
		}

		$content_data		= $salesorder;

		$content_data['doc_title']	= "Sales Order";

		$content_data['issue_by']	= $this->user['display_name'];

		$state_list = $this->common_model->get_state_list();
		$state_map = array_column($state_list, 'name', 'state_code');

		$content_data['bill_actual_state']	= $state_map[$content_data['bill_state']];
		$content_data['del_actual_state']	= $state_map[$content_data['del_state']];

		$title_dia_vars = json_decode($content_data['so_details']['package']['dia_vars'] ?? "", true);
		$title_dia_vars = is_array($title_dia_vars) ? $title_dia_vars : [];
		$value_dia_vars = json_decode($content_data['dia_vars']);

		if (is_array($title_dia_vars)) {
			$content_data['dia_vars'] = [];
		
			foreach ($title_dia_vars as $i => $key) {
				$value = isset($value_dia_vars[$i]) ? $value_dia_vars[$i] : '';
				$content_data['dia_vars'][$key] = $value;
			}
		}

		$address_keys = [
			'company_addr_1',
			'company_addr_2',
			'company_addr_3',
			'company_postal',
			'company_city',
			'company_state'
		];
		
		$company_address_parts = [];
		
		foreach ($address_keys as $key) {
			$result = $this->common_model->get_table('sys_config', '*', "`key` = '$key'");
			$val = $result[0]['val'] ?? null;
			if (!empty($val)) {
				$company_address_parts[] = is_array($val) ? implode(' ', $val) : $val;
			}
		}
		
		$content_data['company_address'] = implode(', ', $company_address_parts);

		$content_data['company_tel'] = $this->common_model->get_table('sys_config','*', "`key` = 'company_phone' ")[0]['val'] ?? null;

		$html = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		$html .= $this->load->view('templates/print_menu', '', true);
		$html .= $this->parser->parse('salesorder/salesorder_print', $content_data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;
	}*/

	function print_quotation($so_id)
	{
		if ( empty($so_id) )  {
			$this->session->set_flashdata("warning_msg", 'Sales Order not found!');
			redirect('salesorder');
		}

		$contact_list = $this->input->post('contact_list');

		$so = $this->salesorder_model->get_so($so_id);

		/*foreach($invoice as $i_key => $i_val)
		{
			$invoice[$i_key]['bill_no'] 			= add_zero($invoice[$i_key]['bill_no']);
			$invoice[$i_key]['bill_date'] 			= date_toggle($invoice[$i_key]['bill_date'],$_SESSION['config']['date_format_printing']);
			$invoice[$i_key]['display_name'] 		= (empty($invoice[$i_key]['bill_name']))?$invoice[$i_key]['customer_name']:$invoice[$i_key]['bill_name'];
		}*/

		$header_data 				= $this->vars;
		$header_data['title'] 		= 'print preview | quotation';
		$header_data['description']	= 'to print quotation';
		//_debug_array($so); exit;
		$content_data['data']		= $so;

		$content_data['quotation_no'] = $so['so_head']['so_num'];
		
		$gst_reg_no	 =  $this->common_model->get_gst_reg_no();
		$content_data['gst_reg_no']	= $gst_reg_no;

        $gst_amount	= $this->common_model->get_default_tax_amount();
		$content_data['default_tax'] = empty($gst_amount) ? '0.0' : $gst_amount[0]['percent'];
		
		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['bills_footer'] = $footer[0]['val'];
		
		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
		$content_data['company_tax_number'] = $tax[0]['val'];
		
		$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
		$content_data['jompay_biller_code'] = $jompay[0]['val'];

		$data['msg'] 				= $this->msg;

		$content_data['einvoice_qr'] = '';
		$content_data['display_name'] = $so['so_head']['cust_name'];
		$content_data['doc_title']	= "Quotation";

		$state_list = $this->common_model->get_state_list();
		$state_map = array_column($state_list, 'name', 'state_code');

		$content_data['bill_actual_state']	= $state_map[$so['so_head']['bill_state']] ?? 'Pulau Pinang';
		$content_data['del_actual_state']	= $state_map[$so['so_head']['del_state']] ?? 'Pulau Pinang';

		$address_keys = [
			'company_addr_1',
			'company_addr_2',
			'company_addr_3',
			'company_postal',
			'company_city',
			'company_state'
		];
		
		$company_address_parts = [];
		
		foreach ($address_keys as $key) {
			$result = $this->common_model->get_table('sys_config', '*', "`key` = '$key'");
			$val = $result[0]['val'] ?? null;
			if (!empty($val)) {
				$company_address_parts[] = is_array($val) ? implode(' ', $val) : $val;
			}
		}
		
		$content_data['company_address'] = implode(', ', $company_address_parts);

		$content_data['company_tel'] = $this->common_model->get_table('sys_config','*', "`key` = 'company_phone' ")[0]['val'] ?? null;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$scode = md5($so_id . $this->e_key);
			puppeteer_print_preview(
				$this->config->item('base_url').'pdfapi/print_quotation/'.$so_id.'/'.$scode, 
				$this->config->item('upload_path').'/temp/pdf/so_'.$so_id.'.pdf', 
				$this->config->item('proj_path'), 
				$this->config->item('chrome_loc'));

			$meta_template = $this->whatsapp_template->build(
				'ITELCO DOCS',
				['doc_type'	=> 'Quotation - '.$so['so_head']['so_num']]
			);

			//send pdf
			$email_list = $json_contact_list[0];
			$whatsapp_list = $json_contact_list[1];
			$telegram_list = $json_contact_list[2];

			$send_array = array();
			$send_array['send_type'] = 'custom';
			$send_array['acc_id'] = 0;
			$send_array['customer_no'] = 0;
			$send_array['user_id'] = 0;
			$send_array['controller'] = 'salesorder';
			$send_array['doc_id'] = $so_id;
			$send_array['send_method'] = 'manual';
			$send_array['acc_name'] = 'Itelco User';
			$send_array['attachment'] = $this->config->item('upload_path').'/temp/pdf/so_'.$so_id.'.pdf';
			$send_array['subject'] = 'Quotation - '.$so['so_head']['so_num'];
			$send_array['body'] = 'Attached herewith is the quotation - ' . $so['so_head']['so_num'];
			$send_array['email_starter'] = 'Quotation - '.$so['so_head']['so_num'];
			$send_array['doc_type'] = '[Quotation Document]';
			$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
			$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];
			$send_array['email_list'] = $email_list;
			$send_array['whatsapp_list'] = $whatsapp_list;
			$send_array['telegram_list'] = $telegram_list;

			$send_result = $this->docs_log_model->do_send($send_array);

			//$job_order_send = 1;
			//@unlink($this->config->item('upload_path').'/temp/pdf/customer_support_form_'.$cs_no.'.pdf');
		}

		$menu_data = array();
		$menu_data['send_btn'] = 1;
		$menu_data['form_action'] = base_url('salesorder/print_quotation/'.$so_id);
		
		$html = '';
		$html .= $this->load->view('templates/print_header', $header_data, true);
		$html .= $this->load->view('templates/print_menu', $menu_data, true);
		$html .= $this->parser->parse('salesorder/print', $content_data, true);
		$html .= $this->load->view('templates/print_footer', '', true);

		echo $html;

	}

	function set_form_validation($mode = 'search', $reg_type = 'r')
	{
		$business_required = 'trim';
		$residential_required = 'trim';
		if ($reg_type == 'b' || $reg_type == 'o') {
			$business_required = 'trim|required';
		}else{
			$residential_required = 'trim|required';
		}
		
		$config = array(				
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
						//array('field' => 'sel_status', 'label' => 'Status', 'rules' => ''),
						//array('field' => 'sel_category', 'label' => 'Category', 'rules' => ''),
					),
					'save' => array (
						array('field' => 'so_head[cust_name]', 'label' => 'Customer Name', 'rules' => 'trim|required'),
						array('field' => 'so_head[date]', 'label' => 'Date', 'rules' => 'trim|required'),
						array('field' => 'so_head[reg_type]', 'label' => 'Customer Type', 'rules' => 'trim|required'),
						//array('field' => 'phone', 'label' => 'Phone No', 'rules' => 'trim|required'),
						//array('field' => 'email', 'label' => 'Email', 'rules' => 'trim'),

						array('field' => 'so_head[comp_name]', 'label' => 'Company Name', 'rules' => $business_required),
						array('field' => 'so_head[comp_num]', 'label' => 'SSM', 'rules' => $business_required),
						array('field' => 'so_head[tin]', 'label' => 'TIN No', 'rules' => 'trim'),

						array('field' => 'so_head[so_id]', 'label' => 'SO ID', 'rules' => 'trim'),
						array('field' => 'so_head[cust_id]', 'label' => 'Customer ID', 'rules' => 'trim'),
						array('field' => 'so_head[doc_rev_num]', 'label' => 'Doc Rev Num', 'rules' => 'trim'),
						array('field' => 'so_head[revised]', 'label' => 'Revised', 'rules' => 'trim'),
						array('field' => 'so_head[currency_code]', 'label' => 'Currency Code', 'rules' => 'trim'),
						array('field' => 'so_head[currency_rate]', 'label' => 'Currency Rate', 'rules' => 'trim'),
						array('field' => 'so_head[po_num]', 'label' => 'PO Num', 'rules' => 'trim'),
						array('field' => 'so_head[shipping_fee]', 'label' => 'Shipping Fee', 'rules' => 'trim'),
						array('field' => 'so_head[status]', 'label' => 'Status', 'rules' => 'trim'),
						array('field' => 'so_head[grand_total]', 'label' => 'Grand Total', 'rules' => 'trim'),
						array('field' => 'so_head[round_val]', 'label' => 'Round Val', 'rules' => 'trim'),
						array('field' => 'so_head[allow_rounding]', 'label' => 'Allow Rounding', 'rules' => 'trim'),
						array('field' => 'so_head[status_approved]', 'label' => 'Status Approved', 'rules' => 'trim'),
						array('field' => 'so_head[status_lock]', 'label' => 'Status Lock', 'rules' => 'trim'),
						array('field' => 'so_head[status_void]', 'label' => 'Status Void', 'rules' => 'trim'),
						array('field' => 'so_head[gst_app]', 'label' => 'GST App', 'rules' => 'trim'),
						array('field' => 'so_head[grand_tax]', 'label' => 'Grand Tax', 'rules' => 'trim'),
						array('field' => 'so_head[grand_subtotal]', 'label' => 'Grand Subtotal', 'rules' => 'trim'),
						array('field' => 'so_head[base_amount]', 'label' => 'Base Amount', 'rules' => 'trim'),
						array('field' => 'so_head[quotation_id]', 'label' => 'Quotation ID', 'rules' => 'trim'),
						array('field' => 'so_head[dept_id]', 'label' => 'Dept ID', 'rules' => 'trim'),
						array('field' => 'so_head[bill_fax]', 'label' => 'Billing Fax', 'rules' => 'trim'),
						array('field' => 'so_head[del_fax]', 'label' => 'Delivery Fax', 'rules' => 'trim'),

						array('field' => 'so_head[so_num]', 'label' => 'SO Num', 'rules' => 'trim'),
						array('field' => 'so_head[gst_num]', 'label' => 'GST Num', 'rules' => 'trim'),
						array('field' => 'so_head[payment_term]', 'label' => 'Payment Term', 'rules' => $business_required.'|numeric'),
						array('field' => 'so_head[notes]', 'label' => 'Notes', 'rules' => 'trim'),

						array('field' => 'so_head[bill_attn]', 'label' => 'Billing Attention', 'rules' => 'trim'),
						array('field' => 'so_head[bill_unit_no]', 'label' => 'Billing Unit No.', 'rules' => 'trim'),
						array('field' => 'so_head[bill_addr_1]', 'label' => 'Billing Address Line 1', 'rules' => 'trim'),
						array('field' => 'so_head[bill_addr_2]', 'label' => 'Billing Address Line 2', 'rules' => 'trim'),
						array('field' => 'so_head[bill_addr_3]', 'label' => 'Billing Address Line 3', 'rules' => 'trim'),
						array('field' => 'so_head[bill_postcode]', 'label' => 'Billing Postcode', 'rules' => 'trim'),
						array('field' => 'so_head[bill_city]', 'label' => 'Billing City', 'rules' => 'trim'),
						array('field' => 'so_head[bill_state]', 'label' => 'Billing State', 'rules' => 'trim'),
						array('field' => 'so_head[bill_tel]', 'label' => 'Billing Tel.', 'rules' => 'trim'),
						array('field' => 'so_head[bill_email]', 'label' => 'Billing Email', 'rules' => 'trim|required'),

						array('field' => 'so_head[del_attn]', 'label' => 'Delivery Attention', 'rules' => 'trim'),
						array('field' => 'so_head[del_unit_no]', 'label' => 'Delivery Unit No.', 'rules' => 'trim'),
						array('field' => 'so_head[del_company_name]', 'label' => 'Delivery Company Name', 'rules' => 'trim'),
						array('field' => 'so_head[del_addr_1]', 'label' => 'Delivery Address Line 1', 'rules' => 'trim'),
						array('field' => 'so_head[del_addr_2]', 'label' => 'Delivery Address Line 2', 'rules' => 'trim'),
						array('field' => 'so_head[del_addr_3]', 'label' => 'Delivery Address Line 3', 'rules' => 'trim'),
						array('field' => 'so_head[del_postcode]', 'label' => 'Delivery Postcode', 'rules' => 'trim'),
						array('field' => 'so_head[del_city]', 'label' => 'Delivery City', 'rules' => 'trim'),
						array('field' => 'so_head[del_state]', 'label' => 'Delivery State', 'rules' => 'trim'),
						array('field' => 'so_head[del_tel]', 'label' => 'Billing Tel.', 'rules' => 'trim'),

						array('field' => 'so_head[temp_id]', 'label' => 'Temp ID', 'rules' => 'trim'),
						array('field' => 'so_head[reg_no]', 'label' => 'Reg No', 'rules' => 'trim'),
						array('field' => 'so_head[reg_type]', 'label' => 'Reg Type', 'rules' => 'trim'),
						array('field' => 'so_head[icno]', 'label' => 'IC No.', 'rules' => $residential_required),

						array('field' => 'so_head[status]', 'label' => 'Status', 'rules' => 'trim'),

						array('field' => 'so_head[preferred_login]', 'label' => 'Username', 'rules' => 'trim'),
						array('field' => 'so_head[preferred_password]', 'label' => 'Preferred Password', 'rules' => 'trim'),
						array('field' => 'so_head[agent_id]', 'label' => 'Agent ID', 'rules' => 'trim'),

						array('field' => 'so_head[dia_vars]', 'label' => 'DIA Vars', 'rules' => 'trim'),
						array('field' => 'so_head[building_no]', 'label' => 'Building No.', 'rules' => 'trim'),

						array('field' => 'so_detail_rows', 'label' => 'SO Detail Rows', 'rules' => 'trim'),

						array('field' => 'so_details[so_detail_id]', 'label' => 'SO Detail ID', 'rules' => 'trim'),
						array('field' => 'so_details[prod_id]', 'label' => 'SO Detail Product ID', 'rules' => 'trim'),
						array('field' => 'so_details[dia_vars]', 'label' => 'SO Detail DIA Vars', 'rules' => 'trim'),
						array('field' => 'so_details[item_name]', 'label' => 'SO Detail Item Name', 'rules' => 'trim'),
						array('field' => 'so_details[monthly_charge]', 'label' => 'SO Detail Monthly Charge', 'rules' => 'trim'),
						array('field' => 'so_details[installation]', 'label' => 'SO Detail Installation Fee', 'rules' => 'trim'),
						array('field' => 'so_details[one_time_charge]', 'label' => 'SO Detail One Time Charge', 'rules' => 'trim'),
						array('field' => 'so_details[deposit]', 'label' => 'SO Detail Deposit', 'rules' => 'trim'),
					),
		);
		
		$this->form_validation->set_rules($config[$mode]);
	}

	function ajax_verify_username(){
		
		$return_val = array( 'exist' => 0 );
		$login_username	= $this->input->post('login_username');
		$radius_login_username = $login_username;
		
		if (!empty($_SESSION['config']['radius_suffix'])) {
			if( strstr( $login_username, $_SESSION['config']['radius_suffix'] ) === false ){
				$radius_login_username = $login_username . $_SESSION['config']['radius_suffix'];
			}
		}
		
		$result1['exist'] = 0;
		$local = $this->customer_model->filtered_customer( "", " AND c.login_username = '".$login_username."' ");
		$result1['exist'] = empty( $local ) ? 0 : 1 ;
			
		$result2['exist'] = 0;
		$result2 = $this->customer_model->radius_account_exist( $radius_login_username ) ;
		
		if( $result1['exist'] == 0 && $result2['exist'] == 0 )
			$return_val['exist'] = 0 ;
		else
			$return_val['exist'] = 1 ;

		//$return_val['sql'] = $result2['sql'];
		
		echo json_encode( $return_val );
		
	}

	//generate item detail during postback
	/*private function gen_post_details($arr = '')
	{
		if(!empty($arr) && is_array($arr))
		{
			$post_back['so_details'] = $arr;
			foreach($post_back['so_details'] as $post_back_k => $post_back_arr)
			{
				$data['class'] 			= 'post_back_detail';
				$data['type'] 			= 'hidden';
				$data['data-num'] 		= $post_back['so_details'][$post_back_k]['num'];
				$data['data-line_num'] 	= $post_back['so_details'][$post_back_k]['line_num'];
				$data['data-desc'] 		= $post_back['so_details'][$post_back_k]['desc'];
				$data['data-quantity'] 	= $post_back['so_details'][$post_back_k]['quantity'];
				$data['data-price'] 	= $post_back['so_details'][$post_back_k]['price'];
				$data['data-subtotal'] 	= $post_back['so_details'][$post_back_k]['subtotal'];
				$data['data-tax_type'] 	= $post_back['so_details'][$post_back_k]['tax_type'];
				$data['data-tax'] 		= $post_back['so_details'][$post_back_k]['tax'];
				$data['data-total'] 	= $post_back['so_details'][$post_back_k]['total'];
				$data['data-uom'] 		= (empty($post_back['so_details'][$post_back_k]['uom']))?'':$post_back['so_details'][$post_back_k]['uom'];
				echo form_input($data);
			}
		}
	}*/

	private function gen_post_attachments($file_arr=array())
	{
		$this->load->model('common_model');

		if (!empty($file_arr)) {	

			$post_back_attach = array();

			$total = count($file_arr['name']);
			$attach_attachment_remark = $this->input->post('attach_attachment_remark');
			$fileCtg = $this->input->post('fileCtg');
			$attach_attachment_print = array();

			$temp_id = $this->input->post('so_head')['temp_id'];
			$reg_no = $this->input->post('so_head')['reg_no'];

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
						
						$this->common_model->insert_tmp_file_attachment($fileCtg[$i] . $file_name , $local_path, 'profile', 0, $remark, $is_print, '', $reg_no, $temp_id, $this->user['idx']);
					}

		    	}
			}
		}

	}

	function ajax_check_icno(){

		$this->load->model('profile_model');

		$icno = $this->input->post('icno');
		$so_id = $this->input->post('so_id');
		$result = $this->profile_model->check_profile_by_ic_from_so($icno, 'r', $so_id);
		
		echo json_encode(['exist' => $result != [], 'data' => $result]); 

		exit;
	}

	function ajax_check_ssm(){

		$this->load->model('profile_model');

		$ssm = $this->input->post('ssm');
		$so_id = $this->input->post('so_id');
		$result = $this->profile_model->check_profile_by_ssm_from_so($ssm, 'b', $so_id);
		
		echo json_encode(['exist' => $result != [], 'data' => $result]); 
		exit;
	}

	function ajax_check_email(){
		$this->load->model('profile_model');

		$email = $this->input->post('email');
		$so_id = $this->input->post('so_id');
		$result = $this->profile_model->check_profile_by_email_from_so($email, $so_id);
		
		//if acc_id is not 0, means this is an edit
		//if($so_id != 0){
			//echo json_encode(['exist' => $result != $acc_id && $result != 0]); 
		//}else{
			echo json_encode(['exist' => $result != 0]); 
		//}
		exit;
	}

	function ajax_verify_radius_account(){
		$this->load->model('customer_model');
		
		$return_val = array( 'exist' => 0 );
		$login_username	= $this->input->post('login_username');
		$customer_no	= $this->input->post('customer_no');
		$radius_login_username = $login_username;
		
		if (!empty($_SESSION['config']['radius_suffix'])) {
			if( strstr( $login_username, $_SESSION['config']['radius_suffix'] ) === false ){
				$radius_login_username = $login_username . $_SESSION['config']['radius_suffix'];
			}
		}

		$self_check = '';
		if (!empty($customer_no)) {
			$customer = $this->customer_model->get_customer($customer_no);
			$self_check = $customer['login_username'];

			if ($radius_login_username == $self_check) {
				$return_val['exist'] = 2;
				echo json_encode( $return_val );
				return false;
			}
		}
		
		$result1['exist'] = 0;
		$local = $this->customer_model->filtered_customer( "", " AND c.login_username = '".$radius_login_username."' ");
		if (!empty($customer_no)) {
			foreach($local as $res_key => $res1) {
				if ($res1['customer_no'] == $customer_no) {
					unset($local[$res_key]);
				}
			}
		}
		$result1['exist'] = empty( $local ) ? 0 : 1 ;
			
		$result2['exist'] = 0;
		$result2 = $this->customer_model->radius_account_exist( $radius_login_username, $self_check ) ;
		
		if( $result1['exist'] == 0 && $result2['exist'] == 0 )
			$return_val['exist'] = 0 ;
		else
			$return_val['exist'] = 1 ;

		//$return_val['sql'] = $result2['sql'];
		
		echo json_encode( $return_val );
		
	}

	/**
	 * Send profile created notification to customer.
	 *
	 * @param int          $acc_id
	 * @param int          $so_id
	 * @param string|array $email
	 * @return bool
	 */
	private function sendProfileCreatedEmail($acc_id, $so_id, $email)
	{
		if (empty($email)) {
			return false;
		}

		$email = array_filter((array) $email);

		if (empty($email)) {
			return false;
		}

		$this->load->library('notification_service');
		$this->load->model('email_model');

		$email_template = $this->email_model->get_email_template_detail(
			"AND template_name = 'PROFILE CREATED' AND is_default = 1"
		);

		if (empty($email_template)) {
			return false;
		}

		$replacements = [
			'%frontend_url%' => $this->config->item('frontend_url') ?? '',
			'%company_name%' => $_SESSION['config']['isp_name'] ?? '',
		];

		$header = $email_template['email_title'];

		$body = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$email_template['email_msg']
		);

		$contacts = $this->notification_service->buildContacts($email);

		return $this->notification_service->queue(
			$contacts,
			$header,
			$body,
			'PROFILE CREATED',
			[],
			'',
			[
				'acc_id'         => $acc_id,
				'user_id'        => $this->user['idx'],
				'controller'     => 'salesorder',
				'doc_id'         => $so_id,
				'send_method'    => 'manual',
				'email_starter'  => $header,
				'doc_type'       => '[Profile Created]',
			]
		);
	}
}