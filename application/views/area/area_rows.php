<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-4">Name</th>
				<th class="col-lg-3 text-center">Postcode</th>
				<th class="col-lg-3 text-center">State</th>
				<th class="col-lg-2 text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{name}</td>
						<td class="text-center">{postcode}</td>
						<td class="text-center">{state_name}</td>
						<td class="text-center">
							<a href="<?php echo base_url('area/edit_area');?>/{id}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
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