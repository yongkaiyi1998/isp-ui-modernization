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
				<form id="customer" name="customer" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<input type='text' id='from_date' name='from_date' value="<?php echo set_value('from_date', $from_date); ?>" placeholder='From Date' style="width:100px;" autocomplete="off"/>
					<input type='text' id='to_date' name='to_date' value="<?php echo set_value('to_date', $to_date); ?>" placeholder='To Date' style="width:100px;" autocomplete="off"/>
					<input type='text' id='customer_no' name='customer_no' value="<?php echo set_value('customer_no', $customer_no); ?>" placeholder='Customer No '/>
					<input type='text' id='description' name='description' value="<?php echo set_value('description', $description); ?>" placeholder='Description'/>
					<span>Action By: </span>
					<select id="action_by" name="action_by">
						<option value="all">All</option>
						<?php
							foreach ($sel_user_list as $val) {
								echo "<option value='" . $val['username'] . "' " . set_select('sel_user_list', $val['username'], ($val['username']==$sel_user_list ? true : false) ) . ">" . $val['username']."</option> ";
							}
						?>
					</select>
					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter()">
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
<script src="<?php echo base_url("js/itelco/action_log.js?".cssjs_ver()); ?>" ></script>
<script>
	function ajax_filter() {
		//post values

		$.ajax({
			type: "POST",
			url: base_url + "action_log/action_log_rows/0",
			data: { 
				page_item_no: $("#page_item_no").val(),
				from_date: $("#from_date").val(),
				to_date: $("#to_date").val(),
				customer_no: $("#customer_no").val(),
				description: $("#description").val(),
				action_by: $("#action_by").val()
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
		$("#from_date").val('');
		$("#to_date").val('');
		$("#customer_no").val('');
		$("#description").val('');
		$("#action_by").val('all');
		$("#from_date").datepicker('setDate', '');
		$("#to_date").datepicker('setDate', '');

		ajax_filter();
		ajax_clear_session('action_log', 'action_log_filter');
	}
</script>
