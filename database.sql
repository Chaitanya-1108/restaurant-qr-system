-- -- ============================================
-- -- QR Code Restaurant Ordering System
-- -- InfinityFree Compatible Version
-- -- ============================================

-- -- CREATE DATABASE IF NOT EXISTS `restaurant_qr`;
-- -- USE `restaurant_qr`;

-- -- ============================================
-- -- TABLES
-- -- ============================================

-- CREATE TABLE IF NOT EXISTS `admins` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `username` VARCHAR(100) NOT NULL UNIQUE,
--   `password` VARCHAR(255) NOT NULL,
--   `name` VARCHAR(150) NOT NULL,
--   `reset_token` VARCHAR(100) DEFAULT NULL,
--   `token_expiry` DATETIME DEFAULT NULL,
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `tables` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `table_number` VARCHAR(20) NOT NULL UNIQUE,
--   `capacity` INT DEFAULT 4,
--   `status` ENUM('available','occupied') DEFAULT 'available',
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `categories` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `name` VARCHAR(100) NOT NULL,
--   `icon` VARCHAR(50) DEFAULT NULL,
--   `sort_order` INT DEFAULT 0,
--   `is_active` TINYINT(1) DEFAULT 1,
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `menu_items` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `category_id` INT NOT NULL,
--   `name` VARCHAR(200) NOT NULL,
--   `description` TEXT,
--   `price` DECIMAL(10,2) NOT NULL,
--   `image` VARCHAR(255) DEFAULT NULL,
--   `is_veg` TINYINT(1) DEFAULT 1,
--   `is_available` TINYINT(1) DEFAULT 1,
--   `is_featured` TINYINT(1) DEFAULT 0,
--   `sort_order` INT DEFAULT 0,
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `orders` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `order_number` VARCHAR(20) NOT NULL UNIQUE,
--   `table_id` INT NOT NULL,
--   `table_number` VARCHAR(20) NOT NULL,
--   `customer_name` VARCHAR(150) DEFAULT 'Guest',
--   `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
--   `payment_method` VARCHAR(50) DEFAULT 'Cash',
--   `payment_status` ENUM('Pending', 'Paid') DEFAULT 'Pending',
--   `status` ENUM('Pending','Preparing','Served','Completed','Cancelled') DEFAULT 'Pending',
--   `notes` TEXT,
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `order_items` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `order_id` INT NOT NULL,
--   `menu_item_id` INT NOT NULL,
--   `item_name` VARCHAR(200) NOT NULL,
--   `item_price` DECIMAL(10,2) NOT NULL,
--   `quantity` INT NOT NULL DEFAULT 1,
--   `notes` TEXT,
--   `subtotal` DECIMAL(10,2) NOT NULL
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `waiter_requests` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `table_id` INT NOT NULL,
--   `table_number` VARCHAR(20) NOT NULL,
--   `request_type` ENUM('Waiter', 'Bill', 'Water', 'Other') DEFAULT 'Waiter',
--   `status` ENUM('pending', 'completed') DEFAULT 'pending',
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- CREATE TABLE IF NOT EXISTS `feedback` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `order_id` INT DEFAULT NULL,
--   `customer_name` VARCHAR(150) DEFAULT 'Anonymous',
--   `rating` INT NOT NULL,
--   `comment` TEXT,
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=MyISAM;

-- -- ============================================
-- -- SAMPLE DATA
-- -- ============================================

-- INSERT INTO `admins` (`username`, `password`, `name`) VALUES
-- ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Restaurant Admin');

-- INSERT INTO `tables` (`table_number`, `capacity`) VALUES
-- ('T1', 2), ('T2', 4), ('T3', 4), ('T4', 6),
-- ('T5', 2), ('T6', 4), ('T7', 8), ('T8', 4),
-- ('T9', 2), ('T10', 6);

-- INSERT INTO `categories` (`name`, `icon`, `sort_order`) VALUES
-- ('Starters', '🥗', 1),
-- ('Main Course', '🍛', 2),
-- ('Breads', '🫓', 3),
-- ('Rice & Biryani', '🍚', 4),
-- ('Beverages', '🥤', 5),
-- ('Desserts', '🍮', 6),
-- ('Fast Food', '🍔', 7);

-- INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `is_veg`, `is_featured`) VALUES
-- (1, 'Paneer Tikka', 'Marinated cottage cheese grilled to perfection', 220.00, 1, 1),
-- (2, 'Butter Chicken', 'Creamy tomato-based curry', 350.00, 0, 1),
-- (4, 'Chicken Biryani', 'Fragrant basmati rice with chicken', 380.00, 0, 1),
-- (5, 'Mango Lassi', 'Refreshing yogurt drink', 120.00, 1, 1),
-- (6, 'Gulab Jamun', 'Milk dumplings in syrup', 120.00, 1, 1),
-- (7, 'Veg Burger', 'Crispy veg patty burger', 180.00, 1, 0);





-- ============================================
-- QR Code Restaurant Ordering System
-- InfinityFree Production Version
-- ============================================

-- ============================================
-- TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(150) NOT NULL,
  reset_token VARCHAR(100) DEFAULT NULL,
  token_expiry DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS tables (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_number VARCHAR(20) NOT NULL UNIQUE,
  capacity INT DEFAULT 4,
  status VARCHAR(20) DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  icon VARCHAR(50) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  is_veg TINYINT(1) DEFAULT 1,
  is_available TINYINT(1) DEFAULT 1,
  is_featured TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(20) NOT NULL UNIQUE,
  table_id INT NOT NULL,
  table_number VARCHAR(20) NOT NULL,
  customer_name VARCHAR(150) DEFAULT 'Guest',
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(50) DEFAULT 'Cash',
  payment_status VARCHAR(20) DEFAULT 'Pending',
  status VARCHAR(20) DEFAULT 'Pending',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT NOT NULL,
  item_name VARCHAR(200) NOT NULL,
  item_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  notes TEXT,
  subtotal DECIMAL(10,2) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS waiter_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_id INT NOT NULL,
  table_number VARCHAR(20) NOT NULL,
  request_type VARCHAR(20) DEFAULT 'Waiter',
  status VARCHAR(20) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT DEFAULT NULL,
  customer_name VARCHAR(150) DEFAULT 'Anonymous',
  rating INT NOT NULL,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- ============================================
-- SAMPLE DATA
-- ============================================

INSERT INTO admins (username, password, name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Restaurant Admin');

INSERT INTO tables (table_number, capacity) VALUES
('T1', 2), ('T2', 4), ('T3', 4), ('T4', 6),
('T5', 2), ('T6', 4), ('T7', 8), ('T8', 4),
('T9', 2), ('T10', 6);

INSERT INTO categories (name, icon, sort_order) VALUES
('Starters', '🥗', 1),
('Main Course', '🍛', 2),
('Breads', '🫓', 3),
('Rice & Biryani', '🍚', 4),
('Beverages', '🥤', 5),
('Desserts', '🍮', 6),
('Fast Food', '🍔', 7);

INSERT INTO menu_items (category_id, name, description, price, is_veg, is_featured) VALUES
(1, 'Paneer Tikka', 'Marinated cottage cheese grilled to perfection', 220.00, 1, 1),
(2, 'Butter Chicken', 'Creamy tomato-based curry', 350.00, 0, 1),
(4, 'Chicken Biryani', 'Fragrant basmati rice with chicken', 380.00, 0, 1),
(5, 'Mango Lassi', 'Refreshing yogurt drink', 120.00, 1, 1),
(6, 'Gulab Jamun', 'Milk dumplings in syrup', 120.00, 1, 1),
(7, 'Veg Burger', 'Crispy veg patty burger', 180.00, 1, 0);