$( document ).ready(function() {
    init();
    
	tinymce.init({
		selector: '.tinymce-editor',
		menubar: false,
		statusbar: true,
		force_p_newlines : false,
		force_br_newlines : false,
		forced_root_block : '',
		plugins: "code table paste image table",
	});
    
});

$('.cancel_button').click(function () {	
	if(typeof url_cancel_button == 'undefined') url_cancel_button = '';	
	var new_tab = 0;	
	var url 	= url_cancel_button;	
	
	if(url != ''){		
		if(new_tab == 1) window.open(url);
		else window.location = url;
	}	
});

$('.delete_button').click(function () {	
	
	if(typeof url_delete_button == 'undefined') url_delete_button = '';	
	if(typeof func_delete_button == 'undefined') func_delete_button = '';		
	var new_tab = 0;	
	var url 	= url_delete_button;	
	
	if(url != '')
	{		
		console.log('got url');
		if(new_tab == 1) window.open(url);
		else window.location = url;
	}
});

$('.add_button').click(function () {
		
	if(typeof url_add_button == 'undefined') url_add_button = '';	
	var new_tab = 0;	
	var url 	= url_add_button;	
	
	if(url != ''){		
		if(new_tab == 1) window.open(url);
		else window.location = url;
	}	
	
});

