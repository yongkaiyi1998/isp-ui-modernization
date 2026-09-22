<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('trouble_ticket', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('customer_support/add_cs_service');?>" style="margin-left: 5px; margin-right: 5px;" >
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

					<span>Type: </span>
					<select id="sel_cs_type" name="sel_cs_type">
						<option value="cs_service" <?php echo set_select('sel_cs_type', 'cs_service', ($sel_cs_type == 'cs_service' ? true : false) ) ?>>TT Services</option>
						<option value="cs_problem" <?php echo set_select('sel_cs_type', 'cs_problem', ($sel_cs_type == 'cs_problem' ? true : false) ) ?>>TT Problems</option>
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
								<td class="text-left hidden">{cs_type}</td>
								<td class="text-center">
									<a href="<?php echo base_url('customer_support/add_cs_service');?>/{cs_type}/{id}" title="Edit">
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