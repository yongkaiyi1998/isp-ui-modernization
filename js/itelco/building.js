$(document).ready(function () {
	// init();
	if ($('#area_id').val() != 0) {
		$('#state').prop('disabled', true);
		$('#postcode').prop('disabled', true);
		$('#city').prop('disabled', true);
	}
});

function fillAddr() {
	var areas = JSON.parse(areaList);

	let areaId = $('#area_id').val();
	var selectedArea = '';
	for (let i = 0; i < areas.length; i++) {
		if (areas[i].id == areaId) {
			selectedArea = areas[i];
			break;
		}
	}
	if (selectedArea != '') {
		$('#state').prop('disabled', true);
		$('#postcode').prop('disabled', true);
		$('#city').prop('disabled', true);

		$('#state').val(selectedArea.state);
		$('#postcode').val(selectedArea.postcode);
		$('#city').val(selectedArea.city);
	} else {
		$('#state').prop('disabled', false);
		$('#postcode').prop('disabled', false);
		$('#city').prop('disabled', false);

		$('#state').val('');
		$('#postcode').val('');
		$('#city').val('');
	}
}


function ajax_filter(filter_pressed = 0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);

	$.ajax({
		type: "POST",
		url: base_url + "building/building_rows/0",
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

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#building_detail').submit(function (e) {
	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');

	$('.button-group button').prop('disabled', true);

	$('#state').prop('disabled', false);
	$('#postcode').prop('disabled', false);
	$('#city').prop('disabled', false);

	let formData = new FormData(this);
	if (clickedButton != '') formData.append(clickedButton, 'submit');

	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		beforeSend: function (data) {
		},
		success: function (data) {
			if (data['status'] == 'ER') {
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
			} else {
				window.location.href = base_url + data['url'];
			}
		},
		complete: function (data) {
			if ($('#area_id').val() != 0) {
				$('#state').prop('disabled', true);
				$('#postcode').prop('disabled', true);
				$('#city').prop('disabled', true);
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