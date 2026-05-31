        </main>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-inner">
                <div class="newsletter-text">
                    <h2>Join the BGAI Circle</h2>
                    <p>Subscribe to receive exclusive offers, new collection previews, and jewellery care tips straight to your inbox.</p>
                </div>
                <form class="newsletter-form" onsubmit="handleNewsletter(event)">
                    <input type="email" placeholder="Enter your email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-brand">
                    <a href="<?php echo APP_URL; ?>" class="footer-logo">
                        <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="BGAI" style="height:50px;">
                        <div>
                            <span class="logo-name" style="color:#d4af37;font-size:1.5rem;">BGAI</span>
                            <span class="logo-subtitle" style="color:#c9a96e;font-size:0.7rem;">Brilliance, Gems of Australia</span>
                        </div>
                    </a>
                    <p>Crafting extraordinary jewellery since 2010. Every piece tells a story of Australian beauty, from the vivid opals of Lightning Ridge to the lustrous pearls of Broome.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>">Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/collections.php">Collections</a></li>
                        <li><a href="<?php echo APP_URL; ?>/products.php">Shop</a></li>
                        <li><a href="<?php echo APP_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo APP_URL; ?>/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="#">Shipping & Returns</a></li>
                        <li><a href="#">Size Guide</a></li>
                        <li><a href="#">Jewellery Care</a></li>
                        <li><a href="#">Gift Wrapping</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Contact Us</h4>
                    <ul class="contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> Level 15, 1 Martin Place, Sydney NSW 2000, Australia</li>
                        <li><i class="fas fa-phone"></i> +61 2 8000 0000</li>
                        <li><i class="fas fa-envelope"></i> hello@bgai.com.au</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 9AM - 6PM AEST</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> BGAI - Brilliance, Gems of Australia International. All rights reserved.</p>
                <div class="footer-badges">
                    <img src="https://img.icons8.com/color/48/visa.png" alt="Visa" style="height:30px;">
                    <img src="https://img.icons8.com/color/48/mastercard.png" alt="Mastercard" style="height:30px;">
                    <img src="https://img.icons8.com/color/48/paypal.png" alt="PayPal" style="height:30px;">
                    <img src="https://img.icons8.com/color/48/apple-pay.png" alt="Apple Pay" style="height:30px;">
                    <i class="fas fa-lock" style="font-size:20px;color:#4CAF50;" title="SSL Secured"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/cart.js"></script>
</body>
</html>
