<script src="<?php echo base_url('js/jquery.min.js'); ?>" ></script>

<div class="book" >	
<?php if(!empty($so_id)):?>	
	<div class="page" data-page="{so_id}" > 
		<div class="subpage">
			
			<div class="col-lg-12 text-left" style="font-size: 10px !important;">
				<table cellpadding="0" cellspacing="0" style="width:100%;">
					<tr>
						<td rowspan="2" style="260px;">
							<?php
							$company_logo = $this->config->item('logo_img'); 
							?>
							<?php if (!empty($company_logo)) { ?>
							<img style="width:150px;" src="<?php echo $company_logo; ?>" >
							<?php } else { ?>
							<img src="<?php echo base_url("/images/telco-icon.png"); ?>" >ITELCO
							<?php } ?>
						</td>
						<td style="width:60px;"></td>
						<td style="width:60px;padding-top:8px;padding-right:3px;vertical-align:top;text-align:left;height:55px;">
							<!--<img width="60px" height="60px" src='<?php echo base_url("images/jompay_logo.jpg");?>' />-->
						</td>
						<td style="width:380px;text-align:left;padding-top:8px;height:55px;">
							<!--<table style="float:right;border-style:solid;border-width:1px;width:380px;font-size:10px ! important;line-height: 10.5px ! important; height: 45px ! important;margin-top:0px;margin-right:63px;" cellspacing=5>
								<tr><td style="text-align:left;padding-left:3px;"><b>Biller Code</b>: {jompay_biller_code} </td></tr>
								<tr><td style="text-align:left;padding-left:3px;"><b>Ref-1</b>: {customer_no}</td></tr>
								<tr><td style="text-align:left;padding-left:3px;"><b>Ref-2</b>: {display_name}</td></tr>
							</table>-->
						</td>
					</tr>
					<tr>
						<td style="width:60px;"></td>
						<td colspan="2" style="vertical-align:top;padding-left:2px;">
							<!--<span style="font-weight:bold;">JomPAY</span><span> online at Internet and Mobile Banking with your Current or Savings account</span>-->
						</td>
					</tr>
					<!--
					<tr>
						<td class="text-left" style="font-size:10px;padding-left:0px;" colspan="2"><b>JomPAY</b> online at Internet and Mobile Banking with your Current or Savings account
						</td>
					</tr>
					-->
				</table>
			</div>
			
			<div class="col-lg-12 content_double_line"></div>				
			<div class="col-lg-12">&nbsp;</div>
				<table border="0" width="98%" class="center" style="">
					<tr>
						<td width="100%" style="vertical-align:top">		
							<table border="0" width="100%">
								<tr style="vertical-align:top">
									<td colspan='2' style="font-size:20px; padding-bottom: 10px; text-align: center;"><b>{doc_title}</b></td>
								</tr>
								<tr style="vertical-align:top">
									<td style="padding-bottom: 10px;"><b>{cust_name}</b></td>
								</tr>
								<tr style="vertical-align:top;">
									<td width="50%"><b>Billing Address</b></td>
									<td width="50%" ><b>Delivery Address</b>&nbsp&nbsp</td>
								</tr>
								<tr style="vertical-align:top">
									<td style="padding-bottom: 10px;">
										<?php if (!empty($bill_unit_no)) echo $bill_unit_no . ','; ?>
										<?php if (!empty($bill_addr_1)) echo $bill_addr_1 . ',' . '<br>'; ?>
										<?php if (!empty($bill_addr_2)) echo $bill_addr_2 . ',' . '<br>'; ?>
										<?php if (!empty($bill_addr_3)) echo $bill_addr_3 . ',' . '<br>'; ?>
										<?php if (!empty($bill_postcode)) echo $bill_postcode . ', '; ?>
										<?php if (!empty($bill_city)) echo $bill_city . ', '; ?>
										<?php if (!empty($bill_actual_state)) echo $bill_actual_state; ?>
									</td>
									<td style="padding-bottom: 10px;">
										<?php if (!empty($del_unit_no)) echo $del_unit_no . ','; ?>
										<?php if (!empty($del_addr_1)) echo $del_addr_1 . ',' . '<br>'; ?>
										<?php if (!empty($del_addr_2)) echo $del_addr_2 . ',' . '<br>'; ?>
										<?php if (!empty($del_addr_3)) echo $del_addr_3 . ',' . '<br>'; ?>
										<?php if (!empty($del_postcode)) echo $del_postcode . ', '; ?>
										<?php if (!empty($del_city)) echo $del_city . ', '; ?>
										<?php if (!empty($del_actual_state)) echo $del_actual_state; ?>
									</td>
								</tr>
								<tr>
									<td>
										<table>
											<tr>
												<td style="font-weight: bold;">Attention</td>
												<td>:&nbsp;</td>
												<td><?= $bill_attn ?></td>
											</tr>
											<tr>
												<td style="font-weight: bold;">Tel:</td>
												<td>:&nbsp;</td>
												<td><?= $bill_tel ?></td>
											</tr>
										</table>
									</td>
									<td>
										<table>
											<tr>
												<td style="font-weight: bold;">Fax</td>
												<td>:&nbsp;</td>
												<td><?= $bill_fax ?></td>
											</tr>
											<tr>
												<td style="font-weight: bold;">Email</td>
												<td>:&nbsp;</td>
												<td><?= $bill_email ?></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				
			<div class="col-lg-12">&nbsp;</div>
			<div class="col-lg-12 content_single_line"></div>
							
			<div class="col-lg-12">
				<table width="100%" style="border-collapse:collapse;" class="item_table" cellpadding="0" cellspacing="0">
					<tr>
						<th>No</th>
						<th width="50%">Description</th>
						<th style="text-align: center;">QTY</th>
						<th style="text-align: center;">UOM</th>
						<th style="text-align: center;">Price (RM)</th>
						<th style="text-align: center;">Subtotal (RM)</th>
						<th style="text-align: center;">Tax (RM)</th>
						<th style="text-align: center;">Total (RM)</th>
					</tr>
					<?php $total_payable = $so_details['total']; ?>
					<?php if(!empty($so_details['package'])){ ?>
						<tr>
							<td>1</td>
							<td><?= $so_details['item_name'] ?></td>
							<td style="text-align: center;"><?= $so_details['quantity'] ?></td>
							<td style="text-align: center;"><?= $so_details['uom'] ?></td>
							<td style="text-align: right;"><?= number_format($so_details['price'], 2) ?></td>
							<td style="text-align: right;"><?= number_format($so_details['subtotal'], 2) ?></td>
							<td style="text-align: right;"><?= number_format($so_details['tax'], 2) ?></td>
							<td style="text-align: right;"><?= number_format($so_details['total'], 2) ?></td>
						</tr>
						<tr>
							<td>2</td>
							<td><?= $so_details['package']['package_month'] ?> months</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					<?php }else{ ?>
						<tr>
							<td colspan="8" style="text-align: center;">No Package</td>
						</tr>
					<?php }?>
					<?php $table_no = 2; $row_count = 2;
						if (isset($so_details['package']['installation']) && $so_details['package']['installation'] > 0) {
							$total_payable += $so_details['package']['installation'];
							$table_no++;
					?>
						<tr>
							<td><?= $table_no ?></td>
							<td><?php echo "Installation Fee" ?></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td style="text-align: right;"><?= $so_details['package']['installation'] ?></td>
						</tr>
					<?php } ?>
					<?php foreach($dia_vars as $dia_var_key => $dia_var_value){ 
						$table_no++;
						$row_count++;
					?>
						<tr>
							<td><?= $table_no ?></td>
							<td><b><?= $so_details['item_name'] . '-' . $dia_var_key . ": " ?></b><?= $dia_var_value ?></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					<?php } ?>
					<?php if(isset($so_details['package']['deposit']) && $so_details['package']['deposit'] > 0) { 
						$total_payable += $so_details['package']['deposit'];
						$table_no++;
					?>
					<tr>
						<td><?= $table_no ?></td>
						<td>
							Payment Term: <b>Deposit</b>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><?= number_format($so_details['package']['deposit'], 2) ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="7" style="text-align: right;"><b>Total Payable (RM): </b></td>
						<td><?= number_format($total_payable, 2) ?></td>
					</tr>
				</table>

			</div>
			
			<div class="print-footer" width="100%" style="border-top:0px solid rgba(0,0,0,0.1); position: absolute; bottom: 0; text-align :center; line-height :1.5em; font-size :0.7em; color :rgba(0,0,0,0.5);">
				<table class="center" style="width:98%;">
					<tbody>
						<tr style="width:100%;">
							<td style="width:12%;">

							</td>
							<td style="width:8%; text-align:left;border: 1px solid rgba(0,0,0,0.2); padding:4px;">		
								<span style="padding-left:0.5em;">
								<b> Issued By :&nbsp;<?= $issue_by ?><br></b>
								</span>
								<br><br><br><br><br>
							</td>
						</tr>
					</tbody>
				</table>
				<div style="padding-top:0.5em;padding-bottom:0.5em;">
					<?= $company_address ?><br>
					<?= "Tel: " . $company_tel ?>
				</div>
			</div>
						
		</div>
	</div>		
<?php endif; ?>	
</div>


<script>
	
$( document ).ready( function(){

	var max_doc = $('.page').length ;

	for( let x = 0 ; x < max_doc ; x++ ){
	  let this_doc_no = $('.page').eq( x ).attr('data-page'); ;
	  sort_content( this_doc_no , 0 );
	  sort_footer( this_doc_no );
	}

  
  //Whole A4 paper = 1125
  //Header( logo + customer detail ) = 420
  //Item List = 1125-420 ~=> at most 705 , deduct by border bottom ~=> at most 660
  //Footer ( subtotal + payment terms n condition ) = 350
  //Warning : footer should not greater than printable area
  //else JS upcar

	function sort_content( doc_no, page_no ){
		var next_page_no = page_no ;
		var ttl_height = 0 ;
		var doc_type = $('.page[data-page="'+doc_no+'"]:eq('+page_no+')').attr('data-doc-type');
		var printable_height = 500;
		console.log( "Document # " + doc_no );
		console.log( "Page # " + ( page_no + 1 ) );
		// console.log( "Doc Type => " + doc_type );
		
		//var tbl_row = document.getElementsByClassName('page')[page_no].getElementsByClassName('item_table')[0].rows.length - 1 ;
		var tbl_row = document.querySelectorAll("[data-page='"+doc_no+"']")[page_no].getElementsByClassName('item_table')[0].rows.length - 1 ;
		for( var y = 0 ; y <= tbl_row ; y++ ){
		  
		  //ttl_height = ttl_height + document.getElementsByClassName('page')[page_no].getElementsByClassName('item_table')[0].rows[y].offsetHeight;
		  ttl_height = ttl_height + document.querySelectorAll("[data-page='"+doc_no+"']")[page_no].getElementsByClassName('item_table')[0].rows[y].offsetHeight;
		  
		  //console.log("Accumulating Height => " + ttl_height);

		  if( ttl_height > printable_height ){ //only 520 for item listing

			//console.log("Accumulating Height OVER, moving Row # " + y + ' and onwards to next page');
			
			//clone this page and insert AFTER SELF, and remove its item listing
			var clone_page = $('.page[data-page="'+doc_no+'"]').eq(page_no).clone();
			clone_page.insertAfter('.page[data-page="'+doc_no+'"]:eq('+page_no+')').find('.item_table > tbody >tr').remove();

			//get Y-th and onwards row and move it to NEXT page
			for( var x = y ; x <= tbl_row ; x++ ){
			  $('.page[data-page="'+doc_no+'"]:eq('+page_no+') .item_table:eq(0)').find('tbody >tr:eq('+y+')').appendTo('.page[data-page="'+doc_no+'"]:eq('+(page_no+1)+') .item_table:eq(0) > tbody');
			}
			
			//next_page_no++ , prepare to run this sort_content function again
			next_page_no = parseInt( page_no ) + 1 ;
			break;

		  }

		}
		
		if( next_page_no > page_no ){
		  console.log('Sorting next page : ' + next_page_no );
		  sort_content( doc_no, next_page_no );
		}else{
		  console.log('End sorting Doc # ' + doc_no );
		}

	}
	function sort_footer( doc_no ){
		$('.page[data-page="'+doc_no+'"]:not(":first")').find('.account-summary').remove();
		$('.page[data-page="'+doc_no+'"]:not(":last")').find('.print-footer').remove();
	}
});

</script>
