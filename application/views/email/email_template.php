<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('email/email_template');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="dealer_detail" name="dealer_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>
					<div class="category-border-main bg-success text-center hide" ></div>
					<div class="row">
						<div class="col-lg-12">
							<fieldset class='category-border'>
								<legend class="category-border">
									Templates List
								</legend>
								<div class="col-lg-6">
									<div class="input-group col-lg-12">
										<span class="input-group-btn clear_field_group" style="display:none;">
											<button class="btn btn-danger clear_field " type="button">
												<i class="fa fa-times"></i>
											</button>
										</span>
										<select name="template_list" id="template_list" class="form-control" >
											<option value="">-- SELECT --</option>
											<?php 
												foreach( $template_list AS $temp ){
													echo '<option value="'.$temp['template_id'].'">'.($temp['is_default'] == 1 ? '* ' : '').$temp['template_name'].'</option>';
												} 
											?>
										</select> * is default template.
									</div>
								</div>
							</fieldset>
						</div>
						<div class="col-lg-12">
							<fieldset class='category-border'>
								<legend class="category-border">
									Email Information
								</legend>
									<div class="col-lg-12">
											<div class="input-group">
												<span class="input-group-addon input_group">Template Name</span>
												<?php $inputname = 'template_name'; ?>
												<input
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text"
													class		="form-control"
													<?php echo $input['is_default'] == 1 ? 'READONLY' : ''; ?>
													placeholder	="Template Name" />
											</div>
										
											<div class="input-group">
												<span class="input-group-addon input_group">Title</span>
												<?php $inputname = 'email_title'; ?>
												<input
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text"
													class		="form-control"
													placeholder	="Title" />
													
												<input type="hidden" name="template_id" id="template_id" value="<?php echo $input['template_id']; ?>" />
												
												<input type="hidden" name="is_default" id="is_default" value="<?php echo $input['is_default']; ?>" />
											</div>

											<?php $inputname = 'email_msg'; ?>
											<?php $email_msg_val =  set_value($inputname, $input[$inputname]); ?>
											<textarea
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													class="form-control"
													placeholder="Type your email here..."
													style="min-width: 100%;height:315px;"><?php echo $email_msg_val;?></textarea>
									</div>
							</fieldset>
						</div>
				</fieldset>
				<div class="col-md-12">
					<div>
						<!-- <button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('sms_scheduler');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel
						</button> -->
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('dealer','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php //echo $input['btn_delete']?> <?php echo check_acl_btn('dealer','D');?>>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i> Delete
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div id="clear" style="clear:both;"></div>
</div>
<script type="text/JavaScript" src="<?php echo base_url("js/tinymce/js/tinymce/tinymce.min.js?".cssjs_ver()); ?>"></script>
<script type="text/JavaScript" src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?").cssjs_ver(); ?>" ></script>
<script>
   var base_url  	 = '<?php echo base_url('email'); ?>';

	$( document ).ready(function() {
				
		tinymce.init({
			selector: '#email_msg',
			menubar: true,
			statusbar: true,
			force_p_newlines : false,
			force_br_newlines : false,
			forced_root_block : '',
			plugins: "code table paste image table",
		});
		
		$(document).keypress(
			function(event){
			 if (event.which == '13') {
				event.preventDefault();
			  }
		});
		
		$( document ).on( "change", "#template_list", function(){
			
			$.ajax({
				url: base_url + "/ajax_get_email_template",
				type: "post",
				dataType:"json",
				data: { template_id: $("#template_list").val() },
				success: function (data)
				{
					
					console.log( data ) ;
					$("#template_id").val( data['template_id'] );
					$("#template_name").val( data['template_name'] );
					$("#is_default").val( data['is_default'] );
					if( $("#is_default").val() == 1 ){
						$("#template_name").attr("readonly",true);
						$("#btDelete").attr("disabled",true);
					}else{
						$("#template_name").attr("readonly",false);
						$("#btDelete").attr("disabled",false);
					}
					$("#email_title").val( data['email_title'] );
					$(tinymce.get('email_msg').getBody()).html( data['email_msg'] );
				},
				error: function ()
				{
				}
			});
			
		});
		
		
	});

</script>
