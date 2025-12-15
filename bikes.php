<?php
require_once 'includes/functions.php';

$category = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');

$sql = "SELECT p.*, 
        (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as main_image
        FROM products p WHERE p.status = 'active'";

if ($category) {
    $sql .= " AND p.category = '$category'";
}
if ($search) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%' OR p.model LIKE '%$search%')";
}

switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY COALESCE(p.sale_price, p.base_price) ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY COALESCE(p.sale_price, p.base_price) DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.views DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$result = mysqli_query($con, $sql);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EV Bikes - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>EV Bikes Collection</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span>EV Bikes</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-8">
                    <form class="d-flex gap-2" method="GET">
                        <input type="text" name="search" class="form-control" placeholder="Search bikes..." value="<?= htmlspecialchars($search) ?>" style="max-width: 300px;">
                        <select name="category" class="form-control" style="max-width: 150px;">
                            <option value="">All Categories</option>
                            <option value="scooter" <?= $category === 'scooter' ? 'selected' : '' ?>>Scooter</option>
                            <option value="bike" <?= $category === 'bike' ? 'selected' : '' ?>>Bike</option>
                            <option value="cycle" <?= $category === 'cycle' ? 'selected' : '' ?>>Cycle</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <select name="sort" class="form-control d-inline-block" style="max-width: 180px;" onchange="window.location.href='?sort='+this.value+'&category=<?= $category ?>&search=<?= $search ?>'">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>
            </div>
            
            <p class="text-muted mb-4">Showing <?= count($products) ?> bikes</p>
            
            <div class="products-grid">
                <?php if (empty($products)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-motorcycle fa-4x text-muted mb-3"></i>
                    <h3>No bikes found</h3>
                    <p class="text-muted">Try adjusting your search or filters</p>
                    <a href="bikes.php" class="btn btn-primary">View All Bikes</a>
                </div>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product['featured']): ?>
                    <span class="badge badge-featured">Featured</span>
                    <?php endif; ?>
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['base_price']): ?>
                    <span class="badge badge-sale" style="left: auto; right: 15px;">Sale</span>
                    <?php endif; ?>
                    <div class="product-image">
                        <img src="<?= UPLOADS_URL ?>/products/<?= $product['main_image'] ?? 'placeholder.jpg' ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             onerror="this.src='https://via.placeholder.com/400x300?text=<?= urlencode($product['name']) ?>'">
                        <div class="product-actions">
                            <?php if (isLoggedIn()): ?>
                            <button onclick="addToWishlist(<?= $product['id'] ?>)" title="Add to Wishlist">
                                <i class="fas fa-heart"></i>
                            </button>
                            <?php endif; ?>
                            <button onclick="addToCompare(<?= $product['id'] ?>)" title="Compare">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>
                    </div>
                    <a href="bike-details.php?slug=<?= $product['slug'] ?>">
                        <div class="product-info">
                            <div class="product-category"><?= ucfirst($product['category']) ?></div>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-specs">
                                <?php if ($product['battery_capacity']): ?>
                                <div class="product-spec"><i class="fas fa-battery-full"></i> <?= $product['battery_capacity'] ?></div>
                                <?php endif; ?>
                                <?php if ($product['max_range']): ?>
                                <div class="product-spec"><i class="fas fa-road"></i> <?= $product['max_range'] ?></div>
                                <?php endif; ?>
                                <?php if ($product['top_speed']): ?>
                                <div class="product-spec"><i class="fas fa-tachometer-alt"></i> <?= $product['top_speed'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-price">
                                <span class="current-price"><?= formatCurrency($product['sale_price'] ?? $product['base_price']) ?></span>
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['base_price']): ?>
                                <span class="original-price"><?= formatCurrency($product['base_price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
