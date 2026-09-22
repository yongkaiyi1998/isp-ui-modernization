<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-4">Name</th>
				<th class="col-lg-1 text-center">Total Units</th>
				<th class="col-lg-1 text-center">Pending</th>
				<th class="col-lg-1 text-center">Cancelled</th>
				<th class="col-lg-1 text-center">Activated</th>
				<th class="col-lg-1 text-center">Suspended</th>
				<th class="col-lg-1 text-center">Terminated</th>
				<th class="col-lg-1 text-center">Total Customers</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td>{name}</td>
						<td class="text-right">{total_unit}</td>
						<th class="col-lg-1 text-right">{p}</th>
						<th class="col-lg-1 text-right">{c}</th>
						<th class="col-lg-1 text-right">{a}</th>
						<th class="col-lg-1 text-right">{s}</th>
						<th class="col-lg-1 text-right">{t}</th>
						<th class="col-lg-1 text-right">{total_customer}</th>
						<td class="text-right">
							<a href="<?php echo base_url('building/edit_building');?>/{building_no}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				{/row_data}
				<?php 
					//building total count below
					$total_unit_count = 0;
					$total_p = 0;
					$total_c = 0;
					$total_a = 0;
					$total_s = 0;
					$total_t = 0;
					$total_customer = 0;
					foreach ($row_data as $row) {
						$total_unit_count = $total_unit_count + $row['total_unit'];
						$total_p = $total_p + $row['p'];
						$total_c = $total_c + $row['c'];
						$total_a = $total_a + $row['a'];
						$total_s = $total_s + $row['s'];
						$total_t = $total_t + $row['t'];
						$total_customer = $total_customer + $row['total_customer'];
					}
				?>
				<tr>
					<td><b>Grand Total</b></td>
					<th class="text-right"><?php echo $total_unit_count; ?></th>
					<th class="text-right"><?php echo $total_p; ?></th>
					<th class="text-right"><?php echo $total_c; ?></th>
					<th class="text-right"><?php echo $total_a; ?></th>
					<th class="text-right"><?php echo $total_s; ?></th>
					<th class="text-right"><?php echo $total_t; ?></th>
					<th class="text-right"><?php echo $total_customer; ?></th>
					<td></td>
				</tr>
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