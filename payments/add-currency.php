<?php
require_once __DIR__ . '/../config/db.php';

$errors = [];
$old = ['code' => '', 'name' => '', 'symbol' => '', 'exchange_rate' => '1.0000', 'is_active' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['code']          = strtoupper(trim($_POST['code'] ?? ''));
    $old['name']          = trim($_POST['name'] ?? '');
    $old['symbol']        = trim($_POST['symbol'] ?? '');
    $old['exchange_rate'] = trim($_POST['exchange_rate'] ?? '');
    $old['is_active']     = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if ($old['code'] === '' || strlen($old['code']) > 10) {
        $errors[] = 'Currency code is required (max 10 characters).';
    }
    if ($old['name'] === '' || strlen($old['name']) > 50) {
        $errors[] = 'Currency name is required (max 50 characters).';
    }
    if ($old['symbol'] === '' || strlen($old['symbol']) > 10) {
        $errors[] = 'Symbol is required (max 10 characters).';
    }
    if (!is_numeric($old['exchange_rate']) || $old['exchange_rate'] <= 0) {
        $errors[] = 'Exchange rate must be a positive number.';
    }

    // Check duplicate code
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM currencies WHERE code = ?");
        $stmt->execute([$old['code']]);
        if ($stmt->fetch()) {
            $errors[] = 'A currency with this code already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO currencies (code, name, symbol, exchange_rate, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$old['code'], $old['name'], $old['symbol'], $old['exchange_rate'], $old['is_active']]);
        setFlash('success', 'Currency added successfully.');
        redirect(BASE_URL . '/payments/currencies.php');
    }
}

$pageTitle = 'Add Currency';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/payments/currencies.php" class="btn btn-gold-outline btn-sm mb-3">
        <i class="fas fa-arrow-left me-1"></i>Back to Currencies
    </a>
    <h2 class="fw-bold"><i class="fas fa-plus-circle me-2" style="color:var(--gold);"></i>Add Currency</h2>
    <p class="text-white-50">Add a new currency to the system</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-dark alert-danger mb-4">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= sanitize($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card-dark p-4" style="max-width:600px;">
    <form method="POST" action="">
        <div class="mb-3">
            <label for="code" class="form-label">Currency Code <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control form-dark" value="<?= sanitize($old['code']) ?>" placeholder="e.g. USD, EUR, GBP" maxlength="10" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Currency Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control form-dark" value="<?= sanitize($old['name']) ?>" placeholder="e.g. US Dollar" maxlength="50" required>
        </div>
        <div class="mb-3">
            <label for="symbol" class="form-label">Symbol <span class="text-danger">*</span></label>
            <input type="text" name="symbol" id="symbol" class="form-control form-dark" value="<?= sanitize($old['symbol']) ?>" placeholder="e.g. $, &euro;, &pound;" maxlength="10" required>
        </div>
        <div class="mb-3">
            <label for="exchange_rate" class="form-label">Exchange Rate (relative to AUD) <span class="text-danger">*</span></label>
            <input type="number" step="0.0001" min="0.0001" name="exchange_rate" id="exchange_rate" class="form-control form-dark" value="<?= sanitize($old['exchange_rate']) ?>" required>
            <small class="text-white-50">1 AUD = X of this currency</small>
        </div>
        <div class="mb-4">
            <div class="form-check form-switch">
                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" <?= $old['is_active'] ? 'checked' : '' ?>>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
        </div>
        <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i>Save Currency</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
