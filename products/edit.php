<?php
$pageTitle = 'Edit Product';
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/admin-header.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid product.');
    redirect(BASE_URL . '/products/manage.php');
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Product not found.');
    redirect(BASE_URL . '/products/manage.php');
}

$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $material    = trim($_POST['material'] ?? '');
    $weight      = trim($_POST['weight'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    // Validation
    if ($name === '') $errors[] = 'Product name is required.';
    if ($category_id <= 0) $errors[] = 'Please select a category.';
    if ($price <= 0) $errors[] = 'Price must be greater than zero.';
    if ($stock < 0) $errors[] = 'Stock cannot be negative.';

    // Check slug uniqueness (exclude self)
    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $chk->execute([$slug, $id]);
        if ($chk->fetch()) {
            $slug .= '-' . time();
        }
    }

    // Image upload (optional on edit)
    $imageName = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, WebP, GIF.';
        } else {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = $slug . '-' . time() . '.' . $ext;
            $dest      = __DIR__ . '/../uploads/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Failed to upload image.';
                $imageName = $product['image'];
            } else {
                // Delete old image
                $oldPath = __DIR__ . '/../uploads/' . $product['image'];
                if ($product['image'] && file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
    }

    // Update
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, slug=?, description=?, price_aud=?, stock=?, image=?, material=?, weight=?, is_featured=?, is_active=? WHERE id=?");
        $stmt->execute([$category_id, $name, $slug, $description, $price, $stock, $imageName, $material, $weight, $is_featured, $is_active, $id]);

        setFlash('success', 'Product updated successfully!');
        redirect(BASE_URL . '/products/manage.php');
    }

    // Merge POST values into product for re-display
    $product = array_merge($product, $_POST, ['image' => $imageName]);
}
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-edit me-2" style="color:var(--gold);"></i>Edit Product</h2>
        <p class="text-white-50 mb-0">Update product: <?= sanitize($product['name']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/products/manage.php" class="btn btn-gold-outline">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-dark alert-danger mb-4">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= sanitize($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card-dark p-4">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="row g-4">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-dark" required
                           value="<?= sanitize($product['name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control form-dark" rows="5"><?= sanitize($product['description'] ?? '') ?></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Material</label>
                        <input type="text" name="material" class="form-control form-dark"
                               value="<?= sanitize($product['material'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Weight</label>
                        <input type="text" name="weight" class="form-control form-dark"
                               value="<?= sanitize($product['weight'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select form-dark" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ((int)$product['category_id'] === $cat['id']) ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price (AUD) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control form-dark" step="0.01" min="0.01" required
                           value="<?= sanitize($product['price_aud']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control form-dark" min="0" required
                           value="<?= sanitize($product['stock']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control form-dark" accept="image/*">
                    <div class="mt-2">
                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($product['image']) ?>" alt="Current image"
                             style="width:100px; height:100px; object-fit:cover; border-radius:8px;">
                        <small class="text-white-50 d-block mt-1">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured"
                           <?= $product['is_featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isFeatured">Featured Product</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                           <?= $product['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isActive">Active</label>
                </div>
            </div>
        </div>
        <hr style="border-color:var(--dark-border, #333);">
        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>/products/manage.php" class="btn btn-gold-outline">Cancel</a>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Update Product</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
