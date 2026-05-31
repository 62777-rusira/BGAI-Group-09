<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/products/shop.php'); }

$userId = $_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$title = trim($_POST['title'] ?? '');
$comment = trim($_POST['comment'] ?? '');

if ($productId <= 0) { setFlash('danger', 'Invalid product.'); redirect(BASE_URL . '/products/shop.php'); }
if ($rating < 1 || $rating > 5) { setFlash('danger', 'Rating must be between 1 and 5.'); redirect(BASE_URL . '/products/detail.php?id=' . $productId); }

$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$productId]);
if (!$stmt->fetch()) { setFlash('danger', 'Product not found.'); redirect(BASE_URL . '/products/shop.php'); }

$stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
$stmt->execute([$productId, $userId]);
if ($stmt->fetch()) { setFlash('warning', 'You have already reviewed this product.'); redirect(BASE_URL . '/products/detail.php?id=' . $productId); }

try {
    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$productId, $userId, $rating, $title, $comment]);
    setFlash('success', 'Thank you for your review!');
} catch (PDOException $e) {
    setFlash('danger', 'Error submitting review.');
}
redirect(BASE_URL . '/products/detail.php?id=' . $productId);
