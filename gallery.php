<?php
require_once 'includes/functions.php';
$gallery = getGallery();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - VPORTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Showroom Gallery</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span>Gallery</span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <?php if (empty($gallery)): ?>
            <div class="gallery-grid">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/400x300?text=Gallery+<?= $i ?>" alt="Gallery Image">
                    <div class="gallery-overlay"><i class="fas fa-search-plus fa-2x"></i></div>
                </div>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($gallery as $item): ?>
                <div class="gallery-item">
                    <img src="<?= UPLOADS_URL ?>/gallery/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus fa-2x"></i>
                        <?php if ($item['title']): ?><p><?= htmlspecialchars($item['title']) ?></p><?php endif; ?>
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
