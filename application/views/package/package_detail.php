<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('package');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="package_detail" name="package_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">

				<input type="hidden" id="dia_vars" name="dia_vars" value="<?php echo set_value('dia_vars', $input['dia_vars']); ?>" />

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						PACKAGE
					</div>			
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Status</span>
									<select class="col-lg-12" id="status" name="status" style="width: 100%;">
										<option value="a" <?php echo set_select('status', 'a', ($input['status'] == 'a' ? true : false) ); ?> >Active</option>
										<option value="i" <?php echo set_select('status', 'i', ($input['status'] == 'i' ? true : false) ); ?> >Inactive</option>
									</select>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Category</span>
									<select class="col-lg-12" id="category" name="category" onclick="chk_dia();" style="width: 100%;">
										<?php
											foreach ($sel_category_list as $val) {
												echo "<option value='" . $val['category_code'] . "' " . set_select('category', $val['category_code'], ($val['category_code']==$input['category'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Package No</span>
									<input type="text" class="form-control" id="package_no" name="package_no" value="<?php echo set_value('package_no', $input['package_no']); ?>" placeholder="Package No" readonly>
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Name</span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Package Name">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Private</span>
									<input type="checkbox" id="private" name="private" value="1" <?php echo set_checkbox('private', '1', ($input['private']==1? true:false) ); ?> />
								</div>
							</div>
						</div>
						<br/>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Domain</span>
									<select class="col-lg-12" id="domain" name="domain" style="width: 100%;">.
										<option value=""> -- SELECT -- </option>
										<?php
											foreach ($sel_domain_list as $val) {
												echo "<option value='" . $val['domain_id'] . "' " . set_select('domain', $val['domain_id'], ($val['domain_id']==$input['domain'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
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
									<span class="input-group-addon input_group_150">Building</span>
									<select multiple="multiple" id="building" name="building[]" class="select2" style="width:100%;" style="width: 100%;">
										<?php foreach($sel_building_list as $building): ?>
											<option value="<?= $building['building_no'] ?>" <?= in_array($building['building_no'], $selected_building) ? 'selected' : '' ?>><?= $building['name'] ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">No of Fixed IP</span>
									<input type="text" class="form-control" id="no_of_fixed_ip" name="no_of_fixed_ip" value="<?php echo set_value('no_of_fixed_ip', $input['no_of_fixed_ip']); ?>" placeholder="No of Fixed IP">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">No of Email</span>
									<input type="text" class="form-control" id="no_of_email" name="no_of_email" value="<?php echo set_value('no_of_email', $input['no_of_email']); ?>" placeholder="No of Email">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Router</span>
									<select class="col-lg-12" id="router_id" name="router_id" style="width: 100%;">
										<option value=""> -- SELECT -- </option>
										<?php
											foreach ($sel_router_list as $val) {
												echo "<option value='" . $val['id'] . "' " . set_select('router_id', $val['id'], ($val['id']==$input['router_id'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
						</div>
						<br/>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Deposit</span>
									<input type="text" class="form-control" id="deposit" name="deposit" value="<?php echo set_value('deposit', $input['deposit']); ?>" placeholder="Deposit">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Stamp Duty</span>
									<input type="text" class="form-control" id="stamp_duty" name="stamp_duty" value="<?php echo set_value('stamp_duty', $input['stamp_duty']); ?>" placeholder="Stamp Duty">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Monthly Fee</span>
									<input type="text" class="form-control" id="monthly_charge" name="monthly_charge" value="<?php echo set_value('monthly_charge', $input['monthly_charge']); ?>" placeholder="Monthly Charge">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Yearly Charge</span>
									<input type="text" class="form-control" id="yearly_charge" name="yearly_charge" value="<?php echo set_value('yearly_charge', $input['yearly_charge']); ?>" placeholder="Yearly Charge">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Reactivation Fee</span>
									<input type="text" class="form-control" id="reactivate_fee" name="reactivate_fee" value="<?php echo set_value('reactivate_fee', $input['reactivate_fee']); ?>" placeholder="Reactivation Charge">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Installation Fee</span>
									<input type="text" class="form-control" id="installation" name="installation" value="<?php echo set_value('installation', $input['installation']); ?>" placeholder="Installation">
								</div>
							</div>							
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">One Time Charge</span>
									<input type="text" class="form-control" id="one_time_charge" name="one_time_charge" value="<?php echo set_value('one_time_charge', $input['one_time_charge']); ?>" placeholder="One Time Charge">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Post Charges</span>
									<input type="text" class="form-control" id="post_charge" name="post_charge" value="<?php echo set_value('post_charge', $input['post_charge']); ?>" placeholder="Post Charges">
								</div>
							</div>					
						</div>
						<div class="row">
							<div class="col-lg-2">
								<div class="input-group">
									<span class="input-group-addon input_group_150"  >Package Month</span>
									<?php $inputname = 'package_month'; ?>
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
										type		="text" 
										class		="form-control"  
										placeholder	="" >
								</div>
							</div>
							<div class="col-lg-4">
								<span>
									<?php $inputname = 'delay_trial_start'; ?>	
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="1"
										<?php echo set_checkbox($inputname, $input[$inputname], ($input[$inputname] == 1? true : false) ); ?>
										type		="checkbox" 
										class		=""  
										placeholder	="" >
										Bill first before waiver start
								</span>
								<br />
								<span>
									<?php $inputname = 'installation_auto'; ?>	
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="1"
										<?php echo set_checkbox($inputname, $input[$inputname], ($input[$inputname] == 1? true : false) ); ?>
										type		="checkbox" 
										class		=""  
										placeholder	="" >
										Installation Fee Paid
								</span>
								<br />
								<span>
									<?php $inputname = 'one_time_charge_auto'; ?>	
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="1"
										<?php echo set_checkbox($inputname, $input[$inputname], ($input[$inputname] == 1? true : false) ); ?>
										type		="checkbox" 
										class		=""  
										placeholder	="" >
										One Time Charge Paid
								</span>
								<br />
								<span>
									<?php $inputname = 'stop_service_after'; ?>	
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="1"
										<?php echo set_checkbox($inputname, $input[$inputname], ($input[$inputname] == 1? true : false) ); ?>
										type		="checkbox" 
										class		=""  
										placeholder	="" >
										Stop service after package month
								</span>
								<br />
								<span>
									<?php $inputname = 'yearly_billing'; ?>	
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="1"
										<?php echo set_checkbox($inputname, $input[$inputname], ($input[$inputname] == 1? true : false) ); ?>
										type		="checkbox" 
										class		=""  
										placeholder	="" >
										Yearly Billing
								</span>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Bill Type</span>
									<select class="col-lg-12" id="bill_type" name="bill_type" style="width: 100%;">
										<?php
											foreach ($sel_bill_type_list as $val) {
												echo "<option value='" . $val['bill_type_id'] . "' " . set_select('bill_type', $val['bill_type_id'], ($val['bill_type_id']==$input['bill_type'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Free Months</span>
									<input type="text" class="form-control" id="bill_waive_period" name="bill_waive_period" value="<?php echo set_value('bill_waive_period', $input['bill_waive_period']); ?>" placeholder="Months">
									<!--<span class="red">*Bill Waive will waive current activation prorated month and the next X months that is being set</span>-->
								</div>
							</div>

							<div class="col-lg-12">
								&nbsp;
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Free Upgrade Month</span>
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

							<div class="col-lg-12">
								&nbsp;
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Line No.</span>
									<input type="text" class="form-control" id="lineno" name="lineno" value="<?php echo set_value('lineno', $input['lineno']); ?>" placeholder="0">
								</div>
							</div>

						</div>
					</div>
				</fieldset>

				<fieldset class='category-border-main dia_fields'>
					<div class="category-border-main bg-success text-center">
						DIA
					</div>

					<!--list of products here-->
					<div class="col-lg-12 dia_vars_list">
					</div>

					<div class="col-md-12" style="margin-top:10px;">
						<div class="col-12 mt-2 text-sm-right">
							<button type="button" class="btn btn-green btn-small" onclick="addDIAVar();">Add</button>
						</div>
					</div>

				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						PHONE
					</div>
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Domestic Rate</span>
									<input type="text" id="dom_rate" name="dom_rate" value="<?php echo set_value('dom_rate', $input['dom_rate']); ?>" placeholder="price per minute">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">International Rate</span>
									<input type="text" id="int_rate" name="int_rate" value="<?php echo set_value('int_rate', $input['int_rate']); ?>" placeholder="price per minute">
								</div>
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						AGENT
					</div>
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Comm Type</span>
									<select class="col-lg-12" id="dealer_comm_type" name="dealer_comm_type" style="width: 100%;">
										<option value="m" <?php echo set_select("dealer_comm_type", 'm', ($input['dealer_comm_type'] == 'm' ? true : false) ); ?> >Monthly</option>
										<option value="o" <?php echo set_select("dealer_comm_type", 'o', ($input['dealer_comm_type'] == 'o' ? true : false) ); ?> >One Time</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Monthly %</span>
									<input type="text" class="form-control" id="dealer_monthly_comm" name="dealer_monthly_comm" value="<?php echo set_value('dealer_monthly_comm', $input['dealer_monthly_comm']); ?>" placeholder="Monthly Commission" onkeypress="return /[0-9.]/i.test(event.key)">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Monthly Times</span>
									<input type="text" class="form-control" id="dealer_monthly_times" name="dealer_monthly_times" value="<?php echo set_value('dealer_monthly_times', $input['dealer_monthly_times']); ?>" placeholder="Claim Months" onkeypress="return /[0-9.]/i.test(event.key)">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">One Time</span>
									<input type="text" class="form-control" id="dealer_onetime_comm" name="dealer_onetime_comm" value="<?php echo set_value('dealer_onetime_comm', $input['dealer_onetime_comm']); ?>" placeholder="One Time Commission" onkeypress="return /[0-9.]/i.test(event.key)">
								</div>
							</div>
						</div>
						<br/>
					</div>
				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						RADIUS
					</div>
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Radius Group Name</span>
									<input type="text" class="radius_framepool-control" id="bandwidth" name="bandwidth" value="<?php echo set_value('bandwidth', $input['bandwidth']); ?>" placeholder="Bandwidth">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Radius Framepool</span>
									<input type="text" class="radius_framepool-control" id="radius_framepool" name="radius_framepool" value="<?php echo set_value('radius_framepool', $input['radius_framepool']); ?>" placeholder="eg.PUB_POOL">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Radius Egress</span>
									<input type="text" class="radius_egress-control" id="radius_egress" name="radius_egress" value="<?php echo set_value('radius_egress', $input['radius_egress']); ?>" placeholder="eg. 10M">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Radius Ingress</span>
									<input type="text" class="radius_ingress-control" id="radius_ingress" name="radius_ingress" value="<?php echo set_value('radius_ingress', $input['radius_ingress']); ?>" placeholder="eg. 10M">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group" style="width:200px;">Radius Mikrotik</span>
									<input type="text" class="radius_framepool-control" id="radius_mikrotik" name="radius_mikrotik" value="<?php echo set_value('radius_mikrotik', $input['radius_mikrotik']); ?>" placeholder="eg.510M/510M">
								</div>
							</div>
						</div>
					</div>
				</fieldset>

				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('package','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('package','D');?>>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
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
<script src="<?php //echo base_url("js/itelco/payment.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/itelco/package.js?".cssjs_ver()); ?>" ></script>
