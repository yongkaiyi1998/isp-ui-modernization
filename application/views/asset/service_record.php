<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('asset/service_list');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form autocomplete="off" id="service_record" name="service_record" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">

				<input type="hidden" id="maint_id" name="maint_id" value="<?php echo set_value('maint_id', $input['maint_id']); ?>" />
				<input type="hidden" id="asset_id" name="asset_id" value="<?php echo set_value('asset_id', $input['asset_id']); ?>" />

				<input id="temp_id" name="temp_id" type="hidden" value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Service Record
					</div>

					<div class="col-lg-6">

						<div class="input-group">
							<span class="input-group-addon input_group">Asset</span>
							<input type="text" class="form-control" id="asset_display" name="asset_display" value="<?php echo $asset_info['asset_name']; ?>" readonly>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Vendor</span>
							<input type="text" class="form-control" id="vendor" name="vendor" value="<?php echo set_value('vendor', $input['vendor']); ?>" placeholder="Vendor Name">
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Request Date</span>
							<input type="text" class="form-control" id="request_date" name="request_date" value="<?php echo set_value('request_date', $input['request_date']); ?>" placeholder="Request Date" autocomplete="off">
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Service Date</span>
							<input type="text" class="form-control" id="service_date_time" name="service_date_time" value="<?php echo set_value('service_date_time', $input['service_date_time']); ?>" placeholder="Service Date" autocomplete="off">
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Maint. Cost</span>
							<input type="text" class="form-control" id="maint_cost" name="maint_cost" value="<?php echo set_value('maint_cost', $input['maint_cost']); ?>" placeholder="Maint. Cost">
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Status</span>
							<select class="col-lg-12" id="status" name="status" style="width: 100%;">
								<option value="P" <?php echo set_select('status', '', ($input['status'] == 'P' ? true : false) ); ?>>Pending</option>
								<option value="D" <?php echo set_select('status', '', ($input['status'] == 'D' ? true : false) ); ?>>Done</option>
							</select>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Reference</span>
							<input type="text" class="form-control" id="doc_ref" name="doc_ref" value="<?php echo set_value('doc_ref', $input['doc_ref']); ?>" placeholder="Reference#">
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Description</span>
							<textarea class="form-control" id="description" name="description" rows="3" style="resize: vertical;"><?php echo set_value('description', $input['description']); ?></textarea>
						</div>

					</div>

				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						ATTACHMENTS
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

				<div class="col-md-12">

					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('asset','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('asset','D');?> >
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

<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>

	<script src="<?php echo base_url("js/itelco/service_record.js?".cssjs_ver()); ?>" ></script>
	<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>