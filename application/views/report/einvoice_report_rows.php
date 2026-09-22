<div id="table_wrapper">
    <table class="table-striped" style="width:100%;">
        <thead>
            <tr>
                <th colspan="8">
                    <h3 class="text-center" style="color: black !important;">
                        <?php echo $page_title; ?><br>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill No
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill Date
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Account No
                </th>
                <th class="col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Name
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:20%;"'; } ?>>E-Invoice UUID
                </th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>E-Invoice Status
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Submitted
                </th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Amount
                </th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            foreach ($data_row as $key => $val) {
                echo "<tr>";
                echo "<td>".$val['bill_no']."</td>";
                echo "<td>".$val['bill_date']."</td>";
                echo "<td>".$val['customer_no']."</td>";
                echo "<td>".$val['profile_name']."</td>";
                echo "<td>".$val['einvoice_uuid']."</td>";
                echo "<td class='text-center'>".$val['einvoice_status_text']."</td>";
                echo "<td>".$val['submitted_on']."</td>";
                echo "<td class='text-right'>".$val['amount']."</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>