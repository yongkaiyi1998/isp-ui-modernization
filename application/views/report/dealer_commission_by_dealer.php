<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .table-responsive {
        overflow: visible !important;
        height: auto !important;
    }

    .table-striped {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table-striped th,
    .table-striped td {
        white-space: nowrap;
        padding: 4px;
    }

    .noprint, .hidden-print {
        display: none !important;
    }
}

#popupDetail {
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -40%);
    width: 60%;
}

@page {
    size: a4 landscape;
}
</style>

<?php if ($isprint == 1) { ?>
<style>
.table-striped {
    width: 100% !important;
}
</style>
<?php } ?>

<div class="container" <?php if ($isprint == 1) { echo 'style="width:95%;"'; } ?>>
    <div class="panel-default">
        <?php if ($isprint != 1) { ?>
        <div class="panel-heading noprint panel_fontsize hidden-print">
            <span class="panel_space float_right">
                <a href="#" onclick="window.history.back()">
                    <i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
                </a>
            </span>
            <?php echo $page_title; ?>
        </div>
        <?php } ?>
        <div class="panel-body">
            <?php if ($isprint != 1) { ?>
            <div class="bg-success filter-bar hidden-print msg-print">
                <?php echo flash_data_helper($msg); ?>
                <form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
                    <input type="hidden" id="is_postback" name="is_postback" value=true>

                    <span>Agent: </span>
                    <input type='text' id='txt_dealer' name='txt_dealer' value="<?php echo set_value('txt_dealer', $txt_dealer); ?>" placeholder='Agent'/>

                    <span>Date: </span>
                    <input type='text' id='txt_date_start' name='txt_date_start' value="{txt_date_start}" placeholder='Start Date' autocomplete="off"/>
                    <span> Until </span>
                    <input type='text' id='txt_date_end' name='txt_date_end' value="{txt_date_end}" placeholder='End Date' autocomplete="off"/>

                    <button id="btSubmit" name="btSubmit" type="submit" value="filter" style="display:none;" class="btn btn-success"></button>

                    <button  id="btn_previous_month" name="btn_previous_month" type="button" value="previous_month" class="btn btn-info">
                        <i class="menu-icon fa fa-calendar" data-toggle="tooltip" title=""></i>
                        Previous Month
                    </button>
                    <button id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter_by_dealer()">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
                        Generate
                    </button>
                    <button id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear_by_dealer()">
                        <i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
                        Clear
                    </button>
                    <button id="btExcel" name="btExcel" type="button" value="Excel" class="btn btn-success" onclick="generate_xls_filtered_by_dealer()">
                        <i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title=""></i>
                        Excel
                    </button>
                    <button id="btPrint" name="btPrint" type="button" value="Print" class="btn btn-success" onclick="window.print()">
                        <i class="menu-icon fa fa-print" data-toggle="tooltip" title=""></i>
						Print
                    </button>
                </form>
            </div>
            <?php } ?>

            <div class="table_rows_area"><?php echo $row_html; ?></div>
        </div>
    </div>
</div>

<div id="popupDetail">
    <div id="popupDetailStd">
        <div id="popupContent">&nbsp;</div>
    </div>
</div>
<div id="backgroundPopup"></div>

<script src="<?php echo base_url("js/itelco/report.js?".cssjs_ver()); ?>" ></script>
<script>

$("#btn_previous_month").off('click').on('click', function() {
    var firstDay = new Date(date.getFullYear(), date.getMonth() - 1, 1);
    var lastDay = new Date(date.getFullYear(), date.getMonth(), 0);
    
    var formattedFirst = $.datepicker.formatDate('yy-mm-dd', firstDay);
    var formattedLast = $.datepicker.formatDate('yy-mm-dd', lastDay);

    $('#txt_date_start').val(formattedFirst).datepicker('setDate', formattedFirst);
    $('#txt_date_end').val(formattedLast).datepicker('setDate', formattedLast);

    ajax_filter_by_dealer();
});

function generate_xls_filtered_by_dealer()
{
    var form = $('form');
    form.attr('action', base_url + 'report/export_dealer_commission_by_dealer');
    $('#btSubmit').click();
    form.attr('action', base_url + 'report/dealer_commission_by_dealer');
}

function ajax_filter_by_dealer() {
    $.ajax({
        type: "POST",
        url: base_url + "report/dealer_commission_by_dealer_rows/0",
        data: { 
            is_postback: $("#is_postback").val(),
            txt_dealer: $("#txt_dealer").val(),
            txt_date_start: $("#txt_date_start").val(),
            txt_date_end: $("#txt_date_end").val()
        },
        success: function (data) {
            $('.table_rows_area').html(data);
        }
    });
}

function ajax_clear_by_dealer() {
    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    $("#is_postback").val('0');
    $("#txt_dealer").val('');
    $("#txt_date_start").val($.datepicker.formatDate('yy-mm-dd', firstDay));
    $("#txt_date_end").val($.datepicker.formatDate('yy-mm-dd', lastDay));
    $("#txt_date_start").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', firstDay));
    $("#txt_date_end").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', lastDay));

    let table_html = `
    <div class="table-responsive">
        <table class="table-striped" width="100%">
            <thead>
                <tr>
                    <th colspan="5">
                        <h3 class="text-center">
                            <?php echo $page_title; ?><br>
                            <?php echo $date_start . ' until ' . $date_end ?>
                        </h3>
                    </th>
                </tr>
                <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                    <th>Dealer</th>
                    <th class="text-right"># Customers</th>
                    <th class="text-right"># Bills</th>
                    <th class="text-right">Total Bill Amount</th>
                    <th class="text-right">Total Commission</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>`;
    $('.table_rows_area').html(table_html);
    ajax_clear_session('report', 'report_dealer_commission_by_dealer_filter');
}
</script>
