<?php
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid product.');
    redirect(BASE_URL . '/products/shop.php');
}

// Fetch product with category
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                        FROM products p
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Product not found.');
    redirect(BASE_URL . '/products/shop.php');
}

// Get product rating
$rating = getProductRating($pdo, $id);

// Fetch approved reviews with user names
$revStmt = $pdo->prepare("SELECT r.*, u.name AS user_name
                           FROM reviews r
                           JOIN users u ON r.user_id = u.id
                           WHERE r.product_id = ? AND r.is_approved = 1
                           ORDER BY r.created_at DESC");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();

// Star breakdown counts
$breakdownStmt = $pdo->prepare("SELECT rating, COUNT(*) AS cnt
                                 FROM reviews
                                 WHERE product_id = ? AND is_approved = 1
                                 GROUP BY rating");
$breakdownStmt->execute([$id]);
$breakdownRaw = $breakdownStmt->fetchAll(PDO::FETCH_KEY_PAIR);
$totalReviews = array_sum($breakdownRaw);
$breakdown = [];
for ($i = 5; $i >= 1; $i--) {
    $breakdown[$i] = $breakdownRaw[$i] ?? 0;
}

// Check if current user already reviewed
$userHasReviewed = false;
if (isLoggedIn()) {
    $checkStmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $checkStmt->execute([$id, $_SESSION['user_id']]);
    $userHasReviewed = (bool)$checkStmt->fetch();
}

// Wishlist check
$inWishlist = isLoggedIn() ? isInWishlist($pdo, $_SESSION['user_id'], $id) : false;

// Related products (same category, exclude current)
$relStmt = $pdo->prepare("SELECT p.*, c.name AS category_name
                           FROM products p
                           JOIN categories c ON p.category_id = c.id
                           WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
                           ORDER BY RAND() LIMIT 4");
$relStmt->execute([$product['category_id'], $id]);
$related = $relStmt->fetchAll();

$pageTitle = $product['name'];
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Product Detail Page Styles ── */
.pd-breadcrumb { background: transparent; padding: 0; margin: 0; }
.pd-breadcrumb .breadcrumb-item a { color: rgba(255,255,255,0.45); text-decoration: none; font-size: 0.85rem; transition: color 0.3s; }
.pd-breadcrumb .breadcrumb-item a:hover { color: var(--gold); }
.pd-breadcrumb .breadcrumb-item.active { color: var(--gold); font-size: 0.85rem; }
.pd-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.2); }

/* Image zoom container */
.pd-image-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    cursor: crosshair;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
}
.pd-image-wrap img {
    width: 100%;
    display: block;
    transition: transform 0.1s ease-out;
    transform-origin: center center;
    max-height: 600px;
    object-fit: cover;
}
.pd-image-wrap .zoom-lens {
    position: absolute;
    width: 160px;
    height: 160px;
    border: 2px solid var(--gold);
    border-radius: 50%;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
    box-shadow: 0 0 30px rgba(212,175,55,0.15);
    background-repeat: no-repeat;
    z-index: 10;
}
.pd-image-wrap:hover .zoom-lens { opacity: 1; }

/* Product info */
.pd-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0.75rem;
}
.pd-price {
    font-size: 2rem;
    color: var(--gold);
    font-weight: 700;
    letter-spacing: 0.5px;
}
.pd-stars { color: var(--gold); font-size: 0.95rem; }
.pd-stars .count { color: rgba(255,255,255,0.45); font-size: 0.85rem; margin-left: 6px; }
.pd-desc { color: rgba(255,255,255,0.6); line-height: 1.85; font-size: 0.95rem; }

/* Specs list */
.pd-specs { list-style: none; padding: 0; margin: 0; }
.pd-specs li {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    font-size: 0.9rem;
}
.pd-specs li:last-child { border-bottom: none; }
.pd-specs .spec-label { color: rgba(255,255,255,0.4); width: 110px; flex-shrink: 0; }
.pd-specs .spec-value { color: rgba(255,255,255,0.85); }
.pd-specs .spec-icon { color: var(--gold); width: 24px; margin-right: 8px; font-size: 0.8rem; }

