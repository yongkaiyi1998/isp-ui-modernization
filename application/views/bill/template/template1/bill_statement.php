<style>
@page {
    size: a4 portrait;
}

html {
  -webkit-print-color-adjust: exact;
}
</style>
<?php 
$logo_url = base_url("images/telco-icon.png");
if (isset($gen_pdf)) {

	$local_base_url = $this->config->item('local_base_url');

	if (!empty($local_base_url)) {
		$logo_url = $local_base_url."images/telco-icon.png";
	}

}
?>
<div class="book" >
<?php if(!empty($data)):?>	
<?php foreach ($data as $d_key => $d_val) { ?>
	<?php foreach ($d_val['customer'] as $c_key => $c_val) { ?>
		<?php foreach ($d_val['bill'] as $b_key => $b_val) { ?>
		<div class="page" data-page="<?php echo $b_val['customer_no']; ?>" > 
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
				
				<div class="col-lg-12 content_double_line"></div>				
				<div class="col-lg-12">&nbsp;</div>
					<table border="0" width="98%" class="center" style="">
						<tr>
							<td width="45%" style="vertical-align:top">		
								<table border="0">
									<tr style="vertical-align:top">
										<td colspan='3' style="font-size:18px;"><?php echo strtoupper(malay_lang(strtolower($b_val['doc_title']))); ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_name']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_unit_no']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_addr1']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_addr2']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_addr3']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_city']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_postcode']; ?> <?php echo $c_val['display_state']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td width="35%">No. Akaun</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="60%"><b><?php echo $c_val['customer_no']; ?></b></td>
									</tr>
								</table>
							</td>
							<td width="55%" style="vertical-align:top">
								<table border="0">
									
									<?php if (!empty($company_tax_number)) { ?>
									<tr style="vertical-align:top">
										<td width="35%">No. SST</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="60%"><?php echo $company_tax_number; ?></td>
									</tr>
									<?php } ?>
									
									<tr style="vertical-align:top">
										<td width="35%">Pakej</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="60%"><?php echo $c_val['package_name']; ?></td>
									</tr>								
									<tr>
										<td>No. Bil</td>
										<td>&nbsp;:&nbsp;</td>
										<td><?php echo $b_val['bill_no']; ?></td>
									</tr>		
									<tr>
										<td>Tarikh Bil</td>
										<td>&nbsp;:&nbsp;</td>
										<td><?php echo $b_val['bill_date']; ?></td>
									</tr>
									<tr>
										<td>Tarikh Akhir Bayaran</td>
										<td>&nbsp;:&nbsp;</td>
										<td><b><?php echo $c_val['pay_on_or_before']; ?></b></td>
									</tr>
									<tr>
										<td>E-mel</td>
										<td>&nbsp;:&nbsp;</td>
										<td><?php echo $c_val['email_1']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td>Deposit</td>
										<td>&nbsp;:&nbsp;</td>
										<td><b><?php echo $c_val['currency_code']; ?> <?php echo $c_val['deposit']; ?></b></td>
									</tr>
									<?php if (!empty($b_val['einvoice_qr'])) { ?>
									<tr style="vertical-align:top">
										<td>QR MyInvois</td>
										<td>&nbsp;:&nbsp;</td>
										<td><?php echo $b_val['einvoice_qr']; ?></td>
									</tr>
									<?php } ?>
								</table>
							</td>
						</tr>
					</table>
					
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12 content_single_line"></div>
								
				<div class="col-lg-12 content_body">
				<table class="summary-header" border="0" style="width:100%;font-size:13px;">
						<tr>
							<td class="to_underline" width="60%"><h5 style="font-family:sans-serif"><b>Ringkasan Akaun</b></h5></td>
							<td width="5%"></td>
							<td class="text-right to_underline" style="text-align:right;"  width="20%"><h5 style="font-family:sans-serif"><b>Amaun (<?php echo $c_val['currency_code']; ?>)</b></h5></td>
						</tr>
						<tr>
							<td class="text-left ">Baki Sebelum</td>
							<td><b>:</b></td>
							<td class="text-right " style="text-align:right;"><?php echo $b_val['previous_balance']; ?></td>
							<td class="text-center "></td>
						</tr>
						<tr>
							<td class="text-left " >Bayaran Terima (Terima Kasih)</td>
							<td><b>:</b></td>
							<td class="text-right " style="text-align:right;">(<?php echo $b_val['payment_received']; ?>)</td>
							<!-- <td class="text-center "></td> -->
						</tr>
				</table>
				<table class="item_table" border="0" style="width:100%;font-size:13px;">
						<tr>
							<td colspan="4"><h6 style="font-family:sans-serif"><b>CAJ SEMASA</b></h6></td>
						</tr>
						<?php foreach ($d_val['bill_detail'] as $b2_key => $b2_val) { ?>							
						<tr>
							<td style="width:60%;border:0px;" class="text-left "><?php echo $b2_val['bill_type_name']; ?> <?php if (!empty($b2_val['remark'])) { ?>(<?php echo $b2_val['remark']; ?>)<?php } ?></td>
							<td style="width:5%;border:0px;padding-left:0px;"><b>:</b></td>
							<td class="text-right" style="text-align:right;width:20%;border:0px;padding-right:0px;"><?php echo $b2_val['item_amount']; ?></td>
							<td style="border:0px;">&nbsp;</td>
						</tr>
						<?php } ?>
				</table>
				<table class="account-summary" border="0" style="width:100%;font-size:13px;">
						<tr>
							<td style="width:60%;"></td>
							<td style="width:5%;"></td>
							<td colspan="2" style="width:20%;margin:0;padding:0;line-height:1px;background-color:grey">&nbsp;</td>
						</tr>									
						<tr>
							<td style="width:60%;" class="text-left" >Amaun</td>
							<td style="width:5%;"><b>:</b></td>
							<td class="text-right" style="text-align:right;width:20%;"><?php echo $b_val['charges']; ?></td>
							<td class="text-center"></td>
						</tr>						
						<tr>
							<td class="text-left">Cukai <?php echo $c_val['default_tax']; ?>%</td>
							<td><b>:</b></td>
							<td class="text-right" style="text-align:right;"><?php echo $b_val['tax_charges']; ?></td>
							<td class="text-center "></td>
						</tr>	
						<tr>
							<td colspan="2"></td>
							<td colspan="2" style="margin:0;padding:0;line-height:1px;background-color:grey">&nbsp;</td>
						</tr>	
											
						<tr>
							<td class="text-left"><b>JUMLAH CAJ SEMASA</b></td>
							<td></td>
							<td class="text-right" style="text-align:right;"><b><?php echo $b_val['amount']; ?></b></td>
							<td class="text-center "></td>
						</tr>	
				</table>
				
					<div class="col-lg-12 account-summary">&nbsp;</div>
					<div class="col-lg-12 balance_due well account-summary"  style="font-size:15px;">
						<table style="width:100%" border="0">
							<tr class="balance_due well" style="border:none;">
								<td width="60%" class="text-left "><b>Imbangan</b></td>
								<td width="5%"><b>:</b></td>
								<td width="35%" class="text-right" style="text-align:center;"><?php echo $b_val['balance']; ?></td>
							</tr>
						</table>
					</div>
				</div>
			
				
				
				<div class="content_footer print-footer" style="position:relative;padding-top:5px;width:100%;">
					<div class="col-lg-12 content_single_line">&nbsp;</div><?php //single line -------------------------------- ?>
					<div class="col-lg-12 text-left" style="line-height:1.15em;font-size:11px;text-align:left;">
					<?php echo $bills_footer; ?>
					</div>
					<div class="col-lg-12">&nbsp;</div>
					<!--<div class="text-center" style="font-weight:bold" >SERVICE PROVIDED BY <?php echo $company_full_name; ?></div>-->
					<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
				</div>
			</div>
		</div>	

		<!-- if there are phone charges details to be shown, create another page, with same header/footer, and put the details in new page -->
		<!-- if else statement to check if array obj is empty or not, if not empty only generate this page-->

		<?php } ?>
	<?php } ?>
