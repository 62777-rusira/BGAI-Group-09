<?php
// ============================================
// Checkout Page
// ============================================

require_once __DIR__ . '/config/app.php';

$pageTitle = 'Checkout';

// Handle POST (order placement)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in, if not redirect to login
    if (!isLoggedIn()) {
        setFlash('error', 'Please sign in to place your order.');
        redirect(APP_URL . '/login.php?redirect=/checkout.php');
        exit;
    }

    $cartTotals = getCartTotals();
    $cartItems = $cartTotals['items'];
    $currency = $cartTotals['currency'];

    if (count($cartItems) === 0) {
        redirect(APP_URL . '/cart.php');
        exit;
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        setFlash('error', 'Invalid request. Please try again.');
        redirect(APP_URL . '/checkout.php');
        exit;
    }

    $db = db();
    $orderNumber = 'BGAI-' . date('Y') . '-' . str_pad($db->query("SELECT COUNT(*) + 1 FROM orders WHERE YEAR(created_at) = YEAR(NOW())")->fetchColumn(), 4, '0', STR_PAD_LEFT);

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO orders (order_number, user_id, first_name, last_name, email, phone,
            shipping_address_line1, shipping_address_line2, shipping_city, shipping_state, shipping_postal_code, shipping_country, shipping_country_code,
            billing_address_line1, billing_address_line2, billing_city, billing_state, billing_postal_code, billing_country,
            currency_code, subtotal, tax_amount, shipping_cost, total_amount, payment_method, payment_status, order_status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $paymentMethod = $_POST['payment_method'] ?? 'Credit Card';
        $sameAsBilling = isset($_POST['same_as_billing']);

        $stmt->execute([
            $orderNumber,
            $_SESSION['user_id'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'] ?? '',
            $_POST['ship_address1'],
            $_POST['ship_address2'] ?? '',
            $_POST['ship_city'],
            $_POST['ship_state'],
            $_POST['ship_postal'],
            $_POST['ship_country'],
            $_SESSION['country_code'],
            $sameAsBilling ? $_POST['ship_address1'] : ($_POST['bill_address1'] ?? ''),
            $sameAsBilling ? $_POST['ship_address2'] ?? '' : ($_POST['bill_address2'] ?? ''),
            $sameAsBilling ? $_POST['ship_city'] : ($_POST['bill_city'] ?? ''),
            $sameAsBilling ? $_POST['ship_state'] : ($_POST['bill_state'] ?? ''),
            $sameAsBilling ? $_POST['ship_postal'] : ($_POST['bill_postal'] ?? ''),
            $sameAsBilling ? $_POST['ship_country'] : ($_POST['bill_country'] ?? ''),
            $currency,
            $cartTotals['subtotal'],
            $cartTotals['tax_amount'],
            $cartTotals['shipping_cost'],
            $cartTotals['total'],
            $paymentMethod,
            'paid',
            'confirmed',
            $_POST['notes'] ?? ''
        ]);

        $orderId = $db->lastInsertId();

        foreach ($cartItems as $item) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                '',
                $item['quantity'],
                $item['unit_price'],
                $item['line_total'] ?? ($item['unit_price'] * $item['quantity'])
            ]);
            $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
        }

        clearCart();
        $db->commit();

        redirect(APP_URL . '/order-success.php?order=' . $orderNumber, 'success', 'Order placed successfully! Order #' . $orderNumber);
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        setFlash('error', 'An error occurred while processing your order. Please try again.');
        redirect(APP_URL . '/checkout.php');
        exit;
    }
}

// ========== GET REQUEST (show checkout form) ==========
$cartTotals = getCartTotals();
$cartItems = $cartTotals['items'];
$currency = $cartTotals['currency'];
$user = isLoggedIn() ? getCurrentUser() : null;

