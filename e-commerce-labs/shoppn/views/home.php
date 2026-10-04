<?php
// home.php — placeholder until Task 10 wires in real product data
require_once __DIR__ . '/layout/header.php';
?>

<main class="home-layout">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>
    <section class="home-content">
        <div class="home-banner" role="img" aria-label="Shoppn fashion, home essentials and everyday products"></div>
        <div class="hero-copy">
            <div class="cta-section">
                <a href="/e-commerce-labs/shoppn/views/all_products.php" class="cta-button">Explore products</a>
                <a href="/e-commerce-labs/shoppn/views/register.php" class="hero-secondary-link">Join Shoppn <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/layout/footer.php';
?>