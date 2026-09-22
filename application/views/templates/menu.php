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
			
			<a href="<?php echo base_url('home')?>" class="navbar-brand ui-navbar-brand">
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
					<img class="ui-brand-logo" style="width:118px;" src="<?php echo $company_logo; ?>" alt="<?php echo $proj_name; ?>">
					</div>
					<?php } else { ?>
					<div style="margin-top:1px;" class="pull-left">
					<img class="ui-brand-logo" src="<?php echo base_url("/images/telco-icon.png"); ?>" alt="">
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
						<span class="user-info">
							<?php echo (empty($user['display_name']))?'unknown':ucwords($user['display_name']); ?>
						</span>
						<i class="ace-icon fa fa-caret-down"></i>
					</a>
					<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
						<li>
							<a href="<?php echo base_url("user/own_view/".$user['username']) ?>">
								<i class="ace-icon fa fa-cog"></i>
								Account Settings
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?php echo base_url("auth/logout") ?>">
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
				<a href="<?php echo base_url("home"); ?>">
					<i class="menu-icon fa fa-tachometer text-primary"></i>
					<span class="menu-text"> Dashboard </span>
				</a>
			</li>
			<?php if ( !empty($_SESSION['acl']['registration']) ) {?>
			<li>
				<a href="<?php echo base_url("registration"); ?>">
					<i class="menu-icon fa fa-male red"></i>
					<span class="menu-text"> Registrations </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['salesorder']) ) {?>
			<li>
				<a href="<?php echo base_url("salesorder"); ?>">
					<i class="menu-icon fa fa-edit red"></i>
					<span class="menu-text"> Sales Order </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['profile']) ) {?>
			<li>
				<a href="<?php echo base_url("profile"); ?> ">
					<i class="menu-icon fa fa-users green"></i>
					<span class="menu-text"> Customer Profile </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['customer']) ) {?>
			<li>
				<a href="<?php echo base_url("customer"); ?> ">
					<i class="menu-icon fa fa-cubes red"></i>
					<span class="menu-text"> Account </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['bill']) || !empty($_SESSION['acl']['payment']) || !empty($_SESSION['acl']['adjustment']) ) {?>
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-dollar orange"></i>
					<span class="menu-text">  Billing </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<?php if ( !empty($_SESSION['acl']['bill']) ) {?>
					<li>
						<a href="<?php echo base_url("bill") ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Billing Details
						</a>
					</li>
					<?php }?>
					
					<?php if ( !empty($_SESSION['acl']['bill_manual']) ) {?>
					
					 <li>
						<a href="<?php echo base_url("bill/bill_manual") ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Manual Billing
						</a>
					</li>
					
					<?php }?>
					
					<?php if ( !empty($_SESSION['acl']['payment']) ) {?>
					<li>
						<a href="<?php echo base_url("payment") ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Payment
						</a>
					</li>
					<?php }?>
					<?php if ( !empty($_SESSION['acl']['adjustment']) ) {?>
					<li>
						<a href="<?php echo base_url("adjustment") ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Adjustment
						</a>
					</li>
					<!--
					<li>
						<a href="<?php echo base_url("adjustment/auto_adjustment") ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Auto Adjustment
						</a>
					</li>
					-->
					<?php }?>
				</ul>
			</li>
			<?php }?>

			<?php if ( !empty($_SESSION['acl']['einvoice']) ) {?>
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-file-o purple"></i>
					<span class="menu-text"> E-invoice </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<li>
						<a href="<?php echo base_url("einvoice") ?>">
							<i class="menu-icon fa fa-file-o red"></i>
							<span class="menu-text"> E-invoice List </span>
						</a>
					</li>
					<!--<li>
						<a href="<?php echo base_url("einvoice/consolidate") ?>">
							<i class="menu-icon fa fa-copy red"></i>
							<span class="menu-text"> Consolidated E-invoice List </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("einvoice/submit_consolidate") ?>">
							<i class="menu-icon fa fa-copy red"></i>
							<span class="menu-text"> Submit Consolidated </span>
						</a>
					</li>-->
				</ul>
			</li>
			
			<?php }?>	

			<?php if ( !empty($_SESSION['acl']['asset']) ) {?>

			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-tasks green"></i>
					<span class="menu-text"> Asset </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<li>
						<a href="<?php echo base_url("asset") ?>">
							<i class="menu-icon fa fa-tasks red"></i>
							<span class="menu-text"> Asset List </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("asset/planned") ?>">
							<i class="menu-icon fa fa-tasks red"></i>
							<span class="menu-text"> Planned Maintenance </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("asset/service_list") ?>">
							<i class="menu-icon fa fa-tasks red"></i>
							<span class="menu-text"> Service Record List </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("asset/transfer_list") ?>">
							<i class="menu-icon fa fa-tasks red"></i>
							<span class="menu-text"> Transfer Record List </span>
						</a>
					</li>
				</ul>
			</li>

			<?php } ?>
			
			<?php if ( !empty($_SESSION['acl']['package']) ) {?>
			<li>
				<a href="<?php echo base_url("package"); ?>">
					<i class="menu-icon fa fa-cubes purple"></i>
					<span class="menu-text"> Package </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['changepackage']) ) {?>
			<li>
				<a href="<?php echo base_url("changepackage"); ?>">
					<i class="menu-icon fa fa-exchange blue"></i>
					<span class="menu-text"> Pkg. Change Request </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['dealer']) ) {?>
			<li>
				<a href="<?php echo base_url("dealer"); ?>">
					<i class="menu-icon fa fa-male green"></i>
					<span class="menu-text"> Agent </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['area']) ) {?>
			<li>
				<a href="<?php echo base_url("area") ?>">
					<i class="menu-icon fa fa-map red2"></i>
					<span class="menu-text"> Area </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['building']) ) {?>
			<li>
				<a href="<?php echo base_url("building") ?>">
					<i class="menu-icon fa fa-building dark-blue"></i>
					<span class="menu-text"> Building </span>
				</a>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['router']) ) {?>
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-sitemap dark-blue"></i>
					<span class="menu-text">  Router </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<li>
						<a href="<?php echo base_url("router") ?>">
							<i class="menu-icon fa fa-sitemap red"></i>
							<span class="menu-text"> Router </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("router/type") ?>">
							<i class="menu-icon fa fa-tag orange"></i>
							<span class="menu-text"> Router Type </span>
						</a>
					</li>
				</ul>
			</li>
			<?php }?>
			<?php if ( !empty($_SESSION['acl']['report']) ) {?>
			<li>
				<a href="<?php echo base_url("report") ?>">
					<i class="menu-icon fa fa-bar-chart green"></i>
					<span class="menu-text"> Report </span>
				</a>
			</li>
			
			<?php }?>			

			<?php if ( !empty($_SESSION['acl']['trouble_ticket'])) {?>
				
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-ticket orange"></i>
					<span class="menu-text">  Service Ticket </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<li>
						<a href="<?php echo base_url("ticket") ?>">
							<i class="menu-icon fa fa-ticket red"></i>
							<span class="menu-text"> Service Ticket </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("ticket/ticket_issues") ?>">
							<i class="menu-icon fa fa-wrench red"></i>
							<span class="menu-text"> Ticket Issues </span>
						</a>
					</li>
					<!-- <li>
						<a href="<?php echo base_url("ticket/setting") ?>">
							<i class="menu-icon fa fa-cogs red"></i>
							<span class="menu-text"> Setting </span>
						</a>
					</li> -->
				</ul>
			</li>
				
			<?php }?>	
			
			<?php if ( !empty($_SESSION['acl']['customer_support']) ) {?>
				
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-headphones orange"></i>
					<span class="menu-text">  Trouble Ticket </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<li>
						<a href="<?php echo base_url("customer_support"); ?>">
							<i class="menu-icon fa fa-headphones red"></i>
							<span class="menu-text"> TT Listing </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("customer_support/customer_support_service"); ?>">
							<i class="menu-icon fa fa-wrench red"></i>
							<span class="menu-text"> TT Service Category </span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("customer_support/customer_support_setting") ?>">
							<i class="menu-icon fa fa-cogs red"></i>
							<span class="menu-text"> Setting </span>
						</a>
					</li>
				</ul>
			</li>
				
			<?php }?>	
			
			<?php if(!empty($_SESSION['acl']['sms'])){ ?>
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-mobile purple"></i>
					<span class="menu-text"> Messaging </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					<!--<li>
						<a href="<?php echo base_url("sms_scheduler/index"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							SMS Scheduler
						</a>
					</li>-->

					<li>
						<a href="<?php echo base_url("message_scheduler/index"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Message Scheduler
						</a>
					</li>
					
					<li>
						<a href="<?php echo base_url("sms_scheduler/message_template"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Messaging Template
						</a>
					</li>
					
					<!--<li>
						<a href="<?php echo base_url("sms_scheduler/sms_report"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Progress Report
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("device_setting"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Device Setting
						</a>
					</li>-->
				</ul>
			</li>
			<?php } ?>
			<?php if(!empty($_SESSION['acl']['email'])){ ?>
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-envelope blue"></i>
					<span class="menu-text"> Email </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
			
					<li>
						<a href="<?php echo base_url("email/schedule_list"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Email Scheduler
						</a>
					</li>
					
					<li>
						<a href="<?php echo base_url("email/email_template"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Email Template
						</a>
					</li>
					
					<li>
						<a href="<?php echo base_url("email/mailing_list"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Mailing List 
						</a>
					</li>
					<li>
						<a href="<?php echo base_url("email/email_report"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Progress Report
						</a>
					</li>
					
				</ul>
			</li>
			<?php } ?>
			
			
			<?php if ( !empty($_SESSION['acl']['action_log']) ): ?>
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-wrench blue"></i>
					<span class="menu-text"> Maintenance </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">
					
					
					<li>
						<a href="<?php echo base_url("action_log/index"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Action Log
						</a>
					</li>

					<li>
						<a href="<?php echo base_url("customer_action_log/index"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Customer Action Log
						</a>
					</li>
					
					<!-- <li>
						<a href="<?php echo base_url("bill/export_bill"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Export Bills 
						</a>
					</li>
					
					<li>
						<a href="<?php echo base_url("payment/export_payment"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Export Payments
						</a>
					</li>

					<li>
						<a href="<?php echo base_url("customer/export_deposit"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Export Deposit
						</a>
					</li> -->
					
				</ul>
			</li>
			
			<?php endif; ?>
			
			<?php if ( !empty($_SESSION['acl']['tax']) || !empty($_SESSION['acl']['config']) || !empty($_SESSION['acl']['user']) || !empty($_SESSION['acl']['acl']) || !empty($_SESSION['acl']['audit']) ): ?>
			
			<li>
				<a href="#" class="dropdown-toggle">
					<i class="menu-icon fa fa-cog grey"></i>
					<span class="menu-text"> Settings </span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<ul class="submenu">

					<?php if ( !empty($_SESSION['acl']['audit'])):?>
					<li>
						<a href="<?php echo base_url("audit_docs"); ?>">
							<i class="menu-icon fa fa-caret-right"></i>
							Audit Docs
						</a>
					</li>
					<?php endif; ?>

					<?php if ( !empty($_SESSION['acl']['config'])):?>
						<li>
							<a href="<?php echo base_url("advertisement"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Advertisement
							</a>
						</li>
					<?php endif; ?>
					
					<?php if ( !empty($_SESSION['acl']['asset'])):?>
						<li>
							<a href="<?php echo base_url("settings/asset_category_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Asset Category Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['asset'])):?>
						<li>
							<a href="<?php echo base_url("settings/asset_code_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Asset Code Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['asset'])):?>
						<li>
							<a href="<?php echo base_url("settings/asset_site_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Asset Site Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['product_category'])):?>
						<li>
							<a href="<?php echo base_url("settings/product_category_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Product Category Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['bill_type'])):?>
						<li>
							<a href="<?php echo base_url("settings/bill_type_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Bill Type Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['payment_type'])):?>
						<li>
							<a href="<?php echo base_url("settings/payment_type_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Pay. Type Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['nas_address'])):?>
						<li>
							<a href="<?php echo base_url("settings/nas_setting_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								NAS Address Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['config'])):?>
						<li>
							<a href="<?php echo base_url("settings/index"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Config Management
							</a>
						</li>
					<?php endif; ?>
					
					<?php if ( !empty($_SESSION['acl']['ledger'])):?>
						<li>
							<a href="<?php echo base_url("settings/sys_ledger_account"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Ledger Acc. Management
							</a>
						</li>
					<?php endif; ?>
					
					
					<?php if ( !empty($_SESSION['acl']['tax'])): ?>
						<li>
							<a href="<?php echo base_url("settings/sys_tax_type"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Tax Management
							</a>
						</li>
					<?php endif; ?>

					<?php if ( !empty($_SESSION['acl']['config'])):?>
						<li>
							<a href="<?php echo base_url("settings/equipment_type_management"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Equipment Type Management
							</a>
						</li>
					<?php endif; ?>

					<?php if ( !empty($_SESSION['acl']['user'])):?>
						<li>
							<a href="<?php echo base_url("user"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Users Management
							</a>
						</li>
					<?php endif; ?>
					<?php if ( !empty($_SESSION['acl']['acl']) ): ?>	
						<li>
							<a href="<?php echo base_url("acl"); ?>">
								<i class="menu-icon fa fa-caret-right"></i>
								Access Permission
							</a>
						</li>	
					<?php endif; ?>
				</ul>
			</li>
			
			<?php endif; ?>
			
		</ul><!-- /.nav-list -->

		<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
			<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
		</div>
	</div>
	<div class="main-content" >
		<div class="main-content-inner ui-content-shell" style="max-width: 99%;">
				<?php // flash data here ?>
