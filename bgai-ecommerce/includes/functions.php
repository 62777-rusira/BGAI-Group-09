<?php
// ============================================
// BGAI Helper Functions
// ============================================

/**
 * Format price with currency symbol
 */
function formatPrice($amount, $currencyCode = null) {
    $currencyCode = $currencyCode ?? $_SESSION['currency_code'] ?? DEFAULT_CURRENCY;
    $symbols = json_decode(CURRENCY_SYMBOLS, true);
    $symbol = $symbols[$currencyCode] ?? '$';
    
    // Indian numbering system
    if ($currencyCode === 'INR') {
        $amount = number_format($amount, 0, '.', ',');
        // Insert decimal separator for Indian format
        $lastThree = substr($amount, -3);
        $rest = substr($amount, 0, -3);
        $formatted = ($rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' : '') . $lastThree;
        return $symbol . $formatted;
    }
    
    // JPY has no decimals
    if ($currencyCode === 'JPY') {
        return $symbol . number_format($amount, 0);
    }
    
    // AED special handling
    if ($currencyCode === 'AED') {
        return $amount . ' ' . $symbol;
    }
    
    return $symbol . number_format($amount, 2);
}

/**
 * Get product price based on country
 */
function getProductPrice($productId, $countryCode = null) {
    $countryCode = $countryCode ?? $_SESSION['country_code'] ?? DEFAULT_COUNTRY;
    $db = db();
    
    $stmt = $db->prepare("SELECT price, sale_price, tax_rate, shipping_cost, currency_code FROM country_pricing WHERE product_id = ? AND country_code = ? LIMIT 1");
    $stmt->execute([$productId, $countryCode]);
    $pricing = $stmt->fetch();
    
    if ($pricing) {
        return $pricing;
    }
    
    // Fallback to base price
    $stmt = $db->prepare("SELECT base_price as price, sale_price, currency_code FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        $taxRates = json_decode(TAX_RATES, true);
        return [
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'tax_rate' => $taxRates[$countryCode] ?? 0,
            'shipping_cost' => 0,
            'currency_code' => $_SESSION['currency_code'] ?? DEFAULT_CURRENCY
        ];
    }
    
    return null;
}

/**
 * Get effective price (sale or regular)
 */
function getEffectivePrice($pricing) {
    return $pricing['sale_price'] && $pricing['sale_price'] < $pricing['price'] 
        ? $pricing['sale_price'] 
        : $pricing['price'];
}

/**
 * Calculate discount percentage
 */
function getDiscountPercent($regular, $sale) {
    if ($sale && $sale < $regular) {
        return round(($regular - $sale) / $regular * 100);
    }
    return 0;
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL-friendly slug
 */
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

/**
 * Get all categories
 */
function getCategories($activeOnly = true) {
    $db = db();
    $sql = "SELECT * FROM categories";
    if ($activeOnly) $sql .= " WHERE status = 'active'";
    $sql .= " ORDER BY sort_order ASC";
    return $db->query($sql)->fetchAll();
}

/**
 * Get all collections
 */
function getCollections($featuredOnly = false, $activeOnly = true) {
    $db = db();
    $sql = "SELECT * FROM collections WHERE 1=1";
    if ($featuredOnly) $sql .= " AND featured = 1";
    if ($activeOnly) $sql .= " AND status = 'active'";
    $sql .= " ORDER BY id ASC";
    return $db->query($sql)->fetchAll();
}

/**
 * Get products with optional filters
 */
function getProducts($filters = []) {
    $db = db();
    $where = ["p.status = 'active'"];
    $params = [];
    
    if (!empty($filters['category_id'])) {
        $where[] = "p.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['collection_id'])) {
        $where[] = "p.collection_id = ?";
        $params[] = $filters['collection_id'];
    }
    
    if (!empty($filters['featured'])) {
        $where[] = "p.is_featured = 1";
    }
    
    if (!empty($filters['bestseller'])) {
        $where[] = "p.is_bestseller = 1";
    }
    
    if (!empty($filters['new_arrival'])) {
        $where[] = "p.is_new_arrival = 1";
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($filters['metal_type'])) {
        $where[] = "p.metal_type = ?";
        $params[] = $filters['metal_type'];
    }
    
    if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
        $where[] = "p.base_price BETWEEN ? AND ?";
        $params[] = $filters['min_price'];
        $params[] = $filters['max_price'];
    }
    
    $sql = "SELECT p.*, c.name as category_name, col.name as collection_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN collections col ON p.collection_id = col.id
            WHERE " . implode(' AND ', $where);
    
    // Sorting
    switch ($filters['sort'] ?? 'newest') {
        case 'price_low': $sql .= " ORDER BY p.base_price ASC"; break;
        case 'price_high': $sql .= " ORDER BY p.base_price DESC"; break;
        case 'name_asc': $sql .= " ORDER BY p.name ASC"; break;
        case 'name_desc': $sql .= " ORDER BY p.name DESC"; break;
        default: $sql .= " ORDER BY p.created_at DESC"; break;
    }
    
    // Pagination
    $page = max(1, intval($filters['page'] ?? 1));
    $limit = intval($filters['limit'] ?? 12);
    $offset = ($page - 1) * $limit;
    
    // Count total
    $countSql = str_replace("SELECT p.*, c.name as category_name, col.name as collection_name,\n            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image", "SELECT COUNT(*) as total", $sql);
    $total = $db->prepare($countSql);
    $total->execute($params);
    $totalCount = $total->fetchColumn();
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    return [
        'products' => $products,
        'total' => intval($totalCount),
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($totalCount / $limit)
    ];
}

/**
 * Get single product by ID or slug
 */
function getProduct($identifier) {
    $db = db();
    
    if (is_numeric($identifier)) {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, col.name as collection_name, col.slug as collection_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN collections col ON p.collection_id = col.id
            WHERE p.id = ? LIMIT 1");
    } else {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, col.name as collection_name, col.slug as collection_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN collections col ON p.collection_id = col.id
            WHERE p.slug = ? LIMIT 1");
    }
    
    $stmt->execute([$identifier]);
    return $stmt->fetch();
}

/**
 * Get product images
 */
function getProductImages($productId) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/**
 * Get related products
 */
function getRelatedProducts($productId, $categoryId, $limit = 4) {
    $db = db();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name,
        (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id != ? AND p.status = 'active' AND (p.category_id = ? OR p.collection_id = (SELECT collection_id FROM products WHERE id = ?))
        ORDER BY RAND() LIMIT ?");
    $stmt->execute([$productId, $categoryId, $productId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get site setting
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        $db = db();
        $rows = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Redirect with flash message
 */
function redirect($url, $type = null, $message = null) {
    if ($type && $message) {
        setFlash($type, $message);
    }
    header("Location: $url");
    exit;
}

/**
 * Get order statistics for admin
 */
function getOrderStats() {
    $db = db();
    $stats = [];
    
    $stats['total_orders'] = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['total_revenue'] = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    $stats['pending_orders'] = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    $stats['total_customers'] = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $stats['total_products'] = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $stats['low_stock'] = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND status = 'active'")->fetchColumn();
    
    // Monthly revenue
    $stats['monthly_revenue'] = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
    
    // Recent orders
    $stats['recent_orders'] = $db->query("SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
    
    // Sales by month (last 6 months)
    $stats['monthly_sales'] = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as orders, SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month")->fetchAll();
    
    // Top products
    $stats['top_products'] = $db->query("SELECT oi.product_name, SUM(oi.quantity) as total_sold, SUM(oi.total_price) as total_revenue FROM order_items oi GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5")->fetchAll();
    
    return $stats;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
?>
