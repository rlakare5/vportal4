<?php
require_once __DIR__ . '/../include/config.php';

function getProducts($limit = null, $category = null, $featured = null, $status = 'active') {
    global $con;
    
    $sql = "SELECT p.*, 
            (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as main_image
            FROM products p WHERE 1=1";
    
    if ($status) {
        $sql .= " AND p.status = '" . sanitize($status) . "'";
    }
    if ($category) {
        $sql .= " AND p.category = '" . sanitize($category) . "'";
    }
    if ($featured !== null) {
        $sql .= " AND p.featured = " . ($featured ? 1 : 0);
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $result = mysqli_query($con, $sql);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    return $products;
}

function getProductById($id) {
    global $con;
    $id = (int)$id;
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result);
}

function getProductImages($product_id) {
    global $con;
    $product_id = (int)$product_id;
    $sql = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY image_type, sort_order";
    $result = mysqli_query($con, $sql);
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
    return $images;
}

function getProductVariants($product_id) {
    global $con;
    $product_id = (int)$product_id;
    $sql = "SELECT * FROM variants WHERE product_id = $product_id AND status = 1";
    $result = mysqli_query($con, $sql);
    $variants = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $variants[] = $row;
    }
    return $variants;
}

function getAccessories($product_id = null) {
    global $con;
    $sql = "SELECT * FROM accessories WHERE status = 1";
    if ($product_id) {
        $sql .= " AND (product_id = " . (int)$product_id . " OR product_id IS NULL)";
    }
    $result = mysqli_query($con, $sql);
    $accessories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $accessories[] = $row;
    }
    return $accessories;
}

function getOffers($active_only = true) {
    global $con;
    $sql = "SELECT * FROM offers WHERE 1=1";
    if ($active_only) {
        $sql .= " AND status = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()";
    }
    $sql .= " ORDER BY created_at DESC";
    $result = mysqli_query($con, $sql);
    $offers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $offers[] = $row;
    }
    return $offers;
}

function getBanners($position = null) {
    global $con;
    $sql = "SELECT * FROM banners WHERE status = 1";
    if ($position) {
        $sql .= " AND position = '" . sanitize($position) . "'";
    }
    $sql .= " AND (start_date IS NULL OR start_date <= CURDATE())";
    $sql .= " AND (end_date IS NULL OR end_date >= CURDATE())";
    $sql .= " ORDER BY sort_order ASC";
    $result = mysqli_query($con, $sql);
    $banners = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $banners[] = $row;
    }
    return $banners;
}

function getTestimonials($limit = null) {
    global $con;
    $sql = "SELECT t.*, p.name as product_name FROM testimonials t 
            LEFT JOIN products p ON t.product_id = p.id 
            WHERE t.status = 1 ORDER BY t.created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $result = mysqli_query($con, $sql);
    $testimonials = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $testimonials[] = $row;
    }
    return $testimonials;
}

function getGallery($category = null, $limit = null) {
    global $con;
    $sql = "SELECT * FROM gallery WHERE status = 1";
    if ($category) {
        $sql .= " AND category = '" . sanitize($category) . "'";
    }
    $sql .= " ORDER BY sort_order ASC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $result = mysqli_query($con, $sql);
    $gallery = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $gallery[] = $row;
    }
    return $gallery;
}

function getPage($slug) {
    global $con;
    $slug = sanitize($slug);
    $sql = "SELECT * FROM pages WHERE slug = '$slug' AND status = 1";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result);
}

function getUserById($id) {
    global $con;
    $id = (int)$id;
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result);
}

function getUserPreorders($user_id) {
    global $con;
    $user_id = (int)$user_id;
    $sql = "SELECT po.*, p.name as product_name, p.slug as product_slug,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as product_image
            FROM preorders po 
            JOIN products p ON po.product_id = p.id 
            WHERE po.user_id = $user_id 
            ORDER BY po.created_at DESC";
    $result = mysqli_query($con, $sql);
    $preorders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $preorders[] = $row;
    }
    return $preorders;
}

function getUserOrders($user_id) {
    global $con;
    $user_id = (int)$user_id;
    $sql = "SELECT o.*, 
            (SELECT GROUP_CONCAT(oi.product_name SEPARATOR ', ') FROM order_items oi WHERE oi.order_id = o.id) as items
            FROM orders o 
            WHERE o.user_id = $user_id 
            ORDER BY o.created_at DESC";
    $result = mysqli_query($con, $sql);
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    return $orders;
}

function getUserWishlist($user_id) {
    global $con;
    $user_id = (int)$user_id;
    $sql = "SELECT w.*, p.*, 
            (SELECT image_path FROM product_images WHERE product_id = p.id AND image_type = 'main' LIMIT 1) as main_image
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = $user_id 
            ORDER BY w.created_at DESC";
    $result = mysqli_query($con, $sql);
    $wishlist = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $wishlist[] = $row;
    }
    return $wishlist;
}

function getUserNotifications($user_id, $unread_only = false) {
    global $con;
    $user_id = (int)$user_id;
    $sql = "SELECT * FROM notifications WHERE user_id = $user_id";
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC";
    $result = mysqli_query($con, $sql);
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    return $notifications;
}

function addToWishlist($user_id, $product_id) {
    global $con;
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    
    $check = mysqli_query($con, "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
    if (mysqli_num_rows($check) > 0) {
        return ['success' => false, 'message' => 'Already in wishlist'];
    }
    
    $sql = "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)";
    if (mysqli_query($con, $sql)) {
        return ['success' => true, 'message' => 'Added to wishlist'];
    }
    return ['success' => false, 'message' => 'Failed to add'];
}

function removeFromWishlist($user_id, $product_id) {
    global $con;
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    
    $sql = "DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id";
    if (mysqli_query($con, $sql)) {
        return ['success' => true, 'message' => 'Removed from wishlist'];
    }
    return ['success' => false, 'message' => 'Failed to remove'];
}

function isInWishlist($user_id, $product_id) {
    global $con;
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    
    $check = mysqli_query($con, "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
    return mysqli_num_rows($check) > 0;
}

function calculateGST($amount, $cgst_rate = 9, $sgst_rate = 9) {
    $cgst = ($amount * $cgst_rate) / 100;
    $sgst = ($amount * $sgst_rate) / 100;
    return [
        'cgst' => round($cgst, 2),
        'sgst' => round($sgst, 2),
        'total_tax' => round($cgst + $sgst, 2),
        'grand_total' => round($amount + $cgst + $sgst, 2)
    ];
}

function getDashboardStats() {
    global $con;
    
    $stats = [];
    
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $stats['total_bikes'] = mysqli_fetch_assoc($result)['count'];
    
    $result = mysqli_query($con, "SELECT SUM(stock_quantity) as total FROM products");
    $stats['total_stock'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM preorders WHERE status = 'pending'");
    $stats['pending_preorders'] = mysqli_fetch_assoc($result)['count'];
    
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = mysqli_fetch_assoc($result)['count'];
    
    $result = mysqli_query($con, "SELECT SUM(total_amount) as total FROM invoices WHERE payment_status = 'paid'");
    $stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM offers WHERE status = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()");
    $stats['active_offers'] = mysqli_fetch_assoc($result)['count'];
    
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = mysqli_fetch_assoc($result)['count'];
    
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM complaints WHERE status = 'open'");
    $stats['open_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}
?>
