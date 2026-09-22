console.log('--------customer_support.js loaded--------');

var userOptions = [];
var commentFormVisible = false;

$( document ).ready(function() {
	if (userOptions.length === 0) {
		$('#reply_to_select option').each(function () {
			userOptions.push({
				value: $(this).val(),
				text: $(this).text()
			});
		});
	}
	updateReplyToSelect();
});

/**
 * Update reply-to select options
 * This function updates the options in the reply-to select dropdown based on the currently selected users.
 * It removes options that are already selected and disables the select if "all" is selected.
 * It also disables the add button if "all" is selected.
 */
function updateReplyToSelect() {
	const selectedValues = $('#selected-users .user-tag').map(function () {
		return $(this).data('value').toString();
	}).get();

	const select = $('#reply_to_select');
	const addButton = $('#add-reply-to');

	select.empty();

	userOptions.forEach(opt => {
		if (!selectedValues.includes(opt.value.toString())) {
			select.append($('<option>', {
				value: opt.value,
				text: opt.text
			}));
		}
	});

	if (selectedValues.includes('all')) {
		select.prop('disabled', true);
		addButton.prop('disabled', true);
	} else {
		select.prop('disabled', false);
		addButton.prop('disabled', false);
	}
}

/**
 * Toggle new comment form
 * This function toggles the visibility of the new comment form in the customer support detail view.
 * It also updates the button icon and label based on the form's visibility state.
 * When the form is shown, it enables the input fields; when hidden, it disables them.
 * The date and time for the new comment are set to the current moment in 'YYYY-MM-DD HH:mm:00' format.
 * The button icon toggles between a plus and minus icon, and the label text changes accordingly.
 */
$(document).on("click", "#add_job_tracking", function (e) {
	let dateTime = moment().format('YYYY-MM-DD HH:mm:00');
	$('#new_comment_date').val(dateTime);

	const $form = $('.new-comment-ui');
	const $button = $(this);
	const $icon = $button.find('i');
	const $label = $button.find('.label-text');

	$form.slideToggle(300).promise().done(function () {
		const isVisible = $form.is(':visible');
		commentFormVisible = isVisible;
		$form.find('textarea, select, input').prop('disabled', !isVisible);
		$icon.removeClass('fa-plus fa-minus').addClass(isVisible ? 'fa-minus' : 'fa-plus');
		$label.text(isVisible ? ' Hide form' : ' Click to add new record');
	});
});

/**
 * Add reply to user into list
 * This function adds a selected user from the reply-to dropdown to the list of selected users.
 * It checks if the user is already in the list to avoid duplicates.
 * If the "all" option is selected, it clears the list of selected users.
 * Each user is represented as a tag with a remove button.
 */
$(document).on('click', '#add-reply-to', function () {
	const val = $('#reply_to_select').val();
	const text = $('#reply_to_select option:selected').text();

	if (!val || $('#selected-users .user-tag[data-value="' + val + '"]').length > 0) return;

	if (val === 'all') {
		$('#selected-users').empty();
	}

	const tag = `
		<span class="user-tag" data-value="${val}">
		${text}
		<span class="remove-user">&times;</span>
		</span>`;
	$('#selected-users').append(tag);

	updateReplyToSelect();
});

/**
 * Remove user from selected users
 * This function removes a user tag from the list of selected users when the remove button is clicked.
 * It finds the closest user tag element and removes it from the DOM.
 * After removal, it updates the reply-to select options to reflect the current state.
 */
$(document).on('click', '.remove-user', function () {
	$(this).closest('.user-tag').remove();
	updateReplyToSelect();
});

/**
 * Delete job tracking record
 * This function handles the deletion of a job tracking record when the delete button is clicked.
 */
$(document).on('click', '.rec_del', function (e) {
	var recID = $(this).closest('tr').find('input[name*=\'rec_id\[\]\']').val();
	var rowIndex = $(this).closest('tr').index();
	if (recID == '') {
		if (confirm('Delete this records?'))
			$(this).closest('tr').remove();
	} else {
		if (confirm('Delete this records?')) {
			$.ajax({
				dataType: "json",
				type: "post",
				data: {
					tt_id: $('#tt_id').val(),
					rec_id: recID
				},
				url: base_url + "customer_support/ajax_delete_job_tracking?" + Math.floor((Math.random() * 10000) + 1),
				success: function (data) {
					if (data['success'] == 1) {
						alert('Deleted');
						$('#job_tracking > tbody > tr').eq(rowIndex).remove();
					} else {
						alert('Failed to delete');
					}

				}
			});
		}
	}
});

/**
 * Show and hide loading icon
 * This function shows a loading icon when an AJAX request is in progress and hides it when the request is complete.
 * It is typically used to indicate to the user that a process is ongoing, such as when submitting a form or fetching data.
 * The loading icon is expected to be an element with the ID 'loading-icon'.
 */
showLoadingIcon = function() {
	$('#loading-icon').show();
}
hideLoadingIcon = function() {
	$('#loading-icon').hide();
}


/**
 * Toggle tracking rows
 * This function toggles the visibility of additional tracking rows in the customer support detail view.
 */
$(function () {
	let expanded = false;
	$('#toggle_tracking_rows').on('click', function () {
		if (expanded) {
			$('.tracking-row-hidden').slideUp(200);
			$(this).html('<i class="fa fa-chevron-down"></i>');
		} else {
			$('.tracking-row-hidden').slideDown(200);
			$(this).html('<i class="fa fa-chevron-up"></i>');
		}
		expanded = !expanded;
	});
});