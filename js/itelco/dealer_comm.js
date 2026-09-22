$(document).on('blur change', '.category-master .master-field', function () {

    if ($(this).is(':disabled') || $(this).is('[readonly]')) return;

    var field = $(this).data('field');
    var table = $(this).closest('table');
    var value;

    if ($(this).is(':checkbox')) {
        value = $(this).prop('checked');
    } else {
        value = $(this).val();
    }
        
    if(value == '') return;

    table.find('tbody tr').not('.category-master').each(function () {

        var input = $(this).find('[name^="' + field + '["]')
                        .not('input[readonly], input:disabled, select:disabled, textarea[readonly]');

        if (input.length) {
            if (input.is(':checkbox')) {
                input.prop('checked', value);
            } else {
                input.val(value);
            }
        }

    });

});

