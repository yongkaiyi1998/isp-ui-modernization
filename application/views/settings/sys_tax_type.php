<?php echo flash_data_helper($msg); ?>
<?php echo $fm_open; ?>
	<div class="col-lg-3 mobile-tax-type">		
		<?php if(!empty($inputs['main'])) foreach ($inputs['main'] as $key => $input) echo $input; ?>
		<br />
	</div>
	<div class="col-lg-12">&nbsp;</div>
	<div class="col-lg-12">
		<div>
			<button name="processtype[add]" type="button" class="btn btn-info" id="processtype[add]" onclick="addNewTax();" title="Add New Tax">
				<i class="menu-icon fa fa-plus white" data-toggle="tooltip" title=""></i> Add
			</button>
			<?php if(!empty($buttons)) foreach ($buttons as $key => $button) echo $button.'&nbsp;'; ?>
		</div>					
	</div>
	<div class="col-lg-12">&nbsp;</div>
	
	<div class="col-md-6 col-lg-3" id="new_tax"></div>
	
	<div id="clear" style="clear:both;"></div>
	<div id="popupDetail">
		<div id="popupDetailStd" onclick="disablePopup();">
			<div id="popupContent">
				&nbsp;
			</div>
		</div>
	</div>
	<div id="backgroundPopup" onclick="hide_popup();"></div>

	<script> 
		function init() 
		{		
			url_delete_button 	="";
			url_cancel_button	="<?php echo base_url('settings/index');?>";		
			url_add_button 		="";	
			
			//~ $('.delete_button').show();
			//~ $('.add_button').show();
			$('.cancel_button').show();
		}

		function addNewTax() {
			let html = `
			<div class="new_tax_block">
				<div class="row">
					<div class="col-lg-10">
						<div class="input-group">
							<span class="input-group-addon input_group" style="text-align: center;">New Tax Code</span>
							<input type="text" name="new_tax_code[]" class="form-control text-right" />
						</div>
						<div class="input-group">
							<span class="input-group-addon input_group" style="text-align: center;">Percent</span>
							<input type="text" name="new_tax_percent[]" class="form-control text-right" placeholder="0.0" value="0.0" step="0.1" />
						</div>
					</div>
					<div class="col-lg-2">
						<button type="button" class="btn btn-danger remove_tax_block">
							<i class="fa fa-trash"></i>
						</button>
					</div>
				</div>
				<br />
			</div>`;
			$('#new_tax').append(html);
		}

		$(document).on('click', '.remove_tax_block', function () {
			$(this).closest('.new_tax_block').remove();
		});
	</script>
	<script src="<?php echo base_url("js/itelco/settings.js?".cssjs_ver()); ?>" ></script>
<?php echo $fm_close; ?>
