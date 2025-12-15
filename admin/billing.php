<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$message = '';
$error = '';

$orders = [];
$result = mysqli_query($con, "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.address 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              WHERE o.id NOT IN (SELECT order_id FROM invoices)
                              ORDER BY o.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

$users = [];
$result = mysqli_query($con, "SELECT * FROM users ORDER BY first_name");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$cgst_rate = (float)(getSettings('cgst_rate') ?? 9);
$sgst_rate = (float)(getSettings('sgst_rate') ?? 9);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_invoice'])) {
    $order_id = (int)$_POST['order_id'];
    $user_id = (int)$_POST['user_id'];
    $customer_name = sanitize($_POST['customer_name']);
    $customer_email = sanitize($_POST['customer_email']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $customer_address = sanitize($_POST['customer_address']);
    $subtotal = (float)$_POST['subtotal'];
    $discount_percent = (float)$_POST['discount_percent'];
    $service_charge = (float)$_POST['service_charge'];
    $payment_method = sanitize($_POST['payment_method']);
    $amount_paid = (float)$_POST['amount_paid'];
    $notes = sanitize($_POST['notes']);
    
    $discount_amount = ($subtotal * $discount_percent) / 100;
    $taxable_amount = $subtotal - $discount_amount;
    $cgst_amount = ($taxable_amount * $cgst_rate) / 100;
    $sgst_amount = ($taxable_amount * $sgst_rate) / 100;
    $total_amount = $taxable_amount + $cgst_amount + $sgst_amount + $service_charge;
    $balance_due = $total_amount - $amount_paid;
    
    $payment_status = 'unpaid';
    if ($amount_paid >= $total_amount) {
        $payment_status = 'paid';
    } elseif ($amount_paid > 0) {
        $payment_status = 'partial';
    }
    
    $invoice_number = generateInvoiceNumber();
    
    $sql = "INSERT INTO invoices (invoice_number, order_id, user_id, customer_name, customer_email, customer_phone, 
            customer_address, subtotal, discount_percent, discount_amount, cgst_percent, cgst_amount, sgst_percent, 
            sgst_amount, service_charge, total_amount, amount_paid, balance_due, payment_method, payment_status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "siissssdddddddddddsss", 
        $invoice_number, $order_id, $user_id, $customer_name, $customer_email, $customer_phone,
        $customer_address, $subtotal, $discount_percent, $discount_amount, $cgst_rate, $cgst_amount,
        $sgst_rate, $sgst_amount, $service_charge, $total_amount, $amount_paid, $balance_due,
        $payment_method, $payment_status, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        $invoice_id = mysqli_insert_id($con);
        
        if ($order_id > 0) {
            mysqli_query($con, "UPDATE orders SET payment_status = '$payment_status' WHERE id = $order_id");
        }
        
        if ($amount_paid > 0) {
            $payment_id = 'PAY' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            mysqli_query($con, "INSERT INTO payments (payment_id, order_id, invoice_id, user_id, amount, payment_method, status) 
                               VALUES ('$payment_id', $order_id, $invoice_id, $user_id, $amount_paid, '$payment_method', 'completed')");
        }
        
        sendNotification($user_id, 'Invoice Generated', 
            "Your invoice #$invoice_number has been generated. Total amount: " . formatCurrency($total_amount), 
            'system', 'order');
        
        logActivity($_SESSION['admin_id'], 'create', 'invoices', "Created invoice $invoice_number", $invoice_id, 'invoice');
        
        header("Location: invoice-view.php?id=$invoice_id&new=1");
        exit();
    } else {
        $error = 'Failed to create invoice: ' . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice - VPORTAL Admin</title>
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
                <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>
                
                <div class="page-title">
                    <h1>Create Invoice</h1>
                    <a href="invoices.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All Invoices
                    </a>
                </div>
                
                <form method="POST" id="invoiceForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3>Customer Information</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Link to Order (Optional)</label>
                                        <select name="order_id" id="order_id" class="form-control" onchange="loadOrderDetails()">
                                            <option value="0">-- Create without Order --</option>
                                            <?php foreach ($orders as $order): ?>
                                            <option value="<?= $order['id'] ?>" 
                                                    data-user="<?= htmlspecialchars(json_encode($order)) ?>">
                                                <?= $order['order_number'] ?> - <?= $order['first_name'] ?> <?= $order['last_name'] ?> - <?= formatCurrency($order['total_amount']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Select Customer *</label>
                                        <select name="user_id" id="user_id" class="form-control" required onchange="loadUserDetails()">
                                            <option value="">-- Select Customer --</option>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($user['phone']) ?>"
                                                    data-address="<?= htmlspecialchars($user['address']) ?>">
                                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> (<?= $user['email'] ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Customer Name *</label>
                                            <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="customer_email" id="customer_email" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Phone *</label>
                                            <input type="text" name="customer_phone" id="customer_phone" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Address</label>
                                            <input type="text" name="customer_address" id="customer_address" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header"><h3>Billing Details</h3></div>
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Subtotal (₹) *</label>
                                            <input type="number" name="subtotal" id="subtotal" class="form-control" required step="0.01" min="0" onchange="calculateTotal()">
                                        </div>
                                        <div class="form-group">
                                            <label>Discount (%)</label>
                                            <input type="number" name="discount_percent" id="discount_percent" class="form-control" value="0" step="0.01" min="0" max="100" onchange="calculateTotal()">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>CGST (<?= $cgst_rate ?>%)</label>
                                            <input type="text" id="cgst_display" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>SGST (<?= $sgst_rate ?>%)</label>
                                            <input type="text" id="sgst_display" class="form-control" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Service Charge (₹)</label>
                                            <input type="number" name="service_charge" id="service_charge" class="form-control" value="0" step="0.01" min="0" onchange="calculateTotal()">
                                        </div>
                                        <div class="form-group">
                                            <label>Grand Total (₹)</label>
                                            <input type="text" id="grand_total" class="form-control" readonly style="font-weight: bold; font-size: 18px;">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header"><h3>Payment</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Payment Method *</label>
                                        <select name="payment_method" class="form-control" required>
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="upi">UPI</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="emi">EMI</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Amount Paid (₹)</label>
                                        <input type="number" name="amount_paid" id="amount_paid" class="form-control" value="0" step="0.01" min="0" onchange="updateBalance()">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Balance Due (₹)</label>
                                        <input type="text" id="balance_due" class="form-control" readonly>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="create_invoice" class="btn btn-primary btn-lg">
                                            <i class="fas fa-file-invoice"></i> Create Invoice
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header"><h3>GST Information</h3></div>
                                <div class="card-body">
                                    <p><strong>GST Number:</strong><br><?= getSettings('gst_number') ?? 'Not configured' ?></p>
                                    <p><strong>CGST Rate:</strong> <?= $cgst_rate ?>%</p>
                                    <p><strong>SGST Rate:</strong> <?= $sgst_rate ?>%</p>
                                    <p class="text-muted small">GST is auto-calculated on taxable amount (Subtotal - Discount)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const cgstRate = <?= $cgst_rate ?>;
    const sgstRate = <?= $sgst_rate ?>;
    
    function loadOrderDetails() {
        const select = document.getElementById('order_id');
        const option = select.options[select.selectedIndex];
        
        if (option.value !== '0') {
            const order = JSON.parse(option.dataset.user);
            document.getElementById('user_id').value = order.user_id;
            document.getElementById('customer_name').value = order.first_name + ' ' + order.last_name;
            document.getElementById('customer_email').value = order.email;
            document.getElementById('customer_phone').value = order.phone;
            document.getElementById('customer_address').value = order.address || '';
            document.getElementById('subtotal').value = order.subtotal;
            calculateTotal();
        }
    }
    
    function loadUserDetails() {
        const select = document.getElementById('user_id');
        const option = select.options[select.selectedIndex];
        
        if (option.value) {
            document.getElementById('customer_name').value = option.dataset.name;
            document.getElementById('customer_email').value = option.dataset.email;
            document.getElementById('customer_phone').value = option.dataset.phone;
            document.getElementById('customer_address').value = option.dataset.address || '';
        }
    }
    
    function calculateTotal() {
        const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
        const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;
        const serviceCharge = parseFloat(document.getElementById('service_charge').value) || 0;
        
        const discountAmount = (subtotal * discountPercent) / 100;
        const taxableAmount = subtotal - discountAmount;
        const cgst = (taxableAmount * cgstRate) / 100;
        const sgst = (taxableAmount * sgstRate) / 100;
        const grandTotal = taxableAmount + cgst + sgst + serviceCharge;
        
        document.getElementById('cgst_display').value = '₹' + cgst.toFixed(2);
        document.getElementById('sgst_display').value = '₹' + sgst.toFixed(2);
        document.getElementById('grand_total').value = '₹' + grandTotal.toFixed(2);
        
        updateBalance();
    }
    
    function updateBalance() {
        const grandTotal = parseFloat(document.getElementById('grand_total').value.replace('₹', '')) || 0;
        const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
        const balance = grandTotal - amountPaid;
        
        document.getElementById('balance_due').value = '₹' + balance.toFixed(2);
    }
    
    calculateTotal();
    </script>
</body>
</html>
