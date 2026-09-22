$( document ).ready(function() {
    init();
	//$('#table_id').DataTable({bFilter: false, bInfo: false});
});

function init()
{
	$("#txt_bill_date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#txt_bill_date_to").datepicker({format: 'yyyy-mm-dd'});	
}

function generate_xml_filtered()
{
	console.log('btXML');
	var form = $('form');
	form.attr('action', base_url+'bill/xml_filtered/');
	form.attr('target', '_self');
	$('#btPrintFiltered').click();
	form.attr('action', base_url + 'bill/export_bill') ;
	form.removeAttr('target');
}
