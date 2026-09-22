<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('dealer');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="dealer_detail" name="dealer_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Agent
					</div>

					<div class="col-lg-12">
						<?php if ($input['is_commission_set'] == 0 && $input['dealer_no'] != '' && $input['upline'] != '0'): ?>
							<div class="alert alert-warning">
								Please set the monthly/fixed deduction or contact the upline to configure a custom commission rate.
							</div>
						<?php endif; ?>
					</div>

					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">
								General Information
							</legend>
							<div>
								<div class="row">
									<div class="col-lg-6">
										<div class="input-group">
											<span class="input-group-addon input_group">Status</span>
											<select class="col-lg-12" id="status" name="status">
												<?php
													foreach ($sel_status_list as $val) {
														echo "<option value='" . $val['status_code'] . "' " . set_select('status', $val['status_code'], ($val['status_code']==$input['status'] ? true : false) ) . ">" . $val['name']."</option>";
													}
												?>
											</select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-6">
										<div class="input-group">
											<span class="input-group-addon input_group">Agent Type</span>
											<select class="col-lg-12" id="dealer_type" name="dealer_type">
												<?php
													foreach ($sel_dealer_type as $dealer_type => $val) {
														echo "<option value='" . $dealer_type . "' " . set_select('dealer_type', $dealer_type, ($dealer_type==$input['dealer_type'] ? true : false) ) . ">" . $val."</option>";
													}
												?>
											</select>
										</div>
									</div>
								</div>
								<br/>
								<div class="row">
									<div class="col-lg-6">
										<div>
											<div class="input-group">
												<span class="input-group-addon input_group">Agent No</span>
												<input type="text" class="form-control" id="dealer_no" name="dealer_no" value="<?php echo set_value('dealer_no', $input['dealer_no']); ?>" placeholder="Agent No" readonly>
											</div>
											<div class="input-group">
												<span class="input-group-addon input_group">Name<span class="red">*</span></span>
												<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Agent Name">
											</div>
											<div class="input-group">
												<span class="input-group-addon input_group">Preferred Login<span class="red">*</span></span>
												<?php $inputname = 'login'; ?>
												<input 
													id			="<?php echo $inputname; ?>" 
													name		="<?php echo $inputname; ?>" 
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text" 
													class		="form-control"  
													placeholder	="" >
											</div>
											<div class="input-group">
												<span class="input-group-addon input_group">Default Password</span>
												<?php $inputname = 'password'; ?>
												<input 
													id			="<?php echo $inputname; ?>" 
													name		="<?php echo $inputname; ?>" 
													value		=""
													type		="text" 
													class		="form-control"  
													placeholder	="" >
											</div>
											<span class="red">*only for first time creating agent account, key in to change password</span>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-group">
											<span class="input-group-addon input_group">Company No<span class="red">*</span></span>
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
											<span class="input-group-addon input_group">PIC<span class="red">*</span></span>
											<?php $inputname = 'pic_name'; ?>
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="" >
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group">Upline</span>
											<?php if ($disabled_upline_option): ?>
												<input
													type="text"
													class="form-control"
													value="<?= $upline_list[$input['upline']] ?? '-- No Upline --' ?>"
													readonly
													onclick="uplineWarningMessage();"
												>
												<input type="hidden" name="upline" id="upline" value="<?= $input['upline'] ?>">
											<?php else: ?>
												<select class="col-lg-12" id="upline" name="upline">
													<option value="0">-- No Upline --</option>
													<?php foreach ($upline_list as $dealer_no => $name): ?>
														<option
															value="<?= $dealer_no ?>"
															<?= set_select('upline', $dealer_no, $dealer_no == $input['upline']) ?>
														>
															<?= $name ?>
														</option>
													<?php endforeach; ?>
												</select>
											<?php endif; ?>
										</div>
										<div class="input-group" id="building-field">
											<span class="input-group-addon input_group">Building</span>
											<select multiple="multiple" id="building" name="building[]" class="select2" style="width:100%;">
												<?php foreach($sel_building_list as $building): ?>
													<option value="<?= $building['building_no'] ?>" <?= in_array($building['building_no'], $selected_building) ? 'selected' : '' ?>><?= $building['name'] ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
								</div>
								<br/>
								<div class="row">
									<div class="col-lg-6">
										<div class="input-group">
											<span class="input-group-addon input_group">Address Line 1<span class="red">*</span></span>	
											<?php $inputname = 'addr1'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Address 1" >	
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group">Address Line 2</span>					
											<?php $inputname = 'addr2'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Address 2" >	
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group">Address Line 3</span>					
											<?php $inputname = 'addr3'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Address 3" >	
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group">City<span class="red">*</span></span>
											<?php $inputname = 'city'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="City" >	
											<span class="input-group-addon input_group">Postcode<span class="red">*</span></span>
											<?php $inputname = 'postcode'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Postcode" >
										</div>
										<div class="input-group ">
											<span class="input-group-addon input_group">State<span class="red">*</span></span>
											<select class="col-lg-12" id="inst_state" name="state">
												<?php
													foreach ($sel_state_list as $val) {
														echo "<option value='" . $val['state_code'] . "' " . set_select('state', $val['state_code'], ($val['state_code']==$input['state'] ? true : false) ) . ">" . $val['name']."</option>";
													}
												?>
											</select>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-group">
											<span class="input-group-addon input_group" >Tel</span>
											<?php $inputname = 'tel_num'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Tel" >
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group" >Fax</span>
											<?php $inputname = 'fax_num'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Fax" >
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group" >Mobile<span class="red">*</span></span>
											<?php $inputname = 'mobile_num'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Mobile Num" >
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group" >Email</span>
											<?php $inputname = 'email'; ?>						
											<input 
												id			="<?php echo $inputname; ?>" 
												name		="<?php echo $inputname; ?>" 
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text" 
												class		="form-control"  
												placeholder	="Email" >
										</div>
										<div class="input-group" id="deduct-input-group" style="display: none;">
											<span class="input-group-addon input_group">Monthly Deduct<span class="red">*</span></span>
												<?php $inputname = 'monthly_pct_deduct'; ?>
												<input 
													id			="<?php echo $inputname; ?>" 
													name		="<?php echo $inputname; ?>" 
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text" 
													class		="form-control"  
													placeholder	="0%" autocomplete="off">					
											<span class="input-group-addon input_group"  >Fixed Deduct<span class="red">*</span></span>
												<?php $inputname = 'one_time_amt_deduct'; ?>
												<input 
													id			="<?php echo $inputname; ?>" 
													name		="<?php echo $inputname; ?>" 
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text" 
													class		="form-control"  
													placeholder	="0.00" autocomplete="off">					
										</div>
									</div>
								</div>
								<br/>
								<div class="row">
									<div class="col-lg-6">
										<div class="input-group">
											<span class="input-group-addon input_group">Sign Up<span class="red">*</span></span>
												<?php $inputname = 'signup_date'; ?>
												<input 
													id			="<?php echo $inputname; ?>" 
													name		="<?php echo $inputname; ?>" 
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text" 
													class		="form-control"  
													placeholder	="1957-08-31" autocomplete="off">					
											<span class="input-group-addon input_group"  >Terminated</span>
												<?php $inputname = 'terminated_date'; ?>
												<input 
													id			="<?php echo $inputname; ?>" 
													name		="<?php echo $inputname; ?>" 
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text" 
													class		="form-control"  
													placeholder	="1957-08-31" autocomplete="off">					
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-group">
											<button  id="btComm" name="btComm" type="button" value="comm" class="btn btn-info" onclick="goCommissionPage(<?php echo $input['dealer_no']; ?>)" <?php echo tooltip_helper('Agent Commission'); ?>>
												<i class="menu-icon fa fa-calculator white" data-toggle="tooltip" title=""></i>Commission
											</button>
										</div>										
									</div>
								</div>
							</div>
						</fieldset>						
					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="group-button">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('dealer');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('dealer','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>

						<!-- temporary remove, since now agent is tied to having an account, cannot simply delete-->
						<!--<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('dealer','D');?>>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
						</button>-->			
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
	<script src="<?php echo base_url("js/itelco/dealer.js?".cssjs_ver()); ?>" ></script>
