<style>
@media print{@page {size: landscape}}
</style>

<div class="container">
	<div class="panel-default">
		<div class="panel-heading noprint panel_fontsize hidden-print">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('report');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<div class="bg-success filter-bar hidden-print">
				<?php echo flash_data_helper($msg); ?>
				<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="is_postback" name="is_postback" value=true>
					
					<span>From </span>
					<input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off" value="<?php echo $date_from; ?>" />
					
					<span>To </span>
					<input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
						value="<?php echo $date_to; ?>" />
					
					<span>Title  </span>
					<input style='width:300px' id="sms_title" name="sms_title" placeholder="" value="<?php echo $sms_title; ?>" />
					

					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
				</form>
			</div>

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
										case 'N':
											$category = "No Action";
											break;
										case 'P':
											$category = "Pending";
											break;
										case 'S':
											$category = "Sent";
											break;
										case 'F':
											$category = "Failed";
											break;
										case 'C':
											$category = "Cancelled";
											break;
									}
									
									echo "<span style='margin-right:4em'>" . $category . " => " . $stat . "</span>";
									
								} 
							?>
							</th>
						</tr>
						
						
						
						
						<tr>
							<th >#</th>
							<th >SMS Title</th>
							<th >Scheduled on</th>
							<th >Processed on</th>
							<th >Send By</th>
							<th >Recipient</th>
							<th >Attempt</th>
							<th >Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach( $reports AS $key => $row ){ ?>
						<tr>
							<td  ><?php echo $key+1; ?></td>
							<td ><?php echo $row['sms_title']; ?></td>
							<td ><?php echo $row['scheduled_time']; ?></td>
							<td ><?php echo $row['sms_processed']; ?></td>
							<td ><?php echo $row['sms_transport']; ?></td>
							<td >
								<?php 
								echo $row['sms_phone'] . ( $row['subscriber_number'] != "" ? " - " . $row['subscriber_number'] : "" ) ; 
								?>
							</td>
							<td ><?php echo $row['sms_attempt']; ?></td>
							<td >
								<?php 
									switch( $row['sms_status'] ){
										case 'N':
											echo 'No Action';
											break;
										case 'P':
											echo 'Pending';
											break;
										case 'S':
											echo 'Sent';
											break;
										case 'F':
											echo 'Failed';
											break;
										case 'C':
											echo 'Cancelled';
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
			
		</div>
	</div>
</div>

<script>
$("#date_from").datepicker({format: 'yyyy-mm-dd'});
$("#date_to").datepicker({format: 'yyyy-mm-dd'});

$( "#sms_title" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "sms_scheduler/autocomplete_load_sms",
			dataType: "json",
			data: { 
					keyword		: request.term ,
					date_from  	: $('#date_from').val(),
					date_to		: $('#date_to').val()
				  },
			success: function( data ) {
				console.log(data);
				var transformed = $.map(data, function (el) {
					return {
							label		: el.sms_title,
							id			: el.scheduler_id,
							val			: el
					};
				});					
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(xhr);
				console.log(ajaxOptions);
				console.log(thrownError);
				
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {			
		console.log(ui.item.val);
		
	}
	
});





</script>
