<?php
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Manage Countries';
include __DIR__ . '/../includes/admin-header.php';

$countries = $pdo->query("
    SELECT co.*, cu.name AS currency_name, cu.symbol AS currency_symbol
    FROM countries co
    LEFT JOIN currencies cu ON co.currency_code = cu.code
    ORDER BY co.name ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fas fa-globe me-2" style="color:var(--gold);"></i>Countries</h2>
        <p class="text-white-50 mb-0">Manage countries, tax rates, and shipping fees</p>
    </div>
    <a href="<?= BASE_URL ?>/payments/add-country.php" class="btn btn-gold">
        <i class="fas fa-plus me-1"></i>Add Country
    </a>
</div>

<?php if (empty($countries)): ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-globe fa-3x mb-3" style="color:var(--gold);"></i>
        <h4>No countries found</h4>
        <p class="text-white-50">Add your first country to get started.</p>
        <a href="<?= BASE_URL ?>/payments/add-country.php" class="btn btn-gold">Add Country</a>
    </div>
<?php else: ?>
    <div class="card-dark p-4">
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Currency</th>
                        <th>Tax Rate</th>
                        <th>Shipping Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($countries as $c): ?>
                        <tr>
                            <td><span class="fw-bold"><?= sanitize($c['code']) ?></span></td>
                            <td><?= sanitize($c['name']) ?></td>
                            <td><?= sanitize($c['currency_symbol'] ?? '') ?> <?= sanitize($c['currency_code']) ?></td>
                            <td><?= number_format($c['tax_rate'], 2) ?>%</td>
                            <td><?= sanitize($c['currency_symbol'] ?? '$') ?><?= number_format($c['shipping_fee'], 2) ?></td>
                            <td>
                                <?php if ($c['is_active']): ?>
                                    <span class="status-badge status-completed">Active</span>
                                <?php else: ?>
                                    <span class="status-badge status-failed">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/payments/edit-country.php?id=<?= $c['id'] ?>" class="btn btn-gold-outline btn-sm me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/payments/delete-country.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this country?');">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-danger-soft btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
