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

<div class="container">
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
					<span>Search: </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					<input type='hidden' id='txt_customer_no' name='txt_customer_no' value="<?php echo set_value('txt_customer_no', $txt_customer_no); ?>"/>
				
					<button style="display:none;" id="btSubmit" name="btSubmit" type="submit" value="filter" class="btn btn-success">
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
					<input type="hidden" id="isprint" name="isprint" value="" />

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
	
function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'report/export_customer_transaction_listing');
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/customer_transaction_listing') ;
}

$( document ).on("click", "#btSend", function(e){
	show_popup2('report/send_list/', '');
});

function ajax_filter(order_by='', order_type='') {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/customer_transaction_listing_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			txt_search: $("#txt_search").val(),
			txt_customer_no: $('#txt_customer_no').val(),
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

	let table_html = `
	<div>
	<table class="table-striped" style="width:100%;">
		<thead>
			<tr>
				<th colspan="100%">
					<h3 class="text-center">
						Customer Transaction Listing
					</h3>
				</th>
			</tr>
			<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
				<th class="text-left col-lg-1">Doc/Pay D</th>
				<th class="text-center col-lg-1">Doc No.</th>
				<th class="text-center col-lg-1">Bill Due Date</th>
				<th class="text-center col-lg-1">Charge</th>
				<th class="text-center col-lg-1">Payment</th>
				<th class="text-center col-lg-1">Balance</th>
				<th class="text-left col-lg-5">Remark</th>
			</tr>
		</thead>
		<tbody>
	`;

	//ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_customer_transaction_listing_filter');
}

</script>