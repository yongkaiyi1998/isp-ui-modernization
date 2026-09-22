<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('settings/bill_type_management');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="bill_type_detail" name="bill_type_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<input type='hidden' id='bill_type_id' name='bill_type_id' value='<?php echo $input['bill_type_id'] ?>' />
				<fieldset class='category-border-main'>	
					<div class="col-lg-8">							
						<div class="input-group">
							<span class="input-group-addon input_group">Bill Type Name</span>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Name">
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group">Adjust Type</span>
							<select class="col-lg-6" id="is_debit" name="is_debit" style="width: 100%;">
								<option value=''>-</option>
								<option value='0' <?php echo set_select('is_debit', '0', ($input['is_debit']=='0' ? true : false) ) ?> >Credit</option>
								<option value='1' <?php echo set_select('is_debit', '1', ($input['is_debit']=='1' ? true : false) ) ?> >Debit</option>
							</select>
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group">Tax Code</span>
							<select class="col-lg-6" id="tax_code" name="tax_code" style="width: 100%;">
								<option value="" <?php echo set_select('tax_code', '', ($input['tax_code'] == '' ? true : false)); ?> >-</option>
								<?php
									foreach ($tax_code_list as $val) {
										echo "<option value='" . $val['code'] . "' " . 
											set_select('tax_code', $val['code'], ($val['code'] == $input['tax_code'])) . ">" . 
											$val['code']."</option>";
									}
								?>
							</select>
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group">Exclude from <br/>Bill Calculation</span>
							<select class="col-lg-6" style="height:34px; width: 100%;" id="exclude_from_bill_calculation" name="exclude_from_bill_calculation">
								<option value="0" <?php echo set_select('exclude_from_bill_calculation', '0', $input['exclude_from_bill_calculation'] == 0); ?>>No</option>
								<option value="1" <?php echo set_select('exclude_from_bill_calculation', '1', $input['exclude_from_bill_calculation'] == 1); ?>>Yes</option>
							</select>
						</div>
						<div class="input-group">
							<span class="red">
								* The amount still will be displayed on bills, <br/>
								but the amounts wont be calculated into balance<br/>
								Example: Deposit Return
							</span>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">CLASS Code</span>
							<select class="select2" multiple="multiple" id="class_codes" name="class_codes[]">
								<?php
									$sel_class_codes = set_value('class_codes[]', $input['class_codes']);
									$sel_class_codes_arr = explode(",", $sel_class_codes);
									foreach ($class_code_list as $code => $val) {
										$selected = '';
										if (in_array($code, $sel_class_codes_arr)) {
											$selected = 'selected="selected"';
										}
										echo "<option value='" . $code . "' ".$selected.">".$code.' - '.$val."</option>";
									}
								?>
							</select>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Tax Type Code</span>
							<select class="col-lg-6" id="tax_type" name="tax_type" style="width: 100%;">
								<option value=''>--Select if Applicable--</option>
								<?php
									$sel_tax_type = set_value('tax_type', $input['tax_type']);
									foreach ($tax_type_list as $code => $val) {
										$selected = '';
										if ($sel_tax_type == $code) {
											$selected = 'selected="selected"';
										}
										echo "<option value='" . $code . "' ".$selected.">".$code.' - '.$val."</option>";
									}
								?>
							</select>
						</div>

					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
							Cancel
						</button>
						
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('bill_type','M');?> >
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>		
							Save					
						</button>
						
						<?php if($is_lock != 1) { ?>
							<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('bill_type','D');?> >
								<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
							</button>			
						<?php } ?>	
					</div>					
				</div>
			</form>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>
</div>

<link rel="stylesheet" href="<?php echo base_url('css/select2/bootstrap-select.css'); ?>" />
<link rel="stylesheet" href="<?php echo base_url('css/select2/select2.css'); ?>" />
<script src="<?php echo base_url('js/select2/bootstrap-select.js'); ?>"></script>
<script src="<?php echo base_url('js/select2/select2.js'); ?>"></script> 
<script>

    jQuery(function($){
        //select2
        $('.select2').css('width','100%').select2({allowClear:true});
    });

	let clickedButton = '';

	$('.button-group button[type=submit]').on('click', function () {
		clickedButton = $(this).attr('name');
	});

	$('#bill_type_detail').submit(function (e) {
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