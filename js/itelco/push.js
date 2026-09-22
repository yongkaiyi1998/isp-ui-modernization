function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "push/push_rows/0",
		data: {
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			date_from: $("#date_from").val(),
			date_to: $("#date_to").val(),
			push_type: $("#push_type").val(),
			source: $("#source").val(),
			push_status: $("#push_status").val(),
		},
		success: function (data) {
			console.log('hi2');
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
	$("#page_item_no").val(0);
	$("#txt_search").val('');
	$("#push_type").val('all');
	$("#source").val('all');
	$("#push_status").val('all');
	$('#date_from').val('');
	$("#date_from").datepicker('setDate', '');
	$('#date_to').val('');
	$("#date_to").datepicker('setDate', '');
	ajax_clear_session('push', 'push_filter');
	ajax_filter(1);
}
