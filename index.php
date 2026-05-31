<?php
$pageTitle = 'Home';
require_once __DIR__ . '/config/db.php';

// Fetch featured products
$featured = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = 1 AND p.is_active = 1
    ORDER BY p.created_at DESC LIMIT 8")->fetchAll();

// Fetch all products for "New Arrivals"
$newArrivals = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC LIMIT 4")->fetchAll();

// Fetch categories
$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count
    FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
    WHERE c.is_active = 1 GROUP BY c.id ORDER BY c.name")->fetchAll();

// Stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();

$currency = getUserCurrency($pdo);

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section - Cinematic -->
<section class="hero-section">
    <!-- Floating particles -->
    <div class="hero-particles" id="heroParticles"></div>

    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-7 animate-in">
                <div class="hero-tag">
                    <i class="fas fa-gem"></i> Australian Crafted Luxury
                </div>
                <h1 class="hero-title">
                    Discover Timeless<br>
                    <span class="gold-text">Brilliance & Elegance</span>
                </h1>
                <p class="hero-subtitle">
                    Ethically sourced diamonds, opals & rare gems. Every piece is handcrafted
                    with passion, precision, and an unwavering commitment to excellence.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= BASE_URL ?>/products/shop.php" class="btn-gold" style="text-decoration:none;">
                        <i class="fas fa-gem"></i> Shop Collections
                    </a>
                    <a href="<?= BASE_URL ?>/products/shop.php?category=rings" class="btn-gold-outline" style="text-decoration:none;">
                        Explore Rings
                    </a>
                </div>

                <!-- Trust indicators -->
                <div class="d-flex gap-4 mt-5 flex-wrap">
                    <div class="trust-badge animate-in delay-2">
                        <i class="fas fa-shield-halved"></i>
                        <div>
                            <strong style="font-size:0.85rem;">Certified</strong>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin:0;">GIA & IGI Certified</p>
                        </div>
                    </div>
                    <div class="trust-badge animate-in delay-3">
                        <i class="fas fa-truck-fast"></i>
                        <div>
                            <strong style="font-size:0.85rem;">Free Shipping</strong>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin:0;">On orders over $500</p>
                        </div>
                    </div>
                    <div class="trust-badge animate-in delay-4">
                        <i class="fas fa-rotate-left"></i>
                        <div>
                            <strong style="font-size:0.85rem;">30-Day Returns</strong>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin:0;">Hassle-free returns</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating product showcase -->
            <div class="col-lg-5 d-none d-lg-block animate-right delay-2">
                <div style="position:relative;padding:2rem;">
                    <?php if (!empty($featured)): ?>
                    <div style="background:var(--dark-card);backdrop-filter:blur(20px);border:1px solid var(--dark-border);border-radius:var(--radius-lg);overflow:hidden;transform:rotate(2deg);box-shadow:var(--shadow-lg);animation:heroCardFloat 4s ease-in-out infinite;">
                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($featured[0]['image']) ?>"
                             alt="Featured" style="width:100%;height:400px;object-fit:cover;">
                        <div style="padding:1.5rem;">
                            <p style="color:var(--gold);font-size:0.75rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:0.3rem;">Featured</p>
                            <h3 style="font-size:1.2rem;margin-bottom:0.3rem;"><?= sanitize($featured[0]['name']) ?></h3>
                            <p style="color:var(--gold);font-family:var(--font-heading);font-size:1.4rem;font-weight:700;"><?= formatPrice($featured[0]['price_aud']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- Decorative floating elements -->
                    <div style="position:absolute;top:0;right:0;width:80px;height:80px;background:rgba(185,151,91,0.08);backdrop-filter:blur(20px);border:1px solid rgba(185,151,91,0.15);border-radius:50%;animation:heroFloat2 5s ease-in-out infinite;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-gem" style="color:var(--gold);font-size:1.5rem;"></i>
                    </div>
                    <div style="position:absolute;bottom:20px;left:0;background:var(--dark-card);backdrop-filter:blur(20px);border:1px solid var(--dark-border);border-radius:var(--radius-md);padding:12px 20px;animation:heroFloat3 6s ease-in-out infinite;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="color:var(--warning);font-size:0.85rem;">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <span style="font-size:0.8rem;color:var(--text-secondary);">4.9/5 Rating</span>
                        </div>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin:4px 0 0;">Trusted by 10,000+ customers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes heroCardFloat { 0%,100%{transform:rotate(2deg) translateY(0);} 50%{transform:rotate(2deg) translateY(-15px);} }
@keyframes heroFloat2 { 0%,100%{transform:translateY(0) rotate(0);} 50%{transform:translateY(-20px) rotate(10deg);} }
@keyframes heroFloat3 { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-12px);} }
</style>

