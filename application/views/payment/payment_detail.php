<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a href="#" onclick="print_receipt(<?php echo $input['payment_no'];?>)">
					<i class="menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print'); ?>></i>
				</a>
				<a href="<?php echo base_url('payment');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="payment_detail" name="payment_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
				<input type="hidden" id="is_lock" name="is_lock" value="<?php echo $input['is_lock']?>">
				<input id="temp_id" name="temp_id" type="hidden" value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						PAYMENT
					</div>			
					<div class="col-lg-7">
						<div class="row">
							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">Payment No</span>
									<input type="text" class="form-control" id="payment_no" name="payment_no" value="<?php echo set_value('payment_no', $input['payment_no']); ?>" placeholder="Payment No" readonly>
								</div>
							</div>
						</div>
						<div class="col-lg-12">&nbsp;</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">Search</span>
									<input type="text" class="form-control" id="txt_search_autocomplete" name="txt_search_autocomplete" value="<?php echo set_value('txt_search_autocomplete', $input['txt_search_autocomplete']); ?>" placeholder="Search">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Customer No<span class="red">*</span></span>
									<input type="text" class="form-control" id="customer_no" name="customer_no" value="<?php echo set_value('customer_no', $input['customer_no']); ?>" placeholder="Customer No" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Customer Name<span class="red">*</span></span>
									<input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo set_value('customer_name', $input['customer_name']); ?>" placeholder="Customer Name" readonly>
								</div>
								<div class="col-lg-12">&nbsp;</div>
								<div class="input-group" id="invoice_id_field">
									<span class="input-group-addon input_group">Reference Bill</span>
									<select name="ref_no" id="ref_no" class="form-control" disabled>
										<option value="" <?= $input['ref_no'] == '' ? 'selected' : '' ?>>-</option>
										<?php foreach($related_invoices as $related_invoice) { ?>
											<option value="<?= $related_invoice['bill_no'] ?>" <?= $input['ref_no'] == $related_invoice['bill_no'] ? 'selected' : '' ?>>
												<?= $related_invoice['bill_no'] . '   [' . $related_invoice['bill_date'] . ']' ?>
											</option>
										<?php } ?>
									</select>
								</div>
								<div class="col-lg-12">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Post Date</span>
									<input type="text" class="form-control" id="tranx_date" name="tranx_date" value="<?php echo set_value('tranx_date', $input['tranx_date']); ?>" placeholder="Post Date" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Pay Date</span>
									<input type="text" class="form-control" id="pay_date" name="pay_date" value="<?php echo set_value('pay_date', $input['pay_date']); ?>" placeholder="Pay Date" autocomplete="off" >
								</div>
								<div class="col-lg-12">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Payment Source</span>
									<select id="payment_source" name="payment_source" style="width: 100%;">
										<option value="<?php echo $input['payment_source']; ?>"><?php echo $input['payment_source_name']; ?></option>
										
										<?php
											foreach ($sel_payment_source_list as $val) {
												echo "<option value='" . $val['payment_source_id'] . "'>" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Bill Type</span>
									<select id="bill_type" name="bill_type" style="width: 100%;">
										<?php
											foreach ($sel_bill_type_list as $val) {
												echo "<option value='" . $val['bill_type_id'] . "' " . set_select('bill_type', $val['bill_type_id'], ( $val['bill_type_id']==$input['bill_type'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
								
								<div class="input-group">
									<span class="input-group-addon input_group">Payment Ref. No.</span>
									<input type="text" class="form-control" id="cheque_no" name="cheque_no" value="<?php echo set_value('cheque_no', $input['cheque_no']); ?>" placeholder="Payment Reference No.">
								</div>
								<div class="col-lg-12">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Amount<span class="red">*</span></span>
									<input type="text" class="form-control" id="amount" name="amount" value="<?php echo set_value('amount', $input['amount']); ?>" placeholder="Amount">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Remark</span>
									<input type="text" class="form-control" id="remark" name="remark" value="<?php echo set_value('remark', $input['remark']); ?>" placeholder="Remark">
								</div>
								
								<div class="input-group" style="padding-top:5px;">
									<span class="">Email PDF to customer</span>
									<input type="checkbox" style="margin:0;" name="email_payment" id="email_payment" value='1' /> 
								</div>
								
							</div>
						</div>
					</div>
					<div class="col-lg-5">
						<fieldset class='category-border'>
							<legend class="category-border">Package Summary</legend>
							<div class="col-lg-12">		
								<ul class="list-group label-custom">
									<li class="list-group-item">
										<span class="label label-info">Status</span>
										<span class="pull-right" id="account_status" name="account_status"><?php echo $input['account_status']; ?></span>
									</li>
									<li class="list-group-item">
										<span class="label label-info">Package</span>
									</li>
									<li class="list-group-item">
										<div style="text-align:right;" id="package_name" name="package_name"><?php echo $input['package_name']?></div>
									</li>
									<li class="list-group-item">
										<span class="label label-info">Monthly Charge</span>							 
										<span class="pull-right" id="monthly_charge" name="monthly_charge"><?php echo $input['monthly_charge']?></span>
									</li>
								</ul>
							</div>
						</fieldset>	
					</div>

					<?php //var_dump($input['customer_no']); die();?>
					<div class="col-lg-5">
						<fieldset class='category-border'>
							<legend class="category-border">Last payment Details</legend>
							<div class="col-lg-12">	

							<div id="err_msg"></div>
								<ul class="list-group label-custom" id="payment_field">
									<li class="list-group-item">
										<span class="label label-info">Payment No </span>
										<span class="pull-right" id="last_payment_no" name="last_payment_no" ></span>
									</li>
									<li class="list-group-item">
										<span class="label label-info">Payment Date </span>
										<span class="pull-right" id="last_payment_date" name="last_payment_date" ></span>
									</li>
									<li class="list-group-item">
										<span class="label label-info">Amount </span>
										<span class="pull-right" id="last_payment_amount" name="last_payment_amount"></span>
									</li>
									<li class="list-group-item">
										<span class="label label-info">Remark </span>
										<span class="pull-right" id="last_payment_remark" name="last_payment_remark" ></span>
									</li>
								</ul>
							</div>
						</fieldset>
					</div>
					
				</fieldset>

				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						ATTACHMENTS
					</div>	

					<div class="form-group col-lg-12 col-xs-12" style="z-index:100;">

						<div class="col-xs-12 col-xs-12">Attach Supporting Documents</div>
						
						<div class="col-lg-3 col-xs-12">
							<input multiple="multiple" type="file" id="attach_file_input" data-preview-file-type="text" />
						</div>
				
						<div class="col-lg-9 col-xs-12" id="file-container">
						<?php 
						if( !empty( $attachment ) ){
							foreach( $attachment AS $row => $res ){
						?>
							<div class="col-lg-3 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" onclick="view_attach_doc(<?php echo $row; ?>);">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>"><a style="cursor:pointer;" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"  onclick="change_remark(this);"><?php echo $attachment_text_title; ?></a></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" id="existing_attach" name="existing_attach[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>

										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>

							<input type="file" name="attach_attachment[<?php echo $row; ?>]" class="hide" id="attach_attachment_<?php echo $row; ?>" value="<?php echo $res['local_path']; ?>">

							<input type="hidden" name="attach_attachment_remark[<?php echo $row; ?>]" value="<?php echo $res['remark']; ?>">
						<?php
							}
						}
						?>
						
						</div>

						<?php include_once( APPPATH . 'views/templates/modal_html_attachment_remark.php'); ?>

					</div>
				</fieldset>	

				<div class="col-md-12">

					<div class="button-group">
						<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
							<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
						</button>
						<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('payment','M');?>>
							<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
						</button>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('payment','D');?> >
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
						</button>				
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
<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>

<script src="<?php echo base_url("js/itelco/payment.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/itelco/payment_detail.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>

<script>
// $('body').on('keydown', 'input, select', function(e) {
//     var self = $(this)
//       , form = self.parents('form:eq(0)')
//       , focusable
//       , next
//       ;
//     if (e.keyCode == 13) {
//         focusable = form.find('input,a,select,textarea').filter(':visible:not([readonly])');
//         next = focusable.eq(focusable.index(this)+1);
        
//         if (next.length) {
//             next.focus();
            
//         } else {
			
//             form.submit();
//         }
//         return false;
//     }
// });

</script>
