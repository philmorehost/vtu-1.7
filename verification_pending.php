<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

// Check if user just registered
if (!isset($_SESSION['registration_success']) || !isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['user_email'];
unset($_SESSION['registration_success']);
unset($_SESSION['user_email']);

// Get site settings
$stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = ?");
$stmt->execute([1]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="hidden md:block w-1/2 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($settings['auth_image'] ?? 'assets/images/auth-bg.jpg') ?>');"></div>
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 sm:p-6 md:p-8">
            <div class="w-full max-w-md">
                <div class="bg-white shadow-lg rounded-lg px-6 sm:px-8 pt-6 pb-8 mb-4" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                    <div class="flex justify-center mb-6">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo" class="h-16">
                        <?php else: ?>
                            <h2 class="text-2xl font-bold text-center"><?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?></h2>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                            <i class="fas fa-envelope text-yellow-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Verify Your Email</h2>
                        <p class="text-gray-600">We've sent a verification link to:</p>
                        <p class="text-blue-600 font-semibold"><?= htmlspecialchars($email) ?></p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">
                                    Check Your Email
                                </h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    Click the verification link in your email to activate your account. The link will expire in 24 hours.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button id="resend-btn" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <span id="resend-text">Resend Verification Email</span>
                            <span id="resend-spinner" class="hidden">
                                <i class="fas fa-spinner fa-spin"></i> Sending...
                            </span>
                        </button>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-blue-500 hover:text-blue-800 text-sm">
                                Back to Login
                            </a>
                        </div>
                    </div>

                    <div id="message-container" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('resend-btn').addEventListener('click', function() {
            const button = this;
            const text = document.getElementById('resend-text');
            const spinner = document.getElementById('resend-spinner');
            const messageContainer = document.getElementById('message-container');
            
            button.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
            
            fetch('resend_verification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: '<?= addslashes($email) ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageContainer.innerHTML = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><span class="block sm:inline">' + data.message + '</span></div>';
                } else {
                    messageContainer.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><span class="block sm:inline">' + data.message + '</span></div>';
                }
            })
            .catch(error => {
                messageContainer.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><span class="block sm:inline">An error occurred. Please try again.</span></div>';
            })
            .finally(() => {
                button.disabled = false;
                text.classList.remove('hidden');
                spinner.classList.add('hidden');
            });
        });
    </script>
</body>
</html>