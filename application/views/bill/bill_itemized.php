<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					{page_title}
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="col-lg-2">Trans Date</th>
							<th class="col-lg-2">Bill Type</th>
							<th class="col-lg-5">Remark</th>
							<th class="col-lg-1 text-right">Amount</th>
							<th class="col-lg-1 text-right">Tax</th>
							<th class="col-lg-1 text-right">Tax Code</th>
						</tr>
					</thead>
					<tbody>
						{row_data}
							<tr>
								<td>{tranx_date}</td>
								<td>{bill_type_name}</td>
								<td>{remark}</td>
								<td class="text-right">{amount}</td>
								<td class="text-right">{tax_amount}</td>
								<td class="text-right">{tax_code}</td>
							</tr>
						{/row_data}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">$("#username").focus();</script>
