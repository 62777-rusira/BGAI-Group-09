<?php
// ============================================
// Order Success Page
// ============================================
$pageTitle = 'Order Confirmed';
require_once __DIR__ . '/includes/header.php';

$orderNumber = $_GET['order'] ?? '';
?>

<section style="min-height:60vh; display:flex; align-items:center; justify-content:center; padding:var(--space-4xl) 0;">
    <div class="container" style="max-width:600px; text-align:center;">
        <div style="width:100px; height:100px; border-radius:50%; background:linear-gradient(135deg, var(--success), #86efac); display:flex; align-items:center; justify-content:center; margin:0 auto var(--space-2xl);">
            <i class="fas fa-check" style="font-size:3rem; color:var(--white);"></i>
        </div>
        <h1 style="font-family:var(--font-heading); font-size:2.5rem; margin-bottom:var(--space-md);">Thank You!</h1>
        <p style="font-size:1.2rem; color:var(--gray-600); margin-bottom:var(--space-lg);">
            Your order has been placed successfully.
        </p>
        <?php if ($orderNumber): ?>
            <div style="background:var(--cream); border:2px solid var(--gold); border-radius:var(--border-radius-lg); padding:var(--space-xl); margin-bottom:var(--space-2xl);">
                <p style="font-size:0.85rem; color:var(--gray-500); text-transform:uppercase; letter-spacing:2px; margin-bottom:var(--space-sm);">Order Number</p>
                <p style="font-family:var(--font-heading); font-size:1.8rem; color:var(--gold-dark); font-weight:700;"><?php echo e($orderNumber); ?></p>
            </div>
        <?php endif; ?>
        <p style="color:var(--gray-500); margin-bottom:var(--space-xl);">
            A confirmation email has been sent to your email address. You can track your order from your account dashboard.
        </p>
        <div style="display:flex; gap:var(--space-md); justify-content:center; flex-wrap:wrap;">
            <a href="<?php echo APP_URL; ?>/profile.php" class="btn btn-primary">
                <i class="fas fa-user"></i> My Orders
            </a>
            <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-outline-dark">
                Continue Shopping
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
