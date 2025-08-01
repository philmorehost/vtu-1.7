<?php
/**
 * Migration script for API Gateway and Dynamic Service Integration
 * Adds new tables: api_providers, service_products, networks, api_routes, product_networks
 */

require_once('includes/config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting API Gateway database migration...\n";

    // API Providers table - stores different API providers and their configurations
    $sql = "CREATE TABLE IF NOT EXISTS `api_providers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `display_name` VARCHAR(100) NOT NULL,
        `base_url` VARCHAR(255) NOT NULL,
        `api_key` VARCHAR(255),
        `secret_key` VARCHAR(255),
        `username` VARCHAR(100),
        `password` VARCHAR(255),
        `auth_type` ENUM('bearer', 'basic', 'api_key', 'custom') DEFAULT 'bearer',
        `headers` TEXT, -- JSON string for custom headers
        `priority` INT DEFAULT 1, -- Higher number = higher priority
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `balance_check_endpoint` VARCHAR(255),
        `requery_endpoint` VARCHAR(255),
        `rate_limit` INT DEFAULT 0, -- requests per minute, 0 = no limit
        `timeout` INT DEFAULT 30, -- seconds
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✓ Created api_providers table\n";

    // Networks table - stores telecom networks
    $sql = "CREATE TABLE IF NOT EXISTS `networks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL UNIQUE,
        `display_name` VARCHAR(50) NOT NULL,
        `code` VARCHAR(10) NOT NULL UNIQUE, -- e.g., MTN = 01, GLO = 02
        `logo_url` VARCHAR(255),
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✓ Created networks table\n";

    // Service Products table - stores all service products (data plans, airtime, etc.)
    $sql = "CREATE TABLE IF NOT EXISTS `service_products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `service_type` ENUM('data', 'airtime', 'cabletv', 'electricity', 'exam', 'betting', 'recharge', 'bulksms', 'giftcard') NOT NULL,
        `network_id` INT,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `plan_code` VARCHAR(50), -- API specific plan code
        `amount` DECIMAL(10, 2) NOT NULL, -- Original amount
        `selling_price` DECIMAL(10, 2) NOT NULL, -- Price to sell to customers
        `discount_percentage` DECIMAL(5, 2) DEFAULT 0.00,
        `validity` VARCHAR(50), -- e.g., '30 days', '1 month'
        `data_size` VARCHAR(20), -- e.g., '1GB', '500MB'
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `extra_data` TEXT, -- JSON for additional service-specific data
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL,
        INDEX idx_service_type (service_type),
        INDEX idx_network_service (network_id, service_type)
    )";
    $pdo->exec($sql);
    echo "✓ Created service_products table\n";

    // API Routes table - maps services to API providers with specific configurations
    $sql = "CREATE TABLE IF NOT EXISTS `api_routes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `service_type` ENUM('data', 'airtime', 'cabletv', 'electricity', 'exam', 'betting', 'recharge', 'bulksms', 'giftcard') NOT NULL,
        `network_id` INT,
        `api_provider_id` INT NOT NULL,
        `endpoint` VARCHAR(255) NOT NULL,
        `method` ENUM('GET', 'POST', 'PUT', 'DELETE') DEFAULT 'POST',
        `request_mapping` TEXT, -- JSON mapping for API request parameters
        `response_mapping` TEXT, -- JSON mapping for API response parsing
        `success_codes` VARCHAR(50) DEFAULT '200', -- HTTP success codes
        `priority` INT DEFAULT 1,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `test_mode` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE CASCADE,
        FOREIGN KEY (api_provider_id) REFERENCES api_providers(id) ON DELETE CASCADE,
        INDEX idx_service_network (service_type, network_id),
        INDEX idx_priority (priority DESC)
    )";
    $pdo->exec($sql);
    echo "✓ Created api_routes table\n";

    // Product Networks table - many-to-many relationship for products and their supported networks
    $sql = "CREATE TABLE IF NOT EXISTS `product_networks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `network_id` INT NOT NULL,
        `api_provider_id` INT,
        `custom_price` DECIMAL(10, 2), -- Override product price for specific network
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES service_products(id) ON DELETE CASCADE,
        FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE CASCADE,
        FOREIGN KEY (api_provider_id) REFERENCES api_providers(id) ON DELETE SET NULL,
        UNIQUE KEY unique_product_network (product_id, network_id)
    )";
    $pdo->exec($sql);
    echo "✓ Created product_networks table\n";

    // Insert default networks
    $networks = [
        ['MTN', 'MTN Nigeria', '01'],
        ['GLO', 'Globacom', '02'],
        ['AIRTEL', 'Airtel Nigeria', '03'],
        ['9MOBILE', '9mobile', '04']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO networks (name, display_name, code) VALUES (?, ?, ?)");
    foreach ($networks as $network) {
        $stmt->execute($network);
    }
    echo "✓ Inserted default networks\n";

    // Insert sample API provider
    $stmt = $pdo->prepare("INSERT IGNORE INTO api_providers (name, display_name, base_url, auth_type, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['sample_api', 'Sample API Provider', 'https://api.example.com', 'bearer', 1]);
    echo "✓ Inserted sample API provider\n";

    // Migrate existing hardcoded data to new structure
    // Data plans from admin/services.php
    $dataPlans = [
        ['MTN', '1GB', 300, '1 Day', 'mtn-1gb-300'],
        ['GLO', '3GB', 700, '14 Days', 'glo-3gb-700']
    ];

    foreach ($dataPlans as $plan) {
        // Get network ID
        $stmt = $pdo->prepare("SELECT id FROM networks WHERE name = ?");
        $stmt->execute([$plan[0]]);
        $network = $stmt->fetch();
        
        if ($network) {
            // Insert service product
            $stmt = $pdo->prepare("INSERT IGNORE INTO service_products (service_type, network_id, name, amount, selling_price, validity, data_size, plan_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(['data', $network['id'], $plan[1] . ' Data Plan', $plan[2], $plan[2], $plan[3], $plan[1], $plan[4]]);
        }
    }
    echo "✓ Migrated existing data plans\n";

    // Add airtime service for all networks
    $stmt = $pdo->prepare("SELECT id FROM networks");
    $stmt->execute();
    $networks = $stmt->fetchAll();

    foreach ($networks as $network) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO service_products (service_type, network_id, name, amount, selling_price, discount_percentage) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['airtime', $network['id'], 'Airtime Recharge', 0, 0, 1.00]); // 1% discount
    }
    echo "✓ Added airtime services for all networks\n";

    echo "\n✅ API Gateway database migration completed successfully!\n";
    echo "New tables created:\n";
    echo "- api_providers: Manages different API providers\n";
    echo "- networks: Stores telecom networks\n";
    echo "- service_products: All service products with pricing\n";
    echo "- api_routes: Maps services to API providers\n";
    echo "- product_networks: Many-to-many relationship for products and networks\n";

} catch (PDOException $e) {
    die("ERROR: Migration failed. " . $e->getMessage() . "\n");
}
?>