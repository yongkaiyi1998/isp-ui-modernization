<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1">ID</th>
				<th class="col-lg-2 text-left">Name</th>
				<th class="col-lg-3 text-left">Image</th>
				<th class="col-lg-1 text-center">Start Date</th>
				<th class="col-lg-1 text-center">End Date</th>
				<th class="col-lg-1 text-center">Active</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{id}</td>
						<td class="text-left">{name}</td>
						<td class="text-left">{image_html}</td>
						<td class="text-center">{start_date}</td>
						<td class="text-center">{end_date}</td>
						<td class="text-center">{is_active_html}</td>
						<td class="text-right">
							<a href="<?php echo base_url('advertisement/edit_advertisement');?>/{id}" title="Edit">
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