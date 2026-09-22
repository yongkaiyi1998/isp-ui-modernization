<?php include 'panel_header.php';?>
<div class="panel-heading noprint panel_fontsize">
		<span class="panel_space float_right">
			<a  href="<?php echo base_url('salesorder');?>">
				<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
			</a>
		</span>
		<?php echo $page_title; ?>
</div>

<div class="panel-body">
	<?php echo flash_data_helper($msg); ?>
	<form id="salesorder_detail" name="salesorder_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">

		<?php //==== hidden fields ===== ?>
			
		<input 	id="form_action" name="form_action" type="hidden" 
				value="<?php echo !empty($input['so_head']['so_id']) ? 'UPDATE' : 'NEW' ; ?>" />	

		<input 	id="so_head[so_id]" name="so_head[so_id]" type="hidden" 
				value="<?php echo set_value('so_head[so_id]', $input['so_head']['so_id']); ?>" />	

		<input 	id="so_head[cust_id]" name="so_head[cust_id]" type="hidden" 
				value="<?php echo set_value('so_head[cust_id]', $input['so_head']['cust_id']); ?>" />

		<input 	id="so_head[doc_rev_num]" name="so_head[doc_rev_num]" type="hidden" 
				value="<?php echo set_value('so_head[doc_rev_num]', $input['so_head']['doc_rev_num']); ?>" />

		<input 	id="so_head[revised]" name="so_head[revised]" type="hidden" 
				value="<?php echo set_value('so_head[revised]', $input['so_head']['revised']); ?>" />

		<input 	id="so_head[currency_code]" name="so_head[currency_code]" type="hidden" 
				value="<?php echo set_value('so_head[currency_code]', $input['so_head']['currency_code']); ?>" />

		<input 	id="so_head[currency_rate]" name="so_head[currency_rate]" type="hidden" 
				value="<?php echo set_value('so_head[currency_rate]', $input['so_head']['currency_rate']); ?>" />

		<input 	id="so_head[po_num]" name="so_head[po_num]" type="hidden" 
				value="<?php echo set_value('so_head[po_num]', $input['so_head']['po_num']); ?>" />

		<input 	id="so_head[shipping_fee]" name="so_head[shipping_fee]" type="hidden" 
				value="<?php echo set_value('so_head[shipping_fee]', $input['so_head']['shipping_fee']); ?>" />

		<input 	id="so_head[grand_total]" name="so_head[grand_total]" type="hidden" 
				value="<?php echo set_value('so_head[grand_total]', $input['so_head']['grand_total']); ?>" />

		<input 	id="so_head[round_val]" name="so_head[round_val]" type="hidden" 
				value="<?php echo set_value('so_head[round_val]', $input['so_head']['round_val']); ?>" />

		<input 	id="so_head[allow_rounding]" name="so_head[allow_rounding]" type="hidden" 
				value="<?php echo set_value('so_head[allow_rounding]', $input['so_head']['allow_rounding']); ?>" />

		<input 	id="so_head[status_approved]" name="so_head[status_approved]" type="hidden" 
				value="<?php echo set_value('so_head[status_approved]', $input['so_head']['status_approved']); ?>" />

		<input 	id="so_head[status_lock]" name="so_head[status_lock]" type="hidden" 
				value="<?php echo set_value('so_head[status_lock]', $input['so_head']['status_lock']); ?>" />

		<input 	id="so_head[status_void]" name="so_head[status_void]" type="hidden" 
				value="<?php echo set_value('so_head[status_void]', $input['so_head']['status_void']); ?>" />

		<input 	id="so_head[gst_app]" name="so_head[gst_app]" type="hidden" 
				value="<?php echo set_value('so_head[gst_app]', $input['so_head']['gst_app']); ?>" />

		<input 	id="so_head[grand_tax]" name="so_head[grand_tax]" type="hidden" 
				value="<?php echo set_value('so_head[grand_tax]', $input['so_head']['grand_tax']); ?>" />

		<input 	id="so_head[grand_subtotal]" name="so_head[grand_subtotal]" type="hidden" 
				value="<?php echo set_value('so_head[grand_subtotal]', $input['so_head']['grand_subtotal']); ?>" />

		<input 	id="so_head[base_amount]" name="so_head[base_amount]" type="hidden" 
				value="<?php echo set_value('so_head[base_amount]', $input['so_head']['base_amount']); ?>" />

		<input 	id="so_head[quotation_id]" name="so_head[quotation_id]" type="hidden" 
				value="<?php echo set_value('so_head[quotation_id]', $input['so_head']['quotation_id']); ?>" />

		<input 	id="so_head[dept_id]" name="so_head[dept_id]" type="hidden" 
				value="<?php echo set_value('so_head[dept_id]', $input['so_head']['dept_id']); ?>" />

		<input 	id="so_head[bill_fax]" name="so_head[bill_fax]" type="hidden" 
				value="<?php echo set_value('so_head[bill_fax]', $input['so_head']['bill_fax']); ?>" />

		<input 	id="so_head[del_fax]" name="so_head[del_fax]" type="hidden" 
				value="<?php echo set_value('so_head[del_fax]', $input['so_head']['del_fax']); ?>" />

		<input 	id="so_detail_rows" name="so_detail_rows" type="hidden" 
				value="<?php echo set_value('so_detail_rows', $input['so_detail_rows']); ?>" />

		<input id="temp_id" name="so_head[temp_id]" type="hidden"
			value="<?php echo set_value('so_head[temp_id]', $input['so_head']['temp_id']); ?>" />

		<input id="so_head[reg_no]" name="so_head[reg_no]" type="hidden"
			value="<?php echo set_value('so_head[reg_no]', $input['so_head']['reg_no']); ?>" />

		<input id="so_head[register_type]" name="so_head[register_type]" type="hidden"
			value="<?php echo set_value('so_head[register_type]', $input['so_head']['register_type']); ?>" />

		<input id="so_head[profile_id]" name="so_head[profile_id]" type="hidden"
			value="<?php echo set_value('so_head[profile_id]', $input['so_head']['profile_id']); ?>" />

		<input id="so_head[customer_no]" name="so_head[customer_no]" type="hidden"
			value="<?php echo set_value('so_head[customer_no]', $input['so_head']['customer_no']); ?>" />

		<input id="so_head[agent_id]" name="so_head[agent_id]" type="hidden"
			value="<?php echo set_value('so_head[agent_id]', $input['so_head']['agent_id']); ?>" />

		<input type="hidden" id="so_head[dia_vars]" name="so_head[dia_vars]" value="<?php echo set_value('so_head[dia_vars]', $input['so_head']['dia_vars']); ?>" />

		<input id="task" name="task" type="hidden"
			value="" /> 

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				SALES ORDER
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>		
					<legend class="category-border">Customer/PIC Information
					</legend>

					<div class="company_related ssm_related" style="display: none;">
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group ssm-title"  >SSM#<span class="red">*</span></span>
								<?php $inputname = 'so_head[comp_num]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['comp_num']); ?>"
									type		="text" 
									class		="form-control" 
									onkeypress="return /[0-9]/i.test(event.key)" 
									/>
							</div>
						</div>
						<div class="col-lg-12 ssm-exist orange mt-2" style="display:none;">
							Company SSM# already exists in Customer Profile.
						</div>
					</div>

					<div class="ic_related">
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group ic-title"  >IC. No/Passport<span class="red">*</span></span>
								<?php $inputname = 'so_head[icno]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['icno']); ?>"
									type		="text" 
									class		="form-control"  
									style="text-transform: uppercase" 
									onkeypress="return /[a-zA-Z0-9]/i.test(event.key)" 
									/>
							</div>
						</div>
						<div class="col-lg-12 icno-exist orange mt-2" style="display:none;">
							IC No./Passport already exists in Customer Profile.
						</div>
					</div>

					<div class="col-lg-12" style="padding-top: 15px;">
						<div class="input-group">
							<span class="input-group-addon input_group"  >SO Num</span>
							<?php $inputname = 'so_head[so_num]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['so_num']); ?>"
								type		="text" 
								class		="form-control"  
								readonly
								 />
						</div>
						<span class="red">*Leave blank for auto numbering</span>
					</div>

					<div class="col-lg-12 customer_name_field">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Customer Name<span class="red">*</span></span>
							<?php $inputname = 'so_head[cust_name]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['cust_name']); ?>"
								type		="text" 
								class		="form-control"  
								 />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >TIN</span>
							<?php $inputname = 'so_head[tin]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['tin']); ?>"
								type		="text" 
								class		="form-control"  
								 />

							<div class="input-group-append">
								<span class="input-group-text tin-valid" style="display:none; color: green; margin-left: 1rem;">
									<i class="fa fa-check-circle"></i> Valid TIN for this IC/SSM
								</span>
								<span class="input-group-text tin-invalid" style="display:none; color: red; margin-left: 1rem;">
									<i class="fa fa-times-circle"></i> Invalid TIN for this IC/SSM
								</span>
								<span class="input-group-text fail-connection" style="display:none; color: #856404;  margin-left: 1rem;">
									<i class="fa fa-exclamation-triangle"></i> 
									Unable to login to LHDN Einvoice API. You may still save this as a record, but please check your einvoice settings.
								</span>
							</div>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Date<span class="red">*</span></span>
							<?php $inputname = 'so_head[date]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['date']); ?>"
								type		="text" 
								class		="form-control"  
								autocomplete = "off" 
								 />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Remark</span>
							<textarea class="form-control" name="so_head[notes]" style='width:100%;' rows=4><?php echo $input['so_head']['notes']; ?></textarea>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Status</span>
							<?php $sel_status = set_value('so_head[status]', $input['so_head']['status']); ?>
							<select class="col-lg-12" id="so_head[status]" name="so_head[status]" style="width: 100%;">
								<option value="1" <?php if ($sel_status == '1') { echo 'selected="selected"'; } ?>> Pending </option>
								<option value="2" <?php if ($sel_status == '2') { echo 'selected="selected"'; } ?>> Approved/Profile </option>
								<option value="3" <?php if ($sel_status == '3') { echo 'selected="selected"'; } ?>> Cancelled </option>
							</select>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Customer Type</span>
							<?php $sel_reg = set_value('so_head[reg_type]', $input['so_head']['reg_type']); ?>
							<select class="col-lg-12" id="so_head[reg_type]" name="so_head[reg_type]" style="width: 100%;">
								<option value="r" <?php if ($sel_reg == 'r') { echo 'selected="selected"'; } ?>> Residential </option>
								<option value="b" <?php if ($sel_reg == 'b') { echo 'selected="selected"'; } ?>> Commercial - Broadband</option>
								<option value="o" <?php if ($sel_reg == 'o') { echo 'selected="selected"'; } ?>> Commercial - Others </option>
							</select>
						</div>
					</div>

				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12 company_related">
				<fieldset class='category-border'>		
					<legend class="category-border">Company Related
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Company Name</span>
							<?php $inputname = 'so_head[comp_name]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['comp_name']); ?>"
								type		="text" 
								class		="form-control"  
								 />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >SST#</span>
							<?php $inputname = 'so_head[gst_num]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['gst_num']); ?>"
								type		="text" 
								class		="form-control"  
								 />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Payment Term</span>
							<?php $inputname = 'so_head[payment_term]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['payment_term']); ?>"
								type		="text" 
								class		="form-control"  
								 />
						</div>
					</div>

				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>		
					<legend class="category-border">Radius
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Username</span>
							<?php $inputname = 'so_head[preferred_login]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['preferred_login']); ?>"
								type		="text" 
								class		="form-control"  
								 />

							<span class="input-group-btn">
								<button type="button" class="btn btn-purple btn-minier btn-verify">
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
					</div>
					
					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group"  >Password</span>
							<?php $inputname = 'so_head[preferred_password]'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input['so_head']['preferred_password']); ?>"
								type		="text" 
								class		="form-control"
								 />
						</div>
					</div>

					<div>
						<span class="red">*Leave blank for auto generate.</span>
					</div>
				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Building / Site & Agent
					</legend>

					<div class="col-lg-12">
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Building / Site</span>
								<select class="col-lg-12" id="so_head[building_no]" name="so_head[building_no]" onchange="autoFillAddress()" style="width: 100%;">
									<option value="0"> Not Applicable </option>
									<?php
									$sel_building = set_value('so_head[building_no]', $input['so_head']['building_no']);
									foreach ($buildings as $building) {
										echo "<option value='" . $building['building_no'] . "' " . ($sel_building == $building['building_no'] ? 'selected="selected"' : '') . ">" . $building['name'] . "</option>";
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Agent</span>
								<select class="col-lg-12" id="so_head[agent_id]" name="so_head[agent_id]" style="width: 100%;">
									<option value="0" <?php echo set_select('so_head[agent_id]', '', ($input['so_head']['agent_id'] == '0' ? true : false) ); ?> >-</option>
									<?php
										foreach ($sel_dealer_list as $val) {
											echo "<option value='" . $val['dealer_no'] . "' " . set_select('so_head[agent_id]', $val['dealer_no'], ($val['dealer_no']==$input['so_head']['agent_id'] ? true : false) ) . ">" . $val['name']."</option>";
										}
									?>
								</select>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			
			<div class="col-lg-6 col-xs-12">
				<fieldset class="category-border">
					<legend class="category-border">
						Preferred Installation Time
					</legend>

					<div class="col-lg-12">

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Preferred Date</span>
								<?php $inputname = 'so_head[preferred_install_date]'; ?>
								<input
									id="<?php echo $inputname; ?>"
									name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $preferred_install_date); ?>"
									type="text"
									class="form-control"
									placeholder=""/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">
									Time Slot
								</span>

								<select
									class="col-lg-12 form-control"
									id="preferred_install_time"
									name="so_head[preferred_install_time]"
									style="font-size: 12px;"
									>

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

					</div>
				</fieldset>
			</div>

			<div class="col-lg-12" style="padding: 0px;">
				<!--billing address -->
				<div class="col-lg-6 col-xs-12">

					<fieldset class='category-border'>		
						<legend class="category-border">Billing Address
						</legend>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Attn</span>
								<?php $inputname = 'so_head[bill_attn]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_attn']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Unit No.</span>
								<?php $inputname = 'so_head[bill_unit_no]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_unit_no']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" 
									/>
								<!--<span class="red">*Applicable to Building Address only</span>-->
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Address Line 1</span>
								<?php $inputname = 'so_head[bill_addr_1]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_addr_1']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Address Line 2</span>
								<?php $inputname = 'so_head[bill_addr_2]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_addr_2']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Address Line 3</span>
								<?php $inputname = 'so_head[bill_addr_3]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_addr_3']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Postcode</span>
								<?php $inputname = 'so_head[bill_postcode]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_postcode']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="" 
									maxlength = "7" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >City</span>
								<?php $inputname = 'so_head[bill_city]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_city']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	=""  
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">						
								<span class="input-group-addon input_group"  >State</span>
								<select class="col-lg-12" id="so_head[bill_state]" name="so_head[bill_state]" style="width: 100%;">
									<option value=""> -- SELECT -- </option>
									<?php
										$sel_bill_state = set_value('so_head[bill_state]', $input['so_head']['bill_state']);
										foreach ($sel_state_list as $val) {
											echo "<option value='" . $val['state_code'] . "' " . ($sel_bill_state==$val['state_code']?'selected="selected"':'') . ">" . $val['name']."</option>";
										}
									?>
								</select>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Tel.</span>
								<?php $inputname = 'so_head[bill_tel]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_tel']); ?>"
									type		="text" 
									class		="form-control"  
									placeholder	="eg. 60188888888" 
									onkeypress	="return /[0-9]/i.test(event.key)" 
									maxlength	="15" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Email<span class="red">*</span></span>
								<?php $inputname = 'so_head[bill_email]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['bill_email']); ?>"
									type		="text" 
									class		="form-control"  
									/>
							</div>
						</div>

						<div class="col-lg-12 email-exist orange mt-2" style="display:none;">
							This email is already tied to another profile.
						</div>

					</fieldset>

				</div>

				<!--installation/delivery address -->
				<div class="col-lg-6 col-xs-12">

					<fieldset class='category-border'>		
						<legend class="category-border">Delivery/Installation Address
						</legend>

						<div class="col-lg-12">
							<div class="input-group">
								*Same with Billing
								<input type="checkbox" id="same_with_billing" name="same_with_billing" value="1" />
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Company Name</span>
								<?php $inputname = 'so_head[del_company_name]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_company_name']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Attn</span>
								<?php $inputname = 'so_head[del_attn]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_attn']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Unit No.</span>
								<?php $inputname = 'so_head[del_unit_no]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_unit_no']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									/>
								<!--<span class="red">*Applicable to Building Address only</span>-->
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Address Line 1</span>
								<?php $inputname = 'so_head[del_addr_1]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_addr_1']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Address Line 2</span>
								<?php $inputname = 'so_head[del_addr_2]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_addr_2']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Address Line 3</span>
								<?php $inputname = 'so_head[del_addr_3]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_addr_3']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Postcode</span>
								<?php $inputname = 'so_head[del_postcode]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_postcode']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="" 
									maxlength = "7" 
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >City</span>
								<?php $inputname = 'so_head[del_city]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_city']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	=""  
									/>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">						
								<span class="input-group-addon input_group"  >State</span>
								<select class="col-lg-12 del_addr" id="so_head[del_state]" name="so_head[del_state]" style="width: 100%;">
									<option value=""> -- SELECT -- </option>
									<?php
										$sel_del_state = set_value('so_head[del_state]', $input['so_head']['del_state']);
										foreach ($sel_state_list as $val) {
											echo "<option value='" . $val['state_code'] . "' " . ($sel_del_state==$val['state_code']?'selected="selected"':'') . ">" . $val['name']."</option>";
										}
									?>
								</select>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Tel.</span>
								<?php $inputname = 'so_head[del_tel]'; ?>						
								<input 
									id			="<?php echo $inputname; ?>" 
									name		="<?php echo $inputname; ?>" 
									value		="<?php echo set_value($inputname, $input['so_head']['del_tel']); ?>"
									type		="text" 
									class		="form-control del_addr"  
									placeholder	="eg. 60188888888" 
									onkeypress	="return /[0-9]/i.test(event.key)" 
									maxlength	="15" 
									/>
							</div>
						</div>

					</fieldset>

				</div>
			</div>
		</fieldset>

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				PRODUCT
			</div>

			<input type="hidden" id="so_details[so_detail_id]" name="so_details[so_detail_id]" value="0">
			<input type="hidden" id="so_details[prod_id]" name="so_details[prod_id]" value="<?php echo set_value('so_details[prod_id]', $input['so_details']['prod_id']); ?>">
			<input type="hidden" id="so_details[dia_vars]" name="so_details[dia_vars]" value="<?php echo set_value('so_details[dia_vars]', $input['so_details']['dia_vars']); ?>">

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group"  >Product</span>
					<?php $inputname = 'so_details[item_name]'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input['so_details']['item_name']); ?>"
						type		="text" 
						class		="form-control"  
						 />
				</div>
			</div>

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group"  >Deposit</span>
					<?php $inputname = 'so_details[deposit]'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input['so_details']['deposit']); ?>"
						type		="text" 
						class		="form-control" readonly 
						 />
				</div>
			</div>

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group"  >Monthly Charges</span>
					<?php $inputname = 'so_details[monthly_charge]'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input['so_details']['monthly_charge']); ?>"
						type		="text" 
						class		="form-control" readonly 
						 />
				</div>
			</div>

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group"  >Installation Fee</span>
					<?php $inputname = 'so_details[installation]'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input['so_details']['installation']); ?>"
						type		="text" 
						class		="form-control" readonly 
						 />
				</div>
			</div>

			<div class="col-lg-6"></div>

			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon input_group"  >One Time Charge</span>
					<?php $inputname = 'so_details[one_time_charge]'; ?>						
					<input 
						id			="<?php echo $inputname; ?>" 
						name		="<?php echo $inputname; ?>" 
						value		="<?php echo set_value($inputname, $input['so_details']['one_time_charge']); ?>"
						type		="text" 
						class		="form-control" readonly 
						 />
				</div>
			</div>

          	<!--<table id="so_detail_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">

		        <thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
		          <th class="col-1">No.</th>
		          <th class="col-3">
		            Product
		          </th>
		          <th class="col-2">Quantity</th>
		          <th class="col-2">Price</th>
		          <th class="col-1">Subtotal</th>
		          <th class="col-1">Tax%</th>
		          <th class="col-1">Tax</th>
		          <th class="col-1">Total</th>
		          <th style="width:50px"><i class='fa fa-ellipsis-v'></i></th>
		        </thead>

		        <tbody id="so_detail_body">
		        </tbody>

                <thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
                  <th colspan="7">Grand Total:</th>
                  <th><b><span id="grand_total_so">0.00</span></b></th>
                  <th></th>
                </thead>

          	</table>

			<div class="col-md-12">
				<div class="col-12 mt-2 text-sm-right">
					<button type="button" class="btn btn-green btn-small" onclick="addSodetail();">Add</button>
				</div>
			</div>-->

		</fieldset>

		<fieldset class='category-border-main' id="dia_area">
			<div class="category-border-main bg-success text-center">
				Additional Properties
			</div>

			<div id="dia_rows_area">
				
			</div>

		</fieldset>

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				UPLOADED FILES
			</div>

			<div class="form-group col-lg-12 col-xs-12" style="z-index:100;">
		
				<div class="col-lg-12 col-xs-12">
					<fieldset class="category-border">
						<legend class="category-border">IC
						</legend>
						<?php 
						if( !empty( $ic_attachment ) ){
							foreach( $ic_attachment AS $row => $res ){
						?>
							<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" data-category="ic" onclick="view_attach_doc(<?php echo $row; ?>,'ic');">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']) ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" name="existing_attach_ic[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>

										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>
						<?php
							}
						}
						?>
					</fieldset>
				</div>

				<div class="col-lg-12 col-xs-12">
					<fieldset class="category-border">
						<legend class="category-border">Company SSM
						</legend>
						<?php 
						if( !empty( $ssm_attachment ) ){
							foreach( $ssm_attachment AS $row => $res ){
						?>
							<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" data-category="ssm" onclick="view_attach_doc(<?php echo $row; ?>,'ssm');">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" name="existing_attach_ssm[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>
										
										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>
						<?php
							}
						}
						?>
					</fieldset>
				</div>

				<div class="col-lg-12 col-xs-12">
					<fieldset class="category-border">
						<legend class="category-border">Letter of authority
						</legend>
						<?php 
						if( !empty( $auth_attachment ) ){
							foreach( $auth_attachment AS $row => $res ){
						?>
							<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" data-category="auth" onclick="view_attach_doc(<?php echo $row; ?>,'auth');">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" name="existing_attach_auth[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>
									
										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>
						<?php
							}
						}
						?>
					</fieldset>
				</div>

				<div class="col-lg-12 col-xs-12">
					<fieldset class="category-border">
						<legend class="category-border">Others
						</legend>
						<?php 
						if( !empty( $others_attachment ) ){
							foreach( $others_attachment AS $row => $res ){
						?>
							<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" data-category="others" onclick="view_attach_doc(<?php echo $row; ?>,'others');">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" name="existing_attach_other[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>

										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>
						<?php
							}
						}
						?>
					</fieldset>
				</div>
			</div>

		</fieldset>

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				ATTACHMENTS
			</div>

			<div class="form-group col-lg-12 col-xs-12" style="z-index:100;">

				<div class="col-xs-12 col-xs-12">Attach Supporting Documents</div>
				
				<div class="col-lg-3 col-xs-12">
					<input multiple="multiple" type="file" id="attach_file_input" data-preview-file-type="text" data-module="profile" />
				</div>
		
				<div class="col-lg-9 col-xs-12" id="file-container">

				<?php 
				if( !empty( $attachment ) ){
					foreach( $attachment AS $row => $res ){
				?>
					<div class="col-lg-3 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
						<div class="desktop-icon">
							<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" onclick="view_attach_doc(<?php echo $row; ?>);">
								<div class="icon-image">
									<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
									<?php if ($res['file_type'] == 'image'): ?>
										<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
									<?php elseif ($res['file_type'] == 'video'): ?>
										<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
										<i class="fa fa-film"></i>
									<?php elseif ($res['file_type'] == 'doc'): ?>
										<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
										<i class="fa fa-file"></i>
									<?php elseif ($res['file_type'] == 'pdf'): ?>
										<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
									<?php endif ?>
								</div>
							</div>

							<div class="file-remark" title="<?php echo $attachment_text_title; ?>"><a style="cursor:pointer;" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"  onclick="change_remark(this);"><?php echo $attachment_text_title; ?></a></div>

							<div class="text-right" style="min-height:20px;">
								<!--only for existing attachments-->
								<?php if ($res['is_temp'] == '0') { ?>
								<input type="hidden" id="existing_attach" name="existing_attach[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
								<?php } ?>
			            		<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>

								&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
									<i class="fa fa-trash" style="cursor:pointer;"></i>
								</a>
							</div>
						</div>
					</div>

					<input type="file" name="attach_attachment[<?php echo $row; ?>]" class="hide" id="attach_attachment_<?php echo $row; ?>" value="<?php echo $res['local_path']; ?>">

					<input type="hidden" name="attach_attachment_remark[<?php echo $row; ?>]" value="<?php echo $res['remark']; ?>">
				<?php
					}
				}
				?>

				</div>

				<?php include_once( APPPATH . 'views/templates/modal_html_attachment_remark.php'); ?>

			</div>

		</fieldset>

		<?php
		$profile_id  = $input['so_head']['profile_id'];
		$customer_no = $input['so_head']['customer_no'];

		if ($profile_id || $customer_no):
			
			$customer_status = $input['so_head']['customer_current_status'] ?? 'P';

		?>

		<fieldset class="category-border-main">

			<div class="category-border-main bg-success text-center">
				Customer Profile And Account
			</div>

			<div class="row" style="padding:15px;">

				<?php if ($profile_id) { ?>
					<div class="col-md-6">
						<div class="well well-sm" style="margin-bottom:10px;">

							<div class="clearfix">
								<strong>
									<i class="fa fa-user text-primary"></i>
									Profile
								</strong>

								<a href="<?=site_url('profile/add_profile/'.$profile_id);?>"
								class="pull-right">
									View
									<i class="fa fa-external-link"></i>
								</a>
							</div>

							<hr style="margin:8px 0;">

							<div style="font-size:16px;font-weight:bold;">
								<?= $input['so_head']['profile_name']; ?>
							</div>

							<small class="text-muted">
								Profile ID :
								<?=$profile_id;?>
							</small>

						</div>
					</div>
				<?php } ?>

				<?php if ($customer_no) { ?>
					<div class="col-md-6">
						<div class="well well-sm" style="margin-bottom:10px;">

							<div class="clearfix">
								<strong>
									<i class="fa fa-building text-success"></i>
									Account
								</strong>

								<a href="<?=site_url('customer/edit_customer/'.$customer_no);?>"
								class="pull-right">
									View
									<i class="fa fa-external-link"></i>
								</a>
							</div>

							<hr style="margin:8px 0;">

							<div class="clearfix">
								<div class="pull-left" style="font-size:16px;font-weight:bold;">
									<?= $input['so_head']['customer_name']; ?>
								</div>

								<div class="pull-right">
									<span class="label label-info" style="">
										<?= $account_status[$customer_status] ?? 'Sign Up'; ?>
									</span>
								</div>
							</div>

							<small class="text-muted">
								Customer No :
								<?=$customer_no;?>
							</small>

						</div>
					</div>
				<?php } ?>

			</div>
		</fieldset>

		<?php endif; ?>

		<!-- buttons-->
		<div class="col-md-12">
			<div class="button-group d-flex flex-column flex-md-row gap-2 justify-content-start align-items-stretch align-items-md-center">
				<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
					Cancel
				</button>

				<?php if (!empty($input['so_head']['so_id'])) { ?>
					<span title="<?= empty($input['so_head']['profile_id']) ? 'Please verify your preferred username.' : 'This sales order is linked to a profile.';?>">
						<button id="btProfile" name="btProfile" type="submit" value="btProfile" class="btn btn-info" <?php echo tooltip_helper('Click to Create Profile from Sales Order'); ?> <?php echo check_acl_btn('salesorder', 'M'); ?> <?php echo !empty($input['so_head']['customer_no']) && !in_array($input['so_head']['customer_current_status'], ['P', 'C']) ? "disabled" : ""; ?> >
							<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i>
							Create Profile
						</button>
					</span>

					<button id="btPrint" name="btPrint" type="button" value="btPrint" class="btn btn-info" onclick="print_salesorder()" <?php echo tooltip_helper('Print Sales Order/Quotation'); ?>>
						<i class="menu-icon fa fa-print white" data-toggle="tooltip" title=""></i>
						Print
					</button>

				<?php } ?>
				<span title="Please verify your preferred username.">
					<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('salesorder','M');?> >
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>		
						Save					
					</button>
				</span>

				<span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>
			</div>	
			<?php if (!empty($input['so_head']['customer_no']) && !in_array($input['so_head']['customer_current_status'], ['P', 'C'])): ?>
				<br>
				<span class="red">Profile recreation is not available for ACTIVATED accounts.</span>
			<?php endif; ?>
		</div>

	</form>
