<?php
class Einvoice_config_model extends MY_Model{

	protected $_table = 'config_einvoice';
	protected $_primary_key = 'idx';

	public function __construct()
	{
        parent::__construct();
	}

	function login_myinvois_portal()
	{

		$this->load->model('Einvoice_api_model');
		$data = array();
		/*
            "client_id" => $this->config->item('einvoice_client_id'),
            "client_secret" => $this->config->item('einvoice_client_secret'),
            "grant_type" => "client_credentials",
            "scope" => "InvoicingAPI",
		*/
        $data['client_id'] = $this->config->item('einvoice_client_id');
        $data['client_secret'] = $this->config->item('einvoice_client_secret');
        $data['grant_type'] = 'client_credentials';
        $data['scope'] = 'InvoicingAPI';
        $data['api_path'] = $this->config->item('einvoice_api');
		$result = $this->Einvoice_api_model->api_login( $data );

		if (isset($result->access_token)) {
			if (!empty($result->access_token)) {
				$_SESSION['einvoice']['access_token'] = $result->access_token;
				return true;
			}
		}

		return false;

	}

	/*function save_submission_api( $data )
	{
		$sql = "INSERT INTO `api_einvoice` (`inv_num`, `copy_id`, `json_data`, `einvoice_type`, `einvoice_submission_id`, `einvoice_uuid`, `einvoice_status`, `einvoice_longid`, `file_name`, `signed_file_name`, `created_date`) VALUES (?,?,?,?,?,?,?,?,?,?,NOW()) 
		ON DUPLICATE KEY UPDATE copy_id=?, json_data=?, einvoice_type=?, einvoice_submission_id=?, einvoice_uuid=?, einvoice_status=?, einvoice_longid=?, file_name=?, signed_file_name=?, created_date=NOW()";

		$this->db->query($sql, array(
			$data['inv_num'], 
			$data['copy_id'], 
			$data['json_data'], 
			$data['einvoice_type'], 
			$data['einvoice_submission_id'], 
			$data['einvoice_uuid'], 
			$data['einvoice_status'], 
			$data['einvoice_longid'], 
			$data['file_name'], 
			$data['signed_file_name'], 
			$data['copy_id'],
			$data['json_data'],
			$data['einvoice_type'],
			$data['einvoice_submission_id'],
			$data['einvoice_uuid'],
			$data['einvoice_status'],
			$data['einvoice_longid'],
			$data['file_name'],
			$data['signed_file_name'],
		));
	}

	function update_submission_status($data) {

		$sql = "UPDATE `api_einvoice` SET einvoice_submission_id=?, einvoice_uuid=?, einvoice_status=?, einvoice_longid=? WHERE inv_num=? ";

		$this->db->query($sql, array( 
			$data['einvoice_submission_id'], 
			$data['einvoice_uuid'], 
			$data['einvoice_status'], 
			$data['einvoice_longid'], 
			$data['inv_num'] 
		));

	}

	function cancel_submission( $inv_num ) {
		$sql = "UPDATE `api_einvoice` SET einvoice_status='C' WHERE inv_num=? ";
		$this->db->query($sql, array( 
			$inv_num 
		));
	}*/

}