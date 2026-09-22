console.log('--------audit_doc.js loaded--------');

$( document ).ready(function() {

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

async function download_file(file_id) {
    try {
        // 1. Call the PHP script via fetch
        const response = await fetch(base_url+'ajax/audit_download_file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=serve_file&file_id='+file_id // Pass parameters if needed
        });

        if (!response.ok) {
			$.gritter.add({
				title: 'ERROR',
				text: 'Connection issue.',
				time: '5000',
				close_icon: 'l-arrows-remove s16',
				class_name: 'info-notice',
			});
			return false;
        }

        // 2. Extract the file filename from Content-Disposition header (optional)
        const disposition = response.headers.get('content-disposition');
        let filename = 'downloaded_file.dat'; // Fallback name

        if (disposition && disposition.indexOf('attachment') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) { 
                // Remove surrounding quotes if present
                filename = matches[1].replace(/['"]/g, '');
            }
        }

        // 3. Convert the response into a binary Blob
        const blob = await response.blob();

        // 4. Create a temporary hidden link element to force download
        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = downloadUrl;
        a.download = filename;
        
        // 5. Trigger the click event and clean up memory
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(downloadUrl);
        a.remove();

		$.gritter.add({
			title: 'SUCCESS',
			text: 'File Downloaded.',
			time: '5000',
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice',
		});

    } catch (error) {
        console.error('Download failed:', error);
		$.gritter.add({
			title: 'ERROR',
			text: 'Could not download the file.',
			time: '5000',
			close_icon: 'l-arrows-remove s16',
			class_name: 'info-notice',
		});
    }
}