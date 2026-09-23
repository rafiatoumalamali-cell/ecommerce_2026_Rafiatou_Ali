document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const emailField = form.querySelector('input[type="email"]');
            const passwordField = form.querySelector('input[type="password"]');

            if (emailField && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
                event.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            if (passwordField && passwordField.value.trim().length < 6) {
                event.preventDefault();
                alert('Password must be at least 6 characters long.');
            }
        });
    });
});
