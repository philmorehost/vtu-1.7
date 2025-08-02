<?php
/**
 * Migration for Optional Pricing in Service Products
 * This script alters the service_products table to make pricing fields nullable.
 *
 * Run this script once from your browser or command line.
 */
require_once(__DIR__ . '/includes/db.php');

echo "<pre>";

try {
    echo "Altering 'service_products' table to make pricing fields optional...\n";

    $pdo->exec("
        ALTER TABLE `service_products`
        MODIFY `amount` DECIMAL(10, 2) NULL,
        MODIFY `selling_price` DECIMAL(10, 2) NULL;
    ");

    echo "Table 'service_products' updated successfully. The 'amount' and 'selling_price' columns are now nullable.\n";

    echo "\nMigration completed successfully!\n";
    echo "You can now delete this file.";

} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

echo "</pre>";
?>
