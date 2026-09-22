<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-10">Payment Type</th>
				<th class="col-lg-2 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				<?php foreach( $row_data AS $row ){ ?>
					<tr>
						<td><?php echo $row['name']; ?></td>
						<td class="text-right">
							<a href="<?php echo base_url('settings/edit_payment_type');?>/<?php echo $row['payment_source_id']; ?>" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
							
							<?php if( $row['status'] == 1  ){ ?>
							
							<a href="<?php echo base_url('settings/inactive_payment_type');?>/<?php echo $row['payment_source_id']; ?>" title="Inactive">
								<i class="fa fa-ban fa-1g red"></i>
							</a>
							
							<?php }elseif( $row['status'] == 0 ){ ?>

							<a href="<?php echo base_url('settings/active_payment_type');?>/<?php echo $row['payment_source_id']; ?>" title="Active">
								<i class="fa fa-check fa-1g green"></i>
							</a>
								
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			<?php else: ?>
				<tr>
					<td colspan="2" class="text-center">No Record Available</td>
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