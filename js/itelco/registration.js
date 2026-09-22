$(document).ready(function () {
	init();

	/*if( $('#customer_no').val() == '' ){
		$('#btSave').prop('disabled', true);
	}

	set_nationality( $( '#nationality_dd' ).val() , true );*/

	$('[data-toggle="tooltip"]').tooltip({
		html: true
	});

	$('#login_username, #login_password').on('blur', function () {
		validate_username();
	});

	$('#tin, #icno, #ssm').on('blur', function () {
		let icno = $('#icno').val().trim();
		let ssm = $('#ssm').val().trim();
		let tin = $('#tin').val().trim();

		if (tin !== '') {
			$('.tin-loading').css('display', 'block');
			$('.tin-invalid').css('display', 'none');
			$('.tin-valid').css('display', 'none');

			let idValue = $('input[name="type"]:checked').val() == 'r'
				? icno
				: ssm;
			let idType = $('input[name="type"]:checked').val() == 'r' ? 'NRIC' : 'BRN';

			ajax_validate_tin(tin, idValue, idType);
		}
	});

	$('.btn-generate-password').on('click', function () {
		let length = 8;
		let charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		let password = "";
		for (var i = 0; i < length; i++) {
			var randomIndex = Math.floor(Math.random() * charset.length);
			password += charset[randomIndex];
		}

		$('#login_password').val(password);

		validate_username();
	});
});

