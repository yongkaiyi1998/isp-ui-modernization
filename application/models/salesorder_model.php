<?php
class Salesorder_model extends MY_Model{

	protected $_table = 'so_head';
	protected $_primary_key = 'so_id';

	public function __construct()
	{
		parent::__construct();
		
	}

	public function get_sales_order_listing($page_item_no=0,$query_where=array()) {
		$return_val['total_row'] = 0;

		$where = '';
		$where_txt = '';
		$order_by = " ORDER BY so.`date` DESC ";

		//$query_where is array with search param
		if (isset($query_where['txt_search'])) {
			if (!empty($query_where['txt_search'])) {

				$query_where['txt_search'] = $this->db->escape_str($query_where['txt_search']);
				
				$where_txt .= " AND (
				icno LIKE '%".$query_where['txt_search']."%' 
				OR cust_name LIKE '%".$query_where['txt_search']."%' 
				OR del_tel LIKE '%".$query_where['txt_search']."%' 
				OR bill_email LIKE '%".$query_where['txt_search']."%' 
				OR comp_name LIKE '%".$query_where['txt_search']."%' 
				OR bill_unit_no LIKE '%".$query_where['txt_search']."%' 
				OR del_unit_no LIKE '%".$query_where['txt_search']."%' 
				)";
			}
		}

		if (isset($query_where['sel_status'])) {
			if (!empty($query_where['sel_status']) && $query_where['sel_status'] != 'all') {
				$where_txt .= " AND `status` = '".$this->db->escape_str($query_where['sel_status'])."' ";
			}
		}

		if (!empty($query_where['sel_installation']) && $query_where['sel_installation'] != 'all') {
			
			switch ($query_where['sel_installation']) {

				case 'upcoming':
					$where_txt .= " AND so.preferred_install_datetime >= NOW() ";
					$order_by = " ORDER BY so.preferred_install_datetime ASC, so.`date` DESC ";
					break;

				case 'overdue':
					$where_txt .= " AND so.preferred_install_datetime < NOW() ";
					$order_by = " ORDER BY so.preferred_install_datetime DESC, so.`date` DESC ";
					break;

				case 'unscheduled':

					$where_txt .= " AND so.preferred_install_datetime IS NULL ";
					$order_by = " ORDER BY so.`date` DESC ";
					break;
			}
		}

