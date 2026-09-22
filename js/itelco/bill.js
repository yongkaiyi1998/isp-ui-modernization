$( document ).ready(function() {
    init();
	//$('#table_id').DataTable({bFilter: false, bInfo: false});
});

function init()
{
	$("#txt_pay_date_start").datepicker({format: 'yyyy-mm-dd'});
	$("#txt_pay_date_end").datepicker({format: 'yyyy-mm-dd'});
	
	$("#pay_date").datepicker({format: 'yyyy-mm-dd'});
	$("#txt_bill_date").datepicker({format: 'yyyy-mm-dd'});
	
	var customer_category =  $('#customer_category').val();
	
	/*
	if(customer_category == 'r'){
		show_invoice_icon(0);		
	}
	*/

	window.addEventListener( "pageshow", function ( event ) {
	  var historyTraversal = event.persisted || 
	                         ( typeof window.performance != "undefined" && 
	                              window.performance.navigation.type === 2 );
	  if ( historyTraversal ) {
	    // Handle page restore.
	    window.location.reload();
	  }
	});

}
function print_bill_filtered(layout)
{
	console.log('btPrintFiltered');
	var form = $('form');
	form.attr('action', base_url+'bill/print_filtered/'+layout);
	form.attr('target', '_blank');
	$('#btPrintFiltered').click();
	form.attr('action', base_url + 'bill') ;
	form.removeAttr('target');
}

function show_invoice_icon(bool)
{
	console.log('func: ||===> show_invoice_icon('+bool+')');
	if(bool == 0) $('.fa-files-o').hide();
	console.log('func: <===|| show_invoice_icon('+bool+')');
}

function print_latest_bill(customer_no)
{
	var new_tab = 0;
	
	url = base_url+'bill/bill_statement/latest_bill_by/'+customer_no;
	
	if(new_tab == 1) window.open(url,'_blank');
	else window.location = url;
}

function print_bill(bill_no)
{
	var new_tab = 1;
	
	url = base_url+'bill/bill_statement/bill/'+bill_no;
	
	if(new_tab == 1) window.open(url,'_blank');
	else window.location = url;
}

function print_invoice(bill_no)
{
	var new_tab = 1;
	
	url = base_url+'bill/bill_invoice/no/'+bill_no;
	
	if(new_tab == 1) window.open(url,'_blank');
	else window.location = url;
}

function submit_einvoice(bill_no, bill_type, elm) {

    const $btn = $(elm);
    const $icon = $btn.find('i');

    const originalIconClass = $icon.attr('class');

	let result = confirm('Are you sure want to submit einvoice?');

	if (!result) {
		return false;
	}

    $.ajax({
        dataType: 'json',
        type: 'POST',
        data: {
            bill_no: bill_no,
            bill_type: bill_type
        },
        url: base_url + 'ajax/send_einvoice',

        beforeSend: function () {
            $icon.attr('class', 'menu-icon fa fa-spinner fa-spin blue');
            $btn.css({
                'pointer-events': 'none',
                'opacity': '0.6'
            });
        },

        success: function (data) {
            if (data['succ'] == true) {

                $.gritter.add({
                    title: 'SUCCESS',
                    text: 'E-Invoice ' + bill_no + ' Sent.',
                    time: '2000',
                    class_name: 'success-notice'
                });

                setTimeout(function () {
                    window.location.reload();
                }, 2000);

            } else {

                $.gritter.add({
                    title: 'ERROR',
                    text: 'E-Invoice ' + bill_no + ' Sent Failed.<br>' + data['msg'],
                    time: '10000',
                    close_icon: 'l-arrows-remove s16',
                    class_name: 'info-notice'
                });
            }
        },

        error: function (a, b, c) {
            console.log(a);
            console.log(b);
            console.log(c);

            $.gritter.add({
                title: 'ERROR',
                text: 'Something wrong has occured during submit.',
                time: '5000',
                close_icon: 'l-arrows-remove s16',
                class_name: 'info-notice'
            });
        },

        complete: function () {
            $icon.attr('class', originalIconClass);
            $btn.css({
                'pointer-events': '',
                'opacity': ''
            });
        }
    });
}

function ajax_filter(filter_pressed=0) {
	//post values

	if (filter_pressed == 1) {
		$("#page_item_no").val(0);
	}
	
	$.ajax({
		type: "POST",
		url: base_url + "bill/bill_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			sel_status: $("#sel_status").val(),
			sel_category: $("#sel_category").val(),
			sel_bill_by: $("#sel_bill_by").val(),
			txt_bill_date: $("#txt_bill_date").val(),
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
	let lastDayOfMonth = new Date(today.getFullYear(), today.getMonth()+1, 0);

	let offset = lastDayOfMonth.getTimezoneOffset()
	lastDayOfMonth = new Date(lastDayOfMonth.getTime() - (offset*60*1000))

	$("#page_item_no").val(0);
	$("#txt_search").val('');
	$("#sel_status").val('all');
	$("#sel_category").val('all');
	$("#sel_bill_by").val('all');
	$("#txt_bill_date").val(lastDayOfMonth.toISOString().split('T')[0]);
	$("#txt_bill_date").datepicker('setDate', lastDayOfMonth.toISOString().split('T')[0]);
	ajax_filter(1);
}