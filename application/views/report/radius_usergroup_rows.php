<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-2">UserName</th>
				<th class="col-lg-2">GroupName</th>
				<th class="col-lg-2">Priority</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $rows AS $row ){ ?>						
			<tr>
				<td><?php echo $row['UserName']; ?></td>
				<td><?php echo $row['GroupName']; ?></td>
				<td><?php echo $row['priority']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>