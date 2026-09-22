<style>
.menu-icon{
	cursor: pointer;
}

.filter-label{
	padding-right:25px;
}

@page {
    size: a4 landscape;
}

#popupDetail {
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -40%);
    width: 60%;
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
		<?php } ?>
		<div class="panel-body">
			<?php if( $print == 0 ){ ?>
			<div class="bg-success filter-bar hidden-print msg-print">
				<?php echo flash_data_helper($msg); ?>
				<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="is_postback" name="is_postback" value=true>
					<div class="col-lg-12 bg-success">
						<span class='filter-label'>UserName </span>
						<input id="username" name="username" value="<?php echo $username; ?>" />
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
			</div>
			<?php } ?>
			
	
			<div>
				<table>
					<tr>
						<th colspan="100%">
							<h3 class="text-center">
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
	
	$("#open_date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#open_date_to").datepicker({format: 'yyyy-mm-dd'});
	
	
	$("#close_date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#close_date_to").datepicker({format: 'yyyy-mm-dd'});
		
	function generate_xls_filtered()
	{
		var form = $('form');
		form.attr('action', base_url + 'report/export_radius_login');
		//form.attr('target', '_blank');
		$('#btSubmit').click();
		form.attr('action', base_url + 'report/radius_login') ;
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
	form.attr('action', base_url + 'report/radius_login/print');
	form.attr('target', '_blank');
	$('#btSubmit').val("print");
	$('#btSubmit').click();
	form.attr('action', base_url + 'report/radius_login');
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
		url: base_url + "report/radius_login_rows/0",
		data: { 
			username: $("#username").val(),
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
	$("#username").val('');

	ajax_filter();
	ajax_clear_session('report', 'report_radius_login_filter');
}

</script>

