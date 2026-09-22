$( document ).ready(function() {
    init();
    
    /*if( $('#customer_no').val() == '' ){
		$('#btSave').prop('disabled', true);
	}

	set_nationality( $( '#nationality_dd' ).val() , true );*/

	if($('#acc_type').val() == 'r' || $('#acc_type').val() == 'a' || $('#acc_type').val() == '') {
		$('#company_related').toggle(false);
		$('#icno_required').show();
	} else {
		$('#company_related').toggle(true);
		$('#icno_required').hide();
	}

	if ((($('#icno').val() || '').trim()) !== '') {
		ajax_check_icno($('#icno').val().trim());
	}

	$('#icno').on('blur', function() {
      var value = $(this).val().trim();
      if (value !== '') {
        ajax_check_icno(value);
		if ($('#tin').val().trim() !== '' && ($('#acc_type').val().trim() == 'r' || $('#acc_type').val().trim() == 'a')) {
			ajax_validate_tin($('#tin').val().trim(), value, 'NRIC');
		}
      }
    });
	
	if ((($('#ssm').val() || '').trim()) !== '') {
		ajax_check_ssm($('#ssm').val().trim());
	}

	$('#ssm').on('blur', function() {
      var value = $(this).val().trim();
      if (value !== '') {
        ajax_check_ssm(value);
		if ($('#tin').val().trim() !== '' && ($('#acc_type').val().trim() == 'b' || $('#acc_type').val().trim() == 'o')) {
			ajax_validate_tin($('#tin').val().trim(), value, 'BRN');
		}
      }
    });

	if ((($('#acc_email').val() || '').trim()) !== '') {
		ajax_check_email($('#acc_email').val().trim());
	}

	$('#acc_email').on('blur', function() {
      var value = $(this).val().trim();
      if (value !== '') {
        ajax_check_email(value);
      }
    });

	$('#same_with_above').on('click', function() {
		const isChecked = $(this).is(':checked');
		if (isChecked) {
			$('#pic_name\\[0\\]').val($('#acc_name').val());
			$('#pic_nric_passport\\[0\\]').val($('#icno').val());
			$('#pic_mobile\\[0\\]').val($('#acc_mobileno').val());
			$('#pic_email_1\\[0\\]').val($('#acc_email').val());

			$('#pic_name\\[0\\]').attr('readonly', true);
			$('#pic_nric_passport\\[0\\]').attr('readonly', true);
			$('#pic_mobile\\[0\\]').attr('readonly', true);
			$('#pic_email_1\\[0\\]').attr('readonly', true);
		}else{
			$('#pic_name\\[0\\]').attr('readonly', false);
			$('#pic_nric_passport\\[0\\]').attr('readonly', false);
			$('#pic_mobile\\[0\\]').attr('readonly', false);
			$('#pic_email_1\\[0\\]').attr('readonly', false);
		}
	});

	$('#tin').on('blur', function () {
		var value = $(this).val().trim();
		if (value !== '' && $('#acc_type').val().trim() !== '' && ($('#icno').val().trim() !== '' || $('#ssm').val().trim() !== '')) {
			let idValue = '';
			let idType = '';
			if($('#acc_type').val() == 'r' || $('#acc_type').val() == 'a') {
				idValue = $('#icno').val().trim();
				idType = 'NRIC';
			} else {
				idValue = $('#ssm').val().trim();
				idType = 'BRN';
			}
			ajax_validate_tin($('#tin').val().trim(), idValue, idType);
		}
	});
});

