<div class="book" >	


	<div class="page" data-page="{data}" > 
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

			<table border="0" width="50%" class="center" style="">
				<tr>
					<tr style="vertical-align:top">
						<td colspan='3' style="font-size:18px;">CUSTOMER DETAIL</td>
					</tr>
					<tr width="45%" style="vertical-align:top">
						<td>Customer No:</td>
						<td><?php echo $customer_model['row']['customer_no']?></td>
					</tr>
					<tr width="45%" style="vertical-align:top">
						<td>Customer Name:</td>
						<td><?php echo $customer_model['row']['name']?></td>
					</tr>
					<tr width="45%" style="vertical-align:top">
						<td>Package :</td>
						<td><?php echo $customer_model['row']['package_name']?></td>
					</tr>
					<tr width="45%" style="vertical-align:top">
						<td>Monthly Charge: </td>
						<td><?php echo $customer_model['row']['monthly_charge']?></td>
					</tr>
					<tr width="45%" style="vertical-align:top">
						<td>Login Username:</td>
						<td><?php echo $customer_model['row']['login_username']?></td>
					</tr>
					
				</tr>
			</table>
		</div>
	</div>

</div>