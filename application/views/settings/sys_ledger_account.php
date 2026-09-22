<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">

			<h4>{page_title}</h4>
		</div>
		
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<!-- flash data -->
												
		<form action="{form_action}" method="post" accept-charset="utf-8" id="ledger_account_details">	
			<div class="col-lg-12">		
				<fieldset class="category-border">
					<legend class="category-border">Payment Source</legend>
					<div style="overflow-x:auto;">					
						<div style="min-width:900px;">
							<div class="input-group">
								<span class="input-group-addon" style="font-size:10px; min-width:350px; text-align:right;">&nbsp;</span>
								<?php 
									$width = number_format( floor((100/(count( $customer_categories )*1)*100)/100), 2 );
									foreach( $customer_categories AS $category ){
										//for( $z = 0 ; $z < 2 ; $z++ ){
											//$debit_credit = $z==0?"DR":"CR";
											echo "<input class='form-control' type='text' style='text-align:center;width:".$width."%;' 
													value='".$category['name']."' title='".$category['name']."' READONLY />" ; 									
										//}
									}
								?>
							</div>

							<?php foreach( $payment_sources As $source ){ ?>
								<?php for( $z = 0 ; $z < 2 ; $z++ ){ ?>
									<?php $debit_credit = $z==0?"DR":"CR"; ?>
										<div class="input-group">
										<?php if ($z==0) { ?>
										<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-bottom:0px; border-top:0px;">
											<?php echo $source['name']; ?>
										</span>
									<?php } else { ?>
										<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-top:0px;">
											
										</span>
										<?php } ?>
										<span class="input-group-addon" style="font-size:10px; min-width:50px; text-align:right;">
											(<?php echo $debit_credit; ?>)
										</span>
										<!--
										<input name="payment[<?php echo $source['payment_source_id']; ?>]" 
											id="payment[<?php echo $source['payment_source_id']; ?>]" 
											value="<?php echo $source['ledger_account_code']; ?>"  
											placeholder="Ledger Account" style="width:33%;" class="form-control" type="text" />
										<input class="form-control" type="text" style="width:33%;" />
										<input class="form-control" type="text" style="width:33%;" />
										-->
										<?php 
											foreach( $customer_categories AS $category ){
												//for( $z = 0 ; $z < 2 ; $z++ ){
													//$debit_credit = $z==0?"DR":"CR";
												echo "<input 
														name='glacc_p[".$source['payment_source_id']."][".$category['category_code']."][".$debit_credit."]'
														placeholder='Ledger Account'
														class='form-control' type='text' 
														style='text-align:center;width:".$width."%;' 
														value='".$source[$category['category_code']][$debit_credit]."'  />" ;
												//}
											}
										?>
									</div>
								<?php } ?>
							<?php } ?>		
						</div>
					</div>
								
				</fieldset>
			</div>
			
			<div class="col-lg-12">	
					<fieldset class="category-border">
						<legend class="category-border">Bill Type</legend>
						<div style="overflow-x:auto;">					
							<div style="min-width:900px;">
									<div class="input-group">
										<span class="input-group-addon" style="font-size:10px; min-width:350px; text-align:right;">&nbsp;</span>
										<?php 
										/*
											foreach( $customer_categories AS $category ){
												echo "<input class='form-control' type='text' style='text-align:center;width:".$width."%;' 
														value='".$category['name']."' READONLY />" ; 
											}
											*/
											foreach( $customer_categories AS $category ){
												//for( $z = 0 ; $z < 2 ; $z++ ){
													//$debit_credit = $z==0?"DR":"CR";
													echo "<input class='form-control' type='text' style='text-align:center;width:".$width."%;' 
															value='".$category['name']."' READONLY title='".$category['name']."' />" ; 									
												}
											//}
											
										?>
									</div>
							
									<?php foreach( $bill_types As $type ){ ?>
										<?php for( $z = 0 ; $z < 2 ; $z++ ){ ?>
											<?php $credit_debit = $z==0?"DR":"CR"; ?>
											<div class="input-group">

											<?php if ($z==0) { ?>
											<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-bottom:0px; border-top:0px;">
												<?php echo $type['name']; ?>
											</span>
											<?php } else { ?>
											<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-top:0px;">
												
											</span>
											<?php } ?>
											<span class="input-group-addon" style="font-size:10px; min-width:50px; text-align:right;">
												(<?php echo $credit_debit; ?>)
											</span>
											<!--
											<input name="bill[<?php echo $type['bill_type_id']; ?>]" id="bill[<?php echo $type['bill_type_id']; ?>]" 
												value="<?php echo $type['ledger_account_code']; ?>"  placeholder="Ledger Account" style="width:100%;padding-right:0.5em;" class="form-control" type="text">
											-->
											
											<?php 
												foreach( $customer_categories AS $category ){
													//for( $z = 0 ; $z < 2 ; $z++ ){
														//$credit_debit = $z==0?"DR":"CR";
													echo "<input 
															name='glacc_b[".$type['bill_type_id']."][".$category['category_code']."][".$credit_debit."]'
															placeholder='Ledger Account'
															class='form-control' type='text' 
															style='text-align:center;width:".$width."%;' 
															value='".$type[$category['category_code']][$credit_debit]."'  />" ; 
													//}
												}
											?>

											</div>
										<?php } ?>	
									<?php } ?>	
							</div>
						</div>
														
					</fieldset>
			</div>

			<div class="col-lg-12">
				<fieldset class="category-border">
					<legend class="category-border">Package/Product</legend>
					<div style="overflow-x:auto;">					
						<div style="min-width:900px;">

							<div class="input-group">
								<span class="input-group-addon" style="font-size:10px; min-width:350px; text-align:right;">&nbsp;</span>
								<?php 
								foreach( $customer_categories AS $category ){
										echo "<input class='form-control' type='text' style='text-align:center;width:".$width."%;' 
												value='".$category['name']."' READONLY title='".$category['name']."' />" ; 									
									}
									
								?>
							</div>

							<?php 
								foreach( $packages As $package ){ ?>
									<?php for( $z = 0 ; $z < 2 ; $z++ ){ ?>
									<?php $credit_debit = $z==0?"DR":"CR"; ?>
									<div class="input-group">

									<?php if ($z==0) { ?>
									<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-bottom:0px; border-top:0px;">
										<?php echo $package['name']; ?>
									</span>
									<?php } else { ?>
									<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-top:0px;">
										
									</span>
									<?php } ?>
									<span class="input-group-addon" style="font-size:10px; min-width:50px; text-align:right;">
										(<?php echo $credit_debit; ?>)
									</span>
									
									<?php 
										foreach( $customer_categories AS $category ){
											echo "<input 
													name='glacc_k[".$package['package_no']."][".$category['category_code']."][".$credit_debit."]'
													placeholder='Ledger Account'
													class='form-control' type='text' 
													style='text-align:center;width:".$width."%;' 
													value='".$package[$category['category_code']][$credit_debit]."'  />" ; 
										}
									?>

									</div>
								<?php } ?>	
							<?php } ?>
						</div>
					</div>
				</fieldset>
			</div>

			<div class="col-lg-12">	
				<fieldset class="category-border">
					<legend class="category-border">Agents</legend>
					<div style="overflow-x:auto;">					
						<div style="min-width:900px;">

							<?php foreach( $agents As $agent ){ ?>
								<?php for( $z = 0 ; $z < 2 ; $z++ ){ ?>
								<?php $credit_debit = $z==0?"DR":"CR"; ?>
								<div class="input-group">
								<?php if ($z==0) { ?>
								<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-bottom:0px; border-top:0px;">
									<?php echo $agent['name']; ?>
								</span>
								<?php } else { ?>
								<span class="input-group-addon" style="font-size:10px; min-width:300px; text-align:right; border-top:0px;">
									
								</span>
								<?php } ?>
								<span class="input-group-addon" style="font-size:10px; min-width:50px; text-align:right;">
									(<?php echo $credit_debit; ?>)
								</span>

								<?php
								echo "<input 
										name='glacc_a[".$agent['dealer_no']."][r][".$credit_debit."]' 
										placeholder='Ledger Account'
										class='form-control' type='text' 
										style='text-align:center;width:".$width."%;' 
										value='".$agent['r'][$credit_debit]."'  />" ;
								?>
								</div>
								<?php } ?>	
							<?php } ?>		
						</div>
					</div>
				</fieldset>
			</div>
				
			<div class="col-lg-12">						
				<fieldset class="category-border">
					<legend class="category-border">Customer Category</legend>	
					<div style="overflow-x:auto;">					
						<div style="min-width:900px;">
							<?php foreach( $customer_categories As $cust ){ ?>
								<div class="input-group">
								<span class="input-group-addon" style="font-size:10px; min-width:350px; text-align:right;"><?php echo $cust['name']; ?></span>
								<input name="customer[<?php echo $cust['category_code']; ?>]" id="customer[<?php echo $cust['category_code']; ?>]" 
									value="<?php echo $cust['ledger_account_code']; ?>"  placeholder="Ledger Account" style="width:100%;padding-right:0.5em;" class="form-control" type="text">
								</div>
							<?php } ?>		
						</div>
					</div>
				</fieldset>
			</div>
			
			<div class="col-lg-12">&nbsp;</div>
			
			<div class="col-lg-12">
				<div class="button-group">
					<button name="save" type="submit" class="btn btn-success" id="save" value="save" title="Save record">
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save</button>&nbsp;	
					<span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>	
				</div>
			</div>
		</form>
		
		<div id="clear" style="clear:both;"></div>
		<div id="popupDetail">
			<div id="popupDetailStd" onclick="disablePopup();">
				<div id="popupContent">
					&nbsp;
				</div>
			</div>
		</div>
		<div id="backgroundPopup" onclick="hide_popup();"></div>
		</div><!--panel-body-->
	</div><!--panel panel-default-->
</div>

<script>
	showLoadingIcon = function () {
		$('#loading-icon').show();
	}

	hideLoadingIcon = function () {
		$('#loading-icon').hide();
	}

	$('#ledger_account_details').submit(function (e) {
		e.preventDefault();

		$('.input-group-addon').parent().removeClass('has-error');
		$('.button-group button').prop('disabled', true);

		let formData = new FormData(this);
		showLoadingIcon();

		$.ajax({
			dataType: 'json',
			url: $(this).attr('action'),
			type: 'POST',
			data: formData,
			success: function (data) {
				hideLoadingIcon();
				if(data['status'] == 'ER'){
					handle_ajax_error(data);
					$('.button-group button').prop('disabled', false);
				} else {
					window.location.href=base_url+data['url'];
				}
			},
			error: function (data) {
				hideLoadingIcon();
				$('.button-group button').prop('disabled', false);
				$.gritter.add({
					title: 'ERROR',
					text: 'Something wrong has occured during saving.',
					time: '5000',
					close_icon: 'l-arrows-remove s16',
					class_name: 'info-notice',
				});
			},
			cache: false,
			contentType: false,
			processData: false
		});
	});
</script>