<div id="table_wrapper">
    <table class="table-striped">
        <thead>
            <tr>
                <th colspan="10">
                    <h3 class="text-center" style="color: black !important;">
                        <?php echo $page_title; ?> <br />
                        <?php if (!empty($date_to)) { ?><span style="font-size:14px;">AS of <?php echo $date_to; ?></span><?php } ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>No
                </th>
                <th class="col-lg-2" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Name
                </th>
                <th class="col-lg-1 text-center" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Paym Term
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Perv Bill
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Due
                </th>
                <th class="col-lg-1 text-center" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Paid
                </th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Amount
                </th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem1</th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem2</th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem3</th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem4</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                                    
                foreach ($data_row as $key => $row) {
                    $bg_color = ($key % 2 == 0) ? '#f6f8f9' : '#d8e6f0';
                    
                    echo "<tr style='background-color: $bg_color; border-top: 1px solid #dee2e6;'>";
                        echo "<td rowspan='2' style='vertical-align: middle;'><strong>" . $row['customer_no'] . "</strong></td>";
                        echo "<td rowspan='2' style='vertical-align: middle;'>" . $row['profile_name'] . "</td>";
                        echo "<td rowspan='2' class='text-center' style='vertical-align: middle;'>" . $row['payment_term'] . "</td>";
                        echo "<td rowspan='2' style='vertical-align: middle;'>" . $row['prev_bill_no'] . "</td>";
                        echo "<td rowspan='2' style='vertical-align: middle;'>" . $row['prev_bill_due_date'] . "</td>";
                        echo "<td rowspan='2' class='text-right' style='vertical-align: middle;'>" . number_format($row['total_paid_within_term'] ?? 0, 2) . "</td>";
                        echo "<td rowspan='2' class='text-right' style='vertical-align: middle; font-weight: bold;'>" . number_format($row['prev_bill_balance'] ?? 0, 2) . "</td>";
                        
                        echo "<td class='text-center' style='border-bottom: none; color: " . (($row['rm1_tick'] == '1') ? '#28a745' : '#ccc') . ";'>" . (($row['rm1_tick'] == '1') ? '✔' : '-') . "</td>";
                        echo "<td class='text-center' style='border-bottom: none; color: " . (($row['rm2_tick'] == '1') ? '#28a745' : '#ccc') . ";'>" . (($row['rm2_tick'] == '1') ? '✔' : '-') . "</td>";
                        echo "<td class='text-center' style='border-bottom: none; color: " . (($row['rm3_tick'] == '1') ? '#28a745' : '#ccc') . ";'>" . (($row['rm3_tick'] == '1') ? '✔' : '-') . "</td>";
                        echo "<td class='text-center' style='border-bottom: none; color: " . (($row['rm4_tick'] == '1') ? '#28a745' : '#ccc') . ";'>" . (($row['rm4_tick'] == '1') ? '✔' : '-') . "</td>";
                    echo "</tr>";

                    echo "<tr style='background-color: $bg_color; border-bottom: 2px solid #ddd;'>";
                        echo "<td class='text-center px-2' style='font-size: 0.85em; color: #666; padding-top: 0;'>" . ((!empty($row['rm1_date'])) ? $row['rm1_date'] : '-') . "</td>";
                        echo "<td class='text-center px-2' style='font-size: 0.85em; color: #666; padding-top: 0;'>" . ((!empty($row['rm2_date'])) ? $row['rm2_date'] : '-') . "</td>";
                        echo "<td class='text-center px-2' style='font-size: 0.85em; color: #666; padding-top: 0;'>" . ((!empty($row['rm3_date'])) ? $row['rm3_date'] : '-') . "</td>";
                        echo "<td class='text-center px-2' style='font-size: 0.85em; color: #666; padding-top: 0;'>" . ((!empty($row['rm4_date'])) ? $row['rm4_date'] : '-') . "</td>";
                    echo "</tr>";
                }
                
            ?>
        </tbody>
    </table>
</div>