<?php
require_once 'includes/functions.php';

$compare_ids = isset($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];

$products_to_compare = [];
if (!empty($compare_ids)) {
    $ids_string = implode(',', $compare_ids);
    $result = mysqli_query($con, "SELECT p.*, 
            (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as main_image
            FROM products p WHERE p.id IN ($ids_string) AND p.status = 'active'");
    while ($row = mysqli_fetch_assoc($result)) {
        $products_to_compare[] = $row;
    }
}

$all_products = getProducts(20, null, null, 'active');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Bikes - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Compare EV Bikes</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span>Compare</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="mb-4">
                <form class="row g-3" id="compareForm">
                    <div class="col-md-3">
                        <select class="form-control compare-select" name="bike1">
                            <option value="">Select Bike 1</option>
                            <?php foreach ($all_products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= in_array($p['id'], $compare_ids) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control compare-select" name="bike2">
                            <option value="">Select Bike 2</option>
                            <?php foreach ($all_products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= isset($compare_ids[1]) && $compare_ids[1] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control compare-select" name="bike3">
                            <option value="">Select Bike 3</option>
                            <?php foreach ($all_products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= isset($compare_ids[2]) && $compare_ids[2] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" onclick="compareNow()">
                            <i class="fas fa-exchange-alt"></i> Compare
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($products_to_compare)): ?>
            <div class="table-responsive">
                <table class="compare-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Specification</th>
                            <?php foreach ($products_to_compare as $p): ?>
                            <th class="text-center">
                                <img src="<?= UPLOADS_URL ?>/products/<?= $p['main_image'] ?? 'placeholder.jpg' ?>" 
                                     alt="<?= htmlspecialchars($p['name']) ?>" 
                                     style="width: 150px; height: 120px; object-fit: contain; margin-bottom: 10px;"
                                     onerror="this.src='https://via.placeholder.com/150x120'">
                                <h5><?= htmlspecialchars($p['name']) ?></h5>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Price</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center">
                                <span class="h5 text-primary"><?= formatCurrency($p['sale_price'] ?? $p['base_price']) ?></span>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Category</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= ucfirst($p['category']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Battery</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['battery_capacity'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Motor Power</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['motor_power'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Max Range</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['max_range'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Top Speed</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['top_speed'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Charging Time</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['charging_time'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Weight</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['weight'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Warranty</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['warranty'] ?: '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>EMI Available</strong></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center"><?= $p['emi_available'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td></td>
                            <?php foreach ($products_to_compare as $p): ?>
                            <td class="text-center">
                                <a href="bike-details.php?slug=<?= $p['slug'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                <a href="preorder.php?product=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Pre-Order</a>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-4x text-muted mb-4"></i>
                <h3>Select bikes to compare</h3>
                <p class="text-muted">Choose up to 3 bikes from the dropdowns above to compare their specifications</p>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    function compareNow() {
        const selects = document.querySelectorAll('.compare-select');
        const ids = [];
        selects.forEach(s => {
            if (s.value) ids.push(s.value);
        });
        if (ids.length < 2) {
            alert('Please select at least 2 bikes to compare');
            return;
        }
        window.location.href = 'compare.php?ids=' + ids.join(',');
    }
    </script>
</body>
</html>
