<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('config', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('advertisement/add_advertisement');?>" style="margin-left: 5px; margin-right: 5px;" >
							<i class="ui-menu-icon fa fa-plus green"></i>
						</a>
					</span>
					<?php }?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="advertisement" name="advertisement" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>

					<span>Date Display From: </span>
					<input type="text" style='width:110px;' id="txt_date_start" name="txt_date_start" placeholder="Date display from" autocomplete="off"
						value="<?php echo set_value('txt_date_start',$txt_date_start); ?>" />
					
					<span>Date Display To: </span>
					<input type="text" style='width:110px;' id="txt_date_end" name="txt_date_end" placeholder="Date display to" autocomplete="off"
						value="<?php echo set_value('txt_date_end',$txt_date_end); ?>" />

					<button style="display: none;" id="btSubmit" name="btSubmit" type="submit" value="submit" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Submit
					</button>
					
					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
					</button>
					
				</form>
			</div>
			<div class="table_rows_area"><?php echo $row_html; ?></div>
		</div>
	</div>
</div>

<div id="clear" style="clear:both;"></div>
<div id="popupDetail">
	<div id="popupDetailStd" onclick="disablePopup();">
		<div id="popupContent">
			&nbsp;
		</div>
	</div>
</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>

<script>
/*
function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'package/export_package_listing');
	//form.attr('target', '_blank');
	$('#btSubmit').click();
	form.attr('action', base_url + 'package/index') ;
	//form.removeAttr('target');
}
*/

var this_month_start = '<?php echo $this_month_start; ?>';
var this_month_end = '<?php echo $this_month_end; ?>';

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "advertisement/advertisement_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			txt_date_start: $("#txt_date_start").val(),
			txt_date_end: $("#txt_date_end").val(),
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

    $("#page_item_no").val(0);
    $("#txt_search").val('');
    $("#txt_date_start").val(this_month_start);
    $("#txt_date_end").val(this_month_end);
    ajax_filter(1);
}

</script>

<script>
	$("#txt_date_start").datepicker({format: 'yyyy-mm-dd'});
	$("#txt_date_end").datepicker({format: 'yyyy-mm-dd'});
</script>