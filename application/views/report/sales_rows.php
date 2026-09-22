<div id="table_wrapper">
    <h3 class="text-center" style="margin-bottom: 20px;">
        <?= $page_title ?><br>
        <?php if (!empty($date_start) && !empty($date_end)) { ?>First Activated Between <?= $date_start . ' until ' . $date_end ?><?php } ?>
    </h3>

    <fieldset style="border: 2px solid #942932ff; border-radius: 10px; padding: 10px;">
        <legend style="padding-left: 15px; font-size:18px; font-weight:bold; color:#942932ff;">
            Package Count
        </legend>

        <table class="table-striped report_table" style="width:100%; font-weight:bold;">
            <thead>
                <tr class="report_table_header report_th" style="background: #942932ff !important; color: #fff;">
                    <th class="col-lg-3 text-left" <?= $isprint ? 'style="width:10%;"' : '' ?>>Package</th>
                    <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:90%;"' : '' ?>>Package Price</th>
                    <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:90%;"' : '' ?>>Subscriber Total</th>
                </tr>
            </thead>

            <tbody>
                <?php $pkg_cnt = 0; ?>
                <?php foreach ($pkg_list as $pkg_row) { ?>
                <tr>
                    <td class="text-left"><?= htmlspecialchars($pkg_row['package_name']) ?></td>
                    <td class="text-right">
                        <?= $pkg_row['monthly_charge'] ?>
                    </td>
                    <td class="text-right">
                        <?= $pkg_row['cnt'] ?>
                    </td>
                </tr>
                <?php $pkg_cnt = $pkg_cnt + $pkg_row['cnt']; ?>
                <?php } ?>

                <tr class="grand_total" style="font-weight:bold;">
                    <td colspan="2" class="text-right" style="padding-right: 1rem;">Grand Total</td>
                    <td class="text-right top-bottom-bordered">
                        <?= $pkg_cnt ?>
                    </td>
                </tr>
            </tbody>
        </table>

    </fieldset>
    <br><br>

    <?php $grand_total_array = []; ?>

    <?php foreach ($data_row as $building_no => $building_data): ?>
        <fieldset style="margin-bottom: 30px; border: 2px solid #289383; border-radius: 10px; padding: 10px;">
            <legend style="padding-left: 15px; font-size:18px; font-weight:bold; color:#289383;">
                Building: <?= htmlspecialchars($building_list[$building_no] ?? 'No Building') ?>
            </legend>

            <table class="table-striped report_table" style="width:100%">
                <thead>
                    <tr class="report_table_header report_th" style="background: #289383; font-weight: bold; color: #fff;">
                        <th class="col-lg-1" <?= $isprint ? 'style="width:10%;"' : '' ?>>Customer No</th>
                        <th class="col-lg-2" <?= $isprint ? 'style="width:10%;"' : '' ?>>Name</th>
                        <th class="col-lg-4" <?= $isprint ? 'style="width:10%;"' : '' ?>>Package</th>
                        <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:10%;"' : '' ?>>Subscription</th>
                        <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:10%;"' : '' ?>>MRC Charges</th>
                        <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:10%;"' : '' ?>>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $b_grand_subscription_total = 0; ?>
                    <?php $b_grand_otc_total = 0; ?>
                    <?php $b_grand_cat_total = 0; ?>
                    <?php foreach ($building_data as $category_name => $customers): ?>
                        <tr class="cat_header">
                            <td colspan="100%"><h5><?= htmlspecialchars($category_list[$category_name] ?? $category_name) ?></h5></td>
                        </tr>

                        <?php $subscription_total = 0; ?>
                        <?php $otc_total = 0; ?>
                        <?php $cat_total = 0; ?>

                        <?php foreach ($customers as $cust): ?>
                            <?php $row_total = 0; ?>
                            <tr class="cust_record main_info_table">
                                 <td class="<?= $isprint ? 'small' : '' ?>"><?= htmlspecialchars($cust['customer_no']) ?></td>
                                 <td class="<?= $isprint ? 'small' : '' ?>"><?= ucwords(strtolower($cust['name'])) ?></td>
                                 <td class="<?= $isprint ? 'small' : '' ?>"><?= ucwords(strtolower($cust['package_name'])) ?></td>
                                 <td class=" text-right <?= $isprint ? 'small' : '' ?>"><?= number_format($cust['monthly_charge'], 2, '.', ',') ?></td>
                                 <td class=" text-right <?= $isprint ? 'small' : '' ?>"><?= number_format($cust['otc_charges'], 2, '.', ',') ?></td>
                                 <?php $row_total = $cust['monthly_charge'] + $cust['otc_charges']; ?>
                                 <?php $subscription_total = $subscription_total + $cust['monthly_charge']; ?>
                                 <?php $otc_total = $otc_total + $cust['otc_charges']; ?>
                                 <?php $cat_total = $cat_total + $row_total; ?>
                                 <td class=" text-right <?= $isprint ? 'small' : '' ?>"><?= number_format($row_total, 2, '.', ',') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="col_subtotal">
                            <td class="text-right" colspan="3">
                                <strong>Subtotal (<?= $category_list[$category_name] . ' in ' . htmlspecialchars($building_list[$building_no] ?? 'No Building') ?>)</strong>
                            </td>
                            <td class="text-right top-bottom-bordered"><strong><?= number_format($subscription_total, 2, '.', ',') ?></strong></td>
                            <td class="text-right top-bottom-bordered"><strong><?= number_format($otc_total, 2, '.', ',') ?></strong></td>
                            <td class="text-right top-bottom-bordered"><strong><?= number_format($cat_total, 2, '.', ',') ?></strong></td>

                        </tr>
                        <?php $b_grand_subscription_total = $b_grand_subscription_total + $subscription_total; ?>
                        <?php $b_grand_otc_total = $b_grand_otc_total + $otc_total; ?>
                        <?php $b_grand_cat_total = $b_grand_cat_total + $cat_total; ?>

                        <?php 
                        if (!isset($grand_total_array[$category_list[$category_name]])) {
                            $grand_total_array[$category_list[$category_name]]['subscription'] = 0;
                            $grand_total_array[$category_list[$category_name]]['otc'] = 0;
                            $grand_total_array[$category_list[$category_name]]['cat'] = 0;
                        }

                        $grand_total_array[$category_list[$category_name]]['subscription'] = $grand_total_array[$category_list[$category_name]]['subscription'] + $subscription_total;
                        $grand_total_array[$category_list[$category_name]]['otc'] = $grand_total_array[$category_list[$category_name]]['otc'] + $otc_total;
                        $grand_total_array[$category_list[$category_name]]['cat'] = $grand_total_array[$category_list[$category_name]]['cat'] + $cat_total;
                        ?>
                    <?php endforeach; ?>

                    <tr class="grand_total">
                       <td class="text-right" colspan="3">
                            <strong>Grand Total (All categories in <?= htmlspecialchars($building_list[$building_no] ?? 'No Building') ?>)</strong>
                        </td>
                        <td class="text-right top-bottom-bordered"><strong><?= number_format($b_grand_subscription_total, 2, '.', ',') ?></strong></td>
                        <td class="text-right top-bottom-bordered"><strong><?= number_format($b_grand_otc_total, 2, '.', ',') ?></strong></td>
                        <td class="text-right top-bottom-bordered"><strong><?= number_format($b_grand_cat_total, 2, '.', ',') ?></strong></td>
                    </tr>
                </tbody>
            </table>

        </fieldset>
    <?php endforeach; ?>

    <fieldset style="border: 2px solid #942932ff; border-radius: 10px; padding: 10px;">
        <legend style="padding-left: 15px; font-size:18px; font-weight:bold; color:#942932ff;">
            Overall Summary (All Buildings)
        </legend>

        <?php $gcat_subscription_total = 0; ?>
        <?php $gcat_otc_total = 0; ?>
        <?php $gcat_cat_total = 0; ?>

        <table class="table-striped report_table" style="width:100%; font-weight:bold;">
            <thead>
                <tr class="report_table_header report_th" style="background: #942932ff !important; color: #fff;">
                    <th class="col-lg-8 text-left" <?= $isprint ? 'style="width:10%;"' : '' ?>>Category</th>
                    <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:90%;"' : '' ?>>Subscription</th>
                    <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:90%;"' : '' ?>>MRC Charges</th>
                    <th class="col-lg-1 text-right" <?= $isprint ? 'style="width:90%;"' : '' ?>>Total</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($grand_total_array as $gcat_text => $gcat) { ?>
                    <tr>
                        <td class="text-left"><?= htmlspecialchars($gcat_text) ?></td>
                        <td class="text-right">
                            <?= number_format($gcat['subscription'], 2, '.', ',') ?>
                        </td>
                        <td class="text-right">
                            <?= number_format($gcat['otc'], 2, '.', ',') ?>
                        </td>
                        <td class="text-right">
                            <?= number_format($gcat['cat'], 2, '.', ',') ?>
                        </td>
                    </tr>

                    <?php $gcat_subscription_total = $gcat_subscription_total + $gcat['subscription']; ?>
                    <?php $gcat_otc_total = $gcat_otc_total + $gcat['otc']; ?>
                    <?php $gcat_cat_total = $gcat_cat_total + $gcat['cat']; ?>
                <?php } ?>

                <tr class="grand_total" style="font-weight:bold;">
                    <td class="text-right" style="padding-right: 1rem;">Grand Total</td>
                        <td class="text-right top-bottom-bordered">
                            <?= number_format($gcat_subscription_total, 2, '.', ',') ?>
                        </td>
                        <td class="text-right top-bottom-bordered">
                            <?= number_format($gcat_otc_total, 2, '.', ',') ?>
                        </td>
                        <td class="text-right top-bottom-bordered">
                            <?= number_format($gcat_cat_total, 2, '.', ',') ?>
                        </td>
                </tr>

            </tbody>

        </table>

    </fieldset>

</div>
