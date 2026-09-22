
$( document ).ready(function() {
    $('.pagination:not(.pagination_ajax) li a').click(function () {
		var link = $(this).get(0).href;
		var form = $('form');
		var segments = link.split('/');
		
		console.log('testing');

		$('#page_item_no').val(segments[segments.length-1]);
		form.attr('action', link);
		form.submit();
		return false;
	});

	$('.filter-form').on('keydown', 'input:not([type=submit]):not([type=button])', function(e) {
		if (e.key === 'Enter') {
			e.preventDefault();
		}
	});
});


function alert_error(elm, msg, func) {
    $(elm).prepend(`<div id="error_msg" class="alert alert-danger" role="alert">`+msg+`</div>`);
    setTimeout(function(){$('#error_msg').fadeOut(); func; },10000); 
}

function alert_success(elm, msg, func) {
    $(elm).prepend(`<div id="success" class="alert alert-success" role="alert">`+msg+`</div>`);
    setTimeout(function(){$('#success').fadeOut(); func(); },2000); 
}

function handle_ajax_error(ajax_data) {
	$.gritter.add({
	    title: 'ERROR',
	    text: ajax_data['err_msg'],
	    time: '10000',
	    close_icon: 'l-arrows-remove s16',
	    class_name: 'info-notice',
	});

	for (const [e_key, e_value] of Object.entries(ajax_data['error_keys'])) {
		let string=e_key.replace(/([^\w \\])/g,'\\$1');
		//console.log(string);
		$('#'+string).parent().addClass('has-error');
	}

	window.scrollTo(0, 0);
}

function pagination_link(page_no) {
	$('#page_item_no').val(page_no);
	ajax_filter();
}

function ajax_clear_session(module, filter_session_name) {
	if(!module || !filter_session_name) {
		alert_error('#main', 'Module and filter session name are required.');
		return;
	}
	
	$.ajax({
		type: 'GET',
		url: base_url + module+'/clear_filter_session/'+filter_session_name,
		dataType: 'json',
		success: function(data) {
			if (data['status'] === 'success') {
				return;
			} else {
				handle_ajax_error(data);
			}
		},
		error: function() {
			alert_error('#main', 'An error occurred while clearing the session.');
		}
	});
}