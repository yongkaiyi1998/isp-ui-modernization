<?php if ( ! defined('BASEPATH')) {exit('No direct script access allowed');}

if (!function_exists('paginationSettings')) {
	function paginationSettings($table = null, $total_rows= null, $per_page = 20, $num_links=2, $uri_segment = 3, $first_link=false, $last_link=false)
    {
		$ci =& get_instance();
		$ci->load->library('pagination');

		if ( empty($total_rows) ) $total_rows = 0;

        //pagination settings
		$config['base_url'] = $ci->config->item('base_url').$ci->router->fetch_class().'/'.$ci->router->method.'/';
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['num_links'] = $num_links;
		$config['uri_segment'] = $uri_segment;

		//config for bootstrap pagination class integration
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '<';//'&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '>';//'&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $ci->pagination->initialize($config);
        return $ci->pagination->create_links();
	}
}

if (!function_exists('paginationSettingsAjax')) {
	function paginationSettingsAjax($table = null, $total_rows= null, $cur_page = 0, $per_page = 20, $num_links=2, $uri_segment = 3, $first_link=false, $last_link=false)
    {
		$ci =& get_instance();
		$ci->load->library('pagination');

		if ( empty($total_rows) ) $total_rows = 0;

        //pagination settings
		$config['base_url'] = '';
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['num_links'] = $num_links;
		$config['uri_segment'] = $uri_segment;

		//$config['use_page_numbers'] = TRUE;

		$config['cur_page'] = $cur_page;

		//config for bootstrap pagination class integration
        $config['full_tag_open'] = '<ul class="pagination pagination_ajax">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '<';//'&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '>';//'&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $ci->pagination->initialize($config);
        return $ci->pagination->create_links_ajax();
	}
}

if (!function_exists('system_date_format'))
{
	function system_date_format( $date = '' )
    {
		$result ='';
		if(!empty($date))
		{
			if($date > 0)
			{



			}
		}
		return $result;
	}
}

if (!function_exists('date_toggle'))
{
	function date_toggle($data = '', $toggle='')
    {
		$return_val = '';
		if(!empty($data))
		{
			if($toggle =='d-m-Y') 			$return_val = date('d-m-Y', strtotime($data));
			if($toggle =='Y-m-d') 			$return_val = date('Y-m-d', strtotime($data));
			if($toggle =='date_custom_1') 	$return_val = date_custom_1($data);
		}
		return $return_val;
	}
}

if (!function_exists('datetime_toggle'))
{
	function datetime_toggle($data = '', $toggle='')
    {
		$return_val = '';
		if(!empty($data))
		{
			if($toggle =='Y-m-d h:i:s A') $return_val = date('Y-m-d h:i:s A', strtotime($data));
			if($toggle =='Y-m-d H:i:s A') $return_val = date('Y-m-d H:i:s A', strtotime($data));
			if($toggle =='d-m-Y h:i:s A') $return_val = date('d-m-Y h:i:s A', strtotime($data));
			if($toggle =='datetime_custom_1') $return_val = datetime_custom_1($data);
		}
		return $return_val;
	}
}

if (!function_exists('date_custom_1'))
{
	function date_custom_1($data = '')
    {
		$result ='';
		if(!empty($data))
		{
			if($data > 0)
			{
				$date = new DateTime($data);
				$result = $date->format('d M Y');
			}
		}
		return $result;
	}
}

if (!function_exists('datetime_custom_1'))
{
	function datetime_custom_1($data = '')
    {
		$result ='';
		if(!empty($data))
		{
			if($data != '0000-00-00 00:00:00')
			{
				//~ $date = date("jS", strtotime($data));
				$date = date("d", strtotime($data));
				$month = date("M", strtotime($data));
				$year = date("Y", strtotime($data));
				$time = date("h:i:s A", strtotime($data));
				//~ $time = date("H:i:s T", strtotime($data));
				$result = $date.' '.strtoupper($month).' '.$year.' | '.$time;
			}
		}
		return $result;
	}
}

if (!function_exists('convert_date_1'))
{
	function convert_date_1($data = '')
    {
		$result ='';
		if(!empty($data))
		{
			if($data > 0)
			{
				$date = new DateTime($data);
				$result = $date->format('d M Y');
			}
		}
		return $result;
	}
}


