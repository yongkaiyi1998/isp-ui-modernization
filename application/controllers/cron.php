<?php

//constant define, future put under config 
define ('_db_backup_path',FCPATH.'files/');
define ('_month_for_overdue',2);

// Monthly - every first day of month
//0 0 1 * * php -qC -d memory=-1 /var/www/html/itelco/index.php cron auto_bill_generate

// Daily - everyday 
//0 0 * * * php -qC -d memory=-1 /var/www/html/itelco/index.php cron update_customer_status

//Val 2025-06-12 Current latest cron job list
// 0 2 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron auto_bill_generate

//!-- if bill generate wrong, do the following steps 
//1. delete all bill records dated with the date of first month
//2. delete all bill detail records
//3. reset customer next_bill_date back to a month before
//4. Adjustment count from last day of last month to first day of last month, set is_lock = 0
//5. Payment count from last day of last month to latest, set is_processed = 0

// 0 9 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron generate_monthly_bill //morning run
////15 12 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron send_bill_messaging //morning run, sends out instantly
// 0 3 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron calc_dealer_comm
// --einvoice only */5 * * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron prep_einvoice
// --einvoice only */5 * * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron einvoice_status_check
// --einvoice only */5 * * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron prep_einvoice_cndn
// 15 * * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron process_phone_csv
// 30 2 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron check_overdue (commented and not more using)
// 40 2 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron check_contract_expiry
// 50 2 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron update_customer_status
// */5 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron check_scheduled_email
// 10 3 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron check_planned_maintenance //Asset
// */5 * * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron cleanup
// */5 * * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron get_bandwidth_stat
// 0 0 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron update_radius_on_package_change

// 0 10 * * * /usr/bin/php /var/www/html/ifibre/backend/index.php cron check_overdue
// */5 * * * * php /var/www/html/ifibre/backend/index.php cron sending_new_contact_whatsapp_msg
// */5 * * * * php /var/www/html/ifibre/backend/index.php cron check_scheduled_whatsapp_message
// */5 * * * * php /var/www/html/ifibre/backend/index.php cron check_scheduled_telegram_message

// * * * * * php /var/www/html/ifibre/backend/index.php cron push_notification_dispatch // run every minute

//~ class Cron extends CI_Controller {
include_once( APPPATH . 'core/DataPage_Controller.php' );
class Cron extends DataPage_Controller {

	//////////////////////////////////////// ZABBIX VARIABLE //////////////////////////////////////////////
	protected $customer_list = [];
	protected $router_list = [];
	protected $bandwidth_stat = [];
	protected $zabbix_host_list = [];
	protected $return = [];
	protected $zbxRouterIPTag = "itelco-router-ip";
	protected $batch_size = 100;
	protected $range_from = 0;
	protected $range_to = 0;
	protected $zabbix_log_days_to_keep = 30;
	//////////////////////////////////////// ZABBIX VARIABLE //////////////////////////////////////////////

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set('Asia/Kuala_Lumpur');
        $this->load->model('common_model');
        $this->load->model('email_model');
        $this->load->model('bill_model');
        $this->load->helper('custom_helper');
		$this->load->library('whatsapp_template');

