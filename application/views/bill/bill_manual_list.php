<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('bill', 'M', false)) { ?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a 	href="<?php echo base_url('bill/bill_manual_detail');?>" 
							style="margin-left: 5px; margin-right: 5px;" >
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
				<form id="bill" name="bill" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
					<!-- <span>Category: </span>
					<select id="sel_category" name="sel_category">
						<option value="all" SELECTED="SELECTED" >All</option>
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select> -->
					
					<span>Status: </span>
					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<?php
							foreach ($sel_status_list as $val) {
								echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<!-- <span>Bill By: </span>
					<select id="sel_bill_by" name="sel_bill_by">
						<option value="all" <?php echo set_select('sel_bill_by', 'all', ($sel_bill_by == 'all' ? true : false) ) ?> >All</option>
						<option value="p" <?php echo set_select('sel_bill_by', 'p', ($sel_bill_by == 'p' ? true : false) ) ?> >Post</option>
						<option value="e"  <?php echo set_select('sel_bill_by', 'e', ($sel_bill_by == 'e' ? true : false) ) ?> >Email</option>
					</select> -->
					
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

<div class="modal fade" id="void_reason-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel">Void Reason</h3>
            </div>
      
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-xs-12">
                    	<h4>Are you sure to void Bill No <span id="bill_no_span"></span>? This is not reversible process.</h4>
                    	<input type="hidden" id="void_bill_no" name="void_bill_no" value="" />
                    	<input type="text" id="void_reason" name="void_reason" value="" class="form-control" />
                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <button id="void_reason-close" type="button" class="btn btn-information"
                    role="button" aria-disabled="false" style="margin:0.2em;" data-dismiss="modal">
                    Close
                </button>

                <button onclick="void_bill();" id="void_reason-submit" type="button" class="btn btn-success"
                    role="button" aria-disabled="false" style="margin:0.2em;">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url("js/itelco/bill.js?".cssjs_ver()); ?>" ></script>

<script>
	function prep_void_bill( bill_no ) {
		$('#bill_no_span').html( bill_no );
		$('#void_reason').val('');
		$('#void_bill_no').val( bill_no );
		$('#void_reason-modal').modal('show');
	}
	
	function void_bill(){	

	let bill_no = $('#void_bill_no').val();
	let void_reason = $('#void_reason').val();

	if (bill_no == '' || void_reason == '') {
		alert('Void reason need to fill.');
		return false;
	}

		$.ajax({
			type: "POST",
			url: base_url + "bill/ajax_void_bill/" ,
			dataType: "json",
			data: { bill_no: bill_no, void_reason: void_reason },
			success: function( data ) {
				
				if( data['success'] )
					alert(data['success']);
				else
					alert(data['fail']);
				
				window.location.reload();
					
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert('error');
			}
		});
	}

	function ajax_filter(filter_pressed=0) {
		if (filter_pressed == 1) {
			$("#page_item_no").val(0);
		}

		$.ajax({
			type: "POST",
			url: base_url + "bill/bill_manual_rows/0",
			data: { 
				page_item_no: $("#page_item_no").val(),
				txt_search: $("#txt_search").val(),
				sel_status: $("#sel_status").val(),
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
		let lastDayOfMonth = new Date(today.getFullYear(), today.getMonth()+1, 0);

		let offset = lastDayOfMonth.getTimezoneOffset()
		lastDayOfMonth = new Date(lastDayOfMonth.getTime() - (offset*60*1000))

		$("#page_item_no").val(0);
		$("#txt_search").val('');
		$("#sel_status").val('all');
		$("#txt_bill_date").val(lastDayOfMonth.toISOString().split('T')[0]);
		$("#txt_bill_date").datepicker('setDate', lastDayOfMonth.toISOString().split('T')[0]);
		ajax_filter(1);
	}

</script>