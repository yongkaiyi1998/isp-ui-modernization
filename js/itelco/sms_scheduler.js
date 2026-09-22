function init()
{
	if(is_edit == '0') $('#sms_cust_cat').val('r');
	initialize_cbox();
	initialize_date();
	initialize_actions();

	if(disable_input == '1')
	{
		$('input').prop("disabled", true);
		$('textarea').prop("disabled", true);
		$('select').prop("disabled", true);
		$('input[name=scheduler_id]').prop('disabled',false);
		$('#btDelete').addClass('disabled');
		$('#btSave').addClass('disabled');
	}
	else
	{
		$('input').prop("disabled", false);
		$('textarea').prop("disabled", false);
		$('select').prop("disabled", false);
		$('input[name=scheduler_id]').prop('disabled',false);
		$('#btDelete').removeClass('disabled');
		$('#btSave').removeClass('disabled');
		
		sms_autocomplete();
		customer_sms_autocomplete();
	}

}

function initialize_cbox(){
	str_1 					= $('#sms_cust_cat').val();
	sms_cust_cat 			= str_1.split(",");
	sms_cust_cat_length 	= sms_cust_cat.length;

	for(scc = 0; scc < sms_cust_cat_length; scc++)
	{
		cust_cat_data_id = sms_cust_cat[scc];
		$('*[class="cust_cat"][data-id="'+cust_cat_data_id+'"]').prop( "checked", true);
	}

	str_2 					= $('#sms_cust_status').val();
	sms_cust_status 		= str_2.split(",");
	sms_cust_status_length 	= sms_cust_status.length;

	for(scs = 0; scs < sms_cust_status_length; scs++)
	{
		cust_status_data_id = sms_cust_status[scs];
		$('*[class="cust_status"][data-id="'+cust_status_data_id+'"]').prop( "checked", true);
	}
}
//====================================================================== datetimepicker
function initialize_date()
{
	$("#sms_schedule_on").datetimepicker({
		format: 'YYYY-MM-DD HH:mm',
		sideBySide : true,
	});
}

//====================================================================== actions
function initialize_actions()
{
	$('.cust_cat').click(function(){

		cbox_cat_length = $('.cust_cat').length;
		final_cust_cat	= '';
		for(cc = 0; cc < cbox_cat_length; cc++)
		{
			data_id = $('.cust_cat').eq(cc).attr('data-id');
			if ($('.cust_cat').eq(cc).prop('checked') == true){
				final_cust_cat = final_cust_cat + data_id + ',';
			}
		}
		$('#sms_cust_cat').val(final_cust_cat);
	});

	$('.cust_status').click(function(){

		cbox_status_length = $('.cust_status').length;
		final_cust_status	= '';
		for(cs = 0; cs < cbox_status_length; cs++)
		{
			data_id = $('.cust_status').eq(cs).attr('data-id');
			if ($('.cust_status').eq(cs).prop('checked') == true){
				final_cust_status = final_cust_status + data_id + ',';
			}
		}
		$('#sms_cust_status').val(final_cust_status);
	});

	$('.clear_field').click(function(){
		$('#sms_autocomplete').val('');
		$('.clear_field_group').hide();
	});
}
//====================================================================== ajax

function sms_autocomplete()
{
	console.log('||===> func: sms_autocomplete()');
	console.log('ajax: ->sms_autocomplete');
	$('#sms_autocomplete').autocomplete({
		source: function (request, response)
		{
			$.ajax({
				url: base_url+"/ajax_set_data",
				dataType:"json",
				data: { query: request.term },
				success: function (data)
				{
					var transformed = $.map(data, function (el)
					{
						real_val = el;
						// console.log(real_val);
						return {
								label		: el.sms_title+' (created on : '+el.sms_schedule_on+')',
								id			: el.scheduler_id,
								realValue	: real_val,
						};
					});
					response(transformed);
				},
				error: function ()
				{
					response([]);
				}
			});
		},
		messages:
		{
			noResults: '',
			results: function() {}
		},
		select: function (event, ui)
		{
			// console.log(ui.item);
			var set_field_result = set_field_value(ui.item);

			// console.log('if successfully populate, clear text within');
			if(set_field_result != 0) $(this).val('');
			return false;
		}
	});
	console.log('ajax: <-sms_autocomplete');
	console.log('<===|| func: sms_autocomplete()');
}

set_field_value = function(object)
{
	console.log('||===> funcVar: set_field_value');
	var scheduler_id = '0';
	//console.log(object);
	$.each(object, function(item, value)
	{
		// console.log(item);
		if(item == 'realValue' || item == '0')
		{
			// console.log(value);
			$.each(value, function(data_key, data_val)
			{
				//return id show that it is selected
				if(data_key == 'scheduler_id') scheduler_id = data_val;

				//populate input sms_title
				if(data_key == 'sms_title') $('input[name=sms_title]').val(data_val);

				//populate textarea sms_msg
				if(data_key == 'sms_msg') $('textarea[name=sms_msg]').val(data_val);
			});
		}
	});
	console.log('<===|| funcVar: set_field_value return:'+scheduler_id);
	return scheduler_id;
}

function customer_sms_autocomplete()
{
	console.log('||===> func: customer_sms_autocomplete()');
	console.log('ajax: ->customer_sms_autocomplete');
	$('#customer_sms_autocomplete').autocomplete({
		source: function (request, response)
		{
			$.ajax({
				url: base_url + "/ajax_get_customer_detail",
				dataType:"json",
				data: { customer_no: request.term },
				success: function (data)
				{
					
					console.log( data );
					
					var transformed = $.map(data['row'], function (el)
					{
						real_val = el;
						// console.log(real_val);
						return {
								label		: el.customer_no+' '+el.name ,
								id			: el.customer_no,
								row		    : real_val,
						};
					});
					response(transformed);
				},
				error: function ()
				{
					response([]);
				}
			});
		},
		messages:
		{
			noResults: '',
			results: function() {}
		},
		select: function (event, ui)
		{
			console.log(ui.item);
			//var set_field_result = set_field_value(ui.item);
			if( ui.item.row['mobile_num'] != '' ){
				if( $('#recipient_mobile_num').val() != '' ){
					$('#recipient_mobile_num').val( $('#recipient_mobile_num').val() + ' ; ' + ui.item.row['mobile_num'] );
				}else{
					$('#recipient_mobile_num').val( ui.item.row['mobile_num'] );
				}
			}
						
			$(this).val('');
			
			// console.log('if successfully populate, clear text within');
			//if(set_field_result != 0) $(this).val('');
			return false;
		}
	});
	console.log('ajax: <-customer_sms_autocomplete');
	console.log('<===|| func: customer_sms_autocomplete()');
}
	
function showFormType(){
	if( $('input[name=send_by]:checked').val() == 'building' ){
		$('#individual_form').hide();
		$('#building_form').show();
	}else if( $('input[name=send_by]:checked').val() == 'individual' ){
		$('#individual_form').show();
		$('#building_form').hide();
	}
}
	
$( document ).on( 'change', 'input[name=send_by]', function(e){
	showFormType();
});

