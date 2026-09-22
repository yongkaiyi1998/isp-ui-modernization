<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-5">Package Name</th>
				<th class="col-lg-1 text-center">Category</th>
				<th class="col-lg-1 text-center">Radius Group (Bandwidth)</th>
				<th class="col-lg-1 text-right">Month Charges</th>
				<th class="col-lg-1 text-right">Yearly Charges</th>
				<th class="col-lg-1 text-right">Sequence</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{name}</td>
						<td class="text-center">{category_name}</td>
						<td class="text-center">{bandwidth}</td>
						<td class="text-right">{monthly_charge}</td>
						<td class="text-right">{yearly_charge}</td>
						<td class="text-right">{lineno}</td>
						<td class="text-right">

							<a href="#" title="Move Up" onclick="package_moveup({package_no});">
								<i class="fa fa-arrow-up fa-1g"></i>
							</a>&nbsp;&nbsp;

							<a href="#" title="Move Down" onclick="package_movedown({package_no});">
								<i class="fa fa-arrow-down fa-1g"></i>
							</a>&nbsp;&nbsp;

							<a href="<?php echo base_url('package/edit_package');?>/{package_no}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="6" class="text-center">No Record Available</td>
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