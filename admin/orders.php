<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    
    if (mysqli_query($con, "UPDATE orders SET status = '$status', updated_at = NOW() WHERE id = $order_id")) {
        $success = 'Order status updated!';
        logActivity($_SESSION['admin_id'], 'update', 'orders', 'Updated order status to: ' . $status, $order_id, 'order');
    } else {
        $error = 'Failed to update status.';
    }
}

$orders = [];
$result = mysqli_query($con, "SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              ORDER BY o.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - VPORTAL Admin</title>
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
                    <h1>Orders</h1>
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
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                <tr><td colspan="8" class="text-center py-4">No orders found</td></tr>
                                <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><strong><?= $o['order_number'] ?></strong></td>
                                    <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                                    <td>
                                        <small><?= $o['email'] ?></small><br>
                                        <small><?= $o['phone'] ?></small>
                                    </td>
                                    <td><?= formatCurrency($o['total_amount']) ?></td>
                                    <td><span class="status-badge <?= $o['payment_status'] ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto;">
                                                <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $o['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="processing" <?= $o['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="shipped" <?= $o['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $o['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $o['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                    <td>
                                        <a href="invoice-view.php?order_id=<?= $o['id'] ?>" class="action-btn" title="View Invoice">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
