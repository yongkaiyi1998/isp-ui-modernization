<?php include 'panel_header.php';?>
<div class="panel-heading noprint panel_fontsize">
		<span class="panel_space float_right">
			<a  href="<?php echo base_url('customer');?>">
				<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
			</a>
		</span>
		<?php echo $page_title; ?>
</div>
<div class="panel-body">
	<?php echo flash_data_helper($msg); ?>
	<form id="customer_detail" name="customer_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">	
		
		<?php //==== hidden fields ===== ?>
		<?php $inputname = 'url_after_save'; ?>	
		<input 
			id			="<?php echo $inputname; ?>" 
			name		="<?php echo $inputname; ?>" 
			value		="<?php echo set_value($inputname, $hidden[$inputname]); ?>" 
			type		="hidden" >
			
		<input 	id="form_action" name="form_action" type="hidden"
				value="<?php echo !empty($input['customer_no']) ? 'UPDATE' : 'NEW' ; ?>" />

		<input id="marital_status" name="marital_status" type="hidden"
			value="" />

		<input id="household" name="household" type="hidden"
			value="" />

		<input id="nationality_dd" name="nationality_dd" type="hidden"
			value="" />

		<input id="nationality" name="nationality" type="hidden"
			value="" />

		<input id="no_of_employee" name="no_of_employee" type="hidden"
			value="0" />

		<input id="no_of_branches" name="no_of_branches" type="hidden"
			value="0" />

		<input id="pic_designation[0]" name="pic_designation[0]" type="hidden"
			value="" />

		<input id="pic_name[0]" name="pic_name[0]" type="hidden"
			value="" />

		<input id="tel_num[0]" name="tel_num[0]" type="hidden"
			value="" />

		<input id="fax_num[0]" name="fax_num[0]" type="hidden"
			value="" />

		<input id="mobile_num[0]" name="mobile_num[0]" type="hidden"
			value="" />

		<input id="email_1[0]" name="email_1[0]" type="hidden"
			value="" />

		<input id="email_2[0]" name="email_2[0]" type="hidden"
			value="" />

		<input id="nric[0]" name="nric[0]" type="hidden"
			value="" />

		<input id="passport[0]" name="passport[0]" type="hidden"
			value="" />

		<input id="date_of_birth[0]" name="date_of_birth[0]" type="hidden"
			value="" />

		<input id="gender[0]" name="gender[0]" type="hidden"
			value="" />

		<input id="race[0]" name="race[0]" type="hidden"
			value="" />

		<input id="signup_date" name="signup_date" type="hidden"
			value="" />

		<input id="activated_date" name="activated_date" type="hidden"
			value="" />

		<input id="bill_name" name="bill_name" type="hidden"
			value="" />

		<input id="bill_addr1" name="bill_addr1" type="hidden"
			value="" />

		<input id="bill_addr2" name="bill_addr2" type="hidden"
			value="" />

		<input id="bill_city" name="bill_city" type="hidden"
			value="" />

		<input id="bill_postcode" name="bill_postcode" type="hidden"
			value="" />

		<input id="bill_state" name="bill_state" type="hidden"
			value="" />

		<input id="status" name="status" type="hidden"
			value="r" />

		<input id="prev_status" name="prev_status" type="hidden"
			value="<?php echo $input['current_status'] ?>" />
		
		<input id="old_package_name" name="old_package_name" type="hidden"
			value="<?php echo $input['package_name'] ?>" />

		<input id="user_idx" name="user_idx" type="hidden"
			value="<?php echo $user_idx ?>" />

		<input id="new_submit" name="new_submit" type="hidden"
			value="1" />
			
		<input type="hidden" name="dia_vars" id="dia_vars" value="<?= htmlspecialchars($input['dia_vars'], ENT_QUOTES, 'UTF-8') ?>">

		<input type="hidden" name="relocation_sop" id="relocation_sop" value="<?php echo $relocation_sop; ?>" />
		<input type="hidden" name="relocation_action" id="relocation_action" value="0" />

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				PACKAGE
			</div>
			<div class="col-lg-12">
				<fieldset class='category-border'>		
					<legend class="category-border">Package Information
					</legend>				
					<div class="col-lg-6">
						<div class="input-group">						
							<span class="input-group-addon input_group_150"  >Category</span>
							<select class="col-lg-12" id="category" name="category" style="width: 100%;">
								<?php
									foreach ($sel_category_list as $val) {
										echo "<option value='" . $val['category_code'] . "' " . set_select('category', $val['category_code'], ($val['category_code']==$input['category'] ? true : false) ) . ">" . $val['name']."</option>";
									}
								?>
							</select>
						</div>
					</div>

					<?php if(empty($input['new_package_id'])) { ?>
						<div class="col-lg-6" >
							<div class="input-group">
								<span class="input-group-addon input_group_150">Package</span>
								<select class="col-lg-12" id="package" name="package" style="width: 100%;">
									<?php
										foreach ($sel_package_list as $val) {
											echo "<option value='" . $val['package_no'] . "' " . set_select('package', $val['package_no'], ($val['package_no']==$input['package'] ? true : false) ) . ">" . $val['name']." ( RM ". $val['monthly_charge'] . " ) </option>";
										}
									?>
								</select>
							</div>
						</div>
					<?php } else { ?>
						<div class="col-lg-6" >
							<div class="input-group">
								<span class="input-group-addon input_group_150" >New Package</span>
								<select class="col-lg-12" id="package" name="package" style="width: 100%;">
									<?php
										foreach ($sel_package_list as $val) {
											echo "<option value='" . $val['package_no'] . "' " . set_select('package', $val['package_no'], ($val['package_no']==$input['new_package_id'] ? true : false) ) . ">" . $val['name']." ( RM ". $val['monthly_charge'] . " ) </option>";
										}
									?>
								</select>
							</div>
						</div>
					<?php } ?>
					<div class="col-lg-3" id="non_wholesale_section">
						<div class="input-group">
							<span class="input-group-addon input_group_150"  >Package Month</span>											
							<?php $inputname = 'package_month'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" 
								READONLY />
						</div>
					</div>
					<div class="col-lg-3" id="non_wholesale_section">
						<span>
							<?php $inputname = 'stop_service_after_display'; ?>	
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="1"
								<?php echo set_checkbox('stop_service_after', "1", ($input['stop_service_after']==1? true : false) ); ?>
								type		="checkbox" 
								class		=""  
								placeholder	="" disabled="disabled">
								Stop service after package month
								<input type="hidden" id="stop_service_after" name="stop_service_after" value="<?php echo set_value('stop_service_after', $input['stop_service_after']); ?>" />
						</span>
					</div>
					<!-- END Non wholesale sectin section -->
					<div class="col-lg-6" id="old_package">
						<div class="input-group">
							<span class="input-group-addon input_group_150" >Current Package</span>
							<select class="col-lg-12" id="old_package_id" name="old_package_id" disabled style="width: 100%;">
								<?php
									foreach ($sel_package_list as $val) {
										echo "<option value='" . $val['package_no'] . "' " . set_select('package', $val['package_no'], ($val['package_no']==$input['package'] ? true : false) ) . ">" . $val['name']." ( RM ". $val['monthly_charge'] . " ) </option>";
									}
								?>
							</select>
						</div>
					</div>
					<span id="package_eff_date_wrapper">
						<div class="col-lg-6" id="new_effective_date">
							<div class="input-group">
								<span class="input-group-addon input_group_150">New Package Eff. Date</span>							
									<?php $inputname = 'new_package_effective_date'; ?>
									<?php $new_package_effective_date = set_value($inputname, $input[$inputname]  ); ?>
									<?php if (!empty($new_package_effective_date)) { $new_package_effective_date = date('Y-m-d', strtotime($new_package_effective_date)); } else { $new_package_effective_date = ''; } ?>
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="<?php echo $new_package_effective_date; ?>"
										type		="text" 
										class		="form-control"  
										placeholder	="" autocomplete="off" >
							</div>
						</div>
					</span>
					<div class="col-lg-6"></div>
					<!-- Start wholesale sectin section -->
					<div class="col-lg-6" id="wholesale_section">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Package Name</span>
							<?php $inputname = 'package_name'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>" 
								type		="text" 
								class		="form-control" 
								placeholder	="" >
						</div>
					</div>
					<div class="col-lg-6" id="wholesale_section">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Monthly Charge</span>
							<?php $inputname = 'monthly_charge'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>" 
								type		="text" 
								class		="form-control" 
								placeholder	="" >
						</div>
					</div>
					<!-- END wholesale sectin section -->
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Bill Cycle Month</span>

							<select class="col-lg-12" id="bill_cycle_month" name="bill_cycle_month" style="width: 100%;">
								<option value="1" <?php if ($input['bill_cycle_month'] == '1') { echo 'selected="selected"'; }; ?> >Monthly</option>
								<option value="2" <?php if ($input['bill_cycle_month'] == '2') { echo 'selected="selected"'; }; ?> >BiMonthly</option>
								<option value="3" <?php if ($input['bill_cycle_month'] == '3') { echo 'selected="selected"'; }; ?> >Quarterly</option>
								<option value="4" <?php if ($input['bill_cycle_month'] == '4') { echo 'selected="selected"'; }; ?> >4 Monthly</option>
								<option value="6" <?php if ($input['bill_cycle_month'] == '6') { echo 'selected="selected"'; }; ?> >Half Yearly</option>
								<option value="12" <?php if ($input['bill_cycle_month'] == '12') { echo 'selected="selected"'; }; ?> >Yearly</option>
							</select>

						</div>
					</div>

					<div class="col-lg-6" style="padding: 0px;">
						<div class="col-lg-5">
							<div class="input-group">
								<span class="input-group-addon input_group_150">Contract Month</span>
								<?php $inputname = 'contract_month'; ?>
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input[$inputname]); ?>" 
									type		="text" 
									class		="form-control"  
									placeholder	="" >
							</div>
						</div>
						<div class="col-lg-3 text-right">
							
							<?php if(!empty($input['customer_no'])): ?>
								<a href="<?php echo base_url('customer/deposit_details/'.$input['customer_no']); ?>">
									Deposit 
								</a>
							<?php endif; ?>
						</div>
						<div class="col-lg-4 text-right">
							<a onclick="show_package_history_popup('customer/package_history/',<?php echo $input['customer_no']?>);" style=cursor:pointer;>
								Package history
							</a>
						</div>
					</div>
					
					<div class="col-lg-6">
						<div class="input-group">						
							<span class="input-group-addon input_group_150"  >Product Category</span>
							<select class="col-lg-12" id="product_category" name="product_category" style="width: 100%;">
								<option value=""> -- SELECT -- </option>
								<?php
									foreach ($sel_product_category_list as $val) {
										echo "<option value='" . $val['product_code'] . "' " . set_select('product_code', $val['product_code'], ($val['product_code']==$input['product_category'] ? true : false) ) . ">" . $val['product_name']."</option>";
									}
								?>
							</select>
						</div>
					</div>

					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Expiry</span>
							<?php $inputname = 'contract_expiry'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo $input['contract_expiry']; ?>" 
								type		="text" 
								class		="form-control"  
								placeholder	="" readonly >
						</div>
					</div>
					
					<div class="col-lg-3">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Free Months</span>
							<input type="text" class="form-control" id="bill_waive_period" name="bill_waive_period" value="<?php echo set_value('bill_waive_period', $input['bill_waive_period']); ?>" placeholder="Months">
							<!--<span class="red">*Bill Waive will waive current activation prorated month and the next X months that is being set</span>-->
						</div>
					</div>

					<div class="col-lg-3" id="non_wholesale_section">
						<span>
							<?php $inputname = 'delay_trial_start'; ?>	
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="1"
								<?php echo set_checkbox('delay_trial_start', "1", ($input['delay_trial_start']==1? true : false) ); ?>
								type		="checkbox" 
								class		=""  
								placeholder	="">
								Bill first before waiver start
						</span>
					</div>

					<div class="col-lg-12">&nbsp;</div>

					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Free Upgrade Months</span>
							<input type="text" class="form-control" id="free_package_upgrade" name="free_package_upgrade" value="<?php echo set_value('free_package_upgrade', $input['free_package_upgrade']); ?>" placeholder="Months">
						</div>
					</div>

					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150">Upgrade To</span>
							<select class="col-lg-12" id="upgrade_package_id" name="upgrade_package_id" style="width: 100%;">
								<option value="0">N/A</option>
								<?php
									foreach ($upgrade_package_list as $pkg_id => $val) {
										echo "<option value='" . $pkg_id . "' " . set_select('upgrade_package_id', $pkg_id, ($pkg_id==$input['upgrade_package_id'] ? true : false) ) . ">" . $val['name']."</option>";
									}
								?>
							</select>
						</div>
					</div>
					
				</fieldset>	

				<fieldset class='category-border'>		
					<legend class="category-border">Preferred Access & Email Login ID
					</legend>				
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150"  >Login ID<span class="red">*</span></span>								
							<?php $inputname = 'login_username'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="eg : john_doe" 
								<?php //echo !empty($input['customer_no']) ? 'READONLY' : '' ; ?>
								/>
							
							<?php if( empty($input['customer_no']) ){ ?>
							<span class="input-icon input-icon-right" style="display:inline;">
								<i class="ace-icon"><?php echo $_SESSION['config']['radius_suffix']; ?></i>
							</span>
							<?php } ?>
							
							
							<span class="input-group-btn">
								<button type="button" class="btn btn-purple btn-minier btn-verify"
								<?php //echo !empty($input['customer_no']) ? 'DISABLED' : '' ; ?> >
									<span class="ace-icon fa fa-search icon-on-right bigger-150"></span>
									<span class="hidden-xs">Check</span>
								</button>
							</span>
						</div>
						
						<div class="verifyAccountFailed" style="display:none;color:red;">
						Invalid account. Please try with another user name.
						</div>
						<div class="verifyAccountSuccess" style="display:none;color:blue;">
						Valid account.
						</div>
						<div class="verifyAccountSame" style="display:none;color:blue;">
						Current account username.
						</div>
						
					</div>
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150"  >Password</span>													
							<?php $inputname = 'login_password'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" >

							<span class="input-group-btn">
								<button type="button" class="btn btn-purple btn-minier btn-generate-password">
									<span class="ace-icon fa fa-refresh icon-on-right bigger-150"></span>
									<span class="hidden-xs">Generate</span>
								</button>
							</span>
						</div>
					</div>
					
				</fieldset>	

				<fieldset class="category-border">
					<legend class="category-border">
						Preferred Installation Time
					</legend>

					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150">
								Preferred Date
							</span>

							<?php $inputname = 'preferred_install_date'; ?>
								<input
									id="<?php echo $inputname; ?>"
									name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $preferred_install_date); ?>"
									type="text"
									class="form-control"
									placeholder=""/>
						</div>
					</div>

					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group_150">
								Time Slot
							</span>

							<select
								id="preferred_install_time"
								name="preferred_install_time"
								class="form-control" style="font-size: 12px; width: 100%;">

								<?php
								/*for ($hour = 0; $hour <= 23; $hour++) {

									$start = DateTime::createFromFormat(
										'H:i',
										sprintf('%02d:00', $hour)
									);

									$end = clone $start;
									$end->modify('+1 hours');

									$value = $start->format('H:i');

									$label =
										$start->format('g:i A') .
										' - ' .
										$end->format('g:i A');*/
									foreach (['10:00' => '10:00AM to 12:00AM', '13:00' => '1:00PM to 3:00PM', '15:00' => '3:00PM to 5:00PM', '17:00' => '5:00PM to 7:00PM'] as $time_key => $time_value) {
								?>
										<option
											value="<?= $time_key ?>"
											<?= ($preferred_install_time ?? '') == $time_key ? 'selected' : '' ?>>
											<?= $time_value ?>
										</option>
								<?php } ?>

							</select>
						</div>
					</div>
				</fieldset>

				<fieldset class='category-border'>		
					<legend class="category-border">Billing
					</legend>
					<div class="col-lg-12" id='signup_date_err'></div>
					
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Next Bill Date</span>
								
								<?php $inputname = 'next_bill_date'; ?>
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, ($input[$inputname]!='0000-00-00'?$input[$inputname]:'')  ); ?>"
									type		="text" 
									class		="form-control"  
									READONLY
									placeholder	="" >
						</div>
					</div>
				</fieldset>

			<fieldset class='category-border'>
				<legend class="category-border">Status
				</legend>

				<?php if (($termination_flow == 0) && ($current_termination_flow == 'C') && !empty($input['terminated_date'])) { ?>
					<span class="red">*Termination will take effect on <?php echo $input['terminated_date']; ?></span>
				<?php } ?>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group"  >Current Status</span>
						<?php $sel_status = set_value('current_status', $input['current_status']); ?>
						<select class="col-lg-12" id="current_status" name="current_status" style="width: 100%;">
							<option value="P" <?php if ($sel_status == 'P') { echo 'selected="selected"'; } ?>> Signup </option>
							<?php if ($input['customer_no'] != ''): ?>
								<option value="A" <?php if ($sel_status == 'A') { echo 'selected="selected"'; } ?>> Activated </option>
								<option value="S" <?php if ($sel_status == 'S') { echo 'selected="selected"'; } ?>> Suspended </option>
							<?php if (($termination_flow == 0) || ($current_termination_flow == 'C') || !empty($input['terminated_date'])) { ?>
								<option value="T" <?php if ($sel_status == 'T') { echo 'selected="selected"'; } ?>> Terminated </option>
								<?php } ?>
								<option value="C" <?php if ($sel_status == 'C') { echo 'selected="selected"'; } ?>> Cancelled </option>
							<?php endif; ?>
						</select>
					</div>
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group"  >Status Date<span class="red">*</span></span>
							
							<?php $inputname = 'transact_date'; ?>
							<?php $transact_date = set_value($inputname, $input[$inputname]  ); ?>
							<?php if (!empty($transact_date)) { $transact_date = date('Y-m-d', strtotime($transact_date)); } else { $transact_date = ''; } ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo $transact_date; ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" autocomplete="off" >
					</div>
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group"  >Suspended Date</span>
							
							<?php $inputname = 'suspended_date'; ?>
							<?php $suspended_date = set_value($inputname, $input[$inputname]  ); ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo $suspended_date; ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" autocomplete="off" >
							<!--<span class="red">*Set this suspended date for customers that are about to be suspended. System will auto update status to suspended once date has reached.</span>-->
					</div>
				</div>

				<?php if (($termination_flow == 0) || ($current_termination_flow == 'C') || !empty($input['terminated_date'])) { ?>
				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group"  >Terminated Date</span>
							
							<?php $inputname = 'terminated_date'; ?>
							<?php $terminated_date = set_value($inputname, $input[$inputname]  ); ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo $terminated_date; ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" autocomplete="off" >
							<!--<span class="red">*Set this terminated date for customers that terminated. System will auto update status to terminated once date has reached.</span>-->
					</div>
				</div>
				<?php } ?>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group">First Activate</span>
						
						<?php $inputname = 'first_activate'; ?>
						<?php $first_activate = set_value($inputname, $input[$inputname]); ?>
						
						<input 
							id="<?php echo $inputname; ?>" 
							name="<?php echo $inputname; ?>" 
							value="<?php echo $first_activate; ?>"
							type="text" 
							class="form-control" 
							placeholder="" 
							autocomplete="off" 
							readonly>
					</div>
				</div>

				<?php if (!empty($input['customer_no'])) { ?>

					<div class="col-lg-12 text-left">
						<a href="#" 
						class="btn btn-link btn-sm" 
						style="padding-left: 0; text-decoration: none; font-weight: 600;"
						onclick="show_status_history_popup('customer/status_history/', <?php echo $input['customer_no']?>);">
							<i class="glyphicon glyphicon-time"></i> Status History
						</a>

						<?php if ( ($termination_init || $termination_confirm) && $termination_flow == 1 ) { ?>

							<a href="#" 
							class="btn btn-link btn-sm" 
							style="padding-left: 0; text-decoration: none; font-weight: 600;"
							onclick="show_termination_history_popup('customer/termination_history/', <?php echo $input['customer_no']?>);">
								<i class="glyphicon glyphicon-time"></i> Termination Flow Log
							</a>

							<?php if ($input['current_status'] == 'A' || $input['current_status'] == 'S'): ?>
								<?php if (empty($new_package_effective_date)) { ?>
									<button style="margin-top: 5px;" type="button" class="btn btn-success btn-small" onclick="start_termination_process();" id="terminate_btn">Terminate Customer</button>
								<?php } else { ?>
									<span class="red">*New effective date in process</span>
								<?php } ?>

								<?php if (($current_termination_flow != 'A') && ($current_termination_flow != 'C')) { ?>
									<button style="margin-top: 5px;" type="button" class="btn btn-danger btn-small" onclick="cancel_termination_process();" id="terminate_btn"><i class="fa fa-undo"></i>
									Cancel Termination Process</button>

									<span class="red" style="padding: 10px;">*Please use the <b>Termination Form</b> below to send the request and follow up on the customer's response.</span>
								<?php } ?>
							<?php endif; ?>
						<?php } ?>

					</div>

				<?php } ?>

			</fieldset>
			
			</div>
		</fieldset>	
		<fieldset class='category-border-main'>	
			<div class="category-border-main bg-success text-center" >
				CUSTOMER DETAILS
			</div>			
			<div class="col-lg-6">
				<fieldset class='category-border'>		
					<legend class="category-border">
						General Information
					</legend>					
					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Account No</span>								
							<?php $inputname = 'customer_no'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" 
								readonly
								/>
						</div>

						<input type="hidden" id="profile_id" name="profile_id" value="<?php echo set_value('profile_id', $input['profile_id']); ?>" />
						<div class="input-group">
							<span class="input-group-addon input_group" >Profile</span>
							<?php $inputname = 'acc_name'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, ($input['acc_type'] === 'r' ? $input[$inputname] : $input['comp_name'])); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" />
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group" >Name<span class="red">*</span></span>
							<?php $inputname = 'name'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" />
						</div>
						
						<div class="input-group">
							<span class="input-group-addon input_group">Currency Code</span>								
							<?php $inputname = 'currency_code'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" />
						</div>
						
					</div>
					<div id="company_section" class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group">Company No</span>
							<?php $inputname = 'reg_no'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" >
						
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group">SST No</span>
							<?php $inputname = 'gst_no'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" >
						
						</div>
					</div>	
				</fieldset>	
			</div>			
			<div class="col-lg-6" id="install_address">
				<fieldset class='category-border'>
					<legend class="category-border">
						Installation Address
					</legend>
					<div class="col-lg-12">
						
						<div>
							<b>Bill By </b>
							<span class="input-checkbox-group-post">
								<?php $inputname = 'bill_by_post'; ?>
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="1"
									<?php echo set_checkbox($inputname, "1", ($input[$inputname]==1? true : false) ); ?>
									type		="checkbox" 
									class		=""
									placeholder	="" >
									Post
								&nbsp;								
								<?php $inputname = 'bill_by_email'; ?>		
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="1"
									<?php echo set_checkbox($inputname, "1", ($input[$inputname]==1? true : false) ); ?>
									type		="checkbox" 
									class		=""    
									placeholder	="" >
									Email
							</span>

							<?php if ($input['current_status'] == 'A' && $input['customer_no'] != ''): ?>
								<?php if (empty($new_package_effective_date)): ?>
									
									<button style="margin-bottom: 5px;" type="button" class="btn btn-success btn-small" onclick="start_relocation_process();" id="relocate_btn">Relocate Customer</button>

									<button style="margin-bottom: 5px;" type="button" class="btn btn-danger btn-small" onclick="cancel_relocation_process();" id="cancel_relocate_btn">Cancel Relocate Action</button>

								<?php else: ?>

									<div class="text-right">
										<button style="margin-bottom: 5px;" type="button" class="btn btn-danger btn-small" onclick="reverse_relocation_process();" id="terminate_relocate_btn">
											<i class="fa fa-undo"></i>
											Reverse
										</button>
									</div>
								
								<?php endif; ?>
							<?php endif; ?>
							
						</div>

						<div id="relocate_help"><span class="red" id="relocate_help_span">*Please fill up the address below with new customer installation address and effective date and click the save button.</span></div>

						<span id="relocation_eff_date_wrapper"></span>

						<?php if (!empty($new_package_effective_date)): ?>
							<div class="alert alert-info" style="margin-bottom: 15px; border-left: 5px solid #31708f; background-color: #f7fbfd;">
								<span style="margin-top: 0; color: #31708f;"><strong><i class="fa fa-info-circle"></i> New Installation Address (Upcoming Effective Change)</strong></span>
								<hr style="margin-top: 5px; margin-bottom: 10px; border-top: 1px solid #bce8f1;">
								
								<?php 
									$display_building = $input['new_building'] ?? '-';
									foreach ($sel_building_list as $b_val) {
										if ($b_val['building_no'] == $input['new_building'] ?? '-') {
											$display_building = $b_val['name'];
											break;
										}
									}
								?>

								<table style="margin-bottom: 10px; background: transparent;">
									<tbody>
										<?php 
											$display_building = '-';
											if (!empty($input['new_building'])) {
												foreach ($sel_building_list as $b_val) {
													if ($b_val['building_no'] == $input['new_building']) {
														$display_building = $b_val['name'];
														break;
													}
												}
											}
										?>
										<tr>
											<td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Building:</td>
											<td class="text-primary"><strong><?php echo $display_building; ?></strong></td>
										</tr>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">Unit No.:</td>
											<td><?php echo !empty($input['new_unit_no']) ? $input['new_unit_no'] : '-'; ?></td>
										</tr>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">Address Line 1:</td>
											<td><?php echo !empty($input['new_addr1']) ? $input['new_addr1'] : '-'; ?></td>
										</tr>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">Address Line 2:</td>
											<td><?php echo !empty($input['new_addr2']) ? $input['new_addr2'] : '-'; ?></td>
										</tr>

										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">Address Line 3:</td>
											<td><?php echo !empty($input['new_addr3']) ? $input['new_addr3'] : '-'; ?></td>
										</tr>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">City:</td>
											<td><?php echo !empty($input['new_city']) ? $input['new_city'] : '-'; ?></td>
										</tr>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">Postcode:</td>
											<td><?php echo !empty($input['new_postcode']) ? $input['new_postcode'] : '-'; ?></td>
										</tr>

										<?php 
											$display_state = '-';
											if (!empty($input['new_state'])) {
												foreach ($sel_state_list as $s_val) {
													if ($s_val['state_code'] == $input['new_state']) {
														$display_state = $s_val['name'];
														break;
													}
												}
											}
										?>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">State:</td>
											<td><?php echo $display_state; ?></td>
										</tr>
										<tr>
											<td style="text-align: right; padding-right: 15px;" class="black">Effective Date:</td>
											<td class="pink"><strong><?php echo !empty($input['new_package_effective_date']) ? $input['new_package_effective_date'] : '-'; ?></strong></td>
										</tr>
									</tbody>
								</table>
								
								<p class="text-warning" style="margin-top: 10px; margin-bottom: 0; font-size: 11px; line-height: 1.4;">
									<i class="fa fa-exclamation-triangle"></i> * The new package is currently in its transition period. The address above is for preview purposes only; submitting this form will still maintain and link to your original address record.
								</p>
							</div>

							<input type="hidden" id="building" name="building" value="<?php echo set_value('building', $input['building']); ?>">
							<input type="hidden" id="inst_unit_no" name="inst_unit_no" value="<?php echo set_value('inst_unit_no', $input['inst_unit_no']); ?>">
							<input type="hidden" id="inst_addr1" name="inst_addr1" value="<?php echo set_value('inst_addr1', $input['inst_addr1']); ?>">
							<input type="hidden" id="inst_addr2" name="inst_addr2" value="<?php echo set_value('inst_addr2', $input['inst_addr2']); ?>">
							<input type="hidden" id="inst_addr3" name="inst_addr3" value="<?php echo set_value('inst_addr3', $input['inst_addr3']); ?>">
							<input type="hidden" id="inst_city" name="inst_city" value="<?php echo set_value('inst_city', $input['inst_city']); ?>">
							<input type="hidden" id="inst_postcode" name="inst_postcode" value="<?php echo set_value('inst_postcode', $input['inst_postcode']); ?>">
							<input type="hidden" id="inst_state" name="inst_state" value="<?php echo set_value('inst_state', $input['inst_state']); ?>">

						<?php else: ?>
							<div class="input-group">
								<span class="input-group-addon input_group">Building</span>
								<select class="col-lg-12" id="building" name="building" onchange="autoFillAddress();" style="width: 100%;">
									<option value="0" <?php echo set_select('building', '', ($input['building'] == '0' ? true : false) ); ?> >-</option>
									<?php
										foreach ($sel_building_list as $val) {
											echo "<option value='" . $val['building_no'] . "' " . set_select('building', $val['building_no'], ($val['building_no']==$input['building'] ? true : false) ) . ">" . $val['name']."</option>";
										}
									?>
								</select>
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">Unit No.</span>                        
								<?php $inputname = 'inst_unit_no'; ?>                       
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text" class="form-control" placeholder="Unit No." >
							</div>
							
							<div class="input-group">
								<span class="input-group-addon input_group">Address Line 1<span class="red">*</span></span>                        
								<?php $inputname = 'inst_addr1'; ?>                    
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text" class="form-control" placeholder="Address 1" >  
							</div>
							
							<div class="input-group">
								<span class="input-group-addon input_group">Address Line 2</span>                                            
								<?php $inputname = 'inst_addr2'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text" class="form-control" placeholder="Address 2" >  
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">Address Line 3</span>                                            
								<?php $inputname = 'inst_addr3'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text" class="form-control" placeholder="Address 3" >  
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">City<span class="red">*</span></span>
								<?php $inputname = 'inst_city'; ?>                      
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text" class="form-control" placeholder="City" >   
								<span class="input-group-addon input_group">Postcode<span class="red">*</span></span>
								<?php $inputname = 'inst_postcode'; ?>                      
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text" class="form-control" placeholder="Postcode" >
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">State<span class="red">*</span></span>
								<select class="col-lg-12" id="inst_state" name="inst_state" style="width: 100%;">
									<?php
										foreach ($sel_state_list as $val) {
											echo "<option value='" . $val['state_code'] . "' " . set_select('inst_state', $val['state_code'], ($val['state_code']==$input['inst_state'] ? true : false) ) . ">" . $val['name']."</option>";
										}
									?>
								</select>
							</div>
						<?php endif; ?>

						<div class="input-group">
							<span class="input-group-addon input_group">Phone</span>											
							<?php $inputname = 'inst_phone'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="eg.60188888888" 
								onkeypress="return /[0-9]/i.test(event.key)" >	
						</div>		

						<div class="input-group">
							<span class="input-group-addon input_group">Email</span>											
							<?php $inputname = 'inst_email'; ?>
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control" >	
						</div>			
						
					</div>
					
				</fieldset>
			</div>
				
		</fieldset>

		<fieldset class="category-border-main">							
			<div class="category-border-main bg-success text-center" >
				OTHER CHARGES	
			</div>

			<div class="col-lg-12">

				<div class="table-responsive">
					<table id="other_charge_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">

						<thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
							<th class="col-1">Charge (Name)</th>
							<th style="width:300px;" class="col-1">Type</th>
							<th style="width:150px;" class="col-1">Amount</th>
							<th class="col-2">Remark</th>
							<th style="width:100px;" class="col-1">End Date</th>
							<th style="width:50px" class="text-center"><i class='fa fa-ellipsis-v'></i></th>
						</thead>

						<tbody>
							<?php 
							$charge_block_id = 0;
							if (!empty($other_charges)) {
							foreach ($other_charges as $charge_key => $charge_arr) { 
							?>
								<tr class="charge_block" data-id="<?php echo $charge_block_id; ?>">						
									<td>
										<input 
											id			="charge_name_<?php echo $charge_block_id; ?>" 
											name		="charge_name[]" 
											value		="<?php echo $charge_arr['ac_name']; ?>"
											type		="text" 
											class		="form-control"  
											placeholder	="" />
									</td>
									<td>
										<select id="charge_type_<?php echo $charge_block_id; ?>" name="charge_type[]" class="col-lg-12" style="height:25px; width: 100%; min-width: 200px;">
											<option value=''>--Please Select--</option>
											<?php
											if(!empty($sel_bill_type))
											{
												foreach($sel_bill_type as $bill_type_key => $bill_type_val)
												{
													$selected = '';
													if ($bill_type_key == $charge_arr['bill_type']) {
														$selected = 'selected="selected"';
													}
													echo '<option '.$selected.' value="'.$bill_type_key.'" >'.$bill_type_val.'</option>';
												}
											} 
											?>
										</select>
									</td>
									<td>
										<input 
											id			="charge_amount_<?php echo $charge_block_id; ?>" 
											name		="charge_amount[]" step="0.01" 
											value		="<?php echo $charge_arr['amount']; ?>"
											type		="number" 
											class		="form-control"  
											placeholder	="0.00" 
											/>
									</td>
									<td>
										<input 
											id			="charge_remark_<?php echo $charge_block_id; ?>" 
											name		="charge_remark[]" 
											value		="<?php echo $charge_arr['remark']; ?>"
											type		="text" 
											class		="form-control"  
											placeholder	="" />
									</td>
									<td>
										<input 
											id			="charge_end_date_<?php echo $charge_block_id; ?>" 
											name		="charge_end_date[]" 
											value		="<?php echo $charge_arr['end_date']; ?>"
											type		="text" 
											class		="form-control other_charges_end_date"  
											placeholder	="" />
									</td>
									<td class="text-center">
										<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="del_charge_row(<?php echo $charge_block_id; ?>)"></i>
									</td>
								</tr>
							<?php $charge_block_id++; } } ?>
						</tbody>

					</table>
				</div>

				<div class="col-md-12" style="margin-top:10px;">
					<div class="col-12 mt-2 text-sm-right">
						<button type="button" class="btn btn-green btn-small" onclick="add_charge_item();">Add</button>
					</div>
				</div>

			</div>

		</fieldset>

		<fieldset class="category-border-main">							
			<div class="category-border-main bg-success text-center" >
				TECHNICAL (For Technical Staff to fill in)	
			</div>

			<fieldset class='category-border'>
				<legend class="category-border">
					Static IP
				</legend>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150"  >Static IPv4 Addr</span>
						<?php $inputname = 'framed_ip_address'; ?>						
						<input 
							id			="<?php echo $inputname; ?>" 
							name		="<?php echo $inputname; ?>" 
							value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
							type		="text" 
							class		="form-control"  
							placeholder	="For fixed IP customer only, leave it blank if irrelevant" />
					</div><!-- 103.91.240.200/32 -->
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150"  >Static IPv6 Addr</span>
						<?php $inputname = 'framed_ipv6_address'; ?>						
						<input 
							id			="<?php echo $inputname; ?>" 
							name		="<?php echo $inputname; ?>" 
							value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
							type		="text" 
							class		="form-control"  
							placeholder	="For fixed IP customer only, leave it blank if irrelevant" />
					</div><!-- 103.91.240.200/32 -->
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150"  >Framed IPV4 Route</span>													
						<?php $inputname = 'framed_route'; ?>						
						<input 
							id			="<?php echo $inputname; ?>" 
							name		="<?php echo $inputname; ?>" 
							value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
							type		="text" 
							class		="form-control"  
							placeholder	="For fixed IP customer only, leave it blank if irrelevant" >
					</div><!-- 103.91.241.0/29 0.0.0.0 150 -->
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150"  >Framed IPV6 Route</span>
						<?php $inputname = 'framed_ipv6_route'; ?>						
						<input 
							id			="<?php echo $inputname; ?>" 
							name		="<?php echo $inputname; ?>" 
							value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
							type		="text" 
							class		="form-control"  
							placeholder	="For fixed IP customer only, leave it blank if irrelevant" />
					</div><!-- 103.91.240.200/32 -->
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150"  >Framed Netmask</span>
						<?php $inputname = 'framed_netmask'; ?>						
						<input 
							id			="<?php echo $inputname; ?>" 
							name		="<?php echo $inputname; ?>" 
							value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
							type		="text" 
							class		="form-control"  
							placeholder	="For fixed IP customer only, leave it blank if irrelevant" />
					</div><!-- 103.91.240.200/32 -->
				</div>

			</fieldset>

			<fieldset class='category-border'>
				<legend class="category-border">
					Router
				</legend>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150">Router</span>
						<select class="col-lg-12" id="router_id" name="router_id" style="width: 100%;">
						<option value="0" <?php echo set_select('router_id', '', ($input['router_id'] == '0' ? true : false)) ?> >-</option>
							<?php
								foreach ($sel_router_list as $val) {
									echo "<option value='" . $val['id'] . "' " . set_select('router_id', $val['id'], ($val['id']==$input['router_id'] ? true : false) ) . ">" . $val['name']."</option>";
								}
							?>
						</select>
					</div>
				</div>

				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group_150"  >Interface ID</span>													
						<?php $inputname = 'interface_id'; ?>						
						<input 
							id			="<?php echo $inputname; ?>" 
							name		="<?php echo $inputname; ?>" 
							value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
							type		="text" 
							class		="form-control"  
							placeholder	="" >
					</div>
				</div>
				
				<br><br>

				<div class="row">
					<div class="col-lg-12">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group_150"  >Core Router ID</span>													
								<?php $inputname = 'core_router_id'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" >
							</div>
						</div>
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group_150"  >Core Router Interface ID</span>													
								<?php $inputname = 'core_router_interface_id'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" >
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group_150"  >Edge Router ID</span>													
								<?php $inputname = 'edge_router_id'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" >
							</div>
						</div>
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group_150"  >Edge Router Interface ID</span>													
								<?php $inputname = 'edge_router_interface_id'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" >
							</div>
						</div>
					</div>
				</div>

			</fieldset>

			<fieldset class="category-border" style="padding-bottom: 0px;">
				<legend class="category-border">
					Equipment
				</legend>
				<div class="col-lg-12">
					<div style="margin-bottom: 15px;">
						<div class="row">
							<?php if (!empty($equipment_list)) { ?>
								<?php foreach ($equipment_list as $eq) { ?>
									<div class="col-sm-4">
										<div class="list-group-item" style="padding: 8px 12px; background: #f9f9f9; border-left: 3px solid #5bc0de; margin-bottom: 10px; border-radius: 4px;">
											<div style="font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
												<?php echo $eq['equipment_name']; ?>
											</div>
											<small class="text-muted">SN: <?php echo !empty($eq['serial_no']) ? $eq['serial_no'] : 'N/A'; ?></small>
										</div>
									</div>
								<?php } ?>
							<?php } else { ?>
								<div class="col-xs-12">
									<div class="list-group-item text-muted" style="font-style: italic; font-size: 13px; border-left: 3px solid #ddd;">
										No equipment recorded
									</div>
								</div>
							<?php } ?>
						</div>

						<?php $inputname = 'serial_num'; ?>
						<input type="hidden" name="<?php echo $inputname; ?>" value="<?php echo set_value($inputname, $input[$inputname]); ?>" />
					</div>
				</div>
			</fieldset>

			<fieldset class='category-border' id="dia_area" style="display:none;">		
				<legend class="category-border">DIA Information</legend>				
				<input type="hidden" name="selected_dia_vars" id="selected_dia_vars" value="<?php echo set_value('selected_dia_vars', $input['selected_dia_vars']); ?>">
				<div id="dia_rows_area"></div>

			</fieldset>	

			<?php if ($has_jumpstart_permission) {?>
				<div class="col-md-12 text-right">
					<button id="btJumpStartDown" name="btJumpStartDown" type="button" value="btJumpStartDown" class="btn btn-secondary" onclick="dia_jumpstart('<?php echo $input['customer_no'] ?>', 'down')" <?php echo tooltip_helper('DIA Interface Down'); ?>>
						<i class="menu-icon fa fa-chain-broken white" data-toggle="tooltip" title=""></i>
						DIA Interface Down
					</button>
					<button id="btJumpStartUp" name="btJumpStartUp" type="button" value="btJumpStartUp" class="btn btn-warning" onclick="dia_jumpstart('<?php echo $input['customer_no'] ?>', 'up')" <?php echo tooltip_helper('DIA Interface Up'); ?>>
						<i class="menu-icon fa fa-link white" data-toggle="tooltip" title=""></i>
						DIA Interface Up
					</button>
				</div>
			<?php } ?>

		</fieldset>

		<fieldset class="category-border-main">							
			<div class="category-border-main bg-success text-center" >
				OTHERS	
			</div>
			<div class="col-lg-6 bt-1">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Project Name</span>
					
					<?php $inputname = 'project_name'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
						type		="text" 
						class		="form-control"  
						placeholder	="" >
				</div>
			</div>
			<div class="col-lg-6 bt-1">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Unit Name</span>
					
					<?php $inputname = 'unit_name'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
						type		="text" 
						class		="form-control"  
						placeholder	="" >
				</div>
			</div>

			<div class="col-lg-12" style="padding-top: 1rem;"></div>

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Agent</span>
					<select class="col-lg-12" id="dealer" name="dealer" style="width: 100%;">
						<option value="" <?php echo set_select('dealer', '', ($input['dealer'] == '' ? true : false) ); ?> >-</option>
						<?php
							foreach ($sel_dealer_list as $val) {
								echo "<option value='" . $val['dealer_no'] . "' " . set_select('dealer', $val['dealer_no'], ($val['dealer_no']==$input['dealer'] ? true : false) ) . ">" . $val['name']."</option>";
							}
						?>
					</select>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Payment Term</span>
					
					<?php $inputname = 'payment_term'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
						type		="text" 
						class		="form-control"  
						placeholder	="" >
				</div>
			</div>
			
			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Installer Name</span>
					<select class="col-lg-12" id="installer_name" name="installer_name" style="width: 100%;">
					<option value="" <?php echo set_select('installer_name', '', (($input['installer_name'] ?? '') == '' || ($input['installer_name'] ?? '') == '0') ); ?> >-</option>
						<?php
							foreach ($sel_installer_list as $val) {
								echo "<option value='" . $val['idx'] . "' " . set_select('installer', $val['idx'], ($val['idx']==$input['installer_name'] ? true : false) ) . ">" . $val['display_name']."</option>";
							}
						?>
					</select>
				</div>
			</div>

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Caller ID</span>
					<?php $inputname = 'caller_id'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
						type		="text" 
						class		="form-control"  
						placeholder	="for subscriber phone services, ID up to 20 chars" maxlength="20">
				</div>
			</div>
			
			<div class="col-lg-12">
				<div class="input-group">
					<span class="input-group-addon input_group" style="min-width:120px; text-align:right;">Remark</span>
					<?php $inputname = 'remark'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
						type		="text" 
						class		="form-control"  
						placeholder	="" >
				</div>
			</div>
		</fieldset>	
		<div class="col-md-12">
			<div class="button-group d-flex flex-column flex-md-row gap-2 justify-content-start align-items-stretch align-items-md-center">
				<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
					Cancel
				</button>

				<?php if (
						$current_termination_flow != 'A'
						) { ?>

					<!-- Termination flow buttons -->

					<button id="btTerminationForm" name="btTerminationForm" type="button" value="btTerminationForm" class="btn btn-info" onclick="window.location.href = '<?php echo base_url('customer/termination_form/'.$input['customer_no']); ?>';" <?php echo tooltip_helper('Print Termination Form'); ?>>
						<i class="menu-icon fa fa-print white" data-toggle="tooltip" title=""></i>
						Termination Form
					</button>

				<?php } else { ?>

					<button id="btPrint" name="btPrint" type="button" value="btPrint" class="btn btn-info" onclick="window.location.href = '<?php echo base_url('customer/installation_form/'.$input['customer_no']); ?>';" <?php echo tooltip_helper('Print Installation Form'); ?>>
						<i class="menu-icon fa fa-print white" data-toggle="tooltip" title=""></i>
						Installation Form
					</button>
					
					<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('customer','M');?> >
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>		
						Save					
					</button>

				<?php } ?>

				<span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>
							
			</div>					
		</div>
	</form>

	<div id="clear" style="clear:both;"></div>

	<!-- packageHistoryModal -->
	<div class="modal fade" id="packageHistoryModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" style="max-height:80vh; display:flex; flex-direction:column;">
				<div class="modal-header" style="flex-shrink:0;">
					<button type="button" class="close" data-dismiss="modal">
						&times;
					</button>
					<h4 class="modal-title">Package History</h4>
				</div>

				<div class="modal-body" id="packageHistoryPopupContent" style="overflow-y:auto; flex:1;"></div>

				<div class="modal-footer" style="flex-shrink:0;">
					<?php if ($current_termination_flow == 'A') { ?>
					<button class="btn btn-success renew-button" style="display:none;" onclick="renewContract()">
						Renew
					</button>
					<?php } ?>
					<button class="btn btn-default" data-dismiss="modal">
						Close
					</button>
				</div>

			</div>
		</div>
	</div>

	<!-- statusHistoryModal -->
	<div class="modal fade" id="statusHistoryModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" style="max-height:80vh; display:flex; flex-direction:column;">
				<div class="modal-header" style="flex-shrink:0;">
					<button type="button" class="close" data-dismiss="modal">
						&times;
					</button>
					<h4 class="modal-title">Status History</h4>
				</div>

				<div class="modal-body" id="statusHistoryPopupContent" style="overflow-y:auto; flex:1;"></div>

				<div class="modal-footer" style="flex-shrink:0;">
					<button class="btn btn-default" data-dismiss="modal">Close</button>
				</div>

			</div>
		</div>
	</div>

	<!-- terminationHistoryModal -->
	<div class="modal fade" id="terminationHistoryModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" style="max-height:80vh; display:flex; flex-direction:column;">
				<div class="modal-header" style="flex-shrink:0;">
					<button type="button" class="close" data-dismiss="modal">
						&times;
					</button>
					<h4 class="modal-title">Termination Flow Log</h4>
				</div>

				<div class="modal-body" id="terminationHistoryPopupContent" style="overflow-y:auto; flex:1;"></div>

				<div class="modal-footer" style="flex-shrink:0;">
					<button class="btn btn-default" data-dismiss="modal">Close</button>
				</div>

			</div>
		</div>
	</div>
