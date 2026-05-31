<?php
// ============================================
// Product Detail Page
// ============================================
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/header.php';

$slug = $_GET['slug'] ?? '';
$product = getProduct($slug);

if (!$product) {
    header("Location: " . APP_URL . "/products.php");
    exit;
}

$pageTitle = $product['name'];
$images = getProductImages($product['id']);
$pricing = getProductPrice($product['id']);
$effectivePrice = $pricing ? getEffectivePrice($pricing) : $product['base_price'];
$discount = $pricing ? getDiscountPercent($pricing['price'], $pricing['sale_price']) : 0;
$relatedProducts = getRelatedProducts($product['id'], $product['category_id']);
$currency = $pricing ? $pricing['currency_code'] : $_SESSION['currency_code'] ?? DEFAULT_CURRENCY;
$taxRate = $pricing ? $pricing['tax_rate'] : 0;
?>

<!-- Breadcrumbs -->
<section style="padding-top:var(--space-lg);">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo APP_URL; ?>">Home</a> <span>/</span>
            <a href="<?php echo APP_URL; ?>/products.php">Shop</a> <span>/</span>
            <?php if ($product['category_slug']): ?>
                <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo e($product['category_slug']); ?>"><?php echo e($product['category_name']); ?></a> <span>/</span>
            <?php endif; ?>
            <span class="current"><?php echo e($product['name']); ?></span>
        </div>
    </div>
</section>