/* Stock indicator */
.stock-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-right: 6px;
    animation: stockPulse 2s infinite;
}
.stock-dot.in-stock { background: #22c55e; box-shadow: 0 0 8px rgba(34,197,94,0.5); }
.stock-dot.out-of-stock { background: #ef4444; box-shadow: 0 0 8px rgba(239,68,68,0.5); animation: none; }
@keyframes stockPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* Quantity selector */
.qty-selector {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
    background: rgba(255,255,255,0.03);
}
.qty-selector button {
    width: 42px; height: 42px;
    background: transparent;
    border: none;
    color: var(--gold);
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.2s;
}
.qty-selector button:hover { background: rgba(212,175,55,0.1); }
.qty-selector input {
    width: 50px; height: 42px;
    background: transparent;
    border: none;
    border-left: 1px solid rgba(255,255,255,0.08);
    border-right: 1px solid rgba(255,255,255,0.08);
    text-align: center;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
}
.qty-selector input:focus { outline: none; }

/* Wishlist button */
.btn-wishlist {
    width: 48px; height: 48px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.03);
    color: rgba(255,255,255,0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-wishlist:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.08); }
.btn-wishlist.active { color: #ef4444; border-color: #ef4444; background: rgba(239,68,68,0.1); }

/* Trust badges */
.trust-row {
    display: flex;
    gap: 16px;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}
.trust-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    flex: 1;
    min-width: 140px;
}
.trust-item i { color: var(--gold); font-size: 1.1rem; }
.trust-item span { font-size: 0.78rem; color: rgba(255,255,255,0.5); line-height: 1.3; }

/* Tabs */
.pd-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    margin-bottom: 2rem;
}
.pd-tab {
    padding: 14px 28px;
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.4);
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    transition: color 0.3s;
    letter-spacing: 0.3px;
}
.pd-tab:hover { color: rgba(255,255,255,0.7); }
.pd-tab.active { color: var(--gold); }
.pd-tab.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0; right: 0;
    height: 2px;
    background: var(--gold);
    border-radius: 2px 2px 0 0;
}
.pd-tab-content { display: none; }
.pd-tab-content.active { display: block; animation: fadeTabIn 0.4s ease; }
@keyframes fadeTabIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

/* Reviews */
.review-summary {
    display: flex;
    gap: 3rem;
    align-items: flex-start;
    flex-wrap: wrap;
}
.review-avg {
    text-align: center;
    min-width: 140px;
}
.review-avg .big-num {
    font-size: 3.5rem;
    font-weight: 700;
    color: var(--gold);
    line-height: 1;
}
.review-avg .avg-label { color: rgba(255,255,255,0.4); font-size: 0.82rem; margin-top: 4px; }
.review-bars { flex: 1; min-width: 250px; }
.review-bar-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    font-size: 0.82rem;
}
.review-bar-row .star-label { color: rgba(255,255,255,0.5); width: 50px; text-align: right; white-space: nowrap; }
.review-bar-track {
    flex: 1;
    height: 8px;
    background: rgba(255,255,255,0.06);
    border-radius: 4px;
    overflow: hidden;
}
.review-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--gold-dark, #b8860b), var(--gold));
    border-radius: 4px;
    transition: width 0.6s ease;
}
.review-bar-row .bar-count { color: rgba(255,255,255,0.35); width: 30px; font-size: 0.78rem; }

/* Single review card */
.review-card {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.review-card:last-child { border-bottom: none; }
.review-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap; }
.review-user {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-dark, #b8860b), var(--gold));
    display: flex; align-items: center; justify-content: center;
    color: #0a0a0a; font-weight: 700; font-size: 0.85rem;
}
.review-name { font-weight: 600; font-size: 0.9rem; }
.review-date { color: rgba(255,255,255,0.3); font-size: 0.78rem; }
.review-title { font-weight: 600; font-size: 1rem; margin-bottom: 4px; }
.review-comment { color: rgba(255,255,255,0.55); font-size: 0.9rem; line-height: 1.7; }

