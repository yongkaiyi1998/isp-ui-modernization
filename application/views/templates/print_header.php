<?php
$current_language		= $this->session->userdata('language'); //'english', 'chinese_simplified'
//http://www.codeigniter.com/user_guide/libraries/language.html#loading-a-language-file
$this->lang->load('custom_text', $current_language);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="0">
	<meta http-equiv="Cache-control" content="no-cache">
	<meta name="viewport" content="width=device-width, initial-scale=1">	
	<meta name="description" content="<?php echo (isset($description))? ucwords($description) : '';?>">      

	<link rel="stylesheet" href="<?php echo base_url("css/theme/bootstrap.css?1"); ?> "/>
	<link rel="stylesheet" href="<?php echo base_url("css/theme/font-awesome.css?1"); ?> "/>
	<link rel="stylesheet" href="<?php echo base_url("css/app.css?3") ?>"/>
	<link rel="stylesheet" href="<?php echo base_url("css/theme/print-style.css?4") ?>"/>

	<link rel="stylesheet" href="<?php echo base_url("css/datepicker.css?".cssjs_ver()); ?>" />
	<link rel="stylesheet" href="<?php echo base_url("css/datepicker/bootstrap-datetimepicker.min.css?".cssjs_ver()); ?>" />

	<title><?php echo (!empty($title))?ucwords($title):'itelco'; ?></title>

	<script src="<?php echo base_url("js/jquery.min.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/jquery-ui.min.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/bootstrap.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/popup.js?".cssjs_ver()); ?>" ></script>

	<!-- for reporting, want to silently submit-->
	<script src="<?php echo base_url("js/jquery.forms.js?".cssjs_ver()); ?>"></script>

	<script src="<?php echo base_url("js/bootstrap-datepicker.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/datepicker/moment.js?".cssjs_ver()); ?>" ></script>

</head>
<body class="no-skin print-bg" >
