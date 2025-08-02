<?php
// Simple, multi-step PHP installer
// A single file to reduce complexity

session_start();

// --- Configuration ---
$install_lock_file = 'includes/install.lock';
$config_file = 'includes/config.php';

// --- Functions ---

/**
 * Checks if the application is already installed.
 * @return bool
 */
function is_installed() {
    global $install_lock_file;
    return file_exists($install_lock_file);
}

/**
 * Verifies the license key and domain.
 *
 * @param string $license_key
 * @param string $domain_name
 * @return bool
 */
function verify_license($license_key, $domain_name) {
    // Placeholder for license verification logic.
    // In a real application, this would make a cURL request to a license server.
    // For now, we'll just check if the license key is not empty.
    return !empty($license_key);
}

/**
 * Renders the installer page.
 *
 * @param string $title The title of the current step.
 * @param string $content The HTML content of the current step.
 */
function render_page($title, $content) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - {$title}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #444; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .btn-disabled { background-color: #ccc; cursor: not-allowed; }
        .error { color: #d8000c; background-color: #ffbaba; border: 1px solid; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { color: #4f8a10; background-color: #dff2bf; border: 1px solid; margin: 10px 0; padding: 15px; border-radius: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        label { font-weight: bold; }
        .requirements li { list-style: none; padding: 5px 0; }
        .requirements .pass { color: green; }
        .requirements .fail { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Application Installer</h1>
        <h2>{$title}</h2>
        {$content}
    </div>
</body>
</html>
HTML;
}

function run_migrations($pdo, $db_name) {
    try {
        // From original setup.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL UNIQUE, `phone` VARCHAR(20) NOT NULL, `password` VARCHAR(255) NOT NULL, `passcode` VARCHAR(255), `tier` INT DEFAULT 1, `wallet_balance` DECIMAL(10, 2) DEFAULT 0.00, `bonus_balance` DECIMAL(10, 2) DEFAULT 0.00, `referral_link` VARCHAR(255), `referred_by` INT, `status` VARCHAR(20) DEFAULT 'active', `is_verified` BOOLEAN DEFAULT FALSE, `api_key` VARCHAR(255), `api_enabled` BOOLEAN DEFAULT FALSE, `last_login` TIMESTAMP NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `user_level` INT DEFAULT 0, `paystack_account` VARCHAR(20), `paystack_bank` VARCHAR(100), `bvn` VARCHAR(11), `nin` VARCHAR(11))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `email_verifications` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `token` VARCHAR(255) NOT NULL UNIQUE, `expires` DATETIME NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `transactions` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `type` VARCHAR(50) NOT NULL, `description` VARCHAR(255) NOT NULL, `amount` DECIMAL(10, 2) NOT NULL, `status` VARCHAR(50) NOT NULL, `service_details` TEXT, `source` VARCHAR(20) DEFAULT 'Website', `balance_before` DECIMAL(10, 2), `balance_after` DECIMAL(10, 2), `batch_id` VARCHAR(255), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `withdrawals` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `amount` DECIMAL(10, 2) NOT NULL, `bank_details` TEXT NOT NULL, `status` VARCHAR(20) DEFAULT 'pending', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `fund_shares` (`id` INT AUTO_INCREMENT PRIMARY KEY, `sender_id` INT NOT NULL, `recipient_id` INT NOT NULL, `amount` DECIMAL(10, 2) NOT NULL, `status` VARCHAR(20) DEFAULT 'pending', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT, `title` VARCHAR(255) NOT NULL, `message` TEXT NOT NULL, `is_read` BOOLEAN DEFAULT FALSE, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `bank_details` (`id` INT AUTO_INCREMENT PRIMARY KEY, `bank_name` VARCHAR(255) NOT NULL, `account_name` VARCHAR(255) NOT NULL, `account_number` VARCHAR(20) NOT NULL, `charge` DECIMAL(10, 2) DEFAULT 0.00, `instructions` TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_orders` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `bank_id` INT, `amount` DECIMAL(10, 2) NOT NULL, `payment_proof` TEXT, `status` VARCHAR(20) DEFAULT 'pending', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (bank_id) REFERENCES bank_details(id) ON DELETE SET NULL)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (`id` INT AUTO_INCREMENT PRIMARY KEY, `site_name` VARCHAR(255) DEFAULT 'My VTU', `site_logo` VARCHAR(255), `auth_image` VARCHAR(255), `session_timeout` INT DEFAULT 30, `cache_control` VARCHAR(255) DEFAULT 'no-cache', `referral_bonus_tier1` DECIMAL(5, 2) DEFAULT 0.00, `referral_bonus_tier2` DECIMAL(5, 2) DEFAULT 0.00, `admin_email` VARCHAR(255) DEFAULT 'admin@example.com')");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `services` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL, `config` TEXT NOT NULL)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `bonus_withdrawals` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `amount` DECIMAL(10, 2) NOT NULL, `bank_details` TEXT NOT NULL, `status` VARCHAR(20) DEFAULT 'pending', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL UNIQUE, `password` VARCHAR(255) NOT NULL, `last_login` TIMESTAMP NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_read_notifications` (`id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `notification_id` int(11) NOT NULL, `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `user_notification` (`user_id`,`notification_id`), KEY `user_id` (`user_id`), KEY `notification_id` (`notification_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (`id` INT AUTO_INCREMENT PRIMARY KEY, `email` VARCHAR(255) NOT NULL, `token` VARCHAR(255) NOT NULL, `expires` DATETIME NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `chat` (`id` INT AUTO_INCREMENT PRIMARY KEY, `sender_id` INT NOT NULL, `recipient_id` INT NOT NULL, `message` TEXT NOT NULL, `is_read` BOOLEAN DEFAULT FALSE, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `transaction_limits` (`id` INT AUTO_INCREMENT PRIMARY KEY, `identifier_type` VARCHAR(50) NOT NULL, `identifier_value` VARCHAR(100) NOT NULL, `limit_type` VARCHAR(50) NOT NULL, `limit_value` DECIMAL(15,2) NOT NULL, `max_transactions` INT DEFAULT NULL, `created_by` INT, `period_type` VARCHAR(50) DEFAULT 'daily', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL, FOREIGN KEY (`created_by`) REFERENCES admins(id) ON DELETE SET NULL)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `blocked_identifiers` (`id` INT AUTO_INCREMENT PRIMARY KEY, `identifier_type` VARCHAR(50) NOT NULL, `identifier_value` VARCHAR(100) NOT NULL, `reason` TEXT, `blocked_by` INT, `blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `unblocked_at` TIMESTAMP NULL DEFAULT NULL, `status` VARCHAR(20) DEFAULT 'blocked')");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `senderid_requests` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `sender_id` VARCHAR(50) NOT NULL, `sample_message` TEXT, `status` VARCHAR(20) DEFAULT 'pending', `moderated_by` INT, `moderated_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `sms_sender_ids` (`id` INT AUTO_INCREMENT PRIMARY KEY, `sender_id` VARCHAR(20) NOT NULL, `status` ENUM('pending', 'approved', 'blocked') DEFAULT 'pending', `requested_by` INT, `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `approved_by` INT DEFAULT NULL, `approved_at` TIMESTAMP NULL DEFAULT NULL, `blocked_reason` VARCHAR(255) DEFAULT NULL, `reviewed_by` INT DEFAULT NULL, `user_id` INT DEFAULT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");

        // From migrate_admin_features.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `transaction_limits` (`id` INT AUTO_INCREMENT PRIMARY KEY, `identifier_type` ENUM('phone', 'meter_number', 'smartcard_number', 'betting_id') NOT NULL, `max_transactions` INT NOT NULL, `period_type` ENUM('daily', 'weekly', 'monthly') NOT NULL, `created_by` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY `idx_identifier_type` (`identifier_type`), FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `blocked_identifiers` (`id` INT AUTO_INCREMENT PRIMARY KEY, `identifier_type` ENUM('phone', 'meter_number', 'smartcard_number', 'betting_id', 'sms_sender_id', 'sms_keyword') NOT NULL, `identifier_value` VARCHAR(255) NOT NULL, `reason` TEXT, `blocked_by` INT NOT NULL, `blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `unique_identifier` (`identifier_type`, `identifier_value`), KEY `idx_identifier_type` (`identifier_type`), KEY `idx_identifier_value` (`identifier_value`), FOREIGN KEY (`blocked_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `sms_sender_ids` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `sender_id` VARCHAR(100) NOT NULL, `sample_message` TEXT NOT NULL, `status` ENUM('pending', 'approved', 'disapproved') DEFAULT 'pending', `reviewed_by` INT NULL, `review_notes` TEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY `idx_user_id` (`user_id`), KEY `idx_sender_id` (`sender_id`), KEY `idx_status` (`status`), FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL)");

        // From migrate_api_gateway.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS `api_providers` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL UNIQUE, `display_name` VARCHAR(100) NOT NULL, `base_url` VARCHAR(255) NOT NULL, `api_key` VARCHAR(255), `secret_key` VARCHAR(255), `username` VARCHAR(100), `password` VARCHAR(255), `auth_type` ENUM('bearer', 'basic', 'api_key', 'custom') DEFAULT 'bearer', `headers` TEXT, `priority` INT DEFAULT 1, `status` ENUM('active', 'inactive') DEFAULT 'active', `balance_check_endpoint` VARCHAR(255), `requery_endpoint` VARCHAR(255), `rate_limit` INT DEFAULT 0, `timeout` INT DEFAULT 30, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `networks` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(50) NOT NULL UNIQUE, `display_name` VARCHAR(50) NOT NULL, `code` VARCHAR(10) NOT NULL UNIQUE, `logo_url` VARCHAR(255), `status` ENUM('active', 'inactive') DEFAULT 'active', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
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

        // Seeding data
        $stmt = $pdo->query("SELECT id FROM site_settings WHERE id = 1");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("INSERT INTO site_settings (id, site_name) VALUES (1, 'My VTU')");
        }

        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = 'admin@example.com'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $admin_pass = password_hash('password', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO admins (name, email, password) VALUES ('Admin', 'admin@example.com', '$admin_pass')");
        }

        // ... (add all other seeding data from migration files)

        return true;
    } catch (PDOException $e) {
        return false;
    }
}