</div>

<?php if (!empty($input['so_head']['so_id'])) { ?>
	<?php include_once( APPPATH . 'views/templates/audit_info.php'); ?>
<?php } ?>

<!-- po detail row template -->
<table class="hidden">
  <tbody id="so_detail_row">
    <tr class="so_detail-row" data-id='' data-line_num='' data-sodetail=''>
      <input type="hidden" name="so_detail.po_detail_id" value="0">
      <input type="hidden" name="so_detail.prod_id" value="0">
      <input type="hidden" name="so_detail.dia_vars" value="">
      <td class="align-middle" data-fieldname="so_detail.line_num"></td>
      <td data-fieldname="so_detail.item_name"><input type="search" class="form-control d-inline-block" name="so_detail.item_name" value=""></td>
      <td data-fieldname="so_detail.quantity"><input type="text" class="form-control d-inline-block" name="so_detail.quantity" value="0"></td>
      <td data-fieldname="so_detail.price"><input type="text" class="form-control d-inline-block" name="so_detail.price" value="0.00"></td>
      <td class="align-middle" data-fieldname="so_detail.subtotal">0.00</td>
      <td data-fieldname="so_detail.tax_rate"><input type="text" class="form-control d-inline-block" name="so_detail.tax_rate" value="0" placeholder="0%"></td>
      <td class="align-middle" data-fieldname="so_detail.tax">0.00</td>
      <td class="align-middle" data-fieldname="so_detail.total">0.00</td>
      <td class="align-middle"><i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delSodetail(this)"></i></td>
    </tr>
  </tbody>
</table>
<!-- po detail row template end -->

<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>

<script>
	var accessView 	 = '<?php echo check_acl('salesorder', 'V', false ) == true ? 1 : 0 ; ?>';
	var accessModify = '<?php echo check_acl('salesorder', 'M', false ) == true ? 1 : 0 ; ?>';
	if( accessView == 1 && accessModify == 0 ){
		$(':input').attr('READONLY','READONLY');
		$('option:not(:selected)').attr('disabled', true);
	}		

	var all_product_list = <?php echo json_encode($product_arr) ?>;
	var profile_id = "<?= $input['so_head']['profile_id'] ?? ''; ?>";
	var customer_no = "<?= $input['so_head']['customer_no'] ?? ''; ?>";
</script>
<script src="<?php echo base_url("js/itelco/salesorder.js?".cssjs_ver()); ?>" ></script>
<!--ace file-->
<script src="<?php echo base_url("js/ace/elements.fileinput.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>
<?php include 'panel_footer.php';?>