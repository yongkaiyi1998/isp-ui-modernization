<style>
@media print{
	.table-striped {
		width: 100%;
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
                    
                    <span>From </span>
                    <input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off"
                        value="<?php echo $date_from; ?>" />
                    
                    <span>To </span>
                    <input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
                        value="<?php echo $date_to; ?>" />
                    

                    <input  type="hidden" name="order_by" id="order_by" value="<?php echo $order_by; ?>" />
                    <input  type="hidden" name="order_type" id="order_type" value="<?php echo $order_type ?>" />
                    
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

            <!--table rows html here-->
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
$("#date_from").datepicker({format: 'yyyy-mm-dd'});
$("#date_to").datepicker({format: 'yyyy-mm-dd'});

var orderBy     = "<?php echo $order_by; ?>";
var orderType   = "<?php echo $order_type; ?>";

if( orderBy != '' ){
    $("#table_wrapper > table > thead").find("[id*=\'"+orderBy+"\']").removeClass('fa-sort').addClass( 'fa-sort-' + orderType );
}
    
function generate_xls_filtered()
{
    var form = $('form');
    form.attr('action', base_url + 'report/export_expiry_contract_report');
    $('#btSubmit').click();
    form.attr('action', base_url + 'report/expiry_contract_report') ;
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

function ajax_filter(order_by='', order_type='') {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/expiry_contract_report_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			date_from: $("#date_from").val(),
			date_to: $("#date_to").val(),
			order_by: order_by,
			order_type: order_type,
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
	$("#date_from").val('');
	$("#date_from").datepicker('setDate', '');
	$("#date_to").val('');
	$("#date_to").datepicker('setDate', '');

	let table_html = `
	<div id="table_wrapper">
        <table class="table-striped">
            <thead>
                <tr>
                    <th colspan="7">
                        <h3 class="text-center">
                            <?php echo $page_title; ?> <br />
                            <?php if (!empty($date_to)) { ?><span style="font-size:14px;">AS of <?php echo $date_to; ?></span><?php } ?>
                        </h3>
                    </th>
                </tr>
                <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>No
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Name
                    </th>
                    <th class="col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Package
                    </th>
                    <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Contract Months 
                    </th>
                    <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Expiry 
                    </th>
                    <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem sent</th>
                    <th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Rem Date</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
	`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_expiry_contract_report_filter');
}

</script>