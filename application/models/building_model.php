<?php
class Building_model extends MY_Model{

	protected $_table = 'building';
	protected $_primary_key = 'building_no';

	public function __construct()
	{
		parent::__construct();
	}
	
	function get_building_list($txt_search='',$page_item_no)
	{

		$txt_search = $this->db->escape_str($txt_search);
		
		$return_val['total_row'] = '0';
		$return_val['row'] = array();
		
		$query_str 					= "SELECT count(b.building_no) AS total_row FROM building b WHERE b.name LIKE '%$txt_search%' ";
		$query 						= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT b.building_no, b.name, b.total_unit 
						FROM building b 
						WHERE b.name LIKE '%$txt_search%' 
						ORDER BY b.name 
						LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;		
	}
	
	function get_building($building_no='')
	{
		$query = $this->db->query("SELECT b.*, ss.einvoice_code 
							FROM building b 
							LEFT JOIN `sys_state` ss ON (ss.state_code = b.`state`) 
							WHERE building_no='".$this->db->escape_str($building_no)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
			
			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['building_no'] = '';
			$return_val['name'] = '';
			$return_val['total_unit'] = '';
			$return_val['pppoe'] = '0';
			$return_val['private'] = '0';
			$return_val['dealer'] = '';
			$return_val['router_id'] = '';
			$return_val['addr_1'] = '';
			$return_val['addr_2'] = '';
			$return_val['addr_3'] = '';
			$return_val['city'] = '';
			$return_val['state'] = '';
			$return_val['postcode'] = '';
			$return_val['area_id'] = 0;
			$return_val['status'] = 'a';

			$return_val['einvoice_code'] = '';

			
			$return_val['btn_delete']='disabled';
		}
		
		return $return_val;
	}
	
	function get_building_total_users( $building_no, $qwhere = "" ){
		$return_val = array();
		$sql = " SELECT count( c.customer_no ) AS total_customer , cs.status AS latest_status 
				 FROM customer c 
				LEFT JOIN (
					SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
				) cs ON (cs.customer_no = c.customer_no) 
				 WHERE cs.status IS NOT NULL AND c.building = '".$building_no."' $qwhere 
				 GROUP BY cs.status ";
		
		$query = $this->db->query($sql);
		$return_val = $query->result_array();
		return $return_val ;
	}
	
	
	function building_insert($name,$total_unit,$pppoe,$private,$dealer,$router_id,$addr_1,$addr_2,$addr_3,$city,$state,$postcode,$area_id,$status,$username)
	{								
		$query_str 		= "SELECT MAX(b.building_no) as building_no FROM building b";
		$query 			= $this->db->query($query_str);
		$building_no 	= $query->row()->building_no + 1;

		if (empty($pppoe)) {
			$pppoe = 0;
		}

		if (empty($dealer)) {
			$dealer = 0;
		}

		if (empty($router_id)) {
			$router_id = 0;
		}
		
		if (empty($total_unit)) {
			$total_unit = 0;
		}

		if (empty($private)) {
			$private = 0;
		}

		if (empty($status)) {
			$status = 'a';
		}
		
		$query_str = "INSERT INTO building (building_no, name, total_unit, pppoe, dealer, router_id, addr_1, addr_2, addr_3, city, state, postcode, area_id, private, status,
					created_by, created_date) VALUES (".
					"'" . $building_no . "', ".
					"'" . $name. "', ".
					"'" . $total_unit. "', ".
					"'" . $pppoe . "', ".
					"'" . $dealer . "', ".
					"'" . $router_id . "', ".
					"'" . $addr_1 . "', ".
					"'" . $addr_2 . "', ".
					"'" . $addr_3 . "', ".
					"'" . $city . "', ".
					"'" . $state . "', ".
					"'" . $postcode . "', ".
					"'" . $area_id . "', ".
					"'" . $private . "', ".
					"'" . $status . "', ".
					"'" . $username . "', now() )";												

					$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a new building/area has been added';	
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
		
		$this->db->query($query_str);
	}
	
