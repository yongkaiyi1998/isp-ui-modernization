console.log('============================= customer_deposit.js ============================');

$( document ).ready(function() {
	init();
});

function init()
{	
	$("input[name=customer_deposit\\[deposit_date\\]]").datepicker({format: 'yyyy-mm-dd'});
	print_deposit_invoice();
	print_deposit_receipt();
}

function print_deposit_invoice()
{
	$('.print_invoice').click(function(e){		
		if($(this).attr('data-id'))
		{			
			//~ console.log(base_url+'customer/deposit_invoice/'+$(this).attr('data-id'));
			window.location = base_url+'customer/deposit_invoice/'+$(this).attr('data-id');
		}
	});
}

function print_deposit_receipt()
{	
	$('.print_receipt').click(function(e){		
		if($(this).attr('data-id')){			
			//~ console.log(base_url+'customer/deposit_receipt/'+$(this).attr('data-id'));
			window.location = base_url+'customer/deposit_receipt/'+$(this).attr('data-id');
		}
	});
}
