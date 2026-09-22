<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-2">NAS IP</th>
				<th class="col-lg-4">Short Name</th>
				<th class="col-lg-1">Type</th>
				<th class="col-lg-1">Port</th>
				<th class="col-lg-1">Secret</th>
				<th class="col-lg-2 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				<?php foreach( $row_data AS $row ){ ?>
					<tr>
						<td><?php echo $row['nasname']; ?></td>
						<td><?php echo $row['shortname']; ?></td>
						<td><?php echo $row['type']; ?></td>
						<td><?php echo $row['ports']; ?></td>
						<td><?php echo $row['secret']; ?></td>
						<td class="text-right">
							<a href="<?php echo base_url('settings/edit_nas_address');?>/<?php echo $row['id']; ?>" title="Edit">
								<i class="fa fa-pencil fa-1g"></i>
							</a>
						</td>
					</tr>
				<?php } ?>
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