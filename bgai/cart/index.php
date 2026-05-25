<?php
/**
 * Shopping Cart (READ)
 * Ultra-modern glassmorphism cart with animations.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

$userId = $_SESSION['user_id'];
$currency = getUserCurrency($pdo);

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.slug, p.price_aud, p.stock, p.image, p.material
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
    ORDER BY ci.created_at DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
$totalQty = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price_aud'] * $item['quantity'];
    $totalQty += $item['quantity'];
}

// Get user's country for tax/shipping (default to Australia)
$countryStmt = $pdo->prepare("SELECT tax_rate, shipping_fee FROM countries WHERE is_active = 1 ORDER BY name LIMIT 1");
$countryStmt->execute();
$defaultCountry = $countryStmt->fetch();
$taxRate = $defaultCountry ? $defaultCountry['tax_rate'] : 10.00;
$shippingFee = $defaultCountry ? $defaultCountry['shipping_fee'] : 0.00;

$taxAmount = $subtotal * ($taxRate / 100);
$total = $subtotal + $taxAmount + $shippingFee;

$pageTitle = 'Shopping Cart';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Cart Glassmorphism ── */
.cart-glass {
    background: rgba(255,255,255,0.03);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: var(--radius-md);
    box-shadow: 0 8px 40px rgba(0,0,0,0.35);
    transition: var(--transition);
}
.cart-glass:hover {
    border-color: rgba(185,151,91,0.18);
    box-shadow: 0 12px 48px rgba(0,0,0,0.45), 0 0 0 1px rgba(185,151,91,0.08);
}

/* ── Cart Item Card ── */
.cart-item-card {
    position: relative;
    overflow: hidden;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.cart-item-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(185,151,91,0.3), transparent);
    opacity: 0;
    transition: var(--transition);
}
.cart-item-card:hover::before { opacity: 1; }

/* ── Product Image ── */
.cart-product-img-wrap {
    width: 110px;
    height: 110px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.06);
    background: rgba(0,0,0,0.3);
}
.cart-product-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.25,0.46,0.45,0.94);
}
.cart-item-card:hover .cart-product-img-wrap img {
    transform: scale(1.12);
}

/* ── Quantity Controls ── */
.qty-control-group {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 50px;
    padding: 4px;
    gap: 0;
}
.qty-control-group .qty-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.06);
    color: var(--text-primary);
    font-size: 0.75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-fast);
}
.qty-control-group .qty-btn:hover:not(:disabled) {
    background: var(--gold);
    color: #0a0a0a;
    box-shadow: 0 0 12px rgba(185,151,91,0.4);
}
.qty-control-group .qty-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}
.qty-control-group .qty-display {
    min-width: 40px;
    text-align: center;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-primary);
}

/* ── Remove Button ── */
.cart-remove-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid rgba(248,113,113,0.15);
    background: rgba(248,113,113,0.06);
    color: var(--danger);
    font-size: 0.8rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}
.cart-remove-btn:hover {
    background: rgba(248,113,113,0.18);
    border-color: rgba(248,113,113,0.35);
    transform: scale(1.1);
    box-shadow: 0 4px 16px rgba(248,113,113,0.2);
}

/* ── Summary Card ── */
.summary-glass {
    position: sticky;
    top: 100px;
    padding: 2rem;
}
.summary-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    border: none;
    margin: 1rem 0;
}
.summary-grand-total {
    font-family: var(--font-heading);
    font-size: 1.6rem;
    color: var(--gold);
    font-weight: 700;
}

/* ── Trust Badge ── */
.trust-badge-row {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
    margin-top: 2.5rem;
    padding-top: 1.5rem;
}
.trust-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.55;
    transition: var(--transition);
}
.trust-item:hover { opacity: 1; }
.trust-item i {
    font-size: 1.4rem;
    color: var(--gold);
}
.trust-item span {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--text-secondary);
}

/* ── Empty State ── */
.cart-empty-state {
    text-align: center;
    padding: 5rem 2rem;
}
.cart-empty-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(185,151,91,0.06);
    border: 2px dashed rgba(185,151,91,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    animation: emptyPulse 3s ease-in-out infinite;
}
@keyframes emptyPulse {
    0%, 100% { transform: scale(1); border-color: rgba(185,151,91,0.15); }
    50% { transform: scale(1.05); border-color: rgba(185,151,91,0.35); }
}

