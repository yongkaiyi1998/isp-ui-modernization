<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-1" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset #</th>
				<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset Name</th>
				<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Last Maintenance</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Next Maintenance</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Request Date</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Service Date Time</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Status</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $assets AS $row ){ ?>						
			<tr>
				<td><?php echo $row['asset_tag']; ?></td>
				<td class="col-lg-1 text-left"><?php echo $row['asset_name']; ?></td>
				<td class="col-lg-1 text-left"><?php echo $row['last_maint_date']; ?></td>
				<td class="col-lg-1 text-left"><?php echo $row['next_maint']; ?></td>
				<td class="col-lg-1 text-left"><?php echo $row['request_date']; ?></td>
				<td class="col-lg-1 text-left"><?php echo $row['service_date_time']; ?></td>
				<td class="col-lg-1 text-left"><?php echo $row['status'] == 'P' ? 'Pending' : ($row['status'] == 'D' ? 'Done' : 'Not Scheduled'); ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>