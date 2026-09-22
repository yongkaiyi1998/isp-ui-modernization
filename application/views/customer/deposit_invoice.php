<div class="book" >	
	{data}
		<div class="page"> 
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
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-1</b>: {customer_no}</td></tr>
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-2</b>: {display_name}</td></tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="width:60px;"></td>
							<td colspan="2" style="vertical-align:top;padding-left:2px;">
							</td>
						</tr>
					</table>
				</div>
					
				<div class="content_body" style="font-size: 12px;">
					<div class="col-lg-12 content_double_line"></div>
					<div class="col-lg-12">&nbsp;</div><div class="col-lg-12">&nbsp;</div>
					<h2 class="text-center" style="text-align:center;font-family:sans-serif">
						INVOICE
					</h2>
					<div class="col-lg-12">&nbsp;</div>
					
					<table border="0" class="col-lg-12" >
						<tbody>
							<tr>
								<td width="60%" style="vertical-align:top">									
									<table border="0"  >
										<tbody>
											<tr><td colspan="6">{display_name}</td></tr>
											<?php if (!empty($data[0]['display_unit_no'])) { ?>
											<tr><td colspan="6"><?php echo $data[0]['display_unit_no']; ?></td></tr>
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
											<tr>
												<td class="text-left">SST Reg. No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{gst_reg_no}</td>
											</tr>
											<tr class="tax-toggle" style="">
												<td class="text-left">Deposit No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_no}</td>
											</tr>
											<tr>
												<td class="text-left">Deposit Date</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{bill_date}</td>
											</tr>
											<tr>
												<td class="text-left">Payment Term</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{payment_term}</td>
											</tr>
											<tr>
												<td class="text-left">Subscriber No:</td>
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
								<th class="col-lg-1 text-center">Item</th>
								<th class="col-lg-7 text-center">Description</th>
								<th class="col-lg-2 text-center">Amount<br>( {currency_code} )</th>
								<th class="col-lg-2 text-center">Tax Code</th>
							</tr>
						</thead>
						<tbody>
							{bill_detail}
							<tr>
								<td style='text-align:right;padding:0px 5px 0px 5px;'>{item_count}</td>
								<td style='text-align:left;padding:0px 5px 0px 5px;'>{bill_type_name} - {remark} </td>
								<td style='text-align:right;padding:0px 5px 0px 5px;'>{item_amount}&nbsp;</td>
								<td style='text-align:center;padding:0px 5px 0px 5px;'>SR</td>
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
									<td class="col-lg-6 text-right"></td>
									<td class="col-lg-4 text-right">Subtotal :</td>
									<td class="col-lg-2 text-right">{charge}</td>
								</tr>
								<tr class="tax-toggle" style="">
									<td class="text-right"></td>
									<td style="text-align:right">Service Tax {default_tax}%</td>
									<td class="text-right">{tax_charge}</td>
								</tr>
								<tr>
									<td class="col-lg-6 text-right"></td>
									<td style="text-align:right"><b>Total Amount ( {currency_code} ):</b></td>
									<td class="text-right">				
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
			</div>
		</div>
	{/data}
</div>
