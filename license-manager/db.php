<?php
// Database configuration for the license manager
define('DB_HOST', 'localhost');
define('DB_NAME', 'license_manager_db');
define('DB_USER', 'license_manager_user');
define('DB_PASS', 'your_secure_password');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create licenses table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `licenses` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `license_key` VARCHAR(255) NOT NULL UNIQUE,
      `domain` VARCHAR(255) NOT NULL,
      `customer_email` VARCHAR(255) NOT NULL,
      `status` ENUM('active', 'inactive') DEFAULT 'active',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // In a real app, you'd want to log this error and show a generic error page.
    die("Database connection failed: " . $e->getMessage());
}
?>
