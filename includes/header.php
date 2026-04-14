<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pdo)) require_once __DIR__ . '/../config/db.php';
$cartCount = isLoggedIn() ? getCartCount($pdo, $_SESSION['user_id']) : 0;
$wishlistCount = isLoggedIn() ? getWishlistCount($pdo, $_SESSION['user_id']) : 0;
$currency = getUserCurrency($pdo);
$currencies = $pdo->query("SELECT code, symbol FROM currencies WHERE is_active = 1 ORDER BY code")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Brilliance Gems of Australia International - Premium Handcrafted Jewellery">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?>Brilliance Gems - BGAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Announcement Bar -->
<div style="background:linear-gradient(90deg,var(--gold-dark),var(--gold),var(--gold-dark));padding:8px 0;text-align:center;font-size:0.78rem;font-weight:600;color:#0a0a0a;letter-spacing:1.5px;text-transform:uppercase;">
    <i class="fas fa-gift me-2"></i> Free shipping on orders over $500 | Use code <strong>BGAI10</strong> for 10% off
</div>

<!-- Glass Navbar -->
<nav class="navbar navbar-expand-lg navbar-bgai fixed-top" style="top:0;">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <i class="fas fa-gem me-2"></i>Brilliance Gems
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="<?= BASE_URL ?>/products/shop.php" data-bs-toggle="dropdown">Shop</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/products/shop.php" style="color:var(--text-secondary);"><i class="fas fa-th me-2" style="color:var(--gold);"></i>All Products</a></li>
                        <li><hr class="dropdown-divider" style="border-color:var(--dark-border);"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/products/shop.php?category=rings" style="color:var(--text-secondary);"><i class="fas fa-ring me-2" style="color:var(--gold);"></i>Rings</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/products/shop.php?category=necklaces" style="color:var(--text-secondary);"><i class="fas fa-link me-2" style="color:var(--gold);"></i>Necklaces</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/products/shop.php?category=bracelets" style="color:var(--text-secondary);"><i class="fas fa-circle me-2" style="color:var(--gold);"></i>Bracelets</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/products/shop.php?category=earrings" style="color:var(--text-secondary);"><i class="fas fa-star me-2" style="color:var(--gold);"></i>Earrings</a></li>
                    </ul>
                </li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/cart/orders.php">My Orders</a></li>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-crown me-1" style="color:var(--gold);font-size:0.7rem;"></i>Admin</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <!-- Currency Selector -->
                <select class="currency-select" id="currencySelect" onchange="changeCurrency(this.value)">
                    <?php foreach ($currencies as $c): ?>
                    <option value="<?= $c['code'] ?>" <?= ($currency['code'] === $c['code']) ? 'selected' : '' ?>>
                        <?= $c['symbol'] ?> <?= $c['code'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <!-- Search Icon -->
                <a href="<?= BASE_URL ?>/products/shop.php" style="color:var(--text-secondary);font-size:1rem;transition:var(--transition);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-secondary)'">
                    <i class="fas fa-search"></i>
                </a>

                <!-- Wishlist -->
                <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/products/wishlist.php" class="cart-badge" style="text-decoration:none;">
                    <i class="fas fa-heart" style="color:var(--text-secondary);font-size:1rem;"></i>
                    <?php if ($wishlistCount > 0): ?>
                    <span class="badge"><?= $wishlistCount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <!-- Cart -->
                <a href="<?= BASE_URL ?>/cart/index.php" class="cart-badge" style="text-decoration:none;">
                    <i class="fas fa-shopping-bag" style="color:var(--text-secondary);font-size:1.1rem;"></i>
                    <?php if ($cartCount > 0): ?>
                    <span class="badge" id="cartBadge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>

                <!-- Account -->
                <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" style="color:var(--text-secondary);font-size:0.88rem;">
                        <i class="fas fa-user-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/profile.php" style="color:var(--text-secondary);"><i class="fas fa-user me-2" style="color:var(--gold);"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/cart/orders.php" style="color:var(--text-secondary);"><i class="fas fa-box me-2" style="color:var(--gold);"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/products/wishlist.php" style="color:var(--text-secondary);"><i class="fas fa-heart me-2" style="color:var(--gold);"></i>Wishlist</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/payments/history.php" style="color:var(--text-secondary);"><i class="fas fa-credit-card me-2" style="color:var(--gold);"></i>Payments</a></li>
                        <li><hr class="dropdown-divider" style="border-color:var(--dark-border);"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout.php" style="color:var(--danger);"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn-gold btn-sm" style="text-decoration:none;padding:8px 24px;">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar + announcement bar -->
<div style="height: 76px;"></div>

<!-- Flash Messages -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-dark alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert" style="animation:fadeInUp 0.4s ease;">
        <?php if ($flash['type'] === 'success'): ?><i class="fas fa-check-circle me-2"></i>
        <?php elseif ($flash['type'] === 'danger'): ?><i class="fas fa-exclamation-circle me-2"></i>
        <?php elseif ($flash['type'] === 'warning'): ?><i class="fas fa-exclamation-triangle me-2"></i>
        <?php else: ?><i class="fas fa-info-circle me-2"></i>
        <?php endif; ?>
        <?= $flash['message'] ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
