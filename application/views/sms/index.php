<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('customer', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('sms_scheduler/add_sms');?>" style="margin-left: 5px; margin-right: 5px;" >
							<i class="ui-menu-icon fa fa-plus green"></i>
						</a>
					</span>
					<?php }?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="sms_scheduler" name="sms_scheduler" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th >Title</th>
							<?php // <th class="col-lg-7 text-left">Message</th> ?>
							<th class="col-lg-3"class="text-right" >Last Modified</th>
							<th class="col-lg-3"class="text-right" >Schedule On</th>
							<th class="col-lg-1" class="text-right" >Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($row_data)): ?>
						{row_data}
							<tr>
								<td class="text-left">{sms_title}</td>
								<?php //<td class="text-left">{sms_msg}</td> ?>
								<td class="text-left" >{modified_date}</td>
								<td class="text-left" >{sms_schedule_on}</td>
								<td class="text-left">
									<a href="<?php echo base_url('sms_scheduler/edit_sms');?>/{scheduler_id}" title="Edit">
										<i class="fa fa-pencil fa-1g"></i>
									</a>
								</td>
							</tr>
						{/row_data}
						<?php else: ?>
							<tr>
								<td colspan="4" class="text-center">No Record Available</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
			<div>
				<div class="col-md-12 text-center">
					<?php echo $pagination; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="clear" style="clear:both;"></div>
<div id="popupDetail">
	<div id="popupDetailStd" onclick="disablePopup();">
		<div id="popupContent">
			&nbsp;
		</div>
	</div>
</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>

<script src="<?php echo base_url("js/itelco/sms_scheduler.js?".cssjs_ver()); ?>" ></script>
