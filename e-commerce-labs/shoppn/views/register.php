<?php include __DIR__ . '/layout/header.php'; ?>

<main>
    <h2>Register</h2>
    <form action="../actions/register_action.php" method="post">
        <input type="text" name="name" placeholder="Full Name"><br>
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <button type="submit">Register</button>
    </form>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
