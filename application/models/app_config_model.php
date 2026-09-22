<?php
class App_config_model extends MY_Model{

	protected $_table = 'action_log';
	protected $_primary_key = 'action_log_id';

	public function __construct()
	{
		parent::__construct();
	}
	
	public function load_sys_config(){
		$query_str 	= "SELECT sc.* FROM sys_config sc ";
		$query 		= $this->db->query($query_str);		
		$return_val	= array();
		foreach ( $query->result_array() as $row ) {
			$return_val[$row['key']] = $row['val'];
		}
		return $return_val;
	}
	
	public function load_sys_account_status(){
		
		$query_str 	= 'SELECT * FROM sys_account_status ORDER BY status_code';
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['status_code']] = $val['name'];
		}
		return $return_val;
	}
	
	public function load_sys_customer_category(){
		
		$query_str = 'SELECT * FROM sys_customer_category ORDER BY category_code';
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['category_code']] = $val['name'];
		}
		return $return_val;
	}
	
	public function load_sys_bill_type(){
		
		$query_str = 'SELECT * FROM sys_bill_type ORDER BY bill_type_id';
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['bill_type_id']] = $val['name'];
		}
		return $return_val;
	}
	
	public function load_sys_marital_status(){
		
		$query_str = 'SELECT * FROM sys_marital_status ORDER BY marital_status_code';		
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['marital_status_code']] = $val['name'];
		}
		return $return_val;
	}
	
	
	public function load_sys_payment_source(){
		
		$query_str = 'SELECT * FROM sys_payment_source ORDER BY payment_source_id';		
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['payment_source_id']] = $val['name'];
		}
		return $return_val;
	}
	
	public function load_sys_state(){
		$query_str = 'SELECT * FROM sys_state ORDER BY state_code';
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['state_code']] = $val['name'];
		}
		return $return_val;		
	}
	
	public function load_sys_tax_type(){		
		$query_str = 'SELECT * FROM sys_tax_type ORDER BY code';
		$query 		= $this->db->query($query_str);
		$return_val	= array();
		foreach ($query->result_array() as $val) {
			$return_val[$val['code']] = $val['percent'];
		}
		return $return_val;
	}
	
	public function load_building(){
		$this->load->model('common_model');
		$result = $this->common_model->get_building_list('building_no') ?? [];
		return array_column($result, 'name', 'building_no');
	}
}
