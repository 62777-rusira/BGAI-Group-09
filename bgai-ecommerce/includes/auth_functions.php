<?php
// ============================================
// BGAI Authentication Functions
// ============================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = db();
    $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, address_line1, address_line2, city, state, postal_code, country, country_code, currency_preference, is_subscribed, role, created_at FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Login user
 */
function loginUser($email, $password) {
    $db = db();
    $stmt = $db->prepare("SELECT id, first_name, last_name, email, password, role, status, country_code, currency_preference FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'Your account has been suspended. Please contact support.'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Update preferences
    if ($user['country_code']) {
        $_SESSION['country_code'] = $user['country_code'];
        $countryMap = [
            'AU' => 'AUD', 'US' => 'USD', 'GB' => 'GBP', 'IN' => 'INR', 
            'AE' => 'AED', 'CA' => 'CAD', 'SG' => 'SGD', 'JP' => 'JPY', 
            'NZ' => 'NZD', 'DE' => 'EUR', 'FR' => 'EUR'
        ];
        $_SESSION['currency_code'] = $countryMap[$user['country_code']] ?? DEFAULT_CURRENCY;
    }
    
    // Update last login
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    
    // Merge cart
    mergeCartToUser($user['id']);
    
    return ['success' => true, 'message' => 'Welcome back, ' . $user['first_name'] . '!', 'role' => $user['role']];
}

/**
 * Register new user
 */
function registerUser($data) {
    $db = db();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'An account with this email already exists'];
    }
    
    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, phone, password, address_line1, city, state, postal_code, country, country_code, currency_preference, is_subscribed, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'customer', 'active')");
    
    $result = $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'] ?? '',
        $hashedPassword,
        $data['address_line1'] ?? '',
        $data['city'] ?? '',
        $data['state'] ?? '',
        $data['postal_code'] ?? '',
        $data['country'] ?? 'Australia',
        $data['country_code'] ?? 'AU',
        $data['currency_preference'] ?? 'AUD',
        $data['is_subscribed'] ?? 0
    ]);
    
    if ($result) {
        return ['success' => true, 'message' => 'Account created successfully! Please sign in.', 'user_id' => $db->lastInsertId()];
    }
    
    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

/**
 * Update user profile
 */
function updateProfile($userId, $data) {
    $db = db();
    $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, country_code = ?, currency_preference = ? WHERE id = ?");
    
    $result = $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['phone'] ?? '',
        $data['address_line1'] ?? '',
        $data['address_line2'] ?? '',
        $data['city'] ?? '',
        $data['state'] ?? '',
        $data['postal_code'] ?? '',
        $data['country'] ?? '',
        $data['country_code'] ?? 'AU',
        $data['currency_preference'] ?? 'AUD',
        $userId
    ]);
    
    if ($result) {
        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to update profile'];
}

/**
 * Change user password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $db = db();
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    return ['success' => true, 'message' => 'Password changed successfully'];
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect(APP_URL . '/login.php');
    }
}
?>
