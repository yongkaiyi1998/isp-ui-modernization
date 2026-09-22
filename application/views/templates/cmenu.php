<!-- #section:basics/navbar.layout -->
<div id="navbar" class="navbar navbar-default" style="display:block !important;">
	<div class="navbar-container" id="navbar-container">
		<div class="navbar-header pull-left">
			
		<button data-target="#sidebar" id="menu-toggler" class="three-bars pull-left menu-toggler navbar-toggle" type="button">							
			<span class="sr-only">Toggle sidebar</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>						
		</button>
			
			<a href="<?php echo base_url('home')?>" class="navbar-brand">
				<div class="row">
					<?php
					$company_logo = $this->config->item('logo_img'); 
					$proj_name = $this->config->item('proj_name');

					if (empty($proj_name)) {
						$proj_name = 'Itelco';
					}
					?>
					<?php if (!empty($company_logo)) { ?>
					<div style="margin-top:1px;" class="pull-left">
					<img style="width:118px;" src="<?php echo $company_logo; ?>" >
					</div>
					<?php } else { ?>
					<div style="margin-top:1px;" class="pull-left">
					<img src="<?php echo base_url("/images/telco-icon.png"); ?>" >
					</div>
					<div style="padding:3px 0 0 10px"><small><?php echo $proj_name; ?></small></div>
					<?php } ?>
				</div>
			</a>
		</div>

		<div class="navbar-buttons navbar-header pull-right" role="navigation">
			<ul class="nav ace-nav">
				<li class="light-blue">
					<a data-toggle="dropdown" href="#" class="dropdown-toggle">
						<span class="user-info">&nbsp;&nbsp;&nbsp;&nbsp;
							<?php echo (empty($cuser['customer_name']))?'unknown':ucwords($cuser['customer_name']); ?>
						</span>
						<i class="ace-icon fa fa-caret-down"></i>
					</a>
					<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
						<li>
							<a href="<?php echo base_url("auth/clogout") ?>">
								<i class="ace-icon fa fa-power-off"></i>
								Logout
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div><!-- /.navbar-container -->
</div>
<div class="main-container" id="main-container" >
	<div id="sidebar" class="sidebar responsive">
		<ul class="nav nav-list">
			<li>
				<a href="<?php echo base_url("chome"); ?>">
					<i class="menu-icon fa fa-tachometer text-primary"></i>
					<span class="menu-text"> Home </span>
				</a>
			</li>
			<li>
				<a href="<?php echo base_url("chome/termination_form"); ?> ">
					<i class="menu-icon fa fa-edit orange"></i>
					<span class="menu-text"> Termination Form </span>
				</a>
			</li>
		</ul>
		<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
			<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
		</div>
	</div>
	<div class="main-content" >
		<div class="main-content-inner" style="max-width: 99%;">
				<?php // flash data here ?>