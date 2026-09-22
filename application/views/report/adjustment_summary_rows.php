<div id="table_wrapper">
	<table class="table-striped" style="width:100%;">
		<thead>
			<tr>
				<th colspan="100%">
					<h3 class="text-center" style="color: black !important;">
						<?php echo $page_title; ?><br>
						<?php echo $date_start . ' until ' . $date_end ?>
					</h3>
				</th>
			</tr>
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="text-right col-lg-2" <?php if( $isprint == 1 ){ echo 'style="width:30%;"'; } ?>>Bill Type</th>
				<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Business</th>
				<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Residential</th>
				<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>DIA</th>
				<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Wholesale</th>
				<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$grand_total = 0;
				$col_total_b = 0;
				$col_total_r = 0;
				$col_total_d = 0;
				$col_total_w = 0;

				foreach ($data_row as $key => $val)
				{
					// business = b + s
					$val_b = (empty($val['b'])?0:$val['b']) + (empty($val['s'])?0:$val['s']);
					// residential = r
					$val_r = (empty($val['r']))?0:$val['r'];
					// dia = d + e
					$val_d = (empty($val['d'])?0:$val['d']) + (empty($val['e'])?0:$val['e']);
					// wholesale = w
					$val_w = (empty($val['w']))?0:$val['w'];
					
					// line total = sum of all
					$line_total = $val_b + $val_r + $val_d + $val_w;

					echo "<tr>";
					echo "<td class='text-right'>" . $val['bill_type_name'] . "</td>";
					echo "<td class='text-right'>" . number_format($val_b,2, '.', ',') . "</td>";
					echo "<td class='text-right'>" . number_format($val_r,2, '.', ',') . "</td>";
					echo "<td class='text-right'>" . number_format($val_d,2, '.', ',') . "</td>";
					echo "<td class='text-right'>" . number_format($val_w,2, '.', ',') . "</td>";
					echo "<td class='text-right'>" . number_format($line_total,2, '.', ',') . "</td>";
					echo "</tr>";
					
					$col_total_b += $val_b;
					$col_total_r += $val_r;
					$col_total_d += $val_d;
					$col_total_w += $val_w;
					$grand_total += $line_total;
				}

				echo "<tr>
					<td class='text-right'><strong>Grand Total</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_b, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_r, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_d, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_w, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($grand_total, 2, '.', ',') . "</strong></td>
					</tr>";
				?>
		</tbody>
	</table>
</div>