<?php
$base_path = '';
$current_dir = dirname($_SERVER['PHP_SELF']);
if (strpos($current_dir, '/user') !== false || strpos($current_dir, '/admin') !== false) {
    $base_path = '../';
}
?>
<nav class="navbar">
    <div class="container">
        <a href="<?= $base_path ?>index.php" class="navbar-brand">
            <i class="fas fa-bolt" style="font-size: 30px; color: var(--primary-color);"></i>
            <span>VPORTAL</span>
        </a>
        
        <ul class="navbar-nav">
            <li><a href="<?= $base_path ?>index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= $base_path ?>bikes.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['bikes.php', 'bike-details.php']) ? 'active' : '' ?>">EV Bikes</a></li>
            <li><a href="<?= $base_path ?>compare.php" class="<?= basename($_SERVER['PHP_SELF']) == 'compare.php' ? 'active' : '' ?>">Compare</a></li>
            <li><a href="<?= $base_path ?>offers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'offers.php' ? 'active' : '' ?>">Offers</a></li>
            <li><a href="<?= $base_path ?>gallery.php" class="<?= basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : '' ?>">Gallery</a></li>
            <li><a href="<?= $base_path ?>contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contact</a></li>
        </ul>
        
        <div class="navbar-actions">
            <?php if (isLoggedIn()): ?>
                <a href="<?= $base_path ?>user/wishlist.php" class="icon-btn" title="Wishlist">
                    <i class="fas fa-heart"></i>
                </a>
                <a href="<?= $base_path ?>user/dashboard.php" class="icon-btn" title="My Account">
                    <i class="fas fa-user"></i>
                </a>
            <?php else: ?>
                <a href="<?= $base_path ?>login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="<?= $base_path ?>register.php" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div class="mobile-menu">
        <ul class="mobile-nav-list">
            <li><a href="<?= $base_path ?>index.php">Home</a></li>
            <li><a href="<?= $base_path ?>bikes.php">EV Bikes</a></li>
            <li><a href="<?= $base_path ?>compare.php">Compare</a></li>
            <li><a href="<?= $base_path ?>offers.php">Offers</a></li>
            <li><a href="<?= $base_path ?>gallery.php">Gallery</a></li>
            <li><a href="<?= $base_path ?>contact.php">Contact</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?= $base_path ?>user/dashboard.php"><i class="fas fa-user me-2"></i>My Account</a></li>
                <li><a href="<?= $base_path ?>user/wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
            <?php else: ?>
                <li><a href="<?= $base_path ?>login.php"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                <li><a href="<?= $base_path ?>register.php"><i class="fas fa-user-plus me-2"></i>Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
