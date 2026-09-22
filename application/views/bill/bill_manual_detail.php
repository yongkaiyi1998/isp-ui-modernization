<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right action_btn_td">
				<span style="margin-right: 5px;">
					{print_bill}
				</span>
				<span style="margin-right: 5px;">
					{print_invoice}
				</span>
				<span>
					<a href="<?php echo base_url('bill/bill_manual'); ?>">
						<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
					</a>
				</span>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="bill_manual_detail" name="bill_manual_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<input type="hidden" id="is_lock" name="is_lock" value="">
				<input id="task" name="task" type="hidden" value=""> 
				<input id="current_status" name="current_status" type="hidden" value="<?= $bill_info['bill_status'] ?>"> 
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						MANUAL BILLING
					</div>
					<div class="col-lg-12">
						<div class="col-lg-12">&nbsp;</div>
						<ul class="steps">
							<li class="complete">
								<span class="step"></span>
								<span class="title">Draft</span>
							</li>
							<?php if(count($lvl1_approvers)>0) { ?>
							<li class="<?= $bill_info['bill_status'] != 'D' && $bill_info['bill_status'] != null ? 'complete' : ''; ?>" id="pending_approval_1">
								<span class="step"></span>
								<span class="title">Pending Approval 1</span>
							</li>
							<?php } ?>
							<?php if(count($lvl2_approvers)>0) { ?>
								<li class="<?= $bill_info['bill_status'] != 'D' && $bill_info['bill_status'] != 'A' && $bill_info['bill_status'] != null ? 'complete' : ''; ?>" id="pending_approval_2">
									<span class="step"></span>
									<span class="title">Pending Approval 2</span>
								</li>
							<?php } ?>
							<li class="<?= $bill_info['bill_status'] == 'C' && $bill_info['bill_status'] != null ? 'complete' : ''; ?>">
								<span class="step"></span>
								<span class="title">Completed</span>
							</li>
							<?php if($bill_info['is_void'] == '1') { ?>
								<li class="void-complete">
									<span class="step"></span>
									<span class="title">Voided</span>
								</li>
							<?php }else{ ?>
								<li class="<?= $bill_info['bill_einvoice_status'] == 'S' ? 'complete' : ''; ?>">
									<span class="step"></span>
									<span class="title">E-Invoice</span>
								</li>
							<?php } ?>
						</ul>
						<br><br>
						<div class="row">
							<div class="col-lg-5">
								<div class="input-group">
									<span class="input-group-addon input_group">Doc. type</span>
									<select name="bill_type" id="bill_type">
										<option value='INV' <?php echo $bill_type=='INV'?'SELECTED':''; ?>>Invoice</option>
										<option value='CN' <?php echo $bill_type=='CN'?'SELECTED':''; ?>>Credit Note</option>
										<option value='DN' <?php echo $bill_type=='DN'?'SELECTED':''; ?>>Debit Note</option>
									</select>
								</div>
								
								<div class="input-group">
									<span class="input-group-addon input_group">Bill No.</span>
									<input 	type="text" class="form-control" 
											id="bill_draft_no" name="bill_draft_no" 
											value="<?php echo $bill_info['bill_draft_no']; ?>" placeholder="" READONLY />
									<input type="hidden" name="idx" id="idx" value="<?= $bill_info['idx']  ?>" />
								</div>

								<div class="input-group">
									<span class="input-group-addon input_group">Bill Date<span class="red">*</span></span>
									
									<input 	type="text" class="form-control" 
											id="bill_date" name="bill_date" 
											data-date-format="yyyy-mm-dd"
											value="<?php echo $bill_info['bill_date']!='' ? $bill_info['bill_date'] : date('Y-m-d') ; ?>" placeholder="" />
								</div>
								
								<div class="input-group">
									<span id='error_msg' style='color:red;'></span>
								</div>

								<div class="col-lg-12">&nbsp;</div>
								
								<div class="input-group">
									<span class="input-group-addon input_group">Search<span class="red">*</span></span>
									<input 	type="text" class="form-control" id="txt_search_autocomplete" 
											name="txt_search_autocomplete" value="" placeholder="Search" />
									<span class="orange cust-warning-msg" style="<?= ($bill_info['customer_status'] ?? 'A') == 'A' ? 'display: none;' : '' ?>">*This customer is currently not active.</span>
								</div>

								<div class="input-group">
									<span class="input-group-addon input_group">Customer No<span class="red">*</span></span>
									<input 	type="text" class="form-control" id="customer_no" name="customer_no" 
											value="<?php echo $bill_info['customer_no']; ?>" 
											placeholder="Customer No" READONLY />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Customer Name<span class="red">*</span></span>
									<input 	type="text" class="form-control" id="customer_name" name="customer_name" 
											value="<?php echo $bill_info['customer_name']; ?>" 
											placeholder="Customer Name" READONLY />
								</div>

								<div class="input-group">
									<span class="input-group-addon input_group">Package Name<span class="red">*</span></span>
									<input 	type="text" class="form-control" id="package_name" name="package_name" 
											value="<?php echo $bill_info['package_name']; ?>" 
											placeholder="Package Name" READONLY />
								</div>
								
								<div class="input-group">
									<span class="input-group-addon input_group">Monthly Charge<span class="red">*</span></span>
									<input  type="text" class="form-control" id="monthly_charge" name="monthly_charge"
									 		value="<?php echo $bill_info['monthly_charge']; ?>" 
											placeholder="Monthly Charge" READONLY />
								</div>
														
								<div class="input-group">
									<span class="input-group-addon input_group">Prev Balance<span class="red">*</span></span>
									<input 	type="text" class="form-control" id="prev_balance" name="prev_balance" 
											value="<?php echo $bill_info['previous_balance']; ?>" READONLY />
								</div>
																
								<!--
								<div class="col-lg-12">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Amount</span>
									<input type="text" class="form-control" id="amount" name="amount" value="" placeholder="Amount">
								</div>
								-->

								<div class="col-lg-12">&nbsp;</div>
								<div class="input-group" id="invoice_id_field" style="display: none">
									<span class="input-group-addon input_group">Invoice Bill No<span class="red">*</span></span>
									<select name="related_bill_no" id="related_bill_no" class="form-control" disabled>
										<option value="" <?= $bill_info['related_bill_no'] == '' ? 'selected' : '' ?>>-</option>
										<?php foreach($related_invoices as $related_invoice) { ?>
											<option value="<?= $related_invoice['bill_no'] ?>" <?= $bill_info['related_bill_no'] == $related_invoice['bill_no'] ? 'selected' : '' ?>>
												<?= $related_invoice['bill_no'] . '   [' . $related_invoice['bill_date'] . ']' ?>
											</option>
										<?php } ?>
									</select>
								</div>

								<div class="input-group" id="reason_field" style="display: none">
									<span class="input-group-addon input_group">Reason<span class="red">*</span></span>
									<textarea name="reason" id="reason" class="form-control" rows="4" cols="50" disabled><?= $bill_info['reason'] ?? '' ?></textarea>
								</div>
							</div>
							
							<div class="col-lg-1">&nbsp;</div>
							<div class="col-lg-5">
								
								<!-- <div class="col-lg-12">&nbsp;</div>

								<div class="input-group">
									<span class="input-group-addon input_group">Search</span>
									<input type="text" class="form-control" id="payment_search_autocomplete" name="payment_search_autocomplete" value="" />
								</div>
								
								<div class="input-group">
									<span class="input-group-addon input_group">Payment No</span>
									<input type="text" class="form-control" id="payment_no" name="payment_no" 
										value="" readonly />
									<span class="input-group-btn">
										<button type="button" class="btn btn-danger add-payment">
											<i class="ace-icon fa fa-plus"></i> Add
										</button>
									</span>
								</div>
																
								<div class="input-group">
									<span class="input-group-addon input_group">Post Date</span>
									<input 	type="text" class="form-control" id="tranx_date" name="tranx_date" 
											value="" 
											placeholder="Post Date" READONLY />
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Pay Date</span>
									<input 	type="text" class="form-control" id="pay_date" name="pay_date" 
											value="" 
											placeholder="Pay Date" READONLY />
								</div>
								
								<div class="input-group">
									<span class="input-group-addon input_group">Paid Amount</span>
									<input 	type="text" class="form-control" id="pay_amount" name="pay_amount" 
											value="" 
											placeholder="Pay Amount" READONLY />
								</div>

								<div class="col-lg-12">&nbsp;</div>
								<div class="col-lg-12">&nbsp;</div>

								<div class="input-group">
									<span class="input-group-addon input_group">Payments #</span>
									<span class="input-icon input-icon-right" style="display:inline;">
									<input 	type="text" class="form-control" id="payments_no" name="payments_no" 
											value="<?php echo $bill_info['related_payment_no']; ?>" 
											READONLY />
									<i class="ace-icon fa fa-times reset-payment" 
										style="cursor:pointer;color:red;"></i>
									</span>
								</div>

								<div class="input-group">
									<span class="input-group-addon input_group">Ttl. Paid Amount</span>
									<input 	type="text" class="form-control" id="ttl_pay_amounts" name="ttl_pay_amounts" 
											value="<?php echo $bill_info['payment_received'] == '' ? '0.00' : $bill_info['payment_received'] ; ?>" 
											READONLY />
								</div>

								<div class="col-lg-12">&nbsp;</div>
								<div class="col-lg-12">&nbsp;</div> -->
								<?php if(count($lvl1_approvers)>0) 	{ ?>
									<div class="input-group">
										<span class="input-group-addon input_group">Level 1 Approver<span class="red">*</span></span>
										<select multiple="multiple" id="lvl1_approver" name="lvl1_approver[]" class="select2" style="width:100%;">
											<?php foreach($lvl1_approvers as $approver): ?>
												<option value="<?= $approver['user_id'] ?>" <?= in_array($approver['user_id'], $selected_lvl1_approver) ? 'selected' : '' ?>><?= $approver['display_name'] ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								<?php } ?>
								<?php if(count($lvl2_approvers)>0) 	{ ?>
									<div class="input-group">
										<span class="input-group-addon input_group">Level 2 Approver<span class="red">*</span></span>
										<select multiple="multiple" id="lvl2_approver" name="lvl2_approver[]" class="select2" style="width:100%;">
											<?php foreach($lvl2_approvers as $approver): ?>
												<option value="<?= $approver['user_id'] ?>" <?= in_array($approver['user_id'], $selected_lvl2_approver) ? 'selected' : '' ?>><?= $approver['display_name'] ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								<?php } ?>

							</div>
							
						</div>
					</div>
					

					<!-- APPEND GRID -->
					
					<div class="col-lg-12">
						<div class="col-lg-12">&nbsp;</div>
						<div class="col-lg-12 row">
							<div class="form-group" style="width: 100%; overflow-x: auto;">
								<table id="tblAppendGrid" style="width:100%;"></table>
							</div>
						</div>
						
						<div class="col-sm-12">
							<div class="form-group">
								<label class="col-sm-10 control-label no-padding-right text-right">Total Charge</label>
								<div class="col-sm-2">
									<input class='form-control text-right' type='text' name='total_charge' 
											id='total_charge' READONLY value='0.00' />
								</div>
								<label class="col-sm-10 control-label no-padding-right text-right">Total Tax</label>
								<div class="col-sm-2">
									<input 	class='form-control text-right' type='text' name='total_tax' 
											id='total_tax' READONLY value='0.00' />
								</div>
								<label class="col-sm-10 control-label no-padding-right text-right">Total Amount</label>
								<div class="col-sm-2">
									<input 	class='form-control text-right' type='text' name='total_amount' 
											id='total_amount' READONLY value='0.00' />
								</div>
							</div>
						</div>
					</div>
					<!-- APPEND GRID -->
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<?php if($bill_info['bill_status'] != 'C' && ($bill_info['bill_status'] == 'D' || $is_approver == 1)){ ?>
							<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning"
							onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?> >
								<i class="menu-icon fa fa-times white" data-toggle="tooltip" ></i> Cancel
							</button>
							<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php
							echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('bill_manual', 'M');?>>
								<i class="menu-icon fa fa-save white" data-toggle="tooltip" ></i> Save
							</button>
						<?php } ?>
						<?php if ($bill_info['bill_status'] == 'D') { ?>
							<?php if($bill_info['idx'] != ''){ ?>
								<?php if (count($lvl1_approvers) == 0 && count($lvl2_approvers) == 0) { ?>
									<button id="btComplete" name="btComplete" type="submit" value="complete" class="btn btn-primary" 
										<?= tooltip_helper('Mark as Completed') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
										<i class="fa fa-check-circle white" data-toggle="tooltip"></i> Mark as Completed
									</button>
								<?php } else { ?>
									<button id="btSendForReview" name="btSendForReview" type="submit" value="sendforreview" class="btn btn-primary" 
										<?= tooltip_helper('Send to Review') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
										<i class="fa fa-paper-plane white" data-toggle="tooltip"></i> Send for Review
									</button>
								<?php } ?>
							<?php } ?>
						<?php } ?>

						<?php if (($bill_info['bill_status'] == 'A' || $bill_info['bill_status'] == 'B') && $is_approver == 1) { ?>
							<button id="btApprove" name="btApprove" type="submit" value="approve" class="btn btn-primary" 
								<?= tooltip_helper('Approve Bill') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
								<i class="fa fa-thumbs-up white" data-toggle="tooltip"></i> Approve
							</button>
							<button id="btReject" name="btReject" type="submit" value="reject" class="btn btn-danger" 
								<?= tooltip_helper('Reject Bill') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
								<i class="fa fa-times-circle white" data-toggle="tooltip"></i> Reject
							</button>
						<?php } ?>

						<?php if (($bill_info['is_void'] == '0' && $bill_info['bill_status'] == 'C')) { ?>
							<button id="btVoid" type="button" class="btn btn-danger" onclick="prep_void_bill(<?php echo $bill_info['bill_no'] ?? ''; ?>)"
								<?= tooltip_helper('Void Bill') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
								<i class="fa fa-remove white" data-toggle="tooltip"></i> Void
							</button>
						<?php } ?>

						<span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>
					</div>
				</div>
			</form>
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

