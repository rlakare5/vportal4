<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit();
}

$product = getProductById($product_id);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

$images = getProductImages($product_id);
$main_image = '';
foreach ($images as $img) {
    if ($img['image_type'] === 'main') {
        $main_image = UPLOADS_URL . '/products/' . $img['image_path'];
        break;
    }
}

if (!$main_image && !empty($images)) {
    $main_image = UPLOADS_URL . '/products/' . $images[0]['image_path'];
}

$product['main_image'] = $main_image ?: 'https://via.placeholder.com/400x300?text=' . urlencode($product['name']);

echo json_encode(['success' => true, 'product' => $product]);
?>
