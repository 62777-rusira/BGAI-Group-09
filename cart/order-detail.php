<?php
/**
 * Order Detail (READ)
 * Shows full order information, items, totals, shipping, and cancel button if Pending.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if ($orderId <= 0) {
    setFlash('danger', 'Invalid order.');
    redirect(BASE_URL . '/cart/orders.php');
}

// Get order (allow admin to view any order)
if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$orderId]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
}
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Order not found.');
    redirect(BASE_URL . '/cart/orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.image, p.slug
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

$statusClass = 'status-' . strtolower($order['status']);

$pageTitle = 'Order ' . $order['order_number'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1><i class="fas fa-receipt me-2"></i>Order <?= sanitize($order['order_number']) ?></h1>
            <p class="mb-0" style="color: var(--text-secondary);">
                Placed on <?= date('d M Y \a\t h:i A', strtotime($order['created_at'])) ?>
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="status-badge <?= $statusClass ?>" style="font-size: 1rem; padding: 8px 20px;">
                <?= sanitize($order['status']) ?>
            </span>
            <?php if ($order['status'] === 'Pending' && (!isAdmin() || $order['user_id'] == $userId)): ?>
                <form action="<?= BASE_URL ?>/cart/cancel-order.php" method="POST"
                      onsubmit="return confirm('Are you sure you want to cancel this order?');">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" class="btn-danger-soft" style="padding: 8px 20px;">
                        <i class="fas fa-times me-1"></i>Cancel Order
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isAdmin()): ?>
        <!-- Admin: Update Status -->
        <div class="card-dark p-4 mb-4">
            <h5 class="mb-3"><i class="fas fa-cog me-2"></i>Admin: Update Status</h5>
            <form action="<?= BASE_URL ?>/cart/update-order-status.php" method="POST" class="d-flex gap-3 align-items-end">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <div class="flex-grow-1">
                    <label class="form-label">Order Status</label>
                    <select name="status" class="form-select form-dark">
                        <?php foreach (['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-gold" style="padding: 10px 25px; border:none; cursor:pointer;">
                    <i class="fas fa-save me-1"></i>Update
                </button>
            </form>
            <?php if (isset($order['customer_name'])): ?>
                <div class="mt-3" style="color: var(--text-secondary);">
                    <small>Customer: <strong><?= sanitize($order['customer_name']) ?></strong> (<?= sanitize($order['customer_email']) ?>)</small>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Order Items -->
        <div class="col-lg-8">
            <div class="card-dark p-4">
                <h4 class="mb-4"><i class="fas fa-gem me-2"></i>Order Items</h4>
                <?php foreach ($orderItems as $item): ?>
                    <div class="cart-item d-flex align-items-center mb-3 pb-3" style="border-bottom: 1px solid var(--dark-border);">
                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($item['image'] ?? 'placeholder.jpg') ?>"
                             alt="<?= sanitize($item['product_name']) ?>"
                             class="rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= sanitize($item['product_name']) ?></h6>
                            <small style="color: var(--text-secondary);">
                                <?= formatPrice($item['price']) ?> x <?= $item['quantity'] ?>
                            </small>
                        </div>
                        <strong><?= formatPrice($item['total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary & Shipping -->
        <div class="col-lg-4">
            <!-- Totals -->
            <div class="card-dark order-summary p-4 mb-4">
                <h4 class="mb-4">Order Total</h4>
                <div class="summary-row d-flex justify-content-between mb-2">
                    <span style="color: var(--text-secondary);">Subtotal</span>
                    <span><?= formatPrice($order['subtotal']) ?></span>
                </div>
                <div class="summary-row d-flex justify-content-between mb-2">
                    <span style="color: var(--text-secondary);">Tax</span>
                    <span><?= formatPrice($order['tax_amount']) ?></span>
                </div>
                <div class="summary-row d-flex justify-content-between mb-3">
                    <span style="color: var(--text-secondary);">Shipping</span>
                    <span><?= $order['shipping_amount'] > 0 ? formatPrice($order['shipping_amount']) : 'Free' ?></span>
                </div>
                <hr style="border-color: var(--dark-border);">
                <div class="summary-total d-flex justify-content-between">
                    <strong style="font-size: 1.1rem;">Total</strong>
                    <strong style="font-size: 1.2rem; color: var(--gold);"><?= formatPrice($order['total']) ?></strong>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="card-dark p-4">
                <h4 class="mb-4"><i class="fas fa-shipping-fast me-2"></i>Shipping</h4>
                <div class="mb-2">
                    <small style="color: var(--text-secondary);">Name</small>
                    <p class="mb-1"><?= sanitize($order['shipping_name']) ?></p>
                </div>
                <div class="mb-2">
                    <small style="color: var(--text-secondary);">Address</small>
                    <p class="mb-1"><?= nl2br(sanitize($order['shipping_address'])) ?></p>
                </div>
                <div class="mb-2">
                    <small style="color: var(--text-secondary);">City</small>
                    <p class="mb-1"><?= sanitize($order['shipping_city']) ?></p>
                </div>
                <div class="mb-2">
                    <small style="color: var(--text-secondary);">Country</small>
                    <p class="mb-1"><?= sanitize($order['shipping_country']) ?></p>
                </div>
                <div class="mb-2">
                    <small style="color: var(--text-secondary);">Phone</small>
                    <p class="mb-1"><?= sanitize($order['shipping_phone']) ?></p>
                </div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="mb-0">
                        <small style="color: var(--text-secondary);">Notes</small>
                        <p class="mb-0"><?= nl2br(sanitize($order['notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>/cart/orders.php" class="btn-gold-outline" style="text-decoration:none; padding: 10px 25px;">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
