// ============================================
// BGAI - Cart JavaScript (AJAX)
// ============================================

// APP_URL is injected by header.php via window.APP_URL
const APP_URL = window.APP_URL || '';

/**
 * Add product to cart via AJAX
 */
function addToCart(productId, quantity = 1) {
    fetch(APP_URL + '/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart_count);
            showToast(data.message || 'Added to cart!', 'success');
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(() => {
        showToast('Something went wrong. Please try again.', 'error');
    });
}

/**
 * Add to cart and then redirect (used by Buy Now)
 */
function addToCartThenRedirect(productId, quantity, redirectUrl) {
    fetch(APP_URL + '/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart_count);
            window.location.href = redirectUrl;
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(() => {
        showToast('Something went wrong. Please try again.', 'error');
    });
}

/**
 * Update cart item quantity via AJAX
 */
function updateCartQty(cartId, quantity) {
    fetch(APP_URL + '/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'update',
            cart_id: cartId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart_count);
            // Reload cart page if on it
            if (window.location.pathname.includes('/cart.php')) {
                location.reload();
            }
        } else {
            showToast(data.message || 'Failed to update cart', 'error');
        }
    })
    .catch(() => {
        showToast('Something went wrong.', 'error');
    });
}

/**
 * Remove item from cart via AJAX
 */
function removeFromCart(cartId) {
    if (!confirm('Remove this item from your cart?')) return;
    
    fetch(APP_URL + '/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'remove',
            cart_id: cartId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart_count);
            showToast(data.message || 'Item removed', 'info');
            
            // Remove item from DOM or reload
            const itemEl = document.getElementById('cart-item-' + cartId);
            if (itemEl) {
                itemEl.style.opacity = '0';
                itemEl.style.transform = 'translateX(-20px)';
                itemEl.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    itemEl.remove();
                    // Check if cart is now empty
                    const cartItems = document.querySelectorAll('.cart-item');
                    if (cartItems.length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                location.reload();
            }
        } else {
            showToast(data.message || 'Failed to remove item', 'error');
        }
    })
    .catch(() => {
        showToast('Something went wrong.', 'error');
    });
}

/**
 * Update cart count badge in header
 */
function updateCartDisplay(count) {
    const cartCountEl = document.getElementById('cartCount');
    if (cartCountEl) {
        cartCountEl.textContent = count || 0;
        // Animate
        cartCountEl.style.transform = 'scale(1.3)';
        setTimeout(() => {
            cartCountEl.style.transform = 'scale(1)';
            cartCountEl.style.transition = 'transform 0.2s ease';
        }, 200);
    }
}

/**
 * Get cart count on page load
 */
function refreshCartCount() {
    fetch(APP_URL + '/api/cart.php?action=count')
        .then(r => r.json())
        .then(data => {
            if (data.count !== undefined) {
                updateCartDisplay(data.count);
            }
        })
        .catch(() => {});
}

// Auto-refresh cart count periodically
document.addEventListener('DOMContentLoaded', function() {
    refreshCartCount();
    setInterval(refreshCartCount, 30000);
});
