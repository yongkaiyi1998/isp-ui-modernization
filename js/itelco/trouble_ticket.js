let cur_tt_sof_id = $('#tt_sof_id').val();
let cur_tt_cof_id = $('#tt_cof_id').val();

let commentFormVisible = false;

autosize($('textarea[class*=autosize]'));

$( document ).ready(function() {
    formMode( mode );
    set_customer_detail( $('#customer_no').val() , pic_name );

	$('.file-uploader-block').each(function () {
		const $block = $(this);
		const uploaderId = $block.data('uploader-id');

		initFileUploader({
			inputSelector: $block.find('.attach-file-input'),
			containerSelector: $block.find('.file-container'),
			remarkDivSelector: `#remark-div-${uploaderId}`,
			modalSelector: `#attachment-remark-modal-${uploaderId}`,
			module: $block.find('.attach-file-input').data('module') || ''
		});
	});
});

function formMode(mode) {
	if (mode == 'view') {
		$('.owner-form input').prop('readonly', true);
		$('.owner-form select option:not(:selected)').prop('disabled', true);
		$('.owner-form textarea').prop('readonly', true);
		$('.owner-form button').prop('disabled', true);
	} else if (mode == 'owner') {
		$('.owner-form input').prop('readonly', false);
		$('.owner-form select option:not(:selected)').prop('disabled', false);
		$('.owner-form textarea').prop('readonly', false);
		$('.owner-form button').prop('disabled', false);
	} else if (mode == 'assign') {
		$('.owner-form input').prop('readonly', true);
		$('.owner-form select option:not(:selected)').prop('disabled', true);
		$('.owner-form textarea').prop('readonly', true);
		$('#tt_complaint_id').prop('disabled', true);
		$('#tt_category').prop('disabled', true);
		$('#pic_name').prop('disabled', true);
		$('.owner-form button').prop('disabled', true);
	}
}

function getDuration() {
	var dateStart = $('#datetime_open').val();
	var dateEnd = $('#datetime_close').val();
	var status = $('#status');
	var prevStatus = $('#prev_status').val();

	if (dateStart != "" && dateEnd != "") {
		var duration = "";//using moment.js
		var start = moment(dateStart, 'YYYY-MM-DD HH:mm:ss').unix();
		var end = moment(dateEnd, 'YYYY-MM-DD HH:mm:ss').unix();
		var delta = Math.abs(start - end);

		// calculate (and subtract) whole days
		var days = Math.floor(delta / 86400);
		delta -= days * 86400;
		// calculate (and subtract) whole hours
		var hours = Math.floor(delta / 3600) % 24;
		delta -= hours * 3600;
		// calculate (and subtract) whole minutes
		var minutes = Math.floor(delta / 60) % 60;
		delta -= minutes * 60;
		// what's left is seconds
		var seconds = delta % 60;  // in theory the modulus is not required
		$('#duration').val(days + " days " + hours + " hours " + minutes + " minutes");
	} else {
		$('#duration').val("");

		if ($('#tt_id').val() == '') {
			if ($('#tt_assign_to').val()) {
				status.val('2');
			} else {
				status.val('1');
			}
		}
	}
}

$("#datetime_open").datetimepicker({
	format: 'YYYY-MM-DD HH:mm:ss'
}).on('dp.change', function (ev) {
	$("#status").val('1');
	getDuration();
});

$("#datetime_close").datetimepicker({
	format: 'YYYY-MM-DD HH:mm:ss',
}).on('dp.change', function (ev) {
	$("#status").val('0');
	getDuration();
});

$(document).on("click", "#clear_customer", function (e) {
	$("#customer_no_name").val("");
	$("#customer_no").val("");
	$("#contact_no").val("");
	$("#customer_category").val("");
	$("#account_category").val("");
	$("#pic_name").html("<option value='' data-set=''>-- SELECT --</option>");
});

$(document).on("click", "#clear_parent_tt", function (e) {
	$("#parent_tt_no").val("");
	$("#parent_tt_id").val("");
});



