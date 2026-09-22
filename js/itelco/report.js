var date = new Date();

$( document ).ready(function() {
    init();
    
    $('#txt_date_start').datepicker({format: 'yyyy-mm-dd'});
	$('#txt_date_end').datepicker({format: 'yyyy-mm-dd'});
});

function init(){}

$("#btn_current_month").click( function() { 
	get_date_range(true);
});

$("#btn_previous_month").click( function() {
	var firstDay = new Date(date.getFullYear(), date.getMonth() - 1, 1);
	var lastDay = new Date(date.getFullYear(), date.getMonth(), 0);
	
	$('#txt_date_start').val( $.datepicker.formatDate('yy-mm-dd', firstDay) );
	$("#txt_date_start").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', firstDay));
	$('#txt_date_end').val( $.datepicker.formatDate('yy-mm-dd', lastDay) );
	$("#txt_date_end").datepicker('setDate', $.datepicker.formatDate('yy-mm-dd', lastDay));

	ajax_filter();
});

$( "#txt_search" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "ajax/autocomplete_customer",
			dataType: "json",
			data: { keyword: request.term },
			success: function( data ) {
				//console.log(data);
				var transformed = $.map(data, function (el) {		
					return {
							label		: (el.customer_no + ' ' + el.name),
							id			: el.customer_no ,
							val			: el.customer_no
					};
				});
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(thrownError);
				//alert(xhr.status);
				//alert(thrownError);
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {
		console.log(ui.item.val);
		$( "#txt_customer_no" ).val(ui.item.val);
		$('#txt_search').val(ui.item.val); 
		return false;
	}
});


$( "#txt_dealer" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "ajax/autocomplete_load_dealer",
			dataType: "json",
			data: { keyword: request.term },
			success: function( data ) {
				//console.log(data);
				var transformed = $.map(data, function (el) {		
					return {
							label		: el.name,
							id			: el.dealer_no,
							val			: el
					};
				});					
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(thrownError);
				//alert(xhr.status);
				//alert(thrownError);
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	}
});
