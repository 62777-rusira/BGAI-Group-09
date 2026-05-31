-- ============================================
-- BGAI E-Commerce Database Schema
-- Brilliance, Gems of Australia International
-- ============================================

CREATE DATABASE IF NOT EXISTS bgai_ecommerce;
USE bgai_ecommerce;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Collections Table
CREATE TABLE IF NOT EXISTS collections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    sku VARCHAR(50) UNIQUE,
    category_id INT,
    collection_id INT,
    base_price DECIMAL(12, 2) NOT NULL,
    sale_price DECIMAL(12, 2),
    weight_grams DECIMAL(8, 2),
    material VARCHAR(255),
    gemstone VARCHAR(255),
    metal_type ENUM('Gold', 'Silver', 'Platinum', 'Rose Gold', 'White Gold') DEFAULT 'Gold',
    metal_purity VARCHAR(50),
    dimensions VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    is_featured BOOLEAN DEFAULT FALSE,
    is_bestseller BOOLEAN DEFAULT FALSE,
    is_new_arrival BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL
);

-- Product Images Table
CREATE TABLE IF NOT EXISTS product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Country-Based Pricing Table
CREATE TABLE IF NOT EXISTS country_pricing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    country_code VARCHAR(5) NOT NULL,
    currency_code VARCHAR(5) NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    sale_price DECIMAL(12, 2),
    tax_rate DECIMAL(5, 2) DEFAULT 0.00,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_country (product_id, country_code)
);

-- Users / Customers Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    country_code VARCHAR(5) DEFAULT 'AU',
    currency_preference VARCHAR(5) DEFAULT 'AUD',
    is_subscribed BOOLEAN DEFAULT FALSE,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Shopping Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    shipping_address_line1 VARCHAR(255),
    shipping_address_line2 VARCHAR(255),
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_postal_code VARCHAR(20),
    shipping_country VARCHAR(100),
    shipping_country_code VARCHAR(5),
    billing_address_line1 VARCHAR(255),
    billing_address_line2 VARCHAR(255),
    billing_city VARCHAR(100),
    billing_state VARCHAR(100),
    billing_postal_code VARCHAR(20),
    billing_country VARCHAR(100),
    currency_code VARCHAR(5) DEFAULT 'AUD',
    subtotal DECIMAL(12, 2) NOT NULL,
    tax_amount DECIMAL(12, 2) DEFAULT 0.00,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'processing', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    tracking_url VARCHAR(500),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255),
    product_sku VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    total_price DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Wishlist Table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert Categories
INSERT INTO categories (name, slug, description, sort_order, status) VALUES
('Rings', 'rings', 'Exquisite rings for every occasion, from engagement to everyday elegance.', 1, 'active'),
('Necklaces', 'necklaces', 'Stunning necklaces and pendants that capture timeless beauty.', 2, 'active'),
('Bracelets', 'bracelets', 'Elegant bracelets crafted with precision and care.', 3, 'active'),
('Earrings', 'earrings', 'Beautiful earrings from classic studs to statement drops.', 4, 'active'),
('Pendants', 'pendants', 'Unique pendants featuring Australian gemstones and diamonds.', 5, 'active'),
('Brooches', 'brooches', 'Artisan brooches combining modern design with classic craftsmanship.', 6, 'active');

-- Insert Collections
INSERT INTO collections (name, slug, description, featured, status) VALUES
('Opal Dreams', 'opal-dreams', 'A mesmerising collection featuring Australia\'s national gemstone — the opal. Each piece showcases the unique play of colour found only in Australian opals.', TRUE, 'active'),
('Diamond Solstice', 'diamond-solstice', 'Brilliant diamonds set in precious metals, inspired by the Australian sun. Timeless elegance meets modern design.', TRUE, 'active'),
('Outback Gold', 'outback-gold', 'Handcrafted gold pieces inspired by the rugged beauty of the Australian outback. Each design tells a story of the land.', TRUE, 'active'),
('Pearl Harbour', 'pearl-harbour', 'Lustrous South Sea pearls from Australian waters, set in elegant designs that celebrate ocean beauty.', FALSE, 'active'),
('Sapphire Coast', 'sapphire-coast', 'Deep blue sapphires from the Australian Sapphire Coast, showcasing nature\'s most captivating blue hues.', FALSE, 'active');

