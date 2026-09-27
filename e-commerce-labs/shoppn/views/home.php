<?php
// home.php — placeholder until Task 10 wires in real product data
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/layout/sidebar.php';
?>

<main class="home-content">
    <h1>Welcome to Shoppn</h1>
    <p>Your one-stop destination for amazing products at unbeatable prices.</p>
    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">🛍️</div>
            <h3>Wide Selection</h3>
            <p>Explore thousands of products across multiple categories</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💰</div>
            <h3>Best Prices</h3>
            <p>Competitive pricing on all your favorite items</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🚚</div>
            <h3>Fast Delivery</h3>
            <p>Quick and reliable shipping to your doorstep</p>
        </div>
    </div>
    <div class="cta-section">
        <a href="register.php" class="cta-button">Get Started</a>
    </div>
</main>

<?php
require_once __DIR__ . '/layout/footer.php';
?>