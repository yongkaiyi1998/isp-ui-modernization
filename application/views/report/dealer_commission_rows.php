<div class="table-responsive">
    <table class="table-striped">
        <thead>
            <tr>
                <th colspan="10">
                    <h3 class="text-center" style="color: black !important;">
                        <?php echo $page_title; ?><br>
                        <?php echo $sel_date_text; ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Pay Date</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Customer No</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Customer</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Activated</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Agent</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Top Level Agent</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Building</th>
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Package</th>
                <th class="col-lg-1 text-center" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill No.</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill Amount</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Comm. Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                foreach (($data_row['rows'] ?? []) as $key => $val) {
                    $row_bg = ($key % 2 == 0) ? '#ffffff' : '#f8f9fa';
                    $border_style = "border-top: 2px solid #dee2e6; background-color: $row_bg;";
                    $sub_row_style = "background-color: $row_bg; border-bottom: 1px solid #dee2e6;";

                    echo "<tr style='$border_style'>";
                        echo "<td rowspan='2' class='text-center' style='vertical-align: middle;'>" . $val['comm_date'] . "</td>";
                        echo "<td rowspan='2' style='vertical-align: middle;'><strong>" . $val['customer_no'] . "</strong></td>";
                        echo "<td rowspan='2' style='vertical-align: middle;'>" . $val['customer_name'] . "</td>";
                        echo "<td rowspan='2' class='text-center' style='vertical-align: middle;'>" . ((!empty($val['activated_date'])) ? date("Y-m-d", strtotime($val['activated_date'])) : '') . "</td>";
                        echo "<td>" . $val['dealer_name'] . "</td>";
                        echo "<td>" . $val['top_level_agent_name'] . "</td>";
                        echo "<td>" . $val['building_name'] . "</td>";
                        echo "<td>" . $val['package_name'] . "</td>";
                        echo "<td class='text-center'><code>" . $val['bill_no'] . "</code></td>";
                        echo "<td class='text-right'>" . number_format($val['bill_amount'], 2) . "</td>";
                        echo "<td class='text-right' style='font-weight: bold; color: #007bff;'>" . number_format($val['amount'], 2) . "</td>";
                    echo "</tr>";

                    echo "<tr style='$sub_row_style'>";
                        echo "<td colspan='7' style='padding-top: 0; padding-bottom: 8px;'>";
                            echo "<span class='text-muted text-primary' style='font-style: italic;'>";
                                echo "<i class='fa fa-comment-o'></i> " . $val['comm_desc'];
                            echo "</span>";
                        echo "</td>";
                    echo "</tr>";
                }
            ?>

            <?php if(isset($data_row['subtotal'])): ?>
                <?php 
                    $isOdd = ($row_bg ?? '#f8f9fa') === '#f8f9fa';
                    foreach ($data_row['subtotal'] as $dealer) { 
                        $row_bg = $isOdd ? '#ffffff' : '#f8f9fa';
                        $isOdd = !$isOdd;
                        $border_style = "border-top: 2px solid #dee2e6; background-color: $row_bg;";
                ?>
                    <tr style="<?php echo $border_style; ?>">
                        <td colspan="8"></td>
                        <td colspan="2" style="font-weight: bold;">
                            Subtotal Commission of <span class="pink"><?= $dealer['name'] ?></span>
                        </td>
                        <td class="text-right" style="font-weight: bold; color: #007bff;">
                            <?= number_format($dealer['subtotal'], 2) ?>
                        </td>
                    </tr>
                <?php } ?>

            <tr class="top-bottom-bordered" style="font-weight: bold; background-color: #d8e6f0;">
                <td colspan="8"></td>
                <td colspan="2">
                    COMMISSION GRAND TOTAL
                </td>
                <td class="text-right">
                    <?= number_format($data_row['grandtotal'], 2) ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>