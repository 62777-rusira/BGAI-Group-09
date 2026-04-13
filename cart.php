<?php require 'includes/header.php'; ?>

<div class="container py-5">

<h2 class="mb-4 text-center" style="font-family:'Playfair Display', serif;">
🛒 Your Cart
</h2>

<div id="cartItems" class="row"></div>

<!-- Total Section -->
<div class="text-end mt-4">
    <h4>
        Total: <span id="cartTotal" class="price">₹0</span>
    </h4>
    <a href="checkout.php" class="btn gold-btn btn-lg px-5 mt-2">
        Proceed to Checkout
    </a>
</div>

</div>

<script>
function renderCart() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const container = document.getElementById('cartItems');

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <h5>Your cart is empty 🛍️</h5>
                <a href="shop.php" class="btn gold-btn mt-3">Continue Shopping</a>
            </div>`;
        document.getElementById('cartTotal').innerText = '₹0';
        return;
    }

    const rate = parseFloat(sessionStorage.getItem('rate') || 55);
    const symbol = sessionStorage.getItem('symbol') || '₹';

    let html = '';
    let totalAud = 0;

    cart.forEach((item, index) => {
        const price = item.priceAud * rate;
        totalAud += item.priceAud * item.qty;

        html += `
        <div class="col-12 mb-3">
            <div class="card bg-dark border-0 shadow-sm">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between">

                    <!-- Left: Image + Info -->
                    <div class="d-flex align-items-center gap-3">
                        <img src="${item.image || 'upload/ring-diamond-solitaire.jpg'}"
                             style="width:80px;height:80px;object-fit:cover;border-radius:12px;">

                        <div>
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-white-50">
                                ${symbol}${price.toFixed(0)} each
                            </small>
                        </div>
                    </div>

                    <!-- Middle: Quantity Controls -->
                    <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
                        <button onclick="changeQty(${index}, -1)" class="btn btn-sm btn-outline-light">-</button>
                        <span>${item.qty}</span>
                        <button onclick="changeQty(${index}, 1)" class="btn btn-sm btn-outline-light">+</button>
                    </div>

                    <!-- Right: Price + Remove -->
                    <div class="text-end mt-3 mt-md-0">
                        <div class="price mb-2">
                            ${symbol}${(price * item.qty).toFixed(0)}
                        </div>
                        <button onclick="removeFromCart(${index})" class="btn btn-sm btn-outline-danger">
                            Remove
                        </button>
                    </div>

                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;

    // Total
    document.getElementById('cartTotal').innerText =
        symbol + (totalAud * rate).toFixed(0);
}

// ➕➖ Change Quantity
function changeQty(index, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    cart[index].qty += change;

    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

// ❌ Remove Item
function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);

    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

// Load Cart
window.onload = function () {
    renderCart();
};
</script>

<?php require 'includes/footer.php'; ?>