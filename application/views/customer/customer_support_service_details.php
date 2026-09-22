<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('customer_support/customer_support_service');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<h4>{page_title}</h4>
		</div>
		
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<!-- flash data -->
			
			<form action="{form_action}" method="post" id="cs_service_config">	
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Customer Support Service Type
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
									<select class="col-lg-12" id="sel_cs_type" name="sel_cs_type">
										<option value=""> -- SELECT -- </option>
										<option value="cs_service" <?php echo set_select('sel_cs_type', 'cs_service', ($sel_cs_type == 'cs_service' ? true : false) ) ?>>Service Type</option>
										<option value="cs_problem" <?php echo set_select('sel_cs_type', 'cs_problem', ($sel_cs_type == 'cs_problem' ? true : false) ) ?>>Problem Type</option>								
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div>
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('customer_support/customer_support_service');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('customer_support','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('customer_support','D');?>>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
						</button>				
					</div>					
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	let clickedButton = '';

	$('.button-group button[type=submit]').on('click', function () {
		clickedButton = $(this).attr('name');
	});

	$('#cs_service_config').submit(function(e) {
		e.preventDefault();
		$('.input-group-addon').parent().removeClass('has-error');

		$('.button-group button').prop('disabled', true);

		let formData = new FormData(this);
		if(clickedButton != '') formData.append(clickedButton, 'submit');

		$.ajax({
			dataType: 'json',
			url: $(this).attr('action'),
			type: 'POST',
			data: formData,
			success: function (data) {
				if(data['status'] == 'ER'){
					handle_ajax_error(data);
					$('.button-group button').prop('disabled', false);
				} else {
					window.location.href=base_url+data['url'];
				}
			},
			error: function (data) {
				$('.button-group button').prop('disabled', false);
				$.gritter.add({
					title: 'ERROR',
					text: 'Something wrong has occured during saving.',
					time: '5000',
					close_icon: 'l-arrows-remove s16',
					class_name: 'info-notice',
				});
			},
			cache: false,
			contentType: false,
			processData: false
		});
	});	
</script>