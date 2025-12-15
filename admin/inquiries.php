<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['inquiry_id'];
        $status = sanitize($_POST['status']);
        $admin_notes = sanitize($_POST['admin_notes']);
        
        if (mysqli_query($con, "UPDATE contact_inquiries SET status = '$status', admin_notes = '$admin_notes', updated_at = NOW() WHERE id = $id")) {
            $success = 'Inquiry updated!';
        } else {
            $error = 'Failed to update.';
        }
    }
    
    if (isset($_POST['delete_inquiry'])) {
        $id = (int)$_POST['inquiry_id'];
        mysqli_query($con, "DELETE FROM contact_inquiries WHERE id = $id");
        $success = 'Inquiry deleted!';
    }
}

$inquiries = [];
$result = mysqli_query($con, "SELECT * FROM contact_inquiries ORDER BY status = 'new' DESC, created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $inquiries[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries - VPORTAL Admin</title>
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
                    <h1>Contact Inquiries</h1>
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
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inquiries)): ?>
                                <tr><td colspan="6" class="text-center py-4">No inquiries found</td></tr>
                                <?php else: ?>
                                <?php foreach ($inquiries as $inq): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($inq['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($inq['name']) ?></td>
                                    <td>
                                        <small><?= $inq['email'] ?></small><br>
                                        <small><?= $inq['phone'] ?? '' ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($inq['subject'] ?? 'General Inquiry') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $inq['status'] === 'new' ? 'danger' : ($inq['status'] === 'read' ? 'warning' : 'success') ?>">
                                            <?= ucfirst($inq['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn" data-bs-toggle="modal" data-bs-target="#viewModal<?= $inq['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                            <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                                            <button type="submit" name="delete_inquiry" class="action-btn text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                
                                <div class="modal fade" id="viewModal<?= $inq['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Inquiry from <?= htmlspecialchars($inq['name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                                                    
                                                    <p><strong>Email:</strong> <?= $inq['email'] ?></p>
                                                    <p><strong>Phone:</strong> <?= $inq['phone'] ?? 'N/A' ?></p>
                                                    <p><strong>Subject:</strong> <?= htmlspecialchars($inq['subject'] ?? 'General') ?></p>
                                                    
                                                    <h6>Message:</h6>
                                                    <div class="p-3 bg-light rounded mb-3">
                                                        <?= nl2br(htmlspecialchars($inq['message'])) ?>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="new" <?= $inq['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                                            <option value="read" <?= $inq['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                                            <option value="replied" <?= $inq['status'] === 'replied' ? 'selected' : '' ?>>Replied</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Admin Notes</label>
                                                        <textarea name="admin_notes" class="form-control" rows="3"><?= htmlspecialchars($inq['admin_notes'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
