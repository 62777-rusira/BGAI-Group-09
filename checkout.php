<?php 
require 'includes/db.php';

// ✅ check BEFORE output
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'includes/header.php';
?>

<div class="container py-5">

<h2 class="text-center mb-5" style="font-family:'Playfair Display', serif;">
💳 Checkout
</h2>

<div class="row g-5">

<!-- LEFT: FORM -->
<div class="col-md-7">

<div class="card bg-dark border-0 p-4 shadow-sm">

<h5 class="mb-4">Shipping Details</h5>

<form id="checkoutForm">

<input type="text" class="form-control mb-3" placeholder="Full Name" required>

<input type="email" class="form-control mb-3" 
value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" 
placeholder="Email" required>

<textarea class="form-control mb-3" placeholder="Full Address" required></textarea>

<select class="form-control mb-3">
<option>Cash on Delivery (Demo)</option>
<option>Card Payment (Demo)</option>
</select>

<button type="button" onclick="processPayment()" 
class="btn gold-btn w-100 btn-lg">
Pay Now
</button>

</form>

</div>
</div>

<!-- RIGHT: ORDER SUMMARY -->
<div class="col-md-5">

<div class="card bg-dark border-0 p-4 shadow-sm">

<h5 class="mb-4">Order Summary</h5>

<div id="orderSummary"></div>

<hr>

<h5 class="d-flex justify-content-between">
<span>Total</span>
<span id="orderTotal" class="price">₹0</span>
</h5>

</div>

</div>

</div>
</div>

<script>
// Render Order Summary
function renderSummary() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    const container = document.getElementById('orderSummary');

    if (cart.length === 0) {
        container.innerHTML = `
            <p class="text-center text-white-50">
                Your cart is empty.
            </p>
            <a href="shop.php" class="btn gold-btn w-100 mt-2">
                Go Shopping
            </a>`;
        return;
    }

    const rate = parseFloat(sessionStorage.getItem('rate') || 55);
    const symbol = sessionStorage.getItem('symbol') || '₹';

    let html = '';
    let totalAud = 0;

    cart.forEach(item => {
        const price = item.priceAud * rate;
        totalAud += item.priceAud * item.qty;

        html += `
        <div class="d-flex align-items-center mb-3 gap-3">

            <img src="${item.image || 'upload/ring-diamond-solitaire.jpg'}"
                 style="width:60px;height:60px;object-fit:cover;border-radius:10px;">

            <div class="flex-grow-1">
                <small>${item.name}</small><br>
                <small class="text-white-50">
                    Qty: ${item.qty}
                </small>
            </div>

            <div>
                ${symbol}${(price * item.qty).toFixed(0)}
            </div>

        </div>`;
    });

    container.innerHTML = html;

    document.getElementById('orderTotal').innerText =
        symbol + (totalAud * rate).toFixed(0);
}

// Process Payment (Demo + DB Save)
function processPayment() {

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
    }

    // Basic validation
    const form = document.getElementById('checkoutForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Send to backend
    fetch('save-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart })
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {

            // Clear cart
            localStorage.setItem('cart', '[]');

            // Redirect
            window.location.href = 'account.php?success=1';

        } else {
            alert("Order failed. Try again.");
        }
    })
    .catch(() => {
        alert("Network error!");
    });
}

// Init
window.onload = renderSummary;
</script>

<?php require 'includes/footer.php'; ?>