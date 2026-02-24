<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    // Add payment_method and payment_status to orders table
    $sql = "ALTER TABLE orders 
            ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash' AFTER total_amount,
            ADD COLUMN payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending' AFTER payment_method";

    $pdo->exec($sql);
    echo "Successfully updated orders table for payments.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
