<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-4">Router Name</th>
				<th class="col-lg-2">IP</th>
				<th class="col-lg-1">SSH Port</th>
				<th class="col-lg-1 text-right">Actions</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{name}</td>
						<td>{ip}</td>
						<td>{ssh_port}</td>
						<td class="text-right">
							<a href="<?php echo base_url('router/edit_router');?>/{id}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
						<td></td>
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