<script src="<?php echo base_url("js/datepicker/bootstrap-datetimepicker.min.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/autosize.js?".cssjs_ver()); ?>"></script>

<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('customer_support');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="cs_detail" name="cs_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
				<input id="temp_id" name="temp_id" type="hidden"
				value="<?php echo set_value('temp_id', $input['temp_id']); ?>" />
				<fieldset class='category-border-main owner-form'>	
					<div class="category-border-main bg-success text-center" >
						Trouble Ticket
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Support #</span>
									<input type="text" class="form-control" id="cs_no" name="cs_no" 
									value="<?php echo set_value( 'cs_no', $input['cs_no'] ); ?>" placeholder="Support #" READONLY />
									<input type="hidden" id="cs_id" name="cs_id" value="<?php echo $input['cs_id']; ?>" />
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
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Report Date</span>
									<input type="text" class="form-control" id="report_date" name="report_date" 
									value="<?php echo set_value( 'report_date', $input['report_on'] ); ?>" placeholder=""  autocomplete="off"/>
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Service By</span>
									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" id="service_by" name="service_by">
											<?php foreach( $sel_user AS $val ){ ?>
												<option <?php echo set_select('service_by', $val['idx'], ($input['service_by'] == $val['idx'] ? true : false) ) ?> value='<?php echo $val['idx'];?>'><?php echo $val['display_name'];?></option>
											<?php } ?>
										</select>
										<input type="hidden" class="form-control" id="prev_service_by" name="prev_service_by" 
											value="<?php echo set_value( 'prev_service_by', $input['prev_service_by'] ); ?>" />
									</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">On Site Date</span>
									<input type="text" class="form-control" id="on_site_date" name="on_site_date" 
									value="<?php echo set_value( 'on_site_date', $input['onsite_on'] ); ?>" placeholder="" autocomplete="off" />
								</div>
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
												
										<i class="ace-icon fa fa-times red" id="clear_customer" style="cursor:pointer;"></i>
									</span>
											
									<input type="hidden" class="form-control" id="customer_no" name="customer_no" 
											value="<?php echo set_value( 'customer_no', $input['customer_no'] ); ?>" placeholder="Customer Number" />
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
									<span class="input-group-addon input_group">Customer Addr.<span class="red">*</span></span>
									<textarea class="autosize" id="customer_address" name="customer_address" style="width:100%"><?php echo set_value('customer_address', $input['customer_addr']); ?></textarea>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">&nbsp;</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Type of Premises </span>

									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" id="premises_type" name="premises_type">
											<option <?php echo set_select('premises_type', 'r', ($input['premises_type'] == 'r' ? true : false) ) ?> value="r">Residential</option>
											<option <?php echo set_select('premises_type', 'c', ($input['premises_type'] == 'c' ? true : false) ) ?> value="c">Commercial</option>
										</select>
									</span>

								</div>
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Package </span>
									<span class="input-icon input-icon-right" style='display:inline;'>
									<input type="text" class="form-control" id="package_name" name="package_name" 
											value="<?php echo set_value( 'package_name', $input['package_name'] ); ?>" placeholder="" READONLY />
									</span>
									<input type="hidden" name="package_id" id="package_id" value="<?php echo set_value( 'package_id', $input['package'] ); ?>" READONLY />
								</div>
							</div>
							
						</div>
					</div>
					
					<div class="col-lg-12">&nbsp;</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Services </span>
									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" name="service_type" id="service_type" >
											<?php 
												foreach( $cs_service_list AS $val ){
													$selected = $input['service_type'] == $val['cs_service_id'] ? true : false ;
													
													echo "<option ".(set_select('service_type',$val['cs_service_id'],$selected))." value='".$val['cs_service_id']."'>".$val['cs_service_name']."</option>";
												}
											?>
										</select>
									</span>
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Problems </span>

									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" name="service_problem" id="service_problem" >
											<?php 
												foreach( $cs_problem_list AS $val ){
													$selected = $input['service_problem'] == $val['cs_problem_id'] ? true : false ;
													
													echo "<option ".(set_select('service_problem',$val['cs_problem_id'],$selected))." value='".$val['cs_problem_id']."'>".$val['cs_problem_name']."</option>";
												}
											?>
										</select>
									</span>

								</div>
							</div>
							
						</div>
					</div>
					
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Remark </span>
									<textarea class="autosize form-control" style="width:100%;" name="service_remark" id="service_remark" ><?php echo set_value('service_remark', $input['service_remark']); ?></textarea>
								</div>
							</div>

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Action Taken</span>
									<textarea class="autosize form-control" style="width:100%;" name="service_action_taken" id="service_action_taken" ><?php echo set_value('service_action_taken', $input['action_remark']); ?></textarea>
								</div>
							</div>
							
						</div>
					</div>
					
				</fieldset>

				<fieldset class='category-border-main'>
					<div class="category-border-main bg-success text-center">
						Attachments
					</div>

					<div class="form-group col-lg-12 col-xs-12" style="z-index:100;">

						<div class="col-xs-12 col-xs-12">Attach Supporting Documents</div>
						
						<div class="col-lg-3 col-xs-12">
							<input multiple="multiple" type="file" id="attach_file_input" data-preview-file-type="text" />
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

				<fieldset class='category-border-main owner-form'>	
					<div class="category-border-main bg-success text-center" >
						Testing & Commisioning Procedure
					</div>

					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Download </span>
											
									<span class="input-icon input-icon-right" style='display:inline;'>
									<input  type="text" class="form-control" id="test_download" name="test_download" 
											value="<?php echo set_value( 'test_download', $input['test_download'] );?>" placeholder="" />
												
										<i class="ace-icon">MBPS</i>
									</span>
											
								</div>
							</div>							
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Upload </span>

									<span class="input-icon input-icon-right" style='display:inline;'>
									<input type="text" class="form-control" id="test_upload" name="test_upload" 
											value="<?php echo set_value( 'test_upload', $input['test_upload'] ); ?>"
											placeholder="" />
												
										<i class="ace-icon">MBPS</i>
									</span>

								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Latency </span>

									<span class="input-icon input-icon-right" style='display:inline;'>
									<input type="text" class="form-control" id="test_latency" name="test_latency" 
											value="<?php echo set_value( 'test_latency', $input['test_latency'] ); ?>"
											placeholder="" /> 
												
										<i class="ace-icon">ms</i>
									</span>
											
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">www.google.com </span>
									
									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" name="test_website" id="test_website" >
											<option <?php echo set_select('test_website', '1', ($input['open_website'] == '1' ? true : false) ) ?> value="1">Yes</option>
											<option <?php echo set_select('test_website', '0', ($input['open_website'] == '0' ? true : false) ) ?> value="0">No</option>
										</select>
									</span>
									
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-md-12">&nbsp;</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Status </span>
									
									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" name="status" id="status" onchange="statusChange()">
											<option <?php echo set_select('status', '0', ($input['cs_status'] == '0' ? true : false) ); ?> value="0">Open</option>
											<option <?php echo set_select('status', '1', ($input['cs_status'] == '1' ? true : false) ); ?> value="1">In Progress</option>
											<option <?php echo set_select('status', '2', ($input['cs_status'] == '2' ? true : false) ); ?> value="2">Escalated to 3rd Level Support</option>
											<option <?php echo set_select('status', '3', ($input['cs_status'] == '3' ? true : false) ); ?> value="3">Closed</option>
										</select>
										<input type="hidden" class="form-control" id="prev_status" name="prev_status" 
											value="<?php echo set_value( 'prev_status', $input['prev_status'] ); ?>" />
									</span>
									
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">On </span>
									
									<input type="text" class="form-control" id="closed_on" name="closed_on" 
										value="<?php echo set_value( 'closed_on', $input['closed_on'] ); ?>"
										placeholder="" autocomplete="off" />
									
								</div>
							</div>
							
						</div>
					</div>

					<div class="col-lg-12" id="escalated">
						<div class="col">
							<div class="row">
								<div class="col-lg-6">
									<div class="input-group">
											<span class="input-group-addon input_group">Email To</span>
											<input type="text" class="form-control" id="email" name="email" 
												value="<?php echo set_value('email', $input['email'] ); ?>"
												placeholder="" autocomplete="off" />
											<input type="hidden" class="form-control" id="email_to_user_id" name="email_to_user_id" 
												value="<?php echo set_value('email_to_user_id', $input['email_to_user_id'] ); ?>"
												placeholder="" autocomplete="off" />
											<input type="hidden" class="form-control" id="prev_email_to_user_id" name="prev_email_to_user_id" 
												value="<?php echo set_value('prev_email_to_user_id', $input['prev_email_to_user_id'] ); ?>"
												placeholder="" autocomplete="off" />
									</div>
								</div>
							</div>
							<div class="row">						
								<div class="col-lg-6">
									<div class="input-group">
										<input type="checkbox" value=1 name="resend_email" id="resend_email" /> Resend email?
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-md-12">&nbsp;</div>
					
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Cust. comment </span>
									
									<span class="input-icon input-icon-right" style='display:inline;'>
										<select style="width:100%;" name="customer_rating" id="customer_rating" >
											<option <?php echo set_select('customer_rating', '0', ($input['customer_comment'] == '0' ? true : false) ); ?> value="0">-- SELECT --</option>
											<option <?php echo set_select('customer_rating', '1', ($input['customer_comment'] == '1' ? true : false) ); ?> value="1">Excellent</option>
											<option <?php echo set_select('status', '2', ($input['customer_comment'] == '2' ? true : false) ); ?> value="2">Good</option>
											<option <?php echo set_select('status', '3', ($input['customer_comment'] == '3' ? true : false) ); ?> value="3">Poor</option>
										</select>
									</span>
									
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Remark</span>
									<textarea style="width:100%;" name="customer_remark" id="customer_remark" class="autosize form-control"><?php echo set_value('customer_remark', $input['customer_remark']); ?></textarea>
								</div>
							</div>
						</div>
					</div>
				</fieldset>

				<?php if( $input['cs_id'] != '' ){ ?>
					<fieldset class='category-border-main assignee-form'>
						<div class="category-border-main bg-success text-center" >
							COMMENT
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
											Comment
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
												<input type="hidden" name="rec_id[]" value="<?php echo $row['cs_job_id']; ?>" />
											</td>
											<td style="vertical-align: middle;">
												<?php echo $row['cs_date']; ?> - <?php echo $row['display_name']; ?>
												<input name="date[]" type="hidden" value="<?php echo $row['cs_date']; ?>" />
											</td>
											<td style="vertical-align: middle;">
												<textarea name="remark[]" style="display:none;" class="form-control"><?php echo $row['cs_remark']; ?></textarea>
												<span><?php echo nl2br(htmlspecialchars($row['cs_remark'])); ?></span>
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
								<select id="reply_to_select" class="form-control">
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

				<div class="col-lg-12">
					<div class="row">
						<div class="button-group d-flex flex-column flex-md-row gap-2 justify-content-start align-items-stretch align-items-md-center" style="padding-top:15px;">
				
							<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
								<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>Cancel
							</button>
							<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('customer_support','M');?>>
								<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>Save
							</button>
							<a href="<?php echo base_url('customer_support/print_customer_support');?>/<?php echo $input['cs_no'] ?>">
								<button id="btPrint" name="btPrint" type="button" value="btPrint" class="btn btn-info">
								<i class="menu-icon fa fa-print white" data-toggle="tooltip" title=""></i>Generate Form
								</button>
							</a>
							<?php if($input['cs_id'] != ''): ?>
								<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo check_acl_btn('customer_support','D');?>>
									<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
								</button>
								<span><img id='loading-icon' style='display:none;' src='<?php echo base_url('images/loading.gif'); ?>' /></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
								
				<div class="col-md-12">
				
				</div>
			</form>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>

</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>
<?php include_once( APPPATH . 'views/templates/modal_html_docs_viewer.php'); ?>
<script src="<?php echo base_url("js/datepicker/moment.js?".cssjs_ver()); ?>" ></script>
<script src="<?php echo base_url("js/ace/elements.fileinput.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/attachment.js?".cssjs_ver()); ?>"></script>

<script>
var user_list = <?php echo $user_list; ?>;
autosize($('textarea[class*=autosize]'));

 $('#report_date').datetimepicker({ format: 'YYYY-MM-DD HH:mm:ss' });
 $('#on_site_date').datetimepicker({ format: 'YYYY-MM-DD HH:mm:ss' });
 $('#closed_on').datetimepicker({ format: 'YYYY-MM-DD HH:mm:ss' });

$(window).on('load', function(e) {
	statusChange();
});

$( "#customer_no_name" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "customer/autocomplete_load_customer",
			dataType: "json",
			data: { keyword: request.term },
			success: function( data ) {
				// console.log(data);
				var transformed = $.map(data, function (el) {		
					return {
							label		: (el.customer_no + ' ' + el.name),
							id			: el.customer_no,
							val			: el
					};
				});					
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(thrownError);
				//alert(xhr.status);
				//alert(thrownError);
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {
		$( "#customer_no" ).val(ui.item.val.customer_no);
		
		// var contact_num = "";
		// if( ui.item.val.tel_num != '' && ui.item.val.mobile_num != '' )
		// 	contact_num = ui.item.val.mobile_num + ' / ' + ui.item.val.tel_num ; 
		// else if( ui.item.val.mobile_num != '' )
		// 	contact_num = ui.item.val.mobile_num ; 
		// else if( ui.item.val.tel_num != '' )
		// 	contact_num = ui.item.val.tel_num ; 
		
		$( "#contact_no" ).val( ui.item.val.inst_phone );
		
		$("#premises_type").val( ui.item.val.category );
		$("#package_name").val( ui.item.val.package_name );
		$("#package_id").val( ui.item.val.package );
		$("#customer_address").val(ui.item.val.cust_address);
	}
});

$( document ).on( 'click', '#clear_customer', function(e){
	$( "#customer_no_name" ).val("");
	$( "#customer_no" ).val("");
	$("#contact_no" ).val("");
	$("#premises_type").val("");
	$("#package_name").val("");
	$("#package_id").val("");
	$("#customer_address").val("");
});

function statusChange() {
	let status = $('#status').val();

	if(status == 2) {
		$('#escalated').toggle(true);
	} else {
		$('#escalated').toggle(false);
	}
}

$(document).on('focus', '#email', function() {
	$('#email').autocomplete({
		autoFocus: true,
		source: user_list,
		select: function (event, ui) {
			$('#email_to_user_id').val(ui.item.idx);
		}
	});
});

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#cs_detail').submit(function(e) {
	let reply_to = $('#selected-users .user-tag').map(function () {
      return $(this).data('value');
    }).get();

	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');

	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);
	
	if(commentFormVisible){
		let remark = ($('#new_comment').val() || '').trim();
		if (remark == '') {
			$.gritter.add({
				title: 'ERROR',
				text: 'Comment cannot be empty!',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
			$('.button-group button').prop('disabled', false);
			hideLoadingIcon();
			return;
		}
		formData.append('new_comment_rec_id', $('#new_comment_rec_id').val());
		formData.append('new_comment_date', $('#new_comment_date').val());
		formData.append('new_comment', $('#new_comment').val());
		if (reply_to.length > 0) {
			reply_to.forEach(function (val) {
				formData.append('reply_to[]', val);
			});
		} else {
			formData.append('reply_to[]', '');
		}
	}

	if(clickedButton != '') formData.append(clickedButton, 'submit');

	showLoadingIcon();

	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		success: function (data) {
			if(data['status'] == 'ER'){
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
				hideLoadingIcon();
			} else {
				hideLoadingIcon();
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			console.log(data);
			hideLoadingIcon();
			$('.button-group button').prop('disabled', false);
			$.gritter.add({
				title: 'ERROR',
				text: 'Something wrong has occured during saving.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
		},
		cache: false,
		contentType: false,
		processData: false
	});
});	
</script>





