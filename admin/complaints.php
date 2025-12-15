<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['complaint_id'];
        $status = sanitize($_POST['status']);
        $admin_notes = sanitize($_POST['admin_notes']);
        
        $sql = "UPDATE complaints SET status = '$status', admin_notes = '$admin_notes', updated_at = NOW() WHERE id = $id";
        if (mysqli_query($con, $sql)) {
            $success = 'Complaint updated!';
            logActivity($_SESSION['admin_id'], 'update', 'complaints', 'Updated complaint status to: ' . $status, $id, 'complaint');
        } else {
            $error = 'Failed to update.';
        }
    }
}

$complaints = [];
$result = mysqli_query($con, "SELECT c.*, u.first_name, u.last_name, u.email, u.phone, p.name as product_name 
                              FROM complaints c 
                              JOIN users u ON c.user_id = u.id 
                              LEFT JOIN products p ON c.product_id = p.id 
                              ORDER BY c.status = 'open' DESC, c.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $complaints[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints - VPORTAL Admin</title>
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
                    <h1>Complaints</h1>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($complaints)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-smile-beam fa-3x text-success mb-3"></i>
                            <p>No complaints! Great job!</p>
                        </div>
                        <?php else: ?>
                        <div class="accordion" id="complaintsAccordion">
                            <?php foreach ($complaints as $i => $c): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#complaint<?= $c['id'] ?>">
                                        <span class="badge bg-<?= $c['status'] === 'open' ? 'danger' : ($c['status'] === 'in_progress' ? 'warning' : 'success') ?> me-2">
                                            <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                                        </span>
                                        <strong class="me-3"><?= $c['ticket_number'] ?></strong>
                                        <?= htmlspecialchars($c['subject']) ?>
                                        <small class="ms-auto text-muted"><?= getTimeAgo($c['created_at']) ?></small>
                                    </button>
                                </h2>
                                <div id="complaint<?= $c['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6>Customer Details:</h6>
                                                <p>
                                                    <strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong><br>
                                                    <i class="fas fa-envelope"></i> <?= $c['email'] ?><br>
                                                    <i class="fas fa-phone"></i> <?= $c['phone'] ?? 'N/A' ?>
                                                </p>
                                                
                                                <h6>Product:</h6>
                                                <p><?= $c['product_name'] ? htmlspecialchars($c['product_name']) : 'General' ?></p>
                                                
                                                <h6>Complaint:</h6>
                                                <p><?= nl2br(htmlspecialchars($c['description'])) ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <form method="POST">
                                                    <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="open" <?= $c['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                                            <option value="in_progress" <?= $c['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                            <option value="resolved" <?= $c['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                            <option value="closed" <?= $c['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Admin Notes</label>
                                                        <textarea name="admin_notes" class="form-control" rows="3"><?= htmlspecialchars($c['admin_notes'] ?? '') ?></textarea>
                                                    </div>
                                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                                </form>
                                            </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
