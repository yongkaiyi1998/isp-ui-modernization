let statusChange = false;
let instAddressObj = {
    building: '',
    inst_unit_no: '',
    inst_addr1: '',
    inst_addr2: '',
    inst_addr3: '',
    inst_city: '',
    inst_postcode: '',
    inst_state: ''
};

$(document).ready(function () {
	init();
	auto_gen_dia();

	if(new_effective_date_empty) {
		$('#old_package').hide();
		$('#new_effective_date').hide();
	} else {
		$('#old_package').show();
		$('#new_effective_date').show();
		$('#new_package_effective_date').prop('disabled', true);
		if(!effective_date_not_reached) {
			$('#package').prop('disabled', true);
		}
	}

	if($('#prev_status').val() == 'S' || $('#prev_status').val() == 'T' || $('#prev_status').val() == 'C') {
		$('#package').prop('disabled', true);
	}

	if ($('#customer_no').val() == '') {
		$('#btSave').prop('disabled', true);
	}

	// if it is not a new account and the account status is active, then the contract month is read only
	if(new_account == '0' && $('#current_status').val() == 'A') {
		$('#contract_month').prop('readonly', true);
		$('#bill_waive_period').prop('readonly', true);
		$('#free_package_upgrade').prop('readonly', true);
		$('#upgrade_package_id').prop('disabled', true);
	}

	set_nationality($('#nationality_dd').val(), true);

	$('#current_status').on('change', function(){
		let prev_status = $('#prev_status').val() || '';
		let current_status = $(this).val() || '';
		if(prev_status != current_status){
			statusChange = true;
		}else{
			statusChange = false;
		}
	});

	$('#sel_status').on('change', function(){
		$(this).val() === 'P' ? 
			$('#select-installation').show() : 
			$('#select-installation').hide();
		if ($(this).val() !== 'P') 
			$('#sel_installation').val('all');
	});

	$('#packageHistoryModal').on('hidden.bs.modal', function () {
		$('#packageHistoryPopupContent').empty();
	});

	$('#statusHistoryModal').on('hidden.bs.modal', function () {
		$('#statusHistoryPopupContent').empty();
	});
});

function init() {
	$("#date_of_birth").datepicker({ format: 'yyyy-mm-dd' });
	$("#new_package_effective_date").datepicker({
		format: 'yyyy-mm-dd',
		startDate: '+0d'
	});

	$("#preferred_install_date").datepicker({
		format: 'yyyy/mm/dd',
		showButtonPanel: true,
		autoclose: true
	});
	//$("#signup_date").datepicker({format: 'yyyy-mm-dd'});

	//~ var dateToday = new Date(); 
	//~ $( "#signup_date" ).datepicker({
	//~ format: 'yyyy-mm-dd',
	//~ numberOfMonths: 1,
	//~ showButtonPanel: true,
	//~ startDate: dateToday
	//~ });

	if((new_account == '0' && (new_effective_date_empty || effective_date_not_reached))) {
		const package_id = $('#package').val();
		$('#package').on('change', function (e) {
			if($('#prev_status').val() == 'A') {
				if(new_effective_date_empty) {
					if(package_id != $(this).val()) {
						$('#old_package').show();
						$('#new_effective_date').show();
					} else {
						$('#old_package').hide();
						$('#new_effective_date').hide();
					}
				}

				if(package_id != $(this).val()) {
					$('#contract_month').prop('readonly', false);
				} else {
					$('#contract_month').val(old_contract_month);
					if($('#current_status').val() == 'A') {					
						$('#contract_month').prop('readonly', true);
					}
				}								
			}
			$('#bill_waive_period').prop('readonly', false);
			$('#free_package_upgrade').prop('readonly', false);
			$('#upgrade_package_id').prop('disabled', false);
		});
	}

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

		if (activated_date.datepicker("getDate").valueOf()) {
			if (activated_date.datepicker("getDate").valueOf() < ev.date.valueOf()) {
				$('#signup_date_err').text('Activated Date shall not be earlier than Signup Date ' + $('#activated_date').val() + '. Signup Date has been updated to ' + $('#signup_date').val());
				var newDate = new Date(ev.date);
				newDate.setDate(newDate.getDate());
				activated_date.datepicker("update", newDate);
			} else if (activated_date.datepicker("getDate").valueOf() >= ev.date.valueOf()) {
				$('#signup_date_err').text('');
				var origin = $('#activated_date').val();
				var newDate = new Date(ev.date);
				newDate.setDate(newDate.getDate());
				activated_date.datepicker("update", newDate);
				activated_date.datepicker("setDate", origin)
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
		if (signup_date.datepicker("getDate").valueOf()) {
			if (ev.date.valueOf() < signup_date.datepicker("getDate").valueOf()) {
				$('#signup_date_err').text('Activated Date shall not be earlier than Signup Date ' + $('#activated_date').val() + '. Signup Date has been updated to ' + $('#signup_date').val());
			} else if (ev.date.valueOf() >= signup_date.datepicker("getDate").valueOf()) {
				$('#signup_date_err').text('');
			}
		}
	});



	//$("#activated_date").datepicker({format: 'yyyy-mm-dd'});
	$("#suspended_date").datepicker({ format: 'yyyy-mm-dd' });
	$("#terminated_date").datepicker({ format: 'yyyy-mm-dd' });

	toggleCompanySection();
	if (new_account == '1' && new_submit == '0') {
		load_package_detail();
	}
	show_billing_address();
	set_nric('');

	//autocomplete
	$(document).on('focus', '#acc_name', function () {
		$('#acc_name').autocomplete({
			autoFocus: true,
			source: function (request, response) {
				var results = $.ui.autocomplete.filter(profile_list, request.term);
				if (!results.length) {
					results = [{ label: "Customer Profile not found", value: "" }];
				}
				response(results);
			},
			select: function (e, ui) {
				$('#profile_id').val(ui.item.id);
				$('#name').val(ui.item.name);
				//$(this).closest('tr').find('input[name="so_detail.item_name"]').addClass('font-bold');

				$('#reg_no').val(ui.item.ssm);
				$('#gst_no').val(ui.item.sst);

				$('#inst_unit_no').val(ui.item.bill_unit_no);
				$('#inst_addr1').val(ui.item.bill_addr_1);
				$('#inst_addr2').val(ui.item.bill_addr_2);
				$('#inst_addr3').val(ui.item.bill_addr_3);
				$('#inst_city').val(ui.item.bill_city);
				$('#inst_postcode').val(ui.item.bill_postcode);
				$('#inst_state').val(ui.item.bill_state);
				
			},
			change: function (e, ui) {
				//const row = $(this).closest('tr');
				//const prodIdField = row.find('input[name="so_detail.prod_id"]');
				//const itemNameField = row.find('input[name="so_detail.item_name"]');
				let result = profile_list.find(item => item.value.trim().toLowerCase() === (e.target.value).trim().toLowerCase());
				if (result) {
					$('#profile_id').val(result.id);
					$('#name').val(result.name);
					//itemNameField.addClass('font-bold');

					$('#reg_no').val(result.ssm);
					$('#gst_no').val(result.sst);

					$('#inst_unit_no').val(result.bill_unit_no);
					$('#inst_addr1').val(result.bill_addr_1);
					$('#inst_addr2').val(result.bill_addr_2);
					$('#inst_addr3').val(result.bill_addr_3);
					$('#inst_city').val(result.bill_city);
					$('#inst_postcode').val(result.bill_postcode);
					$('#inst_state').val(result.bill_state);

				} else {
					$('#profile_id').val(0);
					//itemNameField.removeClass('font-bold');
				}
			}
		});
	});

	$("#transact_date").datepicker({ format: 'yyyy-mm-dd' });

	$(".other_charges_end_date").datepicker({ format: 'yyyy-mm-dd' });

	//ajax_filter();

	window.addEventListener( "pageshow", function ( event ) {
	  var historyTraversal = event.persisted || 
	                         ( typeof window.performance != "undefined" && 
	                              window.performance.navigation.type === 2 );
	  if ( historyTraversal ) {
	    // Handle page restore.
	    window.location.reload();
	  }
	});

	do_relocation_ui();

	do_termination_ui();

}

