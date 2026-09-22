function init() {
	// if (is_edit == '0') $('#email_cust_cat').val('r');
	if(send_by != 'individual') {
		initialize_cbox();
	}
	initialize_date();
	initialize_actions();

	if (disable_input == '1') {
		$('input').prop("disabled", true);
		$('textarea').prop("disabled", true);
		$('select').prop("disabled", true);
		$('input[name=scheduler_id]').prop('disabled', false);
		$('#btDelete').addClass('disabled');
		$('#btSave').addClass('disabled');

	}
	else {
		$('input').prop("disabled", false);
		$('textarea').prop("disabled", false);
		$('select').prop("disabled", false);
		$('input[name=scheduler_id]').prop('disabled', false);
		$('#btDelete').removeClass('disabled');
		$('#btSave').removeClass('disabled');

		email_autocomplete();
		customer_email_autocomplete();
	}

	get_template_name();

}

function initialize_cbox() {
	str_1 = $('#email_cust_cat_' + send_by).val();
	sms_cust_cat = str_1.split(",");
	sms_cust_cat_length = sms_cust_cat.length;

	for (scc = 0; scc < sms_cust_cat_length; scc++) {
		cust_cat_data_id = sms_cust_cat[scc];
		$('*[class="cust_cat_' + send_by + '"][data-id="' + cust_cat_data_id + '"]').prop("checked", true);
	}

	str_2 = $('#email_cust_status_' + send_by).val();
	sms_cust_status = str_2.split(",");
	sms_cust_status_length = sms_cust_status.length;

	for (scs = 0; scs < sms_cust_status_length; scs++) {
		cust_status_data_id = sms_cust_status[scs];
		$('*[class="cust_status_' + send_by + '"][data-id="' + cust_status_data_id + '"]').prop("checked", true);
	}
}
//====================================================================== datetimepicker
function initialize_date() {
	$("#email_schedule_on").datetimepicker({
		format: 'YYYY-MM-DD HH:mm',
		sideBySide: true,
	});
}

//====================================================================== actions
function initialize_actions() {
	// building customer filter
	$('.cust_cat_building').click(function () {

		cbox_cat_length = $('.cust_cat_building').length;
		final_cust_cat_building = '';
		for (cc = 0; cc < cbox_cat_length; cc++) {
			data_id = $('.cust_cat_building').eq(cc).attr('data-id');
			if ($('.cust_cat_building').eq(cc).prop('checked') == true) {
				final_cust_cat_building = final_cust_cat_building + data_id + ',';
			}
		}
		$('#email_cust_cat_building').val(final_cust_cat_building);
	});

	$('.cust_status_building').click(function () {

		cbox_status_length = $('.cust_status_building').length;
		final_cust_status_building = '';
		for (cs = 0; cs < cbox_status_length; cs++) {
			data_id = $('.cust_status_building').eq(cs).attr('data-id');
			if ($('.cust_status_building').eq(cs).prop('checked') == true) {
				final_cust_status_building = final_cust_status_building + data_id + ',';
			}
		}
		$('#email_cust_status_building').val(final_cust_status_building);
	});

	// area customer filter
	$('.cust_cat_area').click(function () {

		cbox_cat_length = $('.cust_cat_area').length;
		final_cust_cat_area = '';
		for (cc = 0; cc < cbox_cat_length; cc++) {
			data_id = $('.cust_cat_area').eq(cc).attr('data-id');
			if ($('.cust_cat_area').eq(cc).prop('checked') == true) {
				final_cust_cat_area = final_cust_cat_area + data_id + ',';
			}
		}
		$('#email_cust_cat_area').val(final_cust_cat_area);
	});

	$('.cust_status_area').click(function () {

		cbox_status_length = $('.cust_status_area').length;
		final_cust_status_area = '';
		for (cs = 0; cs < cbox_status_length; cs++) {
			data_id = $('.cust_status_area').eq(cs).attr('data-id');
			if ($('.cust_status_area').eq(cs).prop('checked') == true) {
				final_cust_status_area = final_cust_status_area + data_id + ',';
			}
		}
		$('#email_cust_status_area').val(final_cust_status_area);
	});

	// all customer filter
	$('.cust_cat_all').click(function () {

		cbox_cat_length = $('.cust_cat_all').length;
		final_cust_cat_area = '';
		for (cc = 0; cc < cbox_cat_length; cc++) {
			data_id = $('.cust_cat_all').eq(cc).attr('data-id');
			if ($('.cust_cat_all').eq(cc).prop('checked') == true) {
				final_cust_cat_area = final_cust_cat_area + data_id + ',';
			}
		}
		$('#email_cust_cat_all').val(final_cust_cat_area);
	});

	$('.cust_status_all').click(function () {

		cbox_status_length = $('.cust_status_all').length;
		final_cust_status_area = '';
		for (cs = 0; cs < cbox_status_length; cs++) {
			data_id = $('.cust_status_all').eq(cs).attr('data-id');
			if ($('.cust_status_all').eq(cs).prop('checked') == true) {
				final_cust_status_area = final_cust_status_area + data_id + ',';
			}
		}
		$('#email_cust_status_all').val(final_cust_status_area);
	});	

	$('.clear_field').click(function () {
		$('#email_autocomplete').val('');
		$('.clear_field_group').hide();
	});
}
//====================================================================== ajax

