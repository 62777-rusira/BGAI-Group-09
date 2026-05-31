<?php
require_once __DIR__ . '/../config/db.php';

// --- GET Parameters ---
$search       = trim($_GET['search'] ?? '');
$categorySlugs = $_GET['cats'] ?? [];
if (!is_array($categorySlugs)) $categorySlugs = [$categorySlugs];
$categorySlugs = array_filter($categorySlugs);

// Legacy single-category support
$legacyCat = $_GET['category'] ?? '';
if ($legacyCat !== '' && empty($categorySlugs)) {
    $categorySlugs = [$legacyCat];
}

$priceMin     = $_GET['price_min'] ?? '';
$priceMax     = $_GET['price_max'] ?? '';
$material     = trim($_GET['material'] ?? '');
$inStockOnly  = isset($_GET['in_stock']);
$sort         = $_GET['sort'] ?? 'newest';
$view         = $_GET['view'] ?? 'grid';
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 12;
$offset       = ($page - 1) * $perPage;

// --- Fetch all active categories ---
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// --- Fetch distinct materials ---
$materials = $pdo->query("SELECT DISTINCT material FROM products WHERE is_active = 1 AND material IS NOT NULL AND material != '' ORDER BY material")->fetchAll(PDO::FETCH_COLUMN);

// --- Build WHERE clauses ---
$where  = ['p.is_active = 1'];
$params = [];

if (!empty($categorySlugs)) {
    $placeholders = implode(',', array_fill(0, count($categorySlugs), '?'));
    $where[] = "c.slug IN ($placeholders)";
    $params = array_merge($params, $categorySlugs);
}

if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.description LIKE ? OR p.material LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($priceMin !== '' && is_numeric($priceMin)) {
    $where[]  = 'p.price_aud >= ?';
    $params[] = (float)$priceMin;
}
if ($priceMax !== '' && is_numeric($priceMax)) {
    $where[]  = 'p.price_aud <= ?';
    $params[] = (float)$priceMax;
}

if ($material !== '') {
    $where[]  = 'p.material = ?';
    $params[] = $material;
}

if ($inStockOnly) {
    $where[] = 'p.stock > 0';
}

$whereSql = implode(' AND ', $where);

// --- Sort ---
$orderMap = [
    'price_asc'  => 'p.price_aud ASC',
    'price_desc' => 'p.price_aud DESC',
    'newest'     => 'p.created_at DESC',
    'name_asc'   => 'p.name ASC',
    'rating'     => 'avg_rating DESC',
];
$orderBy = $orderMap[$sort] ?? 'p.created_at DESC';

// --- Count total ---
$countSql = "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages    = max(1, ceil($totalProducts / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// --- Fetch products with avg rating ---
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
               COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id AND r.is_approved = 1), 0) AS avg_rating,
               COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.is_approved = 1), 0) AS review_count
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE $whereSql
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// --- Price range for display ---
$priceRange = $pdo->query("SELECT MIN(price_aud) as min_price, MAX(price_aud) as max_price FROM products WHERE is_active = 1")->fetch();

// --- Current category names for heading ---
$currentCategoryNames = [];
if (!empty($categorySlugs)) {
    foreach ($categories as $cat) {
        if (in_array($cat['slug'], $categorySlugs)) {
            $currentCategoryNames[] = $cat['name'];
        }
    }
}

// --- Build base query string (without page) ---
function buildFilterUrl($extraParams = []) {
    $params = $_GET;
    unset($params['page']);
    $params = array_merge($params, $extraParams);
    $qs = http_build_query($params);
    return BASE_URL . '/products/shop.php' . ($qs ? '?' . $qs : '');
}

$hasFilters = !empty($categorySlugs) || $search !== '' || $priceMin !== '' || $priceMax !== '' || $material !== '' || $inStockOnly;

$pageTitle = !empty($currentCategoryNames)
    ? implode(' & ', $currentCategoryNames)
    : ($search !== '' ? 'Search Results' : 'Shop');
