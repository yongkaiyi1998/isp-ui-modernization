<!--
page = style="min-height:148mm !important;width:206mm;"
subpage = style="height:138mm !important;"
-->
<style>
@page {
    size: a4 portrait;
}

html {
  -webkit-print-color-adjust: exact;
}
</style>
<div class="book">
	{data}
	<div class="page"> 
		<div class="subpage">
			<!--
			<div class='text-center'><img src='<?php echo base_url("images/logo_letterhead.jpg");?>' /></div>
			<h2 class="text-center" style="font-family:sans-serif">&nbsp;Official Receipt</h2>
			-->
			<div class="content_single_line"></div>
			<table cellpadding="0" cellspacing="0" style="width:100%;">
				<tr>
					<td style="width:80px;vertical-align: top;">
						<?php
						$company_logo = $this->config->item('doc_logo');
						$proj_name = $this->config->item('proj_name');

						if (empty($proj_name)) {
							$proj_name = 'Itelco';
						}
						?>
						<?php if (!empty($company_logo)) { ?>
						<img style="width:70px;margin:5px;" src="<?php echo $company_logo; ?>" >
						<?php } else { ?>
						<img src="<?php echo $logo_url; ?>" ><?php echo $proj_name; ?>
						<?php } ?>
					</td>
					<td style="vertical-align: top;">
						<div style="font-size:14px;margin:5px;">
							<!-- address -->
							<span style="font-weight:bold;"><?php echo $company_full_name; ?> <?php if(!empty($company_brn)) echo '(' . $company_brn . ')'; ?></span><br>
							<?php if (!empty($company_addr_1)) { ?>
							<?php echo $company_addr_1; ?><br>
							<?php } ?>
							<?php if (!empty($company_addr_2)) { ?>
							<?php echo $company_addr_2; ?><br>
							<?php } ?>
							<?php if (!empty($company_addr_3)) { ?>
							<?php echo $company_addr_3; ?><br>
							<?php } ?>
							<?php if (!empty($company_postal) && !empty($company_city) && !empty($company_state)) { ?>
							<?php echo $company_postal; ?> <?php echo $company_city; ?>, <?php echo $company_state; ?>, Malaysia<br><br>
							<?php } ?>
							<?php if (!empty($company_phone) || !empty($company_fax) || !empty($company_email)) { ?>
							<?php if (!empty($company_phone)) { echo "Tel: ".$company_phone."&nbsp;&nbsp;"; } ?><?php if (!empty($company_fax)) { echo "Fax: ".$company_fax."&nbsp;&nbsp;"; } ?><?php if (!empty($company_email)) { echo "Email: ".$company_email."&nbsp;&nbsp;"; } ?>
							<?php } ?>
						</div>
					</td>
				</tr>

			</table>

			<div class="content_body" style="font-size: 12px;">

				<div class="content_single_line"></div>

				<h2 class="text-center" style="text-align:center;font-family: Helvetica, Arial, sans-serif;">
					RESIT RASMI
				</h2>

				<div class="col-lg-12">&nbsp;</div>

				<table border="0" class="col-lg-12" >
					<tbody>
						<tr>
							<td width="50%" style="vertical-align:top">
								<table border="0" >
									<tbody>
										<tr>
											<td class="text-left" style="width:100px;">Nama Projek</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left" style="width:250px;">{project_name}</td>
										</tr>
										<tr>
											<td class="text-left">No. Unit</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{unit_name}</td>
										</tr>
										<tr>
											<td class="text-left">No. Akaun</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{customer_no}</td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>
										<tr>
											<td class="text-left">Terima Daripada</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{customer_name}</td>
										</tr>
										<tr>
											<td class="text-left">No. Rujukan</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{cheque_no}</td>
										</tr>
										<tr>
											<td class="text-left">Sebanyak</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{amount_in_words_malay}</td>
										</tr>
										<tr>
											<td class="text-left">Untuk Bayaran</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{ref_no}</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td width="50%" style="vertical-align:top">
								<table border="0" >
									<tbody>
										<tr>
											<td class="text-left" style="width:100px;"></td>
											<td class="col-lg-1 text-right"></td>
											<td class="col-lg-6 text-left"></td>
										</tr>
										<tr>
											<td class="text-left">No. Resit</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{payment_no}</td>
										</tr>
										<tr>
											<td class="text-left">Tarikh</td>
											<td class="col-lg-1 text-right">:&nbsp;</td>
											<td class="col-lg-6 text-left">{pay_date}</td>
										</tr>
									</tbody>
								</table>	
							</td>
						</tr>
					</tbody>
				</table>

				<?php if (!empty($data[0]['bill_details'])) { ?>

				<div class="col-lg-12">&nbsp;</div>

				<table border="1" bordercolor='C0C0C0' width="98%" style="margin: auto;" class="item_table">
					<thead>
						<tr>
							<th class="text-center col-lg-1">No.</th>
							<th class="text-center">Perihal</th>
							<th class="text-center col-lg-2">Amaun ( {currency_code} )</th>
							<th class="text-center col-lg-1">Kod Cukai</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data[0]['bill_details'] as $detail_key => $detail_row) { ?>
						<tr>
							<td style='text-align:right;padding:0px 5px 0px 5px;'><?php echo $detail_row['item_count']; ?></td>
							<td style='text-align:left;padding:0px 5px 0px 5px;'><?php echo $detail_row['bill_type_name']; ?> <?php if (!empty($detail_row['remark'])) { ?>(<?php echo $detail_row['remark']; ?>)<?php } ?></td>
							<td style='text-align:right;padding:0px 5px 0px 5px;'><?php echo $detail_row['item_amount']; ?></td>
							<td style='text-align:center;padding:0px 5px 0px 5px;' ><?php echo $detail_row['tax_code']; ?></td>
						</tr>
						<?php } ?>
						<tr>
							<td></td>
							<td style='padding:0px 5px 0px 5px;'>Pakej: {package_name}</td>
							<td></td>
							<td></td>
						</tr>
					</tbody>
				</table>

				<?php } ?>

				<div class="col-lg-12">&nbsp;</div>

				<table border="0" class="col-lg-12" >
					<tbody>
						<tr>
							<td width="50%" style="vertical-align:top">
								<table border="0" style="width:400px;" >
									<tbody>
										<tr>
											<td class="text-left">Disediakan Oleh</td>
										</tr>
										<tr>
											<td class="text-left">{prepared_name}</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td width="50%" style="vertical-align:top;text-align:right;">
								<table border="0" style="width:100%;" >
									<tbody>
										<tr>
											<td class="text-right">Resit ini hanya sah setelah cek diperakui oleh bank.</td>
										</tr>
										<tr>
											<td class="text-right">Ini adalah cetakan komputer. Tiada tandatangan diperlukan.</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>

			</div>

			<div class="content_footer">					
				<div class="content_single_line"></div>

				<div>&nbsp;</div>
				<div style="color:brown; font-size:0.8em;"><?php if ((!empty($company_phone)) || (!empty($company_email))) { ?><i>For further information, kindly contact us at <?php if (!empty($company_phone)) { echo "Phone: ".$company_phone; } ?> <?php if (!empty($company_email)) { echo "Email: ".$company_email; } ?></i><?php } ?></div>	
				<div>&nbsp;</div>					
			</div>
			
		</div>
	</div>
	{/data}
</div>