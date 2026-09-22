<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a href="<?php echo base_url('push'); ?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Back To List'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<fieldset class='category-border-main'>
				<div class="row">
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Notification Information
							</legend>
							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">No</span>
									<input type="text" class="form-control" value="<?php echo $input['scheduler_id']; ?>" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Customer No</span>
									<input type="text" class="form-control" value="<?php echo $input['customer_no']; ?>" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Type</span>
									<input type="text" class="form-control" value="<?php echo $input['push_type']; ?>" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Source</span>
									<input type="text" class="form-control" value="<?php echo $input['source']; ?>" readonly>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Queued On</span>
									<input type="text" class="form-control" value="<?php echo $input['created_date']; ?>" readonly>
								</div>
								<?php if (!empty($input['dedupe_key'])) { ?>
									<div class="input-group">
										<span class="input-group-addon input_group">Dedupe Key</span>
										<input type="text" class="form-control" value="<?php echo $input['dedupe_key']; ?>" readonly>
									</div>
								<?php } ?>
							</div>
						</fieldset>
					</div>
					<div class="col-lg-6">
						<fieldset class='category-border'>
							<legend class="category-border">
								Message Preview
							</legend>
							<div class="col-lg-12">
								<textarea class="form-control" style="min-width: 100%; height: 120px; color: GREY;" readonly><?php echo $input['push_title'] . "\n" . $input['push_msg']; ?></textarea>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<fieldset class='category-border'>
							<legend class="category-border">
								Delivery
							</legend>
							<div class="col-lg-12">
								<div class="table-responsive">
									<table class="table table-striped table-hover">
										<thead>
											<tr>
												<th class="col-lg-2 text-left">Device</th>
												<th class="col-lg-1 text-center">Language</th>
												<th class="col-lg-1 text-center">Attempt</th>
												<th class="col-lg-1 text-center">Sent On</th>
												<th class="col-lg-1 text-center">Next Attempt On</th>
												<th class="col-lg-3 text-left">Text Delivered</th>
												<th class="col-lg-1 text-center">Status</th>
												<th class="col-lg-2 text-left">Error</th>
											</tr>
										</thead>
										<tbody>
											<?php if (!empty($input['deliveries'])): ?>
												<?php foreach ($input['deliveries'] as $delivery): ?>
													<tr>
														<td class="text-left">
															<?php echo $delivery['device_uuid']; ?>
															<?php if (!empty($delivery['platform'])): ?>
																(<?php echo $delivery['platform']; ?>)
															<?php endif; ?>
														</td>
														<td class="text-center"><?php echo ($delivery['lang'] == '') ? 'default' : $delivery['lang']; ?></td>
														<td class="text-center"><?php echo $delivery['push_attempt']; ?></td>
														<td class="text-center"><?php echo $delivery['push_sent_on']; ?></td>
														<td class="text-center"><?php echo $delivery['next_attempt_on']; ?></td>
														<td class="text-left">
															<?php if ($delivery['push_title'] == '') { ?>
																<small class="text-muted">Not rendered yet</small>
															<?php } else { ?>
																<?php echo $delivery['push_title']; ?><br>
																<small class="text-muted"><?php echo $delivery['push_msg']; ?></small>
															<?php } ?>
														</td>
														<td class="text-center">
															<?php echo $delivery['status']; ?>
														</td>
														<td class="text-left">
															<?php if (!empty($delivery['error_code'])) { ?>
																<?php echo $delivery['error_code']; ?><br>
																<small class="text-muted"><?php echo $delivery['error_msg']; ?></small>
															<?php } ?>
														</td>
													</tr>
												<?php endforeach; ?>
											<?php else: ?>
												<tr>
													<td colspan="7" class="text-center">No device registered when this was queued</td>
												</tr>
											<?php endif ?>
										</tbody>
									</table>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			<div class="col-md-12 button-group">
				<button id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.location='<?php echo base_url('push'); ?>';" <?php echo tooltip_helper('Back To List'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Back
				</button>
			</div>
		</div>
	</div>
	<div id="clear" style="clear:both;"></div>
</div>
<script src="<?php echo base_url("js/itelco/push.js?") . cssjs_ver(); ?>"></script>