include __DIR__ . '/../includes/header.php';
?>

<!-- Shop Page Styles -->
<style>
/* --- Search Hero --- */
.shop-search-hero {
    position: relative;
    padding: 3rem 0 2rem;
    text-align: center;
}
.shop-search-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--gold-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}
.shop-search-hero .subtitle {
    color: var(--text-secondary);
    font-size: 1rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 2rem;
}
.search-glass {
    max-width: 600px;
    margin: 0 auto;
    position: relative;
}
.search-glass input {
    width: 100%;
    padding: 1rem 1.5rem 1rem 3.2rem;
    border: 1px solid var(--dark-border);
    border-radius: 60px;
    background: rgba(255,255,255,0.03);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    color: var(--text-primary);
    font-size: 1rem;
    transition: var(--transition);
    outline: none;
}
.search-glass input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 30px rgba(185,151,91,0.15);
    background: rgba(255,255,255,0.06);
}
.search-glass input::placeholder { color: var(--text-dim); }
.search-glass .search-icon {
    position: absolute;
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gold);
    font-size: 1rem;
}
.search-glass button {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    border: none;
    color: #0a0a0a;
    padding: 0.6rem 1.5rem;
    border-radius: 60px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition);
}
.search-glass button:hover {
    box-shadow: 0 0 20px var(--gold-glow);
    transform: translateY(-50%) scale(1.03);
}

/* --- Filter Sidebar --- */
.filter-sidebar {
    position: sticky;
    top: 100px;
}
.filter-card {
    background: rgba(255,255,255,0.02);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--dark-border);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.2rem;
    transition: var(--transition);
}
.filter-card:hover {
    border-color: var(--dark-border-hover);
}
.filter-card h6 {
    font-family: 'Playfair Display', serif;
    color: var(--gold);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.filter-card h6 i { font-size: 0.8rem; }

/* Custom checkbox */
.filter-check {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.4rem 0;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 0.88rem;
    transition: var(--transition-fast);
}
.filter-check:hover { color: var(--gold-light); }
.filter-check input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 1.5px solid var(--dark-border);
    border-radius: 4px;
    background: rgba(255,255,255,0.03);
    cursor: pointer;
    transition: var(--transition-fast);
    flex-shrink: 0;
    position: relative;
}
.filter-check input[type="checkbox"]:checked {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    border-color: var(--gold);
}
.filter-check input[type="checkbox"]:checked::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: #0a0a0a;
    font-size: 0.6rem;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Price range inputs */
