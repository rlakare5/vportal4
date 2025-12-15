-- VPORTAL EV Bike Showroom & E-Commerce Management System
-- Database Schema for MySQL
-- Run this file in phpMyAdmin or MySQL CLI

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `vportal_ev` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `vportal_ev`;

-- --------------------------------------------------------
-- Table structure for `admins`
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default.png',
  `role` ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
  `status` TINYINT(1) DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin / admin123
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@vportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin');

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default.png',
  `email_verified` TINYINT(1) DEFAULT 0,
  `phone_verified` TINYINT(1) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `products` (EV Bikes)
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `brand` VARCHAR(100) DEFAULT NULL,
  `model` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `base_price` DECIMAL(12,2) NOT NULL,
  `sale_price` DECIMAL(12,2) DEFAULT NULL,
  `battery_capacity` VARCHAR(50) DEFAULT NULL,
  `motor_power` VARCHAR(50) DEFAULT NULL,
  `max_range` VARCHAR(50) DEFAULT NULL,
  `top_speed` VARCHAR(50) DEFAULT NULL,
  `charging_time` VARCHAR(50) DEFAULT NULL,
  `weight` VARCHAR(50) DEFAULT NULL,
  `warranty` VARCHAR(100) DEFAULT NULL,
  `category` ENUM('scooter', 'bike', 'cycle') DEFAULT 'scooter',
  `stock_quantity` INT(11) DEFAULT 0,
  `emi_available` TINYINT(1) DEFAULT 0,
  `emi_months` VARCHAR(100) DEFAULT NULL,
  `featured` TINYINT(1) DEFAULT 0,
  `status` ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
  `views` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `variants`
-- --------------------------------------------------------
CREATE TABLE `variants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `variant_name` VARCHAR(100) NOT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `color_code` VARCHAR(20) DEFAULT NULL,
  `price_difference` DECIMAL(10,2) DEFAULT 0.00,
  `stock_quantity` INT(11) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `product_images`
-- --------------------------------------------------------
CREATE TABLE `product_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `variant_id` INT(11) DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `image_type` ENUM('main', 'gallery', '360') DEFAULT 'gallery',
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `accessories`
-- --------------------------------------------------------
CREATE TABLE `accessories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `stock_quantity` INT(11) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `accessories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `wishlist`
-- --------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `inquiries`
-- --------------------------------------------------------
CREATE TABLE `inquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `status` ENUM('pending', 'replied', 'closed') DEFAULT 'pending',
  `admin_reply` TEXT DEFAULT NULL,
  `replied_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `preorders`
-- --------------------------------------------------------
CREATE TABLE `preorders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `variant_id` INT(11) DEFAULT NULL,
  `quantity` INT(11) DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `advance_amount` DECIMAL(12,2) DEFAULT 0.00,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `expected_delivery` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'in_progress', 'ready', 'delivered', 'cancelled', 'rejected') DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `preorders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `preorders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `orders`
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `preorder_id` INT(11) DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `service_charge` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'upi', 'emi', 'bank_transfer') DEFAULT 'cash',
  `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
  `order_status` ENUM('pending', 'confirmed', 'processing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
  `delivery_address` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `preorder_id` (`preorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `order_items`
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `variant_id` INT(11) DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `variant_name` VARCHAR(100) DEFAULT NULL,
  `quantity` INT(11) DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `total_price` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `invoices`
-- --------------------------------------------------------
CREATE TABLE `invoices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL,
  `order_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(100) DEFAULT NULL,
  `customer_phone` VARCHAR(20) DEFAULT NULL,
  `customer_address` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `cgst_percent` DECIMAL(5,2) DEFAULT 9.00,
  `cgst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `sgst_percent` DECIMAL(5,2) DEFAULT 9.00,
  `sgst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `igst_percent` DECIMAL(5,2) DEFAULT 0.00,
  `igst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `service_charge` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `amount_paid` DECIMAL(12,2) DEFAULT 0.00,
  `balance_due` DECIMAL(12,2) DEFAULT 0.00,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_status` ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
  `notes` TEXT DEFAULT NULL,
  `pdf_path` VARCHAR(255) DEFAULT NULL,
  `emailed` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `payment_id` VARCHAR(100) NOT NULL,
  `order_id` INT(11) DEFAULT NULL,
  `invoice_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'upi', 'emi', 'bank_transfer') DEFAULT 'cash',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_id` (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `offers`
-- --------------------------------------------------------
CREATE TABLE `offers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `offer_type` ENUM('percentage', 'fixed', 'freebie') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) DEFAULT 0.00,
  `min_purchase` DECIMAL(12,2) DEFAULT 0.00,
  `max_discount` DECIMAL(10,2) DEFAULT NULL,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `applicable_to` ENUM('all', 'specific_product', 'category') DEFAULT 'all',
  `category` VARCHAR(50) DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `usage_limit` INT(11) DEFAULT NULL,
  `times_used` INT(11) DEFAULT 0,
  `banner_image` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_code` (`coupon_code`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `notifications`
-- --------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('email', 'sms', 'push', 'system') DEFAULT 'system',
  `category` ENUM('order', 'preorder', 'offer', 'announcement', 'system') DEFAULT 'system',
  `reference_id` INT(11) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `sent_at` DATETIME DEFAULT NULL,
  `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `system_logs`
