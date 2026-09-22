<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('check_acl'))
{
	function check_acl($page, $action='V', $redirect=true, $acl_list='')
	{
		$acl_act = array();
		
		if ($acl_list=='') $acl_list=$_SESSION['acl'];

		if (!isset($acl_list[$page]['actions']) && $redirect) {
			redirect('home');
		} else if (!isset($acl_list[$page]['actions'])) {
			return false;
		}

		foreach ($acl_list[$page]['actions'] as $row)
		{
			$acl_act[$row] = true;
		}

		if (isset($acl_act[$action])) 
			return true;
		elseif ($redirect) {
			//$this->session->set_flashdata("warning_msg", "You don't have permission!");
			redirect('home');
		}
		else {
			return false;
		}
	}
}

if ( ! function_exists('check_acl_btn'))
{
	function check_acl_btn($page, $action, $acl_list='')
	{
		if ($acl_list=='') $acl_list=$_SESSION['acl'];

		if (!isset($acl_list[$page]['actions'])) {
			return 'disabled';
		}
		
		if (in_array($action, $acl_list[$page]['actions']))
			return '';
		else
			return 'disabled';
	}
}

if ( ! function_exists('check_acl_report'))
{
	function check_acl_report($action, $redirect=true, $acl_list='')
	{
		$acl_act = array();

		
		if ($acl_list=='') $acl_list=$_SESSION['acl'];

		if (!isset($acl_list['report']['actions'])) {
			return false;
		}

		foreach ($acl_list['report']['actions'] as $row)
		{
			$acl_act[$row] = true;
		}

		if (isset($acl_act['A'])) {
			//All selected, pass true
			return true;
		}

		if (isset($acl_act[$action])) 
			return true;
		elseif ($redirect) {
			//$this->session->set_flashdata("warning_msg", "You don't have permission!");
			redirect('home');
		}
		else {
			return false;
		}
	}
}

if ( ! function_exists('find_acl_report'))
{
	function find_acl_report($page)
	{
		$report_list = array(
			"customer_listing" => "C",
			"building_listing" => "C",
			"customer_overdue" => "C",
			"customer_transaction_listing" => "C",
			"customer_deposit_listing" => "C",
			"customer_aging" => "C",
			"detailed_customer_aging" => "C",
			"customer_statement_listing" => "C",
			"terminated_acc" => "C",
			"contract_acc" => "C",
			"expiry_contract_report" => "C",
			"expiry_no_renewal_report" => "C",
			"not_yet_customers"=>"C",
			"sales" => "B",
			"payment_summary" => "B",
			"detail_of_payment" => "B",
			"detail_of_billing" => "B",
			"adjustment_summary" => "B",
			"einvoice_report" => "B",
			"paynet_report" => "B",
			"bill_reminder_report" => "B",
			"ifca_table" => "B",
			"dealer_listing" => "G",
			"dealer_commission_by_dealer" => "G",
			"dealer_commission" => "G",
			"ticket_listing" => "T",
			"asset_listing" => "S",
			"planned_maintenance" => "S",
			"asset_service" => "S",
			"asset_transfer" => "S",
			"docs_log" => "O",
			"email_report" => "O",
			"action_log" => "O",
			"customer_action_log" => "O",
			"radius_radcheck" => "R",
			"radius_usergroup" => "R",
			"radius_login" => "R"
		);

		foreach ($report_list as $report_page => $report_acl) {
			if (strpos($report_page, $page) !== false) {
				return check_acl_report($report_acl);
				break;
			}
		}

		return true;
	}
}

/* End of file acl_helper.php */
/* Location: ./application/helpers/acl_helper.php */
