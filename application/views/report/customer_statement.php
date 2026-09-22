<div class="book" >	
		<?php
			$subtotal = 0;
			for ($p = 1; $p <= $pages; $p++) { 
		?>
		<div class="page"> 
			<div class="subpage">
				<div class='text-center'>
					<?php
					$company_logo = $this->config->item('logo_img'); 
					?>
					<?php if (!empty($company_logo)) { ?>
					<img style="width:150px;" src="<?php echo $company_logo; ?>" >
					<?php } else { ?>
					<img src="<?php echo base_url("/images/telco-icon.png"); ?>" >ITELCO
					<?php } ?>
				</div>	
				<div class="col-lg-12 content_double_line"></div>				
				<div class="col-lg-12">&nbsp;</div>
					<table border="0" width="98%" class="center" style="">
						<tr>
							<td width="100%" style="vertical-align:top">		
								<table border="0">
									<tr style="vertical-align:top">
										<td colspan='3' style="font-size:18px;">Statement of Account</td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'>{display_name}</td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'>{display_addr1}</td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'>{display_addr2}</td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'>{display_city}</td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'>{display_postcode} {display_state}</td>
									</tr>
									<tr style="vertical-align:top">
										<td width="30%">Subscriber No</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="65%"><b>{customer_no}</b></td>
									</tr>	
								</table>
							</td>
							<!--
							<td width="55%" style="vertical-align:top">
								<table border="0">
									<tr style="vertical-align:top">
										<td width="30%">GST Reg. No</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="65%">{gst_reg_no}</td>
									</tr>
									<tr style="vertical-align:top">
										<td width="30%">Package</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="65%">{package_name}</td>
									</tr>								
									<tr>
										<td>Bill No</td>
										<td>&nbsp;:&nbsp;</td>
										<td>{bill_no}</td>
									</tr>		
									<tr>
										<td>Bill Date</td>
										<td>&nbsp;:&nbsp;</td>
										<td>{bill_date}</td>
									</tr>
									<tr>
										<td>Pay On Or Before</td>
										<td>&nbsp;&nbsp;</td>
										<td><b>{pay_on_or_before}</b></td>			
									</tr>
									<tr>
										<td>User Name</td>
										<td>&nbsp;:&nbsp;</td>
										<td>{login_username}</td>
									</tr>
								</table>
							</td>
							-->
						</tr>
					</table>
					
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12 content_single_line"></div>
								
				<div class="col-lg-12">Details {date_from} - {date_to}</div>
				<div class="col-lg-12 content_single_line"></div>
								
				<div class="col-lg-12 content_body">
				<table border="0" style="width:100%;font-size:13px;">
					<tr>
						<th style='text-align:left;'>Date</th>
						<th style='text-align:left;'>Bills Number</th>
						<th style='text-align:left;'>Description</th>
						<th style='text-align:right;'>Amount</th>
						<th style='text-align:right;'>Payment</th>
						<th style='text-align:right;'>Remaining</th>
					</tr>
					
					<?php 
					if(( $opening_balance != 0 ) && ($p == 1)){ 
						$subtotal = $opening_balance ; 
					?>
					<tr>
						<td></td>
						<td></td>
						<td style='text-align:left;'>Bring Forward</td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'></td>
						<td style='text-align:right;'><?php echo number_format( $opening_balance , 2 , "." , "," ); ?></td>
					</tr>
					<?php } else { ?>
					<?php } ?>
					
					
					<?php 
						if( !empty( $trans_details[$p] ) ){
						foreach( $trans_details[$p] AS $dtl ){ 
							$subtotal = $subtotal + $dtl['tr_charges'] - $dtl['tr_pay_amount'];
							if( $dtl['tr_remark'] == 'Monthly Charges' && $dtl['tr_charges'] < 0 )
								$dtl['tr_remark'] = 'Credit Note';
					?>
					<tr>
						<td style='text-align:left;'><?php echo $dtl['tr_date']; ?></td>
						<td style='text-align:left;'><?php echo $dtl['tr_number']; ?></td>
						<td style='text-align:left;'><?php echo $dtl['tr_remark']; ?></td>
						<td style='text-align:right;'><?php echo $dtl['tr_charges']==0?'':number_format($dtl['tr_charges'],2); ?></td>
						<td style='text-align:right;'><?php echo $dtl['tr_pay_amount']==0?'':number_format($dtl['tr_pay_amount'],2); ?></td>
						<td style='text-align:right;'><?php echo number_format( $subtotal, 2 , "." , "," ); ?></td>
					</tr>
					<?php } }?>
					
				</table>
				</div>
				
				<?php if ($p == $pages) { ?>
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12 content_body">
					<div class="col-lg-12 balance_due well"  style="font-size:15px;">
						<table style="width:100%" border="0">
							<tr class="balance_due well" style="border:none;">
								<td width="30%" class="text-left " style="text-align:left;"><b>Balance Due</b></td>
								<td width="5%"><b>:</b></td>
								<td width="50%" class="text-right " style="text-align:right;"><?php echo number_format($subtotal,2); ?></td>
								<td width="15%" class="text-right "></td>
							</tr>
						</table>
					</div>
				</div>

				<?php if (!$footer_push) { ?>
			
				<div class="content_footer" style="position:relative;padding-top:5px;width:100%;">
					<div class="col-lg-12 content_single_line">&nbsp;</div><?php //single line -------------------------------- ?>
					<div class="col-lg-12 text-left" style="line-height:1.15em;font-size:11px;text-align:left;">
					{statement_footer}
					</div>
					<div class="col-lg-12">&nbsp;</div>
					<div class="text-center" style="font-weight:bold;text-align:center;" >SERVICE PROVIDED BY {company_full_name}</div>
					<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
				</div>

				<?php } ?>

				<?php } ?>

			</div>
		</div>
		<?php } ?>
		<?php if ($footer_push) { ?>

			<div class="page" data-page="{data}" > 
				<div class="subpage">

					<div class="content_footer" style="position:relative;padding-top:5px;width:100%;">
						<div class="col-lg-12 content_single_line">&nbsp;</div><?php //single line -------------------------------- ?>
						<div class="col-lg-12 text-left" style="line-height:1.15em;font-size:11px;text-align:left;">
							{statement_footer}
						</div>
						<div class="col-lg-12">&nbsp;</div>
						<div class="text-center" style="font-weight:bold;text-align:center;" >SERVICE PROVIDED BY {company_full_name}</div>
						<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
					</div>

				</div>
			</div>

		<?php } ?>
</div>
