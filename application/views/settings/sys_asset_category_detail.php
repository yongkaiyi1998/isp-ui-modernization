<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('settings/asset_category_management');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<form id="bill_type_detail" name="bill_type_detail" method="post" class="filter-form" action="<?php echo $form_action; ?>">
			<div class="col-lg-6">
			<?php echo flash_data_helper($msg); ?>
			
				<input type='hidden' id='category_idx' name='category_idx' value='<?php echo $input['category_idx'] ?>' />
				<fieldset class='category-border-main'>	
					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Category Code</span>
							<input type="text" class="form-control" id="category_code" name="category_code" value="<?php echo set_value('category_code', $input['category_code']); ?>" placeholder="Code">
						</div>
					</div>
					<div class="col-lg-12">							
						<div class="input-group">
							<span class="input-group-addon input_group">Category Name</span>
							<input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo set_value('category_name', $input['category_name']); ?>" placeholder="Name">
						</div>
					</div>
				</fieldset>

			</div>

			<div class="col-lg-8">
				<fieldset class='category-border'>
					<legend class="category-border">
						Maintenance PIC
					</legend>

					<table id="maintenance_pic_table" class="rows_table mb-0 table table-sm table-borderless table-striped table-bordered-x brc-secondary-l3 text-dark-m2 radius-1 overflow-hidden">

				        <thead class="text-dark-tp3 bgc-grey-l2 text-90 border-b-1 brc-transparent">
				          <th class="col-1">Name</th>
				          <th class="col-1">Email</th>
				          <th class="col-1">Phone</th>
				          <th class="col-1">Telegram</th>
				          <th></th>
				        </thead>

				        <tbody>

				        <?php foreach ($contacts_pic as $rec) { ?>
				        	<tr class="contacts_pic_tr">
				        		<td>
				        			<input class="form-control" type="text" name="username[]" value="<?php echo $rec['username']; ?>" />
				        			<input type="hidden" name="user_id[]" value="<?php echo $rec['user_id']; ?>" />
				        		</td>
				        		<td><input class="form-control" type="text" name="email[]" value="<?php echo $rec['email']; ?>" /></td>
				        		<td><input class="form-control" type="text" name="phone[]" value="<?php echo $rec['phone']; ?>" /></td>
				        		<td><input class="form-control" type="text" name="telegram_id[]" value="<?php echo $rec['telegram_id']; ?>" /></td>
				        		<td>
				        			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
				        		</td>
				        	</tr>
				        <?php } ?>

				        </tbody>

					</table>

					<button id="btAddContact" onclick="add_user_row();" name="btAddContact" type="button" value="button" class="btn btn-info" <?php echo check_acl_btn('asset','M');?>>
						Add
					</button>

				</fieldset>
			</div>

			<div class="col-md-12">
				<div class="button-group">
				<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning" onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?>>
					<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
					Cancel
				</button>
				
				<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('asset','M');?> >
					<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>		
					Save					
				</button>
				
				<button id="btDelete" name="btDelete"  type="submit" value="submit" class="btn btn-danger" onclick="return (confirm('Confirm delete?'))" <?php echo tooltip_helper('Click To Delete Record'); ?> <?php echo $input['btn_delete']?> <?php echo check_acl_btn('asset','D');?> >
					<i class="menu-icon fa fa-trash white" data-toggle="tooltip" title=""></i>Delete
				</button>					
				</div>					
			</div>

			</form>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>
</div>

<script>

function add_user_row(){
	let user_html = `
	<tr class="contacts_pic_tr">
		<td>
			<input type="hidden" name="user_id[]" value="" />
			<input type="text" name="username[]" value="" class="form-control user_autocomplete" />
		</td>
		<td>
			<input type="text" name="email[]" value="" class="form-control" />
		</td>
		<td>
			<input type="text" name="phone[]" value="" class="form-control" />
		</td>
		<td>
			<input type="text" name="telegram_id[]" value="" class="form-control" />
		</td>
		<td class="text-center">
			<i class="fa fa-trash text-danger clickable" style="cursor:pointer;" onclick="delLine(this)"></i>
		</td>
	</tr>
	`;

	$('#maintenance_pic_table tbody').append(user_html);

	//initiate autocomplete
	userAutocomplete();

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
			let telegram_id = '';

			$(this).parent().parent().find('input[name="user_id\\[\\]"]').val(idx);
			$(this).parent().parent().find('input[name="username\\[\\]"]').val(username);
			$(this).parent().parent().find('input[name="email\\[\\]"]').val(email);
			$(this).parent().parent().find('input[name="phone\\[\\]"]').val(phone);
			$(this).parent().parent().find('input[name="telegram_id\\[\\]"]').val(telegram_id);

		},
	});
}

function delLine(elem){
	$(elem).parent().parent().remove();
}

let clickedButton = '';

$('.button-group button[type=submit]').on('click', function () {
	clickedButton = $(this).attr('name');
});

$('#bill_type_detail').submit(function (e) {
	e.preventDefault();

	$('.input-group-addon').parent().removeClass('has-error');
	$('.button-group button').prop('disabled', true);

	let formData = new FormData(this);
	if(clickedButton != '') formData.append(clickedButton, 'submit');

	$.ajax({
		dataType: 'json',
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		success: function (data) {
			if(data['status'] == 'ER'){
				handle_ajax_error(data);
				$('.button-group button').prop('disabled', false);
			} else {
				window.location.href=base_url+data['url'];
			}
		},
		error: function (data) {
			$('.button-group button').prop('disabled', false);
			$.gritter.add({
				title: 'ERROR',
				text: 'Something wrong has occured during saving.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
		},
		cache: false,
		contentType: false,
		processData: false
	});
});

</script>