	function building_update(array $newInfo, $username)
	{
			$this->load->helper('change_log');
			
			$newInfo['pppoe']     = !empty($newInfo['pppoe']) ? $newInfo['pppoe'] : 0;
			$newInfo['dealer']    = !empty($newInfo['dealer']) ? $newInfo['dealer'] : 0;
			$newInfo['router_id'] = !empty($newInfo['router_id']) ? $newInfo['router_id'] : 0;
			$newInfo['total_unit'] = !empty($newInfo['total_unit']) ? $newInfo['total_unit'] : 0;
			$newInfo['private']   = !empty($newInfo['private']) ? $newInfo['private'] : 0;
			$newInfo['status']    = !empty($newInfo['status']) ? $newInfo['status'] : 'a';

			$buildingInfo = $this->get_building($newInfo['building_no']);

			$change_text = '';

			if (!empty($buildingInfo)) {

				$dealer_arr = array_column($this->common_model->get_dealer_list() ?? [], 'name', 'dealer_no');
				$state_arr  = array_column($this->common_model->get_state_list() ?? [], 'name', 'state_code');
				$area_arr   = array_column($this->common_model->get_area_list() ?? [], 'name', 'id');

				$change_text .= compare_field_change('Name', $buildingInfo['name'], $newInfo['name'], [], true, $this->db);
				$change_text .= compare_field_change('Total Units', $buildingInfo['total_unit'], $newInfo['total_unit'], [], true, $this->db);
				$change_text .= compare_field_change('PPPoE', $buildingInfo['pppoe'], $newInfo['pppoe'], [], true, $this->db);
				$change_text .= compare_field_change('Private', $buildingInfo['private'], $newInfo['private'], [], true, $this->db);
				$change_text .= compare_field_change('Agent', $buildingInfo['dealer'], $newInfo['dealer'], $dealer_arr, true, $this->db);
				$change_text .= compare_field_change('Router', $buildingInfo['router_id'], $newInfo['router_id'], [], true, $this->db);
				$change_text .= compare_field_change('Address Line 1', $buildingInfo['addr_1'], $newInfo['addr_1'], [], true, $this->db);
				$change_text .= compare_field_change('Address Line 2', $buildingInfo['addr_2'], $newInfo['addr_2'], [], true, $this->db);
				$change_text .= compare_field_change('Address Line 3', $buildingInfo['addr_3'], $newInfo['addr_3'], [], true, $this->db);
				$change_text .= compare_field_change('City', $buildingInfo['city'], $newInfo['city'], [], true, $this->db);
				$change_text .= compare_field_change('State', $buildingInfo['state'], $newInfo['state'], $state_arr, true, $this->db);
				$change_text .= compare_field_change('Postcode', $buildingInfo['postcode'], $newInfo['postcode'], [], true, $this->db);
				$change_text .= compare_field_change('Area', $buildingInfo['area_id'], $newInfo['area_id'], $area_arr, true, $this->db);
				$change_text .= compare_field_change('Status', $buildingInfo['status'], $newInfo['status'], [], true, $this->db);
			}

			$data = [
				'name'          => $newInfo['name'],
				'total_unit'    => $newInfo['total_unit'],
				'pppoe'         => $newInfo['pppoe'],
				'dealer'        => $newInfo['dealer'],
				'router_id'     => $newInfo['router_id'],
				'addr_1'        => $newInfo['addr_1'],
				'addr_2'        => $newInfo['addr_2'],
				'addr_3'        => $newInfo['addr_3'],
				'city'          => $newInfo['city'],
				'state'         => $newInfo['state'],
				'postcode'      => $newInfo['postcode'],
				'area_id'       => $newInfo['area_id'],
				'private'       => $newInfo['private'],
				'status'        => $newInfo['status'],
				'modified_by'   => $username,
				'modified_date' => date('Y-m-d H:i:s')
			];

			$this->db->where('building_no', $newInfo['building_no']);
			$this->db->update('building', $data);

			$this->load->model('action_log_model');
			$ctrl  = $this->router->fetch_class();
			$method = $this->router->method;
			$esc_query_str = $this->db->escape_str($this->db->last_query());
			$action_desc = 'A building/area (' . $newInfo['name'] . ') has been updated. ' . $change_text;
			$action_category = 'update';
			$this->action_log_model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category);
	}
	
	function building_delete($building_no='')
	{
		$query_str = "DELETE FROM building WHERE building_no=".$building_no;
			
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'a building/area has been deleted';
		$action_category = 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		
		$this->db->query($query_str);
	}
	
	function get_building_no_by_areas_id($areas_id)
	{
		$query_str = "SELECT b.building_no FROM building b WHERE b.area_id IN ($areas_id)";
		$query = $this->db->query($query_str);

		$building = $query->result_array() ?? [];
		return $building;
	}
}