/* Star rating selector */
.star-select { display: inline-flex; gap: 4px; direction: rtl; }
.star-select input { display: none; }
.star-select label {
    font-size: 1.5rem;
    color: rgba(255,255,255,0.15);
    cursor: pointer;
    transition: color 0.2s, transform 0.15s;
}
.star-select label:hover,
.star-select label:hover ~ label,
.star-select input:checked ~ label {
    color: var(--gold);
    transform: scale(1.15);
}

/* Related product cards with tilt */
.related-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.3s;
    will-change: transform;
}
.related-card:hover {
    box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 30px rgba(212,175,55,0.08);
}
.related-card .rc-img {
    width: 100%;
    height: 240px;
    object-fit: cover;
    display: block;
}
.related-card .rc-body { padding: 1.25rem; }
.related-card .rc-name {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Divider */
.section-divider {
    border: none;
    height: 1px;
    background: rgba(255,255,255,0.06);
    margin: 3rem 0;
}

/* Featured badge */
.featured-pulse {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05));
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 0.75rem;
    color: var(--gold);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}
</style>

<div class="container py-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4 animate-in">
        <ol class="breadcrumb pd-breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/products/shop.php?category=<?= urlencode($product['category_slug']) ?>">
                    <?= sanitize($product['category_name']) ?>
                </a>
            </li>
            <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
        </ol>
    </nav>

    <!-- ══ Two-Column Layout ══ -->
    <div class="row g-5">

        <!-- Left: Product Image with Zoom -->
        <div class="col-lg-6 animate-in animate-left">
            <div class="pd-image-wrap" id="imageZoomWrap">
                <img src="<?= BASE_URL ?>/uploads/<?= sanitize($product['image']) ?>"
                     alt="<?= sanitize($product['name']) ?>"
                     id="productImage">
                <div class="zoom-lens" id="zoomLens"></div>
            </div>
        </div>

        <!-- Right: Product Info -->
        <div class="col-lg-6 animate-in animate-right">

            <!-- Category & Featured -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="category-badge"><?= sanitize($product['category_name']) ?></span>
                <?php if ($product['is_featured']): ?>
                    <span class="featured-pulse"><i class="fas fa-bolt"></i> Featured</span>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <h1 class="pd-title"><?= sanitize($product['name']) ?></h1>

            <!-- Rating -->
            <div class="pd-stars mb-3">
                <?= renderStars($rating['average']) ?>
                <span class="count">(<?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?>)</span>
            </div>

            <!-- Price -->
            <div class="pd-price mb-3"><?= formatPrice($product['price_aud']) ?></div>

            <!-- Description -->
            <p class="pd-desc mb-4"><?= nl2br(sanitize($product['description'] ?? '')) ?></p>

            <!-- Specs -->
            <ul class="pd-specs mb-4">
                <?php if ($product['material']): ?>
                <li>
                    <i class="fas fa-gem spec-icon"></i>
                    <span class="spec-label">Material</span>
                    <span class="spec-value"><?= sanitize($product['material']) ?></span>
                </li>
                <?php endif; ?>
                <?php if ($product['weight']): ?>
                <li>
                    <i class="fas fa-weight-hanging spec-icon"></i>
                    <span class="spec-label">Weight</span>
                    <span class="spec-value"><?= sanitize($product['weight']) ?></span>
                </li>
                <?php endif; ?>
                <li>
                    <i class="fas fa-box-open spec-icon"></i>
                    <span class="spec-label">Availability</span>
                    <span class="spec-value">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="stock-dot in-stock"></span>In Stock (<?= $product['stock'] ?> available)
                        <?php else: ?>
                            <span class="stock-dot out-of-stock"></span>Out of Stock
                        <?php endif; ?>
                    </span>
                </li>
            </ul>

            <!-- Add to Cart + Wishlist -->
            <?php if ($product['stock'] > 0): ?>
            <form action="<?= BASE_URL ?>/cart/add.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="qty-selector">
                        <button type="button" id="qtyMinus"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                        <button type="button" id="qtyPlus"><i class="fas fa-plus"></i></button>
                    </div>
                    <button type="submit" class="btn btn-gold btn-lg flex-grow-1">
                        <i class="fas fa-shopping-bag me-2"></i>Add to Cart
                    </button>
                    <?php if (isLoggedIn()): ?>
                    <button type="button" class="btn-wishlist <?= $inWishlist ? 'active' : '' ?>" id="wishlistBtn"
                            onclick="toggleWishlist(<?= $product['id'] ?>)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </form>
            <?php else: ?>
            <div class="d-flex align-items-center gap-3 mb-3">
                <button class="btn btn-secondary btn-lg flex-grow-1" disabled>
                    <i class="fas fa-times-circle me-2"></i>Out of Stock
                </button>
                <?php if (isLoggedIn()): ?>
                <button type="button" class="btn-wishlist <?= $inWishlist ? 'active' : '' ?>" id="wishlistBtn"
                        onclick="toggleWishlist(<?= $product['id'] ?>)">
                    <i class="fas fa-heart"></i>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Trust Badges -->
            <div class="trust-row">
                <div class="trust-item">
                    <i class="fas fa-truck"></i>
                    <span>Free Shipping<br>Orders $500+</span>
                </div>
                <div class="trust-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure<br>Payment</span>
                </div>
                <div class="trust-item">
                    <i class="fas fa-undo-alt"></i>
                    <span>30-Day<br>Returns</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Tabbed Section ══ -->
    <hr class="section-divider">

    <div class="animate-in delay-1">
        <div class="pd-tabs">
            <button class="pd-tab active" data-tab="tab-desc">Description</button>
            <button class="pd-tab" data-tab="tab-specs">Specifications</button>
            <button class="pd-tab" data-tab="tab-reviews">Reviews (<?= $totalReviews ?>)</button>
        </div>

        <!-- Description Tab -->
        <div class="pd-tab-content active" id="tab-desc">
            <div class="card-dark p-4">
                <div class="pd-desc" style="font-size:0.95rem;">
                    <?= nl2br(sanitize($product['description'] ?? 'No description available.')) ?>
                </div>
            </div>
        </div>

        <!-- Specifications Tab -->
        <div class="pd-tab-content" id="tab-specs">
            <div class="card-dark p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <ul class="pd-specs">
                            <li>
                                <i class="fas fa-tag spec-icon"></i>
                                <span class="spec-label">Category</span>
                                <span class="spec-value"><?= sanitize($product['category_name']) ?></span>
                            </li>
                            <?php if ($product['material']): ?>
                            <li>
                                <i class="fas fa-gem spec-icon"></i>
                                <span class="spec-label">Material</span>
                                <span class="spec-value"><?= sanitize($product['material']) ?></span>
                            </li>
                            <?php endif; ?>
                            <?php if ($product['weight']): ?>
                            <li>
                                <i class="fas fa-weight-hanging spec-icon"></i>
                                <span class="spec-label">Weight</span>
                                <span class="spec-value"><?= sanitize($product['weight']) ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="pd-specs">
                            <li>
                                <i class="fas fa-box-open spec-icon"></i>
                                <span class="spec-label">Stock</span>
                                <span class="spec-value"><?= $product['stock'] ?> units</span>
                            </li>
                            <li>
                                <i class="fas fa-fingerprint spec-icon"></i>
                                <span class="spec-label">SKU</span>
                                <span class="spec-value">BGAI-<?= str_pad($product['id'], 5, '0', STR_PAD_LEFT) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div class="pd-tab-content" id="tab-reviews">

            <!-- Rating Summary -->
            <div class="card-dark p-4 mb-4">
                <div class="review-summary">
                    <div class="review-avg">
                        <div class="big-num"><?= number_format($rating['average'], 1) ?></div>
                        <div class="pd-stars mb-1"><?= renderStars($rating['average']) ?></div>
                        <div class="avg-label"><?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?></div>
                    </div>
                    <div class="review-bars">
                        <?php for ($i = 5; $i >= 1; $i--):
                            $pct = $totalReviews > 0 ? round(($breakdown[$i] / $totalReviews) * 100) : 0;
                        ?>
                        <div class="review-bar-row">
                            <span class="star-label"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span>
                            <div class="review-bar-track">
                                <div class="review-bar-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="bar-count"><?= $breakdown[$i] ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Review List -->
            <?php if (!empty($reviews)): ?>
            <div class="card-dark mb-4">
                <?php foreach ($reviews as $rev): ?>
                <div class="review-card">
                    <div class="review-meta">
                        <div class="review-user"><?= strtoupper(substr($rev['user_name'], 0, 1)) ?></div>
                        <div>
                            <div class="review-name"><?= sanitize($rev['user_name']) ?></div>
                            <div class="review-date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></div>
                        </div>
                        <div class="ms-auto pd-stars"><?= renderStars($rev['rating']) ?></div>
                    </div>
                    <?php if ($rev['title']): ?>
                        <div class="review-title"><?= sanitize($rev['title']) ?></div>
                    <?php endif; ?>
                    <div class="review-comment"><?= nl2br(sanitize($rev['comment'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php elseif ($totalReviews === 0): ?>
            <div class="card-dark p-4 mb-4 text-center" style="color:rgba(255,255,255,0.35);">
                <i class="fas fa-comment-slash fa-2x mb-3" style="color:rgba(255,255,255,0.1);"></i>
                <p class="mb-0">No reviews yet. Be the first to share your experience!</p>
            </div>
            <?php endif; ?>

            <!-- Write a Review Form -->
            <?php if (isLoggedIn() && !$userHasReviewed): ?>
            <div class="card-dark p-4">
                <h5 class="mb-4" style="font-family:'Playfair Display',serif;">Write a Review</h5>
                <form action="<?= BASE_URL ?>/products/submit-review.php" method="POST" id="reviewForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="rating" id="ratingInput" value="0">

                    <div class="mb-3">
                        <label class="form-label" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Your Rating</label>
                        <div class="star-select" id="starSelect">
                            <input type="radio" name="star" id="star5" value="5"><label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" name="star" id="star4" value="4"><label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" name="star" id="star3" value="3"><label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" name="star" id="star2" value="2"><label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" name="star" id="star1" value="1"><label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reviewTitle" class="form-label" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Review Title</label>
                        <input type="text" class="form-control form-dark" id="reviewTitle" name="title" placeholder="Summarize your experience" required>
                    </div>

                    <div class="mb-3">
                        <label for="reviewComment" class="form-label" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Your Review</label>
                        <textarea class="form-control form-dark" id="reviewComment" name="comment" rows="4" placeholder="Tell others what you think about this product..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </form>
            </div>
            <?php elseif (isLoggedIn() && $userHasReviewed): ?>
            <div class="card-dark p-4 text-center" style="color:rgba(255,255,255,0.4);">
                <i class="fas fa-check-circle me-2" style="color:var(--gold);"></i>
                You have already reviewed this product.
            </div>
            <?php elseif (!isLoggedIn()): ?>
            <div class="card-dark p-4 text-center" style="color:rgba(255,255,255,0.4);">
                <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--gold);">Sign in</a> to write a review.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ Related Products ══ -->
    <?php if (!empty($related)): ?>
    <hr class="section-divider">
    <div class="animate-in delay-2">
        <h3 class="mb-4" style="font-family:'Playfair Display',serif;">You May Also Like</h3>
        <div class="row g-4">
            <?php foreach ($related as $i => $r): ?>
            <div class="col-lg-3 col-md-6">
                <div class="related-card" data-tilt>
                    <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $r['id'] ?>">
                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($r['image']) ?>"
                             alt="<?= sanitize($r['name']) ?>"
                             class="rc-img">
                    </a>
                    <div class="rc-body">
                        <span class="category-badge" style="font-size:0.7rem;"><?= sanitize($r['category_name']) ?></span>
                        <h5 class="rc-name mt-2"><?= sanitize($r['name']) ?></h5>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="product-price" style="font-size:1rem;"><?= formatPrice($r['price_aud']) ?></span>
                            <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $r['id'] ?>" class="btn btn-gold-outline btn-sm">View</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Image Zoom on Hover ──
    const wrap = document.getElementById('imageZoomWrap');
    const img = document.getElementById('productImage');
    const lens = document.getElementById('zoomLens');

    if (wrap && img && lens) {
        const zoomLevel = 2.5;

        wrap.addEventListener('mouseenter', function() {
            lens.style.backgroundImage = 'url(' + img.src + ')';
            lens.style.backgroundSize = (img.offsetWidth * zoomLevel) + 'px ' + (img.offsetHeight * zoomLevel) + 'px';
        });

        wrap.addEventListener('mousemove', function(e) {
            const rect = wrap.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;

            // Clamp lens position
            const lensW = lens.offsetWidth / 2;
            const lensH = lens.offsetHeight / 2;
            x = Math.max(lensW, Math.min(x, rect.width - lensW));
            y = Math.max(lensH, Math.min(y, rect.height - lensH));

            lens.style.left = (x - lensW) + 'px';
            lens.style.top = (y - lensH) + 'px';

            // Background position for zoomed view inside the lens
            const bgX = -(x * zoomLevel - lensW);
            const bgY = -(y * zoomLevel - lensH);
            lens.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
        });

        wrap.addEventListener('mouseleave', function() {
            lens.style.opacity = '0';
        });
    }

    // ── Quantity +/- Buttons ──
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');

    if (qtyInput && qtyMinus && qtyPlus) {
        const max = parseInt(qtyInput.getAttribute('max')) || 99;
        qtyMinus.addEventListener('click', function() {
            let v = parseInt(qtyInput.value) || 1;
            if (v > 1) qtyInput.value = v - 1;
        });
        qtyPlus.addEventListener('click', function() {
            let v = parseInt(qtyInput.value) || 1;
            if (v < max) qtyInput.value = v + 1;
        });
    }

    // ── Tab Switching ──
    document.querySelectorAll('.pd-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.pd-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.pd-tab-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab).classList.add('active');
        });
    });

    // ── Star Rating Selector ──
    const starSelect = document.getElementById('starSelect');
    const ratingInput = document.getElementById('ratingInput');
    if (starSelect && ratingInput) {
        starSelect.querySelectorAll('input').forEach(function(radio) {
            radio.addEventListener('change', function() {
                ratingInput.value = this.value;
            });
        });
    }

    // ── Review Form Validation ──
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            if (ratingInput.value === '0') {
                e.preventDefault();
                alert('Please select a star rating.');
            }
        });
    }

    // ── 3D Tilt Effect on Related Cards ──
    document.querySelectorAll('[data-tilt]').forEach(function(card) {
        card.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -8;
            const rotateY = ((x - centerX) / centerX) * 8;
            card.style.transform = 'perspective(800px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) scale3d(1.03,1.03,1.03)';
        });
        card.addEventListener('mouseleave', function() {
            card.style.transform = 'perspective(800px) rotateX(0) rotateY(0) scale3d(1,1,1)';
        });
    });
});

// ── Wishlist Toggle (outside DOMContentLoaded so it's global) ──
function toggleWishlist(productId) {
    const btn = document.getElementById('wishlistBtn');
    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('<?= BASE_URL ?>/products/wishlist-toggle.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'added') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    })
    .catch(() => {
        window.location.href = '<?= BASE_URL ?>/auth/login.php';
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
