<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="">Bill No</th>
				<th class="">Customer No</th>
				<th class="">Customer Name</th>
				<th class="text-center">Bill Date</th>
				<th class="text-center">Status</th>
				<th class="text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				<?php foreach ($row_data as $row): ?>
					<tr class="<?= $row['is_approver'] == '1' ? 'highlighted' : '' ; ?>">
						<td class="<?php echo $row['row_style']; ?>"><?php echo $row['bill_draft_no']; ?></td>
						<td class="<?php echo $row['row_style']; ?>"><?php echo $row['customer_no']; ?></td>
						<td class="<?php echo $row['row_style']; ?>"><?php echo $row['name']; ?></td>
						<td class="<?php echo $row['row_style']; ?> text-center"><?php echo $row['last_bill_date']; ?></td>
						<td class="text-center"><?php echo $row['status_icon']; ?></td>
						<td class="text-right action_btn_td">
							<?php if ($row['is_void'] != '1' && $row['status'] == 'C'): ?>
								<a class="no-hover-underline" href="#" onclick="prep_void_bill(<?php echo $row['bill_no']; ?>)" class="px-1">
									<i class="menu-icon fa fa-remove red" data-toggle="tooltip" title="Void"></i>
								</a>
								<span class="visible-inline" style="border-left: 1px solid #ccc; margin: 0 5px; height: 20px; display: inline-block; vertical-align: middle;"></span>
								<a href="<?php echo base_url("bill/bill_statement/bill/" . $row['bill_no']); ?>" class="px-1">
									<i class="menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print'); ?>></i>
								</a>
							<?php endif; ?>
							<a href="<?php echo base_url('bill/bill_manual_detail/' . $row['bill_draft_no']); ?>" title="Edit" class="px-1">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="6" class="text-center">No Record Available</td>
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