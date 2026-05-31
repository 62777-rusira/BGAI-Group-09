<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

// Fetch current user data
$stmt = $pdo->prepare("SELECT u.*, c.name AS country_name FROM users u LEFT JOIN countries c ON u.country_code = c.code WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = 'My Profile';
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
                    <small style="color: var(--text-muted);">Member since <?= date('M Y', strtotime($user['created_at'])) ?></small>
                </div>
                <hr style="border-color: var(--dark-border); margin: 0;">
                <a href="<?= BASE_URL ?>/auth/profile.php" class="sidebar-item active">
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
                <a href="<?= BASE_URL ?>/auth/delete-account.php" class="sidebar-item" style="color: var(--danger);">
                    <i class="fas fa-trash-alt"></i> Delete Account
                </a>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="col-lg-9">
            <div class="card-dark">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 style="color: var(--text-primary); margin: 0; font-family: var(--font-heading);">
                            <i class="fas fa-user me-2" style="color: var(--gold);"></i>Profile Information
                        </h4>
                        <a href="<?= BASE_URL ?>/auth/update-profile.php" class="btn-gold-outline" style="text-decoration: none; padding: 0.4rem 1rem; font-size: 0.85rem;">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Full Name</label>
                                <p style="color: var(--text-primary); font-size: 1.05rem; margin-top: 0.25rem;"><?= sanitize($user['name']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Email Address</label>
                                <p style="color: var(--text-primary); font-size: 1.05rem; margin-top: 0.25rem;">
                                    <i class="fas fa-envelope me-2" style="color: var(--gold); font-size: 0.85rem;"></i><?= sanitize($user['email']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Phone</label>
                                <p style="color: var(--text-primary); font-size: 1.05rem; margin-top: 0.25rem;">
                                    <?php if ($user['phone']): ?>
                                        <i class="fas fa-phone me-2" style="color: var(--gold); font-size: 0.85rem;"></i><?= sanitize($user['phone']) ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic;">Not provided</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Country</label>
                                <p style="color: var(--text-primary); font-size: 1.05rem; margin-top: 0.25rem;">
                                    <i class="fas fa-globe me-2" style="color: var(--gold); font-size: 0.85rem;"></i><?= sanitize($user['country_name'] ?? $user['country_code']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">City</label>
                                <p style="color: var(--text-primary); font-size: 1.05rem; margin-top: 0.25rem;">
                                    <?php if ($user['city']): ?>
                                        <i class="fas fa-city me-2" style="color: var(--gold); font-size: 0.85rem;"></i><?= sanitize($user['city']) ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic;">Not provided</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Account Role</label>
                                <p style="margin-top: 0.25rem;">
                                    <span style="background: <?= $user['role'] === 'admin' ? 'var(--gold)' : 'var(--dark-bg)' ?>; color: <?= $user['role'] === 'admin' ? 'var(--dark-bg)' : 'var(--gold)' ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; border: 1px solid var(--gold);">
                                        <i class="fas fa-<?= $user['role'] === 'admin' ? 'crown' : 'user' ?> me-1"></i><?= ucfirst($user['role']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if ($user['address']): ?>
                    <div class="mt-2">
                        <label style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Address</label>
                        <p style="color: var(--text-primary); font-size: 1.05rem; margin-top: 0.25rem;">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--gold); font-size: 0.85rem;"></i><?= sanitize($user['address']) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <hr style="border-color: var(--dark-border); margin: 1.5rem 0;">

                    <div class="row">
                        <div class="col-md-6">
                            <small style="color: var(--text-muted);">
                                <i class="fas fa-calendar me-1"></i> Account created: <?= date('d M Y, h:i A', strtotime($user['created_at'])) ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small style="color: var(--text-muted);">
                                <i class="fas fa-clock me-1"></i> Last updated: <?= date('d M Y, h:i A', strtotime($user['updated_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
