<?php
// ============================================
// Register Page
// ============================================

require_once __DIR__ . '/config/app.php';

$pageTitle = 'Create Account';

if (isLoggedIn()) {
    redirect(APP_URL);
    exit;
}

$error = '';
$formData = [];
$registrationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'country' => trim($_POST['country'] ?? 'Australia'),
        'country_code' => $_POST['country_code'] ?? 'AU',
        'currency_preference' => $_POST['currency_preference'] ?? 'AUD',
    ];
    
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $data = array_merge($formData, [
            'password' => $password,
            'is_subscribed' => isset($_POST['newsletter']) ? 1 : 0
        ]);
        
        $result = registerUser($data);
        if ($result['success']) {
            $registrationSuccess = true;
        } else {
            $error = $result['message'];
        }
    }
}

// Show flash messages (from redirect)
$flash = getFlash();

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-split">
        <div class="auth-form-side">
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <span class="logo-name">BGAI</span>
                    <h1>Create Account</h1>
                    <p>Join us for an exclusive jewellery shopping experience</p>
                </div>
                
                <?php if ($flash): ?>
                    <div class="flash-message-inline flash-<?php echo $flash['type']; ?>" style="background:<?php echo $flash['type'] === 'success' ? '#f0fdf4; border-color:#86efac; color:#166534' : '#fef2f2; border-color:#fecaca; color:#991b1b'; ?>; border:1px solid; padding:var(--space-md); border-radius:var(--border-radius); margin-bottom:var(--space-xl); font-size:0.9rem;">
                        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo e($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($registrationSuccess): ?>
                    <!-- Success message shown on same page (no redirect needed) -->
                    <div style="background:#f0fdf4; border:1px solid #86efac; color:#166534; padding:var(--space-xl); border-radius:var(--border-radius); margin-bottom:var(--space-xl); text-align:center;">
                        <i class="fas fa-check-circle" style="font-size:2rem; margin-bottom:var(--space-md); display:block; color:#22c55e;"></i>
                        <h3 style="margin-bottom:var(--space-sm); color:#166534;">Account Created Successfully!</h3>
                        <p style="margin-bottom:var(--space-lg);">Your account has been created. You can now sign in with your credentials.</p>
                        <a href="<?php echo APP_URL; ?>/login.php" class="btn btn-primary btn-block" style="color:#fff; text-decoration:none;">
                            <i class="fas fa-sign-in-alt"></i> Sign In Now
                        </a>
                    </div>
                <?php else: ?>

                    <?php if ($error): ?>
                        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:var(--space-md); border-radius:var(--border-radius); margin-bottom:var(--space-xl); font-size:0.9rem;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="registerForm">
                        <div class="form-row">
                            <div class="auth-form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" class="form-input" value="<?php echo e($formData['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="auth-form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" class="form-input" value="<?php echo e($formData['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="form-input" placeholder="your@email.com" value="<?php echo e($formData['email'] ?? ''); ?>" required>
                        </div>
                        <div class="auth-form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-input" value="<?php echo e($formData['phone'] ?? ''); ?>">
                        </div>
                        <div class="auth-form-group">
                            <label>Country *</label>
                            <select name="country_code" class="form-input" onchange="updateCurrency(this)" required>
                                <option value="AU" <?php echo ($formData['country_code'] ?? 'AU') === 'AU' ? 'selected' : ''; ?>>Australia (AUD)</option>
                                <option value="US" <?php echo ($formData['country_code'] ?? '') === 'US' ? 'selected' : ''; ?>>United States (USD)</option>
                                <option value="GB" <?php echo ($formData['country_code'] ?? '') === 'GB' ? 'selected' : ''; ?>>United Kingdom (GBP)</option>
                                <option value="IN" <?php echo ($formData['country_code'] ?? '') === 'IN' ? 'selected' : ''; ?>>India (INR)</option>
                                <option value="AE" <?php echo ($formData['country_code'] ?? '') === 'AE' ? 'selected' : ''; ?>>UAE (AED)</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="auth-form-group">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-input" placeholder="Min 6 characters" required>
                            </div>
                            <div class="auth-form-group">
                                <label>Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter password" required>
                            </div>
                        </div>
                        <input type="hidden" name="country" value="<?php echo e($formData['country'] ?? 'Australia'); ?>">
                        <input type="hidden" name="currency_preference" id="currencyPref" value="<?php echo e($formData['currency_preference'] ?? 'AUD'); ?>">
                        
                        <label class="form-check" style="margin-bottom:var(--space-xl);">
                            <input type="checkbox" name="newsletter" checked>
                            Subscribe to our newsletter for exclusive offers
                        </label>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
                    </form>

                    <div class="auth-form-footer">
                        <p>Already have an account? <a href="<?php echo APP_URL; ?>/login.php">Sign in</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="auth-decorative-side">
            <div class="auth-decorative-content">
                <h2>Join the<br>BGAI Circle</h2>
                <p>Create an account to enjoy personalised recommendations, order tracking, exclusive member pricing, and early access to new collections.</p>
            </div>
        </div>
    </div>
</div>

<script>
function updateCurrency(select) {
    const map = {AU:'AUD', US:'USD', GB:'GBP', IN:'INR', AE:'AED'};
    document.getElementById('currencyPref').value = map[select.value] || 'AUD';
}

// Prevent duplicate form submission
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
