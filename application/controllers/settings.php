<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Settings extends DataPage_Controller {

	function __construct() {
        parent::__construct();
		$this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		//~ $this->check_acl('settings');
		//~ $this->load->helper(array('url','html','form'));
		$this->load->model('common_model');
		$this->load->model('setting_model');

		//SELECT b.name FROM acl_profile a INNER JOIN acl_list b ON a.acl_list LIKE CONCAT('%,', b.idx, ',%') WHERE a.username = 'admin';

		$this->class_list = array(
			'1' => 'Breastfeeding equipment',
			'2' => 'Child care centres and kindergartens fees',
			'3' => 'Computer, smartphone or tablet',
			'4' => 'Consolidated e-Invoice',
			'5' => 'Construction materials',
			'6' => 'Disbursement',
			'7' => 'Donation',
			'8' => 'e-Commerce - e-Invoice to buyer / purchaser',
			'9' => 'e-Commerce - Self-billed e-Invoice to seller, logistics, etc.',
			'10' => 'Education fees',
			'11' => 'Goods on consignment (Consignor)',
			'12' => 'Goods on consignment (Consignee)',
			'13' => 'Gym membership',
			'14' => 'Insurance - Education and medical benefits',
			'15' => 'Insurance - Takaful or life insurance',
			'16' => 'Interest and financing expenses',
			'17' => 'Internet subscription',
			'18' => 'Land and building',
			'19' => 'Medical examination for learning disabilities',
			'20' => 'Medical examination or vaccination expenses',
			'21' => 'Medical expenses for serious diseases',
			'22' => 'Others',
			'23' => 'Petroleum operations',
			'24' => 'Private retirement scheme or deferred annuity scheme',
			'25' => 'Motor vehicle',
			'26' => 'Subscription of books / journals / magazines / newspapers',
			'27' => 'Reimbursement',
			'28' => 'Rental of motor vehicle',
			'29' => 'EV charging facilities',
			'30' => 'Repair and maintenance',
			'31' => 'Research and development',
			'32' => 'Foreign income',
			'33' => 'Self-billed - Betting and gaming',
			'34' => 'Self-billed - Importation of goods',
			'35' => 'Self-billed - Importation of services',
			'36' => 'Self-billed - Others',
			'37' => 'Self-billed - Monetary payment to agents, dealers or distributors',
			'38' => 'Sports equipment',
			'39' => 'Supporting equipment for disabled person',
			'40' => 'Voluntary contribution to approved provident fund',
			'41' => 'Dental examination or treatment',
			'42' => 'Fertility treatment',
			'43' => 'Treatment and home care nursing',
			'44' => 'Vouchers, gift cards, loyalty points, etc',
			'45' => 'Self-billed - Non-monetary payment to agents',
		);

		$this->tax_type_list = array(
			'01' => 'Sales Tax',
			'02' => 'Service Tax',
			'03' => 'Tourism Tax',
			'04' => 'High-Value Goods Tax',
			'05' => 'Sales Tax On Low Value Goods',
			'06' => 'Not Applicable',
			'E' => 'Tax Exemption (Where Applicable)',
		);

    }

	function save_config()
	{
		if($_POST)
		{
			//~ echo '<pre>'; print_r($_POST); exit();
			$this->set_form_validation('save_config');
			//set error message
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1"><b>','</b></label>');

			if($this->form_validation->run() == false)
			{
				$this->msg['error_msg'] = validation_errors();
				$this->sys_config($this->input->post('sys_config'));
			}
			else
			{
				$insert_to_table			= 'sys_config';
				$where_key					= 'key';

				//$post_data['insert_data'] 	= $this->input->post(NULL, TRUE); // returns all POST items with XSS filter
				$post_data['insert_data'] 	= $this->input->post();
				if(isset($post_data['insert_data']['processtype']['save']))
				{
					//~ $query_str ="";
					//~ if(!empty($post_data['insert_data'][$insert_to_table]))
					//~ {
						//~ $query_str .=" UPDATE `sys_config` SET `val` = CASE ";
						//~ foreach($post_data['insert_data'][$insert_to_table] as $key => $val)
						//~ {
							//echo $key.'=>'.$val.'<br>';
							//~ $query_str .=" WHEN `key` = '".$key."' THEN '".$val."' ";
						//~ }
						//~ $query_str .=" ELSE `val` ";
						//~ $query_str .=" END; ";
					//~ }
					//~
					//~ $model				= 'action_log_model';
					//~ $this->load->model($model);
					//~ $ctrl				= $this->router->fetch_class();
					//~ $esc_query_str		= $this->db->escape_str($query_str);
					//~ $method 			= $this->router->method;
					//~ $action_desc		= 'sys config has been updated';
					//~ $action_category	= 'update';
					//~ $cust_no			= '';
					//~ $action_log 		= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
					//~ $result 			= (empty($query_str))?'':$this->db->query($query_str);

					$result = $this->setting_model->config_update($post_data,$insert_to_table);
					if($result)
					{
						$_SESSION['config'] = $post_data['insert_data']['sys_config'];
						$this->msg['msg']	= "Record Saved!";
					}
					else
					{
						$this->msg['error_msg']	= 'Error saving settings';
					}

					$this->sys_config();
				}
			}
		}
		else
		{
			redirect('settings');
		}
	}

	function save_tax()
	{
		if($_POST)
		{
			$this->set_form_validation('save_tax');
			//set error message
			$this->form_validation->set_error_delimiters('<label class="control-label" for="inputError1"><b>','</b></label>');

			if($this->form_validation->run() == false)
			{
				$this->msg['error_msg'] = validation_errors();
				$this->sys_tax_type($this->input->post('sys_tax_type'));
			}
			else
			{
				$insert_to_table			= 'sys_tax_type';
				$where_key					= 'code';
				$post_data['insert_data'] 	= $this->input->post(NULL, TRUE); // returns all POST items with XSS filter

				if(isset($post_data['insert_data']['processtype']['save']))
				{
					//~ $query_str 	= "";
					//~ if(!empty($post_data['insert_data'][$insert_to_table]))
					//~ {
						//~ $query_str .=" UPDATE `sys_tax_type` SET `percent` = CASE ";
						//~ foreach($post_data['insert_data'][$insert_to_table] as $code => $percent)
						//~ {
							//echo $key.'=>'.$val.'<br>';
							//~ $query_str .=" WHEN `code` = '".$code."' THEN '".$percent."' ";
						//~ }
						//~ $query_str .=" ELSE `percent` ";
						//~ $query_str .=" END; ";
					//~ }
					//~
					//~ $model			= 'action_log_model';
					//~ $this->load->model($model);
					//~ $ctrl			= $this->router->fetch_class();
					//~ $esc_query_str	= $this->db->escape_str($query_str);
					//~ $method 		= $this->router->method;
					//~ $action_desc	= 'tax config has been updated';
					//~ $action_category= 'update';
					//~ $cust_no		= '';
					//~ $action_log 	= $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$cust_no);
					//~
					//~ $result 		= (empty($query_str))?'':$this->db->query($query_str);

					$result		= $this->setting_model->tax_update($post_data,$insert_to_table);
					$this->setting_model->tax_insert($post_data,$insert_to_table);
					if($result) $this->msg['msg'] = "Record Saved!";
					else $this->msg['error_msg'] = 'Error Saving tax value.';//$result;
					$this->sys_tax_type();
				}
			}
		}
		else
		{
			redirect('settings/sys_tax_type');
		}
	}

	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'name', 'label' => 'Building Name', 'rules' => 'trim|required'),
						array('field' => 'total_unit', 'label' => 'Total Units', 'rules' => 'trim|is_natural'),
						array('field' => 'pppoe', 'label' => 'PPPoE', 'rules' => 'trim'),
						array('field' => 'dealer', 'label' => 'Agent', 'rules' => 'trim'),
					),
					'save_tax' => array (
						array('field' => 'sys_tax_type[EX]'	,'label' => 'EX'		,'rules' => 'trim|numeric|required'),
						array('field' => 'sys_tax_type[SR]'	,'label' => 'SR'		,'rules' => 'trim|numeric|required'),
						array('field' => 'sys_tax_type[ZR]'	,'label' => 'ZR'		,'rules' => 'trim|numeric|required'),
					),
					'save_config' =>  array (
						array('field' => 'sys_config[default_tax]', 'label' => 'default tax', 'rules' => 'trim|required'),
						array('field' => 'sys_config[set_attachment_at]', 'label' => 'set_attachment_at', 'rules' => 'trim|required'),
						array('field' => 'sys_config[mailtype]', 'label' => 'mailtype', 'rules' => 'trim|required'),
						array('field' => 'sys_config[protocol]', 'label' => 'protocol', 'rules' => 'trim|required'),
						array('field' => 'sys_config[smtp_host]', 'label' => 'smtp_host', 'rules' => 'trim|required'),
						array('field' => 'sys_config[smtp_pass]', 'label' => 'smtp_pass', 'rules' => 'trim|required'),
						array('field' => 'sys_config[smtp_port]', 'label' => 'smtp_port', 'rules' => 'trim|required'),
						array('field' => 'sys_config[smtp_user]', 'label' => 'smtp_user', 'rules' => 'trim|required'),
						array('field' => 'sys_config[profile_pic_count]', 'label' => 'Profile PIC Count', 'rules' => 'trim|required|greater_than[0]'),
					),
					'save_bill_type' => array (
						array('field' => 'name', 'label' => 'Bill Type Name', 'rules' => 'trim|required'),
						array('field' => 'is_debit', 'label' => 'Adjust Type', 'rules' => 'trim|required'),
						array('field' => 'tax_code', 'label' => 'Tax Code', 'rules' => 'trim|required'),
						array('field' => 'exclude_from_bill_calculation', 'label' => 'Exclude from Bill', 'rules' => 'trim'),
						array('field' => 'class_codes[]', 'label' => 'CLASS Codes', 'rules' => 'trim'),
					),
					'save_payment_type' => array (
						array('field' => 'name', 'label' => 'Payment Type Name', 'rules' => 'trim|required'),
					),
					'save_equipment_type' => array (
						array('field' => 'name', 'label' => 'Equipment Type Name', 'rules' => 'trim|required'),
					),
					'save_nas_address' => array(
						array('field' => 'nasname', 'label' => 'NAS Name', 'rules' => 'trim|required'),
						array('field' => 'shortname', 'label' => 'NAS Short Name', 'rules' => 'trim|required'),
						array('field' => 'secret', 'label' => 'NAS Secret', 'rules' => 'trim|required'),
						array('field' => 'ports', 'label' => 'Port', 'rules' => 'trim|numeric'),
					),
					'save_product_category' => array (
						array('field' => 'name', 'label' => 'Product Category', 'rules' => 'trim|required'),
					),
					'save_asset_category' => array (
						array('field' => 'category_code', 'label' => 'Category Code', 'rules' => 'trim|required'),
						array('field' => 'category_name', 'label' => 'Category Name', 'rules' => 'trim|required'),
					),
					'save_asset_code' => array(
						array('field' => 'asset_code', 'label' => 'Asset Code', 'rules' => 'trim|required'),
						array('field' => 'short_name', 'label' => 'Asset Name', 'rules' => 'trim|required'),
						array('field' => 'category_idx', 'label' => 'Category', 'rules' => 'trim|required'),
						array('field' => 'barcode', 'label' => 'Barcode', 'rules' => 'trim'),
						array('field' => 'brand', 'label' => 'Brand', 'rules' => 'trim'),
						array('field' => 'model_no', 'label' => 'Model No.', 'rules' => 'trim'),
						array('field' => 'manufacturer', 'label' => 'Manufacturer', 'rules' => 'trim'),
						array('field' => 'origin_country', 'label' => 'Country of Origin', 'rules' => 'trim'),
					),
					'save_asset_site' => array(
						array('field' => 'site_name', 'label' => 'Site Name', 'rules' => 'trim|required'),
					),
				);

		$this->form_validation->set_rules($config[$mode]);
	}

    public function sys_config()
	{	
		check_acl('config');
		$seg 			= $this->get_segment();
		$controller		= $seg[0];
		$method 		= $seg[1];
		$param_1 		= $seg[2];

		$table		= 'sys_config';
		$fields 	= $this->db->list_fields($table);
		$field_data	= $this->common_model->get_table($table);

		$inputs = array();
		foreach($field_data as $key => $input)
		{
			$input_key 			= $field_data[$key]['key'];
			$input_val			= $field_data[$key]['val'];
			$input_cat			= $field_data[$key]['category'];
			$name_or_id 		= $table.'['.$input_key.']';
			$placeholder		= ucwords(str_replace('_', ' ', $input_key));

			//~ echo $input_key.'<br>';
			//to skip some keys
			if( $input['category'] == 'ticket' || $input['category'] == 'customer_supports' ) continue;

			if ( $name_or_id == 'sys_config[bill_no_prefix]' || $name_or_id == 'sys_config[inv_no_prefix]' || $name_or_id == 'sys_config[jompay_biller_code]' || $name_or_id == 'sys_config[gst_reg_no]' || $name_or_id == 'sys_config[reactivate_min_amount]' ) continue;

			// ========= preset value ==================
			$input_arr['id']			= $name_or_id;
			$input_arr['name']			= $name_or_id;
			$input_arr['type']			= 'text';
			$input_arr['value']			= set_value($name_or_id, $input_val);
			$input_arr['placeholder']	= $placeholder;
			$input_arr['step']			= '0.1';
			$input_arr['style']			= 'width:100%;padding-left:0.6em;';
			$input_arr['class']			= 'form-control text-left';

			$input_key 		= $key;
			$addOnStyle		= "font-size:10px; min-width:150px; text-align:right;";
			$has_error 		= form_error($name_or_id);
			$add_has_error	= (empty($has_error))?'':'has-error';
			$wp_header		= "<div class='input-group $add_has_error'><span class='input-group-addon' style='$addOnStyle'>$placeholder</span>";
			$wp_header_long = "<div class='input-group $add_has_error'><span class='input-group-addon' style='font-size:10px; min-width:250px; text-align:right;'>$placeholder</span>";
			$wp_error		= form_error($name_or_id, "<label for='$name_or_id' class='$input_key text-danger' style='padding-left:0px;'>", "</label>");
			$wp_footer		= "</div>";
			$wp_footer 		.= $wp_error;

			if($name_or_id == 'sys_config[default_tax]')
			{
				$table_2	= 'sys_tax_type';
				$tax_list	= $this->common_model->get_table($table_2);
				foreach ($tax_list as $tax_list_key => $tax_list_arr)
				{
					$opt_key 			= $tax_list[$tax_list_key]['code'];
					$opt_val 			= $tax_list[$tax_list_key]['code'].':'.$tax_list[$tax_list_key]['percent'];
					$options[$opt_key]	= $opt_val;
					$addOnStyle			= 'style="font-size:11px; width: 100%; text-align:left;"';

				}
				$wp_input = $wp_header.form_dropdown($name_or_id, $options, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			else if(($name_or_id == 'sys_config[period_to_suspend]') || ($name_or_id == 'sys_config[period_to_termination]'))
			{
				$add_on_back = '<div class="input-group-addon" style="font-size:10px; padding:5px;">mths</div>';
				$input_arr['placeholder'] = '';
				$wp_input = $wp_header.form_input($input_arr).$add_on_back.$wp_footer ;
			}
			else if(($name_or_id == 'sys_config[reminder_overdue]') || ($name_or_id == 'sys_config[reminder_suspension]') || ($name_or_id == 'sys_config[suspension_grace_period]') || ($name_or_id == 'sys_config[reminder_contract]') || ($name_or_id == 'sys_config[reminder_notice]') || ($name_or_id == 'sys_config[reminder_notice2]') || ($name_or_id == 'sys_config[reminder_overdue2]'))
			{
				$add_on_back = '<div class="input-group-addon" style="font-size:10px; padding:5px;">days</div>';
				$input_arr['placeholder'] = '';
				$wp_input = $wp_header.form_input($input_arr).$add_on_back.$wp_footer ;
			}
			elseif( $name_or_id == 'sys_config[date_format]' ){

				// $options = array("d-m-Y"=>"DD-MM-YYYY", "Y-m-d"=>"YYYY-MM-DD");
				$option_date['d-m-Y'] = 'DD-MM-YYYY ['.date( 'd-m-Y' ).']';
				$option_date['Y-m-d'] = 'YYYY-MM-DD ['.date( 'Y-m-d' ).']';
				$option_date['date_custom_1'] = 'DD MMM YYYY';

				$addOnStyle			= 'style="font-size:11px; width: 100%;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_date, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[date_format_printing]' ){
				// $options = array("d-m-Y"=>"DD-MM-YYYY", "Y-m-d"=>"YYYY-MM-DD");
				$option_date['d-m-Y'] = 'DD-MM-YYYY';
				$option_date['Y-m-d'] = 'YYYY-MM-DD';
				$option_date['date_custom_1'] = 'DD MMM YYYY';

				$addOnStyle			= 'style="font-size:11px; width: 100%;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_date, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[datetime_format]' ){
				// $options = array("d-m-Y h:i:s A"=>"DD-MM-YYYY HH:MM:SS AM/PM", "Y-m-d h:i:s A"=>"YYYY-MM-DD HH:MM:SS AM/PM");
				$option_datetime['d-m-Y h:i:s A'] = 'DD-MM-YYYY HH:MM:SS AM/PM';
				$option_datetime['Y-m-d h:i:s A'] = 'YYYY-MM-DD HH:MM:SS AM/PM';
				$option_datetime['datetime_custom_1'] = 'DD MMM YYYY | HH:MM:SS AM/PM';

				$addOnStyle			= 'style="font-size:11px; width: 100%;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_datetime, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[bills_footer]' ){
				$input_arr['class'] = 'tinymce-editor';
				$wp_input = $wp_header.form_textarea($input_arr).$wp_footer ;
			}
			elseif( $name_or_id == 'sys_config[gst_reg_no]' ){
				$input_arr['placeholder']	= 'Tax Reg No';
				$wp_input = "<div class='input-group $add_has_error'><span class='input-group-addon' style='$addOnStyle'>Tax Reg No</span>".form_input($input_arr).$wp_footer ;
			}
			elseif( $name_or_id == 'sys_config[fpx_enable]' ){
				$option_fpx_enable['1'] = 'ENABLED';
				$option_fpx_enable['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_fpx_enable, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[upload_letter_of_authority]' ){
				$option_upload_letter_of_authority['1'] = 'ENABLED';
				$option_upload_letter_of_authority['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_upload_letter_of_authority, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[auto_skip_so]' ){
				$option_auto_skip_so['1'] = 'ENABLED';
				$option_auto_skip_so['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_auto_skip_so, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[whatsapp_notification]' ){
				$option_whatsapp_notification['1'] = 'ENABLED';
				$option_whatsapp_notification['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_whatsapp_notification, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif(  ( $name_or_id == 'sys_config[relocation_sop]' ) || ( $name_or_id == 'sys_config[termination_sop]' ) || ( $name_or_id == 'sys_config[audit_copy]' ) ){

				$option_yes_no['1'] = 'ENABLED';
				$option_yes_no['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id, $option_yes_no, set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;

			}
			elseif( $name_or_id == 'sys_config[push_enabled]' ){
				$option_push_enabled['1'] = 'ENABLED';
				$option_push_enabled['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id,$option_push_enabled,set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			elseif( $name_or_id == 'sys_config[adjustment_approval]' ){
				$option_adjustment_approval['1'] = 'ENABLED';
				$option_adjustment_approval['0'] = 'DISABLED';

				$addOnStyle	= 'style="font-size:11px; width: 100%; font-weight: bold;"';
				$wp_input = $wp_header.form_dropdown($name_or_id,$option_adjustment_approval,set_value($name_or_id, $input_val),$addOnStyle).$wp_footer;
			}
			else
			{
				$wp_input = $wp_header.form_input($input_arr).$wp_footer ;
			}

			if($input_cat == 'email')
			{
				$inputs['email'][] = $wp_input;
			}
			elseif($input_cat == 'suspend')
			{
				$inputs['suspend'][] = $wp_input;
			}
			elseif($input_cat == 'sms' )
			{
				$inputs['sms'][] = $wp_input ; 
			}
			elseif( $input_cat == 'radius' )
			{
				$inputs['radius'][] = $wp_input ; 
			}
			elseif( $input_cat == 'bills' )
			{
				$inputs['bills'][] = $wp_input ; 
			}
			elseif( $input_cat == 'einvoice' )
			{
				$inputs['einvoice'][] = $wp_input ; 
			}
			elseif( $input_cat == 'ifca' )
			{
				$inputs['ifca'][] = $wp_input ; 
			}
			elseif( $input_cat == 'app' )
			{
				$inputs['app'][] = $wp_input ; 
			}
			elseif( $input_cat == 'fpx' )
			{
				$inputs['fpx'][] = $wp_input ; 
			}
			elseif( $input_cat == 'registration' )
			{
				$inputs['registration'][] = $wp_input ; 
			}
			elseif( $input_cat == 'dealer' )
			{
				$inputs['dealer'][] = $wp_input ; 
			}
			elseif( $input_cat == 'profile' )
			{
				$inputs['profile'][] = $wp_input ; 
			}
			elseif( $input_cat == 'push' )
			{
				$inputs['push'][] = $wp_input ; 
			}
			else
			{
				$inputs['main'][] = $wp_input;
			}
		}

		$button_key 				= 'processtype[cancel]';
		$button_icon				= '<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>';
		$button_attr['class'] 		= 'btn btn-danger';
		$button_attr['id'] 			= $button_key;
		$button_attr['name'] 		= $button_key;
		$button_attr['type']		= 'button';
		$button_attr['value'] 		= 'cancel';
		$button_attr['onclick'] 	= "window.location='".base_url('registration')."';" ;
		$button_attr['data-toggle'] = 'btCancel';
		$button_attr['title'] 		= 'Discard Changes';
		$button_attr['content'] 	= $button_icon.' Cancel';
		$buttons[] 					= form_button($button_attr);


		$button_key 				= 'processtype[save]';
		$button_icon				= '<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i></i>';
		$button_attr['class'] 		= 'btn btn-success';
		$button_attr['id'] 			= $button_key;
		$button_attr['name'] 		= $button_key;
		$button_attr['type']		="submit";
		$button_attr['value']		="save";
		//~ $button_attr['onclick'] 	= "window.location='http://localhost/ip/itelco/building';" ;
		$button_attr['data-toggle'] = $button_key;
		$button_attr['title'] 		= 'Save record';
		$button_attr['content'] 	= $button_icon.' Save';
		$buttons[] 					= form_button($button_attr);

		$panel_data['panel_title']  = 'System Config Management';
		$content_data['fm_open']	= form_open('settings/save_config');
		$content_data['fm_close']	= form_close();

		$content_data['inputs'] 	= (empty($inputs))?'':$inputs;
		$content_data['buttons'] 	= (empty($buttons))?'':$buttons;
		$content_data['msg']		= $this->msg;


		$this->load->view('templates/header'		,$this->vars);
		$this->load->view('templates/menu'			,$this->menu);
		$this->load->view('templates/panel_header'	,$panel_data);
		$this->load->view('settings/sys_config'		,$content_data);
		$this->load->view('templates/panel_footer'	,$panel_data);
		$this->load->view('templates/footer');
	}

    public function sys_tax_type()
	{
		check_acl('tax');
		$seg 			= $this->get_segment();
		$controller		= $seg[0];
		$method 		= $seg[1];
		$param_1 		= $seg[2];

		if(!empty($param_1)) redirect('settings/index');

		$table		= 'sys_tax_type';
		$fields 	= $this->db->list_fields($table);
		$field_data	= $this->common_model->get_table($table);

		$inputs = array();
		foreach($field_data as $key => $input)
		{
			$code 			= $field_data[$key]['code'];
			$percent		= $field_data[$key]['percent'];
			$name_or_id 	= $table.'['.$code.']';
			$placeholder	= $code ;


			// ========= preset value ==================
			$input_arr['id']			= $name_or_id;
			$input_arr['name']			= $name_or_id;
			$input_arr['type']			= 'text';
			$input_arr['value']			= set_value($name_or_id, $percent);
			$input_arr['placeholder']	= $placeholder;
			$input_arr['step']			= '0.1';
			$input_arr['style']			= 'width:100%;padding-right:0.5em;';
			$input_arr['class']			= 'form-control text-right';

			$input_key 		= $code;
			$addOnStyle		= "font-size:10px; min-width:45px; text-align:right;";
			$has_error 		= form_error($name_or_id);
			$add_has_error	= (empty($has_error))?'':'has-error';
			$wp_header		= "<div class='input-group $add_has_error' style='padding-right:13em;'><span class='input-group-addon' style='$addOnStyle'>$placeholder</span>";
			$wp_error		= form_error($name_or_id, "<label for='$name_or_id' class='$input_key text-danger' style='padding-left:0px;'>", "</label>");
			$wp_footer		= "</div>";
			$wp_footer 		.= $wp_error;

			$inputs['main'][] = $wp_input	= $wp_header.form_input($input_arr).$wp_footer;
		}


		// $button_key 				= 'processtype[cancel]';
		// $button_icon				= '<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>';
		// $button_attr['class'] 		= 'btn btn-danger';
		// $button_attr['id'] 			= $button_key;
		// $button_attr['name'] 		= $button_key;
		// $button_attr['type']		= 'button';
		// $button_attr['value'] 		= 'cancel';
		// $button_attr['onclick'] 	= "window.location='http://localhost/ip/itelco/building';" ;
		// $button_attr['data-toggle'] = 'btCancel';
		// $button_attr['title'] 		= 'Discard Changes';
		// $button_attr['content'] 	= $button_icon.' Cancel';
		// $buttons[] 					= form_button($button_attr);

		$button_key 				= 'processtype[save]';
		$button_icon				= '<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i></i>';
		$button_attr['class'] 		= 'btn btn-success';
		$button_attr['id'] 			= $button_key;
		$button_attr['name'] 		= $button_key;
		$button_attr['type']		="submit";
		$button_attr['value']		="save";
		//~ $button_attr['onclick'] 	= "window.location='http://localhost/ip/itelco/building';" ;
		$button_attr['data-toggle'] = $button_key;
		$button_attr['title'] 		= 'Save record';
		$button_attr['content'] 	= $button_icon.' Save';
		$buttons[] 					= form_button($button_attr);

		$panel_data['panel_title']  = 'Tax Type Management';
		$content_data['fm_open']	= form_open('settings/save_tax');;
		$content_data['fm_close']	= form_close();

		$content_data['inputs'] 	= (empty($inputs))?'':$inputs;
		$content_data['buttons'] 	= (empty($buttons))?'':$buttons;
		$content_data['msg']		= $this->msg;

		$this->load->view('templates/header'		,$this->vars);
		$this->load->view('templates/menu'			,$this->menu);
		$this->load->view('templates/panel_header'	,$panel_data);
		$this->load->view('settings/sys_tax_type'	,$content_data);
		$this->load->view('templates/panel_footer'	,$panel_data);
		$this->load->view('templates/footer');
	}

	public function sys_ledger_account(){
		$this->load->model('package_model');
		check_acl('ledger');
		$data['page_title'] 	= 'Manage Ledger Account';
		$data['form_action']	= base_url('settings/save_ledger_account');
		$customer_categories    = $this->common_model->get_category_list();
		$payment_sources 		= $this->common_model->get_payment_source_list();
		$bill_types 			= $this->common_model->get_bill_type_list();
		//package
		$packages = $this->package_model->get_package_listing('', 0,9999999,'');
		//agent
		$agents = $this->common_model->get_dealer_list();
		$data['msg'] 			= $this->msg;

		$all_ledgers = $this->common_model->get_all_ledger_codes();
		//_debug_array($all_ledgers);
		//exit;
		
		$z=0;
		foreach($payment_sources AS $source)
		{
			$payment_sources[$z]['name'] = str_replace( " " , "_" , $payment_sources[$z]['name'] ) ;
			foreach($customer_categories AS $category )
			{
				$payment_sources[$z][$category['category_code']]['CR'] = ( isset($all_ledgers['PAYMENT'][$category['category_code']][$source['payment_source_id']]['CR']) ? $all_ledgers['PAYMENT'][$category['category_code']][$source['payment_source_id']]['CR'] : "" );

				$payment_sources[$z][$category['category_code']]['DR'] = ( isset($all_ledgers['PAYMENT'][$category['category_code']][$source['payment_source_id']]['DR']) ? $all_ledgers['PAYMENT'][$category['category_code']][$source['payment_source_id']]['DR'] : "" );

				/*$ledgers = $this->common_model->get_ledger_account_codes('PAYMENT', $source['payment_source_id'], $category['category_code']);
				foreach( $ledgers AS $key => $ledger ){
					if( $ledger['credit_debit'] == 'CR' || $ledger['credit_debit'] == 'DR' ){
						$payment_sources[$z][$category['category_code']][$ledger['credit_debit']] = ($ledger['ledger_account_code']!='')?$ledger['ledger_account_code']:'' ;
					}
				}*/
			}
			$z++;
		}
		
		$z=0;
		foreach($bill_types AS $type)
		{
			//$bill_types[$z]['name'] = str_replace( " " , "_" , $bill_types[$z]['name'] ) ;
			foreach($customer_categories AS $category )
			{
				$bill_types[$z][$category['category_code']]['CR'] = ( isset($all_ledgers['BILL'][$category['category_code']][$type['bill_type_id']]['CR']) ? $all_ledgers['BILL'][$category['category_code']][$type['bill_type_id']]['CR'] : "" );

				$bill_types[$z][$category['category_code']]['DR'] = ( isset($all_ledgers['BILL'][$category['category_code']][$type['bill_type_id']]['DR']) ? $all_ledgers['BILL'][$category['category_code']][$type['bill_type_id']]['DR'] : "" );

				/*$ledgers = $this->common_model->get_ledger_account_codes('BILL', $type['bill_type_id'], $category['category_code']);
				foreach( $ledgers AS $ledger ){
					if( $ledger['credit_debit'] == 'CR' || $ledger['credit_debit'] == 'DR' ){
					$bill_types[$z][$category['category_code']][$ledger['credit_debit']] = isset($ledger['ledger_account_code'])?$ledger['ledger_account_code']:'' ;
					}
				}*/
			}
			$z++;
		}

		$z=0;
		foreach($packages['row'] AS $package)
		{
			foreach($customer_categories AS $category )
			{
				$packages['row'][$z][$category['category_code']]['CR'] = ( isset($all_ledgers['PACKAGE'][$category['category_code']][$package['package_no']]['CR']) ? $all_ledgers['PACKAGE'][$category['category_code']][$package['package_no']]['CR'] : "" );

				$packages['row'][$z][$category['category_code']]['DR'] = ( isset($all_ledgers['PACKAGE'][$category['category_code']][$package['package_no']]['DR']) ? $all_ledgers['PACKAGE'][$category['category_code']][$package['package_no']]['DR'] : "" ); 

			}
			$z++;
		}

		//Agent
		$z=0;
		foreach($agents as $agent)
		{
			$agents[$z]['r']['CR'] = ( isset($all_ledgers['AGENT']['r'][$agent['dealer_no']]['CR']) ? $all_ledgers['AGENT']['r'][$agent['dealer_no']]['CR'] : "" );

			$agents[$z]['r']['DR'] = ( isset($all_ledgers['AGENT']['r'][$agent['dealer_no']]['DR']) ? $all_ledgers['AGENT']['r'][$agent['dealer_no']]['DR'] : "" ); 

			$z++;
		}
		
		$data['page_title'] 	= 'Manage Ledger Account';
		$data['form_action']	= base_url('settings/save_ledger_account');
		$data['msg'] 			= $this->msg;
		$data['customer_categories'] = $customer_categories ;
		$data['payment_sources'] = $payment_sources ;
		$data['bill_types'] = $bill_types ;
		$data['agents'] = $agents;
		$data['packages'] = $packages['row'] ;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_ledger_account',$data);
		$this->load->view('templates/footer');
	}

	public function save_ledger_account(){
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/sys_ledger_account', 'error_keys' => array());
		$this->load->model('package_model');
		if($this->input->post())
		{
			
			$customer_categories    = $this->common_model->get_category_list();
			$payment_sources 		= $this->common_model->get_payment_source_list();
			$bill_types 			= $this->common_model->get_bill_type_list();	
			//package
			$packages = $this->package_model->get_package_listing('', 0,9999999,'');	

			$agents = $this->common_model->get_dealer_list();	
			
			$glacc_p = $this->input->post('glacc_p');
			$z = 0 ;
			foreach($payment_sources AS $source)
			{
				//$payment_sources[$z]['name'] = str_replace( " " , "_" , $payment_sources[$z]['name'] ) ;
				foreach($customer_categories AS $category )
				{
					$acc_code_dr = $glacc_p[$payment_sources[$z]['payment_source_id']][$category['category_code']]['DR']; 
					$this->setting_model->ledger_update($acc_code_dr, $category['category_code'], 'PAYMENT', $source['payment_source_id'] , 'DR' ); 
					
					$acc_code_cr = $glacc_p[$payment_sources[$z]['payment_source_id']][$category['category_code']]['CR']; 
					$this->setting_model->ledger_update($acc_code_cr, $category['category_code'], 'PAYMENT', $source['payment_source_id'] , 'CR' ); 
					//$this->setting_model->ledger_update($acc_code, $category['category_code'], 'PAYMENT', $source['payment_source_id'] ); 
				}
				$z++;
			}
			
			$glacc_b = $this->input->post('glacc_b');
			$z = 0 ;
			foreach($bill_types AS $bill)
			{
				//$bill_types[$z]['name'] = str_replace( " " , "_" , $bill_types[$z]['name'] ) ;
				foreach($customer_categories AS $category )
				{
					$acc_code_dr = $glacc_b[$bill_types[$z]['bill_type_id']][$category['category_code']]['DR'];
					$this->setting_model->ledger_update($acc_code_dr, $category['category_code'], 'BILL', 
														$bill['bill_type_id'] , 'DR' ); 
														
					$acc_code_cr = $glacc_b[$bill_types[$z]['bill_type_id']][$category['category_code']]['CR'];
					$this->setting_model->ledger_update($acc_code_cr, $category['category_code'], 'BILL', 
														$bill['bill_type_id'] , 'CR' );
				}
				$z++;
			}

			$glacc_k = $this->input->post('glacc_k');
			$z = 0 ;
			foreach($packages['row'] AS $package)
			{
				//$bill_types[$z]['name'] = str_replace( " " , "_" , $bill_types[$z]['name'] ) ;
				foreach($customer_categories AS $category )
				{
					$acc_code_dr = $glacc_k[$packages['row'][$z]['package_no']][$category['category_code']]['DR'];
					$this->setting_model->ledger_update($acc_code_dr, $category['category_code'], 'PACKAGE', 
														$package['package_no'] , 'DR' ); 
														
					$acc_code_cr = $glacc_k[$packages['row'][$z]['package_no']][$category['category_code']]['CR'];
					$this->setting_model->ledger_update($acc_code_cr, $category['category_code'], 'PACKAGE', 
														$package['package_no'] , 'CR' );
				}
				$z++;
			}

			$glacc_a = $this->input->post('glacc_a');
			$z = 0 ;
			foreach($agents as $agent)
			{
				$acc_code_dr = $glacc_a[$agent['dealer_no']]['r']['DR'];
				$this->setting_model->ledger_update($acc_code_dr, 'r', 'AGENT', 
													$agent['dealer_no'] , 'DR' ); 
													
				$acc_code_cr = $glacc_a[$agent['dealer_no']]['r']['CR'];
				$this->setting_model->ledger_update($acc_code_cr, 'r', 'AGENT', 
													$agent['dealer_no'] , 'CR' );
				$z++;
			}
			
			$customers = $this->input->post('customer');
			foreach($customer_categories AS $category )
			{
				$this->setting_model->customer_category_ledger_update( 	$category['category_code'], 
																		$customers[$category['category_code']] );
			}
			
			
			// $this->msg['msg'] = "Record Saved!";
			$this->session->set_flashdata("msg", 'Record Saved!');
			$ajax_return['status'] = 'SUCC';
			
		}
		else
		{
			// $this->msg['error_msg'] = "Invalid access";
			$ajax_return['err_msg'] = "Invalid access";
		}
		echo json_encode($ajax_return);
		return false;
		// redirect('settings/sys_ledger_account');
	}

    public function index()
	{
		redirect('settings/sys_config');
	}

	function z_debug()
	{
		echo CI_VERSION.'<pre>';
		$sess = $this->session->userdata;
		echo '<br>ci sess';
		print_r($sess);
		echo '<br>$_SESS';
		print_r($_SESSION);
	}

	function bill_type_management()
	{
		check_acl('bill_type');
		
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'sel_code' => 'all',
			'sel_adjust_type' => 'all'
		];
		$return = get_filtered_ajax_data('bill_type_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->bill_type_management_rows(1);			
		
		$data['page_title'] 		= 'Bill Type Management';
		$data['form_action'] 		= base_url('settings/bill_type_management');	
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;
		$data['tax_code_list'] 		= $this->setting_model->get_tax_code_list();

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_bill_type', $data);
		$this->load->view('templates/footer');
	}

	function bill_type_management_rows($returnOnly = 0) 
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => '',
			'sel_code' => 'all',
			'sel_adjust_type' => 'all'
		];
		$return = get_filtered_ajax_data('bill_type_management_filter', $default_data, $post_data);

		$data = $return['data'];

		if (empty($data['page_item_no'])) $data['page_item_no'] = 0;

		$result	= $this->setting_model->get_bill_type_listing($data['txt_search'], $data['sel_adjust_type'], $data['sel_code'], $data['page_item_no'], $_SESSION['config']['max_page_item']);

		$data['pagination'] = paginationSettingsAjax('', $result['total_row'], $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['row_data'] = $result['row'];

		$html = $this->parser->parse('settings/sys_bill_type_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_bill_type()
	{
		$data['input']				= $this->setting_model->get_bill_type();

		$data['tax_code_list'] 		= $this->setting_model->get_tax_code_list();

		$data['class_code_list'] = $this->class_list;
		$data['tax_type_list'] = $this->tax_type_list;

		$data['page_title'] 		= 'Bill Type Settings';
		$data['form_action'] 		= base_url('settings/save_bill_type');
		$data['msg'] 				= $this->msg;

		$data['is_lock'] = 0;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_bill_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_bill_type($bill_type_id)
	{
		$data['input']				= $this->setting_model->get_bill_type($bill_type_id);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Bill Type not found!');
			redirect('settings/bill_type_management');
		}

		$data['tax_code_list'] 		= $this->setting_model->get_tax_code_list();

		$data['class_code_list'] = $this->class_list;
		$data['tax_type_list'] = $this->tax_type_list;
		
		$data['page_title'] 		= 'Edit Bill Type';
		$data['form_action'] 		= base_url('settings/save_bill_type');
		$data['msg'] 				= $this->msg;

		$data['is_lock'] = in_array($bill_type_id ?? 0, $this->common_model->get_locked_bill_type_ids() ?? []) ? 1 : 0;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_bill_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_bill_type()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/bill_type_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('bill_type_id') != '')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_type($this->input->post('bill_type_id'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This bill type is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/bill_type_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/bill_type_management/");
			}
			else{
				$result = $this->setting_model->delete_bill_type($this->input->post());
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/bill_type_management';
				if(empty($result)) $this->session->set_flashdata("msg", 'Record Deleted!');
				else $this->session->set_flashdata("msg", 'Record Deleted!');
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/bill_type_management/");
			}
		}
		else{
			$this->set_form_validation('save_bill_type');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('bill_type_id')=='')  $ajax_return['url'] = 'settings/add_bill_type';				
				else  $ajax_return['url'] = 'settings/edit_bill_type/' . $this->input->post('bill_type_id');	
				
				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				if($this->input->post('bill_type_id') == '')
				{
					//insert
					$result = $this->setting_model->insert_bill_type($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'Bill Type Added!');			
					else $this->session->set_flashdata("msg", 'Bill Type Added!');
				
					// redirect(base_url('settings/bill_type_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_bill_type($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'Bill Type Updated!');			
					else $this->session->set_flashdata("msg", 'Bill Type Updated!');
				
					// redirect(base_url('settings/bill_type_management'));
				}
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function nas_setting_management(){
		check_acl('nas_address');
		
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('nas_setting_management_filter', $default_data, $post_data);

		$data = $return['data'];	
		
		$row_html = $this->nas_setting_management_rows(1);
		
		$data['page_title'] 		= 'NAS IP Management';
		$data['form_action'] 		= base_url('settings/nas_setting_management');	
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_nas_address', $data);
		$this->load->view('templates/footer');
	}

	function nas_setting_management_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('nas_setting_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$result	= $this->setting_model->get_nas_setting_listing($data['txt_search']);

		$data['row_data'] = $result['row'];
		$data['pagination'] = paginationSettings('', $result['total_row'], $data['page_item_no'], $_SESSION['config']['max_page_item']);

		$html = $this->parser->parse('settings/sys_nas_address_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_nas_address()
	{
		$data['input']				= $this->setting_model->get_nas_address();
		$data['page_title'] 		= 'NAS IP Management';
		$data['form_action'] 		= base_url('settings/save_nas_address');
		$data['msg'] 				= $this->msg;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_nas_address_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_nas_address($id)
	{
		$data['input']				= $this->setting_model->get_nas_address($id);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'NAS address not found!');
			redirect('settings/nas_setting_management');
		}

		$data['page_title'] 		= 'Edit NAS Address';
		$data['form_action'] 		= base_url('settings/save_nas_address');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_nas_address_detail',$data);
		$this->load->view('templates/footer');
	}

	function inactive_nas_address($nas_id)
	{

		if ($nas_id != '') {
			
			$this->setting_model->update_nas_address_status( $nas_id, 0  );
			$this->session->set_flashdata("warning_msg", 'NAS Address inactived!');
			
		}else{
			$this->session->set_flashdata("warning_msg", 'NAS Address not found!');
		}

		redirect('settings/nas_setting_management');
	}

	function active_nas_address($nas_id)
	{

		if ($nas_id != '') {
			
			$this->setting_model->update_nas_address_status( $nas_id, 1  );
			$this->session->set_flashdata("warning_msg", 'NAS Address activated!');
			
		}else{
			$this->session->set_flashdata("warning_msg", 'NAS Address not found!');
		}

		redirect('settings/nas_setting_management');
	}

	function save_nas_address()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/nas_setting_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('id') != '')
		{			
			//delete - check being used ?
			/*$result = $this->setting_model->validate_being_used_payment($this->input->post('payment_source_id'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This bill type is being used, not allow to delete!');
				redirect("settings/payment_type_management/");
			}
			else{*/
				$result = $this->setting_model->delete_nas_address($this->input->post());
				if(empty($result)) $this->session->set_flashdata("msg", 'Record Deleted!');
				else $this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/nas_setting_management");
			//}
		}
		else{
			$this->set_form_validation('save_nas_address');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('id')=='') $ajax_return['url'] = 'settings/add_nas_address';				
				else $ajax_return['url'] = 'settings/edit_nas_address/' . $this->input->post('nas_id');
				echo json_encode($ajax_return);
				return false;				
			}
			else
			{
				if($this->input->post('id') == '')
				{
					//insert
					$result = $this->setting_model->insert_nas_address($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'NAS Address Added!');			
					else $this->session->set_flashdata("msg", 'NAS Address Added!');
				
					// redirect(base_url('settings/nas_setting_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_nas_address($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'NAS Address Updated!');			
					else $this->session->set_flashdata("msg", 'NAS Address Updated!');
				
					// redirect(base_url('settings/nas_setting_management'));
				}
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function payment_type_management(){
		check_acl('payment_type');
		
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('payment_type_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->payment_type_management_rows(1);

		$data['page_title'] 		= 'Payment Type Management';
		$data['form_action'] 		= base_url('settings/payment_type_management');	
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_payment_type', $data);
		$this->load->view('templates/footer');
	}

	function payment_type_management_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('payment_type_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$result = $this->setting_model->get_payment_type_listing($data['txt_search']);

		$data['pagination'] = paginationSettingsAjax('', $result['total_row'],$data['page_item_no'], $_SESSION['config']['max_page_item'] );
		$data['row_data'] = $result['row'];

		$html = $this->parser->parse('settings/sys_payment_type_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_payment_type()
	{
		$data['input']				= $this->setting_model->get_payment_type();
		$data['page_title'] 		= 'Payment Type Settings';
		$data['form_action'] 		= base_url('settings/save_payment_type');
		$data['msg'] 				= $this->msg;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_payment_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_payment_type($payment_source_id)
	{
		$data['input']				= $this->setting_model->get_payment_type($payment_source_id);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Payment Type not found!');
			redirect('settings/payment_type_management');
		}

		$data['page_title'] 		= 'Edit Payment Type';
		$data['form_action'] 		= base_url('settings/save_payment_type');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_payment_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function inactive_payment_type($payment_source_id)
	{

		if ($payment_source_id != '') {
			
			$this->setting_model->update_payment_type_status( $payment_source_id, 0  );
			$this->session->set_flashdata("warning_msg", 'Payment Type inactived!');
			
		}else{
			$this->session->set_flashdata("warning_msg", 'Payment Type not found!');
		}

		redirect('settings/payment_type_management');
	}

	function active_payment_type($payment_source_id)
	{

		if ($payment_source_id != '') {
			
			$this->setting_model->update_payment_type_status( $payment_source_id, 1  );
			$this->session->set_flashdata("warning_msg", 'Payment Type activated!');
			
		}else{
			$this->session->set_flashdata("warning_msg", 'Payment Type not found!');
		}

		redirect('settings/payment_type_management');
	}

	function save_payment_type()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/payment_type_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('payment_source_id') != '')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_payment($this->input->post('payment_source_id'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This bill type is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/payment_type_management/");
			}
			else{
				$result = $this->setting_model->delete_payment_type($this->input->post());
				if(empty($result)) $this->session->set_flashdata("msg", 'Record Deleted!');
				else $this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/payment_type_management/");
			}
		}
		else{
			$this->set_form_validation('save_payment_type');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('payment_source_id')=='')  $ajax_return['url'] = 'settings/add_payment_type';				
				else $ajax_return['url'] = 'settings/edit_payment_type/' . $this->input->post('payment_source_id');				
				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				if($this->input->post('payment_source_id') == '')
				{
					//insert
					$result = $this->setting_model->insert_payment_type($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'Payment Type Added!');			
					else $this->session->set_flashdata("msg", 'Payment Type Added!');
				
					// redirect(base_url('settings/payment_type_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_payment_type($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'Payment Type Updated!');			
					else $this->session->set_flashdata("msg", 'Payment Type Updated!');
				
					// redirect(base_url('settings/payment_type_management'));
				}
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function equipment_type_management(){
		check_acl('config');
		
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('equipment_type_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->equipment_type_management_rows(1);

		$data['page_title'] 		= 'Equipment Type Management';
		$data['form_action'] 		= base_url('settings/equipment_type_management');	
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_equipment_type', $data);
		$this->load->view('templates/footer');
	}

	function equipment_type_management_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('equipment_type_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$result = $this->setting_model->get_equipment_type_listing($data['txt_search']);

		$data['pagination'] = paginationSettingsAjax('', $result['total_row'],$data['page_item_no'], $_SESSION['config']['max_page_item'] );
		$data['row_data'] = $result['row'];

		$html = $this->parser->parse('settings/sys_equipment_type_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_equipment_type()
	{
		$data['input']				= $this->setting_model->get_equipment_type();
		$data['page_title'] 		= 'Equipment Type Settings';
		$data['form_action'] 		= base_url('settings/save_equipment_type');
		$data['msg'] 				= $this->msg;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_equipment_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_equipment_type($equipment_type_id)
	{
		$data['input']				= $this->setting_model->get_equipment_type($equipment_type_id);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Equipment Type not found!');
			redirect('settings/equipment_type_management');
		}

		$data['page_title'] 		= 'Edit Equipment Type';
		$data['form_action'] 		= base_url('settings/save_equipment_type');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_equipment_type_detail',$data);
		$this->load->view('templates/footer');
	}

	function inactive_equipment_type($equipment_type_id)
	{

		if ($equipment_type_id != '') {
			
			$this->setting_model->update_equipment_type_status( $equipment_type_id, 0  );
			$this->session->set_flashdata("warning_msg", 'Equipment Type inactived!');
			
		}else{
			$this->session->set_flashdata("warning_msg", 'Equipment Type not found!');
		}

		redirect('settings/equipment_type_management');
	}

	function active_equipment_type($equipment_type_id)
	{

		if ($equipment_type_id != '') {
			
			$this->setting_model->update_equipment_type_status( $equipment_type_id, 1  );
			$this->session->set_flashdata("warning_msg", 'Equipment Type activated!');
			
		}else{
			$this->session->set_flashdata("warning_msg", 'Equipment Type not found!');
		}

		redirect('settings/equipment_type_management');
	}

	function save_equipment_type()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/equipment_type_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('equipment_type_id') != '')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_equipment_type($this->input->post('equipment_type_id'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This equipment type is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/payment_type_management/");
			}
			else{
				$result = $this->setting_model->delete_equipment_type($this->input->post());
				if(empty($result)) $this->session->set_flashdata("msg", 'Record Deleted!');
				else $this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/payment_type_management/");
			}
		}
		else{
			$this->set_form_validation('save_equipment_type');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('equipment_type_id')=='')  $ajax_return['url'] = 'settings/add_equipment_type';				
				else $ajax_return['url'] = 'settings/edit_equipment_type/' . $this->input->post('equipment_type_id');				
				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				if($this->input->post('equipment_type_id') == '')
				{
					//insert
					$result = $this->setting_model->insert_equipment_type($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'Equipment Type Added!');			
					else $this->session->set_flashdata("msg", 'Equipment Type Added!');
				
					// redirect(base_url('settings/payment_type_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_equipment_type($this->input->post());
					if(empty($result)) $this->session->set_flashdata("msg", 'Equipment Type Updated!');			
					else $this->session->set_flashdata("msg", 'Equipment Type Updated!');
				
					// redirect(base_url('settings/payment_type_management'));
				}
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function asset_category_management(){
		check_acl('asset');

		$page_item_no 				= ($this->input->post('page_item_no') == '')? 0 : $this->input->post('page_item_no');
		$txt_search 				= $this->input->post('txt_search');
		
		$this->load->model('common_model');
		
		$result						= $this->common_model->get_asset_category_list();
		
		$data['pagination'] 		= paginationSettings('', count( $result ) );
		$data['page_title'] 		= 'Asset Category Management';
		$data['form_action'] 		= base_url('settings/asset_category_management');
		$data['row_data'] 			= $result;
		$data['txt_search'] 		= $txt_search;		
		$data['msg'] 				= $this->msg;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_asset_category', $data);
		$this->load->view('templates/footer');
	}

	function add_asset_category(){
		$data['input']				= $this->setting_model->get_asset_category();
		$data['page_title'] 		= 'Add Asset Category';
		$data['form_action'] 		= base_url('settings/save_asset_category');
		$data['msg'] 				= $this->msg;

		$data['contacts_pic'] = array();
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_asset_category_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_asset_category($category_idx)
	{
		$data['input'] = $this->setting_model->get_asset_category($category_idx);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Asset category not found!');
			redirect('settings/asset_category_management');
		}

		$data['page_title'] 		= 'Edit Asset Category';
		$data['form_action'] 		= base_url('settings/save_asset_category');
		$data['msg'] 				= $this->msg;

		$this->load->model('asset_model');
		$data['contacts_pic'] = $this->asset_model->get_category_pic($category_idx);
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_asset_category_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_asset_category()
	{
		// $post_data = $this->input->post();
		// _debug_array($post_data);
		// exit;
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/asset_category_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('category_idx') != '0')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_asset_category($this->input->post('category_idx'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This asset category is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_category_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/asset_category_management/");
			}else{
				$result = $this->setting_model->delete_asset_category($this->input->post());
				
				$this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_category_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/asset_category_management/");
			}
		}
		else{
			$this->set_form_validation('save_asset_category');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				
				if ($this->input->post('category_idx')=='0')  $ajax_return['url'] = 'settings/add_asset_category';
				else  $ajax_return['url'] = 'settings/edit_asset_category/' . $this->input->post('category_idx');

				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				$post_data = $this->input->post();
				//_debug_array($post_data); exit;
				if($this->input->post('category_idx') == '0')
				{
					//insert
					$result = $this->setting_model->insert_asset_category($this->input->post());
					$post_data['category_idx'] = $this->db->insert_id();
					$this->session->set_flashdata("msg", 'Asset category added!');
				}
				else 
				{
					//update
					$result = $this->setting_model->update_asset_category($this->input->post());
					$this->session->set_flashdata("msg", 'Asset category updated!');
				}

				//save contacts setting
				$this->load->model('asset_model');
				$this->asset_model->delete_contacts_setting($this->input->post('category_idx'));
				//$post_data = $this->input->post();
				if (isset($post_data['user_id'])) {
					foreach ($post_data['user_id'] as $key => $val) {
						if($val === ''){
							continue;
						}
						$arr = array();
						$arr['category_idx'] = $post_data['category_idx'];
						$arr['user_id'] = $post_data['user_id'][$key];
						$arr['email'] = $post_data['email'][$key];
						$arr['phone'] = $post_data['phone'][$key];
						$arr['telegram_id'] = $post_data['telegram_id'][$key];
						$this->asset_model->save_contacts_setting($arr);
					}
				}

				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_category_management';
				echo json_encode($ajax_return);
				return false;

				// redirect(base_url('settings/asset_category_management'));
			}
		}
	}

	function asset_code_management(){
		check_acl('asset');

		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('asset_code_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->asset_code_management_rows(1);

		$data['page_title'] 		= 'Asset Code Management';
		$data['form_action'] 		= base_url('settings/asset_code_management');		
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_asset_code', $data);
		$this->load->view('templates/footer');
	}

	function asset_code_management_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('asset_code_management_filter', $default_data, $post_data);

		$data = $return['data'];

		if (empty($data['page_item_no'])) $data['page_item_no'] = 0;

		$this->load->model('common_model');
		
		$result	= $this->setting_model->get_asset_code_list($data['txt_search']);

		$data['pagination'] = paginationSettingsAjax('', $result['total_row'], $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['row_data'] = $result['row'];

		$html = $this->parser->parse('settings/sys_asset_code_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_asset_code(){
		$data['input']				= $this->setting_model->get_asset_code();
		$data['page_title'] 		= 'Add Asset Code';
		$data['form_action'] 		= base_url('settings/save_asset_code');
		$data['msg'] 				= $this->msg;

		$data['asset_category_list'] 		= $this->common_model->get_asset_category_list();
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_asset_code_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_asset_code($asset_code_idx)
	{
		$data['input'] = $this->setting_model->get_asset_code($asset_code_idx);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Asset code not found!');
			redirect('settings/asset_code_management');
		}

		$data['page_title'] 		= 'Edit Asset Code';
		$data['form_action'] 		= base_url('settings/save_asset_code');
		$data['msg'] 				= $this->msg;

		$data['asset_category_list'] 		= $this->common_model->get_asset_category_list();
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_asset_code_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_asset_code()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/asset_code_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('asset_code_idx') != '0')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_asset_category($this->input->post('asset_code_idx'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This asset code is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_code_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/asset_code_management/");
			}else{
				$result = $this->setting_model->delete_asset_code($this->input->post());
				
				$this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_code_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/asset_code_management/");
			}
		}
		else{
			$this->set_form_validation('save_asset_code');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('asset_code_idx')=='0')  $ajax_return['url'] = 'settings/add_asset_code';
				else  $ajax_return['url'] = 'settings/edit_asset_code/' . $this->input->post('asset_code_idx');

				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				if($this->input->post('asset_code_idx') == '0')
				{
					//insert
					$result = $this->setting_model->insert_asset_code($this->input->post());
					$this->session->set_flashdata("msg", 'Asset Code added!');
					// redirect(base_url('settings/asset_code_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_asset_code($this->input->post());
					$this->session->set_flashdata("msg", 'Asset Code updated!');
					// redirect(base_url('settings/asset_code_management'));
				}

				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function asset_site_management(){
		check_acl('asset');

		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('asset_site_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->asset_site_management_rows(1);

		$data['page_title'] 		= 'Asset Site Management';
		$data['form_action'] 		= base_url('settings/asset_site_management');		
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_asset_site', $data);
		$this->load->view('templates/footer');

	}

	function asset_site_management_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('asset_site_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$this->load->model('common_model');
		
		$result	= $this->common_model->get_asset_site_list($data['txt_search']);

		$data['pagination'] = paginationSettingsAjax('', count( $result ), $adta['page_item_no'], $_SESSION['config']['max_page_item'] );
		$data['row_data'] = $result;

		$html = $this->parser->parse('settings/sys_asset_site_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_asset_site() {
		$data['input']				= $this->setting_model->get_asset_site();
		$data['page_title'] 		= 'Asset Site Management';
		$data['form_action'] 		= base_url('settings/save_asset_site');
		$data['msg'] 				= $this->msg;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_asset_site_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_asset_site($site_code)
	{
		$data['input'] = $this->setting_model->get_asset_site($site_code);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Asset Site not found!');
			redirect('settings/asset_site_management');
		}

		$data['page_title'] 		= 'Edit Asset Site';
		$data['form_action'] 		= base_url('settings/save_asset_site');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_asset_site_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_asset_site()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/asset_site_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('site_code') != '')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_product_category($this->input->post('site_code'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This asset site code is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_site_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/asset_site_management/");
			}else{
				$result = $this->setting_model->delete_asset_site($this->input->post());
				
				$this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/asset_site_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/asset_site_management/");
			}
		}
		else{
			$this->set_form_validation('save_asset_site');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('site_code')=='')  $ajax_return['url'] = 'settings/add_asset_site';
				else  $ajax_return['url'] = 'settings/edit_asset_site/' . $this->input->post('site_code');
				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				if($this->input->post('site_code') == '')
				{
					//insert
					$result = $this->setting_model->insert_asset_site($this->input->post());
					$this->session->set_flashdata("msg", 'Asset Site added!');
					// redirect(base_url('settings/asset_site_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_asset_site($this->input->post());
					$this->session->set_flashdata("msg", 'Asset Site updated!');
					// redirect(base_url('settings/asset_site_management'));
				}
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}

	function product_category_management(){
		check_acl('product_category');
		
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('product_category_management_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->product_category_management_rows(1);
		
		$data['page_title'] 		= 'Product Category Management';
		$data['form_action'] 		= base_url('settings/product_category_management');	
		$data['msg'] 				= $this->msg;
		$data['row_html']			= $row_html;

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu', $this->menu);
		$this->parser->parse('settings/sys_product_category', $data);
		$this->load->view('templates/footer');
	}

	function product_category_management_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('product_category_management_filter', $default_data, $post_data);

		$data = $return['data'];

		if (empty($data['page_item_no'])) $data['page_item_no'] = 0;

		$this->load->model('common_model');
		
		$result	= $this->common_model->get_product_category_list($data['txt_search']);

		$data['pagination'] = paginationSettingsAjax('', count( $result ), $data['page_item_no'], $_SESSION['config']['max_page_item'] );
		$data['row_data'] = $result;

		$html = $this->parser->parse('settings/sys_product_category_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_product_category()
	{
		$data['input']				= $this->setting_model->get_product_category();
		$data['page_title'] 		= 'Product Category Management';
		$data['form_action'] 		= base_url('settings/save_product_category');
		$data['msg'] 				= $this->msg;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_product_category_detail',$data);
		$this->load->view('templates/footer');
	}

	function edit_product_category($product_code)
	{
		$data['input'] = $this->setting_model->get_product_category($product_code);

		if ($data['input']=='') {
			$this->session->set_flashdata("warning_msg", 'Product category not found!');
			redirect('settings/product_category_management');
		}

		$data['page_title'] 		= 'Edit Product Category';
		$data['form_action'] 		= base_url('settings/save_product_category');
		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('settings/sys_product_category_detail',$data);
		$this->load->view('templates/footer');
	}

	function save_product_category()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'settings/product_category_management', 'error_keys' => array());
		if ($this->input->post('btDelete') != '' && $this->input->post('product_code') != '')
		{			
			//delete - check being used ?
			$result = $this->setting_model->validate_being_used_product_category($this->input->post('product_code'));
			if($result > 0)
			{
				$this->session->set_flashdata("warning_msg", 'This product category is being used, not allow to delete!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/product_category_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/product_category_management/");
			}else{
				$result = $this->setting_model->delete_product_category($this->input->post());
				
				$this->session->set_flashdata("msg", 'Record Deleted!');
				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'settings/product_category_management';
				echo json_encode($ajax_return);
				return false;
				// redirect("settings/product_category_management/");
			}
		}
		else{
			$this->set_form_validation('save_product_category');
			if($this->form_validation->run() == false) 
			{
				//validation failed
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				if ($this->input->post('product_code')=='') $ajax_return['url'] = 'settings/add_product_category';
				else  $ajax_return['url'] = 'settings/edit_product_category/' . $this->input->post('product_code');

				echo json_encode($ajax_return);
				return false;
			}
			else
			{
				if($this->input->post('product_code') == '')
				{
					//insert
					$result = $this->setting_model->insert_product_category($this->input->post());
					$this->session->set_flashdata("msg", 'Product category added!');
					// redirect(base_url('settings/product_category_management'));
				}
				else 
				{
					//update
					$result = $this->setting_model->update_product_category($this->input->post());
					$this->session->set_flashdata("msg", 'Product category updated!');
					// redirect(base_url('settings/product_category_management'));
				}
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}


	/*
	 *
	 * Already common_model
	 *
	 * */

	//~ private function get_table($table_name='',$select ='*',$and_where='', $order_by='' , $limit='')
	//~ {
		//~ $data = '';
		//~ if($table_name !=''){
			//~ $this->load->database();
			//~ $this->db->select($select);
			//~ $this->db->from($table_name);
			//~
			//~ if($and_where != '') 	$this->db->where($and_where);
			//~ if($order_by != '') 	$this->db->order_by($order_by);
			//~ if($limit != '') 		$this->db->order_by($limit);
			//~
			//~ $data = $this->db->get()->result_array();
		//~ }
		//~ return $data;
	//~ }

	/*
	 *
	 * Already Moved to MY_Controller
	 *
	 * */

	//~ private function get_segment()
	//~ {
		//~ $seg = 1;
		//~ $segment = array();
		//~ while ($seg != 0)
		//~ {
			//~ $seg_info 	= '';
				//~
			//~ $segment[] = $seg_info = $this->uri->segment($seg);
							//~
			//~ if($seg_info != ''){
				//~ $seg++;
			//~ }
			//~ else
			//~ {
				//~ $seg = 0;
				//~ return $segment;
			//~ }
		//~ }
	//~ }
}
