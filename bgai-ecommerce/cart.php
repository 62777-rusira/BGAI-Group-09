<?php
// ============================================
// Shopping Cart Page
// ============================================
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/includes/header.php';

$cartTotals = getCartTotals();
$cartItems = $cartTotals['items'];
$currency = $cartTotals['currency'];
?>

<section class="cart-section">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo APP_URL; ?>">Home</a> <span>/</span>
            <span class="current">Shopping Cart</span>
        </div>

        <h1 style="font-family:var(--font-heading); font-size:2rem; margin-bottom:var(--space-2xl);">
            Shopping Cart <span class="text-muted" style="font-size:1rem; font-weight:400;">(<?php echo $cartTotals['item_count']; ?> item<?php echo $cartTotals['item_count'] !== 1 ? 's' : ''; ?>)</span>
        </h1>

        <?php if (count($cartItems) > 0): ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <div class="cart-header-row">
                        <span>Product</span>
                        <span>Price</span>
                        <span>Quantity</span>
                        <span>Total</span>
                        <span></span>
                    </div>
                    <?php foreach ($cartItems as $item): 
                        $lineTotal = $item['line_total'] ?? ($item['unit_price'] * $item['quantity']);
                    ?>
                        <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                            <div class="cart-item-product">
                                <div class="cart-item-image">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo APP_URL; ?>/<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="cart-item-name">
                                        <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($item['slug']); ?>"><?php echo e($item['name']); ?></a>
                                    </h3>
                                    <p class="cart-item-material" style="font-size:0.8rem; color:var(--gray-500);">
                                        <?php echo formatPrice($item['unit_price']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="price-current" style="font-size:0.95rem;">
                                <?php echo formatPrice($item['unit_price']); ?>
                            </div>
                            <div>
                                <div class="quantity-selector">
                                    <button class="quantity-btn" onclick="updateCartQty(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">−</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" readonly>
                                    <button class="quantity-btn" onclick="updateCartQty(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                </div>
                            </div>
                            <div style="font-weight:600;">
                                <?php echo formatPrice($lineTotal); ?>
                            </div>
                            <button class="cart-item-remove" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2 class="cart-summary-title">Order Summary</h2>
                    
                    <div class="cart-summary-row">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($cartTotals['subtotal'], $currency); ?></span>
                    </div>
                    
                    <?php if ($cartTotals['tax_rate'] > 0): ?>
                        <div class="cart-summary-row">
                            <span>Tax (<?php echo $cartTotals['tax_rate']; ?>%)</span>
                            <span><?php echo formatPrice($cartTotals['tax_amount'], $currency); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="cart-summary-row">
                        <span>Shipping</span>
                        <span><?php echo $cartTotals['shipping_cost'] > 0 ? formatPrice($cartTotals['shipping_cost'], $currency) : 'Free'; ?></span>
                    </div>
                    
                    <?php if ($cartTotals['subtotal'] < $cartTotals['free_shipping_threshold'] && $cartTotals['shipping_cost'] > 0): ?>
                        <div class="cart-summary-note">
                            <i class="fas fa-info-circle"></i>
                            Add <?php echo formatPrice($cartTotals['free_shipping_threshold'] - $cartTotals['subtotal'], $currency); ?> more for free shipping
                        </div>
                    <?php endif; ?>

                    <div class="cart-summary-row total">
                        <span>Total</span>
                        <span><?php echo formatPrice($cartTotals['total'], $currency); ?></span>
                    </div>

                    <a href="<?php echo APP_URL; ?>/checkout.php" class="btn btn-primary btn-block btn-lg mt-2" style="margin-top:var(--space-xl);">
                        Proceed to Checkout <i class="fas fa-arrow-right"></i>
                    </a>
                    
                    <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-outline-dark btn-block btn-sm mt-1" style="margin-top:var(--space-md);">
                        Continue Shopping
                    </a>

                    <div style="margin-top:var(--space-xl); padding-top:var(--space-lg); border-top:1px solid var(--gray-200);">
                        <div class="guarantee" style="margin-bottom:var(--space-sm);">
                            <i class="fas fa-lock"></i>
                            <span style="font-size:0.8rem;">Secure SSL encrypted checkout</span>
                        </div>
                        <div class="guarantee">
                            <i class="fas fa-undo"></i>
                            <span style="font-size:0.8rem;">30-day hassle-free returns</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-bag"></i>
                <h2>Your Cart is Empty</h2>
                <p class="text-muted">Looks like you haven't added any jewellery to your cart yet.</p>
                <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-primary" style="margin-top:var(--space-xl);">
                    Start Shopping <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
