<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

// Fetch current user
$stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect(BASE_URL . '/auth/login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required.';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $errors[] = 'Current password is incorrect.';
    }

    if (empty($newPassword)) {
        $errors[] = 'New password is required.';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match.';
    }

    if (!empty($currentPassword) && !empty($newPassword) && $currentPassword === $newPassword) {
        $errors[] = 'New password must be different from current password.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

        setFlash('success', 'Password changed successfully!');
        redirect(BASE_URL . '/auth/profile.php');
    }
}

$pageTitle = 'Change Password';
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
                <a href="<?= BASE_URL ?>/auth/update-profile.php" class="sidebar-item">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
                <a href="<?= BASE_URL ?>/auth/change-password.php" class="sidebar-item active">
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

        <!-- Change Password Form -->
        <div class="col-lg-9">
            <div class="card-dark">
                <div class="card-body p-4">
                    <h4 class="mb-4" style="color: var(--text-primary); font-family: var(--font-heading);">
                        <i class="fas fa-key me-2" style="color: var(--gold);"></i>Change Password
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
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password <span style="color: var(--danger);">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: var(--dark-bg); border-color: var(--dark-border); color: var(--gold);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" name="current_password" class="form-control"
                                                   placeholder="Enter current password" required>
                                        </div>
                                    </div>

                                    <hr style="border-color: var(--dark-border); margin: 1.5rem 0;">

                                    <div class="mb-3">
                                        <label class="form-label">New Password <span style="color: var(--danger);">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: var(--dark-bg); border-color: var(--dark-border); color: var(--gold);">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" name="new_password" class="form-control"
                                                   placeholder="Minimum 6 characters" required minlength="6">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Confirm New Password <span style="color: var(--danger);">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: var(--dark-bg); border-color: var(--dark-border); color: var(--gold);">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" name="confirm_password" class="form-control"
                                                   placeholder="Re-enter new password" required>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-3">
                                        <button type="submit" class="btn-gold px-4 py-2">
                                            <i class="fas fa-save me-2"></i>Update Password
                                        </button>
                                        <a href="<?= BASE_URL ?>/auth/profile.php" class="btn-gold-outline px-4 py-2" style="text-decoration: none;">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
