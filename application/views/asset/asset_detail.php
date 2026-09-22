<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('asset');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form autocomplete="off" id="asset_detail" name="asset_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						Asset
					</div>

					<input type="hidden" id="asset_id" name="asset_id" value="<?php echo set_value('asset_id', $input['asset_id']); ?>" />

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Asset Record
							</legend>
							<div>
								<div class="input-group">
									<span class="input-group-addon input_group">S/N#</span>
									<input type="text" class="form-control" id="serial_no" name="serial_no" value="<?php echo set_value('serial_no', $input['serial_no']); ?>" placeholder="Serial No.">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Vendor</span>
									<input type="text" class="form-control" id="vendor" name="vendor" value="<?php echo set_value('vendor', $input['vendor']); ?>" placeholder="Vendor Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Asset Name<span class="red">*</span></span>
									<input type="text" class="form-control" id="asset_name" name="asset_name" value="<?php echo set_value('asset_name', $input['asset_name']); ?>" placeholder="Asset Name">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Asset Code<span class="red">*</span></span>
									<input type="text" class="form-control" id="asset_code" name="asset_code" value="<?php echo set_value('asset_code', $input['asset_code']); ?>" placeholder="Asset Code">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Asset Tag<span class="red">*</span></span>
									<input type="text" class="form-control" id="asset_tag" name="asset_tag" value="<?php echo set_value('asset_tag', $input['asset_tag']); ?>" placeholder="Asset Tag">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Site<span class="red">*</span></span>
									<select class="col-lg-12" id="site_display" name="site_display" <?php if (!empty($input['asset_id'])) { echo 'disabled="disabled"'; } ?> onchange="update_site_field();" style="width: 100%;">
										<option value="0">--Please Select--</option>
										<?php
											foreach ($asset_site_list as $val) {
												echo "<option value='" . $val['site_code'] . "' " . 
													set_select('site', $val['site_code'], ($val['site_code']==$input['site'] ? true : false) ) . ">" . 
													$val['site_code']." - ".$val['site_name']."</option>";
											}
										?>
									</select>
									<input type="hidden" id="site" name="site" value="<?php echo set_value('site', $input['site']); ?>" />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Location</span>
									<input type="text" class="form-control customer_autocomplete" id="location" name="location" value="<?php echo set_value('location', $input['location']); ?>" placeholder="Location">

									<input type="hidden" id="customer_no" name="customer_no" value="<?php echo set_value('customer_no', $input['customer_no']); ?>" />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Category</span>
									<select class="col-lg-12" id="category_idx" name="category_idx" <?php if (empty($input['asset_id'])) { ?>onchange="default_pic();" <?php } ?> style="width: 100%;">
										<option value="0">--Please Select--</option>
										<?php
											foreach ($asset_category_list as $val) {
												echo "<option value='" . $val['category_idx'] . "' " . 
													set_select('category_idx', $val['category_idx'], ($val['category_idx']==$input['category_idx'] ? true : false) ) . ">" . 
													$val['category_code']." ".$val['category_name']."</option>";
											}
										?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Department</span>
									<input type="text" class="form-control" id="department" name="department" value="<?php echo set_value('department', $input['department']); ?>" placeholder="Department">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Brand</span>
									<input type="text" class="form-control" id="brand" name="brand" value="<?php echo set_value('brand', $input['brand']); ?>" placeholder="Brand">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Model No.</span>
									<input type="text" class="form-control" id="model_no" name="model_no" value="<?php echo set_value('model_no', $input['model_no']); ?>" placeholder="Model No.">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Country of Origin</span>
									<input type="text" class="form-control" id="origin_country" name="origin_country" value="<?php echo set_value('origin_country', $input['origin_country']); ?>" placeholder="Country of Origin">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Purchase Date</span>
									<input type="text" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo set_value('purchase_date', $input['purchase_date']); ?>" placeholder="Purchase Date" autocomplete="off">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Purchase Price</span>
									<input type="text" class="form-control" id="purchase_price" name="purchase_price" value="<?php echo set_value('purchase_price', $input['purchase_price']); ?>" placeholder="Purchase Price">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Warranty #</span>
									<input type="text" class="form-control" id="warranty" name="warranty" value="<?php echo set_value('warranty', $input['warranty']); ?>" placeholder="Warranty No.">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Warranty Expiry</span>
									<input type="text" class="form-control" id="warranty_expiry" name="warranty_expiry" value="<?php echo set_value('warranty_expiry', $input['warranty_expiry']); ?>" placeholder="Warranty Expiry" autocomplete="off">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Manufacturer</span>
									<input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?php echo set_value('manufacturer', $input['manufacturer']); ?>" placeholder="Manufacturer">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Owner</span>
									<input type="text" class="form-control" id="owner" name="owner" value="<?php echo set_value('owner', $input['owner']); ?>" placeholder="Owner">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Use Life</span>
									<input type="number" class="form-control" id="use_life" name="use_life" value="<?php echo set_value('use_life', $input['use_life']); ?>" placeholder="No. of Years">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Depreciation</span>
									<input type="text" class="form-control" id="depreciation_rate" name="depreciation_rate" value="<?php echo set_value('depreciation_rate', $input['depreciation_rate']); ?>" placeholder="Depreciation Rate" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Cur. Asset Value</span>
									<input type="text" class="form-control" id="current_asset_value" name="current_asset_value" value="<?php echo set_value('current_asset_value', $input['current_asset_value']); ?>" placeholder="Current Asset Value" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Non-Capitalize</span>
									<input type="checkbox" id="non_capitalize" name="non_capitalize" value="1" <?php echo set_checkbox('non_capitalize', '1', ($input['non_capitalize']==1? true:false) ); ?> />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Status</span>
									<select class="col-lg-12" id="asset_status" name="asset_status" style="width: 100%;">
										<option value="1" <?php echo set_select('asset_status', '', ($input['asset_status'] == '1' ? true : false) ); ?>>Active</option>
										<option value="2" <?php echo set_select('asset_status', '', ($input['asset_status'] == '2' ? true : false) ); ?>>To Scrap</option>
										<option value="3" <?php echo set_select('asset_status', '', ($input['asset_status'] == '3' ? true : false) ); ?>>Disposed</option>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Details</span>
									<textarea class="form-control" id="details" name="details" rows="3" style="resize: vertical;"><?php echo set_value('details', $input['details']); ?></textarea>
								</div>
							</div>
						</fieldset>
					</div>

					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Planned Maintenance
							</legend>

							<div class="input-group">
								<span class="input-group-addon input_group">Start Date</span>
								<input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo set_value('start_date', $input['start_date']); ?>" placeholder="Start Date" autocomplete="off">
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">Interval</span>
								<input type="number" class="form-control" id="interval_value" name="interval_value" value="<?php echo set_value('interval_value', $input['interval_value']); ?>" placeholder="Interval">
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">Type</span>
								<select class="col-lg-12" id="interval_unit" name="interval_unit" style="width: 100%;">
									<option value="d" <?php echo set_select('interval_unit', '', ($input['interval_unit'] == 'd' ? true : false) ); ?>>Day</option>
									<option value="m" <?php echo set_select('interval_unit', '', ($input['interval_unit'] == 'm' ? true : false) ); ?>>Month</option>
									<option value="y" <?php echo set_select('interval_unit', '', ($input['interval_unit'] == 'y' ? true : false) ); ?>>Year</option>
								</select>
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">First Maint.</span>
								<input type="text" class="form-control" id="next_maint_date" name="next_maint_date" value="<?php echo set_value('next_maint_date', $input['next_maint_date']); ?>" placeholder="First Maintenance" readonly>
							</div>

							<div class="input-group">
								<span class="input-group-addon input_group">End Date</span>
								<input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo set_value('end_date', $input['end_date']); ?>" placeholder="End Date" autocomplete="off">
							</div>

						</fieldset>
					</div>

					<?php if (!empty($input['asset_id'])) { ?>

					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">
								Service Record
							</legend>

							<div class="hidden-xs">
								<table id="service_record_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">
									<thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
										<tr>
											<th class="col-1">Vendor</th>
											<th class="col-1">Reference</th>
											<th class="col-1">Description</th>
											<th class="text-center col-1">Status</th>
											<th class="text-center col-1">Request Date</th>
											<th class="text-center col-1">Service Date</th>
											<th style="width:50px" class="text-center">
												<i class="fa fa-ellipsis-v"></i>
											</th>
										</tr>
									</thead>

									<tbody>
										<?php foreach ($service_record as $service): ?>
											<tr>
												<td><?= htmlspecialchars($service['vendor']) ?></td>
												<td><?= htmlspecialchars($service['doc_ref']) ?></td>
												<td><?= htmlspecialchars($service['description']) ?></td>
												<td><?= htmlspecialchars($service['status_text']) ?></td>
												<td><?= htmlspecialchars($service['request_date']) ?></td>
												<td><?= htmlspecialchars($service['service_date_time']) ?></td>
												<td>
													<a href="<?= base_url('asset/add_service/'.$service['asset_id'].'/'.$service['maint_id']) ?>" title="Edit">
														<i class="fa fa-pencil"></i>
													</a>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>

							<div class="visible-xs">
								<?php if (!empty($service_record)): ?>
									<?php foreach (array_reverse($service_record) as $service): ?>
										<div style="margin-bottom:12px;background:#fff;border:1px solid #eee;border-radius:10px;overflow:hidden;font-size:14px;">
											<div style="padding:12px 14px;background:#fafafa;border-bottom:1px solid #f2f2f2;">
												<div style="display:flex;justify-content:space-between;align-items:flex-start;">
													<div>
														<div style="font-size:15px;font-weight:600;">
															<?= htmlspecialchars($service['vendor']) ?>
														</div>

														<div class="grey" style="font-size:12px;margin-top:3px;">
															Ref: <?= htmlspecialchars($service['doc_ref']) ?>
														</div>
													</div>

													<a href="<?= base_url('asset/add_service/'.$service['asset_id'].'/'.$service['maint_id']) ?>" title="Edit">
														<i class="fa fa-pencil"></i>
													</a>
												</div>
											</div>

											<div style="padding:12px 14px;">
												<div style="margin-bottom:10px;">
													<div class="grey" style="font-size:11px;">Description</div>
													<div><?= htmlspecialchars($service['description']) ?></div>
												</div>

												<div style="display:flex;justify-content:space-between;margin-bottom:10px;">
													<div>
														<div class="grey" style="font-size:11px;">Status</div>
														<div><?= htmlspecialchars($service['status_text']) ?></div>
													</div>

													<div style="text-align:right;">
														<div class="grey" style="font-size:11px;">Request Date</div>
														<div><?= htmlspecialchars($service['request_date']) ?></div>
													</div>
												</div>

												<div>
													<div class="grey" style="font-size:11px;">Service Date</div>
													<div><?= htmlspecialchars($service['service_date_time']) ?></div>
												</div>
											</div>
										</div>

									<?php endforeach; ?>

								<?php else: ?>

									<div style="padding:15px;text-align:center;" class="grey">
										No service record found
									</div>

								<?php endif; ?>

							</div>

							<div class="text-center">
								<button id="btAddService"
									onclick="go_service_record();"
									name="btAddService"
									type="button"
									value="button"
									class="btn btn-danger"
									<?= check_acl_btn('asset','M'); ?>>
									Add Service Record
								</button>
							</div>

						</fieldset>
					</div>

					<?php } ?>

					<?php if (!empty($input['asset_id'])) { ?>

					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">
								Transfer Record
							</legend>

							<div class="hidden-xs">

								<table id="transfer_record_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">

									<thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
										<tr>
											<th class="col-1">From</th>
											<th class="col-1">To</th>
											<th class="col-1">Description</th>
											<th class="text-center col-1">Date</th>
										</tr>
									</thead>

									<tbody>
									<?php foreach ($transfer_record as $rec): ?>
										<tr>
											<td><?= htmlspecialchars($rec['from_name']) ?></td>
											<td><?= htmlspecialchars($rec['to_name']) ?></td>
											<td><?= htmlspecialchars($rec['description']) ?></td>
											<td><?= htmlspecialchars($rec['transfer_date']) ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>

								</table>

							</div>

							<div class="visible-xs">
								<?php if (!empty($transfer_record)): ?>
									<?php foreach (array_reverse($transfer_record) as $rec): ?>
										<div style="margin-bottom:12px;background:#fff;border:1px solid #eee;border-radius:10px;overflow:hidden;font-size:14px;">
											<div style="padding:12px 14px;background:#fafafa;border-bottom:1px solid #f2f2f2;">
												<div style="font-size:15px;font-weight:600;">
													<?= htmlspecialchars($rec['from_name']) ?>
													<i class="fa fa-arrow-right" style="margin:0 6px;"></i>
													<?= htmlspecialchars($rec['to_name']) ?>
												</div>
											</div>

											<div style="padding:12px 14px;">
												<div style="margin-bottom:10px;">
													<div class="grey" style="font-size:11px;">Description</div>
													<div><?= htmlspecialchars($rec['description']) ?></div>
												</div>

												<div>
													<div class="grey" style="font-size:11px;">Transfer Date</div>
													<div><?= htmlspecialchars($rec['transfer_date']) ?></div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								<?php else: ?>
									<div style="padding:15px;text-align:center;" class="grey">
										No transfer record found
									</div>
								<?php endif; ?>
							</div>

							<div class="text-center">
								<button id="btAddTransfer" onclick="go_transfer_record();" name="btAddTransfer" type="button" value="button" class="btn btn-danger" <?php echo check_acl_btn('asset','M');?>>
									Add Transfer Record
								</button>
							</div>
							

						</fieldset>
					</div>

					<?php } ?>

					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">
								Maintenance PIC
							</legend>

							<table id="maintenance_pic_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">

						        <thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
						          <th class="col-1">Name</th>
						          <th class="col-1">Email</th>
						          <th class="col-1">Phone</th>
						          <th class="col-1">Telegram</th>
						          <th></th>
						        </thead>

						        <tbody>

						        <?php foreach ($contacts_pic as $rec) { ?>
						        	<tr class="contacts_pic_tr">
						        		<td>
						        			<input class="form-control" type="text" name="username[]" value="<?php echo $rec['username']; ?>" />
						        			<input type="hidden" name="user_id[]" value="<?php echo $rec['user_id']; ?>" />
						        		</td>
						        		<td><input class="form-control" type="text" name="email[]" value="<?php echo $rec['email']; ?>" /></td>
						        		<td><input class="form-control" type="text" name="phone[]" value="<?php echo $rec['phone']; ?>" /></td>
						        		<td><input class="form-control" type="text" name="telegram_id[]" value="<?php echo $rec['telegram_id']; ?>" /></td>
						        		<td>
						        			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
						        		</td>
						        	</tr>
						        <?php } ?>

						    	</tbody>

							</table>

							<button id="btAddContact" onclick="add_user_row();" name="btAddContact" type="button" value="button" class="btn btn-info" <?php echo check_acl_btn('asset','M');?>>
								Add
							</button>

						</fieldset>
					</div>

				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('asset');?>';" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('asset','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('asset','D');?>>
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
	<script src="<?php echo base_url("js/itelco/asset.js?".cssjs_ver()); ?>" ></script>
