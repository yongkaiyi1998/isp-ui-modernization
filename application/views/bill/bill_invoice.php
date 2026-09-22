<!--<script src="<?php echo base_url('js/jquery.min.js'); ?>" ></script>-->

<div class="book" >	
	{data}
		<div class="page" data-page="{customer_no}" > 
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
								<table style="float:right;border-style:solid;border-width:1px;width:380px;font-size:10px ! important;line-height: 10.5px ! important; height: 45px ! important;margin-top:0px;margin-right:63px;" cellspacing=5>
									<!--<tr><td style="text-align:left;padding-left:3px;"><b>Biller Code</b>: {jompay_biller_code} </td></tr>-->
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-1</b>: {customer_no}</td></tr>
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-2</b>: {display_name}</td></tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="width:60px;"></td>
							<td colspan="2" style="vertical-align:top;padding-left:2px;">
								<!--<span style="font-weight:bold;">JomPAY</span><span> online at Internet and Mobile Banking with your Current or Savings account</span>-->
							</td>
						</tr>
					</table>
				</div>
				
				
				
				<div class="content_body" style="font-size: 12px;">
					
					<div class="col-lg-12 content_double_line"></div>
					<div class="col-lg-12">&nbsp;</div><div class="col-lg-12">&nbsp;</div>
					<h2 class="text-center" style="text-align:center;font-family: Helvetica, Arial, sans-serif;">
						{doc_title}
					</h2>
					<div class="col-lg-12">&nbsp;</div>
					
					<table border="0" class="col-lg-12" >
						<tbody>
							<tr>
								<td width="60%" style="vertical-align:top">									
									<table border="0"  >
										<tbody>
											<tr><td colspan="6">{display_name}</td></tr>
											<!-- <tr><td colspan="6">{display_name} {reg_no}</td></tr> -->
											<?php if (!empty($data[$bill_no]['display_unit_no'])) { ?>
											<tr><td colspan="6"><?php echo $data[$bill_no]['display_unit_no']; ?></td></tr>
											<?php } ?>
											<tr><td colspan="6">{display_addr1}</td></tr>
											<tr><td colspan="6">{display_addr2}</td></tr>
											<tr><td colspan="6">{display_addr3}</td></tr>
											<tr><td colspan="6">{display_city}</td></tr>
											<tr><td colspan="6">{display_postcode} {display_state}</td></tr>
											<tr>
												<td class="text-left" style="width:0.2em;">Tel</td>
												<td class="text-left" style="width:0.2em;">:&nbsp;</td>
												<td class="text-left" style="width:14em;">{tel_num}</td>
												<td class="text-left" style="width:0.2em;">Email</td>
												<td class="text-left" style="width:0.2em;">:&nbsp;</td>
												<td class="text-left" style="width:14em;"> {email_addr}</td>
											</tr>
										</tbody>
									</table>									
								</td>
								<td width="40%" style="vertical-align:top">
									<table border="0" >
										<tbody>
											<?php if (!empty($company_tax_number)) { ?>
											<tr>
												<td class="text-left">SST Reg. No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{company_tax_number}</td>
											</tr>
											<?php } ?>
											<tr class="tax-toggle" style="">
												<td class="text-left">Bill No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_no}</td>
											</tr>
											<tr>
												<td class="text-left">Bill Date</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_date}</td>
											</tr>
											<tr>
												<td class="text-left">Payment Term</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{payment_term} days</td>
											</tr>
											<tr>
												<td class="text-left">PO No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{po_no}</td>
											</tr>
											<tr>
												<td class="text-left">Subscriber No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{customer_no}</td>
											</tr>
										</tbody>
									</table>	
								</td>
							</tr>
						</tbody>
					</table>
					
					
					
					<div class="col-lg-12">&nbsp;</div>
					
					<table border="1" bordercolor='C0C0C0' width="98%" style="margin: auto;" class="item_table">
						<thead>
							<tr>
								<th class="text-center">Item</th>
								<th class="text-center">Description</th>
								<th class="text-center">Amount<br>( {currency_code} )</th>
								<th class="text-center">Tax Code</th>
							</tr>
						</thead>
						<tbody>
							{bill_detail}
							<tr>
								<td style='text-align:right;padding:0px 5px 0px 5px;'>{item_count}</td>
								<td style='text-align:left;padding:0px 5px 0px 5px;'>{bill_type_name_with_remark}</td>
								<td style='text-align:right;padding:0px 5px 0px 5px;'>{item_amount}</td>
								<td style='text-align:center;padding:0px 5px 0px 5px;' >{tax_code}</td>
							</tr>
							{/bill_detail}
							<tr>
								<td></td>
								<td style='padding:0px 5px 0px 5px;'>Package: {package_name}</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
					
					<div class="col-lg-12">&nbsp;</div>
					<span class="row invoice_detail">
						<table class="table borderless table-condensed center" style="width:98%; margin-top:-1em;">
							<tbody>
								<tr>
									<td class="col-lg-6 text-right" rowspan="3">
										<?php if (!empty($einvoice_qr)) { ?>
										<div class="col-lg-12 col-xs-12 well" style="padding:0.5em;font-size:14px;">
											E-Invoice QR Code
											{einvoice_qr}
										</div>
										<?php } ?>
									</td>
									<td class="col-lg-4" style="text-align:right">Subtotal :</td>
									<td class="col-lg-2" style="text-align:right">{charge}</td>
								</tr>
								<tr class="tax-toggle" style="">
									<td style="text-align:right">Service Tax {default_tax}%</td>
									<td style="text-align:right">{tax_charge}</td>
								</tr>
								<tr>
									<td style="text-align:right"><b>Total Amount ( {currency_code} ):</b></td>
									<td style="text-align:right">				
										<div class="single_underline">&nbsp;</div>
										<span>{amount}</span> 
										<div style="line-height:50%;">&nbsp;</div>
										<div class="double_underline">&nbsp;</div>
									</td>
								</tr>
							</tbody>
						</table>
					</span>
				</div>
				
				<div class="content_footer" style="position:absolute;padding-top:5px;width:100%;">
					<div class="col-lg-12 content_single_line">&nbsp;</div><?php //single line -------------------------------- ?>
					<div class="col-lg-12 text-left" style="line-height:1.15em;font-size:11px;text-align:left;">
					{bills_footer}
					</div>
					<div class="col-lg-12">&nbsp;</div>
					<div class="text-center" style="font-weight:bold" >SERVICE PROVIDED BY {company_full_name}</div>
					<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
				</div>
				
				<!--
				<div class="content_footer">					
					<div class="content_single_line"></div><?php //single line -------------------------------- ?>

					<div>&nbsp;</div>
					<table class="center" style="width:100%; ">
						<tbody>
							<tr style="width:100%;">
								<td style="text-align:left; padding:0.5em;">
									<p style="display:none;"><b>Payment Information:</b></p>				
									<ul>
										<li><span style="position: relative; left: -0.6em; top: 0.1em;">
												All payment by cheque should be crossed and made payable to <br>
												<b> MY PENANG FON SDN BHD</b><br>
												<b>Maybank A/C No: 5070 4002 7508</b>
											</span>
										</li>
										<li><span style="position: relative; left: -0.6em; top: 0.1em;">Interest will be charged at 1.5% per month on overdue accounts</span></li>							
									</ul>		
								</td>
							</tr>
						</tbody>
					</table>
					<table class="center" style="width:98%;">
						<tbody>
							<tr>
								<td style="width:50%;text-align:left;border: 1px solid rgba(0,0,0,0.2); padding:5px;">&nbsp;
								<span style="padding-left:1em;">
										<b  style="display:none;">CHECKED &amp; RECEIVED BY:</b>
									</span>
									<br><br>
									<br><br>	
									<br><br>	
									<div class="text-center" style="border-top:1px solid rgba(0,0,0,0.2); ">
										<i>Authorised Signature</i>
									</div>	
								</td>
								<td style="width:50%; text-align:left;border: 1px solid rgba(0,0,0,0.2); padding:5px;">		
									
									<span style="padding-left:1em;">
										<b>ACKNOWLEDGED RECEIPT BY:</b>	
									</span>
									<div>&nbsp;</div>	
									<table style=" border: 0px solid black;">
										<tbody>
											<tr>
												<td class="col-lg-1 text-left">NAME</td>
												<td class="col-lg-1 text-left">:</td>
												<td class="col-lg-9 text-left footer_line" >&nbsp;&nbsp;</td>
											
												<td class="col-lg-1 text-left"></td>
											</tr>
											<tr>
												<td class="col-lg-1 text-left">I/C NO</td>
												<td class="col-lg-1 text-left">:</td>
												<td class="col-lg-9 text-left footer_line" >&nbsp;&nbsp;</td>
											
												<td class="col-lg-1 text-left"></td>
											</tr>
											<tr>
												<td class="col-lg-1 text-left">SIGNATURE</td>
												<td class="col-lg-1 text-left">:</td>
												<td class="col-lg-9 text-left footer_line" >&nbsp;&nbsp;</td>
											
												<td class="col-lg-1 text-left"></td>
											</tr>
											<tr>
												<td class="col-lg-1 text-left">DATE</td>
												<td class="col-lg-1 text-left">:</td>
												<td class="col-lg-9 text-left footer_line" >&nbsp;&nbsp;</td>
											
												<td class="col-lg-1 text-left"></td>
											</tr>
											<tr>
												<td class="col-lg-1 text-left">CO. CHOP</td>
												<td class="col-lg-1 text-left" >:</td>
												<td class="col-lg-9 text-left footer_line" >&nbsp;&nbsp;</td>
											
												<td class="col-lg-1 text-left"></td>
											</tr>
										</tbody>										
									</table>											
									<div>&nbsp;</div>								
								</td>
							</tr>
						</tbody>
					</table>
					<div>&nbsp;</div>					
				</div>
				-->
			</div>
		</div>
	{/data}
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
		var printable_height = 520;
		var invoice_detail_height = 120;
		console.log( "Document # " + doc_no );
		console.log( "Page # " + ( page_no + 1 ) );
		// console.log( "Doc Type => " + doc_type );
		
		//var tbl_row = document.getElementsByClassName('page')[page_no].getElementsByClassName('item_table')[0].rows.length - 1 ;
		var tbl_row = document.querySelectorAll("[data-page='"+doc_no+"']")[page_no].getElementsByClassName('item_table')[0].rows.length - 1 ;
		for( var y = 0 ; y <= tbl_row ; y++ ){
		  
		  //ttl_height = ttl_height + document.getElementsByClassName('page')[page_no].getElementsByClassName('item_table')[0].rows[y].offsetHeight;
		  ttl_height = ttl_height + document.querySelectorAll("[data-page='"+doc_no+"']")[page_no].getElementsByClassName('item_table')[0].rows[y].offsetHeight;
		  
		  console.log("Accumulating Height => " + ( ttl_height + invoice_detail_height ) );

		  if( ( ttl_height + invoice_detail_height )  > printable_height ){ //only 520 for item listing

			console.log("Accumulating Height OVER, moving Row # " + y + ' and onwards to next page');
			
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
		//$('.page[data-page="'+doc_no+'"]:not(":first")').find('.account-summary').remove();
		$('.page[data-page="'+doc_no+'"]:not(":last")').find('.invoice_detail').remove();
	}
});

</script>
