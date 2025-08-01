<?php
$title = 'Bank Withdrawals';
require_once('includes/header.php');

// Fetch all bank withdrawal requests
$stmt = $pdo->query("
    SELECT bw.*, u.name as user_name
    FROM bonus_withdrawals bw
    JOIN users u ON bw.user_id = u.id
    WHERE bw.bank_details IS NOT NULL
    ORDER BY bw.created_at DESC
");
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white p-6 rounded-lg shadow-md table-container">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Bank Details</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($withdrawals as $withdrawal): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($withdrawal['user_name']) ?></td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($withdrawal['amount'], 2)) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($withdrawal['bank_details']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($withdrawal['created_at']) ?></td>
                    <td class="text-left py-3 px-4">
                        <span class="px-2 py-1 font-semibold leading-tight rounded-full
                            <?= $withdrawal['status'] === 'approved' ? 'text-green-700 bg-green-100' : '' ?>
                            <?= $withdrawal['status'] === 'pending' ? 'text-yellow-700 bg-yellow-100' : '' ?>
                            <?= $withdrawal['status'] === 'rejected' ? 'text-red-700 bg-red-100' : '' ?>
                            <?= $withdrawal['status'] === 'cancelled' ? 'text-gray-700 bg-gray-100' : '' ?>
                        ">
                            <?= htmlspecialchars($withdrawal['status']) ?>
                        </span>
                    </td>
                    <td class="text-left py-3 px-4">
                        <?php if ($withdrawal['status'] === 'pending'): ?>
                            <form action="bank_withdrawal_actions.php" method="POST" class="inline-block">
                                <input type="hidden" name="withdrawal_id" value="<?= $withdrawal['id'] ?>">
                                <button type="submit" name="action" value="approve" class="text-green-500 hover:text-green-700">Approve</button>
                            </form>
                            <form action="bank_withdrawal_actions.php" method="POST" class="inline-block">
                                <input type="hidden" name="withdrawal_id" value="<?= $withdrawal['id'] ?>">
                                <button type="submit" name="action" value="reject" class="text-red-500 hover:text-red-700">Reject</button>
                            </form>
                            <form action="bank_withdrawal_actions.php" method="POST" class="inline-block">
                                <input type="hidden" name="withdrawal_id" value="<?= $withdrawal['id'] ?>">
                                <button type="submit" name="action" value="cancel" class="text-gray-500 hover:text-gray-700">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once('includes/footer.php'); ?>
