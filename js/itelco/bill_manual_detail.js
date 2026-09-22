console.log('=============================bill_manual_detail.js=====================================');
$( document ).ready(function() {
    $("#bill_date").datepicker({format: 'yyyy-mm-dd'});
	
    toggleInvoiceIdField();

    $('#bill_type').change(function() {
        toggleInvoiceIdField();
    });

    $('#related_bill_no').change(function(e) {
		e.preventDefault();
		if ($(this).val() != "") {			
			load_invoice_detail($(this).val());
		}else{
			removeAllRow();
		}
    });

	if($('#customer_no').val() != ""){
		$('#related_bill_no').prop('disabled', false);
	}

	if (canEdit == 0) {
		// Disable all input fields in the form
		$('#bill_manual_detail')
			.find('input, select, textarea, button')
			.prop('disabled', true);

		$('.button-group #btVoid').prop('disabled', false);
	}
});

function removeAllRow() {
	let i = $('#tblAppendGrid').appendGrid('getRowCount') - 1;
	while (i >= 0) $('#tblAppendGrid').appendGrid('removeRow', i--);
}

function toggleInvoiceIdField() {
	if ($('#bill_type').val() !== 'INV') {
		$('#invoice_id_field').show();
		$('#reason_field').show();
		let customer_no = $('#customer_no').val();
		if(customer_no != ""){
			after_select_customer();
		}
	} else {
		$('#invoice_id_field').hide();
		$('#reason_field').hide();
	}
}

//payment_no
$( "#payment_search_autocomplete" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "bill/ajax_get_unprocess_payment",
			dataType: "json",
			data: { customer_no: $('#customer_no').val() ,
					payment_no : request.term
				  },
			success: function( data ) {
				//console.log(data);
				var transformed = $.map(data, function (el) {		
					return {
							label		: (el.tranx_date + ' #' + el.payment_no),
							id			: el.idx,
							val			: el
					};
				});
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(base_url + "bill/ajax_get_unprocess_payment");
				response([]);
				console.log(thrownError);
				
			}
		});

	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui){
		console.log(ui.item.val);
		$( "#payment_no" ).val(ui.item.val.payment_no);
		$( "#tranx_date" ).val(ui.item.val.tranx_date);
		$( "#pay_date" ).val(ui.item.val.pay_date);
		$( "#pay_amount" ).val(ui.item.val.amount);
	}
});

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
	select: function (event, ui){
		console.log(ui.item.val);
		if(ui.item.val.latest_status !== 'A'){
			$('.cust-warning-msg').show();
		}else{
			$('.cust-warning-msg').hide();
		}
		$( "#customer_no" ).val(ui.item.val.customer_no);
		$( "#customer_name" ).val(ui.item.val.name);
		$( "#package_name" ).val(ui.item.val.package_name);
		$( "#monthly_charge" ).val(ui.item.val.monthly_charge);
		load_last_bill( ui.item.val.customer_no);
		load_invoice_bill(ui.item.val.customer_no);
		after_select_customer();
	}
});

function after_select_customer(){
	$('#related_bill_no').prop('disabled', false);
	$('#reason').prop('disabled', false);
}
function load_last_bill(){
	$.ajax({
		type: "POST",
		url: base_url + "bill/ajax_get_last_bill",
		dataType: "json",
		data: { 
			customer_no: $( "#customer_no" ).val(), 
			bill_no: $( "#bill_no" ).val()
		},
		success: function( data ) {
			if(data['previous_balance'] != ""){
				$('#prev_balance').val( data['balance'] );
			}else{
				$('#prev_balance').val('0.00');
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
			//alert(xhr.status);
			//alert(thrownError);
		}
	});

}

function load_invoice_bill(customer_no) {
	$.ajax({
		type: "POST",
		url: base_url + "ajax/ajax_get_invoice_bill",
		dataType: "json",
		data: { customer_no: customer_no},
		success: function( data ) {
			$('#related_bill_no').empty();
			$('#related_bill_no').append('<option value="">-</option>');
			$.each(data, function(index, invoice) {
				$('#related_bill_no').append(
					'<option value="' + invoice.bill_no + '">' + invoice.bill_no + '   [' + invoice.bill_date + ']</option>'
				);
			});
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
	});
}

function load_invoice_detail(bill_no){
	$.ajax({
		type: "POST",
		url: base_url + "bill/ajax_get_invoice_detail",
		dataType: "json",
		data: { bill_no: bill_no},
		success: function( data ) {
			removeAllRow();
			
			if(data.length > 0){
				$.each(data, function(index, detail) {
					append_row(
						detail.idx,
						detail.bill_draft_no,
						detail.tranx_date,
						detail.bill_type,
						detail.transaction_type,
						detail.tax_code,
						detail.tax_percent,
						detail.amount,
						detail.tax_amount,
						detail.plus_minus,
						detail.remark,
						detail.total_amount,
						index
					);
				});
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
	});
}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#bill_manual_detail').submit(function(e) {
	e.preventDefault();

	tinyMCE.triggerSave();
	
	let submitter = e.originalEvent.submitter.id;
	$('#task').val(submitter);

	$('.input-group-addon').parent().removeClass('has-error');
	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);
	if(clickedButton != '') formData.append(clickedButton, 'submit');

	showLoadingIcon();
	
	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		success: function (data) {
			if(data['status'] == 'ER'){
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
				hideLoadingIcon();
			} else {
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			$('.button-group button').prop('disabled', false);
			hideLoadingIcon();
			$.gritter.add({
				title: 'ERROR',
				text: 'Something wrong has occured during saving.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
		},
		cache: false,
		contentType: false,
		processData: false
	});
});	

showLoadingIcon = function() {
	$('#loading-icon').show();
}

hideLoadingIcon = function() {
	$('#loading-icon').hide();
}