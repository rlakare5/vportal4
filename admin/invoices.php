<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$invoices = [];
$result = mysqli_query($con, "SELECT i.*, u.first_name, u.last_name, u.email 
                              FROM invoices i 
                              JOIN users u ON i.user_id = u.id 
                              ORDER BY i.created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $invoices[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - VPORTAL Admin</title>
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
                <div class="page-title">
                    <h1>Invoices</h1>
                    <a href="billing.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Invoice
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Subtotal</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoices)): ?>
                                <tr><td colspan="8" class="text-center py-4">No invoices found</td></tr>
                                <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><strong><?= $inv['invoice_number'] ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($inv['first_name'] . ' ' . $inv['last_name']) ?><br>
                                        <small class="text-muted"><?= $inv['email'] ?></small>
                                    </td>
                                    <td><?= formatCurrency($inv['subtotal']) ?></td>
                                    <td><?= formatCurrency(($inv['cgst_amount'] ?? 0) + ($inv['sgst_amount'] ?? 0)) ?></td>
                                    <td><strong><?= formatCurrency($inv['total_amount']) ?></strong></td>
                                    <td><span class="status-badge <?= $inv['payment_status'] ?>"><?= ucfirst($inv['payment_status']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($inv['created_at'])) ?></td>
                                    <td>
                                        <a href="invoice-view.php?id=<?= $inv['id'] ?>" class="action-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="invoice-view.php?id=<?= $inv['id'] ?>&print=1" class="action-btn" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
