<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-2" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>ST #</th>
				<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Status</th>
				<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Contact</th>
				<th class="col-lg-2 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Customer</th>
				<th class="col-lg-1 col-c text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Opened By</th>
				<th class="col-lg-1 col-c text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Opened On</th>
				<th class="col-lg-1 col-s text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Closed On</th>
				<th class="col-lg-1 col-t text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Duration</th>
				<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Complaint</th>
				<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>SOF</th>
				<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>COF</th>
				<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Assigned to</th>
				
			</tr>
		</thead>

		<tbody>
		<?php foreach( $tickets AS $key => $row ){ 
			$row_bg = ($key % 2 == 0) ? '#f6f8f9' : '#d8e6f0';
			$base_style = "background-color: $row_bg; vertical-align: middle;";
		?>						
			<tr style="<?php echo $base_style; ?> border-top: 1px solid #dee2e6;">
				<td><?php echo $row['tt_no']; ?></td>
				<td><?php echo $row['tt_status']; ?></td>
				<td><?php echo $row['contact_no']; ?></td>
				<td><?php echo $row['customer_no'].' '.$row['pic_name']; ?></td>
				<td><?php echo $row['prepared_by_name']; ?></td>
				<td><?php echo $row['datetime_open']; ?></td>
				<td><?php echo $row['datetime_close']; ?></td>
				<td><?php echo $row['tt_duration']; ?></td>
				<td><?php echo $row['tt_complaint_name']; ?></td>
				<td><?php echo $row['tt_sof_name']; ?></td>
				<td><?php echo $row['tt_cof_name']; ?></td>
				<td><?php echo $row['assign_to_name'] ?? ''; ?></td>
			</tr>
			<tr style="<?php echo $base_style; ?>">
				<td>Remark:</td>
				<td colspan="11"><?php echo $row['tt_remark']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>