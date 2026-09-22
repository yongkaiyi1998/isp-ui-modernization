<?php
class Xls_model extends MY_Model{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function xls_export($post)
	{
		$this->load->model('report_model');
	   	$result = $this->customer_model->get_deposit_export($post['sel_category'], $post['deposit_date_start'], $post['deposit_date_end']);
		$data 	= $result['row'];

		if(!empty($data))
		{
			$stuff = '';
			$filename ="deposit.xls";
	    	header('Content-type: application/ms-excel');
	    	header('Content-Disposition: attachment; filename='.$filename);

	    	$stuff .= "Deposit Date\tCustomer No\tCustomer Name\tRemark\tPayment Source\tPayment Info\tAmount\t\n";
	    	foreach( $data AS $row )
	    	{
	    		$stuff .= $row['pay_date']."\t".$row['customer_no']."\t".$row['customer_name']."\t".$row['remark']."\t".$row['payment_source_name']."\t".$row['cheque_no']."\t".$row['amount']."\t\n";
	    	}
	   	 	echo $stuff;
	   	}
	   	else
	   	{
	   		$this->session->set_flashdata("warning_msg", 'Deposit record not found! You have failed to export deposit.');
			redirect('customer/export_deposit');
	   	}

	}

	
	 public function exportExcelData( $filename, $records)
	 {

		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		$heading = false;
		if (!empty($records)){
			foreach ($records as $row) {
				if (!$heading) {
					// display field/column names as a first row
					echo implode("\t", array_keys($row)) . "\n";
					$heading = true;
				}
				echo implode("\t", ($row)) . "\n";
			}
		}
	 }
	

	
}

