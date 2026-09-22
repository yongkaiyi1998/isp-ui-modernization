<!--<script src="<?php echo base_url('js/jquery.min.js'); ?>" ></script>-->
<div class="book" >	
	{data}
		<div class="page" data-page="{customer_no}" > 
			<div class="subpage">

				<div class="col-lg-12 text-left" style="font-size: 10px !important;">
					<table cellpadding="0" cellspacing="0" style="width:100%;">
						<tr>
							<td style="width:80px;vertical-align: top;">
								<?php
								$company_logo = $this->config->item('doc_logo');
								$proj_name = $this->config->item('proj_name');

								if (empty($proj_name)) {
									$proj_name = 'Itelco';
								}
								?>
								<?php if (!empty($company_logo)) { ?>
								<img style="width:70px;margin:5px;" src="<?php echo $company_logo; ?>" >
								<?php } else { ?>
								<img src="<?php echo $logo_url; ?>" ><?php echo $proj_name; ?>
								<?php } ?>
							</td>
							<td style="vertical-align: top;">
								<div style="font-size:14px;margin:5px;">
									<!-- address -->
									<span style="font-weight:bold;"><?php echo $company_full_name; ?> <?php if(!empty($company_brn)) echo '(' . $company_brn . ')'; ?></span><br>
									<?php if (!empty($company_addr_1)) { ?>
									<?php echo $company_addr_1; ?><br>
									<?php } ?>
									<?php if (!empty($company_addr_2)) { ?>
									<?php echo $company_addr_2; ?><br>
									<?php } ?>
									<?php if (!empty($company_addr_3)) { ?>
									<?php echo $company_addr_3; ?><br>
									<?php } ?>
									<?php if (!empty($company_postal) && !empty($company_city) && !empty($company_state)) { ?>
									<?php echo $company_postal; ?> <?php echo $company_city; ?>, <?php echo $company_state; ?>, Malaysia<br><br>
									<?php } ?>
									<?php if (!empty($company_phone) || !empty($company_fax) || !empty($company_email)) { ?>
									<?php if (!empty($company_phone)) { echo "Tel: ".$company_phone."&nbsp;&nbsp;"; } ?><?php if (!empty($company_fax)) { echo "Fax: ".$company_fax."&nbsp;&nbsp;"; } ?><?php if (!empty($company_email)) { echo "Email: ".$company_email."&nbsp;&nbsp;"; } ?>
									<?php } ?>
								</div>
							</td>
						</tr>
	
					</table>
				</div>
				
				
				
				<div class="content_body" style="font-size: 12px;">
					
					<div class="col-lg-12 content_double_line"></div>
					<div class="col-lg-12">&nbsp;</div>

					<table border="0" class="col-lg-12" >
						<tbody>
							<tr>
								<td width="100%" style="vertical-align:top;text-align: left;">									
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
											<tr><td colspan="6">{display_city} {display_postcode} {display_state}, MALAYSIA</td></tr>
											<tr>
												<td>
													{einvoice_qr}
												</td>
											</tr>
										</tbody>
									</table>									
								</td>
							</tr>
						</tbody>
					</table>

					<h2 class="text-center" style="text-align:center;font-family: Helvetica, Arial, sans-serif;">
						<?php echo strtoupper(malay_lang(strtolower($data[$bill_no]['doc_title']))); ?>
					</h2>
					<div class="col-lg-12">&nbsp;</div>
					
					<table border="0" class="col-lg-12" >
						<tbody>
							<tr>
								<td width="50%" style="vertical-align:top">
									<table border="0" >
										<tbody>
											<tr>
												<td class="text-left">Nama Projek</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{project_name}</td>
											</tr>
											<tr>
												<td class="text-left">No. Unit</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{unit_name}</td>
											</tr>
											<tr>
												<td class="text-left">No. Akaun</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{customer_no}</td>
											</tr>
											<tr>
												<td class="text-left">No. Akaun Bank</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{comp_bank_acc}</td>
											</tr>
										</tbody>
									</table>
								</td>
								<td width="50%" style="vertical-align:top">
									<table border="0" >
										<tbody>
											<?php if (!empty($company_tax_number)) { ?>
											<tr>
												<td class="text-left tax-toggle">No. SST</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{company_tax_number}</td>
											</tr>
											<?php } ?>
											<tr>
												<td class="text-left">No. Bil</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_no}</td>
											</tr>
											<tr>
												<td class="text-left">Tarikh Bil</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_date}</td>
											</tr>
											<tr>
												<td class="text-left">Tarikh Akhir Bayaran</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_due_date}</td>
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
								<th class="text-center">No.</th>
								<th class="text-center">Perihal</th>
								<th class="text-center">Amaun<br>( {currency_code} )</th>
								<th class="text-center col-lg-1">Kod Cukai</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($data[$bill_no]['bill_detail'] as $detail_key => $detail_row) { ?>
							<tr>
								<td style='text-align:right;padding:0px 5px 0px 5px;'><?php echo $detail_row['item_count']; ?></td>
								<td style='text-align:left;padding:0px 5px 0px 5px;'><?php echo $detail_row['bill_type_name']; ?> <?php if (!empty($detail_row['remark'])) { ?>(<?php echo $detail_row['remark']; ?>)<?php } ?></td>
								<td style='text-align:right;padding:0px 5px 0px 5px;'><?php echo $detail_row['item_amount']; ?></td>
								<td style='text-align:center;padding:0px 5px 0px 5px;' ><?php echo $detail_row['tax_code']; ?></td>
							</tr>
							<?php } ?>
							<tr>
								<td></td>
								<td style='padding:0px 5px 0px 5px;'>Pakej: {package_name}</td>
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
									</td>
									<td class="col-lg-4" style="text-align:right">Amaun :</td>
									<td class="col-lg-2" style="text-align:right">{charge}</td>
								</tr>
								<tr class="tax-toggle" style="">
									<td style="text-align:right">Cukai {default_tax}%</td>
									<td style="text-align:right">{tax_charge}</td>
								</tr>
								<tr>
									<td style="text-align:right"><b>Jumlah Bersih ( {currency_code} ):</b></td>
									<td style="text-align:right">				
										<div class="single_underline">&nbsp;</div>
										<span>{amount}</span> 
										<div style="line-height:50%;">&nbsp;</div>
										<div class="double_underline">&nbsp;</div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><b>RINGGIT : <?php $convert = ucwords(convert_number_to_words_malay($data[$bill_no]['actual_amount'])); echo $convert; ?> SAHAJA</b></td>
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
					<!--<div class="text-center" style="font-weight:bold" >SERVICE PROVIDED BY {company_full_name}</div>-->
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
		var invoice_detail_height = 220;
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
