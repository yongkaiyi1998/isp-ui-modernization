<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1 text-left">Adjust No</th>
				<th class="col-lg-2 text-center">Trans Date</th>
				<th class="col-lg-1">Account</th>
				<th class="col-lg-3">Customer Name / Building / Area</th>
				<th class="col-lg-1">Status</th>
				<th class="col-lg-1">Adjust Type</th>
				<th class="col-lg-1 text-right">Amount</th>
				<th class="col-lg-1 text-center">Bill</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr class="{highlight_class}">
						<td>{adj_no}</td>
						<td class="text-center">{tranx_date}</td>
						<td>{customer_no}</td>
						<td>{customer_name}</td>
						<td>{status_icon}</td>
						<td>{adjust_type}</td>
						<td class="text-right">{amount}</td>
						<td class="text-right">{bill_no}</td>
						<td class="text-right">
							<a href="<?php echo base_url('adjustment/edit_adjustment');?>/{adj_no}" title="Edit">
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
<div>
	<div class="col-md-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>