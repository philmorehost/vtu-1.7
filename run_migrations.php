<?php
// A script to run database migrations directly.

require_once('includes/config.php');
require_once('includes/db.php');

function run_migrations($pdo, $db_name) {
    try {
        // First, ensure the migrations table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (`id` INT AUTO_INCREMENT PRIMARY KEY, `migration` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

        $stmt = $pdo->query("SELECT migration FROM migrations");
        $run_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $migration_files = glob('migrations/*.php');
        sort($migration_files);

        foreach ($migration_files as $file) {
            $migration_name = basename($file);
            if (!in_array($migration_name, $run_migrations)) {
                $migration = require($file);
                if (isset($migration['up']) && is_callable($migration['up'])) {
                    $migration['up']($pdo);
                    $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                    $stmt->execute([$migration_name]);
                    echo "Migration '{$migration_name}' executed successfully.\n";
                }
            }
        }

        // Seeding data
        $stmt = $pdo->query("SELECT id FROM site_settings WHERE id = 1");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("INSERT INTO site_settings (id, site_name) VALUES (1, 'My VTU')");
            echo "Site settings seeded.\n";
        }

        return true;
    } catch (PDOException $e) {
        error_log("Migration Error: " . $e->getMessage());
        echo "Migration Error: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "Starting migrations...\n";
if (run_migrations($pdo, DB_NAME)) {
    echo "Migrations completed successfully.\n";
} else {
    echo "Migrations failed.\n";
}
?>
