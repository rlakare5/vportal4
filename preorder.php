<?php
require_once 'includes/functions.php';

$product_id = isset($_GET['product']) ? (int)$_GET['product'] : 0;
$product = null;
$variants = [];

if ($product_id) {
    $product = getProductById($product_id);
    if ($product) {
        $variants = getProductVariants($product_id);
    }
}

$products = getProducts(null, null, null, 'active');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=preorder');
        exit();
    }
    
    $selected_product_id = (int)$_POST['product_id'];
    $variant_id = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
    $quantity = (int)$_POST['quantity'];
    $customer_name = sanitize($_POST['customer_name']);
    $customer_email = sanitize($_POST['customer_email']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $delivery_address = sanitize($_POST['delivery_address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    $notes = sanitize($_POST['notes']);
    
    $selected_product = getProductById($selected_product_id);
    if (!$selected_product) {
        $error = 'Invalid product selected';
    } else {
        $unit_price = $selected_product['sale_price'] ?? $selected_product['base_price'];
        
        if ($variant_id) {
            $variant = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM variants WHERE id = $variant_id"));
            if ($variant) {
                $unit_price += $variant['price_difference'];
            }
        }
        
        $total_amount = $unit_price * $quantity;
        $order_number = generateOrderNumber('PRE');
        
        $sql = "INSERT INTO preorders (order_number, user_id, product_id, variant_id, quantity, unit_price, total_amount, 
                customer_name, customer_email, customer_phone, delivery_address, city, state, pincode, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        $user_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "siiidddssssssss", $order_number, $user_id, $selected_product_id, 
            $variant_id, $quantity, $unit_price, $total_amount, $customer_name, $customer_email, $customer_phone, 
            $delivery_address, $city, $state, $pincode, $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            sendNotification($_SESSION['user_id'], 'Preorder Placed', 
                "Your preorder #$order_number has been placed successfully. We will notify you once it's approved.", 
                'system', 'preorder');
            
            $success = "Your preorder #$order_number has been placed successfully! We will contact you soon.";
        } else {
            $error = 'Failed to place preorder. Please try again.';
        }
    }
}

$user = null;
if (isLoggedIn()) {
    $user = getUserById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Order - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Pre-Order Your EV Bike</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span>Pre-Order</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
            <div class="text-center">
                <a href="user/dashboard.php" class="btn btn-primary">View My Orders</a>
                <a href="bikes.php" class="btn btn-outline">Continue Shopping</a>
            </div>
            <?php else: ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            
            <?php if (!isLoggedIn()): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> Please <a href="login.php">login</a> or <a href="register.php">create an account</a> to place a preorder.
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                            <div class="card-body p-4">
                                <h4 class="mb-4">Select Your EV Bike</h4>
                                <div class="form-group">
                                    <label>Choose Product *</label>
                                    <select name="product_id" id="productSelect" class="form-control" required onchange="updatePrice()">
                                        <option value="">-- Select a Bike --</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" 
                                                data-price="<?= $p['sale_price'] ?? $p['base_price'] ?>"
                                                <?= $product && $product['id'] == $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?> - <?= formatCurrency($p['sale_price'] ?? $p['base_price']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Variant (Optional)</label>
                                            <select name="variant_id" id="variantSelect" class="form-control" onchange="updatePrice()">
                                                <option value="">-- Select Variant --</option>
                                                <?php foreach ($variants as $v): ?>
                                                <option value="<?= $v['id'] ?>" data-price="<?= $v['price_difference'] ?>">
                                                    <?= htmlspecialchars($v['variant_name']) ?> 
                                                    <?= $v['price_difference'] > 0 ? '(+' . formatCurrency($v['price_difference']) . ')' : '' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="5" onchange="updatePrice()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                            <div class="card-body p-4">
                                <h4 class="mb-4">Contact Information</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Full Name *</label>
                                            <input type="text" name="customer_name" class="form-control" required 
                                                   value="<?= $user ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email *</label>
                                            <input type="email" name="customer_email" class="form-control" required
                                                   value="<?= $user ? htmlspecialchars($user['email']) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Phone *</label>
                                    <input type="tel" name="customer_phone" class="form-control" required
                                           value="<?= $user ? htmlspecialchars($user['phone']) : '' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                            <div class="card-body p-4">
                                <h4 class="mb-4">Delivery Address</h4>
                                <div class="form-group">
                                    <label>Address *</label>
                                    <textarea name="delivery_address" class="form-control" rows="2" required><?= $user ? htmlspecialchars($user['address']) : '' ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>City *</label>
                                            <input type="text" name="city" class="form-control" required value="<?= $user ? htmlspecialchars($user['city']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>State *</label>
                                            <input type="text" name="state" class="form-control" required value="<?= $user ? htmlspecialchars($user['state']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Pincode *</label>
                                            <input type="text" name="pincode" class="form-control" required value="<?= $user ? htmlspecialchars($user['pincode']) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Additional Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Any special requirements..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card" style="background: var(--dark-bg); border-radius: 16px; color: white; position: sticky; top: 100px;">
                            <div class="card-body p-4">
                                <h4 class="mb-4">Order Summary</h4>
                                <div id="orderSummary">
                                    <p class="text-muted">Select a product to see summary</p>
                                </div>
                                <hr style="border-color: var(--dark-border);">
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Estimated Total</span>
                                    <span class="h4 mb-0" id="totalPrice">₹0</span>
                                </div>
                                <p class="small text-muted mb-4">* Final price may vary. Our team will confirm the exact amount.</p>
                                
                                <?php if (isLoggedIn()): ?>
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-calendar-check"></i> Place Preorder
                                </button>
                                <?php else: ?>
                                <a href="login.php" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login to Preorder
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    function updatePrice() {
        const productSelect = document.getElementById('productSelect');
        const variantSelect = document.getElementById('variantSelect');
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        
        let price = 0;
        let productName = '';
        
        if (productSelect.value) {
            const option = productSelect.options[productSelect.selectedIndex];
            price = parseFloat(option.dataset.price) || 0;
            productName = option.text.split(' - ')[0];
        }
        
        if (variantSelect.value) {
            const variantOption = variantSelect.options[variantSelect.selectedIndex];
            price += parseFloat(variantOption.dataset.price) || 0;
        }
        
        const total = price * quantity;
        
        document.getElementById('totalPrice').textContent = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 0
        }).format(total);
        
        if (productName) {
            document.getElementById('orderSummary').innerHTML = `
                <p><strong>${productName}</strong></p>
                <p>Quantity: ${quantity}</p>
                <p>Unit Price: ${new Intl.NumberFormat('en-IN', {style: 'currency', currency: 'INR', minimumFractionDigits: 0}).format(price)}</p>
            `;
        }
    }
    
    updatePrice();
    </script>
</body>
</html>
