$( document ).ready(function() {
    init2();
	if($('.select2')){
		$('.select2').css('width','100%').select2({allowClear:true})
	}
});

function init2()
{
	chk_dia();

	chk_dia_vars();
}

function chk_dia() {
	if ($('#category').val() == 'd' || $('#category').val() == 'e') {
		$('.dia_fields').css('display', '');
	} else {
		$('.dia_fields').css('display', 'none');
	}
}

var dia_var_block_id = 0;

function addDIAVar() {

	let vars_html = `
	<div class="input-group dia_var_block" data-id="`+dia_var_block_id+`">						
		Parameter Name <input type="text" id="vars_`+dia_var_block_id+`" name="vars_`+dia_var_block_id+`" value="" style="width:200px;" />&nbsp;&nbsp;&nbsp;<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delDIAVar(`+dia_var_block_id+`)"></i>
	</div>
	`;

	$('.dia_vars_list').append(vars_html);
	dia_var_block_id++;
}

function delDIAVar(block_id) {
	$('.dia_var_block[data-id="'+block_id+'"]').remove();
}

function construct_dia_json() {

	let dia_arr = [];

	$('.dia_var_block input').each(function(index,data) {
   		let field_value = $(this).val();
   		if (field_value != '') {
   			dia_arr.push(field_value);
   		}
	});

	$('#dia_vars').val(JSON.stringify(dia_arr));

}

function chk_dia_vars() {

	try {
		let dia_vars = JSON.parse($('#dia_vars').val());

		for (var key in dia_vars) {
			addDIAVar();
			let cur_id = dia_var_block_id - 1;
			$('#vars_'+cur_id).val(dia_vars[key]);
		}

	} catch(e) {
		//do nothing
	}

}


let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#package_detail').submit(function(e) {
	e.preventDefault();
	let router = $("#router_id").val();
	
	if(router == ''){
		$.gritter.add({
			title: 'ERROR',
			text: 'Please select a Router before submitting.',
			time: '5000',
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice',
		});
		$("#router_id").css("border", "1px solid red");
		return;
	}else{
		$("#router_id").css("border", "");
	}

	$('.input-group-addon').parent().removeClass('has-error');
	$('.button-group button').prop('disabled', true);
	if ($('#category').val() == 'd' || $('#category').val() == 'e') {
		construct_dia_json();
	} else {
		$('#dia_vars').val(JSON.stringify([]));
	}

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