if (!function_exists('convert_date_2'))
{
	function convert_date_2($data = '')
    {
		$result ='';
		if(!empty($data))
		{

			if($data != '0000-00-00 00:00:00')
			{
				//~ $date = date("jS", strtotime($data));
				$date = date("d", strtotime($data));
				$month = date("M", strtotime($data));
				$year = date("Y", strtotime($data));
				$time = date("h:i:s A", strtotime($data));
				//~ $time = date("H:i:s T", strtotime($data));
				$result = $date.' '.strtoupper($month).' '.$year.' | '.$time;
			}
		}

		return $result;
	}
}

if (!function_exists('convert_date_3'))
{
	function convert_date_3($data = '')
    {
		$result ='';
		if(!empty($data))
		{
			if($data > 0)
			{
				// DD-MM-YYYY H:i:S am pm
				// $date = new DateTime($data);
				$date = date("d", strtotime($data));
				$month = date("m", strtotime($data));
				$year = date("Y", strtotime($data));
				$time = date("h:i:s A", strtotime($data));
				// $result = $date->format('d-m-Y | ');
				$result = $date.'-'.strtoupper($month).'-'.$year.' | '.$time;
			}
		}
		return $result;
	}
}

if (!function_exists('get_bill_type_name_by_id'))
{
	function get_bill_type_name_by_id($data = '')
    {
		$return_data = '';
		if(!empty($data))
		{
			$CI			= & get_instance();
			$select 	='name';
			$table_name ='sys_bill_type';
			$and_where  ="bill_type_id ='".$data."'";

			$CI->load->database();
			$CI->db->select($select);
			$CI->db->from($table_name);
			if($and_where != '') $CI->db->where($and_where);

			$result 		= $CI->db->get()->result_array();
			$return_data 	= (empty($result[0]['name']))?$data:$result[0]['name'];
		}
		return $return_data;
	}
}

if (!function_exists('flag_email_icon')){
	function flag_email_icon($data = '0')
    {
		if($data == '0') return '<i class="fa fa-check-circle green" title="Email is valid"></i>';
		if($data == '1') return '<i class="fa fa-exclamation-circle orange" title="Email is not valid"></i>';
		if($data == '2') return '<i class="fa fa-times-circle red" title="Email is not defined"></i>';
	}
}

if (!function_exists('convert_boolean_to_words')){
	function convert_boolean_to_words($data = '0')
    {
		if($data == '0') return '<i class="fa fa-envelope-square red" title="Don\'t Bill By Email"></i>';
		if($data == '1') return '<i class="fa fa-envelope-square green" title="Allow to Bill By Email"></i>';
	}
}

if (!function_exists('convert_number_to_words')){
	/**
	 * @usage:
	 *
	 * return true if today is bigger than the date in the field vice versa.
	 *
	 * */
    function convert_number_to_words($data = '0')
    {
		$number	= '0';
		if(is_numeric($data)){
			$number	= $data;
		}

		$hyphen      = '-';

		//~ if(empty($havDecimal)){
			//~ $conjunction = ' ';
		//~ }else{
			//~ $conjunction = ' and ';
		//~ }
		//~ $conjunction = ' and ';//~ $conjunction = ' and ';

		$conjunction = ' ';
		//~ $conjunction = ' and ';
		$separator   = ', ';
		$negative    = 'negative ';
		$decimal     = ' point ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . convert_number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}

		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . convert_number_to_words($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= convert_number_to_words($remainder);
				}
				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}

		return $string;
    }
}


if (!function_exists('get_state_name_by_code'))
{
	function get_state_name_by_code($data = '')
    {
		$return_data = '';
		if(!empty($data))
		{
			$CI			= & get_instance();
			$select 	='name';
			$table_name ='sys_state';
			$and_where  ="state_code ='".$data."'";

			$CI->load->database();
			$CI->db->select($select);
			$CI->db->from($table_name);
			if($and_where != '') $CI->db->where($and_where);

			$result 		= $CI->db->get()->result_array();
			$return_data 	= (empty($result[0]['name']))?$data:$result[0]['name'];
		}
		return $return_data;
	}
}

if (!function_exists('get_input_by_arr_itemize'))
{
	function get_input_by_arr_itemize($data = '', $table_name = '', $type= '',$debug ='')
    {
		if(empty($type)) $type = 'hidden';
		if(!empty($data) && !empty($table_name))
		{
			foreach($data as $key => $val)
			{
				$input_arr['class']    		= $table_name;
				$input_arr['data-label'] 	= get_bill_type_name_by_id($val['bill_type']);
				$input_arr['data-remark'] 	= $val['remark'];
				$input_arr['value'] 		= $val['amount'];
				$input_arr['type']			= $type;

				if(!empty($debug)) echo $table_name.'['.$key.']'.form_input($input_arr).'<br>';
				else echo form_input($input_arr);
			}
		}
	}
}

