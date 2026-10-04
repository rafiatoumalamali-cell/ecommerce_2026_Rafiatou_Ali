<?php include __DIR__ . '/layout/header.php'; ?>

<main>
    <h2>Login</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <form class="auth-form" action="../actions/login_action.php" method="post">
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_COOKIE['remember_me_email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <input type="password" name="password" id="login-password" placeholder="Password" required>
            <span class="password-toggle" onclick="togglePassword('login-password')">👁️</span>
        </div>
        <div class="form-group remember-me">
            <label>
                <input type="checkbox" name="remember_me" value="1" <?php echo isset($_COOKIE['remember_me_email']) ? 'checked' : ''; ?>>
                Remember me
            </label>
        </div>
        <button type="submit">Login</button>
    </form>
    <p class="forgot-password"><a href="forgot_password.php">Forgot Password?</a></p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
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
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
