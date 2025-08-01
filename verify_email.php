<?php
require_once('includes/db.php');
require_once('includes/notifications.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM email_verifications WHERE token = ? AND expires >= NOW()");
        $stmt->execute([$token]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($verification) {
            // Update user verification status
            $stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE WHERE id = ?");
            $stmt->execute([$verification['user_id']]);

            // Delete the verification token
            $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE id = ?");
            $stmt->execute([$verification['id']]);

            // Send welcome notification
            $title = "Email Verified Successfully";
            $message = "Your email has been verified successfully. You can now log in to your account.";
            send_notification($verification['user_id'], $title, $message, 'verification', true, false);

            header("Location: verification_complete.php?success=1");
            exit();
        } else {
            // Check if token exists but expired
            $stmt = $pdo->prepare("SELECT * FROM email_verifications WHERE token = ?");
            $stmt->execute([$token]);
            $expired_verification = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($expired_verification) {
                header("Location: verification_complete.php?error=expired");
            } else {
                header("Location: verification_complete.php?error=invalid");
            }
            exit();
        }
    } catch (PDOException $e) {
        error_log("Email verification error: " . $e->getMessage());
        header("Location: verification_complete.php?error=system");
        exit();
    }
}

header("Location: login.php?error=Invalid verification request.");
exit();
?>
