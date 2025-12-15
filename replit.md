# VPORTAL - EV Bike Showroom & E-Commerce Management System

## Project Overview
A complete production-ready EV Bike showroom and e-commerce management system built with PHP + MySQL for XAMPP localhost.

## Key Features
- **Admin Panel**: Dashboard, Product Management, Orders/Preorders, Billing with GST, Offers, Content Management, Support System
- **Customer Website**: Home, Bike Listing, Bike Details, Compare, Pre-Order, User Dashboard, Gallery, Contact

## Technologies
- Backend: PHP 8.x
- Database: MySQL / MariaDB
- Frontend: HTML5, CSS3, JavaScript, Bootstrap 5.3
- Icons: Font Awesome 6.4
- Fonts: Google Fonts (Inter)

## Project Structure
```
vportal/
├── admin/                  # Admin panel (all files use light-theme)
│   ├── includes/           # Admin header, sidebar
│   ├── index.php           # Dashboard
│   ├── login.php           # Admin login
│   ├── products.php        # Product management
│   └── ...other admin pages
├── assets/
│   ├── css/                # style.css (light theme), admin.css
│   ├── js/                 # main.js, admin.js
│   └── uploads/            # products/, banners/, gallery/, avatars/
├── include/
│   └── config.php          # Database configuration
├── includes/
│   └── functions.php       # Helper functions
├── user/                   # User dashboard pages
├── index.php               # Homepage
├── DATABASE.sql            # Database schema
└── README.md
```

## Installation (XAMPP)
1. Copy project to `C:\xampp\htdocs\vportal`
2. Create database `vportal_ev` in phpMyAdmin
3. Import `DATABASE.sql`
4. Update `include/config.php` if needed

## Default Credentials
- **Admin**: username: `admin`, password: `admin123`
- **Website**: `http://localhost/vportal/`
- **Admin Panel**: `http://localhost/vportal/admin/`

## Theme
- Clean **light theme** applied to all pages
- Primary color: #2563eb (blue)
- Secondary color: #7c3aed (purple)

## Development Notes
- All upload directories created: products, banners, gallery, avatars
- PHP 8.2 installed for local development/testing
- For XAMPP: No changes needed, just copy and import database
