<style>
.menu-icon{
	cursor: pointer;
}

.filter-label{
	padding-right:25px;
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
<?php 
//_debug_array( $_POST );
if( isset( $_POST['btSubmit'] ) == true && $_POST['btSubmit'] == 'print' )
	$print = 1 ;
else
	$print = 0 ;
?>
<div class="container">
	<div class="panel-default">
	<?php if( $print == 0 ){ ?>
		<div class="panel-heading noprint panel_fontsize hidden-print">
			<span class="panel_space float_right">
				<a href="#" onclick="window.history.back()">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<div class="bg-success filter-bar hidden-print msg-print">
				<?php echo flash_data_helper($msg); ?>
				<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="is_postback" name="is_postback" value=true>
					
					<div class="col-lg-12 bg-success">

						<span class='filter-label'>Category </span>
						<select id="sel_category" name="sel_category">
							<option value="all">All</option>
							<?php
								foreach ($opt_category_list as $val) {
									echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
								}
							?>
						</select>
						<span class='filter-label'></span>
						<span class='filter-label'>Product Category </span>
						<select id="sel_product_category" name="sel_product_category">
							<option value="all">All</option>
							<?php
								foreach ($opt_product_category_list as $val) {
									echo "<option value='" . $val['product_code'] . "' " . set_select('sel_product_category', $val['product_code'], ($val['product_code']==$sel_product_category ? true : false) ) . ">" . $val['product_name']."</option> ";
								}
							?>
						</select>
						<span class='filter-label'></span>
						<span class='filter-label'>Status </span>
						<select id="sel_status" name="sel_status">
							<option value="all">All</option>
							<option value="0" <?php echo set_select('sel_status', '0', ($sel_status == '0' ? true : false) ) ?> >Close</option>
							<option value="1" <?php echo set_select('sel_status', '1', ($sel_status == '1' ? true : false) ) ?> >Open</option>
						</select>
						
						<span class='filter-label'></span>

						<span class='filter-label'>Search </span>
						<input id="txt_search_ticket" name="txt_search_ticket" placeholder="PIC Name/Remarks etc" autocomplete="off"
						value="<?php echo set_value('txt_search_ticket',$txt_search_ticket); ?>" />

						<br /><br />
						<span style='padding-right:19px;'>Complaint</span>
						<select id="sel_complaint" name="sel_complaint">
							<option value="all">All</option>
							<?php
								foreach ($opt_complaint as $val) {
									echo "<option value='" . $val['tt_complaint_id'] . "' " . set_select('sel_complaint', $val['tt_complaint_id'], ($val['tt_complaint_id']==$sel_complaint ? true : false) ) . ">" . $val['tt_complaint_name']."</option> ";
								}
							?>
						</select>

						<span class='filter-label'></span>
						<span class='filter-label'>SOF</span>
						<select id="sel_sof" name="sel_sof">
							<option value="all">All</option>
							<?php
								foreach ($opt_sof as $val) {
									echo "<option value='" . $val['tt_sof_id'] . "' " . set_select('sel_sof', $val['tt_sof_id'], ($val['tt_sof_id']==$sel_sof ? true : false) ) . ">" . $val['tt_sof_name']."</option> ";
								}
							?>
						</select>
						
						<span class='filter-label'></span>
						<span class='filter-label'>COF</span>
						<select id="sel_cof" name="sel_cof">
							<option value="all">All</option>
							<?php
								foreach ($opt_cof as $val) {
									echo "<option value='" . $val['tt_cof_id'] . "' " . set_select('sel_cof', $val['tt_cof_id'], ($val['tt_cof_id']==$sel_cof ? true : false) ) . ">" . $val['tt_cof_name']."</option> ";
								}
							?>
						</select>

						<span class='filter-label'></span>
						<span class='filter-label'>Opened By</span>
						<select id="open_by" name="open_by">
							<option value="" <?php if ($open_by=="") { echo 'selected="selected"'; } ?>>All</option>
							<option value="0" <?php if ($open_by=="0") { echo 'selected="selected"'; } ?>>--Customer--</option>
							<?php
								foreach ($opt_user as $val) {
									echo "<option value='" . $val['idx'] . "' " . set_select('open_by', $val['idx'], ($val['idx']==$open_by ? true : false) ) . ">" . $val['display_name']."</option> ";
								}
							?>
						</select>
						
					</div>
					
					<div class="col-lg-12 bg-success" style="padding-top:15px;">
						<span>Opened From</span>
						<input style='width:80px;' id="open_date_from" name="open_date_from" placeholder="Date from" autocomplete="off"
							value="<?php echo set_value('open_date_from',$open_date_from); ?>" />
						
						<span>To </span>
						<input style='width:80px;' id="open_date_to" name="open_date_to" placeholder="Date to" autocomplete="off"
							value="<?php echo set_value('open_date_to',$open_date_to); ?>" />
					
						<span class='filter-label'></span>
						<span class='filter-label'>Closed From</span>
						<input style='width:80px;' id="close_date_from" name="close_date_from" placeholder="Date from" autocomplete="off"
							value="<?php echo set_value('close_date_from',$close_date_from); ?>" />
						
						<span>To </span>
						<input style='width:80px;' id="close_date_to" name="close_date_to" placeholder="Date to" autocomplete="off"
							value="<?php echo set_value('close_date_to',$close_date_to); ?>" />
						
						<span class='filter-label'></span>
						<span class='filter-label'>Assigned to</span>
						<select id="sel_user" name="sel_user">
							<option value="">All</option>
							<?php
								foreach ($opt_user as $val) {
									echo "<option value='" . $val['idx'] . "' " . set_select('sel_user', $val['idx'], ($val['idx']==$sel_user ? true : false) ) . ">" . $val['display_name']."</option> ";
								}
							?>
						</select>
						
						<input type="hidden" id="order_by" 	 name="order_by"   value="<?php echo $order_by; ?>" />
						<input type="hidden" id="order_type" name="order_type" value="<?php echo $order_type; ?>" />
						<span class='filter-label'></span>
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

			</div>
			</form>
	<?php } ?>
			<div>
				<table>
					<tr>
						<th colspan="100%">
							<h3 class="text-center" style="color: black !important;">
								<?php echo $page_title; ?>
							</h3>
						</th>
					</tr>
				</table>
			</div>

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
	var orderBy		= "<?php echo $order_by; ?>";
	var orderType	= "<?php echo $order_type; ?>";
	
	if( orderBy != '' ){
		$("#table_wrapper > table > thead").find("[id*=\'"+orderBy+"\']").removeClass('fa-sort').addClass( 'fa-sort-' + orderType );
	}
	
	$("#open_date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#open_date_to").datepicker({format: 'yyyy-mm-dd'});
	
	
	$("#close_date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#close_date_to").datepicker({format: 'yyyy-mm-dd'});
		
	function generate_xls_filtered()
	{
		var form = $('form');
		form.attr('action', base_url + 'report/export_ticket_listing');
		//form.attr('target', '_blank');
		$('#btSubmit').click();
		form.attr('action', base_url + 'report/ticket_listing') ;
		//form.removeAttr('target');
	}

// $(document).on("click", ".fa-sort", function(e){
// 	$(this).removeClass('fa-sort').addClass('fa-sort-desc');
// 	$('#order_by').val( $(this).attr('id') );
// 	$('#order_type').val( 'desc' );	
// 	$('#report').submit();
// });

// $(document).on("click", ".fa-sort-asc", function(e){
// 	$(this).removeClass('fa-sort-asc').addClass('fa-sort');
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

$( document ).on("click", "#btPrint", function(e){
	var form = $('form');
	form.attr('action', base_url + 'report/ticket_listing/print');
	form.attr('target', '_blank');
	$('#btSubmit').val("print");
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/ticket_listing');
	$('#btSubmit').val("filter");
	form.removeAttr('target');
});

var print = '<?php echo $print == 1 ? 1 : 0 ; ?>';
if( print == 1 ){
	$('div').css('padding','0');
	$('.fa-sort').css('display','none');
}

$( document ).on("click", "#btSend", function(e){
    show_popup2('report/send_list/', '');
});

function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/ticket_listing_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			sel_category: $("#sel_category").val(),
			sel_product_category: $("#sel_product_category").val(),
			sel_status: $("#sel_status").val(),
			sel_complaint: $("#sel_complaint").val(),
			sel_sof: $("#sel_sof").val(),
			sel_cof: $("#sel_cof").val(),
			open_date_from: $("#open_date_from").val(),
			open_date_to: $("#open_date_to").val(),
			close_date_from: $("#close_date_from").val(),
			close_date_to: $("#close_date_to").val(),
			sel_user: $("#sel_user").val(),
			open_by: $("#open_by").val(),
			order_by: $("#order_by").val(),
			order_type: $("#order_type").val(),
			txt_search_ticket: $("#txt_search_ticket").val(),
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
	$("#sel_category").val('all');
	$("#sel_product_category").val('all');
	$("#sel_status").val('all');
	$("#sel_complaint").val('all');
	$("#sel_sof").val('all');
	$("#sel_cof").val('all');
	$("#sel_user").val('');
	$("#open_by").val('');
	$("#open_date_from").datepicker('setDate', '');
	$("#open_date_to").datepicker('setDate', '');
	$("#close_date_from").datepicker('setDate', '');
	$("#close_date_to").datepicker('setDate', '');

	$("#txt_search_ticket").val('');

	let table_html = `
	<div id="table_wrapper" class="table-responsive">
		<table class="table-striped" style="width:100%;">

			<thead>						
				<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
					<th class="col-lg-2" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>ST #</th>
					<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Status</th>
					<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Contact</th>
					<th class="col-lg-2 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Customer</th>
					<th class="col-lg-1 col-c text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Opened On</th>
					<th class="col-lg-1 col-s text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Closed On</th>
					<th class="col-lg-1 col-t text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Duration</th>
					<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Complaint</th>
					<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>SOF</th>
					<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>COF</th>
					<th class="col-lg-1 text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Assigned to</th>
					
				</tr>
			</thead>

			<tbody>
			</tbody>

			<tfoot>
				
			</tfoot>

		</table>
	</div>
	`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_ticket_listing_filter');
}

</script>

