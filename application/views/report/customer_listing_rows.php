<?php
function getSortIcon($columnId, $orderBy, $orderType) {
    if ($orderBy == $columnId) {
        return ($orderType == 'desc') ? 'fa-sort-desc' : 'fa-sort-asc';
    }
    return 'fa-sort';
}
?>

<div id="table_wrapper">
    <table class="table-striped" style="width:100%;">
        <thead>
            <tr>
                <th colspan="100%">
                    <h3 class="text-center">
                        <?php echo $page_title; ?><br>
                        <span class="small"><?php echo $filter_text; ?></span>
                    </h3>
                </th>
            </tr>
            <tr class="report_th">
                <th class="text-left" style="width:40px;">#</th>
                <th class="text-left" style="width:80px;">No / Name 
                    <i class="menu-icon fa <?php echo getSortIcon('customer_name', $order_by, $order_type); ?>" id="customer_name"></i>
                </th>
                <th class="text-center">Login
                    <i class="menu-icon fa <?php echo getSortIcon('login_username', $order_by, $order_type); ?>" id="login_username"></i>
                </th>
                <th class="text-left">First Activate
                    <i class="menu-icon fa <?php echo getSortIcon('first_activate', $order_by, $order_type); ?>" id="first_activate"></i>
                </th>
                <th class="text-center">Mobile
                    <i class="menu-icon fa <?php echo getSortIcon('mobile_num', $order_by, $order_type); ?>" id="mobile_num"></i>
                </th>
                <th class="text-center">Email
                    <i class="menu-icon fa <?php echo getSortIcon('email_1', $order_by, $order_type); ?>" id="email_1"></i>
                </th>
                <th class="text-center">Status
                    <i class="menu-icon fa <?php echo getSortIcon('status', $order_by, $order_type); ?>" id="status"></i>
                </th>
                <th class="text-center">Status Date
                    <i class="menu-icon fa <?php echo getSortIcon('status_date', $order_by, $order_type); ?>" id="status_date"></i>
                </th>
                <th class="text-center">Agent
                    <i class="menu-icon fa <?php echo getSortIcon('dealer', $order_by, $order_type); ?>" id="dealer"></i>
                </th>
                <th class="text-center">Pack.
                    <i class="menu-icon fa <?php echo getSortIcon('package_name', $order_by, $order_type); ?>" id="package_name"></i>
                </th>
                <th class="text-center">Pack.<br>Changed
                    <i class="menu-icon fa <?php echo getSortIcon('package_changed_date', $order_by, $order_type); ?>" id="package_changed_date"></i>
                </th>
                <th class="text-center">Monthly
                    <i class="menu-icon fa <?php echo getSortIcon('monthly_charge', $order_by, $order_type); ?>" id="monthly_charge"></i>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($data_row as $key => $val) {
                $row_bg = ($key % 2 == 0) ? '#f6f8f9' : '#d8e6f0';
                $base_style = "background-color: $row_bg; vertical-align: middle;";
                
                $address_parts = array_filter([
                    trim(($val['building'] ?? '') . ' ' . ($val['inst_unit_no'] ?? '')),
                    $val['inst_addr1'] ?? '',
                    $val['inst_addr2'] ?? '',
                    $val['inst_addr3'] ?? '',
                    $val['inst_city'] ?? '',
                    $val['inst_postcode'] ?? '',
                    $val['inst_state'] ?? ''
                ]);
                $full_address = implode(', ', array_filter($address_parts));
            ?>
                <tr style="<?php echo $base_style; ?> border-top: 1px solid #dee2e6;">
                    <td class="text-center"><strong><?php echo $val['no']; ?></strong></td>
                    <td>
                        <strong><?php echo $val['customer_no']; ?></strong><br>
                        <strong><?php echo $val['name']; ?></strong>
                    </td>
                    <td class="text-center"><?php echo $val['login_username']; ?></td>
                    <td class="text-center"><?php echo $val['first_activate']; ?></td>
                    <td class="text-center"><?php echo $val['mobile_num']; ?></td>
                    <td class="text-center" style="font-size: 0.9em;">
                        <?php echo $val['email_1'] . ($val['email_2'] != '' ? '<br />' . $val['email_2'] : ''); ?>
                    </td>
                    <td class="text-center"><?php echo $val['status']; ?></td>
                    <td class="text-center"><?php echo $val['status_date']; ?></td>
                    <td class="text-center"><?php echo $val['dealer']; ?></td>
                    <td class="text-center"><?php echo $val['package_name']; ?></td>
                    <td class="text-center"><?php echo $val['package_start']; ?></td>
                    <td class="text-center"><?php echo $val['monthly_charge']; ?></td>
                </tr>
                <tr style="<?php echo $base_style; ?>">
                    <td></td>
                    <td></td>
                    <td colspan="10" style="padding-top: 0; padding-bottom: 10px; color: #666; font-size: 0.85em; font-style: italic;">
                        <i class="fa fa-map-marker" style="margin-right: 5px;"></i>
                        <?php echo $full_address; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>