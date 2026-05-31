<?php
// ============================================
// BGAI Application Configuration
// ============================================

require_once __DIR__ . '/database.php';

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Timezone
date_default_timezone_set('Australia/Sydney');

// Autoload helper functions
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cart_functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';

// Initialize cart session
if (!isset($_SESSION['cart_id'])) {
    $_SESSION['cart_id'] = session_id();
}

// Detect user country and currency
if (!isset($_SESSION['country_code'])) {
    // Use GeoIP detection or default
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if ($ip === '::1' || $ip === '127.0.0.1') {
        $_SESSION['country_code'] = DEFAULT_COUNTRY;
        $_SESSION['currency_code'] = DEFAULT_CURRENCY;
    } else {
        // Try to detect country (simplified)
        $geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
        if ($geo) {
            $data = json_decode($geo, true);
            $countryMap = [
                'AU' => 'AUD', 'US' => 'USD', 'GB' => 'GBP', 'IN' => 'INR', 
                'AE' => 'AED', 'CA' => 'CAD', 'SG' => 'SGD', 'JP' => 'JPY', 
                'NZ' => 'NZD', 'DE' => 'EUR', 'FR' => 'EUR'
            ];
            $_SESSION['country_code'] = $data['countryCode'] ?? DEFAULT_COUNTRY;
            $_SESSION['currency_code'] = $countryMap[$_SESSION['country_code']] ?? DEFAULT_CURRENCY;
        } else {
            $_SESSION['country_code'] = DEFAULT_COUNTRY;
            $_SESSION['currency_code'] = DEFAULT_CURRENCY;
        }
    }
}

// Flash message helper
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

// Check if HTTPS
function isHttps() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443;
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
