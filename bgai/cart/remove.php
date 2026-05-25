<?php
/**
 * Remove Cart Item (DELETE)
 * POST/GET handler - removes an item from the cart.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

$cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id']
            : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
$userId = $_SESSION['user_id'];

if ($cartItemId <= 0) {
    setFlash('danger', 'Invalid cart item.');
    redirect(BASE_URL . '/cart/index.php');
}

// Verify ownership and delete
$stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
$stmt->execute([$cartItemId, $userId]);

if ($stmt->rowCount() > 0) {
    setFlash('success', 'Item removed from cart.');
} else {
    setFlash('danger', 'Cart item not found.');
}

redirect(BASE_URL . '/cart/index.php');
