<div id="table_wrapper">
    <h3 class="text-center" style="margin-bottom: 20px;">
        <?= $page_title ?><br>
        <?= $date_start . ' until ' . $date_end ?>
    </h3>

    <?php foreach ($data_row['billing'] as $building_no => $building_data): ?>
        <fieldset style="margin-bottom: 30px; border: 2px solid #289383; border-radius: 10px; padding: 10px;">
            <legend style="padding-left: 15px; font-size:18px; font-weight:bold; color:#289383;">
                Building: <?= htmlspecialchars($building_list[$building_no] ?? 'No Building') ?>
            </legend>

            <table class="table-striped report_table" style="width:100%;">
                <thead>
                    <tr class="report_table_header report_th" style="background: #289383; font-weight: bold; color: #fff;">
                        <th class="col-lg-1" <?= $isprint ? 'style="width:10%;"' : '' ?>>Doc No</th>
                        <th class="col-lg-1" <?= $isprint ? 'style="width:10%;"' : '' ?>>Bill Date</th>
                        <th class="col-lg-1" <?= $isprint ? 'style="width:10%;"' : '' ?>>Customer No</th>

                        <?php 
                            $name_col_width = 8 - min(count($data_row['bill_type'][$building_no]), 5);
                            $name_width = 60 - min((count($data_row['bill_type'][$building_no]) * 10), 40);
                        ?>
                        <th class="col-lg-<?= $name_col_width ?>" <?= $isprint ? 'style="width: '.$name_width.';"' : '' ?>>Name</th>

                        <?php $idx = 0; ?>
                        <?php foreach ($data_row['bill_type'][$building_no] as $key => $val): ?>
                            <th class="text-center col-lg-1 <?= $idx >= 3 ? 'hidden-print' : '' ?>"
                                <?= $isprint ? 'style="width:10%;"' : '' ?>>
                                <?= $val ?>
                            </th>
                            <?php $idx++; ?>
                        <?php endforeach; ?>

                        <?php if (count($data_row['bill_type'][$building_no]) > 3): ?>
                            <th class="text-right col-lg-1 visible-print" <?= $isprint ? 'style="width:10%;"' : '' ?>>
                                [Other Bill Type]
                            </th>
                        <?php endif; ?>

                        <th class="col-lg-1 text-center" <?= $isprint ? 'style="width:10%;"' : '' ?>>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($building_data as $category_name => $customers): ?>
                        <tr class="cat_header">
                            <td colspan="100%"><h5><?= $category_name ?></h5></td>
                        </tr>

                        <?php foreach ($customers as $cust): ?>
                            <tr class="cust_record">
                                <td class=""><?= htmlspecialchars($cust['bill_no']) ?></td>
                                <td class=""><?= htmlspecialchars($cust['bill_date']) ?></td>
                                <td class=" text-center"><?= htmlspecialchars($cust['customer_no']) ?></td>
                                <td class=""><?= ucwords(strtolower($cust['customer_name'])) ?></td>

                                <?php $idx = 0; ?>
                                <?php foreach ($data_row['bill_type'][$building_no] as $key => $val): ?>
                                    <?php 
                                        $bill_idx = array_search($key, $cust['bill_type']);
                                        $has_value = ($bill_idx !== false);
                                    ?>
                                    <td class="text-right  data-col-<?= $key ?> <?= $idx >= 3 ? 'hidden-print' : '' ?>">
                                        <?= $has_value ? number_format($cust['total_amount'][$bill_idx], 2, '.', ',') : '' ?>
                                    </td>
                                    <?php $idx++; ?>
                                <?php endforeach; ?>

                                <?php if (count($data_row['bill_type'][$building_no]) > 3): ?>
                                    <td class="text-right  visible-print">
                                        <?= ($cust['other_bill_type_total_amount_in_print'] === '') 
                                            ? '' 
                                            : number_format($cust['other_bill_type_total_amount_in_print'], 2, '.', ',') ?>
                                    </td>
                                <?php endif; ?>

                                <td class="text-right ">
                                    <?= number_format($cust['all_item_total_amount'], 2, '.', ',') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="col_subtotal">
                            <td class="text-right" colspan="4">
                                <strong>Subtotal (<?= $category_name . ' in ' . htmlspecialchars($building_list[$building_no] ?? 'No Building') ?>)</strong>
                            </td>

                            <?php $idx = 0; ?>
                            <?php foreach ($data_row['bill_type'][$building_no] as $key => $val): ?>
                                <?php $subtotal = $data_row['subtotal'][$building_no][$category_name][$key] ?? 0; ?>
                                <td class="text-right top-bottom-bordered <?= $idx >= 3 ? 'hidden-print' : '' ?>">
                                    <strong><?= number_format($subtotal, 2, '.', ',') ?></strong>
                                </td>
                                <?php $idx++; ?>
                            <?php endforeach; ?>

                            <?php if (count($data_row['bill_type'][$building_no]) > 3): ?>
                                <td class="text-right top-bottom-bordered visible-print">
                                    <strong>
                                        <?= number_format($data_row['subtotal'][$building_no][$category_name]['other_for_print_subtotal'] ?? 0, 2, '.', ',') ?>
                                    </strong>
                                </td>
                            <?php endif; ?>

                            <td class="text-right top-bottom-bordered">
                                <strong>
                                    <?= number_format($data_row['subtotal'][$building_no][$category_name]['category_subtotal'] ?? 0, 2, '.', ',') ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="grand_total">
                        <td class="text-right" colspan="4">
                            <strong>Grand Total (All categories in <?= htmlspecialchars($building_list[$building_no] ?? 'No Building') ?>)</strong>
                        </td>

                        <?php $idx = 0; ?>
                        <?php foreach ($data_row['bill_type'][$building_no] as $key => $val): ?>
                            <td class="text-right top-bottom-bordered <?= $idx >= 3 ? 'hidden-print' : '' ?>">
                                <strong><?= number_format($data_row['grandtotal'][$building_no][$key] ?? 0, 2, '.', ',') ?></strong>
                            </td>
                            <?php $idx++; ?>
                        <?php endforeach; ?>

                        <?php if (count($data_row['bill_type'][$building_no]) > 3): ?>
                            <td class="text-right top-bottom-bordered visible-print">
                                <strong><?= number_format($data_row['grandtotal'][$building_no]['other_for_print_grand_total'] ?? 0, 2, '.', ',') ?></strong>
                            </td>
                        <?php endif; ?>

                        <td class="text-right top-bottom-bordered">
                            <strong><?= number_format($data_row['grandtotal'][$building_no]['category_grand_total'], 2, '.', ',') ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    <?php endforeach; ?>

    <fieldset style="border: 2px solid #942932ff; border-radius: 10px; padding: 10px;">
        <legend style="padding-left: 15px; font-size:18px; font-weight:bold; color:#942932ff;">
            Overall Summary (All Buildings)
        </legend>

        <table class="table-striped report_table" style="width:100%; font-weight:bold;">
            <thead>
                <tr class="report_table_header report_th" style="background: #942932ff !important; color: #fff;">
                    <th class="col-lg-11 text-left" <?= $isprint ? 'style="width:10%;"' : '' ?>>Category</th>
                    <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:90%;"' : '' ?>>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_row['summary_categories'] as $cat): ?>
                    <tr>
                        <td class="text-left"><?= htmlspecialchars($cat) ?></td>
                        <td class="text-right">
                            <?= number_format($data_row['category_totals'][$cat], 2, '.', ',') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr class="grand_total" style="font-weight:bold;">
                    <td class="text-right" style="padding-right: 1rem;">Grand Total</td>
                    <td class="text-right top-bottom-bordered">
                        <?= number_format($data_row['overall_grand_total'], 2, '.', ',') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>
