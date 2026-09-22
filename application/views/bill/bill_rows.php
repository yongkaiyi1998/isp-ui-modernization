<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="">Customer No</th>
				<th class="">Customer Name</th>
				<th class="text-center">Status</th>
				<th class="text-center">Activated Date</th>
				<th class="text-center">Last Bill Date</th>
				<th class="text-center">Balance</th>
				<th class="text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{customer_no}</td>
						<td>{name}</td>
						<td class="text-center">{latest_status_name}</td>
						<td class="text-center">{activated_date}</td>
						<td class="text-center">{last_bill_date}</td>
						<td class="text-right">{balance}</td>
						<td class="text-right action_btn_td">
							<a href="#" onclick="print_latest_bill({customer_no})" class="px-1">
								<i class="menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print'); ?>></i>
							</a>
							<a href="<?php echo base_url('bill/bill_detail');?>/{customer_no}" title="Edit" class="px-1">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="6" class="text-center">No Record Available</td>
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