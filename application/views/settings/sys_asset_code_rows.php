<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-2">Asset Code</th>
				<th class="col-lg-6">Name</th>
				<th class="col-lg-2 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{asset_code}</td>
						<td>{short_name}</td>
						<td class="text-right">
							<a href="<?php echo base_url('settings/edit_asset_code');?>/{asset_code_idx}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="3" class="text-center">No Record Available</td>
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