function email_autocomplete() {
	console.log('||===> func: email_autocomplete()');
	console.log('ajax: ->email_autocomplete');
	$('#email_autocomplete').autocomplete({
		source: function (request, response) {
			$.ajax({
				url: base_url + "/ajax_set_data",
				dataType: "json",
				data: { query: request.term },
				success: function (data) {
					console.log(data);

					var transformed = $.map(data, function (el) {
						real_val = el;
						// console.log(real_val);
						return {
							label: el.email_title + ' (created on : ' + el.email_schedule_on + ')',
							id: el.scheduler_id,
							realValue: real_val,
						};
					});
					response(transformed);
				},
				error: function () {
					response([]);
				}
			});
		},
		messages:
		{
			noResults: '',
			results: function () { }
		},
		select: function (event, ui) {
			// console.log(ui.item);
			var set_field_result = set_field_value(ui.item);

			// console.log('if successfully populate, clear text within');
			if (set_field_result != 0) $(this).val('');
			return false;
		}
	});
	console.log('ajax: <-email_autocomplete');
	console.log('<===|| func: email_autocomplete()');
}

set_field_value = function (object) {
	console.log('||===> funcVar: set_field_value');
	var scheduler_id = '0';
	//console.log(object);
	$.each(object, function (item, value) {
		// console.log(item);
		if (item == 'realValue' || item == '0') {
			// console.log(value);
			$.each(value, function (data_key, data_val) {
				//return id show that it is selected
				if (data_key == 'scheduler_id') scheduler_id = data_val;

				//populate input sms_title
				if (data_key == 'email_title') $('input[name=email_title]').val(data_val);

				//populate textarea sms_msg
				if (data_key == 'email_msg') $('textarea[name=email_msg]').val(data_val);
			});
		}
	});
	console.log('<===|| funcVar: set_field_value return:' + scheduler_id);
	return scheduler_id;
}

function customer_email_autocomplete() {
	console.log('||===> func: customer_email_autocomplete()');
	console.log('ajax: ->customer_email_autocomplete');
	$('#customer_email_autocomplete').autocomplete({
		source: function (request, response) {
			$.ajax({
				url: base_url + "/ajax_get_customer_email",
				dataType: "json",
				data: { customer_no: request.term },
				success: function (data) {

					console.log(data);

					var transformed = $.map(data['row'], function (el) {
						real_val = el;
						// console.log(real_val);
						return {
							label: el.customer_no + ' ' + el.name,
							id: el.customer_no,
							row: real_val,
						};
					});
					response(transformed);
				},
				error: function () {
					response([]);
				}
			});
		},
		messages:
		{
			noResults: '',
			results: function () { }
		},
		select: function (event, ui) {
			console.log(ui.item);
			$('#customer_no').val(ui.item.row['customer_no']);
			//var set_field_result = set_field_value(ui.item);
			if (ui.item.row['pic_email_1'] != '') {
				if ($('#recipient_emails').val() != '') {
					$('#recipient_emails').val($('#recipient_emails').val() + ' ; ' + ui.item.row['pic_email_1']);
				} else {
					$('#recipient_emails').val(ui.item.row['pic_email_1']);
				}
			}

			if (ui.item.row['pic_email_2'] != '') {
				if ($('#recipient_emails').val() != '') {
					$('#recipient_emails').val($('#recipient_emails').val() + ' ; ' + ui.item.row['pic_email_2']);
				} else {
					$('#recipient_emails').val(ui.item.row['pic_email_2']);
				}
			}

			$(this).val('');

			// console.log('if successfully populate, clear text within');
			//if(set_field_result != 0) $(this).val('');
			return false;
		}
	});
	console.log('ajax: <-email_autocomplete');
	console.log('<===|| func: email_autocomplete()');
}

function showFormType() {
	if ($('input[name=send_by]:checked').val() == 'building') {
		$('#individual_form').hide();
		$('#building_form').show();
		$('#area_form').hide();
		$('#all_form').hide();
		send_by = 'building';
	} else if ($('input[name=send_by]:checked').val() == 'individual') {
		$('#individual_form').show();
		$('#building_form').hide();
		$('#area_form').hide();
		$('#all_form').hide();
		send_by = 'individual';
	} else if ($('input[name=send_by]:checked').val() == 'area') {
		$('#individual_form').hide();
		$('#building_form').hide();
		$('#area_form').show();
		$('#all_form').hide();
		send_by = 'area';
	} else if ($('input[name=send_by]:checked').val() == 'all') {
		$('#individual_form').hide();
		$('#building_form').hide();
		$('#area_form').hide();
		$('#all_form').show();
		send_by = 'all';
	}
}

$(document).on('change', 'input[name=send_by]', function (e) {
	showFormType();
});

function get_template_name() {
	const selectedTemplate = document.getElementById('template_list');
	const selectedText = selectedTemplate.options[selectedTemplate.selectedIndex].text;
	if (selectedText.includes('AUTO BILLING EMAIL')) {
		$('#customer_no_info').toggle(true);
		$('#btSave').attr('type', 'button');
		$('#btSave').attr('data-toggle', 'modal');
		$('#btSave').attr('data-target', '#confirm_schedule_auto_billing');
		$('#email_attachment').prop('disabled', true);
	} else {
		$('#customer_no_info').toggle(false);
		$('#btSave').attr('type', 'submit');
		$('#btSave').removeAttr('data-target');
		$('#btSave').removeAttr('data-toggle');
		$('#email_attachment').prop('disabled', false);
	}
}

function submitForm() {
	if($('#email_schedule_on').val() == '') {
		$.gritter.add({
			title: 'ERROR',
			text: 'The Schedule On field is required.',
			time: '5000',
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice'
		});
		$('#confirm_schedule_auto_billing').modal('hide');
	} else {
		$('#confirm_schedule_auto_billing_close').prop('disabled', true);
		$('#auto_billing_submit_button').prop('disabled', true);
		$('#email_detail').submit();
	}	
}

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "email/schedule_list_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
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
	$("#page_item_no").val(0);
	$("#txt_search").val('');
	ajax_filter(1);
}
