<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pdo)) require_once __DIR__ . '/../config/db.php';
requireAdmin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?>BGAI Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <div class="brand"><i class="fas fa-gem me-2"></i>BGAI Admin</div>

    <div class="nav-section">Main</div>
    <a href="<?= BASE_URL ?>/admin/index.php" class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <div class="nav-section">Management</div>
    <a href="<?= BASE_URL ?>/admin/users.php" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Users
    </a>
    <a href="<?= BASE_URL ?>/products/manage.php" class="nav-item <?= $currentPage === 'manage' ? 'active' : '' ?>">
        <i class="fas fa-gem"></i> Products
    </a>
    <a href="<?= BASE_URL ?>/products/categories.php" class="nav-item <?= $currentPage === 'categories' ? 'active' : '' ?>">
        <i class="fas fa-tags"></i> Categories
    </a>
    <a href="<?= BASE_URL ?>/admin/orders.php" class="nav-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
        <i class="fas fa-shopping-bag"></i> Orders
    </a>
    <a href="<?= BASE_URL ?>/payments/manage.php" class="nav-item <?= $currentPage === 'manage-payments' || $currentPage === 'manage' && strpos($_SERVER['PHP_SELF'], 'payments') ? 'active' : '' ?>">
        <i class="fas fa-credit-card"></i> Payments
    </a>

    <div class="nav-section">Settings</div>
    <a href="<?= BASE_URL ?>/payments/currencies.php" class="nav-item <?= $currentPage === 'currencies' ? 'active' : '' ?>">
        <i class="fas fa-coins"></i> Currencies
    </a>
    <a href="<?= BASE_URL ?>/payments/countries.php" class="nav-item <?= $currentPage === 'countries' ? 'active' : '' ?>">
        <i class="fas fa-globe"></i> Countries
    </a>

    <div class="nav-section">Reports</div>
    <a href="<?= BASE_URL ?>/admin/reports.php" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i> Reports
    </a>
    <a href="<?= BASE_URL ?>/admin/activity-log.php" class="nav-item <?= $currentPage === 'activity-log' ? 'active' : '' ?>">
        <i class="fas fa-history"></i> Activity Log
    </a>
    <a href="<?= BASE_URL ?>/admin/settings.php" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i> Settings
    </a>

    <div class="nav-section" style="margin-top:2rem;"></div>
    <a href="<?= BASE_URL ?>/index.php" class="nav-item"><i class="fas fa-store"></i> View Store</a>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item" style="color:var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>

<!-- Admin Content Area -->
<main class="admin-content">

<!-- Flash Messages -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="alert alert-dark alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert">
    <?= $flash['message'] ?>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
