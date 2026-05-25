<?php
/**
 * Checkout (CREATE order)
 * Ultra-modern glassmorphism checkout with shipping form, payment, and order review.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

$userId = $_SESSION['user_id'];
$currency = getUserCurrency($pdo);

// Get cart items
$stmt = $pdo->prepare("
    SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.price_aud, p.stock, p.image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
    ORDER BY ci.created_at DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    setFlash('danger', 'Your cart is empty.');
    redirect(BASE_URL . '/cart/index.php');
}

// Get active countries for dropdown
$countries = $pdo->query("SELECT id, name, code, tax_rate, shipping_fee FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingName    = trim($_POST['shipping_name'] ?? '');
    $shippingEmail   = trim($_POST['shipping_email'] ?? '');
    $shippingPhone   = trim($_POST['shipping_phone'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingCity    = trim($_POST['shipping_city'] ?? '');
    $shippingCountryId = (int) ($_POST['shipping_country'] ?? 0);
    $shippingPostal  = trim($_POST['shipping_postal'] ?? '');
    $notes           = trim($_POST['notes'] ?? '');
    $paymentMethod   = trim($_POST['payment_method'] ?? '');
    $cardLast4       = '';

    // Validate
    if (empty($shippingName))    $errors[] = 'Full name is required.';
    if (empty($shippingEmail))   $errors[] = 'Email address is required.';
    if (empty($shippingPhone))   $errors[] = 'Phone number is required.';
    if (empty($shippingAddress)) $errors[] = 'Address is required.';
    if (empty($shippingCity))    $errors[] = 'City is required.';
    if ($shippingCountryId <= 0) $errors[] = 'Please select a country.';
    if (empty($shippingPostal))  $errors[] = 'Postal code is required.';
    if (empty($paymentMethod))   $errors[] = 'Please select a payment method.';

    // Capture last 4 digits for card payments (demo)
    if (in_array($paymentMethod, ['credit_card', 'debit_card'])) {
        $cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        if (strlen($cardNumber) < 13) {
            $errors[] = 'Please enter a valid card number.';
        } else {
            $cardLast4 = substr($cardNumber, -4);
        }
        if (empty($_POST['card_expiry'])) $errors[] = 'Card expiry is required.';
        if (empty($_POST['card_cvv']))    $errors[] = 'CVV is required.';
    }

    // Get country details
    $countryData = null;
    if ($shippingCountryId > 0) {
        $stmt = $pdo->prepare("SELECT name, code, currency_code, tax_rate, shipping_fee FROM countries WHERE id = ? AND is_active = 1");
        $stmt->execute([$shippingCountryId]);
        $countryData = $stmt->fetch();
        if (!$countryData) $errors[] = 'Invalid country selected.';
    }

    // Re-validate stock
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = sanitize($item['name']) . ' has insufficient stock (only ' . $item['stock'] . ' available).';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Calculate totals using selected country's rates
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price_aud'] * $item['quantity'];
            }
            $taxRate = $countryData['tax_rate'];
            $taxAmount = $subtotal * ($taxRate / 100);
            $shippingAmount = $countryData['shipping_fee'];
            $total = $subtotal + $taxAmount + $shippingAmount;

            // Generate unique order number
            $orderNumber = 'ORD-' . time() . '-' . rand(100, 999);

            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_number, subtotal, tax_amount, shipping_amount, total,
                    currency_code, status, shipping_name, shipping_address, shipping_city,
                    shipping_country, shipping_phone, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId, $orderNumber, $subtotal, $taxAmount, $shippingAmount, $total,
                $countryData['currency_code'] ?? 'AUD',
                $shippingName, $shippingAddress, $shippingCity,
                $countryData['name'], $shippingPhone, $notes ?: null
            ]);
            $orderId = $pdo->lastInsertId();

            // Create order items and reduce stock
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($cartItems as $item) {
                $lineTotal = $item['price_aud'] * $item['quantity'];
                $itemStmt->execute([
                    $orderId, $item['product_id'], $item['name'],
                    $item['quantity'], $item['price_aud'], $lineTotal
                ]);

                $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if ($stockStmt->rowCount() === 0) {
                    throw new Exception('Stock unavailable for ' . $item['name']);
                }
            }

            // Create payment record
            $paymentLabels = [
                'credit_card'   => 'Credit Card',
                'debit_card'    => 'Debit Card',
                'paypal'        => 'PayPal',
                'bank_transfer' => 'Bank Transfer',
            ];
            $paymentLabel = $paymentLabels[$paymentMethod] ?? 'Other';
            $paymentRef = 'PAY-' . time() . '-' . rand(1000, 9999);

            // Check if payments table exists and insert
            try {
                $payStmt = $pdo->prepare("
                    INSERT INTO payments (order_id, user_id, amount, currency_code, payment_method, transaction_id, status, card_last4)
                    VALUES (?, ?, ?, ?, ?, ?, 'Completed', ?)
                ");
                $payStmt->execute([
                    $orderId, $userId, $total,
                    $countryData['currency_code'] ?? 'AUD',
                    $paymentLabel, $paymentRef, $cardLast4 ?: null
                ]);
            } catch (Exception $payErr) {
                // Payment table may not have all columns; order still succeeds
            }

            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);

            $pdo->commit();

            setFlash('success', 'Order placed successfully! Your order number is ' . $orderNumber);
            redirect(BASE_URL . '/cart/order-detail.php?id=' . $orderId);

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to place order: ' . $e->getMessage();
        }
    }
}

// Calculate display totals (use first country as default for display)
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price_aud'] * $item['quantity'];
}
$defaultCountry = $countries[0] ?? ['tax_rate' => 10, 'shipping_fee' => 0];
$displayTaxRate = $defaultCountry['tax_rate'];
$displayTax = $subtotal * ($displayTaxRate / 100);
$displayShipping = $defaultCountry['shipping_fee'];
$displayTotal = $subtotal + $displayTax + $displayShipping;

$pageTitle = 'Checkout';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Checkout Glassmorphism ── */
.checkout-glass {
    background: rgba(255,255,255,0.03);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: var(--radius-md);
    box-shadow: 0 8px 40px rgba(0,0,0,0.35);
    padding: 2rem;
    transition: var(--transition);
}
.checkout-glass:hover {
    border-color: rgba(185,151,91,0.15);
}

