<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect(BASE_URL . '/auth/login.php');
}

// Fetch countries
$countries = $pdo->query("SELECT code, name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country_code = trim($_POST['country_code'] ?? 'AU');

    // Validation
    if (empty($name)) $errors[] = 'Full name is required.';
    if (strlen($name) > 100) $errors[] = 'Name must be under 100 characters.';

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // Check duplicate email (exclude current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already used by another account.';
        }
    }

    if (!empty($phone) && !preg_match('/^[\+0-9\s\-\(\)]{7,20}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, city = ?, country_code = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone ?: null, $address ?: null, $city ?: null, $country_code, $_SESSION['user_id']]);

        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['email'] = $email;

        setFlash('success', 'Profile updated successfully!');
        redirect(BASE_URL . '/auth/profile.php');
    }

    // If errors, use submitted data
    $user['name'] = $name;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['address'] = $address;
    $user['city'] = $city;
    $user['country_code'] = $country_code;
}

$pageTitle = 'Edit Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="sidebar-dark">
                <div class="text-center p-4">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--gold-dark)); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; color: var(--dark-bg);">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h6 style="color: var(--text-primary);"><?= sanitize($user['name']) ?></h6>
                </div>
                <hr style="border-color: var(--dark-border); margin: 0;">
                <a href="<?= BASE_URL ?>/auth/profile.php" class="sidebar-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="<?= BASE_URL ?>/auth/update-profile.php" class="sidebar-item active">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
                <a href="<?= BASE_URL ?>/auth/change-password.php" class="sidebar-item">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <a href="<?= BASE_URL ?>/cart/orders.php" class="sidebar-item">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <hr style="border-color: var(--dark-border); margin: 0;">
                <a href="<?= BASE_URL ?>/auth/delete-account.php" class="sidebar-item" style="color: var(--danger);">
                    <i class="fas fa-trash-alt"></i> Delete Account
                </a>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-lg-9">
            <div class="card-dark">
                <div class="card-body p-4">
                    <h4 class="mb-4" style="color: var(--text-primary); font-family: var(--font-heading);">
                        <i class="fas fa-edit me-2" style="color: var(--gold);"></i>Edit Profile
                    </h4>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-dark alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle me-2"></i><?= sanitize($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-dark" style="padding: 0; background: transparent; border: none;">
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span style="color: var(--danger);">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                           value="<?= sanitize($user['name']) ?>" required maxlength="100">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span style="color: var(--danger);">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= sanitize($user['email']) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+61 400 000 000"
                                           value="<?= sanitize($user['phone'] ?? '') ?>" maxlength="20">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <select name="country_code" class="form-select">
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= sanitize($c['code']) ?>" <?= $c['code'] === $user['country_code'] ? 'selected' : '' ?>>
                                                <?= sanitize($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" placeholder="Your city"
                                           value="<?= sanitize($user['city'] ?? '') ?>" maxlength="100">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3"
                                              placeholder="Your full address"><?= sanitize($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn-gold px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="<?= BASE_URL ?>/auth/profile.php" class="btn-gold-outline px-4 py-2" style="text-decoration: none;">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
