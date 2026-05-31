<?php
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Manage Payments';
include __DIR__ . '/../includes/admin-header.php';

// Filters
$filterStatus = $_GET['status'] ?? '';
$filterMethod = $_GET['method'] ?? '';
$search       = $_GET['search'] ?? '';

$where  = ['1=1'];
$params = [];

if ($filterStatus !== '') {
    $where[]  = 'p.status = ?';
    $params[] = $filterStatus;
}
if ($filterMethod !== '') {
    $where[]  = 'p.payment_method = ?';
    $params[] = $filterMethod;
}
if ($search !== '') {
    $where[]  = '(o.order_number LIKE ? OR p.transaction_id LIKE ? OR u.name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT p.*, o.order_number, u.name AS user_name, u.email AS user_email
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    JOIN users u ON p.user_id = u.id
    WHERE $whereSql
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$payments = $stmt->fetchAll();
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1"><i class="fas fa-credit-card me-2" style="color:var(--gold);"></i>Payment Management</h2>
    <p class="text-white-50 mb-0">View and manage all payment transactions</p>
</div>

<!-- Filters -->
<div class="card-dark p-4 mb-4">
    <form method="GET" action="" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-white-50">Search</label>
            <input type="text" name="search" class="form-control form-dark" placeholder="Order #, Transaction ID, User..." value="<?= sanitize($search) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-white-50">Status</label>
            <select name="status" class="form-control form-dark">
                <option value="">All Statuses</option>
                <?php foreach (['Pending', 'Completed', 'Failed', 'Refunded'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-white-50">Payment Method</label>
            <select name="method" class="form-control form-dark">
                <option value="">All Methods</option>
                <?php foreach (['Credit Card', 'Debit Card', 'PayPal', 'Bank Transfer'] as $m): ?>
                    <option value="<?= $m ?>" <?= $filterMethod === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-gold me-2"><i class="fas fa-filter me-1"></i>Filter</button>
            <a href="<?= BASE_URL ?>/payments/manage.php" class="btn btn-gold-outline">Clear</a>
        </div>
    </form>
</div>

<!-- Results -->
<?php if (empty($payments)): ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-search fa-3x mb-3" style="color:var(--gold);"></i>
        <h4>No payments found</h4>
        <p class="text-white-50">No payments match the current filters.</p>
    </div>
<?php else: ?>
    <div class="card-dark p-4">
        <div class="mb-3 text-white-50 small"><?= count($payments) ?> payment(s) found</div>
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Order #</th>
                        <th>Customer</th>
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
                            <td><code class="small"><?= sanitize($p['transaction_id'] ?? 'N/A') ?></code></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/orders.php?id=<?= $p['order_id'] ?>" class="text-warning text-decoration-none">
                                    <?= sanitize($p['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div><?= sanitize($p['user_name']) ?></div>
                                <small class="text-white-50"><?= sanitize($p['user_email']) ?></small>
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
                                    <br><small class="text-white-50">****<?= sanitize($p['card_last_four']) ?></small>
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
                            <td><?= date('d M Y', strtotime($p['created_at'])) ?><br><small class="text-white-50"><?= date('H:i', strtotime($p['created_at'])) ?></small></td>
                            <td>
                                <a href="<?= BASE_URL ?>/payments/detail.php?id=<?= $p['id'] ?>&admin=1" class="btn btn-gold-outline btn-sm me-1" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($p['status'] === 'Completed'): ?>
                                    <button type="button" class="btn btn-danger-soft btn-sm" title="Refund" data-bs-toggle="modal" data-bs-target="#refundModal<?= $p['id'] ?>">
                                        <i class="fas fa-undo"></i>
                                    </button>

                                    <!-- Refund Modal -->
                                    <div class="modal fade" id="refundModal<?= $p['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content" style="background:var(--dark-card);border-color:var(--dark-border);">
                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title"><i class="fas fa-undo me-2" style="color:var(--gold);"></i>Refund Payment</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="<?= BASE_URL ?>/payments/refund.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                        <p class="text-white-50">Refund <strong><?= sanitize($p['currency_code']) ?> <?= number_format($p['amount'], 2) ?></strong> for order <strong><?= sanitize($p['order_number']) ?></strong>?</p>
                                                        <div class="mb-3">
                                                            <label for="refund_reason_<?= $p['id'] ?>" class="form-label">Refund Reason <span class="text-danger">*</span></label>
                                                            <textarea name="refund_reason" id="refund_reason_<?= $p['id'] ?>" class="form-control form-dark" rows="3" placeholder="Enter the reason for this refund..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-gold-outline" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger-soft"><i class="fas fa-undo me-1"></i>Process Refund</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