<?php } ?>

<?php if (!empty($d_val['bill_call'])) { ?>

<div class="page bill_call" data-page="<?php echo $b_val['customer_no']; ?>" >
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
									<span style="font-weight:bold;"><?php echo $company_full_name; ?> (<?php echo $company_brn; ?>)</span><br>
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
									<?php if (!empty($company_phone)) { echo "Tel:".$company_phone."&nbsp;&nbsp;"; } ?><?php if (!empty($company_fax)) { echo "Fax:".$company_fax."&nbsp;&nbsp;"; } ?><?php if (!empty($company_email)) { echo "Email:".$company_email."&nbsp;&nbsp;"; } ?>
									<?php } ?>
								</div>
							</td>
						</tr>
	
					</table>
				</div>

				<div class="col-lg-12 content_double_line"></div>				
				<div class="col-lg-12">&nbsp;</div>
					<table border="0" width="98%" class="center" style="">
						<tr>
							<td width="45%" style="vertical-align:top">		
								<table border="0">
									<tr style="vertical-align:top">
										<td colspan='3' style="font-size:18px;"><?php echo strtoupper(malay_lang(strtolower($b_val['doc_title']))); ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_name']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_unit_no']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_addr1']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_addr2']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_addr3']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_city']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $c_val['display_postcode']; ?> <?php echo $c_val['display_state']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td width="35%">No. Akaun</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="60%"><b><?php echo $c_val['customer_no']; ?></b></td>
									</tr>
								</table>
							</td>
							<td width="55%" style="vertical-align:top">
								<table border="0">
									
									<?php if (!empty($company_tax_number)) { ?>
									<tr style="vertical-align:top">
										<td width="35%">No. SST</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="60%"><?php echo $company_tax_number; ?></td>
									</tr>
									<?php } ?>
									
									<tr style="vertical-align:top">
										<td width="35%">Pakej</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="60%"><?php echo $c_val['package_name']; ?></td>
									</tr>								
									<tr>
										<td>No. Bil</td>
										<td>&nbsp;:&nbsp;</td>
										<td><?php echo $b_val['bill_no']; ?></td>
									</tr>		
									<tr>
										<td>Tarikh Bil</td>
										<td>&nbsp;:&nbsp;</td>
										<td><?php echo $b_val['bill_date']; ?></td>
									</tr>
									<tr>
										<td>Tarikh Akhir Bayaran</td>
										<td>&nbsp;:&nbsp;</td>
										<td><b><?php echo $c_val['pay_on_or_before']; ?></b></td>
									</tr>
									<tr style="vertical-align:top">
										<td>Deposit</td>
										<td>&nbsp;:&nbsp;</td>
										<td><b><?php echo $c_val['currency_code']; ?> <?php echo $c_val['deposit']; ?></b></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12 content_single_line"></div>

				<div class="col-lg-12 content_body">
				<table class="summary-header" border="0" style="width:100%;font-size:13px;">
						<tr>
							<td class="to_underline" width="60%"><h5 style="font-family:sans-serif"><b>Caj Panggilan Telefon</b></h5></td>
							<td width="5%"></td>
							<td class="text-right to_underline" style="text-align:right;"  width="20%"><h5 style="font-family:sans-serif"><b>Amaun (<?php echo $c_val['currency_code']; ?>)</b></h5></td>
						</tr>
				</table>
				<table class="item_table" border="0" style="width:100%;font-size:13px;">
						<tr>
							<td colspan="6"><h6 style="font-family:sans-serif"><b>CAJ SEMASA</b></h6></td>
						</tr>
						<?php $sum_phone_charges = 0; ?>
						<?php foreach ($d_val['bill_call'] as $a_key => $a_val) { ?>							
						<tr>
							<td style="width:20%;border:0px;" class="text-left "><?php echo $a_val['start_time']; ?></td>
							<td style="width:20%;border:0px;" class="text-left "><b><?php echo $a_val['called']; ?></b></td>
							<td style="width:20%;border:0px;" class="text-left "><?php echo gmdate("H:i:s", $a_val['talk_time']); ?></td>
							<td style="width:5%;border:0px;padding-left:0px;"><b>:</b></td>
							<td class="text-right" style="text-align:right;width:20%;border:0px;padding-right:0px;"><?php echo $a_val['total']; ?></td>
							<td style="border:0px;">&nbsp;</td>
						</tr>
						<?php $sum_phone_charges = $sum_phone_charges + $a_val['total']; ?>
						<?php } ?>
				</table>

				<div class="col-lg-12 account-summary">&nbsp;</div>
				<div class="col-lg-12 balance_due well account-summary"  style="font-size:15px;">
					<table style="width:100%" border="0">
						<tr class="balance_due well" style="border:none;">
							<td width="60%" class="text-left "><b>Jumlah</b></td>
							<td width="5%"><b>:</b></td>
							<td width="35%" class="text-right" style="text-align:center;"><?php echo number_format($sum_phone_charges, 2, ".", ","); ?></td>
						</tr>
					</table>
				</div>

				</div>

				<div class="content_footer print-footer" style="position:relative;padding-top:5px;width:100%;">
					<div class="col-lg-12 content_single_line">&nbsp;</div><?php //single line -------------------------------- ?>
					<div class="col-lg-12 text-left" style="line-height:1.15em;font-size:11px;text-align:left;">
					<?php echo $bills_footer; ?>
					</div>
					<div class="col-lg-12">&nbsp;</div>
					<!--<div class="text-center" style="font-weight:bold" >SERVICE PROVIDED BY <?php echo $company_full_name; ?></div>-->
					<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
				</div>

	</div>
