var date = new Date();

$( document ).ready(function() {
    init();
    
    $('#txt_date_start').datepicker({format: 'yyyy-mm-dd'});
	$('#txt_date_end').datepicker({format: 'yyyy-mm-dd'});
	
	//~ $('.report_table .report_table_header').find('th').eq(6).hide();
	//~ check_grand_total();
	//~ $('.report_table').show();
});

function init(){}

function check_grand_total(){
	var gt_td = $('.grand_total td').length;
	var other_td = $('.grand_total td').length+3;
	var cust_record_length = $('.cust_record').length;
	var check_to_hide_length = $('.to_hide').length;
	var cat_header_length = $('.cat_header').length;
	var tr_subtotal_length = $('.col_subtotal').length;
	
	console.log('got_to_hide');
	for(m=0;m<cat_header_length;m++){
		cat_header_col = 20 - check_to_hide_length;
		$('.cat_header td').eq(m).attr('colspan',cat_header_col);
	}
	for(i=0;i<gt_td;i++){
		//~ $('.report_table .report_table_header').find('th').eq(6).hide();
		
		console.log('i ='+i);
		got_to_hide = $('.grand_total td').eq(i).hasClass( "to_hide" );
		if(got_to_hide)
		{
			j=i+3;		
			$('.data-col-'+i).hide();
			$('.grand_total td').eq(i).hide();	
			for(n=0;n<tr_subtotal_length;n++){
				$('.col_subtotal').eq(n).find('td').eq(i).hide();
			}							
			$('.report_table .report_table_header').find('th').eq(j).hide();
		}
		
		console.log(got_to_hide);
	}
}
$("#btn_current_month").click( function() { 
	get_date_range(true);
});


$("#btn_previous_month").click( function() {
	get_date_range(false);
});

function get_date_range(this_month)
{
	if (this_month) {
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
		var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
	}
	else {
		var firstDay = new Date(date.getFullYear(), date.getMonth() - 1, 1);
		var lastDay = new Date(date.getFullYear(), date.getMonth(), 0);
	}
	
	$('#txt_date_start').val( $.datepicker.formatDate('yy-mm-dd', firstDay) );
	$('#txt_date_end').val( $.datepicker.formatDate('yy-mm-dd', lastDay) );
}

$( "#txt_search" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "ajax/autocomplete_customer",
			dataType: "json",
			data: { keyword: request.term },
			success: function( data ) {
				//console.log(data);
				var transformed = $.map(data, function (el) {		
					return {
							label		: (el.customer_no + ' ' + el.name),
							id			: el.customer_no,
							val			: el
					};
				});					
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(thrownError);
				//alert(xhr.status);
				//alert(thrownError);
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {			
		console.log(ui.item.val);
		$( "#txt_customer_no" ).val(ui.item.val.customer_no);
	}
});
