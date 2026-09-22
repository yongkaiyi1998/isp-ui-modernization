<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('changepackage');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="changepackage_detail" name="changepackage_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Package Changing Request Details
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Customer Infomation
							</legend>
								<input type="hidden" class="form-control" id="id" name="id" value="<?php echo set_value('id', $input['id']); ?>">
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Customer Name</span>
									<input type="text" class="form-control" id="acc_name" name="acc_name" value="<?php echo set_value('acc_name', $input['acc_name']); ?>" placeholder="Customer Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">IC No</span>
									<input type="text" class="form-control" id="icno" name="icno" value="<?php echo set_value('icno', $input['icno']); ?>" placeholder="IC No">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Category</span>
									<input type="text" class="form-control" id="acc_type" name="acc_type" value="<?php echo set_value('acc_type', $input['acc_type']); ?>" placeholder="Category">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Mobile No</span>
									<input type="text" class="form-control" id="acc_mobileno" name="acc_mobileno" value="<?php echo set_value('acc_mobileno', $input['acc_mobileno']); ?>">
								</div>
							</div>
						</fieldset>
					</div>

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Billing Address
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Unit No</span>
									<input type="text" class="form-control" id="bill_unit_no" name="bill_unit_no" value="<?php echo set_value('bill_unit_no', $input['bill_unit_no']); ?>" placeholder="Unit No">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 1</span>
									<input type="text" class="form-control" id="bill_addr_1" name="bill_addr_1" value="<?php echo set_value('bill_addr_1', $input['bill_addr_1']); ?>" placeholder="Address Line 1">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 2</span>
									<input type="text" class="form-control" id="bill_addr_2" name="bill_addr_2" value="<?php echo set_value('bill_addr_2', $input['bill_addr_2']); ?>" placeholder="Address Line 2">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 3</span>
									<input type="text" class="form-control" id="bill_addr_3" name="bill_addr_3" value="<?php echo set_value('bill_addr_3', $input['bill_addr_3']); ?>" placeholder="Address Line 3">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">City</span>
									<input type="text" class="form-control" id="bill_city" name="bill_city" value="<?php echo set_value('bill_city', $input['bill_city']); ?>" placeholder="City">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">State</span>
									<select class="col-lg-12" id="bill_state" name="bill_state">
										<option value=""> -- SELECT -- </option>
										<?php
										$sel_bill_state = set_value('bill_state', $input['bill_state']);
										foreach ($sel_state_list as $val) {
											echo "<option value='" . $val['state_code'] . "' " . ($sel_bill_state == $val['state_code'] ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Postcode</span>
									<input type="text" class="form-control" id="bill_postcode" name="bill_postcode" value="<?php echo set_value('bill_postcode', $input['bill_postcode']); ?>" placeholder="Postcode">
								</div>
							</div>
						</fieldset>
					</div>

					<?php if($input['acc_type'] == 'Business') { ?>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Company Infomation
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Company Name</span>
									<input type="text" class="form-control" id="comp_name" name="comp_name" value="<?php echo set_value('comp_name', $input['comp_name']); ?>" placeholder="Company Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Company SSM</span>
									<input type="text" class="form-control" id="ssm" name="ssm" value="<?php echo set_value('ssm', $input['ssm']); ?>" placeholder="SSM">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Company TIN</span>
									<input type="text" class="form-control" id="tin" name="tin" value="<?php echo set_value('tin', $input['tin']); ?>" placeholder="TIN">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">PIC Name</span>
									<input type="text" class="form-control" id="pic_name" name="pic_name" value="<?php echo set_value('pic_name', $input['pic_name']); ?>" placeholder="PIC Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">PIC Mobile</span>
									<input type="text" class="form-control" id="pic_mobile" name="pic_mobile" value="<?php echo set_value('pic_mobile', $input['pic_mobile']); ?>" placeholder="PIC Mobile">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">PIC Email</span>
									<input type="text" class="form-control" id="pic_email_1" name="pic_email_1" value="<?php echo set_value('pic_email_1', $input['pic_email_1']); ?>" placeholder="PIC Email">
								</div>
							</div>
						</fieldset>
					</div>
					<?php } ?>
				</fieldset>

				<fieldset class='category-border-main' id="customer_details">	
					<div class="category-border-main bg-success text-center" >
						Current Package Details
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Current Package Details
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Customer No</span>
									<input type="text" class="form-control" id="customer_no" name="customer_no" value="<?php echo set_value('customer_no', $input['customer_no']); ?>" placeholder="Customer No">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package No</span>
									<input type="text" class="form-control" id="package_no" name="package_no" value="<?php echo set_value('package_no', $input['old_pkg']['package_no']); ?>" placeholder="Package No">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package Name</span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['old_pkg']['name']); ?>" placeholder="Package Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package Category</span>
									<input type="text" class="form-control" id="category" name="category" value="<?php echo set_value('category', $input['old_pkg']['category']); ?>" placeholder="Package Category">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Bandwidth</span>
									<input type="text" class="form-control" id="bandwidth" name="bandwidth" value="<?php echo set_value('bandwidth', $input['old_pkg']['bandwidth']); ?>" placeholder="Bandwidth">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Monthly Charge</span>
									<input type="text" class="form-control" id="monthly_charge" name="monthly_charge" value="<?php echo set_value('monthly_charge', $input['old_pkg']['monthly_charge']); ?>" placeholder="Monthly Charge">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Sign Up Date</span>
									<input type="text" class="form-control" id="signup_date" name="signup_date" value="<?php echo set_value('signup_date', $input['old_pkg']['signup_date']); ?>" placeholder="Sign Up Date">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Contract Month</span>
									<input type="text" class="form-control" id="contract_month" name="contract_month" value="<?php echo set_value('contract_month', $input['old_pkg']['package_month']); ?>" placeholder="Contract Month">
								</div>
							</div>
						</fieldset>
					</div>

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Installation Address
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Unit No</span>
									<input type="text" class="form-control" id="inst_unit_no" name="inst_unit_no" value="<?php echo set_value('inst_unit_no', $input['old_pkg']['inst_unit_no']); ?>" placeholder="Unit No">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 1</span>
									<input type="text" class="form-control" id="inst_addr1" name="inst_addr1" value="<?php echo set_value('inst_addr1', $input['old_pkg']['inst_addr1']); ?>" placeholder="Address Line 1">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 2</span>
									<input type="text" class="form-control" id="inst_addr2" name="inst_addr2" value="<?php echo set_value('inst_addr2', $input['old_pkg']['inst_addr2']); ?>" placeholder="Address Line 2">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 3</span>
									<input type="text" class="form-control" id="inst_addr3" name="inst_addr3" value="<?php echo set_value('inst_addr3', $input['old_pkg']['inst_addr3']); ?>" placeholder="Address Line 3">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">City</span>
									<input type="text" class="form-control" id="inst_city" name="inst_city" value="<?php echo set_value('inst_city', $input['old_pkg']['inst_city']); ?>" placeholder="City">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">State</span>
									<select class="col-lg-12" id="inst_state" name="inst_state">
										<option value=""> -- SELECT -- </option>
										<?php
										$sel_inst_state = set_value('inst_state', $input['old_pkg']['inst_state']);
										foreach ($sel_state_list as $val) {
											echo "<option value='" . $val['state_code'] . "' " . ($sel_bill_state == $val['state_code'] ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Postcode</span>
									<input type="text" class="form-control" id="inst_postcode" name="inst_postcode" value="<?php echo set_value('inst_postcode', $input['old_pkg']['inst_postcode']); ?>" placeholder="Postcode">
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>

				<fieldset class='category-border-main' id="customer_details">	
					<div class="category-border-main bg-success text-center" >
						Requested Package Details
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Requested Package Details
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package No</span>
									<input type="text" class="form-control" id="new_pkg_package_no" name="new_pkg_package_no" value="<?php echo set_value('new_pkg_package_no', $input['new_pkg']['package_no']); ?>" placeholder="Package No">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package Name</span>
									<input type="text" class="form-control" id="new_pkg_name" name="new_pkg_name" value="<?php echo set_value('new_pkg_name', $input['new_pkg']['name']); ?>" placeholder="Package Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package Category</span>
									<input type="text" class="form-control" id="new_pkg_category" name="new_pkg_category" value="<?php echo set_value('new_pkg_category', $input['new_pkg']['category']); ?>" placeholder="Package Category">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Bandwidth</span>
									<input type="text" class="form-control" id="new_pkg_bandwidth" name="new_pkg_bandwidth" value="<?php echo set_value('new_pkg_bandwidth', $input['new_pkg']['bandwidth']); ?>" placeholder="Bandwidth">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Monthly Charge</span>
									<input type="text" class="form-control" id="new_pkg_monthly_charge" name="new_pkg_monthly_charge" value="<?php echo set_value('new_pkg_monthly_charge', $input['new_pkg']['monthly_charge']); ?>" placeholder="Monthly Charge">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Package Month</span>
									<input type="text" class="form-control" id="new_pkg_package_month" name="new_pkg_monthly_charge" value="<?php echo set_value('new_pkg_monthly_charge', $input['new_pkg']['package_month']); ?>" placeholder="Package Month">
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>

				<fieldset class='category-border-main' id="customer_details">	
					<div class="category-border-main bg-success text-center" >
						Reason To Change
					</div>
					<div class="col-lg-12">
						<div class="input-group col-lg-12">
							<textarea class="form-control" id="reason" name="reason" rows="5" style="resize: vertical;"><?php echo set_value('reason', $input['reason']); ?></textarea>
						</div>
					</div>
				</fieldset>

				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('changepackage');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSO" name="btSO" type="submit" value="submit" class="btn btn-info" <?php echo tooltip_helper('Click To Convert Sales Order'); ?> <?php echo check_acl_btn('changepackage','M');?>>
							<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i>Sales Order
						</button>
						<button id="btPrint" name="btPrint"  type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Generate Form'); ?> <?php echo check_acl_btn('changepackage','M');?>>
							<i class="menu-icon fa fa-print" data-toggle="tooltip" title=""></i>Generate Form
						</button>				
					</div>					
				</div>
			</form>
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
</div>
	<div id="backgroundPopup" onclick="hide_popup();"></div>
	<script src="<?php echo base_url("js/itelco/changepackage.js?".cssjs_ver()); ?>" ></script>
