<?php
class Einvoice_model extends MY_Model{

	protected $_table = 'bill';
	protected $_primary_key = 'idx';

	public function __construct()
	{
		parent::__construct();
	}

	function get_einvoice_list($txt_search,$page_item_no,$txt_bill_date = '',$sel_status = 'all')
	{

		$txt_search = $this->db->escape_str($txt_search);
		
		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		$query_where = '';
		$query_max_where = '';

		if (!empty($txt_bill_date)){
			$query_where .= " AND ( b.bill_date <= '".date("Y-m-d",strtotime($txt_bill_date))."' ) " ;
			$query_max_where .= " AND ( bill_date <= '".date("Y-m-d",strtotime($txt_bill_date))."' ) "; 			
		}

		if ($sel_status == 'P'){
			$query_where .= " AND ( be.einvoice_status = 'P' ) " ;
		} elseif ($sel_status == 'S'){
			$query_where .= " AND ( be.einvoice_status = 'S' ) " ;
		} elseif ($sel_status == 'F'){
			$query_where .= " AND ( be.einvoice_status = 'F' ) " ;
		} elseif ($sel_status == 'U'){
			$query_where .= " AND ( be.einvoice_status IS NULL OR be.einvoice_status NOT IN ('P', 'S') ) " ;
		}

		$query_str= "SELECT count(*) as total_row 
						FROM customer c 
						INNER JOIN bill b ON c.customer_no = b.customer_no 
						INNER JOIN ( 
							SELECT MAX(bill_no) as bill_no 
							FROM bill 
							WHERE 1=1 AND is_void = 0 ".$query_max_where."
							GROUP BY customer_no 
						) a ON a.bill_no = b.bill_no 
						LEFT JOIN bill_einvoice be ON (be.bill_no = b.bill_no) 
						WHERE (c.customer_no LIKE '%$txt_search%' 
								OR c.name LIKE '%$txt_search%' 
								OR c.nric LIKE '%$txt_search%') " . $query_where;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query = $this->db->query("SELECT c.customer_no, c.name, 
											IF(c.activated_date = 0, '', c.activated_date) as activated_date, 
											b.bill_no, b.bill_date as last_bill_date, b.balance, b.amount, be.einvoice_status, be.created_on AS einvoice_submit_date, b.bill_type    
									FROM customer c 
									INNER JOIN bill b ON c.customer_no = b.customer_no 
									INNER JOIN ( 
										SELECT MAX(bill_no) as bill_no 
										FROM bill 
										WHERE 1=1 AND is_void = 0 ".$query_max_where."
										GROUP BY customer_no 
									) a ON a.bill_no = b.bill_no 
									LEFT JOIN bill_einvoice be ON (be.bill_no = b.bill_no) 
									WHERE (c.customer_no LIKE '%$txt_search%' 
											OR c.name LIKE '%$txt_search%' 
											OR c.nric LIKE '%$txt_search%') 
									" . $query_where . " 
									ORDER BY c.customer_no LIMIT $page_item_no, ". $_SESSION['config']['max_page_item']);
		if ($query->num_rows() > 0){
			$return_val['row'] = $query->result_array();
		}
		return $return_val;
	}

	function get_consolidated_einvoice_list($page_item_no,$txt_bill_date = '')
	{
		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		$query_where = '';

		if (!empty($txt_bill_date)){
			$query_where .= " AND ( ce.inv_date <= '".date("Y-m-d",strtotime($txt_bill_date))."' ) " ;			
		}

		$query_str= "SELECT count(*) as total_row 
						FROM consolidated_einvoice ce 
						WHERE 1=1 " . $query_where;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query = $this->db->query("SELECT ce.* 
									FROM consolidated_einvoice ce 
									WHERE 1=1 
									" . $query_where . " 
									ORDER BY ce.created_on DESC LIMIT $page_item_no, ". $_SESSION['config']['max_page_item']);
		if ($query->num_rows() > 0){
			$return_val['row'] = $query->result_array();
		}
		return $return_val;

	}

