<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$stats = getDashboardStats();

$recent_preorders = [];
$result = mysqli_query($con, "SELECT po.*, p.name as product_name, u.first_name, u.last_name 
                              FROM preorders po 
                              JOIN products p ON po.product_id = p.id 
                              JOIN users u ON po.user_id = u.id 
                              ORDER BY po.created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_preorders[] = $row;
}

$recent_orders = [];
$result = mysqli_query($con, "SELECT o.*, u.first_name, u.last_name 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              ORDER BY o.created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VPORTAL</title>
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
                    <h1>Dashboard</h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-secondary">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Bike
                        </button>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $stats['total_bikes'] ?></h3>
                            <p>Total Bikes</p>
                            <span class="change positive"><i class="fas fa-arrow-up"></i> Active models</span>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $stats['total_stock'] ?></h3>
                            <p>Total Stock</p>
                            <span class="change positive"><i class="fas fa-arrow-up"></i> Units available</span>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $stats['pending_preorders'] ?></h3>
                            <p>Pending Preorders</p>
                            <span class="change <?= $stats['pending_preorders'] > 0 ? 'negative' : 'positive' ?>">
                                <i class="fas fa-clock"></i> Requires attention
                            </span>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= formatCurrency($stats['total_revenue']) ?></h3>
                            <p>Total Revenue</p>
                            <span class="change positive"><i class="fas fa-arrow-up"></i> From paid invoices</span>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Preorders</h3>
                                <a href="preorders.php" class="btn btn-sm btn-outline">View All</a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recent_preorders)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No preorders yet</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($recent_preorders as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                                            <td><?= formatCurrency($order['total_amount']) ?></td>
                                            <td><span class="status-badge <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                            <td>
                                                <div class="action-btns">
                                                    <a href="preorder-view.php?id=<?= $order['id'] ?>" class="action-btn" title="View">
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
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Stats</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span>Total Users</span>
                                    <strong><?= $stats['total_users'] ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span>Total Orders</span>
                                    <strong><?= $stats['total_orders'] ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span>Active Offers</span>
                                    <strong><?= $stats['active_offers'] ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Open Complaints</span>
                                    <strong class="text-danger"><?= $stats['open_complaints'] ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="products.php?action=add" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add New Bike
                                    </a>
                                    <a href="offers.php?action=add" class="btn btn-secondary">
                                        <i class="fas fa-tag"></i> Create Offer
                                    </a>
                                    <a href="billing.php" class="btn btn-secondary">
                                        <i class="fas fa-file-invoice"></i> Create Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
