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

    $("#chk_all").click(function(){
        $('input:checkbox').not(this).prop('checked', this.checked);
    });
	
}

function submit_einvoice( bill_no, bill_type, elm ) {

    $.ajax({
        dataType: 'json',
        type: 'POST',
        data: {
            'bill_no':bill_no,
            'bill_type':bill_type 
        },
        url: base_url + 'ajax/send_einvoice',
        beforeSend: function (data) {
            $('#loading_'+bill_no).css('display', '');
            $('#btEinvoice_'+bill_no).prop('disabled', true);
        },
        success: function (data) {
            if (data['succ'] == true) {
                $('.msg-print').prepend(`<div id="success" class="alert alert-success" role="alert">E-Invoice `+bill_no+` Sent.</div>`);
                setTimeout(function(){$('#success').fadeOut(); $('#btFilter').click();},2000); 
            } else {
                $('.msg-print').prepend(`<div id="error_msg" class="alert alert-danger" role="alert">E-Invoice `+bill_no+` Sent Failed.<br>`+data['msg']+`</div>`);
                setTimeout(function(){$('#error_msg').fadeOut();},10000); 
            }

        },
        complete: function (msg) {
            $('#loading_'+bill_no).css('display', 'none');
            $('#btEinvoice_'+bill_no).prop('disabled', false);
        },
        error: function (a, b, c) {
            console.log(a);
            console.log(b);
            console.log(c);
            $('#loading_'+bill_no).css('display', 'none');
            $('#btEinvoice_'+bill_no).prop('disabled', false);
        }
    });

}

let bill_arr = [];

function consolidate() {

    bill_arr = [];

    $('.bill_no_chk:checked').each(function () {
        let bill_no = $(this).attr('data-bill_no');
        bill_arr.push(bill_no);
    });

    if (bill_arr.length <= 0) {
        alert_error('.msg-print', 'No bill selected.', function(){});
        return false;
    }

    $('.bill_no_div').html(bill_arr.join(','));
    $('#confirm-modal').modal('show');

}

function do_consolidate()
{
    $('#confirm-modal').modal('hide');

    $.ajax({
        dataType: 'json',
        type: 'POST',
        data: {
            'bill_arr':bill_arr
        },
        url: base_url + 'ajax/consolidate_einvoice',
        beforeSend: function (data) {
            $('#loading').css('display', '');
            $('#btConsolidate').prop('disabled', true);
        },
        success: function (data) {
            if (data['succ'] == true) {
                $('.msg-print').prepend(`<div id="success" class="alert alert-success" role="alert">Consolidated E-Invoice created and sent.</div>`);
                setTimeout(function(){$('#success').fadeOut(); window.location.href=window.location.href; },2000); 
            } else {
                $('.msg-print').prepend(`<div id="error_msg" class="alert alert-danger" role="alert">Consolidated E-Invoice Sent Failed.<br>`+data['msg']+`</div>`);
                setTimeout(function(){$('#error_msg').fadeOut();},10000); 
            }

        },
        complete: function (msg) {
            $('#loading').css('display', 'none');
            $('#btConsolidate').prop('disabled', false);
        },
        error: function (a, b, c) {
            console.log(a);
            console.log(b);
            console.log(c);
            $('#loading').css('display', 'none');
            $('#btConsolidate').prop('disabled', false);
        }
    });
}

function submit_consolidated_einvoice(inv_no, elm)
{
    $.ajax({
        dataType: 'json',
        type: 'POST',
        data: {
            'inv_no':inv_no
        },
        url: base_url + 'ajax/send_consolidated_einvoice',
        beforeSend: function (data) {
            $('#loading_'+inv_no).css('display', '');
            $('#btEinvoice_'+inv_no).prop('disabled', true);
        },
        success: function (data) {
            if (data['succ'] == true) {
                $('.msg-print').prepend(`<div id="success" class="alert alert-success" role="alert">Consolidated E-Invoice `+inv_no+` Sent.</div>`);
                setTimeout(function(){$('#success').fadeOut(); $('#btFilter').click();},2000); 
            } else {
                $('.msg-print').prepend(`<div id="error_msg" class="alert alert-danger" role="alert">Consolidated E-Invoice `+inv_no+` Sent Failed.<br>`+data['msg']+`</div>`);
                setTimeout(function(){$('#error_msg').fadeOut();},10000); 
            }

        },
        complete: function (msg) {
            $('#loading_'+inv_no).css('display', 'none');
            $('#btEinvoice_'+inv_no).prop('disabled', false);
        },
        error: function (a, b, c) {
            console.log(a);
            console.log(b);
            console.log(c);
            $('#loading_'+inv_no).css('display', 'none');
            $('#btEinvoice_'+inv_no).prop('disabled', false);
        }
    });
}

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "einvoice/einvoice_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			txt_bill_date: $("#txt_bill_date").val(),
			sel_status: $("#sel_status").val(),
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

    $("#page_item_no").val(0);
    $("#txt_search").val('');
    $("#txt_bill_date").val(formatDate(start));
    $("#txt_bill_date").datepicker('setDate', formatDate(start));
    $("#sel_status").val('all');
    ajax_filter(1);
}