.price-range-inputs {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
.price-range-inputs input {
    flex: 1;
    padding: 0.5rem 0.7rem;
    border: 1px solid var(--dark-border);
    border-radius: 8px;
    background: rgba(255,255,255,0.03);
    color: var(--text-primary);
    font-size: 0.85rem;
    outline: none;
    transition: var(--transition-fast);
}
.price-range-inputs input:focus {
    border-color: var(--gold);
}
.price-range-inputs span {
    color: var(--text-dim);
    font-size: 0.8rem;
}

/* Toggle switch */
.toggle-switch {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 0.88rem;
}
.toggle-switch input { display: none; }
.toggle-track {
    width: 42px;
    height: 22px;
    background: rgba(255,255,255,0.08);
    border-radius: 11px;
    position: relative;
    transition: var(--transition-fast);
}
.toggle-track::after {
    content: '';
    width: 16px;
    height: 16px;
    background: var(--text-dim);
    border-radius: 50%;
    position: absolute;
    top: 3px;
    left: 3px;
    transition: var(--transition-fast);
}
.toggle-switch input:checked + .toggle-track {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
}
.toggle-switch input:checked + .toggle-track::after {
    background: #0a0a0a;
    left: 23px;
}

/* --- Toolbar --- */
.shop-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem 1.2rem;
    background: rgba(255,255,255,0.02);
    backdrop-filter: blur(20px);
    border: 1px solid var(--dark-border);
    border-radius: 12px;
}
.shop-toolbar .product-count {
    color: var(--text-secondary);
    font-size: 0.88rem;
}
.shop-toolbar .product-count strong {
    color: var(--gold);
}
.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}
.sort-select {
    padding: 0.5rem 2.2rem 0.5rem 0.9rem;
    border: 1px solid var(--dark-border);
    border-radius: 8px;
    background: rgba(255,255,255,0.03);
    color: var(--text-secondary);
    font-size: 0.85rem;
    outline: none;
    cursor: pointer;
    transition: var(--transition-fast);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23a09890'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.8rem center;
}
.sort-select:focus { border-color: var(--gold); }
.sort-select option { background: #1a1a1a; color: var(--text-primary); }

.view-toggle {
    display: flex;
    border: 1px solid var(--dark-border);
    border-radius: 8px;
    overflow: hidden;
}
.view-btn {
    background: transparent;
    border: none;
    color: var(--text-dim);
    padding: 0.5rem 0.7rem;
    cursor: pointer;
    transition: var(--transition-fast);
    font-size: 0.9rem;
}
.view-btn.active {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #0a0a0a;
}
.view-btn:hover:not(.active) { color: var(--gold); }

/* --- Product Grid --- */
.products-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
.products-grid.list-view {
    grid-template-columns: 1fr;
}
.products-grid.list-view .product-card-wrap .product-card {
    display: grid;
    grid-template-columns: 280px 1fr;
}
.products-grid.list-view .product-card-wrap .image-wrapper {
    border-radius: 16px 0 0 16px;
    height: 100%;
}
.products-grid.list-view .product-card-wrap .product-image {
    height: 100%;
}
.products-grid.list-view .product-card-wrap .card-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Product Card */
.product-card-wrap {
    perspective: 800px;
}
.product-card {
    background: rgba(255,255,255,0.02);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--dark-border);
    border-radius: 16px;
    overflow: hidden;
    transition: var(--transition);
    transform-style: preserve-3d;
    will-change: transform;
    height: 100%;
}
.product-card:hover {
    border-color: var(--dark-border-hover);
    box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 40px rgba(185,151,91,0.08);
}
.product-card .image-wrapper {
    position: relative;
    overflow: hidden;
    height: 280px;
    background: rgba(0,0,0,0.2);
}
.product-card .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.product-card:hover .product-image {
    transform: scale(1.08);
}

/* Overlay actions on image */
.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 50%);
    opacity: 0;
    pointer-events: none;
    transition: var(--transition);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 1.2rem;
    gap: 0.6rem;
    z-index: 5;
}
.product-card:hover .card-overlay { opacity: 1; pointer-events: auto; }
.overlay-btn {
    padding: 0.5rem 1.2rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: var(--transition-fast);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.overlay-btn-primary {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #0a0a0a;
}
.overlay-btn-primary:hover {
    box-shadow: 0 0 15px var(--gold-glow);
    transform: translateY(-2px);
    color: #0a0a0a;
}
.overlay-btn-ghost {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.15);
}
.overlay-btn-ghost:hover {
    background: rgba(255,255,255,0.2);
    color: #fff;
}

