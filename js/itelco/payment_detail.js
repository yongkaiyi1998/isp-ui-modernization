console.log('=============================payment_detail.js=====================================');
$( document ).ready(function() {
	if($('#customer_no').val() != ''){
		after_select_customer();
	}
});

function load_invoice_bill(customer_no) {
	$.ajax({
		type: "POST",
		url: base_url + "ajax/ajax_get_invoice_bill",
		dataType: "json",
		data: { customer_no: customer_no},
		success: function( data ) {
			$('#ref_no').empty();
			$('#ref_no').append('<option value="">-</option>');
			$.each(data, function(index, invoice) {
				$('#ref_no').append(
					'<option value="' + invoice.bill_no + '">' + invoice.bill_no + '   [' + invoice.bill_date + ']</option>'
				);
			});
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
	});
}

function after_select_customer(){
	$('#ref_no').prop('disabled', false);
}