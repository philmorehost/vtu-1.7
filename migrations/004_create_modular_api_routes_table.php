<?php

// migrations/004_create_modular_api_routes_table.php

return [
    "up" => function($pdo) {
        $pdo->exec("
            CREATE TABLE `modular_api_routes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `service_product_id` INT NOT NULL,
                `api_provider_id` INT NOT NULL,
                `priority` INT DEFAULT 1,
                `status` ENUM('active', 'inactive') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`service_product_id`) REFERENCES `service_products`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`api_provider_id`) REFERENCES `api_providers`(`id`) ON DELETE CASCADE
            )
        ");
    },
    "down" => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS `modular_api_routes`");
    }
];
