<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        $name = sanitize($_POST['name']);
        $slug = createSlug($name);
        $brand = sanitize($_POST['brand']);
        $model = sanitize($_POST['model']);
        $description = sanitize($_POST['description']);
        $short_description = sanitize($_POST['short_description']);
        $base_price = (float)$_POST['base_price'];
        $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $battery_capacity = sanitize($_POST['battery_capacity']);
        $motor_power = sanitize($_POST['motor_power']);
        $max_range = sanitize($_POST['max_range']);
        $top_speed = sanitize($_POST['top_speed']);
        $charging_time = sanitize($_POST['charging_time']);
        $weight = sanitize($_POST['weight']);
        $warranty = sanitize($_POST['warranty']);
        $category = sanitize($_POST['category']);
        $stock_quantity = (int)$_POST['stock_quantity'];
        $emi_available = isset($_POST['emi_available']) ? 1 : 0;
        $emi_months = sanitize($_POST['emi_months']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $status = sanitize($_POST['status']);
        
        if (isset($_POST['add_product'])) {
            $check = mysqli_query($con, "SELECT id FROM products WHERE slug = '$slug'");
            if (mysqli_num_rows($check) > 0) {
                $slug .= '-' . time();
            }
            
            $sql = "INSERT INTO products (name, slug, brand, model, description, short_description, base_price, sale_price, 
                    battery_capacity, motor_power, max_range, top_speed, charging_time, weight, warranty, category, 
                    stock_quantity, emi_available, emi_months, featured, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssddssssssssiisis", $name, $slug, $brand, $model, $description, 
                $short_description, $base_price, $sale_price, $battery_capacity, $motor_power, $max_range, 
                $top_speed, $charging_time, $weight, $warranty, $category, $stock_quantity, $emi_available, 
                $emi_months, $featured, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $product_id = mysqli_insert_id($con);
                
                if (!empty($_FILES['images']['name'][0])) {
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $upload = uploadImage($file, 'products');
                        if ($upload['success']) {
                            $image_type = $key === 0 ? 'main' : 'gallery';
                            mysqli_query($con, "INSERT INTO product_images (product_id, image_path, image_type, sort_order) 
                                               VALUES ($product_id, '{$upload['filename']}', '$image_type', $key)");
                        }
                    }
                }
                
                logActivity($_SESSION['admin_id'], 'create', 'products', "Added product: $name", $product_id, 'product');
                $message = 'Product added successfully!';
                $action = 'list';
            } else {
                $error = 'Failed to add product: ' . mysqli_error($con);
            }
        } else {
            $sql = "UPDATE products SET name=?, slug=?, brand=?, model=?, description=?, short_description=?, 
                    base_price=?, sale_price=?, battery_capacity=?, motor_power=?, max_range=?, top_speed=?, 
                    charging_time=?, weight=?, warranty=?, category=?, stock_quantity=?, emi_available=?, 
                    emi_months=?, featured=?, status=? WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssddssssssssiisisi", $name, $slug, $brand, $model, $description, 
                $short_description, $base_price, $sale_price, $battery_capacity, $motor_power, $max_range, 
                $top_speed, $charging_time, $weight, $warranty, $category, $stock_quantity, $emi_available, 
                $emi_months, $featured, $status, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                if (!empty($_FILES['images']['name'][0])) {
                    $last_order = mysqli_fetch_assoc(mysqli_query($con, "SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = $id"));
                    $order = ($last_order['max_order'] ?? 0) + 1;
                    
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $upload = uploadImage($file, 'products');
                        if ($upload['success']) {
                            mysqli_query($con, "INSERT INTO product_images (product_id, image_path, image_type, sort_order) 
                                               VALUES ($id, '{$upload['filename']}', 'gallery', " . ($order + $key) . ")");
                        }
                    }
                }
                
                logActivity($_SESSION['admin_id'], 'update', 'products', "Updated product ID: $id", $id, 'product');
                $message = 'Product updated successfully!';
                $action = 'list';
            } else {
                $error = 'Failed to update product';
            }
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $delete_id = (int)$_POST['delete_id'];
        
        $images = mysqli_query($con, "SELECT image_path FROM product_images WHERE product_id = $delete_id");
        while ($img = mysqli_fetch_assoc($images)) {
            deleteImage('products/' . $img['image_path']);
        }
        
        mysqli_query($con, "DELETE FROM products WHERE id = $delete_id");
        logActivity($_SESSION['admin_id'], 'delete', 'products', "Deleted product ID: $delete_id", $delete_id, 'product');
        $message = 'Product deleted successfully!';
    }
}

