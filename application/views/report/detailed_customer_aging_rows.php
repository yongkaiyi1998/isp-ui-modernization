<div id="table_wrapper">
    <table class="table-striped">
        <thead>
            <tr>
                <th colspan="10">
                    <h3 class="text-center">
                        <?php echo $page_title; ?> <br />
                        <span style="font-size:14px;">AS of <?php echo $date_to; ?></span>
                    </h3>
                </th>
            </tr>
            <?php if (!empty($customer_no)) { ?>
            <tr>
                <th colspan="10">
                    <h5 class="text-center">
                        Subscriber No: <?php echo $customer_no; ?><br>
                        Name: <?php echo $name; ?>
                    </h5>
                </th>
            </tr>
            <tr>
                <th colspan="10">
                    <h6>
                        User Name: <?php echo $login_username; ?>
                        <span style="display:inline-block; width: 20px;"></span>
                        Current Package: <?php echo $package_name; ?>
                    </h6>
                </th>
            </tr>
            <?php } ?>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill No.
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill Date
                </th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>0-30 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>31-60 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>61-90 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>91-120 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>121-364 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>1-2 y</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>> 2 y</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>Total A/R</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_ar = 0;
            $total_30 = 0;
            $total_60 = 0;
            $total_90 = 0;
            $total_120 = 0;
            $total_150 = 0;
            $total_365 = 0;
            $total_730 = 0;
                                    
            foreach( $data_row AS $row ){
                                            
                    echo "<tr>";
                        echo "<td>" . $row['bill_no'] . "</td>";
                        echo "<td>" . $row['bill_date'] . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month1'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month2'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month3'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month4'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month5'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['year1'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['year2'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['total_ar'],2) . "</td>";
                    echo "</tr>";
            
                    $total_ar += number_format($row['total_ar'],2,'.','');
                    $total_30 += number_format($row['month1'],2,'.','');
                    $total_60 += number_format($row['month2'],2,'.','');
                    $total_90 += number_format($row['month3'],2,'.','');
                    $total_120 += number_format($row['month4'],2,'.','');
                    $total_150 += number_format($row['month5'],2,'.','');
                    $total_365 += number_format($row['year1'],2,'.','');
                    $total_730 += number_format($row['year2'],2,'.','');
            
            }
            
            echo "<tr>
                <td class='text-right' colspan='2'><strong>Total</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_30, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_60, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_90, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_120, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_150, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_365, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_730, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_ar, 2, '.', ',') . "</strong></td>
                </tr>";
            ?>
        </tbody>
    </table>
</div>