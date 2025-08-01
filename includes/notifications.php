<?php
require_once 'send_email.php';
require_once 'db.php';

/**
 * Send notification to both user and admin
 * @param int $user_id User ID (can be null for admin-only notifications)
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (login, registration, transaction, etc.)
 * @param bool $email_user Whether to send email to user
 * @param bool $email_admin Whether to send email to admin
 */
function send_notification($user_id, $title, $message, $type = 'general', $email_user = true, $email_admin = true) {
    global $pdo;

    try {
        // Get site settings
        $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Store notification in database if user_id provided
        if ($user_id) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $title, $message]);
        }

        // Get user details if user_id provided
        $user = null;
        if ($user_id) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Send email to user
        if ($email_user && $user && !empty($user['email'])) {
            $email_body = "<h3>Hello {$user['name']},</h3>";
            $email_body .= "<p>{$message}</p>";
            $email_body .= "<p>Best regards,<br>{$settings['site_name']} Team</p>";
            
            send_email($user['email'], $title, $email_body);
        }

        // Send email to admin
        if ($email_admin && !empty($settings['admin_email'])) {
            $admin_message = $message;
            if ($user) {
                $admin_message = "User: {$user['name']} ({$user['email']})<br>" . $message;
            }
            
            $admin_body = "<h3>Admin Notification</h3>";
            $admin_body .= "<p>{$admin_message}</p>";
            $admin_body .= "<p>Notification Type: " . ucfirst($type) . "</p>";
            
            send_email($settings['admin_email'], "[ADMIN] " . $title, $admin_body);
        }

        return true;
    } catch (PDOException $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send user registration notification
 */
function notify_user_registration($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $title = "Welcome to our platform!";
        $message = "Thank you for registering with us. Please verify your email address to activate your account.";
        send_notification($user_id, $title, $message, 'registration');
    }
}

/**
 * Send user login notification
 */
function notify_user_login($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $title = "Login Activity";
        $message = "You have successfully logged into your account on " . date('Y-m-d H:i:s') . ".";
        send_notification($user_id, $title, $message, 'login', true, false); // Don't email admin for every login
    }
}

/**
 * Send transaction notification
 */
function notify_transaction($user_id, $transaction_type, $amount, $status, $description = '') {
    $title = ucfirst($transaction_type) . " " . ucfirst($status);
    $message = "Your {$transaction_type} of ₦" . number_format($amount, 2) . " has been {$status}.";
    if ($description) {
        $message .= "<br>Details: {$description}";
    }
    
    send_notification($user_id, $title, $message, 'transaction');
}

/**
 * Send withdrawal notification
 */
function notify_withdrawal($user_id, $amount, $status) {
    $title = "Withdrawal " . ucfirst($status);
    $message = "Your withdrawal request of ₦" . number_format($amount, 2) . " has been {$status}.";
    
    send_notification($user_id, $title, $message, 'withdrawal');
}

/**
 * Send password change notification
 */
function notify_password_change($user_id) {
    $title = "Password Changed";
    $message = "Your account password has been successfully changed on " . date('Y-m-d H:i:s') . ". If you did not make this change, please contact support immediately.";
    
    send_notification($user_id, $title, $message, 'security');
}

/**
 * Send profile update notification
 */
function notify_profile_update($user_id) {
    $title = "Profile Updated";
    $message = "Your profile information has been successfully updated on " . date('Y-m-d H:i:s') . ".";
    
    send_notification($user_id, $title, $message, 'profile', true, false); // Don't email admin for profile updates
}

/**
 * Send email verification notification
 */
function send_verification_email($user_id, $token) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $verification_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/verify_email.php?token=" . $token;
        
        $title = "Email Verification Required";
        $email_body = "<h3>Hello {$user['name']},</h3>";
        $email_body .= "<p>Thank you for registering with {$settings['site_name']}. Please click the link below to verify your email address:</p>";
        $email_body .= "<p><a href='{$verification_url}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email Address</a></p>";
        $email_body .= "<p>Or copy and paste this link into your browser:</p>";
        $email_body .= "<p>{$verification_url}</p>";
        $email_body .= "<p>This link will expire in 24 hours.</p>";
        $email_body .= "<p>If you did not create an account, please ignore this email.</p>";
        $email_body .= "<p>Best regards,<br>{$settings['site_name']} Team</p>";
        
        return send_email($user['email'], $title, $email_body);
    }
    
    return false;
}
?>