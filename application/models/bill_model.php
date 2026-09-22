<?php
class Bill_model extends MY_Model{

	protected $_table = 'bill';
	protected $_primary_key = 'idx';

	private $e_key = "1nf0n4l";

	protected $_global_session = [];

	public function __construct()
	{
		parent::__construct();

		$this->load->model('app_config_model');
		$this->_global_session['config'] 		= $this->app_config_model->load_sys_config();
		$this->_global_session['acc_status'] 	= $this->app_config_model->load_sys_account_status();		
		$this->_global_session['bill_type'] 		= $this->app_config_model->load_sys_bill_type();
		$this->_global_session['cust_category'] 	= $this->app_config_model->load_sys_customer_category();
		$this->_global_session['marital_status'] = $this->app_config_model->load_sys_marital_status();
		$this->_global_session['payment_source'] = $this->app_config_model->load_sys_payment_source();
		$this->_global_session['state'] 			= $this->app_config_model->load_sys_state();		
		$this->_global_session['tax_type']		= $this->app_config_model->load_sys_tax_type();
		$this->_global_session['building'] 		= $this->app_config_model->load_building();
	}

	function bill_generate($var='',$msg='')
	{
		ini_set('max_execution_time', 0);

		//get attachment path
		//what is this for???
		/*$subject		= 'penangFon Bill Statement';
		$email_from 	= 'penangFon';
		$message		= '';
		$attachments	= '';
		$email_to		= '';
		$config_query	= "`category` = 'email'";

		$this->load->model('common_model');
		$table_config	= $this->common_model->get_table('sys_config','*', $config_query);

		$email_settings = array(); 
		foreach ($table_config as $tc_key => $t_arr) $email_settings[$t_arr['key']] = $t_arr['val'];
		$fullpath = $this->config->item('proj_path').$email_settings['set_attachment_at'].'/'.$attachments;*/
		
		$this_month_bill_count = $this->bill_model->this_month_bill_count();

		//testing_purpose comment out, using bill format to check for whether or not this month bill already run d
		if ($this_month_bill_count > 0) {
			exit("Same bill date existed!");
		}
		
		$cust_bill = $this->bill_model->bill_preparation();

		//$get_max_bill_no 	= $this->bill_model->get_max_bill_no();
		//ALL bills for this month start with #1, output should be YYMM000001 , with total length of 10
		$get_max_bill_no = sprintf( "%06d", 0 );
		$get_max_bill_no = date("ym") . $get_max_bill_no;
		$new_bill_no = $get_max_bill_no + 1;
		
		$query_insert_header = '';
		$query_insert_detail = '';
		
		$batch_update = array();
		$payment_no_str = "";
		$adjustment_no_str = "";

		$query_insert_call = '';

		//bill_extra_header
		$query_insert_extra = '';
		
		$date_first_day	= date('Y-m-1');
		$date_last_day	= date('Y-m-t');
		$this->load->model('customer_model');

		/*
		customer no
		type of update - adjustment, bill etc
		*/
		$batch_log_updates = array();
		
		foreach ($cust_bill as $customer_no => $row) {
			
			$cust_info = $this->customer_model->get_customer( $customer_no );
			if( $cust_info['customer_no'] != '' ){
				//~ //testing_purpose uncomment below
				//~ $date_first_day	= '2016-11-1';
				//~ $date_last_day	= '2016-11-30';

				//for bill header, store details here, or split out to extra table

				$query_insert_header .= "(" .
									"'" . $new_bill_no . "', " .
									"'" . $customer_no . "', " .
									"'" . $date_first_day . "', " .
									"'" . $date_last_day . "', " .
									"'" . $row['previous_balance'] . "', " .
									"'" . $row['payment_received'] . "', " .
									"'" . $row['charges'] . "', " .
									"'" . $row['tax_charges'] . "', " .
									"'" . $row['amount'] . "', " .
									"'" . $row['balance'] . "' " .
									"),";

				//bill extra header - just check 1 field if existing or not
				if (isset($row['extra_detail']['package'])) {
					$query_insert_extra .= "(" .
										"'" . $new_bill_no . "', " .
										"'" . $date_first_day . "', " .
										"'" . $customer_no . "', " .
										"'" . ($row['extra_detail']['bill_cycle_month'] ?? 1) . "', " .
										"'" . ($row['extra_detail']['package'] ?? 0) . "', " .
										"'" . ($row['extra_detail']['package_name'] ?? '') . "', " .
										"'" . ($row['extra_detail']['payment_term'] ?? '30') . "', " .
										"'" . ($row['extra_detail']['bill_unit_no'] ?? '') . "', " .
										"'" . ($row['extra_detail']['bill_addr_1'] ?? '') . "', " .
										"'" . ($row['extra_detail']['bill_addr_2'] ?? '') . "', " .
										"'" . ($row['extra_detail']['bill_addr_3'] ?? '') . "', " .
										"'" . ($row['extra_detail']['bill_postcode'] ?? '') . "', " .
										"'" . ($row['extra_detail']['bill_city'] ?? '') . "', " .
										"'" . ($row['extra_detail']['bill_state'] ?? '') . "', " .
										"'" . ($row['extra_detail']['email'] ?? '') . "', " .
										"'" . ($row['extra_detail']['phone'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_unit_no'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_addr1'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_addr2'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_addr3'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_postcode'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_city'] ?? '') . "', " .
										"'" . ($row['extra_detail']['inst_state'] ?? '') . "', " .
										"now(), ".
										"'0' ".
										"),";
				}
				
				//prepare for update payments
				foreach( $row['payment_num'] AS $payment_no )
				{
					$batch_update['payments'][] = array(
									  'payment_no' => $payment_no ,
									  'bill_no' => $new_bill_no ,
									  'is_lock' => '1' ,
									  'processed' => '1',
									  'process_date' => date('Y-m-d'),
								  );
					$payment_no_str .= $payment_no . "," ;
				}

				//for bill_details, can save any data here in charge_detail array

				if (!empty($row['charge_detail']) && is_array($row['charge_detail'])) {
					foreach ($row['charge_detail'] as $row_charge) {
						$query_insert_detail .= "(" .
											"'" . $new_bill_no . "', " .
											"'" . $row_charge['tranx_date'] . "', " .
											"'" . $row_charge['bill_type'] . "', " .
											"'" . $row_charge['tax_code'] . "', " .
											"'" . $row_charge['tax_percent'] . "', " .
											"'" . $row_charge['amount'] . "', " .
											"'" . $row_charge['tax_amount'] . "', " .
											"'" . $this->db->escape_str($row_charge['remark']) . "' " .
											")," ;
						//prepare for update adjustments
						if( $row_charge['adj_no'] != '' )
						{
							$batch_update['adjustments'][] = array(
														'adj_no' => $row_charge['adj_no'] ,
														'bill_no' => $new_bill_no ,
														'is_lock' => '1' 
														);
							$adjustment_no_str .= $row_charge['adj_no'] . ",";
						}
					}
				}

				//phone charges
				foreach( $row['call_detail'] AS $call_row )
				{
					$query_insert_call .= "(" .
									"'" . $customer_no . "', " .
									"'" . $call_row['caller_id'] . "', " .
									"'" . $call_row['start_time'] . "', " .
									"'" . $call_row['answer_time'] . "', " .
									"'" . $call_row['end_time'] . "', " .
									"'" . $call_row['called'] . "', " .
									"'" . $call_row['call_time'] . "', " .
									"'" . $call_row['talk_time'] . "', " .
									"'" . $call_row['tranx_date'] . "', " .
									"'" . $call_row['amount'] . "', " .
									"'" . $call_row['tax_amount'] . "', " .
									"'" . $call_row['tax_code'] . "', " .
									"'" . $call_row['tax_percent'] . "', " .
									"'" . $call_row['total'] . "', " .
									"'" . $this->db->escape_str($call_row['remark']) . "', " .
									"'" . $new_bill_no . "', 'admin', now()" . 
									")," ;
				}

				$new_bill_no ++;
			}
		}
		
		//testing_purpose comment out
		//If no record insert, immediate exit;
		if ( empty($query_insert_header) ) {
		//if ( empty($query_insert_header) || empty($query_insert_detail) ) {
			return 0;
		}

		if( $query_insert_header != '' )
			$this->bill_model->generate_bill_header($query_insert_header);
		
		if( $query_insert_detail != '' )
			$this->bill_model->generate_bill_detail($query_insert_detail);

		if( $query_insert_call != '' )
			$this->bill_model->generate_bill_call($query_insert_call);

		if ( $query_insert_extra != '' )
			$this->bill_model->generate_bill_extra($query_insert_extra);
		
		//update payment processed , is_lock , bill_no by batch
		if( !empty($batch_update['payments']) ){
				
			$this->db->update_batch('payment', $batch_update['payments'], 'payment_no');
			$this->db->trans_complete();
			if( $this->db->trans_status() === TRUE )
			{
				$this->load->model('action_log_model');			
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str( " Payment # : " . rtrim( $payment_no_str , "," ) );	
				$method 		= $this->router->method; 				
				$action_desc	= 'Monthly Bills Generation: Payments';	
				$action_category = 'update';
				$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
			}
			
		}

		//update adjustment is_lock , bill_no by batch
		if( !empty( $batch_update['adjustments'] ) ){

			$this->db->update_batch('bill_adjustment', $batch_update['adjustments'], 'adj_no');
			$this->db->trans_complete();
			if( $this->db->trans_status() === TRUE )
			{
				$this->load->model('action_log_model');			
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str( " Adjustment # : " . rtrim( $adjustment_no_str , "," ) );	
				$method 		= $this->router->method; 				
				$action_desc	= 'Monthly Bills Generation: Adjustments';	
				$action_category = 'update';
				$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
			}
		
		}
	
		$this->bill_model->update_next_bill_date();
		
		/*
		if( !empty( $cust_bill['payment_detail'] ) ){
			$this->bill_model->update_payment_processed( $cust_bill['payment_detail'] );
			$this->bill_model->lock_payment( $cust_bill['payment_detail'] );
		}

		if( !empty( $cust_bill['adjustment_no'] ) ){
			$this->bill_model->lock_adjustment( $cust_bill['adjustment_no'] );
		}
		*/
		//~ $this->bill_model->update_payment_processed();
		//~ $this->bill_model->lock_adjustment_and_payment();

		return $cust_bill;
	}

