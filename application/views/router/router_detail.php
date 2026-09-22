<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('router');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form autocomplete="off" id="router_detail" name="router_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Router Configuration
					</div>

					<input type="hidden" id="id" name="id" value="<?php echo set_value('id', $input['id']); ?>" />

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Router Config
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Router Name</span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Router Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">IP Address</span>
									<input type="text" class="form-control" id="ip" name="ip" value="<?php echo set_value('ip', $input['ip']); ?>" placeholder="IP Address">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">SSH Port</span>
									<input type="text" class="form-control" id="ssh_port" name="ssh_port" value="<?php echo set_value('ssh_port', $input['ssh_port']); ?>" placeholder="SSH Port">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Login ID</span>
									<input type="text" class="form-control" id="login_id" name="login_id" value="<?php echo set_value('login_id', $input['login_id']); ?>" placeholder="Login ID">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Login Password</span>
									<input type="text" class="form-control" id="login_password" name="login_password" value="<?php echo set_value('login_password', $input['login_password']); ?>" placeholder="Login Password">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Login SSH Key</span>
									<textarea class="form-control" id="login_key" name="login_key" rows="3" style="resize: vertical;"><?php echo set_value('login_key', $input['login_key']); ?></textarea>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Router Type</span>
									<select class="col-lg-12" id="type" name="type">
										<option value="0">--Router Type--</option>
										<?php foreach($router_list as $router) { ?>
											<option value=<?php echo $router['id'] ?> <?php echo set_select('type', '', ($input['type'] == $router['id'] ? true : false) ); ?>><?php echo $router['name'] ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">PPPoE</span>
									<input type="checkbox" id="pppoe" name="pppoe" value="1" <?php echo set_checkbox('pppoe', '1', ($input['pppoe']==1? true:false) ); ?> />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">SSH Enable</span>
									<input type="checkbox" id="ssh_enabled" name="ssh_enabled" value="1" <?php echo set_checkbox('ssh_enabled', '1', ($input['ssh_enabled']==1? true:false) ); ?> />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Description</span>
									<textarea class="form-control" id="description" name="description" rows="3" style="resize: vertical;"><?php echo set_value('description', $input['description']); ?></textarea>
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('router');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('router','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('router','D');?>>
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
	<script src="<?php echo base_url("js/itelco/router.js?".cssjs_ver()); ?>" ></script>
