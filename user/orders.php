<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn('../login.php');

$user = getUserById($_SESSION['user_id']);
$orders = getUserOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>My Orders</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a> <span>/</span> <a href="dashboard.php">Dashboard</a> <span>/</span> <span>Orders</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body text-center p-4">
                            <img src="../assets/uploads/avatars/<?= $user['avatar'] ?? 'default.png' ?>" 
                                 alt="Avatar" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/80'">
                            <h5><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                            <p class="text-muted small"><?= $user['email'] ?></p>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="dashboard.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <a href="preorders.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-calendar-check me-2"></i> My Preorders
                            </a>
                            <a href="orders.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-shopping-bag me-2"></i> My Orders
                            </a>
                            <a href="wishlist.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-heart me-2"></i> Wishlist
                            </a>
                            <a href="profile.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                            <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body p-4">
                            <h5 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>My Orders (<?= count($orders) ?>)</h5>
                            
                            <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                <h5>No Orders Yet</h5>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="../bikes.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead style="background: var(--light-bg);">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Payment</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= htmlspecialchars($order['items'] ?? 'N/A') ?></td>
                                            <td><?= formatCurrency($order['total_amount']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($order['payment_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'info',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $color = $status_colors[$order['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
