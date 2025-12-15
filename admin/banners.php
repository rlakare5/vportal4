<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_banner'])) {
        $title = sanitize($_POST['title']);
        $subtitle = sanitize($_POST['subtitle']);
        $link = sanitize($_POST['link']);
        $position = sanitize($_POST['position']);
        $sort_order = (int)$_POST['sort_order'];
        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload = uploadImage($_FILES['image'], 'banners');
            if ($upload['success']) {
                $image_path = $upload['path'];
            }
        }
        
        $sql = "INSERT INTO banners (title, subtitle, image_path, link, position, sort_order, status) 
                VALUES ('$title', '$subtitle', '$image_path', '$link', '$position', $sort_order, 1)";
        
        if (mysqli_query($con, $sql)) {
            $success = 'Banner added successfully!';
            logActivity($_SESSION['admin_id'], 'create', 'banners', 'Added banner: ' . $title);
        } else {
            $error = 'Failed to add banner.';
        }
    }
    
    if (isset($_POST['delete_banner'])) {
        $id = (int)$_POST['banner_id'];
        $banner = mysqli_fetch_assoc(mysqli_query($con, "SELECT image_path FROM banners WHERE id = $id"));
        if ($banner && $banner['image_path']) {
            deleteImage($banner['image_path']);
        }
        mysqli_query($con, "DELETE FROM banners WHERE id = $id");
        $success = 'Banner deleted!';
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['banner_id'];
        $current = (int)$_POST['current_status'];
        mysqli_query($con, "UPDATE banners SET status = " . ($current ? 0 : 1) . " WHERE id = $id");
        $success = 'Banner status updated!';
    }
}

$banners = [];
$result = mysqli_query($con, "SELECT * FROM banners ORDER BY position, sort_order");
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - VPORTAL Admin</title>
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
                    <h1>Banners</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                        <i class="fas fa-plus"></i> Add Banner
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
                        <div class="row">
                            <?php if (empty($banners)): ?>
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <p>No banners found. Add your first banner!</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($banners as $b): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <img src="../assets/uploads/<?= $b['image_path'] ?: 'banners/default.jpg' ?>" 
                                         class="card-img-top" style="height:150px;object-fit:cover;"
                                         onerror="this.src='https://via.placeholder.com/400x150?text=Banner'">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($b['title']) ?></h5>
                                        <p class="card-text small text-muted"><?= htmlspecialchars($b['subtitle'] ?? '') ?></p>
                                        <p class="card-text">
                                            <small>Position: <?= ucfirst($b['position']) ?></small><br>
                                            <small>Order: <?= $b['sort_order'] ?></small>
                                        </p>
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="banner_id" value="<?= $b['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $b['status'] ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $b['status'] ? 'success' : 'secondary' ?>">
                                                <?= $b['status'] ? 'Active' : 'Inactive' ?>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this banner?')">
                                            <input type="hidden" name="banner_id" value="<?= $b['id'] ?>">
                                            <button type="submit" name="delete_banner" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="subtitle" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link URL</label>
                            <input type="url" name="link" class="form-control" placeholder="https://">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <select name="position" class="form-control">
                                        <option value="hero">Hero Section</option>
                                        <option value="sidebar">Sidebar</option>
                                        <option value="footer">Footer</option>
                                        <option value="popup">Popup</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_banner" class="btn btn-primary">Add Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
