<?php include __DIR__ . '/layout/header.php'; ?>

<main>
    <h2>Register</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <form class="auth-form" action="../actions/register_action.php" method="post">
        <div class="form-group">
            <input type="text" name="name" placeholder="Full Name" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" id="reg-password" placeholder="Password" required>
            <span class="password-toggle" onclick="togglePassword('reg-password')">👁️</span>
        </div>
        <div class="form-group">
            <input type="text" name="country" placeholder="Country" required>
        </div>
        <div class="form-group">
            <input type="text" name="city" placeholder="City" required>
        </div>
        <div class="form-group">
            <input type="text" name="contact" placeholder="Contact Number" required>
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
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