	function bill_statement($statement_by, $statement_key, $gen_pdf = 0 , $pdf_name = '', $filter_date='')
	{
		
		$this->load->model('common_model');
        $this->load->helper('custom_helper');
		$seg 			= $this->common_model->get_segment();
		$states 		= $this->common_model->get_state_list();
		
		$bill_prefix	= isset( $_SESSION['config']['inv_no_prefix'] ) ? $_SESSION['config']['inv_no_prefix'] : '' ;
		
		$state_list = array();
		foreach( $states AS $state ){
			$state_list[$state['state_code']] = $state['name'];
		}
		
		$controller		= $seg[0];
		$method 		= $seg[1];
		//param 1 = type , such as latest_bill_by or bill 
		$param_1		= (empty($statement_by)?$seg[2]:$statement_by);
		//param 2 = bill no
		$param_2		= (empty($statement_key)?$seg[3]:$statement_key);
		//unused?
		$param_3		= (empty($seg[4]))?'':$seg[4];
		$input			= array();
		
		if( empty($param_2) ){
			$this->session->set_flashdata("warning_msg", 'Bill Statement not found!');
			redirect('bill');
		}
		
		//latest_bill_by uses this if statement, for the rest is the below
		if((!empty($_POST['btPrintFiltered'])) ||  ($param_1 == 'latest_bill_by'))
		{
			if(!is_array($statement_key)){
				$make_array[0] = $statement_key;
				$statement_key = $make_array;
			}
			$input_data_arr = array();

			//~ $statement_arr  = array();
			$input['data'] = $this->bill_model->get_statement('latest_bill_by',$statement_key,$filter_date);

			/*echo "<pre>";
			print_r($input['data']);
			echo "</pre>"; 
			exit;*/

			foreach ($input['data'] as $key => $val)
			{
				foreach ($val['bill_detail'] as $key2 => $val2){
					$input['data'][$key]['bill_detail'][$key2]['bill_type_name'] = get_bill_type_name_by_id($input['data'][$key]['bill_detail'][$key2]['bill_type']);
					$input['data'][$key]['bill_detail'][$key2]['bill_type_name_with_remark'] = get_bill_type_name_by_id($input['data'][$key]['bill_detail'][$key2]['bill_type']) . 
						((!empty($input['data'][$key]['bill_detail'][$key2]['item_remark'])) ? ' ('.$input['data'][$key]['bill_detail'][$key2]['item_remark'].')' : '');
				}
			}
						
		}
		else
		{
			
			if ( empty($param_1) || empty($param_2) || (($param_1 != 'bill' ) && ($param_1 != 'customer')  && ($param_1 != 'latest_bill_by')) )  {
				$this->session->set_flashdata("warning_msg", 'Bill Statement not found!');
				redirect('bill');
			}

			$bill_statement = $this->bill_model->get_statement($param_1,$param_2);

			/*
			echo "<pre>";
			print_r($bill_statement);
			echo "</pre>"; 
			exit;
			*/
			
			$statement_arr = array();

			foreach ($bill_statement as $bs_key => $bs_arr){
				$statement_arr['customer'][] = $bill_statement[$bs_key]['customer'];
				$statement_arr['bill'][] = $bill_statement[$bs_key]['bill'];

				$statement_arr['bill_detail'] = array();
				if(!empty($bill_statement[$bs_key]['bill_detail']))
				{
					foreach ($bill_statement[$bs_key]['bill_detail'] as $bd_key => $bd_arr){
						$bill_statement[$bs_key]['bill_detail'][$bd_key]['bill_type_name'] = get_bill_type_name_by_id($bill_statement[$bs_key]['bill_detail'][$bd_key]['bill_type']);
						$bill_statement[$bs_key]['bill_detail'][$bd_key]['bill_type_name_with_remark'] = get_bill_type_name_by_id($bill_statement[$bs_key]['bill_detail'][$bd_key]['bill_type']) . 
						((!empty($bill_statement[$bs_key]['bill_detail'][$bd_key]['item_remark'])) ? '('.$bill_statement[$bs_key]['bill_detail'][$bd_key]['item_remark'].')' : '');
					}
					$statement_arr['bill_detail'] = $bill_statement[$bs_key]['bill_detail'];
				}

				$statement_arr['bill_call'] = $bill_statement[$bs_key]['bill_call'];

				$input['data'][] = $statement_arr;
				$statement_arr = array();
			}
		}
		
        $gst_amount	= $this->common_model->get_default_tax_amount();
			
		$_SESSION['config']['date_format_printing'] = isset( $_SESSION['config']['date_format_printing'] ) ? $_SESSION['config']['date_format_printing'] : 'Y-m-d';

		/*
		echo "<pre>";
		print_r($input['data']);
		echo "</pre>";
		exit;
		*/

		foreach($input['data'] as $i_data_key => $i_data_val)
		{

			//Get gst amount
			$input['data'][$i_data_key]['customer'][0]['default_tax'] =
			$gst_amount[0]['percent'] ?? 0;

			$check_cat = $input['data'][$i_data_key]['customer'][0]['category'];
			if($check_cat != 'w')
			{
				$input['data'][$i_data_key]['customer'][0]['pay_on_or_before'] =
				date_toggle($input['data'][$i_data_key]['bill'][0]['bill_due_date'],$_SESSION['config']['date_format_printing']);
			}
			else
			{
				$input['data'][$i_data_key]['customer'][0]['pay_on_or_before'] =
				date_toggle($input['data'][$i_data_key]['bill'][0]['bill_due_date'],$_SESSION['config']['date_format_printing']);
			}

			$input['data'][$i_data_key]['customer'][0]['next_bill_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['next_bill_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['date_of_birth'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['date_of_birth'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['signup_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['signup_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['activated_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['activated_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['suspended_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['suspended_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['terminated_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['terminated_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['created_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['created_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['customer'][0]['modified_date'] =
			date_toggle($input['data'][$i_data_key]['customer'][0]['modified_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['bill'][0]['bill_date'] =
			date_toggle($input['data'][$i_data_key]['bill'][0]['bill_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['bill'][0]['bill_due_date'] =
			date_toggle($input['data'][$i_data_key]['bill'][0]['bill_due_date'],$_SESSION['config']['date_format_printing']);

			$input['data'][$i_data_key]['bill'][0]['last_printed_date'] =
			date_toggle($input['data'][$i_data_key]['bill'][0]['last_printed_date'],$_SESSION['config']['date_format_printing']);
			
			$input['data'][$i_data_key]['bill'][0]['bill_no'] = $bill_prefix . 
			add_zero($input['data'][$i_data_key]['bill'][0]['bill_no']);

			$input['data'][$i_data_key]['customer'][0]['display_name'] 		= $input['data'][$i_data_key]['customer'][0]['name'];
			$input['data'][$i_data_key]['customer'][0]['display_unit_no'] 	= ((!empty($input['data'][$i_data_key]['customer'][0]['building_name'])) ? $input['data'][$i_data_key]['customer'][0]['building_name'].', '.$input['data'][$i_data_key]['customer'][0]['inst_unit_no'] : '');
			$input['data'][$i_data_key]['customer'][0]['display_addr1'] 	= $input['data'][$i_data_key]['customer'][0]['inst_addr1'];
			$input['data'][$i_data_key]['customer'][0]['display_addr2'] 	= $input['data'][$i_data_key]['customer'][0]['inst_addr2'];
			$input['data'][$i_data_key]['customer'][0]['display_addr3'] 	= $input['data'][$i_data_key]['customer'][0]['inst_addr3'];
			$input['data'][$i_data_key]['customer'][0]['display_city'] 		= $input['data'][$i_data_key]['customer'][0]['inst_city'];
			$input['data'][$i_data_key]['customer'][0]['display_postcode'] 	= $input['data'][$i_data_key]['customer'][0]['inst_postcode'];
			// $input['data'][$i_data_key]['customer'][0]['display_state'] 	= strtoupper( $input['data'][$i_data_key]['customer'][0]['inst_state'] != '-' ? $state_list[$input['data'][$i_data_key]['customer'][0]['inst_state']] :'');
			$input['data'][$i_data_key]['customer'][0]['display_state'] = strtoupper(($input['data'][$i_data_key]['customer'][0]['inst_state'] ?? '-') !== '-' && isset($state_list[$input['data'][$i_data_key]['customer'][0]['inst_state']]) ? $state_list[$input['data'][$i_data_key]['customer'][0]['inst_state']] : '');


			//overide display_sets if bill_addr1 is not empty
			if(!empty($input['data'][$i_data_key]['customer'][0]['bill_addr1']))
			{
				// in future will take data from extra header table
				$input['data'][$i_data_key]['customer'][0]['display_name'] 		= $input['data'][$i_data_key]['customer'][0]['name'];
				$input['data'][$i_data_key]['customer'][0]['display_unit_no'] 	= ((!empty($input['data'][$i_data_key]['customer'][0]['building_name'])) ? $input['data'][$i_data_key]['customer'][0]['building_name'].', '.$input['data'][$i_data_key]['customer'][0]['inst_unit_no'] : '');
				$input['data'][$i_data_key]['customer'][0]['display_addr1'] 	= $input['data'][$i_data_key]['customer'][0]['bill_addr1'];
				$input['data'][$i_data_key]['customer'][0]['display_addr2'] 	= $input['data'][$i_data_key]['customer'][0]['bill_addr2'];
				$input['data'][$i_data_key]['customer'][0]['display_addr3'] 	= $input['data'][$i_data_key]['customer'][0]['bill_addr3'];
				$input['data'][$i_data_key]['customer'][0]['display_city'] 		= $input['data'][$i_data_key]['customer'][0]['bill_city'];
				$input['data'][$i_data_key]['customer'][0]['display_postcode'] 	= $input['data'][$i_data_key]['customer'][0]['bill_postcode'];
				$input['data'][$i_data_key]['customer'][0]['display_state'] 	= strtoupper( $input['data'][$i_data_key]['customer'][0]['bill_state'] != '-' ? $input['data'][$i_data_key]['customer'][0]['bill_state'] : '' ) ;

				$input['data'][$i_data_key]['customer'][0]['display_state'] = strtoupper(($input['data'][$i_data_key]['customer'][0]['bill_state'] ?? '-') !== '-' && isset($state_list[$input['data'][$i_data_key]['customer'][0]['bill_state']]) ? $state_list[$input['data'][$i_data_key]['customer'][0]['bill_state']] : '');
			}

			foreach ($input['data'][$i_data_key]['bill_detail'] as $b_d_key => $b_d_val)
			{
				$input['data'][$i_data_key]['bill_detail'][$b_d_key]['tranx_date'] =
				date_toggle($input['data'][$i_data_key]['bill_detail'][$b_d_key]['tranx_date'],$_SESSION['config']['date_format_printing']);
			}

		}
		$gst_reg_no	 =  $this->common_model->get_gst_reg_no();

		$return_val = array();
		$return_val['input'] 		= $input;
		$return_val['gst_reg_no'] 	= $gst_reg_no;
		$return_val['gen_pdf'] 		= $gen_pdf;
		
		return $return_val;

		//~ $this->load->helper('form');
		//~ $header_data 					= $this->vars;
		//~ $header_data['title']			= 'print preview';
		//~ $header_data['description']		= 'to print bill statements';
		//~ $content_data['data']			= (empty($input['data']))?'':$input['data'];
		//~ $content_data['gst_reg_no']		= (empty($gst_reg_no))?'':$gst_reg_no;
		//~ $data['msg']					= $this->msg;

		//~ _debug_array($gst_amount); exit;
	}

	function get_bill_list_by_cust_no($customer_no, $limit='')
	{
		if( $limit != '' )
			$limit = " LIMIT $limit ";
		
		$query = $this->db->query(" SELECT 	b.*, 
											be.einvoice_status,
											be.created_on AS einvoice_submit_date
									FROM bill b
									LEFT JOIN bill_einvoice be 
										ON be.bill_no = b.bill_no
									WHERE b.customer_no = '".$this->db->escape_str($customer_no)."'
									ORDER BY b.bill_no DESC $limit " );
		
		$return_val = $query->result_array();

		return $return_val;
	}

	function get_bill_list($txt_search, $page_item_no, $query_where = '')
	{
		$return_val['total_row'] = 0;
		$return_val['row'] = array();

		$txt_search = $this->db->escape_str($txt_search);

		$query_str = "SELECT count(*) as total_row 
						FROM customer c 
						INNER JOIN bill b ON c.customer_no = b.customer_no 
						INNER JOIN ( 
							SELECT MAX(bill_no) as bill_no 
							FROM bill 
							WHERE 1=1 AND is_void = 0 
							GROUP BY customer_no 
						) a ON a.bill_no = b.bill_no 

						LEFT JOIN profile p ON (p.acc_id = c.profile_id)

						LEFT JOIN (
							SELECT s1.customer_no, s1.status
							FROM customer_status s1
							WHERE s1.status_id IN (
								SELECT MAX(status_id) 
								FROM customer_status 
								GROUP BY customer_no
							)
						) cls ON (cls.customer_no = c.customer_no)

						WHERE (
							c.customer_no LIKE '%$txt_search%' 
							OR c.name LIKE '%$txt_search%' 
							OR c.nric LIKE '%$txt_search%'
							OR p.pic_email_1 LIKE '%$txt_search%'
							OR p.pic_email_2 LIKE '%$txt_search%'
							OR p.acc_mobileno LIKE '%$txt_search%'
						) " . $query_where;

		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$query = $this->db->query("
			SELECT 
				c.customer_no, 
				c.name, 
				IF(csa.transact_date IS NULL, '', csa.transact_date) as activated_date, 
				b.bill_date as last_bill_date, 
				b.balance,
				cls.status as latest_status,
				p.pic_email_1,
				p.pic_email_2,
				p.acc_mobileno

			FROM customer c 

			INNER JOIN bill b 
				ON c.customer_no = b.customer_no 

			INNER JOIN ( 
				SELECT MAX(bill_no) as bill_no 
				FROM bill 
				WHERE is_void = 0  
				GROUP BY customer_no 
			) a ON a.bill_no = b.bill_no 

			LEFT JOIN profile p 
				ON (p.acc_id = c.profile_id)

			LEFT JOIN (
				SELECT acs.* 
				FROM customer_status acs 
				JOIN (
					SELECT customer_no, MIN(status_id) AS min_status_id 
					FROM customer_status 
					WHERE `status` = 'A' 
					GROUP BY customer_no
				) bcs ON (acs.status_id = bcs.min_status_id) 
			) csa ON (csa.customer_no = c.customer_no)

			LEFT JOIN (
				SELECT s1.customer_no, s1.status
				FROM customer_status s1
				WHERE s1.status_id IN (
					SELECT MAX(status_id) 
					FROM customer_status 
					GROUP BY customer_no
				)
			) cls ON (cls.customer_no = c.customer_no)

			WHERE (
				c.customer_no LIKE '%$txt_search%' 
				OR c.name LIKE '%$txt_search%' 
				OR c.nric LIKE '%$txt_search%'
				OR p.pic_email_1 LIKE '%$txt_search%'
				OR p.pic_email_2 LIKE '%$txt_search%'
				OR p.acc_mobileno LIKE '%$txt_search%'
			) 
			" . $query_where . " 

			ORDER BY c.customer_no 

			LIMIT $page_item_no, " . $_SESSION['config']['max_page_item']
		);
									
		if ($query->num_rows() > 0) {
			$return_val['row'] = $query->result_array();
		}

		return $return_val;
	}

	function get_bill($customer_no='')
	{
		$query = $this->db->query("SELECT 
										c.customer_no, 
										c.category, 
										c.name AS customer_name, 
										c.login_username, 
										csa.transact_date AS activated_date, 
										c.package_name, 
										c.next_bill_date, 
										cs.status AS latest_status, 
										b.last_billed_date, 
										b.bill_due_date 
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
									LEFT JOIN (
										SELECT acs.* 
										FROM customer_status acs 
										JOIN (
											SELECT customer_no, MAX(status_id) AS max_status_id 
											FROM customer_status 
											WHERE `status` = 'A' 
											GROUP BY customer_no
										) bcs ON acs.status_id = bcs.max_status_id
									) csa ON csa.customer_no = c.customer_no 
									LEFT JOIN (
										SELECT customer_no, MAX(bill_date) AS last_billed_date, MAX(bill_due_date) AS bill_due_date 
										FROM bill 
										WHERE is_void = 0 
										GROUP BY customer_no
									) b ON b.customer_no = c.customer_no 
									WHERE c.customer_no = '".$this->db->escape_str($customer_no)."' 
									LIMIT 1
								");

		if ($query->num_rows() > 0)	{
			$return_val = $query->row_array();
		}
		else {
			$return_val['customer_no'] = '';
			$return_val['customer_name'] = '';
			$return_val['category'] = '';
			$return_val['package_name'] = '';
			$return_val['login_username'] = '';
			$return_val['status_name'] = '';
			$return_val['last_billed_date'] = '';
			$return_val['bill_due_date'] = '';
			$return_val['next_bill_date'] = '';
			$return_val['next_billing_summary'] = "";
			$return_val['previous_balance'] = '';
			$return_val['payment_received'] = '';
			$return_val['bill_detail_item'] = array();
			$return_val['tax_charges'] = '';
			$return_val['balance'] = '';
		}

		$bill_summary = $this->bill_preparation($customer_no);
		$return_val['previous_balance'] = $bill_summary[$customer_no]['previous_balance'];
		$return_val['payment_received'] = $bill_summary[$customer_no]['payment_received'];
		$return_val['charge_detail'] = $bill_summary[$customer_no]['charge_detail'] ?? []; // add ?? [] for handle terminated customer bill
		$return_val['tax_charges'] = $bill_summary[$customer_no]['tax_charges'];
		$return_val['balance'] = $bill_summary[$customer_no]['balance'];

		return $return_val;
	}

	function get_manual_bill_list( $txt_search,$page_item_no,$query_where = '' ){
		
		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		$txt_search = $this->db->escape_str($txt_search);

		$query_str= "SELECT count(*) as total_row 
						FROM customer c 
						INNER JOIN bill_draft b ON c.customer_no = b.customer_no 
						LEFT JOIN bill b2 ON b2.bill_no = b.bill_no
						LEFT JOIN bill_status bs ON bs.id = (
							SELECT MAX(bs2.id)
							FROM bill_status bs2
							WHERE bs2.bill_draft_no = b.bill_draft_no
						)
						WHERE (c.customer_no LIKE '%$txt_search%' 
								OR c.name LIKE '%$txt_search%' 
								OR c.nric LIKE '%$txt_search%') 
					    AND b.is_void = 0 " . $query_where ;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;
		
		$bill = "SELECT 
					c.customer_no, 
					c.name, 
					b.bill_draft_no, 
					b.bill_no,
					IF(c.activated_date = 0, '', c.activated_date) AS activated_date, 
					b.bill_date AS last_bill_date, 
					b.balance, 
					b2.is_void, 
					bs.status,
					(
					SELECT 
						EXISTS (
						SELECT 1 
						FROM docs_approver da 
						WHERE da.doc_ref = b.bill_draft_no 
							AND da.doc_type = 'mb' 
							AND da.user_id = ?
							AND da.level = CASE 
											WHEN bs.status = 'B' THEN 2
											WHEN bs.status = 'A' THEN 1
											ELSE 0
										END
						)
					) AS is_approver
				FROM customer c 
				INNER JOIN bill_draft b ON c.customer_no = b.customer_no 
				LEFT JOIN bill b2 ON b2.bill_no = b.bill_no
				LEFT JOIN bill_status bs ON bs.id = (
					SELECT MAX(bs2.id)
					FROM bill_status bs2
					WHERE bs2.bill_draft_no = b.bill_draft_no
				)
				WHERE (
					c.customer_no LIKE '%$txt_search%' 
					OR c.name LIKE '%$txt_search%' 
					OR c.nric LIKE '%$txt_search%'
				) 
				" . $query_where . " 
				ORDER BY 
					CASE 
						WHEN bs.status = 'C' AND b2.is_void = '1' THEN 0
						WHEN bs.status = 'C' AND (b2.is_void = '0' OR b2.is_void IS NULL) THEN 1
						WHEN bs.status = 'B' THEN 2
						WHEN bs.status = 'A' THEN 3
						WHEN bs.status = 'D' THEN 4
						ELSE 5
					END DESC,
					b.bill_draft_no DESC, 
					c.customer_no 
				LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];
		$query = $this->db->query($bill, [$this->session->userdata("user")['idx'] ?? 0]);
		if ($query->num_rows() > 0){
			$return_val['row'] = $query->result_array();
		}
		return $return_val;
		
	}

	function get_bill_manual_detail($bill_draft_no) {
		$return_val = [
			'bill_info' => [],
			'bill_dtl' => [],
			'doc_lvl_1_approver' => [],
			'doc_lvl_2_approver' => []
		];
	
		// Main
		$bill_sql = "
			SELECT 
				b.idx, b.bill_draft_no, b.bill_no, b.bill_date, b.bill_type, b.related_bill_no, b.payment_received, b.related_payment_no, b.reason, b.created_by,
				c.customer_no, c.profile_id, c.name AS customer_name, c.package_name, c.monthly_charge, 
				b.previous_balance, bs.status AS bill_status, bs.date AS bill_status_date, cs.status AS customer_status, 
				be.einvoice_status as bill_einvoice_status, bill.is_void
			FROM bill_draft b
			LEFT JOIN customer c ON b.customer_no = c.customer_no 
			LEFT JOIN bill_einvoice be ON be.bill_no = b.bill_no 
			LEFT JOIN bill ON bill.bill_no = b.bill_no 
			LEFT JOIN (
				SELECT bs1.* 
				FROM bill_status bs1 
				JOIN (
					SELECT bill_draft_no, MAX(id) AS max_id 
					FROM bill_status 
					GROUP BY bill_draft_no
				) bs2 ON bs1.id = bs2.max_id
			) bs ON bs.bill_draft_no = b.bill_draft_no
			LEFT JOIN (
				SELECT acs.* 
				FROM customer_status acs 
				JOIN (
					SELECT customer_no, MAX(status_id) AS max_status_id 
					FROM customer_status 
					GROUP BY customer_no
				) bcs ON acs.status_id = bcs.max_status_id
			) cs ON cs.customer_no = c.customer_no
			WHERE b.bill_draft_no = ?
		";
	
		$bill_query = $this->db->query($bill_sql, [$bill_draft_no]);
		
		if ($bill_query->num_rows() > 0) {
			$return_val['bill_info'] = $bill_query->row_array();
	
			// Detail
			$dtl_sql = "SELECT * FROM bill_draft_detail WHERE bill_draft_no = ?";
			$dtl_query = $this->db->query($dtl_sql, [$bill_draft_no]);
	
			foreach ($dtl_query->result_array() as $row) {
				$amount = abs($row['amount']);
				$tax = abs($row['tax_amount']);
				$is_credit = $row['amount'] < 0;
	
				$return_val['bill_dtl'][] = [
					'idx' => $row['idx'],
					'bill_draft_no' => $row['bill_draft_no'],
					'tranx_date' => $row['tranx_date'],
					'bill_type' => $row['bill_type'],
					'tax_code' => $row['tax_code'],
					'tax_percent' => $row['tax_percent'],
					'amount' => $amount,
					'tax_amount' => $tax,
					'adj_type' => $is_credit ? 'cr' : 'dr',
					'plus_minus' => $is_credit ? '-' : '+',
					'remark' => $row['remark'],
					'total_amount' => $amount + $tax
				];
			}
	
			// Approevrs
			$return_val['doc_lvl_1_approver'] = $this->db
				->query("SELECT user_id FROM docs_approver WHERE level = 1 AND doc_type = 'mb' AND doc_ref = ?", [$bill_draft_no])
				->result_array();

			$return_val['doc_lvl_2_approver'] = $this->db
				->query("SELECT user_id FROM docs_approver WHERE level = 2 AND doc_type = 'mb' AND doc_ref = ?", [$bill_draft_no])
				->result_array();
		} else {
			// Default bill_info when not found
			$return_val['bill_info'] = [
				'idx' => '',
				'bill_draft_no' => '',
				'bill_date' => '',
				'bill_type' => '',
				'related_bill_no' => '',
				'reason' => '',
				'customer_no' => '',
				'customer_name' => '',
				'package_name' => '',
				'monthly_charge' => '',
				'previous_balance' => '',
				'tranx_date' => '',
				'pay_date' => '',
				'payment_no' => '',
				'pay_amount' => '',
				'bill_status' => 'D',
				'related_payment_no' => '',
				'payment_received' => '0.00',
				'bill_einvoice_status' => '',
				'is_void' => ''
			];
		}
	
		return $return_val;
	}

	function lock_adjustment( $adjustment_no_arr = array() )
	{
		if( !empty($adjustment_no_arr) ){
			$adjustment_no_str = "";
			foreach( $adjustment_no_arr AS $adjustment_no ){
				$query_str = "UPDATE bill_adjustment SET is_lock = 1 WHERE tranx_date < '" . date("Y-m-1") . "' 
								AND adj_no = '".$adjustment_no."'; ";
				$query = $this->db->query($query_str);
				if( $this->db->affected_rows() )
				{
					$adjustment_no_str .= $adjustment_no.",";
				}
			}
			
			if( $adjustment_no_str != '' )
			{
				$this->load->model('action_log_model');			
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str( " Adjustment # : " . rtrim( $adjustment_no_str , "," ) );	
				$method 		= $this->router->method; 				
				$action_desc	= 'Monthly Bills Generation: Lock Adjustments';	
				$action_category = 'update';
				$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
			}
		}
	}
	
	function lock_payment( $payment_no_arr = array() )
	{
		if( !empty($payment_no_arr) )
		{
			$payment_no_str = "";
			foreach( $payment_no_arr AS $payment_no )
			{
				$query_str = "UPDATE payment SET is_lock = 1 
								WHERE pay_date < '" . date("Y-m-1") . "' AND payment_no = '".$payment_no."'  ";
				$query = $this->db->query($query_str);
				if( $this->db->affected_rows() )
				{
					$payment_no_str .= $payment_no.",";
				}
			}
			
			if( $payment_no_str != '' )
			{
				$this->load->model('action_log_model');			
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str( " Payment # : " . rtrim( $payment_no_str , "," ) );	
				$method 		= $this->router->method; 				
				$action_desc	= 'Monthly Bills Generation: Lock Payments';	
				$action_category = 'update';
				$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
			}
		}
	}
	
	
	function update_payment_processed( $payment_no_arr = array() )
	{

		if( !empty($payment_no_arr) )
		{
			$payment_no_str = "";
			foreach( $payment_no_arr AS $payment_no )
			{
				$query_str = "UPDATE payment SET processed = 1 
								WHERE pay_date < '" . date('Y-m-1') . "' AND payment_no = '".$payment_no."'; ";
				$query = $this->db->query($query_str);
				if( $this->db->affected_rows() )
				{
					$payment_no_str .= $payment_no.",";
				}
			}
			
			if( $payment_no_str != '' )
			{
				$this->load->model('action_log_model');			
				$ctrl			= $this->router->fetch_class();
				$esc_query_str	= $this->db->escape_str( " Payment # : " . rtrim( $payment_no_str , "," ) );	
				$method 		= $this->router->method; 				
				$action_desc	= 'Monthly Bills Generation: Process Payments';	
				$action_category = 'update';
				$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
			}
		}
	}

	function update_next_bill_date()
	{
		
		//no more next bill after terminated
		$query_str = "UPDATE customer SET next_bill_date = NULL 
						WHERE next_bill_date = '" . date('Y-m-1') . "' AND status = 't' ";
		$this->db->query($query_str);
		$query_str = "UPDATE customer SET next_bill_date = DATE_ADD(next_bill_date, INTERVAL 1 MONTH) 
						WHERE next_bill_date = '" . date('Y-m-1') . "' ";
		$this->db->query($query_str);
	}
	function generate_bill_detail($query_insert_detail)
	{
		$query_insert_detail = rtrim($query_insert_detail, ',');
		$query_str = "INSERT INTO bill_detail (bill_no, tranx_date, bill_type, tax_code, tax_percent, amount, tax_amount, remark) VALUES " . $query_insert_detail;
		$this->db->query($query_str);
	}
	function generate_bill_header($query_insert_header)
	{
		$query_insert_header = rtrim($query_insert_header, ',');
		$query_str = "INSERT INTO bill (bill_no, customer_no, bill_date, bill_due_date, previous_balance, payment_received, charges, tax_charges, amount, balance) VALUES " .
					$query_insert_header;
		$this->db->query($query_str);
	}

	function generate_bill_call($query_insert_call)
	{
		$query_insert_call = rtrim($query_insert_call, ',');
		$query_str = "INSERT INTO bill_call (customer_no, caller_id, start_time, answer_time, end_time, called, call_time, talk_time, tranx_date, amount, tax_amount, tax_code, tax_percent, total, remark, bill_no, created_by, created_date) VALUES " .
					$query_insert_call;
		$this->db->query($query_str);
	}

	function generate_bill_extra($query_insert_extra)
	{
		$query_insert_extra = rtrim($query_insert_extra, ',');
		$query_str = "REPLACE INTO bill_header_extra (bill_no, bill_date, customer_no, bill_cycle_month, package, package_name, payment_term, bill_unit_no, bill_addr_1, bill_addr_2, bill_addr_3, bill_postcode, bill_city, bill_state, email, phone, inst_unit_no, inst_addr1, inst_addr2, inst_addr3, inst_postcode, inst_city, inst_state, created_on, created_by) VALUES " .
					$query_insert_extra;
		$this->db->query($query_str);
	}

	function generate_bill_headers( $new_bill_no, $customer_no, $date_first_day, $date_last_day, $previous_balance, $payment_received, $payment_nums, $charges, $tax_charges, $amount, $balance ){
		
		$query_str = "INSERT INTO bill (bill_no, customer_no, bill_date, bill_due_date, previous_balance, payment_received, charges, tax_charges, amount, balance) VALUES " .
		$query_str = " INSERT INTO bill SET bill_no =  '".$new_bill_no."' , 
											customer_no = '".$customer_no."' , 
											bill_date = '".$date_first_day."' ,
											bill_due_date = '".$date_last_day."' ,
											previous_balance = '".$previous_balance."' ,
											payment_received = '".$payment_received."' ,
											charges = '".$charges."' , 
											tax_charges = '".$tax_charges."' , 
											amount = '".$amount."' ,
											balance = '".$balanace."';" ;
		$this->db->query($query_str);		
		if( $this->db->affected_rows() ){
			//update payment number
			foreach( $payment_nums AS $payment ){
				$payment_str = " UPDATE payment SET bill_no = '".$new_bill_no."' ";
			}
		}
	}


	function get_max_bill_draft_no()
	{
		$query_str = "SELECT MAX(bd.bill_draft_no) as bill_draft_no FROM bill_draft bd";
		$query = $this->db->query($query_str);
		return $query->row()->bill_draft_no;
	}

	function get_max_bill_no()
	{
		$query_str = "SELECT MAX(b.bill_no) as bill_no FROM bill b";
		$query = $this->db->query($query_str);
		return $query->row()->bill_no;
	}

	function get_consolidated_new_no()
	{
		$query_str = "SELECT MAX(c.inv_no) as inv_no FROM consolidated_einvoice c WHERE c.inv_no LIKE '".date('ym')."%'";
		$query = $this->db->query($query_str);
		$res = $query->row()->inv_no;
		if (empty($res)) {
			$get_max_bill_no = sprintf( "%06d", 0 );
			$get_max_bill_no = date("ym") . $get_max_bill_no;
			return $get_max_bill_no;
		} else {
			return $res;
		}
	}


	function this_month_bill_count(){
		$return_val = 0;
		$query_str = "SELECT bill_no FROM bill WHERE bill_date >= '" . date("Y-m-1") . "' AND is_manual = 0 LIMIT 1";
		$query = $this->db->query($query_str);
		if ($query->num_rows() >0) {
			$return_val = $query->num_rows();
		}
		return $return_val;
	}

	function this_month_bill_count_with_rundate($run_date){
		$return_val = 0;
		$query_str = "SELECT bill_no FROM bill WHERE bill_date >= ? AND is_manual = 0 LIMIT 1";
		$query = $this->db->query($query_str, [$run_date]);
		if ($query->num_rows() >0) {
			$return_val = $query->num_rows();
		}
		return $return_val;
	}

	function this_month_einvoice_count(){
		$return_val = 0;
		$query_str = "SELECT bill_no FROM bill_einvoice WHERE bill_date >= '" . date("Y-m-1") . "' LIMIT 1";
		$query = $this->db->query($query_str);
		if ($query->num_rows() >0) {
			$return_val = $query->num_rows();
		}
		return $return_val;
	}

	function bill_preparation($customer_no = '', $test_date=array())
	{

		$return_val = array();

		//notes:
		//bill preparation can be run based on individual customer_no, would be good for testing

		//logic flow:
		//1. get list of payment made by customer, this one excludes deposit payment, as it will be used to calculate this months charges
		//2. get the last bill of all the customers, this one also be used to calculate this months charges
		//3. main query written, basically get all customers that have to be billed, including those that are set for termination
		
		$this->load->model('customer_model');
		$this->load->model('profile_model');
		//get default tax code
        $query_str 			= "SELECT `val` FROM `sys_config` WHERE `key` = 'default_tax'";
		$result 			= $this->db->query($query_str)->result_array();
        $default_tax_code	= (empty($result[0]['val']))?'SV':$result[0]['val'];
		
        //get tax value from default tax code
        $query_str 			= "SELECT percent FROM sys_tax_type WHERE code = '".$default_tax_code."'";
		$result 			= $this->db->query($query_str)->result_array();
		$default_tax_rate 	= $result[0]['percent'] / 100;
		$default_tax_percent= $result[0]['percent'];
		$this->tax_rate 	= $default_tax_rate;
		
		$default_bill_type	= 1;
		$bill_description 	= "Subscription Fee";
		
		$this_month = true; //for generating current month bill
		$query_where = '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'default_dom_call_rate' ");
		$default_dom_call_rate = $config_record[0]['val'];

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'default_int_call_rate' ");
		$default_int_call_rate = $config_record[0]['val'];

		if (!empty($test_date)) {
			$last_month_ini = new DateTime($test_date['last_month_ini']);
			$last_month_end = new DateTime($test_date['last_month_end']);
			$month_ini = new DateTime($test_date['month_ini']);
			$month_end = new DateTime($test_date['month_end']);
			if( !empty($customer_no) ){
				//if test date puts customer no, then just load for that customer regardless of their next_bill_date setting
				$query_where .= "AND c.customer_no = '$customer_no' ";
			} else {
				//simulate production environment
				$query_where .= "AND ( c.next_bill_date IS NOT NULL AND c.next_bill_date <= '" . $month_ini->format('Y-m-d') . "' ) ";
			}
		} else {
			if( !empty($customer_no) ){
				$today = date("d");
				//this is used for bill detail, probably need to follow variable bill_cycle_date
				if( $today < $bill_cycle_day ){
					$next_bill_date = date( "Y-m-01" , strtotime( "-1 months") );
					$last_month_ini = new DateTime("first day of last month");
					$last_month_end = new DateTime("last day of last month");
					$month_ini = new DateTime("first day of this month");
					$month_end = new DateTime("last day of this month");
				}else{
					//for preview next month bill
					$next_bill_date = date( "Y-m-01" );
					$last_month_ini = new DateTime("first day of this month");
					$last_month_end = new DateTime("last day of this month");
					$month_ini = new DateTime("first day of next month");
					$month_end = new DateTime("last day of next month");
				}
				$query_where .= "AND c.customer_no = '$customer_no' ";
				$query_where .= "AND ( c.next_bill_date IS NOT NULL AND c.next_bill_date >= '" . $next_bill_date . "' ) ";
			}else{
				$last_month_ini = new DateTime("first day of last month");
				$last_month_end = new DateTime("last day of last month");
				$month_ini = new DateTime("first day of this month");
				$month_end = new DateTime("last day of this month");
				$query_where .= "AND ( c.next_bill_date IS NOT NULL AND c.next_bill_date <= '" . date('Y-m-01') . "' ) ";
			}
		}
		
		//Get all customer payment
		//$payment = $this->get_payment($last_month_end->format('Y-m-d'));
		$payment = $this->get_payment(date("Y-m-d"));
		$latest_bill_list = $this->get_last_bill();

		//testing_purpose comment below $query_str and $query
		/*$query_str = "	SELECT * FROM (
						(
							SELECT c.customer_no,c.name,c.pic_name,c.login_username,c.bill_name, c.status, c.category,
									c.building, c.activated_date, c.suspended_date, c.terminated_date,
									c.monthly_charge, c.bill_cycle_month,
									c.bill_by_email, c.email_1 , c.bill_by_post , c.package
							FROM customer c 
							WHERE ( status = 'r' OR status = 's' ) 
								AND c.activated_date <> 0 
							 " . $query_where . " 
						)
						UNION
						(
							SELECT c.customer_no,c.name,c.pic_name,c.login_username,c.bill_name, c.status, c.category,
									c.building, c.activated_date, c.suspended_date, c.terminated_date,
									c.monthly_charge, c.bill_cycle_month,
									c.bill_by_email, c.email_1 , c.bill_by_post , c.package
							FROM customer c
							WHERE ( status = 't' OR ( status = 's' AND category = 'b' ) ) 
							 " . $query_where . " 
								AND (
										EXISTS ( SELECT payment_no FROM payment
													WHERE payment.customer_no = c.customer_no
													AND pay_date BETWEEN '".$last_month_ini->format('Y-m-d')."'
													AND '".$last_month_end->format('Y-m-d')."' )
										
										OR
										
										EXISTS ( SELECT adj_no FROM bill_adjustment
													WHERE bill_adjustment.customer_no = c.customer_no
													AND tranx_date BETWEEN '".$last_month_ini->format('Y-m-d')."'
													AND '".$last_month_end->format('Y-m-d')."' )
									)
						)
						UNION
						(
							SELECT c.customer_no,c.name,c.pic_name,c.login_username,c.bill_name, c.status, c.category,
									c.building, c.activated_date, c.suspended_date, c.terminated_date,
									c.monthly_charge, c.bill_cycle_month,
									c.bill_by_email, c.email_1 , c.bill_by_post , c.package
							FROM customer c 
							WHERE ( status = 'r' OR status = 't' ) 
							AND ( c.suspended_date <> 0 OR c.terminated_date <> 0  )
							AND ( 
								( c.suspended_date BETWEEN '".$last_month_ini->format('Y-m-d')."' AND '".$last_month_end->format('Y-m-d')."' )
								OR 
								( c.terminated_date BETWEEN '".$last_month_ini->format('Y-m-d')."' AND '".$last_month_end->format('Y-m-d')."' )
							)
							AND c.activated_date <> 0 
							" . $query_where . " 
						)
						) z ORDER BY z.category, z.customer_no ";*/

		//Val 30-05-25 middle query should be obsolete, as currently we have a function that when customer make payment it would auto reactivate their account and tack on reactivate charges

		$query_str = "	SELECT * FROM (
		(
			SELECT c.customer_no,c.name,p.pic_name,c.login_username,c.bill_name, c.status AS account_status, c.category,
					c.building, 
					c.monthly_charge, c.bill_cycle_month,
					c.bill_by_email, p.pic_email_1 AS email_1 , c.bill_by_post , c.package, cs.transact_date AS latest_status_date, cs.status AS latest_status, csa.transact_date AS activated_date, csfa.transact_date AS first_activated_date, c.caller_id, c.bill_waive_period, c.free_package_upgrade, c.upgrade_package_id, c.bill_cycle_start_date, c.delay_trial_start   
			FROM customer c 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no) 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csa ON (csa.customer_no = c.customer_no) 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csfa ON (csfa.customer_no = c.customer_no) 
			left join profile p on (c.profile_id = p.acc_id) 
			WHERE ( cs.status = 'A' OR cs.status = 'S' ) 
			 " . $query_where . " 
		)
		UNION
		(
			SELECT c.customer_no,c.name,p.pic_name,c.login_username,c.bill_name, c.status AS account_status, c.category,
					c.building, 
					c.monthly_charge, c.bill_cycle_month,
					c.bill_by_email, p.pic_email_1 AS email_1 , c.bill_by_post , c.package, cs.transact_date AS latest_status_date, cs.status AS latest_status, csa.transact_date AS activated_date, csfa.transact_date AS first_activated_date, c.caller_id, c.bill_waive_period, c.free_package_upgrade, c.upgrade_package_id, c.bill_cycle_start_date, c.delay_trial_start       
			FROM customer c 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no) 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csa ON (csa.customer_no = c.customer_no) 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csfa ON (csfa.customer_no = c.customer_no) 
			left join profile p on (c.profile_id = p.acc_id) 
			WHERE ( cs.status = 'T' OR ( cs.status = 'S' AND category = 'b' ) ) 
			 " . $query_where . " 
				AND (
						EXISTS ( SELECT payment_no FROM payment
									WHERE payment.customer_no = c.customer_no
									AND pay_date BETWEEN '".$last_month_ini->format('Y-m-d')."'
									AND '".$last_month_end->format('Y-m-d')."' )
						
						OR
						
						EXISTS ( SELECT adj_no FROM bill_adjustment
									WHERE bill_adjustment.customer_no = c.customer_no
									AND tranx_date BETWEEN '".$last_month_ini->format('Y-m-d')."'
									AND '".$last_month_end->format('Y-m-d')."' )
					)
		)
		UNION
		(
			SELECT c.customer_no,c.name,p.pic_name,c.login_username,c.bill_name, c.status AS account_status, c.category,
					c.building, 
					c.monthly_charge, c.bill_cycle_month,
					c.bill_by_email, p.pic_email_1 AS email_1 , c.bill_by_post , c.package, cs.transact_date AS latest_status_date, cs.status AS latest_status, csa.transact_date AS activated_date, csfa.transact_date AS first_activated_date, c.caller_id, c.bill_waive_period, c.free_package_upgrade, c.upgrade_package_id, c.bill_cycle_start_date, c.delay_trial_start       
			FROM customer c 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) cs ON (cs.customer_no = c.customer_no) 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csa ON (csa.customer_no = c.customer_no) 
			LEFT JOIN (
				SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
			) csfa ON (csfa.customer_no = c.customer_no) 
			left join profile p on (c.profile_id = p.acc_id) 
			WHERE ( cs.status = 'S' OR cs.status = 'T' ) AND csa.transact_date IS NOT NULL 
			AND ( 
				( cs.transact_date BETWEEN '".$month_ini->format('Y-m-d')."' AND '".$month_end->format('Y-m-d')."' )
			) 
			" . $query_where . " 
		)
		) z ORDER BY z.category, z.customer_no ";	
		 
		//1st subquery filter to get customer where status = r 
		//2nd subquery filter to get customer where status = t with any Payment / Adjustment on last month
		//3rd subquery filter to get customer where SUSPENDED / TERMINATED on this month
		//~ ( YEAR(c.suspended_date)='".date('Y')."' AND MONTH(c.suspended_date)='".date('m')."')
		//~ OR 
		//~ ( YEAR(c.terminated_date)='".date('Y')."' AND MONTH(c.terminated_date)='".date('m')."')

		//log_message('error', 'Bill SQL:'.$query_str);
		//echo $query_str; exit;

		//echo $query_str;
		
		$query = $this->db->query($query_str);

		if ($query->num_rows() > 0) {
			foreach ( $query->result_array() as $row ) {
				
				if (
					$row['first_activated_date'] < date('Y-m-'.$bill_cycle_day) && 
					date('Y-m-d', strtotime($row['first_activated_date'])) !== date('Y-m-01') && 
					date('Y-m', strtotime($row['first_activated_date'])) == date('Y-m')
				) {
					if(!empty($test_date)){
						echo "<pre>";
						echo "Skipped this customer no cause by activated before bill cycle day (".$row['first_activated_date']."): " . $row['customer_no'];
						echo "</pre>";
					}

					unset($return_val[$row['customer_no']]);
					continue;
				}

				//_debug_array($row);

				$customer_no		= $row['customer_no'];
				$building			= $row['building'];
				$tax_charges		= 0;
				$total_charge		= 0;
				$total_tax_amount 	= 0;

				//what if pic name empty?
				$return_val[$customer_no]['pic_name'] = $row['pic_name'];
				$return_val[$customer_no]['bill_by_email'] = $row['bill_by_email'];
				$return_val[$customer_no]['email_1'] = $row['email_1'];
				
				//read package tax info
				$query_str 			= "	SELECT 	b.name AS `desc`, IFNULL( t.code , 'ZR' ) AS code, IFNULL( t.percent , '0.00' ) AS percent ,
												p.bill_type
										FROM package p 
										LEFT JOIN sys_bill_type b ON p.bill_type = b.bill_type_id 
										LEFT JOIN sys_tax_type t ON b.tax_code = t.code
										WHERE p.package_no = '".$this->db->escape_str( $row['package'] )."'";
				$result 			= $this->db->query($query_str)->row_array();
				
				if( !empty( $result) ){
					$bill_description 	= $result['desc'] ;
					$default_bill_type	= $result['bill_type'] ;
					$default_tax_code   = $result['code'] ;
					$default_tax_rate 	= $result['percent'] / 100 ;
					$default_tax_percent= $result['percent'] ;
				}
				/*
				 * Get the previous Balance / last bill
				 */
				if (isset($latest_bill_list[$customer_no])) {
					$last_bill = $latest_bill_list[$customer_no];
				}
				else {
					$last_bill['bill_no'] = '';
					$last_bill['bill_date'] = '';
					$last_bill['bill_due_date'] = '';
					$last_bill['balance'] = 0;
				}

				$return_val[$customer_no]['previous_balance'] = $last_bill['balance'];

				//BILL HEADER ADDRESS ETC HERE

				//USE NULL COALESCENCE JUST IN CASE
				$temp_customer_info = $this->customer_model->get_customer($customer_no);
				$temp_profile_info = $this->profile_model->get_profile($temp_customer_info['profile_id']);
				//make sure got detail only save
				if (isset($temp_profile_info['acc_id']) && isset($temp_customer_info['customer_no'])) {
					//no name? 
					$return_val[$customer_no]['extra_detail']['bill_cycle_month'] = $temp_customer_info['bill_cycle_month'];
					$return_val[$customer_no]['extra_detail']['package'] = $temp_customer_info['package'];
					$return_val[$customer_no]['extra_detail']['package_name'] = $temp_customer_info['package_name'];
					$return_val[$customer_no]['extra_detail']['bill_unit_no'] = $temp_customer_info['inst_unit_no'];
					$return_val[$customer_no]['extra_detail']['bill_addr_1'] = $temp_profile_info['bill_addr_1'];
					$return_val[$customer_no]['extra_detail']['bill_addr_2'] = $temp_profile_info['bill_addr_2'];
					$return_val[$customer_no]['extra_detail']['bill_addr_3'] = $temp_profile_info['bill_addr_3'];
					$return_val[$customer_no]['extra_detail']['bill_postcode'] = $temp_profile_info['bill_postcode'];
					$return_val[$customer_no]['extra_detail']['bill_city'] = $temp_profile_info['bill_city'];
					$return_val[$customer_no]['extra_detail']['bill_state'] = $temp_profile_info['bill_state'];
					$return_val[$customer_no]['extra_detail']['email'] = $temp_profile_info['pic_email_1'];
					$return_val[$customer_no]['extra_detail']['phone'] = $temp_customer_info['mobile_num'];
					$return_val[$customer_no]['extra_detail']['inst_unit_no'] = $temp_customer_info['inst_unit_no'];
					$return_val[$customer_no]['extra_detail']['inst_addr1'] = $temp_customer_info['inst_addr1'];
					$return_val[$customer_no]['extra_detail']['inst_addr2'] = $temp_customer_info['inst_addr2'];
					$return_val[$customer_no]['extra_detail']['inst_addr3'] = $temp_customer_info['inst_addr3'];
					$return_val[$customer_no]['extra_detail']['inst_postcode'] = $temp_customer_info['inst_postcode'];
					$return_val[$customer_no]['extra_detail']['inst_city'] = $temp_customer_info['inst_city'];
					$return_val[$customer_no]['extra_detail']['inst_state'] = $temp_customer_info['inst_state'];
					$return_val[$customer_no]['extra_detail']['payment_term'] = $temp_customer_info['payment_term'];

					//some of these fields might be updated below... for now just here
				}
				
				/*
				 * Get the payment
				 * */
				$return_val[$customer_no]['payment_received'] = empty($payment[$customer_no]) ? 0: $payment[$customer_no];
				//$return_val[$customer_no]['payment_num'] = $this->get_payment_by_customer( $customer_no , $last_month_end->format('Y-m-d') );
				$return_val[$customer_no]['payment_num'] = $this->get_payment_by_customer( $customer_no , date("Y-m-d") );
				
				/*
				 * Get the Charges ***Subscription Fee for new customer (pro-rated
				 * */
				$amount = 0;
				// ** to CAPTURE MISSING CUSTOMER while migration ONLY
				// ** Val 2025-05-29 commented out the migration calc part to prevent bugs
				$service_end = 0 ;
				//test this with activated date older than last month end
				//this part of the function only applies to customers where bill cycle was not run in last month, therefore it is adjusted in this month by adding an additional 
				/*if( $row['account_status'] == 'r' && $row['activated_date'] <= $last_month_end->format('Y-m-d') ){
					
					$rval = $this->get_new_customer_fee( $customer_no , $row['activated_date'] , $month_ini->format('Y-m-d') ) ;
					//subscription fee ONLY

					foreach( $rval AS $num => $arr ){
						$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																			 'bill_type'=>$default_bill_type,
																			 'tax_code'=>$default_tax_code,
																			 'tax_percent'=>$default_tax_percent,
																			 'adjust_desc'=>$bill_description,
																			 'amount'=>number_format($arr['amount'],2,".",""),
																			 'tax_amount'=>number_format( $arr['amount'] * $default_tax_percent / 100 , 2 , "." , "" ),
																			 'remark'=>$arr['remark'],
																			 'adj_no'=>"",
																			);
						
						if( $arr['end'] == 1 )
							$service_end = 1 ;

						$total_charge += $arr['amount'] ;
						$total_tax_amount += number_format( $arr['amount'] * $default_tax_percent / 100 , 2 , "." , "" );
					}
				}*/

				//testing get_new_customer_fee
				//print_r($return_val);
				//exit;
				
				//if latest status is terminated and business, put service end right away
				//if( $row['category'] == 'b' && $row['latest_status'] == 'T' ) $service_end = 1 ;
				//Val 30-05-25 - Why? just because category business then terminated no need prorated bill?
				
				//Category business will be given 3 months subscription EVEN they have been suspended
				//Category business wont be charged anymore once suspended ( 2021-12-21 added )
				//if( $row['category'] == 'b' && $row['latest_status'] == 'S' && $return_val[$customer_no]['payment_received'] == 0 )

				//Val 23-02-2026 - now logic is suspended still need to bill
				/*if( $row['latest_status'] == 'S' && $return_val[$customer_no]['payment_received'] == 0 && ($row['latest_status_date'] < $last_month_ini->format('Y-m-d')))
					$service_end = 1 ;
				*/

				//Val 30-05-25 - double check if suspended status , should no longer bill customer regardless of business or residential due to their internet already being cut off
				//Val 30-05-25 - use $service_end as indicator to skip billing for certain cases, like suspended
				//Val 30-05-25 if suspended date is less than last month's first day, means we cannot charge customer anymore as they already been suspended for more than a month

				//service end = 1 means won't be charged this cycle

				//if service ended on last month = false
				// ** calculate for CURRENT MONTH ONLY
				//if( $service_end == 0 && $row['category'] != 'w' ){
				$account_got_problem = false;

				if( $service_end == 0 ){
					
					// if( $this_month == true ){
					// 	$month_start = $month_ini->format('Y-m-d');
					// 	$month_end = $month_end->format('Y-m-d');
					// }else{
					// 	$month_start = $month_ini->format('Y-m-d');
					// 	$month_end = $month_end->format('Y-m-d');						
					// }

					//Val 08-09-25 - if customer bill_cycle_month is more than 1, we need to do the logic differently
					
					//monthly charge taken wrongly, should be taken from customer_package_history
					$return = $this->customer_model->get_customer_monthly_charge( $row['customer_no'] , $last_month_end->format( 'Y-m-d' ) );

					if( $return['monthly_charge'] != '' ){
						$row['monthly_charge'] = $return['monthly_charge'];
					}
					
					
					if( $row['category'] == 'b' ){ //for business
						// ** Suspended or registered business customer
						/* //suspended but still full charge ?
						if ( $row['suspended_date'] != '0000-00-00' 
								|| ( $row['status'] == 'r' && $row['terminated_date'] == '0000-00-00'  )   ) {
						*/
						if ( $row['account_status'] == 'r' && ($row['latest_status'] == 'A' || $row['latest_status'] == 'S') ) {

							//normal month

							$amount = number_format( $row['monthly_charge'] , 2 , "." , "" );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $month_ini->format('t M Y');

						}
						elseif ( ($row['latest_status'] == 'T') && ($row['latest_status_date'] >= $month_ini->format('Y-m-d') && $row['latest_status_date'] <= $month_end->format('Y-m-d')) ) {
							//terminated
							//val - suppose this is terminated for business
							$total_days = $month_ini->format('t');
							$total_used_days = new datetime($row['latest_status_date']);
							$total_used_days = $total_used_days->format("d");
							$amount = number_format( $row['monthly_charge'] / $total_days * $total_used_days , 2 , "." , "" );

							$this_month_end = new DateTime( date( 'Y-m-d' , strtotime($row['latest_status_date']) ) );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $this_month_end->format('d M Y');

							//$month_ini->format('d M Y') . " - " . (new datetime($row['terminated_date']))->format('d M Y');
						}
						elseif ( $row['account_status'] == 'r' && ($row['latest_status'] == 'T') && ($row['latest_status_date'] >= $month_end->format('Y-m-d')) ) {
							//val - terminated date is still not yet there, calculate as normal
							$amount = number_format( $row['monthly_charge'] , 2 , "." , "" );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $month_ini->format('t M Y');
						}
						//Val 23-02-2026 - suspended also need to keep billing, so no need prorate
						/*
						elseif ( ($row['latest_status'] == 'S') && ($row['latest_status_date'] >= $month_ini->format('Y-m-d') && $row['latest_status_date'] <= $month_end->format('Y-m-d') ) ) {
							$total_days = $month_ini->format('t');
							$total_used_days = new datetime($row['latest_status_date']);
							$total_used_days = $total_used_days->format("d");
							$amount = number_format( $row['monthly_charge'] / $total_days * $total_used_days , 2 , "." , "" );

							$this_month_end = new DateTime( date( 'Y-m-d' , strtotime($row['latest_status_date']) ) );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $this_month_end->format('d M Y');
							//$month_ini->format('d M Y') . " - " . (new datetime($row['suspended_date']))->format('d M Y');
						}
						*/
						else {
							$amount = 0;
							$subcription_remark = '';
						}
						
					}else{
					//elseif( $row['category'] == 'r' || $row['category'] == 'w' ){ //for resident and wholesale
						// ** Suspended customer ( exclude business category )
						//Val 30-05-25 - their method of suspended and terminated date is they will predetermine the dates in the future and update accordingly, however in our case, we need to check the last month instead since all the status updates are automatic and reflect in real time

						//put a variable to control whether or not the account is running the last charges in the following use cases, if true then dont do the bill_cycle_month billing for those e.g bill quarterly

						//Val 23-02-2026 - suspended also need to keep billing, so no need prorate
						/*
						if ( ($row['latest_status'] == 'S') && ($row['latest_status_date'] >= $month_ini->format('Y-m-d') && $row['latest_status_date'] <= $month_end->format('Y-m-d') ) ) {
							$total_days = $month_ini->format('t');
							$total_used_days = new datetime($row['latest_status_date']);
							$total_used_days = $total_used_days->format("d");
							$amount = number_format( $row['monthly_charge'] / $total_days * $total_used_days , 2 , "." , "" );

							$this_month_end = new DateTime( date( 'Y-m-d' , strtotime($row['latest_status_date']) ) );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $this_month_end->format('d M Y');
							//$month_ini->format('d M Y') . " - " . (new datetime($row['suspended_date']))->format('d M Y');

							$account_got_problem = true;
						}
						*/
						if ( ($row['latest_status'] == 'T') && ($row['latest_status_date'] >= $month_ini->format('Y-m-d') && $row['latest_status_date'] <= $month_end->format('Y-m-d')) ) {
							//terminated within this month
							//val - suppose this is terminated for residential/etc
							$total_days = $month_ini->format('t');
							$total_used_days = new datetime($row['latest_status_date']);
							$total_used_days = $total_used_days->format("d");
							$amount = number_format( $row['monthly_charge'] / $total_days * $total_used_days , 2 , "." , "" );

							$this_month_end = new DateTime( date( 'Y-m-d' , strtotime($row['latest_status_date']) ) );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $this_month_end->format('d M Y');

							//$month_ini->format('d M Y') . " - " . (new datetime($row['terminated_date']))->format('d M Y');

							$account_got_problem = true;
						}
						elseif( $row['account_status'] == 'r' 
							&& ($row['latest_status'] == 'A' || $row['latest_status'] == 'S') 
						){
							// there is a chance that a customer with status `r` but terminated 
							// ( in between the phase of converting status to `t` )
							// Sample case : a customer terminated on this month 30th, 
							// where his status is still in `r` before this month 30th, 
							// ( system will run a cron job to convert customer status to `t` on 30th )
							
							//normal monthly charge
							$amount = number_format( $row['monthly_charge'] , 2 , "." , "" );
							$subcription_remark = $month_ini->format('1 M Y') . " - " . $month_ini->format('t M Y');
						}
						//elseif ( $row['account_status'] == 'r' && ($row['terminated_date'] >= $month_end->format('Y-m-d')) ) {

							//also considered normal charge as terminated date is still not in this month 

							//$amount = number_format( $row['monthly_charge'] , 2 , "." , "" );
							//$subcription_remark = $month_ini->format('1 M Y') . " - " . $month_ini->format('t M Y');
						//}
						else {
							$amount = 0;
							$subcription_remark = '';

							$account_got_problem = true;
						}
						
					}

					//bill waiver period
					$waiver = 0;

					/* Old bill waiver logic - will be deprecated in the future*/
					if (!empty($row['bill_waive_period']) && in_array($row['latest_status'], ['A', 'S'])) {

						$first_activated_date = strtotime($row['first_activated_date']);
						$day = (int) date("j", $first_activated_date);

						$delay_month = (!empty($row['delay_trial_start']) && $row['delay_trial_start'] == 1) ? 1 : 0;
						$waiver_from = "";
						$waiver_to = "";

						$last_day_of_month = (int) date("t", $first_activated_date);

						if ($day === 1) {
							// Activation on 1st of month
							$waive_date = strtotime("+" . ($row['bill_waive_period'] + $delay_month) . " months", $first_activated_date);
							$waiver_from = date("Y-m-01", strtotime("+" . $delay_month . " months", $first_activated_date));
							$waiver_to = date("Y-m-t", strtotime("+" . ($row['bill_waive_period'] + $delay_month - 1) . " months", $first_activated_date));
							$waive_date = strtotime(date('Y-m-01', $waive_date));

							$month_after_waive_date = strtotime(date('Y-m-01', strtotime("+" . ($row['bill_waive_period'] + $delay_month) . " months", $first_activated_date)));

						} elseif ($day === $last_day_of_month) {
							// Activation on last day of month
							$waiver_from = date("Y-m-01", strtotime("+".(28 + ($delay_month * 30))." days", $first_activated_date));
							$waive_date = strtotime(date('Y-m-01', strtotime("+" . ($row['bill_waive_period'] + $delay_month + 1) . " months", $first_activated_date)));
							$waiver_to = date("Y-m-d", strtotime("-1 day", $waive_date));

							$month_after_waive_date = strtotime(date('Y-m-01', strtotime("+" . ($row['bill_waive_period'] + $delay_month + 1) . " months", $first_activated_date)));

						} else {
							// Activation on any other day
							$waive_date = strtotime(date('Y-m-01', strtotime("+" . ($row['bill_waive_period'] + $delay_month + 1) . " months", $first_activated_date)));
							$waiver_from = date("Y-m-01", strtotime("last day of +" . (1 + $delay_month) . " months", $first_activated_date));
							$waiver_to = date("Y-m-t", strtotime("+" . ($row['bill_waive_period'] + $delay_month) . " months", $first_activated_date));

							$month_after_waive_date = strtotime(date('Y-m-01', strtotime("+" . ($row['bill_waive_period'] + $delay_month + 1) . " months", $first_activated_date)));
						}

						// echo date('Y-m-d', $waive_date);
						// echo "<br>";

						if(!empty($test_date)){
							if ($month_after_waive_date > strtotime(date('Y-m-01 12:00:00'))) {
								echo $row['customer_no'].' '.$waiver_from. ' - '.$waiver_to.' Current:'.$month_ini->format('Y-m-d 00:00:00').' Month After Waiver:'.date("Y-m-d", $month_after_waive_date);
								echo "<br>";
							}
						}

						// Determine if waiver applies
						if ($waive_date > strtotime($month_ini->format('Y-m-d 00:00:00')) && strtotime($waiver_from) <= strtotime($month_ini->format('Y-m-d 00:00:00'))) {
							$amount = 0;
							$subcription_remark .= " Waiver Period: $waiver_from - $waiver_to";
							$waiver = 1;
						} else {
							// First month after waiver ends
							//Val 06-06-2026 - add another condition here, bill condition for this must be after waiver start
							if ( ( $month_after_waive_date >= strtotime($month_ini->format('Y-m-d 00:00:00')) ) && (strtotime($month_ini->format('Y-m-d 00:00:00')) > strtotime($waiver_from)) ) {
								$after_waive_remark = "Free {$row['bill_waive_period']} Months, Charges waived from $waiver_from to $waiver_to";

								$return_val[$customer_no]['charge_detail'][] = [
									'tranx_date' => $month_ini->format('Y-m-d'),
									'bill_type' => $default_bill_type,
									'tax_code' => $default_tax_code,
									'tax_percent' => $default_tax_percent,
									'adjust_desc' => $bill_description,
									'amount' => 0,
									'tax_amount' => 0,
									'remark' => $after_waive_remark,
									'adj_no' => "",
								];
							}
						}
					}

					/* Val 31-10-25 new Prorate Adjustment 
					//get customer waiver period
					$customer_waiver_info = $this->customer_model->get_customer_bill_waiver($row['customer_no']);
					if (!empty($row['bill_waive_period']) && $row['latest_status'] == 'A') {

						$waiver_from = $customer_waiver_info['waiver_start_date'];
						$waiver_to = $customer_waiver_info['waiver_end_date'];

						//check activated date is it within bill_waive_period, if yes then $amount = 0
						$first_activated_date = $row['first_activated_date'];
						if (date("d", strtotime($first_activated_date)) == '01') {

							$month_after_waive_date = strtotime("+".($row['bill_waive_period']+1)." months", strtotime($first_activated_date));
							$month_after_waive_date = strtotime(date('Y-m-01', $month_after_waive_date));

						} else if (
							( (int)date("n", strtotime($first_activated_date)) == 1 && (int)date("j", strtotime($first_activated_date)) == 31 ) ||  
							( (int)date("n", strtotime($first_activated_date)) == 2 && (int)date("j", strtotime($first_activated_date)) >= 28 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 3 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 4 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 5 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 6 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 7 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 8 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 9 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 10 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 11 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
							( (int)date("n", strtotime($first_activated_date)) == 12 && (int)date("j", strtotime($first_activated_date)) == 31 ) 
						) {
							//need to refactor the above
							//got bug calculating when activated day is the last day of the month

							$month_after_waive_date = strtotime("+1 days", strtotime($waiver_to));
							$month_after_waive_date = strtotime(date('Y-m-01', $month_after_waive_date));

							//echo "<br>";
							//echo date("Y-m-d", $month_after_waive_date);

						} else {

							$month_after_waive_date = strtotime("+".($row['bill_waive_period']+2)." months", strtotime($first_activated_date));
							$month_after_waive_date = strtotime(date('Y-m-01', $month_after_waive_date));

						}

						//echo date("Y-m-d", $waive_date); 
						//echo "<br>";

						//if ($waive_date > strtotime($month_ini->format('Y-m-d 00:00:00'))) {
						if (strtotime($waiver_to) > strtotime($month_ini->format('Y-m-d 00:00:00'))) {
							$amount = 0;
							$subcription_remark .= " Waiver Period: ".$waiver_from." - ".$waiver_to;

							//if this is the last month of waiver... maybe the from and to have to reflect the last month otherwise the bill will show quite weird

							$waiver = 1;
						} else {
							//its else so it must be over waive date
							//add in additional remark if its the first month after waive date
							if ($month_after_waive_date > strtotime($month_ini->format('Y-m-d 00:00:00'))) {

								$after_waive_remark = "Free ".$row['bill_waive_period']." Months, Charges waived from ".$waiver_from." to ".$waiver_to;

								$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																			'bill_type'=>$default_bill_type,
																			'tax_code'=>$default_tax_code,
																			'tax_percent'=>$default_tax_percent,
																			'adjust_desc'=>$bill_description,
																			'amount'=>0,
																			'tax_amount'=>0,
																			'remark'=>$after_waive_remark,
																			'adj_no'=>"",
																		);
							}
						}

					}
					*/

					//check for prorated bill, if prorated bill found, dont add monthly charge, instead just process adjustments
					//Val 31-05-25 - should no need run this anymore, prorated is additional charge will count with this month's bill
					/*$prorated_chk = $this->check_last_month_prorated( $row['customer_no'], $last_month_ini, $last_month_end );

					if ($prorated_chk) {
						$amount = 0;
						$subcription_remark = '';
					}*/
					
					//if no subscription remark, dont create obj
					if (!empty($subcription_remark)) {
						$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																			'bill_type'=>$default_bill_type,
																			'tax_code'=>$default_tax_code,
																			'tax_percent'=>$default_tax_percent,
																			'adjust_desc'=>$bill_description,
																			'amount'=>number_format( $amount , 2 , "." , "" ),
																			'tax_amount'=>number_format( $amount * $default_tax_percent / 100 , 2 , "." , "" ),
																			'remark'=>$subcription_remark,
																			'adj_no'=>"",
																		);
					}

					$total_charge += $amount;
					$total_tax_amount += number_format( $amount * $default_tax_percent / 100 , 2 , "." , "" ) ;

					//Val 13-08-25 other charges here
					//if bill waiver is on for this account, don't count additional charges in yet
					if (empty($waiver) && $row['bill_cycle_month'] < 2) {
						$query_str = "	SELECT cac.*, sbt.name as bill_type_name, sbt.is_debit , sbt.tax_code , 
										sbt.exclude_from_bill_calculation , stt.percent 
						FROM customer_additional_charge cac
						INNER JOIN sys_bill_type sbt ON cac.bill_type = sbt.bill_type_id 
						INNER JOIN sys_tax_type stt ON stt.code = sbt.tax_code 
						WHERE cac.customer_no = '".$customer_no."' AND sbt.exclude_from_bill_calculation = 0 AND (cac.end_date >= '".$last_month_end->format('Y-m-d')."' OR cac.end_date = '0000-00-00' OR cac.end_date IS NULL) ";

						$query_others = $this->db->query($query_str);
						$row_others = $query_others->result_array();
						foreach ( $row_others as $others_row ) {
							$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																			'bill_type'=>$others_row['bill_type'],
																			'tax_code'=>$others_row['tax_code'],
																			'tax_percent'=>$others_row['percent'],
																			'adjust_desc'=>$others_row['ac_name'],
																			'amount'=>number_format( $others_row['amount'] , 2 , "." , "" ),
																			'tax_amount'=>number_format( $others_row['amount'] * $others_row['percent'] / 100 , 2 , "." , "" ),
																			'remark'=>(!empty($others_row['remark'])?$others_row['remark']:''),
																			'adj_no'=>"",
																		);

							$total_charge += $others_row['amount'];
							$total_tax_amount += number_format( $others_row['amount'] * $others_row['percent'] / 100 , 2 , "." , "" ) ;
						}

					}

				}//end service end on last month 
				else{
					//service end = charge_detail is empty
					$return_val[$customer_no]['charge_detail'] = array();
				}
				
				$adj_month_ini = $last_month_ini;
				
				//For wholesale, need to times the bill cycle month if bill cycle month more than 1
				//if ($row['category'] == 'w' && $row['bill_cycle_month'] > 1 ) {

				//Val - 08-09-2025, do logic for bill_cycle_month > 1, for customer that want quarterly billing for example
				//1. check if no. of month to bill is now?
				//2. if yes, then do as normal but times the number of month charges
				//3. if no, dont generate bill (empty)
				//4. handle case if suspension on the corner or termination, checks every month... 
				//	4.1 if suspended, then don't bill for now, 
				//	4.2 if terminated, do prorate and bill


				//use this variable to control if the below code cycle is run or not
				$not_bill_this_month = false;
				if ($row['bill_cycle_month'] > 1 ) {

					if ($service_end == 1) {
						//service already ended due to suspension so dont bill
						$not_bill_this_month = true;
					}

					if ($account_got_problem) {
						//account got something wrong and is prorated this month due to suspended or terminated
						$not_bill_this_month = true;
					}

					//how do we determine when to bill and when not to bill?
					if (!empty($row['bill_cycle_start_date'])) {
						$first_activated_date = $row['bill_cycle_start_date'];
					} else {
						$first_activated_date = $row['first_activated_date'];
					}
					$cycle_months_array = $this->get_cycle_month_array($first_activated_date, $row['bill_cycle_month']);
					/*
					echo "Cycle months array<br>";
					echo "<pre>";
					print_r($cycle_months_array);
					echo "</pre>";
					*/
					$cycle_ini = date("n", strtotime($month_ini->format('Y-m-d')));
					if (!in_array($cycle_ini, $cycle_months_array)) {
						//this month is not in the cycle
						$not_bill_this_month = true;
					}

					/*
					for ($l = 0; $l < ($row['bill_cycle_month'] - 1); $l++) {

						$cycle_ini = date("1 M Y", strtotime("+".($l+1)." month", strtotime($month_ini->format('Y-m-d'))));
						$cycle_end = date("t M Y", strtotime("+".($l+1)." month", strtotime($month_ini->format('Y-m-d'))));

						//this is iffy - using remark to match... could be wrong... so if any changes to remark, need to be aware of this
						$chk_cycle = $this->check_if_cycle_billed($customer_no, $cycle_ini);

						if ($chk_cycle) {
							$not_bill_this_month = true;
							break;
						}

					}
					*/

					if( $not_bill_this_month ){
						//dont run

						$total_charge -= $return_val[$customer_no]['charge_detail'][0]['amount'] ?? 0.00;
						$total_tax_amount -= $return_val[$customer_no]['charge_detail'][0]['tax_amount'] ?? 0.00;

						unset($return_val[$customer_no]['charge_detail'][0]);
						$return_val[$customer_no]['charge_detail'] = array_values($return_val[$customer_no]['charge_detail']);

						//note, don't unset payment? process as they come?
					} else {

						//This formula to takecare of prorate of first month for new wholesale customer
						$this_month_charge = 0;

						//need to refactor this
						$query_str = "	SELECT cac.*, sbt.name as bill_type_name, sbt.is_debit , sbt.tax_code , 
										sbt.exclude_from_bill_calculation , stt.percent 
						FROM customer_additional_charge cac
						INNER JOIN sys_bill_type sbt ON cac.bill_type = sbt.bill_type_id 
						INNER JOIN sys_tax_type stt ON stt.code = sbt.tax_code 
						WHERE cac.customer_no = '".$customer_no."' AND sbt.exclude_from_bill_calculation = 0 AND (cac.end_date >= '".$last_month_end->format('Y-m-d')."' OR cac.end_date = '0000-00-00' OR cac.end_date IS NULL) ";

						$query_others = $this->db->query($query_str);
						$row_others = $query_others->result_array();

						foreach ( $row_others as $others_row ) {
							$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																			'bill_type'=>$others_row['bill_type'],
																			'tax_code'=>$others_row['tax_code'],
																			'tax_percent'=>$others_row['percent'],
																			'adjust_desc'=>$others_row['ac_name'],
																			'amount'=>number_format( $others_row['amount'] , 2 , "." , "" ),
																			'tax_amount'=>number_format( $others_row['amount'] * $others_row['percent'] / 100 , 2 , "." , "" ),
																			'remark'=>(!empty($others_row['remark'])?$others_row['remark']:''),
																			'adj_no'=>"",
																		);

							$total_charge += $others_row['amount'];
							$total_tax_amount += number_format( $others_row['amount'] * $others_row['percent'] / 100 , 2 , "." , "" ) ;
						}
						
						//$amount += number_format( $row['monthly_charge'] * ($row['bill_cycle_month'] - 1) , 2 , "." , "" );

						//do loop for every month is one charge item
						for ($l = 0; $l < ($row['bill_cycle_month'] - 1); $l++) {
							$this_month_charge = number_format( $row['monthly_charge'] , 2 , "." , "" );
							
							//$subcription_remark = $month_ini->format('1 M Y') . " - " . date( 't M Y', strtotime("+". ($row['bill_cycle_month'] - 1 ) ." months", strtotime($month_ini->format('Y-m-d'))) );

							$cycle_ini = date("1 M Y", strtotime("+".($l+1)." month", strtotime($month_ini->format('Y-m-d'))));
							$cycle_end = date("t M Y", strtotime("+".($l+1)." month", strtotime($month_ini->format('Y-m-d'))));
							$cycle_end_ymd = date("Y-m-d", strtotime("+".($l+1)." month", strtotime($month_ini->format('Y-m-d'))));
							$subcription_remark = $cycle_ini . " - " . $cycle_end;
							
							$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																					'bill_type'=>$default_bill_type,
																					'tax_code'=>$default_tax_code,
																					'tax_percent'=>$default_tax_percent,
																					'adjust_desc'=>$bill_description,
																					'amount'=>$this_month_charge,
																					'tax_amount'=>number_format( $this_month_charge * $default_tax_percent / 100 , 2 , "." , "" ) ,
																					'remark'=>$subcription_remark,
																					'adj_no'=>"",
																				);
							$total_charge += $this_month_charge;
							$total_tax_amount += number_format( $this_month_charge * $default_tax_percent / 100 , 2 , "." , "" ) ;

							//other charges - for each month, do select because of the end date
							$query_str = "	SELECT cac.*, sbt.name as bill_type_name, sbt.is_debit , sbt.tax_code , 
											sbt.exclude_from_bill_calculation , stt.percent 
							FROM customer_additional_charge cac
							INNER JOIN sys_bill_type sbt ON cac.bill_type = sbt.bill_type_id 
							INNER JOIN sys_tax_type stt ON stt.code = sbt.tax_code 
							WHERE cac.customer_no = '".$customer_no."' AND sbt.exclude_from_bill_calculation = 0 AND (cac.end_date >= '".$cycle_end_ymd."' OR cac.end_date = '0000-00-00' OR cac.end_date IS NULL) ";

							$query_others = $this->db->query($query_str);
							$row_others = $query_others->result_array();

							foreach ( $row_others as $others_row ) {
								$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$month_ini->format('Y-m-d'),
																				'bill_type'=>$others_row['bill_type'],
																				'tax_code'=>$others_row['tax_code'],
																				'tax_percent'=>$others_row['percent'],
																				'adjust_desc'=>$others_row['ac_name'],
																				'amount'=>number_format( $others_row['amount'] , 2 , "." , "" ),
																				'tax_amount'=>number_format( $others_row['amount'] * $others_row['percent'] / 100 , 2 , "." , "" ),
																				'remark'=>(!empty($others_row['remark'])?$others_row['remark']:''),
																				'adj_no'=>"",
																			);

								$total_charge += $others_row['amount'];
								$total_tax_amount += number_format( $others_row['amount'] * $others_row['percent'] / 100 , 2 , "." , "" ) ;
							}

						}

						/*
						if ( !empty($last_bill['bill_date']) ) {
							$adj_month_ini = new DateTime($last_bill['bill_date']);
						}

						echo "Adjusted Month:".$adj_month_in->format('1 M Y');
						*/

					}
				}


				/* POST CHARGE */
				
				if(( $row['bill_by_post'] == 1 ) && (!$not_bill_this_month)){

					$query_str = " SELECT post_charge FROM package WHERE package_no = '".$row['package']."' ";
					$query_post = $this->db->query($query_str);
					if( $query_post->num_rows() > 0 )
						$post = $query_post->row_array();
					else
						$post['post_charge'] = 0 ;
					
					if( $post['post_charge'] > 0 ){

						$charge_str = " SELECT sbt.name, sbt.tax_code, stt.percent
										FROM sys_bill_type sbt 
										INNER JOIN sys_tax_type stt ON stt.code = sbt.tax_code 
										WHERE sbt.bill_type_id = '6' ";
						$charge = $this->db->query($charge_str);
						$charge_info = $charge->row_array();
						
						$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>date('Y-m-01'),
																		 'bill_type'=>6,
																		 'tax_code'=>$charge_info['tax_code'],
																		 'tax_percent'=>$charge_info['percent'],
																		 'adjust_desc'=>$charge_info['name'],
																		 'amount'=>$post['post_charge'],
																		 'tax_amount'=>number_format( $post['post_charge'] * $charge_info['percent'] / 100 , 2 , "." , "" ) ,
																		 'remark'=>'Postage charge',
																		 'adj_no'=>'',
																		);
						
						$total_charge += $post['post_charge'];
						$total_tax_amount += number_format( $post['post_charge'] * $charge_info['percent'] / 100 , 2 , "." , "" ) ;
						
					}
										
				}

				/*
				 * Get the adjustment record
				 * Becareful the adj_month_ini, specially create for wholesale where bill cycle more than 1 month
				 * Skip the bill_type=11 as deposit will not take into bill process 
				 * bill_type 11 now display as usual but its amount DOES NOT sum up into total amount
				 * sys_bill_type setting exclude_from_bill_calculation
				*/
								
				$query_str = "	SELECT 	ba.adj_no, ba.tranx_date, ba.bill_type, ba.amount, ba.remark, ba.adjust_type,
										sbt.name as bill_type_name, sbt.is_debit , sbt.tax_code , 
										sbt.exclude_from_bill_calculation , stt.percent 
								FROM bill_adjustment ba 
								INNER JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
								INNER JOIN sys_tax_type stt ON stt.code = sbt.tax_code 
								WHERE ( 
									(ba.customer_no ='" . $customer_no . "' AND ba.adjust_by = 'i' ) 
									OR 
									(ba.building =' " . $building . "' AND ba.adjust_by = 'b')
									OR
									(ba.area =' " . "(SELECT b.area_id FROM building b WHERE b.building_no = $building)" . "' AND ba.adjust_by = 'a') 
								) 
								AND ( ba.tranx_date <= '" . $last_month_end->format('Y-m-d') . "' ) 
								AND ba.is_lock = 0
								ORDER BY tranx_date ";
								
								//AND ba.bill_type <> 11 
								//bill_type 11 = deposit return
				$query_adj = $this->db->query($query_str);
				//if( $query_adj->num_rows > 0 ) echo $query_str . '<br /><br />' ;
				foreach ( $query_adj->result_array() as $adj_row ) {

					if ($not_bill_this_month) {
						//if bill cycle more than 1 month and not in billing month, skip, charge at the time when we bill altogether
						continue;
					}

					if ($adj_row['adjust_type'] == 'dr') {
						$amount = number_format( $adj_row['amount'] , 2 , "." , "" ) ;
					}
					else {
						$amount = number_format( $adj_row['amount'] * -1 , 2 , "." , "" ) ;
					}
					$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>$adj_row['tranx_date'],
																			'bill_type'=>$adj_row['bill_type'],
																			'tax_code'=>$adj_row['tax_code'],
																			'tax_percent'=>$adj_row['percent'],
																			'adjust_desc'=>$adj_row['bill_type_name'],
																			'amount'=>$amount,
																			'tax_amount'=>number_format( $amount * $adj_row['percent'] / 100 , 2 , "." , "" ) ,
																			'remark'=>$adj_row['remark'],
																			'adj_no'=>$adj_row['adj_no'],
																		);
					$return_val['adjustment_no'][] = $adj_row['adj_no'] ;
					
					if( $adj_row['exclude_from_bill_calculation'] == 0 ){
						$total_charge += $amount;
						$total_tax_amount += number_format( $amount * $adj_row['percent'] / 100 , 2 , "." , "" ) ;
					}
					
				}

				/* Phone charges */

				//read from phone record table, search by date, group by caller id, calculate charges and sum it to a bill detail row

				//use start time as indicator
				$return_val[$customer_no]['call_detail'] = array();
				$query_str = "
				SELECT pcr.* 
				FROM phone_call_record pcr 
				WHERE pcr.start_time >= '".$last_month_ini->format('Y-m-d')." 00:00:00' 
				AND pcr.start_time <= '".$last_month_end->format('Y-m-d')." 23:59:59' 
				AND pcr.caller_no = '".$row['caller_id']."' 
				";
				$query_phone = $this->db->query($query_str);

				//phone bill info and tax
				$phone_call_charge_str = " SELECT sbt.name, sbt.tax_code, stt.percent
								FROM sys_bill_type sbt 
								INNER JOIN sys_tax_type stt ON stt.code = sbt.tax_code 
								WHERE sbt.bill_type_id = '40' ";
				$phone_call_charge = $this->db->query($phone_call_charge_str);
				$phone_call_charge_info = $phone_call_charge->row_array();

				//dom/int rate
				$dom_query_str = " SELECT dom_rate, int_rate FROM package WHERE package_no = '".$row['package']."' ";
				$query_dom = $this->db->query($dom_query_str);
				if( $query_dom->num_rows() > 0 ) {
					$dom_return = $query_dom->row_array();
					$dom_rate = $dom_return['dom_rate'];
					$int_rate = $dom_return['int_rate'];
				} else {
					$dom_rate = $default_dom_call_rate;
					$int_rate = $default_int_call_rate;
				}

				$sum_phone_charges = 0;
				$sum_phone_tax_charges = 0;
				foreach ( $query_phone->result_array() as $phone_row ) {

					if ($not_bill_this_month) {
						//if bill cycle more than 1 month and not in billing month, skip, charge at the time when we bill altogether
						continue;
					}

					//tally the charges of all the records and give a sum
					//each charge calc in 2 decimal, and then add up
					try {
						$row_phone_charge = number_format( ( ($phone_row['talk_time'] / 60) * $dom_rate ) , 2 , "." , "" ) ;
						$row_phone_tax = number_format( $row_phone_charge * $phone_call_charge_info['percent'] / 100 , 2 , "." , "" );
						$sum_phone_charges += $row_phone_charge;
						$sum_phone_tax_charges += $row_phone_tax;

						//put ready information into array, this one will be inserted in to table when bill is run to keep the history of the call charges etc
						$return_val[$customer_no]['call_detail'][] = array(
							'tranx_date'=>date('Y-m-01'),
							'caller_id' => $phone_row['caller_no'],
							'start_time' => $phone_row['start_time'],
							'answer_time' => $phone_row['answer_time'],
							'end_time' => $phone_row['end_time'],
							'called' => $phone_row['callee_no'],
							'call_time' => $phone_row['call_time'],
							'talk_time' => $phone_row['talk_time'],
							'amount'=>$row_phone_charge,
							'tax_amount'=> $row_phone_tax,
							'tax_code'=>$phone_call_charge_info['tax_code'],
							'tax_percent'=>$phone_call_charge_info['percent'],
							'total' => $row_phone_charge + $row_phone_tax,
							'remark' => 'Call Charges '.$phone_row['callee_no'],
						);

					} catch (Exception $phone_e) {
						$sum_phone_charges += 0;
						$sum_phone_tax_charges += 0;
						log_message('error', 'phone charge calc error:'.print_r($phone_e, true));
					}
				}

				if ($sum_phone_charges > 0) {
					//if got phone charges, add line to charge_detail

					//current fixed code for phone charges , set at 40
					try {
						$return_val[$customer_no]['charge_detail'][] = array('tranx_date'=>date('Y-m-01'),
																		 'bill_type'=>40,
																		 'tax_code'=>$phone_call_charge_info['tax_code'],
																		 'tax_percent'=>$phone_call_charge_info['percent'],
																		 'adjust_desc'=>$phone_call_charge_info['name'],
																		 'amount'=>$sum_phone_charges,
																		 'tax_amount'=>$sum_phone_tax_charges ,
																		 'remark'=>$last_month_ini->format('Y-m-d').' - '.$last_month_end->format('Y-m-d'),
																		 'adj_no'=>'',
																		);

						$total_charge += $sum_phone_charges;
						$total_tax_amount += $sum_phone_tax_charges ;

					} catch (Exception $phone_e) {
						log_message('error', 'phone charge calc error:'.print_r($phone_e, true));
					}

				}

				//for call details, can use the original phone record table to display line by line

				$total_charge = number_format($total_charge,2,".","");
				//$tax_charges = $total_charge * $this->tax_rate;
				$tax_charges = $total_tax_amount;
				$tax_charges  = number_format($tax_charges,2,".","");
				$balance = ($return_val[$customer_no]['previous_balance'] - $return_val[$customer_no]['payment_received']) + $total_charge + $tax_charges;
				$balance	  = number_format($balance,2,".","");

				$return_val[$customer_no]['bill_period'] = $month_ini->format('M, Y');
				$return_val[$customer_no]['charges'] = $total_charge ;
				$return_val[$customer_no]['tax_charges'] = $tax_charges ;
				$return_val[$customer_no]['amount'] = $total_charge + $tax_charges;
				$return_val[$customer_no]['balance'] = $balance;
			}
			
		}
		else {

			//BILL HEADER ADDRESS ETC HERE

			$return_val = array(
							$customer_no => array(
											'bill_by_email' => 0,
											'email_1' => '',
											'previous_balance' => 0,
											'payment_received' => 0,
											'payment_num' => array(),
											'tax_charges' => 0,
											'charge_detail' => array(),
											'charges' => 0,
											'amount' => 0,
											'balance' => 0,
											)
						);
		}
		
		//$return_val['payment_detail'] = $payment['payment_no'];
		return $return_val;
	}

	function get_new_customer_fee( $customer_no , $activated_date , $current_date ){
		//return true = is new users
		//filter out a new user without bill n adjustment ( for subscription bill_type = 1 only )

		//need to rewrite this query using latest customer_status table
		$query_str = " SELECT c.customer_no , name , cs.transact_date AS latest_status_date, cs.status AS latest_status,    monthly_charge, csa.transact_date AS activated_date 
						FROM customer c 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MAX(status_id) AS max_status_id FROM customer_status GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) cs ON (cs.customer_no = c.customer_no) 
						LEFT JOIN (
							SELECT acs.* FROM customer_status acs JOIN (SELECT customer_no, MIN(status_id) AS max_status_id FROM customer_status WHERE `status` = 'A' GROUP BY customer_no) bcs ON (acs.status_id = bcs.max_status_id) 
						) csa ON (csa.customer_no = c.customer_no) 
						WHERE csa.transact_date <= '".date( 'Y-m-01' , strtotime($current_date) )."'
						AND (cs.status = 'A' OR cs.status = 'S' OR cs.status = 'T') 
						AND c.customer_no = '".$customer_no."'
						AND NOT EXISTS( SELECT MAX(b.bill_no) AS latest_bill_no FROM bill b
										WHERE b.customer_no = c.customer_no GROUP BY b.customer_no )
						AND NOT EXISTS( SELECT adj_no FROM bill_adjustment adj
										WHERE adj.customer_no = c.customer_no
										AND adj.bill_type = 1
										AND adj.tranx_date BETWEEN '".date( 'Y-m-01' , strtotime('-1 month',strtotime($current_date)) )."'
															   AND '".date( 'Y-m-t' ,  strtotime('-1 month',strtotime($current_date)) )."'
									  ) " ;

		$query = $this->db->query($query_str);
		$return_val = array();
		if( $query->num_rows() > 0 ){
			$row = $query->row_array();
			$end_date = NULL;

			/*if( $row['suspended_date'] != NULL )
				$end_date = $row['suspended_date'] ;
			elseif( $row['terminated_date'] != NULL )
				$end_date = $row['terminated_date'] ;*/

			if ($row['latest_status'] == 'S' || $row['latest_status'] == 'T') {
				$end_date = $row['latest_status_date'] ;
			}

			$i = 0 ;
			$is_terminated = 0;
			while( strtotime($activated_date) < strtotime($current_date) ){
				//echo "Calculating : " . $activated_date . '<br />' ;
				$this_month_ini = new DateTime( date( 'Y-m-d' , strtotime($activated_date) ) );
				$this_month_end = new DateTime( date( 'Y-m-t' , strtotime($activated_date) ) );

				//if this customer terminated b4 month end then assign terminated date -> $this_month_end
				if( $end_date!=NULL && strtotime($end_date) < strtotime( date( 'Y-m-t' , strtotime($activated_date) ) ) ){
					$this_month_end = new DateTime( date( 'Y-m-d' , strtotime($end_date) ) );
					$is_terminated = true;
					$return_val[$i]['end'] = 1 ;
				}else
					$return_val[$i]['end'] = 0 ;


				$total_days = date( "t" , strtotime($activated_date) );
				$total_used_days = $this_month_end->diff( new datetime($activated_date) )->format('%a') + 1; // + 1 because need to including the day start using
				$amount = ($row['monthly_charge'] / $total_days * $total_used_days );
				$subcription_remark = $this_month_ini->format('d M Y') . " - " . $this_month_end->format('d M Y');

				$return_val[$i]['tranx_date'] = $activated_date;
				$return_val[$i]['amount'] = number_format( $amount , 2 , "." , "" );
				$return_val[$i]['remark'] = $subcription_remark;
				if( $is_terminated == true ) break;
				
				//set activated date = 1st of the month, then +1 on it
				$activated_date = date( "Y-m-01" , strtotime( $activated_date ));
				$activated_date = date( "Y-m-01" , strtotime("+1 month", strtotime($activated_date)) );
				$i++ ;
			}
		}
		return $return_val ;
	}

	function get_payment($pay_month)
	{
		//This get payment will not get the deposit / deposit return as payment.
		//bill type 4 is deposit
		$return_val = array();
		$query_str = "SELECT p.customer_no, SUM(p.amount) as amount " .
					"FROM payment p " .
					"WHERE p.pay_date <= '" . $pay_month . "' " .
					"AND p.processed = 0 " .
					"AND p.bill_type<>4 " .
					"GROUP BY p.customer_no " .
					"ORDER BY p.customer_no ";
		$query = $this->db->query($query_str);
		foreach ($query->result_array() as $row) {
			$return_val[$row['customer_no']] = $row['amount'];
		}
		/*
		$query_str = "	SELECT p.* FROM payment p 
						WHERE p.pay_date <= '" . $pay_month . "' 
						AND p.processed = 0 
						AND p.bill_type<>4 
						ORDER BY p.customer_no ";
		$query = $this->db->query($query_str);
		foreach ($query->result_array() as $row) {
			$return_val['payment_no'][] = $row['payment_no'];
		}
		*/
		return $return_val;
	}

	function get_payment_by_customer( $customer_no , $pay_month )
	{
		$return_val = array();
				
		$query_str = "	SELECT p.*
						FROM payment p 
						WHERE p.pay_date <= '".$pay_month."' 
						AND p.customer_no = '".$customer_no."'
						AND p.processed = 0 AND p.bill_type<>4 
						ORDER BY p.payment_no;";
		$query = $this->db->query($query_str);
		foreach ($query->result_array() as $row) 
		{
			$return_val[] = $row['payment_no'];
		}
		return $return_val;
	}

	function get_unprocessed_payment_by_customer( $customer_no , $pay_month = "")
	{
		$return_val = array();
		$where = "";

		if($pay_month != ""){
			$where .= " AND p.pay_date <= '".$pay_month."'";
		}

		$query_str = "	SELECT p.*
						FROM payment p 
						WHERE p.customer_no = '".$customer_no."'
						AND p.processed = 0 
						AND p.bill_type<>4 
						$where
						ORDER BY p.payment_no";
		$query = $this->db->query($query_str);
		$return_val = $query->result_array();
		
		return $return_val;
	}

	function get_total_unprocessed_payment_by_customer( $customer_no , $date )
	{
		$return_val = array();
		$query_str = "	SELECT SUM( p.amount ) AS amount
						FROM payment p 
						WHERE p.pay_date >= '".$date."' 
						AND p.customer_no = '".$customer_no."'
						AND p.processed = 0 
						AND p.bill_type<>4 
						GROUP BY p.customer_no ";
		$query = $this->db->query($query_str);
		$return_val = $query->row_array();
		
		return $return_val;
	}

	function get_processed_payment_by_customer( $customer_no , $pay_month, $exclude_bill_no )
	{
		$return_val = array();
		$query_str = "	SELECT p.*
						FROM payment p 
						WHERE p.pay_date BETWEEN '".date('Y-m-01', strtotime( $pay_month ) )."' AND '".date('Y-m-t', strtotime( $pay_month ) )."'
						AND p.customer_no = '".$customer_no."'
						AND p.processed = 1 
						AND ( p.bill_no IS NOT NULL AND p.bill_no != '".$exclude_bill_no."' )
						AND p.bill_type<>4 
						ORDER BY p.payment_no";
		$query = $this->db->query($query_str);
		$return_val = $query->result_array();
		
		return $return_val;
	}

	function get_last_bill($customer_no = '' , $no_void='1')
	{
		$qwhere = "";
		if( $no_void == 1 ){
			$qwhere .= " AND b.is_void = '0' ";
		}
		
		if (empty($customer_no)) {
			
			$str = "SELECT 	b.customer_no, b.bill_no, b.bill_date, b.bill_due_date, b.balance, 
							b.previous_balance , b.payment_received , b.charges, b.tax_charges , b.is_manual
					FROM bill b 
					INNER JOIN ( SELECT MAX(bill_no) as bill_no 
								 FROM bill WHERE is_void = 0 GROUP BY customer_no ) a ON a.bill_no = b.bill_no
					WHERE 1 = 1 ".$qwhere.";";
			$query = $this->db->query($str);


			if ($query->num_rows() > 0) {
				foreach ($query->result_array() as $row) {
					$return_val[$row['customer_no']] = array(
							'bill_no' => $row['bill_no'],
							'bill_date' => $row['bill_date'],
							'bill_due_date' => $row['bill_due_date'],
							'balance' => $row['balance'],
							'previous_balance' => $row['previous_balance'],
							'payment_received' => $row['payment_received'],
							'charges' => $row['charges'],
							'tax_charges' => $row['tax_charges'],
					);
				}
			}
		}
		else {
			$str = "SELECT b.bill_no, b.bill_date, b.bill_due_date, b.balance , 
					b.previous_balance , b.payment_received , b.charges, b.tax_charges , b.is_manual
					FROM bill b
					WHERE b.customer_no ='".$this->db->escape_str($customer_no)."' 
					".$qwhere." ORDER BY b.bill_no DESC LIMIT 1 ";
			
			$query = $this->db->query($str);
			$return_val = $query->row_array();
			if ( empty($return_val['balance']) ){
				$return_val['bill_no'] = '';
				$return_val['bill_date'] = '';
				$return_val['bill_due_date'] = '';
				$return_val['balance'] = 0;
				$return_val['previous_balance'] = 0;
				$return_val['payment_received'] = 0;
				$return_val['charges'] = 0;
				$return_val['tax_charges'] = 0;
			}
		}

		return $return_val;
	}

	function get_next_bills_paid_amount( $customer_no , $bill_no ){
		
		$return_val = array();
		
		$str = "SELECT SUM( b.payment_received ) AS payment_received
				FROM bill b 
				WHERE b.customer_no ='".$this->db->escape_str($customer_no)."' 
				AND b.bill_no > '".$this->db->escape_str($bill_no)."'
				GROUP BY b.customer_no ";
		$query = $this->db->query($str);
		$return_val = $query->row_array();
		
		return $return_val;
	}

	function get_next_balance_after_adjustment_amount( $customer_no , $bill_no ) {

		$return_val = array();

		//only get next month after the bill
		$str = "SELECT b.balance 
				FROM bill b  
				WHERE b.customer_no ='".$this->db->escape_str($customer_no)."'  
				AND b.bill_no > '".$this->db->escape_str($bill_no)."' 
				ORDER BY idx ASC limit 1";

		$query = $this->db->query($str);
		$return_val = $query->row_array();
		
		return $return_val;
	}

	function get_statement($by = 'bill', $num='',$filter_date='')
	{
		//3 big if else
		//by bill
		//by customer - cant find any instance of using this
		//by latest_bill

		//for QR
		$got_qr_lib = 0;
		$this->load->model('einvoice_api_model');
		if (file_exists(FCPATH.'phpqrcode/qrlib.php')) {
			include_once(FCPATH.'phpqrcode/qrlib.php'); 
			$got_qr_lib = 1;
		}

		$return_val = array();
		if ($by == 'bill')
		{
			//eg - view bill statement

			$table_bill_query	= ($num != '')? "bill_no = '$num'":"";
			$table_bill			= $this->get_table('bill','*', $table_bill_query);
			
			$sst_date = strtotime( "2018-09-01" );
			
			foreach ($table_bill as $key => $val){
				$return_val[$key]['bill'] = $table_bill[$key];
				
				$bill_date = strtotime( date( "Y-m-d", strtotime( $val['bill_date'] ) ) );
				
				if( $sst_date > $bill_date ){
					if ($val['bill_type'] == 'INV') {
						$return_val[$key]['bill']['doc_title'] = 'TAX INVOICE';
					} elseif ($val['bill_type'] == 'DN') {
						$return_val[$key]['bill']['doc_title'] = 'DEBIT NOTE';
					} else {
						$return_val[$key]['bill']['doc_title'] = 'CREDIT NOTE';
					}
					
				}else{
					if ($val['bill_type'] == 'INV') {
						$return_val[$key]['bill']['doc_title'] = 'INVOICE';
					} elseif ($val['bill_type'] == 'DN') {
						$return_val[$key]['bill']['doc_title'] = 'DEBIT NOTE';
					} else {
						$return_val[$key]['bill']['doc_title'] = 'CREDIT NOTE';
					}
					
				}
				
				$return_val[$key]['bill']['previous_balance'] = number_format( $val['previous_balance'], 2, ".", ",");
				$return_val[$key]['bill']['payment_received'] = number_format( $val['payment_received'], 2, ".", ",");
				$return_val[$key]['bill']['charges'] = number_format( $val['charges'], 2, ".", ",");
				$return_val[$key]['bill']['tax_charges'] = number_format( $val['tax_charges'], 2, ".", ",");
				$return_val[$key]['bill']['amount'] = number_format( $val['amount'], 2, ".", ",");
				$return_val[$key]['bill']['balance'] = number_format( $val['balance'], 2, ".", ",");

				//einvoice
				$einvoice_details = $this->get_einvoice_details($val['bill_no']);

				$return_val[$key]['bill']['einvoice_no'] = $einvoice_details['einvoice_no'] ?? '';
				$return_val[$key]['bill']['einvoice_submission_id'] = $einvoice_details['einvoice_submission_id'] ?? '';
				$return_val[$key]['bill']['einvoice_uuid'] = $einvoice_details['einvoice_uuid'] ?? '';
				$return_val[$key]['bill']['einvoice_status'] = $einvoice_details['einvoice_status'] ?? '';
				$return_val[$key]['bill']['einvoice_longid'] = $einvoice_details['einvoice_longid'] ?? '';

				$return_val[$key]['bill']['einvoice_qr'] = "";

				//qr
				if ($got_qr_lib == 1 && !empty($return_val[$key]['bill']['einvoice_longid']) && !empty($return_val[$key]['bill']['einvoice_uuid']) && $return_val[$key]['bill']['einvoice_status'] == 'S') {
					$qr_data = array();
					$qr_data['portal'] = $this->config->item('einvoice_portal_url');
					$qr_data['uuid'] = $return_val[$key]['bill']['einvoice_uuid'];
					$qr_data['longid'] = $return_val[$key]['bill']['einvoice_longid'];
					$qr_link = $this->einvoice_api_model->return_qr_link($qr_data);

					ob_start();
					QRCode::png($qr_link, null);
					$imageString = base64_encode( ob_get_contents() );
					ob_end_clean();

					$einvoice_html = '
					<img src="data:image/png;base64,'.$imageString.'" style="width:80px;" />
					';

					$return_val[$key]['bill']['einvoice_qr'] = $einvoice_html;

				}
				
				$customer_no			= $table_bill[$key]['customer_no'];
				$table_customer_query 	= ($customer_no != '')? "customer_no = '$customer_no'":"";

				$select_c_field =" c.customer_no, ";
				$select_c_field .=" c.name, ";
				$select_c_field .=" c.currency_code, ";
				//~ $select_c_field .=" c.reg_no, ";
				$select_c_field .=" c.gst_no, ";
				$select_c_field .=" c.pic_name, ";
				$select_c_field .=" c.status, ";
				$select_c_field .=" c.category, ";
				$select_c_field .=" c.building, ";
				$select_c_field .=" c.package, ";
				$select_c_field .=" c.package_name, ";
				$select_c_field .=" c.monthly_charge, ";
				$select_c_field .=" c.yearly_charge, ";
				$select_c_field .=" c.bill_cycle_month, ";
				$select_c_field .=" c.next_bill_date, ";
				$select_c_field .=" c.package_month, ";
				$select_c_field .=" c.stop_service_after, ";
				$select_c_field .=" c.contract_month, ";
				$select_c_field .=" c.login_username, ";
				$select_c_field .=" c.login_password, ";
				$select_c_field .=" c.login_domain, ";
				$select_c_field .=" bb.name AS building_name, ";
				$select_c_field .=" c.inst_unit_no, ";
				$select_c_field .=" c.inst_addr1, ";
				$select_c_field .=" c.inst_addr2, ";
				$select_c_field .=" c.inst_addr3, ";
				$select_c_field .=" c.inst_city, ";
				$select_c_field .=" c.inst_postcode, ";
				$select_c_field .=" c.inst_state, ";
				$select_c_field .=" p.ssm AS comp_ssm, ";
				$select_c_field .=" p.acc_name AS bill_name, ";
				$select_c_field .=" p.bill_addr_1 AS bill_addr1, ";
				$select_c_field .=" p.bill_addr_2 AS bill_addr2, ";
				$select_c_field .=" p.bill_addr_3 AS bill_addr3, ";
				$select_c_field .=" p.bill_city, ";
				$select_c_field .=" p.bill_postcode, ";
				$select_c_field .=" p.bill_state, ";
				$select_c_field .=" c.bill_by_post, ";
				$select_c_field .=" c.bill_by_email, ";
				$select_c_field .=" c.tel_num, ";
				$select_c_field .=" c.fax_num, ";
				$select_c_field .=" c.mobile_num, ";
				$select_c_field .=" p.pic_email_1 AS email_1, ";
				$select_c_field .=" p.pic_email_2 AS email_2, ";
				$select_c_field .=" c.nric, ";
				$select_c_field .=" c.passport, ";
				$select_c_field .=" c.date_of_birth, ";
				$select_c_field .=" c.gender, ";
				$select_c_field .=" c.race, ";
				$select_c_field .=" c.marital_status, ";
				$select_c_field .=" c.household, ";
				$select_c_field .=" c.ownership_type, ";
				$select_c_field .=" c.no_of_employee, ";
				$select_c_field .=" c.no_of_branches, ";
				$select_c_field .=" c.signup_date, ";
				$select_c_field .=" c.activated_date, ";
				$select_c_field .=" c.suspended_date, ";
				$select_c_field .=" c.terminated_date, ";
				$select_c_field .=" c.last_email_sent, ";
				$select_c_field .=" c.last_file_gen, ";
				$select_c_field .=" c.dealer, ";
				$select_c_field .=" c.remark, ";
				$select_c_field .=" c.payment_term, ";
				$select_c_field .=" c.flag_email, ";
				$select_c_field .=" c.project_name, ";
				$select_c_field .=" c.unit_name, ";
				$select_c_field .=" c.created_by, ";
				$select_c_field .=" c.created_date, ";
				$select_c_field .=" c.modified_by, ";
				$select_c_field .=" c.modified_date ";

				//$table_customer 		= $this->get_table('customer as c',' '.$select_c_field.' ',$table_customer_query);

				$query = $this->db->query("
					SELECT $select_c_field
					FROM customer c 
					INNER JOIN profile p ON p.acc_id = c.profile_id 
					LEFT JOIN building bb ON (c.building = bb.building_no) WHERE c.customer_no = ? "
					, array($customer_no));

				$table_customer 		= $query->result_array();

				$return_val[$key]['customer'] = (empty($table_customer[0]))?[]:$table_customer[0];
				
				/*$str =	"	SELECT SUM( IFNULL(deposit,0) ) AS deposit FROM (
								(
									SELECT SUM( deposit ) AS deposit
									FROM customer_deposit 
									WHERE customer_no = '".$customer_no."'
									AND deposit_date <= '".$val['bill_date']."' 
								)
								UNION	
								(
									SELECT	SUM( CASE WHEN ( ba.adjust_type = 'cr' ) THEN (-1 * ba.amount ) 
											ELSE ba.amount END ) AS deposit
									FROM bill_adjustment ba 
									INNER JOIN customer c ON c.customer_no = ba.customer_no 
									LEFT JOIN building b ON b.building_no = c.building 
									LEFT JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
									LEFT JOIN bill bb ON ( ba.bill_no = bb.bill_no ) 
									WHERE ( ba.bill_type IN ( '4' , '11' , '14' ) ) 
									AND c.customer_no = '".$customer_no."'
									AND ba.tranx_date <= '".$val['bill_date']."' 
									AND bb.is_void = 0 
								)			
								UNION 
								(
									SELECT  SUM( p.amount ) AS deposit 
									FROM payment p
									INNER JOIN sys_bill_type sbt ON p.bill_type = sbt.bill_type_id 
									WHERE p.customer_no = '".$customer_no."' 
									AND p.pay_date <= '".$val['bill_date']."' 
									AND sbt.is_debit = 1 
									AND p.bill_type IN ( '4', '11', '14' ) 
								)

							) z ";*/

					$str =	"	SELECT ( SUM(IFNULL(deposit1,0)) + SUM(IFNULL(deposit2,0)) + SUM(IFNULL(deposit3,0)) ) AS deposit 
								FROM (
								(
									SELECT SUM( deposit ) AS deposit1, 0 AS deposit2, 0 AS deposit3 
									FROM customer_deposit 
									WHERE customer_no = '".$customer_no."'
									AND deposit_date <= '".$val['bill_date']."' 
								)
								UNION	
								(
									SELECT	0 AS deposit1, SUM( CASE WHEN ( ba.adjust_type = 'cr' ) THEN (-1 * ba.amount ) 
											ELSE ba.amount END ) AS deposit2, 0 AS deposit3 
									FROM bill_adjustment ba 
									INNER JOIN customer c ON c.customer_no = ba.customer_no 
									LEFT JOIN building b ON b.building_no = c.building 
									LEFT JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
									LEFT JOIN bill bb ON ( ba.bill_no = bb.bill_no ) 
									WHERE ( ba.bill_type IN ( '4' , '11' , '14' ) ) 
									AND c.customer_no = '".$customer_no."'
									AND ba.tranx_date <= '".$val['bill_date']."' 
									AND bb.is_void = 0 
								)			
								UNION 
								(
									SELECT  0 AS deposit1, 0 AS deposit2, SUM( p.amount ) AS deposit3 
									FROM payment p
									INNER JOIN sys_bill_type sbt ON p.bill_type = sbt.bill_type_id 
									WHERE p.customer_no = '".$customer_no."' 
									AND p.pay_date <= '".$val['bill_date']."' 
									AND sbt.is_debit = 1 
									AND p.bill_type IN ( '4', '11', '14' ) 
								)

							) z ";
				
				$query = $this->db->query($str);
				
				if( $query->num_rows() > 0 ){
					$row = $query->row_array();
					$return_val[$key]['customer']['deposit'] = $row['deposit'];
				}else{
					$return_val[$key]['customer']['deposit'] = 0 ;
				}
				
				$bill_no			= $table_bill[$key]['bill_no'];
				$bill_detail_query 	= ($bill_no != '')? "bill_no = '$bill_no'":"";
				$bill_detail 		= $this->get_table('bill_detail','*, amount as item_amount, remark as item_remark',$bill_detail_query);
				
				/*
				$inst_state = $this->get_table('sys_state', '*', 'state_code = "'.$return_val[$key]['customer']['inst_state'].'" ') ;
				$return_val[$key]['inst_state'] = $inst_state[0]['name'];
				
				$bill_state = $this->get_table('sys_state', '*', 'state_code = "'.$return_val[$key]['customer']['bill_state'].'" ') ;
				$return_val[$key]['bill_state'] = $bill_state[0]['name'];
				*/
				
				$return_val[$key]['bill_detail'] = (empty($bill_detail))?'':$bill_detail;
				
				if( !empty( $return_val[$key]['bill_detail'] ) ){
				
				foreach( $return_val[$key]['bill_detail'] AS $keyz => $valz ){
					$return_val[$key]['bill_detail'][$keyz]['tax_percent'] = number_format( $valz['tax_percent'], 2, ".", ","); 
					$return_val[$key]['bill_detail'][$keyz]['amount'] = number_format( $valz['amount'], 2, ".", ","); 
					$return_val[$key]['bill_detail'][$keyz]['tax_amount'] = number_format( $valz['tax_amount'], 2, ".", ","); 
					$return_val[$key]['bill_detail'][$keyz]['item_amount'] = number_format( $valz['item_amount'], 2, ".", ","); 
				}
				
				}

				//bill call - put !empty the query to prevent accidently querying every record
				if (!empty($bill_detail_query)) {
					$bill_call 		= $this->get_table('bill_call','*',$bill_detail_query);

					$return_val[$key]['bill_call'] = (empty($bill_call))?'':$bill_call;

					if( !empty( $return_val[$key]['bill_call'] ) ){

						//do number formatting

						foreach( $return_val[$key]['bill_call'] AS $keyc => $valc ){
							$return_val[$key]['bill_call'][$keyc]['amount'] = number_format( $valc['tax_percent'], 2, ".", ","); 
							$return_val[$key]['bill_call'][$keyc]['tax_amount'] = number_format( $valc['amount'], 2, ".", ","); 
							$return_val[$key]['bill_call'][$keyc]['tax_percent'] = number_format( $valc['tax_amount'], 2, ".", ","); 
							$return_val[$key]['bill_call'][$keyc]['total'] = number_format( $valc['total'], 2, ".", ","); 
						}

					}
				}
				
			}
		}
		elseif ($by == 'customer')
		{
			$table_customer_query	= ($num != '')? "customer_no = '$num'":"";

			$select_c_field =" c.customer_no, ";
			$select_c_field .=" c.name, ";
			$select_c_field .=" c.currency_code, ";
			//~ $select_c_field .=" c.reg_no, ";
			$select_c_field .=" c.gst_no, ";
			$select_c_field .=" c.pic_name, ";
			$select_c_field .=" c.status, ";
			$select_c_field .=" c.category, ";
			$select_c_field .=" c.building, ";
			$select_c_field .=" c.package, ";
			$select_c_field .=" c.package_name, ";
			$select_c_field .=" c.monthly_charge, ";
			$select_c_field .=" c.yearly_charge, ";
			$select_c_field .=" c.bill_cycle_month, ";
			$select_c_field .=" c.next_bill_date, ";
			$select_c_field .=" c.package_month, ";
			$select_c_field .=" c.stop_service_after, ";
			$select_c_field .=" c.contract_month, ";
			$select_c_field .=" c.login_username, ";
			$select_c_field .=" c.login_password, ";
			$select_c_field .=" c.login_domain, ";
			$select_c_field .=" bb.name AS building_name, ";
			$select_c_field .=" c.inst_unit_no, ";
			$select_c_field .=" c.inst_addr1, ";
			$select_c_field .=" c.inst_addr2, ";
			$select_c_field .=" c.inst_addr3, ";
			$select_c_field .=" c.inst_city, ";
			$select_c_field .=" c.inst_postcode, ";
			$select_c_field .=" c.inst_state, ";
			$select_c_field .=" p.ssm AS comp_ssm, ";
			$select_c_field .=" p.acc_name AS bill_name, ";
			$select_c_field .=" p.bill_addr_1 AS bill_addr1, ";
			$select_c_field .=" p.bill_addr_2 AS bill_addr2, ";
			$select_c_field .=" p.bill_addr_3 AS bill_addr3, ";
			$select_c_field .=" p.bill_city, ";
			$select_c_field .=" p.bill_postcode, ";
			$select_c_field .=" p.bill_state, ";
			$select_c_field .=" c.bill_by_post, ";
			$select_c_field .=" c.bill_by_email, ";
			$select_c_field .=" c.tel_num, ";
			$select_c_field .=" c.fax_num, ";
			$select_c_field .=" c.mobile_num, ";
			$select_c_field .=" c.email_1, ";
			$select_c_field .=" c.email_2, ";
			$select_c_field .=" c.nric, ";
			$select_c_field .=" c.passport, ";
			$select_c_field .=" c.date_of_birth, ";
			$select_c_field .=" c.gender, ";
			$select_c_field .=" c.race, ";
			$select_c_field .=" c.marital_status, ";
			$select_c_field .=" c.household, ";
			$select_c_field .=" c.ownership_type, ";
			$select_c_field .=" c.no_of_employee, ";
			$select_c_field .=" c.no_of_branches, ";
			$select_c_field .=" c.signup_date, ";
			$select_c_field .=" c.activated_date, ";
			$select_c_field .=" c.suspended_date, ";
			$select_c_field .=" c.terminated_date, ";
			$select_c_field .=" c.last_email_sent, ";
			$select_c_field .=" c.last_file_gen, ";
			$select_c_field .=" c.dealer, ";
			$select_c_field .=" c.remark, ";
			$select_c_field .=" c.payment_term, ";
			$select_c_field .=" c.flag_email, ";
			$select_c_field .=" c.created_by, ";
			$select_c_field .=" c.created_date, ";
			$select_c_field .=" c.modified_by, ";
			$select_c_field .=" c.modified_date ";

			//$table_customer			= $this->get_table('customer as c',' '.$select_c_field.' ', $table_customer_query);

			$query = $this->db->query("
				SELECT $select_c_field
				FROM customer c 
				INNER JOIN profile p ON p.acc_id = c.profile_id 
				LEFT JOIN building bb ON (c.building = bb.building_no) WHERE c.customer_no = ? "
				, array($num));

			$table_customer 		= $query->result_array();

			foreach ($table_customer as $key => $val)
			{
				$customer_no		= $table_customer[$key]['customer_no'];
				$table_bill_query	= ($customer_no != '')? "customer_no = '$customer_no'":"";
				$table_bill			= $this->get_table('bill','*', $table_bill_query);

				foreach ($table_bill as $table_bill_key => $table_bill_val)
				{
					$return_val[$table_bill_key]['customer'] 	= $table_customer[$key];
										
					$return_val[$table_bill_key]['bill']		= (empty($table_bill[$table_bill_key]))?'':$table_bill[$table_bill_key];

					$return_val[$table_bill_key]['bill']['doc_title'] = [ 'INV' => 'INVOICE', 'DN'  => 'DEBIT NOTE', 'CN'  => 'CREDIT NOTE' ][$table_bill_val['bill_type']] ?? 'INVOICE';
					$return_val[$table_bill_key]['bill']['previous_balance'] = number_format( $table_bill_val['previous_balance'], 2, ".", ",");
					$return_val[$table_bill_key]['bill']['payment_received'] = number_format( $table_bill_val['payment_received'], 2, ".", ",");
					$return_val[$table_bill_key]['bill']['charges'] = number_format( $table_bill_val['charges'], 2, ".", ",");
					$return_val[$table_bill_key]['bill']['tax_charges'] = number_format( $table_bill_val['tax_charges'], 2, ".", ",");
					$return_val[$table_bill_key]['bill']['amount'] = number_format( $table_bill_val['amount'], 2, ".", ",");
					$return_val[$table_bill_key]['bill']['balance'] = number_format( $table_bill_val['balance'], 2, ".", ",");

					$bill_no			= $table_bill[$table_bill_key]['bill_no'];
					$bill_detail_query 	= ($bill_no != '')? "bill_no = '$bill_no'":"";
					$bill_detail 		= $this->get_table('bill_detail','*, amount as item_amount, remark as item_remark',$bill_detail_query);
					$return_val[$table_bill_key]['bill_detail'] = (empty($bill_detail))?'':$bill_detail;
					
					foreach( $return_val[$table_bill_key]['bill_detail'] AS $keyz => $valz ){
						$return_val[$table_bill_key]['bill_detail'][$keyz]['tax_percent'] = number_format( $valz['tax_percent'], 2, ".", ","); 
						$return_val[$table_bill_key]['bill_detail'][$keyz]['amount'] = number_format( $valz['amount'], 2, ".", ","); 
						$return_val[$table_bill_key]['bill_detail'][$keyz]['tax_amount'] = number_format( $valz['tax_amount'], 2, ".", ","); 
						$return_val[$table_bill_key]['bill_detail'][$keyz]['item_amount'] = number_format( $valz['item_amount'], 2, ".", ","); 
					}
					
				}
			}
		}
		elseif ($by == 'latest_bill_by')
		{
			$return_val = false;
			if(is_array($num))
			{
				$query_in = implode (", ", $num);
				$table_customer_query	= ($query_in != '')? "customer_no IN( $query_in )":"";
				$table_customer			= $this->get_table('customer','*', $table_customer_query);

				$table_bill_query	= ($query_in != '')? "customer_no IN( $query_in )":"";
				$order_by			="bill_date desc";
				$limit				='1';
				$table_bill			= $this->get_table('bill','*', $table_bill_query,$order_by,$limit);

				//field for customer table
				$select_c_field =" c.customer_no, ";
				$select_c_field .=" c.name, ";
				$select_c_field .=" c.currency_code, ";
				//~ $select_c_field .=" c.reg_no, ";
				$select_c_field .=" c.gst_no, ";
				$select_c_field .=" c.pic_name, ";
				$select_c_field .=" c.status, ";
				$select_c_field .=" c.category, ";
				$select_c_field .=" c.building, ";
				$select_c_field .=" c.package, ";
				$select_c_field .=" c.package_name, ";
				$select_c_field .=" c.monthly_charge, ";
				$select_c_field .=" c.yearly_charge, ";
				$select_c_field .=" c.bill_cycle_month, ";
				$select_c_field .=" c.next_bill_date, ";
				$select_c_field .=" c.package_month, ";
				$select_c_field .=" c.stop_service_after, ";
				$select_c_field .=" c.contract_month, ";
				$select_c_field .=" c.login_username, ";
				$select_c_field .=" c.login_password, ";
				$select_c_field .=" c.login_domain, ";
				$select_c_field .=" bb.name AS building_name, ";
				$select_c_field .=" c.inst_unit_no, ";
				$select_c_field .=" c.inst_addr1, ";
				$select_c_field .=" c.inst_addr2, ";
				$select_c_field .=" c.inst_addr3, ";
				$select_c_field .=" c.inst_city, ";
				$select_c_field .=" c.inst_postcode, ";
				$select_c_field .=" c.inst_state, ";
				$select_c_field .=" p.ssm AS comp_ssm, ";
				$select_c_field .=" p.acc_name AS bill_name, ";
				$select_c_field .=" p.bill_addr_1 AS bill_addr1, ";
				$select_c_field .=" p.bill_addr_2 AS bill_addr2, ";
				$select_c_field .=" p.bill_addr_3 AS bill_addr3, ";
				$select_c_field .=" p.bill_city, ";
				$select_c_field .=" p.bill_postcode, ";
				$select_c_field .=" p.bill_state, ";
				$select_c_field .=" c.bill_by_post, ";
				$select_c_field .=" c.bill_by_email, ";
				$select_c_field .=" c.tel_num, ";
				$select_c_field .=" c.fax_num, ";
				$select_c_field .=" c.mobile_num, ";
				$select_c_field .=" p.pic_email_1 AS email_1, ";
				$select_c_field .=" p.pic_email_2 AS email_2, ";
				$select_c_field .=" c.nric, ";
				$select_c_field .=" c.passport, ";
				$select_c_field .=" c.date_of_birth, ";
				$select_c_field .=" c.gender, ";
				$select_c_field .=" c.race, ";
				$select_c_field .=" c.marital_status, ";
				$select_c_field .=" c.household, ";
				$select_c_field .=" c.ownership_type, ";
				$select_c_field .=" c.no_of_employee, ";
				$select_c_field .=" c.no_of_branches, ";
				$select_c_field .=" c.signup_date, ";
				$select_c_field .=" c.activated_date, ";
				$select_c_field .=" c.suspended_date, ";
				$select_c_field .=" c.terminated_date, ";
				$select_c_field .=" c.last_email_sent, ";
				$select_c_field .=" c.last_file_gen, ";
				$select_c_field .=" c.dealer, ";
				$select_c_field .=" c.remark, ";
				$select_c_field .=" c.payment_term, ";
				$select_c_field .=" c.flag_email, ";
				$select_c_field .=" c.project_name, ";
				$select_c_field .=" c.unit_name, ";
				$select_c_field .=" c.created_by, ";
				$select_c_field .=" c.created_date, ";
				$select_c_field .=" c.modified_by, ";
				$select_c_field .=" c.modified_date ";
				//field for bill table
				$select_b_field =" b.idx as b_idx, ";
				$select_b_field .=" b.customer_no, ";
				$select_b_field .=" b.bill_no, ";
				$select_b_field .=" b.bill_type AS doc_type, ";
				$select_b_field .=" b.bill_date, ";
				$select_b_field .=" b.bill_due_date, ";
				$select_b_field .=" b.bill_period, ";
				$select_b_field .=" b.previous_balance, ";
				$select_b_field .=" b.payment_received, ";
				$select_b_field .=" b.charges, ";
				$select_b_field .=" b.tax_charges, ";
				$select_b_field .=" b.amount, ";
				$select_b_field .=" b.balance, ";
				$select_b_field .=" b.last_printed_by, ";
				$select_b_field .=" b.last_printed_date ";
				//field for bill detail table
				$select_bd_field =" bd.idx as bd_idx, ";
				$select_bd_field .=" bd.bill_no, ";
				$select_bd_field .=" bd.tranx_date, ";
				$select_bd_field .=" bd.bill_type, ";
				$select_bd_field .=" bd.tax_code, ";
				//$select_bd_field .=" bd.amount, ";
				$select_bd_field .=" bd.remark, ";
				$select_bd_field .=" bd.amount as item_amount, ";
				$select_bd_field .=" bd.remark as item_remark ";
				//field for bill_call table
				/*$select_bc_field = " bc.idx AS bc_idx, ";
				$select_bc_field .=" bc.bill_no, ";
				$select_bc_field .=" bc.tranx_date, ";
				$select_bc_field .=" bc.start_time, ";
				$select_bc_field .=" bc.answer_time, ";
				$select_bc_field .=" bc.end_time, ";
				$select_bc_field .=" bc.called, ";
				$select_bc_field .=" bc.call_time, ";
				$select_bc_field .=" bc.talk_time, ";
				$select_bc_field .=" bc.amount, ";
				$select_bc_field .=" bc.tax_amount, ";
				$select_bc_field .=" bc.tax_code, ";
				$select_bc_field .=" bc.tax_percent, ";
				$select_bc_field .=" bc.total, ";
				$select_bc_field .=" bc.remark AS call_remark ";*/

				$fields  =$select_c_field;
				$fields .= ','.$select_b_field;
				$fields .= ','.$select_bd_field;
				//$fields .= ','.$select_bc_field;

				$query_where = "";
				if( $filter_date != '' ){
					$query_where .= " AND ( bill_date BETWEEN '".date("Y-m-01",strtotime($filter_date))."'
									  AND '".date("Y-m-d",strtotime($filter_date))."' ) " ;
				}

				$query = $this->db->query("
					SELECT $fields
					FROM customer c 
					INNER JOIN profile p ON p.acc_id = c.profile_id 
					INNER JOIN bill b ON c.customer_no = b.customer_no
					INNER JOIN bill_detail bd ON b.bill_no=bd.bill_no
					INNER JOIN (
						SELECT customer_no,MAX(bill_no) as bill_no
						FROM (`bill`)
						WHERE `customer_no` IN( $query_in ) AND is_void = 0 $query_where
						GROUP BY customer_no
					 ) a ON b.bill_no = a.bill_no 
					LEFT JOIN building bb ON (c.building = bb.building_no) "
					);

				$query_arr 		= $query->result_array();
				$statement_arr 	= array();

				//weird disjointing way to collect data
				$bill_call_done = array();
				$bill_call_arr = array();
				foreach($query_arr as $q_key =>  $q_val)
				{
					$bill_detail_arr[$q_val['customer_no']][$q_key]['bd_idx']		= $q_val['bd_idx'];
					$bill_detail_arr[$q_val['customer_no']][$q_key]['bill_no'] 		= $q_val['bill_no'];
					$bill_detail_arr[$q_val['customer_no']][$q_key]['tranx_date'] 	= $q_val['tranx_date'];
					$bill_detail_arr[$q_val['customer_no']][$q_key]['bill_type'] 	= $q_val['bill_type'];
					$bill_detail_arr[$q_val['customer_no']][$q_key]['tax_code'] 	= $q_val['tax_code'];
					$bill_detail_arr[$q_val['customer_no']][$q_key]['amount'] 		= number_format( $q_val['amount']  , 2 , "." , "," );
					$bill_detail_arr[$q_val['customer_no']][$q_key]['remark'] 		= $q_val['remark'];
					$bill_detail_arr[$q_val['customer_no']][$q_key]['item_amount'] 	= number_format( $q_val['item_amount'] , 2 , "." , "," );
					$bill_detail_arr[$q_val['customer_no']][$q_key]['item_remark']	= $q_val['item_remark'];

					$bill_arr[$q_val['customer_no']][0]['b_idx']				= $q_val['b_idx'];
					$bill_arr[$q_val['customer_no']][0]['customer_no']			= $q_val['customer_no'];
					$bill_arr[$q_val['customer_no']][0]['bill_no']				= $q_val['bill_no'];
					$bill_arr[$q_val['customer_no']][0]['doc_title'] 			= [ 'INV' => 'INVOICE', 'DN'  => 'DEBIT NOTE', 'CN'  => 'CREDIT NOTE' ][$q_val['doc_type']] ?? 'INVOICE';
					$bill_arr[$q_val['customer_no']][0]['bill_date']			= $q_val['bill_date'];
					$bill_arr[$q_val['customer_no']][0]['bill_due_date']		= $q_val['bill_due_date'];
					$bill_arr[$q_val['customer_no']][0]['bill_period']			= $q_val['bill_period'];
					$bill_arr[$q_val['customer_no']][0]['previous_balance']		= number_format( $q_val['previous_balance'] , 2 , "." , "," );
					$bill_arr[$q_val['customer_no']][0]['payment_received']		= number_format( $q_val['payment_received'] , 2 , "." , "," );
					$bill_arr[$q_val['customer_no']][0]['charges']				= number_format( $q_val['charges'] , 2 , "." , "," ) ;
					$bill_arr[$q_val['customer_no']][0]['tax_charges']			= number_format( $q_val['tax_charges']  , 2 , "." , "," );
					$bill_arr[$q_val['customer_no']][0]['amount']				= number_format( $q_val['amount']  , 2 , "." , "," );
					$bill_arr[$q_val['customer_no']][0]['balance']				= number_format( $q_val['balance']  , 2 , "." , "," );
					$bill_arr[$q_val['customer_no']][0]['last_printed_by']		= $q_val['last_printed_by'];
					$bill_arr[$q_val['customer_no']][0]['last_printed_date']	= $q_val['last_printed_date'];

					//einvoice
					$einvoice_details = $this->get_einvoice_details($q_val['bill_no']);

					$bill_arr[$q_val['customer_no']][0]['einvoice_no'] = $einvoice_details['einvoice_no'] ?? '';
					$bill_arr[$q_val['customer_no']][0]['einvoice_submission_id'] = $einvoice_details['einvoice_submission_id'] ?? '';
					$bill_arr[$q_val['customer_no']][0]['einvoice_uuid'] = $einvoice_details['einvoice_uuid'] ?? '';
					$bill_arr[$q_val['customer_no']][0]['einvoice_status'] = $einvoice_details['einvoice_status'] ?? '';
					$bill_arr[$q_val['customer_no']][0]['einvoice_longid'] = $einvoice_details['einvoice_longid'] ?? '';

					$bill_arr[$q_val['customer_no']][0]['einvoice_qr'] = "";

					//qr
					if ($got_qr_lib == 1 && !empty($bill_arr[$q_val['customer_no']][0]['einvoice_longid']) && !empty($bill_arr[$q_val['customer_no']][0]['einvoice_uuid']) && $bill_arr[$q_val['customer_no']][0]['einvoice_status'] == 'S') {
						$qr_data = array();
						$qr_data['portal'] = $this->config->item('einvoice_portal_url');
						$qr_data['uuid'] = $bill_arr[$q_val['customer_no']][0]['einvoice_uuid'];
						$qr_data['longid'] = $bill_arr[$q_val['customer_no']][0]['einvoice_longid'];
						$qr_link = $this->einvoice_api_model->return_qr_link($qr_data);

						ob_start();
						QRCode::png($qr_link, null);
						$imageString = base64_encode( ob_get_contents() );
						ob_end_clean();

						$einvoice_html = '
						<img src="data:image/png;base64,'.$imageString.'" style="width:80px;" />
						';

						$bill_arr[$q_val['customer_no']][0]['einvoice_qr'] = $einvoice_html;

					}

					$cust_arr[$q_val['customer_no']][0]['customer_no']			= $q_val['customer_no'];
					$cust_arr[$q_val['customer_no']][0]['name']					= $q_val['name'];
					$cust_arr[$q_val['customer_no']][0]['currency_code']		= $q_val['currency_code'];
					//~ $cust_arr[$q_val['customer_no']][0]['reg_no']			= $q_val['reg_no'];
					$cust_arr[$q_val['customer_no']][0]['gst_no']				= $q_val['gst_no'];
					$cust_arr[$q_val['customer_no']][0]['pic_name']				= $q_val['pic_name'];
					$cust_arr[$q_val['customer_no']][0]['status']				= $q_val['status'];
					$cust_arr[$q_val['customer_no']][0]['category']				= $q_val['category'];
					$cust_arr[$q_val['customer_no']][0]['building']				= $q_val['building'];
					$cust_arr[$q_val['customer_no']][0]['package']				= $q_val['package'];
					$cust_arr[$q_val['customer_no']][0]['package_name']			= $q_val['package_name'];
					$cust_arr[$q_val['customer_no']][0]['monthly_charge']		= number_format( $q_val['monthly_charge']  , 2 , "." , "," );
					$cust_arr[$q_val['customer_no']][0]['yearly_charge']		= number_format( $q_val['yearly_charge']  , 2 , "." , "," );
					$cust_arr[$q_val['customer_no']][0]['bill_cycle_month']		= $q_val['bill_cycle_month'];
					$cust_arr[$q_val['customer_no']][0]['next_bill_date']		= $q_val['next_bill_date'];
					$cust_arr[$q_val['customer_no']][0]['package_month']		= $q_val['package_month'];
					$cust_arr[$q_val['customer_no']][0]['stop_service_after']	= $q_val['stop_service_after'];
					$cust_arr[$q_val['customer_no']][0]['contract_month']		= $q_val['contract_month'];
					$cust_arr[$q_val['customer_no']][0]['login_username']		= $q_val['login_username'];
					$cust_arr[$q_val['customer_no']][0]['login_password']		= $q_val['login_password'];
					$cust_arr[$q_val['customer_no']][0]['login_domain']			= $q_val['login_domain'];
					$cust_arr[$q_val['customer_no']][0]['building_name']		= $q_val['building_name'];
					$cust_arr[$q_val['customer_no']][0]['comp_ssm']				= $q_val['comp_ssm'];
					$cust_arr[$q_val['customer_no']][0]['inst_unit_no']			= $q_val['inst_unit_no'];
					$cust_arr[$q_val['customer_no']][0]['inst_addr1']			= $q_val['inst_addr1'];
					$cust_arr[$q_val['customer_no']][0]['inst_addr2']			= $q_val['inst_addr2'];
					$cust_arr[$q_val['customer_no']][0]['inst_addr3']			= $q_val['inst_addr3'];
					$cust_arr[$q_val['customer_no']][0]['inst_city']			= $q_val['inst_city'];
					$cust_arr[$q_val['customer_no']][0]['inst_postcode']		= $q_val['inst_postcode'];
					$cust_arr[$q_val['customer_no']][0]['inst_state']			= $q_val['inst_state'];
					$cust_arr[$q_val['customer_no']][0]['bill_name']			= $q_val['bill_name'];
					$cust_arr[$q_val['customer_no']][0]['bill_addr1']			= $q_val['bill_addr1'];
					$cust_arr[$q_val['customer_no']][0]['bill_addr2']			= $q_val['bill_addr2'];
					$cust_arr[$q_val['customer_no']][0]['bill_addr3']			= $q_val['bill_addr3'];
					$cust_arr[$q_val['customer_no']][0]['bill_city']			= $q_val['bill_city'];
					$cust_arr[$q_val['customer_no']][0]['bill_postcode']		= $q_val['bill_postcode'];
					$cust_arr[$q_val['customer_no']][0]['bill_state']			= $q_val['bill_state'];
					$cust_arr[$q_val['customer_no']][0]['bill_by_post']			= $q_val['bill_by_post'];
					$cust_arr[$q_val['customer_no']][0]['bill_by_email']		= $q_val['bill_by_email'];
					$cust_arr[$q_val['customer_no']][0]['tel_num']				= $q_val['tel_num'];
					$cust_arr[$q_val['customer_no']][0]['fax_num']				= $q_val['fax_num'];
					$cust_arr[$q_val['customer_no']][0]['mobile_num']			= $q_val['mobile_num'];
					$cust_arr[$q_val['customer_no']][0]['email_1']				= $q_val['email_1'];
					$cust_arr[$q_val['customer_no']][0]['email_2']				= $q_val['email_2'];
					$cust_arr[$q_val['customer_no']][0]['nric']					= $q_val['nric'];
					$cust_arr[$q_val['customer_no']][0]['passport']				= $q_val['passport'];
					$cust_arr[$q_val['customer_no']][0]['date_of_birth']		= $q_val['date_of_birth'];
					$cust_arr[$q_val['customer_no']][0]['gender']				= $q_val['gender'];
					$cust_arr[$q_val['customer_no']][0]['race']					= $q_val['race'];
					$cust_arr[$q_val['customer_no']][0]['marital_status']		= $q_val['marital_status'];
					$cust_arr[$q_val['customer_no']][0]['household']			= $q_val['household'];
					$cust_arr[$q_val['customer_no']][0]['ownership_type']		= $q_val['ownership_type'];
					$cust_arr[$q_val['customer_no']][0]['no_of_employee']		= $q_val['no_of_employee'];
					$cust_arr[$q_val['customer_no']][0]['no_of_branches']		= $q_val['no_of_branches'];
					$cust_arr[$q_val['customer_no']][0]['signup_date']			= $q_val['signup_date'];
					$cust_arr[$q_val['customer_no']][0]['activated_date']		= $q_val['activated_date'];
					$cust_arr[$q_val['customer_no']][0]['suspended_date']		= $q_val['suspended_date'];
					$cust_arr[$q_val['customer_no']][0]['terminated_date']		= $q_val['terminated_date'];
					$cust_arr[$q_val['customer_no']][0]['last_email_sent']		= $q_val['last_email_sent'];
					$cust_arr[$q_val['customer_no']][0]['last_file_gen']		= $q_val['last_file_gen'];
					$cust_arr[$q_val['customer_no']][0]['dealer']				= $q_val['dealer'];
					$cust_arr[$q_val['customer_no']][0]['remark']				= $q_val['remark'];
					$cust_arr[$q_val['customer_no']][0]['payment_term']			= $q_val['payment_term'];
					$cust_arr[$q_val['customer_no']][0]['flag_email']			= $q_val['flag_email'];
					$cust_arr[$q_val['customer_no']][0]['project_name']			= $q_val['project_name'];
					$cust_arr[$q_val['customer_no']][0]['unit_name']			= $q_val['unit_name'];
					$cust_arr[$q_val['customer_no']][0]['created_by']			= $q_val['created_by'];
					$cust_arr[$q_val['customer_no']][0]['created_date']			= $q_val['created_date'];
					$cust_arr[$q_val['customer_no']][0]['modified_by']			= $q_val['modified_by'];
					$cust_arr[$q_val['customer_no']][0]['modified_date']		= $q_val['modified_date'];
										
					$str =	"	SELECT SUM( IFNULL(deposit,0) ) AS deposit FROM (
								(
									SELECT SUM( deposit ) AS deposit
									FROM customer_deposit 
									WHERE customer_no = '".$q_val['customer_no']."'
									AND deposit_date <= '".$q_val['bill_date']."' 
								)
								UNION	
								(
									SELECT	SUM( CASE WHEN ( ba.adjust_type = 'cr' ) THEN (-1 * ba.amount ) 
											ELSE ba.amount END ) AS deposit
									FROM bill_adjustment ba 
									INNER JOIN customer c ON c.customer_no = ba.customer_no 
									LEFT JOIN building b ON b.building_no = c.building 
									LEFT JOIN sys_bill_type sbt ON ba.bill_type = sbt.bill_type_id 
									WHERE ( ba.bill_type IN ( '4' , '11' , '14' ) ) 
									AND c.customer_no = '".$q_val['customer_no']."'
									AND ba.tranx_date <= '".$q_val['bill_date']."'  
								)			
								UNION 
								(
									SELECT  SUM( p.amount ) AS deposit 
									FROM payment p
									INNER JOIN sys_bill_type sbt ON p.bill_type = sbt.bill_type_id 
									WHERE p.customer_no = '".$q_val['customer_no']."' 
									AND p.pay_date <= '".$q_val['bill_date']."' 
									AND sbt.is_debit = 1 
									AND p.bill_type IN ( '4', '11', '14' ) 
								)

							) z ";
					
					$query = $this->db->query($str);					
					if( $query->num_rows() > 0 ){
						$row = $query->row_array();
						$cust_arr[$q_val['customer_no']][0]['deposit'] = $row['deposit'] ;
					}else{
						$cust_arr[$q_val['customer_no']][0]['deposit'] = 0;
					}
					
					$row = $query->row_array();

					//bill call - not a very efficient way but very difficult to join with 2 multi tables, detail and call
					if (!isset($bill_call_done[$q_val['customer_no']])) {
						$bill_call_arr[$q_val['customer_no']] = array();

						//the query
						$bill_call_query 	= ($q_val['bill_no'] != '')? "bill_no = '".$q_val['bill_no']."'":"";
						if (!empty($bill_call_query)) {
							$bill_call 		= $this->get_table('bill_call','*',$bill_call_query);

							$bill_call_arr[$q_val['customer_no']] = (empty($bill_call))?'':$bill_call;

							if( !empty( $bill_call_arr[$q_val['customer_no']] ) ){

								//do number formatting

								foreach( $bill_call_arr[$q_val['customer_no']] AS $keyc => $valc ){
									$bill_call_arr[$q_val['customer_no']][$keyc]['amount'] = number_format( $valc['tax_percent'], 2, ".", ","); 
									$bill_call_arr[$q_val['customer_no']][$keyc]['tax_amount'] = number_format( $valc['amount'], 2, ".", ","); 
									$bill_call_arr[$q_val['customer_no']][$keyc]['tax_percent'] = number_format( $valc['tax_amount'], 2, ".", ","); 
									$bill_call_arr[$q_val['customer_no']][$keyc]['total'] = number_format( $valc['total'], 2, ".", ","); 
								}

							}
						}

						//set to 1 so wont run for this customer again
						$bill_call_done[$q_val['customer_no']] = 1;
					}
					
				}

				foreach($num as $num_key => $customer_num)
				{
					$statement = array();
					if( (!empty($cust_arr[$customer_num]))
					&& (!empty($bill_arr[$customer_num]))
					&& (!empty($bill_detail_arr[$customer_num])) ){
						$statement['customer'] 		= $cust_arr[$customer_num];
						$statement['bill'] 			= $bill_arr[$customer_num];
						$statement['bill_detail'] 	= $bill_detail_arr[$customer_num];
						$statement['bill_call'] 	= $bill_call_arr[$customer_num];
						$statement_arr[] = $statement;
					}
				}

				$return_val = $statement_arr;
			}
		}
		elseif ($by == 'latest_bill_by_bk')
		{
			$table_customer_query	= ($num != '')? "customer_no = '$num'":"";
			$table_customer			= $this->get_table('customer','*', $table_customer_query);

			foreach ($table_customer as $key => $val)
			{
				$customer_no		= $table_customer[$key]['customer_no'];
				$table_bill_query	= ($customer_no != '')? "customer_no = '$customer_no'":"";

				$order_by			="bill_date desc";
				$limit				='1';

				$table_bill			= $this->get_table('bill','*', $table_bill_query,$order_by,$limit);

				foreach ($table_bill as $table_bill_key => $table_bill_val)
				{
					$return_val[$table_bill_key]['customer'] 	= $table_customer[$key];
					$return_val[$table_bill_key]['bill']		= (empty($table_bill[$table_bill_key]))?'':$table_bill[$table_bill_key];

					$bill_no			= $table_bill[$table_bill_key]['bill_no'];
					$bill_detail_query 	= ($bill_no != '')? "bill_no = '$bill_no'":"";
					$bill_detail 		= $this->get_table('bill_detail','*, amount as item_amount, remark as item_remark',$bill_detail_query);
					$return_val[$table_bill_key]['bill_detail'] = (empty($bill_detail))?'':$bill_detail;
				}
			}
		}
		return $return_val;
	}

	function get_bill_itemized($bill_no=''){
		$query_str = "SELECT bd.idx , bd.tranx_date, bd.remark, bd.amount, bd.tax_amount, bd.tax_code ,
								sbt.bill_type_id, 
								sbt.name as bill_type_name , sbt.ledger_account_code
						FROM bill_detail bd
						INNER JOIN sys_bill_type sbt ON bd.bill_type = sbt.bill_type_id
						WHERE bd.bill_no = '$bill_no'
						ORDER BY tranx_date, idx ";
		$query = $this->db->query($query_str);
		if ($query->num_rows() > 0)
			$return_val = $query->result_array();
		else
			$return_val = array();

		foreach ($return_val as $key => $val) {
			$return_val[$key]['tranx_date'] =
			date_toggle($return_val[$key]['tranx_date'],$_SESSION['config']['date_format']);
		}
		return $return_val;
	}

	function get_einvoice_status($bill_no=''){

		$query_str = "
		SELECT be.einvoice_status, be.einvoice_uuid, be2.einvoice_status AS cn_einvoice_status, be2.einvoice_uuid AS cn_einvoice_uuid, b.is_void  
		FROM bill b 
		LEFT JOIN bill_einvoice be ON (be.bill_no = b.bill_no) 
		LEFT JOIN bill_einvoice_cn be2 ON (be2.bill_no = b.bill_no) 
		WHERE b.bill_no = ? 
		";
		$query = $this->db->query($query_str, array($bill_no));
		if ($query->num_rows() > 0)
			$return_val = $query->row_array();
		else
			$return_val = array();

		return $return_val;
	}

	function get_invoice($bill_no = '', $date_select ='')
	{
		$customer_category_list = $_SESSION['cust_category'];
		$bill_type_list = $_SESSION['bill_type'];
		$state_list = $_SESSION['state'];

		$return_val = array();
		$query_where = '';
		if ( is_array( $bill_no ) && !empty($bill_no) ){
			$query_where .= " AND b.bill_no IN ( ".implode( "," , $bill_no  )." ) ";
		}
		elseif ( !is_array($bill_no) && !empty($bill_no) ) {
			$query_where .= "AND b.bill_no = '$bill_no' ";
		}
		if ( !empty($date_select) ) {
			$date = new DateTime($date_select);
			$query_where .= "AND (b.bill_date >= '" . $date->format('Y-m-1') . "' AND b.bill_date <= '" .$date->format('Y-m-t') . "') ";
		}
		if ( empty($query_where) ) {
			return $return_val;
		}

		$pre_bill_no = '';
		$pre_cust_no = '';
	
		$query_str = "SELECT b.idx as bill_idx , b.customer_no, b.bill_no, b.po_no, b.bill_date, b.bill_type as actual_bill_type, b.bill_due_date, c.project_name, c.unit_name, p.ssm as comp_ssm,
								CASE WHEN b.bill_type = 'INV' OR b.bill_type = 'DN' THEN b.charges ELSE (-1*b.charges) END AS charges , 
								CASE WHEN b.bill_type = 'INV' OR b.bill_type = 'DN' THEN b.tax_charges ELSE (-1*b.tax_charges) END AS tax_charges ,
								CASE WHEN b.bill_type = 'INV' OR b.bill_type = 'DN' THEN b.amount ELSE -1*(b.amount) END AS amount ,
								b.is_void, bd.idx as bill_dtl_idx , bd.bill_type, 
								CASE WHEN b.bill_type = 'INV' OR b.bill_type = 'DN' THEN bd.amount ELSE -1*(bd.amount) END AS item_amount ,
								bd.remark, bd.tranx_date, c.name as customer_name, c.reg_no, c.pic_name, c.payment_term, 
								c.package_name, c.category, p.acc_name AS bill_name, bb.name AS building_name, p.bill_unit_no, p.bill_addr_1 AS bill_addr1, p.bill_addr_2 AS bill_addr2, p.bill_addr_3 AS bill_addr3, p.bill_city,
								p.bill_postcode, p.bill_state, c.inst_unit_no, c.inst_addr1, c.inst_addr2, c.inst_addr3, c.inst_city, c.inst_postcode,
								c.inst_state, p.acc_mobileno AS tel_num, p.acc_email AS email_addr, c.fax_num , b.bill_type AS doc_title, bd.tax_code, c.currency_code, be.einvoice_uuid, be.einvoice_longid 
						FROM bill b 
						LEFT JOIN bill_detail bd ON b.bill_no = bd.bill_no 
						LEFT JOIN customer c ON b.customer_no = c.customer_no 
						LEFT JOIN profile p ON p.acc_id = c.profile_id 
						LEFT JOIN building bb ON bb.building_no = c.building  
						LEFT JOIN bill_einvoice be ON (b.bill_no = be.bill_no) 
						WHERE 1=1 $query_where 
						ORDER BY c.category, b.customer_no, b.bill_no, bill_dtl_idx";
		/*
		$query_str = "SELECT b.customer_no, b.bill_no, bill_date, b.charges, b.tax_charges, b.amount, " .
					"bd.bill_type, bd.amount as item_amount, bd.remark, " .
					"c.name as customer_name, c.reg_no, c.pic_name, c.payment_term, c.package_name, c.category, " .
					"c.bill_name, c.bill_addr1, c.bill_addr2, c.bill_city, c.bill_postcode, c.bill_state, ".
					"c.inst_addr1, c.inst_addr2, c.inst_city, c.inst_postcode, c.inst_state, c.tel_num, c.fax_num " .
					"FROM bill b " .
					"INNER JOIN bill_detail bd ON b.bill_no = bd.bill_no " .
					"INNER JOIN customer c ON b.customer_no = c.customer_no " .
					"WHERE c.category != 'r' " .
					$query_where .
					"ORDER BY c.category, b.customer_no, b.bill_no";
					*/

		$query = $this->db->query($query_str);

		foreach ($query->result_array() as $row) {
			if ($pre_bill_no != $row['bill_no']) {
				$item_count = 1;
				$pre_bill_no = $row['bill_no'];

				$return_val[$row['bill_no']]['idx'] = $row['bill_idx'];
				$return_val[$row['bill_no']]['doc_title'] = [ 'INV' => 'INVOICE', 'DN'  => 'DEBIT NOTE', 'CN'  => 'CREDIT NOTE' ][$row['doc_title']] ?? 'INVOICE';

				$return_val[$row['bill_no']]['bill_no'] = $row['bill_no'];
				$return_val[$row['bill_no']]['actual_bill_type'] = $row['actual_bill_type'];
				$return_val[$row['bill_no']]['po_no'] = $row['po_no'];
				$return_val[$row['bill_no']]['is_void'] = $row['is_void'];
				$return_val[$row['bill_no']]['customer_category'] = $row['category'];
				$return_val[$row['bill_no']]['customer_no'] = $row['customer_no'];
				$return_val[$row['bill_no']]['customer_name'] = $row['customer_name'];
				$return_val[$row['bill_no']]['currency_code'] = $row['currency_code'];
				$return_val[$row['bill_no']]['package_name'] = $row['package_name'];
				$return_val[$row['bill_no']]['bill_date'] = $row['bill_date'];
				$return_val[$row['bill_no']]['bill_due_date'] = $row['bill_due_date'];
				$return_val[$row['bill_no']]['charge'] = number_format( $row['charges'] , 2 , "." , "," ) ;
				$return_val[$row['bill_no']]['tax_charge'] = number_format( $row['tax_charges'] , 2 , "." , "," ) ;
				$return_val[$row['bill_no']]['amount'] = number_format( $row['amount'] , 2 , "." , "," ) ;
				//Get customer detail information
				$return_val[$row['bill_no']]['comp_ssm'] = $row['comp_ssm'];
				$return_val[$row['bill_no']]['reg_no'] = $row['reg_no'];
				$return_val[$row['bill_no']]['pic_name'] = $row['pic_name'];
				$return_val[$row['bill_no']]['bill_name'] = $row['bill_name'];
				$return_val[$row['bill_no']]['bill_addr1'] = $row['bill_addr1'];
				$return_val[$row['bill_no']]['bill_addr2'] = $row['bill_addr2'];
				$return_val[$row['bill_no']]['bill_city'] = $row['bill_city'];
				$return_val[$row['bill_no']]['bill_postcode'] = $row['bill_postcode'];
				$return_val[$row['bill_no']]['bill_state'] = (empty($state_list[$row['bill_state']]))?'':$state_list[$row['bill_state']];

				$return_val[$row['bill_no']]['inst_addr1'] = $row['inst_addr1'];
				$return_val[$row['bill_no']]['inst_addr2'] = $row['inst_addr2'];
				$return_val[$row['bill_no']]['inst_addr3'] = $row['inst_addr3'];
				$return_val[$row['bill_no']]['inst_city'] = $row['inst_city'];
				$return_val[$row['bill_no']]['inst_postcode'] = $row['inst_postcode'];
				$return_val[$row['bill_no']]['inst_state'] = $state_list[$row['inst_state']] ?? '';

				if (empty($return_val[$row['bill_no']]['bill_addr1'])) {
					$return_val[$row['bill_no']]['display_unit_no'] = ( (!empty($row['building_name'])) ? $row['inst_unit_no'].", ".$row['building_name'] : '');
					$return_val[$row['bill_no']]['display_addr1'] = $row['inst_addr1'];
					$return_val[$row['bill_no']]['display_addr2'] = $row['inst_addr2'];
					$return_val[$row['bill_no']]['display_addr3'] = $row['inst_addr3'];
					$return_val[$row['bill_no']]['display_city'] = $row['inst_city'];
					$return_val[$row['bill_no']]['display_postcode'] = $row['inst_postcode'];
					$return_val[$row['bill_no']]['display_state'] = strtoupper($row['inst_state']!='-'?$state_list[$row['inst_state']]:'');
				}
				else {
					$return_val[$row['bill_no']]['display_unit_no'] = ( (!empty($row['building_name'])) ? $row['inst_unit_no'].", ".$row['building_name'] : '');
					$return_val[$row['bill_no']]['display_addr1'] = $row['bill_addr1'];
					$return_val[$row['bill_no']]['display_addr2'] = $row['bill_addr2'];
					$return_val[$row['bill_no']]['display_addr3'] = $row['bill_addr3'];
					$return_val[$row['bill_no']]['display_city'] = $row['bill_city'];
					$return_val[$row['bill_no']]['display_postcode'] = $row['bill_postcode'];
					$return_val[$row['bill_no']]['display_state'] = strtoupper($row['bill_state'] != '-' && isset($state_list[$row['bill_state']]) ? $state_list[$row['bill_state']] : '');
				}

				$return_val[$row['bill_no']]['tel_num'] = $row['tel_num'];
				$return_val[$row['bill_no']]['fax_num'] = $row['fax_num'];
				$return_val[$row['bill_no']]['email_addr'] = $row['email_addr'];
				$return_val[$row['bill_no']]['payment_term'] = $row['payment_term'];

				$return_val[$row['bill_no']]['project_name'] = $row['project_name'];
				$return_val[$row['bill_no']]['unit_name'] = $row['unit_name'];

				$return_val[$row['bill_no']]['einvoice_uuid'] = $row['einvoice_uuid'];
				$return_val[$row['bill_no']]['einvoice_longid'] = $row['einvoice_longid'];
			}
			
			$return_val[$row['bill_no']]['bill_detail'][] = array(
						'item_count' => $item_count++,
						'idx' => $row['bill_dtl_idx'],
						'tranx_date' => $row['tranx_date'],
						'bill_type' => $row['bill_type'],
						'bill_type_name' => $bill_type_list[$row['bill_type']] ?? '',
						'bill_type_name_with_remark' => ($bill_type_list[$row['bill_type']] ?? '') . ( !empty($row['remark']) ? ' - '.$row['remark'] : '' ),
						'item_amount' => number_format( $row['item_amount'] , 2 , "." , "," ) ,
						'remark' => $row['remark'],
						'tax_code' => $row['tax_code'],
						);
		}

		return $return_val;
	}

	function get_print_filtered($txt_search='',$page_item_no, $query_where = '',$isNotBtPrintFiltered)
	{

		$txt_search = $this->db->escape_str($txt_search);

		$return_val['total_row'] = 0;
		$query_str= "SELECT count(*) as total_row " .
					"FROM customer c " .
					"INNER JOIN bill b ON c.customer_no = b.customer_no " .
					//~ "INNER JOIN ( " .
						//~ "SELECT MAX(bill_no) as bill_no " .
						//~ "FROM bill GROUP BY customer_no " .
						//~ " ) a ON a.bill_no = b.bill_no " .
					"WHERE (c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR c.nric LIKE '%$txt_search%') " .
					$query_where;

		//echo $query_str ; exit ;
		$query 		= $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query_str = "SELECT c.customer_no, c.name, IF(c.activated_date = 0, '', c.activated_date) as activated_date , " .
					"sas.name as status, " .
					"scc.name as category , b.bill_date , b.bill_no " .
					"FROM customer c " .
					"INNER JOIN sys_account_status sas ON c.status = sas.status_code " .
					"INNER JOIN sys_customer_category scc ON c.category = scc.category_code " .
					"INNER JOIN bill b ON c.customer_no = b.customer_no " .
					//~ "INNER JOIN ( " .
						//~ "SELECT MAX(bill_no) as bill_no " .
						//~ "FROM bill GROUP BY customer_no " .
						//~ " ) a ON a.bill_no = b.bill_no " .
					"WHERE (c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR c.nric LIKE '%$txt_search%') " .
					$query_where .
					"ORDER BY c.customer_no";
		$query 		= $this->db->query($query_str);
		if(!empty($isNotBtPrintFiltered))
		{
			if ($query->num_rows() > 0)	{
				$row_filtered = $query->result_array();
			}

			$print_arr = array();
			foreach ($row_filtered as $rf_key => $rf_arr){
				$print_cust_no_arr[] = $row_filtered[$rf_key]['customer_no'];
				$print_bill_no_arr[] = $row_filtered[$rf_key]['bill_no'];
			}
			$return_val['print_cust_no_arr'] = $print_cust_no_arr;
			$return_val['print_bill_no_arr'] = $print_bill_no_arr;
		}

		return $return_val;
	}

	/*
	// This function not in used at this moment~~~
	*/
	function detail_of_billing($category, $status, $bill_type, $date_start, $date_end)
	{
		$return_val = array();
		$query_where = '';
		$amount = 0;
		if ( !empty($category) && $category !== 'all' ) {
			$query_where = "AND c.category = '$category' ";
		}
		if ( !empty($status) && $status !== 'all' ) {
			$query_where = "AND c.status = '$status' ";
		}
		if ( !empty($bill_type) && $bill_type !== 'all' ) {
			$query_where = "AND bd.bill_type = '$bill_type' ";
		}
		$query_str = "SELECT b.bill_no, b.bill_date, bd.bill_type, bd.amount,
					c.customer_no, c.name as customer_name, c.category,
					sbt.name as bill_type_name, sbt.is_debit
					FROM bill b
					INNER JOIN bill_detail bd ON b.bill_no = bd.bill_no
					INNER JOIN customer c ON b.customer_no = c.customer_no
					INNER JOIN sys_bill_type sbt ON bd.bill_type = sbt.bill_type_id
					WHERE b.bill_date >= '$date_start' AND b.bill_date <= '$date_end' " .
					$query_where .
					"ORDER BY c.category, c.customer_no, b.bill_no, bd.bill_type";
		$query = $this->db->query($query_str);
		foreach ( $query->result_array() as $row ) {
			if ($row['is_debit'] == 0)
				$row['amount'] = $row['amount'] * -1;
			$return_val['bill_type'][$row['bill_type']] = $row['bill_type_name'];
			if (empty($return_val['subtotal'][$row['category']][$row['bill_type']])) $return_val['subtotal'][$row['category']][$row['bill_type']] = 0;
			if (empty($return_val['grandtotal'][$row['bill_type']])) $return_val['grandtotal'][$row['bill_type']] = 0;
			$return_val['subtotal'][$row['category']][$row['bill_type']] += $row['amount'];
			$return_val['grandtotal'][$row['bill_type']] += $row['amount'];

			$return_val['billing'][$row['category']][] = array(
					'bill_no' => $row['bill_no'],
					'bill_date' =>date_toggle($row['bill_date'],$_SESSION['config']['date_format']),
					'bill_type' => $row['bill_type'],
					'customer_no' => $row['customer_no'],
					'customer_name' => $row['customer_name'],
					'amount' => $row['amount'],
			);
		}

		return $return_val;
	}

	function get_table($table_name='',$select ='*',$and_where='', $order_by='' , $limit='')
	{
		$data = '';
		if($table_name !=''){
			$this->load->database();
			$this->db->select($select);
			$this->db->from($table_name);

			if($and_where != '') $this->db->where($and_where);
			if($order_by != '') $this->db->order_by($order_by);
			if($limit != '') $this->db->limit($limit);

			$data = $this->db->get()->result_array();
		}
		return $data;
	}

	function get_export_bill_list($txt_search,$page_item_no,$query_where = '')
	{
		$return_val['total_row'] 	= 0;
		$return_val['row'] 			= array();

		$txt_search = $this->db->escape_str($txt_search);

		$query_str= "SELECT count(*) as total_row " .
					"FROM customer c " .
					"INNER JOIN bill b ON c.customer_no = b.customer_no " .
					"WHERE (c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR c.nric LIKE '%$txt_search%') " .
					$query_where;
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query = $this->db->query("SELECT c.customer_no, c.name, IF(c.activated_date = 0, '', c.activated_date) as activated_date, " .
			" b.bill_no , b.bill_date, b.amount, b.balance , b.charges , b.tax_charges " .
			"FROM customer c " .
			"INNER JOIN bill b ON c.customer_no = b.customer_no " .
			"WHERE (c.customer_no LIKE '%$txt_search%' OR c.name LIKE '%$txt_search%' OR c.nric LIKE '%$txt_search%') " .
			$query_where .
			"ORDER BY b.bill_date , c.customer_no  LIMIT $page_item_no, 20");

		if ($query->num_rows() > 0)	{
			$return_val['row'] = $query->result_array();
		}

		return $return_val;
	}

	function get_export_filtered($query_where = '')
	{
		$query_str = "SELECT c.customer_no, c.name,
						b.bill_no , b.bill_date, b.amount, b.balance , b.charges , b.tax_charges
						FROM bill b
						INNER JOIN customer c ON c.customer_no = b.customer_no
						WHERE 1 = 1 " . $query_where .
						" ORDER BY b.bill_date , c.customer_no" ;
		$query = $this->db->query( $query_str );
		$return_val = $query->num_rows() > 0 ? $query->result_array() : array() ;
		return $return_val;
	}

	function get_invoice_bill($query_where = '')
	{
		$query_str = "SELECT bill_no , bill_date, amount, balance , charges , tax_charges
						FROM bill
						WHERE  1 = 1 " . $query_where . ' ORDER BY bill_date DESC';
		$query = $this->db->query($query_str);
		$return_val = $query->num_rows() > 0 ? $query->result_array() : array() ;
		return $return_val;
	}

	function get_bill_detail($query_where = '')
	{
		$query_str = "SELECT 
						bd.*,
						sbt.is_debit,
						CASE 
							WHEN sbt.is_debit = 1 THEN 'dr'
							ELSE 'cr'
						END AS transaction_type,
						CASE 
							WHEN sbt.is_debit = 1 THEN '+'
							ELSE '-'
						END AS plus_minus
					FROM `bill_detail` bd 
					LEFT JOIN `sys_bill_type` sbt ON bd.bill_type = sbt.bill_type_id
					WHERE 1=1 " . $query_where;
		$query = $this->db->query($query_str);
		$return_val = $query->num_rows() > 0 ? $query->result_array() : array() ;
		return $return_val;
	}

	function void_bill( $bill_no, $void_reason='' ){
		
		$err_msg = "";
		$smsg = "";
		$bill_info = $this->get_invoice( $bill_no );
		$last_bill = $this->get_last_bill( $bill_info[$bill_no]['customer_no'] , 1 );

		if( $bill_no == $last_bill['bill_no'] )
		{
			//if true, delete
			$str = "UPDATE bill SET is_void = 1 WHERE bill_no = '".$bill_no."' ";
			$query = $this->db->query( $str );
			if( $this->db->affected_rows() )
			{//after delete, reset payment & adjustment status
				if( $last_bill['is_manual'] == 1 ){
					$str = " DELETE FROM bill_adjustment WHERE bill_no = '".$bill_no."' ";
					$query = $this->db->query( $str );
				}else{
					$str = " UPDATE bill_adjustment SET is_lock = 0 , bill_no = NULL
							 WHERE bill_no = '".$bill_no."' ";
					$query = $this->db->query( $str );
				}
				
				$str = " UPDATE payment SET processed = 0, is_lock = 0, bill_no = NULL
								WHERE bill_no = '".$bill_no."' ";
				$query = $this->db->query( $str );
			}
			
			//einvoice - check if submitted to lhdn, if already... then issue CN
			if (!empty($bill_info[$bill_no]['einvoice_longid']) && !empty($bill_info[$bill_no]['einvoice_uuid']) && $bill_info[$bill_no]['actual_bill_type'] == 'INV') {
				//insert into record
				$update_data = array();
				$update_data['bill_date'] = date('Y-m-d');
				$update_data['cn_reason'] = $void_reason;
				$update_data['einvoice_no'] = $bill_no;
				$update_data['einvoice_submission_id'] = '';
				$update_data['einvoice_uuid'] = '';
				$update_data['einvoice_status'] = 'W';
				$update_data['einvoice_longid'] = '';
				$this->bill_model->create_void_cn($bill_no, $update_data);

			}

			$smsg = "You have voided Bill No ".$bill_no.".\nStatus for payments and adjustments under Bill No ".$bill_no." will be reset.";

			$this->load->model('action_log_model');			
			$ctrl			= $this->router->fetch_class();
			$esc_query_str	= $this->db->escape_str( "UPDATE bill SET is_void = 1 WHERE bill_no = '".$bill_no."'" );	
			$method 		= $this->router->method; 				
			$action_desc	= 'Void Bill '.$bill_no;	
			$action_category = 'update';
			$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

		}
		else
		{
			$err_msg = "You are not allow to void this bill.";
		}
		
		return array( 0 => $smsg , 1 => $err_msg );
		
	}

	function update_po_no( $bill_no , $po_no ){
		
		$err_msg = "";
		$smsg = "";
		
		$str = " UPDATE bill SET po_no = '".$po_no."' WHERE bill_no = '".$bill_no."' ";
		$query = $this->db->query( $str );
		$smsg = "Updated.";
		
		return array( 0 => $smsg , 1 => $err_msg );
		
	}

	function get_last_payment($customer_no, $processed = null ){

		$qwhere = "";
		if( $processed == 1 ){
			$qwhere .= " AND processed = '0' ";
		}
		
		$query = "SELECT * FROM payment WHERE customer_no = ".$customer_no. $qwhere." 
		order by pay_date DESC Limit 1";

		$query = $this->db->query($query);

		$return_val = $query->num_rows() > 0 ? $query->row_array() : array();

		return $return_val;
	}

	function get_bill_type(){

		$query = "SELECT name FROM sys_bill_type";

		$query = $this->db->query($query);

		$return_val = $query->num_rows() > 0 ? $query->result_array() : array();

		return $return_val;
	}

	function save_bill_manual( $post_data ){
		$max_bill_draft_no = $this->get_max_bill_draft_no();
		$next_bill_draft_no = $this->get_max_bill_draft_no() + 1 ;

		$last_bill = $this->get_last_bill( $post_data['customer_no'] );
		$prev_balance = $last_bill['balance'];
		
		if( $prev_balance != 0 ){
			$balance = $prev_balance + $post_data['total_amount'];
		}else{
			$balance = $post_data['total_amount'];
		}

		if($post_data['idx'] != ''){
			$bill_draft_no = $post_data['idx'];
		}else{
			$bill_draft_no = $next_bill_draft_no;
		}

		if (!empty($post_data['bill_date'])) {
			$bill_date = strtotime($post_data['bill_date']);
		} else {
			$bill_date = strtotime(date("Y-m-d"));
		}

		if($post_data['related_bill_no'] == '-' || $post_data['bill_type'] == 'INV'){
			$post_data['related_bill_no'] = NULL;
			$post_data['reason'] = NULL;
		}

		$bill = "INSERT INTO bill_draft SET  
			idx = " . (is_numeric($post_data['idx']) ? $post_data['idx'] : "NULL") . " ,
			customer_no = '" . $post_data['customer_no'] . "' ,
			bill_draft_no = '" . $bill_draft_no . "' ,
			bill_date = '" . $post_data['bill_date'] . "' ,
			bill_due_date = '" . date('Y-m-d', strtotime("+30 days", strtotime($post_data['bill_date']))) . "' ,
			previous_balance = '" . $prev_balance . "' , 
			related_bill_no = " . ($post_data['related_bill_no'] === NULL ? "NULL" : "'" . $post_data['related_bill_no'] . "'") . " ,
			reason = '" . $post_data['reason'] . "' ,
			charges = '" . $post_data['total_charge'] . "' ,
			tax_charges = '" . $post_data['total_tax'] . "' ,
			amount = '" . $post_data['total_amount'] . "' ,
			balance = '" . $balance . "' ,
			is_void = '0',
			bill_type = '" . $post_data['bill_type'] . "' ,
			created_by = '" . $post_data['created_by'] . "'
		ON DUPLICATE KEY UPDATE 
			idx = VALUES(idx),
			customer_no = VALUES(customer_no),
			bill_draft_no = VALUES(bill_draft_no),
			bill_date = VALUES(bill_date),
			bill_due_date = VALUES(bill_due_date),
			related_bill_no = VALUES(related_bill_no),
			reason = VALUES(reason),
			previous_balance = VALUES(previous_balance),
			charges = VALUES(charges),
			tax_charges = VALUES(tax_charges),
			amount = VALUES(amount),
			balance = VALUES(balance),
			is_void = VALUES(is_void),
			bill_type = VALUES(bill_type);";

		$query = $this->db->query($bill);
		$rows = $this->db->query("SELECT ROW_COUNT()")->row();

		$post_data['tblAppendGrid_rowOrder'];
		$this->db->query("DELETE FROM bill_draft_detail WHERE bill_draft_no = '" . $bill_draft_no . "';");
		$rows = explode( "," , $post_data['tblAppendGrid_rowOrder'] );
		foreach( $rows AS $row ){	

			if( $post_data['tblAppendGrid_plus_minus_' . $row] == '-' ){
				$post_data['tblAppendGrid_tax_amount_' . $row] = $post_data['tblAppendGrid_tax_amount_' . $row]*-1;
				$post_data['tblAppendGrid_amount_' . $row] = $post_data['tblAppendGrid_amount_' . $row]*-1;
				$post_data['tblAppendGrid_charge_' . $row] = $post_data['tblAppendGrid_charge_' . $row]*-1;
			}

			$post_data['tblAppendGrid_tax_percent_' . $row] = normalize_input($post_data['tblAppendGrid_tax_percent_' . $row], 'float', 0.00);
			
			$dtl = " INSERT INTO bill_draft_detail 
							SET	bill_draft_no		= '".$bill_draft_no."' ,
								tranx_date	= '".$post_data['tblAppendGrid_date_' . $row]."' ,
								bill_type   = '".$post_data['tblAppendGrid_category_' . $row]."' ,
								tax_code    = '".$post_data['tblAppendGrid_tax_code_' . $row ]."' ,
								tax_percent = '".$post_data['tblAppendGrid_tax_percent_' . $row]."' ,
								tax_amount  = '".$post_data['tblAppendGrid_tax_amount_' . $row ]."' ,
								amount      = '".$post_data['tblAppendGrid_charge_' . $row]."' ,
								remark      = '".$this->db->escape_str($post_data['tblAppendGrid_desc_' . $row])."'; ";

			$query = $this->db->query($dtl);
			
		}

		// approver
		$this->db->delete('docs_approver', ['doc_type' => 'mb', 'doc_ref' => $bill_draft_no]);
		if (!empty($post_data['lvl1_approver'])) {
			foreach ($post_data['lvl1_approver'] as $approver) {
				$data = [
					'doc_type' => 'mb',
					'doc_ref' => $bill_draft_no,
					'level' => 1,
					'user_id' => $approver
				];
				$this->db->insert('docs_approver', $data);
			}
		}
		if (!empty($post_data['lvl2_approver'])) {
			foreach ($post_data['lvl2_approver'] as $approver) {
				$data = [
					'doc_type' => 'mb',
					'doc_ref' => $bill_draft_no,
					'level' => 2,
					'user_id' => $approver
				];
				$this->db->insert('docs_approver', $data);
			}
		}
		
		$action_log_desc = '';

		if ($post_data['idx'] == '') {
			$data = [
				"bill_draft_no" => $bill_draft_no,
				"status"  => 'D',
				"date"  => date('Y-m-d H:i:s'),
				"created_on"  => date('Y-m-d H:i:s'),
			];
			$this->db->insert('bill_status', $data);
		}else if($post_data['task'] != 'btSave'){
			$task = $post_data['task'];
			$userdata = $this->session->userdata;
			$user_display = (!empty($userdata)) ? ' by ' . $userdata['user']['display_name'] : '';

			switch ($task) {
				case 'btSendForReview':
					$action_log_desc = ' and sent for review' . $user_display;
					break;
				case 'btApprove':
					$action_log_desc = ' and approved (Level ' . $post_data['changed_status'] == 'B'?'1':'2' .')'. $user_display;
					break;
				case 'btReject':
					$action_log_desc = ' and rejected' . $user_display;
					break;
			}

			$data = [
				"bill_draft_no" => $bill_draft_no,
				"status"  => $post_data['changed_status'],
				"date"  => date('Y-m-d H:i:s'),
				"created_on"  => date('Y-m-d H:i:s'),
			];
			$this->db->insert('bill_status', $data);
		}
		
		// once approved, update the bill_draft to bill
		if($post_data['changed_status']  === 'C'){
			$this->load->model('customer_model');
			$this->load->model('profile_model');

			$max_bill_no = $this->get_max_bill_no();
			$next_bill_no = $this->get_max_bill_no() + 1 ;

			$bill = " INSERT INTO bill SET customer_no = '".$post_data['customer_no']."' ,
										bill_no     = '".$next_bill_no."' ,
										bill_date   = '".$post_data['bill_date']."' ,
										bill_due_date	 = '".date('Y-m-t' , $bill_date)."' ,
										previous_balance = '".$prev_balance."' , 
										payment_received = '".($post_data['ttl_pay_amounts'] ?? 0)."' ,
										charges          = '".$post_data['total_charge']."' ,
										tax_charges      = '".$post_data['total_tax']."' ,
										amount           = '".$post_data['total_amount']."' ,
										balance          = '".$balance."' ,
										is_void          = '0',
										is_manual        = '1',
										bill_type 		 = '".$post_data['bill_type']."' ;";
		
			$query = $this->db->query($bill);

			if( $this->db->affected_rows() ){

				$post_data['tblAppendGrid_rowOrder'];
				$rows = explode( "," , $post_data['tblAppendGrid_rowOrder'] );
				foreach( $rows AS $row ){

					$adjust = " INSERT INTO bill_adjustment 
									SET adjust_by = 'i' ,
										customer_no	= '".$post_data['customer_no']."' ,
										tranx_date	= '".$post_data['bill_date']."' ,
										bill_type	= '".$post_data['tblAppendGrid_category_' . $row]."' ,
										adjust_type = '".$post_data['tblAppendGrid_adjust_type_' . $row]."' ,
										amount      = '".$post_data['tblAppendGrid_charge_'.$row]."' ,
										remark      = '".$this->db->escape_str($post_data['tblAppendGrid_desc_' . $row])."' ,
										is_lock     = 1 ,
										is_autogen  = 0 ,  
										bill_no     = '".$next_bill_no."' ,
										created_by  = '' ,
										created_date = NOW(),
										modified_by = '' ,
										modified_date = NULL
										";
					$query = $this->db->query($adjust);

					$adj_no = $this->db->insert_id();
					
					$this->db->insert('bill_adjustment_status', [
						"adj_no" => $adj_no,
						"status"  => 'C',
						"date"  => date('Y-m-d H:i:s'),
						"created_on"  => date('Y-m-d H:i:s'),
					]);
					
					if( $post_data['tblAppendGrid_plus_minus_' . $row] == '-' ){
						$post_data['tblAppendGrid_tax_amount_' . $row] = $post_data['tblAppendGrid_tax_amount_' . $row]*-1;
						$post_data['tblAppendGrid_amount_' . $row] = $post_data['tblAppendGrid_amount_' . $row]*-1;
						$post_data['tblAppendGrid_charge_' . $row] = $post_data['tblAppendGrid_charge_' . $row]*-1;
					}
					
					$dtl = " INSERT INTO bill_detail 
									SET	bill_no		= '".$next_bill_no."' ,
										tranx_date	= '".$post_data['tblAppendGrid_date_' . $row]."' ,
										bill_type   = '".$post_data['tblAppendGrid_category_' . $row]."' ,
										tax_code    = '".$post_data['tblAppendGrid_tax_code_' . $row ]."' ,
										tax_percent = '".$post_data['tblAppendGrid_tax_percent_' . $row]."' ,
										tax_amount  = '".$post_data['tblAppendGrid_tax_amount_' . $row ]."' ,
										amount      = '".$post_data['tblAppendGrid_charge_' . $row]."' ,
										remark      = '".$this->db->escape_str($post_data['tblAppendGrid_desc_' . $row])."'; ";
					$query = $this->db->query($dtl);
					
				}

				$draft = " UPDATE `bill_draft` SET `bill_no` = ? WHERE `idx` = ?;";
				$this->db->query($draft, [$next_bill_no, $post_data['idx']]);

				
				$temp_customer_info = $this->customer_model->get_customer($post_data['customer_no']);
				$temp_profile_info = $this->profile_model->get_profile($temp_customer_info['profile_id']);
				$query_insert_extra = "";
				if (!empty($temp_customer_info['package'])) {
					$query_insert_extra .= "(" .
										"'" . $next_bill_no . "', " .
										"'" . $post_data['bill_date'] . "', " .
										"'" . $post_data['customer_no'] . "', " .
										"'" . ($temp_customer_info['bill_cycle_month'] ?? 1) . "', " .
										"'" . ($temp_customer_info['package'] ?? 0) . "', " .
										"'" . ($temp_customer_info['package_name'] ?? '') . "', " .
										"'" . ($temp_customer_info['payment_term'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_unit_no'] ?? '') . "', " .
										"'" . ($temp_profile_info['bill_addr_1'] ?? '') . "', " .
										"'" . ($temp_profile_info['bill_addr_2'] ?? '') . "', " .
										"'" . ($temp_profile_info['bill_addr_3'] ?? '') . "', " .
										"'" . ($temp_profile_info['bill_postcode'] ?? '') . "', " .
										"'" . ($temp_profile_info['bill_city'] ?? '') . "', " .
										"'" . ($temp_profile_info['bill_state'] ?? '') . "', " .
										"'" . ($temp_profile_info['pic_email_1'] ?? '') . "', " .
										"'" . ($temp_customer_info['mobile_num'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_unit_no'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_addr1'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_addr2'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_addr3'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_postcode'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_city'] ?? '') . "', " .
										"'" . ($temp_customer_info['inst_state'] ?? '') . "', " .
										"now(), ".
										"'0' ".
										"),";

					if ( $query_insert_extra != '' )
						$this->generate_bill_extra($query_insert_extra);
				}
				
				// if( $post_data['payments_no'] != '' ){
				// 	//payment
				// 	$pay = " UPDATE payment SET bill_no = '". $next_bill_no ."' , is_lock = '1', processed = '1' 
				// 			WHERE payment_no IN ( ".$post_data['payments_no']." ) ; ";
				// 	$this->db->query($pay);
				// }
				
				//update next bill date
				// $this->load->model('customer_model');
				// $customer = $this->customer_model->get_customer($post_data['customer_no']);
				// $next_bill_date = date( 'Y-m-01' , strtotime( " +1 month " , strtotime( $post_data['bill_date'] ) ) ) ;
				// $tranx_date = $post_data['bill_date'];
				// $cronjob_date = CRONJOB_DATE;
				
				// if( ( strtotime($tranx_date) > strtotime($cronjob_date) 
				// 	&& strtotime($next_bill_date) > strtotime($customer['next_bill_date']) )
				// 	|| ( $customer['next_bill_date'] == '' || $customer['next_bill_date'] == NULL )
				// )
				// {
				// 	$this->customer_model->customer_update_next_bill_date($post_data['customer_no'],$next_bill_date);
				// }
			}
		}

		$model			= 'action_log_model';
		$this->load->model($model);			
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($bill.print_r($post_data, true));	
		$method 		= $this->router->method;
		if($post_data['idx'] == ''){
			$action_desc	= 'Manual Billing entry (#'.$bill_draft_no.') has been created'. $action_log_desc . '.';
			$action_category = 'insert';
		}else{
			$action_desc	= 'Manual Billing entry (#'.$bill_draft_no.') has been edit successfully'. $action_log_desc . '.';
			$action_category = 'update';
		}
		$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);
	}

	public function get_monthly_bill_customer(){
		
		$last_month_ini = new DateTime("first day of last month");
		$last_month_end = new DateTime("last day of last month");
		$month_ini = new DateTime("first day of this month");
		$month_end = new DateTime("last day of this month");
		
		
		$query_str = "	SELECT * FROM (
						(
							SELECT c.customer_no,c.name,c.pic_name,c.login_username,c.bill_name, c.status, c.category,
									c.building, c.activated_date, c.suspended_date, c.terminated_date,
									c.monthly_charge, c.bill_cycle_month,
									c.bill_by_email, c.email_1 , c.bill_by_post , c.package
							FROM customer c 
							WHERE ( status = 'r' OR ( status = 's' AND category = 'b' ) ) 
								AND c.activated_date <> 0 
						)
						UNION
						(
							SELECT c.customer_no,c.name,c.pic_name,c.login_username,c.bill_name, c.status, c.category,
									c.building, c.activated_date, c.suspended_date, c.terminated_date,
									c.monthly_charge, c.bill_cycle_month,
									c.bill_by_email, c.email_1 , c.bill_by_post , c.package
							FROM customer c
							WHERE ( status = 't' OR ( status = 's' AND category = 'b' ) ) 
								AND (
										EXISTS ( SELECT payment_no FROM payment
													WHERE payment.customer_no = c.customer_no
													AND pay_date BETWEEN '".$last_month_ini->format('Y-m-d')."'
													AND '".$last_month_end->format('Y-m-d')."' )
										
										OR
										
										EXISTS ( SELECT adj_no FROM bill_adjustment
													WHERE bill_adjustment.customer_no = c.customer_no
													AND tranx_date BETWEEN '".$last_month_ini->format('Y-m-d')."'
													AND '".$last_month_end->format('Y-m-d')."' )
									)
						)
						UNION
						(
							SELECT c.customer_no,c.name,c.pic_name,c.login_username,c.bill_name, c.status, c.category,
									c.building, c.activated_date, c.suspended_date, c.terminated_date,
									c.monthly_charge, c.bill_cycle_month,
									c.bill_by_email, c.email_1 , c.bill_by_post , c.package
							FROM customer c 
							WHERE ( status = 'r' OR status = 't' ) 
							AND ( c.suspended_date <> 0 or c.terminated_date <> 0  )
							AND ( 
								( YEAR(c.suspended_date)='".date('Y')."' AND MONTH(c.suspended_date)='".date('m')."')  OR 
								( YEAR(c.terminated_date)='".date('Y')."' AND MONTH(c.terminated_date)='".date('m')."')
							)
							AND c.activated_date <> 0 
						)
						) z ORDER BY z.category, z.customer_no ";
		
		$this->db->query($query_str);
		$return_val = $query->num_rows() > 0 ? $query->result_array() : array();
		return $return_val;
	}
	
	function generate_bill_pdf($statement_by, $statement_key, $gen_pdf = 0 , $pdf_name = '', $filter_date='')
	{
		
		$this->load->model('common_model');
		
		$bill_model = $this->bill_statement($statement_by, $statement_key, $gen_pdf, $pdf_name, $filter_date);
		$y=0;
		
		foreach( $bill_model['input']['data'] AS $bill ){
			$z=0;
			foreach( $bill['customer'] AS $cust ){
				$display_state = $this->common_model->get_table('sys_state', '*', 'state_code = "'.$cust['display_state'].'" ') ;
				if( isset($display_status[0]['name']) ){
					$bill_model['input']['data'][$y]['customer'][$z]['display_state'] = strtoupper($display_state[0]['name']) ;
				}
				$z++;
			}
			
			$y++;
		}
		
		$this->load->helper('form');
		//$header_data 				= $this->vars;
		$header_data['title']		= $pdf_name;
		$header_data['description']	= 'Monthly Bill # ' . $statement_key ;
		$content_data['data']		= (empty($bill_model['input']['data']))?'':$bill_model['input']['data'];
		$content_data['gst_reg_no']	= (empty($bill_model['gst_reg_no']))?'':$bill_model['gst_reg_no'];
		
		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['bills_footer'] = $footer[0]['val'];
		
		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
		$content_data['company_tax_number'] = $tax[0]['val'];
		
		$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
		$content_data['jompay_biller_code'] = $jompay[0]['val'];
		
		//$data['msg'] 				= $this->msg;
		$gen_pdf 					= $bill_model['gen_pdf'];
		
		$html  = '';
		$dont_gen = false;

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'audit_copy' ");
		$audit_copy = $config_record[0]['val'];

		$audit_file_path = $this->config->item('upload_path').'/temp/pdf/'.$pdf_name.'.pdf';
		$audit_file_name = $pdf_name.'.pdf';

		if ($audit_copy == '1') {
			//check if the bill generated already exist, if exist, don't generate, flag a log
			$chk_audit = $this->common_model->search_audit($audit_file_path, 'bill', $audit_file_name, $statement_key);
			if ($chk_audit) {
				log_message('error', 'Bill No:'.$statement_key.' Not generated because another same bill already exists in audit folder.');
				$dont_gen = true;
			}
		}

		//the function effectively generates each time it is run, even tho the file already exists.

		if($gen_pdf == '1' && !$dont_gen)
		{
			$content_data['gen_pdf'] = 1;
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('bill/bill_statement', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			//echo $html; exit;

			//$this->load->helper(array('dompdf', 'file'));
			//$data = pdf_create($html, '', false);

			//if(empty($pdf_name)) $pdf_name = 'temp_bill_statement_'.date("Y-m-d_h:i:sa", time());
			if(empty($pdf_name)) $pdf_name = 'bill_statement_'.date("Y-m-d");

			$pdf_path = $this->config->item('upload_path')."/temp/pdf";
			//$result = write_file('temp/email_scheduler/'.$pdf_name.'.pdf', $data);
			//$result = write_file($pdf_path.'/'.$pdf_name.'.pdf', $data);

			$this->load->helper('puppeteer_helper');
			$scode = md5($statement_key . $this->e_key);
			puppeteer_print_preview(
				$this->config->item('base_url').'pdfapi/bill_statement/'.$statement_by.'/'.$statement_key.'/'.$scode.'/'.$filter_date, 
				$this->config->item('upload_path').'/temp/pdf/'.$pdf_name.'.pdf', 
				$this->config->item('proj_path'), 
				$this->config->item('chrome_loc'));
			$result = 1;

			$bill_info = $this->get_bill_row($statement_key);
			$this->common_model->perform_audit(
				$audit_file_path, 
				'bill', 
				$audit_file_name, 
				$statement_key, 
				'Customer No:'.$bill_info['customer_no'].' '.date("M y", strtotime($bill_info['bill_date'])).' Bill', 
				'', 
				'', 
				$bill_info['customer_no']
			);

			return $result;
		} else {
			if ($dont_gen) {
				return 1;
			} else {
				return false;
			}
		}
	}
	
	function generate_bill_pdf2($bill_no = '' , $pdf_name = '')
	{

		//what does this function do differently than generate_bill_pdf?
		
		$this->load->helper('custom_helper');
		
		$invoice = $this->get_invoice($bill_no);
		
		$result = '';
		if( !empty($invoice ) ){
		
		
			foreach($invoice as $i_key => $i_val)
			{
				$invoice[$i_key]['bill_no'] 			= add_zero($invoice[$i_key]['bill_no']);
				$invoice[$i_key]['bill_date'] 			= date_toggle($invoice[$i_key]['bill_date'],$_SESSION['config']['date_format_printing']);
				$invoice[$i_key]['display_name'] 		= (empty($invoice[$i_key]['bill_name']))?$invoice[$i_key]['customer_name']:$invoice[$i_key]['bill_name'];
			}
			
			//$header_data 				= $this->vars;
			$header_data['title'] 		= 'print preview | invoice';
			$header_data['description']	= 'to print invoice';
			$content_data['data']		= $invoice;
			
			$gst_reg_no	 =  $this->common_model->get_gst_reg_no();
			$content_data['gst_reg_no']	= $gst_reg_no;

			$gst_amount	= $this->common_model->get_default_tax_amount();
			$content_data['default_tax'] = $gst_amount[0]['percent'];
			
			$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
			$content_data['bills_footer'] = $footer[0]['val'];
			
			$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
			$content_data['company_full_name'] = $comp[0]['val'];

			$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
			$content_data['company_tax_number'] = $tax[0]['val'];
			
			$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
			$content_data['jompay_biller_code'] = $jompay[0]['val'];
		
			//company name and address
			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_1' ");
			$content_data['company_addr_1'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_2' ");
			$content_data['company_addr_2'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_addr_3' ");
			$content_data['company_addr_3'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_postal' ");
			$content_data['company_postal'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_city' ");
			$content_data['company_city'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
			$content_data['company_phone'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_brn' ");
			$content_data['company_brn'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_state' ");
			$company_state = $config[0]['val'];
			$content_data['company_state'] = $this->common_model->get_state_name_from_einvoice_code($company_state);

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_phone' ");
			$content_data['company_phone'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_fax' ");
			$content_data['company_fax'] = $config[0]['val'];

			$config = $this->common_model->get_table('sys_config','*', "`category` = 'einvoice' AND `key` = 'company_email' ");
			$content_data['company_email'] = $config[0]['val'];
		
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', '', true);
			//$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);

			$doc_template = $this->config->item('doc_template');

			if (!empty($doc_template)) {

				if (file_exists(FCPATH.'application/views/bill/template/'.$doc_template.'/bill_invoice.php')) {
					$html .= $this->parser->parse('bill/template/'.$doc_template.'/bill_invoice', $content_data, true);
				} else {
					$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);
				}

			} else {
				$html .= $this->parser->parse('bill/bill_invoice', $content_data, true);
			}

			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$data = pdf_create($html, '', false);
			
			if(empty($pdf_name)) $pdf_name = 'bill_statement_'.date("Y-m-d");
			$result = write_file('temp/email_scheduler/'.$pdf_name.'.pdf', $data);
			
		}

		return $result;

	}

	function create_prorated_bill($customer_no, $monthly_charge, $activated_date,$suspended_date,$terminated_date)
	{
		if ( !empty($activated_date) ) {
			
			$end_date = "";
			if( $suspended_date != '0000-00-00' && $suspended_date != '' )
				$end_date = $suspended_date; 
			elseif( $terminated_date != '0000-00-00' && $terminated_date != '' )
				$end_date = $terminated_date; 
			
			//Pro-rate
			$month_ini = new DateTime("first day of this month");

			if( $end_date != "" )
				$month_end = new DateTime( date( 'Y-m-d' , strtotime($end_date) ) );							
			else
				$month_end = new DateTime("last day of this month");			

			$total_days = $month_ini->format('t');
			$total_used_days = $month_end->diff( new datetime($activated_date) )->format('%a') + 1; // + 1 because need to including the day start using
			$amount = ($monthly_charge / $total_days * $total_used_days );
			
			//$obj_adj = new Adjustment();
			$adj_remark = 'Prorated Bill From ' . date('Y-m-d',strtotime($activated_date)) . ' UNTIL ' . $month_end->format('Y-m-d');
			
			$this->load->model('adjustment_model');
			$this->adjustment_model->insert_adj('i', $customer_no, '', '', date('Y-m-d'), '41', 'dr', $amount, $adj_remark, 1);
			
		}
	}

	public function phone_call_record_insert($post_back) {

		//print_r($post_back); exit;

		$sql = "INSERT INTO `phone_call_record` (
			import_id, `account_code`, caller_no, caller_nat, callee_no, `callee_nat`, context, caller_id, source_channel, dest_channel, last_app, last_data, start_time, answer_time, end_time, call_time, talk_time, call_status, ama_flags, unique_id, call_type, dest_channel_ext, caller_name, answered_by, `session`, premier_caller, action_type, source_trunk, dest_trunk, created_on, created_by
		) VALUES (
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?, 
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?  
		)";

		$values_array = array(
			$post_back['import_id'],
			$post_back['account_code'],
			$post_back['caller_no'],
			$post_back['caller_nat'],
			$post_back['callee_no'],
			$post_back['callee_nat'],
			$post_back['context'],
			$post_back['caller_id'],
			$post_back['source_channel'],
			$post_back['dest_channel'],
			$post_back['last_app'],
			$post_back['last_data'],
			$post_back['start_time'],
			$post_back['answer_time'],
			$post_back['end_time'],
			$post_back['call_time'],
			$post_back['talk_time'],
			$post_back['call_status'],
			$post_back['ama_flags'],
			$post_back['unique_id'],
			$post_back['call_type'],
			$post_back['dest_channel_ext'],
			$post_back['caller_name'],
			$post_back['answered_by'],
			$post_back['session'],
			$post_back['premier_caller'],
			$post_back['action_type'],
			$post_back['source_trunk'],
			$post_back['dest_trunk'],
			date('Y-m-d H:i:s'),
			0 
		);

		$this->db->query($sql, $values_array);

	}

	public function update_einvoice_param($bill_no, $update_data) {
		$sql = "REPLACE INTO `bill_einvoice` VALUES (?,?,?,?,?,?,?,?,?,?) ";

		$values_array = array(
			$bill_no,
			$update_data['bill_date'],
			$bill_no,
			$update_data['einvoice_submission_id'],
			$update_data['einvoice_uuid'],
			$update_data['einvoice_status'],
			$update_data['einvoice_longid'],
			date('Y-m-d H:i:s'),
			0,
			date('Y-m-d H:i:s')
		);

		$this->db->query($sql, $values_array);
	}

	public function create_void_cn($bill_no, $update_data) {
		$sql = "REPLACE INTO `bill_einvoice_cn` VALUES (?,?,?,?,?,?,?,?,?,?,?) ";

		$values_array = array(
			$bill_no,
			$update_data['bill_date'],
			$update_data['cn_reason'],
			$bill_no,
			$update_data['einvoice_submission_id'],
			$update_data['einvoice_uuid'],
			$update_data['einvoice_status'],
			$update_data['einvoice_longid'],
			date('Y-m-d H:i:s'),
			0,
			date('Y-m-d H:i:s')
		);

		$this->db->query($sql, $values_array);
	}

	public function prepare_einvoice_array( $data, $xml=FALSE ) {

		$einvoice_data = array();

		//debugarr($data);

		//get bill details - adjustments will be added into this table during bill generate cycle, including extra phone charges
    	$query_str = "SELECT bd.*, sbt.class_codes, sbt.tax_type, sbt.name AS bill_type_name FROM bill_detail bd LEFT JOIN sys_bill_type sbt ON (bd.bill_type = sbt.bill_type_id) WHERE bd.bill_no = ? ";
    	$query		= $this->db->query($query_str, array($data['bill_no']));
    	$bill_detail = $query->result_array();

		//use the actual inv number with prefix for invoice num
		$doc_num = $data['bill_no'];
		//$einvoice_data['invoice_head']['invoice_num'] = $doc_num;

		//example einvoice obj here
		$einvoice_data['invoice_num'] = $doc_num;
		$einvoice_data['created_date'] = date('Y-m-d H:i:s');
		$einvoice_data['cur'] = $data['currency_code'];
		$einvoice_data['cur_rate'] = 1;
		$einvoice_data['notes'] = 'Bill No:'.$doc_num;
		$einvoice_data['period_start'] = $data['bill_date'];
		$period_end = $data['bill_date'];
		if (is_numeric($data['payment_term'])) {
			$period_end = date('Y-m-d', strtotime($data['bill_date'] . " +".$data['payment_term']." days"));
		}
		$einvoice_data['period_end'] = $period_end;
		$einvoice_data['payment_method'] = $this->_global_session['config']['company_lhdn_paymode'];
		$einvoice_data['payment_acc'] = $this->_global_session['config']['payment_acc'];
		$einvoice_data['payment_term'] = $data['payment_term'];
		$einvoice_data['payment_ref'] = '';
		//prepaid
		$einvoice_data['prepaid'] = array();

		//this is us - the party that is selling our goods and services
		$supplier = array();
		$supplier['line_1'] = htmlspecialchars($this->_global_session['config']['company_addr_1']);
		$supplier['line_2'] = htmlspecialchars($this->_global_session['config']['company_addr_2']);
		$supplier['line_3'] = htmlspecialchars($this->_global_session['config']['company_addr_3']);
		$supplier['postal_zone'] = $this->_global_session['config']['company_postal'];
		$supplier['city_name'] = $this->_global_session['config']['company_city'];
		$supplier['state_code'] = $this->_global_session['config']['company_state'];
		$supplier['country_code'] = $this->_global_session['config']['company_country'];
		$supplier['company_name'] = $this->_global_session['config']['company_full_name'];
		$supplier['agency'] = ''; //ATIGA, for export supplier use only
		$supplier['agency_no'] = '';
		$supplier['tin'] = $this->_global_session['config']['company_tin'];
		$supplier['company_reg_id'] = $this->_global_session['config']['company_brn'];
		$supplier['company_reg_type'] = 'BRN';
		$supplier['tax_id'] = $this->_global_session['config']['company_tax_number'];
		$supplier['ttx_id'] = $this->_global_session['config']['company_ttx'];
		$supplier['tel'] = $this->_global_session['config']['company_phone'];
		$supplier['email'] = $this->_global_session['config']['company_email'];
		$supplier['msic'] = $this->_global_session['config']['company_msic'];
		$supplier['msic_desc'] = $this->_global_session['config']['company_msic_desc'];
		$einvoice_data['supplier'] = $supplier;

		//this is customer for the invoice
		$buyer = array();
		$buyer['line_1'] = htmlspecialchars($data['bill_addr_1']);
		$buyer['line_2'] = htmlspecialchars($data['bill_addr_2']);
		$buyer['line_3'] = htmlspecialchars($data['bill_addr_3']);
		$buyer['postal_zone'] = $data['bill_postcode'];
		$buyer['city_name'] = $data['bill_city'];
		$buyer['state_code'] = $data['bill_state'];
		//might have passport holders living outside of malaysia?
		$buyer['country_code'] = 'MYS';
		$buyer['company_name'] = ((strtoupper($data['acc_type']) != 'R') ? $data['comp_name'] : $data['acc_name']);
		$buyer['agency'] = '';
		$buyer['agency_no'] = '';
		$buyer['tin'] = $data['cust_tin'];
		if (strtoupper($data['acc_type']) != 'R') {
			//company
			$buyer['company_reg_id'] = $data['cust_brn'];
			$buyer['company_reg_type'] = 'BRN';
		} else {
			//nric / personal

			//place general tin if tin not provided
			if (empty($buyer['tin'])) {
				if (!empty($data['nationality']) && $data['nationality'] != 'Malaysian') {
					//foreigner
					$buyer['tin'] = 'EI00000000020';
				} else {
					$buyer['tin'] = 'EI00000000010';
				}
			}
			$buyer['company_reg_id'] = $data['cust_icno'];
			$buyer['company_reg_type'] = 'NRIC';
		}
		$buyer['tax_id'] = $data['cust_sst'];
		$buyer['ttx_id'] = $data['cust_ttx'];
		$buyer['tel'] = $data['cust_mobile'];
		$buyer['email'] = $data['cust_email'];
		$buyer['msic'] = '';
		$buyer['msic_desc'] = '';
		$einvoice_data['buyer'] = $buyer;

		$delivery = array();
		$delivery['line_1'] = htmlspecialchars((!empty($data['del_unit_no']) ? $data['del_unit_no'].', ' : '').$data['del_addr_1']);
		$delivery['line_2'] = htmlspecialchars($data['del_addr_2']);
		$delivery['line_3'] = htmlspecialchars($data['del_addr_3']);
		$delivery['postal_zone'] = $data['del_postal'];
		$delivery['city_name'] = $data['del_city'];
		$delivery['state_code'] = $data['del_state'];
		$delivery['country_code'] = 'MYS';
		$delivery['company_name'] = ((strtoupper($data['acc_type']) != 'R') ? $data['comp_name'] : $data['acc_name']);
		$delivery['agency'] = '';
		$delivery['agency_no'] = '';
		$delivery['tin'] = $data['cust_tin'];
		if (strtoupper($data['acc_type']) != 'R') {
			$delivery['company_reg_id'] = $data['cust_brn'];
			$delivery['company_reg_type'] = 'BRN';
		} else {
			$delivery['company_reg_id'] = $data['cust_icno'];
			$delivery['company_reg_type'] = 'NRIC';
		}
		$delivery['tax_id'] = $data['cust_sst'];
		$delivery['ttx_id'] = $data['cust_ttx'];
		$delivery['tel'] = $data['del_tel'];
		$delivery['email'] = $data['del_email'];
		$delivery['msic'] = '';
		$delivery['msic_desc'] = '';
		//freight
		$delivery['freight_charges'] = array();
		$einvoice_data['delivery'] = $delivery;

		$einvoice_data['additional_doc'] = array();

		//clean up invoice lines, desc only lines merge with the item desc
		$grand_tax_type = '';
		$grand_tax_percent = 0;
		$new_item_list = array();
		$temp_cur_row = array();
		if (!empty($bill_detail)) {
			$line_num = 1;
			foreach ($bill_detail as $key => $val) {
				if (empty($val['remark']) && empty($val['amount'])) {
					//line totally empty, ignore
					continue;
				} else if (empty($val['amount']) && !empty($val['remark'])) {
					//description only, merge with previous item desc
					$temp_cur_row['remark'] = $temp_cur_row['remark'] . " " . $val['remark'];
					$new_item_list[$temp_cur_row['key']]['desc'] = $temp_cur_row['remark'];
				} else {

					$tax_type = $val['tax_type'];
					if (empty($val['tax_type']) && empty($val['tax_amount'])) {
						$tax_type = '06';
					} else if (empty($val['tax_type'])) {
						$tax_type = $this->_global_session['config']['company_def_tax_code'];
					}

					if (empty($val['remark'])) {
						$val['remark'] = $val['bill_type_name'];
					}

					//regular row
					//$new_item_list[$key] = $val;
					$new_item_list[$key]['id'] = $doc_num.$line_num;
					$new_item_list[$key]['price'] = $val['amount'];
					$new_item_list[$key]['cur'] = $data['currency_code'];
					//$new_item_list[$key]['tariff_code'] = '022';
					//$new_item_list[$key]['tariff_type'] = 'CLASS';
					$new_item_list[$key]['desc'] = $val['remark'];
					$new_item_list[$key]['country_code'] = 'MYS';
					$new_item_list[$key]['quantity'] = 1;
					//$new_item_list[$key]['uom'] = $val['uom'];
					$new_item_list[$key]['uom'] = 'EA';
					//tax
					$new_item_list[$key]['tax'] = $val['tax_amount'];
					$new_item_list[$key]['tax_cur'] = $data['currency_code'];
					$new_item_list[$key]['taxable'] = $val['tax_amount'];
					$new_item_list[$key]['taxable_cur'] = $data['currency_code'];
					$new_item_list[$key]['tax_rate'] = $val['tax_percent'];
					$new_item_list[$key]['tax_type'] = $tax_type;
					$new_item_list[$key]['tax_code'] = empty($tax_type) ? '06' : $tax_type;
					$new_item_list[$key]['tax_exemption_reason'] = '';
					$new_item_list[$key]['per_tax'] = $val['tax_amount'];
					//subtotal
					$new_item_list[$key]['subtotal'] = $val['amount']+$val['tax_amount'];
					$new_item_list[$key]['subtotal_cur'] = $data['currency_code'];
					//discounts
					$new_item_list[$key]['discount'] = array();
					//total excluding tax
					$new_item_list[$key]['total_no_tax'] = $val['amount'];
					$new_item_list[$key]['total_no_tax_cur'] = $data['currency_code'];

					//Tariffs
					//Commodity classification follow by bill type
					$tariffs = array();
					if (!empty($val['class_codes'])) {
						$classes = explode(",", $val['class_codes']);
						foreach($classes as $class) {
							$tariffs[] = str_pad($class, 3, '0', STR_PAD_LEFT);
						}
					}
					$new_item_list[$key]['tariffs'] = $tariffs;

					$temp_cur_row = $val;
					$temp_cur_row['key'] = $key;

					//???
					$grand_tax_type = $tax_type;
					$grand_tax_percent = $val['tax_percent'];

					$line_num++;

				}
			}
			$einvoice_data['item_list'] = $new_item_list;
		}

		$einvoice_data['allowance'] = array();

		//grand tax
		$einvoice_data['grand_tax'] = $data['tax_charges'];
		$einvoice_data['grand_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_taxable'] = $data['tax_charges'];
		$einvoice_data['grand_taxable_cur'] = $data['currency_code'];
		$einvoice_data['grand_tax_code'] = empty($grand_tax_type) ? '06' : $grand_tax_type;
		$einvoice_data['grand_tax_type'] = $grand_tax_type;
		$einvoice_data['grand_tax_exemption_reason'] = '';
		$einvoice_data['grand_tax_percent'] = $grand_tax_percent;

		//grand net
		$einvoice_data['grand_total_no_tax'] = $data['charges'];
		$einvoice_data['grand_total_no_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_total_with_tax'] = $data['amount'];
		$einvoice_data['grand_total_with_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_total_payable'] =  $data['amount'];
		$einvoice_data['grand_total_payable_cur'] = $data['currency_code'];
		$einvoice_data['grand_subtotal'] = $data['charges'];
		$einvoice_data['grand_subtotal_cur'] = $data['currency_code'];
		$einvoice_data['grand_discount'] = 0;
		$einvoice_data['grand_discount_cur'] = $data['currency_code'];
		$einvoice_data['grand_fees'] = 0;
		$einvoice_data['grand_fees_cur'] = $data['currency_code'];
		$einvoice_data['grand_rounding'] = 0;
		$einvoice_data['grand_rounding_cur'] = $data['currency_code'];

		//file
		if (!$xml) {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.json';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.json';
		} else {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.xml';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.xml';
		}
		$einvoice_data['xml_type'] = '01';

		$einvoice_data['version'] = '1.1';

		return $einvoice_data;

	}

	public function prepare_einvoice_cn_array( $data, $xml=FALSE ) {

		$einvoice_data = array();

    	$query_str = "SELECT bd.*, sbt.class_codes, sbt.tax_type, sbt.name AS bill_type_name FROM bill_detail bd LEFT JOIN sys_bill_type sbt ON (bd.bill_type = sbt.bill_type_id) WHERE bd.bill_no = ? ";
    	$query		= $this->db->query($query_str, array($data['bill_no']));
    	$bill_detail = $query->result_array();

    	$void_array = array();
    	$void_array['idx'] = ($bill_detail[count($bill_detail)-1]['idx'] + 1);
    	$void_array['bill_no'] = $bill_detail[0]['bill_no'];
    	$void_array['tranx_date'] = $bill_detail[0]['tranx_date'];
    	$void_array['bill_type'] = 0;
    	$void_array['tax_code'] = 'SVE';
    	$void_array['tax_percent'] = 0;
    	$void_array['amount'] = 0;
    	$void_array['tax_amount'] = 0;
    	$void_array['remark'] = 'CN Reason:'.$data['cn_reason'];
    	$void_array['class_codes'] = '';
    	$void_array['tax_type'] = '06';
    	$bill_detail[] = $void_array;

    	/*echo "<pre>";
    	print_r($bill_detail); 
    	echo "</pre>"; exit;*/

		//use the actual inv number with prefix for invoice num
		$doc_num = 'CN'.$data['bill_no'];
		//$einvoice_data['invoice_head']['invoice_num'] = $doc_num;

		//example einvoice obj here
		$einvoice_data['invoice_num'] = $doc_num;
		$einvoice_data['created_date'] = date('Y-m-d H:i:s');
		$einvoice_data['cur'] = $data['currency_code'];
		$einvoice_data['cur_rate'] = 1;
		$einvoice_data['notes'] = 'Bill No:'.$data['bill_no'].' CN Reason:'.$data['cn_reason'];
		$einvoice_data['period_start'] = $data['bill_date'];
		$period_end = $data['bill_date'];
		if (is_numeric($data['payment_term'])) {
			$period_end = date('Y-m-d', strtotime($data['bill_date'] . " +".$data['payment_term']." days"));
		}
		$einvoice_data['period_end'] = $period_end;
		$einvoice_data['payment_method'] = $_SESSION['config']['company_lhdn_paymode'];
		$einvoice_data['payment_acc'] = $_SESSION['config']['payment_acc'];
		$einvoice_data['payment_term'] = $data['payment_term'];
		$einvoice_data['payment_ref'] = '';
		//prepaid
		$einvoice_data['prepaid'] = array();

		$main_doc = array();
		$main_doc['id'] = $data['inv_bill_no'];
		$main_doc['uuid'] = $data['inv_einvoice_uuid'];
		$einvoice_data['main_doc'] = $main_doc;

		//this is us - the party that is selling our goods and services
		$supplier = array();
		$supplier['line_1'] = htmlspecialchars($_SESSION['config']['company_addr_1']);
		$supplier['line_2'] = htmlspecialchars($_SESSION['config']['company_addr_2']);
		$supplier['line_3'] = htmlspecialchars($_SESSION['config']['company_addr_3']);
		$supplier['postal_zone'] = $_SESSION['config']['company_postal'];
		$supplier['city_name'] = $_SESSION['config']['company_city'];
		$supplier['state_code'] = $_SESSION['config']['company_state'];
		$supplier['country_code'] = $_SESSION['config']['company_country'];
		$supplier['company_name'] = $_SESSION['config']['company_full_name'];
		$supplier['agency'] = ''; //ATIGA, for export supplier use only
		$supplier['agency_no'] = '';
		$supplier['tin'] = $_SESSION['config']['company_tin'];
		$supplier['company_reg_id'] = $_SESSION['config']['company_brn'];
		$supplier['company_reg_type'] = 'BRN';
		$supplier['tax_id'] = $_SESSION['config']['company_tax_number'];
		$supplier['ttx_id'] = $_SESSION['config']['company_ttx'];
		$supplier['tel'] = $_SESSION['config']['company_phone'];
		$supplier['email'] = $_SESSION['config']['company_email'];
		$supplier['msic'] = $_SESSION['config']['company_msic'];
		$supplier['msic_desc'] = $_SESSION['config']['company_msic_desc'];
		$einvoice_data['supplier'] = $supplier;

		//this is customer for the invoice
		$buyer = array();
		$buyer['line_1'] = htmlspecialchars($data['bill_addr_1']);
		$buyer['line_2'] = htmlspecialchars($data['bill_addr_2']);
		$buyer['line_3'] = htmlspecialchars($data['bill_addr_3']);
		$buyer['postal_zone'] = $data['bill_postcode'];
		$buyer['city_name'] = $data['bill_city'];
		$buyer['state_code'] = $data['bill_state'];
		//might have passport holders living outside of malaysia?
		$buyer['country_code'] = 'MYS';
		$buyer['company_name'] = ((strtoupper($data['acc_type']) != 'R') ? $data['comp_name'] : $data['acc_name']);
		$buyer['agency'] = '';
		$buyer['agency_no'] = '';
		$buyer['tin'] = $data['cust_tin'];
		if (strtoupper($data['acc_type']) != 'R') {
			//company
			$buyer['company_reg_id'] = $data['cust_brn'];
			$buyer['company_reg_type'] = 'BRN';
		} else {
			//nric / personal
			//place general tin if tin not provided
			if (empty($buyer['tin'])) {
				if (!empty($data['nationality']) && $data['nationality'] != 'Malaysian') {
					//foreigner
					$buyer['tin'] = 'EI00000000020';
				} else {
					$buyer['tin'] = 'EI00000000010';
				}
			}
			$buyer['company_reg_id'] = $data['cust_icno'];
			$buyer['company_reg_type'] = 'NRIC';
		}
		$buyer['tax_id'] = $data['cust_sst'];
		$buyer['ttx_id'] = $data['cust_ttx'];
		$buyer['tel'] = $data['cust_mobile'];
		$buyer['email'] = $data['cust_email'];
		$buyer['msic'] = '';
		$buyer['msic_desc'] = '';
		$einvoice_data['buyer'] = $buyer;

		$delivery = array();
		$delivery['line_1'] = htmlspecialchars((!empty($data['del_unit_no']) ? $data['del_unit_no'].', ' : '').$data['del_addr_1']);
		$delivery['line_2'] = htmlspecialchars($data['del_addr_2']);
		$delivery['line_3'] = htmlspecialchars($data['del_addr_3']);
		$delivery['postal_zone'] = $data['del_postal'];
		$delivery['city_name'] = $data['del_city'];
		$delivery['state_code'] = $data['del_state'];
		$delivery['country_code'] = 'MYS';
		$delivery['company_name'] = ((strtoupper($data['acc_type']) != 'R') ? $data['comp_name'] : $data['acc_name']);
		$delivery['agency'] = '';
		$delivery['agency_no'] = '';
		$delivery['tin'] = $data['cust_tin'];
		if (strtoupper($data['acc_type']) != 'R') {
			$delivery['company_reg_id'] = $data['cust_brn'];
			$delivery['company_reg_type'] = 'BRN';
		} else {
			$delivery['company_reg_id'] = $data['cust_icno'];
			$delivery['company_reg_type'] = 'NRIC';
		}
		$delivery['tax_id'] = $data['cust_sst'];
		$delivery['ttx_id'] = $data['cust_ttx'];
		$delivery['tel'] = $data['del_tel'];
		$delivery['email'] = $data['del_email'];
		$delivery['msic'] = '';
		$delivery['msic_desc'] = '';
		//freight
		$delivery['freight_charges'] = array();
		$einvoice_data['delivery'] = $delivery;

		$einvoice_data['additional_doc'] = array();

		//clean up invoice lines, desc only lines merge with the item desc
		$grand_tax_type = '';
		$grand_tax_percent = 0;
		$new_item_list = array();
		$temp_cur_row = array();
		if (!empty($bill_detail)) {
			$line_num = 1;
			foreach ($bill_detail as $key => $val) {
				if (empty($val['remark']) && empty($val['amount'])) {
					//line totally empty, ignore
					continue;
				} else if (empty($val['amount']) && !empty($val['remark'])) {
					//description only, merge with previous item desc
					$temp_cur_row['remark'] = $temp_cur_row['remark'] . " " . $val['remark'];
					$new_item_list[$temp_cur_row['key']]['desc'] = $temp_cur_row['remark'];
				} else {

					$tax_type = $val['tax_type'];
					if (empty($val['tax_type']) && empty($val['tax_amount'])) {
						$tax_type = '06';
					} else if (empty($val['tax_type'])) {
						$tax_type = $_SESSION['config']['company_def_tax_code'];
					}

					if (empty($val['remark'])) {
						$val['remark'] = $val['bill_type_name'];
					}

					//regular row
					//$new_item_list[$key] = $val;
					$new_item_list[$key]['id'] = $doc_num.$line_num;
					$new_item_list[$key]['price'] = $val['amount'];
					$new_item_list[$key]['cur'] = $data['currency_code'];
					//$new_item_list[$key]['tariff_code'] = '022';
					//$new_item_list[$key]['tariff_type'] = 'CLASS';
					$new_item_list[$key]['desc'] = $val['remark'];
					$new_item_list[$key]['country_code'] = 'MYS';
					$new_item_list[$key]['quantity'] = 1;
					//$new_item_list[$key]['uom'] = $val['uom'];
					$new_item_list[$key]['uom'] = 'EA';
					//tax
					$new_item_list[$key]['tax'] = $val['tax_amount'];
					$new_item_list[$key]['tax_cur'] = $data['currency_code'];
					$new_item_list[$key]['taxable'] = $val['tax_amount'];
					$new_item_list[$key]['taxable_cur'] = $data['currency_code'];
					$new_item_list[$key]['tax_rate'] = $val['tax_percent'];
					$new_item_list[$key]['tax_type'] = $tax_type;
					$new_item_list[$key]['tax_code'] = empty($tax_type) ? '06' : $tax_type;
					$new_item_list[$key]['tax_exemption_reason'] = '';
					$new_item_list[$key]['per_tax'] = $val['tax_amount'];
					//subtotal
					$new_item_list[$key]['subtotal'] = $val['amount']+$val['tax_amount'];
					$new_item_list[$key]['subtotal_cur'] = $data['currency_code'];
					//discounts
					$new_item_list[$key]['discount'] = array();
					//total excluding tax
					$new_item_list[$key]['total_no_tax'] = $val['amount'];
					$new_item_list[$key]['total_no_tax_cur'] = $data['currency_code'];

					//Tariffs
					//Commodity classification follow by bill type
					$tariffs = array();
					if (!empty($val['class_codes'])) {
						$classes = explode(",", $val['class_codes']);
						foreach($classes as $class) {
							$tariffs[] = str_pad($class, 3, '0', STR_PAD_LEFT);
						}
					}
					$new_item_list[$key]['tariffs'] = $tariffs;

					$temp_cur_row = $val;
					$temp_cur_row['key'] = $key;

					//???
					$grand_tax_type = $tax_type;
					$grand_tax_percent = $val['tax_percent'];

					$line_num++;

				}
			}
			$einvoice_data['item_list'] = $new_item_list;
		}

		$einvoice_data['allowance'] = array();

		//grand tax
		$einvoice_data['grand_tax'] = $data['tax_charges'];
		$einvoice_data['grand_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_taxable'] = $data['tax_charges'];
		$einvoice_data['grand_taxable_cur'] = $data['currency_code'];
		$einvoice_data['grand_tax_code'] = empty($grand_tax_type) ? '06' : $grand_tax_type;
		$einvoice_data['grand_tax_type'] = $grand_tax_type;
		$einvoice_data['grand_tax_exemption_reason'] = '';
		$einvoice_data['grand_tax_percent'] = $grand_tax_percent;

		//grand net
		$einvoice_data['grand_total_no_tax'] = $data['charges'];
		$einvoice_data['grand_total_no_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_total_with_tax'] = $data['amount'];
		$einvoice_data['grand_total_with_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_total_payable'] =  $data['amount'];
		$einvoice_data['grand_total_payable_cur'] = $data['currency_code'];
		$einvoice_data['grand_subtotal'] = $data['charges'];
		$einvoice_data['grand_subtotal_cur'] = $data['currency_code'];
		$einvoice_data['grand_discount'] = 0;
		$einvoice_data['grand_discount_cur'] = $data['currency_code'];
		$einvoice_data['grand_fees'] = 0;
		$einvoice_data['grand_fees_cur'] = $data['currency_code'];
		$einvoice_data['grand_rounding'] = 0;
		$einvoice_data['grand_rounding_cur'] = $data['currency_code'];

		//file
		if (!$xml) {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.json';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.json';
		} else {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.xml';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.xml';
		}
		$einvoice_data['xml_type'] = '02';

		$einvoice_data['version'] = '1.1';

		return $einvoice_data;

	}

	public function prepare_einvoice_dn_array( $data, $xml=FALSE ) { 

		$einvoice_data = array();

    	$query_str = "SELECT bd.*, sbt.class_codes, sbt.tax_type, sbt.name AS bill_type_name FROM bill_detail bd LEFT JOIN sys_bill_type sbt ON (bd.bill_type = sbt.bill_type_id) WHERE bd.bill_no = ? ";
    	$query		= $this->db->query($query_str, array($data['bill_no']));
    	$bill_detail = $query->result_array();

    	$void_array = array();
    	$void_array['idx'] = ($bill_detail[count($bill_detail)-1]['idx'] + 1);
    	$void_array['bill_no'] = $bill_detail[0]['bill_no'];
    	$void_array['tranx_date'] = $bill_detail[0]['tranx_date'];
    	$void_array['bill_type'] = 0;
    	$void_array['tax_code'] = 'SVE';
    	$void_array['tax_percent'] = 0;
    	$void_array['amount'] = 0;
    	$void_array['tax_amount'] = 0;
    	$void_array['remark'] = 'DN Reason:'.$data['cn_reason'];
    	$void_array['class_codes'] = '';
    	$void_array['tax_type'] = '06';
    	$bill_detail[] = $void_array;

    	/*echo "<pre>";
    	print_r($bill_detail); 
    	echo "</pre>"; exit;*/

		//use the actual inv number with prefix for invoice num
		$doc_num = 'DN'.$data['bill_no'];
		//$einvoice_data['invoice_head']['invoice_num'] = $doc_num;

		//example einvoice obj here
		$einvoice_data['invoice_num'] = $doc_num;
		$einvoice_data['created_date'] = date('Y-m-d H:i:s');
		$einvoice_data['cur'] = $data['currency_code'];
		$einvoice_data['cur_rate'] = 1;
		$einvoice_data['notes'] = 'Bill No:'.$data['bill_no'].' DN Reason:'.$data['cn_reason'];
		$einvoice_data['period_start'] = $data['bill_date'];
		$period_end = $data['bill_date'];
		if (is_numeric($data['payment_term'])) {
			$period_end = date('Y-m-d', strtotime($data['bill_date'] . " +".$data['payment_term']." days"));
		}
		$einvoice_data['period_end'] = $period_end;
		$einvoice_data['payment_method'] = $_SESSION['config']['company_lhdn_paymode'];
		$einvoice_data['payment_acc'] = $_SESSION['config']['payment_acc'];
		$einvoice_data['payment_term'] = $data['payment_term'];
		$einvoice_data['payment_ref'] = '';
		//prepaid
		$einvoice_data['prepaid'] = array();

		$main_doc = array();
		$main_doc['id'] = $data['inv_bill_no'];
		$main_doc['uuid'] = $data['inv_einvoice_uuid'];
		$einvoice_data['main_doc'] = $main_doc;

		//this is us - the party that is selling our goods and services
		$supplier = array();
		$supplier['line_1'] = htmlspecialchars($_SESSION['config']['company_addr_1']);
		$supplier['line_2'] = htmlspecialchars($_SESSION['config']['company_addr_2']);
		$supplier['line_3'] = htmlspecialchars($_SESSION['config']['company_addr_3']);
		$supplier['postal_zone'] = $_SESSION['config']['company_postal'];
		$supplier['city_name'] = $_SESSION['config']['company_city'];
		$supplier['state_code'] = $_SESSION['config']['company_state'];
		$supplier['country_code'] = $_SESSION['config']['company_country'];
		$supplier['company_name'] = $_SESSION['config']['company_full_name'];
		$supplier['agency'] = ''; //ATIGA, for export supplier use only
		$supplier['agency_no'] = '';
		$supplier['tin'] = $_SESSION['config']['company_tin'];
		$supplier['company_reg_id'] = $_SESSION['config']['company_brn'];
		$supplier['company_reg_type'] = 'BRN';
		$supplier['tax_id'] = $_SESSION['config']['company_tax_number'];
		$supplier['ttx_id'] = $_SESSION['config']['company_ttx'];
		$supplier['tel'] = $_SESSION['config']['company_phone'];
		$supplier['email'] = $_SESSION['config']['company_email'];
		$supplier['msic'] = $_SESSION['config']['company_msic'];
		$supplier['msic_desc'] = $_SESSION['config']['company_msic_desc'];
		$einvoice_data['supplier'] = $supplier;

		//this is customer for the invoice
		$buyer = array();
		$buyer['line_1'] = htmlspecialchars($data['bill_addr_1']);
		$buyer['line_2'] = htmlspecialchars($data['bill_addr_2']);
		$buyer['line_3'] = htmlspecialchars($data['bill_addr_3']);
		$buyer['postal_zone'] = $data['bill_postcode'];
		$buyer['city_name'] = $data['bill_city'];
		$buyer['state_code'] = $data['bill_state'];
		//might have passport holders living outside of malaysia?
		$buyer['country_code'] = 'MYS';
		$buyer['company_name'] = ((strtoupper($data['acc_type']) != 'R') ? $data['comp_name'] : $data['acc_name']);
		$buyer['agency'] = '';
		$buyer['agency_no'] = '';
		$buyer['tin'] = $data['cust_tin'];
		if (strtoupper($data['acc_type']) != 'R') {
			//company
			$buyer['company_reg_id'] = $data['cust_brn'];
			$buyer['company_reg_type'] = 'BRN';
		} else {
			//nric / personal
			//place general tin if tin not provided
			if (empty($buyer['tin'])) {
				if (!empty($data['nationality']) && $data['nationality'] != 'Malaysian') {
					//foreigner
					$buyer['tin'] = 'EI00000000020';
				} else {
					$buyer['tin'] = 'EI00000000010';
				}
			}
			$buyer['company_reg_id'] = $data['cust_icno'];
			$buyer['company_reg_type'] = 'NRIC';
		}
		$buyer['tax_id'] = $data['cust_sst'];
		$buyer['ttx_id'] = $data['cust_ttx'];
		$buyer['tel'] = $data['cust_mobile'];
		$buyer['email'] = $data['cust_email'];
		$buyer['msic'] = '';
		$buyer['msic_desc'] = '';
		$einvoice_data['buyer'] = $buyer;

		$delivery = array();
		$delivery['line_1'] = htmlspecialchars((!empty($data['del_unit_no']) ? $data['del_unit_no'].', ' : '').$data['del_addr_1']);
		$delivery['line_2'] = htmlspecialchars($data['del_addr_2']);
		$delivery['line_3'] = htmlspecialchars($data['del_addr_3']);
		$delivery['postal_zone'] = $data['del_postal'];
		$delivery['city_name'] = $data['del_city'];
		$delivery['state_code'] = $data['del_state'];
		$delivery['country_code'] = 'MYS';
		$delivery['company_name'] = ((strtoupper($data['acc_type']) != 'R') ? $data['comp_name'] : $data['acc_name']);
		$delivery['agency'] = '';
		$delivery['agency_no'] = '';
		$delivery['tin'] = $data['cust_tin'];
		if (strtoupper($data['acc_type']) != 'R') {
			$delivery['company_reg_id'] = $data['cust_brn'];
			$delivery['company_reg_type'] = 'BRN';
		} else {
			$delivery['company_reg_id'] = $data['cust_icno'];
			$delivery['company_reg_type'] = 'NRIC';
		}
		$delivery['tax_id'] = $data['cust_sst'];
		$delivery['ttx_id'] = $data['cust_ttx'];
		$delivery['tel'] = $data['del_tel'];
		$delivery['email'] = $data['del_email'];
		$delivery['msic'] = '';
		$delivery['msic_desc'] = '';
		//freight
		$delivery['freight_charges'] = array();
		$einvoice_data['delivery'] = $delivery;

		$einvoice_data['additional_doc'] = array();

		//clean up invoice lines, desc only lines merge with the item desc
		$grand_tax_type = '';
		$grand_tax_percent = 0;
		$new_item_list = array();
		$temp_cur_row = array();
		if (!empty($bill_detail)) {
			$line_num = 1;
			foreach ($bill_detail as $key => $val) {
				if (empty($val['remark']) && empty($val['amount'])) {
					//line totally empty, ignore
					continue;
				} else if (empty($val['amount']) && !empty($val['remark'])) {
					//description only, merge with previous item desc
					$temp_cur_row['remark'] = $temp_cur_row['remark'] . " " . $val['remark'];
					$new_item_list[$temp_cur_row['key']]['desc'] = $temp_cur_row['remark'];
				} else {

					$tax_type = $val['tax_type'];
					if (empty($val['tax_type']) && empty($val['tax_amount'])) {
						$tax_type = '06';
					} else if (empty($val['tax_type'])) {
						$tax_type = $_SESSION['config']['company_def_tax_code'];
					}

					if (empty($val['remark'])) {
						$val['remark'] = $val['bill_type_name'];
					}

					//regular row
					//$new_item_list[$key] = $val;
					$new_item_list[$key]['id'] = $doc_num.$line_num;
					$new_item_list[$key]['price'] = $val['amount'];
					$new_item_list[$key]['cur'] = $data['currency_code'];
					//$new_item_list[$key]['tariff_code'] = '022';
					//$new_item_list[$key]['tariff_type'] = 'CLASS';
					$new_item_list[$key]['desc'] = $val['remark'];
					$new_item_list[$key]['country_code'] = 'MYS';
					$new_item_list[$key]['quantity'] = 1;
					//$new_item_list[$key]['uom'] = $val['uom'];
					$new_item_list[$key]['uom'] = 'EA';
					//tax
					$new_item_list[$key]['tax'] = $val['tax_amount'];
					$new_item_list[$key]['tax_cur'] = $data['currency_code'];
					$new_item_list[$key]['taxable'] = $val['tax_amount'];
					$new_item_list[$key]['taxable_cur'] = $data['currency_code'];
					$new_item_list[$key]['tax_rate'] = $val['tax_percent'];
					$new_item_list[$key]['tax_type'] = $tax_type;
					$new_item_list[$key]['tax_code'] = empty($tax_type) ? '06' : $tax_type;
					$new_item_list[$key]['tax_exemption_reason'] = '';
					$new_item_list[$key]['per_tax'] = $val['tax_amount'];
					//subtotal
					$new_item_list[$key]['subtotal'] = $val['amount']+$val['tax_amount'];
					$new_item_list[$key]['subtotal_cur'] = $data['currency_code'];
					//discounts
					$new_item_list[$key]['discount'] = array();
					//total excluding tax
					$new_item_list[$key]['total_no_tax'] = $val['amount'];
					$new_item_list[$key]['total_no_tax_cur'] = $data['currency_code'];

					//Tariffs
					//Commodity classification follow by bill type
					$tariffs = array();
					if (!empty($val['class_codes'])) {
						$classes = explode(",", $val['class_codes']);
						foreach($classes as $class) {
							$tariffs[] = str_pad($class, 3, '0', STR_PAD_LEFT);
						}
					}
					$new_item_list[$key]['tariffs'] = $tariffs;

					$temp_cur_row = $val;
					$temp_cur_row['key'] = $key;

					//???
					$grand_tax_type = $tax_type;
					$grand_tax_percent = $val['tax_percent'];

					$line_num++;

				}
			}
			$einvoice_data['item_list'] = $new_item_list;
		}

		$einvoice_data['allowance'] = array();

		//grand tax
		$einvoice_data['grand_tax'] = $data['tax_charges'];
		$einvoice_data['grand_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_taxable'] = $data['tax_charges'];
		$einvoice_data['grand_taxable_cur'] = $data['currency_code'];
		$einvoice_data['grand_tax_code'] = empty($grand_tax_type) ? '06' : $grand_tax_type;
		$einvoice_data['grand_tax_type'] = $grand_tax_type;
		$einvoice_data['grand_tax_exemption_reason'] = '';
		$einvoice_data['grand_tax_percent'] = $grand_tax_percent;

		//grand net
		$einvoice_data['grand_total_no_tax'] = $data['charges'];
		$einvoice_data['grand_total_no_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_total_with_tax'] = $data['amount'];
		$einvoice_data['grand_total_with_tax_cur'] = $data['currency_code'];
		$einvoice_data['grand_total_payable'] =  $data['amount'];
		$einvoice_data['grand_total_payable_cur'] = $data['currency_code'];
		$einvoice_data['grand_subtotal'] = $data['charges'];
		$einvoice_data['grand_subtotal_cur'] = $data['currency_code'];
		$einvoice_data['grand_discount'] = 0;
		$einvoice_data['grand_discount_cur'] = $data['currency_code'];
		$einvoice_data['grand_fees'] = 0;
		$einvoice_data['grand_fees_cur'] = $data['currency_code'];
		$einvoice_data['grand_rounding'] = 0;
		$einvoice_data['grand_rounding_cur'] = $data['currency_code'];

		//file
		if (!$xml) {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.json';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.json';
		} else {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.xml';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.xml';
		}
		$einvoice_data['xml_type'] = '03';

		$einvoice_data['version'] = '1.1';

		return $einvoice_data;

	}

	public function prepare_consolidated_einvoice_array($inv_no, $xml=FALSE )
	{
		$einvoice_data = array();

		$min_bill = 0;
		$max_bill = 0;

    	$query_str = "SELECT bd.*, sbt.class_codes, sbt.tax_type, sbt.name AS bill_type_name 
    	FROM consolidated_detail cd 
    	LEFT JOIN bill b ON (cd.bill_no = b.bill_no)  
    	LEFT JOIN bill_detail bd ON (b.bill_no = bd.bill_no) 
    	LEFT JOIN sys_bill_type sbt ON (bd.bill_type = sbt.bill_type_id) 
    	WHERE cd.inv_no = ? ";
    	$query		= $this->db->query($query_str, array($inv_no));
    	$bill_detail = $query->result_array();

    	//do bill detail here
    	$item_detail = array();
    	$grand_tax = 0;
    	$grand_amount = 0;
    	foreach ($bill_detail as $bill_row) {
    		if (!isset($item_detail[$bill_row['bill_type']])) {
    			//not found, create first one
    			$item_detail[$bill_row['bill_type']]['amount'] = $bill_row['amount'];
    			$item_detail[$bill_row['bill_type']]['remark'] = $min_bill.' - '.$max_bill.' : '.$bill_row['bill_type_name'];
    			$item_detail[$bill_row['bill_type']]['tax_type'] = $bill_row['tax_type'];
    			$item_detail[$bill_row['bill_type']]['tax_amount'] = $bill_row['tax_amount'];
    			$item_detail[$bill_row['bill_type']]['tax_percent'] = $bill_row['tax_percent'];
    			$item_detail[$bill_row['bill_type']]['class_codes'] = $bill_row['class_codes'];
    		} else {
    			//found, add in the values
    			$item_detail[$bill_row['bill_type']]['amount'] += $bill_row['amount'];
    			$item_detail[$bill_row['bill_type']]['tax_amount'] += $bill_row['tax_amount'];
    		}

    		$grand_amount += $bill_row['amount'];
    		$grand_tax += $bill_row['tax_amount'];
    		//amount
    		//remark
    		//tax_type
    		//tax_amount
    		//tax_percent
    		//class_codes

    		//min and max
    		if (empty($min_bill)) {
    			$min_bill = $bill_row['bill_no'];
    		}

       		if (empty($max_bill)) {
    			$max_bill = $bill_row['bill_no'];
    		}

    		if ($min_bill < $bill_row['bill_no']) {
    			$min_bill = $bill_row['bill_no'];
    		}

    		if ($max_bill > $bill_row['bill_no']) {
    			$max_bill = $bill_row['bill_no'];
    		}
    	}

    	$grand_total = $grand_amount+$grand_tax;

    	$doc_num = 'CO'.$inv_no;

		//example einvoice obj here
		$einvoice_data['invoice_num'] = $doc_num;
		$einvoice_data['created_date'] = date('Y-m-d H:i:s');
		$einvoice_data['cur'] = 'MYR';
		$einvoice_data['cur_rate'] = 1;
		$einvoice_data['notes'] = 'Consolidated Invoice :'.$doc_num;
		$einvoice_data['period_start'] = date('Y-m-d');
		$period_end = date('Y-m-d');
		$einvoice_data['period_end'] = $period_end;
		$einvoice_data['payment_method'] = $_SESSION['config']['company_lhdn_paymode'];
		$einvoice_data['payment_acc'] = $_SESSION['config']['payment_acc'];
		$einvoice_data['payment_term'] = $_SESSION['config']['default_payment_term'];
		$einvoice_data['payment_ref'] = '';
		//prepaid
		$einvoice_data['prepaid'] = array();

		//this is us - the party that is selling our goods and services
		$supplier = array();
		$supplier['line_1'] = htmlspecialchars($_SESSION['config']['company_addr_1']);
		$supplier['line_2'] = htmlspecialchars($_SESSION['config']['company_addr_2']);
		$supplier['line_3'] = htmlspecialchars($_SESSION['config']['company_addr_3']);
		$supplier['postal_zone'] = $_SESSION['config']['company_postal'];
		$supplier['city_name'] = $_SESSION['config']['company_city'];
		$supplier['state_code'] = $_SESSION['config']['company_state'];
		$supplier['country_code'] = $_SESSION['config']['company_country'];
		$supplier['company_name'] = $_SESSION['config']['company_full_name'];
		$supplier['agency'] = ''; //ATIGA, for export supplier use only
		$supplier['agency_no'] = '';
		$supplier['tin'] = $_SESSION['config']['company_tin'];
		$supplier['company_reg_id'] = $_SESSION['config']['company_brn'];
		$supplier['company_reg_type'] = 'BRN';
		$supplier['tax_id'] = $_SESSION['config']['company_tax_number'];
		$supplier['ttx_id'] = $_SESSION['config']['company_ttx'];
		$supplier['tel'] = $_SESSION['config']['company_phone'];
		$supplier['email'] = $_SESSION['config']['company_email'];
		$supplier['msic'] = $_SESSION['config']['company_msic'];
		$supplier['msic_desc'] = $_SESSION['config']['company_msic_desc'];
		$einvoice_data['supplier'] = $supplier;

		$einvoice_data['buyer'] = array();
		$einvoice_data['delivery'] = array();

		$einvoice_data['additional_doc'] = array();

		//clean up invoice lines, desc only lines merge with the item desc
		$grand_tax_type = '';
		$grand_tax_percent = 0;
		$new_item_list = array();
		$temp_cur_row = array();
		if (!empty($item_detail)) {
			$line_num = 1;
			foreach ($item_detail as $key => $val) {
				$tax_type = $val['tax_type'];
				if (empty($val['tax_type'])) {
					$tax_type = '06';
				}

				//regular row
				//$new_item_list[$key] = $val;
				$new_item_list[$key]['id'] = $doc_num.$line_num;
				$new_item_list[$key]['price'] = $val['amount'];
				$new_item_list[$key]['cur'] = 'MYR';
				//$new_item_list[$key]['tariff_code'] = '022';
				//$new_item_list[$key]['tariff_type'] = 'CLASS';
				$new_item_list[$key]['desc'] = $val['remark'];
				$new_item_list[$key]['country_code'] = 'MYS';
				$new_item_list[$key]['quantity'] = 1;
				//$new_item_list[$key]['uom'] = $val['uom'];
				$new_item_list[$key]['uom'] = 'EA';
				//tax
				$new_item_list[$key]['tax'] = $val['tax_amount'];
				$new_item_list[$key]['tax_cur'] = 'MYR';
				$new_item_list[$key]['taxable'] = $val['tax_amount'];
				$new_item_list[$key]['taxable_cur'] = 'MYR';
				$new_item_list[$key]['tax_rate'] = $val['tax_percent'];
				$new_item_list[$key]['tax_type'] = $tax_type;
				$new_item_list[$key]['tax_code'] = empty($tax_type) ? '06' : $tax_type;
				$new_item_list[$key]['tax_exemption_reason'] = '';
				$new_item_list[$key]['per_tax'] = $val['tax_amount'];
				//subtotal
				$new_item_list[$key]['subtotal'] = $val['amount']+$val['tax_amount'];
				$new_item_list[$key]['subtotal_cur'] = 'MYR';
				//discounts
				$new_item_list[$key]['discount'] = array();
				//total excluding tax
				$new_item_list[$key]['total_no_tax'] = $val['amount'];
				$new_item_list[$key]['total_no_tax_cur'] = 'MYR';

				//Tariffs
				//Commodity classification follow by bill type
				$tariffs = array();
				if (!empty($val['class_codes'])) {
					$classes = explode(",", $val['class_codes']);
					foreach($classes as $class) {
						$tariffs[] = str_pad($class, 3, '0', STR_PAD_LEFT);
					}
				}
				$new_item_list[$key]['tariffs'] = $tariffs;

				//$temp_cur_row = $val;
				//$temp_cur_row['key'] = $key;

				//???
				if ($val['tax_percent'] > $grand_tax_percent) {
					$grand_tax_type = $tax_type;
					$grand_tax_percent = $val['tax_percent'];
				}

				$line_num++;
			}

			$einvoice_data['item_list'] = $new_item_list;

		}

		$einvoice_data['allowance'] = array();

		//grand tax
		$einvoice_data['grand_tax'] = $grand_tax;
		$einvoice_data['grand_tax_cur'] = 'MYR';
		$einvoice_data['grand_taxable'] = $grand_tax;
		$einvoice_data['grand_taxable_cur'] = 'MYR';
		$einvoice_data['grand_tax_code'] = empty($grand_tax_type) ? '06' : $grand_tax_type;
		$einvoice_data['grand_tax_type'] = $grand_tax_type;
		$einvoice_data['grand_tax_exemption_reason'] = '';
		$einvoice_data['grand_tax_percent'] = $grand_tax_percent;

		//grand net
		$einvoice_data['grand_total_no_tax'] = $grand_amount;
		$einvoice_data['grand_total_no_tax_cur'] = 'MYR';
		$einvoice_data['grand_total_with_tax'] = $grand_total;
		$einvoice_data['grand_total_with_tax_cur'] = 'MYR';
		$einvoice_data['grand_total_payable'] =  $grand_total;
		$einvoice_data['grand_total_payable_cur'] = 'MYR';
		$einvoice_data['grand_subtotal'] = $grand_amount;
		$einvoice_data['grand_subtotal_cur'] = 'MYR';
		$einvoice_data['grand_discount'] = 0;
		$einvoice_data['grand_discount_cur'] = 'MYR';
		$einvoice_data['grand_fees'] = 0;
		$einvoice_data['grand_fees_cur'] = 'MYR';
		$einvoice_data['grand_rounding'] = 0;
		$einvoice_data['grand_rounding_cur'] = 'MYR';

		//file
		if (!$xml) {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.json';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.json';
		} else {
			$einvoice_data['file_path'] = $this->config->item('einvoice_file_path').'/';
			$einvoice_data['file_name'] = $doc_num.'.xml';
			$einvoice_data['signed_file_name'] = $doc_num.'_signed.xml';
		}
		$einvoice_data['xml_type'] = '01';

		$einvoice_data['version'] = '1.1';

		return $einvoice_data;

	}

	function check_bill_exist($bill_no)
	{
		$query = "SELECT `idx` FROM `bill` WHERE `bill_no` = ?";
		$query = $this->db->query($query, [$bill_no]);
		return $query->num_rows() > 0;
	}

	function get_sys_approvers($level, $doc_type = 'mb')
	{
		$query = "SELECT sa.`user_id`, u.`display_name`, u.`allow_whatsapp`, u.`allow_telegram` FROM `sys_approver` sa LEFT JOIN `user` u ON sa.`user_id` = u.`idx` WHERE sa.`level` = ? AND sa.`doc_type` = ?";
		$query = $this->db->query($query, [$level, $doc_type]);
		$return_val = $query->num_rows() > 0 ? $query->result_array() : [];
		
		return $return_val;
	}

	function resend_einvoice($bill_no)
	{

		//also need to do for CN/DN

		$return = array('status' => false, 'err' => '');

		if (empty($bill_no)) {
			$return['err'] = 'No bill no provided.';
			return $return;
		}

    	$query_str = "SELECT b.*, c.currency_code, be.einvoice_no, be.einvoice_submission_id, be.einvoice_uuid, be.einvoice_status, be.einvoice_longid, p.bill_addr_1, p.bill_addr_2, p.bill_addr_3, p.bill_postcode, p.bill_city, ss2.einvoice_code AS bill_state, p.acc_name, p.comp_name, p.tin AS cust_tin, p.ssm AS cust_brn, p.icno AS cust_icno, p.sst AS cust_sst, p.ttx AS cust_ttx, p.acc_mobileno AS cust_mobile, p.acc_email AS cust_email, c.inst_unit_no AS del_unit_no, c.inst_addr1 AS del_addr_1, c.inst_addr2 AS del_addr_2, c.inst_addr3 AS del_addr_3, c.inst_postcode AS del_postal, c.inst_city AS del_city, ss.einvoice_code AS del_state, c.inst_phone AS del_tel, c.inst_email AS del_email, c.payment_term, p.acc_type, be.einvoice_status, p.nationality       
    	FROM bill b 
    	LEFT JOIN bill_einvoice be ON (b.bill_no = be.bill_no) 
    	LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
    	LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
    	LEFT JOIN `sys_state` ss ON (ss.state_code = c.`inst_state`) 
    	LEFT JOIN `sys_state` ss2 ON (ss2.state_code = p.`bill_state`) 
    	WHERE b.bill_no = ? ";

    	$query		= $this->db->query($query_str, array($bill_no));
    	$row = $query->row_array();

    	if (!isset($row['bill_no'])) {
			$return['err'] = 'Bill No not found.';
			return $return;
    	}

    	//check if einvoice status must not be valid or pending
       	if ($row['einvoice_status'] == 'S' || $row['einvoice_status'] == 'P') {
			$return['err'] = 'E-Invoice for this bill already submitted or already valid.';
			return $return;
    	}

    	//check if this bill is manual, if manual, maybe might be different vars

    	//check if this bill is void, if void cannot
       	if ($row['is_void'] != 0) {
			$return['err'] = 'Bill already voided.';
			return $return;
    	}

   		$einvoice_type = $this->config->item('einvoice_type');
		$this->load->model('einvoice_xml_model');
		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');
		//$this->load->model('einvoice_json_model');

		$einvoice_folder = $this->config->item('einvoice_file_path');

		$einvoice_data = $this->bill_model->prepare_einvoice_array( $row, ($einvoice_type=='JSON'?FALSE:TRUE) );

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

		if (!$sign_succ) {
			$xml_error = $sign_err;
			$return['err'] = $xml_error;
			return $return;
		}

		sleep(1);

		$signed_xml_loc = $einvoice_folder.'/'.$row['bill_no'].'_signed.xml';

		if (file_exists($signed_xml_loc)) {

			$login = $this->einvoice_config_model->login_myinvois_portal();

			if ($login) {
				$this->load->model('einvoice_model');
				
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

				$this->load->helper('einvoice');

				$einvoice_log = [
					'bill_no'        => $row['bill_no'],
					'document_type'  => 'INV',
					'submission_uid' => $result->submissionUid ?? null,
					'uuid'           => $result->acceptedDocuments[0]->uuid ?? null,
					'response'       => json_encode($result),
					'error_message'  => !empty($result->rejectedDocuments)
										? einvoice_error_readable($result)
										: null,
					'controller_name' => $this->router->fetch_class(),
					'function_name'   => $this->router->fetch_method(),
				];

				if (isset($result->submissionUid) && !empty($result->submissionUid)) {

					$update_data = [
						'einvoice_submission_id' => $result->submissionUid,
						'einvoice_uuid'          => $result->acceptedDocuments[0]->uuid ?? null,
						'einvoice_status'        => 'P',
						'einvoice_longid'        => '',
						'bill_date'              => $row['bill_date'],
					];
					$this->bill_model->update_einvoice_param($row['bill_no'], $update_data);

					$einvoice_log['error_message'] = null;

					$msg = "E-Invoice successfully sent.";
					$return['status'] = true;
					$return['err'] = $msg;

					$this->load->model('action_log_model');
					$ctrl = $this->router->fetch_class();
					$method = $this->router->method;
					$esc_query_str = $this->db->escape_str("E-Invoice: {$row['bill_no']} Sent.");
					$action_desc   = "E-Invoice: {$row['bill_no']} Sent.";
					$this->action_log_model->save_action($ctrl, $method, $esc_query_str, $action_desc, 'insert');

				} else {

					$return['status'] = false;

					if (!empty($result->rejectedDocuments)) {
						$return['err'] = nl2br(einvoice_error_readable($result));
						$einvoice_log['error_message'] =einvoice_error_readable($result);
					} else {
						$return['err'] = "E-Invoice either already sent or no response from MyInvois system.";
						$einvoice_log['error_message'] = $return['err'];
					}
				}

				$this->einvoice_model->insert_log($einvoice_log);

				return $return;

				//log_message('error', $row['bill_no'].': '.$msg);

			} else {
				$return['err'] = 'Failed to Login to LHDN website.';
				return $return;
			}

		} else {
			log_message('error', $einvoice_folder.'/'.$row['bill_no'].'_signed.xml not found!');

			$return['err'] = $einvoice_folder.'/'.$row['bill_no'].'_signed.xml not found!';
			return $return;
		}

	}

	function resend_einvoice_cndn($bill_no)
	{
		$return = array('status' => false, 'err' => '');

		if (empty($bill_no)) {
			$return['err'] = 'No bill no provided.';
			return $return;
		}

		/*
		b = CN/DN bill
		be = CN/DN einvoice status
		bd = Draft record of CN/DN
		be2 = Related record einvoice status
		*/

    	$query_str = "SELECT b.*, bd.related_bill_no AS inv_bill_no, c.currency_code, be.einvoice_no, be.einvoice_submission_id, be.einvoice_uuid, be.einvoice_status, be.einvoice_longid, p.bill_addr_1, p.bill_addr_2, p.bill_addr_3, p.bill_postcode, p.bill_city, ss2.einvoice_code AS bill_state, p.acc_name, p.comp_name, p.tin AS cust_tin, p.ssm AS cust_brn, p.icno AS cust_icno, p.sst AS cust_sst, p.ttx AS cust_ttx, p.acc_mobileno AS cust_mobile, p.acc_email AS cust_email, c.inst_unit_no AS del_unit_no, c.inst_addr1 AS del_addr_1, c.inst_addr2 AS del_addr_2, c.inst_addr3 AS del_addr_3, c.inst_postcode AS del_postal, c.inst_city AS del_city, ss.einvoice_code AS del_state, c.inst_phone AS del_tel, c.inst_email AS del_email, c.payment_term, p.acc_type, be.einvoice_status, 'Invoice Adjustment' AS cn_reason, be2.einvoice_uuid AS inv_einvoice_uuid, be2.einvoice_status AS inv_einvoice_status, b.bill_date AS cn_date, p.nationality         
    	FROM bill b  
    	LEFT JOIN bill_einvoice be ON (b.bill_no = be.bill_no) 
    	LEFT JOIN bill_draft bd ON (b.bill_no = bd.bill_no) 
    	LEFT JOIN bill_einvoice be2 ON (bd.related_bill_no = be2.bill_no) 
    	LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
    	LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
    	LEFT JOIN `sys_state` ss ON (ss.state_code = c.`inst_state`) 
    	LEFT JOIN `sys_state` ss2 ON (ss2.state_code = p.`bill_state`) 
    	WHERE b.bill_no = ? ";
    	//echo $query_str; exit;

       	$query		= $this->db->query($query_str, array($bill_no));
    	$row = $query->row_array();

    	if (!isset($row['bill_no'])) {
			$return['err'] = 'Bill No not found.';
			return $return;
    	}

    	//check if einvoice status must not be valid or pending
       	if ($row['einvoice_status'] == 'S' || $row['einvoice_status'] == 'P') {
			$return['err'] = 'E-Invoice for this document already submitted or already valid.';
			return $return;
    	}

    	//check if this bill is void, if void cannot
       	if ($row['is_void'] != 0) {
			$return['err'] = 'Bill already voided.';
			return $return;
    	}

    	//related document must be valid
    	if ($row['inv_einvoice_status'] != 'S') {
			$return['err'] = 'Related document either not submitted to LHDN or not approved.';
			return $return;
    	}

      	$einvoice_type = $this->config->item('einvoice_type');
		$this->load->model('einvoice_xml_model');
		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');
		//$this->load->model('einvoice_json_model');

		$einvoice_folder = $this->config->item('einvoice_file_path');

		if ($row['bill_type'] == 'DN') {
			$einvoice_data = $this->bill_model->prepare_einvoice_dn_array( $row, ($einvoice_type=='JSON'?FALSE:TRUE) );
		} else {
			$einvoice_data = $this->bill_model->prepare_einvoice_cn_array( $row, ($einvoice_type=='JSON'?FALSE:TRUE) );
		}

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

		if (!$sign_succ) {
			$xml_error = $sign_err;
			$return['err'] = $xml_error;
			return $return;
		}

		sleep(1);

		$doc_name = $row['bill_type'].$row['bill_no'];

		$signed_xml_loc = $einvoice_folder.'/'.$doc_name.'_signed.xml';

		if (file_exists($signed_xml_loc)) {

			$login = $this->einvoice_config_model->login_myinvois_portal();

			if ($login) {

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

				$this->load->helper('einvoice');

				$einvoice_log = [
					'bill_no'        => $row['bill_no'],
					'document_type'  => $row['bill_type'] ?? 'INV',
					'submission_uid' => $result->submissionUid ?? null,
					'uuid'           => $result->acceptedDocuments[0]->uuid ?? null,
					'response'       => json_encode($result),
					'error_message'  => !empty($result->rejectedDocuments)
										? einvoice_error_readable($result)
										: null,
					'controller_name' => $this->router->fetch_class(),
					'function_name'   => $this->router->fetch_method(),
				];

				if (!empty($result->submissionUid)) {

					$update_data = [
						'einvoice_submission_id' => $result->submissionUid,
						'einvoice_uuid'          => $result->acceptedDocuments[0]->uuid ?? null,
						'einvoice_status'        => 'P',
						'einvoice_longid'        => '',
						'bill_date'              => $row['bill_date'],
					];
					$this->bill_model->update_einvoice_param($row['bill_no'], $update_data);

					$einvoice_log['error_message'] = null;

					$msg = "E-Invoice successfully sent.";
					$return['status'] = true;
					$return['err'] = $msg;

					$this->load->model('action_log_model');
					$ctrl  = $this->router->fetch_class();
					$method = $this->router->method;
					$esc_query_str = $this->db->escape_str("E-Invoice: {$row['bill_no']} Sent.");
					$action_desc = "E-Invoice: {$row['bill_no']} Sent.";
					$this->action_log_model->save_action($ctrl, $method, $esc_query_str, $action_desc, 'insert');

				} else {

					$return['status'] = false;

					if (!empty($result->rejectedDocuments)) {
						$return['err'] = nl2br(einvoice_error_readable($result));
						$einvoice_log['error_message'] = $return['err'];
					} else {
						$return['err'] = "E-Invoice either already sent or no response from MyInvois system (sent too many times).";
						$einvoice_log['error_message'] = $return['err'];
					}

					log_message('error', $return['err']);
				}

				$this->einvoice_model->insert_log($einvoice_log);

				return $return;

				//log_message('error', $row['bill_no'].': '.$msg);

			} else {
				$return['err'] = 'Failed to Login to LHDN website.';
				return $return;
			}

		} else {
			log_message('error', $einvoice_folder.'/'.$doc_name.'_signed.xml not found!');

			$return['err'] = $einvoice_folder.'/'.$doc_name.'_signed.xml not found!';
			return $return;
		}

	}

	function consolidated_einvoice($bill_arr)
	{
		$return = array('status' => false, 'err' => '');
		$submitted = false;

		if (empty($bill_arr)) {
			$return['err'] = 'No bill array provided.';
			return $return;
		}

		$this->load->model('einvoice_model');
		$next_consolidated_bill_no = $this->get_consolidated_new_no() + 1 ;

		//calc rough amount + tax
    	$query_str = "SELECT bd.*, sbt.class_codes, sbt.tax_type, sbt.name AS bill_type_name FROM bill_detail bd LEFT JOIN sys_bill_type sbt ON (bd.bill_type = sbt.bill_type_id) WHERE bd.bill_no IN (".implode(",", $bill_arr).") ";
    	$query		= $this->db->query($query_str);
    	$bill_detail = $query->result_array();

    	$amount = 0;
    	$tax = 0;
    	foreach ($bill_detail as $bill_row) {
    		$amount += $bill_row['amount'];
    		$tax += $bill_row['tax_amount'];
    	}

		//insert head
		$arr = array();
		$arr['inv_no'] = $next_consolidated_bill_no;
		$arr['inv_date'] = date('Y-m-d');
		$arr['amount'] = $amount;
		$arr['tax'] = $tax;
		$arr['einvoice_no'] = '';
		$arr['einvoice_submission_id'] = '';
		$arr['einvoice_uuid'] = '';
		$arr['einvoice_status'] = 'W';
		$arr['einvoice_longid'] = '';
		$this->einvoice_model->insert_consolidated_head($arr);

		//insert details
		foreach ($bill_arr as $bill_no) {
			$arr_detail = array();
			$arr_detail['inv_no'] = $next_consolidated_bill_no;
			$arr_detail['bill_no'] = $bill_no;
			$this->einvoice_model->insert_consolidated_detail($arr_detail);
		}

		//$consolidate_return = $this->resend_consolidate($next_consolidated_bill_no);

		//$return = $consolidate_return;
		$return = array('status' => true, 'err' => '');
		return $return;

	}

	function resend_consolidate($inv_no)
	{

		$return = array('status' => false, 'err' => '');

		if (empty($inv_no)) {
			$return['err'] = 'No inv no provided.';
			return $return;
		}

    	/*$query_str = "SELECT b.*, c.currency_code, be.einvoice_no, be.einvoice_submission_id, be.einvoice_uuid, be.einvoice_status, be.einvoice_longid, p.bill_addr_1, p.bill_addr_2, p.bill_addr_3, p.bill_postcode, p.bill_city, ss2.einvoice_code AS bill_state, p.acc_name, p.comp_name, p.tin AS cust_tin, p.ssm AS cust_brn, p.icno AS cust_icno, p.sst AS cust_sst, p.ttx AS cust_ttx, p.acc_mobileno AS cust_mobile, p.acc_email AS cust_email, c.inst_unit_no AS del_unit_no, c.inst_addr1 AS del_addr_1, c.inst_addr2 AS del_addr_2, c.inst_addr3 AS del_addr_3, c.inst_postcode AS del_postal, c.inst_city AS del_city, ss.einvoice_code AS del_state, c.inst_phone AS del_tel, c.inst_email AS del_email, c.payment_term, p.acc_type, be.einvoice_status      
    	FROM consolidated_einvoice b 
    	LEFT JOIN bill_einvoice be ON (b.bill_no = be.bill_no) 
    	WHERE b.inv_no = ? ";

    	$query		= $this->db->query($query_str, array($inv_no));
    	$row = $query->row_array();*/

		//construct einvoice array

   		$einvoice_type = $this->config->item('einvoice_type');
		$this->load->model('einvoice_xml_model');
		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');
		$this->load->model('einvoice_model');
		//$this->load->model('einvoice_json_model');

		$einvoice_folder = $this->config->item('einvoice_file_path');

		$einvoice_data = $this->bill_model->prepare_consolidated_einvoice_array( $inv_no, ($einvoice_type=='JSON'?FALSE:TRUE) );

		if (isset($einvoice_data['file_name'])) {
			$xml_return = array();
			$xml_return['status'] = 'err';
			$xml_return['msg'] = '';
			$sign_succ = false;

			//temporary use xml approach first
			$xml_return = $this->einvoice_xml_model->generate_xml_by_id( $einvoice_data, TRUE );

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

			if (!$sign_succ) {
				$xml_error = $sign_err;
				$return['err'] = $xml_error;
				return $return;
			}

			sleep(1);

			$doc_num = 'CO'.$inv_no;

			$signed_xml_loc = $einvoice_folder.'/'.$doc_num.'_signed.xml';

			if (file_exists($signed_xml_loc)) {

				$login = $this->einvoice_config_model->login_myinvois_portal();

				if ($login) {

					$xml_file = file_get_contents($signed_xml_loc);

					$send_documents = array();
					$send_documents[0]['format'] = 'XML';
					$send_documents[0]['documentHash'] = hash_file("sha256", $signed_xml_loc);
					$send_documents[0]['codeNumber'] = $doc_num;
					$send_documents[0]['document'] = base64_encode($xml_file);

					$senddata = array();
					$senddata['token'] = $_SESSION['einvoice']['access_token'];
					$senddata['api_path'] = $this->config->item('einvoice_api');
					$senddata['documents'] = $send_documents;
					$result = $this->einvoice_api_model->send_api( $senddata );

					if (isset($result->submissionUid)) {
						if (!empty($result->submissionUid)) {
							$update_data = array();
							$update_data['einvoice_submission_id'] = $result->submissionUid;
							$update_data['einvoice_uuid'] = $result->acceptedDocuments[0]->uuid;
							$update_data['einvoice_status'] = 'P';
							$update_data['einvoice_longid'] = '';
							$this->einvoice_model->update_consolidated_einvoice($inv_no, $update_data);
							//debugarr($result);
							$msg = "Consolidated e-invoice successfully sent.";

							//Log if einvoice sent
							$this->load->model('action_log_model');			
							$ctrl			= $this->router->fetch_class();
							$esc_query_str	= $this->db->escape_str( "Consolidated e-invoice: ".$inv_no." Sent." ) ;	
							$method 		= $this->router->method; 				
							$action_desc	= "Consolidated e-invoice: ".$inv_no." Sent.";	
							$action_category = 'insert';
							$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

							$return['status'] = true;
	 						$return['err'] = $msg;
							return $return;

						} else {

							log_message('error', "Returned with error:".print_r($result, true));
							$msg = "Returned with error:".print_r($result, true);

	 						$return['err'] = $msg;
							return $return;

						}
					} else {
						//error
						$return_arr = print_r($result, true);
						if (!empty($return_arr)) {
							$msg = "Returned with error:".print_r($result, true);
						} else {
							$msg = "E-Invoice either already sent or no response from myinvois system (sent too many times).";
						}

						log_message('error', $msg);

						$return['err'] = $msg;
						return $return;

					}

					//log_message('error', $row['bill_no'].': '.$msg);

				} else {
					$return['err'] = 'Failed to Login to LHDN website.';
					return $return;
				}

			} else {
				log_message('error', $einvoice_folder.'/'.$doc_num.'_signed.xml not found!');

				$return['err'] = $einvoice_folder.'/'.$doc_num.'_signed.xml not found!';
				return $return;
			}

		}

	}

	function void_einvoice($bill_no)
	{
		$return = array('status' => false, 'err' => '');

		if (empty($bill_no)) {
			$return['err'] = 'No bill no provided.';
			return $return;
		}

		$query_str = "SELECT * FROM bill_einvoice be LEFT JOIN bill b ON (be.bill_no = b.bill_no) WHERE be.bill_no = ? ";

		$query		= $this->db->query($query_str, array($bill_no));
    	$row = $query->row_array();

    	if (!isset($row['bill_no'])) {
			$return['err'] = 'Bill No not found.';
			return $return;
    	}

    	//check if einvoice status must not be valid or pending
       	if ($row['einvoice_status'] == 'C') {
			$return['err'] = 'E-Invoice for this bill already voided.';
			return $return;
    	}

    	//check if this bill is manual, if manual, maybe might be different vars

    	//check if this bill is void, if void cannot
       	if ($row['is_void'] != 0) {
			$return['err'] = 'Bill already voided.';
			return $return;
    	}

      	$einvoice_type = $this->config->item('einvoice_type');
		$this->load->model('einvoice_xml_model');
		$this->load->model('einvoice_config_model');
		$this->load->model('einvoice_api_model');

		$login = $this->einvoice_config_model->login_myinvois_portal();

		if ($login) {
			$senddata = array();
			$senddata['token'] = $_SESSION['einvoice']['access_token'];
			$senddata['api_path'] = $this->config->item('einvoice_api');
			$senddata['uuid'] = $row['einvoice_uuid'];
			$senddata['status'] = 'cancelled';
			$senddata['reason'] = 'Wrong Invoice Details';
			$result = $this->einvoice_api_model->cancel_invoice( $senddata );
			if (isset($result->status)) {
				if (!empty($result->status)) {
					switch(strtolower($result->status)) {
						case 'cancelled':
							$status = 'C';
							break;
						default:
							break;
					}
					$update_data = array();
					$update_data['einvoice_submission_id'] = $row['einvoice_submission_id'];
					$update_data['einvoice_uuid'] = $row['einvoice_uuid'];
					$update_data['einvoice_status'] = $status;
					$update_data['einvoice_longid'] = $row['einvoice_longid'];
					$update_data['bill_date'] = $row['bill_date'];
					$this->bill_model->update_einvoice_param($bill_no, $update_data);
					$msg = "E-Invoice successfully cancelled.";

					//Log if einvoice sent
					$this->load->model('action_log_model');			
					$ctrl			= $this->router->fetch_class();
					$esc_query_str	= $this->db->escape_str( "E-Invoice: ".$bill_no." Cancelled." ) ;	
					$method 		= $this->router->method; 				
					$action_desc	= "E-Invoice: ".$bill_no." Cancelled.";	
					$action_category = 'insert';
					$action_log =  $this->action_log_model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);

					$return['status'] = true;
					$return['err'] = $msg;
					return $return;

				} else {
						log_message('error', "Returned with error:".print_r($result, true));
						$msg = "Returned with error:".print_r($result, true);

 						$return['err'] = $msg;
						return $return;
				}
			} else {
				log_message('error', "Returned with error:".print_r($result, true));
				$msg = "Returned with error:".print_r($result, true);

					$return['err'] = $msg;
					return $return;
			}
		} else {
			$return['err'] = 'Failed to Login to LHDN website.';
			return $return;
		}

		return $return;

	}

	function check_last_month_prorated($customer_no, $last_month_ini, $last_month_end) {

		$return = false;

		//check any subscription fee bill in adjustment table for last month that is unproccessed for this customer, if found, skip this month's fee
		$sql = "SELECT * FROM bill_adjustment WHERE customer_no = ? AND tranx_date BETWEEN '".$last_month_ini->format('Y-m-d')."' AND '".$last_month_end->format('Y-m-d')."' AND bill_type = 1 AND is_lock = 0 LIMIT 1 ";
		$query = $this->db->query($sql, array($customer_no));
		$return_val = $query->row_array();

		if (isset($return_val['adj_no'])) {
			$return = true;
		}

		return $return;

	}

	function check_if_cycle_billed($customer_no, $month_start) {

		$return = false;
		if (empty($month_start)) {
			return $return;
		}

		//match by remark, very tricky
		$sql = "SELECT bill_no FROM bill_detail WHERE customer_no = ? AND remark LIKE '".$month_start."%' AND bill_type = 1 LIMIT 1 ";
		$query = $this->db->query($sql, array($customer_no));
		$return_val = $query->row_array();

		if (isset($return_val['bill_no'])) {
			$return = true;
		}

		return $return;

	}

	function get_cycle_month_array($first_activated_date, $bill_cycle_month) {

		if ($bill_cycle_month < 2) {
			//bill every month shouldnt be here
			return array();
		}

		if (date("d", strtotime($first_activated_date)) == '01') {
			//first of the day
			$start_date = date("Y-m-01", strtotime($first_activated_date));
		} else if (
			( (int)date("n", strtotime($first_activated_date)) == 1 && (int)date("j", strtotime($first_activated_date)) == 31 ) ||  
			( (int)date("n", strtotime($first_activated_date)) == 2 && (int)date("j", strtotime($first_activated_date)) >= 28 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 3 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 4 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 5 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 6 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 7 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 8 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 9 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 10 && (int)date("j", strtotime($first_activated_date)) == 31 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 11 && (int)date("j", strtotime($first_activated_date)) == 30 ) || 
			( (int)date("n", strtotime($first_activated_date)) == 12 && (int)date("j", strtotime($first_activated_date)) == 31 ) 
		) {
			//last of the day
			$start_date = date("Y-m-01", strtotime("+28 days", strtotime($first_activated_date)));
		} else {
			//other days
			$start_date = date("Y-m-01", strtotime("+1 months", strtotime($first_activated_date)));
		}

		$begin_month = date("n", strtotime($start_date));
		$xchk = 0;
		$next_bill = 0;
		$return_arr = array();
		while($xchk <= 24) {
			if (empty($return_arr)) {
				$return_arr[] = $begin_month;
				$xchk = $xchk + $begin_month;
				$next_bill = $begin_month;
			} else {
				$next_bill = $next_bill + $bill_cycle_month;
				if ($next_bill > 12) {
					$next_bill = $next_bill - 12;
				}
				$return_arr[] = $next_bill;
				$xchk = $xchk + $bill_cycle_month;
			}
		}

		return $return_arr;

	}

	function update_einvoice_submit_time($bill_no) {
		$this->db->query("UPDATE `bill_einvoice` SET updated_on = now() WHERE bill_no = ?", array($bill_no));
	}

	function update_einvoice_cn_submit_time($bill_no) {
		$this->db->query("UPDATE `bill_einvoice_cn` SET updated_on = now() WHERE bill_no = ?", array($bill_no));
	}

	function get_einvoice_details($bill_no) {
		$query = $this->db->query('SELECT * FROM bill_einvoice WHERE bill_no = ?',[$bill_no]);
		return $query->row_array();
	}

	function get_overdue_obj($customer_no) {
		if (empty($customer_no)) {
			return array();
		}

		$return_obj = array();

		//get current customer payment term
		$sql = "SELECT b.*, bd.payment_term AS bd_payment_term, c.payment_term AS c_payment_term FROM bill b LEFT JOIN bill_header_extra bd ON (b.bill_no = bd.bill_no) LEFT JOIN customer c ON (b.customer_no = c.customer_no)
		WHERE b.customer_no = ? ORDER BY b.bill_date ASC";
		$query = $this->db->query($sql, [$customer_no]);
		$bill_detail = $query->result_array();

		//get list of payment to match 
		$sql2 = "SELECT * FROM payment WHERE customer_no = ? ORDER BY pay_date ASC";
		$query = $this->db->query($sql2, [$customer_no]);
		$temp_payment_detail = $query->result_array();
		$payment_detail = array();
		foreach ($temp_payment_detail as $p) {
			if (isset($payment_detail[$p['pay_date']])) {
				//key found, so just add
				$payment_detail[$p['pay_date']] += $p['amount'];
			} else {
				$payment_detail[$p['pay_date']] = $p['amount'];
			}
		}

		$balance = 0;

		$outstanding = 0;
		$overdue = array();
		foreach ($bill_detail as $b) {

			//one bill cycle

			$payment_term = $b['c_payment_term'];
			if (isset($b['bd_payment_term'])) {
				if (!empty($b['bd_payment_term'])) {
					$payment_term = $b['bd_payment_term'];
				}
			}

			//for each bill record, use payment term + bill date to find out period where payment counts
				//do a search in payment array and find all payment record and add into it
				//if cleared, then remove from obj, leftover can go next bill

			//so now we know what is the requisite for overdue which is bill_date + term, we can then check if within this period are there are payments and if there is, how much is overdue...
			//we can then flag that specific bill as having a certain "overdue" amount then continue onwards
			$start_date = $b['bill_date'];
			$temp_date = new DateTimeImmutable($start_date);
			$end_date = $temp_date->modify('+'.$payment_term.' days')->format('Y-m-d');

			$to_pay = $b['amount'];
			foreach ($payment_detail as $pay_date => $pay_amt) {
				if (strtotime($pay_date) > strtotime($end_date)) {
					//payment period passed, consider overdue amt
					if ($to_pay <= 0) {
						//finished paying this bill
					} else {
						//flag
						//this to_pay amount will be the amount overdue passed payment term
						$days_past_overdue = floor( (strtotime($pay_date) - strtotime($end_date))/(24*60*60) );
						$overdue[$b['bill_no']]['amt'] = $to_pay;
						$overdue[$b['bill_no']]['days'] = $days_past_overdue;
						$overdue[$b['bill_no']]['nearest_paydate'] = $pay_date;
						$overdue[$b['bill_no']]['bill_date'] = $b['bill_date'];
					}

					//cycle stopped
					break;
				} else {
					//within payment period
					$to_pay -= $pay_amt;
					unset($payment_detail[$pay_date]);
				}
			}

			//do summary - any amount not yet paid
			$balance += $to_pay;
		}

		$return_obj['overdue'] = $overdue;
		$return_obj['latest_balance'] = $balance;

		return $return_obj;

	}

	function get_bill_row($bill_no)
	{
   		$query		= $this->db->query("SELECT * FROM `bill` WHERE bill_no = ?", array($bill_no));
    	$row = $query->row_array();
    	return $row;
	}

	function get_autocomplete_load_bill($customer_no, $keyword)
	{
		$keyword = $this->db->escape_str($keyword);
		$customer_no = $this->db->escape_str($customer_no);

		if (empty($customer_no)) {
			return array();
		}

		$query_str = "	SELECT b.*, c.name AS customer_name, DATE_FORMAT(bill_date, '%M %Y') AS bill_date_text   
						FROM bill b 
						LEFT JOIN customer c ON (b.customer_no = c.customer_no) 
						WHERE b.bill_no LIKE '%$keyword%' AND c.customer_no = ?  
						ORDER BY b.bill_date DESC LIMIT 10 ";
		$query = $this->db->query($query_str, [$customer_no]);
		return $query->result_array(); 

	}

}