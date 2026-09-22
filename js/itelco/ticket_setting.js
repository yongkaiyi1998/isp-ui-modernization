/* -------------------------------
   Ticket PIC Section
-------------------------------- */
$(document).on("click", "#add_new_pic", function (e) {
	var generate_html = `<tr class="text-center">
							<td>
								<input type="text" class="col-sm-12 pic_name" name="pic_name[]" required />
							</td>
							<td>
								<input type="text" class="col-sm-12" name="pic_email[]" />
							</td>
							<td>
								<input type="text" class="col-sm-12" name="pic_contact[]" />
							</td>
							<td>
								<input type="text" class="col-sm-12" name="pic_telegram[]" />
							</td>
							<td>
								<input type="hidden" class="col-sm-12" name="tt_setting_id[]" />
								<input type="hidden" class="col-sm-12" name="pic_id[]" />
								<i class="fa fa-trash red tooltip-event delete_pic" style="cursor:pointer" title="Delete"></i>
							</td>
						</tr>`;
	$('#ticket_pic > tbody').append(generate_html);
});

$(document).on('focus', '.pic_name', function () {
	$(this).autocomplete({
		autoFocus: true,
		source: user_list,
		select: function (event, ui) {
			const $row = $(this).closest('tr');
			$row.find('input[name="pic_email[]"]').val(ui.item.email);
			$row.find('input[name="pic_contact[]"]').val(ui.item.mobile_no);
			$row.find('input[name="pic_telegram[]"]').val(ui.item.telegram_id);
			$row.find('input[name="pic_id[]"]').val(ui.item.idx);
		}
	});
});

$(document).on("click", ".delete_pic", function (e) {
	var row = $(this).closest('tr');
	var picId = row.find('input[name="tt_setting_id[]"]').val() ?? '';
	var picName = row.find('input[name="tt_setting_id[]"]').data('pic_name');

	if (picId === '') {
		row.remove();
	} else {
		if (confirm('Delete this record?')) {
			$.ajax({
				dataType: "json",
				type: "post",
				data: {
					pic_id: picId,
					pic_name: picName
				},
				url: base_url + "ticket/ajax_delete_tt_pic",
				success: function (data) {
					if (data['success'] == 1) {
						alert('Deleted');
						row.remove();
					} else {
						alert('Failed to delete');
					}
				}
			});
		}
	}
});

/* -------------------------------
   Technical User Section
-------------------------------- */
$(document).on("click", "#add_new_technical", function (e) {
	var generate_html = `<tr class="text-center">
							<td>
								<input type="text" class="col-sm-12 technical_name" name="technical_name[]" required />
							</td>
							<td>
								<input type="text" class="col-sm-12" name="technical_email[]" />
							</td>
							<td>
								<input type="text" class="col-sm-12" name="technical_contact[]" />
							</td>
							<td>
								<input type="text" class="col-sm-12" name="technical_telegram[]" />
							</td>
							<td>
								<input type="hidden" class="col-sm-12" name="technical_id[]" />
								<input type="hidden" class="col-sm-12" name="technical_user_id[]" />
								<i class="fa fa-trash red tooltip-event delete_technical" style="cursor:pointer" title="Delete"></i>
							</td>
						</tr>`;
	$('#technical_user > tbody').append(generate_html);
});

$(document).on('focus', '.technical_name', function () {
	$(this).autocomplete({
		autoFocus: true,
		source: technical_user_list,
		select: function (event, ui) {
			const $row = $(this).closest('tr');
			$row.find('input[name="technical_email[]"]').val(ui.item.email);
			$row.find('input[name="technical_contact[]"]').val(ui.item.mobile_no);
			$row.find('input[name="technical_telegram[]"]').val(ui.item.telegram_id);
			$row.find('input[name="technical_user_id[]"]').val(ui.item.idx);
		}
	});
});

$(document).on("click", ".delete_technical", function (e) {
	var row = $(this).closest('tr');
	var techId = row.find('input[name="technical_id[]"]').val() ?? '';
	var user_id = row.find('input[name="technical_user_id[]"]').val() ?? '';

	if (techId === '') {
		row.remove();
	} else {
		if (confirm('Delete this record?')) {
			$.ajax({
				dataType: "json",
				type: "post",
				data: {
					pic_id: techId,
					user_id: user_id
				},
				url: base_url + "ticket/ajax_delete_technical_user",
				success: function (data) {
					if (data['success'] == 1) {
						alert('Deleted');
						row.remove();
					} else {
						alert('Failed to delete');
					}
				}
			});
		}
	}
});
