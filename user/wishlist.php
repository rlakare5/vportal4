<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn('../login.php');

$user = getUserById($_SESSION['user_id']);
$wishlist = getUserWishlist($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_wishlist'])) {
    $product_id = (int)$_POST['product_id'];
    $result = removeFromWishlist($_SESSION['user_id'], $product_id);
    header('Location: wishlist.php?msg=' . ($result['success'] ? 'removed' : 'error'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>My Wishlist</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a> <span>/</span> <a href="dashboard.php">Dashboard</a> <span>/</span> <span>Wishlist</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-<?= $_GET['msg'] === 'removed' ? 'success' : 'error' ?> mb-4">
                    <?= $_GET['msg'] === 'removed' ? 'Item removed from wishlist!' : 'An error occurred.' ?>
                </div>
            <?php endif; ?>
            
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
                            <a href="orders.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-bag me-2"></i> My Orders
                            </a>
                            <a href="wishlist.php" class="list-group-item list-group-item-action active">
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
                            <h5 class="mb-4"><i class="fas fa-heart text-danger me-2"></i>My Wishlist (<?= count($wishlist) ?> items)</h5>
                            
                            <?php if (empty($wishlist)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-heart-broken fa-4x text-muted mb-3"></i>
                                <h5>Your wishlist is empty</h5>
                                <p class="text-muted">Start adding items to your wishlist by browsing our bikes!</p>
                                <a href="../bikes.php" class="btn btn-primary">Browse Bikes</a>
                            </div>
                            <?php else: ?>
                            <div class="row">
                                <?php foreach ($wishlist as $item): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="product-card">
                                        <div class="product-image">
                                            <img src="../assets/uploads/<?= $item['main_image'] ?? 'products/default.png' ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                        </div>
                                        <div class="product-info">
                                            <div class="product-category"><?= ucfirst($item['category'] ?? 'EV Bike') ?></div>
                                            <h3 class="product-name"><?= htmlspecialchars($item['name']) ?></h3>
                                            <div class="product-price mb-3">
                                                <span class="current-price"><?= formatCurrency($item['sale_price'] ?? $item['base_price']) ?></span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="../bike-details.php?id=<?= $item['product_id'] ?>" class="btn btn-primary btn-sm flex-grow-1">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                    <button type="submit" name="remove_wishlist" class="btn btn-outline btn-sm" 
                                                            onclick="return confirm('Remove from wishlist?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
