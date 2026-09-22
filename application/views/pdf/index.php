<div>
	<div class="page">					
		<table width="100%" cellspacing="0" cellpadding="0" style="height: 1050px;">
			<tr>
				<td width="5%"></td>
				<!-- left side -->
				<td style="vertical-align: top; width: 45%;">
					<img src="<?= $company_logo ?>" alt="company_logo" width="200px" style="margin-top: 20px;">
					<br><br><br>

					<b>{ customer_name }</b><br>
					{!! customer_address !!}
				</td>
				<!-- right side -->
				<td width="50%" style="padding: 0px; border: none;">
					<div style="background-color: #2caae0; height: 100%; width: 100%; border-top-right-radius: 55px; border-bottom-left-radius: 55px;">

						<div style="height: 25px;"></div>

						<div style="background-color: #ffffff; width: 60%; border-top-right-radius: 55px; border-bottom-right-radius: 55px; padding: 5px;">
							&nbsp;&nbsp;&nbsp;My Bill Overview
						</div>

						<br><br>

						<table width="90%" style="font-size: 12px; margin: 5px; margin-left: 25px;" cellspacing="0" cellpadding="0">
							<tr>
								<td>Account No.</td>
								<td rowspan="2" class="text-right"><h4>Total Due (RM)</h4></td>
							</tr>
							<tr>
								<td><b>{ account_no }</b></td>
							</tr>
							<tr>
								<td>Bill No</td>
								<td rowspan="2" class="text-right"><h4>{ charges }</h4></td>
							</tr>
							<tr>
								<td><b>{ bill_no }</b></td>
							</tr>
							<tr>
								<td>Bill Date</td>
							</tr>
							<tr>
								<td><b>{ bill_date }</b></td>
							</tr>
							<tr>
								<td>Bill Period</td>
							</tr>
							<tr>
								<td><b>{ bill_period }</b></td>
							</tr>
							<tr>
								<td style="font-size: 15px;"><br><b>Previous Overdue</b></td>
								<td style="font-size: 15px;" class="text-right"><br><b>{ previous_balance }</b></td>
							</tr>
							<tr>
								<td style="font-size: 15px;"><br><br><b>Current Bill</b></td>
								<td style="font-size: 15px;" class="text-right"><br><br><b>{ amount }</b></td>
							</tr>
						</table>
						<div style="margin-top: 400px; font-size: 12px; margin-left: 25px;">
							ABCD Network Sdn Bhd <br>
							Registration no: 20070185153 (784125-X) <br>
							THE <br>
							1-15-20 Lebuh Nipah 5, <br>
							11900 Bayan Lepas Pulau Pinang <br>
						</div>
					</div>
				</td>
			</tr>
		</table>

		<div style="border-top: dashed 1px red;"></div>
	</div>
</div>