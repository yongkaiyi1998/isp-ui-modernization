<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title) ? $page_title : 'itelco'); ?>
					<?php if (check_acl('customer', 'M', false)) { ?>
						<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
							<a href="<?php echo base_url('user/user_detail/'); ?>"
								style="margin-left: 5px; margin-right: 5px;">
								<i class="ui-menu-icon fa fa-user-plus green"></i>
							</a>
						</span>
					<?php } ?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<!-- flash data -->
			<?php if ($this->session->flashdata('msg') != ''): ?>
				<div id="success" class="alert alert-success" role="alert"><?php echo $this->session->flashdata('msg'); ?>
				</div>
				<script> setTimeout(function () { $('#success').fadeOut(); }, 2000); </script>
			<?php endif; ?>
			<?php if ($this->session->flashdata('error_msg') != ''): ?>
				<div id="error_msg" class="alert alert-danger" role="alert">
					<?php echo $this->session->flashdata('error_msg'); ?>
				</div>
				<script> setTimeout(function () { $('#error_msg').fadeOut(); }, 10000); </script>
			<?php endif; ?>
			<?php if ($this->session->flashdata('warning_msg') != ''): ?>
				<div id="error_msg" class="alert alert-warning" role="alert">
					<?php echo $this->session->flashdata('warning_msg'); ?>
				</div>
				<script> setTimeout(function () { $('#error_msg').fadeOut(); }, 2000); </script>
			<?php endif; ?>
			<div class="bg-success filter-bar">
				<form id="user_filter" name="user" method="post" class="filter-form"
					action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">
					<span>Search: </span>
					<input type="text" id="txt_search" name="txt_search"
						value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder="Login ID / Name" />

					<span>Role: </span>
					<select id="sel_role" name="sel_role">
						<option value="all">All</option>
						<?php foreach ($sel_acl_role_list as $role): ?>
							<option value="<?php echo $role['role_no']; ?>" <?php echo ($sel_role == $role['role_no']) ? 'selected' : ''; ?>>
								<?php echo $role['name']; ?>
							</option>
						<?php endforeach; ?>
					</select>

					<?php
					$cfg_options = array(
						'admin_notification' => array('label' => 'Admin Notification', 'yes' => 'Enable', 'no' => 'Disable'),
						'tech_notification' => array('label' => 'Tech Notification', 'yes' => 'Enable', 'no' => 'Disable'),
						'customer_support_assign' => array('label' => 'Customer Support Assign', 'yes' => 'Enable', 'no' => "Disable"),
						'trouble_ticket_assign' => array('label' => 'Trouble Ticket Assign', 'yes' => 'Enable', 'no' => "Disable"),
						'mb_lvl1_approver' => array('label' => 'MB Level 1 Approver', 'yes' => 'Enable', 'no' => "Disable"),
						'mb_lvl2_approver' => array('label' => 'MB Level 2 Approver', 'yes' => 'Enable', 'no' => "Disable"),
						'ba_lvl1_approver' => array('label' => 'BA Level 1 Approver', 'yes' => 'Enable', 'no' => "Disable"),
						'ba_lvl2_approver' => array('label' => 'BA Level 2 Approver', 'yes' => 'Enable', 'no' => "Disable"),
					);

					$cfg_filter = isset($cfg_filter) && is_array($cfg_filter) ? $cfg_filter : array();

					$cfg_selected_count = 0;
					foreach ($cfg_options as $key => $opt) {
						$val = isset($cfg_filter[$key]) ? $cfg_filter[$key] : 'all';
						if ($val == 'yes' || $val == 'no')
							$cfg_selected_count++;
					}

					$cfg_label = 'All';
					if ($cfg_selected_count >= 1)
						$cfg_label = 'Config (' . $cfg_selected_count . ' selected)';
					?>
					<span>Config: </span>
					<div id="cfg_dropdown" class="btn-group">
						<button type="button" class="cfg-filter-toggle dropdown-toggle" id="btnCfgFilter"
							data-toggle="dropdown">
							<span id="cfg_filter_label"
								style="display:inline-block; min-width:106px; text-align:left;"><?php echo $cfg_label; ?></span>
							<i class="fa fa-chevron-down" style="font-size:10px;"></i>
						</button>
						<ul class="dropdown-menu" id="cfgFilterMenu">
							<?php foreach ($cfg_options as $key => $opt): ?>
								<?php $val = isset($cfg_filter[$key]) ? $cfg_filter[$key] : 'all'; ?>
								<li>
									<label
										style="display:flex; align-items:center; gap:10px; padding:4px 20px; font-weight:normal; white-space:nowrap;">
										<span style="min-width:170px;"><?php echo $opt['label']; ?></span>
										<select class="cfg-attr-select" name="cfg_<?php echo $key; ?>">
											<option value="all" <?php echo ($val == 'all' ? 'selected' : ''); ?>>All</option>
											<option value="yes" <?php echo ($val == 'yes' ? 'selected' : ''); ?>>
												<?php echo $opt['yes']; ?>
											</option>
											<option value="no" <?php echo ($val == 'no' ? 'selected' : ''); ?>>
												<?php echo $opt['no']; ?>
											</option>
										</select>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<span class="mx-1"></span>
					<button type="button" class="btn btn-success" onclick="ajax_filter(1)">
						<i class="fa fa-filter"></i>
						Filter
					</button>
					<button type="button" class="btn btn-info" onclick="ajax_clear()">
						<i class="fa fa-times"></i>
						Clear
					</button>
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="col-lg-0.5 text-center" style="width:50px;">
								<label class="py-0">
									<input type="checkbox" id="check-all"
										class="align-middle mb-n1 border-2 text-dark-m3" />
								</label>
							</th>
							<th class="col-lg-1">Login ID</th>
							<th class="col-lg-4.5">Name</th>
							<th class="col-lg-1 text-center">Role</th>
							<th class="col-lg-1 text-center">Status</th>
							<th class="col-lg-3 text-center">Last Login</th>
							<th class="col-lg-1 text-right">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($row_data)): ?>
							<?php foreach ($row_data as $entry): ?>
								<tr>
									<td class="text-center pr-0 pos-rel">
										<div class="position-tl h-100 ml-n1px border-l-4 brc-orange-m1 v-hover">
											<!-- border shown on hover -->
										</div>
										<div class="position-tl h-100 ml-n1px border-l-4 brc-success-m1 v-active">
											<!-- border shown when row is selected -->
										</div>
										<label>
											<input type="checkbox" class="align-middle user-checkbox"
												value="<?php echo $entry['username']; ?>" />
										</label>
									</td>
									<td>
										<?php echo $entry['username']; ?>
									</td>
									<td>
										<?php echo $entry['display_name']; ?>
									</td>
									<td class=" text-center">
										<?php echo $entry['role_name']; ?>
									</td>
									<td class="text-center"><i
											class="fa <?php echo ($entry['active'] ? 'fa-check green' : 'fa-ban orange'); ?> fa-1g"></i>
									</td>
									<td class="text-center">
										<?php echo $entry['last_login']; ?>
									</td>
									<td class="text-right">
										<a href="<?php echo base_url('user/user_detail/' . $entry['username']); ?>"
											title="Edit">
											<!-- <a href="#" title="Edit" onclick="show_user_popup('user/user_detail/','<?php echo urlencode($entry['username']); ?>');"> -->
											<i class="fa fa-pencil fa-1g"></i>
										</a>
										<?php if (check_acl('user', 'D', false)) { ?>
											<a href="<?php echo base_url('user/delete_user/' . $entry['username']); ?>"
												title="Delete" onclick="return confirm('Delete this user?');">
												<i class="fa fa-trash fa-1g red"></i>
											</a>
										<?php } ?>
										<a href="<?php echo base_url('user/set_status/' . $entry['username']); ?>"
											title="Status">
											<i
												class="fa <?php echo ($entry['active'] ? 'fa-ban orange' : 'fa-check green'); ?> fa-1g"></i>
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
				<div class="mb-2">
					<div class="btn-group">

						<button class="btn btn-primary dropdown-toggle" id="btnBulkAction" data-toggle="dropdown"
							disabled>Bulk Action</button>
						<ul class="dropdown-menu">
							<li>
								<a href="#" class="bulk-action" data-action="assign">Assign</a>
							</li>
							<li>
								<a href="#" class="bulk-action" data-action="activate">Activate</a>
							</li>
							<li>
								<a href="#" class="bulk-action" data-action="deactivate">Deactivate</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div>
				<div class="col-md-12 text-center">
					<?php echo $pagination; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="clear" style="clear:both;"></div>
<div id="popupDetail">
	<div id="popupDetailStd">
		<div id="popupContent">
			&nbsp;
		</div>
	</div>
</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>

<div id="edit_container" class="boxFrame" style="display:none;"></div>

<?php $this->load->view('user/modal/bulk_edit_modal'); ?>

<script src="<?php echo base_url("js/itelco/user.js?" . cssjs_ver()); ?>"></script>