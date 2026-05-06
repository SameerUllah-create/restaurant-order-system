# Restaurant Order System

A simple PHP-based restaurant order management system with customer and admin interfaces.

## Features

### Customer Side
- Browse food items from the menu
- Add items to shopping cart
- Adjust quantities
- Checkout with delivery details
- Place orders with order confirmation
- View order summary and details

### Admin Side
- Admin login authentication
- Dashboard with order statistics
- View all orders
- Update order status (Pending, Preparing, Delivered, Cancelled)
- Manage food items (add, edit, delete)
- Filter orders by status

## Technology Stack

- **Backend**: PHP 8.x
- **Database**: MySQL
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Server**: Apache (XAMPP)

## Project Structure

```
restaurant-order-system/
├── admin/
│   ├── dashboard.php           # Admin dashboard
│   ├── order-management.php    # Order management interface
│   ├── food-management.php     # Food items management
│   └── logout.php              # Admin logout
├── includes/
│   ├── config.php              # Database configuration
│   ├── header.php              # Header template
│   └── footer.php              # Footer template
├── assets/
│   └── images/                 # Food images
├── admin_login.php             # Admin login page
├── index.php                   # Customer homepage
├── cart.php                    # Shopping cart
├── checkout.php                # Checkout page
├── place-order.php             # Order processing
└── success.php                 # Order success page
```

## Database Schema

### Tables

1. **admins** - Admin user accounts
2. **foods** - Food menu items
3. **orders** - Customer orders
4. **order_items** - Items in each order

## Installation

1. **Prerequisites**
   - XAMPP or similar local server setup
   - PHP 7.4+
   - MySQL 5.7+

2. **Setup Steps**
   ```bash
   # Navigate to project directory
   cd c:\xampp\htdocs\restaurant-order-system
   
   # Initialize database
   # Import the database tables (create them using setup files)
   ```

3. **Create Admin Account**
   - Visit: `http://localhost/restaurant-order-system/init.php`
   - This will create a default admin account:
     - Username: `admin`
     - Password: `admin123`

4. **Start Using**
   - Customer page: `http://localhost/restaurant-order-system/`
   - Admin login: `http://localhost/restaurant-order-system/admin_login.php`

## Usage

### For Customers
1. Browse food items on the homepage
2. Add items to cart with desired quantity
3. Go to checkout
4. Enter delivery details
5. Place order
6. View order confirmation

### For Admin
1. Login with admin credentials
2. View dashboard statistics
3. Manage orders (view, update status)
4. Manage food items (add, edit, delete)

## Database Configuration

Edit `includes/config.php` to configure database connection:

```php
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'restaurant_db';
```

## Default Login

**Admin Credentials:**
- Username: `admin`
- Password: `admin123`

## Security Notes

- Passwords are hashed using bcrypt
- SQL queries use prepared statements to prevent SQL injection
- Input validation is implemented
- Session-based authentication for admin

## Features in Development

- Customer account registration and login
- Order tracking
- Payment gateway integration
- Email notifications
- Customer reviews and ratings

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code comments or review the database structure.

---

**Author**: Sameer Ullah  
**Version**: 1.0  
**Last Updated**: May 2026
