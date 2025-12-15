<?php
require_once 'includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
$page = getPage($slug);

if (!$page) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['meta_title'] ?? $page['title']) ?> - VPORTAL</title>
    <?php if ($page['meta_description']): ?>
    <meta name="description" content="<?= htmlspecialchars($page['meta_description']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>/</span> <span><?= htmlspecialchars($page['title']) ?></span>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-md);">
                <div class="card-body p-5">
                    <?= $page['content'] ?>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