-- Insert Sample Products
INSERT INTO products (name, slug, description, short_description, sku, category_id, collection_id, base_price, sale_price, weight_grams, material, gemstone, metal_type, metal_purity, stock_quantity, is_featured, is_bestseller, is_new_arrival, status) VALUES
('Royal Opal Engagement Ring', 'royal-opal-engagement-ring', 'A breathtaking engagement ring featuring a stunning Australian black opal set in 18K yellow gold. The opal displays a magnificent play of colour with vibrant flashes of green, blue, and red. Surrounded by a halo of brilliant-cut diamonds, this ring is a true masterpiece of Australian jewellery craftsmanship. Each opal is ethically sourced from Lightning Ridge, New South Wales, ensuring both beauty and responsibility.', 'Stunning black opal engagement ring with diamond halo in 18K gold', 'BGAI-R001', 1, 1, 4850.00, 4299.00, 4.8, '18K Yellow Gold', 'Australian Black Opal', 'Gold', '18K', 15, TRUE, TRUE, FALSE, 'active'),
('Diamond Solstice Pendant Necklace', 'diamond-solstice-pendant-necklace', 'An exquisite pendant necklace featuring a 1.5-carat brilliant-cut diamond suspended from a delicate 18K white gold chain. The diamond is GIA certified with excellent cut, D colour, and VS1 clarity. Inspired by the brilliance of the Australian sun, this pendant captures light from every angle, creating a dazzling display of sparkle. The chain is adjustable from 16 to 18 inches.', '1.5ct brilliant diamond pendant on 18K white gold chain', 'BGAI-N001', 2, 2, 6200.00, NULL, 3.2, '18K White Gold', 'Diamond (1.5ct GIA Certified)', 'White Gold', '18K', 8, TRUE, FALSE, TRUE, 'active'),
('Outback Gold Bracelet', 'outback-gold-bracelet', 'A handcrafted bracelet inspired by the flowing patterns of the Australian outback. Made from solid 9K yellow gold, this piece features organic, fluid lines that wrap gracefully around the wrist. The design incorporates subtle textures reminiscent of wind-sculpted sandstone, making each piece uniquely beautiful. Width: 8mm, adjustable fit.', 'Handcrafted 9K gold bracelet with outback-inspired organic design', 'BGAI-B001', 3, 3, 1890.00, 1690.00, 12.5, '9K Yellow Gold', 'None', 'Gold', '9K', 20, FALSE, TRUE, FALSE, 'active'),
('Pearl Harbour Drop Earrings', 'pearl-harbour-drop-earrings', 'Elegant drop earrings featuring lustrous South Sea pearls from Australian waters. Each pearl measures 12-13mm with excellent lustre and a subtle rose overtone. Set in 18K rose gold with delicate diamond accents, these earrings embody sophistication and grace. The pearls are hand-selected for their perfect shape and colour.', 'South Sea pearl drop earrings in 18K rose gold with diamonds', 'BGAI-E001', 4, 4, 3450.00, NULL, 6.8, '18K Rose Gold', 'Australian South Sea Pearls', 'Rose Gold', '18K', 12, TRUE, FALSE, TRUE, 'active'),
('Sapphire Coast Ring', 'sapphire-coast-ring', 'A stunning cocktail ring featuring a deep blue Australian sapphire weighing 2.3 carats. The sapphire is surrounded by a pavé setting of brilliant-cut diamonds in platinum. The rich blue hue of the sapphire, sourced from the Sapphire Coast region, is truly captivating. This statement piece is perfect for special occasions and formal events.', '2.3ct Australian sapphire ring with diamond pavé in platinum', 'BGAI-R002', 1, 5, 7800.00, NULL, 5.2, 'Platinum', 'Australian Sapphire (2.3ct)', 'Platinum', '950', 5, TRUE, FALSE, FALSE, 'active'),
('Opal Dream Tennis Bracelet', 'opal-dream-tennis-bracelet', 'A magnificent tennis bracelet featuring 25 perfectly matched Australian crystal opals, each displaying vibrant flashes of colour. Set in 14K yellow gold with a secure box clasp and safety catch. The opals are graduated from 5mm to 7mm, creating a beautiful flowing effect on the wrist. A true statement piece for the discerning collector.', 'Australian crystal opal tennis bracelet in 14K yellow gold', 'BGAI-B002', 3, 1, 9500.00, 8500.00, 18.0, '14K Yellow Gold', 'Australian Crystal Opals', 'Gold', '14K', 3, FALSE, FALSE, TRUE, 'active'),
('Diamond Solstice Stud Earrings', 'diamond-solstice-stud-earrings', 'Classic solitaire stud earrings featuring 1-carat total weight of brilliant-cut diamonds. Set in 18K white gold with secure butterfly backs, these versatile earrings are perfect for everyday wear. The diamonds are H colour and SI1 clarity, offering exceptional brilliance at an outstanding value. A timeless addition to any jewellery collection.', '1ct total weight diamond solitaire studs in 18K white gold', 'BGAI-E002', 4, 2, 2800.00, 2499.00, 2.1, '18K White Gold', 'Diamonds (1ct total)', 'White Gold', '18K', 25, FALSE, TRUE, FALSE, 'active'),
('Outback Gold Pendant', 'outback-gold-pendant', 'A striking pendant inspired by the iconic shapes of the Australian landscape. Crafted in 18K yellow gold, this pendant features a stylised boomerang motif adorned with a brilliant-cut diamond. The design represents journey and return, making it a meaningful gift. Comes with a 20-inch 18K gold chain.', '18K gold boomerang pendant with diamond accent', 'BGAI-P001', 5, 3, 1350.00, NULL, 5.5, '18K Yellow Gold', 'Diamond (0.15ct)', 'Gold', '18K', 18, FALSE, FALSE, TRUE, 'active'),
('Pearl Harbour Necklace', 'pearl-harbour-necklace', 'A showstopping necklace featuring a strand of perfectly matched Australian South Sea pearls. Each pearl measures 11-12mm with silver-white colour and excellent lustre. The strand is hand-knotted between each pearl for security and drape. Completed with an 18K white gold clasp set with diamonds.', 'Australian South Sea pearl strand necklace in silver-white', 'BGAI-N002', 2, 4, 12500.00, NULL, 45.0, '18K White Gold', 'Australian South Sea Pearls', 'White Gold', '18K', 4, TRUE, FALSE, FALSE, 'active'),
('Opal and Diamond Brooch', 'opal-and-diamond-brooch', 'An artisan brooch featuring a spectacular Australian boulder opal set amidst a cascade of brilliant-cut diamonds. The organic, nature-inspired design mimics a flowering gum blossom, one of Australia\'s most beloved native flowers. Set in 18K yellow gold, this brooch is both a work of art and a wearable treasure.', 'Australian boulder opal brooch with diamond accents, gum blossom design', 'BGAI-BR001', 6, 1, 5600.00, 4999.00, 8.5, '18K Yellow Gold', 'Australian Boulder Opal', 'Gold', '18K', 7, FALSE, FALSE, TRUE, 'active'),
('Classic Gold Wedding Band', 'classic-gold-wedding-band', 'A timeless wedding band crafted from solid 18K yellow gold. This classic comfort-fit design features a polished finish that will never go out of style. The 4mm width makes it perfect for both men and women. Each band is hallmarked and comes with a certificate of authenticity. Made in Australia with ethically sourced gold.', '18K yellow gold classic comfort-fit wedding band', 'BGAI-R003', 1, 3, 890.00, NULL, 4.0, '18K Yellow Gold', 'None', 'Gold', '18K', 50, FALSE, TRUE, FALSE, 'active'),
('Sapphire and Diamond Earrings', 'sapphire-and-diamond-earrings', 'Elegant halo earrings featuring Australian blue sapphires surrounded by brilliant-cut diamonds. The sapphires total 1.8 carats and the diamonds total 0.6 carats. Set in 18K white gold with secure push-back fittings. The deep blue of the sapphires creates a stunning contrast against the sparkle of the diamonds.', 'Australian sapphire and diamond halo earrings in 18K white gold', 'BGAI-E003', 4, 5, 4200.00, NULL, 4.2, '18K White Gold', 'Australian Sapphires', 'White Gold', '18K', 10, TRUE, FALSE, FALSE, 'active');

