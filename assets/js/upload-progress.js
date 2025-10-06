document.addEventListener('DOMContentLoaded', () => {
    // --- UI Creation Functions ---

    function createUploadOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'upload-overlay';
        overlay.style.display = 'none'; // Initially hidden
        overlay.innerHTML = `
            <div class="spinner"></div>
            <div class="progress-text">Caricamento: 0%</div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    function showNotification(message, type = 'success') {
        const banner = document.createElement('div');
        banner.className = `notification-banner ${type}`;
        banner.textContent = message;
        document.body.appendChild(banner);

        // Show the banner
        banner.style.display = 'block';

        // Hide after 5 seconds
        setTimeout(() => {
            banner.style.display = 'none';
            document.body.removeChild(banner);
        }, 5000);
    }

    // --- Main Logic ---

    // Create the overlay once and reuse it.
    const uploadOverlay = createUploadOverlay();
    const progressText = uploadOverlay.querySelector('.progress-text');

    const uploadForms = document.querySelectorAll('form[enctype="multipart/form-data"]');

    uploadForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            handleFormSubmission(form);
        });
    });

    function handleFormSubmission(form) {
        // Show the progress overlay.
        progressText.textContent = 'Caricamento: 0%';
        uploadOverlay.style.display = 'flex';

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        xhr.open('POST', form.action, true);
        // Add a custom header to identify AJAX requests on the server-side
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', function(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                progressText.textContent = `Caricamento: ${percentComplete.toFixed(0)}%`;
            }
        });

        xhr.addEventListener('load', function() {
            uploadOverlay.style.display = 'none'; // Hide overlay

            if (xhr.status >= 200 && xhr.status < 400) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        showNotification(response.message, 'success');
                        form.reset(); // Clear the form on success
                    } else {
                        showNotification(response.message || 'Si è verificato un errore.', 'error');
                    }
                } catch (e) {
                    showNotification('Errore nella risposta del server.', 'error');
                }
            } else {
                showNotification(`Errore del server: ${xhr.status}`, 'error');
            }
        });

        xhr.addEventListener('error', function() {
            uploadOverlay.style.display = 'none';
            showNotification('Errore di rete durante il caricamento.', 'error');
        });

        xhr.send(formData);
    }
});