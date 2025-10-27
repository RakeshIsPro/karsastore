# YBT Digital - Digital Product Selling Website

A comprehensive e-commerce platform for selling digital products built with PHP, MySQL, and modern web technologies.

## Features

### User Features
- **Authentication System**
  - User registration and login
  - Password reset functionality
  - Secure session management
  - Role-based access control

- **Product Browsing**
  - Responsive product catalog
  - Advanced search and filtering
  - Category-based navigation
  - Product detail pages with image galleries

- **Shopping Cart & Checkout**
  - Add/remove products from cart
  - Coupon code support
  - Secure checkout process
  - Multiple payment gateway integration (Stripe, PayPal, Razorpay)

- **Order Management**
  - Order history and tracking
  - Secure digital product downloads
  - Invoice generation
  - Email notifications

### Admin Features
- **Dashboard**
  - Sales analytics and reports
  - User management
  - Order tracking

- **Product Management**
  - Add/edit/delete products
  - Category management
  - File upload and management
  - Inventory tracking

- **Order Management**
  - View and process orders
  - Payment status tracking
  - Customer management

- **Settings**
  - Payment gateway configuration
  - Tax settings
  - Site customization

### Technical Features
- **Responsive Design**
  - Mobile-first approach
  - Desktop and tablet optimization
  - Native app-like mobile experience
  - Bottom navigation for mobile

- **Security**
  - SQL injection protection
  - XSS prevention
  - CSRF protection
  - Secure file handling
  - Rate limiting

- **Performance**
  - Optimized database queries
  - Image optimization
  - Lazy loading
  - Caching strategies

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/LAMP stack

### Setup Instructions

1. **Clone or Download**
   ```bash
   # Place files in your web server directory
   # For XAMPP: C:\xampp\htdocs\digital nest\
   ```

2. **Database Setup**
   - Start your MySQL server
   - The database will be created automatically on first run
   - Default database name: `ybt_digital`

3. **Configuration**
   - Update database credentials in `config/database.php` if needed
   - Configure payment gateway settings in admin panel

4. **Access the Application**
   - Frontend: `http://localhost/digital nest/`
   - Admin Panel: `http://localhost/digital nest/admin/`

### Default Admin Account
- **Email:** admin@ybtdigital.com
- **Password:** admin123

## File Structure

```
digital nest/
├── admin/                  # Admin panel
│   ├── index.php          # Admin dashboard
│   └── login.php          # Admin login
├── api/                   # API endpoints
│   ├── cart.php           # Cart operations
│   ├── products.php       # Product data
│   ├── search.php         # Search functionality
│   └── coupon.php         # Coupon operations
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── main.js        # Main JavaScript
│   └── images/            # Image assets
├── auth/                  # Authentication
│   ├── login.php          # User login
│   ├── signup.php         # User registration
│   └── logout.php         # Logout handler
├── config/
│   └── database.php       # Database configuration
├── includes/
│   └── functions.php      # Core functions
├── index.php              # Homepage
├── products.php           # Product listing
├── product.php            # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout process
├── payment.php            # Payment processing
├── order-success.php      # Order confirmation
├── orders.php             # User orders
└── README.md              # This file
```

## Database Schema

### Core Tables
- **users** - User accounts and profiles
- **products** - Product catalog
- **categories** - Product categories
- **orders** - Order information
- **order_items** - Order line items
- **cart** - Shopping cart items
- **coupons** - Discount codes
- **settings** - System configuration

## Payment Gateway Integration

### Supported Gateways
1. **Stripe** - Credit/Debit cards
2. **PayPal** - PayPal payments
3. **Razorpay** - Indian payment methods

### Configuration
Update payment gateway settings in the admin panel:
- Navigate to Admin → Settings
- Enter your API keys and credentials
- Test with sandbox/test modes first

## Customization

### Styling
- Main CSS file: `assets/css/style.css`
- Uses MDBootstrap for UI components
- CSS custom properties for theming
- Responsive breakpoints included

### Functionality
- Core functions in `includes/functions.php`
- API endpoints in `api/` directory
- Modular structure for easy extension

## Security Features

### Implemented Security Measures
- **Input Sanitization** - All user inputs are sanitized
- **Prepared Statements** - SQL injection prevention
- **CSRF Protection** - Cross-site request forgery prevention
- **Rate Limiting** - Brute force attack prevention
- **Secure Sessions** - Session hijacking prevention
- **File Upload Security** - Secure file handling

### Best Practices
- Regular security updates
- Strong password policies
- HTTPS in production
- Regular backups
- Error logging

## Mobile Experience

### Features
- **Responsive Design** - Works on all screen sizes
- **Bottom Navigation** - Native app-like navigation
- **Touch Optimized** - Large touch targets
- **Fast Loading** - Optimized for mobile networks
- **Offline Support** - Basic offline functionality

### Performance
- Lazy loading images
- Minified CSS/JS
- Optimized database queries
- Caching strategies

## Development

### Adding New Features
1. Follow the existing code structure
2. Use prepared statements for database queries
3. Implement proper error handling
4. Add input validation and sanitization
5. Test on multiple devices and browsers

### API Development
- RESTful API design
- JSON responses
- Proper HTTP status codes
- Error handling and logging

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL service is running
   - Verify database credentials
   - Ensure database exists

2. **File Upload Issues**
   - Check PHP upload limits
   - Verify folder permissions
   - Ensure adequate disk space

3. **Payment Gateway Issues**
   - Verify API credentials
   - Check sandbox/live mode settings
   - Review error logs

### Debug Mode
Enable debug mode by setting error reporting in PHP:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

### Getting Help
- Check the FAQ section
- Review error logs
- Contact support team

### Contributing
1. Fork the repository
2. Create feature branch
3. Make changes with proper testing
4. Submit pull request

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Complete e-commerce functionality
- Admin panel
- Payment gateway integration
- Mobile responsive design

---

**YBT Digital** - Premium Digital Products Platform
Built with ❤️ using PHP, MySQL, and modern web technologies.
