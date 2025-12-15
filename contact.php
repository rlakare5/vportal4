<?php
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill all required fields';
    } else {
        $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
        $sql = "INSERT INTO inquiries (user_id, name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isssss", $user_id, $name, $email, $phone, $subject, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Your message has been sent successfully. We will get back to you soon!';
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span>Contact</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100" style="background: var(--dark-bg); color: white; border-radius: 16px;">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Get in Touch</h4>
                            <div class="d-flex mb-4">
                                <div class="me-3" style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Address</h6>
                                    <p class="text-muted small mb-0"><?= getSettings('site_address') ?? '123 EV Street, Tech City, India' ?></p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="me-3" style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Phone</h6>
                                    <p class="text-muted small mb-0"><?= getSettings('site_phone') ?? '+91 9876543210' ?></p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="me-3" style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Email</h6>
                                    <p class="text-muted small mb-0"><?= getSettings('site_email') ?? 'info@vportal.com' ?></p>
                                </div>
                            </div>
                            <hr style="border-color: var(--dark-border);">
                            <h6 class="mb-3">Follow Us</h6>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Send us a Message</h4>
                            
                            <?php if ($error): ?>
                            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Your Name *</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email Address *</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phone Number</label>
                                            <input type="tel" name="phone" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Subject</label>
                                            <input type="text" name="subject" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Your Message *</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
