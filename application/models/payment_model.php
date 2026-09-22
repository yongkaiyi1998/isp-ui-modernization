<?php

class Payment_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	function payment_update($payment_source,$bill_type,$customer_no,$tranx_date,$pay_date,$amount,$cheque_no,$remark,$username,$payment_no, $ref_no)
	{
		$change_text = ''; 

		$paymentInfo = $this->get_payment( $payment_no );

		if (!empty($paymentInfo)) {
			$this->load->helper('change_log');

			$this->load->model('common_model');
			
			$payment_source_arr = array_column($this->common_model->get_payment_source_list() ?? [], 'name', 'payment_source_id');
			$bill_type_arr      = array_column($this->common_model->get_bill_type_list() ?? [], 'name', 'bill_type_id');

			$change_text .= compare_field_change('Payment Source', $paymentInfo['payment_source'] ?? '', $payment_source ?? '', $payment_source_arr, true, $this->db);
			$change_text .= compare_field_change('Bill Type', $paymentInfo['bill_type'] ?? '', $bill_type ?? '', $bill_type_arr, true, $this->db);
			$change_text .= compare_field_change('Customer No', $paymentInfo['customer_no'] ?? '', $customer_no ?? '', [], true, $this->db);
			$change_text .= compare_field_change('Transaction Date', $paymentInfo['tranx_date'] ?? '', $tranx_date ?? '', [], true, $this->db);
			$change_text .= compare_field_change('Pay Date', $paymentInfo['pay_date'] ?? '', $pay_date ?? '', [], true, $this->db);
			$change_text .= compare_field_change('Amount', $paymentInfo['amount'] ?? '', $amount ?? '', [], true, $this->db);
			$change_text .= compare_field_change('Cheque No.', $paymentInfo['cheque_no'] ?? '', $cheque_no ?? '', [], true, $this->db);
			$change_text .= compare_field_change('Reference No.', $paymentInfo['ref_no'] ?? '', $ref_no ?? '', [], true, $this->db);
		}

		$query_str = "UPDATE payment SET " .
								"payment_source = '" . $payment_source . "', " .
								"bill_type = '" . $bill_type . "', " .
								"customer_no = '" . $customer_no . "', " .
								"tranx_date = '" . $tranx_date . "', " .
								"pay_date = '" . $pay_date . "', " .
								"amount = '" . $amount . "', " .
								"cheque_no = '" . $cheque_no . "', " .
								"remark = '" . $remark . "', " .
								"ref_no = '" . $ref_no . "', " .
								"modified_by = '" . $username . "', " .
								"modified_date = now() ".
								"WHERE payment_no = ' " . $payment_no . "' ";					
					
		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);	
		$method 		= $this->router->method; 				
		$action_desc	= 'Payment #'.$payment_no.' has been edited. '.$change_text;	
		$action_category 	= 'update';
		$cust_no			= ''; // $customer_no; 
		$action_log 		=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);

		if (!empty($customer_no)) {
			$customer_action_model			= 'customer_action_log_model';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
		}		
					
		$query = $this->db->query($query_str);
		
		if( $this->db->affected_rows() ){
			// $this->load->model('customer_model');	
			// $customer = $this->customer_model->get_customer($customer_no);
			// $pay_date = date( 'Y-m-01' , strtotime( $pay_date ) );
			// $next_bill_date = date( 'Y-m-01' , strtotime( " +1 month " , strtotime( $pay_date ) ) ) ;
			// $cronjob_date = CRONJOB_DATE;

			// if( ( strtotime($tranx_date) > strtotime($cronjob_date) 
			// 	&& strtotime($next_bill_date) > strtotime($customer['next_bill_date']) )
			// 	|| ( $customer['next_bill_date'] == '' || $customer['next_bill_date'] == '0000-00-00' )
			// )
			// {
			// 	$this->customer_model->customer_update_next_bill_date($customer_no,$next_bill_date);
			// }
		}
	}
	
	function payment_delete($customer_no,$payment_no='')
	{	
		$payment = $this->get_payment( $payment_no ) ;
		$paydate = $payment['pay_date'] ;
		
		$query_str = "DELETE FROM payment WHERE customer_no = '".$customer_no."' AND payment_no='".$payment_no."'";			
		
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'a payment has been deleted';	
		$action_category 	= 'delete';
		$action_log 		=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	

		if (!empty($customer_no)) {
			$customer_action_model			= 'customer_action_log_model';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
		}
					
		$this->db->query($query_str);		
	}
	
	function get_payment($payment_no='')
	{
		$query = $this->db->query("SELECT p.customer_no, p.payment_no, p.payment_no as temp_id, p.tranx_date, p.pay_date, p.payment_source, p.bill_type, p.amount, p.cheque_no, p.remark, p.is_lock, p.ref_no,
							CASE WHEN pr.comp_name != '' THEN pr.comp_name ELSE pr.acc_name END as customer_name, 
							c.package_name, c.login_username, pr.pic_email_1, c.monthly_charge, c.currency_code,
							sbt.name as bill_type_name, 
							sps.name as payment_source_name , 
							CASE WHEN c.status = 'r' THEN 'Registered' 
								 WHEN c.status = 't' THEN 'Terminated'
								 WHEN c.status = 'c' THEN 'Cancelled' 
								 WHEN c.status = 'p' THEN 'Pending' 
								 WHEN c.status = 's' THEN 'Suspended' 
								 ELSE '' END AS account_status, 
							p.ref_no, 
							c.project_name, 
							c.unit_name, 
							CASE WHEN u.display_name != '' THEN u.display_name ELSE u.username END as prepared_name   
							FROM payment p 
							INNER JOIN customer c ON p.customer_no = c.customer_no 
							LEFT JOIN profile pr ON (pr.acc_id = c.profile_id)  
							LEFT JOIN sys_bill_type sbt ON p.bill_type = sbt.bill_type_id 
							LEFT JOIN sys_payment_source sps ON p.payment_source = sps.payment_source_id 
							LEFT JOIN user u ON (u.username = p.created_by) 
							WHERE p.payment_no='".$this->db->escape_str($payment_no)."'
							LIMIT 1");
		if ($query->num_rows() > 0)	{
			
			$return_val = $query->row_array();
			
			$return_val['txt_search_autocomplete']='';
			$return_val['btn_delete']='enabled';
		}
		else {
			$return_val['txt_search_autocomplete'] = '';
			$return_val['payment_no'] = '';
			$return_val['customer_no'] = '';
			$return_val['customer_name'] = '';
			$return_val['login_username'] = '';
			$return_val['email_1'] = '';
			$return_val['tranx_date'] = date('Y-m-d');
			$return_val['pay_date'] = date('Y-m-d');
			$return_val['payment_source'] = '';
			$return_val['bill_type'] = '';
			$return_val['amount'] = '';
			$return_val['cheque_no'] = '';
			$return_val['remark'] = '';
			$return_val['is_lock'] = '';
			$return_val['bill_type_name'] = '';
			$return_val['payment_source_name'] = '';
			$return_val['package_name'] = '';
			$return_val['monthly_charge'] = '0';
			$return_val['account_status'] = '';
			$return_val['ref_no'] = '';
			$return_val['btn_delete']='disabled';

			$return_val['ref_no'] = '';
			$return_val['project_name'] = '';
			$return_val['unit_name'] = '';
			$return_val['prepared_name'] = '';
			
			$return_val['temp_id'] = rand(10000, 99999);
		}
		
		return $return_val;
	}
	
	
	function get_payment_listing($txt_search,$page_item_no,$query_where,$limit=0)
	{

		if (empty($limit)) {
			$limit = $_SESSION['config']['max_page_item'];
		}

		$txt_search = $this->db->escape_str($txt_search);
		
		$qwhere = "";
		if( $txt_search != "" )
			$qwhere .= " AND ( c.name LIKE '%$txt_search%' OR c.customer_no LIKE '%$txt_search%' )";
		
		$query_str = "SELECT count(p.payment_no) AS total_row 
					FROM payment p 
					INNER JOIN customer c ON p.customer_no = c.customer_no 
					WHERE 1 = 1 " . $qwhere . $query_where;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$query_str = "SELECT p.payment_no, p.tranx_date, p.pay_date, p.remark, p.amount, p.customer_no,
					c.name as customer_name, p.bill_no, IF(p.ref_no=0,'-',p.ref_no) AS ref_no   
					FROM payment p 
					INNER JOIN customer c ON p.customer_no = c.customer_no 
					WHERE 1 = 1 " . 
					 $qwhere . 
					 $query_where . 
					" ORDER BY p.pay_date DESC, p.payment_no DESC " . 
					" LIMIT $page_item_no, ".$limit;
		$query = $this->db->query($query_str);	
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		return $return_val;
	}
	
	function get_payment_filtered($query_where)
	{
		$query_str = "SELECT p.idx, p.payment_no, p.tranx_date, p.pay_date, p.remark, p.amount, 
								p.payment_source, c.name as customer_name, c.customer_no, 
								c.category as customer_category, sps.ledger_account_code, 
								sps.name AS payment_source_name
					FROM payment p 
					INNER JOIN customer c ON p.customer_no = c.customer_no 
					LEFT JOIN sys_payment_source sps ON p.payment_source = sps.payment_source_id
					WHERE 1 = 1 " . $query_where . 
					" ORDER BY p.pay_date DESC, p.payment_no DESC ";
		$query = $this->db->query($query_str);
		$return_val = ($query->num_rows() > 0) ? $query->result_array() : array();
		
		return $return_val;
	}
	
	function payment_summary($category, $status, $date_start, $date_end)
	{
		$return_val = array();
		$query_where = '';
		$amount = 0;
		if ( !empty($category) && $category !== 'all' ) {
			$query_where = "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}

		$query_str = "SELECT sbt.bill_type_id, sbt.name as bill_type_name, sbt.is_debit " .
					"FROM sys_bill_type sbt " .
					"ORDER BY sbt.bill_type_id ";
		$query = $this->db->query($query_str);

		$customer_category_list = array_column($this->common_model->get_category_list(), 'name', 'category_code');

		foreach ( $query->result_array() as $row ) {
			$total_bill_type = 0;
			foreach($customer_category_list as $cat_key => $cat_val) {
				$return_val[$cat_key][$row['bill_type_id']]['amount'] = 0;
			}
			
			$query_str = "SELECT c.category, " . 
						"SUM(p.amount) as amount " . 
						"FROM payment p " . 
						"INNER JOIN customer c ON c.customer_no = p.customer_no " . 
						"WHERE p.bill_type = '" . $row['bill_type_id'] . "' " . 
						"AND p.pay_date >= '$date_start' AND p.pay_date <= '$date_end' " . 
						$query_where . 
						"GROUP BY c.category " .
						"ORDER BY c.category";
						
			if( strtoupper($row['bill_type_name']) == 'DEPOSIT' ){
				//deposit from custmer_deposit
				$deposit_str = "SELECT c.category, " . 
								"SUM(cd.deposit) as amount " . 
								"FROM customer_deposit cd " . 
								"INNER JOIN customer c ON c.customer_no = cd.customer_no " . 
								"WHERE cd.deposit_date >= '$date_start' AND cd.deposit_date <= '$date_end' " . 
								$query_where . 
								"GROUP BY c.category " .
								"ORDER BY c.category "; 
				$query_str = " ( " . $query_str . " ) UNION ( " . $deposit_str . " ) " ;
			}
						
			$query = $this->db->query($query_str);
			foreach ( $query->result_array() as $row2 ) {
				if ($row['is_debit']) {
					$amount = $row2['amount'];
				} else {
					$amount = $row2['amount'] * -1;
				}
				
				$return_val[$row2['category']][$row['bill_type_id']]['amount'] = 
					($return_val[$row2['category']][$row['bill_type_id']]['amount'] ?? 0) 
					+ $amount;
				
				$total_bill_type += $amount;
			}
			
			$return_val['bill_type'][$row['bill_type_id']] = array(
								'name' => $row['bill_type_name'],
								'total' => $total_bill_type,
								);
		}

		return $return_val;
	}
	
	
	function detail_of_payment($category, $status, $payment_source, $dealer, $date_start, $date_end)
	{
		$return_val = array();
		$query_where = '';
		$amount = 0;
		
		$payment_source_list = $_SESSION['payment_source'];
		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		
		if ( !empty($category) && $category !== 'all' ) {
			$query_where = "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND c.status = '$status' ";
		}
		if ( !empty($payment_source) && $payment_source !== 'all' ) {
			$query_where .= "AND p.payment_source = '$payment_source' ";
		}
		if ( !empty($dealer) && $dealer !== 'all' ) {
			$query_where .= "AND c.dealer = '".$dealer."' ";
		}
		
		$query_str = 	"SELECT c.category, c.customer_no, c.name as customer_name, " .
						"b.name as building_name, " .
						"p.pay_date, p.remark, p.payment_no, p.cheque_no, p.amount, p.bill_type, p.payment_source, " .
						"(SELECT spb.bank_displayname
							FROM paynet_ac ac
							LEFT JOIN sys_paynet_banklist spb ON spb.bank_code = ac.fpx_buyerBankId
							WHERE ac.fpx_sellerExOrderNo = p.cheque_no
							ORDER BY ac.idx DESC
							LIMIT 1
							) AS paynet_bank " .
						"FROM payment p " .
						"INNER JOIN customer c ON c.customer_no = p.customer_no " .
						"LEFT JOIN building b ON b.building_no = c.building " .
						"WHERE p.pay_date >= '$date_start' AND p.pay_date <= '$date_end' " .
						$query_where .
						"ORDER BY c.category, p.bill_type, p.pay_date DESC, p.payment_no DESC, customer_no ";


		$query = $this->db->query($query_str);

		foreach ( $query->result_array() as $row ) {
			$category_name = $customer_category_list[$row['category']] ?? '';
			$payment_source_name = $payment_source_list[$row['payment_source']] ?? '';
			$bill_type_name = $bill_type_list[$row['bill_type']];
			
			$return_val[$category_name][$bill_type_name][] = array( 
															"pay_date" => $row['pay_date'],
															"customer_no" => $row['customer_no'],
															"customer_name" => $row['customer_name'],
															"building_name" => $row['building_name'],
															"remark" => $row['remark'],
															"payment_no" => $row['payment_no'],
															"payment_source_name" => $payment_source_name,
															"cheque_no" => $row['cheque_no'],
															"amount" => $row['amount'],
															"paynet_bank" => $row['paynet_bank'],
															);
		}

		return $return_val;
	}

	
	function payment_insert($payment_source, $bill_type, $customer_no, $tranx_date, $pay_date, $amount, $cheque_no, $remark,$username='', $ref_no='')
	{		
		$query_str = "SELECT MAX(payment_no) as payment_no FROM payment ";
		$query = $this->db->query($query_str);
		$row = $query->row_array(0);
		$payment_no = $row['payment_no'] + 1;

		if (empty($ref_no)) {
			$ref_no = 0;
		}
			
		$query_str = "INSERT INTO payment (payment_no, payment_source, bill_type, customer_no, tranx_date, pay_date, amount, cheque_no, remark,
					ref_no, created_by, created_date) VALUES (".
					"'" . $payment_no . "', ".
					"'" . $this->db->escape_str($payment_source) . "', ".
					"'" . $this->db->escape_str($bill_type) . "', ".
					"'" . $this->db->escape_str($customer_no) . "', ".
					"'" . $this->db->escape_str($tranx_date) . "', ".
					"'" . $this->db->escape_str($pay_date) . "', ".
					"'" . $this->db->escape_str($amount) . "', ".
					"'" . $this->db->escape_str($cheque_no) . "', ".
					"'" . $this->db->escape_str($remark) . "', ".
					"'" . $ref_no . "', ".
					"'" . $username . "', now() )";
	
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 				
		$action_desc		= 'New payment #'.$payment_no.' has been added';	
		$action_category 	= 'insert';
		$cust_no			= $customer_no; 
		$action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);	
		if (!empty($customer_no)) {
			$customer_action_model			= 'customer_action_log_model';
			$action_desc		= 'New payment #'.$payment_no.' by ('.$customer_no.') has been added';
			$this->load->model($customer_action_model);
			$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$this->db->escape_str($customer_no));
		}		
		
		$query = $this->db->query($query_str);
		
		if( $this->db->affected_rows() ){
				// $this->load->model('customer_model');	
				// $customer = $this->customer_model->get_customer($customer_no);
				//convert to 1st of pay date month
				
				// $next_bill_date = date( 'Y-m-01' , strtotime( " +1 month " , strtotime( date( 'Y-m-01' , strtotime( $pay_date ) ) ) ) );
				
				// $cronjob_date = CRONJOB_DATE;
				
				//update next bill date if needed, late then cronjob date
				//strtotime($tranx_date) > strtotime($cronjob_date) 
				// if( ( strtotime($pay_date) > strtotime($cronjob_date) 
				// 	&& strtotime($next_bill_date) > strtotime($customer['next_bill_date']) )
				// 	|| ( $customer['next_bill_date'] == '' || $customer['next_bill_date'] == '0000-00-00' )
				// )
				// {
				// 	$this->customer_model->customer_update_next_bill_date($customer_no,$next_bill_date);
				// }
				
		}
		
		return $payment_no;
	}
	
	function get_payment_source( $payment_source_id ){
		$query_str = " SELECT * FROM sys_payment_source WHERE payment_source_id = '".$payment_source_id."' ";
		$query = $this->db->query($query_str);
		if( $query->num_rows() ){
			$row = $query->row_array;
		}else{
			$row['payment_source_id']='';
			$row['name']='';
			$row['ledger_account_code']='';
		}
		
		return $row;
	}
	
		
}
