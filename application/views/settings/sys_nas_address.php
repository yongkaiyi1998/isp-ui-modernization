<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('bill_type', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('settings/add_nas_address');?>" style="margin-left: 5px; margin-right: 5px;" >
							<i class="ui-menu-icon fa fa-plus green"></i>
						</a>
					</span>
					<?php } ?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="nas_address_listing" name="nas_address_listing" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					
					<span>Search: </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" 
							placeholder='Search'/>
					
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

<script>
	function ajax_filter(filter_pressed=0) {
		if (filter_pressed == 1) $("#page_item_no").val(0);
		
		$.ajax({
			type: "POST",
			url: base_url + "settings/nas_setting_management_rows/0",
			data: { 
				page_item_no: $("#page_item_no").val(),
				txt_search: $("#txt_search").val(),
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
		ajax_filter(1);
	}
</script>