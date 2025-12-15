<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_page'])) {
        $title = sanitize($_POST['title']);
        $slug = createSlug($title);
        $content = mysqli_real_escape_string($con, $_POST['content']);
        $meta_title = sanitize($_POST['meta_title']);
        $meta_description = sanitize($_POST['meta_description']);
        
        $check = mysqli_query($con, "SELECT id FROM pages WHERE slug = '$slug'");
        if (mysqli_num_rows($check) > 0) {
            $slug .= '-' . time();
        }
        
        $sql = "INSERT INTO pages (title, slug, content, meta_title, meta_description, status) 
                VALUES ('$title', '$slug', '$content', '$meta_title', '$meta_description', 1)";
        
        if (mysqli_query($con, $sql)) {
            $success = 'Page created successfully!';
            logActivity($_SESSION['admin_id'], 'create', 'pages', 'Added page: ' . $title);
        } else {
            $error = 'Failed to create page.';
        }
    }
    
    if (isset($_POST['delete_page'])) {
        $id = (int)$_POST['page_id'];
        mysqli_query($con, "DELETE FROM pages WHERE id = $id");
        $success = 'Page deleted!';
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['page_id'];
        $current = (int)$_POST['current_status'];
        mysqli_query($con, "UPDATE pages SET status = " . ($current ? 0 : 1) . " WHERE id = $id");
        $success = 'Page status updated!';
    }
}

$pages = [];
$result = mysqli_query($con, "SELECT * FROM pages ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $pages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages - VPORTAL Admin</title>
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
                    <h1>Pages</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPageModal">
                        <i class="fas fa-plus"></i> Add Page
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
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pages)): ?>
                                <tr><td colspan="5" class="text-center py-4">No pages found</td></tr>
                                <?php else: ?>
                                <?php foreach ($pages as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                                    <td><code>/page.php?slug=<?= $p['slug'] ?></code></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="page_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $p['status'] ?>">
                                            <button type="submit" name="toggle_status" class="status-badge <?= $p['status'] ? 'active' : 'inactive' ?>" style="border:none;cursor:pointer;">
                                                <?= $p['status'] ? 'Published' : 'Draft' ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                                    <td>
                                        <a href="../page.php?slug=<?= $p['slug'] ?>" target="_blank" class="action-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this page?')">
                                            <input type="hidden" name="page_id" value="<?= $p['id'] ?>">
                                            <button type="submit" name="delete_page" class="action-btn text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
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
    
    <div class="modal fade" id="addPageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Page</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="10" required></textarea>
                            <small class="text-muted">HTML is allowed</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta Title (SEO)</label>
                            <input type="text" name="meta_title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta Description (SEO)</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_page" class="btn btn-primary">Create Page</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
