<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('customer', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('device_setting/add_device');?>" style="margin-left: 5px; margin-right: 5px;" >
							<i class="ui-menu-icon fa fa-plus green"></i>
						</a>
					</span>
					<?php }?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="device" name="device" method="post" action="">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', ''); ?>" placeholder='Search'/>
					
					<span>Status: </span>
					<select id="status" name="status">
						<?php $status = 'all' ?>
						<option value="0" <?php echo set_select('status', '0', ($status == '0' ? true : false) ) ?> >All</option>
						<option value="1" <?php echo set_select('status', '1', ($status == '1' ? true : false) ) ?> >Active</option>
						<option value="2"  <?php echo set_select('status', '2', ($status == '2' ? true : false) ) ?> >Inactive</option>
					</select>
					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="col-lg-2">Device Number</th>
							<th class="col-lg-2 text-center">Status</th>
							<th class="col-lg-3 text-right">Last Check In</th>
							<th class="col-lg-1 text-right">Pending</th>
							<th class="col-lg-1 text-right">Sent</th>
							<th class="col-lg-1 text-right">Failed</th>
							<th class="col-lg-1 text-right">Received</th>
							<th class="col-lg-1 text-right">Actions</th>
						</tr>
					</thead>
					<tbody>
					<?php if(!empty($row_data)): ?>
						{row_data}
						<tr>
							<td>{dev_number}</td>
							<td class="text-center">{dev_active}</td>
							<td class="text-right">{dev_lastcheckin}</td>
							<td class="text-right">{dev_pending}</td>
							<td class="text-right">{dev_sent}</td>
							<td class="text-right">{dev_failed}</td>
							<td class="text-right">{dev_received}</td>
							<td class="text-right">
								<a href="<?php echo base_url('device_setting/edit_device');?>/{dev_number}" title="Edit">
									<i class="fa fa-pencil fa-1g"></i>
								</a>
							</td>
						</tr>
						{/row_data}
						<?php else: ?>
							<tr>
								<td colspan="8" class="text-center">No Record Available</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
			<div class="col-md-12 text-center">
					<?php echo $pagination; ?>
			</div>
		</div>
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
<div id="backgroundPopup" onclick="hide_popup();"></div>
