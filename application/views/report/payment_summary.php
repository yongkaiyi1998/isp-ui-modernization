<style>
@page {
    size: a4 portrait;
}

#popupDetail {
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -40%);
    width: 60%;
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
			<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
			<?php if( $isprint == 0 ){ ?>
			<div class="bg-success filter-bar hidden-print msg-print">
				<?php echo flash_data_helper($msg); ?>
					<input type="hidden" id="is_postback" name="is_postback" value=true>
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
					</select>
					<span> Date: </span>
					<input type='text' id='txt_date_start' name='txt_date_start' value="{txt_date_start}" placeholder='Start Date' autocomplete="off"/>
					<span> Between </span>
					<input type='text' id='txt_date_end' name='txt_date_end' value="{txt_date_end}" placeholder='End Date' autocomplete="off"/>
					
					<button  id="toggle_display" name="toggle_display" type="button" value="toggle_display" class="btn btn-secondary" onclick="toggle_display_area();">
						<i class="menu-icon fa fa-gear" data-toggle="tooltip" title=""></i>
						Toggle Display
					</button>

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

					<button id="btPrint" name="btPrint" type="button" value="Print" class="btn btn-success">
						<i class="menu-icon fa fa-print" data-toggle="tooltip" title=""></i>
						Print
					</button>

					<input type="hidden" id="contact_list" name="contact_list" value="" />

					<button id="btSend" name="btSend" type="button" value="Send" class="btn btn-success">
						<i class="menu-icon fa fa-send" data-toggle="tooltip" title=""></i>
						Send
					</button>
			</div>

			<div class="bg-success filter-bar hidden-print">
				<div class="row display_area" style="display:none">
					<div class="col-lg-2">
						<span><b>Display:</b></span>
					</div>
					<?php foreach ($bill_type_list as $bill_id => $bill) { ?>
					<div class="col-lg-2">
					<input style="margin:0px;" type="checkbox" name="display_col[]" data-id="<?php echo $bill_id; ?>" value="<?php echo $bill_id; ?>" <?php if ((in_array($bill_id, $display_col)) || (empty($display_col))) { echo 'checked="checked"'; } ?> onclick="toggle_column(this, <?php echo $bill_id; ?>);" 
							 /> <?php echo $bill['name']; ?></div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
			</form>

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
$( document ).ready(function() {
	$( 'input[name="display_col\\[\\]"]' ).each(function() {
  		toggle_column(this, $(this).attr('data-id'));
	});
});

function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'report/export_payment_summary');
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/payment_summary') ;
}

function toggle_display_area() {
    $('.display_area').slideToggle('slow');
}

function toggle_column(chk, bill_id) {
	let chk_prop = $(chk).is(':checked');
	if (chk_prop) {
		$('.bill_list_th[data-id="'+bill_id+'"]').css('display', '');
		$('td[data-id="'+bill_id+'"]').css('display', '');
	} else {
		$('.bill_list_th[data-id="'+bill_id+'"]').css('display', 'none');
		$('td[data-id="'+bill_id+'"]').css('display', 'none');
	}
}

$( document ).on("click", "#btPrint", function(e){
	var form = $('form');
	form.attr('action', base_url + 'report/payment_summary/print');
	form.attr('target', '_blank');
	$('#btSubmit').val("print");
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/payment_summary');
	$('#btSubmit').val("filter");
	form.removeAttr('target');
});

$( document ).on("click", "#btSend", function(e){
	show_popup2('report/send_list/', '');
});

function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/payment_summary_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			sel_category: $("#sel_category").val(),
			sel_status: $("#sel_status").val(),
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
	$("#sel_category").val('all');
	$("#sel_status").val('all');
	$("#txt_date_start").val($.datepicker.formatDate('yy-mm-dd', firstDay));
	$("#txt_date_end").val($.datepicker.formatDate('yy-mm-dd', lastDay));
	$("#txt_date_start").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', firstDay));
	$("#txt_date_end").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', lastDay));

	let table_html = `
	<div class="table-wrapper">
		<table class="table-bordered sticky_table">
			<thead>
				<tr>
					<th colspan="100%">
						<h3 class="text-center">
							<?php echo $page_title; ?><br>
							<?php echo $date_start . ' until ' . $date_end ?>
						</h3>
					</th>
				</tr>
				<tr class="report_th">
					<th class="text-left col-lg-1 sticky">
						Category
					</th>
					<?php foreach ($bill_type_list as $bill_id => $bill) { ?>
					<th class="text-center col-lg-1 bill_list_th" data-id="<?php echo $bill_id; ?>">
						<?php echo $bill['name']; ?>
					</th>
					<?php } ?>
					<th class="text-center col-lg-1">
						<strong>Total</strong>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="sticky">
						<strong>Total</strong>
					</td>
					<?php 
					foreach ($bill_type_list as $bill_id => $row) {
						echo "<td class='text-right' data-id='".$bill_id."'><strong>" . number_format($row['total'], 2, '.', ',') . "</strong></td>";
					}
					echo "<td class='text-right'><strong>" . number_format($total_all, 2, '.', ',') . "</strong></td>";
					?>
				</tr>
			</tbody>
		</table>
	</div>
	`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_payment_summary_filter');
}
</script>