/* ── Section Headers ── */
.section-label {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.section-label i {
    color: var(--gold);
    font-size: 0.9rem;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(185,151,91,0.08);
    border-radius: 50%;
    border: 1px solid rgba(185,151,91,0.12);
}
.section-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-dark), var(--gold));
    color: #0a0a0a;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Form Inputs ── */
.checkout-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(15,15,20,0.6);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 0.9rem;
    transition: var(--transition);
    font-family: var(--font-body);
}
.checkout-input:focus {
    outline: none;
    border-color: rgba(185,151,91,0.4);
    box-shadow: 0 0 0 3px rgba(185,151,91,0.08);
    background: rgba(15,15,20,0.8);
}
.checkout-input::placeholder { color: var(--text-muted); }
select.checkout-input {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23a09890' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}
textarea.checkout-input { resize: vertical; min-height: 70px; }

.form-label-sm {
    display: block;
    font-size: 0.78rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-secondary);
    margin-bottom: 0.4rem;
}
.form-required { color: var(--danger); }

/* ── Payment Method Radio Buttons ── */
.payment-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
.payment-option {
    position: relative;
}
.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.payment-option label {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1rem;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.85rem;
    color: var(--text-secondary);
}
.payment-option label i {
    font-size: 1.1rem;
    color: var(--text-muted);
    transition: var(--transition);
    width: 20px;
    text-align: center;
}
.payment-option input[type="radio"]:checked + label {
    border-color: rgba(185,151,91,0.4);
    background: rgba(185,151,91,0.06);
    color: var(--text-primary);
    box-shadow: 0 0 0 1px rgba(185,151,91,0.15);
}
.payment-option input[type="radio"]:checked + label i {
    color: var(--gold);
}

/* ── Card Fields ── */
.card-fields {
    display: none;
    margin-top: 1rem;
    padding: 1.25rem;
    background: rgba(0,0,0,0.2);
    border-radius: var(--radius-sm);
    border: 1px solid rgba(255,255,255,0.05);
    animation: slideDown 0.3s ease;
}
.card-fields.active { display: block; }
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Order Review Item ── */
.review-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: var(--transition-fast);
}
.review-item:last-of-type { border-bottom: none; }
.review-item:hover { background: rgba(255,255,255,0.015); margin: 0 -0.5rem; padding-left: 0.5rem; padding-right: 0.5rem; border-radius: 8px; }
.review-item-img {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.06);
}
.review-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ── Summary Sticky ── */
.checkout-summary-sticky {
    position: sticky;
    top: 100px;
}

