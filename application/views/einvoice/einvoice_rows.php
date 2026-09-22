<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="">Customer No</th>
				<th class="">Customer Name</th>
				<th class="text-left">Latest Bill No</th>
				<th class="text-center">Last Bill Date</th>
				<th class="text-right">Bill Amount</th>
				<th class="text-center">E-invoice Status</th>
				<th class="text-left">E-invoice Submitted</th>
				<th class="text-center">Type</th>
				<th class="text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				<?php foreach ($row_data as $val) { ?>
					<tr>
						<td><?php echo $val['customer_no']; ?></td>
						<td><?php echo $val['name']; ?></td>
						<td class="text-left"><?php echo $val['bill_no']; ?></td>
						<td class="text-center"><?php echo $val['last_bill_date']; ?></td>
						<td class="text-right"><?php echo $val['amount']; ?></td>
						<td class="text-center"><?php echo $val['einvoice_status_text']; ?></td>
						<td class="text-left"><?php echo $val['einvoice_submit_date']; ?></td>
						<td class="text-center"><?php echo $val['bill_type']; ?></td>
						<td class="text-center">
						<?php if ( ($val['einvoice_status'] != 'S') && ($val['einvoice_status'] != 'P') ) { ?>
							<button id='btEinvoice_<?php echo $val['bill_no']; ?>' name='btEinvoice' type='button' value='Send' class='btn btn-success btn-xs' onclick='submit_einvoice("<?php echo $val['bill_no']; ?>", "<?php echo $val['bill_type']; ?>", this);'>
								<i class='menu-icon fa fa-send' data-toggle='tooltip' title=''></i> Submit
							</button><img id='loading_<?php echo $val['bill_no']; ?>' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' />
						<?php } ?>
						</td>
					</tr>
				<?php } ?>
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