function init()
{
	$(".pic_dob").datepicker({format: 'yyyy-mm-dd'});

	$(document).on("click", ".customer-pic-span", function(e){
		var panel = $(this).text() ;
		$( '.customer-pic' ).addClass('hide') ; 
		$( '.customer-pic' ).eq( panel - 1 ).removeClass('hide');
		$( '.customer-pic' ).eq( panel - 1 ).find( '.customer-pic-span' ).eq( panel - 1 ).css('text-decoration','underline');
		
		
	});
	
	$(document).on("click", ".customer-pic-more", function(e){
		
		var newPic = $( '.customer-pic' ).length + 1 ;
		var picLegend  = "";
			picLegend += "Personal & Contacts Information" ;
		var html = "<div class=\"col-lg-12 customer-pic new-customer-pic\">" + 
						"<fieldset class=\"category-border\">" + 
						"<legend class=\"category-border pic-legend\">" + 
						"Personal & Contacts Information" ;

		let acc_id = $('#acc_id').val();
						
		for( var z = 0 ; z < newPic ; z++ ){
			if( (z+1) == newPic )
				html += 	"[ <span class=\"customer-pic-span\" style=\"text-decoration:underline;cursor:pointer;\">"+(z+1)+"</span> ]";
			else
				html += 	"[ <span class=\"customer-pic-span\" style=\"cursor:pointer;\">"+(z+1)+"</span> ]";
			
			picLegend +="[ <span class=\"customer-pic-span\" style=\"cursor:pointer;\">"+(z+1)+"</span> ]";
		}
			if (newPic < profile_pic_count) {
				picLegend +="[ <span class=\"customer-pic-more\" style=\"cursor:pointer;\">more</span> ]";
			}
			
			html +=		(newPic < profile_pic_count? "[ <span class=\"customer-pic-more\" style=\"cursor:pointer;\">more</span> ]" : "") + 
					"</legend>" + 
					"<div class=\"col-lg-12\" id=\"nric_err\"></div>" + 
					"<div class=\"col-lg-6\">" + 
					"<div id=\"company_section\" class=\"input-group\">" + 
						"<span class=\"input-group-addon input_group\">PIC Name</span>" + 
							"<input type=\"hidden\" id=\"pic_id\" name=\"pic_id["+(z-1)+"]\" value=\"\" />" +
							"<input type=\"hidden\" id=\"pic_acc_id\" name=\"pic_acc_id["+(z-1)+"]\" value=\""+acc_id+"\" />" +
							"<input id=\"pic_name\" name=\"pic_name["+(z-1)+"]\" value=\"\" type=\"text\" class=\"form-control\" placeholder=\"\">" + 
					"</div>" + 
					"<div class=\"input-group\">" + 
					"<span class=\"input-group-addon input_group\" id=\"nric_span\" " + 
					" >NRIC</span>" + 
						"<input id=\"pic_nric_passport\" name=\"pic_nric_passport["+(z-1)+"]\" value=\"\" type=\"text\" " + 
						"class=\"form-control\" onkeypress=\"return /[a-zA-Z0-9]/i.test(event.key)\" >" +
					"</div>" +
					"<div class=\"input-group\">" + 
						"<span class=\"input-group-addon input_group\">Date of Birth</span>" +
							"<input id=\"pic_dob\" name=\"pic_dob["+(z-1)+"]\" value=\"\" type=\"text\" " +
							"class=\"form-control pic_dob\" placeholder=\"Date of Birth\" >" +
					"</div>" +
					"<div class=\"input-group\">" +
						"<span class=\"input-group-addon input_group\" >Race</span>" +
							"<select class=\"form-control\" id=\"pic_race\" name=\"pic_race["+(z-1)+"]\" style=\"font-size:12px;\">" +
								"<option value=\"m\">Malay</option>" +
								"<option value=\"c\">Chinese</option>" +
								"<option value=\"i\">Indian</option>" +
								"<option value=\"o\">Others</option>" +
							"</select>" + 
					"</div>" +
					"<div class=\"input-group\">" + 
						"<span class=\"input-group-addon input_group\" >Gender</span>" +
							"<select class=\"form-control\" id=\"pic_gender\" name=\"pic_gender["+(z-1)+"]\" style=\"font-size:12px;\">" +
								"<option value=\"m\">Male</option>" +
								"<option value=\"f\">Female</option>" +
							"</select>" +
						"</div>" +
					"</div>" +
					"<div class=\"col-lg-6\">" +
						"<div class=\"input-group\">" +
						"<span class=\"input-group-addon input_group\" >Designation</span>" +
							"<input id=\"pic_designation\" name=\"pic_designation["+(z-1)+"]\" value=\"\" " +
									"type=\"text\" class=\"form-control\" placeholder=\"Designation\" >" +
						"</div>" +
					"</div>" +
					"<div class=\"col-lg-6\">" +
					"<div class=\"input-group\">" +
						"<span class=\"input-group-addon input_group\" >Mobile</span>" +
							"<input id=\"pic_mobile\" name=\"pic_mobile["+(z-1)+"]\" value=\"\" type=\"text\" " +
								"class=\"form-control\" placeholder=\"eg. 60188888888\" onkeypress=\"return /[0-9]/i.test(event.key)\" maxlength=\"15\" >" +
					"</div>" +
					"<div class=\"input-group\">" +
						"<span class=\"input-group-addon input_group\" >Email 1</span>" +
							"<input id=\"pic_email_1\" name=\"pic_email_1["+(z-1)+"]\" value=\"\" type=\"text\" " +
									"class=\"form-control\" placeholder=\"Email\" >" +
					"</div>" +
					"<div class=\"input-group\">" +
						"<span class=\"input-group-addon input_group\" >Email 2</span>" +
							"<input id=\"pic_email_2\" name=\"pic_email_2["+(z-1)+"]\" value=\"\" type=\"text\" " +
									"class=\"form-control\"  placeholder=\"Alternative Email\" >" +
					"</div>" +
					
					"<div class=\"input-group\">"+
						"<input type=\"checkbox\" name=\"delete["+(z-1)+"]\" value=1 style='margin-right: 1rem;' /> <span class='red'>DELETE WHEN SAVE</span>" +
						"</div>" +
					"</div>" +
				"</fieldset>" +
			"</div>";
		
		$('.customer-pic').addClass('hide');
		$('.pic-legend').html( picLegend );
		$('.customer-pic:last').after( html );
		$('.customer-pic:last').removeClass('hide');

		$(".pic_dob").datepicker({format: 'yyyy-mm-dd'});

	});

	set_nationality( $( '#nationality_dd' ).val() , true );

	$( document ).on('change', '#nationality_dd', function(e){
		set_nationality( $( this ).val() );
	});

	window.addEventListener( "pageshow", function ( event ) {
	  var historyTraversal = event.persisted || 
	                         ( typeof window.performance != "undefined" && 
	                              window.performance.navigation.type === 2 );
	  if ( historyTraversal ) {
	    // Handle page restore.
	    window.location.reload();
	  }
	});
	
}

