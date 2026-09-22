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
.table-striped {
	width: 100% !important;
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
					<span>Search </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					<input type='hidden' id='txt_customer_no' name='txt_customer_no' value='<?php echo set_value('txt_customer_no', $txt_customer_no); ?>' />
					<span>Category </span>
					<select id="sel_category" name="sel_category">
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<span>From </span>
					<input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off"
						value="<?php echo set_value('date_from',$date_from); ?>" />
					
					<span>To </span>
					<input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
						value="<?php echo set_value('date_to',$date_to); ?>" />
					
					<br /><br />
					
					<span>Building </span>
					<select id="sel_building" name="sel_building" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_building_list as $val) {
								echo "<option value='" . $val['building_no'] . "' " . set_select('sel_building', $val['building_no'], ($val['building_no']==$sel_building ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>
				
					<span>Package </span>
					<select id="sel_package" name="sel_package" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_package_list as $val) {
								echo "<option value='" . $val['package_no'] . "' " . set_select('sel_package', $val['package_no'], ($val['package_no']==$sel_package ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>

					<span>Agent </span>
					<select id="sel_dealer" name="sel_dealer" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_dealer_list as $val) {
								echo "<option value='" . $val['dealer_no'] . "' " . set_select('sel_dealer', $val['dealer_no'], ($val['dealer_no']==$sel_dealer ? true : false) ) . ">" . $val['name'] . "</option>";
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
		form.attr('action', base_url + 'report/export_contract_acc');
		//form.attr('target', '_blank');
		$('#btSubmit').click();
		form.attr('action', base_url + 'report/contract_acc') ;
		//form.removeAttr('target');
	}

$(document).on("click", ".fa-sort", function(e){
	$(this).removeClass('fa-sort').addClass('fa-sort-desc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	
	
	ajax_filter($(this).attr('id'), 'desc');
});

$(document).on("click", ".fa-sort-asc", function(e){
	$(this).removeClass('fa-sort-asc').addClass('fa-sort');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	
	
	ajax_filter($(this).attr('id'), 'desc');
});
	
$(document).on("click", ".fa-sort-desc", function(e){
	$(this).removeClass('fa-sort-desc').addClass('fa-sort-asc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'asc' );
	
	ajax_filter($(this).attr('id'), 'asc');
});

$( document ).on("click", "#btPrint", function(e){
	var form = $('form');
	form.attr('action', base_url + 'report/contract_acc/print');
	form.attr('target', '_blank');
	$('#btSubmit').val("print");
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/contract_acc');
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

function ajax_filter(order_by='', order_type='') {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/contract_acc_row/0",
		data: { 
			is_postback: $("#is_postback").val(),
			sel_category: $("#sel_category").val(),
			sel_building: $("#sel_building").val(),
			sel_package: $("#sel_package").val(),
			sel_dealer: $("#sel_dealer").val(),
			txt_search: $("#txt_search").val(),
			txt_customer_no: $("#txt_customer_no").val(),
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
	$("#sel_category").val('r');
	$("#sel_building").val('all');
	$("#sel_package").val('all');
	$("#sel_dealer").val('all');
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
					<th colspan="10">
						<h3 class="text-center" style="color: black !important;">
							<?php echo $page_title; ?><br>
							<span class="small"><?php echo $filter_text; ?></span>
						</h3>
					</th>
				</tr>
				<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
					<th class="text-left" style="width:80px;">Acc.No</th>
					
					<th class="text-left" style="width:80px;">Name 
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="customer_name"></i><?php } ?>
					</th>
					
					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Login
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="login_username"></i><?php } ?>
					</th>
					
					<th class="text-left" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Gender
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="gender"></i><?php } ?>
					</th>
					
					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Mobile
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="mobile_num"></i><?php } ?>
					</th>

					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Email
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="email_1"></i><?php } ?>
					</th>

					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Contract Start
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="contract_date"></i><?php } ?>
					</th>

					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Contract End
					</th>
					
					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Agent
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="dealer"></i><?php } ?>
					</th>
					
					<th class="text-center" <?php if ($print == 1) { echo 'style="width:10%;"'; } ?>>Package
					<?php if ($print != 1) { ?><i class="menu-icon fa fa-sort" id="package_name"></i><?php } ?>
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
	ajax_clear_session('report', 'report_contract_acc_filter');
}
</script>