-- Insert Product Images
INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) VALUES
(1, 'assets/images/products/ring-1.jpg', 'Royal Opal Engagement Ring', TRUE, 1),
(2, 'assets/images/products/necklace-1.jpg', 'Diamond Solstice Pendant Necklace', TRUE, 1),
(3, 'assets/images/products/bracelet-1.jpg', 'Outback Gold Bracelet', TRUE, 1),
(4, 'assets/images/products/earring-1.jpg', 'Pearl Harbour Drop Earrings', TRUE, 1),
(5, 'assets/images/products/ring-2.jpg', 'Sapphire Coast Ring', TRUE, 1),
(6, 'assets/images/products/bracelet-2.jpg', 'Opal Dream Tennis Bracelet', TRUE, 1),
(7, 'assets/images/products/earring-2.jpg', 'Diamond Solstice Stud Earrings', TRUE, 1),
(8, 'assets/images/products/pendant-1.jpg', 'Outback Gold Pendant', TRUE, 1),
(9, 'assets/images/products/necklace-2.jpg', 'Pearl Harbour Necklace', TRUE, 1),
(10, 'assets/images/products/brooch-1.jpg', 'Opal and Diamond Brooch', TRUE, 1),
(11, 'assets/images/products/ring-3.jpg', 'Classic Gold Wedding Band', TRUE, 1),
(12, 'assets/images/products/earring-3.jpg', 'Sapphire and Diamond Earrings', TRUE, 1);

