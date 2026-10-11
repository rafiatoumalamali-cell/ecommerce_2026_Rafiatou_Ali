document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('product-form');
    const imageInput = document.getElementById('product_image');
    if (!form || !imageInput) {
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const maxSize = 5 * 1024 * 1024;

    imageInput.addEventListener('change', function () {
        imageInput.setCustomValidity('');
        const file = imageInput.files && imageInput.files[0];
        if (!file) {
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            imageInput.setCustomValidity('Choose a JPEG, PNG, GIF, or WebP image.');
        } else if (file.size > maxSize) {
            imageInput.setCustomValidity('The image must be 5 MB or smaller.');
        }

        if (!imageInput.checkValidity()) {
            imageInput.reportValidity();
        }
    });

    form.addEventListener('submit', function (event) {
        const file = imageInput.files && imageInput.files[0];
        imageInput.setCustomValidity('');
        if (file && !allowedTypes.includes(file.type)) {
            imageInput.setCustomValidity('Choose a JPEG, PNG, GIF, or WebP image.');
        } else if (file && file.size > maxSize) {
            imageInput.setCustomValidity('The image must be 5 MB or smaller.');
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
});
