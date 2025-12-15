<?php
require_once '../includes/functions.php';
redirectIfNotAdmin('login.php');

$invoice = null;
$items = [];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = mysqli_query($con, "SELECT i.*, u.first_name, u.last_name, u.email, u.phone, u.address, u.city, u.state, u.pincode 
                                  FROM invoices i 
                                  JOIN users u ON i.user_id = u.id 
                                  WHERE i.id = $id");
    $invoice = mysqli_fetch_assoc($result);
    
    if ($invoice) {
        $items_result = mysqli_query($con, "SELECT * FROM invoice_items WHERE invoice_id = $id");
        while ($row = mysqli_fetch_assoc($items_result)) {
            $items[] = $row;
        }
    }
}

if (!$invoice) {
    header('Location: invoices.php');
    exit();
}

$print_mode = isset($_GET['print']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= $invoice['invoice_number'] ?> - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .invoice-box { max-width: 800px; margin: 30px auto; padding: 30px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .invoice-header { border-bottom: 2px solid #00d4ff; padding-bottom: 20px; margin-bottom: 20px; }
        .invoice-header h1 { color: #00d4ff; margin: 0; }
        .invoice-details { margin-bottom: 30px; }
        .invoice-table { width: 100%; border-collapse: collapse; }
        .invoice-table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        .invoice-table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .invoice-total { background: #f8f9fa; font-weight: bold; }
        .invoice-footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #666; }
        @media print { 
            .no-print { display: none !important; } 
            body { background: white; }
            .invoice-box { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body <?= $print_mode ? 'onload="window.print()"' : '' ?>>
    <div class="no-print" style="background: #1e293b; padding: 15px; margin-bottom: 30px;">
        <div class="container">
            <a href="invoices.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Invoices
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm ms-2">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    
    <div class="invoice-box">
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-bolt"></i> VPORTAL</h1>
                <p class="mb-0">EV Showroom</p>
            </div>
            <div class="text-end">
                <h2>INVOICE</h2>
                <p class="mb-0"><strong><?= $invoice['invoice_number'] ?></strong></p>
                <p class="mb-0">Date: <?= date('M d, Y', strtotime($invoice['created_at'])) ?></p>
            </div>
        </div>
        
        <div class="invoice-details row">
            <div class="col-md-6">
                <h5>Bill To:</h5>
                <p class="mb-1"><strong><?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?></strong></p>
                <p class="mb-1"><?= htmlspecialchars($invoice['address'] ?? '') ?></p>
                <p class="mb-1"><?= htmlspecialchars(($invoice['city'] ?? '') . ', ' . ($invoice['state'] ?? '') . ' - ' . ($invoice['pincode'] ?? '')) ?></p>
                <p class="mb-1">Email: <?= $invoice['email'] ?></p>
                <p class="mb-0">Phone: <?= $invoice['phone'] ?? 'N/A' ?></p>
            </div>
            <div class="col-md-6 text-end">
                <h5>From:</h5>
                <p class="mb-1"><strong>VPORTAL EV Showroom</strong></p>
                <p class="mb-1"><?= getSettings('site_address') ?? '123 EV Street, Tech City' ?></p>
                <p class="mb-1">Email: <?= getSettings('site_email') ?? 'info@vportal.com' ?></p>
                <p class="mb-0">Phone: <?= getSettings('site_phone') ?? '+91 9876543210' ?></p>
                <p class="mb-0">GSTIN: <?= getSettings('gstin') ?? 'XXXXXXXXXXXX' ?></p>
            </div>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>HSN</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= $item['hsn_code'] ?? '-' ?></td>
                    <td class="text-end"><?= $item['quantity'] ?></td>
                    <td class="text-end"><?= formatCurrency($item['unit_price']) ?></td>
                    <td class="text-end"><?= formatCurrency($item['total_price']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">Subtotal:</td>
                    <td class="text-end"><?= formatCurrency($invoice['subtotal']) ?></td>
                </tr>
                <?php if (!empty($invoice['discount_amount'])): ?>
                <tr>
                    <td colspan="5" class="text-end">Discount:</td>
                    <td class="text-end">-<?= formatCurrency($invoice['discount_amount']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="5" class="text-end">CGST (<?= $invoice['cgst_rate'] ?? 9 ?>%):</td>
                    <td class="text-end"><?= formatCurrency($invoice['cgst_amount'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end">SGST (<?= $invoice['sgst_rate'] ?? 9 ?>%):</td>
                    <td class="text-end"><?= formatCurrency($invoice['sgst_amount'] ?? 0) ?></td>
                </tr>
                <tr class="invoice-total">
                    <td colspan="5" class="text-end">Grand Total:</td>
                    <td class="text-end"><?= formatCurrency($invoice['total_amount']) ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h6>Payment Status:</h6>
                <span class="badge bg-<?= $invoice['payment_status'] === 'paid' ? 'success' : 'warning' ?>" style="font-size: 14px;">
                    <?= ucfirst($invoice['payment_status']) ?>
                </span>
                <?php if ($invoice['payment_method']): ?>
                <p class="mt-2 mb-0">Method: <?= ucfirst($invoice['payment_method']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if ($invoice['notes']): ?>
                <h6>Notes:</h6>
                <p><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="invoice-footer">
            <p>Thank you for your business!</p>
            <small>This is a computer-generated invoice.</small>
        </div>
    </div>
</body>
</html>
