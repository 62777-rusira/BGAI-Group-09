<?php 
require 'includes/header.php';
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>
<div class="container py-5">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h2>
    <p class="lead">Track your orders and manage your profile here.</p>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Thank you for your purchase!</div>
    <?php endif; ?>
    
    <h4 class="mt-5">Recent Orders</h4>
    <p class="text-white-50">Your order history will appear here once you place orders.</p>
    
    <a href="logout.php" class="btn btn-outline-danger mt-4">Logout</a>
</div>
<?php require 'includes/footer.php'; ?>