<!-- Scrolling Brand Marquee -->
<section style="padding:2rem 0;border-top:1px solid var(--dark-border);border-bottom:1px solid var(--dark-border);overflow:hidden;">
    <div class="marquee-track">
        <span>Diamonds</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Sapphires</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Emeralds</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Rubies</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Opals</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>18K Gold</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Platinum</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Rose Gold</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <!-- Duplicate for seamless loop -->
        <span>Diamonds</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Sapphires</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Emeralds</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Rubies</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Opals</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>18K Gold</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Platinum</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
        <span>Rose Gold</span><span><i class="fas fa-gem" style="font-size:0.7rem;"></i></span>
    </div>
</section>

<!-- Featured Collection -->
<section style="padding:6rem 0;">
    <div class="container">
        <h2 class="section-title">Curated Collection</h2>
        <div class="section-divider"></div>
        <p class="section-subtitle">Handpicked masterpieces from our finest artisans</p>

        <div class="row g-4">
            <?php foreach ($featured as $i => $product): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 animate-in delay-<?= ($i % 4) + 1 ?>">
                <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                    <div class="card-dark product-card">
                        <div class="image-wrapper">
                            <span class="category-badge"><?= sanitize($product['category_name']) ?></span>
                            <?php if ($product['is_featured']): ?>
                            <span class="featured-badge"><i class="fas fa-star me-1"></i>Featured</span>
                            <?php endif; ?>
                            <img src="<?= BASE_URL ?>/uploads/<?= sanitize($product['image']) ?>"
                                 alt="<?= sanitize($product['name']) ?>" class="product-image">
                        </div>
                        <div class="card-body">
                            <h5 class="product-name"><?= sanitize($product['name']) ?></h5>
                            <?php $rating = getProductRating($pdo, $product['id']); ?>
                            <?php if ($rating['count'] > 0): ?>
                            <div class="mb-1" style="display:flex;align-items:center;gap:6px;">
                                <?= renderStars($rating['avg'], '0.75rem') ?>
                                <span style="font-size:0.72rem;color:var(--text-muted);">(<?= $rating['count'] ?>)</span>
                            </div>
                            <?php endif; ?>
                            <p class="product-desc"><?= sanitize($product['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="product-price"><?= formatPrice($product['price_aud']) ?></span>
                                <span class="stock-info">
                                    <?php if ($product['stock'] > 10): ?>
                                        <i class="fas fa-check-circle" style="color:var(--success);"></i> In Stock
                                    <?php elseif ($product['stock'] > 0): ?>
                                        <span class="stock-low"><i class="fas fa-exclamation-circle"></i> <?= $product['stock'] ?> left</span>
                                    <?php else: ?>
                                        <span class="stock-out"><i class="fas fa-times-circle"></i> Sold Out</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>/products/shop.php" class="btn-gold-outline" style="text-decoration:none;">
                View All <?= $totalProducts ?> Products <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Shop by Category - 3D Cards -->
<section style="padding:6rem 0;background:linear-gradient(180deg, rgba(18,18,22,0.5), var(--dark-bg));">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="section-divider"></div>
        <p class="section-subtitle">Find the perfect piece for every occasion</p>

        <div class="row g-4">
            <?php foreach ($categories as $i => $cat): ?>
            <div class="col-lg-3 col-md-6 animate-in delay-<?= ($i % 4) + 1 ?>">
                <a href="<?= BASE_URL ?>/products/shop.php?category=<?= $cat['slug'] ?>" class="text-decoration-none">
                    <div class="category-card">
                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($cat['image'] ?: 'ring-diamond-solitaire.jpg') ?>"
                             alt="<?= sanitize($cat['name']) ?>">
                        <div class="overlay">
                            <div>
                                <h4><?= sanitize($cat['name']) ?></h4>
                                <p style="color:var(--text-secondary);font-size:0.85rem;margin:0;">
                                    <?= $cat['product_count'] ?> Products
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Brilliance Gems - Feature Cards -->
<section style="padding:6rem 0;">
    <div class="container">
        <h2 class="section-title">The Brilliance Promise</h2>
        <div class="section-divider"></div>
        <p class="section-subtitle">Why discerning collectors choose us</p>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6 animate-in delay-1">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-gem"></i></div>
                    <h5>Ethically Sourced</h5>
                    <p>Every gem traced from Australian mines with full transparency and ethical mining practices.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 animate-in delay-2">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                    <h5>GIA Certified</h5>
                    <p>All diamonds come with GIA or IGI certification guaranteeing quality, cut, and authenticity.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 animate-in delay-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-truck-fast"></i></div>
                    <h5>Global Delivery</h5>
                    <p>Insured shipping to 120+ countries with real-time tracking and secure packaging.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 animate-in delay-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <h5>Secure Payments</h5>
                    <p>256-bit SSL encryption with Credit Card, PayPal, and Bank Transfer support.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<?php if (!empty($newArrivals)): ?>
<section style="padding:6rem 0;background:linear-gradient(180deg, rgba(185,151,91,0.03), transparent);">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 animate-left">
                <h2 class="section-title" style="text-align:left;">New Arrivals</h2>
                <p style="color:var(--text-secondary);font-size:0.95rem;margin-top:0.5rem;">The latest additions to our collection, fresh from our master craftsmen.</p>
            </div>
            <div class="col-lg-6 text-lg-end animate-right">
                <a href="<?= BASE_URL ?>/products/shop.php" class="btn-gold-outline" style="text-decoration:none;">
                    See All New <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($newArrivals as $i => $product): ?>
            <div class="col-lg-3 col-md-6 animate-in delay-<?= $i + 1 ?>">
                <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                    <div class="card-dark product-card">
                        <div class="image-wrapper">
                            <span class="category-badge"><?= sanitize($product['category_name']) ?></span>
                            <img src="<?= BASE_URL ?>/uploads/<?= sanitize($product['image']) ?>"
                                 alt="<?= sanitize($product['name']) ?>" class="product-image">
                        </div>
                        <div class="card-body">
                            <h5 class="product-name"><?= sanitize($product['name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="product-price"><?= formatPrice($product['price_aud']) ?></span>
                                <span style="color:var(--text-muted);font-size:0.78rem;"><?= sanitize($product['material'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter - Glass -->
<section class="newsletter-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="newsletter-glass text-center animate-scale">
                    <div style="margin-bottom:0.5rem;">
                        <i class="fas fa-envelope" style="color:var(--gold);font-size:1.5rem;"></i>
                    </div>
                    <h3 style="font-family:var(--font-heading);font-size:1.6rem;margin-bottom:0.5rem;">Join the Inner Circle</h3>
                    <p style="color:var(--text-secondary);margin-bottom:1.5rem;font-size:0.9rem;">
                        Get 10% off your first order, exclusive previews, and VIP access to new collections.
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap" style="max-width:450px;margin:0 auto;">
                        <input type="email" class="form-control" placeholder="your@email.com"
                               style="background:var(--dark-input);border:1px solid var(--dark-border);color:var(--text-primary);border-radius:var(--radius-xl);padding:14px 24px;flex:1;min-width:200px;">
                        <button class="btn-gold">Subscribe</button>
                    </div>
                    <p style="color:var(--text-dim);font-size:0.75rem;margin-top:1rem;">No spam. Unsubscribe anytime.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating particles JS -->
<script>
// Generate floating particles in hero
(function() {
    const container = document.getElementById('heroParticles');
    if (!container) return;
    for (let i = 0; i < 25; i++) {
        const span = document.createElement('span');
        span.style.left = Math.random() * 100 + '%';
        span.style.animationDuration = (Math.random() * 8 + 6) + 's';
        span.style.animationDelay = (Math.random() * 10) + 's';
        span.style.width = span.style.height = (Math.random() * 3 + 1) + 'px';
        container.appendChild(span);
    }
})();

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar-bgai');
    if (navbar) {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    }
});

// Scroll-triggered animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.animate-in, .animate-left, .animate-right, .animate-scale').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
});

// 3D tilt effect on product cards
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
