<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-lg-1">Account No
					<i class="menu-icon fa 
					<?php 
					if ($order_by == 'ord_customer_no') {
						if ($order_type == 'desc') {
							echo 'fa-sort-desc';
						} else {
							echo 'fa-sort-asc';
						}
					} else {
						echo 'fa-sort';
					}
					?>
					" id="ord_customer_no" style="cursor: pointer;"></i>
				</th>
				<th class="col-lg-2">Customer Name
					<i class="menu-icon fa 
					<?php 
					if ($order_by == 'ord_name') {
						if ($order_type == 'desc') {
							echo 'fa-sort-desc';
						} else {
							echo 'fa-sort-asc';
						}
					} else {
						echo 'fa-sort';
					}
					?>
					" id="ord_name" style="cursor: pointer;"></i>
				</th>
				<th class="col-lg-2">Package</th>
				<th class="col-lg-1 text-center">Monthly</th>
				<th class="col-lg-1 text-center">Category</th>
				<th class="col-lg-1 text-center">Status</th>
				<?php if ($sel_status == 'P'): ?>
					<th class="col-lg-1 text-center">Installation Date</th>
				<?php else: ?>
					<th class="col-lg-1 text-center">Activation Date
						<i class="menu-icon fa 
						<?php 
						if ($order_by == 'ord_transact_date') {
							if ($order_type == 'desc') {
								echo 'fa-sort-desc';
							} else {
								echo 'fa-sort-asc';
							}
						} else {
							echo 'fa-sort';
						}
						?>
						" id="ord_transact_date" style="cursor: pointer;"></i>
					</th>
				<?php endif; ?>
				<th class="col-lg-1 text-right">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($row_data)): ?>
				{row_data}
					<tr class="customer_row" data-customer-no="{customer_no}" data-name="{name}">
						<td class="align-middle-important">{customer_no}</td>
						<td class="align-middle-important">{name}</td>
						<td class="align-middle-important">{package_name}</td>
						<td class="align-middle-important text-center">{monthly_charge}</td>
						<td class="align-middle-important text-center">{category}</td>
						<td class="align-middle-important text-center">{status_text}</td>
						<?php if ($sel_status == 'P'): ?>
							<td class="align-middle-important text-center">{preferred_installation}</td>
						<?php else: ?>
							<td class="align-middle-important text-center">{first_activate}</td>
						<?php endif; ?>
						<td class="align-middle-important text-right action_btn_td">
							<a href="<?php echo base_url('customer/edit_customer');?>/{customer_no}" title="Edit" class="px-1">
								<i class="fa <?php echo $view_only==1?'fa-search black':'fa-pencil'; ?> fa-1g"></i>
							</a>
							
							<a href="<?php echo base_url('customer/installation_form');?>/{customer_no}" title="Installation Order Form" class="px-1">
								<i class="fa fa-user fa-1g purple"></i>
							</a>

							<!--<a href="<?php echo base_url('customer/termination_form');?>/{customer_no}" title="Termination Form">
								<i class="fa fa-user fa-1g black"></i>
							</a>-->
							
							<!--<a class='{show_welcome_note}' target="_blank" href="<?php echo base_url('customer/welcome_note');?>/{customer_no}" title="Edit">
								<i class="fa fa-file fa-1g black"></i>
							</a>-->

							<a href="#" onclick="openNASModel('{customer_no}');" title="Disconnect NAS" class="px-1">
								<i class="fa fa-times red"></i>
							</a>
							
						</td>
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