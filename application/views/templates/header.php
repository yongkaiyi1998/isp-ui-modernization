<?php

$current_language		= $this->session->userdata('language'); //'english', 'chinese_simplified'
//http://www.codeigniter.com/user_guide/libraries/language.html#loading-a-language-file
$this->lang->load('custom_text', $current_language);

$proj_name = $this->config->item('proj_name');

if (empty($proj_name)) {
	$proj_name = 'Itelco';
}

$favicon = $this->config->item('favicon_img');
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
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
	<meta name="robots" content="noindex">

	<?php if (!empty($favicon)) { ?>
	<link rel="icon" type="image/x-icon" href="<?php echo $favicon; ?>">
	<?php } ?>
	
	<link rel="stylesheet" href="<?php echo base_url("css/theme/bootstrap.css?".cssjs_ver()); ?> "/>
	<link rel="stylesheet" href="<?php echo base_url("css/theme/font-awesome.css?".cssjs_ver()); ?> "/>
	<link rel="stylesheet" href="<?php echo base_url("css/theme/ace.css?".cssjs_ver()); ?> "/>
	<link rel="stylesheet" href="<?php echo base_url("css/datepicker.css?".cssjs_ver()); ?>" />
	<link rel="stylesheet" href="<?php echo base_url("css/datepicker/bootstrap-datetimepicker.min.css?".cssjs_ver()); ?>" />
	<link rel="stylesheet" href="<?php echo base_url("css/app.css?".cssjs_ver()); ?>" />
	<link rel="stylesheet" href="<?php echo base_url("css/theme/custom-style.css?".cssjs_ver()) ?>"/>
	<link rel="stylesheet" href="<?php echo base_url("css/select2.css?".cssjs_ver()) ?>"/>
	<link rel="stylesheet" href="<?php echo base_url("css/responsive.css?".cssjs_ver()) ?>"/>
	<link rel="stylesheet" href="<?php echo base_url("css/theme/ace-fonts.css?".cssjs_ver()); ?>" />	

	<link rel="stylesheet" href="<?php echo base_url("css/jquery.gritter.css?".cssjs_ver()); ?>" />
	<!--[if lte IE 9]>
		<link rel="stylesheet" href="css/theme/ace-part2.css?1" class="ace-main-stylesheet" />
	<![endif]-->
	<!--[if lte IE 9]>
	  <link rel="stylesheet" href="css/theme/ace-ie.css?1" />
	<![endif]-->
	<link rel="stylesheet" href="<?php echo base_url("css/ui-modern.css?".cssjs_ver()); ?>" />

	
	
	<script src="<?php echo base_url("js/jquery.min.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/jquery-ui.min.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/bootstrap.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/ace/ace.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/ace/ace.sidebar.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/ace-elements.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/select2.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/bootstrap-datepicker.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/highcharts.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/popup.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/datepicker/moment.js?".cssjs_ver()); ?>" ></script>

	<script src="<?php echo base_url("js/jquery.gritter.js?".cssjs_ver()); ?>" ></script>
	
	<script src="<?php echo base_url("js/itelco/common.js?".cssjs_ver()); ?>" ></script>

	<!-- for reporting, want to silently submit-->
	<!-- <script src="<?php echo base_url("js/jquery.forms.js?".cssjs_ver()); ?>"></script> -->


	<title><?php echo $proj_name; ?></title>

	<!--[if lte IE 8]>
	<script src="js/html5shiv.js?1"></script>
	<script src="js/respond.js?1"></script>
	<![endif]-->

	<?php
		// Meta Headers
		if (isset($metaheaders) && is_array($metaheaders)) {
			foreach ($metaheaders as $meta)
				echo '<meta '.$meta.'>';
		}
		// css files
		if (isset($cssfiles) && is_array($cssfiles)) {
			foreach ($cssfiles as $file)
				echo '<link rel="stylesheet" type="text/css" href="'.$file."?".cssjs_ver().'" />';
		}
		// javascript files
		if (isset($jsfiles) && is_array($jsfiles)) {
			foreach ($jsfiles as $file)
				echo '<script type="text/javaScript" src="'.base_url($file)."?".cssjs_ver().'"></script>';
		}
		// javascripts
		if (isset($jscripts) && is_array($jscripts)) {
			foreach ($jscripts as $script)
				echo "<script type='text/JavaScript'>\n".$script."\n</script>";
		}
	?>

</head>
<body class="no-skin ui-modern-shell">
