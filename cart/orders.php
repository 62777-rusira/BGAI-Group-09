<?php
/**
 * My Orders (READ)
 * Lists all orders for the logged-in user with status badges.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT id, order_number, total, currency_code, status, created_at,
           (SELECT COUNT(*) FROM order_items WHERE order_id = orders.id) AS item_count
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="page-header mb-4">
        <h1><i class="fas fa-box me-2"></i>My Orders</h1>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-box-open fa-4x mb-3" style="color: var(--gold); opacity: 0.5;"></i>
            <h3>No orders yet</h3>
            <p style="color: var(--text-secondary);">Start shopping to see your orders here.</p>
            <a href="<?= BASE_URL ?>/products/shop.php" class="btn-gold mt-3" style="text-decoration:none; display:inline-block; padding:10px 30px;">
                <i class="fas fa-gem me-2"></i>Browse Collection
            </a>
        </div>
    <?php else: ?>
        <div class="card-dark p-0">
            <div class="table-responsive">
                <table class="table table-dark-custom mb-0">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $statusClass = 'status-' . strtolower($order['status']);
                            ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($order['order_number']) ?></strong>
                                </td>
                                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                <td><?= $order['item_count'] ?> item<?= $order['item_count'] > 1 ? 's' : '' ?></td>
                                <td><strong style="color: var(--gold);"><?= formatPrice($order['total']) ?></strong></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= sanitize($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/cart/order-detail.php?id=<?= $order['id'] ?>"
                                       class="btn-gold-outline btn-sm" style="text-decoration:none; padding: 5px 15px;">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
