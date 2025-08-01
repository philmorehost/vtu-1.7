<?php
/**
 * Migration and Seeder for Service Providers
 * This script creates the service_providers table, alters the service_products table,
 * and seeds the initial betting providers.
 *
 * Run this script once from your browser or command line.
 */
require_once(__DIR__ . '/includes/db.php');

echo "<pre>";

try {
    $pdo->beginTransaction();

    // 1. Create the service_providers table
    echo "Checking for 'service_providers' table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `service_providers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `service_type` VARCHAR(50) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(100) NOT NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `service_type_code` (`service_type`, `code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'service_providers' created or already exists.\n\n";

    // 2. Alter the service_products table to add service_provider_id
    echo "Checking for 'service_provider_id' column in 'service_products' table...\n";
    $checkColumn = $pdo->query("SHOW COLUMNS FROM `service_products` LIKE 'service_provider_id'");
    if ($checkColumn->rowCount() == 0) {
        $pdo->exec("
            ALTER TABLE `service_products`
            ADD COLUMN `service_provider_id` INT NULL AFTER `network_id`,
            ADD CONSTRAINT `fk_service_provider`
            FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers`(`id`)
            ON DELETE SET NULL;
        ");
        echo "'service_provider_id' column added successfully.\n";
    } else {
        echo "'service_provider_id' column already exists.\n";
    }

    // Make network_id nullable
    echo "Checking if 'network_id' column is nullable...\n";
    $pdo->exec("ALTER TABLE `service_products` MODIFY `network_id` INT NULL;");
    echo "'network_id' column is now nullable.\n\n";


    // 3. Seed the betting providers
    echo "Seeding betting providers...\n";
    $bettingProviders = [
        ['msport', 'MSport'],
        ['naijabet', 'NaijaBet'],
        ['nairabet', 'NairaBet'],
        ['bet9ja-agent', 'Bet9ja Agent'],
        ['betland', 'BetLand'],
        ['betlion', 'BetLion'],
        ['supabet', 'SupaBet'],
        ['bet9ja', 'Bet9ja'],
        ['bangbet', 'BangBet'],
        ['betking', 'BetKing'],
        ['1xbet', '1xBet'],
        ['betway', 'Betway'],
        ['merrybet', 'MerryBet'],
        ['mlotto', 'MLotto'],
        ['western-lotto', 'Western Lotto'],
        ['hallabet', 'HallaBet'],
        ['green-lotto', 'Green Lotto']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO service_providers (service_type, code, name) VALUES ('betting', ?, ?)");

    $seededCount = 0;
    foreach ($bettingProviders as $provider) {
        $stmt->execute([$provider[0], $provider[1]]);
        if ($stmt->rowCount() > 0) {
            $seededCount++;
        }
    }

    if ($seededCount > 0) {
        echo "Seeded {$seededCount} new betting providers.\n";
    } else {
        echo "Betting providers were already seeded.\n";
    }

    $pdo->commit();

    echo "\n\nMigration and seeding completed successfully!\n";
    echo "You can now delete this file.";

} catch (Exception $e) {
    $pdo->rollBack();
    die("An error occurred: " . $e->getMessage());
}

echo "</pre>";
?>
