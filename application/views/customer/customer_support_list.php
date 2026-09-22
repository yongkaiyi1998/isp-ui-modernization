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
					<?php if (check_acl('customer_support', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('customer_support/add_customer_support');?>" style="margin-left: 5px; margin-right: 5px;" >
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
					
					<span>Type of Premises </span>
					<select id="sel_premises" name="sel_premises">
						<option value="" <?php echo set_select('sel_premises', '', ($sel_premises == '' ? true : false) ) ?>>All</option>
						<option value="r" <?php echo set_select('sel_premises', 'r', ($sel_premises == 'r' ? true : false) ) ?>>Residential</option>
						<option value="c" <?php echo set_select('sel_premises', 'c', ($sel_premises == 'c' ? true : false) ) ?> >Commercial</option>
					</select>
					
					<span>Status </span>
					<select id="sel_status" name="sel_status">
						<option value="" <?php echo set_select('sel_status', '', ($sel_status == '' ? true : false) ) ?>>All</option>
						<option value="0" <?php echo set_select('sel_status', '0', ($sel_status === '0' ? true : false) ) ?>>Open</option>
						<option value="1" <?php echo set_select('sel_status', '1', ($sel_status === '1' ? true : false) ) ?>>In Progress</option>
						<option value="2" <?php echo set_select('sel_status', '2', ($sel_status === '2' ? true : false) ) ?>>Escalated to 3rd Level Support</option>
						<option value="3" <?php echo set_select('sel_status', '3', ($sel_status === '3' ? true : false) ) ?>>Closed</option>
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
							<th class="col-lg-1 text-left">Support #</th>
							<th class="col-lg-1 text-left">Customer</th>
							<th class="col-lg-1 text-center">Opened On</th>
							<th class="col-lg-1 text-center">Status</th>
							<th class="col-lg-1 text-center">Closed On</th>
							<th class="col-lg-1 text-left">Serviced By</th>
							<th class="col-lg-1 text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($row_data)): ?>
							{row_data}
								<tr>
									<td class="text-left">{cs_no}</td>
									<td class="text-left">{customer_no}</td>
									<td class="text-center">{report_on}</td>
									<td class="text-center">{cs_status_name}</td>
									<td class="text-center">{closed_on}</td>
									<td class="text-left">{service_by_name}</td>
									<td class="text-center action_btn_td">
										<a href="<?php echo base_url('customer_support/add_customer_support');?>/{cs_no}" title="Edit">
											<i class="fa fa-pencil fa-1g"></i>
										</a>
										
										<a target="_BLANK" href="<?php echo base_url('customer_support/print_customer_support');?>/{cs_no}" title="Print">
											<i class="fa fa-print fa-1g light-red"></i>
										</a>
										
									</td>
								</tr>
							{/row_data}
						<?php else: ?>
							<tr>
								<td colspan="7" class="text-center">No Record Available</td>
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
