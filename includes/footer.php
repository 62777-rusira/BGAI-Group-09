<!-- Footer -->
<footer class="footer-bgai">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5><i class="fas fa-gem me-2"></i>Brilliance Gems</h5>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.9; margin-bottom: 1.5rem;">
                    Crafting timeless elegance since 1998. Every piece tells a story of artistry,
                    passion, and uncompromising quality from the heart of Australia.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.04);border:1px solid var(--dark-border);border-radius:50%;color:var(--text-muted);transition:var(--transition);"
                       onmouseover="this.style.background='var(--gold)';this.style.color='#0a0a0a';this.style.borderColor='var(--gold)';this.style.transform='translateY(-3px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='var(--text-muted)';this.style.borderColor='var(--dark-border)';this.style.transform='translateY(0)'">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.04);border:1px solid var(--dark-border);border-radius:50%;color:var(--text-muted);transition:var(--transition);"
                       onmouseover="this.style.background='var(--gold)';this.style.color='#0a0a0a';this.style.borderColor='var(--gold)';this.style.transform='translateY(-3px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='var(--text-muted)';this.style.borderColor='var(--dark-border)';this.style.transform='translateY(0)'">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.04);border:1px solid var(--dark-border);border-radius:50%;color:var(--text-muted);transition:var(--transition);"
                       onmouseover="this.style.background='var(--gold)';this.style.color='#0a0a0a';this.style.borderColor='var(--gold)';this.style.transform='translateY(-3px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='var(--text-muted)';this.style.borderColor='var(--dark-border)';this.style.transform='translateY(0)'">
                        <i class="fab fa-pinterest-p"></i>
                    </a>
                    <a href="#" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.04);border:1px solid var(--dark-border);border-radius:50%;color:var(--text-muted);transition:var(--transition);"
                       onmouseover="this.style.background='var(--gold)';this.style.color='#0a0a0a';this.style.borderColor='var(--gold)';this.style.transform='translateY(-3px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='var(--text-muted)';this.style.borderColor='var(--dark-border)';this.style.transform='translateY(0)'">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h5>Collections</h5>
                <ul>
                    <li><a href="<?= BASE_URL ?>/products/shop.php?category=rings">Rings</a></li>
                    <li><a href="<?= BASE_URL ?>/products/shop.php?category=necklaces">Necklaces</a></li>
                    <li><a href="<?= BASE_URL ?>/products/shop.php?category=bracelets">Bracelets</a></li>
                    <li><a href="<?= BASE_URL ?>/products/shop.php?category=earrings">Earrings</a></li>
                    <li><a href="<?= BASE_URL ?>/products/shop.php">All Products</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h5>Account</h5>
                <ul>
                    <li><a href="<?= BASE_URL ?>/auth/profile.php">My Account</a></li>
                    <li><a href="<?= BASE_URL ?>/cart/orders.php">Order History</a></li>
                    <li><a href="<?= BASE_URL ?>/cart/index.php">Shopping Cart</a></li>
                    <li><a href="<?= BASE_URL ?>/payments/history.php">Payments</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5>Get in Touch</h5>
                <ul>
                    <li style="display:flex;align-items:flex-start;gap:10px;">
                        <i class="fas fa-map-marker-alt mt-1" style="color:var(--gold);width:16px;"></i>
                        <span style="color:var(--text-muted);font-size:0.85rem;">250 Collins Street, Melbourne VIC 3000, Australia</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-phone" style="color:var(--gold);width:16px;"></i>
                        <span style="color:var(--text-muted);font-size:0.85rem;">+61 3 9000 1234</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-envelope" style="color:var(--gold);width:16px;"></i>
                        <span style="color:var(--text-muted);font-size:0.85rem;">info@bgai.com.au</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-clock" style="color:var(--gold);width:16px;"></i>
                        <span style="color:var(--text-muted);font-size:0.85rem;">Mon-Sat: 10AM - 6PM AEST</span>
                    </li>
                </ul>

                <!-- Payment methods -->
                <div class="mt-3 d-flex gap-2 align-items-center" style="color:var(--text-dim);font-size:1.2rem;">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fas fa-university" style="font-size:0.9rem;"></i>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <p>&copy; <?= date('Y') ?> Brilliance Gems of Australia International (BGAI). All rights reserved.</p>
            <div class="d-flex justify-content-center gap-3 mt-2" style="font-size:0.75rem;">
                <a href="#" style="color:var(--text-dim);">Privacy Policy</a>
                <span style="color:var(--text-dim);">|</span>
                <a href="#" style="color:var(--text-dim);">Terms of Service</a>
                <span style="color:var(--text-dim);">|</span>
                <a href="#" style="color:var(--text-dim);">Shipping Policy</a>
                <span style="color:var(--text-dim);">|</span>
                <a href="#" style="color:var(--text-dim);">Returns</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function changeCurrency(code) {
    fetch('<?= BASE_URL ?>/payments/set-currency.php?code=' + code)
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
}

// Navbar scroll effect (for pages that don't have their own)
if (!window._navScrollBound) {
    window._navScrollBound = true;
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-bgai');
        if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
}
</script>
</body>
</html>