</div>

<?php if (!empty($input['customer_no'])) { ?>
	<?php include_once( APPPATH . 'views/templates/audit_info.php'); ?>
<?php } ?>

<?php
$billtypeOption 		= '';
if(!empty($sel_bill_type))
{
	foreach($sel_bill_type as $bill_type_key => $bill_type_val)
	{
		$billtypeOption .= '<option value="'.$bill_type_key.'" >'.$bill_type_val.'</option>';
	}
}
?>
<script>
	var sel_package_list = <?php echo json_encode($sel_package_list) ?>;
	
	var accessView 	 = '<?php echo check_acl('customer', 'V', false ) == true ? 1 : 0 ; ?>';
	var accessModify = '<?php echo check_acl('customer', 'M', false ) == true ? 1 : 0 ; ?>';
	if( accessView == 1 && accessModify == 0 ){
		$(':input').attr('READONLY','READONLY');
		$('option:not(:selected)').attr('disabled', true);
	}

	var profile_list = <?php echo json_encode($profile_arr) ?>;

	var new_account = '<?php echo !empty($input['customer_no']) ? '0' : '1' ; ?>';
	var new_submit = '<?php echo set_value('new_submit', '0'); ?>';

	var billtypeOption = '<?php echo $billtypeOption; ?>';

	var charge_block_id = '<?php echo $charge_block_id; ?>';
	charge_block_id = parseInt(charge_block_id);

	var new_effective_date_empty = '<?php echo empty($input['new_package_effective_date']) ? true : false; ?>';

	var effective_date_not_reached = '<?php echo $effective_date_not_reached; ?>';

	var new_package_id = '<?php echo $input['new_package_id']; ?>';

	var old_contract_month = '<?php echo $input['contract_month'] ?>';

	var sel_state_list = <?php echo json_encode($sel_state_list); ?>;

	var sel_building_list = <?php echo json_encode($sel_building_list); ?>;

	var current_termination_flow = '<?php echo $current_termination_flow; ?>';
</script>
<script src="<?php echo base_url("js/itelco/customer.js?".cssjs_ver()); ?>" ></script>
<?php include 'panel_footer.php';?>
