<?php
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

// Fetch countries for dropdown
$countries = $pdo->query("SELECT code, name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();

$errors = [];
$name = $email = $phone = $country_code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $country_code = trim($_POST['country_code'] ?? 'AU');
    $terms = isset($_POST['terms']);

    // Validation
    if (empty($name)) $errors[] = 'Full name is required.';
    if (strlen($name) > 100) $errors[] = 'Name must be under 100 characters.';

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!empty($phone) && !preg_match('/^[\+0-9\s\-\(\)]{7,20}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$terms) {
        $errors[] = 'You must accept the terms and conditions.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, country_code, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$name, $email, $phone ?: null, $hashedPassword, $country_code]);

        setFlash('success', 'Account created successfully! Please sign in.');
        redirect(BASE_URL . '/auth/login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Brilliance Gems</title>
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
            flex: 0 0 42%;
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
            max-width: 420px;
        }

        .panel-brand {
            font-family: var(--font-heading);
            font-size: 2.6rem;
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
            font-size: 1.1rem;
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

        .panel-features {
            margin-top: 2.5rem;
            text-align: left;
        }

        .panel-feature {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .panel-feature i {
            color: var(--gold);
            font-size: 0.8rem;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(185,151,91,0.2);
            border-radius: 50%;
            flex-shrink: 0;
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
            0% { opacity: 0; transform: translateY(100vh) scale(0); }
            10% { opacity: 0.6; }
            90% { opacity: 0.3; }
            100% { opacity: 0; transform: translateY(-10vh) scale(1); }
        }

        .floating-ring {
            position: absolute;
            border: 1px solid rgba(185,151,91,0.1);
            border-radius: 50%;
            animation: ringFloat 12s ease-in-out infinite;
        }

        .floating-ring:nth-child(1) {
            width: 200px; height: 200px;
            top: 10%; left: 10%;
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
            overflow-y: auto;
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
            max-width: 480px;
            background: rgba(18, 18, 24, 0.6);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius-lg);
            padding: 2.5rem 3rem;
            box-shadow:
                0 25px 60px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.04);
            animation: fadeInUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .form-header h2 {
            font-family: var(--font-heading);
            font-size: 1.7rem;
            color: var(--text-primary);
            margin-bottom: 0.3rem;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* ---- Form Controls ---- */
        .auth-field {
            margin-bottom: 1.15rem;
            animation: fieldSlideIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            opacity: 0;
        }

        .auth-field:nth-child(1) { animation-delay: 0.1s; }
        .auth-field:nth-child(2) { animation-delay: 0.15s; }
        .auth-field:nth-child(3) { animation-delay: 0.2s; }
        .auth-field:nth-child(4) { animation-delay: 0.25s; }
        .auth-field:nth-child(5) { animation-delay: 0.3s; }
        .auth-field:nth-child(6) { animation-delay: 0.35s; }
        .auth-field:nth-child(7) { animation-delay: 0.4s; }

        @keyframes fieldSlideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .auth-field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .auth-field label .required {
            color: var(--danger);
            margin-left: 2px;
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
            font-size: 0.85rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .auth-input-wrap input,
        .auth-input-wrap select {
            width: 100%;
            padding: 13px 16px 13px 44px;
            background: var(--dark-input);
            border: 1px solid var(--dark-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 0.92rem;
            transition: var(--transition-fast);
            outline: none;
        }

        .auth-input-wrap select {
            appearance: none;
            cursor: pointer;
            padding-right: 40px;
        }

        .auth-input-wrap select option {
            background: #121216;
            color: var(--text-primary);
        }

        .select-arrow {
            position: absolute;
            right: 16px;
            color: var(--text-muted);
            font-size: 0.7rem;
            pointer-events: none;
            z-index: 2;
        }

        .auth-input-wrap input:focus,
        .auth-input-wrap select:focus {
            border-color: var(--gold);
            background: rgba(18, 18, 24, 0.9);
            box-shadow: 0 0 0 3px rgba(185,151,91,0.1);
        }

        .auth-input-wrap input:focus ~ .field-icon,
        .auth-input-wrap select:focus ~ .field-icon {
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
            font-size: 0.85rem;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--gold);
        }

        /* ---- Password Strength Meter ---- */
        .password-strength {
            margin-top: 8px;
        }

        .strength-bar-track {
            height: 4px;
            background: rgba(255,255,255,0.06);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: all 0.4s ease;
        }

        .strength-label {
            font-size: 0.75rem;
            margin-top: 4px;
            text-align: right;
            font-weight: 500;
            min-height: 1.1em;
        }

        .strength-weak .strength-bar-fill { width: 33%; background: var(--danger); }
        .strength-weak .strength-label { color: var(--danger); }

        .strength-medium .strength-bar-fill { width: 66%; background: var(--warning); }
        .strength-medium .strength-label { color: var(--warning); }

        .strength-strong .strength-bar-fill { width: 100%; background: var(--success); }
        .strength-strong .strength-label { color: var(--success); }

        /* ---- Terms Checkbox ---- */
        .terms-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 1.5rem;
            margin-top: 0.3rem;
        }

        .terms-check input[type="checkbox"] {
            width: 17px;
            height: 17px;
            margin-top: 2px;
            background: var(--dark-input);
            border: 1px solid var(--dark-border);
            border-radius: 4px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: var(--gold);
        }

        .terms-check input[type="checkbox"]:checked {
            background: var(--gold);
            border-color: var(--gold);
        }

        .terms-check label {
            font-size: 0.84rem;
            color: var(--text-secondary);
            cursor: pointer;
            line-height: 1.5;
        }

        .terms-check label a {
            color: var(--gold);
            text-decoration: none;
        }

        .terms-check label a:hover {
            color: var(--gold-light);
        }

        /* ---- Submit Button ---- */
        .auth-submit {
            width: 100%;
            padding: 14px;
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
            margin-top: 1.5rem;
            padding-top: 1.2rem;
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
            padding: 12px 16px;
            margin-bottom: 1.3rem;
            font-size: 0.85rem;
            color: #fca5a5;
            animation: fadeInUp 0.5s ease forwards;
        }

        .auth-alert div {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 2px 0;
        }

        /* ---- Two-column row ---- */
        .auth-row {
            display: flex;
            gap: 1rem;
        }

        .auth-row .auth-field {
            flex: 1;
        }

        /* ---- Responsive ---- */
        @media (max-width: 991.98px) {
            .auth-panel-left { display: none; }
            .auth-panel-right { flex: 1; }
            .auth-panel-right::before { display: none; }
        }

        @media (max-width: 575.98px) {
            .glass-form-card {
                padding: 1.8rem 1.4rem;
                border-radius: var(--radius-md);
            }
            .auth-panel-right {
                padding: 1rem;
            }
            .auth-row {
                flex-direction: column;
                gap: 0;
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
                    "Every gem tells a story of<br>
                    brilliance, beauty, and belonging."
                </p>

                <div class="panel-features">
                    <div class="panel-feature">
                        <i class="fas fa-shield-halved"></i>
                        <span>Certified authentic gemstones</span>
                    </div>
                    <div class="panel-feature">
                        <i class="fas fa-truck-fast"></i>
                        <span>Free insured worldwide shipping</span>
                    </div>
                    <div class="panel-feature">
                        <i class="fas fa-rotate-left"></i>
                        <span>30-day hassle-free returns</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-panel-right">
            <div class="glass-form-card">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Join the Brilliance Gems family</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="auth-alert">
                        <?php foreach ($errors as $error): ?>
                            <div><i class="fas fa-exclamation-circle"></i><?= sanitize($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <div class="auth-field">
                        <label>Full Name <span class="required">*</span></label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" name="name" placeholder="Your full name"
                                   value="<?= sanitize($name) ?>" required maxlength="100">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Email Address <span class="required">*</span></label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-envelope field-icon"></i>
                            <input type="email" name="email" placeholder="your@email.com"
                                   value="<?= sanitize($email) ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="auth-row">
                        <div class="auth-field">
                            <label>Phone Number</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-phone field-icon"></i>
                                <input type="tel" name="phone" placeholder="+61 400 000 000"
                                       value="<?= sanitize($phone) ?>" maxlength="20">
                            </div>
                        </div>

                        <div class="auth-field">
                            <label>Country <span class="required">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-globe field-icon"></i>
                                <select name="country_code" required>
                                    <?php foreach ($countries as $c): ?>
                                        <option value="<?= sanitize($c['code']) ?>" <?= $c['code'] === ($country_code ?: 'AU') ? 'selected' : '' ?>>
                                            <?= sanitize($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Password <span class="required">*</span></label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="password" id="regPassword"
                                   placeholder="Minimum 6 characters" required minlength="6"
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('regPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="strengthMeter">
                            <div class="strength-bar-track">
                                <div class="strength-bar-fill"></div>
                            </div>
                            <div class="strength-label"></div>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Confirm Password <span class="required">*</span></label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="confirm_password" id="regConfirm"
                                   placeholder="Re-enter your password" required
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('regConfirm', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="terms-check">
                        <input type="checkbox" id="termsCheck" name="terms" required>
                        <label for="termsCheck">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit">
                        <span><i class="fas fa-user-plus me-2"></i>Create Account</span>
                    </button>
                </form>

                <div class="auth-footer">
                    Already have an account?
                    <a href="<?= BASE_URL ?>/auth/login.php">Sign In</a>
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

        // Password strength meter
        (function() {
            const passwordInput = document.getElementById('regPassword');
            const meter = document.getElementById('strengthMeter');
            if (!passwordInput || !meter) return;

            const label = meter.querySelector('.strength-label');

            passwordInput.addEventListener('input', function() {
                const val = this.value;
                meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');

                if (val.length === 0) {
                    label.textContent = '';
                    return;
                }

                let score = 0;
                if (val.length >= 6) score++;
                if (val.length >= 10) score++;
                if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
                if (/\d/.test(val)) score++;
                if (/[^a-zA-Z0-9]/.test(val)) score++;

                if (score <= 2) {
                    meter.classList.add('strength-weak');
                    label.textContent = 'Weak';
                } else if (score <= 3) {
                    meter.classList.add('strength-medium');
                    label.textContent = 'Medium';
                } else {
                    meter.classList.add('strength-strong');
                    label.textContent = 'Strong';
                }
            });
        })();

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
