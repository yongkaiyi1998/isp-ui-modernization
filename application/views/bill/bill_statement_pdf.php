<div class="book" >	
<?php if(!empty($data)):?>	
{data}		
	{customer}
		{bill}
		<div class="page" data-page="{data}" > 
			<div class="subpage">
				
				<div class="col-lg-12 text-left" style="font-size: 10px !important;">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td rowspan="2" style="width:260px;">
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
								<img src="<?php echo $logo_url; ?>" ><?php echo $proj_name; ?>
								<?php } ?>
							</td>
							<td style="width:60px;"></td>
							<td style="width:60px;padding-top:32px;padding-right:3px;vertical-align:top;text-align:left;">
								<!-- <img width="62px" height="62px" src='<?php echo base_url("images/jompay_logo.jpg");?>' /> -->
							</td>
							<td style="width:320px;text-align:left;padding-top:32px;">
								<table style="float:right;border-style:solid;border-width:1px;width:300px;font-size:10px ! important;line-height: 10.5px ! important; height: 45px ! important;margin-top:3px;margin-right:63px;" cellspacing=5>
									<tr><td style="text-align:left;padding-left:3px;"><b>Biller Code</b>: 71548 </td></tr>
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-1</b>: {customer_no}</td></tr>
									<tr><td style="text-align:left;padding-left:3px;"><b>Ref-2</b>: {display_name}</td></tr>
								</table>
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
				
				<!--
				<div class="col-lg-5" style="margin-top:0px;padding-left:5px;font-size:10px;">
					<table style="float:left;margin-top:8px;padding-right:5px;">
						<tr>
							<td><img width="51px" height="51px" src='<?php echo base_url("images/jompay_logo.jpg");?>' /></td>
						</tr>					
					</table>
					<table style="float:left;margin-top:11px;font-family:helvetica;border-style:solid;border-width:1px;width:230px;" cellspacing=5>
						<tr><td style="text-align:left;padding-left:3px;"><b>Biller Code</b>: 71548 </td></tr>
						<tr><td style="text-align:left;padding-left:3px;"><b>Ref-1</b>: {customer_no}</td></tr>
						<tr><td style="text-align:left;padding-left:3px;"><b>Ref-2</b>: {display_name}</td></tr>
						
					</table>
					<table style="float:left;padding-left:3px;">
						<tr><td><b>JomPAY</b> online at Internet and Mobile Banking with your Current or Savings account</td></tr>
					</table>
				</div>
				-->
				
				
				
				<div class="col-lg-12 content_double_line"></div>				
				<div class="col-lg-12">&nbsp;</div>
					<table border="0" width="98%" class="center" style="">
						<tr>
							<td width="45%" style="vertical-align:top">		
								<table border="0">
									<tr style="vertical-align:top">
										<td colspan='3' style="font-size:18px;">{doc_title}</td>
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
							<td width="55%" style="vertical-align:top">
								<table border="0">
									
									<tr style="vertical-align:top">
										<td width="30%">SST Reg. No</td>
										<td width="5%">&nbsp;:&nbsp;</td>
										<td width="65%">P11-1808-31034792</td>
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
									<!--tr>
										<td>Bill Period</td>
										<td>&nbsp;:&nbsp;</td>
										<td>{bill_period}</td>
									</tr-->
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
									<tr style="vertical-align:top">
										<td>Deposit</td>
										<td>&nbsp;:&nbsp;</td>
										<td><b>RM {deposit}</b></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12 content_single_line"></div>
								
				<div class="col-lg-12 content_body">
				<table border="0" style="width:100%;font-size:13px;">
						<tr>
							<td class="to_underline" width="60%"><h5 style="font-family:sans-serif"><b>Account Summary</b></h5></td>
							<td width="5%"></td>
							<td class="text-right to_underline" width="20%"><h5 style="font-family:sans-serif"><b>Total (RM)</b></h5></td>
							<!--
							<td class="text-right to_underline" width="15%"><h5 style="font-family:sans-serif"><b>Tax Code</b></h5></td>
							-->
						</tr>
						<tr>
							<td class="text-left ">Previous Balance</td>
							<td><b>:</b></td>
							<td class="text-right ">{previous_balance}</td>
							<td class="text-center "></td>
						</tr>
						<tr>
							<td class="text-left ">Payment Received</td>
							<td><b>:</b></td>
							<td class="text-right ">({payment_received})</td>
							<!-- <td class="text-center "></td> -->
						</tr>
						<tr>
							<td colspan="4"><h6 style="font-family:sans-serif"><b>CURRENT MONTH CHARGES</b></h6></td>
						</tr>
						{bill_detail}							
						<tr>
							<td class="text-left ">{bill_type_name} ({item_remark})</td>
							<td><b>:</b></td>
							<td class="text-right ">{item_amount}</td>
							<!-- <td class="text-center ">{tax_code}</td> -->
						</tr>
						{/bill_detail}		
						<tr>
							<td colspan="2"></td>
							<td colspan="2" style="margin:0;padding:0;line-height:1px;background-color:grey">&nbsp;</td>
						</tr>									
						<tr>
							<td class="text-left" >Total</td>
							<td><b>:</b></td>
							<td class="text-right ">{charges}</td>
							<td class="text-center "></td>
						</tr>						
						<tr>
							<td class="text-left">Service Tax {default_tax}%</td>
							<td><b>:</b></td>
							<td class="text-right ">{tax_charges}</td>
							<td class="text-center "></td>
						</tr>	
						<tr>
							<td colspan="2"></td>
							<td colspan="2" style="margin:0;padding:0;line-height:1px;background-color:grey">&nbsp;</td>
						</tr>	
											
						<tr>
							<td class="text-left"><b>Current Month Total</b></td>
							<td></td>
							<td class="text-right "><b>{amount}</b></td>
							<td class="text-center "></td>
						</tr>	
				</table>
				
					<div class="col-lg-12">&nbsp;</div>
					<div class="col-lg-12 balance_due well"  style="font-size:15px;">
						<table style="width:100%" border="0">
							<tr class="balance_due well" style="border:none;">
								<td width="30%" class="text-left "><b>Balance Due</b></td>
								<td width="5%"><b>:</b></td>
								<td width="50%" class="text-right ">{balance}</td>
								<td width="15%" class="text-right "></td>
							</tr>
						</table>
					</div>
				</div>
			
				
				
				<div class="content_footer" style="position:relative;padding-top:5px;width:100%;">
					<div class="col-lg-12 content_single_line">&nbsp;</div><?php //single line -------------------------------- ?>
					<div class="col-lg-12 text-left" style="line-height:1.15em;font-size:11px">
						All cheques should be crossed and made payable to MY PENANG FON SDN BHD <br>
						and mail to 368-4-10 Bellisa Row, Jalan Burmah, Pulau Tikus, 10350 Penang <br>
						<br>
						Please indicate your name and subscriber number on the reverse side of the cheque.<br>
						<br>
						Payment can also be made over the counter at Pulau Tikus Office. <br>
						or via Maybank Internet Banking at http://www.maybank2u.com.my (My Penang Fon Sdn Bhd A/C)<br>
						PLEASE DO NOT MAKE PAYMENT TO "METROFON / PENANGFON"<br>
						<br>
						For direct bank-in to MY PENANG FON SDN BHD account<br>
						MBB Account No: 5070-4002-7508 or CIMB Account No: 800-394-7045<br>
						Please print the Payment Receipt and email to us for update in our system.<br>
						Email to: payment.penangfon@gmail.com<br>
						<br>
						Without prejudice to any other right or remedy that OCESB have against the customer for non-payment of the service. OCESB reserves the right to suspend the service if payment is not received by the stipulated bill date. A reconnection fee is applicable for the service to be restored.<br>
						Residential Reconnection RM10.00.<br>
						<br>
						For bill enquiries, email to payment.penangfon@gmail.com<br>	
						<br>						
						For internet downtime, please call our call centre at 604-683 8888 or toll free 1-700-81-8862
						<br>
						or Email to: support@penangfon.net  /  customerservice.penangfon@gmail.com
						<br>
					</div>
					<div class="col-lg-12">&nbsp;</div>
					<div class="text-center" style="font-weight:bold" >SERVICE PROVIDED BY OPTICAL COMMUNICATION ENGINEERING SDN.BHD.</div>
					<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
				</div>
			</div>
		</div>		
		{/bill}
	{/customer}	
{/data}

<?php endif; ?>	
</div>
