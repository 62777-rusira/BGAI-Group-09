<?php
// ============================================
// Admin - Pricing Management
// ============================================
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$db = db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_pricing') {
            $stmt = $db->prepare("UPDATE country_pricing SET price = ? WHERE id = ?");
            $stmt->execute([$_POST['price'], $_POST['pricing_id']]);
            $message = 'Pricing updated successfully';
        }
        if ($_POST['action'] === 'add_pricing') {
            $stmt = $db->prepare("INSERT INTO country_pricing (product_id, country_code, currency_code, price, sale_price, tax_rate, shipping_cost) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([
                $_POST['product_id'], $_POST['country_code'], $_POST['currency_code'], $_POST['price'],
                !empty($_POST['sale_price']) ? $_POST['sale_price'] : null,
                $_POST['tax_rate'] ?? 10, $_POST['shipping_cost'] ?? 0
            ]);
            $message = 'Pricing added successfully';
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'unique_product_country') !== false) {
            $message = 'Pricing for this product and country already exists.';
        } else {
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}

$products = $db->query("SELECT id, name, sku FROM products WHERE status = 'active' ORDER BY name")->fetchAll();
$countries = ['AU'=>'Australia','US'=>'United States','GB'=>'United Kingdom','IN'=>'India','AE'=>'UAE','CA'=>'Canada','SG'=>'Singapore','JP'=>'Japan','NZ'=>'New Zealand','DE'=>'Germany'];
$countryCodes = ['AU'=>'AUD','US'=>'USD','GB'=>'GBP','IN'=>'INR','AE'=>'AED','CA'=>'CAD','SG'=>'SGD','JP'=>'JPY','NZ'=>'NZD','DE'=>'EUR'];

// Get all pricing data
$pricingData = $db->query("SELECT cp.*, p.name as product_name, p.sku FROM country_pricing cp JOIN products p ON cp.product_id = p.id ORDER BY p.name, cp.country_code")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing | BGAI Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <header style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:0 var(--space-xl); height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:200;">
        <div style="display:flex; align-items:center; gap:var(--space-md);">
            <a href="index.php" style="font-family:var(--font-heading); font-size:1.3rem; font-weight:700; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BGAI Admin</a>
            <span style="color:var(--gray-300);">/</span><span style="font-weight:500;">Pricing</span>
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
                <a href="pricing.php" class="active"><i class="fas fa-dollar-sign"></i> Pricing</a>
                <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            </nav>
        </aside>
        <div class="admin-content">
            <?php if ($flash || $message): ?>
                <?php $msgType = isset($flash) ? $flash['type'] : (strpos($message, 'error') !== false || strpos($message, 'already exists') !== false ? 'error' : 'success'); ?>
                <div class="flash-message flash-<?php echo $msgType; ?>"><?php echo e($flash['message'] ?? $message); ?></div>
            <?php endif; ?>

            <div class="admin-page-header">
                <h1><i class="fas fa-dollar-sign" style="color:var(--gold);"></i> Country-Based Pricing</h1>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('addForm').style.display = document.getElementById('addForm').style.display === 'none' ? 'block' : 'none';">
                    <i class="fas fa-plus"></i> Add Pricing
                </button>
            </div>

            <!-- Add Pricing Form -->
            <div class="admin-table-container" id="addForm" style="display:none; margin-bottom:var(--space-2xl);">
                <div class="admin-table-header"><h2>Add New Pricing</h2></div>
                <form method="POST" style="padding:var(--space-xl);">
                    <input type="hidden" name="action" value="add_pricing">
                    <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto; gap:var(--space-md); margin-bottom:var(--space-lg);">
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Product</label>
                            <select name="product_id" class="form-input" style="font-size:0.85rem;">
                                <?php foreach ($products as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo e($p['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Country</label>
                            <select name="country_code" class="form-input" style="font-size:0.85rem;" onchange="document.getElementById('currCode').value = this.options[this.selectedIndex].dataset.curr;">
                                <?php foreach ($countries as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" data-curr="<?php echo $countryCodes[$code]; ?>"><?php echo $code; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Currency</label>
                            <input type="text" name="currency_code" id="currCode" class="form-input" style="font-size:0.85rem;" value="AUD" readonly>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Price</label>
                            <input type="number" step="0.01" name="price" class="form-input" style="font-size:0.85rem;" required>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Sale Price</label>
                            <input type="number" step="0.01" name="sale_price" class="form-input" style="font-size:0.85rem;">
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Tax %</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-input" style="font-size:0.85rem;" value="10">
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">Shipping</label>
                            <input type="number" step="0.01" name="shipping_cost" class="form-input" style="font-size:0.85rem;" value="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add</button>
                </form>
            </div>

            <!-- Pricing Table -->
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2>All Pricing Rules (<?php echo count($pricingData); ?>)</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>Product</th><th>Country</th><th>Price</th><th>Sale</th><th>Tax %</th><th>Shipping</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pricingData as $pd): ?>
                            <tr>
                                <td><strong style="font-size:0.85rem;"><?php echo e(truncateText($pd['product_name'], 25)); ?></strong></td>
                                <td><?php echo $pd['country_code']; ?> (<?php echo $pd['currency_code']; ?>)</td>
                                <td><?php echo formatPrice($pd['price'], $pd['currency_code']); ?></td>
                                <td><?php echo $pd['sale_price'] ? formatPrice($pd['sale_price'], $pd['currency_code']) : '—'; ?></td>
                                <td><?php echo $pd['tax_rate']; ?>%</td>
                                <td><?php echo $pd['shipping_cost'] > 0 ? formatPrice($pd['shipping_cost'], $pd['currency_code']) : 'Free'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline-flex; gap:4px;">
                                        <input type="hidden" name="action" value="update_pricing">
                                        <input type="hidden" name="pricing_id" value="<?php echo $pd['id']; ?>">
                                        <input type="number" step="0.01" name="price" value="<?php echo $pd['price']; ?>" style="width:80px; padding:4px 8px; border:1px solid var(--gray-200); border-radius:4px; font-size:0.8rem;">
                                        <button type="submit" class="btn btn-sm btn-primary" style="padding:4px 8px;"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
