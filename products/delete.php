<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/products/manage.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid product ID.');
    redirect(BASE_URL . '/products/manage.php');
}

// Fetch product to get image path
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Product not found.');
    redirect(BASE_URL . '/products/manage.php');
}

// Delete product image
$imagePath = __DIR__ . '/../uploads/' . $product['image'];
if ($product['image'] && file_exists($imagePath)) {
    unlink($imagePath);
}

// Delete product
$del = $pdo->prepare("DELETE FROM products WHERE id = ?");
$del->execute([$id]);

setFlash('success', 'Product deleted successfully.');
redirect(BASE_URL . '/products/manage.php');
