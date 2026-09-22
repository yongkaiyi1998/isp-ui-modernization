console.log('=============================payment.js=====================================');
$( document ).ready(function() {
    init();
    
    var is_lock = $( "#is_lock" ).val();
    if (is_lock == true) {
		$(":input").attr('readonly', true);
		$(":button").attr('disabled', true);
	}
});

function init()
{
	$("#txt_pay_date_start").datepicker({format: 'yyyy-mm-dd'});
	$("#txt_pay_date_end").datepicker({format: 'yyyy-mm-dd'});
	
	$("#pay_date").datepicker(
		{	
			format: 'yyyy-mm-dd' , 
			autoclose: true
		}
	);
}

$( "#txt_search_autocomplete" ).autocomplete({
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
							id			: el.customer_no,
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
	},
	select: function (event, ui) {			
		console.log(ui.item.val);
		$( "#customer_no" ).val(ui.item.val.customer_no);
		$( "#customer_name" ).val(ui.item.val.name);
		$( "#package_name" ).text(ui.item.val.package_name);
		$( "#monthly_charge" ).text(ui.item.val.monthly_charge);
	}
});

function print_bill(bill_no)
{
	var new_tab = 0;
	url = base_url+'bill/bill_statement/bill/'+bill_no;
	if(new_tab == 1) window.open(url);
	else window.location = url;
}


function generate_xml_filtered()
{
	console.log('btXML');
	var form = $('form');
	form.attr('action', base_url+'payment/xml_filtered/');
	form.attr('target', '_self');
	$('#btFilter').click();
	form.attr('action', base_url + 'payment/export_payment') ;
	form.removeAttr('target');
}

