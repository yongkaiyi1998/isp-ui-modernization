<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading noprint panel_fontsize">

            <span class="panel_space float_right">
                <a href="<?php echo base_url('user'); ?>">
                    <i class="menu-icon fa fa-times grey" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
                </a>
            </span>
            <?php echo $page_title; ?>
        </div>
        <div class="panel-body">
            <form id="user_detail" name="user_detail" method="post" action="{form_action}">
                <input type="hidden" id="acc_exist" name="acc_exist" value="{acc_exist}">

                <fieldset class="category-border-main">

                    <div class="category-border-main bg-success text-center">
                        User Details
                    </div>

                    <div class="col-lg-12">

                        <div class="row">

                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">User ID</span>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="{username}" {username_readonly}>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">Password</span>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">Name</span>
                                    <input type="text" class="form-control" id="display_name" name="display_name"
                                        value="{display_name}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">Email</span>
                                    <input type="text" class="form-control" id="email" name="email" value="{email}">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">Mobile</span>
                                    <input type="text" class="form-control" id="mobile_no" name="mobile_no"
                                        value="{mobile_no}" placeholder="6013xxxxxxx">
                                </div>
                            </div>

                            <div class="col-lg-6" style="padding-top:7px;">
                                <label style="font-weight: normal;">
                                    <input type="checkbox" id="allow_whatsapp" name="allow_whatsapp" value="1"
                                        {allow_whatsapp} style="vertical-align: middle; margin-top: -2px;">
                                    Notify by WhatsApp
                                </label>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">Telegram ID</span>
                                    <input type="text" class="form-control" id="telegram_id" name="telegram_id"
                                        value="{telegram_id}">
                                </div>
                            </div>

                            <div class="col-lg-6" style="padding-top:7px;">
                                <label style="font-weight:normal;">
                                    <input type="checkbox" id="allow_telegram" name="allow_telegram" value="1"
                                        {allow_telegram} style="vertical-align: middle; margin-top: -2px;">
                                    Notify by Telegram
                                </label>
                                &nbsp;&nbsp;
                                <a href="{telegram_bot_url}" target="_blank">
                                    Get Telegram ID
                                </a>
                            </div>
                        </div>
                    </div>

                </fieldset>


                <fieldset class="category-border-main">

                    <div class="category-border-main bg-success text-center">
                        ACCOUNT
                    </div>

                    <div class="col-lg-12">

                        <div class="row">

                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">Account Type</span>

                                    <select id="acc_type" name="acc_type" class="form-control">

                                        <option value="A" {acc_typeA}>Admin</option>
                                        <option value="U" {acc_typeU}>User</option>

                                    </select>

                                </div>
                            </div>

                            <div class="col-lg-6">

                                <div class="input-group">

                                    <span class="input-group-addon input_group_150">Role</span>

                                    <select id="acl_role" name="acl_role" class="form-control">

                                        <?php foreach ($sel_acl_role_list as $val): ?>

                                            <option value="<?= $val['role_no']; ?>"
                                                <?= ($val['role_no'] == $acl_role) ? 'selected' : ''; ?>>
                                                <?= $val['name']; ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-lg-6">

                                <div class="input-group">
                                    <span class="input-group-addon input_group_150">
                                        Last Login
                                    </span>

                                    <input type="text" class="form-control" readonly value="{last_login}">
                                </div>

                            </div>

                            <div class="col-lg-6">

                                <label style="font-weight:normal;">
                                    <input type="checkbox" id="active" name="active" value="1" {active}
                                        style="vertical-align: middle; margin-top: -2px;">
                                    Active
                                </label>

                            </div>

                        </div>

                    </div>

                </fieldset>

                <fieldset class="category-border-main">
                    <div class="category-border-main bg-success text-center">
                        Notification Setting
                    </div>
                    <div class="row" style="display: flex; flex-wrap: wrap; padding-left: 1rem; padding-right: 1rem;">
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="display: flex;">
                            <div class="panel panel-default"
                                style="flex-grow: 1; display: flex; flex-direction: column;">
                                <div class="panel-heading text-center">
                                    <strong>Notification Setting</strong>
                                </div>
                                <div class="panel-body">
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="allow_admin_notification"
                                                name="allow_admin_notification" value="1" {allow_admin_notification}
                                                style="margin:0;" />
                                            <span>Enable Admin Notification</span>
                                        </label>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="allow_tech_notification"
                                                name="allow_tech_notification" value="1" {allow_tech_notification}
                                                style="margin:0;" />
                                            <span>Enable Tech Notification</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="category-border-main" id="technical_module">
                    <div class="category-border-main bg-success text-center">
                        Technical Config
                    </div>
                    <div class="row" style="display: flex; flex-wrap: wrap; padding-left: 1rem; padding-right: 1rem;">
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="display: flex;">
                            <div class="panel panel-default"
                                style="flex-grow: 1; display: flex; flex-direction: column;">
                                <div class="panel-heading text-center">
                                    <strong>Technical Config</strong>
                                </div>
                                <div class="panel-body">
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="customer_support_assign"
                                                name="customer_support_assign" value="1" {customer_support_assign}
                                                style="margin:0;" />
                                            <span>Service Ticket Assignment</span>
                                        </label>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="trouble_ticket_assign"
                                                name="trouble_ticket_assign" value="1" {trouble_ticket_assign}
                                                style="margin:0;" />
                                            <span>Trouble Ticket Assignment</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="category-border-main">
                    <div class="category-border-main bg-success text-center">
                        Approver Config
                    </div>
                    <div class="row" style="display: flex; flex-wrap: wrap; padding-left: 1rem; padding-right: 1rem;">
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="display: flex;">
                            <div class="panel panel-default"
                                style="flex-grow: 1; display: flex; flex-direction: column;">
                                <div class="panel-heading text-center">
                                    <strong>Manual Billing</strong>
                                </div>
                                <div class="panel-body">
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="mb_lvl1_approver" name="mb_lvl1_approver"
                                                value="1" {mb_lvl1_approver} style="margin:0;" />
                                            <span>Level 1 Approver</span>
                                        </label>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="mb_lvl2_approver" name="mb_lvl2_approver"
                                                value="1" {mb_lvl2_approver} style="margin:0;" />
                                            <span>Level 2 Approver</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="display: flex;">
                            <div class="panel panel-default"
                                style="flex-grow: 1; display: flex; flex-direction: column;">
                                <div class="panel-heading text-center">
                                    <strong>Bill Adjustment</strong>
                                </div>
                                <div class="panel-body">
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="ba_lvl1_approver" name="ba_lvl1_approver"
                                                value="1" {ba_lvl1_approver} style="margin:0;" />
                                            <span>Level 1 Approver</span>
                                        </label>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                            <input type="checkbox" id="ba_lvl2_approver" name="ba_lvl2_approver"
                                                value="1" {ba_lvl2_approver} style="margin:0;" />
                                            <span>Level 2 Approver</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="col-md-12">
                    <div class="button-group">
                        <button id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning"
                            onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
                            <i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
                        </button>

                        <button id="btSave" name="btSave" type="button" value="submit" class="btn btn-success"
                            onclick="save_action('user/save_user','user_detail');" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('user', 'M'); ?>>
                            <i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
                        </button>
                        <button id="btDelete" name="btDelete" type="button" value="submit" class="btn btn-danger"
                            onclick="return (confirm('Confirm delete?')) && delete_action('user/delete_user','username');"
                            <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo check_acl_btn('user', 'D'); ?>>
                            <i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <div id="clear" style="clear:both;"></div>
        <div id="popupDetail">
            <div id="popupDetailStd" onclick="disablePopup();">
                <div id="popupContent">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>
    <div id="backgroundPopup" onclick="hide_popup();"></div>
    <script src="<?php echo base_url("js/itelco/user.js?" . cssjs_ver()); ?>" </script>