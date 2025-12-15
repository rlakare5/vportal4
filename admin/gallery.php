<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_gallery'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $category = sanitize($_POST['category']);
        $sort_order = (int)$_POST['sort_order'];
        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload = uploadImage($_FILES['image'], 'gallery');
            if ($upload['success']) {
                $image_path = $upload['path'];
            }
        }
        
        if ($image_path) {
            $sql = "INSERT INTO gallery (title, description, image_path, category, sort_order, status) 
                    VALUES ('$title', '$description', '$image_path', '$category', $sort_order, 1)";
            
            if (mysqli_query($con, $sql)) {
                $success = 'Image added to gallery!';
                logActivity($_SESSION['admin_id'], 'create', 'gallery', 'Added gallery image: ' . $title);
            } else {
                $error = 'Failed to add image.';
            }
        } else {
            $error = 'Please upload an image.';
        }
    }
    
    if (isset($_POST['delete_gallery'])) {
        $id = (int)$_POST['gallery_id'];
        $item = mysqli_fetch_assoc(mysqli_query($con, "SELECT image_path FROM gallery WHERE id = $id"));
        if ($item && $item['image_path']) {
            deleteImage($item['image_path']);
        }
        mysqli_query($con, "DELETE FROM gallery WHERE id = $id");
        $success = 'Image deleted!';
    }
}

$gallery = [];
$result = mysqli_query($con, "SELECT * FROM gallery ORDER BY category, sort_order");
while ($row = mysqli_fetch_assoc($result)) {
    $gallery[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - VPORTAL Admin</title>
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
                    <h1>Gallery</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
                        <i class="fas fa-plus"></i> Add Image
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
                            <?php if (empty($gallery)): ?>
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <p>No images in gallery. Add your first image!</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($gallery as $g): ?>
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="card h-100">
                                    <img src="../assets/uploads/<?= $g['image_path'] ?>" 
                                         class="card-img-top" style="height:180px;object-fit:cover;"
                                         onerror="this.src='https://via.placeholder.com/300x180?text=Image'">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-1"><?= htmlspecialchars($g['title'] ?: 'Untitled') ?></h6>
                                        <span class="badge bg-secondary"><?= ucfirst($g['category'] ?? 'general') ?></span>
                                    </div>
                                    <div class="card-footer">
                                        <form method="POST" onsubmit="return confirm('Delete this image?')">
                                            <input type="hidden" name="gallery_id" value="<?= $g['id'] ?>">
                                            <button type="submit" name="delete_gallery" class="btn btn-sm btn-danger w-100">
                                                <i class="fas fa-trash"></i> Delete
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
    
    <div class="modal fade" id="addGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Gallery Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-control">
                                        <option value="showroom">Showroom</option>
                                        <option value="events">Events</option>
                                        <option value="products">Products</option>
                                        <option value="customers">Customers</option>
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
                        <button type="submit" name="add_gallery" class="btn btn-primary">Add Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
