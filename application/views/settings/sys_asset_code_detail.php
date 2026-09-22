<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('settings/asset_code_management');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<div class="col-lg-6">
			<?php echo flash_data_helper($msg); ?>
			<form id="asset_code_detail" name="asset_code_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<input type='hidden' id='asset_code_idx' name='asset_code_idx' value='<?php echo $input['asset_code_idx'] ?>' />
				<fieldset class='category-border-main'>	
					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Asset Code</span>
							<input type="text" class="form-control" id="asset_code" name="asset_code" value="<?php echo set_value('asset_code', $input['asset_code']); ?>" placeholder="Code">
						</div>
					</div>
					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Name</span>
							<input type="text" class="form-control" id="short_name" name="short_name" value="<?php echo set_value('short_name', $input['short_name']); ?>" placeholder="Name">
						</div>
					</div>

					<div class="col-lg-12">	
						<div class="input-group">
							<span class="input-group-addon input_group">Category</span>
							<select class="col-lg-12" id="category_idx" name="category_idx">
								<option value="" <?php echo set_select('category_idx', '', ($input['category_idx'] == '' ? true : false) ); ?> >-</option>
								<?php
									foreach ($asset_category_list as $val) {
										echo "<option value='" . $val['category_idx'] . "' " . 
											set_select('category_idx', $val['category_idx'], ($val['category_idx']==$input['category_idx'] ? true : false) ) . ">" . 
											$val['category_code']." ".$val['category_name']."</option>";
									}
								?>
							</select>
						</div>

					</div>

					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Barcode</span>
							<input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo set_value('barcode', $input['barcode']); ?>" placeholder="Barcode">
						</div>
					</div>

					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Brand</span>
							<input type="text" class="form-control" id="brand" name="brand" value="<?php echo set_value('brand', $input['brand']); ?>" placeholder="Brand">
						</div>
					</div>

					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Model No.</span>
							<input type="text" class="form-control" id="model_no" name="model_no" value="<?php echo set_value('model_no', $input['model_no']); ?>" placeholder="Model No.">
						</div>
					</div>

					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Manufacturer</span>
							<input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?php echo set_value('manufacturer', $input['manufacturer']); ?>" placeholder="Manufacturer">
						</div>
					</div>

					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Country of Origin</span>
							<input type="text" class="form-control" id="origin_country" name="origin_country" value="<?php echo set_value('origin_country', $input['origin_country']); ?>" placeholder="Country of Origin">
						</div>
					</div>

				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
					<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Cancel
					</button>
					
					<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('asset','M');?> >
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>		
						Save					
					</button>
					
					<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('asset','D');?> >
						<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
					</button>					
					</div>					
				</div>
			</form>
			</div>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>
</div>

<script>
	let clickedButton = '';

	$('.button-group button[type=submit]').on('click', function () {
		clickedButton = $(this).attr('name');
	});

	$('#asset_code_detail').submit(function (e) {
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
