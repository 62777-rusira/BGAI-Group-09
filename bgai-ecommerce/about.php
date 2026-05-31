<?php
// ============================================
// About Page
// ============================================
$pageTitle = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="about-hero">
    <div class="hero-bg" style="background-image: url('<?php echo APP_URL; ?>/assets/images/about-hero.jpg'); opacity:0.3;"></div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="about-hero-content">
            <span class="section-label" style="color:var(--gold);">Our Story</span>
            <h1>Crafting Australian Brilliance Since 2010</h1>
            <p>At BGAI, we believe that every piece of jewellery should tell a story. Founded in Sydney, Australia, our mission is to bring the extraordinary beauty of Australian gemstones to the world through exceptional craftsmanship and timeless design. From the vivid opals of Lightning Ridge to the lustrous South Sea pearls of Broome, we source only the finest materials nature has to offer.</p>
            <div style="margin-top:var(--space-2xl);">
                <a href="<?php echo APP_URL; ?>/collections.php" class="btn btn-primary">Explore Collections</a>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Our Values</span>
            <h2 class="section-title">What Drives Us</h2>
            <div class="section-divider"></div>
        </div>
        <div class="values-grid reveal">
            <div class="value-card">
                <div class="value-card-icon"><i class="fas fa-leaf"></i></div>
                <h3>Ethical Sourcing</h3>
                <p>We are committed to responsible sourcing practices. Every gemstone is traceable to certified Australian mines, and we maintain strict environmental and social standards throughout our supply chain. Our partnerships with local mining communities ensure fair wages and sustainable practices.</p>
            </div>
            <div class="value-card">
                <div class="value-card-icon"><i class="fas fa-hammer"></i></div>
                <h3>Master Craftsmanship</h3>
                <p>Our team of master jewellers brings together decades of experience in traditional goldsmithing and modern design techniques. Each piece undergoes meticulous quality control, with hand-finishing that ensures every detail meets our exacting standards of perfection.</p>
            </div>
            <div class="value-card">
                <div class="value-card-icon"><i class="fas fa-heart"></i></div>
                <h3>Customer Dedication</h3>
                <p>From your first enquiry to lifetime care of your jewellery, we are dedicated to providing an exceptional customer experience. Our personal stylists, hassle-free returns policy, and comprehensive warranty reflect our commitment to your complete satisfaction.</p>
            </div>
            <div class="value-card">
                <div class="value-card-icon"><i class="fas fa-award"></i></div>
                <h3>Certified Quality</h3>
                <p>Every diamond is GIA certified, every opal comes with an authenticity certificate, and each completed piece includes a professional valuation certificate. We believe in complete transparency about the quality and origin of every component in our jewellery.</p>
            </div>
            <div class="value-card">
                <div class="value-card-icon"><i class="fas fa-globe"></i></div>
                <h3>Australian Heritage</h3>
                <p>We celebrate the unique beauty of Australia through our designs. From the vibrant colours of the outback to the deep blues of our coastlines, each collection draws inspiration from the landscapes, culture, and natural wonders that make Australia extraordinary.</p>
            </div>
            <div class="value-card">
                <div class="value-card-icon"><i class="fas fa-infinity"></i></div>
                <h3>Timeless Design</h3>
                <p>We create jewellery that transcends trends. Our design philosophy centres on creating pieces that will be treasured for generations, combining classic elegance with contemporary sensibility to produce truly timeless works of wearable art.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Journey Timeline -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-label">Our Journey</span>
            <h2 class="section-title">Milestones</h2>
            <div class="section-divider"></div>
        </div>
        <div class="timeline reveal">
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-year">2010</div>
                <div class="timeline-title">The Beginning</div>
                <div class="timeline-desc">BGAI was founded in a small workshop in Sydney's Paddington, starting with a collection of opal pendants that quickly gained attention for their exceptional quality and unique Australian character.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-year">2013</div>
                <div class="timeline-title">First Flagship Store</div>
                <div class="timeline-desc">Opened our first flagship boutique on Martin Place, Sydney, establishing BGAI as a destination for discerning jewellery collectors and expanding our range to include diamond and sapphire collections.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-year">2016</div>
                <div class="timeline-title">International Expansion</div>
                <div class="timeline-desc">Launched international shipping and opened partnerships with retailers in London, New York, and Dubai, bringing Australian jewellery to a global audience with country-specific pricing.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-year">2019</div>
                <div class="timeline-title">Digital Transformation</div>
                <div class="timeline-desc">Launched our e-commerce platform with virtual try-on technology and AI-powered personalisation, making the BGAI experience accessible to customers worldwide from the comfort of their homes.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-year">2022</div>
                <div class="timeline-title">Award Recognition</div>
                <div class="timeline-desc">Received the Australian Jewellery Design Award for our 'Opal Dreams' collection and was recognised as one of Australia's top luxury brands by Luxury Travel Magazine.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-year">2025</div>
                <div class="timeline-title">Looking Forward</div>
                <div class="timeline-desc">Continuing to innovate with sustainable practices, expanded collections featuring rare Australian gemstones, and a growing global community of jewellery lovers who share our passion for Australian brilliance.</div>
            </div>
        </div>
    </div>
</section>

<!-- Numbers -->
<section class="section section-dark">
    <div class="container">
        <div class="features-grid reveal">
            <div style="text-align:center;">
                <div style="font-family:var(--font-heading); font-size:3rem; color:var(--gold); font-weight:700;">15+</div>
                <p style="color:var(--gray-400); margin-top:var(--space-sm);">Years of Excellence</p>
            </div>
            <div style="text-align:center;">
                <div style="font-family:var(--font-heading); font-size:3rem; color:var(--gold); font-weight:700;">50K+</div>
                <p style="color:var(--gray-400); margin-top:var(--space-sm);">Happy Customers</p>
            </div>
            <div style="text-align:center;">
                <div style="font-family:var(--font-heading); font-size:3rem; color:var(--gold); font-weight:700;">5,000+</div>
                <p style="color:var(--gray-400); margin-top:var(--space-sm);">Unique Designs</p>
            </div>
            <div style="text-align:center;">
                <div style="font-family:var(--font-heading); font-size:3rem; color:var(--gold); font-weight:700;">50+</div>
                <p style="color:var(--gray-400); margin-top:var(--space-sm);">Countries Served</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
