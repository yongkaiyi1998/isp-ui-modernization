<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<!--<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0); float:right;">
						<a style="margin-left: 5px; margin-right: 5px;cursor:pointer" onclick="print_bill_filtered('invoice')">
							<i class="menu-icon fa fa-files-o grey" <?php echo tooltip_helper('Print All Invoice'); ?> ></i>
						</a>
					</span>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0); float:right;">
						<a style="margin-left: 5px; margin-right: 5px;cursor:pointer" onclick="print_bill_filtered('bill')">
							<i class="ui-menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print All Bill'); ?> ></i>
						</a>
					</span>-->
				</h4>
			</div>
		</div>
		<div class="panel-body msg-print">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="bill" name="bill" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					
					<span>Last Bill Date: </span>
					<input id="txt_bill_date" name="txt_bill_date" placeholder="Last Billing Date" autocomplete="off"
						value="<?php echo set_value('txt_bill_date',$txt_bill_date); ?>" />

					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
					</button>
					
					<button  id="btPrintFiltered" name="btPrintFiltered" type="submit" value="filter" class="btn btn-success" role="button" style="display:none" >
						btPrintFiltered
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

<script src="<?php echo base_url("js/itelco/einvoice.js?".cssjs_ver()); ?>" ></script>
<script>
	function ajax_filter(filter_pressed=0) {
		if (filter_pressed == 1) $("#page_item_no").val(0);
		
		$.ajax({
			type: "POST",
			url: base_url + "einvoice/consolidate_rows/0",
			data: { 
				page_item_no: $("#page_item_no").val(),
				txt_search: $("#txt_search").val(),
				txt_bill_date: $("#txt_bill_date").val(),
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
		let today = new Date();
		let formatDate = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
		let end = new Date(today.getFullYear(), today.getMonth() + 1, 0);

		$("#page_item_no").val(0);
		$("#txt_search").val('');
		$("#txt_bill_date").val(formatDate(end));
		$("#txt_bill_date").datepicker('setDate', formatDate(end));
		ajax_filter(1);
	}
</script>