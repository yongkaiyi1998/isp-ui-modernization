$( document ).ready(function() {
    init();
    
    var is_lock = $( "#is_lock" ).val();
    if (is_lock == true) {
		$(":input").attr('readonly', true);
		$(":button").attr('disabled', true);
	}
});

function init()
{
	$("#tranx_date").datepicker({format: 'yyyy-mm-dd',autoclose: true});
	$("#txt_date_start").datepicker({format: 'yyyy-mm-dd',autoclose: true});
	$("#txt_date_end").datepicker({format: 'yyyy-mm-dd',autoclose: true});
	adjustByChange();
	$('.select2').css('width','100%').select2({allowClear:true})
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
		console.log(ui.item.val.customer_no);
		$( "#customer_no" ).val(ui.item.val.customer_no);
		$( "#customer_name" ).val(ui.item.val.name);
	}
});

$("#adjust_by").change(function () {
    adjustByChange();
});

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#adjustment_detail').submit(function(e) {
	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');

	$('.button-group button').prop('disabled', true);

	let submitter = e.originalEvent.submitter.id;
	$('#task').val(submitter);
	
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

function adjustByChange() {
    var adjust_by = $("#adjust_by").val();

    if (adjust_by == 'i') {

        // Individual
        $("#individual_section").css("display", "");
        $("#building_section").css("display", "none");
        $("#area_section").css("display", "none");

        $("#txt_search_autocomplete").prop('disabled', false);
        $("#customer_no").prop('disabled', false);
        $("#customer_name").prop('disabled', false);

        $("#building").prop('disabled', true);
        $("#area").prop('disabled', true);

    } else if (adjust_by == 'b') {

        // Building
        $("#individual_section").css("display", "none");
        $("#building_section").css("display", "");
        $("#area_section").css("display", "none");

        $("#building").prop('disabled', false);

        $("#area").prop('disabled', true);
        $("#txt_search_autocomplete").prop('disabled', true);
        $("#customer_no").prop('disabled', true);
        $("#customer_name").prop('disabled', true);

    } else if (adjust_by == 'a') {

        // Area
        $("#individual_section").css("display", "none");
        $("#building_section").css("display", "none");
        $("#area_section").css("display", "");

        $("#area").prop('disabled', false);

        $("#building").prop('disabled', true);
        $("#txt_search_autocomplete").prop('disabled', true);
        $("#customer_no").prop('disabled', true);
        $("#customer_name").prop('disabled', true);
    }
}

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "adjustment/adjustment_rows/0",
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

function ajax_clear() {
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
    ajax_filter(1);
}