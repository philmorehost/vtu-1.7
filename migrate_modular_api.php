<?php
/**
 * Migration script for Modular API Provider System
 * Updates database to support module-based API integration
 */

require_once('includes/config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting modular API provider system migration...\n";

    // Update api_providers table to include provider_module field
    $sql = "ALTER TABLE api_providers 
            ADD COLUMN IF NOT EXISTS provider_module VARCHAR(100) DEFAULT NULL AFTER name,
            ADD COLUMN IF NOT EXISTS config_fields TEXT DEFAULT NULL AFTER headers";
    $pdo->exec($sql);
    echo "✓ Updated api_providers table with module support\n";

    // Create new api_provider_routes table (simplified routing)
    $sql = "CREATE TABLE IF NOT EXISTS `api_provider_routes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `api_provider_id` INT NOT NULL,
        `service_type` ENUM('airtime', 'data', 'cable_tv', 'electricity', 'exam', 'betting', 'recharge_card', 'bulk_sms', 'gift_card') NOT NULL,
        `network_id` INT NULL,
        `priority` INT DEFAULT 1,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (api_provider_id) REFERENCES api_providers(id) ON DELETE CASCADE,
        FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL,
        INDEX idx_service_network (service_type, network_id),
        INDEX idx_priority (priority DESC)
    )";
    $pdo->exec($sql);
    echo "✓ Created api_provider_routes table\n";

    // Create api_transaction_logs table for audit trail
    $sql = "CREATE TABLE IF NOT EXISTS `api_transaction_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `service_type` VARCHAR(50) NOT NULL,
        `provider_id` INT NOT NULL,
        `success` BOOLEAN NOT NULL,
        `response_message` TEXT,
        `request_data` TEXT,
        `response_data` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (provider_id) REFERENCES api_providers(id) ON DELETE CASCADE,
        INDEX idx_service_provider (service_type, provider_id),
        INDEX idx_created_at (created_at)
    )";
    $pdo->exec($sql);
    echo "✓ Created api_transaction_logs table\n";

    // Insert sample modular API providers
    $providers = [
        [
            'name' => 'clubkonnect',
            'provider_module' => 'clubkonnect',
            'display_name' => 'Club Konnect',
            'base_url' => 'https://www.nellobytesystems.com/APIVerify.asp',
            'auth_type' => 'bearer',
            'priority' => 5,
            'config_fields' => json_encode(['api_key', 'secret_key'])
        ],
        [
            'name' => 'smartdata', 
            'provider_module' => 'smartdata',
            'display_name' => 'Smartdata',
            'base_url' => 'https://smartrecharge.ng/api/v2/',
            'auth_type' => 'api_key',
            'priority' => 4,
            'config_fields' => json_encode(['api_key', 'secret_key'])
        ],
        [
            'name' => 'vtpass',
            'provider_module' => 'vtpass', 
            'display_name' => 'VTPass',
            'base_url' => 'https://vtpass.com/api/',
            'auth_type' => 'custom',
            'priority' => 3,
            'config_fields' => json_encode(['api_key', 'secret_key'])
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO api_providers 
        (name, provider_module, display_name, base_url, auth_type, priority, config_fields) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($providers as $provider) {
        $stmt->execute([
            $provider['name'],
            $provider['provider_module'],
            $provider['display_name'],
            $provider['base_url'],
            $provider['auth_type'],
            $provider['priority'],
            $provider['config_fields']
        ]);
    }
    echo "✓ Inserted sample modular API providers\n";

    // Create default service routes for each provider
    $services = ['airtime', 'data', 'cable_tv', 'electricity', 'exam'];
    
    // Get all provider IDs
    $stmt = $pdo->query("SELECT id, name FROM api_providers WHERE provider_module IS NOT NULL");
    $providers = $stmt->fetchAll();

    $routeStmt = $pdo->prepare("
        INSERT IGNORE INTO api_provider_routes 
        (api_provider_id, service_type, priority, status) 
        VALUES (?, ?, ?, 'active')
    ");

    foreach ($providers as $provider) {
        foreach ($services as $service) {
            $priority = ($provider['name'] === 'clubkonnect') ? 5 : 
                       (($provider['name'] === 'smartdata') ? 4 : 3);
            
            $routeStmt->execute([
                $provider['id'],
                $service,
                $priority
            ]);
        }
    }
    echo "✓ Created default service routes\n";

    // Migrate existing api_routes data to new structure if exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_routes");
        $routeCount = $stmt->fetch()['count'];
        
        if ($routeCount > 0) {
            echo "Found $routeCount existing routes, migrating...\n";
            
            $stmt = $pdo->query("
                SELECT ar.*, ap.provider_module 
                FROM api_routes ar 
                JOIN api_providers ap ON ar.api_provider_id = ap.id 
                WHERE ap.provider_module IS NOT NULL
            ");
            $existingRoutes = $stmt->fetchAll();
            
            $migrateStmt = $pdo->prepare("
                INSERT IGNORE INTO api_provider_routes 
                (api_provider_id, service_type, network_id, priority, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($existingRoutes as $route) {
                $migrateStmt->execute([
                    $route['api_provider_id'],
                    $route['service_type'],
                    $route['network_id'],
                    $route['priority'],
                    $route['status']
                ]);
            }
            echo "✓ Migrated existing routes\n";
        }
    } catch (Exception $e) {
        echo "Note: Could not migrate existing routes (table may not exist): " . $e->getMessage() . "\n";
    }

    // Create backup of old api_routes table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS api_routes_backup AS SELECT * FROM api_routes");
        echo "✓ Created backup of api_routes table\n";
    } catch (Exception $e) {
        echo "Note: Could not backup api_routes table: " . $e->getMessage() . "\n";
    }

    echo "\n✅ Modular API provider system migration completed successfully!\n";
    echo "New features:\n";
    echo "- Module-based API providers (no more JSON mappings)\n";
    echo "- Simplified API provider configuration\n";
    echo "- Enhanced transaction logging\n";
    echo "- Backward compatibility maintained\n";
    echo "\nNext steps:\n";
    echo "1. Update admin interface to use new modular system\n";
    echo "2. Configure API providers with keys/secrets\n";
    echo "3. Test services with new module system\n";

} catch (PDOException $e) {
    die("ERROR: Migration failed. " . $e->getMessage() . "\n");
}
?>