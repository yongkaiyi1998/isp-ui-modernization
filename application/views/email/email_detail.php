<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('email/schedule_list');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="email_detail" name="email_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data" >
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
										<select name="template_list" id="template_list" class="form-control" onchange="get_template_name()">
											<option value="">-- SELECT --</option>
											<?php 
												foreach( $template_list AS $temp ){
													echo '<option value="'.$temp['template_id'].'">'.($temp['is_default'] == 1 ? '* ' : '').$temp['template_name'].'</option>';
												} 
											?>
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					
					<div class="row">
						
						<div class="col-lg-6">
							<fieldset class='category-border'>
								<legend class="category-border">
									Email Information
								</legend>
									<div class="col-lg-12">
											<div class="input-group">
												<span class="input-group-addon input_group">Schedule on<span class="red">*</span></span>
												<?php $inputname = 'email_schedule_on'; ?>
												<input
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text"
													class		="form-control"
													placeholder	="Schedule on" autocomplete="off">
												<input type="hidden" name="scheduler_id" id="scheduler_id" value="<?php echo $input['scheduler_id']; ?>" />
													
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
													placeholder	="Title" >
											</div>

											<div class="input-group">
												<span class="input-group-addon input_group">Attachment</span>
												<?php $inputname = 'email_attachment'; ?>

													
												<?php
													if( $input['email_attachment'] != '' && file_exists( $this->config->item('upload_path').'/temp/pdf/' . $input['email_attachment'] ) ){
														echo '<a style="line-height:25px;" target="_blank" href="'.$this->config->item('upload_url').'/temp/pdf/' . $input['email_attachment'].'"> Download here </a>';
														
														echo '<a style="line-height:25px;margin-left:50px;" class="remove_attachment">Remove Attachment</a>';
													}else{
														?>
														<input	id			="<?php echo $inputname; ?>"
																name		="<?php echo $inputname; ?>"
																value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
																type		="file"
																placeholder	="Title" ><!--<span class="red">*Leave blank if want system to attach relevant document</span>-->
														<?php
													}
												?>
													
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
						<div class="col-lg-6">
							<fieldset class='category-border'>
								<legend class="category-border">
									<?php $send_by =  set_value('send_by', ''); ?>
									<?php 
										$is_building = $input['is_building'];
										$is_individual = $input['is_individual'];
										$is_area = $input['is_area'];
										$is_all = $input['is_all'];
										if (!empty($send_by)) {
											if ($send_by == 'building') {
												$is_building = 1;
												$is_individual = 0;
												$is_area = 0;
												$is_all = 0;
											} else if ($send_by == 'individual') {
												$is_building = 0;
												$is_individual = 1;
												$is_area = 0;
												$is_all = 0;
											} else if ($send_by == 'area') {
												$is_building = 0;
												$is_individual = 0;
												$is_area = 1;
												$is_all = 0;
											} else if ($send_by == 'all') {
												$is_building = 0;
												$is_individual = 0;
												$is_area = 0;
												$is_all = 1;
											}
										}
									?>
									Recipient By 
									<input type="radio" id="building" name="send_by" value="building" <?php echo $is_building == 1 ? 'CHECKED' : '' ;  ?> /> Building 
									<input type="radio" id="individual"  name="send_by" value="individual" <?php echo $is_individual == 1 ? 'CHECKED' : '' ;  ?> /> Individual 
									<input type="radio" id="area"  name="send_by" value="area" <?php echo $is_area == 1 ? 'CHECKED' : '' ;  ?> /> Area 
									<input type="radio" id="all"  name="send_by" value="all" <?php echo $is_all == 1 ? 'CHECKED' : '' ;  ?> /> All 
								</legend>
								<div class="col-lg-12" id="individual_form">
								
								<input id="customer_email_autocomplete" name="customer_email_autocomplete" 
									   type="text" value="" autocomplete='off'
									   style="width:100%;margin-bottom:10px;"
									   placeholder="Search by account number / name / NRIC / passport" />

								<span id="customer_no_info" class="red" style="font-size:12px;">This email will send to this account no: <input class="form-control" type="text" id="customer_no" name="customer_no" autocomplete="off" readonly /><br /></span>
								
								<?php $inputname = 'recipient_emails'; ?>
								<?php $email_msg_val =  set_value($inputname, ''); ?>
								<textarea
										id			="<?php echo $inputname; ?>"
										name		="<?php echo $inputname; ?>"
										class="form-control"
										placeholder="Insert your email here, use ; to separate multiple emails"
										style="min-width: 100%;height:395px;"><?php if (!empty($email_msg_val)) { echo $email_msg_val; } else { echo $input['email_to']; } ?></textarea>
								</div>
								
								<div class="col-lg-12" id="building_form">
									<div class="category-border-main bg-success text-center" >Sent To Customer in Building</div>

									<!--
									<select multiple="multiple" id="sms_building"
									name="sms_building[]" class="select2 tag-input-style"
									data-placeholder="Click to Choose..." size="10" >
									-->
									<select multiple="multiple" id="email_building" name="email_building[]" class="select2 tag-input-style">
										<?php foreach( $building_opt as $opt => $opt_val):?>
											<option value="<?php echo $opt; ?>" <?php echo set_select('email_building[]',$opt , (in_array( $opt,$input['email_building'])), 1); ?> ><?php echo $opt_val; ?></option>
										<?php endforeach; ?>
								   </select>
								  <div class="category-border-main bg-success text-center" >Where Customer</div>
								  <span class="red">Ignore this if the email is scheduled to be sent to all customers.</span><br /><br />
								  <div class="col-lg-6">

									<?php $inputname = 'email_cust_cat'; ?>
									<input
										type		="hidden"
										id			="email_cust_cat_building"
										name		="<?php echo $inputname . '_building'; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">

									<span style="font-weight:bold;font-size:15px;">Category</span><br>
									<?php
									if(!empty($cust_cat))
									{
										foreach ($cust_cat as $cust_cat_key => $cust_cat_val){
											echo '<input type="checkbox" style="margin-top:0px" class="cust_cat_building" data-id="'.$cust_cat[$cust_cat_key]['category_code'].'">&nbsp;';

											//~ echo form_checkbox('cust_cat['.$cust_cat[$cust_cat_key]['category_code'].']', '', set_checkbox('cust_cat', ''));
											echo $cust_cat[$cust_cat_key]['name'];
											echo '<br>';
										}
									}
									?>
								  </div>
								  <div class="col-lg-6">
									<?php $inputname = 'email_cust_status'; ?>
									<input
										type		="hidden"
										id			="email_cust_status_building"
										name		="<?php echo $inputname . '_building'; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">
									  <span style="font-weight:bold;font-size:15px;">Status</span><br>
									<?php
									if(!empty($cust_status)){
										foreach ($cust_status as $cust_status_key => $cust_status_val){

											echo '<input type="checkbox" style="margin-top:0px" class="cust_status_building" data-id="'.$cust_status_key.'">&nbsp;';
											echo $cust_status_val;
											echo '<br>';
										}
									}
									?>
								  </div>
								</div>

								<div class="col-lg-12" id="area_form">
									<div class="category-border-main bg-success text-center" >Sent To Customer in Area</div>

									<select multiple="multiple" id="email_area" name="email_area[]" class="select2 tag-input-style">
										<?php foreach( $area_opt as $opt => $opt_val):?>
											<option value="<?php echo $opt; ?>" <?php echo set_select('email_area[]',$opt , (in_array( $opt,$input['email_area'])), 1); ?> ><?php echo $opt_val; ?></option>
										<?php endforeach; ?>
								   </select>
								  <div class="category-border-main bg-success text-center" >Where Customer</div>
								  <span class="red">Ignore this if the email is scheduled to be sent to all customers.</span><br /><br />
								  <div class="col-lg-6">

									<?php $inputname = 'email_cust_cat'; ?>
									<input
										type		="hidden"
										id			="email_cust_cat_area"
										name		="<?php echo $inputname . '_area'; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">

									<span style="font-weight:bold;font-size:15px;">Category</span><br>
									<?php
									if(!empty($cust_cat))
									{
										foreach ($cust_cat as $cust_cat_key => $cust_cat_val){
											echo '<input type="checkbox" style="margin-top:0px" class="cust_cat_area" data-id="'.$cust_cat[$cust_cat_key]['category_code'].'">&nbsp;';

											//~ echo form_checkbox('cust_cat['.$cust_cat[$cust_cat_key]['category_code'].']', '', set_checkbox('cust_cat', ''));
											echo $cust_cat[$cust_cat_key]['name'];
											echo '<br>';
										}
									}
									?>
								  </div>
								  <div class="col-lg-6">
									<?php $inputname = 'email_cust_status'; ?>
									<input
										type		="hidden"
										id			="email_cust_status_area"
										name		="<?php echo $inputname . '_area'; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">
									  <span style="font-weight:bold;font-size:15px;">Status</span><br>
									<?php
									if(!empty($cust_status)){
										foreach ($cust_status as $cust_status_key => $cust_status_val){

											echo '<input type="checkbox" style="margin-top:0px" class="cust_status_area" data-id="'.$cust_status_key.'">&nbsp;';
											echo $cust_status_val;
											echo '<br>';
										}
									}
									?>
								  </div>
								</div>

								<div class="col-lg-12" id="all_form">
									<div class="category-border-main bg-success text-center" >Sent To All Customer</div>

								  <div class="category-border-main bg-success text-center" >Where Customer</div>
								  <span class="red">Ignore this if the email is scheduled to be sent to all customers.</span><br /><br />
								  <div class="col-lg-6">

									<?php $inputname = 'email_cust_cat'; ?>
									<input
										type		="hidden"
										id			="email_cust_cat_all"
										name		="<?php echo $inputname . '_all'; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">

									<span style="font-weight:bold;font-size:15px;">Category</span><br>
									<?php
									if(!empty($cust_cat))
									{
										foreach ($cust_cat as $cust_cat_key => $cust_cat_val){
											echo '<input type="checkbox" style="margin-top:0px" class="cust_cat_all" data-id="'.$cust_cat[$cust_cat_key]['category_code'].'">&nbsp;';

											//~ echo form_checkbox('cust_cat['.$cust_cat[$cust_cat_key]['category_code'].']', '', set_checkbox('cust_cat', ''));
											echo $cust_cat[$cust_cat_key]['name'];
											echo '<br>';
										}
									}
									?>
								  </div>
								  <div class="col-lg-6">
									<?php $inputname = 'email_cust_status'; ?>
									<input
										type		="hidden"
										id			="email_cust_status_all"
										name		="<?php echo $inputname . '_all'; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">
									  <span style="font-weight:bold;font-size:15px;">Status</span><br>
									<?php
									if(!empty($cust_status)){
										foreach ($cust_status as $cust_status_key => $cust_status_val){

											echo '<input type="checkbox" style="margin-top:0px" class="cust_status_all" data-id="'.$cust_status_key.'">&nbsp;';
											echo $cust_status_val;
											echo '<br>';
										}
									}
									?>
								  </div>
								</div>
							</fieldset>
						</div>
						
					</div>
				</fieldset>

				<div class="col-md-12">
					<div>
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('email/schedule_list');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel
						</button>
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
<div class="modal fade" id="confirm_schedule_auto_billing" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel">Confirm Sending Scheduled Auto Billing Email ?</h3>
            </div>
      
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-xs-12">
                    	<h5>By scheduling the auto billing email, the records will create based on the number of customer.<br /><br />Attachment is not allowed for this email.<br />The system will auto attach the billing to the customer.</h5>
                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <button id="confirm_schedule_auto_billing_close" type="button" class="btn btn-information"
                    role="button" aria-disabled="false" style="margin:0.2em;" data-dismiss="modal">
                    No
                </button>

                <button type="button" class="btn btn-success" id="auto_billing_submit_button"
                    role="button" aria-disabled="false" style="margin:0.2em;" onclick="submitForm()">
                    Yes
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/JavaScript" src="<?php echo base_url("js/tinymce/js/tinymce/tinymce.min.js?".cssjs_ver()); ?>"></script>
<script type="text/JavaScript" src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?").cssjs_ver(); ?>" ></script>
<script>
   var disable_input = '<?php echo (empty($disabled_input))?'0':$disabled_input; ?>';
   var filter_phone  = $('#sms_filter_phone').prop('checked');
   var base_url  	 = '<?php echo base_url('email'); ?>';
   var is_edit  	 = '<?php echo $is_edit; ?>';
   var send_by  	 = '<?php echo $by; ?>';

	$( document ).ready(function() {
		init();
		showFormType();
		$('#email_autocomplete').change(function(){
			// alert("The text has been changed.");
			if($('#email_autocomplete').val() != ''){
				$('.clear_field_group').show();
			}else{
				$('.clear_field_group').hide();
			}
		});
		
		tinymce.init({
			selector: '#email_msg',
			menubar: true,
			statusbar: true,
			force_p_newlines : false,
			force_br_newlines : false,
			forced_root_block : '',
			plugins: "code table paste ",
		});
		
		$(document).keypress(
			function(event){
			 if (event.which == '13') {
				event.preventDefault();
			  }
		});
		
		$('.select2').css('width','100%').select2({allowClear:true})

	});
	
	// jQuery(function($){
	// 	$('.select2').css('width','200px').select2({allowClear:true})
	// 	$('#select2-multiple-style .btn').on('click', function(e){
	// 		var target = $(this).find('input[type=radio]');
	// 		var which = parseInt(target.val());
	// 		if(which == 2) $('.select2').addClass('tag-input-style');
	// 		else $('.select2').removeClass('tag-input-style');
	// 	});
	// });
</script>
<script src="<?php echo base_url("js/itelco/email_scheduler.js?").cssjs_ver(); ?>" ></script>
<script>
$( document ).on( "change", "#template_list", function(){
	$.ajax({
		url: base_url + "/ajax_get_email_template",
		type: "post",
		dataType:"json",
		data: { template_id: $("#template_list").val() },
		success: function (data)
		{
			$("#email_title").val( data['email_title'] );
			$(tinymce.get('email_msg').getBody()).html( data['email_msg'] );
		},
		error: function ()
		{
		}
	});	
});

$( document ).on( "click", ".remove_attachment", function(){
	if( confirm("Remove attachment ?") ){
		window.location.href = "<?php echo base_url("email/remove_attachment") . "/" . $input['scheduler_id'] ; ?>" ;
	}
});

</script>


