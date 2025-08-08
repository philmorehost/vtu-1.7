<?php
/**
 * Migration script to add 'product_type' to the 'service_products' table.
 */

require_once('includes/config.php');
require_once('includes/db.php'); // Use the centralized db connection

try {
    echo "Starting migration: Add 'product_type' column to 'service_products' table...\n";

    // SQL to add the new column
    $sql = "ALTER TABLE `service_products` ADD COLUMN `product_type` VARCHAR(100) NULL DEFAULT NULL COMMENT 'e.g., Bouquets, Add-ons for Cable TV' AFTER `status`";

    $pdo->exec($sql);

    echo "✅ Migration completed successfully!\n";
    echo "Column 'product_type' was added to the 'service_products' table.\n";

} catch (PDOException $e) {
    // Check if the error is "Duplicate column name" (error code 1060)
    if ($e->getCode() == '42S21' || strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✓ INFO: Column 'product_type' already exists in 'service_products' table. No action needed.\n";
    } else {
        echo "ERROR: Could not complete migration. " . $e->getMessage() . "\n";
    }
}
?>
