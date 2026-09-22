<style>

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

    .table-striped th{
		color: #fff !important;
	}

    .noprint, .hidden-print {
        display: none !important;
    }
}

</style>
<div class="container">
	<div class="panel-default">
		<?php if( $isprint == 0 ){ ?>
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
			<?php if( $isprint == 0 ){ ?>
			<div class="bg-success filter-bar hidden-print">
				<?php echo flash_data_helper($msg); ?>
				<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="is_postback" name="is_postback" value=true>
					<span>Bill Type: </span>
					<select id="sel_bill_type" name="sel_bill_type">
						<option value="all">All</option>
						<?php
							foreach ($sel_bill_type_list as $val) {
								echo "<option value='" . $val['bill_type_id'] . "' " . set_select('sel_bill_type', $val['bill_type_id'], ($val['bill_type_id']==$sel_bill_type ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					<span> Date: </span>
					<input type='text' id='txt_date_start' name='txt_date_start' value="{txt_date_start}" placeholder='Start Date' autocomplete="off"/>
					<span> Between </span>
					<input type='text' id='txt_date_end' name='txt_date_end' value="{txt_date_end}" placeholder='End Date' autocomplete="off"/>
					
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

function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'report/export_adjustment_summary');
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/adjustment_summary') ;
}

// $(document).on("click", ".fa-sort", function(e){
// 	$(this).removeClass('fa-sort').addClass('fa-sort-desc');
// 	$('#order_by').val( $(this).attr('id') );
// 	$('#order_type').val( 'desc' );	
// 	$('#report').submit();
// });

// $(document).on("click", ".fa-sort-asc", function(e){
// 	$(this).removeClass('fa-sort-asc').addClass('fa-sort-desc');
// 	$('#order_by').val( $(this).attr('id') );
// 	$('#order_type').val( 'desc' );	
// 	$('#report').submit();
// });
	
// $(document).on("click", ".fa-sort-desc", function(e){
// 	$(this).removeClass('fa-sort-desc').addClass('fa-sort-asc');
// 	$('#order_by').val( $(this).attr('id') );
// 	$('#order_type').val( 'asc' );
// 	$('#report').submit();
// });

$( document ).on("click", "#btSend", function(e){
	show_popup2('report/send_list/', '');
});

function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/adjustment_summary_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			sel_bill_type: $("#sel_bill_type").val(),
			txt_date_start: $("#txt_date_start").val(),
			txt_date_end: $("#txt_date_end").val()
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
	$("#is_postback").val('0');
	$("#sel_bill_type").val('all');
	$("#txt_date_start").val($.datepicker.formatDate('yy-mm-dd', firstDay));
	$("#txt_date_end").val($.datepicker.formatDate('yy-mm-dd', lastDay));
	$("#txt_date_start").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', firstDay));
	$("#txt_date_end").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', lastDay));

	let table_html = `
	<div id="table_wrapper">
		<table class="table-striped" style="width:100%;">
			<thead>
				<tr>
					<th colspan="100%">
						<h3 class="text-center">
							<?php echo $page_title; ?><br>
							<?php echo $date_start . ' until ' . $date_end ?>
						</h3>
					</th>
				</tr>
				<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
					<th class="text-right col-lg-2" <?php if( $isprint == 1 ){ echo 'style="width:30%;"'; } ?>>Bill Type</th>
					<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:15%;"'; } ?>>Business</th>
					<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:15%;"'; } ?>>Residential</th>
					<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:15%;"'; } ?>>Wholesale</th>
					<th class="text-center col-lg-1" <?php if( $isprint == 1 ){ echo 'style="width:15%;"'; } ?>>Total</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				$grand_total = 0;
				$col_total_b = 0;
				$col_total_r = 0;
				$col_total_w = 0;
				echo "<tr>
					<td class='text-right'><strong>Grand Total</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_b, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_r, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($col_total_w, 2, '.', ',') . "</strong></td>
					<td class='text-right top-bottom-bordered'><strong>" . number_format($grand_total, 2, '.', ',') . "</strong></td>
					</tr>";
				?>
			</tbody>
		</table>
	</div>
	`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_adjustment_summary_filter');
}

</script>

<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>