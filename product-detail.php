<?php 
require 'includes/header.php';
require 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<h2 class='text-center py-5'>Product not found</h2>";
    require 'includes/footer.php';
    exit;
}
?>

<div class="container py-5">
<div class="row g-5">

<div class="col-md-6">
<img src="<?= htmlspecialchars($product['image']) ?>" 
     class="img-fluid rounded-4 shadow">
</div>

<div class="col-md-6">

<h1><?= htmlspecialchars($product['name']) ?></h1>

<div class="price mb-3" id="priceBox">
₹<?= number_format($product['price_aud'] * 55, 0) ?>
</div>

<p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

<p><strong>Specs:</strong><br><?= htmlspecialchars($product['specs']) ?></p>
<p><strong>Stock:</strong> <?= $product['stock'] ?></p>

<button onclick="addToCart(<?= $product['id'] ?>,'<?= addslashes($product['name']) ?>',<?= $product['price_aud'] ?>,'<?= $product['image'] ?>')" 
class="btn gold-btn btn-lg w-100">
<i class="fa fa-cart-plus"></i> Add to Cart
</button>

</div>
</div>
</div>

<script>
function addToCart(id,name,priceAud,image){
let cart=JSON.parse(localStorage.getItem('cart')||'[]');

let item=cart.find(i=>i.id===id);
if(item){ item.qty++; }
else{ cart.push({id,name,priceAud,image,qty:1}); }

localStorage.setItem('cart',JSON.stringify(cart));
updateCartCount();
alert("Added to cart!");
}

// currency
document.addEventListener('DOMContentLoaded', ()=>{
const rate=parseFloat(sessionStorage.getItem('rate')||55);
const symbol=sessionStorage.getItem('symbol')||'₹';

const base=<?= $product['price_aud'] ?>;
document.getElementById('priceBox').innerText = symbol + Math.round(base * rate);
});
</script>

<?php require 'includes/footer.php'; ?>