<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-5 text-left">Message</th>
				<th class="col-lg-2 text-center">Last Modified</th>
				<th class="col-lg-2 text-center">Schedule On</th>
				<th class="col-lg-1 text-center">Message Type</th>
				<th class="col-lg-1 text-center">Status</th>
				<th class="col-lg-1 text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
			{row_data}
				<tr>
					<td class="text-left">{substring_preview}</td>
					<td class="text-center" >{modified_date}</td>
					<td class="text-center" >{msg_schedule_on}</td>
					<td class="text-center" >{type}</td>
					<td class="text-center" >{status}</td>
					<td class="text-center">
						<a href="<?php echo base_url('message_scheduler/add_message');?>/{scheduler_id}" title="Edit">
							<i class="fa fa-pencil fa-1g"></i>
						</a>
					</td>
				</tr>
			{/row_data}
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