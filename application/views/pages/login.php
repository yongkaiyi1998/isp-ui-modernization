<div class="ui-login-shell">
	<div class="ui-login-card" role="main" aria-labelledby="login-title">
		<?php echo form_open('auth/authenticate'); ?>
			<?php
			$company_logo = $this->config->item('logo_img');
			$proj_name = $this->config->item('proj_name');

			if (empty($proj_name)) {
				$proj_name = 'Itelco';
			}
			?>

			<div class="ui-login-brand">
				<?php if (!empty($company_logo)) { ?>
				<img class="ui-login-logo" src="<?php echo $company_logo; ?>" alt="<?php echo html_escape($proj_name); ?>">
				<?php } else { ?>
				<div class="ui-login-brand-fallback">
					<img class="ui-login-logo ui-login-logo-icon" src="<?php echo base_url("/images/telco-icon.png"); ?>" alt="">
					<span><?php echo html_escape($proj_name); ?></span>
				</div>
				<?php } ?>
			</div>

			<div class="ui-login-intro">
				<h1 id="login-title">Sign in</h1>
				<p>Enter your account credentials to continue.</p>
			</div>

			<?php if (isset($err_msg)) { ?>
			<div class="ui-login-alert" role="alert">
				<span class="err_msg"><?php echo html_escape($err_msg); ?></span>
			</div>
			<?php } ?>

			<?php if (ENVIRONMENT != 'production') { ?>
			<div class="ui-login-environment">Developer Mode</div>
			<?php } ?>

			<div class="ui-login-fields">
				<label class="ui-login-label">
					<span>Username</span>
					<input id="AUTH_USER" name="AUTH_USER" required="required" value="" placeholder="Username" class="form-field ui-login-input">
				</label>

				<label class="ui-login-label">
					<span>Password</span>
					<input name="AUTH_PW" type="password" required="required" value="" placeholder="Password" class="form-field ui-login-input">
				</label>

				<?php if(!empty($captcha)): ?>
				<div class="ui-login-captcha">
					<span class="ui-login-label-text">Security verification</span>
					<div class="ui-login-captcha-image"><?php echo $captcha['image']; ?></div>
					<label class="ui-login-label ui-login-captcha-field">
						<span>Captcha code</span>
						<input name="AUTH_CAPTCHA" required="required" value="" placeholder="Captcha Code" class="form-field ui-login-input">
					</label>
				</div>
				<?php endif; ?>
			</div>

			<div class="submit-container ui-login-actions">
				<input class="submit-button ui-login-submit" type="submit" value="Log In" />
				<?php if ($termination_flow == 1) { ?>
				<div class="ui-login-portal"><a href="<?php echo base_url("auth/clogin"); ?>">Customer Portal</a></div>
				<?php } ?>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>
<script>
window.onload = function() {
  var input = document.getElementById('AUTH_USER').focus();
}
</script>
