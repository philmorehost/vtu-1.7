<?php
$title = 'Transaction Summary';
require_once('includes/header.php');

// Fetch transaction summary data
$summary = [];
$service_types = ['Airtime', 'Data', 'CableTV', 'Electricity', 'Betting', 'Exam', 'BulkSMS', 'GiftCard', 'Recharge Card'];

foreach ($service_types as $type) {
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_transactions,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Completed' THEN amount ELSE 0 END) as total_revenue
        FROM transactions
        WHERE type = ?
    ");
    $stmt->execute([$type]);
    $summary[$type] = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch other stats
$total_user_balance = $pdo->query("SELECT SUM(wallet_balance) FROM users")->fetchColumn();
$total_bonus_balance = $pdo->query("SELECT SUM(bonus_balance) FROM users")->fetchColumn();
$total_funding = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'Funding' AND status = 'Completed'")->fetchColumn();

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Transaction Summary</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg">
            <h3 class="text-lg font-bold">Total User Wallet Balance</h3>
            <p class="text-2xl mt-2">₦<?= htmlspecialchars(number_format($total_user_balance ?? 0, 2)) ?></p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg">
            <h3 class="text-lg font-bold">Total User Bonus Balance</h3>
            <p class="text-2xl mt-2">₦<?= htmlspecialchars(number_format($total_bonus_balance ?? 0, 2)) ?></p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg">
            <h3 class="text-lg font-bold">Total Funding</h3>
            <p class="text-2xl mt-2">₦<?= htmlspecialchars(number_format($total_funding ?? 0, 2)) ?></p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Service</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Total Transactions</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Completed</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Failed</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Pending</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Total Revenue</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($summary as $service => $stats): ?>
                    <tr class="border-b">
                        <td class="py-2 px-4"><?= htmlspecialchars($service) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($stats['total_transactions']) ?></td>
                        <td class="py-2 px-4 text-green-500"><?= htmlspecialchars($stats['completed']) ?></td>
                        <td class="py-2 px-4 text-red-500"><?= htmlspecialchars($stats['failed']) ?></td>
                        <td class="py-2 px-4 text-yellow-500"><?= htmlspecialchars($stats['pending']) ?></td>
                        <td class="py-2 px-4">₦<?= htmlspecialchars(number_format(abs($stats['total_revenue'] ?? 0), 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once('includes/footer.php'); ?>