-- Insert Country Pricing
INSERT INTO country_pricing (product_id, country_code, currency_code, price, sale_price, tax_rate, shipping_cost) VALUES
-- Australia (AUD)
(1, 'AU', 'AUD', 4850.00, 4299.00, 10.00, 0.00),
(2, 'AU', 'AUD', 6200.00, NULL, 10.00, 0.00),
(3, 'AU', 'AUD', 1890.00, 1690.00, 10.00, 0.00),
(4, 'AU', 'AUD', 3450.00, NULL, 10.00, 0.00),
(5, 'AU', 'AUD', 7800.00, NULL, 10.00, 0.00),
(6, 'AU', 'AUD', 9500.00, 8500.00, 10.00, 0.00),
(7, 'AU', 'AUD', 2800.00, 2499.00, 10.00, 0.00),
(8, 'AU', 'AUD', 1350.00, NULL, 10.00, 0.00),
(9, 'AU', 'AUD', 12500.00, NULL, 10.00, 0.00),
(10, 'AU', 'AUD', 5600.00, 4999.00, 10.00, 0.00),
(11, 'AU', 'AUD', 890.00, NULL, 10.00, 0.00),
(12, 'AU', 'AUD', 4200.00, NULL, 10.00, 0.00),
-- United States (USD)
(1, 'US', 'USD', 3299.00, 2919.00, 0.00, 25.00),
(2, 'US', 'USD', 4215.00, NULL, 0.00, 25.00),
(3, 'US', 'USD', 1285.00, 1149.00, 0.00, 25.00),
(4, 'US', 'USD', 2345.00, NULL, 0.00, 25.00),
(5, 'US', 'USD', 5305.00, NULL, 0.00, 25.00),
(6, 'US', 'USD', 6460.00, 5779.00, 0.00, 25.00),
(7, 'US', 'USD', 1904.00, 1699.00, 0.00, 25.00),
(8, 'US', 'USD', 918.00, NULL, 0.00, 25.00),
(9, 'US', 'USD', 8500.00, NULL, 0.00, 25.00),
(10, 'US', 'USD', 3808.00, 3399.00, 0.00, 25.00),
(11, 'US', 'USD', 605.00, NULL, 0.00, 25.00),
(12, 'US', 'USD', 2856.00, NULL, 0.00, 25.00),
-- United Kingdom (GBP)
(1, 'GB', 'GBP', 2599.00, 2309.00, 20.00, 15.00),
(2, 'GB', 'GBP', 3320.00, NULL, 20.00, 15.00),
(3, 'GB', 'GBP', 1012.00, 899.00, 20.00, 15.00),
(4, 'GB', 'GBP', 1848.00, NULL, 20.00, 15.00),
(5, 'GB', 'GBP', 4180.00, NULL, 20.00, 15.00),
(6, 'GB', 'GBP', 5090.00, 4539.00, 20.00, 15.00),
(7, 'GB', 'GBP', 1500.00, 1339.00, 20.00, 15.00),
(8, 'GB', 'GBP', 723.00, NULL, 20.00, 15.00),
(9, 'GB', 'GBP', 6700.00, NULL, 20.00, 15.00),
(10, 'GB', 'GBP', 3000.00, 2679.00, 20.00, 15.00),
(11, 'GB', 'GBP', 477.00, NULL, 20.00, 15.00),
(12, 'GB', 'GBP', 2255.00, NULL, 20.00, 15.00),
-- India (INR)
(1, 'IN', 'INR', 274000.00, 242999.00, 3.00, 35.00),
(2, 'IN', 'INR', 350400.00, NULL, 3.00, 35.00),
(3, 'IN', 'INR', 106800.00, 95499.00, 3.00, 35.00),
(4, 'IN', 'INR', 194800.00, NULL, 3.00, 35.00),
(5, 'IN', 'INR', 440600.00, NULL, 3.00, 35.00),
(6, 'IN', 'INR', 536900.00, 479999.00, 3.00, 35.00),
(7, 'IN', 'INR', 158200.00, 141399.00, 3.00, 35.00),
(8, 'IN', 'INR', 76200.00, NULL, 3.00, 35.00),
(9, 'IN', 'INR', 705800.00, NULL, 3.00, 35.00),
(10, 'IN', 'INR', 316600.00, 282499.00, 3.00, 35.00),
(11, 'IN', 'INR', 50300.00, NULL, 3.00, 35.00),
(12, 'IN', 'INR', 237500.00, NULL, 3.00, 35.00),
-- UAE (AED)
(1, 'AE', 'AED', 12125.00, 10749.00, 5.00, 20.00),
(2, 'AE', 'AED', 15500.00, NULL, 5.00, 20.00),
(3, 'AE', 'AED', 4725.00, 4225.00, 5.00, 20.00),
(4, 'AE', 'AED', 8625.00, NULL, 5.00, 20.00),
(5, 'AE', 'AED', 19500.00, NULL, 5.00, 20.00),
(6, 'AE', 'AED', 23750.00, 21250.00, 5.00, 20.00),
(7, 'AE', 'AED', 7000.00, 6249.00, 5.00, 20.00),
(8, 'AE', 'AED', 3375.00, NULL, 5.00, 20.00),
(9, 'AE', 'AED', 31250.00, NULL, 5.00, 20.00),
(10, 'AE', 'AED', 14000.00, 12499.00, 5.00, 20.00),
(11, 'AE', 'AED', 2225.00, NULL, 5.00, 20.00),
(12, 'AE', 'AED', 10500.00, NULL, 5.00, 20.00);

