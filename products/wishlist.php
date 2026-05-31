<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    $removeId = (int)$_POST['remove_product_id'];
    if ($removeId > 0) {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $removeId]);
        setFlash('success', 'Item removed from your wishlist.');
        redirect(BASE_URL . '/products/wishlist.php');
    }
}

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, w.created_at AS wishlisted_at
    FROM wishlists w JOIN products p ON w.product_id = p.id JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ? AND p.is_active = 1 ORDER BY w.created_at DESC");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$pageTitle = 'My Wishlist';
include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:3rem 0 5rem;">
    <div class="page-header text-center" style="border:none;">
        <h1><i class="fas fa-heart me-3" style="color:var(--gold);"></i>My Wishlist</h1>
        <p style="color:var(--text-secondary);"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?> saved</p>
    </div>

    <?php if (empty($items)): ?>
    <div class="empty-state">
        <i class="far fa-heart"></i>
        <h3>Your wishlist is empty</h3>
        <p>Browse our collections and save the pieces you love.</p>
        <a href="<?= BASE_URL ?>/products/shop.php" class="btn-gold" style="text-decoration:none;"><i class="fas fa-gem me-2"></i>Explore Shop</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($items as $i => $item): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 animate-in delay-<?= ($i % 4) + 1 ?>">
            <div class="card-dark product-card h-100">
                <div class="image-wrapper">
                    <span class="category-badge"><?= sanitize($item['category_name']) ?></span>
                    <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $item['id'] ?>">
                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($item['image']) ?>" alt="<?= sanitize($item['name']) ?>" class="product-image">
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="product-name">
                        <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $item['id'] ?>" style="color:var(--text-primary);text-decoration:none;"><?= sanitize($item['name']) ?></a>
                    </h5>
                    <?php $r = getProductRating($pdo, $item['id']); if ($r['count'] > 0): ?>
                    <div class="mb-2"><?= renderStars($r['avg'], '0.75rem') ?> <span style="font-size:0.72rem;color:var(--text-muted);">(<?= $r['count'] ?>)</span></div>
                    <?php endif; ?>
                    <div class="mt-auto">
                        <div class="product-price mb-3"><?= formatPrice($item['price_aud']) ?></div>
                        <div class="d-grid gap-2">
                            <?php if ($item['stock'] > 0): ?>
                            <form action="<?= BASE_URL ?>/cart/add.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-gold w-100" style="justify-content:center;"><i class="fas fa-shopping-bag me-1"></i>Add to Cart</button>
                            </form>
                            <?php else: ?>
                            <button class="btn-gold-outline w-100" disabled style="opacity:0.5;">Out of Stock</button>
                            <?php endif; ?>
                            <form action="<?= BASE_URL ?>/products/wishlist.php" method="POST">
                                <input type="hidden" name="remove_product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-danger-soft w-100" onclick="return confirm('Remove from wishlist?')">
                                    <i class="fas fa-heart-broken me-1"></i>Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
