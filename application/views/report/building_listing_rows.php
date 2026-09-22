<div>
	<table>
		<tr>
			<th colspan="100%">
				<h3 class="text-center" style="color: black !important;">
					<?php echo $page_title; ?>
				</h3>
			</th>
		</tr>
	</table>
</div>

<div id="table_wrapper">
	<table class="table-striped" style="width:100%;">

		<thead>						
			<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
				<th class="col-lg-4">Name</th>
				<th class="col-lg-1 text-center">Total Units</th>
				
				<?php if( in_array( "p", $display_col ) == true ){ ?>
				<th class="col-lg-1 col-p text-center">Pending</th>
				<?php } ?>
				
				<?php if( in_array( "c", $display_col ) == true ){ ?>
				<th class="col-lg-1 col-c text-center">Cancelled</th>
				<?php } ?>

				<?php if( in_array( "a", $display_col ) == true ){ ?>
				<th class="col-lg-1 col-r text-center">Activated</th>
				<?php } ?>
				
				<?php if( in_array( "s", $display_col ) == true ){ ?>
				<th class="col-lg-1 col-s text-center">Suspended</th>
				<?php } ?>
				
				<?php if( in_array( "t", $display_col ) == true ){ ?>
				<th class="col-lg-1 col-t text-center">Terminated</th>
				<?php } ?>
				
				<th class="col-lg-1 text-center">Total Customers</th>
			</tr>
		</thead>

		<tbody>
			
			<?php 
				$grand_total_customer = 0 ;
				foreach( $row_data AS $val ){
					$row_total_customer = 0;
			?>
				<tr>
					<td><?php echo $val['name']; ?></td>
					<td class="text-center"><?php echo $val['total_unit']; ?></td>
					
					<?php if( in_array( "p", $display_col ) == true ){ ?>
					<td class="col-lg-1 col-r text-center"><?php echo $val['p']; ?></td>
					<?php 
							$row_total_customer += $val['p'];
						  } 
					?>
					
					<?php if( in_array( "c", $display_col ) == true ){ ?>
					<td class="col-lg-1 col-p text-center"><?php echo $val['c']; ?></td>
					<?php 
							$row_total_customer += $val['c'];
						  } 
					?>
					
					<?php if( in_array( "a", $display_col ) == true ){ ?>
					<td class="col-lg-1 col-c text-center"><?php echo $val['a']; ?></td>
					<?php 
							$row_total_customer += $val['a'];
						  } 
					?>
					
					<?php if( in_array( "s", $display_col ) == true ){ ?>
					<td class="col-lg-1 col-s text-center"><?php echo $val['s']; ?></td>
					<?php 
							$row_total_customer += $val['s'];
						  }
					?>
					
					<?php if( in_array( "t", $display_col ) == true ){ ?>
					<td class="col-lg-1 col-t text-center"><?php echo $val['t']; ?></td>
					<?php 
							$row_total_customer += $val['t'];
						  } 
					?>
					
					<td class="col-lg-1 text-center"><?php echo $row_total_customer; ?></td>
				</tr>
			<?php 
					$grand_total_customer += $row_total_customer;
				} 
			?>
			
		</tbody>

		<tfoot>
				<tr>
					<td></td>
					<td class="text-center">{grand_total_unit}</td>
					
					<?php if( in_array( "p", $display_col ) == true ){ ?>
					<td class="col-lg-1 text-center">{grand_p}</td>
					<?php } ?>
					
					<?php if( in_array( "c", $display_col ) == true ){ ?>
					<td class="col-lg-1 text-center">{grand_c}</td>
					<?php } ?>
					
					<?php if( in_array( "a", $display_col ) == true ){ ?>
					<td class="col-lg-1 text-center">{grand_a}</td>
					<?php } ?>
					
					<?php if( in_array( "s", $display_col ) == true ){ ?>
					<td class="col-lg-1 text-center">{grand_s}</td>
					<?php } ?>
					
					<?php if( in_array( "t", $display_col ) == true ){ ?>
					<td class="col-lg-1 text-center">{grand_t}</td>
					<?php } ?>
					
					<td class="col-lg-1 text-center"><?php echo $grand_total_customer; ?></td>
				</tr>
			
		</tfoot>

	</table>
</div>