<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="0">
		<meta http-equiv="Cache-control" content="no-cache">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo (isset($title))? $title : 'Itelco';?></title>
        <meta name="description" content="<?php echo (isset($description))? $description : 'Itelco';?>">
        <meta id="viewport" name="viewport" content="width=device-width, initial-scale=1">		

        <link rel="icon" type="image/png" href="<?php echo $this->config->item('base_url') ?>images/icon_small.png">
		<link rel="stylesheet" href="<?php echo base_url("css/theme/bootstrap.css?".cssjs_ver()); ?> "/>
		<link rel="stylesheet" href="<?php echo base_url("css/theme/font-awesome.css?".cssjs_ver()); ?> "/>
		<!--[if lte IE 9]>
			<link rel="stylesheet" href="css/theme/ace-part2.css?1" class="ace-main-stylesheet" />
		<![endif]-->
		<!--[if lte IE 9]>
		  <link rel="stylesheet" href="css/theme/ace-ie.css?1" />
		<![endif]-->
		<link rel="stylesheet" href="<?php echo base_url("css/app.css?".cssjs_ver()); ?>" />
		<link rel="stylesheet" href="<?php echo base_url("css/theme/ace.css?".cssjs_ver()); ?> "/>

		<link rel="stylesheet" href="<?php echo base_url("css/app_print.css?".cssjs_ver()); ?> "/>

		<script src="<?php echo base_url("js/jquery.min.js?".cssjs_ver()); ?>" ></script>

		<?php if (isset($custom_js)) { ?>
		<script type="text/javascript">
		<?php echo $custom_js; ?>
		</script>
		<?php } ?>
    </head>
    <body style="background: rgba(0,0,0,0.2);">
		<?php include_once('pdf_nav.php'); ?>
		<div class="clearfix noprint">&nbsp;</div>
