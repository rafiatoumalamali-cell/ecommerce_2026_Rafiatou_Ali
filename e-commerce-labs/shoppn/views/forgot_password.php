<?php include __DIR__ . '/layout/header.php'; ?>

<main>
    <h2>Forgot Password</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <form class="auth-form" action="../actions/forgot_password_action.php" method="post">
        <div class="form-group">
            <input type="email" name="email" placeholder="Enter your email address" required>
        </div>
        <button type="submit">Continue</button>
    </form>
    <p>Remember your password? <a href="login.php">Login here</a></p>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
