<script src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/autosize.js?".cssjs_ver()); ?>"></script>

<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('audit_docs');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>

		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="upload_doc_form" name="upload_doc_form" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">

				<input id="temp_id" name="temp_id" type="hidden"
				value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />
				<fieldset class='category-border-main owner-form'>	
					<div class="category-border-main bg-success text-center" >
						Upload Document to Audit Folder
					</div>

					<div class="col-lg-12">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Customer No.<span class="red">*</span></span>
								<input type="text" class="form-control" id="customer_no_display" name="customer_no_display" 
										value="<?php echo set_value( 'customer_no_display', $input['customer_no_display'] ); ?>" placeholder="Customer No." />
								<input type="hidden" class="form-control" id="customer_no" name="customer_no" 
										value="<?php echo set_value( 'customer_no', $input['customer_no'] ); ?>" />
							</div>
						</div>

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Customer Name</span>
								<input type="text" class="form-control" id="customer_name" name="customer_name" readonly  
										value="" placeholder="Customer Name" />
							</div>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Bill No.</span>
								<input type="text" class="form-control" id="bill_no_display" name="bill_no_display" 
										value="<?php echo set_value( 'bill_no_display', $input['bill_no_display'] ); ?>" placeholder="Please select a customer_no first" />
								<input type="hidden" class="form-control" id="bill_no" name="bill_no" 
										value="<?php echo set_value( 'bill_no', $input['bill_no'] ); ?>" />
							</div>
						</div>

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Bill</span>
								<input type="text" class="form-control" id="bill_display" name="bill_display" readonly  
										value="" placeholder="Bill" />
							</div>
						</div>
					</div>

					<div class="col-lg-12">

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">File Type<span class="red">*</span></span>
								<span class="input-icon input-icon-right" style='display:inline;'>
									<select style="width:100%;" id="link_doc" name="link_doc">
										<option value="bill" <?php if ($input['link_doc'] == 'bill') { echo 'selected="selected"'; } ?>>Bill</option>
										<option value="customer_so" <?php if ($input['link_doc'] == 'customer_so') { echo 'selected="selected"'; } ?>>Customer Sales Order</option>
										<option value="customer_install" <?php if ($input['link_doc'] == 'customer_install') { echo 'selected="selected"'; } ?>>Customer Installation Form</option>
										<option value="customer_term" <?php if ($input['link_doc'] == 'customer_term') { echo 'selected="selected"'; } ?>>Customer Termination Form</option>
										<option value="customer_contract" <?php if ($input['link_doc'] == 'customer_contract') { echo 'selected="selected"'; } ?>>Customer Contract</option>
									</select>
								</span>
							</div>
						</div>

					</div>

					<div class="col-lg-12">

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Remarks</span>
								<span class="input-icon input-icon-right" style='display:inline;'>
									<textarea style="width:100%;height:50px;" id="remark" name="remark"><?php echo set_value( 'remark', $input['remark'] ); ?></textarea>
								</span>
							</div>
						</div>

					</div>

				</fieldset>

				<fieldset class='category-border-main'>
					<div class="category-border-main bg-success text-center">
						Attachments
					</div>

					<div class="form-group col-lg-12 col-xs-12" style="z-index:100;">

						<div class="col-xs-12 col-xs-12">Attach Supporting Documents</div>
						
						<div class="col-lg-3 col-xs-12">
							<input multiple="multiple" type="file" id="attach_file_input" data-preview-file-type="text" />
						</div>
				
						<div class="col-lg-9 col-xs-12" id="file-container">
						<?php 
						if( !empty( $attachment ) ){
							foreach( $attachment AS $row => $res ){
						?>
							<div class="col-lg-3 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" onclick="view_attach_doc(<?php echo $row; ?>);">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>"><a style="cursor:pointer;" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"  onclick="change_remark(this);"><?php echo $attachment_text_title; ?></a></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" id="existing_attach" name="existing_attach[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>

										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>

							<input type="file" name="attach_attachment[<?php echo $row; ?>]" class="hide" id="attach_attachment_<?php echo $row; ?>" value="<?php echo $res['local_path']; ?>">

							<input type="hidden" name="attach_attachment_remark[<?php echo $row; ?>]" value="<?php echo $res['remark']; ?>">
						<?php
							}
						}
						?>
						</div>

						<?php include_once( APPPATH . 'views/templates/modal_html_attachment_remark.php'); ?>

					</div>

				</fieldset>

				<div class="col-lg-12">
					<div class="row">
						<div class="button-group d-flex flex-column flex-md-row gap-2 justify-content-start align-items-stretch align-items-md-center" style="padding-top:15px;">
				
							<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
								<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
							</button>
							<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php //echo check_acl_btn('audit_docs','M');?>>
								<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
							</button>
						</div>
					</div>
				</div>

			</form>
		</div>

	</div>

	<div id="clear" style="clear:both;"></div>

</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>
<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>
<script src="<?php echo base_url("js/datepicker/moment.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/ace/elements.fileinput.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>

<script>

$('#upload_doc_form').submit(function(e) {

	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');

	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);

	showLoadingIcon();

	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		success: function (data) {
			if(data['status'] == 'ER'){
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
				hideLoadingIcon();
			} else {
				hideLoadingIcon();
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			console.log(data);
			hideLoadingIcon();
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

$( "#customer_no_display" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "audit_docs/autocomplete_load_customer",
			dataType: "json",
			data: { keyword: request.term },
			success: function( data ) {
				// console.log(data);
				var transformed = $.map(data, function (el) {		
					return {
							label		: (el.customer_no + ' ' + el.name),
							id			: el.customer_no,
							val			: el
					};
				});					
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(thrownError);
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {
		event.preventDefault();
		$( "#customer_no_display" ).val(ui.item.val.customer_no); 
		$( "#customer_no" ).val(ui.item.val.customer_no); 
		$( "#customer_name" ).val(ui.item.val.name); 
	}
});

$( "#bill_no_display" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "audit_docs/autocomplete_load_bill",
			dataType: "json",
			data: { keyword: request.term, customer_no : $('#customer_no').val(), },
			success: function( data ) {
				// console.log(data);
				if (data) {
					var transformed = $.map(data, function (el) {		
						return {
								label		: (el.bill_no + ' ' + el.bill_date_text + ' Bill'),
								id			: el.bill_no,
								val			: el
						};
					});					
					response(transformed);
				} else if ($('#customer_no_display').val() == '') {
					response([{ label: 'Please select a customer.', id: '', realValue: '' }]);
				} else {
					response([{ label: 'No bills found.', id: '', realValue: '' }]);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(thrownError);
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {
		event.preventDefault();
		$( "#bill_no_display" ).val(ui.item.val.bill_no); 
		$( "#bill_no" ).val(ui.item.val.bill_no); 
		$( "#bill_display" ).val(ui.item.val.bill_date_text);
	}
});

</script>