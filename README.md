# 🍽️ QR Code Restaurant Ordering System

A complete, production-ready restaurant ordering system where customers scan QR codes at their table to browse the menu and place orders — no app download required.

---

## 📁 Folder Structure

```
restaurant-qr-system/
├── index.php                    # Customer menu page (entry point)
├── order_confirmation.php       # Order success page
├── .htaccess                    # Apache security & performance rules
├── database.sql                 # Full database schema + sample data
│
├── config/
│   ├── database.php             # DB connection (PDO singleton)
│   └── app.php                  # Bootstrap, helpers, autoloader
│
├── app/
│   ├── Models/
│   │   ├── MenuModel.php        # Menu items & categories CRUD
│   │   ├── OrderModel.php       # Orders, tables, sales data
│   │   └── AdminModel.php       # Admin authentication
│   └── Views/
│       └── admin/
│           └── sidebar.php      # Admin sidebar partial
│
├── admin/
│   ├── login.php                # Admin login page
│   ├── logout.php               # Session destroy
│   ├── dashboard.php            # Stats, charts, recent orders
│   ├── orders.php               # Live orders (auto-refresh)
│   ├── menu_items.php           # Menu CRUD with image upload
│   ├── categories.php           # Category management
│   ├── tables.php               # Tables + QR code generation
│   └── sales.php                # Daily sales report
│
├── api/
│   ├── place_order.php          # POST: Place customer order
│   ├── update_order_status.php  # POST: Admin status update
│   └── get_orders.php           # GET: Live orders (admin)
│
├── public/
│   ├── css/
│   │   └── style.css            # Complete stylesheet
│   ├── js/                      # (Reserved for future JS modules)
│   └── images/                  # Static images
│
└── uploads/
    └── menu/                    # Uploaded menu item images
```

---

## ⚙️ Setup Instructions

### Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- XAMPP / WAMP / LAMP / any PHP web server

### Step 1: Copy Files
Place the `restaurant-qr-system` folder inside your web server's root:
- XAMPP: `C:/xampp/htdocs/restaurant-qr-system/`
- WAMP: `C:/wamp64/www/restaurant-qr-system/`

### Step 2: Create Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import** → Choose `database.sql`
3. Click **Go**

This creates the `restaurant_qr` database with all tables and sample data.

### Step 3: Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'restaurant_qr');
define('BASE_URL', 'http://localhost/restaurant-qr-system');
define('SITE_NAME', 'Spice Garden Restaurant');
```

### Step 4: Set Permissions
Make sure the `uploads/menu/` folder is writable:
```bash
chmod 755 uploads/menu/
```

### Step 5: Access the System

| URL | Description |
|-----|-------------|
| `http://localhost/restaurant-qr-system/` | Customer menu (no table selected) |
| `http://localhost/restaurant-qr-system/?table=T1` | Menu for Table T1 |
| `http://localhost/restaurant-qr-system/admin/login.php` | Admin panel login |

---

## 🔐 Admin Credentials

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |

> ⚠️ **Change the password immediately** after first login via phpMyAdmin or by updating the hash in the `admins` table.

To generate a new password hash (PHP):
```php
echo password_hash('your_new_password', PASSWORD_BCRYPT);
```

---

## 📱 How QR Codes Work

1. Go to **Admin → Tables & QR Codes**
2. Each table shows a QR code generated via [QR Server API](https://goqr.me/api/)
3. The QR code encodes the URL: `BASE_URL/?table=TABLE_NUMBER`
4. Click **Download** to save the QR code as PNG
5. Print and laminate for each table

---

## 🛒 Customer Flow

1. Customer scans QR code → Opens menu page for their table
2. Browse categories, search items, filter by type
3. Add items to cart → Adjust quantities
4. Enter name (optional) + special instructions
5. Click **Place Order** → Order saved to database
6. Confirmation page shows order number + status

---

## 👨‍💼 Admin Features

| Feature | Description |
|---------|-------------|
| Dashboard | Live stats, revenue chart, top items |
| Live Orders | Auto-refreshes every 15s, one-click status updates |
| Menu Items | Add/Edit/Delete with image upload |
| Categories | Manage with emoji icons |
| Tables & QR | Generate, preview, download QR codes |
| Sales Report | Daily summary, 7-day chart, order status breakdown |

---

## 🔒 Security Features

- PDO prepared statements (SQL injection prevention)
- Server-side price validation (prevents cart tampering)
- Password hashing with `password_hash()` / `password_verify()`
- Session-based admin authentication
- Input sanitization with `htmlspecialchars()`
- File upload validation (type + size)
- `Options -Indexes` in `.htaccess`

---

## 🎨 Customization

### Change Restaurant Name
In `config/database.php`:
```php
define('SITE_NAME', 'Your Restaurant Name');
```

### Change Currency
```php
define('CURRENCY', '$');  // or '€', '£', etc.
```

### Change Tax Rate
```php
define('TAX_PERCENT', 18);  // 18% GST
```

### Change Colors
In `public/css/style.css`, update the `:root` variables:
```css
--primary: #e8521a;      /* Main brand color */
--accent: #f5a623;       /* Accent/highlight color */
```

---

## 📊 Database Tables

| Table | Purpose |
|-------|---------|
| `admins` | Admin user accounts |
| `tables` | Restaurant tables with status |
| `categories` | Menu categories with icons |
| `menu_items` | Food items with pricing |
| `orders` | Customer orders |
| `order_items` | Individual items per order |

---

## 🚀 Production Deployment

1. Set `BASE_URL` to your actual domain
2. Use a strong MySQL password
3. Change admin password
4. Enable HTTPS (SSL certificate)
5. Set proper file permissions
6. Configure PHP error logging (disable display_errors)
7. Set up regular database backups

---

Made with ❤️ | Modern Restaurant QR Ordering System
