<aside class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-bolt" style="font-size: 24px; color: var(--secondary-color);"></i>
        <span class="sidebar-logo">VPORTAL</span>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">Main</span>
            <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Products</span>
            <a href="products.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                <i class="fas fa-motorcycle"></i>
                <span>EV Bikes</span>
            </a>
            <a href="variants.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'variants.php' ? 'active' : '' ?>">
                <i class="fas fa-palette"></i>
                <span>Variants</span>
            </a>
            <a href="accessories.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'accessories.php' ? 'active' : '' ?>">
                <i class="fas fa-toolbox"></i>
                <span>Accessories</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Orders</span>
            <a href="preorders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'preorders.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Preorders</span>
                <?php
                $pending = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM preorders WHERE status = 'pending'"));
                if ($pending['cnt'] > 0):
                ?>
                <span class="badge"><?= $pending['cnt'] ?></span>
                <?php endif; ?>
            </a>
            <a href="orders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Billing</span>
            <a href="billing.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice"></i>
                <span>Create Invoice</span>
            </a>
            <a href="invoices.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>All Invoices</span>
            </a>
            <a href="reports.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Marketing</span>
            <a href="offers.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'offers.php' ? 'active' : '' ?>">
                <i class="fas fa-tag"></i>
                <span>Offers</span>
            </a>
            <a href="notifications.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Content</span>
            <a href="banners.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'active' : '' ?>">
                <i class="fas fa-images"></i>
                <span>Banners</span>
            </a>
            <a href="pages.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'pages.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Pages</span>
            </a>
            <a href="gallery.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : '' ?>">
                <i class="fas fa-photo-video"></i>
                <span>Gallery</span>
            </a>
            <a href="testimonials.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : '' ?>">
                <i class="fas fa-quote-right"></i>
                <span>Testimonials</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Support</span>
            <a href="complaints.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'complaints.php' ? 'active' : '' ?>">
                <i class="fas fa-headset"></i>
                <span>Complaints</span>
                <?php
                $open = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM complaints WHERE status = 'open'"));
                if ($open['cnt'] > 0):
                ?>
                <span class="badge"><?= $open['cnt'] ?></span>
                <?php endif; ?>
            </a>
            <a href="inquiries.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'inquiries.php' ? 'active' : '' ?>">
                <i class="fas fa-question-circle"></i>
                <span>Inquiries</span>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Settings</span>
            <a href="settings.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>General Settings</span>
            </a>
            <a href="users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>
