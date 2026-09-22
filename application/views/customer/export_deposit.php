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
				<form id="deposit" name="deposit" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Category </span>
					<select id="sel_category" name="sel_category">
						<option value="all">All</option>
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . 
										set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . 
										$val['name']."</option> ";
							}
						?>
					</select>
					<span>Deposit Date: </span>
					<input type='text' id='deposit_date_start' name='deposit_date_start' value="<?php echo set_value('deposit_date_start', $deposit_date_start); ?>" placeholder='Start Date' autocomplete="off"/>
					<span>To: </span>
					<input type='text' id='deposit_date_end' name='deposit_date_end' value="<?php echo set_value('deposit_date_end', $deposit_date_end); ?>" placeholder='End Date' autocomplete="off"/>
					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					
					<button  id="btExcel" name="btExcel" type="button" value="Excel" class="btn btn-success" onclick='generate_xls_filtered()'>
						<i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title=""></i>
						Excel
					</button>
					
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>Deposit Date</th>
							<th>Customer No</th>
							<th>Customer Name</th>
							<th>Remark</th>
							<th>Payment Source</th>
							<th>Payment Info</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($row_data)): ?>
							{row_data}
								<tr>
									<td>{pay_date}</td>
									<td>{customer_no}</td>
									<td>{customer_name}</td>
									<td>{remark}</td>
									<td>{payment_source_name}</td>
									<td>{cheque_no}</td>
									<td>{amount}</td>
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

<script src="<?php echo base_url("js/itelco/deposit_export.js?".cssjs_ver()); ?>" ></script>