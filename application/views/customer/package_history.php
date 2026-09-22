<div class="hidden-xs" style="padding: 20px;">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="col-lg-3">Package Name</th>
                <th class="col-lg-2">Monthly Charge</th>
                <th class="col-lg-2">Start Date</th>
                <th class="col-lg-2">End Date</th>
                <th class="col-lg-3">Installation Address</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($row_data as $row): ?>

            <?php
            $full_address = "";

            if (!empty($row['building_name'])) {
                $full_address .= $row['building_name']." ";

                if (!empty($row['inst_unit_no'])) {
                    $full_address .= $row['inst_unit_no'].",\n";
                }
            }

            if (!empty($row['inst_addr1']))
                $full_address .= $row['inst_addr1'].",\n";

            if (!empty($row['inst_addr2']))
                $full_address .= $row['inst_addr2'].",\n";

            if (!empty($row['inst_addr3']))
                $full_address .= $row['inst_addr3'].",\n";

            if (!empty($row['inst_city']))
                $full_address .= $row['inst_city'].", ";

            if (!empty($row['inst_postcode']))
                $full_address .= $row['inst_postcode'].",\n";

            if (!empty($row['state_name']))
                $full_address .= $row['state_name'];
            ?>

            <tr>
                <td>
                    <?= htmlspecialchars($row['package_name']) ?>

                    <?php if($row['contract_start_date'] && $row['contract_end_date']): ?>
                        <br>
                        <small class="text-muted">
                            Contract:
                            <br>
                            <?= htmlspecialchars($row['contract_start_date']) ?>
                            -
                            <?= htmlspecialchars($row['contract_end_date']) ?>
                        </small>
                    <?php endif; ?>
                </td>
                <td>RM <?= htmlspecialchars($row['monthly_charge']) ?></td>
                <td><?= htmlspecialchars($row['start_date']) ?></td>
                <td><?= htmlspecialchars($row['end_date']) ?></td>
                <td><?= nl2br(htmlspecialchars($full_address)) ?></td>
            </tr>

        <?php endforeach; ?>
        </tbody>

    </table>
</div>

<div class="visible-xs">
    <?php if (!empty($row_data)): ?>

        <?php 
            foreach (array_reverse($row_data) as $row): ?>

            <?php
            $full_address = "";

            if (!empty($row['building_name'])) {
                $full_address .= $row['building_name']." ";

                if (!empty($row['inst_unit_no'])) {
                    $full_address .= $row['inst_unit_no'].",\n";
                }
            }

            if (!empty($row['inst_addr1']))
                $full_address .= $row['inst_addr1'].",\n";

            if (!empty($row['inst_addr2']))
                $full_address .= $row['inst_addr2'].",\n";

            if (!empty($row['inst_addr3']))
                $full_address .= $row['inst_addr3'].",\n";

            if (!empty($row['inst_city']))
                $full_address .= $row['inst_city'].", ";

            if (!empty($row['inst_postcode']))
                $full_address .= $row['inst_postcode'].",\n";

            if (!empty($row['state_name']))
                $full_address .= $row['state_name'];
            ?>

            <div style="margin-bottom:12px;background:#fff;border:1px solid #eee;border-radius:10px;overflow:hidden;font-size:14px;line-height:1.4;">

                <div style="padding:12px 14px;border-bottom:1px solid #f2f2f2;background:#fafafa;">

                    <div style="font-size:15px;font-weight:600;color:#333;">
                        <?= htmlspecialchars($row['package_name']) ?>
                    </div>

                    <?php if($row['contract_start_date'] && $row['contract_end_date']): ?>
                        <div style="margin-top:4px;font-size:12px;color:#888;">
                            Contract:
                            <?= htmlspecialchars($row['contract_start_date']) ?>
                            - <?= htmlspecialchars($row['contract_end_date']) ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div style="padding:12px 14px;">
                    <div style="display:flex; justify-content:space-between;">

                        <div>
                            <div style="font-size:11px;color:#999;">Monthly</div>
                            <div style="font-size:16px;font-weight:700;color:#e74c3c;">
                                RM <?= htmlspecialchars($row['monthly_charge']) ?>
                            </div>
                        </div>

                        <div style="text-align:right;">
                            <div style="font-size:11px;" class="grey">Active From</div>
                            <div style="font-size:13px; font-weight:500;" class="green">
                                <?= htmlspecialchars($row['start_date']) ?>
                            </div>
                        </div>

                    </div>

                    <?php if(!empty($row['end_date'])): ?>
                        <div style="margin-bottom:10px;text-align:right;">
                            <div style="font-size:11px;" class="grey">End</div>
                            <div style="font-size:13px;font-weight:500;" class="red">
                                <?= htmlspecialchars($row['end_date']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:10px;padding-top:10px;border-top:1px dashed #eee;">

                        <div style="font-size:11px;" class="grey">
                            Installation Address
                        </div>

                        <div style="font-size:13px;color:#444;white-space:pre-line;line-height:1.5;">
                            <?= htmlspecialchars($full_address) ?>
                        </div>

                    </div>

                </div>

            </div>
        <?php endforeach; ?>

    <?php else: ?>

        <div style="font-size: 15px; padding:15px; text-align:center;" class="grey">
            No package history found
        </div>

    <?php endif; ?>
</div>