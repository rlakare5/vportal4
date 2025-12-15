<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn('../login.php');

$user = getUserById($_SESSION['user_id']);
$preorders = getUserPreorders($_SESSION['user_id']);
$orders = getUserOrders($_SESSION['user_id']);
$wishlist = getUserWishlist($_SESSION['user_id']);
$notifications = getUserNotifications($_SESSION['user_id'], false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>My Dashboard</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a> <span>/</span> <span>Dashboard</span>
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
                            <a href="dashboard.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <a href="preorders.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-calendar-check me-2"></i> My Preorders
                            </a>
                            <a href="orders.php" class="list-group-item list-group-item-action">
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
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center p-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 16px;">
                                <h3><?= count($preorders) ?></h3>
                                <p class="mb-0">Preorders</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center p-4" style="background: linear-gradient(135deg, var(--accent-color), #059669); color: white; border-radius: 16px;">
                                <h3><?= count($orders) ?></h3>
                                <p class="mb-0">Orders</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center p-4" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border-radius: 16px;">
                                <h3><?= count($wishlist) ?></h3>
                                <p class="mb-0">Wishlist Items</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Recent Preorders</h5>
                                <a href="preorders.php" class="btn btn-sm btn-outline">View All</a>
                            </div>
                            
                            <?php if (empty($preorders)): ?>
                            <p class="text-muted text-center py-4">No preorders yet. <a href="../bikes.php">Browse bikes</a></p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($preorders, 0, 5) as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                                            <td><?= formatCurrency($order['total_amount']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['status'] === 'delivered' ? 'success' : 
                                                    ($order['status'] === 'pending' ? 'warning' : 
                                                    ($order['status'] === 'rejected' || $order['status'] === 'cancelled' ? 'danger' : 'info')) 
                                                ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
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
                    
                    <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body p-4">
                            <h5 class="mb-4">Recent Notifications</h5>
                            <?php if (empty($notifications)): ?>
                            <p class="text-muted text-center py-4">No notifications</p>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
                                <div class="list-group-item <?= $notif['is_read'] ? '' : 'bg-light' ?>">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($notif['title']) ?></strong>
                                        <small class="text-muted"><?= getTimeAgo($notif['created_at']) ?></small>
                                    </div>
                                    <p class="mb-0 small text-muted"><?= htmlspecialchars($notif['message']) ?></p>
                                </div>
                                <?php endforeach; ?>
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
