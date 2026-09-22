console.log('===================mailing_list.js=====================');

$( document ).ready(function() {
    init();
});

$('.attach_file').click(function(){
	console.log($(this));	
});

function checkAttachment(){
	console.log('func: checkAttachment()');
	var count 		= $('.attach_file').length;
	var checkFile 	= '';
	for(i = 0; i < count; i++){
		checkFile = $('.attach_file').eq(i).attr('data-file');
		if(checkFile != ''){
			$('.attach_file').eq(i).show();			
			$('.attach_file').eq(i).text(checkFile.substring(0, 12));
		}
	}
}

function viewBill(e){
	var file_name = $(e).attr('data-file');	
	
	if (file_name == null || file_name == ''){
		alert('no file exist');
	}else{
		console.log('checkFileExist');
		checkFileExist = attachment_folder+file_name;
		$.ajax({
			url:base_url + "bill/check_bill_file_exist",
			type:'POST',
			dataType: "json",
			data: { bill_file_name: file_name },
			success: function(data)
			{
				if(data.success == 'SUCC') {
					//file exists
					console.log(checkFileExist);
					window.open(checkFileExist);
				} else {
					//file not exists
					console.log(checkFileExist+' NOT EXIST!!');
					alert('File has been removed or it does not exist');
				}				
			}
		});	
	}
	
	return false;
}

function init()
{
	$("#date_of_birth").datepicker({format: 'yyyy-mm-dd'});
	$("#signup_date").datepicker({format: 'yyyy-mm-dd'});
	$("#activated_date").datepicker({format: 'yyyy-mm-dd'});
	$("#suspended_date").datepicker({format: 'yyyy-mm-dd'});
	$("#terminated_date").datepicker({format: 'yyyy-mm-dd'});
	checkAttachment();
	toggleCompanySection();
	load_package_detail();
}

$( "#status" ).change(function () {
	if ( $(this).val() == 't' ) {
		var c_date = new Date( $( "#activated_date" ).val() );
		var now = new Date();
		c_date.setMonth( c_date.getMonth() + $( "#contract_month" ).val());
		
		if ( c_date <= now ) {
			alert("Note: still under contract period!");
		}
	}
});

$( "#category" ).change(function () {
	toggleCompanySection();
	load_package_list();
});

$( "#package" ).change(function () {
	load_package_detail();
});

function toggleCompanySection() {
	if ($( "#category" ).val() == 'r') {
		$("[id=company_section]").hide();
		$("[id=wholesale_section]").hide();
		$("[id=non_wholesale_section]").show();
	}
	else if ($( "#category" ).val() == 'b') {
		$("[id=company_section]").show();
		$("[id=wholesale_section]").hide();
		$("[id=non_wholesale_section]").show();
	}
	else {
		$("[id=company_section]").show();
		$("[id=wholesale_section]").show();
		$("[id=non_wholesale_section]").hide();
	}
}

function load_package_list() {
	$.ajax({
			type: "POST",
			url: base_url + "ajax/load_package_list",
			dataType: "json",
			data: { category: $( "#category" ).val() },
			success: function( data ) {
				var sel_package = $('#package');
				$("#package").empty();
				
				$.each(data, function(){
					sel_package.append( $("<option></option>").attr("value",this.package_no).text(this.name) );
				});

				//Force to selected first option
				$("#package").val($("#package option:first").val());
				
				load_package_detail();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(thrownError);
			}
	});
}

function load_package_detail() {
	$.ajax({
			type: "POST",
			url: base_url + "ajax/load_package_detail",
			dataType: "json",
			data: { package_no: $( "#package" ).val(), 
					customer_no: $( "#customer_no" ).val()
				},
			success: function( data ) {
				$( '#package_name' ).val(data.name);
				$( '#monthly_charge' ).val(data.monthly_charge);
				$( '#package_month' ).val(data.package_month);
				$( '#stop_service_after').prop('checked', (data.stop_service_after==1 ? true: false));
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(thrownError);
			}
	});
}

function ajax_filter(filter_pressed=0) {
	if (filter_pressed == 1) $("#page_item_no").val(0);
	
	$.ajax({
		type: "POST",
		url: base_url + "email/mailing_list_rows/0",
		data: { 
			page_item_no: $("#page_item_no").val(),
			txt_search: $("#txt_search").val(),
			sel_category: $('#sel_category').val(),
			sel_status: $('#sel_status').val()
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
	$('#sel_category').val('all');
	$('#sel_status').val('all');
	ajax_filter(1);
}
