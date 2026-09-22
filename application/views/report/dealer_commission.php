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

    .table-striped th {
        color: #fff !important;
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

.bootbox-close-button {
    display:none;
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
                    <select 
						name="txt_dealer[]" 
						id="txt_dealer" 
						class="selectpicker" 
						multiple 
						data-live-search="true" 
						title="Select Agent(s)..."
					>
						<?php foreach ($sel_dealer_list as $dealer): ?>
                            <option
                                value="<?= $dealer['dealer_no'] ?>"
                                data-upline="<?= $dealer['upline'] ?>">
                                <?= $dealer['name'] ?>
                            </option>
                        <?php endforeach; ?>
					</select>
                    
                    <span>Building: </span>
                    <select style="max-width:250px" id="sel_building" name="sel_building">
                        <option value="all">All</option>
                        <?php
                            foreach ($sel_building_list as $val) {
                                echo "<option value='" . $val['name'] . "' " . set_select('sel_building', $val['name'], ($val['name']==$sel_building ? true : false) ) . ">" . $val['name']."</option> ";
                            }
                        ?>
                    </select>
                    <span>Category: </span>
                    <select id="sel_category" name="sel_category">
                        <option value="all">All</option>
                        <?php
                            foreach ($sel_category_list as $val) {
                                echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
                            }
                        ?>
                    </select>
                    <span>Status: </span>
                    <select id="sel_status" name="sel_status">
                        <option value="all">All</option>
                        <?php
                        foreach ($sel_status_list as $val) {
                            echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name'] . "</option>";
                        }
                        ?>
                    </select><br/>
                    <span> Date: </span>
                    <select id="sel_date" name="sel_date">
                        <option value="all">All</option>
                        <?php
                        foreach ($sel_date_list as $key => $val) {
                            echo "<option value='" . $key . "' " . set_select('sel_date', $key, ($key==$sel_date ? true : false) ) . ">" . $val . "</option>";
                        }
                        ?>
                    </select>
                    
                    <button  id="btn_previous_month" name="btn_previous_month" type="button" value="previous_month" class="btn btn-info">
                        <i class="menu-icon fa fa-calendar" data-toggle="tooltip" title=""></i>
                        Previous Month
                    </button>
                    <button style="display: none;" id="btSubmit" name="btSubmit" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter()">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Generate
					</button>
					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
					</button>

                    <button id="btExcel" name="btExcel" type="button" value="Excel" class="btn btn-success" onclick="generate_xls_filtered()">
                        <i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title=""></i>
                        Excel
                    </button>

                    <button id="btPrint" name="btPrint" type="button" value="Print" class="btn btn-success" onclick="window.print()">
						<i class="menu-icon fa fa-print" data-toggle="tooltip" title=""></i>
						Print
					</button>

                    <input type="hidden" id="contact_list" name="contact_list" value="" />

                    <button id="btSend" name="btSend" type="button" value="Send" class="btn btn-success">
                        <i class="menu-icon fa fa-send" data-toggle="tooltip" title=""></i>
                        Send
                    </button>

                    <?php if ($recalc_acl == '1') { ?>
                    <button id="btRecalc" name="btRecalc" type="button" value="Send" class="btn btn-success" onclick="do_recalc();">
                        <i class="menu-icon fa fa-refresh" data-toggle="tooltip" title=""></i>
                        Recalc Commission 
                    </button>
                    <?php } ?>

                    <span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>

                </form>
            </div>
            <?php } ?>

            <div class="table_rows_area"><?php echo $row_html; ?></div>

        </div>
    </div>
</div>

<div id="popupDetail">
    <div id="popupDetailStd">
        <div id="popupContent">
            &nbsp;
        </div>
    </div>
</div>
<div id="backgroundPopup"></div>

<script src="<?php echo base_url("js/itelco/report.js?".cssjs_ver()); ?>" ></script>
<script>

$(document).ready(function() {
	$('#txt_dealer').selectpicker();
	$('.bootstrap-select .btn').css({
        'border-radius': '4px',
        'box-shadow': 'none',
        'color': '#444242',
        'background-color': '#ffffff',
        'border': '1px solid #d5d5d5',
        'font-size': '12px'
    });

    $('#txt_dealer').on('changed.bs.select', function(e, clickedIndex, isSelected) {
        if (!isSelected) return;

        let selected = $(this).find('option').eq(clickedIndex).val();
        let values = $(this).val() || [];

        $(this).find('option').each(function () {
            if ($(this).data('upline') == selected) {
                let val = $(this).val();
                if (!values.includes(val)) {
                    values.push(val);
                }
            }
        });

        $(this).selectpicker('val', values);
    });
});

function generate_xls_filtered()
{
    var form = $('form');
    form.attr('action', base_url + 'report/export_dealer_commission');
    $('#btSubmit').click();
    form.attr('action', base_url + 'report/dealer_commission') ;
}

$( document ).on("click", "#btSend", function(e){
    show_popup2('report/send_list/', '');
});

function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/dealer_commission_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			txt_dealer: $("#txt_dealer").val(),
			sel_building: $("#sel_building").val(),
			sel_category: $("#sel_category").val(),
			sel_status: $("#sel_status").val(),
			sel_date: $("#sel_date").val(),
		},
		success: function (data) {
			$('.table_rows_area').html(data);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		}
	});
}

