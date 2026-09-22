<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Payment extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
        $this->load->helper('url');
		$this->load->library(array('form_validation','session','upload'));

		check_acl('payment');
		$this->load->model('payment_model');
		$this->load->model('common_model');
		$this->load->helper('image_helper');
		$this->load->model('docs_log_model');
    }

    function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
						array('field' => 'txt_pay_date_start', 'label' => 'Start Date', 'rules' => 'trim'),
						array('field' => 'txt_pay_date_end', 'label' => 'End Date', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'payment_no', 'label' => 'Payment No', 'rules' => 'trim'),
						array('field' => 'txt_search', 'label' => '', 'rules' => 'trim'),
						array('field' => 'customer_no', 'label' => 'Customer', 'rules' => 'trim|required'),
						array('field' => 'customer_name', 'label' => 'Customer', 'rules' => 'trim|required'),
						array('field' => 'ref_no', 'label' => 'Reference Bill', 'rules' => 'trim'),
						array('field' => 'tranx_date', 'label' => 'Post Date', 'rules' => 'trim'),
						array('field' => 'pay_date', 'label' => 'Pay Date', 'rules' => 'trim|required'),
						array('field' => 'payment_source', 'label' => 'Payment Source', 'rules' => 'trim'),
						array('field' => 'bill_type', 'label' => 'Bill Type', 'rules' => 'trim'),
						array('field' => 'cheque_no', 'label' => 'Cheque', 'rules' => 'trim'),
						array('field' => 'amount', 'label' => 'Amount', 'rules' => 'trim|required|numeric'),
						array('field' => 'remark', 'label' => 'Remark', 'rules' => 'trim'),
						array('field' => 'payment_source', 'label' => 'Payment Source', 'rules' => 'trim|required|greater_than[0]')
					),
				);

		$this->form_validation->set_rules($config[$mode]);
	}

	function add_payment()
	{
		$chk_temp_id = $this->input->post('temp_id');

		$data['input'] 						= $this->payment_model->get_payment();
		$data['page_title'] 				= 'Add Payment';
		$data['form_action']				= base_url('payment/save_payment');
		$data['sel_payment_source_list'] 	= $this->common_model->get_payment_source_list();
		$data['sel_bill_type_list'] 		= $this->common_model->get_bill_type_list(true);
		$data['msg'] 						= $this->msg;
		
		if (!empty($chk_temp_id)) $data['input']['temp_id'] = $chk_temp_id;

		$file_result = [];
		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('payment', $chk_temp_id, '', 0);
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

					$file_result[$key]['thumbnail_path'] = $this->config->item('upload_url').$thumbnail_path;
				} elseif (in_array($extension, ['pdf'])) {
					$file_result[$key]['file_type'] = 'pdf';

					$thumbnail_filename = $file_info['filename'] . '-0.jpg';

					$thumbnail_path = dirname($val['local_path']) . '/' . $thumbnail_filename;

					$file_result[$key]['thumbnail_path'] = $this->config->item('upload_url').$thumbnail_path;
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

		$data['related_invoices'] = [];

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('payment/payment_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_payment($payment_no='')
	{
		$chk_temp_id = $this->input->post('temp_id');
		$payment =  $this->payment_model->get_payment($payment_no);

		if ($payment['payment_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Payment not found!');
			redirect('payment');
		}

		if (!empty($chk_temp_id)) {
			$ticket['temp_id'] = $chk_temp_id;
		}
		
		$data['input'] 						= $payment;
		$data['page_title'] 				= 'Edit Payment';
		$data['form_action']				= base_url('payment/save_payment');
		$data['sel_payment_source_list'] 	= $this->common_model->get_payment_source_list();
		$data['sel_bill_type_list'] 		= $this->common_model->get_bill_type_list(true);
		$data['msg'] 						= $this->msg;

		$file_result	= $this->common_model->get_file_attachment('payment', $payment_no);

		if (!empty($chk_temp_id)) {
			$leftover_attach = $this->common_model->get_temp_file_attachment('payment', $chk_temp_id, '', 0);
  
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
		$this->load->model('bill_model');
		$data['related_invoices'] = $this->bill_model->get_invoice_bill(" AND customer_no = '" . $data['input']['customer_no'] . "'  AND is_void = 0 AND bill_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)");

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('payment/payment_detail',$data);
		$this->load->view('templates/footer');
	}

	//~ private function get_segment()
	//~ {
		//~ $seg = 1;
		//~ $segment = array();
		//~ while ($seg != 0)
		//~ {
			//~ $seg_info 	= '';
				//~
			//~ $segment[] = $seg_info = $this->uri->segment($seg);
							//~
			//~ if($seg_info != ''){
				//~ $seg++;
			//~ }
			//~ else
			//~ {
				//~ $seg = 0;
				//~ return $segment;
			//~ }
		//~ }
	//~ }

	function save_payment()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'payment', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('payment_no') != '') {

			$this->payment_model->payment_delete($this->input->post('customer_no'),$this->input->post('payment_no'));

			//~ $query_str = "DELETE FROM payment WHERE payment_no=".$this->input->post('payment_no');
			//~
			//~ $model				= 'action_log_model';
			//~ $this->load->model($model);
			//~ $ctrl				= $this->router->fetch_class();
			//~ $esc_query_str		= $this->db->escape_str($query_str);
			//~ $method 			= $this->router->method;
			//~ $action_desc		= 'a payment has been deleted';
			//~ $action_category 	= 'delete';
			//~ $action_log 		=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
						//~
			//~ $this->db->query($query_str);

			$this->session->set_flashdata("msg", 'Payment deleted!');
			// redirect('payment');
			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'payment';
			echo json_encode($ajax_return);
			return false;
		}
		else {
			$this->set_form_validation('save');

			//check pay date - somehow can save 0000-00-00
			$date_chk = true;
			$pattern = "/^\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])$/";
			if (!preg_match($pattern, $this->input->post('pay_date'))) {
				$date_chk = false;
			}

			if($this->form_validation->run() == false) {
				$this->msg['error_msg'] = validation_errors();

				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				$this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));
				
				if ($this->input->post('payment_no')=='') {
					$this->add_payment();
				}else{
					$this->edit_payment($this->input->post('payment_no'));
				}
			}elseif(!$date_chk) {

				$this->msg['error_msg'] = 'Pay date is incorrect format.';

				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = 'Pay date is incorrect format.';
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
				
				$this->gen_post_attachments((isset($_FILES['attach_attachment'])?$_FILES['attach_attachment']:array()));

				if ($this->input->post('payment_no')=='') {
					$this->add_payment();
				}else{
					$this->edit_payment($this->input->post('payment_no'));
				}

			}else{
				
				$payment_no = '';
				
				if ( $this->input->post('payment_no') == '' ) {
					$payment_no = $this->payment_model->payment_insert(
													$this->input->post('payment_source'),
													$this->input->post('bill_type'),
													$this->input->post('customer_no'),
													$this->input->post('tranx_date'),
													$this->input->post('pay_date'),
													$this->input->post('amount'),
													$this->input->post('cheque_no'),
													$this->input->post('remark'),
													$this->user['username'],
													$this->input->post('ref_no'),
					);

					//reactivate customer if status is S
					if(!empty($this->input->post('customer_no'))){
						$this->load->model('customer_model');
						$custInfo = $this->customer_model->get_customer($this->input->post('customer_no'));
						if($custInfo['current_status'] == 'S'){
							$this->customer_model->create_status_record($custInfo['customer_no'], 'A', date('Y-m-d'), 0);
							$this->load->model('package_model');
							$reactivate_fee = $this->package_model->get_package($custInfo['package'])['reactivate_fee'];
							if(!empty($reactivate_fee) && floatval($reactivate_fee) > 0.00) {
								$this->load->model('adjustment_model');
								$this->adjustment_model->insert_adj('i', $custInfo['customer_no'], 0, 0, $this->input->post('tranx_date'), 8, 'dr', $reactivate_fee, 'REACTIVATION FEE', 1);
							}
						}
					}

				}else{
					$this->payment_model->payment_update(	$this->db->escape_str($this->input->post('payment_source')),
															$this->db->escape_str($this->input->post('bill_type')),
															$this->db->escape_str($this->input->post('customer_no')),
															$this->db->escape_str($this->input->post('tranx_date')),
															$this->db->escape_str($this->input->post('pay_date')),
															$this->db->escape_str($this->input->post('amount')),
															$this->db->escape_str($this->input->post('cheque_no')),
															$this->db->escape_str($this->input->post('remark')),
															$this->user['username'],
															$this->db->escape_str($this->input->post('payment_no')),
															$this->db->escape_str($this->input->post('ref_no'))
					);
					
					$payment_no = $this->db->escape_str($this->input->post('payment_no'));
				}

				$chk_temp_id = $this->input->post('temp_id');
				if (!empty($chk_temp_id)) {
					$leftover_attach = $this->common_model->get_temp_file_attachment('payment', $chk_temp_id, '', 0, 0); 
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
							
							$this->common_model->insert_file_attachment($file_name , $local_path, 'payment', $payment_no, $remark, $is_print, '', 0, 0, $this->user['idx']);

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
								
								$this->common_model->insert_file_attachment($file_name , $local_path, 'payment', $payment_no, $remark, $is_print, '', '', 0, $this->user['idx']);
							}
						}
					}
				}

				$save_msg = "Payment Saved!";
				if( $this->input->post('email_payment') == 1 ){
					
					$customer_no = $this->input->post('customer_no');
					$pay_no_prefix = $_SESSION['config']['pay_no_prefix'];
					$pdf_file_name  = $customer_no . "_" . $pay_no_prefix . $payment_no ;
					$pdf = $this->payment_receipt( $payment_no, 1, $pdf_file_name ) ;
					if( $pdf == true ){
						$this->load->model('customer_model');
						$custInfo = $this->customer_model->get_customer( $customer_no );
						if( $custInfo['pic_email_1'] != ''){
							
							$this->load->model('email_model');
							$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'PAYMENT RECEIPT' AND is_default = 1 " );
							$default_email_title = $emailInfo['email_title'];
							$default_email_msg = $emailInfo['email_msg'];
							
							$email_title = str_replace( "%MONTH%", date("M"), $default_email_title ) ;
							$email_title = str_replace( "%SUBSCRIBER_NO%", $custInfo['customer_no'] , $email_title ) ;
							$email_title = str_replace( "%CUSTOMER_NAME%", $custInfo['name'], $email_title ) ;
							
							$email_msg = $default_email_msg;
							
							$post_data['recipient_emails'] = $custInfo['pic_email_1'];
							$pdf_file_name = $pdf_file_name.'.pdf';
							$post_data['scheduler_id'] 	= '';
							$post_data['send_by'] 		= '';
							$post_data['email_title']	= $email_title ;
							$post_data['email_msg']		= $email_msg;
							$post_data['email_cust_status'] = 1;
							$post_data['email_attachment']	= $pdf_file_name ;
							$post_data['email_schedule_on'] = date("Y-m-d H:i:s") ;
							$this->load->model('email_model');
							$this->email_model->add_email_schedule( $post_data );
							$save_msg .= "An email attached with this payment PDF will be sent to customer.";
						}
					}
				
				}

				//$this->msg['msg'] = "Payment Saved!";
				//$this->edit_payment($payment_no);
				//$this->index();
				$this->session->set_flashdata("msg", $save_msg);
				//redirect('payment/edit_payment/'.$payment_no);
				
				// echo "<script>window.open('".base_url("payment/payment_receipt/". $payment_no )."')</script>";
				// echo "<script>window.location.href = '".base_url("payment/add_payment")."'</script>";
				//redirect('payment/add_payment/');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'payment';
				$ajax_return['open_url'] = base_url("payment/payment_receipt/". $payment_no);
				echo json_encode($ajax_return);
				return false;
			}
		}
	}


    public function index()
    {
		$row_html = $this->payment_rows(1);
		$post_data = array();
		
		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_pay_date_start'] = $post_data['txt_pay_date_start'];
			$data['txt_pay_date_end'] = $post_data['txt_pay_date_end'];
		} else {
			$payment_filter           = get_session_filter('payment_filter');
			$data['page_item_no'] = $payment_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $payment_filter['txt_search'] ?? '';
			$data['txt_pay_date_start'] = $payment_filter['txt_pay_date_start'] ?? date('Y-m-01');
			$data['txt_pay_date_end'] = $payment_filter['txt_pay_date_end'] ?? date('Y-m-t');
		}

		$data['page_title'] 		= 'Payment';
		$data['form_action'] 		= base_url('payment');
		$data['msg'] 				= $this->msg;
		$data['row_html'] 			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('payment/index',$data);
		$this->load->view('templates/footer');
	}

	function payment_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$payment_filter           = get_session_filter('payment_filter');
			$post_data['page_item_no'] = $payment_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $payment_filter['txt_search'] ?? '';
			$post_data['txt_pay_date_start'] = $payment_filter['txt_pay_date_start'] ?? date('Y-m-1');
			$post_data['txt_pay_date_end'] = $payment_filter['txt_pay_date_end'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['txt_pay_date_start'])) $post_data['txt_pay_date_start'] = date('Y-m-1');

		if (!isset($post_data['txt_pay_date_end'])) $post_data['txt_pay_date_end'] = date('Y-m-t');

		$total_row = 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search = $post_data['txt_search'];
		$txt_pay_date_start = $post_data['txt_pay_date_start'];
		$txt_pay_date_end = $post_data['txt_pay_date_end'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'txt_pay_date_start' => $txt_pay_date_start,
			'txt_pay_date_end' => $txt_pay_date_end
		);
		set_session_filter('payment_filter', $session_array);

		
		if ($page_item_no == '') $page_item_no = 0;
		if ( empty($txt_pay_date_start) ) $txt_pay_date_start = date('Y-m-1');
		if ( empty($txt_pay_date_end) ) $txt_pay_date_end = date('Y-m-t');

		$query_where = "";
		if ( !empty($txt_pay_date_start) ) {
			$query_where = "AND p.pay_date >= '$txt_pay_date_start' ";
		}
		if ( !empty($txt_pay_date_end) ) {
			$query_where .= "AND p.pay_date <= '$txt_pay_date_end' ";
		}

		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		if( $aclInfo['role_name'] == 'BPO' ){
			$query_where .= " AND c.category IN ( 'b' , 'r' ) ";
		}

		$result	= $this->payment_model->get_payment_listing($txt_search, $page_item_no, $query_where);
		$row	= $result['row'];
		$total_row	= $result['total_row'];

		foreach ($row as $key => $val) {
			$row[$key]['tranx_date'] = date_toggle($row[$key]['tranx_date'],$_SESSION['config']['date_format']);
			$row[$key]['pay_date'] = date_toggle($row[$key]['pay_date'],$_SESSION['config']['date_format']);
		}

		$data['pagination'] = paginationSettingsAjax('payment', $total_row, $page_item_no,  $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;

		$html = $this->parser->parse('payment/payment_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function payment_receipt_t($type = '',$type_val= '',$gen_pdf = 0)
	{
		//~ $result = $this->payment_receipt('82620',1,'filename');
		$result = $this->payment_receipt('82620',1);
		if($result == 1){
			echo 'successfully generated pdf';
		}else{
			echo 'fail to generated pdf';
		}
	}

	function payment_receipt($type_val= '',$gen_pdf = 0, $pdf_name = '')
	{
		$seg 			= $this->get_segment();
		$controller		= $seg[0];
		$method 		= $seg[1];
		$param_1		= (empty($type_val))?$seg[2]:$type_val;
		$param_2		= (empty($seg[3]))?'':$seg[3];
		
		if ( empty($param_1) )  {
			$this->session->set_flashdata("warning_msg", 'Payment Receipt not found!');
			redirect('payment');
		}
		
		$get_payment	= $this->payment_model->get_payment($param_1);
		if(!empty($get_payment['amount'])){
			$amount_split = explode('.', $get_payment['amount']);
			$cents = ' ';
			if(!empty($amount_split[1]) && $amount_split[1] != '00'){
				$cent_amount 	= (int)$amount_split[1];
				$cents 			.= 'and '.convert_number_to_words($cent_amount).' cent ';
			}
			$get_payment['amount_in_words'] = convert_number_to_words($amount_split[0]).$cents.'only';

			$use_malay = $this->config->item('use_malay');
			if ($use_malay) {
				$get_payment['amount_in_words_malay'] = ucwords(convert_number_to_words_malay($get_payment['amount']).' sahaja');
			} else {
				$get_payment['amount_in_words_malay'] = $get_payment['amount_in_words'];
			}
		}

		$bill_details = array();
		$bill_detail_query 	= "bill_no = '".$get_payment['ref_no']."'";
		$bill_detail 		= $this->common_model->get_table('bill_detail','*, amount as item_amount, remark as item_remark',$bill_detail_query);
		if (!empty($bill_detail)) {
			$bill_type_list = $_SESSION['bill_type'];
			$cnt = 1;
			foreach ($bill_detail as $b_key => $b_val) {
				$bill_detail[$b_key]['item_count'] = $cnt;
				$bill_detail[$b_key]['bill_type_name'] = $bill_type_list[$b_val['bill_type']] ?? '';
				$bill_detail[$b_key]['bill_type_name_with_remark'] = ($bill_type_list[$b_val['bill_type']] ?? '') . ( !empty($b_val['remark']) ? ' - '.$b_val['remark'] : '' );
				$cnt++;
			}
		}
		$get_payment['bill_details'] = $bill_detail;
		
		$get_payment['tranx_date'] = date_toggle($get_payment['tranx_date'],$_SESSION['config']['date_format_printing']);

		$get_payment['pay_date'] = date_toggle($get_payment['pay_date'],$_SESSION['config']['date_format_printing']);

		$pay_no_prefix = $_SESSION['config']['pay_no_prefix'] ;
		$get_payment['payment_no'] = $pay_no_prefix . $get_payment['payment_no'] ;
		
		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$content_data['company_email'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$content_data['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$content_data['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$content_data['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$content_data['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$content_data['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_brn' ");
		$content_data['company_brn'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_state' ");
		$company_state = $config[0]['val'];
		$content_data['company_state'] = $this->common_model->get_state_name_from_einvoice_code($company_state);

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$content_data['company_phone'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_fax' ");
		$content_data['company_fax'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$content_data['company_email'] = $config[0]['val'];

		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$input['data'][] = $get_payment;

		$header_data = $this->vars;
		$header_data['title']			= 'print preview | payment receipt';
		$header_data['description']		= 'to print receipt';
		$content_data['data']			= (empty($input['data']))?'':$input['data'];

		$data['msg'] = $this->msg;
		
		//~ // page info here, db calls, etc.
		if(!empty($gen_pdf) && $gen_pdf == '1')
		{
			
			$html  = '';
			$html .= $this->load->view('templates/print_header_genpdf_half', $header_data, true);
			//$html .= $this->parser->parse('payment/payment_receipt', $content_data, true);

			$doc_template = $this->config->item('doc_template');

			if (!empty($doc_template)) {

				if (file_exists(FCPATH.'application/views/payment/template/'.$doc_template.'/payment_receipt.php')) {
					$html .= $this->parser->parse('payment/template/'.$doc_template.'/payment_receipt', $content_data, true);
				} else {
					$html .= $this->parser->parse('payment/payment_receipt', $content_data, true);
				}

			} else {
				$html .= $this->parser->parse('payment/payment_receipt', $content_data, true);
			}

			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$data = pdf_create($html, '', false);

			if(empty($pdf_name)) $pdf_name = 'temp_payment_'.date("Y-m-d_h:i:sa", time());
			//$result = write_file('temp/'.$pdf_name.'.pdf', $data);
			//$result = write_file('temp/email_scheduler/'.$pdf_name.'.pdf', $data);
			$result = write_file($this->config->item('upload_path').'/temp/pdf/'.$pdf_name.'.pdf', $data);
			return $result;
		}
		else
		{
			$html  = '';
			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', '', true);
			//$html .= $this->parser->parse('payment/payment_receipt', $content_data, true);

			$doc_template = $this->config->item('doc_template');

			if (!empty($doc_template)) {

				if (file_exists(FCPATH.'application/views/payment/template/'.$doc_template.'/payment_receipt.php')) {
					$html .= $this->parser->parse('payment/template/'.$doc_template.'/payment_receipt', $content_data, true);
				} else {
					$html .= $this->parser->parse('payment/payment_receipt', $content_data, true);
				}

			} else {
				$html .= $this->parser->parse('payment/payment_receipt', $content_data, true);
			}

			$html .= $this->load->view('templates/print_footer', '', true);
			echo $html;
		}

	}

	public function xml_filtered( $xml_type = 'payment' )
	{
		if(!$_POST)
		{
			$this->session->set_flashdata("warning_msg", 'invalid access');
			redirect('payment');
		}
		else
		{

			$total_row 			= 0;
			$page_item_no 		= $this->input->post('page_item_no');
			$txt_search 		= $this->input->post('txt_search');
			$txt_pay_date_start = $this->input->post('txt_pay_date_start');
			$txt_pay_date_end 	= $this->input->post('txt_pay_date_end');

			if ($page_item_no == '') {
				$page_item_no = 0;
			}

			if ( empty($txt_pay_date_start) ) {
				$txt_pay_date_start = date('Y-m-01' , strtotime( "-1 month" , strtotime(date('Y-m-01')) ) );
			}
			if ( empty($txt_pay_date_end) ) {
				$txt_pay_date_end = date('Y-m-t' , strtotime( "-1 month" , strtotime(date('Y-m-t')) ) );
			}

			$query_where = "";
			if ( !empty($txt_search) ){
				$query_where .= "AND ( p.customer_no LIKE '%".$txt_search."%' OR 
									   p.payment_no LIKE '%".$txt_search."%' OR 
									   c.name LIKE '%".$txt_search."%'
									 ) ";
			}
			if ( !empty($txt_pay_date_start) ) {
				$query_where .= "AND p.pay_date >= '$txt_pay_date_start' ";
			}
			if ( !empty($txt_pay_date_end) ) {
				$query_where .= "AND p.pay_date <= '$txt_pay_date_end' ";
			}

			$query_where .= " AND processed = 1 AND is_lock = 1 ";

			$this->load->model('xml_model');
			$this->xml_model->xml_payment( $query_where ) ;

		}
	}

	public function export_payment()
	{
		$post_data = array();
		
		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_pay_date_start'] = $post_data['txt_pay_date_start'];
			$data['txt_pay_date_end'] = $post_data['txt_pay_date_end'];
		} else {
			$payment_filter           = get_session_filter('payment_filter');
			$data['txt_search'] = $payment_filter['txt_search'] ?? '';
			$data['txt_pay_date_start'] = $payment_filter['txt_pay_date_start'] ?? date('Y-m-01');
			$data['txt_pay_date_end'] = $payment_filter['txt_pay_date_end'] ?? date('Y-m-t');
		}

		$page_item_no = 0;
		if ( empty($data['txt_pay_date_start']) ) $data['txt_pay_date_start'] = date('Y-m-1');
		if ( empty($data['txt_pay_date_end']) ) $data['txt_pay_date_end'] = date('Y-m-t');

		$query_where = "";
		if ( !empty($data['txt_pay_date_start']) ) {
			$query_where = "AND p.pay_date >= '".$data['txt_pay_date_start']."' ";
		}
		if ( !empty($data['txt_pay_date_end']) ) {
			$query_where .= "AND p.pay_date <= '".$data['txt_pay_date_end']."' ";
		}

		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		if( $aclInfo['role_name'] == 'BPO' ){
			$query_where .= " AND c.category IN ( 'b' , 'r' ) ";
		}

		$result	= $this->payment_model->get_payment_listing($data['txt_search'], $page_item_no, $query_where, 99999);

		/*
		echo "<pre>";
		print_r($result);
		echo "</pre>";
		*/

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Payment Listing');

		$headers = [
			'Payment No','Post Date','Pay Date','Customer No.','Customer Name',
			'Remark','Amount','Paid For Bill',
		];

		$row = 1;
		$col = 'A';
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row++;

		$data_row = $result['row'];
		foreach ($data_row as $r) {

			$sheet->setCellValue('A'.$row, strval($r['payment_no']));
			$sheet->setCellValue('B'.$row, $r['tranx_date']);
			$sheet->setCellValue('C'.$row, $r['pay_date']);
			$sheet->setCellValue('D'.$row, strval($r['customer_no']));
			$sheet->setCellValue('E'.$row, $r['customer_name']);
			$sheet->setCellValue('F'.$row, $r['remark']);
			$sheet->setCellValue('G'.$row, $r['amount']);
			$sheet->setCellValue('H'.$row, $r['ref_no']);

			$row++;

		}

		foreach (range('A','H') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'payment_listing_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;

	}


    /*public function export_payment()
    {
		$total_row 			= 0;
		$page_item_no 		= $this->input->post('page_item_no');
		$txt_search 		= $this->input->post('txt_search');
		$txt_pay_date_start = $this->input->post('txt_pay_date_start');
		$txt_pay_date_end 	= $this->input->post('txt_pay_date_end');

		if ($page_item_no == '') {
			$page_item_no = 0;
		}

		if ( empty($txt_pay_date_start) ) {
			$txt_pay_date_start = date('Y-m-01' , strtotime( "-1 month" , strtotime(date('Y-m-01')) ) );
		}
		if ( empty($txt_pay_date_end) ) {
			$txt_pay_date_end = date('Y-m-t' , strtotime( "-1 month" , strtotime(date('Y-m-t')) ) );
		}

		$query_where = "";
		if ( !empty($txt_pay_date_start) ) {
			$query_where = "AND p.pay_date >= '$txt_pay_date_start' ";
		}
		if ( !empty($txt_pay_date_end) ) {
			$query_where .= "AND p.pay_date <= '$txt_pay_date_end' ";
		}

		$query_where .= " AND processed = 1 AND is_lock = 1 ";


		$result	= $this->payment_model->get_payment_listing($txt_search, $page_item_no, $query_where);
		$row	= $result['row'];

		foreach ($row as $key => $val) {
			$row[$key]['tranx_date'] = convert_date_1($row[$key]['tranx_date']);
			$row[$key]['pay_date'] = convert_date_1($row[$key]['pay_date']);
		}

		$data['page_title'] 		= 'Export Payments';
		$data['form_action'] 		= base_url('payment/export_payment');
		$data['row_data'] 			= $row;
		$data['txt_search'] 		= $txt_search;
		$data['txt_pay_date_start'] = $txt_pay_date_start;
		$data['txt_pay_date_end'] 	= $txt_pay_date_end;
		$data['pagination'] 		= paginationSettings('', $result['total_row']);
		$data['msg'] 				= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('payment/payment_export',$data);
		$this->load->view('templates/footer');
	}
	*/

	function ajax_get_unprocess_payment(){

		$this->load->model('bill_model');

		if($_POST['customer_no']){
			
			$unpay_data = $this->bill_model->get_last_payment($_POST['customer_no'],1);
		}
			if($unpay_data){
				echo json_encode($unpay_data);
			}else{
				echo json_encode(array("idx" => ""));
			}

	}

	function ajax_get_last_payment(){

		$this->load->model('bill_model');

		if($_POST['customer_no']){
			$last_data = $this->bill_model->get_last_payment($_POST['customer_no']);
		}
			if($last_data){
				echo json_encode($last_data);
			}else{
				echo json_encode(array("idx" => ""));
			}
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

			for( $i=0 ; $i < $total ; $i++ ) {
			  	$tmp_path = $file_arr['tmp_name'][$i];

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
							if (extension_loaded('imagick')){
								_create_preview_images($prefix_n_file_name, $this->config->item('upload_path')."/temp/");
							}
						}
						
						$this->common_model->insert_tmp_file_attachment($file_name , $local_path, 'payment', $temp_id, $remark, $is_print, '', 0, 0, $this->user['idx']);
					}

		    	}
			}
		}

	}

	/*
	 *
	 * this function already move to model
	 *
	 * */

	//~ function payment_summary($category, $status, $date_start, $date_end)
	//~ {
		//~ $return_val = array();
		//~ $query_where = '';
		//~ $amount = 0;
		//~ if ( !empty($category) && $category !== 'all' ) {
			//~ $query_where = "AND c.category = '$category' ";
		//~ }
		//~ if ( !empty($status) && $status !== 'all' ) {
			//~ $query_where .= "AND c.status = '$status' ";
		//~ }
		//~
		//~ $query_str = "SELECT sbt.bill_type_id, sbt.name as bill_type_name, sbt.is_debit " .
					//~ "FROM sys_bill_type sbt " .
					//~ "ORDER BY sbt.bill_type_id ";
		//~ $query = $this->db->query($query_str);
		//~ foreach ( $query->result_array() as $row ) {
			//~ $total_bill_type = 0;
			//~ $return_val['r'][$row['bill_type_id']]['amount'] = 0;
			//~ $return_val['b'][$row['bill_type_id']]['amount'] = 0;
			//~ $return_val['w'][$row['bill_type_id']]['amount'] = 0;
			//~
			//~ $query_str = "SELECT c.category, " .
						//~ "SUM(p.amount) as amount " .
						//~ "FROM payment p " .
						//~ "INNER JOIN customer c ON c.customer_no = p.customer_no " .
						//~ "WHERE p.bill_type = '" . $row['bill_type_id'] . "' " .
						//~ "AND p.pay_date >= '$date_start' AND p.pay_date <= '$date_end' " .
						//~ $query_where .
						//~ "GROUP BY c.category " .
						//~ "ORDER BY c.category";
			//~ $query = $this->db->query($query_str);
			//~ foreach ( $query->result_array() as $row2 ) {
				//~ if ($row['is_debit']) {
					//~ $amount = $row2['amount'];
				//~ }
				//~ else {
					//~ $amount = $row2['amount'] * -1;
				//~ }
				//~
				//~ $return_val[$row2['category']][$row['bill_type_id']]['amount'] += $amount;
				//~ $total_bill_type += $amount;
			//~ }
			//~
			//~ $return_val['bill_type'][$row['bill_type_id']] = array(
								//~ 'name' => $row['bill_type_name'],
								//~ 'total' => $total_bill_type,
								//~ );
		//~ }
		//~
		//~ return $return_val;
	//~ }
	//~
	/*
	 *  this function already moved to payment_model
	 *
	 * */

	//~ function detail_of_payment($category, $status, $payment_source, $date_start, $date_end)
	//~ {
		//~ $return_val = array();
		//~ $query_where = '';
		//~ $amount = 0;
		//~
		//~ $payment_source_list = $_SESSION['payment_source'];
		//~ $customer_category_list = $_SESSION['cust_category'];
		//~ $bill_type_list = $_SESSION['bill_type'];
		//~
		//~ if ( !empty($category) && $category !== 'all' ) {
			//~ $query_where = "AND c.category = '$category' ";
		//~ }
		//~ if ( !empty($status) && $status !== 'all' ) {
			//~ $query_where .= "AND c.status = '$status' ";
		//~ }
		//~ if ( !empty($payment_source) && $payment_source !== 'all' ) {
			//~ $query_where .= "AND p.payment_source = '$payment_source' ";
		//~ }
			//~
		//~ $query_str = "SELECT c.category, c.customer_no, c.name as customer_name, " .
					//~ "b.name as building_name, " .
					//~ "p.pay_date, p.remark, p.payment_no, p.cheque_no, p.amount, p.bill_type, p.payment_source " .
					//~ "FROM payment p " .
					//~ "INNER JOIN customer c ON c.customer_no = p.customer_no " .
					//~ "LEFT JOIN building b ON b.building_no = c.building " .
					//~ "WHERE p.pay_date >= '$date_start' AND p.pay_date <= '$date_end' " .
					//~ $query_where .
					//~ "ORDER BY c.category, p.bill_type, p.pay_date DESC, p.payment_no DESC, customer_no ";
		//~ $query = $this->db->query($query_str);
		//~ foreach ( $query->result_array() as $row ) {
			//~ $category_name = $customer_category_list[$row['category']];
			//~ $payment_source_name = $payment_source_list[$row['payment_source']];
			//~ $bill_type_name = $bill_type_list[$row['bill_type']];
			//~
			//~ $return_val[$category_name][$bill_type_name][] = array(
															//~ "pay_date" => $row['pay_date'],
															//~ "customer_no" => $row['customer_no'],
															//~ "customer_name" => $row['customer_name'],
															//~ "building_name" => $row['building_name'],
															//~ "remark" => $row['remark'],
															//~ "payment_no" => $row['payment_no'],
															//~ "payment_source_name" => $payment_source_name,
															//~ "cheque_no" => $row['cheque_no'],
															//~ "amount" => $row['amount'],
															//~ );
		//~ }
//~
		//~ return $return_val;
	//~ }

}
