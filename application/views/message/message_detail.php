<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('message_scheduler');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="message_detail" name="dealer_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>
					<div class="category-border-main bg-success text-center hide" ></div>
					<div class="row">
						<input type="hidden" class="form-control" id="scheduler_id" name="scheduler_id" value="<?php echo $input['scheduler_id']; ?>" >
						<input type="hidden" class="form-control" id="outgoing_id" name="outgoing_id" value="<?php echo $input['outgoing_id']; ?>" >
						
						<?php if ($input['msg_status'] != 'S') { ?>
							<div class="col-lg-6">
								<fieldset class='category-border'>
									<legend class="category-border">
										Message Information
									</legend>
									<div class="col-lg-12">
										<div class="input-group">
											<span class="input-group-addon input_group">Schedule on</span>
											<?php $inputname = 'msg_schedule_on'; ?>
											<input
												id			="<?php echo $inputname; ?>"
												name		="<?php echo $inputname; ?>"
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text"
												class		="form-control"
												placeholder	="Schedule on" 
												style="height: 50px;">
										</div>
										<div class="input-group">
											<span class="input-group-addon input_group">Title</span>
											<?php $inputname = 'title'; ?>
											<input
												id			="<?php echo $inputname; ?>"
												name		="<?php echo $inputname; ?>"
												value		="<?php echo set_value($inputname, $title); ?>"
												type		="text"
												class		="form-control"
												placeholder	="Title" 
												style="height: 50px;" oninput="preview()">
										</div>

										<?php $inputname = 'message'; ?>
										<?php $message =  set_value($inputname, $message); ?>
										<textarea
												id			="<?php echo $inputname; ?>"
												name		="<?php echo $inputname; ?>"
												class="form-control"
												placeholder="Type your Message here..."
												style="min-width: 100%; height: 390px; color: black;" oninput="preview()"><?php echo $message;?></textarea>
									</div>
								</fieldset>
							</div>
						<?php } ?>
						<div class="col-lg-6">
							<?php if ($input['msg_status'] == 'S') { ?>
								<fieldset class='category-border'>
									<legend class="category-border">
										Message Information
									</legend>
									<div class="col-lg-12">
										<div class="input-group">
											<span class="input-group-addon input_group">Schedule on</span>
											<?php $inputname = 'msg_schedule_on'; ?>
											<input
												id			="<?php echo $inputname; ?>"
												name		="<?php echo $inputname; ?>"
												value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
												type		="text"
												class		="form-control"
												placeholder	="Schedule on" 
												style="height: 50px;">
										</div>
									</div>
								</fieldset>
							<?php } ?>
							<fieldset class='category-border'>
								<legend class="category-border">
									Recipient By 
									<input type="radio" name="send_by" value="individual" <?php echo $input['is_individual'] == 1 ? 'CHECKED' : '' ;  ?> /> Individual 
								</legend>
								
								<div class="col-lg-12" id="individual_form">
									<?php $inputname = 'msg_to'; ?>
									<textarea
										id			="<?php echo $inputname; ?>"
										name		="<?php echo $inputname; ?>"
										class="form-control"
										style="min-width: 100%; height: 50px; resize: none;" placeholder="601XXXXXXXXX" <?php echo ($input['msg_status'] == 'S')? 'disabled' : '' ?>><?php echo $input['msg_to'];?></textarea>
									<br>
									<fieldset class="category-border">
										<legend class="category-border">
											Message Type
										</legend>
										<?php echo $type ?>
									</fieldset>
									<fieldset class="category-border">
										<legend class="category-border">
											Sending Status
										</legend>
										<?php echo $status ?>
									</fieldset>
								</div>
							</fieldset>
							<fieldset class='category-border'>
								<legend class="category-border">
									Message Preview 
								</legend>
								
								<div class="col-lg-12" id="individual_form">
									<?php $inputname = 'full_message'; ?>
									<textarea
										id			="<?php echo $inputname; ?>"
										name		="<?php echo $inputname; ?>"
										class="form-control"
										style="min-width: 100%; height: 300px; color: GREY;" readonly><?php echo $input['message'];?></textarea>
								</div>
							</fieldset>
						</div>
					</div>
				</fieldset>
				<div class="col-md-12 button-group">
					<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('message_scheduler');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel
					</button>
					<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?>
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save
					</button>
					<button id="btDelete" name="btDelete" type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php //echo $input['btn_delete']?>
						<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i> Delete
					</button>
				</div>
			</form>
		</div>
	</div>
	<div id="clear" style="clear:both;"></div>
</div>
<script>
   var disable_input = '<?php echo (empty($disabled_input))?'0':$disabled_input; ?>';

	$( document ).ready(function() {
		init();		
	});
</script>
<script src="<?php echo base_url("js/itelco/message_scheduler.js?").cssjs_ver(); ?>" ></script>
<script src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?").cssjs_ver(); ?>" ></script>
