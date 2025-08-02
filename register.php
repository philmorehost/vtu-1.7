<?php
require_once('core_integrity.php');
_0x2a1b_c4d3e5();

require_once('includes/session_config.php');
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
    <title>Register - <?= htmlspecialchars($settings['site_name'] ?? 'Your Platform') ?></title>
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
            margin-top: 1rem;
        }
        .submit-btn:hover {
            background-color: var(--primary-hover-color);
        }
        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        #password-strength-meter {
            width: 100%;
            background-color: #e5e7eb;
            border-radius: 9999px;
            height: 0.5rem;
            margin-top: 0.5rem;
        }
        #password-strength-bar {
            height: 0.5rem;
            border-radius: 9999px;
            transition: width 0.3s, background-color 0.3s;
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
                    <h1><?= htmlspecialchars($settings['site_name'] ?? 'Create an Account') ?></h1>
                <?php endif; ?>
                <p>Join us today to get started</p>
            </div>

            <?php
            if (isset($_SESSION['register_error'])) {
                echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>' . htmlspecialchars($_SESSION['register_error']) . '</div>';
                unset($_SESSION['register_error']);
            }
            ?>

            <form action="auth_user.php?action=register" method="POST">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" placeholder="John Doe" required autocomplete="name">
                </div>
                 <div class="input-group">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com" required autocomplete="email">
                </div>
                 <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="tel" placeholder="08012345678" required autocomplete="tel">
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" required autocomplete="new-password">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
                <div id="password-strength-meter"><div id="password-strength-bar" style="width: 0%"></div></div>
                <p id="password-strength-text" class="text-xs italic text-right h-4 mt-1"></p>

                <input type="hidden" name="ref" value="<?= htmlspecialchars($_GET['ref'] ?? ''); ?>">
                <button type="submit" id="register-button" class="submit-btn" disabled>Create Account</button>
            </form>
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
        <div class="auth-image-section"></div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordStrengthBar = document.getElementById('password-strength-bar');
        const passwordStrengthText = document.getElementById('password-strength-text');
        const registerButton = document.getElementById('register-button');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;
            let text = '';
            let color = '#e5e7eb'; // bg-gray-200

            if (password.length > 0) {
                if (password.length >= 8) strength += 25;
                if (password.match(/[a-z]/)) strength += 25;
                if (password.match(/[A-Z]/)) strength += 25;
                if (password.match(/[0-9]/)) strength += 25;
            }

            if (strength == 100) {
                text = 'Very Strong';
                color = '#22c55e'; // green-500
            } else if (strength == 75) {
                text = 'Strong';
                color = '#84cc16'; // lime-500
            } else if (strength == 50) {
                text = 'Medium';
                color = '#f59e0b'; // amber-500
            } else if (strength == 25) {
                text = 'Weak';
                color = '#ef4444'; // red-500
            }

            passwordStrengthBar.style.width = strength + '%';
            passwordStrengthBar.style.backgroundColor = color;
            passwordStrengthText.innerText = text;

            registerButton.disabled = strength < 50;
        });
    </script>
</body>
</html>
