<?php
// ============================================
// Admin - Product Management
// ============================================
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$db = db();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $id = $_POST['id'] ?? null;
            $data = [
                $_POST['name'], generateSlug($_POST['name']), $_POST['description'], $_POST['short_description'],
                $_POST['sku'], $_POST['category_id'] ?: null, $_POST['collection_id'] ?: null,
                $_POST['base_price'], $_POST['sale_price'] ?: null,
                $_POST['weight_grams'] ?: null, $_POST['material'] ?: null, $_POST['gemstone'] ?: null,
                $_POST['metal_type'] ?: 'Gold', $_POST['metal_purity'] ?: null,
                $_POST['dimensions'] ?: null, $_POST['stock_quantity'] ?: 0,
                $_POST['is_featured'] ?? 0, $_POST['is_bestseller'] ?? 0, $_POST['is_new_arrival'] ?? 0,
                $_POST['status'] ?? 'draft'
            ];
            
            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE products SET name=?, slug=?, description=?, short_description=?, sku=?, category_id=?, collection_id=?, base_price=?, sale_price=?, weight_grams=?, material=?, gemstone=?, metal_type=?, metal_purity=?, dimensions=?, stock_quantity=?, is_featured=?, is_bestseller=?, is_new_arrival=?, status=? WHERE id=?");
                $data[] = $id;
                $stmt->execute($data);
                $message = 'Product updated successfully';
            } else {
                $stmt = $db->prepare("INSERT INTO products (name, slug, description, short_description, sku, category_id, collection_id, base_price, sale_price, weight_grams, material, gemstone, metal_type, metal_purity, dimensions, stock_quantity, is_featured, is_bestseller, is_new_arrival, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute($data);
                $message = 'Product created successfully';
            }
        }
        
        if ($action === 'delete') {
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$_POST['id']]);
            $message = 'Product deleted successfully';
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
    }
}

