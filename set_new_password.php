<?php
require_once('includes/session_config.php');
require_once('includes/db.php');
require_once('includes/notifications.php');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Invalid token.');
}

try {
    $stmt = $pdo->prepare("SELECT email, expires FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset || strtotime($reset['expires']) < time()) {
        die('Token has expired or is invalid.');
    }
} catch (PDOException $e) {
    die('An error occurred.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $_SESSION['reset_error'] = 'Please enter and confirm your new password.';
    } elseif ($password !== $password_confirm) {
        $_SESSION['reset_error'] = 'Passwords do not match.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // Get user ID for notification
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$reset['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$password_hash, $reset['email']]);

            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$reset['email']]);

            // Send password change notification
            if ($user) {
                notify_password_change($user['id']);
            }

            $pdo->commit();

            $_SESSION['login_success'] = 'Your password has been reset successfully. Please log in.';
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['reset_error'] = 'An error occurred. Please try again later.';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = ?");
$stmt->execute([1]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - <?= htmlspecialchars($settings['site_name'] ?? '') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="hidden md:block w-1/2 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($settings['auth_image'] ?? 'assets/images/auth-bg.jpg') ?>');"></div>
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 sm:p-6 md:p-8">
            <div class="w-full max-w-md">
                <form action="set_new_password.php?token=<?= htmlspecialchars($token) ?>" method="POST" class="bg-white shadow-lg rounded-lg px-6 sm:px-8 pt-6 pb-8 mb-4" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                    <div class="flex justify-center mb-6">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo" class="h-16">
                        <?php else: ?>
                            <h2 class="text-2xl font-bold text-center"><?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?></h2>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-2xl font-bold text-center mb-6">Set New Password</h2>
                    <?php
                    if (isset($_SESSION['reset_error'])) {
                        echo '<p class="text-red-500 text-xs italic text-center mb-4">' . $_SESSION['reset_error'] . '</p>';
                        unset($_SESSION['reset_error']);
                    }
                    ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            New Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="******************" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirm">
                            Confirm New Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password_confirm" name="password_confirm" type="password" placeholder="******************" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
