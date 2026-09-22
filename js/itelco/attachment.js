var file_count = $('.attach-file').length;

$('#attach_file_input').ace_file_input({
	style: 'well',
	btn_choose: 'Click to choose your files here',
	btn_change: null,
	no_icon: 'ace-icon fa fa-cloud-upload',
	droppable: true,
	thumbnail: 'small',//large | fit
	//,icon_remove:null//set null, to hide remove/reset button
	preview_error : function(filename, error_code) {
		
		console.log( filename ) ;
		console.log( error_code ) ;
		
		//name of the file that failed
		//error_code values
		//1 = 'FILE_LOAD_FAILED',
		//2 = 'IMAGE_LOAD_FAILED',
		//3 = 'THUMBNAIL_FAILED'
		//alert(error_code);
	}

}).on('change', function(files, dropped){
	//console.log($(this).data('ace_input_files'));
	//console.log($(this).data('ace_input_method'));
	let module = $('#attach_file_input').data('module') ?? '';

	let inputFiles = [].slice.call($(this).data('ace_input_files'));
	if (inputFiles.length > 0) {

		let input_file_count = inputFiles.length;

		let remark_html = '';
		for (let i = 0; i < input_file_count ; i++) {
			let file_name = inputFiles[i].name;

			remark_html += `
							<div class="input-group col-lg-6 col-xs-12 text-left">
                        		<span class="text-center modal-remark"><b>Remark for ${file_name}</b></span>
							</div>
							<div class="input-group col-lg-12 col-xs-12">
                        		<input type="text" value="" maxlength="255" autocomplete="off" class="form-control modal_attachment_remark" id="attach_attachment_remark_${i}">
                    		</div>` + 
							((module === 'profile') ? 
							`<div class="input-group col-lg-6 col-xs-12 text-left">
                        		<span class="text-center modal-remark"><b>File Type for ${file_name}</b></span>
							</div>
							<select id="fileCtg_${i}">
								<option value="">-- Select --</option>
								<option value="IC_">IC</option>
								<option value="SSM_">SSM</option>
								<option value="AUTH_">Auth</option>
								<option value="">Other</option>
							</select>` : ``) +

                    		`<div>&nbsp;</div>`;
        }

        $('#remark-div').html('');
        $('#remark-div').append(remark_html);
		$('#attachment-remark-modal').modal('show');

		$('#attachment-remark-close').css('display', 'none');

		$('#attachment-remark-modal').on('hide.bs.modal', function (e) {
			let current_file_count = 0;

			for (let i = 0; i < input_file_count ; i++) {
				let attach_attachment_remark = $('#attach_attachment_remark_'+i).val();
				let fileCtg = $('#fileCtg_'+i).val() ?? '';
			    let file_url = URL.createObjectURL(inputFiles[i]);
			    let file_name = inputFiles[i].name;
			    console.log(inputFiles[i].type);
			    let file_type = '';
			    let file_type_text = '';
			    let file_extension = file_name.split('.').pop();

			    if (inputFiles[i].type.includes("image")) {
			    	file_type = `<img class="thumbnail-display" src="${file_url}">`;
			    	file_type_text = 'image';
			    } else if (inputFiles[i].type.includes("video")) {
			    	file_type = `<i class="fa fa-film"></i>`;
			    	file_type_text = 'video';
			    	if (attach_attachment_remark == '') {
			    		attach_attachment_remark = file_name;
			    	}
			    } else if (inputFiles[i].type.includes("pdf")) {
			    	file_type = `<i class="fa fa-file-pdf-o"></i>`;
			    	file_type_text = 'pdf';
			    	if (attach_attachment_remark == '') {
			    		attach_attachment_remark = file_name;
			    	}
			    } else {
			    	file_type = '<i class="fa fa-file"></i>';
			    	file_type_text = 'doc';
			    	if (attach_attachment_remark == '') {
			    		attach_attachment_remark = file_name;
			    	}
			    }

			    let print_att_html = ``;
			    /*print_att_html = `
		    	Print? <input type="checkbox" id="attach_attachment_print[${file_count}]" name="attach_attachment_print[${file_count}]" value="1" />
			    `;*/

				let delete_att_html = ``;
				delete_att_html = `
				&nbsp;&nbsp;&nbsp;<a class="att_remove_btn" style="color: red; cursor: pointer; font-size:16px;" onclick="removeFile(this);">
					<i class="fa fa-trash" style="cursor:pointer;"></i>
				</a>
				`;

			    let html = `<div class="col-lg-3 col-xs-6 attach-file" style="margin-bottom: 10px; height:150px;" data-id="${file_count}">
								<div class="desktop-icon">
									<div class="preview_area" style="cursor:pointer;" data-id="${file_count}" data-local-path="${file_url}" data-file-type="${file_type_text}" data-saved="0" data-extension="${file_extension}" onclick="view_attach_doc(${file_count});"> 
										<div class="icon-image">
											${file_type}
										</div>
										<!--<div class="file-name">${file_name}</div>-->
									</div>

									<div class="file-remark" title="${attach_attachment_remark}"><a style="cursor:pointer;" data-existing="0" data-existing_id="0" data-id="${file_count}" data-file-name="${file_name}" data-is-temp="0" onclick="change_remark(this);">${attach_attachment_remark}</a></div>

									<div class="text-right" style="min-height:20px;">
									`+print_att_html+`
									`+delete_att_html+`
									</div>
								</div>

								<input type="file" name="attach_attachment[${file_count}]" class="hide" id="attach_attachment_${file_count}">

								<input type="hidden" name="attach_attachment_remark[${file_count}]" value="${attach_attachment_remark}">

								<input type="hidden" name="fileCtg[${file_count}]" value="${fileCtg}"
							</div>`;

				$('#file-container').append(html);

				let dataTransfer = new DataTransfer();

				dataTransfer.items.add($('#attach_file_input').data('ace_input_files')[current_file_count]);

				$('#attach_attachment_'+file_count).get(0).files = dataTransfer.files;

				$('#attachment_remark').val('');

				resetAceFileInput();

				file_count++;
				current_file_count++;
			}

			$(this).off('hide.bs.modal');
			$('#attach_file_input').val('');
		});
	}
});