/* Badges on card */
.badge-float {
    position: absolute;
    top: 1rem;
    z-index: 2;
    padding: 0.35rem 0.8rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.badge-category {
    left: 1rem;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(10px);
    color: var(--gold-light);
    border: 1px solid rgba(185,151,91,0.2);
}
.badge-featured {
    right: 1rem;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #0a0a0a;
}
.wishlist-float {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 3;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition-fast);
    color: rgba(255,255,255,0.6);
    text-decoration: none;
}
.wishlist-float:hover, .wishlist-float.active {
    background: rgba(185,151,91,0.2);
    border-color: var(--gold);
    color: var(--gold);
}
.wishlist-float.active { color: #e74c3c; }

/* Card body */
.product-card .card-body {
    padding: 1.3rem 1.3rem 1.5rem;
}
.card-rating {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
}
.card-rating .count {
    font-size: 0.75rem;
    color: var(--text-dim);
}
.product-card .card-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.3rem;
    line-height: 1.3;
}
.product-card .card-name a {
    color: inherit;
    text-decoration: none;
    transition: var(--transition-fast);
}
.product-card .card-name a:hover { color: var(--gold); }
.product-card .card-material {
    font-size: 0.78rem;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.8rem;
}
.product-card .card-bottom {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-top: auto;
}
.product-card .card-price {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--gold);
}
.product-card .card-stock {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.card-stock.in-stock {
    background: rgba(46,204,113,0.1);
    color: #2ecc71;
    border: 1px solid rgba(46,204,113,0.2);
}
.card-stock.low-stock {
    background: rgba(241,196,15,0.1);
    color: #f1c40f;
    border: 1px solid rgba(241,196,15,0.2);
}
.card-stock.out-of-stock {
    background: rgba(231,76,60,0.1);
    color: #e74c3c;
    border: 1px solid rgba(231,76,60,0.2);
}

/* --- Mobile Filter Toggle --- */
.mobile-filter-toggle {
    display: none;
    width: 100%;
    padding: 0.8rem;
    margin-bottom: 1rem;
    border: 1px solid var(--dark-border);
    border-radius: 12px;
    background: rgba(255,255,255,0.02);
    color: var(--gold);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--transition-fast);
    text-align: center;
}
.mobile-filter-toggle:hover {
    border-color: var(--gold);
    background: rgba(185,151,91,0.05);
}

/* --- Active Filters Pills --- */
.active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.8rem;
    border-radius: 20px;
    background: rgba(185,151,91,0.1);
    border: 1px solid rgba(185,151,91,0.2);
    color: var(--gold-light);
    font-size: 0.78rem;
    text-decoration: none;
    transition: var(--transition-fast);
}
.filter-pill:hover {
    background: rgba(185,151,91,0.2);
    color: var(--gold);
}
.filter-pill i { font-size: 0.65rem; }

/* --- Pagination --- */
.shop-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.3rem;
    margin-top: 3rem;
    padding: 1rem 0;
}
.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 0.5rem;
    border-radius: 10px;
    border: 1px solid var(--dark-border);
    background: rgba(255,255,255,0.02);
    color: var(--text-secondary);
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition-fast);
}
.page-btn:hover:not(.active):not(.disabled) {
    border-color: var(--gold);
    color: var(--gold);
    background: rgba(185,151,91,0.05);
}
.page-btn.active {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #0a0a0a;
    border-color: var(--gold);
    font-weight: 700;
}
.page-btn.disabled {
    opacity: 0.3;
    pointer-events: none;
}
.page-ellipsis {
    color: var(--text-dim);
    padding: 0 0.3rem;
    font-size: 0.88rem;
}

/* --- Animations --- */
.product-card-wrap {
    animation: cardFadeIn 0.5s ease both;
}
@keyframes cardFadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}
.product-card-wrap:nth-child(1)  { animation-delay: 0.05s; }
.product-card-wrap:nth-child(2)  { animation-delay: 0.10s; }
.product-card-wrap:nth-child(3)  { animation-delay: 0.15s; }
.product-card-wrap:nth-child(4)  { animation-delay: 0.20s; }
.product-card-wrap:nth-child(5)  { animation-delay: 0.25s; }
.product-card-wrap:nth-child(6)  { animation-delay: 0.30s; }
.product-card-wrap:nth-child(7)  { animation-delay: 0.35s; }
.product-card-wrap:nth-child(8)  { animation-delay: 0.40s; }
.product-card-wrap:nth-child(9)  { animation-delay: 0.45s; }
.product-card-wrap:nth-child(10) { animation-delay: 0.50s; }
.product-card-wrap:nth-child(11) { animation-delay: 0.55s; }
.product-card-wrap:nth-child(12) { animation-delay: 0.60s; }

