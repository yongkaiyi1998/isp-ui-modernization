<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<button  id="btAdd" name="btAdd" type="button" value="Add" class="btn btn-success" onclick="window.location.href='<?php echo base_url('audit_docs/add');?>';">
							<i class="ui-menu-icon fa fa-plus white"></i> Upload File To Audit Folder 
						</button>
					</span>
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
					<select id="sel_file_type" name="sel_file_type">
						<option value="" <?php echo set_select('sel_file_type', '', ($sel_file_type == '' ? true : false) ) ?>>All</option>
						<option value="bill" <?php echo set_select('sel_file_type', 'bill', ($sel_file_type == 'bill' ? true : false) ) ?>>Bills</option>
						<option value="customer_so" <?php echo set_select('sel_file_type', 'customer_so', ($sel_file_type == 'customer_so' ? true : false) ) ?>>Customer Sales Order</option>
						<option value="customer_install" <?php echo set_select('sel_file_type', 'customer_install', ($sel_file_type == 'customer_install' ? true : false) ) ?>>Customer Installation Form</option>
						<option value="customer_term" <?php echo set_select('sel_file_type', 'customer_term', ($sel_file_type == 'customer_term' ? true : false) ) ?>>Customer Termination Form</option>
						<option value="customer_contract" <?php echo set_select('sel_file_type', 'customer_contract', ($sel_file_type == 'customer_contract' ? true : false) ) ?>>Customer Contract</option>
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
							<th class="col-lg-1 text-left">Bill No.</th>
							<th class="col-lg-1 text-left">Customer No.</th>
							<th class="col-lg-2 text-left">Customer</th>
							<th class="col-lg-5 text-left">File Name</th>
							<th class="col-lg-2 text-left">Uploaded Date</th>
							<th class="col-lg-1 text-center"></th>
						</tr>
					</thead>
					<tbody>
					<?php if(!empty($row_data)): ?>
						{row_data}
							<tr>
								<td class="text-left">{bill_no}</td>
								<td class="text-left">{customer_no}</td>
								<td class="text-left">{customer_name}</td>
								<td class="text-left">{file_name}</td>
								<td class="text-left">{created_at}</td>
								<td class="text-center">
									<button id="download_btn_{file_id}" name="btDownload" type="button" value="Download" class="btn btn-success btn-xs" onclick="download_file('{file_id}');">
									<i class="menu-icon fa fa-download" data-toggle="tooltip" title=""></i> Download
							</button>
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