<style>
th, td{
	font-size: 11px !important;
}


</style>

<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('trouble_ticket', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('ticket/add_trouble_ticket');?>" style="margin-left: 5px; margin-right: 5px;" >
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
				<form id="payment" name="payment" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
					<span>Category: </span>
					<select id="sel_category" name="sel_category">
						<option value="all">All</option>
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<span>Status: </span>
					<select id="sel_status" name="sel_status">
						<option value="all" <?php echo set_select('sel_status', 'all', ($sel_status == 'all' ? true : false) ) ?> >All</option>
						<option value="1" <?php echo set_select('sel_status', '1', ($sel_status == '1' ? true : false) ) ?> >Open</option>
						<option value="2" <?php echo set_select('sel_status', '2', ($sel_status == '2' ? true : false) ) ?> >Assigned</option>
						<option value="3" <?php echo set_select('sel_status', '3', ($sel_status == '3' ? true : false) ) ?> >In Progress</option>
						<option value="4" <?php echo set_select('sel_status', '4', ($sel_status == '4' ? true : false) ) ?> >Solved</option>
						<option value="0"  <?php echo set_select('sel_status', '0', ($sel_status == '0' ? true : false) ) ?> >Close</option>
					</select>

					<span>Date Open From: </span>
					<input type="text" style='width:110px;' id="date_from" name="date_from" placeholder="Date open from" autocomplete="off"
						value="<?php echo set_value('date_from',$date_from); ?>" />
					
					<span>Date Open To: </span>
					<input type="text" style='width:110px;' id="date_to" name="date_to" placeholder="Date open to" autocomplete="off"
						value="<?php echo set_value('date_to',$date_to); ?>" />
					
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
							<th class="col-lg-1 text-center">ST #</th>
							<th class="col-lg-1 text-center">Status</th>
							<th class="col-lg-1 text-center">Customer</th>
							<th class="col-lg-1 text-center">Contact</th>
							<th class="col-lg-1 text-center">Opened On</th>
							<th class="col-lg-1 text-center">Closed On</th>
							<th class="col-lg-1 text-center">Duration</th>
							<th class="col-lg-1 text-center">Complaint</th>
							<th class="col-lg-1 text-center">SOF</th>
							<th class="col-lg-1 text-center">COF</th>
							<th class="col-lg-1 text-center">Action</th>
						</tr>
					</thead>
					<tbody>
					<?php if(!empty($row_data)): ?>
						{row_data}
							<tr {row_style}>
								<td class="text-center">{tt_no}</td>
								<td class="text-center">{status_badge}</td>
								<td class="text-center">{customer_no}</td>
								<td class="text-center">{contact_no}</td>
								<td class="text-center">{datetime_open}</td>
								<td class="text-center">{datetime_close}</td>
								<td class="text-center">{tt_duration}</td>
								<td class="text-center">{tt_complaint_name}</td>
								<td class="text-center">{tt_sof_name}</td>
								<td class="text-center">{tt_cof_name}</td>

								<td class="text-center action_btn_td">
									<a href="<?php echo base_url('ticket/edit_trouble_ticket');?>/{tt_id}" title="Edit" class="px-1">
										<i class="fa fa-pencil fa-1g"></i>
									</a>
									<?php if (check_acl('customer_support', 'M', false)) {?>
									<a href="<?php echo base_url('customer_support/add_customer_support/');?>/{tt_id}/1" title="Go to TT" class="px-1">
										<i class="fa fa-arrow-right fa-1g red"></i>
									</a>
									<?php } ?>
								</td>
							</tr>
						{/row_data}
					<?php else: ?>
						<tr>
							<td colspan="12" class="text-center">No Record Available</td>
						</tr>
					<?php endif ?>
					</tbody>
				</table>
			</div>
			
			<div>
				<div class="col-md-12 text-center">
					<?php echo $pagination; ?>
				</div>
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

<script>
	$("#date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#date_to").datepicker({format: 'yyyy-mm-dd'});
</script>
