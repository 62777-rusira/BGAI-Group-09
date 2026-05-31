<?php
// ============================================
// API - User Logout
// ============================================
require_once __DIR__ . '/../config/app.php';
logoutUser();
$redirect = $_GET['redirect'] ?? APP_URL;
header("Location: $redirect");
exit;
?>
