<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: user/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($first_name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $check = mysqli_query($con, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email already registered';
        } else {
            $check = mysqli_query($con, "SELECT id FROM users WHERE phone = '$phone'");
            if (mysqli_num_rows($check) > 0) {
                $error = 'Phone number already registered';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($con, "INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $phone, $hashed_password);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .auth-card { max-width: 550px; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <a href="index.php" class="navbar-brand d-inline-flex align-items-center gap-2">
                <i class="fas fa-bolt" style="font-size: 40px; color: var(--primary-color);"></i>
                <span style="font-size: 28px; font-weight: 700; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">VPORTAL</span>
            </a>
            <h2>Create Account</h2>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="tel" name="phone" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Create Account</button>
        </form>
        
        <p class="text-center mt-4" style="color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary-color);">Login</a>
        </p>
        
        <p class="text-center mt-3"><a href="index.php" style="color: var(--text-muted);"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
    </div>
</body>
</html>
