<?php include __DIR__ . '/layout/header.php'; ?>

<?php if (!isset($_SESSION['reset_email'])): ?>
    <main>
        <h2>Reset Password</h2>
        <p class="flash-error">Please start the password reset process from the <a href="forgot_password.php">Forgot Password</a> page.</p>
    </main>
<?php else: ?>
<main>
    <h2>Reset Password</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <form class="auth-form" action="../actions/reset_password_action.php" method="post">
        <div class="form-group">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <input type="password" name="secret_keyword" id="reset-secret-keyword" placeholder="Enter your secret keyword" required>
            <span class="password-toggle" onclick="togglePassword('reset-secret-keyword')">👁️</span>
        </div>
        <div class="form-group">
            <input type="password" name="password" id="reset-password" placeholder="New Password" required>
            <span class="password-toggle" onclick="togglePassword('reset-password')">👁️</span>
        </div>
        <div class="password-requirements">
            <small>Password must be at least 8 characters with uppercase, lowercase, number, and special character (@$!%*?&)</small>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" id="reset-confirm-password" placeholder="Confirm New Password" required>
            <span class="password-toggle" onclick="togglePassword('reset-confirm-password')">👁️</span>
        </div>
        <button type="submit">Reset Password</button>
    </form>
    <p>Remember your password? <a href="login.php">Login here</a></p>
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

        // Validate password confirmation before form submission
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const password = document.getElementById('reset-password').value;
            const confirmPassword = document.getElementById('reset-confirm-password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</main>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
