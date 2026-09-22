<?php

include_once( APPPATH . 'core/DataPage_Controller.php' );
include_once( APPPATH . 'controllers/app_config.php' );
class Chome extends DataPage_Controller {

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set('Asia/Kuala_Lumpur');
        $this->load->model('common_model');
        $this->load->helper('custom_helper');

        $this->load->library(array('form_validation','session','upload'));

        // this controller can only be called from the command line
        //if (!$this->input->is_cli_request()) show_error('Direct access is not allowed');

        //if login is not a customer, then redirect to normal itelco home
        $this->cuser = $this->session->userdata("cuser");
        if (empty($this->cuser['customer_no'])) {
        	redirect("/");
        }

		$this->termination_status = array(
			'P' => 'Pending Send',
			'S' => 'Pending Customer Signature',
			'F' => 'Customer Rejected',
			'D' => 'Pending Confirmation',
			'C' => 'Confirmed',
			'A' => 'Cancelled',
		);
    }

    function index()
    {
    	//echo "You are now in Customer Portal. <a href='".base_url("auth/clogout")."'>Logout</a>";
    	$this->init();

    	$data['page_title'] 		= 'Customer Dashboard';

    	$this->menu['cuser'] = $this->cuser;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/cmenu',$this->menu);
		$this->parser->parse('chome/index',$data);
		$this->load->view('templates/footer');

    }

    function error()
    {
    	$data['page_title'] 		= 'Something went wrong.';

    	$this->menu['cuser'] = $this->cuser;

    	$data['msg'] = 'Something went wrong. Please contact administrator.';

   		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/cmenu',$this->menu);
		$this->parser->parse('chome/index',$data);
		$this->load->view('templates/footer');
    }

    function termination_form()
    {
//for send contact, will have additional post fields

    	$this->load->model('customer_model');

    	$customer_no = $this->cuser['customer_no'] ?? '';
    	if (empty($customer_no)) {
    		redirect('chome/error');
    		exit;
    	}
		$contact_list = $this->input->post('contact_list');
		
		$customer = $this->customer_model->get_customer( $customer_no );

		$header_data 				= $this->vars;
		$header_data['title']		= 'Termination Form';
		$header_data['description']	= 'to print termination form';
		$header_data['cssfiles'][] 	= 'css/theme/bootstrap-grid.css';
		
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$customer['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		
		if( $customer['pic_name'] == '' ){
			$customer['pic_name'] = $customer['name'];
		}
		
		if( $customer['mobile_num'] != '' ){
			$customer['contact_no'] = $customer['mobile_num'];
		}elseif( $customer['tel_num'] != '' ){
			$customer['contact_no'] = $customer['tel_num'];
		}
		
		$this->load->model('building_model');
		$building = $this->building_model->get_building( $customer['building'] );
		$customer['building_name'] = $building['name'];
		
		if( $customer['category'] == 'r' ){
			$customer['residential_check'] = '/';
			$customer['business_check'] = '';
		}else{
			$customer['residential_check'] = '';
			$customer['business_check'] = '/';			
		}
		
		if( $customer['contract_month'] != '' && $customer['contract_month'] >= 0 ){
			$customer['contract_month'] = $customer['contract_month'] . ' month(s)';
		}

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$customer['company_addr_1'] = $config[0]['val'];

		//company name and address
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
		$customer['company_addr_1'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
		$customer['company_addr_2'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
		$customer['company_addr_3'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
		$customer['company_postal'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
		$customer['company_city'] = $config[0]['val'];

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$customer['company_phone'] = $config[0]['val'];
	
		// signature
		$signature = $this->common_model->get_file_attachment_with_signature_form_info('customer', $customer_no, 'termination_signature', 0, 'termination form', NULL, NULL);

		if (!empty($signature)) {
			$customer['signature'] 		= $this->config->item('upload_url') . $signature[0]['local_path'];
			$customer['sign_date'] 		= $signature[0]['created_date'];
			$customer['signer_name'] 	= $signature[0]['signer_name'];
			$customer['signer_ic'] 		= $signature[0]['signer_ic'];
		} else {

			$select = 'acc_name, icno, pic_name, pic_nric_passport, acc_type';
			$where = ['acc_id ' => $customer['profile_id']];
			$profile = $this->common_model->get_table('profile', $select, $where);

			$customer['signature'] 		= '';
			$customer['sign_date'] 		= '';
			if($profile[0]['acc_type'] === 'r'){
				$signer_name = $profile[0]['acc_name'];
				$signer_ic = $profile[0]['icno'];
			}else{
				$signer_name = $profile[0]['pic_name'];
				$signer_ic = $profile[0]['pic_nric_passport'];
			}
			$customer['signer_name'] 	= $signer_name;
			$customer['signer_ic'] 		= $signer_ic;
		}

		//Equipment list
		$customer['equipment_types'] = $this->common_model->get_equipment_type_list();

		//Currently selected equipment
		$customer['equipment_list'] = $this->customer_model->get_equipment_record($customer_no);

		$frontend_url = $this->config->item('frontend_url') ?? false;
		$customer['tnc_url'] = $frontend_url 
									? 
										$customer['category'] == 'r' 
											?
												$frontend_url . $this->config->item('residential_tnc_endpoint') ?? 'tnc'
											:
												$frontend_url . $this->config->item('commercial_tnc_endpoint') ?? 'CommercialTnc'
									:
										'';

		$report_send = 0;

		if (!empty($contact_list)) {

			//contact list should be unused here

		}

		$menu_data = array();
		//$menu_data['send_btn'] = 0;
		$menu_data['form_action'] = base_url('chome/termination_form/'.$customer_no);
		$menu_data['report_send'] = $report_send;

		/*
		echo "<pre>";
		print_r($customer);
		echo "</pre>";
		exit;
		*/

		$customer['customer_sign'] = $this->customer_model->check_termination_sign($customer_no);

		$customer['termination_init'] = false;
		$customer['termination_confirm'] = false;
		$customer['is_customer'] = true;

		$customer['sel_state_list'] 			= $this->common_model->get_state_list();
		$customer['sel_building_list'] 	= $this->common_model->get_building_list();

		$latest_termination_flow = $this->customer_model->check_latest_termination_flow($customer_no);
		$customer['current_termination_flow'] = $latest_termination_flow['termination_status'] ?? 'A';

		//current termination if its not S , but D or F (F being reject)

		$customer['termination_data'] = $this->customer_model->get_latest_termination_info($customer_no);

		$customer['customer_no'] = $customer_no;

		$menu_data['current_status'] = (isset($this->termination_status[$customer['current_termination_flow']]) ? $this->termination_status[$customer['current_termination_flow']] : '');
		
		$this->load->view('templates/print_header', $header_data);
		$this->load->view('templates/termination/print_menu', $menu_data);
		$this->parser->parse('customer/termination_form', $customer);
		$this->load->view('templates/print_footer', '');
    }

    public function update_is_termination_terms_accepted() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model('customer_model');

        $is_checked  = $this->input->post('is_checked', TRUE);
        $customer_no = $this->input->post('customer_no', TRUE);

        if (empty($customer_no)) {
            echo json_encode([
				'success' => false, 
				'message' => 'Invalid Customer Number.'
			]);
            return;
        }

        $result = $this->customer_model->update_termination_terms_status($customer_no, $is_checked);

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Agreement to Terms and Conditions has been recorded.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update status or no changes made.'
            ]);
        }
    }

	//Set all the confign value into session
	function init()
	{
		$obj_app_config = new app_config();
		$obj_app_config->load_config();
	}

	public function upload_signature() {

		$this->load->model('customer_model');

		$customer_no 	= $this->input->post('customer_no');
		$signer_name 	= $this->input->post('signer_name');
		$signer_ic 		= $this->input->post('signer_ic');
		$signature_file = $_FILES['signature_file'] ?? false;
		$upload_path = $this->config->item('upload_path');

		//validate if terms have been accepted or not
		$customer_sign = $this->customer_model->check_termination_sign($customer_no);
		if (!$customer_sign) {
			echo json_encode(['status' => 'no_terms']);
			return false;
		}

		if($signature_file && !empty($signature_file['tmp_name']) && file_exists($signature_file['tmp_name'])) {
			$prefix = date('YmdHis');
			$new_file_name = "signature-{$customer_no}-{$prefix}.png";
			$local_path = "/upload/" . $new_file_name;
			$full_destination = $upload_path . $local_path;

			$src = imagecreatefrompng($signature_file['tmp_name']);
			$width = imagesx($src);
			$height = imagesy($src);

			$new_width = 400;
			$new_height = ($height / $width) * $new_width;
			$dst = imagecreatetruecolor($new_width, $new_height);

			imagealphablending($dst, false);
			imagesavealpha($dst, true);
			$transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
			imagefill($dst, 0, 0, $transparent);

			imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			if (imagepng($dst, $full_destination)) {
				$existing = $this->common_model->get_file_attachment('customer', $customer_no, 'termination_signature', 0);
				if (!empty($existing)) {
					foreach ($existing as $row) {
						$this->common_model->remove_attachment($row['file_id'], $customer_no, 'termination_signature');
					}
				}
				$this->common_model->insert_file_attachment($new_file_name, $local_path, 'customer', $customer_no, 'Signature Upload', 0, 'termination_signature', 0, 0, 0);			

			} else {
				echo json_encode(['status' => 'err_msg', 'err_msg' => 'Disk error']);
				return false;
			}
		}
		
		$new_signature 			= $this->common_model->get_file_attachment('customer', $customer_no, 'termination_signature', 0);
		$file_idx = ($new_signature[0]['file_id'] ?? 0);
		if(!empty($new_signature)){
			$this->save_signature_info([
				'form_type' => 'termination form',
				'signer_name' => $signer_name,
				'signer_ic' => $signer_ic,
				'customer_no' => $customer_no,
				'signature_file_id' => $file_idx,
			], $customer_no, 'termination form');
		}

		//change status
		$this->customer_model->create_termination_status_record($customer_no, 'D', date('Y-m-d H:i:s'), 0);

		//update signature idx
		$this->customer_model->termination_update_file_idx($customer_no, $file_idx);

		//log action
		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);
		$ctrl			= $this->router->fetch_class();
		$method 		= $this->router->method; 	
		$action_desc	= 'account('.$this->db->escape_str($customer_no).') signed termination form.';
		$action_category = 'update';
		$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,'',$action_desc,$action_category,$this->db->escape_str($customer_no));

		//send notice to administration
		$this->notify_admins('D');
		
		echo json_encode(['status' => 'success']);

		return false;
	}

	function reject_form() {

		$this->load->model('customer_model');

		$customer_no 	= $this->input->post('customer_no');
		$reject_reason 	= $this->input->post('reject_reason');

		$this->customer_model->create_termination_status_record($customer_no, 'F', date('Y-m-d H:i:s'), 0, $reject_reason);

		//send notice to administration
		$this->notify_admins('F', $reject_reason);

		echo json_encode(['status' => 'success']);

		return false;

	}

	private function save_signature_info (array $data, $customer_no, $form_type) {
		$this->load->model('customer_model');
		if(!empty($data) && $customer_no){
			$this->customer_model->save_form_signatures($data, $customer_no, $form_type);
		}
	}

	private function notify_admins($status_type='D', $remarks='') {

		$this->load->library('notification_service');
		$this->load->model('email_model');

		$customer_no = $this->cuser['customer_no'] ?? '';

		if (empty($customer_no) || ($status_type != 'D' && $status_type != 'F')) {
			log_message('error', 'Error sending termination signature status update');
			return false;
		}

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$isp_name      = $cfg['isp_name'] ?? '';

		$email_template_name = $status_type == 'F'
			? 'TERMINATION SIGNATURE REJECTION STATUS'
			: 'TERMINATION SIGNATURE CONFIRMATION STATUS';

		$meta_template_name = 'TERMINATION FORM STATUS UPDATED';

		$email_template = $this->email_model->get_email_template_detail(
			"AND template_name = '{$email_template_name}' AND is_default = 1"
		);

		$header = str_replace(
			'%ISP_NAME%',
			$isp_name,
			$email_template['email_title']
		);

		$replacements = [
			'%ISP_NAME%'         => $isp_name,
			'%CUSTOMER_NO%'      => $customer_no,
			'%REJECTION_REASON%' => $remarks,
		];

		$body = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$email_template['email_msg']
		);

		$admin_list = $this->common_model->get_admin_list();

		$email_list = [];
		$whatsapp_list = [];
		$telegram_list = [];

		foreach ($admin_list as $value) {

			if (!empty($value['email'])) {
				$email_list[] = $value['email'];
			}

			if (!empty($value['mobile_no']) && $value['allow_whatsapp'] == 1) {
				$whatsapp_list[] = $value['mobile_no'];
			}

			if (!empty($value['telegram_id']) && $value['allow_telegram'] == 1) {
				$telegram_list[] = $value['telegram_id'];
			}
		}

		$contacts = $this->notification_service->buildContacts($email_list, $whatsapp_list, $telegram_list);

		$this->notification_service->queue(
			$contacts,
			$header,
			$body,
			$meta_template_name,
			[
				'customer_no' => $customer_no,
				'status'      => $status_type == 'F' ? 'Rejected' : 'Signed',
				'remarks'     => $status_type == 'F'
					? ($remarks ?: '-')
					: 'Customer has signed the termination form.',
			],
			$customer_no,
			[
				'controller'    => 'chome',
				'send_method'   => 'manual',
				'doc_type'      => '[TERMINATION FORM CUSTOMER SIGNATURE STATUS]',
				'email_starter' => $email_template_name,
			]
		);
	}

}