<script type="text/JavaScript" src="<?php echo base_url("js/tinymce/js/tinymce/tinymce.min.js?".cssjs_ver()); ?>"></script>
<script type="text/JavaScript" src="<?php echo base_url("js/itelco/bill_manual_detail.js?".cssjs_ver()); ?>" ></script>

<script>

//define bill_type_info in JS

<?php echo $js; ?>
<?php echo $bill_type_js; ?>

$(document).ready(function(){

	tinymce.init({
		//selector: 'textarea',
		menubar: false,
		toolbar: false,
		statusbar: false,
		force_p_newlines : false,
		force_br_newlines : false,
		forced_root_block : '',
		//plugins: "code table paste ",
	});
	
	$(document).keypress(
		function(event){
		 if (event.which == '13') {
			event.preventDefault();
		  }
	});

	$(function () {
		if ($('#tblAppendGrid').appendGrid('isReady')){
			<?php 
				if( !empty( $bill_dtl ) ){
					$x = 1 ;
					foreach( $bill_dtl as $dtl ){
			?>
					append_row(	'<?php echo $dtl['idx']; ?>',
								'<?php echo $dtl['bill_draft_no']; ?>',
								'<?php echo $dtl['tranx_date']; ?>',
								'<?php echo $dtl['bill_type']; ?>',
								'<?php echo $dtl['adj_type']; ?>',
								'<?php echo $dtl['tax_code']; ?>',
								'<?php echo $dtl['tax_percent']; ?>',
								'<?php echo $dtl['amount']; ?>',
								'<?php echo $dtl['tax_amount']; ?>',
								'<?php echo $dtl['plus_minus']; ?>',
								'<?php echo $dtl['remark']; ?>',
								'<?php echo $dtl['total_amount']; ?>', 
								'<?php echo $x; ?>'
							  );
			<?php 
						$x++; 
					}
				}
			?>
		}
	});
	
	$('#tblAppendGrid').appendGrid({
		caption: 'Manual billing' ,
		initRows: <?php echo empty($bill_dtl)?1:0; ?> ,
		columns: [
			{
				name: 'date', display: 'Trans Date', type: 'date', value: '<?php echo date('Y-m-d'); ?>',
				displayCss: { 'width' : '100px' , 'text-align' : 'center' },
				ctrlCss: { 'max-width': '100px' }
			},
			{
				name: 'category', display: 'Bill Type', type: 'select',
				displayCss: { 'width' : '200px' , 'text-align' : 'center' },
				ctrlCss: { 'max-width': '200px' },
				ctrlOptions: <?php echo $bill_type_opt; ?>,
				onChange: function (evt, rowIndex) {
				 	var uniqueIndex = $('#tblAppendGrid').appendGrid('getUniqueIndex', rowIndex);
				 	change_tax_code( uniqueIndex );
				}
			},
			{
				name: 'adjust_type', display: 'Type', type: 'select',
				displayCss: { 'text-align' : 'center' },
				ctrlOptions: { 'dr' : 'DR' , 'cr' : 'CR' },
				onChange: function (evt, rowIndex) {
				 	var uniqueIndex = $('#tblAppendGrid').appendGrid('getUniqueIndex', rowIndex);
				 	//change_tax_code( uniqueIndex );
				 	change_plus_minus( uniqueIndex );
				}
			},
			/*
			{
				name: 'desc', display: "Description", type: 'text', 
				displayCss: { 'text-align' : 'center' },
				ctrlAttr: { readonly: false},
			},
			*/
			{
				name: 'charge', display: 'Charge', type: 'number',  value: '0.00',
				displayCss : { 'width' : '100px' , 'text-align' : 'center' },
				ctrlCss: { 'width': '100%' , 'text-align' : 'right' },
				onChange: function (evt, rowIndex) {
				 	var uniqueIndex = $('#tblAppendGrid').appendGrid('getUniqueIndex', rowIndex);
				 	count_total();
				}
			},
			{
				name: 'tax_code', display: 'Tax Code', type: 'text',  value: '',
				displayCss : { 'width' : '50px' , 'text-align' : 'center' },
				ctrlCss: { 'width': '100%' , 'text-align' : 'center'  },
				ctrlAttr: { 'readonly': true },
			},
			{
				name: 'tax_percent', display: 'Percent', type: 'text',  value: '0.00',
				displayCss : { 'width' : '50px' , 'text-align' : 'center' },
				ctrlCss: { 'width': '100%' , 'text-align' : 'center'  },
				ctrlAttr: { 'readonly': true },
			},
			{ 
				name: 'tax_amount', display: 'Tax Amount', type: 'number',  value: '0.00',
				displayCss : { 'width' : '100px' , 'text-align' : 'center' },
				ctrlCss: { 'width': '100%' , 'text-align' : 'right'},
				ctrlAttr: { 'readonly': true  },				
			},
			{
				name: 'plus_minus', display: "", type: 'text', value: '+',
				displayCss: { 'text-align' : 'center' },
				ctrlCss: { 'width': '25px' , 'text-align' : 'center' },
				ctrlAttr: { readonly: true},
			},
			{
				name: 'amount', display: 'Amount', type: 'text',  value: '0.00',
				displayCss : { 'width' : '100px' , 'text-align' : 'center' },
				ctrlCss: { 'width': '100%' , 'text-align' : 'right' },
				ctrlAttr: { 'readonly': true  },
			},
			{name: 'idx', type: 'hidden', value: ""},
		],
        useSubPanel: true,
        subPanelBuilder: function (cell, uniqueIndex){
            // Create a label
            $('<span></span>').appendTo(cell);
            // Create a text area
            
            $('<textarea style="width:99%"></textarea>').css('vertical-align', 'middle').attr({
                id: 'tblAppendGrid_desc_' + uniqueIndex,
                name: 'tblAppendGrid_desc_' + uniqueIndex,
                rows: 1
            }).appendTo(cell);
            
            tinyMCE.execCommand("mceAddEditor", false, 'tblAppendGrid_desc_' + uniqueIndex );
        },
		afterRowAppended: function (caller, parentRowIndex, addedRowIndex) {
			var uniqueIndex = $('#tblAppendGrid').appendGrid('getUniqueIndex', addedRowIndex[0]);
			var rval = parseInt(uniqueIndex);
		},
		afterRowRemoved: function (caller, rowIndex) {
			count_total();
		},
		hideButtons: {
			append: false,
			moveUp: true,
			moveDown: true,
			insert: true
		},
	});


	$( document ).on("click" , ".add-payment" , function(){
		
		var newPayNum = $('#payment_no').val();
		var newPayAmt = parseFloat( $('#pay_amount').val() ) ;
		var addedPayNum = $('#payments_no').val();
		var ttlPayAmt = parseFloat( $('#ttl_pay_amounts').val() ) ;
		
		if( addedPayNum == '' ){
			$('#payments_no').val( newPayNum );
			$('#ttl_pay_amounts').val( newPayAmt );			
		}else if( addedPayNum != '' && addedPayNum.search( newPayNum ) == -1 ){
			$('#payments_no').val( addedPayNum + "," + newPayNum );
			$('#ttl_pay_amounts').val( ttlPayAmt + newPayAmt );
		}
		
		$('#payment_no').val('');
		$('#pay_amount').val('');
		$('#tranx_date').val('');
		$('#pay_date').val('');
		$('#payment_search_autocomplete').val('');
		$('#payment_search_autocomplete').focus();
		$('#payment_search_autocomplete').select();
	});
	
	$( document ).on("click" , ".reset-payment" , function(){
		$('#payments_no').val('');
		$('#ttl_pay_amounts').val('0.00');
	});
	
	$('.select2').css('width','100%').select2({allowClear:true})
});

