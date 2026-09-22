<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-5">Bill Type</th>
				<th class="col-lg-4">Adjust Type</th>
				<th class="col-lg-2">Tax Code</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
				<tr>
					<td>{name}</td>
					<td>{is_debit}</td>
					<td>{tax_code}</td>
					<td class="text-right">
						<a href="<?php echo base_url('settings/edit_bill_type');?>/{bill_type_id}" title="Edit">
							<i class="fa fa-pencil fa-1g"></i>
						</a>
					</td>
				</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="4" class="text-center">No Record Available</td>
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