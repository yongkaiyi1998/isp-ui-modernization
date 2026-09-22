<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th class="col-lg-2 text-left">&nbsp;Date</th>
				<th class="col-lg-2 text-left">Customer</th>
				<th class="col-lg-2 text-left">Account</th>
				<th class="text-left">Description</th>
			</tr>
		</thead>
		<tbody>
			
			<?php if((empty($row_data))):?>
				<tr><td class="text-center" colspan="4">No Record Available</td></tr>
			<?php else: ?>
				{row_data}
					<tr>
						<td class="text-left">{date_created}</td>
						<td class="text-left">{customer_name}</td>
						<td class="text-left">{user_name}</td>
						<td class="text-left">{remark}</td>
					</tr>
				{/row_data}
			<?php endif; ?>
		</tbody>
	</table>
</div>
<div>
	<div class="col-md-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>