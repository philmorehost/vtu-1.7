<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php require_once('csrf_token.php'); ?>
<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-200">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white p-4">
            <h2 class="text-2xl font-bold mb-6">Admin Panel</h2>
            <nav>
                <a href="dashboard.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Dashboard</a>
                <a href="users.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Users</a>
                <a href="transactions.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Transactions</a>
                <a href="payment_orders.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Payment Orders</a>
                <a href="services.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Services</a>
                <a href="bank_settings.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Bank Settings</a>
                <a href="site_settings.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Site Settings</a>
                <a href="logout.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold mb-6">Create New User</h1>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="user_actions.php?action=create" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Full Name</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="name" name="name" type="text" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="email" name="email" type="email" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="phone" name="phone" type="text" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="password" name="password" type="password" required>
                    </div>
                    <div class="mt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
