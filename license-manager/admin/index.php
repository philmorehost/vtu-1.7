<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would use a database to store admin credentials.
    // For now, we'll use hardcoded credentials for simplicity.
    $admin_user = 'admin';
    $admin_pass_hash = password_hash('password', PASSWORD_DEFAULT);

    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $admin_user && password_verify($_POST['password'], $admin_pass_hash)) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 24rem; }
        h1 { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 2rem; color: #111827; }
        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; font-weight: 500; margin-bottom: 0.5rem; }
        .input-group input { width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; box-sizing: border-box; }
        .submit-btn { background-color: #3b82f6; color: #fff; font-weight: 600; padding: 0.75rem; border: none; border-radius: 0.5rem; cursor: pointer; width: 100%; }
        .error { color: #b91c1c; background-color: #fef2f2; border: 1px solid #fca5a5; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>
    </div>
</body>
</html>
