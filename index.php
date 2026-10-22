<?php
require_once __DIR__ . '/includes/products.php';

$pageTitle  = 'Home';
$activePage = 'home';
include __DIR__ . '/includes/header.php';
?>

    <section class="hero hero-video">
        <video class="hero-video-bg" autoplay muted loop playsinline preload="metadata">
            <source src="<?= $assetPath ?>images/rs8-hero.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-inner">
            <h1><span class="highlight">Speed</span>, Quality, and Champions!</h1>
            <p class="hero-sub">RS8 Taiwan Speed Factory<br>Excite your life.</p>
            <div class="hero-ctas">
                <a href="#featured" class="btn btn-arrow">Check Our Products</a>
            </div>
        </div>
    </section>

    <section id="featured">
        <div class="container">
            <div class="section-title">
                <span class="eyebrow">Featured Products</span>
                <h2>Race-Ready Components</h2>
                <p>Top-tier parts in the RS8 line-up, engineered for uncompromising performance.</p>
            </div>

            <div class="product-grid">
                <?php foreach (rs8_products() as $product): ?>
                    <div class="product-card" id="product-<?= htmlspecialchars($product['id']) ?>" data-testid="product-<?= htmlspecialchars($product['id']) ?>">
                        <div class="img-wrap">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['alt']) ?>">
                        </div>
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">₱<?= number_format((float) $product['price'], 2) ?></div>
                        <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>

                        <div class="details-content" id="details-content-<?= htmlspecialchars($product['id']) ?>">
                            <ul class="product-features">
                                <?php foreach ($product['features'] as $feature): ?>
                                    <li><?= htmlspecialchars($feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="product-actions">
                            <button type="button" class="details-btn btn" id="details-<?= htmlspecialchars($product['id']) ?>" data-testid="details-<?= htmlspecialchars($product['id']) ?>" aria-expanded="false" aria-controls="details-content-<?= htmlspecialchars($product['id']) ?>">View Details</button>

                            <div class="cart-control">
                                <label class="sr-only" for="qty-<?= htmlspecialchars($product['id']) ?>">Quantity for <?= htmlspecialchars($product['name']) ?></label>
                                <input type="number" class="qty-input" id="qty-<?= htmlspecialchars($product['id']) ?>" data-testid="qty-<?= htmlspecialchars($product['id']) ?>" value="1" min="1" max="99" inputmode="numeric">
                                <button type="button" class="add-to-cart-btn btn" id="add-to-cart-<?= htmlspecialchars($product['id']) ?>" data-testid="add-to-cart-<?= htmlspecialchars($product['id']) ?>" data-id="<?= htmlspecialchars($product['id']) ?>">Add to Cart</button>
                            </div>

                            <p class="cart-feedback" id="cart-feedback-<?= htmlspecialchars($product['id']) ?>" data-testid="cart-feedback-<?= htmlspecialchars($product['id']) ?>" role="status" aria-live="polite"></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="feature-strip">
        <div class="container">
            <div class="feature-grid">
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 3.5"/></svg></div>
                    <div><h3>Taiwan Precision</h3><p>Every part is machined to tight tolerances by manufacturers who supply the Taiwanese racing scene.</p></div>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg></div>
                    <div><h3>Dyno-Tested</h3><p>Nothing ships until it's proven power gains and reliability on the bench, not just on paper.</p></div>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                    <div><h3>Rider-First Support</h3><p>Talk to a real person about fitment, tuning, and what setup makes sense for your build.</p></div>
                </div>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