-- Insert Admin User (password: admin123)
-- NOTE: This hash is generated with password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (first_name, last_name, email, phone, password, role, status, country_code, currency_preference) VALUES
('Admin', 'BGAI', 'admin@bgai.com.au', '+61 2 8000 0000', '$2y$10$jxUrXCA8gwHnTQRIEUyMY.pTsqMlXrWG3S4dHetc56N0DOFsKhJvi', 'admin', 'active', 'AU', 'AUD');

-- Insert Sample Customer (password: password)
INSERT INTO users (first_name, last_name, email, phone, password, address_line1, city, state, postal_code, country, country_code, currency_preference, role, status) VALUES
('Sarah', 'Mitchell', 'sarah.mitchell@email.com', '+61 412 345 678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '42 Harbour View Drive', 'Sydney', 'NSW', '2000', 'Australia', 'AU', 'AUD', 'customer', 'active'),
('James', 'Thompson', 'james.t@email.com', '+1 555 234 5678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '128 Ocean Boulevard', 'Malibu', 'CA', '90265', 'United States', 'US', 'USD', 'customer', 'active');

-- Insert Sample Orders
INSERT INTO orders (order_number, user_id, first_name, last_name, email, phone, shipping_address_line1, shipping_city, shipping_state, shipping_postal_code, shipping_country, shipping_country_code, currency_code, subtotal, tax_amount, shipping_cost, total_amount, payment_method, payment_status, order_status) VALUES
('BGAI-2026-0001', 2, 'Sarah', 'Mitchell', 'sarah.mitchell@email.com', '+61 412 345 678', '42 Harbour View Drive', 'Sydney', 'NSW', '2000', 'Australia', 'AU', 'AUD', 4299.00, 429.90, 0.00, 4728.90, 'Credit Card', 'paid', 'delivered'),
('BGAI-2026-0002', 2, 'Sarah', 'Mitchell', 'sarah.mitchell@email.com', '+61 412 345 678', '42 Harbour View Drive', 'Sydney', 'NSW', '2000', 'Australia', 'AU', 'AUD', 2499.00, 249.90, 0.00, 2748.90, 'PayPal', 'paid', 'shipped'),
('BGAI-2026-0003', 3, 'James', 'Thompson', 'james.t@email.com', '+1 555 234 5678', '128 Ocean Boulevard', 'Malibu', 'CA', '90265', 'United States', 'US', 'USD', 2919.00, 0.00, 25.00, 2944.00, 'Credit Card', 'paid', 'processing'),
('BGAI-2026-0004', NULL, 'Emma', 'Watson', 'emma.w@email.com', '+44 20 7946 0958', '15 Kensington Gardens', 'London', '', 'W8 4PX', 'United Kingdom', 'GB', 'GBP', 4180.00, 836.00, 15.00, 5031.00, 'Credit Card', 'processing', 'confirmed');

-- Insert Sample Order Items
INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) VALUES
(1, 1, 'Royal Opal Engagement Ring', 'BGAI-R001', 1, 4299.00, 4299.00),
(2, 7, 'Diamond Solstice Stud Earrings', 'BGAI-E002', 1, 2499.00, 2499.00),
(3, 1, 'Royal Opal Engagement Ring', 'BGAI-R001', 1, 2919.00, 2919.00),
(4, 5, 'Sapphire Coast Ring', 'BGAI-R002', 1, 4180.00, 4180.00);

-- Insert Site Settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'BGAI - Brilliance, Gems of Australia International'),
('site_tagline', 'Exquisite Australian Jewellery'),
('site_description', 'Discover the finest Australian jewellery featuring opals, diamonds, sapphires, and South Sea pearls.'),
('currency_default', 'AUD'),
('country_default', 'AU'),
('shipping_free_threshold', '500'),
('tax_inclusive', '1'),
('contact_email', 'hello@bgai.com.au'),
('contact_phone', '+61 2 8000 0000'),
('contact_address', 'Level 15, 1 Martin Place, Sydney NSW 2000, Australia');
