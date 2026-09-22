<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('building');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="building_detail" name="building_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						BUILDING
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								General Information
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Building No</span>
									<input type="text" class="form-control" id="building_no" name="building_no" value="<?php echo set_value('building_no', $input['building_no']); ?>" placeholder="Building No" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Name<span class="red">*</span></span>
									<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $input['name']); ?>" placeholder="Building Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Total Units</span>
									<input type="text" class="form-control" id="total_unit" name="total_unit" value="<?php echo set_value('total_unit', $input['total_unit']); ?>" placeholder="Total Units">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Status</span>
									<select class="col-lg-12" id="status" name="status" style="width: 100%;">
										<option value="a" <?php echo set_select('status', 'a', ($input['status'] == 'a' ? true : false) ); ?> >Active</option>
										<option value="i" <?php echo set_select('status', 'i', ($input['status'] == 'i' ? true : false) ); ?> >Inactive</option>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">PPPoE</span>
									<input type="checkbox" id="pppoe" name="pppoe" value="1" <?php echo set_checkbox('pppoe', '1', ($input['pppoe']==1? true:false) ); ?> style="margin-left: 0.5rem;" />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Private</span>
									<input type="checkbox" id="private" name="private" value="1" <?php echo set_checkbox('private', '1', ($input['private']==1? true:false) ); ?> style="margin-left: 0.5rem;" />
								</div>
							</div>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Agent</span>
									<select class="col-lg-12" id="dealer" name="dealer" style="width: 100%;">
										<option value="" <?php echo set_select('dealer', '', ($input['dealer'] == '' ? true : false) ); ?> >-</option>
										<?php
											foreach ($sel_dealer_list as $val) {
												echo "<option value='" . $val['dealer_no'] . "' " . set_select('dealer', $val['dealer_no'], ($val['dealer_no']==$input['dealer'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Router</span>
									<select class="col-lg-12" id="router_id" name="router_id" style="width: 100%;">
										<option value="" <?php echo set_select('router_id', '', ($input['router_id'] == '' ? true : false) ); ?> >-</option>
										<?php
											foreach ($sel_router_list as $val) {
												echo "<option value='" . $val['id'] . "' " . set_select('router_id', $val['id'], ($val['id']==$input['router_id'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
						</fieldset>
					</div>

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Building Address
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">Area</span>
									<select class="col-lg-12" id="area_id" name="area_id" onchange="fillAddr()" style="width: 100%;">
										<option value="0" <?php echo set_select('area_id', '', ($input['area_id'] == '' ? true : false) ); ?> >-</option>
										<?php
											foreach ($sel_area_list as $val) {
												echo "<option value='" . $val['id'] . "' " . set_select('area_id', $val['id'], ($val['id']==$input['area_id'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
								<br />
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 1<span class="red">*</span></span>
									<input type="text" class="form-control" id="addr_1" name="addr_1" value="<?php echo set_value('addr_1', $input['addr_1']); ?>" placeholder="Address Line 1">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 2</span>
									<input type="text" class="form-control" id="addr_2" name="addr_2" value="<?php echo set_value('addr_2', $input['addr_2']); ?>" placeholder="Address Line 2">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Address Line 3</span>
									<input type="text" class="form-control" id="addr_3" name="addr_3" value="<?php echo set_value('addr_3', $input['addr_3']); ?>" placeholder="Address Line 3">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">City<span class="red">*</span></span>
									<input type="text" class="form-control" id="city" name="city" value="<?php echo set_value('city', $input['city']); ?>" placeholder="City">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">State<span class="red">*</span></span>
									<select class="col-lg-12" id="state" name="state" style="width: 100%;">
										<option value=""> -- SELECT -- </option>
										<?php
										$sel_bill_state = set_value('state', $input['state']);
										foreach ($sel_state_list as $val) {
											echo "<option value='" . $val['state_code'] . "' " . ($sel_bill_state == $val['state_code'] ? 'selected="selected"' : '') . ">" . $val['name'] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Postcode<span class="red">*</span></span>
									<input type="text" class="form-control" id="postcode" name="postcode" value="<?php echo set_value('postcode', $input['postcode']); ?>" placeholder="Postcode">
								</div>
							</div>
						</fieldset>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('building');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('building','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<!--<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('building','D');?>>
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
	<script src="<?php echo base_url("js/itelco/building.js?".cssjs_ver()); ?>" ></script>
	<script>
		var areaList = '<?php echo json_encode($sel_area_list) ?>';
	</script>
