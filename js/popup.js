function center_popup(){
    //request data for centering
    var windowWidth = document.documentElement.clientWidth;
    var windowHeight = document.documentElement.clientHeight;
    var popupWidth = $("#popupDetail").width();
    var popupHeight = $("#popupDetail").height();
    
	var isMobile = windowWidth <= 768;

    //centering
    $("#popupDetail").css({
    	"position": "absolute",
        "top": 50,
    	//"top": windowHeight/2-popupHeight/2,
    	"left": isMobile? "auto" : windowWidth/2-popupWidth/2,
		"margin-right": windowWidth/4+"px"
    });
}

function show_popup(page, parameter){
    center_popup();
    $("#backgroundPopup").fadeIn("fast");
    $("#popupDetail").fadeIn("fast");
    
    $('#popupContent').show().toggleClass('loadingIcon').load(base_url+page+parameter).toggleClass('loadingIcon');
}

function show_popup2(page, parameter){
    //center_popup();
    $("#backgroundPopup").fadeIn("fast");
    $("#popupDetail").fadeIn("fast");

    $('#loading_symbol').css('display', 'none');
    
    $('#popupContent').show().toggleClass('loadingIcon').load(base_url+page+parameter).toggleClass('loadingIcon');

    setTimeout(function(){
    	$('#popupDetail #include_customer').val($('#trigger_include_customer').val());

    	if ($('#trigger_include_customer').val() == '1') {
    		$('.user_customer').html('Users/Customers');

    		$('.user_customer_col_1').removeClass('col-lg-1');
    		$('.user_customer_col_1').addClass('col-lg-2');

       		$('.user_customer_col_2').removeClass('col-lg-11');
    		$('.user_customer_col_2').addClass('col-lg-10');
    	}
    }, 50);
}

function hide_popup(){
    $("#backgroundPopup").fadeOut("fast");
    $("#popupDetail").fadeOut("fast");
}

function save_action(ref_page, form_id) {
	var data = $("#"+form_id).serialize();
	
	if (!data) {
		alert("User ID cannot be empty!");
	}
	else {
		res_obj = $.post(base_url+ref_page, data);
		res_obj.done( function (return_data) {
				window.location.href = return_data;
			});
		
	}
}

function delete_action(ref_page, delete_id) {
	var data = $("#"+delete_id).val();
	if (!data) {
		alert("Delete failed!");
	}
	else {
		res_obj = $.post(base_url+ref_page, { username: data });
		res_obj.done( function (return_data) {
				window.location.href = return_data;
			});
		
	}
}



