<div id="table_wrapper">
	<table class="table-striped" style="width:100%;">
		<thead>
			<tr>
				<th colspan="7">
					<h3 class="text-center">
						<?php echo $page_title; ?><br>
					</h3>
				</th>
			</tr>
			<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
				<th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Customer No
				<?php if ($isprint != 1) { ?><i class="menu-icon fa 
				<?php 
				if ($order_by == 'customer_no') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
				" id="customer_no"></i><?php } ?>
				</th>
				<th class="col-lg-2" <?php if ($isprint == 1) { echo 'style="width:25%;"'; } ?>>Name
				<?php if ($isprint != 1) { ?><i class="menu-icon fa 
				<?php 
				if ($order_by == 'customer_name') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
				" id="customer_name"></i><?php } ?>
				</th>
				<th class="col-lg-1">Current Status</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Last Bill Date
				<?php if ($isprint != 1) { ?><i class="menu-icon fa 
				<?php 
				if ($order_by == 'bill_date') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
				" id="bill_date"></i><?php } ?>
				</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Last Payment Date
				<?php if ($isprint != 1) { ?><i class="menu-icon fa 
				<?php 
				if ($order_by == 'last_pay_date') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
				" id="last_pay_date"></i><?php } ?>
				</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Overdue
				<?php if ($isprint != 1) { ?><i class="menu-icon fa 
				<?php 
				if ($order_by == 'overdue') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
				" id="overdue"></i><?php } ?>
				</th>
				<th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Current Balance
				<?php if ($isprint != 1) { ?><i class="menu-icon fa 
				<?php 
				if ($order_by == 'balance') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
				" id="balance"></i><?php } ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$grand_total = 0;
			foreach ($data_row as $key => $val) {
				echo "<tr><td colspan='100%'><h5>" . (isset($cust_category[$key]) ? $cust_category[$key] : '') . "</h5></td></tr>";
				
				$total_category = 0;
				foreach ($val as $key2 => $val2) {
					echo "<tr>";
						echo "<td>" . $key2 . "</td>";
						echo "<td>" . $val2['customer_name'] . "</td>";
						echo "<td>" . $val2['latest_status'] . "</td>";
						echo "<td class='text-center'>" . $val2['bill_date'] . "</td>";
						echo "<td class='text-center'>" . $val2['last_payment_date'] . "</td>";
						echo "<td class='text-center'>" . $val2['overdue'] . "</td>";
						echo "<td class='text-right'>" . $val2['current_balance'] . "</td>";
						echo "</tr>";

					$total_category += $val2['current_balance'];
				}
				
				echo "<tr>
					<td class='text-right' colspan='6'><strong>Total (" . (isset($cust_category[$key]) ? $cust_category[$key] : '') . ")</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($total_category, 2, '.', ',') . "</strong></td>
					</tr>";
					
				$grand_total += $total_category;
			}
			echo "<tr>
				<td class='text-right' colspan='6'><strong>Grand Total</strong></td>
				<td class='text-right top-bottom-bordered'><strong>" . number_format($grand_total, 2, '.', ',') . "</strong></td>
				</tr>";
			?>
		</tbody>
	</table>
</div>