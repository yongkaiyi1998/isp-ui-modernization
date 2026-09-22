<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th >Title</th>
				<?php // <th class="col-lg-7 text-left">Message</th> ?>
				<th class="col-lg-3"class="text-right" >Last Modified</th>
				<th class="col-lg-3"class="text-right" >Schedule On</th>
				<th class="col-lg-1" class="text-right" >Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
			{row_data}
				<tr>
					<td class="text-left">{email_title}</td>
					<?php //<td class="text-left">{sms_msg}</td> ?>
					<td class="text-left" >{modified_date}</td>
					<td class="text-left" >{email_schedule_on}</td>
					<td class="text-center">
						<a href="<?php echo base_url('email/add_email');?>/{scheduler_id}" 
							title="Edit">
							<i class="fa fa-pencil fa-1g"></i>
						</a>
					</td>
				</tr>
			{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="4" class="text-center">No Record Available</td>
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