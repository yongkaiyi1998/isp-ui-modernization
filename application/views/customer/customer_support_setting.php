<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">

			<h4>{page_title}</h4>
		</div>
		
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<!-- flash data -->
			
			<form action="{form_action}" method="post" accept-charset="utf-8" id="cs_setting_details">	
				<div class="col-lg-12">
					
					<div class="input-group">
						<span class="input-group-addon" style="font-size:10px; min-width:200px; text-align:right;">TT Prefix</span>
						<input 	class="form-control" style="width:30%; min-width:100px;" id="cs_no_prefix" name="cs_no_prefix" 
								value="<?php echo $cs_no_prefix; ?>" />
					</div>

					<div class="input-group">
						<span class="input-group-addon" style="font-size:10px; min-width:200px; text-align:right;">TT running number length</span>
						<input 	class="form-control" style="width:30%; min-width:100px;" id="cs_no_length" name="cs_no_length" 
								value="<?php echo $cs_no_length; ?>" />
					</div>

					<div class="input-group">
						<span class="input-group-addon" style="font-size:10px; min-width:200px; text-align:right;">TT running number</span>
						<input 	class="form-control" style="width:30%; min-width:100px;" id="cs_no" name="cs_no" 
								value="<?php echo $cs_no; ?>" />
					</div>

				</div>
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12">
				<div>
						<button name="cancel" type="button" class="btn btn-danger" id="cancel" value="cancel" onclick="window.location='<?php echo base_url('ticket/setting'); ?>" title="Discard Changes"><i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel</button>
						&nbsp;
						<button name="save" type="submit" class="btn btn-success" id="save" value="save" title="Save record"><i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save</button></div>					
				</div>
			</form>

		</div>
		
	</div>
</div>	

<script>
	$('#cs_setting_details').submit(function(e) {
		e.preventDefault();
		$('.input-group-addon').parent().removeClass('has-error');

		let formData = new FormData(this);

		$.ajax({
			dataType: 'json',
			url: $(this).attr('action'),
			type: 'POST',
			data: formData,
			success: function (data) {
				if(data['status'] == 'ER'){
					handle_ajax_error(data);
				} else {
					window.location.href=base_url+data['url'];
				}
			},
			error: function (data) {
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
