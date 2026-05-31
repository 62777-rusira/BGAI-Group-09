<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/products/categories.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid category ID.');
    redirect(BASE_URL . '/products/categories.php');
}

// Fetch category to get image
$stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlash('danger', 'Category not found.');
    redirect(BASE_URL . '/products/categories.php');
}

// Delete category image
if ($category['image']) {
    $imagePath = __DIR__ . '/../uploads/' . $category['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Delete category (products cascade-delete via FK)
$del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$del->execute([$id]);

setFlash('success', 'Category deleted successfully.');
redirect(BASE_URL . '/products/categories.php');
