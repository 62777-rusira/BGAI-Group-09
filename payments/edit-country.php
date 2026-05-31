<?php
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid country ID.');
    redirect(BASE_URL . '/payments/countries.php');
}

$stmt = $pdo->prepare("SELECT * FROM countries WHERE id = ?");
$stmt->execute([$id]);
$country = $stmt->fetch();

if (!$country) {
    setFlash('danger', 'Country not found.');
    redirect(BASE_URL . '/payments/countries.php');
}

$currencies = $pdo->query("SELECT code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY code")->fetchAll();

$errors = [];
$old = $country;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']          = trim($_POST['name'] ?? '');
    $old['currency_code'] = trim($_POST['currency_code'] ?? '');
    $old['tax_rate']      = trim($_POST['tax_rate'] ?? '0');
    $old['shipping_fee']  = trim($_POST['shipping_fee'] ?? '0');
    $old['is_active']     = isset($_POST['is_active']) ? 1 : 0;

    if ($old['name'] === '' || strlen($old['name']) > 100) {
        $errors[] = 'Country name is required (max 100 characters).';
    }
    if ($old['currency_code'] === '') {
        $errors[] = 'Please select a currency.';
    }
    if (!is_numeric($old['tax_rate']) || $old['tax_rate'] < 0) {
        $errors[] = 'Tax rate must be a non-negative number.';
    }
    if (!is_numeric($old['shipping_fee']) || $old['shipping_fee'] < 0) {
        $errors[] = 'Shipping fee must be a non-negative number.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE countries SET name = ?, currency_code = ?, tax_rate = ?, shipping_fee = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$old['name'], $old['currency_code'], $old['tax_rate'], $old['shipping_fee'], $old['is_active'], $id]);
        setFlash('success', 'Country updated successfully.');
        redirect(BASE_URL . '/payments/countries.php');
    }
}

$pageTitle = 'Edit Country';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/payments/countries.php" class="btn btn-gold-outline btn-sm mb-3">
        <i class="fas fa-arrow-left me-1"></i>Back to Countries
    </a>
    <h2 class="fw-bold"><i class="fas fa-edit me-2" style="color:var(--gold);"></i>Edit Country: <?= sanitize($country['name']) ?></h2>
    <p class="text-white-50">Update country settings</p>
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
            <label class="form-label">Country Code</label>
            <input type="text" class="form-control form-dark" value="<?= sanitize($old['code']) ?>" disabled>
            <small class="text-white-50">Country code cannot be changed.</small>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Country Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control form-dark" value="<?= sanitize($old['name']) ?>" maxlength="100" required>
        </div>
        <div class="mb-3">
            <label for="currency_code" class="form-label">Currency <span class="text-danger">*</span></label>
            <select name="currency_code" id="currency_code" class="form-control form-dark" required>
                <option value="">-- Select Currency --</option>
                <?php foreach ($currencies as $cur): ?>
                    <option value="<?= sanitize($cur['code']) ?>" <?= $old['currency_code'] === $cur['code'] ? 'selected' : '' ?>>
                        <?= sanitize($cur['symbol']) ?> <?= sanitize($cur['code']) ?> - <?= sanitize($cur['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="tax_rate" class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="tax_rate" id="tax_rate" class="form-control form-dark" value="<?= sanitize($old['tax_rate']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="shipping_fee" class="form-label">Shipping Fee <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="shipping_fee" id="shipping_fee" class="form-control form-dark" value="<?= sanitize($old['shipping_fee']) ?>" required>
        </div>
        <div class="mb-4">
            <div class="form-check form-switch">
                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" <?= $old['is_active'] ? 'checked' : '' ?>>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
        </div>
        <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i>Update Country</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
