
<?php
require_once('includes/config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $pdo->exec("USE `" . DB_NAME . "`");

    // Users table
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `phone` VARCHAR(20) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `passcode` VARCHAR(255),
        `tier` INT DEFAULT 1,
        `wallet_balance` DECIMAL(10, 2) DEFAULT 0.00,
        `bonus_balance` DECIMAL(10, 2) DEFAULT 0.00,
        `referral_link` VARCHAR(255),
        `referred_by` INT,
        `status` VARCHAR(20) DEFAULT 'active',
        `is_verified` BOOLEAN DEFAULT FALSE,
        `api_key` VARCHAR(255),
        `api_enabled` BOOLEAN DEFAULT FALSE,
        `last_login` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `user_level` INT DEFAULT 0,
        `paystack_account` VARCHAR(20),
        `paystack_bank` VARCHAR(100),
        `bvn` VARCHAR(11),
        `nin` VARCHAR(11)
    )";
    $pdo->exec($sql);

    // Email Verifications table
    $sql = "CREATE TABLE IF NOT EXISTS `email_verifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `token` VARCHAR(255) NOT NULL UNIQUE,
        `expires` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Transactions table
    $sql = "CREATE TABLE IF NOT EXISTS `transactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `type` VARCHAR(50) NOT NULL,
        `description` VARCHAR(255) NOT NULL,
        `amount` DECIMAL(10, 2) NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `service_details` TEXT,
        `source` VARCHAR(20) DEFAULT 'Website',
        `balance_before` DECIMAL(10, 2),
        `balance_after` DECIMAL(10, 2),
        `batch_id` VARCHAR(255),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Withdrawals table
    $sql = "CREATE TABLE IF NOT EXISTS `withdrawals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `amount` DECIMAL(10, 2) NOT NULL,
        `bank_details` TEXT NOT NULL,
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Fund Shares table
    $sql = "CREATE TABLE IF NOT EXISTS `fund_shares` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sender_id` INT NOT NULL,
        `recipient_id` INT NOT NULL,
        `amount` DECIMAL(10, 2) NOT NULL,
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Notifications table
    $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT,
        `title` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `is_read` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Bank Details table
    $sql = "CREATE TABLE IF NOT EXISTS `bank_details` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `bank_name` VARCHAR(255) NOT NULL,
        `account_name` VARCHAR(255) NOT NULL,
        `account_number` VARCHAR(20) NOT NULL,
        `charge` DECIMAL(10, 2) DEFAULT 0.00,
        `instructions` TEXT
    )";
    $pdo->exec($sql);

    // Payment Orders table
    $sql = "CREATE TABLE IF NOT EXISTS `payment_orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `bank_id` INT,
        `amount` DECIMAL(10, 2) NOT NULL,
        `payment_proof` TEXT,
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (bank_id) REFERENCES bank_details(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);

    // Site Settings table
    $sql = "CREATE TABLE IF NOT EXISTS `site_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `site_name` VARCHAR(255) DEFAULT 'My VTU',
        `site_logo` VARCHAR(255),
        `auth_image` VARCHAR(255),
        `session_timeout` INT DEFAULT 30,
        `cache_control` VARCHAR(255) DEFAULT 'no-cache',
        `referral_bonus_tier1` DECIMAL(5, 2) DEFAULT 0.00,
        `referral_bonus_tier2` DECIMAL(5, 2) DEFAULT 0.00,
        `admin_email` VARCHAR(255) DEFAULT 'admin@example.com'
    )";
    $pdo->exec($sql);
    $stmt = $pdo->query("SELECT id FROM site_settings WHERE id = 1");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO site_settings (id, site_name) VALUES (1, 'My VTU')");
    }


    // Services table
    $sql = "CREATE TABLE IF NOT EXISTS `services` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `config` TEXT NOT NULL
    )";
    $pdo->exec($sql);

    // Bonus Withdrawals table
    $sql = "CREATE TABLE IF NOT EXISTS `bonus_withdrawals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `amount` DECIMAL(10, 2) NOT NULL,
        `bank_details` TEXT NOT NULL,
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Admins table
    $sql = "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `last_login` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // User Read Notifications table
    $sql = "
    CREATE TABLE IF NOT EXISTS `user_read_notifications` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `notification_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `user_notification` (`user_id`,`notification_id`),
      KEY `user_id` (`user_id`),
      KEY `notification_id` (`notification_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ";
    $pdo->exec($sql);

    // Password Resets table
    $sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL,
        `token` VARCHAR(255) NOT NULL,
        `expires` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Add admin user if not exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = 'admin@example.com'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $admin_pass = password_hash('password', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admins (name, email, password) VALUES ('Admin', 'admin@example.com', '$admin_pass')");
    }

    // Chat table
    $sql = "CREATE TABLE IF NOT EXISTS `chat` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sender_id` INT NOT NULL,
        `recipient_id` INT NOT NULL,
        `message` TEXT NOT NULL,
        `is_read` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // TRANSACTION LIMITS
    $sql = "CREATE TABLE IF NOT EXISTS `transaction_limits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `identifier_type` VARCHAR(50) NOT NULL,
    `identifier_value` VARCHAR(100) NOT NULL,
    `limit_type` VARCHAR(50) NOT NULL,
    `limit_value` DECIMAL(15,2) NOT NULL,
    `max_transactions` INT DEFAULT NULL,      -- <-- Add this line!
    `created_by` INT,
    `period_type` VARCHAR(50) DEFAULT 'daily',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`created_by`) REFERENCES admins(id) ON DELETE SET NULL
)";
try {
    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "Error creating transaction_limits: " . $e->getMessage();
}

    //BLOCK IDENTIFIER
