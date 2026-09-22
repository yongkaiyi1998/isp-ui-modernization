// ========================================================== Mouse Keyboard Event ===============================

$(document).on('change','#check_all',function(){
	console.log("io: -> check all");
	$('input[class=case]:checkbox').prop("checked", $(this).is(':checked'));
});

$('.item-add').on('click',function(){	
	console.log("io: -> add icon");
	addAction();	
});

$('.delete').on('click', function() {
	console.log("io: -> delete icon");	
	
	var checkedRow = $('.case:checkbox:checked').parents("tr").length;
	var i=$('#item-table .item-row').length;
	
	i = i - checkedRow + 1;	
		
	$('.case:checkbox:checked').parents("tr").remove();
	$('#check_all').prop("checked", false); 
	
	//add 1 new row if current row is 0
	var TableRow = $('#item-table .item-row').length;	
	if(TableRow == 0 ){
		console.log("delete all, addAction");
		addAction();
		renameRowID();
		disableOnlyLastButon();
	}
	
	renameRowID();
	disableOnlyLastButon();
	//calculateTotal();
	//calAll();
});


//========================================= other events

$('[data-onload]').each(function(){	
	console.log("io: -> data-onload");
    eval($(this).data('onload'));
});

// ========================================================== Panel Header Action Event ================================== 

function printBtn(bool){
	if(bool){
		$('.print').fadeIn();
		$( "#print" ).click(function() {
			 window.print();
		});
	}else{		
		$('.print').fadeOut();
	}


}


function previewBtn(bool){
	if(bool){
		$('.pdf').fadeIn();
		$( "#pdf" ).click(function() { 
			//console.log("io_top: -> pdf");     	
			var button = '#processtype\\[preview\\]';
			$(button).click();
		});	
	}else{		
		$('.pdf').fadeOut();
	}	
}

function saveBtn(bool){
	if(bool){
		$('.save').fadeIn();
		$( "#save" ).click(function() {  
			//console.log("io_top: -> save");   	
			var button = '#processtype\\[submit\\]';
			$(button).click();		
		});	
	}else{		
		$('.save').fadeOut();
	}	
}

function mainDiscardBtn(bool){
	if(bool){
		$('.main_back').show();
		$( "#main_back" ).click(function() {  
			//console.log("io_top: -> main_back");   	
			var button = '#processtype\\[discard\\]';	
			$(button).click();
		});
	}else{		
		$('.main_back').hide();
	}	
}

function mainBackBtn(bool){
	if(bool){
		$('.main_back').show();
		$( "#main_back" ).click(function() {  
			//console.log("io_top: -> main_back");   	
			var button = '#processtype\\[back\\]';	
			$(button).click();
		});
	}else{		
		$('.main_back').hide();
	}	
}
