function init()
{
	initialize_date();

	if(disable_input == '1')
	{
		$('input').prop("disabled", true);
		$('#message').prop("disabled", true);
		$('input[name=scheduler_id]').prop('disabled',false);
		$('#btDelete').addClass('disabled');
		$('#btSave').addClass('disabled');	
	}
	else
	{
		$('input').prop("disabled", false);
		$('#message').prop("disabled", false);
		$('input[name=scheduler_id]').prop('disabled',false);
		$('#btDelete').removeClass('disabled');
		$('#btSave').removeClass('disabled');
	}

}

function initialize_date()
{
	$("#msg_schedule_on").datetimepicker({
		format: 'YYYY-MM-DD HH:mm',
		sideBySide : true,
	});
}

function preview() {
	let title = $('#title').val();
	let message = $('#message').val();

	$('#full_message').val(title + '\n' + message);
}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#message_detail').submit(function(e) {
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