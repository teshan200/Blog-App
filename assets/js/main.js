/**
 * Main JavaScript file.
 * Initialises the EasyMDE Markdown editor on any textarea with id="editor",
 * enables drag-and-drop image uploads, and handles the delete confirmation dialog.
 */

document.addEventListener('DOMContentLoaded', function () {
    // --- Initialise the EasyMDE Markdown editor ---
    var editorEl = document.getElementById('editor');
    var easyMDE  = null;

    if (editorEl) {
        easyMDE = new EasyMDE({
            element: editorEl,
            spellChecker: false,
            placeholder: 'Write your post in Markdown...',
            toolbar: [
                'bold', 'italic', 'heading', '|',
                'quote', 'unordered-list', 'ordered-list', '|',
                {
                    name: 'image',
                    action: EasyMDE.drawUploadedImage,
                    className: 'fa fa-picture-o',
                    title: 'Upload Image'
                },
                'link', '|',
                'preview', 'side-by-side', 'fullscreen', '|',
                'guide'
            ],
            tabSize: 2,
            showIcons: ['code', 'table'],
            // Image upload settings
            uploadImage: true,
            imageUploadEndpoint: 'api/upload.php',
            imageUploadFunction: function (file, onSuccess, onError) {
                var formData = new FormData();
                formData.append('image', file);

                fetch('api/upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function (response) {
                    if (!response.ok) {
                        return response.json().then(function (err) {
                            throw new Error(err.error || 'Upload failed');
                        });
                    }
                    return response.json();
                })
                .then(function (result) {
                    if (result.data && result.data.filePath) {
                        onSuccess(result.data.filePath);
                    } else if (result.data && typeof result.data === 'string') {
                        onSuccess(result.data);
                    } else {
                        onError('Unexpected response from server.');
                    }
                })
                .catch(function (err) {
                    onError(err.message || 'Upload error.');
                });
            }
        });
    }

    // --- Prevent form submission with empty EasyMDE content ---
    var postForm = document.querySelector('.form');
    if (postForm && easyMDE) {
        postForm.addEventListener('submit', function (e) {
            // The original textarea is hidden by EasyMDE, so we manually
            // check the editor's value instead of relying on 'required'.
            if (easyMDE.value().trim() === '') {
                e.preventDefault();
                alert('Content cannot be empty.');
                easyMDE.codemirror.focus();
            }
        });
    }

    // --- Delete confirmation (fallback for any dynamically-added forms) ---
    var forms = document.querySelectorAll('form[onsubmit*="confirm"]');
});
