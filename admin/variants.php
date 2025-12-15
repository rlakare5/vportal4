<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_variant'])) {
        $product_id = (int)$_POST['product_id'];
        $name = sanitize($_POST['name']);
        $color_name = sanitize($_POST['color_name']);
        $color_code = sanitize($_POST['color_code']);
        $price_difference = (float)$_POST['price_difference'];
        $stock = (int)$_POST['stock'];
        
        $sql = "INSERT INTO variants (product_id, variant_name, color_name, color_code, price_difference, stock_quantity, status) 
                VALUES ($product_id, '$name', '$color_name', '$color_code', $price_difference, $stock, 1)";
        
        if (mysqli_query($con, $sql)) {
            $success = 'Variant added successfully!';
            logActivity($_SESSION['admin_id'], 'create', 'variants', 'Added variant: ' . $name);
        } else {
            $error = 'Failed to add variant.';
        }
    }
    
    if (isset($_POST['delete_variant'])) {
        $id = (int)$_POST['variant_id'];
        if (mysqli_query($con, "DELETE FROM variants WHERE id = $id")) {
            $success = 'Variant deleted successfully!';
        } else {
            $error = 'Failed to delete variant.';
        }
    }
}

$products = getProducts();
$variants = [];
$result = mysqli_query($con, "SELECT v.*, p.name as product_name FROM variants v JOIN products p ON v.product_id = p.id ORDER BY p.name, v.variant_name");
while ($row = mysqli_fetch_assoc($result)) {
    $variants[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Variants - VPORTAL Admin</title>
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
                <div class="page-title">
                    <h1>Product Variants</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVariantModal">
                        <i class="fas fa-plus"></i> Add Variant
                    </button>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Variant Name</th>
                                    <th>Color</th>
                                    <th>Price Diff</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($variants)): ?>
                                <tr><td colspan="7" class="text-center py-4">No variants found</td></tr>
                                <?php else: ?>
                                <?php foreach ($variants as $v): ?>
                                <tr>
                                    <td><?= htmlspecialchars($v['product_name']) ?></td>
                                    <td><?= htmlspecialchars($v['variant_name']) ?></td>
                                    <td>
                                        <span style="display:inline-block;width:20px;height:20px;background:<?= $v['color_code'] ?>;border-radius:4px;vertical-align:middle;"></span>
                                        <?= htmlspecialchars($v['color_name']) ?>
                                    </td>
                                    <td><?= $v['price_difference'] >= 0 ? '+' : '' ?><?= formatCurrency($v['price_difference']) ?></td>
                                    <td><?= $v['stock_quantity'] ?></td>
                                    <td><span class="status-badge <?= $v['status'] ? 'active' : 'inactive' ?>"><?= $v['status'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this variant?')">
                                            <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                                            <button type="submit" name="delete_variant" class="action-btn text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addVariantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Variant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-control" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variant Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Pro, Standard">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Color Name</label>
                                    <input type="text" name="color_name" class="form-control" placeholder="e.g., Midnight Black">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Color Code</label>
                                    <input type="color" name="color_code" class="form-control" value="#000000">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Price Difference</label>
                                    <input type="number" name="price_difference" class="form-control" value="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_variant" class="btn btn-primary">Add Variant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
