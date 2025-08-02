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
            margin-bottom: 1.25rem;
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
        .password-toggle {
            position: absolute;
            top: 70%;
            right: 1rem;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9CA3AF;
        }
        .submit-btn {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 1rem;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }
        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        #password-strength-meter {
            width: 100%;
            background-color: #E5E7EB;
            border-radius: 9999px;
            height: 0.5rem;
        }
        #password-strength-bar {
            height: 0.5rem;
            border-radius: 9999px;
            transition: width 0.3s, background-color 0.3s;
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
                <p>Create your account to get started</p>
            </div>

            <?php
            if (isset($_SESSION['register_error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
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
                <div class="flex items-center space-x-2 mb-4">
                    <div id="password-strength-meter"><div id="password-strength-bar" style="width: 0%"></div></div>
                    <p id="password-strength-text" class="text-xs italic whitespace-nowrap"></p>
                </div>
                <input type="hidden" name="ref" value="<?= htmlspecialchars($_GET['ref'] ?? ''); ?>">
                <button type="submit" id="register-button" class="submit-btn">Create Account</button>
            </form>
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
        <div class="login-image-container"></div>
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
            let text = 'Weak';
            let color = '#EF4444'; // bg-red-500

            if (password.length > 0) {
                strength += 10;
                if (password.length >= 8) strength += 15;
                if (password.match(/[a-z]/)) strength += 15;
                if (password.match(/[A-Z]/)) strength += 20;
                if (password.match(/[0-9]/)) strength += 20;
                if (password.match(/[^a-zA-Z0-9]/)) strength += 20;
            }

            if (strength > 80) {
                text = 'Very Strong';
                color = '#22C55E'; // bg-green-500
            } else if (strength > 60) {
                text = 'Strong';
                color = '#84CC16'; // bg-lime-500
            } else if (strength > 40) {
                text = 'Medium';
                color = '#F59E0B'; // bg-amber-500
            } else if (strength > 0) {
                text = 'Weak';
                color = '#F87171'; // bg-red-400
            } else {
                text = '';
            }

            passwordStrengthBar.style.width = strength + '%';
            passwordStrengthBar.style.backgroundColor = color;
            passwordStrengthText.innerText = text;

            if (strength >= 50) {
                registerButton.disabled = false;
            } else {
                registerButton.disabled = true;
            }
        });
    </script>
</body>
</html>
