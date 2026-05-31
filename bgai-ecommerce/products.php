<?php
// ============================================
// Products Catalogue Page
// ============================================
$pageTitle = 'Shop';
require_once __DIR__ . '/includes/header.php';

// Get filter parameters
$categorySlug = $_GET['category'] ?? '';
$collectionSlug = $_GET['collection'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$metalType = $_GET['metal'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$page = $_GET['page'] ?? 1;

// Resolve category/collection IDs
$categoryId = null;
$collectionId = null;
$activeCategory = null;
$activeCollection = null;
$allCategories = getCategories();
$allCollections = getCollections();

if ($categorySlug) {
    foreach ($allCategories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $categoryId = $cat['id'];
            $activeCategory = $cat;
            $pageTitle = $cat['name'];
            break;
        }
    }
}

if ($collectionSlug) {
    foreach ($allCollections as $col) {
        if ($col['slug'] === $collectionSlug) {
            $collectionId = $col['id'];
            $activeCollection = $col;
            $pageTitle = $col['name'] . ' Collection';
            break;
        }
    }
}

// Build filters
$filters = [
    'category_id' => $categoryId,
    'collection_id' => $collectionId,
    'search' => $search,
    'sort' => $sort,
    'metal_type' => $metalType,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'page' => $page,
    'limit' => 12
];

$products = getProducts($filters);
?>

<!-- Page Header -->
<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo APP_URL; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo APP_URL; ?>/products.php">Shop</a>
            <?php if ($activeCategory): ?>
                <span>/</span>
                <span class="current"><?php echo e($activeCategory['name']); ?></span>
            <?php elseif ($activeCollection): ?>
                <span>/</span>
                <span class="current"><?php echo e($activeCollection['name']); ?></span>
            <?php elseif ($search): ?>
                <span>/</span>
                <span class="current">Search: "<?php echo e($search); ?>"</span>
            <?php endif; ?>
        </div>
        
        <?php if ($activeCategory): ?>
            <div class="section-header" style="text-align:left; max-width:100%;">
                <h1 class="section-title" style="margin-bottom:var(--space-sm);"><?php echo e($activeCategory['name']); ?></h1>
                <p class="section-description"><?php echo e($activeCategory['description']); ?></p>
            </div>
        <?php elseif ($activeCollection): ?>
            <div class="section-header" style="text-align:left; max-width:100%;">
                <h1 class="section-title" style="margin-bottom:var(--space-sm);"><?php echo e($activeCollection['name']); ?></h1>
                <p class="section-description"><?php echo e($activeCollection['description']); ?></p>
            </div>
        <?php else: ?>
            <div class="section-header" style="text-align:left; max-width:100%;">
                <h1 class="section-title" style="margin-bottom:var(--space-sm);">
                    <?php echo $search ? 'Search Results' : 'All Jewellery'; ?>
                </h1>
                <?php if ($search): ?>
                    <p class="section-description">Showing results for "<?php echo e($search); ?>"</p>
                <?php else: ?>
                    <p class="section-description">Explore our complete collection of handcrafted Australian jewellery</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Shop Content -->
<section class="section" style="padding-top:var(--space-lg);">
    <div class="container">
        <div class="shop-layout">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar" id="filtersSidebar">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xl);">
                    <h3 style="font-family:var(--font-heading);">Filters</h3>
                    <a href="<?php echo APP_URL; ?>/products.php" style="font-size:0.85rem; color:var(--gold-dark);">Clear All</a>
                </div>

                <!-- Categories -->
                <div class="filter-group">
                    <h4 class="filter-title">Category</h4>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" name="category" value="rings" <?php echo $categorySlug === 'rings' ? 'checked' : ''; ?>>
                            Rings
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="category" value="necklaces" <?php echo $categorySlug === 'necklaces' ? 'checked' : ''; ?>>
                            Necklaces
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="category" value="bracelets" <?php echo $categorySlug === 'bracelets' ? 'checked' : ''; ?>>
                            Bracelets
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="category" value="earrings" <?php echo $categorySlug === 'earrings' ? 'checked' : ''; ?>>
                            Earrings
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="category" value="pendants" <?php echo $categorySlug === 'pendants' ? 'checked' : ''; ?>>
                            Pendants
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="category" value="brooches" <?php echo $categorySlug === 'brooches' ? 'checked' : ''; ?>>
                            Brooches
                        </label>
                    </div>
                </div>

                <!-- Collections -->
                <div class="filter-group">
                    <h4 class="filter-title">Collection</h4>
                    <div class="filter-options">
                        <?php foreach ($allCollections as $col): ?>
                            <label class="filter-option">
                                <input type="checkbox" name="collection" value="<?php echo e($col['slug']); ?>" <?php echo $collectionSlug === $col['slug'] ? 'checked' : ''; ?>>
                                <?php echo e($col['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Metal Type -->
                <div class="filter-group">
                    <h4 class="filter-title">Metal Type</h4>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" name="metal" value="Gold" <?php echo $metalType === 'Gold' ? 'checked' : ''; ?>>
                            Gold
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="metal" value="White Gold" <?php echo $metalType === 'White Gold' ? 'checked' : ''; ?>>
                            White Gold
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="metal" value="Rose Gold" <?php echo $metalType === 'Rose Gold' ? 'checked' : ''; ?>>
                            Rose Gold
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="metal" value="Platinum" <?php echo $metalType === 'Platinum' ? 'checked' : ''; ?>>
                            Platinum
                        </label>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="filter-group">
                    <h4 class="filter-title">Price Range</h4>
                    <form method="GET" class="price-range-form">
                        <input type="hidden" name="category" value="<?php echo e($categorySlug); ?>">
                        <input type="hidden" name="collection" value="<?php echo e($collectionSlug); ?>">
                        <input type="hidden" name="search" value="<?php echo e($search); ?>">
                        <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo e($minPrice); ?>">
                            <span>—</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo e($maxPrice); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm btn-block mt-1" style="margin-top:var(--space-md);">Apply Price Filter</button>
                    </form>
                </div>
            </aside>

            <!-- Products Area -->
            <div class="shop-products">
                <div class="shop-header">
                    <p class="shop-results">
                        Showing <?php echo count($products['products']); ?> of <?php echo $products['total']; ?> results
                    </p>
                    <div style="display:flex; gap:var(--space-md); align-items:center;">
                        <button class="btn btn-sm btn-outline-dark filter-toggle-btn" onclick="document.getElementById('filtersSidebar').classList.toggle('show')">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                        <form method="GET" class="shop-sort">
                            <input type="hidden" name="category" value="<?php echo e($categorySlug); ?>">
                            <input type="hidden" name="collection" value="<?php echo e($collectionSlug); ?>">
                            <input type="hidden" name="search" value="<?php echo e($search); ?>">
                            <select name="sort" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                            </select>
                        </form>
                    </div>
                </div>

                <?php if (count($products['products']) > 0): ?>
                    <div class="products-grid">
                        <?php foreach ($products['products'] as $product): 
                            $pricing = getProductPrice($product['id']);
                            $effectivePrice = $pricing ? getEffectivePrice($pricing) : $product['base_price'];
                            $discount = $pricing ? getDiscountPercent($pricing['price'], $pricing['sale_price']) : 0;
                        ?>
                            <div class="product-card">
                                <div class="product-card-image">
                                    <?php if ($product['primary_image']): ?>
                                        <img src="<?php echo APP_URL; ?>/<?php echo e($product['primary_image']); ?>" alt="<?php echo e($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder"><i class="fas fa-gem"></i></div>
                                    <?php endif; ?>
                                    <div class="product-badges">
                                        <?php if ($discount > 0): ?>
                                            <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_new_arrival']): ?>
                                            <span class="badge badge-new">New</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_bestseller']): ?>
                                            <span class="badge badge-bestseller">Bestseller</span>
                                        <?php endif; ?>
                                        <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                            <span class="badge" style="background:var(--warning);color:var(--white);">Low Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-quick-actions">
                                        <button class="quick-action-btn" onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart">
                                            <i class="fas fa-shopping-bag"></i>
                                        </button>
                                        <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="quick-action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-card-body">
                                    <div class="product-card-category"><?php echo e($product['category_name'] ?? 'Jewellery'); ?></div>
                                    <h3 class="product-card-title">
                                        <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>">
                                            <?php echo e($product['name']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-card-material"><?php echo e($product['metal_type']); ?> · <?php echo e($product['gemstone'] ?: 'Solid Metal'); ?></div>
                                    <div class="product-card-price">
                                        <span class="price-current"><?php echo formatPrice($effectivePrice); ?></span>
                                        <?php if ($discount > 0 && $pricing): ?>
                                            <span class="price-original"><?php echo formatPrice($pricing['price']); ?></span>
                                            <span class="price-discount">Save <?php echo $discount; ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($products['total_pages'] > 1): ?>
                        <div class="pagination">
                            <?php if ($products['page'] > 1): ?>
                                <a href="?page=<?php echo $products['page'] - 1; ?>&category=<?php echo e($categorySlug); ?>&collection=<?php echo e($collectionSlug); ?>&search=<?php echo e($search); ?>&sort=<?php echo e($sort); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $products['total_pages']; $i++): ?>
                                <?php if ($i === $products['page']): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&category=<?php echo e($categorySlug); ?>&collection=<?php echo e($collectionSlug); ?>&search=<?php echo e($search); ?>&sort=<?php echo e($sort); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($products['page'] < $products['total_pages']): ?>
                                <a href="?page=<?php echo $products['page'] + 1; ?>&category=<?php echo e($categorySlug); ?>&collection=<?php echo e($collectionSlug); ?>&search=<?php echo e($search); ?>&sort=<?php echo e($sort); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="cart-empty">
                        <i class="fas fa-search"></i>
                        <h2>No Products Found</h2>
                        <p class="text-muted">We couldn't find any jewellery matching your criteria. Try adjusting your filters.</p>
                        <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-primary mt-2" style="margin-top:var(--space-xl);">View All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
