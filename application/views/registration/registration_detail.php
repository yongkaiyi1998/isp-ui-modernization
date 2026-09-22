<?php include 'panel_header.php'; ?>
<div class="panel-heading noprint panel_fontsize">
	<span class="panel_space float_right">
		<a href="<?php echo base_url('registration'); ?>">
			<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
		</a>
	</span>
	<?php echo $page_title; ?>
</div>
<div class="panel-body">
	<?php echo flash_data_helper($msg); ?>
	<form id="registration_detail" name="registration_detail" method="post" class="filter-form"
		action="<?php echo $form_action; ?>" enctype="multipart/form-data">

		<?php //==== hidden fields ===== 
		?>
		<?php $inputname = 'url_after_save'; ?>
		<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
			value="<?php echo set_value($inputname, $hidden[$inputname]); ?>" type="hidden">

		<input id="form_action" name="form_action" type="hidden"
			value="<?php echo !empty($input['id']) ? 'UPDATE' : 'NEW'; ?>" />

		<input id="id" name="id" type="hidden" value="<?php echo set_value('id', $input['id']); ?>" />

		<input id="attach_reg_no" name="attach_reg_no" type="hidden"
			value="<?php echo set_value('id', $input['id']); ?>" />

		<input id="temp_id" name="temp_id" type="hidden"
			value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />

		<input id="interested" name="interested" type="hidden"
			value="<?php echo set_value('interested', $input['interested']); ?>" />

		<input id="preferred_contact_date_time" name="preferred_contact_date_time" type="hidden"
			value="<?php echo set_value('preferred_contact_date_time', $input['preferred_contact_date_time']); ?>" />

		<input id="agent_id" name="agent_id" type="hidden"
			value="<?php echo set_value('agent_id', $input['agent_id']); ?>" />

		<input id="reg_no" name="reg_no" type="hidden" value="<?php echo set_value('reg_no', $input['reg_no']); ?>" />

		<input id="task" name="task" type="hidden" value="" />

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				REGISTRATION
			</div>
			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Customer/PIC Information
					</legend>

					<?php $sel_type = set_value('type', $input['type']); ?>

					<div class="col-lg-12">
						<div class="input-group">
							Residential
							<?php $inputname = 'type'; ?>
							<input id="type_r" name="<?php echo $inputname; ?>" value="r" type="radio" checked="checked"
								onclick="chk_rb();" <?php if ($sel_type == 'r') {
									echo 'checked="checked"';
								} ?> />&nbsp;&nbsp;&nbsp;
							Commercial Broadband
							<?php $inputname = 'type'; ?>
							<input id="type_b" name="<?php echo $inputname; ?>" value="b" type="radio"
								onclick="chk_rb();" <?php if ($sel_type == 'b') {
									echo 'checked="checked"';
								} ?> />&nbsp;&nbsp;&nbsp;
							Commercial - Others
							<?php $inputname = 'type'; ?>
							<input id="type_o" name="<?php echo $inputname; ?>" value="o" type="radio"
								onclick="chk_rb();" <?php if ($sel_type == 'o') {
									echo 'checked="checked"';
								} ?> />&nbsp;&nbsp;&nbsp;
						</div>
					</div>

					<?php if (!empty($input['id'])) { ?>
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Reg No.<span class="red">*</span></span>
								<?php $inputname = 'reg_no'; ?>
								<input id="fake_<?php echo $inputname; ?>" name="fake_<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control" placeholder="Registration number" readonly />
							</div>
						</div>
					<?php } ?>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Name<span class="red">*</span></span>
							<?php $inputname = 'name'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder="Name as in IC"
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">IC no./Passport<span class="red">*</span></span>
							<?php $inputname = 'icno'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder="" style="text-transform: uppercase"
								onkeypress="return /[a-zA-Z0-9]/i.test(event.key)"
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Phone<span class="red">*</span></span>
							<?php $inputname = 'phone'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder="eg. 60188888888"
								onkeypress="return /[0-9]/i.test(event.key)"
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Email</span>
							<?php $inputname = 'email'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group" style="display: flex; width: 100%;">
							<span class="input-group-addon input_group" style="white-space: normal !important;">
								<i class="fa fa-question-circle mr-2" data-toggle="tooltip" data-html="true"
									data-placement="top"
									title="For e-invoice submission, Malaysian applicants are recommended to provide their TIN. If no TIN is provided, a general TIN will be used. For foreign applicants, a general TIN will always be used."></i>
								Tin No.
							</span>

							<?php $inputname = 'tin'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" style="width: 60%; " />

							<div class="input-group-append mr-2 mt-1">
								<span class="input-group-text tin-loading"
									style="display:none; color: grey; margin-left: 1rem;">
									<i class="fa fa-spinner fa-spin"></i>
								</span>
								<span class="input-group-text tin-valid"
									style="display:none; color: green; margin-left: 1rem;">
									<i class="fa fa-check-circle"></i>
								</span>
								<span class="input-group-text tin-invalid"
									style="display:none; color: red; margin-left: 1rem;">
									<i class="fa fa-times-circle"></i>
								</span>
								<span class="input-group-text fail-connection"
									style="display:none; color: #856404;  margin-left: 1rem;">
									<i class="fa fa-exclamation-triangle"></i>
								</span>
							</div>

							<a href="#" class="text-primary d-none d-md-inline text-right ml-auto input-group-append"
								data-toggle="modal" data-target="#tinTutorialModal"
								style="font-size: 13px; white-space: nowrap; margin-left: auto;">
								<span class="hidden-xs">How to Get </span>TIN?
							</a>

						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Status</span>
							<?php $sel_status = set_value('status', $input['status']); ?>
							<select class="col-lg-12" id="status" name="status" style="width: 100%">
								<option value="P" <?php if ($sel_status == 'P') {
									echo 'selected="selected"';
								} ?>>
									Pending </option>
								<option value="F" <?php if ($sel_status == 'F') {
									echo 'selected="selected"';
								} ?>> Follow
									Up </option>
								<option value="S" <?php if ($sel_status == 'S') {
									echo 'selected="selected"';
								} ?>> Sales
									Order </option>
								<option value="C" <?php if ($sel_status == 'C') {
									echo 'selected="selected"';
								} ?>>
									Cancelled </option>
							</select>
						</div>
					</div>

				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12">
				<?php if ($auto_skip_so == '1'): ?>
					<fieldset class='category-border'>
						<legend class="category-border">
							Radius
						</legend>

						<div class="col-lg-12">

							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">Username</span>
									<input type="text" class="form-control" id="login_username" name="login_username"
										value="<?php echo set_value('login_username', $input['login_username']); ?>">

									<div class="input-group-append mr-2 mt-1"
										style="position: absolute; right: 12px; top: 45%; transform: translateY(-50%); z-index: 5; display: flex; align-items: center;">
										<span class="input-group-text username-loading"
											style="display:none; color: grey; margin-left: 1rem;">
											<i class="fa fa-spinner fa-spin"></i>
										</span>
										<span class="input-group-text username-valid"
											style="display:none; color: green; margin-left: 1rem;">
											<i class="fa fa-check-circle"></i>
										</span>
										<span class="input-group-text username-invalid"
											style="display:none; color: red; margin-left: 1rem;">
											<i class="fa fa-times-circle"></i>
										</span>
										<span class="input-group-text username-fail-connection"
											style="display:none; color: #856404;  margin-left: 1rem;">
											<i class="fa fa-exclamation-triangle"></i>
										</span>
									</div>
								</div>
							</div>

							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">Password</span>
									<input type="text" class="form-control" id="login_password" name="login_password"
										value="<?php echo set_value('login_password', $input['login_password']); ?>">

									<span class="input-group-btn">
										<button type="button" class="btn btn-purple btn-minier btn-generate-password">
											<span class="ace-icon fa fa-refresh icon-on-right bigger-150"></span>
											<span class="hidden-xs">Generate</span>
										</button>
									</span>
								</div>
							</div>

						</div>
					</fieldset>
				<?php endif; ?>
				<fieldset class='category-border'>
					<legend class="category-border">Building / Site & Agent
					</legend>

					<div class="col-lg-12">
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Building / Site</span>
								<select class="col-lg-12" id="building_no" name="building_no"
									onchange="autoFillAddress(); load_package_list();" style="width: 100%;">
									<option value=""> Not Applicable </option>
									<?php
									$sel_building = set_value('building_no', $input['building_no']);
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
								<select class="col-lg-12" id="agent_id" name="agent_id" style="width: 100%;">
									<option value="0" <?php echo set_select('agent_id', '', ($input['agent_id'] == '0' ? true : false)); ?>>-</option>
									<?php
									foreach ($sel_dealer_list as $val) {
										echo "<option value='" . $val['dealer_no'] . "' " . set_select('agent_id', $val['dealer_no'], ($val['dealer_no'] == $input['agent_id'] ? true : false)) . ">" . $val['name'] . "</option>";
									}
									?>
								</select>
							</div>
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
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Address Line 1<span class="red">*</span></span>
							<?php $inputname = 'bill_addr_1'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Address Line 2</span>
							<?php $inputname = 'bill_addr_2'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Address Line 3</span>
							<?php $inputname = 'bill_addr_3'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Postcode<span class="red">*</span></span>
							<?php $inputname = 'bill_postcode'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">City<span class="red">*</span></span>
							<?php $inputname = 'bill_city'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder=""
								maxlength="<?php echo $inputlength[$inputname]; ?>" />
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

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Installation Address
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							*Same with Billing
							<input type="checkbox" id="same_with_billing" name="same_with_billing" value="1" />
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Unit No</span>
								<?php $inputname = 'ins_unit_no'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control ins_addr" placeholder=""
									maxlength="<?php echo $inputlength[$inputname]; ?>" />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Address Line 1<span
										class="red">*</span></span>
								<?php $inputname = 'ins_addr_1'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control ins_addr" placeholder=""
									maxlength="<?php echo $inputlength[$inputname]; ?>" />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Address Line 2</span>
								<?php $inputname = 'ins_addr_2'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control ins_addr" placeholder=""
									maxlength="<?php echo $inputlength[$inputname]; ?>" />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Address Line 3</span>
								<?php $inputname = 'ins_addr_3'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control ins_addr" placeholder=""
									maxlength="<?php echo $inputlength[$inputname]; ?>" />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Postcode<span class="red">*</span></span>
								<?php $inputname = 'ins_postcode'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control ins_addr" placeholder=""
									maxlength="<?php echo $inputlength[$inputname]; ?>" />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">City<span class="red">*</span></span>
								<?php $inputname = 'ins_city'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
									class="form-control ins_addr" placeholder=""
									maxlength="<?php echo $inputlength[$inputname]; ?>" />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">State<span class="red">*</span></span>
								<select class="col-lg-12" id="ins_state" name="ins_state" style="width: 100%;">
									<option value=""> -- SELECT -- </option>
									<?php
									$sel_ins_state = set_value('ins_state', $input['ins_state']);
									foreach ($sel_state_list as $val) {
										echo "<option value='" . $val['state_code'] . "' " . ($sel_ins_state == $val['state_code'] ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
									}
									?>
								</select>
							</div>
						</div>
				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Preferred Contact Date Time
					</legend>

					<div class="col-lg-12">
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Preferred Day<span
										class="red">*</span></span>
								<?php if ($input['preferred_contact_date_time'] != '') { ?>
									<?php $inputname = 'day'; ?>
									<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
										value="<?php echo set_value($inputname, $day); ?>" type="text"
										class="form-control day" placeholder="" disabled />
								<?php } else { ?>
									<select class="col-lg-12" name="day" id="day" style="width: 100%;">
										<option value="anyday" <?php echo set_select('day', 'anyday', TRUE); ?>>
											Anyday
										</option>
										<option value="weekday" <?php echo set_select('day', 'weekday'); ?>>
											Weekday
										</option>
										<option value="weekend" <?php echo set_select('day', 'weekend'); ?>>
											Weekend
										</option>
									</select>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php if ($input['preferred_contact_date_time'] != '') { ?>
						<div class="col-lg-12">
							<div class="input-group" style="padding-left: 12px; padding-right: 12px;">
								<span class="input-group-addon input_group">Preferred Time<span class="red">*</span></span>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $time); ?>" type="text"
									class="form-control time" placeholder="" disabled />
							</div>
						</div>
					<?php } else { ?>
						<div class="col-lg-12">
							<div class="input-group" style="padding-left: 12px; padding-right: 12px;">
								<span class="input-group-addon input_group">From<span class="red">*</span></span>
								<select id="from_time" name="from_time" style="padding-right: 16px;">
									<?php for ($i = 1; $i <= 12; $i++): ?>
										<option value="<?php echo $i; ?>" <?php echo set_select('from_time', (string) $i, $i == 12); ?>>
											<?php echo $i . ':00'; ?>
										</option>
									<?php endfor; ?>
								</select>
								<select id="from_block" name="from_block" style="padding-right: 16px;">
									<option value="am" <?php echo set_select('from_block', 'am', TRUE); ?>>AM
									</option>
									<option value="pm" <?php echo set_select('from_block', 'pm'); ?>>PM</option>
								</select>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="input-group" style="padding-left: 12px; padding-right: 12px;">
								<span class="input-group-addon input_group">To<span class="red">*</span></span>
								<select id="to_time" name="to_time" style="padding-right: 16px;">
									<?php for ($i = 1; $i <= 12; $i++): ?>
										<option value="<?php echo $i; ?>" <?php echo set_select('to_time', (string) $i, $i == 11); ?>>
											<?php echo $i . ':00'; ?>
										</option>
									<?php endfor; ?>
								</select>
								<select id="to_block" name="to_block" style="padding-right: 16px;">
									<option value="am" <?php echo set_select('to_block', 'am'); ?>>AM
									</option>
									<option value="pm" <?php echo set_select('to_block', 'pm', TRUE); ?>>PM</option>
								</select>
							</div>
						</div>
					<?php } ?>


					<div class="col-lg-12">
						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">Specific Date</span>
								<?php $inputname = 'specific_date'; ?>
								<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
									value="<?php echo set_value($inputname, $specific_date); ?>" type="text"
									class="form-control" placeholder="" <?= $input['preferred_contact_date_time'] != '' ? 'disabled' : '' ?> />
							</div>
						</div>
					</div>
				</fieldset>
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class="category-border">
					<legend class="category-border">
						Preferred Installation Datetime
					</legend>

					<div class="col-lg-12">

						<div class="col-lg-12">
							<div class="input-group">
								<span class="input-group-addon input_group">
									Preferred Date
								</span>

								<input id="preferred_install_date" name="preferred_install_date"
									value="<?= set_value('preferred_install_date', $preferred_install_date ?? '') ?>"
									type="text" class="form-control" autocomplete="off"
									<?= !empty($input['so_head']['preferred_install_datetime']) ? 'disabled' : '' ?> />
							</div>
						</div>

						<div class="col-lg-12">
							<div class="input-group">

								<span class="input-group-addon input_group">
									Time Slot
								</span>

								<select id="preferred_install_time" name="preferred_install_time" class="form-control"
									style="font-size: 12px;" <?= !empty($input['so_head']['preferred_install_datetime']) ? 'disabled' : '' ?>>

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

										<option value="<?= $time_key ?>" <?= ($preferred_install_time ?? '') == $time_key ? 'selected' : '' ?>>
											<?= $time_value ?>
										</option>

									<?php } ?>

								</select>

							</div>
						</div>

					</div>
				</fieldset>
			</div>
		</fieldset>

		</fieldset>
		<fieldset class='category-border-main b_fields' id="commercial">
			<div class="category-border-main bg-success text-center">
				COMMERCIAL
			</div>
			<div class="col-lg-6 col-xs-12">

				<fieldset class='category-border'>
					<legend class="category-border">Company Information
					</legend>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">Company Name</span>
							<?php $inputname = 'comp_name'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder="" />
						</div>
					</div>

					<div class="col-lg-12">
						<div class="input-group">
							<span class="input-group-addon input_group">SSM#</span>
							<?php $inputname = 'ssm'; ?>
							<input id="<?php echo $inputname; ?>" name="<?php echo $inputname; ?>"
								value="<?php echo set_value($inputname, $input[$inputname]); ?>" type="text"
								class="form-control" placeholder="" />
						</div>
					</div>
				</fieldset>
			</div>
		</fieldset>

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				PRODUCTS INTERESTED
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Products
					</legend>

					<!--list of products here-->
					<div class="interested_list">
					</div>

				</fieldset>
			</div>

		</fieldset>

		<fieldset class='category-border-main'>
			<div class="category-border-main bg-success text-center">
				Remark
			</div>

			<div class="col-lg-6 col-xs-12">
				<fieldset class='category-border'>
					<legend class="category-border">Remark
					</legend>
					<textarea class="form-control" id="remark" name="remark" rows="3"
						style="resize: vertical;"><?php echo set_value('remark', $input['remark']); ?></textarea>
				</fieldset>
			</div>

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
						if (!empty($ic_attachment)) {
							foreach ($ic_attachment as $row => $res) {
								?>
								<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; "
									data-id="<?php echo $row; ?>">
									<div class="desktop-icon">
										<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>"
											data-local-path="<?php echo $res['local_path']; ?>"
											data-file-type="<?php echo $res['file_type']; ?>"
											data-extension="<?php echo $res['extension']; ?>" data-saved="1" data-category="ic"
											onclick="view_attach_doc(<?php echo $row; ?>,'ic');">
											<div class="icon-image">
												<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
												<?php if ($res['file_type'] == 'image'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php elseif ($res['file_type'] == 'video'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-film"></i>
												<?php elseif ($res['file_type'] == 'doc'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-file"></i>
												<?php elseif ($res['file_type'] == 'pdf'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php endif ?>
											</div>
										</div>

										<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1"
											data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>"
											data-file-name="<?php echo $res['file_name']; ?>"
											data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?>
										</div>

										<div class="text-right" style="min-height:20px;">
											<!--only for existing attachments-->
											<?php if ($res['is_temp'] == '0') { ?>
												<input type="hidden" name="existing_attach_ic[<?php echo $row; ?>]"
													value="<?php echo $res['file_id']; ?>" />
											<?php } ?>
											<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol"
												id="loading_symbol_attach_<?php echo $row; ?>"
												name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;" />

											&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;"
												data-file-id="<?php echo $res['file_id']; ?>"
												data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>"
												class="att_remove_btn" onclick="removeFile(this)">
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
						if (!empty($ssm_attachment)) {
							foreach ($ssm_attachment as $row => $res) {
								?>
								<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; "
									data-id="<?php echo $row; ?>">
									<div class="desktop-icon">
										<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>"
											data-local-path="<?php echo $res['local_path']; ?>"
											data-file-type="<?php echo $res['file_type']; ?>"
											data-extension="<?php echo $res['extension']; ?>" data-saved="1" data-category="ssm"
											onclick="view_attach_doc(<?php echo $row; ?>,'ssm');">
											<div class="icon-image">
												<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
												<?php if ($res['file_type'] == 'image'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php elseif ($res['file_type'] == 'video'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-film"></i>
												<?php elseif ($res['file_type'] == 'doc'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-file"></i>
												<?php elseif ($res['file_type'] == 'pdf'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php endif ?>
											</div>
										</div>

										<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1"
											data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>"
											data-file-name="<?php echo $res['file_name']; ?>"
											data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?>
										</div>

										<div class="text-right" style="min-height:20px;">
											<!--only for existing attachments-->
											<?php if ($res['is_temp'] == '0') { ?>
												<input type="hidden" name="existing_attach_ssm[<?php echo $row; ?>]"
													value="<?php echo $res['file_id']; ?>" />
											<?php } ?>
											<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol"
												id="loading_symbol_attach_<?php echo $row; ?>"
												name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;" />

											&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;"
												data-file-id="<?php echo $res['file_id']; ?>"
												data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>"
												class="att_remove_btn" onclick="removeFile(this)">
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
						if (!empty($auth_attachment)) {
							foreach ($auth_attachment as $row => $res) {
								?>
								<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; "
									data-id="<?php echo $row; ?>">
									<div class="desktop-icon">
										<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>"
											data-local-path="<?php echo $res['local_path']; ?>"
											data-file-type="<?php echo $res['file_type']; ?>"
											data-extension="<?php echo $res['extension']; ?>" data-saved="1"
											data-category="auth" onclick="view_attach_doc(<?php echo $row; ?>,'auth');">
											<div class="icon-image">
												<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
												<?php if ($res['file_type'] == 'image'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php elseif ($res['file_type'] == 'video'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-film"></i>
												<?php elseif ($res['file_type'] == 'doc'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-file"></i>
												<?php elseif ($res['file_type'] == 'pdf'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php endif ?>
											</div>
										</div>

										<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1"
											data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>"
											data-file-name="<?php echo $res['file_name']; ?>"
											data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?>
										</div>

										<div class="text-right" style="min-height:20px;">
											<!--only for existing attachments-->
											<?php if ($res['is_temp'] == '0') { ?>
												<input type="hidden" name="existing_attach_auth[<?php echo $row; ?>]"
													value="<?php echo $res['file_id']; ?>" />
											<?php } ?>
											<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol"
												id="loading_symbol_attach_<?php echo $row; ?>"
												name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;" />

											&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;"
												data-file-id="<?php echo $res['file_id']; ?>"
												data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>"
												class="att_remove_btn" onclick="removeFile(this)">
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
						if (!empty($others_attachment)) {
							foreach ($others_attachment as $row => $res) {
								?>
								<div class="col-lg-2 col-xs-12 attach-file" style="margin-bottom: 10px; "
									data-id="<?php echo $row; ?>">
									<div class="desktop-icon">
										<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>"
											data-local-path="<?php echo $res['local_path']; ?>"
											data-file-type="<?php echo $res['file_type']; ?>"
											data-extension="<?php echo $res['extension']; ?>" data-saved="1"
											data-category="others" onclick="view_attach_doc(<?php echo $row; ?>,'others');">
											<div class="icon-image">
												<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
												<?php if ($res['file_type'] == 'image'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php elseif ($res['file_type'] == 'video'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-film"></i>
												<?php elseif ($res['file_type'] == 'doc'): ?>
													<?php if (empty($attachment_text_title)) {
														$attachment_text_title = $res['file_name'];
													} ?>
													<i class="fa fa-file"></i>
												<?php elseif ($res['file_type'] == 'pdf'): ?>
													<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
												<?php endif ?>
											</div>
										</div>

										<div class="file-remark" title="<?php echo $attachment_text_title; ?>" data-existing="1"
											data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>"
											data-file-name="<?php echo $res['file_name']; ?>"
											data-is-temp="<?php echo $res['is_temp']; ?>"><?php echo $attachment_text_title; ?>
										</div>

										<div class="text-right" style="min-height:20px;">
											<!--only for existing attachments-->
											<?php if ($res['is_temp'] == '0') { ?>
												<input type="hidden" name="existing_attach_other[<?php echo $row; ?>]"
													value="<?php echo $res['file_id']; ?>" />
											<?php } ?>
											<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol"
												id="loading_symbol_attach_<?php echo $row; ?>"
												name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;" />

											&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;"
												data-file-id="<?php echo $res['file_id']; ?>"
												data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>"
												class="att_remove_btn" onclick="removeFile(this)">
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
					<input multiple="multiple" type="file" id="attach_file_input" data-preview-file-type="text"
						data-module="profile" />
				</div>

				<div class="col-lg-9 col-xs-12" id="file-container">
					<?php
					if (!empty($attachment)) {
						foreach ($attachment as $row => $res) {
							?>
							<div class="col-lg-3 col-xs-12 attach-file" style="margin-bottom: 10px; "
								data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>"
										data-local-path="<?php echo $res['local_path']; ?>"
										data-file-type="<?php echo $res['file_type']; ?>"
										data-extension="<?php echo $res['extension']; ?>" data-saved="1"
										onclick="view_attach_doc(<?php echo $row; ?>);">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) {
													$attachment_text_title = $res['file_name'];
												} ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) {
													$attachment_text_title = $res['file_name'];
												} ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>"><a
											style="cursor:pointer;" data-existing="1"
											data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>"
											data-file-name="<?php echo $res['file_name']; ?>"
											data-is-temp="<?php echo $res['is_temp']; ?>"
											onclick="change_remark(this);"><?php echo $attachment_text_title; ?></a></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
											<input type="hidden" id="existing_attach" name="existing_attach[<?php echo $row; ?>]"
												value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol"
											id="loading_symbol_attach_<?php echo $row; ?>"
											name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;" />

										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;"
											data-file-id="<?php echo $res['file_id']; ?>"
											data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>"
											class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>

							<input type="file" name="attach_attachment[<?php echo $row; ?>]" class="hide"
								id="attach_attachment_<?php echo $row; ?>" value="<?php echo $res['local_path']; ?>">

							<input type="hidden" name="attach_attachment_remark[<?php echo $row; ?>]"
								value="<?php echo $res['remark']; ?>">
							<?php
						}
					}
					?>

				</div>

				<?php include_once(APPPATH . 'views/templates/modal_html_attachment_remark.php'); ?>

			</div>

		</fieldset>

		<!-- buttons-->
		<div class="col-md-12">
			<div
				class="button-group d-flex flex-column flex-md-row gap-2 justify-content-start align-items-stretch align-items-md-center">
				<button id="btCancel" name="btCancel" type="button" value="cancel"
					class="btn btn-warning mb-2 mb-md-0 me-md-2" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
					Cancel
				</button>

				<?php if (!empty($input['id'])) { ?>

					<button id="btSO" name="btSO" type="submit" value="btSO" class="btn btn-info mb-2 mb-md-0 me-md-2" <?php echo tooltip_helper('Click To Convert Record to Sales Order'); ?> 	<?php echo check_acl_btn('registration', 'M'); ?>>
						<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i>
						Sales Order
					</button>

					<?php if ($auto_skip_so == '1') { ?>
						<button id="btSOProfile" name="btSOProfile" type="submit" value="btSOProfile"
							class="btn btn-info mb-2 mb-md-0 me-md-2" <?php echo tooltip_helper('Click To Convert Record to Sales Order'); ?> 		<?php echo check_acl_btn('registration', 'M'); ?>>
							<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i>
							Sales Order And Profile
						</button>
					<?php } ?>

				<?php } ?>

				<button id="btSave" name="btSave" type="submit" value="submit"
					class="btn btn-success mb-2 mb-md-0 me-md-2" <?php echo tooltip_helper('Click To Save Record'); ?>
					<?php echo check_acl_btn('registration', 'M'); ?>>
					<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>
					Save
				</button>

				<span class="ms-md-2"><img id='loading-icon' style='display:none;'
						src='<?php echo base_url('images/loading.gif'); ?>' /></span>
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

	<?php if (!empty($input['reg_no'])) { ?>
		<?php include_once(APPPATH . 'views/templates/audit_info.php'); ?>
	<?php } ?>

</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>

<?php include_once(APPPATH . 'views/registration/modal/tintutorialmodal.php'); ?>
<?php include_once(APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>

<script>
	var accessView = '<?php echo check_acl('registration', 'V', false) == true ? 1 : 0; ?>';
	var accessModify = '<?php echo check_acl('registration', 'M', false) == true ? 1 : 0; ?>';
	if (accessView == 1 && accessModify == 0) {
		$(':input').attr('READONLY', 'READONLY');
		$('option:not(:selected)').attr('disabled', true);
	}
</script>
<script src="<?php echo base_url("js/itelco/registration.js?" . cssjs_ver()); ?>"></script>
<!--ace file-->
<script src="<?php echo base_url("js/ace/elements.fileinput.js?" . cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/attachment.js?" . cssjs_ver()); ?>"></script>
<?php include 'panel_footer.php'; ?>