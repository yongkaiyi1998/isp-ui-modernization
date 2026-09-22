<?php

class Home_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function this_month_payment() {
		$this->db->select('COUNT(*) as payment_count, SUM(amount) as total_amount');
		$this->db->from('payment');
		$this->db->where('MONTH(pay_date)', date('m'));
		$this->db->where('YEAR(pay_date)', date('Y'));
		
		$query = $this->db->get();
		return $query->row_array();
	}

	public function last_month_bill_amount() {
		$sql = "
			SELECT SUM(balance) AS total_balance
			FROM bill
			WHERE idx IN (
				SELECT MAX(idx)
				FROM bill
				WHERE is_void != 1
				AND MONTH(bill_date) = ?
				AND YEAR(bill_date) = ?
				GROUP BY customer_no
			)
		";

		$query = $this->db->query($sql, [date('m'), date('Y')]);
		return $query->row_array();
	}


	public function new_registration_in_week() {
		$sql = "
			SELECT DATE(created_at) as reg_date, COUNT(*) as total
			FROM registration
			WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
			AND deleted_at IS NULL
			GROUP BY DATE(created_at)
			ORDER BY reg_date ASC
		";

		$query = $this->db->query($sql);
		$results = $query->result_array();

		$labels = [];
		$totals = [];

		$date_map = [];
		foreach ($results as $row) {
			$date_map[$row['reg_date']] = (int)$row['total'];
		}

		for ($i = 30; $i >= 0; $i--) {
			$date = date('Y-m-d', strtotime("-$i days"));
			$labels[] = $date;
			$totals[] = isset($date_map[$date]) ? $date_map[$date] : 0;
		}

		return [
			'labels' => $labels,
			'total'  => $totals,
			'total_amount' => array_sum($totals),
			'max' => empty($totals) ? 0 : max($totals),
			'min' => empty($totals) ? 0 : min($totals)
		];
	}

	public function count_account_by_category() {
		$this->load->model('common_model');
		$category_map  = array_column($this->common_model->get_category_list() ?? [], 'name', 'category_code');
		
		$sql = "
			SELECT c.category, COUNT(*) as total
			FROM customer c 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no) 
			WHERE cs.status IN ('A', 'S') 
			GROUP BY c.category
			ORDER BY c.category ASC
		";

		$query = $this->db->query($sql);
		$results = $query->result_array();

		$labels = [];
		$totals = [];

		foreach ($results as $row) {
			$code = strtolower($row['category']);
			$labels[] = isset($category_map[$code]) ? $category_map[$code] : strtoupper($code);
			$totals[] = (int)$row['total'];
		}

		return [
			'labels' => $labels,
			'total'  => $totals
		];
	}

	public function count_account_by_category_by_month($from="", $to="") {
		$this->load->model('common_model');
		$category_map  = array_column($this->common_model->get_category_list() ?? [], 'name', 'category_code');

		$where = "";
		if (!empty($from) && !empty($to)) {
			$where = " WHERE cs.status IN ('A', 'S') AND cs.transact_date >= '$from 00:00:00' AND cs.transact_date <= '$to 23:59:59' ";
		}
		
		$sql = "
			SELECT c.category, COUNT(*) as total
			FROM customer c 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no) 
			$where
			GROUP BY c.category
			ORDER BY c.category ASC
		";

		$query = $this->db->query($sql);
		$results = $query->result_array();

		$labels = [];
		$totals = [];

		foreach ($results as $row) {
			$code = strtolower($row['category']);
			$labels[] = isset($category_map[$code]) ? $category_map[$code] : strtoupper($code);
			$totals[] = (int)$row['total'];
		}

		return [
			'labels' => $labels,
			'total'  => $totals
		];
	}

	public function total_sales_and_payments_by_month() {
		$months = [];
		for ($i = 11; $i >= 0; $i--) {
			$date = date('Y-m-01', strtotime("-$i months"));
			$key = date('Y-m', strtotime($date));
			$label = date('M Y', strtotime($date));
			$months[$key] = $label;
		}

		$sales_data = array_fill_keys(array_keys($months), 0.00);
		$payment_data = array_fill_keys(array_keys($months), 0.00);

		$bill_sql = "
			SELECT DATE_FORMAT(bill_date, '%Y-%m') as month, SUM(amount) as total
			FROM bill
			WHERE bill_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
			AND is_void != 1
			GROUP BY DATE_FORMAT(bill_date, '%Y-%m')
		";

		$bill_query = $this->db->query($bill_sql)->result_array();
		foreach ($bill_query as $row) {
			$key = $row['month'];
			if (isset($sales_data[$key])) {
				$sales_data[$key] = (float)$row['total'];
			}
		}

		$payment_sql = "
			SELECT DATE_FORMAT(pay_date, '%Y-%m') as month, SUM(amount) as total
			FROM payment
			WHERE pay_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
			GROUP BY DATE_FORMAT(pay_date, '%Y-%m')
		";

		$payment_query = $this->db->query($payment_sql)->result_array();
		foreach ($payment_query as $row) {
			$key = $row['month'];
			if (isset($payment_data[$key])) {
				$payment_data[$key] = (float)$row['total'];
			}
		}

		return [
			'sales' => [
				'labels' => array_values($months),
				'total_amounts' => array_values($sales_data),
			],
			'payment' => [
				'labels' => array_values($months),
				'total_amounts' => array_values($payment_data),
			],
		];
	}

	public function assigned_ticket() {
		$userdata = $this->session->userdata;
		$user_id = $userdata['user']['idx'];

		$acl = $_SESSION['acl'] ?? [];
		$tt_permission_action = $acl['trouble_ticket']['actions'] ?? [];
		$tt_permission = in_array('A', $tt_permission_action) ? 'A' : (in_array('V', $tt_permission_action) ? 'V' : false);

		$this->load->model('common_model');
		$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'idx');

		$this->db->select('
			tt.tt_id,
			tt.tt_no,
			tt.datetime_open,
			tt.tt_status,
			tt.tt_assign_to,
			c.name as customer_name
		');
		$this->db->from('trouble_ticket tt');
		$this->db->join('customer c', 'c.customer_no = tt.customer_no', 'left');
		if ($tt_permission == 'V') {
			$this->db->where("(
								CONCAT(',', REPLACE(REPLACE(tt.tt_assign_to, '\"', ''), ' ', ''), ',') LIKE '%,$user_id,%' 
								OR tt.created_by = $user_id
							)");
		}

		$this->db->where("tt.tt_status !=", '0');
		$this->db->order_by('tt.created_on', 'DESC');
		$query = $this->db->get();
		$result = [];

		foreach ($query->result() as $row) {
			$color = 'grey';
			$status = '<span class="badge badge-secondary text-white-tp1">Open</span>';
			switch ($row->tt_status) {
				case 2: 
					$color = 'danger'; 
					$status = '<span class="badge badge-danger text-white-tp1">Assigned</span>';
					break;
				case 3: 
					$color = 'orange'; 
					$status = '<span class="badge badge-warning text-white-tp1">In Progress</span>';
					break;
				case 4: 
					$color = 'yellow'; 
					$status = '<span class="badge badge-yellow text-white-tp1">Solved</span>';
					break;
			}

			$result[] = [
				'tt_id'         => $row->tt_id,
				'tt_no'         => $row->tt_no,
				'datetime_open' => $row->datetime_open,
				'customer_name' => $row->customer_name,
				'assigned_user_name' => $tt_permission == 'V'
					? [$user_list[$user_id] ?? 'No user assigned']
					: (array_filter($names = array_map(fn($id) => $user_list[$id] ?? null, str_getcsv($row->tt_assign_to ?? ''))) ? array_values($names) : ['No user assigned']),
				'status'     	=> $status,
				'design'		=> [
					'colorClass'    => 'progress-bar-' . $color,
					'percentage' => $row->tt_status == 0 ? 100 : max(0, min(100, ($row->tt_status - 1) * 25)),
				]
			];
		}

		return $result;
	}

	public function open_ticket() {
		$this->load->model('common_model');
		$complaint_type_list = $this->common_model->get_tt_complaint_list();
		$list = array_column($complaint_type_list, 'tt_complaint_name', 'tt_complaint_id');
		
		$this->db->select('tt.tt_id as id, tt.tt_remark, tt.created_on, tt.tt_complaint_id, c.name as customer_name');
		$this->db->from('trouble_ticket tt');
		$this->db->join('customer c', 'c.customer_no = tt.customer_no', 'left');
		$this->db->where('tt.tt_status', 1);
		$this->db->order_by('tt.created_on', 'DESC');
		$this->db->limit(10);

		$query = $this->db->get();

		$result = [];

		foreach ($query->result() as $row) {
			$result[] = [
				'id'        => $row->id,
				'name'      => $row->customer_name ?? 'Unknown',
				'complaint_type' => $list[$row->tt_complaint_id ?? 0] ?? "-",
				'complaint' => $row->tt_remark,
				'time'      => $this->time_elapsed_string($row->created_on)
			];
		}

		return $result;
	}

	public function assigned_pending_manual_bill() {
		$userdata = $this->session->userdata;
		$user_id = $userdata['user']['idx'];

		$this->db->select('
			bd.idx AS id,
			bd.bill_draft_no,
			c.name AS customer_name,
			bd.bill_date AS date,
			bd.balance AS total_amount,
			bs.status
		');
		$this->db->from('bill_draft bd');
		$this->db->join('customer c', 'c.customer_no = bd.customer_no', 'left');

		$this->db->join('(SELECT bill_draft_no, MAX(id) AS max_id FROM bill_status GROUP BY bill_draft_no) bs_max', 'bs_max.bill_draft_no = bd.idx', 'inner');
		$this->db->join('bill_status bs', 'bs.id = bs_max.max_id', 'inner');

		// Filter where status is A or B
		// A = pending 1, B = pending 2
		$this->db->where_in('bs.status', ['A', 'B']);

		// Join docs_approver for matching user
		$this->db->join('docs_approver da', 'da.doc_ref = bd.idx AND da.doc_type = "mb" AND da.user_id = ' . (int)$user_id, 'inner');

		// Match level with status
		$this->db->where('
			(bs.status = "A" AND da.level = 1) OR
			(bs.status = "B" AND da.level = 2)
		', null, false);

		$this->db->order_by('bd.bill_date', 'DESC');
		$query = $this->db->get();

		$result = [];
		foreach ($query->result() as $row) {
			$status_badge = '';
			if ($row->status == 'A') {
				$status_badge = '<span class="badge badge-primary text-white-tp1">Pending 1</span>';
			} elseif ($row->status == 'B') {
				$status_badge = '<span class="badge badge-primary text-white-tp1">Pending 2</span>';
			}

			$result[] = [
				'id'            => $row->id,
				'bill_draft_no'	=> $row->bill_draft_no,
				'name'          => $row->customer_name,
				'date'          => $row->date,
				'total_amount'  => $row->total_amount,
				'status'        => $status_badge,
			];
		}

		return $result;
	}

	private function time_elapsed_string($datetime, $full = false) {
		$now = new \DateTime;
		$ago = new \DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = [
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		];
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	function get_dashboard()
	{
		$month_ini 			= new DateTime("first day of this month");
		$this_month_ini 	= date('Y-m-1');
		$this_month_end		= date('Y-m-t');
		$month_ini 			= new DateTime("first day of last month");
		$last_month_ini 	= $month_ini->format('Y-m-1');
		$last_month_end 	= $month_ini->format('Y-m-t');
		
		$query_str = "SELECT COUNT(c.customer_no) as counter FROM customer c";
		$query = $this->db->query($query_str);
		$data['total_customer'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter FROM customer c 
		LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no)
		WHERE cs.status = 'A'";
		$query = $this->db->query($query_str);
		$data['total_customer_registered'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter FROM customer c 
		LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no)
		WHERE cs.status = 'S'";
		$query = $this->db->query($query_str);
		$data['total_customer_suspended'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter FROM customer c 
		LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no)
		WHERE cs.status = 'T'";
		$query = $this->db->query($query_str);
		$data['total_customer_terminated'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter " . 
					"FROM customer c " . 
					"LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) " .
					"WHERE cs.status = 'A' " . 
					"AND (cs.transact_date BETWEEN '$this_month_ini' AND '$this_month_end') ";
		$query = $this->db->query($query_str);
		$data['registered'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter " . 
					"FROM customer c " . 
					"LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) " .
					"WHERE cs.status = 'S' " . 
					"AND (cs.transact_date BETWEEN '$this_month_ini' AND '$this_month_end') ";
		$query = $this->db->query($query_str);
		$data['suspended'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter " . 
					"FROM customer c " . 
					"LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) " .
					"WHERE cs.status = 'T' " . 
					"AND (cs.transact_date BETWEEN '$this_month_ini' AND '$this_month_end') ";
		$query = $this->db->query($query_str);
		$data['terminated'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter " . 
					"FROM customer c " . 
					"LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) " .
					"WHERE cs.status = 'A' " . 
					"AND (cs.transact_date BETWEEN '$last_month_ini' AND '$last_month_end') ";
		$query = $this->db->query($query_str);
		$data['last_month_registered'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter " . 
					"FROM customer c " . 
					"LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) " .
					"WHERE cs.status = 'S' " . 
					"AND (cs.transact_date BETWEEN '$last_month_ini' AND '$last_month_end') ";
		$query = $this->db->query($query_str);
		$data['last_month_suspended'] = $query->row(0)->counter;
		
		$query_str = "SELECT COUNT(c.customer_no) as counter " . 
					"FROM customer c " . 
					"LEFT JOIN (
						SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
					) cs ON (cs.customer_no = c.customer_no) " .
					"WHERE cs.status = 'T' " . 
					"AND (cs.transact_date BETWEEN '$last_month_ini' AND '$last_month_end') ";
		$query = $this->db->query($query_str);
		$data['last_month_terminated'] = $query->row(0)->counter;
		return $data;
	}

	function get_bill_inner_join_customer($from="", $to="")
	{

		$query_order = "ORDER BY c.category, b.bill_date, b.customer_no";

		if (empty($from)) {
			$first_day_of_month = date('Y-m-01');
		}
		if (empty($to)) {
			$today = date('Y-m-d');
		}

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
				WHERE bill_date <= ? 
				AND is_void = 0 
				GROUP BY customer_no
			) AS a ON b.bill_no = a.bill_no
			INNER JOIN customer c ON b.customer_no = c.customer_no
			LEFT JOIN (
				SELECT customer_no, MAX(pay_date) AS pay_date
				FROM payment 
				WHERE pay_date <= '".date('Y-m-d')."' 
				GROUP BY customer_no
			) p ON p.customer_no = b.customer_no 
			LEFT JOIN (
				SELECT ref_no, SUM(amount) AS amount 
				FROM payment 
				WHERE pay_date <= '".date('Y-m-d')."' and ref_no != 0 and ref_no IS NOT NULL 
				GROUP BY ref_no 
			) p2 ON p2.ref_no = b.bill_no 
			LEFT JOIN (
				SELECT customer_no, SUM(amount) AS amount 
				FROM payment 
				WHERE pay_date <= '".date('Y-m-d')."' AND processed = 0 
				GROUP BY customer_no 
			) p3 ON p3.customer_no = b.customer_no 
			WHERE (b.balance - IFNULL(p2.amount,0) - IFNULL(p3.amount,0) - b.amount) > 0  
			$query_order
		";

		$query = $this->db->query($sql, [$from, $to]);

		return [
			'result_array' => $query->result_array()
		];
	}
	
	
}
