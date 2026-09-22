<?php
class Action_log_model extends MY_Model{

	protected $_table		= 'action_log';
	protected $_primary_key = 'action_log_id';

	public function __construct()
	{
		parent::__construct();
	}

	function save_action($controller_name,$funcion_name,$query_str, $action_desc , $action_category='others',$customer_no = '')
    {
		$return_val				= 0;
		$query_insert_header	= '';

		$sess = $this->session->userdata;

		$this->load->helper('ip_helper');
        $ip = get_client_ip();
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'],0,100);
        $os = getOS();

		if((!empty($controller_name)) && (!empty($funcion_name)) && (!empty($sess['user']['username'])))
		{
			$data['controller_name']	= (empty($controller_name))?'':$controller_name;
			$data['funcion_name']		= (empty($funcion_name))?'':$funcion_name;
			$data['query_str']			= (empty($query_str))?'':$query_str;
			$data['action_category']	= (empty($action_category))?'others':$action_category;
			$data['customer_no']		= (empty($customer_no))?0:$customer_no;
			$data['action_desc']		= (empty($action_desc))?'':$this->db->escape_str(stripslashes($action_desc));
			$data['action_by'] 			= $sess['user']['username'];
			$data['date_modified'] 		= date("Y-m-d H:i:s");

			$query_insert_header .= "(" .
								"'" . $data['controller_name'] . "', " .
								"'" . $data['funcion_name'] . "', " .
								"'" . $data['action_category'] . "', " .
								"'" . $data['query_str'] . "', " .
								"'" . $data['action_by'] . "', " .
								"'" . $data['action_desc'] . "', " .
								"'" . $data['customer_no'] . "', " .
								"'" . $ip . "', " .
								"'" . $user_agent . "', " .
								"'" . $os . "', " .
								"'" . $data['date_modified'] . "' " .
								")";

			$query_str = "INSERT INTO ".$this->_table." (controller_name, ".
					" function_name, ".
					" action_category, ".
					" query_str, ".
					" action_by, ".
					" action_desc, ".
					" customer_no, ".
					" ip, ".
					" browser, ".
					" os, ".
					" date_modified) VALUES " .
					$query_insert_header;

			$query = $this->db->query($query_str);


			if (!empty($query))	{
				$return_val = 1; //$return_val = $data;
			}
		}

		return $return_val;
	}

	function get_action_log($description ='',$page_item_no='', $query_where='')
	{
		$return_val['data'] = array();
		$return_val['num_rows'] = '0';
		$select_a_field =" a.action_log_id, ";
		//~ $select_a_field .=" a.controller_name	, ";
		//~ $select_a_field .=" a.function_name, ";
		//~ $select_a_field .=" a.action_category	, ";
		$select_a_field .=" a.customer_no, ";
		$select_a_field .=" a.action_desc, ";
		//~ $select_a_field .=" a.query_str, ";
		$select_a_field .=" a.action_by, ";
		$select_a_field .=" a.date_modified ";

		$query_str= " SELECT SQL_CALC_FOUND_ROWS " . $select_a_field .
					" FROM ".$this->_table." a " .
					" WHERE (a.action_desc LIKE '%$description%' ) " .
					" $query_where ".
					" ORDER BY a.date_modified DESC ".
					" LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];

		$query 		= $this->db->query($query_str);
		$get_count	= $this->db->query('SELECT FOUND_ROWS() AS `Count`');

		if ($query->num_rows() > 0)	{
			$result = $query->result_array();
			foreach($result as $r_key => $r_val) {
				$result[$r_key]['date_modified'] = datetime_toggle($result[$r_key]['date_modified'],$_SESSION['config']['datetime_format']);
			}
			$return_val['data'] 	= $result;
			$return_val['num_rows']	= $get_count->row()->Count;
		}
		return $return_val;
	}

}
