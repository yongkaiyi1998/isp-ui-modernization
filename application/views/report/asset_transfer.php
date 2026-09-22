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
					
                    <span>Search </span>    
                    <input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>

					<span>Service Date: </span>
					<input type='text' id='txt_date_start' name='txt_date_start' value="<?php echo set_value('txt_date_start', $txt_date_start); ?>" placeholder='Start Date' autocomplete='off' />
					<span>Until: </span>
					<input type='text' id='txt_date_end' name='txt_date_end' value="<?php echo set_value('txt_date_end', $txt_date_end); ?>" placeholder='End Date' autocomplete='off' />
						
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
			</form>
	<?php } ?>
			<div>
				<table>
					<tr>
						<th colspan="6">
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
		form.attr('action', base_url + 'report/export_asset_transfer');
		//form.attr('target', '_blank');
		$('#btSubmit').click();
		form.attr('action', base_url + 'report/asset_transfer') ;
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
	form.attr('action', base_url + 'report/asset_transfer/print');
	form.attr('target', '_blank');
	$('#btSubmit').val("print");
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/asset_transfer');
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
		url: base_url + "report/asset_transfer_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			txt_search: $("#txt_search").val(),
			txt_date_start: $("#txt_date_start").val(),
			txt_date_end: $("#txt_date_end").val(),
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
	$("#txt_date_start").val('');
	$("#txt_date_end").val('');
	$("#txt_date_start").datepicker('setDate', '');
	$("#txt_date_end").datepicker('setDate', '');

	let table_html = `
	<div id="table_wrapper" class="table-responsive">
		<table class="table-striped" style="width:100%;">

			<thead>						
				<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
					<th class="col-lg-1" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset #</th>
					<th class="col-lg-1 text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Asset Name</th>
					<th class="col-lg-1 col-p text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>From</th>
					<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>To</th>
					<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Date</th>
					<th class="col-lg-1 col-r text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Description</th>
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
	ajax_clear_session('report', 'report_asset_transfer_filter');
}

</script>