function ajax_clear() {
	var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
	var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
	var selDate = $.datepicker.formatDate('yymm', firstDay);
    
	$("#is_postback").val('0');
	$("#txt_dealer").val([]);
    $("#txt_dealer").selectpicker('refresh');
	$("#sel_building").val('all');
	$("#sel_category").val('all');
	$("#sel_status").val('all');
    $("#sel_date").val( $("#sel_date option[value='" + selDate + "']").length ? selDate : 'all' );
    
	let table_html = `
	<div class="table-responsive">
        <table class="table-striped">
            <thead>
                <tr>
                    <th colspan="9">
                        <h3 class="text-center">
                            <?php echo $page_title; ?><br>
                            <?php echo $sel_date_text ?>
                        </h3>
                    </th>
                </tr>
                <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Pay Date</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Customer No</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Customer</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Activated</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Agent</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Top Level Agent</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Building</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Package</th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill No.</th>
                    <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill Amount</th>
                    <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Comm. Amount</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
	`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_dealer_commission_filter');
}

showLoadingIcon = function () {
  $('#loading-icon').show();
}

hideLoadingIcon = function () {
  $('#loading-icon').hide();
}

function do_recalc() {

    let month_selected_text = $('#sel_date option:selected').text();
    let month_selected = $('#sel_date option:selected').val();

    if (month_selected.toLowerCase() == 'all') {
        bootbox.alert('Please choose the year and month to recalc commission.');
        return false;
    }

    bootbox.confirm({
        title: 'Recalc Commission for '+month_selected_text,
        message: 'Are you sure you want to recalc the commission report for '+month_selected_text+'?',
        focus: false,
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (result) {
                showLoadingIcon();

                console.log('Recalc Commission for '+month_selected);

                $.ajax({
                    url: base_url + 'report/do_recalc_agent_comm',
                    type: 'POST',
                    data: {
                        month_selected: month_selected,
                    },
                    success: function (response) {
                        var result = JSON.parse(response);
                        
                        if(result.success) {
                            $.gritter.add({
                                title: 'SUCCESS',
                                text: result.message,
                                time: '5000',
                                class_name: 'info-notice'
                            });

                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        } else {
                            $.gritter.add({
                                title: 'ERROR',
                                text: result.message,
                                time: '5000',
                                class_name: 'danger-notice'
                            });
                        }

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(thrownError);
                    },
                    complete: function () {
                        hideLoadingIcon();
                    }
                });

            }
        }
    });
}

</script>