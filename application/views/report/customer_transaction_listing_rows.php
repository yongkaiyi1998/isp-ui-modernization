<div>
	<table class="table-striped" style="width:100%;">
		<thead>
			<tr>
				<th colspan="100%">
					<h3 class="text-center" style="color: black !important;">
						<?php echo $page_title; ?>
					</h3>
				</th>
			</tr>
			<?php if (!empty($data_row['customer']['customer_no'])) { ?>
			<tr>
				<th colspan="100%">
					<h5 class="text-center">
						Subscriber No: <?php echo $data_row['customer']['customer_no']; ?><br>
						Name: <?php echo $data_row['customer']['name']; ?>
					</h5>
				</th>
			</tr>
			<tr>
				<th colspan="100%">
					<h6>
						User Name: <?php echo $data_row['customer']['login_username']; ?>
						<span style="display:inline-block; width: 20px;"></span>
						Current Package: <?php echo $data_row['customer']['package_name']; ?>
					</h6>
				</th>
			</tr>
			<?php } ?>
			<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
				<th class="text-left col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Doc/Pay D</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Doc No.</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill Due Date</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Charge</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Payment</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Balance</th>
				<th class="text-left col-lg-5" <?php if ($isprint == 1) { echo 'style="width:30%;"'; } ?>>Remark</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($data_row['transaction'] as $key => $val) {
				echo "<tr>";
				echo "<td>" . $val['tranx_date'] . "</td>";
				echo "<td class='text-center'>" . $val['tranx_no'] . "</td>";
				echo "<td class='text-center'>" . $val['bill_due_date'] . "</td>";
				echo "<td class='text-center'>" . $val['charge'] . "</td>";
				echo "<td class='text-center'>" . $val['payment'] . "</td>";
				//echo "<td class='text-center'>" . ( empty($val['balance']) ? '' : number_format($val['balance'],2) ) . "</td>";
				echo "<td class='text-center'>" . number_format($val['balance'],2) . "</td>";
				echo "<td class='text-left'>" . $val['remark'] . "</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>