# VPORTAL - EV Bike Showroom & E-Commerce Management System

A complete production-ready EV Bike showroom and e-commerce management system built with PHP + MySQL for XAMPP localhost.

## Features

### Admin Panel
- **Dashboard**: Overview of total bikes, stock, pending preorders, revenue
- **Product Management**: Add/Edit/Delete EV bikes with variants, images, specifications
- **Orders & Preorders**: View, approve/reject preorders, update status
- **Billing System**: Create invoices with auto GST calculation, generate PDF
- **Offers & Notifications**: Create promotions, send notifications
- **Content Management**: Banners, pages, gallery, testimonials
- **Support System**: Manage complaints and customer inquiries

### Customer Website
- **Home Page**: Featured bikes, offers slider, testimonials
- **Bike Listing**: Browse with filters and sorting
- **Bike Details**: Full specifications, variants, 360° gallery
- **Compare Bikes**: Side-by-side comparison up to 4 bikes
- **User Dashboard**: Track preorders, orders, wishlist
- **Pre-Order System**: Easy preorder with delivery address
- **Contact & Support**: Customer inquiry form

## Installation on XAMPP

### Step 1: Setup Files
1. Copy the entire project folder to `C:\xampp\htdocs\vportal`
2. Make sure XAMPP Apache and MySQL services are running

### Step 2: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `vportal_ev`
3. Import `DATABASE.sql` file into the database
4. Or run the SQL file directly via phpMyAdmin Import feature

### Step 3: Configure Database Connection
Edit `include/config.php` if needed:
```php
$db_host = "localhost";
$db_name = "vportal_ev";
$db_user = "root";
$db_pass = "";  // Your MySQL password if any
```

### Step 4: Set URL Path
Edit `include/config.php` and update:
```php
define('SITE_URL', 'http://localhost/vportal');
```

### Step 5: Create Upload Directories
Make sure these directories exist and are writable:
- `assets/uploads/products/`
- `assets/uploads/banners/`
- `assets/uploads/gallery/`
- `assets/uploads/avatars/`

## Access URLs

- **Website**: `http://localhost/vportal/`
- **Admin Panel**: `http://localhost/vportal/admin/`

## Default Login Credentials

### Admin
- **Username**: `admin`
- **Password**: `admin123`

## Project Structure

```
vportal/
├── admin/                  # Admin panel
│   ├── includes/           # Admin header, sidebar
│   ├── index.php           # Dashboard
│   ├── login.php           # Admin login
│   ├── products.php        # Product management
│   ├── preorders.php       # Preorder management
│   ├── billing.php         # Invoice creation
│   └── ...
├── assets/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   ├── images/             # Static images
│   └── uploads/            # User uploaded files
├── include/
│   └── config.php          # Database configuration
├── includes/
│   ├── functions.php       # Helper functions
│   ├── navbar.php          # Website navbar
│   └── footer.php          # Website footer
├── user/                   # User dashboard pages
├── index.php               # Homepage
├── bikes.php               # Bike listing
├── bike-details.php        # Single bike page
├── compare.php             # Compare bikes
├── preorder.php            # Preorder form
├── offers.php              # Offers page
├── contact.php             # Contact form
├── login.php               # User login
├── register.php            # User registration
├── DATABASE.sql            # Database schema
└── README.md               # This file
```

## Technologies Used

- **Backend**: PHP 8.x
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.4
- **Fonts**: Google Fonts (Inter)

## Key Features

### GST Billing
- Auto-calculation of CGST and SGST
- Configurable tax rates
- Invoice generation with all tax details

### Preorder System
- Customer preorder placement
- Admin approval workflow
- Status tracking (Pending → Approved → In Progress → Ready → Delivered)
- Customer notifications

### Product Management
- Multiple product images
- Color variants with price differences
- EMI options
- Stock management

## Customization

### Change Theme Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #00d4ff;
    --secondary-color: #7c3aed;
    --accent-color: #10b981;
}
```

### Update Settings
Access Admin Panel → Settings to update:
- Site name, email, phone
- GST number and rates
- SMS/Email API settings

## Support

For any issues or customization requests, please refer to the admin panel or contact the developer.

## License

This project is provided as-is for educational and commercial use.
