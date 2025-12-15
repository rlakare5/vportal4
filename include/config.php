<?php
session_start();

$db_host = "localhost";
$db_name = "vportal_ev";
$db_user = "root";
$db_pass = "";

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");

define('SITE_URL', 'http://localhost/vportal');
define('ADMIN_URL', SITE_URL . '/admin');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/assets/uploads');

define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ASSETS_PATH . 'uploads/');

function getSettings($key = null) {
    global $con;
    static $settings = null;
    
    if ($settings === null) {
        $settings = [];
        $result = mysqli_query($con, "SELECT setting_key, setting_value FROM global_settings");
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    if ($key) {
        return isset($settings[$key]) ? $settings[$key] : null;
    }
    return $settings;
}

function sanitize($data) {
    global $con;
    return mysqli_real_escape_string($con, htmlspecialchars(trim($data)));
}

function generateOrderNumber($prefix = 'ORD') {
    return $prefix . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function generateInvoiceNumber() {
    return 'INV' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function generateTicketNumber() {
    return 'TKT' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

function uploadImage($file, $folder = 'products') {
    $target_dir = UPLOADS_PATH . $folder . '/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_path = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $new_filename, 'path' => $folder . '/' . $new_filename];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}

function deleteImage($path) {
    $full_path = UPLOADS_PATH . $path;
    if (file_exists($full_path)) {
        unlink($full_path);
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function redirectIfNotLoggedIn($url = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $url");
        exit();
    }
}

function redirectIfNotAdmin($url = 'login.php') {
    if (!isAdminLoggedIn()) {
        header("Location: $url");
        exit();
    }
}

function logActivity($admin_id, $action, $module = null, $description = null, $reference_id = null, $reference_type = null) {
    global $con;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = mysqli_prepare($con, "INSERT INTO admin_activity (admin_id, action, module, description, reference_id, reference_type, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isssiss", $admin_id, $action, $module, $description, $reference_id, $reference_type, $ip);
    mysqli_stmt_execute($stmt);
}

function sendNotification($user_id, $title, $message, $type = 'system', $category = 'system') {
    global $con;
    $stmt = mysqli_prepare($con, "INSERT INTO notifications (user_id, title, message, type, category, status) VALUES (?, ?, ?, ?, ?, 'sent')");
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $title, $message, $type, $category);
    return mysqli_stmt_execute($stmt);
}

function getTimeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M d, Y', $time);
}

function createSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
?>
