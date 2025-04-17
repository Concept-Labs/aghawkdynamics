

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData();
        let type = document.getElementById('type').value;
        let accountId = document.getElementById('account_id').value;
        let fileInput = document.getElementById('file');
        let comments = document.getElementById('comments').value;

        formData.append('type', type);
        formData.append('account_id', accountId);
        formData.append('file', fileInput.files[0]);
        formData.append('comments', comments);

        if (type === 'block') {
            let blockId = document.getElementById('block_id').value;
            formData.append('block_id', blockId);
        } else if (type === 'service_request') {
            let serviceRequestId = document.getElementById('service_request_id').value;
            formData.append('service_request_id', serviceRequestId);
        }

        // Send the request via fetch API
        fetch('_upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('response').innerText = data;
			// If the upload was successful, fade out the message after 3 seconds
			if (data.includes('successfully')) {
				setTimeout(function() {
					let responseElement = document.getElementById('response');
					responseElement.style.transition = 'opacity 1s';  // Set the transition effect
					responseElement.style.opacity = '0';  // Start fading out

					// After fade out, remove the text and reset opacity for next time
					setTimeout(function() {
						responseElement.innerText = '';
						responseElement.style.opacity = '1';  // Reset opacity for future messages
					}, 1000);
				}, 3000);
			}
			// If the response indicates success, clear the file and comments fields
			if (data.toLowerCase().includes('successfully')) {
				loadAttachments(); // Reload attachments after successful upload
				document.getElementById('file').value = '';
				document.getElementById('comments').value = '';
			}
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('response').innerText = 'There was an error uploading the file.';
        });
    });
});
