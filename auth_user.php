<?php
require_once('includes/session_config.php');
require_once('includes/db.php');
require_once('includes/notifications.php');

$action = $_GET['action'] ?? 'login';

if ($action === 'register') {
    function calculate_password_strength($password) {
        $strength = 0;
        if (strlen($password) >= 6) {
            $strength += 25;
        }
        if (preg_match('/[a-z]/', $password)) {
            $strength += 25;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $strength += 25;
        }
        if (preg_match('/[0-9]/', $password)) {
            $strength += 25;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $strength += 25;
        }
        return min(100, $strength);
    }
    // Handle Registration
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $ref = $_POST['ref'] ?? '';

        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            die('Please fill all required fields.');
        }

        if (calculate_password_strength($password) < 80) {
            $_SESSION['register_error'] = 'Password is too weak. Please choose a stronger password.';
            header('Location: register.php');
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $referral_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/register.php?ref=" . uniqid();

        try {
            // Check if email or phone already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                if ($existing_user['email'] === $email) {
                    $_SESSION['register_error'] = 'Email already exists for another user.';
                } else {
                    $_SESSION['register_error'] = 'Phone number already exists for another user.';
                }
                header('Location: register.php');
                exit();
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, wallet_balance, bonus_balance, referral_link, is_verified) VALUES (?, ?, ?, ?, 0.00, 0.00, ?, FALSE)");
            $stmt->execute([$name, $email, $phone, $password_hash, $referral_link]);
            $new_user_id = $pdo->lastInsertId();

            // Create email verification token
            $verification_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, token, expires) VALUES (?, ?, ?)");
            $stmt->execute([$new_user_id, $verification_token, $expires]);

            if (!empty($ref)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_link = ?");
                $stmt->execute([$ref]);
                $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($referrer) {
                    $stmt = $pdo->prepare("UPDATE users SET referred_by = ? WHERE id = ?");
                    $stmt->execute([$referrer['id'], $new_user_id]);
                }
            }

            $pdo->commit();

            // Send verification email
            send_verification_email($new_user_id, $verification_token);
            
            // Send registration notification
            notify_user_registration($new_user_id);

            $_SESSION['registration_success'] = true;
            $_SESSION['user_email'] = $email;
            header('Location: verification_pending.php');
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['register_error'] = 'Registration failed: ' . $e->getMessage();
            header('Location: register.php');
            exit();
        }
    }
} else {
    // Handle Login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            die('Please fill all required fields.');
        }

        try {
            $stmt = $pdo->prepare("SELECT id, password, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Check if email is verified
                if (!$user['is_verified']) {
                    $_SESSION['login_error'] = 'Please verify your email address before logging in. Check your email for verification link.';
                    header('Location: login.php');
                    exit();
                }

                $_SESSION['user_id'] = $user['id'];

                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Send login notification
                notify_user_login($user['id']);

                header('Location: index.php');
                exit();
            } else {
                $_SESSION['login_error'] = 'Invalid email or password.';
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            die('Login failed: ' . $e->getMessage());
        }
    }
}

header('Location: login.php');
exit();
?>
