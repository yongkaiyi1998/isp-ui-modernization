<div class="container" >
	{data}
	<div class="panel panel-default" width="800">
		<div class="panel-heading noprint">
			<div>
				<h4>
					{page_title}
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<!-- flash data -->
			<?php echo flash_data_helper($msg); ?>
			<form method="post" action="{form_action}">
				<div class="col-lg-4">
					<div class="input-group">
						<span class="input-group-addon input_group_150">Usernames</span>
						<input type="text" class="form-control" value="{display_name}" disabled>
					</div>
					<div class="input-group">
						<span class="input-group-addon input_group_150">Password</span>
						<input type="password" name="input[password]" class="form-control" value="<?php echo set_value('input[password]', ''); ?>">
					</div>
					<div class="input-group">
						<span class="input-group-addon input_group_150">Re-type Password</span>
						<input type="password" name="retype_password" class="form-control" >
					</div>
				</div>
				<div class="col-lg-12">&nbsp;</div>				
				<div class="col-lg-12 text-left">
					<button name="processtype[cancel]" type="submit" value="cancel" class="btn btn-warning">						
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel 
					</button>
					<button name="processtype[save]" type="submit" value="save" class="btn btn-success">
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save 
					</button>
				</div>
			</form>
		</div>
	</div>
	{/data}
</div>

<script src="<?php echo base_url("js/itelco/user.js?".cssjs_ver()); ?>" ></script>
