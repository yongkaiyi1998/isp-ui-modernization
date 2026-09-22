<?php
function getSortIcon($columnId, $orderBy, $orderType) {
    if ($orderBy == $columnId) {
        return ($orderType == 'desc') ? 'fa-sort-desc' : 'fa-sort-asc';
    }
    return 'fa-sort';
}

$isPrint = ($print == 1);
$printWidth = $isPrint ? 'style="width:10%;"' : '';
?>

<div id="table_wrapper">
    <table class="table-striped" style="width:100%;">
        <thead>
            <tr>
                <th colspan="9">
                    <h3 class="text-center">
                        <?php echo $page_title; ?><br>
                        <span class="small"><?php echo $filter_text; ?></span>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="text-left" style="width:80px;">Acc.No</th>
                <th class="text-left" style="width:80px;">Name 
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('customer_name', $order_by, $order_type); ?>" id="customer_name"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>Login
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('login_username', $order_by, $order_type); ?>" id="login_username"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>Mobile
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('mobile_num', $order_by, $order_type); ?>" id="mobile_num"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>Email
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('email_1', $order_by, $order_type); ?>" id="email_1"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>First Activated
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('activated_status_date', $order_by, $order_type); ?>" id="activated_status_date"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>Terminated
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('status_date', $order_by, $order_type); ?>" id="status_date"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>Agent
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('dealer', $order_by, $order_type); ?>" id="dealer"></i>
                    <?php endif; ?>
                </th>
                <th class="text-center" <?php echo $printWidth; ?>>Last Pack.
                    <?php if (!$isPrint): ?>
                        <i class="menu-icon fa <?php echo getSortIcon('package_name', $order_by, $order_type); ?>" id="package_name"></i>
                    <?php endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_row as $key => $val): 
                $row_bg = ($key % 2 == 0) ? '#f6f8f9' : '#d8e6f0';
                $base_style = "background-color: $row_bg; vertical-align: middle;";
                
                $addr = array_filter([
                    !empty($val['building']) ? $val['building'] : '',
                    $val['inst_unit_no'],
                    $val['inst_addr1'],
                    $val['inst_addr2'],
                    $val['inst_city'],
                    $val['inst_postcode'],
                    $val['inst_state']
                ]);
            ?>
                <tr style="<?php echo $base_style; ?> border-top: 1px solid #dee2e6;">
                    <td class="text-center"><strong><?php echo $val['customer_no']; ?></strong></td>
                    <td><strong><?php echo $val['name']; ?></strong></td>
                    <td class="text-center"><?php echo $val['login_username']; ?></td>
                    <td class="text-center"><?php echo $val['mobile_num']; ?></td>
                    <td class="text-center" style="font-size: 0.9em;">
                        <?php echo $val['email_1'] . ($val['email_2'] != '' ? '<br />' . $val['email_2'] : ''); ?>
                    </td>
                    <td class="text-center"><?php echo $val['activated_date']; ?></td>
                    <td class="text-center"><?php echo $val['status_date']; ?></td>
                    <td class="text-center"><?php echo $val['dealer']; ?></td>
                    <td class="text-center"><?php echo $val['package_name']; ?></td>
                </tr>
                <tr style="<?php echo $base_style; ?>">
                    <td></td>
                    <td></td>
                    <td colspan="7" style="padding-top: 0; padding-bottom: 10px; color: #666; font-size: 0.85em; font-style: italic;">
                        <i class="fa fa-map-marker" style="margin-right: 5px;"></i>
                        <?php echo implode(', ', array_filter($addr)); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>