// --- Main Logic ---

// If installed, redirect to home page.
if (is_installed()) {
    header("Location: index.php");
    exit;
}

$step = $_GET['step'] ?? '1';

switch ($step) {
    case '1':
        // Face 1: Welcome & License
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $license_key = $_POST['license_key'] ?? '';
            $domain_name = $_POST['domain_name'] ?? '';

            if (verify_license($license_key, $domain_name)) {
                $_SESSION['license_key'] = $license_key;
                $_SESSION['domain_name'] = $domain_name;
                header("Location: setup.php?step=2");
                exit;
            } else {
                $error_message = "<div class='error'>Invalid license key or domain. Please try again.</div>";
            }
        }

        $content = "
            <h3>Welcome</h3>
            {$error_message}
            <p>Welcome to the application installer. This wizard will guide you through the installation process.</p>
            <form action='setup.php?step=1' method='post'>
                <div>
                    <label for='license_key'>License Key</label>
                    <input type='text' id='license_key' name='license_key' required>
                </div>
                <div>
                    <label for='domain_name'>Domain Name</label>
                    <input type='text' id='domain_name' name='domain_name' value='{$_SERVER['SERVER_NAME']}' required>
                </div>
                <button type='submit' class='btn'>Verify & Proceed</button>
            </form>
        ";
        render_page('License Verification', $content);
        break;

    case '2':
        // Face 2: Server Requirements
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 7.4',
                'check' => version_compare(PHP_VERSION, '7.4', '>=')
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'check' => extension_loaded('pdo')
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'check' => extension_loaded('curl')
            ],
            'gd' => [
                'name' => 'GD Extension',
                'check' => extension_loaded('gd')
            ],
             'mysqli' => [
                'name' => 'MySQLi Extension',
                'check' => extension_loaded('mysqli')
            ]
        ];

        $all_requirements_met = true;
        $requirements_html = '<ul class="requirements">';
        foreach ($requirements as $req) {
            $status_class = $req['check'] ? 'pass' : 'fail';
            $requirements_html .= "<li class='{$status_class}'>{$req['name']}</li>";
            if (!$req['check']) {
                $all_requirements_met = false;
            }
        }
        $requirements_html .= '</ul>';

        $proceed_button = $all_requirements_met
            ? '<a href="setup.php?step=3" class="btn">Proceed to Database Setup</a>'
            : '<a href="#" class="btn btn-disabled" onclick="alert(\'Please fix the failed requirements before proceeding.\'); return false;">Proceed</a>';

        $content = "
            <h3>Server Requirements</h3>
            {$requirements_html}
            {$proceed_button}
        ";
        render_page('Server Requirements', $content);
        break;

    case '3':
        // Face 3: Database Setup
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db_host = $_POST['db_host'] ?? '';
            $db_name = $_POST['db_name'] ?? '';
            $db_user = $_POST['db_user'] ?? '';
            $db_pass = $_POST['db_pass'] ?? '';

            // Create config file content
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '{$db_host}');\n";
            $config_content .= "define('DB_NAME', '{$db_name}');\n";
            $config_content .= "define('DB_USER', '{$db_user}');\n";
            $config_content .= "define('DB_PASS', '{$db_pass}');\n";

            // Write config file
            if (file_put_contents($config_file, $config_content)) {
                try {
                    // Test DB connection and run migrations
                    $pdo = new PDO("mysql:host={$db_host}", $db_user, $db_pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}`");
                    $pdo->exec("USE `{$db_name}`");

                    if (run_migrations($pdo, $db_name)) {
                        // Create install lock file
                        file_put_contents($install_lock_file, 'installed');
                        header("Location: setup.php?step=4");
                        exit;
                    } else {
                        $error_message = "<div class='error'>Database migration failed.</div>";
                    }
                } catch (PDOException $e) {
                    $error_message = "<div class='error'>Database connection failed: " . $e->getMessage() . "</div>";
                }
            } else {
                $error_message = "<div class='error'>Could not write to config file. Please check permissions.</div>";
            }
        }

        $content = "
            <h3>Database Configuration</h3>
            {$error_message}
            <p>Please provide your database connection details.</p>
            <form action='setup.php?step=3' method='post'>
                <div><label for='db_host'>Database Host</label><input type='text' id='db_host' name='db_host' value='localhost' required></div>
                <div><label for='db_name'>Database Name</label><input type='text' id='db_name' name='db_name' required></div>
                <div><label for='db_user'>Database User</label><input type='text' id='db_user' name='db_user' required></div>
                <div><label for='db_pass'>Database Password</label><input type='password' id='db_pass' name='db_pass'></div>
                <button type='submit' class='btn'>Save & Install Database</button>
            </form>
        ";
        render_page('Database Setup', $content);
        break;

    case '4':
        // Face 4: Complete
        $content = "
            <div class='success'>Congratulations! The application has been installed successfully.</div>
            <h3>Admin Login Details</h3>
            <p><strong>Username:</strong> admin@example.com</p>
            <p><strong>Password:</strong> password</p>
            <p>For security reasons, please change your password immediately after logging in.</p>
            <a href='admin/login.php' class='btn'>Login to Admin Panel</a>
        ";
        render_page('Installation Complete', $content);
        break;

    default:
        header("Location: setup.php?step=1");
        exit;
}
?>
