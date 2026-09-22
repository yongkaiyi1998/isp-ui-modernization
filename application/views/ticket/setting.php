<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">

			<h4>{page_title}</h4>
		</div>
		
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<!-- flash data -->
			
			<form action="{form_action}" method="post" accept-charset="utf-8" autocomplete="off">	
				<div class="col-lg-12">
					<div class="row col-lg-12" style="margin-bottom: 2.5rem; width: 100%; overflow-x: auto;">
						<h4>Service Ticket/Trouble Ticket Main PIC</h4>
						<table class="table table-responsive table-borderless table-striped" id="ticket_pic">
							<thead>
								<th class="col-xs-5 text-center">
									PIC
								</th>
								<th class="col-xs-2 text-center">
									Email
								</th>
								<th class="col-xs-2 text-center">
									Contact
								</th>
								<th class="col-xs-2 text-center">
									Telegram
								</th>
								<th class="col-xs-1">
									<i id="add_new_pic" style="cursor:pointer;"
										class="ace-icon fa fa-plus white tooltip-event" 
										title="Add New Records"> Click to add
									</i>
								</th>
							</thead>
							<tbody>
								<?php foreach( $tt_pic AS $row ) { ?>
									<tr class="text-center">
										<td>
											<input type="text" class="col-sm-12" name="pic_name[]" value="<?php echo $row['tt_pic']; ?>" required />
										</td>
										<td>
											<input type="text" class="col-sm-12" name="pic_email[]"  value="<?php echo $row['tt_email']; ?>" />
										</td>
										<td>
											<input type="text" class="col-sm-12" name="pic_contact[]" value="<?php echo $row['tt_contact']; ?>" />
										</td>
										<td>
											<input type="text" class="col-sm-12" name="pic_telegram[]"  value="<?php echo $row['tt_telegram']; ?>" />
										</td>
										<td>
											<input type="hidden" class="col-sm-12" name="tt_setting_id[]" value="<?php echo $row['tt_setting_id']; ?>" data-pic_name="<?php echo $row['tt_pic']; ?>" />
											<input type="hidden" class="col-sm-12" name="pic_id[]" value="<?php echo $row['tt_user_id'] ?>"/>
											<i class="fa fa-trash red tooltip-event delete_pic" style="cursor:pointer" title="Delete"></i>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>					
				</div>
				<div class="col-lg-12">&nbsp;</div>
				<div class="col-lg-12">
				<div>
					<button name="cancel" type="button" class="btn btn-danger" id="cancel" value="cancel" onclick="window.location='<?php echo base_url('ticket/setting'); ?>'" title="Discard Changes"> <i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel </button>
					&nbsp;
					<button name="save" type="submit" class="btn btn-success" id="save" value="save" title="Save record"><i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save</button></div>					
				</div>
			</form>

		</div>
		
	</div>
</div>

<script>
	var user_list = <?php echo $user_list; ?>;
</script>
