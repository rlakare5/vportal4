<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['user_id'];
        $current = (int)$_POST['current_status'];
        $new_status = $current ? 0 : 1;
        mysqli_query($con, "UPDATE users SET status = $new_status WHERE id = $id");
        $success = 'User status updated!';
    }
    
    if (isset($_POST['delete_user'])) {
        $id = (int)$_POST['user_id'];
        mysqli_query($con, "DELETE FROM users WHERE id = $id");
        $success = 'User deleted!';
    }
}

$users = [];
$result = mysqli_query($con, "SELECT u.*, 
                              (SELECT COUNT(*) FROM preorders WHERE user_id = u.id) as preorder_count,
                              (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count
                              FROM users u ORDER BY u.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - VPORTAL Admin</title>
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
                    <h1>Users</h1>
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
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Preorders</th>
                                    <th>Orders</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="text-center py-4">No users found</td></tr>
                                <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/uploads/avatars/<?= $u['avatar'] ?? 'default.png' ?>" 
                                                 class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;"
                                                 onerror="this.src='https://via.placeholder.com/40'">
                                            <div>
                                                <strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong><br>
                                                <small class="text-muted"><?= $u['email'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $u['phone'] ?? 'N/A' ?></td>
                                    <td><?= $u['preorder_count'] ?></td>
                                    <td><?= $u['order_count'] ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $u['status'] ?>">
                                            <button type="submit" name="toggle_status" class="status-badge <?= $u['status'] ? 'active' : 'inactive' ?>" style="border:none;cursor:pointer;">
                                                <?= $u['status'] ? 'Active' : 'Blocked' ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.')">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="delete_user" class="action-btn text-danger"><i class="fas fa-trash"></i></button>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
