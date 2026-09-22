<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('ticket/ticket_issues');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<h4>{page_title}</h4>
		</div>
		
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<!-- flash data -->
			
			<form action="{form_action}" method="post" id="ticket_issues_form">	
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Ticket Issues
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<input id="temp_id" name="temp_id" type="hidden"
								value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />
							<input type="hidden" id="id" name="id" value="<?php echo set_value('id', $input['id']); ?>" />
							<legend class="category-border">
								General Information
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Name</span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value( 'name', $input['name'] ); ?>">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Description</span>
									<textarea class="form-control" id="desc" name="desc" rows="4" style="resize: vertical;"><?php echo set_value( 'desc', $input['desc'] ); ?></textarea>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Category</span>
									<select class="col-lg-12" id="sel_category" name="sel_category">
										<option value=""> -- SELECT -- </option>
										<option value="tt_complaint" <?php echo set_select('sel_category', 'tt_complaint', ($sel_category == 'tt_complaint' ? true : false) ) ?>>Complaint Type</option>
										<option value="tt_sof" <?php echo set_select('sel_category', 'tt_sof', ($sel_category == 'tt_sof' ? true : false) ) ?>>Source of Fault</option>										
										<option value="tt_cof" <?php echo set_select('sel_category', 'tt_cof', ($sel_category == 'tt_cof' ? true : false) ) ?>>Cause of Fault</option>										
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('ticket/ticket_issues');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('trouble_ticket','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('trouble_ticket','D');?>>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
						</button>				
					</div>					
				</div>
			</form>
		</div>
	</div>
</div>
<script src="<?php echo base_url("js/itelco/ticket_issues_detail.js?".cssjs_ver()); ?>"></script>