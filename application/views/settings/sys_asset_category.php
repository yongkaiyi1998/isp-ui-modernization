<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('settings/add_asset_category');?>" style="margin-left: 5px; margin-right: 5px;" >
							<i class="ui-menu-icon fa fa-plus green"></i>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="bill_type" name="bill_type" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					
					<!--<span>Search: </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" 
							placeholder='Search'/>
					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>-->
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="col-lg-2">Category Code</th>
							<th class="col-lg-8">Asset Category Name</th>
							<th class="col-lg-2 text-right">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($row_data)): ?>
							{row_data}
								<tr>
									<td>{category_code}</td>
									<td>{category_name}</td>
									<td class="text-right">
										<a href="<?php echo base_url('settings/edit_asset_category');?>/{category_idx}" title="Edit">
											<i class="fa fa-pencil fa-1g"></i>
										</a>
									</td>
								</tr>
							{/row_data}
						<?php else: ?>
							<tr>
								<td colspan="3" class="text-center">No Record Available</td>
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

