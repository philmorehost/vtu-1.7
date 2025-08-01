<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

// Get site settings
$stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = ?");
$stmt->execute([1]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$success = isset($_GET['success']);
$error = $_GET['error'] ?? null;
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
                        <?php if ($success): ?>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                <i class="fas fa-check text-green-600 text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Email Verified!</h2>
                            <p class="text-gray-600 mb-4">Your email has been successfully verified. You can now log in to your account.</p>
                        <?php elseif ($error === 'expired'): ?>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <i class="fas fa-clock text-red-600 text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Link Expired</h2>
                            <p class="text-gray-600 mb-4">Your verification link has expired. Please request a new one.</p>
                        <?php elseif ($error === 'invalid'): ?>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <i class="fas fa-times text-red-600 text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Invalid Link</h2>
                            <p class="text-gray-600 mb-4">The verification link is invalid or has already been used.</p>
                        <?php else: ?>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Verification Failed</h2>
                            <p class="text-gray-600 mb-4">An error occurred during verification. Please try again or contact support.</p>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-4">
                        <?php if ($success): ?>
                            <a href="login.php" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline text-center block">
                                Continue to Login
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline text-center block">
                                Back to Registration
                            </a>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-blue-500 hover:text-blue-800 text-sm">
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
