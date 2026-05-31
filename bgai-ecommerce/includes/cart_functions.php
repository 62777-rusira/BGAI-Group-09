<?php
// ============================================
// BGAI Cart Functions
// ============================================

/**
 * Get cart items for current user/session
 */
function getCartItems() {
    $db = db();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    if ($userId) {
        $stmt = $db->prepare("SELECT c.*, p.name, p.slug, p.base_price, p.status,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.status = 'active'");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT c.*, p.name, p.slug, p.base_price, p.status,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ? AND p.status = 'active'");
        $stmt->execute([$sessionId]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Add item to cart
 */
function addToCart($productId, $quantity = 1) {
    $db = db();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    // Check product exists and is active
    $product = getProduct($productId);
    if (!$product || $product['status'] !== 'active') {
        return ['success' => false, 'message' => 'Product not available'];
    }
    
    if ($product['stock_quantity'] < $quantity) {
        return ['success' => false, 'message' => 'Insufficient stock'];
    }
    
    // Check if already in cart
    if ($userId) {
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    } else {
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
    }
    
    $existing = $stmt->fetch();
    
    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['stock_quantity']) {
            return ['success' => false, 'message' => 'Insufficient stock for this quantity'];
        }
        $update = $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$newQty, $existing['id']]);
    } else {
        $insert = $db->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $insert->execute([$userId, $sessionId, $productId, $quantity]);
    }
    
    return ['success' => true, 'message' => 'Added to cart', 'cart_count' => getCartCount()];
}

/**
 * Update cart item quantity
 */
function updateCartItem($cartId, $quantity) {
    $db = db();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    if ($quantity <= 0) {
        return removeFromCart($cartId);
    }
    
    if ($userId) {
        $stmt = $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cartId, $userId]);
    } else {
        $stmt = $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ? AND session_id = ?");
        $stmt->execute([$quantity, $cartId, $sessionId]);
    }
    
    return ['success' => true, 'message' => 'Cart updated', 'cart_count' => getCartCount()];
}

/**
 * Remove item from cart
 */
function removeFromCart($cartId) {
    $db = db();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    if ($userId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartId, $userId]);
    } else {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
        $stmt->execute([$cartId, $sessionId]);
    }
    
    return ['success' => true, 'message' => 'Item removed', 'cart_count' => getCartCount()];
}

/**
 * Get cart item count
 */
function getCartCount() {
    $db = db();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    if ($userId) {
        $stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
    
    return intval($stmt->fetchColumn());
}

/**
 * Get cart totals
 */
function getCartTotals() {
    $items = getCartItems();
    $countryCode = $_SESSION['country_code'] ?? DEFAULT_COUNTRY;
    $subtotal = 0;
    $taxRate = 0;
    $shippingCost = 0;
    $itemDetails = [];
    
    $taxRates = json_decode(TAX_RATES, true);
    $taxRate = floatval($taxRates[$countryCode] ?? 0);
    
    foreach ($items as $item) {
        $pricing = getProductPrice($item['product_id'], $countryCode);
        if ($pricing) {
            $effectivePrice = getEffectivePrice($pricing);
            $lineTotal = $effectivePrice * $item['quantity'];
            $subtotal += $lineTotal;
            $shippingCost = floatval($pricing['shipping_cost'] ?? 0);
            $itemDetails[] = array_merge($item, [
                'unit_price' => $effectivePrice,
                'line_total' => $lineTotal,
                'currency' => $pricing['currency_code']
            ]);
        }
    }
    
    $taxAmount = $subtotal * ($taxRate / 100);
    
    // Free shipping for orders above threshold
    $freeThreshold = floatval(getSetting('shipping_free_threshold', FREE_SHIPPING_THRESHOLD));
    if ($subtotal >= $freeThreshold) {
        $shippingCost = 0;
    }
    
    $total = $subtotal + $taxAmount + $shippingCost;
    
    return [
        'items' => $itemDetails,
        'subtotal' => $subtotal,
        'tax_rate' => $taxRate,
        'tax_amount' => $taxAmount,
        'shipping_cost' => $shippingCost,
        'total' => $total,
        'item_count' => count($items),
        'currency' => $_SESSION['currency_code'] ?? DEFAULT_CURRENCY,
        'free_shipping_threshold' => $freeThreshold
    ];
}

/**
 * Clear cart
 */
function clearCart() {
    $db = db();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    if ($userId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
    
    // Merge session cart to user cart on login
    if ($userId && $sessionId) {
        $db->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ? AND user_id IS NULL")->execute([$userId, $sessionId]);
    }
}

/**
 * Merge session cart to user cart after login
 */
function mergeCartToUser($userId) {
    $db = db();
    $sessionId = $_SESSION['cart_id'] ?? session_id();
    
    // Move session items to user
    $stmt = $db->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ? AND user_id IS NULL");
    $stmt->execute([$userId, $sessionId]);
}
?>