/* ── Line Total ── */
.line-total {
    font-family: var(--font-heading);
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
}

/* ── Stagger Animation ── */
.cart-stagger { opacity: 0; transform: translateY(20px); animation: cartFadeUp 0.5s ease forwards; }
@keyframes cartFadeUp { to { opacity: 1; transform: translateY(0); } }

/* ── Checkout Button ── */
.btn-checkout-lg {
    display: block;
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--gold-dark), var(--gold), var(--gold-light));
    color: #0a0a0a;
    font-weight: 700;
    font-size: 1.05rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}
.btn-checkout-lg::after {
    content: '';
    position: absolute;
    top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s ease;
}
.btn-checkout-lg:hover::after { left: 100%; }
.btn-checkout-lg:hover {
    box-shadow: var(--shadow-gold-lg);
    transform: translateY(-2px);
    color: #0a0a0a;
}

/* ── Material Tag ── */
.material-tag {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 50px;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(185,151,91,0.08);
    color: var(--gold-light);
    border: 1px solid rgba(185,151,91,0.12);
    margin-top: 4px;
}

/* ── Low Stock Pulse ── */
.low-stock {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.72rem;
    color: var(--warning);
    margin-top: 4px;
}
.low-stock::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--warning);
    animation: stockPulse 1.5s ease-in-out infinite;
}
@keyframes stockPulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}

