<?php
require_once 'includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    header('Location: bikes.php');
    exit();
}

$result = mysqli_query($con, "SELECT * FROM products WHERE slug = '$slug' AND status = 'active'");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Location: bikes.php');
    exit();
}

mysqli_query($con, "UPDATE products SET views = views + 1 WHERE id = " . $product['id']);

$images = getProductImages($product['id']);
$variants = getProductVariants($product['id']);
$accessories = getAccessories($product['id']);

$related = [];
$result = mysqli_query($con, "SELECT p.*, 
        (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as main_image
        FROM products p WHERE p.category = '{$product['category']}' AND p.id != {$product['id']} AND p.status = 'active' 
        ORDER BY RAND() LIMIT 4");
while ($row = mysqli_fetch_assoc($result)) {
    $related[] = $row;
}

$main_image = null;
$gallery_images = [];
$view_360 = [];

foreach ($images as $img) {
    if ($img['image_type'] === 'main') {
        $main_image = $img;
    } elseif ($img['image_type'] === '360') {
        $view_360[] = $img;
    } else {
        $gallery_images[] = $img;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .product-gallery { display: flex; gap: 20px; }
        .gallery-thumbnails { display: flex; flex-direction: column; gap: 10px; }
        .gallery-thumbnail { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; }
        .gallery-thumbnail.active { border-color: var(--primary-color); }
        .gallery-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-main { flex: 1; border-radius: 16px; overflow: hidden; background: #f1f5f9; }
        .gallery-main img { width: 100%; height: 500px; object-fit: contain; }
        .spec-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .spec-item { background: #f8fafc; padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; }
        .spec-icon { width: 50px; height: 50px; border-radius: 12px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
        .spec-info h4 { font-size: 14px; color: var(--text-muted); margin-bottom: 5px; }
        .spec-info p { font-size: 18px; font-weight: 600; margin: 0; }
        .variant-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .variant-option { padding: 10px 20px; border: 2px solid var(--light-border); border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .variant-option:hover, .variant-option.active { border-color: var(--primary-color); background: rgba(0, 212, 255, 0.1); }
        .variant-color { width: 24px; height: 24px; border-radius: 50%; display: inline-block; vertical-align: middle; margin-right: 8px; border: 2px solid #ddd; }
        .price-section { background: var(--dark-bg); padding: 30px; border-radius: 16px; color: white; }
        .price-section .current-price { font-size: 36px; font-weight: 800; }
        .emi-info { background: rgba(0, 212, 255, 0.1); padding: 15px; border-radius: 8px; margin-top: 15px; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span>
                <a href="bikes.php">EV Bikes</a> <span>/</span>
                <span><?= htmlspecialchars($product['name']) ?></span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="product-gallery">
                        <div class="gallery-thumbnails">
                            <?php if ($main_image): ?>
                            <div class="gallery-thumbnail active" onclick="changeImage('<?= UPLOADS_URL ?>/products/<?= $main_image['image_path'] ?>')">
                                <img src="<?= UPLOADS_URL ?>/products/<?= $main_image['image_path'] ?>" alt="" onerror="this.src='https://via.placeholder.com/80'">
                            </div>
                            <?php endif; ?>
                            <?php foreach ($gallery_images as $img): ?>
                            <div class="gallery-thumbnail" onclick="changeImage('<?= UPLOADS_URL ?>/products/<?= $img['image_path'] ?>')">
                                <img src="<?= UPLOADS_URL ?>/products/<?= $img['image_path'] ?>" alt="" onerror="this.src='https://via.placeholder.com/80'">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="gallery-main">
                            <img src="<?= $main_image ? UPLOADS_URL . '/products/' . $main_image['image_path'] : 'https://via.placeholder.com/600x500?text=' . urlencode($product['name']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" id="mainImage"
                                 onerror="this.src='https://via.placeholder.com/600x500?text=<?= urlencode($product['name']) ?>'">
                        </div>
                    </div>
                    
                    <?php if (!empty($view_360)): ?>
                    <div class="mt-4 text-center">
                        <button class="btn btn-outline" onclick="show360View()">
                            <i class="fas fa-sync-alt"></i> View 360°
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-6">
                    <span class="badge badge-featured mb-3"><?= ucfirst($product['category']) ?></span>
                    <h1 class="mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="text-muted mb-4"><?= htmlspecialchars($product['brand']) ?> <?= htmlspecialchars($product['model']) ?></p>
                    
                    <?php if ($product['short_description']): ?>
                    <p class="mb-4"><?= htmlspecialchars($product['short_description']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($variants)): ?>
                    <div class="mb-4">
                        <h5 class="mb-3">Select Variant</h5>
                        <div class="variant-options">
                            <?php foreach ($variants as $variant): ?>
                            <div class="variant-option" data-variant="<?= $variant['id'] ?>" data-price="<?= $variant['price_difference'] ?>">
                                <?php if ($variant['color_code']): ?>
                                <span class="variant-color" style="background: <?= $variant['color_code'] ?>"></span>
                                <?php endif; ?>
                                <?= htmlspecialchars($variant['variant_name']) ?>
                                <?php if ($variant['price_difference'] > 0): ?>
                                <small>(+<?= formatCurrency($variant['price_difference']) ?>)</small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="price-section mb-4">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="current-price" id="displayPrice"><?= formatCurrency($product['sale_price'] ?? $product['base_price']) ?></span>
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['base_price']): ?>
                            <span class="original-price text-muted" style="font-size: 20px;"><?= formatCurrency($product['base_price']) ?></span>
                            <span class="badge bg-danger">
                                <?= round((($product['base_price'] - $product['sale_price']) / $product['base_price']) * 100) ?>% OFF
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted mb-0">Inclusive of all taxes</p>
                        
                        <?php if ($product['emi_available']): ?>
                        <div class="emi-info">
                            <i class="fas fa-credit-card me-2"></i>
                            EMI available starting from <?= formatCurrency(($product['sale_price'] ?? $product['base_price']) / 24) ?>/month
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex gap-3 mb-4">
                        <a href="preorder.php?product=<?= $product['id'] ?>" class="btn btn-primary btn-lg flex-fill">
                            <i class="fas fa-calendar-check"></i> Pre-Order Now
                        </a>
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline btn-lg" onclick="addToWishlist(<?= $product['id'] ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-outline btn-lg" onclick="addToCompare(<?= $product['id'] ?>)">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                    
                    <div class="d-flex gap-4 text-muted">
                        <div><i class="fas fa-truck me-2"></i> Free Delivery</div>
                        <div><i class="fas fa-shield-alt me-2"></i> <?= $product['warranty'] ?? '1 Year Warranty' ?></div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-12">
                    <ul class="nav nav-tabs mb-4" id="productTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#specs">Specifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#description">Description</a>
                        </li>
                        <?php if (!empty($accessories)): ?>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#accessories">Accessories</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="specs">
                            <div class="spec-grid">
                                <?php if ($product['battery_capacity']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-battery-full"></i></div>
                                    <div class="spec-info">
                                        <h4>Battery Capacity</h4>
                                        <p><?= htmlspecialchars($product['battery_capacity']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['motor_power']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-bolt"></i></div>
                                    <div class="spec-info">
                                        <h4>Motor Power</h4>
                                        <p><?= htmlspecialchars($product['motor_power']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['max_range']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-road"></i></div>
                                    <div class="spec-info">
                                        <h4>Max Range</h4>
                                        <p><?= htmlspecialchars($product['max_range']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['top_speed']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    <div class="spec-info">
                                        <h4>Top Speed</h4>
                                        <p><?= htmlspecialchars($product['top_speed']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['charging_time']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-plug"></i></div>
                                    <div class="spec-info">
                                        <h4>Charging Time</h4>
                                        <p><?= htmlspecialchars($product['charging_time']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['weight']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-weight"></i></div>
                                    <div class="spec-info">
                                        <h4>Weight</h4>
                                        <p><?= htmlspecialchars($product['weight']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="description">
                            <div class="p-4 bg-light rounded">
                                <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($accessories)): ?>
                        <div class="tab-pane fade" id="accessories">
                            <div class="row">
                                <?php foreach ($accessories as $acc): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5><?= htmlspecialchars($acc['name']) ?></h5>
                                            <p class="text-muted small"><?= htmlspecialchars($acc['description']) ?></p>
                                            <p class="fw-bold text-primary"><?= formatCurrency($acc['price']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($related)): ?>
            <div class="mt-5">
                <h3 class="mb-4">Related Bikes</h3>
                <div class="products-grid">
                    <?php foreach ($related as $rel): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= UPLOADS_URL ?>/products/<?= $rel['main_image'] ?? 'placeholder.jpg' ?>" 
                                 alt="<?= htmlspecialchars($rel['name']) ?>"
                                 onerror="this.src='https://via.placeholder.com/400x300'">
                        </div>
                        <a href="bike-details.php?slug=<?= $rel['slug'] ?>">
                            <div class="product-info">
                                <div class="product-category"><?= ucfirst($rel['category']) ?></div>
                                <h3 class="product-name"><?= htmlspecialchars($rel['name']) ?></h3>
                                <div class="product-price">
                                    <span class="current-price"><?= formatCurrency($rel['sale_price'] ?? $rel['base_price']) ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    const basePrice = <?= $product['sale_price'] ?? $product['base_price'] ?>;
    
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.gallery-thumbnail').forEach(t => t.classList.remove('active'));
        event.target.closest('.gallery-thumbnail').classList.add('active');
    }
    
    document.querySelectorAll('.variant-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.variant-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            
            const priceDiff = parseFloat(this.dataset.price) || 0;
            const newPrice = basePrice + priceDiff;
            document.getElementById('displayPrice').textContent = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 0
            }).format(newPrice);
        });
    });
    </script>
</body>
</html>