</div>

<?php } ?>

<?php endif; ?>	
</div>


<script>
	
$( document ).ready( function(){

	var max_doc = $('.page').not('.bill_call').length ;

	for( let x = 0 ; x < max_doc ; x++ ){
	  let this_doc_no = $('.page').eq( x ).attr('data-page'); ;
	  sort_content( this_doc_no , 0 );
	  sort_footer( this_doc_no );
	}

	var max_call_doc = $('.bill_call').length ;

	for( let y = 0 ; y < max_call_doc ; y++ ){
	  let this_doc_no = $('.bill_call').eq( y ).attr('data-page'); ;

	  //temporary clone the function
	  sort_call_content( this_doc_no, 0 );
	  sort_call_footer( this_doc_no );
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
		//var doc_type = $('.page[data-page="'+doc_no+'"]:eq('+page_no+')').attr('data-doc-type');
		//edited to 450 for new layout, original 500
		var printable_height = 450;
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
		//$('.page[data-page="'+doc_no+'"]:not(":first")').find('.account-summary').remove();
		//$('.page[data-page="'+doc_no+'"]:not(":last")').find('.print-footer').remove();

		//edited by val 2025-08-21
		$('.page[data-page="'+doc_no+'"]:not(".bill_call"):not(":first")').find('.summary-header').remove();
		$('.page[data-page="'+doc_no+'"]:not(".bill_call"):not(":last")').find('.account-summary').remove();
	}

	function sort_call_content( doc_no, page_no ){

		var next_page_no = page_no ;
		var ttl_height = 0 ;
		//var doc_type = $('.bill_call[data-page="'+doc_no+'"]:eq('+page_no+')').attr('data-doc-type');
		//edited to 450 for new layout, original 500
		var printable_height = 450;
		console.log( "Call Document # " + doc_no );
		console.log( "Call Page # " + ( page_no + 1 ) );
		// console.log( "Doc Type => " + doc_type );
		
		//var tbl_row = document.getElementsByClassName('page')[page_no].getElementsByClassName('item_table')[0].rows.length - 1 ;
		var tbl_row = document.querySelectorAll(".bill_call[data-page='"+doc_no+"']")[page_no].getElementsByClassName('item_table')[0].rows.length - 1 ;
		for( var y = 0 ; y <= tbl_row ; y++ ){
		  
		  //ttl_height = ttl_height + document.getElementsByClassName('page')[page_no].getElementsByClassName('item_table')[0].rows[y].offsetHeight;
		  ttl_height = ttl_height + document.querySelectorAll(".bill_call[data-page='"+doc_no+"']")[page_no].getElementsByClassName('item_table')[0].rows[y].offsetHeight;
		  
		  //console.log("Accumulating Height => " + ttl_height);

		  if( ttl_height > printable_height ){ //only 520 for item listing

			//console.log("Accumulating Height OVER, moving Row # " + y + ' and onwards to next page');
			
			//clone this page and insert AFTER SELF, and remove its item listing
			var clone_page = $('.bill_call[data-page="'+doc_no+'"]').eq(page_no).clone();
			clone_page.insertAfter('.bill_call[data-page="'+doc_no+'"]:eq('+page_no+')').find('.item_table > tbody >tr').remove();

			//get Y-th and onwards row and move it to NEXT page
			for( var x = y ; x <= tbl_row ; x++ ){
			  $('.bill_call[data-page="'+doc_no+'"]:eq('+page_no+') .item_table:eq(0)').find('tbody >tr:eq('+y+')').appendTo('.bill_call[data-page="'+doc_no+'"]:eq('+(page_no+1)+') .item_table:eq(0) > tbody');
			}
			
			//next_page_no++ , prepare to run this sort_content function again
			next_page_no = parseInt( page_no ) + 1 ;
			break;

		  }

		}
		
		if( next_page_no > page_no ){
		  console.log('Sorting next page : ' + next_page_no );
		  sort_call_content( doc_no, next_page_no );
		}else{
		  console.log('End sorting Doc # ' + doc_no );
		}

	}

	function sort_call_footer( doc_no ){

		//edited by val 2025-08-21
		$('.bill_call[data-page="'+doc_no+'"]:not(":first")').find('.summary-header').remove();
		$('.bill_call[data-page="'+doc_no+'"]:not(":last")').find('.account-summary').remove();
	}

});

</script>
