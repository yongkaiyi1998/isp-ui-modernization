<!--<script src="<?php echo base_url('js/jquery.min.js'); ?>" ></script>-->

<div class="book" >	
		<div class="page" data-page="{customer_no}" > 
			<div class="subpage">

				<div class="col-lg-12 text-left" style="font-size: 10px !important;">
					<table cellpadding="0" cellspacing="0" style="width:100%;">
						<tr>
							<td rowspan="2" style="260px;">
								<?php
								$company_logo = $this->config->item('logo_img'); 
								$proj_name = $this->config->item('proj_name');

								if (empty($proj_name)) {
									$proj_name = 'Itelco';
								}
								?>
								<?php if (!empty($company_logo)) { ?>
								<img style="width:150px;" src="<?php echo $company_logo; ?>" >
								<?php } else { ?>
								<img src="<?php echo base_url("/images/telco-icon.png"); ?>" ><?php echo $proj_name; ?>
								<?php } ?>
							</td>
							<td style="width:60px;"></td>
							<td style="width:60px;padding-top:8px;padding-right:3px;vertical-align:top;text-align:left;height:55px;">
							</td>
							<td style="width:380px;text-align:left;padding-top:8px;height:55px;">
								<table style="float:right;border-style:solid;border-width:1px;width:380px;font-size:10px ! important;line-height: 10.5px ! important; height: 45px ! important;margin-top:0px;margin-right:63px;" cellspacing=5>
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-1</b>: {display_name}</td></tr>
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
											<tr><td colspan="6"><?php echo $data['so_head']['cust_name']; ?></td></tr>
											<!-- <tr><td colspan="6">{display_name} {reg_no}</td></tr> -->
											<?php if (!empty($data['so_head']['bill_unit_no'])) { ?>
											<tr><td colspan="6"><?php echo $data['so_head']['bill_unit_no']; ?></td></tr>
											<?php } ?>
											<tr><td colspan="6"><?php echo $data['so_head']['bill_addr_1']; ?></td></tr>
											<tr><td colspan="6"><?php echo $data['so_head']['bill_addr_2']; ?></td></tr>
											<tr><td colspan="6"><?php echo $data['so_head']['bill_addr_3']; ?></td></tr>
											<tr><td colspan="6"><?php echo $data['so_head']['bill_city']; ?></td></tr>
											<tr><td colspan="6"><?php echo $data['so_head']['bill_postcode']; ?> <?php echo $bill_actual_state; ?></td></tr>
											<tr>
												<td class="text-left" style="width:0.2em;">Tel</td>
												<td class="text-left" style="width:0.2em;">:&nbsp;</td>
												<td class="text-left" style="width:14em;"><?php echo $data['so_head']['bill_tel']; ?></td>
												<td class="text-left" style="width:0.2em;">Email</td>
												<td class="text-left" style="width:0.2em;">:&nbsp;</td>
												<td class="text-left" style="width:14em;"> <?php echo $data['so_head']['bill_email']; ?></td>
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
												<td class="col-lg-6 text-left"><?php echo $data['so_head']['gst_num']; ?></td>
											</tr>
											<tr class="tax-toggle" style="">
												<td class="text-left">Quotation No</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left">{quotation_no}</td>
											</tr>
											<tr>
												<td class="text-left">Date</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left"><?php echo $data['so_head']['date']; ?></td>
											</tr>
											<tr>
												<td class="text-left">Payment Term</td>
												<td class="col-lg-1 text-right">:&nbsp;</td>
												<td class="col-lg-6 text-left"><?php echo $data['so_head']['payment_term']; ?> days</td>
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
								<th class="text-center">Amount<br>( <?php echo $data['so_head']['currency_code']; ?> )</th>
								<th class="text-center">Tax Code</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style='text-align:right;padding:0px 5px 0px 5px;'><?php echo $data['item_list']['line_num'] ?? 1; ?></td>
								<td style='text-align:left;padding:0px 5px 0px 5px;'>Subscription Package: <?php echo $data['item_list']['desc'] ?? ''; ?></td>
								<td style='text-align:right;padding:0px 5px 0px 5px;'><?php echo $data['item_list']['price'] ?? 0.00; ?></td>
								<td style='text-align:center;padding:0px 5px 0px 5px;' ><?php echo $data['item_list']['tax'] ?? 0.00; ?></td>
							</tr>
						</tbody>
					</table>
					
					<div class="col-lg-12">&nbsp;</div>
					<span class="row invoice_detail">
						<table class="table borderless table-condensed center" style="width:98%; margin-top:-1em;">
							<tbody>
								<tr>
									<td class="col-lg-6 text-right" rowspan="3">
										{einvoice_qr}
									</td>
									<td class="col-lg-4" style="text-align:right">Subtotal :</td>
									<td class="col-lg-2" style="text-align:right"><?php echo $data['so_head']['base_amount']; ?></td>
								</tr>
								<tr class="tax-toggle" style="">
									<td style="text-align:right">Service Tax {default_tax}%</td>
									<td style="text-align:right"><?php echo $data['so_head']['grand_tax']; ?></td>
								</tr>
								<tr>
									<td style="text-align:right"><b>Total Amount ( <?php echo $data['so_head']['currency_code']; ?> ):</b></td>
									<td style="text-align:right">				
										<div class="single_underline">&nbsp;</div>
										<span><?php echo $data['so_head']['grand_total']; ?></span> 
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
</div>
