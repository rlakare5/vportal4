<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_offer'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $discount_type = sanitize($_POST['discount_type']);
        $discount_value = (float)$_POST['discount_value'];
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : 'NULL';
        
        $sql = "INSERT INTO offers (title, description, discount_type, discount_value, product_id, start_date, end_date, status) 
                VALUES ('$title', '$description', '$discount_type', $discount_value, $product_id, '$start_date', '$end_date', 1)";
        
        if (mysqli_query($con, $sql)) {
            $success = 'Offer added successfully!';
            logActivity($_SESSION['admin_id'], 'create', 'offers', 'Added offer: ' . $title);
        } else {
            $error = 'Failed to add offer.';
        }
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['offer_id'];
        $current = (int)$_POST['current_status'];
        $new_status = $current ? 0 : 1;
        mysqli_query($con, "UPDATE offers SET status = $new_status WHERE id = $id");
        $success = 'Offer status updated!';
    }
    
    if (isset($_POST['delete_offer'])) {
        $id = (int)$_POST['offer_id'];
        mysqli_query($con, "DELETE FROM offers WHERE id = $id");
        $success = 'Offer deleted!';
    }
}

$products = getProducts();
$offers = [];
$result = mysqli_query($con, "SELECT o.*, p.name as product_name FROM offers o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $offers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offers - VPORTAL Admin</title>
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
                    <h1>Offers & Promotions</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfferModal">
                        <i class="fas fa-plus"></i> Add Offer
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
                                    <th>Title</th>
                                    <th>Discount</th>
                                    <th>Product</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($offers)): ?>
                                <tr><td colspan="6" class="text-center py-4">No offers found</td></tr>
                                <?php else: ?>
                                <?php foreach ($offers as $o): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($o['title']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars(substr($o['description'] ?? '', 0, 50)) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($o['discount_type'] === 'percentage'): ?>
                                            <?= $o['discount_value'] ?>% OFF
                                        <?php else: ?>
                                            <?= formatCurrency($o['discount_value']) ?> OFF
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $o['product_name'] ? htmlspecialchars($o['product_name']) : 'All Products' ?></td>
                                    <td>
                                        <?= date('M d', strtotime($o['start_date'])) ?> - <?= date('M d, Y', strtotime($o['end_date'])) ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="offer_id" value="<?= $o['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $o['status'] ?>">
                                            <button type="submit" name="toggle_status" class="status-badge <?= $o['status'] ? 'active' : 'inactive' ?>" style="border:none;cursor:pointer;">
                                                <?= $o['status'] ? 'Active' : 'Inactive' ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this offer?')">
                                            <input type="hidden" name="offer_id" value="<?= $o['id'] ?>">
                                            <button type="submit" name="delete_offer" class="action-btn text-danger"><i class="fas fa-trash"></i></button>
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
    
    <div class="modal fade" id="addOfferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Offer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Discount Type</label>
                                    <select name="discount_type" class="form-control" required>
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Discount Value</label>
                                    <input type="number" name="discount_value" class="form-control" step="0.01" required>
                                </div>
                            </div>
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
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_offer" class="btn btn-primary">Add Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
