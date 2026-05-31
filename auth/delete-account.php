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
    $password = $_POST['password'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if (empty($password)) {
        $errors[] = 'Password is required to delete your account.';
    } elseif (!password_verify($password, $user['password'])) {
        $errors[] = 'Incorrect password. Account deletion cancelled.';
    }

    if ($confirmation !== 'DELETE') {
        $errors[] = 'Please type DELETE to confirm account deletion.';
    }

    if (empty($errors)) {
        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        // Destroy session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        session_start();
        setFlash('success', 'Your account has been permanently deleted.');
        redirect(BASE_URL . '/index.php');
    }
}

$pageTitle = 'Delete Account';
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
                <a href="<?= BASE_URL ?>/auth/change-password.php" class="sidebar-item">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <a href="<?= BASE_URL ?>/cart/orders.php" class="sidebar-item">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <hr style="border-color: var(--dark-border); margin: 0;">
                <a href="<?= BASE_URL ?>/auth/delete-account.php" class="sidebar-item active" style="color: var(--danger);">
                    <i class="fas fa-trash-alt"></i> Delete Account
                </a>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="col-lg-9">
            <div class="card-dark">
                <div class="card-body p-4">
                    <h4 class="mb-4" style="color: var(--danger); font-family: var(--font-heading);">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                    </h4>

                    <div class="alert alert-dark alert-danger" style="border-left: 4px solid var(--danger);">
                        <h6 style="color: var(--danger); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-circle me-2"></i>Warning: This action is permanent
                        </h6>
                        <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">
                            Deleting your account will permanently remove all your data, including your profile information,
                            order history, and saved preferences. This action cannot be undone.
                        </p>
                    </div>

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
                                        <label class="form-label">Enter Your Password <span style="color: var(--danger);">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: var(--dark-bg); border-color: var(--dark-border); color: var(--danger);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" name="password" class="form-control"
                                                   placeholder="Confirm your password" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Type <strong style="color: var(--danger);">DELETE</strong> to confirm <span style="color: var(--danger);">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: var(--dark-bg); border-color: var(--dark-border); color: var(--danger);">
                                                <i class="fas fa-exclamation"></i>
                                            </span>
                                            <input type="text" name="confirmation" class="form-control"
                                                   placeholder="Type DELETE" required autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="d-flex gap-3">
                                        <button type="submit" class="btn-danger-soft px-4 py-2">
                                            <i class="fas fa-trash-alt me-2"></i>Permanently Delete Account
                                        </button>
                                        <a href="<?= BASE_URL ?>/auth/profile.php" class="btn-gold-outline px-4 py-2" style="text-decoration: none;">
                                            <i class="fas fa-arrow-left me-2"></i>Go Back
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
