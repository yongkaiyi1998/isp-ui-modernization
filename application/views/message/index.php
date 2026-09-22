<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
				</h4>	
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="message_scheduler" name="message_scheduler" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no" value="<?php echo $page_item_no; ?>">
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					<span>Date From: </span>
					<input type='text' id='date_from' name='date_from' value="<?php echo set_value('date_from', $date_from); ?>" placeholder='Date From' autocomplete="off"/>
					<span>Date To: </span>
					<input type='text' id='date_to' name='date_to' value="<?php echo set_value('date_to', $date_to); ?>" placeholder='Date To' autocomplete="off"/>
					<span>Status: </span>
					<select id="msg_status" name="msg_status">
						<option value="all" <?php echo set_select('msg_status', 'all', ($msg_status == 'all' ? true : false) ); ?>>All</option>
						<option value="P" <?php echo set_select('msg_status', 'P', ($msg_status == 'P' ? true : false) ); ?>>Pending</option>
						<option value="S" <?php echo set_select('msg_status', 'S', ($msg_status == 'S' ? true : false) ); ?>>Success</option>
						<option value="F" <?php echo set_select('msg_status', 'F', ($msg_status == 'F' ? true : false) ); ?>>Failed</option>						
					</select>
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
			<!--table rows html here-->
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
	
	$("#date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#date_to").datepicker({format: 'yyyy-mm-dd'});

	function ajax_filter(filter_pressed=0) {
		if (filter_pressed == 1) {
			$("#page_item_no").val(0);
		}
		
		$.ajax({
			type: "POST",
			url: base_url + "message_scheduler/messaging_rows/0",
			data: { 
				page_item_no: $("#page_item_no").val(),
				txt_search: $("#txt_search").val(),
				date_from: $("#date_from").val(),
				date_to: $("#date_to").val(),
				msg_status: $("#msg_status").val(),
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
		$("#msg_status").val('all');
		$('#date_from').val('');
		$("#date_from").datepicker('setDate', '');
		$('#date_to').val('');
		$("#date_to").datepicker('setDate', '');
		ajax_clear_session('message_scheduler', 'message_scheduler_filter');
		ajax_filter(1);
	}

</script>
