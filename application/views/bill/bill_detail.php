<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('bill');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<input type="hidden" id="customer_category" value="<?php echo $input['category']?>" >
			<fieldset class='category-border-main'>
				<div class="category-border-main bg-success text-center">
					BILLING
				</div>
				<div class="col-lg-7">
					<fieldset class='category-border'>
						<legend class="category-border">Customer Details
						</legend>
						<div class="col-lg-12">		
							<ul class="list-group label-custom">
							  <li class="list-group-item">
									<span class="label label-info ">Customer No</span>
									<span><?php echo $input['customer_no']?></span>
							  </li>
							  <li class="list-group-item">
									<span class="label label-info ">Name</span>				 
									<span><?php echo $input['customer_name']?></span>
							  </li>
							  <li class="list-group-item">
									<span class="label label-info ">Package</span>				 
									<span><?php echo $input['package_name']?></span>
							  </li>
							  <li class="list-group-item">
									<span class="label label-info ">Login ID</span>				 
									<span><?php echo $input['login_username']?></span>
							  </li>
							</ul>
						</div>
						<div class="col-lg-6">		
							<ul class="list-group label-custom">
							 <!--<li class="list-group-item">
									<span class="label label-info ">Login ID</span>				 
									<span><?php echo $input['login_username']?></span>
							  </li>-->
							  <li class="list-group-item text-left">
									<span class="label label-info ">Status</span>				 
									<span><?php echo $input['status_name']?></span>
							  </li>
							  <li class="list-group-item text-left">
									<span class="label label-info ">Last Billed Date</span>				 
									<span><?php echo $input['last_billed_date']?></span>
							  </li>
							</ul>
						</div>					
						<div class="col-lg-6">		
							<ul class="list-group label-custom">
							  <li class="list-group-item text-left">
									<span class="label label-info ">Activated</span>
									<span><?php echo $input['activated_date']?></span>
							  </li>		
							  <li class="list-group-item text-left">
									<span class="label label-info ">Due Date</span>				 
									<span><?php echo $input['bill_due_date']?></span>
							  </li>	
							  <li class="list-group-item text-left">
									<span class="label label-info ">Last paid date</span>
									<span><?php echo !isset($paydate)?'':$paydate; ?></span>
							  </li>						  
							</ul>	
						</div>	
					
						<div class="col-lg-12" style="padding-left:10px;">Unprocess payment detail</div>
						<div class="col-lg-12">	
								<table class="table table-striped table-hover" style="margin-bottom:0px">
									<thead>
										<tr>
											<th class="col-lg-3" style="font-size:12px">Payment No</th>
											<th class="col-lg-3" style="font-size:12px">Amount</th>
											<th class="col-lg-6" style="font-size:12px">Remark</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											if( !empty( $unpayment_data ) ){
												foreach( $unpayment_data AS $row ){
												?>
												<tr>
												<td style="font-size:12px"><?php echo $row['payment_no']?></td>
												<td style="font-size:12px"><?php echo $row['amount']?></td>
												<td style="font-size:12px"><?php echo $row['remark'] ?></td>
												</tr>
												<?php 
												}
											}else{
												?>
												<tr>
												<td style="font-size:12px;text-align:center" colspan="3"><?php echo "No result to show."; ?></td>
												</tr>
												<?php 
											}
										?>
									</tbody>
								</table>	
							
						</div>	
					</fieldset>	
				</div>
				<div class="col-lg-5">
					<fieldset class='category-border'>
						<legend class="category-border">Next Billing <?php echo $input['next_bill_date'];?>
						</legend>
						<div class="col-lg-12">		
							<ul class="list-group label-custom">
								<li class="list-group-item">
									<span class="label label-default">Previous Balance</span>
									<span class="pull-right"><?php echo $input['previous_balance']?></span>
								</li>
								<li class="list-group-item">
									<span class="label label-default">Payment Received</span>							 
									<span class="pull-right"><?php echo $input['payment_received']?></span>
								</li>
								<br/>
								Current Monthly Charged
								<?php 
								//_debug_array( $input['charge_detail'] );
									foreach ($input['charge_detail'] as $row ) {
								?>
								<li class="list-group-item">
									<span class="label label-warning ">
										<?php 
											echo $row['adjust_desc'];
											echo "(".$row['tax_code']." ".number_format($row['tax_percent'],2)."%)";
										
										?>
									</span>
									<span class="pull-right">
										<?php 
											echo number_format($row['amount'],2); 
											echo " + (".$row['tax_amount'].")";
										?>
									</span>
								</li>
								<?php
									}
								?>
								<li class="list-group-item">
									<span class="label label-warning ">Tax</span>
									<span class="pull-right"><?php echo number_format($input['tax_charges'],2)?></span>
								</li>
								<br/>
								<li class="list-group-item">
									<span class="label label-success ">Balance</span>
									<span class="pull-right"><?php echo number_format($input['balance'],2)?></span>
								</li>
							</ul>
						</div>
					</fieldset>	
				</div>

			<!-- 	<div class="col-lg-7">
					<fieldset class='category-border'>
						<legend class="category-border">Last payment Details
						</legend>
					
					</fieldset>
				</div> -->
			</fieldset>

			<!-- <fieldset class='category-border-main'>
				<div class="category-border-main bg-success text-center">
					Last payment History Listing
					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">Last payment Details
							</legend>
							<div class="col-lg-7">	
								<table class="table table-striped table-hover">
									<thead>
										<tr>
										<th class="col-lg-3">Payment No</th>
										<th class="col-lg-3">Amount</th>
										<th class="col-lg-6">Remark</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><?php //echo $unpayment_data['payment_no']?></td>
											<td><?php //echo $unpayment_data['amount']?></td>
											<td><?php //echo $unpayment_data['remark'] ?></td>
										</tr>
									</tbody>
								</table>	
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			 -->
			<fieldset class='category-border-main'>
				<div class="category-border-main bg-success text-center">
					Bill History Listing
				</div>

				<div class="hidden-xs">
					<table class="table table-striped table-hover">
						<thead>
							<tr>
								<th class="col-lg-1">Bill No</th>
								<th class="col-lg-1">Bill Date</th>
								<th class="col-lg-1 text-right">Prev Bal</th>
								<th class="col-lg-1 text-right">Pay Rec</th>
								<th class="col-lg-1 text-right">Charges</th>
								<th class="col-lg-1 text-right">Tax</th>
								<th class="col-lg-1 text-right">Amount</th>
								<th class="col-lg-1 text-right">Balance</th>
								<th class="col-lg-1 text-right">Action</th>
							</tr>
						</thead>

						<tbody>
							{row_data}
								<tr class="{tr_class}">
									<td>{bill_no}</td>
									<td>{bill_date}</td>
									<td class="text-right">{previous_balance}</td>
									<td class="text-right">{payment_received}</td>
									<td class="text-right">{charges}</td>
									<td class="text-right">{tax_charges}</td>
									<td class="text-right">{amount}</td>
									<td class="text-right">{balance}</td>
									<td class="text-right action_btn_td">
										{print_invoice_action}
										{print_bill_action}
										{detail_action}
										{void_action}
										{einvoice_action}
									</td>
								</tr>
							{/row_data}
						</tbody>

					</table>
				</div>

				<div class="visible-xs">

					<?php if (!empty($row_data)): ?>

						<?php foreach ($row_data as $row): ?>

							<div style="margin-bottom: 12px; background: #fff; border-radius:10px; overflow: hidden; font-size: 14px; border: 1px solid #ccc;">
								<div style="padding:12px 14px;background: #fafafa;border-bottom:1px solid #f2f2f2;">

									<div style="font-size:15px;font-weight:600;">
										Bill #<?= htmlspecialchars($row['bill_no'] ?? '') ?>
									</div>

									<div class="grey" style="font-size: 12px; margin-top: 3px;">
										<?= htmlspecialchars($row['bill_date'] ?? '') ?>
									</div>

								</div>

								<div style="padding: 12px;">
									<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
										<div class="text-left">
											<div class="grey" style="font-size: 11px;">Prev Bal</div>
											<div>
												<?= $row['previous_balance'] ?? '' ?>
											</div>
										</div>

										<div class="text-right">
											<div class="grey" style="font-size: 11px;">Pay Rec</div>
											<div class="green" style="font-weight: bold;">
												<?= $row['payment_received'] ?? '' ?>
											</div>
										</div>
									</div>

									<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
										<div class="text-left">
											<div class="grey" style="font-size:11px;">Charges</div>
											<div>
												<?= $row['charges'] ?? '' ?>
											</div>
										</div>

										<div class="text-right">
											<div class="grey" style="font-size: 11px;">Tax</div>
											<div>
												<?= $row['tax_charges'] ?? '' ?>
											</div>
										</div>
									</div>

									<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
										<div class="text-left">
											<div class="grey" style="font-size: 11px;">Amount</div>
											<div class="orange" style="font-weight: bold;">
												<?= $row['amount'] ?? '' ?>
											</div>
										</div>

										<div class="text-right">
											<div class="grey" style="font-size: 11px;">Balance</div>
											<div class="pink" style="font-weight: bold;">
												<?= $row['balance'] ?? '' ?>
											</div>
										</div>
									</div>

									<div style="border-top: 1px dashed #eee; padding-top: 10px; display: flex; flex-wrap: wrap; gap: 20px; font-size: 14px !important; justify-content: space-evenly;">
										<?= $row['print_invoice_action'] ?? '' ?>
										<?= $row['print_bill_action'] ?? '' ?>
										<?= $row['detail_action'] ?? '' ?>
										<?= $row['void_action'] ?? '' ?>
										<?= $row['einvoice_action'] ?? '' ?>
									</div>
								</div>
							</div>

						<?php endforeach; ?>

					<?php else: ?>

						<div style="padding:15px; text-align:center;" class="grey">
							No invoice found
						</div>

					<?php endif; ?>

				</div>
			</fieldset>
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

<script src="<?php echo base_url("js/itelco/bill.js?".cssjs_ver()); ?>" ></script>

<script>
	
$('.is_void').css("color", "red");

function prep_void_bill( bill_no ) {
	$('#bill_no_span').html( bill_no );
	$('#void_reason').val('');
	$('#void_bill_no').val( bill_no );
	$('#void_reason-modal').modal('show');
}
	
function void_bill(){	
	
	//if( confirm('Are you sure to void Bill No ' + bill_no + '?\nThis is not reversible process.' ) )
	//{

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
	//}
}

function update_po_no( bill_no ){
	$.ajax({
		type: "POST",
		url: base_url + "bill/ajax_update_po_no/" ,
		dataType: "json",
		data: { 
				bill_no: bill_no ,
				po_no : $('#po_' + bill_no ).val()
			  },
		success: function( data ) {
			
			if( data['success'] )
				alert(data['success']);
			else
				alert(data['fail']);
			
			//window.location.reload();
				
		},
		error: function (xhr, ajaxOptions, thrownError) {
			alert('error');
		}
	});
}

</script>

