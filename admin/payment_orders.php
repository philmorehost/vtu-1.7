<?php
$title = 'Payment Orders';
require_once('includes/header.php');

// Fetch pending payment orders
$stmt = $pdo->query("SELECT po.*, u.name as user_name FROM payment_orders po JOIN users u ON po.user_id = u.id WHERE po.status = 'pending' ORDER BY po.created_at DESC");
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pending_orders as $key => $order) {
    if (isset($order['bank_id'])) {
        $stmt = $pdo->prepare("SELECT bank_name FROM bank_details WHERE id = ?");
        $stmt->execute([$order['bank_id']]);
        $bank = $stmt->fetch(PDO::FETCH_ASSOC);
        $pending_orders[$key]['bank_name'] = $bank ? $bank['bank_name'] : 'N/A';
    } else {
        $pending_orders[$key]['bank_name'] = 'N/A';
    }
}

// Fetch processed payment orders
$stmt = $pdo->query("SELECT po.*, u.name as user_name FROM payment_orders po JOIN users u ON po.user_id = u.id WHERE po.status != 'pending' ORDER BY po.updated_at DESC");
$processed_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Pending Requests -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6 table-container">
    <h2 class="text-2xl font-bold mb-4">Pending Requests</h2>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Bank</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Proof</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($pending_orders as $order): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['user_name']) ?></td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($order['amount'], 2)) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['bank_name']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['payment_proof']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['created_at']) ?></td>
                    <td class="text-left py-3 px-4">
                        <form action="payment_order_actions.php" method="POST" class="inline-block">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <?php if (isset($order['bank_id'])): ?>
                                <input type="hidden" name="bank_id" value="<?= $order['bank_id'] ?>">
                            <?php endif; ?>
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="text-green-500 hover:text-green-700">Approve</button>
                        </form>
                        <form action="payment_order_actions.php" method="POST" class="inline-block ml-4">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="text-red-500 hover:text-red-700">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Processed History -->
<div class="bg-white p-6 rounded-lg shadow-md table-container">
    <h2 class="text-2xl font-bold mb-4">Processed History</h2>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date Processed</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($processed_orders as $order): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['user_name']) ?></td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($order['amount'], 2)) ?></td>
                    <td class="text-left py-3 px-4">
                         <span class="px-2 py-1 font-semibold leading-tight rounded-full
                            <?= $order['status'] === 'approved' ? 'text-green-700 bg-green-100' : '' ?>
                            <?= $order['status'] === 'disapproved' ? 'text-red-700 bg-red-100' : '' ?>
                            <?= $order['status'] === 'cancelled' ? 'text-gray-700 bg-gray-100' : '' ?>
                        ">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once('includes/footer.php'); ?>
