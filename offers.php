<?php
require_once 'includes/functions.php';
$offers = getOffers(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Special Offers</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span>Offers</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <?php if (empty($offers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tag fa-4x text-muted mb-4"></i>
                <h3>No Active Offers</h3>
                <p class="text-muted">Check back soon for exciting deals!</p>
                <a href="bikes.php" class="btn btn-primary">Browse Bikes</a>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($offers as $offer): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100" style="background: var(--gradient-primary); color: white; border-radius: 16px; overflow: hidden;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-white text-dark">
                                    <?= $offer['offer_type'] === 'percentage' ? $offer['discount_value'] . '% OFF' : 
                                       ($offer['offer_type'] === 'fixed' ? formatCurrency($offer['discount_value']) . ' OFF' : 'Free Gift') ?>
                                </span>
                                <span class="small">Valid till <?= date('M d, Y', strtotime($offer['end_date'])) ?></span>
                            </div>
                            <h3><?= htmlspecialchars($offer['title']) ?></h3>
                            <p class="opacity-75"><?= htmlspecialchars($offer['description']) ?></p>
                            <?php if ($offer['coupon_code']): ?>
                            <div class="bg-white text-dark d-inline-block px-3 py-2 rounded mb-3">
                                <small>Use Code:</small> <strong><?= $offer['coupon_code'] ?></strong>
                            </div>
                            <?php endif; ?>
                            <?php if ($offer['min_purchase'] > 0): ?>
                            <p class="small mb-0">*Min. purchase: <?= formatCurrency($offer['min_purchase']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
