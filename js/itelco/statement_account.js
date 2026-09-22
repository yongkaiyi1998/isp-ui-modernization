var date = new Date();

$(document).ready(function () {
	init();
	//$('#table_id').DataTable({bFilter: false, bInfo: false});
});

function init() {
	$("#txt_date_from").datepicker({ format: 'yyyy-mm-dd' });
	$("#txt_date_to").datepicker({ format: 'yyyy-mm-dd' });
	var customer_category = $('#customer_category').val();
}

function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "report/customer_statement_listing_rows/0",
		data: {
			page_item_no: $('#page_item_no').val(),
			txt_search: $('#txt_search').val(),
			sel_category: $("#sel_category").val(),
			sel_status: $("#sel_status").val(),
			txt_date_from: $("#txt_date_from").val(),
			txt_date_to: $("#txt_date_to").val()
		},
		success: function (data) {
			$('.table_rows_area').html(data);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		}
	});
}

function ajax_clear() {
	$('#page_item_no').val(0);
	$("#txt_search").val('');
	$("#sel_category").val('all');
	$("#sel_status").val('r');

	var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);

	$("#txt_date_from").val($.datepicker.formatDate('yy-mm-dd', firstDay));
	$("#txt_date_to").val($.datepicker.formatDate('yy-mm-dd', date));
	$("#txt_date_from").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', firstDay));
	$("#txt_date_to").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', date));

	let table_html = `
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th class="">Customer No</th>
					<th class="">Customer Name</th>
					<th class="text-center">Opening Balance / BF</th>
					<th class="text-center">Balance</th>
					<th class="text-center">Actions</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		`;

	// ajax_filter();
	$('.table_rows_area').html(table_html);
	ajax_clear_session('report', 'report_customer_statement_listing_filter');
}