function change_tax_code( row_num ){
	var bill_type = $('#tblAppendGrid_category_' + row_num).val() ; 
	if( bill_type != '' ){
		$('#tblAppendGrid_tax_code_' + row_num).val( tax[bill_type]['tax_code'] ) ;
		$('#tblAppendGrid_tax_percent_' + row_num).val( parseFloat( tax[bill_type]['tax_percent'] ).toFixed(2) ) ;
		$('#tblAppendGrid_adjust_type_' + row_num).val( tax[bill_type]['adjust_type'] ) ;
		$('#tblAppendGrid_plus_minus_' + row_num).val( tax[bill_type]['plus_minus'] ) ;
		count_total();
	}
}

function change_plus_minus( row_num ){
	var adjust_type = $('#tblAppendGrid_adjust_type_' + row_num).val() ;
	if( adjust_type == 'cr' ){
		$('#tblAppendGrid_plus_minus_' + row_num).val('-') ;
		count_total();
	}else if( adjust_type == 'dr' ){
		$('#tblAppendGrid_plus_minus_' + row_num).val('+') ;
		count_total();		
	}
}

function count_total() {
    var parseVal = function(val) {
        var parsed = parseFloat(val);
        return isNaN(parsed) ? 0 : parsed;
    };

    var total_row_str = $('#tblAppendGrid_rowOrder').val() || "";
    var total_row_arr = total_row_str ? total_row_str.split(",") : [];
    
    var total_charge = 0;
    var total_tax_amount = 0;
    var total_amount = 0;

    for (var z = 0; z < total_row_arr.length; z++) {
        var rowId = total_row_arr[z];
        
        var charge = parseVal($('#tblAppendGrid_charge_' + rowId).val());
        var tax_percent = parseVal($('#tblAppendGrid_tax_percent_' + rowId).val());
        var plus_minus = $('#tblAppendGrid_plus_minus_' + rowId).val();

        var row_tax_amount = charge * (tax_percent / 100);
        var row_total_amount = charge + row_tax_amount;

        $('#tblAppendGrid_tax_amount_' + rowId).val(row_tax_amount.toFixed(2));
        $('#tblAppendGrid_amount_' + rowId).val(row_total_amount.toFixed(2));

        if (plus_minus === '+') {
            total_charge += charge;
            total_tax_amount += row_tax_amount;
            total_amount += row_total_amount;
        } else {
            total_charge -= charge;
            total_tax_amount -= row_tax_amount;
            total_amount -= row_total_amount;
        }
    }

    $('#total_charge').val(total_charge.toFixed(2));
    $('#total_tax').val(total_tax_amount.toFixed(2));
    $('#total_amount').val(total_amount.toFixed(2));
}

function append_row( idx , bill_draft_no , tranx_date, bill_type, adj_type, tax_code, tax_percent, amount, tax_amount, plus_minus , remark , total_amount , row_num )
{

	$('#tblAppendGrid').appendGrid('insertRow', [
	{
		'idx' : idx , 
		'date' : tranx_date ,
		'category' : bill_type , 
		'adjust_type' : adj_type , 
		'desc' : remark ,
		'charge' : amount ,
		'tax_code' : tax_code ,
		'tax_percent' : tax_percent ,
		'tax_amount' : tax_amount , 
		'plus_minus' : plus_minus ,
		'amount' : total_amount
	}

	]);
	
	$('#tblAppendGrid_desc_' + row_num ).val( remark );
			
	count_total();

}

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
			
			window.location.href = base_url + "bill/bill_manual";
		},
		error: function (xhr, ajaxOptions, thrownError) {
			alert('error');
		}
	});
}

</script>


