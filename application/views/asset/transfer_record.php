<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('asset/transfer_list');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form autocomplete="off" id="transfer_record" name="transfer_record" method="post" class="filter-form" action="<?php echo $form_action; ?>">

				<input type="hidden" id="transfer_id" name="transfer_id" value="<?php echo set_value('transfer_id', $input['transfer_id']); ?>" />
				<input type="hidden" id="asset_id" name="asset_id" value="<?php echo set_value('asset_id', $input['asset_id']); ?>" />

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Transfer Record
					</div>

					<div class="col-lg-6">

						<div class="input-group">
							<span class="input-group-addon input_group">Asset</span>
							<input type="text" class="form-control" id="asset_display" name="asset_display" value="<?php echo $asset_info['asset_name']; ?>" readonly>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">From</span>
							<select class="col-lg-12" id="from" name="from" style="width: 100%;">
								<option value="">--Please Select--</option>
								<?php
									foreach ($asset_site_list as $val) {
										echo "<option value='" . $val['site_code'] . "' " . 
											set_select('from', $val['site_code'], ($val['site_code']==$input['from'] ? true : false) ) . ">" . 
											$val['site_code']." - ".$val['site_name']."</option>";
									}
								?>
							</select>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">To</span>
							<select class="col-lg-12" id="to" name="to" style="width: 100%;">
								<option value="">--Please Select--</option>
								<?php
									foreach ($asset_site_list as $val) {
										echo "<option value='" . $val['site_code'] . "' " . 
											set_select('to', $val['site_code'], ($val['site_code']==$input['to'] ? true : false) ) . ">" . 
											$val['site_code']." - ".$val['site_name']."</option>";
									}
								?>
							</select>
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Transfer Date</span>
							<input type="text" class="form-control" id="transfer_date" name="transfer_date" value="<?php echo set_value('transfer_date', $input['transfer_date']); ?>" placeholder="Transfer Date">
						</div>

						<div class="input-group">
							<span class="input-group-addon input_group">Description</span>
							<textarea class="form-control" id="description" name="description" rows="3" style="resize: vertical;"><?php echo set_value('description', $input['description']); ?></textarea>
						</div>

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
						<!--<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('asset','D');?> >
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
						</button>-->			
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

	<script src="<?php echo base_url("js/itelco/transfer_record.js?".cssjs_ver()); ?>" ></script>