if (!function_exists('get_input_by_arr'))
{
	function get_input_by_arr($data = '', $table_name = '', $type= '',$debug ='')
    {
		if(empty($type)) $type = 'hidden';
		if(!empty($data) && !empty($table_name))
		{
			foreach($data as $key => $val)
			{
				$input_arr['name'] 			= $table_name.'['.$key.']';
				$input_arr['id'] 			= $table_name.'['.$key.']';
				$input_arr['value'] 		= $val;
				$input_arr['type']			= $type;
				$input_arr['data-label'] 	= str_replace('_', ' ', $key);

				if($table_name == 'customer')
				{
					if($key == 'package')		$input_arr['value'] = get_package_name_by_no($val);
					if($key == 'inst_state')	$input_arr['value'] = get_state_name_by_code($val);
				}

				if(!empty($debug)) echo $table_name.'['.$key.']'.form_input($input_arr).'<br>';
				else echo form_input($input_arr);
			}
		}
	}
}

if (!function_exists('get_package_name_by_no'))
{
	function get_package_name_by_no($data = '')
    {
		$return_data = '';
		if(!empty($data))
		{
			$CI			= & get_instance();
			$select 	='name';
			$table_name ='package';
			$and_where  ="package_no ='".$data."'";

			$CI->load->database();
			$CI->db->select($select);
			$CI->db->from($table_name);
			if($and_where != '') $CI->db->where($and_where);

			$result 		= $CI->db->get()->result_array();
			$return_data 	= $result[0]['name'];
		}
		return $return_data;
	}
}

if (!function_exists('tooltip_helper'))
{
	function tooltip_helper($data = '')
    {
		$return_data = '';
		if(!empty($data)){
			$return_data =  "data-toggle='tooltip' title='".$data."'";
		}
		return $return_data;
	}
}

if (!function_exists('add_zero'))
{
	function add_zero($data = '')
    {
		if(!empty($data)){

			//~ $CI = & get_instance();

			//~ $config_sess = $CI->session->userdata('config');

			$s = $data;
			$number_of_digit = '10'; //$config_sess['num_digit_per_doc'];

			$zero_added_data = sprintf("%0".$number_of_digit."s", $s);

			return $zero_added_data;

		}
		return 'no-data';
    }
}

if (!function_exists('flash_data_helper'))
{
	/**
	 * @usage:
	 *
	 * return true if today is bigger than the date in the field vice versa.
	 *
	 * */
    function flash_data_helper($msg = '')
    {
			$CI = & get_instance();
			if ( empty($msg)) {
				$msg = array('msg'=>'', 'error_msg'=>'', 'warning_msg'=>'');
			}
		?>
		<?php if(($msg['msg'] != '') || ($msg['error_msg'] != '') || ($msg['warning_msg'] != '')): ?>

			<?php if($msg['msg'] != ''):?>
				<div id="success" class="alert alert-success" role="alert"><?php echo $msg['msg']; ?></div>
				<script> setTimeout(function(){$('#success').fadeOut();},2000); </script>
			<?php endif; ?>
			<?php if($msg['error_msg'] != ''):?>
				<div id="error_msg" class="alert alert-danger" role="alert"><?php echo $msg['error_msg']; ?></div>
				<script> setTimeout(function(){$('#error_msg').fadeOut();},10000); </script>
			<?php endif; ?>
			<?php if($msg['warning_msg'] != ''):?>
				<div id="error_msg" class="alert alert-warning" role="alert"><?php echo $msg['warning_msg']; ?></div>
				<script> setTimeout(function(){$('#error_msg').fadeOut();},2000); </script>
			<?php endif; ?>
		<?php else: ?>
			<!-- flash data -->
			<?php if($CI->session->flashdata('msg') != ''):?>
				<div id="success" class="alert alert-success" role="alert"><?php echo $CI->session->flashdata('msg'); ?></div>
				<script> setTimeout(function(){$('#success').fadeOut();},2000); </script>
			<?php endif; ?>
			<?php if($CI->session->flashdata('error_msg') != ''):?>
				<div id="error_msg" class="alert alert-danger" role="alert"><?php echo $CI->session->flashdata('error_msg'); ?></div>
				<script> setTimeout(function(){$('#error_msg').fadeOut();},10000); </script>
			<?php endif; ?>
			<?php if($CI->session->flashdata('warning_msg') != ''):?>
				<div id="error_msg" class="alert alert-warning" role="alert"><?php echo $CI->session->flashdata('warning_msg'); ?></div>
				<script> setTimeout(function(){$('#error_msg').fadeOut();},2000); </script>
			<?php endif; ?>
		<?php endif; ?>
		<?php

    }
}

