<div id="table_wrapper">
    <table class="table-striped" style="width:100%;">
        <thead>
            <tr>
                <th colspan="10">
                    <h3 class="text-center">
                        <?php echo $page_title; ?><br>
                        <span class="small"><?php echo $filter_text; ?></span>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="text-left" <?php if ($print == 1) { echo 'style="width:5%;"'; } else { echo 'style="width:80px;"'; } ?>>Acc.No</th>
                
                <th class="text-left" <?php if ($print == 1) { echo 'style="width:5%;"'; } else { echo 'style="width:150px;"'; } ?>>Name 
                <?php if ($print != 1) { ?><i class="menu-icon fa 
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
                
                <!--<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Login
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'login_username') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="login_username"></i><?php } ?>
                </th>-->
                
                <!--<th class="text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Gender
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'gender') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="gender"></i><?php } ?>
                </th>-->
                
                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Mobile
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'mobile_num') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="mobile_num"></i><?php } ?>
                </th>

                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Email
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'email_1') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="email_1"></i><?php } ?>
                </th>

                <th class="text-center" <?php if ($print == 1) { echo 'style="width:5%;"'; } ?>>Contract Start
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'contract_date') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="contract_date"></i><?php } ?>
                </th>

                <th class="text-center" <?php if ($print == 1) { echo 'style="width:5%;"'; } ?>>Contract End
                </th>

                <th class="text-center" <?php if ($print == 1) { echo 'style="width:5%;"'; } ?>>Unbilled</th>
                
                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Agent
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'dealer') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="dealer"></i><?php } ?>
                </th>
                
                <th class="text-center" <?php if ($print == 1) { echo 'style="width:300px;"'; } else { echo 'style="width:300px;"';  } ?>>Package
                <?php if ($print != 1) { ?><i class="menu-icon fa 
                <?php 
				if ($order_by == 'package_name') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="package_name"></i><?php } ?>
                </th>
                
            </tr>

        </thead>
        <tbody>
            <?php 
            	foreach ($data_row as $key => $val) {
					$addr_parts = [];
					if (!empty($val['building']) || !empty($val['inst_unit_no'])) {
						$addr_parts[] = trim(($val['building'] ?? '') . ' ' . ($val['inst_unit_no'] ?? ''));
					}
					$addr_parts[] = $val['inst_addr1'];
					if (!empty($val['inst_addr2'])) $addr_parts[] = $val['inst_addr2'];
					if (!empty($val['inst_addr3'])) $addr_parts[] = $val['inst_addr3'];
					$addr_parts[] = $val['inst_city'];
					$addr_parts[] = $val['inst_postcode'];
					$addr_parts[] = $val['inst_state'];
					
					$full_address = implode(', ', array_filter($addr_parts));
					$row_bg = ($key % 2 == 0) ? '#f6f8f9' : '#d8e6f0';

					echo "<tr style='background-color: $row_bg; border-top: 1.5px solid #ececec;'>";
						echo "<td class='text-center' style='vertical-align: middle;'><strong>" . $val['customer_no'] . "</strong></td>";
						echo "<td style='vertical-align: middle;'><strong>" . $val['name'] . "</strong></td>";
						//echo "<td class='text-center' style='vertical-align: middle;'>" . $val['login_username'] . "</td>";
						//echo "<td class='text-center' style='vertical-align: middle;'>" . $val['gender'] . "</td>";
						echo "<td class='text-center' style='vertical-align: middle;'>" . $val['mobile_num'] . "</td>";
						echo "<td class='text-center' style='vertical-align: middle; font-size: 0.85em;'>" . $val['email_1'] . ($val['email_2'] != '' ? '<br />' . $val['email_2'] : '') . "</td>";
						echo "<td class='text-center' style='vertical-align: middle;'>" . $val['contract_start'] . "</td>";
						echo "<td class='text-center' style='vertical-align: middle;'>" . $val['contract_end'] . "</td>";
						echo "<td class='text-center' style='vertical-align: middle;'>" . $val['unbilled_months'] . "</td>";
						echo "<td class='text-center' style='vertical-align: middle;'>" . $val['dealer'] . "</td>";
						echo "<td rowspan='2' class='text-center' style='vertical-align: middle;overflow-wrap: break-word;'>" . $val['package_name'] . "</td>";
					echo "</tr>";

					echo "<tr style='background-color: $row_bg;'>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td colspan='8' style='padding-top: 0; padding-bottom: 12px; color: #777; font-size: 0.9em;'>";
                            echo "<i class='fa fa-map-marker' style='margin-right: 5px;'></i>";
							echo $full_address;
						echo "</td>";
					echo "</tr>";
				}
            ?>
        </tbody>
    </table>
</div>