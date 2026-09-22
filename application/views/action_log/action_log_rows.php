<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th class="col-lg-2 text-left">&nbsp;Date Modified</th>
				<th class="col-lg-2 text-left">Customer No</th>
				<th class="text-left">Description</th>
				<th class="col-lg-2 text-right">Action by&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			
			<?php if((empty($row_data))):?>
				<tr><td class="text-center" colspan="4">No Record Available</td></tr>
			<?php else: ?>
				{row_data}
					<tr>
						<td class="text-left">{date_modified}</td>
						<td class="text-left">{customer_no}</td>
						<td class="text-left">{action_desc}</td>
						<td  class="text-right" >{action_by}&nbsp;</td>
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