        // this controller can only be called from the command line
        //if (!$this->input->is_cli_request()) show_error('Direct access is not allowed');
    }
	
	function production_only_cli() {
		if(ENVIRONMENT === 'production' && !$this->input->is_cli_request()) {
			show_error('Direct access is not allowed');
		}
	}

	//iTelco Main cron job, running this everymonth for billing purpose and suspend/terminated for overdue account
    function auto_bill_generate()
    {
    	//need to test this db backup function, or move to cleanup
        /*$db_backup_path = _db_backup_path;
        
        //Backup database every month before generate the bill
        exec('rm ' . $db_backup_path . 'backup_1.sql');
        exec('mv ' . $db_backup_path . 'backup_2.sql  ' . $db_backup_path . 'backup_1.sql');
        exec('mv ' . $db_backup_path . 'backup_3.sql  ' . $db_backup_path . 'backup_2.sql');
        
        $username = $this->db->username;
        $password = $this->db->password;
        $database = $this->db->database;
        $filename = $db_backup_path.'backup_3.sql';
        $exec_str = sprintf('mysqldump -u %s -p%s %s > %s', $username, $password, $database, $filename);
        exec( $exec_str );*/

		//Generate the monthly bill
		//include_once( APPPATH . 'controllers/bill.php' );
        //$obj_bill = new Bill();
        //$obj_bill->bill_generate(); 
		
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];
		// check if today is the bill cycle day
		$today = date('d');
		if ($today != $bill_cycle_day) {
			echo "Not bill generated day.";
			return;
		}

        $this->load->model('bill_model');
        //stop cronjob
        $cust_bill = $this->bill_model->bill_generate(); 

        //bill generate then after that another cron job only generates the monthly bill by making scheduler for bill to be sent out
        log_message('error', print_r($cust_bill, true));

        //*~ RERUN 
        //To rerun this for debugging, delete all bill records from bill and bill_detail, reset next_bill_date for customer back to one month ago

        //use faketime app in server to run multiple cycles of billing... try and see if it works
        
        //Check overdue and change customer status
        //$this->check_overdue(); // see sh do what
        
    }

    function test_bill_prep($customer_no='')
    {
    	$this->load->model('bill_model');

    	$date_arr = array();
    	$date_arr['last_month_ini'] = '2026-08-01';
    	$date_arr['last_month_end'] = '2026-08-31';
    	$date_arr['month_ini'] = '2026-09-01';
    	$date_arr['month_end'] = '2026-09-30';

    	echo "Bill For:".$date_arr['month_ini'];
    	echo "<br><br>";

    	/*$date_arr['last_month_ini'] = '2025-06-01';
    	$date_arr['last_month_end'] = '2025-06-30';
    	$date_arr['month_ini'] = '2025-07-01';
    	$date_arr['month_end'] = '2025-07-31';*/

    	$cust_bill = $this->bill_model->bill_preparation($customer_no, $date_arr);
    	echo "<pre>";
    	print_r($cust_bill);
    	echo "</pre>";
    }

    function test_whatbot()
    {
    	$this->load->model('whatbot_model');
    	$meta_template = 'itelco_auto_bill';
    	$meta_vars = array(
    		'title' => 'Ifibre',
    		'customer_no' => '8900000123',
    		'overdue_amount' => 'RM100.00',
    		'bill_due_date' => '2026-07-31',
    	);
    	$this->whatbot_model->send('60138810470', 'test from itelco', '', '', '', '', '', '', '', '', '', '', 0, $meta_template, $meta_vars);
    }

    function test_attachment()
    {
    	$pdf_path = $this->config->item('upload_path')."/temp/pdf";
    	$pdf_file_name = '[Jul2026]bill_8900000519.pdf';
    	$attachment = $pdf_path.'/'.$pdf_file_name;
		$temp_img       = file_get_contents($attachment);
		$base64_img     = base64_encode($temp_img);
		echo $base64_img;
    }

    /*function dealer_test()
    {
    	$this->load->model('dealer_model');
    	$agent_comm_info = $this->dealer_model->get_agent_pkg_setting(8, 8);

		echo "<pre>";
    	print_r($agent_comm_info);
    	echo "</pre>";

    }*/

    function calc_dealer_comm($run_date='',$preview='')
    {
		$this->load->model('dealer_model');
    	$this->load->model('customer_model');

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'comm_generate_day'");
		$comm_generate_day = $config_record[0]['val'];
		// check if today is the bill cycle day
		$today = date('d');
		if ($today != $comm_generate_day && $preview != 'preview') {
			echo "Not dealer commission generated day.";
			return;
		}
		
    	//check if dealer comm for this month has run
    	$this_month_comm_count = $this->dealer_model->this_month_comm_count();
		if ($this_month_comm_count > 0 && $preview != 'preview') {
			exit("Same commission date existed!");
		}

    	//check if bill for this month has run
    	$this->load->model('bill_model');
    	$this_month_bill_count = $this->bill_model->this_month_bill_count();
    	if ($this_month_bill_count <= 0 && $preview != 'preview') {
    		exit("Billing cycle not yet run!");
    	}

    	if (!empty($run_date)) {
    		$run_month_start = date("Y-m-1", strtotime($run_date));
    		$run_month_end = date("Y-m-t", strtotime($run_date));
    	} else {
    		$run_month_start = date("Y-m-1");
			$run_month_end = date("Y-m-t");
    	}

    	//run based on total bill this month
    	//for each bill, check if dealer associated with the account
    	$query_str = "SELECT b.*, c.dealer, c.package, c.name, c.monthly_charge, ddd.upline, ddd.upline_tree,   
    	p.dealer_comm_type AS p_comm_type, 
    	p.dealer_monthly_comm AS p_monthly_comm, 
    	p.dealer_monthly_times AS p_monthly_times, 
    	p.dealer_onetime_comm AS p_onetime_comm,  
      	dd.comm_type AS dd_comm_type, 
    	dd.monthly AS dd_monthly_comm, 
    	dd.monthly_times AS dd_monthly_times, 
    	dd.onetime AS dd_onetime_comm, 
    	IFNULL(dd.follow_package,1) AS follow_package  
    	FROM bill b 
    	LEFT JOIN dealer_comm_record d ON (b.bill_no = d.bill_no) 
    	LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
    	LEFT JOIN package p ON (c.package = p.package_no) 
    	LEFT JOIN dealer_comm dd ON (dd.dealer_no = c.dealer AND dd.package = c.package) 
    	LEFT JOIN dealer ddd ON (c.dealer = ddd.dealer_no) 
    	WHERE b.bill_date >= '" . $run_month_start . "' AND b.bill_date <= '".$run_month_end."' AND d.comm_no IS NULL AND c.dealer != 0 AND b.is_manual = 0 ";
    	$query		= $this->db->query($query_str);

    	//echo $query_str; exit;

   		$get_max_comm_no = sprintf( "%06d", 0 );
		$get_max_comm_no = date("ym") . $get_max_comm_no;
		$new_comm_no = $get_max_comm_no + 1;

		//for comm date and comm due date, not sure if needed
		$date_first_day	= date('Y-m-1');
		$date_last_day	= date('Y-m-t');

		//for package charges, check the customer package of last month's end
		$last_month_ini = new DateTime("first day of last month");
		$last_month_end = new DateTime("last day of last month");

		$records = $query->result_array();

		if($preview == 'preview'){
			echo "<pre>";
			echo "<h2>Top Level Dealer</h2>";
			echo "</pre>";
		}

		//company payout to first level
		foreach ($records as $row) {

			/*
			echo "<pre>";
    		print_r($row);
    		echo "</pre>";
			*/

			//monthly charge taken wrongly, should be taken from customer_package_history
			$package_amount = 0;
			$cur_package_info = $this->customer_model->get_customer_monthly_charge( $row['customer_no'] , $last_month_end->format( 'Y-m-d' ) );
			if( $cur_package_info['monthly_charge'] != '' ){
				$package_amount = $cur_package_info['monthly_charge'];
			} else {
				$package_amount = $row['monthly_charge'];
			}

			$package_no = $row['package'];
			if( isset($cur_package_info['package_no']) ){
				if( $cur_package_info['package_no'] != '' ){
					$package_no = $cur_package_info['package_no'];
				}
			}

    		//if got dealer, then take this customer's package and check if shud follow package commission or dealer set commission
    		//have to check who is the main upline - and get from their setting
    		if (!empty($row['upline'])) {
    			$upline_tree = explode(",", $row['upline_tree']);
    			$setting_info = $this->dealer_model->get_agent_pkg_setting($upline_tree[0], $package_no);
   	    		$comm_type = $setting_info['comm_type'];
	    		$monthly_comm = $setting_info['monthly_comm'];
	    		$monthly_times = $setting_info['monthly_times'];
	    		$onetime_comm = $setting_info['onetime_comm'];

	    		$dealer_no = $upline_tree[0];
    		} else {
    			//if upline not equal empty, means just follow the query
    			$dealer_no = $row['dealer'];
	    		if ($row['follow_package'] == '1') {
	    			//follow package
	    			$comm_type = $row['p_comm_type'];
	    			$monthly_comm = $row['p_monthly_comm'];
	    			$monthly_times = $row['p_monthly_times'];
	    			$onetime_comm = $row['p_onetime_comm'];
	    		} else {
	    			//dont follow package
	       			$comm_type = $row['dd_comm_type'];
	    			$monthly_comm = $row['dd_monthly_comm'];
	    			$monthly_times = $row['dd_monthly_times'];
	    			$onetime_comm = $row['dd_onetime_comm'];
	    		}
    		}

    		$dealer_comm_rec = array();
    		$comm_desc = '';

    		//check if values are filled out
    		if ($comm_type == 'm') {
    			if (empty($monthly_comm)) {
    				continue;
    			}

    			//check if collected times have exceeded
    			//if exceeded, dont give anymore
    			if (!empty($monthly_times)) {
	    			$chk_monthly = $this->dealer_model->chk_monthly_comm_times($row['customer_no'], $dealer_no);
	    			if ($chk_monthly >= $monthly_times) {
	    				continue;
	    			}
    			}

    			//follow monthly rate if got set, otherwise check if one time has been paid out
    			$comm = (($package_amount * $monthly_comm) / 100);
    			$comm_desc = 'Monthly Commission For Period '.$last_month_ini->format( 'Y-m-d' ).' to '.$last_month_end->format( 'Y-m-d' ).' Customer: '.$row['name'];

    			//if monthly, calc monthly charge (after tax or before tax?) with % and deposit to agent

    		} else if ($comm_type == 'o') {
    			if (empty($onetime_comm)) {
    				continue;
    			}

    			//check if one time for this customer has already been distribute to the agent
    			//if change package then how? shud be only one time based on customer_no
    			$chk_one_time = $this->dealer_model->chk_one_time_comm($row['customer_no'], $dealer_no);

    			//if one time, then just distribute one time to agent
    			if ($chk_one_time <= 0) {
    				$comm = $onetime_comm;

    				$comm_desc = 'One-Time Commission For Customer: '.$row['name'];
    			}
    		} else {
    			continue;
    			//unknown comm type
    		}

    		//if got this far then insert into dealer_comm_rec
    		$dealer_comm_rec['customer_no'] = $row['customer_no'];
    		$dealer_comm_rec['bill_no'] = $row['bill_no'];
    		$dealer_comm_rec['comm_no'] = $new_comm_no;
    		$dealer_comm_rec['dealer_no'] = $dealer_no;
    		$dealer_comm_rec['package_no'] = $package_no;
    		$dealer_comm_rec['comm_date'] = $date_first_day;
    		$dealer_comm_rec['comm_due_date'] = $date_last_day;
    		//empty for some reason
    		$dealer_comm_rec['comm_period'] = '';
    		$dealer_comm_rec['comm_desc'] = $comm_desc;
    		//for if payment is made, update this field
    		$dealer_comm_rec['ref_no'] = '';
    		$dealer_comm_rec['amount'] = $comm;
    		//0 for now
    		$dealer_comm_rec['tax_charges'] = 0;
    		$dealer_comm_rec['is_void'] = 0;
    		$dealer_comm_rec['is_manual'] = 0;
    		$dealer_comm_rec['created_on'] = date('Y-m-d H:i:s');
    		//system
    		$dealer_comm_rec['created_by'] = 0;

			if($comm <= 0) continue;

			if($preview == 'preview'){
				echo "<pre>";
				print_r($dealer_comm_rec);
				echo "</pre>";
			}else{
				$this->dealer_model->dealer_comm_record_insert($dealer_comm_rec);
			}

    		//should we auto submit self billed e-invoice for this?

    		//log
    		log_message('error', print_r($dealer_comm_rec, true));

    		$new_comm_no++;
    	}

		if($preview == 'preview'){
			echo "<pre>";
			echo "<h2>Downline Dealer</h2>";
			echo "</pre>";
		}
    	//do for downline
    	foreach ($records as $row) {
    		if (empty($row['upline'])) {
    			//not downline
    			continue;
    		}
			
			//monthly charge taken wrongly, should be taken from customer_package_history
			$package_amount = 0;
			$cur_package_info = $this->customer_model->get_customer_monthly_charge( $row['customer_no'] , $last_month_end->format( 'Y-m-d' ) );

			if( $cur_package_info['monthly_charge'] != '' ){
				$package_amount = $cur_package_info['monthly_charge'];
			} else {
				$package_amount = $row['monthly_charge'];
			}

			$package_no = $row['package'];
			if( isset($cur_package_info['package_no']) ){
				if( $cur_package_info['package_no'] != '' ){
					$package_no = $cur_package_info['package_no'];
				}
			}

			//determine how many downline to pay for 1 transaction
			$downlines = array();
			$upline_tree = array_filter(explode(",", $row['upline_tree'])); // array_filter can clear the empty value in array
			//start from 1 because topmost already done above
			for ($k = 1; $k < count($upline_tree); $k++) {
    			$setting_info = $this->dealer_model->get_agent_pkg_setting($upline_tree[$k], $package_no);
   	    		$comm_type = $setting_info['comm_type'];
	    		$monthly_comm = $setting_info['monthly_comm'];
	    		$monthly_times = $setting_info['monthly_times'];
	    		$onetime_comm = $setting_info['onetime_comm'];

	    		$comm = 0;
	    		$comm_desc = '';
	    		$dealer_no = $upline_tree[$k];

	    		if ($comm_type == 'm') {
	    			if (empty($monthly_comm)) {
	    				continue;
	    			}

	    			//check if collected times have exceeded
	    			//if exceeded, dont give anymore
	    			if (!empty($monthly_times)) {
		    			$chk_monthly = $this->dealer_model->chk_monthly_comm_times($row['customer_no'], $dealer_no);
		    			if ($chk_monthly >= $monthly_times) {
		    				continue;
		    			}
	    			}

	    			//follow monthly rate if got set, otherwise check if one time has been paid out
	    			$comm = (($package_amount * $monthly_comm) / 100);
	    			$comm_desc = 'Monthly Commission For Period '.$last_month_ini->format( 'Y-m-d' ).' to '.$last_month_end->format( 'Y-m-d' ).' Customer: '.$row['name'];

	    			//if monthly, calc monthly charge (after tax or before tax?) with % and deposit to agent

	    		} else if ($comm_type == 'o') {
	    			if (empty($onetime_comm)) {
	    				continue;
	    			}

	    			//check if one time for this customer has already been distribute to the agent
	    			//if change package then how? shud be only one time based on customer_no
	    			$chk_one_time = $this->dealer_model->chk_one_time_comm($row['customer_no'], $dealer_no);

	    			//if one time, then just distribute one time to agent
	    			if ($chk_one_time <= 0) {
	    				$comm = $onetime_comm;

	    				$comm_desc = 'One-Time Commission For Customer: '.$row['name'];
	    			}
	    		} else {
	    			continue;
	    			//unknown comm type
	    		}

	    		$downlines[$dealer_no]['desc'] = $comm_desc;
	    		$downlines[$dealer_no]['comm'] = $comm;

			}

			foreach ($downlines as $downline => $downline_info) {

				if (empty($downline)) {
					log_message('error', 'Something went wrong in downline loop.');
					continue;
				}

				$dealer_comm_rec = array();

	    		//if got this far then insert into dealer_comm_rec
	    		$dealer_comm_rec['customer_no'] = $row['customer_no'];
	    		$dealer_comm_rec['bill_no'] = $row['bill_no'];
	    		$dealer_comm_rec['comm_no'] = $new_comm_no;
	    		$dealer_comm_rec['dealer_no'] = $downline;
	    		$dealer_comm_rec['package_no'] = $package_no;
	    		$dealer_comm_rec['comm_date'] = $date_first_day;
	    		$dealer_comm_rec['comm_due_date'] = $date_last_day;
	    		//empty for some reason
	    		$dealer_comm_rec['comm_period'] = '';
	    		$dealer_comm_rec['comm_desc'] = $downline_info['desc'];
	    		//for if payment is made, update this field
	    		$dealer_comm_rec['ref_no'] = '';
	    		$dealer_comm_rec['amount'] = $downline_info['comm'];
	    		//0 for now
	    		$dealer_comm_rec['tax_charges'] = 0;
	    		$dealer_comm_rec['is_void'] = 0;
	    		$dealer_comm_rec['is_manual'] = 0;
	    		$dealer_comm_rec['created_on'] = date('Y-m-d H:i:s');
	    		//system
	    		$dealer_comm_rec['created_by'] = 0;

				if($downline_info['comm'] <= 0) continue;

				if($preview == 'preview'){
					echo "<pre>";
					print_r($dealer_comm_rec);
					echo "</pre>";
				}else{
					$this->dealer_model->dealer_comm_record_insert($dealer_comm_rec);
				}

	    		//log
	    		log_message('error', print_r($dealer_comm_rec, true));

	    		$new_comm_no++;

    		}
    	}

    	echo "done";
    	
    }

    function prep_einvoice() {

    	$this->load->model('bill_model');
    	//check if einvoice module for this month have run
    	/*$this_month_einvoice_count = $this->bill_model->this_month_einvoice_count();
		if ($this_month_einvoice_count > 0) {
			exit("Same einvoice generation date existed!");
		}*/

    	//check if bill for this month has run
    	$this_month_bill_count = $this->bill_model->this_month_bill_count();
    	if ($this_month_bill_count <= 0) {
			exit("Billing cycle not yet run!");
    	}

    	//for this function, maybe need an agreement on when to send to lhdn, perhaps there is a grace period after bill is generated

   		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'days_after_bill_date' ");
		$days_after_bill_date = $config_record[0]['val'];

		$where = " b.bill_date >= '" . date("Y-m-1") . "' AND b.bill_date <= '".date("Y-m-t")."' ";
		if (!empty($days_after_bill_date)) {
			if ($days_after_bill_date > 0) {
				$where = " b.bill_date < (NOW() - INTERVAL ".$days_after_bill_date." DAY) ";
			}
		}

    	//for each bill, convert to xml and store in folder

    	//folder needs to be periodically cleanup

    	//do in batches, save file to folder if not yet created, and send xml to lhdn if not yet
    	//manual maybe need seperate from this process as well
    	//also might need to resubmit for those that fail

		//Val 06-06-25 put a field in bill_einvoice where it suggests attempts of submission, or an update datetime field, then we can do buffer

    	$query_str = "SELECT b.*, c.currency_code, be.einvoice_no, be.einvoice_submission_id, be.einvoice_uuid, be.einvoice_status, be.einvoice_longid, p.bill_addr_1, p.bill_addr_2, p.bill_addr_3, p.bill_postcode, p.bill_city, ss2.einvoice_code AS bill_state, p.acc_name, p.comp_name, p.tin AS cust_tin, p.ssm AS cust_brn, p.icno AS cust_icno, p.sst AS cust_sst, p.ttx AS cust_ttx, p.acc_mobileno AS cust_mobile, p.acc_email AS cust_email, c.inst_unit_no AS del_unit_no, c.inst_addr1 AS del_addr_1, c.inst_addr2 AS del_addr_2, c.inst_addr3 AS del_addr_3, c.inst_postcode AS del_postal, c.inst_city AS del_city, ss.einvoice_code AS del_state, c.inst_phone AS del_tel, c.inst_email AS del_email, c.payment_term, p.acc_type, be.einvoice_status, p.nationality, c.name AS cust_name       
    	FROM bill b 
    	LEFT JOIN bill_einvoice be ON (b.bill_no = be.bill_no) 
    	LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
    	LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
    	LEFT JOIN `sys_state` ss ON (ss.state_code = c.`inst_state`) 
    	LEFT JOIN `sys_state` ss2 ON (ss2.state_code = p.`bill_state`) 
    	WHERE ".$where." AND b.bill_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND b.bill_date <  DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH) AND (be.einvoice_status NOT IN ('S', 'P') OR be.einvoice_status IS NULL) AND b.is_manual = 0 AND b.is_void = 0 AND p.tin != '' AND NOT EXISTS (SELECT 1 FROM einvoice_log el WHERE el.bill_no = b.bill_no AND el.controller_name = 'cron' AND DATE(el.created_at) = CURDATE()) ORDER BY be.updated_on ASC, b.bill_no ASC LIMIT 10";
    	//echo $query_str; exit;

    	$query		= $this->db->query($query_str);

		$einvoice_type = $this->config->item('einvoice_type');
		$this->load->model('einvoice_xml_model');
		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');
		//$this->load->model('einvoice_json_model');

		$einvoice_folder = $this->config->item('einvoice_file_path');

    	//set limit to 10, run on intervals
    	foreach ($query->result_array() as $row) {

    		//check if this bill_no xml already exists, if exists, skip to send function
    		//if (!file_exists($einvoice_folder.'/'.$row['bill_no'].'_signed.xml') && empty($row['einvoice_status'])) {

	    		//pass the bill array into the function
	    		$einvoice_data = $this->bill_model->prepare_einvoice_array( $row, ($einvoice_type=='JSON'?FALSE:TRUE) );
	    		//log_message('error', print_r($einvoice_data, true));
	    		/*echo "<pre>";
	    		print_r($einvoice_data); exit;
	    		echo "</pre>";*/

				$xml_return = array();
				$xml_return['status'] = 'err';
				$xml_return['msg'] = '';
				$sign_succ = false;

				//temporary use xml approach first
				$xml_return = $this->einvoice_xml_model->generate_xml_by_id( $einvoice_data, FALSE );

				//test c14n
				$xml_loc = $einvoice_data['file_path'].$einvoice_data['file_name'];
				if (file_exists($xml_loc)) {

					$sign_arr = array();
					$sign_data = array();
					$sign_arr['xml_loc'] = $xml_loc;
					$sign_arr['p12_file'] = $this->config->item('p12_file');
					$sign_arr['p12_pin'] = $this->config->item('p12_pin');
					$sign_arr['p12_pem'] = $this->config->item('p12_pem');
						$sign_data = $this->einvoice_xml_model->prep_signature( $sign_arr );

						if ($sign_data['succ'] == 1) {
							$sign_data['file_path'] = $einvoice_data['file_path'];
						$sign_data['signed_file_name'] = $einvoice_data['signed_file_name'];
						$sign_return = $this->einvoice_xml_model->sign_doc( $sign_data );
						$sign_succ = $sign_return['status'];
						$sign_err = $sign_return['msg'];
					} else {
						$sign_succ = false;
						$sign_err = $sign_data['err'];
					}

				}

				if ($xml_return['status'] != 'succ') {
					$xml_error = $xml_return['msg'];
					log_message('error', $xml_error);
					continue;
				} 

				if (!$sign_succ) {
					$xml_error = $sign_err;
					log_message('error', $xml_error);
					continue;
				}

			//}

			//send to lhdn
			sleep(1);

			$signed_xml_loc = $einvoice_folder.'/'.$row['bill_no'].'_signed.xml';

			if (file_exists($signed_xml_loc)) {

				$login = $this->einvoice_config_model->login_myinvois_portal();

				if ($login) {

					$this->load->helper('einvoice');

					$xml_file = file_get_contents($signed_xml_loc);

					$send_documents = array();
					$send_documents[0]['format'] = 'XML';
					$send_documents[0]['documentHash'] = hash_file("sha256", $signed_xml_loc);
					$send_documents[0]['codeNumber'] = $row['bill_no'];
					$send_documents[0]['document'] = base64_encode($xml_file);

					$senddata = array();
					$senddata['token'] = $_SESSION['einvoice']['access_token'];
					$senddata['api_path'] = $this->config->item('einvoice_api');
					$senddata['documents'] = $send_documents;
					$result = $this->einvoice_api_model->send_api($senddata);

					$einvoice_log = [
						'bill_no'        => $row['bill_no'],
						'document_type'  => $row['bill_type'] ?? 'INV',
						'submission_uid' => $result->submissionUid ?? null,
						'uuid'           => $result->acceptedDocuments[0]->uuid ?? null,
						'response'       => json_encode($result),
						'error_message'  => null,
						'controller_name' => $this->router->fetch_class(),
						'function_name'   => $this->router->fetch_method(),
					];

					if (isset($result->submissionUid)) {

						if (!empty($result->submissionUid)) {

							$update_data = [
								'einvoice_submission_id' => $result->submissionUid,
								'einvoice_uuid'          => $result->acceptedDocuments[0]->uuid ?? null,
								'einvoice_status'        => 'P',
								'einvoice_longid'        => '',
								'bill_date'              => $row['bill_date'],
							];
							$this->bill_model->update_einvoice_param($row['bill_no'], $update_data);

							$msg = "E-Invoice successfully sent.";

							$this->load->model('action_log_model');
							$ctrl    = $this->router->fetch_class();
							$method  = $this->router->method;
							$esc     = $this->db->escape_str("E-Invoice: {$row['bill_no']} Sent.");
							$desc    = "E-Invoice: {$row['bill_no']} Sent. Customer:(".$row['customer_no'].", ".$row['customer_name'].")";
							$this->action_log_model->save_action($ctrl, $method, $esc, $desc, 'insert');

							$customer_action_model			= 'customer_action_log_model';
							$this->load->model($customer_action_model);
							$customer_action_log =  $this->$customer_action_model->save_action($ctrl,$method,$esc,$desc,'insert',$row['customer_no']);

						} else {
							$msg = "Returned with error: " . !empty($result->rejectedDocuments) 
																? einvoice_error_dispatcher($result) 
																: null;
							$einvoice_log['error_message'] = $msg;
						}

					} else {
						$msg_data = !empty($result->rejectedDocuments) ? einvoice_error_dispatcher($result) : null;
						if (!empty($msg_data)) {
							$msg = "Returned with error: " . $msg_data;
						} else {
							$msg = "E-Invoice either already sent or no response from MyInvois system.";
						}
						$einvoice_log['error_message'] = $msg;
					}

    				$this->load->model('einvoice_model');
					$this->einvoice_model->insert_log($einvoice_log);

					log_message('error', $row['bill_no'].': '.$msg);

				} else {
					log_message('error', 'Failed to login to LHDN portal.');
					break;
				}

			} else {
				log_message('error', $einvoice_folder.'/'.$row['bill_no'].'_signed.xml not found!');
			}

			//update updated_on
			$this->bill_model->update_einvoice_submit_time($row['bill_no']);

    	}

    	echo "done";

    }

    function einvoice_status_check($force = 0)
    {
    	$this->load->model('bill_model');
    	$this->load->model('einvoice_model');

		$query_where_be  = "";
		$query_where_be2 = "";
		$query_where_be3 = "";

		if ($force != 1) {
			$today = date('Y-m-d');
			$query_where_be  = " AND be.bill_no NOT IN (
				SELECT bill_no FROM einvoice_log
				WHERE DATE(created_at) = '$today'
				AND function_name = 'einvoice_status_check'
			)";

			$query_where_be2 = " AND CONCAT('CN', be2.bill_no) NOT IN (
				SELECT bill_no FROM einvoice_log
				WHERE DATE(created_at) = '$today'
				AND function_name = 'einvoice_status_check'
			)";

			$query_where_be3 = " AND be3.inv_no NOT IN (
				SELECT bill_no FROM einvoice_log
				WHERE DATE(created_at) = '$today'
				AND function_name = 'einvoice_status_check'
			)";
		}

    	//UNION dn and cn tables
    	$query_str = " 	SELECT be.*, 'INV' AS be_type, '' AS cn_reason
						FROM bill_einvoice be
						WHERE be.einvoice_status = 'P'
						$query_where_be

						UNION

						SELECT CONCAT('CN', be2.bill_no) AS bill_no,
							be2.bill_date, be2.einvoice_no, be2.einvoice_submission_id,
							be2.einvoice_uuid, be2.einvoice_status, be2.einvoice_longid,
							be2.created_on, be2.created_by, be2.updated_on,
							'CN' AS be_type, be2.cn_reason
						FROM bill_einvoice_cn be2
						WHERE be2.einvoice_status = 'P'
						$query_where_be2

						UNION

						SELECT be3.inv_no AS bill_no, be3.inv_date AS bill_date,
							be3.einvoice_no, be3.einvoice_submission_id, be3.einvoice_uuid,
							be3.einvoice_status, be3.einvoice_longid, be3.created_on,
							be3.created_by, be3.created_on AS updated_on,
							'CO' AS be_type, '' AS cn_reason
						FROM consolidated_einvoice be3
						WHERE be3.einvoice_status = 'P'
						$query_where_be3

						ORDER BY created_on DESC
						LIMIT 10
					";
    	//echo $query_str; exit;
    	$query		= $this->db->query($query_str);

		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');

		// $rows = $query->result_array();
		// print_r($rows);
		// exit;

    	foreach ($query->result_array() as $row) {
    		$login = $this->einvoice_config_model->login_myinvois_portal();

			if ($login) {

				$this->load->helper('einvoice');

				$senddata = array();
				$senddata['token'] = $_SESSION['einvoice']['access_token'];
				$senddata['api_path'] = $this->config->item('einvoice_api');
				$senddata['uuid'] = $row['einvoice_uuid'];
				$result = $this->einvoice_api_model->check_status($senddata);

				$einvoice_log = [
					'bill_no'        => $row['bill_no'],
					'document_type'  => $row['be_type'] ?? 'INV',
					'submission_uid' => $result->submissionUid ?? null,
					'uuid'           => $result->uuid ?? null,
					'response'       => json_encode($result),
					'error_message'  => null,
					'controller_name' => $this->router->fetch_class(),
					'function_name'   => $this->router->fetch_method(),
				];

				if (isset($result->submissionUid)) {

					if (!empty($result->submissionUid)) {

						$update_data = [
							'einvoice_submission_id' => $result->submissionUid,
							'einvoice_uuid'          => $result->uuid ?? null,
							'einvoice_status'        => 'P',
							'einvoice_longid'        => $result->longId ?? null,
							'bill_date'              => $row['bill_date'],
							'cn_reason'              => $row['cn_reason'] ?? null,
						];

						$status = 'P';
						switch (strtolower($result->status)) {
							case 'valid':      $status = 'S'; break;
							case 'invalid':    $status = 'F'; break;
							case 'submitted':  $status = 'P'; break;
							case 'cancelled':  $status = 'C'; break;
						}
						$update_data['einvoice_status'] = $status;

						if ($row['be_type'] == 'CN') {
							$this->bill_model->create_void_cn(str_replace('CN', '', $row['bill_no']), $update_data);
						} elseif ($row['be_type'] == 'CO') {
							$this->einvoice_model->update_consolidated_einvoice($row['bill_no'], $update_data);
						} else {
							$this->bill_model->update_einvoice_param($row['bill_no'], $update_data);
						}

						$error_msg = einvoice_error_dispatcher($result);
						if(!empty($error_msg)){
							$msg = "Returned with error: " . $error_msg;
							$einvoice_log['error_message'] = $msg;
						}

						$msg = "Successfully updated status.";

					} else {
						$msg = "Returned with error: " . !empty($result->rejectedDocuments) ? einvoice_error_dispatcher($result) : null;
						$einvoice_log['error_message'] = $msg;
					}

				} else {

					$return_arr = !empty($result->rejectedDocuments) ? einvoice_error_dispatcher($result) : null;
					if (!empty($return_arr)) {
						$msg = "Returned with error: " . $return_arr;
					} else {
						$msg = "E-Invoice either already sent or no response from MyInvois system (sent too many times).";
					}
					$einvoice_log['error_message'] = $msg;
				}

				$this->einvoice_model->insert_log($einvoice_log);

				log_message('error', $row['bill_no'].': '.$msg);
				sleep(1);

			} else {
				$msg = "Login to myinvois portal failed.";
				break;
			}

    	}

    	echo "done";
    }

    function prep_einvoice_cndn()
    {

    	$query_str = "SELECT b.*, b.bill_no AS inv_bill_no, c.currency_code, be.einvoice_no, be.einvoice_submission_id, be.einvoice_uuid, be.einvoice_status, be.einvoice_longid, p.bill_addr_1, p.bill_addr_2, p.bill_addr_3, p.bill_postcode, p.bill_city, ss2.einvoice_code AS bill_state, p.acc_name, p.comp_name, p.tin AS cust_tin, p.ssm AS cust_brn, p.icno AS cust_icno, p.sst AS cust_sst, p.ttx AS cust_ttx, p.acc_mobileno AS cust_mobile, p.acc_email AS cust_email, c.inst_unit_no AS del_unit_no, c.inst_addr1 AS del_addr_1, c.inst_addr2 AS del_addr_2, c.inst_addr3 AS del_addr_3, c.inst_postcode AS del_postal, c.inst_city AS del_city, ss.einvoice_code AS del_state, c.inst_phone AS del_tel, c.inst_email AS del_email, c.payment_term, p.acc_type, be.einvoice_status, be.cn_reason, be2.einvoice_uuid AS inv_einvoice_uuid, be.bill_date AS cn_date, p.nationality         
    	FROM bill_einvoice_cn be 
    	LEFT JOIN bill b ON (b.bill_no = be.bill_no) 
    	LEFT JOIN bill_einvoice be2 ON (b.bill_no = be2.bill_no) 
    	LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
    	LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
    	LEFT JOIN `sys_state` ss ON (ss.state_code = c.`inst_state`) 
    	LEFT JOIN `sys_state` ss2 ON (ss2.state_code = p.`bill_state`) 
    	WHERE be.einvoice_status NOT IN ('S', 'P') AND NOT EXISTS (SELECT 1 FROM einvoice_log el WHERE el.bill_no = b.bill_no AND el.controller_name = 'cron' AND DATE(el.created_at) = CURDATE()) ORDER BY be.updated_on ASC, b.bill_no ASC LIMIT 10";
    	//echo $query_str; exit;

       	$query		= $this->db->query($query_str);

		$einvoice_type = $this->config->item('einvoice_type');
		$this->load->model('einvoice_xml_model');
		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');

		$einvoice_folder = $this->config->item('einvoice_file_path');

		foreach ($query->result_array() as $row) {

			$einvoice_data = $this->bill_model->prepare_einvoice_cn_array( $row, ($einvoice_type=='JSON'?FALSE:TRUE) );

    		/*echo "<pre>";
    		print_r($einvoice_data);
    		echo "</pre>"; exit;*/

			$xml_return = array();
			$xml_return['status'] = 'err';
			$xml_return['msg'] = '';
			$sign_succ = false;

			//temporary use xml approach first
			$xml_return = $this->einvoice_xml_model->generate_xml_by_id( $einvoice_data, FALSE );

			//test c14n
			$xml_loc = $einvoice_data['file_path'].$einvoice_data['file_name'];
			if (file_exists($xml_loc)) {

				$sign_arr = array();
				$sign_data = array();
				$sign_arr['xml_loc'] = $xml_loc;
				$sign_arr['p12_file'] = $this->config->item('p12_file');
				$sign_arr['p12_pin'] = $this->config->item('p12_pin');
				$sign_arr['p12_pem'] = $this->config->item('p12_pem');
					$sign_data = $this->einvoice_xml_model->prep_signature( $sign_arr );

					if ($sign_data['succ'] == 1) {
						$sign_data['file_path'] = $einvoice_data['file_path'];
					$sign_data['signed_file_name'] = $einvoice_data['signed_file_name'];
					$sign_return = $this->einvoice_xml_model->sign_doc( $sign_data );
					$sign_succ = $sign_return['status'];
					$sign_err = $sign_return['msg'];
				} else {
					$sign_succ = false;
					$sign_err = $sign_data['err'];
				}

			}

			if ($xml_return['status'] != 'succ') {
				$xml_error = $xml_return['msg'];
				log_message('error', $xml_error);
				continue;
			} 

			if (!$sign_succ) {
				$xml_error = $sign_err;
				log_message('error', $xml_error);
				continue;
			}

			//}

			//send to lhdn
			sleep(1);

			$signed_xml_loc = $einvoice_folder.'/CN'.$row['bill_no'].'_signed.xml';

			if (file_exists($signed_xml_loc)) {

				$login = $this->einvoice_config_model->login_myinvois_portal();

				if ($login) {

					$this->load->helper('einvoice');

					$xml_file = file_get_contents($signed_xml_loc);

					$send_documents = array();
					$send_documents[0]['format'] = 'XML';
					$send_documents[0]['documentHash'] = hash_file("sha256", $signed_xml_loc);
					$send_documents[0]['codeNumber'] = $row['bill_no'];
					$send_documents[0]['document'] = base64_encode($xml_file);

					$senddata = array();
					$senddata['token'] = $_SESSION['einvoice']['access_token'];
					$senddata['api_path'] = $this->config->item('einvoice_api');
					$senddata['documents'] = $send_documents;
					$result = $this->einvoice_api_model->send_api($senddata);

					$einvoice_log = [
						'bill_no'        => $row['bill_no'],
						'document_type'  => 'CN',
						'submission_uid' => $result->submissionUid ?? null,
						'uuid'           => $result->acceptedDocuments[0]->uuid ?? null,
						'response'       => json_encode($result),
						'error_message'  => null,
						'controller_name' => $this->router->fetch_class(),
						'function_name'   => $this->router->fetch_method(),
					];

					if (isset($result->submissionUid)) {

						if (!empty($result->submissionUid)) {

							$update_data = [
								'einvoice_submission_id' => $result->submissionUid,
								'einvoice_uuid'          => $result->acceptedDocuments[0]->uuid ?? null,
								'einvoice_status'        => 'P',
								'einvoice_longid'        => '',
								'bill_date'              => $row['cn_date'],
								'cn_reason'              => $row['cn_reason'],
							];
							$this->bill_model->create_void_cn($row['bill_no'], $update_data);

							$msg = "E-Invoice CN successfully sent.";

						} else {
							$msg = "Returned with error: " . !empty($result->rejectedDocuments) ? einvoice_error_dispatcher($result) : null;
							$einvoice_log['error_message'] = $msg;
						}

					} else {
						$return_arr = !empty($result->rejectedDocuments) ? einvoice_error_dispatcher($result) : null;

						if (!empty($return_arr)) {
							$msg = "Returned with error: " . $return_arr;
						} else {
							$msg = "E-Invoice either already sent or no response from MyInvois system (sent too many times).";
						}

						$einvoice_log['error_message'] = $msg;
					}

					$this->einvoice_model->insert_log($einvoice_log);

					log_message('error', 'CN'.$row['bill_no'].': '.$msg);

				} else {
					log_message('error', 'Failed to login to LHDN portal.');
					break;
				}

			} else {
				log_message('error', $einvoice_folder.'/CN'.$row['bill_no'].'_signed.xml not found!');
			}

			//update updated_on
			$this->bill_model->update_einvoice_cn_submit_time($row['bill_no']);

		}

		echo "done";

    }

    function process_phone_csv()
    {
    	//check csv phone folder for any csv files
    	$this->load->model('bill_model');
    	$phone_csv_folder = $this->config->item('phone_csv_file_path');

    	//if have, read through file, save to db
		$files = [];
		if (!empty($phone_csv_folder) && is_dir($phone_csv_folder)) {
			$files = scandir($phone_csv_folder);
		}
		foreach ($files as $file) {
		    $filePath = $phone_csv_folder . '/' . $file;
		    if (is_file($filePath)) {
		        $file_parts = pathinfo($file);
		        if ($file_parts['extension'] == 'csv') {

		        	log_message('error', 'Processing:'.$file);

		        	$import_id = date('YmdHi');

		        	//echo "<pre>";
					$csv = fopen($filePath, 'r');
					while (($line = fgetcsv($csv)) !== FALSE) {
					   //$line[0] = '1004000018' in first iteration
					   //print_r($line);

						if ($line[0] == 'Account Code') {
							//header skip
							continue;
						}

						log_message('error', print_r($line, true));

						$phone_rec = array();
						$phone_rec['import_id'] = $import_id;
						$phone_rec['account_code'] = $line[0];
						$phone_rec['caller_no'] = $line[1];
						$phone_rec['caller_nat'] = $line[2];
						$phone_rec['callee_no'] = $line[3];
						$phone_rec['callee_nat'] = $line[4];
						$phone_rec['context'] = $line[5];
						$phone_rec['caller_id'] = $line[6];
						$phone_rec['source_channel'] = $line[7];
						$phone_rec['dest_channel'] = $line[8];
						$phone_rec['last_app'] = $line[9];
						$phone_rec['last_data'] = $line[10];
						$phone_rec['start_time'] = $line[11];
						$phone_rec['answer_time'] = $line[12];
						$phone_rec['end_time'] = $line[13];
						$phone_rec['call_time'] = $line[14];
						$phone_rec['talk_time'] = $line[15];
						$phone_rec['call_status'] = $line[16];
						$phone_rec['ama_flags'] = $line[17];
						$phone_rec['unique_id'] = $line[18];
						$phone_rec['call_type'] = $line[19];
						$phone_rec['dest_channel_ext'] = $line[20];
						$phone_rec['caller_name'] = $line[21];
						$phone_rec['answered_by'] = $line[22];
						$phone_rec['session'] = $line[23];
						$phone_rec['premier_caller'] = $line[24];
						$phone_rec['action_type'] = $line[25];
						$phone_rec['source_trunk'] = $line[26];
						$phone_rec['dest_trunk'] = $line[27];

						$this->bill_model->phone_call_record_insert($phone_rec);
					}
					fclose($csv);
					//echo "</pre>";

					//then move file to processed sub folder
					rename($filePath, $phone_csv_folder.'/processed/'.$file);

		        }
		    }
		}

		echo "done";

    }

    function check_overdue_0726($run_date = '', $run_customer_no = 0, $preview = 0)
    {
    	//july 2026 rendition
		$this->load->model(['docs_log_model', 'email_model', 'sms_scheduler_model', 'customer_model', 'whatbot_model', 'message_scheduler_model']);

		$run_date = !empty($run_date) ? $run_date : date('Y-m-d');
		$run_time = strtotime($run_date . ' 12:00:00');

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name','company_full_name','company_phone','company_email','from_name', 
			'reminder_notice', 'reminder_overdue', 'suspension_grace_period', 'reminder_notice2', 'reminder_overdue2', 'bill_generate_day')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$isp_name      = $cfg['isp_name'] ?? '';
		$company_name  = $cfg['company_full_name'] ?? '';
		$company_phone = $cfg['company_phone'] ?? '';
		$company_email = $cfg['company_email'] ?? '';
		$from_name     = $cfg['from_name'] ?? 'no_reply@isp.com';

		$reminder_notice         	= (int) ($cfg['reminder_notice'] ?? 0);
		$reminder_overdue      	= (int) ($cfg['reminder_overdue'] ?? 0);
		$suspension_grace_period  	= (int) ($cfg['suspension_grace_period'] ?? 0);

		$reminder_notice2  	= (int) ($cfg['reminder_notice2'] ?? 0);
		$reminder_overdue2  	= (int) ($cfg['reminder_overdue2'] ?? 0);

		$bill_generate_day = (int) ($cfg['bill_generate_day'] ?? 1);

		//move to setting in the future
		$reminder_x_times = 1;

		//email templates
		$emailInfo1 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER BEFORE DUE' AND is_default = 1 " );
		$emailInfo2 = $this->email_model->get_email_template_detail( " AND template_name = 'OVERDUE REMINDER' AND is_default = 1 " );
		$emailInfo3 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER BEFORE SUSPENSION' AND is_default = 1 " );
		$emailInfo4 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER ABOUT SUSPENSION' AND is_default = 1 " );

		//sms templates
		$smsInfo1 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder Before Due' AND is_default = 1 " );
		$smsInfo2 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Overdue SMS' AND is_default = 1 " );
		$smsInfo3 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder Before Suspension' AND is_default = 1 " );
		$smsInfo4 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder About Suspension' AND is_default = 1 " );

		if ($run_customer_no == 'i') {
			//ignore - just so can run param 3
			$run_customer_no = '';
		}

		$cwhere = !empty($run_customer_no)
			? " AND c.customer_no = '" . $this->db->escape_str($run_customer_no) . "'"
			: '';

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
					b.bill_due_date  
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
				WHERE c.category IN ('r', 'b') 
				AND IFNULL(cs.status, 'P') = 'A'
				{$cwhere}";

		//echo $sql; exit;
		if (!empty($preview)) {
			echo $sql; echo "<br>";
		}

		$rows = $this->db->query($sql)->result_array();
		$notice_list = [];

		foreach ($rows as $r) {

			$notice = [
				'customer_no'   => $r['customer_no'],
				'customer_name' => $r['customer_name'],
				'email_1'       => $r['pic_email_1'],
				'email_2'       => $r['pic_email_2'],
				'mobile'       	=> $r['pic_mobile'],
				'bill_date'		=> $r['bill_date'],
				'balance'		=> $r['balance'],
				'latest_status' => $r['latest_status'], 
				'payment_term'	=> $r['payment_term'],
				'due_date'		=> $r['bill_due_date'],
				'updated_balance' => $r['updated_balance'],
			];

			$suspension_notice = 0;
			$notice2_arrs = array();

			//cond 1 = first notice after bill issued
			$cond1 = $reminder_notice;
			//cond 2 = second notice after bill issued (will upgrade to x times in future)
			$cond2 = $reminder_notice + $reminder_notice2;
			//cond 3 = first overdue notice after term
			$cond3 = $notice['payment_term'] + $reminder_overdue;
			//cond 4 = second overdue notice after term (will upgrade to x times in future)
			$cond4 = $notice['payment_term'] + $reminder_overdue + $reminder_overdue2;
			//cond 5 = suspension grace... concurrent with overdue notice
			$cond5 = $notice['payment_term'] + $suspension_grace_period;

			if ($preview === 'preview') {
				echo "If Current Month:<br>";
				echo "cond1 : ".$cond1." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond1-1).' days')); echo "<br>";
				echo "cond2 : ".$cond2." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond2-1).' days')); echo "<br>";
				echo "cond3 : ".$cond3." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond3-1).' days')); echo "<br>";
				echo "cond4 : ".$cond4." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond4-1).' days')); echo "<br>";
				echo "cond5 : ".$cond5." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond5-1).' days')); echo "<br>";
			}

			//for each customer row, send to a model function to process
				//return an array with bill no as key and the overdue amount
			$overdue_obj = $this->bill_model->get_overdue_obj($r['customer_no']);


			//from the array above, construct the notice obj
			echo "<pre>";
			print_r($overdue_obj);
			echo "</pre>";

		}

		if (!empty($preview)) {
			echo "<pre>";
			print_r($notice_list);
			echo "</pre>";
			//exit;
		}

		echo "Done";

    }

    function test_overdue_obj($customer_no)
    {
    	$this->load->model('bill_model');
		$overdue_obj = $this->bill_model->get_overdue_obj($customer_no);


		//from the array above, construct the notice obj
		echo "<pre>";
		print_r($overdue_obj);
		echo "</pre>";

    }

	function check_overdue($run_date = '', $run_customer_no = 0, $preview = 0)
	{
		$this->load->model(['docs_log_model', 'email_model', 'sms_scheduler_model', 'customer_model', 'whatbot_model', 'message_scheduler_model']);
		$this->load->library('push_service');
		$this->push_service->reset_budget();

		$run_date = !empty($run_date) ? $run_date : date('Y-m-d');
		$run_time = strtotime($run_date . ' 12:00:00');

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('isp_name','company_full_name','company_phone','company_email','from_name', 
			'reminder_notice', 'reminder_overdue', 'suspension_grace_period', 'reminder_notice2', 'reminder_overdue2', 'bill_generate_day')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$isp_name      = $cfg['isp_name'] ?? '';
		$company_name  = $cfg['company_full_name'] ?? '';
		$company_phone = $cfg['company_phone'] ?? '';
		$company_email = $cfg['company_email'] ?? '';
		$from_name     = $cfg['from_name'] ?? 'no_reply@isp.com';

		$reminder_notice         	= (int) ($cfg['reminder_notice'] ?? 0);
		$reminder_overdue      	= (int) ($cfg['reminder_overdue'] ?? 0);
		$suspension_grace_period  	= (int) ($cfg['suspension_grace_period'] ?? 0);

		$reminder_notice2  	= (int) ($cfg['reminder_notice2'] ?? 0);
		$reminder_overdue2  	= (int) ($cfg['reminder_overdue2'] ?? 0);

		$bill_generate_day = (int) ($cfg['bill_generate_day'] ?? 1);

		//move to setting in the future
		$reminder_x_times = 1;

		/*$emailInfo 				= $this->email_model->get_email_template_detail(" AND template_name = 'SUSPENSION REMINDER'");
		$smsBeforeSuspension   	= $this->sms_scheduler_model->get_sms_template_detail(" AND template_name = 'Reminder Before Suspension'");
		$smsAboutSuspension   	= $this->sms_scheduler_model->get_sms_template_detail(" AND template_name = 'Reminder About Suspension'");*/

		//email templates
		$emailInfo1 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER BEFORE DUE' AND is_default = 1 " );
		$emailInfo2 = $this->email_model->get_email_template_detail( " AND template_name = 'OVERDUE REMINDER' AND is_default = 1 " );
		$emailInfo3 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER BEFORE SUSPENSION' AND is_default = 1 " );
		$emailInfo4 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER ABOUT SUSPENSION' AND is_default = 1 " );

		//sms templates
		$smsInfo1 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder Before Due' AND is_default = 1 " );
		$smsInfo2 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Overdue SMS' AND is_default = 1 " );
		$smsInfo3 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder Before Suspension' AND is_default = 1 " );
		$smsInfo4 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder About Suspension' AND is_default = 1 " );

		/*if (empty($emailInfo)) {
			log_message('error', 'Suspension reminder email template not found.');
			return;
		}*/

		if ($run_customer_no == 'i') {
			//ignore - just so can run param 3
			$run_customer_no = '';
		}

		$cwhere = !empty($run_customer_no)
			? " AND c.customer_no = '" . $this->db->escape_str($run_customer_no) . "'"
			: '';

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
					b.bill_due_date  
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
				WHERE c.category IN ('r', 'b') 
				AND IFNULL(cs.status, 'P') = 'A'
				{$cwhere}";

		//echo $sql; exit;
		if ($preview === 'preview') {
			echo $sql; echo "<br>";
		}

		$rows = $this->db->query($sql)->result_array();

		if ($preview === 'preview') {
			echo json_encode($rows); echo "<br>";
		}
		$notice_list = [];

		foreach ($rows as $r) {

			$notice = [
				'customer_no'   	=> $r['customer_no'],
				'customer_name' 	=> $r['customer_name'],
				'email_1'       	=> $r['pic_email_1'],
				'email_2'       	=> $r['pic_email_2'],
				'mobile'       		=> $r['pic_mobile'],
				'bill_date'			=> $r['bill_date'],
				'balance'			=> $r['balance'],
				'latest_status' 	=> $r['latest_status'], 
				'payment_term'		=> $r['payment_term'],
				'due_date'			=> $r['bill_due_date'],
				'updated_balance' 	=> $r['updated_balance'],
			];

			$suspension_notice = 0;
			$notice2_arrs = array();

			$last_payment_date = !empty($r['last_payment_date'])
				? date('Y-m-d', strtotime($r['last_payment_date']))
				: '';

			//cond 1 = first notice after bill issued
			$cond1 = $reminder_notice;
			//cond 2 = second notice after bill issued (will upgrade to x times in future)
			$cond2 = $reminder_notice + $reminder_notice2;
			//cond 3 = first overdue notice after term
			$cond3 = $notice['payment_term'] + $reminder_overdue;
			//cond 4 = second overdue notice after term (will upgrade to x times in future)
			$cond4 = $notice['payment_term'] + $reminder_overdue + $reminder_overdue2;
			//cond 5 = suspension grace... concurrent with overdue notice
			$cond5 = $notice['payment_term'] + $suspension_grace_period;

			if ($preview === 'preview') {
				echo "If Current Month:<br>";
				echo "cond1 : ".$cond1." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond1+$bill_generate_day-1).' days')); echo "<br>";
				echo "cond2 : ".$cond2." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond2+$bill_generate_day-1).' days')); echo "<br>";
				echo "cond3 : ".$cond3." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond3+$bill_generate_day-1).' days')); echo "<br>";
				echo "cond4 : ".$cond4." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond4+$bill_generate_day-1).' days')); echo "<br>";
				echo "cond5 : ".$cond5." days ".date('Y-m-d', strtotime($r['bill_date']. ' + '.($cond5+$bill_generate_day-1).' days')); echo "<br>";
			}

			if ($notice['updated_balance'] <= 0) {
				continue;
			}

			if (!empty($last_payment_date)) {

				$days_since_payment = (strtotime(date('Y-m-d')) - strtotime($last_payment_date)) / 86400;  // get days difference for last payment until now

				$last_payment_ts = strtotime($last_payment_date);
				$now_ts          = time();

				//if true = customer paid for last month but not this month = notice only
				//if false = customer also didnt pay for last month = overdue + suspension 
					//if false = check with cond3 which is term + overdue1
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

				// Determine to Stage 3 / 4 only if:
				// No payment for 28+ days
				// No payment made after last month’s bill (7th)
				// No payment recorded on the bill (means so far no payment received last month)
				if ($days_since_payment >= $cond3 && !$payment_within_preview_bill_period && $payment_received <= 0) {

					//overdue and suspension grace

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
						$notice['bill_no'] = $bill['bill_no'];
						$notice['stage'] = 4;
						$notice['dev_notes'] = 'cond4';
						$notice['due_date'] = $bill['bill_due_date'];
					} elseif ($days_since_bill >= $cond3) {
						$notice['bill_no'] = $bill['bill_no'];
						$notice['stage'] = 3;
						$notice['dev_notes'] = 'cond3';
						$notice['due_date'] = $bill['bill_due_date'];
					} else {
						//don't continue, still got cond5 at the bottom
						//continue;
					}

					//grace period for suspension concurrent with overdue notice
					if ($days_since_bill >= $cond5) {
						$notice2_arrs['bill_no'] = $bill['bill_no'];
						$suspension_notice = 1;
					}

					if ($preview === 'preview') {
						echo "Prev Month:<br>";
						echo "cond1 : ".$cond1." days ".date('Y-m-d', strtotime($bill['bill_date']. ' + '.($cond1+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond2 : ".$cond2." days ".date('Y-m-d', strtotime($bill['bill_date']. ' + '.($cond2+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond3 : ".$cond3." days ".date('Y-m-d', strtotime($bill['bill_date']. ' + '.($cond3+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond4 : ".$cond4." days ".date('Y-m-d', strtotime($bill['bill_date']. ' + '.($cond4+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond5 : ".$cond5." days ".date('Y-m-d', strtotime($bill['bill_date']. ' + '.($cond5+$bill_generate_day-1).' days')); echo "<br>";
					}

				} else {

					//notice only

					$sql = "SELECT bill_no, bill_date, balance
							FROM bill
							WHERE customer_no = '{$r['customer_no']}'
							AND balance > 0
							AND DATE_FORMAT(bill_date, '%Y-%m') = '{$current_month}'
							ORDER BY bill_date ASC
							LIMIT 1";

					$bill = $this->db->query($sql)->row_array();
					//if this month bill is 0 then skip
					if (empty($bill) || $bill['balance'] <= 0) continue;

					$days_since_bill = floor(($run_time - strtotime(date('Y-m-0'.$bill_generate_day, strtotime($bill['bill_date'])) . ' 12:00:00')) / 86400);

					//technically cond1 and 2 are the same stage - but for now we keep the logic like this so not confuse
					if ($days_since_bill >= $cond1 && $days_since_bill < $cond2) {
						$notice['bill_no'] = $bill['bill_no'];
						$notice['stage'] = 1;
						$notice['dev_notes'] = 'cond1';
					} elseif ($days_since_bill >= $cond2 && $days_since_bill < $cond3) {
						$notice['bill_no'] = $bill['bill_no'];
						$notice['stage'] = 2;
						$notice['dev_notes'] = 'cond2';
					}
				}

			} else {

				//no last payment date at all, check starting balance > 0 for first bill

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
					$notice['bill_no'] = $bill['bill_no'];
					$notice['stage'] = 4;
					$notice['dev_notes'] = 'cond4';
					$notice['due_date'] = $last_bill['bill_due_date'] ?? $bill['bill_due_date'];
				} elseif ($days_since_bill >= $cond3) {
					$notice['bill_no'] = $bill['bill_no'];
					$notice['stage'] = 3;
					$notice['dev_notes'] = 'cond3';
					$notice['due_date'] = $last_bill['bill_due_date'] ?? $bill['bill_due_date'];
				} elseif ($days_since_bill >= $cond2) {
					$notice['bill_no'] = $bill['bill_no'];
					$notice['stage'] = 2;
					$notice['dev_notes'] = 'cond2';
				} elseif ($days_since_bill >= $cond1) {
					$notice['bill_no'] = $bill['bill_no'];
					$notice['stage'] = 1;
					$notice['dev_notes'] = 'cond1';
				} else {
					//don't continue, still got cond5 at the bottom
					//continue;
				}

				//grace period for suspension concurrent with overdue notice
				if ($days_since_bill >= $cond5) {
					$notice2_arrs['bill_no'] = $bill['bill_no'];
					$suspension_notice = 1;
				}

				if ($preview === 'preview') {
					if (!empty($last_bill)) {
						echo "Prev Month:<br>";
						echo "cond1 : ".$cond1." days ".date('Y-m-d', strtotime($last_bill['bill_date']. ' + '.($cond1+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond2 : ".$cond2." days ".date('Y-m-d', strtotime($last_bill['bill_date']. ' + '.($cond2+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond3 : ".$cond3." days ".date('Y-m-d', strtotime($last_bill['bill_date']. ' + '.($cond3+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond4 : ".$cond4." days ".date('Y-m-d', strtotime($last_bill['bill_date']. ' + '.($cond4+$bill_generate_day-1).' days')); echo "<br>";
						echo "cond5 : ".$cond5." days ".date('Y-m-d', strtotime($last_bill['bill_date']. ' + '.($cond5+$bill_generate_day-1).' days')); echo "<br>";
					}
				}

			}

			//for each user - gather all the notice that will be sent out - could be more than 1 for example overdue + suspension
			//check for each notice, whether it has been sent or not, check using reminder_status table based on times and also stage

			if (isset($notice['bill_no'])) {
				$prep_reminder_sent = 0;

				//do check in reminder status
				$chk = $this->common_model->check_reminder_sent_times($notice['customer_no'], $notice['bill_no'], $notice['stage'], $reminder_x_times);
				if ($chk) {
					//fulfilled reminder sent promise, so can skip
					$prep_reminder_sent = 1;
				}

				$notice['reminder_sent'] = $prep_reminder_sent;
				$notice_list[] = $notice;
			}

			if ($suspension_notice == 1) {
				$notice2 = [
					'customer_no'   => $r['customer_no'],
					'customer_name' => $r['customer_name'],
					'email_1'       => $r['pic_email_1'],
					'email_2'       => $r['pic_email_2'],
					'mobile'       	=> $r['pic_mobile'],
					'bill_date'		=> $r['bill_date'],
					'balance'		=> $r['balance'],
					'latest_status' => $r['latest_status'], 
					'payment_term'	=> $r['payment_term'],
					'bill_no' => $notice2_arrs['bill_no'],
					'stage' => 6,
					'dev_notes' => 'cond5',
					'due_date' => $r['bill_due_date'],
					'updated_balance' => $r['updated_balance'],
				];

				$prep_reminder_sent = 0;

				//do check in reminder status
				$chk = $this->common_model->check_reminder_sent_times($notice2['customer_no'], $notice2['bill_no'], $notice2['stage'], 1);
				if ($chk) {
					//fulfilled reminder sent promise, so can skip
					$prep_reminder_sent = 1;
				}

				$notice2['reminder_sent'] = $prep_reminder_sent;

				$notice_list[] = $notice2;
			}
		}

		if ($preview === 'preview') {
			echo "<pre>";
			print_r($notice_list);
			echo "</pre>";
			//exit;
		}

		$suspend_acc = array();
		
		foreach ($notice_list as $notice) {

			//stage 1 = notice 1 & 2
			//stage 2 = overdue 1 & 2
			//stage 3 = suspension notice

			$bill_no       	= $notice['bill_no'];
			$stage         	= $notice['stage'];
			$customer_no   	= $notice['customer_no'];
			$customer_name 	= $notice['customer_name'];
			$email_1       	= $notice['email_1'];
			$email_2       	= $notice['email_2'];
			$mobile       	= $notice['mobile'];
			$bill_date		= $notice['bill_date'];
			$balance       	= $notice['balance'];
			$reminder_sent 	= $notice['reminder_sent'];
			$latest_status 	= $notice['latest_status'];

			$updated_balance = $notice['updated_balance'];

			if ($updated_balance <= 0) {
				log_message('error', "Customer Overpaid: $customer_no $updated_balance");
				continue;
			}

			$bill_due_date = $notice['due_date'];

			$day_left 	= $notice['day_left'] ?? 'a few';
			// for mobile notification use
			$day_left_2 	= max(0, (strtotime($bill_due_date) - strtotime(date('Y-m-d'))) / 86400);

			if ($stage == 6 && $latest_status === 'A' && $preview != 'preview') {
				//temporary disable auto suspension as per instructed - dont send email/whatsapp out as well
				log_message('error', "SUPPOSED SUSPEND: $customer_no");
				$suspend_acc[] = $customer_no;
				continue;
			}

			//stage 6 notice + suspension
			if ($stage == 6 && $latest_status === 'A' && $preview != 'preview') {
				$this->customer_model->create_status_record($customer_no, 'S', date('Y-m-d'), 0);
				log_message('error', "Start: Suspending account: $customer_no");
				$this->customer_model->varied_dia_suspend_account($customer_no);
				log_message('error', "End: Suspending account: $customer_no");
			}

			if ($reminder_sent == 1) continue;

			$template_name = 'REMINDER BEFORE DUE';

			if ($stage == 1 || $stage == 2) {
				$email_msg 			= $emailInfo1['email_msg'];
				$email_title 		= $emailInfo1['email_title'];
				$whatsapp_message 	= $smsInfo1['sms_msg'];
				$whatsapp_title 	= $smsInfo1['sms_title'];
				$push_type = 'bill_reminder';
			} else if ($stage == 3 || $stage == 4) {
				$email_msg 			= $emailInfo2['email_msg'];
				$email_title 		= $emailInfo2['email_title'];
				$whatsapp_message 	= $smsInfo2['sms_msg'];
				$whatsapp_title 	= $smsInfo2['sms_title'];
				$template_name 		= 'OVERDUE SMS';
				$push_type = 'bill_overdue';
			} else if ($stage == 6) {
				$email_msg 			= $emailInfo4['email_msg'];
				$email_title 		= $emailInfo4['email_title'];
				$whatsapp_message 	= $smsInfo4['sms_msg'];
				$whatsapp_title 	= $smsInfo4['sms_title'];
				$template_name 		= 'REMINDER ABOUT SUSPENSION';
				$push_type = 'bill_suspension';
			} else {
				$push_type = '';
				continue;
			}

			$email_msg = str_replace('%SUBSCRIBER_NO%', $customer_no, $email_msg);
			$email_msg = str_replace('%CUSTOMER_NAME%', $customer_name, $email_msg);
			$email_msg = str_replace('%ISP_NAME%', $isp_name, $email_msg);
			$email_msg = str_replace('%COMPANY_NAME%', $company_name, $email_msg);
			$email_msg = str_replace('%COMPANY_PHONE%', $company_phone, $email_msg);
			$email_msg = str_replace('%COMPANY_EMAIL%', $company_email, $email_msg);
			//$email_msg = str_replace('%SUSPENSION_REMINDER_CONTENT%', $suspension_content, $email_msg);
			$email_msg = str_replace('%BALANCE%', $updated_balance, $email_msg);
			$email_msg = str_replace('%DUE_DATE%', $bill_due_date, $email_msg);

			$email_title = str_replace('%ISP_NAME%', $isp_name, $email_title);

			$recipient_email = !empty($email_1) ? $email_1 : $email_2;
			if (!empty($recipient_email)) {
				if ($preview !== 'preview') {
					log_message('error', "Start: Scheduling email for suspension notice for customer " . $customer_no);
					$this->email_model->add_email_schedule([
						'recipient_emails'  => $recipient_email,
						'scheduler_id'        => '',
						'send_by'             => '',
						'email_title'       => $email_title,
						'email_msg'         => $email_msg,
						'email_cust_status' => 1,
						'email_attachment'    => '',
						'email_schedule_on' => date("Y-m-d 12:00:00")
					]);
					log_message('error', "End: Scheduling email for suspension notice for customer " . $customer_no);
				}
			}

			if ($preview !== 'preview') {
				log_message('error', "Start: Inserting reminder status for customer " . $customer_no . ". Stage: $stage");
				$this->common_model->insert_reminder_status_times([
					'customer_no'   => $customer_no,
					'bill_no'       => $bill_no,
					'reminder_type' => $stage,
					'email'         => 1,
					'whatsapp'      => 0,
					'telegram'      => 0
				]);
				log_message('error', "End: Inserting reminder status for customer " . $customer_no . ". Stage: $stage");
			}

			// Push notification to the customer's mobile app.
			if ($preview !== 'preview') {
				try {
					if ($push_type !== '') {
						$this->push_service->notify_customer(
							$customer_no,
							$push_type,
							array(
								'CUSTOMER_NO'	=> $customer_no,
								'BALANCE'		=> $updated_balance,
								'DUE_DATE'		=> $bill_due_date,
								'DAYS_LEFT'		=> $day_left_2,
							),
							array(
								'related_id'	=> $bill_no,
								'send_at'		=> $this->push_service->today_at('12:00:00'), // implies defer
								// One notification per bill per stage. remove if allow duplicate notification.
								'dedupe_key'	=> $push_type . ':' . $customer_no . ':' . $bill_no . ':' . $stage,
							)
						);
					}
				} catch (Exception $e) {
					log_message('error', '[push] overdue notifiation failed for ' . $customer_no . ' stage ' . $stage . ': ' . $e->getMessage());
				}
			}

			if(!isValidGlobalPhone($mobile)) continue;

			$meta_template = $this->whatsapp_template->build(
				$template_name,
				[
					'customer_no' 	=> !empty($customer_no) ? $customer_no : '-',
					'balance' 		=> isset($updated_balance) && $updated_balance !== ''
										? $updated_balance
										: '-',
					'bill_due_date' => !empty($bill_due_date) ? $bill_due_date : '-',
				]
			);

			$day_left .= ' days';
			$bill_month 		= date('Y F', strtotime($bill_date));

			$whatsapp_message = str_replace('%ISP_NAME%', $isp_name, $whatsapp_message);
			$whatsapp_message = str_replace('%BILL_MONTH%', $bill_month, $whatsapp_message);
			$whatsapp_message = str_replace('%ACCOUNT_NUMBER%', $customer_no, $whatsapp_message);
			$whatsapp_message = str_replace('%OVERDUE_AMOUNT%', $updated_balance, $whatsapp_message);
			//$whatsapp_message = str_replace('%DAYS_LEFT%', $day_left, $whatsapp_message);
			$whatsapp_message = str_replace('%DUE_DATE%', $bill_due_date, $whatsapp_message);
			$whatsapp_message = str_replace('%BALANCE%', $updated_balance, $whatsapp_message);
			$whatsapp_message = str_replace('%SUBSCRIBER_NO%', $customer_no, $whatsapp_message);

			$whatsapp_title = str_replace('%ISP_NAME%', $isp_name, $whatsapp_title);
			
			// WHATSAPP
			$send_array = [
				'send_type' => 'custom',
				'acc_id' => 0,
				'customer_no' => $customer_no,
				'user_id' => 0,
				'controller' => 'cron',
				'doc_id' => 0,
				'send_method' => 'auto',
				'acc_name' => 'Itelco User',
				'subject' => '[FOLLOW WHATSAPP META TEMPLATE]',
				'body' => '[FOLLOW WHATSAPP META TEMPLATE]',
				'from' => $from_name,
				'email_starter' => '',
				'doc_type' => "[Reminder before due whatsapp]",
				'whatsapp_list' => [$mobile],
				'attachment' => [],
				'meta_template' => $meta_template['meta_template_name'] ?? '',
				'meta_vars' => $meta_template['meta_variable'] ?? []
			];

			// temporarily set the msg_schedule_on to random time from 10am to before 8pm default value is 12:00:00
			$message_data = [
				'message' => $whatsapp_title . "\n" . $whatsapp_message,
				'msg_type' => 'whatsapp',
				'msg_to' => $mobile,
				'customer_no' => $customer_no,
				'msg_schedule_on' => date('Y-m-d ' . rand(10, 19) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT))
			];
			if ($preview !== 'preview') {
				log_message('error', "Start: Scheduling whatsapp message for suspension notice for customer " . $customer_no);
				$this->message_scheduler_model->insert_new_scheduled_message($message_data, $send_array);
				log_message('error', "End: Scheduling whatsapp message for suspension notice for customer " . $customer_no);
			}

			if ($preview === 'preview') {
				echo "Send To:".$recipient_email; echo "<br>";
				echo $email_title; echo "<br>";
				echo $email_msg; echo "<br><br>";
				echo "Mobile:".$mobile; echo "<br>";
				echo $whatsapp_title; echo "<br>";
				echo $whatsapp_message; echo "<br><br>";
			}

		}

		//TODO
		//reminder check split out between email and whatsapp
		//also split out the reminder start/stop based on both email and whatsapp

		if (!empty($suspend_acc) && $preview !== 'preview') {
			log_message('error', 'Informing Admin about suspended accounts.');
			$this->email_model->add_email_schedule([
				'recipient_emails'  => 'valerie@infonal.com.my',
				'scheduler_id'        => '',
				'send_by'             => '',
				'email_title'       => 'ACCOUNTS THAT STILL NOT YET PAY',
				'email_msg'         => implode(",", $suspend_acc),
				'email_cust_status' => 1,
				'email_attachment'    => '',
				'email_schedule_on' => date("Y-m-d 12:00:00")
			]);
		}

		echo "Done";
	}

	/*function prep_robot() {
		$this->load->model('message_scheduler_model');
		$send_array = [
			'send_type' => 'custom',
			'acc_id' => 0,
			'customer_no' => 0,
			'user_id' => 0,
			'controller' => 'cron',
			'doc_id' => 0,
			'send_method' => 'auto',
			'acc_name' => 'Itelco User',
			'subject' => 'Whatsapp Checkup',
			'body' => "[Whatsapp Checkup] " . "\n" . "How are you doing today?",
			'from' => "Itelco System",
			'email_starter' => '',
			'doc_type' => "[Whatsapp Checkup]",
			'whatsapp_list' => ['60138810470'],
			'attachment' => []
		];
		$message_data = [
			'message' => "[Whatsapp Checkup] " . "\n" . "How are you doing today?",
			'msg_type' => 'whatsapp',
			'msg_to' => '60138810470',
			'customer_no' => 0,
			'msg_schedule_on' => date('Y-m-d H:'.str_pad(rand(5, 59),2,"0",STR_PAD_LEFT).':00'),
		];
		$this->message_scheduler_model->insert_new_scheduled_message($message_data, $send_array);
	}*/

	function sending_new_contact_whatsapp_msg() {
		log_message('error', 'Start: Checking and sending new whatsapp contact message');
		$this->load->model('docs_log_model');
		$this->load->model('message_scheduler_model');
		$sql = "SELECT a.*, b.msg_to FROM `new_whatsapp_contact_scheduler` a LEFT JOIN message_outgoing b ON (a.outgoing_id = b.outgoing_id) WHERE a.`retry_times` < 3 ORDER BY a.retry_times ASC LIMIT 1";
		$query = $this->db->query($sql);
		$result = $query->result_array();

		foreach ($result as $val) {
			$send_array = json_decode($val['data'], true);
			//add a greeting
			if ($this->docs_log_model->do_send($send_array)['status'] != 'succ') {
				$retry_times = $val['retry_times'] + 1;

				$this->db->update('new_whatsapp_contact_scheduler', ['retry_times' => $retry_times], ['id' => $val['id']]);

				$outgoing_msg_data = [
					'msg_attempt' => $retry_times
				];

				if($outgoing_msg_data['msg_attempt'] >= 3) {
					$outgoing_msg_data['msg_status'] = 'F';
				}
			} else {
				$outgoing_msg_data = [
					'msg_sent_on' => date('Y-m-d H:i:s'),
					'msg_status' => 'S'
				];
				$this->db->delete('new_whatsapp_contact_scheduler', ['id' => $val['id']]);
			}
			$this->db->update('message_outgoing', $outgoing_msg_data, ['outgoing_id' => $val['outgoing_id']]);
		}
		echo 'Done';
		log_message('error', 'End: Checking and sending new whatsapp contact message');
	}

    function check_contract_expiry($run_date='') {

    	//this check runs once a day, every day
    	if (empty($run_date)) {
    		$run_date = date('Y-m-d');
    	}

    	$this->load->model('docs_log_model');
    	$this->load->model('email_model');
    	$this->load->model('sms_scheduler_model');
    	$this->load->model('customer_model');
		$this->load->model('message_scheduler_model');

    	//get contract expiry date for every customer account

      	$query_str = "	SELECT 	c.profile_id, c.customer_no, p.acc_name as customer_name, c.login_username, c.login_password, IFNULL(pd.pay_date,0) AS last_pay_date ,
								c.building	, p.pic_mobile, p.pic_email_1 AS email_1, p.pic_email_2 AS email_2, p.telegram_id AS telegram_id, cs.transact_date AS latest_status_date, cs.status AS latest_status, csfa.transact_date AS activated_date, p.allow_whatsapp AS allow_whatsapp, p.allow_telegram AS allow_telegram, c.contract_month  
						FROM customer c 
						LEFT JOIN ( SELECT customer_no, MAX(pay_date) as pay_date 
									FROM payment p WHERE p.bill_type = '1' GROUP BY customer_no 
								  ) pd ON pd.customer_no = c.customer_no 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) csfa ON (csfa.customer_no = c.customer_no) 
						LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
						WHERE c.status = 'r' 
						AND c.category != 'w' AND cs.status IN ('A')  
						" ;
		$query		= $this->db->query($query_str);

    	//logic follow the old check_overdue function, but with different tier reminder set in to be sent out to customer

		//maybe might have x time of reminders before..???
		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'suspend' AND `key` = 'reminder_contract' ");
		$reminder_contract = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : 0;

		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'isp_name' ");
		$isp_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$company_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
		$company_phone = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$config	= $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
		$company_email = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		//email templates
		$emailInfo1 = $this->email_model->get_email_template_detail( " AND template_name = 'REMINDER BEFORE CONTRACT' AND is_default = 1 " );

		//sms templates
		$smsInfo1 = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Reminder Before Contract' AND is_default = 1 " );
		
		foreach ($query->result_array() as $row) {

			$whatsapp_tick = $row['allow_whatsapp'] ?? 1;
			$telegram_tick = $row['allow_telegram'] ?? 1;

    		//if contract months is not 0 , calc time of expiry, then check setting X days before contract expiry and send reminder
    		if (!empty($row['contract_month'])) {

   				$contract_expiry = date('Y-m-d', strtotime("+".$row['contract_month']." months", strtotime($row['activated_date'])));

    			$date_today = strtotime($run_date.' 12:00:00');
    			$date_due = strtotime($contract_expiry.' 12:00:00');

      			$diff = $reminder_contract*24*60*60;

    			$due_days_left = floor( (($date_due - $date_today) / (24*60*60)) );

    			if ((($date_due - $date_today) <= $diff) && ($due_days_left > 0) && !empty($reminder_contract)) {

    				//check if reminder sent
    				$chk = $this->common_model->check_reminder_sent($row['customer_no'], 0, 5);

					$rem_status_exists = 0;
    				if (isset($chk['id'])) {
    					$rem_status_exists = $chk['id'];
    				}

    				$email_done = $chk['email'] ?? 0;
    				$whatsapp_done = $chk['whatsapp'] ?? 0;
    				$telegram_done = $chk['telegram'] ?? 0;

    				if (empty($email_done)) {
    					//either not done or failed last time, try again

						$default_email_title = $emailInfo1['email_title'];
						$default_email_msg = $emailInfo1['email_msg'];

						$email_list = array();
						$email_list[] = $row['email_1'];
						if (!empty($row['email_2'])) {
							$email_list[] = $row['email_2'];
						}

						//title
						$email_title = str_replace( "%ISP_NAME%", $isp_name, $default_email_title ) ;

						//msg
						$email_msg = str_replace( "%SUBSCRIBER_NO%", $row['customer_no'] , $default_email_msg ) ;
						$email_msg = str_replace( "%CUSTOMER_NAME%", $row['customer_name'], $email_msg ) ;
						$email_msg = str_replace( "%DUE_DATE%", $contract_expiry, $email_msg ) ;
						$email_msg = str_replace( "%DAYS_LEFT%", $due_days_left.' days', $email_msg ) ;
						$email_msg = str_replace( "%ISP_NAME%", $isp_name, $email_msg ) ;

						$email_msg = str_replace( "%COMPANY_NAME%", $company_name, $email_msg ) ;
						$email_msg = str_replace( "%COMPANY_PHONE%", $company_phone, $email_msg ) ;
						$email_msg = str_replace( "%COMPANY_EMAIL%", $company_email, $email_msg ) ;

						$send_array = array();
						$send_array['send_type'] = 'custom'; /* to_customer, to_backend_user or technician */
						$send_array['acc_id'] = $row['profile_id']; /* technically one should be enough, acc_id will point to profile, or if any product will also just join with profile to get customer details */
						$send_array['customer_no'] = $row['customer_no'];
						$send_array['user_id'] = 0;
						$send_array['controller'] = 'cron'; /* which controller calling this function */
						$send_array['doc_id'] = 0; /* if there is attachment or document related, then the id is here */
						$send_array['send_method'] = 'auto'; /* either auto or manual */
						$send_array['acc_name'] = $row['customer_name'];
						//attachcment related
						$send_array['attachment'] = ''; /* attachcment in full path */
						//email
						$send_array['subject'] = $email_title; /* email subject */
						$send_array['body'] = $email_msg; /* email body */
						$send_array['email_starter'] = $email_title; /* email body header */
						$send_array['doc_type'] = '[Reminder before contract end email]'; /* what kind of email is this */
						$send_array['email_list'] = $email_list;

						$send_result = $this->docs_log_model->do_send($send_array);

						if ($send_result['status'] == 'succ') {
							$email_done = 1;
						} else {
							//failed, do nothing?
						}

    				}

					$meta_template = $this->whatsapp_template->build(
						'REMINDER BEFORE CONTRACT',
						[
							'customer_no' 		=> !empty($row['customer_no']) ? $row['customer_no'] : '-',
							'days_left' 		=> isset($due_days_left) && $due_days_left !== '' ? $due_days_left : '-',
							'contract_due_date' => !empty($contract_expiry) ? $contract_expiry : '-',
						]
					);

					$default_sms_title = $smsInfo1['sms_title'];
					$default_sms_msg = $smsInfo1['sms_msg'];
					$sms_msg = str_replace( "%ACCOUNT_NUMBER%", $row['customer_no'], $default_sms_msg ) ; 
					$sms_msg = str_replace( "%DAYS_LEFT%", $due_days_left, $sms_msg );
					$sms_msg = str_replace( "%DUE_DATE%", $contract_expiry, $sms_msg );

    				if (empty($whatsapp_done) && $whatsapp_tick == 1 && !empty($row['pic_mobile'])) {
						$send_array = array();
						$send_array['send_type'] = 'custom'; /* to_customer, to_backend_user or technician */
						$send_array['acc_id'] = $row['profile_id']; /* technically one should be enough, acc_id will point to profile, or if any product will also just join with profile to get customer details */
						$send_array['customer_no'] = $row['customer_no'];
						$send_array['user_id'] = 0;
						$send_array['controller'] = 'cron'; /* which controller calling this function */
						$send_array['doc_id'] = 0; /* if there is attachment or document related, then the id is here */
						$send_array['send_method'] = 'auto'; /* either auto or manual */
						$send_array['acc_name'] = $row['customer_name'];
						//attachcment related
						$send_array['attachment'] = ''; /* attachcment in full path */
						//email
						$send_array['subject'] = '[FOLLOW WHATSAPP META TEMPLATE]'; /* email subject */
						$send_array['body'] = '[FOLLOW WHATSAPP META TEMPLATE]'; /* email body */
						$send_array['email_starter'] = ''; /* email body header */
						$send_array['doc_type'] = '[Reminder before contract end whatsapp]'; /* what kind of email is this */
						$send_array['whatsapp_list'] = array($row['pic_mobile']);
						$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
						$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];

						$whatsapp_data = [
							'message' => $default_sms_title . "\n" . $sms_msg,
							'msg_type' => 'whatsapp',
							'msg_to' => $row['pic_mobile'],
							'customer_no' => $row['customer_no'],
							'acc_id' => $row['profile_id'],
							'msg_schedule_on' => date('Y-m-d 12:00:00')
						];
						$this->message_scheduler_model->insert_new_scheduled_message($whatsapp_data, $send_array);
						$whatsapp_done = 1;
    				}

    				if (empty($telegram_done) && $telegram_tick == 1 && !empty($row['telegram_id'])) {
						$send_array = array();
						$send_array['send_type'] = 'custom'; /* to_customer, to_backend_user or technician */
						$send_array['acc_id'] = $row['profile_id']; /* technically one should be enough, acc_id will point to profile, or if any product will also just join with profile to get customer details */
						$send_array['customer_no'] = $row['customer_no'];
						$send_array['user_id'] = 0;
						$send_array['controller'] = 'cron'; /* which controller calling this function */
						$send_array['doc_id'] = 0; /* if there is attachment or document related, then the id is here */
						$send_array['send_method'] = 'auto'; /* either auto or manual */
						$send_array['acc_name'] = $row['customer_name'];
						//attachcment related
						$send_array['attachment'] = ''; /* attachcment in full path */
						//email
						$send_array['subject'] = $default_sms_title; /* email subject */
						$send_array['body'] = $sms_msg; /* email body */
						$send_array['email_starter'] = ''; /* email body header */
						$send_array['doc_type'] = '[Reminder before contract end telegram]'; /* what kind of email is this */
						$send_array['telegram_list'] = array($row['telegram_id']);

						$telegram_data = [
							'message' => $default_sms_title . "\n" . $sms_msg,
							'msg_type' => 'telegram',
							'msg_to' => $row['telegram_id'],
							'customer_no' => $row['customer_no'],
							'acc_id' => $row['profile_id'],
							'msg_schedule_on' => date('Y-m-d 12:00:00')
						];
						$this->message_scheduler_model->insert_new_scheduled_message($telegram_data, $send_array);
						$telegram_done = 1;
    				}

					$rem_status = array();
					$rem_status['customer_no'] = $row['customer_no'];
					$rem_status['bill_no'] = 0;
					$rem_status['reminder_type'] = 5;
					$rem_status['email'] = $email_done;
					$rem_status['whatsapp'] = $whatsapp_done;
					$rem_status['telegram'] = $telegram_done;

					if (!empty($rem_status_exists)) {
						$rem_status['id'] = $rem_status_exists;
						$rem_status['times'] = $chk['times'];
						$this->common_model->update_reminder_status($rem_status);
					} else {
						$this->common_model->insert_reminder_status($rem_status);
					}

    			}

    			//for those customer account that has stop service after ticked, calc expiry from activated date
    			//From activation date.  Contract period has nothing to do with billing, it's just if they terminate before contract end there will be penalty
    			//just set to terminated, then send disconenct protocol

				$date_today = strtotime($run_date.' 12:00:00');
				$date_due = strtotime($contract_expiry.' 12:00:00');
				//maybe it might not run on the due date itself, if anything fails
    			if (($date_today == $date_due)) {
    				$this->customer_model->create_status_record($row['customer_no'], 'T', date('Y-m-d'), 0);

    				//DC router
    				$this->customer_model->varied_suspend_account( $row['customer_no'] );
    			}

    		}

		}

		echo "Done";

    }
	
	/**
	 * Automatically update customer account statuses based on date conditions.
	 *
	 * This function is typically executed by a scheduled cron job.
	 * This check runs once a day, every day
	 * It performs three main operations:
	 *
	 * 1. Terminates customers whose `terminated_date` has passed.
	 * 2. Suspends customers whose `suspended_date` has passed.
	 * 3. Reverts free upgrade customers after their free period expires.
	 *
	 * @param string $run_date (optional) Date to check against. Defaults to current date.
	 * @return void
	 */
	function update_customer_status($run_date = '')
	{
		$this->load->model('customer_model');

		/*$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('termination_sop')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$termination_flow = $cfg['termination_sop'] ?? 0;*/

		$period_date = $run_date ?: date('Y-m-d');
		$status_date = empty($run_date) ? 'now()' : "'{$run_date}'";

		///////////////////////////////////////////////////////////
		////////////////////// TERMINATION ////////////////////////
		///////////////////////////////////////////////////////////
		$query_terminate = "
			SELECT c.customer_no, c.terminated_date
			FROM customer c
			LEFT JOIN (
				SELECT acs.* 
				FROM customer_status acs 
				JOIN (
					SELECT customer_no, MAX(status_id) AS max_status_id 
					FROM customer_status 
					GROUP BY customer_no
				) bcs ON acs.status_id = bcs.max_status_id
			) cs ON cs.customer_no = c.customer_no
			WHERE cs.status != 'T'
			AND c.terminated_date < {$status_date}
			AND c.terminated_date NOT IN ('0000-00-00', '1899-12-30')
			AND c.terminated_date IS NOT NULL
		";

		$terminated = $this->db->query($query_terminate)->result_array();

		foreach ($terminated as $row) {
			$this->customer_model->create_status_record($row['customer_no'], 'T', $row['terminated_date'], 0);
			$this->customer_model->varied_suspend_account($row['customer_no'], 1);

			/*if ($termination_flow == 1) {
				$this->customer_model->create_termination_status_record($row['customer_no'], 'C', date('Y-m-d H:i:s'), 0);
			}*/
		}

		///////////////////////////////////////////////////////////
		//////////////////////// SUSPEND //////////////////////////
		///////////////////////////////////////////////////////////
		$query_suspend = "
			SELECT c.customer_no, c.suspended_date
			FROM customer c
			LEFT JOIN (
				SELECT acs.* 
				FROM customer_status acs 
				JOIN (
					SELECT customer_no, MAX(status_id) AS max_status_id 
					FROM customer_status 
					GROUP BY customer_no
				) bcs ON acs.status_id = bcs.max_status_id
			) cs ON cs.customer_no = c.customer_no
			WHERE cs.status != 'S'
			AND c.suspended_date < {$status_date}
			AND c.suspended_date NOT IN ('0000-00-00', '1899-12-30')
			AND c.suspended_date IS NOT NULL
		";

		$suspended = $this->db->query($query_suspend)->result_array();

		foreach ($suspended as $row) {
			$this->customer_model->create_status_record($row['customer_no'], 'S', $row['suspended_date'], 0);
			$this->customer_model->varied_suspend_account($row['customer_no']);
		}

		///////////////////////////////////////////////////////////
		////////////////////// FREE PACKAGE ///////////////////////
		///////////////////////////////////////////////////////////
		$this->db->select('cfu.*, c.*');
		$this->db->from('customer_free_upgrade cfu');
		$this->db->join('customer c', 'cfu.customer_no = c.customer_no', 'left');
		$this->db->where('cfu.free_upgrade_end', date('Y-m-d', strtotime($period_date . '- 1 day')));
		$free_upgrades = $this->db->get()->result_array();

		foreach ($free_upgrades as $row) {
			$isReverted = $this->customer_model->check_radius_group($row['login_username'], $row['package_no']);
			if (!$isReverted) {
				$this->customer_model->revert_free_upgrade($row['customer_no'], $row['package']);
			}
		}

		echo "Done";
	}
		
	function trouble_ticket_aging(){
		/*
		$this->load->model('ticket_model');
		$tickets = $this->ticket_model->get_overdue_trouble_ticket(1440); //1440 = 1 day 
		
		foreach( $tickets AS $ticket ){
		
			$email_subject = "iTelco Trouble Ticket Reminder - " . $ticket['tt_no'] ;
			$email_template = "This ticket has been overdue.";
			$attachments   = "";
			
			$email_to	= array();
			//send to assignee
			if( $ticket['tt_email'] != '' ){
				$emails = explode( ";" , $ticket['tt_email'] );
				foreach( $emails AS $email ){
					$email_to[] = trim($email);
				}
			}
			
			//send to creator
			if( $ticket['created_by'] != '' ){
				$this->load->model('user_model');
				$user = $this->user_model->get_user_by_id( $ticket['created_by'] );
				if( $user['email'] != '' )
					$email_to[]= trim($user['email']);
			}
			
			if( is_array( $email_to ) && !empty($email_to) ){
				$sent_mail = $this->email_model->generate_email($email_subject, 
																$email_template, 
																$attachments, 
																$email_to, 
																"no_reply@itelco.com"); 
				
			}
		
		}
		*/
	}
	
	function check_trouble_ticket()
	{
		/*
		$this->load->model('ticket_model');
		$tickets = $this->ticket_model->get_overdue_trouble_ticket(30);
		
		foreach( $tickets AS $ticket ){
			
			$warning_level = $ticket['warning_level'] + 1;
			$level = $this->ticket_model->get_tt_level( $warning_level, $ticket['tt_category'] );
			
			$email_template = $this->ticket_model->get_tt_email_template();
			$email_template = str_replace( "[tt_no]", $ticket['tt_no'], $email_template );
			$email_template = str_replace( "[tt_recipient]", $level['tt_pic'], $email_template );
			
			
			$email_subject = "Level " . $warning_level . " iTelco Trouble Ticket - " . $ticket['tt_no'] ;
			$attachments   = "";
			
			$email_to	= array();
			//send to next LEVEL
			if( $level['tt_email'] != '' )
				$email_to[] = trim($level['tt_email']);
			
			//send to assignee
			if( $ticket['tt_email'] != '' ){
				$emails = explode( ";" , $ticket['tt_email'] );
				foreach( $emails AS $email ){
					$email_to[] = trim($email);
				}
			}
			
			//send to creator
			if( $ticket['created_by'] != '' ){
				$this->load->model('user_model');
				$user = $this->user_model->get_user_by_id( $ticket['created_by'] );
				if( $user['email'] != '' )
					$email_to[]= trim($user['email']);
			}
			

			$email_to = array_unique( $email_to );

			if( is_array( $email_to ) && !empty($email_to) ){
			
				$sent_mail = $this->email_model->generate_email($email_subject, 
																$email_template, 
																$attachments, 
																$email_to, 
																"no_reply@itelco.com"); 
				
				if( $sent_mail == 1 ){
					$this->ticket_model->update_warning_level( $ticket['tt_no'], $warning_level );
				}
			
			}
		}
		*/
	}
	
	function check_scheduled_email(){
		
		$this->load->model('email_model');
		$this->load->model('docs_log_model');
		
		$smtp_user	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'smtp_user' ");
		
		$smtp_name = ( isset( $smtp_user[0]['val'] ) && $smtp_user[0]['val'] != '' ) ? $smtp_user[0]['val'] : 'no_reply@penangfon.net';

		$smtp_from	= $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'from_name' ");
		
		$from_name = ( isset( $smtp_from[0]['val'] ) && $smtp_from[0]['val'] != '' ) ? $smtp_from[0]['val'] : 'no_reply@itelco.net';
		
		$emails = $this->email_model->get_pending_outgoing_email();

		foreach( $emails AS $email ){
			
			$attachments = "";
			
			//$email_to = explode( ";" , $email['email_to'] );
			
			if( $email['email_attachment'] != '' ){
				$attachments = $email['email_attachment'];
			}

			/*$sent_mail = $this->email_model->generate_email($email['email_title'], 
															$email['email_msg'], 
															$attachments, 
															$email['email_to'], 
															$smtp_name);*/


			$sent_mail = $this->email_model->generate_html_email(
				$email['email_title'],
				$email['email_msg'], 
				'',
				$email['email_to'],
				$from_name,
				'',
				'',
				!empty($attachments) ? $this->config->item('upload_path').'/temp/pdf/'.$attachments : ''
			);
			
			if( $sent_mail != 1 ){
				$this->email_model->update_email_outgoing( $email['outgoing_id'], $email['email_attempt'], 0 );
			}else{
				//success
				$this->email_model->update_email_outgoing( $email['outgoing_id'], $email['email_attempt'], 1 );

				//insert to docs_send_log as well
	            $log_data[] = array(
	                'acc_id'               =>  0,
	                'customer_no'       =>  0,
	                'user_id'   =>  '0',
	                'controller'             =>  'email',
	                'doc_id'               =>  $email['scheduler_id'],
	                'send_type'                =>  'email',
	                'remark'           =>  'Email titled ' . $email['email_title'] . ' to ' . $email['email_to'],
	                'send_method' => 'scheduler',
	            );

	            $this->docs_log_model->log_item($log_data);
	            unset($log_data);

			}
			
		}

		echo "Done";
		
	}

	/**
	 * Generate Termination Emails
	 *
	 * This function automatically sends termination emails to customers
	 * whose service termination date is scheduled for tomorrow. It:
	 *   - Fetches customers scheduled for termination.
	 *   - Loads company and email template configuration.
	 *   - Generates the latest bill PDF (if applicable).
	 *   - Composes and schedules the termination email with attachment.
	 *
	 * @return void
	 */
	function generate_terminated_email(){
		
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '-1');

		$this->load->model('bill_model');		

		$tomorrow = date("Y-m-d", strtotime('tomorrow'));

		$sql =	"	SELECT 	c.category , 
							c.customer_no,c.name, p.pic_email_1 as email_1 ,p.pic_email_2 as email_2,c.mobile_num,b.last_bill AS bill_no   
					FROM customer c 
					LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
					LEFT JOIN (
						select customer_no, max(bill_no) AS last_bill from bill group by customer_no 
					) b ON (b.customer_no = c.customer_no) 
					WHERE c.terminated_date = '".$tomorrow."'   
					AND ( category = 'w' OR category = 'r' OR category = 'b' )
					AND c.bill_by_email = 1 ";

		$query 		= $this->db->query($sql);
		$results 	= $query->result_array();

		if( !empty( $results ) ){

			$config = $this->common_model->get_table('sys_config','*', "`key` = 'company_full_name' OR `key` = 'isp_name' OR `key` = 'company_phone' ");
			$config_arr = array_column($config, 'val', 'key');

			$emailInfo = $this->email_model->get_email_template_detail(" AND template_name = 'TERMINATION EMAIL' AND is_default = 1 ");
			$email_title = $emailInfo['email_title'];
			$email_msg = $emailInfo['email_msg'];

			foreach ($results as $row) {
				$customer_no = $row['customer_no'];
                $pdf_file_name = '['.$row['bill_no'].']'.'BILL_'.$row['customer_no'];

				if( $row['email_1'] != '' || $row['email_2'] != '' ){
					$genPDF = false;
					$post_data = [
						'recipient_emails'		=> $row['email_1'] != '' ? $row['email_1'] : $row['email_2'],
						'scheduler_id' 			=> '',
						'send_by' 				=> '',
						'email_title'			=> '',
						'email_msg'				=> '',
						'email_cust_status' 	=> 1,
						'email_attachment'		=> '',
						'email_schedule_on' 	=> date("Y-m-d 12:00:00")
					];
					
					$email_title = str_replace("%ISP_NAME%", $config_arr['isp_name'] ?? 'itelco', $email_title);
					$post_data['email_title']	= $email_title;
					
					$email_msg = str_replace("%CUSTOMER_NAME%", $row['name'], $email_msg);
					$email_msg = str_replace("%COMPANY_PHONE%", $config_arr['company_phone'] ?? '-', $email_msg);
					$email_msg = str_replace("%COMPANY_NAME%", $config_arr['company_full_name'] ?? 'itelco', $email_msg);
					$email_msg = str_replace("%ISP_NAME%", $config_arr['isp_name'] ?? 'itelco', $email_msg);
					$email_msg = str_replace("%CAPITAL_ISP_NAME%", strtoupper($config_arr['isp_name'] ?? 'itelco'), $email_msg);
					$post_data['email_msg']	= $email_msg ;
					
					if (!empty($row['bill_no'])) {
						if( $row['category'] != 'w' )
							$genPDF = $this->bill_model->generate_bill_pdf( 'bill', $row['bill_no'], 1 , $pdf_file_name );
						elseif( $row['category'] == 'w' )
							$genPDF = $this->bill_model->generate_bill_pdf2( $row['bill_no'], $pdf_file_name );
					}

					if ($genPDF == true) {
						$pdf_file_name = $pdf_file_name.'.pdf';
						$post_data['email_attachment']	= $pdf_file_name ;
					}

					$this->load->model('email_model');
					$this->email_model->add_email_schedule($post_data);
					$this->email_model->customer_update_last_email_sent($customer_no, date('Y-m-d'));
					
				}//end if email
			}
		}
	}

	function test_audit_bill() {
		$pdf_file_name = '[2605000105]bill_8900000622';
		$genPDF = $this->bill_model->generate_bill_pdf( 'bill', '2605000105', 1 , $pdf_file_name );
		if( $genPDF == true ){
			echo "Done.";
		} else {
			echo "Something went wrong.";
		}
	}
	
	function generate_monthly_bill(){

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];
		// check if today is the bill cycle day
		$today = date('d');
		if ($today != $bill_cycle_day) {
			echo "Not bill generated day.";
			return;
		}

		//run at 10 in the morning
		
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		
		$today = date('Y-m-d');

        $this->load->model('whatbot_model');
        $this->load->model('telegram_model');
        $this->load->model('docs_log_model');

		//$config = $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'set_attachment_at' ");
		//$set_attachment_at = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		//$pdf_path = $this->config->item('proj_path').$set_attachment_at;

		$pdf_path = $this->config->item('upload_path')."/temp/pdf";
		
		$sql =	"	SELECT 	c.category , 
							c.customer_no,p.acc_name AS name, p.pic_email_1 AS email_1,p.pic_email_2 AS email_2,p.acc_mobileno AS mobile_num,
							b.bill_no ,b.bill_date, b.bill_due_date,b.balance, '' AS telegram_id, c.profile_id, d.dealer_type, d.email AS agent_email, d.mobile_num AS agent_mobile   
					FROM customer c 
					LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
					INNER JOIN bill b ON c.customer_no = b.customer_no 
					LEFT JOIN dealer d ON (d.dealer_no = c.dealer) 
					WHERE b.bill_date BETWEEN '".date('Y-m-01' , strtotime( $today ) )."' AND '".date('Y-m-t' , strtotime( $today ))."' 
					AND c.bill_by_email = 1 
					AND b.is_manual = 0 AND b.is_void = 0 ";
		
		$query 		= $this->db->query($sql);
		$results 	= $query->result_array();
		
		if( !empty( $results ) ){
			
			$this->load->model('sms_scheduler_model');
			$this->load->library('push_service');
			
			/*$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'inv_no_prefix' ");
			$inv_no_prefix = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';*/
			$inv_no_prefix = '';

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
			$comp_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
			
			$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'AUTO BILLING EMAIL' AND is_default = 1 " );
			$default_email_title = $emailInfo['email_title'];
			$default_email_msg = $emailInfo['email_msg'];

			$smsInfo = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Auto Billing SMS' AND is_default = 1 " );
			$default_sms_title = $smsInfo['sms_title'];
			$default_sms_msg = $smsInfo['sms_msg'];
			$default_sms_msg = str_replace( "%COMP_NAME%", $comp_name, $default_sms_msg ) ; 
			$default_sms_msg = str_replace( "%THIS_MONTH%", date('M Y'), $default_sms_msg );
			
			$date = date( 'MY' );

			$small_cnt = 0;
			$big_cnt = 0;

			$time_slots = array(
				"12:00:00",
				"12:30:00",
				"13:00:00",
				"13:30:00",
				"14:00:00",
				"14:30:00",
				"15:00:00",
				"15:30:00",
				"16:00:00",
				"16:30:00",
				"17:00:00"
			);
			
			foreach ( $results as $row) {
				
				$customer_no = $row['customer_no'];
				
				//for auditable doc... its best to use a file name that can be easily tracked that won't spawn countless duplicates
				$pdf_file_name = '['.$row['bill_no'].']'.'bill_'.$row['customer_no'];
				
				if( $row['category'] != 'w' )
					$genPDF = $this->bill_model->generate_bill_pdf( 'bill', $row['bill_no'], 1 , $pdf_file_name );
				elseif( $row['category'] == 'w' )
					$genPDF = $this->bill_model->generate_bill_pdf2( $row['bill_no'], $pdf_file_name );
					
				$post_data = array();
				
				if( $genPDF == true ){
					
					$this->email_model->customer_update_last_file_gen($pdf_file_name.".pdf",$row['customer_no']);

					if ($row['dealer_type'] == 'R' || $row['dealer_type'] == 'D') {
						$email_1 = $row['agent_email'];
						$email_2 = '';
					} else {
						//normal agent or without agent
						$email_1 = $row['email_1'];
						$email_2 = $row['email_2'];
					}				
					
					if( $email_1 != '' || $email_2 != '' ){
						$post_data['recipient_emails'] = $email_1 != '' ? $email_1 : $email_2 ;
						$pdf_file_name = $pdf_file_name.'.pdf';
						$post_data['scheduler_id'] 	= '';
						$post_data['send_by'] 		= '';
						
						$email_title = str_replace( "%MONTH%", date("M" , strtotime( $today ) ), $default_email_title ) ;
						$email_title = str_replace( "%SUBSCRIBER_NO%", $row['customer_no'] , $email_title ) ;
						$email_title = str_replace( "%CUSTOMER_NAME%", $row['name'], $email_title ) ;
						$post_data['email_title']	= $email_title ;
						
						$email_msg = str_replace("%THIS_MONTH%", date("F Y" , strtotime( $today ) ), $default_email_msg);
						$email_msg = str_replace("%SUBSCRIBER_NO%", $row['customer_no'], $email_msg);
						$email_msg = str_replace("%CUSTOMER_NAME%", $row['name'], $email_msg);
						$email_msg = str_replace("%BILL_DATE%", $row['bill_date'], $email_msg);
						$email_msg = str_replace("%DUE_DATE%", $row['bill_due_date'], $email_msg);
						$email_msg = str_replace("%BALANCE%", $row['balance'], $email_msg);
						$post_data['email_msg']	= $email_msg ;
						
						$post_data['email_cust_status'] = 1;
						$post_data['email_attachment']	= $pdf_file_name ;
						$time_to_send = $time_slots[$big_cnt] ?? "12:00:00";
						$post_data['email_schedule_on'] = date("Y-m-d ".$time_to_send) ;
						$this->load->model('email_model');
						$this->email_model->add_email_schedule( $post_data );
						//send to email 2 as well
						if (($email_1 != '' && $email_2 != '') && ($email_1 != $email_2)) {
							$post_data['recipient_emails'] = $row['email_2'];
							$this->email_model->add_email_schedule( $post_data );
						}
						$this->email_model->customer_update_last_email_sent($row['customer_no'], date('Y-m-d') );

						$small_cnt++;

						if ($small_cnt > 50) {
							$small_cnt = 0;
							$big_cnt++;
						}
						
						/*
						if( $row['mobile_num'] != '' ){

							$attachment = $pdf_path.'/'.$pdf_file_name;
            				$pathinfo = pathinfo($attachment);
            				$filename = $pathinfo['filename'];
            				$mimetype = mime_content_type($attachment);
							
							//change to other function to immediately send out whatsapp or telegram
							$sms_msg = $default_sms_msg; 
							$sms_title = 'Auto Billing SMS Notification for ' . $row['customer_no'] . ' #' . $row['bill_no'];

			                $chat_id = $row['mobile_num'];

			                $mimetype       = $mimetype;
			                $temp_img       = file_get_contents($attachment);
			                $base64_img     = base64_encode($temp_img);
			                $filename       = $filename;

			                $whatsapp_doc_result = $this->whatbot_model->send(
			                    $chat_id, 
			                    $sms_title."\n".$sms_msg, 
			                    '', 
			                    $base64_img, 
			                    $mimetype, 
			                    $filename, 
			                    $row['customer_no']
			                );

			                $log_data[] = array(
			                    'acc_id'               =>  $send_data['profile_id'] ?? 0,
			                    'customer_no'       =>  $send_data['customer_no'] ?? 0,
			                    'user_id'   =>  0,
			                    'controller'             =>  'cron',
			                    'doc_id'               =>  $row['bill_no'],
			                    'send_type'                =>  'whatsapp',
			                    'remark'           =>  'Whastapp Auto-Bill #'.$row['bill_no'].' to ' . $row['name'] . '('.$chat_id.')',
			                    'send_method' => 'auto',
			                );

			                $this->docs_log_model->log_item($log_data);
			                unset($log_data);
							
						}//end if mobile num

						//once telegram id is done
						if ($row['telegram_id'] != '') {

							$attachment = $pdf_path.'/'.$pdf_file_name;
            				$pathinfo = pathinfo($attachment);
            				$filename = $pathinfo['filename'];
            				$mimetype = mime_content_type($attachment);
							
							//change to other function to immediately send out whatsapp or telegram
							$sms_msg = $default_sms_msg; 
							$sms_title = 'Auto Billing SMS Notification for ' . $row['customer_no'] . ' #' . $row['bill_no'];

			                $send_array = array();
			                $send_array['chat_id'] = $row['telegram_id'];
			                $send_array['text'] = $sms_title."\n".$sms_msg;
			                $send_array['attachment'] = $attachment;
			                $send_array['mimetype'] = $mimetype;
			                $send_array['filename'] = $filename;

			                $send_result = $this->telegram_model->send($send_array);

			                $log_data[] = array(
			                    'acc_id'               =>  $send_data['profile_id'] ?? 0,
			                    'customer_no'       =>  $send_data['customer_no'] ?? 0,
			                    'user_id'   =>  0,
			                    'controller'             =>  'cron',
			                    'doc_id'               =>  $row['bill_no'],
			                    'send_type'                =>  'telegram',
			                    'remark'           =>  'Telegram Auto-Bill #'.$row['bill_no'].' to ' . $row['name'] . '('.$row['telegram_id'].')',
			                    'send_method' => 'auto',
			                );

			                $this->docs_log_model->log_item($log_data);
			                unset($log_data);

						}
						*/
						
					}//end if email

					// Push notification to the customer's mobile app.
					try {
						$this->push_service->notify_customer(
							$row['customer_no'],
							'bill_issued',
							array(
								'CUSTOMER_NO'	=> $row['customer_no'],
								'BALANCE'		=> $row['balance'],
								'DUE_DATE'		=> $row['bill_due_date'],
							),
							array(
								'send_at'		=> $this->push_service->today_at('12:00:00'), // implies defer
								// One notification per customer per billing. Remove if allow duplicate notification
								'dedupe_key'	=> 'bill_issued:' . $row['customer_no'] . ':' . date('Y-m', strtotime($row['bill_date'])),
							)
						);
					} catch (Exception $e) {
						log_message('error', '[push] bill issued notification failed for ' . $row['customer_no'] . ': ' . $e->getMessage());
					}
					
				}//end if PDF created
				
			}//foreach customers
		
		}//if customers found
		
	}

	function send_bill_messaging(){

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];
		// check if today is the bill cycle day
		$today = date('d');
		if ($today != $bill_cycle_day) {
			echo "Not bill generated day.";
			return;
		}

		//run at 10 in the morning
		
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		
		$today = date('Y-m-d');

        $this->load->model('whatbot_model');
        $this->load->model('telegram_model');
        $this->load->model('docs_log_model');
		$this->load->model('message_scheduler_model');

		//$config = $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'set_attachment_at' ");
		//$set_attachment_at = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		//$pdf_path = $this->config->item('proj_path').$set_attachment_at;

		$pdf_path = $this->config->item('upload_path')."/temp/pdf";
		
		$sql =	"	SELECT 	c.category , 
							c.customer_no,p.acc_name AS name, p.pic_email_1 AS email_1,p.pic_email_2 AS email_2,p.acc_mobileno AS mobile_num,
							b.bill_no ,b.bill_date, b.bill_due_date,b.balance, p.telegram_id AS telegram_id, c.profile_id, p.allow_telegram, p.allow_whatsapp, d.dealer_type, d.email AS agent_email, d.mobile_num AS agent_mobile    
					FROM customer c 
					LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
					INNER JOIN bill b ON c.customer_no = b.customer_no 
					LEFT JOIN dealer d ON (d.dealer_no = c.dealer) 
					WHERE b.bill_date BETWEEN '".date('Y-m-01' , strtotime( $today ) )."' AND '".date('Y-m-t' , strtotime( $today ))."' 
					AND c.bill_by_email = 1  
					AND b.is_manual = 0 AND b.is_void = 0 AND (p.allow_whatsapp = 1 OR p.allow_telegram = 1) ";
		
		$query 		= $this->db->query($sql);
		$results 	= $query->result_array();

		if( !empty( $results ) ){
			
			$this->load->model('sms_scheduler_model');
			
			/*$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'inv_no_prefix' ");
			$inv_no_prefix = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';*/
			$inv_no_prefix = '';

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
			$comp_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
			
			$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'AUTO BILLING EMAIL' AND is_default = 1 " );
			$default_email_title = $emailInfo['email_title'];
			$default_email_msg = $emailInfo['email_msg'];

			$smsInfo = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Auto Billing SMS' AND is_default = 1 " );
			$default_sms_title = $smsInfo['sms_title'];
			$default_sms_msg = $smsInfo['sms_msg'];
			$default_sms_msg = str_replace( "%COMP_NAME%", $comp_name, $default_sms_msg ) ; 
			$default_sms_msg = str_replace( "%THIS_MONTH%", date('M Y'), $default_sms_msg );
			
			$date = date( 'MY' );
			
			foreach ( $results as $row) {
				
				$customer_no = $row['customer_no'];
				
				$pdf_file_name = '['.$row['bill_no'].']'.'bill_'.$row['customer_no'];
				
				if( $row['category'] != 'w' )
					$genPDF = $this->bill_model->generate_bill_pdf( 'bill', $row['bill_no'], 1 , $pdf_file_name );
				elseif( $row['category'] == 'w' )
					$genPDF = $this->bill_model->generate_bill_pdf2( $row['bill_no'], $pdf_file_name );
					
				$post_data = array();
				
				if( $genPDF == true ){
					
					$this->email_model->customer_update_last_file_gen($pdf_file_name.".pdf",$row['customer_no']);					
					//this is wrong - this function should be checking mobile num and not email
					if( $row['email_1'] != '' || $row['email_2'] != '' ){
						$post_data['recipient_emails'] = $row['email_1'] != '' ? $row['email_1'] : $row['email_2'] ;
						$pdf_file_name = $pdf_file_name.'.pdf';
						$post_data['scheduler_id'] 	= '';
						$post_data['send_by'] 		= '';
						
						$email_title = str_replace( "%MONTH%", date("M" , strtotime( $today ) ), $default_email_title ) ;
						$email_title = str_replace( "%SUBSCRIBER_NO%", $row['customer_no'] , $email_title ) ;
						$email_title = str_replace( "%CUSTOMER_NAME%", $row['name'], $email_title ) ;
						$post_data['email_title']	= $email_title ;
						
						$email_msg = str_replace("%THIS_MONTH%", date("F Y" , strtotime( $today ) ), $default_email_msg);
						$email_msg = str_replace("%SUBSCRIBER_NO%", $row['customer_no'], $email_msg);
						$email_msg = str_replace("%CUSTOMER_NAME%", $row['name'], $email_msg);
						$email_msg = str_replace("%BILL_DATE%", $row['bill_date'], $email_msg);
						$email_msg = str_replace("%DUE_DATE%", $row['bill_due_date'], $email_msg);
						$email_msg = str_replace("%BALANCE%", $row['balance'], $email_msg);
						$post_data['email_msg']	= $email_msg ;
						
						$post_data['email_cust_status'] = 1;
						$post_data['email_attachment']	= $pdf_file_name ;
						$post_data['email_schedule_on'] = date("Y-m-d 12:00:00") ;
						$this->load->model('email_model');

						//$this->email_model->add_email_schedule( $post_data );
						//$this->email_model->customer_update_last_email_sent($row['customer_no'], date('Y-m-d') );

						if ($row['dealer_type'] == 'R' || $row['dealer_type'] == 'D') {
							$mobile_num = $row['agent_mobile'];
							$allow_whatsapp = '1';
						} else {
							$mobile_num = $row['mobile_num'];
							$allow_whatsapp = $row['allow_whatsapp'];
						}
						
						if( $mobile_num != '' && $allow_whatsapp == '1' ){

							$attachment = $pdf_path.'/'.$pdf_file_name;
            				$pathinfo = pathinfo($attachment);
            				$filename = $pathinfo['filename'];
            				$mimetype = mime_content_type($attachment);
							
							//change to other function to immediately send out whatsapp or telegram
							$sms_msg = $default_sms_msg; 
							$sms_title = 'Auto Billing Notification for ' . $row['customer_no'] . ' #' . $row['bill_no'];

			                $chat_id = $mobile_num;

			                $mimetype       = $mimetype;
			                $temp_img       = file_get_contents($attachment);
			                $base64_img     = base64_encode($temp_img);
			                $filename       = $filename;

							$meta_template = $this->whatsapp_template->build(
								'AUTO BILLING',
								[
									'customer_no' => !empty($row['customer_no']) ? $row['customer_no'] : '-',
									'bill_no' => !empty($row['bill_no']) ? $row['bill_no'] : '-',
									'bill_date' => !empty($row['bill_date']) ? $row['bill_date'] : '-',
								]
							);

							$whatsapp_data = [
								'message' => $sms_title."\n".$sms_msg,
								'msg_type' => 'whatsapp',
								'msg_to' => $chat_id,
								'msg_image' => $base64_img,
								'mimetype' => $mimetype,
								'filename' => $filename,
								'customer_no' => $row['customer_no'],
								'msg_schedule_on' => date('Y-m-d 12:15:00')
							];

							$log_data = [
								'acc_id' =>  $row['profile_id'] ?? 0,
			                    'customer_no' =>  $row['customer_no'] ?? 0,
			                    'user_id' =>  0,
			                    'controller' =>  'cron',
			                    'doc_id' =>  $row['bill_no'],
			                    'send_type' =>  'custom',
			                    'remark' =>  'Whatsapp Auto-Bill #'.$row['bill_no'].' to ' . $row['name'] . '('.$chat_id.')',
			                    'send_method' => 'auto',
								'subject' => '[FOLLOW WHATSAPP META TEMPLATE]',
								'body' => '[FOLLOW WHATSAPP META TEMPLATE]',
								'whatsapp_list' => [$chat_id],
								'attachment' => $attachment,
								'send_bill_messaging' => true,
								'meta_template' => $meta_template['meta_template_name'] ?? '',
								'meta_vars' => $meta_template['meta_variable'] ?? []
							];
							$this->message_scheduler_model->insert_new_scheduled_message($whatsapp_data, $log_data);							
						}//end if mobile num

						//once telegram id is done
						if ($row['telegram_id'] != '' && $row['allow_telegram'] == '1' ) {

							$attachment = $pdf_path.'/'.$pdf_file_name;
            				$pathinfo = pathinfo($attachment);
            				$filename = $pathinfo['filename'];
            				$mimetype = mime_content_type($attachment);
							
							//change to other function to immediately send out whatsapp or telegram
							$sms_msg = $default_sms_msg; 
							$sms_title = 'Auto Billing Notification for ' . $row['customer_no'] . ' #' . $row['bill_no'];

							$telegram_data = [
								'message' => $sms_title."\n".$sms_msg,
								'msg_type' => 'telegram',
								'msg_to' => $row['telegram_id'],
								'msg_attachment' => $attachment,
								'mimetype' => $mimetype,
								'filename' => $filename,
								'customer_no' => $row['customer_no'],
								'msg_schedule_on' => date('Y-m-d 12:15:00')
							];

							$log_data = [
								'acc_id' =>  $row['profile_id'] ?? 0,
			                    'customer_no' =>  $row['customer_no']?? 0,
			                    'user_id' =>  0,
			                    'controller' =>  'cron',
			                    'doc_id' =>  $row['bill_no'],
			                    'send_type' =>  'custom',
			                    'remark' =>  'Telegram Auto-Bill #'.$row['bill_no'].' to ' . $row['name'] . '('.$row['telegram_id'].')',
			                    'send_method' => 'auto',
								'subject' => $sms_title,
								'body' => $sms_msg,
								'telegram_list' => [$row['telegram_id']],
								'attachment' => $attachment,
								'send_bill_messaging' => true
							];
							$this->message_scheduler_model->insert_new_scheduled_message($telegram_data, $log_data);
						}
						
					}//end if email
					
				}//end if PDF created
				
			}//foreach customers
		
		}//if customers found

	}
	
	function generate_overdue_sms( $date = '' ){
		
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		
		$from_date = $date == '' ? date('Y-m-d') : date('Y-m-d' , strtotime( $date ) ); 
		
		$sql =	"	SELECT 	c.customer_no, 
							c.mobile_num,
							b.bill_no,
							b.bill_date , 
							b.bill_due_date ,
							b.balance 
					FROM customer c
					INNER JOIN ( 
						SELECT MAX(bill_no) as bill_no , customer_no
						FROM bill 
						WHERE is_void = 0 
						AND balance > 0
						AND (  DATEDIFF( '".$from_date."' , bill_due_date ) >= '15' )
						GROUP BY customer_no 
					) a ON a.customer_no = c.customer_no 
					INNER JOIN bill b ON b.bill_no = a.bill_no 
					WHERE c.status = 'r' 
					AND c.category = 'r' 
					AND ( c.terminated_date = '0000-00-00' OR c.terminated_date = NULL ) 
					GROUP BY c.customer_no ";
					
			//~ DATEDIFF( '".$from_date."' , bill_due_date ) = '15' 
		//~ OR 	DATEDIFF( '".$from_date."' , bill_due_date ) = '30' 
		//~ OR 	DATEDIFF( '".$from_date."' , bill_due_date ) = '45' 
		//~ OR 	DATEDIFF( '".$from_date."' , bill_due_date ) = '60' 
					
		$query 		= $this->db->query($sql);
		$results 	= $query->result_array();
	
		if( !empty( $results ) ){
			
			$this->load->model('bill_model');
			$this->load->model('sms_scheduler_model');
			
			/*$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'inv_no_prefix' ");
			$inv_no_prefix = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';*/
			$inv_no_prefix = '';

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
			$comp_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
						
			$smsInfo = $this->sms_scheduler_model->get_sms_template_detail( " AND template_name = 'Overdue SMS' AND is_default = 1 " );
			$default_sms_title = $smsInfo['sms_title'];
			$default_sms_msg = $smsInfo['sms_msg'];
			$default_sms_msg = str_replace( '%COMP_NAME%' , $comp_name , $default_sms_msg ) ;
			
			foreach ( $results as $row) {
				
				//if( $row['balance'] <= 0 ) continue ;
				$balance_to_pay  = (float)$row['balance'] ;
				$payment_receive = 0 ;
				if( $row['mobile_num'] != '' ){
					
					//check if any unprocessed payment
					$last_payment = $this->bill_model->get_total_unprocessed_payment_by_customer( $row['customer_no'] , $row['bill_date'] ) ;
					if( !empty( $last_payment ) ){
						$payment_receive += $last_payment['amount'] ; 
					}
					
					//check ALL next bills payment_received after this bill
					$next_bill = $this->bill_model->get_next_bills_paid_amount( $row['customer_no'] , $row['bill_no'] ) ;
					if( !empty( $next_bill ) ){
						if( $next_bill['payment_received'] > 0 ){
							$payment_receive += $next_bill['payment_received'] ; 
						}
					}
					
					if( (float)$payment_receive >= (float)$balance_to_pay ){
						continue ;
					}else{
						$balance_to_pay = (float)$balance_to_pay - (float)$payment_receive ;
					}

					//check if balance is restored < 0 after adjustment
					$next_balance = $this->bill_model->get_next_balance_after_adjustment_amount( $row['customer_no'] , $row['bill_no'] ) ;
					if (isset($next_balance['balance'])) {
						if ((float)$next_balance['balance'] < 0) {
							continue;
						}
					}
					
					echo $row['customer_no'] . "," . $row['bill_no'] . "," . ( $row['balance'] ) . "," . ( $payment_receive ) . "," . number_format( (float)$row['balance'] - (float)$payment_receive , 2 )  . '<br />';
					
					$sms_msg = str_replace( "%DUE_DATE%" , date("d/m/Y" , strtotime( $row['bill_due_date'] ) ) , $default_sms_msg ) ; 
					$sms_msg = str_replace( "%ACCOUNT_NUMBER%", $row['customer_no'], $sms_msg ) ; 
					$sms_msg = str_replace( "%OVERDUE_AMOUNT%", number_format($balance_to_pay,2), $sms_msg ) ; 
					$post_data['sms_scheduler'] = 	array(	'sms_to' => $row['mobile_num'],
															'sms_schedule_on' => date("Y-m-d 09:00:00") , 
															'sms_title'	=> 'Auto Billing SMS Overdue for ' . $row['customer_no'] . ' #' . $row['bill_no'] ,
															'sms_msg'	=> $sms_msg,
															'is_building'	=> '0',
															'sms_building'	=> '',
															'sms_cust_cat'	=> '',
															'sms_cust_status'	=> '',
															'created_date'	=> date("Y-m-d H:i:s"),
															'created_by'	=> 0,
															'modified_date'	=> date("Y-m-d H:i:s"),
															'modified_by'	=> 0,
													);
					
					$this->sms_scheduler_model->sms_add( $post_data['sms_scheduler'] );
					
				}//end if mobile num
			}
		}
		
	}
	
	function check_scheduled_sms(){
		
		$this->load->model('common_model');	
		
		$result		= $this->common_model->get_table('sys_config','*', "`category` = 'sms' AND `key` = 'maxis_sms_id' ");
		$maxis_id 	= ( isset( $result[0]['val'] ) && $result[0]['val'] != '' ) ? $result[0]['val'] : '';
		
		$result 	= $this->common_model->get_table('sys_config','*', "`category` = 'sms' AND `key` = 'maxis_sms_pwd' ");
		$maxis_pwd 	= ( isset( $result[0]['val'] ) && $result[0]['val'] != '' ) ? $result[0]['val'] : '';
		
		if( $maxis_id == '' || $maxis_pwd == '' ){
			log_message('error', 'MAXIS SMS account configuration error.' );
			exit();
		}else{
			//~ log_message('error', 'START Checking scheduled sms ');
			
			$this->load->model('sms_outgoing_model');
			
			//get pending SMS , with less than 3 attempts
			$sms = $this->sms_outgoing_model->get_pending_outgoing_sms();
			
			foreach( $sms AS $row ){
				
				$phone	= str_replace( "-" , "" , trim($row['sms_phone']) ) ; 
				$msg	= trim( $row['sms_msg'] );
				
				$sent_sms = $this->sms_outgoing_model->sms_send( $phone, $msg, $maxis_id, $maxis_pwd );
				
				if( $sent_sms['success'] == 1 ){
					$this->sms_outgoing_model->update_sms_outgoing( $row['sms_id'], $row['sms_attempt'], 1 );
					log_message('error', 'SMS success => ' . $row['sms_phone'] );
				}else{
					$this->sms_outgoing_model->update_sms_outgoing( $row['sms_id'], $row['sms_attempt'], 0 , $sent_sms['remark'] );
					log_message('error', 'SMS error => ' . $row['sms_phone'] . ' remark => ' . $sent_sms['remark'] );
				}
				
			}
			//~ log_message('error', 'END Checking scheduled sms ');
		}
		
	}
	
	function generate_statement_account(){
		
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		
		//~ $this_date	= date('Y-m-d' , strtotime( "2020-01-01" ));
		$this_date	= date('Y-m-d' , strtotime( "2021-05-31" ));
		$date_start = date('Y-m-01', strtotime( "- 3 months " , strtotime( $this_date )) );
		$date_end	= $this_date ;
		//~ $date_file = date( 'MY' , strtotime( "2020-01-01" ) ) ;
		$date_file = date( 'MY' , strtotime( $this_date ) ) ;
		
		$sql =	"	SELECT 	c.category , c.customer_no , c.name , p.pic_email_1 as email_1, p.pic_email_2 as email_2, c.mobile_num 					
					FROM customer c
					LEFT JOIN profile p ON (p.acc_id = c.profile_id)
					WHERE ( 
						c.status = 'r'
						OR ( c.status = 's' AND c.suspended_date >= '2020-01-01 00:00:00' )
						OR c.terminated_date BETWEEN '".$date_start."' AND '".$date_end."'
					)
					ORDER BY c.customer_no ; ";
					
		$query 		= $this->db->query($sql);
		$results 	= $qucery->result_array();

		if( !empty( $results ) ){
			
			$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
			$comp_name = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
			
			//$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'STATEMENT OF ACCOUNT' AND is_default = 1 " );
			$emailInfo = $this->email_model->get_email_template_detail( " AND template_name = 'SOA Error' AND is_default = 1 " );
			$default_email_title = $emailInfo['email_title'];
			$default_email_title = str_replace( "%DATE%", date("d M Y" , strtotime( $this_date ) ), $default_email_title ) ;
			$default_email_msg = $emailInfo['email_msg'];
			
			//$quarter = ceil(date('n' , strtotime($this_date)) / 3);
			$this->load->model('report_model');
			foreach ( $results as $row) {
				
				$customer_no = $row['customer_no'];
				$pdf_name = '['.$date_file.']'.'SOA_'.$row['customer_no'];
				//$genPDF = $this->report_model->generate_soa_pdf( $customer_no, $date_start, $date_end , $pdf_name );
				
				$post_data = array();
				//if( $genPDF == true ){
					
					if( $row['email_1'] != '' || $row['email_2'] != '' ){
						$post_data['recipient_emails'] = $row['email_1'] != '' ? $row['email_1'] : $row['email_2'] ;
						$pdf_name = $pdf_name.'.pdf';
						$post_data['scheduler_id'] 	= '';
						$post_data['send_by'] 		= '';
						
						$new_email_title = str_replace( "%SUBSCRIBER_NO%", $row['customer_no']	, $default_email_title ) ;
						$new_email_title = str_replace( "%CUSTOMER_NAME%", $row['name']			, $new_email_title ) ;
						
						$post_data['email_title']	= $new_email_title ;
						$post_data['email_msg']		= $default_email_msg ;
						
						$post_data['email_cust_status'] = 1;
						//$post_data['email_attachment']	= $pdf_name ;
						$post_data['email_attachment']	= '' ;
						$post_data['email_schedule_on'] = date("Y-m-d 18:00:00") ;
						$this->load->model('email_model');
						$this->email_model->add_email_schedule( $post_data );
												
					}//end if email
					
				//}//end if PDF created
				
			}//foreach customers
		
		}//if customers found
		
	}

	function check_planned_maintenance() {

		//run once a day
		$this->load->model('asset_model');
		$this->load->model('docs_log_model');

		//check each asset planned maintenance record, select last maint_date within this month
    	$query_str = "SELECT pam.*, a.asset_tag, a.asset_name, 
    	CASE 
			WHEN interval_unit = 'd' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value DAY) 
			WHEN interval_unit = 'm' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value MONTH)
			WHEN interval_unit = 'y' THEN DATE_ADD(IFNULL(last_maint_date,start_date), INTERVAL interval_value YEAR) 
		END AS next_maint  
    	FROM planned_asset_maint pam 
    	LEFT JOIN asset a ON (pam.asset_id = a.asset_id) 
    	WHERE a.asset_status = 1 ";
    	$query		= $this->db->query($query_str);

		//check see if the maintenance date is near (use last_maint_date)
		foreach ($query->result_array() as $row) {

			if (empty($row['next_maint'])) {
				log_message('error', 'Cron check planned maintenance, Asset Tag:'.$row['asset_tag'].' No next maint selected');
				continue;
			}

			$unix_next_maint = strtotime($row['next_maint'].' 12:00:00');
			$today = strtotime(date('Y-m-d 12:00:00'));

			$end_date = strtotime($row['end_date']);

			if ($unix_next_maint < $end_date) {
				//check if date is less than end date of asset record
				if (($unix_next_maint - $today) < (60*60*24*30)) {

					//date of next maint is within 30 days

					$chk = $this->asset_model->chk_service_record_close($row['asset_id']);

					if ($chk) {
						//service record created within 30 days
					} else {

						//check if service record already created, dont create if already got pending service record
						$chkp = $this->asset_model->chk_pending_maint_record($row['asset_id']);

						//not yet created
						if (!$chkp) {

							//create service record
							$data = array();
							$data['asset_id'] = $row['asset_id'];
							$data['vendor'] = '';
							$data['request_date'] = date('Y-m-d');
							$data['service_date_time'] = NULL;
							$data['maint_cost'] = 10;
							$data['status'] = 'P';
							$data['doc_ref'] = '';
							$data['is_planned'] = 1;
							$data['description'] = '';
							$data['created_by'] = 0;
							$maint_id = $this->asset_model->service_record_insert($data);

							//notify PIC
							$chk2 = $this->asset_model->has_notified_pic($row['asset_id']);

							if ($chk2) {

							} else {
								//notify PIC
								$pic = $this->asset_model->get_asset_pic($row['asset_id']);

								$email_list = array();
								$whatsapp_list = array();
								$telegram_list = array();
								foreach ($pic as $pic_row) {
									if (!empty($pic_row['email'])) {
										$email_list[] = $pic_row['email'];
									}
									if (!empty($pic_row['phone'])) {
										$whatsapp_list[] = $pic_row['phone'];
									}
									if (!empty($pic_row['telegram_id'])) {
										$telegram_list[] = $pic_row['telegram_id'];
									}
								}

								$send_array = array();
								$send_array['send_type'] = 'custom'; /* to_customer, to_backend_user or technician */
								$send_array['acc_id'] = 0; /* technically one should be enough, acc_id will point to profile, or if any product will also just join with profile to get customer details */
								$send_array['customer_no'] = 0;
								$send_array['user_id'] = 0;
								$send_array['controller'] = 'cron'; /* which controller calling this function */
								$send_array['doc_id'] = 0; /* if there is attachment or document related, then the id is here */
								$send_array['send_method'] = 'auto'; /* either auto or manual */
								$send_array['acc_name'] = 'Itelco User';
								//attachcment related
								$send_array['attachment'] = ''; /* attachcment in full path */
								//email
								$send_array['subject'] = 'Notification of Planned Maintenance - '.$row['asset_tag'].', '.$row['asset_name']; /* email subject */
								$send_array['body'] = 'Attached herewith is the details of billing report sent from itelco system.'; /* email body */
								$send_array['email_starter'] = 'Notification of Planned Maintenance'; /* email body header */
								$send_array['doc_type'] = '[Notification of Planned Maintenance]'; /* what kind of email is this */
								$send_array['email_list'] = $email_list;
								$send_array['whatsapp_list'] = $whatsapp_list;
								$send_array['telegram_list'] = $telegram_list;

								$send_result = $this->docs_log_model->do_send($send_array);

								$this->asset_model->insert_notification_record($row['asset_id']);
							}

						}

					}

				}
			}

		}

		//check see if there is any planned maintenance record created in this period

		//create planned maintenance record, with is_planned = 1

		echo "done";
	}

	function generate_customer_waiver()
	{
		$this->load->model('customer_model');
    	$query_str = "SELECT c.*, cs.transact_date AS latest_status_date, cs.status AS latest_status 
    	FROM customer c 
    	LEFT JOIN customer_bill_waiver b ON (b.customer_no = c.customer_no) 
		LEFT JOIN (
			SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
		) cs ON (cs.customer_no = c.customer_no) 
    	WHERE c.bill_waive_period != 0 AND b.waiver_start_date IS NULL ";
    	$query		= $this->db->query($query_str);

    	foreach ($query->result_array() as $row) {
    		if ($row['latest_status'] == 'A') {
				$waiver_arr = $this->customer_model->add_bill_waiver($row['customer_no'], $row['package'], $row['transact_date'], $row['bill_waive_period']);

				log_message('error', 'Customer No: '.$row['customer_no'].' waiver record '.$waiver_arr['start'].' - '.$waiver_arr['end'].' added.');
    		}
    	}
	}

	public function generate_customer_free_upgrade()
	{
		$this->load->model('customer_model');

		$query_str = "
			SELECT c.*, cs.transact_date AS latest_status_date, cs.status AS latest_status 
			FROM customer c 
			LEFT JOIN customer_free_upgrade f ON (f.customer_no = c.customer_no)
			LEFT JOIN (
				SELECT acs.* 
				FROM customer_status acs 
				JOIN (
					SELECT customer_no, MAX(status_id) AS max_status_id 
					FROM customer_status 
					GROUP BY customer_no
				) bcs ON (acs.status_id = bcs.max_status_id)
			) cs ON (cs.customer_no = c.customer_no)
			WHERE c.free_upgrade_period != 0 
			AND f.free_upgrade_start IS NULL
		";

		$query = $this->db->query($query_str);

		foreach ($query->result_array() as $row) {
			if ($row['latest_status'] == 'A') { 
				$upgrade_arr = $this->customer_model->add_free_upgrade(
					$row['customer_no'],
					$row['upgrade_package_id'],
					$row['transact_date'],
					$row['free_package_upgrade']
				);

				log_message('error', 'Customer No: ' . $row['customer_no'] . ' free upgrade record ' . $upgrade_arr['start'] .  ' - ' . $upgrade_arr['end'] . ' added.');
			}
		}
	}

	function cleanup() {
		$this->clean_radpostauth();
		$this->clean_logs();
		$this->clean_zabbix_logs();
		echo "Clean Up Done";
	}

	protected function clean_radpostauth() {
		$this->radius_db = $this->load->database('radius',true,false);
		
		if( $this->radius_db->initialize() ){
			$this->radius_db->query("CREATE TABLE delete_keys SELECT id FROM radpostauth WHERE 1=2");
			$this->radius_db->query("INSERT INTO delete_keys 
				SELECT id FROM (
					SELECT id FROM radpostauth 
					WHERE authdate <= (CURDATE() - INTERVAL 1 MONTH) ORDER BY authdate) A 
				LIMIT 10000");
			$this->radius_db->query("ALTER TABLE delete_keys ADD PRIMARY KEY (id)");
			$this->radius_db->query("DELETE B.* FROM delete_keys INNER JOIN radpostauth B USING (id)");
			$this->radius_db->query("DROP TABLE delete_keys");
			echo "Old radpostauth rows cleaned.\n";
		}
		//cleanup einvoice files that are already sent to lhdn?

    }

	protected function clean_logs() {
		$this->load->library('Log_maintenance');
        $result = $this->log_maintenance->delete_old_logs();
        echo "Log cleanup completed. Total: {$result['total']}, Deleted: {$result['deleted']}, Kept: {$result['kept']}\n";
    }

	protected function clean_zabbix_logs() {
		$this->load->helper('zabbix');
		$result = clean_zabbix_logs($this->zabbix_log_days_to_keep);

		if ($result['status']) {
			echo "Zabbix log cleanup completed. Total: {$result['total']}, Deleted: {$result['deleted']}, Kept: {$result['kept']}\n";
		} else {
			echo $result['msg'];
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////// ZABBIX START ////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////

	private function set_time_range() {
		$this->load->helper('zabbix');
		$current_time = time();
		$this->range_to = $current_time - ($current_time % 60) - 1;
		$this->range_from = $this->range_to - (20 * 60);
		zabbix_log(
			'Cron get_bandwidth_stat range: ' . date('Y-m-d H:i:s', $this->range_from) . ' - ' . date('Y-m-d H:i:s', $this->range_to),
			'error'
		);
	}

	function get_bandwidth_stat($debug = 0) {
		$this->load->helper('zabbix');

		zabbix_log('Cron get_bandwidth_stat start', 'error');

		$this->load->model(['customer_model', 'bandwidth_model']);

		$this->set_time_range();

		$this->customer_list = $this->customer_model->get_dia_customer_list();

		if (empty($this->customer_list)) {
			zabbix_log('Cron get_bandwidth_stat no customer found', 'error');
			return;
		}
		
		if ($debug === 'debug_mode') {
			echo '<div style="border: 2px solid #444; padding: 10px; margin: 10px 0; background-color: #f9f9f9; font-family: monospace;">';
			echo "<strong>Customer List:</strong><br><hr>";
			echo "<pre>";
			var_dump($this->customer_list);
			echo "</pre>";
			echo "</div>";
		}
		
		zabbix_log('Check Api status', 'error');
		callZabbixRequest('apiinfo.version', [], false); 

		zabbix_log('Extract host', 'error');
		$params = [
			'evaltype'     => 2,
			'tags'         => [],
			'selectTags'   => 'extend',
			'output'       => ['hostid', 'host', 'name', 'status', 'maintenance_status', 'active_available'],
			'preservekeys' => true,
		];

		$router_ips = array_values(array_unique(array_filter(array_map(function ($c) {
			return $c['router_ip'] ?? null;
		}, $this->customer_list))));

		foreach ($router_ips as $ip) {
			$params['tags'][] = [
				'tag'      => $this->zbxRouterIPTag,
				'value'    => $ip,
				'operator' => 1,
			];
		}

		$host_list = callZabbixRequest('host.get', $params, true);

		if (!empty($host_list)) {
			$this->zabbix_host_list = array_map(function ($host) {
				$host = (array)$host;
				$host['itelco_routerip'] = null;
				foreach ($host['tags'] ?? [] as $tag) {
					if (($tag->tag ?? '') === $this->zbxRouterIPTag) {
						$host['itelco_routerip'] = $tag->value;
						break;
					}
				}
				return (object)$host;
			}, get_object_vars($host_list));
		}

		if ($debug === 'debug_mode') {
			echo '<div style="border: 2px solid #444; padding: 10px; margin: 10px 0; background-color: #f9f9f9; font-family: monospace;">';
			echo "<strong>Zabbix Host List:</strong><br><hr>";
			echo "<pre>";
			var_dump($this->zabbix_host_list);
			echo "</pre>";
			echo "</div>";
		}

		foreach ($this->zabbix_host_list as $host) {
			$this->router_list[$host->itelco_routerip] = [
				'zbx_hostid'       => $host->hostid,
				'itelco_routerip'  => $host->itelco_routerip,
				'ints'             => [],
			];
		}

		foreach ($this->customer_list as $cust) {
			$ip     = $cust['router_ip'] ?? null;
			$int_id = $cust['interface_id'] ?? null;

			if (empty($ip) || empty($int_id) || !isset($this->router_list[$ip])) continue;

			if (!isset($this->router_list[$ip]['ints'][$int_id])) {
				$this->router_list[$ip]['ints'][$int_id] = [
					'cust'               => null,
					'bandwidth_stats'    => [],
					'zbx_ingress_itemid' => null,
					'zbx_egress_itemid'  => null,
				];
			}

			if ($this->router_list[$ip]['ints'][$int_id]['cust'] === null) {
				$customer_no = $cust['customer_no'] ?? null;
				$this->router_list[$ip]['ints'][$int_id]['cust'] = [
					'customer_no' => !empty($customer_no) && is_string($customer_no) ? $customer_no : null,
				];
			}
		}

		if ($debug === 'debug_mode') {
			echo '<div style="border: 2px solid #333; padding: 10px; margin: 15px 0; background-color: #f0f0f0; font-family: monospace;">';
			echo "<strong>Router List:</strong><br><hr>";
			echo "<pre>";
			var_dump($this->router_list);
			echo "</pre>";
			echo "</div>";
		}

		zabbix_log('Extract zabbix items', 'error');

		$interface_list = array_map(fn($v) => array_keys($v['ints']), $this->router_list);
		$extractRegex = '/Interface \[(.*)\]\[.*\]: Bits (received|sent)/';

		foreach ($this->router_list as $router_ip => $router) {
			$params = [
				'hostids' => $router['zbx_hostid'],
				'search'  => ['name_resolved' => 'Interface [*]: Bits *'],
				'searchWildcardsEnabled' => true,
				'preservekeys'           => true
			];

			if (!$result = zabbixRequest('item.get', $params, true)) continue;

			foreach ($result as $item) {
				if (
					preg_match($extractRegex, $item->name_resolved, $match) &&
					count($match) >= 3 &&
					in_array($match[1], $interface_list[$router_ip])
				) {
					$intf = $match[1];
					$mode = $match[2] === 'sent' ? 'egress' : ($match[2] === 'received' ? 'ingress' : false);

					if ($mode) {
						$this->router_list[$router_ip]['ints'][$intf]["zbx_{$mode}_itemid"] = $item->itemid;
					}
				}
			}
		}

		if ($debug === 'debug_mode') {
			echo '<div style="border: 2px solid #444; padding: 10px; margin: 10px 0; background-color: #f9f9f9; font-family: monospace;">';
			echo "<strong>Zabbix Items (router with interface):</strong><br><hr>";
			echo "<pre>";
			var_dump($this->router_list);
			echo "</pre>";
			echo "</div>";
		}

		zabbix_log('Get zabbix item history', 'error');

		$router_item_ids = [];
		foreach ($this->router_list as $router_ip => $router) {
			$router_item_ids[$router_ip] = [];
			foreach ($router['ints'] as $int) {
				foreach (['zbx_ingress_itemid', 'zbx_egress_itemid'] as $type) {
					if (!empty($int[$type])) $router_item_ids[$router_ip][] = $int[$type];
				}
			}
		}

		foreach ($router_item_ids as $router => $item_ids) {
			if (empty($item_ids)) continue;

			$item_batches = array_chunk($item_ids, $this->batch_size);

			foreach ($item_batches as $batch) {
				$params = [
					'itemids'   => $batch,
					'time_from' => $this->range_from,
					'sortfield' => ['itemid', 'clock']
				];

				$result = zabbixRequest('history.get', $params, true);

				if (is_array($result) && !empty($result)) {
					$this->bandwidth_stat = array_merge($this->bandwidth_stat, $result);
				}
			}
		}

		if ($debug === 'debug_mode') {
			echo '<div style="border: 2px solid #444; padding: 10px; margin: 10px 0; background-color: #f9f9f9; font-family: monospace;">';
			echo "<strong>Zabbix Bandwidth Stat:</strong><br><hr>";
			echo "<pre>";
			var_dump($this->bandwidth_stat);
			echo "</pre>";
			echo "</div>";
		}

		$range_gap = 60 * 5;
		$current_range_to = $this->range_from + $range_gap;
		$current_sum = [];
		$last_item_id = null;

		foreach ($this->bandwidth_stat as $key => $stat) {
			if (empty($stat->itemid)) continue;
			
			if ($last_item_id !== $stat->itemid) {
				$current_range_to = $this->range_from + $range_gap;
				$current_sum = [];
				$last_item_id = $stat->itemid;
			}

			if ($current_range_to > $this->range_to) continue;

			$detail = ['itemid'=>$stat->itemid,'routerip'=>null,'int'=>null,'type'=>null,'cust'=>null];

			foreach ($this->router_list as $router_ip => $router) {
				foreach ($router['ints'] as $int_name => $int_data) {
					if ($int_data['zbx_ingress_itemid'] == $stat->itemid) $detail['type']='ingress';
					elseif ($int_data['zbx_egress_itemid'] == $stat->itemid) $detail['type']='egress';
					else continue;

					$detail['routerip'] = $router_ip;
					$detail['int'] = $int_name;
					$detail['cust'] = $int_data['cust'];
					break 2;
				}
			}

			$stat_time = intval($stat->clock);

			if ($stat_time <= $current_range_to) {
				$current_sum[] = intval($stat->value);

				$next_bandwidth_stat = $this->bandwidth_stat[$key + 1] ?? false;
				$next_bandwidth_item_id = $next_bandwidth_stat ? $next_bandwidth_stat->itemid : false;
				if($next_bandwidth_item_id != $last_item_id){
					$customer_no = $detail['cust']['customer_no'] ?? null;
					$this->return[$customer_no][$current_range_to][$detail['type']] = round(array_sum($current_sum)/8000);
				}
			} else {
				$customer_no = $detail['cust']['customer_no'] ?? null;
				if ($customer_no !== null) {
					if (!isset($this->return[$customer_no][$current_range_to])) $this->return[$customer_no][$current_range_to]=['ingress'=>null,'egress'=>null];
					$this->return[$customer_no][$current_range_to][$detail['type']] = round(array_sum($current_sum)/8000);
				}
				$current_range_to += $range_gap;
				$current_sum[] = intval($stat->value);
			}
		}

		$post_data = [];
		foreach($this->return as $customer_no => $bandwidth_data){
			foreach($bandwidth_data as $timestamp => $kbps){
				$post_data[]=[
					'customer_no'=>$customer_no,
					'timestamp'=>date('Y-m-d H:i:s',$timestamp),
					'send_kb'=>$kbps['ingress']??0, // need to reverse cause this interface is facing to customer
					'received_kb'=>$kbps['egress']??0 // need to reverse cause this interface is facing to customer
				];
			}
		}

		zabbix_log(print_r($post_data,true), 'error');
		$this->bandwidth_model->insert_batch_data($post_data);

		zabbix_log('Done insert', 'error');
		echo "done";
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////// ZABBIX END //////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////

	public function update_radius_on_package_change() {
		log_message('error', 'CRON JOB START: update_radius_on_package_change()');
		$this->load->model(['customer_model', 'package_model']);

		$customers = $this->customer_model->get_package_change_customer();

		if(!empty($customers)) {
			foreach ($customers as $cust) {
				$history_result = $this->customer_model->get_package_history($cust['customer_no']);
				$package_history_record = $history_result['row'];

				if(!empty($package_history_record) && count($package_history_record) >= 2) {
					$new_inserted_record = $package_history_record[count($package_history_record) - 1] ?? [];
					$last_package_used = $package_history_record[count($package_history_record) - 2] ?? [];

					if((isset($new_inserted_record['package_no']) && $new_inserted_record['package_no'] == $cust['new_package_id'] && !empty($cust['new_package_effective_date']))) {
						if(date('Y-m-d', strtotime($cust['new_package_effective_date'])) <= date('Y-m-d')) {
							$control_by = 'cisco';

							$package_info = $this->package_model->get_package($cust['new_package_id']);

							if($control_by == 'radius') {
								$this->customer_model->radius_usergroup_upsert($cust['login_username'], $package_info['bandwidth']);

								//if DIA customer, activate the script
								// if ((($cust['category'] == 'd' || $cust['category'] == 'e')) && (($cust['interface_id'] != '')) && (!empty($cust['router_id']))) {
								// 	$this->customer_model->dia_jumpstart($cust['customer_no'], 'up');
								// }					
								$this->customer_model->update_package_change_customer($cust, $package_info, $last_package_used, $new_inserted_record);
							} else if ($control_by == 'cisco') {
								$this->customer_model->cisco_usergroup_upsert($cust['login_username'], $package_info['bandwidth'], $cust['login_username']);

								// if DIA customer, activate the script
								// if ((($cust['category'] == 'd' || $cust['category'] == 'e')) && (($cust['interface_id'] != '')) && (!empty($cust['router_id']))) {
								// 	$this->customer_model->dia_jumpstart($cust['customer_no'], 'up');
								// }
								$this->customer_model->update_package_change_customer($cust, $package_info, $last_package_used, $address_arr, $new_inserted_record);
							}
						} else {
							log_message('error', 'Customer ' . $cust['customer_no'] . ' package change date has not been reached');
						}
					} else {
						log_message('error', 'Customer ' . $cust['customer_no'] . ' info not complete. Something went wrong');
					}				
				} else {
					log_message('error', 'Customer ' . $cust['customer_no'] . ' has not performed the action yet');
				}
			}
			echo "Done";
		} else {
			log_message('error', 'No new customer updates on package change');
			echo "No new customer updates on package change";
		}
		log_message('error', 'CRON JOB END: update_radius_on_package_change()');
	}
	
	public function check_scheduled_whatsapp_message() {
		$this->load->model('message_scheduler_model');
		$this->load->model('docs_log_model');
		$this->load->model('whatbot_model');

		$pending_whatsapp_msg = $this->message_scheduler_model->get_pending_outgoing_msg('whatsapp', 3);

		foreach ($pending_whatsapp_msg as &$val) {

			//add a greeting
			$result = $this->docs_log_model->do_send(json_decode($val['remark'], true));
			if($result['status'] == 'succ') {
				$data = [
					'msg_sent_on' => date('Y-m-d H:i:s'),
					'msg_status' => 'S',
				];
			} else {
				$data = [
					'msg_attempt' => $val['msg_attempt'] + 1
				];
				if($data['msg_attempt'] >= 3) {
					$data['msg_status'] = 'F';
				}
			}
			$this->db->update('message_outgoing', $data, ['outgoing_id' => $val['outgoing_id']]);
			
		}
	}

	public function check_scheduled_telegram_message() {
		$this->load->model('message_scheduler_model');
		$this->load->model('docs_log_model');

		$pending_telegram_msg = $this->message_scheduler_model->get_pending_outgoing_msg('telegram');

		foreach ($pending_telegram_msg as $val) {
			$result = $this->docs_log_model->do_send(json_decode($val['remark'], true));
			if($result['status'] == 'succ') {
				$data = [
					'msg_sent_on' => date('Y-m-d H:i:s'),
					'msg_status' => 'S'
				];
			} else {
				$data = [
					'msg_attempt' => $val['msg_attempt'] + 1
				];
				if($data['msg_attempt'] >= 3) {
					$data['msg_status'] = 'F';
				}
			}
			$this->db->update('message_outgoing', $data, ['outgoing_id' => $val['outgoing_id']]);
		}
	}

	/**
	 * The worker to claims due deliveries and push them. Exit quietly when nothing is due.
	 * Run every minutes.
	 * 
	 * TESTING: - Add the customer_no to sys_config.whitelist to avoid pushing notification to every users.
	 */
	public function push_notification_dispatch()
	{
		$this->load->library('push_service');

		$started = microtime(TRUE);

		// Global Switch
		if (! $this->push_service->is_enabled()) {
			return;
		}

		$result = $this->push_service->run();

		$elapsed = round(microtime(TRUE) - $started, 2);

		// Nothing to do is the normal case. Say nothing, so the cron log
		// stays readable and anything that IS written deserves attention.
		if ($result['claimed'] === 0 && $result['released'] === 0) {
			return;
		}

		$line = '[push] cronjob push_notification_dispatch'
			. ' claimed=' . $result['claimed']
			. ' sent=' . $result['sent']
			. ' failed=' . $result['failed']
			. ' retrying=' . $result['retrying']
			. ' pruned=' . $result['pruned']
			. ' released=' . $result['released']
			. ' ' . $elapsed . 's';

		log_message('info', $line);
	}
}

?>
