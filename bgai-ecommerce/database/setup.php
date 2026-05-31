<?php
// ============================================
// BGAI Database Setup Script
// Run this ONCE to set up the database with correct passwords
// ============================================

echo "<h2>BGAI Database Setup</h2>";

// 1. Create Database
$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("<p style='color:red;'>Connection failed: " . $e->getMessage() . "<br>Make sure MySQL is running in XAMPP.</p>");
}

// 2. Create database
$pdo->exec("CREATE DATABASE IF NOT EXISTS bgai_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE bgai_ecommerce");
echo "<p>✅ Database 'bgai_ecommerce' created/verified</p>";

// 3. Drop existing tables (fresh install)
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = ['order_items', 'orders', 'wishlist', 'cart', 'country_pricing', 'product_images', 'products', 'collections', 'categories', 'users', 'contact_messages', 'newsletter_subscribers', 'site_settings'];
foreach ($tables as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `$table`");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "<p>✅ Existing tables dropped</p>";

// 4. Execute the full schema
$sqlFile = __DIR__ . '/schema.sql';
$sql = file_get_contents($sqlFile);

// Remove the CREATE DATABASE and USE lines since we already did that
$sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $sql);
$sql = preg_replace('/USE bgai_ecommerce;/s', '', $sql);

$pdo->exec($sql);
echo "<p>✅ Schema imported from schema.sql</p>";

// 5. Fix admin password - hash with PHP properly
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@bgai.com.au'")->execute([$adminPassword]);
echo "<p>✅ Admin password set to 'admin123' (properly hashed)</p>";

// 6. Fix customer passwords
$customerPassword = password_hash('password', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'sarah.mitchell@email.com'")->execute([$customerPassword]);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'james.t@email.com'")->execute([$customerPassword]);
echo "<p>✅ Customer passwords set to 'password' (properly hashed)</p>";

// 7. Verify
$stmt = $pdo->query("SELECT email, role FROM users ORDER BY role");
echo "<p>✅ Users in database:</p><ul>";
while ($row = $stmt->fetch()) {
    echo "<li>" . $row['email'] . " (" . $row['role'] . ")</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p style='color:green;font-weight:bold;font-size:1.2em;'>🎉 Setup Complete!</p>";
echo "<p><strong>Login Credentials:</strong></p>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><td>Admin</td><td>admin@bgai.com.au</td><td>admin123</td></tr>";
echo "<tr><td>Customer</td><td>sarah.mitchell@email.com</td><td>password</td></tr>";
echo "<tr><td>Customer</td><td>james.t@email.com</td><td>password</td></tr>";
echo "</table>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='../admin/'>Go to Admin</a></p>";
?>
