<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="col-lg-2">Asset</th>
                <th class="col-lg-1 text-center">Request Date</th>
                <th class="col-lg-1 text-center">Service Date</th>
                <th class="col-lg-1">Vendor</th>
                <th class="col-lg-1 text-right">Maint.Cost</th>
                <th class="col-lg-1 text-center">Status</th>
                <th class="col-lg-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($row_data)): ?>
                {row_data}
                    <tr>
                        <td>{asset_name}</td>
                        <td class="col-lg-1 text-center">{request_date}</td>
                        <td class="col-lg-1 text-center">{service_date_time}</td>
                        <td>{vendor}</td>
                        <td class="col-lg-1 text-right">{maint_cost}</td>
                        <td class="col-lg-1 text-center">{status_text}</td>
                        <td class="col-lg-1 text-right">
                            <a href="<?php echo base_url('asset/add_service');?>/{asset_id}/{maint_id}" title="Edit">
                                <i class="fa fa-pencil fa-1g"></i>
                            </a>

                        </td>
                    </tr>
                {/row_data}
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No Record Available</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
</div>

<div>
    <div class="col-md-12 text-center">
        <?php echo $pagination; ?>
    </div>
</div>