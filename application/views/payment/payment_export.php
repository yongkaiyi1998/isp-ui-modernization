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
				<form id="payment" name="payment" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					<span>Pay Date: </span>
					<input type='text' id='txt_pay_date_start' name='txt_pay_date_start' value="<?php echo set_value('txt_pay_date_start', $txt_pay_date_start); ?>" placeholder='Start Date' autocomplete="off"/>
					<span>Until: </span>
					<input type='text' id='txt_pay_date_end' name='txt_pay_date_end' value="<?php echo set_value('txt_pay_date_end', $txt_pay_date_end); ?>" placeholder='End Date' autocomplete="off"/>
					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					
					<button  id="btXML" name="btXML" type="button" value="XML" class="btn btn-success" onclick='generate_xml_filtered()'>
						<i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title=""></i>
						XML
					</button>
					
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>Payment No</th>
							<th class="text-center">Post Date</th>
							<th class="text-center">Pay Date</th>
							<th>Customer Name</th>
							<th >Remark</th>
							<th class="col-lg-1 text-right">Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($row_data)): ?>
							{row_data}
								<tr>
									<td>{payment_no}</td>
									<td class="text-center">{tranx_date}</td>
									<td class="text-center">{pay_date}</td>
									<td>{customer_name}</td>
									<td>{remark}</td>
									<td class="text-right">{amount}</td>
								</tr>
							{/row_data}
						<?php else: ?>
							<tr>
								<td colspan="6" class="text-center">No Record Available</td>
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

<script src="<?php echo base_url("js/itelco/payment.js?".cssjs_ver()); ?>" ></script>
