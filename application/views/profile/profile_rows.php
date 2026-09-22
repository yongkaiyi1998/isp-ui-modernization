<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="col-lg-4">Name</th>
                <th class="col-lg-2">Type</th>
                <th class="col-lg-2">Phone</th>
                <th class="col-lg-3">Email</th>
                <th class="col-lg-1 text-right">Actions</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($row_data)): ?>
                <?php foreach ($row_data as $row): ?>
                    <tr class="customer_row"
                        data-customer-no="<?= $row['acc_id']; ?>"
                        data-name="<?= $row['acc_name']; ?>">
                        
                        <td><?= $row['acc_type'] === 'r' ? $row['acc_name'] : $row['comp_name']; ?></td>
                        <td><?= $row['acc_type_text']; ?></td>
                        <td><?= $row['acc_mobileno']; ?></td>
                        <td><?= $row['acc_email']; ?></td>
                        <td class="text-right">
                            <a href="<?= base_url('profile/add_profile/'.$row['acc_id']); ?>" title="Edit">
                                <i class="fa <?= $view_only == 1 ? 'fa-search black' : 'fa-pencil'; ?>"></i>
                            </a>
                        </td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No Record Available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="col-md-12 text-center">
    <?= $pagination; ?>
</div>
