# YBT Digital - Quick Setup Guide

## 🚀 Quick Start (5 Minutes)

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Ensure both services are running (green indicators)

### Step 2: Access the Website
1. Open your browser
2. Go to: `http://localhost/digital nest/`
3. The database will be created automatically on first visit

### Step 3: Add Sample Data
1. Visit: `http://localhost/digital nest/sample-data.php`
2. This will populate the database with demo products
3. Create placeholder images: `http://localhost/digital nest/create_placeholder.php`

### Step 4: Admin Access
1. Go to: `http://localhost/digital nest/admin/`
2. **Login credentials:**
   - Email: `admin@ybtdigital.com`
   - Password: `admin123`

## 🎯 Test the Features

### User Features
1. **Browse Products**: Visit the products page and test filtering
2. **Create Account**: Sign up as a new user
3. **Add to Cart**: Add products to cart and test checkout
4. **Demo Payment**: Use the demo payment system
5. **Download Products**: Test the download functionality

### Admin Features
1. **Dashboard**: View sales statistics and recent orders
2. **Product Management**: Add/edit products (coming in admin panel)
3. **User Management**: View registered users
4. **Orders**: Track and manage customer orders

## 🛠️ Configuration

### Payment Gateways
To enable real payment processing:
1. Go to Admin → Settings
2. Add your API keys for:
   - Stripe (for credit cards)
   - PayPal (for PayPal payments)
   - Razorpay (for Indian payments)

### Email Settings
Configure SMTP settings in `includes/functions.php` for:
- Order confirmations
- Password resets
- Support tickets

## 📱 Mobile Testing

The website is fully responsive. Test on:
- Mobile devices (native app-like experience)
- Tablets (optimized layout)
- Desktop (full feature set)

## 🔧 Troubleshooting

### Database Issues
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Database `ybt_digital` is created automatically

### File Permissions
- Ensure `uploads/` directory is writable
- Check file permissions for image uploads

### Sample Data
- Run `sample-data.php` only once
- Delete existing products before re-running if needed

## 🎨 Customization

### Styling
- Main CSS: `assets/css/style.css`
- Uses MDBootstrap for UI components
- Dark/light mode toggle included

### Branding
- Update logo and site name in navigation
- Modify colors using CSS custom properties
- Change footer information

## 📊 Features Included

✅ **User Management**
- Registration/Login/Logout
- Profile management
- Password reset

✅ **Product Catalog**
- Product listing with filters
- Search functionality
- Category-based browsing
- Product detail pages

✅ **Shopping Cart**
- Add/remove products
- Coupon code support
- Secure checkout

✅ **Payment Processing**
- Multiple payment gateways
- Order confirmation
- Email notifications

✅ **Digital Downloads**
- Secure file delivery
- Download tracking
- Order history

✅ **Admin Panel**
- Dashboard with analytics
- User management
- Order tracking
- Basic product management

✅ **Mobile Responsive**
- Mobile-first design
- Bottom navigation
- Touch-optimized interface

## 🚀 Going Live

### For Production Deployment:
1. Update database credentials
2. Configure real payment gateway keys
3. Set up SSL certificate
4. Configure email SMTP settings
5. Update file upload limits
6. Enable error logging
7. Set up regular backups

## 📞 Support

For issues or questions:
- Check the FAQ section on the website
- Review the main README.md file
- Contact support through the built-in contact form

---

**Congratulations!** 🎉 Your YBT Digital marketplace is ready to use!

Visit `http://localhost/digital nest/` to start exploring.
