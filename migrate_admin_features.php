<?php
/**
 * Migration script for Admin Features: Transaction Limits, Blocking System, and SMS Sender ID Moderation
 * Adds new tables: transaction_limits, blocked_identifiers, sms_sender_ids
 */

require_once('includes/config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting admin features database migration...\n";

    // Transaction Limits table - stores maximum allowed transactions per identifier type
    $sql = "CREATE TABLE IF NOT EXISTS `transaction_limits` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `identifier_type` ENUM('phone', 'meter_number', 'smartcard_number', 'betting_id') NOT NULL,
        `max_transactions` INT NOT NULL,
        `period_type` ENUM('daily', 'weekly', 'monthly') NOT NULL,
        `created_by` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_identifier_type` (`identifier_type`),
        FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✓ Created transaction_limits table\n";

    // Blocked Identifiers table - stores blocked phone numbers, meter numbers, etc.
    $sql = "CREATE TABLE IF NOT EXISTS `blocked_identifiers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `identifier_type` ENUM('phone', 'meter_number', 'smartcard_number', 'betting_id', 'sms_sender_id', 'sms_keyword') NOT NULL,
        `identifier_value` VARCHAR(255) NOT NULL,
        `reason` TEXT,
        `blocked_by` INT NOT NULL,
        `blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_identifier` (`identifier_type`, `identifier_value`),
        KEY `idx_identifier_type` (`identifier_type`),
        KEY `idx_identifier_value` (`identifier_value`),
        FOREIGN KEY (`blocked_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✓ Created blocked_identifiers table\n";

    // SMS Sender IDs table - stores SMS sender ID registrations and moderation status
    $sql = "CREATE TABLE IF NOT EXISTS `sms_sender_ids` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `sender_id` VARCHAR(100) NOT NULL,
        `sample_message` TEXT NOT NULL,
        `status` ENUM('pending', 'approved', 'disapproved') DEFAULT 'pending',
        `reviewed_by` INT NULL,
        `review_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_user_id` (`user_id`),
        KEY `idx_sender_id` (`sender_id`),
        KEY `idx_status` (`status`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "✓ Created sms_sender_ids table\n";

    // Insert default transaction limits (can be modified by admin later)
    $defaultLimits = [
        ['phone', 50, 'daily'],
        ['meter_number', 10, 'daily'],
        ['smartcard_number', 5, 'daily'],
        ['betting_id', 20, 'daily']
    ];

    $stmt = $pdo->prepare("SELECT id FROM admins LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    $adminId = $admin ? $admin['id'] : 1;

    foreach ($defaultLimits as $limit) {
        $checkStmt = $pdo->prepare("SELECT id FROM transaction_limits WHERE identifier_type = ? AND period_type = ?");
        $checkStmt->execute([$limit[0], $limit[2]]);
        if ($checkStmt->rowCount() == 0) {
            $insertStmt = $pdo->prepare("INSERT INTO transaction_limits (identifier_type, max_transactions, period_type, created_by) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$limit[0], $limit[1], $limit[2], $adminId]);
            echo "✓ Added default limit for {$limit[0]}: {$limit[1]} per {$limit[2]}\n";
        }
    }

    echo "\n✅ Admin features migration completed successfully!\n";
    echo "New tables created:\n";
    echo "- transaction_limits: For managing transaction limits per identifier\n";
    echo "- blocked_identifiers: For blocking phone numbers, meter numbers, etc.\n";
    echo "- sms_sender_ids: For SMS sender ID registration and moderation\n";

} catch (PDOException $e) {
    echo "ERROR: Could not complete migration. " . $e->getMessage() . "\n";
}
?>