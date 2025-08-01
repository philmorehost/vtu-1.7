<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's bonus balance and bank details
$stmt = $pdo->prepare("SELECT bonus_balance, bank_details FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's bonus withdrawal history
$stmt = $pdo->prepare("SELECT * FROM bonus_withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonus Wallet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Bonus Wallet</h1>
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-2">Your Bonus Balance</h2>
            <p class="text-3xl font-bold text-green-500">₦<span id="bonus-balance"><?= htmlspecialchars(number_format($user['bonus_balance'], 2)) ?></span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Withdraw to Bank Account</h2>
                <form action="bonus_withdrawal_actions.php?action=request" method="POST">
                    <div class="mb-4">
                        <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">Amount</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>
                    <div class="mb-4">
                        <label for="bank_details" class="block text-gray-700 text-sm font-bold mb-2">Bank Details</label>
                        <textarea name="bank_details" id="bank_details" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required><?= htmlspecialchars($user['bank_details'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-600 mt-1">Please enter your Account Name, Account Number, and Bank Name.</p>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Request Withdrawal</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Withdraw to Main Wallet</h2>
                <form action="bonus_withdrawal_actions.php?action=wallet" method="POST">
                    <div class="mb-4">
                        <label for="wallet_amount" class="block text-gray-700 text-sm font-bold mb-2">Amount</label>
                        <input type="number" step="0.01" name="amount" id="wallet_amount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Withdraw to Wallet</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Manage Bank Details</h2>
                <form action="user_actions.php?action=update_bank_details" method="POST">
                    <div class="mb-4">
                        <label for="bank_details_manage" class="block text-gray-700 text-sm font-bold mb-2">Your Bank Details</label>
                        <textarea name="bank_details" id="bank_details_manage" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required><?= htmlspecialchars($user['bank_details'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Save Details</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Withdrawal History</h2>
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr>
                                <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($withdrawal['amount'], 2)) ?></td>
                                <td class="text-left py-3 px-4"><?= htmlspecialchars($withdrawal['created_at']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                        <?= $withdrawal['status'] === 'approved' ? 'text-green-700 bg-green-100' : '' ?>
                                        <?= $withdrawal['status'] === 'pending' ? 'text-yellow-700 bg-yellow-100' : '' ?>
                                        <?= $withdrawal['status'] === 'rejected' ? 'text-red-700 bg-red-100' : '' ?>
                                    ">
                                        <?= htmlspecialchars($withdrawal['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const bonusBalance = urlParams.get('bonus_balance');
            if (bonusBalance) {
                document.getElementById('bonus-balance').textContent = parseFloat(bonusBalance).toFixed(2);
            }
        });
    </script>
</body>
</html>