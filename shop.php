<?php 
require_once __DIR__ . '/includes/header.php'; 
require_once __DIR__ . '/includes/db.php'; 

$category = $_GET['category'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM products WHERE category LIKE ? ORDER BY id DESC");
$stmt->execute(["%$category%"]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback (ensures at least 8)
$fallback = [
['id'=>1,'name'=>'Diamond Ring','category'=>'Rings','description'=>'Luxury diamond ring','price_aud'=>1200,'image'=>'upload/ring-diamond-solitaire.jpg'],
['id'=>2,'name'=>'Sapphire Ring','category'=>'Rings','description'=>'Blue sapphire elegance','price_aud'=>1400,'image'=>'upload/ring-sapphire-halo.jpg'],
['id'=>3,'name'=>'Emerald Necklace','category'=>'Necklaces','description'=>'Emerald beauty','price_aud'=>900,'image'=>'upload/necklace-emerald-gold.jpg'],
['id'=>4,'name'=>'Ruby Necklace','category'=>'Necklaces','description'=>'Ruby pendant','price_aud'=>850,'image'=>'upload/necklace-ruby-pendant.jpg'],
['id'=>5,'name'=>'Diamond Bracelet','category'=>'Bracelets','description'=>'Classic tennis bracelet','price_aud'=>700,'image'=>'upload/bracelet-diamond-tennis.jpg'],
['id'=>6,'name'=>'Gold Bracelet','category'=>'Bracelets','description'=>'Gold diamond bracelet','price_aud'=>650,'image'=>'upload/bracelet-gold-diamond.jpg'],
['id'=>7,'name'=>'Premium Ring','category'=>'Rings','description'=>'High-end ring','price_aud'=>1500,'image'=>'upload/ring-diamond-solitaire.jpg'],
['id'=>8,'name'=>'Statement Necklace','category'=>'Necklaces','description'=>'Modern necklace','price_aud'=>950,'image'=>'upload/necklace-emerald-gold.jpg']
];

$products = array_merge($products, $fallback);
$products = array_slice($products, 0, 12);
?>

<div class="container py-5">

<div class="text-center mb-5">
<h1 class="display-4 fw-bold" style="font-family:'Playfair Display', serif;">
<?= htmlspecialchars($category ?: 'Our Collections') ?>
</h1>
<p class="text-white-50">Discover luxury handcrafted jewellery</p>
</div>

<div class="row g-4">
<?php foreach ($products as $p): ?>
<div class="col-lg-3 col-md-6">
<div class="product-card h-100">

<img src="<?= htmlspecialchars($p['image']) ?>" class="w-100" style="height:280px; object-fit:cover;">

<div class="p-4 d-flex flex-column">

<span class="category-badge"><?= $p['category'] ?></span>

<h5 class="mt-3"><?= $p['name'] ?></h5>

<p class="text-white-50 small flex-grow-1"><?= $p['description'] ?></p>

<div class="mt-auto">
<div class="price" id="price-<?= $p['id'] ?>">
<span class="symbol">₹</span>
<span class="amount"><?= number_format($p['price_aud'] * 55, 0) ?></span>
</div>

<a href="product-detail.php?id=<?= $p['id'] ?>" class="btn gold-btn w-100 mt-2">
View Details
</a>
</div>

</div>
</div>
</div>
<?php endforeach; ?>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
const rate=parseFloat(sessionStorage.getItem('rate')||55);
const symbol=sessionStorage.getItem('symbol')||'₹';

document.querySelectorAll('.price').forEach(el=>{
const amt=el.querySelector('.amount');
if(amt){
const base=parseFloat(amt.textContent)/55;
amt.textContent=Math.round(base*rate);
el.querySelector('.symbol').textContent=symbol;
}
});
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>