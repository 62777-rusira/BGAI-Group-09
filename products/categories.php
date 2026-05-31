<?php
$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/admin-header.php';

// Fetch categories with product count
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count
                      FROM categories c
                      LEFT JOIN products p ON p.category_id = c.id
                      GROUP BY c.id
                      ORDER BY c.name");
$categories = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-tags me-2" style="color:var(--gold);"></i>Categories</h2>
        <p class="text-white-50 mb-0"><?= count($categories) ?> total categories</p>
    </div>
    <a href="<?= BASE_URL ?>/products/create-category.php" class="btn btn-gold">
        <i class="fas fa-plus me-1"></i> Add Category
    </a>
</div>

<?php if (empty($categories)): ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-tags fa-3x mb-3" style="color:var(--gold);"></i>
        <h4>No categories yet</h4>
        <p class="text-white-50">Start by adding your first category.</p>
        <a href="<?= BASE_URL ?>/products/create-category.php" class="btn btn-gold">Add Category</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <?php if ($cat['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= sanitize($cat['image']) ?>" alt=""
                                     style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                            <?php else: ?>
                                <div style="width:50px; height:50px; border-radius:8px; background:var(--dark-border, #333); display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-image text-white-50"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= sanitize($cat['name']) ?></strong></td>
                        <td class="text-white-50"><?= sanitize($cat['slug']) ?></td>
                        <td><span class="badge bg-secondary"><?= $cat['product_count'] ?></span></td>
                        <td>
                            <?php if ($cat['is_active']): ?>
                                <span class="status-badge badge bg-success bg-opacity-25 text-success">Active</span>
                            <?php else: ?>
                                <span class="status-badge badge bg-danger bg-opacity-25 text-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/products/edit-category.php?id=<?= $cat['id'] ?>" class="btn btn-gold-outline btn-sm me-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="<?= BASE_URL ?>/products/delete-category.php" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this category? All products in it will also be deleted!');">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