/* ── Responsive ── */
@media (max-width: 767px) {
    .cart-product-img-wrap { width: 80px; height: 80px; }
    .cart-item-inner { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
    .cart-item-right { width: 100%; display: flex; justify-content: space-between; align-items: center; }
    .summary-glass { position: static; margin-top: 1rem; }
    .trust-badge-row { gap: 1.2rem; }
}
</style>

<div class="container py-5">
    <!-- Page Header -->
    <div class="page-header mb-4 animate-in">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 style="font-family:var(--font-heading); font-size:2rem; margin-bottom:0.25rem;">
                    <i class="fas fa-shopping-bag me-2" style="color:var(--gold);"></i>Shopping Cart
                </h1>
                <?php if (!empty($cartItems)): ?>
                <p style="color:var(--text-secondary); font-size:0.88rem; margin:0;">
                    <?= $totalQty ?> item<?= $totalQty !== 1 ? 's' : '' ?> in your cart
                </p>
                <?php endif; ?>
            </div>
            <?php if (!empty($cartItems)): ?>
            <a href="<?= BASE_URL ?>/products/shop.php" style="color:var(--gold); font-size:0.85rem; text-decoration:none; transition:var(--transition);">
                <i class="fas fa-arrow-left me-1"></i>Continue Shopping
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($cartItems)): ?>
        <!-- Empty Cart State -->
        <div class="cart-glass cart-empty-state animate-in">
            <div class="cart-empty-icon">
                <i class="fas fa-shopping-bag fa-3x" style="color:var(--gold); opacity:0.6;"></i>
            </div>
            <h3 style="font-family:var(--font-heading); margin-bottom:0.75rem;">Your cart is empty</h3>
            <p style="color:var(--text-secondary); max-width:380px; margin:0 auto 2rem; line-height:1.7;">
                Discover our exquisite collection of handcrafted jewellery and find something truly special.
            </p>
            <a href="<?= BASE_URL ?>/products/shop.php" class="btn-checkout-lg" style="display:inline-block; width:auto; padding:0.85rem 2.5rem;">
                <i class="fas fa-gem me-2"></i>Browse Collection
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items Column -->
            <div class="col-lg-8">
                <?php foreach ($cartItems as $idx => $item): ?>
                <div class="cart-glass cart-item-card cart-stagger" style="animation-delay: <?= $idx * 0.08 ?>s;">
                    <div class="d-flex align-items-center gap-3 cart-item-inner">
                        <!-- Product Image -->
                        <div class="cart-product-img-wrap">
                            <a href="<?= BASE_URL ?>/products/detail.php?slug=<?= sanitize($item['slug'] ?? '') ?>">
                                <img src="<?= BASE_URL ?>/uploads/<?= sanitize($item['image']) ?>"
                                     alt="<?= sanitize($item['name']) ?>">
                            </a>
                        </div>

                        <!-- Product Info -->
                        <div class="flex-grow-1" style="min-width:0;">
                            <a href="<?= BASE_URL ?>/products/detail.php?slug=<?= sanitize($item['slug'] ?? '') ?>"
                               style="text-decoration:none; color:var(--text-primary);">
                                <h6 style="margin-bottom:2px; font-weight:600; font-size:0.95rem;"><?= sanitize($item['name']) ?></h6>
                            </a>
                            <?php if (!empty($item['material'])): ?>
                                <span class="material-tag"><?= sanitize($item['material']) ?></span>
                            <?php endif; ?>
                            <div style="margin-top:6px;">
                                <span style="color:var(--gold); font-weight:600; font-size:0.9rem;">
                                    <?= formatPrice($item['price_aud']) ?>
                                </span>
                            </div>
                            <?php if ($item['stock'] <= 5 && $item['stock'] > 0): ?>
                                <span class="low-stock">Only <?= $item['stock'] ?> left</span>
                            <?php endif; ?>
                        </div>

                        <!-- Quantity + Total + Remove -->
                        <div class="cart-item-right d-flex align-items-center gap-3">
                            <!-- Quantity Controls -->
                            <div class="qty-control-group">
                                <form action="<?= BASE_URL ?>/cart/update.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>"
                                            class="qty-btn" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </form>
                                <span class="qty-display"><?= $item['quantity'] ?></span>
                                <form action="<?= BASE_URL ?>/cart/update.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>"
                                            class="qty-btn" <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Line Total -->
                            <span class="line-total"><?= formatPrice($item['price_aud'] * $item['quantity']) ?></span>

                            <!-- Remove -->
                            <form action="<?= BASE_URL ?>/cart/remove.php" method="POST"
                                  onsubmit="return confirm('Remove this item from your cart?');">
                                <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="cart-remove-btn" title="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="cart-glass summary-glass cart-stagger" style="animation-delay: 0.15s;">
                    <h4 style="font-family:var(--font-heading); margin-bottom:1.5rem; font-size:1.2rem;">
                        <i class="fas fa-receipt me-2" style="color:var(--gold); font-size:0.9rem;"></i>Order Summary
                    </h4>

                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary); font-size:0.9rem;">Subtotal</span>
                        <span style="font-weight:500;"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary); font-size:0.9rem;">Est. Tax (<?= number_format($taxRate, 1) ?>%)</span>
                        <span style="font-weight:500;"><?= formatPrice($taxAmount) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary); font-size:0.9rem;">Est. Shipping</span>
                        <span style="font-weight:500;"><?= $shippingFee > 0 ? formatPrice($shippingFee) : '<span style="color:var(--success);">Free</span>' ?></span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span style="font-size:1rem; font-weight:600;">Grand Total</span>
                        <span class="summary-grand-total"><?= formatPrice($total) ?></span>
                    </div>

                    <a href="<?= BASE_URL ?>/cart/checkout.php" class="btn-checkout-lg">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>

                    <a href="<?= BASE_URL ?>/products/shop.php"
                       class="btn-gold-outline w-100 d-block text-center py-2 mt-3"
                       style="text-decoration:none; font-size:0.88rem; border-radius:var(--radius-sm);">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>

                    <!-- Security Note -->
                    <div style="text-align:center; margin-top:1.5rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.05);">
                        <i class="fas fa-shield-alt" style="color:var(--gold); font-size:0.8rem; margin-right:4px;"></i>
                        <span style="font-size:0.72rem; color:var(--text-muted); letter-spacing:0.5px;">
                            Secure 256-bit SSL Encryption
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Badges -->
        <div class="trust-badge-row animate-in">
            <div class="trust-item">
                <i class="fas fa-shield-alt"></i>
                <span>Secure Checkout</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-undo-alt"></i>
                <span>Free Returns</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-lock"></i>
                <span>SSL Encrypted</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-gem"></i>
                <span>Certified Authentic</span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
