<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading noprint panel_fontsize">
            <span class="panel_space float_right">
                <a href="<?= $return_link; ?>">
                    <i class="menu-icon fa fa-times red" <?= tooltip_helper('Cancel / Discard'); ?>></i>
                </a>
            </span>
            <?= $page_title; ?>
        </div>

        <div class="panel-body">
            <?= flash_data_helper($msg); ?>

            <form id="dealer_detail_comm" name="dealer_detail_comm" method="post" class="filter-form" action="<?= $form_action; ?>">
                <input type="hidden" id="dealer_no" name="dealer_no" value="<?= $dealer['dealer_no'] ?>">

                <fieldset class="category-border-main">
                    <div class="category-border-main bg-success text-center">
                        Package List - <?= $dealer['name']; ?>
                    </div>

                    <?php foreach ($category_list as $category_key => $category_val): ?>
                        <?php
                            if (!isset(${$category_key}) || count(${$category_key}) === 0) continue;
                            $no_upline = isset($upline) && empty($upline);
                        ?>
                        <div class="col-lg-12">
                            <fieldset class="category-border">
                                <legend class="category-border"><?= $category_val ?></legend>
                                <div class="col-12 <?= $no_upline ? 'col-lg-8' : 'col-lg-7'; ?>">

                                    <div class="table-responsive">
                                        <table class="table-striped category-table">
                                            <thead class="bg-info">
                                                <tr>
                                                    <th class="col-lg-4">Package Name</th>
                                                    <th class="col-lg-1 text-center">By</th>
                                                    <th class="col-lg-1 text-center">Monthly %</th>
                                                    <th class="col-lg-1 text-center">Monthly Times</th>
                                                    <th class="col-lg-1 text-center">One Time</th>
                                                    <?php if ($no_upline): ?>
                                                        <th class="col-lg-1 text-center">Follow Package</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if ($no_upline): ?>
                                                    <tr class="category-master" style="background-color: #fcf2e0;">
                                                        <td></td>
                                                        <td class="text-center">
                                                            <select class="col-lg-12 master-field" data-field="comm_type">
                                                                <option value="m">Monthly</option>
                                                                <option value="o">One Time</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="text" class="form-control master-field" data-field="monthly">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="text" class="form-control master-field" data-field="monthly_times">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="text" class="form-control master-field" data-field="onetime">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="checkbox" class="master-field" data-field="follow_package">
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>

                                                <?php foreach (${$category_key} as $val): 
                                                    $pkg_no = $val['package_no'];
                                                    $by_type = $val['comm_type'] ?? 'm';
                                                    $monthly_val = $val['monthly'] ?? 0;
                                                    $monthly_times_val = $val['monthly_times'] ?? 1;
                                                    $onetime_val = $val['onetime'] ?? 0;
                                                    $follow_package = $val['follow_package'] ?? 0;
                                                ?>
                                                    <tr>
                                                        <td><?= $val['name']; ?></td>

                                                        <?php if ($no_upline): ?>
                                                            <td class="text-center">
                                                                <select class="col-lg-12" name="comm_type[<?= $pkg_no; ?>]">
                                                                    <option value="m" <?= $by_type === 'm' ? 'selected' : ''; ?>>Monthly</option>
                                                                    <option value="o" <?= $by_type === 'o' ? 'selected' : ''; ?>>One Time</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control" name="monthly[<?= $pkg_no; ?>]" value="<?= $monthly_val; ?>">
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control" name="monthly_times[<?= $pkg_no; ?>]" value="<?= $monthly_times_val; ?>">
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control" name="onetime[<?= $pkg_no; ?>]" value="<?= $onetime_val; ?>">
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="checkbox" name="follow_package[<?= $pkg_no; ?>]" value="1" <?= $follow_package ? 'checked' : ''; ?>>
                                                            </td>
                                                        <?php else: ?>
                                                            <td class="text-center">
                                                                <select class="col-lg-12" disabled>
                                                                    <option value="m" <?= $by_type === 'm' ? 'selected' : ''; ?>>Monthly</option>
                                                                    <option value="o" <?= $by_type === 'o' ? 'selected' : ''; ?>>One Time</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control" value="<?= $monthly_val; ?>" readonly>
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control" value="<?= $monthly_times_val; ?>" readonly>
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control" value="<?= $onetime_val; ?>" readonly>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </fieldset>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <div class="col-md-12">
                    <div>
                        <button type="button" class="btn btn-warning" onclick="window.location.href='<?= $return_link; ?>';">
                            <i class="menu-icon fa fa-times white"></i> Cancel
                        </button>

                        <?php if ($no_upline): ?>
                            <button type="submit" class="btn btn-success">
                                <i class="menu-icon fa fa-save white"></i> Save
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div id="clear" style="clear:both;"></div>

    <div id="popupDetail">
        <div id="popupDetailStd" onclick="disablePopup();">
            <div id="popupContent">&nbsp;</div>
        </div>
    </div>
</div>

<div id="backgroundPopup" onclick="hide_popup();"></div>

<script src="<?= base_url("js/itelco/dealer.js?" . cssjs_ver()); ?>"></script>
<script src="<?= base_url("js/itelco/dealer_comm.js?" . cssjs_ver()); ?>"></script>
