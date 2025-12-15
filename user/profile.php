<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn('../login.php');

$user = getUserById($_SESSION['user_id']);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $con;
    
    if (isset($_POST['update_profile'])) {
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $city = sanitize($_POST['city']);
        $state = sanitize($_POST['state']);
        $pincode = sanitize($_POST['pincode']);
        
        $sql = "UPDATE users SET 
                first_name = '$first_name', 
                last_name = '$last_name', 
                phone = '$phone', 
                address = '$address', 
                city = '$city', 
                state = '$state', 
                pincode = '$pincode',
                updated_at = NOW()
                WHERE id = " . $_SESSION['user_id'];
        
        if (mysqli_query($con, $sql)) {
            $success = 'Profile updated successfully!';
            $user = getUserById($_SESSION['user_id']);
        } else {
            $error = 'Failed to update profile.';
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = '$hashed', updated_at = NOW() WHERE id = " . $_SESSION['user_id'];
            if (mysqli_query($con, $sql)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Edit Profile</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a> <span>/</span> <a href="dashboard.php">Dashboard</a> <span>/</span> <span>Profile</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success mb-4"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error mb-4"><?= $error ?></div>
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
                            <a href="wishlist.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-heart me-2"></i> Wishlist
                            </a>
                            <a href="profile.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                            <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <div class="card mb-4" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body p-4">
                            <h5 class="mb-4"><i class="fas fa-user me-2"></i>Personal Information</h5>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" name="first_name" class="form-control" 
                                                   value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" name="last_name" class="form-control" 
                                                   value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" value="<?= $user['email'] ?>" disabled>
                                            <small class="text-muted">Email cannot be changed</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="tel" name="phone" class="form-control" 
                                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>City</label>
                                            <input type="text" name="city" class="form-control" 
                                                   value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>State</label>
                                            <input type="text" name="state" class="form-control" 
                                                   value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Pincode</label>
                                            <input type="text" name="pincode" class="form-control" 
                                                   value="<?= htmlspecialchars($user['pincode'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body p-4">
                            <h5 class="mb-4"><i class="fas fa-lock me-2"></i>Change Password</h5>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Current Password</label>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Confirm Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-secondary">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </form>
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
