<?php
// ============================================
// Admin - Customer Management
// ============================================
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$db = db();
$customers = $db->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count, (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent FROM users u WHERE u.role = 'customer' ORDER BY u.created_at DESC")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | BGAI Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <header style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:0 var(--space-xl); height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:200;">
        <div style="display:flex; align-items:center; gap:var(--space-md);">
            <a href="index.php" style="font-family:var(--font-heading); font-size:1.3rem; font-weight:700; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BGAI Admin</a>
            <span style="color:var(--gray-300);">/</span><span style="font-weight:500;">Customers</span>
        </div>
        <a href="<?php echo APP_URL; ?>" target="_blank" style="color:var(--gray-500); font-size:0.9rem;"><i class="fas fa-external-link-alt"></i> View Store</a>
    </header>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header"><h3>Navigation</h3><p>Administration Panel</p></div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="products.php"><i class="fas fa-gem"></i> Products</a>
                <a href="orders.php"><i class="fas fa-box"></i> Orders</a>
                <a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a>
                <a href="pricing.php"><i class="fas fa-dollar-sign"></i> Pricing</a>
                <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            </nav>
        </aside>
        <div class="admin-content">
            <?php if ($flash): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>"><?php echo e($flash['message']); ?></div>
            <?php endif; ?>
            <div class="admin-page-header">
                <h1><i class="fas fa-users" style="color:var(--gold);"></i> Customer Management</h1>
                <span style="color:var(--gray-500); font-size:0.9rem;"><?php echo count($customers); ?> registered customers</span>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr><th>Customer</th><th>Location</th><th>Orders</th><th>Total Spent</th><th>Status</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <div style="width:40px;height:40px;border-radius:50%;background:var(--gold-gradient);display:flex;align-items:center;justify-content:center;color:var(--white);font-weight:700;font-size:0.8rem;flex-shrink:0;">
                                            <?php echo strtoupper(substr($c['first_name'],0,1).substr($c['last_name'],0,1)); ?>
                                        </div>
                                        <div>
                                            <strong style="font-size:0.85rem;"><?php echo e($c['first_name'] . ' ' . $c['last_name']); ?></strong><br>
                                            <small style="color:var(--gray-500);"><?php echo e($c['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:0.85rem;"><?php echo e($c['city'] . ', ' . $c['country']); ?></td>
                                <td><?php echo $c['order_count']; ?></td>
                                <td><strong><?php echo formatPrice($c['total_spent']); ?></strong></td>
                                <td><span class="order-status status-<?php echo $c['status'] === 'active' ? 'delivered' : 'cancelled'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
                                <td style="font-size:0.85rem;"><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
