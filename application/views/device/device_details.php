<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('device_setting');?>">
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
							BASIC
					</div>	
					<div class="col-lg-12">
						<div class="row">				
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Device Number</span>
									<?php $inputname = 'dev_number'; ?>					
									<input 
										id			= "<?php echo $inputname; ?>"
										name		= "<?php echo $inputname; ?>"  
										value		= "<?php echo set_value($inputname, $input[$inputname]); ?>" 
										type		="text" 
										class		="form-control"  
										placeholder	="" 
										<?php echo $readonly; ?>
										>
									<input 
										id			= "dev_number_h"
										name		= "dev_number_h"  
										value		= "<?php echo $input[$inputname]; ?>" 
										type		="hidden" 
										class		="form-control"  
										placeholder	="" >
								</div>				
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Status</span>					
										<select class="col-lg-12" id="status" name="status">
											<option value="1" <?php echo set_select('status', '1', ($input['dev_active'] == '1' ? true : false) ); ?> >Active</option>
											<option value="2" <?php echo set_select('status', '2', ($input['dev_active'] == '2' ? true : false) ); ?> >Inactive</option>
										</select>
								</div>				
							</div>											
						</div>
					</div>					
				</fieldset>
				<fieldset class='category-border-main'>											
					<div class="category-border-main bg-success text-center" >
							MORE INFORMATION
					</div>	
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Last Check in</span>
									<input 
										id 			="last_checking" 
										name 		="last_checking" 
										type 		="text" 
										class 		="form-control" 
										value 		="<?php echo $input['dev_lastcheckin']; ?>" 
										placeholder	="" 
										readonly >
								</div>
							</div>
							<div class="col-lg-6">
							<div class="pull-right"> 
								<button  id="btReset" name="btReset" type="button" value="reset" class="btn btn-primary" onclick="" 
								<?php echo tooltip_helper('Click To Reset Pending, Sent, Failed, Received Records'); ?>
								<?php echo $input['btn_reset']?> >
								<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i> Reset
								</button>
							</div>
							</div>							
						</div>
						<br/>
						<div class="row">
							<div class="col-lg-3">
								<div class="input-group">
									<span class="input-group-addon input_group">Pending</span>
									<?php $inputname = 'dev_pending'; ?>	
									<input 
										id			= "<?php echo $inputname; ?>"
										name		= "<?php echo $inputname; ?>"  
										value		= "<?php echo set_value($inputname, $input[$inputname]); ?>" 
										type		= "text" 
										class		= "form-control"  
										placeholder	= ""
										readonly 
										 >
								</div>
							</div>
							<div class="col-lg-3">
								<div class="input-group">
									<span class="input-group-addon input_group">Sent</span>
									<?php $inputname = 'dev_sent'; ?>
									<input 
										id			= "<?php echo $inputname; ?>"
										name		= "<?php echo $inputname; ?>"  
										value		= "<?php echo set_value($inputname, $input[$inputname]); ?>" 
										type		= "text" 
										class		= "form-control"  
										placeholder	= "" 
										readonly 
										 >
								</div>
							</div>
							<div class="col-lg-3">
								<div class="input-group">
									<span class="input-group-addon input_group">Failed</span>
									<?php $inputname = 'dev_failed'; ?>
									<input 
										id			= "<?php echo $inputname; ?>"
										name		= "<?php echo $inputname; ?>"  
										value		= "<?php echo set_value($inputname, $input[$inputname]); ?>" 
										type		= "text" 
										class		= "form-control"  
										placeholder	= "" 
										readonly 
										 >
								</div>
							</div>
							<div class="col-lg-3">
								<div class="input-group">
									<span class="input-group-addon input_group">Received</span>
									<?php $inputname = 'dev_received'; ?>
									<input 
										id			= "<?php echo $inputname; ?>"
										name		= "<?php echo $inputname; ?>"  
										value		= "<?php echo set_value($inputname, $input[$inputname]); ?>" 
										type		= "text" 
										class		= "form-control"  
										placeholder	= "" 
										readonly 
										 >
								</div>
							</div>
						</div>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div>
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('device_setting');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('dealer','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?>
							<?php echo $input['btn_delete']?> >
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i> Delete
						</button>				
					</div>					
				</div>
			</form>
		</div>
	</div>	
	<div id="clear" style="clear:both;"></div>
</div>

<script src="<?php echo base_url("js/itelco/device.js?".cssjs_ver()); ?>" ></script>
