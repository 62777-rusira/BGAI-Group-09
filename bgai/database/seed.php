<?php
// Database Schema & Seed Data for BGAI E-Commerce
// Run this file once to set up the database

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bgai_ecommerce';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // Drop tables in correct order (foreign keys)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['reviews','wishlists','activity_logs','reports','payments','order_items','orders','cart_items',
               'products','categories','countries','currencies','site_settings','users'];
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ========================================
    // MODULE 1 - Users (Praveen)
    // ========================================
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        city VARCHAR(100) DEFAULT NULL,
        country_code VARCHAR(10) DEFAULT 'AU',
        role ENUM('customer','admin') DEFAULT 'customer',
        avatar VARCHAR(300) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // ========================================
    // MODULE 2 - Products & Categories (Rusira)
    // ========================================
    $pdo->exec("CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT DEFAULT NULL,
        image VARCHAR(300) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(200) NOT NULL,
        slug VARCHAR(200) UNIQUE NOT NULL,
        description TEXT,
        price_aud DECIMAL(10,2) NOT NULL,
        stock INT DEFAULT 0,
        image VARCHAR(300) NOT NULL,
        material VARCHAR(100) DEFAULT NULL,
        weight VARCHAR(50) DEFAULT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )");

    // ========================================
    // MODULE 3 - Cart & Orders (Thimira)
    // ========================================
    $pdo->exec("CREATE TABLE cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_cart_item (user_id, product_id)
    )");

    $pdo->exec("CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(20) UNIQUE NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) DEFAULT 0.00,
        shipping_amount DECIMAL(10,2) DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL,
        currency_code VARCHAR(10) DEFAULT 'AUD',
        status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
        shipping_name VARCHAR(100),
        shipping_address TEXT,
        shipping_city VARCHAR(100),
        shipping_country VARCHAR(100),
        shipping_phone VARCHAR(20),
        notes TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(200) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // ========================================
    // MODULE 4 - Currencies, Countries & Payments (Rukshan)
    // ========================================
    $pdo->exec("CREATE TABLE currencies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(10) UNIQUE NOT NULL,
        name VARCHAR(50) NOT NULL,
        symbol VARCHAR(10) NOT NULL,
        exchange_rate DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE countries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(10) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        currency_code VARCHAR(10) NOT NULL,
        tax_rate DECIMAL(5,2) DEFAULT 0.00,
        shipping_fee DECIMAL(10,2) DEFAULT 0.00,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (currency_code) REFERENCES currencies(code) ON UPDATE CASCADE
    )");

    $pdo->exec("CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        currency_code VARCHAR(10) DEFAULT 'AUD',
        payment_method ENUM('Credit Card','Debit Card','PayPal','Bank Transfer') NOT NULL,
        card_last_four VARCHAR(4) DEFAULT NULL,
        transaction_id VARCHAR(100) DEFAULT NULL,
        status ENUM('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
        refund_reason TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // ========================================
    // NEW - Wishlists
    // ========================================
    $pdo->exec("CREATE TABLE wishlists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_wishlist (user_id, product_id)
    )");

    // ========================================
    // NEW - Product Reviews
    // ========================================
    $pdo->exec("CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        title VARCHAR(200) DEFAULT NULL,
        comment TEXT DEFAULT NULL,
        is_approved TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_review (product_id, user_id)
    )");

    // ========================================
    // MODULE 5 - Admin Dashboard & Reports (Akela)
    // ========================================
    $pdo->exec("CREATE TABLE reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        type ENUM('Sales','Inventory','Users','Orders','Custom') NOT NULL,
        description TEXT DEFAULT NULL,
        date_from DATE NOT NULL,
        date_to DATE NOT NULL,
        generated_data JSON DEFAULT NULL,
        created_by INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        action VARCHAR(100) NOT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT DEFAULT NULL,
        details TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    $pdo->exec("CREATE TABLE site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // ========================================
    // SEED DATA
    // ========================================

    // Admin user (password: admin123)
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (name, email, password, role, country_code) VALUES
        ('Admin BGAI', 'admin@bgai.com', '$adminPass', 'admin', 'AU')");

    // Demo customer (password: customer123)
    $custPass = password_hash('customer123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (name, email, password, role, country_code, phone, address, city) VALUES
        ('Jane Smith', 'jane@example.com', '$custPass', 'customer', 'AU', '+61412345678', '123 Collins St', 'Melbourne')");

    // Currencies
    $pdo->exec("INSERT INTO currencies (code, name, symbol, exchange_rate) VALUES
        ('AUD', 'Australian Dollar', 'A\$', 1.0000),
        ('USD', 'US Dollar', '\$', 0.6500),
        ('GBP', 'British Pound', '\xC2\xA3', 0.5200),
        ('EUR', 'Euro', '\xE2\x82\xAC', 0.6100),
        ('INR', 'Indian Rupee', '\xE2\x82\xB9', 54.0000),
        ('LKR', 'Sri Lankan Rupee', 'Rs', 210.0000)
    ");

    // Countries
    $pdo->exec("INSERT INTO countries (code, name, currency_code, tax_rate, shipping_fee) VALUES
        ('AU', 'Australia', 'AUD', 10.00, 15.00),
        ('US', 'United States', 'USD', 8.00, 25.00),
        ('GB', 'United Kingdom', 'GBP', 20.00, 30.00),
        ('IN', 'India', 'INR', 18.00, 20.00),
        ('LK', 'Sri Lanka', 'LKR', 12.00, 18.00),
        ('DE', 'Germany', 'EUR', 19.00, 28.00)
    ");

    // Categories
    $pdo->exec("INSERT INTO categories (name, slug, description, image) VALUES
        ('Rings', 'rings', 'Exquisite rings crafted with precision and elegance', 'ring-diamond-solitaire.jpg'),
        ('Necklaces', 'necklaces', 'Stunning necklaces for every occasion', 'necklace-emerald-gold.jpg'),
        ('Bracelets', 'bracelets', 'Beautiful bracelets that complement your style', 'bracelet-diamond-tennis.jpg'),
        ('Earrings', 'earrings', 'Elegant earrings to enhance your beauty', 'earrings.jpg')
    ");

    // Products - Comprehensive collection like grahams.com.au
    $pdo->exec("INSERT INTO products (category_id, name, slug, description, price_aud, stock, image, material, weight, is_featured) VALUES
        -- RINGS (Category 1)
        (1, 'Diamond Solitaire Ring', 'diamond-solitaire-ring', 'A timeless classic featuring a brilliant-cut 1.2ct diamond set in 18k white gold. Perfect for engagements or special celebrations. GIA certified.', 2499.00, 15, 'ring-diamond-solitaire.jpg', '18K White Gold, Diamond (1.2ct)', '4.2g', 1),
        (1, 'Sapphire Halo Ring', 'sapphire-halo-ring', 'A stunning 2ct blue sapphire surrounded by a halo of 0.5ct sparkling diamonds set in rose gold. A modern classic.', 1899.00, 12, 'ring-sapphire-halo.jpg', '18K Rose Gold, Sapphire, Diamond', '3.8g', 1),
        (1, 'Vintage Emerald Ring', 'vintage-emerald-ring', 'Art Deco inspired emerald ring featuring a 1.5ct Colombian emerald flanked by tapered baguette diamonds.', 3450.00, 5, 'ring-sapphire-halo.jpg', '18K Yellow Gold, Emerald, Diamond', '5.1g', 0),
        (1, 'Eternity Diamond Band', 'eternity-diamond-band', 'Full eternity band with 2.0ct total weight round brilliant diamonds channel set in platinum.', 3999.00, 8, 'ring-diamond-solitaire.jpg', 'Platinum, Diamond (2.0ct TW)', '6.8g', 1),
        (1, 'Three-Stone Anniversary Ring', 'three-stone-anniversary-ring', 'Past, present, future design with three perfectly matched round diamonds totalling 1.8ct in white gold.', 4200.00, 4, 'ring-diamond-solitaire.jpg', '18K White Gold, Diamond (1.8ct TW)', '4.5g', 0),
        (1, 'Rose Gold Morganite Ring', 'rose-gold-morganite-ring', 'Blush pink 3ct morganite cushion cut stone surrounded by a micro-pave diamond halo. Romantic and feminine.', 1250.00, 18, 'ring-sapphire-halo.jpg', '14K Rose Gold, Morganite, Diamond', '3.2g', 0),

        -- NECKLACES (Category 2)
        (2, 'Emerald Gold Necklace', 'emerald-gold-necklace', 'Luxurious 2ct emerald pendant on a delicate 22k gold chain. A statement piece for the discerning collector.', 3200.00, 8, 'necklace-emerald-gold.jpg', '22K Gold, Emerald (2ct)', '12.5g', 1),
        (2, 'Ruby Pendant Necklace', 'ruby-pendant-necklace', 'A vibrant 1.8ct Burmese ruby pendant set in platinum with diamond accents along the bail.', 2750.00, 10, 'necklace-ruby-pendant.jpg', 'Platinum, Ruby, Diamond', '8.3g', 1),
        (2, 'Diamond Riviere Necklace', 'diamond-riviere-necklace', 'Graduated diamond necklace with 5ct total weight of round brilliant diamonds. The ultimate luxury statement.', 12500.00, 3, 'necklace-emerald-gold.jpg', '18K White Gold, Diamond (5ct TW)', '22.0g', 1),
        (2, 'Australian Opal Pendant', 'australian-opal-pendant', 'Stunning Lightning Ridge black opal pendant with vibrant play-of-colour, set in 18k yellow gold with diamond surround.', 4800.00, 5, 'necklace-ruby-pendant.jpg', '18K Yellow Gold, Black Opal, Diamond', '6.7g', 0),
        (2, 'Pearl Strand Necklace', 'pearl-strand-necklace', 'Classic 18-inch South Sea pearl strand featuring 7-8mm round pearls with beautiful lustre and 14k gold clasp.', 1890.00, 12, 'necklace-emerald-gold.jpg', '14K Gold, South Sea Pearl', '45.0g', 0),
        (2, 'Sapphire Heart Pendant', 'sapphire-heart-pendant', 'Heart-shaped blue sapphire pendant surrounded by diamonds on an 18k white gold chain. A symbol of love.', 1650.00, 15, 'necklace-ruby-pendant.jpg', '18K White Gold, Sapphire, Diamond', '5.2g', 0),

        -- BRACELETS (Category 3)
        (3, 'Diamond Tennis Bracelet', 'diamond-tennis-bracelet', 'Classic tennis bracelet featuring 3 carats of round brilliant diamonds individually set in 18k white gold.', 4500.00, 6, 'bracelet-diamond-tennis.jpg', '18K White Gold, Diamond (3ct TW)', '15.2g', 1),
        (3, 'Gold Diamond Bangle', 'gold-diamond-bangle', 'Elegant gold bangle with pave-set diamonds creating a continuous sparkle. Hinged for easy wear.', 2100.00, 14, 'bracelet-gold-diamond.jpg', '18K Yellow Gold, Diamond', '18.7g', 1),
        (3, 'Ruby & Diamond Bracelet', 'ruby-diamond-bracelet', 'Alternating round rubies and diamonds in a line bracelet setting. Bold colour meets classic elegance.', 3850.00, 7, 'bracelet-diamond-tennis.jpg', '18K White Gold, Ruby, Diamond', '14.0g', 0),
        (3, 'Charm Link Bracelet', 'charm-link-bracelet', 'Italian-made 18k gold link bracelet with diamond-set charm stations. Versatile everyday luxury.', 1450.00, 20, 'bracelet-gold-diamond.jpg', '18K Yellow Gold, Diamond', '12.5g', 0),
        (3, 'Emerald Cuff Bracelet', 'emerald-cuff-bracelet', 'Bold open cuff bracelet featuring 2ct of emeralds and diamonds set in a geometric pattern.', 5200.00, 4, 'bracelet-diamond-tennis.jpg', '18K White Gold, Emerald, Diamond', '28.0g', 0),
        (3, 'Pearl & Diamond Bracelet', 'pearl-diamond-bracelet', 'Akoya pearls alternating with diamond rondelles on a flexible wire bracelet with magnetic clasp.', 980.00, 22, 'bracelet-gold-diamond.jpg', '18K White Gold, Pearl, Diamond', '9.5g', 0),

        -- EARRINGS (Category 4)
        (4, 'Pearl Drop Earrings', 'pearl-drop-earrings', 'Classic South Sea pearl drops with diamond-set hooks in 18k white gold. Timeless sophistication.', 1650.00, 20, 'earrings.jpg', '18K White Gold, Pearl, Diamond', '6.4g', 1),
        (4, 'Diamond Stud Earrings', 'diamond-stud-earrings', 'Timeless 1ct total weight round brilliant diamond studs in a four-prong platinum setting. GIA certified.', 1999.00, 18, 'earrings.jpg', 'Platinum, Diamond (1ct TW)', '3.2g', 1),
        (4, 'Sapphire Cluster Earrings', 'sapphire-cluster-earrings', 'Blue sapphires and diamonds arranged in a floral cluster design. Eye-catching and elegant.', 2350.00, 9, 'earrings.jpg', '18K White Gold, Sapphire, Diamond', '7.1g', 0),
        (4, 'Gold Hoop Earrings', 'gold-hoop-earrings', 'Classic 30mm polished hoops in 18k yellow gold. Lightweight and comfortable for everyday wear.', 650.00, 30, 'earrings.jpg', '18K Yellow Gold', '5.0g', 0),
        (4, 'Diamond Chandelier Earrings', 'diamond-chandelier-earrings', 'Spectacular cascading diamond earrings with 2.5ct total weight. Perfect for special occasions and galas.', 6500.00, 3, 'earrings.jpg', '18K White Gold, Diamond (2.5ct TW)', '10.8g', 1),
        (4, 'Ruby Huggie Earrings', 'ruby-huggie-earrings', 'Petite huggie hoops lined with brilliant-cut rubies and diamonds. Modern luxury for everyday.', 890.00, 25, 'earrings.jpg', '14K Rose Gold, Ruby, Diamond', '3.8g', 0)
    ");

    // More products - Watches & Pendants categories
    $pdo->exec("INSERT INTO categories (name, slug, description, image) VALUES
        ('Watches', 'watches', 'Luxury timepieces for the distinguished collector', 'ring-diamond-solitaire.jpg'),
        ('Pendants', 'pendants', 'Statement pendants that captivate and enchant', 'necklace-ruby-pendant.jpg')
    ");

    $pdo->exec("INSERT INTO products (category_id, name, slug, description, price_aud, stock, image, material, weight, is_featured) VALUES
        -- MORE RINGS
        (1, 'Platinum Trilogy Ring', 'platinum-trilogy-ring', 'Three stone ring symbolizing past, present and future. Features 1.5ct total weight round brilliant diamonds in platinum.', 5500.00, 6, 'ring-diamond-solitaire.jpg', 'Platinum, Diamond (1.5ct TW)', '7.2g', 0),
        (1, 'Yellow Diamond Ring', 'yellow-diamond-ring', 'Rare natural fancy yellow diamond 2ct set in 18k yellow gold with white diamond shoulders. Extraordinary colour.', 8900.00, 2, 'ring-sapphire-halo.jpg', '18K Yellow Gold, Fancy Yellow Diamond', '5.5g', 1),
        (1, 'Tanzanite Cocktail Ring', 'tanzanite-cocktail-ring', 'Bold 4ct tanzanite in a dramatic cocktail ring setting with diamond accents. A true conversation starter.', 2850.00, 9, 'ring-sapphire-halo.jpg', '18K White Gold, Tanzanite, Diamond', '6.1g', 0),

        -- MORE NECKLACES
        (2, 'Diamond Choker Necklace', 'diamond-choker-necklace', 'Stunning 3ct diamond choker on 14k white gold. Sits perfectly on the collarbone for maximum impact.', 5600.00, 4, 'necklace-emerald-gold.jpg', '14K White Gold, Diamond (3ct TW)', '18.5g', 0),
        (2, 'Layered Gold Chain Set', 'layered-gold-chain-set', 'Set of 3 delicate 18k gold chains at different lengths. Mix and match for a modern layered look.', 1200.00, 25, 'necklace-ruby-pendant.jpg', '18K Yellow Gold', '8.2g', 0),
        (2, 'Aquamarine Drop Necklace', 'aquamarine-drop-necklace', 'Serene 3ct aquamarine briolette drop on diamond-set bail. Captures the colour of the Australian ocean.', 2100.00, 11, 'necklace-emerald-gold.jpg', '18K White Gold, Aquamarine, Diamond', '5.8g', 1),

        -- MORE BRACELETS
        (3, 'Sapphire Tennis Bracelet', 'sapphire-tennis-bracelet', 'Alternating sapphires and diamonds in a classic tennis bracelet. 4ct total gem weight in white gold.', 5800.00, 5, 'bracelet-diamond-tennis.jpg', '18K White Gold, Sapphire, Diamond', '16.0g', 1),
        (3, 'Rose Gold Bangle Set', 'rose-gold-bangle-set', 'Set of 3 stackable rose gold bangles with diamond stations. Mix widths for a modern stacked look.', 1800.00, 15, 'bracelet-gold-diamond.jpg', '14K Rose Gold, Diamond', '24.0g', 0),

        -- MORE EARRINGS
        (4, 'Emerald Drop Earrings', 'emerald-drop-earrings', 'Pear-shaped emeralds suspended from diamond-set hooks. A striking combination of green and sparkle.', 3200.00, 8, 'earrings.jpg', '18K Yellow Gold, Emerald, Diamond', '8.5g', 0),
        (4, 'Diamond Ear Cuff', 'diamond-ear-cuff', 'Modern diamond ear cuff with 0.5ct pave diamonds. No piercing required. Edgy luxury.', 750.00, 30, 'earrings.jpg', '18K White Gold, Diamond', '2.1g', 0),

        -- WATCHES (Category 5)
        (5, 'Diamond Dress Watch', 'diamond-dress-watch', 'Elegant ladies dress watch with mother of pearl dial, diamond bezel and 18k white gold bracelet. Swiss movement.', 9500.00, 4, 'ring-diamond-solitaire.jpg', '18K White Gold, Diamond, Swiss Movement', '65.0g', 1),
        (5, 'Gold Chronograph Watch', 'gold-chronograph-watch', 'Mens 18k gold chronograph with black dial, date window and alligator leather strap. Automatic movement.', 12000.00, 3, 'bracelet-gold-diamond.jpg', '18K Yellow Gold, Sapphire Crystal', '85.0g', 1),
        (5, 'Rose Gold Ladies Watch', 'rose-gold-ladies-watch', 'Minimalist rose gold watch with blush pink dial, diamond hour markers and mesh bracelet.', 3200.00, 10, 'bracelet-diamond-tennis.jpg', '14K Rose Gold, Diamond', '42.0g', 0),

        -- PENDANTS (Category 6)
        (6, 'Diamond Cross Pendant', 'diamond-cross-pendant', 'Classic diamond cross pendant with 0.75ct pave diamonds on an 18k white gold chain. Meaningful elegance.', 1450.00, 15, 'necklace-ruby-pendant.jpg', '18K White Gold, Diamond (0.75ct)', '4.5g', 0),
        (6, 'Opal Teardrop Pendant', 'opal-teardrop-pendant', 'Australian boulder opal in a teardrop shape showing brilliant play of colour. One of a kind natural artwork.', 2800.00, 3, 'necklace-emerald-gold.jpg', '18K Yellow Gold, Boulder Opal', '5.2g', 1),
        (6, 'Initial Letter Pendant', 'initial-letter-pendant', 'Personalised diamond-set initial pendant in 14k gold. Choose your letter. Makes a thoughtful gift.', 650.00, 50, 'necklace-ruby-pendant.jpg', '14K Yellow Gold, Diamond', '2.8g', 0),
        (6, 'Sapphire Star Pendant', 'sapphire-star-pendant', 'Blue sapphire star pendant with diamond tips. Inspired by the Southern Cross constellation.', 1850.00, 12, 'necklace-emerald-gold.jpg', '18K White Gold, Sapphire, Diamond', '3.9g', 1)
    ");

    // Sample Reviews
    $pdo->exec("INSERT INTO reviews (product_id, user_id, rating, title, comment) VALUES
        (1, 2, 5, 'Absolutely Stunning!', 'My fiance loved this ring! The diamond sparkles beautifully in any light. Packaging was exquisite too.'),
        (2, 2, 5, 'Beautiful Sapphire', 'The sapphire colour is incredible - deep blue with amazing clarity. Worth every cent.'),
        (7, 2, 4, 'Gorgeous Necklace', 'The emerald is stunning and the gold chain is very delicate and elegant. Slight delay in shipping but worth the wait.'),
        (13, 2, 5, 'Perfect Tennis Bracelet', 'Bought this for my wife for our anniversary. She has not taken it off since! Diamonds are brilliant.'),
        (19, 2, 5, 'Classic Pearl Earrings', 'These pearls have a beautiful lustre and the diamond hooks add a touch of luxury. Very elegant.'),
        (20, 2, 4, 'Great Diamond Studs', 'Beautiful earrings, good size and great sparkle. Slightly smaller than expected but still lovely.')
    ");

    // Site settings
    $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES
        ('site_name', 'Brilliance Gems of Australia International'),
        ('site_email', 'info@bgai.com.au'),
        ('site_phone', '+61 3 9000 1234'),
        ('site_address', '250 Collins Street, Melbourne VIC 3000'),
        ('default_currency', 'AUD'),
        ('tax_enabled', '1'),
        ('shipping_enabled', '1'),
        ('maintenance_mode', '0')
    ");

    echo "<div style='font-family:sans-serif;padding:40px;text-align:center;background:#111;color:#c9a84c;min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;'>
        <h1>Database Setup Complete!</h1>
        <p style='color:#ccc;margin:20px 0;'>All tables created and seed data inserted successfully.</p>
        <p style='color:#999;font-size:14px;'>Admin login: admin@bgai.com / admin123<br>Customer login: jane@example.com / customer123</p>
        <a href='../index.php' style='margin-top:30px;display:inline-block;padding:12px 40px;background:linear-gradient(135deg,#c9a84c,#e8d48b);color:#111;text-decoration:none;border-radius:30px;font-weight:bold;'>Go to Homepage</a>
    </div>";

} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:40px;color:red;'>Error: " . $e->getMessage() . "</div>");
}
