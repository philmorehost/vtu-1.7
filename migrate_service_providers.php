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

    // 4. Seed Cable TV providers
    echo "\nSeeding Cable TV providers...\n";
    $cableProviders = [
        ['dstv-padi', 'DStv Padi N4,400'], ['dstv-yanga', 'DStv Yanga N6,000'], ['dstv-confam', 'Dstv Confam N11,000'],
        ['dstv79', 'DStv Compact N19,000'], ['dstv3', 'DStv Premium N44,500'], ['dstv7', 'DStv Compact Plus N30,000'],
        ['dstv9', 'DStv Premium-French N69,000'], ['dstv10', 'DStv Premium-Asia N50,500'], ['confam-extra', 'DStv Confam + ExtraView N17,000'],
        ['yanga-extra', 'DStv Yanga + ExtraView N12,000'], ['padi-extra', 'DStv Padi + ExtraView N10,400'], ['dstv30', 'DStv Compact + Extra View N25,000'],
        ['com-frenchtouch', 'DStv Compact + French Touch N26,000'], ['dstv33', 'DStv Premium + Extra View N50,500'],
        ['com-frenchtouch-extra', 'DStv Compact + French Touch + ExtraView N32,000'], ['dstv43', 'DStv Compact Plus + French Plus N54,500'],
        ['complus-frenchtouch', 'DStv Compact Plus + French Touch N37,000'], ['dstv45', 'DStv Compact Plus + Extra View N36,000'],
        ['complus-french-extraview', 'DStv Compact Plus + FrenchPlus + Extra View N60,500'], ['dstv47', 'DStv Compact + French Plus N43,500'],
        ['dstv62', 'DStv Premium + French + Extra View N75,000'], ['frenchplus-addon', 'DStv French Plus Add-on N24,500'],
        ['dstv-greatwall', 'DStv Great Wall Standalone Bouquet N3,800'], ['frenchtouch-addon', 'DStv French Touch Add-on N7,000'],
        ['extraview-access', 'ExtraView Access N6,000'], ['dstv-yanga-showmax', 'DStv Yanga + Showmax N7,750'],
        ['dstv-greatwall-showmax', 'DStv Great Wall Standalone Bouquet + Showmax N7,300'], ['dstv-compact-plus-showmax', 'DStv Compact Plus + Showmax N31,750'],
        ['dstv-confam-showmax', 'Dstv Confam + Showmax N12,750'], ['dstv-compact-showmax', 'DStv Compact + Showmax N20,750'],
        ['dstv-padi-showmax', 'DStv Padi + Showmax N7,900'], ['dstv-asia-showmax', 'DStv Asia + Showmax N18,400'],
        ['dstv-premium-french-showmax', 'DStv Premium + French + Showmax N69,000'], ['dstv-premium-showmax', 'DStv Premium + Showmax N44,500'],
        ['dstv-indian', 'DStv Indian N14,900'], ['dstv-premium-indian', 'DStv Premium East Africa and Indian N16530'],
        ['dstv-fta-plus', 'DStv FTA Plus N1,600'], ['dstv-premium-hd', 'DStv PREMIUM HD N39,000'], ['dstv-access-1', 'DStv Access N2000'],
        ['dstv-family-1', 'DStv Family'], ['dstv-indian-add-on', 'DStv India Add-on N14,900'], ['dstv-mobile-1', 'DSTV MOBILE N790'],
        ['dstv-movie-bundle-add-on', 'DStv Movie Bundle Add-on N3500'], ['dstv-pvr-access', 'DStv PVR Access Service N4000'],
        ['dstv-premium-wafr-showmax', 'DStv Premium W/Afr + Showmax N50,500']
    ];

    $stmtCable = $pdo->prepare("INSERT IGNORE INTO service_providers (service_type, code, name) VALUES ('cabletv', ?, ?)");
    $cableSeededCount = 0;
    foreach ($cableProviders as $provider) {
        $stmtCable->execute([$provider[0], $provider[1]]);
        if ($stmtCable->rowCount() > 0) {
            $cableSeededCount++;
        }
    }
    if ($cableSeededCount > 0) {
        echo "Seeded {$cableSeededCount} new Cable TV providers.\n";
    } else {
        echo "Cable TV providers were already seeded.\n";
    }

    // 5. Seed Electricity providers
    echo "\nSeeding Electricity providers...\n";
    $electricityProviders = [
        ['eko-electric', 'Eko Electric - EKEDC'], ['ikeja-electric', 'Ikeja Electric - IKEDC'], ['abuja-electric', 'Abuja Electric - AEDC'],
        ['kano-electric', 'Kano Electric - KEDC'], ['portharcourt-electric', 'Porthacourt Electric - PHEDC'], ['jos-electric', 'Jos Electric - JEDC'],
        ['ibadan-electric', 'Ibadan Electric - IBEDC'], ['kaduna-electric', 'Kaduna Electric - KAEDC'], ['enugu-electric', 'Enugu Electric - EEDC'],
        ['benin-electric', 'Benin Electric - BEDC'], ['yola-electric', 'Yola Electric - YEDC'], ['aba-electric', 'Aba Electric - APLE']
    ];

    $stmtElec = $pdo->prepare("INSERT IGNORE INTO service_providers (service_type, code, name) VALUES ('electricity', ?, ?)");
    $elecSeededCount = 0;
    foreach ($electricityProviders as $provider) {
        $stmtElec->execute([$provider[0], $provider[1]]);
        if ($stmtElec->rowCount() > 0) {
            $elecSeededCount++;
        }
    }
    if ($elecSeededCount > 0) {
        echo "Seeded {$elecSeededCount} new Electricity providers.\n";
    } else {
        echo "Electricity providers were already seeded.\n";
    }

    $pdo->commit();

    echo "\n\nMigration and seeding completed successfully!\n";
    echo "You can now delete this file.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("An error occurred: " . $e->getMessage());
}

echo "</pre>";
?>
