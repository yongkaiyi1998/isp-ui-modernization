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
<?php 
//_debug_array( $_POST );
if( isset( $_POST['btSubmit'] ) == true && $_POST['btSubmit'] == 'print' )
	$print = 1 ;
else
	$print = 0 ;
?>

<?php if ($print == 1) { ?>
<style>
@page {
    size: a4 landscape;
  }
</style>
<?php } ?>
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
					<span>Category </span>
					<select id="sel_category" name="sel_category">
						<option value="all">All</option>
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					<span>Status </span>

					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<option value="P" <?php echo set_select('sel_status', 'P', ($sel_status =='P' ? true : false) ); ?> >Sign Up</option>
						<option value="A" <?php echo set_select('sel_status', 'A', ($sel_status =='A' ? true : false) ); ?> >Activated</option>
						<option value="T" <?php echo set_select('sel_status', 'T', ($sel_status =='T' ? true : false) ); ?> >Terminated</option>
						<option value="S" <?php echo set_select('sel_status', 'S', ($sel_status =='S' ? true : false) ); ?> >Suspended</option>		
						<option value="C" <?php echo set_select('sel_status', 'C', ($sel_status =='C' ? true : false) ); ?> >Cancelled</option>					
					</select>
					
					<span>From </span>
					<input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off"
						value="<?php echo set_value('date_from',$date_from); ?>" />
					
					<span>To </span>
					<input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
						value="<?php echo set_value('date_to',$date_to); ?>" />
					
					<span>Activated </span>
					<select id="sel_activated" name="sel_activated">
						<option value="all">All</option>
						<?php
						echo "<option value='yes' " . set_select('sel_activated', 'yes', ($sel_activated=='yes' ? true : false) ) . ">Yes</option>";
						echo "<option value='no' " . set_select('sel_activated', 'no', ($sel_activated=='no' ? true : false) ) . ">No</option>";
						?>
					</select>
					
					<span>Building </span>
					<select id="sel_building" name="sel_building" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_building_list as $val) {
								echo "<option value='" . $val['building_no'] . "' " . set_select('sel_building', $val['building_no'], ($val['building_no']==$sel_building ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>
					
					<input type="hidden" id="order_by" 	 name="order_by"   value="<?php echo $order_by; ?>" />
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
				<span>Display</span>
				<input type="checkbox" name="display_col[]" value="p" 
						<?php echo in_array( "p", $display_col ) == true ? 'checked' : ''; ?> /> Pending
				<input type="checkbox" name="display_col[]" value="c" 
						<?php echo in_array( "c", $display_col ) == true ? 'checked' : ''; ?> /> Cancelled
				<input type="checkbox" name="display_col[]" value="a" 
						<?php echo in_array( "a", $display_col ) == true ? 'checked' : ''; ?> /> Activated
				<input type="checkbox" name="display_col[]" value="s" 
						<?php echo in_array( "s", $display_col ) == true ? 'checked' : ''; ?> /> Suspended
				<input type="checkbox" name="display_col[]" value="t" 
						<?php echo in_array( "t", $display_col ) == true ? 'checked' : ''; ?> /> Terminated
			</div>
			</form>
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
	var orderBy		= "<?php echo $order_by; ?>";
	var orderType	= "<?php echo $order_type; ?>";
	
	if( orderBy != '' ){
		$("#table_wrapper > table > thead").find("[id*=\'"+orderBy+"\']").removeClass('fa-sort').addClass( 'fa-sort-' + orderType );
	}
	
	
	$("#date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#date_to").datepicker({format: 'yyyy-mm-dd'});
		
	function generate_xls_filtered()
	{
		var form = $('form');
		form.attr('action', base_url + 'report/export_customer_building');
		//form.attr('target', '_blank');
		$('#btSubmit').click();
		form.attr('action', base_url + 'report/building_listing') ;
		//form.removeAttr('target');
	}

$(document).on("click", ".fa-sort", function(e){
	$(this).removeClass('fa-sort').addClass('fa-sort-desc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	
	$('#report').submit();
});

$(document).on("click", ".fa-sort-asc", function(e){
	$(this).removeClass('fa-sort-asc').addClass('fa-sort');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	
	$('#report').submit();
});
	
$(document).on("click", ".fa-sort-desc", function(e){
	$(this).removeClass('fa-sort-desc').addClass('fa-sort-asc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'asc' );
	$('#report').submit();
});

$( document ).on("click", "#btPrint", function(e){
	var form = $('form');
	form.attr('action', base_url + 'report/building_listing/print');
	//form.attr('target', '_blank');
	$('#btSubmit').val("print");
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/building_listing');
	$('#btSubmit').val("filter");
	form.removeAttr('target');
});

$( document ).on("click", "#btSend", function(e){
	show_popup2('report/send_list/', '');
});

var print = '<?php echo $print == 1 ? 1 : 0 ; ?>';
if( print == 1 ){
	$('div').css('padding','0');
	$('.fa-sort').css('display','none');
}

//~ var col_r = '<?php echo in_array( "r", $display_col ) == true ? 1 : 0 ; ?>';
//~ if( col_r == '0' )	$('.col-r').css('display','none');

//~ var col_p = '<?php echo in_array( "p", $display_col ) == true ? 1 : 0 ; ?>';
//~ if( col_p == '0' )	$('.col-p').css('display','none');

//~ var col_c = '<?php echo in_array( "c", $display_col ) == true ? 1 : 0 ; ?>';
//~ if( col_c == '0' )	$('.col-c').css('display','none');

//~ var col_s = '<?php echo in_array( "s", $display_col ) == true ? 1 : 0 ; ?>';
//~ if( col_s == '0' )	$('.col-s').css('display','none');

//~ var col_t = '<?php echo in_array( "t", $display_col ) == true ? 1 : 0 ; ?>';
//~ if( col_t == '0' )	$('.col-t').css('display','none');

function ajax_filter(order_by='', order_type='') {
	//post values

	var display_col = new Array();
	$("input[name*='display_col']:checked").each(function(i) {
	    display_col.push($(this).val());
	});

	$.ajax({
		type: "POST",
		url: base_url + "report/building_listing_rows/0",
		data: { 
			is_postback: $("#is_postback").val(),
			sel_category: $("#sel_category").val(),
			sel_status: $("#sel_status").val(),
			sel_activated: $("#sel_activated").val(),
			sel_building: $("#sel_building").val(),
			display_col: display_col,
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
	$("#sel_category").val('all');
	$("#sel_status").val('all');
	$("#sel_activated").val('all');
	$("#sel_building").val('all');
	$('input[name*="display_col"]').prop('checked',true);
	$("#date_from").val('');
	$("#date_to").val('');
	$("#date_from").datepicker('setDate', '');
	$("#date_to").datepicker('setDate', '');

	let table_html = `
	<div>
		<table>
			<tr>
				<th colspan="100%">
					<h3 class="text-center">
						Customer Report By Building
					</h3>
				</th>
			</tr>
		</table>
	</div>

	<div id="table_wrapper">
		<table class="table-striped" style="width:100%;">

			<thead>						
				<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
					<th class="col-lg-4">Name</th>
					<th class="col-lg-1 text-center">Total Units</th>
					
					<th class="col-lg-1 col-p text-center">Pending</th>
					
					<th class="col-lg-1 col-c text-center">Cancelled</th>

					<th class="col-lg-1 col-r text-center">Activated</th>
					
					<th class="col-lg-1 col-s text-center">Suspended</th>
					
					<th class="col-lg-1 col-t text-center">Terminated</th>
					
					<th class="col-lg-1 text-center">Total Customers</th>
				</tr>
			</thead>

			<tbody>
			</tbody>

			<tfoot>
			</tfoot>

		</table>
	</div>
	`;

	//ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_building_listing_filter');
}

</script>