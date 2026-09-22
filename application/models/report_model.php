<?php

class Report_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Retrieve and process detailed billing information with subtotals and grand totals.
	 *
	 * This function builds a SQL query based on dynamic filters and ordering,
	 * executes it, and structures the result into categorized billing data.
	 *
	 * @param string $query_where  The SQL WHERE conditions to filter billing data.
	 * @param string $order_by     (Optional) The column used for sorting results.
	 *                             Accepted values: 'bill_no', 'bill_date', 'customer_name'.
	 * @param string $order_type   (Optional) Sort direction: 'ASC' or 'DESC'. Default is 'ASC'.
	 *
	 * @return array An associative array containing:
	 *               - 'bill_type': Mapping of bill type IDs to their names.
	 *               - 'billing': Categorized billing entries.
	 *               - 'subtotal': Category-wise totals per bill type.
	 *               - 'grandtotal': Combined totals across all categories.
	 */
	function detail_of_billing($query_where, $order_by = '', $order_type = '')
	{
		$order_type = $order_type ?: 'ASC';

		switch ($order_by) {
			case 'bill_no':
				$order_by = " ORDER BY b.bill_no $order_type, bt.name ASC, c.name ASC ";
				break;
			case 'bill_date':
				$order_by = " ORDER BY b.bill_date $order_type, b.bill_no ASC, bt.name ASC, c.name ASC ";
				break;
			case 'customer_name':
				$order_by = " ORDER BY c.name $order_type, b.bill_no ASC, bt.name ASC ";
				break;
			case 'building':
				$order_by = " ORDER BY c.building $order_type, c.name ASC, b.bill_no ASC ";
				break;
			default:
				$order_by = " ORDER BY c.building ASC, b.bill_no ASC, bt.name ASC, c.name ASC ";
				break;
		}

		$query_str = "SELECT 
							b.bill_no,
							b.bill_date,
							b.customer_no,
							bd.bill_no AS detail_bill_no,
							bd.bill_type,
							bd.amount,
							bd.tax_amount,
							bt.name AS bt_name,
							c.name AS customer_name,
							cs.status,
							c.category,
							c.building,
							(bd.amount) AS total_amount
						FROM bill b
						INNER JOIN bill_detail bd ON bd.bill_no = b.bill_no
						INNER JOIN sys_bill_type bt ON bt.bill_type_id = bd.bill_type
						INNER JOIN customer c ON c.customer_no = b.customer_no
						LEFT JOIN (
							SELECT 
								customer_no, 
								status
							FROM customer_status cs1
							WHERE cs1.status_id = (
								SELECT MAX(cs2.status_id)
								FROM customer_status cs2
								WHERE cs2.customer_no = cs1.customer_no
							)
						) cs ON cs.customer_no = c.customer_no
						$query_where
						$order_by";

		$result = $this->db->query($query_str);
		$billing_info = $result->result_array();

		$combined = [];

		foreach ($billing_info as $bill) {
			$bill_no = $bill['bill_no'];
			if (!isset($combined[$bill_no])) {
				$combined[$bill_no] = [
					'bill_no' => $bill_no,
					'bill_date' => $bill['bill_date'],
					'customer_no' => $bill['customer_no'],
					'customer_name' => $bill['customer_name'],
					'status' => $bill['status'],
					'category' => $bill['category'],
					'building' => $bill['building'],
					'total_amount' => [],
					'bill_type' => [],
					'bt_name' => [],
					'other_bill_type_total_amount_in_print' => '',
					'all_item_total_amount' => 0.00
				];
			}

			$combined[$bill_no]['all_item_total_amount'] += $bill['total_amount'];

			$bill_type = $bill['bill_type'];
			$index = array_search($bill_type, $combined[$bill_no]['bill_type']);
			if ($index === false) {
				$combined[$bill_no]['bill_type'][] = $bill_type;
				$combined[$bill_no]['bt_name'][] = $bill['bt_name'];
				$combined[$bill_no]['total_amount'][] = $bill['total_amount'];
			} else {
				$combined[$bill_no]['total_amount'][$index] += $bill['total_amount'];
			}
		}

		$billing_info = array_values($combined);

		/*
		echo "<pre>";
		print_r($billing_info);
		echo "</pre>";
		exit;
		*/

		$bill_type_list = $this->common_model->get_bill_type_list();
		$bill_type_ids = array_column($bill_type_list, 'bill_type_id');

		$categories = ['Residential', 'Business', 'Wholesale', 'DIA', 'Other'];
		$billing = ['billing' => [], 'subtotal' => [], 'grandtotal' => [], 'bill_type' => []];

		$category_map = [
			'r' => 'Residential',
			'b' => 'Business',
			's' => 'Business(R)',
			'w' => 'Wholesale',
			'd' => 'DIA',
			'e' => 'DIA(R)'
		];

		foreach ($billing_info as &$row) {
			$building = $row['building'];
			$cat_name = $category_map[$row['category']] ?? 'Other';

			if (!isset($billing['billing'][$building])) {
				$billing['billing'][$building] = [];
			}
			if (!isset($billing['subtotal'][$building])) {
				$billing['subtotal'][$building] = [];
			}

			foreach ($row['bill_type'] as $idx => $bill_type_id) {
				$amount = $row['total_amount'][$idx];
				$billing['bill_type'][$building][$bill_type_id] = $row['bt_name'][$idx];

				/////////////////// PRINT MODE ///////////////////
				$bill_type_keys = array_keys($billing['bill_type'][$building]);
				if (count($bill_type_keys) > 3 && in_array((int)$bill_type_id, array_slice($bill_type_keys, 3))) {
					$row['other_bill_type_total_amount_in_print'] =
						($row['other_bill_type_total_amount_in_print'] === '' 
							? 0.00 : 
							$row['other_bill_type_total_amount_in_print'])
						+ $amount;
					
					$billing['subtotal'][$building][$cat_name]['other_for_print_subtotal'] =
						($billing['subtotal'][$building][$cat_name]['other_for_print_subtotal'] ?? 0) + $amount;
				}
				//////////////////////////////////////////////////

				$billing['subtotal'][$building][$cat_name][$bill_type_id] =
					($billing['subtotal'][$building][$cat_name][$bill_type_id] ?? 0) + $amount;
				$billing['subtotal'][$building][$cat_name]['category_subtotal'] =
					($billing['subtotal'][$building][$cat_name]['category_subtotal'] ?? 0) + $amount;
			}

			$billing['billing'][$building][$cat_name][] = $row;
		}

		foreach ($billing['subtotal'] as $building => $data) {
			foreach ($bill_type_ids as $i) {
				foreach ($categories as $cat) {
					$billing['subtotal'][$building][$cat][$i] = $billing['subtotal'][$building][$cat][$i] ?? 0;
				}
				$category_grand_total =
					($billing['subtotal'][$building]['Residential'][$i] ?? 0) +
					($billing['subtotal'][$building]['Business'][$i] ?? 0) +
					($billing['subtotal'][$building]['Wholesale'][$i] ?? 0) +
					($billing['subtotal'][$building]['DIA'][$i] ?? 0);

				$billing['grandtotal'][$building][$i] = $category_grand_total;
				$billing['grandtotal'][$building]['category_grand_total'] =
					($billing['grandtotal'][$building]['category_grand_total'] ?? 0) + $category_grand_total;
			}

			$other_for_print_grand_total =
				($billing['subtotal'][$building]['Residential']['other_for_print_subtotal'] ?? 0) +
				($billing['subtotal'][$building]['Business']['other_for_print_subtotal'] ?? 0) +
				($billing['subtotal'][$building]['Wholesale']['other_for_print_subtotal'] ?? 0) +
				($billing['subtotal'][$building]['DIA']['other_for_print_subtotal'] ?? 0);

			$billing['grandtotal'][$building]['other_for_print_grand_total'] =
				($billing['grandtotal'][$building]['other_for_print_grand_total'] ?? 0) + $other_for_print_grand_total;
		}

		$summary_categories = array_filter($categories, function($cat) use ($billing) {
			foreach ($billing['subtotal'] as $data) {
				if (!empty($data[$cat]['category_subtotal'])) return true;
			}
			return false;
		});

		$category_totals = array_fill_keys($summary_categories, 0);
		$overall_grand_total = 0;

		foreach ($billing['grandtotal'] as $building_no => $building_totals) {
			if (!isset($billing['subtotal'][$building_no])) continue;
			foreach ($summary_categories as $cat) {
				$category_total = $billing['subtotal'][$building_no][$cat]['category_subtotal'] ?? 0;
				$category_totals[$cat] += $category_total;
				$overall_grand_total += $category_total;
			}
		}

		$billing['summary_categories'] = $summary_categories;
		$billing['category_totals'] = $category_totals;
		$billing['overall_grand_total'] = $overall_grand_total;

		return $billing;
	}

	/* Sales Report */
	/* Copied from details of billing except certain changes */
	function sales_report($query_where, $order_by = '', $order_type = '')
	{
		$order_type = $order_type ?: 'ASC';

		switch ($order_by) {
			/*case 'bill_no':
				$order_by = " ORDER BY b.bill_no $order_type, bt.name ASC, c.name ASC ";
				break;
			case 'bill_date':
				$order_by = " ORDER BY b.bill_date $order_type, b.bill_no ASC, bt.name ASC, c.name ASC ";
				break;
			case 'customer_name':
				$order_by = " ORDER BY c.name $order_type, b.bill_no ASC, bt.name ASC ";
				break;
			case 'building':
				$order_by = " ORDER BY c.building $order_type, c.name ASC, b.bill_no ASC ";
				break;*/
			default:
				$order_by = " ORDER BY c.building ASC, c.name ASC ";
				break;
		}

		$query_str = "
					SELECT c.customer_no, c.name, c.package, c.package_name, c.building, 
					c.category, c.monthly_charge, IFNULL(cac.otc_charges,0) AS otc_charges   
					FROM customer c 
					LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) 
					LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) csa ON (csa.customer_no = c.customer_no) 
					LEFT JOIN (
						SELECT customer_no, sum(amount) AS otc_charges from customer_additional_charge group by customer_no 
					) cac ON (cac.customer_no = c.customer_no) 
					WHERE ( cs.status = 'A' OR cs.status = 'S' ) $query_where 
					ORDER BY c.name 
					";

		$result = $this->db->query($query_str);
		$customer_charge_info = $result->result_array();

		//for loop, just group the buildings first dimension of array, then each building, group by category 'r', 'b' etc... then do sum on monthly charges + other charges per month

		//totals designed like details of billing but also do a count, display count in a subtable above

		$customer_info = [];

		foreach ($customer_charge_info as $customer_rec) {
			$customer_info[$customer_rec['building']][$customer_rec['category']][] = $customer_rec;
		}

		/*
		echo "<pre>";
		print_r($customer_info);
		echo "</pre>";
		exit;
		*/

		return $customer_info;
	}

	function sales_report_pkg_count($query_where, $order_by = '', $order_type = '')
	{
		$query_str = "
		SELECT z.*, p.monthly_charge, p.name AS package_name FROM 
		(
			SELECT c.package, count(*) AS cnt 
			FROM customer c 
			LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no) 
			LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csa ON (csa.customer_no = c.customer_no) 
			LEFT JOIN (
			SELECT customer_no, sum(amount) AS otc_charges from customer_additional_charge group by customer_no 
			) cac ON (cac.customer_no = c.customer_no) 
			WHERE ( cs.status = 'A' OR cs.status = 'S' ) $query_where 
			GROUP BY c.package 
		) z 
		LEFT JOIN package p ON (z.package = p.package_no)  
		ORDER BY p.monthly_charge DESC
		";

		$result = $this->db->query($query_str);
		$package_count = $result->result_array();

		return $package_count;

	}

	function prepare_ifca_export( $date_from='', $date_to='' )
	{
		$query_where = '';
		$query_where2 = '';
		$query_where3 = '';

		if (empty($date_from) || empty($date_to)) {
			return array();
		}

		$query_where .= " AND ( b.bill_date BETWEEN '$date_from' AND '$date_to' )";
		$query_where2 .= " AND ( p.tranx_date BETWEEN '$date_from' AND '$date_to' ) ";
		$query_where3 .= " AND ( d.comm_date BETWEEN '$date_from' AND '$date_to' ) ";

		//for manual billing only select those that approve - supposed approve if the record is in the bill table
		//Val 14-08-25 - Add in agent commission as well
		
		$query_str = " 	SELECT (bd.amount + bd.tax_amount) AS amount, bd.tranx_date, b.bill_no AS inv_no, bd.remark, sbt.name AS bill_type_name, b.customer_no, c.category, 'bill' AS tranx_type, bd.bill_type AS bill_type_id, 0 AS payment_source       
						FROM bill_detail bd 
						LEFT JOIN bill b ON (b.bill_no = bd.bill_no) 
						LEFT JOIN sys_bill_type sbt ON (bd.bill_type = sbt.bill_type_id) 
						LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
						WHERE 1=1 
						$query_where 
						AND b.is_void = '0' 
					UNION (
						SELECT p.amount, p.tranx_date, p.payment_no AS inv_no, p.remark, sbt2.name AS bill_type_name, p.customer_no, c2.category, 'payment' AS tranx_type, p.bill_type AS bill_type_id, p.payment_source        
						FROM payment p 
						LEFT JOIN sys_bill_type sbt2 ON (p.bill_type = sbt2.bill_type_id) 
						LEFT JOIN customer c2 ON (p.customer_no = c2.customer_no) 
						WHERE 1=1 
						$query_where2 
					) 
					UNION (
						SELECT d.amount, d.comm_date AS trax_date, d.comm_no AS inv_no, comm_desc AS remark, 
						'Agent Commission' AS bill_type_name, c3.customer_no, c3.category, 'agent_comm' AS trax_type, 
						99 AS bill_type_id, 0 AS payment_source  
						FROM dealer_comm_record d 
						LEFT JOIN customer c3 ON (d.customer_no = c3.customer_no) 
						WHERE 1=1 
						$query_where3
					)
						ORDER BY tranx_date ASC, customer_no ASC ";

		$result			= $this->db->query($query_str);	
		$billing_info	= $result->result_array();

		return $billing_info;

	}
	
	function get_bill_adjustment_left_join_customer($query_where)
	{
		$return_val = array();
		$query_str 	= "SELECT ba.bill_type, SUM(ba.amount) as amount, c.category " . 
						"FROM bill_adjustment ba " . 
						"LEFT JOIN customer c ON ( " . 
							"(c.customer_no = ba.customer_no AND ba.adjust_by = 'i') " . 
							"OR (c.building = ba.building AND ba.adjust_by = 'b') " . 
						") " . 
						$query_where . 
						"GROUP BY ba.bill_type, c.category " . 
						"ORDER BY ba.bill_type, c.category ";			
						
		$query = $this->db->query($query_str);
		$return_val['result_array']	= $query->result_array();		
		return $return_val;
	}
	
	function get_customer_by_cust_no($txt_customer_no)
	{
		$txt_customer_no = $this->db->escape_str($txt_customer_no);
		$return_val = array();
		$query_str = "SELECT c.customer_no, c.name, c.login_username, c.package_name " . 
						"FROM customer c " .
						"WHERE c.customer_no = '$txt_customer_no' ";
		$query = $this->db->query($query_str);
		
		$return_val['result_array'] = $query->result_array();
		$return_val['num_rows'] = $query->num_rows();
		$return_val['row_array'] = $query->row_array();
		
		
		return $return_val;
	}
	
	
	
	function get_payment_inner_join_customer($query_where)
	{
		$return_val	= array();
		$query_str	= "SELECT p.customer_no, MAX(p.pay_date) as pay_date, 
						c.category 
						FROM payment p 
						INNER JOIN customer c ON p.customer_no = c.customer_no 
						WHERE p.pay_date < '" . date('Y-m-1') . "' 
						$query_where 
						GROUP BY c.category, p.customer_no ORDER BY p.customer_no ";
		$query		= $this->db->query($query_str);
		$return_val['result_array'] = $query->result_array();		
		return $return_val;
	}
	
	function get_bill_inner_join_customer_order_by_bill_no($query_where)
	{		
		$return_val = array();
		$query_str 	= "SELECT b.bill_no, b.bill_date, b.amount, b.customer_no, " .
						"c.category, c.name as customer_name " .
						"FROM bill b " .
						"INNER JOIN customer c ON b.customer_no = c.customer_no " .
						/* hmm??? join below for what purpose???? slow down query
						"INNER JOIN ( " .
							"SELECT DISTINCT customer_no " .
							"FROM bill " .
							"INNER JOIN ( " .
									"SELECT MAX(bill_no) as bill_no 
									FROM bill
									GROUP BY customer_no " .
								") " .
							"AS aa ON bill.bill_no = aa.bill_no " .
							"WHERE bill_date <= '" . date('Y-m-01') . "' " .
							"AND balance > 0 " .
							"GROUP BY customer_no ) ".
						"AS a ON b.customer_no = a.customer_no ".
						*/
						"WHERE b.bill_date <= '" . date('Y-m-1') . "' " .
						$query_where .
						"ORDER BY c.category, b.customer_no, b.bill_date, b.bill_no ";
						
		$query							= $this->db->query($query_str);
		$return_val['num_rows'] 		= $query->num_rows();
		$return_val['row_array'] 		= $query->row_array();	
		$return_val['result_array'] 	= $query->result_array();		
		return $return_val;
	}
	
	/**
	 * Retrieves billing information joined with customer and payment data.
	 * * @param string $query_where Extra WHERE conditions (should be pre-sanitized)
	 * @param string $query_order Custom ORDER BY clause
	 * @return array Result array containing 'result_array'
	 */
	function get_bill_inner_join_customer($query_where, $query_order = "")
	{
		if (empty($query_order)) {
			$query_order = "ORDER BY c.category, b.bill_date, b.customer_no";
		}

		$first_day_of_month = date('Y-m-01');
		$today = date('Y-m-d');

		$sql = "
			SELECT 
				b.customer_no, b.bill_date, b.bill_due_date, b.previous_balance, 
				b.payment_received, b.amount, IF((b.balance - IFNULL(p2.amount,0) - IFNULL(p3.amount,0))<0,0,(b.balance - IFNULL(p2.amount,0) - IFNULL(p3.amount,0))) AS balance, 
				c.category, c.name AS customer_name, c.bill_cycle_month,
				IFNULL(p.pay_date, '') AS last_pay_date,
				IFNULL(p.pay_date, '') AS overdue, p2.amount AS paid_amt 
			FROM bill b
			INNER JOIN (
				SELECT MAX(bill_no) as bill_no
				FROM bill 
				WHERE bill_date < ? 
				AND is_void = 0 
				GROUP BY customer_no
			) AS a ON b.bill_no = a.bill_no
			INNER JOIN customer c ON b.customer_no = c.customer_no
			LEFT JOIN (
				SELECT customer_no, MAX(pay_date) AS pay_date
				FROM payment 
				WHERE pay_date <= ?
				GROUP BY customer_no
			) p ON p.customer_no = b.customer_no 
			LEFT JOIN (
				SELECT ref_no, SUM(amount) AS amount 
				FROM payment 
				WHERE pay_date <= ? and ref_no != 0 and ref_no IS NOT NULL 
				GROUP BY ref_no 
			) p2 ON p2.ref_no = b.bill_no 
			LEFT JOIN (
				SELECT customer_no, SUM(amount) AS amount 
				FROM payment 
				WHERE pay_date <= ? AND processed = 0 
				GROUP BY customer_no 
			) p3 ON p3.customer_no = b.customer_no 
			WHERE (b.balance - IFNULL(p2.amount,0) - IFNULL(p3.amount,0) - b.amount) > 0  
			$query_where
			$query_order
		";

		$query = $this->db->query($sql, [$first_day_of_month, $today, $today, $today]);

		return [
			'result_array' => $query->result_array()
		];
	}

	/* Update Overdue Report, Val 03-07-26 - factors in grace period settings and customer by payment term */
	function get_overdue_list($query_where, $query_order = "")
	{
		//get config

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name','company_full_name','company_phone','company_email','from_name', 
			'reminder_notice', 'reminder_overdue', 'suspension_grace_period', 'reminder_notice2', 'reminder_overdue2', 'bill_generate_day')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$reminder_notice         	= (int) ($cfg['reminder_notice'] ?? 0);
		$reminder_overdue      	= (int) ($cfg['reminder_overdue'] ?? 0);
		$suspension_grace_period  	= (int) ($cfg['suspension_grace_period'] ?? 0);

		$reminder_notice2  	= (int) ($cfg['reminder_notice2'] ?? 0);
		$reminder_overdue2  	= (int) ($cfg['reminder_overdue2'] ?? 0);

		$bill_generate_day = (int) ($cfg['bill_generate_day'] ?? 1);

		$run_date = date('Y-m-d');
		$run_time = strtotime($run_date . ' 12:00:00');

		if (empty($query_order)) {
			$query_order = "ORDER BY c.customer_no";
		}

		$return_arr = array();

		$sql = "SELECT
					c.profile_id,
					c.customer_no,
					IF(c.category!='r',p.comp_name,p.acc_name) AS customer_name,
					p.pic_mobile,
					p.pic_email_1,
					p.pic_email_2,
					p.telegram_id,
					b.bill_date,
					b.balance, 
					(b.balance - IFNULL(payment4.unprocessed_amount,0)) AS updated_balance,
					IFNULL(cs.status, 'P') AS latest_status,
					IFNULL(payment.pay_date, '') AS last_payment_date, 
					c.payment_term, 
					b.bill_due_date, 
					c.category,
					csa.transact_date AS first_activate   
				FROM customer c
				LEFT JOIN profile p ON p.acc_id = c.profile_id
				LEFT JOIN (
					SELECT b1.*
					FROM bill b1
					JOIN (
						SELECT customer_no, MAX(bill_date) AS max_date
						FROM bill
						GROUP BY customer_no
					) b2 
					ON b1.customer_no = b2.customer_no 
					AND b1.bill_date = b2.max_date
				) b ON b.customer_no = c.customer_no
				LEFT JOIN (
					SELECT p1.*
					FROM payment p1
					JOIN (
						SELECT customer_no, MAX(idx) AS max_idx
						FROM payment 
						WHERE bill_type NOT IN (4,11,14) 
						GROUP BY customer_no
					) p2 ON p1.customer_no = p2.customer_no AND p1.idx = p2.max_idx
				) payment ON payment.customer_no = c.customer_no
				LEFT JOIN (
					SELECT cs1.*
					FROM customer_status cs1
					JOIN (
						SELECT customer_no, MAX(status_id) AS max_status_id
						FROM customer_status
						GROUP BY customer_no
					) cs2 ON cs1.status_id = cs2.max_status_id
				) cs ON cs.customer_no = c.customer_no 
				LEFT JOIN (
					select c4.customer_no, c4.`name`, sum(p4.amount) AS unprocessed_amount 
					from payment p4 
					left join customer c4 on (c4.customer_no = p4.customer_no) 
					where p4.processed = 0 AND bill_type NOT IN (4,11,14) group by p4.customer_no
				) payment4 ON payment4.customer_no = c.customer_no 
				LEFT JOIN (
					SELECT acs.* FROM customer_status acs 
					JOIN (
						SELECT customer_no, MAX(status_id) AS max_status_id 
						FROM customer_status 
						WHERE `status` = 'A' 
						GROUP BY customer_no
					) bcs ON (acs.status_id = bcs.max_status_id) 
				) csa ON (csa.customer_no = c.customer_no) 
				WHERE 1=1  
				{$query_where}";

		//echo $sql; exit;

		$rows = $this->db->query($sql)->result_array();

		foreach ($rows as $r) {

			$do_row = false;

			//cond 1 = first notice after bill issued
			$cond1 = $reminder_notice;
			//cond 2 = second notice after bill issued (will upgrade to x times in future)
			$cond2 = $reminder_notice + $reminder_notice2;
			//cond 3 = first overdue notice after term
			$cond3 = $r['payment_term'] + $reminder_overdue;
			//cond 4 = second overdue notice after term (will upgrade to x times in future)
			$cond4 = $r['payment_term'] + $reminder_overdue + $reminder_overdue2;
			//cond 5 = suspension grace... concurrent with overdue notice
			$cond5 = $r['payment_term'] + $suspension_grace_period;

			$last_payment_date = !empty($r['last_payment_date'])
				? date('Y-m-d', strtotime($r['last_payment_date']))
				: '';

			//if terminated, straight away show
			if ($r['latest_status'] == 'T') {
				$return_arr[] = $r;
				continue;
			}

			if (!empty($last_payment_date)) {

				$days_since_payment = (strtotime(date('Y-m-d')) - strtotime($last_payment_date)) / 86400;

				$last_payment_ts = strtotime($last_payment_date);
				$now_ts          = time();

				$payment_within_preview_bill_period =
					($last_payment_ts >= strtotime(date('Y-m-0'.$bill_generate_day, strtotime('first day of last month'))) &&
					$last_payment_ts <= $now_ts); // check if payment made within 7th of last month to current date

				//if true = customer paid for this month already
				$payment_within_current_bill_period =
					($last_payment_ts >= strtotime(date('Y-m-0'.$bill_generate_day, strtotime('first day of this month'))) &&
					$last_payment_ts <= $now_ts); // check if payment made within 7th of this month to current date

				$current_month = date('Y-m');
				$sql = "SELECT bill_no, bill_date, balance, payment_received
						FROM bill
						WHERE customer_no = '{$r['customer_no']}'
						AND balance > 0
						AND DATE_FORMAT(bill_date, '%Y-%m') = '{$current_month}'
						ORDER BY bill_date ASC
						LIMIT 1";

				$bill = $this->db->query($sql)->row_array();
				$payment_received = empty($bill) ? 0 : ($bill['payment_received'] ?? 0);

				if ($payment_within_current_bill_period) continue;

				if ($days_since_payment >= $cond3 && !$payment_within_preview_bill_period && $payment_received <= 0) {

					$last_month = date('Y-m', strtotime('first day of last month'));
					$sql = "SELECT bill_no, bill_date, balance, bill_due_date 
							FROM bill
							WHERE customer_no = '{$r['customer_no']}'
							AND balance > 0
							AND DATE_FORMAT(bill_date, '%Y-%m') = '{$last_month}'
							ORDER BY bill_date ASC
							LIMIT 1";

					$bill = $this->db->query($sql)->row_array();
					//last month bill is 0 - should we continue? this means that notice been sent but previous bill still unpaid
					if (empty($bill) || $bill['balance'] <= 0) continue;

					$days_since_bill = floor(($run_time - strtotime(date('Y-m-0'.$bill_generate_day, strtotime($bill['bill_date'])) . ' 12:00:00')) / 86400);

					if ($days_since_bill >= $cond4) {
						$do_row = true;
					} elseif ($days_since_bill >= $cond3) {
						$do_row = true;
					}

				} else {
					//only cond3 and cond4 will be counted
					continue;
				}

			} else {

				$sql = "SELECT bill_no, bill_date, balance, bill_due_date 
						FROM bill
						WHERE customer_no = '{$r['customer_no']}'
						AND balance > 0
						ORDER BY bill_date ASC
						LIMIT 1";

				$bill = $this->db->query($sql)->row_array();
				if (empty($bill) || $bill['balance'] <= 0) continue;

				$days_since_bill = floor(($run_time - strtotime(date('Y-m-0'.$bill_generate_day, strtotime($bill['bill_date'])) . ' 12:00:00')) / 86400);

				//check for last month bill for cond3 and cond4 condition
				$last_month = date('Y-m', strtotime('first day of last month'));
				$sql = "SELECT bill_no, bill_date, balance, bill_due_date 
						FROM bill
						WHERE customer_no = '{$r['customer_no']}'
						AND balance > 0
						AND DATE_FORMAT(bill_date, '%Y-%m') = '{$last_month}'
						ORDER BY bill_date ASC
						LIMIT 1";

				$last_bill = $this->db->query($sql)->row_array();

				if ($days_since_bill >= $cond4) {
					$do_row = true;
				} elseif ($days_since_bill >= $cond3) {
					$do_row = true;
				}

			}

			if ($do_row) {
				$return_arr[] = $r;
			}

		}

		return [
			'result_array' => $return_arr 
		];

	}

	function get_not_yet_activated_customers($sel_status="", $sel_installation="all")
	{

		$return = [];

		$reg_where = "";
		$so_where = "";
		$cus_where = "";

		if ($sel_installation != 'all') {
			
			switch ($sel_installation) {

				case 'upcoming':
					$reg_where .= " AND preferred_install_datetime >= NOW() ";
					$so_where .= " AND preferred_install_datetime >= NOW() ";
					$cus_where .= " AND preferred_install_datetime >= NOW() ";
					break;

				case 'overdue':
					$reg_where .= " AND preferred_install_datetime < NOW() ";
					$so_where .= " AND preferred_install_datetime < NOW() ";
					$cus_where .= " AND preferred_install_datetime < NOW() ";
					break;

				case 'unscheduled':
					$reg_where .= " AND preferred_install_datetime IS NULL ";
					$so_where .= " AND preferred_install_datetime IS NULL ";
					$cus_where .= " AND preferred_install_datetime IS NULL ";
					break;
			}
		}
		

		$sql = "
		SELECT `name`, comp_name, icno, ssm, phone, email, 'Registered' AS status_text, 
		preferred_install_datetime, unix_timestamp(preferred_install_datetime) AS unix_install 
		FROM registration r WHERE `status` = 'P' $reg_where
		";

		$query = $this->db->query($sql, []);
		foreach ($query->result_array() as $row) {
			if ($sel_status == 'Registered' || $sel_status == '' || $sel_status == 'all') {
				$return[] = $row;
			}
		}

		$sql2 = "
		SELECT cust_name AS `name`, comp_name, icno, comp_num AS ssm, bill_tel AS phone, bill_email AS email, 'SO' AS status_text, 
		preferred_install_datetime, unix_timestamp(preferred_install_datetime) AS unix_install  
		FROM so_head so 
		WHERE `status` = 1 $so_where
		";

		$query = $this->db->query($sql2, []);
		foreach ($query->result_array() as $row) {
			if ($sel_status == 'SO' || $sel_status == '' || $sel_status == 'all') {
				$return[] = $row;
			}
		}

		$sql3 = "
		SELECT p.acc_name AS `name`, p.comp_name AS `comp_name`, p.icno, p.ssm, acc_mobileno AS phone, acc_email AS email, 
		'Waiting Activation' AS status_text, c.preferred_install_datetime, unix_timestamp(preferred_install_datetime) AS unix_install  
		FROM customer c 
		LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
		) cs ON (cs.customer_no = c.customer_no) 
		LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
		WHERE cs.status = 'P' $cus_where
		";

		$query = $this->db->query($sql3, []);
		foreach ($query->result_array() as $row) {
			if ($sel_status == 'Waiting Activation' || $sel_status == '' || $sel_status == 'all') {
				$return[] = $row;
			}
		}

		usort($return, function ($a, $b) use ($sel_installation) {
			$timeA = $a['unix_install'] ?? null;
			$timeB = $b['unix_install'] ?? null;
			switch ($sel_installation) {
				case 'upcoming':
					return ($timeA <=> $timeB) ?: strcmp($a['name'] ?? '', $b['name'] ?? '');
				case 'overdue':
					return ($timeB <=> $timeA) ?: strcmp($a['name'] ?? '', $b['name'] ?? '');
				case 'unscheduled':
					$a_null = empty($timeA);
					$b_null = empty($timeB);
					if ($a_null !== $b_null) return $a_null ? -1 : 1;
					return strcmp($a['name'] ?? '', $b['name'] ?? '');
				default:
					return ($timeA ?? PHP_INT_MAX) <=> ($timeB ?? PHP_INT_MAX) ?: strcmp($a['name'] ?? '', $b['name'] ?? '');
			}
		});

		return [
			'result_array' => $return
		];
	}
	
	function get_bill_join_customer($query_where)
	{
		$return_val = array();
		$query_str = "SELECT b.customer_no, SUM(b.payment_received) as total_payment_received " .
						"FROM bill b " .
						"INNER JOIN customer c ON b.customer_no=c.customer_no " .
						"WHERE b.bill_date <= '" . date('Y-m-01') . "' " .
						$query_where .
						"GROUP BY b.customer_no " .
						"ORDER BY b.customer_no";
		$query = $this->db->query($query_str);
		$return_val['result_array']	= $query->result_array();		
		return $return_val;
	}
	
	function get_customer_join_building($txt_search,$query_where,$query_order="")
	{
		$return_val = array();
		
		if( $query_order == "" ){
			$query_order =  " ORDER BY c.customer_no " ; 
		}
		
		$query_str  = "	SELECT c.*, c.name AS customer_name, b.name as building_name , d.name AS dealer, 
							e.start_date AS package_changed_date, e.contract_start_date, e.contract_end_date, 
							cs.transact_date AS latest_status_date, cs.status AS latest_status, 
							p.acc_name AS profile_name, p.pic_mobile AS pic_mobile, p.pic_email_1 AS pic_email_1, p.pic_email_2 AS pic_email_2, 
							DATE(csa.transact_date) AS first_activate,
							csa.transact_date AS activated_date, csa.transact_date AS contract_start, 
							c.contract_month, DATE_ADD(csa.transact_date, INTERVAL c.contract_month MONTH) AS contract_end     
						FROM customer c 
						LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
						LEFT JOIN building b ON b.building_no = c.building 
						LEFT JOIN dealer d ON d.dealer_no = c.dealer 
						LEFT JOIN (
							SELECT cph1.* FROM customer_package_history cph1 
							LEFT JOIN customer_package_history cph2 
							ON (cph1.customer_no = cph2.customer_no AND cph1.idx < cph2.idx) 
							WHERE cph2.idx IS NULL
						) e ON (c.customer_no = e.customer_no) 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS min_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.min_status_id) 
						) csa ON (csa.customer_no = c.customer_no) 
						WHERE (c.customer_no LIKE '%".$this->db->escape_str($txt_search)."%' OR p.acc_name LIKE '%".$this->db->escape_str($txt_search)."%' OR p.icno LIKE '%".$this->db->escape_str($txt_search)."%') " . $query_where . $query_order;
		$query = $this->db->query($query_str);
		$return_val['num_rows'] 	= $query->num_rows();
		$return_val['row_array'] 	= $query->row_array();
		$return_val['result_array'] = $query->result_array();
		return $return_val;
	}

	function get_customer_einvoice($txt_search,$query_where,$query_order="")
	{
		$txt_search = $this->db->escape_str($txt_search);
		$return_val = array();

		if( $query_order == "" ){
			$query_order =  " ORDER BY b.bill_date DESC, c.customer_no " ; 
		}

		//UNION from einvoice CN/DN, manual billing as well

		$query_str 	= "SELECT p.acc_name AS profile_name, b.bill_no, be.einvoice_uuid, be.created_on AS submitted_on, b.bill_period, b.amount, c.customer_no, b.bill_date, be.einvoice_status            
						FROM bill b  
						LEFT JOIN bill_einvoice be ON (be.bill_no = b.bill_no) 
						LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
						LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						WHERE (c.customer_no LIKE '%$txt_search%' OR p.acc_name LIKE '%$txt_search%' OR p.icno LIKE '%$txt_search%') AND b.is_void = 0 " . $query_where . $query_order;
		$query 		= $this->db->query($query_str);
		$return_val['num_rows'] 	= $query->num_rows();
		$return_val['row_array'] 	= $query->row_array();
		$return_val['result_array'] = $query->result_array();

		return $return_val;
	}

	function get_customer_payment($txt_search,$query_where,$query_order="")
	{
		$txt_search = $this->db->escape_str($txt_search);
		$return_val = array();

		if( $query_order == "" ){
			$query_order =  " ORDER BY pr.created_at DESC " ; 
		}

		$query_str 	= "
		SELECT pr.fpx_sellerExOrderNo, pr.fpx_buyerBankId, pr.created_at AS transaction_date, pr.fpx_txnAmount, ac.fpx_debitAuthCode, ac.fpx_creditAuthCode, b.bill_no, c.customer_no, p.acc_name AS profile_name, b.bill_period, spb.bank_displayname, b.bill_date   
		FROM `paynet_ar` pr 
		LEFT JOIN (
		SELECT ac1.* FROM paynet_ac ac1 JOIN (SELECT fpx_sellerExOrderNo, MAX(idx) AS max_id FROM paynet_ac GROUP BY fpx_sellerExOrderNo) ac2 ON (ac1.idx = ac2.max_id) 
		) ac ON (pr.fpx_sellerExOrderNo = ac.fpx_sellerExOrderNo) 
		LEFT JOIN `bill` b ON (b.bill_no = pr.fpx_sellerOrderNo) 
		LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
		LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
		LEFT JOIN `sys_paynet_banklist` spb ON (spb.bank_code = pr.fpx_buyerBankId AND spb.staging = '0') 
		WHERE (c.customer_no LIKE '%$txt_search%' OR p.acc_name LIKE '%$txt_search%' OR p.icno LIKE '%$txt_search%' OR b.bill_no LIKE '%$txt_search%') " . $query_where . $query_order;

		$query 		= $this->db->query($query_str);
		$return_val['num_rows'] 	= $query->num_rows();
		$return_val['row_array'] 	= $query->row_array();
		$return_val['result_array'] = $query->result_array();

		return $return_val;

	}

	function get_reminder_report($txt_search, $date_from = '', $date_to = '', $query_order = "")
	{
		$txt_search = $this->db->escape_str($txt_search);
		$return_val = [];

		$query_where = "";
		$p_where = "";
		$b_where = "";

		if ($date_from != '') {
			$query_where .= " AND b.bill_date >= '" . date('Y-m-d', strtotime($date_from)) . "' ";
		}
		if ($date_to != '') {
			$query_where .= " AND b.bill_date <= '" . date('Y-m-d', strtotime($date_to)) . "' ";
			$b_where     .= " AND bill_date <= '" . date('Y-m-d', strtotime($date_to)) . "' ";
		}

		if ($query_order == "") {
			$query_order = " ORDER BY b.bill_date DESC, c.customer_no ASC ";
		}

		$query_str = "SELECT 
						p.acc_name AS profile_name,
						c.customer_no,
						IFNULL(c.payment_term, 0) AS payment_term,
						b.bill_no,
						old_bill.bill_no AS prev_bill_no,
						old_bill.bill_date AS prev_bill_date,
						old_bill.balance AS prev_bill_balance,
						old_bill.bill_due_date AS prev_bill_due_date,
						pay_recent.total_payment AS total_paid_within_term,
						rm1.created_at AS rm1_date, 
						rm2.created_at AS rm2_date,
						rm3.created_at AS rm3_date,
						rm4.created_at AS rm4_date, 
						IF(rm1.id IS NOT NULL,1,0) AS rm1_tick,   
						IF(rm2.id IS NOT NULL,1,0) AS rm2_tick,
						IF(rm3.id IS NOT NULL,1,0) AS rm3_tick,
						IF(rm4.id IS NOT NULL,1,0) AS rm4_tick

					FROM customer c
					LEFT JOIN profile p ON p.acc_id = c.profile_id
					LEFT JOIN (
						SELECT acs.* 
						FROM customer_status acs 
						JOIN (
							SELECT customer_no, MAX(status_id) AS max_status_id 
							FROM customer_status 
							GROUP BY customer_no
						) bcs ON acs.status_id = bcs.max_status_id
					) cs ON cs.customer_no = c.customer_no 
					LEFT JOIN (
						SELECT ba.* 
						FROM bill ba 
						JOIN (
							SELECT customer_no, MAX(bill_no) AS max_bill_no 
							FROM bill 
							GROUP BY customer_no
						) bb ON ba.bill_no = bb.max_bill_no 
						WHERE 1=1 $b_where
					) b ON b.customer_no = c.customer_no 
					LEFT JOIN (
						SELECT b1.*
						FROM bill b1
						WHERE b1.bill_no = (
							SELECT MAX(b2.bill_no)
							FROM bill b2
							WHERE b2.customer_no = b1.customer_no
							AND b2.bill_no < (
								SELECT MAX(b3.bill_no)
								FROM bill b3
								WHERE b3.customer_no = b1.customer_no
							)
						)
					) old_bill ON old_bill.customer_no = c.customer_no
					LEFT JOIN (
						SELECT 
							pm.customer_no, 
							SUM(pm.amount) AS total_payment
						FROM payment pm
						JOIN customer cc ON cc.customer_no = pm.customer_no
						JOIN bill pb ON pb.customer_no = pm.customer_no
						WHERE 
							pm.pay_date BETWEEN pb.bill_date 
							AND DATE_ADD(pb.bill_date, INTERVAL IFNULL(cc.payment_term, 0) DAY)
						GROUP BY pm.customer_no
					) pay_recent ON pay_recent.customer_no = c.customer_no
					LEFT JOIN (
						SELECT r1.* 
						FROM reminder_status r1
						JOIN (
							SELECT bill_no, customer_no, reminder_type, MAX(id) AS max_id
							FROM reminder_status
							WHERE reminder_type = 1
							GROUP BY bill_no, customer_no, reminder_type
						) r1m ON r1.id = r1m.max_id
					) rm1 ON rm1.customer_no = c.customer_no AND rm1.bill_no = b.bill_no
					LEFT JOIN (
						SELECT r2.*
						FROM reminder_status r2
						JOIN (
							SELECT bill_no, customer_no, reminder_type, MAX(id) AS max_id
							FROM reminder_status
							WHERE reminder_type = 2
							GROUP BY bill_no, customer_no, reminder_type
						) r2m ON r2.id = r2m.max_id
					) rm2 ON rm2.customer_no = c.customer_no AND rm2.bill_no = b.bill_no
					LEFT JOIN (
						SELECT r3.*
						FROM reminder_status r3
						JOIN (
							SELECT bill_no, customer_no, reminder_type, MAX(id) AS max_id
							FROM reminder_status
							WHERE reminder_type = 3
							GROUP BY bill_no, customer_no, reminder_type
						) r3m ON r3.id = r3m.max_id
					) rm3 ON rm3.customer_no = c.customer_no AND rm3.bill_no = b.bill_no
					LEFT JOIN (
						SELECT r4.*
						FROM reminder_status r4
						JOIN (
							SELECT bill_no, customer_no, reminder_type, MAX(id) AS max_id
							FROM reminder_status
							WHERE reminder_type = 4
							GROUP BY bill_no, customer_no, reminder_type
						) r4m ON r4.id = r4m.max_id
					) rm4 ON rm4.customer_no = c.customer_no AND rm4.bill_no = b.bill_no
					WHERE 
						b.bill_no IS NOT NULL
						$query_where
					$query_order
				";

		$query = $this->db->query($query_str);
		$return_val['num_rows']    = $query->num_rows();
		$return_val['row_array']   = $query->row_array();
		$return_val['result_array'] = $query->result_array();

		return $return_val;
	}

	function get_contract_reminder_report($txt_search,$date_from='',$date_to='',$query_order="")
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val = array();

		$query_where = "";

		if( $date_from != '' ) {
			$query_where .= " AND acph.contract_end_date >= '".( date('Y-m-d', strtotime($date_from)) )."' ";
		}
		if( $date_to != '' ) {
			$query_where .= " AND acph.contract_end_date <= '".( date('Y-m-d', strtotime($date_to)) )."' ";
		}

		if( $query_order == "" ){
			$query_order =  " ORDER BY acph.contract_end_date ASC " ; 
		}

		$query_str 	= "SELECT p.acc_name AS profile_name, c.customer_no, rm1.created_at AS rm1_date, 
		IF(rm1.id IS NOT NULL,1,0) AS rm1_tick, c.contract_month, acph.contract_end_date AS expiry_date, c.package_name      
						FROM customer c  
						LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
						LEFT JOIN (SELECT cph.* FROM customer_package_history cph ORDER BY cph.idx DESC) acph ON (acph.customer_no = c.customer_no AND acph.package_no = c.package)
						LEFT JOIN (
							SELECT rm1a.* FROM reminder_status rm1a JOIN (SELECT reminder_type, bill_no, customer_no, MAX(id) AS max_id FROM reminder_status GROUP BY bill_no, customer_no, reminder_type) rm1b ON (rm1a.id = rm1b.max_id) 
						) rm1 ON (rm1.customer_no = c.customer_no AND rm1.bill_no = 0 AND rm1.reminder_type = '5')  
						WHERE c.status = 'r' AND c.contract_month > 0 " . $query_where . $query_order;

		$query 		= $this->db->query($query_str);
		$return_val['num_rows'] 	= $query->num_rows();
		$return_val['row_array'] 	= $query->row_array();
		$return_val['result_array'] = $query->result_array();

		return $return_val;
	}
	
	function get_bill_by_cust_no($txt_customer_no)
	{
		$return_val = array();		
		$query_str 	= "(SELECT b.bill_date as tranx_date, b.bill_due_date, b.bill_no as tranx_no, b.amount as charge, 
							'' as payment, '' as remark, 'bill' as doc_type, b.balance 
							FROM bill b  
							WHERE b.customer_no = '$txt_customer_no' AND is_void = 0 ) 
							UNION 
							(SELECT p.pay_date, '', p.payment_no, '', p.amount, p.remark, 'payment', '' 
							FROM payment p 
							WHERE p.customer_no = '$txt_customer_no') 
							ORDER BY tranx_date, tranx_no, remark ";				
		$query 		= $this->db->query($query_str);		
		$return_val = $query->result_array();		
		return $return_val;
	}
	
	/**
	 * Generates a summary aging report for all customers.
	 * attributes payments based on processed status and billing cycle.
	 * * @param string $qwhere     Additional SQL WHERE filtering
	 * @param string $order_by   Column to sort by
	 * @param string $order_type Sort direction (ASC/DESC)
	 */
	function get_customer_aging_report($qwhere, $order_by, $order_type, $profile_id = 0, $as_date = '')
	{
		$config_record = $this->common_model->get_table('sys_config', '*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = !empty($config_record) ? (int)$config_record[0]['val'] : 1;

		$order_type = (strtoupper($order_type) == 'DESC') ? 'DESC' : 'ASC';
		$qorder = " ORDER BY customer_name " . $order_type;
		if ($order_by == 'customer_no') {
			$qorder = " ORDER BY customer_no " . $order_type;
		} else if ($order_by == 'total_ar') {
			$qorder = " ORDER BY (ALLDAYS - ((IFNULL(SUM(z.total_payment), 0) + IFNULL(pay.unprocessed_amount, 0)))) " . $order_type;
		}

		if (empty($as_date)) {
			$today = date('Y-m-d');
		} else {
			$today = $as_date;
		}
		$join_profile_query = '';
		if(!empty($profile_id)) {
			$today = date('Y-m-t');
			$join_profile_query = " INNER JOIN profile p ON p.acc_id = c.profile_id ";
			$qwhere = " AND p.acc_id = $profile_id ";
		}

		//do a check to find the last bill date
		$last_bill_date = $this->get_last_bill_based_on_date($today);

		//check for a beyond bill date after $today, if found then use it as cap, if not then all unprocessed payment also included
		$payment_where = [];

		$next_bill_date = $this->get_next_bill_process_date($today);

		if (date('Y-m', strtotime($last_bill_date)) != date('Y-m')) {
			$process_date_from = date('Y-m-d', strtotime($last_bill_date . ' +1 day'));
			$payment_where[] = "p.process_date BETWEEN '$process_date_from' AND '$today'";
		}

		if (empty($next_bill_date)) {
			$payment_where[] = "p.processed = 0";
		}

		$payment_where_sql = '';

		if (!empty($payment_where)) {
			$payment_where_sql = ' AND (' . implode(' OR ', $payment_where) . ')';
		}

		$query_str 
			= "	SELECT 
					z.customer_no, 
					z.customer_name,
					z.package_name,
					b4.idx,
					b4.bill_no,
					b4.bill_due_date,
					b4.balance,
					(IFNULL(SUM(z.total_payment), 0) + IFNULL(pay.unprocessed_amount, 0)) AS total_payment,
					SUM(z.ALLDAYS) AS ALLDAYS,
					SUM(z.2YEARS) AS 2YEARS,
					SUM(z.1YEARS) AS 1YEARS,
					SUM(z.365DAYS) AS 365DAYS,
					SUM(z.120DAYS) AS 120DAYS,
					SUM(z.90DAYS) AS 90DAYS,
					SUM(z.60DAYS) AS 60DAYS,
					SUM(z.30DAYS) AS 30DAYS
				FROM (
					SELECT 
						b.customer_no, 
						c.name AS customer_name, 
						c.package_name,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) >= 0 THEN b.payment_received ELSE 0 END) AS total_payment, 
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) >= 0 THEN b.amount ELSE 0 END) AS ALLDAYS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) > 730 THEN b.amount ELSE 0 END) AS 2YEARS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) BETWEEN 365 AND 730 THEN b.amount ELSE 0 END) AS 1YEARS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) BETWEEN 121 AND 364 THEN b.amount ELSE 0 END) AS 365DAYS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) BETWEEN 91 AND 120 THEN b.amount ELSE 0 END) AS 120DAYS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) BETWEEN 61 AND 90 THEN b.amount ELSE 0 END) AS 90DAYS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) BETWEEN 31 AND 60 THEN b.amount ELSE 0 END) AS 60DAYS,
						SUM(CASE WHEN DATEDIFF('$today', b.bill_date) BETWEEN 0 AND 30 THEN b.amount ELSE 0 END) AS 30DAYS
					FROM bill b
					INNER JOIN customer c ON b.customer_no = c.customer_no $join_profile_query
					WHERE b.bill_date BETWEEN '2016-03-01' AND '$today'
					AND b.is_void = 0
					$qwhere
					GROUP BY b.customer_no, c.name, c.package_name
				) z
				LEFT JOIN (
					SELECT p.customer_no, SUM(p.amount) AS unprocessed_amount
					FROM payment p
					WHERE p.bill_type != 4 $payment_where_sql
					GROUP BY p.customer_no
				) AS pay ON pay.customer_no = z.customer_no
				LEFT JOIN (
					SELECT b2.customer_no, MAX(b2.idx) AS idx 
					FROM bill b2 
					GROUP BY b2.customer_no
				) b3 ON b3.customer_no = z.customer_no
				LEFT JOIN bill b4 ON b4.idx = b3.idx
				GROUP BY 
					z.customer_no, 
					z.customer_name, 
					z.package_name,
					b4.idx,
					b4.bill_no,
					b4.bill_due_date,
					b4.balance,
					pay.unprocessed_amount
				$qorder
			";

		return $this->db->query($query_str)->result_array();
	}

	function get_last_bill_based_on_date($date="") {
		if (empty($date)) {
			$date = date("Y-m-d");
		}

		$return = '2016-03-01';

		$sql = "select distinct max(process_date) AS last_process_date from payment where process_date <= ?";
		$query = $this->db->query( $sql, [$date] );

		if ($query->num_rows() > 0)	{
			$row = $query->row_array();
			$return = $row['last_process_date'] ;
		}

		return $return;
	}

	function get_next_bill_process_date($date="") {
		if (empty($date)) {
			$date = date("Y-m-d");
		}

		$return = '';

		$sql = "select distinct min(process_date) AS next_process_date from payment where process_date > ?";
		$query = $this->db->query( $sql, [$date] );

		if ($query->num_rows() > 0)	{
			$row = $query->row_array();
			$return = $row['next_process_date'] ;
		}

		return $return;
	}

	/**
	 * GENERATE detailed customer aging report based on bill cycle logic in config.
	 * * This function calculates debt age based on the provided end date and 
	 * attributes payments to bills. It will handles two types of payments:
	 * 1. Processed (processed == 1): linked to bills via the 7th-to-7th billing cycle day.
	 * 2. Unprocessed (processed == 0 ): applied to the most recent bill only
	 * to avoid some race conditions with back-dated entries
	 *
	 * @param string $customer_no The unique identifier for the customer.
	 * @param string $date_from   The start date for filtering bill dates (optional).
	 * @param string $date_to     The anchor date for aging and payment window (optional).
	 * @return array              An array of bills with aging buckets and payments.
	 */
	function get_detailed_customer_aging_report($customer_no, $date_from = '', $date_to = '')
	{
		$config_record = $this->common_model->get_table('sys_config', '*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = !empty($config_record) ? (int)$config_record[0]['val'] : 1;

		if (empty($customer_no)) {
			return array();
		}

		$anchor_end = ($date_to != '') ? date('Y-m-d', strtotime($date_to)) : date('Y-m-d');
		
		$qwhere_bills = " AND b.bill_date <= " . $this->db->escape($anchor_end);
		if ($date_from != '') {
			$qwhere_bills .= " AND b.bill_date >= " . $this->db->escape(date('Y-m-d', strtotime($date_from)));
		} else {
			$qwhere_bills .= " AND b.bill_date >= '2016-03-01' ";
		}

		$query_str = "	SELECT 
							b.customer_no, 
							c.name AS customer_name, 
							b.bill_no, 
							b.bill_date,
							(IFNULL(pay_processed.total, 0) + IFNULL(pay_unprocessed.total, 0)) AS total_payment,
							b.amount AS ALLDAYS,
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) > 730 THEN b.amount ELSE 0 END AS '2YEARS',
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) BETWEEN 365 AND 730 THEN b.amount ELSE 0 END AS '1YEARS',
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) BETWEEN 121 AND 364 THEN b.amount ELSE 0 END AS '365DAYS',
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) BETWEEN 91 AND 120 THEN b.amount ELSE 0 END AS '120DAYS',
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) BETWEEN 61 AND 90 THEN b.amount ELSE 0 END AS '90DAYS',
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) BETWEEN 31 AND 60 THEN b.amount ELSE 0 END AS '60DAYS',
							CASE WHEN DATEDIFF('$anchor_end', b.bill_date) <= 30 THEN b.amount ELSE 0 END AS '30DAYS'
						FROM bill b
						INNER JOIN customer c ON b.customer_no = c.customer_no
						LEFT JOIN (
							SELECT 
								p.customer_no,
								SUM(p.amount) AS total,
								DATE_FORMAT(
									CASE 
										WHEN DAY(p.pay_date) < $bill_cycle_day 
										THEN DATE_SUB(p.pay_date, INTERVAL 1 MONTH) 
										ELSE p.pay_date 
									END, 
									'%Y-%m-01'
								) AS bill_link_date
							FROM payment p
							WHERE p.bill_type != 4 AND p.processed = 1
							GROUP BY p.customer_no, bill_link_date
						) AS pay_processed ON (pay_processed.customer_no = b.customer_no AND pay_processed.bill_link_date = DATE_FORMAT(b.bill_date, '%Y-%m-01'))
						LEFT JOIN (
							SELECT 
								p.customer_no,
								SUM(p.amount) AS total,
								(SELECT b2.bill_no 
								FROM bill b2 
								WHERE b2.customer_no = p.customer_no AND b2.is_void = 0 
								ORDER BY b2.bill_date DESC LIMIT 1) as latest_bill_no
							FROM payment p
							WHERE p.bill_type != 4 AND p.processed = 0
							GROUP BY p.customer_no
						) AS pay_unprocessed ON (pay_unprocessed.latest_bill_no = b.bill_no)
						WHERE b.is_void = 0 
						AND b.customer_no = " . $this->db->escape($customer_no) . "
						$qwhere_bills
						ORDER BY b.bill_date DESC
					";

		return $this->db->query($query_str)->result_array();
	}
	
	function get_statement_list($txt_search,$page_item_no,$query_where = '',$date_from,$date_to)
	{
		$txt_search = $this->db->escape_str($txt_search);
		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		$query_str= "SELECT count(*) as total_row 
						FROM customer c 
						INNER JOIN bill b ON c.customer_no = b.customer_no 
						INNER JOIN ( 
							SELECT MAX(bill_no) as bill_no 
							FROM bill 
							WHERE is_void = 0 
							AND bill_date >= '".$date_from."' 
							AND bill_date <= '".$date_to."' 
							GROUP BY customer_no 
						) a ON a.bill_no = b.bill_no 
						LEFT JOIN (
							SELECT  customer_no , 
									payment_received AS ttl_payment , amount AS  ttl_charge , 
									balance AS ttl_charge2 , previous_balance AS op_balance
							FROM bill 
							WHERE is_void = 0 
							AND bill_date <= '".$date_from."' 
							ORDER BY bill_date DESC LIMIT 1
						) op ON op.customer_no = c.customer_no
						WHERE (c.customer_no LIKE '%$txt_search%' 
								OR c.name LIKE '%$txt_search%' 
								OR c.nric LIKE '%$txt_search%') " . 
								$query_where .
						" AND b.bill_date >= '".$date_from."' AND b.bill_date <= '".$date_to."';";
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$Q = "SELECT c.customer_no, c.name, 
						IF(c.activated_date = 0, '', c.activated_date) as activated_date, 
						b.bill_no AS last_bill_no,
						b.bill_date as last_bill_date, b.balance , 
						( SELECT previous_balance FROM bill WHERE customer_no = c.customer_no AND bill_date <= '".$date_from."' ORDER BY bill_date DESC LIMIT 1 ) AS opening_balance
				FROM customer c 
				INNER JOIN bill b ON c.customer_no = b.customer_no 
				INNER JOIN ( 
					SELECT MAX(bill_no) as bill_no 
					FROM bill 
					WHERE is_void = 0 
					AND bill_date >= '".$date_from."' 
					AND bill_date <= '".$date_to."' 
					GROUP BY customer_no 
				) a ON a.bill_no = b.bill_no 
				WHERE (c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR c.nric LIKE '%$txt_search%') 
				" . $query_where . "
				AND b.bill_date >= '".$date_from."' 
				AND b.bill_date <= '".$date_to."' 
				ORDER BY c.customer_no LIMIT $page_item_no, 20 ";

		$query = $this->db->query( $Q );
		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}

		return $return_val;
	}
	
	function get_customer_statement( $customer_no, $date_from, $date_to ){
		
		$date_from = $date_from == '' ? '1970-01-01'  : date('Y-m-d', strtotime($date_from) ) ;
		$date_to   = $date_to   == '' ? date('Y-m-d') : date('Y-m-d', strtotime($date_to  ) ) ;
		
		$BF = " SELECT ( balance ) AS previous_balance
				FROM bill WHERE customer_no = '".$customer_no."' AND bill_date < '".$date_from."'
				ORDER BY idx DESC, bill_date DESC LIMIT 1 ";
		$query = $this->db->query( $BF );
		$return_val['opening_balance'] = '0.00';
		if ($query->num_rows() > 0)	{
			$row = $query->row_array();
			$return_val['opening_balance'] = $row['previous_balance'] ;
		}
		
		$Q = "	SELECT * FROM (
					(
						SELECT 'CHARGES' AS `trans_type`, bill_date AS `tr_date`,
								bill.amount AS `tr_charges`, '0.00' AS `tr_pay_amount` ,
								bill.bill_no AS `tr_number`, 'Monthly Charges' AS `tr_remark`
						FROM bill 
						WHERE customer_no = '".$customer_no."' 
						AND is_void = 0 
						AND bill_date BETWEEN '".$date_from."' AND '".$date_to."' 
						AND bill.amount <> 0 
					)
						UNION 
					(
						
						SELECT 'PAYMENT' AS `trans_type`, pay_date AS `tr_date`,
								'0.00' AS `tr_charges`, p.amount AS `tr_pay_amount` ,
								payment_no AS `tr_number`, 'Payment' AS `tr_remark`
						FROM payment p
						WHERE customer_no = '".$customer_no."' 
						AND processed = 0 AND p.bill_type <> 4 
						AND pay_date <= '".$date_to."' 
					)
						UNION
					(
						SELECT 'PAYMENT' AS `trans_type`, 
								CASE WHEN b.bill_date BETWEEN '".$date_from."' AND '".$date_to."' THEN bill_date 
									 ELSE pay_date END AS `tr_date` , 
								'0.00' AS `tr_charges`, p.amount AS `tr_pay_amount` ,
								payment_no AS `tr_number`, 'Payment' AS `tr_remark`
						FROM bill b
						INNER JOIN payment p ON b.bill_no = p.bill_no 
						WHERE b.customer_no = '".$customer_no."' 
						AND b.is_void = 0 
						AND ( b.bill_date BETWEEN '".$date_from."' AND '".$date_to."' 
							OR p.pay_date BETWEEN '".$date_from."' AND '".$date_to."' 
						)
						AND p.processed = 1						
					)					
					
				) z ORDER BY z.tr_date ";
				//1st query => get all bills
				//2nd query => get unprocessed payment
				//remove date range for unprosess payment
				//AND pay_date BETWEEN '".$date_from."' AND '".$date_to."' 
				//3rd query => get payment that bind to bill #
				//          => current date 31 March, bills for this month have been generated
				//			=> scenario 1 : SOA Report date from 1st Jan ~ 28 Feb , customer payment made on 15th Feb
				//			=> result : do not show bill on March
				//			=>        : but show the payment ( 15th Feb ) that bound to bill on March
				//			=> scenario 2 : SOA Report date from 1st Jan ~ 31 Mac , 
				//			=> result : show all bills & payments
			
		$query = $this->db->query( $Q );
		$return_val['trans'] = array();
		if ($query->num_rows() > 0)	{
			$return_val['trans'] = $query->result_array();
		}

		return $return_val;
		
	}
	
	//new function
	function deposit_summary($category, $status, $order_by='', $order_type='')
	{
		$return_val = array();
		$query_where = '';
		$amount = 0;
		
		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		
		if ( !empty($category) && $category !== 'all' ) {
			$query_where = "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where .= "AND cs.status = '$status' "; 
		}
		
		$order_where = "";
		$order_where2= "";
		if( $order_type == '' ){
			$order_type = 'ASC';
		}
		
		if( $order_by != "" ){
			if( $order_by == "customer_no" ){
				$order_where .= " , c.customer_no ".$order_type." , pay_date ";
				$order_where2 .= " , z.customer_no ".$order_type." , z.pay_date ";
				
			}elseif( $order_by == "pay_date" ){
				$order_where .= " , pay_date ".$order_type." , c.customer_no ";
				$order_where2 .= " , z.pay_date ".$order_type." , z.customer_no ";
			}elseif( $order_by == "customer_name" ){
				$order_where .= " , customer_name ".$order_type." , pay_date ";
				$order_where2 .= " , customer_name ".$order_type." , z.pay_date ";
			}elseif( $order_by == "building_name" ){
				$order_where .= " , building_name ".$order_type." , pay_date ";
				$order_where2 .= " , building_name ".$order_type." , z.pay_date ";
			}elseif( $order_by == "type" ){	
				$order_where .= " , bill_type ".$order_type." , pay_date ";
				$order_where2 .= " , bill_type ".$order_type." , z.pay_date ";
			}elseif( $order_by == "amount" ){
				$order_where .= " , amount ".$order_type." , pay_date ";
				$order_where2 .= " , amount ".$order_type." , z.pay_date ";				
			}
		}
		
		
		$str1 = "SELECT c.category, c.customer_no, c.name as customer_name,  b.name as building_name, 
						DATE_FORMAT(cd.deposit_date,'%Y-%m-%d') AS pay_date, cd.remark, 'DEPOSIT' AS payment_no, 
						cd.deposit AS amount, '4' AS bill_type 
						FROM customer_deposit cd 
						INNER JOIN customer c ON c.customer_no = cd.customer_no 
						LEFT JOIN building b ON b.building_no = c.building 
						LEFT JOIN sys_payment_source sps ON sps.payment_source_id = cd.payment_source 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						WHERE 1 = 1 $query_where
						ORDER BY c.category $order_where ";
						
		$str2 = "	SELECT 	c.category, c.customer_no, c.name as customer_name, b.name as building_name, 
							DATE_FORMAT(ba.tranx_date,'%Y-%m-%d') AS pay_date, ba.remark, 'DEPOSIT' AS payment_no, 
							CASE WHEN ( ba.adjust_type = 'cr' ) THEN (-1 * ba.amount ) 
								 ELSE ba.amount END AS amount, ba.bill_type 
					FROM bill_adjustment ba 
					INNER JOIN customer c ON c.customer_no = ba.customer_no 
					LEFT JOIN building b ON b.building_no = c.building 
					LEFT JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
					LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) 
					WHERE ( ba.bill_type IN ( '4' , '11' , '14' ) ) 
					$query_where
					ORDER BY c.category $order_where ";
		
		$query_str = " 	SELECT z.*, SUM( z.amount ) AS amount 
						FROM (
							( ".$str1." ) UNION ALL ( ".$str2." )
						) z 
						GROUP BY z.customer_no, z.category, z.customer_name, z.building_name, z.pay_date, 
								 z.remark, z.payment_no, z.amount, z.bill_type
						HAVING amount != 0
						ORDER BY z.category $order_where2 ";
						
		$query = $this->db->query($query_str);
		
		foreach ( $query->result_array() as $row ) {
			$category_name = $customer_category_list[$row['category']];
			$bill_type_name = $bill_type_list[$row['bill_type']];
			
			$return_val[$category_name][] = array(	"pay_date" => $row['pay_date'],
													"bill_type" =>$bill_type_name,
													"customer_no" => $row['customer_no'],
													"customer_name" => $row['customer_name'],
													"building_name" => $row['building_name'],
													"remark" => $row['remark'],
													"amount" => $row['amount'],
													);
		}
		return $return_val;
	}
	
	function get_radius_radcheck( $username = '' ){
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		$qwhere = "";
		if( $username != '' )
			$qwhere .= " AND UserName LIKE '%".$this->radius_db->escape_str( $username )."%' ";
		
		$str = " SELECT `UserName`, `Attribute`, `op`, `Value`  
					FROM `radcheck` 
					WHERE 1 = 1 $qwhere 
					ORDER BY UserName, Attribute ";
		
		$query = $this->radius_db->query( $str );
		$return_val = array();
		if ($query->num_rows() > 0)	{
			$return_val = $query->result_array();
		}
		
		return $return_val;
		
	}
	
	function get_radius_usergroup( $username = '', $groupname = '' ){
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		$qwhere = "";
		if( $username != '' ){
			//$qwhere .= " AND UserName LIKE '%".$this->radius_db->escape_str( $username )."%' ";
			$qwhere .= " AND username LIKE '%".$this->radius_db->escape_str( $username )."%' ";
		}
			
			
		if( $groupname != '' ){
			//$qwhere .= " AND GroupName LIKE '%".$this->radius_db->escape_str( $groupname )."%' ";
			$qwhere .= " AND groupname LIKE '%".$this->radius_db->escape_str( $groupname )."%' ";
		}
			
		
		/*
		$str = " SELECT `UserName`, `GroupName`, `priority`
					FROM `usergroup` 
					WHERE 1 = 1 $qwhere 
					ORDER BY UserName ";
		*/

		
		$str = " SELECT `username` AS UserName, `groupname` AS GroupName, `priority`
					FROM `radusergroup` 
					WHERE 1 = 1 $qwhere 
					ORDER BY username ";
		
		
		$query = $this->radius_db->query( $str );
		$return_val = array();
		if ($query->num_rows() > 0)	{
			$return_val = $query->result_array();
		}
		
		return $return_val;
		
	}
	
	function get_radius_login( $username = '' ){
		
		$this->radius_db = $this->load->database('radius',true,false);
		
		$return_val = array() ;
		if( $username != '' ){		
			$str = " SELECT `username`, `acctstarttime`, `acctstoptime`, `acctsessiontime`  
						FROM `radacct` 
						WHERE username = '".$this->radius_db->escape_str( $username )."' 
						ORDER BY radacctid DESC  ";
			
			$query = $this->radius_db->query( $str );
			$return_val = array();
			if ($query->num_rows() > 0)	{
				$return_val = $query->result_array();
			}
		}
		
		return $return_val;
		
	}
	
	function generate_soa_pdf( $customer_no, $date_from, $date_to, $pdf_name ){
	
		$this->load->helper('custom_helper');
		
		$statements = $this->get_customer_statement( $customer_no, $date_from, $date_to );
		
		$result = '';
		if( !empty($statements ) ){
			
			$header_data['title']		= 'print preview';
			$header_data['description']	= 'to print statement of account';
			$content_data['opening_balance'] = $statements['opening_balance'];
			$content_data['date_from']  = $date_from;
			$content_data['date_to'] 	= $date_to;
			$content_data['details']	= (empty($statements['trans']))?'':$statements['trans'];

			$this->load->model('customer_model');
			$cust_info  = $this->customer_model->get_customer($customer_no);

			$content_data['customer_no'] = $cust_info['customer_no'] ;
			if( $cust_info['bill_addr1'] != '' ){
				$content_data['display_name'] 		= $cust_info['bill_name'];
				$content_data['display_addr1'] 		= $cust_info['bill_addr1'];
				$content_data['display_addr2'] 		= $cust_info['bill_addr2'];
				$content_data['display_city'] 		= $cust_info['bill_city'];
				$content_data['display_postcode'] 	= $cust_info['bill_postcode'];
				$content_data['display_state'] 		= $cust_info['bill_state'];
			}elseif( $cust_info['inst_addr1'] != '' ){
				$content_data['display_name'] 		= $cust_info['name'];
				$content_data['display_addr1'] 		= $cust_info['inst_addr1'];
				$content_data['display_addr2'] 		= $cust_info['inst_addr2'];
				$content_data['display_city'] 		= $cust_info['inst_city'];
				$content_data['display_postcode'] 	= $cust_info['inst_postcode'];
				$content_data['display_state'] 		= $cust_info['inst_state'];
			}
			
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', '', true);
			$html .= $this->parser->parse('report/customer_statement', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			//$this->load->helper(array('dompdf', 'file'));
			
			$ci = get_instance();
			$data = $ci->pdf_create($html, '', false);
			
			if(empty($pdf_name)) $pdf_name = '['.date("MY").']SOA_'.$customer_no;
			$result = write_file('temp/email_scheduler/'.$pdf_name.'.pdf', $data);
			
		}

		return $result;
		
	}
	
	
}
