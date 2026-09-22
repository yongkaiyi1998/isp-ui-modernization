<?php
class Change_package_model extends MY_Model{

	protected $_table = 'package_change_request';
	protected $_primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	function get_cp_request_list($txt_search='', $page_item_no) {

		$txt_search = $this->db->escape_str($txt_search);
		
		$return_val['total_row'] = '0';
		$return_val['row'] = array();

		$query_str	= "SELECT count(pcr.id) AS total_row FROM package_change_request pcr";
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] 	= $query->row(0)->total_row;

		$query_str	= "SELECT pcr.*, p.*, oldpkg.name AS old_pkg_name, newpkg.name AS new_pkg_name,
						CASE
						WHEN pcr.status = 1 THEN 'IN PROGRESS'
						WHEN pcr.status = 2 THEN 'COMPLETED'
						ELSE 'PENDING' END AS status_name,
						CASE
						WHEN acc_type = 'b' THEN 'BUSINESS'
						ELSE 'RESIDENTIAL' END AS acc_type
						FROM package_change_request pcr
						LEFT JOIN `profile` p ON (pcr.acc_id = p.acc_id)
						LEFT JOIN `package` oldpkg ON (pcr.old_package_id = oldpkg.package_no)
						LEFT JOIN `package` newpkg ON (pcr.new_package_id = newpkg.package_no)
						WHERE p.acc_name LIKE '%$txt_search%'
						ORDER BY pcr.created_at 
						LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		
		$query 		= $this->db->query($query_str);
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}
		
		return $return_val;	
	}

	function view_cp_request($id='')
	{
		$query = $this->db->query("SELECT pcr.*, p.*,
									CASE
									WHEN acc_type = 'b' THEN 'Business'
									ELSE 'Residential' END AS acc_type
									FROM package_change_request pcr
									LEFT JOIN `profile` p ON (pcr.acc_id = p.acc_id)
									WHERE pcr.id = ?
									LIMIT 1",[$id]);

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();

			$old_pkg_sql = "SELECT c.*, p.*, 
							CASE
								WHEN p.category = 'b' THEN 'Business'
								ELSE 'Residential' END AS category
							FROM customer c
							LEFT JOIN package p ON (c.package = p.package_no)
							WHERE c.profile_id = ? AND c.package = ?";
			
			$pkg_sql = "SELECT *,
						CASE
							WHEN category = 'b' THEN 'Business'
							ELSE 'Residential' END AS category
						FROM package WHERE package_no = ?";

			$query_old_pkg = $this->db->query($old_pkg_sql,[$return_val['acc_id'], $return_val['old_package_id']]);
			$return_val['old_pkg'] = $query_old_pkg->row_array();

			$query_new_pkg = $this->db->query($pkg_sql,[$return_val['new_package_id']]);
			$return_val['new_pkg'] = $query_new_pkg->row_array();
		}
		return $return_val ?? [];
	}

	function update_cp_status($id='',$status=0)
	{
		$query = $this->db->query("UPDATE package_change_request SET status = ? WHERE id = ?",[$status, $id]);
	}
}
