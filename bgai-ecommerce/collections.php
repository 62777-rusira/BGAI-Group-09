<?php
// ============================================
// Collections Page
// ============================================
$pageTitle = 'Collections';
require_once __DIR__ . '/includes/header.php';

$activeSlug = $_GET['slug'] ?? '';
$allCollections = getCollections();

if ($activeSlug) {
    $activeCollection = null;
    foreach ($allCollections as $col) {
        if ($col['slug'] === $activeSlug) {
            $activeCollection = $col;
            $pageTitle = $col['name'];
            break;
        }
    }
}
?>

<?php if ($activeSlug && $activeCollection): ?>
    <!-- Single Collection Page -->
    <section class="about-hero" style="min-height:40vh;">
        <div class="hero-bg" style="background-image:url('<?php echo APP_URL; ?>/assets/images/collections/<?php echo e($activeCollection['slug']); ?>.jpg'); opacity:0.4;"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content" style="max-width:700px;">
            <span class="hero-badge">Collection</span>
            <h1 class="hero-title" style="font-size:clamp(2rem,5vw,3.5rem);"><?php echo e($activeCollection['name']); ?></h1>
            <p class="hero-subtitle"><?php echo e($activeCollection['description']); ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php
            $products = getProducts(['collection_id' => $activeCollection['id'], 'limit' => 20]);
            if (count($products['products']) > 0):
            ?>
                <div class="products-grid">
                    <?php foreach ($products['products'] as $product): 
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
            <?php else: ?>
                <div class="cart-empty">
                    <i class="fas fa-gem"></i>
                    <h2>Coming Soon</h2>
                    <p class="text-muted">New pieces are being crafted for this collection. Check back soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <!-- All Collections -->
    <section class="about-hero" style="min-height:35vh;">
        <div class="hero-overlay"></div>
        <div class="hero-content" style="max-width:700px;">
            <span class="hero-badge">Explore</span>
            <h1 class="hero-title">Our Collections</h1>
            <p class="hero-subtitle">Each collection tells a story of Australian beauty, from vivid outback opals to lustrous ocean pearls.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="collections-grid">
                <?php foreach ($allCollections as $col): ?>
                    <a href="<?php echo APP_URL; ?>/collections.php?slug=<?php echo e($col['slug']); ?>" class="collection-card">
                        <img src="<?php echo APP_URL; ?>/assets/images/collections/<?php echo e($col['slug']); ?>.jpg" 
                             alt="<?php echo e($col['name']); ?>"
                             onerror="this.parentElement.style.background='linear-gradient(135deg, #1a1a1a, #333)';">
                        <div class="collection-card-overlay">
                            <h3 class="collection-card-name"><?php echo e($col['name']); ?></h3>
                            <p class="collection-card-desc"><?php echo e(truncateText($col['description'], 120)); ?></p>
                            <span class="collection-card-link">
                                Explore Collection <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
