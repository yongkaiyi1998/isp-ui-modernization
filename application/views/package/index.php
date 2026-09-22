<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('customer', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('package/add_package');?>" style="margin-left: 5px; margin-right: 5px;" >
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
				<form id="payment" name="payment" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
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
						<option value="all" <?php echo set_select('sel_status', 'all', ($sel_status == 'all' ? true : false) ) ?> >All</option>
						<option value="a" <?php echo set_select('sel_status', 'a', ($sel_status == 'a' ? true : false) ) ?> >Active</option>
						<option value="i"  <?php echo set_select('sel_status', 'i', ($sel_status == 'i' ? true : false) ) ?> >Inactive</option>
					</select>

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
					
					<button id="btExcel" name="btExcel" type="button" value="Excel" class="btn btn-success" onclick="generate_xls_filtered()">
						<i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title=""></i>
						Excel
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

function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'package/export_package_listing');
	//form.attr('target', '_blank');
	$('#btSubmit').click();
	form.attr('action', base_url + 'package/index') ;
	//form.removeAttr('target');
}

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "package/package_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			sel_category: $("#sel_category").val(),
			sel_status: $("#sel_status").val(),
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
    $("#sel_category").val('all');
    $("#sel_status").val('all');
    ajax_filter(1);
}

function package_moveup(package_no) {
	$.ajax({
		type: "POST",
		url: base_url + "package/moveup",
		data: { 
			package_no: package_no,
		},
		success: function (data) {
			ajax_filter(0);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		}
	});
}

function package_movedown(package_no) {
	$.ajax({
		type: "POST",
		url: base_url + "package/movedown",
		data: { 
			package_no: package_no,
		},
		success: function (data) {
			ajax_filter(0);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		}
	});
}

</script>