// Get products
$products = $db->query("SELECT p.*, c.name as category_name, col.name as collection_name,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN collections col ON p.collection_id = col.id
    ORDER BY p.created_at DESC")->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$collections = $db->query("SELECT * FROM collections ORDER BY name")->fetchAll();
$editProduct = null;

if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch();
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | BGAI Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <header style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:0 var(--space-xl); height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:200;">
        <div style="display:flex; align-items:center; gap:var(--space-md);">
            <a href="index.php" style="font-family:var(--font-heading); font-size:1.3rem; font-weight:700; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">BGAI Admin</a>
            <span style="color:var(--gray-300);">/</span>
            <span style="font-weight:500;">Products</span>
        </div>
        <a href="<?php echo APP_URL; ?>" target="_blank" style="color:var(--gray-500); font-size:0.9rem;"><i class="fas fa-external-link-alt"></i> View Store</a>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header"><h3>Navigation</h3><p>Administration Panel</p></div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="products.php" class="active"><i class="fas fa-gem"></i> Products</a>
                <a href="orders.php"><i class="fas fa-box"></i> Orders</a>
                <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="pricing.php"><i class="fas fa-dollar-sign"></i> Pricing</a>
                <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            </nav>
        </aside>

        <div class="admin-content">
            <?php if ($flash || $message): ?>
                <?php $msgType = isset($flash) ? $flash['type'] : (strpos($message, 'error') !== false || strpos($message, 'Database') !== false ? 'error' : 'success'); ?>
                <div class="flash-message flash-<?php echo $msgType; ?>">
                    <?php echo e($flash['message'] ?? $message); ?>
                </div>
            <?php endif; ?>

            <div class="admin-page-header">
                <h1><i class="fas fa-gem" style="color:var(--gold);"></i> Product Management</h1>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('productForm').style.display = document.getElementById('productForm').style.display === 'none' ? 'block' : 'none'; document.getElementById('productForm').scrollIntoView({behavior:'smooth'});">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>

            <!-- Add/Edit Form -->
            <div class="admin-table-container" id="productForm" style="display:<?php echo $editProduct ? 'block' : 'none'; ?>; margin-bottom:var(--space-2xl);">
                <div class="admin-table-header">
                    <h2><?php echo $editProduct ? 'Edit' : 'Add New'; ?> Product</h2>
                    <button class="btn btn-sm btn-outline-dark" onclick="this.closest('.admin-table-container').style.display='none'">Cancel</button>
                </div>
                <form method="POST" style="padding:var(--space-xl);">
                    <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                    <?php if ($editProduct): ?><input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>"><?php endif; ?>
                    
                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-lg);">
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Product Name *</label>
                            <input type="text" name="name" class="form-input" value="<?php echo e($editProduct['name'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">SKU *</label>
                            <input type="text" name="sku" class="form-input" value="<?php echo e($editProduct['sku'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div style="margin-bottom:var(--space-lg);">
                        <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Short Description</label>
                        <input type="text" name="short_description" class="form-input" value="<?php echo e($editProduct['short_description'] ?? ''); ?>">
                    </div>
                    
                    <div style="margin-bottom:var(--space-lg);">
                        <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Full Description</label>
                        <textarea name="description" class="form-input" rows="4"><?php echo e($editProduct['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-lg);">
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Base Price (AUD) *</label>
                            <input type="number" step="0.01" name="base_price" class="form-input" value="<?php echo e($editProduct['base_price'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Sale Price (AUD)</label>
                            <input type="number" step="0.01" name="sale_price" class="form-input" value="<?php echo e($editProduct['sale_price'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Stock Quantity *</label>
                            <input type="number" name="stock_quantity" class="form-input" value="<?php echo e($editProduct['stock_quantity'] ?? 0); ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-lg);">
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Category</label>
                            <select name="category_id" class="form-input">
                                <option value="">-- None --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Collection</label>
                            <select name="collection_id" class="form-input">
                                <option value="">-- None --</option>
                                <?php foreach ($collections as $col): ?>
                                    <option value="<?php echo $col['id']; ?>" <?php echo ($editProduct['collection_id'] ?? '') == $col['id'] ? 'selected' : ''; ?>><?php echo e($col['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Status</label>
                            <select name="status" class="form-input">
                                <option value="active" <?php echo ($editProduct['status'] ?? 'draft') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="draft" <?php echo ($editProduct['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="inactive" <?php echo ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-lg);">
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Metal Type</label>
                            <select name="metal_type" class="form-input">
                                <option <?php echo ($editProduct['metal_type'] ?? 'Gold') === 'Gold' ? 'selected' : ''; ?>>Gold</option>
                                <option <?php echo ($editProduct['metal_type'] ?? '') === 'White Gold' ? 'selected' : ''; ?>>White Gold</option>
                                <option <?php echo ($editProduct['metal_type'] ?? '') === 'Rose Gold' ? 'selected' : ''; ?>>Rose Gold</option>
                                <option <?php echo ($editProduct['metal_type'] ?? '') === 'Silver' ? 'selected' : ''; ?>>Silver</option>
                                <option <?php echo ($editProduct['metal_type'] ?? '') === 'Platinum' ? 'selected' : ''; ?>>Platinum</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Metal Purity</label>
                            <input type="text" name="metal_purity" class="form-input" value="<?php echo e($editProduct['metal_purity'] ?? ''); ?>" placeholder="e.g. 18K">
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Gemstone</label>
                            <input type="text" name="gemstone" class="form-input" value="<?php echo e($editProduct['gemstone'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px;">Weight (grams)</label>
                            <input type="number" step="0.01" name="weight_grams" class="form-input" value="<?php echo e($editProduct['weight_grams'] ?? ''); ?>">
                        </div>
                    </div>

                    <div style="display:flex; gap:var(--space-lg); margin-bottom:var(--space-xl); flex-wrap:wrap;">
                        <label class="form-check"><input type="checkbox" name="is_featured" value="1" <?php echo ($editProduct['is_featured'] ?? 0) ? 'checked' : ''; ?>> Featured</label>
                        <label class="form-check"><input type="checkbox" name="is_bestseller" value="1" <?php echo ($editProduct['is_bestseller'] ?? 0) ? 'checked' : ''; ?>> Bestseller</label>
                        <label class="form-check"><input type="checkbox" name="is_new_arrival" value="1" <?php echo ($editProduct['is_new_arrival'] ?? 0) ? 'checked' : ''; ?>> New Arrival</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $editProduct ? 'Update' : 'Create'; ?> Product
                    </button>
                </form>
            </div>

            <!-- Products Table -->
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2>All Products (<?php echo count($products); ?>)</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price (AUD)</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <div class="product-thumb">
                                            <?php if ($p['primary_image']): ?>
                                                <img src="<?php echo APP_URL; ?>/<?php echo e($p['primary_image']); ?>" alt="">
                                            <?php else: ?>
                                                <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong style="font-size:0.85rem;"><?php echo e(truncateText($p['name'], 30)); ?></strong>
                                            <br><small style="color:var(--gray-500);"><?php echo e($p['sku']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($p['category_name'] ?? '—'); ?></td>
                                <td>
                                    <strong><?php echo formatPrice($p['base_price']); ?></strong>
                                    <?php if ($p['sale_price']): ?>
                                        <br><small style="color:var(--danger); text-decoration:line-through;"><?php echo formatPrice($p['sale_price']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="color:<?php echo $p['stock_quantity'] <= 5 ? 'var(--danger)' : 'var(--success)'; ?>; font-weight:600;">
                                        <?php echo $p['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td><span class="order-status status-<?php echo $p['status'] === 'active' ? 'delivered' : ($p['status'] === 'draft' ? 'pending' : 'cancelled'); ?>"><?php echo ucfirst($p['status']); ?></span></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="?edit=<?php echo $p['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($p['slug']); ?>" title="View" target="_blank"><i class="fas fa-eye"></i></a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" style="color:var(--danger);" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
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
