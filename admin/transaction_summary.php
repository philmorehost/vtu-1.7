<?php
$title = 'Transaction Summary';
require_once('includes/header.php');
require_once('../includes/db.php');

// --- Fetch all the required statistics ---

// 1. Total User Wallet & Bonus Balance
$balances = $pdo->query("SELECT SUM(wallet_balance) as total_wallet, SUM(bonus_balance) as total_bonus FROM users")->fetch(PDO::FETCH_ASSOC);
$totalWalletBalance = $balances['total_wallet'] ?? 0;
$totalBonusBalance = $balances['total_bonus'] ?? 0;

// 2. Total Funding
// Assuming 'funding' are positive transactions or a specific type like 'deposit'
$totalFunding = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'wallet_funding' OR (type = 'deposit' AND status = 'Completed')")->fetchColumn() ?? 0;

// 3. Transaction counts (successful, failed, pending)
$transactionCounts = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
    FROM transactions
")->fetch(PDO::FETCH_ASSOC);

// 4. Summary of sales for each service
$salesSummary = $pdo->query("
    SELECT
        type,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM transactions
    WHERE amount < 0 AND status = 'Completed'
    GROUP BY type
    ORDER BY total_amount ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Page Title -->
<h1 class="text-3xl font-bold text-gray-800 mb-6">Transaction Summary</h1>

<!-- Top Row: Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Wallet Balances Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-gray-700 mb-4">User Balances</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Total Wallet Balance:</span>
                <span class="font-semibold text-lg text-green-600">₦<?= number_format($totalWalletBalance, 2) ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Total Bonus Balance:</span>
                <span class="font-semibold text-lg text-blue-600">₦<?= number_format($totalBonusBalance, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Total Funding Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-gray-700 mb-4">Total Funding</h3>
        <p class="text-3xl font-bold text-green-700 mt-2">₦<?= number_format($totalFunding, 2) ?></p>
        <p class="text-sm text-gray-500">Total amount funded by users.</p>
    </div>

    <!-- Transaction Status Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-gray-700 mb-4">Transaction Status</h3>
        <div class="space-y-2">
            <p class="flex justify-between"><span>Total:</span> <span class="font-bold"><?= number_format($transactionCounts['total'] ?? 0) ?></span></p>
            <p class="flex justify-between text-green-600"><span>Successful:</span> <span class="font-bold"><?= number_format($transactionCounts['successful'] ?? 0) ?></span></p>
            <p class="flex justify-between text-red-600"><span>Failed:</span> <span class="font-bold"><?= number_format($transactionCounts['failed'] ?? 0) ?></span></p>
            <p class="flex justify-between text-yellow-600"><span>Pending:</span> <span class="font-bold"><?= number_format($transactionCounts['pending'] ?? 0) ?></span></p>
        </div>
    </div>
</div>

<!-- Bottom Row: Sales by Service -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Sales Summary by Service</h3>
        <p class="text-sm text-gray-600">Breakdown of completed sales for each service type.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Service Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Number of Sales</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Total Sales Value</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($salesSummary)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No sales data available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($salesSummary as $sale): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900"><?= htmlspecialchars(ucfirst($sale['type'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= number_format($sale['count']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">
                                ₦<?= number_format(abs($sale['total_amount']), 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php
require_once('includes/footer.php');
?>