if (count($cartItems) === 0) {
    redirect(APP_URL . '/cart.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$csrfToken = generateCSRFToken();
?>

<section class="checkout-section">
    <div class="container">
        <!-- Progress Steps -->
        <div class="checkout-progress">
            <div class="checkout-step active">
                <span class="checkout-step-number">1</span> Shipping
            </div>
            <div class="checkout-step">
                <span class="checkout-step-number">2</span> Payment
            </div>
            <div class="checkout-step">
                <span class="checkout-step-number">3</span> Confirmation
            </div>
        </div>

        <?php if (!isLoggedIn()): ?>
            <!-- Login prompt for non-logged-in users -->
            <div style="background:linear-gradient(135deg, #fef3c7, #fde68a); border:2px solid #f59e0b; border-radius:var(--border-radius-lg); padding:var(--space-xl); margin-bottom:var(--space-xl); text-align:center;">
                <h3 style="margin-bottom:var(--space-md); color:#92400e;"><i class="fas fa-lock" style="margin-right:var(--space-sm);"></i> Sign In to Checkout</h3>
                <p style="color:#78350f; margin-bottom:var(--space-lg);">Please sign in or create an account to complete your purchase. This ensures your order is securely tracked.</p>
                <div style="display:flex; gap:var(--space-md); justify-content:center; flex-wrap:wrap;">
                    <a href="<?php echo APP_URL; ?>/login.php?redirect=/checkout.php" class="btn btn-primary btn-lg" style="color:#fff; text-decoration:none;">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                    <a href="<?php echo APP_URL; ?>/register.php" class="btn btn-outline-dark btn-lg" style="text-decoration:none;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                    <a href="<?php echo APP_URL; ?>/cart.php" class="btn btn-lg" style="background:var(--gray-100); color:var(--dark); text-decoration:none;">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>

            <!-- Still show order summary below -->
            <div style="max-width:450px; margin:var(--space-xl) auto;">
                <div class="cart-summary">
                    <h2 class="cart-summary-title">Your Cart Summary</h2>
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display:flex; gap:var(--space-md); margin-bottom:var(--space-lg); padding-bottom:var(--space-lg); border-bottom:1px solid var(--gray-100);">
                            <div style="width:60px; height:60px; border-radius:var(--border-radius); overflow:hidden; flex-shrink:0; background:var(--cream);">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo APP_URL; ?>/<?php echo e($item['image']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                <?php endif; ?>
                            </div>
                            <div style="flex:1;">
                                <p style="font-weight:600; font-size:0.85rem; margin-bottom:2px;"><?php echo e(truncateText($item['name'], 40)); ?></p>
                                <p style="font-size:0.8rem; color:var(--gray-500);">Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                            <p style="font-weight:600; font-size:0.9rem;"><?php echo formatPrice($item['line_total'] ?? ($item['unit_price'] * $item['quantity'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div class="cart-summary-row total">
                        <span>Total</span>
                        <span><?php echo formatPrice($cartTotals['total'], $currency); ?></span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Full checkout form for logged-in users -->
            <form method="POST" id="checkoutForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="checkout-layout">
                    <!-- Checkout Form -->
                    <div>
                        <!-- Shipping Information -->
                        <div class="checkout-form-card">
                            <h2><i class="fas fa-truck" style="color:var(--gold); margin-right:var(--space-sm);"></i> Shipping Information</h2>
                            
                            <div class="form-row">
                                <div class="auth-form-group">
                                    <label>First Name *</label>
                                    <input type="text" name="first_name" class="form-input" value="<?php echo e($user['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="auth-form-group">
                                    <label>Last Name *</label>
                                    <input type="text" name="last_name" class="form-input" value="<?php echo e($user['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="auth-form-group">
                                    <label>Email Address *</label>
                                    <input type="email" name="email" class="form-input" value="<?php echo e($user['email'] ?? ''); ?>" required>
                                </div>
                                <div class="auth-form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" class="form-input" value="<?php echo e($user['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="auth-form-group">
                                <label>Shipping Address *</label>
                                <input type="text" name="ship_address1" class="form-input" placeholder="Street address" value="<?php echo e($user['address_line1'] ?? ''); ?>" required>
                            </div>
                            <div class="auth-form-group">
                                <label>Apartment, suite, unit (optional)</label>
                                <input type="text" name="ship_address2" class="form-input" placeholder="Apartment, suite, unit etc." value="<?php echo e($user['address_line2'] ?? ''); ?>">
                            </div>
                            <div class="form-row">
                                <div class="auth-form-group">
                                    <label>City *</label>
                                    <input type="text" name="ship_city" class="form-input" value="<?php echo e($user['city'] ?? ''); ?>" required>
                                </div>
                                <div class="auth-form-group">
                                    <label>State / Province *</label>
                                    <input type="text" name="ship_state" class="form-input" value="<?php echo e($user['state'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="auth-form-group">
                                    <label>Postal Code *</label>
                                    <input type="text" name="ship_postal" class="form-input" value="<?php echo e($user['postal_code'] ?? ''); ?>" required>
                                </div>
                                <div class="auth-form-group">
                                    <label>Country *</label>
                                    <input type="text" name="ship_country" class="form-input" value="<?php echo e($user['country'] ?? 'Australia'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Information -->
                        <div class="checkout-form-card">
                            <h2><i class="fas fa-file-invoice-dollar" style="color:var(--gold); margin-right:var(--space-sm);"></i> Billing Information</h2>
                            <label class="form-check">
                                <input type="checkbox" name="same_as_billing" id="sameAsBilling" checked>
                                Same as shipping address
                            </label>
                            
                            <div id="billingFields" style="display:none; margin-top:var(--space-lg);">
                                <div class="form-row">
                                    <div class="auth-form-group">
                                        <label>Billing Address</label>
                                        <input type="text" name="bill_address1" class="form-input" placeholder="Street address">
                                    </div>
                                    <div class="auth-form-group">
                                        <label>City</label>
                                        <input type="text" name="bill_city" class="form-input">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="auth-form-group">
                                        <label>State / Province</label>
                                        <input type="text" name="bill_state" class="form-input">
                                    </div>
                                    <div class="auth-form-group">
                                        <label>Postal Code</label>
                                        <input type="text" name="bill_postal" class="form-input">
                                    </div>
                                </div>
                                <div class="auth-form-group">
                                    <label>Country</label>
                                    <input type="text" name="bill_country" class="form-input">
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="checkout-form-card">
                            <h2><i class="fas fa-credit-card" style="color:var(--gold); margin-right:var(--space-sm);"></i> Payment Method</h2>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
                                <label class="filter-option" style="padding:var(--space-lg); border:2px solid var(--gray-200); border-radius:var(--border-radius); cursor:pointer;">
                                    <input type="radio" name="payment_method" value="Credit Card" checked style="margin-bottom:var(--space-sm);">
                                    <div><i class="fas fa-credit-card" style="font-size:1.2rem; margin-bottom:4px; display:block;"></i> <strong>Credit / Debit Card</strong></div>
                                </label>
                                <label class="filter-option" style="padding:var(--space-lg); border:2px solid var(--gray-200); border-radius:var(--border-radius); cursor:pointer;">
                                    <input type="radio" name="payment_method" value="PayPal" style="margin-bottom:var(--space-sm);">
                                    <div><i class="fab fa-paypal" style="font-size:1.2rem; margin-bottom:4px; display:block;"></i> <strong>PayPal</strong></div>
                                </label>
                            </div>

                            <div id="cardFields" style="margin-top:var(--space-xl); padding-top:var(--space-xl); border-top:1px solid var(--gray-200);">
                                <div class="auth-form-group">
                                    <label>Card Number</label>
                                    <input type="text" class="form-input" placeholder="4242 4242 4242 4242" maxlength="19">
                                </div>
                                <div class="form-row">
                                    <div class="auth-form-group">
                                        <label>Expiry Date</label>
                                        <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                                    </div>
                                    <div class="auth-form-group">
                                        <label>CVV</label>
                                        <input type="text" class="form-input" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                                <div class="auth-form-group">
                                    <label>Cardholder Name</label>
                                    <input type="text" class="form-input" placeholder="Name on card">
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="checkout-form-card">
                            <h2><i class="fas fa-sticky-note" style="color:var(--gold); margin-right:var(--space-sm);"></i> Order Notes (Optional)</h2>
                            <div class="auth-form-group">
                                <textarea name="notes" class="form-input" placeholder="Special instructions, gift message, etc." rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div>
                        <div class="cart-summary">
                            <h2 class="cart-summary-title">Order Summary</h2>
                            
                            <?php foreach ($cartItems as $item): ?>
                                <div style="display:flex; gap:var(--space-md); margin-bottom:var(--space-lg); padding-bottom:var(--space-lg); border-bottom:1px solid var(--gray-100);">
                                    <div style="width:60px; height:60px; border-radius:var(--border-radius); overflow:hidden; flex-shrink:0; background:var(--cream);">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo APP_URL; ?>/<?php echo e($item['image']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex:1;">
                                        <p style="font-weight:600; font-size:0.85rem; margin-bottom:2px;"><?php echo e(truncateText($item['name'], 40)); ?></p>
                                        <p style="font-size:0.8rem; color:var(--gray-500);">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <p style="font-weight:600; font-size:0.9rem;"><?php echo formatPrice($item['line_total'] ?? ($item['unit_price'] * $item['quantity'])); ?></p>
                                </div>
                            <?php endforeach; ?>

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
                                <span><?php echo $cartTotals['shipping_cost'] > 0 ? formatPrice($cartTotals['shipping_cost'], $currency) : '<span style="color:var(--success); font-weight:600;">FREE</span>'; ?></span>
                            </div>
                            <div class="cart-summary-row total">
                                <span>Total</span>
                                <span><?php echo formatPrice($cartTotals['total'], $currency); ?></span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:var(--space-xl);">
                                <i class="fas fa-lock"></i> Place Order
                            </button>

                            <div class="cart-summary-note" style="margin-top:var(--space-md);">
                                <i class="fas fa-shield-alt"></i>
                                Your payment information is encrypted and secure
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<script>
document.getElementById('sameAsBilling')?.addEventListener('change', function() {
    document.getElementById('billingFields').style.display = this.checked ? 'none' : 'block';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