function removeFile(element) {
    if (confirm("Delete this attachment?")) {
        
		let file_id = $(element).attr('data-file-id') ?? '';

		if(file_id != '') {
			let is_temp = $(element).attr('data-is-temp');
			let temp_id = $('#temp_id').val();
			$.ajax({
				type: "POST",
				url: base_url + "ajax/ajax_remove_attachment" ,
				dataType: "json",
				data: { 
						file_id 	: file_id,
						is_temp		: is_temp,
						temp_id		: temp_id
					},
				success: function( data ) {
						$(element).closest(".attach-file").remove();
				},
				error: function (xhr, ajaxOptions, thrownError) {
				}
			});
		} else {
			$(element).closest(".attach-file").remove();
		}
    }
}

function resetAceFileInput() {
	$('.ace-file-container').attr('data-title', 'Click to choose your files here');
	$('.ace-file-container').removeClass('hide-placeholder');
	$('.ace-file-container').removeClass('selected');

	let ace_element = `<span class="ace-file-name" data-title="No File ..."><i class=" ace-icon ace-icon fa fa-cloud-upload"></i></span>`;

	$('.ace-file-container').html('');
	$('.ace-file-container').html(ace_element);
}

function change_remark(element) {
	
	let remark_html = '';
	let file_id = $(element).attr('data-id');
	let file_name = $(element).attr('data-file-name');

	let existing = $(element).attr('data-existing');
	let existing_id = $(element).attr('data-existing_id');

	let is_temp = $(element).attr('data-is-temp');

	let previous_remark = $('input[name="attach_attachment_remark\\['+file_id+'\\]"]').val();

	remark_html += `
	<div class="input-group col-lg-6 col-xs-12 text-left">
		<span class="text-center modal-remark"><b>Remark for ${file_name}</b></span>
	</div>
	<div class="input-group col-lg-12 col-xs-12">
		<input type="hidden" id="temp_file_id" name="temp_file_id" value="${file_id}" />
		<input type="hidden" id="temp_file_name" name="temp_file_name" value="${file_name}" />
		<input type="hidden" id="temp_existing" name="temp_existing" value="${existing}" />
		<input type="hidden" id="temp_existing_id" name="temp_existing_id" value="${existing_id}" />
		<input type="hidden" id="temp_is_temp" name="temp_is_temp" value="${is_temp}" />
		<input type="text" value="${previous_remark}" maxlength="255" autocomplete="off" class="form-control modal_attachment_remark" id="attach_attachment_remark_${file_id}">
	</div>

	<div>&nbsp;</div>`;

    $('#remark-div').html('');
    $('#remark-div').append(remark_html);

	$('#attachment-remark-modal').modal('show');

	$('#attachment-remark-close').css('display', '');

	$('#attachment-remark-modal').on('hide.bs.modal', function (e) {

		let tosave = $('#tosave').val();

		if (tosave == '0') {
			$(this).off('hide.bs.modal');
		} else {

			let temp_file_id = $('#temp_file_id').val();
			let attach_attachment_remark = $('#attach_attachment_remark_'+temp_file_id).val();

			let temp_existing = $('#temp_existing').val();
			let temp_existing_id = $('#temp_existing_id').val();

			let temp_is_temp = $('#temp_is_temp').val();

			//console.log(attach_attachment_remark);

	    	if (attach_attachment_remark == '') {
	    		attach_attachment_remark = $('#temp_file_name').val();
	    	}

	    	$('.file-remark a[data-id="'+temp_file_id+'"]').html(attach_attachment_remark);

	    	$('input[name="attach_attachment_remark\\['+temp_file_id+'\\]"]').val(attach_attachment_remark);

	    	if (temp_existing == '1') {
				$.ajax({
					type: "POST",
					url: base_url + "ajax/ajax_change_attach_remark" ,
					dataType: "json",
					data: { 
							file_id 	: temp_existing_id,
							remark 		: attach_attachment_remark, 
							is_temp 	: temp_is_temp
						  },
					success: function( data ) {
					},
					error: function (xhr, ajaxOptions, thrownError) {
						//console.log( xhr );
						//console.log( ajaxOptions );
						//console.log( thrownError );
					}
				});
	    	}

			$(this).off('hide.bs.modal');

		}
	});

}