<?php 
session_start();
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BGAI - Brilliance, Gems of Australia International</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">

<style>
:root { --gold:#c5a05b; --dark:#111; }

body {
    font-family:'Inter', sans-serif;
    background:var(--dark);
    color:#eee;
}

.logo {
    font-family:'Playfair Display', serif;
    font-size:2rem;
    color:var(--gold);
    font-weight:700;
}

.gold-btn {
    background:linear-gradient(90deg,#c5a05b,#e8c77a);
    color:#111;
    border:none;
    font-weight:600;
}
.gold-btn:hover { transform:scale(1.05); }

.product-card {
    background:#1a1a1a;
    border-radius:16px;
    overflow:hidden;
    transition:0.3s;
}
.product-card:hover {
    transform:translateY(-8px);
    box-shadow:0 20px 25px rgba(197,160,91,0.2);
}

.category-badge {
    background:rgba(197,160,91,0.15);
    color:var(--gold);
    font-size:0.75rem;
    padding:4px 12px;
    border-radius:30px;
}

.price {
    color:var(--gold);
    font-family:'Playfair Display', serif;
    font-size:1.6rem;
}

.text-gold { color:var(--gold); }

* { transition:all 0.2s ease; }
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background:rgba(17,17,17,0.95); backdrop-filter:blur(10px);">
<div class="container">

<a class="navbar-brand logo" href="index.php">BGAI</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarNav">

<ul class="navbar-nav mx-auto">
<li class="nav-item"><a class="nav-link px-3" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link px-3" href="shop.php">Shop</a></li>
<li class="nav-item"><a class="nav-link px-3" href="account.php">Account</a></li>
</ul>

<div class="d-flex align-items-center gap-3">

<select id="countrySelector" class="form-select form-select-sm bg-dark text-white" style="width:150px;" onchange="changeCountry()">
<option value="AU" data-rate="1" data-symbol="A$">🇦🇺 AUD</option>
<option value="US" data-rate="0.65" data-symbol="$">🇺🇸 USD</option>
<option value="IN" data-rate="55" data-symbol="₹" selected>🇮🇳 INR</option>
<option value="GB" data-rate="0.52" data-symbol="£">🇬🇧 GBP</option>
</select>

<a href="cart.php" class="btn btn-outline-light position-relative">
<i class="fa-solid fa-cart-shopping"></i>
<span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge bg-warning text-dark">0</span>
</a>

<?php if (isset($_SESSION['user_id'])): ?>
<a href="account.php" class="text-white"><i class="fa-solid fa-circle-user fa-2x"></i></a>
<?php else: ?>
<a href="login.php" class="btn btn-sm gold-btn px-4">Login</a>
<?php endif; ?>

</div>
</div>
</div>
</nav>

<script>
function updateCartCount(){
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const el = document.getElementById('cartCount');
    if(el){
        el.textContent = cart.reduce((sum,i)=>sum+(i.qty||1),0);
    }
}

function changeCountry(){
    const sel=document.getElementById('countrySelector');
    const opt=sel.options[sel.selectedIndex];

    sessionStorage.setItem('rate', opt.dataset.rate);
    sessionStorage.setItem('symbol', opt.dataset.symbol);
    sessionStorage.setItem('country', opt.value);

    location.reload();
}

window.addEventListener('load', ()=>{
    updateCartCount();

    const saved=sessionStorage.getItem('country');
    if(saved){
        const sel=document.getElementById('countrySelector');
        for(let o of sel.options){
            if(o.value===saved){
                o.selected=true;
                sessionStorage.setItem('rate', o.dataset.rate);
                sessionStorage.setItem('symbol', o.dataset.symbol);
            }
        }
    }
});
</script>