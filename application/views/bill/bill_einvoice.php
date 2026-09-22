<style type="text/css">
#popupContent {
    /* height: 60px; */
    width: 100%;
    font-size: unset;
    font-weight: bold;
    line-height: unset;
}
</style>

<div class="container einvoice_modal">
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo $page_title; ?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">

				<div class="col-lg-10">
					<fieldset class="category-border">
						<legend class="category-border">Invoice
						</legend>

						<div class="col-lg-12">		
							<ul class="list-group label-custom">
							  <li class="list-group-item">
									<span class="label label-info ">E-Invoice Status</span>
									<span><?php echo (isset($einvoice_status[$row_data['einvoice_status']]) ? $einvoice_status[$row_data['einvoice_status']] : 'Unsubmitted'); ?></span>
							  </li>
							  <li class="list-group-item">
									<span class="label label-info ">UUID</span>
									<span><?php echo $row_data['einvoice_uuid']; ?></span>
							  </li>
							</ul>
						</div>

					</fieldset>

					<?php if (!empty($row_data['cn_einvoice_status']) && $row_data['is_void'] == '1') { ?>
					<fieldset class="category-border">
						<legend class="category-border">Void (Credit Note)
						</legend>

						<div class="col-lg-12">		
							<ul class="list-group label-custom">
							  <li class="list-group-item">
									<span class="label label-info ">E-Invoice Status</span>
									<span><?php echo (isset($einvoice_status[$row_data['cn_einvoice_status']]) ? $einvoice_status[$row_data['cn_einvoice_status']] : ''); ?></span>
							  </li>
							  <li class="list-group-item">
									<span class="label label-info ">UUID</span>
									<span><?php echo $row_data['cn_einvoice_uuid']; ?></span>
							  </li>
							</ul>
						</div>

					</fieldset>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>
</div>
