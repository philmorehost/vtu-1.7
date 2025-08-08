<?php
/**
 * Migration script to add 'product_type' to the 'service_products' table.
 * This script is intended to be run via a web request.
 */

require_once('includes/config.php');
require_once('includes/db.php');

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
        // Return a 500 Internal Server Error for other exceptions
        header("HTTP/1.1 500 Internal Server Error");
        echo "ERROR: Could not complete migration. " . $e->getMessage() . "\n";
    }
}
?>
