<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

// Fetch all payments for the logged-in user
$stmt = $pdo->prepare("
    SELECT p.*, o.order_number
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$payments = $stmt->fetchAll();

$pageTitle = 'Payment History';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="page-header mb-4">
        <h1 class="display-6 fw-bold" style="font-family:'Playfair Display',serif;">
            <i class="fas fa-credit-card me-2" style="color:var(--gold);"></i>Payment History
        </h1>
        <p class="text-white-50">View all your payment transactions</p>
    </div>

    <?php if (empty($payments)): ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-receipt fa-3x mb-3" style="color:var(--gold);"></i>
            <h4>No payments found</h4>
            <p class="text-white-50">You haven't made any payments yet.</p>
            <a href="<?= BASE_URL ?>/products/shop.php" class="btn btn-gold">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="card-dark p-4">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Order #</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><code><?= sanitize($p['transaction_id'] ?? 'N/A') ?></code></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/cart/orders.php?id=<?= $p['order_id'] ?>" class="text-warning text-decoration-none">
                                        <?= sanitize($p['order_number']) ?>
                                    </a>
                                </td>
                                <td class="fw-bold"><?= sanitize($p['currency_code']) ?> <?= number_format($p['amount'], 2) ?></td>
                                <td>
                                    <?php
                                    $methodIcons = [
                                        'Credit Card'   => 'fas fa-credit-card',
                                        'Debit Card'    => 'fas fa-credit-card',
                                        'PayPal'        => 'fab fa-paypal',
                                        'Bank Transfer' => 'fas fa-university',
                                    ];
                                    $icon = $methodIcons[$p['payment_method']] ?? 'fas fa-money-bill';
                                    ?>
                                    <i class="<?= $icon ?> me-1" style="color:var(--gold);"></i>
                                    <?= sanitize($p['payment_method']) ?>
                                    <?php if ($p['card_last_four']): ?>
                                        <span class="text-white-50">****<?= sanitize($p['card_last_four']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'Pending'   => 'status-pending',
                                        'Completed' => 'status-completed',
                                        'Failed'    => 'status-failed',
                                        'Refunded'  => 'status-refunded',
                                    ];
                                    $cls = $statusClass[$p['status']] ?? 'status-pending';
                                    ?>
                                    <span class="status-badge <?= $cls ?>"><?= sanitize($p['status']) ?></span>
                                </td>
                                <td><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/payments/detail.php?id=<?= $p['id'] ?>" class="btn btn-gold-outline btn-sm">
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
