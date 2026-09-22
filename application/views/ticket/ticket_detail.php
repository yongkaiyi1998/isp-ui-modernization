<script src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url('js/autosize.js?'.cssjs_ver()); ?>"></script>

<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('ticket');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
				<form id="ticket_detail" name="ticket_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
				<input id="temp_id" name="temp_id" type="hidden"
					value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />

				<fieldset class='category-border-main owner-form'>	
					<div class="category-border-main bg-success text-center" >
						Service Ticket
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">ST #</span>
									<input type="text" class="form-control" id="tt_no" name="tt_no" 
									value="<?php echo set_value( 'tt_no', $input['tt_no'] ); ?>" placeholder="ST #" READONLY />
									<input type="hidden" id="tt_id" name="tt_id" value="<?php echo $input['tt_id']; ?>" />
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Parent ST #</span>
									<span class="input-icon input-icon-right" style='display:inline;'>
										<input  type="text" class="form-control" id="parent_tt_no" name="parent_tt_no" 
												value="<?php echo $input['parent_tt_no']; ?>" placeholder="ST #" />
										<i class="ace-icon fa fa-times red" id="clear_parent_tt" style="top:-2px;cursor:pointer;"></i>
									</span>
									<input type="hidden" id="parent_tt_id" name="parent_tt_id" value="<?php echo $input['parent_tt_id']; ?>" />		
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">

							</div>
							
							<div class="col-lg-6">
								<div class="input-group"></div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								
								<div class="input-group">
									<span class="input-group-addon input_group">Customer<span class="red">*</span></span>

									<span class="input-icon input-icon-right" style='display:inline;'>
										<input type="text" class="form-control" id="customer_no_name" 
											name="customer_no_name" placeholder="By Customer Number"
											value="<?php echo set_value('customer_no_name', $input['customer_no_name']); ?>" />
												
										<i class="ace-icon fa fa-times red" id="clear_customer" style="top:-2px;cursor:pointer;"></i>
									</span>
											
									<input type="hidden" class="form-control" id="customer_no" name="customer_no" 
											value="<?php echo set_value( 'customer_no', $input['customer_no'] ); ?>" placeholder="Customer Number" />
								</div>
								
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Account Category</span>
									<input type="text" class="form-control" id="account_category" 
											value="<?php echo $input['customer_category_name'] ?? ''; ?>" disabled 
											style="background-color:#fff; cursor:default;" />
									<input type="hidden" id="customer_category" name="customer_category" 
											value="<?php echo set_value( 'customer_category', $input['customer_category'] ?? '' ); ?>" />
								</div>
							</div>
							
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								
								<div class="input-group">
									<span class="input-group-addon input_group">Account PIC<span class="red">*</span></span>

									<span class="input-icon input-icon-right" style='display:inline;'>
										<select name="pic_name" id="pic_name" class="col-lg-12" style="width: 100%;">
											<option>-- SELECT --</option>
										</select>
									</span>
									
								</div>

							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Contact<span class="red">*</span></span>
									<input type="text" class="form-control" id="contact_no" name="contact_no" 
											value="<?php echo set_value( 'contact_no', $input['contact_no'] ); ?>" placeholder="Contact Number" />
								</div>
							</div>
						</div>
					</div>
					
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Category </span>

									<span class="input-icon input-icon-right" style='display:inline;'>
										<select class="col-lg-12" id="tt_category" name="tt_category" style="width: 100%;">
										<option value="0" > -- SELECT -- </option>
										<?php 
											foreach( $sel_product_category_list AS $val ){
												$selected = ($input['tt_category'] ?? 0) == $val['product_code'] ? true : false ;
												
												echo "<option ".(set_select('tt_category',$val['product_code'],$selected))." value='".$val['product_code']."'>".$val['product_name']."</option>";
											}
										?>
										</select>
												
										
									</span>

								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Prepared By </span>
									<span class="input-icon input-icon-right" style='display:inline;'>
									<input type="text" name="created_by_name" id="created_by_name" class="form-control" value="<?php echo $input['created_by_name']; ?>" READONLY />
									<input type="hidden" name="created_by" id="created_by" class="form-control" value="<?php echo $input['created_by']; ?>" READONLY />
									</span>
								</div>
							</div>
							
						</div>
					</div>
					
					<div class="col-lg-12">&nbsp;</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Date Open</span>
									<input type="text" class="form-control" id="datetime_open" name="datetime_open" 
										   value="<?php echo set_value( 'datetime_open', $input['datetime_open'] ); ?>"  autocomplete="off"/>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Status</span>
									<select class="col-lg-12" id="status" name="status" style="width: 100%;">
										<?php if ($mode == 'owner') { ?>
										<option <?php echo set_select( 'status', '1', ( $input['tt_status'] == 1 || $input['tt_status'] == '' ) ? true : false ); ?> value='1' >OPEN</option>
										<?php } ?>
										<option <?php echo set_select( 'status', '2', $input['tt_status'] == 2 ? true : false ); ?> value='2' >ASSIGNED</option>
										<option <?php echo set_select( 'status', '3', $input['tt_status'] == 3 ? true : false ); ?> value='3' >IN PROGRESS</option>
										<option <?php echo set_select( 'status', '4', $input['tt_status'] == 4 ? true : false ); ?> value='4' >SOLVED</option>
										<option <?php echo set_select( 'status', '5', $input['tt_status'] == 5 ? true : false ); ?> value='5' >NOT RELATED</option>
										<?php if ($mode == 'owner' || $input['tt_status'] == 0) { ?>
										<option <?php echo set_select( 'status', '0', $input['tt_status'] == 0 ? true : false ); ?> value='0' >CLOSE</option>
										<?php } ?>
									</select>
									<input type="hidden" class="form-control" id="prev_status" name="prev_status" 
											value="<?php echo set_value( 'prev_status', $input['prev_status'] ); ?>" />
								</div>
							</div>
							
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Date Closed</span>
									<input type="text" class="form-control" id="datetime_close" name="datetime_close" 
										   value="<?php echo set_value( 'datetime_close', $input['datetime_close'] ); ?>" autocomplete="off"/>
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Duration</span>
									<input type="text" class="form-control" id="duration" name="duration" 
										   value="<?php echo set_value( 'duration', $input['tt_duration'] ); ?>" 
										   READONLY />
								</div>
							</div>
							
						</div>
					</div>
				</fieldset>
				<fieldset class='category-border-main owner-form'>	
					<div class="category-border-main bg-success text-center" >
						COMPLAINT / PROBLEM
					</div>
					<div class="col-lg-6">
						<div class="row">
							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">Complaint</span>
									<select class="col-lg-12" id="tt_complaint_id" name="tt_complaint_id" style="width: 100%;">
										<option value="">-- SELECT -- </option>
										<?php 
											foreach( $sel_complaint AS $val ){
												$selected = $input['tt_complaint_id'] == $val['tt_complaint_id'] ? true : false ;
												
												echo "<option ".(set_select('tt_complaint_id',$val['tt_complaint_id'],$selected))." value='".$val['tt_complaint_id']."'>".$val['tt_complaint_name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
							
							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">SOF</span>
									<select class="col-lg-12" id="tt_sof_id" name="tt_sof_id" style="width: 100%;">
										<option value="">-- SELECT -- </option>
										<?php 
											foreach( $sel_sof AS $val ){
												$selected = $input['tt_sof_id'] == $val['tt_sof_id'] ? true : false ;
												
												echo "<option ".(set_select('tt_sof_id',$val['tt_sof_id'],$selected))." value='".$val['tt_sof_id']."' >".$val['tt_sof_name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
							
							<div class="col-lg-12">
								<div class="input-group">
									<span class="input-group-addon input_group">COF</span>
									<select class="col-lg-12" id="tt_cof_id" name="tt_cof_id" style="width: 100%;">
										<option value="">-- SELECT -- </option>
										<?php 
											foreach( $sel_cof AS $val ){
												$selected = $input['tt_cof_id'] == $val['tt_cof_id'] ? true : false ;
												
												echo "<option ".(set_select('tt_cof_id',$val['tt_cof_id'],$selected))." value='".$val['tt_cof_id']."'>".$val['tt_cof_name']."</option>";
											}
										?>
									</select>
								</div>
							</div>
							
						</div>
					</div>
					
					<?php if ($full_access) { ?>
						<div class="col-lg-6">
							<div class="row">
								<div class="col-lg-12">
									<div class="input-group">
										<span class="input-group-addon input_group">Assign to <br /><br />CTRL + Left Click<br /> for multiple choice</span>
										<select class="col-lg-12" id="tt_assign_to" name="tt_assign_to[]" multiple="" style="width: 100%;">
											<?php 
											foreach( $sel_user AS $val ){
												
												if(  in_array( $val['idx'] , $input['tt_assign_to'] ) )
													$selected = true ;
												else
													$selected = false; 
												
												echo "<option ".(set_select('tt_assign_to',$val['idx'],$selected))." value='".$val['idx']."'>".$val['display_name']."</option>";
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
					<?php }else{ 
						if (!empty($input['tt_assign_to'])) {
							foreach ($input['tt_assign_to'] as $user_id) {
								echo '<input type="hidden" name="tt_assign_to[]" value="' . htmlspecialchars($user_id) . '">';
							}
						}
					 } ?>
					
					<div class="col-lg-12">&nbsp;</div>
										
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Customer Remark</span>
									<textarea class="form-control" name="tt_remark" style='width:100%;' rows=4><?php echo $input['tt_remark']; ?></textarea>
								</div>
							</div>
							
						<?php if ($full_access) { ?>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Email to<br /><br />separates with ;</span>
									<textarea class="form-control" name="tt_email" id="tt_email" style='width:100%;' rows=4 placeholder=""><?php echo $input['tt_email']; ?></textarea>
								</div>
							</div>
						<?php } ?>
							
						</div>
					</div>
					
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6"></div>
						
							<?php if( $input['tt_id'] != '' && $full_access){ ?>
								<div class="col-lg-6">
									<div class="input-group">
										<input type="checkbox" value=1 name="resend_email" id="resend_email" /> Resend email?
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</fieldset>

				<?php if(!empty($input['tt_id'])) { ?>
					<fieldset class="category-border-main">
						<div class="category-border-main bg-success text-center">
							Reply To Customer
						</div>
						<div class="col-lg-12">
							<textarea id="customer_reply" class="form-control" rows="6" placeholder="Write your reply that want to send to customer..."></textarea>
						</div>
						<div class="col-lg-12 text-right" style="margin-top: 20px">
							<span><img id='sending-loading-icon' style="display: none;" src='<?php echo base_url('images/loading.gif'); ?>' /></span>
							<button id="btSendToCustomer" name="btSendToCustomer" type="button" class="btn btn-warning" <?php echo tooltip_helper('Click To Send To Customer'); ?> <?php echo check_acl_btn('trouble_ticket','M');?> onclick="send_reply_to_customer()">
								<i class="menu-icon fa fa-send white" data-toggle="tooltip" title=""></i>Send
							</button>						
						</div>
					</fieldset>
				<?php } ?>

				<fieldset class='category-border-main'>
					<div class="category-border-main bg-success text-center">
						Attachments
					</div>

					<div class="form-group col-lg-12 col-xs-12" style="z-index:100;">

						<div class="col-xs-12 col-xs-12">Attach Supporting Documents</div>
						
						<div class="col-lg-3 col-xs-12">
							<input multiple="multiple" type="file" id="attach_file_input" data-preview-file-type="text"/>
						</div>
				
						<div class="col-lg-9 col-xs-12" id="file-container">
						<?php 
						if( !empty( $attachment ) ){
							foreach( $attachment AS $row => $res ){
						?>
							<div class="col-lg-3 col-xs-12 attach-file" style="margin-bottom: 10px; " data-id="<?php echo $row; ?>">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="<?php echo $row; ?>" data-local-path="<?php echo $res['local_path']; ?>" data-file-type="<?php echo $res['file_type']; ?>" data-extension="<?php echo $res['extension']; ?>" data-saved="1" onclick="view_attach_doc(<?php echo $row; ?>);">
										<div class="icon-image">
											<?php $attachment_text_title = (empty($res['remark']) ? $res['file_name'] : $res['remark']); ?>
											<?php if ($res['file_type'] == 'image'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php elseif ($res['file_type'] == 'video'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-film"></i>
											<?php elseif ($res['file_type'] == 'doc'): ?>
												<?php if (empty($attachment_text_title)) { $attachment_text_title = $res['file_name']; } ?>
												<i class="fa fa-file"></i>
											<?php elseif ($res['file_type'] == 'pdf'): ?>
												<img class="thumbnail-display" src="<?php echo $res['thumbnail_path']; ?>">
											<?php endif ?>
										</div>
									</div>

									<div class="file-remark" title="<?php echo $attachment_text_title; ?>"><a style="cursor:pointer;" data-existing="1" data-existing_id="<?php echo $res['file_id']; ?>" data-id="<?php echo $row; ?>" data-file-name="<?php echo $res['file_name']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>"  onclick="change_remark(this);"><?php echo $attachment_text_title; ?></a></div>

									<div class="text-right" style="min-height:20px;">
										<!--only for existing attachments-->
										<?php if ($res['is_temp'] == '0') { ?>
										<input type="hidden" id="existing_attach" name="existing_attach[<?php echo $row; ?>]" value="<?php echo $res['file_id']; ?>" />
										<?php } ?>
										<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol_attach_<?php echo $row; ?>" name="loading_symbol_attach_<?php echo $row; ?>" style="display:none;"/>

										&nbsp;&nbsp;&nbsp;<a style="color: red; cursor: pointer;font-size:16px;" data-file-id="<?php echo $res['file_id']; ?>" data-is-temp="<?php echo $res['is_temp']; ?>" data-id="<?php echo $row; ?>" class="att_remove_btn" onclick="removeFile(this)">
											<i class="fa fa-trash" style="cursor:pointer;"></i>
										</a>
									</div>
								</div>
							</div>

							<input type="file" name="attach_attachment[<?php echo $row; ?>]" class="hide" id="attach_attachment_<?php echo $row; ?>" value="<?php echo $res['local_path']; ?>">

							<input type="hidden" name="attach_attachment_remark[<?php echo $row; ?>]" value="<?php echo $res['remark']; ?>">
						<?php
							}
						}
						?>
						
						</div>

						<?php include_once( APPPATH . 'views/templates/modal_html_attachment_remark.php'); ?>
					</div>

				</fieldset>
				
				<?php if( $input['tt_id'] != '' ){ ?>
				
					<fieldset class='category-border-main assignee-form'>
						<div class="category-border-main bg-success text-center" >
							JOB TRACKING AND COMMENT
						</div>
						
						<div class="col-lg-12">


						<div class="widget-box widget-color-blue2">

							<table id="job_tracking" class="table table-striped" style="margin-bottom: 0;">
								<thead>
									<tr>
										<th class="col-xs-1 text-left">
											<button type="button" id="toggle_tracking_rows" class="btn btn-xs btn-outline-secondary" style="margin-left: 10px;">
												<i class="fa fa-chevron-down"></i>
											</button>
											#
										</th>
										<th class="col-xs-3">Date</th>
										<th class="col-xs-12">
											Remark and Comment
										</th>
										<th class="col-xs-2 text-center">
											<span id="add_job_tracking" style="cursor:pointer;" class="tooltip-event" title="Add New Records">
												<i class="ace-icon fa fa-plus white"></i>
												<span class="label-text"> Click to add new record</span>
											</span>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($job_tracking as $idx => $row): 
										$is_hidden = $idx < count($job_tracking) - 5 ? 'tracking-row-hidden' : ''; ?>
										<tr class="<?php echo $is_hidden; ?>">
											<td class="text-center" style="vertical-align: middle;">
												<?php echo $idx + 1 ?>
												<input type="hidden" name="rec_id[]" value="<?php echo $row['tt_job_id']; ?>" />
											</td>
											<td style="vertical-align: middle;">
												<?php echo $row['tt_date']; ?> - <?php echo $row['display_name']; ?>
												<input name="date[]" type="hidden" value="<?php echo $row['tt_date']; ?>" />
											</td>
											<td style="vertical-align: middle;">
												<textarea name="remark[]" style="display:none;" class="form-control"><?php echo $row['tt_remark']; ?></textarea>
												<span><?php echo nl2br(htmlspecialchars($row['tt_remark'])); ?></span>
											</td>
											<td class="text-center" style="vertical-align: middle;">
												<i class="ace-icon fa fa-trash red tooltip-event rec_del" style="padding-right:10px;cursor:pointer" title="Delete"></i>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>

						</div>					
					</fieldset>

					<div class="new-comment-ui" style="padding-left: 3rem; padding-right: 3rem; display: none;">
						<input type="hidden" id="new_comment_rec_id" value="" disabled>
						<input type="hidden" id="new_comment_date" value="" disabled>
						<input type="hidden" id="reply_to" value="" disabled>
						<div class="row">
						<div class="col-md-4 col-xs-12">
							<div class="form-group">
							<label for="reply_to_select" style="font-weight:bold;">Reply To</label>
							<div class="input-group">
								<select id="reply_to_select" class="form-control" style="width: 100%;">
								<option value="all">All</option>
								<?php 
									foreach ($sel_reply as $key => $reply) {
									echo "<option value=\"{$key}\">{$reply}</option>";
									}
								?>
								</select>
								<span class="input-group-btn">
								<button type="button" class="btn btn-primary" id="add-reply-to">
									<span class="glyphicon glyphicon-plus"></span>
								</button>
								</span>
							</div>
							</div>
							<div id="selected-users" style="margin-top:10px;"></div>
						</div>
						<div class="col-md-8 col-xs-12">
							<div class="form-group">
							<label for="new_comment" style="font-weight:bold;">Comment</label>
							<textarea id="new_comment" class="form-control" rows="6" placeholder="Write your comment..."></textarea>
							</div>
						</div>
						</div>
					</div>
				<?php } ?>
				
				<div class="col-lg-6 owner-form button-group" style="padding-top:15px;">
					
					<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
					</button>
					<button id="btSave" name="btSave" type="button" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('trouble_ticket','M');?>>
						<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
					</button>
					<?php if (!empty($input['tt_id'])) { ?>
						<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo check_acl_btn('trouble_ticket','D');?>>
							<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
						</button>
					<?php } ?>
		
					<span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>
				</div>
			</form>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>

</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>
<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>
<script src="<?php echo base_url("js/itelco/trouble_ticket.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/datepicker/moment.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/ace/elements.fileinput.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>

<script>
	var mode = '<?php echo $mode; ?>';
	var pic_name = '<?php echo isset( $input['pic_name'] ) ? $input['pic_name'] : ''  ; ?>';
</script>