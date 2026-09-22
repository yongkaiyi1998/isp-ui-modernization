<div>
	<table class="table-striped" style="width:100%">
		<thead>
			<tr>
				<th colspan="100%">
					<h3 class="text-center">
						<?php echo $page_title; ?> <br />
					</h3>
				</th>
			</tr>
			
			<tr>
				<th colspan="100%" style='text-align:center;'>
				<?php 
					foreach( $statistic AS $key => $stat ){
						
						switch( $key ){
							case 'P':
								$category = "Pending";
								break;
							case 'S':
								$category = "Sent";
								break;
							case 'F':
								$category = "Failed";
								break;
						}
						
						
						echo "<span style='margin-right:4em'>" . $category . " => " . $stat . "</span>";
						
					} 
				?>
				</th>
			</tr>
			
			
			
			
			<tr>
				<th >#</th>
				<th >Email Title</th>
				<th >Scheduled on</th>
				<th >Processed on</th>
				<th >Recipient</th>
				<th >Attempt</th>
				<th >Status</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $reports AS $key => $row ){ ?>
			<tr>
				<td  ><?php echo $key+1; ?></td>
				<td ><?php echo $row['email_title']; ?></td>
				<td ><?php echo $row['email_scheduled_on']; ?></td>
				<td ><?php echo $row['email_sent_on']; ?></td>
				<td >
					<?php 
					echo $row['email_to'] . ( isset($row['subscriber_number']) ? " - " . $row['subscriber_number'] : "" ) ; 
					?>
				</td>
				<td ><?php echo $row['email_attempt']; ?></td>
				<td >
					<?php 
						switch( $row['email_status'] ){
							case 'P':
								echo 'Pending';
								break;
							case 'S':
								echo 'Sent';
								break;
							case 'F':
								echo 'Failed';
								break;
							default:
								echo '-';
								
						}
					?>
				
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>