if (!function_exists('generateRandomString'))
{
	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';

	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[random_int(0, $charactersLength - 1)];
	    }

	    return $randomString;
	}
}

if (!function_exists('hex_to_base64'))
{
	function hex_to_base64($hex){
	  $return = '';
	  foreach(str_split($hex, 2) as $pair){
	    $return .= chr(hexdec($pair));
	  }
	  return base64_encode($return);
	}
}

if (!function_exists('pem2der'))
{

	function pem2der($pem_data){
	   $begin = "KEY-----";
	   $end   = "-----END";
	   $pem_data = substr($pem_data, strpos($pem_data, $begin)+strlen($begin));   
	   $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
	   $der = base64_decode($pem_data);
	   return $der;
	}

}

if (!function_exists('cssjs_ver'))
{

	function cssjs_ver(){
		return 81;
	}

}


if (!function_exists('malay_lang'))
{

	function malay_lang($term){
		$malay_lang = array(
			'subscription fee' => 'yuran langganan',
			'invoice' => 'invois',
			'dn' => 'nota debit',
			'cn' => 'nota kredit',
			'1 core p2p fiber service' => '1 core p2p perkhidmatan fiber',
			'1 core p2p fiber service sve' => '1 core p2p perkhidmatan fiber sve',
			'1u rack space' => '1u ruang rak',
			'2 cores p2p fiber service' => 'dua core p2p perkhidmatan fiber',
			'22u rack space' => '22u ruang rak',
			'4 cores p2p fiber service' => 'empat core p2p perkhidmatan fiber',
			'adjustment' => 'pelarasan',
			'admin fee (no contract)' => 'fee admin',
			'bandwidth' => 'lebar jalur',
			'bill by post' => 'fee pos',
			'cancellation refund' => 'bayaran balik pembatalan',
			'cross connect' => 'cross connect',
			'deposit' => 'deposit',
			'deposit offset subscription fee' => 'deposit mengimbangi dari yuran langganan',
			'deposit return' => 'bayaran balik deposit',
			'dia subscription' => 'langganan dia',
			'discount / rebate' => 'diskaun / rebat',
			'downtime/service outrage rebate' => 'downtime/rebat putus servis ',
			'early termination' => 'penamatan awal',
		);

		if (isset($malay_lang[$term])) {
			return $malay_lang[$term];
		} else {
			return $term;
		}
	}

}

if (!function_exists('convert_number_to_words_malay')){
	/**
	 * @usage:
	 *
	 * return true if today is bigger than the date in the field vice versa.
	 *
	 * */
    function convert_number_to_words_malay($data = '0')
    {
		$number	= '0';
		if(is_numeric($data)){
			$number	= $data;
		}

		$hyphen      = ' ';

		//~ if(empty($havDecimal)){
			//~ $conjunction = ' ';
		//~ }else{
			//~ $conjunction = ' and ';
		//~ }
		//~ $conjunction = ' and ';//~ $conjunction = ' and ';

		$conjunction = ' ';
		//~ $conjunction = ' and ';
		$separator   = ' ';
		$negative    = 'negatif ';
		$decimal     = ' dan ';
		$dictionary  = array(
			0                   => 'kosong',
			1                   => 'satu',
			2                   => 'dua',
			3                   => 'tiga',
			4                   => 'empat',
			5                   => 'lima',
			6                   => 'enam',
			7                   => 'tujuh',
			8                   => 'lapan',
			9                   => 'sembilan',
			'00'                   => 'kosong',
			'01'                   => 'satu',
			'02'                   => 'dua',
			'03'                   => 'tiga',
			'04'                   => 'empat',
			'05'                   => 'lima',
			'06'                   => 'enam',
			'07'                   => 'tujuh',
			'08'                   => 'lapan',
			'09'                   => 'sembilan',
			10                  => 'sepuluh',
			11                  => 'sebelas',
			12                  => 'dua belas',
			13                  => 'tiga belas',
			14                  => 'empat belas',
			15                  => 'lima belas',
			16                  => 'enam belas',
			17                  => 'tujuh belas',
			18                  => 'lapan belas',
			19                  => 'sembilan belas',
			20                  => 'dua puluh',
			30                  => 'tiga puluh',
			40                  => 'empat puluh',
			50                  => 'lima puluh',
			60                  => 'enam puluh',
			70                  => 'tujuh puluh',
			80                  => 'lapan puluh',
			90                  => 'sembilan puluh',
			100                 => 'ratus',
			1000                => 'ribu',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . convert_number_to_words_malay(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
			if ($fraction == '00') {
				$fraction = null;
			}
		}

		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . convert_number_to_words_malay($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = convert_number_to_words_malay($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= convert_number_to_words_malay($remainder);
				}
				break;
		}

		$string .= " ringgit ";

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();

			/*foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}*/

			switch (true) {
				case $fraction < 21:
					$words[] = $dictionary[$fraction];
					break;
				case $fraction < 100:
					$word = '';
					$tens   = ((int) ($fraction / 10)) * 10;
					$units  = $fraction % 10;
					$word = $dictionary[$tens];
					if ($units) {
						$word .= $hyphen . $dictionary[$units];
					}
					$words[] = $word;
					break;
				default:
					break;
			}

			$words[] = 'sen';

			$string .= implode(' ', $words);
		}

		if (substr( $string, 0, 10 ) === "kosong dan") {
			$string = trim(substr($string, 10));
		}

		return $string;
    }
}

