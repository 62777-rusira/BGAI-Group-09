<?php
// ============================================
// API - Shopping Cart Operations
// ============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config/app.php';
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server configuration error: ' . $e->getMessage()]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !is_array($input)) {
        $input = $_POST;
    }
    $action = $input['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'add':
            $productId = intval($input['product_id'] ?? 0);
            $quantity = intval($input['quantity'] ?? 1);
            if ($productId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
                exit;
            }
            $result = addToCart($productId, $quantity);
            echo json_encode($result);
            break;
            
        case 'update':
            $cartId = intval($input['cart_id'] ?? 0);
            $quantity = intval($input['quantity'] ?? 1);
            if ($cartId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
                exit;
            }
            $result = updateCartItem($cartId, $quantity);
            echo json_encode($result);
            break;
            
        case 'remove':
            $cartId = intval($input['cart_id'] ?? 0);
            if ($cartId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
                exit;
            }
            $result = removeFromCart($cartId);
            echo json_encode($result);
            break;
            
        case 'count':
            echo json_encode(['count' => getCartCount()]);
            break;
            
        case 'totals':
            echo json_encode(getCartTotals());
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    error_log("Cart API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