-- --------------------------------------------------------
CREATE TABLE `system_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `log_type` ENUM('info', 'warning', 'error', 'debug') DEFAULT 'info',
  `module` VARCHAR(100) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `admin_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `admin_activity`
-- --------------------------------------------------------
CREATE TABLE `admin_activity` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `reference_id` INT(11) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_activity_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `global_settings`
-- --------------------------------------------------------
CREATE TABLE `global_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('text', 'number', 'boolean', 'json', 'file') DEFAULT 'text',
  `category` VARCHAR(50) DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings
INSERT INTO `global_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('site_name', 'VPORTAL EV Showroom', 'text', 'general', 'Website name'),
('site_email', 'info@vportal.com', 'text', 'general', 'Contact email'),
('site_phone', '+91 9876543210', 'text', 'general', 'Contact phone'),
('site_address', '123 EV Street, Tech City, India', 'text', 'general', 'Showroom address'),
('currency', 'INR', 'text', 'general', 'Currency code'),
('currency_symbol', '₹', 'text', 'general', 'Currency symbol'),
('gst_number', 'GSTIN1234567890', 'text', 'billing', 'GST Number'),
('cgst_rate', '9', 'number', 'billing', 'CGST Rate %'),
('sgst_rate', '9', 'number', 'billing', 'SGST Rate %'),
('sms_api_key', '', 'text', 'notifications', 'SMS API Key'),
('sms_sender_id', '', 'text', 'notifications', 'SMS Sender ID'),
('email_smtp_host', '', 'text', 'notifications', 'SMTP Host'),
('email_smtp_port', '587', 'number', 'notifications', 'SMTP Port'),
('email_smtp_user', '', 'text', 'notifications', 'SMTP Username'),
('email_smtp_pass', '', 'text', 'notifications', 'SMTP Password');

-- --------------------------------------------------------
-- Table structure for `banners`
-- --------------------------------------------------------
CREATE TABLE `banners` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) DEFAULT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(50) DEFAULT NULL,
  `position` ENUM('home_slider', 'home_banner', 'offer_banner', 'category_banner') DEFAULT 'home_slider',
  `sort_order` INT(11) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `pages`
-- --------------------------------------------------------
CREATE TABLE `pages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `meta_title` VARCHAR(200) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default pages
INSERT INTO `pages` (`title`, `slug`, `content`, `status`) VALUES
('About Us', 'about-us', '<h2>Welcome to VPORTAL EV Showroom</h2><p>We are the leading electric vehicle showroom dedicated to providing eco-friendly transportation solutions. Our mission is to make electric mobility accessible to everyone.</p><h3>Our Vision</h3><p>To be the premier destination for electric vehicles, offering the best selection, service, and support for our customers.</p>', 1),
('Terms & Conditions', 'terms-conditions', '<h2>Terms and Conditions</h2><p>By using our website and services, you agree to these terms...</p>', 1),
('Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Your privacy is important to us...</p>', 1);

-- --------------------------------------------------------
-- Table structure for `gallery`
-- --------------------------------------------------------
CREATE TABLE `gallery` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT 'showroom',
  `sort_order` INT(11) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `testimonials`
-- --------------------------------------------------------
CREATE TABLE `testimonials` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_image` VARCHAR(255) DEFAULT 'default.png',
  `designation` VARCHAR(100) DEFAULT NULL,
  `rating` INT(1) DEFAULT 5,
  `review` TEXT NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `complaints`
-- --------------------------------------------------------
CREATE TABLE `complaints` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `order_id` INT(11) DEFAULT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
  `admin_notes` TEXT DEFAULT NULL,
  `resolution` TEXT DEFAULT NULL,
  `assigned_to` INT(11) DEFAULT NULL,
  `resolved_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `themes`
-- --------------------------------------------------------
CREATE TABLE `themes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `primary_color` VARCHAR(20) DEFAULT '#00d4ff',
  `secondary_color` VARCHAR(20) DEFAULT '#7c3aed',
  `accent_color` VARCHAR(20) DEFAULT '#10b981',
  `dark_mode` TINYINT(1) DEFAULT 0,
  `custom_css` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default theme
INSERT INTO `themes` (`name`, `primary_color`, `secondary_color`, `accent_color`, `dark_mode`, `is_active`) VALUES
('Default EV Theme', '#00d4ff', '#7c3aed', '#10b981', 0, 1);

COMMIT;
