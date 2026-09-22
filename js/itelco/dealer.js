let changeUpline = false;
let changeBuilding = false;

$( document ).ready(function() {
	if (!$("#dealer_no").val()) {
		$("#btComm").prop('disabled', true);
	}

	if($('.select2')){
		$('.select2').css('width','100%').select2({allowClear:true})
	}
	
	$('#upline').on('change', function() {
		changeUpline = true;
		handleBuildingSelect();	
		handleUplineSelect();
	});

	$('#building').on('change', function() {
		changeBuilding = true;
	});

	handleBuildingSelect();	
	handleUplineSelect();
});

function init() {
	$("#signup_date").datepicker({format: 'yyyy-mm-dd'});
	$("#terminated_date").datepicker({format: 'yyyy-mm-dd'});
}

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "dealer/dealer_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val()
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

$('#dealer_detail').submit(function(e) {
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

function goCommissionPage(dealer_no) {
	if (changeUpline || changeBuilding) {
		$.gritter.add({
			title: 'ERROR',
			text: 'Please save the changes before proceeding to commission settings.',
			time: '5000',
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice',
		});

		$('#btSave').addClass('blink_me');
		
		setTimeout(function() {
			$('#btSave').removeClass('blink_me');
		}, 1500);
	}else{
		window.location = base_url + 'dealer/dealer_comm/' + dealer_no;
	}
}

function handleBuildingSelect() {
	let upline = $('#upline').val();
	if (upline == 0){
		$('#building-field').show();
	}else{
		$('#building-field').hide();
	}
}

function handleUplineSelect() {
    let upline = $('#upline').val();
    let $inputGroup = $('#deduct-input-group');
	
    if (upline == 0 || upline == undefined) {
        $inputGroup.hide();

        if ($('#monthly_pct_deduct_hidden').length === 0) {
            let monthlyVal = $('#monthly_pct_deduct').val();
            let oneTimeVal = $('#one_time_amt_deduct').val();

            $('<input>').attr({
                type: 'hidden',
                id: 'monthly_pct_deduct_hidden',
                name: 'monthly_pct_deduct',
                value: monthlyVal
            }).appendTo($inputGroup.parent());

            $('<input>').attr({
                type: 'hidden',
                id: 'one_time_amt_deduct_hidden',
                name: 'one_time_amt_deduct',
                value: oneTimeVal
            }).appendTo($inputGroup.parent());
        }
    } else {
        $inputGroup.show();
        $('#monthly_pct_deduct_hidden, #one_time_amt_deduct_hidden').remove();
    }
}

function uplineWarningMessage() {
	if (($('#upline').length === 0 && $('input[name="upline"][type="hidden"]').length)) {
		$.gritter.add({
			title: 'NOTICE',
			text: 'You cannot change the upline for this dealer because they already have downlines.',
			time: 5000,
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice'
		});
	}
}