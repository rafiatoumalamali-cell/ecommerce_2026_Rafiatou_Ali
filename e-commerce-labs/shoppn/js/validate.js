document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('register-form');
    if (!form) {
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^[0-9+\-\s]{7,15}$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    function showError(fieldName, message) {
        const field = form.querySelector('[name="' + fieldName + '"]');
        const errorBox = form.querySelector('[data-error-for="' + fieldName + '"]');

        if (field) {
            field.setAttribute('aria-invalid', 'true');
        }

        if (errorBox) {
            errorBox.textContent = message || '';
            errorBox.style.color = 'red';
        }
    }

    function clearError(fieldName) {
        const field = form.querySelector('[name="' + fieldName + '"]');
        const errorBox = form.querySelector('[data-error-for="' + fieldName + '"]');

        if (field) {
            field.setAttribute('aria-invalid', 'false');
        }

        if (errorBox) {
            errorBox.textContent = '';
        }
    }

    form.addEventListener('submit', function (event) {
        let isValid = true;
        const submitButton = form.querySelector('button[type="submit"]');

        const requiredFields = ['name', 'email', 'password', 'confirm_password', 'country', 'city', 'contact'];
        requiredFields.forEach(function (fieldName) {
            const field = form.querySelector('[name="' + fieldName + '"]');
            const value = field ? field.value.trim() : '';

            if (!value) {
                showError(fieldName, 'This field is required.');
                isValid = false;
            } else {
                clearError(fieldName);
            }
        });

        const emailField = form.querySelector('[name="email"]');
        if (emailField && emailField.value.trim() && !emailRegex.test(emailField.value.trim())) {
            showError('email', 'Please enter a valid email address.');
            isValid = false;
        }

        const passwordField = form.querySelector('[name="password"]');
        if (passwordField && passwordField.value && !passwordRegex.test(passwordField.value)) {
            showError('password', 'Password must be at least 8 characters with uppercase, lowercase, number, and special character (@$!%*?&).');
            isValid = false;
        }

        const confirmPasswordField = form.querySelector('[name="confirm_password"]');
        if (confirmPasswordField && confirmPasswordField.value && passwordField && confirmPasswordField.value !== passwordField.value) {
            showError('confirm_password', 'Passwords do not match.');
            isValid = false;
        }

        const phoneField = form.querySelector('[name="contact"]');
        if (phoneField && phoneField.value.trim() && !phoneRegex.test(phoneField.value.trim())) {
            showError('contact', 'Please enter a valid phone number.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            if (submitButton) {
                submitButton.disabled = false;
            }
            return false;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Registering...';
        }
    });
});