$("#status").change(function () {
	if ($(this).val() == 't') {
		var c_date = new Date($("#activated_date").val());
		var now = new Date();
		c_date.setMonth(c_date.getMonth() + $("#contract_month").val());
		if (c_date > now) {
			alert("Note: still under contract period!");
		}
	}
});

$("#category").change(function () {
	toggleCompanySection();
	load_package_list();
});

$("#package").change(function () {
	load_package_detail();
});

function getDIAJSON() {
	let submit_obj = [];

	$('.dia_text_row').each(function (i, obj) {
		try {
			submit_obj.push($(this).val());
		} catch (e) {
			// silent fail
		}
	});

	return submit_obj;
}

function toggleCompanySection() {
	if ($("#category").val() == 'r') {
		$("[id=company_section]").hide();
		//$("[id=wholesale_section]").hide();
		//$("[id=non_wholesale_section]").show();
	}
	else if ($("#category").val() == 'b') {
		$("[id=company_section]").show();
		//$("[id=wholesale_section]").hide();
		//$("[id=non_wholesale_section]").show();
	}
	else {
		$("[id=company_section]").show();
		//$("[id=wholesale_section]").show();
		//$("[id=non_wholesale_section]").hide();
	}
}

function load_package_list() {
	$.ajax({
		type: "POST",
		url: base_url + "ajax/load_package_list",
		dataType: "json",
		data: { category: $("#category").val() },
		success: function (data) {
			var sel_package = $('#package');
			$("#package").empty();

			$.each(data, function () {
				sel_package.append($("<option></option>").attr("value", this.package_no).text(this.name + '( RM ' + this.monthly_charge + ' )'));
			});

			//Force to selected first option
			$("#package").val($("#package option:first").val());

			load_package_detail();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
	});

}

function load_package_detail() {
	$.ajax({
		type: "POST",
		url: base_url + "ajax/load_package_detail",
		dataType: "json",
		data: {
			package_no: $("#package").val(),
			customer_no: $("#customer_no").val()
		},
		success: function (data) {
			//console.log(data.router_id);
			$('#package_name').val(data.name);
			$('#monthly_charge').val(data.monthly_charge);
			$('#package_month').val(data.package_month);
			$('#contract_month').val(data.package_month);

			$('#stop_service_after_display').prop('checked', (data.stop_service_after == 1 ? true : false));
			$('#stop_service_after').val(data.stop_service_after);
			$('#selected_dia_vars').val(data.dia_vars);
			auto_gen_dia();

			if($('#prev_status').val() == 'P') {
				$('#bill_waive_period').val(data.bill_waive_period);
				$('#free_package_upgrade').val(data.free_package_upgrade);
				$('#upgrade_package_id').val(data.upgrade_package_id);
				$('#delay_trial_start').prop('checked', data.delay_trial_start == 1);
			} else {
				$('#bill_waive_period').val(data.bill_waive_period);
				$('#free_package_upgrade').val(data.free_package_upgrade);
				$('#upgrade_package_id').val(data.upgrade_package_id);
				$('#delay_trial_start').prop('checked', false);
			}
			$('#router_id').val(data.router_id ?? '0');
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
	});
}

function auto_gen_dia() {
	let dia_html = ``;
	let raw_val = $('#dia_vars').val();
	let existing_dia_vars = [];

	try {
		if (raw_val && raw_val.trim() !== '') {
			existing_dia_vars = JSON.parse(raw_val);
		}
	} catch (e) {
		existing_dia_vars = [];
	}

	let cnt = 0;
	$('#dia_rows_area').html('');

	try {
		let this_vars = JSON.parse($('#selected_dia_vars').val());
		
		for (let i = 0; i < this_vars.length; i++) {
			if (this_vars[i]) {
				let this_value = existing_dia_vars[cnt] ?? '';

				let this_pkg_name = sel_package_list.find(
					item => item.package_no == $('#package').val()
				)?.name ?? 'Package';

				dia_html = `
			<div class="col-lg-6">
			  <div class="input-group">
				<span class="input-group-addon input_group">${this_pkg_name} - ${this_vars[i]}</span>
				<input 
				  name="dia_vars_fields-${i}" 
				  value="${this_value}"
				  type="text" 
				  class="form-control dia_text_row" 
				/>
			  </div>
			</div>
		  `;

				$('#dia_rows_area').append(dia_html);
				cnt++;
			}
		}
	} catch (e) {
		// silently fail
	}

	let dia_vars_count = $('.dia_text_row').length;
	$('#dia_area').css('display', dia_vars_count > 0 ? '' : 'none');
}


function print_customer_detail(customer_no) {

	var new_tab = 1;

	url = base_url + 'customer/customer_statement/' + customer_no;

	if (new_tab == 1) window.open(url, '_blank');
	else window.location = url;
}

function print_customer_filtered(layout) {

	var txt_search = $('#txt_search').val();
	var sel_category = $('#sel_category').val();
	var sel_status = $('#sel_status').val();

	// url = base_url+'customer/customer_filtered/'+layout;

	var action = "customer/customer_filtered/" + layout;

	$("#customer").attr("action", action);

	$('#customer').submit();

	// window.open(action,'_blank');




	// var txt_search 		= $('#txt_search').val();
	// var sel_category	= $('#sel_category').val();
	// var sel_status 		= $('#sel_status').val();
	// var new_tab = 1;

	// $.ajax({
	// 		type: "POST",
	// 		url: base_url + "customer/customer_filtered/" + layout,
	// 		dataType: "html",
	// 		data: { search: txt_search, 
	// 				category: sel_category,
	// 				status: sel_status,
	// 			},
	// 		success: function( data ) {

	// 			// $('#sel').html(data);
	// 			// console.log(data);

	// 			var new_tab = 1;
	// 			if(new_tab == 1) window.open(url,'_blank');

	// 			// $( '#package_name' ).val(data.name);
	// 			// $( '#monthly_charge' ).val(data.monthly_charge);
	// 			// $( '#package_month' ).val(data.package_month);
	// 			// $( '#stop_service_after').prop('checked', (data.stop_service_after==1 ? true: false));
	// 		},
	// 		error: function (xhr, ajaxOptions, thrownError) {
	// 			console.log(thrownError);
	// 		}
	// });




	// var new_tab = 1;

	// url = base_url+'customer/customer_filtered/'+layout;

	// if(new_tab == 1) window.open(url,'_blank');

	// else window.location = url;
}

function show_billing_address() {
	if ($('#show_billing').prop('checked') === true) {
		$('#bill_name').attr('disabled', false);
		$('#bill_addr1').attr('disabled', false);
		$('#bill_addr2').attr('disabled', false);
		$('#bill_city').attr('disabled', false);
		$('#bill_postcode').attr('disabled', false);
		$('#bill_state').attr('disabled', false);
	} else {
		$('#bill_name').attr('disabled', true);
		$('#bill_addr1').attr('disabled', true);
		$('#bill_addr2').attr('disabled', true);
		$('#bill_city').attr('disabled', true);
		$('#bill_postcode').attr('disabled', true);
		$('#bill_state').attr('disabled', true);
	}
}

function set_nric(id_type) {

	if (id_type == '' && $('#nric').val() != '' && $('#passport').val() == '') {
		id_type = 'nric';
	} else if (id_type == '' && $('#nric').val() == '' && $('#passport').val() != '') {
		id_type = 'passport';
	} else if (id_type == '' && $('#nric').val() == '' && $('#passport').val() != '') {

	}

	if (id_type == 'nric') {
		$('#nric').attr('readonly', false);
		$('#nric_span').css('background-color', '');
		$('#passport').attr('readonly', true);
		$('#passport_span').css('background-color', 'darkgray');
	} else if (id_type == 'passport') {
		$('#nric').attr('readonly', true);
		$('#nric_span').css('background-color', 'darkgray');
		$('#passport').attr('readonly', false);
		$('#passport_span').css('background-color', '');
	}
}

$("#nric").change(function () {
	var regExp = new RegExp(/^\d{6}-\d{2}-\d{4}$/);
	var nric = $(this).val();
	if (nric.match(regExp) == null) {
		$('#nric_err').text('Invalid NRIC. Sample format : 123456-12-1234');
		$('#nric').val('s');
		$('#nric').css('border-color', 'red');
	} else {
		$('#nric_err').text('');
		$('#nric').css('border-color', '');
		var lastDigit = nric.substr(nric.length - 1);
		if (lastDigit % 2 == 0) {
			//female
			$('#gender').val('f');
		} else {
			//male
			$('#gender').val('m');
		}
		var nric_arr = nric.split("-");
		var dob = "19" + nric_arr[0].substr(0, 2) + "-" + nric_arr[0].substr(2, 2) + "-" + nric_arr[0].substr(4, 2);
		$('#date_of_birth').val(dob);
	}
});


function call_back(action_url) {
	// var action = "customer/customer_filtered/" + layout;
	$("#customer").attr("action", action_url);
	$('#customer').submit();
}

$(document).on("click", ".fa-sort", function(e){
	$(this).removeClass('fa-sort').addClass('fa-sort-desc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	

	ajax_filter(0, $(this).attr('id'), 'desc');
});

$(document).on("click", ".fa-sort-asc", function(e){
	$(this).removeClass('fa-sort-asc').addClass('fa-sort');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	

	ajax_filter(0, $(this).attr('id'), 'desc');
});
	
$(document).on("click", ".fa-sort-desc", function(e){
	$(this).removeClass('fa-sort-desc').addClass('fa-sort-asc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'asc' );

	ajax_filter(0, $(this).attr('id'), 'asc');
});

function ajax_filter(filter_pressed=0, order_by='', order_type='') {
	//post values

	if (!order_by) {
		order_by = $('#order_by').val();
	}
	if (!order_type) {
		order_type = $('#order_type').val();
	}

	if (filter_pressed == 1) {
		$("#page_item_no").val(0);
	}

	$.ajax({
		type: "POST",
		url: base_url + "customer/customer_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			sel_category: $("#sel_category").val(),
			sel_status: $("#sel_status").val(),
			sel_building: $("#sel_building").val(),
			sel_installation: $("#sel_installation").val(),
			order_by: order_by,
			order_type: order_type
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
	$("#sel_category").val('all');
	$("#sel_status").val('all');
	$("#sel_building").val('all');
	$("#sel_installation").val('all');
	$("#order_by").val('');
	$("#order_type").val('');
	ajax_filter(1);

	$('#select-installation').hide();
}

$(document).on('click', '.btn-verify', function (e) {

	if ($.trim($('#login_username').val()) != '') {

		$.ajax({
			type: "POST",
			url: base_url + "customer/ajax_verify_radius_account",
			dataType: "json",
			data: { 
				login_username: $("#login_username").val(), 
				customer_no:  $("#customer_no").val()
			},
			success: function (data) {
				if (data['exist'] == 2) {

					$("#login_username").css("border", "1px solid lightgray");
					$(".verifyAccountFailed").hide();
					$(".verifyAccountSuccess").hide();
					$(".verifyAccountSame").show();
					$("#btSave").prop("disabled", false);

				} else if (data['exist'] == 1) {

					$("#login_username").css("border", "1px solid red");
					$(".verifyAccountFailed").show();
					$(".verifyAccountSuccess").hide();
					$(".verifyAccountSame").hide();
					$("#btSave").prop("disabled", true);

				} else {

					$("#login_username").css("border", "1px solid lightgray");
					$(".verifyAccountFailed").hide();
					$(".verifyAccountSuccess").show();
					$(".verifyAccountSame").hide();
					$("#btSave").prop("disabled", false);

				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr);
				console.log(ajaxOptions);
				console.log(thrownError);
			}
		});

	} else {
		alert('Fill in login username to verify ...');
	}
});

$(document).on('click', '.btn-generate-password', function (e) {
	var password = generatePassword(8);
	$('#login_password').val(password);
});

function generatePassword(length) {
	var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	var password = "";
	for (var i = 0; i < length; i++) {
		var randomIndex = Math.floor(Math.random() * charset.length);
		password += charset[randomIndex];
	}
	return password;
}

$(document).on("click", ".customer-pic-span", function (e) {
	var panel = $(this).text();
	$('.customer-pic').addClass('hide');
	$('.customer-pic').eq(panel - 1).removeClass('hide');
	$('.customer-pic').eq(panel - 1).find('.customer-pic-span').eq(panel - 1).css('text-decoration', 'underline');
});

$(document).on("click", ".customer-pic-more", function (e) {

	var newPic = $('.customer-pic').length + 1;
	var picLegend = "";
	picLegend += "Personal & Contacts Information";
	var html = "<div class=\"col-lg-12 customer-pic new-customer-pic\">" +
		"<fieldset class=\"category-border\">" +
		"<legend class=\"category-border pic-legend\">" +
		"Personal & Contacts Information";

	for (var z = 0; z < newPic; z++) {
		if ((z + 1) == newPic)
			html += "[ <span class=\"customer-pic-span\" style=\"text-decoration:underline;cursor:pointer;\">" + (z + 1) + "</span> ]";
		else
			html += "[ <span class=\"customer-pic-span\" style=\"cursor:pointer;\">" + (z + 1) + "</span> ]";

		picLegend += "[ <span class=\"customer-pic-span\" style=\"cursor:pointer;\">" + (z + 1) + "</span> ]";
	}
	picLegend += "[ <span class=\"customer-pic-more\" style=\"cursor:pointer;\">more</span> ]";

	html += "[ <span class=\"customer-pic-more\" style=\"cursor:pointer;\">more</span> ]" +
		"</legend>" +
		"<div class=\"col-lg-12\" id=\"nric_err\"></div>" +
		"<div class=\"col-lg-6\">" +
		"<div id=\"company_section\" class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\">Person In Charge</span>" +
		"<input type=\"hidden\" id=\"pic_id\" name=\"pic_id[" + (z - 1) + "]\" value=\"\" />" +
		"<input id=\"pic_name\" name=\"pic_name[" + (z - 1) + "]\" value=\"\" type=\"text\" class=\"form-control\" placeholder=\"\">" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" onclick=\"set_nric('nric')\" id=\"nric_span\" " +
		"style=\"cursor: pointer;\" title=\"Click to enable\">NRIC</span>" +
		"<input id=\"nric\" name=\"nric[" + (z - 1) + "]\" value=\"\" onclick=\"set_nric('nric')\" type=\"text\" " +
		"class=\"form-control\" placeholder=\"Click to enable\">" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" onclick=\"set_nric('passport')\" " +
		"id=\"passport_span\" style=\"cursor: pointer;\" title=\"Click to enable\">Passport</span> " +
		"<input id=\"passport\" name=\"passport[" + (z - 1) + "]\" value=\"\" onclick=\"set_nric('passport')\" " +
		"type=\"text\" class=\"form-control\" placeholder=\"Click to enable\" >" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\">Date of Birth</span>" +
		"<input id=\"date_of_birth\" name=\"date_of_birth[" + (z - 1) + "]\" value=\"\" type=\"text\" " +
		"class=\"form-control\" placeholder=\"Date of Birth\" >" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Race</span>" +
		"<select class=\"form-control\" id=\"race\" name=\"race[" + (z - 1) + "]\" >" +
		"<option value=\"m\">Malay</option>" +
		"<option value=\"c\">Chinese</option>" +
		"<option value=\"i\">Indian</option>" +
		"<option value=\"o\">Others</option>" +
		"</select>" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Gender</span>" +
		"<select class=\"form-control\" id=\"gender\" name=\"gender[" + (z - 1) + "]\" >" +
		"<option value=\"m\">Male</option>" +
		"<option value=\"f\">Female</option>" +
		"</select>" +
		"</div>" +
		"</div>" +
		"<div class=\"col-lg-6\">" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Designation</span>" +
		"<input id=\"pic_designation\" name=\"pic_designation[" + (z - 1) + "]\" value=\"\" " +
		"type=\"text\" class=\"form-control\" placeholder=\"Designation\" >" +
		"</div>" +
		"</div>" +
		"<div class=\"col-lg-6\">" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Tel</span>" +
		"<input id=\"tel_num\" name=\"tel_num[" + (z - 1) + "]\" value=\"\" type=\"text\" " +
		"class=\"form-control\" placeholder=\"Tel\" >" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Fax</span>" +
		"<input id=\"fax_num\" name=\"fax_num[" + (z - 1) + "]\" value=\"\" type=\"text\" " +
		"class=\"form-control\" placeholder=\"Fax\"> " +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Mobile</span>" +
		"<input id=\"mobile_num\" name=\"mobile_num[" + (z - 1) + "]\" value=\"\" type=\"text\" " +
		"class=\"form-control\" placeholder=\"Mobile No\" >" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Email 1</span>" +
		"<input id=\"email_1\" name=\"email_1[" + (z - 1) + "]\" value=\"\" type=\"text\" " +
		"class=\"form-control\" placeholder=\"Email\" >" +
		"</div>" +
		"<div class=\"input-group\">" +
		"<span class=\"input-group-addon input_group\" >Email 2</span>" +
		"<input id=\"email_2\" name=\"email_2[" + (z - 1) + "]\" value=\"\" type=\"text\" " +
		"class=\"form-control\"  placeholder=\"Alternative Email\" >" +
		"</div>" +

		"<div class=\"input-group\">" +
		"<input type=\"checkbox\" name=\"delete[" + (z - 1) + "]\" value=1 /> DELETE" +
		"</div>" +
		"</div>" +
		"</fieldset>" +
		"</div>";

	$('.customer-pic').addClass('hide');
	$('.pic-legend').html(picLegend);
	$('.customer-pic:last').after(html);
	$('.customer-pic:last').removeClass('hide');
});

$(document).on('change', 'input[name*=\'delete\']', function (e) {
	if ($(this).prop('checked') == true)
		$(this).parents('.customer-pic').find('input:not([name*=\'delete\'])').prop('readonly', true);
	else
		$(this).parents('.customer-pic').find('input').prop('readonly', false);
});

$(document).on('change', '#nationality_dd', function (e) {
	set_nationality($(this).val());
});

function set_nationality(nationality, init = false) {
	if (nationality == 'Malaysian') {
		$('#nationality').val(nationality);
		$('.nationality_others').hide();
	} else {
		if (init == false) $('#nationality').val("");
		$('.nationality_others').show();
	}
}

function openNASModel(customer_no) {
	$('#customer_no').val(customer_no);
	$('#customer_name').html($('.customer_row[data-customer-no="' + customer_no + '"]').attr('data-name'));
	$('.modal').modal('show');
}

function proceedDC() {
	let url = base_url + 'customer/execute_dc/' + $('#customer_no').val() + '/' + $('#nas_id').val();
	window.location = url;
}

function autoFillAddress() {

	if ($('#building').val() == '0') {
		return false;
	}

	$.ajax({
		dataType: 'json',
		type: 'POST',
		data: { 'building_no': $('#building').val() },
		url: base_url + 'ajax/get_building_details',
		beforeSend: function (data) {
		},
		success: function (data) {
			var building = data;
			$('#inst_addr1').val(building['addr_1']);
			$('#inst_addr2').val(building['addr_2']);
			$('#inst_addr3').val(building['addr_3']);
			$('#inst_city').val(building['city']);
			$('#inst_state').val(building['state']);
			$('#inst_postcode').val(building['postcode']);

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

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#customer_detail').submit(function(e) {
	e.preventDefault();
	
	effectiveDateValidation();

	$('.input-group-addon').parent().removeClass('has-error');
	$('.button-group button').prop('disabled', true);
	$('#dia_vars').val(JSON.stringify(getDIAJSON()));
	$('#old_package_id').prop('disabled', false);
	$('#package').prop('disabled', false);
	$('#new_package_effective_date').prop('disabled', false);
	$('#upgrade_package_id').prop('disabled', false);

	showLoadingIcon();

	//un-disable certain fields for submission
	undisable_fields();

	let formData = new FormData(this);
	if(clickedButton != '') formData.append(clickedButton, 'submit');

	let control_action = 0;

	if((($('#old_package_id').val() != $('#package') && new_effective_date_empty) 
		|| ($('#old_package_id').val() != $('#package') && $('#package').val() != new_package_id && effective_date_not_reached)) 
		&& $('#new_package_effective_date').val() != '') {
			if($('#contract_month').val() == 0){
				//directly start new contract and call submitForm
				//startNewContract(formData);

				control_action = 2;
			} else {
				//confirm, ask user yes or no whether to continue contract or no
				//confirmContract(formData);
				control_action = 1;
			}
	} else {
		//regular form submission
		//submitForm(formData);
		control_action = 0;
	}	

	//check if relocation in process
	let relocation_action = $('#relocation_action').val();
	if (relocation_action == '1') {
		control_action = 1;
	}

	if (control_action == 2) {
		startNewContract(formData);
	} else if (control_action == 1) {
		confirmContract(formData);
	} else {
		//control action = 0
		submitForm(formData);
	}
});

function effectiveDateValidation () {

	$('#new_package_effective_date').css('border', '');

	if($('#new_package_effective_date').val() === '' && $('#new_package_effective_date').is(':visible')){

		$('#new_package_effective_date').css('border', '1px solid red');

		$('#new_package_effective_date').focus();
		
		$.gritter.add(
			{ 
				title: 'ERROR', 
				text: 'Invalid New package effective date', 
				time: 5000, 
				close_icon: 'l-arrows-remove s16', 
				class_name: 'info-notice' 
			}
		);

		return false;
	}
}

function submitForm(formData) {

	$.ajax({
		dataType: 'json',
		url: $('#customer_detail').attr('action'),
		type: 'POST',
		data: formData,
		success: function (data) {
			if(data['status'] == 'ER'){
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
				$('#old_package_id').prop('disabled', true);
				if(new_account == '0' && $('#current_status').val() == 'A') {
					$('#upgrade_package_id').prop('disabled', true);
				}
				if(new_effective_date_empty) {
					$('#package').prop('disabled', false);
					$('#new_package_effective_date').prop('disabled', false);
				} else {
					if(!effective_date_not_reached) {
						$('#package').prop('disabled', true);
					}
					$('#new_package_effective_date').prop('disabled', true);
				}
				hideLoadingIcon();
			} else {
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			$('.button-group button').prop('disabled', false);
			$('#old_package_id').prop('disabled', true);
			if(new_account == '0' && $('#current_status').val() == 'A') {
				$('#upgrade_package_id').prop('disabled', true);
			}
			if(new_effective_date_empty) {
				$('#package').prop('disabled', false);
			} else {
				$('#package').prop('disabled', true);
			}
			$.gritter.add({
				title: 'ERROR',
				text: 'Something wrong has occured during saving.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
			hideLoadingIcon();
		},
	      complete: function (data) {
	        $('.button-group button').prop('disabled', false);
	        $('#old_package_id').prop('disabled', true);
			if(new_account == '0' && $('#current_status').val() == 'A') {
				$('#upgrade_package_id').prop('disabled', true);
			}
			if(new_effective_date_empty) {
				$('#package').prop('disabled', false);
				$('#new_package_effective_date').prop('disabled', false);
			} else {
				$('#package').prop('disabled', true);
				$('#new_package_effective_date').prop('disabled', true);
			}
	        hideLoadingIcon();
	      },
		cache: false,
		contentType: false,
		processData: false
	});
}

function add_charge_item() {

	let vars_html = `
	<tr class="charge_block" data-id="`+charge_block_id+`">						
		<td>
			<input 
				id			="charge_name_`+charge_block_id+`" 
				name		="charge_name[]" 
				value		=""
				type		="text" 
				class		="form-control"  
				placeholder	="" />
		</td>
		<td>
			<select id="charge_type_`+charge_block_id+`" name="charge_type[]" class="col-lg-12" style="height:25px;">
				<option value="">--Please Select--</option>
				`+billtypeOption+`
			</select>
		</td>
		<td>
			<input 
				id			="charge_amount_`+charge_block_id+`" 
				name		="charge_amount[]" step="0.01" 
				value		=""
				type		="number" 
				class		="form-control"  
				placeholder	="0.00" 
				 />
		</td>
		<td>
			<input 
				id			="charge_remark_`+charge_block_id+`" 
				name		="charge_remark[]" 
				value		=""
				type		="text" 
				class		="form-control"  
				placeholder	="" />
		</td>
		<td>
			<input 
				id			="charge_end_date_`+charge_block_id+`" 
				name		="charge_end_date[]" 
				value		=""
				type		="text" 
				class		="form-control other_charges_end_date"  
				placeholder	="" />
		</td>
		<td class="text-center">
			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="del_charge_row(`+charge_block_id+`)"></i>
		</td>
	</tr>
	`;

	$('#other_charge_table tbody').append(vars_html);
	$(".other_charges_end_date").datepicker({ format: 'yyyy-mm-dd' });
	charge_block_id++;
}

function del_charge_row(block_id) {
	$('.charge_block[data-id="'+block_id+'"]').remove();
}

showLoadingIcon = function () {
  $('#loading-icon').show();
}

hideLoadingIcon = function () {
  $('#loading-icon').hide();
}

function confirmContract(formData) {

	let relocation_action = $('#relocation_action').val();

	//old package id is disabled if no change package occured, the value is the same as current "package"

	$.ajax({
		dataType: 'json',
		url: base_url + 'customer/get_last_package/' + $("#customer_no").val() + '/' + $('#old_package_id').val(),
		type: 'POST',
		data: {},
		success: function (data) {
			var lastPackage = data;
			let currentStart = lastPackage['contract_start_date'];
			let currentEnd = lastPackage['contract_end_date'];

			let new_contract_start = $('#new_package_effective_date').val();
			let d = new Date(new_contract_start);
			d.setMonth(d.getMonth() + +$('#contract_month').val());
			d.setDate(d.getDate() - 1);
			let new_contract_end = d.toISOString().split('T')[0];

			let prev_building = lastPackage['building'] ?? '0';
			let prev_unit_no = lastPackage['inst_unit_no'] ?? '';
			let prev_inst_addr1 = lastPackage['inst_addr1'] ?? '';
			let prev_inst_addr2 = lastPackage['inst_addr2'] ?? '';
			let prev_inst_addr3 = lastPackage['inst_addr3'] ?? '';
			let prev_inst_city = lastPackage['inst_city'] ?? '';
			let prev_inst_postcode = lastPackage['inst_postcode'] ?? '';
			let prev_inst_state = lastPackage['inst_state'] ?? '';

			let prev_inst_state_name = sel_state_list.find(
				item => item.state_code == prev_inst_state 
			)?.name ?? '';

			let prev_building_name = sel_building_list.find(
				item => item.building_no == prev_building 
			)?.name ?? '';

			let prev_inst_addr = '';
			if (prev_building_name) {
				prev_inst_addr = prev_inst_addr + prev_building_name + ' ';
				if (prev_unit_no) {
					prev_inst_addr = prev_inst_addr + prev_unit_no+',';
				}	
				prev_inst_addr = prev_inst_addr + '<br>';
			}
			if (prev_inst_addr1) {
				prev_inst_addr = prev_inst_addr + prev_inst_addr1+',<br>';
			}
			if (prev_inst_addr2) {
				prev_inst_addr = prev_inst_addr + prev_inst_addr2+',<br>';
			}
			if (prev_inst_addr3) {
				prev_inst_addr = prev_inst_addr + prev_inst_addr3+',<br>';
			}
			if (prev_inst_city) {
				prev_inst_addr = prev_inst_addr + prev_inst_city+', ';
			}
			if (prev_inst_postcode) {
				prev_inst_addr = prev_inst_addr + prev_inst_postcode+',<br>';
			}
			if (prev_inst_state_name) {
				prev_inst_addr = prev_inst_addr + prev_inst_state_name;
			}

			let new_building = $('#building').val();
			let new_unit_no = $('#inst_unit_no').val();
			let new_inst_addr1 = $('#inst_addr1').val();
			let new_inst_addr2 = $('#inst_addr2').val();
			let new_inst_addr3 = $('#inst_addr3').val();
			let new_inst_city = $('#inst_city').val();
			let new_inst_postcode = $('#inst_postcode').val();
			let new_inst_state = $('#inst_state').val();

			let new_inst_state_name = sel_state_list.find(
				item => item.state_code == new_inst_state 
			)?.name ?? '';

			let new_building_name = sel_building_list.find(
				item => item.building_no == new_building 
			)?.name ?? '';

			let new_inst_addr = '';
			if (new_building_name) {
				new_inst_addr = new_inst_addr + new_building_name + ' ';
				if (new_unit_no) {
					new_inst_addr = new_inst_addr + new_unit_no+',';
				}	
				new_inst_addr = new_inst_addr + '<br>';
			}
			if (new_inst_addr1) {
				new_inst_addr = new_inst_addr + new_inst_addr1+',<br>';
			}
			if (new_inst_addr2) {
				new_inst_addr = new_inst_addr + new_inst_addr2+',<br>';
			}
			if (new_inst_addr3) {
				new_inst_addr = new_inst_addr + new_inst_addr3+',<br>';
			}
			if (new_inst_city) {
				new_inst_addr = new_inst_addr + new_inst_city+', ';
			}
			if (new_inst_postcode) {
				new_inst_addr = new_inst_addr + new_inst_postcode+',<br>';
			}
			if (new_inst_state_name) {
				new_inst_addr = new_inst_addr + new_inst_state_name;
			}

			//bootbox also show relocation address if any

			bootbox.dialog({
				size: 'large',
				closeButton: false,
				title: `<strong>Package Update Confirmation</strong>`,
				message: `
					<div>This customer's service package has been updated.</div><br>

					<div><strong>Current Contract</strong><br>
					Package: <b>${$('#old_package_name').val()}</b><br>
					Period: <b>${currentStart || ''}</b> - <b>${currentEnd || ''}</b></div><br>

					<div><strong>Current Installation Address</strong><br>
					`+prev_inst_addr+`

					<div><strong>Updated Package</strong><br>
					Package: <b>${$('#package_name').val()}</b><br>
					Effective Date: <b>${$('#new_package_effective_date').val()}</b><br>
					Contract Month: <b>${$('#contract_month').val()}</b></div><br>

					<div><strong>Updated Installation Address</strong><br>
					`+new_inst_addr+`

					<div>Contract Period if Create New Contract: <b>${new_contract_start}</b> - <b>${new_contract_end}</b></div>
					<div>Contract Period if Continue Contract: <b>${currentStart || ''}</b> - <b>${currentEnd || ''}</b></div>
					<hr>
					<div>
						<input type="checkbox" id="reset_free_waiver" checked style="margin-top: 0px !important;">
						<label class="text-danger" for="reset_free_waiver" style="margin-bottom: 0px !important;">
							<strong>Reset and End Free Waiver Month (Set to 0)</strong>
						</label>
					</div>
				`,
				buttons: {
					cancel: {
						label: 'Cancel',
						className: 'btn-secondary gap-2',
						callback: function () {
							$('.button-group button').prop('disabled', false);
							$('#old_package_id').prop('disabled', true);
							if(new_effective_date_empty || effective_date_not_reached) {
								$('#package').prop('disabled', false);
								$('#new_package_effective_date').prop('disabled', false);
							} else {
								$('#package').prop('disabled', true);
								$('#new_package_effective_date').prop('disabled', true);
							}
							document.activeElement?.blur();
							hideLoadingIcon();
						}
					},
					newContract: {
						label: 'Start New Contract',
						className: 'btn-info gap-2',
						callback: function () {
							formData.append('reset_bill_waive_period', 0);

							let shouldResetWaiver = $('#reset_free_waiver').is(':checked');
							if (shouldResetWaiver) {
								formData.set('bill_waive_period', 0);
    							formData.set('reset_bill_waive_period', 1);
							}
							
							startNewContract(formData);
						}
					},
					continueContract: {
						label: 'Continue with Current Contract',
						className: 'btn-success gap-2',
						disabled: old_contract_month == '0' ? true : false,
						callback: function () {
							formData.append('reset_bill_waive_period', 0);

							let shouldResetWaiver = $('#reset_free_waiver').is(':checked');
							if (shouldResetWaiver) {
								formData.set('bill_waive_period', 0);
    							formData.set('reset_bill_waive_period', 1);
							}
							continueContract(formData);
						}
					}
				}
			});
		},
		error: function (data) {
			$('.button-group button').prop('disabled', false);
			$('#old_package_id').prop('disabled', true);if(new_account == '0' && $('#current_status').val() == 'A') {
				$('#upgrade_package_id').prop('disabled', true);
			}
			if(new_effective_date_empty || effective_date_not_reached) {
				$('#package').prop('disabled', false);
				$('#new_package_effective_date').prop('disabled', false);
			} else {
				$('#package').prop('disabled', true);
				$('#new_package_effective_date').prop('disabled', true);
			}
			document.activeElement?.blur();
			hideLoadingIcon();
		},
		complete: function (data) {
			$('.button-group button').prop('disabled', false);
			$('#old_package_id').prop('disabled', true);
			if(new_account == '0' && $('#current_status').val() == 'A') {
				$('#upgrade_package_id').prop('disabled', true);
			}
			if(new_effective_date_empty || effective_date_not_reached) {
				$('#package').prop('disabled', false);
				$('#new_package_effective_date').prop('disabled', false);
			} else {
				$('#package').prop('disabled', true);
				$('#new_package_effective_date').prop('disabled', true);
			}
			document.activeElement?.blur();
			hideLoadingIcon();
		},
		cache: false,
		contentType: false,
		processData: false
	});
}

function startNewContract(formData) {
	$.ajax({
		url: base_url + 'customer/create_new_contract',
		method: 'POST',
		dataType: 'json',
		data: { 
			customer_no: $('#customer_no').val(), 
			package: $('#package').val(),
			new_package_effective_date: $('#new_package_effective_date').val(),
			contract_month: $('#contract_month').val(), 
			building: $('#building').val(),
			inst_unit_no: $('#inst_unit_no').val(),
			inst_addr1: $('#inst_addr1').val(),
			inst_addr2: $('#inst_addr2').val(),
			inst_addr3: $('#inst_addr3').val(),
			inst_city: $('#inst_city').val(),
			inst_postcode: $('#inst_postcode').val(),
			inst_state: $('#inst_state').val(),

		},
		success: function (res) {
			res.status === 'success' ? submitForm(formData) : handle_ajax_error(res);
		},
		error: function () {
			hideLoadingIcon();
			$.gritter.add({
				title: 'ERROR',
				text: 'Something wrong has occured during saving.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
		}
	});
}

function continueContract(formData) {
	$.ajax({
		url: base_url + 'customer/continue_contract',
		method: 'POST',
		dataType: 'json',
		data: { 
			customer_no: $('#customer_no').val(), 
			package: $('#package').val(),
			new_package_effective_date: $('#new_package_effective_date').val(),
			contract_month: $('#contract_month').val(),
			building: $('#building').val(),
			inst_unit_no: $('#inst_unit_no').val(),
			inst_addr1: $('#inst_addr1').val(),
			inst_addr2: $('#inst_addr2').val(),
			inst_addr3: $('#inst_addr3').val(),
			inst_city: $('#inst_city').val(),
			inst_postcode: $('#inst_postcode').val(),
			inst_state: $('#inst_state').val(),
		},
		success: function (res) {
			if(res.status === 'success') {
				if(old_contract_month != 0) {
					formData.set('contract_month', old_contract_month);
				}				
				submitForm(formData);
			} else {
				handle_ajax_error(res);
			}
		},
		error: function () {
			hideLoadingIcon();
			$.gritter.add({
				title: 'ERROR',
				text: 'Something wrong has occured during saving.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
		}
	});
}

function renewContract() {
	const effectiveDate = $('#new_package_effective_date').val();
	const contractMonth = parseInt($('#contract_month').val(), 10);

	if (effectiveDate) {
		$.gritter.add(
			{ 
				title: 'ERROR', 
				text: 'Please wait for the new package effective date before renewing the contract.', 
				time: 5000, close_icon: 'l-arrows-remove s16',
				 class_name: 'info-notice' 
			}
		);
		return;
	}

	if (contractMonth == 0) {
		$.gritter.add(
			{ 
				title: 'ERROR', 
				text: 'Zero contact month no need to renew contract.', 
				time: 5000, 
				close_icon: 'l-arrows-remove s16', 
				class_name: 'info-notice' 
			}
		);
		return;
	}

	if (!contractMonth || contractMonth < 0) {
		$.gritter.add(
			{ 
				title: 'ERROR', 
				text: 'Please enter a valid contract duration in months.', 
				time: 5000, 
				close_icon: 'l-arrows-remove s16', 
				class_name: 'info-notice' 
			}
		);
		return;
	}

	bootbox.dialog({
		size: 'medium',
		closeButton: false,
		title: '<strong>Renew Contract</strong>',
		message: `
			<div class="row">
				<div class="col-lg-12">
					<div class="input-group">
						<span class="input-group-addon input_group" style="min-width:220px; text-align:right;">Package</span>
						<input type="text" class="form-control" disabled value="${$('#old_package_name').val()}">
					</div>
				</div>
				<div class="col-lg-12">
					<div class="input-group">
						<span class="input-group-addon input_group" style="min-width:220px; text-align:right;">
							New Contract Start Date<span class="red">*</span>
						</span>
						<input id="new_contract_start_date" type="text" class="form-control" placeholder="YYYY-MM-DD" autocomplete="off">
					</div>
				</div>
				<div class="col-lg-12">
					<div class="input-group">
						<span class="input-group-addon input_group" style="min-width:220px; text-align:right;">
							Contract Duration (months)<span class="red">*</span>
						</span>
						<input id="new_contract_duration" type="number" min="1" value="${contractMonth}" class="form-control" placeholder="e.g. 12" autocomplete="off">
					</div>
				</div>
				<div class="col-lg-12" style="padding: 0.5rem;">
					Renew Contract Instructions:
					<ol>
						<li>Package shown is current package subscribed.</li>
						<li>New Contract Start Date is required.</li>
						<li>Contract Duration in here edited will be save.</li>
						<li>New Contract Start Date must be after current contract end date.</li>
						<li>Click "Renew Contract" to proceed.</li>
					</ol>
				</div>
			</div>
		`,
		onShown: function () {
			setTimeout(function () {
				$('#new_contract_start_date').datepicker({
					format: 'yyyy-mm-dd',
					startDate: '+0d',
					autoclose: true,
					todayHighlight: true
				});
			}, 150);
		},
		buttons: {
			cancel: {
				label: 'Cancel',
				className: 'btn-default',
				callback: function () {
					document.activeElement?.blur();
				}
			},
			confirm: {
				label: 'Renew Contract',
				className: 'btn-success',
				callback: function () {
					const newStartDate = $('#new_contract_start_date').val().trim();
					const newDuration = parseInt($('#new_contract_duration').val(), 10);
					const packageId = $('#old_package_id').val();
					const customerNo = $('#customer_no').val();

					if (!newStartDate) {
						$.gritter.add(
							{ 
								title: 'ERROR', 
								text: 'Please enter the new contract start date.', 
								time: 5000, 
								close_icon: 'l-arrows-remove s16', 
								class_name: 'info-notice' 
							}
						);
						return false;
					}
										
					if (newDuration == 0) {
						$.gritter.add(
							{ 
								title: 'ERROR', 
								text: 'Zero contact month no need to renew contract.', 
								time: 5000, 
								close_icon: 'l-arrows-remove s16', 
								class_name: 'info-notice' 
							}
						);
						return false;
					}
					
					if (!newDuration || newDuration < 0) {
						$.gritter.add(
							{ 
								title: 'ERROR', 
								text: 'Please enter a valid contract duration in months.', 
								time: 5000, 
								close_icon: 'l-arrows-remove s16', 
								class_name: 'info-notice' 
							}
						);
						return false;
					}

					showLoadingIcon();
					$.ajax({
						url: base_url + 'customer/renew_contract',
						method: 'POST',
						dataType: 'json',
						data: { 
							customer_no: customerNo,
							package: packageId,
							new_contract_start_date: newStartDate,
							new_contract_duration: newDuration,
							building: $('#building').val(),
							inst_unit_no: $('#inst_unit_no').val(),
							inst_addr1: $('#inst_addr1').val(),
							inst_addr2: $('#inst_addr2').val(),
							inst_addr3: $('#inst_addr3').val(),
							inst_city: $('#inst_city').val(),
							inst_postcode: $('#inst_postcode').val(),
							inst_state: $('#inst_state').val(),
						},
						success: function (res) {
							hideLoadingIcon();
							if (res.status === 'success') {
								document.activeElement?.blur();
        						$('#packageHistoryModal').modal('hide');
								 $.gritter.add({ 
									title: 'SUCCESS', 
									text: 'Saved!', 
									time: '2000', 
									class_name: 'success-notice' 
								});
							}else{
								$.gritter.add(
									{ 
										title: 'ERROR', 
										text: res.err_msg || 'Something went wrong while saving.', 
										time: 5000, 
										close_icon: 'l-arrows-remove s16', 
										class_name: 'info-notice' 
									}
								);
							}
						},
						error: function () {
							hideLoadingIcon();
							$.gritter.add(
								{ 
									title: 'ERROR', 
									text: 'Something went wrong while saving.', 
									time: 5000, 
									close_icon: 'l-arrows-remove s16', 
									class_name: 'info-notice' 
								}
							);
						}
					});
				}
			}
		}
	});
}

function show_package_history_popup(page, parameter){
	let current_status = $('#current_status').val() || '';
	
	if(!statusChange && current_status != 'P'){
		$('.renew-button').show();
        $('#packageHistoryPopupContent')
            .html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>')
            .load(base_url + page + parameter);
        $('#packageHistoryModal').modal('show');
	}else{
		let msg = 'Please save to continue view package history.';
		if(current_status == 'P') msg = 'Signup customer cannot view package history.';
		$.gritter.add(
			{ 
				title: 'ERROR', 
				text: msg, 
				time: 5000, 
				close_icon: 'l-arrows-remove s16', 
				class_name: 'info-notice' 
			}
		);
	}
}

function show_status_history_popup (page, parameter) {
	$('#statusHistoryPopupContent')
		.html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>')
		.load(base_url + page + parameter);
	$('#statusHistoryModal').modal('show');
}

function show_termination_history_popup(page, parameter) {
	$('#terminationHistoryPopupContent')
		.html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>')
		.load(base_url + page + parameter);
	$('#terminationHistoryModal').modal('show');
}

function hide_popup(){
    $("#backgroundPopup").fadeOut("fast");
    $("#popupDetail").fadeOut("fast");
    $('.renew-button').hide();
}

function do_relocation_ui() {

	let relocation_sop = $("#relocation_sop").val();

	if (relocation_sop == '1') {
		//disable installation address fields
		$('#building').prop('disabled', true);
		$('#inst_unit_no').prop('disabled', true);
		$('#inst_addr1').prop('disabled', true);
		$('#inst_addr2').prop('disabled', true);
		$('#inst_addr3').prop('disabled', true);
		$('#inst_city').prop('disabled', true);
		$('#inst_postcode').prop('disabled', true);
		$('#inst_state').prop('disabled', true);
		//show relocation button
		$('#relocate_btn').show();
	} else {
		$('#relocate_btn').hide();
		$('#relocate_help').hide();
		$('#cancel_relocate_btn').hide();
		return false;
	}

	$('#relocate_help').hide();
	$('#cancel_relocate_btn').hide();

}

function undisable_fields() {

	//for relocation
	$('#building').prop('disabled', false);
	$('#inst_unit_no').prop('disabled', false);
	$('#inst_addr1').prop('disabled', false);
	$('#inst_addr2').prop('disabled', false);
	$('#inst_addr3').prop('disabled', false);
	$('#inst_city').prop('disabled', false);
	$('#inst_postcode').prop('disabled', false);
	$('#inst_state').prop('disabled', false);

}

function start_relocation_process() {
	$('#relocate_btn').prop("disabled", true);
	$('#cancel_relocate_btn').prop("disabled", false);
	$('#cancel_relocate_btn').show();

	//enable back installation fields
	$('#relocation_action').val('1');

	$('#building').prop('disabled', false);
	$('#inst_unit_no').prop('disabled', false);
	$('#inst_addr1').prop('disabled', false);
	$('#inst_addr2').prop('disabled', false);
	$('#inst_addr3').prop('disabled', false);
	$('#inst_city').prop('disabled', false);
	$('#inst_postcode').prop('disabled', false);
	$('#inst_state').prop('disabled', false);

	instAddressObj.buidling			= $('#building').val();
	instAddressObj.inst_unit_no		= $('#inst_unit_no').val();
	instAddressObj.inst_addr1		= $('#inst_addr1').val();
	instAddressObj.inst_addr2		= $('#inst_addr2').val();
	instAddressObj.inst_addr3		= $('#inst_addr3').val();
	instAddressObj.inst_city		= $('#inst_city').val();
	instAddressObj.inst_postcode	= $('#inst_postcode').val();
	instAddressObj.inst_state		= $('#inst_state').val();
	
	//help
	$('#relocate_help').show();

	$('#package_eff_date_wrapper').contents().appendTo('#relocation_eff_date_wrapper');
	$('#new_effective_date').show();
}

function cancel_relocation_process() {
	$('#relocate_btn').prop("disabled", false);
	$('#relocate_btn').prop("disabled", false);
	$('#cancel_relocate_btn').prop("disabled", true);
	$('#cancel_relocate_btn').hide();
	$('#relocation_action').val('0');

	$('#relocation_eff_date_wrapper').contents().appendTo('#package_eff_date_wrapper');
	$('#new_effective_date').hide();

	$('#building').val(instAddressObj.buidling);
    $('#inst_unit_no').val(instAddressObj.inst_unit_no);
    $('#inst_addr1').val(instAddressObj.inst_addr1);
    $('#inst_addr2').val(instAddressObj.inst_addr2);
    $('#inst_addr3').val(instAddressObj.inst_addr3);
    $('#inst_city').val(instAddressObj.inst_city);
    $('#inst_postcode').val(instAddressObj.inst_postcode);
    $('#inst_state').val(instAddressObj.inst_state);

	do_relocation_ui();
}

function reverse_relocation_process ()
{
	let result = confirm('Do you want to reverse all installation address and package?');
	
	if (!result) return;
	
	const customer_no = $('#customer_no').val();

	$('#terminate_relocate_btn').prop('disabled', true);

	$.ajax({
		type: "POST",
		url: base_url + "customer/reverse_relocation_process",
		dataType: "json",
		data: { customer_no: customer_no },
		success: function (response) {
			if(response.success) {
				window.location.reload();
			} else {
				$.gritter.add({
					title: 'ERROR',
					text: response.message,
					time: '5000',
					class_name: 'danger-notice'
				});
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		},
		complete: function () {
			$('#terminate_relocate_btn').prop('disabled', false);
		}
	});
}

function dia_jumpstart(customer_no, action) {
	if(confirm("Are you sure want to perform this action?")) {
		$.ajax({
			url: base_url + 'customer/dia_jumpstart',
			type: 'POST',
			data: {
				customer_no: customer_no,
				activate: action
			},
			success: function (response) {
				var result = JSON.parse(response);
				
				if(result.success) {
					$.gritter.add({
						title: 'SUCCESS',
						text: result.message,
						time: '5000',
						class_name: 'info-notice'
					});
				} else {
					$.gritter.add({
						title: 'ERROR',
						text: result.message,
						time: '5000',
						class_name: 'danger-notice'
					});
				}
			}
		});
	}
}

function start_termination_process() {
	bootbox.confirm({
		title: 'Termination Flow',
	    message: 'Are you sure you want to begin the termination process?',
	    focus: false,
	    buttons: {
	        confirm: {
	            label: 'Yes',
	            className: 'btn-success'
	        },
	        cancel: {
	            label: 'No',
	            className: 'btn-danger'
	        }
	    },
	    callback: function (result) {
	        if (result) {
	        	showLoadingIcon();

				$.ajax({
					url: base_url + 'customer/begin_termination_flow',
					type: 'POST',
					data: {
						customer_no: $('#customer_no').val(),
					},
					success: function (response) {
						var result = JSON.parse(response);
						
						if(result.success) {
							$.gritter.add({
								title: 'SUCCESS',
								text: result.message,
								time: '5000',
								class_name: 'info-notice'
							});

							setTimeout(function () {
								window.location.reload();
							}, 2000);
						} else {
							$.gritter.add({
								title: 'ERROR',
								text: result.message,
								time: '5000',
								class_name: 'danger-notice'
							});
						}

					},
					error: function (xhr, ajaxOptions, thrownError) {
						console.log(thrownError);
					},
					complete: function () {
						hideLoadingIcon();
					}
				});

	        }
	    }
	});
}

function cancel_termination_process() {
	bootbox.confirm({
		title: 'Termination Flow',
	    message: 'Are you sure you want to cancel the termination process? Cancelling will restart the whole process from beginning.',
	    focus: false,
	    buttons: {
	        confirm: {
	            label: 'Yes',
	            className: 'btn-success'
	        },
	        cancel: {
	            label: 'No',
	            className: 'btn-danger'
	        }
	    },
	    callback: function (result) {
	        if (result) {
	        	showLoadingIcon();

				$.ajax({
					url: base_url + 'customer/cancel_termination_flow',
					type: 'POST',
					data: {
						customer_no: $('#customer_no').val(),
					},
					success: function (response) {
						var result = JSON.parse(response);
						
						if(result.success) {
							$.gritter.add({
								title: 'SUCCESS',
								text: result.message,
								time: '5000',
								class_name: 'info-notice'
							});

							window.location.reload();
						} else {
							$.gritter.add({
								title: 'ERROR',
								text: result.message,
								time: '5000',
								class_name: 'danger-notice'
							});
						}

					},
					error: function (xhr, ajaxOptions, thrownError) {
						console.log(thrownError);
					},
					complete: function () {
						hideLoadingIcon();
					}
				});

	        }
	    }
	});
}

function do_termination_ui() {

	if (typeof current_termination_flow !== 'undefined' && current_termination_flow != 'A') {
		//not default state
		$('input').prop('disabled', true);
		$('select').prop('disabled', true);

		//buttons and functions
		$('#relocate_btn').prop('disabled', true);
		$('#terminate_btn').prop('disabled', true);
		$('#terminate_btn').css('display', 'none');

		$('.btn-generate-password').prop('disabled', true);
	} else {
		//write different state what to do for each state if necessary
	}

}