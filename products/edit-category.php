<?php
$pageTitle = 'Edit Category';
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/admin-header.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid category.');
    redirect(BASE_URL . '/products/categories.php');
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlash('danger', 'Category not found.');
    redirect(BASE_URL . '/products/categories.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    // Validation
    if ($name === '') $errors[] = 'Category name is required.';

    // Check slug uniqueness (exclude self)
    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $chk->execute([$slug, $id]);
        if ($chk->fetch()) {
            $slug .= '-' . time();
        }
    }

    // Image upload (optional on edit)
    $imageName = $category['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, WebP, GIF.';
        } else {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'cat-' . $slug . '-' . time() . '.' . $ext;
            $dest      = __DIR__ . '/../uploads/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Failed to upload image.';
                $imageName = $category['image'];
            } else {
                // Delete old image
                if ($category['image']) {
                    $oldPath = __DIR__ . '/../uploads/' . $category['image'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }
        }
    }

    // Update
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, image=?, is_active=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $imageName, $is_active, $id]);

        setFlash('success', 'Category updated successfully!');
        redirect(BASE_URL . '/products/categories.php');
    }

    // Merge POST for re-display
    $category = array_merge($category, $_POST, ['image' => $imageName]);
}
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-edit me-2" style="color:var(--gold);"></i>Edit Category</h2>
        <p class="text-white-50 mb-0">Update: <?= sanitize($category['name']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/products/categories.php" class="btn btn-gold-outline">
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

<div class="card-dark p-4" style="max-width:700px;">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-dark" required
                   value="<?= sanitize($category['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control form-dark" rows="4"><?= sanitize($category['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Category Image</label>
            <input type="file" name="image" class="form-control form-dark" accept="image/*">
            <?php if ($category['image']): ?>
                <div class="mt-2">
                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($category['image']) ?>" alt="Current image"
                         style="width:100px; height:100px; object-fit:cover; border-radius:8px;">
                    <small class="text-white-50 d-block mt-1">Leave empty to keep current image</small>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-check form-switch mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                   <?= $category['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
        <hr style="border-color:var(--dark-border, #333);">
        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>/products/categories.php" class="btn btn-gold-outline">Cancel</a>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Update Category</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
