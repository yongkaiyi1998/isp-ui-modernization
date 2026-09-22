<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1">SN#</th>
				<th class="col-lg-1 text-center">Category</th>
				<th class="col-lg-1">Asset#</th>
				<th class="col-lg-1">Site</th>
				<th class="col-lg-2">Location/Ownership</th>
				<th class="col-lg-1">Department</th>
				<th class="col-lg-3">Asset Name</th>
				<th class="col-lg-1"></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{serial_no}</td>
						<td class="col-lg-1 text-center">{category_name}</td>
						<td>{asset_tag}</td>
						<td class="col-lg-2">{site_name}</td>
						<td class="col-lg-1">{location}</td>
						<td class="col-lg-1">{department}</td>
						<td>{asset_name}</td>
						<td class="col-lg-2 text-right action_btn_td">
							<a href="<?php echo base_url('asset/edit_asset');?>/{asset_id}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>

							<a href="<?php echo base_url('asset/add_service');?>/{asset_id}" title="Add Service Record">
								<i class="fa fa-plus fa-1g orange"></i>
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