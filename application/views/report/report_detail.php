<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('report');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
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
