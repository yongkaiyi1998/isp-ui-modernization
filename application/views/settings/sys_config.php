<?php echo flash_data_helper($msg); ?>
<?php echo $fm_open; ?>
	<div class="col-lg-5 mobile-block-list-wrap">	

		<?php
			if(!empty($inputs['main']))
			{
				$f_title = 'General Settings';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['main'] as $key => $input) echo $input;
				echo "</fieldset>";
			}

		?>

	</div>

	<div class="col-lg-4 mobile-block-list-wrap">	

		<?php
			if(!empty($inputs['profile']))
			{
				$f_title = 'Profile Settings';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['profile'] as $key => $input) echo $input;
				echo "</fieldset>";
			}

		?>

	</div>

	<div class="col-lg-4 mobile-block-list-wrap">

		<?php
			if(!empty($inputs['bills']))
			{
				$f_title = 'Billing';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['bills'] as $key => $input) echo $input;
				echo "</fieldset>";
			}

		?>

	</div>

	<div class="col-lg-4 mobile-block-list-wrap">

		<?php
			if(!empty($inputs['einvoice']))
			{
				$f_title = 'E-invoice';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['einvoice'] as $key => $input) echo $input;
				echo "</fieldset>";
			}

		?>

	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['suspend']))
			{
				$f_title = 'Suspend & Termination';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['suspend'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">

		<?php
			if(!empty($inputs['email']))
			{
				$f_title = 'Email Settings';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['email'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>

	</div>
	
	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['sms']))
			{
				$f_title = 'Maxis Direct SMS Settings';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['sms'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['ifca']))
			{
				$f_title = 'IFCA Export Settings';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['ifca'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>
	
	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['radius']))
			{
				$f_title = 'Radius Server Settings';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['radius'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['app']))
			{
				$f_title = 'App Config';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['app'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['push']))
			{
				$f_title = 'Mobile App Push Notification Config';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['push'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['fpx']))
			{
				$f_title = 'Fpx Config';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['fpx'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['registration']))
			{
				$f_title = 'Registration Config';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['registration'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>

	<div class="col-lg-4 mobile-block-list-wrap">
		<?php
			if(!empty($inputs['dealer']))
			{
				$f_title = 'Dealer Config';
				echo "<fieldset class='category-border'><legend class='category-border'>$f_title</legend>	";
				foreach ($inputs['dealer'] as $key => $input) echo $input;
				echo "</fieldset>";
			}
		?>
	</div>	
	
	<div class="col-lg-12">&nbsp;</div>
	<div class="col-lg-12">
		<div>
			<?php if(!empty($buttons)) foreach ($buttons as $key => $button) echo $button.'&nbsp;'; ?>
		</div>
	</div>

	<div id="clear" style="clear:both;"></div>
	<div id="popupDetail">
		<div id="popupDetailStd" onclick="disablePopup();">
			<div id="popupContent">
				&nbsp;
			</div>
		</div>
	</div>
	<div id="backgroundPopup" onclick="hide_popup();"></div>

	<script>
		function init()
		{
			url_delete_button 	="";
			url_cancel_button	="<?php echo base_url('settings/index');?>";
			url_add_button 		="";

			//~ $('.delete_button').show();
			//~ $('.add_button').show();
			$('.cancel_button').show();
		}
	</script>
	<script type="text/JavaScript" src="<?php echo base_url("js/tinymce/js/tinymce/tinymce.min.js?".cssjs_ver()); ?>"></script>
	<script type="text/JavaScript" src="<?php echo base_url("js/itelco/settings.js?".cssjs_ver()); ?>" ></script>
	
<?php echo $fm_close; ?>
