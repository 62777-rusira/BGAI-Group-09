<?php 
require 'includes/header.php';
require 'includes/db.php';

if ($_POST) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        $stmt->execute([$name,$email,$password]);

        header("Location: login.php");
        exit;
    } catch(PDOException $e) {
        echo "<div class='alert alert-danger text-center'>Email already exists</div>";
    }
}
?>

<div class="container py-5">
<div class="col-md-5 mx-auto">
<h2 class="text-center mb-4">Create Account</h2>

<form method="post">
<input name="name" class="form-control mb-3" placeholder="Full Name" required>
<input name="email" type="email" class="form-control mb-3" placeholder="Email" required>
<input name="password" type="password" class="form-control mb-3" placeholder="Password" required>

<button class="btn gold-btn w-100">Register</button>
</form>

<p class="text-center mt-3">
Already have an account? <a href="login.php" class="text-gold">Login</a>
</p>
</div>
</div>

<?php require 'includes/footer.php'; ?>