console.log('=============================action_log.js=====================================');
$( document ).ready(function() {
    init();
});

function init()
{
	
	$("#from_date").datepicker({format: 'yyyy-mm-dd'});
	$("#to_date").datepicker({format: 'yyyy-mm-dd'});

	$( "#customer_no" ).autocomplete({
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
			$( "#customer_no" ).val(ui.item.val);
			//$('#txt_search').val(ui.item.val); 
			return false;
		}
	});
	
}
