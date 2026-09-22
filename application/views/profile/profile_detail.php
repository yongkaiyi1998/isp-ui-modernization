<?php include 'panel_header.php';?>
<div class="panel-heading noprint panel_fontsize">
		<span class="panel_space float_right">
			<a  href="<?php echo base_url('profile');?>">
				<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
			</a>
		</span>
		<?php echo $page_title; ?>
</div>

<div class="panel-body">
	<?php echo flash_data_helper($msg); ?>
	<form id="profile_detail" name="profile_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">

		<?php //==== hidden fields ===== 
		?>
		<?php $inputname = 'url_after_save'; ?>
		<input
			id="<?php echo $inputname; ?>"
			name="<?php echo $inputname; ?>"
			value="<?php echo set_value($inputname, $hidden[$inputname]); ?>"
			type="hidden">

		<input id="form_action" name="form_action" type="hidden"
			value="<?php echo !empty($input['acc_id']) ? 'UPDATE' : 'NEW'; ?>" />

		<input id="acc_id" name="acc_id" type="hidden"
			value="<?php echo set_value('acc_id', $input['acc_id']); ?>" />

		<input id="so_id" name="so_id" type="hidden"
			value="<?php echo set_value('so_id', $input['so_id']); ?>" />

		<input id="task" name="task" type="hidden"
			value="" /> 

		<input id="temp_id" name="temp_id" type="hidden"
			value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				CUSTOMER PROFILE
			</div>
			<div class="col-lg-12">
				<div class="alert alert-info mt-2 mb-3 <?php echo ($create_login_alert == '1' && $profile_linked == 0) ? '' : 'hide'; ?>" id="main-login-creation-alert">
					<i class="fa fa-info-circle"></i>
					<strong>Important:</strong> 
					The fields 
					<span class="text-primary <?php echo $input['acc_type'] == 'r' ? '' : 'hide'; ?>" id="residential-login-creation-info">IC No./Passport</span> 
					<span class="text-primary <?php echo ($input['acc_type'] == 'b' || $input['acc_type'] == 'o') ? '' : 'hide'; ?>" id="commercial-login-creation-info">SSM#</span> 
					and <span class="text-primary">Email</span> are used for customer login creation. 
				</div>
			</div>
			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Customer/PIC Information
					</legend>
					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Name<span class="red">*</span></span>
							<?php $inputname = 'acc_name'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="Name as in IC" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group <?= ($input['acc_type'] == 'r' && $profile_linked == 0) ? 'input-highlight' : '' ?>" id="ic-input-grp">
							<span class="input-group-addon input_group">IC No./Passport<span class="red" id="icno_required" style="display: none;">*</span></span>
							<?php $inputname = 'icno'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="IC No." 
								style="text-transform: uppercase" 
								onkeypress="return /[a-zA-Z0-9]/i.test(event.key)" 
								/>
						</div>
					</div>

					<div class="col-lg-12 icno-exist red mt-2" style="display:none;">
						IC No. already exists. Cannot proceed with saving.
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">TIN</span>
							<?php $inputname = 'tin'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />

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
							<span class="input-group-addon input_group">Mobile No.</span>
							<?php $inputname = 'acc_mobileno'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="Mobile No. (Start With 60)" 
								onkeypress="return /[0-9]/i.test(event.key)" 
								/>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group <?= ($create_login_alert == '1' && $profile_linked == 0) ? 'input-highlight' : '' ?>" id="email-input-grp">
							<span class="input-group-addon input_group">Email<span class="red">*</span></span>
							<?php $inputname = 'acc_email'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="Email" 
								/>
								<div class="col-lg-12 email-exist orange mt-2" style="display:none;">
									# Email address already exists. Cannot proceed with saving.
								</div>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Type<span class="red">*</span></span>
							<select class="col-lg-12" id="acc_type" name="acc_type" <?php echo !empty($input['acc_id']) ? 'disabled="disabled"' : ''; ?> style="width: 100%;">
								<option value=""> -- SELECT -- </option>
								<?php
								$sel_acc_type = strtolower(set_value('acc_type', $input['acc_type']));
								foreach ($sel_profile_list as $val) {
									if ($val['category_code'] == 'A') {
										continue;
									}
									echo "<option value='" . $val['category_code'] . "' " . ($sel_acc_type == strtolower($val['category_code']) ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
								}
								?>
							</select>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Agent</span>
							<select class="col-lg-12" id="agent_id" name="agent_id" <?php echo !empty($input['acc_id']) ? 'disabled="disabled"' : ''; ?> style="width: 100%;">
								<option value="0"> -- N/A -- </option>
								<?php
								$agent_id = set_value('agent_id', $input['agent_id']);
								foreach ($sel_dealer_list as $val) {
									echo "<option value='" . $val['dealer_no'] . "' " . ($agent_id == $val['dealer_no'] ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
								}
								?>
							</select>
						</div>
					</div>

				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12" id="company_related">
				<fieldset class='category-border'>
					<legend class="category-border">Company Information
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Company Name<span class="red">*</span></span>
							<?php $inputname = 'comp_name'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group <?= (($input['acc_type'] == 'b' || $input['acc_type'] == 'o') && $profile_linked == 0)? 'input-highlight' : '' ?>" id="ssm-input-grp">
							<span class="input-group-addon input_group">SSM#<span class="red">*</span></span>
							<?php $inputname = 'ssm'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />
						</div>
					</div>
					<div class="col-lg-12 ssm-exist orange mt-2" style="display:none;">
						#SSM already exists. Cannot proceed with saving.
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">SST#</span>
							<?php $inputname = 'sst'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">TTX#</span>
							<?php $inputname = 'ttx'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />
						</div>
					</div>

				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12">

				<fieldset class='category-border'>
					<legend class="category-border">Billing Address
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Unit No</span>
							<?php $inputname = 'bill_unit_no'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Address Line 1<span class="red">*</span></span>
							<?php $inputname = 'bill_addr_1'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Address Line 2</span>
							<?php $inputname = 'bill_addr_2'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Address Line 3</span>
							<?php $inputname = 'bill_addr_3'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Postcode<span class="red">*</span></span>
							<?php $inputname = 'bill_postcode'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder=""
								maxlength="7" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">City<span class="red">*</span></span>
							<?php $inputname = 'bill_city'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control"
								placeholder="" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">State<span class="red">*</span></span>
							<select class="col-lg-12" id="bill_state" name="bill_state" style="width: 100%;">
								<option value=""> -- SELECT -- </option>
								<?php
								$sel_bill_state = set_value('bill_state', $input['bill_state']);
								foreach ($sel_state_list as $val) {
									echo "<option value='" . $val['state_code'] . "' " . ($sel_bill_state == $val['state_code'] ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
								}
								?>
							</select>
						</div>
					</div>

				</fieldset>

			</div>

			<!-- ANOTHER SET OF PIC -->
			<?php 
				if( !empty($profile_pic)  ){
				foreach( $profile_pic AS $key => $pic ){
			?>

			<div class="col-lg-12 customer-pic <?php echo $key > 0 ? "hide" : "" ; ?>" >
				<fieldset class='category-border'>
					<legend class="category-border pic-legend">
					Personal & Contacts Information
					<?php 
						foreach( $profile_pic AS $key2 => $pic ){
							if( $key == $key2 )
								echo "[ <span class='customer-pic-span' style='text-decoration:underline;cursor:pointer;'>".($key2+1)."</span> ] ";
							else
								echo "[ <span class='customer-pic-span' style='cursor:pointer;'>".($key2+1)."</span> ] ";
						}
					?>
					<?php if (count($profile_pic) < $profile_pic_count) { ?>
					[ <span class='customer-pic-more' style='cursor:pointer;'>more</span> ]
					<?php } ?>
					</legend>
					<div class="col-lg-12" id='nric_err'></div>
					<?php if($key == 0): ?>
							<div class="col-lg-12">
								<div class="input-group">
									<input type="checkbox" id="same_with_above" style="margin-right: 1rem;"/> 
									<label for="same_with_above"><strong>SAME WITH ABOVE</strong></label>
								</div>
							</div>
						<?php endif; ?>
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group">PIC Name</span>
							<input type="hidden" id="pic_id[<?php echo $key; ?>]" name="pic_id[<?php echo $key; ?>]" value="<?php echo $profile_pic[$key]['pic_id']; ?>" />
							<input type="hidden" id="pic_acc_id[<?php echo $key; ?>]" name="pic_acc_id[<?php echo $key; ?>]" value="<?php echo $profile_pic[$key]['pic_acc_id']; ?>" />
							<?php $inputname = 'pic_name'; ?>
							<input 
								id			="pic_name[<?php echo $key; ?>]" 
								name		="pic_name[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_name[".$key."]", $profile_pic[$key]['pic_name'] ); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" >
						</div>
						<div class="input-group">
							<span 	class="input-group-addon input_group"  
									id="nric_span" 
									>NRIC/Passport</span>
							<?php $inputname = 'pic_nric_passport'; ?>						
							<input 
								id			="pic_nric_passport[<?php echo $key; ?>]" 
								name		="pic_nric_passport[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_nric_passport[".$key."]", $profile_pic[$key]['pic_nric_passport']); ?>"
								type		="text" 
								class		="form-control"
								onkeypress	="return /[a-zA-Z0-9]/i.test(event.key)" 
								  >
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group">Date of Birth</span>							
							<?php $inputname = 'pic_dob'; ?>
							<input 
								id			="pic_dob[<?php echo $key; ?>]" 
								name		="pic_dob[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_dob[".$key."]", $profile_pic[$key]['pic_dob']); ?>"
								type		="text" 
								class		="form-control pic_dob"  
								placeholder	="Date of Birth" autocomplete="off">
						</div>						
						<div class="input-group">
							<span class="input-group-addon input_group" >Race</span>
							<select class="form-control" id="pic_race[<?php echo $key; ?>]" name="pic_race[<?php echo $key; ?>]" style="font-size:12px;" >
								<option value="" <?php echo set_select('pic_race['.$key.']', '', ($profile_pic[$key]['pic_race'] == '' ? true : false) ); ?> >- SELECT -</option>
								<option value="m" <?php echo set_select('pic_race['.$key.']', 'm', ($profile_pic[$key]['pic_race'] == 'm' ? true : false) ); ?> >Malay</option>
								<option value="c" <?php echo set_select('pic_race['.$key.']', 'c', ($profile_pic[$key]['pic_race'] == 'c' ? true : false) ); ?> >Chinese</option>
								<option value="i" <?php echo set_select('pic_race['.$key.']', 'i', ($profile_pic[$key]['pic_race'] == 'i' ? true : false) ); ?> >Indian</option>
								<option value="i" <?php echo set_select('pic_race['.$key.']', 'o', ($profile_pic[$key]['pic_race'] == 'o' ? true : false) ); ?> >Others</option>
							</select>
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group" >Gender</span>
							<select class="form-control" id="pic_gender[<?php echo $key; ?>]" name="pic_gender[<?php echo $key; ?>]" style="font-size:12px;" >
								<option value="" <?php echo set_select('pic_gender['.$key.']', '', ($profile_pic[$key]['pic_gender'] == '' ? true : false) ); ?> >- SELECT -</option>
								<option value="m" <?php echo set_select('pic_gender['.$key.']', 'm', ($profile_pic[$key]['pic_gender'] == 'm' ? true : false) ); ?> >Male</option>
								<option value="f" <?php echo set_select('pic_gender['.$key.']', 'f', ($profile_pic[$key]['pic_gender'] == 'f' ? true : false) ); ?> >Female</option>
							</select>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group" >Designation</span>
							<?php $inputname = 'pic_designation'; ?>						
							<input 
								id			="pic_designation[<?php echo $key; ?>]" 
								name		="pic_designation[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_designation[".$key."]", $profile_pic[$key]['pic_designation']); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="Designation" >
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group" >Mobile</span>							
							<?php $inputname = 'pic_mobile'; ?>						
							<input 
								id			="pic_mobile[<?php echo $key; ?>]" 
								name		="pic_mobile[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_mobile[".$key."]", $profile_pic[$key]['pic_mobile']); ?>"
								type		="text" 
								class		="form-control"  
								placeholder="eg. 60188888888"
								onkeypress="return /[0-9]/i.test(event.key)"
								maxlength="15" >
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group" >Email 1</span>
							<?php $inputname = 'pic_email_1'; ?>						
							<input 
								id			="pic_email_1[<?php echo $key; ?>]" 
								name		="pic_email_1[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_email_1[".$key."]", $profile_pic[$key]['pic_email_1']); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="Email" >
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group" >Email 2</span>							
							<?php $inputname = 'pic_email_2'; ?>						
							<input 
								id			="pic_email_2[<?php echo $key; ?>]" 
								name		="pic_email_2[<?php echo $key; ?>]" 
								value		="<?php echo set_value("pic_email_2[".$key."]", $profile_pic[$key]['pic_email_2']); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="Alternative Email" >
						</div>
						<div class="input-group <?php echo $key == 0 ? 'hide' : '' ;  ?>" style="padding: 0.5rem;">
							<input type="checkbox" name="delete[<?php echo $key; ?>]" value="1" <?php echo set_checkbox("delete[".$key."]", "1", FALSE ); ?> style="margin-right: 1rem;" /> <span class="red">DELETE WHEN SAVE</span>
						</div>
					</div>
				</fieldset>
			</div>

			<?php 
					} //end foreach
				}
			?>

		</fieldset>

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				Uploaded Files
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
				Attachments
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

		<fieldset class="category-border-main">				
			<div class="category-border-main bg-success text-center">
				ADDITONAL DETAILS
			</div>
			<div class="col-lg-12">
				<fieldset class='category-border'>				
					<legend class="category-border">
						Family Details
					</legend>
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group" >Marital Status</span>
							<select class="col-lg-12" id="marital_status" name="marital_status" style="width: 100%;">
								<option value="" <?php echo set_select('marital_status', '', ($input['marital_status'] == '' ? true : false) ); ?> >-</option>
								<?php
									foreach ($sel_marital_status_list as $val) {
										echo "<option value='" . $val['marital_status_code'] . "' " . set_select('marital_status', $val['marital_status_code'], ($val['marital_status_code']==$input['marital_status'] ? true : false) ) . ">" . $val['name']."</option>";
									}
								?>
							</select>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group" >Household</span>
							
							<?php $inputname = 'household'; ?>						
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
							<span class="input-group-addon input_group">Nationality</span>
							<select class="col-lg-12" id="nationality_dd" name="nationality_dd" style="width: 100%;">
								<option value="Malaysian" <?php echo ($input['nationality'] == 'Malaysian') ? 'SELECTED' : '' ; ?> >Malaysian</option>
								<option value="Non Malaysian" <?php echo ($input['nationality'] != 'Malaysian') ? 'SELECTED' : '' ;; ?> >Non Malaysian</option>
							</select>
						</div>
					</div>
					
					<div class="col-lg-6 nationality_others">
						<div class="input-group">
							<?php $inputname = 'nationality'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="Fill in other nationality" >
						</div>
					</div>

				</fieldset>	
			</div>
			<div id="company_section" class="col-lg-12">
				<fieldset class='category-border'>				
					<legend class="category-border">
						Company Details
					</legend>
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group" >No of employees</span>
							
							<?php $inputname = 'no_of_employee'; ?>						
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
							<span class="input-group-addon input_group" >No of branches</span>							
							<?php $inputname = 'no_of_branches'; ?>						
							<input 
								id			="<?php echo $inputname; ?>" 
								name		="<?php echo $inputname; ?>" 
								value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type		="text" 
								class		="form-control"  
								placeholder	="" >
						</div>
					</div>	

					<span class="red">*Applicable for company account</span>
				</fieldset>	
			</div>				
		</fieldset>	

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				ACCOUNTS
			</div>
			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Accounts/Product Information
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">User Login<span class="red">*</span></span>
							<?php $inputname = 'acc_username'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Password</span>
							<?php $inputname = 'acc_password'; ?>
							<input
								id="<?php echo $inputname; ?>"
								name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>"
								type="text"
								class="form-control" />
							<span class="red">*Leave blank if not changing password</span>
						</div>
					</div>

				</fieldset>

			</div>

			<?php if (!empty($input['acc_id'])) { ?>
			<div class="col-lg-12">
				<fieldset class='category-border'>
					<legend class="category-border">
						List of Accounts
					</legend>

					<div class="hidden-xs"> 
						<table id="account_detail_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">
							<thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
								<th class="col-1">No.</th>
								<th class="col-3">Package</th>
								<th class="text-right col-1">Monthly</th>
								<th class="text-center col-1">Category</th>
								<th class="text-center col-1">Status</th>
								<th style="width:50px" class="text-center"><i class='fa fa-ellipsis-v'></i></th>
							</thead>
							<tbody>
							<?php foreach ($account_rows as $row) { ?>
								<tr>
									<td><?php echo $row['customer_no']; ?></td>
									<td><?php echo $row['package_name']; ?></td>
									<td class="text-right"><?php echo $row['monthly_charge']; ?></td>
									<td class="text-center"><?php echo $row['category']; ?></td>
									<td class="text-center"><?php echo (isset($account_status[$row['latest_status']]) ? $account_status[$row['latest_status']] : '' ); ?></td>
									<td>
										<a href="<?php echo base_url('customer/edit_customer');?>/<?php echo $row['customer_no']; ?>" title="Edit">
											<i class="fa fa-pencil fa-1g"></i>
										</a>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>

					<div class="visible-xs"> 
						<?php foreach ($account_rows as $row) { ?>
							<div class="panel panel-default" style="margin-bottom: 10px; border-radius: 4px;">
								<div class="panel-body" style="padding: 12px; position: relative;">
									<div style="position: absolute; top: 12px; right: 12px;">
										<a href="<?php echo base_url('customer/edit_customer');?>/<?php echo $row['customer_no']; ?>" class="btn btn-default btn-xs" title="Edit">
											<i class="fa fa-pencil"></i>
										</a>
									</div>

									<div style="margin-bottom: 10px;">
										<span class="label label-info">No. <?php echo $row['customer_no']; ?></span>
										<span class="label <?php echo $category_map[$row['category_id']]; ?>" style="margin-left: 5px;"><?php echo $row['category']; ?></span>
									</div>
									
									<span style="font-weight: bold; font-size: 13px;">
										<?php echo $row['package_name']; ?>
									</span>
									
									<hr style="margin: 8px 0;">
									
									<div class="row">
										<div class="col-xs-6">
											<span class="text-muted">Monthly:</span><br>
											<strong class="red" style="font-size: 14px;"><?php echo $row['monthly_charge']; ?></strong>
										</div>
										<div class="col-xs-6 text-right">
											<span class="text-muted">Status:</span><br>
											<strong class="<?php echo $status_colors[$row['latest_status']]; ?>" style="font-size: 12px;"><?php echo (isset($account_status[$row['latest_status']]) ? $account_status[$row['latest_status']] : '-' ); ?></strong>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>

				</fieldset>
			</div>

			<div class="col-lg-12">
				<fieldset class='category-border'>
					<legend class="category-border">
						List of Assets
					</legend>

					<div class="hidden-xs">
						<table id="asset_detail_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">
							<thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
								<th class="col-1">SN#</th>
								<th class="col-1">Category</th>
								<th class="col-1">Asset#</th>
								<th class="col-1">Site</th>
								<th class="col-1">Department</th>
								<th class="col-4">Asset Name</th>
								<th style="width:50px" class="text-center"><i class='fa fa-ellipsis-v'></i></th>
							</thead>
							<tbody>
							<?php foreach ($asset_rows as $row) { ?>
								<tr>
									<td><?php echo $row['serial_no']; ?></td>
									<td><?php echo $row['category_name']; ?></td>
									<td><?php echo $row['asset_tag']; ?></td>
									<td><?php echo $row['site_name']; ?></td>
									<td><?php echo $row['department']; ?></td>
									<td><?php echo $row['asset_name']; ?></td>
									<td>
										<a href="<?php echo base_url('asset/edit_asset');?>/<?php echo $row['asset_id']; ?>" title="Edit">
											<i class="fa fa-pencil fa-1g"></i>
										</a>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>

					<div class="visible-xs">
						<?php foreach ($asset_rows as $row) { ?>
							<div class="panel panel-default" style="margin-bottom: 10px; border-radius: 4px;">
								<div class="panel-body" style="padding: 12px; position: relative;">
									
									<div style="position: absolute; top: 12px; right: 12px;">
										<a href="<?php echo base_url('asset/edit_asset');?>/<?php echo $row['asset_id']; ?>" class="btn btn-default btn-xs" title="Edit">
											<i class="fa fa-pencil"></i>
										</a>
									</div>

									<div style="margin-bottom: 8px; padding-right: 35px;">
										<span class="label label-info"><?php echo $row['category_name']; ?></span>
										<span class="text-muted" style="font-size: 12px; margin-left: 5px;">Tag: <strong><?php echo $row['asset_tag']; ?></strong></span>
									</div>
									
									<div style="font-weight: bold; font-size: 14px; color: #333; margin-bottom: 8px;">
										<?php echo $row['asset_name']; ?>
									</div>
									
									<hr style="margin: 8px 0; border-color: #eee;">
									
									<div class="row" style="font-size: 12px;">
										<div class="col-xs-7">
											<span class="text-muted"><i class="fa fa-map-marker"></i> Site:</span> 
											<span><?php echo $row['site_name']; ?></span>
											<br>
											<span class="text-muted"><i class="fa fa-users"></i> Dept:</span> 
											<span><?php echo $row['department']; ?></span>
										</div>
										<div class="col-xs-5 text-right" style="border-left: 1px solid #eee;">
											<span class="text-muted">Serial No:</span><br>
											<span style="word-break: break-all; color: #666; font-family: monospace;">
												<?php echo $row['serial_no'] ? $row['serial_no'] : '-'; ?>
											</span>
										</div>
									</div>

								</div>
							</div>
						<?php } ?>
					</div>

				</fieldset>
			</div>

			<?php } ?>

		</fieldset>

		<!-- buttons-->
		<div class="col-md-12">
			<div>
				<button id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
					Cancel
				</button>

				<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('profile', 'M'); ?>>
					<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>
					Save
				</button>

			</div>
		</div>

	</form>

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

<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>

<?php if (!empty($input['acc_id'])) { ?>
	<?php include_once( APPPATH . 'views/templates/audit_info.php'); ?>
<?php } ?>

<script>
	var accessView = '<?php echo check_acl('profile', 'V', false) == true ? 1 : 0; ?>';
	var accessModify = '<?php echo check_acl('profile', 'M', false) == true ? 1 : 0; ?>';
	var profile_linked = '<?php echo $profile_linked; ?>';
	if (accessView == 1 && accessModify == 0) {
		$(':input').attr('READONLY', 'READONLY');
		$('option:not(:selected)').attr('disabled', true);
	}
	var profile_pic_count = '<?php echo $profile_pic_count; ?>';
</script>
<script src="<?php echo base_url("js/itelco/profile.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/ace/elements.fileinput.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>
<?php include 'panel_footer.php'; ?>