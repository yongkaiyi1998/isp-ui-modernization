<div id="table_wrapper">
    <table class="table-striped" style='width:100%;'>
        <thead>
            <tr>
                <th colspan="7">
                    <h3 class="text-center">
                        <?php echo $page_title; ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
                <th class="text-center col-lg-2" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Date
                <?php if ($isprint != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'pay_date') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="pay_date"></i><?php } ?>
                </th>
                
                <th class="text-center col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Account No
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
                <th class="text-left col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Name
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
                <th class="text-left col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Building
                <?php if ($isprint != 1) { ?><i class="menu-icon fa  
                <?php 
				if ($order_by == 'building_name') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="building_name"></i><?php } ?>
                </th>
                <th class="text-left col-lg-3" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Remark</th>
                <th class="text-left col-lg-2" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Type
                <?php if ($isprint != 1) { ?><i class="menu-icon fa  
                <?php 
				if ($order_by == 'type') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="type"></i><?php } ?>
                </th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Amount
                <?php if ($isprint != 1) { ?><i class="menu-icon fa  
                <?php 
				if ($order_by == 'amount') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="amount"></i><?php } ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            foreach ($data_row as $key => $val) {
                echo "<tr><td colspan='7'><h5>" . $key . "</h5></td></tr>";
                
                    $total_category = 0;
                    foreach ($val as $val3) {
                        echo "<tr>";
                        echo "<td class='text-center small'>" . $val3['pay_date'] . "</td>";
                        echo "<td class='text-center small'>" . $val3['customer_no'] . "</td>";
                        echo "<td class='small'>" . $val3['customer_name'] . "</td>";
                        echo "<td class='text-left small'>" . $val3['building_name'] . "</td>";
                        echo "<td class='text-left small'>" . $val3['remark'] . "</td>";
                        echo "<td class='text-left small'>" . $val3['bill_type'] . "</td>";
                        echo "<td class='text-right small'>" . $val3['amount'] . "</td>";
                        echo "</tr>";

                        $total_category += $val3['amount'];
                    }

                echo "<tr>
                    <td class='text-right' colspan='6'><strong>Total (" . $key . ")</strong></td>
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