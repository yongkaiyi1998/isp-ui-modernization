<?php

//#mysqldump -C -h 202.73.10.12 -u GvyFromIndian -pN@5iLem@kG#@d xspeed_p > xspeed_p_160301.sql
//#mysqldump -C -h 202.73.10.12 -u GvyFromIndian -pN@5iLem@kG#@d xspeed_p app_biz_details applicant bill_addr email login m_deposit ref_condo ref_package > update_xspeed_p_160301.sql

class Migration extends CI_Controller {

    function __construct()
    {
        parent::__construct();
    }
	
	function run()
	{
		$month_ini = new DateTime("first day of this month");
		$month_end = new DateTime("last day of this month");
		
		$month_next = new DateTime("first day of next month");
		
		//include_once(APPPATH.'controllers/bill.php');
		//$obj_bill = new Bill();
		$this->load->model('bill_model');
		
		//Get all customer payment
		$payment = $this->get_payment($month_next); 	
		//Get all customer Payment
		//$latest_bill_list = $obj_bill->get_last_bill();
		$latest_bill_list = $this->bill_model->get_last_bill();
		
		//Bill Prorated for new billing date purpose - REsidential / Business
		$query_str = "SELECT MAX(b.bill_date) as bill_date, b.customer_no, c.monthly_charge, c.bill_cycle_month, c.category " . 
					"FROM bill b " . 
					"INNER JOIN customer c ON b.customer_no = c.customer_no " . 
					"WHERE c.activated_date <> 0 " . 
					"AND c.status = 'r' " .
					"GROUP BY b.customer_no, c.monthly_charge, bill_cycle_month " . 
					"ORDER BY b.customer_no " . 
					"";
		
		$query = $this->db->query($query_str);
		foreach ($query->result_array() as $row) {
			$bill_date = new datetime($row['bill_date']);
			$customer_no = $row['customer_no'];
			$monthly_charge = $row['monthly_charge'];
			
			/*
			 * Get the previous Balance / last bill
			 * */
			if (isset($latest_bill_list[$customer_no])) {
				$last_bill = $latest_bill_list[$customer_no];
			}
			else {
				$last_bill['bill_no'] = '';
				$last_bill['bill_date'] = '';
				$last_bill['bill_due_date'] = '';
				$last_bill['balance'] = 0;
			}
			$bill_list[$customer_no]['previous_balance'] = $last_bill['balance'];
			
			//Payment
			$bill_list[$customer_no]['payment_received'] = empty($payment[$customer_no]) ? 0: $payment[$customer_no];
			
			$amount_last_month = 0;
			//Not runing this as because the migration is do it on 1st day of the month.
			/*if ($bill_date->format('m') !== $month_ini->format('m')) {
				$amount_last_month = $monthly_charge; 
				$bill_list[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-t'), 'bill_type'=>1, 'adjust_desc'=>'Subscription Fee', 'amount'=>$amount_last_month, 'remark'=>$bill_date->format('1 M Y') . " - " . $bill_date->format('t M Y'),);
				$bill_date->add(new DateInterval('P1M'));
			}*/

			if ($row['category'] == 'w') {
				$bill_date->add(new DateInterval('P1M'));
				$total_days = $bill_date->format('t');
				$total_used_days = $total_days - $bill_date->format('d') + 1;
				
				$amount = ($monthly_charge / $total_days * $total_used_days );
				$bill_list[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-t'), 'bill_type'=>1, 'adjust_desc'=>'Subscription Fee', 'amount'=>$amount, 'remark'=>$bill_date->format('d M Y') . " - " . $bill_date->format('t M Y'),);
				$bill_list[$customer_no]['charges'] = $amount;
			}
			else {
				$total_days = $bill_date->format('t');
				$total_used_days = $total_days - $bill_date->format('d') + 1;
				
				$amount = ($monthly_charge / $total_days * $total_used_days );
				$bill_list[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-t'), 'bill_type'=>1, 'adjust_desc'=>'Subscription Fee', 'amount'=>$amount, 'remark'=>$bill_date->format('d M Y') . " - " . $bill_date->format('t M Y'),);
				$bill_list[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-1'), 'bill_type'=>1, 'adjust_desc'=>'Subscription Fee', 'amount'=>$monthly_charge, 'remark'=>$month_ini->format('1 M Y') . " - " . $month_ini->format('t M Y'),);
				$bill_list[$customer_no]['charges'] = $amount + $monthly_charge; //+ $amount_last_month;
			}
			
			$tax_charges = $bill_list[$customer_no]['charges'] * 0.06;
			$bill_list[$customer_no]['tax_charges'] = $tax_charges;
			$bill_list[$customer_no]['amount'] = $bill_list[$customer_no]['charges'] + $bill_list[$customer_no]['tax_charges'];
			
			$balance = ($bill_list[$customer_no]['previous_balance'] - $bill_list[$customer_no]['payment_received']) + $bill_list[$customer_no]['amount'];
			$bill_list[$customer_no]['balance'] = $balance;
			
			$bill_list[$customer_no]['bill_period'] = $month_ini->format('M, Y');
		}
		$cust_bill = $bill_list;
		
		$query_str = "SELECT MAX(b.bill_no) as bill_no FROM bill b";
		$query = $this->db->query($query_str);
		$new_bill_no = $query->row()->bill_no + 1;
		
		$query_insert_header = '';
		$query_insert_detail = '';
		
		foreach ($cust_bill as $customer_no => $row) {
			$query_insert_header .= "(" .
								"'" . $new_bill_no . "', " . 
								"'" . $customer_no . "', " . 
								"'" . $month_ini->format('Y-m-1') . "', " . 
								"'" . $month_end->format('Y-m-t') . "', " . 
								"'" . $row['bill_period'] . "', " . 
								"'" . $row['previous_balance'] . "', " .
								"'" . $row['payment_received'] . "', " .
								"'" . $row['charges'] . "', " .
								"'" . $row['tax_charges'] . "', " .
								"'" . $row['amount'] . "', " .
								"'" . $row['balance'] . "' " .
								"),";
			
			foreach ($row['charge_detail'] as $row_charge) {
				$query_insert_detail .= "(" .
									"'" . $new_bill_no . "', " .
									"'" . $row_charge['tranx_date'] . "', " .
									"'" . $row_charge['bill_type'] . "', " .
									"'" . $row_charge['amount'] . "', " .
									"'" . $row_charge['remark'] . "' " .
									")," ;
			}
			$new_bill_no ++;
		}
		
		//If no record insert, immediate exit;
		if ( empty($query_insert_header) || empty($query_insert_detail) ) {
			return 0;
		}

		$query_str = "UPDATE customer SET next_bill_date = '" . $month_next->format('Y-m-1') . "' ";
		$this->db->query($query_str);
		
		//*** Insert the bill header by batch insert
		$query_insert_header = rtrim($query_insert_header, ',');
		$query_str = "INSERT INTO bill (bill_no, customer_no, bill_date, bill_due_date, bill_period, previous_balance, payment_received, charges, tax_charges, amount, balance) VALUES " . 
					$query_insert_header;
		$this->db->query($query_str);
		
		//*** Insert the bill detail by batch insert
		$query_insert_detail = rtrim($query_insert_detail, ',');
		$query_str = "INSERT INTO bill_detail (bill_no, tranx_date, bill_type, amount, remark) VALUES " .
					$query_insert_detail;
		$this->db->query($query_str);
		
		//Update next bill date
		$query_str = "UPDATE customer SET next_bill_date = DATE_ADD(next_bill_date, INTERVAL 1 MONTH) ";
		$this->db->query($query_str);
		
		//Update payment processed
		$query_str = "UPDATE payment SET processed = 1 WHERE pay_date < '" . $month_next->format('Y-m-1') . "' ";
		$this->db->query($query_str);
		
		//*** Lock the adjustment and payment
		$query_str = "UPDATE bill_adjustment SET is_lock = 1 WHERE tranx_date < '" . $month_next->format("Y-m-1") . "'";
		$this->db->query($query_str);
		$query_str = "UPDATE payment SET is_lock = 1 WHERE pay_date < '" . $month_next->format("Y-m-1") . "'";
		$this->db->query($query_str);

		$this->special_process();
		
		echo "Billing Migration completed!";
	}
	
	// *****************
	// * For first time migration only~~~
	// *****************
	function get_payment($pay_month)
	{
		$return_val = array();
		$query_str = "SELECT p.customer_no, SUM(p.amount) as amount " .
					"FROM payment p " .
					"INNER JOIN (SELECT MAX(bill_date) as bill_date, customer_no FROM bill GROUP BY customer_no) b ON p.customer_no=b.customer_no AND p.pay_date>=b.bill_date " . 
					"WHERE p.pay_date <= '" . $pay_month->format('Y-m-t') . "' " .
					"GROUP BY p.customer_no " .
					"ORDER BY p.customer_no ";
		$query = $this->db->query($query_str);
		foreach ($query->result_array() as $row) {
			$return_val[$row['customer_no']] = $row['amount'];
		}
		
		return $return_val;
	}

	function special_process()
	{
	}

	/**
	 * This recalc process will update the xspeed DB, and shd run before migration into itelco DB.
	 */
	function recalc_last_bill_balance()
	{
		$m_db = $this->load->database('migration', TRUE);

		$sql = "SELECT b.app_no, b.bill_date, b.balance, p.post_date, p.amount
				FROM m_bill b 
				INNER JOIN (SELECT MAX(bill_date) as bill_date, app_no FROM m_bill GROUP BY app_no) bb ON b.bill_date=bb.bill_date AND b.app_no=bb.app_no
				INNER JOIN applicant a ON a.app_no=b.app_no 
				INNER JOIN t_payment p ON p.app_no=a.app_no AND p.post_date>bb.bill_date AND p.bill_type<>10 AND p.bill_type<>7
				WHERE a.status='t' and b.balance <> 0";
		$query = $m_db->query($sql);
		foreach ($query->result_array() as $row)
		{
			$sql = "UPDATE m_bill SET balance=balance-".$row['amount']." WHERE app_no='".$row['app_no']."' AND bill_date='".$row['bill_date']."' ";
			$m_db->query($sql);
		}

		echo "completed!";
	}

	/**
	 * This recalc process will update the xspeed DB, and shd run before migration into itelco DB.
	 */
	function recalc_gst()
	{
		$m_db = $this->load->database('migration', TRUE);
		$gst_month = new DateTime("2015-03-01");

		/** Get All billdetails for after 2015 April */
		$sql = "SELECT bd.app_no, bd.trx_date, SUM(bd.amount) AS charge_amt
				FROM t_billdetails bd 
				WHERE bd.trx_date>='2015-04-01'
				GROUP BY bd.app_no, bd.trx_date
				ORDER BY bd.app_no, bd.trx_date ";
		$query = $m_db->query($sql);
		foreach ($query->result_array() as $row)
		{
			$charges[$row['app_no']][$row['trx_date']]['charge_amt'] = $row['charge_amt'];
		}

		//Special take care for first month of GST - Prorate
		/*$update_sql = '';
		$prev_cust = '';
		$sql = "SELECT b.app_no, b.bill_date, b.prev_bal, b.charges, b.payment_rec, b.balance, DAY(bill_date) as bill_day
				FROM m_bill b
				WHERE b.bill_date>='".$gst_month->format('Y-m-1')."' AND b.bill_date<='".$gst_month->format('Y-m-t')."'
				ORDER BY b.app_no, b.bill_date";
		$query = $m_db->query($sql);
		foreach ($query->result_array() as $row)
		{
			$charge = 0;
			if ($prev_cust !== $row['app_no'])
			{
				$prev_bal = $row['prev_bal'];
				$prev_cust = $row['app_no'];
			}
			else
				$prev_bal = $balance;
			if (isset($charges[$row['app_no']][$row['bill_date']]))
				$charge = $charges[$row['app_no']][$row['bill_date']]['charge_amt'];

			if ($row['charges']>0)
				$charge += round($charge/30*($row['bill_day']-1)*0.06,2);
			$balance = ($prev_bal + $charge) - $row['payment_rec'];

			//Update this month balance with 6% GST
			$update_sql = "UPDATE m_bill SET balance='" . $balance . "' WHERE app_no='".$row['app_no']."' AND bill_date='".$row['bill_date']."' ";
			$m_db->query($update_sql);
			//Update next bill prev_bal with this month balance
			$update_sql = "UPDATE m_bill a INNER JOIN (
								SELECT app_no, MIN(bill_date) AS bill_date 
								FROM m_bill 
								WHERE bill_date>'".$row['bill_date']."' AND app_no='".$row['app_no']."' 
							) b ON a.app_no=b.app_no AND a.bill_date=b.bill_date
							SET a.prev_bal = '".$balance."'";
			$m_db->query($update_sql);
		}*/

		//for ($i=4;$i<=13;$i++)
		for ($i=4;$i<=4;$i++)
		{
			$gst_month->add(new DateInterval('P1M'));
			$update_sql = '';
			$prev_cust = '';
			$sql = "SELECT b.app_no, b.bill_date, b.prev_bal, b.charges, b.payment_rec, b.balance, DAY(bill_date) as bill_day
					FROM m_bill b
					WHERE b.bill_date>='".$gst_month->format('Y-m-1')."' 
					AND b.bill_date<='".$gst_month->format('Y-m-t')."'
					ORDER BY b.app_no, b.bill_date";
			$query = $m_db->query($sql);
			foreach ($query->result_array() as $row)
			{
				$charge = 0;
				if ($prev_cust !== $row['app_no'])
				{
					$prev_bal = $row['prev_bal'];
					$prev_cust = $row['app_no'];
				}
				else
					$prev_bal = $balance;
				if (isset($charges[$row['app_no']][$row['bill_date']]))
					$charge = $charges[$row['app_no']][$row['bill_date']]['charge_amt'];

				if ($row['charges']>0) //Normal Charges
				{
					if ($i==4)
						$charge += round($charge/30*($row['bill_day']-1)*0.06,2);
					else
						$charge += round($charge*0.06,2);
				}
				else //Adjustment
				{
					//if ($i==4)
						//$charge += round($charge/30*($row['bill_day']-1)*0.06,2);
					//else
						$charge += round($charge*0.06,2);
				}
				$balance = ($prev_bal + $charge) - $row['payment_rec'];

				//Update this month balance with 6% GST
				$update_sql = "UPDATE m_bill SET balance='" . $balance . "' WHERE app_no='".$row['app_no']."' AND bill_date='".$row['bill_date']."' ";
				$m_db->query($update_sql);
				//Update next bill prev_bal with this month balance
				$update_sql = "UPDATE m_bill a INNER JOIN (
									SELECT app_no, MIN(bill_date) AS bill_date 
									FROM m_bill 
									WHERE bill_date>'".$row['bill_date']."' AND app_no='".$row['app_no']."' 
								) b ON a.app_no=b.app_no AND a.bill_date=b.bill_date
								SET a.prev_bal = '".$balance."'";
				$m_db->query($update_sql);
			}
		}

		/** INSERT installation fee into bill details*/
		/*
		$sql = "INSERT INTO t_billdetails (app_no, trx_date, charge_desc, amount, charge_code) 
				SELECT p.app_no, b.bill_date, 'INSTALLATION FEE', SUM(p.amount), 4
				FROM t_payment p 
				INNER JOIN 
					(SELECT MIN(bill_date) AS bill_date, app_no FROM m_bill GROUP BY app_no) b ON p.app_no = b.app_no
				WHERE p.bill_type=3
				GROUP BY p.app_no, b.bill_date";
		$query = $m_db->query($sql);
		*/

		echo "gst recalc completed!";
	}
}

?>
