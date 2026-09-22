<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th class="">Customer No</th>
			<th class="">Customer Name</th>
			<th class="text-center">Opening Balance / BF</th>
			<th class="text-center">Balance</th>
			<th class="text-center">Actions</th>
		</tr>
	</thead>
	<tbody>
		{row_data}
			<tr>
				<td>{customer_no}</td>
				<td>{name}</td>
				<td class="text-right">{opening_balance}</td>
				<td class="text-right">{balance}</td>
				<td class="text-right">
					<a href="<?php echo base_url('report/customer_statement');?>/{customer_no}/{txt_date_from}/{txt_date_to}" title="Edit">
						<i class="fa fa-pencil fa-1g"></i>
					</a>
				</td>
			</tr>
		{/row_data}
	</tbody>
</table>
<div>
	<div class="col-md-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>