function ajax_check_icno($icno) {
	if ($('#acc_type').val().toUpperCase() !== 'R') return;
	
	$.ajax({
		type: 'POST',
		url: base_url + 'profile/ajax_check_icno',
		data: { icno: $icno, acc_id: $('#acc_id').val()},
		success: function(data) {
			var parsed = JSON.parse(data);
			if (parsed.exist) {
				$('.icno-exist').css('display', 'block');
				$('#btSave').prop('disabled', true);
			} else {
				$('.icno-exist').css('display', 'none');
				$('#btSave').prop('disabled', false);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.error(xhr, ajaxOptions, thrownError);
		}
	});
}

function ajax_check_email(email) {
	$.ajax({
		type: 'POST',
		url: base_url + 'profile/ajax_check_email',
		data: { email: email, acc_id: $('#acc_id').val()},
		success: function(data) {
			let parsed = JSON.parse(data);
			
			if (parsed.exist) {
				$('.email-exist').css('display', 'block');
				$('#btSave').prop('disabled', true);
			} else {
				$('.email-exist').css('display', 'none');
				$('#btSave').prop('disabled', false);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.error(xhr, ajaxOptions, thrownError);
		}
	});
}

function ajax_check_ssm($ssm) {
	if (!['b', 'o'].includes($('#acc_type').val().toLowerCase())) return;

	$.ajax({
		type: 'POST',
		url: base_url + 'profile/ajax_check_ssm',
		data: { ssm: $ssm, acc_id: $('#acc_id').val()},
		success: function(data) {
			var parsed = JSON.parse(data);
			if (parsed.exist) {
				$('.ssm-exist').css('display', 'block');
				$('#btSave').prop('disabled', true);
			} else {
				$('.ssm-exist').css('display', 'none');
				$('#btSave').prop('disabled', false);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.error(xhr, ajaxOptions, thrownError);
		}
	});
}

$('#acc_type').change(function () {
	let companyFields = $('#company_related');
	let idValue = '';
	let idType = '';
	if($('#acc_type').val() == 'r' || $('#acc_type').val() == 'a') {
		companyFields.toggle(false);
		$('#comp_name').val('');
		$('#ssm').val('');
		$('#sst').val('');
		$('#ttx').val('');
		idValue = $('#icno').val().trim();
		idType = 'NRIC';
		$('#icno_required').show();
	} else {
		companyFields.toggle(true);
		idValue = $('#ssm').val().trim();
		idType = 'BRN';
		$('#icno_required').hide();
	}

	if($('#tin').val() !== '') {
		ajax_validate_tin($('#tin').val().trim(), idValue, idType);
	}

	handle_create_login_alert($('#acc_type').val());
})

function handle_create_login_alert(type) {
	if(profile_linked == '1') return;
	const isResidential = type === 'r';
	const isCommercial = type === 'b' || type === 'o';

	$('#email-input-grp, #ssm-input-grp, #ic-input-grp')
		.removeClass('input-highlight');
	$('#main-login-creation-alert, #residential-login-creation-info, #commercial-login-creation-info')
		.addClass('hide');

	if (isResidential) {
		$('#email-input-grp, #ic-input-grp').addClass('input-highlight');
		$('#main-login-creation-alert, #residential-login-creation-info').removeClass('hide');
	} 
	else if (isCommercial) {
		$('#email-input-grp, #ssm-input-grp').addClass('input-highlight');
		$('#main-login-creation-alert, #commercial-login-creation-info').removeClass('hide');
	}
}

function set_nationality( nationality, init = false ){
	if( nationality == 'Malaysian' ){
		$('#nationality').val( nationality );
		$('.nationality_others').hide();
	}else{
		if( init == false ) $('#nationality').val( "" );
		$('.nationality_others').show();
	}	
}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#profile_detail').submit(function(e) {
	e.preventDefault();
	$('.input-group-addon').parent().removeClass('has-error');
	$('.button-group button').prop('disabled', true);
	$('#acc_type').removeAttr('disabled');
	$('#agent_id').removeAttr('disabled');

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
	//post values

	if (filter_pressed == 1) {
		$("#page_item_no").val(0);
	}

	$.ajax({
		type: "POST",
		url: base_url + "profile/profile_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			//sel_status: $("#sel_status").val(),
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
	//$("#sel_status").val('P');
	ajax_filter(1);
}

function ajax_validate_tin(tin, idValue, idType) {
  $.ajax({
    type: 'POST',
    url: base_url + 'ajax/validate_tin',
    data: { 
      tin: tin, 
      idValue: idValue,
      idType: idType
    },
    dataType: 'json',
    success: function (response) {
      	$('.tin-invalid').css('display', 'none');
        $('.tin-valid').css('display', 'none');
        $('.fail-connection').css('display', 'none');

        if(response.connected == false){
          $('.fail-connection').css('display', 'block');
        }else if(response.is_valid){
          $('.tin-valid').css('display', 'block');
        }else{
          $('.tin-invalid').css('display', 'block');
        }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.error(xhr, ajaxOptions, thrownError);
    }
  });
}
