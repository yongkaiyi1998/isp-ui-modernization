<center>
	<br><br>
	<div class="m_1" style="padding: 0px;">
		<?php echo form_open('auth/authenticate'); ?>
				<div class="m">
					<div>&nbsp;</div>
					<div style="font-size:16px;">
					<?php
					$company_logo = $this->config->item('logo_img'); 
					$proj_name = $this->config->item('proj_name');

					if (empty($proj_name)) {
						$proj_name = 'Itelco';
					}
					?>
					<?php if (!empty($company_logo)) { ?>
					<img style="width:200px;" src="<?php echo $company_logo; ?>" >
					<?php } else { ?>
					<img src="<?php echo base_url("/images/telco-icon.png"); ?>" ><b><?php echo $proj_name; ?></b>
					<?php } ?>
					</div>					
					<div>
					<?php					
						if(isset($err_msg)){
							echo "<span class='err_msg'>".$err_msg."</span>";
						}
					?>
					</div>
					<div>&nbsp;</div>
					<?php if (ENVIRONMENT != 'production') { echo "<div>Developer Mode</div><div>&nbsp;</div>"; } ?>
					<input id="AUTH_USER" name="AUTH_USER" required="required" value="" placeholder="Username" class="form-field">					
					<input name="AUTH_PW" type="password" required="required" value="" placeholder="Password" class="form-field">
					<?php if(!empty($captcha)): ?>						
					<?php echo $captcha['image']; ?>
					<div>&nbsp;</div>
					<input name="AUTH_CAPTCHA" required="required" value="" placeholder="Captcha Code" class="form-field"><br>
					<?php endif; ?>	
					<div class="submit-container">
						<?php if ($termination_flow == 1) { ?>
						<div style="float:left;margin-left:20px;"><a href="<?php echo base_url("auth/clogin"); ?>">Customer Portal</a></div>
						<?php } ?>
						<input class="submit-button" type="submit" value="Log In" />
					</div>
					<div>&nbsp;</div>
				</div>
		<?php echo form_close(); ?>
	</div>
</center>
<div>

</div>
<script>
window.onload = function() {
  var input = document.getElementById('AUTH_USER').focus();
}
</script>

