<?php
class Router_model extends MY_Model{

	protected $_table = 'router';
	protected $_primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	function get_routers() {
		$query_str	= "SELECT r.* 
						FROM router r";
		
		$query 		= $this->db->query($query_str);
		return $query->result_array();
	}
	
	function get_router_list($txt_search='',$page_item_no)
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$query_str 					= "SELECT count(r.id) AS total_row FROM router r WHERE r.name LIKE '%$txt_search%' ";
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT r.* 
						FROM router r 
						WHERE r.name LIKE '%$txt_search%' 
						ORDER BY r.name 
						LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}
	
	function get_router($id='')
	{
		$query = $this->db->query("SELECT r.*
							FROM router r 
							WHERE id='".$this->db->escape_str($id)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['id'] = '';
			$return_val['name'] = '';
			$return_val['ip'] = '';		
			$return_val['ssh_port'] = '';
			$return_val['login_id'] = '';
			$return_val['login_password'] = '';
			$return_val['login_key'] = '';
			$return_val['type'] = 0;
			$return_val['description'] = '';
			$return_val['ssh_enabled'] = '1';
			$return_val['pppoe'] = '0';
			
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}	
	
	function router_insert($ip,$ssh_port,$name,$description,$login_id,$login_password,$login_key,$type,$ssh_enabled,$pppoe,$user_id)
	{								
		if (empty($pppoe)) {
			$pppoe = 0;
		}

		if (empty($ssh_enabled)) {
			$ssh_enabled = 0;
		}
		
		$query_str = "INSERT INTO router (id, ip, ssh_port, name, description, login_id, login_password, login_key, type, ssh_enabled, pppoe,
					created_by,updated_by) VALUES (".
					"'" . 0 . "', ".
					"'" . $ip. "', ".
					"'" . $ssh_port. "', ".
					"'" . $name . "', ".
					"'" . $description . "', ".
					"'" . $login_id . "', ".
					"'" . $login_password . "', ".
					"'" . $login_key . "', ".
					"'" . $type . "', ".
					"'" . $ssh_enabled . "', ".
					"'" . $pppoe . "', ".
					"'" . $user_id . "', ".
					"'" . $user_id . "' )";	
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new router has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
	}
	
	function router_update($ip,$ssh_port,$name,$description,$login_id,$login_password,$login_key,$type,$ssh_enabled,$pppoe,$user_id,$id)
	{

			$change_text = '';

			$routerInfo = $this->get_router( $id );

			if (!empty($routerInfo)) {

				if ($routerInfo['name'] != $ip) {
					$ip_text = compare_field_change('IP', $routerInfo['ip'], $ip, [], true, $this->db);

					$change_text .= $ip_text;
				}

				if ($routerInfo['ssh_port'] != $ssh_port) {
					$ssh_port_text = compare_field_change('SSH Port', $routerInfo['ssh_port'], $ssh_port, [], true, $this->db);

					$change_text .= $ssh_port_text;
				}

				if ($routerInfo['name'] != $name) {
					$name_text = compare_field_change('Name', $routerInfo['name'], $name, [], true, $this->db);

					$change_text .= $name_text;
				}

				if ($routerInfo['description'] != $description) {
					$description_text = compare_field_change('Description', $routerInfo['description'], $description, [], true, $this->db);

					$change_text .= $description_text;
				}

				if ($routerInfo['login_id'] != $login_id) {
					$login_id_text = compare_field_change('Login ID', $routerInfo['login_id'], $login_id, [], true, $this->db);

					$change_text .= $login_id_text;
				}

				if ($routerInfo['login_password'] != $login_password) {
					$login_password_text = compare_field_change('Login Password', $routerInfo['login_password'], $login_password, [], true, $this->db);

					$change_text .= $login_password_text;
				}

			}

			if (empty($pppoe)) {
				$pppoe = 0;
			}

			if (empty($ssh_enabled)) {
				$ssh_enabled = 0;
			}

			$query_str = "UPDATE router SET " .
						"ip = '" . $ip . "', " .
						"ssh_port = '" . $ssh_port . "', " .
						"name = '" . $name . "', " .
						"description = '" . $description . "', " .
						"login_id = '" . $login_id . "', " .
						"login_password = '" . $login_password . "', " .
						"login_key = '" . $login_key . "', " .
						"type = '" . $type . "', " .
						"ssh_enabled = '" . $ssh_enabled . "', " .
						"pppoe = '" . $pppoe . "', " .
						"updated_by = '" . $user_id . "' " .
						"WHERE id = ' " . $id . "' ";
			
			$model			= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 				
			$action_desc	= 'a router ('.$name.') has been updated. '.$change_text;	
			$action_category = 'update';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
			
			$this->db->query($query_str);
	}
	
	function router_delete($id='',$name='')
	{
		$query_str = "DELETE FROM router WHERE id=".$id;
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= "a router [$name] ($id) has been deleted.";
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}

	function get_all_router_type()
	{
		$query_str	= "SELECT rt.* FROM router_type rt ORDER BY rt.name";
		
		$query 		= $this->db->query($query_str);
		return $query->result_array();
	}

	function get_router_type_list($txt_search='',$page_item_no)
	{

		$txt_search = $this->db->escape_str($txt_search);
		
		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$query_str 					= "SELECT count(rt.id) AS total_row FROM router rt WHERE rt.name LIKE '%$txt_search%' ";
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT rt.* 
						FROM router_type rt 
						WHERE rt.name LIKE '%$txt_search%' 
						ORDER BY rt.name 
						LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}

	function get_router_type($id = '') 
	{
		$query = $this->db->query("SELECT rt.* FROM router_type rt WHERE id = ? LIMIT 1",[$id]);
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['id'] = '';
			$return_val['name'] = '';
			$return_val['description'] = '';
			$return_val['configuration'] = '';
			
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function router_type_insert($name,$description,$configuration,$user_id)
	{		
		$query_str = "INSERT INTO router_type (id, `name`, `description`, `configuration`,
					created_by,updated_by) VALUES (".
					"'" . 0 . "', ".
					"'" . $name . "', ".
					"'" . $description . "', ".
					"'" . $configuration . "', ".
					"'" . $user_id . "', ".
					"'" . $user_id . "' )";	
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new router type has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
	}
	
	function router_type_update($id,$name,$description,$configuration,$user_id)
	{
		$query_str = "UPDATE router_type SET " .
					"name = '" . $name . "', " .
					"description = '" . $description . "', " .
					"configuration = '" . $configuration . "', " .
					"updated_by = '" . $user_id . "' " .
					"WHERE id = ' " . $id . "' ";
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a router type has been updated';	
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
	}

	function router_type_delete($id='', $name='')
	{
		$query_str = "DELETE FROM router_type WHERE id=".$id;
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= "a router type [$name] ($id) has been deleted.";
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}

	function get_router_params($router_id)
	{
		$query = $this->db->query("SELECT rt.configuration FROM router r LEFT JOIN router_type rt ON (r.type = rt.id) WHERE r.id = ? LIMIT 1",[$router_id]);
		$return_val = $query->row_array();

		return $return_val['configuration'];
	}
}
