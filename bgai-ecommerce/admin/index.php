<?php
// ============================================
// Admin Dashboard
// ============================================
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$stats = getOrderStats();
$activePage = 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | BGAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Admin Top Bar -->
    <header style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:0 var(--space-xl); height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:var(--z-sticky);">
        <div style="display:flex; align-items:center; gap:var(--space-md);">
            <a href="<?php echo APP_URL; ?>/admin/" style="font-family:var(--font-heading); font-size:1.3rem; font-weight:700; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BGAI Admin</a>
        </div>
        <div style="display:flex; align-items:center; gap:var(--space-lg);">
            <a href="<?php echo APP_URL; ?>" style="color:var(--gray-500); font-size:0.9rem;" target="_blank"><i class="fas fa-external-link-alt"></i> View Store</a>
            <span style="color:var(--gray-300);">|</span>
            <span style="font-size:0.9rem; color:var(--gray-600);"><i class="fas fa-user" style="color:var(--gold); margin-right:4px;"></i> <?php echo e($_SESSION['user_name'] ?? 'Admin'); ?></span>
            <a href="<?php echo APP_URL; ?>/api/logout.php" style="color:var(--gray-500); font-size:0.9rem;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h3>Navigation</h3>
                <p>Administration Panel</p>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="products.php" class="<?php echo $activePage === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-gem"></i> Products
                </a>
                <a href="orders.php" class="<?php echo $activePage === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Orders
                </a>
                <a href="customers.php" class="<?php echo $activePage === 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Customers
                </a>
                <a href="pricing.php" class="<?php echo $activePage === 'pricing' ? 'active' : ''; ?>">
                    <i class="fas fa-dollar-sign"></i> Pricing
                </a>
                <a href="messages.php" class="<?php echo $activePage === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                <a href="../" style="margin-top:var(--space-xl); border-top:1px solid var(--dark-border); padding-top:var(--space-lg);">
                    <i class="fas fa-arrow-left"></i> Back to Store
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="admin-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon gold"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_customers']; ?></h3>
                        <p>Customers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-gem"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Active Products</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['monthly_revenue']); ?></h3>
                        <p>Monthly Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2><i class="fas fa-clock" style="color:var(--gold); margin-right:var(--space-sm);"></i> Recent Orders</h2>
                    <a href="orders.php" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_orders'] as $order): ?>
                            <tr>
                                <td><strong><?php echo e($order['order_number']); ?></strong></td>
                                <td><?php echo e(($order['first_name'] ?? 'Guest') . ' ' . ($order['last_name'] ?? '')); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                <td><span class="order-status status-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                <td><span class="order-status status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stats['recent_orders'])): ?>
                            <tr><td colspan="6" style="text-align:center; padding:var(--space-xl); color:var(--gray-500);">No orders yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Products & Low Stock -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-xl);">
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2><i class="fas fa-trophy" style="color:var(--gold); margin-right:var(--space-sm);"></i> Top Products</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Product</th><th>Sold</th><th>Revenue</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['top_products'] ?? [] as $tp): ?>
                                <tr>
                                    <td><?php echo e(truncateText($tp['product_name'], 30)); ?></td>
                                    <td><?php echo $tp['total_sold']; ?></td>
                                    <td><strong><?php echo formatPrice($tp['total_revenue']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2><i class="fas fa-battery-quarter" style="color:var(--danger); margin-right:var(--space-sm);"></i> Low Stock Alert</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Product</th><th>Stock</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $db = db();
                            $lowStock = $db->query("SELECT name, sku, stock_quantity, low_stock_threshold FROM products WHERE stock_quantity <= low_stock_threshold AND status = 'active' ORDER BY stock_quantity ASC LIMIT 5")->fetchAll();
                            foreach ($lowStock as $ls): ?>
                                <tr>
                                    <td><?php echo e(truncateText($ls['name'], 25)); ?></td>
                                    <td><strong style="color:<?php echo $ls['stock_quantity'] === 0 ? 'var(--danger)' : 'var(--warning)'; ?>"><?php echo $ls['stock_quantity']; ?></strong></td>
                                    <td><span class="order-status <?php echo $ls['stock_quantity'] === 0 ? 'status-cancelled' : 'status-pending'; ?>"><?php echo $ls['stock_quantity'] === 0 ? 'Out of Stock' : 'Low Stock'; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($lowStock)): ?>
                                <tr><td colspan="3" style="text-align:center; padding:var(--space-xl); color:var(--gray-500);">All products well stocked</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
