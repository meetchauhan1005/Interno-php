# INTERNO - Modern E-commerce Website

A professional, modern e-commerce website built with PHP and designed to work with XAMPP.

## Features

### Frontend Features
- **Modern Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Product Catalog** - Browse products with search, filtering, and pagination
- **Product Details** - Detailed product pages with related products
- **Shopping Cart** - Add/remove items, update quantities
- **User Authentication** - Register, login, logout functionality
- **Categories** - Organized product categories
- **Professional UI** - Clean, modern interface with smooth animations

### Backend Features
- **Admin Dashboard** - Complete admin panel with statistics
- **Product Management** - Add, edit, delete products
- **Order Management** - Track and manage customer orders
- **User Management** - Manage customer accounts
- **Category Management** - Organize products into categories
- **Inventory Tracking** - Stock quantity management
- **Secure Authentication** - Password hashing and session management

### Technical Features
- **PHP 7.4+** - Modern PHP with PDO for database operations
- **MySQL Database** - Relational database with proper structure
- **AJAX Integration** - Dynamic cart updates without page refresh
- **Responsive CSS** - Mobile-first design approach
- **Font Awesome Icons** - Professional iconography
- **Security** - SQL injection prevention, XSS protection

## Installation Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser
- Text editor (optional, for customization)

### Setup Steps

1. **Install XAMPP**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Start Apache and MySQL services

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database:
     - Click "Import" tab
     - Choose file: `database.sql`
     - Click "Go" to import

3. **Configure Application**
   - The application is already configured for XAMPP defaults
   - Database settings in `includes/config.php`:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: interno_ecommerce

4. **Access the Website**
   - Open your browser
   - Go to: `http://localhost/interno-php/`

### Default Admin Account
- **Username:** admin
- **Password:** password

## File Structure

```
interno-php/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # JavaScript functionality
│   └── images/                # Product images (create this folder)
├── includes/
│   ├── config.php             # Database configuration
│   ├── header.php             # Header template
│   ├── footer.php             # Footer template
│   └── cart_handler.php       # AJAX cart operations
├── user/
│   ├── login.php              # User login
│   ├── register.php           # User registration
│   ├── logout.php             # Logout functionality
│   └── cart.php               # Shopping cart page
├── admin/
│   └── dashboard.php          # Admin dashboard
├── index.php                  # Homepage
├── products.php               # Product listing
├── product_detail.php         # Product details
├── categories.php             # Category listing
├── about.php                  # About page
├── contact.php                # Contact page
├── database.sql               # Database schema
└── README.md                  # This file
```

## Usage Guide

### For Customers
1. **Browse Products** - Visit the homepage or products page
2. **Search & Filter** - Use search bar and category filters
3. **View Details** - Click on any product for detailed information
4. **Create Account** - Register for a new account
5. **Add to Cart** - Add products to your shopping cart
6. **Manage Cart** - Update quantities or remove items
7. **Contact Support** - Use the contact form for inquiries

### For Administrators
1. **Login as Admin** - Use admin credentials to access dashboard
2. **View Statistics** - Monitor sales, users, and inventory
3. **Manage Products** - Add, edit, or remove products
4. **Process Orders** - View and update order status
5. **Manage Users** - View customer accounts
6. **Update Categories** - Organize product categories

## Customization

### Styling Changes
- Edit `assets/css/style.css` for visual customizations
- Colors, fonts, and layouts can be easily modified

### Adding Features
- The codebase is modular and easy to extend
- Add new pages by following existing file patterns
- Database schema can be extended as needed

## Security Features

- **Password Hashing** - All passwords are securely hashed
- **SQL Injection Prevention** - PDO prepared statements
- **XSS Protection** - Input sanitization and output escaping
- **Session Management** - Secure session handling
- **Input Validation** - Server-side validation for all forms

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers

## Support

For technical support or questions:
- Check the contact page for support options
- Review the code comments for implementation details
- Modify `includes/config.php` for database connection issues

## License

This project is created for educational and demonstration purposes.

---

**INTERNO** - Modern E-commerce Made Simple