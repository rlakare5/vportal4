<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$product_id = (int)($data['product_id'] ?? 0);

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($action === 'add') {
    $result = addToWishlist($user_id, $product_id);
    echo json_encode($result);
} elseif ($action === 'remove') {
    $result = removeFromWishlist($user_id, $product_id);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
