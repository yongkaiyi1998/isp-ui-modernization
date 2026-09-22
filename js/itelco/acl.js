$( document ).ready(function() {
    init();
});

function init(){
	sync_all();
}

$( "#role_name" ).keypress( function() {
	$( '#role_no' ).val('');
});

function role_select( role_no )
{
	$.ajax({
		type: "POST",
		url: base_url + "acl/role_select",
		dataType: "json",
		data: { role_no: role_no },
		success: function( data ) {
			$( '#role_no' ).val(data.role_no);
			$( '#role_name' ).val(data.role_name);
			set_permission(data.acl_list);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(thrownError);
		}
});
}

function set_permission(acl_list) 
{
	$.each(acl_list, function(index,value){
		$('#chk_'+index+"_A").prop("checked", value.A);
		$('#chk_'+index+"_V").prop("checked", value.V);
		$("#chk_"+index+"_M").prop("checked", value.M);
		$("#chk_"+index+"_D").prop("checked", value.D);
	});
	sync_all();
}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#acl_detail').submit(function (e) {
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
		url: base_url + "acl/acl_rows/0",
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

function get_all_sections() {
	let sections = [];
	$('.perm-checkbox').each(function () {
		let section = $(this).data('section');
		if (sections.indexOf(section) === -1) sections.push(section);
	});
	return sections;
}

function sync_section_checkbox(section) {
	let $checkboxes = $('.perm-checkbox[data-section="' + section + '"]');
	let allChecked = $checkboxes.length > 0 && $checkboxes.length === $checkboxes.filter(':checked').length;
	$('.section-select-all[data-section="' + section + '"]').prop('checked', allChecked);
}

function sync_global_checkbox() {
	let $all = $('.perm-checkbox');
	let allChecked = $all.length > 0 && $all.length === $all.filter(':checked').length;
	$('#chk_select_all_global').prop('checked', allChecked);
}

function sync_all() {
	$.each(get_all_sections(), function (i, section) {
		sync_section_checkbox(section);
	});
	sync_global_checkbox();
}

$(document).on('change', '#chk_select_all_global', function () {
	let checked = $(this).is(':checked');
	$('.perm-checkbox').prop('checked', checked);
	$('.section-select-all').prop('checked', checked);
});

$(document).on('change', '.section-select-all', function () {
	let section = $(this).data('section');
	let checked = $(this).is(':checked');
	$('.perm-checkbox[data-section="' + section + '"]').prop('checked', checked);
	sync_global_checkbox();
});

$(document).on('change', '.perm-checkbox', function () {
	sync_section_checkbox($(this).data('section'));
	sync_global_checkbox();
});