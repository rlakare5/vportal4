<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: user/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE email = ? AND status = 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            header('Location: user/dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <a href="index.php" class="navbar-brand d-inline-flex align-items-center gap-2">
                <i class="fas fa-bolt" style="font-size: 40px; color: var(--primary-color);"></i>
                <span style="font-size: 28px; font-weight: 700; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">VPORTAL</span>
            </a>
            <h2>Welcome Back</h2>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Login</button>
        </form>
        
        <p class="text-center mt-4" style="color: var(--text-muted);">
            Don't have an account? <a href="register.php" style="color: var(--primary-color);">Sign Up</a>
        </p>
        
        <p class="text-center mt-3"><a href="index.php" style="color: var(--text-muted);"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
    </div>
</body>
</html>
