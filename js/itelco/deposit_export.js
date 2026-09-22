$( document ).ready(function() {
    init();
});

function init()
{
	$("#deposit_date_start").datepicker({format: 'yyyy-mm-dd'});
	$("#deposit_date_end").datepicker({format: 'yyyy-mm-dd'});	
}

function generate_xls_filtered()
{
	var form = $('form');
	form.attr('action', base_url + 'customer/excel_filter');
	//form.attr('target', '_self');
	$('#btFilter').click();
	form.attr('action', base_url + 'customer/export_deposit') ;
	//form.removeAttr('target');
}
