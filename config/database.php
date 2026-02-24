// <?php
// // ============================================
// // Database Configuration
// // ============================================

// define('DB_HOST', '127.0.0.1');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'restaurant_qr');
// define('DB_CHARSET', 'utf8mb4');
// define('DB_PORT', '3306');

// define('BASE_URL', 'http://localhost/restaurant-qr-system');
// define('SITE_NAME', 'Spice Garden Restaurant');
// define('CURRENCY', '₹');
// define('TAX_PERCENT', 5); // GST %
// define('UPI_ID', 'SpiceGarden@okicici'); // Your Merchant UPI ID
// define('UPI_NAME', 'Spice Garden Restaurant');

// // Payment Gateway Settings (Razorpay)
// define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_HERE');
// define('RAZORPAY_KEY_SECRET', 'YOUR_SECRET_HERE');
// define('ENABLE_ONLINE_PAYMENT', false); // Set to true after adding keys

// class Database
// {
//     private static $instance = null;
//     private $pdo;

//     private function __construct()
//     {
//         $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
//         $options = [
//             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::ATTR_EMULATE_PREPARES => false,
//         ];
//         try {
//             $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
//         } catch (PDOException $e) {
//             die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
//         }
//     }

//     public static function getInstance(): self
//     {
//         if (self::$instance === null) {
//             self::$instance = new self();
//         }
//         return self::$instance;
//     }

//     public function getConnection(): PDO
//     {
//         return $this->pdo;
//     }



<?php
// ============================================
// Database Configuration (Render PostgreSQL)
// ============================================

// Render automatically provides this in Environment
$databaseUrl = getenv("DATABASE_URL");

if ($databaseUrl) {
    $db = parse_url($databaseUrl);

    define('DB_HOST', $db['host']);
    define('DB_USER', $db['user']);
    define('DB_PASS', $db['pass']);
    define('DB_NAME', ltrim($db['path'], '/'));
    define('DB_PORT', $db['port']);
} else {
    // Local fallback (for development)
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'postgres');
    define('DB_PASS', 'password');
    define('DB_NAME', 'restaurant_qr');
    define('DB_PORT', '5432');
}

// App Settings
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost');
define('SITE_NAME', 'Spice Garden Restaurant');
define('CURRENCY', '₹');
define('TAX_PERCENT', 5);
define('UPI_ID', 'SpiceGarden@okicici');
define('UPI_NAME', 'Spice Garden Restaurant');

// Payment Gateway (Optional)
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
define('ENABLE_ONLINE_PAYMENT', false);

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode([
                'error' => 'Database connection failed',
                'message' => $e->getMessage()
            ]));
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
// }
