<?php
/**
 * Add to Cart (CREATE)
 * POST handler - adds a product to the cart or increments quantity if already present.
 * Requires login. Validates stock availability.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

// Accept both POST and GET requests
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);
$userId = $_SESSION['user_id'];

// Validate inputs
if ($productId <= 0 || $quantity <= 0) {
    setFlash('danger', 'Invalid product or quantity.');
    redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/products/shop.php');
}

// Check product exists and is active
$stmt = $pdo->prepare("SELECT id, name, stock, is_active FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product || !$product['is_active']) {
    setFlash('danger', 'Product not found or is unavailable.');
    redirect(BASE_URL . '/products/shop.php');
}

// Check existing cart quantity
$stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$existing = $stmt->fetch();

$totalQty = $quantity + ($existing ? $existing['quantity'] : 0);

// Check stock availability
if ($totalQty > $product['stock']) {
    setFlash('danger', 'Insufficient stock. Only ' . $product['stock'] . ' available.');
    redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/products/shop.php');
}

if ($existing) {
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$totalQty, $existing['id']]);
} else {
    // Insert new cart item
    $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $productId, $quantity]);
}

setFlash('success', sanitize($product['name']) . ' has been added to your cart.');
redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/cart/index.php');
