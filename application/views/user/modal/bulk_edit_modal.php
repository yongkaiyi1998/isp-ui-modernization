<div class="modal fade" id="bulkEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="display:flex; flex-direction:column;">

            <div class="modal-header" style="flex-shrink: 0;">
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
                <h4 class="modal-title">Bulk Assign User Setting</h4>
            </div>

            <div class="modal-body">

                <fieldset class="category-border-main">
                    <!-- <div class="category-border-main bg-success text-center">
                        Notification Setting
                    </div> -->

                    <!-- <div class="row">
                        <div class="col-md-6">
                            <label>Enable Admin Notification</label>
                            <select class="form-control" id="bulk_allow_admin_notification">
                                <option value="">-- No Change --</option>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Enable Tech Notification</label>
                            <select class="form-control" id="bulk_allow_tech_notification">
                                <option value="">-- No Change --</option>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div> -->

                    <div class="row" style="display: flex; flex-wrap: wrap; margin-left: 1rem; margin-right: 1rem;">
                        <div class="panel panel-default" style="flex-grow: 1; display: flex; flex-direction:column;">
                            <div class="panel-heading text-center">
                                <strong>Notification Setting</strong>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Enable Admin Notification</label>
                                        <select class="form-control" id="bulk_allow_admin_notification">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="clearfix visible-sm-block visible-xs-block"></div>
                                    <div class="visible-sm-block visible-xs-block" style="margin-bottom:15px;"></div>
                                    <div class="col-md-6">
                                        <label>Enable Tech Notification</label>
                                        <select class="form-control" id="bulk_allow_tech_notification">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <table class="table table-bordered bulk-table">
                        <tbody>

                            <tr>
                                <th>Enable Admin Notification</th>
                                <td>
                                    <select id="bulk_allow_admin_notification" class="form-control input-sm">
                                        <option value="">Remain Unchanged</option>
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th>Enable Tech Notification</th>
                                <td>
                                    <select id="bulk_allow_tech_notification" class="form-control input-sm">
                                        <option value="">Remain Unchanged</option>
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </td>
                            </tr>

                        </tbody>
                    </table> -->
                </fieldset>

                <fieldset class="category-border-main">
                    <!-- <div class="category-border-main bg-success text-center">
                        Technical Config
                    </div> -->

                    <div class="row" style="display: flex; flex-wrap: wrap; margin-left: 1rem; margin-right: 1rem;">
                        <div class="panel panel-default" style="flex-grow: 1; display: flex; flex-direction:column;">
                            <div class="panel-heading text-center">
                                <strong>Technical Config</strong>
                            </div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Service Ticket Assignment</label>
                                        <select class="form-control" id="bulk_customer_support_assign">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="clearfix visible-sm-block visible-xs-block"></div>
                                    <div class="visible-sm-block visible-xs-block" style="margin-bottom:15px;"></div>
                                    <div class="col-md-6">
                                        <label>Trouble Ticket Assignment</label>
                                        <select class="form-control" id="bulk_trouble_ticket_assign">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="category-border-main">
                    <!-- <div class="category-border-main bg-success text-center">
                        Approver Config
                    </div> -->

                    <div class="row" style="display: flex; flex-wrap: wrap; margin-left: 1rem; margin-right: 1rem;">
                        <div class="panel panel-default" style="flex-grow: 1; display: flex; flex-direction:column;">
                            <div class="panel-heading text-center">
                                <strong>Manual Billing</strong>
                            </div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Level 1 Approver</label>
                                        <select class="form-control" id="bulk_mb_lvl1_approver">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="clearfix visible-sm-block visible-xs-block"></div>
                                    <div class="visible-sm-block visible-xs-block" style="margin-bottom:15px;"></div>
                                    <div class="col-md-6 ">
                                        <label>Level 2 Approver</label>
                                        <select class="form-control" id="bulk_mb_lvl2_approver">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="display: flex; flex-wrap: wrap; margin-left: 1rem; margin-right: 1rem;">
                        <div class="panel panel-default" style="flex-grow: 1; display: flex; flex-direction:column;">
                            <div class="panel-heading text-center">
                                <strong>Bill Adjustment</strong>
                            </div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Level 1 Approver</label>
                                        <select class="form-control" id="bulk_ba_lvl1_approver">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="clearfix visible-sm-block visible-xs-block"></div>
                                    <div class="visible-sm-block visible-xs-block" style="margin-bottom:15px;"></div>
                                    <div class="col-md-6 ">
                                        <label>Level 2 Approver</label>
                                        <select class="form-control" id="bulk_ba_lvl2_approver">
                                            <option value="">-- No Change --</option>
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="saveBulkEdit">
                    Save
                </button>
            </div>

        </div>
    </div>
</div>