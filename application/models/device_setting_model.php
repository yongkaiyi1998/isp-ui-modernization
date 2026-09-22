<?php
class Device_setting_model extends MY_Model{

	protected $_table 			= 'devices';
	protected $_primary_key 	= 'dev_number';

	public function __construct()
	{
		parent::__construct();
	}

	function get_all_device($txt_search, $status, $page_item_no)
	{
		$qwhere = "";
		if( $txt_search != "" )
			$qwhere .= " AND ( d.dev_number LIKE '%$txt_search%')";

		if( $status != "" )
			if($status == 0)
				$qwhere .= " AND d.dev_active IN(1,2)";
			else
				$qwhere .= " AND d.dev_active IN(".$status.")";

		$query_str = "SELECT count(d.dev_number) AS total_row
					FROM devices d WHERE 1 = 1 ".$qwhere;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query_str = "SELECT d.dev_number, d.dev_active, d.dev_lastcheckin,
					d.dev_pending, d.dev_sent, d.dev_failed, d.dev_received
					FROM devices d WHERE 1 = 1 ".$qwhere.
					" ORDER BY d.dev_active ASC, d.dev_number ASC" .
					" LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		$query = $this->db->query($query_str);

		$result = array();
		if($query->num_rows() > 0) {
			$result = $query->result_array();
			foreach($result as $r_key => $r_val){
				$result[$r_key]['dev_lastcheckin'] = datetime_toggle($result[$r_key]['dev_lastcheckin'],$_SESSION['config']['datetime_format']);
			}
			$return_val['row'] = $result;
		}

		$return_val['row'] = $result;
		return $return_val;

	}

	function get_device_by_number($dev_number='')
	{
		$query = $this->db->query("SELECT d.*
							FROM devices d WHERE d.dev_number='".$this->db->escape_str($dev_number)."' LIMIT 1");

		if ($query->num_rows() > 0)
		{
			$return_val = $query->row_array();
			$return_val['btn_delete']='enabled';
			$return_val['btn_reset']='enabled';
		}
		else
		{
			$return_val['dev_number'] = '';
			$return_val['status'] = 1;
			$return_val['dev_lastcheckin'] = '';
			$return_val['dev_pending'] = '';
			$return_val['dev_sent'] = '';
			$return_val['dev_failed'] = '';
			$return_val['dev_received'] = '';
			$return_val['dev_active'] = 1;
			$return_val['btn_delete']='disabled';
			$return_val['btn_reset']='disabled';
		}

		return $return_val;
	}

	function check_duplicate_device_number($dev_number='')
	{
		$query = $this->db->query("SELECT d.*
					FROM devices d WHERE d.dev_number='".$this->db->escape_str($dev_number)."' LIMIT 1");
		return $query->num_rows();
	}

	function add_device($dev_number, $status)
	{
		$query_str = "INSERT INTO devices (dev_number, dev_active) VALUES ('".$dev_number."',".$status.")";

		$model				= 'action_log_model';
		$this->load->model($model);
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);
		$method 			= $this->router->method;
		$action_desc		= 'a new device has been added';
		$action_category 	= 'insert';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		$query = $this->db->query($query_str);
	}

	function update_device($post_back, $ori_dev_number)
	{
		$query_str = "UPDATE devices SET
						dev_number = '".$post_back['dev_number']."',
						dev_active = ".$post_back['status'].",
						dev_pending = ".$post_back['dev_pending'].",
						dev_sent = ".$post_back['dev_sent'].",
						dev_failed = ".$post_back['dev_failed'].",
						dev_received = ".$post_back['dev_received']."
						WHERE dev_number = '".$ori_dev_number."'";
		echo $query_str;
		$model				= 'action_log_model';
		$this->load->model($model);
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);
		$method 			= $this->router->method;
		$action_desc		= 'update device status';
		$action_category 	= 'update';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		$query = $this->db->query($query_str);
	}

	function delete_device($dev_number)
	{
		$query_str = "DELETE FROM devices WHERE dev_number = '".$dev_number."'";

		$model				= 'action_log_model';
		$this->load->model($model);
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);
		$method 			= $this->router->method;
		$action_desc		= 'a device status has been deleted';
		$action_category 	= 'delete';
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		$query = $this->db->query($query_str);
	}

	function check_device_outgoing($dev_number)
	{
		$query = $this->db->query("SELECT s.*
					FROM sms_outgoing s WHERE s.sms_transport='".$this->db->escape_str($dev_number)."' LIMIT 1");
		return $query->num_rows();
	}


}
