<?php
require_once('includes/session_config.php');

// If not installed, redirect to setup
if (!file_exists('includes/install.lock')) {
    header('Location: setup.php');
    exit;
}

require_once('core_integrity.php');
_0x2a1b_c4d3e5();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require_once('includes/db.php');
$stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = ?");
$stmt->execute([1]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($settings['site_name'] ?? 'Your Platform') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        :root {
            --primary-color: #3b82f6;
            --primary-hover-color: #2563eb;
            --background-color: #f9fafb;
            --card-background: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --input-border: #d1d5db;
            --input-focus-border: #3b82f6;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .auth-container {
            display: grid;
            grid-template-columns: 1fr;
            max-width: 56rem; /* 896px */
            width: 90%;
            background-color: var(--card-background);
            border-radius: 1rem; /* 16px */
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        @media (min-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        .auth-form-section {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-image-section {
            background-image: url('<?= htmlspecialchars($settings['auth_image'] ?? 'assets/images/auth-bg.jpg') ?>');
            background-size: cover;
            background-position: center;
            display: none;
        }
        @media (min-width: 768px) {
            .auth-image-section {
                display: block;
            }
        }
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-header img {
            height: 3.5rem; /* 56px */
            margin: 0 auto 1rem;
        }
        .form-header h1 {
            font-size: 1.5rem; /* 24px */
            font-weight: 700;
            color: var(--text-primary);
        }
        .form-header p {
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        .input-group {
            margin-bottom: 1.25rem; /* 20px */
            position: relative;
        }
        .input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.875rem; /* 14px */
            color: var(--text-primary);
        }
        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem; /* 8px */
            border: 1px solid var(--input-border);
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        .password-toggle {
            position: absolute;
            top: 2.4rem; /* 38.4px */
            right: 1rem;
            cursor: pointer;
            color: #9ca3af;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .form-options a {
            font-size: 0.875rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .form-options a:hover {
            text-decoration: underline;
        }
        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }
        .submit-btn:hover {
            background-color: var(--primary-hover-color);
        }
        .form-footer {
            text-align: center;
            font-size: 0.875rem;
            margin-top: 1.5rem;
        }
        .form-footer a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            border-width: 1px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        .alert i {
            margin-right: 0.75rem;
        }
        .alert-error {
            background-color: #fef2f2;
            border-color: #fca5a5;
            color: #b91c1c;
        }
        .alert-success {
            background-color: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form-section">
            <div class="form-header">
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo">
                <?php else: ?>
                    <h1><?= htmlspecialchars($settings['site_name'] ?? 'Welcome Back') ?></h1>
                <?php endif; ?>
                <p>Login to access your account</p>
            </div>

            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            if (isset($_GET['message'])) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i>' . htmlspecialchars($_GET['message']) . '</div>';
            }
            ?>

            <form action="auth_user.php" method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
                <div class="form-options">
                    <div></div>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="submit-btn">Sign In</button>
            </form>
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Create one now</a></p>
            </div>
        </div>
        <div class="auth-image-section"></div>
    </div>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
