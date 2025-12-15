<?php
require_once 'includes/functions.php';

$featured_products = getProducts(6, null, true);
if (empty($featured_products)) {
    $featured_products = getProducts(6);
}
$all_products = getProducts(8);
$banners = getBanners('home_slider');
$offers = getOffers(true);
$testimonials = getTestimonials(3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPORTAL - EV Bike Showroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-bolt" style="font-size: 30px; color: var(--primary-color);"></i>
                <span>VPORTAL</span>
            </a>
            
            <ul class="navbar-nav">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="bikes.php">EV Bikes</a></li>
                <li><a href="compare.php">Compare</a></li>
                <li><a href="offers.php">Offers</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            
            <div class="navbar-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="user/wishlist.php" class="icon-btn" title="Wishlist">
                        <i class="fas fa-heart"></i>
                    </a>
                    <a href="user/dashboard.php" class="icon-btn" title="My Account">
                        <i class="fas fa-user"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline btn-sm">Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
                <?php endif; ?>
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Experience The Future of <span>Electric Mobility</span></h1>
                    <p>Discover our premium collection of electric vehicles designed for sustainable and stylish transportation. Zero emissions, maximum performance.</p>
                    <div class="d-flex gap-3">
                        <a href="bikes.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-motorcycle"></i> Explore Bikes
                        </a>
                        <a href="preorder.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-calendar-check"></i> Pre-Order Now
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="number">50+</div>
                            <div class="label">EV Models</div>
                        </div>
                        <div class="hero-stat">
                            <div class="number">5000+</div>
                            <div class="label">Happy Customers</div>
                        </div>
                        <div class="hero-stat">
                            <div class="number">100km</div>
                            <div class="label">Avg Range</div>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/images/hero-bike.png" alt="Electric Bike" onerror="this.src='https://via.placeholder.com/600x400?text=EV+Bike'">
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Featured EV Bikes</h2>
                <p>Explore our handpicked selection of top-performing electric vehicles</p>
            </div>
            
            <div class="products-grid">
                <?php if (empty($featured_products)): ?>
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="product-card">
                        <span class="badge badge-featured">Featured</span>
                        <div class="product-image">
                            <img src="https://via.placeholder.com/400x300?text=EV+Bike+<?= $i + 1 ?>" alt="EV Bike">
                            <div class="product-actions">
                                <button title="Add to Wishlist"><i class="fas fa-heart"></i></button>
                                <button title="Quick View"><i class="fas fa-eye"></i></button>
                                <button title="Compare"><i class="fas fa-exchange-alt"></i></button>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Electric Scooter</div>
                            <h3 class="product-name">Sample EV Model <?= $i + 1 ?></h3>
                            <div class="product-specs">
                                <div class="product-spec"><i class="fas fa-battery-full"></i> 3.5 kWh</div>
                                <div class="product-spec"><i class="fas fa-road"></i> 100 km</div>
                                <div class="product-spec"><i class="fas fa-tachometer-alt"></i> 80 kmph</div>
                            </div>
                            <div class="product-price">
                                <span class="current-price">₹89,999</span>
                                <span class="original-price">₹99,999</span>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['featured']): ?>
                        <span class="badge badge-featured">Featured</span>
                        <?php endif; ?>
                        <div class="product-image">
                            <img src="<?= UPLOADS_URL ?>/products/<?= $product['main_image'] ?? 'placeholder.jpg' ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='https://via.placeholder.com/400x300?text=<?= urlencode($product['name']) ?>'">
                            <div class="product-actions">
                                <button onclick="addToWishlist(<?= $product['id'] ?>)" title="Add to Wishlist">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button onclick="quickView(<?= $product['id'] ?>)" title="Quick View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="addToCompare(<?= $product['id'] ?>)" title="Compare">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                            </div>
                        </div>
                        <a href="bike-details.php?slug=<?= $product['slug'] ?>">
                            <div class="product-info">
                                <div class="product-category"><?= ucfirst($product['category']) ?></div>
                                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="product-specs">
                                    <div class="product-spec"><i class="fas fa-battery-full"></i> <?= $product['battery_capacity'] ?></div>
                                    <div class="product-spec"><i class="fas fa-road"></i> <?= $product['max_range'] ?></div>
                                    <div class="product-spec"><i class="fas fa-tachometer-alt"></i> <?= $product['top_speed'] ?></div>
                                </div>
                                <div class="product-price">
                                    <span class="current-price"><?= formatCurrency($product['sale_price'] ?? $product['base_price']) ?></span>
                                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['base_price']): ?>
                                    <span class="original-price"><?= formatCurrency($product['base_price']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="bikes.php" class="btn btn-primary btn-lg">
                    View All Bikes <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="section features-section">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose VPORTAL?</h2>
                <p>We offer the best electric vehicles with unmatched features and service</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Zero Emissions</h3>
                    <p>100% electric vehicles that contribute to a cleaner, greener environment</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Fast Charging</h3>
                    <p>Quick charge technology gets you back on the road in no time</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3>Easy EMI Options</h3>
                    <p>Flexible financing with 0% interest on select models</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Extended Warranty</h3>
                    <p>Comprehensive warranty coverage for peace of mind</p>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($offers) || true): ?>
    <section class="offers-slider">
        <div class="container">
            <div class="offer-card">
                <div class="offer-content">
                    <div class="offer-discount">SAVE UP TO ₹15,000</div>
                    <h3>Festival Special Offer!</h3>
                    <p>Get exclusive discounts on all EV models. Limited time offer with free accessories worth ₹5,000.</p>
                    <a href="offers.php" class="btn btn-primary btn-lg" style="background: white; color: var(--secondary-color);">
                        View All Offers <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="offer-image">
                    <img src="assets/images/offer-bike.png" alt="Offer" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="section testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>What Our Customers Say</h2>
                <p>Real experiences from our valued customers</p>
            </div>
            
            <div class="testimonials-grid">
                <?php if (empty($testimonials)): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="https://via.placeholder.com/60" alt="Customer" class="testimonial-avatar">
                            <div class="testimonial-author">
                                <h4>Rahul Sharma</h4>
                                <p>Delhi, India</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Amazing experience! The EV bike exceeded my expectations. Great mileage and zero maintenance costs. Highly recommended!"</p>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="https://via.placeholder.com/60" alt="Customer" class="testimonial-avatar">
                            <div class="testimonial-author">
                                <h4>Priya Patel</h4>
                                <p>Mumbai, India</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Best investment I've made. The customer service is exceptional and the bike performs brilliantly. Love the eco-friendly aspect!"</p>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="https://via.placeholder.com/60" alt="Customer" class="testimonial-avatar">
                            <div class="testimonial-author">
                                <h4>Amit Kumar</h4>
                                <p>Bangalore, India</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text">"Smooth riding experience with great range. The pre-order process was seamless and delivery was on time. Very satisfied!"</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="<?= UPLOADS_URL ?>/avatars/<?= $testimonial['customer_image'] ?>" 
                                 alt="<?= htmlspecialchars($testimonial['customer_name']) ?>" 
                                 class="testimonial-avatar"
                                 onerror="this.src='https://via.placeholder.com/60'">
                            <div class="testimonial-author">
                                <h4><?= htmlspecialchars($testimonial['customer_name']) ?></h4>
                                <p><?= htmlspecialchars($testimonial['designation']) ?></p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <?php if ($i < $testimonial['rating']): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"<?= htmlspecialchars($testimonial['review']) ?>"</p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="navbar-brand">
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
                        <li><a href="bikes.php">EV Bikes</a></li>
                        <li><a href="compare.php">Compare Bikes</a></li>
                        <li><a href="offers.php">Offers</a></li>
                        <li><a href="preorder.php">Pre-Order</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="page.php?slug=about-us">About Us</a></li>
                        <li><a href="page.php?slug=terms-conditions">Terms & Conditions</a></li>
                        <li><a href="page.php?slug=privacy-policy">Privacy Policy</a></li>
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

    <a href="preorder.php" class="preorder-btn-sticky btn btn-primary btn-lg">
        <i class="fas fa-calendar-check"></i> Pre-Order Now
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
