<?php
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    // For AJAX requests return JSON, for regular requests redirect to login
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in']);
        exit;
    }
    redirect(BASE_URL . '/auth/login.php');
}

$userId = $_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$redirectUrl = $_POST['redirect'] ?? $_GET['redirect'] ?? BASE_URL . '/products/shop.php';

if ($productId <= 0) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    setFlash('danger', 'Invalid product.');
    redirect($redirectUrl);
}

try {
    if (isInWishlist($pdo, $userId, $productId)) {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $action = 'removed';
        $msg = 'Removed from wishlist.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        $action = 'added';
        $msg = 'Added to wishlist!';
    }

    $count = (int)getWishlistCount($pdo, $userId);

    // AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'action' => $action, 'count' => $count]);
        exit;
    }

    // Regular redirect
    setFlash('success', $msg);
    redirect($redirectUrl);
} catch (PDOException $e) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
        exit;
    }
    setFlash('danger', 'An error occurred.');
    redirect($redirectUrl);
}
