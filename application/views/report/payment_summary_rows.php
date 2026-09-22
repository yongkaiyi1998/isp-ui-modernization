<div class="table-wrapper">
    <table class="table-bordered sticky_table">
        <thead>
            <tr>
                <th colspan="100%">
                    <h3 class="text-center">
                        <?php echo $page_title; ?><br>
                        <?php echo $date_start . ' until ' . $date_end ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th">
                <th class="text-left col-lg-1 sticky">
                    Category
                </th>
                <?php foreach ($bill_type_list as $bill_id => $bill) { ?>
                <th class="text-center col-lg-1 bill_list_th" data-id="<?php echo $bill_id; ?>">
                    <?php echo $bill['name']; ?>
                </th>
                <?php } ?>
                <th class="text-center col-lg-1">
                    <strong>Total</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$initial) { ?>
            <tr>
                <td class="sticky">
                    Business
                </td>
                <?php 
                foreach ($data_row_b as $bill_id => $row) {
                    echo "<td class='text-right' data-id='".$bill_id."'>" . number_format($row['amount'], 2, '.', ',') . "</td>";
                } 
                echo "<td class='text-right'><strong>" . number_format($total_b, 2, '.', ',') . "</strong></td>";
                ?>
            </tr>
            <tr>
                <td class="sticky">
                    Residential
                </td>
                <?php 
                foreach ($data_row_r as $bill_id => $row) {
                    echo "<td class='text-right' data-id='".$bill_id."'>" . number_format($row['amount'], 2, '.', ',') . "</td>";
                } 
                echo "<td class='text-right'><strong>" . number_format($total_r, 2, '.', ',') . "</strong></td>";
                ?>
            </tr>
            <tr>
                <td class="sticky">
                    DIA
                </td>
                <?php 
                foreach ($data_row_d as $bill_id => $row) {
                    echo "<td class='text-right' data-id='".$bill_id."'>" . number_format($row['amount'], 2, '.', ',') . "</td>";
                } 
                echo "<td class='text-right'><strong>" . number_format($total_d, 2, '.', ',') . "</strong></td>";
                ?>
            </tr>
            <tr>
                <td class="sticky">
                    Wholesale
                </td>
                <?php 
                foreach ($data_row_w as $bill_id => $row) {
                    echo "<td class='text-right' data-id='".$bill_id."'>" . number_format($row['amount'], 2, '.', ',') . "</td>";
                } 
                echo "<td class='text-right'><strong>" . number_format($total_w, 2, '.', ',') . "</strong></td>";
                ?>
            </tr>
            <?php } ?>
            <tr>
                <td class="sticky">
                    <strong>Total</strong>
                </td>
                <?php 
                foreach ($bill_type_list as $bill_id => $row) {
                    echo "<td class='text-right' data-id='".$bill_id."'><strong>" . number_format($row['total'], 2, '.', ',') . "</strong></td>";
                }
                echo "<td class='text-right'><strong>" . number_format($total_all, 2, '.', ',') . "</strong></td>";
                ?>
            </tr>
        </tbody>
    </table>
</div>