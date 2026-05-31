<?php
// ============================================
// Admin - Contact Messages
// ============================================
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$db = db();

if (isset($_GET['read'])) {
    $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$_GET['read']]);
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$unreadCount = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | BGAI Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <header style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:0 var(--space-xl); height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:200;">
        <div style="display:flex; align-items:center; gap:var(--space-md);">
            <a href="index.php" style="font-family:var(--font-heading); font-size:1.3rem; font-weight:700; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BGAI Admin</a>
            <span style="color:var(--gray-300);">/</span><span style="font-weight:500;">Messages</span>
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
                <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="pricing.php"><i class="fas fa-dollar-sign"></i> Pricing</a>
                <a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages <?php if ($unreadCount > 0): ?><span style="background:var(--danger); color:var(--white); border-radius:50%; padding:1px 6px; font-size:0.7rem; margin-left:4px;"><?php echo $unreadCount; ?></span><?php endif; ?></a>
            </nav>
        </aside>
        <div class="admin-content">
            <div class="admin-page-header">
                <h1><i class="fas fa-envelope" style="color:var(--gold);"></i> Contact Messages</h1>
                <span style="color:var(--gray-500); font-size:0.9rem;"><?php echo $unreadCount; ?> unread</span>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr><th>From</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $m): ?>
                            <tr style="<?php echo !$m['is_read'] ? 'background:var(--cream-light);' : ''; ?>">
                                <td>
                                    <strong style="font-size:0.85rem;"><?php echo e($m['name']); ?></strong><br>
                                    <small style="color:var(--gray-500);"><?php echo e($m['email']); ?></small>
                                </td>
                                <td style="font-size:0.85rem;"><?php echo e($m['subject'] ?: 'No Subject'); ?></td>
                                <td style="font-size:0.85rem; max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo e($m['message']); ?></td>
                                <td style="font-size:0.85rem;"><?php echo date('d M Y', strtotime($m['created_at'])); ?></td>
                                <td>
                                    <?php if (!$m['is_read']): ?>
                                        <a href="?read=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary" style="font-size:0.75rem;">Mark Read</a>
                                    <?php else: ?>
                                        <span style="color:var(--success); font-size:0.8rem;"><i class="fas fa-check"></i> Read</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:var(--space-xl); color:var(--gray-500);">No messages yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
