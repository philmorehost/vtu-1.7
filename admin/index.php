<?php
require_once('../includes/session_config.php');
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}
require_once('../includes/db.php');
$stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = ?");
$stmt->execute([1]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= htmlspecialchars($settings['site_name'] ?? '') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="hidden md:block w-1/2 bg-cover bg-center" style="background-image: url('../<?= htmlspecialchars($settings['auth_image'] ?? 'assets/images/auth-bg.jpg') ?>');"></div>
        <div class="w-full md:w-1/2 flex items-center justify-center">
            <div class="w-full max-w-md p-8">
                <form action="auth.php" method="POST" class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-4" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                    <div class="flex justify-center mb-6">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo" class="h-16">
                        <?php else: ?>
                            <h2 class="text-2xl font-bold text-center"><?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?></h2>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-2xl font-bold text-center mb-6">Admin Login</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="Email" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="******************" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>