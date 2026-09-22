<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1">Payment No</th>
				<th class="text-center">Post Date</th>
				<th class="text-center">Pay Date</th>
				<th>Customer Name</th>
				<th >Remark</th>
				<th class="col-lg-1 text-right">Amount</th>
				<th class="col-lg-1 text-right">Paid for bill</th>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr>
						<td class="align-middle-important">{payment_no}</td>
						<td class="text-center align-middle-important">{tranx_date}</td>
						<td class="text-center align-middle-important">{pay_date}</td>
						<td>
							<div>{customer_name}</div>
							<small class="text-muted">{customer_no}</small>
						</td>
						<td class="align-middle-important">{remark}</td>
						<td class="text-right align-middle-important">{amount}</td>
						<td class="text-right align-middle-important">{ref_no}</td>
						<td class="text-right action_btn_td align-middle-important">
							<a href="#" onclick="print_receipt({payment_no})">
								<i class="menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print Receipt'); ?>></i>
							</a>
							<a href="<?php echo base_url('payment/edit_payment');?>/{payment_no}" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="8" class="text-center">No Record Available</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
</div>
<div>
	<div class="col-md-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>