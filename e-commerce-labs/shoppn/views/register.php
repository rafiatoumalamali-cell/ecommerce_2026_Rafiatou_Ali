<?php
$countries = require __DIR__ . '/../core/countries.php';
include __DIR__ . '/layout/header.php';
?>

<main>
    <h2>Register</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form id="register-form" class="auth-form" action="../actions/register_action.php" method="post" enctype="multipart/form-data" novalidate>
        <div class="form-group">
            <input type="text" name="name" placeholder="Full Name" required>
            <div class="field-error" data-error-for="name"></div>
        </div>

        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
            <div class="field-error" data-error-for="email"></div>
        </div>

        <div class="form-group">
            <input type="password" name="password" id="reg-password" placeholder="Password" required>
            <span class="password-toggle" onclick="togglePassword('reg-password')">👁️</span>
            <div class="field-error" data-error-for="password"></div>
        </div>

        <div class="password-requirements">
            <small>Password must be at least 8 characters with uppercase, lowercase, number, and special character (@$!%*?&)</small>
        </div>

        <div class="form-group">
            <input type="password" name="confirm_password" id="reg-confirm-password" placeholder="Confirm Password" required>
            <span class="password-toggle" onclick="togglePassword('reg-confirm-password')">👁️</span>
            <div class="field-error" data-error-for="confirm_password"></div>
        </div>

        <div class="form-group">
            <input type="password" name="secret_keyword" id="reg-secret-keyword" placeholder="Secret Keyword (for password recovery)" required>
            <span class="password-toggle" onclick="togglePassword('reg-secret-keyword')">👁️</span>
            <div class="field-error" data-error-for="secret_keyword"></div>
        </div>

        <div class="form-group">
            <input type="password" name="confirm_secret_keyword" id="reg-confirm-secret-keyword" placeholder="Confirm Secret Keyword" required>
            <span class="password-toggle" onclick="togglePassword('reg-confirm-secret-keyword')">👁️</span>
            <div class="field-error" data-error-for="confirm_secret_keyword"></div>
        </div>

        <div class="form-group">
            <label for="profile_image">Profile Image (Optional)</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*">
        </div>

        <div class="form-group">
            <input list="country-list" name="country" id="country" placeholder="Select Country" required>
            <datalist id="country-list">
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <div class="field-error" data-error-for="country"></div>
        </div>

        <div class="form-group">
            <input type="text" name="city" placeholder="City" required>
            <div class="field-error" data-error-for="city"></div>
        </div>

        <div class="form-group">
            <input type="text" name="contact" placeholder="Contact Number" required>
            <div class="field-error" data-error-for="contact"></div>
        </div>

        <div class="form-group">
            <input type="text" name="address" placeholder="Address">
            <div class="field-error" data-error-for="address"></div>
        </div>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                toggle.textContent = '🙈';
            } else {
                input.type = 'password';
                toggle.textContent = '👁️';
            }
        }
    </script>
    <script src="/e-commerce-labs/shoppn/js/validate.js"></script>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
