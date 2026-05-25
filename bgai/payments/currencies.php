<?php
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Manage Currencies';
include __DIR__ . '/../includes/admin-header.php';

$currencies = $pdo->query("SELECT * FROM currencies ORDER BY code ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fas fa-coins me-2" style="color:var(--gold);"></i>Currencies</h2>
        <p class="text-white-50 mb-0">Manage currency exchange rates and settings</p>
    </div>
    <a href="<?= BASE_URL ?>/payments/add-currency.php" class="btn btn-gold">
        <i class="fas fa-plus me-1"></i>Add Currency
    </a>
</div>

<?php if (empty($currencies)): ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-coins fa-3x mb-3" style="color:var(--gold);"></i>
        <h4>No currencies found</h4>
        <p class="text-white-50">Add your first currency to get started.</p>
        <a href="<?= BASE_URL ?>/payments/add-currency.php" class="btn btn-gold">Add Currency</a>
    </div>
<?php else: ?>
    <div class="card-dark p-4">
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currencies as $c): ?>
                        <tr>
                            <td><span class="fw-bold"><?= sanitize($c['code']) ?></span></td>
                            <td><?= sanitize($c['name']) ?></td>
                            <td class="fs-5"><?= sanitize($c['symbol']) ?></td>
                            <td><?= number_format($c['exchange_rate'], 4) ?></td>
                            <td>
                                <?php if ($c['is_active']): ?>
                                    <span class="status-badge status-completed">Active</span>
                                <?php else: ?>
                                    <span class="status-badge status-failed">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($c['updated_at'])) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/payments/edit-currency.php?id=<?= $c['id'] ?>" class="btn btn-gold-outline btn-sm me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($c['code'] !== 'AUD'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/payments/delete-currency.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this currency?');">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger-soft btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
