<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_accessory'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : 'NULL';
        
        $sql = "INSERT INTO accessories (name, description, price, product_id, status) 
                VALUES ('$name', '$description', $price, $product_id, 1)";
        
        if (mysqli_query($con, $sql)) {
            $success = 'Accessory added successfully!';
            logActivity($_SESSION['admin_id'], 'create', 'accessories', 'Added accessory: ' . $name);
        } else {
            $error = 'Failed to add accessory.';
        }
    }
    
    if (isset($_POST['delete_accessory'])) {
        $id = (int)$_POST['accessory_id'];
        if (mysqli_query($con, "DELETE FROM accessories WHERE id = $id")) {
            $success = 'Accessory deleted successfully!';
        } else {
            $error = 'Failed to delete accessory.';
        }
    }
}

$products = getProducts();
$accessories = [];
$result = mysqli_query($con, "SELECT a.*, p.name as product_name FROM accessories a LEFT JOIN products p ON a.product_id = p.id ORDER BY a.name");
while ($row = mysqli_fetch_assoc($result)) {
    $accessories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories - VPORTAL Admin</title>
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
                    <h1>Accessories</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccessoryModal">
                        <i class="fas fa-plus"></i> Add Accessory
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
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>For Product</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accessories)): ?>
                                <tr><td colspan="6" class="text-center py-4">No accessories found</td></tr>
                                <?php else: ?>
                                <?php foreach ($accessories as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['name']) ?></td>
                                    <td><?= htmlspecialchars(substr($a['description'] ?? '', 0, 50)) ?>...</td>
                                    <td><?= formatCurrency($a['price']) ?></td>
                                    <td><?= $a['product_name'] ? htmlspecialchars($a['product_name']) : 'All Products' ?></td>
                                    <td><span class="status-badge <?= $a['status'] ? 'active' : 'inactive' ?>"><?= $a['status'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this accessory?')">
                                            <input type="hidden" name="accessory_id" value="<?= $a['id'] ?>">
                                            <button type="submit" name="delete_accessory" class="action-btn text-danger"><i class="fas fa-trash"></i></button>
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
    
    <div class="modal fade" id="addAccessoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Accessory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">For Product (Optional)</label>
                            <select name="product_id" class="form-control">
                                <option value="">All Products</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_accessory" class="btn btn-primary">Add Accessory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
