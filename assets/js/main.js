document.addEventListener('DOMContentLoaded', function() {
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn');

    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Auto-hide flash messages after 5 seconds
    const messages = document.querySelectorAll('.message');

    if (messages) {
        messages.forEach(message => {
            setTimeout(function() {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 600);
            }, 5000);
        });
    }

    // Preview image before upload
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    imagePreview.src = this.result;
                    imagePreview.style.display = 'block';
                });
                reader.readAsDataURL(file);
            }
        });
    }
});
