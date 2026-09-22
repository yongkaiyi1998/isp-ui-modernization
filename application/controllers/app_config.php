<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App_config extends CI_Controller {

	//~ protected $app_config;
	//~ protected $acc_status;
	//~ protected $bill_type;
	//~ protected $cust_category;
	//~ protected $marital_status;
	//~ protected $payment_method;
	//~ protected $payment_source;
	//~ protected $state;
	//~ protected $tax_type;
	//~ protected $building;
	
	function __construct()
	{
        parent::__construct();
		$this->load->model('app_config_model');
    }
    
    public function index()
    {
	}
	
	function load_config()
	{				
		$_SESSION['config'] 		= $this->app_config_model->load_sys_config();
		$_SESSION['acc_status'] 	= $this->app_config_model->load_sys_account_status();		
		$_SESSION['bill_type'] 		= $this->app_config_model->load_sys_bill_type();
		$_SESSION['cust_category'] 	= $this->app_config_model->load_sys_customer_category();
		$_SESSION['marital_status'] = $this->app_config_model->load_sys_marital_status();
		$_SESSION['payment_source'] = $this->app_config_model->load_sys_payment_source();
		$_SESSION['state'] 			= $this->app_config_model->load_sys_state();		
		$_SESSION['tax_type']		= $this->app_config_model->load_sys_tax_type();
		$_SESSION['building'] 		= $this->app_config_model->load_building();
	}
	
	/*
	 * 
	 * moved to model
	 * 
	 * */
	
	//~ function load_config_bk()
	//~ {
		//~ $query_str = "SELECT sc.* FROM sys_config sc ";
		//~ $query = $this->db->query($query_str);		
		//~ 
		//~ foreach ( $query->result_array() as $row ) {
			//~ $this->app_config[$row['key']] = $row['val'];
		//~ }
		//~ $_SESSION['config'] = $this->app_config;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_account_status ORDER BY status_code';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->acc_status[$val['status_code']] = $val['name'];
		//~ }
		//~ $_SESSION['acc_status'] = $this->acc_status;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_bill_type ORDER BY bill_type_id';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->bill_type[$val['bill_type_id']] = $val['name'];
		//~ }
		//~ $_SESSION['bill_type'] = $this->bill_type;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_customer_category ORDER BY category_code';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->cust_category[$val['category_code']] = $val['name'];
		//~ }
		//~ $_SESSION['cust_category'] = $this->cust_category;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_marital_status ORDER BY marital_status_code';		
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->marital_status[$val['marital_status_code']] = $val['name'];
		//~ }
		//~ $_SESSION['marital_status'] = $this->marital_status;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_payment_source ORDER BY payment_source_id';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->payment_source[$val['payment_source_id']] = $val['name'];
		//~ }
		//~ $_SESSION['payment_source'] = $this->payment_source;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_state ORDER BY state_code';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->state[$val['state_code']] = $val['name'];
		//~ }
		//~ $_SESSION['state'] = $this->state;
		//~ 
		//~ $query_str = 'SELECT * FROM sys_tax_type ORDER BY code';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->state[$val['code']] = $val['percent'];
		//~ }
		//~ $_SESSION['tax_type'] = $this->tax_type;		
		//~ 
		//~ $query_str = 'SELECT * FROM building ORDER BY building_no';
		//~ $query = $this->db->query($query_str);
		//~ foreach ($query->result_array() as $val) {
			//~ $this->building[$val['building_no']] = $val['name'];
		//~ }
		//~ $_SESSION['building'] = $this->building;
	//~ }

}