$("#parent_tt_no").autocomplete({
	source: function (request, response) {
		$.ajax({
			type: "POST",
			url: base_url + "ticket/autocomplete_load_trouble_ticket",
			dataType: "json",
			data: {
				keyword: request.term,
				tt_id: $('#tt_id').val()
			},
			success: function (data) {
				var transformed = $.map(data, function (el) {
					return {
						label: el.tt_no,
						id: el.tt_no,
						val: el
					};
				});
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(xhr);
				console.log(ajaxOptions);
				console.log(thrownError);

			}
		});
	},
	messages: {
		noResults: '',
		results: function () { }
	},
	select: function (event, ui) {
		console.log(ui.item.val);
		$("#parent_tt_id").val(ui.item.val.tt_id);
	}

});


$("#customer_no_name").autocomplete({
	source: function (request, response) {
		$.ajax({
			type: "POST",
			url: base_url + "ajax/autocomplete_customer",
			dataType: "json",
			data: { keyword: request.term },
			success: function (data) {
				console.log(data);
				var transformed = $.map(data, function (el) {
					return {
						label: (el.customer_no + ' ' + el.name),
						id: el.customer_no,
						val: el
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
		results: function () { }
	},
	select: function (event, ui) {
		console.log(ui.item.val);
		$("#customer_no").val(ui.item.val.customer_no);

		var contact_num = "";
		if (ui.item.val.tel_num != '' && ui.item.val.mobile_num != '')
			contact_num = ui.item.val.mobile_num + ' / ' + ui.item.val.tel_num;
		else if (ui.item.val.mobile_num != '')
			contact_num = ui.item.val.mobile_num;
		else if (ui.item.val.tel_num != '')
			contact_num = ui.item.val.tel_num;

		//$( "#contact_no" ).val( contact_num );

		if (ui.item.val.product_category != '')
			$("#tt_category").val(ui.item.val.product_category);

		//set 
		set_customer_detail(ui.item.val.customer_no);

	}
});

function set_customer_detail(customer_no, pic_name) {

	$.ajax({
		type: "POST",
		url: base_url + "ticket/ajax_get_customer_detail",
		dataType: "json",
		data: {
			customer_no: customer_no,
			pic_name: pic_name
		},
		success: function (data) {
			//~ console.log( data );
			$("#pic_name").html(data['pic']);
			$("#contact_no").val(data['contact_no']);
			$("#customer_category").val(data['customer_category']);
			$("#account_category").val(data['customer_category_name']);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			response([]);
			console.log(thrownError);
		}
	});

}


$(document).on("change", "#pic_name", function (e) {
	var contact_no = $('#pic_name option:selected').attr('data-set');
	$('#contact_no').val(contact_no);
});

$(document).on("change", "#tt_sof_id", function (e) {
	if (mode == 'assign') {
		if (cur_tt_sof_id != $('#tt_sof_id').val()) {
			$('#btSave').prop('disabled', false);
		} else {
			$('#btSave').prop('disabled', true);
		}
	}
});

$(document).on("change", "#tt_cof_id", function (e) {
	if (mode == 'assign') {
		if (cur_tt_cof_id != $('#tt_cof_id').val()) {
			$('#btSave').prop('disabled', false);
		} else {
			$('#btSave').prop('disabled', true);
		}
	}
});

$(document).on("change", "#attach_file_input", function (e) {
	if (mode == 'assign') {
		$('#btSave').prop('disabled', false);
	}
});

$(document).on("change", "#status", function (e) {

	if (mode == 'assign') {
		if (($('#prev_status').val() != $('#status').val())) {
			$('#btSave').prop('disabled', false);
		} else {
			$('#btSave').prop('disabled', true);
		}
	}

	if ($(this).val() == '0') {
		var currentdate = new Date();
		var date_now = currentdate.getFullYear()
			+ "-"
			+ ((currentdate.getMonth() + 1) < 10 ? "0" + (currentdate.getMonth() + 1) : (currentdate.getMonth() + 1))
			+ "-"
			+ ((currentdate.getDate() + 1) < 10 ? "0" + (currentdate.getDate()) : currentdate.getDate())
			+ " "
			+ ((currentdate.getHours() + 1) < 10 ? "0" + (currentdate.getHours()) : currentdate.getHours())
			+ ":"
			+ ((currentdate.getMinutes() + 1) < 10 ? "0" + (currentdate.getMinutes()) : currentdate.getMinutes())
			+ ":"
			+ ((currentdate.getSeconds() + 1) < 10 ? "0" + (currentdate.getSeconds()) : currentdate.getSeconds())
		$("#datetime_close").val(date_now);

	} else {
		$("#datetime_close").val("");
	}

	getDuration();
});

$(document).on('click', '#add-comment-submit', function (e) {
	let recId = $('#new_comment_rec_id').val();
	let date = $('#new_comment_date').val();
	let remark = $('#new_comment').val();
	let reply_to = $('#selected-users .user-tag').map(function () {
      return $(this).data('value');
    }).get();

	if (confirm('Save this records?')) {
		if (remark == '') {
			$.gritter.add({
				title: 'ERROR',
				text: 'Comment cannot be empty!',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
			return;
		}

        $('#add-comment-modal').modal('hide');

		$.ajax({
			dataType: "json",
			type: "post",
			data: {
				tt_id: $('#tt_id').val(),
				rec_id: recId,
				date: date,
				remark: remark,
				reply_to: reply_to,
			},
			url: base_url + "ticket/ajax_save_job_tracking?" + Math.floor((Math.random() * 10000) + 1),
			success: function (data) {
				// console.log(data);
				if (data['success'] == 1) {
					let rec_id = data['rec_id'] ?? 0;
					alert('Saved.');
					location.reload();
					
					if(rec_id == 0) return;

					// send job tracking
					$.ajax({
						dataType: "script",
						type: "post",
						data: {
							rec_id: rec_id,
							tt_id: $('#tt_id').val(),
							tt_no: $('#tt_no').val(),
							date: date,
							remark: remark,
						},
						url: base_url + "ticket/send_job_tracking",
						success: function (data) {
						}
					});
				} else {
					alert('Failed to save.');
				}
			}
		});
	}


});

$(document).on('click', '.rec_del', function (e) {

	var recID = $(this).closest('tr').find('input[name*=\'rec_id\[\]\']').val();
	var rowIndex = $(this).closest('tr').index();
	if (recID == '') {
		if (confirm('Delete this records?'))
			$(this).closest('tr').remove();
	} else {
		if (confirm('Delete this records?')) {
			$.ajax({
				dataType: "json",
				type: "post",
				data: {
					tt_id: $('#tt_id').val(),
					rec_id: recID
				},
				url: base_url + "ticket/ajax_delete_job_tracking?" + Math.floor((Math.random() * 10000) + 1),
				success: function (data) {
					if (data['success'] == 1) {
						alert('Deleted');
						$('#job_tracking > tbody > tr').eq(rowIndex).remove();
					} else {
						alert('Failed to delete');
					}

				}
			});
		}
	}


});

$(document).on('change', '#tt_assign_to', function (e) {

	if ($('#tt_assign_to').val()) {
		$('#status').val('2');
	} else {
		$('#tt_email').val('');
		if ($('#datetime_open').val()) {
			$('#status').val('1');
		} else {
			$('#status').val('0');
		}
	}

	$.ajax({
		dataType: "json",
		type: "post",
		data: {tt_assign_to: $('#tt_assign_to').val()},
		url: base_url + "ticket/ajax_get_user_emails?" + Math.floor((Math.random() * 10000) + 1),
		success: function (data) {
			$('#tt_email').val(data['emails']);
		}
	});

});

$(document).on('click', '#btSave', function (e) {
	$('#btSave').prop('disabled', true);
	if( mode == 'assign' && commentFormVisible) formMode('owner');
	$('#ticket_detail').submit();
});

$('#ticket_detail').submit(function(e) {
	let reply_to = $('#selected-users .user-tag').map(function () {
      return $(this).data('value');
    }).get();

	e.preventDefault();

	$('.input-group-addon').parent().removeClass('has-error');

	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);
	
	try {
		let submitter = e.originalEvent.submitter.id;

		if(submitter == 'btDelete'){
			formData.append('btDelete', 1);
		}
	} catch (err) {

	}

	if(commentFormVisible){
		let remark = ($('#new_comment').val() || '').trim();
		if (remark == '') {
			$.gritter.add({
				title: 'ERROR',
				text: 'Comment cannot be empty!',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
			$('.button-group button').prop('disabled', false);
			return;
		}

		formData.append('new_comment_rec_id', $('#new_comment_rec_id').val());
		formData.append('new_comment_date', $('#new_comment_date').val());
		formData.append('new_comment', $('#new_comment').val());
		if (reply_to.length > 0) {
			reply_to.forEach(function (val) {
				formData.append('reply_to[]', val);
			});
		} else {
			formData.append('reply_to[]', '');
		}
	}

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
				hideLoadingIcon();
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			hideLoadingIcon();
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

$(document).on("click", "#add_job_tracking", function (e) {
	let dateTime = moment().format('YYYY-MM-DD HH:mm:00');
	$('#new_comment_date').val(dateTime);

	const $form = $('.new-comment-ui');
	const $button = $(this);
	const $icon = $button.find('i');
	const $label = $button.find('.label-text');

	$form.slideToggle(300).promise().done(function () {
		const isVisible = $form.is(':visible');
		commentFormVisible = isVisible;
		$form.find('textarea, select, input').prop('disabled', !isVisible);
		$icon.removeClass('fa-plus fa-minus').addClass(isVisible ? 'fa-minus' : 'fa-plus');
		$label.text(isVisible ? ' Hide form' : ' Click to add new record');
		if (mode == 'assign') {
			$('#btSave').prop('disabled', !isVisible);
		}
	});
});

// COMMENT PART START
var userOptions = [];

$(document).ready(function () {
	if (userOptions.length === 0) {
		$('#reply_to_select option').each(function () {
			userOptions.push({
				value: $(this).val(),
				text: $(this).text()
			});
		});
	}
	updateReplyToSelect();
});

function updateReplyToSelect() {
	const selectedValues = $('#selected-users .user-tag').map(function () {
		return $(this).data('value').toString();
	}).get();

	const select = $('#reply_to_select');
	const addButton = $('#add-reply-to');

	select.empty();

	userOptions.forEach(opt => {
		if (!selectedValues.includes(opt.value.toString())) {
			select.append($('<option>', {
				value: opt.value,
				text: opt.text
			}));
		}
	});

	if (selectedValues.includes('all')) {
		select.prop('disabled', true);
		addButton.prop('disabled', true);
	} else {
		select.prop('disabled', false);
		addButton.prop('disabled', false);
	}
}

$(document).on('click', '#add-reply-to', function () {
	const val = $('#reply_to_select').val();
	const text = $('#reply_to_select option:selected').text();

	if (!val || $('#selected-users .user-tag[data-value="' + val + '"]').length > 0) return;

	if (val === 'all') {
		$('#selected-users').empty();
	}

	const tag = `
		<span class="user-tag" data-value="${val}">
		${text}
		<span class="remove-user">&times;</span>
		</span>`;
	$('#selected-users').append(tag);

	updateReplyToSelect();
});

$(document).on('click', '.remove-user', function () {
	$(this).closest('.user-tag').remove();
	updateReplyToSelect();
});

$(document).on('click', '#add-comment-close', function () {
	$('#add-comment-modal').modal('hide');

	$('#new_comment').val('');
	$('#selected-users').empty();
	$('#reply_to_select').val('all').prop('disabled', false);
	$('#add-reply-to').prop('disabled', false);

	updateReplyToSelect();
});
//	COMMENT PART END

showLoadingIcon = function() {
	$('#loading-icon').show();
}

hideLoadingIcon = function() {
	$('#loading-icon').hide();
}

$(function () {
	let expanded = false;
	$('#toggle_tracking_rows').on('click', function () {
		if (expanded) {
			$('.tracking-row-hidden').slideUp(200);
			$(this).html('<i class="fa fa-chevron-down"></i>');
		} else {
			$('.tracking-row-hidden').slideDown(200);
			$(this).html('<i class="fa fa-chevron-up"></i>');
		}
		expanded = !expanded;
	});
});

function send_reply_to_customer() {
	if($('#customer_reply').val().trim() == '') {
		$.gritter.add({
			title: 'ERROR',
			text: 'Text message cannot be empty.',
			time: '5000',
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice',
		});
		return;
	}
	
	var formData = new FormData()
	formData.append('tt_id', $('#tt_id').val());
	formData.append('tt_no', $('#tt_no').val());
	formData.append('customer_no', $('#customer_no').val());
	formData.append('customer_reply', $('#customer_reply').val());

	$('#sending-loading-icon').show();
	$.ajax({
		dataType: 'json',
		url: base_url + 'ticket/ajax_send_reply_to_customer',
		type: 'POST',
		data: formData,
		success: function (data) {
			$('#sending-loading-icon').hide();
			$('#customer_reply').val('');
			$.gritter.add({
				title: 'SUCCESS',
				text: data.message,
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
		},
		error: function (data) {
			$('#sending-loading-icon').hide();
			$('#btSendToCustomer').prop('disabled', false);
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
}