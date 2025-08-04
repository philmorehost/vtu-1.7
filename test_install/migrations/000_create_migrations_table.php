<?php

// migrations/000_create_migrations_table.php

return [
    "up" => function($pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (`id` INT AUTO_INCREMENT PRIMARY KEY, `migration` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    },
    "down" => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS `migrations`");
    }
];
