<?php
$title = 'Manage Users';
require_once('includes/header.php');

// Fetch all users
$stmt = $pdo->query("SELECT u.id, u.name, u.email, u.phone, u.wallet_balance, u.status, u.last_login, r.name as referrer_name, COUNT(t.id) as total_transactions FROM users u LEFT JOIN users r ON u.referred_by = r.id LEFT JOIN transactions t ON u.id = t.user_id GROUP BY u.id ORDER BY u.created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white p-6 rounded-lg shadow-md table-container">
    <a href="user_create.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Create New User
    </a>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Name</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Email / Phone</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Balance</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Total Transactions</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Last Login</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Referred By</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($user['name']) ?></td>
                    <td class="text-left py-3 px-4">
                        <div><?= htmlspecialchars($user['email']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($user['phone']) ?></div>
                    </td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($user['wallet_balance'], 2)) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($user['total_transactions']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($user['last_login'] ?? 'N/A') ?></td>
                    <td class="text-left py-3 px-4">
                        <span class="px-2 py-1 font-semibold leading-tight <?= $user['status'] === 'active' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100' ?> rounded-full">
                            <?= htmlspecialchars($user['status']) ?>
                        </span>
                    </td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($user['referrer_name'] ?? 'N/A') ?></td>
                    <td class="text-left py-3 px-4">
                        <a href="user_edit.php?id=<?= $user['id'] ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-2 inline-block">Edit</a>
                        <a href="user_login.php?id=<?= $user['id'] ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Login</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once('includes/footer.php'); ?>
