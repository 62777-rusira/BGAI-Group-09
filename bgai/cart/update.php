<?php
/**
 * Update Cart Item (UPDATE)
 * POST handler - updates cart item quantity. Validates stock.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/cart/index.php');
}

$cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
$userId = $_SESSION['user_id'];

if ($cartItemId <= 0 || $quantity <= 0) {
    setFlash('danger', 'Invalid quantity.');
    redirect(BASE_URL . '/cart/index.php');
}

// Verify the cart item belongs to this user and get product info
$stmt = $pdo->prepare("
    SELECT ci.id, ci.product_id, p.stock, p.name
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.id = ? AND ci.user_id = ?
");
$stmt->execute([$cartItemId, $userId]);
$cartItem = $stmt->fetch();

if (!$cartItem) {
    setFlash('danger', 'Cart item not found.');
    redirect(BASE_URL . '/cart/index.php');
}

// Check stock
if ($quantity > $cartItem['stock']) {
    setFlash('danger', 'Insufficient stock for ' . sanitize($cartItem['name']) . '. Only ' . $cartItem['stock'] . ' available.');
    redirect(BASE_URL . '/cart/index.php');
}

// Update quantity
$stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
$stmt->execute([$quantity, $cartItemId]);

setFlash('success', 'Cart updated successfully.');
redirect(BASE_URL . '/cart/index.php');
