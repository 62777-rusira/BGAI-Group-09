<?php
// ============================================
// User Profile / Account Page
// ============================================
$pageTitle = 'My Account';
require_once __DIR__ . '/includes/header.php';
requireLogin();

$user = getCurrentUser();
$activeTab = $_GET['tab'] ?? 'dashboard';
$db = db();

// Get user orders
$orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$orders->execute([$_SESSION['user_id']]);
$userOrders = $orders->fetchAll();

// Get wishlist items
$wishlist = $db->prepare("SELECT w.*, p.name, p.slug, p.base_price, p.status,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$wishlist->execute([$_SESSION['user_id']]);
$wishlistItems = $wishlist->fetchAll();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $result = updateProfile($_SESSION['user_id'], $_POST);
        if ($result['success']) {
            redirect(APP_URL . '/profile.php?tab=settings', 'success', $result['message']);
            $user = getCurrentUser();
        }
    }
    if ($_POST['action'] === 'change_password') {
        $result = changePassword($_SESSION['user_id'], $_POST['current_password'], $_POST['new_password']);
        if (!$result['success']) {
            $passwordError = $result['message'];
        } else {
            redirect(APP_URL . '/profile.php?tab=settings', 'success', $result['message']);
        }
    }
}
?>

<section class="profile-section">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo APP_URL; ?>">Home</a> <span>/</span>
            <span class="current">My Account</span>
        </div>

        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <h3 class="profile-name"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p class="profile-email"><?php echo e($user['email']); ?></p>
                <nav class="profile-nav">
                    <a href="?tab=dashboard" class="<?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="?tab=orders" class="<?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> My Orders
                    </a>
                    <a href="?tab=wishlist" class="<?php echo $activeTab === 'wishlist' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i> Wishlist
                    </a>
                    <a href="?tab=settings" class="<?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="<?php echo APP_URL; ?>/api/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <?php if ($activeTab === 'dashboard'): ?>
                    <h2>Dashboard</h2>
                    
                    <!-- Stats -->
                    <div class="stats-grid" style="margin-bottom:var(--space-2xl);">
                        <div class="stat-card">
                            <div class="stat-icon gold"><i class="fas fa-box"></i></div>
                            <div class="stat-info">
                                <h3><?php echo count($userOrders); ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-info">
                                <h3><?php echo count(array_filter($userOrders, fn($o) => $o['order_status'] === 'delivered')); ?></h3>
                                <p>Delivered</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-heart"></i></div>
                            <div class="stat-info">
                                <h3><?php echo count($wishlistItems); ?></h3>
                                <p>Wishlist Items</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <h3 style="font-family:var(--font-heading); margin-bottom:var(--space-lg);">Recent Orders</h3>
                    <?php if (count($userOrders) > 0): ?>
                        <div class="admin-table-container">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($userOrders, 0, 5) as $order): ?>
                                        <tr>
                                            <td><strong><?php echo e($order['order_number']); ?></strong></td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td><span class="order-status status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                            <td><span class="order-status status-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                    <?php endif; ?>

                <?php elseif ($activeTab === 'orders'): ?>
                    <h2>My Orders</h2>
                    <?php if (count($userOrders) > 0): ?>
                        <div class="admin-table-container">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userOrders as $order): 
                                        $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $items->execute([$order['id']]);
                                        $orderItems = $items->fetchAll();
                                    ?>
                                        <tr>
                                            <td><strong><?php echo e($order['order_number']); ?></strong></td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo count($orderItems); ?> item(s)</td>
                                            <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                            <td><span class="order-status status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                            <td><span class="order-status status-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                        </tr>
                                        <?php if ($order['tracking_number']): ?>
                                            <tr>
                                                <td colspan="6" style="background:var(--cream-light); font-size:0.85rem;">
                                                    <i class="fas fa-truck" style="color:var(--gold);"></i>
                                                    Tracking: <strong><?php echo e($order['tracking_number']); ?></strong>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="cart-empty">
                            <i class="fas fa-box-open"></i>
                            <h2>No Orders Yet</h2>
                            <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-primary" style="margin-top:var(--space-xl);">Start Shopping</a>
                        </div>
                    <?php endif; ?>

                <?php elseif ($activeTab === 'wishlist'): ?>
                    <h2>My Wishlist</h2>
                    <?php if (count($wishlistItems) > 0): ?>
                        <div class="products-grid">
                            <?php foreach ($wishlistItems as $item): ?>
                                <div class="product-card">
                                    <div class="product-card-image">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo APP_URL; ?>/<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
                                        <?php else: ?>
                                            <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                                        <?php endif; ?>
                                        <div class="product-quick-actions" style="opacity:1; transform:translateX(-50%) translateY(0);">
                                            <button class="quick-action-btn" onclick="addToCart(<?php echo $item['product_id']; ?>)" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                                            <button class="quick-action-btn" onclick="removeWishlist(<?php echo $item['product_id']; ?>)" title="Remove" style="color:var(--danger);"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-card-body">
                                        <h3 class="product-card-title">
                                            <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($item['slug']); ?>"><?php echo e($item['name']); ?></a>
                                        </h3>
                                        <div class="product-card-price">
                                            <span class="price-current"><?php echo formatPrice($item['base_price']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="cart-empty">
                            <i class="fas fa-heart"></i>
                            <h2>Your Wishlist is Empty</h2>
                            <p class="text-muted">Save your favourite pieces here for later.</p>
                            <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-primary" style="margin-top:var(--space-xl);">Browse Jewellery</a>
                        </div>
                    <?php endif; ?>

                <?php elseif ($activeTab === 'settings'): ?>
                    <h2>Account Settings</h2>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-2xl);">
                        <!-- Profile Settings -->
                        <div>
                            <h3 style="font-family:var(--font-heading); margin-bottom:var(--space-lg);">Personal Information</h3>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="form-row">
                                    <div class="auth-form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" class="form-input" value="<?php echo e($user['first_name']); ?>" required>
                                    </div>
                                    <div class="auth-form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" class="form-input" value="<?php echo e($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="auth-form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-input" value="<?php echo e($user['email']); ?>" disabled>
                                </div>
                                <div class="auth-form-group">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" class="form-input" value="<?php echo e($user['phone']); ?>">
                                </div>
                                <div class="auth-form-group">
                                    <label>Address</label>
                                    <input type="text" name="address_line1" class="form-input" value="<?php echo e($user['address_line1']); ?>" placeholder="Street address">
                                </div>
                                <div class="form-row">
                                    <div class="auth-form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-input" value="<?php echo e($user['city']); ?>">
                                    </div>
                                    <div class="auth-form-group">
                                        <label>State</label>
                                        <input type="text" name="state" class="form-input" value="<?php echo e($user['state']); ?>">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="auth-form-group">
                                        <label>Postal Code</label>
                                        <input type="text" name="postal_code" class="form-input" value="<?php echo e($user['postal_code']); ?>">
                                    </div>
                                    <div class="auth-form-group">
                                        <label>Country</label>
                                        <input type="text" name="country" class="form-input" value="<?php echo e($user['country']); ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2" style="margin-top:var(--space-lg);">Save Changes</button>
                            </form>
                        </div>

                        <!-- Change Password -->
                        <div>
                            <h3 style="font-family:var(--font-heading); margin-bottom:var(--space-lg);">Change Password</h3>
                            <?php if (isset($passwordError)): ?>
                                <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:var(--space-md); border-radius:var(--border-radius); margin-bottom:var(--space-lg); font-size:0.9rem;">
                                    <?php echo e($passwordError); ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="auth-form-group">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" class="form-input" required>
                                </div>
                                <div class="auth-form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" class="form-input" placeholder="Min 6 characters" required>
                                </div>
                                <div class="auth-form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_new_password" class="form-input" placeholder="Re-enter new password" required>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2" style="margin-top:var(--space-lg);">Update Password</button>
                            </form>
                            
                            <!-- Country Preference -->
                            <h3 style="font-family:var(--font-heading); margin-top:var(--space-3xl); margin-bottom:var(--space-lg);">Shopping Preferences</h3>
                            <div class="auth-form-group">
                                <label>Currency / Region</label>
                                <select class="form-input" onchange="changeCountry(this.value.split(',')[0], this.value.split(',')[1])">
                                    <option value="AU,AUD" <?php echo ($user['country_code'] ?? 'AU') === 'AU' ? 'selected' : ''; ?>>🇦🇺 Australia (AUD)</option>
                                    <option value="US,USD" <?php echo ($user['country_code'] ?? '') === 'US' ? 'selected' : ''; ?>>🇺🇸 United States (USD)</option>
                                    <option value="GB,GBP" <?php echo ($user['country_code'] ?? '') === 'GB' ? 'selected' : ''; ?>>🇬🇧 United Kingdom (GBP)</option>
                                    <option value="IN,INR" <?php echo ($user['country_code'] ?? '') === 'IN' ? 'selected' : ''; ?>>🇮🇳 India (INR)</option>
                                    <option value="AE,AED" <?php echo ($user['country_code'] ?? '') === 'AE' ? 'selected' : ''; ?>>🇦🇪 UAE (AED)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function removeWishlist(productId) {
    fetch('<?php echo APP_URL; ?>/api/wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({product_id: productId, action: 'remove'})
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
