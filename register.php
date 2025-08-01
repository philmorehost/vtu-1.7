<?php
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
    <title>Register - <?= htmlspecialchars($settings['site_name'] ?? '') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="hidden md:block w-1/2 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($settings['auth_image'] ?? 'assets/images/auth-bg.jpg') ?>');"></div>
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 sm:p-6 md:p-8">
            <div class="w-full max-w-md">
                <form action="auth_user.php?action=register" method="POST" class="bg-white shadow-lg rounded-lg px-6 sm:px-8 pt-6 pb-8 mb-4" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                    <div class="flex justify-center mb-6">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo" class="h-16">
                        <?php else: ?>
                            <h2 class="text-2xl font-bold text-center"><?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?></h2>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>
                    <?php
                    if (isset($_SESSION['register_error'])) {
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
                        echo '<strong class="font-bold">Error!</strong>';
                        echo '<span class="block sm:inline"> ' . htmlspecialchars($_SESSION['register_error']) . '</span>';
                        echo '</div>';
                        unset($_SESSION['register_error']);
                    }
                    ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                            Full Name
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" type="text" placeholder="Full Name" required autocomplete="name">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="Email" required autocomplete="email">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                            Phone Number
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" type="tel" placeholder="Phone Number" required autocomplete="tel">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <div class="relative">
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="******************" required autocomplete="new-password">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                                <svg class="h-6 w-6 text-gray-700 cursor-pointer" id="toggle-password" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div id="password-strength-meter" class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="password-strength-bar" class="bg-red-500 h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <p id="password-strength-text" class="text-xs italic whitespace-nowrap"></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" id="register-button">
                            <span id="register-button-text">Register</span>
                            <span id="register-button-spinner" class="hidden">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">
                            Login
                        </a>
                    </div>
                    <input type="hidden" name="ref" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">
                </form>
            </div>
        </div>
    </div>
    <div id="password-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Weak Password
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Your password is too weak. Please use a stronger password. A strong password should contain a mix of uppercase and lowercase letters, numbers, and special characters. For example: `Letters2025@`
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="close-modal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordStrengthBar = document.getElementById('password-strength-bar');
        const passwordStrengthText = document.getElementById('password-strength-text');
        const registerButton = document.getElementById('register-button');
        const registerButtonText = document.getElementById('register-button-text');
        const registerButtonSpinner = document.getElementById('register-button-spinner');
        const passwordModal = document.getElementById('password-modal');
        const closeModalButton = document.getElementById('close-modal');
        const togglePassword = document.getElementById('toggle-password');

        registerButton.disabled = true;

        document.querySelector('form').addEventListener('submit', () => {
            registerButton.disabled = true;
            registerButtonText.classList.add('hidden');
            registerButtonSpinner.classList.remove('hidden');
        });

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (type === 'password') {
                togglePassword.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            } else {
                togglePassword.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .95-3.11 3.843-5.48 7.4-6.225M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c1.13 0 2.212.206 3.23.588M21 21l-4.35-4.35" />
                `;
            }
        });

        registerButton.addEventListener('click', (e) => {
            if (passwordInput.value.length > 0 && calculateStrength(passwordInput.value) < 80) {
                e.preventDefault();
                passwordModal.classList.remove('hidden');
            }
        });

        closeModalButton.addEventListener('click', () => {
            passwordModal.classList.add('hidden');
        });

        function calculateStrength(password) {
            let strength = 0;
            if (password.length >= 6) {
                strength += 25;
            }
            if (password.match(/[a-z]/)) {
                strength += 25;
            }
            if (password.match(/[A-Z]/)) {
                strength += 25;
            }
            if (password.match(/[0-9]/)) {
                strength += 25;
            }
            if (password.match(/[^a-zA-Z0-9]/)) {
                strength += 25;
            }
            return strength;
        }

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;
            let text = 'Weak';
            let color = 'bg-red-500';

            if (password.length >= 6) {
                strength += 25;
            }
            if (password.match(/[a-z]/)) {
                strength += 25;
            }
            if (password.match(/[A-Z]/)) {
                strength += 25;
            }
            if (password.match(/[0-9]/)) {
                strength += 25;
            }
            if (password.match(/[^a-zA-Z0-9]/)) {
                strength += 25;
            }

            if (strength >= 100) {
                text = 'Very Strong';
                color = 'bg-green-500';
            } else if (strength >= 75) {
                text = 'Strong';
                color = 'bg-yellow-500';
            } else if (strength >= 50) {
                text = 'Medium';
                color = 'bg-blue-500';
            }

            passwordStrengthBar.style.width = strength + '%';
            passwordStrengthBar.className = `h-2.5 rounded-full ${color}`;
            passwordStrengthText.innerText = `Password Strength: ${text}`;

            if (strength >= 80) {
                registerButton.disabled = false;
                registerButton.classList.remove('cursor-not-allowed', 'opacity-50');
            } else {
                registerButton.disabled = true;
                registerButton.classList.add('cursor-not-allowed', 'opacity-50');
            }
        });
    </script>
</body>
</html>
