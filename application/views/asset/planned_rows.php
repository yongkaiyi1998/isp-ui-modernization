<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-2">Asset#</th>
				<th class="col-lg-2">Name</th>
				<th class="col-lg-1 text-center">Last Maint.</th>
				<th class="col-lg-1 text-center">Next Maint.</th>
				<th class="col-lg-2 text-center">Request Date</th>
				<th class="col-lg-2 text-center">Service Date Time</th>
				<th class="col-lg-2 text-center">Status</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{asset_tag}</td>
						<td class="col-lg-2">{asset_name}</td>
						<td class="col-lg-1 text-center">{last_maint_date}</td>
						<td class="col-lg-1 text-center">{next_maint}</td>
						<td class="col-lg-2 text-center">{request_date}</td>
						<td class="col-lg-2 text-center">{service_date_time}</td>
						<td class="col-lg-2 text-center">{status_icon}</td>
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