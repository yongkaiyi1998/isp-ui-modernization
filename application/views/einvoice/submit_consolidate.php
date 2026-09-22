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

					<button onclick="consolidate();" id="btConsolidate" name="btConsolidate" type="button" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-copy white" data-toggle="tooltip" title=""></i>
						Consolidate
					</button><img id='loading' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' />
					
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

<div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel">Confirm Submit Consolidated?</h3>
            </div>
      
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-xs-12">
                    	<h4>Are you sure you want to submit the following bill as Consolidated Invoice to LHDN?</h4>

                    	<div class="bill_no_div"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <button id="confirm-close" type="button" class="btn btn-information"
                    role="button" aria-disabled="false" style="margin:0.2em;" data-dismiss="modal">
                    Close
                </button>

                <button onclick="do_consolidate();" id="confirm-submit" type="button" class="btn btn-success"
                    role="button" aria-disabled="false" style="margin:0.2em;">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url("js/itelco/einvoice.js?".cssjs_ver()); ?>" ></script>
<script>
	function ajax_filter(filter_pressed=0) {
		if (filter_pressed == 1) $("#page_item_no").val(0);
		
		$.ajax({
			type: "POST",
			url: base_url + "einvoice/submit_consolidate_rows/0",
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
</script>