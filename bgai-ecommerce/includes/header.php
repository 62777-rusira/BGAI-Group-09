<?php
// ============================================
// BGAI Header Include
// ============================================
require_once __DIR__ . '/../config/app.php';
$categories = getCategories();
$cartCount = getCartCount();
$userName = $_SESSION['user_name'] ?? null;
$isLoggedIn = isLoggedIn();
$isAdminUser = isAdmin();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e(getSetting('site_description', 'BGAI - Exquisite Australian Jewellery featuring opals, diamonds, sapphires, and South Sea pearls.')); ?>">
    <title><?php echo e($pageTitle ?? 'BGAI'); ?> | Brilliance, Gems of Australia International</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    
    <!-- Pass APP_URL to JavaScript -->
    <script>window.APP_URL = '<?php echo APP_URL; ?>';</script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/images/favicon.png">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container top-bar-inner">
            <div class="top-bar-left">
                <span><i class="fas fa-phone"></i> +61 2 8000 0000</span>
                <span><i class="fas fa-envelope"></i> hello@bgai.com.au</span>
            </div>
            <div class="top-bar-right">
                <div class="country-selector" id="countrySelector">
                    <button class="country-btn" onclick="toggleCountryDropdown()">
                        <i class="fas fa-globe"></i>
                        <span id="currentCurrency"><?php echo e($_SESSION['currency_code'] ?? DEFAULT_CURRENCY); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="country-dropdown" id="countryDropdown">
                        <button onclick="changeCountry('AU', 'AUD')"><span>🇦🇺</span> Australia (AUD)</button>
                        <button onclick="changeCountry('US', 'USD')"><span>🇺🇸</span> United States (USD)</button>
                        <button onclick="changeCountry('GB', 'GBP')"><span>🇬🇧</span> United Kingdom (GBP)</button>
                        <button onclick="changeCountry('IN', 'INR')"><span>🇮🇳</span> India (INR)</button>
                        <button onclick="changeCountry('AE', 'AED')"><span>🇦🇪</span> UAE (AED)</button>
                    </div>
                </div>
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo APP_URL; ?>/profile.php"><i class="fas fa-user"></i> <?php echo e(explode(' ', $userName)[0]); ?></a>
                    <?php if ($isAdminUser): ?>
                        <a href="<?php echo APP_URL; ?>/admin/"><i class="fas fa-cog"></i> Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo APP_URL; ?>/api/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a>
                    <a href="<?php echo APP_URL; ?>/register.php"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <header class="main-header" id="mainHeader">
        <div class="container header-inner">
            <a href="<?php echo APP_URL; ?>" class="logo">
                <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="BGAI Logo" class="logo-img">
                <div class="logo-text">
                    <span class="logo-name">BGAI</span>
                    <span class="logo-subtitle">Brilliance, Gems of Australia</span>
                </div>
            </a>
            
            <nav class="main-nav" id="mainNav">
                <ul class="nav-list">
                    <li><a href="<?php echo APP_URL; ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">Home</a></li>
                    <li class="nav-dropdown">
                        <a href="<?php echo APP_URL; ?>/collections.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'collections.php') ? 'active' : ''; ?>">
                            Collections <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <?php $collections = getCollections(); ?>
                            <?php foreach ($collections as $col): ?>
                                <a href="<?php echo APP_URL; ?>/collections.php?slug=<?php echo e($col['slug']); ?>"><?php echo e($col['name']); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li class="nav-dropdown">
                        <a href="<?php echo APP_URL; ?>/products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'products.php') ? 'active' : ''; ?>">
                            Shop <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo e($cat['slug']); ?>"><?php echo e($cat['name']); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li><a href="<?php echo APP_URL; ?>/about.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'about.php') ? 'active' : ''; ?>">About</a></li>
                    <li><a href="<?php echo APP_URL; ?>/contact.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <button class="search-toggle" onclick="toggleSearch()"><i class="fas fa-search"></i></button>
                <a href="<?php echo APP_URL; ?>/cart.php" class="cart-btn">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                </a>
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="search-bar" id="searchBar">
            <div class="container">
                <form action="<?php echo APP_URL; ?>/products.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search for rings, necklaces, opals, diamonds..." autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i></button>
                    <button type="button" class="search-close" onclick="toggleSearch()"><i class="fas fa-times"></i></button>
                </form>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage">
            <div class="container">
                <span><?php echo e($flash['message']); ?></span>
                <button onclick="this.parentElement.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <span class="logo-name">BGAI</span>
            <button onclick="toggleMobileMenu()"><i class="fas fa-times"></i></button>
        </div>
        <div class="mobile-menu-body">
            <a href="<?php echo APP_URL; ?>">Home</a>
            <a href="<?php echo APP_URL; ?>/collections.php">Collections</a>
            <?php foreach ($collections ?? getCollections() as $col): ?>
                <a href="<?php echo APP_URL; ?>/collections.php?slug=<?php echo e($col['slug']); ?>" class="sub-link"><?php echo e($col['name']); ?></a>
            <?php endforeach; ?>
            <a href="<?php echo APP_URL; ?>/products.php">Shop All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo e($cat['slug']); ?>" class="sub-link"><?php echo e($cat['name']); ?></a>
            <?php endforeach; ?>
            <a href="<?php echo APP_URL; ?>/about.php">About</a>
            <a href="<?php echo APP_URL; ?>/contact.php">Contact</a>
            <hr>
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo APP_URL; ?>/profile.php"><i class="fas fa-user"></i> My Account</a>
                <?php if ($isAdminUser): ?>
                    <a href="<?php echo APP_URL; ?>/admin/"><i class="fas fa-cog"></i> Admin Panel</a>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>/api/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>/login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a>
                <a href="<?php echo APP_URL; ?>/register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <main>
