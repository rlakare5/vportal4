<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $preorder_id = (int)$_POST['preorder_id'];
        $new_status = sanitize($_POST['new_status']);
        $expected_delivery = sanitize($_POST['expected_delivery'] ?? '');
        $rejection_reason = sanitize($_POST['rejection_reason'] ?? '');
        
        $sql = "UPDATE preorders SET status = ?";
        $params = [$new_status];
        $types = "s";
        
        if ($expected_delivery) {
            $sql .= ", expected_delivery = ?";
            $params[] = $expected_delivery;
            $types .= "s";
        }
        
        if ($rejection_reason && $new_status === 'rejected') {
            $sql .= ", rejection_reason = ?";
            $params[] = $rejection_reason;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $preorder_id;
        $types .= "i";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            $preorder = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM preorders WHERE id = $preorder_id"));
            sendNotification($preorder['user_id'], 'Preorder Status Updated', 
                "Your preorder #{$preorder['order_number']} status has been updated to: " . ucfirst($new_status), 
                'system', 'preorder');
            
            logActivity($_SESSION['admin_id'], 'update', 'preorders', "Updated preorder status to $new_status", $preorder_id, 'preorder');
            $message = 'Preorder status updated successfully!';
        } else {
            $error = 'Failed to update status';
        }
    }
}

$status_filter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT po.*, p.name as product_name, p.slug as product_slug, u.first_name, u.last_name, u.email as user_email,
        (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as product_image
        FROM preorders po 
        JOIN products p ON po.product_id = p.id 
        JOIN users u ON po.user_id = u.id 
        WHERE 1=1";

if ($status_filter) {
    $sql .= " AND po.status = '$status_filter'";
}
if ($search) {
    $sql .= " AND (po.order_number LIKE '%$search%' OR po.customer_name LIKE '%$search%' OR po.customer_phone LIKE '%$search%')";
}

$sql .= " ORDER BY po.created_at DESC";
$result = mysqli_query($con, $sql);
$preorders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $preorders[] = $row;
}

$status_counts = [];
$counts_result = mysqli_query($con, "SELECT status, COUNT(*) as count FROM preorders GROUP BY status");
while ($row = mysqli_fetch_assoc($counts_result)) {
    $status_counts[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preorders - VPORTAL Admin</title>
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
                
                <div class="page-title">
                    <h1>Preorders</h1>
                </div>
                
                <div class="stats-grid mb-4">
                    <a href="?status=pending" class="stat-card text-decoration-none">
                        <div class="stat-info">
                            <h3><?= $status_counts['pending'] ?? 0 ?></h3>
                            <p>Pending</p>
                        </div>
                        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                    </a>
                    <a href="?status=approved" class="stat-card text-decoration-none">
                        <div class="stat-info">
                            <h3><?= $status_counts['approved'] ?? 0 ?></h3>
                            <p>Approved</p>
                        </div>
                        <div class="stat-icon green"><i class="fas fa-check"></i></div>
                    </a>
                    <a href="?status=in_progress" class="stat-card text-decoration-none">
                        <div class="stat-info">
                            <h3><?= $status_counts['in_progress'] ?? 0 ?></h3>
                            <p>In Progress</p>
                        </div>
                        <div class="stat-icon blue"><i class="fas fa-spinner"></i></div>
                    </a>
                    <a href="?status=delivered" class="stat-card text-decoration-none">
                        <div class="stat-info">
                            <h3><?= $status_counts['delivered'] ?? 0 ?></h3>
                            <p>Delivered</p>
                        </div>
                        <div class="stat-icon purple"><i class="fas fa-check-double"></i></div>
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <form class="d-flex gap-2" method="GET">
                            <input type="text" name="search" class="form-control" placeholder="Search order #, name, phone..." value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
                            <select name="status" class="form-control" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="ready" <?= $status_filter === 'ready' ? 'selected' : '' ?>>Ready</option>
                                <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                            <a href="preorders.php" class="btn btn-outline">Clear</a>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($preorders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-check"></i>
                                            <h3>No Preorders Found</h3>
                                            <p>Preorders will appear here when customers place them</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($preorders as $order): ?>
                                <tr>
                                    <td><strong><?= $order['order_number'] ?></strong></td>
                                    <td>
                                        <div class="product-cell">
                                            <img src="../assets/uploads/products/<?= $order['product_image'] ?? 'placeholder.jpg' ?>" 
                                                 alt="" class="product-img" onerror="this.src='https://via.placeholder.com/50'">
                                            <span><?= htmlspecialchars($order['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                        <small class="d-block text-muted"><?= $order['customer_phone'] ?></small>
                                    </td>
                                    <td><?= formatCurrency($order['total_amount']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td><span class="status-badge <?= $order['status'] ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="action-btn" onclick="openStatusModal(<?= htmlspecialchars(json_encode($order)) ?>)" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="preorder-view.php?id=<?= $order['id'] ?>" class="action-btn" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
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
    
    <div class="modal-overlay" id="statusModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Update Preorder Status</h3>
                <button class="modal-close" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="preorder_id" id="modal_preorder_id">
                    <p><strong>Order:</strong> <span id="modal_order_number"></span></p>
                    <p><strong>Customer:</strong> <span id="modal_customer_name"></span></p>
                    
                    <div class="form-group">
                        <label>New Status</label>
                        <select name="new_status" id="modal_status" class="form-control" onchange="toggleFields()">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="in_progress">In Progress</option>
                            <option value="ready">Ready for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="delivery_date_group">
                        <label>Expected Delivery Date</label>
                        <input type="date" name="expected_delivery" class="form-control" id="modal_delivery_date">
                    </div>
                    
                    <div class="form-group" id="rejection_reason_group" style="display:none;">
                        <label>Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    function openStatusModal(order) {
        document.getElementById('modal_preorder_id').value = order.id;
        document.getElementById('modal_order_number').textContent = order.order_number;
        document.getElementById('modal_customer_name').textContent = order.customer_name;
        document.getElementById('modal_status').value = order.status;
        document.getElementById('modal_delivery_date').value = order.expected_delivery || '';
        document.getElementById('statusModal').classList.add('active');
        toggleFields();
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    
    function toggleFields() {
        const status = document.getElementById('modal_status').value;
        document.getElementById('rejection_reason_group').style.display = status === 'rejected' ? 'block' : 'none';
        document.getElementById('delivery_date_group').style.display = ['approved', 'in_progress', 'ready'].includes(status) ? 'block' : 'none';
    }
    </script>
</body>
</html>
