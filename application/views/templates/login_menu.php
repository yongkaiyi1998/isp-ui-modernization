<?php $sess_check = $this->session->userdata('acl'); ?>
<!-- #section:basics/navbar.layout -->
<div id="navbar" class="navbar navbar-default">

<div class="navbar-container" id="navbar-container">


	<div class="navbar-buttons navbar-header pull-right" role="navigation">		
		<ul class="nav ace-nav">
			
			
			<?php 
				$file = 'notification';
				if (file_exists('application/views/templates/'.$file.'.php')){	
					//include_once($file.'.php');
				}
			?>
			<li class="light-blue">
				<a data-toggle="dropdown" href="#" class="dropdown-toggle">
					<span class="user-info">&nbsp;&nbsp;&nbsp;&nbsp;
						<small>
						User: 
						<?php echo (empty($user['login_name']))?'No-Name':ucwords($user['login_name']); ?>
						</small>&nbsp;&nbsp;
					</span>

					<i class="ace-icon fa fa-caret-down"></i>
				</a>

				<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
					<li>
						<a href="<?php echo $base_url; ?>settings/account">
							<i class="ace-icon fa fa-cog"></i>
							Account Settings
						</a>
					</li>

					<li class="divider"></li>

					<li>
						<a href="<?php echo $base_url; ?>auth/logout">
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

	<!--div class="sidebar-shortcuts" id="sidebar-shortcuts">
		<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
			<button class="btn btn-info" title="Transaction Entry" onclick="parent.location='transaction-entry.html'">
				<i class="ace-icon fa fa-exchange"></i>
			</button>

			<button class="btn btn-success" title="Payment Entry" onclick="parent.location='payment-entry.html'">
				<i class="ace-icon fa fa-dollar"></i>
			</button>

			<button class="btn btn-warning" title="Adjustment Entry" onclick="parent.location='adjustment-entry.html'">
				<i class="ace-icon fa fa-sliders"></i>
			</button>

			<button class="btn btn-danger" title="Contra Entry" onclick="parent.location='contra-entry.html'">
				<i class="ace-icon fa fa-tag"></i>
			</button>

		</div>

		<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
			<span class="btn btn-success"></span>

			<span class="btn btn-info"></span>

			<span class="btn btn-warning"></span>

			<span class="btn btn-danger"></span>
		</div>
	</div><!-- /.sidebar-shortcuts -->

	<ul class="nav nav-list">
		
		<li class="">
			<a href="<?php echo $base_url; ?>customers/index">
				<i class="menu-icon fa fa-users red"></i>
				<span class="menu-text"> Customers </span>
			</a>
			<b class="arrow"></b>
		</li>
		
		<li class="">
			<a href="<?php echo $base_url; ?>recurring/index">
				<i class="menu-icon fa fa-calendar orange"></i>
				<span class="menu-text"> Recurr </span>
			</a>
			<b class="arrow"></b>
		</li>
		<li class="">
			<a href="<?php echo $base_url; ?>invoice/index">
				<i class="menu-icon fa fa-dollar green"></i>
				<span class="menu-text"> Invoice </span>
			</a>
			<b class="arrow"></b>
		</li>
		<li class="">
			<a href="<?php echo $base_url; ?>debit/index">
				<i class="menu-icon fa fa-plus dark-blue"></i>
				<span class="menu-text"> Debit Note </span>
			</a>
			<b class="arrow"></b>
		</li>
		<li class="">
			<a href="<?php echo $base_url; ?>credit/index">
				<i class="menu-icon fa fa-minus blue"></i>
				<span class="menu-text"> Credit Note </span>
			</a>
			<b class="arrow"></b>
		</li>

		<li class="">
			<a href="<?php echo $base_url; ?>order/index">
				<i class="menu-icon fa fa-truck purple"></i>
				<span class="menu-text"> Delivery Order </span>
			</a>
			<b class="arrow"></b>
		</li>
		<?php if($sess_check['admin'] == '1,1'): ?>
		<li class="">
			<a href="#" class="dropdown-toggle">
				<i class="menu-icon fa fa-wrench grey"></i>
				<span class="menu-text"> Settings </span>
				<b class="arrow fa fa-angle-down"></b>
			</a>
			<b class="arrow"></b>
			<ul class="submenu">
				<li class="">
					<a href="<?php echo $base_url; ?>settings/system">
						<i class="menu-icon fa fa-caret-right"></i>
						System Settings
					</a>
					<b class="arrow"></b>
				</li>
				<li class="">
					<a href="<?php echo $base_url; ?>settings/users">
						<i class="menu-icon fa fa-caret-right"></i>
						Users Management
					</a>
					<b class="arrow"></b>
				</li>
			</ul>
		</li>
		<?php endif; ?>


		
<!--
		<li class="">
			<a href="#" class="dropdown-toggle">
				<i class="menu-icon fa fa-dollar green"></i>
				<span class="menu-text"> Accounting </span>
				<b class="arrow fa fa-angle-down"></b>
			</a>
			<b class="arrow"></b>
			<ul class="submenu">
				<li class="">
					<a href="<?php echo $base_url; ?>acc_entry">
						<i class="menu-icon fa fa-caret-right"></i>
						Accounting Entry
					</a>
					<b class="arrow"></b>
				</li>
				<li class="">
					<a href="<?php echo $base_url; ?>acc_entry/report">
						<i class="menu-icon fa fa-caret-right"></i>
						Reports
					</a>
					<b class="arrow"></b>
				</li>
				<li class="">
					<a href="<?php echo $base_url; ?>acc_accounts">
						<i class="menu-icon fa fa-caret-right"></i>
						Accounts Setup
					</a>
					<b class="arrow"></b>
				</li>
			</ul>
		</li>


-->

		

<!--
		<li class="">
			<a href="<?php echo $base_url; ?>datascraper">
				<i class="menu-icon fa fa-camera purple"></i>
				<span class="menu-text"> Data Scraper </span>
			</a>
			<b class="arrow"></b>
		</li>
-->
<!--
		<li class="">
-->
	</ul><!-- /.nav-list -->

	<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
		<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
	</div>
</div>
<div class="main-content">
	<div class="main-content-inner" style="max-width: 99%;">
			<?php // flash data here ?>
		