/* ── Dividers ── */
.checkout-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    border: none;
    margin: 1.25rem 0;
}
.section-gap { margin-top: 2rem; padding-top: 1.75rem; border-top: 1px solid rgba(255,255,255,0.05); }

/* ── Grand Total ── */
.checkout-grand-total {
    font-family: var(--font-heading);
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--gold);
}

/* ── Place Order Button ── */
.btn-place-order {
    display: block;
    width: 100%;
    padding: 1.1rem;
    border: none;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--gold-dark), var(--gold), var(--gold-light));
    color: #0a0a0a;
    font-weight: 700;
    font-size: 1.05rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}
.btn-place-order::after {
    content: '';
    position: absolute;
    top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
    transition: left 0.6s ease;
}
.btn-place-order:hover::after { left: 100%; }
.btn-place-order:hover {
    box-shadow: var(--shadow-gold-lg);
    transform: translateY(-2px);
}

/* ── Error Alert ── */
.checkout-errors {
    background: rgba(248,113,113,0.06);
    border: 1px solid rgba(248,113,113,0.2);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.checkout-errors li {
    color: var(--danger);
    font-size: 0.88rem;
    margin-bottom: 0.25rem;
}
.checkout-errors li:last-child { margin-bottom: 0; }

/* ── Trust Badges ── */
.checkout-trust-row {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
    margin-top: 2.5rem;
    padding-top: 1.5rem;
}
.checkout-trust-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.5;
    transition: var(--transition);
}
.checkout-trust-item:hover { opacity: 1; }
.checkout-trust-item i { font-size: 1.3rem; color: var(--gold); }
.checkout-trust-item span { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-secondary); }

/* ── Animate In ── */
.checkout-fade { opacity: 0; transform: translateY(20px); animation: chkFadeUp 0.5s ease forwards; }
@keyframes chkFadeUp { to { opacity: 1; transform: translateY(0); } }

/* ── Responsive ── */
@media (max-width: 991px) {
    .checkout-summary-sticky { position: static; margin-top: 1.5rem; }
    .payment-options { grid-template-columns: 1fr; }
}
@media (max-width: 575px) {
    .checkout-glass { padding: 1.25rem; }
}
</style>

