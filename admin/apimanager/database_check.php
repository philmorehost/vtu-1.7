<?php
/**
 * Database Check and Fix Script for API Gateway
 * Ensures api_key column exists in api_providers table
 */

require_once('../../includes/db.php');

try {
    // Check if api_providers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'api_providers'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Error: api_providers table does not exist. Please run migrate_api_gateway.php first.\n";
        exit(1);
    }
    
    // Check if api_key column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM api_providers LIKE 'api_key'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "Adding missing api_key column to api_providers table...\n";
        $pdo->exec("ALTER TABLE api_providers ADD COLUMN api_key VARCHAR(255) DEFAULT NULL");
        echo "✓ api_key column added successfully\n";
    } else {
        echo "✓ api_key column already exists\n";
    }
    
    // Check other critical columns
    $requiredColumns = ['secret_key', 'auth_type', 'priority', 'status', 'base_url'];
    foreach ($requiredColumns as $column) {
        $stmt = $pdo->query("SHOW COLUMNS FROM api_providers LIKE '$column'");
        $exists = $stmt->rowCount() > 0;
        if (!$exists) {
            echo "Warning: Column '$column' is missing from api_providers table\n";
        } else {
            echo "✓ Column '$column' exists\n";
        }
    }
    
    echo "Database check completed successfully.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>