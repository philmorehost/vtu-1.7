<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

$stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = ?");
$stmt->execute([1]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$reset_error = $_SESSION['reset_error'] ?? null;
$reset_success = $_SESSION['reset_success'] ?? null;
unset($_SESSION['reset_error']);
unset($_SESSION['reset_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= htmlspecialchars($settings['site_name'] ?? 'Your Platform') ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #6366F1;
            --background-color: #F3F4F6;
            --card-background: #FFFFFF;
            --text-color: #374151;
            --input-border: #D1D5DB;
            --input-focus-border: #4F46E5;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-color);
        }
        .login-container {
            display: grid;
            grid-template-columns: 1fr;
            max-width: 900px;
            width: 100%;
            background-color: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        @media (min-width: 768px) {
            .login-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        .login-form-container {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-image-container {
            background-image: url('<?= htmlspecialchars($settings['auth_image'] ?? 'assets/images/auth-bg.jpg') ?>');
            background-size: cover;
            background-position: center;
            display: none;
        }
        @media (min-width: 768px) {
            .login-image-container {
                display: block;
            }
        }
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-header img {
            height: 4rem;
            margin: 0 auto 1rem;
        }
        .form-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-color);
        }
        .form-header p {
            color: #6B7280;
            margin-top: 0.5rem;
        }
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--input-border);
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
        }
        .submit-btn {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
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
        }
        .alert-error {
            background-color: #FEF2F2;
            border-color: #F87171;
            color: #B91C1C;
        }
        .alert-success {
            background-color: #F0FDF4;
            border-color: #4ADE80;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form-container">
            <div class="form-header">
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo">
                <?php else: ?>
                    <h1><?= htmlspecialchars($settings['site_name'] ?? 'Welcome') ?></h1>
                <?php endif; ?>
                <p>Enter your email to receive a password reset link.</p>
            </div>

            <?php
            if ($reset_error) {
                echo '<div class="alert alert-error">' . htmlspecialchars($reset_error) . '</div>';
            }
            if ($reset_success) {
                echo '<div class="alert alert-success">' . htmlspecialchars($reset_success) . '</div>';
            }
            ?>

            <form action="reset_password.php" method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="submit-btn">Send Reset Link</button>
            </form>
            <div class="form-footer">
                <p>Remember your password? <a href="login.php">Sign In</a></p>
            </div>
        </div>
        <div class="login-image-container"></div>
    </div>
</body>
</html>
