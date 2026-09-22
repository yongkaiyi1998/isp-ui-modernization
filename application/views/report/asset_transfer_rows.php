<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-1" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset #</th>
				<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset Name</th>
				<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>From</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>To</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Date</th>
				<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Description</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $assets AS $row ){ ?>						
			<tr>
				<td><?php echo $row['asset_tag']; ?></td>
				<td><?php echo $row['asset_name']; ?></td>
				<td><?php echo $row['from_name']; ?></td>
				<td><?php echo $row['to_name']; ?></td>
				<td><?php echo $row['transfer_date']; ?></td>
				<td><?php echo $row['description']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>