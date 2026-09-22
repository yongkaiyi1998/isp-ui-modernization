$( document ).ready(function() {
    init2();
	
});

function init2()
{
	$("#start_date").datepicker({format: 'yyyy-mm-dd'});
	$("#end_date").datepicker({format: 'yyyy-mm-dd'});

    $('.ace-file-input-input').aceFileInput({      
    });

	$('a.remove').on("click", function(e) {
		clear_preview();
	});

	process_uploaded_pic();

}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#advertisement_detail').submit(function(e) {
	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');
	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);
	if(clickedButton != '') formData.append(clickedButton, 'submit');

	/*let file_upload = $('#file_path').data('ace_input_files');
	if (file_upload) {
		formData.append('file_path', file_upload[0]);
	}*/

	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
        contentType: false,
        processData: false,
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

function clear_preview() {
    let embed_str = ``;
    document.getElementById("preview_area").innerHTML = embed_str;
    $('a.remove').css('display', 'none');
}

var for_preview = {};
var preview_id = 1;
function startPreview(event) {

	let current_preview_id = event.target.getAttribute("data-preview-id");
  
	let src = URL.createObjectURL(event.target.files[0]);
	for_preview[current_preview_id] = { src: src };

	$('a.remove').css('display', 'block');
  
	refresh_preview();
}

function process_uploaded_pic() {
  if ($('#already_uploaded').val() == '') {
    return false;
  }
  let current_preview_id = 1;
  for_preview[current_preview_id] = { src: upload_path + escape($('#already_uploaded').val()) };

  refresh_preview();
}

function refresh_preview() {
    let embed_str = ``;
    Object.values(for_preview).forEach((previewObject) => {
      embed_str += `
      <a href=${previewObject.src} data-lightbox="image-1" data-title="Advertisement Preview" target="_BLANK">
      <img
      src=${previewObject.src}
      width="100%" style="max-width: 500px;"></a>`;
    });
  
    document.getElementById("preview_area").innerHTML = embed_str;

}