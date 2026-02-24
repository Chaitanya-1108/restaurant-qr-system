<?php
// ============================================
// Database Configuration
// ============================================

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurant_qr');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306');

define('BASE_URL', 'http://localhost/restaurant-qr-system');
define('SITE_NAME', 'Spice Garden Restaurant');
define('CURRENCY', '₹');
define('TAX_PERCENT', 5); // GST %
define('UPI_ID', 'SpiceGarden@okicici'); // Your Merchant UPI ID
define('UPI_NAME', 'Spice Garden Restaurant');

// Payment Gateway Settings (Razorpay)
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_HERE');
define('RAZORPAY_KEY_SECRET', 'YOUR_SECRET_HERE');
define('ENABLE_ONLINE_PAYMENT', false); // Set to true after adding keys

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
