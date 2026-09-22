<div id="table_wrapper">
	<table class="table-striped" style="width:100%;">
		<thead>
			<tr>
				<th colspan="7">
					<h3 class="text-center">
						<?php echo $page_title; ?><br>
					</h3>
				</th>
			</tr>
			<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
				<th class="col-lg-4" <?php if ($isprint == 1) { echo 'style="width:25%;"'; } ?>>Name</th>
				<th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>IC.No</th>
				<th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Company</th>
				<th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Phone</th>
				<th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Email</th>
				<th class="text-center col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>Status</th>
				<th class="text-center col-lg-2" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Preferred Installation Date/Time</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($data_row as $key => $val) {
				echo "<tr>";
				echo "<td>".$val['name']."</td>";
				echo "<td>".$val['icno']."</td>";
				echo "<td>".$val['comp_name']."</td>";
				echo "<td>".$val['phone']."</td>";
				echo "<td>".$val['email']."</td>";
				echo "<td class='text-center'>".$val['status_text']."</td>";
				echo "<td class='text-center'>".$val['preferred_install_datetime']."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>