<div class="container py-5">
    <!-- Page Header -->
    <div class="page-header mb-4 animate-in">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/cart/index.php" style="color:var(--text-secondary); font-size:1.1rem; transition:var(--transition);"
               onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 style="font-family:var(--font-heading); font-size:2rem; margin-bottom:0.15rem;">
                    <i class="fas fa-lock me-2" style="color:var(--gold); font-size:1.4rem;"></i>Secure Checkout
                </h1>
                <p style="color:var(--text-secondary); font-size:0.82rem; margin:0;">
                    <?= count($cartItems) ?> item<?= count($cartItems) !== 1 ? 's' : '' ?> &middot; Complete your order below
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="checkout-errors checkout-fade">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                <i class="fas fa-exclamation-circle" style="color:var(--danger);"></i>
                <strong style="color:var(--danger); font-size:0.9rem;">Please fix the following errors:</strong>
            </div>
            <ul style="padding-left:1.25rem; margin:0;">
                <?php foreach ($errors as $err): ?>
                    <li><?= sanitize($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="checkoutForm">
        <div class="row g-4">
            <!-- Left: Shipping + Payment -->
            <div class="col-lg-7">

                <!-- Contact Information -->
                <div class="checkout-glass checkout-fade" style="animation-delay:0.05s;">
                    <div class="section-label">
                        <span class="section-number">1</span>
                        Contact Information
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-sm">Full Name <span class="form-required">*</span></label>
                            <input type="text" name="shipping_name" class="checkout-input" placeholder="John Smith"
                                   value="<?= sanitize($_POST['shipping_name'] ?? $_SESSION['user_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">Email Address <span class="form-required">*</span></label>
                            <input type="email" name="shipping_email" class="checkout-input" placeholder="john@example.com"
                                   value="<?= sanitize($_POST['shipping_email'] ?? $_SESSION['user_email'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-sm">Phone Number <span class="form-required">*</span></label>
                            <input type="text" name="shipping_phone" class="checkout-input" placeholder="+61 4XX XXX XXX"
                                   value="<?= sanitize($_POST['shipping_phone'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="checkout-glass checkout-fade mt-4" style="animation-delay:0.12s;">
                    <div class="section-label">
                        <span class="section-number">2</span>
                        Shipping Address
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-sm">Street Address <span class="form-required">*</span></label>
                            <textarea name="shipping_address" class="checkout-input" rows="2"
                                      placeholder="123 Main Street, Apt 4B"
                                      required><?= sanitize($_POST['shipping_address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">City <span class="form-required">*</span></label>
                            <input type="text" name="shipping_city" class="checkout-input" placeholder="Sydney"
                                   value="<?= sanitize($_POST['shipping_city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">Country <span class="form-required">*</span></label>
                            <select name="shipping_country" id="shippingCountry" class="checkout-input" required>
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?= $c['id'] ?>"
                                            data-tax="<?= $c['tax_rate'] ?>"
                                            data-shipping="<?= $c['shipping_fee'] ?>"
                                            <?= (isset($_POST['shipping_country']) && (int)$_POST['shipping_country'] === (int)$c['id']) ? 'selected' : '' ?>>
                                        <?= sanitize($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">Postal / ZIP Code <span class="form-required">*</span></label>
                            <input type="text" name="shipping_postal" class="checkout-input" placeholder="2000"
                                   value="<?= sanitize($_POST['shipping_postal'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div style="margin-top:1rem;">
                        <label class="form-label-sm">Order Notes <span style="color:var(--text-muted); text-transform:none; letter-spacing:0;">(optional)</span></label>
                        <textarea name="notes" class="checkout-input" rows="2"
                                  placeholder="Special delivery instructions, gift message..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-glass checkout-fade mt-4" style="animation-delay:0.18s;">
                    <div class="section-label">
                        <span class="section-number">3</span>
                        Payment Method
                    </div>

                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="credit_card" id="pmCreditCard"
                                   <?= ($_POST['payment_method'] ?? '') === 'credit_card' ? 'checked' : '' ?>>
                            <label for="pmCreditCard">
                                <i class="fas fa-credit-card"></i>
                                <span>Credit Card</span>
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="debit_card" id="pmDebitCard"
                                   <?= ($_POST['payment_method'] ?? '') === 'debit_card' ? 'checked' : '' ?>>
                            <label for="pmDebitCard">
                                <i class="far fa-credit-card"></i>
                                <span>Debit Card</span>
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="paypal" id="pmPaypal"
                                   <?= ($_POST['payment_method'] ?? '') === 'paypal' ? 'checked' : '' ?>>
                            <label for="pmPaypal">
                                <i class="fab fa-paypal"></i>
                                <span>PayPal</span>
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer" id="pmBank"
                                   <?= ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : '' ?>>
                            <label for="pmBank">
                                <i class="fas fa-university"></i>
                                <span>Bank Transfer</span>
                            </label>
                        </div>
                    </div>

                    <!-- Card Details (shown for Credit/Debit Card) -->
                    <div class="card-fields" id="cardFields">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-sm">Card Number</label>
                                <input type="text" name="card_number" class="checkout-input" placeholder="4242 4242 4242 4242"
                                       maxlength="19" autocomplete="cc-number"
                                       value="<?= sanitize($_POST['card_number'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label-sm">Expiry Date</label>
                                <input type="text" name="card_expiry" class="checkout-input" placeholder="MM / YY"
                                       maxlength="7" autocomplete="cc-exp"
                                       value="<?= sanitize($_POST['card_expiry'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label-sm">CVV</label>
                                <input type="text" name="card_cvv" class="checkout-input" placeholder="123"
                                       maxlength="4" autocomplete="cc-csc">
                            </div>
                        </div>
                        <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.4rem;">
                            <i class="fas fa-lock" style="font-size:0.7rem; color:var(--gold);"></i>
                            <span style="font-size:0.7rem; color:var(--text-muted);">Your card details are encrypted and secure (demo mode)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Review -->
            <div class="col-lg-5">
                <div class="checkout-glass checkout-summary-sticky checkout-fade" style="animation-delay:0.1s;">
                    <div class="section-label" style="margin-bottom:1rem;">
                        <i class="fas fa-receipt"></i>
                        Order Review
                    </div>

                    <!-- Item List -->
                    <div style="max-height:320px; overflow-y:auto; margin-bottom:0.5rem; padding-right:0.25rem;">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="review-item">
                                <div class="review-item-img">
                                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($item['image']) ?>"
                                         alt="<?= sanitize($item['name']) ?>">
                                </div>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div style="font-size:0.85rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= sanitize($item['name']) ?>
                                    </div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">
                                        Qty: <?= $item['quantity'] ?> &times; <?= formatPrice($item['price_aud']) ?>
                                    </div>
                                </div>
                                <div style="font-weight:600; font-size:0.9rem; white-space:nowrap;">
                                    <?= formatPrice($item['price_aud'] * $item['quantity']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="checkout-divider"></div>

                    <!-- Totals -->
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary); font-size:0.88rem;">Subtotal</span>
                        <span style="font-weight:500;" id="summarySubtotal"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary); font-size:0.88rem;">Tax</span>
                        <span style="font-weight:500;" id="summaryTax"><?= formatPrice($displayTax) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary); font-size:0.88rem;">Shipping</span>
                        <span style="font-weight:500;" id="summaryShipping"><?= $displayShipping > 0 ? formatPrice($displayShipping) : '<span style="color:var(--success);">Free</span>' ?></span>
                    </div>

                    <div class="checkout-divider"></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span style="font-size:1rem; font-weight:600;">Total</span>
                        <span class="checkout-grand-total" id="summaryTotal"><?= formatPrice($displayTotal) ?></span>
                    </div>

                    <button type="submit" class="btn-place-order">
                        <i class="fas fa-check-circle me-2"></i>Place Order
                    </button>

                    <a href="<?= BASE_URL ?>/cart/index.php"
                       class="btn-gold-outline w-100 d-block text-center py-2 mt-3"
                       style="text-decoration:none; font-size:0.85rem; border-radius:var(--radius-sm);">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>

                    <!-- Security -->
                    <div style="text-align:center; margin-top:1.5rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.05);">
                        <i class="fas fa-shield-alt" style="color:var(--gold); font-size:0.75rem; margin-right:4px;"></i>
                        <span style="font-size:0.7rem; color:var(--text-muted); letter-spacing:0.5px;">
                            256-bit SSL &middot; PCI Compliant &middot; Secure Payment
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Trust Badges -->
    <div class="checkout-trust-row animate-in">
        <div class="checkout-trust-item">
            <i class="fas fa-shield-alt"></i>
            <span>Secure Checkout</span>
        </div>
        <div class="checkout-trust-item">
            <i class="fas fa-undo-alt"></i>
            <span>Free Returns</span>
        </div>
        <div class="checkout-trust-item">
            <i class="fas fa-lock"></i>
            <span>SSL Encrypted</span>
        </div>
        <div class="checkout-trust-item">
            <i class="fas fa-headset"></i>
            <span>24/7 Support</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subtotal = <?= $subtotal ?>;
    const countrySelect = document.getElementById('shippingCountry');
    const cardFields = document.getElementById('cardFields');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');

    // ── Update summary when country changes ──
    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const taxRate = parseFloat(opt.dataset.tax || 0);
            const shipping = parseFloat(opt.dataset.shipping || 0);
            const tax = subtotal * (taxRate / 100);
            const total = subtotal + tax + shipping;

            document.getElementById('summaryTax').textContent = formatCurrency(tax);
            document.getElementById('summaryShipping').innerHTML = shipping > 0
                ? formatCurrency(shipping)
                : '<span style="color:var(--success);">Free</span>';
            document.getElementById('summaryTotal').textContent = formatCurrency(total);
        });
    }

    // ── Show/hide card fields based on payment method ──
    paymentRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'credit_card' || this.value === 'debit_card') {
                cardFields.classList.add('active');
            } else {
                cardFields.classList.remove('active');
            }
        });
        // Init on load
        if (radio.checked && (radio.value === 'credit_card' || radio.value === 'debit_card')) {
            cardFields.classList.add('active');
        }
    });

    // ── Card number formatting ──
    const cardInput = document.querySelector('input[name="card_number"]');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '').substring(0, 16);
            let formatted = val.replace(/(.{4})/g, '$1 ').trim();
            this.value = formatted;
        });
    }

    // ── Expiry formatting ──
    const expiryInput = document.querySelector('input[name="card_expiry"]');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '').substring(0, 4);
            if (val.length >= 3) {
                val = val.substring(0, 2) + ' / ' + val.substring(2);
            }
            this.value = val;
        });
    }

    function formatCurrency(amount) {
        return '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