function init() {
	/*$("#date_of_birth").datepicker({format: 'yyyy-mm-dd'});
	//$("#signup_date").datepicker({format: 'yyyy-mm-dd'});
	
	//~ var dateToday = new Date(); 
	//~ $( "#signup_date" ).datepicker({
		//~ format: 'yyyy-mm-dd',
		//~ numberOfMonths: 1,
		//~ showButtonPanel: true,
		//~ startDate: dateToday
	//~ });

	var nowTemp = new Date();
	var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
	now.setDate(now.getDate() - 3);

	var signup_date = $('#signup_date').datepicker({
		format: 'yyyy-mm-dd',
		numberOfMonths: 1,
		showButtonPanel: true,
		//~ beforeShowDay: function (date) {
			//~ return date.valueOf() >= now.valueOf();			
		//~ },
		autoclose: true

	}).on('changeDate', function (ev) {
		
		if( activated_date.datepicker("getDate").valueOf() ){
			if( activated_date.datepicker("getDate").valueOf() < ev.date.valueOf() ){
				$('#signup_date_err').text('Activated Date shall not be earlier than Signup Date '  + $('#activated_date').val() + '. Signup Date has been updated to ' + $('#signup_date').val() );
				var newDate = new Date(ev.date);
				newDate.setDate(newDate.getDate());
				activated_date.datepicker("update", newDate);
			}else if( activated_date.datepicker("getDate").valueOf() >= ev.date.valueOf() ){
				$('#signup_date_err').text('');
				var origin = $('#activated_date').val();
				var newDate = new Date(ev.date);
				newDate.setDate(newDate.getDate());
				activated_date.datepicker("update", newDate);
				activated_date.datepicker("setDate", origin )
			}
		}
	});

	var activated_date = $('#activated_date').datepicker({
		format: 'yyyy-mm-dd',
		beforeShowDay: function (date) {
			if (!signup_date.datepicker("getDate").valueOf()) {
				return date.valueOf() >= new Date().valueOf();
			} else {
				return date.valueOf() > signup_date.datepicker("getDate").valueOf();
			}
		},
		autoclose: true
	}).on('changeDate', function (ev) {
		if( signup_date.datepicker("getDate").valueOf() ){
			if( ev.date.valueOf() < signup_date.datepicker("getDate").valueOf() ){
				$('#signup_date_err').text('Activated Date shall not be earlier than Signup Date '  + $('#activated_date').val() + '. Signup Date has been updated to ' + $('#signup_date').val() );
			}else if( ev.date.valueOf() >= signup_date.datepicker("getDate").valueOf() ){
				$('#signup_date_err').text('');
			}
		}
	});
	
	
	
	//$("#activated_date").datepicker({format: 'yyyy-mm-dd'});
	$("#suspended_date").datepicker({format: 'yyyy-mm-dd'});
	$("#terminated_date").datepicker({format: 'yyyy-mm-dd'});
	
	toggleCompanySection();
	load_package_detail();
	show_billing_address();
	set_nric('');*/

	if (inDetail) chk_rb(); // only work in detail page

	if ($('#from_time').length) {
		$('#from_time, #from_block, #to_time, #to_block').on('change', updateTimeOptions);
		updateTimeOptions;
	}

	$('#same_with_billing').change(function () {
		if ($('input[name="same_with_billing"]:checked').val() == '1') {
			$('.ins_addr').attr('readonly', 'readonly');
			$('#ins_state').attr('disabled', 'disabled');

			$('#ins_unit_no').val($('#bill_unit_no').val());
			$('#ins_addr_1').val($('#bill_addr_1').val());
			$('#ins_addr_2').val($('#bill_addr_2').val());
			$('#ins_addr_3').val($('#bill_addr_3').val());
			$('#ins_postcode').val($('#bill_postcode').val());
			$('#ins_city').val($('#bill_city').val());
			$('#ins_state').val($('#bill_state').val());
		} else {
			$('.ins_addr').removeAttr('readonly');
			$('#ins_state').removeAttr('disabled');
		}
	});

	$('#registration_detail').submit(async function (e) {
		$('.button-group button').prop('disabled', true);

		e.preventDefault();
		let submitter = e.originalEvent.submitter.id;
		let result = true;

		if (submitter == 'btSOProfile') {
			let $firstChecked = $('.pkg_checkbox:checked').first();
			let package_id = $firstChecked.attr('id');
			let package_name = $firstChecked.closest('.col-lg-12').find('.pkg_checkbox_name').text().trim();

			let confirm_message = '';
			if (package_name != '') {
				confirm_message = 'Create sales order and profile with the selected package: ' + package_name + '?';
			} else {
				confirm_message = 'Create sales order and profile without selecting a package?';
			}

			let icssmExist = await ajax_check_icno_and_ssm();

			if (icssmExist) {
				confirm_message = 'This IC/SSM already exists in the system. \nCreating a new profile will link to the existing customer. \n\n' + confirm_message;
			}
			result = confirm(confirm_message);
		}

		if (!result) {
			$('.button-group button').prop('disabled', false);
			return;
		}

		$('#task').val(submitter);

		$('#ins_state').removeAttr('disabled');

		//interested
		construct_interested();

		//$(this).unbind('submit').submit();

		let formData = new FormData(this);

		$('.input-group-addon').parent().removeClass('has-error');

		showLoadingIcon();

		$.ajax({
			dataType: 'json',
			url: $(this).attr('action'),
			type: 'POST',
			data: formData,
			success: function (data) {
				//alert(data)

				if (data['status'] == 'ER') {

					handle_ajax_error(data);

					$('.button-group button').prop('disabled', false);
					hideLoadingIcon();

				} else {
					window.location.href = base_url + data['url'];
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
			complete: function (data) {
				$('.button-group button').prop('disabled', false);
				hideLoadingIcon();
			},
			cache: false,
			contentType: false,
			processData: false
		});

	});

	$("#specific_date").datepicker({
		format: 'yyyy/mm/dd',
		showButtonPanel: true,
		autoclose: true
	});

	$("#preferred_install_date").datepicker({
		format: 'yyyy/mm/dd',
		showButtonPanel: true,
		autoclose: true
	});

	window.addEventListener("pageshow", function (event) {
		var historyTraversal = event.persisted ||
			(typeof window.performance != "undefined" &&
				window.performance.navigation.type === 2);
		if (historyTraversal) {
			// Handle page restore.
			window.location.reload();
		}
	});

}

showLoadingIcon = function () {
	$('#loading-icon').show();
}

hideLoadingIcon = function () {
	$('#loading-icon').hide();
}

function validate_username() {
	let $username = $('#login_username');
	let login_username = $.trim($username.val());
	let password = $.trim($('#login_password').val());

	$('.username-valid, .username-invalid, .username-fail-connection, .username-loading').hide();

	if (!login_username) {
		if (password) {
			$('.username-invalid').show();
			if (!$('#btSave').prop('disabled')) {
				$('#btSave, #btSO, #btSOProfile').prop('disabled', true);
				$.gritter.add({
					title: 'ERROR',
					text: 'Save button disabled due to invalid username.',
					time: '5000',
					close_icon: 'l-arrows-remove s16',
					class_name: 'info-notice',
				});
			}
		}
		return;
	}

	$('.username-loading').show();
	$username.prop('disabled', true);

	$.ajax({
		type: 'POST',
		url: base_url + 'ajax/ajax_verify_radius_account',
		dataType: 'json',
		data: {
			login_username: login_username
		},
		success: function (data) {
			if (data.exist == 1) {
				$('.username-invalid').show();
				$('#btSave, #btSO, #btSOProfile').prop('disabled', true);
				$.gritter.add({
					title: 'ERROR',
					text: 'Save button disabled due to invalid username.',
					time: '5000',
					close_icon: 'l-arrows-remove s16',
					class_name: 'info-notice',
				});
			} else {
				$('.username-valid').show();
				$('#btSave').prop('disabled', false);
				$('#btSO').prop('disabled', false);
				$('#btSOProfile').prop('disabled', false);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			$('.username-fail-connection').show();
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		},
		complete: function () {
			$('.username-loading').hide();
			$username.prop('disabled', false);
		},
	});
}

function call_back(action_url) {
	// var action = "customer/customer_filtered/" + layout;
	$("#registration").attr("action", action_url);
	$('#registration').submit();
}

function ajax_filter(filter_pressed = 0) {
	//post values

	if (filter_pressed == 1) {
		$("#page_item_no").val(0);
	}

	$.ajax({
		type: "POST",
		url: base_url + "registration/registration_rows/0",
		data: {
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			sel_status: $("#sel_status").val(),
			sel_installation: $("#sel_installation").val(),
			sel_building: $("#sel_building").val(),
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
	$("#sel_status").val('P');
	$("#sel_installation").val('all');
	$("#sel_building").val('all');
	ajax_filter(1);
}

function chk_rb() {
	let idValue = '';
	let idType = '';
	if ($('input[name="type"]:checked').val() == 'r') {
		$('.b_fields').css('display', 'none');
		idValue = $('#icno').val().trim();
		idType = 'NRIC';
	} else {
		$('.b_fields').css('display', '');
		idValue = $('#ssm').val().trim();
		idType = 'BRN';
	}

	if ($('#tin').val() !== '') {
		ajax_validate_tin($('#tin').val().trim(), idValue, idType);
	}

	load_package_list();
}

// function to24Hour(hour12, block) {
// 	if (isNaN(hour12)) return NaN;
// 	var h = hour12 % 12;
// 	if (block === 'pm') h += 12;
// 	return h;
// }

// function updateTimeOptions() {
// 	var $fromTime = $('#from_time');
// 	var $fromBlock = $('#from_block');
// 	var $toTime = $('#to_time');
// 	var $toBlock = $('#to_block');

// 	if (!$fromTime.length || !$toTime.length) return;
// 	$fromTime.add($toTime).find('option').prop('disabled', false);

// 	var fromTime = parseInt($fromTime.val());
// 	var fromBlock = $fromBlock.val();
// 	var toTime = parseInt($toTime.val());
// 	var toBlock = $toBlock.val();

// 	if (isNaN(fromTime) || isNaN(toTime)) return;

// 	var fromHour24 = to24Hour(fromTime, fromBlock);
// 	var toHour24 = to24Hour(toTime, toBlock);

// 	if (toHour24 <= fromHour24)
// 		toHour24 = fromHour24 + 1;
// 	if (toHour24 > 23) {
// 		fromHour24 = 22;
// 		toHour24 = 23;
// 	}
// 	if (fromHour24 >= toHour24) {
// 		fromHour24 = toHour24 - 1;
// 		if (fromHour24 < 0) fromHour24 = 0;
// 	}

// 	var fromBlockNew = fromHour24 >= 12 ? 'pm' : 'am';
// 	var fromHour12 = fromHour24 % 12;
// 	if (fromHour12 === 0) fromHour12 = 12;
// 	var toBlockNew = toHour24 >= 12 ? 'pm' : 'am';
// 	var toHour12 = toHour24 % 12;
// 	if (toHour12 === 0) toHour12 = 12;

// 	$fromBlock.val(fromBlockNew);
// 	$fromTime.val(fromHour12);
// 	$toBlock.val(toBlockNew);
// 	$toTime.val(toHour12);

// 	$toTime.find('option').each(function () {
// 		var h = to24Hour(parseInt($(this).val()), $toBlock.val());
// 		$(this).prop('disabled', h <= fromHOur24);
// 	});
// 	$fromTime.find('option').each(function () {
// 		var h = to24Hour(parseInt($(this).val()), $fromBlock.val());
// 		$(this).prop('disabled', h >= toHour24);
// 	})
// }

function to24Hour(hour, block) {
	if (hour === 12) return block === 'am' ? 0 : 12;
	return block === 'pm' ? hour + 12 : hour;
}

function updateTimeOptions() {
	var fromTime = parseInt($('#from_time').val());
	var fromBlock = $('#from_block').val();
	var toTime = parseInt($('#to_time').val());
	var toBlock = $('#to_block').val();

	var fromHour24 = to24Hour(fromTime, fromBlock);
	var toHour24 = to24Hour(toTime, toBlock);

	if (toHour24 <= fromHour24) {
		toHour24 = fromHour24 + 1;
	}

	if (toHour24 > 23) {
		fromHour24 = 22;
		toHour24 = 23;
	}

	if (fromHour24 >= toHour24) {
		fromHour24 = toHour24 - 1;
		if (fromHour24 < 0) fromHour24 = 0;
	}

	var fromBlockNew = fromHour24 >= 12 ? 'pm' : 'am';
	var fromHour12 = fromHour24 % 12;
	if (fromHour12 === 0) fromHour12 = 12;

	var toBlockNew = toHour24 >= 12 ? 'pm' : 'am';
	var toHour12 = toHour24 % 12;
	if (toHour12 === 0) toHour12 = 12;

	$('#from_block').val(fromBlockNew);
	$('#from_time').val(fromHour12);
	$('#to_block').val(toBlockNew);
	$('#to_time').val(toHour12);

	$('#to_time option, #from_time option').prop('disabled', false);

	$('#to_time option').each(function () {
		var h = to24Hour(parseInt($(this).val()), $('#to_block').val());
		$(this).prop('disabled', h <= fromHour24);
	});

	$('#from_time option').each(function () {
		var h = to24Hour(parseInt($(this).val()), $('#from_block').val());
		$(this).prop('disabled', h >= toHour24);
	});
}

function autoFillAddress() {

	if ($('#building_no').val() == '0') {
		return false;
	}

	$.ajax({
		dataType: 'json',
		type: 'POST',
		data: { 'building_no': $('#building_no').val() },
		url: base_url + 'ajax/get_building_details',
		beforeSend: function (data) {
		},
		success: function (data) {
			var building = data;
			$('#ins_addr_1').val(building['addr_1']);
			$('#ins_addr_2').val(building['addr_2']);
			$('#ins_addr_3').val(building['addr_3']);
			$('#ins_city').val(building['city']);
			$('#ins_state').val(building['state']);
			$('#ins_postcode').val(building['postcode']);

			$('#bill_addr_1').val(building['addr_1']);
			$('#bill_addr_2').val(building['addr_2']);
			$('#bill_addr_3').val(building['addr_3']);
			$('#bill_city').val(building['city']);
			$('#bill_state').val(building['state']);
			$('#bill_postcode').val(building['postcode']);

		},
		complete: function (msg) {
		},
		error: function (a, b, c) {
			console.log(a);
			console.log(b);
			console.log(c);
		}
	});
}

function load_package_list() {
	//get package list

	let selected_package = [];
	try {
		selected_package = JSON.parse($('#interested').val());
	} catch (e) {

	}

	$.ajax({
		type: "POST",
		url: base_url + "registration/load_package_list",
		dataType: "json",
		data: {
			package_type: $('input[name="type"]:checked').val(),
			building: $('#building_no').val() ?? '',
		},
		success: function (data) {
			$('.interested_list').html('');
			$.each(data, function () {

				let checked = ``;
				if (Object.values(selected_package).indexOf(this.package_no) > -1) {
					checked = `checked="checked"`;
				}

				let interested_html = `
					<div class="col-lg-12">
						<div class="pkg_checkbox_name col-lg-6 col-xs-8">`+ this.name + `</div>
						<div class="col-lg-6 col-xs-4">
							<div class="input-group">					
								<input 
									id			="pkg_`+ this.package_no + `" 
									name		="pkg_`+ this.package_no + `" 
									value		="`+ this.package_no + `"
									type		="checkbox" 
									class		="pkg_checkbox" `+ checked + ` 
									 />
							</div>
						</div>
					</div>
					`;

				$('.interested_list').append(interested_html);
			});
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
	});
}

function construct_interested() {

	let interested_arr = [];

	$('.pkg_checkbox').each(function (index, data) {
		if ($(this).is(':checked')) {
			interested_arr.push($(this).val());
		}
	});

	$('#interested').val(JSON.stringify(interested_arr));

}

function ajax_validate_tin(tin, idValue, idType) {
	$.ajax({
		type: 'POST',
		url: base_url + 'ajax/validate_tin',
		data: {
			tin: tin,
			idValue: idValue,
			idType: idType
		},
		dataType: 'json',
		success: function (response) {
			$('.tin-loading').css('display', 'none');
			$('.tin-invalid').css('display', 'none');
			$('.tin-valid').css('display', 'none');
			$('.fail-connection').css('display', 'none');

			if (response.connected == false) {
				$('.fail-connection').css('display', 'block');
			} else if (response.is_valid) {
				$('.tin-valid').css('display', 'block');
			} else {
				$('.tin-invalid').css('display', 'block');
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.error(xhr, ajaxOptions, thrownError);
		}
	});
}

async function ajax_check_icno_and_ssm() {
	let url = '';
	let payload = { so_id: '' };

	if ($('input[name="type"]:checked').val() === 'r') {
		url = base_url + 'salesorder/ajax_check_icno';
		payload.icno = $('#icno').val() || '';
	} else {
		url = base_url + 'salesorder/ajax_check_ssm';
		payload.ssm = $('#ssm').val() || '';
	}

	try {
		const parsed = await $.ajax({
			type: 'POST',
			url: url,
			data: payload,
			dataType: 'json'
		});

		return !!parsed.exist;

	} catch (xhr) {
		console.error("AJAX Error:", xhr.statusText);
		return false;
	}
}