	function get_unsubmitted_bills($page_item_no)
	{
		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		//including manual
		$query_str= "SELECT count(*) as total_row 
						FROM bill b 
						LEFT JOIN customer c ON c.customer_no = b.customer_no 
						LEFT JOIN bill_einvoice be ON (be.bill_no = b.bill_no) 
						LEFT JOIN consolidated_detail cd ON (cd.bill_no = b.bill_no) 
						LEFT JOIN consolidated_einvoice ce ON (ce.inv_no = cd.inv_no) 
						WHERE (be.einvoice_uuid IS NULL OR be.einvoice_status NOT IN ('P', 'S') ) AND ( ce.einvoice_uuid IS NULL OR ce.einvoice_status NOT IN ('W', 'P', 'S') )
						AND b.is_void= 0 ";
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query = $this->db->query("SELECT c.customer_no, c.name,  
											b.bill_no, b.bill_date, b.balance, b.amount, b.is_manual 
									FROM bill b 
									LEFT JOIN customer c ON c.customer_no = b.customer_no 
									LEFT JOIN bill_einvoice be ON (be.bill_no = b.bill_no) 
									LEFT JOIN consolidated_detail cd ON (cd.bill_no = b.bill_no) 
									LEFT JOIN consolidated_einvoice ce ON (ce.inv_no = cd.inv_no) 
									WHERE (be.einvoice_uuid IS NULL OR be.einvoice_status NOT IN ('P', 'S') ) AND ( ce.einvoice_uuid IS NULL OR ce.einvoice_status NOT IN ('W', 'P', 'S') ) 
									AND b.is_void= 0 ORDER BY b.bill_date DESC, c.customer_no ASC LIMIT $page_item_no, ".$_SESSION['config']['max_page_item']);
		if ($query->num_rows() > 0){
			$return_val['row'] = $query->result_array();
		}
		return $return_val;

	}

	function insert_consolidated_head( $arr )
	{
		$sql = "REPLACE INTO `consolidated_einvoice` VALUES (?,?,?,?,?,?,?,?,?,?,?) ";

		$values_array = array(
			$arr['inv_no'],
			$arr['inv_date'],
			$arr['amount'],
			$arr['tax'],
			$arr['einvoice_no'],
			$arr['einvoice_submission_id'],
			$arr['einvoice_uuid'],
			$arr['einvoice_status'],
			$arr['einvoice_longid'],
			date('Y-m-d H:i:s'),
			0
		);

		$this->db->query($sql, $values_array);
	}

	function insert_consolidated_detail( $arr )
	{
		$sql = "INSERT INTO `consolidated_detail` (inv_no, bill_no) VALUES (?,?) ";

		$values_array = array(
			$arr['inv_no'],
			$arr['bill_no'],
		);

		$this->db->query($sql, $values_array);
	}

	function update_consolidated_einvoice( $inv_no, $arr )
	{
		$sql = "UPDATE `consolidated_einvoice` SET einvoice_submission_id = ?, einvoice_uuid = ?, einvoice_status = ?, einvoice_longid = ? WHERE inv_no = ? ";

		$values_array = array(
			$arr['einvoice_submission_id'],
			$arr['einvoice_uuid'],
			$arr['einvoice_status'],
			$arr['einvoice_longid'],
			$inv_no,
		);

		$this->db->query($sql, $values_array);
	}

	/**
	 * Insert e-invoice submission log
	 *
	 * @param array $data  Expected keys:
	 *   - bill_no          (string)
	 *   - document_type    (string: INV | CN | DN)
	 *   - controller_name  (string|null)
	 *   - function_name    (string|null)
	 *   - submission_uid   (string|null)
	 *   - uuid             (string|null)
	 *   - response         (string|null)
	 *   - error_message    (string|null)
	 *
	 * @return bool
	 */
	public function insert_log(array $data): bool
	{
		$defaults = [
			'document_type'  => 'INV',
			'controller_name' => null,
			'function_name'   => null,
			'submission_uid'  => null,
			'uuid'            => null,
			'response'        => null,
			'error_message'   => null,
		];

		$data = array_merge($defaults, $data);

		return $this->db->insert('einvoice_log', $data);
	}

}