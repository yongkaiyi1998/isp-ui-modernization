<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('router/type');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form autocomplete="off" id="router_type_detail" name="router_type_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Router Type Details
					</div>

					<input type="hidden" id="id" name="id" value="<?php echo set_value('id', $input['id']); ?>" />

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Router Type
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Router Name</span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Router Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Description</span>
									<textarea class="form-control" id="description" name="description" rows="3" style="resize: vertical;"><?php echo set_value('description', $input['description']); ?></textarea>
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Router Type Default Configuration
					</div>

					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">
								Router Configuration
							</legend>
							<div style="width: 100%; overflow-x: auto;">
								<table class="table table-responsive table-borderless table-striped" id="router_default_configuration">
									<thead>
										<th class="col-xs-3 text-center">
											Attribute
										</th>
										<th class="col-xs-3 text-center">
											Operation
										</th>
										<th class="col-xs-5 text-center">
											Value
										</th>
										<th class="col-xs-1">
											<i id="add_new_attr" style="cursor:pointer;"
												class="ace-icon fa fa-plus white tooltip-event" 
												title="Add New Attribute"> Click to add new attribute
											</i>
										</th>
									</thead>
									<tbody>
										<?php if(!empty($configurations)) { ?>
										<?php foreach( $configurations AS $conf ) { ?>
											<tr class="text-center">
												<td>
													<input type="text" class="col-sm-12" name="attribute[]" value="<?php echo $conf['attribute']; ?>" />
												</td>
												<td>
													<input type="text" class="col-sm-12"  name="operation[]" value="<?php echo $conf['operation']; ?>" />
												</td>
												<td>
													<input type="text" class="col-sm-12"  name="value[]" value="<?php echo $conf['value']; ?>" />
												</td>
												<td>
													<i class="fa fa-trash red tooltip-event delete_conf" style="cursor:pointer" title="Delete"></i>
												</td>
											</tr>
										<?php } ?>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</fieldset>
					</div>
				</fieldset>

				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('router/type');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
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

<script>
$(document).on("click", "#add_new_attr", function (e) {

	var generate_html = `<tr class="text-center">
							<td>
								<input type="text" class="col-sm-12" name="attribute[]" required />
							</td>
							<td>
								<input type="text" class="col-sm-12"  name="operation[]" />
							</td>
							<td>
								<input type="text" class="col-sm-12"  name="value[]" />
							</td>
							<td>
								<i class="fa fa-trash red tooltip-event delete_conf" style="cursor:pointer" title="Delete"></i>
							</td>
						</tr>`;

	$('#router_default_configuration > tbody').append(generate_html);
});

$(document).on("click", ".delete_conf", function (e) {
	if(confirm('Delete this configuration?')) {
		$(this).closest('tr').remove();
	}
});

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#router_type_detail').submit(function(e) {
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
				if(data['open_url'] != undefined && data['open_url'] != '') {
					// If open_url is set, open URL
					window.open(data['open_url'], '_blank');
				}
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
