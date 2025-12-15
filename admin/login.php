<?php
require_once '../include/config.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $stmt = mysqli_prepare($con, "SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 1");
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        
if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            
            mysqli_query($con, "UPDATE admins SET last_login = NOW() WHERE id = " . $admin['id']);
            logActivity($admin['id'], 'login', 'auth', 'Admin logged in');
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #00d4ff;
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: var(--dark-card);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .login-logo i {
            font-size: 40px;
            color: var(--secondary-color);
        }
        
        .login-logo span {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .login-header h2 {
            color: #f8fafc;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #64748b;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #f8fafc;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 1px solid #334155;
            border-radius: 8px;
            background: var(--dark-bg);
            color: #f8fafc;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }
        
        .form-control::placeholder {
            color: #64748b;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(124, 58, 237, 0.3);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 14px;
        }
        
        .back-link a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .demo-info {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(0, 212, 255, 0.2);
        }
        
        .demo-info p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }
        
        .demo-info strong {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-bolt"></i>
                    <span>VPORTAL</span>
                </div>
                <h2>Admin Login</h2>
                <p>Enter your credentials to access the dashboard</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username or Email</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Enter username or email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="demo-info">
                <p><strong>Demo Credentials:</strong><br>
                Username: <strong>admin</strong><br>
                Password: <strong>admin123</strong></p>
            </div>
            
            <p class="back-link">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </p>
        </div>
    </div>
</body>
</html>
