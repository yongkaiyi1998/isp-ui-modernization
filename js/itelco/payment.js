console.log('=============================payment.js=====================================');
$( document ).ready(function() {
    init();
    
    var is_lock = $( "#is_lock" ).val();
    if (is_lock == true) {
		$(":input").attr('readonly', true);
		//$(":button").attr('disabled', true);
		$("#btDelete").attr('disabled', true);
		$("#cheque_no").attr('readonly', false);
		$("#remark").attr('readonly', false);
	}
	
	if( $('#customer_no').val() != '' && $('#customer_no').val() != undefined){
		load_unprocess_payment();
	}else{
		$("#payment_field").hide();
	}


});

function print_receipt(data){
	
	var new_tab = 1;
	
	url = base_url+'payment/payment_receipt/'+data;
	
	if(new_tab == 1) window.open(url);
	else window.location = url;
}

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
		$( '#account_status').text(ui.item.val.account_status);
		//load_unprocess_payment();
		load_last_payment();
		load_invoice_bill(ui.item.val.customer_no);
		after_select_customer();
	}
});

function load_last_payment(){

	$.ajax({
		type: "POST",
		url: base_url + "payment/ajax_get_last_payment",
		dataType: "json",
		data: { customer_no: $( "#customer_no" ).val() },
		success: function( data ) {

			// console.log(data);

			if(data['idx'] == ""){

				$( "#err_msg" ).text("Nothing to show");
				$( "#err_msg" ).show();
				$("#payment_field").hide();
				
				//('#err_msg').text("<div>This is a test.</div>");
			}else{
				$( "#err_msg" ).hide();
				$("#payment_field").show();
				$( "#last_payment_no" ).text(data['payment_no']);
				$( "#last_payment_date" ).text(data['pay_date']);
				$( "#last_payment_amount" ).text(data['amount']);
				$( "#last_payment_remark" ).text(data['remark']);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			
			console.log(thrownError);
			//alert(xhr.status);
			//alert(thrownError);
		}
	});

}

function load_unprocess_payment(){

	$.ajax({
		type: "POST",
		url: base_url + "payment/ajax_get_unprocess_payment",
		dataType: "json",
		data: { customer_no: $( "#customer_no" ).val() },
		success: function( data ) {

			// console.log(data);

			if(data['idx'] == ""){

				$( "#err_msg" ).text("Nothing to show");
				$( "#err_msg" ).show();
				$("#payment_field").hide();
				
				//('#err_msg').text("<div>This is a test.</div>");
			}else{
				$( "#err_msg" ).hide();
				$("#payment_field").show();
				$( "#last_payment_no" ).text(data['payment_no']);
				$( "#last_payment_date" ).text(data['pay_date']);
				$( "#last_payment_amount" ).text(data['amount']);
				$( "#last_payment_remark" ).text(data['remark']);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			
			console.log(thrownError);
			//alert(xhr.status);
			//alert(thrownError);
		}
	});

}


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

function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'payment/export_payment');
	$('#btSubmit').click();
	form.attr('action', base_url + 'payment') ;
}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#payment_detail').submit(function(e) {
	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');

	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);
	if(clickedButton != '') formData.append(clickedButton, 'submit');

	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		success: function (data) {
			if(data['status'] == 'ER'){
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
			} else {
				if(data['open_url'] != undefined && data['open_url'] != '') {
					// If open_url is set, open URL
					window.open(data['open_url'], '_blank');
				}
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			$('.button-group button').prop('disabled', false);
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

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "payment/payment_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			txt_pay_date_start: $("#txt_pay_date_start").val(),
			txt_pay_date_end: $("#txt_pay_date_end").val(),
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
    let today = new Date();
    let formatDate = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let start = new Date(today.getFullYear(), today.getMonth(), 1);
    let end = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    $("#page_item_no").val(0);
    $("#txt_search").val('');
    $("#txt_pay_date_start").val(formatDate(start));
    $("#txt_pay_date_end").val(formatDate(end));
    $("#txt_pay_date_start").datepicker('setDate', formatDate(start));
    $("#txt_pay_date_end").datepicker('setDate', formatDate(end));
    ajax_filter(1);
}