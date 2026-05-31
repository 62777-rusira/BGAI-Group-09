<?php
// ============================================
// Contact Page
// ============================================
$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/header.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db();
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['phone'] ?? ''),
        trim($_POST['subject'] ?? ''),
        trim($_POST['message'] ?? '')
    ]);
    $sent = true;
}
?>

<section class="about-hero" style="min-height:35vh;">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="about-hero-content">
            <span class="section-label" style="color:var(--gold);">Get in Touch</span>
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Whether you have a question about our jewellery, need help with an order, or want to discuss a custom piece, our team is here to help.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info-cards">
                <div class="contact-card">
                    <div class="contact-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h3>Visit Us</h3>
                        <p>Level 15, 1 Martin Place<br>Sydney NSW 2000<br>Australia</p>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-card-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <h3>Call Us</h3>
                        <p>+61 2 8000 0000<br>Mon - Fri: 9AM - 6PM AEST<br>Sat: 10AM - 4PM AEST</p>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-card-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h3>Email Us</h3>
                        <p>hello@bgai.com.au<br>orders@bgai.com.au<br>support@bgai.com.au</p>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-card-icon"><i class="fas fa-gem"></i></div>
                    <div>
                        <h3>Custom Jewellery</h3>
                        <p>Interested in a bespoke piece? Our design team can create something unique just for you. Book a consultation today.</p>
                    </div>
                </div>
                
                <!-- Map Placeholder -->
                <div style="width:100%; height:250px; background:var(--cream); border-radius:var(--border-radius-lg); display:flex; align-items:center; justify-content:center; border:1px solid var(--gray-200);">
                    <div style="text-align:center; color:var(--gray-500);">
                        <i class="fas fa-map" style="font-size:2rem; margin-bottom:var(--space-sm); color:var(--gold);"></i>
                        <p>Interactive Map</p>
                        <p style="font-size:0.8rem;">Sydney, NSW 2000, Australia</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-card">
                <h2><i class="fas fa-paper-plane" style="color:var(--gold); margin-right:var(--space-sm);"></i> Send Us a Message</h2>
                
                <?php if ($sent): ?>
                    <div style="background:#dcfce7; border:1px solid #bbf7d0; color:#166534; padding:var(--space-lg); border-radius:var(--border-radius); margin-bottom:var(--space-xl); text-align:center;">
                        <i class="fas fa-check-circle" style="font-size:1.5rem; margin-bottom:var(--space-sm);"></i>
                        <p style="font-weight:600;">Thank you for your message!</p>
                        <p style="font-size:0.9rem;">We'll get back to you within 24 hours.</p>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="form-row">
                            <div class="auth-form-group">
                                <label>Full Name *</label>
                                <input type="text" name="name" class="form-input" required>
                            </div>
                            <div class="auth-form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-input" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="auth-form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-input">
                            </div>
                            <div class="auth-form-group">
                                <label>Subject *</label>
                                <select name="subject" class="form-input" required>
                                    <option value="">Select a topic</option>
                                    <option>General Enquiry</option>
                                    <option>Order Status</option>
                                    <option>Returns & Exchanges</option>
                                    <option>Custom Jewellery</option>
                                    <option>Wholesale Enquiry</option>
                                    <option>Press & Media</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label>Message *</label>
                            <textarea name="message" class="form-input" rows="5" placeholder="Tell us how we can help..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
