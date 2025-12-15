<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$total_sales = mysqli_fetch_assoc(mysqli_query($con, 
    "SELECT SUM(total_amount) as total FROM invoices WHERE payment_status = 'paid' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'"
))['total'] ?? 0;

$total_orders = mysqli_fetch_assoc(mysqli_query($con, 
    "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'"
))['total'] ?? 0;

$total_preorders = mysqli_fetch_assoc(mysqli_query($con, 
    "SELECT COUNT(*) as total FROM preorders WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'"
))['total'] ?? 0;

$new_users = mysqli_fetch_assoc(mysqli_query($con, 
    "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'"
))['total'] ?? 0;

$top_products = [];
$result = mysqli_query($con, "SELECT p.name, COUNT(po.id) as orders, SUM(po.total_amount) as revenue 
                              FROM preorders po 
                              JOIN products p ON po.product_id = p.id 
                              WHERE DATE(po.created_at) BETWEEN '$start_date' AND '$end_date'
                              GROUP BY p.id 
                              ORDER BY orders DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $top_products[] = $row;
}

$monthly_sales = [];
$result = mysqli_query($con, "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total 
                              FROM invoices 
                              WHERE payment_status = 'paid' 
                              GROUP BY month 
                              ORDER BY month DESC LIMIT 12");
while ($row = mysqli_fetch_assoc($result)) {
    $monthly_sales[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - VPORTAL Admin</title>
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
                    <h1>Reports</h1>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= formatCurrency($total_sales) ?></h3>
                            <p>Total Sales</p>
                        </div>
                        <div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $total_orders ?></h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $total_preorders ?></h3>
                            <p>Total Preorders</p>
                        </div>
                        <div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $new_users ?></h3>
                            <p>New Users</p>
                        </div>
                        <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Top Products</h3>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Orders</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($top_products)): ?>
                                        <tr><td colspan="3" class="text-center">No data</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($top_products as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['name']) ?></td>
                                            <td><?= $p['orders'] ?></td>
                                            <td><?= formatCurrency($p['revenue']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Monthly Sales</h3>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($monthly_sales)): ?>
                                        <tr><td colspan="2" class="text-center">No data</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($monthly_sales as $m): ?>
                                        <tr>
                                            <td><?= date('F Y', strtotime($m['month'] . '-01')) ?></td>
                                            <td><?= formatCurrency($m['total']) ?></td>
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
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
