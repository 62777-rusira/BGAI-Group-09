<?php
require 'includes/db.php';

// Create tables + seed data
$pdo->exec("DROP TABLE IF EXISTS order_items, orders, products, users");

$pdo->exec("
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') DEFAULT 'customer',
    country VARCHAR(10) DEFAULT 'IN',
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price_aud DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 20,
    image VARCHAR(300) NOT NULL,
    specs TEXT
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10),
    country VARCHAR(10),
    status ENUM('Pending','Paid','Shipped','Delivered') DEFAULT 'Pending',
    payment_method VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
");

// Seed Admin (password = admin123)
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)")
    ->execute(['Admin BGAI', 'admin@bgai.com', $adminHash, 'admin']);

// Seed 12 beautiful products
$products = [
['Diamond Solitaire Ring','Rings','Luxury solitaire',1499,'upload/ring-diamond-solitaire.jpg','Gold • Diamond'],
['Sapphire Halo Ring','Rings','Blue sapphire halo',1399,'upload/ring-sapphire-halo.jpg','Sapphire'],
['Emerald Necklace','Necklaces','Emerald elegance',899,'upload/necklace-emerald-gold.jpg','Gold Emerald'],
['Ruby Necklace','Necklaces','Ruby pendant',850,'upload/necklace-ruby-pendant.jpg','Ruby'],
['Diamond Bracelet','Bracelets','Tennis bracelet',700,'upload/bracelet-diamond-tennis.jpg','Diamond'],
['Gold Bracelet','Bracelets','Gold bracelet',650,'upload/bracelet-gold-diamond.jpg','Gold'],
];

$stmt = $pdo->prepare("INSERT INTO products (name,category,description,price_aud,stock,image,specs) VALUES (?,?,?,?,?,?,?)");
foreach ($products as $p) {
    $stmt->execute([$p[0],$p[1],$p[2],$p[3],20,$p[4],$p[5]]);
}

echo "<h1 style='color:#c5a05b;text-align:center;margin-top:50px;'>✅ BGAI Database Seeded Successfully!<br><a href='index.php'>Go to Homepage →</a></h1>";
?>