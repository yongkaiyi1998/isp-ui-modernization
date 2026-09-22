<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1 text-left">Customer</th>
				<th class="col-lg-2 text-left">Type</th>
				<th class="col-lg-3 text-left">Title</th>
				<th class="col-lg-1 text-center">Source</th>
				<th class="col-lg-2 text-center">Queued On</th>
				<th class="col-lg-1 text-center">Delivered</th>
				<th class="col-lg-1 text-center">Status</th>
				<th class="col-lg-1 text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($row_data)): ?>
				{row_data}
				<tr>
					<td class="text-left">{customer_no}</td>
					<td class="text-left">{push_type}</td>
					<td class="text-left">{preview}</td>
					<td class="text-center">{source}</td>
					<td class="text-center">{created_date}</td>
					<td class="text-center">{delivery}</td>
					<td class="text-center">{status}</td>
					<td class="text-center">
						<a href="<?php echo base_url('push/push_detail'); ?>/{scheduler_id}" title="Detail">
							<i class="fa fa-list fa-1g"></i>
						</a>
					</td>
				</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="9" class="text-center">No Record Available</td>
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