$products = [];
if ($action === 'list') {
    $search = sanitize($_GET['search'] ?? '');
    $category_filter = sanitize($_GET['category'] ?? '');
    $status_filter = sanitize($_GET['status'] ?? '');
    
    $sql = "SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as main_image FROM products p WHERE 1=1";
    
    if ($search) {
        $sql .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%' OR p.model LIKE '%$search%')";
    }
    if ($category_filter) {
        $sql .= " AND p.category = '$category_filter'";
    }
    if ($status_filter) {
        $sql .= " AND p.status = '$status_filter'";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

$product = null;
if ($action === 'edit' && $id) {
    $product = getProductById($id);
    $product_images = getProductImages($id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - VPORTAL Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="light-theme">
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="content-area">
                <?php if ($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($action === 'list'): ?>
                <div class="page-title">
                    <h1>EV Bikes</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Bike
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <form class="d-flex gap-2" method="GET">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 200px;">
                            <select name="category" class="form-control" style="width: 150px;">
                                <option value="">All Categories</option>
                                <option value="scooter" <?= ($_GET['category'] ?? '') === 'scooter' ? 'selected' : '' ?>>Scooter</option>
                                <option value="bike" <?= ($_GET['category'] ?? '') === 'bike' ? 'selected' : '' ?>>Bike</option>
                                <option value="cycle" <?= ($_GET['category'] ?? '') === 'cycle' ? 'selected' : '' ?>>Cycle</option>
                            </select>
                            <select name="status" class="form-control" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="out_of_stock" <?= ($_GET['status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                            </select>
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-motorcycle"></i>
                                            <h3>No Products Found</h3>
                                            <p>Start by adding your first EV bike</p>
                                            <a href="?action=add" class="btn btn-primary">Add Product</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($products as $prod): ?>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <img src="../assets/uploads/products/<?= $prod['main_image'] ?? 'placeholder.jpg' ?>" 
                                                 alt="<?= htmlspecialchars($prod['name']) ?>" class="product-img"
                                                 onerror="this.src='https://via.placeholder.com/50'">
                                            <div>
                                                <strong><?= htmlspecialchars($prod['name']) ?></strong>
                                                <small class="d-block text-muted"><?= $prod['brand'] ?> <?= $prod['model'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= ucfirst($prod['category']) ?></td>
                                    <td>
                                        <?php if ($prod['sale_price'] && $prod['sale_price'] < $prod['base_price']): ?>
                                        <span class="text-success"><?= formatCurrency($prod['sale_price']) ?></span>
                                        <small class="text-muted text-decoration-line-through d-block"><?= formatCurrency($prod['base_price']) ?></small>
                                        <?php else: ?>
                                        <?= formatCurrency($prod['base_price']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $prod['stock_quantity'] ?></td>
                                    <td><span class="status-badge <?= $prod['status'] ?>"><?= ucfirst(str_replace('_', ' ', $prod['status'])) ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="?action=edit&id=<?= $prod['id'] ?>" class="action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="variants.php?product_id=<?= $prod['id'] ?>" class="action-btn" title="Variants">
                                                <i class="fas fa-palette"></i>
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="delete_id" value="<?= $prod['id'] ?>">
                                                <button type="submit" name="delete_product" class="action-btn danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="page-title">
                    <h1><?= $action === 'add' ? 'Add New Bike' : 'Edit Bike' ?></h1>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3>Basic Information</h3></div>
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Product Name *</label>
                                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Brand</label>
                                            <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($product['brand'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Model</label>
                                            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($product['model'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select name="category" class="form-control">
                                                <option value="scooter" <?= ($product['category'] ?? '') === 'scooter' ? 'selected' : '' ?>>Scooter</option>
                                                <option value="bike" <?= ($product['category'] ?? '') === 'bike' ? 'selected' : '' ?>>Bike</option>
                                                <option value="cycle" <?= ($product['category'] ?? '') === 'cycle' ? 'selected' : '' ?>>Cycle</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Short Description</label>
                                        <input type="text" name="short_description" class="form-control" maxlength="500" value="<?= htmlspecialchars($product['short_description'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Full Description</label>
                                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header"><h3>Specifications</h3></div>
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Battery Capacity</label>
                                            <input type="text" name="battery_capacity" class="form-control" placeholder="e.g., 3.5 kWh" value="<?= htmlspecialchars($product['battery_capacity'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Motor Power</label>
                                            <input type="text" name="motor_power" class="form-control" placeholder="e.g., 3000W" value="<?= htmlspecialchars($product['motor_power'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Max Range</label>
                                            <input type="text" name="max_range" class="form-control" placeholder="e.g., 100 km" value="<?= htmlspecialchars($product['max_range'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Top Speed</label>
                                            <input type="text" name="top_speed" class="form-control" placeholder="e.g., 80 kmph" value="<?= htmlspecialchars($product['top_speed'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Charging Time</label>
                                            <input type="text" name="charging_time" class="form-control" placeholder="e.g., 4-5 hours" value="<?= htmlspecialchars($product['charging_time'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Weight</label>
                                            <input type="text" name="weight" class="form-control" placeholder="e.g., 75 kg" value="<?= htmlspecialchars($product['weight'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Warranty</label>
                                        <input type="text" name="warranty" class="form-control" placeholder="e.g., 3 Years / 50000 km" value="<?= htmlspecialchars($product['warranty'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header"><h3>Images</h3></div>
                                <div class="card-body">
                                    <div class="file-upload" onclick="document.getElementById('images').click()">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload images<br><small>First image will be the main image</small></p>
                                        <input type="file" name="images[]" id="images" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
                                    </div>
                                    <div class="image-preview" id="imagePreview">
                                        <?php if (!empty($product_images)): ?>
                                        <?php foreach ($product_images as $img): ?>
                                        <div class="image-preview-item">
                                            <img src="../assets/uploads/products/<?= $img['image_path'] ?>" alt="">
                                            <button type="button" class="remove-btn" onclick="deleteImage(<?= $img['id'] ?>)"><i class="fas fa-times"></i></button>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header"><h3>Pricing</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Base Price (₹) *</label>
                                        <input type="number" name="base_price" class="form-control" required step="0.01" value="<?= $product['base_price'] ?? '' ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Sale Price (₹)</label>
                                        <input type="number" name="sale_price" class="form-control" step="0.01" value="<?= $product['sale_price'] ?? '' ?>">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="emi_available" id="emi_available" <?= ($product['emi_available'] ?? 0) ? 'checked' : '' ?>>
                                        <label for="emi_available">EMI Available</label>
                                    </div>
                                    <div class="form-group">
                                        <label>EMI Tenure Options</label>
                                        <input type="text" name="emi_months" class="form-control" placeholder="e.g., 6,12,18,24" value="<?= htmlspecialchars($product['emi_months'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header"><h3>Inventory</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Stock Quantity</label>
                                        <input type="number" name="stock_quantity" class="form-control" min="0" value="<?= $product['stock_quantity'] ?? 0 ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="out_of_stock" <?= ($product['status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                        </select>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="featured" id="featured" <?= ($product['featured'] ?? 0) ? 'checked' : '' ?>>
                                        <label for="featured">Featured Product</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <?php if ($action === 'add'): ?>
                                <button type="submit" name="add_product" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Add Product
                                </button>
                                <?php else: ?>
                                <button type="submit" name="update_product" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Update Product
                                </button>
                                <?php endif; ?>
                                <a href="products.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `<img src="${e.target.result}" alt=""><button type="button" class="remove-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }
    </script>
</body>
</html>
