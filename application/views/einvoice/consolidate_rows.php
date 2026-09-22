<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="text-left">Inv No</th>
				<th class="text-center">Date</th>
				<th class="text-right">Bill Total</th>
				<th class="text-center">E-invoice Status</th>
				<th class="text-left">E-invoice Submitted</th>
				<th class="text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				<?php foreach ($row_data as $val) { ?>
					<tr>
						<td class="text-left"><?php echo $val['inv_no']; ?></td>
						<td class="text-center"><?php echo $val['inv_date']; ?></td>
						<td class="text-right"><?php echo $val['amount']+$val['tax']; ?></td>
						<td class="text-center"><?php echo $val['einvoice_status_text']; ?></td>
						<td class="text-left"><?php echo $val['created_on']; ?></td>
						<td class="text-center">
						<?php if ( ($val['einvoice_status'] != 'S') && ($val['einvoice_status'] != 'P') ) { ?>
							<button id='btEinvoice_<?php echo $val['inv_no']; ?>' name='btEinvoice' type='button' value='Send' class='btn btn-success btn-xs' onclick='submit_consolidated_einvoice("<?php echo $val['inv_no']; ?>", this);'>
								<i class='menu-icon fa fa-send' data-toggle='tooltip' title=''></i> Submit
							</button><img id='loading_<?php echo $val['inv_no']; ?>' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' />
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