<?php
// ============================================
// API - Wishlist Operations
// ============================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config/app.php';
requireLogin();

$db = db();
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = $input['action'] ?? '';
$productId = intval($input['product_id'] ?? 0);

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

switch ($action) {
    case 'add':
        $exists = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $exists->execute([$_SESSION['user_id'], $productId]);
        if ($exists->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Already in wishlist']);
        } else {
            $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $productId]);
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        }
        break;
        
    case 'remove':
        $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $productId]);
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
