<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="col-lg-1">No</th>
                <th class="col-lg-3">Name</th>
                <th class="col-lg-7">Building</th>
                <th class="col-lg-1 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($row_data)): ?>
                <?php foreach ($row_data as $val): ?>
                    <tr class="<?= ($val['upline'] == 0) ? 'highlight-top' : '' ?>">
                        <td><?= $val['dealer_no'] ?></td>
                        <td><?= $val['display_name'] ?></td>
                        <td>
                            <?php foreach ($val['building_names'] as $name): ?>
                                <span class="badge badge-info"><?= $name ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="text-right">
                            <a href="<?= base_url('dealer/edit_dealer/' . $val['dealer_no']) ?>" title="Edit">
                                <i class="fa fa-pencil fa-1g"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">No Record Available</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
</div>

<div>
    <div class="col-md-12 text-center">
        <?= $pagination ?>
    </div>
</div>
