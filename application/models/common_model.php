<?php

class Common_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
    function get_segment()
	{		
		$seg = 1;
		$segment = array();
		while ($seg != 0)
		{							
			$seg_info 	= '';
				
			$segment[] = $seg_info = $this->uri->segment($seg);
							
			if($seg_info != ''){
				$seg++;	
			}
			else
			{	
				$seg = 0;
				return $segment;
			}				
		}
	}
    
	
	function get_table($table_name='',$select ='*',$and_where='', $order_by='' , $limit='')
	{
		$data = '';	
		if($table_name !=''){	
			$this->load->database();		
			$this->db->select($select);		
			$this->db->from($table_name);
			
			if($and_where != '') 	$this->db->where($and_where);
			if($order_by != '') 	$this->db->order_by($order_by); 
			if($limit != '') 		$this->db->order_by($limit); 
			
			$data = $this->db->get()->result_array();	
		}
		return $data;
	}
	
	function get_bill_type_list($is_debit_only = false)
	{
		$query_where = '';
		if ($is_debit_only) {
			$query_where = "WHERE is_debit = '1' ";
		}
		
		$query = $this->db->query('SELECT * FROM sys_bill_type ' . $query_where . 'ORDER BY CASE bill_type_id 
        WHEN "1" THEN 1 ELSE 999 END ASC, `name` ASC');
		return $query->result_array();
	}
	
	function get_payment_source_list($active_only=1)
	{
		if( $active_only == 1 )
			$qwhere = " WHERE `status` = 1 ";
		else
			$qwhere = "";
		
		$query = $this->db->query('SELECT * FROM sys_payment_source '.$qwhere.' ORDER BY name');
		return $query->result_array();
	}

	function get_equipment_type_list($active_only=1)
	{
		if( $active_only == 1 )
			$qwhere = " WHERE `status` = 1 ";
		else
			$qwhere = "";
		
		$query = $this->db->query('SELECT * FROM sys_equipment_type '.$qwhere.' ORDER BY name');
		return $query->result_array();
	}
	
	function get_domain_list()
	{
		$query = $this->db->query('SELECT * FROM domain ORDER BY domain_id');
		return $query->result_array();
	}
	
	function get_state_list()
	{
		$query = $this->db->query('SELECT * FROM sys_state ORDER BY state_code');
		return $query->result_array();
	}
	
	function get_user_list()
	{
		$query = $this->db->query('SELECT * FROM user ORDER BY username');
		return $query->result_array();
	}
	
	function get_dealer_list()
	{
		$query = $this->db->query('SELECT * FROM dealer ORDER BY dealer_no');
		return $query->result_array();
	}

	function get_upline_dealer_list()
	{
		$query = $this->db->query('SELECT * FROM dealer WHERE upline = 0 ORDER BY dealer_no');
		return $query->result_array();
	}

	function get_installer_list()
	{
		$query = $this->db->query('SELECT * FROM user where `acl_role` = 6 ORDER BY idx');
		return $query->result_array();
	}

	// "module" refers to name in table acl_entry
	function get_technical_list($module = '')
	{
		$sql = '';
		$arr = [];
		if(!empty($module)) {
			$sql = "SELECT tu.*, u.* FROM technical_user tu LEFT JOIN user u ON tu.user_id = u.idx WHERE tu.doc_type = ?";
			$arr[] = $module;
		} else {
			$sql = "SELECT u.* FROM user u WHERE u.allow_tech_notification = 1";
		}
		$query = $this->db->query($sql, $arr);
		$tech_users = $query->result_array();
		return $tech_users;
	}
	
	function get_acc_status_list() 
    {
		$query = $this->db->query('SELECT * FROM sys_account_status ORDER BY status_code');
		return $query->result_array();
	}

	function get_acl_role_list()
	{
		$query = $this->db->query('SELECT * FROM acl_role ORDER BY name');
		return $query->result_array();
	}

	function get_customer_status_list() 
    {
		return [
			['status_code' => 'P', 'name' => 'Signup'],
			['status_code' => 'A', 'name' => 'Activated'],
			['status_code' => 'S', 'name' => 'Suspended'],
			['status_code' => 'T', 'name' => 'Terminated'],
			['status_code' => 'C', 'name' => 'Cancelled']
		];
	}

	function get_bill_status_list() 
    {
		return [
			['status_code' => 'D', 'name' => 'Draft'],
			['status_code' => 'A', 'name' => 'Pending 1'],
			['status_code' => 'B', 'name' => 'Pending 2'],
			['status_code' => 'C', 'name' => 'Completed'],
			['status_code' => 'V', 'name' => 'Void']
		];
	}
	
	function get_building_list($order = 'name', $building_no = '', $query_where = '')
	{
		if ($building_no != '') {
			$query_where .= " AND building_no = '".$this->db->escape_str($building_no)."' ";
		}

		$sql = "SELECT * FROM building WHERE 1=1 $query_where ORDER BY $order";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	
	function get_category_list()
	{
		$query = $this->db->query('SELECT * FROM sys_customer_category ORDER BY category_code');
		return $query->result_array();
	}

	function get_asset_category_list()
	{
		$query = $this->db->query('SELECT * FROM sys_asset_category ORDER BY category_name');
		return $query->result_array();
	}

	function get_asset_code_list()
	{
		$query = $this->db->query('SELECT * FROM sys_asset_code ORDER BY asset_code');
		return $query->result_array();
	}

	function get_asset_site_list($txt_search='')
	{
		$where = '';
		if (!empty($txt_search)) {
			$where .= ' AND site_name LIKE "%'.$txt_search.'%" ';
		}

		$query = $this->db->query('SELECT * FROM sys_asset_site WHERE 1=1 '.$where.' ORDER BY site_code');
		return $query->result_array();
	}
	
	function get_product_category_list($txt_search='')
	{
		$where = '';
		if(!empty($txt_search)) {
			$where = "WHERE product_name LIKE '%$txt_search%'";
		}
		$query = $this->db->query("SELECT * FROM sys_product_category $where ORDER BY product_name");
		return $query->result_array();
	}
	
	function get_marital_status_list()
	{
		$query = $this->db->query('SELECT * FROM sys_marital_status ORDER BY marital_status_code');
		return $query->result_array();
	}

	function get_profile_type_list()
	{
		$query = $this->db->query('SELECT * FROM sys_profile_type ORDER BY category_code DESC');
		return $query->result_array();
	}
	
	function get_default_tax_amount()
    {		
		$query_str 			= "SELECT `val` FROM `sys_config` WHERE `key` = 'default_tax'";        
		$result 			= $this->db->query($query_str)->result_array();	        
        $default_tax_code	= (empty($result[0]['val']))?'SR':$result[0]['val'];    
        
        $query_str 	= "SELECT percent FROM sys_tax_type WHERE code = '".$default_tax_code."'";        
		return $this->db->query($query_str)->result_array();	
	}
	
	function get_package_list($category = '', $status = '', $where = "")
	{
		$query_where = "";

		if ($status != '') {
			$query_where .= " AND p.status = '$status'";
		}

		if (!empty($category)) {
			$query_where .= " AND p.category = '$category'";
		}

		if (!empty($where)) {
			$query_where .= $where;
		}

		$query = $this->db->query("
			SELECT p.*
			FROM package p
			WHERE 1=1 AND deleted_date IS NULL $query_where
			ORDER BY CASE WHEN p.lineno=0 THEN 99999 ELSE p.lineno END, p.monthly_charge, p.name 
		");

		$result = $query->result_array();

		foreach($result as &$value){
			if($value['deleted_date'] != NULL){
				$value['name'] = '[DELETED] '.$value['name'];
			}
		}

		return $result;
	}
	
	function get_tax_list( $code = '' )
    {
		$query_where = "";
		if ( $code != '' ) {
			$query_where = "WHERE code ='".$this->db->escape_str($code)."' ";
		}
		$query = $this->db->query("SELECT * FROM sys_tax_type $query_where ORDER BY code");
		return $query->result_array();
	}
	
	function get_gst_reg_no()
	{		
		//get default tax code
        $query_str 			= "SELECT `val` FROM `sys_config` WHERE `key` = 'gst_reg_no'";        
		$result 			= $this->db->query($query_str)->result_array();	        
        $gst_reg_no			= (empty($result[0]['val']))?'SR':$result[0]['val'];
        
        return $gst_reg_no;
	}
	
	function get_ledger_account_code( $ref_type , $ref_id , $customer_category_code ){
		$str = "SELECT *
				FROM sys_ledger_account 
				WHERE refer_type = '".$ref_type."'
				AND refer_id = '".$ref_id."'
				AND customer_category_code = '".$customer_category_code."'; ";
				
		$query = $this->db->query($str);
		$return_val = ($query->num_rows() > 0) ? $query->row_array():array('ledger_account_code'=> '');
		return $return_val;
	}
	
	function get_ledger_account_codes( $ref_type , $ref_id , $customer_category_code , $credit_debit = ''){
		$qwhere = "";
		if( $credit_debit == 'CR' )
			$qwhere .= " AND credit_debit = 'CR' ";
		elseif( $credit_debit == 'DR' )
			$qwhere .= " AND credit_debit = 'DR' ";
		
		$str = "SELECT *
				FROM sys_ledger_account 
				WHERE refer_type = '".$ref_type."'
				AND refer_id = '".$ref_id."'
				AND customer_category_code = '".$customer_category_code."'
				$qwhere ";
				
		$query = $this->db->query($str);
		//~ $return_val = ($query->num_rows() > 0) ? $query->result_array():array('ledger_account_code'=> '');
		return $query->result_array();
	}

	function get_all_ledger_codes() {

		$return = array();
		$str = "SELECT * 
				FROM sys_ledger_account 
				";
				
		$query = $this->db->query($str);
		$rows = $query->result_array();
		foreach ($rows as $row) {
			$return[$row['refer_type']][$row['customer_category_code']][$row['refer_id']][$row['credit_debit']] = $row['ledger_account_code'];
		}

		return $return;
	}
	
	function get_tt_complaint_list(){
		$query = $this->db->query('SELECT * FROM tt_complaint;');
		return $query->result_array();
	}

	function get_tt_sof_list(){
		$query = $this->db->query('SELECT * FROM tt_sof;');
		return $query->result_array();
	}

	function get_tt_cof_list(){
		$query = $this->db->query('SELECT * FROM tt_cof;');
		return $query->result_array();
	}
	
	function get_file_attachment($link_doc, $link_doc_ref='', $sub_doc_ref='', $is_print=0, $reg_no=NULL, $so_id=NULL) {
		$return_val = array();

		$q_array = array($link_doc);

		$link_doc_ref_q = '';
		if(!empty($link_doc_ref)) {
			$link_doc_ref_q = ' AND link_doc_ref = ? ';
			$q_array[] = $link_doc_ref;
		}

		$sub_doc_ref_q = '';
		if (!empty($sub_doc_ref)) {
			$sub_doc_ref_q = ' AND sub_doc_ref = ? ';
			$q_array[] = $sub_doc_ref;
		} else {
			$sub_doc_ref_q = ' AND (sub_doc_ref = "" OR sub_doc_ref IS NULL) ';
		}

		$is_printed_q = '';
		if (!empty($is_print)) {
			$is_printed_q = ' AND is_print = 1 ';
		}

		$is_reg_no = '';
		if (!empty($reg_no)) {
			$is_reg_no = ' AND reg_no = ? ';
			$q_array[] = $reg_no;
		}

		$is_so_id = '';
		if (!empty($so_id)) {
			$is_so_id = ' AND so_id = ? ';
			$q_array[] = $so_id;
		}

		$query = $this->db->query('
			SELECT file_id, local_path, file_name, remark, created_by, is_print, 0 AS is_temp , upload_date AS created_date  
			FROM file_attachment 
			WHERE link_doc = ? '.$link_doc_ref_q.' '.$sub_doc_ref_q.' '.$is_printed_q.' '.$is_reg_no.' '.$is_so_id.' 
			ORDER BY file_id ASC', 
			$q_array
		);

		if (!empty($query->result_array())) {
			$return_val = $query->result_array();
		}

		return $return_val;
	}

	function get_file_attachment_with_signature_form_info($link_doc, $link_doc_ref='', $sub_doc_ref='', $is_print=0, $form_type='installation form', $reg_no=NULL, $so_id=NULL) {

		$q_array = array($form_type, $link_doc);

		$link_doc_ref_q = '';
		if(!empty($link_doc_ref)) {
			$link_doc_ref_q = ' AND fa.link_doc_ref = ? ';
			$q_array[] = $link_doc_ref;
		}

		$sub_doc_ref_q = '';
		if (!empty($sub_doc_ref)) {
			$sub_doc_ref_q = ' AND fa.sub_doc_ref = ? ';
			$q_array[] = $sub_doc_ref;
		} else {
			$sub_doc_ref_q = ' AND (fa.sub_doc_ref = "" OR fa.sub_doc_ref IS NULL) ';
		}

		$is_printed_q = '';
		if (!empty($is_print)) {
			$is_printed_q = ' AND fa.is_print = 1 ';
		}

		$is_reg_no = '';
		if (!empty($reg_no)) {
			$is_reg_no = ' AND fa.reg_no = ? ';
			$q_array[] = $reg_no;
		}

		$is_so_id = '';
		if (!empty($so_id)) {
			$is_so_id = ' AND fa.so_id = ? ';
			$q_array[] = $so_id;
		}
	
		$query = $this->db->query('
			SELECT 
				fa.file_id,
				fa.local_path,
				fa.file_name,
				fa.remark,
				fa.created_by,
				fa.is_print,
				0 AS is_temp,
				fa.upload_date AS created_date,

				fs.form_type,
				fs.signer_name,
				fs.signer_ic,
				fs.customer_no

			FROM file_attachment fa

			LEFT JOIN form_signatures fs
				ON fs.signature_file_id = fa.file_id

			WHERE fs.form_type = ?
			AND fa.link_doc = ? 
			'.$link_doc_ref_q.'
			'.$sub_doc_ref_q.'
			'.$is_printed_q.'
			'.$is_reg_no.'
			'.$is_so_id.'

			ORDER BY fa.file_id ASC
		', $q_array);

		return $query->result_array();
	}

	function get_temp_file_attachment($link_doc, $link_doc_ref='', $sub_doc_ref='', $is_print=0, $reg_no=NULL, $so_id=NULL) {
		$return_val = array();

		$q_array = array($link_doc);

		$link_doc_ref_q = '';
		if(!empty($link_doc_ref)) {
			$link_doc_ref_q = ' AND link_doc_ref = ? ';
			$q_array[] = $link_doc_ref;
		}

		$sub_doc_ref_q = '';
		if (!empty($sub_doc_ref)) {
			$sub_doc_ref_q = ' AND sub_doc_ref = ? ';
			$q_array[] = $sub_doc_ref;
		} else {
			$sub_doc_ref_q = ' AND (sub_doc_ref = "" OR sub_doc_ref IS NULL) ';
		}

		$is_printed_q = '';
		if (!empty($is_print)) {
			$is_printed_q = ' AND is_print = 1 ';
		}

		$is_reg_no = '';
		if (!empty($reg_no)) {
			$is_reg_no = ' AND reg_no = ? ';
			$q_array[] = $reg_no;
		}

		$is_so_id = '';
		if (!empty($so_id)) {
			$is_so_id = ' AND so_id = ? ';
			$q_array[] = $so_id;
		}

		$query = $this->db->query('
			SELECT file_id, local_path, file_name, remark, created_by, is_print, 1 AS is_temp   
			FROM temp_file_attachment 
			WHERE link_doc = ? '.$link_doc_ref_q.' '.$sub_doc_ref_q.' '.$is_printed_q.' '.$is_reg_no.' '.$is_so_id.' 
			ORDER BY file_id ASC', 
			$q_array
		);

		if (!empty($query->result_array())) {
			$return_val = $query->result_array();
		}

		return $return_val;
	}

	function insert_file_attachment( $file_name, $local_path, $link_doc, $link_doc_ref, $remark, $is_print=0, $sub_doc_ref='', $reg_no=0, $so_id=0, $created_by=0 ) {
		$query = "INSERT INTO file_attachment SET file_name = ?, upload_date = NOW(), local_path = ?, link_doc = ?, link_doc_ref = ?, remark = ?, created_by = ?, sub_doc_ref = ?, is_print = ?, reg_no = ?, so_id = ?";
		
		$query = $this->db->query($query, 
			array(
				$file_name, 
				$local_path, 
				$link_doc, 
				$link_doc_ref, 
				$remark, 
				$created_by,
				$sub_doc_ref, 
				$is_print, 
				$reg_no, 
				$so_id 
			)
		);
		
		return true;
	}

	function insert_tmp_file_attachment( $file_name, $local_path, $link_doc, $link_doc_ref, $remark, $is_print=0, $sub_doc_ref='', $reg_no=0, $so_id=0, $created_by=0 ) {
		$query = "INSERT INTO temp_file_attachment SET file_name = ?, upload_date = NOW(), local_path = ?, link_doc = ?, link_doc_ref = ?, remark = ?, created_by = ?, sub_doc_ref = ?, is_print = ?, reg_no = ?, so_id = ?";
		
		$query = $this->db->query($query, 
			array(
				$file_name, 
				$local_path, 
				$link_doc, 
				$link_doc_ref, 
				$remark, 
				$created_by,
				$sub_doc_ref, 
				$is_print, 
				$reg_no, 
				$so_id 
			)
		);
		
		return true;
	}

	function get_attachment_by_id( $file_id ){
		$queryStr = "	SELECT file_id, link_doc_ref, local_path 
						FROM `file_attachment`
						WHERE `file_id` = '".$this->db->escape_str($file_id)."' ;";

		$query = $this->db->query($queryStr);        
		if($query->num_rows($query) >= 1){
			$return_val = $query->row_array();
		 	return $return_val;
		}else{
			return array();
		}
	}

	public function get_temp_attachment_by_id( $file_id ){
		$queryStr = "	SELECT file_id, link_doc_ref, local_path 
						FROM `temp_file_attachment`
						WHERE `file_id` = '".$this->db->escape_str($file_id)."' ;";

		$query = $this->db->query($queryStr);        
		if($query->num_rows($query) >= 1){
			$return_val = $query->row_array();
		 	return $return_val;
		}else{
			return array();
		}
	}

	function remove_attachment( $file_id, $link_doc_ref, $sub_doc_ref='' ){
		$success = 0 ;

		$upload_path = $this->config->item('upload_path');

		$info = $this->get_attachment_by_id( $file_id ) ;

		$sub_doc_ref_q = '';
		if (!empty($sub_doc_ref)) {
			$sub_doc_ref_q = " AND sub_doc_ref = '".$this->db->escape_str($sub_doc_ref)."' ";
		}

		$query_str = "	DELETE FROM file_attachment 
						WHERE file_id = '".$this->db->escape_str($file_id)."' ".$sub_doc_ref_q." ; ";

		$query = $this->db->query($query_str);
		if( $this->db->affected_rows() > 0 ){
			$success = 1 ;
			if(file_exists($upload_path.$info['local_path'])){
				@unlink($upload_path.$info['local_path']); 
			}

			$file_info = pathinfo($info['local_path']);
			$thumbnail_filename = 'thumbnail_' . $file_info['filename'] . '.jpeg';
			$thumbnail_path = dirname($info['local_path']) . '/' . $thumbnail_filename;

			if(file_exists($upload_path.$thumbnail_path)){
				@unlink($upload_path.$thumbnail_path); 
			}

			//pdf
			$extension = strtolower($file_info['extension']);
			if (in_array($extension, ['pdf'])) {
				$cnt = 0;
				while($cnt<99) {
					$thumbnail_filename = $file_info['filename'] . '-'.$cnt.'.jpg';
					$thumbnail_path = dirname($info['local_path']) . '/' . $thumbnail_filename;
					if (file_exists($upload_path.$thumbnail_path)) {
						@unlink($upload_path.$thumbnail_path);
					} else {
						break;
					}
					$cnt++;
				}
			}
		}
		
		return $success;
	}

	public function remove_temp_attachment( $file_id ){
		$success = 0 ;

		$upload_path = $this->config->item('upload_path');

		$info = $this->get_temp_attachment_by_id( $file_id ) ;

		$query_str = "	DELETE FROM temp_file_attachment 
						WHERE file_id = '".$this->db->escape_str($file_id)."'";
		
		$query = $this->db->query($query_str);
		if( $this->db->affected_rows() > 0 ){
			$success = 1 ;
			if(file_exists($upload_path.$info['local_path'])){
				@unlink($upload_path.$info['local_path']); 
			}

			$file_info = pathinfo($info['local_path']);
			$thumbnail_filename = 'thumbnail_' . $file_info['filename'] . '.jpeg';
			$thumbnail_path = dirname($info['local_path']) . '/' . $thumbnail_filename;

			if(file_exists($upload_path.$thumbnail_path)){
				@unlink($upload_path.$thumbnail_path); 
			}

			//pdf
			$extension = strtolower($file_info['extension']);
			if (in_array($extension, ['pdf'])) {
				$cnt = 0;
				while($cnt<99) {
					$thumbnail_filename = $file_info['filename'] . '-'.$cnt.'.jpg';
					$thumbnail_path = dirname($info['local_path']) . '/' . $thumbnail_filename;
					if (file_exists($upload_path.$thumbnail_path)) {
						@unlink($upload_path.$thumbnail_path);
					} else {
						break;
					}
					$cnt++;
				}
			}
		}
		
		return $success;
	}

	function change_remark( $file_id, $remark ) {
		$query = $this->db->query("UPDATE file_attachment SET remark = ? WHERE file_id = ? ", array($remark, $file_id));
		return 1;
	}

	function update_so_id_to_attachment($so_id, $reg_no) {
		$this->db->query("UPDATE file_attachment SET so_id = ? WHERE reg_no = ? ", array($so_id, $reg_no));
	}

	function update_acc_id_to_attachment($acc_id, $so_id) {
		$this->db->query("UPDATE file_attachment SET link_doc_ref = ? WHERE so_id = ? ", array($acc_id, $so_id));
	}

	function get_state_code_by_einvoice_code($code) {
		$queryStr = "	SELECT *  
						FROM `sys_state`
						WHERE `einvoice_code` = ? ";

		$query = $this->db->query($queryStr, array($code));        
		if($query->num_rows($query) >= 1){
			$return_val = $query->row_array();
		 	return $return_val['state_code'];
		}else{
			return '';
		}
	}

	function insert_reminder_status_times( $data ) {

		//chk if got any rows
		$rem_obj = $this->check_reminder_sent($data['customer_no'],$data['bill_no'],$data['reminder_type']);
		if (!empty($rem_obj)) {
			//record existed, add to times and count
			$data['id'] = $rem_obj['id'];
			$data['times'] = $rem_obj['times'] + 1;
			$data['email'] = $rem_obj['email'] + 1;
			$data['whatsapp'] = $rem_obj['whatsapp'] + 1;
			$data['telegram'] = $rem_obj['telegram'] + 1;
			$this->update_reminder_status( $data );
		} else {
			$this->insert_reminder_status( $data );
		}
	}

	function insert_reminder_status( $data ) {
		$query = "INSERT INTO reminder_status SET 
		customer_no = ?, 
		bill_no = ?, 
		reminder_type = ?, 
		`times` = 1, 
		email = ?, 
		whatsapp = ?, 
		telegram = ?, 
		created_by = 0, 
		updated_by = 0 
		";
		
		$query = $this->db->query($query, 
			array(
				$data['customer_no'], 
				$data['bill_no'],
				$data['reminder_type'], 
				$data['email'], 
				$data['whatsapp'], 
				$data['telegram'] 
			)
		);
		
		return true;
	}

	function update_reminder_status( $data ) {
		$query = "UPDATE reminder_status SET 
		customer_no = ?, 
		bill_no = ?, 
		reminder_type = ?, 
		`times` = ?, 
		email = ?, 
		whatsapp = ?, 
		telegram = ?, updated_at = now() WHERE id = ? 
		";
		
		$query = $this->db->query($query, 
			array(
				$data['customer_no'], 
				$data['bill_no'],
				$data['reminder_type'], 
				$data['times'],
				$data['email'], 
				$data['whatsapp'], 
				$data['telegram'],
				$data['id'] 
			)
		);
		
		return true;
	}

	function check_reminder_sent($customer_no, $bill_no, $reminder_type) {
		$sql = "SELECT * FROM `reminder_status` WHERE customer_no = ? AND bill_no = ? AND reminder_type = ? ";

		$query = $this->db->query($sql, array($customer_no, $bill_no, $reminder_type));        
		if($query->num_rows($query) >= 1){
			$return_val = $query->row_array();
		 	return $return_val;
		}else{
			return array();
		}
	}

	function check_reminder_sent_times($customer_no, $bill_no, $reminder_type, $times) {
		$sql = "SELECT * FROM `reminder_status` WHERE customer_no = ? AND bill_no = ? AND reminder_type = ? AND `times` >= ?";
		$query = $this->db->query($sql, array($customer_no, $bill_no, $reminder_type, $times));
		if($query->num_rows($query) >= 1){
			//already sent enough times
			return true;
		} else {
			return false;
		}
	}

	function get_user_detail_by_id($user_id) {
		$sql = "SELECT * FROM user WHERE idx = ?";

		$query = $this->db->query($sql, $user_id);
		if($query->num_rows($query) >= 1){
			$return_val = $query->row_array();
		 	return $return_val;
		}else{
			return array();
		}
	}
	
	function get_cs_service_list() {
		$query = $this->db->query('SELECT * FROM cs_service');
		return $query->result_array();
	}

	function get_cs_problem_list() {
		$query = $this->db->query('SELECT * FROM cs_problem');
		return $query->result_array();
	}

	function get_cs_service_by_id($id) {
		$query = $this->db->query('SELECT * FROM cs_service WHERE cs_service_id = ?',[$id]);
		return $query->row_array();
	}

	function get_cs_problem_by_id($id) {
		$query = $this->db->query('SELECT * FROM cs_problem WHERE cs_problem_id = ?',[$id]);
		return $query->row_array();
	}

	function get_area_list() {
		$query = $this->db->query("SELECT * FROM area ORDER BY name");
		return $query->result_array() ?? [];
	}

	/**
	 * Load ticket main PICs from tt_setting table.
	 * This function retrieves the user IDs of main PICs for tickets.
	 * 
	 * @return array Returns an array of user IDs who are main PICs.
	 */
	public function get_ticket_main_pic(){
		$query_str = 'SELECT `tt_setting_id`, `tt_user_id` FROM `tt_setting`';
		$query 		= $this->db->query($query_str);
		$result 	= $query->result_array();
		$return_val	= [];

		if (!empty($result)){
			$return_val = array_column($result, 'tt_user_id');
		}
		return $return_val;
	}

	public function get_admin_list() {
		$query_str = "SELECT u.* FROM user u WHERE allow_admin_notification = 1";
		$query = $this->db->query($query_str);
		$result = $query->result_array();

		return $result ?? [];
	}

	public function get_state_name_from_einvoice_code($state_code) {
		$query = $this->db->query('SELECT * FROM sys_state WHERE einvoice_code = ?',[$state_code]);
		$row = $query->row_array();
		return $row['name'] ?? $state_code;
	}

	public function get_locked_bill_type_ids() {
		return ['1', '2', '4', '8', '30', '31', '40', '41'];
	}

	public function get_sys_approvers($level, $doc_type)
	{
		$query = "SELECT sa.`user_id`, u.`display_name` FROM `sys_approver` sa LEFT JOIN `user` u ON sa.`user_id` = u.`idx` WHERE sa.`level` = ? AND sa.`doc_type` = ?";
		$query = $this->db->query($query, [$level, $doc_type]);
		$return_val = $query->num_rows() > 0 ? $query->result_array() : [];
		
		return $return_val;
	}

	public function perform_audit($file_path, $file_type='', $file_name = '', $link_doc_ref='', $remark='', $reg_no ='', $so_id ='', $customer_no='')
	{
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'audit_copy' ");
		$audit_copy = $config_record[0]['val'];

		if ($audit_copy == '1') {

			if (empty($file_type) || empty($file_name)) {
				log_message('error', 'Empty file type or file name on : '.$file_path);
				return false;
			}

			//check if file already cloned as auditable, supposed if this is true, then file would not be able to generate again - this should be outside of this wrapper

			//take the file in file path and clone a copy to audit
			$audit_path = $this->config->item('upload_path').'/audit/'.$file_type.'/'.basename($file_name);
			$localized_path = '/audit/'.$file_type.'/'.basename($file_name);
			$file_move = copy($file_path, $audit_path);
			if ($file_move) {

				$chk_file = $this->check_audit_record($file_name, $localized_path);

				if (!$chk_file) {
					//do a record insert for each auditable file
					$data['file_name'] = $file_name;
					$data['local_path'] = $localized_path;
					$data['link_doc'] = $file_type;
					$data['link_doc_ref'] = $link_doc_ref;
					$data['remark'] = $remark;
					$data['reg_no'] = $reg_no;
					$data['so_id'] = $so_id;
					if ($file_type == 'bill') {
						$data['bill_no'] = $link_doc_ref;
					} else {
						$data['bill_no'] = '';
					}
					$data['customer_no'] = $customer_no ?? '';
					$this->insert_audit_record($data);
				} else {
					log_message('error', 'Audit record for '.$file_name.' Path:'.$localized_path.' already created. Skipping this step.');
				}

			} else {
				log_message('error', 'Error moving file : '.$file_path.' Audit path : '.$audit_path);
				return false;
			}

			
		}
	}

	public function insert_audit_record($data) {
		$query = "INSERT INTO audit_file SET file_name = ?, upload_date = NOW(), local_path = ?, link_doc = ?, link_doc_ref = ?, remark = ?, created_by = ?, created_at = now(), sub_doc_ref = ?, is_print = ?, reg_no = ?, so_id = ?, bill_no = ?, customer_no = ?";
		
		$query = $this->db->query($query, 
			array(
				$data['file_name'], 
				$data['local_path'], 
				$data['link_doc'], 
				$data['link_doc_ref'], 
				$data['remark'],
				$this->user['idx'], 
				null, 
				0, 
				$data['reg_no'], 
				$data['so_id'],
				$data['bill_no'],
				$data['customer_no']
			)
		);
		
		return true;
	}

	public function check_audit_record($file_name, $local_path) {
		$str = "SELECT * FROM audit_file WHERE file_name = ? AND local_path = ? ";
				
		$query = $this->db->query($str, [$file_name, $local_path]);
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function search_audit($file_path, $file_type='', $file_name = '', $link_doc_ref='', $reg_no ='', $so_id ='') {
		$audit_path = $this->config->item('upload_path').'/audit/'.$file_type.'/'.$file_name;
		return file_exists($audit_path);
	}

	public function get_audit_files($txt_search='',$page_item_no=0,$row_per_page='',$query_where='')
	{

		$txt_search = $this->db->escape_str($txt_search);
		$return_val['total_row'] = 0;		
		
		$qwhere="";
		$vars = [];
		
		if( $txt_search != '' ){
			$qwhere = " AND ("
					. "file_name "
					. " LIKE '%$txt_search%' OR b.bill_no LIKE '%$txt_search%' OR c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%')";
		}

		if ( $query_where != '' ) {
			$qwhere .= " AND (link_doc = ?) ";
			$vars[] = $query_where;
		}
		
		$query_str 	= "	SELECT count(*) AS total_row 
						FROM audit_file a left join bill b on (a.bill_no = b.bill_no) left join customer c on (a.customer_no = c.customer_no)  
						WHERE 1 = 1 " . $qwhere ;
		
		$query 		= $this->db->query($query_str, $vars);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		if( $row_per_page == '' )
			$row_per_page = $_SESSION['config']['max_page_item'];
		
		$query_str 	= "	SELECT a.*, b.bill_no AS bill_no, c.customer_no AS customer_no, c.name AS customer_name "
					. "	FROM audit_file a left join bill b on (a.bill_no = b.bill_no) left join customer c on (a.customer_no = c.customer_no)   
						WHERE 1 = 1 $qwhere "
					. "ORDER BY a.created_at DESC "
					. "LIMIT $page_item_no, $row_per_page";
		$query = $this->db->query($query_str, $vars);
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ; 
		return $return_val;
	}

	public function get_audit_file_by_id($file_id) {
		$str = "SELECT * FROM audit_file WHERE file_id = ? ";
				
		$query = $this->db->query($str, [$file_id]);
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row;
		} else {
			return false;
		}
	}

	function audit_new_rec() 
	{
		$arr = array();
		$arr['file_id'] = 0;
		$arr['file_name'] = '';
		$arr['upload_date'] = date('Y-m-d H:i:s');
		$arr['local_path'] = '';
		$arr['link_doc'] = '';
		$arr['link_doc_ref'] = '';
		$arr['reg_no'] = '0';
		$arr['so_id'] = '0';
		$arr['remark'] = '';
		$arr['is_print'] = '0';
		$arr['sub_doc_ref'] = '';
		$arr['bill_no'] = '';
		$arr['customer_no'] = '';
		$arr['temp_id'] = rand(10000, 99999);
		return $arr;
	}

}
