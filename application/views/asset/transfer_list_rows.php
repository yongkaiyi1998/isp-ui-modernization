<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-2">Asset</th>
				<th class="col-lg-1">From</th>
				<th class="col-lg-1">To</th>
				<th class="col-lg-1 text-center">Date</th>
				<th class="col-lg-1">Description</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{asset_name}</td>
						<td class="col-lg-1">{from_name}</td>
						<td>{to_name}</td>
						<td class="col-lg-1 text-center">{transfer_date}</td>
						<td class="col-lg-1">{description}</td>
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