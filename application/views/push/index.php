<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading noprint">
            <div>
                <h4>
                    <?php echo (!empty($page_title) ? $page_title : 'itelco'); ?>
                </h4>
            </div>
        </div>
        <div class="panel-body">
            <?php echo flash_data_helper($msg); ?>
            <div class="bg-success filter-bar">
                <form id="push_scheduler" name="push_scheduler" method="post" class="filter-form" action="<?php echo $form_action; ?>">
                    <input type="hidden" id="page_item_no" name="page_item_no" value="<?php echo $page_item_no; ?>">
                    <span>Search: </span>
                    <input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search' />
                    <span>Date From: </span>
                    <input type='text' id='date_from' name='date_from' value="<?php echo set_value('date_from', $date_from); ?>" placeholder='Date From' autocomplete="off" />
                    <span>Date To: </span>
                    <input type='text' id='date_to' name='date_to' value="<?php echo set_value('date_to', $date_to); ?>" placeholder='Date To' autocomplete="off" />
                    <span>Type: </span>
                    <select id="push_type" name="push_type">
                        <option value="all" <?php echo set_select('push_type', 'all', ($push_type == 'all' ? true : false)); ?>>All</option>
                        <?php foreach ($type_list as $type_key => $type_def) { ?>
                            <option value="<?php echo $type_key; ?>" <?php echo set_select('push_type', $type_key, ($push_type == $type_key ? true : false)); ?>><?php echo $type_key; ?></option>
                        <?php } ?>
                    </select>
                    <span>Source: </span>
                    <select id="source" name="source">
                        <option value="all" <?php echo set_select('source', 'all', ($source == 'all' ? true : false)); ?>>All</option>
                        <?php foreach (array('cron', 'ticket', 'customer_support', 'push', 'customer_portal') as $source_key) { ?>
                            <option value="<?php echo $source_key; ?>" <?php echo set_select('source', $source_key, ($source == $source_key ? true : false)); ?>><?php echo $source_key; ?></option>
                        <?php } ?>
                    </select>
                    <span>Status: </span>
                    <select id="push_status" name="push_status">
                        <option value="all" <?php echo set_select('push_status', 'all', ($push_status == 'all' ? true : false)); ?>>All</option>
                        <option value="P" <?php echo set_select('push_status', 'P', ($push_status == 'P' ? true : false)); ?>>Pending</option>
                        <option value="R" <?php echo set_select('push_status', 'R', ($push_status == 'R' ? true : false)); ?>>Sending</option>
                        <option value="S" <?php echo set_select('push_status', 'S', ($push_status == 'S' ? true : false)); ?>>Success</option>
                        <option value="F" <?php echo set_select('push_status', 'F', ($push_status == 'F' ? true : false)); ?>>Failed</option>
                    </select>
                    <button id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
                        <i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
                        Filter
                    </button>
                    <button id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
                        <i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
                        Clear
                    </button>
                </form>
            </div>
            <!--table rows html here-->
            <div class="table_rows_area"><?php echo $row_html; ?></div>
        </div>
    </div>
</div>

<div id="clear" style="clear:both;"></div>
<div id="popupDetail">
    <div id="popupDetailStd" onclick="disablePopup();">
        <div id="popupContent">
            &nbsp;
        </div>
    </div>
</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>

<script>
    $("#date_from").datepicker({
        format: 'yyyy-mm-dd'
    });
    $("#date_to").datepicker({
        format: 'yyyy-mm-dd'
    });
</script>
<script src="<?php echo base_url("js/itelco/push.js?") . cssjs_ver(); ?>"></script>