<div>
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
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Pay Date</th>
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:15%;"'; } ?>>Customer No</th>
                <th class="text-center col-lg-2" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Name</th>
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Building</th>
                <th class="text-center col-lg-2" <?php if( $isprint == 1 ){ echo 'style="width:15%;"'; } ?>>Remark</th>
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Payment No.</th>
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Source</th>
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Ref.</th>
                <th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:10%;"'; } ?>>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            foreach ($data_row as $key => $val) {
                echo "<tr><td colspan='100%'><h5>" . $key . "</h5></td></tr>";
                
                $total_category = 0;
                foreach ($val as $key2 => $val2) {
                    echo "<tr><td colspan='100%'><h6>" . $key2 . "</h6></td></tr>";
                    
                    $total_bill_type = 0;
                    foreach ($val2 as $val3) {
                        echo "<tr>";
                        echo "<td class='small'>" . $val3['pay_date'] . "</td>";
                        echo "<td class='text-center small'>" . $val3['customer_no'] . "</td>";
                        echo "<td class='small'>" . $val3['customer_name'] . "</td>";
                        echo "<td class='text-center small'>" . $val3['building_name'] . "</td>";
                        echo "<td class='text-center small'>" . $val3['remark'] . "</td>";
                        echo "<td class='text-center small'>" . $val3['payment_no'] . "</td>";
                        echo "<td class='text-center small'>" . $val3['payment_source_name'] . ((strtolower($val3['payment_source_name'])=='paynet') ? ' ('.$val3['paynet_bank'].')' : '') . "</td>";
                        echo "<td class='text-center small'>" . $val3['cheque_no'] . "</td>";
                        echo "<td class='text-right small'>" . $val3['amount'] . "</td>";
                        echo "</tr>";

                        $total_bill_type += $val3['amount'];
                    }
                    echo "<tr>
                        <td class='text-right' colspan='8'><strong>Total (" . $key2 . ")</strong></td>
                        <td class='text-right top-bottom-bordered'><strong>" . number_format($total_bill_type, 2, '.', ',') . "</strong></td>
                        </tr>";
                    $total_category += $total_bill_type;
                }
                echo "<tr>
                    <td class='text-right' colspan='8'><strong>Total (" . $key . ")</strong></td>
                    <td class='text-right top-bottom-bordered'><strong>" . number_format($total_category, 2, '.', ',') . "</strong></td>
                    </tr>";
                $grand_total += $total_category;
            }
            echo "<tr>
                <td class='text-right' colspan='8'><strong>Grand Total</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($grand_total, 2, '.', ',') . "</strong></td>
                </tr>";
            ?>
        </tbody>
    </table>
</div>