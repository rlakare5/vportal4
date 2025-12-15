<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_testimonial'])) {
        $customer_name = sanitize($_POST['customer_name']);
        $customer_location = sanitize($_POST['customer_location']);
        $content = sanitize($_POST['content']);
        $rating = (int)$_POST['rating'];
        $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : 'NULL';
        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload = uploadImage($_FILES['image'], 'testimonials');
            if ($upload['success']) {
                $image_path = $upload['path'];
            }
        }
        
        $sql = "INSERT INTO testimonials (customer_name, customer_location, customer_image, content, rating, product_id, status) 
                VALUES ('$customer_name', '$customer_location', '$image_path', '$content', $rating, $product_id, 1)";
        
        if (mysqli_query($con, $sql)) {
            $success = 'Testimonial added successfully!';
            logActivity($_SESSION['admin_id'], 'create', 'testimonials', 'Added testimonial from: ' . $customer_name);
        } else {
            $error = 'Failed to add testimonial.';
        }
    }
    
    if (isset($_POST['delete_testimonial'])) {
        $id = (int)$_POST['testimonial_id'];
        mysqli_query($con, "DELETE FROM testimonials WHERE id = $id");
        $success = 'Testimonial deleted!';
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['testimonial_id'];
        $current = (int)$_POST['current_status'];
        mysqli_query($con, "UPDATE testimonials SET status = " . ($current ? 0 : 1) . " WHERE id = $id");
        $success = 'Status updated!';
    }
}

$products = getProducts();
$testimonials = [];
$result = mysqli_query($con, "SELECT t.*, p.name as product_name FROM testimonials t LEFT JOIN products p ON t.product_id = p.id ORDER BY t.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $testimonials[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials - VPORTAL Admin</title>
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
                    <h1>Testimonials</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                        <i class="fas fa-plus"></i> Add Testimonial
                    </button>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <?php if (empty($testimonials)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-quote-right fa-3x text-muted mb-3"></i>
                                <p>No testimonials yet. Add your first testimonial!</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($testimonials as $t): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="../assets/uploads/<?= $t['customer_image'] ?: 'testimonials/default.png' ?>" 
                                         class="rounded-circle me-3" style="width:50px;height:50px;object-fit:cover;"
                                         onerror="this.src='https://via.placeholder.com/50'">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($t['customer_name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($t['customer_location'] ?? '') ?></small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $t['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text">"<?= htmlspecialchars($t['content']) ?>"</p>
                                <?php if ($t['product_name']): ?>
                                <small class="text-muted">For: <?= htmlspecialchars($t['product_name']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="testimonial_id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $t['status'] ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $t['status'] ? 'success' : 'secondary' ?>">
                                        <?= $t['status'] ? 'Active' : 'Inactive' ?>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                    <input type="hidden" name="testimonial_id" value="<?= $t['id'] ?>">
                                    <button type="submit" name="delete_testimonial" class="btn btn-sm btn-danger">
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
    
    <div class="modal fade" id="addTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Testimonial</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="customer_location" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Testimonial</label>
                            <textarea name="content" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <select name="rating" class="form-control">
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-control">
                                        <option value="">General</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_testimonial" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
