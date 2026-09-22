<?php include 'panel_header.php';?>
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('customer', 'M', false)) {?>
					<!--<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0); float:right;">
						<a style="margin-left: 5px; margin-right: 5px;cursor:pointer" onclick="print_customer_filtered('print_multiple')">
							<i class="ui-menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print All Bill By Post'); ?> ></i>
						</a>
					</span>-->
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('customer/add_customer');?>" style="margin-left: 5px; margin-right: 5px;">
							<i class="ui-menu-icon fa fa-user-plus green"></i>
						</a>
					</span>
					<?php }?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="customer" name="customer" method="post" class="filter-form" action="<?php echo $form_action; ?>" >
					<input type="hidden" id="page_item_no" name="page_item_no" value="<?php echo $page_item_no; ?>">	
					<span>Search: </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo $txt_search; ?>" placeholder='Search'/>
					<span>Category: </span>
					<select id="sel_category" name="sel_category">
						<option value="all">All</option>
						<?php
							foreach ($sel_category_list as $val) {
								$select = "";
								if ($val['category_code'] == $sel_category) {
									$select = "selected='selected'";
								}
								echo "<option value='" . $val['category_code'] . "' " . $select . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<span>Status: </span>
					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<?php 
							foreach ($sel_status_list as $key => $val) {
								$select = "";
								if ($key == $sel_status) {
									$select = "selected='selected'";
								}
								echo "<option value='" . $key . "' " . $select . ">" . $val . "</option>";
							}
						?>
					</select>
					
					<span>Building: </span>
					<select id="sel_building" name="sel_building">
						<option value="all">All</option>
						<?php 
							foreach ($sel_building_list as $val) {
								$select = "";
								if ($val['building_no'] == $sel_building) {
									$select = "selected='selected'";
								}
								echo "<option value='" . $val['building_no'] . "' " . $select . ">" . $val['name'] . "</option>";
							}
						?>
					</select>

					<span id="select-installation" style="<?php echo $sel_status == 'P' ? '' : 'display:none;'?>">
						<span>Installation: </span>
						<select id="sel_installation" name="sel_installation">
							<option value="all">All</option>
							<option value="upcoming">Upcoming</option>
							<option value="overdue">Overdue</option>
							<option value="unscheduled">Unscheduled</option>
						</select>
					</span>
					
					<!--<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success" onclick="call_back('<?php echo $form_action; ?>')">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>-->

					<input type="hidden" id="order_by" 	 name="order_by"   value="<?php echo $order_by; ?>" />
					<input type="hidden" id="order_type" name="order_type" value="<?php echo $order_type; ?>" />

					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>

					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
					</button>
					
				</form>
			</div>

			<!--table rows html here-->
			<div class="table_rows_area"><?php echo $row_html; ?></div>

		</div>

<div class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Packet Of Disconnect</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -28px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<input type="hidden" id="customer_no" name="customer_no" value="" />
      	<div class="panel-body">
		<fieldset class='category-border'>		
			<legend class="category-border">General</legend>				
				<div class="col-lg-6">
					<div class="input-group">
						<span class="input-group-addon input_group"  >Customer Name</span>
						<span id="customer_name" style="margin-left:10px;"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon input_group"  >NAS Address</span>
				        <select id="nas_id" name="nas_id">
				        	<?php foreach ($nas_address as $address) { ?>
				        		<option value="<?php echo $address['id']; ?>"><?php echo $address['nasname']; ?></option>
				        	<?php } ?>
				        </select>
					</div>
				</div>
		</fieldset>
    	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="proceedDC();">Proceed</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
<script>
	var new_account = '0';
	var new_submit = '0';
	var new_effective_date_empty = true;
	var orderBy	= "<?php echo $order_by; ?>";
	var orderType = "<?php echo $order_type; ?>";
</script>
<script src="<?php echo base_url("js/itelco/customer.js?".cssjs_ver()); ?>" ></script>
<?php include 'panel_footer.php';?>
