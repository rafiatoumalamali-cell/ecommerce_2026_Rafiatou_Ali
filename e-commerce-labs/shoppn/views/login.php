<?php include __DIR__ . '/layout/header.php'; ?>

<main>
    <h2>Login</h2>
    <form action="../actions/login_action.php" method="post">
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <button type="submit">Login</button>
    </form>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
