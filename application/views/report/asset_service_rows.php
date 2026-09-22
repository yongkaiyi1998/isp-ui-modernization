<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-1" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset #</th>
				<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset Name</th>
				<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Service Date</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Vendor</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Cost</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Status</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $assets AS $row ){ ?>						
			<tr>
				<td><?php echo $row['asset_tag']; ?></td>
				<td><?php echo $row['asset_name']; ?></td>
				<td><?php echo $row['service_date_time']; ?></td>
				<td><?php echo $row['vendor']; ?></td>
				<td><?php echo $row['maint_cost']; ?></td>
				<td><?php echo $row['status_text']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>