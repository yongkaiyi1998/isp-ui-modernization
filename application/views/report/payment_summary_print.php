<style>
@page {
    size: a4 portrait;
}

@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .table-responsive {
        overflow: visible !important;
        height: auto !important;
    }

    .table-striped {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table-striped th,
    .table-striped td {
        white-space: nowrap;
        padding: 4px;
    }

    .noprint, .hidden-print {
        display: none !important;
    }
}
</style>
<div class="container">
    <div class="panel-default">
        <div class="panel-body">

            <div>
                <table class="table-bordered sticky_table table-striped" style="width:100%;">
                    <thead>
                        <tr>
                            <th colspan="100%">
                                <h3 class="text-center">
                                    <?php echo $page_title; ?><br>
                                    <?php echo $date_start . ' until ' . $date_end ?>
                                </h3>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Business -->
                        <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                            <td style="width:80%;">Category: Business</td>
                            <td style="width:20%;" class="text-right">Amount</td>
                        </tr>
                        <?php 
                        $cnt = 0;
                        foreach ($data_row_b as $bill_id => $row) {
                            if ((in_array($bill_id, $display_col)) || (empty($display_col))) {
                                $style = ($cnt % 2 == 0) 
                                    ? "style='background-color: #d8e6f0;'" 
                                    : "style='background-color: #f6f8f9;'";
                        ?>
                        <tr <?php echo $style; ?>>
                            <td><?php echo $bill_type_list[$bill_id]['name']; ?></td>
                            <td class="text-right"><?php echo number_format($row['amount'], 2, '.', ','); ?></td>
                        </tr>
                        <?php $cnt++; } } ?>
                        <tr <?php echo ($cnt % 2 == 0) ? "style='background-color: #d8e6f0;'" : "style='background-color: #f6f8f9;'"; ?>>
                            <td><b>Total:</b></td>
                            <td class="text-right"><b><?php echo number_format($total_b, 2, '.', ','); ?></b></td>
                        </tr>

                        <!-- Residential -->
                        <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                            <td>Category: Residential</td>
                            <td class="text-center">Amount</td>
                        </tr>
                        <?php 
                        $cnt = 0;
                        foreach ($data_row_r as $bill_id => $row) {
                            if ((in_array($bill_id, $display_col)) || (empty($display_col))) {
                                $style = ($cnt % 2 == 0) 
                                    ? "style='background-color: #d8e6f0;'" 
                                    : "style='background-color: #f6f8f9;'";
                        ?>
                        <tr <?php echo $style; ?>>
                            <td><?php echo $bill_type_list[$bill_id]['name']; ?></td>
                            <td class="text-right"><?php echo number_format($row['amount'], 2, '.', ','); ?></td>
                        </tr>
                        <?php $cnt++; } } ?>
                        <tr <?php echo ($cnt % 2 == 0) ? "style='background-color: #d8e6f0;'" : "style='background-color: #f6f8f9;'"; ?>>
                            <td><b>Total:</b></td>
                            <td class="text-right"><b><?php echo number_format($total_r, 2, '.', ','); ?></b></td>
                        </tr>

                        <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                            <td>DIA</td>
                            <td class="text-center">Amount</td>
                        </tr>
                        <?php 
                        $cnt = 0;
                        foreach ($data_row_d as $bill_id => $row) {
                            if ((in_array($bill_id, $display_col)) || (empty($display_col))) {
                                $style = ($cnt % 2 == 0) 
                                    ? "style='background-color: #d8e6f0;'" 
                                    : "style='background-color: #f6f8f9;'";
                        ?>
                        <tr <?php echo $style; ?>>
                            <td><?php echo $bill_type_list[$bill_id]['name']; ?></td>
                            <td class="text-right">
                                <?php 
                                    $amount_d = $row['amount'] ?? 0;
                                    $amount_e = $data_row_e[$bill_id]['amount'] ?? 0;
                                    echo number_format($amount_d + $amount_e, 2, '.', ','); 
                                ?>
                            </td>
                        </tr>
                        <?php $cnt++; } } ?>
                        <tr <?php echo ($cnt % 2 == 0) ? "style='background-color: #d8e6f0;'" : "style='background-color: #f6f8f9;'"; ?>>
                            <td><b>Total:</b></td>
                            <td class="text-right"><b><?php echo number_format($total_d, 2, '.', ','); ?></b></td>
                        </tr>

                        <!-- Wholesale -->
                        <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                            <td>Category: Wholesale</td>
                            <td class="text-center">Amount</td>
                        </tr>
                        <?php 
                        $cnt = 0;
                        foreach ($data_row_w as $bill_id => $row) {
                            if ((in_array($bill_id, $display_col)) || (empty($display_col))) {
                                $style = ($cnt % 2 == 0) 
                                    ? "style='background-color: #d8e6f0;'" 
                                    : "style='background-color: #f6f8f9;'";
                        ?>
                        <tr <?php echo $style; ?>>
                            <td><?php echo $bill_type_list[$bill_id]['name']; ?></td>
                            <td class="text-right"><?php echo number_format($row['amount'], 2, '.', ','); ?></td>
                        </tr>
                        <?php $cnt++; } } ?>
                        <tr <?php echo ($cnt % 2 == 0) ? "style='background-color: #d8e6f0;'" : "style='background-color: #f6f8f9;'"; ?>>
                            <td><b>Total:</b></td>
                            <td class="text-right"><b><?php echo number_format($total_w, 2, '.', ','); ?></b></td>
                        </tr>

                        <!-- Grand Total -->
                        <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                            <td>Grand Total</td>
                            <td class="text-right"><b><?php echo number_format($total_all, 2, '.', ','); ?></b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<script src="<?php echo base_url("js/itelco/report.js?".cssjs_ver()); ?>" ></script>
