<?php

// migrations/002_add_more_tables.php

return [
    "up" => function($pdo) {
        // From migrate_admin_features.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `transaction_limits` (`id` INT AUTO_INCREMENT PRIMARY KEY, `identifier_type` ENUM('phone', 'meter_number', 'smartcard_number', 'betting_id') NOT NULL, `max_transactions` INT NOT NULL, `period_type` ENUM('daily', 'weekly', 'monthly') NOT NULL, `created_by` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY `idx_identifier_type` (`identifier_type`), FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `blocked_identifiers` (`id` INT AUTO_INCREMENT PRIMARY KEY, `identifier_type` ENUM('phone', 'meter_number', 'smartcard_number', 'betting_id', 'sms_sender_id', 'sms_keyword') NOT NULL, `identifier_value` VARCHAR(255) NOT NULL, `reason` TEXT, `blocked_by` INT NOT NULL, `blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `unique_identifier` (`identifier_type`, `identifier_value`), KEY `idx_identifier_type` (`identifier_type`), KEY `idx_identifier_value` (`identifier_value`), FOREIGN KEY (`blocked_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `sms_sender_ids` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `sender_id` VARCHAR(100) NOT NULL, `sample_message` TEXT NOT NULL, `status` ENUM('pending', 'approved', 'disapproved') DEFAULT 'pending', `reviewed_by` INT NULL, `review_notes` TEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY `idx_user_id` (`user_id`), KEY `idx_sender_id` (`sender_id`), KEY `idx_status` (`status`), FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL)");

        // From migrate_api_gateway.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `api_providers` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL UNIQUE, `display_name` VARCHAR(100) NOT NULL, `base_url` VARCHAR(255) NOT NULL, `api_key` VARCHAR(255), `secret_key` VARCHAR(255), `username` VARCHAR(100), `password` VARCHAR(255), `auth_type` ENUM('bearer', 'basic', 'api_key', 'custom') DEFAULT 'bearer', `headers` TEXT, `priority` INT DEFAULT 1, `status` ENUM('active', 'inactive') DEFAULT 'active', `balance_check_endpoint` VARCHAR(255), `requery_endpoint` VARCHAR(255), `rate_limit` INT DEFAULT 0, `timeout` INT DEFAULT 30, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `networks` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(50) NOT NULL UNIQUE, `display_name` VARCHAR(50) NOT NULL, `code` VARCHAR(10) NOT NULL, `logo_url` VARCHAR(255), `status` ENUM('active', 'inactive') DEFAULT 'active', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `service_products` (`id` INT AUTO_INCREMENT PRIMARY KEY, `service_type` ENUM('data', 'airtime', 'cabletv', 'electricity', 'exam', 'betting', 'recharge', 'bulksms', 'giftcard') NOT NULL, `network_id` INT, `name` VARCHAR(100) NOT NULL, `description` TEXT, `plan_code` VARCHAR(50), `amount` DECIMAL(10, 2) NOT NULL, `selling_price` DECIMAL(10, 2) NOT NULL, `discount_percentage` DECIMAL(5, 2) DEFAULT 0.00, `validity` VARCHAR(50), `data_size` VARCHAR(20), `status` ENUM('active', 'inactive') DEFAULT 'active', `extra_data` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL, INDEX idx_service_type (service_type), INDEX idx_network_service (network_id, service_type))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `api_routes` (`id` INT AUTO_INCREMENT PRIMARY KEY, `service_type` ENUM('data', 'airtime', 'cabletv', 'electricity', 'exam', 'betting', 'recharge', 'bulksms', 'giftcard') NOT NULL, `network_id` INT, `api_provider_id` INT NOT NULL, `endpoint` VARCHAR(255) NOT NULL, `method` ENUM('GET', 'POST', 'PUT', 'DELETE') DEFAULT 'POST', `request_mapping` TEXT, `response_mapping` TEXT, `success_codes` VARCHAR(50) DEFAULT '200', `priority` INT DEFAULT 1, `status` ENUM('active', 'inactive') DEFAULT 'active', `test_mode` BOOLEAN DEFAULT FALSE, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE CASCADE, FOREIGN KEY (api_provider_id) REFERENCES api_providers(id) ON DELETE CASCADE, INDEX idx_service_network (service_type, network_id), INDEX idx_priority (priority DESC))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `product_networks` (`id` INT AUTO_INCREMENT PRIMARY KEY, `product_id` INT NOT NULL, `network_id` INT NOT NULL, `api_provider_id` INT, `custom_price` DECIMAL(10, 2), `status` ENUM('active', 'inactive') DEFAULT 'active', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES service_products(id) ON DELETE CASCADE, FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE CASCADE, FOREIGN KEY (api_provider_id) REFERENCES api_providers(id) ON DELETE SET NULL, UNIQUE KEY unique_product_network (product_id, network_id))");

        // From migrate_modular_api.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `api_provider_routes` (`id` INT AUTO_INCREMENT PRIMARY KEY, `api_provider_id` INT NOT NULL, `service_type` ENUM('airtime', 'data', 'cable_tv', 'electricity', 'exam', 'betting', 'recharge_card', 'bulk_sms', 'gift_card') NOT NULL, `network_id` INT NULL, `priority` INT DEFAULT 1, `status` ENUM('active', 'inactive') DEFAULT 'active', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (api_provider_id) REFERENCES api_providers(id) ON DELETE CASCADE, FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL, INDEX idx_service_network (service_type, network_id), INDEX idx_priority (priority DESC))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `api_transaction_logs` (`id` INT AUTO_INCREMENT PRIMARY KEY, `service_type` VARCHAR(50) NOT NULL, `provider_id` INT NOT NULL, `success` BOOLEAN NOT NULL, `response_message` TEXT, `request_data` TEXT, `response_data` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (provider_id) REFERENCES api_providers(id) ON DELETE CASCADE, INDEX idx_service_provider (service_type, provider_id), INDEX idx_created_at (created_at))");

        // From migrate_service_providers.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `service_providers` (`id` INT AUTO_INCREMENT PRIMARY KEY, `service_type` VARCHAR(50) NOT NULL, `name` VARCHAR(255) NOT NULL, `code` VARCHAR(100) NOT NULL, `status` ENUM('active', 'inactive') DEFAULT 'active', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `service_type_code` (`service_type`, `code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // ALTER TABLE statements (idempotent)
        $alter_statements = [
            "ALTER TABLE `site_settings` ADD COLUMN `smtp_host` VARCHAR(255), ADD COLUMN `smtp_port` INT, ADD COLUMN `smtp_user` VARCHAR(255), ADD COLUMN `smtp_pass` VARCHAR(255), ADD COLUMN `smtp_from_email` VARCHAR(255), ADD COLUMN `smtp_from_name` VARCHAR(255)",
            "ALTER TABLE `users` ADD COLUMN `is_verified` BOOLEAN DEFAULT FALSE",
            "ALTER TABLE `users` ADD COLUMN `paystack_account` VARCHAR(20)",
            "ALTER TABLE `users` ADD COLUMN `paystack_bank` VARCHAR(100)",
            "ALTER TABLE `users` ADD COLUMN `bvn` VARCHAR(11)",
            "ALTER TABLE `users` ADD COLUMN `nin` VARCHAR(11)",
            "ALTER TABLE `api_providers` ADD COLUMN `provider_module` VARCHAR(100) DEFAULT NULL AFTER name",
            "ALTER TABLE `api_providers` ADD COLUMN `config_fields` TEXT DEFAULT NULL AFTER headers",
            "ALTER TABLE `service_products` MODIFY `amount` DECIMAL(10, 2) NULL",
            "ALTER TABLE `service_products` MODIFY `selling_price` DECIMAL(10, 2) NULL",
            "ALTER TABLE `service_products` ADD COLUMN `service_provider_id` INT NULL AFTER `network_id`, ADD CONSTRAINT `fk_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers`(`id`) ON DELETE SET NULL",
            "ALTER TABLE `service_products` MODIFY `network_id` INT NULL"
        ];

        foreach($alter_statements as $sql) {
            try { $pdo->exec($sql); } catch (PDOException $e) { /* ignore if already exists */ }
        }
    },
    "down" => function($pdo) {
        // Drop tables in reverse order of creation
    }
];
