<div class="hidden-xs">
	<div class="row" style="padding: 20px;">
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th class="col-lg-4">Status</th>
					<th class="col-lg-3">Date</th>
					<th class="col-lg-3">Updated By</th>
				</tr>
			</thead>

			<tbody>
				<?php if (!empty($row_data)): ?>
					<?php foreach ($row_data as $row): ?>
						<tr>
							<td><?= $row['status_text'] ?? '' ?></td>
							<td><?= $row['transact_date'] ?? '' ?></td>
							<td><?= $row['display_name'] ?? '' ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="visible-xs">
	<?php if (!empty($row_data)): ?>

		<?php foreach (array_reverse($row_data) as $row): ?>

			<div style="margin-bottom:12px;background:#fff;border:1px solid #eee;border-radius:10px;overflow:hidden;font-size:14px;line-height:1.4;">
				
				<div style="padding:12px 14px; background: #fafafa;">
					<div style="font-size:15px; font-weight:600;">
						<?= $row['status_text'] ?? '' ?>
					</div>
				</div>

				<div style="padding:12px 14px;">
					<div style="margin-bottom:10px;">
						<div style="font-size:11px;">Date</div>
						<div style="font-size:13px;">
							<?= $row['transact_date'] ?? '' ?>
						</div>
					</div>

					<div>
						<div style="font-size:11px;">Updated By</div>
						<div style="font-size:13px;">
							<?= $row['display_name'] ?? 'System' ?>
						</div>
					</div>
				</div>

			</div>

		<?php endforeach; ?>

	<?php else: ?>

		<div style="padding:15px; text-align:center; font-size:14px;" class="grey">
			No termination history found
		</div>

	<?php endif; ?>
</div>