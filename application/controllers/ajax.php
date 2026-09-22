<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
//~ include_once( APPPATH . 'controllers/package.php' );

class Ajax extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		$this->load->model('common_model');
    }

    function ajax_remove_attachment(){
		//$this->load->model("common_model");

    	$return = array("success" => 0, "msg" => "Failed to delete.");

    	//$reg_no = $_POST['reg_no'];
    	$file_id = $_POST['file_id'];
    	$is_temp = $_POST['is_temp'];
    	$temp_id = $_POST['temp_id'];

    	if ($is_temp == '1') {
    		$success = $this->common_model->remove_temp_attachment($file_id);
    	} else {
    		$success = $this->common_model->remove_attachment($file_id, 0);
    	}
    	
    	if($success == 1 ){
    		$return['success'] = 1;
    		$return['msg'] = "Attachment deleted";
    	}

    	echo json_encode($return);

		
	}

	function ajax_change_attach_remark() {

    	$return = array("success" => 0, "msg" => "Failed to delete.");

    	//$reg_no = $_POST['reg_no'];
    	$file_id = $_POST['file_id'];
    	$remark = $_POST['remark'];

    	$is_temp = $_POST['is_temp'];

    	if ($is_temp == '1') {
    		$success = $this->common_model->remove_temp_attachment($file_id);
    	} else {
    		$success = $this->common_model->change_remark($file_id, $remark);
    	}
    	
    	if($success == 1 ){
    		$return['success'] = 1;
    		$return['msg'] = "Remark Changed";
    	}

    	echo json_encode($return);

	}

	function get_building_details() {
		$this->load->model('building_model');
		$building_no = $this->input->post('building_no');

		$details = $this->building_model->get_building($building_no);
		echo json_encode($details);
	}

	function search_user() {

		//$this->load->model("user_model");
		//$this->db->from($table);
		
		$query = '';
		if(!empty($_GET['query']))
		{						

			$keyword =$this->db->escape_str($_GET['query']);	

			$q = "SELECT u.idx, u.username, u.email, u.mobile_no, u.telegram_id FROM `user` u 
			WHERE u.`username` LIKE '%$keyword%' 
			";

			if (!empty($_GET['include_customer'])) {
				$q .= "UNION (
					SELECT c.customer_no AS idx, CONCAT(c.customer_no, ' - ', c.name) AS username, p.pic_email_1 AS email, p.acc_mobileno AS mobile_no, p.telegram_id   
					FROM customer c 
					LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
					WHERE (c.name LIKE '%$keyword%' OR c.customer_no LIKE '%$keyword%') 
				) ";
			}

			$q .= " ORDER BY username LIMIT 10";

			$query = $this->db->query($q);
			$result = $query->result_array();

		}			

		echo json_encode($result);

	}

	function send_einvoice() {
		$result = array();
		$bill_no = $_POST['bill_no'];
		$bill_type = $_POST['bill_type'];

		$this->load->model('bill_model');

		if ($bill_type == 'CN' || $bill_type == 'DN') {
			$return = $this->bill_model->resend_einvoice_cndn($bill_no);
		} else {
			$return = $this->bill_model->resend_einvoice($bill_no);
		}

		if ($return['status']) {
			$result['succ'] = true;
			$result['msg'] = '';
		} else {
			$result['succ'] = false;
			$result['msg'] = $return['err'];
		}

		echo json_encode($result);
	}

	function consolidate_einvoice() {
		$result = array();
		$bill_arr = $_POST['bill_arr'];

		$this->load->model('bill_model');
		$return = $this->bill_model->consolidated_einvoice($bill_arr);

		if ($return['status']) {
			$result['succ'] = true;
			$result['msg'] = '';
		} else {
			$result['succ'] = false;
			$result['msg'] = $return['err'];
		}

		echo json_encode($result);
	}

	function send_consolidated_einvoice() {
		$result = array();
		$inv_no = $_POST['inv_no'];

		$this->load->model('bill_model');
		$return = $this->bill_model->resend_consolidate($inv_no);

		if ($return['status']) {
			$result['succ'] = true;
			$result['msg'] = '';
		} else {
			$result['succ'] = false;
			$result['msg'] = $return['err'];
		}

		echo json_encode($result);
	}

	//Ajax - Function
	function autocomplete_asset_code()
	{
		$this->load->model('asset_model');
		$return_val = array();
		$assets = $this->asset_model->get_autocomplete_asset_code($this->input->post('keyword'));
		
		$return_val = $assets;
		
		echo json_encode($return_val);
	}

	function autocomplete_customer()
	{
		$this->load->model('customer_model');
		$return_val = array();
		$accounts = $this->customer_model->get_autocomplete_load_customer($this->input->post('keyword'));
		
		$return_val = $accounts;
		
		echo json_encode($return_val);
	}

	function autocomplete_load_dealer()
	{
		$return_val = $this->dealer_model->get_autocomplete_load_dealer($this->input->post('keyword'));
		echo json_encode($return_val);
	}

	function ajax_get_category_pic()
	{
		$category_idx = $_POST['category_idx'];

		$this->load->model('asset_model');
		$contacts = $this->asset_model->get_category_pic($category_idx);
		$return_val = array();

		$return_val = $contacts;

		echo json_encode($return_val);
	}

	function load_package_list()
	{
		$category 	= $this->input->post("category");
		$return_val = $this->common_model->get_package_list($category, 'a');
		
		if ($this->input->is_ajax_request()) {
			echo json_encode($return_val);
		} 
		else {
			return $return_val;
		}
	}

	function load_package_detail()
	{
		$this->load->model('customer_model');

		$package_no = $this->input->post("package_no");
		if ( empty($package_no)) {
			$customer_no = $this->input->post("customer_no");
			$customer_data = $this->customer_model->get_customer($customer_no);
			
			$return_val['name'] = $customer_data['package_name'];
			$return_val['monthly_charge'] = $customer_data['monthly_charge'];
			$return_val['package_month'] = $customer_data['package_month'];
			$return_val['stop_service_after'] = $customer_data['stop_service_after'];

			$return_val['bill_waive_period'] = $customer_data['bill_waive_period'];
			$return_val['free_package_upgrade'] = $customer_data['free_package_upgrade'];
			$return_val['upgrade_package_id'] = $customer_data['upgrade_package_id'];
			$return_val['delay_trial_start'] = $customer_data['delay_trial_start'];
		}
		else 
		{					
			$this->load->model('package_model');
			$return_val = $this->package_model->get_package($package_no);
		}
		
		if ($this->input->is_ajax_request()) 
		{
			echo json_encode($return_val);
		}
		else 
		{
			return $return_val;
		}
	}

	public function validate_tin()
	{
		$tin 		= $this->input->post('tin');
		$idValue 	= $this->input->post('idValue');
		$idType		= $this->input->post('idType');

		if(empty($tin) || empty($idValue) || empty($idType)) {
			echo json_encode(array('is_valid' => false, 'msg' => 'Please make sure Tin is filled in.'));
			return;
		}
		
		$this->load->model('einvoice_api_model');
		$this->load->model('einvoice_config_model');

		$login = $this->einvoice_config_model->login_myinvois_portal();

		if (!isset($_SESSION['einvoice']['access_token'])) {

			$data = [
				'tin' => $tin,
				'idvalue' => $idValue,
				'type' => $idType,
				'api_path' => $this->config->item('einvoice_api'),
				'token' => $_SESSION['einvoice']['access_token']
			];

			$is_valid = $this->einvoice_api_model->validate_tin($data);

			echo json_encode(array('is_valid' => $is_valid, 'msg' => ''));

		} else {
			echo json_encode(array('connected' => false, 'msg' => 'Unable to login to LHDN Einvoice API, please check einvoice settings.'));
		}
	}

	function ajax_get_invoice_bill(){
		$this->load->model('bill_model');

		$return = array();
		$qwhere = '';

		if( $this->input->post('customer_no') )
			$qwhere .= " AND customer_no = '".$this->input->post('customer_no')."' AND is_void = 0 AND bill_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
		
		$bills = $this->bill_model->get_invoice_bill($qwhere);
		
		echo json_encode( $bills );
	}

	public function ajax_verify_radius_account()
	{
		$this->load->model('customer_model');

		$login_username = $this->input->post('login_username');
		$customer_no    = $this->input->post('customer_no');

		$radius_login_username = $login_username;
		$suffix = $_SESSION['config']['radius_suffix'] ?? '';

		if ($suffix && strpos($login_username, $suffix) === false) {
			$radius_login_username .= $suffix;
		}

		$self_check = '';

		if ($customer_no) {
			$customer   = $this->customer_model->get_customer($customer_no);
			$self_check = $customer['login_username'] ?? '';

			if ($radius_login_username === $self_check) {
				echo json_encode(['exist' => 2]);
				return;
			}
		}

		$local = $this->customer_model->filtered_customer(
			'',
			" AND c.login_username = '{$radius_login_username}' "
		);

		if ($customer_no) {
			$local = array_filter(
				$local,
				fn($row) => $row['customer_no'] != $customer_no
			);
		}

		$local_exist  = !empty($local);
		$radius_exist = !empty(
			$this->customer_model
				->radius_account_exist($radius_login_username, $self_check)['exist']
		);

		echo json_encode([
			'exist' => ($local_exist || $radius_exist) ? 1 : 0
		]);
	}

	public function audit_download_file()
	{

		$chk = check_acl('audit', 'V', false);
		if (!$chk) {
        	http_response_code(404);
        	echo "Error: No permissions to view audit doc.";
        	exit;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'serve_file' && isset($_POST['file_id'])) {

			$file_id = $_POST['file_id'];
			$file_info = $this->common_model->get_audit_file_by_id($file_id);

			$filePath = $this->config->item('upload_path').$file_info['local_path'];

			if (file_exists($filePath)) {

		        // Clean output buffer to ensure no extra whitespace corrupts the file
		        if (ob_get_level()) {
		            ob_end_clean();
		        }

        		/*http_response_code(200);
        		echo "ALL OK.";
        		exit;*/

		        // Define headers to prompt browser file download
		        header('Content-Description: Audit Doc Transfer');
		        header('Content-Type: '.mime_content_type($filePath)); // Standard binary type
		        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
		        header('Expires: 0');
		        header('Cache-Control: must-revalidate');
		        header('Pragma: public');
		        header('Content-Length: ' . filesize($filePath));
		        
		        // Read the file and write it to the output stream
		        readfile($filePath);
		        exit;

			} else {
				http_response_code(404);
       			echo "Error: File not found.";
        		exit;
			}

		} else {
	        http_response_code(404);
	        echo "Error: File not found.";
	        exit;
		}
	}

}