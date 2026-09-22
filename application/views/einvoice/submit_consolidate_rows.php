<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="text-center"><input style="margin: -2px 0 0;" type="checkbox" id="chk_all" name="chk_all" value="1" ></th>
				<th class="">Customer No</th>
				<th class="">Customer Name</th>
				<th class="text-left">Bill No</th>
				<th class="text-center">Bill Date</th>
				<th class="text-right">Bill Amount</th>
				<th class="text-center">Manual</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				<?php foreach ($row_data as $val) { ?>
					<tr>
						<td class="text-center"><input style="margin: -2px 0 0;" type="checkbox" id="chk_<?php echo $val['bill_no']; ?>" name="chk_<?php echo $val['bill_no']; ?>" value="1" data-bill_no="<?php echo $val['bill_no']; ?>" class="bill_no_chk" ></td>
						<td><?php echo $val['customer_no']; ?></td>
						<td><?php echo $val['name']; ?></td>
						<td class="text-left"><?php echo $val['bill_no']; ?></td>
						<td class="text-center"><?php echo $val['bill_date']; ?></td>
						<td class="text-right"><?php echo $val['amount']; ?></td>
						<td class="text-center"><?php echo ( ($val['is_manual'] == '1') ? 'Manual' : 'Auto-gen'); ?></td>
					</tr>
				<?php } ?>
			<?php else: ?>
				<tr>
					<td colspan="7" class="text-center">No bill to submit</td>
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