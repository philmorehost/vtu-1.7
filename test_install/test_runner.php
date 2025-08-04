<?php

// test_install/test_runner.php

// Define mock database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'test_install_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Include the setup file
require_once('setup.php');

// Create a PDO connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop the test database if it exists
    $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");

    // Create the test database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $pdo->exec("USE `" . DB_NAME . "`");

    echo "Test database created successfully.\n";

    // Run the migrations
    if (run_migrations($pdo, DB_NAME)) {
        echo "Migrations ran successfully.\n";

        // Verification
        $tables = ['users', 'admins', 'site_settings', 'service_providers', 'networks', 'service_products', 'migrations'];
        $all_tables_exist = true;
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                echo "Error: Table '$table' does not exist.\n";
                $all_tables_exist = false;
            }
        }

        if ($all_tables_exist) {
            echo "All essential tables exist.\n";
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM service_providers");
        $provider_count = $stmt->fetchColumn();
        echo "Service providers count: $provider_count\n";

        $stmt = $pdo->query("SELECT COUNT(*) FROM service_products");
        $product_count = $stmt->fetchColumn();
        echo "Service products count: $product_count\n";

        $stmt = $pdo->query("SELECT COUNT(*) FROM migrations");
        $migration_count = $stmt->fetchColumn();
        echo "Migrations count: $migration_count\n";

    } else {
        echo "Migrations failed.\n";
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
