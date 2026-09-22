<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('settings/payment_type_management');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="payment_type_detail" name="payment_type_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<input type='hidden' id='payment_source_id' name='payment_source_id' value='<?php echo $input['payment_source_id'] ?>' />
				<fieldset class='category-border-main'>	
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon input_group">Payment Type Name</span>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Name">
						</div>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
					<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Cancel
					</button>
					
					<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('payment_type','M');?> >
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>		
						Save					
					</button>
					
					<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('payment_type','D');?> >
						<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
					</button>					
					</div>					
				</div>
			</form>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>
</div>

<script>
	let clickedButton = '';

	$('.button-group button[type=submit]').on('click', function () {
		clickedButton = $(this).attr('name');
	});

	$('#payment_type_detail').submit(function (e) {
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
