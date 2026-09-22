<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="bill" name="bill" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
					<span>Category </span>
					<select id="sel_category" name="sel_category">
						<option value="all">All</option>
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<span>Status </span>
					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<?php
							foreach ($sel_status_list as $val) {
								echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>

					<span>Bill Date </span>
					<input id="txt_bill_date_from" name="txt_bill_date_from" placeholder="From" autocomplete="off"
						value="<?php echo set_value('txt_bill_date_from',$txt_bill_date_from); ?>" />

					<span>To </span>
					<input id="txt_bill_date_to" name="txt_bill_date_to" placeholder="To" autocomplete="off"
						value="<?php echo set_value('txt_bill_date_to',$txt_bill_date_to); ?>" />

					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					

					<button  id="btXML" name="btXML" type="button" value="XML" class="btn btn-success" onclick='generate_xml_filtered()'>
						<i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title="Latest Bill"></i>
						XML
					</button>
										
					<button  id="btPrintFiltered" name="btPrintFiltered" type="submit" value="filter" class="btn btn-success" role="button" style="display:none" >
						btPrintFiltered
					</button>
					
					<button  id="btXMLFiltered" name="btXMLFiltered" type="submit" value="XML" class="btn btn-success" role="button" style="display:none" >
						btXMLFiltered
					</button>
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="">Bill No</th>
							<th class="">Customer No</th>
							<th class="">Customer Name</th>
							<th class="text-center">Bill Date</th>
							<th class="text-center">Charges</th>
							<th class="text-center">GST</th>
							<th class="text-center">Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($row_data)): ?>
							{row_data}
								<tr>
									<td>{bill_no}</td>
									<td>{customer_no}</td>
									<td>{name}</td>
									<td class="text-center">{bill_date}</td>
									<td class="text-right">{charges}</td>
									<td class="text-right">{tax_charges}</td>
									<td class="text-right">{amount}</td>
								</tr>
							{/row_data}
						<?php else: ?>
							<tr>
								<td colspan="7" class="text-center">No Record Available</td>
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

<script src="<?php echo base_url("js/itelco/bill_export.js?".cssjs_ver()); ?>" ></script>
