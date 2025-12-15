<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $title = sanitize($_POST['title']);
    $message = sanitize($_POST['message']);
    $type = sanitize($_POST['type']);
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    
    if ($user_id) {
        sendNotification($user_id, $title, $message, $type);
        $success = 'Notification sent to user!';
    } else {
        $users = [];
        $result = mysqli_query($con, "SELECT id FROM users");
        while ($row = mysqli_fetch_assoc($result)) {
            sendNotification($row['id'], $title, $message, $type);
        }
        $success = 'Notification sent to all users!';
    }
    
    logActivity($_SESSION['admin_id'], 'create', 'notifications', 'Sent notification: ' . $title);
}

$users = [];
$result = mysqli_query($con, "SELECT id, first_name, last_name, email FROM users ORDER BY first_name");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$notifications = [];
$result = mysqli_query($con, "SELECT n.*, u.first_name, u.last_name 
                              FROM notifications n 
                              JOIN users u ON n.user_id = u.id 
                              ORDER BY n.created_at DESC LIMIT 50");
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - VPORTAL Admin</title>
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
                    <h1>Notifications</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                        <i class="fas fa-paper-plane"></i> Send Notification
                    </button>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Notifications</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($notifications)): ?>
                                <tr><td colspan="6" class="text-center py-4">No notifications sent yet</td></tr>
                                <?php else: ?>
                                <?php foreach ($notifications as $n): ?>
                                <tr>
                                    <td><?= htmlspecialchars($n['first_name'] . ' ' . $n['last_name']) ?></td>
                                    <td><?= htmlspecialchars($n['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($n['message'], 0, 50)) ?>...</td>
                                    <td><span class="badge bg-info"><?= ucfirst($n['type']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= $n['is_read'] ? 'success' : 'warning' ?>">
                                            <?= $n['is_read'] ? 'Read' : 'Unread' ?>
                                        </span>
                                    </td>
                                    <td><?= getTimeAgo($n['created_at']) ?></td>
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
    
    <div class="modal fade" id="sendNotificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">To</label>
                            <select name="user_id" class="form-control">
                                <option value="">All Users</option>
                                <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> (<?= $u['email'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="system">System</option>
                                <option value="offer">Offer</option>
                                <option value="order">Order Update</option>
                                <option value="promotional">Promotional</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="send_notification" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