		$query_str= "SELECT count(*) as total_row FROM so_head so " .
					"WHERE 1=1 ".$where_txt." ".$where;
					
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query_str = "
		SELECT 
			so.*, 
			u.username AS requestor_name 
		FROM 
			so_head so 
		LEFT JOIN `user` u 
			ON (so.created_by = u.idx) 
		WHERE 1=1 ".$where_txt." ".$where." ".$order_by." 
		LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];

		$query = $this->db->query($query_str);
		
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ;
		return $return_val;

	}

	public function get_so($so_id='') {
		$query = $this->db->query("SELECT so.*, so.so_id AS temp_id, p.acc_name as profile_name, c.name as customer_name, cs.status as customer_current_status
							FROM so_head so 
							LEFT JOIN profile p
							ON p.acc_id = so.profile_id
							LEFT JOIN customer c
							ON c.customer_no = so.customer_no
							LEFT JOIN (
								SELECT acs.* 
								FROM customer_status acs 
								JOIN (
									SELECT customer_no, MAX(status_id) AS max_status_id 
									FROM customer_status 
									GROUP BY customer_no
								) bcs ON acs.status_id = bcs.max_status_id
							) cs ON cs.customer_no = so.customer_no
							WHERE so.so_id='".$this->db->escape_str($so_id)."'
							LIMIT 1");

		if ($query->num_rows() > 0)	{

			$return_val[$this->_table] = $query->row_array();

			//put limit 1, temporary link to only 1 record
			$query = "	SELECT a.*, p.dia_vars, p.monthly_charge, p.installation, p.deposit, p.one_time_charge FROM so_details a LEFT JOIN package p ON (a.prod_id = p.package_no) 
						WHERE a.so_id = '".$this->db->escape_str($so_id)."' LIMIT 1; ";

			$details = $this->db->query( $query );

			$return_val['item_list'] = array();
			$return_val['so_detail_rows'] = json_encode(array());
			if ($details->num_rows() > 0)
			{
				// $return_val['item_list'] = $details->result_array();
				$item_list = $details->row_array();
				/*foreach($item_list as $il_key => $il_val){
					foreach($il_val as $il_val_key => $il_val_val){
						$item_list[$il_key][$il_val_key] = htmlentities($il_val_val,ENT_QUOTES, "UTF-8");
					}

					//dont do htmlentities on this field
					$item_list[$il_key]['dia_vars'] = $il_val['dia_vars'];
				}*/
				$return_val['item_list'] = $item_list;
				$return_val['so_detail_rows'] = json_encode($item_list);

				//so detail 1 row
				$return_val['so_details']['so_detail_id'] = $item_list['so_detail_id'];
				$return_val['so_details']['prod_id'] = $item_list['prod_id'];
				$return_val['so_details']['dia_vars'] = $item_list['dia_vars'];
				$return_val['so_details']['item_name'] = $item_list['item_name'];
				$return_val['so_details']['monthly_charge'] = $item_list['monthly_charge'];
				$return_val['so_details']['installation'] = $item_list['installation'];
				$return_val['so_details']['one_time_charge'] = $item_list['one_time_charge'];
				$return_val['so_details']['deposit'] = $item_list['deposit'];

			} else {
				//empty so_details
				$return_val['so_details']['so_detail_id'] = 0;
				$return_val['so_details']['prod_id'] = 0;
				$return_val['so_details']['dia_vars'] = json_encode(array());
				$return_val['so_details']['item_name'] = '';
				$return_val['so_details']['monthly_charge'] = 0.00;
				$return_val['so_details']['installation'] = 0.00;
				$return_val['so_details']['one_time_charge'] = 0.00;
				$return_val['so_details']['deposit'] = 0.00;
			}

		} else {
			//new record
			$return_val['item_list'] = array();
			$return_val['so_detail_rows'] = json_encode(array());
			//$return_val['record_exist'] 					= '0';
			$return_val[$this->_table][$this->_primary_key]	= '0';
			$return_val[$this->_table]['cust_id'] 			= '0';
			$return_val[$this->_table]['cust_name'] 			= '';
			$return_val[$this->_table]['so_num']			= '';
			$return_val[$this->_table]['comp_name'] 		= '';
			$return_val[$this->_table]['comp_num'] 			= '';
			$return_val[$this->_table]['comp_tin'] 			= '';
			$return_val[$this->_table]['currency_code'] 	= 'MYR';
			$return_val[$this->_table]['currency_rate'] 	= '1';
			$return_val[$this->_table]['gst_num'] 			= '';
			$return_val[$this->_table]['po_num'] 			= '';
			$return_val[$this->_table]['payment_term'] 		= '';
			$return_val[$this->_table]['date'] 		= '';
			$return_val[$this->_table]['del_attn'] 			= '';
			$return_val[$this->_table]['del_unit_no'] 			= '';
			$return_val[$this->_table]['del_company_name'] 			= '';
			$return_val[$this->_table]['del_addr_1'] 		= '';
			$return_val[$this->_table]['del_addr_2'] 		= '';
			$return_val[$this->_table]['del_addr_3'] 		= '';
			$return_val[$this->_table]['del_postcode'] 		= '';
			$return_val[$this->_table]['del_city'] 		= '';
			$return_val[$this->_table]['del_tel'] 			= '';
			$return_val[$this->_table]['del_fax'] 			= '';
			$return_val[$this->_table]['del_state'] 			= '07';
			$return_val[$this->_table]['bill_attn'] 		= '';
			$return_val[$this->_table]['bill_unit_no'] 			= '';
			$return_val[$this->_table]['bill_addr_1'] 		= '';
			$return_val[$this->_table]['bill_addr_2'] 		= '';
			$return_val[$this->_table]['bill_addr_3'] 		= '';
			$return_val[$this->_table]['bill_postcode'] 		= '';
			$return_val[$this->_table]['bill_city'] 		= '';
			$return_val[$this->_table]['bill_tel'] 			= '';
			$return_val[$this->_table]['bill_fax'] 			= '';
			$return_val[$this->_table]['bill_state'] 			= '07';
			$return_val[$this->_table]['bill_email'] 			= '';
			$return_val[$this->_table]['notes'] 			= '';
			$return_val[$this->_table]['status'] 			= '1';
			
			$return_val[$this->_table]['shipping_fee'] 		= '0.00';
			$return_val[$this->_table]['grand_total'] 			= '0.00';
			$return_val[$this->_table]['round_val'] 		= '0.00';
			$return_val[$this->_table]['allow_rounding'] 	= '0';
			$return_val[$this->_table]['doc_rev_num'] 	= '0';
			$return_val[$this->_table]['revised'] 	= '0';
			$return_val[$this->_table]['status_approved'] 	= '0';
			$return_val[$this->_table]['status_lock'] 		= '0';
			$return_val[$this->_table]['status_void'] 		= '0';
			$return_val[$this->_table]['gst_app'] 			= '0';			
			$return_val[$this->_table]['grand_tax'] 		= '0.00';
			$return_val[$this->_table]['grand_subtotal'] 	= '0.00';
			$return_val[$this->_table]['base_amount'] 		= '0.00';
			$return_val[$this->_table]['created_by'] 		= '';
			$return_val[$this->_table]['modified_by'] 		= '';

			$return_val[$this->_table]['quotation_id'] 			= '0';
			$return_val[$this->_table]['dept_id'] 			= '0';

			$return_val[$this->_table]['reg_no']			= '';
			$return_val[$this->_table]['register_type']			= 'r';
			$return_val[$this->_table]['icno']			= '';

			$return_val[$this->_table]['profile_id']		= '';
			$return_val[$this->_table]['customer_no']		= '';

			$return_val[$this->_table]['agent_id']			= '0';
			$return_val[$this->_table]['preferred_login']			= '';
			$return_val[$this->_table]['preferred_password']			= '';

			$return_val[$this->_table]['dia_vars'] = json_encode(array());
			$return_val[$this->_table]['building_no'] = '0';
			$return_val[$this->_table]['reg_type'] = 'r';
			$return_val[$this->_table]['tin'] = '';

			$return_val[$this->_table]['preferred_install_datetime'] = '';

			$return_val[$this->_table]['temp_id'] = rand(10000, 99999);

			$datetime = date("Y-m-d H:i:s");
			$return_val[$this->_table]['created_date'] 		= $datetime;
			$return_val[$this->_table]['modified_date'] 	= $datetime;

			//so detail 1 row
			$return_val['so_details']['so_detail_id'] = '0';
			$return_val['so_details']['prod_id'] = '0';
			$return_val['so_details']['dia_vars'] = '';
			$return_val['so_details']['item_name'] = '';
			$return_val['so_details']['monthly_charge'] = '';
			$return_val['so_details']['installation'] = '';
			$return_val['so_details']['one_time_charge'] = '';
			$return_val['so_details']['deposit'] = '';
		}

		return $return_val;
	}

	function get_print_so($so_id='') {
		$so_query = $this->db->query("SELECT * FROM so_head WHERE so_id=? LIMIT 1", [$so_id]);
		$so_head = $so_query->row_array();
	
		$query = $this->db->query("SELECT * FROM so_details WHERE so_id=? LIMIT 1", [$so_id]);
		$so_detail = $query->row_array();
	
		$query = $this->db->query("SELECT * FROM package WHERE package_no=? LIMIT 1", [$so_detail['prod_id']]);
		$so_detail['package'] = $query->row_array();
	
		$so_head['so_details'] = $so_detail;

		if ($so_query->num_rows() > 0) {
			return $so_head;
		} else {
			return [];
		}
	}

	public function salesorder_insert($post_back)
	{

		if (!isset($post_back['so_head']['bill_state'])) {
			$post_back['so_head']['bill_state'] = '';
		}

		if (!isset($post_back['so_head']['del_state'])) {
			$post_back['so_head']['del_state'] = '';
		}

		$new = 1;
		$change_text = '';
		if (!empty($post_back['so_head']['so_id'])) {
			$soInfo = $this->get_so( $post_back['so_head']['so_id'] );

			if (!empty($soInfo)) {
				$this->load->helper('change_log');

				$new = 0;
				$status_arr = ['1' => 'Pending', '2' => 'Approved/Profile'];
				$state_arr = array_column($this->common_model->get_state_list() ?? [], 'name', 'einvoice_code');
				$dealer_arr = array_column($this->common_model->get_dealer_list() ?? [], 'name', 'dealer_no');

				$change_text .= compare_field_change('So No.', $soInfo['so_head']['so_num'], $post_back['so_head']['so_num'], [], true, $this->db);
				$change_text .= compare_field_change('Customer Name', $soInfo['so_head']['cust_name'], $post_back['so_head']['cust_name'], [], true, $this->db);
				$change_text .= compare_field_change('Company Name', $soInfo['so_head']['comp_name'], $post_back['so_head']['comp_name'], [], true, $this->db);
				$change_text .= compare_field_change('IC No.', $soInfo['so_head']['icno'], $post_back['so_head']['icno'], [], true, $this->db);
				$change_text .= compare_field_change('SSM#', $soInfo['so_head']['comp_num'] ?? '', ($post_back['so_head']['comp_num'] ?? ''), [], true, $this->db);
				$change_text .= compare_field_change('Company TIN', ($soInfo['so_head']['tin'] ?? ''), ($post_back['so_head']['tin'] ?? ''), [], true, $this->db);
				$change_text .= compare_field_change('SST#', $soInfo['so_head']['gst_num'], $post_back['so_head']['gst_num'], [], true, $this->db);
				$change_text .= compare_field_change('Payment Term', $soInfo['so_head']['payment_term'], $post_back['so_head']['payment_term'], [], true, $this->db);
				$change_text .= compare_field_change('Date', $soInfo['so_head']['date'], $post_back['so_head']['date'], [], true, $this->db);
				$change_text .= compare_field_change('Status', $soInfo['so_head']['status'], $post_back['so_head']['status'], $status_arr, true, $this->db);
				$change_text .= compare_field_change('Agent', $soInfo['so_head']['agent_id'], $post_back['so_head']['agent_id'], $dealer_arr, true, $this->db);

				$change_text .= compare_field_change('Billing Attn.', $soInfo['so_head']['bill_attn'], $post_back['so_head']['bill_attn'], [], true, $this->db);
				$change_text .= compare_field_change('Billing Address Line 1', $soInfo['so_head']['bill_addr_1'], $post_back['so_head']['bill_addr_1'], [], true, $this->db);
				$change_text .= compare_field_change('Billing Address Line 2', $soInfo['so_head']['bill_addr_2'], $post_back['so_head']['bill_addr_2'], [], true, $this->db);
				$change_text .= compare_field_change('Billing Address Line 3', $soInfo['so_head']['bill_addr_3'], $post_back['so_head']['bill_addr_3'], [], true, $this->db);
				$change_text .= compare_field_change('Billing Postcode', $soInfo['so_head']['bill_postcode'], $post_back['so_head']['bill_postcode'], [], true, $this->db);
				$change_text .= compare_field_change('Billing City', $soInfo['so_head']['bill_city'], $post_back['so_head']['bill_city'], [], true, $this->db);
				$change_text .= compare_field_change('Billing Tel', $soInfo['so_head']['bill_tel'], $post_back['so_head']['bill_tel'], [], true, $this->db);
				$change_text .= compare_field_change('Billing State', $soInfo['so_head']['bill_state'], $post_back['so_head']['bill_state'], $state_arr, true, $this->db);
				$change_text .= compare_field_change('Billing Email', $soInfo['so_head']['bill_email'], $post_back['so_head']['bill_email'], [], true, $this->db);

				$change_text .= compare_field_change('Delivery Attn.', $soInfo['so_head']['del_attn'], $post_back['so_head']['del_attn'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery Company Name', $soInfo['so_head']['del_company_name'], $post_back['so_head']['del_company_name'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery Address Line 1', $soInfo['so_head']['del_addr_1'], $post_back['so_head']['del_addr_1'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery Address Line 2', $soInfo['so_head']['del_addr_2'], $post_back['so_head']['del_addr_2'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery Address Line 3', $soInfo['so_head']['del_addr_3'], $post_back['so_head']['del_addr_3'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery Postcode', $soInfo['so_head']['del_postcode'], $post_back['so_head']['del_postcode'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery City', $soInfo['so_head']['del_city'], $post_back['so_head']['del_city'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery Tel.', $soInfo['so_head']['del_tel'], $post_back['so_head']['del_tel'], [], true, $this->db);
				$change_text .= compare_field_change('Delivery State', $soInfo['so_head']['del_state'], $post_back['so_head']['del_state'], $state_arr, true, $this->db);

				$change_text .= compare_field_change('Notes', $soInfo['so_head']['notes'], $post_back['so_head']['notes'], [], true, $this->db);
				$change_text .= compare_field_change('Grand Total', $soInfo['so_head']['grand_total'], $post_back['so_head']['grand_total'], [], true, $this->db);
				$change_text .= compare_field_change('Grand Tax', $soInfo['so_head']['grand_tax'], $post_back['so_head']['grand_tax'], [], true, $this->db);
				$change_text .= compare_field_change('Grand Subtotal', $soInfo['so_head']['grand_subtotal'], $post_back['so_head']['grand_subtotal'], [], true, $this->db);
				$change_text .= compare_field_change('Preferred Login', $soInfo['so_head']['preferred_login'], $post_back['so_head']['preferred_login'], [], true, $this->db);
				$change_text .= compare_field_change('Preferred Install Datetime', $soInfo['so_head']['preferred_install_datetime'], $post_back['so_head']['preferred_install_datetime'], [], true, $this->db);

				$building_changed = compare_field_change('Building', $soInfo['so_head']['building_no'], $post_back['so_head']['building_no'], [], true, $this->db);
				if ($building_changed) $change_text .= ' Building has been changed. ';

				if (($soInfo['item_list']['prod_id'] ?? 0) != ($post_back['so_details']['prod_id'] ?? 0)) {
					$change_text .= ' Product changed to ' . ($post_back['so_details']['item_name'] ?? '') . '. ';
				}
			}
		}

		//if po_num and is add, then auto generate po_num
		if (empty($post_back['so_head']['so_id']) && empty($post_back['so_head']['so_num'])) {
			$post_back['so_head']['so_num'] = $this->auto_gen_so_num();
		}

		$post_back['so_head']['reg_no'] = normalize_input($post_back['so_head']['reg_no'], 'int', 0);
		$post_back['so_head']['preferred_install_datetime'] = normalize_input($post_back['so_head']['preferred_install_datetime']);

		$sql = "INSERT INTO `so_head` (
			so_id, `cust_id`, cust_name, so_num, doc_rev_num, revised, `comp_name`, comp_num, comp_tin, currency_code, currency_rate, gst_num, po_num, shipping_fee, payment_term, `date`, del_attn, del_unit_no, del_company_name, del_addr_1, del_addr_2, del_addr_3, del_postcode, del_city, del_tel, del_fax, del_state, bill_attn, bill_unit_no, bill_addr_1, bill_addr_2, bill_addr_3, bill_postcode, bill_city, bill_tel, bill_fax, bill_state, bill_email, notes, `status`, grand_total, round_val, allow_rounding, status_approved, status_lock, status_void, gst_app, grand_tax, grand_subtotal, base_amount, created_by, created_date, quotation_id, dept_id, reg_no, register_type, icno, agent_id, preferred_login, preferred_password, dia_vars, building_no, reg_type, tin, preferred_install_datetime, modified_by, modified_date 
		) VALUES (
			?,?,?,?,?,?,?,?,?,?,
			?,?,?,?,?,?,?,?,?,?,
			?,?,?,?,?,?,?,?,?,?,
			?,?,?,?,?,?,?,?,?,?,
			?,?,?,?,?,?,?,?,?,?,
			?,?,?,?,?,?,?,?,?,?,
			?,?,?,?,?,?,?           
		) ON DUPLICATE KEY UPDATE 
			`cust_id` = ?, 
			cust_name = ?,
			so_num = ?,
			doc_rev_num = ?,
			revised = ?,
			`comp_name` = ?,
			comp_num = ?,
			comp_tin = ?,
			currency_code = ?,
			currency_rate = ?,
			gst_num = ?,
			po_num = ?,
			shipping_fee = ?,
			payment_term = ?,
			`date` = ?, 
			del_attn = ?,
			del_unit_no=?,
			del_company_name = ?,
			del_addr_1 = ?,
			del_addr_2 = ?,
			del_addr_3 = ?,
			del_postcode = ?,
			del_city = ?,
			del_tel=?,
			del_fax=?,  
			del_state=?, 
			bill_attn=?, 
			bill_unit_no=?,
			bill_addr_1=?, 
			bill_addr_2=?, 
			bill_addr_3=?, 
			bill_postcode=?, 
			bill_city=?, 
			bill_tel=?, 
			bill_fax=?, 
			bill_state=?,
			bill_email=?,
			notes=?, 
			`status`=?, 
			grand_total=?, 
			round_val=?, 
			allow_rounding=?, 
			status_approved=?, 
			status_lock=?, 
			status_void=?, 
			gst_app=?, 
			grand_tax=?, 
			grand_subtotal=?, 
			base_amount=?, 
			modified_by=?, 
			modified_date=?, 
			quotation_id=?, 
			dept_id=?, 
			reg_no=?, 
			register_type=?, 
			icno=?, 
			agent_id=?,
			preferred_login=?,
			preferred_password=?,
			dia_vars=?, 
			building_no=?, 
			reg_type=?, 
			tin=?,
			preferred_install_datetime=?       
		";

		$values_array = array(
			/*insert*/
			$post_back['so_head']['so_id'], 
			$post_back['so_head']['cust_id'],
			$post_back['so_head']['cust_name'],
			$post_back['so_head']['so_num'],
			$post_back['so_head']['doc_rev_num'],
			$post_back['so_head']['revised'],
			$post_back['so_head']['comp_name'],
			$post_back['so_head']['comp_num'],
			$post_back['so_head']['tin'] ?? '',
			$post_back['so_head']['currency_code'],
			$post_back['so_head']['currency_rate'],
			$post_back['so_head']['gst_num'],
			$post_back['so_head']['po_num'],
			$post_back['so_head']['shipping_fee'],
			$post_back['so_head']['payment_term'],
			$post_back['so_head']['date'],
			$post_back['so_head']['del_attn'],
			$post_back['so_head']['del_unit_no'],
			$post_back['so_head']['del_company_name'],
			$post_back['so_head']['del_addr_1'],
			$post_back['so_head']['del_addr_2'],
			$post_back['so_head']['del_addr_3'],
			$post_back['so_head']['del_postcode'],
			$post_back['so_head']['del_city'],
			$post_back['so_head']['del_tel'],
			$post_back['so_head']['del_fax'],
			$post_back['so_head']['del_state'],
			$post_back['so_head']['bill_attn'],
			$post_back['so_head']['bill_unit_no'],
			$post_back['so_head']['bill_addr_1'],
			$post_back['so_head']['bill_addr_2'],
			$post_back['so_head']['bill_addr_3'],
			$post_back['so_head']['bill_postcode'],
			$post_back['so_head']['bill_city'],
			$post_back['so_head']['bill_tel'],
			$post_back['so_head']['bill_fax'],
			$post_back['so_head']['bill_state'],
			$post_back['so_head']['bill_email'],
			$post_back['so_head']['notes'],
			$post_back['so_head']['status'],
			$post_back['so_head']['grand_total'],
			$post_back['so_head']['round_val'],
			$post_back['so_head']['allow_rounding'],
			$post_back['so_head']['status_approved'],
			$post_back['so_head']['status_lock'],
			$post_back['so_head']['status_void'],
			$post_back['so_head']['gst_app'],
			$post_back['so_head']['grand_tax'],
			$post_back['so_head']['grand_subtotal'],
			$post_back['so_head']['base_amount'],
			$post_back['user_idx'],
			date('Y-m-d H:i:s'),
			$post_back['so_head']['quotation_id'],
			$post_back['so_head']['dept_id'],
			$post_back['so_head']['reg_no'],
			$post_back['so_head']['register_type'],
			$post_back['so_head']['icno'], 
			$post_back['so_head']['agent_id'], 
			$post_back['so_head']['preferred_login'], 
			$post_back['so_head']['preferred_password'], 
			$post_back['so_head']['dia_vars'],
			$post_back['so_head']['building_no'],
			strtolower($post_back['so_head']['reg_type']),
			$post_back['so_head']['tin'],
			$post_back['so_head']['preferred_install_datetime'],
			$post_back['user_idx'],
			date('Y-m-d H:i:s'),
			/*update*/
			$post_back['so_head']['cust_id'],
			$post_back['so_head']['cust_name'],
			$post_back['so_head']['so_num'],
			$post_back['so_head']['doc_rev_num'],
			$post_back['so_head']['revised'],
			$post_back['so_head']['comp_name'],
			$post_back['so_head']['comp_num'],
			$post_back['so_head']['comp_tin'] ?? '',
			$post_back['so_head']['currency_code'],
			$post_back['so_head']['currency_rate'],
			$post_back['so_head']['gst_num'],
			$post_back['so_head']['po_num'],
			$post_back['so_head']['shipping_fee'],
			$post_back['so_head']['payment_term'],
			$post_back['so_head']['date'],
			$post_back['so_head']['del_attn'],
			$post_back['so_head']['del_unit_no'],
			$post_back['so_head']['del_company_name'],
			$post_back['so_head']['del_addr_1'],
			$post_back['so_head']['del_addr_2'],
			$post_back['so_head']['del_addr_3'],
			$post_back['so_head']['del_postcode'],
			$post_back['so_head']['del_city'],
			$post_back['so_head']['del_tel'],
			$post_back['so_head']['del_fax'],
			$post_back['so_head']['del_state'],
			$post_back['so_head']['bill_attn'],
			$post_back['so_head']['bill_unit_no'],
			$post_back['so_head']['bill_addr_1'],
			$post_back['so_head']['bill_addr_2'],
			$post_back['so_head']['bill_addr_3'],
			$post_back['so_head']['bill_postcode'],
			$post_back['so_head']['bill_city'],
			$post_back['so_head']['bill_tel'],
			$post_back['so_head']['bill_fax'],
			$post_back['so_head']['bill_state'],
			$post_back['so_head']['bill_email'],
			$post_back['so_head']['notes'],
			$post_back['so_head']['status'],
			$post_back['so_head']['grand_total'],
			$post_back['so_head']['round_val'],
			$post_back['so_head']['allow_rounding'],
			$post_back['so_head']['status_approved'],
			$post_back['so_head']['status_lock'],
			$post_back['so_head']['status_void'],
			$post_back['so_head']['gst_app'],
			$post_back['so_head']['grand_tax'],
			$post_back['so_head']['grand_subtotal'],
			$post_back['so_head']['base_amount'],
			$post_back['user_idx'], 
			date('Y-m-d H:i:s'),
			$post_back['so_head']['quotation_id'],
			$post_back['so_head']['dept_id'],
			$post_back['so_head']['reg_no'], 
			$post_back['so_head']['register_type'], 
			$post_back['so_head']['icno'], 
			$post_back['so_head']['agent_id'], 
			$post_back['so_head']['preferred_login'], 
			$post_back['so_head']['preferred_password'],
			$post_back['so_head']['dia_vars'], 
			$post_back['so_head']['building_no'], 
			strtolower($post_back['so_head']['reg_type']),
			$post_back['so_head']['tin'],
			$post_back['so_head']['preferred_install_datetime']
		);

		$this->db->query($sql, $values_array);

		if (empty($post_back['so_head']['so_id'])) {
			$post_back['so_head']['so_id'] = $this->db->insert_id();
		}

		//so_details
		$this->db->query("delete from `so_details` WHERE so_id = ? ", array($post_back['so_head']['so_id']));
		//$so_details_list = json_decode($post_back['so_detail_rows']);

		if (!empty($post_back['so_details']['prod_id'])) {
			$this->load->model('package_model');
			$package_data = $this->package_model->get_package($post_back['so_details']['prod_id']);

			$tax_data = $this->db->query('SELECT b.percent FROM sys_bill_type a LEFT JOIN sys_tax_type b ON (b.code = a.tax_code) WHERE bill_type_id = ? LIMIT 1', [$package_data['bill_type']])->row_array();
			
			$tax_rate = $tax_data['percent'] ?? 0;
			$tax = number_format((floatval($package_data['monthly_charge']) * $tax_rate / 100), 2) ?? 0.00;
			$total = $package_data['monthly_charge'] + $tax;

			$so_details_list[0]['line_num'] = '1';
			$so_details_list[0]['prod_id'] = $post_back['so_details']['prod_id'];
			$so_details_list[0]['item_name'] = $post_back['so_details']['item_name'];
			$so_details_list[0]['price'] = $package_data['monthly_charge'];
			$so_details_list[0]['quantity'] = '1';
			//if bill type is tax?
			$so_details_list[0]['tax'] = $tax;
			$so_details_list[0]['subtotal'] = $package_data['monthly_charge'];
			$so_details_list[0]['total'] = $total;
			$so_details_list[0]['tax_rate'] = $tax_rate;

			foreach ($so_details_list as $so_details_row) {
				if (empty($so_details_row['price']) || $so_details_row['price'] == '') $so_details_row['price'] = '0.00';
				if (empty($so_details_row['quantity']) || $so_details_row['quantity'] == '') $so_details_row['quantity'] = '0';
				if (empty($so_details_row['subtotal']) || $so_details_row['subtotal'] == '') $so_details_row['subtotal'] = '0.00';
				if (empty($so_details_row['tax_rate']) || $so_details_row['tax_rate'] == '') $so_details_row['tax_rate'] = '0.00';

				$this->db->query("INSERT INTO `so_details` (
					so_id, line_num, prod_id, item_name, `desc`, price, quantity, uom, tax, subtotal, total, tax_type, tax_rate, purchase_type, po_jo 
				) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", array(
					$post_back['so_head']['so_id'], 
					$so_details_row['line_num'],
					$so_details_row['prod_id'],
					$so_details_row['item_name'],
					$so_details_row['item_name'],
					$so_details_row['price'],
					$so_details_row['quantity'],
					'unit',
					$so_details_row['tax'],
					$so_details_row['subtotal'],
					$so_details_row['total'],
					'',
					$so_details_row['tax_rate'],
					'',
					'jo'
				));
			}

		}

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.print_r($values_array, true));	
		$method 		= $this->router->method; 				
		if ($new == 1) {
			$action_desc	= 'a new Sales Order ('.$this->db->escape_str($post_back['so_head']['so_num']).') has been added';	
			$action_category = 'insert';
		} else {
			$action_desc	= 'a Sales Order ('.$this->db->escape_str($post_back['so_head']['so_num']).') has been edited.'.$change_text;
			$action_category = 'update';
		}
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		return $post_back['so_head']['so_id'];

	}

	public function update_so_customer_info($so_id, $profile_id, $customer_no)
	{
		return $this->db->where('so_id', $so_id)
						->update('so_head', [
							'profile_id' => $profile_id,
							'customer_no' => $customer_no
						]);
	}

	public function auto_gen_so_num()
	{
		$query = $this->db->query("select IFNULL(max(so_id),0)+1 AS next_so_num from so_head");

		$return_val = $query->row_array();

		return $return_val['next_so_num'];
	}

	public function update_status_to_approved($so_id)
	{
		$sql = "UPDATE `so_head` SET `status` = 2 WHERE `so_id` = ?";
		$this->db->query($sql, [$so_id]);
	}

}