<!-- Product Detail -->
<section class="product-detail-section">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="product-main-image" id="mainImage">
                    <?php if (!empty($images)): ?>
                        <img src="<?php echo APP_URL; ?>/<?php echo e($images[0]['image_path']); ?>" alt="<?php echo e($product['name']); ?>" id="mainImg">
                    <?php elseif ($product['primary_image']): ?>
                        <img src="<?php echo APP_URL; ?>/<?php echo e($product['primary_image']); ?>" alt="<?php echo e($product['name']); ?>" id="mainImg">
                    <?php else: ?>
                        <div class="product-placeholder" style="aspect-ratio:1;"><i class="fas fa-gem" style="font-size:5rem;"></i></div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="product-thumbnails">
                        <?php foreach ($images as $idx => $img): ?>
                            <div class="product-thumbnail <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="changeImage('<?php echo APP_URL; ?>/<?php echo e($img['image_path']); ?>', this)">
                                <img src="<?php echo APP_URL; ?>/<?php echo e($img['image_path']); ?>" alt="<?php echo e($img['alt_text']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <?php if ($product['collection_name']): ?>
                    <div class="product-info-category">
                        <a href="<?php echo APP_URL; ?>/collections.php?slug=<?php echo e($product['collection_slug']); ?>" style="color:var(--gold-muted);">
                            <?php echo e($product['collection_name']); ?> Collection
                        </a>
                    </div>
                <?php endif; ?>

                <h1 class="product-info-name"><?php echo e($product['name']); ?></h1>

                <div class="product-info-rating">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    <span>(4.5) · <?php echo e($product['sku']); ?></span>
                </div>

                <div class="product-info-price">
                    <span class="price-current"><?php echo formatPrice($effectivePrice); ?></span>
                    <?php if ($discount > 0 && $pricing): ?>
                        <span class="price-original"><?php echo formatPrice($pricing['price']); ?></span>
                        <span class="price-discount">Save <?php echo $discount; ?>%</span>
                    <?php endif; ?>
                    <?php if ($taxRate > 0): ?>
                        <p class="text-muted text-small mt-1" style="margin-top:var(--space-sm);">Incl. <?php echo $taxRate; ?>% tax</p>
                    <?php endif; ?>
                </div>

                <p class="product-info-description"><?php echo e($product['description']); ?></p>

                <!-- Specifications -->
                <div class="product-specs">
                    <div class="spec-row">
                        <span class="spec-label">Metal</span>
                        <span class="spec-value"><?php echo e($product['metal_type'] . ' ' . ($product['metal_purity'] ? '· ' . $product['metal_purity'] : '')); ?></span>
                    </div>
                    <?php if ($product['gemstone']): ?>
                        <div class="spec-row">
                            <span class="spec-label">Gemstone</span>
                            <span class="spec-value"><?php echo e($product['gemstone']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($product['weight_grams']): ?>
                        <div class="spec-row">
                            <span class="spec-label">Weight</span>
                            <span class="spec-value"><?php echo e($product['weight_grams']); ?>g</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($product['dimensions']): ?>
                        <div class="spec-row">
                            <span class="spec-label">Dimensions</span>
                            <span class="spec-value"><?php echo e($product['dimensions']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="spec-row">
                        <span class="spec-label">SKU</span>
                        <span class="spec-value"><?php echo e($product['sku']); ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Category</span>
                        <span class="spec-value">
                            <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo e($product['category_slug']); ?>" style="color:var(--gold-dark);">
                                <?php echo e($product['category_name']); ?>
                            </a>
                        </span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Availability</span>
                        <span class="spec-value" style="color: <?php echo $product['stock_quantity'] > 5 ? 'var(--success)' : ($product['stock_quantity'] > 0 ? 'var(--warning)' : 'var(--danger)'); ?>">
                            <?php echo $product['stock_quantity'] > 0 ? ($product['stock_quantity'] <= 5 ? 'Only ' . $product['stock_quantity'] . ' left in stock' : 'In Stock') : 'Out of Stock'; ?>
                        </span>
                    </div>
                </div>

                <!-- Quantity & Add to Cart -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div style="display:flex; align-items:center; gap:var(--space-md); margin-top:var(--space-xl);">
                        <label style="font-weight:600; font-size:0.9rem;">Quantity:</label>
                        <div class="quantity-selector">
                            <button class="quantity-btn" onclick="updateQty(-1)">−</button>
                            <input type="number" class="quantity-input" id="qty" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button class="quantity-btn" onclick="updateQty(1)">+</button>
                        </div>
                    </div>

                    <div class="product-action-buttons">
                        <button class="btn btn-primary btn-lg" onclick="addToCartQty(<?php echo $product['id']; ?>)" style="flex:1;">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                        <button class="btn btn-outline-dark btn-lg" onclick="buyNow(<?php echo $product['id']; ?>)">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                    </div>
                <?php else: ?>
                    <button class="btn btn-lg btn-block" disabled style="background:var(--gray-300); color:var(--white); cursor:not-allowed; margin-top:var(--space-xl);">
                        Out of Stock
                    </button>
                <?php endif; ?>

                <!-- Guarantees -->
                <div class="product-guarantees">
                    <div class="guarantee">
                        <i class="fas fa-shield-alt"></i>
                        <span>Lifetime Warranty</span>
                    </div>
                    <div class="guarantee">
                        <i class="fas fa-undo"></i>
                        <span>30-Day Returns</span>
                    </div>
                    <div class="guarantee">
                        <i class="fas fa-truck"></i>
                        <span>Free Insured Shipping</span>
                    </div>
                    <div class="guarantee">
                        <i class="fas fa-certificate"></i>
                        <span>Certificate of Authenticity</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (count($relatedProducts) > 0): ?>
<section class="section section-cream">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">You May Also Like</span>
            <h2 class="section-title">Related Products</h2>
            <div class="section-divider"></div>
        </div>
        <div class="products-grid reveal">
            <?php foreach ($relatedProducts as $rp): 
                $rpPricing = getProductPrice($rp['id']);
                $rpPrice = $rpPricing ? getEffectivePrice($rpPricing) : $rp['base_price'];
                $rpDiscount = $rpPricing ? getDiscountPercent($rpPricing['price'], $rpPricing['sale_price']) : 0;
            ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <?php if ($rp['primary_image']): ?>
                            <img src="<?php echo APP_URL; ?>/<?php echo e($rp['primary_image']); ?>" alt="<?php echo e($rp['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                        <?php endif; ?>
                        <div class="product-badges">
                            <?php if ($rpDiscount > 0): ?>
                                <span class="badge badge-sale">-<?php echo $rpDiscount; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-quick-actions">
                            <button class="quick-action-btn" onclick="addToCart(<?php echo $rp['id']; ?>)" title="Add to Cart">
                                <i class="fas fa-shopping-bag"></i>
                            </button>
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($rp['slug']); ?>" class="quick-action-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category"><?php echo e($rp['category_name'] ?? 'Jewellery'); ?></div>
                        <h3 class="product-card-title">
                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($rp['slug']); ?>">
                                <?php echo e($rp['name']); ?>
                            </a>
                        </h3>
                        <div class="product-card-price">
                            <span class="price-current"><?php echo formatPrice($rpPrice); ?></span>
                            <?php if ($rpDiscount > 0 && $rpPricing): ?>
                                <span class="price-original"><?php echo formatPrice($rpPricing['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function changeImage(src, thumb) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.product-thumbnail').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function updateQty(delta) {
    const input = document.getElementById('qty');
    let val = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}

function addToCartQty(productId) {
    const qty = parseInt(document.getElementById('qty').value);
    addToCart(productId, qty);
}

function buyNow(productId) {
    const qty = parseInt(document.getElementById('qty').value);
    addToCartThenRedirect(productId, qty, '<?php echo APP_URL; ?>/checkout.php');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
