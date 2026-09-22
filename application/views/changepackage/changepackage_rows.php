<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-md-1">ID</th>
				<th class="col-md-2 text-center">Customer Name</th>
				<th class="col-md-2 text-center">IC No.</th>
				<th class="col-md-1 text-center">Type</th>
				<th class="col-md-2 text-center">Old Pkg. Name</th>
				<th class="col-md-2 text-center">New Pkg. Name</th>
				<th class="col-md-1 text-center">Status</th>
				<th class="col-md-1 text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{id}</td>
						<th class="text-center">{acc_name}</th>
						<td class="text-center">{icno}</td>
						<td class="text-center">{acc_type}</td>
						<td class="text-center">{old_pkg_name}</td>
						<td class="text-center">{new_pkg_name}</td>
						<th class="text-center">{status_name}</th>
						<td class="text-center">
							<a href="<?php echo base_url('changepackage/view_cp_request');?>/{id}" title="View">
								<i class="fa fa-file fa-1g grey"></i>
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