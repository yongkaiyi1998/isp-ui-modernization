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
	<?php $local_base_url = $this->config->item('local_base_url'); ?>
	<link rel="stylesheet" href="<?php if (!empty($local_base_url)) { echo $local_base_url."css/theme/bootstrap.css?1"; } else { echo base_url("css/theme/bootstrap.css?1"); } ?> "/>
	<link rel="stylesheet" href="<?php if (!empty($local_base_url)) { echo $local_base_url."css/theme/font-awesome.css?1"; } else { echo base_url("css/theme/font-awesome.css?1"); } ?> "/>
	<link rel="stylesheet" href="<?php if (!empty($local_base_url)) { echo $local_base_url."css/theme/print-style.css?1"; } else { echo base_url("css/theme/print-style.css?1"); } ?>"/>
	<link rel="stylesheet" href="<?php if (!empty($local_base_url)) { echo $local_base_url."css/theme/print-style-pdf.css?1"; } else { echo base_url("css/theme/print-style-pdf.css?1"); } ?>"/>
	<title><?php echo (!empty($title))?ucwords($title):'itelco'; ?></title>
</head>
<body class="no-skin print-bg" style="font-family:sans-serif">