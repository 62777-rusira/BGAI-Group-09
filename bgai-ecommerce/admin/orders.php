<?php
// ============================================
// Admin - Order Management
// ============================================
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$db = db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $db->prepare("UPDATE orders SET order_status = ?, payment_status = ?, tracking_number = ?, notes = ? WHERE id = ?")->execute([
            $_POST['order_status'], $_POST['payment_status'], $_POST['tracking_number'] ?? '', $_POST['notes'] ?? '', $_POST['id']
        ]);
        $message = 'Order updated successfully';
    }
}

$orders = $db->query("SELECT o.*, u.first_name as u_first, u.last_name as u_last, u.email as u_email 
    FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();

if (isset($_GET['view'])) {
    $stmt = $db->prepare("SELECT o.*, u.first_name as u_first, u.last_name as u_last, u.email as u_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$_GET['view']]);
    $viewOrder = $stmt->fetch();
    
    $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$viewOrder['id']]);
    $orderItems = $items->fetchAll();
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | BGAI Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <header style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:0 var(--space-xl); height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:200;">
        <div style="display:flex; align-items:center; gap:var(--space-md);">
            <a href="index.php" style="font-family:var(--font-heading); font-size:1.3rem; font-weight:700; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BGAI Admin</a>
            <span style="color:var(--gray-300);">/</span><span style="font-weight:500;">Orders</span>
        </div>
        <a href="<?php echo APP_URL; ?>" target="_blank" style="color:var(--gray-500); font-size:0.9rem;"><i class="fas fa-external-link-alt"></i> View Store</a>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header"><h3>Navigation</h3><p>Administration Panel</p></div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="products.php"><i class="fas fa-gem"></i> Products</a>
                <a href="orders.php" class="active"><i class="fas fa-box"></i> Orders</a>
                <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="pricing.php"><i class="fas fa-dollar-sign"></i> Pricing</a>
                <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            </nav>
        </aside>

        <div class="admin-content">
            <?php if ($flash || $message): ?>
                <div class="flash-message flash-<?php echo ($flash['type'] ?? 'success'); ?>"><?php echo e($flash['message'] ?? $message); ?></div>
            <?php endif; ?>

            <?php if (isset($viewOrder)): ?>
                <!-- Order Detail View -->
                <a href="orders.php" class="btn btn-sm btn-outline-dark" style="margin-bottom:var(--space-xl);"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:var(--space-xl);">
                    <div>
                        <div class="admin-table-container" style="margin-bottom:var(--space-xl);">
                            <div class="admin-table-header">
                                <h2>Order #<?php echo e($viewOrder['order_number']); ?></h2>
                                <span class="order-status status-<?php echo $viewOrder['order_status']; ?>"><?php echo ucfirst($viewOrder['order_status']); ?></span>
                            </div>
                            <div style="padding:var(--space-xl);">
                                <p style="color:var(--gray-500); font-size:0.9rem;">Placed on <?php echo date('d M Y, h:i A', strtotime($viewOrder['created_at'])); ?></p>
                                
                                <h3 style="font-family:var(--font-heading); margin:var(--space-lg) 0 var(--space-md);">Items</h3>
                                <table class="admin-table">
                                    <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td><?php echo e($item['product_name']); ?></td>
                                                <td><?php echo e($item['product_sku']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo formatPrice($item['unit_price']); ?></td>
                                                <td><strong><?php echo formatPrice($item['total_price']); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Update Order Form -->
                        <div class="admin-table-container">
                            <div class="admin-table-header"><h2>Update Order</h2></div>
                            <form method="POST" style="padding:var(--space-xl);">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $viewOrder['id']; ?>">
                                <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-lg);">
                                    <div>
                                        <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Order Status</label>
                                        <select name="order_status" class="form-input">
                                            <?php foreach(['pending','confirmed','processing','shipped','delivered','cancelled','returned'] as $s): ?>
                                                <option value="<?php echo $s; ?>" <?php echo $viewOrder['order_status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Payment Status</label>
                                        <select name="payment_status" class="form-input">
                                            <?php foreach(['pending','processing','paid','failed','refunded'] as $s): ?>
                                                <option value="<?php echo $s; ?>" <?php echo $viewOrder['payment_status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div style="margin-bottom:var(--space-lg);">
                                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Tracking Number</label>
                                    <input type="text" name="tracking_number" class="form-input" value="<?php echo e($viewOrder['tracking_number'] ?? ''); ?>" placeholder="Enter tracking number">
                                </div>
                                <div style="margin-bottom:var(--space-lg);">
                                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Notes</label>
                                    <textarea name="notes" class="form-input" rows="2"><?php echo e($viewOrder['notes'] ?? ''); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Order</button>
                            </form>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div>
                        <div class="admin-table-container">
                            <div class="admin-table-header"><h2>Summary</h2></div>
                            <div style="padding:var(--space-xl);">
                                <div class="cart-summary-row"><span>Subtotal</span><span><?php echo formatPrice($viewOrder['subtotal']); ?></span></div>
                                <div class="cart-summary-row"><span>Tax</span><span><?php echo formatPrice($viewOrder['tax_amount']); ?></span></div>
                                <div class="cart-summary-row"><span>Shipping</span><span><?php echo $viewOrder['shipping_cost'] > 0 ? formatPrice($viewOrder['shipping_cost']) : 'Free'; ?></span></div>
                                <div class="cart-summary-row total"><span>Total</span><span><?php echo formatPrice($viewOrder['total_amount']); ?></span></div>
                                
                                <hr style="margin:var(--space-lg) 0; border-color:var(--gray-200);">
                                <h4 style="margin-bottom:var(--space-md);">Customer</h4>
                                <p style="font-size:0.9rem; font-weight:600;"><?php echo e($viewOrder['first_name'] . ' ' . $viewOrder['last_name']); ?></p>
                                <p style="font-size:0.85rem; color:var(--gray-500);"><?php echo e($viewOrder['email']); ?></p>
                                <?php if ($viewOrder['phone']): ?>
                                    <p style="font-size:0.85rem; color:var(--gray-500); margin-top:4px;"><?php echo e($viewOrder['phone']); ?></p>
                                <?php endif; ?>
                                
                                <hr style="margin:var(--space-lg) 0; border-color:var(--gray-200);">
                                <h4 style="margin-bottom:var(--space-md);">Shipping Address</h4>
                                <p style="font-size:0.85rem; color:var(--gray-500); line-height:1.6;">
                                    <?php echo e($viewOrder['shipping_address_line1']); ?><br>
                                    <?php if ($viewOrder['shipping_address_line2']) echo e($viewOrder['shipping_address_line2']) . '<br>'; ?>
                                    <?php echo e($viewOrder['shipping_city'] . ', ' . $viewOrder['shipping_state'] . ' ' . $viewOrder['shipping_postal_code']); ?><br>
                                    <?php echo e($viewOrder['shipping_country']); ?>
                                </p>
                                
                                <?php if ($viewOrder['payment_method']): ?>
                                    <hr style="margin:var(--space-lg) 0; border-color:var(--gray-200);">
                                    <h4 style="margin-bottom:var(--space-sm);">Payment</h4>
                                    <p style="font-size:0.85rem; color:var(--gray-500);"><?php echo e($viewOrder['payment_method']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Orders List -->
                <div class="admin-page-header">
                    <h1><i class="fas fa-box" style="color:var(--gold);"></i> Order Management</h1>
                    <span style="color:var(--gray-500); font-size:0.9rem;"><?php echo count($orders); ?> total orders</span>
                </div>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): 
                                $itemCount = $db->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                                $itemCount->execute([$o['id']]);
                                $count = $itemCount->fetchColumn();
                            ?>
                                <tr>
                                    <td><strong><?php echo e($o['order_number']); ?></strong></td>
                                    <td>
                                        <?php echo e(($o['first_name'] ?? 'Guest') . ' ' . ($o['last_name'] ?? '')); ?>
                                        <br><small style="color:var(--gray-500);"><?php echo e($o['email']); ?></small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                                    <td><?php echo $count; ?></td>
                                    <td><strong><?php echo formatPrice($o['total_amount']); ?></strong></td>
                                    <td><span class="order-status status-<?php echo $o['payment_status']; ?>"><?php echo ucfirst($o['payment_status']); ?></span></td>
                                    <td><span class="order-status status-<?php echo $o['order_status']; ?>"><?php echo ucfirst($o['order_status']); ?></span></td>
                                    <td><a href="?view=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye"></i> View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
