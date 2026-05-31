<?php
// ============================================
// Login Page
// ============================================

require_once __DIR__ . '/config/app.php';

$pageTitle = 'Sign In';

if (isLoggedIn()) {
    redirect(APP_URL);
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = loginUser($email, $password);
    if ($result['success']) {
        $redirect = $_GET['redirect'] ?? '';
        if ($redirect) {
            redirect(APP_URL . $redirect, 'success', $result['message']);
        } else {
            redirect(APP_URL, 'success', $result['message']);
        }
        exit;
    } else {
        $error = $result['message'];
    }
}

// Show flash messages (from redirect)
$flash = getFlash();

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-split">
        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <span class="logo-name">BGAI</span>
                    <h1>Welcome Back</h1>
                    <p>Sign in to your account to continue shopping</p>
                </div>
                
                <?php if ($flash): ?>
                    <div style="background:<?php echo $flash['type'] === 'success' ? '#f0fdf4; border-color:#86efac; color:#166534' : '#fef2f2; border-color:#fecaca; color:#991b1b'; ?>; border:1px solid; padding:var(--space-md); border-radius:var(--border-radius); margin-bottom:var(--space-xl); font-size:0.9rem;">
                        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo e($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:var(--space-md); border-radius:var(--border-radius); margin-bottom:var(--space-xl); font-size:0.9rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="auth-form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="your@email.com" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="auth-form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xl);">
                        <label class="form-check">
                            <input type="checkbox"> Remember me
                        </label>
                        <a href="#" style="font-size:0.85rem; color:var(--gold-dark);">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
                </form>

                <div class="auth-form-footer">
                    <p>Don't have an account? <a href="<?php echo APP_URL; ?>/register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">Create one</a></p>
                </div>
            </div>
        </div>
        
        <!-- Decorative Side -->
        <div class="auth-decorative-side">
            <div class="auth-decorative-content">
                <h2>The Art of<br>Australian<br>Jewellery</h2>
                <p>Discover handcrafted pieces featuring the world's finest opals, diamonds, sapphires, and South Sea pearls.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Prevent duplicate form submission
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
