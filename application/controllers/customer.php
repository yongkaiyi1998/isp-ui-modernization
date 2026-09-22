<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
//~ include_once( APPPATH . 'controllers/package.php' );

class Customer extends DataPage_Controller {

	private $e_key = "1nf0n4l";
	
	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->helper(array('url','html','form'));
		
		check_acl('customer');
		$this->load->model('customer_model');
		$this->load->model('common_model');
		$this->load->helper('image_helper');

		$this->load->helper('puppeteer_helper');

		$this->load->model('package_model');
		$this->load->model('docs_log_model');
		$this->load->model('router_model');
		$this->load->model('email_model');

		$this->account_status = array('P' => 'Signup', 'A' => 'Activated', 'S' => 'Suspended', 'T' => 'Terminated', 'C' => 'Cancelled');

		$this->termination_status = array(
			'P' => 'Pending Send',
			'S' => 'Pending Customer Signature',
			'F' => 'Customer Rejected',
			'D' => 'Pending Confirmation',
			'C' => 'Confirmed',
			'A' => 'Cancelled',
		);
    }
	
	function save_customer()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'customer', 'error_keys' => array());

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day' ");
		$bill_cycle_day = $config_record[0]['val'];
		$cronjob_date = date('Y-m-'.$bill_cycle_day);

		if ($this->input->post('btDelete') != '' && $this->input->post('customer_no') != '')
		{
			//$this->session->set_flashdata("warning_msg", 'System not allow to delete customer');
			//~ $this->edit_customer($this->input->post('customer_no'));

			//AJAX - if ajax, then no need gen_post, just display err to user
			$ajax_return['err_msg'] = 'System does not allow to delete account';
			$ajax_return['error_keys'] = array();
			echo json_encode($ajax_return);
			return false;

			redirect("customer/edit_customer/".$this->input->post('customer_no'));
		}else 
		{
			$this->set_form_validation('save', (($this->input->post('old_package_id') != $this->input->post('package')) && ($this->input->post('prev_status') == 'A')));
			if($this->form_validation->run() == false) 
			{
				//$this->msg['error_msg'] = validation_errors();

				//AJAX - if ajax, then no need gen_post, just display err to user
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;

				//if ($this->input->post('customer_no')=='')  $this->add_customer();				
				//else  $this->edit_customer($this->input->post('customer_no'));				
			}
			else 
			{
				$post_data = $this->input->post();
				$customer_no = '';
				$cust_details = '';
				//echo "<pre>"; print_r($post_data); echo "</pre>"; exit;
				
				/*
				//if is blank for password, will automatic fill in ic/password for residential or fill in company reg no for business/wholesale
				$login_password = $this->db->escape_str($this->input->post('login_password'));
				if ( empty($login_password) && $this->input->post('category') == 'r' ) {
					
					if ( $this->input->post('nric') !== '' ) {
						$post_data['login_password'] = str_replace('-', '', $this->db->escape_str($this->input->post('nric')) );
					}else{
						$post_data['login_password'] = str_replace('-', '', $this->db->escape_str($this->input->post('passport')) );
					}
					
				}elseif ( empty($login_password) && $this->input->post('category') != 'r' ){

					$post_data['login_password'] = str_replace('-', '', $this->db->escape_str($this->input->post('reg_no')) );

				}
				*/
				
				$package = $this->input->post('package');
				if ( empty($package) ) {
					$package = 0;
				}

				if (($post_data['reset_bill_waive_period'] ?? 0) == 1) {
					$post_data['bill_waive_period'] 	= 0;
					$post_data['free_package_upgrade'] 	= 0;
					$post_data['upgrade_package_id'] 	= 0;
				}

				$post_data['preferred_install_datetime'] = '';
				if (!empty($post_data['preferred_install_date']) && !empty($post_data['preferred_install_time'])) {
					$post_data['preferred_install_datetime'] = str_replace('/', '-', $post_data['preferred_install_date']) . ' ' . $post_data['preferred_install_time'] . ':00';
				}
				
				//$control_by = strtolower( $_SESSION['config']['radius_or_cisco'] ) == 'radius' ? 'radius' : 'cisco' ; 
				$control_by = 'cisco';
				
				//insert into
				
				if ( $post_data['customer_no'] == '' ) 
				{
					
					if (!empty($_SESSION['config']['radius_suffix'])) {
						if( strstr( $post_data['login_username'], $_SESSION['config']['radius_suffix'] ) === false ){
							$post_data['login_username'] = $post_data['login_username'].$_SESSION['config']['radius_suffix'];
						}
					}

					 //_debug_array($this->input->post()); exit;
					$result = $this->customer_model->customer_insert($post_data,$package,$this->user['username'],'');	
					
					$customer_no = (empty($result['customer_no']))?'':$result['customer_no'];	
					
					$this->customer_model->create_activation_fee($customer_no,$this->user['username'],$package);

					$cust_details = $this->customer_model->get_customer( $customer_no );
					
					//bill waive must be zero
					if ($cust_details['current_status'] == 'A') {

						if (empty($cust_details['bill_waive_period'])) { 
							//got case where prorated bill was created if activated date started way earlier... also might also be way later... 
							$transact_md = date("ym", strtotime($cust_details['transact_date']));
							if ((int)$transact_md == (int)date("ym")) {
								$this->create_prorated_bill($customer_no, 
												$this->db->escape_str($this->input->post('monthly_charge')), 
												$cust_details['transact_date'],
												'',
												'');
							}
						} else {

							$chk_waiver = $this->customer_model->chk_customer_bill_waiver($post_data['customer_no']);

							if (!empty($cust_details['bill_waive_period']) && strtotime($cust_details['transact_date']) > 0 && !$chk_waiver) {
								//check if activate day of the month is first day, if first day , dont add prorate

								$waiver_arr = $this->customer_model->add_bill_waiver($post_data['customer_no'], $package, $cust_details['transact_date'],$cust_details['bill_waive_period']);

								if (date("d", strtotime($cust_details['transact_date'])) != '01') {
									$delay_trial_start = $cust_details['delay_trial_start'] ?? 0;
									$transact_ts = strtotime($cust_details['transact_date']);
									
									$ts = $delay_trial_start == 1
											? $transact_ts
											: strtotime("+{$cust_details['bill_waive_period']} months", $transact_ts);

									$after_waiver_date = date(
										'Y-m-d',
										date('d', (int)$ts) != date('d', $transact_ts)
											? strtotime('last day of previous month', (int)$ts)
											: (int)$ts
									);
											
									$this->create_prorated_bill_for_waiver($post_data['customer_no'], 
														$this->db->escape_str($this->input->post('monthly_charge')), 
														$cust_details['transact_date'],
														'',
														'',
														$after_waiver_date);

									/* Val 31-10-25 new Prorate Adjustment
									$this->create_prorated_bill_for_waiver($post_data['customer_no'], 
														$this->db->escape_str($this->input->post('monthly_charge')), 
														$waiver_arr['start_charge'],
														'',
														'',
														$waiver_arr['start_charge']);
									*/
								}
							}

						}
					
						$transact_date = strtotime($cust_details['transact_date']);
						$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day'");
						$bill_cycle_day = (int)$config_record[0]['val'];
						$today = strtotime(date('Y-m-d'));

						$next_bill_month = strtotime(date('Y-m-01', $today));
						$bill_day_in_next_month = date('Y-m-', $next_bill_month) . str_pad($bill_cycle_day, 2, '0', STR_PAD_LEFT);

						if ($today >= strtotime($bill_day_in_next_month)) {
							$next_bill_month = strtotime('+1 month', $next_bill_month);
						}

						$next_bill_date = date('Y-m-01', $next_bill_month);

						if( $cust_details['next_bill_date'] == '' || $cust_details['next_bill_date'] == NULL )
						{
							$this->customer_model->customer_update_next_bill_date($post_data['customer_no'],$next_bill_date);
						}
					}

					if( $control_by == 'radius' ){
					
						$login_username = $post_data['login_username'];
						$status 		= $post_data['status'] == 'r' ? 'accept' : 'reject' ; 
						$password 		= $post_data['login_password'];

						$this->customer_model->radius_account_status( $login_username, $status );
						$this->customer_model->radius_account_password( $login_username, $password );
							
					}elseif( $control_by == 'cisco' ){

						$login_username = $post_data['login_username'];
						$status 		= $post_data['status'] == 'r' ? 'accept' : 'reject' ; 
						$password 		= $post_data['login_password'];

						if( $status == 'accept' ){
							$this->customer_model->cisco_account_upsert( $login_username, $password );
							
							if( $post_data['framed_ip_address'] != '' || $post_data['framed_route'] != '' || $post_data['framed_netmask'] != '' || $post_data['framed_ipv6_address'] != '' || $post_data['framed_ipv6_route'] != ''){
								$this->customer_model->cisco_fixed_ip_upsert( $login_username, 
																				$post_data['framed_ip_address'],
																				$post_data['framed_route'],
																				$post_data['framed_netmask'],
																				$post_data['framed_ipv6_address'],
																				$post_data['framed_ipv6_route'] ) ;
							}
						}
						
						// if ($cust_details['current_status'] == 'A') {

						// 	//if DIA customer, activate the script
						// 	if ( 
						// 		( ($post_data['category'] == 'd' || $post_data['category'] == 'e') ) && 
						// 		( ($post_data['interface_id'] != '') ) &&
						// 		(!empty($post_data['router_id'])) 
						// 		) {

						// 		$this->customer_model->dia_jumpstart($customer_no, 'up');

						// 	}

						// }
						
					}

					//send installer form to installer if installer is selected
					if (!empty($post_data['installer_name'])) {
						$this->customer_model->initiate_send_installation_form($customer_no, $post_data['installer_name']);
					}

					//if activated and free package upgrade, reinsert radius param for chosen upgrade package
					if ($cust_details['current_status'] == 'A' && !empty($cust_details['free_package_upgrade'])) {
						$this->customer_model->enable_free_upgrade($customer_no, $cust_details['upgrade_package_id']);
					}
					
				}
				else 
				{//update

					$customer_no = $post_data['customer_no'];

					$prev_cust = $this->customer_model->get_customer( $post_data['customer_no'] );

					$post_data['package'] = $prev_cust['package'];

					$this->customer_model->customer_update($post_data,$package,$this->user['username'],$post_data['customer_no']);

					$cust_details = $this->customer_model->get_customer( $post_data['customer_no'] );

					if( $cust_details['current_status'] == 'A' && ($cust_details['current_status'] != $prev_cust['current_status']) ){
						
						//only create if bill waiver count is zero

						if (empty($cust_details['bill_waive_period'])) { 
							$transact_md = date("ym", strtotime($cust_details['transact_date']));
							if ((int)$transact_md == (int)date("ym")) {
								$this->create_prorated_bill($post_data['customer_no'], 
												$this->db->escape_str($this->input->post('monthly_charge')), 
												$cust_details['transact_date'],
												'',
												'');
							}
						} else {
							//got bill waiver

							$chk_waiver = $this->customer_model->chk_customer_bill_waiver($post_data['customer_no']);

							if (!empty($cust_details['bill_waive_period']) && strtotime($cust_details['transact_date']) > 0 && !$chk_waiver) {
								//should be +1 because prorate cannot count

								$waiver_arr = $this->customer_model->add_bill_waiver($post_data['customer_no'], $package, $cust_details['transact_date'],$cust_details['bill_waive_period']);
								
								if (date("d", strtotime($cust_details['transact_date'])) != '01') {
									$delay_trial_start = $cust_details['delay_trial_start'] ?? 0;
									$transact_ts = strtotime($cust_details['transact_date']);

									$ts = ($delay_trial_start == 1)
											? $transact_ts
											: strtotime("+{$cust_details['bill_waive_period']} months", $transact_ts);

									$after_waiver_date = date(
										'Y-m-d',
										date('d', (int)$ts) != date('d', $transact_ts)
											? strtotime('last day of previous month', (int)$ts)
											: (int)$ts
									);

									$this->create_prorated_bill_for_waiver($post_data['customer_no'], 
													$this->db->escape_str($this->input->post('monthly_charge')), 
													$cust_details['transact_date'],
													'',
													'',
													$after_waiver_date);

									/* Val 31-10-25 new Prorate Adjustment
									$this->create_prorated_bill_for_waiver($post_data['customer_no'], 
													$this->db->escape_str($this->input->post('monthly_charge')), 
													$waiver_arr['start_charge'],
													'',
													'',
													$waiver_arr['start_charge']);
									*/
								}
							}
						}	

						$chk_free_package_upgrade = $this->customer_model->chk_customer_free_upgrade($post_data['customer_no']);
						if (!$chk_free_package_upgrade 
							&& !empty($cust_details['free_package_upgrade']) 
							&& !empty($cust_details['upgrade_package_id']) 
							&& strtotime($cust_details['transact_date']) > 0) 
						{
							$this->customer_model->add_free_upgrade($post_data['customer_no'], $cust_details['upgrade_package_id'], $cust_details['transact_date'],$cust_details['free_package_upgrade']);
						}

						//just changed to status "A", check if next bill date empty only update
						// $next_bill_date = date( 'Y-m-01' , strtotime( " +1 month " , strtotime( date('Y-m-d H:i:s') ) ) ) ;
						$transact_date = strtotime($cust_details['transact_date']);
						$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bill_generate_day'");
						$bill_cycle_day = (int)$config_record[0]['val'];
						$today = strtotime(date('Y-m-d'));

						$next_bill_month = strtotime(date('Y-m-01', $today));
						$bill_day_in_next_month = date('Y-m-', $next_bill_month) . str_pad($bill_cycle_day, 2, '0', STR_PAD_LEFT);

						if ($today >= strtotime($bill_day_in_next_month)) {
							$next_bill_month = strtotime('+1 month', $next_bill_month);
						}

						$next_bill_date = date('Y-m-01', $next_bill_month);

						if( $cust_details['next_bill_date'] == '' || $cust_details['next_bill_date'] == NULL )
						{
							$this->customer_model->customer_update_next_bill_date($post_data['customer_no'],$next_bill_date);
						}
					}
					
					//$control_by = strtolower( $_SESSION['config']['radius_or_cisco'] ) == 'radius' ? 'radius' : 'cisco' ;
					$control_by = 'cisco'; 

					if( $control_by == 'radius' ){
					
						//deactivate radius if this is not residential
						/*if( $prev_cust['category'] != $post_data['category'] && $prev_cust['category'] == 'r' ){
							$this->customer_model->radius_account_status( $prev_cust['login_username'], 'reject' );
						}elseif( $post_data['category'] == 'r' ){*/
						
							//~ if( ( $post_data['status'] != $prev_cust['status'] )
								//~ || ( $post_data['login_username'] != $prev_cust['login_username'] )
								//~ || ( $post_data['login_password'] != '' && $post_data['login_password'] != $prev_cust['login_password'] )
							//~ ){
								$radius_suffix = $_SESSION['config']['radius_suffix'] ;
								$old_username = $prev_cust['login_username'] ;
								//~ $old_username = $prev_cust['login_username'] . $radius_suffix ;
								$new_username = $post_data['login_username'] ;
								//~ $new_username = $post_data['login_username'] . $radius_suffix ;
								
								//change username ? NOT ALLOW TO CHANGE USER NAME
								
								if( $post_data['login_username'] != $prev_cust['login_username'] ){
									$this->customer_model->radius_account_username_update( $old_username, $new_username );
								}
								
								//change status
								//if( $post_data['status'] != $prev_cust['status'] ){
									$status = $post_data['status'] == 'r' ? 'accept' : 'reject' ; 
									$this->customer_model->radius_account_status( $new_username, $status );
								//}
								
								//change password
								if( $post_data['login_password'] != '' && $post_data['login_password'] != $prev_cust['login_password'] ){
									$password = $post_data['login_password'];
									$this->customer_model->radius_account_password( $new_username, $password );
								}
								
							//}
						
							// when change package do not update the usergroup immediately
							// will update in every day cron job
							//change package, update usergroup in radius
							// if( $package != $prev_cust['package'] ){
							// 	//$radius_suffix = $_SESSION['config']['radius_suffix'];
							// 	//$login_username = $post_data['login_username'] . $radius_suffix ;
							// 	$login_username = $post_data['login_username'] ;
								
							// 	$this->load->model('package_model');
							// 	$package_info = $this->package_model->get_package( $package );
							// 	$this->customer_model->radius_usergroup_upsert( $login_username, $package_info['bandwidth'] );
							// }
						
						//}
					
					}elseif( $control_by == 'cisco' ){

						$cust_details = $this->customer_model->get_customer( $post_data['customer_no'] );
						
						$login_username = $post_data['login_username'];
						$password = $post_data['login_password'];
						
						if( ($cust_details['current_status'] == 'S' || $cust_details['current_status'] == 'T') && ($cust_details['current_status'] != $prev_cust['current_status']) ){
							//remove from radcheck & usergroup
							//$this->customer_model->cisco_account_remove( $login_username );
							//$this->customer_model->cisco_fixed_ip_remove( $login_username );
							//$this->customer_model->cisco_usergroup_remove( $login_username );

							//if want to suspend or terminate now
							$this->customer_model->varied_suspend_account( $post_data['customer_no'] );

						}else{
							
							$this->customer_model->cisco_account_upsert( $login_username, $password, $prev_cust['login_username'] );
							//~ if( $package != $prev_cust['package'] ){
								$this->load->model('package_model');
								$package_info = $this->package_model->get_package( $prev_cust['package'] );
								
								//if( $post_data['category'] == 'b' )
									//$package_info['bandwidth'] = 'BIZ' . $package_info['bandwidth'];
								if ($cust_details['current_status'] != 'P' && !$this->customer_model->chk_customer_free_upgrade_period($prev_cust['customer_no'])) {
									$this->customer_model->cisco_usergroup_upsert( $login_username, $package_info['bandwidth'], $prev_cust['login_username'] );
								}
							//~ }
							
							if( $post_data['framed_ip_address'] != '' || $post_data['framed_route'] != '' || $post_data['framed_netmask'] != '' || $post_data['framed_ipv6_address'] != '' || $post_data['framed_ipv6_route'] != ''){
									$this->customer_model->cisco_fixed_ip_upsert( $login_username, 
																					$post_data['framed_ip_address'],
																					$post_data['framed_route'],
																					$post_data['framed_netmask'],
																					$post_data['framed_ipv6_address'],
																					$post_data['framed_ipv6_route'],
																					$prev_cust['login_username'] ) ;
							}else if( $post_data['framed_ip_address'] == '' && $post_data['framed_route'] == '' && $post_data['framed_netmask'] == '' && $post_data['framed_ipv6_address'] == '' && $post_data['framed_ipv6_route'] == '' ){
								$this->customer_model->cisco_fixed_ip_remove( $login_username ) ;
							}

							//if DIA customer, activate the script

							//if( ($cust_details['current_status'] == 'A') && ($cust_details['current_status'] != $prev_cust['current_status']) ){

							// if ($cust_details['current_status'] == 'A') {
							// 	if (
							// 		in_array($post_data['category'], ['d', 'e']) &&
							// 		!empty($post_data['interface_id']) &&
							// 		!empty($post_data['router_id'])
							// 	) {
							// 		if ($prev_cust['current_status'] != 'A') {
							// 			$this->customer_model->dia_jumpstart($post_data['customer_no'], 'up');
							// 		}
							// 	}

							// }

							//}

							
						}
						
					}

					if ($cust_details['new_package_id'] != $prev_cust['new_package_id']) {
						$this->customer_model->process_bill_waiver($cust_details);
					}
					
					$this->customer_model->update_radius_on_package_change($customer_no);

					//Automatic send installer form to installer if installer selected and also got changed
					if ( !empty($post_data['installer_name']) && ($post_data['installer_name'] != $prev_cust['installer_name']) ) {
						$this->customer_model->initiate_send_installation_form($post_data['customer_no'], $post_data['installer_name']);
					}

					//if activated and free package upgrade, reinsert radius param for chosen upgrade package
					$cust_details = $this->customer_model->get_customer( $post_data['customer_no'] );
					if( $cust_details['current_status'] == 'A' && ($cust_details['current_status'] != $prev_cust['current_status']) && !empty($cust_details['free_package_upgrade']) ){
						$this->customer_model->enable_free_upgrade($post_data['customer_no'], $cust_details['upgrade_package_id']);
					} else if ($cust_details['current_status'] == 'A' && ($cust_details['free_package_upgrade'] != $prev_cust['free_package_upgrade']) && !empty($cust_details['free_package_upgrade'])) {

						//only upgrade if still within period of free upgrade from activated date
						$period_end_date = strtotime("+".$cust_details['free_package_upgrade']." months", strtotime($cust_details['transact_date']));
						if ($period_end_date > strtotime(date('Y-m-d 00:00:00'))) {
							$this->customer_model->enable_free_upgrade($post_data['customer_no'], $cust_details['upgrade_package_id']);
						}
					}
				}
				
				//Create JO in customer support?

				//all done
				
				/*if(empty($_POST['url_after_save']))
				{
					$this->session->set_flashdata("msg", 'Customer Saved!');
					// redirect("customer");
					$ajax_return['url'] = 'customer';
				}
				else
				{
					// redirect($_POST['url_after_save']);
					$ajax_return['url'] = $_POST['url_after_save'];
				}*/

				if (isset($post_data['charge_name'])) {
					$other_charge_arr = array();

					foreach ($post_data['charge_name'] as $item_key => $item_value) {
						$other_charge_arr[$item_key]['charge_name'] = $item_value;
						$other_charge_arr[$item_key]['charge_type'] = $post_data['charge_type'][$item_key] ?? 31;
						$other_charge_arr[$item_key]['charge_amount'] = $post_data['charge_amount'][$item_key] ?? 0.00;
						$other_charge_arr[$item_key]['charge_remark'] = $post_data['charge_remark'][$item_key] ?? '';
						$other_charge_arr[$item_key]['charge_end_date'] = $post_data['charge_end_date'][$item_key] ?? '';
					}

					$this->customer_model->update_other_charges($customer_no, $other_charge_arr);
				} else {
					$this->customer_model->clear_other_charges($customer_no);
				}

				//Audit, code very messy, no time to refactor yet, put it down here for clearer reference
				if ($cust_details['current_status'] == 'A')
				{
					$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'audit_copy' ");
					$audit_copy = $config_record[0]['val'];

					$audit_file_path = $this->config->item('upload_path').'/temp/pdf/installation_form_'.$customer_no.'.pdf';
					$audit_file_name = 'installation_form_'.$customer_no.'.pdf';

					if ($audit_copy == '1') {
						$chk_audit = $this->common_model->search_audit($audit_file_path, 'customer_install', $audit_file_name, $customer_no);
						if ($chk_audit) {
							//already got, so skip
							log_message('error', 'Customer Installation Form:'.$customer_no.' Not generated because another same installation form already exists in audit folder.');
						} else {
							//save to audit folder
							$scode = md5($customer_no . $this->e_key);
							puppeteer_print_preview(
								$this->config->item('base_url').'pdfapi/installation_form/'.$customer_no.'/'.$scode, 
								$this->config->item('upload_path').'/temp/pdf/installation_form_'.$customer_no.'.pdf', 
								$this->config->item('proj_path'), 
								$this->config->item('chrome_loc')
							);

							//save to audit folder
							$this->common_model->perform_audit(
								$audit_file_path, 
								'customer_install', 
								$audit_file_name, 
								$customer_no, 
								'Customer Installation Form:'.$customer_no, 
								'', 
								'', 
								$customer_no
							);

						}
					}
				}

				if(empty($_POST['url_after_save']))
				{
					$this->session->set_flashdata("msg", 'Customer Saved!');
					// redirect("customer");
					$ajax_return['url'] = 'customer';
				}
				else
				{
					// redirect($_POST['url_after_save']);
					$ajax_return['url'] = $_POST['url_after_save'];
				}

				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
	
	//Ajax - Function
	function autocomplete_load_customer()
	{
		$return_val = array();
		$customer = $this->customer_model->get_autocomplete_load_customer($this->input->post('keyword'));
		
		$return_val = $customer;
		
		//~ $customer_pic_1[] = array(	
									//~ 'pic_id' => '',
									//~ 'customer_no' => $customer['customer_no'], 
									//~ 'pic_name' => $customer['pic_name'], 
									//~ 'tel_num' => $customer['tel_num'], 
									//~ 'fax_num' => $customer['fax_num'], 
									//~ 'pic_name' => $customer['pic_name'], 
									//~ 'mobile_num' => $customer['mobile_num'], 
									//~ 'email_1' => $customer['email_1'], 
									//~ 'email_2' => $customer['email_2'], 
									//~ 'nric' => $customer['nric'], 
									//~ 'passport' => $customer['passport'], 
									//~ 'date_of_birth' => $customer['date_of_birth'], 
									//~ 'race' => $customer['race'], 
									//~ 'gender' => $customer['gender']
							//~ );
		
		//~ $customer_pic_2 = $this->customer_model->get_customer_pic( $this->input->post('keyword') );
		
		//~ $return_val['pic'] = array_merge( $customer_pic_1 , $customer_pic_2 );
		
		echo json_encode($return_val);
	}
	
	function package_history($customer_no = '')
	{
		//~ $query_str = "SELECT cph.package_name, cph.monthly_charge, cph.start_date, cph.end_date " . 
					//~ "FROM customer_package_history cph " . 
					//~ "WHERE cph.customer_no = '" . $customer_no . "' " .
					//~ "ORDER BY cph.start_date ";
		//~ 
		//~ $query = $this->db->query($query_str);
		//~ if ($query->num_rows() > 0)	
			//~ $row = $query->result_array();
		//~ else 
			//~ $row = array();
		//~ $data['row_data'] = $row;
		
		$result 			= $this->customer_model->get_package_history($customer_no);
		$data['row_data'] 	= $result['row'];		
		$data['page_title'] = 'Package History';
		$this->parser->parse('customer/package_history',$data);	
	}

	function status_history($customer_no = '')
	{
		$result 			= $this->customer_model->get_customer_history($customer_no); 

		$account_status = $this->account_status;
		foreach ($result['row'] as $cnt => $row) {
			$result['row'][$cnt]['status_text'] = (isset($account_status[$row['status']]) ? $account_status[$row['status']] : '');

			if (!empty($row['transact_date'])) {
				$result['row'][$cnt]['transact_date'] = date('Y-m-d', strtotime($row['transact_date']));
			}
		}
		
		$data['row_data'] 	= $result['row'];		
		$data['page_title'] = 'Status History';
		$this->parser->parse('customer/status_history',$data);
	}

	function termination_history($customer_no = '')
	{
		$result 			= $this->customer_model->get_termination_history($customer_no); 

		$termination_status = $this->termination_status;
		foreach ($result['row'] as $cnt => $row) {
			$result['row'][$cnt]['status_text'] = (isset($termination_status[$row['status']]) ? $termination_status[$row['status']] : '');

			if (!empty($row['transact_date'])) {
				$result['row'][$cnt]['transact_date'] = date('Y-m-d H:i:s', strtotime($row['transact_date']));
			}
		}
		
		$data['row_data'] 	= $result['row'];		
		$data['page_title'] = 'Termination Flow Log';
		$this->parser->parse('customer/termination_history',$data);	
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
			$month_ini = new DateTime(date("Y-m-1", strtotime($activated_date)));

			if( $end_date != "" )
				$month_end = new DateTime( date( 'Y-m-d' , strtotime($end_date) ) );							
			else
				$month_end = new DateTime(date("Y-m-t", strtotime($activated_date)));	

			//check if activated_date is less than this month's end
			if (strtotime($activated_date) < strtotime($month_end->format('Y-m-d'))) {		

				$total_days = $month_ini->format('t');
				$total_used_days = $month_end->diff( new datetime($activated_date) )->format('%a') + 1; // + 1 because need to including the day start using
				$amount = ($monthly_charge / $total_days * $total_used_days );
				
				//$obj_adj = new Adjustment();
				$adj_remark = 'Prorated Bill From ' . date('Y-m-d',strtotime($activated_date)) . ' UNTIL ' . $month_end->format('Y-m-d');
				
				$this->load->model('adjustment_model');
				$this->adjustment_model->insert_adj('i', $customer_no, '', '', $activated_date, '41', 'dr', $amount, $adj_remark, 1);

			}
			
		}
	}

	function create_prorated_bill_for_waiver($customer_no, $monthly_charge, $activated_date, $suspended_date,$terminated_date, $charge_date)
	{
		if ( !empty($activated_date) ) {
			
			$end_date = "";
			if( $suspended_date != '0000-00-00' && $suspended_date != '' )
				$end_date = $suspended_date; 
			elseif( $terminated_date != '0000-00-00' && $terminated_date != '' )
				$end_date = $terminated_date; 
			
			//Pro-rate
			$month_ini = new DateTime(date("Y-m-1", strtotime($activated_date)));

			if( $end_date != "" )
				$month_end = new DateTime( date( 'Y-m-d' , strtotime($end_date) ) );							
			else
				$month_end = new DateTime(date("Y-m-t", strtotime($activated_date)));	

			//check if activated_date is less than this month's end
			if (strtotime($activated_date) <= strtotime($month_end->format('Y-m-d'))) {		

				$total_days = $month_ini->format('t');
				$total_used_days = $month_end->diff( new datetime($activated_date) )->format('%a') + 1; // + 1 because need to including the day start using
				$amount = ($monthly_charge / $total_days * $total_used_days );
				
				//$obj_adj = new Adjustment();
				$adj_remark = 'Prorated Bill From ' . date('Y-m-d',strtotime($activated_date)) . ' UNTIL ' . $month_end->format('Y-m-d');
				
				$this->load->model('adjustment_model');
				$this->adjustment_model->insert_adj('i', $customer_no, '', '', $charge_date, '41', 'dr', $amount, $adj_remark, 1);

			}
			
		}
	}
	
	function deposit_invoice($idx, $type_val = '', $gen_pdf = 0 , $pdf_name = '')
	{
		$invoice = $this->customer_model->get_deposit_invoice($idx);	
		
		$data['data'] = $invoice['deposit'];
        
        foreach( $data['data'] AS $key => $val ){
			foreach( $data['data'][$key] AS $key2 => $val2 ){
				if( $key2 == 'bill_date' )
					$data['data'][$key][$key2] = date_toggle($val2,$_SESSION['config']['date_format_printing']);
			}
		}

        
		$gst_reg_no = $this->common_model->get_gst_reg_no();	
		
		$header_data 				= $this->vars;
		$header_data['title'] 		= 'print preview | invoice';
		$header_data['description']	= 'to print invoice';	
		$content_data['data']		= $data['data'];
		$content_data['gst_reg_no']	= $gst_reg_no;

		$config = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'payment_acc_details' ");
		$content_data['payment_acc_details'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';

		$gst_amount	= $this->common_model->get_default_tax_amount();
		$content_data['default_tax'] = $gst_amount[0]['percent'];

		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['bills_footer'] = $footer[0]['val'];

		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];
		
		$html = ''; 	
		if($gen_pdf == '1')
		{
			$html .= $this->load->view('templates/print_header_genpdf', '', true);
			$html .= $this->parser->parse('customer/deposit_invoice', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);
						
			$this->load->helper(array('dompdf', 'file'));			
			$data = pdf_create($html, '', false);	 
			
			if(empty($pdf_name)) $pdf_name = 'temp_invoice_'.date("Y-m-d_h:i:sa", time());			
			$result = write_file('temp/'.$pdf_name.'.pdf', $data);			
			return $result;			
		}
		else
		{	
			$this->load->view('templates/print_header', $header_data);	
			$this->load->view('templates/print_menu');
			$this->parser->parse('customer/deposit_invoice',$content_data);
			$this->load->view('templates/print_footer');
		}
	} 
	
	function deposit_receipt($idx, $gen_pdf = 0, $pdf_name = '')
	{
		$data = $this->customer_model->get_deposit_receipt($idx);
		
		$data['customer'][0]['display_name'] 		= $data['customer'][0]['name'] ;
		$data['customer'][0]['display_addr1'] 		= $data['customer'][0]['inst_addr1'] ;
		$data['customer'][0]['display_addr2'] 		= $data['customer'][0]['inst_addr2'] ;
		$data['customer'][0]['display_city'] 		= $data['customer'][0]['inst_city'] ;
		$data['customer'][0]['display_postcode'] 	= $data['customer'][0]['inst_postcode'] ;
		$data['customer'][0]['display_state'] 		= $data['customer'][0]['inst_state'] ;
		
		//overide display_sets if bill_addr1 is not empty
		if(!empty($data['customer'][0]['bill_addr1']))
		{				
			$data['customer'][0]['display_name'] 		= $data['customer'][0]['bill_name'] ;
			$data['customer'][0]['display_addr1'] 		= $data['customer'][0]['bill_addr1'] ;
			$data['customer'][0]['display_addr2'] 		= $data['customer'][0]['bill_addr2'] ;
			$data['customer'][0]['display_city'] 		= $data['customer'][0]['bill_city'] ;
			$data['customer'][0]['display_postcode'] 	= $data['customer'][0]['bill_postcode'] ;
			$data['customer'][0]['display_state'] 		= $data['customer'][0]['bill_state'] ;
		}
		
		//~ _debug_array($data); exit;
		
		$deposit_receipt['payment_no']			= add_zero($data['deposit'][0]['idx']);
		$deposit_receipt['tranx_date']			= date_toggle($data['deposit'][0]['deposit_date'],$_SESSION['config']['date_format_printing']);
		
		$deposit_receipt['customer_no']			= $data['customer'][0]['customer_no'];
		$deposit_receipt['login_username']		= $data['customer'][0]['login_username'];
		$deposit_receipt['email_1']				= $data['customer'][0]['email_1'];
		$deposit_receipt['package_name']		= $data['customer'][0]['package_name'];
		$deposit_receipt['customer_name']		= $data['customer'][0]['display_name'];
		$deposit_receipt['currency_code']		= $data['customer'][0]['currency_code'];
		$deposit_receipt['amount']				= $data['deposit'][0]['deposit'];
		
		if(!empty($deposit_receipt['amount'])){			
			$amount_split = explode('.', $deposit_receipt['amount']);			
			$cents = ' ';
			if(!empty($amount_split[1]) && $amount_split[1] != '00'){				
				$cent_amount 	= (int)$amount_split[1];				
				$cents 			.= 'and '.convert_number_to_words($cent_amount).' cent ';
			}			
			$deposit_receipt['amount_in_words']	= convert_number_to_words($amount_split[0]).$cents.'only';
		}
		
		$deposit_receipt['bill_type_name']		= $data['deposit'][0]['remark'];
		//$deposit_receipt['payment_source_name']	= 'CASH';
		$deposit_receipt['payment_source_name']	= $data['deposit'][0]['payment_source_name'];
		
		$data = array();
		
		$header_data 					= $this->vars;
		$header_data['title']			= 'print preview | deposit receipt';
		$header_data['description']		= 'to print receipt';
		$data['data'][] 				= $deposit_receipt;
		$data['msg'] 					= $this->msg;	
		
		//~ _debug_array($data); exit;
		
		// page info here, db calls, etc.   
		if(!empty($gen_pdf) && $gen_pdf == '1')
		{	
			$html  = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('customer/deposit_receipt', $data, true);
			$html .= $this->load->view('templates/print_footer', '', true);
		
			$this->load->helper(array('dompdf', 'file'));			
			$data = pdf_create($html, '', false);	
			
			if(empty($pdf_name)) $pdf_name = 'temp_payment_'.date("Y-m-d_h:i:sa", time());				 
			$result = write_file('temp/'.$pdf_name.'.pdf', $data);			
			return $result;			
		}
		else
		{
			$html  = '';
			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', '', true);
			$html .= $this->parser->parse('customer/deposit_receipt', $data, true);
			$html .= $this->load->view('templates/print_footer', '', true);
			echo $html; 
		}
	} 
	
	/**
	 * [Load the deposit details page]
	 * @param  String $customer_no
	 * @return *no return
	 */
	function deposit_details($customer_no)
	{
		//Load the deposit history, and controller
		if(empty($customer_no)) redirect(base_url('customer'));
		
		$data['row_data'] = $this->customer_model->get_deposit_history($customer_no);
		
		$data['total_deposit'] = 0;	
		if(!empty($data['row_data']))
		{
			foreach ($data['row_data'] as $rd_key => $rd_arr)
			{
				$data['row_data'][$rd_key]['deposit_date'] = date_toggle($data['row_data'][$rd_key]['deposit_date'],$_SESSION['config']['date_format']);
				
				if( $data['row_data'][$rd_key]['void'] == 0 ){
					$data['total_deposit'] = number_format( $data['total_deposit'],2,".","") + 
											 number_format( $data['row_data'][$rd_key]['deposit'],2,".","");
				}
			}
		}
		
		if(!$_POST){
			$inputs['customer_deposit']['deposit_date'] = '';
			$inputs['customer_deposit']['remark'] 		= '';
			$inputs['customer_deposit']['payment_source'] = '';
			$inputs['customer_deposit']['payment_info'] = '';
			$inputs['customer_deposit']['deposit'] 		= '';
		}
		
		$data['var']['page_title']	= ucwords('deposit details');
		$data['var']['fieldset_1'] 	= ucwords('previous deposits');
		$data['var']['fieldset_2'] 	= ucwords('add new deposit');
		$data['var']['form_action'] = base_url('customer/save_deposit');
		$data['var']['form_action2'] = base_url('customer/update_deposit');
		$data['var']['customer_no'] = (empty($customer_no))?'':$customer_no;		
		$data['input'] 				= (empty($inputs))?'':$inputs;		
		$data['payment_source_opt'] = $this->common_model->get_payment_source_list();;
		$data['msg'] 				= $this->msg;

		$latest_termination_flow = $this->customer_model->check_latest_termination_flow($customer_no);
		$data['current_termination_flow'] = $latest_termination_flow['termination_status'] ?? 'A';
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);			
		$this->parser->parse('customer/deposit_detail',$data);				
		$this->load->view('templates/footer');
	}

	
	/**
	 * Insert new deposit transaction
	 * @param String $customer_no
	 * @param Decimal $deposit
	 * @param String $remark
	 */
	//~ function save_deposit($customer_no, $deposit, $remark)
	function save_deposit()
	{
		if(!$_POST || (empty( $_POST['customer_deposit']['customer_no']))) 
		{
			$this->msg['error_msg'] = 'Invalid Access';
			redirect(base_url('customer'));
        }
        $customer_no 	= $_POST['customer_deposit']['customer_no'];        
		$post_back 		= $this->input->post(NULL, TRUE);	
		
        if(isset($_POST['processtype']['cancel'])){
			redirect(base_url('customer/edit_customer/'.$customer_no));
		}
        elseif(isset($_POST['processtype']['save'])){
			//continue to run below's code
			$this->set_form_validation('save_deposit'); //set rules for form validation
			
			//set error message template if form_validation run false
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1">','</label>');	
			if($this->form_validation->run() == false) 
			{		
				//fail to pass validation	
				$this->msg['error_msg'] = validation_errors();	
				$this->deposit_details($customer_no);
			}
			else
			{	
				$this->load->library('session');
				$user_data 	= $this->session->userdata('user');
				$username 	= $user_data['username'];
				$result 	= $this->customer_model->customer_deposit_insert($post_back,$username);			
				
				if(empty($result)) $this->session->set_flashdata("msg", 'Deposit Added!');			
				else $this->session->set_flashdata("msg", 'Deposit Added!');
				
				redirect(base_url('customer/deposit_details/'.$customer_no));
			}
		}elseif(isset($_POST['processtype']['update'])){

			//update btn, seperate update by origin
			
			//continue to run below's code
			$this->set_form_validation('update_deposit'); //set rules for form validation
			//set error message template if form_validation run false
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError2">','</label>');	
			if($this->form_validation->run() == false) 
			{		
				//fail to pass validation	
				$this->msg['error_msg'] = validation_errors();	
				$this->deposit_details($customer_no);
			}else{
				$this->load->library('session');
				$user_data 	= $this->session->userdata('user');
				$username 	= $user_data['username'];
				$result 	= $this->customer_model->customer_deposit_update($_POST['update_deposit'],$username);
				
				
				if(empty($result)) $this->session->set_flashdata("msg", 'Deposit updated!');			
				else $this->session->set_flashdata("msg", 'Deposit updated!');
				
				redirect(base_url('customer/deposit_details/'.$customer_no));
			}
		}
	}
	
	function numeric_wdot()
	{
		$return_val 	= false;
		$error_msg_1	= ucwords('invalid amount');
		$error_msg_2	= ucwords('amount is required');
		
		if(empty($_POST['customer_deposit']['deposit'])) 
		{
			$this->form_validation->set_message('numeric_wdot', $error_msg_2);
		}
		else
		{
			if (!is_numeric($_POST['customer_deposit']['deposit']) || $_POST['customer_deposit']['deposit'] < 0 )
			{				
				$this->form_validation->set_message('numeric_wdot', $error_msg_1);
			}
			else
			{				
				$got_error = preg_match('/^[0-9.-]+$/', $_POST['customer_deposit']['deposit']);
				if(!empty($got_error)) $return_val = true;			
			}				
		}
		return $return_val;
	}

	function numeric_wdot2()
	{
		$return_val 	= true;
		$error_msg_1	= ucwords('invalid amount');
		$error_msg_2	= ucwords('amount is required');
		

		
		if( count( $_POST['update_deposit']['payment_amount'] ) > 0 ){
			for( $z = 0 ; $z < count( $_POST['update_deposit']['payment_amount'] ) ; $z++ ){
				
				if(empty($_POST['update_deposit']['payment_amount'][$z])) 
				{
					$this->form_validation->set_message('numeric_wdot2', $error_msg_2);
					$return_val = false;
					break;
				}
				else
				{
					if (!is_numeric($_POST['update_deposit']['payment_amount'][$z]) || $_POST['update_deposit']['payment_amount'][$z] < 0 )
					{				
						$this->form_validation->set_message('numeric_wdot2', $error_msg_1);
						$return_val = false;
						break;
					}
					else
					{
						$got_error = preg_match('/^[0-9.-]+$/', $_POST['update_deposit']['payment_amount'][$z]);
						//if(!empty($got_error)) $return_val = true;		
						if(empty($got_error)){ 
							$return_val = false;
							break;
						}
					}
				}
				
			}
		}
		
		return $return_val;
	}

    public function index()
    {

    	$row_html = $this->customer_rows(1);

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();

			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['sel_category'] = $post_data['sel_category'];
			$data['sel_status'] = $post_data['sel_status'];
			$data['sel_building'] = $post_data['sel_building'];
			$data['order_by'] = $post_data['order_by'];
			$data['order_type']	= $post_data['order_type'];
		} else {
			$customer_filter = get_session_filter('customer_filter');
			$data['page_item_no'] = $customer_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $customer_filter['txt_search'] ?? '';
			$data['sel_category'] = $customer_filter['sel_category'] ?? 'all';
			$data['sel_status'] = $customer_filter['sel_status'] ?? 'all';
			$data['sel_building'] = $customer_filter['sel_building'] ?? 'all';
			$post_data['order_by'] = $customer_filter['order_by'] ?? '';
			$post_data['order_type'] = $customer_filter['order_type'] ?? '';
		}

		$data['page_title'] 		= 'Account';
		$data['form_action'] 		= base_url('customer');

		//$data['sel_status_list'] 	= $this->common_model->get_acc_status_list();
		$data['sel_category_list'] 	= $this->common_model->get_category_list();

		$account_status = $this->account_status;
		$data['sel_status_list'] = $account_status;
		
		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		if( $aclInfo['role_name'] == 'BPO' ){
			foreach( $data['sel_category_list'] AS $row => $res ){
				if( $res['category_code'] != 'r' && $res['category_code'] != 'b' ) UNSET( $data['sel_category_list'][$row] ) ;
			}
		}
				
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		
		$data['msg'] 				= $this->msg;

		$data['row_html'] = $row_html;

		$access_view   = check_acl('customer', 'V', false ) == true ? 1 : 0 ;
		$access_modify = check_acl('customer', 'M', false ) == true ? 1 : 0 ;
		$data['view_only'] = 0 ; 
		if( $access_view == 1 && $access_modify == 0 ){
			$data['view_only'] = 1 ;
		}

		$this->load->model('setting_model');
		$result	 					= $this->setting_model->get_nas_setting_listing('');

		$data['nas_address'] = $result['row'];

		$this->vars['cssfiles'][] = 'css/theme/bootstrap-grid.css';

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/index',$data);
		$this->load->view('templates/footer');
	}

	public function customer_rows($returnOnly = 0)
	{

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$customer_filter = get_session_filter('customer_filter');
			$post_data['page_item_no'] = $customer_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $customer_filter['txt_search'] ?? '';
			$post_data['sel_category'] = $customer_filter['sel_category'] ?? 'all';
			$post_data['sel_status'] = $customer_filter['sel_status'] ?? 'all';
			$post_data['sel_building'] = $customer_filter['sel_building'] ?? 'all';
			$post_data['sel_installation'] = $customer_filter['sel_installation'] ?? 'all';
			$post_data['order_by'] = $customer_filter['order_by'] ?? '';
			$post_data['order_type'] = $customer_filter['order_type'] ?? '';
		}

		//generate rows html here
		if (!isset($post_data['page_item_no'])) {
			$post_data['page_item_no'] = 0;
		}

		if (!isset($post_data['txt_search'])) {
			$post_data['txt_search'] = '';
		}

		if (!isset($post_data['sel_category'])) {
			$post_data['sel_category'] = 'all';
		}

		if (!isset($post_data['sel_status'])) {
			$post_data['sel_status'] = 'all';
		}

		if (!isset($post_data['sel_building'])) {
			$post_data['sel_building'] = 'all';
		}

		if (!isset($post_data['sel_installation'])) {
			$post_data['sel_installation'] = 'all';
		}

		//post values , must also run by session values
		$total_row = 0;
		$page_item_no = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search = $post_data['txt_search'];
		$sel_category = $post_data['sel_category'];
		$sel_status = $post_data['sel_status'];
		$sel_building = $post_data['sel_building'];
		$sel_installation = $post_data['sel_installation'];
		$order_by = $post_data['order_by'];
		$order_type = $post_data['order_type'];

		//apply session
		/*$_SESSION['listing']['customer_page_item_no'] = $page_item_no;
		$_SESSION['listing']['customer_txt_search'] = $txt_search;
		$_SESSION['listing']['customer_sel_category'] = $sel_category;
		$_SESSION['listing']['customer_sel_status'] = $sel_status;
		$_SESSION['listing']['customer_sel_building'] = $sel_building;*/

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_category' => $sel_category,
			'sel_status' => $sel_status,
			'sel_building' => $sel_building,
			'sel_installation' => $sel_installation,
			'order_by' => $order_by,
			'order_type' => $order_type
		);
		set_session_filter('customer_filter', $session_array);
		
		if ( empty($sel_category) ) {
			$sel_category = 'all';
		}
		if ( empty($sel_status) ) {
			$sel_status = 'all';
		}
		if ( empty($sel_installation) ) {
			$sel_installation = 'all';
		}
		
		$query_where = '';
		$query_order = '';


		if ($sel_category != 'all' && !empty($sel_category)) {
			$query_where = "AND c.category = '$sel_category' ";
		}
		if ($sel_status != 'all' && !empty($sel_status)) {
			$query_where = $query_where."AND cs.status = '$sel_status' ";
		}
		if ( $sel_building != 'all' && !empty( $sel_building ) ){
			$query_where = $query_where."AND c.building = '$sel_building' ";
		}
		if ($sel_installation != 'all' && $sel_status == 'P') {
			switch ($sel_installation) {
				case 'upcoming':
					$query_where .= " AND c.preferred_install_datetime >= NOW() ";
					$query_order = " ORDER BY c.preferred_install_datetime ASC, c.name ASC ";
					break;

				case 'overdue':
					$query_where .= " AND c.preferred_install_datetime < NOW() ";
					$query_order = " ORDER BY c.preferred_install_datetime DESC, c.name ASC ";
					break;

				case 'unscheduled':
					$query_where .= " AND c.preferred_install_datetime IS NULL ";
					$query_order = " ORDER BY c.name ASC ";
					break;
			}
		}
		
		$this->load->model('acl_model');
		$aclInfo = $this->acl_model->get_acl( $this->user['acl_role'] ) ;
		
		$data['show_welcome_note'] = '';
		if( $aclInfo['role_name'] == 'BPO' ){
			$query_where = $query_where . " AND c.category IN ( 'b' , 'r' ) ";
			$data['show_welcome_note'] = 'hide';
		}

		if ($sel_status != "P" & $order_by != "" && $order_type != "") {
			if ($order_by == "ord_transact_date") {
				$sql_order_by = "first_activate";
			} else if ($order_by == "ord_customer_no") {
				$sql_order_by = "customer_no";
			} else if ($order_by == "ord_name") {
				$sql_order_by = "name";
			} else {
				$sql_order_by = $order_by;
			}
			
			$query_order = " ORDER BY " . $sql_order_by . " " . $this->db->escape_str($order_type);
		}
		
		$result = $this->customer_model->get_customer_listing($txt_search,$page_item_no,$query_where,$query_order);

		$account_status = $this->account_status;
		foreach($result['row'] as $key => $row ) {
			$result['row'][$key]['status_text'] = (isset($account_status[$row['latest_status']]) ? $account_status[$row['latest_status']] : '');
			$result['row'][$key]['activation_date'] = '';
			if($row['latest_status'] == 'A') {
				$result['row'][$key]['activation_date'] = date('Y-m-d', strtotime($row['transact_date']));
			}

			$result['row'][$key]['preferred_installation'] = '-';
			if (!empty($row['preferred_install_datetime'])) {
				$datetime = new DateTime($row['preferred_install_datetime']);
				$end = clone $datetime;
				$end->modify('+2 hours');
				$result['row'][$key]['preferred_installation'] =
					$datetime->format('d/m/Y')
					. '<br><small class="pink">'
					. $datetime->format('g:i A')
					. ' - '
					. $end->format('g:i A')
					. '</small>';
			}
		}

		$data['row_data'] 			= $result['row'];

		$data['txt_search'] 		= $txt_search;
		$data['sel_category'] 		= $sel_category;
		$data['sel_status'] 		= $sel_status;
		$data['sel_building']		= $sel_building;
		$data['order_by']			= $order_by;
		$data['order_type']			= $order_type;

		$data['pagination'] 		= paginationSettingsAjax('', $result['total_row'], $page_item_no, $_SESSION['config']['max_page_item']);

		$access_view   = check_acl('customer', 'V', false ) == true ? 1 : 0 ;
		$access_modify = check_acl('customer', 'M', false ) == true ? 1 : 0 ;
		$data['view_only'] = 0 ; 
		if( $access_view == 1 && $access_modify == 0 ){
			$data['view_only'] = 1 ;
		}

		$html = $this->parser->parse('customer/customer_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}

	public function execute_dc($customer_no, $nas_id) {

		$this->customer_model->do_dc($customer_no, $nas_id);

		$this->msg['msg'] = 'Disconnect CMD executed.';

		$this->index();

	}
	
	function set_form_validation($mode = 'search', $change_package = false)
	{
		$new_effective_date_required = 'trim';
		if ($change_package) {
			$new_effective_date_required = 'trim|required';
		}

		$config = array(
					'save_deposit' => array (
						array('field' => 'customer_deposit[deposit_date]', 'label' => 'Date', 'rules' => 'trim|required'),
						array('field' => 'customer_deposit[remark]', 'label' => 'Remark', 'rules' => 'trim|required'),
						array('field' => 'customer_deposit[deposit]', 'label' => 'Amount', 'rules' => 'callback_numeric_wdot'),
					),
					'update_deposit' => array (
						array('field' => 'update_deposit[payment_amount][]', 'label' => 'Amount', 'rules' => 'callback_numeric_wdot2'),
					),				
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
						array('field' => 'sel_status', 'label' => 'Status', 'rules' => ''),
						array('field' => 'sel_category', 'label' => 'Category', 'rules' => ''),
					),
					'save' => array (
						array('field' => 'status', 'label' => 'Status', 'rules' => 'trim'),
						array('field' => 'category', 'label' => 'Category', 'rules' => 'trim'),
						array('field' => 'package', 'label' => 'package', 'rules' => 'trim|required'),
						array('field' => 'new_package_effective_date', 'label' => 'New Package Eff. Date', 'rules' => $new_effective_date_required),
						array('field' => 'package_name', 'label' => 'Package Name', 'rules' => 'trim'),
						array('field' => 'monthly_charge', 'label' => 'Monthly Charge', 'rules' => 'trim|numeric'),
						array('field' => 'yearly_charge', 'label' => 'Yearly Charge', 'rules' => 'trim'),
						array('field' => 'package_month', 'label' => 'Package Month', 'rules' => 'trim|is_natural'),
						array('field' => 'stop_service_after', 'label' => 'Stop Service After', 'rules' => 'trim'),
						array('field' => 'bill_cycle_month', 'label' => 'Bill Cycle Month', 'rules' => 'trim|is_natural'),
						array('field' => 'contract_month', 'label' => 'Contract Month', 'rules' => 'trim|is_natural|integer|greater_than_equal_to[0]'),
						array('field' => 'login_username', 'label' => 'Login User Name', 'rules' => 'trim|required'),
						array('field' => 'login_password', 'label' => 'Login Password', 'rules' => 'trim|required'),
						array('field' => 'customer_no', 'label' => 'Customer No', 'rules' => 'trim'),
						array('field' => 'name', 'label' => 'Customer Name', 'rules' => 'trim|required'),
						array('field' => 'reg_no', 'label' => 'Reg. No.', 'rules' => 'trim'),
						array('field' => 'gst_no', 'label' => 'GST No.', 'rules' => 'trim'),
						array('field' => 'pic_no', 'label' => 'PIC Name', 'rules' => 'trim'),
						array('field' => 'building', 'label' => 'Building', 'rules' => 'trim'),
						array('field' => 'inst_unit_no', 'label' => 'Installation Unit No.', 'rules' => 'trim'),
						array('field' => 'inst_addr1', 'label' => 'Installation Address 1', 'rules' => 'trim|required'),
						array('field' => 'inst_addr2', 'label' => 'Installation Address 2', 'rules' => 'trim'),
						array('field' => 'inst_addr3', 'label' => 'Installation Address 3', 'rules' => 'trim'),
						array('field' => 'inst_city', 'label' => 'Installation City', 'rules' => 'trim|required'),
						array('field' => 'inst_postcode', 'label' => 'Installation Postcode', 'rules' => 'trim|required|is_natural'),
						array('field' => 'inst_state', 'label' => 'Installation State', 'rules' => 'trim|required'),
						array('field' => 'inst_phone', 'label' => 'Installation Phone', 'rules' => 'trim'),
						array('field' => 'inst_email', 'label' => 'Installation Email', 'rules' => 'trim'),
						array('field' => 'bill_name', 'label' => 'Billing Address 1', 'rules' => 'trim'),
						array('field' => 'bill_addr1', 'label' => 'Billing Address 1', 'rules' => 'trim'),
						array('field' => 'bill_addr2', 'label' => 'Billing Address 2', 'rules' => 'trim'),
						array('field' => 'bill_city', 'label' => 'Billing City', 'rules' => 'trim'),
						array('field' => 'bill_postcode', 'label' => 'Billing Postcode', 'rules' => 'trim|is_natural'),
						array('field' => 'bill_state', 'label' => 'Billing State', 'rules' => 'trim'),
						array('field' => 'bill_by_post', 'label' => 'Bill By Post', 'rules' => 'trim'),
						array('field' => 'bill_by_email', 'label' => 'Bill By Email', 'rules' => 'trim'),
						array('field' => 'tel_num[]', 'label' => 'Home No', 'rules' => 'trim'),
						array('field' => 'fax_num[]', 'label' => 'Fax No', 'rules' => 'trim'),
						array('field' => 'mobile_num[]', 'label' => 'Mobile No', 'rules' => 'trim'),
						array('field' => 'email_1[]', 'label' => 'Email', 'rules' => 'trim'),
						array('field' => 'email_2[]', 'label' => 'Alternative Email', 'rules' => 'trim'),
						array('field' => 'pic_name[]', 'label' => 'Person In Charge', 'rules' => 'trim'),
						array('field' => 'nric[]', 'label' => 'New IC', 'rules' => 'trim'),
						array('field' => 'passport[]', 'label' => 'Passport', 'rules' => 'trim'),
						array('field' => 'date_of_birth[]', 'label' => 'Date Of Birth', 'rules' => 'trim'),
						array('field' => 'gender[]', 'label' => 'Gender', 'rules' => 'trim'),
						array('field' => 'race[]', 'label' => 'Race', 'rules' => 'trim'),
						array('field' => 'marital_status', 'label' => 'Marital Status', 'rules' => 'trim'),
						array('field' => 'household', 'label' => 'Household Size', 'rules' => 'trim'),
						array('field' => 'ownership_type', 'label' => 'Ownership Type', 'rules' => 'trim'),
						array('field' => 'no_of_employee', 'label' => 'No Of Employee', 'rules' => 'trim'),
						array('field' => 'no_of_branches', 'label' => 'No Of Branches', 'rules' => 'trim'),
						array('field' => 'signup_date', 'label' => 'Signup Date', 'rules' => 'trim'),
						array('field' => 'activated_date', 'label' => 'Activated Date', 'rules' => 'trim'),
						array('field' => 'suspended_date', 'label' => 'Suspended Date', 'rules' => 'trim'),
						array('field' => 'terminated_date', 'label' => 'Terminated Date', 'rules' => 'trim'),
						array('field' => 'dealer', 'label' => 'Agent', 'rules' => 'trim'),
						array('field' => 'remark', 'label' => 'Remark', 'rules' => 'trim'),
						array('field' => 'payment_term', 'label' => 'Payment Term', 'rules' => 'trim'),

						array('field' => 'profile_id', 'label' => 'Customer Profile', 'rules' => 'trim'),
						array('field' => 'acc_name', 'label' => 'Customer Profile', 'rules' => 'trim'),

						array('field' => 'serial_num', 'label' => 'Serial Num', 'rules' => 'trim'),
						array('field' => 'installer_name', 'label' => 'Installer Name', 'rules' => 'trim'),
						array('field' => 'caller_id', 'label' => 'Caller ID', 'rules' => 'trim'),

						array('field' => 'current_status', 'label' => 'Current Status', 'rules' => 'trim'),
						array('field' => 'transact_date', 'label' => 'Transact Date', 'rules' => 'trim|required'),

						array('field' => 'router_id', 'label' => 'Router', 'rules' => 'trim'),
						array('field' => 'interface_id', 'label' => 'Interface Id', 'rules' => 'trim'),
						array('field' => 'core_router_id', 'label' => 'Core Router ID', 'rules' => 'trim'),
						array('field' => 'core_router_interface_id', 'label' => 'Core Router Interface ID', 'rules' => 'trim'),
						array('field' => 'edge_router_id', 'label' => 'Edge Router ID', 'rules' => 'trim'),
						array('field' => 'edge_router_interface_id', 'label' => 'Edge Router Interface ID', 'rules' => 'trim'),

						array('field' => 'framed_netmask', 'label' => 'Framed Netmask', 'rules' => 'trim'),
						array('field' => 'framed_ipv6_address', 'label' => 'Framed IPV6 Address', 'rules' => 'trim'),
						array('field' => 'framed_ipv6_route', 'label' => 'Framed IPV6 Route', 'rules' => 'trim'),

						array('field' => 'new_submit', 'label' => 'new_submit', 'rules' => 'trim'),
					),
		);
		
		if ($mode === 'save') {
			$charge_name   = $this->input->post('charge_name');
			$charge_type   = $this->input->post('charge_type');
			$charge_amount = $this->input->post('charge_amount');

			if (!empty($charge_name)) {
				foreach ($charge_name as $i => $val) {
					$config['save'][] = array(
						'field' => "charge_name[$i]",
						'label' => "Charge Name #".($i+1),
						'rules' => 'trim|required'
					);

					$config['save'][] = array(
						'field' => "charge_type[$i]",
						'label' => "Charge Type #".($i+1),
						'rules' => 'trim|required'
					);

					$config['save'][] = array(
						'field' => "charge_amount[$i]",
						'label' => "Charge Amount #".($i+1),
						'rules' => 'trim|required|numeric'
					);
				}
			}
		}
		
		$this->form_validation->set_rules($config[$mode]);
	}
	
	
	function add_customer()
	{
		$customer = $this->customer_model->get_customer();
		/*$customer_pic_1[] = array(
									'pic_id' => '',
									'pic_designation'=> $customer['pic_designation'],
									'customer_no' => $customer['customer_no'], 
									'pic_name' => $customer['pic_name'], 
									'tel_num' => $customer['tel_num'], 
									'fax_num' => $customer['fax_num'], 
									'pic_name' => $customer['pic_name'], 
									'mobile_num' => $customer['mobile_num'], 
									'email_1' => $customer['email_1'], 
									'email_2' => $customer['email_2'], 
									'nric' => $customer['nric'], 
									'passport' => $customer['passport'], 
									'date_of_birth' => $customer['date_of_birth'], 
									'race' => $customer['race'], 
									'gender' => $customer['gender']
							);
		
		$data['customer_pic'] 		= $customer_pic_1;*/
		$data['input']				= $customer;
		$data['page_title'] 		= 'Add Account';
		$data['form_action'] 		= base_url('customer/save_customer');
		$hidden['url_after_save'] 	= '';
		$data['hidden'] 			= $hidden;
		
		$data['sel_package_list'] 			= $this->common_model->get_package_list($data['input']['category'], 'a');
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
		$data['sel_category_list'] 			= $this->common_model->get_category_list();
		$data['sel_state_list'] 			= $this->common_model->get_state_list();
		$data['sel_building_list'] 			= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['sel_marital_status_list'] 	= $this->common_model->get_marital_status_list();
		$data['sel_dealer_list'] 			= format_dealer_list_hierarchy($this->common_model->get_dealer_list() ?? []);
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();
		$data['sel_installer_list']			= $this->common_model->get_installer_list();
		$data['sel_router_list']			= $this->router_model->get_routers();
		$this->load->model('app_config_model');
		$data['sel_bill_type'] = $this->app_config_model->load_sys_bill_type();
		$data['msg'] 						= $this->msg;

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('relocation_sop')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$data['relocation_sop']      = $cfg['relocation_sop'] ?? '0';

		$data['other_charges'] = array();

		$data['upgrade_package_list'] = $this->package_model->get_package_listing_all_active();

		$profile_arr = array();
		$this->load->model('profile_model');
		$profile_list = $this->profile_model->get_profile_listing_all();
        foreach ($profile_list as $acc_id => $acc_row) {
            $profile_arr[] = [
            	'value'=>$acc_row['acc_type'] === 'r' ? $acc_row['acc_name'] : $acc_row['comp_name'],
            	'label'=>$acc_row['acc_type'] === 'r' ? $acc_row['acc_name'] : $acc_row['comp_name'],
            	'id'=>$acc_id, 
            	'name'=>$acc_row['acc_type'] === 'r' ? $acc_row['acc_name'] : $acc_row['comp_name'],
            	'bill_unit_no' => $acc_row['bill_unit_no'],
            	'bill_addr_1' => $acc_row['bill_addr_1'],
            	'bill_addr_2' => $acc_row['bill_addr_2'],
            	'bill_addr_3' => $acc_row['bill_addr_3'],
            	'bill_postcode' => $acc_row['bill_postcode'],
            	'bill_city' => $acc_row['bill_city'],
            	'bill_state' => $acc_row['bill_state'],
            	'ssm' => $acc_row['ssm'],
            	'sst' => $acc_row['sst'],
            ];
        }

        $data['profile_arr']		= $profile_arr;
		
		$data['not_same_addr'] = 0 ;
		$data['account_status'] = $this->account_status;

		$data['effective_date_not_reached'] = false;

		//Currently selected equipment
		$data['current_equipment_type'] = '';
		$data['current_serial_no'] = '';

		// default value
		$data['preferred_install_date'] = '';

		$data['user_idx'] = $this->user['idx'];

		//no need to bother with add
		$data['termination_flow'] = 0;

		$data['termination_init'] = check_acl('customer', 'TI', false);
		$data['termination_confirm'] = check_acl('customer', 'TC', false);

		$data['current_termination_flow'] = 'A'; /* put A for add customer because 'A' is cancelled meaning also similar to not having any termination flow */

		$data['has_jumpstart_permission'] = ($this->user['acl_role'] == 1 || $this->user['acl_role'] == 6);
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('customer/customer_detail', $data);
		$this->load->view('templates/footer');
	}
	
	function edit_customer($customer_no='', $url_after_save = '')
	{

		$hidden['url_after_save'] = '';
		if($url_after_save != ''){
			$hidden['url_after_save'] = base_url($url_after_save);
		}
		
		$customer = $this->customer_model->get_customer($customer_no);
		//log_message('error',print_r($customer,true));
		
		/*$customer_pic_1[] = array(	
									'pic_id' => '',
									'customer_no' => $customer['customer_no'], 
									'pic_designation' => $customer['pic_designation'],
									'pic_name' => $customer['pic_name'], 
									'tel_num' => $customer['tel_num'], 
									'fax_num' => $customer['fax_num'], 
									'pic_name' => $customer['pic_name'], 
									'mobile_num' => $customer['mobile_num'], 
									'email_1' => $customer['email_1'], 
									'email_2' => $customer['email_2'], 
									'nric' => $customer['nric'], 
									'passport' => $customer['passport'], 
									'date_of_birth' => $customer['date_of_birth'], 
									'race' => $customer['race'], 
									'gender' => $customer['gender']
								);
		
		$customer_pic_2 = $this->customer_model->get_customer_pic( $customer_no );
		$data['customer_pic'] = array_merge( $customer_pic_1 , $customer_pic_2 );*/
		
		if ($customer['customer_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Customer not found!');
			redirect('customer');
		}

		//contract expiry
		$current_package = $this->customer_model->get_last_package($customer['customer_no'], $customer['package']);
		$customer['contract_expiry'] = $current_package['contract_end_date'] ?? '';
		
		$data['input'] = $customer;
		$data['hidden'] = $hidden;
		
		$data['page_title'] = 'Edit Account';
		$data['form_action'] = base_url('customer/save_customer');
				
		$data['sel_package_list'] 			= $this->common_model->get_package_list($data['input']['category'], 'a', " OR package_no = " . $customer['package']);
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
		$data['sel_category_list'] 			= $this->common_model->get_category_list();
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();
		$data['sel_state_list'] 			= $this->common_model->get_state_list();
		$data['sel_building_list'] 			= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['sel_marital_status_list'] 	= $this->common_model->get_marital_status_list();
		$data['sel_dealer_list'] 			= format_dealer_list_hierarchy($this->common_model->get_dealer_list() ?? []);
		$data['sel_installer_list']			= $this->common_model->get_installer_list();
		$data['sel_router_list']			= $this->router_model->get_routers();
		
		$this->load->model('app_config_model');
		$data['sel_bill_type'] 				= $this->app_config_model->load_sys_bill_type();
		$data['msg'] 						= $this->msg;

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('relocation_sop')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$data['relocation_sop']      = $cfg['relocation_sop'] ?? '0';

		$data['upgrade_package_list'] = $this->package_model->get_package_listing_all_active();

		$data['other_charges'] = $this->customer_model->get_other_charges($customer_no);

		$profile_arr = array();
		$this->load->model('profile_model');
		$profile_list = $this->profile_model->get_profile_listing_all();
        foreach ($profile_list as $acc_id => $acc_row) {
            $profile_arr[] = [
            	'value'=>$acc_row['acc_type'] === 'r' ? $acc_row['acc_name'] : $acc_row['comp_name'],
            	'label'=>$acc_row['acc_type'] === 'r' ? $acc_row['acc_name'] : $acc_row['comp_name'],
            	'id'=>$acc_id, 
            	'name'=>$acc_row['acc_type'] === 'r' ? $acc_row['acc_name'] : $acc_row['comp_name'],
            	'bill_unit_no' => $acc_row['bill_unit_no'],
            	'bill_addr_1' => $acc_row['bill_addr_1'],
            	'bill_addr_2' => $acc_row['bill_addr_2'],
            	'bill_addr_3' => $acc_row['bill_addr_3'],
            	'bill_postcode' => $acc_row['bill_postcode'],
            	'bill_city' => $acc_row['bill_city'],
            	'bill_state' => $acc_row['bill_state'],
            	'ssm' => $acc_row['ssm'],
            	'sst' => $acc_row['sst'],
            ];
        }

        $data['profile_arr']		= $profile_arr;
		
		/*	
		if( $customer['bill_name'] == '' && $customer['bill_addr1'] == '' && $customer['bill_addr2'] == '' && 
			$customer['bill_city'] == '' && $customer['bill_postcode'] ){
		*/
		if( $customer['bill_name'] == '' ){
			$data['not_same_addr'] = 0 ;
		}else{
			$data['not_same_addr'] = 1 ;
		}

		$data['account_status'] = $this->account_status;

		if (!empty($customer['new_package_effective_date']) && (date('Y-m-d') < date('Y-m-d', strtotime($customer['new_package_effective_date'])))) {
			$data['effective_date_not_reached'] = true;
		} else {
			$data['effective_date_not_reached'] = false;
		}

		if (!empty($customer['preferred_install_datetime'])) {
			$datetime = new \DateTime($customer['preferred_install_datetime']);
			$data['preferred_install_date'] = $datetime->format('Y/m/d');
			$data['preferred_install_time'] = $datetime->format('H:i');
		} else {
			$data['preferred_install_date'] = '';
			$data['preferred_install_time'] = '';
		}

		if(!empty($customer['customer_no'])) {
			$user_list = array_column($this->common_model->get_user_list(), 'display_name', 'username');
			$data['created_by'] = $user_list[$customer['created_by']] ?? '-';
			$data['created_at'] = $customer['created_date'] ?? '-';
			$data['updated_by'] = $user_list[$customer['modified_by']] ?? '-';
			$data['updated_at'] = $customer['modified_date'] ?? '-';	
		}

		//Currently selected equipment
		$data['current_equipment_type'] = '';
		$data['current_serial_no'] = '';
		if(!empty($customer['customer_no'])) {
			$data['equipment_list'] = $this->customer_model->get_equipment_record($customer_no);
		}

		$data['user_idx'] = $this->user['idx'];
		$data['has_jumpstart_permission'] = ($this->user['acl_role'] == 1 || $this->user['acl_role'] == 6);

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('termination_sop')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		$data['termination_flow'] = $cfg['termination_sop'] ?? 0;

		$data['termination_init'] = check_acl('customer', 'TI', false);
		$data['termination_confirm'] = check_acl('customer', 'TC', false);

		$latest_termination_flow = $this->customer_model->check_latest_termination_flow($customer_no);
		$data['current_termination_flow'] = $latest_termination_flow['termination_status'] ?? 'A';
				
		$this->vars['jsfiles'][] = 'js/bootbox.min.js';

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/customer_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function welcome_note( $customer_no ){
		
		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print bill statements';
		
		$cust_info = $this->customer_model->get_customer( $customer_no );
		
		$cust_info['display_name'] = $cust_info['name'] ;
		$cust_info['display_addr1'] =  $cust_info['inst_addr1'] != '' ? $cust_info['inst_addr1'] : $cust_info['bill_addr1'] ;
		$cust_info['display_addr2'] =  $cust_info['inst_addr2'] != '' ? $cust_info['inst_addr2'] : $cust_info['bill_addr2'] ;			
		$cust_info['display_city'] =  $cust_info['inst_city'] != '' ? $cust_info['inst_city'] : $cust_info['bill_city'] ;
		$cust_info['display_postcode'] =  $cust_info['inst_postcode'] != '' ? $cust_info['inst_postcode'] : $cust_info['bill_postcode'] ;			
		$cust_info['display_state'] =  $cust_info['inst_state'] != '' ? $cust_info['inst_state'] : $cust_info['bill_state'] ;			
		$content_data['data']		= $cust_info;
		$data['msg'] 				= $this->msg;
		$gen_pdf = 1 ; 
		$html = "";
		if($gen_pdf == '1')
		{
			//$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('customer/welcome_note', $content_data, true);
			//$html .= $this->load->view('templates/print_footer', '', true);
			
			$this->load->helper(array('dompdf', 'file'));
			$pdf_name = $cust_info['customer_no'].'_welcome_note';
			$data = pdf_create(trim($html), $pdf_name, true);	
			
			//~ $result = write_file('temp/'.$pdf_name.'.pdf', $data);							
			//~ return $result;			
		}
		else
		{
			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', '', true);
			$html .= $this->parser->parse('customer/welcome_note', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);
		}
		
		echo $html ;
		
	}

	function export_deposit()
    {
		$deposit_date_start  			= $this->input->post('deposit_date_start');
		$deposit_date_end    			= $this->input->post('deposit_date_end');
		$sel_category					= $this->input->post('sel_category');

		if($deposit_date_start == '') 	$deposit_date_start = date('Y-m-01');;
		if($deposit_date_end == '') 	$deposit_date_end = date('Y-m-t');
		if (empty($sel_category))		$sel_category 	= 'r';

		$data['page_title'] 			= 'Export Deposit';
		$data['msg'] 					= $this->msg;
		$data['form_action'] 			= base_url('customer/export_deposit');
		$data['sel_category_list'] 		= $this->common_model->get_category_list();
		$data['deposit_date_start'] 	= $deposit_date_start;
		$data['deposit_date_end']   	= $deposit_date_end;
		$data['sel_category'] 			= $sel_category;
		
		$result 						= $this->customer_model->get_deposit_export($sel_category, $deposit_date_start, $deposit_date_end);
		$data['row_data'] 				= $result['row'];
		$data['pagination'] 			= paginationSettings('', $result['total_row']);
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('customer/export_deposit',$data);
		$this->load->view('templates/footer');
	}

	function excel_filter()
	{
		if(!$_POST)
		{
			$this->session->set_flashdata("warning_msg", 'invalid access');
			redirect('customer/export_deposit');
		}
		else
		{
			$deposit_date_start  		= $this->input->post('deposit_date_start');
			$deposit_date_end    		= $this->input->post('deposit_date_end');
			$sel_category				= $this->input->post('sel_category');

			if($deposit_date_start == '') 	$deposit_date_start = date('Y-m-01');;
			if($deposit_date_end == '') 	$deposit_date_end = date('Y-m-t');
			if (empty($sel_category))		$sel_category 	= 'r';

			$this->load->model('xls_model');
			$this->xls_model->xls_export($this->input->post());
		}
	}
	
	/*
	 * this function has moved to customer_model 
	 * */
		
	//~ function get_customer($customer_no='')
	//~ {
		//~ $query = $this->db->query("SELECT c.* 
							//~ FROM customer c
							//~ WHERE c.customer_no='".$this->db->escape_str($customer_no)."'
							//~ LIMIT 1");
		//~ if ($query->num_rows() > 0)	{
			//~ $return_val = $query->row_array();
			//~ 
			//~ if($return_val['signup_date'] == 0){
				//~ $return_val['signup_date'] = '';
			//~ }
			//~ if($return_val['activated_date'] == 0){
				//~ $return_val['activated_date'] = '';
			//~ }
			//~ if($return_val['suspended_date'] == 0){
				//~ $return_val['suspended_date'] = '';
			//~ }
			//~ if($return_val['terminated_date'] == 0){
				//~ $return_val['terminated_date'] = '';
			//~ }
			//~ $return_val['btn_delete']='enabled';
		//~ }
		//~ else 
		//~ {
			//~ //Asign blank data for add new customer
			//~ $return_val['customer_no']='';
			//~ $return_val['name']='';
			//~ $return_val['reg_no']='';
			//~ $return_val['gst_no']='';
			//~ $return_val['pic_name']='';
			//~ $return_val['status']='r';
			//~ $return_val['category']='r';
			//~ $return_val['building']='0';
			//~ $return_val['package']='';
			//~ $return_val['package_name']='';
			//~ $return_val['monthly_charge']='';
			//~ $return_val['yearly_charge']='';
			//~ $return_val['bill_cycle_month'] = '1';
			//~ 
			//~ $return_val['login_username']='';
			//~ $return_val['login_password']='';
			//~ $return_val['package_month']='';
			//~ $return_val['stop_service_after']='';
			//~ $return_val['contract_month']='0';
			//~ 
			//~ $return_val['inst_addr1']= '';
			//~ $return_val['inst_addr2']= '';
			//~ $return_val['inst_city']= '';
			//~ $return_val['inst_postcode']= '';
			//~ $return_val['inst_state']= '';
			//~ $return_val['bill_name']= '';
			//~ $return_val['bill_addr1']= '';
			//~ $return_val['bill_addr2']= '';
			//~ $return_val['bill_city']= '';
			//~ $return_val['bill_postcode']= '';
			//~ $return_val['bill_state']= '';
			//~ 
			//~ $return_val['bill_by_post']='';
			//~ $return_val['bill_by_email']='1';
			//~ 
			//~ $return_val['tel_num']= '';
			//~ $return_val['fax_num']= '';
			//~ $return_val['mobile_num']= '';
			//~ $return_val['email_1']= '';
			//~ $return_val['email_2']= '';
			//~ $return_val['nric']= '';
			//~ $return_val['passport']= '';
			//~ $return_val['date_of_birth']= '';
			//~ $return_val['gender'] = 'm';
			//~ $return_val['race'] = 'm';
			//~ $return_val['marital_status'] = 's';
			//~ $return_val['household'] = '';
			//~ 
			//~ $return_val['ownership_type'] = '';
			//~ $return_val['no_of_employee'] = '';
			//~ $return_val['no_of_branches'] = '';
			//~ 
			//~ $return_val['signup_date'] = date('Y-m-d');
			//~ $return_val['activated_date'] = '';
			//~ $return_val['suspended_date'] = '';
			//~ $return_val['terminated_date'] = '';
			//~ $return_val['dealer'] = '';
			//~ $return_val['remark'] = '';
			//~ $return_val['payment_term'] = '30';
			//~ 
			//~ $return_val['btn_delete']='disabled';
		//~ }
		//~ 
		//~ return $return_val;
	//~ }

	function customer_statement($customer_no,$gen_pdf = 0 , $pdf_name = ''){

		$customer_model = $this->customer_model->customer_detail($customer_no, $gen_pdf, $pdf_name);

		$this->load->helper('form');
		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print bill statements';
		$content_data['customer_model'] = $customer_model;
		$data['msg'] 				= $this->msg;
		$gen_pdf 					= $customer_model['gen_pdf'];

		$html  = '';

			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', '', true);
			$html .= $this->parser->parse('customer/customer_statement', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			echo $html;

	}

	function customer_filtered($layout){

		$search 	= '';
		$category 	= '';
		$status 	= '';


		$search = $this->input->post('txt_search',TRUE);
		$category = $this->input->post('sel_category',TRUE);
		$status = $this->input->post('sel_status',TRUE);

		// var_dump($category);
		// die();

		if ($search == '') $search = '';
		if ( empty($category)) $category = 'all';
		if ( empty($status)) $status = 'r';

		$query_where = '';

		if ($search != '' && !empty($search)) {
			$query_where = "AND c.category = '$search' ";
		}
		if ($category != 'all' && !empty($category)) {
			$query_where = "AND c.category = '$category' ";
		}
		if ($status != 'all' && !empty($status)) {
			$query_where = $query_where."AND cs.status = '$status' ";
		}
		
		$content_data['rows'] = $this->customer_model->filtered_customer($search,$query_where);

		$header_data = "Customer record";

		$html  = '';

			$html .= $this->load->view('templates/print_header', $header_data, true);
			$html .= $this->load->view('templates/print_menu', '', true);
			$html .= $this->parser->parse('customer/print_multiple_customer', $content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			echo $html;
	}

	function ajax_verify_radius_account(){
		
		$return_val = array( 'exist' => 0 );
		$login_username	= $this->input->post('login_username');
		$customer_no	= $this->input->post('customer_no');
		$radius_login_username = $login_username;
		
		if (!empty($_SESSION['config']['radius_suffix'])) {
			if( strstr( $login_username, $_SESSION['config']['radius_suffix'] ) === false ){
				$radius_login_username = $login_username . $_SESSION['config']['radius_suffix'];
			}
		}

		$self_check = '';
		if (!empty($customer_no)) {
			$customer = $this->customer_model->get_customer($customer_no);
			$self_check = $customer['login_username'];

			if ($radius_login_username == $self_check) {
				$return_val['exist'] = 2;
				echo json_encode( $return_val );
				return false;
			}
		}
		
		$result1['exist'] = 0;
		$local = $this->customer_model->filtered_customer( "", " AND c.login_username = '".$radius_login_username."' ");
		if (!empty($customer_no)) {
			foreach($local as $res_key => $res1) {
				if ($res1['customer_no'] == $customer_no) {
					unset($local[$res_key]);
				}
			}
		}
		$result1['exist'] = empty( $local ) ? 0 : 1 ;
			
		$result2['exist'] = 0;
		$result2 = $this->customer_model->radius_account_exist( $radius_login_username, $self_check ) ;
		
		if( $result1['exist'] == 0 && $result2['exist'] == 0 )
			$return_val['exist'] = 0 ;
		else
			$return_val['exist'] = 1 ;

		//$return_val['sql'] = $result2['sql'];
		
		echo json_encode( $return_val );
		
	}

	function ajax_get_deposit_detail(){
		
		$customer_no = $this->input->post('customer_no');
		$deposits = $this->input->get_deposit_history( $customer_no );
		
	}
	
	function installation_form( $customer_no ){

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$customer = $this->customer_model->get_customer( $customer_no );

		$header_data 				= $this->vars;
		$header_data['title']		= 'Installation Form';
		$header_data['description']	= 'to print installation form';
		$header_data['cssfiles'][] 	= 'css/theme/bootstrap-grid.css';
		
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$customer['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		
		if( $customer['pic_name'] == '' ){
			$customer['pic_name'] = $customer['name'];
		}
		
		if( $customer['mobile_num'] != '' ){
			$customer['contact_no'] = $customer['mobile_num'];
		}elseif( $customer['tel_num'] != '' ){
			$customer['contact_no'] = $customer['tel_num'];
		}
		
		$this->load->model('building_model');
		$building = $this->building_model->get_building( $customer['building'] );
		$customer['building_name'] = $building['name'];
		
		if( $customer['category'] == 'r' ){
			$customer['residential_check'] = '/';
			$customer['business_check'] = '';
		}else{
			$customer['residential_check'] = '';
			$customer['business_check'] = '/';			
		}
		
		if( $customer['contract_month'] != '' && $customer['contract_month'] >= 0 ){
			$customer['contract_month'] = $customer['contract_month'] . ' month(s)';
		}

		//company name and address
		$customer['company_addr_1'] = $_SESSION['config']['company_addr_1'];
		$customer['company_addr_2'] = $_SESSION['config']['company_addr_2'];
		$customer['company_addr_3'] = $_SESSION['config']['company_addr_3'];
		$customer['company_postal'] = $_SESSION['config']['company_postal'];
		$customer['company_city'] = $_SESSION['config']['company_city'];
		$customer['company_phone'] = $_SESSION['config']['company_phone'];
	
		// signature
		$signature = $this->common_model->get_file_attachment_with_signature_form_info('customer', $customer_no, 'customer_signature', 0);

		if (!empty($signature)) {
			$customer['signature'] 		= $this->config->item('upload_url') . $signature[0]['local_path'];
			$customer['sign_date'] 		= $signature[0]['created_date'];
			$customer['signer_name'] 	= $signature[0]['signer_name'];
			$customer['signer_ic'] 		= $signature[0]['signer_ic'];
		} else {

			$select = 'acc_name, icno, pic_name, pic_nric_passport, acc_type';
			$where = ['acc_id ' => $customer['profile_id']];
			$profile = $this->common_model->get_table('profile', $select, $where);

			$customer['signature'] 		= '';
			$customer['sign_date'] 		= '';
			if($profile[0]['acc_type'] === 'r'){
				$signer_name = $profile[0]['acc_name'];
				$signer_ic = $profile[0]['icno'];
			}else{
				$signer_name = $profile[0]['pic_name'];
				$signer_ic = $profile[0]['pic_nric_passport'];
			}
			$customer['signer_name'] 	= $signer_name;
			$customer['signer_ic'] 		= $signer_ic;
		}

		//Equipment list
		$customer['equipment_types'] = $this->common_model->get_equipment_type_list();

		//Currently selected equipment
		$customer['equipment_list'] = $this->customer_model->get_equipment_record($customer_no);

		$frontend_url = $this->config->item('frontend_url') ?? false;
		$customer['tnc_url'] = $frontend_url 
									? 
										$customer['category'] == 'r' 
											?
												$frontend_url . $this->config->item('residential_tnc_endpoint') ?? 'tnc'
											:
												$frontend_url . $this->config->item('commercial_tnc_endpoint') ?? 'CommercialTnc'
									:
										'';

		$report_send = 0;

		if (!empty($contact_list)) {

			$scode = md5($customer_no . $this->e_key);
			puppeteer_print_preview($this->config->item('base_url').'pdfapi/installation_form/'.$customer_no.'/'.$scode, $this->config->item('upload_path').'/temp/pdf/installation_form_'.$customer_no.'.pdf', $this->config->item('proj_path'), $this->config->item('chrome_loc'));

			$this->load->library('whatsapp_template');
			$meta_template = $this->whatsapp_template->build('ITELCO DOCS', ['doc_type' => 'Installation Form']);

			$json_contact_list = json_decode($contact_list);

			//$content_data['isprint'] = 1;

			$email_list = $json_contact_list[0];
			$whatsapp_list = $json_contact_list[1];
			$telegram_list = $json_contact_list[2];

			$send_array = array();
			$send_array['send_type'] = 'custom';
			$send_array['acc_id'] = 0;
			$send_array['customer_no'] = $customer_no;
			$send_array['user_id'] = 0;
			$send_array['controller'] = 'customer';
			$send_array['doc_id'] = 0;
			$send_array['send_method'] = 'manual';
			$send_array['acc_name'] = 'Installer/Customer';
			//attachcment related
			$send_array['attachment'] = $this->config->item('upload_path').'/temp/pdf/installation_form_'.$customer_no.'.pdf';
			//email
			$send_array['subject'] = 'Installation Form';
			$send_array['body'] = 'Attached herewith is the installation form.';
			$send_array['email_starter'] = 'Installation Form';
			$send_array['doc_type'] = '[Installation Form]';
			$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
			$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];
			$send_array['email_list'] = $email_list;
			$send_array['whatsapp_list'] = $whatsapp_list;
			$send_array['telegram_list'] = $telegram_list;

			$send_result = $this->docs_log_model->do_send($send_array);

			$report_send = 1;

		}

		$menu_data = array();
		$menu_data['send_btn'] = 1;
		$menu_data['form_action'] = base_url('customer/installation_form/'.$customer_no);
		$menu_data['report_send'] = $report_send;

		/*
		echo "<pre>";
		print_r($customer);
		echo "</pre>";
		exit;
		*/
		
		$this->load->view('templates/print_header', $header_data);
		$this->load->view('templates/installation/print_menu', $menu_data);
		$this->parser->parse('customer/installation_form', $customer);
		$this->load->view('templates/print_footer', '');

	}
	
	function termination_form( $customer_no ){
		
//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$customer = $this->customer_model->get_customer( $customer_no );

		$header_data 				= $this->vars;
		$header_data['title']		= 'Termination Form';
		$header_data['description']	= 'to print termination form';
		$header_data['cssfiles'][] 	= 'css/theme/bootstrap-grid.css';
		
		$config = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$customer['comp_name'] = ( isset( $config[0]['val'] ) && $config[0]['val'] != '' ) ? $config[0]['val'] : '';
		
		if( $customer['pic_name'] == '' ){
			$customer['pic_name'] = $customer['name'];
		}
		
		if( $customer['mobile_num'] != '' ){
			$customer['contact_no'] = $customer['mobile_num'];
		}elseif( $customer['tel_num'] != '' ){
			$customer['contact_no'] = $customer['tel_num'];
		}
		
		$this->load->model('building_model');
		$building = $this->building_model->get_building( $customer['building'] );
		$customer['building_name'] = $building['name'];
		
		if( $customer['category'] == 'r' ){
			$customer['residential_check'] = '/';
			$customer['business_check'] = '';
		}else{
			$customer['residential_check'] = '';
			$customer['business_check'] = '/';			
		}
		
		if( $customer['contract_month'] != '' && $customer['contract_month'] >= 0 ){
			$customer['contract_month'] = $customer['contract_month'] . ' month(s)';
		}

		//company name and address
		$customer['company_addr_1'] = $_SESSION['config']['company_addr_1'];
		$customer['company_addr_2'] = $_SESSION['config']['company_addr_2'];
		$customer['company_addr_3'] = $_SESSION['config']['company_addr_3'];
		$customer['company_postal'] = $_SESSION['config']['company_postal'];
		$customer['company_city'] = $_SESSION['config']['company_city'];
		$customer['company_phone'] = $_SESSION['config']['company_phone'];
	
		// signature
		$signature = $this->common_model->get_file_attachment_with_signature_form_info('customer', $customer_no, 'termination_signature', 0, 'termination form', NULL, NULL);

		if (!empty($signature)) {
			$customer['signature'] 		= $this->config->item('upload_url') . $signature[0]['local_path'];
			$customer['sign_date'] 		= $signature[0]['created_date'];
			$customer['signer_name'] 	= $signature[0]['signer_name'];
			$customer['signer_ic'] 		= $signature[0]['signer_ic'];
		} else {

			$select = 'acc_name, icno, pic_name, pic_nric_passport, acc_type';
			$where = ['acc_id ' => $customer['profile_id']];
			$profile = $this->common_model->get_table('profile', $select, $where);

			$customer['signature'] 		= '';
			$customer['sign_date'] 		= '';
			if($profile[0]['acc_type'] === 'r'){
				$signer_name = $profile[0]['acc_name'];
				$signer_ic = $profile[0]['icno'];
			}else{
				$signer_name = $profile[0]['pic_name'];
				$signer_ic = $profile[0]['pic_nric_passport'];
			}
			$customer['signer_name'] 	= $signer_name;
			$customer['signer_ic'] 		= $signer_ic;
		}

		//Equipment list
		$customer['equipment_types'] = $this->common_model->get_equipment_type_list();

		//Currently selected equipment
		$customer['equipment_list'] = $this->customer_model->get_equipment_record($customer_no);

		$frontend_url = $this->config->item('frontend_url') ?? false;
		$customer['tnc_url'] = $frontend_url 
									? 
										$customer['category'] == 'r' 
											?
												$frontend_url . $this->config->item('residential_tnc_endpoint') ?? 'tnc'
											:
												$frontend_url . $this->config->item('commercial_tnc_endpoint') ?? 'CommercialTnc'
									:
										'';

		$report_send = 0;

		if (!empty($contact_list)) {

			$scode = md5($customer_no . $this->e_key);
			puppeteer_print_preview($this->config->item('base_url').'pdfapi/termination_form/'.$customer_no.'/'.$scode, $this->config->item('upload_path').'/temp/pdf/termination_form_'.$customer_no.'.pdf', $this->config->item('proj_path'), $this->config->item('chrome_loc'));

			$this->load->library('whatsapp_template');
			$meta_template = $this->whatsapp_template->build('ITELCO DOCS', ['doc_type' => 'Termination Form']);
			
			$json_contact_list = json_decode($contact_list);

			//$content_data['isprint'] = 1;

			$email_list = $json_contact_list[0];
			$whatsapp_list = $json_contact_list[1];
			$telegram_list = $json_contact_list[2];

			$send_array = array();
			$send_array['send_type'] = 'custom';
			$send_array['acc_id'] = 0;
			$send_array['customer_no'] = $customer_no;
			$send_array['user_id'] = 0;
			$send_array['controller'] = 'customer';
			$send_array['doc_id'] = 0;
			$send_array['send_method'] = 'manual';
			$send_array['acc_name'] = 'Installer/Customer';
			//attachcment related
			$send_array['attachment'] = $this->config->item('upload_path').'/temp/pdf/termination_form_'.$customer_no.'.pdf';
			//email
			$send_array['subject'] = 'Termination Form';
			$send_array['body'] = 'Attached herewith is the termination form.';
			$send_array['email_starter'] = 'Termination Form';
			$send_array['doc_type'] = '[Termination Form]';
			$send_array['meta_template'] = $meta_template['meta_template_name'] ?? '';
			$send_array['meta_vars'] = $meta_template['meta_variable'] ?? [];
			$send_array['email_list'] = $email_list;
			$send_array['whatsapp_list'] = $whatsapp_list;
			$send_array['telegram_list'] = $telegram_list;

			$send_result = $this->docs_log_model->do_send($send_array);

			$report_send = 1;

		}

		$menu_data = array();
		$menu_data['send_btn'] = 1;
		$menu_data['equip_btn'] = 1;
		$menu_data['form_action'] = base_url('customer/termination_form/'.$customer_no);
		$menu_data['report_send'] = $report_send;

		/*
		echo "<pre>";
		print_r($customer);
		echo "</pre>";
		exit;
		*/

		$customer['customer_sign'] = $this->customer_model->check_termination_sign($customer_no);

		$customer['termination_init'] = check_acl('customer', 'TI', false);
		$customer['termination_confirm'] = check_acl('customer', 'TC', false);
		$customer['is_customer'] = false;

		$customer['sel_state_list'] 			= $this->common_model->get_state_list();
		$customer['sel_building_list'] 	= $this->common_model->get_building_list();

		$latest_termination_flow = $this->customer_model->check_latest_termination_flow($customer_no);
		$customer['current_termination_flow'] = $latest_termination_flow['termination_status'] ?? 'A';

		$menu_data['current_termination_flow'] = $customer['current_termination_flow'];
		$menu_data['current_status'] = (isset($this->termination_status[$customer['current_termination_flow']]) ? $this->termination_status[$customer['current_termination_flow']] : '');

		$menu_data['reject_reason'] = $latest_termination_flow['rejection_remarks'];

		$customer['termination_data'] = $this->customer_model->get_latest_termination_info($customer_no);

		$customer['customer_no'] = $customer_no;
		
		$this->load->view('templates/print_header', $header_data);
		$this->load->view('templates/termination/print_menu', $menu_data);
		$this->parser->parse('customer/termination_form', $customer);
		$this->load->view('templates/print_footer', '');

	}

	//~ function ajax_delete_customer_pic(){
		
		//~ $return_val = array();
		
		//~ $this->input->post('customer_no');
		//~ $this->input->post('pic_id');
		
		//~ $return_val = $this->customer_model->delete_customer_pic( $this->input->post('customer_no'), $this->input->post('pic_id') );
		
		//~ echo json_encode( $return_val );
		
	//~ }

	function customerXML(){
	
		$filename ="customer.xls";
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);
		
	
		$file_path = "/var/www/html/itelco/temp/customerXML/" ;
		
		$custArr = array();

		
		$string_to_html = "Cust. #;Cust. Name;Status;Category;PIC;Tel;Fax;Email;Addr1;Addr2;Addr3;Addr4;Agent;PayTerm;CreateDate\n";
		if(is_dir($file_path))
		{
			$files = scandir($file_path);
			$z = 0;
			foreach($files as $key => $file)
			{
				
				if( $file != "." && $file != ".." )
				{
					$feed = file_get_contents( $file_path . $file );
					$xml = simplexml_load_string( $feed );
					
					$custArr[$key]	= array(	"customer_no" => "",
												"name" => "",
												"status" => "",
												"category" => "",
												"pic" => "",
												"tel_num" => "",
												"fax_num" => "",
												"email_1" => "",
												"inst_addr1" => "",
												"inst_addr2" => "",
												"inst_addr3" => "",
												"inst_addr4" => "",
												"dealer" => "",
												"payment_term" => "",
												"create_date" => ""
											);
					
					foreach( $xml->ROWDATA->ROW->sdsBranch[0] AS $keys => $val ){
						//echo $keys . ' ==> ' . $val . '<br />';
						$custArr[$z]['customer_no'] = (string)$xml->ROWDATA->ROW->attributes()->CODE ;
						$custArr[$z]['name'] = (string)$xml->ROWDATA->ROW->attributes()->COMPANYNAME ;
						$custArr[$z]['payment_term'] = (int)$xml->ROWDATA->ROW->attributes()->CREDITTERM ;
						$custArr[$z]['status'] = (string)$xml->ROWDATA->ROW->attributes()->STATUS == 'A' ? 'r' : 's';
						$custArr[$z]['create_date'] = (string)$xml->ROWDATA->ROW->attributes()->CREATIONDATE;
						$custArr[$z]['dealer'] = (string)$xml->ROWDATA->ROW->attributes()->AGENT;
						
						//$custArr[$key]['DTLKEY'] = isset( $val['DTLKEY'] ) ? (string)$val['DTLKEY'] : '';
						$custArr[$z]['inst_addr1'] = isset( $val['ADDRESS1'] ) ? (string)$val['ADDRESS1'] : '';
						$custArr[$z]['inst_addr2'] = isset( $val['ADDRESS2'] ) ? (string)$val['ADDRESS2'] : '';
						$custArr[$z]['inst_addr3'] = isset( $val['ADDRESS3'] ) ? (string)$val['ADDRESS3'] : '';
						$custArr[$z]['inst_addr4'] = isset( $val['ADDRESS4'] ) ? (string)$val['ADDRESS4'] : '';
						$custArr[$z]['pic'] = isset( $val['ATTENTION'] ) ? (string)$val['ATTENTION'] : '';
						$custArr[$z]['tel_num'] = isset( $val['PHONE1'] ) ? (string)$val['PHONE1'] : '';
						$custArr[$z]['fax_num'] = isset( $val['FAX1'] ) ? (string)$val['FAX1'] : '';
						$custArr[$z]['email_1'] = isset( $val['EMAIL1'] ) ? (string)$val['EMAIL1'] : '';
						
						$string_to_html .=	"'".$custArr[$z]['customer_no'] . "';" . 
											"'".$custArr[$z]['name'] . "';" . 
											"'".$custArr[$z]['status'] . "';" . 
											"'r';" . 
											"'".$custArr[$z]['pic'] . "';" . 
											"'".$custArr[$z]['tel_num'] . "';" . 
											"'".$custArr[$z]['fax_num'] . "';" . 
											"'".$custArr[$z]['email_1'] . "';" . 
											"'".$custArr[$z]['inst_addr1'] . "';" . 
											"'".$custArr[$z]['inst_addr2'] . "';" . 
											"'".$custArr[$z]['inst_addr3'] . "';" . 
											"'".$custArr[$z]['inst_addr4'] . "';" . 
											"'".$custArr[$z]['dealer'] . "';" . 
											"'".$custArr[$z]['payment_term'] . "';" . 
											"'".$custArr[$z]['create_date'] . "'\n" ;
					}
				
				
				$z++;
				}else{
					continue;
				}
				
			}
			

			
			
			//_debug_array( $custArr );
			
			
			
		}
	
		if( $string_to_html != '' )
			echo $string_to_html ;
		
	}

	function insert_cisco(){
		
		$this->customer_model->insert_cisco();
		
	}

	function create_new_contract() {
		$customer_no = $this->input->post('customer_no');
		$new_package_id = $this->input->post('package');
		$new_package_effective_date = $this->input->post('new_package_effective_date');
		$contract_month = $this->input->post('contract_month');

		//address
		$address = array();
		$address['building'] = $this->input->post('building');
		$address['inst_unit_no'] = $this->input->post('inst_unit_no');
		$address['inst_addr1'] = $this->input->post('inst_addr1');
		$address['inst_addr2'] = $this->input->post('inst_addr2');
		$address['inst_addr3'] = $this->input->post('inst_addr3');
		$address['inst_city'] = $this->input->post('inst_city');
		$address['inst_postcode'] = $this->input->post('inst_postcode');
		$address['inst_state'] = $this->input->post('inst_state');

		$result = ['status' => 'err_msg', 'message' => 'Invalid Action'];

        if(!empty($customer_no) || !empty($new_package_id)){
            $result = $this->customer_model->create_new_contract($customer_no, $new_package_id, $new_package_effective_date, $contract_month, $address);
        }
		echo json_encode($result);
		return false;
	}

	function continue_contract() {
		$customer_no = $this->input->post('customer_no');
		$new_package_id = $this->input->post('package');
		$new_package_effective_date = $this->input->post('new_package_effective_date');
		$contract_month = $this->input->post('contract_month');

		//address
		$address = array();
		$address['building'] = $this->input->post('building');
		$address['inst_unit_no'] = $this->input->post('inst_unit_no');
		$address['inst_addr1'] = $this->input->post('inst_addr1');
		$address['inst_addr2'] = $this->input->post('inst_addr2');
		$address['inst_addr3'] = $this->input->post('inst_addr3');
		$address['inst_city'] = $this->input->post('inst_city');
		$address['inst_postcode'] = $this->input->post('inst_postcode');
		$address['inst_state'] = $this->input->post('inst_state');

		$result = ['status' => 'err_msg', 'err_msg' => 'Invalid Action'];

        if(!empty($customer_no) || !empty($new_package_id)){
            $result = $this->customer_model->continue_contract($customer_no, $new_package_id, $new_package_effective_date, $contract_month, $address);
        }
		echo json_encode($result);
		return false;
	}

	function begin_termination_flow() {
		$customer_no = $this->input->post('customer_no');

		$result = ['status' => 'err_msg', 'err_msg' => 'Invalid Action'];

		$this->customer_model->create_termination_status_record($customer_no, 'P', date('Y-m-d H:i:s'), $this->user['idx']);

		$this->customer_model->init_termination_record($customer_no);

		$result = [
                'success' => true, 
                'message' => 'Started Termination Flow.'
        ];

		echo json_encode($result);
		return false;
	}

	function cancel_termination_flow() {
		$customer_no = $this->input->post('customer_no');

		$result = ['status' => 'err_msg', 'err_msg' => 'Invalid Action'];

		$this->customer_model->create_termination_status_record($customer_no, 'A', date('Y-m-d H:i:s'), $this->user['idx']);

		$this->customer_model->termination_wipe_tnc($customer_no);

		$result = [
                'success' => true, 
                'message' => 'Cancelled Termination Flow.'
        ];

		echo json_encode($result);
		return false;
	}

	function renew_contract() {
		$customer_no = $this->input->post('customer_no');
		$package_id = $this->input->post('package');
		$new_package_effective_date = $this->input->post('new_contract_start_date');
		$contract_month = intval($this->input->post('new_contract_duration'));

		//address
		$address = array();
		$address['building'] = $this->input->post('building');
		$address['inst_unit_no'] = $this->input->post('inst_unit_no');
		$address['inst_addr1'] = $this->input->post('inst_addr1');
		$address['inst_addr2'] = $this->input->post('inst_addr2');
		$address['inst_addr3'] = $this->input->post('inst_addr3');
		$address['inst_city'] = $this->input->post('inst_city');
		$address['inst_postcode'] = $this->input->post('inst_postcode');
		$address['inst_state'] = $this->input->post('inst_state');

		$result = ['status' => 'err_msg', 'err_msg' => 'Invalid Action'];

		if ($contract_month == 0) {
			$result['err_msg'] = 'Zero contract month, no need to renew contract.';
			echo json_encode($result);
			return;
		}

		if ($contract_month < 0) {
			$result['err_msg'] = 'Please enter a valid contract duration in months.';
			echo json_encode($result);
			return;
		}

		if (!empty($customer_no) && !empty($package_id)) {
			$result = $this->customer_model->renew_contract($customer_no, $package_id, $new_package_effective_date, $contract_month, $address);
		} else {
			$result['err_msg'] = 'Customer number or package ID missing.';
		}

		echo json_encode($result);
		return false;
	}

	function get_last_package($customer_no, $package_no) {
		$last_package = [];
		if(!empty($customer_no) && !empty($package_no)) {
			$last_package = $this->customer_model->get_last_package($customer_no, $package_no);
		}
		echo json_encode($last_package);
	}

	public function upload_signature() {
		$customer_no 	= $this->input->post('customer_no');
		$signer_name 	= $this->input->post('signer_name');
		$signer_ic 		= $this->input->post('signer_ic');
		$signature_file = $_FILES['signature_file'] ?? false;
		$upload_path = $this->config->item('upload_path');

		if($signature_file && !empty($signature_file['tmp_name']) && file_exists($signature_file['tmp_name'])) {
			$prefix = date('YmdHis');
			$new_file_name = "signature-{$customer_no}-{$prefix}.png";
			$local_path = "/upload/" . $new_file_name;
			$full_destination = $upload_path . $local_path;

			$src = imagecreatefrompng($signature_file['tmp_name']);
			$width = imagesx($src);
			$height = imagesy($src);

			$new_width = 400;
			$new_height = ($height / $width) * $new_width;
			$dst = imagecreatetruecolor($new_width, $new_height);

			imagealphablending($dst, false);
			imagesavealpha($dst, true);
			$transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
			imagefill($dst, 0, 0, $transparent);

			imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			if (imagepng($dst, $full_destination)) {
				$existing = $this->common_model->get_file_attachment('customer', $customer_no, 'customer_signature', 0);
				if (!empty($existing)) {
					foreach ($existing as $row) {
						$this->common_model->remove_attachment($row['file_id'], $customer_no, 'customer_signature');
					}
				}
				$this->common_model->insert_file_attachment($new_file_name, $local_path, 'customer', $customer_no, 'Signature Upload', 0, 'customer_signature', 0, 0, $this->user['idx']);			

			} else {
				echo json_encode(['status' => 'err_msg', 'err_msg' => 'Disk error']);
				return false;
			}
		}
		
		$new_signature 			= $this->common_model->get_file_attachment('customer', $customer_no, 'customer_signature', 0);
		if(!empty($new_signature)){
			$this->save_signature_info([
				'form_type' => 'installation form',
				'signer_name' => $signer_name,
				'signer_ic' => $signer_ic,
				'customer_no' => $customer_no,
				'signature_file_id' => $new_signature[0]['file_id'] ?? 0
			], $customer_no, 'installation form');
		}
		
		echo json_encode(['status' => 'success']);

		return false;
	}

	public function save_equipment_used() {
		$customer_no = $this->input->post('customer_no');
		$equipment_list = $this->input->post('equipment');

		if (!empty($customer_no)) {
			$this->save_equipment_info($equipment_list, $customer_no);
			echo json_encode(['status' => 'success']);
		} else {
			echo json_encode(['status' => 'error', 'err_msg' => 'Invalid Customer']);
		}
		return false;
	}

	private function save_signature_info (array $data, $customer_no, $form_type) {
		if(!empty($data) && $customer_no){
			$this->customer_model->save_form_signatures($data, $customer_no, $form_type);
		}
	}

	private function save_equipment_info (array $data, $customer_no) {
		if(!empty($data) && $customer_no){
			$this->customer_model->save_equipment_info($data, $customer_no);
		}
	}

	public function init_termination_send($customer_no = NULL) {
		if (empty($customer_no)) {
			echo json_encode([]);
			return;
		}

		$result = array();

		//get if any data is saved in the db
		$latest_data = $this->customer_model->get_latest_termination_info($customer_no);

		//check things like contract date to and from
		//unbilled months
		$latest_data['ori_unbilled_months'] = get_x_months(date('Y-m-d'), $latest_data['contract_end_date']);
		$latest_data['ori_unbilled_amt'] = number_format(($latest_data['ori_unbilled_months']*$latest_data['data_monthly_charge']),2,".","");

		//load the data and return to front
		$result = $latest_data;

		header('Content-Type: application/json');
		echo json_encode($result);
	}

	public function finalize_termination($customer_no = NULL) {
		if (empty($customer_no)) {
			echo json_encode([]);
			return;
		}

		$result = array();

		//get if any data is saved in the db
		$latest_data = $this->customer_model->get_latest_termination_info($customer_no);

		$result = $latest_data;

		header('Content-Type: application/json');
		echo json_encode($result);

	}

	public function save_term_only() {

		$err_msg = array();

		$post_data = $this->input->post();

		//validation
		if (empty($post_data['customer_no'] ?? '')) {
			$err_msg[] = 'No customer under this no. found.';
		}

		if (empty($post_data['pic_name'] ?? '')) {
			$err_msg[] = 'PIC name cannot be empty.';
		}

		if (empty($post_data['pic_email'] ?? '')) {
			$err_msg[] = 'PIC email cannot be empty.';
		}

		$email_regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		if(!preg_match($email_regex, $post_data['pic_email'])) {
		    $err_msg[] = 'Not a valid email address. If valid, please contact administrator.';
		}

		if (!empty($post_data['pic_mobile'] ?? '')) {
			if(!isValidGlobalPhone($post_data['pic_mobile'])) {
				$err_msg[] = 'Not a valid phone number. Please use the format 601xxxxxx';
			}
		}

		if (empty($post_data['effective_date'] ?? '')) {
			$err_msg[] = 'Effective Date cannot be empty.';
		}

		if (($post_data['penalty'] ?? '0') == '1') {
			if ( ((int)$post_data['unbilled_months'] ?? 0) <= 0) {
				$err_msg[] = 'Please enter a valid numeric value for unbilled months.';
			}

			if (floatval( ($post_data['unbilled_amt'] ?? 0.00)) <= 0.001) { 
				$err_msg[] = 'Please enter a valid value for unbilled amount.';
			}
		} else {
			//auto put 0 if not ticked
			$post_data['unbilled_months'] = 0;
			$post_data['unbilled_amt'] = 0.00;
		}

		if (empty($err_msg)) {
			//no errors
			$post_data['updated_by'] = $this->user['idx'];
			$this->customer_model->save_termination_info($post_data);

			$return = ['status' => 'success', 'msg' => ''];

			//if send is 1
			if (($post_data['send_form'] ?? '0') == '1') {
				//process send email to PIC for Termination signature and confirm
				$send_data = array();

				//create a temp login and password - put an expiry date (once expired, itelco user can resend)

				//update termination status to "Pending Customer Signature"
				$send_data['temp_password'] = generateRandomString(6);
				$send_data['expiry'] = date('Y-m-d', strtotime('+14 days'));

				//log this action as well
				$send_status = $this->customer_model->send_termination_form_to_customer($post_data['customer_no'], $send_data);

				if ($send_status['status'] == 'success') {
					$return = ['status' => 'success', 'msg' => ''];
				} else {
					$return = ['status' => 'error', 'msg' => implode("<br>", $send_status['msg'])];
				}
			}

		} else {
			$return = ['status' => 'error', 'msg' => implode("<br>", $err_msg)];
		}

		echo json_encode($return);

		return false;
	}

	public function finalize_term()
	{
		$err_msg = array();

		$post_data = $this->input->post();

		if (empty($post_data['customer_no'] ?? '')) {
			$err_msg[] = 'No customer under this no. found.';
		}

		$customer_no = $post_data['customer_no'];

		$termination_data = $this->customer_model->get_latest_termination_info($customer_no);

		if (empty($termination_data['data_effective_date']) || empty($customer_no)) {
			$err_msg[] = 'Effective Date or customer no. is empty.';
		}

		if (empty($err_msg)) {

			$this->customer_model->update_terminated_date($customer_no, $termination_data['data_effective_date']);

			//effective date is before today or within today
			if (strtotime($termination_data['data_effective_date'].' 12:00:00') < strtotime(date('Y-m-d 23:59:59'))) {

				//how to switch to terminate?
				$this->customer_model->create_status_record($customer_no, 'T', $termination_data['data_effective_date'], $this->user['idx']);

				//force a shutdown
				$this->customer_model->varied_suspend_account($customer_no, 1);

				//just update status and also termination status maybe?
				$this->customer_model->create_termination_status_record($customer_no, 'C', date('Y-m-d H:i:s'), $this->user['idx']);

			} else {
				//effective date is after today
			}

			//save termination form to audit folder
			$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'audit_copy' ");
			$audit_copy = $config_record[0]['val'];
			if ($audit_copy == '1') {

				$audit_file_path = $this->config->item('upload_path').'/temp/pdf/termination_form_'.$customer_no.'.pdf';
				$audit_file_name = 'termination_form_'.$customer_no.'.pdf';

				$this->load->helper('puppeteer_helper');
				$scode = md5($customer_no . $this->e_key);
				puppeteer_print_preview(
					$this->config->item('base_url').'pdfapi/termination_form/'.$customer_no.'/'.$scode, 
					$this->config->item('upload_path').'/temp/pdf/termination_form_'.$customer_no.'.pdf', 
					$this->config->item('proj_path'), 
					$this->config->item('chrome_loc')
				);

				//save to audit folder
				$this->common_model->perform_audit(
					$audit_file_path, 
					'customer_term', 
					$audit_file_name, 
					$customer_no, 
					'Customer No:'.$customer_no.' Termination Form', 
					'', 
					'', 
					$customer_no
				);

			}

			$return = ['status' => 'success', 'msg' => ''];

		} else {
			$return = ['status' => 'error', 'msg' => implode("<br>", $err_msg)];
		}

		echo json_encode($return);

		return false;

	}

	public function get_form_signature($customer_no = NULL, $form_type = NULL)
	{
		if (empty($customer_no) || empty($form_type)) {
			echo json_encode([]);
			return;
		}

		$this->db->select('signer_name, signer_ic, customer_no, form_type');
		$this->db->from('form_signatures');
		$this->db->where('customer_no', $customer_no);
		$this->db->where('form_type', urldecode($form_type));
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);

		$query = $this->db->get();
		$result = $query->row_array();

		if (!$result) {
			$this->db->select('profile_id');
			$this->db->from('customer');
			$this->db->where('customer_no', $customer_no);
			$customer = $this->db->get()->row_array();

			if (!empty($customer) && !empty($customer['profile_id'])) {
				$select = 'acc_name, icno, pic_name, pic_nric_passport, acc_type';
				$where = ['acc_id ' => $customer['profile_id']];
				$profile = $this->common_model->get_table('profile', $select, $where);

				if (!empty($profile)) {
					if ($profile[0]['acc_type'] === 'r') {
						$signer_name = $profile[0]['acc_name'];
						$signer_ic = $profile[0]['icno'];
					} else {
						$signer_name = $profile[0]['pic_name'];
						$signer_ic = $profile[0]['pic_nric_passport'];
					}

					$result = [
						'signer_name'  => $signer_name,
						'signer_ic'    => $signer_ic,
						'customer_no'  => $customer_no,
						'form_type'    => urldecode($form_type)
					];

					//for termination form
					if ($form_type == 'termination form') {

					}
				} else {
					$result = [];
				}
			} else {
				$result = [];
			}
		}

		header('Content-Type: application/json');
		echo json_encode($result);
	}

	public function get_equipment_form($customer_no = NULL)
	{
		if (empty($customer_no)) {
			echo json_encode([]);
			return;
		}

		$this->db->select('equipment_type_id, serial_no, customer_no');
		$this->db->from('customer_equipment');
		$this->db->where('customer_no', $customer_no);
		$this->db->order_by('id', 'ASC');

		$query = $this->db->get();
		$result = $query->result_array();

		header('Content-Type: application/json');
		echo json_encode($result);
	}

	public function update_is_terms_accepted() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $is_checked  = $this->input->post('is_checked', TRUE);
        $customer_no = $this->input->post('customer_no', TRUE);

        if (empty($customer_no)) {
            echo json_encode([
				'success' => false, 
				'message' => 'Invalid Customer Number.'
			]);
            return;
        }

        $result = $this->customer_model->update_terms_status($customer_no, $is_checked);

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Agreement to Terms and Conditions has been recorded.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update status or no changes made.'
            ]);
        }
    }

    public function update_is_termination_terms_accepted() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $is_checked  = $this->input->post('is_checked', TRUE);
        $customer_no = $this->input->post('customer_no', TRUE);

        if (empty($customer_no)) {
            echo json_encode([
				'success' => false, 
				'message' => 'Invalid Customer Number.'
			]);
            return;
        }

        $result = $this->customer_model->update_termination_terms_status($customer_no, $is_checked);

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Agreement to Terms and Conditions has been recorded.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update status or no changes made.'
            ]);
        }
    }

	public function dia_jumpstart() {
		$customer_no = $this->input->post('customer_no');
		$activate = $this->input->post('activate');

		$config = $this->common_model->get_table('sys_config', 'val', "`key` = 'from_name'");
		$from_name = $config['val'] ?? 'no_reply@isp.com';

		$result = $this->customer_model->dia_jumpstart($customer_no, $activate);

		if($result) {
			$this->load->model('common_model');
			$this->load->model('email_model');
			$this->load->model('whatbot_model');
			$this->load->model('telegram_model');
			$technical_users = $this->common_model->get_technical_list();

			$title = 'DIA Jumpstart action performed';
			$body = 'Customer No. ' . $customer_no . ' has performed the DIA jumpstart action to ' . $activate;

			foreach ($technical_users as $tech) {
				if(!empty($tech['allow_tech_notification'])) {
					$this->load->model('message_scheduler_model');
					
					if(!empty($tech['email'])) {
						$this->email_model->add_email_schedule([
							'recipient_emails'  => $tech['email'],
							'scheduler_id'      => '',
							'send_by'           => '',
							'email_title'       => $title,
							'email_msg'         => $body,
							'email_cust_status' => 1,
							'email_attachment'  => '',
							'email_schedule_on' => date("Y-m-d H:i:s")
						]);
					}
					
					if(!empty($tech['mobile_no']) && !empty($tech['allow_whatsapp'])) {

						$this->load->library('whatsapp_template');

						$meta_template = $this->whatsapp_template->build(
							'DIA JUMPSTART',
							[
								'customer_no' => !empty($customer_no) ? $customer_no : '-',
								'activate' => !empty($activate)
									? ($activate == 'up' ? 'Activate' : 'Deactivate')
									: '-',
							]
						);

						$message_data = [
							'message' => '[DIA Jumpstart reminder] - ' . $customer_no,
							'msg_type' => 'whatsapp',
							'msg_to' => $tech['mobile_no'],
							'customer_no' => $customer_no,
							'msg_schedule_on' => date("Y-m-d H:i:s")
						];

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
							'doc_type' => "[DIA Jumpstart reminder]",
							'whatsapp_list' => [$tech['mobile_no']],
							'meta_template' => $meta_template['meta_template_name'] ?? '',
							'meta_vars' => $meta_template['meta_variable'] ?? []
						];

						$this->message_scheduler_model->insert_new_scheduled_message($message_data, $send_array);
					}

					if(!empty($tech['telegram_id']) && !empty($tech['allow_telegram'])) {

						$telegram_data = [
							'message' => $body,
							'msg_type' => 'telegram',
							'msg_to' => $tech['telegram_id'],
							'customer_no' => $customer_no,
							'msg_schedule_on' => date("Y-m-d H:i:s")
						];

						$send_array = [
							'send_type' => 'custom',
							'acc_id' => 0,
							'customer_no' => $customer_no,
							'user_id' => 0,
							'controller' => 'cron',
							'doc_id' => 0,
							'send_method' => 'auto',
							'acc_name' => 'Itelco User',
							'subject' => $title,
							'body' => $body,
							'from' => $from_name,
							'doc_type' => "[DIA Jumpstart reminder]",
							'telegram_list' => [$tech['telegram_id']],
						];

						$this->message_scheduler_model->insert_new_scheduled_message($telegram_data, $send_array);
					}
				}
			}

			echo json_encode([
                'success' => true, 
                'message' => 'DIA Jumpstart success and notification sent to all technical users.'
            ]);
		} else {
			echo json_encode([
                'success' => false, 
                'message' => 'DIA Jumpstart failed. Please recheck any related configurations.'
            ]);
		}
	}
	
	public function reverse_relocation_process () 
	{
		$customer_no = $this->input->post('customer_no');

		if (empty($customer_no)) {
            echo json_encode([
				'success' => false, 
				'message' => 'Invalid Customer Number.'
			]);
            return;
        }

		$result = $this->customer_model->reverse_relocation_process($customer_no);

		if ($result) {
            echo json_encode([
                'success' => true
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to terminate, please try again later.'
            ]);
        }
	}
}
