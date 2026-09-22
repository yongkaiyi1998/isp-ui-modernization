<link rel="stylesheet" href="<?php echo base_url("css/theme/bootstrap.css?1"); ?> "/>
<link rel="stylesheet" href="<?php echo base_url("css/theme/font-awesome.css?1"); ?> "/>
<div class="book" >	<?php if(!empty($data)):?>
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
				<br><br>
					<table border="0" width="98%" class="center" style="">
						<tr>
							<td width="45%" style="vertical-align:top">		
								<table border="0">
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $data['display_addr1']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $data['display_addr2']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $data['display_city']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'><?php echo $data['display_postcode'].' '.$data['display_state']; ?></td>
									</tr>
									<tr style="vertical-align:top">
										<td colspan='3'></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					
				<div class="col-lg-12">&nbsp;</div>
								
				<div class="col-lg-12 content_body text-left" style="line-height:1.15em;font-size:16px">

					Dear <?php echo $data['display_name']; ?> , <br /><br />
					Thank you for subscribing to <?php echo ucwords( $this->config->item('proj_name') ); ?> Broadband Services. Kindly refer to your email for your<br />
					billing statement and correspondence. Below is the account information of your subscription :<br /><br />
											
					<table style="border:none;">
						<tr>
							<td style="width:150px;">Subscriber No</td>
							<td style="width:25px;">:</td>
							<td><?php echo $data['customer_no'] ?></td>
						</tr>
						<tr>
							<td>Login</td>
							<td>:</td>
							<td><?php echo $data['login_username'] ?></td>
						</tr>
						<tr>
							<td>Password</td>
							<td>:</td>
							<td><?php echo $data['login_password'] ?></td>
						</tr>
					</table><br/>

					Kindly refer to http://www.penangfon.com/emailguide/index.html for E-mail Access User Guide.<br /><br />
					
					Payment Methods : <br />
					a) Through EON Bank (Over the counter)
						Please fill in "Agency Bills Payment Slip" and follow the <br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;steps below when making your payments:<br /><br />
						1) Please fill your name as per your bill statement<br />
						2) Name of Agencies : Optical Communication Engineering Sdn Bhd<br />
						3) Reference: Please fill in your 10-digit subscribers number as in your bill statement<br />
						4) Please fill in the amount for your payment<br /><br />
					
					b) Our service counter <br />&nbsp;&nbsp;&nbsp;&nbsp;
						MY PENANG FON SDN BHD <br />&nbsp;&nbsp;&nbsp;&nbsp;
						Lot 1-2-11, Mayang Mall Complex, Jln Mayang Pasir 1, 11950 Bayan Lepas, Penang.<br />
						<br />
					c) Payment via UOB Payonline at http://payonline.uob.com.my <br />
						<br />
					d) Payment via EON Internet Banking at https://ebank.eonbank.com.my <br />
						<br />
					e) Payment via Maybank Internet Banking at http://www.maybank2.com.my &nbsp;&nbsp;&nbsp;&nbsp;
						(MetroFon/PenangFon)<br />
						<br />
					Please contact our Customer Service at 04-683 8888 or Toll Free No: 1-700-81-8862 <br />
					or email to customerservice.penangfon@gmail.com should you require any assistance.<br />
					<br />
					This notice is generated automatically by the system, therefore please do not reply to this mail.<br/>
					<br />
					Thank you.<br /><br />
					<span style="font-weight:bold" >For <?php echo ucwords( $this->config->item('proj_name') ); ?></span>
				</div>
			
				<div class="content_footer" style="position:relative;padding-top:5px;width:100%;">
					<div class="col-lg-12">&nbsp;</div>
					<div class="text-center" style="font-weight:bold" >SERVICE PROVIDED BY OPTICAL COMMUNICATION ENGINEERING SDN.BHD.</div>
					<!--<div class="col-lg-12" style="padding-bottom:10em;">&nbsp;</div>-->
				</div>
			</div>
		</div>
<?php endif; ?>	</div></body></html>
