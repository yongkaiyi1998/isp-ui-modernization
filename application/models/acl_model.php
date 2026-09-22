<?php

class Acl_model extends MY_Model{

	protected $_table = 'acl_role';
	protected $_primary_key = 'role_no';
	
	protected $_table_2 = 'acl_entry';
	protected $_primary_key_2 = 'idx';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	function get_acl_role_list()
	{
		$query_str	= 'SELECT * FROM acl_role ORDER BY name';
		$query		= $this->db->query($query_str);		
		$return_val = $query->result_array();		
		return $return_val;
	}
	
	function get_acl_entry()
	{
		$acl_list = array();
		$db_field = '';
		
		//~ $db_field .= 'name';		
		if(empty($db_field)) $db_field = '*';
		
		$query_str = "SELECT ".$db_field." FROM `".$this->_table_2."` ORDER BY ".$this->_primary_key_2;
		$query = $this->db->query($query_str);
		foreach ($query->result_array() as $row)
		{
			$acl_list[$row['idx']] = array(
										'name'=>$row['name'],
										'display_name'=>$row['display_name'],
										'A'=>0,
										'V'=>0,
										'M'=>0,
										'D'=>0,
										'O'=>0,
										);
		}
		return $acl_list;
	}
	
	function get_acl($role_no='')
	{
		$acl_list = $this->get_acl_entry();
		$db_field = '';
		
		$db_field .= 'role_no,';
		$db_field .= 'name as role_name,';
		$db_field .= 'acl_list';
		
		if(empty($db_field)) $db_field = '*';
		
		$query_str = ' SELECT '.$db_field.' FROM ';
		$query_str .= $this->_table;
		$query_str .= ' WHERE role_no="'.$this->db->escape_str($role_no).'" ';
		$query_str .= ' LIMIT 1 ';
		$query = $this->db->query($query_str);
							
		if ($query->num_rows() > 0)	
		{
			$return_val = $query->row_array();
			
			$role_acl_list = explode(',', $return_val['acl_list']);
			$role_acl_list = array_slice($role_acl_list, 1);

			foreach ($role_acl_list as $row) {
				$val = explode(':', $row);
				for ($i=1;$i<count($val);$i++)
				{
					$acl_list[$val[0]][$val[$i]] = 1;
				}
			}

			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['role_no'] = '';
			$return_val['role_name'] = '';
			
			$return_val['btn_delete']='disabled';
		}
		$return_val['acl_list'] = $acl_list;
		
		
		return $return_val;
	}

	function acl_delete($role_no)
	{		
		$query_str 			= "DELETE FROM acl_role WHERE role_no=".$role_no;
		$model				= 'action_log_model';		
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= ' ACL Role # :'.$role_no .' has been deleted.';
		$action_category 	= 'delete';		
		$action_log 		=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);			
		$this->db->query($query_str);			
	}
	
	function acl_update($role_name,$acl_str,$username,$role_no)
	{
		$query_str = "UPDATE acl_role SET " .
					"name = '" . $role_name . "', " .
					"acl_list = '" . $acl_str . "', " .
					"modified_by = '" . $username . "', " .
					"modified_date = now() " .
					"WHERE role_no = ' " . $role_no . "' ";
					
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'ACL of '.$role_no .' role has been updated.';
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
	}
	
	function acl_insert($role_name,$acl_str,$username)
	{
		$query_str = "INSERT INTO acl_role (name, acl_list, 
					created_by, created_date) VALUES (".
					"'" . $role_name . "', ".
					"'" . $acl_str . "', ".
					"'" . $username . "', now() )";
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'New ACL Role Named "'.$role_name .'" has been added.';
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
					
		$this->db->query($query_str);
	}

}
