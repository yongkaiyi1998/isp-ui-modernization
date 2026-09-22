<div class="book">
	{data}
	<div class="page" style="min-height:148mm !important;width:206mm;"> 
		<div class="subpage" style="height:138mm !important;">
			<!--
			<div class='text-center'><img src='<?php echo base_url("images/logo_letterhead.jpg");?>' /></div>
			<h2 class="text-center" style="font-family:sans-serif">&nbsp;Official Receipt</h2>
			-->
			<div class="content_single_line"></div>
			<table class="table table-condensed borderless" style="width:100%;">
				<tbody>
					<tr>
						<td rowspan="2" class="col-lg-8" style="width:65%;padding-left:15px;padding-top:15px;">
							<?php
							$company_logo = $this->config->item('logo_img'); 
							?>
							<?php if (!empty($company_logo)) { ?>
							<img style="width:150px;" src="<?php echo $company_logo; ?>" >
							<?php } else { ?>
							<img src="<?php echo base_url("/images/telco-icon.png"); ?>" >ITELCO
							<?php } ?>
						</td>
						
						<td class="text-right col-lg-2" style="text-align:right;width:20%;vertical-align:bottom;" >Official Receipt No :</td>
						<td class="text-left col-lg-2"  style="width:15%;vertical-align:bottom;" >{payment_no}</td>
					</tr>
					<tr>
						
						<td class="text-right col-lg-2" style="text-align:right;width:20%;">Date :</td>
						<td class="text-left col-lg-2" style="width:15%;">
							{pay_date}
							<input type="hidden" id="pay_date" value="{pay_date}">
						</td>
					</tr>
				</tbody>
			</table>
			<div class="content_single_line"></div>
			<table class="table borderless table-condensed" style="width:98%; margin-top:1em;height:145px;">
				<tbody>
					<tr style="height:35px;">
						<td class="col-lg-3 text-right" style="width:25%;text-align:right;">Received From :</td>				
						<td class="col-lg-9 text-left" style="width:75%;">									
							<div style="line-height:1.5em;color:black"><span class="customer_no">{customer_no} {customer_name}</span></div>
						</td>
					</tr>
					<tr style="height:35px;">
						<td class="text-right" style="text-align:right;">Package Name :</td>
						<td class="text-left">									
							<div style="line-height:1.5em;color:black"><span class="package_name">{package_name}</span></div>
							
						</td>
					</tr>
					<!--
					<tr>
						<td class="text-right">Received from :</td>
						<td class="text-left">									
							<div style="line-height:1.5em;color:black"><span class="customer_name">{customer_name}</span></div>
							<div class="text-center" style="border-top:0.12em solid rgba(0,0,0,0.5); "></div>
						</td>
					</tr>
					-->
					<tr style="height:35px;">
						<td class="text-right" style="text-align:right;">The Sum Of {currency_code} :</td>
						<td class="text-left">									
							<div style="line-height:1.5em;color:black"><span style="text-transform: capitalize;"> {amount_in_words} &nbsp;</span></div> 
							<div class="text-center" style="border-top:0.12em solid rgba(0,0,0,0.5); "></div>
						</td>
					</tr>
					<tr style="height:35px;">
						<td class="text-right" style="text-align:right;">being payment for :</td>
						<td class="text-left">										
							<div style="line-height:1.5em;color:black">								
							<span class="package_name">{bill_type_name}</span>
							<input type="hidden" id="remark" value="{remark}" />
							</div>
							<div class="text-center" style="border-top:0.12em solid rgba(0,0,0,0.5); "></div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="content_footer">					
				<div class="content_single_line"></div>
				<div>&nbsp;</div>
		
				<table class="center" style="width:98%;height:140px;">
					<tbody>
						<tr>
							<td style="width:50%;text-align:left;border: 1px solid rgba(0,0,0,0.2); padding:5px;margin:0px;">
								<div>&nbsp;</div>
								<div class="text-center" style="height:70px;text-align:center;color:rgba(0,0,0,1); font-family:sans-serif;font-size: 36px;font-weight:500;line-height:1.1;"> {currency_code} {amount}	
								</div>
								<div class="text-center" style="text-align:center;border-top:1px solid rgba(0,0,0,0.2);padding-top:3px; ">
									<i class="payment_source_name">{payment_source_name} &nbsp;</i>
								</div>
							</td>
							<td style="width:50%; text-align:left;border: 1px solid rgba(0,0,0,0.2); padding:5px;margin:0px;">		
								<div>&nbsp;</div>								
								<div style="height:70px;text-align:center;color:rgba(100,100,100,1); font-family:sans-serif; font-size:1.3em;">
									This is computer generated<br />No signature is required
								</div>
								<div class="text-center" style="border-top:1px solid rgba(0,0,0,0.2);padding-top:3px;">
									<i class="payment_source_name">&nbsp;</i>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<div>&nbsp;</div>
				<div style="color:brown; font-size:0.8em;"><?php if ((!empty($company_phone)) || (!empty($company_email))) { ?><i>For further information, kindly contact us at <?php if (!empty($company_phone)) { echo "Phone: ".$company_phone; } ?> <?php if (!empty($company_email)) { echo "Email: ".$company_email; } ?></i><?php } ?></div>	
				<div>&nbsp;</div>					
			</div>
			
		</div>
	</div>
	{/data}
</div>