$sql = "CREATE TABLE IF NOT EXISTS `blocked_identifiers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `identifier_type` VARCHAR(50) NOT NULL,   -- phone, meter, smartcard, betting_id, sender_id, keyword
    `identifier_value` VARCHAR(100) NOT NULL,
    `reason` TEXT,
    `blocked_by` INT,                         -- admin id
    `blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `unblocked_at` TIMESTAMP NULL DEFAULT NULL,
    `status` VARCHAR(20) DEFAULT 'blocked'
)";
try {
    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "Error creating blocked_identifiers: " . $e->getMessage();
}

    //Bulk sms sender ID moderation
    $sql = "CREATE TABLE IF NOT EXISTS `senderid_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `sender_id` VARCHAR(50) NOT NULL,
    `sample_message` TEXT,
    `status` VARCHAR(20) DEFAULT 'pending',   -- pending, approved, rejected
    `moderated_by` INT,                       -- admin id
    `moderated_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
try {
    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "Error creating senderid_requests: " . $e->getMessage();
}

    // SMS SENDER ID 
    $sql = "CREATE TABLE IF NOT EXISTS `sms_sender_ids` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sender_id` VARCHAR(20) NOT NULL,
        `status` ENUM('pending', 'approved', 'blocked') DEFAULT 'pending',
        `requested_by` INT,
        `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `approved_by` INT DEFAULT NULL,
        `approved_at` TIMESTAMP NULL DEFAULT NULL,
        `blocked_reason` VARCHAR(255) DEFAULT NULL,
        `reviewed_by` INT DEFAULT NULL,
        `user_id` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    try {
    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "Error creating blocked_identifiers: " . $e->getMessage();
}

    // Add SMTP settings to site_settings table
$sql = "ALTER TABLE `site_settings`
    ADD COLUMN `smtp_host` VARCHAR(255),
    ADD COLUMN `smtp_port` INT,
    ADD COLUMN `smtp_user` VARCHAR(255),
    ADD COLUMN `smtp_pass` VARCHAR(255),
    ADD COLUMN `smtp_from_email` VARCHAR(255),
    ADD COLUMN `smtp_from_name` VARCHAR(255)";
try {
    $pdo->exec($sql);
} catch (PDOException $e) {
    // Ignore if columns already exist
}

    // Add is_verified field to users table if not exists
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_verified` BOOLEAN DEFAULT FALSE");
    } catch (PDOException $e) {
        // Ignore if column already exists
    }

    // Add new Paystack and verification fields to users table if not exists
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `paystack_account` VARCHAR(20)");
    } catch (PDOException $e) {
        // Ignore if column already exists
    }
    
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `paystack_bank` VARCHAR(100)");
    } catch (PDOException $e) {
        // Ignore if column already exists
    }
    
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `bvn` VARCHAR(11)");
    } catch (PDOException $e) {
        // Ignore if column already exists
    }
    
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `nin` VARCHAR(11)");
    } catch (PDOException $e) {
        // Ignore if column already exists
    }

    echo "Database and tables created/updated successfully.";

} catch (PDOException $e) {
    die("ERROR: Could not set up the database. " . $e->getMessage());
}
?>
