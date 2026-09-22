<style>
.menu-icon{
	cursor: pointer;
}

#popupDetail {
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -40%);
    width: 60%;
}

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
</style>

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

                    <span>Search </span>    
                    <input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>

                    <input type='hidden' id='txt_customer_no' name='txt_customer_no' value='<?php echo set_value('txt_customer_no', $txt_customer_no); ?>' />

                    <span>From </span>
                    <input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off"
                        value="<?php echo set_value('date_from',$date_from); ?>" />
                    
                    <span>To </span>
                    <input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
                        value="<?php echo set_value('date_to',$date_to); ?>" />
            
                    <input type="hidden" id="order_by"   name="order_by"   value="<?php echo $order_by; ?>" />
                    <input type="hidden" id="order_type" name="order_type" value="<?php echo $order_type; ?>" />
                    
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
var orderBy     = "<?php echo $order_by; ?>";
var orderType   = "<?php echo $order_type; ?>";

if( orderBy != '' ){
    $("#table_wrapper > table > thead").find("[id*=\'"+orderBy+"\']").removeClass('fa-sort').addClass( 'fa-sort-' + orderType );
}

    $("#date_from").datepicker({format: 'yyyy-mm-dd'});
    $("#date_to").datepicker({format: 'yyyy-mm-dd'});
    
function generate_xls_filtered()
{
    var form = $('form');
    form.attr('action', base_url + 'report/export_paynet_report');
    $('#btSubmit').click();
    form.attr('action', base_url + 'report/paynet_report') ;
}

// $(document).on("click", ".fa-sort", function(e){
//     $(this).removeClass('fa-sort').addClass('fa-sort-desc');
//     $('#order_by').val( $(this).attr('id') );
//     $('#order_type').val( 'desc' ); 
//     $('#report').submit();
// });

// $(document).on("click", ".fa-sort-asc", function(e){
//     $(this).removeClass('fa-sort-asc').addClass('fa-sort-desc');
//     $('#order_by').val( $(this).attr('id') );
//     $('#order_type').val( 'desc' ); 
//     $('#report').submit();
// });
    
// $(document).on("click", ".fa-sort-desc", function(e){
//     $(this).removeClass('fa-sort-desc').addClass('fa-sort-asc');
//     $('#order_by').val( $(this).attr('id') );
//     $('#order_type').val( 'asc' );
//     $('#report').submit();
// });

$( document ).on("click", "#btSend", function(e){
    show_popup2('report/send_list/', '');
});

function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/paynet_report_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			txt_search: $("#txt_search").val(),
			txt_customer_no: $("#txt_customer_no").val(),
			date_from: $("#date_from").val(),
			date_to: $("#date_to").val(),
			order_by: $("#order_by").val(),
			order_type: $("#order_type").val()
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
	$("#is_postback").val('0');
	$("#txt_search").val('');
	$("#txt_customer_no").val('');
    $("#date_from").val('');
	$("#date_from").datepicker('setDate', '');
	$("#date_to").val('');
	$("#date_to").datepicker('setDate', '');

	let table_html = `
	<div id="table_wrapper">
        <table class="table-striped" style="width:100%;">
            <thead>
                <tr>
                    <th colspan="9">
                        <h3 class="text-center">
                            <?php echo $page_title; ?><br>
                        </h3>
                    </th>
                </tr>
                <tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill No
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Bill Date
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Account No
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Name
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Paynet ID
                    </th>
                    <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Amount
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Bank
                    </th>
                    <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Status
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Date
                    </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
	`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_paynet_report_filter');
}

</script>