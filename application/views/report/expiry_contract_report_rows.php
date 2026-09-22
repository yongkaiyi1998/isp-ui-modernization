<div id="table_wrapper">
    <table class="table-striped">
        <thead>
            <tr>
                <th colspan="7">
                    <h3 class="text-center">
                        <?php echo $page_title; ?> <br />
                        <?php if (!empty($date_to)) { ?><span style="font-size:14px;">AS of <?php echo $date_to; ?></span><?php } ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>No
                </th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Name
                </th>
                <th class="col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Package
                </th>
                <th class="text-center  col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Contract Months 
                </th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Expiry 
                </th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem sent</th>
                <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                                    
            foreach( $data_row AS $row ){
                
                    echo "<tr>";
                        echo "<td>" . $row['customer_no'] . "</td>";
                        echo "<td>" . $row['profile_name'] . "</td>";
                        echo "<td>" . $row['package_name'] . "</td>";
                        echo "<td class='text-center'>" . $row['contract_month'] . "</td>";
                        echo "<td class='text-center'>" . $row['expiry_date'] . "</td>";
                        echo "<td class='text-center'>" . (($row['rm1_tick'] == '1') ? 'X' : '-') . "</td>";
                        echo "<td class='text-center'>". ((!empty($row['rm1_date'])) ? $row['rm1_date'] : '-') ."</td>";
                    echo "</tr>";
            
            }
            ?>
        </tbody>
    </table>
</div>