
	<div class="page" data-page="{data}" > 
			
			<div id="sel">
				<?php 
				$i = 1;
				echo'<table style="page-break-after:always" id="print_customer">';
				
				foreach ($rows as $row) {
						
					if($i % 2 == 1 ){

						echo '<tr style="height:50px;"></tr>';
						echo '<tr style="page-break-after:always;" id="cus">';
						echo '<td style="width:50%;min-height:300px ;border-width:2px; border-style:solid;padding-left:10px;">'.$row['customer_name'].
						'</br>'.$row['addr1'].
						'</br>'.$row['addr2'].
						'</br>'.$row['city'].
						'</br>'.$row['postcode'].
						' '.$row['state'].
						'</td>';
						echo '<td style="padding-left:50px;"></td>';

						$i++;

					
					
					}elseif($i % 2 == 0 ){

						echo '<td style="width:50%; border-width:2px;min-height:300px;border-style:solid;padding-left:10px">'.$row['customer_name'].
						'</br>'.$row['addr1'].
						'</br>'.$row['addr2'].
						'</br>'.$row['city'].
						'</br>'.$row['postcode'].
						' '.$row['state'].
						'</td>';
						echo '</tr>';

						$i = 1;
					
					}
				}

				echo'</table>';
			?>

			</div>
			

	
	</div>

<script src="<?php echo base_url("js/jquery.min.js?".cssjs_ver()); ?>" ></script>
