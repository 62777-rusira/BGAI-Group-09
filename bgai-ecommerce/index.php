<?php
// ============================================
// BGAI Homepage
// ============================================
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$featuredProducts = getProducts(['featured' => true, 'limit' => 4]);
$bestsellers = getProducts(['bestseller' => true, 'limit' => 4]);
$newArrivals = getProducts(['new_arrival' => true, 'limit' => 4]);
$featuredCollections = getCollections(true);
$categories = getCategories();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg" style="background-image: url('<?php echo APP_URL; ?>/assets/images/hero-bg.jpg');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge">✦ Australian Luxury Jewellery ✦</span>
        <h1 class="hero-title">
            Where Nature's
            <span class="gold-text">Brilliance</span>
            Meets Artistry
        </h1>
        <p class="hero-subtitle">
            Discover handcrafted jewellery featuring Australia's most exquisite opals, diamonds, 
            sapphires, and South Sea pearls. Each piece tells a story of rare beauty.
        </p>
        <div class="hero-actions">
            <a href="<?php echo APP_URL; ?>/collections.php" class="btn btn-primary btn-lg">
                <i class="fas fa-gem"></i> Explore Collections
            </a>
            <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-outline btn-lg">
                <i class="fas fa-shopping-bag"></i> Shop Now
            </a>
        </div>
    </div>
    <div class="hero-scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- Featured Collections -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Our Collections</span>
            <h2 class="section-title">Curated for the Extraordinary</h2>
            <div class="section-divider"></div>
            <p class="section-description">Each collection is inspired by the natural wonders of Australia, crafted with the finest materials and unparalleled attention to detail.</p>
        </div>
        <div class="collections-grid reveal">
            <?php foreach ($featuredCollections as $col): ?>
                <a href="<?php echo APP_URL; ?>/collections.php?slug=<?php echo e($col['slug']); ?>" class="collection-card">
                    <img src="<?php echo APP_URL; ?>/assets/images/collections/<?php echo e($col['slug']); ?>.jpg" 
                         alt="<?php echo e($col['name']); ?>"
                         onerror="this.parentElement.classList.add('no-img'); this.style.display='none';">
                    <div class="collection-card-overlay">
                        <h3 class="collection-card-name"><?php echo e($col['name']); ?></h3>
                        <p class="collection-card-desc"><?php echo e(truncateText($col['description'], 120)); ?></p>
                        <span class="collection-card-link">
                            Discover Collection <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Featured Pieces</span>
            <h2 class="section-title">Handpicked for You</h2>
            <div class="section-divider"></div>
            <p class="section-description">Our most sought-after creations, each one a masterpiece of Australian craftsmanship and design.</p>
        </div>
        <div class="products-grid reveal">
            <?php foreach ($featuredProducts['products'] as $product): 
                $pricing = getProductPrice($product['id']);
                $effectivePrice = $pricing ? getEffectivePrice($pricing) : $product['base_price'];
                $discount = $pricing ? getDiscountPercent($pricing['price'], $pricing['sale_price']) : 0;
            ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <?php if ($product['primary_image']): ?>
                            <img src="<?php echo APP_URL; ?>/<?php echo e($product['primary_image']); ?>" alt="<?php echo e($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                        <?php endif; ?>
                        <div class="product-badges">
                            <?php if ($discount > 0): ?>
                                <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                            <?php if ($product['is_new_arrival']): ?>
                                <span class="badge badge-new">New</span>
                            <?php endif; ?>
                            <?php if ($product['is_bestseller']): ?>
                                <span class="badge badge-bestseller">Bestseller</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-quick-actions">
                            <button class="quick-action-btn" onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart">
                                <i class="fas fa-shopping-bag"></i>
                            </button>
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="quick-action-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category"><?php echo e($product['category_name'] ?? 'Jewellery'); ?></div>
                        <h3 class="product-card-title">
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h3>
                        <div class="product-card-material"><?php echo e($product['metal_type']); ?> · <?php echo e($product['gemstone'] ?: 'Solid Metal'); ?></div>
                        <div class="product-card-price">
                            <span class="price-current"><?php echo formatPrice($effectivePrice); ?></span>
                            <?php if ($discount > 0 && $pricing): ?>
                                <span class="price-original"><?php echo formatPrice($pricing['price']); ?></span>
                                <span class="price-discount">Save <?php echo $discount; ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3 reveal">
            <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-outline-dark">
                View All Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose BGAI -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Why BGAI</span>
            <h2 class="section-title">The BGAI Difference</h2>
            <div class="section-divider"></div>
        </div>
        <div class="features-grid reveal">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-gem"></i></div>
                <h3 class="feature-title">Ethically Sourced</h3>
                <p class="feature-desc">Every gemstone is responsibly sourced from certified Australian mines, ensuring both exceptional quality and ethical integrity.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-hand-sparkles"></i></div>
                <h3 class="feature-title">Handcrafted Excellence</h3>
                <p class="feature-desc">Our master jewellers bring decades of experience to every piece, combining traditional techniques with contemporary design.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                <h3 class="feature-title">Certified Authentic</h3>
                <p class="feature-desc">All diamonds are GIA certified, opals come with authenticity certificates, and every piece includes a certificate of valuation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-globe-americas"></i></div>
                <h3 class="feature-title">Worldwide Delivery</h3>
                <p class="feature-desc">Secure insured shipping to over 50 countries with country-specific pricing and currency support for a seamless shopping experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- Bestsellers -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Most Loved</span>
            <h2 class="section-title">Our Bestsellers</h2>
            <div class="section-divider"></div>
            <p class="section-description">The pieces our customers can't stop talking about — tried, tested, and treasured.</p>
        </div>
        <div class="products-grid reveal">
            <?php foreach ($bestsellers['products'] as $product): 
                $pricing = getProductPrice($product['id']);
                $effectivePrice = $pricing ? getEffectivePrice($pricing) : $product['base_price'];
                $discount = $pricing ? getDiscountPercent($pricing['price'], $pricing['sale_price']) : 0;
            ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <?php if ($product['primary_image']): ?>
                            <img src="<?php echo APP_URL; ?>/<?php echo e($product['primary_image']); ?>" alt="<?php echo e($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                        <?php endif; ?>
                        <div class="product-badges">
                            <?php if ($discount > 0): ?>
                                <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                            <?php if ($product['is_bestseller']): ?>
                                <span class="badge badge-bestseller">Bestseller</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-quick-actions">
                            <button class="quick-action-btn" onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart">
                                <i class="fas fa-shopping-bag"></i>
                            </button>
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="quick-action-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category"><?php echo e($product['category_name'] ?? 'Jewellery'); ?></div>
                        <h3 class="product-card-title">
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h3>
                        <div class="product-card-material"><?php echo e($product['metal_type']); ?> · <?php echo e($product['gemstone'] ?: 'Solid Metal'); ?></div>
                        <div class="product-card-price">
                            <span class="price-current"><?php echo formatPrice($effectivePrice); ?></span>
                            <?php if ($discount > 0 && $pricing): ?>
                                <span class="price-original"><?php echo formatPrice($pricing['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3 reveal">
            <a href="<?php echo APP_URL; ?>/products.php?sort=popular" class="btn btn-outline">
                Shop Bestsellers <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Shop by Category -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Categories</span>
            <h2 class="section-title">Shop by Category</h2>
            <div class="section-divider"></div>
        </div>
        <div class="features-grid reveal">
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo e($cat['slug']); ?>" class="feature-card" style="border:1px solid var(--gray-200); border-radius:var(--border-radius-lg);">
                    <div class="feature-icon">
                        <?php 
                        $icons = ['rings'=>'fa-ring', 'necklaces'=>'fa-necklace', 'bracelets'=>'fa-circle-notch', 'earrings'=>'fa-star', 'pendants'=>'fa-gem', 'brooches'=>'fa-spa'];
                        $icon = $icons[strtolower($cat['slug'])] ?? 'fa-gem';
                        ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <h3 class="feature-title"><?php echo e($cat['name']); ?></h3>
                    <p class="feature-desc"><?php echo e(truncateText($cat['description'], 100)); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Just Arrived</span>
            <h2 class="section-title">New Arrivals</h2>
            <div class="section-divider"></div>
            <p class="section-description">Fresh from our workshop — the latest additions to the BGAI collection.</p>
        </div>
        <div class="products-grid reveal">
            <?php foreach ($newArrivals['products'] as $product): 
                $pricing = getProductPrice($product['id']);
                $effectivePrice = $pricing ? getEffectivePrice($pricing) : $product['base_price'];
                $discount = $pricing ? getDiscountPercent($pricing['price'], $pricing['sale_price']) : 0;
            ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <?php if ($product['primary_image']): ?>
                            <img src="<?php echo APP_URL; ?>/<?php echo e($product['primary_image']); ?>" alt="<?php echo e($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                        <?php endif; ?>
                        <div class="product-badges">
                            <span class="badge badge-new">New</span>
                            <?php if ($discount > 0): ?>
                                <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-quick-actions">
                            <button class="quick-action-btn" onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart">
                                <i class="fas fa-shopping-bag"></i>
                            </button>
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="quick-action-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category"><?php echo e($product['category_name'] ?? 'Jewellery'); ?></div>
                        <h3 class="product-card-title">
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h3>
                        <div class="product-card-material"><?php echo e($product['metal_type']); ?> · <?php echo e($product['gemstone'] ?: 'Solid Metal'); ?></div>
                        <div class="product-card-price">
                            <span class="price-current"><?php echo formatPrice($effectivePrice); ?></span>
                            <?php if ($discount > 0 && $pricing): ?>
                                <span class="price-original"><?php echo formatPrice($pricing['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Testimonials</span>
            <h2 class="section-title">What Our Clients Say</h2>
            <div class="section-divider"></div>
        </div>
        <div class="features-grid reveal">
            <div class="feature-card" style="border:1px solid var(--gray-200);">
                <div style="color:var(--gold); margin-bottom:var(--space-md);">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="feature-desc" style="font-style:italic;">"The opal engagement ring exceeded all expectations. The play of colour is mesmerising and the craftsmanship is impeccable. My fiancée was in tears of joy."</p>
                <p style="margin-top:var(--space-md); font-weight:600; font-size:0.9rem;">— Sarah M., Sydney</p>
            </div>
            <div class="feature-card" style="border:1px solid var(--gray-200);">
                <div style="color:var(--gold); margin-bottom:var(--space-md);">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="feature-desc" style="font-style:italic;">"I ordered from the US and the international shipping was seamless. The pearl earrings arrived beautifully packaged. Absolute premium quality."</p>
                <p style="margin-top:var(--space-md); font-weight:600; font-size:0.9rem;">— James T., California</p>
            </div>
            <div class="feature-card" style="border:1px solid var(--gray-200);">
                <div style="color:var(--gold); margin-bottom:var(--space-md);">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p class="feature-desc" style="font-style:italic;">"The sapphire ring is even more beautiful in person. The customer service team was incredibly helpful with sizing. Will definitely shop again."</p>
                <p style="margin-top:var(--space-md); font-weight:600; font-size:0.9rem;">— Emma W., London</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
