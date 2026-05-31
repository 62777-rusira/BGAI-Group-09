<?php
// ============================================
// BGAI Quick Fix Script - Run if you already have the database set up
// This updates passwords without dropping tables
// ============================================

echo "<h2>BGAI Quick Password Fix</h2>";

$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=bgai_ecommerce;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("<p style='color:red;'>Connection failed: " . $e->getMessage() . "<br>Make sure MySQL is running.</p>");
}

// Fix admin password
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@bgai.com.au'")->execute([$adminPassword]);
echo "<p>✅ Admin password updated to 'admin123'</p>";

// Fix customer passwords
$customerPassword = password_hash('password', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'sarah.mitchell@email.com'")->execute([$customerPassword]);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'james.t@email.com'")->execute([$customerPassword]);
echo "<p>✅ Customer passwords updated to 'password'</p>";

echo "<hr>";
echo "<p style='color:green;font-weight:bold;'>✅ All passwords updated successfully!</p>";
echo "<p><a href='../index.php'>Go to Homepage</a> | <a href='../login.php'>Go to Login</a></p>";
?>
