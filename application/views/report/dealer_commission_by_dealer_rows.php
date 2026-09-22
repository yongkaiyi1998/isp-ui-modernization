<div class="table-responsive">
    <table class="table-striped" width="100%">
        <thead>
            <tr>
                <th colspan="5">
                    <h3 class="text-center">
                        <?php echo $page_title; ?><br>
                        <?php echo $date_start . ' until ' . $date_end ?>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th>Dealer</th>
                <th class="text-right"># Customers</th>
                <th class="text-right"># Bills</th>
                <th class="text-right">Total Bill Amount</th>
                <th class="text-right">Total Commission</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_row as $val) { ?>
                <tr>
                    <td><?php echo $val['dealer_name']; ?></td>
                    <td class="text-right"><?php echo $val['total_customers']; ?></td>
                    <td class="text-right"><?php echo $val['total_bills']; ?></td>
                    <td class="text-right"><?php echo number_format($val['total_bill_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($val['total_commission'], 2); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>