if(!function_exists('format_dealer_list_hierarchy')){
		function format_dealer_list_hierarchy($dealerList)
	{
		$dealerMap = [];
		foreach ($dealerList as $row) {
			$dealerMap[$row['dealer_no']] = $row;
		}

		$getTopDealer = function ($dealerNo) use (&$getTopDealer, $dealerMap) {
			$dealer = $dealerMap[$dealerNo];
			if ($dealer['upline'] == 0) return $dealer;
			return $getTopDealer($dealer['upline']);
		};

		$grouped = [];
		foreach ($dealerList as $val) {
			$topDealer = $getTopDealer($val['dealer_no']);
			$topId = $topDealer['dealer_no'];
			$topName = $topDealer['name'];

			if (!isset($grouped[$topId])) {
				$grouped[$topId] = ['dealer_no' => $topId, 'name' => $topName, 'children' => []];
			}

			if ($val['dealer_no'] != $topId) {
				$val['name'] = $val['name'] . ' (' . $topName . ')';
				$grouped[$topId]['children'][] = $val;
			}
		}

		foreach ($grouped as &$group) {
			usort($group['children'], function($a, $b) {
				return strcmp($a['name'], $b['name']);
			});
		}
		unset($group);

		$finalList = [];
		foreach ($grouped as $group) {
			$finalList[] = ['dealer_no' => $group['dealer_no'], 'name' => $group['name']];
			foreach ($group['children'] as $child) {
				$finalList[] = $child;
			}
		}

		return $finalList;
	}
}

if(!function_exists('normalize_input')){
	function normalize_input($value, $type = 'string', $default = null)
	{
		if ($value === '' || $value === null) {
			return $default;
		}

		switch ($type) {
			case 'int':
				return (int) $value;

			case 'float':
				return (float) $value;

			case 'date':
				return $value ?: $default; // assume already valid format

			case 'array':
				return is_array($value) ? $value : $default;
				
			case 'string':
			default:
				return trim($value);
		}
	}
}

/**
 * Validates a global phone number (Numeric Only).
 * * - Must be strictly digits.
 * - Minimum 7 digits, Maximum 15 digits (ITU-T E.164 standard).
 * * @param string $number
 * @return bool
 */
function isValidGlobalPhone($number) {
    // 1. make sure its only number
    if (!ctype_digit($number)) {
        return false;
    }

    // 2. check length (International standard is 7 to 15 digits)
    $length = strlen($number);
    if ($length < 7 || $length > 15) {
        return false;
    }

    // 3. Avoid the numbers starting with '00' (common international prefix error)
    // If you want numbers to start with Country Code, they shouldn't start with 00
    if (strpos($number, '00') === 0) {
        return false;
    }

    return true;
}

if(!function_exists('get_x_months')){
	function get_x_months($date1, $date2) {
		try { 
			$ts1 = strtotime($date1);
			$ts2 = strtotime($date2);

			$year1 = date('Y', $ts1);
			$year2 = date('Y', $ts2);

			$month1 = date('m', $ts1);
			$month2 = date('m', $ts2);

			$diff = (($year2 - $year1) * 12) + ($month2 - $month1);

			return $diff;
		} catch (Exception $e) {
			log_message('error', 'get_x_months error:'.print_r($e, true));
			return 0;
		}
	}
}