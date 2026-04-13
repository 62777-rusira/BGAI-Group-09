<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BGAI - Brilliance, Gems of Australia International | Premium Jewellery E-commerce">
    <title>BGAI - Brilliance, Gems of Australia International</title>
    
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts - Playfair Display for luxury feel + Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&amp;family=Inter:wght@400;500&amp;display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gold: #c5a05b;
            --dark: #111111;
            --light: #f8f1e3;
        }
        
        * {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        
        body {
            font-family: 'Inter', system_ui, sans-serif;
            background-color: var(--dark);
            color: #eee;
        }
        
        .navbar {
            background-color: rgba(17, 17, 17, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--gold);
            text-shadow: 0 0 20px rgba(197, 160, 91, 0.3);
        }
        
        .hero {
            background: linear-gradient(rgba(17, 17, 17, 0.7), rgba(17, 17, 17, 0.7)), url('uploads/ring_background.jpg') center/cover no-repeat;
            height: 100vh;
            min-height: 600px;
            display: flex;
            align-items: center;
        }
        
        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            background: #1a1a1a;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgb(197 160 91 / 0.2);
        }
        
        .gold-btn {
            background: linear-gradient(90deg, #c5a05b, #e8c77a);
            color: #111;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            padding: 12px 32px;
        }
        
        .gold-btn:hover {
            background: linear-gradient(90deg, #e8c77a, #c5a05b);
            transform: scale(1.05);
        }
        
        .price {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--gold);
        }
        
        .category-badge {
            background: rgba(197, 160, 91, 0.15);
            color: var(--gold);
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 30px;
        }
        
        .footer {
            background: #0a0a0a;
        }
        
        .modal-content {
            background: #1a1a1a;
            color: #eee;
        }
    </style>
</head>
<body>

<!-- ====================== NAVBAR ====================== -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="uploads/logo.png" alt="BGAI" style="height:40px;">
            <span class="ms-2 fs-6 fw-light text-white-50">Brilliance, Gems of Australia International</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link px-3" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="shop.php">Shop</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3" href="#" role="button" data-bs-toggle="dropdown">
                        Collections
                    </a>
                    <ul class="dropdown-menu bg-dark border-0 shadow">
                        <li><a class="dropdown-item text-white" href="shop.php?category=Rings">Rings</a></li>
                        <li><a class="dropdown-item text-white" href="shop.php?category=Necklaces">Necklaces</a></li>
                        <li><a class="dropdown-item text-white" href="shop.php?category=Bracelets">Bracelets</a></li>
                        <li><a class="dropdown-item text-white" href="shop.php?category=Earrings">Earrings</a></li>
                        <li><a class="dropdown-item text-white" href="shop.php?category=Pendants">Pendants</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link px-3" href="account.php">My Account</a></li>
            </ul>

            <!-- Country Selector + Currency -->
            <div class="d-flex align-items-center me-3">
                <select id="countrySelector" class="form-select form-select-sm bg-dark text-white border-gold" style="width: 140px;" onchange="changeCountry()">
                    <option value="AU" data-rate="1" data-symbol="A$" data-tax="0.10">🇦🇺 Australia (AUD)</option>
                    <option value="US" data-rate="0.65" data-symbol="$" data-tax="0.08">🇺🇸 USA (USD)</option>
                    <option value="IN" data-rate="55" data-symbol="₹" data-tax="0.18" selected>🇮🇳 India (INR)</option>
                    <option value="GB" data-rate="0.52" data-symbol="£" data-tax="0.20">🇬🇧 UK (GBP)</option>
                    <option value="EU" data-rate="0.60" data-symbol="€" data-tax="0.19">🇪🇺 Europe (EUR)</option>
                </select>
            </div>

            <!-- Cart -->
            <a href="cart.php" class="btn btn-outline-light position-relative me-3">
                <i class="fa-solid fa-shopping-cart"></i>
                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">0</span>
            </a>

            <!-- User -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="account.php" class="text-white d-flex align-items-center">
                    <i class="fa-solid fa-circle-user fs-4 me-1"></i>
                    <span class="small"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm gold-btn">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ====================== HERO ====================== -->
<section class="hero text-center text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3" style="font-family: 'Playfair Display', serif; letter-spacing: -2px;">
                    Brilliance from the Heart of Australia
                </h1>
                <p class="lead mb-5 fs-4">Discover ethically sourced diamonds, opals &amp; rare gems. Handcrafted luxury jewellery delivered worldwide.</p>
                
                <a href="shop.php" class="btn gold-btn btn-lg px-5 py-3 fs-5 shadow-lg">
                    <i class="fa-solid fa-gem me-2"></i> Explore Collections
                </a>
                
                <div class="mt-5 d-flex justify-content-center gap-4 text-white-50">
                    <div><i class="fa-solid fa-shield-halved me-1"></i> 2-Year Warranty</div>
                    <div><i class="fa-solid fa-truck-fast me-1"></i> Free Global Shipping over $500</div>
                    <div><i class="fa-solid fa-rotate-left me-1"></i> 30-Day Returns</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================== FEATURED PRODUCTS ====================== -->
<section class="py-5 bg-dark">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="fs-1 fw-bold" style="font-family: 'Playfair Display', serif;">Featured Jewellery</h2>
            <a href="shop.php" class="text-gold text-decoration-underline">View all collections →</a>
        </div>
        
        <div class="row" id="featuredProducts">
            <!-- Populated via PHP / JS below -->
        </div>
    </div>
</section>

<!-- ====================== CATEGORIES ====================== -->
<section class="py-5" style="background: linear-gradient(#1a1a1a, #111111);">
    <div class="container">
        <h2 class="text-center mb-5 fs-1 fw-bold" style="font-family: 'Playfair Display', serif;">Shop by Category</h2>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <a href="shop.php?category=Rings" class="text-decoration-none">
                    <div class="card product-card text-center h-100">
                        <img src="uploads/ring-diamond-solitaire.jpg" class="card-img-top" alt="Rings" style="height: 260px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="category-badge d-inline-block mb-2">RINGS</h5>
                            <p class="text-gold">Timeless symbols of love &amp; commitment</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="shop.php?category=Necklaces" class="text-decoration-none">
                    <div class="card product-card text-center h-100">
                        <img src="uploads/necklace-ruby-pendant.jpg" class="card-img-top" alt="Necklaces" style="height: 260px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="category-badge d-inline-block mb-2">NECKLACES</h5>
                            <p class="text-gold">Elegance that rests close to your heart</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="shop.php?category=Bracelets" class="text-decoration-none">
                    <div class="card product-card text-center h-100">
                        <img src="uploads/bracelet-diamond-tennis.jpg" class="card-img-top" alt="Bracelets" style="height: 260px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="category-badge d-inline-block mb-2">BRACELETS</h5>
                            <p class="text-gold">Stackable Australian opals &amp; gold</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="shop.php?category=Earrings" class="text-decoration-none">
                    <div class="card product-card text-center h-100">
                        <img src="uploads/earrings.jpg" class="card-img-top" alt="Earrings" style="height: 260px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="category-badge d-inline-block mb-2">EARRINGS</h5>
                            <p class="text-gold">Sparkle that turns heads</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ====================== WHY BGAI ====================== -->
<section class="py-5 bg-dark border-top border-bottom border-gold border-opacity-25">
    <div class="container">
        <div class="row text-center g-5">
            <div class="col-md-4">
                <i class="fa-solid fa-gem fa-3x mb-3 text-gold"></i>
                <h5 class="fw-semibold">Ethically Sourced</h5>
                <p class="text-white-50">Every gem traced from Australian mines with full transparency.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-truck-fast fa-3x mb-3 text-gold"></i>
                <h5 class="fw-semibold">Global Delivery</h5>
                <p class="text-white-50">Shipped to 120+ countries with real-time tracking.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-lock fa-3x mb-3 text-gold"></i>
                <h5 class="fw-semibold">Secure Payments</h5>
                <p class="text-white-50">256-bit SSL • Stripe • Razorpay • PayPal supported.</p>
            </div>
        </div>
    </div>
</section>

<!-- ====================== FOOTER ====================== -->
<footer class="footer py-5 text-white-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="logo mb-2">BGAI</div>
                <p class="small">Brilliance, Gems of Australia International<br>Handcrafted luxury since 2018</p>
                <div class="mt-3">
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-instagram fa-2x"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-tiktok fa-2x"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-pinterest fa-2x"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="text-gold mb-3">Shop</h6>
                <ul class="list-unstyled small">
                    <li><a href="shop.php" class="text-white-50">All Jewellery</a></li>
                    <li><a href="shop.php?category=Rings" class="text-white-50">Rings</a></li>
                    <li><a href="shop.php?category=Necklaces" class="text-white-50">Necklaces</a></li>
                    <li><a href="shop.php?category=Bracelets" class="text-white-50">Bracelets</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="text-gold mb-3">Company</h6>
                <ul class="list-unstyled small">
                    <li><a href="#" class="text-white-50">Our Story</a></li>
                    <li><a href="#" class="text-white-50">Sustainability</a></li>
                    <li><a href="#" class="text-white-50">Care Guide</a></li>
                    <li><a href="account.php" class="text-white-50">Track Order</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-gold mb-3">Newsletter</h6>
                <p class="small">Get 10% off your first order + exclusive drops.</p>
                <div class="input-group">
                    <input type="email" class="form-control bg-dark border-0 text-white" placeholder="your@email.com">
                    <button class="btn gold-btn">Subscribe</button>
                </div>
                <p class="small mt-3">© 2026 BGAI. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
// Global country rates (updated live from selector)
let currentRate = 55;
let currentSymbol = '₹';
let currentTax = 0.18;
let currentCountry = 'IN';

// Load featured products (demo data + PHP will override in real shop.php)
const featured = [
    {id:1, name:"Eternal Diamond Ring", category:"Rings", priceAud:1499, image:"uploads/ring-sapphire-halo.jpg"},
    {id:2, name:"Opal Halo Necklace", category:"Necklaces", priceAud:899, image:"uploads/necklace-emerald-gold.jpg"},
    {id:3, name:"Australian Sapphire Bracelet", category:"Bracelets", priceAud:649, image:"uploads/bracelet-gold-diamond.jpg"},
    {id:4, name:"Rose Gold Drop Earrings", category:"Earrings", priceAud:399, image:"uploads/earrings.jpg"}
];

function renderFeatured() {
    const container = document.getElementById('featuredProducts');
    container.innerHTML = featured.map(p => `
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="product-card rounded-4 overflow-hidden">
                <img src="${p.image}" class="w-100" style="height:280px; object-fit:cover;" alt="${p.name}">
                <div class="p-4">
                    <span class="category-badge">${p.category}</span>
                    <h5 class="mt-3 mb-2">${p.name}</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <span class="price" id="price-${p.id}">${currentSymbol}${(p.priceAud * currentRate).toFixed(0)}</span>
                        </div>
                        <button onclick="quickAddToCart(${p.id}, '${p.name}', ${p.priceAud})" class="btn gold-btn btn-sm">
                            <i class="fa-solid fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Quick add to cart
function quickAddToCart(id, name, priceAud) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const existing = cart.find(item => item.id === id);
    
    if (existing) {
        existing.qty++;
    } else {
        cart.push({
            id: id,
            name: name,
            priceAud: priceAud,
            qty: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    
    // Toast
    const toast = document.createElement('div');
    toast.className = 'toast show position-fixed bottom-0 end-0 m-3 bg-success text-white';
    toast.style.zIndex = 9999;
    toast.innerHTML = `<div class="toast-body">✓ ${name} added to cart</div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    document.getElementById('cartCount').textContent = cart.reduce((sum, item) => sum + item.qty, 0);
}

function changeCountry() {
    const select = document.getElementById('countrySelector');
    const option = select.options[select.selectedIndex];
    
    currentRate = parseFloat(option.dataset.rate);
    currentSymbol = option.dataset.symbol;
    currentTax = parseFloat(option.dataset.tax);
    currentCountry = option.value;
    
    // Update all prices on page
    renderFeatured();
    
    // Store in sessionStorage so other pages can read
    sessionStorage.setItem('country', currentCountry);
    sessionStorage.setItem('rate', currentRate);
    sessionStorage.setItem('symbol', currentSymbol);
    sessionStorage.setItem('tax', currentTax);
    
    // Optional: you can AJAX to PHP to store in $_SESSION if needed
    console.log('🌍 Country changed to', currentCountry);
}

// Initialize
window.onload = function() {
    renderFeatured();
    updateCartCount();
    
    // Restore last selected country
    const savedCountry = sessionStorage.getItem('country');
    if (savedCountry) {
        const select = document.getElementById('countrySelector');
        for (let opt of select.options) {
            if (opt.value === savedCountry) {
                opt.selected = true;
                currentRate = parseFloat(opt.dataset.rate);
                currentSymbol = opt.dataset.symbol;
                currentTax = parseFloat(opt.dataset.tax);
                break;
            }
        }
    }
    renderFeatured();
};
</script>

</body>
</html>