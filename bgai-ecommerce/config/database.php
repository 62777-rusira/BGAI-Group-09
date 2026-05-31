<?php
// ============================================
// BGAI Database Configuration
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'bgai_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'BGAI');
define('APP_FULL_NAME', 'Brilliance, Gems of Australia International');
define('APP_URL', 'http://localhost/bgai-ecommerce');
define('APP_ROOT', dirname(__DIR__));

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('CART_COOKIE_NAME', 'bgai_cart_session');

// Default Settings
define('DEFAULT_CURRENCY', 'AUD');
define('DEFAULT_COUNTRY', 'AU');
define('FREE_SHIPPING_THRESHOLD', 500);

// Currency Symbols
define('CURRENCY_SYMBOLS', json_encode([
    'AUD' => 'A$', 'USD' => '$', 'GBP' => '£', 'EUR' => '€', 'INR' => '₹', 'AED' => 'د.إ', 'CAD' => 'C$', 'SGD' => 'S$', 'JPY' => '¥', 'NZD' => 'NZ$'
]));

// Tax Rates by Country
define('TAX_RATES', json_encode([
    'AU' => 10, 'US' => 0, 'GB' => 20, 'IN' => 3, 'AE' => 5, 'CA' => 5, 'SG' => 7, 'JP' => 10, 'NZ' => 15, 'DE' => 19, 'FR' => 20
]));

// Database Connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    return $pdo;
}

// Helper: Get PDO instance
function db() {
    return getDBConnection();
}
?>
