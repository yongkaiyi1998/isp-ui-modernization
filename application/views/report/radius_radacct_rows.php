<div id="table_wrapper">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-2">User Name</th>
				<th class="col-lg-2">Login</th>
				<th class="col-lg-2">Logout</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $rows AS $row ){ ?>						
			<tr>
				<td><?php echo $row['username']; ?></td>
				<td><?php echo $row['acctstarttime']; ?></td>
				<td><?php echo $row['acctstoptime']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>