/* --- Filter apply button --- */
.btn-apply-filters {
    width: 100%;
    padding: 0.7rem;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #0a0a0a;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    transition: var(--transition);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.btn-apply-filters:hover {
    box-shadow: 0 0 25px var(--gold-glow);
    transform: translateY(-1px);
}
.btn-clear-filters {
    width: 100%;
    padding: 0.5rem;
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--dark-border);
    border-radius: 10px;
    font-size: 0.82rem;
    cursor: pointer;
    transition: var(--transition-fast);
    margin-top: 0.5rem;
}
.btn-clear-filters:hover {
    border-color: var(--gold);
    color: var(--gold);
}

/* --- Empty state --- */
.shop-empty {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255,255,255,0.02);
    border: 1px dashed var(--dark-border);
    border-radius: 16px;
}
.shop-empty .empty-icon {
    font-size: 3.5rem;
    color: var(--gold);
    margin-bottom: 1.2rem;
    opacity: 0.6;
}
.shop-empty h4 {
    font-family: 'Playfair Display', serif;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.shop-empty p { color: var(--text-secondary); margin-bottom: 1.5rem; }

/* --- Responsive --- */
@media (max-width: 991.98px) {
    .mobile-filter-toggle { display: block; }
    .filter-sidebar-col { display: none; }
    .filter-sidebar-col.show { display: block; }
    .products-grid { grid-template-columns: repeat(2, 1fr); }
    .products-grid.list-view { grid-template-columns: 1fr; }
    .products-grid.list-view .product-card { grid-template-columns: 200px 1fr; }
}
@media (max-width: 575.98px) {
    .products-grid { grid-template-columns: 1fr; }
    .products-grid.list-view .product-card { grid-template-columns: 1fr; }
    .shop-search-hero h1 { font-size: 2rem; }
    .shop-toolbar { flex-direction: column; align-items: stretch; }
    .toolbar-actions { justify-content: space-between; }
}
</style>

<div class="container py-4">

    <!-- Search Hero -->
    <div class="shop-search-hero animate-in">
        <h1><?= sanitize(!empty($currentCategoryNames) ? implode(' & ', $currentCategoryNames) : ($search !== '' ? 'Search: "' . $search . '"' : 'Our Collections')) ?></h1>
        <p class="subtitle">Discover luxury handcrafted jewellery</p>
        <form method="GET" action="<?= BASE_URL ?>/products/shop.php" class="search-glass">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="search" placeholder="Search by name, description, or material..." value="<?= sanitize($search) ?>" autocomplete="off">
            <!-- Preserve other filters when searching -->
            <?php foreach ($categorySlugs as $cs): ?>
                <input type="hidden" name="cats[]" value="<?= sanitize($cs) ?>">
            <?php endforeach; ?>
            <?php if ($sort !== 'newest'): ?>
                <input type="hidden" name="sort" value="<?= sanitize($sort) ?>">
            <?php endif; ?>
            <button type="submit"><i class="fas fa-search me-1"></i> Search</button>
        </form>
    </div>

    <div class="section-divider mb-4"></div>

    <!-- Mobile Filter Toggle -->
    <button class="mobile-filter-toggle" id="mobileFilterToggle">
        <i class="fas fa-sliders-h me-2"></i> Filters & Sorting
        <?php if ($hasFilters): ?>
            <span style="background:var(--gold);color:#0a0a0a;padding:2px 8px;border-radius:10px;font-size:0.72rem;margin-left:0.5rem;">Active</span>
        <?php endif; ?>
    </button>

    <div class="row g-4">

        <!-- Sidebar Filters -->
        <div class="col-lg-3 filter-sidebar-col" id="filterSidebar">
            <form method="GET" action="<?= BASE_URL ?>/products/shop.php" id="filterForm">
                <div class="filter-sidebar">

                    <!-- Preserve search & sort -->
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                    <?php endif; ?>
                    <?php if ($sort !== 'newest'): ?>
                        <input type="hidden" name="sort" value="<?= sanitize($sort) ?>">
                    <?php endif; ?>
                    <?php if ($view !== 'grid'): ?>
                        <input type="hidden" name="view" value="<?= sanitize($view) ?>">
                    <?php endif; ?>

                    <!-- Categories -->
                    <div class="filter-card animate-in">
                        <h6><i class="fas fa-tags"></i> Categories</h6>
                        <?php foreach ($categories as $cat): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="cats[]" value="<?= sanitize($cat['slug']) ?>"
                                    <?= in_array($cat['slug'], $categorySlugs) ? 'checked' : '' ?>>
                                <span><?= sanitize($cat['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-card animate-in delay-1">
                        <h6><i class="fas fa-dollar-sign"></i> Price Range</h6>
                        <div class="price-range-inputs">
                            <input type="number" name="price_min" placeholder="Min" value="<?= sanitize($priceMin) ?>" min="0" step="1">
                            <span>&mdash;</span>
                            <input type="number" name="price_max" placeholder="Max" value="<?= sanitize($priceMax) ?>" min="0" step="1">
                        </div>
                        <?php if ($priceRange): ?>
                            <div style="font-size:0.72rem;color:var(--text-dim);margin-top:0.5rem;">
                                Range: <?= formatPrice($priceRange['min_price']) ?> &ndash; <?= formatPrice($priceRange['max_price']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Material -->
                    <?php if (!empty($materials)): ?>
                    <div class="filter-card animate-in delay-2">
                        <h6><i class="fas fa-gem"></i> Material</h6>
                        <select name="material" class="sort-select" style="width:100%;">
                            <option value="">All Materials</option>
                            <?php foreach ($materials as $mat): ?>
                                <option value="<?= sanitize($mat) ?>" <?= $material === $mat ? 'selected' : '' ?>>
                                    <?= sanitize($mat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- In Stock Toggle -->
                    <div class="filter-card animate-in delay-3">
                        <h6><i class="fas fa-box-open"></i> Availability</h6>
                        <label class="toggle-switch">
                            <span>In Stock Only</span>
                            <input type="checkbox" name="in_stock" value="1" <?= $inStockOnly ? 'checked' : '' ?>>
                            <div class="toggle-track"></div>
                        </label>
                    </div>

                    <!-- Apply / Clear -->
                    <button type="submit" class="btn-apply-filters animate-in delay-4">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    <?php if ($hasFilters): ?>
                        <a href="<?= BASE_URL ?>/products/shop.php" class="btn-clear-filters d-block text-center text-decoration-none">
                            <i class="fas fa-times me-1"></i> Clear All Filters
                        </a>
                    <?php endif; ?>

                </div>
            </form>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">

            <!-- Active Filter Pills -->
            <?php if ($hasFilters): ?>
                <div class="active-filters animate-in">
                    <?php foreach ($categorySlugs as $cs): ?>
                        <?php
                        $catName = $cs;
                        foreach ($categories as $c) { if ($c['slug'] === $cs) { $catName = $c['name']; break; } }
                        // Build URL without this category
                        $remaining = array_diff($categorySlugs, [$cs]);
                        $removeParams = $_GET;
                        unset($removeParams['page']);
                        $removeParams['cats'] = array_values($remaining);
                        if (empty($removeParams['cats'])) unset($removeParams['cats']);
                        unset($removeParams['category']);
                        ?>
                        <a href="<?= BASE_URL ?>/products/shop.php?<?= http_build_query($removeParams) ?>" class="filter-pill">
                            <?= sanitize($catName) ?> <i class="fas fa-times"></i>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($search !== ''): ?>
                        <?php $rp = $_GET; unset($rp['search'], $rp['page']); ?>
                        <a href="<?= BASE_URL ?>/products/shop.php?<?= http_build_query($rp) ?>" class="filter-pill">
                            Search: "<?= sanitize($search) ?>" <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($priceMin !== '' || $priceMax !== ''): ?>
                        <?php $rp = $_GET; unset($rp['price_min'], $rp['price_max'], $rp['page']); ?>
                        <a href="<?= BASE_URL ?>/products/shop.php?<?= http_build_query($rp) ?>" class="filter-pill">
                            Price: <?= $priceMin !== '' ? '$'.$priceMin : '...' ?> &ndash; <?= $priceMax !== '' ? '$'.$priceMax : '...' ?> <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($material !== ''): ?>
                        <?php $rp = $_GET; unset($rp['material'], $rp['page']); ?>
                        <a href="<?= BASE_URL ?>/products/shop.php?<?= http_build_query($rp) ?>" class="filter-pill">
                            <?= sanitize($material) ?> <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($inStockOnly): ?>
                        <?php $rp = $_GET; unset($rp['in_stock'], $rp['page']); ?>
                        <a href="<?= BASE_URL ?>/products/shop.php?<?= http_build_query($rp) ?>" class="filter-pill">
                            In Stock Only <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Toolbar: Count + Sort + View -->
            <div class="shop-toolbar animate-in">
                <div class="product-count">
                    Showing <strong><?= count($products) ?></strong> of <strong><?= $totalProducts ?></strong> product<?= $totalProducts !== 1 ? 's' : '' ?>
                </div>
                <div class="toolbar-actions">
                    <!-- Sort -->
                    <select class="sort-select" id="sortSelect" onchange="applySort(this.value)">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A &ndash; Z</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                    </select>
                    <!-- View Toggle -->
                    <div class="view-toggle">
                        <button type="button" class="view-btn <?= $view === 'grid' ? 'active' : '' ?>" data-view="grid" title="Grid View">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="view-btn <?= $view === 'list' ? 'active' : '' ?>" data-view="list" title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <?php if (empty($products)): ?>
                <div class="shop-empty animate-in">
                    <div class="empty-icon"><i class="fas fa-gem"></i></div>
                    <h4>No products found</h4>
                    <p>Try adjusting your filters or search to find what you're looking for.</p>
                    <a href="<?= BASE_URL ?>/products/shop.php" class="btn btn-gold">
                        <i class="fas fa-th me-1"></i> View All Products
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid <?= $view === 'list' ? 'list-view' : '' ?>" id="productsGrid">
                    <?php foreach ($products as $idx => $p):
                        $rating = ['avg' => round($p['avg_rating'], 1), 'count' => (int)$p['review_count']];
                        $inWishlist = isLoggedIn() ? isInWishlist($pdo, $_SESSION['user_id'], $p['id']) : false;
                    ?>
                        <div class="product-card-wrap">
                            <div class="product-card" data-tilt>
                                <!-- Image -->
                                <div class="image-wrapper">
                                    <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $p['id'] ?>">
                                        <img src="<?= BASE_URL ?>/uploads/<?= sanitize($p['image']) ?>"
                                             alt="<?= sanitize($p['name']) ?>"
                                             class="product-image" loading="lazy">
                                    </a>

                                    <!-- Badges -->
                                    <span class="badge-float badge-category"><?= sanitize($p['category_name']) ?></span>
                                    <?php if (!empty($p['is_featured'])): ?>
                                        <span class="badge-float badge-featured"><i class="fas fa-star me-1"></i>Featured</span>
                                    <?php else: ?>
                                        <!-- Wishlist heart -->
                                        <a href="<?= isLoggedIn() ? BASE_URL . '/products/wishlist-toggle.php?product_id=' . $p['id'] . '&redirect=' . urlencode($_SERVER['REQUEST_URI']) : BASE_URL . '/auth/login.php' ?>"
                                           class="wishlist-float <?= $inWishlist ? 'active' : '' ?>"
                                           title="<?= $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                                            <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($p['is_featured'])): ?>
                                        <!-- Show wishlist below featured badge -->
                                        <a href="<?= isLoggedIn() ? BASE_URL . '/products/wishlist-toggle.php?product_id=' . $p['id'] . '&redirect=' . urlencode($_SERVER['REQUEST_URI']) : BASE_URL . '/auth/login.php' ?>"
                                           class="wishlist-float <?= $inWishlist ? 'active' : '' ?>"
                                           style="top:3.2rem;"
                                           title="<?= $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                                            <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Hover overlay -->
                                    <div class="card-overlay">
                                        <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $p['id'] ?>" class="overlay-btn overlay-btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($p['stock'] > 0): ?>
                                            <a href="<?= BASE_URL ?>/cart/add.php?product_id=<?= $p['id'] ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                               class="overlay-btn overlay-btn-ghost">
                                                <i class="fas fa-shopping-bag"></i> Add to Cart
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="card-body">
                                    <!-- Rating -->
                                    <div class="card-rating">
                                        <?= renderStars($rating['avg'], '0.75rem') ?>
                                        <span class="count">(<?= $rating['count'] ?>)</span>
                                    </div>

                                    <!-- Name -->
                                    <div class="card-name">
                                        <a href="<?= BASE_URL ?>/products/detail.php?id=<?= $p['id'] ?>">
                                            <?= sanitize($p['name']) ?>
                                        </a>
                                    </div>

                                    <!-- Material -->
                                    <?php if (!empty($p['material'])): ?>
                                        <div class="card-material">
                                            <i class="fas fa-gem me-1" style="font-size:0.65rem;"></i><?= sanitize($p['material']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Price & Stock -->
                                    <div class="card-bottom">
                                        <span class="card-price"><?= formatPrice($p['price_aud']) ?></span>
                                        <?php if ($p['stock'] <= 0): ?>
                                            <span class="card-stock out-of-stock">Sold Out</span>
                                        <?php elseif ($p['stock'] <= 5): ?>
                                            <span class="card-stock low-stock">Only <?= $p['stock'] ?> left</span>
                                        <?php else: ?>
                                            <span class="card-stock in-stock">In Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="shop-pagination">
                        <!-- Previous -->
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                           class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>

                        <?php
                        // Smart pagination: show first, last, and nearby pages
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end   = min($totalPages, $page + $range);

                        if ($start > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="page-btn">1</a>
                            <?php if ($start > 2): ?><span class="page-ellipsis">...</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?><span class="page-ellipsis">...</span><?php endif; ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" class="page-btn"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <!-- Next -->
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                           class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Shop Page Scripts -->
<script>
// --- Sort handler ---
function applySort(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// --- Grid / List toggle ---
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const view = btn.dataset.view;
        const grid = document.getElementById('productsGrid');
        const url = new URL(window.location.href);

        // Toggle class
        if (view === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }

        // Update active state
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update URL without reload
        url.searchParams.set('view', view);
        history.replaceState(null, '', url.toString());
    });
});

// --- Mobile filter toggle ---
document.getElementById('mobileFilterToggle')?.addEventListener('click', () => {
    const sidebar = document.getElementById('filterSidebar');
    sidebar.classList.toggle('show');
    const btn = document.getElementById('mobileFilterToggle');
    const isOpen = sidebar.classList.contains('show');
    btn.innerHTML = isOpen
        ? '<i class="fas fa-times me-2"></i> Close Filters'
        : '<i class="fas fa-sliders-h me-2"></i> Filters & Sorting';
});

// --- 3D Tilt Effect ---
document.querySelectorAll('[data-tilt]').forEach(card => {
    const wrap = card.closest('.product-card-wrap');

    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = ((y - centerY) / centerY) * -6;
        const rotateY = ((x - centerX) / centerX) * 6;

        card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(800px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
        card.style.transition = 'transform 0.5s ease';
    });

    card.addEventListener('mouseenter', () => {
        card.style.transition = 'transform 0.1s ease';
    });
});

// --- Intersection Observer for scroll animations ---
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card-wrap').forEach(card => {
    card.style.animationPlayState = 'paused';
    observer.observe(card);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
