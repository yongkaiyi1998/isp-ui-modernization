<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Report extends DataPage_Controller {

	private $e_key = "1nf0n4l";

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		//read page title and check the appropriate acl
		$uri_str = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$report_page = explode("/", $uri_str);
		if (isset($report_page[3])) {
			find_acl_report($report_page[3]);
		} else {
			if ( !empty($_SESSION['acl']['report']) ) {
			} else {
				redirect("home");
			}
		}
		//check_acl_report('report');
		$this->load->model('common_model');	
		$this->load->model('report_model');		

		$this->load->model('docs_log_model');
		$this->load->helper('puppeteer_helper');

		$this->account_status = array('P' => 'Signup', 'F' => 'First Activated', 'A' => 'Activated', 'S' => 'Suspended', 'T' => 'Terminated', 'C' => 'Cancelled');

		$this->einvoice_status = array(
			'P' => 'Submitted',
			'S' => 'Valid',
			'F' => 'Invalid',
			'C' => 'Cancelled',
		);

		$this->paynet_status = array(
			'00' => 'Approved/Success',
			'13' => 'Invalid Amount',
			'48' => 'Maximum Transaction Limit Exceeded',
			'49' => 'Merchant Specific Limit Exceeded',
			'51' => 'Insufficient Funds',
			'2A' => 'Transaction Amount Is Lower Than Minimum Limit'
		);

		$this->asset_status = array(
			'1' => 'Active',
			'2' => 'To Scrap',
			'3' => 'Disposed',
		);

		$this->service_record_status = array('P' => 'Pending', 'D' => 'Done');

		$this->alphabet = range('A', 'Z');

    }
    
	function detail_of_billing() 
	{
		$data = array();
		$post_data = $this->input->post();
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_bill_type' => 'all',
			'sel_building' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t'),
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_detail_of_billing_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title']			= 'Details of Billing';
		$data['form_action']		= base_url('report/detail_of_billing');	
		$data['sel_category_list']	= $this->common_model->get_category_list();
		$data['sel_status_list']	= $this->common_model->get_customer_status_list();
		$data['sel_bill_type_list']	= $this->common_model->get_bill_type_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		
		$data['msg']		= $this->msg;
		$row_html = $initial? '' : $this->detail_of_billing_rows(1);
		$data['row_html'] = $row_html;
		$data['isprint'] = 0;

		if (!empty($contact_list)) {
			$json_contact_list = json_decode($contact_list);

			$_POST['scode_str'] = date('YmdHi');
			$_POST['scode'] = md5($_POST['scode_str'].$this->e_key);
			puppeteer_print_preview_forpost($this->config->item('base_url').'pdfapi/detail_of_billing_report', $this->config->item('upload_path').'/temp/pdf/details_of_billing.pdf', json_encode($_POST), $this->config->item('proj_path'), $this->config->item('chrome_loc'));
			
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/details_of_billing.pdf',
				'Details of Billing Report',
				'Attached herewith is the details of billing report sent from itelco system.',
				'[Details of Billing Report]'
			);
		}

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/detail_of_billing', $data);
		$this->load->view('templates/footer');
	}

	function detail_of_billing_rows($returnOnly = 0) 
	{
		$data = array();
		$post_data = $this->input->post();
		$default_data = [
			'is_postback' => 0,
			'sel_building' => 'all',
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_bill_type' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t'),
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_detail_of_billing_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$query_where = " WHERE b.is_void = '0'";
		if ($data['sel_building'] != 'all') {
			$query_where .= " AND c.building = '" . $data['sel_building'] . "'";
		}
		if ($data['sel_category'] != 'all') {
			$query_where .= " AND c.category = '" . $data['sel_category'] . "'";
		}
		if ($data['sel_status'] != 'all') {
			$query_where .= " AND cs.status = '" . $data['sel_status'] . "'";
		}
		if ($data['sel_bill_type'] != 'all') {
			$query_where .= " AND bd.bill_type = '" . $data['sel_bill_type'] . "'";
		}
		if (!empty($data['txt_date_start']) || !empty($data['txt_date_end'])) {
			$txt_date_start = !empty($data['txt_date_start']) ? $data['txt_date_start'] : date('Y-m-01');
			$txt_date_end   = !empty($data['txt_date_end'])   ? $data['txt_date_end']   : date('Y-m-t');
			$query_where .= " AND (b.bill_date BETWEEN '$txt_date_start' AND '$txt_date_end')";
		}

		$details = $this->report_model->detail_of_billing($query_where,$data['order_by'],$data['order_type']);

		$data['date_start']	= $data['txt_date_start'];
		$data['date_end']	= $data['txt_date_end'];
		$data['data_row']	= $initial? [] : $details;
		$data['page_title']	= 'Details of Billing';
		$data['isprint'] = 0;

		$data['building_list'] = array_column($this->common_model->get_building_list() ?? [], 'name', 'building_no');

		$html = $this->parser->parse('report/detail_of_billing_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	function export_detail_of_billing()
	{
		$is_postback    = $this->input->post('is_postback');
		$sel_building   = $this->input->post('sel_building');
		$sel_category   = $this->input->post('sel_category');
		$sel_status     = $this->input->post('sel_status');
		$sel_bill_type  = $this->input->post('sel_bill_type');
		$txt_date_start = $this->input->post('txt_date_start');
		$txt_date_end   = $this->input->post('txt_date_end');
		$order_by       = $this->input->post('order_by');
		$order_type     = $this->input->post('order_type');

		if (empty($txt_date_start)) $txt_date_start = date('Y-m-01');
		if (empty($txt_date_end))   $txt_date_end   = date('Y-m-t');

		$query_where = " WHERE b.is_void = '0'";
		if ($sel_category != 'all') $query_where .= " AND c.category = '$sel_category'";
		if ($sel_status != 'all')   $query_where .= " AND cs.status = '$sel_status'";
		if ($sel_bill_type != 'all')$query_where .= " AND bd.bill_type = '$sel_bill_type'";
		if ($sel_building != 'all') $query_where .= " AND c.building = '$sel_building'";
		$query_where .= " AND (b.bill_date BETWEEN '$txt_date_start' AND '$txt_date_end')";

		$data_row = (empty($is_postback)) ? [] : $this->report_model->detail_of_billing($query_where, $order_by, $order_type);

		/*
		echo "<pre>";
		print_r($data_row);
		echo "</pre>";
		exit;
		*/

		$building_list = array_column($this->common_model->get_building_list() ?? [], 'name', 'building_no');

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();

		$objPHPExcel->getProperties()
			->setCreator('Itelco System')
			->setTitle('Details of Billing');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Billing Details');

		$sheet->setCellValue('A1', 'Details of Billing');
		$sheet->setCellValue('A2', 'Period: ' . $txt_date_start . ' until ' . $txt_date_end);
		$sheet->mergeCells('A1:D1');
		$sheet->mergeCells('A2:D2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$irow = 4;
		$overall_summary = [];

		$grand_last = '';
		$grand_second_last = '';

		if (!empty($data_row['billing'])) {
			foreach ($data_row['billing'] as $building_no => $building_data) {
				$building_name = $building_list[$building_no] ?? 'No Building';
				$sheet->setCellValue('A' . $irow, $building_name);
				$sheet->getStyle('A' . $irow)->getFont()->setBold(true)->setSize(12);
				$irow++;

				$building_total = 0;

				$abc = array();
				$last = '';
				$second_last = '';

				$type_totals = array();
				$grand_totals = array();

				foreach ($building_data as $category => $cat_data) {
					$category_total = 0;
					$category_has_data = false;

					if (!empty($data_row['bill_type'][$building_no])) {
						//foreach ($data_row['bill_type'][$building_no] as $bill_type_id => $bill_type_name) {
							$type_total = 0;
							$type_has_data = false;

							//determine cat data total columns here
							$ty_col = array();
							foreach($cat_data as $ty) {
								foreach ($ty['bill_type'] as $bt_key => $bt) {
									if (!isset($ty_col[$bt])) {
										$ty_col[$bt] = $ty['bt_name'][$bt_key];
									}
								}
							}

							/*
							echo "<pre>";
							print_r($cat_data);
							echo "</pre>";
							exit;
							*/

							foreach ($cat_data as $r) {

								//$index = array_search($bill_type_id, $r['bill_type']);
								//if ($index !== false) {
									if (!$category_has_data) {
										$sheet->setCellValue('A' . $irow, $category);
										$sheet->getStyle('A' . $irow)->getFont()->setItalic(true);
										$irow++;

										$headers = ['Doc No', 'Bill Date', 'Customer No', 'Name'];
										foreach($ty_col as $bt_id => $bt_name) {
											$headers[] = $bt_name;

											//init the array
											$type_totals[$category][$bt_id] = 0;
											$grand_totals[$bt_id] = 0;

										}
										$headers[] = 'Total';
										$col = 'A';
										foreach ($headers as $h) {
											$sheet->setCellValue($col . $irow, $h);
											$abc[] = $col;
											$col++;
										}

										$last = end($abc);
										$temp_abc = $abc;
										end($temp_abc);
										$second_last = prev($temp_abc);

										$sheet->getStyle('A' . $irow . ':'.$last . $irow)->getFont()->setBold(true);
										$sheet->getStyle('A' . $irow . ':'.$last . $irow)->getFill()
											->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
											->getStartColor()->setRGB('D9D9D9');
										$irow++;
										$category_has_data = true;
									}

									if (!$type_has_data) {
										//$sheet->setCellValue('A' . $irow, $bill_type_name);
										//$sheet->getStyle('A' . $irow)->getFont()->setBold(true);
										//$irow++;
										$type_has_data = true;
									}

									$sheet->setCellValue('A' . $irow, $r['bill_no']);

									$bill_dt = new DateTime($r['bill_date'], new DateTimeZone('Asia/Kuala_Lumpur')); 
									$bill_timezone = strtotime($bill_dt->format('Y-m-d H:i:s'));

									$bill_timeStart = $bill_timezone + date('Z', $bill_timezone);

									//$sheet->setCellValue('B' . $irow, PHPExcel_Shared_Date::PHPToExcel($bill_timeStart));
									$sheet->setCellValue('B' . $irow, $r['bill_date']);
									$sheet->getStyle('B' . $irow)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
									$sheet->setCellValue('C' . $irow, $r['customer_no']);
									$sheet->setCellValue('D' . $irow, $r['customer_name']);

									//dynamic columns
									$next_abc = 'E';
									$this_row_total = 0;
									foreach($ty_col as $bt_id => $bt_name) {

										//find the bill_type one by one
										$bt_key = '';
										foreach ($r['bill_type'] as $rbt_key => $rbt_val) {
											if ($rbt_val == $bt_id) {
												//found
												$bt_key = $rbt_key;
												break;
											}
										}

										//if isset the bill type for this customer
										if ($bt_key !== '') {
											//got key
											$sheet->setCellValueExplicit($next_abc . $irow, $r['total_amount'][$bt_key], PHPExcel_Cell_DataType::TYPE_NUMERIC);
											$this_row_total = $this_row_total + $r['total_amount'][$bt_key];

											//tally up
											$type_totals[$category][$rbt_val] += $r['total_amount'][$bt_key];
											$grand_totals[$rbt_val] += $r['total_amount'][$bt_key];

										} else {
											$sheet->setCellValue($next_abc . $irow, '');
										}
										$next_abc++;
									}

									$sheet->setCellValueExplicit($last . $irow, $this_row_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
									$sheet->getStyle($last . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

									if ($irow % 2 == 0) {
										$sheet->getStyle('A' . $irow . ':'.$last . $irow)->getFill()
											->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
											->getStartColor()->setRGB('F9F9F9');
									}

									$type_total += (float)$this_row_total;
									$irow++;
								//}
							}

							if ($type_total > 0 || $type_has_data) {
								/*$sheet->setCellValue($second_last . $irow, 'Total ' . $bill_type_name . ':');
								$sheet->setCellValueExplicit($last . $irow, $type_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
								$sheet->getStyle($second_last . $irow . ':'.$last . $irow)->getFont()->setBold(true);
								$sheet->getStyle($last . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
								$irow += 2;*/
								$category_total = $type_total;
							}
						//}
					}

					if ($category_has_data) {
						$sheet->setCellValue('D' . $irow, 'Subtotal ' . $category . ' in ' . $building_name . ':');
						$sheet->setCellValueExplicit($last . $irow, $category_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$sheet->getStyle('D' . $irow . ':'.$last . $irow)->getFont()->setBold(true);
						$sheet->getStyle($last . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

						//subtotals
						$next_abc = 'E';
						foreach($ty_col as $bt_id => $bt_name) {
							if (isset($type_totals[$category][$bt_id])) {
								$sheet->setCellValueExplicit($next_abc . $irow, $type_totals[$category][$bt_id], PHPExcel_Cell_DataType::TYPE_NUMERIC);
								$sheet->getStyle($next_abc . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
							}
							$next_abc++;
						}

						$building_total += $category_total;
						$overall_summary[$category] = ($overall_summary[$category] ?? 0) + $category_total;
						$irow += 1;
					}
				}

				$sheet->setCellValue('D' . $irow, 'Grand Total (' . $building_name . '):');
				$sheet->setCellValueExplicit($last . $irow, $building_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('D' . $irow . ':'.$last . $irow)->getFont()->setBold(true)->getColor()->setRGB('006400');
				$sheet->getStyle($last . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

				//grand totals
				$next_abc = 'E';
				foreach($ty_col as $bt_id => $bt_name) {
					if (isset($grand_totals[$bt_id])) {
						$sheet->setCellValueExplicit($next_abc . $irow, $grand_totals[$bt_id], PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$sheet->getStyle($next_abc . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
					}
					$next_abc++;
				}

				$irow += 2;

				//check alphabet max
				$kkey = array_search($last, $this->alphabet);
				if (empty($grand_last)) {
					$grand_last = $kkey;
					$grand_second_last = $kkey-1;
				}
				if ($kkey > $grand_last) {
					$grand_last = $kkey;
					$grand_second_last = $kkey-1;
				}
			}
		} else {
			$sheet->setCellValue('A' . $irow, 'No billing data found for this period.');
		}

		foreach (range('A', $last) as $col) {
			$sheet->getColumnDimension($col)->setWidth(20);
		}

		//for right side, add 3 to the last abc
		$sum_last = $this->alphabet[$grand_last+2]; //H
		$sum_second_last = $this->alphabet[$grand_last+1]; //G

		/* ======== BEAUTIFUL OVERALL SUMMARY (RIGHT SIDE) ======== */
		if (!empty($overall_summary)) {
			$summary_col = $sum_second_last;
			$sheet->setCellValue($summary_col . '3', 'Summary (All Building)');
			$sheet->mergeCells($sum_second_last.'3:'.$sum_last.'3');
			$sheet->getStyle($sum_second_last.'3')->getFont()->setBold(true)->setSize(12);

			$sheet->setCellValue($sum_second_last.'4', 'Category');
			$sheet->setCellValue($sum_last.'4', 'Total Amount');
			$sheet->getStyle($sum_second_last.'4:'.$sum_last.'4')->getFont()->setBold(true);
			$sheet->getStyle($sum_second_last.'4:'.$sum_last.'4')->getFill()
				->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()->setRGB('D9D9D9');

			$sheet->getColumnDimension($sum_second_last)->setWidth(25);
			$sheet->getColumnDimension($sum_last)->setWidth(18);

			$srow = 5;
			$grand_total_all = 0;
			foreach ($overall_summary as $cat => $total) {
				$sheet->setCellValue($sum_second_last . $srow, $cat);
				$sheet->setCellValueExplicit($sum_last . $srow, $total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle($sum_last . $srow)->getNumberFormat()->setFormatCode('#,##0.00');

				if ($srow % 2 == 0) {
					$sheet->getStyle($sum_second_last . $srow . ':'.$sum_last . $srow)->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setRGB('F9F9F9');
				}

				$grand_total_all += $total;
				$srow++;
			}

			$sheet->setCellValue($sum_second_last . $srow, 'Grand Total (All Building):');
			$sheet->setCellValueExplicit($sum_last . $srow, $grand_total_all, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle($sum_second_last . $srow . ':'.$sum_last . $srow)->getFont()->setBold(true)->getColor()->setRGB('006400');
			$sheet->getStyle($sum_last . $srow)->getNumberFormat()->setFormatCode('#,##0.00');
		}

		while (ob_get_level()) ob_end_clean();

		$filename = 'detail_of_billing_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

	function xxxexport_detail_of_billing()
	{
		$is_postback    = $this->input->post('is_postback');
		$sel_building   = $this->input->post('sel_building');
		$sel_category   = $this->input->post('sel_category');
		$sel_status     = $this->input->post('sel_status');
		$sel_bill_type  = $this->input->post('sel_bill_type');
		$txt_date_start = $this->input->post('txt_date_start');
		$txt_date_end   = $this->input->post('txt_date_end');
		$order_by       = $this->input->post('order_by');
		$order_type     = $this->input->post('order_type');

		if (empty($txt_date_start)) $txt_date_start = date('Y-m-01');
		if (empty($txt_date_end))   $txt_date_end   = date('Y-m-t');

		$query_where = '';
		if ($sel_category != 'all') $query_where .= " WHERE c.category = '$sel_category'";
		if ($sel_status != 'all')   $query_where .= " AND c.status = '$sel_status'";
		if ($sel_bill_type != 'all')$query_where .= " AND bd.bill_type = '$sel_bill_type'";
		if ($sel_building != 'all') $query_where .= " AND c.building = '$sel_building'";
		$query_where .= " AND (b.bill_date BETWEEN '$txt_date_start' AND '$txt_date_end')";

		$data_row = (empty($is_postback)) ? [] : $this->report_model->detail_of_billing($query_where, $order_by, $order_type);
		$building_list = array_column($this->common_model->get_building_list() ?? [], 'name', 'building_no');

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();

		$objPHPExcel->getProperties()
			->setCreator('Itelco System')
			->setLastModifiedBy('Itelco System')
			->setTitle('Details of Billing')
			->setSubject('Details of Billing')
			->setDescription('Details of Billing Report by Building')
			->setCategory('Billing Export');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Billing Details');

		$sheet->setCellValue('A1', 'DETAILS OF BILLING');
		$sheet->setCellValue('A2', 'Period: ' . $txt_date_start . ' until ' . $txt_date_end);
		$sheet->mergeCells('A1:H1');
		$sheet->mergeCells('A2:H2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$irow = 4;

		foreach ($data_row['billing'] as $building_no => $building_data) {
			$building_name = $building_list[$building_no] ?? 'No Building';
			$sheet->setCellValue('A' . $irow, 'Building: ' . $building_name . ' (' . $building_no . ')');
			$sheet->getStyle('A' . $irow)->getFont()->setBold(true)->getColor()->setRGB('289383');
			$irow += 2;

			$sheet->setCellValue('A' . $irow, 'Doc No')
				->setCellValue('B' . $irow, 'Bill Date')
				->setCellValue('C' . $irow, 'Customer No')
				->setCellValue('D' . $irow, 'Customer Name');

			$col = 'E';
			foreach ($data_row['bill_type'][$building_no] as $key => $val) {
				$sheet->setCellValue($col . $irow, $val);
				$col++;
			}
			$sheet->getStyle('A' . $irow . ':' . chr(ord($col) - 1) . $irow)->getFont()->setBold(true);
			$irow++;

			foreach ($building_data as $key_cat => $val_cat) {
				$sheet->setCellValue('A' . $irow, $key_cat);
				$sheet->getStyle('A' . $irow)->getFont()->setBold(true)->setItalic(true);
				$irow++;

				foreach ($val_cat as $val_cust) {
					$sheet->setCellValue('A' . $irow, $val_cust['bill_no']);
					$sheet->setCellValue('B' . $irow, $val_cust['bill_date']);
					$sheet->setCellValue('C' . $irow, $val_cust['customer_no']);
					$sheet->setCellValue('D' . $irow, $val_cust['customer_name']);

					$col = 'E';
					foreach ($data_row['bill_type'][$building_no] as $key => $val) {
						$value = ($val_cust['bill_type'] == $key) ? $val_cust['total_amount'] : '';
						$sheet->setCellValue($col . $irow, $value);
						$col++;
					}
					$irow++;
				}

				$sheet->setCellValue('A' . $irow, 'Total (' . $key_cat . ')');
				$sheet->getStyle('A' . $irow)->getFont()->setBold(true);
				$col = 'E';
				foreach ($data_row['bill_type'][$building_no] as $key => $val) {
					$subtotal = $data_row['subtotal'][$building_no][$key_cat][$key] ?? 0;
					$sheet->setCellValue($col . $irow, $subtotal);
					$col++;
				}
				$irow += 2;
			}

			$sheet->setCellValue('A' . $irow, 'Grand Total (' . $building_name . ')');
			$sheet->getStyle('A' . $irow)->getFont()->setBold(true);
			$col = 'E';
			foreach ($data_row['bill_type'][$building_no] as $key => $val) {
				$grand = $data_row['grandtotal'][$building_no][$key] ?? 0;
				$sheet->setCellValue($col . $irow, $grand);
				$col++;
			}
			$irow += 3;
		}

		foreach (range('A', 'Z') as $columnID) {
			$sheet->getColumnDimension($columnID)->setAutoSize(true);
		}

		while (ob_get_level()) { ob_end_clean(); }

		$filename = 'detail_of_billing_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

    function adjustment_summary()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_bill_type' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t')
		];
		$return = get_filtered_ajax_data('report_adjustment_summary_filter', $default_data, $post_data);
		$data = $return['data'];

		$contact_list	= $this->input->post('contact_list');
			
		$data['page_title']			= 'Adjustment Summary';
		$data['form_action']		= base_url('report/adjustment_summary');		
		$data['sel_bill_type_list']	= $this->common_model->get_bill_type_list();

		$row_html = $this->adjustment_summary_rows(1);
		$data['row_html'] = $row_html;		
		$data['msg'] 		= $this->msg;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'adjustment_summary';
			$header_data['description']	= 'Adjustment Summary Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/adjustment_summary',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/adjustment_summary.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/adjustment_summary.pdf',
				'Adjustment Summary Report',
				'Attached herewith is the adjustment summary report sent from itelco system.',
				'[Adjustment Summary Report]'
			);
		}

		$data['isprint'] = 0;
				
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/adjustment_summary',$data);
		$this->load->view('templates/footer');
	}

	function adjustment_summary_rows($returnOnly = 0) 
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_bill_type' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t')
		];
		$return = get_filtered_ajax_data('report_adjustment_summary_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$adjustment = array();
		$sess_bill_type = $_SESSION['bill_type'];
			
		$query_where = "WHERE ba.tranx_date >= '" . $this->db->escape_str($data['txt_date_start']) . "' " . 
						"AND ba.tranx_date <= '" . $this->db->escape_str($data['txt_date_end']) . "' ";
		
		$result = $this->report_model->get_bill_adjustment_left_join_customer($query_where);
		
		foreach ($result['result_array'] as $row)
		{
			$bill_type	= $row['bill_type'];
			$category	= $row['category'];
			$adjustment[$bill_type]['bill_type_name'] 	= $sess_bill_type[$bill_type];
			$adjustment[$bill_type][$category] 			= $row['amount'];
		}

		$data['data_row'] = $initial? [] : $adjustment;
		$data['page_title'] = 'Adjustment Summary';
		$data['date_start']	= $data['txt_date_start'];
		$data['date_end']	= $data['txt_date_end'];
		$data['isprint'] = 0;

		$html = $this->parser->parse('report/adjustment_summary_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

    function export_adjustment_summary()
	{
		$is_postback 	= $this->input->post('is_postback');		
		$sel_bill_type 	= $this->input->post('sel_bill_type');
		$txt_date_start	= $this->input->post('txt_date_start');
		$txt_date_end 	= $this->input->post('txt_date_end');

		if (empty($txt_date_start)) $txt_date_start = date('Y-m-01');
		if (empty($txt_date_end))   $txt_date_end   = date('Y-m-t');

		$adjustment = [];

		if (!empty($is_postback)) {
			$sess_bill_type = $_SESSION['bill_type'] ?? [];

			$query_where = "WHERE ba.tranx_date >= '" . $this->db->escape_str($txt_date_start) . "' " .
						"AND ba.tranx_date <= '" . $this->db->escape_str($txt_date_end) . "' ";

			$result = $this->report_model->get_bill_adjustment_left_join_customer($query_where);

			foreach ($result['result_array'] as $row) {
				$bill_type	= $row['bill_type'];
				$category	= $row['category'];
				$adjustment[$bill_type]['bill_type_name'] = $sess_bill_type[$bill_type] ?? 'Unknown';
				$adjustment[$bill_type][$category] = $row['amount'];
			}
		}

		$data_row = $adjustment;

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Adjustment Summary');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Adjustment Summary');

		$sheet->setCellValue('A1', 'Adjustment Summary');
		$sheet->setCellValue('A2', 'Period: ' . $txt_date_start . ' until ' . $txt_date_end);
		$sheet->mergeCells('A1:E1');
		$sheet->mergeCells('A2:E2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$irow = 4;
		$sheet->setCellValue('A' . $irow, 'Bill Type');
		$sheet->setCellValue('B' . $irow, 'Business');
		$sheet->setCellValue('C' . $irow, 'Residential');
		$sheet->setCellValue('D' . $irow, 'Wholesale');
		$sheet->setCellValue('E' . $irow, 'Total');
		$sheet->getStyle('A' . $irow . ':E' . $irow)->getFont()->setBold(true);
		$irow++;

		$grand_total = 0;
		$col_total_b = 0;
		$col_total_r = 0;
		$col_total_w = 0;

		foreach ($data_row as $val) {
			$val_b = $val['b'] ?? 0;
			$val_r = $val['r'] ?? 0;
			$val_w = $val['w'] ?? 0;
			$line_total = $val_b + $val_r + $val_w;

			$sheet->setCellValue('A' . $irow, $val['bill_type_name']);
			$sheet->setCellValueExplicit('B' . $irow, $val_b, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicit('C' . $irow, $val_r, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicit('D' . $irow, $val_w, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicit('E' . $irow, $line_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);

			$sheet->getStyle('B' . $irow . ':E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

			$col_total_b += $val_b;
			$col_total_r += $val_r;
			$col_total_w += $val_w;
			$grand_total += $line_total;

			$irow++;
		}

		$sheet->setCellValue('A' . $irow, 'Grand Total');
		$sheet->setCellValueExplicit('B' . $irow, $col_total_b, PHPExcel_Cell_DataType::TYPE_NUMERIC);
		$sheet->setCellValueExplicit('C' . $irow, $col_total_r, PHPExcel_Cell_DataType::TYPE_NUMERIC);
		$sheet->setCellValueExplicit('D' . $irow, $col_total_w, PHPExcel_Cell_DataType::TYPE_NUMERIC);
		$sheet->setCellValueExplicit('E' . $irow, $grand_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
		$sheet->getStyle('A' . $irow . ':E' . $irow)->getFont()->setBold(true);
		$sheet->getStyle('B' . $irow . ':E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

		foreach (range('A', 'E') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'adjustment_summary_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

    function detail_of_payment()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_payment_source' => 'all',
			'sel_dealer' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t')
		];
		$return = get_filtered_ajax_data('report_detail_of_payment', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title']					= 'Details of Payment';
		$data['form_action']				= base_url('report/detail_of_payment');
		$data['sel_category_list']			= $this->common_model->get_category_list();
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
		$data['sel_payment_source_list']	= $this->common_model->get_payment_source_list();
		$data['sel_dealer_list'] 			= $this->common_model->get_dealer_list();

		$data['msg']		= $this->msg;
		$row_html = $this->detail_of_payment_rows(1);
		$data['row_html'] = $row_html; 

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'details_of_payment';
			$header_data['description']	= 'Details of Payment Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/detail_of_payment',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/details_of_payment.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/details_of_payment.pdf',
				'Details of Payment Report',
				'Attached herewith is the details of payment report sent from itelco system.',
				'[Details of Payment Report]'
			);

			unset($_POST['btFilter']);
		}

		$data['isprint'] = 0;
		
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/detail_of_payment',$data);
		$this->load->view('templates/footer');
	}
	
	function detail_of_payment_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_payment_source' => 'all',
			'sel_dealer' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t')
		];
		$return = get_filtered_ajax_data('report_detail_of_payment', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$this->load->model('payment_model');
		$payment = $this->payment_model->detail_of_payment($data['sel_category'], $data['sel_status'], $data['sel_payment_source'],$data['sel_dealer'], $data['txt_date_start'], $data['txt_date_end']);
		$this->load->model('customer_model');
		$deposit = $this->customer_model->payment_of_deposit($data['sel_category'], $data['sel_status'], $data['sel_payment_source'],$data['sel_dealer'], $data['txt_date_start'], $data['txt_date_end']);
		$payment = array_merge_recursive( $payment , $deposit );

		$data['data_row'] = $initial? [] : $payment;
		$data['page_title']	= 'Details of Payment';
		$data['isprint'] = $this->input->post('isprint');
		$data['date_start']	= $data['txt_date_start'];
		$data['date_end']	= $data['txt_date_end'];

		$html = $this->parser->parse('report/detail_of_payment_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

    function export_detail_of_payment()
	{
		$is_postback        = $this->input->post('is_postback');       
		$sel_category        = $this->input->post('sel_category');
		$sel_status         = $this->input->post('sel_status');
		$sel_payment_source = $this->input->post('sel_payment_source');
		$sel_dealer         = $this->input->post('sel_dealer');
		$txt_date_start     = $this->input->post('txt_date_start');
		$txt_date_end       = $this->input->post('txt_date_end');

		if (empty($txt_date_start)) $txt_date_start = date('Y-m-01');
		if (empty($txt_date_end))   $txt_date_end   = date('Y-m-t');

		$payment = [];
		if (!empty($is_postback)) {
			$this->load->model('payment_model');
			$payment = $this->payment_model->detail_of_payment($sel_category, $sel_status, $sel_payment_source, $sel_dealer, $txt_date_start, $txt_date_end);
			$this->load->model('customer_model');
			$deposit = $this->customer_model->payment_of_deposit($sel_category, $sel_status, $sel_payment_source, $sel_dealer, $txt_date_start, $txt_date_end);
			$payment = array_merge_recursive($payment, $deposit);
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();

		$objPHPExcel->getProperties()
			->setCreator('Itelco System')
			->setTitle('Details of Payment');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Payment Details');

		$sheet->setCellValue('A1', 'Details of Payment');
		$sheet->setCellValue('A2', 'Period: ' . $txt_date_start . ' until ' . $txt_date_end);
		$sheet->mergeCells('A1:H1');
		$sheet->mergeCells('A2:H2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$irow = 4;
		$grand_total = 0;

		if (!empty($payment)) {
			foreach ($payment as $category => $category_data) {
				$sheet->setCellValue('A' . $irow, $category);
				$sheet->getStyle('A' . $irow)->getFont()->setBold(true)->setSize(12);
				$irow++;

				$category_total = 0;

				foreach ($category_data as $type => $type_data) {
					$sheet->setCellValue('A' . $irow, $type);
					$sheet->getStyle('A' . $irow)->getFont()->setItalic(true);
					$irow++;

					$sheet->setCellValue('A' . $irow, 'Pay Date');
					$sheet->setCellValue('B' . $irow, 'Customer No');
					$sheet->setCellValue('C' . $irow, 'Name');
					$sheet->setCellValue('D' . $irow, 'Building');
					$sheet->setCellValue('E' . $irow, 'Remark');
					$sheet->setCellValue('F' . $irow, 'Receipt');
					$sheet->setCellValue('G' . $irow, 'Source');
					$sheet->setCellValue('H' . $irow, 'Cheque');
					$sheet->setCellValue('I' . $irow, 'Amount');
					$sheet->getStyle('A' . $irow . ':I' . $irow)->getFont()->setBold(true);
					$irow++;

					$type_total = 0;
					foreach ($type_data as $r) {
						$sheet->setCellValue('A' . $irow, $r['pay_date']);
						$sheet->setCellValue('B' . $irow, $r['customer_no']);
						$sheet->setCellValue('C' . $irow, $r['customer_name']);
						$sheet->setCellValue('D' . $irow, $r['building_name']);
						$sheet->setCellValue('E' . $irow, $r['remark']);
						$sheet->setCellValue('F' . $irow, $r['payment_no']);
						$sheet->setCellValue('G' . $irow, $r['payment_source_name']);
						$sheet->setCellValue('H' . $irow, $r['cheque_no']);
						$sheet->setCellValueExplicit('I' . $irow, $r['amount'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$sheet->getStyle('I' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

						$type_total += $r['amount'];
						$irow++;
					}

					$sheet->setCellValue('H' . $irow, 'Total (' . $type . '):');
					$sheet->setCellValueExplicit('I' . $irow, $type_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$sheet->getStyle('H' . $irow . ':I' . $irow)->getFont()->setBold(true);
					$sheet->getStyle('I' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
					$irow += 2;

					$category_total += $type_total;
				}

				$sheet->setCellValue('H' . $irow, 'Total (' . $category . '):');
				$sheet->setCellValueExplicit('I' . $irow, $category_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('H' . $irow . ':I' . $irow)->getFont()->setBold(true);
				$sheet->getStyle('I' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$irow += 3;

				$grand_total += $category_total;
			}

			$sheet->setCellValue('H' . $irow, 'Grand Total:');
			$sheet->setCellValueExplicit('I' . $irow, $grand_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle('H' . $irow . ':I' . $irow)->getFont()->setBold(true)->getColor()->setRGB('006400');
			$sheet->getStyle('I' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
		} else {
			$sheet->setCellValue('A' . $irow, 'No payment data found for this period.');
		}

		foreach (range('A', 'I') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();

		$filename = 'detail_of_payment_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

    function customer_deposit_listing()
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['sel_category'] 		= $post_data['sel_category'];
			$data['sel_status'] 		= $post_data['sel_status'];
			$data['order_by']			= $post_data['order_by'];
			$data['order_type']			= $post_data['order_type'];
		} else {
			$report_customer_deposit_listing_filter = get_session_filter('report_customer_deposit_listing_filter');
			$data['sel_category'] = $report_customer_deposit_listing_filter['sel_category'] ?? 'all';
			$data['sel_status'] = $report_customer_deposit_listing_filter['sel_status'] ?? 'all';
			$data['order_by'] = $report_customer_deposit_listing_filter['order_by'] ?? '';
			$data['order_type'] = $report_customer_deposit_listing_filter['order_type'] ?? '';
		}
		
		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title']					= 'Deposit Summary';
		$data['form_action']				= base_url('report/customer_deposit_listing');
		$data['sel_category_list']			= $this->common_model->get_category_list();
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
				
		$row_html = $this->customer_deposit_listing_rows(1);
		$data['row_html']	= $row_html;
		$data['msg']		= $this->msg;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'customer_deposit_listing';
			$header_data['description']	= 'Customer Deposit Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/customer_deposit_listing',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/customer_deposit_listing.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/customer_deposit_listing.pdf',
				'Customer Deposit Report',
				'Attached herewith is the customer deposit report sent from itelco system.',
				'[Customer Deposit Report]'
			);

		}	

		$data['isprint'] = 0;
		
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/customer_deposit_listing',$data);
		$this->load->view('templates/footer');
	}

	function customer_deposit_listing_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = array();
		$initial = 0;

		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_customer_deposit_listing_filter = get_session_filter('report_customer_deposit_listing_filter');
			if(empty($report_customer_deposit_listing_filter)) $initial = 1;

			$post_data['is_postback'] = $report_customer_deposit_listing_filter['is_postback'] ?? 0;
			$post_data['sel_category'] = $report_customer_deposit_listing_filter['sel_category'] ?? 'all';
			$post_data['sel_status'] = $report_customer_deposit_listing_filter['sel_status'] ?? 'all';
			$post_data['order_by'] = $report_customer_deposit_listing_filter['order_by'] ?? '';
			$post_data['order_type'] = $report_customer_deposit_listing_filter['order_type'] ?? '';
		}

		$data['is_postback']		= $post_data['is_postback'];
		$data['sel_category'] 		= $post_data['sel_category'];
		$data['sel_status'] 		= $post_data['sel_status'];
		$data['order_by']			= $post_data['order_by'];
		$data['order_type']			= $post_data['order_type'];

		$session_array = array(
			'is_postback' => $data['is_postback'],
			'sel_category' => $data['sel_category'],
			'sel_status' => $data['sel_status'],
			'order_by' => $data['order_by'],
			'order_type' => $data['order_type'],
		);
		if(!$initial) set_session_filter('report_customer_deposit_listing_filter', $session_array);
		
		$result = $this->report_model->deposit_summary($data['sel_category'], $data['sel_status'], $data['order_by'],$data['order_type']);

		$data_row = $result ?? array();
		
		$data['page_title'] = 'Deposit Summary';
		$data['data_row']	= $initial? [] : $data_row;
		$data['isprint'] = $this->input->post('isprint');

		$html = $this->parser->parse('report/customer_deposit_listing_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}
	
    function export_customer_deposit_listing()
	{
		$is_postback			= $this->input->post('is_postback');
		$sel_category			= $this->input->post('sel_category');
		$sel_status				= $this->input->post('sel_status');
		$order_by 				= $this->input->post('order_by') != '' ? $this->input->post('order_by') : 'customer' ;
		$order_type				= $this->input->post('order_type');
		
		
		$deposit = array();
		if ( !empty($is_postback) ) {
			$this->load->model('customer_model');
			$deposit = $this->report_model->deposit_summary($sel_category, $sel_status, $order_by, $order_type);
		}
		
		$records = "";			
		$records .= "Customer No\tName\tBuilding\tRemark\tType\tAmount\t\n" ;

		$grand_total = 0;
		
		foreach ($deposit as $key => $val) {
			$records .= $key . "\t\n";
			
				$total_category = 0;
				foreach ($val as $val3) {
					$records .= $val3['customer_no'] . "\t";
					$records .= $val3['customer_name'] . "\t";
					$records .= $val3['building_name'] . "\t";
					$records .= $val3['remark'] . "\t";
					$records .= $val3['bill_type'] . "\t";
					$records .= $val3['amount'] . "\t\n";
					$total_category += $val3['amount'];
				}

			$records .=  "\t\t\t\tTotal (" . $key . ")\t" . number_format($total_category, 2, '.', '') . "\t\n";
			$grand_total += $total_category;
		}
		
		$records .=  "\t\t\t\tGrand Total\t" . number_format($grand_total, 2, '.', '') . "\t\n";
		
		$filename = "customer_deposit_listing.xls";
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);
		
		echo $records;



	}
	
	function dealer_listing()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_dealer' => '',
			'sel_building' => 'all',
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_payment_source' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t'),
		];
		$return = get_filtered_ajax_data('report_dealer_listing_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title']					= 'Agent Collection';
		$data['form_action']				= base_url('report/dealer_listing');
		$data['sel_building_list']			= $this->common_model->get_building_list();
		$data['sel_category_list']			= $this->common_model->get_category_list();
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
		$data['sel_payment_source_list']	= $this->common_model->get_payment_source_list();

		$data['msg'] = $this->msg;
		$row_html = $this->dealer_listing_rows(1);
		$data['row_html'] = $row_html;	


		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'dealer_listing';
			$header_data['description']	= 'Agent Collection Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/dealer_listing',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/dealer_listing.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/dealer_listing.pdf',
				'Agent Collection Report',
				'Attached herewith is the agent collection report sent from itelco system.',
				'[Agent Collection Report]'
			);

		}

		$data['isprint'] = 0;

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/dealer_listing',$data);
		$this->load->view('templates/footer');
	}

	function dealer_listing_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_dealer' => '',
			'sel_building' => 'all',
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_payment_source' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t'),
		];
		$return = get_filtered_ajax_data('report_dealer_listing_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$dealer = array();
		
		$this->load->model('dealer_model');
		$dealer = $this->dealer_model->dealer_listing($data['txt_dealer'], $data['sel_building'], $data['sel_category'], $data['sel_status'], $data['sel_payment_source'], $data['txt_date_start'], $data['txt_date_end']);

		$data['data_row'] = $initial? [] : $dealer;				
		$data['date_start']	= $data['txt_date_start'];
		$data['date_end'] = $data['txt_date_end'];
		$data['page_title']	= 'Agent Collection';
		$data['isprint'] = 0;

		$html = $this->parser->parse('report/dealer_listing_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_dealer_listing()
	{
		$txt_dealer        = $this->input->post('txt_dealer');            
		$sel_building      = $this->input->post('sel_building');
		$sel_category      = $this->input->post('sel_category');
		$sel_status        = $this->input->post('sel_status');
		$sel_payment_source= $this->input->post('sel_payment_source');
		$txt_date_start    = $this->input->post('txt_date_start');
		$txt_date_end      = $this->input->post('txt_date_end');

		$this->load->model('dealer_model');
		$dealer = $this->dealer_model->dealer_listing(
			$txt_dealer, 
			$sel_building, 
			$sel_category, 
			$sel_status, 
			$sel_payment_source, 
			$txt_date_start, 
			$txt_date_end
		);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Dealer Collection');

		$headers = [
			'Pay Date','Customer No','Name','Agent','Building','Remark',
			'Payment No.','Source','Bank (Paynet)','Ref.','Amount'
		];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		$grand_total = 0;

		foreach ($dealer as $dealer_name => $categories) {
			$dealer_start_row = $row;
			$dealer_total = 0;

			foreach ($categories as $category_name => $payments) {
				$category_total = 0;
				foreach ($payments as $p) {
					$sheet->setCellValue('A'.$row, $p['pay_date']);
					$sheet->setCellValue('B'.$row, $p['customer_no']);
					$sheet->setCellValue('C'.$row, $p['customer_name']);
					$sheet->setCellValue('D'.$row, $p['dealer_name']);
					$sheet->setCellValue('E'.$row, $p['building_name']);
					$sheet->setCellValue('F'.$row, $p['remark']);
					$sheet->setCellValue('G'.$row, $p['payment_no']);
					$sheet->setCellValue('H'.$row, $p['payment_source_name']);
					$sheet->setCellValue('I'.$row, $p['paynet_bank']);
					$sheet->setCellValue('J'.$row, $p['cheque_no']);
					$sheet->setCellValue('K'.$row, $p['amount']);
					$category_total += $p['amount'];
					$row++;
				}

				$sheet->setCellValue('J'.$row, "Total ($category_name)");
				$sheet->setCellValue('K'.$row, $category_total);
				$sheet->getStyle('J'.$row.':K'.$row)->getFont()->setBold(true);
				$row++;
				$dealer_total += $category_total;
			}

			$sheet->setCellValue('J'.$row, "Total ($dealer_name)");
			$sheet->setCellValue('K'.$row, $dealer_total);
			$sheet->getStyle('J'.$row.':K'.$row)->getFont()->setBold(true);
			$row++;
			$grand_total += $dealer_total;
		}

		$sheet->setCellValue('J'.$row, "Grand Total");
		$sheet->setCellValue('K'.$row, $grand_total);
		$sheet->getStyle('J'.$row.':K'.$row)->getFont()->setBold(true);

		foreach (range('A','K') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'dealer_collection_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function dealer_commission()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_dealer' => '',
			'sel_building' => '',
			'sel_category' => '',
			'sel_status' => '',
			'sel_date' => date('Ym'),
		];
		$return = get_filtered_ajax_data('report_dealer_commission_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title']					= 'Agent Commission';
		$data['form_action']				= base_url('report/dealer_commission');
		$data['sel_building_list']			= $this->common_model->get_building_list();
		$data['sel_category_list']			= $this->common_model->get_category_list();
		$data['sel_status_list']			= $this->common_model->get_acc_status_list();
		$data['sel_payment_source_list']	= $this->common_model->get_payment_source_list();
		$data['sel_dealer_list'] 			= array_map(function ($dealer) {
													return [
														'dealer_no' => $dealer['dealer_no'],
														'name'      => $dealer['name'],
														'upline'    => $dealer['upline'] ?? ''
													];
												}, 
												format_dealer_list_hierarchy($this->common_model->get_dealer_list() ?? []));

		$this->load->model('dealer_model');
		$data['sel_date_list'] = $this->dealer_model->get_date_range();

		$recalc_acl = check_acl_report('G2', false);
		if ($recalc_acl) {
			$data['recalc_acl'] = '1';
		} else {
			$data['recalc_acl'] = '0';
		}

		$data['msg']		= $this->msg;
		$row_html = $this->dealer_commission_rows(1);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'dealer_commission';
			$header_data['description']	= 'Agent Commission Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/dealer_commission',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/dealer_commission.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/dealer_commission.pdf',
				'Agent Commission Report',
				'Attached herewith is the agent commission report sent from itelco system.',
				'[Agent Commission Report]'
			);
		}

		$data['isprint'] = 0;
		
		$this->vars['cssfiles'][] 	= '../css/theme/bootstrap-select.min.css';
		$this->vars['jsfiles'][] 	= 'js/bootstrap-select.min.js';
		$this->vars['jsfiles'][] = 'js/bootbox.min.js';

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/dealer_commission',$data);
		$this->load->view('templates/footer');

	}

	function dealer_commission_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_dealer' => '',
			'sel_building' => '',
			'sel_category' => '',
			'sel_status' => '',
			'sel_date' => date('Ym'),
		];
		$return = get_filtered_ajax_data('report_dealer_commission_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$dealer = array();

		$this->load->model('dealer_model');
		$dealer = $this->dealer_model->dealer_comm_listing($data['txt_dealer'] ?? '', $data['sel_building'], $data['sel_category'], $data['sel_status'], $data['sel_date']);

		$data['data_row'] = $initial? [] : $dealer;
		$data['page_title']	= 'Agent Commission';
		$data['sel_date_text'] = $data['sel_date'] == 'all' ? 'All Time' : date("F Y", strtotime(substr($data['sel_date'], 0, 4)."-".substr($data['sel_date'], 4, 2)."-01"));
		$data['isprint'] = 0;

		$html = $this->parser->parse('report/dealer_commission_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_dealer_commission()
	{
		$is_postback = $this->input->post('is_postback');
		$txt_dealer = $this->input->post('txt_dealer');
		$sel_building = $this->input->post('sel_building');
		$sel_category = $this->input->post('sel_category');
		$sel_status = $this->input->post('sel_status');
		$sel_date = $this->input->post('sel_date');

		$this->load->model('dealer_model');
		$dealer = $this->dealer_model->dealer_comm_listing($txt_dealer, $sel_building, $sel_category, $sel_status, $sel_date);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Dealer Commission');

		$headers = ['Pay Date','Customer No','Customer','Activated','Agent','Top Level Agent','Building','Package','Bill No.','Commission Amount','Remark'];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach (($dealer['rows'] ?? []) as $r) {
			$sheet->setCellValue('A'.$row, $r['comm_date']);
			$sheet->setCellValue('B'.$row, $r['customer_no']);
			$sheet->setCellValue('C'.$row, $r['customer_name']);
			$sheet->setCellValue('D'.$row, !empty($r['activated_date']) ? date("Y-m-d", strtotime($r['activated_date'])) : '');
			$sheet->setCellValue('E'.$row, $r['dealer_name']);
			$sheet->setCellValue('F'.$row, ($r['top_level_agent_name'] == "-" ? $r['dealer_name'] : $r['top_level_agent_name'] ));
			$sheet->setCellValue('G'.$row, $r['building_name']);
			$sheet->setCellValue('H'.$row, $r['package_name']);
			//$sheet->setCellValue('I'.$row, $r['bill_no']);
			$sheet->setCellValueExplicit('I'.$row, $r['bill_no'], PHPExcel_Cell_DataType::TYPE_STRING);
			//$sheet->setCellValue('J'.$row, $r['bill_amount']);
			$sheet->setCellValue('J'.$row, $r['amount']);
			$sheet->setCellValue('K'.$row, $r['comm_desc']);
			$row++;
		}

		$row++;

		foreach ($dealer['subtotal'] as $item) {
			$sheet->setCellValue("A{$row}", "Subtotal Commission of " .$item['name']);
			$sheet->mergeCells("A{$row}:I{$row}");
			$sheet->getStyle("A{$row}:I{$row}")
				->getAlignment()
				->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$sheet->setCellValue("J{$row}", $item['subtotal']);
			$sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
			$row++;
		}

		$sheet->mergeCells("A{$row}:I{$row}");
		$sheet->getStyle("A{$row}:I{$row}")
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->setCellValue("A{$row}", "COMMISSION GRAND TOTAL");
		$sheet->setCellValue("J{$row}", $dealer['grandtotal']);

		$sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);

		$sheet->getStyle("I2:J{$row}")
			->getNumberFormat()
			->setFormatCode('#,##0.00');

		foreach (range('A','K') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'dealer_commission_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		\PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function do_recalc_agent_comm()
	{
		$month_selected = $this->input->post('month_selected');

		$result = ['status' => 'err_msg', 'message' => 'Invalid Action'];

		$recalc_acl = check_acl_report('G2', false);
		if (!$recalc_acl) {
			$result = ['status' => 'err_msg', 'message' => 'No permission to recalc agent commission.'];
			echo json_encode($result);
			return false;
		}

		$year = substr($month_selected,0,4);
		$month = substr($month_selected,4);

		$run_date = date($year."-".$month."-d");

		$this->load->model('dealer_model');
		$return = $this->dealer_model->do_recalc_agent_comm($run_date);

		if ($return == '1') {
			$result = [
	                'success' => true, 
	                'message' => 'Done recalc agent commission report for '.$month_selected.'.'
	        ];
	    } else {
	    	//error
			$result = ['status' => 'err_msg', 'message' => $return];
	    }

		echo json_encode($result);
		return false;
	}

	function dealer_commission_by_dealer()
	{
		$data = [];
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback'    => 0,
			'txt_dealer'     => '',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end'   => date('Y-m-t'),
		];
		$return = get_filtered_ajax_data('report_dealer_commission_by_dealer_filter', $default_data, $post_data);
		$data = $return['data'];

		$this->load->model('dealer_model');
		$dealer_summary = $this->dealer_model->dealer_comm_summary(
			$data['txt_dealer'],
			$data['txt_date_start'],
			$data['txt_date_end']
		);

		$data['form_action'] = base_url('report/dealer_commission_by_dealer');
		$data['data_row']    = $return['initial'] ? [] : $dealer_summary;
		$data['page_title']  = 'Agent Commission (Dealer Summary)';
		$data['date_start']  = $data['txt_date_start'];
		$data['date_end']    = $data['txt_date_end'];
		$data['isprint']     = 0;
		$data['msg']         = $this->msg;

		$row_html = $this->parser->parse('report/dealer_commission_by_dealer_rows', $data, true);
		$data['row_html'] = $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/dealer_commission_by_dealer', $data);
		$this->load->view('templates/footer');
	}

	function dealer_commission_by_dealer_rows($returnOnly = 0)
	{
		$data = [];
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback'    => 0,
			'txt_dealer'     => '',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end'   => date('Y-m-t'),
		];
		$return = get_filtered_ajax_data('report_dealer_commission_by_dealer_filter', $default_data, $post_data);
		$data = $return['data'];

		$this->load->model('dealer_model');
		$dealer_summary = $this->dealer_model->dealer_comm_summary(
			$data['txt_dealer'],
			$data['txt_date_start'],
			$data['txt_date_end']
		);

		$data['data_row']   = $return['initial'] ? [] : $dealer_summary;
		$data['page_title'] = 'Agent Commission (Dealer Summary)';
		$data['date_start'] = $data['txt_date_start'];
		$data['date_end']   = $data['txt_date_end'];
		$data['isprint']    = 0;

		$html = $this->parser->parse('report/dealer_commission_by_dealer_rows', $data, true);

		if ($returnOnly) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_dealer_commission_by_dealer()
	{
		$txt_dealer     = $this->input->post('txt_dealer');
		$txt_date_start = $this->input->post('txt_date_start');
		$txt_date_end   = $this->input->post('txt_date_end');

		$this->load->model('dealer_model');
		$dealer_summary = $this->dealer_model->dealer_comm_summary(
			$txt_dealer, $txt_date_start, $txt_date_end
		);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Dealer Commission');

		$headers = ['Dealer','Total Customers','Total Bills','Total Bill Amount','Total Commission (RM)'];
		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($dealer_summary as $d) {
			$sheet->setCellValue('A'.$row, $d['dealer_name']);
			$sheet->setCellValue('B'.$row, $d['total_customers']);
			$sheet->setCellValue('C'.$row, $d['total_bills']);
			$sheet->setCellValue('D'.$row, $d['total_bill_amount']);
			$sheet->setCellValue('E'.$row, $d['total_commission']);
			$row++;
		}

		foreach (range('A','E') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'dealer_commission_summary_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function sales( $mode='' )
	{
		$data = array();
		$post_data = $this->input->post();
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_building' => 'all',
			'txt_date_start' => '',
			'txt_date_end' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_sales_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title']			= 'Sales Report';
		$data['form_action']		= base_url('report/sales');	
		$data['sel_category_list']	= $this->common_model->get_category_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		
		$data['msg']		= $this->msg;
		$row_html = $initial? '' : $this->sales_rows(1);
		$data['row_html'] = $row_html;
		$data['isprint'] = 0;

		if (!empty($contact_list)) {
			$json_contact_list = json_decode($contact_list);

			$_POST['scode_str'] = date('YmdHi');
			$_POST['scode'] = md5($_POST['scode_str'].$this->e_key);
			puppeteer_print_preview_forpost($this->config->item('base_url').'pdfapi/sales_report', $this->config->item('upload_path').'/temp/pdf/sales_report.pdf', json_encode($_POST), $this->config->item('proj_path'), $this->config->item('chrome_loc'));
			
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/sales_report.pdf',
				'Sales Report',
				'Attached herewith is the sales report sent from itelco system.',
				'[Sales Report]'
			);
		}

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/sales', $data);
		$this->load->view('templates/footer');
	}

	function sales_rows( $returnOnly = 0 )
	{
		$data = array();
		$post_data = $this->input->post();
		$default_data = [
			'is_postback' => 0,
			'sel_building' => 'all',
			'sel_category' => 'all',
			'txt_date_start' => '',
			'txt_date_end' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_sales_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$query_where = "";
		if ($data['sel_building'] != 'all') {
			$query_where .= " AND c.building = '" . $data['sel_building'] . "'";
		}
		if ($data['sel_category'] != 'all') {
			$query_where .= " AND c.category = '" . $data['sel_category'] . "'";
		}
		if (!empty($data['txt_date_start']) || !empty($data['txt_date_end'])) {
			$txt_date_start = !empty($data['txt_date_start']) ? $data['txt_date_start'] : '';
			$txt_date_end   = !empty($data['txt_date_end'])   ? $data['txt_date_end']   : '';
			$query_where .= " AND (csa.transact_date BETWEEN '$txt_date_start' AND '$txt_date_end')";
		}

		$details = $this->report_model->sales_report($query_where,$data['order_by'],$data['order_type']);

		$data['date_start']	= $data['txt_date_start'];
		$data['date_end']	= $data['txt_date_end'];
		$data['data_row']	= $initial? [] : $details;
		$data['page_title']	= 'Sales Report';
		$data['isprint'] = 0;

		$data['building_list'] = array_column($this->common_model->get_building_list() ?? [], 'name', 'building_no');

		$data['category_list'] = [
			'r' => 'Residential',
			'b' => 'Business',
			's' => 'Business(R)',
			'w' => 'Wholesale',
			'd' => 'DIA',
			'e' => 'BPP'
		];

		$data['pkg_list'] = $this->report_model->sales_report_pkg_count($query_where,$data['order_by'],$data['order_type']);

		$html = $this->parser->parse('report/sales_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	function export_sales()
	{
		$this->load->model('report_model');
		$is_postback    = $this->input->post('is_postback');
		$sel_building   = $this->input->post('sel_building');
		$sel_category   = $this->input->post('sel_category');
		$txt_date_start = $this->input->post('txt_date_start');
		$txt_date_end   = $this->input->post('txt_date_end');
		$order_by       = $this->input->post('order_by');
		$order_type     = $this->input->post('order_type');

		if (empty($txt_date_start)) $txt_date_start = '';
		if (empty($txt_date_end))   $txt_date_end   = '';

		$query_where = " ";
		if ($sel_category != 'all') $query_where .= " AND c.category = '$sel_category'";
		if ($sel_building != 'all') $query_where .= " AND c.building = '$sel_building'";
		$query_where .= " AND (csa.transact_date BETWEEN '$txt_date_start' AND '$txt_date_end')";

		//$data_row = (empty($is_postback)) ? [] : $this->report_model->detail_of_billing($query_where, $order_by, $order_type);
		$data_row = (empty($is_postback)) ? [] : $this->report_model->sales_report($query_where,$data['order_by'],$data['order_type']);

		/*
		echo "<pre>";
		print_r($data_row);
		echo "</pre>";
		exit;
		*/

		$building_list = array_column($this->common_model->get_building_list() ?? [], 'name', 'building_no');

		$category_list = [
			'r' => 'Residential',
			'b' => 'Business',
			's' => 'Business(R)',
			'w' => 'Wholesale',
			'd' => 'DIA',
			'e' => 'BPP'
		];

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();

		$objPHPExcel->getProperties()
			->setCreator('Itelco System')
			->setTitle('Sales Report');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Billing Details');

		$sheet->setCellValue('A1', 'Sales Report');
		if (!empty($txt_date_start) && !empty($txt_date_end)) {
			$sheet->setCellValue('A2', 'Period: ' . $txt_date_start . ' until ' . $txt_date_end);
		}
		$sheet->mergeCells('A1:D1');
		$sheet->mergeCells('A2:D2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$irow = 4;
		$overall_summary = [];

		$grand_last = '';
		$grand_second_last = '';

		$pkg_list = $this->report_model->sales_report_pkg_count($query_where,$data['order_by'],$data['order_type']);

		$prow = 4;
		if (!empty($pkg_list)) {

			$sheet->setCellValue('H'.$prow, 'Package Count');
			$sheet->getStyle('H'.$prow)->getFont()->setBold(true)->setSize(14);
			$prow++;
			$prow++;

			$headers = ['Package', 'Package Price', 'Subscriber Total'];

			$col = 'H';
			foreach ($headers as $h) {
				$sheet->setCellValue($col.$prow, $h);
				$sheet->getStyle($col.$prow)->getFont()->setBold(true);
				$sheet->getStyle('H' . $prow . ':J' . $prow)->getFill()
					->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
					->getStartColor()->setRGB('D9D9D9');
				$col++;
			}

			$prow++;

			$pkg_cnt = 0;

			foreach ($pkg_list as $p) {
				$sheet->setCellValue('H'.$prow, $p['package_name']);
				$sheet->setCellValue('I'.$prow, $p['monthly_charge']);
				$sheet->setCellValue('J'.$prow, $p['cnt']);
				$pkg_cnt = $pkg_cnt + $p['cnt'];
				$prow++;
			}

			$sheet->setCellValue('I'.$prow, 'Grand Total');
			$sheet->getStyle('I'.$prow)->getFont()->setBold(true);
			$sheet->setCellValue('J'.$prow, $pkg_cnt);
			$sheet->getStyle('J'.$prow)->getFont()->setBold(true);

		}

		$sheet->getColumnDimension('H')->setWidth(75);
		$sheet->getColumnDimension('I')->setWidth(20);
		$sheet->getColumnDimension('J')->setWidth(20);

		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->getColumnDimension('B')->setWidth(40);
		$sheet->getColumnDimension('C')->setWidth(60);

		if (!empty($data_row)) {

			$grand_total_array = [];

			foreach ($data_row as $building_no => $building_data) {
				$building_name = $building_list[$building_no] ?? 'No Building';
				$sheet->setCellValue('A' . $irow, $building_name);
				$sheet->getStyle('A' . $irow)->getFont()->setBold(true)->setSize(12);
				$irow++;

				$b_grand_subscription_total = 0;
				$b_grand_otc_total = 0;
				$b_grand_cat_total = 0;

				foreach ($building_data as $category_name => $customers) {
					$sheet->setCellValue('A' . $irow, $category_list[$category_name]);
					$sheet->getStyle('A' . $irow)->getFont()->setItalic(true)->setSize(12);

					$irow++;

					$headers = ['Customer No', 'Name', 'Package', 'Subscription', 'MRC Charges', 'Total'];

					$col = 'A';
					foreach ($headers as $h) {
						$sheet->setCellValue($col.$irow, $h);
						$sheet->getStyle($col.$irow)->getFont()->setBold(true);
						$sheet->getStyle('A' . $irow . ':F' . $irow)->getFill()
							->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
							->getStartColor()->setRGB('D9D9D9');
						$col++;
					}

					$sheet->getColumnDimension('D')->setWidth(20);
					$sheet->getColumnDimension('E')->setWidth(20);
					$sheet->getColumnDimension('F')->setWidth(20);

					$irow++;

					$subscription_total = 0;
					$otc_total = 0;
					$cat_total = 0;

					foreach ($customers as $cust) {
						$sheet->setCellValue('A'.$irow, $cust['customer_no']);
						$sheet->setCellValue('B'.$irow, $cust['name']);
						$sheet->setCellValue('C'.$irow, $cust['package_name']);
						$sheet->setCellValueExplicit('D' . $irow,  $cust['monthly_charge'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$sheet->getStyle('D' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
						$sheet->setCellValueExplicit('E' . $irow,  $cust['otc_charges'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$sheet->getStyle('E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
						$row_total = $cust['monthly_charge'] + $cust['otc_charges'];
						$sheet->setCellValueExplicit('F' . $irow,  $row_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$sheet->getStyle('F' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');

						$subscription_total = $subscription_total + $cust['monthly_charge'];
						$otc_total = $otc_total + $cust['otc_charges'];
						$cat_total = $cat_total + $row_total;

						$irow++;
					}

					$sheet->setCellValue('A'.$irow, '');
					$sheet->setCellValue('B'.$irow, '');
					$sheet->setCellValue('C'.$irow, 'Subtotal ('.$category_list[$category_name].' in '.($building_list[$building_no] ?? 'No Building').') ');
					$sheet->getStyle('C'.$irow)->getFont()->setBold(true);

					$sheet->setCellValueExplicit('D' . $irow,  $subscription_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$sheet->getStyle('D' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
					$sheet->getStyle('D'.$irow)->getFont()->setBold(true);

					$sheet->setCellValueExplicit('E' . $irow,  $otc_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$sheet->getStyle('E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
					$sheet->getStyle('E'.$irow)->getFont()->setBold(true);

					$sheet->setCellValueExplicit('F' . $irow,  $cat_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$sheet->getStyle('F' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
					$sheet->getStyle('F'.$irow)->getFont()->setBold(true);

					$b_grand_subscription_total = $b_grand_subscription_total + $subscription_total;
					$b_grand_otc_total = $b_grand_otc_total + $otc_total;
					$b_grand_cat_total = $b_grand_cat_total + $cat_total;

	                if (!isset($grand_total_array[$category_list[$category_name]])) {
	                    $grand_total_array[$category_list[$category_name]]['subscription'] = 0;
	                    $grand_total_array[$category_list[$category_name]]['otc'] = 0;
	                    $grand_total_array[$category_list[$category_name]]['cat'] = 0;
	                }

                    $grand_total_array[$category_list[$category_name]]['subscription'] = $grand_total_array[$category_list[$category_name]]['subscription'] + $subscription_total;
                    $grand_total_array[$category_list[$category_name]]['otc'] = $grand_total_array[$category_list[$category_name]]['otc'] + $otc_total;
                    $grand_total_array[$category_list[$category_name]]['cat'] = $grand_total_array[$category_list[$category_name]]['cat'] + $cat_total;

				}

				$irow++;

				$sheet->setCellValue('A'.$irow, '');
				$sheet->setCellValue('B'.$irow, '');
				$sheet->setCellValue('C'.$irow, 'Grand Total (All categories in '.($building_list[$building_no] ?? 'No Building').')');
				$sheet->getStyle('C'.$irow)->getFont()->setBold(true);

				$sheet->setCellValueExplicit('D' . $irow,  $b_grand_subscription_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('D' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->getStyle('D'.$irow)->getFont()->setBold(true);

				$sheet->setCellValueExplicit('E' . $irow,  $b_grand_otc_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->getStyle('E'.$irow)->getFont()->setBold(true);

				$sheet->setCellValueExplicit('F' . $irow,  $b_grand_cat_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('F' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->getStyle('F'.$irow)->getFont()->setBold(true);

				$irow++;
				$irow++;

			}

			$sheet->setCellValue('A' . $irow, 'Overall Summary (All Buildings)');
			$sheet->getStyle('A' . $irow)->getFont()->setBold(true)->setSize(12);

			$irow++;

			$gcat_subscription_total = 0;
			$gcat_otc_total = 0;
			$gcat_cat_total = 0;

			$headers = ['Category', '', '', 'Subscription', 'MRC Charges', 'Total'];

			$col = 'A';
			foreach ($headers as $h) {
				$sheet->setCellValue($col.$irow, $h);
				$sheet->getStyle($col.$irow)->getFont()->setBold(true);
				$sheet->getStyle('A' . $irow . ':F' . $irow)->getFill()
					->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
					->getStartColor()->setRGB('D9D9D9');
				$col++;
			}

			foreach ($grand_total_array as $gcat_text => $gcat) {
				$sheet->setCellValue('A'.$irow, $gcat_text);
				$sheet->getStyle('A'.$irow)->getFont()->setBold(true);

				$sheet->setCellValue('B'.$irow, '');
				$sheet->setCellValue('C'.$irow, '');

				$sheet->setCellValueExplicit('D' . $irow,  $gcat['subscription'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('D' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->getStyle('D'.$irow)->getFont()->setBold(true);

				$sheet->setCellValueExplicit('E' . $irow,  $gcat['otc'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->getStyle('E'.$irow)->getFont()->setBold(true);

				$sheet->setCellValueExplicit('F' . $irow,  $gcat['cat'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('F' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->getStyle('F'.$irow)->getFont()->setBold(true);

				$gcat_subscription_total = $gcat_subscription_total + $gcat['subscription'];
				$gcat_otc_total = $gcat_otc_total + $gcat['otc'];
				$gcat_cat_total = $gcat_cat_total + $gcat['cat'];

				$irow++;
			}

			$sheet->setCellValue('A'.$irow, 'Grand Total');
			$sheet->getStyle('A'.$irow)->getFont()->setBold(true);

			$sheet->setCellValue('B'.$irow, '');
			$sheet->setCellValue('C'.$irow, '');

			$sheet->setCellValueExplicit('D' . $irow,  $gcat_subscription_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle('D' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
			$sheet->getStyle('D'.$irow)->getFont()->setBold(true);

			$sheet->setCellValueExplicit('E' . $irow,  $gcat_otc_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle('E' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
			$sheet->getStyle('E'.$irow)->getFont()->setBold(true);

			$sheet->setCellValueExplicit('F' . $irow,  $gcat_cat_total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle('F' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
			$sheet->getStyle('F'.$irow)->getFont()->setBold(true);

		}

		while (ob_get_level()) ob_end_clean();

		$filename = 'sales_report_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;

	}
	
	function payment_summary( $mode='' )
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'txt_date_start' => date('Y-m-01'),
			'txt_date_end' => date('Y-m-t'),
			'display_col' => []
		];
		$return = get_filtered_ajax_data('report_payment_summary_filter', $default_data, $post_data);

		$data = $return['data'];

		$data['print']	= $mode;

		$contact_list	= $this->input->post('contact_list');

		$data['page_title']			= 'Payment Summary';
		$data['form_action']		= base_url('report/payment_summary');		
		$data['sel_category_list']	= $this->common_model->get_category_list();
		$data['sel_status_list']	= $this->common_model->get_acc_status_list();		
		$data['bill_type_list']		= array();

		$this->load->model('payment_model');
		$payment = $this->payment_model->payment_summary($data['sel_category'], $data['sel_status'], $data['txt_date_start'], $data['txt_date_end']);

		$data['bill_type_list'] = $payment['bill_type'];

		$data['msg']			= $this->msg;

		$row_html = $this->payment_summary_rows(1);
		$data['row_html'] = $row_html ; 

		if (!empty($contact_list)) {
			//sent the print version of this report
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'payment_summary';
			$header_data['description']	= 'Payment Summary Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/payment_summary_print',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/payment_summary.pdf', $pdf_data);

			//send pdf
			// $send_result = $this->sendReportDoc(
			// 	$json_contact_list,
			// 	$this->config->item('upload_path') . '/temp/pdf/payment_summary.pdf',
			// 	'Payment Summary Report',
			// 	'Attached herewith is the payment summary report sent from itelco system.',
			// 	'[Payment Summary Report]'
			// );

		}

		$data['isprint'] = 0;
		
		if( $data['print'] != 'print' ){
			$this->load->view('templates/header', $this->vars);
			$this->parser->parse('report/payment_summary',$data);
			$this->load->view('templates/footer');
		} else {
			//print form
			$this->load->view('templates/header', $this->vars);
			$this->parser->parse('report/payment_summary_print',$data);
			$this->load->view('templates/footer');
		}
	}

	function payment_summary_rows($returnOnly = 0)
	{
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback'   => 0,
			'sel_category'  => 'all',
			'sel_status'    => 'all',
			'txt_date_start'=> date('Y-m-01'),
			'txt_date_end'  => date('Y-m-t'),
			'display_col'   => []
		];
		$return = get_filtered_ajax_data('report_payment_summary_filter', $default_data, $post_data);

		$data = $return['data'] + [
			'initial'       => $return['initial'],
			'page_title'    => 'Payment Summary',
			'date_start'    => $return['data']['txt_date_start'],
			'date_end'      => $return['data']['txt_date_end']
		];

		$totals = ['r'=>0,'b'=>0,'d'=>0,'w'=>0];

		$this->load->model('payment_model');
		$payment = $this->payment_model->payment_summary(
			$data['sel_category'],
			$data['sel_status'],
			$data['txt_date_start'],
			$data['txt_date_end']
		);

		$data = array_merge($data, [
			'bill_type_list'=> $payment['bill_type'],
			'data_row_r'    => $payment['r'],
			'data_row_b'    => $payment['b'],
			'data_row_d'    => $payment['d'],
			'data_row_e'    => $payment['e'],
			'data_row_w'    => $payment['w'],
		]);

		foreach ($data['bill_type_list'] as $key => $val) {
			$totals['r'] += $data['data_row_r'][$key]['amount'] ?? 0;
			$totals['b'] += ($data['data_row_b'][$key]['amount'] ?? 0) + ($payment['s'][$key]['amount'] ?? 0);
			$totals['d'] += ($data['data_row_d'][$key]['amount'] ?? 0) + ($data['data_row_e'][$key]['amount'] ?? 0);
			$totals['w'] += $data['data_row_w'][$key]['amount'] ?? 0;
		}

		$data['total_r'] = $totals['r'];
		$data['total_b'] = $totals['b'];
		$data['total_d'] = $totals['d'];
		$data['total_w'] = $totals['w'];
		$data['total_all'] = array_sum($totals);

		$html = $this->parser->parse('report/payment_summary_rows', $data, true);
		return !empty($returnOnly) ? $html : print($html);
	}
	
	function export_payment_summary()
	{
		$is_postback     = $this->input->post('is_postback');		
		$sel_category    = $this->input->post('sel_category');
		$sel_status      = $this->input->post('sel_status');
		$txt_date_start  = $this->input->post('txt_date_start');
		$txt_date_end    = $this->input->post('txt_date_end');
		$display_col     = $this->input->post('display_col');
		if (empty($display_col)) $display_col = [];

		if (empty($txt_date_start)) $txt_date_start = date('Y-m-01');
		if (empty($txt_date_end))   $txt_date_end   = date('Y-m-t');

		$data = [
			'bill_type_list' => [],
			'data_row_r'     => [],
			'data_row_b'     => [],
			'data_row_w'     => [],
			'total_r'        => 0,
			'total_b'        => 0,
			'total_w'        => 0,
			'total_all'      => 0,
		];

		if (!empty($is_postback)) {
			$this->load->model('payment_model');
			$payment = $this->payment_model->payment_summary($sel_category, $sel_status, $txt_date_start, $txt_date_end);
			$data['bill_type_list'] = $payment['bill_type'];
			$data['data_row_r'] = $payment['r'];
			$data['data_row_b'] = $payment['b'];
			$data['data_row_w'] = $payment['w'];

			foreach ($data['bill_type_list'] as $key => $val) {
				$data['total_r'] += $data['data_row_r'][$key]['amount'];
				$data['total_b'] += $data['data_row_b'][$key]['amount'];
				$data['total_w'] += $data['data_row_w'][$key]['amount'];
			}
			$data['total_all'] = $data['total_r'] + $data['total_b'] + $data['total_w'];
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Payment Summary');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Payment Summary');
		$sheet->setCellValue('A1', 'Payment Summary');
		$sheet->setCellValue('A2', 'Period: ' . $txt_date_start . ' until ' . $txt_date_end);
		$sheet->mergeCells('A1:Z1');
		$sheet->mergeCells('A2:Z2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$irow = 4;
		$sheet->setCellValue('A' . $irow, 'Category');
		$col = 'B';
		foreach ($data['bill_type_list'] as $bill_id => $row) {
			if (!empty($display_col) && !in_array($bill_id, $display_col)) continue;
			$sheet->setCellValue($col . $irow, $row['name']);
			$col++;
		}
		$sheet->setCellValue($col . $irow, 'Total');
		$sheet->getStyle('A' . $irow . ':' . $col . $irow)->getFont()->setBold(true);
		$sheet->getStyle('A' . $irow . ':' . $col . $irow)->getAlignment()->setWrapText(true);
		$irow++;

		$types = ['Business' => 'data_row_b', 'Residential' => 'data_row_r', 'Wholesale' => 'data_row_w'];
		foreach ($types as $type_name => $key) {
			$sheet->setCellValue('A' . $irow, $type_name);
			$col = 'B';
			foreach ($data['bill_type_list'] as $bill_id => $row) {
				if (!empty($display_col) && !in_array($bill_id, $display_col)) continue;
				$sheet->setCellValueExplicit($col . $irow, $data[$key][$bill_id]['amount'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle($col . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$col++;
			}
			$total_key = 'total_' . strtolower($type_name[0]);
			$sheet->setCellValueExplicit($col . $irow, $data[$total_key], PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle($col . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
			$irow++;
		}

		$sheet->setCellValue('A' . $irow, 'Total');
		$col = 'B';
		foreach ($data['bill_type_list'] as $bill_id => $row) {
			if (!empty($display_col) && !in_array($bill_id, $display_col)) continue;
			$sheet->setCellValueExplicit($col . $irow, $row['total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->getStyle($col . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
			$col++;
		}
		$sheet->setCellValueExplicit($col . $irow, $data['total_all'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
		$sheet->getStyle($col . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
		$sheet->getStyle('A' . $irow . ':' . $col . $irow)->getFont()->setBold(true);

		$lastColIndex = PHPExcel_Cell::columnIndexFromString($col);
		for ($i = 0; $i < $lastColIndex; $i++) {
			$columnLetter = PHPExcel_Cell::stringFromColumnIndex($i);
			$sheet->getColumnDimension($columnLetter)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'payment_summary_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
	
	//Customer Aging report
	function customer_aging()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'nozero' => 0,
			'order_by' => '',
			'order_type' => '',
			'as_date' => '',
		];
		$return = get_filtered_ajax_data('report_customer_aging_filter', $default_data, $post_data);

		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
			
		$data['page_title']			= 'Customer - Aging';
		$data['form_action']		= base_url('report/customer_aging');
		$data['sel_category_list']	= $this->common_model->get_category_list();

		$row_html = $this->customer_aging_rows(1);
		$data['row_html'] = $row_html; 
		$data['msg'] = $this->msg;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'customer_aging';
			$header_data['description']	= 'Customer Aging Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/customer_aging', $data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/customer_aging.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/customer_aging.pdf',
				'Customer Aging Report',
				'Attached herewith is the customer aging report sent from itelco system.',
				'[Customer Aging Report]'
			);
		}

		$data['isprint'] = 0;

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/customer_aging',$data);
		$this->load->view('templates/footer');
	}

	function customer_aging_rows($returnOnly = 0) 
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'nozero' => 0,
			'order_by' => '',
			'order_type' => '',
			'as_date' => '',
		];
		$return = get_filtered_ajax_data('report_customer_aging_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$query_where = '';
		if( !empty($data['sel_category']) && $data['sel_category'] !== 'all' ) {
			$query_where .= " AND c.category = '".$data['sel_category']."' ";
		}
		
		if( !empty( $data['sel_status'] ) && $data['sel_status'] != 'all'  ){
			$query_where .= " AND cs.status = '".$data['sel_status']."' ";
		}

		$results = $this->report_model->get_customer_aging_report( $query_where, $data['order_by'], $data['order_type'], 0, $data['as_date'] ) ;
		$data_row = array();
		foreach( $results as $row ){

			$month1=0 ;
			$month2=0 ;
			$month3=0 ;
			$month4=0 ;
			$month5=0 ;
			$year1 =0 ;
			$year2 =0 ;
			$total_ar = $row['ALLDAYS'] - $row['total_payment'] ;
			if( $row['total_payment'] - $row['2YEARS']  > 0){
				$row['total_payment'] = $row['total_payment'] - $row['2YEARS'] ;
				if( $row['total_payment'] - $row['1YEARS']  > 0){
					$row['total_payment'] = $row['total_payment'] - $row['1YEARS'] ;
					if( $row['total_payment'] - $row['365DAYS'] > 0 ){ //overpaid , outstanding = 0 
						$row['total_payment'] = $row['total_payment'] - $row['365DAYS'] ;
						if( $row['total_payment'] - $row['120DAYS'] > 0 ){ //overpaid , outstanding = 0 
							$row['total_payment'] = $row['total_payment'] - $row['120DAYS'] ;
							//echo "OP 1.1 -->".$row['total_payment']."<br />" ; 
							if( $row['total_payment'] - $row['90DAYS'] > 0 ){ //overpaid , outstanding = 0
								$row['total_payment'] = $row['total_payment'] - $row['90DAYS'] ;
								//echo "OP 2.1 -->".$row['total_payment']."<br />" ;
								if( $row['total_payment'] - $row['60DAYS'] > 0 ){ //overpaid , outstanding = 0
									$row['total_payment'] = $row['total_payment'] - $row['60DAYS'] ;
									//echo "OP 3.1 -->".$row['total_payment']."<br />" ;
									if( $row['total_payment'] - $row['30DAYS'] > 0 ){ //overpaid , outstanding = 0
										$row['total_payment'] = $row['total_payment'] - $row['30DAYS'] ;
										//echo "OP 4.1 -->".$row['total_payment']."<br />" ;
									}else{
										$month1 = $row['30DAYS'] - $row['total_payment'] ;
									}
							
								}else{
									$month2 = $row['60DAYS'] - $row['total_payment'] ;
									$month1 = $row['30DAYS'] ;
								}
								
							}else{
								$month3 = $row['90DAYS'] - $row['total_payment'] ;
								$month2 = $row['60DAYS'] ;
								$month1 = $row['30DAYS'] ;
							}
						
						}else{
							$month4 = $row['120DAYS'] - $row['total_payment'] ;
							$month3 = $row['90DAYS'];
							$month2 = $row['60DAYS'];
							$month1 = $row['30DAYS'];
						}
					}else{
						$month5 = $row['365DAYS'] - $row['total_payment'] ;
						$month4 = $row['120DAYS'];
						$month3 = $row['90DAYS'];
						$month2 = $row['60DAYS'];
						$month1 = $row['30DAYS'];				
					}
				}else{
					$year1  = $row['1YEARS'] - $row['total_payment'] ;
					$month5 = $row['365DAYS'];
					$month4 = $row['120DAYS'];
					$month3 = $row['90DAYS'];
					$month2 = $row['60DAYS'];
					$month1 = $row['30DAYS'];
				}
			}else{
				$year2  = $row['2YEARS'] - $row['total_payment'] ;
				$year1  = $row['1YEARS'] ;
				$month5 = $row['365DAYS'];
				$month4 = $row['120DAYS'];
				$month3 = $row['90DAYS'];
				$month2 = $row['60DAYS'];
				$month1 = $row['30DAYS'];
			}
				
				
		
			$data_row[] = array(
				'customer_no' => $row['customer_no'], 
				'customer_name' => $row['customer_name'],
				'total_ar' => $total_ar ,
				'month1' => $month1,
				'month2' => $month2,
				'month3' => $month3,
				'month4' => $month4,
				'month5' => $month5,
				'year1'  => $year1 ,
				'year2'  => $year2 
			);
		}

		$data['data_row'] = $initial? [] : $data_row;
		$data['page_title']	= 'Customer - Aging';
		$data['isprint'] = $this->input->post('isprint');

		$html = $this->parser->parse('report/customer_aging_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}
	
	//Customer Aging report
	public function export_customer_aging()
	{
		$is_postback  	= $this->input->post('is_postback');
		$sel_category 	= $this->input->post('sel_category');
		$order_by     	= $this->input->post('order_by');
		$order_type   	= $this->input->post('order_type');
		$sel_status   	= $this->input->post('sel_status');
		$nozero 		= $this->input->post('nozero');

		$as_date 		= $this->input->post('as_date');

		$query_where = '';
		if (!empty($sel_category) && $sel_category !== 'all') {
			$query_where .= " AND c.category = '".$this->db->escape_str($sel_category)."' ";
		}
		if (!empty($sel_status) && $sel_status !== 'all') {
			$query_where .= " AND cs.status = '".$this->db->escape_str($sel_status)."' ";
		}

		$results = $this->report_model->get_customer_aging_report(
			$query_where,
			$order_by,
			$order_type,
			0,
			$as_date
		);

		$data_row = [];
		foreach ($results as $row) {
			$month1 = $month2 = $month3 = $month4 = $month5 = $year1 = $year2 = 0;
			$total_ar = round($row['ALLDAYS'] - $row['total_payment'], 2);

			$tp = $row['total_payment'];

			if ($tp - $row['2YEARS'] > 0) {
				$tp -= $row['2YEARS'];
				if ($tp - $row['1YEARS'] > 0) {
					$tp -= $row['1YEARS'];
					if ($tp - $row['365DAYS'] > 0) {
						$tp -= $row['365DAYS'];
						if ($tp - $row['120DAYS'] > 0) {
							$tp -= $row['120DAYS'];
							if ($tp - $row['90DAYS'] > 0) {
								$tp -= $row['90DAYS'];
								if ($tp - $row['60DAYS'] > 0) {
									$tp -= $row['60DAYS'];
									$month1 = round(max($row['30DAYS'] - $tp, 0), 2);
								} else {
									$month2 = round($row['60DAYS'] - $tp, 2);
									$month1 = round($row['30DAYS'], 2);
								}
							} else {
								$month3 = round($row['90DAYS'] - $tp, 2);
								$month2 = round($row['60DAYS'], 2);
								$month1 = round($row['30DAYS'], 2);
							}
						} else {
							$month4 = round($row['120DAYS'] - $tp, 2);
							$month3 = round($row['90DAYS'], 2);
							$month2 = round($row['60DAYS'], 2);
							$month1 = round($row['30DAYS'], 2);
						}
					} else {
						$month5 = round($row['365DAYS'] - $tp, 2);
						$month4 = round($row['120DAYS'], 2);
						$month3 = round($row['90DAYS'], 2);
						$month2 = round($row['60DAYS'], 2);
						$month1 = round($row['30DAYS'], 2);
					}
				} else {
					$year1  = round($row['1YEARS'] - $tp, 2);
					$month5 = round($row['365DAYS'], 2);
					$month4 = round($row['120DAYS'], 2);
					$month3 = round($row['90DAYS'], 2);
					$month2 = round($row['60DAYS'], 2);
					$month1 = round($row['30DAYS'], 2);
				}
			} else {
				$year2  = round($row['2YEARS'] - $tp, 2);
				$year1  = round($row['1YEARS'], 2);
				$month5 = round($row['365DAYS'], 2);
				$month4 = round($row['120DAYS'], 2);
				$month3 = round($row['90DAYS'], 2);
				$month2 = round($row['60DAYS'], 2);
				$month1 = round($row['30DAYS'], 2);
			}

			$data_row[] = [
				'customer_no'   => $row['customer_no'],
				'customer_name' => $row['customer_name'],
				'month1'        => $month1,
				'month2'        => $month2,
				'month3'        => $month3,
				'month4'        => $month4,
				'month5'        => $month5,
				'year1'         => $year1,
				'year2'         => $year2,
				'total_ar'      => $total_ar
			];
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Customer Aging');

		$headers = [
			'Customer No','Name','0-30 days','31-60 days','61-90 days',
			'91-120 days','121-364 days','1-2 years','> 2 years','Total A/R'
		];

		$row = 1;
		$col = 'A';
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row++;
		$total_30 = $total_60 = $total_90 = $total_120 = $total_150 = $total_365 = $total_730 = $total_ar = 0;

        $ctotal_ar = 0;
        $ctotal_30 = 0;
        $ctotal_60 = 0;
        $ctotal_90 = 0;
        $ctotal_120 = 0;
        $ctotal_150 = 0;
        $ctotal_365 = 0;
        $ctotal_730 = 0; 

		foreach ($data_row as $r) {

			if ($nozero == 1) {
				if (round($r['total_ar'],2) > 0.001) {
				} else {
					continue;
				}
			}

			$sheet->setCellValue('A'.$row, $r['customer_no']);
			$sheet->setCellValue('B'.$row, $r['customer_name']);
			$sheet->setCellValue('C'.$row, strval(round($r['month1'],2)));
			$sheet->setCellValue('D'.$row, strval(round($r['month2'],2)));
			$sheet->setCellValue('E'.$row, strval(round($r['month3'],2)));
			$sheet->setCellValue('F'.$row, strval(round($r['month4'],2)));
			$sheet->setCellValue('G'.$row, strval(round($r['month5'],2)));
			$sheet->setCellValue('H'.$row, strval(round($r['year1'],2)));
			$sheet->setCellValue('I'.$row, strval(round($r['year2'],2)));
			$sheet->setCellValue('J'.$row, strval(round($r['total_ar'],2)));

			$total_30 += round($r['month1'],2);
			$total_60 += round($r['month2'],2);
			$total_90 += round($r['month3'],2);
			$total_120 += round($r['month4'],2);
			$total_150 += round($r['month5'],2);
			$total_365 += round($r['year1'],2);
			$total_730 += round($r['year2'],2);
			$total_ar  += round($r['total_ar'],2);

            if (round($r['month1'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_30++;
            }

            if (round($r['month2'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_60++;
            }

            if (round($r['month3'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_90++;
            }

            if (round($r['month4'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_120++;
            }

            if (round($r['month5'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_150++;
            }

            if (round($r['year1'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_365++;
            }

            if (round($r['year2'],2) > 0.001) {
                $ctotal_ar++;
                $ctotal_730++;
            }

			$row++;
		}

		$sheet->setCellValue('B'.$row, 'Total');
		$sheet->setCellValue('C'.$row, strval($total_30));
		$sheet->setCellValue('D'.$row, strval($total_60));
		$sheet->setCellValue('E'.$row, strval($total_90));
		$sheet->setCellValue('F'.$row, strval($total_120));
		$sheet->setCellValue('G'.$row, strval($total_150));
		$sheet->setCellValue('H'.$row, strval($total_365));
		$sheet->setCellValue('I'.$row, strval($total_730));
		$sheet->setCellValue('J'.$row, strval($total_ar));
		$sheet->getStyle('B'.$row.':J'.$row)->getFont()->setBold(true);

		$row++;

		$sheet->setCellValue('B'.$row, 'Total Rows (Not 0$)');
		$sheet->setCellValue('C'.$row, strval($ctotal_30));
		$sheet->setCellValue('D'.$row, strval($ctotal_60));
		$sheet->setCellValue('E'.$row, strval($ctotal_90));
		$sheet->setCellValue('F'.$row, strval($ctotal_120));
		$sheet->setCellValue('G'.$row, strval($ctotal_150));
		$sheet->setCellValue('H'.$row, strval($ctotal_365));
		$sheet->setCellValue('I'.$row, strval($ctotal_730));
		$sheet->setCellValue('J'.$row, strval($ctotal_ar));
		$sheet->getStyle('B'.$row.':J'.$row)->getFont()->setBold(true);

		foreach (range('A','J') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'customer_aging_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function detailed_customer_aging()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => date('Y-m-d'),
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_detailed_customer_aging_filter', $default_data, $post_data);

		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title']			= 'Customer - Detailed Aging';
		$data['form_action']		= base_url('report/detailed_customer_aging');
	
		$row_html = $this->detailed_customer_aging_rows(1);
		$data['row_html'] = $row_html ; 
		$data['msg'] = $this->msg;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;
			$txt_customer_no = $data['txt_customer_no'];
			$header_data = array();
			$header_data['title']		= 'detailed_customer_aging';
			$header_data['description']	= 'Detailed Customer Aging Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/detailed_customer_aging',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/detailed_customer_aging.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/detailed_customer_aging.pdf',
				'Detailed Customer Aging Report',
				'Attached herewith is the detailed customer aging report (Acc No: ' . $txt_customer_no . ') sent from itelco system.',
				'[Detailed Customer Aging Report]'
			);
		}

		$data['isprint'] = 0;

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/detailed_customer_aging',$data);
		$this->load->view('templates/footer');
	}

	function detailed_customer_aging_rows($returnOnly = 0) {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => date('Y-m-d'),
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_detailed_customer_aging_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$results = $this->report_model->get_detailed_customer_aging_report( $data['txt_customer_no'], $data['date_from'], $data['date_to'] );
		
		$data_row = array();
		foreach( $results as $row ){

			$month1=0 ;
			$month2=0 ;
			$month3=0 ;
			$month4=0 ;
			$month5=0 ;
			$year1 =0 ;
			$year2 =0 ;
			$total_ar = $row['ALLDAYS'] - $row['total_payment'] ;
			if( $row['total_payment'] - $row['2YEARS']  > 0){
				$row['total_payment'] = $row['total_payment'] - $row['2YEARS'] ;
				if( $row['total_payment'] - $row['1YEARS']  > 0){
					$row['total_payment'] = $row['total_payment'] - $row['1YEARS'] ;
					if( $row['total_payment'] - $row['365DAYS'] > 0 ){ //overpaid , outstanding = 0 
						$row['total_payment'] = $row['total_payment'] - $row['365DAYS'] ;
						if( $row['total_payment'] - $row['120DAYS'] > 0 ){ //overpaid , outstanding = 0 
							$row['total_payment'] = $row['total_payment'] - $row['120DAYS'] ;
							//echo "OP 1.1 -->".$row['total_payment']."<br />" ; 
							if( $row['total_payment'] - $row['90DAYS'] > 0 ){ //overpaid , outstanding = 0
								$row['total_payment'] = $row['total_payment'] - $row['90DAYS'] ;
								//echo "OP 2.1 -->".$row['total_payment']."<br />" ;
								if( $row['total_payment'] - $row['60DAYS'] > 0 ){ //overpaid , outstanding = 0
									$row['total_payment'] = $row['total_payment'] - $row['60DAYS'] ;
									//echo "OP 3.1 -->".$row['total_payment']."<br />" ;
									if( $row['total_payment'] - $row['30DAYS'] > 0 ){ //overpaid , outstanding = 0
										$row['total_payment'] = $row['total_payment'] - $row['30DAYS'] ;
										//echo "OP 4.1 -->".$row['total_payment']."<br />" ;
									}else{
										$month1 = $row['30DAYS'] - $row['total_payment'] ;
									}
							
								}else{
									$month2 = $row['60DAYS'] - $row['total_payment'] ;
									$month1 = $row['30DAYS'] ;
								}
								
							}else{
								$month3 = $row['90DAYS'] - $row['total_payment'] ;
								$month2 = $row['60DAYS'] ;
								$month1 = $row['30DAYS'] ;
							}
						
						}else{
							$month4 = $row['120DAYS'] - $row['total_payment'] ;
							$month3 = $row['90DAYS'];
							$month2 = $row['60DAYS'];
							$month1 = $row['30DAYS'];
						}
					}else{
						$month5 = $row['365DAYS'] - $row['total_payment'] ;
						$month4 = $row['120DAYS'];
						$month3 = $row['90DAYS'];
						$month2 = $row['60DAYS'];
						$month1 = $row['30DAYS'];				
					}
				}else{
					$year1  = $row['1YEARS'] - $row['total_payment'] ;
					$month5 = $row['365DAYS'];
					$month4 = $row['120DAYS'];
					$month3 = $row['90DAYS'];
					$month2 = $row['60DAYS'];
					$month1 = $row['30DAYS'];
				}
			}else{
				$year2  = $row['2YEARS'] - $row['total_payment'] ;
				$year1  = $row['1YEARS'] ;
				$month5 = $row['365DAYS'];
				$month4 = $row['120DAYS'];
				$month3 = $row['90DAYS'];
				$month2 = $row['60DAYS'];
				$month1 = $row['30DAYS'];
			}
				
				
		
			$data_row[] = array(
				'bill_no' => $row['bill_no'], 
				'bill_date' => $row['bill_date'],
				'total_ar' => $total_ar ,
				'month1' => $month1,
				'month2' => $month2,
				'month3' => $month3,
				'month4' => $month4,
				'month5' => $month5,
				'year1'  => $year1 ,
				'year2'  => $year2 
			);
		}

		if (!empty($data['txt_customer_no'])) { 
			$get_customer_by_cust_no = $this->report_model->get_customer_by_cust_no($data['txt_customer_no']);
			$customer_row 						= $get_customer_by_cust_no['row_array']; //$query->row_array();

			$data['customer_no'] = $customer_row['customer_no'];
			$data['login_username'] = $customer_row['login_username'];
			$data['name'] = $customer_row['name'];
			$data['package_name'] = $customer_row['package_name'];
		} else {
			$data['customer_no'] = '';
			$data['login_username'] = '';
			$data['name'] = '';
			$data['package_name'] = '';
		}

		$data['data_row'] = $initial? [] : $data_row;
		$data['page_title']	= 'Customer - Detailed Aging';
		$data['isprint'] = $this->input->post('isprint');

		$html = $this->parser->parse('report/detailed_customer_aging_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_detailed_customer_aging()
	{
		$txt_customer_no = $this->input->post('txt_customer_no');
		$date_from       = $this->input->post('date_from') != '' ? $this->input->post('date_from') : '';
		$date_to         = $this->input->post('date_to') != '' ? $this->input->post('date_to') : date('Y-m-d');

		$results = $this->report_model->get_detailed_customer_aging_report($txt_customer_no, $date_from, $date_to);

		$data_row = [];
		foreach ($results as $row) {
			$month1 = $month2 = $month3 = $month4 = $month5 = $year1 = $year2 = 0;
			$total_ar = round($row['ALLDAYS'] - $row['total_payment'], 2);

			$tp = $row['total_payment'];

			if ($tp - $row['2YEARS'] > 0) {
				$tp -= $row['2YEARS'];
				if ($tp - $row['1YEARS'] > 0) {
					$tp -= $row['1YEARS'];
					if ($tp - $row['365DAYS'] > 0) {
						$tp -= $row['365DAYS'];
						if ($tp - $row['120DAYS'] > 0) {
							$tp -= $row['120DAYS'];
							if ($tp - $row['90DAYS'] > 0) {
								$tp -= $row['90DAYS'];
								if ($tp - $row['60DAYS'] > 0) {
									$tp -= $row['60DAYS'];
									$month1 = round(max($row['30DAYS'] - $tp, 0), 2);
								} else {
									$month2 = round($row['60DAYS'] - $tp, 2);
									$month1 = round($row['30DAYS'], 2);
								}
							} else {
								$month3 = round($row['90DAYS'] - $tp, 2);
								$month2 = round($row['60DAYS'], 2);
								$month1 = round($row['30DAYS'], 2);
							}
						} else {
							$month4 = round($row['120DAYS'] - $tp, 2);
							$month3 = round($row['90DAYS'], 2);
							$month2 = round($row['60DAYS'], 2);
							$month1 = round($row['30DAYS'], 2);
						}
					} else {
						$month5 = round($row['365DAYS'] - $tp, 2);
						$month4 = round($row['120DAYS'], 2);
						$month3 = round($row['90DAYS'], 2);
						$month2 = round($row['60DAYS'], 2);
						$month1 = round($row['30DAYS'], 2);
					}
				} else {
					$year1  = round($row['1YEARS'] - $tp, 2);
					$month5 = round($row['365DAYS'], 2);
					$month4 = round($row['120DAYS'], 2);
					$month3 = round($row['90DAYS'], 2);
					$month2 = round($row['60DAYS'], 2);
					$month1 = round($row['30DAYS'], 2);
				}
			} else {
				$year2  = round($row['2YEARS'] - $tp, 2);
				$year1  = round($row['1YEARS'], 2);
				$month5 = round($row['365DAYS'], 2);
				$month4 = round($row['120DAYS'], 2);
				$month3 = round($row['90DAYS'], 2);
				$month2 = round($row['60DAYS'], 2);
				$month1 = round($row['30DAYS'], 2);
			}

			$data_row[] = [
				'customer_no'   => $row['customer_no'],
				'customer_name' => $row['customer_name'],
				'bill_no'       => $row['bill_no'],
				'bill_date'     => $row['bill_date'],
				'month1'        => $month1,
				'month2'        => $month2,
				'month3'        => $month3,
				'month4'        => $month4,
				'month5'        => $month5,
				'year1'         => $year1,
				'year2'         => $year2,
				'total_ar'      => $total_ar
			];
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Detailed Customer Aging');

		$headers = [
			'Customer No','Customer Name','Bill No','Bill Date','0-30 days','31-60 days','61-90 days',
			'91-120 days','121-364 days','1-2 years','> 2 years','Total A/R'
		];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row++;
		$totals = [
			'month1'=>0, 'month2'=>0, 'month3'=>0, 'month4'=>0,
			'month5'=>0, 'year1'=>0, 'year2'=>0, 'total_ar'=>0
		];

		foreach ($data_row as $r) {
			$sheet->setCellValue('A'.$row, $r['customer_no']);
			$sheet->setCellValue('B'.$row, $r['customer_name']);
			$sheet->setCellValue('C'.$row, $r['bill_no']);
			$sheet->setCellValue('D'.$row, $r['bill_date']);
			$sheet->setCellValue('E'.$row, $r['customer_name']);
			$sheet->setCellValue('F'.$row, $r['month1']);
			$sheet->setCellValue('G'.$row, $r['month2']);
			$sheet->setCellValue('H'.$row, $r['month3']);
			$sheet->setCellValue('I'.$row, $r['month4']);
			$sheet->setCellValue('J'.$row, $r['month5']);
			$sheet->setCellValue('K'.$row, $r['year1']);
			$sheet->setCellValue('L'.$row, $r['year2']);
			$sheet->setCellValue('M'.$row, $r['total_ar']);
			$sheet->setCellValue('N'.$row, $r['bill_no']);
			$sheet->setCellValue('O'.$row, $r['bill_date']);

			foreach (['month1','month2','month3','month4','month5','year1','year2','total_ar'] as $key) {
				$totals[$key] += $r[$key];
			}

			$row++;
		}

		$sheet->setCellValue('D'.$row, 'Total');
		$sheet->setCellValue('E'.$row, $totals['month1']);
		$sheet->setCellValue('F'.$row, $totals['month2']);
		$sheet->setCellValue('G'.$row, $totals['month3']);
		$sheet->setCellValue('H'.$row, $totals['month4']);
		$sheet->setCellValue('I'.$row, $totals['month5']);
		$sheet->setCellValue('J'.$row, $totals['year1']);
		$sheet->setCellValue('K'.$row, $totals['year2']);
		$sheet->setCellValue('L'.$row, $totals['total_ar']);
		$sheet->getStyle('B'.$row.':L'.$row)->getFont()->setBold(true);

		foreach (range('A','L') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'detailed_customer_aging_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function customer_transaction_listing()
	{
		$is_postback 				= $this->input->post('is_postback');		
		$txt_search 				= $this->input->post('txt_search');
		$txt_customer_no 			= $this->input->post('txt_customer_no');	

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Customer Transaction Listing';
		$data['form_action'] 		= base_url('report/customer_transaction_listing');		
		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_acc_status 			= $_SESSION['acc_status'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];		
			
		$data['msg'] 				= $this->msg;

		$row_html = $this->customer_transaction_listing_rows(1);
		$data['row_html']		= $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;
			$header_data = array();
			$header_data['title']		= 'customer_transaction_listing';
			$header_data['description']	= 'Customer Transaction Listing Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/customer_transaction_listing',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/customer_transaction_listing.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/customer_transaction_listing.pdf',
				'Customer Transaction Listing Report',
				'Attached herewith is the customer transaction listing report sent from itelco system.',
				'[Customer Transaction Listing Report]'
			);
		}

		$data['isprint'] = 0;
		
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/customer_transaction_listing',$data);
		$this->load->view('templates/footer');
	}

	function customer_transaction_listing_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = array();
		$initial = 0;

		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_customer_transaction_listing_filter = get_session_filter('report_customer_transaction_listing_filter');
			if(empty($report_customer_transaction_listing_filter)) $initial = 1;

			$post_data['is_postback'] = $report_customer_transaction_listing_filter['is_postback'] ?? 0;
			$post_data['txt_search'] = $report_customer_transaction_listing_filter['txt_search'] ?? '';
			$post_data['txt_customer_no'] = $report_customer_transaction_listing_filter['txt_customer_no'] ?? '';
		}

		$data['is_postback']		= $post_data['is_postback'];
		$data['txt_search'] 		= $post_data['txt_search'];
		$data['txt_customer_no']	= $post_data['txt_customer_no'];

		$session_array = array(
			'is_postback' => $data['is_postback'],
			'txt_search' => $data['txt_search'],
			'txt_customer_no' => $data['txt_customer_no'],
		);
		if(!$initial) set_session_filter('report_customer_transaction_listing_filter', $session_array);

		$data['page_title'] 		= 'Customer Transaction Listing';	
		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_acc_status 			= $_SESSION['acc_status'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];		
		$query_where 				= '';
		$filter_text 				= 'Search : ' . $data['txt_search'];

		$customer = array(	"customer_no" => '',
							"name" => '',
							"login_username" => '',
							"package_name" => '',
						);

		$transaction = array();

		if ( empty($data['txt_customer_no']) ) {
			$data['txt_customer_no'] = $data['txt_search'];
		}

		$get_customer_by_cust_no = $this->report_model->get_customer_by_cust_no($data['txt_customer_no']);
		
		//~ if ($query->num_rows() > 0)
		if ($get_customer_by_cust_no['num_rows'] > 0)
		{	
			$row 						= $get_customer_by_cust_no['row_array']; //$query->row_array();
			$customer['customer_no'] 	= $row['customer_no'];
			$customer['name'] 			= $row['name'];
			$customer['login_username'] = $row['login_username'];
			$customer['package_name'] 	= $row['package_name'];
			
			$get_bill_by_cust_no = $this->report_model->get_bill_by_cust_no($data['txt_customer_no']);
			$balance 	= 0;
			
			//~ foreach ($query->result_array() as $row)
			foreach ($get_bill_by_cust_no as $row)
			{
				if ($row['doc_type'] == 'bill')
				{
					//$balance += $row['charge'];
					$balance = $row['balance'];
				}
				else 
				{
					$balance -= $row['payment'];
				}
				$transaction[] = array(
								'tranx_date' => $row['tranx_date'],
								'bill_due_date' => $row['bill_due_date'],
								'tranx_no' => $row['tranx_no'],
								'charge' => $row['charge'],
								'payment' => $row['payment'],
								'balance' => $balance,
								'remark' => $row['remark'],
								'doc_type' => $row['doc_type'],
								);
			}
		}

		$data_row['customer'] 		= $customer;
		$data_row['transaction'] 	= $transaction;
		$data['data_row'] 			= $data_row;
		$data['filter_text'] 		= $filter_text;		
		$data['isprint'] = $this->input->post('isprint');

		$html = $this->parser->parse('report/customer_transaction_listing_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}

	function export_customer_transaction_listing()
	{
		$is_postback 				= $this->input->post('is_postback');		
		$txt_search 				= $this->input->post('txt_search');
		$txt_customer_no 			= $this->input->post('txt_customer_no');		
		$data['txt_search'] 		= $txt_search;
		$data['txt_customer_no'] 	= $txt_customer_no;		
		$data['page_title'] 		= 'Customer Transaction Listing';
		$data['form_action'] 		= base_url('report/customer_transaction_listing');		
		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_acc_status 			= $_SESSION['acc_status'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];		
		$query_where 				= '';
		$filter_text 				= 'Search : ' . $txt_search;

		$customer = array(	"customer_no" => '',
							"name" => '',
							"login_username" => '',
							"package_name" => '',
						);
						
		$transaction = array();
		//if ( !empty($is_postback) ) 
		//{
			if ( empty($txt_customer_no) ) {
				$txt_customer_no = $txt_search;
			}
			
			//~ $query_str = "SELECT c.customer_no, c.name, c.login_username, c.package_name " . 
						//~ "FROM customer c " .
						//~ "WHERE c.customer_no = '$txt_customer_no' ";
			//~ $query = $this->db->query($query_str);
			
			$get_customer_by_cust_no = $this->report_model->get_customer_by_cust_no($txt_customer_no);
			
			//~ if ($query->num_rows() > 0)
			if ($get_customer_by_cust_no['num_rows'] > 0)
			{
				$row 						= $get_customer_by_cust_no['row_array']; //$query->row_array();
				$customer['customer_no'] 	= $row['customer_no'];
				$customer['name'] 			= $row['name'];
				$customer['login_username'] = $row['login_username'];
				$customer['package_name'] 	= $row['package_name'];
				
				$get_bill_by_cust_no = $this->report_model->get_bill_by_cust_no($txt_customer_no);
				$balance 	= 0;
				
				//~ foreach ($query->result_array() as $row)
				foreach ($get_bill_by_cust_no as $row)
				{
					if ($row['doc_type'] == 'bill')
					{
						//$balance += $row['charge'];
						$balance = $row['balance'];
					}
					else 
					{
						$balance -= $row['payment'];
					}
					$transaction[] = array(
									'tranx_date' => $row['tranx_date'],
									'bill_due_date' => $row['bill_due_date'],
									'tranx_no' => $row['tranx_no'],
									'charge' => $row['charge'],
									'payment' => $row['payment'],
									'balance' => $balance,
									'remark' => $row['remark'],
									'doc_type' => $row['doc_type'],
									);
				}
			}
		//}
		
		$records  = "";
		$records .= "Date\tDoc No.\tBill Due Date\tCharge\tPayment\tRemark\t\n";

		
		$grand_total = 0;
		foreach ($transaction as $key => $val) {
			$records .= $val['tranx_date'] . "\t" ;
			$records .= $val['tranx_no']   . "\t";
			$records .= $val['bill_due_date'] . "\t";
			$records .= $val['charge'] . "\t";
			$records .= $val['payment'] . "\t";
			$records .= $val['remark'] . "\t\n";
		}

		$filename = "customer_transaction_listing.xls";
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);

		echo $records ; 
	}

	function not_yet_customers()
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['sel_status'] 		= $post_data['sel_status'];
			$data['order_by']			= $post_data['order_by'];
			$data['order_type']			= $post_data['order_type'];
		} else {
			$report_not_yet_customers_filter          = get_session_filter('report_not_yet_customers_filter');
			$data['sel_status'] = $report_not_yet_customers_filter['sel_status'] ?? 'all';
			$data['order_by'] = $report_not_yet_customers_filter['order_by'] ?? '';
			$data['order_type'] = $report_not_yet_customers_filter['order_type'] ?? '';
		}

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title'] = 'Not Yet Activated Customer';
		$data['form_action'] = base_url('report/not_yet_customers');
		
		$data['sel_statuslist'] = ['Registered', 'SO', 'Waiting Activation'];
				
		$data['msg'] 		= $this->msg;

		$row_html = $this->not_yet_customers_rows(1);
		$data['row_html']		= $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;
			$header_data = array();
			$header_data['title']		= 'not_yet_activated';
			$header_data['description']	= 'Not Yet Activated Customer Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/not_yet_customers',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/not_yet_customers.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/not_yet_customers.pdf',
				'Not Yet Activated Customer Report',
				'Attached herewith is the report for "Not Yet Activated Customers" sent from itelco system.',
				'[Not Yet Activated Customer Report]'
			);
		}	

		$data['isprint'] = 0;
		
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/not_yet_customers',$data);
		$this->load->view('templates/footer');
	}

	function not_yet_customers_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = array();
		$initial = 0;

		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_not_yet_customers_filter          = get_session_filter('report_not_yet_customers_filter');
			if(empty($report_not_yet_customers_filter)) $initial = 1;
			$post_data['is_postback'] = $report_not_yet_customers_filter['is_postback'] ?? 0;
			$post_data['sel_status'] = $report_not_yet_customers_filter['sel_status'] ?? 'all';
			$post_data['sel_installation'] = $report_not_yet_customers_filter['sel_installation'] ?? 'all';
			$post_data['order_by'] = $report_not_yet_customers_filter['order_by'] ?? '';
			$post_data['order_type'] = $report_not_yet_customers_filter['order_type'] ?? '';
		}

		$data['is_postback']		= $post_data['is_postback'];
		$data['sel_status'] 		= $post_data['sel_status'];
		$data['sel_installation'] 		= $post_data['sel_installation'];
		$data['order_by']			= $post_data['order_by'];
		$data['order_type']			= $post_data['order_type'];

		$session_array = array(
			'is_postback' => $data['is_postback'],
			'sel_status' => $data['sel_status'],
			'sel_installation' => $data['sel_installation'],
			'order_by' => $data['order_by'],
			'order_type' => $data['order_type'],
		);
		if(!$initial) set_session_filter('report_not_yet_customers_filter', $session_array);

		$result = $this->report_model->get_not_yet_activated_customers($data['sel_status'], $data['sel_installation']);

		$data_row = array();
		foreach ($result['result_array'] as $row)
		{
			switch($row['status_text']) {
				case 'Registered':
					$row['status_text'] = "<b class='green'>Registered</b>";
					break;
				case 'SO':
					$row['status_text'] = "<b class='orange'>SO</b>";
					break;
				case 'Waiting Activation':
					$row['status_text'] = "<b class='pink'>Waiting Activation</b>";
					break;
			}
			$data_row[] = $row;
		}

		$data['page_title'] = 'Not Yet Activated Customer Report';
		$data['data_row']	= $initial? [] : $data_row;
		$data['isprint'] = $this->input->post('isprint');

		$html = $this->parser->parse('report/not_yet_customers_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}
	}

	function export_not_yet_customers()
	{
		$is_postback  = $this->input->post('is_postback');
		$sel_status = $this->input->post('sel_status');
		$order_by    = $this->input->post('order_by');
		$order_type  = $this->input->post('order_type');

		$result = $this->report_model->get_not_yet_activated_customers($sel_status);

		$data_row = [];
		foreach ($result['result_array'] as $row) {
			$data_row[] = $row;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Not Yet Activated');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Not Yet Activated');

		$sheet->setCellValue('A1', 'Not Yet Activated Customer Report');
		$sheet->mergeCells('A1:H1');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		$row = 3;
		$headers = ['Name','IC.No','Company','Phone','Email','Status','Preferred Installation Date/Time'];
		$col = 'A';
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$col++;
		}
		$sheet->getStyle('A'.$row.':'.$col.$row)->getFont()->setBold(true);
		$row++;

		$grand_total = 0;
		foreach ($data_row as $key => $val) {
			$sheet->setCellValue('A'.$row, $val['name']);
			$sheet->setCellValueExplicit('B'.$row, $val['icno'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('C'.$row, $val['comp_name']);
			$sheet->setCellValueExplicit('D'.$row, $val['phone'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('E'.$row, $val['email']);
			$sheet->setCellValue('F'.$row, $val['status_text']);
			$sheet->setCellValueExplicit('G'.$row, $val['preferred_install_datetime'], PHPExcel_Cell_DataType::TYPE_STRING);
			$row++;
		}

		foreach (range('A','G') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'not_yet_activated_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}
	
    //Customer overdue report
	function customer_overdue()
	{

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['sel_category'] 		= $post_data['sel_category'];
			$data['showterminate']		= $post_data['showterminate'] ?? '0';
			$data['order_by']			= $post_data['order_by'];
			$data['order_type']			= $post_data['order_type'];
		} else {
			$report_customer_overdue_filter          = get_session_filter('report_customer_overdue_filter');
			$data['sel_category'] = $report_customer_overdue_filter['sel_category'] ?? 'all';
			$data['showterminate']					= $report_customer_overdue_filter['showterminate'] ?? '0';
			$data['order_by'] = $report_customer_overdue_filter['order_by'] ?? '';
			$data['order_type'] = $report_customer_overdue_filter['order_type'] ?? '';
		}

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title'] = 'Customer - Overdue';
		$data['form_action'] = base_url('report/customer_overdue');
		
		$data['sel_category_list'] = $this->common_model->get_category_list();
				
		$data['msg'] 		= $this->msg;

		$row_html = $this->customer_overdue_rows(1);
		$data['row_html']		= $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;
			$header_data = array();
			$header_data['title']		= 'customer_overdue';
			$header_data['description']	= 'Customer Overdue Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/customer_overdue',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/customer_overdue.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/customer_overdue.pdf',
				'Customer Overdue Report',
				'Attached herewith is the customer overdue report sent from itelco system.',
				'[Customer Overdue Report]'
			);

		}	

		$data['isprint'] = 0;
		
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/customer_overdue',$data);
		$this->load->view('templates/footer');
	}

	function customer_overdue_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = array();
		$initial = 0;

		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_customer_overdue_filter          = get_session_filter('report_customer_overdue_filter');
			if(empty($report_customer_overdue_filter)) $initial = 1;
			$post_data['is_postback'] = $report_customer_overdue_filter['is_postback'] ?? 0;
			$post_data['sel_category'] = $report_customer_overdue_filter['sel_category'] ?? 'all';
			$post_data['showterminate'] = $report_customer_overdue_filter['showterminate'] ?? '0';
			$post_data['order_by'] = $report_customer_overdue_filter['order_by'] ?? '';
			$post_data['order_type'] = $report_customer_overdue_filter['order_type'] ?? '';
		}

		$data['is_postback']		= $post_data['is_postback'];
		$data['sel_category'] 		= $post_data['sel_category'];
		$data['showterminate']		= $post_data['showterminate'] ?? '0';
		$data['order_by']			= $post_data['order_by'];
		$data['order_type']			= $post_data['order_type'];

		$session_array = array(
			'showterminate' => $data['showterminate'],
			'is_postback' => $data['is_postback'],
			'sel_category' => $data['sel_category'],
			'order_by' => $data['order_by'],
			'order_type' => $data['order_type'],
		);
		if(!$initial) set_session_filter('report_customer_overdue_filter', $session_array);

		$query_where = '';
		if ( !empty($data['sel_category']) && $data['sel_category'] !== 'all' ) {
			$query_where = "AND c.category = '".$this->db->escape_str($data['sel_category'])."' ";
		}

		if (!empty($data['showterminate'])) {
			$query_where .= " AND IFNULL(cs.status, 'P') IN ('A','T','S') ";
		} else {
			$query_where .= " AND IFNULL(cs.status, 'P') IN ('A','S') ";
		}

		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){
			$query_order .= " ORDER BY c.category, ".$this->db->escape_str($data['order_by'])." ". $this->db->escape_str($data['order_type']) ;
		}
		
		$result = $this->report_model->get_overdue_list($query_where,$query_order);

		$data_row = array();
		foreach ($result['result_array'] as $row)
		{
			$overdue = "";
			if (empty($row['last_payment_date'])) {
				$row['last_payment_date'] = $row['first_activate'];
			}
			if( $row['last_payment_date'] != '' ){
				$pay_date 	= new DateTime($row['last_payment_date']);
				$now 		= new DateTime();
				$interval 	= date_diff($pay_date, $now);
				
				if ( $interval->format('%y') > 0 ) 
				{
					$overdue .= $interval->format('%y') . ' Year(s) ';
				}
				if ($interval->format('%m') > 0 ) 
				{
					$overdue .= $interval->format('%m') . ' Month(s)';
				}				
			}
			$data_row[$row['category']][$row['customer_no']] = array(
				'customer_name' => $row['customer_name'],
				'bill_date' => $row['bill_date'],
				'bill_due_date' => $row['bill_due_date'],
				'last_payment_date' => date('Y-m-d', strtotime($row['last_payment_date'])),
				'overdue' => $overdue,
				'current_balance' => $row['updated_balance'],
				'first_activate' => $row['first_activate'],
				'latest_status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
			);
		}

		/*
		echo "<pre>";
		print_r($data_row);
		echo "</pre>";
		exit;
		*/

		$data['page_title'] = 'Customer - Overdue';
		$data['data_row']	= $initial? [] : $data_row;
		$data['isprint'] = $this->input->post('isprint');

		$sess_cust_category 		= $_SESSION['cust_category'];
		$data['cust_category'] = $sess_cust_category;

		$html = $this->parser->parse('report/customer_overdue_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}
	
	function customer_listing() 
	{

		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['sel_category'] 		= $post_data['sel_category'];
			$data['sel_status_date']	= $post_data['sel_status_date'];
			$data['sel_activated'] 		= $post_data['sel_activated'];
			$data['sel_building'] 		= $post_data['sel_building'];
			$data['sel_package'] 		= $post_data['sel_package'];
			$data['sel_dealer'] 		= $post_data['sel_dealer'];
			$data['txt_search'] 		= $post_data['txt_search'];
			$data['txt_customer_no']    = $post_data['txt_customer_no'];
			$data['date_from']			= $post_data['date_from'];
			$data['date_to']			= $post_data['date_to'];
			$data['order_by']			= $post_data['order_by'];
			$data['order_type']			= $post_data['order_type'];
		} else {
			$report_customer_listing_filter          = get_session_filter('report_customer_listing_filter');
			$data['sel_category'] = $report_customer_listing_filter['sel_category'] ?? 'r';
			$data['sel_status_date'] = $report_customer_listing_filter['sel_status_date'] ?? 'all';
			$data['sel_activated'] = $report_customer_listing_filter['sel_activated'] ?? 'all';
			$data['sel_building'] = $report_customer_listing_filter['sel_building'] ?? 'all';
			$data['sel_package'] = $report_customer_listing_filter['sel_package'] ?? 'all';
			$data['sel_dealer'] = $report_customer_listing_filter['sel_dealer'] ?? 'all';
			$data['txt_search'] = $report_customer_listing_filter['txt_search'] ?? '';
			$data['txt_customer_no'] = $report_customer_listing_filter['txt_customer_no'] ?? '';
			$data['date_from'] = $report_customer_listing_filter['date_from'] ?? '';
			$data['date_to'] = $report_customer_listing_filter['date_to'] ?? '';
			$data['order_by'] = $report_customer_listing_filter['order_by'] ?? '';
			$data['order_type'] = $report_customer_listing_filter['order_type'] ?? '';
		}

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Customer Listing';
		$data['form_action'] 		= base_url('report/customer_listing');
		
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_status_list'] 	= $this->common_model->get_acc_status_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list'] 	= $this->common_model->get_package_list();
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();
		
		$data['msg'] 			= $this->msg;

		$row_html = $this->customer_listing_rows(1);
		$data['row_html']		= $row_html;

		if (!empty($contact_list)) {

			$_POST['scode_str'] = date('YmdHi');
			$_POST['scode'] = md5($_POST['scode_str'].$this->e_key);
			$_POST['btSubmit'] = 'print';
			puppeteer_print_preview_forpost($this->config->item('base_url').'pdfapi/customer_listing_report', $this->config->item('upload_path').'/temp/pdf/customer_listing_report.pdf', json_encode($_POST), $this->config->item('proj_path'), $this->config->item('chrome_loc'));

			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btSubmit'] = 'print';

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/customer_listing_report.pdf',
				'Customer Listing Report',
				'Attached herewith is the customer listing report sent from itelco system.',
				'[Customer Listing Report]'
			);

			unset($_POST['btSubmit']);

		}
				
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('report/customer_listing',$data);
		$this->load->view('templates/footer');
		
	}

	function customer_listing_rows($returnOnly = 0)
	{

		//1. Get vars from either POST or Session
		$data = array();
		$post_data = array();
		$initial = 0;

		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_customer_listing_filter          = get_session_filter('report_customer_listing_filter');
			if(empty($report_customer_listing_filter)) $initial = 1;

			$post_data['is_postback'] = $report_customer_listing_filter['is_postback'] ?? 0;
			$post_data['sel_category'] = $report_customer_listing_filter['sel_category'] ?? 'r';
			$post_data['sel_status_date'] = $report_customer_listing_filter['sel_status_date'] ?? 'all';
			$post_data['sel_activated'] = $report_customer_listing_filter['sel_activated'] ?? 'all';
			$post_data['sel_building'] = $report_customer_listing_filter['sel_building'] ?? 'all';
			$post_data['sel_package'] = $report_customer_listing_filter['sel_package'] ?? 'all';
			$post_data['sel_dealer'] = $report_customer_listing_filter['sel_dealer'] ?? 'all';
			$post_data['txt_search'] = $report_customer_listing_filter['txt_search'] ?? '';
			$post_data['txt_customer_no'] = $report_customer_listing_filter['txt_customer_no'] ?? '';
			$post_data['date_from'] = $report_customer_listing_filter['date_from'] ?? '';
			$post_data['date_to'] = $report_customer_listing_filter['date_to'] ?? '';
			$post_data['order_by'] = $report_customer_listing_filter['order_by'] ?? '';
			$post_data['order_type'] = $report_customer_listing_filter['order_type'] ?? '';
		}
		
		//2 Put Vars into $data array for view 
		$data['is_postback']		= $post_data['is_postback'];
		$data['sel_category'] 		= $post_data['sel_category'];
		$data['sel_status_date']	= $post_data['sel_status_date'];
		$data['sel_activated'] 		= $post_data['sel_activated'];
		$data['sel_building'] 		= $post_data['sel_building'];
		$data['sel_package'] 		= $post_data['sel_package'];
		$data['sel_dealer'] 		= $post_data['sel_dealer'];
		$data['txt_search'] 		= $post_data['txt_search'];
		$data['txt_customer_no']    = $post_data['txt_customer_no'];
		$data['date_from']			= $post_data['date_from'];
		$data['date_to']			= $post_data['date_to'];
		$data['order_by']			= $post_data['order_by'];
		$data['order_type']			= $post_data['order_type'];

		//3. Put vars into session so can maintain report search
		$session_array = array(
			'is_postback' => $data['is_postback'],
			'sel_category' => $data['sel_category'],
			'sel_status_date' => $data['sel_status_date'],
			'sel_activated' => $data['sel_activated'],
			'sel_building' => $data['sel_building'],
			'sel_package' => $data['sel_package'],
			'sel_dealer' => $data['sel_dealer'],
			'txt_search' => $data['txt_search'],
			'txt_customer_no' => $data['txt_customer_no'],
			'date_from' => $data['date_from'],
			'date_to' => $data['date_to'],
			'order_by' => $data['order_by'],
			'order_type' => $data['order_type'],
		);
		if (!$initial) set_session_filter('report_customer_listing_filter', $session_array);
		//4. Construct the SQL, and filter_text

		$customer = array();

		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_acc_status			= $_SESSION['acc_status'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];

		$filter_text 				= '';
		$query_where 				= "AND c.category = '".$this->db->escape_str($data['sel_category'])."' ";
		$filter_text 				.= 'Category : ' . $sess_cust_category[$data['sel_category']];
		if (!empty($data['txt_search'])) {
			$filter_text 				.= '<br />Search : ' . $data['txt_search'];
		}

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		if( $data['date_from'] != '' )
			$query_where .= $data['sel_status_date'] === 'F' 
							? " AND csa.transact_date >= '".( date('Y-m-d', strtotime($this->db->escape_str($data['date_from']))) )."' "
							: " AND cs.transact_date >= '".( date('Y-m-d', strtotime($this->db->escape_str($data['date_from']))) )."' ";
		if( $data['date_to'] != '' )

			$query_where .= $data['sel_status_date'] === 'F' 
							? " AND csa.transact_date <= '".( date('Y-m-d', strtotime($this->db->escape_str($data['date_to']))) )."' "
							: " AND cs.transact_date <= '".( date('Y-m-d', strtotime($this->db->escape_str($data['date_to']))) )."' ";

		if($data['sel_status_date'] != 'all' && $data['sel_status_date'] != 'F' && !empty($data['sel_status_date'])){
			$query_where .= " AND cs.status = '".$this->db->escape_str($data['sel_status_date'])."' ";
			$filter_text .= '<br />Status : ' . (isset($this->account_status[$data['sel_status_date']])?$this->account_status[$data['sel_status_date']]:'');
		}

		if ($data['sel_activated'] != 'all' && !empty($data['sel_activated'])) {
			if ($data['sel_activated'] == 'yes')
				$query_where = $query_where."AND cs.status IN ('A', 'S', 'T') ";
			else
				$query_where = $query_where."AND (cs.status IN ('P', 'C') OR cs.status IS NULL) ";
			$filter_text .= '<br>Activated : ' . $data['sel_activated'];
		}

		if ($data['sel_building'] != 'all' && !empty($data['sel_building'])) {
			$query_where = $query_where."AND c.building = '".$this->db->escape_str($data['sel_building'])."' ";
			$filter_text .= '<br>Building : ' . $sess_building[$data['sel_building']];
		}
		if ($data['sel_package'] != 'all' && !empty($data['sel_package'])) {
			$query_where = $query_where."AND c.package = '".$this->db->escape_str($data['sel_package'])."' ";
			//$filter_text .= '<br>Package : ' . $sess_package[$sel_package];
		}

		if ($data['sel_dealer'] != 'all' && !empty($data['sel_dealer'])) {
			$query_where = $query_where."AND c.dealer = '".$this->db->escape_str($data['sel_dealer'])."' ";
		}

		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = "";
			if ($data['order_by'] == 'status') {
				$sel_order_by = 'cs.status';
			} else if ($data['order_by'] == 'status_date') {
				$sel_order_by = 'cs.transact_date';
			} else if ($data['order_by'] == 'dealer') {
				$sel_order_by = 'c.dealer';
			} else {
				$sel_order_by = $data['order_by'];
			}

			$query_order .= " ORDER BY ".$sel_order_by." ". $this->db->escape_str($data['order_type']) ;
		}

		$result = $this->report_model->get_customer_join_building($data['txt_search'],$query_where, $query_order);

		//5. Process the result_array
		$no = 0;
		foreach ($result['result_array'] as $row) 
		{
			$no++;
			$customer[] = array(
					'no' => $no,
					'customer_no' => $row['customer_no'],
					'name' => $row['profile_name'],
					'login_username' => $row['login_username'],
					'first_activate' => $row['first_activate'],
					'status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
					'status_date' => (!empty($row['latest_status_date'])?date('Y-m-d', strtotime($row['latest_status_date'])):''),
					'category' => $sess_cust_category[$row['category']],
					'building' => $row['building_name'],
					'package_name' => $row['package_name'],
					'monthly_charge' => $row['monthly_charge'],
					'mobile_num' => $row['pic_mobile'],
					'email_1' => $row['pic_email_1'],
					'email_2' => $row['pic_email_2'],
					'dealer' => $row['dealer'],
					'inst_unit_no' => $row['inst_unit_no'],
					'inst_addr1' => $row['inst_addr1'],
					'inst_addr2' => $row['inst_addr2'],
					'inst_addr3' => $row['inst_addr3'],
					'inst_city' => $row['inst_city'],
					'inst_postcode' => $row['inst_postcode'],
					'inst_state' => isset( $row['inst_state'] ) ? (isset($sess_state[$row['inst_state']]) ? $sess_state[$row['inst_state']] : '') : '', 
					'package_start' => ( $row['package_changed_date'] == '0000-00-00' ? ( $row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date'] ) : $row['package_changed_date'] ),
			);
		}
		$data['data_row'] 		= $initial?[]:$customer;
		$data['filter_text'] 	= $initial?'':$filter_text;
		$data['page_title'] 		= 'Customer Listing';

		//6. Display view
		$html = $this->parser->parse('report/customer_listing_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}
	
	function print_customer_listing() 
	{
		$is_postback 	= $this->input->post('is_postback');		
		$sel_category 	= $this->input->post('sel_category');
		$sel_status 	= $this->input->post('sel_status');
		$sel_status_date= $this->input->post('sel_status_date');
		$sel_activated 	= $this->input->post('sel_activated');
		$sel_building 	= $this->input->post('sel_building');
		$sel_package 	= $this->input->post('sel_package');
		$sel_dealer 	= $this->input->post('sel_dealer');
		$txt_search 	= $this->input->post('txt_search');
		$txt_customer_no= $this->input->post('txt_customer_no');
		$date_from		= $this->input->post('date_from');
		$date_to		= $this->input->post('date_to');
		$order_by		= $this->input->post('order_by');
		$order_type		= $this->input->post('order_type');
		
		if (empty($sel_category)) $sel_category = 'r';
		
		$data['sel_category'] 		= $sel_category;
		$data['sel_status'] 		= $sel_status;
		$data['sel_status_date'] 	= $sel_status_date;
		$data['sel_activated'] 		= $sel_activated;
		$data['sel_building'] 		= $sel_building;
		$data['sel_package'] 		= $sel_package;
		$data['sel_dealer'] 		= $sel_dealer;
		$data['txt_search'] 		= $txt_search;
		$data['txt_customer_no']    = $txt_customer_no;
		$data['date_from']			= $date_from;
		$data['date_to']			= $date_to;
		$data['page_title'] 		= 'Customer Listing';
		$data['form_action'] 		= base_url('report/customer_listing');
		$data['order_by']			= $order_by;
		$data['order_type']			= $order_type;
		
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_status_list'] 	= $this->common_model->get_acc_status_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list'] 	= $this->common_model->get_package_list();
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();
		
		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_acc_status			= $_SESSION['acc_status'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];
		
		$query_where 				= "AND c.category = '$sel_category' ";
		$filter_text 				= 'Category : ' . $sess_cust_category[$sel_category];

		if($sel_status != 'all' && !empty($sel_status) && $sel_status != 'signup' && $sel_status != 'activated' ){
			$query_where = $query_where." AND c.status = '".$this->db->escape_str($sel_status)."' ";
			$filter_text .= '<br />Status : ' . $sess_acc_status[$sel_status];
		}
		
		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}
		
		//if($sel_status_date != 'all' && !empty($sel_status_date)){
			/*$date_type = '';
			if( $sel_status_date == 'suspended' )
				$date_type = 'suspended_date';
			elseif( $sel_status_date == 'terminated' )
				$date_type = 'terminated_date';
			elseif( $sel_status_date == 'signup' )
				$date_type = 'signup_date';
			elseif( $sel_status_date == 'activated' )
				$date_type = 'activated_date';
			
			if( $date_type != '' ){
				if( $date_from != '' )
					$query_where .= " AND c.".$date_type." >= '".( date('Y-m-d', strtotime($date_from)) )."' ";
				if( $date_to != '' )
					$query_where .= " AND c.".$date_type." <= '".( date('Y-m-d', strtotime($date_to)) )."' ";
			}*/

			//if( $date_type != '' ){
				if( $date_from != '' )
					$query_where .= " AND cs.transact_date >= '".( date('Y-m-d', strtotime($this->db->escape_str($date_from))) )."' ";
				if( $date_to != '' )
					$query_where .= " AND cs.transact_date <= '".( date('Y-m-d', strtotime($this->db->escape_str($date_to))) )."' ";
			//}

		//}

				if($sel_status_date != 'all' && !empty($sel_status_date)){
					$query_where .= " AND cs.status = '".$this->db->escape_str($sel_status_date)."' ";
				}


		if ($sel_activated != 'all' && !empty($sel_activated)) {
			if ($sel_activated == 'yes')
				$query_where = $query_where."AND cs.status IN ('A', 'S', 'T') ";
			else
				$query_where = $query_where."AND (cs.status IN ('P', 'C') OR cs.status IS NULL) ";
			$filter_text .= '<br>Activated : ' . $sel_activated;
		}
		if ($sel_building != 'all' && !empty($sel_building)) {
			$query_where = $query_where."AND c.building = '".$this->db->escape_str($sel_building)."' ";
			$filter_text .= '<br>Building : ' . $sess_building[$sel_building];
		}
		if ($sel_package != 'all' && !empty($sel_package)) {
			$query_where = $query_where."AND c.package = '".$this->db->escape_str($sel_package)."' ";
		}
		if ($sel_dealer != 'all' && !empty($sel_dealer)) {
			$query_where = $query_where."AND c.dealer = '".$this->db->escape_str($sel_dealer)."' ";
		}

		$customer = array();
		//if ( !empty($is_postback) ) 
		//{

			$query_order =  "";
			if( $order_by != "" && $order_type != "" ){

				$sel_order_by = "";
				if ($order_by == 'status') {
					$sel_order_by = 'cs.status';
				} else if ($order_by == 'status_date') {
					$sel_order_by = 'cs.transact_date';
				} else if ($order_by == 'dealer') {
					$sel_order_by = 'c.dealer';
				} else {
					$sel_order_by = $order_by;
				}

				$query_order .= " ORDER BY ".$sel_order_by." ". $this->db->escape_str($order_type) ;
			}
			
			$result = $this->report_model->get_customer_join_building($txt_search,$query_where, $query_order);	
			$no = 0;
			//~ foreach ($query->result_array() as $row) 			
			foreach ($result['result_array'] as $row) 
			{
				$no++;
				$customer[] = array(
						'no' => $no,
						'customer_no' => $row['customer_no'],
						'name' => $row['profile_name'],
						'login_username' => $row['login_username'],
						'gender' => strtoupper($row['gender']),
						'status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
						'status_date' => (!empty($row['latest_status_date'])?date('Y-m-d', strtotime($row['latest_status_date'])):''),
						'category' => $sess_cust_category[$row['category']],
						'building' => $row['building_name'],
						'package_name' => $row['package_name'],
						'monthly_charge' => $row['monthly_charge'],
						'mobile_num' => $row['pic_mobile'],
						'email_1' => $row['pic_email_1'],
						'email_2' => $row['pic_email_2'],
						'dealer' => $row['dealer'],
						'inst_unit_no' => $row['inst_unit_no'],
						'inst_addr1' => $row['inst_addr1'],
						'inst_addr2' => $row['inst_addr2'],
						'inst_addr3' => $row['inst_addr3'],
						'inst_city' => $row['inst_city'],
						'inst_postcode' => $row['inst_postcode'],
						'inst_state' => isset( $row['inst_state'] ) ? (isset($sess_state[$row['inst_state']]) ? $sess_state[$row['inst_state']] : '') : '', 
						'package_start' => ( $row['package_changed_date'] == '0000-00-00' ? ( $row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date'] ) : $row['package_changed_date'] ),
				);
				
			}
		//}
		
		$data['data_row'] 		= $customer;
		$data['filter_text'] 	= $filter_text;				
		$data['msg'] 			= $this->msg;	

		//can consider refactor into file path so its faster
		$data['logo_1'] = $this->config->item('logo_img');	
		$data['logo_2'] = $this->config->item('logo_img_2');
		$data['proj_name'] = $this->config->item('proj_name');
		$data['logo_1_width'] = $this->config->item('logo_img_width');
		$data['logo_2_width'] = $this->config->item('logo_img_2_width');
		
		$this->load->view('templates/header', $this->vars);
		//$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('report/customer_listing_print',$data);
		$this->load->view('templates/footer');
		
	}

	public function terminated_acc( $mode='' )
	{
		$post_data = array();
		if($this->input->post()) {
			$post_data = $this->input->post();
			$data['sel_category'] 		= $post_data['sel_category'];
			$data['sel_building'] 		= $post_data['sel_building'];
			$data['sel_package'] 		= $post_data['sel_package'];
			$data['sel_dealer'] 		= $post_data['sel_dealer'];
			$data['txt_search'] 		= $post_data['txt_search'];
			$data['txt_customer_no']    = $post_data['txt_customer_no'];
			$data['date_from']			= $post_data['date_from'];
			$data['date_to']			= $post_data['date_to'];
			$data['order_by']			= $post_data['order_by'];
			$data['order_type']			= $post_data['order_type'];
		} else {
			$report_terminated_acc_filter = get_session_filter('report_terminated_acc_filter');
			$data['sel_category'] 		= $report_terminated_acc_filter['sel_category'] ?? 'r';
			$data['sel_building'] 		= $report_terminated_acc_filter['sel_building'] ?? 'all';
			$data['sel_package'] 		= $report_terminated_acc_filter['sel_package'] ?? 'all';
			$data['sel_dealer'] 		= $report_terminated_acc_filter['sel_dealer'] ?? 'all';
			$data['txt_search'] 		= $report_terminated_acc_filter['txt_search'] ?? '';
			$data['txt_customer_no']    = $report_terminated_acc_filter['txt_customer_no'] ?? '';
			$data['date_from']			= $report_terminated_acc_filter['date_from'] ?? '';
			$data['date_to']			= $report_terminated_acc_filter['date_to'] ?? '';
			$data['order_by']			= $report_terminated_acc_filter['order_by'] ?? '';
			$data['order_type']			= $report_terminated_acc_filter['order_type'] ?? '';
		}

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['print'] 				= $mode;
		$data['page_title'] 		= 'Terminated Accounts';
		$data['form_action'] 		= base_url('report/terminated_acc');
		
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list'] 	= $this->common_model->get_package_list();
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();

		$data['msg'] 			= $this->msg;
		$row_html = $this->terminated_acc_rows(1,$mode);
		$data['row_html']	= $row_html;

		if (!empty($contact_list)) {

			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';
			$data['print'] = 'print';

			$header_data = array();
			$header_data['title']		= 'terminated_accounts_report';
			$header_data['description']	= 'Terminated Accounts Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/terminated_acc',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/terminated_acc.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/terminated_acc.pdf',
				'Terminated Accounts Report',
				'Attached herewith is the terminated accounts report sent from itelco system.',
				'[Terminated Accounts Report]'
			);

			unset($_POST['btFilter']);

		}
				
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/terminated_acc',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}

	}

	public function terminated_acc_rows($returnOnly = 0, $mode='') 
	{
		$data = array();
		$post_data = array();
		$initial = 0;

		if($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_terminated_acc_filter = get_session_filter('report_terminated_acc_filter');
			if(empty($report_terminated_acc_filter)) $initial = 1;

			$post_data['is_postback'] 		= $report_terminated_acc_filter['is_postback'] ?? 0;
			$post_data['sel_category'] 		= $report_terminated_acc_filter['sel_category'] ?? 'r';
			$post_data['sel_building'] 		= $report_terminated_acc_filter['sel_building'] ?? 'all';
			$post_data['sel_package'] 		= $report_terminated_acc_filter['sel_package'] ?? 'all';
			$post_data['sel_dealer'] 		= $report_terminated_acc_filter['sel_dealer'] ?? 'all';
			$post_data['txt_search'] 		= $report_terminated_acc_filter['txt_search'] ?? '';
			$post_data['txt_customer_no']    = $report_terminated_acc_filter['txt_customer_no'] ?? '';
			$post_data['date_from']			= $report_terminated_acc_filter['date_from'] ?? '';
			$post_data['date_to']			= $report_terminated_acc_filter['date_to'] ?? '';
			$post_data['filter_text'] 		= $report_terminated_acc_filter['filter_text'] ?? '';
			$post_data['order_by']			= $report_terminated_acc_filter['order_by'] ?? '';
			$post_data['order_type']		= $report_terminated_acc_filter['order_type'] ?? '';
		}

		$data['is_postback'] 		= $post_data['is_postback'];
		$data['sel_category'] 		= $post_data['sel_category'];
		$data['sel_building'] 		= $post_data['sel_building'];
		$data['sel_package'] 		= $post_data['sel_package'];
		$data['sel_dealer'] 		= $post_data['sel_dealer'];
		$data['txt_search'] 		= $post_data['txt_search'];
		$data['txt_customer_no']    = $post_data['txt_customer_no'];
		$data['date_from']			= $post_data['date_from'];
		$data['date_to']			= $post_data['date_to'];
		$data['order_by']			= $post_data['order_by'];
		$data['order_type']			= $post_data['order_type'];

		$session_array = array(
			'is_postback' => $data['is_postback'],
			'sel_category' => $data['sel_category'],
			'sel_building' => $data['sel_building'],
			'sel_package' => $data['sel_package'],
			'sel_dealer' => $data['sel_dealer'],
			'txt_search' => $data['txt_search'],
			'txt_customer_no' => $data['txt_customer_no'],
			'date_from' => $data['date_from'],
			'date_to' => $data['date_to'],
			'order_by' => $data['order_by'],
			'order_type' => $data['order_type']
		);
		if(!$initial) set_session_filter('report_terminated_acc_filter', $session_array);

		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];

		$filter_text				= '';
		$filter_text 				.= 'Search : ' . $data['txt_search'];
		$query_where 				= " AND cs.status = 'T' AND c.category = '" . $data['sel_category'] . "' ";
		$filter_text 				.= '<br>Category : ' . $sess_cust_category[$data['sel_category']];
		
		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		if( $data['date_from'] != '' )
			$query_where .= " AND cs.transact_date >= '".( date('Y-m-d', strtotime($data['date_from'])) )."' ";
		if( $data['date_to'] != '' )
			$query_where .= " AND cs.transact_date <= '".( date('Y-m-d', strtotime($data['date_to'])) )."' ";

		if ($data['sel_building'] != 'all' && !empty($data['sel_building'])) {
			$query_where = $query_where."AND c.building = '".$data['sel_building']."' ";
			$filter_text .= '<br>Building : ' . $sess_building[$data['sel_building']];
		}
		if ($data['sel_package'] != 'all' && !empty($data['sel_package'])) {
			$query_where = $query_where."AND c.package = '".$data['sel_package']."' ";
		}

		if ($data['sel_dealer'] != 'all' && !empty($data['sel_dealer'])) {
			$query_where = $query_where."AND c.dealer = '".$data['sel_dealer']."' ";
		}

		$customer = array();

		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = "";
			if ($data['order_by'] == 'status') {
				$sel_order_by = 'cs.status';
			} else if ($data['order_by'] == 'status_date') {
				$sel_order_by = 'cs.transact_date';
			} else if ($data['order_by'] == 'activated_status_date') {
				$sel_order_by = 'csa.transact_date';
			} else {
				$sel_order_by = $data['order_by'];
			}

			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}
		
		$result = $this->report_model->get_customer_join_building($data['txt_search'],$query_where, $query_order);

		$no = 0;		
		foreach ($result['result_array'] as $row) 
		{
			$no++;
			$customer[] = array(
					'no' => $no,
					'customer_no' => $row['customer_no'],
					'name' => $row['profile_name'],
					'login_username' => $row['login_username'],
					'gender' => strtoupper($row['gender']),
					'status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
					'status_date' => (!empty($row['latest_status_date'])?date('Y-m-d', strtotime($row['latest_status_date'])):''),
					'activated_date' => (!empty($row['first_activate'])?date('Y-m-d', strtotime($row['first_activate'])):''),
					'category' => $sess_cust_category[$row['category']],
					'building' => $row['building_name'],
					'package_name' => $row['package_name'],
					'monthly_charge' => $row['monthly_charge'],
					'mobile_num' => $row['pic_mobile'],
					'email_1' => $row['pic_email_1'],
					'email_2' => $row['pic_email_2'],
					'dealer' => $row['dealer'],
					'inst_unit_no' => $row['inst_unit_no'],
					'inst_addr1' => $row['inst_addr1'],
					'inst_addr2' => $row['inst_addr2'],
					'inst_city' => $row['inst_city'],
					'inst_postcode' => $row['inst_postcode'],
					'inst_state' => isset( $row['inst_state'] ) ? (isset($sess_state[$row['inst_state']]) ? $sess_state[$row['inst_state']] : '') : '', 
					'package_start' => ( $row['package_changed_date'] == '0000-00-00' ? ( $row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date'] ) : $row['package_changed_date'] ),
			);
		}

		$data['data_row'] 		= $initial? [] : $customer;
		$data['filter_text'] 	= $initial? '' : $filter_text;
		$data['page_title'] = 'Terminated Accounts';
		$data['print'] = $mode;

		$html = $this->parser->parse('report/terminated_acc_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_terminated_acc()
	{
		$sel_category  = $this->input->post('sel_category');
		$sel_building  = $this->input->post('sel_building');
		$sel_package   = $this->input->post('sel_package');
		$sel_dealer    = $this->input->post('sel_dealer');
		$txt_search    = $this->input->post('txt_search');
		$txt_customer_no = $this->input->post('txt_customer_no');
		$date_from     = $this->input->post('date_from');
		$date_to       = $this->input->post('date_to');
		$order_by      = $this->input->post('order_by');
		$order_type    = $this->input->post('order_type');

		$sess_cust_category = $_SESSION['cust_category'];
		$sess_state         = $_SESSION['state'];

		$query_where = " AND cs.status = 'T' AND c.category = '".$this->db->escape_str($sel_category)."' ";
		if ($date_from != '') $query_where .= " AND cs.transact_date >= '".date('Y-m-d', strtotime($date_from))."' ";
		if ($date_to != '') $query_where .= " AND cs.transact_date <= '".date('Y-m-d', strtotime($date_to))."' ";
		if ($sel_building != 'all' && !empty($sel_building)) $query_where .= " AND c.building = '".$this->db->escape_str($sel_building)."' ";
		if ($sel_package != 'all' && !empty($sel_package)) $query_where .= " AND c.package = '".$this->db->escape_str($sel_package)."' ";
		if ($sel_dealer != 'all' && !empty($sel_dealer)) $query_where .= " AND c.dealer = '".$this->db->escape_str($sel_dealer)."' ";

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$sel_order_by = ($order_by == 'status') ? 'cs.status' : (($order_by == 'status_date') ? 'cs.transact_date' : (($order_by == 'activated_status_date') ? 'csa.transact_date' : $order_by));
			$query_order = " ORDER BY ".$sel_order_by." ".$order_type;
		}

		$result = $this->report_model->get_customer_join_building($txt_search, $query_where, $query_order);

		$customers = [];
		$no = 1;
		foreach ($result['result_array'] as $row) {
			$customers[] = [
				'no'             => $no,
				'customer_no'    => $row['customer_no'],
				'name'           => $row['profile_name'],
				'login_username' => $row['login_username'],
				'gender'         => strtoupper($row['gender']),
				'status'         => isset($this->account_status[$row['latest_status']]) ? $this->account_status[$row['latest_status']] : '',
				'status_date'    => !empty($row['latest_status_date']) ? date('Y-m-d', strtotime($row['latest_status_date'])) : '',
				'activated_date' => !empty($row['activated_date']) ? date('Y-m-d', strtotime($row['activated_date'])) : '',
				'category'       => $sess_cust_category[$row['category']] ?? '',
				'building'       => $row['building_name'],
				'package_name'   => $row['package_name'],
				'monthly_charge' => $row['monthly_charge'],
				'mobile_num'     => $row['pic_mobile'],
				'email_1'        => $row['pic_email_1'],
				'email_2'        => $row['pic_email_2'],
				'dealer'         => $row['dealer'] ?? '',
				'inst_unit_no'   => $row['inst_unit_no'],
				'inst_addr1'     => $row['inst_addr1'],
				'inst_addr2'     => $row['inst_addr2'],
				'inst_city'      => $row['inst_city'],
				'inst_postcode'  => $row['inst_postcode'],
				'inst_state'     => isset($row['inst_state']) ? ($sess_state[$row['inst_state']] ?? '') : '',
				'package_start'  => ($row['package_changed_date'] == '0000-00-00' ? ($row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date']) : $row['package_changed_date'])
			];
			$no++;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Terminated Accounts');

		$headers = [
			'#','Customer No','Name','Login','Gender','Mobile','Email','Activated','Terminated','Dealer','Package','Inst. Addr'
		];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($customers as $c) {
			$emails = $c['email_1'];
			if (!empty($c['email_2'])) $emails .= ';'.$c['email_2'];

			$address = ($c['building'] ? $c['building'].', ' : '') . $c['inst_unit_no'].', '.$c['inst_addr1'].', '.$c['inst_addr2'].', '.$c['inst_city'].' '.$c['inst_postcode'].', '.$c['inst_state'];

			$sheet->setCellValue('A'.$row, $c['no']);
			$sheet->setCellValue('B'.$row, $c['customer_no']);
			$sheet->setCellValue('C'.$row, $c['name']);
			$sheet->setCellValue('D'.$row, $c['login_username']);
			$sheet->setCellValue('E'.$row, $c['gender']);
			$sheet->setCellValue('F'.$row, $c['mobile_num']);
			$sheet->setCellValue('G'.$row, $emails);
			$sheet->setCellValue('H'.$row, $c['activated_date']);
			$sheet->setCellValue('I'.$row, $c['status_date']);
			$sheet->setCellValue('J'.$row, $c['dealer']);
			$sheet->setCellValue('K'.$row, $c['package_name']);
			$sheet->setCellValue('L'.$row, $address);
			$row++;
		}

		foreach (range('A','L') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'terminated_accounts_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}


	public function contract_acc( $mode='' )
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'r',
			'sel_building' => 'all',
			'sel_package' => 'all',
			'sel_dealer' => 'all',
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_contract_acc_filter', $default_data, $post_data);

		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Contract Accounts';
		$data['form_action'] 		= base_url('report/contract_acc');
		
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list'] 	= $this->common_model->get_package_list();
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();

		$data['msg'] 			= $this->msg;
		$row_html = $this->contract_acc_row(1);
		$data['row_html']	= $row_html;

		if (!empty($contact_list)) {

			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';
			$data['print'] = 'print';

			$header_data = array();
			$header_data['title']		= 'contracts_accounts_report';
			$header_data['description']	= 'Contract Accounts Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/contract_acc',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/contract_acc.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/contract_acc.pdf',
				'Contracts Accounts Report',
				'Attached herewith is the contracts accounts report sent from itelco system.',
				'[Contracts Accounts Report]'
			);

			unset($_POST['btFilter']);

		}

		$data['print'] = $mode;
				
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/contract_acc',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
	}

	public function contract_acc_row($returnOnly = 0, $mode = '') 
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'r',
			'sel_building' => 'all',
			'sel_package' => 'all',
			'sel_dealer' => 'all',
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_contract_acc_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];
		$filter_text				= '';
		$filter_text 				= 'Search : ' . $data['txt_search'];
		$query_where 				= " AND csa.transact_date IS NOT NULL AND c.contract_month > 0 AND c.category = '".$data['sel_category']."' ";
		$filter_text 				.= '<br>Category : ' . $sess_cust_category[$data['sel_category']];

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		if( $data['date_from'] != '' )
			$query_where .= " AND csa.transact_date >= '".( date('Y-m-d', strtotime($data['date_from'])) )."' ";
		if( $data['date_to'] != '' )
			$query_where .= " AND DATE_ADD(csa.transact_date, INTERVAL c.contract_month MONTH) <= '".( date('Y-m-d', strtotime($data['date_to'])) )."' ";

		if ($data['sel_building'] != 'all' && !empty($data['sel_building'])) {
			$query_where = $query_where."AND c.building = '".$data['sel_building']."' ";
			$filter_text .= '<br>Building : ' . $sess_building[$data['sel_building']];
		}
		if ($data['sel_package'] != 'all' && !empty($data['sel_package'])) {
			$query_where = $query_where."AND c.package = '".$data['sel_package']."' ";
		}

		if ($data['sel_package'] != 'all' && !empty($data['sel_package'])) {
			$query_where = $query_where."AND c.package = '".$data['sel_package']."' ";
		}

		if ($data['sel_dealer'] != 'all' && !empty($data['sel_dealer'])) {
			$query_where = $query_where."AND c.dealer = '".$data['sel_dealer']."' ";
		}

		$customer = array();
		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = "";
			if ($data['order_by'] == 'status') {
				$sel_order_by = 'cs.status';
			} else if ($data['order_by'] == 'status_date') {
				$sel_order_by = 'cs.transact_date';
			} else if ($data['order_by'] == 'activated_status_date') {
				$sel_order_by = 'csa.transact_date';
			} else if ($data['order_by'] == 'contract_date') {
				$sel_order_by = 'csa.transact_date';
			} else {
				$sel_order_by = $data['order_by'];
			}

			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}
		
		$result = $this->report_model->get_customer_join_building($data['txt_search'],$query_where, $query_order);	
		$no = 0;
		//~ foreach ($query->result_array() as $row) 			
		foreach ($result['result_array'] as $row) 
		{

			//unbilled months
			if (!empty($row['contract_start_date']) && !empty($row['contract_end_date'])) {
				$date1 = date('Y-m-d');
				$date2 = $row['contract_end_date'];
				$d1=new DateTime($date2); 
				$d2=new DateTime($date1);                                  
				$Months = $d2->diff($d1); 
				$unbilled = (($Months->y) * 12) + ($Months->m);
			} else {
				$unbilled = '-';
			}

			$no++;
			$customer[] = array(
					'no' => $no,
					'customer_no' => $row['customer_no'],
					'name' => $row['profile_name'],
					'login_username' => $row['login_username'],
					'gender' => strtoupper($row['gender']),
					'status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
					'status_date' => (!empty($row['latest_status_date'])?date('Y-m-d', strtotime($row['latest_status_date'])):''),
					'activated_date' => (!empty($row['activated_date'])?date('Y-m-d', strtotime($row['activated_date'])):''),
					'category' => $sess_cust_category[$row['category']],
					'building' => $row['building_name'],
					'package_name' => $row['package_name'],
					'monthly_charge' => $row['monthly_charge'],
					'mobile_num' => $row['pic_mobile'],
					'email_1' => $row['pic_email_1'],
					'email_2' => $row['pic_email_2'],
					'dealer' => $row['dealer'],
					'inst_unit_no' => $row['inst_unit_no'],
					'inst_addr1' => $row['inst_addr1'],
					'inst_addr2' => $row['inst_addr2'],
					'inst_addr2' => $row['inst_addr3'],
					'inst_city' => $row['inst_city'],
					'inst_postcode' => $row['inst_postcode'],
					'inst_state' => isset( $row['inst_state'] ) ? (isset($sess_state[$row['inst_state']]) ? $sess_state[$row['inst_state']] : '') : '', 
					'package_start' => ( $row['package_changed_date'] == '0000-00-00' ? ( $row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date'] ) : $row['package_changed_date'] ),
					'contract_start' => ((!empty($row['contract_start_date'])) ? date('Y-m-d', strtotime($row['contract_start_date'])) : ''),
					'contract_end' => ((!empty($row['contract_end_date'])) ? date('Y-m-d', strtotime($row['contract_end_date'])) : ''),
					'unbilled_months' => $unbilled, 
			);
		}

		$data['data_row'] 		= $initial? [] : $customer;
		$data['filter_text'] 	= $initial? '' : $filter_text;
		$data['page_title'] 		= 'Contract Accounts';
		$data['print'] = $mode;

		$html = $this->parser->parse('report/contract_acc_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_contract_acc()
	{
		$sel_category   = $this->input->post('sel_category');
		$sel_building   = $this->input->post('sel_building');
		$sel_package    = $this->input->post('sel_package');
		$sel_dealer     = $this->input->post('sel_dealer');
		$txt_search     = $this->input->post('txt_search');
		$txt_customer_no= $this->input->post('txt_customer_no');
		$date_from      = $this->input->post('date_from');
		$date_to        = $this->input->post('date_to');
		$order_by       = $this->input->post('order_by');
		$order_type     = $this->input->post('order_type');

		$sess_cust_category = $_SESSION['cust_category'];
		$sess_state         = $_SESSION['state'];

		$query_where = " AND csa.transact_date IS NOT NULL AND c.contract_month > 0 AND c.category = '".$this->db->escape_str($sel_category)."' ";
		if ($date_from != '') $query_where .= " AND csa.transact_date >= '".date('Y-m-d', strtotime($date_from))."' ";
		if ($date_to != '') $query_where .= " AND DATE_ADD(csa.transact_date, INTERVAL c.contract_month MONTH) <= '".date('Y-m-d', strtotime($date_to))."' ";
		if ($sel_building != 'all' && !empty($sel_building)) $query_where .= " AND c.building = '".$this->db->escape_str($sel_building)."' ";
		if ($sel_package != 'all' && !empty($sel_package)) $query_where .= " AND c.package = '".$this->db->escape_str($sel_package)."' ";
		if ($sel_dealer != 'all' && !empty($sel_dealer)) $query_where .= " AND c.dealer = '".$this->db->escape_str($sel_dealer)."' ";

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$sel_order_by = in_array($order_by, ['status', 'status_date', 'activated_status_date', 'contract_date']) ? 
							(($order_by == 'status') ? 'cs.status' : 'csa.transact_date') : $order_by;
			$query_order = " ORDER BY ".$sel_order_by." ".$order_type;
		}

		$result = $this->report_model->get_customer_join_building($txt_search, $query_where, $query_order);

		$customers = [];
		$no = 1;
		foreach ($result['result_array'] as $row) {

			//unbilled months
			if (!empty($row['contract_start']) && !empty($row['contract_end'])) {
				$date1 = $row['contract_start'];
				$date2 = $row['contract_end'];
				$d1=new DateTime($date2); 
				$d2=new DateTime($date1);                                  
				$Months = $d2->diff($d1); 
				$unbilled = (($Months->y) * 12) + ($Months->m);
			} else {
				$unbilled = '-';
			}

			$customers[] = [
				'no'             => $no,
				'customer_no'    => $row['customer_no'],
				'name'           => $row['profile_name'],
				'login_username' => $row['login_username'],
				'gender'         => strtoupper($row['gender']),
				'status'         => $this->account_status[$row['latest_status']] ?? '',
				'status_date'    => !empty($row['latest_status_date']) ? date('Y-m-d', strtotime($row['latest_status_date'])) : '',
				'activated_date' => !empty($row['activated_date']) ? date('Y-m-d', strtotime($row['activated_date'])) : '',
				'category'       => $sess_cust_category[$row['category']] ?? '',
				'building'       => $row['building_name'],
				'package_name'   => $row['package_name'],
				'monthly_charge' => $row['monthly_charge'],
				'mobile_num'     => $row['pic_mobile'],
				'email_1'        => $row['pic_email_1'],
				'email_2'        => $row['pic_email_2'],
				'dealer'         => $row['dealer'] ?? '',
				'inst_unit_no'   => $row['inst_unit_no'],
				'inst_addr1'     => $row['inst_addr1'],
				'inst_addr2'     => $row['inst_addr2'] ?? '',
				'inst_addr3'     => $row['inst_addr3'] ?? '',
				'inst_city'      => $row['inst_city'],
				'inst_postcode'  => $row['inst_postcode'],
				'inst_state'     => $row['inst_state'] ? ($sess_state[$row['inst_state']] ?? '') : '',
				'package_start'  => ($row['package_changed_date'] == '0000-00-00' ? ($row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date']) : $row['package_changed_date']),
				'contract_start' => !empty($row['contract_start']) ? date('Y-m-d', strtotime($row['contract_start'])) : '',
				'contract_end'   => !empty($row['contract_end']) ? date('Y-m-d', strtotime($row['contract_end'])) : '',
				'unbilled_months' => $unbilled, 
			];
			$no++;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Contract Accounts');

		$headers = [
			'#','Customer No','Name','Login','Gender','Mobile','Email','Contract Start','Contract End','Agent','Package','Unbilled Months','Inst. Addr'
		];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($customers as $c) {
			$emails = $c['email_1'];
			if (!empty($c['email_2'])) $emails .= ';'.$c['email_2'];

			$address = '';
			if (!empty($c['building']) || !empty($c['inst_unit_no'])) {
				$address .= trim($c['building'].' '.$c['inst_unit_no']).', ';
			}
			$address .= $c['inst_addr1'] ? $c['inst_addr1'].', ' : '';
			$address .= $c['inst_addr2'] ? $c['inst_addr2'].', ' : '';
			$address .= $c['inst_addr3'] ? $c['inst_addr3'].', ' : '';
			$address .= $c['inst_city'] ? $c['inst_city'].', ' : '';
			$address .= $c['inst_postcode'] ? $c['inst_postcode'].', ' : '';
			$address .= $c['inst_state'] ?? '';

			$sheet->setCellValueExplicit('A'.$row, $c['no'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('B'.$row, $c['customer_no'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('C'.$row, $c['name']);
			$sheet->setCellValue('D'.$row, $c['login_username']);
			$sheet->setCellValue('E'.$row, $c['gender']);
			$sheet->setCellValueExplicit('F'.$row, $c['mobile_num'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('G'.$row, $emails);
			$sheet->setCellValue('H'.$row, $c['contract_start']);
			$sheet->setCellValue('I'.$row, $c['contract_end']);
			$sheet->setCellValue('J'.$row, $c['dealer']);
			$sheet->setCellValue('K'.$row, $c['package_name']);
			$sheet->setCellValueExplicit('L'.$row, $c['unbilled_months'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('M'.$row, $address);
			$row++;
		}

		foreach (range('A','M') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'contract_accounts_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}


	public function expiry_no_renewal_report( $mode='' )
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => '',
			'sel_category' => 'r',
			'sel_building' => 'all',
			'sel_package' => 'all',
			'sel_dealer' => 'all',
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_expiry_no_renewal_report_filter', $default_data, $post_data);

		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title'] 		= 'Expiry Contract Without Renewal';
		$data['form_action'] 		= base_url('report/expiry_no_renewal_report');
		
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
		$data['sel_package_list'] 	= $this->common_model->get_package_list();
		$data['sel_dealer_list'] 	= $this->common_model->get_dealer_list();

		$row_html = $this->expiry_no_renewal_report_rows(1, $mode);
		$data['row_html'] = $row_html ; 
		$data['msg'] = $this->msg;

		if (!empty($contact_list)) {

			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';
			$data['print'] = 'print';

			$header_data = array();
			$header_data['title']		= 'expiry_no_renewal_report';
			$header_data['description']	= 'Expired Contract Without Renewal Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/expiry_no_renewal_report',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/expiry_no_renewal_report.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/expiry_no_renewal_report.pdf',
				'Expired Contract Without Renewal Report',
				'Attached herewith is the expired contract without renewal report sent from itelco system.',
				'[Expiry Contract Without Renewal Report]'
			);

			unset($_POST['btFilter']);

		}

		$data['print'] = $mode;
				
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/expiry_no_renewal_report',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
	}

	public function expiry_no_renewal_report_rows($returnOnly = 0, $mode = '') 
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => '',
			'sel_category' => 'r',
			'sel_building' => 'all',
			'sel_package' => 'all',
			'sel_dealer' => 'all',
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_expiry_no_renewal_report_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];
		$filter_text 				= 'Search : ' . $data['txt_search'];
		$query_where 				= " AND csa.transact_date IS NOT NULL AND c.contract_month > 0 AND cs.status = 'T' AND c.category = '".$data['sel_category']."' ";
		$filter_text 				.= '<br>Category : ' . $sess_cust_category[$data['sel_category']];
		
		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		if( $data['date_from'] != '' )
			$query_where .= " AND e.contract_start_date >= '".( date('Y-m-d', strtotime($data['date_from'])) )."' ";
		if( $data['date_to'] != '' )
			$query_where .= " AND e.contract_end_date <= '".( date('Y-m-d', strtotime($data['date_to'])) )."' ";

		if ($data['sel_building'] != 'all' && !empty($data['sel_building'])) {
			$query_where = $query_where."AND c.building = '".$data['sel_building']."' ";
			$filter_text .= '<br>Building : ' . $sess_building[$data['sel_building']];
		}
		if ($data['sel_package'] != 'all' && !empty($data['sel_package'])) {
			$query_where = $query_where."AND c.package = '".$data['sel_package']."' ";
		}

		if ($data['sel_package'] != 'all' && !empty($data['sel_package'])) {
			$query_where = $query_where."AND c.package = '".$data['sel_package']."' ";
		}

		if ($data['sel_dealer'] != 'all' && !empty($data['sel_dealer'])) {
			$query_where = $query_where."AND c.dealer = '".$data['sel_dealer']."' ";
		}

		$customer = array();
		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = "";
			if ($data['order_by'] == 'status') {
				$sel_order_by = 'cs.status';
			} else if ($data['order_by'] == 'status_date') {
				$sel_order_by = 'cs.transact_date';
			} else if ($data['order_by'] == 'activated_status_date') {
				$sel_order_by = 'csa.transact_date';
			} else if ($data['order_by'] == 'contract_date') {
				$sel_order_by = 'csa.transact_date';
			} else {
				$sel_order_by = $data['order_by'];
			}

			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}
		
		$result = $this->report_model->get_customer_join_building($data['txt_search'],$query_where, $query_order);	
		$no = 0;
		//~ foreach ($query->result_array() as $row) 			
		foreach ($result['result_array'] as $row) 
		{
			$no++;
			$customer[] = array(
					'no' => $no,
					'customer_no' => $row['customer_no'],
					'name' => $row['profile_name'],
					'login_username' => $row['login_username'],
					'gender' => strtoupper($row['gender']),
					'status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
					'status_date' => (!empty($row['latest_status_date'])?date('Y-m-d', strtotime($row['latest_status_date'])):''),
					'activated_date' => (!empty($row['activated_date'])?date('Y-m-d', strtotime($row['activated_date'])):''),
					'category' => $sess_cust_category[$row['category']],
					'building' => $row['building_name'],
					'package_name' => $row['package_name'],
					'monthly_charge' => $row['monthly_charge'],
					'mobile_num' => $row['pic_mobile'],
					'email_1' => $row['pic_email_1'],
					'email_2' => $row['pic_email_2'],
					'dealer' => $row['dealer'],
					'inst_addr1' => $row['inst_addr1'],
					'inst_addr2' => $row['inst_addr2'],
					'inst_city' => $row['inst_city'],
					'inst_postcode' => $row['inst_postcode'],
					'inst_state' => isset( $row['inst_state'] ) ? (isset($sess_state[$row['inst_state']]) ? $sess_state[$row['inst_state']] : '') : '', 
					'package_start' => ( $row['package_changed_date'] == '0000-00-00' ? ( $row['activated_date'] == '0000-00-00' ? '-' : $row['activated_date'] ) : $row['package_changed_date'] ),
					'contract_start' => ((!empty($row['contract_start_date'])) ? date('Y-m-d', strtotime($row['contract_start_date'])) : ''),
					'contract_end' => ((!empty($row['contract_end_date'])) ? date('Y-m-d', strtotime($row['contract_end_date'])) : ''),
			);
		}

		$data['data_row'] = $initial? [] : $customer;
		$data['filter_text'] = $initial? '' : $filter_text;
		$data['page_title'] = 'Expiry Contract Without Renewal';
		$data['print'] = $mode;

		$html = $this->parser->parse('report/expiry_no_renewal_report_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_expiry_no_renewal_report()
	{
		$txt_search     = $this->input->post('txt_search');
		$txt_customer_no= $this->input->post('txt_customer_no');
		$sel_category   = $this->input->post('sel_category');
		$sel_building   = $this->input->post('sel_building');
		$sel_package    = $this->input->post('sel_package');
		$sel_dealer     = $this->input->post('sel_dealer');
		$date_from      = $this->input->post('date_from');
		$date_to        = $this->input->post('date_to');
		$order_by       = $this->input->post('order_by');
		$order_type     = $this->input->post('order_type');

		$sess_cust_category = $_SESSION['cust_category'];
		$sess_state         = $_SESSION['state'];

		$result = $this->report_model->get_customer_join_building($txt_search, "", "");

		$data = [];
		$no = 1;
		foreach ($result['result_array'] as $row) {
			$data[] = [
				'no'             => $no,
				'customer_no'    => $row['customer_no'],
				'name'           => $row['profile_name'],
				'login'          => $row['login_username'],
				'gender'         => strtoupper($row['gender']),
				'mobile'         => $row['pic_mobile'],
				'email'          => $row['pic_email_1'] . (!empty($row['pic_email_2']) ? ';' . $row['pic_email_2'] : ''),
				'contract_start' => !empty($row['contract_start']) ? date('Y-m-d', strtotime($row['contract_start'])) : '',
				'contract_end'   => !empty($row['contract_end']) ? date('Y-m-d', strtotime($row['contract_end'])) : '',
				'dealer'         => $row['dealer'] == 0 ? '' : $row['dealer'],
				'package'        => $row['package_name'],
				'inst_addr'      => trim(
					($row['building_name'] ?? '') . ', ' .
					($row['inst_unit_no'] ?? '') . ', ' .
					($row['inst_addr1'] ?? '') . ', ' .
					($row['inst_addr2'] ?? '') . ', ' .
					($row['inst_city'] ?? '') . ' ' .
					($row['inst_postcode'] ?? '') . ', ' .
					($row['inst_state'] ?? '')
				)
			];
			$no++;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Expiry No Renewal');

		$headers = ['#','Customer No','Name','Login','Gender','Mobile','Email','Contract Start','Contract End','Dealer','Package','Inst. Addr'];
		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($data as $r) {
			$sheet->setCellValue('A'.$row, $r['no']);
			$sheet->setCellValue('B'.$row, $r['customer_no']);
			$sheet->setCellValue('C'.$row, $r['name']);
			$sheet->setCellValue('D'.$row, $r['login']);
			$sheet->setCellValue('E'.$row, $r['gender']);
			$sheet->setCellValue('F'.$row, $r['mobile']);
			$sheet->setCellValue('G'.$row, $r['email']);
			$sheet->setCellValue('H'.$row, $r['contract_start']);
			$sheet->setCellValue('I'.$row, $r['contract_end']);
			$sheet->setCellValue('J'.$row, $r['dealer']);
			$sheet->setCellValue('K'.$row, $r['package']);
			$sheet->setCellValue('L'.$row, $r['inst_addr']);
			$row++;
		}

		foreach (range('A','L') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'expiry_no_renewal_report_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	public function einvoice_report()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];

		$return = get_filtered_ajax_data('report_einvoice_report_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'E-Invoice Report';
		$data['form_action'] 		= base_url('report/einvoice_report');
		$data['msg'] 			= $this->msg;
		$row_html = $this->einvoice_report_rows(1);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'einvoice_report';
			$header_data['description']	= 'E-Invoice Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/einvoice_report',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/einvoice_report.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/einvoice_report.pdf',
				'E-Invoice Report',
				'Attached herewith is the e-invoice report sent from itelco system.',
				'[E-Invoice Report]'
			);
		}

		$data['isprint'] = 0;
				
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/einvoice_report',$data);
		$this->load->view('templates/footer');

	}

	public function einvoice_report_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_einvoice_report_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$filter_text 				= 'Search : ' . $data['txt_search'];

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		$einvoice = array();

		$query_where = '';

		if( $data['date_from'] != '' )
			$query_where .= " AND b.bill_date >= '".( date('Y-m-d', strtotime($data['date_from'])) )."' ";
		if( $data['date_to'] != '' )
			$query_where .= " AND b.bill_date <= '".( date('Y-m-d', strtotime($data['date_to'])) )."' ";

		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = $order_by;

			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}
		
		$result = $this->report_model->get_customer_einvoice($data['txt_search'],$query_where, $query_order);	
		$no = 0;		
		foreach ($result['result_array'] as $row) 
		{
			$einvoice[] = array(
					'profile_name' => $row['profile_name'],
					'bill_no' => $row['bill_no'],
					'einvoice_uuid' => $row['einvoice_uuid'],
					'submitted_on' => $row['submitted_on'],
					'bill_period' => $row['bill_period'],
					'amount' => $row['amount'],
					'customer_no' => $row['customer_no'],
					'bill_date' => $row['bill_date'],
					'einvoice_status_text' => (isset($this->einvoice_status[$row['einvoice_status']]) ? $this->einvoice_status[$row['einvoice_status']] : 'Unsubmitted'),
					'einvoice_status' => $row['einvoice_status'], 
			);
		}

		$data['data_row'] 		= $initial? [] : $einvoice;
		$data['filter_text'] 	= $filter_text;
		$data['page_title'] 	= 'E-Invoice Report';
		$data['isprint'] = 0;

		$html = $this->parser->parse('report/einvoice_report_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_einvoice_report()
	{
		$is_postback     = $this->input->post('is_postback');		
		$txt_search      = $this->input->post('txt_search');
		$txt_customer_no = $this->input->post('txt_customer_no');
		$date_from       = $this->input->post('date_from');
		$date_to         = $this->input->post('date_to');
		$order_by        = $this->input->post('order_by');
		$order_type      = $this->input->post('order_type');

		$query_where = '';
		if ($date_from != '') $query_where .= " AND be.bill_date >= '" . date('Y-m-d', strtotime($date_from)) . "' ";
		if ($date_to != '') $query_where .= " AND be.bill_date <= '" . date('Y-m-d', strtotime($date_to)) . "' ";

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$query_order .= " ORDER BY " . $order_by . " " . $order_type;
		}

		$result = $this->report_model->get_customer_einvoice($txt_search, $query_where, $query_order);

		if ($result['num_rows'] > 0) {
			$this->load->helper('excel');
			$objPHPExcel = prepare_excel();
			$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Einvoice Report');

			$sheet = $objPHPExcel->setActiveSheetIndex(0);
			$sheet->setTitle('Einvoice Report');

			$sheet->setCellValue('A1', 'Einvoice Report');
			$sheet->setCellValue('A2', 'Period: ' . ($date_from ?? '-') . ' until ' . ($date_to ?? '-'));
			$sheet->mergeCells('A1:I1');
			$sheet->mergeCells('A2:I2');
			$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
			$sheet->getStyle('A2')->getFont()->setItalic(true);

			$irow = 4;
			$headers = ['#', 'Bill No', 'Bill Date', 'Account No', 'Name', 'Einvoice UUID', 'Einvoice Status', 'Submitted', 'Amount'];
			$colIndex = 0;
			foreach ($headers as $h) {
				$sheet->setCellValueByColumnAndRow($colIndex, $irow, $h);
				$colIndex++;
			}
			$sheet->getStyle('A' . $irow . ':I' . $irow)->getFont()->setBold(true);
			$irow++;

			$no = 1;
			foreach ($result['result_array'] as $row) {
				$colIndex = 0;
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $no);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['bill_no']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['bill_date']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['customer_no']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['profile_name']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['einvoice_uuid']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['einvoice_status'] ?? 'Unsubmitted');
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['submitted_on']);
				$sheet->setCellValueExplicitByColumnAndRow($colIndex++, $irow, $row['amount'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle('I' . $irow)->getNumberFormat()->setFormatCode('#,##0.00');
				$irow++;
				$no++;
			}

			$lastColIndex = count($headers) - 1;
			for ($i = 0; $i <= $lastColIndex; $i++) {
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
			}

			while (ob_get_level()) ob_end_clean();
			$filename = 'einvoice_report_' . date('Ymd_His') . '.xlsx';
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
		}
	}

	public function paynet_report()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_paynet_report_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Customer Payment Report (Paynet)';
		$data['form_action'] 		= base_url('report/paynet_report');

		$data['msg'] 			= $this->msg;
		$row_html = $this->paynet_report_rows(1);
		$data['row_html'] = $row_html;	
		

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'paynet_report';
			$header_data['description']	= 'Customer Payment Report (Paynet)' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/paynet_report',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/paynet_report.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/paynet_report.pdf',
				'Customer Payment Report (PayNet)',
				'Attached herewith is the customer payment report (PayNet) sent from itelco system.',
				'[Customer Payment Report (PayNet)]'
			);

		}

		$data['isprint'] = 0;
				
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/paynet_report',$data);
		$this->load->view('templates/footer');
	}

	public function paynet_report_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_paynet_report_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$filter_text 				= 'Search : ' . $data['txt_search'];

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		$rec = array();
		$query_where = '';

		if( $data['date_from'] != '' )
			$query_where .= " AND pr.created_at >= '".( date('Y-m-d 00:00:00', strtotime($data['date_from'])) )."' ";
		if( $data['date_to'] != '' )
			$query_where .= " AND pr.created_at <= '".( date('Y-m-d 23:59:59', strtotime($data['date_to'])) )."' ";

		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){
			$sel_order_by = $order_by;
			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}
		
		$result = $this->report_model->get_customer_payment($data['txt_search'],$query_where, $query_order);	
		$no = 0;		
		foreach ($result['result_array'] as $row) 
		{
			$rec[] = array(
					'bill_no' => $row['bill_no'],
					'bill_date' => $row['bill_date'],
					'bill_period' => $row['bill_period'],
					'amount' => $row['fpx_txnAmount'],
					'customer_no' => $row['customer_no'],
					'transaction_date' => $row['transaction_date'], 
					'response_code' => (isset($this->paynet_status[$row['fpx_debitAuthCode']]) ? $this->paynet_status[$row['fpx_debitAuthCode']] : 'Transaction Failed'),
					'paynet_id' => $row['fpx_sellerExOrderNo'],
					'bank' => $row['bank_displayname'], 
					'profile_name' => $row['profile_name'],
			);
		}

		$data['data_row'] 		= $initial? [] : $rec;
		$data['filter_text'] 	= $filter_text;
		$data['page_title'] 	= 'Customer Payment Report (Paynet)';
		$data['isprint'] = 0;

		$html = $this->parser->parse('report/paynet_report_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_paynet_report()
	{
		$is_postback     = $this->input->post('is_postback');		
		$txt_search      = $this->input->post('txt_search');
		$txt_customer_no = $this->input->post('txt_customer_no');
		$date_from       = $this->input->post('date_from');
		$date_to         = $this->input->post('date_to');
		$order_by        = $this->input->post('order_by');
		$order_type      = $this->input->post('order_type');

		$rec = [];

		$query_where = '';
		if ($date_from != '') $query_where .= " AND pr.created_at >= '" . date('Y-m-d 00:00:00', strtotime($date_from)) . "' ";
		if ($date_to != '') $query_where .= " AND pr.created_at <= '" . date('Y-m-d 23:59:59', strtotime($date_to)) . "' ";

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$query_order .= " ORDER BY " . $order_by . " " . $order_type;
		}

		$result = $this->report_model->get_customer_payment($txt_search, $query_where, $query_order);

		foreach ($result['result_array'] as $row) {
			$rec[] = [
				'bill_no'          => $row['bill_no'],
				'bill_date'        => $row['bill_date'],
				'bill_period'      => $row['bill_period'],
				'amount'           => $row['fpx_txnAmount'],
				'customer_no'      => $row['customer_no'],
				'transaction_date' => $row['transaction_date'], 
				'response_code'    => $this->paynet_status[$row['fpx_debitAuthCode']] ?? 'Transaction Failed',
				'paynet_id'        => $row['fpx_sellerExOrderNo'],
				'bank'             => $row['bank_displayname'], 
				'profile_name'     => $row['profile_name'],
			];
		}

		if ($result['num_rows'] > 0) {
			$this->load->helper('excel');
			$objPHPExcel = prepare_excel();
			$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Paynet Report');

			$sheet = $objPHPExcel->setActiveSheetIndex(0);
			$sheet->setTitle('Paynet Report');

			$sheet->setCellValue('A1', 'Paynet Report');
			$sheet->setCellValue('A2', 'Period: ' . ($date_from ?? '-') . ' until ' . ($date_to ?? '-'));
			$sheet->mergeCells('A1:J1');
			$sheet->mergeCells('A2:J2');
			$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
			$sheet->getStyle('A2')->getFont()->setItalic(true);

			$irow = 4;
			$headers = ['#', 'Bill No', 'Bill Date', 'Account No', 'Name', 'Paynet ID', 'Amount', 'Bank', 'Status', 'Date'];
			foreach ($headers as $colIndex => $h) {
				$sheet->setCellValueByColumnAndRow($colIndex, $irow, $h);
			}
			$sheet->getStyle('A' . $irow . ':J' . $irow)->getFont()->setBold(true);
			$irow++;

			$no = 1;
			foreach ($rec as $row) {
				$colIndex = 0;
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $no);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['bill_no']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['bill_date']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['customer_no']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['profile_name']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['paynet_id']);
				$sheet->setCellValueExplicitByColumnAndRow($colIndex++, $irow, $row['amount'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($colIndex - 1) . $irow)
					->getNumberFormat()->setFormatCode('#,##0.00');
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['bank']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['response_code']);
				$sheet->setCellValueByColumnAndRow($colIndex++, $irow, $row['transaction_date']);
				$irow++;
				$no++;
			}

			$lastColIndex = count($headers) - 1;
			for ($i = 0; $i <= $lastColIndex; $i++) {
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
			}

			while (ob_get_level()) ob_end_clean();
			$filename = 'paynet_report_' . date('Ymd_His') . '.xlsx';
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
		}
	}

	public function bill_reminder_report()
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_customer_no' => '',
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_bill_reminder_report_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Bill Reminder Report';
		$data['form_action'] 		= base_url('report/bill_reminder_report');
		$data['msg'] 			= $this->msg;
		$row_html = $this->bill_reminder_report_rows(1);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'bill_reminder_report';
			$header_data['description']	= 'Bill Reminder Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/bill_reminder_report',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/bill_reminder_report.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/bill_reminder_report.pdf',
				'Bill Reminder Report',
				'Attached herewith is the bill reminder report sent from itelco system.',
				'[Bill Reminder Report]'
			);
		}

		$data['isprint'] = 0;
				
		$this->vars['cssfiles'][] = '../css/theme/bootstrap-grid.css';

		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/bill_reminder_report',$data);
		$this->load->view('templates/footer');
	}

	public function bill_reminder_report_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_bill_reminder_report_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$filter_text 				= 'Search : ' . '';

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		$reminder = array();

		$query_where = '';
		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = "";

			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}

		$result = $this->report_model->get_reminder_report('',$data['date_from'],$data['date_to'],$query_order);	
		$no = 0;		
		foreach ($result['result_array'] as $row) 
		{
			//show list of bill, query by date, display customer name and account status
			//then reminder 1,2,3,4 whether sent

			$reminder[] = array(
					'profile_name' 				=> $row['profile_name'],
					'prev_bill_no' 				=> $row['prev_bill_no'],
					'payment_term' 				=> $row['payment_term'],
					'prev_bill_due_date'		=> $row['prev_bill_due_date'],
					'total_paid_within_term' 	=> $row['total_paid_within_term'],
					'prev_bill_balance' 		=> $row['prev_bill_balance'],
					'customer_no' 				=> $row['customer_no'],
					'bill_date' 				=> $row['bill_date'] ?? '-',
					'last_pay_date' 			=> $row['last_pay_date'] ?? '-',
					'rm1_date' 					=> $row['rm1_date'],
					'rm2_date' 					=> $row['rm2_date'],
					'rm3_date' 					=> $row['rm3_date'],
					'rm4_date' 					=> $row['rm4_date'],
					'rm1_tick' 					=> $row['rm1_tick'],
					'rm2_tick' 					=> $row['rm2_tick'],
					'rm3_tick' 					=> $row['rm3_tick'],
					'rm4_tick' 					=> $row['rm4_tick'],
			);
		}

		$data['page_title'] 	= 'Bill Reminder Report';
		$data['data_row'] 		= $initial? [] : $reminder;
		$data['filter_text'] 	= $filter_text;
		$data['isprint'] = 0;

		$html = $this->parser->parse('report/bill_reminder_report_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_bill_reminder_report()
	{
		$is_postback     = $this->input->post('is_postback');
		$txt_search      = $this->input->post('txt_search');
		$txt_customer_no = $this->input->post('txt_customer_no');
		$date_from       = $this->input->post('date_from');
		$date_to         = $this->input->post('date_to');
		$order_by        = $this->input->post('order_by');
		$order_type      = $this->input->post('order_type');

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$query_order = " ORDER BY {$order_by} {$order_type} ";
		}

		$result = $this->report_model->get_reminder_report($txt_search, $date_from, $date_to, $query_order);

		if ($result['num_rows'] <= 0) {
			return;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Bill Reminder Report');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Bill Reminder');

		$sheet->setCellValue('A1', 'Bill Reminder Report');
		$sheet->setCellValue('A2', 'Period: ' . ($date_from ?: '-') . ' until ' . ($date_to ?: '-'));
		$sheet->mergeCells('A1:O1');
		$sheet->mergeCells('A2:O2');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A2')->getFont()->setItalic(true);

		$headers = [
			'#','Account No','Name','Bill No','Bill Due','Last Paid','Amount',
			'Rem1','Rem1 Date','Rem2','Rem2 Date','Rem3','Rem3 Date','Rem4','Rem4 Date'
		];

		$rowIndex = 4;
		foreach ($headers as $i => $header) {
			$sheet->setCellValueByColumnAndRow($i, $rowIndex, $header);
		}
		$sheet->getStyle('A'.$rowIndex.':O'.$rowIndex)->getFont()->setBold(true);
		$rowIndex++;

		$no = 1;
		foreach ($result['result_array'] as $row) {
			$col = 0;
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $no);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['customer_no']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['profile_name']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['bill_no']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['bill_due_date']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['last_pay_date']);
			$sheet->setCellValueExplicitByColumnAndRow($col++, $rowIndex, $row['amount'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm1_tick'] == '1' ? 'X' : '-');
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm1_date']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm2_tick'] == '1' ? 'X' : '-');
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm2_date']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm3_tick'] == '1' ? 'X' : '-');
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm3_date']);
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm4_tick'] == '1' ? 'X' : '-');
			$sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row['rm4_date']);
			$rowIndex++;
			$no++;
		}

		$lastColIndex = count($headers) - 1;
		for ($i = 0; $i <= $lastColIndex; $i++) {
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'bill_reminder_report_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		$writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$writer->save('php://output');
		exit;
	}

	function ifca_table() {

		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_ifca_table_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'IFCA export';
		$data['form_action'] 		= base_url('report/ifca_table');
		$data['msg'] 			= $this->msg;
		$row_html = $this->ifca_table_rows(1);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'ifca_table';
			$header_data['description']	= 'IFCA Export' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/ifca_table',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/ifca_export.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/ifca_export.pdf',
				'IFCA Export',
				'Attached herewith is the IFCA export sent from itelco system.',
				'[IFCA Export]'
			);

		}

		$data['isprint'] = 0;
				
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/ifca_table',$data);
		$this->load->view('templates/footer');
	}

	function ifca_table_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_ifca_table_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$filter_text 				= '';

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		//get from setting
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'account_setting_code' ");
		$account_setting_code = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'dept_no' ");
		$dept_no = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'employee_no' ");
		$employee_no = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'ifca_currency_code' ");
		$ifca_currency_code = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'journal_type' ");
		$journal_type = $config_record[0]['val'] ?? '';

		//ledger
		$all_ledgers = $this->common_model->get_all_ledger_codes();

		//_debug_array($all_ledgers); exit;

		$bills = array();

		$result = $this->report_model->prepare_ifca_export($data['date_from'],$data['date_to']);	
		$no = 1;		
		foreach ($result as $row) 
		{
			//show list of bill, query by date, display customer name and account status
			//then reminder 1,2,3,4 whether sent

			$dr_account_code = '';
			$cr_account_code = '';

			$prefix = '';
			$desc = '';

			if ($row['tranx_type'] == 'bill') {
				//bill
				$dr_account_code = $all_ledgers['BILL'][$row['category']][$row['bill_type_id']]['DR'] ?? '';
				$cr_account_code = $all_ledgers['BILL'][$row['category']][$row['bill_type_id']]['CR'] ?? '';
				$prefix = 'B';
				$desc = $row['remark'].' '.$row['bill_type_name'].' Bill No:'.$row['inv_no'].' Customer No:'.$row['customer_no'];
				$desc = trim($desc);
			} else {
				//payment
				$dr_account_code = $all_ledgers['PAYMENT'][$row['category']][$row['payment_source']]['DR'] ?? '';
				$cr_account_code = $all_ledgers['PAYMENT'][$row['category']][$row['payment_source']]['CR'] ?? '';
				$prefix = 'P';
				$desc = $row['remark'].' '.$row['bill_type_name'].' Payment No:'.$row['inv_no'].' Customer No:'.$row['customer_no'];
				$desc = trim($desc);
			}

			//DR
			$bills[] = array(
				'virtual_voucher_no' => $no, 
				'account_setting_code' => $account_setting_code,
				'account_code' => $dr_account_code, 
				'journal_type' => $journal_type,
				'dept_no' => $dept_no,
				'emp_no' => $employee_no,
				'foreign_dr_amount' => $row['amount'],
				'foreign_cr_amount' => '',
				'currency' => $ifca_currency_code,
				'base_dr_amount' => $row['amount'], 
				'base_cr_amount' => '',
				'create_date' => $row['tranx_date'],
				'ref_no' => $prefix.$row['inv_no'],
				'description' => $desc,
			);

			//CR
			$bills[] = array(
				'virtual_voucher_no' => $no, 
				'account_setting_code' => $account_setting_code,
				'account_code' => $cr_account_code, 
				'journal_type' => $journal_type,
				'dept_no' => $dept_no,
				'emp_no' => $employee_no,
				'foreign_dr_amount' => '',
				'foreign_cr_amount' => $row['amount'],
				'currency' => $ifca_currency_code,
				'base_dr_amount' => '',
				'base_cr_amount' => $row['amount'], 
				'create_date' => $row['tranx_date'],
				'ref_no' => $prefix.$row['inv_no'],
				'description' => $desc,
			);

			$no++;
		}

		$data['page_title'] 	= 'IFCA export';
		$data['isprint'] = 0;
		$data['data_row'] 		= $initial? [] : $bills;
		$data['filter_text'] 	= $filter_text;

		$html = $this->parser->parse('report/ifca_table_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	function export_ifca_table() {
		$data = array();
		$is_postback 	= $this->input->post('is_postback');		
		$date_from		= $this->input->post('date_from');
		$date_to		= $this->input->post('date_to');
		$order_by		= $this->input->post('order_by');
		$order_type		= $this->input->post('order_type');

		//get from setting
		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'account_setting_code' ");
		$account_setting_code = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'dept_no' ");
		$dept_no = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'employee_no' ");
		$employee_no = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'ifca_currency_code' ");
		$ifca_currency_code = $config_record[0]['val'] ?? '';

		$config_record = $this->common_model->get_table('sys_config','*', "`category` = 'ifca' AND `key` = 'journal_type' ");
		$journal_type = $config_record[0]['val'] ?? '';

		//ledger
		$all_ledgers = $this->common_model->get_all_ledger_codes();

		$result = $this->report_model->prepare_ifca_export($date_from,$date_to);
		$no = 1;
		foreach ($result as $row) 
		{
			//show list of bill, query by date, display customer name and account status
			//then reminder 1,2,3,4 whether sent

			$dr_account_code = '';
			$cr_account_code = '';

			$prefix = '';
			$desc = '';

			if ($row['tranx_type'] == 'bill') {
				//bill
				$dr_account_code = $all_ledgers['BILL'][$row['category']][$row['bill_type_id']]['DR'] ?? '';
				$cr_account_code = $all_ledgers['BILL'][$row['category']][$row['bill_type_id']]['CR'] ?? '';
				$prefix = 'B';
				$desc = $row['remark'].' '.$row['bill_type_name'].' Bill No:'.$row['inv_no'].' Customer No:'.$row['customer_no'];
				$desc = trim($desc);
			} else {
				//payment
				$dr_account_code = $all_ledgers['PAYMENT'][$row['category']][$row['payment_source']]['DR'] ?? '';
				$cr_account_code = $all_ledgers['PAYMENT'][$row['category']][$row['payment_source']]['CR'] ?? '';
				$prefix = 'P';
				$desc = $row['remark'].' '.$row['bill_type_name'].' Payment No:'.$row['inv_no'].' Customer No:'.$row['customer_no'];
				$desc = trim($desc);
			}

			//DR
			$bills[] = array(
				'virtual_voucher_no' => $no, 
				'account_setting_code' => $account_setting_code,
				'account_code' => $dr_account_code, 
				'journal_type' => $journal_type,
				'dept_no' => $dept_no,
				'emp_no' => $employee_no,
				'foreign_dr_amount' => $row['amount'],
				'foreign_cr_amount' => '',
				'currency' => $ifca_currency_code,
				'base_dr_amount' => $row['amount'], 
				'base_cr_amount' => '',
				'create_date' => $row['tranx_date'],
				'ref_no' => $prefix.$row['inv_no'],
				'description' => $desc,
			);

			//CR
			$bills[] = array(
				'virtual_voucher_no' => $no, 
				'account_setting_code' => $account_setting_code,
				'account_code' => $cr_account_code, 
				'journal_type' => $journal_type,
				'dept_no' => $dept_no,
				'emp_no' => $employee_no,
				'foreign_dr_amount' => '',
				'foreign_cr_amount' => $row['amount'],
				'currency' => $ifca_currency_code,
				'base_dr_amount' => '',
				'base_cr_amount' => $row['amount'], 
				'create_date' => $row['tranx_date'],
				'ref_no' => $prefix.$row['inv_no'],
				'description' => $desc,
			);

			$no++;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();

		$objPHPExcel->getProperties()->setCreator('Itelco System')
									 ->setLastModifiedBy('Itelco System')
									 ->setTitle("XLSX IFCA Export")
									 ->setSubject("XLSX IFCA Export")
									 ->setDescription("IFAC Export")
									 ->setKeywords("office 2007 openxml itelco IFCA export")
									 ->setCategory("IFCA Export");

		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'VirtualVoucherNo') 
		            ->setCellValue('B1', 'AccountSettingCode')
		            ->setCellValue('C1', 'A/c Code') 
		            ->setCellValue('D1', 'Journal Type') 
		            ->setCellValue('E1', 'DeptNo') 
		            ->setCellValue('F1', 'EmployeeNo') 
		            ->setCellValue('G1', 'Foreign DR Amount') 
		            ->setCellValue('H1', 'Foreign CR Amount') 
		            ->setCellValue('I1', 'Currency')
		            ->setCellValue('J1', 'Base DR Amount')
		            ->setCellValue('K1', 'Base CR Amount')
		            ->setCellValue('L1', 'Create Date')
		            ->setCellValue('M1', 'Ref. No.') 
		            ->setCellValue('N1', 'Description');

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('Sheet1');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);

		$irow = 2;
        foreach ($bills ?? [] as $row) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$irow, strval($row['virtual_voucher_no']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$irow, $row['account_setting_code']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$irow, $row['account_code']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$irow, $row['journal_type']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$irow, $row['dept_no']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$irow, $row['emp_no']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$irow, strval($row['foreign_dr_amount']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$irow, strval($row['foreign_cr_amount']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$irow, $row['currency']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$irow, strval($row['base_dr_amount']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$irow, strval($row['base_cr_amount']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$irow, strval(date('n/j/Y')));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$irow, strval($row['ref_no']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$irow, strval($row['description']));

            $irow++;

        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0); 

        // Save Excel 2007 file
        #echo date('H:i:s') . " Write to Excel2007 format\n";
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter = prepare_writer($objPHPExcel);
        ob_end_clean();
        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="ifca_export'.(!empty($date_from)?'_'.$date_from:'').(!empty($date_to)?'_'.$date_to:'').'.xls"');
        $objWriter->save('php://output');

        exit;

		/*$records = "";
		$records .= "VirtualVoucherNo\tAccountSettingCode\tA/c Code\tJournal Type\tDeptNo\tEmployeeNo\tForeign DR Amount\tForeign CR Amount\tCurrency\tBase DR Amount\tBase CR Amount\tCreate Date\tRef. No.\tDescription\t\n";
		foreach ($bills as $row) 
		{
			
    		$records .= $row['virtual_voucher_no']."\t".$row['account_setting_code']."\t".$row['account_code']."\t".$row['journal_type']."\t".$row['dept_no']."\t".$row['emp_no']."\t".$row['foreign_dr_amount']."\t".$row['foreign_cr_amount']."\t".$row['currency']."\t".$row['base_dr_amount']."\t".$row['base_cr_amount']."\t".$row['create_date']."\t".$row['ref_no']."\t".$row['description']."\t\n";
			$no++;
		}
		
		$filename = "ifca_export.xls";
    	header('Content-type: application/ms-excel');
    	header('Content-Disposition: attachment; filename='.$filename);
		
		echo $records;*/

	}

	function expiry_contract_report() {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_expiry_contract_report_filter', $default_data, $post_data);

		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Contract Expiry Reminder Report';
		$data['form_action'] 		= base_url('report/expiry_contract_report');

		$data['msg'] 			= $this->msg;
		$row_html = $this->expiry_contract_report_rows(1);
		$data['row_html']	= $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'expiry_contract_report';
			$header_data['description']	= 'Contract Expiry Reminder Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/expiry_contract_report',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/expiry_contract_report.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/expiry_contract_report.pdf',
				'Contract Expiry Reminder Report',
				'Attached herewith is the contract expiry reminder report sent from itelco system.',
				'[Contract Expiry Reminder Report]'
			);

		}

		$data['isprint'] = 0;
				
		$this->load->view('templates/header', $this->vars);
		$this->parser->parse('report/expiry_contract_report',$data);
		$this->load->view('templates/footer');

	}

	function expiry_contract_report_rows($returnOnly = 0) 
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_expiry_contract_report_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$filter_text 				= '';

		if( $data['date_from'] != '' && $data['date_to'] != '' ){
			$filter_text .= '<br />From ' . $data['date_from'] . ' to ' . $data['date_to'] ;
		}elseif( $data['date_from'] == '' && $data['date_to'] != '' ){
			$filter_text .= '<br /> as of ' . $data['date_to'] ;
		}elseif( $data['date_from'] != '' && $data['date_to'] == '' ){
			$filter_text .= '<br /> since ' . $data['date_from'] ;
		}

		$reminder = array();
		$query_where = '';

		/*if( $date_from != '' )
			$query_where .= " AND b.bill_date >= '".( date('Y-m-d', strtotime($date_from)) )."' ";
		if( $date_to != '' )
			$query_where .= " AND b.bill_date <= '".( date('Y-m-d', strtotime($date_to)) )."' ";*/

		$query_order =  "";
		if( $data['order_by'] != "" && $data['order_type'] != "" ){

			$sel_order_by = "";

			$query_order .= " ORDER BY ".$sel_order_by." ". $data['order_type'] ;
		}

		$result = $this->report_model->get_contract_reminder_report('',$data['date_from'],$data['date_to'],$query_order);	
		$no = 0;		
		foreach ($result['result_array'] as $row) 
		{
			//show list of bill, query by date, display customer name and account status
			//then reminder 1,2,3,4 whether sent

			$reminder[] = array(
					'profile_name' => $row['profile_name'],
					'customer_no' => $row['customer_no'],
					'package_name' => $row['package_name'],
					'contract_month' => $row['contract_month'],
					'expiry_date' => (!empty($row['expiry_date']) ? date('Y-m-d', strtotime($row['expiry_date'])) : ''),
					'rm1_date' => $row['rm1_date'],
					'rm1_tick' => $row['rm1_tick'],
			);
		}

		$data['page_title'] 	= 'Contract Expiry Reminder Report';
		$data['data_row'] 		= $initial? [] : $reminder;
		$data['isprint'] 		= 0;

		$html = $this->parser->parse('report/expiry_contract_report_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_expiry_contract_report()
	{
		$txt_search     = $this->input->post('txt_search');
		$txt_customer_no= $this->input->post('txt_customer_no');
		$date_from      = $this->input->post('date_from');
		$date_to        = $this->input->post('date_to');
		$order_by       = $this->input->post('order_by');
		$order_type     = $this->input->post('order_type');

		$result = $this->report_model->get_contract_reminder_report($txt_search, $date_from, $date_to, '');

		$reminders = [];
		$no = 1;
		foreach ($result['result_array'] as $row) {
			$reminders[] = [
				'no'             => $no,
				'customer_no'    => $row['customer_no'],
				'profile_name'   => $row['profile_name'],
				'package_name'   => $row['package_name'],
				'contract_month' => $row['contract_month'],
				'expiry_date'    => !empty($row['expiry_date']) ? date('Y-m-d', strtotime($row['expiry_date'])) : '',
				'reminder_sent'  => ($row['rm1_tick'] == '1') ? 'X' : '-',
				'reminder_date'  => $row['rm1_date'] ?? ''
			];
			$no++;
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Expiry Contract Report');

		$headers = ['#','Account No','Name','Package','Contract Months','Expiry','Reminder Sent','Reminder Date'];
		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($reminders as $r) {
			$sheet->setCellValue('A'.$row, $r['no']);
			$sheet->setCellValue('B'.$row, $r['customer_no']);
			$sheet->setCellValue('C'.$row, $r['profile_name']);
			$sheet->setCellValue('D'.$row, $r['package_name']);
			$sheet->setCellValue('E'.$row, $r['contract_month']);
			$sheet->setCellValue('F'.$row, $r['expiry_date']);
			$sheet->setCellValue('G'.$row, $r['reminder_sent']);
			$sheet->setCellValue('H'.$row, $r['reminder_date']);
			$row++;
		}

		foreach (range('A','H') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'expiry_contract_report_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}


	public function send_list()
	{
		$data = array();
		$data['page_title'] = 'Send List';
		$this->parser->parse('report/send_list',$data);
	}
		
    public function index()
    {
		$data['page_title'] = 'Report Listing';
		$data['msg'] 		= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('report/index',$data);
		$this->load->view('templates/footer');
	}
	
	function testing(){
		$query = "	SELECT customer_no ,
						CASE WHEN total_payment - 30DAYS > 0 THEN 0 ELSE 30DAYS - total_payment END AS month1 ,
						CASE WHEN total_payment - 60DAYS > 0 THEN 0 ELSE 60DAYS - total_payment END AS month2 ,
						CASE WHEN total_payment - 90DAYS > 0 THEN 0 ELSE 90DAYS - total_payment END AS month3 ,
						CASE WHEN total_payment - ALLDAYS > 0 THEN 0 ELSE ALLDAYS - total_payment END AS total_outstanding 
						
					FROM (
					
						SELECT b.customer_no , SUM(payment_received) AS total_payment ,
							SUM( CASE WHEN DATEDIFF( '2016-04-01' , bill_date ) <= 30 THEN amount ELSE 0 END ) AS 30DAYS ,
							SUM( CASE WHEN DATEDIFF( '2016-04-01' , bill_date ) BETWEEN 31 AND 60 THEN amount ELSE 0 END ) AS 60DAYS ,
							SUM( CASE WHEN DATEDIFF( '2016-04-01' , bill_date ) BETWEEN 61 AND 90 THEN amount ELSE 0 END ) AS 90DAYS ,
							SUM( amount ) AS ALLDAYS 
						FROM bill b
						INNER JOIN customer c ON b.customer_no = c.customer_no 
						WHERE b.bill_date <= '".date('Y-m-t')."' AND c.category = 'r' 
						GROUP BY b.customer_no 
						HAVING total_payment < ALLDAYS 
					
					) z 
				";
		echo $query ; 
	}

	function customer_statement_listing() 
	{
		$post_data = array();
		if($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_customer_statement_listing_filter = get_session_filter('report_customer_statement_listing_filter');
			$post_data = $report_customer_statement_listing_filter;
		}
		
		$page_item_no 			= empty($post_data['page_item_no'])? 0 : $post_data['page_item_no'];
		$data['txt_search']		= $post_data['txt_search'] ?? '';
		$data['sel_category'] 	= empty($post_data['sel_category'])? 'all' : $post_data['sel_category'];
		$data['sel_status'] 	= empty($post_data['sel_status'])? 'r' : $post_data['sel_status'];
		$data['sel_bill_by'] 	= $post_data['sel_bill_by'] ?? '';
		$data['txt_date_from']  = !empty($post_data['txt_date_from'])? date('Y-m-d' , strtotime($post_data['txt_date_from'])) : date('Y-m-01');
		$data['txt_date_to']    = !empty($post_data['txt_date_to'])?  date('Y-m-d' , strtotime($post_data['txt_date_to'])) : date('Y-m-d');
		
		$row_html = $this->customer_statement_listing_rows(1);
		$data['row_html'] = $row_html;

		$data['page_title'] = 'Statement of Account';
		$data['form_action'] = base_url('report/customer_statement_listing');

		$data['sel_status_list'] = $this->common_model->get_acc_status_list();
		$data['sel_category_list'] = $this->common_model->get_category_list();

		$data['msg'] = $this->msg;
	
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('report/customer_statement_listing',$data);
		$this->load->view('templates/footer');
		
	}

	function customer_statement_listing_rows($returnOnly = 0)
	{
		$this->load->model('bill_model');

		$data = array();
		$post_data = array();
		$initial = 0;

		if($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$report_customer_statement_listing_filter = get_session_filter('report_customer_statement_listing_filter');
			if(empty($report_customer_statement_listing_filter)) $initial = 1;

			$post_data = $report_customer_statement_listing_filter;
		}

		$page_item_no 	= $post_data['page_item_no'] ?? '';
		$txt_search		= $post_data['txt_search'] ?? '';
		$sel_category 	= $post_data['sel_category'] ?? 'all';
		$sel_status 	= $post_data['sel_status'] ?? 'r';
		$sel_bill_by 	= $post_data['sel_bill_by'] ?? '';
		$txt_date_from  = $post_data['txt_date_from'] ?? '';
		$txt_date_to    = $post_data['txt_date_to'] ?? '';

		if ($page_item_no == '') $page_item_no = 0;
		if ( empty($sel_category)) $sel_category = 'all';
		if ( empty($sel_status)) $sel_status = 'r';

		if ( !empty($txt_date_from) ){
			$txt_date_from = date( 'Y-m-d' , strtotime( $txt_date_from ) );
		}else{
			$txt_date_from = date( 'Y-m-01' );
		}
		
		if ( !empty( $txt_date_to ) ){
			$txt_date_to = date( 'Y-m-d' , strtotime( $txt_date_to ) );
		}else{
			$txt_date_to = date( 'Y-m-d' );
		}

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_category' => $sel_category,
			'sel_status' => $sel_status,
			'sel_bill_by' => $sel_bill_by,
			'txt_date_from' => $txt_date_from,
			'txt_date_to' => $txt_date_to
		);
		if(!$initial) set_session_filter('report_customer_statement_listing_filter', $session_array);

		$query_where = '';
		if ($sel_category != 'all' && !empty($sel_category)) {
			$query_where = "AND c.category = '$sel_category' ";
		}
		if ($sel_status != 'all' && !empty($sel_status)) {
			$query_where = $query_where."AND c.status = '$sel_status' ";
		}
		
		$get_list 	= $this->report_model->get_statement_list($txt_search,$page_item_no,$query_where,$txt_date_from,$txt_date_to);
		$total_row 	= $get_list['total_row'];
		$row 		= $get_list['row'];

		foreach ($row as $key => $val) {
			$row[$key]['activated_date'] = date_toggle($row[$key]['activated_date'],$_SESSION['config']['date_format']);
			$row[$key]['last_bill_date'] = date_toggle($row[$key]['last_bill_date'],$_SESSION['config']['date_format']);
			
			$payments = $this->bill_model->get_unprocessed_payment_by_customer( $val['customer_no'] , $txt_date_to ) ;
			$total_unprocessed_payment = 0 ;
			foreach( $payments AS $payment ){
				$total_unprocessed_payment += $payment['amount'] ;
			}
			
			$last_payments = $this->bill_model->get_processed_payment_by_customer( $val['customer_no'] , $txt_date_to , $val['last_bill_no'] );
			$total_last_processed_payment = 0 ;
			foreach( $last_payments AS $payment ){
				$total_last_processed_payment += $payment['amount'] ;
			}
			
			$row[$key]['opening_balance'] = number_format( $val['opening_balance'] , 2 , "." , "," ) ;
			$row[$key]['balance'] = number_format( $val['balance'] - $total_unprocessed_payment - $total_last_processed_payment, 2 , ".", "," ) ; 
			
		}

		$data['pagination'] = paginationSettingsAjax('statement', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data'] = $initial? [] : $row;

		$data['txt_date_from'] = $txt_date_from;
		$data['txt_date_to'] = $txt_date_to;

		$html = $this->parser->parse('report/customer_statement_listing_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	function customer_statement($customer_no, $date_from, $date_to)
	{

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$statements = $this->report_model->get_customer_statement($customer_no, $date_from, $date_to);

		$header_data 				= $this->vars;
		$header_data['title']		= 'print preview';
		$header_data['description']	= 'to print bill statements';
		$content_data['opening_balance'] = $statements['opening_balance'];
		$content_data['date_from']  = $date_from;
		$content_data['date_to'] 	= $date_to;
		$content_data['details']	= (empty($statements['trans']))?'':$statements['trans'];

		//sort details in to pages
		$page = 1;
		$temp_trans = array();
		if( !empty( $content_data['details'] ) ){
			$row_cnt = 0;
			foreach( $content_data['details'] AS $dtl ){ 
				$temp_trans[$page][] = $dtl;
				$row_cnt++;
				if ($row_cnt >= 35) {
					$row_cnt = 0;
					$page++;
				}
			}
		}

		//if last page more than a certain amount, footer push to next page
		$footer_push = false;
		$last_page_rows = count($temp_trans[$page] ?? array());
		if ($last_page_rows >= 20) {
			$footer_push = true;
		}

		/*echo "<pre>";
		var_dump($temp_trans); 
		echo "</pre>";
		exit;*/

		$content_data['footer_push'] = $footer_push;
		$content_data['pages'] = $page;
		$content_data['trans_details'] = $temp_trans;

		$content_data['gst_reg_no']	= (empty($statements['gst_reg_no']))?'':$statements['gst_reg_no'];
		$data['msg'] 				= $this->msg;

		$this->load->model('customer_model');
		$cust_info  = $this->customer_model->get_customer($customer_no);

		$state_list = $_SESSION['state'];

		$content_data['customer_no'] = $cust_info['customer_no'] ;
		if( $cust_info['bill_addr1'] != '' ){
			$content_data['display_name'] 		= $cust_info['bill_name'];
			$content_data['display_addr1'] 		= $cust_info['bill_addr1'];
			$content_data['display_addr2'] 		= $cust_info['bill_addr2'];
			$content_data['display_city'] 		= $cust_info['bill_city'];
			$content_data['display_postcode'] 	= $cust_info['bill_postcode'];
			$content_data['display_state'] 		= (empty($state_list[$cust_info['bill_state']]))?'':$state_list[$cust_info['bill_state']];
		}elseif( $cust_info['inst_addr1'] != '' ){
			$content_data['display_name'] 		= $cust_info['name'];
			$content_data['display_addr1'] 		= $cust_info['inst_addr1'];
			$content_data['display_addr2'] 		= $cust_info['inst_addr2'];
			$content_data['display_city'] 		= $cust_info['inst_city'];
			$content_data['display_postcode'] 	= $cust_info['inst_postcode'];
			$content_data['display_state'] 		= (empty($state_list[$cust_info['inst_state']]))?'':$state_list[$cust_info['inst_state']];
		}

		$footer = $this->common_model->get_table('sys_config','*', "`category` = 'bills' AND `key` = 'bills_footer' ");
		$content_data['statement_footer'] = $footer[0]['val'];
		
		$comp = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_full_name' ");
		$content_data['company_full_name'] = $comp[0]['val'];

		$tax = $this->common_model->get_table('sys_config','*', "`category` = 'others' AND `key` = 'company_tax_number' ");
		$content_data['company_tax_number'] = $tax[0]['val'];
		
		$jompay = $this->common_model->get_table('sys_config','*', "`category` = 'jompay' AND `key` = 'jompay_biller_code' ");
		$content_data['jompay_biller_code'] = $jompay[0]['val'];

		$report_send = 0;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$content_data['isprint'] = 1;

			$header_data = array();
			$header_data['title']		= 'customer_statement_of_account';
			$header_data['description']	= 'Customer SOA' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/customer_statement',$content_data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			//$html = $this->parser->parse('report/test_dompdf',$content_data, true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/customer_soa.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/customer_soa.pdf',
				'Customer SOA',
				'Attached herewith is the customer statement of account sent from itelco system.',
				'[Customer SOA]'
			);

			$report_send = 1;

		}

		$menu_data = array();
		$menu_data['send_btn'] = 1;
		$menu_data['form_action'] = base_url('report/customer_statement/'.$customer_no.'/'.$date_from.'/'.$date_to);
		$menu_data['report_send'] = $report_send;
		
		$this->load->view('templates/print_header', $header_data);
		$this->load->view('templates/print_menu', $menu_data);
		$this->parser->parse('report/customer_statement', $content_data);
		$this->load->view('templates/print_footer', '');
		
	}

	function building_listing( $mode='' )
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_activated' => 'all',
			'sel_building' => 'all',
			'display_col' => array("p", "c", "a", "s", "t"),
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_building_listing_filter', $default_data, $post_data);

		$data = $return['data'];

		$data['print']	= $mode;

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title'] 		= 'Customer Report By Building';
		$data['form_action'] 		= base_url('report/building_listing');
		
		$data['sel_category_list'] 	= $this->common_model->get_category_list();
		$data['sel_status_list'] 	= $this->common_model->get_acc_status_list();
		$data['sel_building_list'] 	= $this->common_model->get_building_list();
					
		$data['msg'] 			= $this->msg;	

		$row_html = $this->building_listing_rows(1);
		$data['row_html']		= $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btSubmit'] = 'print';

			$header_data = array();
			$header_data['title']		= 'building_listing_report';
			$header_data['description']	= 'Building Listing Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/building_listing',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/building_listing_report.pdf', $pdf_data);

			//send pdf
			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/building_listing_report.pdf',
				'Building Listing Report',
				'Attached herewith is the building listing report sent from itelco system.',
				'[Building Listing Report]'
			);

			unset($_POST['btSubmit']);
		}	
		
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/building_listing',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
		
	}

	function building_listing_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_status' => 'all',
			'sel_activated' => 'all',
			'sel_building' => 'all',
			'display_col' => array("p", "c", "a", "s", "t"),
			'date_from' => '',
			'date_to' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_building_listing_filter', $default_data, $post_data);

		$data = $return['data'];
		$initial = $return['initial'];

		$data['page_title'] 		= 'Customer Report By Building';

		$data['print']	= '';

		$sess_cust_category 		= $_SESSION['cust_category'];
		$sess_acc_status			= $_SESSION['acc_status'];
		$sess_state 				= $_SESSION['state'];
		$sess_building 				= $_SESSION['building'];
		$query_where 				= "";
		$filter_text				= "";

		if( $data['sel_category'] != 'all' && !empty( $data['sel_category'] ) ){
			$query_where	= "AND category = '".$this->db->escape_str($data['sel_category'])."' ";
			$filter_text	.= "Category : " . $sess_cust_category[$data['sel_category']];
		}
		
		if( $data['date_from'] != '' )
			$query_where .= " AND cs.transact_date >= '".( date('Y-m-d', strtotime($this->db->escape_str($data['date_from']))) )."' ";
		if( $data['date_to'] != '' )
			$query_where .= " AND cs.transact_date <= '".( date('Y-m-d', strtotime($this->db->escape_str($data['date_to']))) )."' ";

		if($data['sel_status'] != 'all' && !empty($data['sel_status'])){
			$query_where .= " AND cs.status = '".$this->db->escape_str($data['sel_status'])."' ";
			$filter_text .= (isset($this->account_status[$data['sel_status']]) ? "<br>Status : " . $this->account_status[$data['sel_status']] : '');
		}


		if ($data['sel_activated'] != 'all' && !empty($data['sel_activated'])) {
			if ($data['sel_activated'] == 'yes')
				$query_where = $query_where."AND cs.status IN ('A', 'S', 'T') ";
			else
				$query_where = $query_where."AND cs.status IN ('P', 'C') ";
			$filter_text .= '<br>Activated : ' . $data['sel_activated'];
		}
		
		if ($data['sel_building'] != 'all' && !empty($data['sel_building'])) {
			$filter_text .= '<br>Building : ' . $sess_building[$data['sel_building']];
			$building_where = $data['sel_building'];
		}else{
			$building_where = '';
		}

		$this->load->model("building_model");
		$building_list = $this->common_model->get_building_list('building_no', $building_where);

		$data['grand_total_unit'] = 0;
		$data['grand_p'] = 0;
		$data['grand_c'] = 0;
		$data['grand_a'] = 0;
		$data['grand_s'] = 0;
		$data['grand_t'] = 0;
		$data['grand_total_customer'] = 0;
		
		foreach( $building_list AS $key => $list ){
			
			$results = $this->building_model->get_building_total_users( $list['building_no'], $query_where );
			
			$total_customer = 0;
			$total['P'] = 0;
			$total['C'] = 0;
			$total['A'] = 0;
			$total['S'] = 0;
			$total['T'] = 0;
			
			foreach( $results AS $result ){
				$total[$result['latest_status']] = $result['total_customer'];
				$total_customer = $total_customer + $result['total_customer'];
			}
			
			$building_list[$key]['p'] = $total['P'];
			$building_list[$key]['c'] = $total['C'];
			$building_list[$key]['a'] = $total['A'];
			$building_list[$key]['s'] = $total['S'];
			$building_list[$key]['t'] = $total['T'];
			$building_list[$key]['total_customer'] = $total_customer;
			
			$data['grand_total_unit'] += $list['total_unit'];
			$data['grand_p'] += $total['P'];
			$data['grand_c'] += $total['C'];
			$data['grand_a'] += $total['A'];
			$data['grand_s'] += $total['S'];
			$data['grand_t'] += $total['T'];
			$data['grand_total_customer'] += $total_customer;			
			
		}
		
		$data['row_data'] 		= $initial? [] : $building_list;

		$html = $this->parser->parse('report/building_listing_rows',$data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			//not return only, so generate the whole html
			echo $html;
		}

	}

	function ticket_listing( $mode='' ){
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_product_category' => 'all',
			'sel_status' => 'all',
			'sel_complaint' => 'all',
			'sel_sof' => 'all',	
			'sel_cof' => 'all',
			'open_date_from' => '',
			'open_date_to' => '',
			'close_date_from' => '',
			'close_date_to' => '',
			'sel_user' => '',
			'open_by' => '',
			'order_by' => '',
			'order_type' => '',
			'txt_search_ticket' => '',
		];
		$return = get_filtered_ajax_data('report_ticket_listing_filter', $default_data, $post_data);
		$data = $return['data'];		
		
		$data['print']	= $mode;

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');		
		
		$data['page_title'] 		= 'Service Ticket Report';
		$data['form_action'] 		= base_url('report/ticket_listing');

		
		$data['opt_category_list'] 	= $this->common_model->get_category_list();		
		$data['opt_product_category_list'] = $this->common_model->get_product_category_list();		
		$data['opt_complaint']		= $this->common_model->get_tt_complaint_list();
		$data['opt_sof']			= $this->common_model->get_tt_sof_list();
		$data['opt_cof']			= $this->common_model->get_tt_cof_list();
		$data['opt_user']			= $this->common_model->get_user_list();
		$data['msg'] 				= $this->msg;	
		$row_html = $this->ticket_listing_rows(1, $mode);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) { 
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'ticket_listing';
			$header_data['description']	= 'Service Ticket Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/ticket_listing',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/ticket_listing.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/ticket_listing.pdf',
				'Service Ticket Report',
				'Attached herewith is the service ticket report sent from itelco system.',
				'[Service Ticket Report]'
			);

			unset($_POST['btFilter']);
		}

		$data['print']	= $mode;
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/ticket_listing',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}		
	}

	function ticket_listing_rows($returnOnly = 0, $mode = '')
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'sel_category' => 'all',
			'sel_product_category' => 'all',
			'sel_status' => 'all',
			'sel_complaint' => 'all',
			'sel_sof' => 'all',	
			'sel_cof' => 'all',
			'open_date_from' => '',
			'open_date_to' => '',
			'close_date_from' => '',
			'close_date_to' => '',
			'sel_user' => '',
			'open_by' => '',
			'order_by' => '',
			'order_type' => '',
			'txt_search_ticket' => '',
		];
		$return = get_filtered_ajax_data('report_ticket_listing_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$qwhere = "";

		if ( $data['sel_category'] != 'all' && !empty($data['sel_category']) ) {
			$qwhere .= "AND c.category = '" . $this->db->escape_str($data['sel_category']) . "'";
		}

		if ( $data['sel_product_category'] != 'all' && !empty($data['sel_product_category']) ) {
			$qwhere .= "AND tt.tt_category = '" . $this->db->escape_str($data['sel_product_category']) . "'";
		}

		if ( $data['sel_status'] != 'all' ) {
			$qwhere .= "AND tt.tt_status = '" . $this->db->escape_str($data['sel_status']) . "'";
		}

		if ( $data['sel_complaint'] != 'all' && !empty($data['sel_complaint']) ) {
			$qwhere .= "AND tt.tt_complaint_id = '" . $this->db->escape_str($data['sel_complaint']) . "'";
		}

		if ( $data['sel_sof'] != 'all' && !empty($data['sel_sof']) ) {
			$qwhere .= "AND tt.tt_sof_id = '" . $this->db->escape_str($data['sel_sof']) . "'";
		}

		if ( $data['sel_cof'] != 'all' && !empty($data['sel_cof']) ) {
			$qwhere .= "AND tt.tt_cof_id = '" . $this->db->escape_str($data['sel_cof']) . "'";
		}

		if( $data['open_date_from'] != '' ){
			$qwhere .= " AND tt.datetime_open >= '".$this->db->escape_str(date( "Y-m-d", strtotime($data['open_date_from']) ))."' ";
		}

		if( $data['open_date_to'] != '' ){
			$qwhere .= " AND tt.datetime_open <= '".$this->db->escape_str(date( "Y-m-d", strtotime($data['open_date_to']) ))."' ";
		}

		if( $data['close_date_from'] != '' ){
			$qwhere .= " AND tt.datetime_close >= '".$this->db->escape_str(date( "Y-m-d", strtotime($data['close_date_from']) ))."' ";
		}

		if( $data['close_date_to'] != '' ){
			$qwhere .= " AND tt.datetime_close <= '".$this->db->escape_str(date( "Y-m-d", strtotime($data['close_date_to']) ))."' ";
		}

		if( $data['sel_user'] != '' ){
			$qwhere .= " AND tt.tt_assign_to LIKE '%\"".$this->db->escape_str($data['sel_user'])."\"%' ";
		}

		if( $data['open_by'] != '' ){
			$qwhere .= " AND tt.created_by = '".$this->db->escape_str($data['open_by'])."' ";
		}

		//text search
		if( $data['txt_search_ticket'] != '' ){
			$qwhere .= " AND (
				c.name LIKE '%".$this->db->escape_str($data['txt_search_ticket'])."%' 
				OR tt.tt_remark LIKE '%".$this->db->escape_str($data['txt_search_ticket'])."%' 
				OR tt.tt_no LIKE '%".$this->db->escape_str($data['txt_search_ticket'])."%'  
				OR tt.customer_no LIKE '%".$this->db->escape_str($data['txt_search_ticket'])."%' 
			) ";
		}

		$this->load->model('ticket_model');
		$tickets = $this->ticket_model->get_trouble_ticket_listing( '', '', 0, 99999, $qwhere );
		foreach( $tickets['row'] as $key => $row ){
			$ticket['row'][$key]['assign_to_name'] = "";
			if( $row['tt_assign_to'] != '' ){
				$str   = " SELECT GROUP_CONCAT( username ) AS usernames
							FROM user WHERE idx IN ( ". $row['tt_assign_to']." ) ";
				$query = $this->db->query($str);
				if( $query->num_rows() > 0 ){
					$cust = $query->row_array();
					$tickets['row'][$key]['assign_to_name'] = $cust['usernames'];
				}				
			}
		}

		$data['tickets']			= $initial? [] : $tickets['row'];
		$data['print'] 				= $mode;
		$html = $this->parser->parse('report/ticket_listing_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	function asset_listing( $mode='' ) {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_asset_listing_filter', $default_data, $post_data);
		$data = $return['data'];

		$qwhere 		= "";
		$data['print']	= $mode;

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Asset Listing';
		$data['form_action'] 		= base_url('report/asset_listing');

		$data['msg'] 			= $this->msg;
		$row_html = $this->asset_listing_rows(1);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'asset_listing';
			$header_data['description']	= 'Asset Listing' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/asset_listing',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/asset_listing.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/asset_listing.pdf',
				'Asset Listing Report',
				'Attached herewith is the asset listing report sent from itelco system.',
				'[Asset Listing Report]'
			);

			unset($_POST['btFilter']);
		}

		$data['print']	= $mode;
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/asset_listing',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
	}

	function asset_listing_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_asset_listing_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$qwhere 		= "";
		$data['print']	= '';

		$this->load->model('asset_model');
		$assets = $this->asset_model->get_asset_list( $data['txt_search'], 0, 99999 );

		foreach ($assets['row'] as $key => $val) {
			$assets['row'][$key]['status_text'] = (isset($this->asset_status[$val['asset_status']]) ? $this->asset_status[$val['asset_status']] : '');
		}

		$data['assets']			= $initial? [] : $assets['row'];

		$html = $this->parser->parse('report/asset_listing_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_asset_listing()
	{
		$txt_search = $this->input->post('txt_search');

		$this->load->model('asset_model');
		$result = $this->asset_model->get_asset_list($txt_search, 0, 99999);
		$assets = $result['row'];

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Asset Listing');

		$headers = [
			'#','Vendor','Name','Code','Tag','Site','Location','Category','Department',
			'Brand','Details','Model','Country of Origin','Serial No.','Purchase Date',
			'Purchase Price','Warranty','Warranty Expiry','Manufacturer','Owner',
			'Use Life','Depreciation Rate','Current Asset Value','Asset Status','Non-Capitalize'
		];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		$no = 1;
		foreach ($assets as $asset) {
			$non_capitalize = $asset['non_capitalize'] == '1' ? 'Yes' : 'No';
			$asset_status = isset($this->asset_status[$asset['asset_status']]) ? $this->asset_status[$asset['asset_status']] : '';

			$sheet->setCellValue('A'.$row, $no);
			$sheet->setCellValue('B'.$row, $asset['vendor']);
			$sheet->setCellValue('C'.$row, $asset['asset_name']);
			$sheet->setCellValue('D'.$row, $asset['asset_code']);
			$sheet->setCellValue('E'.$row, $asset['asset_tag']);
			$sheet->setCellValue('F'.$row, $asset['site_name']);
			$sheet->setCellValue('G'.$row, $asset['location']);
			$sheet->setCellValue('H'.$row, $asset['category_name']);
			$sheet->setCellValue('I'.$row, $asset['department']);
			$sheet->setCellValue('J'.$row, $asset['brand']);
			$sheet->setCellValue('K'.$row, $asset['details']);
			$sheet->setCellValue('L'.$row, $asset['model_no']);
			$sheet->setCellValue('M'.$row, $asset['origin_country']);
			$sheet->setCellValue('N'.$row, $asset['serial_no']);
			$sheet->setCellValue('O'.$row, $asset['purchase_date']);
			$sheet->setCellValue('P'.$row, $asset['purchase_price']);
			$sheet->setCellValue('Q'.$row, $asset['warranty']);
			$sheet->setCellValue('R'.$row, $asset['warranty_expiry']);
			$sheet->setCellValue('S'.$row, $asset['manufacturer']);
			$sheet->setCellValue('T'.$row, $asset['owner']);
			$sheet->setCellValue('U'.$row, $asset['use_life']);
			$sheet->setCellValue('V'.$row, $asset['depreciation_rate']);
			$sheet->setCellValue('W'.$row, $asset['current_asset_value']);
			$sheet->setCellValue('X'.$row, $asset_status);
			$sheet->setCellValue('Y'.$row, $non_capitalize);

			$row++;
			$no++;
		}

		foreach (range('A','Y') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'asset_listing_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function planned_maintenance( $mode='' ) {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_planned_maintenance_filter', $default_data, $post_data);
		$data = $return['data'];
		
		$data['print']	= $mode;

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Planned Maintenance Report';
		$data['form_action'] 		= base_url('report/planned_maintenance');

		$data['msg'] 			= $this->msg;
		$row_html = $this->planned_maintenance_rows(1);
		$data['row_html'] = $row_html;	

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'planned_maintenance';
			$header_data['description']	= 'Planned Maintenance Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/planned_maintenance',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/planned_maintenance.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/planned_maintenance.pdf',
				'Planned Maintenance Report',
				'Attached herewith is the planned maintenance report sent from itelco system.',
				'[Planned Maintenance Report]'
			);

			unset($_POST['btFilter']);
		}

		$data['print']	= $mode;
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/planned_maintenance',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
	}

	function planned_maintenance_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'order_by' => '',
			'order_type' => '',
		];
		$return = get_filtered_ajax_data('report_planned_maintenance_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$qwhere 		= "";
		$data['print']	= '';

		$this->load->model('asset_model');
		$asset_list			= $this->asset_model->get_upcoming_maint($data['txt_search'],'',0,99999);

		$data['assets']			= $initial? [] : $asset_list['row'];

		$html = $this->parser->parse('report/planned_maintenance_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_planned_maintenance()
	{
		$txt_search = $this->input->post('txt_search');

		$this->load->model('asset_model');
		$result = $this->asset_model->get_upcoming_maint($txt_search, '', 0, 99999);
		$assets = $result['row'];

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Planned Maintenance');

		$headers = ['#','Asset Tag','Asset Name','Last Maintenance','Next Maintenance'];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		$no = 1;
		foreach ($assets as $a) {
			$sheet->setCellValue('A'.$row, $no);
			$sheet->setCellValue('B'.$row, $a['asset_tag']);
			$sheet->setCellValue('C'.$row, $a['asset_name']);
			$sheet->setCellValue('D'.$row, $a['last_maint_date']);
			$sheet->setCellValue('E'.$row, $a['next_maint']);
			$row++;
			$no++;
		}

		foreach (range('A','E') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'planned_maintenance_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function asset_service( $mode='' ){
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_date_start' => '',
			'txt_date_end' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_asset_service_filter', $default_data, $post_data);
		$data = $return['data'];

		$data['print']	= $mode;

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');
		
		$data['page_title'] 		= 'Asset Service Report';
		$data['form_action'] 		= base_url('report/asset_service');

		$data['msg'] 			= $this->msg;
		$row_html = $this->asset_service_rows(1);
		$data['row_html'] = $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'asset_service';
			$header_data['description']	= 'Asset Service Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/asset_service',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/asset_service.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/asset_service.pdf',
				'Asset Service Report',
				'Attached herewith is the asset service report sent from itelco system.',
				'[Asset Service Report]'
			);

			unset($_POST['btFilter']);
		}

		$data['print']	= $mode;
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/asset_service',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
	}

	function asset_service_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_date_start' => '',
			'txt_date_end' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_asset_service_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$qwhere 		= "";
		$data['print'] = '';

		$this->load->model('asset_model');
		$record_list			= $this->asset_model->get_service_record_list($data['txt_search'],$data['txt_date_start'],$data['txt_date_end'],0,99999);

		foreach ($record_list['row'] as $key => $val) { 
			$record_list['row'][$key]['status_text'] = (isset($this->service_record_status[$val['status']]) ? $this->service_record_status[$val['status']] : '');

			$record_list['row'][$key]['service_date_time'] = (!empty($val['service_date_time']) ? date('Y-m-d', strtotime($val['service_date_time'])) : '');

			$record_list['row'][$key]['maint_cost'] = number_format($val['maint_cost'],2,'.',',');
		}

		$data['assets']			= $initial? [] : $record_list['row'];

		$html = $this->parser->parse('report/asset_service_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_service_list()
	{
		$txt_search      = $this->input->post('txt_search');
		$txt_date_start  = $this->input->post('txt_date_start');
		$txt_date_end    = $this->input->post('txt_date_end');

		$this->load->model('asset_model');
		$result = $this->asset_model->get_service_record_list($txt_search, $txt_date_start, $txt_date_end, 0, 99999);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Service List');

		$headers = ['#','Asset Tag','Asset Name','Service Date','Vendor','Cost','Status'];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		$no = 1;
		foreach ($result['row'] as $r) {
			$service_date = !empty($r['service_date_time']) ? date('Y-m-d', strtotime($r['service_date_time'])) : '';
			$status_text = $this->service_record_status[$r['status']] ?? '';
			$maint_cost = $r['maint_cost'] ?? 0;

			$sheet->setCellValue('A'.$row, $no);
			$sheet->setCellValue('B'.$row, $r['asset_tag']);
			$sheet->setCellValue('C'.$row, $r['asset_name']);
			$sheet->setCellValue('D'.$row, $service_date);
			$sheet->setCellValue('E'.$row, $r['vendor']);
			$sheet->setCellValue('F'.$row, $maint_cost);
			$sheet->setCellValue('G'.$row, $status_text);

			$row++;
			$no++;
		}

		foreach (range('A','G') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'asset_service_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function asset_transfer( $mode='' ) {
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_date_start' => '',
			'txt_date_end' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_asset_transfer_filter', $default_data, $post_data);
		$data = $return['data'];

		$data['print']	= $mode;

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['page_title'] 		= 'Asset Transfer Report';
		$data['form_action'] 		= base_url('report/asset_transfer');

		$row_html = $this->asset_transfer_rows(1, $mode);
		$data['row_html'] = $row_html;

		$data['msg'] = $this->msg;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'asset_transfer';
			$header_data['description']	= 'Asset Transfer Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/asset_transfer',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/asset_transfer.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/asset_transfer.pdf',
				'Asset Transfer Report',
				'Attached herewith is the asset transfer report sent from itelco system.',
				'[Asset Transfer Report]'
			);

			unset($_POST['btFilter']);
		}

		$data['print']	= $mode;
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/asset_transfer',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}

	}

	function asset_transfer_rows($returnOnly = 0, $mode='')
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'is_postback' => 0,
			'txt_search' => '',
			'txt_date_start' => '',
			'txt_date_end' => '',
			'order_by' => '',
			'order_type' => ''
		];
		$return = get_filtered_ajax_data('report_asset_transfer_filter', $default_data, $post_data);
		$data = $return['data'];
		$initial = $return['initial'];

		$this->load->model('asset_model');
		$record_list			= $this->asset_model->get_transfer_record_list($data['txt_search'],$data['txt_date_start'],$data['txt_date_end'],0,99999);

		$data['assets']	= $initial? [] : $record_list['row'];
		$data['print'] = $mode;

		$html = $this->parser->parse('report/asset_transfer_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_asset_transfer()
	{
		$txt_search     = $this->input->post('txt_search');
		$txt_date_start = $this->input->post('txt_date_start');
		$txt_date_end   = $this->input->post('txt_date_end');

		$this->load->model('asset_model');
		$result = $this->asset_model->get_transfer_record_list($txt_search, $txt_date_start, $txt_date_end, 0, 99999);
		$assets = $result['row'] ?? [];

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Asset Transfer');

		$headers = ['#', 'Asset Tag', 'Asset Name', 'From', 'To', 'Transfer Date', 'Description'];
		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		$no = 1;
		foreach ($assets as $a) {
			$sheet->setCellValue('A'.$row, $no);
			$sheet->setCellValue('B'.$row, $a['asset_tag']);
			$sheet->setCellValue('C'.$row, $a['asset_name']);
			$sheet->setCellValue('D'.$row, $a['from_name']);
			$sheet->setCellValue('E'.$row, $a['to_name']);
			$sheet->setCellValue('F'.$row, $a['transfer_date']);
			$sheet->setCellValue('G'.$row, $a['description']);
			$row++;
			$no++;
		}

		foreach (range('A','G') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'asset_transfer_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}
	
	public function export_customer_listing()
	{
		$is_postback      = $this->input->post('is_postback');
		$sel_category     = $this->input->post('sel_category');
		$sel_status       = $this->input->post('sel_status');
		$sel_status_date  = $this->input->post('sel_status_date');
		$sel_activated    = $this->input->post('sel_activated');
		$sel_building     = $this->input->post('sel_building');
		$sel_package      = $this->input->post('sel_package');
		$sel_dealer       = $this->input->post('sel_dealer');
		$txt_search       = $this->input->post('txt_search');
		$date_from        = $this->input->post('date_from');
		$date_to          = $this->input->post('date_to');
		$order_by         = $this->input->post('order_by');
		$order_type       = $this->input->post('order_type');

		if (empty($sel_category)) $sel_category = 'r';

		$query_where = "AND c.category = '{$sel_category}' ";

		if ($sel_status != 'all' && !empty($sel_status) && !in_array($sel_status, ['signup','activated'])) {
			$query_where .= "AND c.status = '{$sel_status}' ";
		}

		if ($sel_status_date != 'all' && !empty($sel_status_date)) {
			$map = [
				'signup'     => 'signup_date',
				'activated'  => 'activated_date',
				'suspended'  => 'suspended_date',
				'terminated' => 'terminated_date'
			];
			if (isset($map[$sel_status_date])) {
				if ($date_from != '') $query_where .= "AND c.{$map[$sel_status_date]} >= '".date('Y-m-d', strtotime($date_from))."' ";
				if ($date_to   != '') $query_where .= "AND c.{$map[$sel_status_date]} <= '".date('Y-m-d', strtotime($date_to))."' ";
			}
		}

		if ($sel_activated != 'all' && !empty($sel_activated)) {
			$query_where .= ($sel_activated == 'yes') ? "AND c.activated_date > 0 " : "AND c.activated_date = 0 ";
		}

		if ($sel_building != 'all' && !empty($sel_building)) $query_where .= "AND c.building = '{$sel_building}' ";
		if ($sel_package  != 'all' && !empty($sel_package))  $query_where .= "AND c.package  = '{$sel_package}' ";
		if ($sel_dealer   != 'all' && !empty($sel_dealer))   $query_where .= "AND c.dealer   = '{$sel_dealer}' ";

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$query_order = " ORDER BY {$order_by} {$order_type} ";
		}

		$result = $this->report_model->get_customer_join_building($txt_search, $query_where, $query_order);
		if ($result['num_rows'] <= 0) return;

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Customer Listing');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Customer Listing');

		$sheet->setCellValue('A1', 'Customer Listing');
		$sheet->mergeCells('A1:Q1');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		$headers = [
			'#','Customer No','Name','Login','Gender','Mobile','Email','Status',
			'Category','Dealer','Package','Monthly','Inst. Address',
			'Signup','Activated','Suspended','Terminated'
		];

		$row = 3;
		foreach ($headers as $i => $h) {
			$sheet->setCellValueByColumnAndRow($i, $row, $h);
		}
		$sheet->getStyle("A{$row}:Q{$row}")->getFont()->setBold(true);
		$row++;

		$no = 1;
		foreach ($result['result_array'] as $r) {
			$address = trim(
				($r['building_name'].' '.$r['inst_unit_no']).', '.
				$r['inst_addr1'].', '.$r['inst_addr2'].', '.$r['inst_addr3'].', '.
				$r['inst_city'].', '.$r['inst_postcode'].', '.$r['inst_state'],
				', '
			);

			$email = $r['email_1'] . ($r['email_2'] ? ';'.$r['email_2'] : '');

			$dates = ['signup_date','activated_date','suspended_date','terminated_date'];
			foreach ($dates as $d) {
				if ($r[$d] == '0000-00-00') $r[$d] = '-';
			}

			$data = [
				$no++,
				$r['customer_no'],
				$r['name'],
				$r['login_username'],
				strtoupper($r['gender']),
				$r['pic_mobile'],
				$email,
				strtoupper($r['latest_status']),
				strtoupper($r['category']),
				$r['dealer'] ?: '',
				$r['package_name'],
				$r['monthly_charge'],
				$address,
				$r['signup_date'],
				$r['activated_date'],
				$r['suspended_date'],
				$r['terminated_date']
			];

			foreach ($data as $i => $val) {
				$sheet->setCellValueByColumnAndRow($i, $row, $val);
			}
			$row++;
		}

		for ($i = 0; $i <= 16; $i++) {
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'customer_listing_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}
	
	public function export_ticket_listing()
	{
		$sel_category         = $this->input->post('sel_category') ?: 'all';
		$sel_product_category = $this->input->post('sel_product_category') ?: 'all';
		$sel_status           = $this->input->post('sel_status') ?: 'all';
		$sel_complaint        = $this->input->post('sel_complaint') ?: 'all';
		$sel_sof              = $this->input->post('sel_sof') ?: 'all';
		$sel_cof              = $this->input->post('sel_cof') ?: 'all';
		$open_date_from       = $this->input->post('open_date_from');
		$open_date_to         = $this->input->post('open_date_to');
		$close_date_from      = $this->input->post('close_date_from');
		$close_date_to        = $this->input->post('close_date_to');
		$sel_user             = $this->input->post('sel_user');
		$open_by             = $this->input->post('open_by');
		$txt_search_ticket	= $this->input->post('txt_search_ticket');

		$qwhere = "";
		if ($sel_category != 'all') $qwhere .= "AND c.category = '".$this->db->escape_str($sel_category)."' ";
		if ($sel_product_category != 'all') $qwhere .= "AND tt.tt_category = '".$this->db->escape_str($sel_product_category)."' ";
		if ($sel_status != 'all') $qwhere .= "AND tt.tt_status = '".$this->db->escape_str($sel_status)."' ";
		if ($sel_complaint != 'all') $qwhere .= "AND tt.tt_complaint_id = '".$this->db->escape_str($sel_complaint)."' ";
		if ($sel_sof != 'all') $qwhere .= "AND tt.tt_sof_id = '".$this->db->escape_str($sel_sof)."' ";
		if ($sel_cof != 'all') $qwhere .= "AND tt.tt_cof_id = '".$this->db->escape_str($sel_cof)."' ";
		if ($open_date_from) $qwhere .= "AND tt.datetime_open >= '".$this->db->escape_str(date("Y-m-d", strtotime($open_date_from)))."' ";
		if ($open_date_to) $qwhere .= "AND tt.datetime_open <= '".$this->db->escape_str(date("Y-m-d", strtotime($open_date_to)))."' ";
		if ($close_date_from) $qwhere .= "AND tt.datetime_close >= '".$this->db->escape_str(date("Y-m-d", strtotime($close_date_from)))."' ";
		if ($close_date_to) $qwhere .= "AND tt.datetime_close <= '".$this->db->escape_str(date("Y-m-d", strtotime($close_date_to)))."' ";
		if ($sel_user) $qwhere .= "AND tt.tt_assign_to LIKE '%\"".$this->db->escape_str($sel_user)."\"%' ";
		if ($open_by) $qwhere .= "AND tt.created_by = '".$this->db->escape_str($open_by)."' ";

		//text search
		if( $txt_search_ticket != '' ){
			$qwhere .= " AND (
				c.name LIKE '%".$this->db->escape_str($txt_search_ticket)."%' 
				OR tt.tt_remark LIKE '%".$this->db->escape_str($txt_search_ticket)."%' 
				OR tt.tt_no LIKE '%".$this->db->escape_str($txt_search_ticket)."%'  
				OR tt.customer_no LIKE '%".$this->db->escape_str($txt_search_ticket)."%' 
			) ";
		}

		$this->load->model('ticket_model');
		$tickets = $this->ticket_model->get_trouble_ticket_listing('', '', 0, 99999, $qwhere);

		foreach ($tickets['row'] as $key => $row) {
			$tickets['row'][$key]['assign_to_name'] = "";
			if (!empty($row['tt_assign_to'])) {
				$query = $this->db->query("SELECT GROUP_CONCAT(username) AS usernames FROM user WHERE idx IN (".$row['tt_assign_to'].")");
				if ($query->num_rows() > 0) {
					$tickets['row'][$key]['assign_to_name'] = $query->row()->usernames;
				}
			}
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Ticket Listing');

		$headers = [
			'#','TT #','Status','Customer No','Customer','Contact','Opened By','Opened On','Closed On','Duration','Complaint','SOF','COF','Assigned To', 'Remarks'
		];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		$no = 1;
		foreach ($tickets['row'] as $t) {
			$sheet->setCellValue('A'.$row, $no);
			$sheet->setCellValue('B'.$row, $t['tt_no']);
			$sheet->setCellValue('C'.$row, $t['tt_status']);
			$sheet->setCellValue('D'.$row, $t['customer_no']);
			$sheet->setCellValue('E'.$row, $t['pic_name']);
			$sheet->setCellValue('F'.$row, $t['contact_no']);
			$sheet->setCellValue('G'.$row, $t['prepared_by_name']);
			$sheet->setCellValue('H'.$row, $t['datetime_open']);
			$sheet->setCellValue('I'.$row, $t['datetime_close']);
			$sheet->setCellValue('J'.$row, $t['tt_duration']);
			$sheet->setCellValue('K'.$row, $t['tt_complaint_name']);
			$sheet->setCellValue('L'.$row, $t['tt_sof_name']);
			$sheet->setCellValue('M'.$row, $t['tt_cof_name']);
			$sheet->setCellValue('N'.$row, $t['assign_to_name']);
			$sheet->setCellValue('O'.$row, $t['tt_remark']);
			$row++;
			$no++;
		}

		foreach (range('A','O') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'ticket_listing_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}
	
	public function export_customer_building()
	{
		$is_postback   = $this->input->post('is_postback');
		$sel_category  = $this->input->post('sel_category') ?: 'all';
		$sel_status    = $this->input->post('sel_status');
		$sel_activated = $this->input->post('sel_activated');
		$sel_building  = $this->input->post('sel_building');
		$display_col   = $this->input->post('display_col') ?: ['p','c','a','s','t'];
		$date_from     = $this->input->post('date_from');
		$date_to       = $this->input->post('date_to');
		$order_by      = $this->input->post('order_by');
		$order_type    = $this->input->post('order_type');

		$query_where = '';

		if ($sel_category != 'all') {
			$query_where .= "AND category = '".$this->db->escape_str($sel_category)."' ";
		}

		if ($sel_status != 'all' && !empty($sel_status)) {
			if ($date_from != '') {
				$query_where .= "AND cs.transact_date >= '".date('Y-m-d', strtotime($this->db->escape_str($date_from)))."' ";
			}
			if ($date_to != '') {
				$query_where .= "AND cs.transact_date <= '".date('Y-m-d', strtotime($this->db->escape_str($date_to)))."' ";
			}
			$query_where .= "AND cs.status = '".$this->db->escape_str($sel_status)."' ";
		}

		if ($sel_activated != 'all' && !empty($sel_activated)) {
			$query_where .= ($sel_activated == 'yes')
				? "AND cs.status IN ('A','S','T') "
				: "AND cs.status IN ('P','C') ";
		}

		if ($sel_building == 'all') $sel_building = '';

		$this->load->model('building_model');
		$building_list = $this->common_model->get_building_list('building_no', $sel_building);

		$grand = [
			'unit' => 0, 'p' => 0, 'c' => 0, 'a' => 0, 's' => 0, 't' => 0, 'total' => 0
		];

		foreach ($building_list as $k => $b) {
			$results = $this->building_model->get_building_total_users($b['building_no'], $query_where);
			$tot = ['P'=>0,'C'=>0,'A'=>0,'S'=>0,'T'=>0];
			foreach ($results as $r) {
				$tot[$r['latest_status']] = $r['total_customer'];
			}
			$building_list[$k]['p'] = $tot['P'];
			$building_list[$k]['c'] = $tot['C'];
			$building_list[$k]['a'] = $tot['A'];
			$building_list[$k]['s'] = $tot['S'];
			$building_list[$k]['t'] = $tot['T'];
			$building_list[$k]['total_customer'] = array_sum($tot);

			$grand['unit'] += $b['total_unit'];
			$grand['p'] += $tot['P'];
			$grand['c'] += $tot['C'];
			$grand['a'] += $tot['A'];
			$grand['s'] += $tot['S'];
			$grand['t'] += $tot['T'];
			$grand['total'] += array_sum($tot);
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Customer By Building');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Customer By Building');

		$sheet->setCellValue('A1', 'Customer Report By Building');
		$sheet->mergeCells('A1:Z1');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		$row = 3;
		$col = 'A';
		$headers = ['#','Building','Total Units'];

		if (in_array('p',$display_col)) $headers[] = 'Pending';
		if (in_array('c',$display_col)) $headers[] = 'Cancelled';
		if (in_array('a',$display_col)) $headers[] = 'Activated';
		if (in_array('s',$display_col)) $headers[] = 'Suspended';
		if (in_array('t',$display_col)) $headers[] = 'Terminated';
		$headers[] = 'Total Customers';

		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$col++;
		}
		$sheet->getStyle('A'.$row.':'.$col.$row)->getFont()->setBold(true);
		$row++;

		$no = 1;
		foreach ($building_list as $b) {
			$col = 'A';
			$sheet->setCellValue($col++.$row, $no++);
			$sheet->setCellValue($col++.$row, $b['name']);
			$sheet->setCellValue($col++.$row, $b['total_unit']);
			if (in_array('p',$display_col)) $sheet->setCellValue($col++.$row, $b['p']);
			if (in_array('c',$display_col)) $sheet->setCellValue($col++.$row, $b['c']);
			if (in_array('a',$display_col)) $sheet->setCellValue($col++.$row, $b['a']);
			if (in_array('s',$display_col)) $sheet->setCellValue($col++.$row, $b['s']);
			if (in_array('t',$display_col)) $sheet->setCellValue($col++.$row, $b['t']);
			$sheet->setCellValue($col.$row, $b['total_customer']);
			$row++;
		}

		$col = 'A';
		$sheet->setCellValue($col++.$row, '');
		$sheet->setCellValue($col++.$row, 'Grand Total');
		$sheet->setCellValue($col++.$row, $grand['unit']);
		if (in_array('p',$display_col)) $sheet->setCellValue($col++.$row, $grand['p']);
		if (in_array('c',$display_col)) $sheet->setCellValue($col++.$row, $grand['c']);
		if (in_array('a',$display_col)) $sheet->setCellValue($col++.$row, $grand['a']);
		if (in_array('s',$display_col)) $sheet->setCellValue($col++.$row, $grand['s']);
		if (in_array('t',$display_col)) $sheet->setCellValue($col++.$row, $grand['t']);
		$sheet->setCellValue($col.$row, $grand['total']);
		$sheet->getStyle('A'.$row.':'.$col.$row)->getFont()->setBold(true);

		foreach (range('A',$col) as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'customer_building_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}
	
	public function export_customer_overdue()
	{
		$is_postback  = $this->input->post('is_postback');
		$sel_category = $this->input->post('sel_category');
		$showterminate = $this->input->post('showterminate');
		$order_by    = $this->input->post('order_by');
		$order_type  = $this->input->post('order_type');

		$query_where = '';
		if (!empty($sel_category) && $sel_category !== 'all') {
			$query_where = "AND c.category = '".$this->db->escape_str($sel_category)."' ";
		}

		if (!empty($showterminate)) {
			$query_where .= " AND IFNULL(cs.status, 'S') IN ('A','T','S') ";
		} else {
			$query_where .= " AND IFNULL(cs.status, 'S') IN ('A','S') ";
		}

		$query_order = '';
		if ($order_by != '' && $order_type != '') {
			$query_order = " ORDER BY c.category, ".$this->db->escape_str($order_by)." ".$this->db->escape_str($order_type);
		}

		$sess_cust_category = $_SESSION['cust_category'];

		$result = $this->report_model->get_overdue_list($query_where, $query_order);

		$data_row = [];
		foreach ($result['result_array'] as $row)
		{
			$overdue = "";
			if (empty($row['last_payment_date'])) {
				$row['last_payment_date'] = $row['first_activate'];
			}
			if( $row['last_payment_date'] != '' ){
				$pay_date 	= new DateTime($row['last_payment_date']);
				$now 		= new DateTime();
				$interval 	= date_diff($pay_date, $now);
				
				if ( $interval->format('%y') > 0 ) 
				{
					$overdue .= $interval->format('%y') . ' Year(s) ';
				}
				if ($interval->format('%m') > 0 ) 
				{
					$overdue .= $interval->format('%m') . ' Month(s)';
				}				
			}
			$data_row[$row['category']][$row['customer_no']] = array(
				'customer_name' => $row['customer_name'],
				'bill_date' => $row['bill_date'],
				'bill_due_date' => $row['bill_due_date'],
				'last_payment_date' => date('Y-m-d', strtotime($row['last_payment_date'])),
				'overdue' => $overdue,
				'current_balance' => $row['updated_balance'],
				'first_activate' => $row['first_activate'],
				'latest_status' => (isset($this->account_status[$row['latest_status']])?$this->account_status[$row['latest_status']]:''),
			);
		}

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$objPHPExcel->getProperties()->setCreator('Itelco System')->setTitle('Customer Overdue Report');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Customer Overdue');

		$sheet->setCellValue('A1', 'Customer Overdue Report');
		$sheet->mergeCells('A1:H1');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		$row = 3;
		$headers = ['Customer No','Name','Current Status','Last Bill Date','Last Payment Date','Overdue','Current Balance'];
		$col = 'A';
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$col++;
		}
		$sheet->getStyle('A'.$row.':'.$col.$row)->getFont()->setBold(true);
		$row++;

		$grand_total = 0;
		foreach ($data_row as $cat => $customers) {
			$sheet->setCellValue('A'.$row, isset($sess_cust_category[$cat]) ? $sess_cust_category[$cat] : '');
			$sheet->mergeCells('A'.$row.':G'.$row);
			$sheet->getStyle('A'.$row)->getFont()->setBold(true);
			$row++;

			$total_category = 0;
			foreach ($customers as $cust_no => $c) {
				$sheet->setCellValue('A'.$row, $cust_no);
				$sheet->setCellValue('B'.$row, $c['customer_name']);
				$sheet->setCellValue('C'.$row, $c['latest_status']);
				$sheet->setCellValue('D'.$row, $c['bill_date']);
				$sheet->setCellValue('E'.$row, $c['last_payment_date']);
				$sheet->setCellValue('F'.$row, $c['overdue']);
				$sheet->setCellValue('G'.$row, $c['current_balance']);
				$total_category += $c['current_balance'];
				$row++;
			}

			$sheet->setCellValue('F'.$row, 'Total');
			$sheet->setCellValue('G'.$row, number_format($total_category, 2, '.', ''));
			$sheet->getStyle('F'.$row.':G'.$row)->getFont()->setBold(true);
			$row++;

			$grand_total += $total_category;
		}

		$sheet->setCellValue('F'.$row, 'Grand Total');
		$sheet->setCellValue('G'.$row, number_format($grand_total, 2, '.', ''));
		$sheet->getStyle('F'.$row.':G'.$row)->getFont()->setBold(true);

		foreach (range('A','G') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'customer_overdue_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function radius_radcheck( $mode='' ){
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'username' => ''
		];
		$return = get_filtered_ajax_data('report_radius_radcheck_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['print']	= $mode;
		
		$data['page_title']			= 'Radcheck';
		$data['form_action']		= base_url('report/radius_radcheck');	
		$data['msg']				= $this->msg;
		$row_html = $this->radius_radcheck_rows(1);
		$data['row_html'] = $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'radius_radcheck';
			$header_data['description']	= 'Radius Radcheck Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/radius_radcheck',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/radius_radcheck.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/radius_radcheck.pdf',
				'Radius Radcheck Report',
				'Attached herewith is the radius radcheck report sent from itelco system.',
				'[Radius Radcheck Report]'
			);

			unset($_POST['btFilter']);
		}
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/radius_radcheck',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
		
	}

	function radius_radcheck_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'username' => ''
		];
		$return = get_filtered_ajax_data('report_radius_radcheck_filter', $default_data, $post_data);
		$data = $return['data'];

		$data['rows'] = $this->report_model->get_radius_radcheck( $data['username'] );

		$html = $this->parser->parse('report/radius_radcheck_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_radius_radcheck()
	{
		$username = $this->input->post('username');
		$rows = $this->report_model->get_radius_radcheck($username);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Radius Radcheck');

		$headers = ['Username','Attribute','Operator','Value'];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($rows as $r) {
			$sheet->setCellValue('A'.$row, $r['UserName']);
			$sheet->setCellValue('B'.$row, $r['Attribute']);
			$sheet->setCellValue('C'.$row, $r['op']);
			$sheet->setCellValue('D'.$row, $r['Value']);
			$row++;
		}

		foreach (range('A','D') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'radius_radcheck_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	function radius_usergroup( $mode='' ){
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'username' => '',
			'groupname' => ''
		];
		$return = get_filtered_ajax_data('report_radius_usergroup_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['print']	= $mode;
		
		$data['page_title']			= 'User Group';
		$data['form_action']		= base_url('report/radius_usergroup');	
		$data['msg']				= $this->msg;
		$row_html = $this->radius_usergroup_rows(1);
		$data['row_html'] = $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'radius_usergroup';
			$header_data['description']	= 'Radius Usergroup Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/radius_usergroup',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/radius_usergroup.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/radius_usergroup.pdf',
				'Radius Usergroup Report',
				'Attached herewith is the radius usergroup report sent from itelco system.',
				'[Radius Usergroup Report]'
			);

			unset($_POST['btFilter']);
		}
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/radius_usergroup',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}
	}

	function radius_usergroup_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'username' => '',
			'groupname' => ''
		];
		$return = get_filtered_ajax_data('report_radius_usergroup_filter', $default_data, $post_data);
		$data = $return['data'];

		$data['rows'] = $this->report_model->get_radius_usergroup( $data['username'], $data['groupname'] );

		$html = $this->parser->parse('report/radius_usergroup_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_radius_usergroup()
	{
		$username = $this->input->post('username');
		$groupname = $this->input->post('groupname');
		$rows = $this->report_model->get_radius_usergroup($username, $groupname);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Radius Usergroup');

		$headers = ['Username', 'GroupName', 'Priority'];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($rows as $r) {
			$sheet->setCellValue('A'.$row, $r['UserName']);
			$sheet->setCellValue('B'.$row, $r['GroupName']);
			$sheet->setCellValue('C'.$row, $r['priority']);
			$row++;
		}

		foreach (range('A','C') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

		while (ob_get_level()) ob_end_clean();
		$filename = 'radius_usergroup_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}
	
	function radius_login( $mode='' ){
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'username' => ''
		];
		$return = get_filtered_ajax_data('report_radius_login_filter', $default_data, $post_data);
		$data = $return['data'];

		//for send contact, will have additional post fields
		$contact_list = $this->input->post('contact_list');

		$data['print']	= $mode;		

		$data['page_title']			= 'Radius Login';
		$data['form_action']		= base_url('report/radius_login');	
		$data['msg']				= $this->msg;
		$row_html = $this->radius_login_rows(1);
		$data['row_html'] = $row_html;

		if (!empty($contact_list)) {
			//prep dompdf to generate pdf file
			$json_contact_list = json_decode($contact_list);

			$_POST['btFilter'] = 'print';

			$header_data = array();
			$header_data['title']		= 'radius_login';
			$header_data['description']	= 'Radius Login Report' ;
			$html = '';
			$html .= $this->load->view('templates/print_header_genpdf', $header_data, true);
			$html .= $this->parser->parse('report/radius_radacct',$data, true);
			$html .= $this->load->view('templates/print_footer', '', true);

			$this->load->helper(array('dompdf', 'file'));
			$pdf_data = pdf_create($html, '', false);

			$pdf_result = write_file($this->config->item('upload_path').'/temp/pdf/radius_login.pdf', $pdf_data);

			$send_result = $this->sendReportDoc(
				$json_contact_list,
				$this->config->item('upload_path') . '/temp/pdf/radius_login.pdf',
				'Radius Login Report',
				'Attached herewith is the radius login report sent from itelco system.',
				'[Radius Login Report]'
			);

			unset($_POST['btFilter']);
		}
		
		$this->load->view('templates/header', $this->vars);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/menu',$this->menu);
		}
		$this->parser->parse('report/radius_radacct',$data);
		if( $data['print'] != 'print' ){
			$this->load->view('templates/footer');
		}	
	}

	function radius_login_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? [];
		$default_data = [
			'username' => ''
		];
		$return = get_filtered_ajax_data('report_radius_login_filter', $default_data, $post_data);
		$data = $return['data'];

		$data['rows'] = $this->report_model->get_radius_login( $data['username'] );

		$html = $this->parser->parse('report/radius_radacct_rows', $data, true);

		if (!empty($returnOnly)) {
			return $html;
		} else {
			echo $html;
		}
	}

	public function export_radius_login()
	{
		$username = $this->input->post('username');
		$rows = $this->report_model->get_radius_login($username);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Radius Login');

		$headers = ['Username', 'Login', 'Logout'];

		$col = 'A';
		$row = 1;
		foreach ($headers as $h) {
			$sheet->setCellValue($col.$row, $h);
			$sheet->getStyle($col.$row)->getFont()->setBold(true);
			$col++;
		}

		$row = 2;
		foreach ($rows as $r) {
			$sheet->setCellValue('A'.$row, $r['username']);
			$sheet->setCellValue('B'.$row, $r['acctstarttime']);
			$sheet->setCellValue('C'.$row, $r['acctstoptime']);
			$row++;
		}

		foreach (range('A','C') as $c) {
			$sheet->getColumnDimension($c)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();
		$filename = 'radius_login_'.date('Ymd_His').'.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save('php://output');
		exit;
	}

	/**
	 * Send report document via Email / WhatsApp / Telegram.
	 *
	 * @param array  $json_contact_list
	 * @param string $attachment
	 * @param string $subject
	 * @param string $body
	 * @param string $doc_type
	 * @return mixed
	 */
	private function sendReportDoc($json_contact_list, $attachment, $subject, $body, $doc_type)
	{
		$this->load->library('whatsapp_template');

		$smtp_user = $this->common_model->get_table('sys_config', '*', "`category` = 'email' AND `key` = 'from_name'");

		$from_name = (!empty($smtp_user[0]['val']))
			? $smtp_user[0]['val']
			: 'no_reply@itelco.net';

		$meta_template = $this->whatsapp_template->build('ITELCO DOCS', ['doc_type' => $subject]);

		$send_array = [
			'send_type'		=> 'custom',
			'acc_id'		=> 0,
			'customer_no'	=> 0,
			'user_id'		=> 0,
			'controller'	=> 'report',
			'doc_id'		=> 0,
			'send_method'	=> 'manual',
			'acc_name'		=> 'Itelco User',
			'attachment'	=> $attachment,
			'subject'		=> $subject,
			'body'			=> $body,
			'from'			=> $from_name,
			'email_starter'	=> $subject,
			'doc_type'		=> $doc_type,
			'meta_template'	=> $meta_template['meta_template_name'] ?? '',
			'meta_vars'		=> $meta_template['meta_variable'] ?? [],
			'email_list'	=> $json_contact_list[0] ?? [],
			'whatsapp_list'	=> $json_contact_list[1] ?? [],
			'telegram_list'	=> $json_contact_list[2] ?? [],
		];

		return $this->docs_log_model->do_send($send_array);
	}

}

