$(document).ready(function () {
    init();
    updateBulkButton();
});

function init() {
    $("#username").focus();
    updateCfgFilterLabel();
}

function show_user_popup(page, parameter) {
    $('#userModalContent')
        .html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>')
        .load(base_url + page + parameter);
    $('#userModal').modal('show');
}

function selectedUsers() {
    var users = [];

    $('.user-checkbox:checked').each(function () {
        users.push($(this).val());
    });

    return users;
}

$('#check-all').change(function () {
    $('.user-checkbox').prop(
        'checked',
        $(this).is(':checked')
    );

    updateBulkButton();
});

$(document).on('change', '.user-checkbox', function () {
    $('#check-all').prop(
        'checked',
        $('.user-checkbox').length === $('.user-checkbox:checked').length
    );

    updateBulkButton();
});

$(document).on('click', '.bulk-action', function (e) {
    e.preventDefault();

    var action = $(this).data('action');

    switch (action) {
        case 'assign':
            console.log('assign pressed');
            $('#bulkEditModal').modal('show');
            break;

        case 'activate':
            console.log('activate pressed');
            if (!confirm('Activate selected users?')) return;
            bulkUpdateStatus(1);
            break;

        case 'deactivate':
            console.log('deactiate pressed');
            if (!confirm('Deactivate selected users?')) return;
            bulkUpdateStatus(0);
            break;
    }
});

$('#saveBulkEdit').click(function () {
    var users = selectedUsers();

    if (users.length == 0) {
        alert('Please select user.');
        return;
    }

    $.ajax({
        url: base_url + 'user/bulk_update',
        type: 'POST',
        dataType: 'json',
        data: {
            users: users,
            allow_admin_notification: $('#bulk_allow_admin_notification').val(),
            allow_tech_notification: $('#bulk_allow_tech_notification').val(),
            customer_support_assign: $('#bulk_customer_support_assign').val(),
            trouble_ticket_assign: $('#bulk_trouble_ticket_assign').val(),
            mb_lvl1_approver: $('#bulk_mb_lvl1_approver').val(),
            mb_lvl2_approver: $('#bulk_mb_lvl2_approver').val(),
        },
        beforeSend: function () {
            $('#saveBulkEdit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving');
        },
        success: function (data) {
            if (data['success'] == 1) {
                $('#bulkEditModal').modal('hide');
                location.reload();
            } else {
                alert('Failed to update');
            }
        },
        complete: function () {
            $('#saveBulkEdit').prop('disabled', false).html('Save');
        }
    });
});

function bulkUpdateStatus(status) {
    $.ajax({
        url: base_url + 'user/bulk_update_status',
        type: 'POST',
        dataType: 'json',
        data: {
            users: selectedUsers(),
            status: status
        },
        success: function (data) {
            if (data['success'] == 1) {
                location.reload();
            } else {
                alert('Failed to update.');
            }
        }
    });
}

function updateBulkButton() {
    var count = selectedUsers().length;

    $('#btnBulkAction').prop('disabled', count === 0).text(count > 0 ? 'Bulk Action (' + count + ')' : 'Bulk Action');
}

function ajax_filter(filter_pressed = 0) {
    if (filter_pressed == 1)
        $("#page_item_no").val(0);

    $("#user_filter").submit();
}

function ajax_clear() {
    $('#txt_search').val('');
    $('#sel_role').val('all');
    $('.cfg-attr-select').val('all');
    updateCfgFilterLabel();
    $('#page_item_no').val(0);
    ajax_filter(1);
}

function updateCfgFilterLabel() {
    var count = 0;

    $('.cfg-attr-select').each(function () {
        if ($(this).val() != 'all') count++;
    });

    var label = 'All';
    if (count >= 1) label = 'Config (' + count + ' selected)';

    $('#cfg_filter_label').text(label);
}

$(document).on('change', '.cfg-attr-select', function () {
    updateCfgFilterLabel();
});

$('#cfgFilterMenu').on('click', function (e) {
    e.stopPropagation();
});