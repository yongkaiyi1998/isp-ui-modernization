<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1 text-left"></th>
				<th class="col-lg-9 text-left">Router Type</th>
				<th class="col-lg-2 text-center">Actions</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td></td>
						<td class="text-left">{name}</td>
						<td class="text-center">
							<a href="<?php echo base_url('router/edit_router_type');?>/{id}" title="Edit">
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