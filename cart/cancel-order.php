<?php
/**
 * Cancel Order (UPDATE/DELETE)
 * POST handler - changes order status to 'Cancelled' and restores product stock.
 * Only allowed if current status is 'Pending'.
 */
require_once __DIR__ . '/../config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/cart/orders.php');
}

$orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
$userId = $_SESSION['user_id'];

if ($orderId <= 0) {
    setFlash('danger', 'Invalid order.');
    redirect(BASE_URL . '/cart/orders.php');
}

// Get order - verify ownership and status
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Order not found.');
    redirect(BASE_URL . '/cart/orders.php');
}

if ($order['status'] !== 'Pending') {
    setFlash('danger', 'Only pending orders can be cancelled.');
    redirect(BASE_URL . '/cart/order-detail.php?id=' . $orderId);
}

try {
    $pdo->beginTransaction();

    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$orderId]);

    // Restore product stock
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();

    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    foreach ($items as $item) {
        $stockStmt->execute([$item['quantity'], $item['product_id']]);
    }

    $pdo->commit();
    setFlash('success', 'Order has been cancelled and stock has been restored.');

} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', 'Failed to cancel order. Please try again.');
}

redirect(BASE_URL . '/cart/order-detail.php?id=' . $orderId);
