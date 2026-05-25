<?php
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = 'Email is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) {
                $errors[] = 'Your account has been deactivated. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                setFlash('success', 'Welcome back, ' . sanitize($user['name']) . '!');
                redirect(BASE_URL . '/index.php');
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Brilliance Gems</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        /* ---- Split Screen Layout ---- */
        .auth-wrapper {
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ---- Left Decorative Panel ---- */
        .auth-panel-left {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #0a0a10 0%, #12101a 40%, #1a1424 70%, #0e0c14 100%);
        }

        .auth-panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 600px 400px at 30% 20%, rgba(185,151,91,0.12) 0%, transparent 70%),
                radial-gradient(ellipse 500px 500px at 70% 80%, rgba(185,151,91,0.08) 0%, transparent 60%);
            animation: panelGlow 8s ease-in-out infinite alternate;
        }

        @keyframes panelGlow {
            0% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .panel-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 3rem;
            max-width: 480px;
        }

        .panel-brand {
            font-family: var(--font-heading);
            font-size: 2.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--gold-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }

        .panel-icon {
            font-size: 3rem;
            color: var(--gold);
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 0 20px rgba(185,151,91,0.4));
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .panel-quote {
            font-family: var(--font-heading);
            font-size: 1.15rem;
            font-style: italic;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-top: 1.5rem;
            opacity: 0.85;
        }

        .panel-divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 1.5rem auto;
            border: none;
        }

        /* ---- Floating Particles ---- */
        .particles-container {
            position: absolute;
            inset: 0;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--gold);
            border-radius: 50%;
            opacity: 0;
            animation: particleDrift linear infinite;
        }

        @keyframes particleDrift {
            0% {
                opacity: 0;
                transform: translateY(100vh) scale(0);
            }
            10% { opacity: 0.6; }
            90% { opacity: 0.3; }
            100% {
                opacity: 0;
                transform: translateY(-10vh) scale(1);
            }
        }

        /* ---- Floating Rings ---- */
        .floating-ring {
            position: absolute;
            border: 1px solid rgba(185,151,91,0.1);
            border-radius: 50%;
            animation: ringFloat 12s ease-in-out infinite;
        }

        .floating-ring:nth-child(1) {
            width: 200px; height: 200px;
            top: 10%; left: 10%;
            animation-delay: 0s;
        }

        .floating-ring:nth-child(2) {
            width: 150px; height: 150px;
            bottom: 20%; right: 10%;
            animation-delay: -4s;
            border-color: rgba(185,151,91,0.06);
        }

        .floating-ring:nth-child(3) {
            width: 100px; height: 100px;
            top: 60%; left: 60%;
            animation-delay: -8s;
            border-color: rgba(185,151,91,0.08);
        }

        @keyframes ringFloat {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(15px, -20px) rotate(90deg); }
            50% { transform: translate(-10px, 15px) rotate(180deg); }
            75% { transform: translate(20px, 10px) rotate(270deg); }
        }

        /* ---- Right Form Panel ---- */
        .auth-panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            background: var(--dark-bg);
        }

        .auth-panel-right::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(180deg, transparent, rgba(185,151,91,0.2), transparent);
        }

        /* ---- Glass Form Card ---- */
        .glass-form-card {
            width: 100%;
            max-width: 440px;
            background: rgba(18, 18, 24, 0.6);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius-lg);
            padding: 3rem;
            box-shadow:
                0 25px 60px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.04);
            animation: fadeInUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: 0.4rem;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 0.92rem;
        }

        /* ---- Form Controls ---- */
        .auth-field {
            margin-bottom: 1.4rem;
        }

        .auth-field label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .auth-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .auth-input-wrap .field-icon {
            position: absolute;
            left: 16px;
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .auth-input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: var(--dark-input);
            border: 1px solid var(--dark-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 0.95rem;
            transition: var(--transition-fast);
            outline: none;
        }

        .auth-input-wrap input:focus {
            border-color: var(--gold);
            background: rgba(18, 18, 24, 0.9);
            box-shadow: 0 0 0 3px rgba(185,151,91,0.1);
        }

        .auth-input-wrap input:focus ~ .field-icon,
        .auth-input-wrap input:focus + .field-icon {
            color: var(--gold);
        }

        .auth-input-wrap input::placeholder {
            color: var(--text-dim);
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 0.9rem;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--gold);
        }

        /* ---- Remember / Forgot Row ---- */
        .auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
            font-size: 0.85rem;
        }

        .auth-options .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auth-options .form-check-input {
            width: 16px;
            height: 16px;
            background: var(--dark-input);
            border: 1px solid var(--dark-border);
            border-radius: 4px;
            cursor: pointer;
            margin: 0;
        }

        .auth-options .form-check-input:checked {
            background: var(--gold);
            border-color: var(--gold);
        }

        .auth-options .form-check-label {
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .auth-options .forgot-link {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-options .forgot-link:hover {
            color: var(--gold-light);
        }

        /* ---- Submit Button ---- */
        .auth-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border: none;
            border-radius: var(--radius-sm);
            color: #0a0a0c;
            font-family: var(--font-body);
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .auth-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-gold);
        }

        .auth-submit:hover::before {
            opacity: 1;
        }

        .auth-submit span {
            position: relative;
            z-index: 1;
        }

        .auth-submit:active {
            transform: translateY(0);
        }

        /* ---- Footer Link ---- */
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--dark-border);
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-footer a:hover {
            color: var(--gold-light);
        }

        /* ---- Error Alert ---- */
        .auth-alert {
            background: rgba(248, 113, 113, 0.08);
            border: 1px solid rgba(248, 113, 113, 0.2);
            border-radius: var(--radius-sm);
            padding: 14px 18px;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            color: #fca5a5;
            animation: fadeInUp 0.5s ease forwards;
        }

        .auth-alert div {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 2px 0;
        }

        .auth-alert-success {
            background: rgba(52, 211, 153, 0.08);
            border-color: rgba(52, 211, 153, 0.2);
            color: #6ee7b7;
        }

        /* ---- Responsive ---- */
        @media (max-width: 991.98px) {
            .auth-panel-left { display: none; }
            .auth-panel-right { flex: 1; }
            .auth-panel-right::before { display: none; }
        }

        @media (max-width: 575.98px) {
            .glass-form-card {
                padding: 2rem 1.5rem;
                border-radius: var(--radius-md);
            }
            .auth-panel-right {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <!-- Left Decorative Panel -->
        <div class="auth-panel-left">
            <div class="particles-container" id="particles"></div>
            <div class="floating-ring"></div>
            <div class="floating-ring"></div>
            <div class="floating-ring"></div>

            <div class="panel-content">
                <div class="panel-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="panel-brand">Brilliance Gems</div>
                <hr class="panel-divider">
                <p class="panel-quote">
                    "Where timeless elegance meets<br>
                    the artistry of fine jewellery."
                </p>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-panel-right">
            <div class="glass-form-card">
                <div class="form-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account</p>
                </div>

                <?php if ($flash): ?>
                    <div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : '' ?>">
                        <div><i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i><?= sanitize($flash['message']) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="auth-alert">
                        <?php foreach ($errors as $error): ?>
                            <div><i class="fas fa-exclamation-circle"></i><?= sanitize($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="auth-field">
                        <label>Email Address</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-envelope field-icon"></i>
                            <input type="email" name="email" placeholder="your@email.com"
                                   value="<?= sanitize($email) ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="password" id="loginPassword"
                                   placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('loginPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-options">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="auth-submit">
                        <span><i class="fas fa-sign-in-alt me-2"></i>Sign In</span>
                    </button>
                </form>

                <div class="auth-footer">
                    Don't have an account?
                    <a href="<?= BASE_URL ?>/auth/register.php">Create Account</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password show/hide toggle
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Generate floating particles
        (function() {
            const container = document.getElementById('particles');
            if (!container) return;
            const count = 30;
            for (let i = 0; i < count; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                p.style.left = Math.random() * 100 + '%';
                p.style.width = (Math.random() * 3 + 1) + 'px';
                p.style.height = p.style.width;
                p.style.animationDuration = (Math.random() * 10 + 8) + 's';
                p.style.animationDelay = (Math.random() * 10) + 's';
                p.style.opacity = 0;
                container.appendChild(p);
            }
        })();
    </script>
</body>
</html>
