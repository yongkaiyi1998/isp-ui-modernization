<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-1" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>SN #</th>
				<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Category</th>
				<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset#</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Site</th>
				<th class="col-lg-1 col-c text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Location</th>
				<th class="col-lg-1 col-s text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Department</th>
				<th class="col-lg-2 col-t text-left" <?php if ($print == 1) { echo 'style="width:30%;"'; } ?>>Asset Name</th>
				<th class="col-lg-1 col-s text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Status</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $assets AS $row ){ ?>						
			<tr>
				<td><?php echo $row['serial_no']; ?></td>
				<td><?php echo $row['category_name']; ?></td>
				<td><?php echo $row['asset_tag']; ?></td>
				<td><?php echo $row['site_name']; ?></td>
				<td><?php echo $row['location']; ?></td>
				<td><?php echo $row['department']; ?></td>
				<td><?php echo $row['asset_name']; ?></td>
				<td><?php echo $row['status_text']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>