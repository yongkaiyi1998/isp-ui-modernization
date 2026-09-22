<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1">SO#</th>
				<th class="col-lg-2">Date</th>
				<th class="col-lg-2">Customer Name</th>
				<th class="col-lg-2">Company</th>
				<th class="col-lg-1">Requestor</th>
				<th class="col-lg-2 text-center">Installation Date</th>
				<th class="col-lg-1 text-center">Status</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr class="customer_row" data-customer-no="{so_id}" data-name="{comp_name}">
						<td class="align-middle-important">{so_id}</td>
						<td class="align-middle-important">{date}</td>
						<td class="align-middle-important">{cust_name}</td>
						<td class="align-middle-important">{comp_name}</td>
						<td class="align-middle-important">{requestor_name}</td>
						<td class="align-middle-important text-center">{preferred_installation}</td>
						<td class="text-center align-middle-important">{status_text}</td>
						<td class="text-right align-middle-important">
							<a href="<?php echo base_url('salesorder/add_so');?>/{so_id}" title="Edit">
								<i class="fa <?php echo $view_only==1?'fa-search fa-1g black':'fa-pencil fa-1g'; ?>"></i>
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