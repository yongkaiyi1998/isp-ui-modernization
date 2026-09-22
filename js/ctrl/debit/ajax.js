// ========================================================== JSON AJAX ======================================= 
// -------------------------- retreive JSON data from customer table  -----------------------------------------
function custInfoAutocomplete(){	
	console.log('ajax: ->custInfoAutocomplete');
	$('#custInfo').autocomplete({
		source: function (request, response) {
			$.ajax({
				url: baseURL+"customers/ajax_custInfo",
				dataType:"json",
				data: { query: request.term },
				success: function (data) {					
					var transformed = $.map(data, function (el) 
						{		
							real_val = el;
							//console.log(real_val);
							return {
									label		: el.comp_name,
									id			: el.cust_id,
									realValue	: real_val,		
							};
						});					
					response(transformed);
				},
				error: function () {
					response([]);
				}
			});
		},
		messages: {
			noResults: '',
			results: function() {}
		},
		select: function (event, ui) {			
			//console.log(ui.item);
			var sessionCustID = setInvoiceHeader(ui.item);
			
			console.log('ajax:||===>'+sessionCustID);
			
			if(sessionCustID != 0){
				setInvoiceHeaderSession(sessionCustID);				
			}			
		}
	});
	console.log('ajax: <-custInfoAutocomplete');
}
	
	
	
// -------------------------- function to set invoice_head input value into session ---------------------------
setInvoiceHeaderSession = function(sessionCustID) 
{		
	console.log('ajax: ->setInvoiceHeaderSession'+baseURL+controller);
	var controller_url = baseURL+controller+'/ajax_setCust/'+sessionCustID;
	//~ console.log(controller);
	
	$.ajax({
		url: controller_url,
		dataType:'html',				
		data: 'post',
		success: function (data) {					
			//console.log('set session pass!');
		},
		error: function () {
			//console.log('set session fail');
			response([]);
		}
	});
	
	console.log('ajax: <-setInvoiceHeaderSession');
			
}
		
// -------------------------- function to set invoice_head input value  -------------------------- ------------
setInvoiceHeader = function(object) 
{
	console.log('ajax: ->setInvoiceHeader');
	var sessionCustID = '0';
	//console.log(object);
	$.each(object, function(item, value){			
		if(item == 'realValue'){
			//console.log(item);
			//console.log(value);
			$.each(value, function(cust_key, cust_val)
			{					
				if(cust_key != 'is_active'){					
					//~ console.log(cust_key);
					//~ console.log(cust_val);
					if(cust_key == 'cust_id'){
						sessionCustID = cust_val;
					}								
					$('#'+db_table+'\\['+cust_key+'\\]').val(cust_val);											
				}				
			});				
		}
		
	});	
	console.log('ajax: <-setInvoiceHeader return:'+sessionCustID);
	return sessionCustID;				
}

