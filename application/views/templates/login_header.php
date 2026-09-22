<!DOCTYPE html>
<html>
<head>

<?php

$proj_name = $this->config->item('proj_name');

if (empty($proj_name)) {
	$proj_name = 'Itelco';
}

$favicon = $this->config->item('favicon_img');
?>

	<meta charset="utf-8">
	<link rel="icon" type="image/png" href="<?php echo $this->config->item('base_url') ?>images/favicon.ico">
	<title><?php echo $proj_name.' - '.$title ?></title>
	<base href="<?php echo $this->config->item('base_url') ?>">
	<link rel="stylesheet" href="css/system.css?123" type="text/css" />
	<link rel="stylesheet" href="css/ui-modern.css?123" type="text/css" />
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
	<meta name="robots" content="noindex">

	<?php if (!empty($favicon)) { ?>
	<link rel="icon" type="image/x-icon" href="<?php echo $favicon; ?>">
	<?php } ?>

</head>
<body class="login_bg ui-login-page">
