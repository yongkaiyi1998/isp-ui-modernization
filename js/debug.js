/**
 * Pop a new window with the AJAX error response.
 * 
 * @param {*} response 
 */
function _debug_ajax_error(response) {
    const newWindow = window.open('', '_blank');

    if (newWindow) {
        newWindow.document.open();
        newWindow.document.write(response.responseText);
        newWindow.document.close();
    } else {
        console.error('Popup blocked or failed to open.');
    }
}