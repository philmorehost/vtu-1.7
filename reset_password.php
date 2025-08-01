<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $_SESSION['reset_error'] = 'Please enter your email address.';
        header('Location: forgot_password.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);

            $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/set_new_password.php?token=$token";

            // In a real application, you would send an email here.
            // For this example, we'll just show the link.
            $_SESSION['reset_success'] = "A password reset link has been sent to your email address. For testing purposes, the link is: <a href='$reset_link'>$reset_link</a>";
        } else {
            $_SESSION['reset_error'] = 'No user found with that email address.';
        }
    } catch (PDOException $e) {
        $_SESSION['reset_error'] = 'An error occurred. Please try again later.';
    }
}

header('Location: forgot_password.php');
exit();
