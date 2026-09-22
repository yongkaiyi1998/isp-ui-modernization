$( document ).ready(function() {
    init();
});

function init()
{

	$("#txt_date").datepicker({format: 'yyyy-mm-dd'});

	$("#txt_date_start").datepicker({format: 'yyyy-mm-dd'});
	$("#txt_date_end").datepicker({format: 'yyyy-mm-dd'});

	$("#purchase_date").datepicker({format: 'yyyy-mm-dd'});
	$("#warranty_expiry").datepicker({format: 'yyyy-mm-dd'});

	$("#start_date").datepicker({format: 'yyyy-mm-dd'});
	$("#end_date").datepicker({format: 'yyyy-mm-dd'});

	$("#transfer_date").datepicker({format: 'yyyy-mm-dd'});

	$( "#asset_code" ).autocomplete({
		source: function( request, response ) {
			$.ajax({
				type: "POST",
				url: base_url + "ajax/autocomplete_asset_code",
				dataType: "json",
				data: { keyword: request.term },
				success: function( data ) {
					if (data != '') {
						//console.log(data);
						var transformed = $.map(data, function (el) {
							let real_val = el;		
							return {
									label		: (el.asset_code + ' ' + el.short_name),
									id			: el.asset_code ,
									val			: el.asset_code ,
									realValue	: real_val 
							};
						});
						response(transformed);
					} else {
	                	response([{ label: 'No matches found', id: null, realValue: null }]);
	                }
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
			//console.log(ui.item.val);
			//var description	= ui.item.realValue.asset_code;
			$( "#asset_code" ).val(ui.item.realValue.asset_code);
			$( "#category_idx" ).val(ui.item.realValue.category_idx);
			$( "#brand" ).val(ui.item.realValue.brand);
			$( "#model_no" ).val(ui.item.realValue.model_no);
			$( "#manufacturer" ).val(ui.item.realValue.manufacturer);
			$( "#origin_country" ).val(ui.item.realValue.origin_country);
			$( "#asset_name" ).val(ui.item.realValue.short_name);
			//$('#txt_search').val(ui.item.val); 
			return false;
		}
	});

	$('.customer_autocomplete').autocomplete({
		source: function( request, response ) {
			$.ajax({
				type: "POST",
				url: base_url + "ajax/autocomplete_customer",
				dataType: "json",
				data: { keyword: request.term },
				success: function( data ) {
					if (data != '') {
						//console.log(data);
						var transformed = $.map(data, function (el) {
							let real_val = el;		
							return {
									label		: (el.customer_no + ' ' + el.name),
									id			: el.customer_no ,
									val			: el.customer_no ,
									realValue	: real_val 
							};
						});
						response(transformed);
					} else {
	                	response([{ label: 'No matches found', id: null, realValue: null }]);
	                }
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
			//console.log(ui.item.val);
			$( "#customer_no" ).val(ui.item.realValue.customer_no);
			$( "#location" ).val(ui.item.realValue.name);

			return false;
		}
	});

    $(document).on('change', '[id*="purchase_price"], [id*="depreciation_rate"], [id*="use_life"]', function () {
    	cal_current_asset_value();
    });

    cal_current_asset_value();

	$('#start_date').on('dp.change', function (e) {
    	if ($(this).val() == '') {
    		$('#next_maint_date').val('');
    	}

		cal_next_maint_date();
    });

    $(document).on('change', '#interval_value, #interval_unit', function () {
    	if ($(this).val() == '') {
    		$('#next_maint_date').val('');
    	}

		cal_next_maint_date();
    });

}

function cal_current_asset_value() {
	let current_asset_value = 0;
	let asset_value 		= parseFloat($('#purchase_price').val());
	let use_life 			= parseInt($('#use_life').val());
	let depreciation_rate 	= 0;

	let input_purchase_date = $('#purchase_date').val();
	let year_diff = 0;
	if (input_purchase_date != '') {
		let purchase_date 		= new Date(input_purchase_date);
		let purchase_year 		= purchase_date.getFullYear();
		let current_year 		= new Date().getFullYear();
		year_diff				= parseInt(current_year) - parseInt(purchase_year);
	}

	if (use_life > 0) {
		depreciation_rate = 100 / use_life;
	}

	if (asset_value > 0 && depreciation_rate > 0 && input_purchase_date != '') {
		current_asset_value = asset_value - (((year_diff * depreciation_rate) / 100) * (asset_value - 1));
	}

	if (depreciation_rate > 0 ) {
		$('#depreciation_rate').val(depreciation_rate.toFixed(2));
	}

	if (current_asset_value > 0) {
		$('#current_asset_value').val(current_asset_value.toFixed(2));
	}
}

function cal_next_maint_date() {
	let start_date = $('#start_date').val();
	let interval_value = $('#interval_value').val();
	let interval_unit = $('#interval_unit').val();

	if (start_date != '' && interval_value != '' && interval_unit != '') {
		let new_date = new Date(start_date);
		interval_value = parseInt(interval_value);

		if (interval_unit == 'd') {
			new_date.setDate(new_date.getDate() + interval_value);
		}
		else if (interval_unit == 'm') {
			new_date.setMonth(new_date.getMonth() + interval_value);
		}
		else if (interval_unit == 'y') {
			new_date.setFullYear(new_date.getFullYear() + interval_value);
		}

		if (new_date) {
		    let formattedDate = new_date.toISOString().split('T')[0];
		    $('#next_maint_date').val(formattedDate);
		}
	}
}

function go_service_record() {
	window.location.href=base_url+'asset/add_service/'+$('#asset_id').val();
}

function go_transfer_record() {
	window.location.href=base_url+'asset/add_transfer/'+$('#asset_id').val();
}

function update_site_field() {
	$('#site').val($('#site_display').val());
}

function add_user_row(){
	let user_html = `
	<tr class="contacts_pic_tr">
		<td>
			<input type="hidden" name="user_id[]" value="" />
			<input type="text" name="username[]" value="" class="form-control user_autocomplete" />
		</td>
		<td>
			<input type="text" name="email[]" value="" class="form-control" />
		</td>
		<td>
			<input type="text" name="phone[]" value="" class="form-control" />
		</td>
		<td>
			<input type="text" name="telegram_id[]" value="" class="form-control" />
		</td>
		<td class="text-center">
			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
		</td>
	</tr>
	`;

	$('#maintenance_pic_table tbody').append(user_html);

	//initiate autocomplete
	userAutocomplete();

}

function userAutocomplete()
{		
	$('.user_autocomplete').autocomplete({
		source: function (request, response) {
			$.ajax({
				url: base_url+"ajax/search_user",
				dataType:"json",
				data: { 
					query: request.term,   
				},
				success: function (data) {	
					if (data != '') {				
						var transformed = $.map(data, function (el) 
							{		
								real_val = el;

								/*let show_desc = el.description[0];
								if (el.short_name != '') {
									show_desc = el.short_name;
								}*/

								return {
										label		: el.username,
										id			: el.idx,
										realValue	: real_val,		
								};
							});					
						response(transformed);
					} else {
	                	response([{ label: 'No matches found', id: null, realValue: null }]);
	                }
				},
				error: function () {
					response([]);
				}
			});
		},
		messages: {
			noResults: '',
			results: function() {}
		},
		select: function (event, ui) {			

			event.preventDefault();

			let idx 		= ui.item.realValue.idx;
			let username	= ui.item.realValue.username;
			let email		= ui.item.realValue.email;
			let phone		= ui.item.realValue.mobile_no;
			let telegram_id = '';

			$(this).parent().parent().find('input[name="user_id\\[\\]"]').val(idx);
			$(this).parent().parent().find('input[name="username\\[\\]"]').val(username);
			$(this).parent().parent().find('input[name="email\\[\\]"]').val(email);
			$(this).parent().parent().find('input[name="phone\\[\\]"]').val(phone);
			$(this).parent().parent().find('input[name="telegram_id\\[\\]"]').val(telegram_id);

		},
	});
}

function delLine(elem){
	$(elem).parent().parent().remove();
}

function default_pic() {
	let category_idx = $('#category_idx').val();

	$.ajax({
		type: "POST",
		url: base_url + "ajax/ajax_get_category_pic",
		dataType: "json",
		data: { category_idx: category_idx },
		success: function( data ) {

			//console.log(data);

			let cnt = 0;
			for (const row of data) { // You can use `let` instead of `const` if you like
			    //console.log(row);
			    add_user_row();
			    $('input[name="user_id\\[\\]"]').eq(cnt).val(row.user_id);
			    $('input[name="username\\[\\]"]').eq(cnt).val(row.username);
			    $('input[name="email\\[\\]"]').eq(cnt).val(row.email);
			    $('input[name="phone\\[\\]"]').eq(cnt).val(row.phone);
			    $('input[name="telegram_id\\[\\]"]').eq(cnt).val(row.telegram_id);
			    cnt++;
			}

			/*if(data['idx'] == ""){

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
			}*/
		},
		error: function (xhr, ajaxOptions, thrownError) {
			
			console.log(thrownError);
			//alert(xhr.status);
			//alert(thrownError);
		}
	});

}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#asset_detail').submit(function(e) {
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
		url: base_url + "asset/asset_rows/0",
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

/**
 * Hot fix
 * Can split to different file in future
 */
function ajax_service_list_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "asset/service_list_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			txt_date_start: $("#txt_date_start").val(),
			txt_date_end: $("#txt_date_end").val(),
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

/**
 * Hot fix
 * Can split to different file in future
 */
function ajax_service_list_clear() {
	let today = new Date();
    let formatDate = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let start = new Date(today.getFullYear(), today.getMonth(), 1);
    let end = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    $("#page_item_no").val(0);
    $("#txt_search").val('');
    $("#txt_date_start").val(formatDate(start));
    $("#txt_date_end").val(formatDate(end));
	$("#txt_date_start").datepicker('setDate', formatDate(start));
	$("#txt_date_end").datepicker('setDate', formatDate(end));
    ajax_service_list_filter(1);
}

/**
 * Hot fix
 * Can split to different file in future
 */
function ajax_transfer_list_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "asset/transfer_list_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			txt_date_start: $("#txt_date_start").val(),
			txt_date_end: $("#txt_date_end").val(),
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

/**
 * Hot fix
 * Can split to different file in future
 */
function ajax_transfer_list_clear() {
	let today = new Date();
    let formatDate = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let start = new Date(today.getFullYear(), today.getMonth(), 1);
    let end = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    $("#page_item_no").val(0);
    $("#txt_search").val('');
    $("#txt_date_start").val(formatDate(start));
    $("#txt_date_end").val(formatDate(end));
	$("#txt_date_start").datepicker('setDate', formatDate(start));
	$("#txt_date_end").datepicker('setDate', formatDate(end));
    ajax_transfer_list_filter(1);
}

/**
 * Hot fix
 * Can split to different file in future
 */
function ajax_planned_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "asset/planned_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			txt_date: $("#txt_date").val(),
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

/**
 * Hot fix
 * Can split to different file in future
 */
function ajax_planned_clear() {
	let today = new Date();
    let formatDate = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let end = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    $("#page_item_no").val(0);
    $("#txt_search").val('');
    $("#txt_date").val(formatDate(end));
	$("#txt_date").datepicker('setDate', formatDate(end));
    ajax_planned_filter(1);
}