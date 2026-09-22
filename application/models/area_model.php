<?php
class Area_model extends MY_Model{

	protected $_table = 'area';
	protected $_primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	function get_areas() {
		$query_str	= "SELECT a.* 
						FROM area a";
		
		$query 		= $this->db->query($query_str);
		return $query->result_array();
	}
	
	function get_area_list($txt_search='',$page_item_no)
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$query_str 					= "SELECT count(a.id) AS total_row FROM area a WHERE a.name LIKE '%$txt_search%' ";
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT a.id, a.name, a.postcode, a.state
						FROM area a 
						WHERE a.name LIKE '%$txt_search%' 
						ORDER BY a.name 
						LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}
	
	function get_area($id='')
	{
		$query = $this->db->query("SELECT a.*
							FROM area a 
							WHERE id='".$this->db->escape_str($id)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['id'] = 0;
			$return_val['name'] = '';
			$return_val['addr_1'] = '';
			$return_val['addr_2'] = '';
			$return_val['addr_3'] = '';
			$return_val['city'] = '';
			$return_val['state'] = '';
			$return_val['postcode'] = '';
			
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}	
	
	function area_insert($name,$addr_1,$addr_2,$addr_3,$city,$state,$postcode,$user_id)
	{									
		$query_str = "INSERT INTO area (id, name, addr_1, addr_2, addr_3, city, state, postcode,
					created_by) VALUES (".
					"0, ".
					"'" . $name. "', ".
					"'" . $addr_1 . "', ".
					"'" . $addr_2 . "', ".
					"'" . $addr_3 . "', ".
					"'" . $city . "', ".
					"'" . $state . "', ".
					"'" . $postcode . "', ".
					"'" . $user_id . "')";												
		
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new area has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
	}
	
	function area_update($name,$addr_1,$addr_2,$addr_3,$city,$state,$postcode,$user_id,$id)
	{

			$change_text = '';

			$areaInfo = $this->get_area( $id );

			if (!empty($areaInfo)) {

				$state_arr = array();
				$state_list = $this->common_model->get_state_list();
				foreach ($state_list as $state_rec) {
					$state_arr[$state_rec['state_code']] = $state_rec['name'];
				}

				if ($areaInfo['name'] != $name) {
					$name_text = compare_field_change('Name', $areaInfo['name'], $name, [], true, $this->db);

					$change_text .= $name_text;
				}
				
				if ($areaInfo['addr_1'] != $addr_1) {
					$addr_1_text = compare_field_change('Address Line 1', $areaInfo['addr_1'], $addr_1, [], true, $this->db);

					$change_text .= $addr_1_text;
				}

				if ($areaInfo['addr_2'] != $addr_2) {
					$addr_2_text = compare_field_change('Address Line 2', $areaInfo['addr_2'], $addr_2, [], true, $this->db);

					$change_text .= $addr_2_text;
				}

				if ($areaInfo['addr_3'] != $addr_3) {
					$addr_3_text = compare_field_change('Address Line 3', $areaInfo['addr_3'], $addr_3, [], true, $this->db);

					$change_text .= $addr_3_text;
				}

				if ($areaInfo['city'] != $city) {
					$city_text = compare_field_change('City', $areaInfo['city'], $city, [], true, $this->db);
					$change_text .= $city_text;
				}

				try { 
					if ($areaInfo['state'] != $state) {
						$state_text = compare_field_change('State', $areaInfo['state'], $state, $state_arr, true, $this->db);

						$change_text .= $state_text;
					}
				} catch (Exception $e) {
					log_message('error', 'Area Edit: '.$e->getMessage());
				}

				if ($areaInfo['postcode'] != $postcode) {
					$postcode_text = compare_field_change('Postcode', $areaInfo['postcode'], $postcode, [], true, $this->db);

					$change_text .= $postcode_text;
				}

			}

			$query_str = "UPDATE area SET " .
						"name = '" . $name . "', " .
						"addr_1 = '" . $addr_1 . "', " .
						"addr_2 = '" . $addr_2 . "', " .
						"addr_3 = '" . $addr_3 . "', " .
						"city = '" . $city . "', " .
						"state = '" . $state . "', " .
						"postcode = '" . $postcode . "', " .
						"updated_by = '" . $user_id . "' " .
						"WHERE id = '" . $id . "' ";
			
			$model			= 'action_log_model';
			$this->load->model($model);			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str($query_str);	
			$method 		= $this->router->method; 				
			$action_desc	= 'an area ('.$name.') has been updated. '.$change_text;	
			$action_category = 'update';
			$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
			
			$this->db->query($query_str);
	}
	
	
	
	function area_delete($id='',$name)
	{
		$query_str = "DELETE FROM area WHERE id=".$id;
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'an area ('.$name.') has been deleted';
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}
}
