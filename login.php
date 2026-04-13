<?php 
// login.php
require 'includes/header.php';
require 'includes/db.php';

if ($_POST) {
    $email = $_POST['email'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        header("Location: account.php"); exit;
    } else {
        echo "<div class='alert alert-danger text-center'>Invalid credentials</div>";
    }
}
?>
<div class="container py-5">
    <div class="col-md-5 mx-auto">
        <h2 class="text-center mb-4">Login to BGAI</h2>
        <form method="post">
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            <button type="submit" class="btn gold-btn w-100">Login</button>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="register.php" class="text-gold">Register</a></p>
    </div>
</div>
<?php require 'includes/footer.php'; ?>