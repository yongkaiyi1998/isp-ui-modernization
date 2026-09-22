<div id="table_wrapper" class="table-responsive">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
				<th class="col-lg-2">UserName</th>
				<th class="col-lg-2">Attribute</th>
				<th class="col-lg-2">&nbsp;</th>
				<th class="col-lg-2">Value</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach( $rows AS $row ){ ?>						
			<tr>
				<td><?php echo $row['UserName']; ?></td>
				<td><?php echo $row['Attribute']; ?></td>
				<td><?php echo $row['op']; ?></td>
				<td><?php echo $row['Value']; ?></td>
			</tr>
		<?php } ?>
		</tbody>

		<tfoot>
			
		</tfoot>

	</table>
</div>