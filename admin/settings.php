<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settings = [
        'site_name' => sanitize($_POST['site_name']),
        'site_email' => sanitize($_POST['site_email']),
        'site_phone' => sanitize($_POST['site_phone']),
        'site_address' => sanitize($_POST['site_address']),
        'gstin' => sanitize($_POST['gstin']),
        'cgst_rate' => (float)$_POST['cgst_rate'],
        'sgst_rate' => (float)$_POST['sgst_rate'],
        'booking_amount' => (float)$_POST['booking_amount'],
        'facebook_url' => sanitize($_POST['facebook_url']),
        'twitter_url' => sanitize($_POST['twitter_url']),
        'instagram_url' => sanitize($_POST['instagram_url']),
        'youtube_url' => sanitize($_POST['youtube_url']),
    ];
    
    foreach ($settings as $key => $value) {
        $check = mysqli_query($con, "SELECT id FROM global_settings WHERE setting_key = '$key'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($con, "UPDATE global_settings SET setting_value = '$value' WHERE setting_key = '$key'");
        } else {
            mysqli_query($con, "INSERT INTO global_settings (setting_key, setting_value) VALUES ('$key', '$value')");
        }
    }
    
    $success = 'Settings saved successfully!';
    logActivity($_SESSION['admin_id'], 'update', 'settings', 'Updated site settings');
}

$current_settings = getSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - VPORTAL Admin</title>
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
                    <h1>General Settings</h1>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3><i class="fas fa-store"></i> Business Information</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Site Name</label>
                                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($current_settings['site_name'] ?? 'VPORTAL EV') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($current_settings['site_email'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="site_phone" class="form-control" value="<?= htmlspecialchars($current_settings['site_phone'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea name="site_address" class="form-control" rows="3"><?= htmlspecialchars($current_settings['site_address'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">GSTIN</label>
                                        <input type="text" name="gstin" class="form-control" value="<?= htmlspecialchars($current_settings['gstin'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3><i class="fas fa-calculator"></i> Tax Settings</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label">CGST Rate (%)</label>
                                                <input type="number" name="cgst_rate" class="form-control" step="0.01" value="<?= $current_settings['cgst_rate'] ?? 9 ?>">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label">SGST Rate (%)</label>
                                                <input type="number" name="sgst_rate" class="form-control" step="0.01" value="<?= $current_settings['sgst_rate'] ?? 9 ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Default Booking Amount</label>
                                        <input type="number" name="booking_amount" class="form-control" step="0.01" value="<?= $current_settings['booking_amount'] ?? 5000 ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3><i class="fas fa-share-alt"></i> Social Media</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fab fa-facebook"></i> Facebook</label>
                                        <input type="url" name="facebook_url" class="form-control" value="<?= htmlspecialchars($current_settings['facebook_url'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fab fa-twitter"></i> Twitter</label>
                                        <input type="url" name="twitter_url" class="form-control" value="<?= htmlspecialchars($current_settings['twitter_url'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fab fa-instagram"></i> Instagram</label>
                                        <input type="url" name="instagram_url" class="form-control" value="<?= htmlspecialchars($current_settings['instagram_url'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fab fa-youtube"></i> YouTube</label>
                                        <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars($current_settings['youtube_url'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
