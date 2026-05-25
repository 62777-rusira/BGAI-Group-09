<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'bgai_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'Brilliance Gems');
define('BASE_URL', '/bgai');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function getCartCount($pdo, $userId) {
    if (!$userId) return 0;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getUserCurrency($pdo) {
    $default = ['code' => 'AUD', 'symbol' => '$', 'rate' => 1.00];
    if (isset($_SESSION['currency_code'])) {
        $stmt = $pdo->prepare("SELECT code, symbol, exchange_rate as rate FROM currencies WHERE code = ? AND is_active = 1");
        $stmt->execute([$_SESSION['currency_code']]);
        $c = $stmt->fetch();
        if ($c) return $c;
    }
    return $default;
}

function formatPrice($amount, $currency = null) {
    global $pdo;
    if (!$currency) $currency = getUserCurrency($pdo);
    $converted = $amount * $currency['rate'];
    return $currency['symbol'] . number_format($converted, 2);
}

function getWishlistCount($pdo, $userId) {
    if (!$userId) return 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function isInWishlist($pdo, $userId, $productId) {
    if (!$userId) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return $stmt->fetchColumn() > 0;
}

function getProductRating($pdo, $productId) {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ? AND is_approved = 1");
    $stmt->execute([$productId]);
    $r = $stmt->fetch();
    return ['avg' => round($r['avg_rating'] ?? 0, 1), 'count' => (int)$r['review_count']];
}

function renderStars($rating, $size = '0.85rem') {
    $html = '<div class="star-rating" style="display:inline-flex;gap:2px;font-size:'.$size.';">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="fas fa-star" style="color:var(--gold);"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt" style="color:var(--gold);"></i>';
        } else {
            $html .= '<i class="far fa-star" style="color:var(--text-dim);"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

function logActivity($pdo, $userId, $action, $entityType, $entityId = null, $details = null) {
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $entityType, $entityId, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
}
