<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('advertisement');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="advertisement_detail" name="advertisement_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">

				<input type="hidden" id="already_uploaded" name="already_uploaded" value="<?php echo set_value('file_path', $input['file_path']); ?>">

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						ADVERTISEMENT
					</div>		

					<input type="hidden" id="id" name="id" value="<?php echo set_value('id', $input['id']); ?>" />

					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Line No.</span>
									<input type="text" class="form-control" id="lineno" name="lineno" value="<?php echo set_value('lineno', $input['lineno']); ?>" placeholder="Line No." onkeypress="return /[0-9]/i.test(event.key)">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Name</span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Name" >
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Start Date</span>
									<input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo set_value('start_date', $input['start_date']); ?>" placeholder="Start Date" >
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">End Date</span>
									<input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo set_value('end_date', $input['end_date']); ?>" placeholder="End Date" >
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Upload</span>
				    					<input type="file" class="ace-file-input-input" id="file_path" name="file_path" data-preview-id="1" onchange="startPreview(event)" accept="image/*" >
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group_150">Link</span>
									<input type="text" class="form-control" id="link" name="link" value="<?php echo set_value('link', $input['link']); ?>" placeholder="https://xxx" >
								</div>
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span>
										<?php $inputname = 'is_active'; ?>	
										<input 
											id			="<?php echo $inputname; ?>" 
											name		="<?php echo $inputname; ?>" 
											value		="1"
											<?php echo set_checkbox($inputname, $input[$inputname], ($input[$inputname] == 1? true : false) ); ?>
											type		="checkbox" 
											class		=""  
											placeholder	="" >
											Active?
									</span>
								</div>
							</div>
						</div>

					</div>

				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						PREVIEW
					</div>	

					<div class="col-lg-12">
						<div class="row">
							<div id="preview_area" style="margin-left: 12px;"></div>
						</div>
					</div>

				</fieldset>

				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('config','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('config','D');?>>
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
<script type="text/javascript">
	var upload_path = '<?php echo $this->config->item('upload_url'); ?>';
</script>
<script src="<?php echo base_url("js/itelco/advertisement.js?".cssjs_ver()); ?>" ></script>

</div>