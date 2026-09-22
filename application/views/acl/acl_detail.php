<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('acl');?>">
					<i class="menu-icon fa fa-times grey" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="acl_detail" name="acl_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						ACL Role
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								ACL Role
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Role No</span>
									<input type="text" class="form-control" id="role_no" name="role_no" value="<?php echo set_value('role_no', $input['role_no']); ?>" placeholder="Adjustment No" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Role Name</span>
									<?php $inputname = 'role_name'; ?>
									<input 
										id			="<?php echo $inputname; ?>" 
										name		="<?php echo $inputname; ?>" 
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
										type		="text" 
										class		="form-control">
									<div class="input-group-btn">
										<button id="btRole" name="btRole" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Roles <span class="caret"></span></button>
										<ul class="dropdown-menu dropdown-menu-right">
											<?php
												foreach ($sel_acl_role_list as $val) {
													echo "<li><a href='#' onclick='role_select(" . $val['role_no'] . ")'>" . $val['name'] . "</a></li>";
												}
											?>
										</ul>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								List of User (Under this role)
							</legend>
							<div id="list_of_user_role">
							</div>
						</fieldset>
					</div>
				</fieldset>
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center">
						<span style="display:inline-block; vertical-align:middle;">
							ACL Permission
						</span>
						<label style="display:inline-block; margin-left:20px; margin-bottom:0; font-weight:normal; vertical-align:middle; cursor:pointer;">
							<input type="checkbox" id="chk_select_all_global" style="vertical-align:middle; height: auto; margin-top: 0;">
							Select All / Full Access
						</label>
					</div>
					<?php
						$z = 0;
						$acl_entry_list = $input['acl_list'];
					?>

					<div class="row" style="display: flex; flex-wrap: wrap; padding-left: 1rem; padding-right: 1rem;">
						<?php foreach ($acl_entry_list as $key => $val): $z++; ?>
							<div class="col-xs-12 col-sm-6 col-md-3 col-lg-2" style="display: flex;">
								<div class="panel panel-default" style="flex-grow: 1; display: flex; flex-direction: column;">
									<div class="panel-heading text-center">
										<label style="display:flex; align-items:center; justify-content:center; gap:6px; margin:0; cursor:pointer;">
											<input type="checkbox" class="section-select-all" data-section="<?php echo $key; ?>" style="margin:0;">
											<strong><?php echo $val['display_name']; ?></strong>
										</label>
									</div>
									<div class="panel-body">
										<?php if ($val['display_name'] == 'Service Ticket'): ?>
											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_A'; ?>"
														name="<?php echo 'chk_' . $key . '_A'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_A', "1", ($val['A'] == 1)); ?>
														style="margin:0;"
													>
													<span><strong>Full Access</strong></span>
												</label>
											</div>
											<hr>
										<?php endif; ?>

										<?php if ($val['display_name'] == 'Account'): ?>
											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_V'; ?>"
														name="<?php echo 'chk_' . $key . '_V'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_V', "1", ($val['V'] == 1)); ?>
														style="margin:0;"
													>
													<span>View</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_M'; ?>"
														name="<?php echo 'chk_' . $key . '_M'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_M', "1", ($val['M'] == 1)); ?>
														style="margin:0;"
													>
													<span>Modify</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_D'; ?>"
														name="<?php echo 'chk_' . $key . '_D'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_D', "1", ($val['D'] == 1)); ?>
														style="margin:0;"
													>
													<span>Delete</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_TI'; ?>"
														name="<?php echo 'chk_' . $key . '_TI'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_TI', "1", (($val['TI'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Termination Initiator</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_TC'; ?>"
														name="<?php echo 'chk_' . $key . '_TC'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_TC', "1", (($val['TC'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Termination Confirm</span>
												</label>
											</div>
										<?php endif; ?>

										<!-- Reporting -->
										<?php if ($val['display_name'] == 'Reporting'): ?>
											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_A'; ?>"
														name="<?php echo 'chk_' . $key . '_A'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_A', "1", ($val['A'] == 1)); ?>
														style="margin:0;"
													>
													<span><strong>Full Access</strong></span>
												</label>
											</div>
											<hr>

											<!-- various reporting category here -->

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_C'; ?>"
														name="<?php echo 'chk_' . $key . '_C'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_C', "1", (($val['C'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Cutomer Reports</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_B'; ?>"
														name="<?php echo 'chk_' . $key . '_B'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_B', "1", (($val['B'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Billing Reports</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_G'; ?>"
														name="<?php echo 'chk_' . $key . '_G'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_G', "1", (($val['G'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Agent Reports</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_G2'; ?>"
														name="<?php echo 'chk_' . $key . '_G2'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_G2', "1", (($val['G2'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Agent Recalc Commission</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_T'; ?>"
														name="<?php echo 'chk_' . $key . '_T'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_T', "1", (($val['T'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Trouble/Service Ticket Reports</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_S'; ?>"
														name="<?php echo 'chk_' . $key . '_S'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_S', "1", (($val['S'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Asset Reports</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_R'; ?>"
														name="<?php echo 'chk_' . $key . '_R'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_R', "1", (($val['R'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Radius Reports</span>
												</label>
											</div>

											<div style="margin-bottom: 10px;">
												<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
													<input 
														type="checkbox"
														id="<?php echo 'chk_' . $key . '_L'; ?>"
														name="<?php echo 'chk_' . $key . '_L'; ?>"
														value="1"
														class="perm-checkbox"
														data-section="<?php echo $key; ?>"
														<?php echo set_checkbox('chk_' . $key . '_L', "1", (($val['L'] ?? 0) == 1)); ?>
														style="margin:0;"
													>
													<span>Other Reports (Logs...etc)</span>
												</label>
											</div>

										<?php endif; ?>

										<?php if ($val['display_name'] != 'Reporting' && $val['display_name'] != 'Account'): ?>

										<div style="margin-bottom: 10px;">
											<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
												<input 
													type="checkbox"
													id="<?php echo 'chk_' . $key . '_V'; ?>"
													name="<?php echo 'chk_' . $key . '_V'; ?>"
													value="1"
													class="perm-checkbox"
													data-section="<?php echo $key; ?>"
													<?php echo set_checkbox('chk_' . $key . '_V', "1", ($val['V'] == 1)); ?>
													style="margin:0;"
												>
												<span>View</span>
											</label>
										</div>

										<div style="margin-bottom: 10px;">
											<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
												<input 
													type="checkbox"
													id="<?php echo 'chk_' . $key . '_M'; ?>"
													name="<?php echo 'chk_' . $key . '_M'; ?>"
													value="1"
													class="perm-checkbox"
													data-section="<?php echo $key; ?>"
													<?php echo set_checkbox('chk_' . $key . '_M', "1", ($val['M'] == 1)); ?>
													style="margin:0;"
												>
												<span>Modify</span>
											</label>
										</div>

										<div style="margin-bottom: 10px;">
											<label style="display: flex; align-items: center; gap: 6px; margin: 0;">
												<input 
													type="checkbox"
													id="<?php echo 'chk_' . $key . '_D'; ?>"
													name="<?php echo 'chk_' . $key . '_D'; ?>"
													value="1"
													class="perm-checkbox"
													data-section="<?php echo $key; ?>"
													<?php echo set_checkbox('chk_' . $key . '_D', "1", ($val['D'] == 1)); ?>
													style="margin:0;"
												>
												<span>Delete</span>
											</label>
										</div>

										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('acl','M');?> >
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('acl','D');?> >
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
	<script src="<?php echo base_url("js/itelco/acl.js?".cssjs_ver()); ?>" ></script>
