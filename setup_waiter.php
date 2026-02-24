<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = "CREATE TABLE IF NOT EXISTS waiter_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        table_id INT NOT NULL,
        table_number VARCHAR(20) NOT NULL,
        request_type ENUM('Waiter', 'Bill', 'Water', 'Other') DEFAULT 'Waiter',
        status ENUM('pending', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=MyISAM;";
    $db->exec($sql);
    echo "Table waiter_requests created successfully\n";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
