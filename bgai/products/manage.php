<?php
$pageTitle = 'Manage Products';
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/admin-header.php';

// Fetch all products with category
$stmt = $pdo->query("SELECT p.*, c.name AS category_name
                      FROM products p
                      JOIN categories c ON p.category_id = c.id
                      ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-gem me-2" style="color:var(--gold);"></i>Products</h2>
        <p class="text-white-50 mb-0"><?= count($products) ?> total products</p>
    </div>
    <a href="<?= BASE_URL ?>/products/create.php" class="btn btn-gold">
        <i class="fas fa-plus me-1"></i> Add Product
    </a>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-gem fa-3x mb-3" style="color:var(--gold);"></i>
        <h4>No products yet</h4>
        <p class="text-white-50">Start by adding your first product.</p>
        <a href="<?= BASE_URL ?>/products/create.php" class="btn btn-gold">Add Product</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <img src="<?= BASE_URL ?>/uploads/<?= sanitize($p['image']) ?>" alt=""
                                 style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                        </td>
                        <td><?= sanitize($p['name']) ?></td>
                        <td><span class="category-badge"><?= sanitize($p['category_name']) ?></span></td>
                        <td><?= formatPrice($p['price_aud']) ?></td>
                        <td>
                            <?php if ($p['stock'] > 0): ?>
                                <span class="text-success"><?= $p['stock'] ?></span>
                            <?php else: ?>
                                <span class="text-danger">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['is_active']): ?>
                                <span class="status-badge badge bg-success bg-opacity-25 text-success">Active</span>
                            <?php else: ?>
                                <span class="status-badge badge bg-danger bg-opacity-25 text-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['is_featured']): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-white-50"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-gold-outline btn-sm me-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="<?= BASE_URL ?>/products/delete.php" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-danger-soft btn-sm" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
