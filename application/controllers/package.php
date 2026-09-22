<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Package extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		
		check_acl('package');
		$this->load->model('package_model');
		$this->load->model('common_model');	
		$this->load->model('router_model');	
    }
	
	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'status', 'label' => 'Status', 'rules' => 'trim'),
						array('field' => 'category', 'label' => 'Category', 'rules' => 'trim'),
						array('field' => 'package_no', 'label' => 'Package No', 'rules' => 'trim'),
						array('field' => 'name', 'label' => 'Package Name', 'rules' => 'trim|required'),
						array('field' => 'domain', 'label' => 'Domain', 'rules' => 'trim'),
						array('field' => 'bandwidth', 'label' => 'Bandwidth', 'rules' => 'trim'),
						array('field' => 'no_of_fixed_ip', 'label' => 'No of Fixed IP', 'rules' => 'trim|is_natural'),
						array('field' => 'no_of_email', 'label' => 'No of Email', 'rules' => 'trim|is_natural'),
						array('field' => 'router_id', 'label' => 'Router', 'rules' => 'trim|required'),
						array('field' => 'deposit', 'label' => 'Deposit', 'rules' => 'trim||numeric'),
						array('field' => 'stamp_duty', 'label' => 'Stamp Duty', 'rules' => 'trim|numeric'),
						array('field' => 'monthly_charge', 'label' => 'Monthly Charge', 'rules' => 'trim|numeric'),
						array('field' => 'yearly_charge', 'label' => 'Yearly Charge', 'rules' => 'trim|numeric'),
						array('field' => 'installation', 'label' => 'Installation', 'rules' => 'trim|numeric'),
						array('field' => 'one_time_charge', 'label' => 'One Time Charge', 'rules' => 'trim|numeric'),
						array('field' => 'package_month', 'label' => 'Package Month', 'rules' => 'trim|is_natural|required'),
						array('field' => 'stop_service_after', 'label' => 'Stop Service After', 'rules' => 'trim'),
						array('field' => 'yearly_billing', 'label' => 'Yearly Billing', 'rules' => 'trim'),
						array('field' => 'dealer_comm_type', 'label' => 'Monthly Commission', 'rules' => 'trim|required'),
						array('field' => 'dealer_monthly_comm', 'label' => 'Monthly Commission', 'rules' => 'trim|required|numeric'),
						array('field' => 'dealer_monthly_times', 'label' => 'Monthly Times', 'rules' => 'trim|required|numeric'),
						array('field' => 'dealer_onetime_comm', 'label' => 'One Time Commission', 'rules' => 'trim|required|numeric'),

						array('field' => 'dia_vars', 'label' => 'DIA Vars', 'rules' => 'trim'),

						array('field' => 'dom_rate', 'label' => 'Domestic Rate', 'rules' => 'trim'),
						array('field' => 'int_rate', 'label' => 'International Rate', 'rules' => 'trim'),

						array('field' => 'bill_waive_period', 'label' => 'Bill Waive Period', 'rules' => 'trim'),
						array('field' => 'free_package_upgrade', 'label' => 'Free Package Upgrade', 'rules' => 'trim'),
						array('field' => 'upgrade_package_id', 'label' => 'Upgrade To', 'rules' => 'trim'),

						array('field' => 'reactivate_fee', 'label' => 'Reactivate Fee', 'rules' => 'trim'),

						array('field' => 'product_category', 'label' => 'Product Category', 'rules' => 'trim'),

						array('field' => 'lineno', 'label' => 'Line No', 'rules' => 'trim'),
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}
	
	public function index()
    {
		$row_html = $this->package_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['sel_category'] = $post_data['sel_category'];
			$data['sel_status'] = $post_data['sel_status'];
		} else {
			$package_filter           = get_session_filter('package_filter');
			$data['page_item_no'] = $package_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $package_filter['txt_search'] ?? '';
			$data['sel_category'] = $package_filter['sel_category'] ?? 'all';
			$data['sel_status'] = $package_filter['sel_status'] ?? 'all';
		}

		$data['sel_category_list'] 	= $this->common_model->get_category_list();

		$data['page_title'] 	= 'Package';
		$data['form_action'] 	= base_url('package');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('package/index',$data);
		$this->load->view('templates/footer');
	}

	public function package_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$package_filter           = get_session_filter('package_filter');
			$post_data['page_item_no'] = $package_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $package_filter['txt_search'] ?? '';
			$post_data['sel_category'] = $package_filter['sel_category'] ?? 'all';
			$post_data['sel_status'] = $package_filter['sel_status'] ?? 'all';
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['sel_category'])) $post_data['sel_category'] = 'all';

		if (!isset($post_data['sel_status'])) $post_data['sel_status'] ='all';

		$total_row 		= 0;
		$page_item_no  = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search    = $post_data['txt_search'];
		$sel_category  = $post_data['sel_category'];
		$sel_status    = $post_data['sel_status'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'sel_category' => $sel_category,
			'sel_status' => $sel_status
		);

		set_session_filter('package_filter', $session_array);

		$query_where = "";
		if ( $sel_category != 'all' && !empty($sel_category) ) {
			$query_where = "AND p.category = '$sel_category' ";
		}
		
		if ( $sel_status != 'all' && !empty($sel_status) ) {
			$query_where .= "AND p.status = '$sel_status' ";
		}
		
		$result = $this->package_model->get_package_listing($txt_search,$page_item_no,$_SESSION['config']['max_page_item'],$query_where);
		$total_row = $result['total_row'];
		$row = $result['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);
		$data['row_data']	= $row;
		
		$html = $this->parser->parse('package/package_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function export_package_listing() {
		$txt_search   = $this->input->post('txt_search');
		$sel_category = $this->input->post('sel_category');
		$sel_status   = $this->input->post('sel_status');

		$query_where = "";
		if ($sel_category != 'all' && !empty($sel_category)) {
			$query_where = "AND p.category = '$sel_category' ";
		}
		if ($sel_status != 'all' && !empty($sel_status)) {
			$query_where .= "AND p.status = '$sel_status' ";
		}

		$result = $this->package_model->get_package_listing($txt_search, 0, 9999999, $query_where);

		$this->load->helper('excel');
		$objPHPExcel = prepare_excel();

		$objPHPExcel->getProperties()
			->setCreator('Itelco System')
			->setTitle('Package Listing');

		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('Package Listing');

		$sheet->setCellValue('A1', 'Package Listing');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		$irow = 3;

		$headers = [
			'#', 'Name', 'Category', 'Status', 'Domain', 'Bandwidth',
			'No of Fixed IP', 'No of Email', 'Deposit', 'Stamp Duty',
			'Monthly Fee', 'Yearly Charge', 'Installation Fee', 'Auto Pay Installation',
			'Package Month', 'Stop service after package month', 'Yearly Billing', 'Post Charges'
		];

		$colIndex = 0;
		foreach ($headers as $h) {
			$col = PHPExcel_Cell::stringFromColumnIndex($colIndex);
			$sheet->setCellValue($col . $irow, $h);
			$colIndex++;
		}

		$lastCol = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
		$sheet->getStyle("A{$irow}:{$lastCol}{$irow}")->getFont()->setBold(true);
		$sheet->getStyle("A{$irow}:{$lastCol}{$irow}")->getFill()
			->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()->setRGB('D9D9D9');

		$irow++;

		if (!empty($result['row'])) {
			$no = 1;
			foreach ($result['row'] as $row) {
				$sheet->setCellValue('A' . $irow, (string)$no);
				$sheet->setCellValue('B' . $irow, (string)$row['name']);
				$sheet->setCellValue('C' . $irow, (string)$row['category_name']);
				$sheet->setCellValue('D' . $irow, $row['status'] == 'a' ? 'Active' : 'Inactive');
				$sheet->setCellValue('E' . $irow, (string)$row['domain']);
				$sheet->setCellValue('F' . $irow, (string)strtoupper($row['bandwidth']));
				$sheet->setCellValue('G' . $irow, (string)strtoupper($row['no_of_fixed_ip']));
				$sheet->setCellValue('H' . $irow, (string)$row['no_of_email']);
				$sheet->setCellValue('I' . $irow, (string)$row['deposit']);
				$sheet->setCellValue('J' . $irow, (string)$row['stamp_duty']);
				$sheet->setCellValue('K' . $irow, (string)$row['monthly_charge']);
				$sheet->setCellValue('L' . $irow, (string)$row['yearly_charge']);
				$sheet->setCellValue('M' . $irow, (string)$row['installation']);
				$sheet->setCellValue('N' . $irow, $row['installation_auto'] == 1 ? 'Y' : 'N');
				$sheet->setCellValue('O' . $irow, (string)$row['package_month']);
				$sheet->setCellValue('P' . $irow, $row['stop_service_after'] == 1 ? 'Y' : 'N');
				$sheet->setCellValue('Q' . $irow, (string)$row['yearly_billing']);
				$sheet->setCellValue('R' . $irow, (string)$row['post_charge']);

				if ($irow % 2 == 0) {
					$sheet->getStyle("A{$irow}:R{$irow}")->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setRGB('F9F9F9');
				}

				$no++;
				$irow++;
			}
		} else {
			$sheet->setCellValue('A' . $irow, 'No package data found.');
		}

		for ($c = 0; $c < count($headers); $c++) {
			$col = PHPExcel_Cell::stringFromColumnIndex($c);
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		while (ob_get_level()) ob_end_clean();

		$filename = 'package_listing_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

	function add_package()
	{
		$data['input'] 						= $this->package_model->get_package();
		$data['page_title'] 				= 'Add Package';
		$data['form_action'] 				= base_url('package/save_package');		
		$data['sel_category_list'] 			= $this->common_model->get_category_list();
		$data['sel_tax_list'] 				= $this->common_model->get_tax_list();
		$data['sel_bill_type_list'] 		= $this->common_model->get_bill_type_list();
		$data['sel_domain_list'] 			= $this->common_model->get_domain_list();
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();
		$data['sel_router_list'] 			= $this->router_model->get_routers();
		$data['sel_building_list'] 			= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['upgrade_package_list'] 		= $this->package_model->get_package_listing_all_active();
		$data['msg'] 						= $this->msg;
		$data['selected_building'] 			= [];
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('package/package_detail',$data);
		$this->load->view('templates/footer');
	}                                                                             
	
	function edit_package($package_no='')
	{
		$package = $this->package_model->get_package($package_no);
		
		if ($package['package_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Package not found!');
			redirect('package');
		}
		
		$data['input'] 						= $package;
		$data['page_title'] 				= 'Edit Package';
		$data['form_action'] 				= base_url('package/save_package');
		$data['sel_category_list'] 			= $this->common_model->get_category_list();
		$data['sel_tax_list'] 				= $this->common_model->get_tax_list();
		$data['sel_bill_type_list'] 		= $this->common_model->get_bill_type_list();
		$data['sel_domain_list'] 			= $this->common_model->get_domain_list();		
		$data['sel_product_category_list'] 	= $this->common_model->get_product_category_list();
		$data['sel_router_list'] 			= $this->router_model->get_routers();
		$data['sel_building_list'] 			= $this->common_model->get_building_list('building_no', '', 'AND `status` = "a"');
		$data['msg'] 						= $this->msg;

		$data['upgrade_package_list'] 		= $this->package_model->get_package_listing_all_active();
		$data['selected_building'] 			= !empty($package['building']) ? explode(',', rtrim($package['building'], ',')) : [];
			
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('package/package_detail',$data);
		$this->load->view('templates/footer');
	}	
	
	function save_package()
	{
		$ajax = ['status' => 'ER', 'err_msg' => '', 'url' => 'package', 'error_keys' => []];
		$post = $this->input->post();

		if (!empty($post['btDelete']) && !empty($post['package_no'])) {

			$in_use = $this->check_customer_package_in_use($post['package_no'], [], 'customer_name');

			if (!empty($in_use)) {
				$ajax['err_msg'] = 'Package in use. Cannot delete.<br><br>' . $in_use;
				echo json_encode($ajax);
				return;
			}

			$this->package_model->package_delete($post['package_no'], $this->user['username']);

			$control_by = 'varied';
			if ($control_by === 'cisco') {
				$this->package_model->cisco_package_update();
			} elseif ($control_by === 'radius') {
				$this->package_model->radius_package_update();
			} else {
				$this->package_model->varied_package_update();
			}

			$this->session->set_flashdata("msg", 'Package deleted!');
			$ajax['status'] = 'SUCC';

			echo json_encode($ajax);
			return;
		}

		$this->set_form_validation('save');

		if (!$this->form_validation->run()) {
			$ajax['err_msg'] = validation_errors();
			$ajax['error_keys'] = validation_error_array();
			echo json_encode($ajax);
			return;
		}

		$post['building'] = !empty($post['building'])
			? $this->db->escape_str(implode(',', (array)$post['building'])) . ','
			: '';

		if (empty($post['package_no'])) {
			$result = $this->package_model->package_insert($post, $this->user['username']);
			$package_no = $result['package_no'];
		} else {
			$this->package_model->package_update($post, $this->user['username'], $post['package_no']);
			$package_no = $post['package_no'];
		}

		$control_by = 'varied';
		if ($control_by === 'cisco') {
			$this->package_model->cisco_package_update();
		} elseif ($control_by === 'radius') {
			$this->package_model->radius_package_update();
		} else {
			$this->package_model->varied_package_update();
		}

		$this->session->set_flashdata("msg", 'Package Saved!');

		$ajax['status'] = 'SUCC';
		$ajax['url'] = 'package/edit_package/' . $package_no;

		echo json_encode($ajax);
	}

	function moveup() {
		$post_data = $this->input->post();
		$package_no = $post_data['package_no'];

		$this->package_model->moveup($package_no);
	}

	function movedown() {
		$post_data = $this->input->post();
		$package_no = $post_data['package_no'];

		$this->package_model->movedown($package_no);
	}

	private function check_customer_package_in_use($package_no, $exclude_customer_no = [], $return = 'boolean')
	{
		if (!is_array($exclude_customer_no)) {
			$exclude_customer_no = [$exclude_customer_no];
		}

		$in_use = $this->package_model->check_customer_package_in_use($package_no, $exclude_customer_no);

		if ($return === 'boolean') {
			return !empty($in_use);
		}

		if ($return === 'customer_name') {
			if (empty($in_use)) {
				return '';
			}

			$display_list = array_slice($in_use, 0, 5);

			$customer_names = array_map(function ($customer) {
				return $customer['name'] . ' (' . $customer['customer_no'] . ')';
			}, $display_list);

			$result = implode('<br>', $customer_names);

			$extra = count($in_use) - 5;

			if ($extra > 0) {
				$result .= '<br>... and ' . $extra . ' more';
			}

			return $result;
		}

		return $in_use;
	}
}

