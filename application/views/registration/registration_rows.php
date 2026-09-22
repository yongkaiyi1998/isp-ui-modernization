<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1">Reg No</th>
				<th class="col-lg-3">Name</th>
				<!--<th class="col-lg-1">IC No.</th>-->
				<th class="col-lg-1">Phone No.</th>
				<!--<th class="col-lg-2">Email</th>-->
				<th class="col-lg-1 text-center">Status</th>
				<!--<th class="col-lg-2 text-center">Source</th>-->
				<th class="col-lg-2 text-center">Building</th>
				<th class="col-lg-2 text-center">Unit No.</th>
				<th class="col-lg-2 text-center">Installation Date</th>
				<th class="col-lg-1 text-right">Actions</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr class="customer_row" data-customer-no="{id}" data-name="{name}">
						<td class="align-middle-important text-left">{reg_no}</td>
						<td class="align-middle-important">{name}</td>
						<!--<td class="align-middle-important">{icno}</td>-->
						<td class="align-middle-important">{phone}</td>
						<!--<td class="align-middle-important">{email}</td>-->
						<td class="align-middle-important text-center">{status_text}</td>
						<!--<td class="align-middle-important text-center">{source_text}</td>-->
						<td class="align-middle-important text-left">{building_name}</td>
						<td class="align-middle-important text-left">{ins_unit_no}</td>
						<td class="align-middle-important text-center">{preferred_installation}</td>
						<td class="align-middle-important text-right">
							<a href="<?php echo base_url('registration/add_registration');?>/{id}" title="Edit">
								<i class="fa <?php echo $view_only==1?'fa-search fa-1g black':'fa-pencil fa-1g'; ?> "></i>
							</a>
							
						</td>
						<td></td>
					</tr>
				{/row_data}
			<?php else: ?>
				<tr>
					<td colspan="7" class="text-center">No Record Available</td>
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