<?php
$base_path = '';
$current_dir = dirname($_SERVER['PHP_SELF']);
if (strpos($current_dir, '/user') !== false || strpos($current_dir, '/admin') !== false) {
    $base_path = '../';
}
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= $base_path ?>index.php" class="navbar-brand">
                    <i class="fas fa-bolt" style="font-size: 30px; color: var(--primary-color);"></i>
                    <span style="font-size: 24px; font-weight: 700; color: var(--primary-color);">VPORTAL</span>
                </a>
                <p>Your trusted destination for premium electric vehicles. Experience the future of sustainable transportation.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= $base_path ?>bikes.php">EV Bikes</a></li>
                    <li><a href="<?= $base_path ?>compare.php">Compare Bikes</a></li>
                    <li><a href="<?= $base_path ?>offers.php">Offers</a></li>
                    <li><a href="<?= $base_path ?>preorder.php">Pre-Order</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Support</h4>
                <ul>
                    <li><a href="<?= $base_path ?>contact.php">Contact Us</a></li>
                    <li><a href="<?= $base_path ?>page.php?slug=about-us">About Us</a></li>
                    <li><a href="<?= $base_path ?>page.php?slug=terms-conditions">Terms & Conditions</a></li>
                    <li><a href="<?= $base_path ?>page.php?slug=privacy-policy">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contact Info</h4>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> <?= getSettings('site_address') ?? '123 EV Street, Tech City' ?></li>
                    <li><i class="fas fa-phone"></i> <?= getSettings('site_phone') ?? '+91 9876543210' ?></li>
                    <li><i class="fas fa-envelope"></i> <?= getSettings('site_email') ?? 'info@vportal.com' ?></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> VPORTAL EV Showroom. All Rights Reserved.</p>
        </div>
    </div>
</footer>
