<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$paymentId = (int)($_GET['id'] ?? 0);
$isAdminView = isset($_GET['admin']) && isAdmin();
$backUrl = $isAdminView ? BASE_URL . '/payments/manage.php' : BASE_URL . '/payments/history.php';

if ($paymentId <= 0) {
    setFlash('danger', 'Invalid payment ID.');
    redirect($backUrl);
}

// Fetch payment with order info - admin can view any payment
if ($isAdminView) {
    $stmt = $pdo->prepare("
        SELECT p.*, o.order_number, o.subtotal, o.tax_amount, o.shipping_amount, o.total AS order_total,
               o.status AS order_status, o.shipping_name, o.shipping_address, o.shipping_city,
               o.shipping_country, o.shipping_phone, o.currency_code AS order_currency
        FROM payments p
        JOIN orders o ON p.order_id = o.id
        WHERE p.id = ?
    ");
    $stmt->execute([$paymentId]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, o.order_number, o.subtotal, o.tax_amount, o.shipping_amount, o.total AS order_total,
               o.status AS order_status, o.shipping_name, o.shipping_address, o.shipping_city,
               o.shipping_country, o.shipping_phone, o.currency_code AS order_currency
        FROM payments p
        JOIN orders o ON p.order_id = o.id
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$paymentId, $_SESSION['user_id']]);
}
$payment = $stmt->fetch();

if (!$payment) {
    setFlash('danger', 'Payment not found.');
    redirect($backUrl);
}

$pageTitle = 'Payment Details';
include __DIR__ . '/../includes/header.php';

$statusClass = [
    'Pending'   => 'status-pending',
    'Completed' => 'status-completed',
    'Failed'    => 'status-failed',
    'Refunded'  => 'status-refunded',
];
$cls = $statusClass[$payment['status']] ?? 'status-pending';

$methodIcons = [
    'Credit Card'   => 'fas fa-credit-card',
    'Debit Card'    => 'fas fa-credit-card',
    'PayPal'        => 'fab fa-paypal',
    'Bank Transfer' => 'fas fa-university',
];
$icon = $methodIcons[$payment['payment_method']] ?? 'fas fa-money-bill';
?>

<div class="container py-5">
    <div class="page-header mb-4">
        <a href="<?= $backUrl ?>" class="btn btn-gold-outline btn-sm mb-3">
            <i class="fas fa-arrow-left me-1"></i>Back to <?= $isAdminView ? 'Payment Management' : 'Payment History' ?>
        </a>
        <h1 class="display-6 fw-bold" style="font-family:'Playfair Display',serif;">
            <i class="fas fa-receipt me-2" style="color:var(--gold);"></i>Payment Details
        </h1>
    </div>

    <div class="row g-4">
        <!-- Payment Information -->
        <div class="col-lg-7">
            <div class="card-dark p-4 mb-4">
                <h5 class="mb-4" style="color:var(--gold);"><i class="fas fa-credit-card me-2"></i>Payment Information</h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-white-50 small d-block">Transaction ID</label>
                        <code class="fs-6"><?= sanitize($payment['transaction_id'] ?? 'N/A') ?></code>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-white-50 small d-block">Status</label>
                        <span class="status-badge <?= $cls ?>"><?= sanitize($payment['status']) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-white-50 small d-block">Amount</label>
                        <span class="fs-5 fw-bold"><?= sanitize($payment['currency_code']) ?> <?= number_format($payment['amount'], 2) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-white-50 small d-block">Payment Method</label>
                        <span><i class="<?= $icon ?> me-1" style="color:var(--gold);"></i><?= sanitize($payment['payment_method']) ?></span>
                        <?php if ($payment['card_last_four']): ?>
                            <span class="text-white-50 ms-1">****<?= sanitize($payment['card_last_four']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-white-50 small d-block">Payment Date</label>
                        <span><?= date('d M Y, H:i:s', strtotime($payment['created_at'])) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-white-50 small d-block">Last Updated</label>
                        <span><?= date('d M Y, H:i:s', strtotime($payment['updated_at'])) ?></span>
                    </div>
                    <?php if ($payment['refund_reason']): ?>
                        <div class="col-12">
                            <label class="text-white-50 small d-block">Refund Reason</label>
                            <div class="alert alert-dark mt-1 mb-0"><?= sanitize($payment['refund_reason']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-5">
            <div class="card-dark p-4 mb-4">
                <h5 class="mb-4" style="color:var(--gold);"><i class="fas fa-shopping-bag me-2"></i>Order Summary</h5>
                <div class="mb-3">
                    <label class="text-white-50 small d-block">Order Number</label>
                    <span class="fw-bold"><?= sanitize($payment['order_number']) ?></span>
                </div>
                <div class="mb-3">
                    <label class="text-white-50 small d-block">Order Status</label>
                    <?php
                    $orderStatusClass = [
                        'Pending'    => 'status-pending',
                        'Processing' => 'status-pending',
                        'Shipped'    => 'status-completed',
                        'Delivered'  => 'status-completed',
                        'Cancelled'  => 'status-failed',
                    ];
                    $osCls = $orderStatusClass[$payment['order_status']] ?? 'status-pending';
                    ?>
                    <span class="status-badge <?= $osCls ?>"><?= sanitize($payment['order_status']) ?></span>
                </div>
                <hr style="border-color:var(--dark-border);">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Subtotal</span>
                    <span><?= sanitize($payment['order_currency']) ?> <?= number_format($payment['subtotal'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Tax</span>
                    <span><?= sanitize($payment['order_currency']) ?> <?= number_format($payment['tax_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Shipping</span>
                    <span><?= sanitize($payment['order_currency']) ?> <?= number_format($payment['shipping_amount'], 2) ?></span>
                </div>
                <hr style="border-color:var(--dark-border);">
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span style="color:var(--gold);"><?= sanitize($payment['order_currency']) ?> <?= number_format($payment['order_total'], 2) ?></span>
                </div>
            </div>

            <?php if ($payment['shipping_name']): ?>
            <div class="card-dark p-4">
                <h5 class="mb-3" style="color:var(--gold);"><i class="fas fa-truck me-2"></i>Shipping Details</h5>
                <p class="mb-1 fw-bold"><?= sanitize($payment['shipping_name']) ?></p>
                <p class="mb-1 text-white-50"><?= sanitize($payment['shipping_address']) ?></p>
                <p class="mb-1 text-white-50"><?= sanitize($payment['shipping_city']) ?>, <?= sanitize($payment['shipping_country']) ?></p>
                <?php if ($payment['shipping_phone']): ?>
                    <p class="mb-0 text-white-50"><i class="fas fa-phone me-1"></i><?= sanitize($payment['shipping_phone']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
