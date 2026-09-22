<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
include_once( APPPATH . 'controllers/app_config.php' );
class Home extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->model('home_model');
		$this->load->model('common_model');	
		$this->load->model('report_model');		
		$this->load->model('payment_model');
    }
	
	public function index($id = null, $index = null)
	{
		$this->init();		
		$data = $this->home_model->get_dashboard();	
		
		$this->msg['msg'] 			= $this->session->flashdata('msg');
		$this->msg['error_msg'] 	= $this->session->flashdata('error_msg');
		$this->msg['warning_msg'] 	= $this->session->flashdata('warning_msg');		
		$data['page_title'] 		= 'Dashboard';	
		$data['msg'] 				= $this->msg;

		$data['this_month_payments'] = $this->home_model->this_month_payment();
		$data['last_month_bill_amount'] = $this->home_model->last_month_bill_amount(); // get balance no the amount
		$data['new_registration'] = $this->home_model->new_registration_in_week();
		$data['account_by_category'] = $this->home_model->count_account_by_category();
		$data['total_sales_and_payment'] = $this->home_model->total_sales_and_payments_by_month();
		$data['assigned_ticket'] = $this->home_model->assigned_ticket();
		$data['on_pending_manual_bills'] = $this->home_model->assigned_pending_manual_bill();
		$data['open_tickets'] = $this->home_model->open_ticket();

		$data['show_this_month_payment']      = !empty($_SESSION['acl']['payment']);
		$data['show_last_month_bill']         = !empty($_SESSION['acl']['bill']);
		$data['show_new_registrations']       = !empty($_SESSION['acl']['registration']);
		$data['show_customer_account']        = !empty($_SESSION['acl']['customer']);
		$data['show_trouble_tickets']         = !empty($_SESSION['acl']['trouble_ticket']);
		$data['show_open_tickets_panel']      = !empty($_SESSION['acl']['trouble_ticket']) && in_array('A', $_SESSION['acl']['trouble_ticket']['actions']);
		$data['show_bill_and_payment_stats']  = !empty($_SESSION['acl']['bill']) && !empty($_SESSION['acl']['payment']);
		$data['show_manual_bills']            = !empty($_SESSION['acl']['bill_manual']);

		//Sales data
		$data['show_last_month_sales']         = !empty($_SESSION['acl']['bill']);
		$data['show_last_month_accounts']         = !empty($_SESSION['acl']['customer']);

		//Sales data queries
		$today = date("d");
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];
		if( $today < $bill_cycle_day ){
			$last_month_start = date( "Y-m-01" , strtotime( "-2 months") );
			$last_month_end = date( "Y-m-t" , strtotime( "-2 months") );

			$next_month_refresh = date("Y-m-".str_pad($bill_cycle_day, 2, '0', STR_PAD_LEFT));
		} else {
			$last_month_ini 			= new DateTime("first day of last month");
			$last_month_start 	= $last_month_ini->format('Y-m-01');
			$last_month_end 	= $last_month_ini->format('Y-m-t');

			$next_month_ini 			= new DateTime("first day of next month");
			$next_month_refresh = $next_month_ini->format('Y-m-'.str_pad($bill_cycle_day, 2, '0', STR_PAD_LEFT));
		}
		$query_where = "";
		$query_where .= " AND (csa.transact_date <= '$last_month_end')";
		$sales_details = $this->report_model->sales_report($query_where);

		$last_month_sales = 0;
		foreach ($sales_details as $building_no => $building_data) {
			foreach ($building_data as $category_name => $customers) {
				foreach ($customers as $cust) {
					$row_total = $cust['monthly_charge'] + $cust['otc_charges'];
					$last_month_sales = $last_month_sales + $row_total;
				}
			}
		}

		$data['last_month_sales'] = $last_month_sales;
		$data['next_month_refresh'] = $next_month_refresh;

		$payment_details = $this->payment_model->detail_of_payment('all', 'all', 'all', 'all', $last_month_start, $last_month_end);
		$grand_total = 0;
		foreach ($payment_details as $key => $val) {
			foreach ($val as $key2 => $val2) {
				foreach ($val2 as $val3) {
					$grand_total += $val3['amount'];
				}
			}
		}
		$data['last_month_collection'] = $grand_total;

		$total_overdue = 0;
		$query_where = " AND IFNULL(cs.status, 'P') IN ('A','S') ";
		$overdue_details = $this->report_model->get_overdue_list($query_where);
		foreach ($overdue_details as $key => $val) {
			foreach ($val as $key2 => $val2) {
				$total_overdue += $val2['updated_balance'];
			}
		}
		$data['total_overdue'] = $total_overdue;

		$data['last_month_account_by_category'] = $this->home_model->count_account_by_category_by_month($last_month_start,$last_month_end);

		$data['last_month_start'] = $last_month_start;
		$data['last_month_end'] = $last_month_end;

		$this->vars = array_merge_recursive($this->vars, [
            'cssfiles' => [
                'css/home.css',
                'css/theme/bootstrap-grid.css',
            ],
            'jsfiles' => [
                'js/itelco/home.js?2',
                'js/itelco/chart.js',
            ],
			'jscripts' => array(
				'var new_registration = ' . json_encode($data['new_registration']) . ';',
				'var account_by_category = ' . json_encode($data['account_by_category']) . ';',
				'var total_sales_and_payment = ' . json_encode($data['total_sales_and_payment']) . ';',
				'var show_this_month_payment = ' . json_encode($data['show_this_month_payment']) . ';',
				'var show_last_month_bill = ' . json_encode($data['show_last_month_bill']) . ';',
				'var show_new_registrations = ' . json_encode($data['show_new_registrations']) . ';',
				'var show_customer_account = ' . json_encode($data['show_customer_account']) . ';',
				'var show_trouble_tickets = ' . json_encode($data['show_trouble_tickets']) . ';',
				'var show_open_tickets_panel = ' . json_encode($data['show_open_tickets_panel']) . ';',
				'var show_bill_and_payment_stats = ' . json_encode($data['show_bill_and_payment_stats']) . ';',
				'var show_manual_bills = ' . json_encode($data['show_manual_bills']) . ';',
				'var show_last_month_accounts = ' . json_encode($data['show_last_month_accounts']) . ';',
				'var last_month_account_by_category = ' . json_encode($data['last_month_account_by_category']) . ';',
			),
        ]);
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('home/index',$data);
		$this->load->view('templates/footer');
	}

	public function view_more()
	{
		$session_name = $this->input->post('session_name');
		$filter = $this->input->post('filter');

		if($session_name == '' && $filter == ''){
			echo json_encode([
				'status' => 'success',
				'message' => 'No filter applied.'
			]);
		}else{
			set_session_filter($session_name, $filter);

			echo json_encode([
				'status' => 'success',
				'message' => 'Filter applied successfully.'
			]);
		}
		exit;
	}

	public function ajax_get_open_ticket() {
		$open_tickets = $this->home_model->open_ticket();
		echo json_encode($open_tickets);
	}
	
	//Set all the confign value into session
	function init()
	{
		$obj_app_config = new app_config();
		$obj_app_config->load_config();
	}
}

