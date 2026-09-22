<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('sms_scheduler');?>">
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
									Functions
								</legend>
									<input type="hidden" class="form-control" id="scheduler_id" name="scheduler_id" value="<?php echo $input['scheduler_id']; ?>" >
									<div class="col-lg-6">
										<div class="input-group col-lg-12">
											<span class="input-group-btn clear_field_group" style="display:none;">
												<button class="btn btn-danger clear_field " type="button">
													<i class="fa fa-times"></i>
												</button>
											</span>
											<input
													id			="sms_autocomplete"
													value		=""
													type		="text"
													class		="form-control"
													placeholder	=" Populate data with existing sms" >
									</div>
									</div>
							</fieldset>
						</div>
						<div class="col-lg-6">
							<fieldset class='category-border'>
								<legend class="category-border">
									SMS Information
								</legend>
									<div class="col-lg-12">
											<div class="input-group">
												<span class="input-group-addon input_group">Schedule on</span>
												<?php $inputname = 'sms_schedule_on'; ?>
												<input
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text"
													class		="form-control"
													placeholder	="Schedule on" >
											</div>
											<div class="input-group">
												<span class="input-group-addon input_group">Title</span>
												<?php $inputname = 'sms_title'; ?>
												<input
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													value		="<?php echo set_value($inputname, $input[$inputname]); ?>"
													type		="text"
													class		="form-control"
													placeholder	="Title" >
											</div>

											<?php $inputname = 'sms_msg'; ?>
											<?php $sms_msg_val =  set_value($inputname, $input[$inputname]); ?>
											<textarea
													id			="<?php echo $inputname; ?>"
													name		="<?php echo $inputname; ?>"
													class="form-control"
													placeholder="Type your SMS Message here..."
													style="min-width: 100%; height: 390px;"><?php echo $sms_msg_val;?></textarea>
									</div>
							</fieldset>
						</div>
						<div class="col-lg-6">
							<fieldset class='category-border'>
								<legend class="category-border">
									Recipient By 
									<input type="radio" name="send_by" value="building" <?php echo $input['is_building'] == 1 ? 'CHECKED' : '' ;  ?> /> Building 
									<input type="radio" name="send_by" value="individual" <?php echo $input['is_building'] == 0 ? 'CHECKED' : '' ;  ?> /> Individual 
								</legend>
								
								<div class="col-lg-12" id="individual_form">
								
								<input id="customer_sms_autocomplete" name="customer_sms_autocomplete" 
									   type="text" value="" autocomplete='off'
									   style="width:100%;margin-bottom:10px;"
									   placeholder="Search by account number / name / NRIC / passport" />
								
								<?php $inputname = 'recipient_mobile_num'; ?>
								<?php //$email_msg_val =  set_value($inputname, $input[$inputname]); ?>
								<textarea
										id			="<?php echo $inputname; ?>"
										name		="<?php echo $inputname; ?>"
										class="form-control"
										placeholder="Insert your H/P number here, use ; to separate multiple numbers"
										style="min-width: 100%;height:395px;"><?php echo $input['sms_to'];?></textarea>
								</div>
								
								<div class="col-lg-12" id="building_form">
									<?php
									//~ _debug_array($building_opt);
									?>
									

									<!--
									<select multiple="multiple" id="sms_building"
									name="sms_building[]" class="select2 tag-input-style"
									data-placeholder="Click to Choose..." size="10" >
									-->
									<select multiple="" id="sms_building" name="sms_building[]" class="select2 tag-input-style" size="10">
										<?php foreach( $building_opt as $opt => $opt_val):?>
											<option value="<?php echo $opt; ?>" <?php echo set_select('sms_building[]',$opt , (in_array( $opt,$input['sms_building'])), 1); ?> ><?php echo $opt_val; ?></option>
										<?php endforeach; ?>
								   </select>
								   
								  <div class="category-border-main bg-success text-center" >Where Customer</div>
								  <div class="col-lg-6" style="display:block;">

									<?php $inputname = 'sms_cust_cat'; ?>
									<input
										type		="hidden"
										id			="<?php echo $inputname; ?>"
										name		="<?php echo $inputname; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">

									<span style="font-weight:bold;font-size:15px;">Category</span><br>
									<?php
									if(!empty($cust_cat))
									{
										foreach ($cust_cat as $cust_cat_key => $cust_cat_val){
											echo '<input type="checkbox" style="margin-top:0px" class="cust_cat" data-id="'.$cust_cat[$cust_cat_key]['category_code'].'">&nbsp;';

											//~ echo form_checkbox('cust_cat['.$cust_cat[$cust_cat_key]['category_code'].']', '', set_checkbox('cust_cat', ''));
											echo $cust_cat[$cust_cat_key]['name'];
											echo '<br>';
										}
									}
									?>
								  </div>
								  <div class="col-lg-6">
									<?php $inputname = 'sms_cust_status'; ?>
									<input
										type		="hidden"
										id			="<?php echo $inputname; ?>"
										name		="<?php echo $inputname; ?>"
										value		="<?php echo set_value($inputname, $input[$inputname]); ?>">
									  <span style="font-weight:bold;font-size:15px;">Status</span><br>
									<?php
									if(!empty($cust_status)){
										foreach ($cust_status as $cust_status_key => $cust_status_val){
											//~ echo '<input type="checkbox" style="margin-top:0px" name="cust_status['.$cust_status[$cust_status_key]['status_code'].']">&nbsp;';
											echo '<input type="checkbox" style="margin-top:0px" class="cust_status" data-id="'.$cust_status[$cust_status_key]['status_code'].'">&nbsp;';
											echo $cust_status[$cust_status_key]['name'];
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
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('sms_scheduler');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php //echo $input['btn_delete']?>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i> Delete
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div id="clear" style="clear:both;"></div>
</div>
<script>
   var disable_input = '<?php echo (empty($disabled_input))?'0':$disabled_input; ?>';
   var filter_phone  = $('#sms_filter_phone').prop('checked');
   var base_url  	 = '<?php echo base_url('sms_scheduler'); ?>';
   var is_edit  	 = '<?php echo $is_edit; ?>';

	$( document ).ready(function() {
		init();
		showFormType();
		$('#sms_autocomplete').change(function(){
			// alert("The text has been changed.");
			if($('#sms_autocomplete').val() != ''){
				$('.clear_field_group').show();
			}else{
				$('.clear_field_group').hide();
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
<script src="<?php echo base_url("js/itelco/sms_scheduler.js?").cssjs_ver(); ?>" ></script>
<script src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?").cssjs_ver(); ?>" ></script>
