<?php

class Advertisement_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}

	function get_advertisement_listing($txt_search,$date_from,$date_to,$page_item_no,$max_page_item_no,$query_where)
	{		
		$return_val['total_row'] = 0;		

		$txt_search = $this->db->escape_str($txt_search);
		$qdate = '';
		if (!empty($date_from)) {
			if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date_from)) {
				
			} else {
			    $date_from = date('Y-m-01');
			}
			$qdate .= " AND start_date >= '".$this->db->escape_str($date_from)."' ";
		}

		if (!empty($date_to)) {
			if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date_to)) {
				
			} else {
			    $date_to = date('Y-m-t');
			}
			$qdate .= " AND start_date <= '".$this->db->escape_str($date_to)."' ";
		}	

		$query_str 	= "SELECT count(a.id) AS total_row 
					FROM advertisement a 
					WHERE a.name LIKE '%$txt_search%' " .
					$query_where;
					
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$query_str 	= "SELECT a.* 
					FROM advertisement a 
					WHERE a.name LIKE '%$txt_search%' " .
					$query_where . $qdate .
					"ORDER BY a.start_date ASC  " . 
					"LIMIT $page_item_no, ". $max_page_item_no;
		$query = $this->db->query($query_str);	
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		
		return $return_val;
	}

	function get_advertisement($id='')
	{
		$query = $this->db->query("SELECT * FROM advertisement 
									WHERE id='".$this->db->escape_str($id)."' LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';

			//$radius = $this->get_radius_info($return_val['bandwidth']);

		}else{
			$return_val['id'] = '0';
			$return_val['lineno'] = '0';
			$return_val['name'] = '';
			$return_val['start_date'] = '';
			$return_val['end_date'] = '';
			$return_val['file_path'] = '';
			$return_val['link'] = '';
			$return_val['is_active'] = '1';

			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}

	function advertisement_delete($id='')
	{
		$query_str 		= "DELETE FROM advertisement WHERE id=".$id;	
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'an advertisement has been deleted';
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}

	function advertisement_insert($post_back,$username)
	{		

		if (isset($post_back['is_active'])) {
			if (empty($post_back['is_active'])) {
				$is_active = 0;
			} else {
				$is_active = $this->db->escape_str($post_back['is_active']);
			}
		} else {
			$is_active = 0;
		}						
		
		$query_str 		= "INSERT INTO advertisement (lineno, `name`, start_date, end_date, link, is_active) VALUES (".
								"'" . $this->db->escape_str($post_back['lineno']) . "', ".
								"'" . $this->db->escape_str($post_back['name']) . "', ".
								"'" . $this->db->escape_str($post_back['start_date']) . "', ".
								"'" . $this->db->escape_str($post_back['end_date']) . "', ".
								"'" . $this->db->escape_str($post_back['link']) . "', ".
								"'" . $is_active . "' )";										
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new advertisement ('.$this->db->escape_str($post_back['name']).') has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
		$id = $this->db->insert_id();
		return array('id' => $id);
	}

	public function advertisement_update($post_back,$username,$id)
	{	

		if (isset($post_back['is_active'])) {
			if (empty($post_back['is_active'])) {
				$is_active = 0;
			} else {
				$is_active = $this->db->escape_str($post_back['is_active']);
			}
		} else {
			$is_active = 0;
		}	

		$query_str = "UPDATE advertisement SET " .
					"lineno = '" . $this->db->escape_str($post_back['lineno']) . "', " .
					"`name` = '" . $this->db->escape_str($post_back['name']) . "', " .
					"start_date = '" . $this->db->escape_str($post_back['start_date']) . "', " .
					"end_date = '" . $this->db->escape_str($post_back['end_date']) . "', " .
					"link = '" . $this->db->escape_str($post_back['link']) . "', " .
					"is_active = '" . $is_active . "' " .
					"WHERE id = '" . $this->db->escape_str($post_back['id']) . "' ";
		
		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);
		$method 		= $this->router->method;
		$action_desc	= 'an advertisement('.$this->db->escape_str($post_back['name']).') has been edited.';
		$action_category = 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
		
		$this->db->query($query_str);

		$id = $this->db->escape_str($id);

		return $id;
	}

	public function update_file_path($id, $file_path)
	{
		$this->db->query("UPDATE advertisement SET file_path = ? WHERE id = ? ", array($file_path, $id));
	}

}