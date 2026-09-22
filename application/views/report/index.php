<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="row">
				<?php if ( check_acl_report('C', false) ) {?>
				<div class="col-lg-4 mobile-block-list-wrap">
					<fieldset class='category-border'>
						<legend class="category-border">
							Customer
						</legend>
						<div class="col-lg-12">
							<div class="row">
								<a href="<?php echo base_url('report/customer_listing');?>">Customer Listing</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/building_listing');?>">Customer Report ( By Building )</a></br>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/customer_overdue');?>">Customer Overdue</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/customer_transaction_listing');?>">Customer Transaction Listing</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/customer_deposit_listing');?>">Customer Deposit Listing</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/customer_aging');?>">Customer Aging</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/detailed_customer_aging');?>">Detailed Customer Aging</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/customer_statement_listing');?>">Statement of Account</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/terminated_acc');?>">Terminated Accounts</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/contract_acc');?>">Contract Accounts</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/expiry_contract_report');?>">Contract Expiry Reminder</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/expiry_no_renewal_report');?>">Expired Contract Without Renewal</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/not_yet_customers');?>">Not Yet Activated</a>
							</div>
						</div>
					</fieldset>
				</div>
				<?php } ?>
				<?php if ( check_acl_report('B', false) ) {?>
				<div class="col-lg-4 mobile-block-list-wrap">
					<fieldset class='category-border'>
						<legend class="category-border">
							Billing
						</legend>
						<div class="col-lg-12">
							<div class="row">
								<a href="<?php echo base_url('report/sales');?>">Sales Report</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/payment_summary');?>">Payment Summary</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/detail_of_payment');?>">Details of Payment</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/detail_of_billing');?>">Details of Billing</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/adjustment_summary');?>">Adjustment Summary</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/einvoice_report');?>">E-invoice Report</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/paynet_report');?>">Customer Payment Report (Paynet)</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/bill_reminder_report');?>">Reminder Notification</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/ifca_table');?>">IFCA Export</a>
							</div>
						</div>
					</fieldset>
				</div>
				<?php } ?>
				<?php if ( check_acl_report('G', false) ) {?>
				<div class="col-lg-4 mobile-block-list-wrap">
					<fieldset class='category-border'>
						<legend class="category-border">
							Agent
						</legend>
						<div class="col-lg-12">
							<a href="<?php echo base_url('report/dealer_listing');?>">Agent Collection</a></br>
							<a href="<?php echo base_url('report/dealer_commission');?>">Agent Commission</a></br>
							<a href="<?php echo base_url('report/dealer_commission_by_dealer');?>">Agent Commission Summary</a></br>
						</div>
					</fieldset>
				</div>
				<?php } ?>
			</div>
			<div class="row">
				<?php if ( check_acl_report('T', false) ) {?>
				<div class="col-lg-4 mobile-block-list-wrap">
					<fieldset class='category-border'>
						<legend class="category-border">
							Trouble Ticket/Service Ticket
						</legend>
						<div class="col-lg-12">
							<div class="row">
								<a href="<?php echo base_url('report/ticket_listing');?>">Service Ticket</a>
							</div>
						</div>
					</fieldset>
				</div>
				<?php } ?>
				<?php if ( check_acl('action_log', 'V', false) || check_acl_report('S', false) || check_acl('email', 'V', false) ) {?>
				<div class="col-lg-4 mobile-block-list-wrap">
					<fieldset class='category-border'>
						<legend class="category-border">
							Others
						</legend>
						<div class="col-lg-12">
							
							<?php if ( check_acl_report('S', false) ) {?>
								<div class="row">
									<a href="<?php echo base_url('report/asset_listing');?>">Asset List</a>
								</div>
								<div class="row">
									<a href="<?php echo base_url('report/planned_maintenance');?>">Planned Maintenance</a>
								</div>
								<div class="row">
									<a href="<?php echo base_url('report/asset_service');?>">Asset Service Record</a>
								</div>
								<div class="row">
									<a href="<?php echo base_url('report/asset_transfer');?>">Asset Transfer Record</a>
								</div>
							<?php } ?>
							<?php if ( check_acl('action_log', 'V', false) ) {?>
								<div class="row">
									<a href="<?php echo base_url('docs_log');?>">Send Log (Email, Whatsapp, Telegram)</a>
								</div>
							<?php } ?>
							<?php if ( check_acl('email', 'V', false) ) { ?>
								<div class="row">
									<a href="<?php echo base_url('email/email_report');?>">Email Scheduler Progress Report</a>
								</div>
							<?php } ?>
							<?php if ( check_acl('action_log', 'V', false) ) { ?>
								<div class="row">
									<a href="<?php echo base_url('action_log');?>">Action Log</a>
								</div>
								<div class="row">
									<a href="<?php echo base_url('customer_action_log');?>">Customer Action Log</a>
								</div>
							<?php } ?>
						</div>
					</fieldset>
				</div>
				<?php } ?>
				<?php if ( check_acl_report('R', false) ) {?>
				<div class="col-lg-4 mobile-block-list-wrap">
					<fieldset class='category-border'>
						<legend class="category-border">
							Radius
						</legend>
						<div class="col-lg-12">
							<div class="row">
								<a href="<?php echo base_url('report/radius_radcheck');?>">Radcheck</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/radius_usergroup');?>">Usergroup</a>
							</div>
							<div class="row">
								<a href="<?php echo base_url('report/radius_login');?>">Login Log</a>
							</div>
						</div>
					</fieldset>
				</div>
				<?php } ?>
				
				
			</div>

		</div>
	</div>
</div>

<div id="clear" style="clear:both;"></div>
<div id="popupDetail">
	<div id="popupDetailStd" onclick="disablePopup();">
		<div id="popupContent">
			&nbsp;
		</div>
	</div>
</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>

<script src="<?php echo base_url("js/itelco/report.js?".cssjs_ver()); ?>" ></script>
