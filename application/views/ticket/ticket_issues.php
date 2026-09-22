<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('trouble_ticket', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('ticket/add_ticket_issues');?>" style="margin-left: 5px; margin-right: 5px;" >
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
				<form id="issues" name="issues" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>

					<span>Category: </span>
					<select id="sel_category" name="sel_category">
						<option value="tt_complaint" <?php echo set_select('sel_category', 'tt_complaint', ($sel_category == 'tt_complaint' ? true : false) ) ?>>Complaint Types</option>
						<option value="tt_sof" <?php echo set_select('sel_category', 'tt_sof', ($sel_category == 'tt_sof' ? true : false) ) ?>>Source of Fault</option>
						<option value="tt_cof" <?php echo set_select('sel_category', 'tt_cof', ($sel_category == 'tt_cof' ? true : false) ) ?>>Cause of Fault</option>
					</select>
					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
				</form>
			</div>
			<h5><?php echo (!empty($page_desc)?$page_desc:''); ?></h5>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="col-lg-1 text-left"></th>
							<th class="col-lg-9 text-left">Name</th>
							<th class="col-lg-2 text-center">Action</th>
						</tr>
					</thead>
					<tbody>
					<?php if(!empty($row_data)): ?>
						{row_data}
							<tr>
								<td></td>
								<td class="text-left">{name}</td>
								<td class="text-left hidden">{category}</td>
								<td class="text-center">
									<a href="<?php echo base_url('ticket/add_ticket_issues');?>/{category}/{id}" title="Edit">
										<i class="fa fa-pencil fa-1g"></i>
									</a>
								</td>
							</tr>
						{/row_data}
					<?php else: ?>
						<tr>
							<td colspan="12" class="text-center">No Record Available</td>
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