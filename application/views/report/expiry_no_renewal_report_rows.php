<div id="table_wrapper">
    <table class="table-striped" style="width:100%;">
        <thead>
            <tr>
                <th colspan="10">
                    <h3 class="text-center" style="color: black !important;">
                        <?php echo $page_title; ?><br>
                        <span class="small"><?php echo $filter_text; ?></span>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="text-left" style="width:80px;">Acc.No</th>
                
                <th class="text-left" style="width:80px;">Name 
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
                
                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Login
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
                </th>
                
                <th class="text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Gender
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
                </th>
                
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

                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Contract Start
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

                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Contract End
                </th>
                
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
                
                <th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Package
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
                    $row_bg = ($key % 2 == 0) ? '#f6f8f9' : '#d8e6f0';
                    $base_style = "background-color: $row_bg; vertical-align: middle;";

                    echo "<tr style='$base_style border-top: 1px solid #ddd;'>";
                        echo "<td class='text-center'><strong>" . $val['customer_no'] . "</strong></td>";
                        echo "<td><strong>" . $val['name'] . "</strong></td>";
                        echo "<td class='text-center'>" . $val['login_username'] . "</td>";
                        echo "<td class='text-center'>" . $val['gender'] . "</td>";
                        echo "<td class='text-center'>" . $val['mobile_num'] . "</td>";
                        echo "<td class='text-center' style='font-size: 0.9em;'>" . $val['email_1'] . ($val['email_2'] != '' ? '<br />' . $val['email_2'] : '') . "</td>";
                        echo "<td class='text-center'>" . $val['contract_start'] . "</td>";
                        echo "<td class='text-center'>" . $val['contract_end'] . "</td>";
                        echo "<td class='text-center'>" . $val['dealer'] . "</td>";
                        echo "<td class='text-center'>" . $val['package_name'] . "</td>";
                    echo "</tr>";

                    echo "<tr style='$base_style'>";
                        echo "<td></td>"; // Indent for the address
                        echo "<td></td>"; // Indent for the address
                        echo "<td colspan='8' style='padding-top: 0; padding-bottom: 10px; color: #666; font-size: 0.9em;'>";
                            echo "<i class='fa fa-map-marker' style='margin-right: 5px;'></i>";
                            echo (!empty($val['building']) ? $val['building'] . ', ' : '') . 
                                $val['inst_addr1'] . ', ' . 
                                $val['inst_addr2'] . ', ' . 
                                $val['inst_city'] . ', ' . 
                                $val['inst_postcode'] . ', ' . 
                                $val['inst_state'];
                        echo "</td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>