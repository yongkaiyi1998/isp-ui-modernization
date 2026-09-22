<div id="table_wrapper">
    <table class="table-striped">
        <thead>
            <tr>
                <th colspan="14">
                    <h3 class="text-center" style="color: #000 !important;">
                        <?php echo $page_title; ?> <br />
                        <?php if (!empty($date_to)) { ?><span style="font-size:14px;">AS of <?php echo $date_to; ?></span><?php } ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Virtual Voucher No
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>AccountSettingCode
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Account Code
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Journal Type
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>DeptNo
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>EmployeeNo
                </th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Foreign Debit Amount</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Foreign Credit Amount</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Currency </th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Base Debit Amount</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Base Credit Amount</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Created Date</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Ref. No.</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                                    
            foreach( $data_row AS $row ){
                
                    echo "<tr>";
                        echo "<td>" . $row['virtual_voucher_no'] . "</td>";
                        echo "<td>" . $row['account_setting_code'] . "</td>";
                        echo "<td>" . $row['account_code'] . "</td>";
                        echo "<td>" . $row['journal_type'] . "</td>";
                        echo "<td>" . $row['dept_no'] . "</td>";
                        echo "<td>" . $row['emp_no'] . "</td>";
                        echo "<td>" . $row['foreign_dr_amount'] . "</td>";
                        echo "<td>" . $row['foreign_cr_amount'] . "</td>";
                        echo "<td>" . $row['currency'] . "</td>";
                        echo "<td>" . $row['base_dr_amount'] . "</td>";
                        echo "<td>" . $row['base_cr_amount'] . "</td>";
                        echo "<td>" . $row['create_date'] . "</td>";
                        echo "<td>" . $row['ref_no'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                    echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>