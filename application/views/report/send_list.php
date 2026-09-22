<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					{page_title}
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="user_customer_col_1 col-lg-1" style="font-size:14px;">
					<span class="user_customer">Users</span>
				</div>
				<div class="user_customer_col_2 col-lg-11">				
					<button  id="btAddUser" name="btAddUser" type="button" value="cancel" class="btn btn-info" onclick="add_user_row();">
					Add
					</button>
				</div>
				<div class="col-lg-12">
					<table class="table table-striped table-hover" id="user_table">
						<thead>
							<tr>
								<th class="col-lg-2"><span class="user_customer">User</span></th>
								<th class="col-lg-2">Email</th>
								<th class="col-lg-2">Phone</th>
								<th class="col-lg-2">Telegram</th>
								<th class="col-lg-1 text-center"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-1" style="font-size:14px;">
					Custom
				</div>
				<div class="col-lg-11">				
					<button  id="btAddUser" name="btAddUser" type="button" value="cancel" class="btn btn-info" onclick="add_custom_row();">
					Add
					</button>
				</div>
				<div class="col-lg-12">
					<table class="table table-striped table-hover" id="custom_table">
						<thead>
							<tr>
								<th class="col-lg-3">Email/Phone/Telegram Chat ID</th>
								<th class="col-lg-2">Type</th>
								<th class="col-lg-1"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>

			<div class="row">

				<input type="hidden" id="include_customer" name="include_customer" value="0" />

				<button  id="btSendCancel" name="btSendCancel" type="button" value="cancel" class="btn btn-warning" onclick="prep_hide_popup();" <?php echo tooltip_helper('Cancel / Discard'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
					Cancel
				</button>
				
				<button id="btSendSave" name="btSendSave" type="button" value="button" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> onclick="construct_field();" >
					<i class="menu-icon fa fa-send white" data-toggle="tooltip" title=""></i>		
					Send					
				</button>

				<img src="<?php echo base_url('images/loading.gif'); ?>" class="loading_symbol" id="loading_symbol" style="display:none;"/>

			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url("js/jquery.forms.js?".cssjs_ver()); ?>"></script>
<script>

$(document).ready(function() {
	$('#report').ajaxForm(function() { 
		//do ajax submit then proceed to close window
		$('#report').ajaxFormUnbind();
		$('#contact_list').val('');
		$('#isprint').val('0');
		hide_popup();
		$('.msg-print').prepend(`<div id="success" class="alert alert-success" role="alert">Document Sent.</div>`);
		setTimeout(function(){$('#success').fadeOut();},2000);
    });

	auto_append_send_user();
});

function auto_append_send_user() {
	try{
		if(default_send_user != '') {
			let user_html = `
				<tr>
				<td>
					<input type="hidden" name="user_id" value="` + default_send_user.acc_id +`" />
					<input type="text" name="user" value="` + default_send_user.acc_name + `" class="form-control user_autocomplete" />
				</td>
				<td>
					<input type="text" name="email" value="` + default_send_user.acc_email + `" class="form-control" />
				</td>
				<td>
					<input type="text" name="phone" value="` + default_send_user.acc_phone + `" class="form-control" />
				</td>
				<td>
					<input type="text" name="telegram_id" value="` + default_send_user.acc_telegram + `" class="form-control" />
				</td>
				<td class="text-center">
					<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
				</td>
			</tr>
			`;
			$('#user_table tbody').append(user_html);
		} else {
			console.log('No default send user');
		}
	} catch (e) {
		console.log('No default send user');
	}
}

function prep_hide_popup() {
	$('#report').ajaxFormUnbind();
	hide_popup();
}

function add_user_row(){
	let user_html = `
	<tr>
		<td>
			<input type="hidden" name="user_id" value="" />
			<input type="text" name="user" value="" class="form-control user_autocomplete" />
		</td>
		<td>
			<input type="text" name="email" value="" class="form-control" />
		</td>
		<td>
			<input type="text" name="phone" value="" class="form-control" />
		</td>
		<td>
			<input type="text" name="telegram_id" value="" class="form-control" />
		</td>
		<td class="text-center">
			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
		</td>
	</tr>
	`;

	$('#user_table tbody').append(user_html);

	//initiate autocomplete
	userAutocomplete();

}

function add_custom_row(){
	let custom_html = `
	<tr>
		<td>
			<input type="text" name="address" value="" class="form-control" />
		</td>
		<td>
			<select class="form-control" name="send_type" style="height:25px;">
				<option value="">--Please Select--</option>
				<option value="email">Email</option>
				<option value="whatsapp">Whatsapp</option>
				<option value="telegram">Telegram</option>
			</select>
		</td>
		<td class="text-center">
			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
		</td>
	</tr>
	`;

	$('#custom_table tbody').append(custom_html);
}

function delLine(elem){
	$(elem).parent().parent().remove();
}

function userAutocomplete()
{		
	$('.user_autocomplete').autocomplete({
		source: function (request, response) {
			$.ajax({
				url: base_url+"ajax/search_user",
				dataType:"json",
				data: { 
					query: request.term,   
					include_customer: $('#include_customer').val(),
				},
				success: function (data) {	
					if (data != '') {				
						var transformed = $.map(data, function (el) 
							{		
								real_val = el;

								/*let show_desc = el.description[0];
								if (el.short_name != '') {
									show_desc = el.short_name;
								}*/

								return {
										label		: el.username,
										id			: el.idx,
										realValue	: real_val,		
								};
							});					
						response(transformed);
					} else {
	                	response([{ label: 'No matches found', id: null, realValue: null }]);
	                }
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

			event.preventDefault();

			let idx 		= ui.item.realValue.idx;
			let username	= ui.item.realValue.username;
			let email		= ui.item.realValue.email;
			let phone		= ui.item.realValue.mobile_no;
			let telegram_id = ui.item.realValue.telegram_id;

			$(this).parent().parent().find('input[name="user_id"]').val(idx);
			$(this).parent().parent().find('input[name="user"]').val(username);
			$(this).parent().parent().find('input[name="email"]').val(email);
			$(this).parent().parent().find('input[name="phone"]').val(phone);
			$(this).parent().parent().find('input[name="telegram_id"]').val(telegram_id);

		},
	});
}

function construct_field(){
	let email_list = [];
	let whatsapp_list = [];
	let telegram_list = [];

	$( "#user_table tbody tr" ).each(function() {
	  let username = $(this).find('input[name="user"]').val();
	  if (username != '') {
	  	let email = $(this).find('input[name="email"]').val();
	  	let phone = $(this).find('input[name="phone"]').val();
	  	let telegram_id = $(this).find('input[name="telegram_id"]').val();

	  	if (email != '') {
	  		email_list.push(email);
	  	}

	  	if (phone != '') {
	  		whatsapp_list.push(phone);
	  	}

	  	if (telegram_id != '') {
	  		telegram_list.push(telegram_id);
	  	}

	  }
	});

	$( "#custom_table tbody tr" ).each(function() {
		let address = $(this).find('input[name="address"]').val();
		let send_type = $(this).find('select[name="send_type"]').val();
		if (address != '' && send_type != '') {
			if (send_type == 'email') {
				email_list.push(address);
			} else if (send_type == 'whatsapp') {
				whatsapp_list.push(address);
			} else if (send_type == 'telegram') {
				telegram_list.push(address);
			}
		}
	});

	$('#contact_list').val(JSON.stringify([email_list, whatsapp_list, telegram_list]));
	//console.log($('#contact_list').val());

	$('#isprint').val('1');

	$('#loading_symbol').css('display', '');
	$('#report').submit();
	//change to loading?

}

</script>