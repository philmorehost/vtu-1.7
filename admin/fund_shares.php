<?php
$title = 'Fund Shares';
require_once('includes/header.php');

// Fetch all fund share requests
$stmt = $pdo->query("SELECT fs.*, s.name as sender_name, r.name as recipient_name FROM fund_shares fs JOIN users s ON fs.sender_id = s.id JOIN users r ON fs.recipient_id = r.id ORDER BY fs.created_at DESC");
$shares = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white p-6 rounded-lg shadow-md table-container">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Sender</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Recipient</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($shares as $share): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($share['sender_name']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($share['recipient_name']) ?></td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($share['amount'], 2)) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($share['created_at']) ?></td>
                    <td class="text-left py-3 px-4">
                        <span class="px-2 py-1 font-semibold leading-tight rounded-full
                            <?= $share['status'] === 'approved' ? 'text-green-700 bg-green-100' : '' ?>
                            <?= $share['status'] === 'pending' ? 'text-yellow-700 bg-yellow-100' : '' ?>
                            <?= $share['status'] === 'rejected' ? 'text-red-700 bg-red-100' : '' ?>
                            <?= $share['status'] === 'cancelled' ? 'text-gray-700 bg-gray-100' : '' ?>
                        ">
                            <?= htmlspecialchars($share['status']) ?>
                        </span>
                    </td>
                    <td class="text-left py-3 px-4">
                        <?php if ($share['status'] === 'pending'): ?>
                            <form action="fund_share_actions.php" method="POST" class="inline-block">
                                <input type="hidden" name="share_id" value="<?= $share['id'] ?>">
                                <button type="submit" name="action" value="approve" class="text-green-500 hover:text-green-700">Approve</button>
                            </form>
                            <form action="fund_share_actions.php" method="POST" class="inline-block">
                                <input type="hidden" name="share_id" value="<?= $share['id'] ?>">
                                <button type="submit" name="action" value="reject" class="text-red-500 hover:text-red-700">Reject</button>
                            </form>
                            <form action="fund_share_actions.php" method="POST" class="inline-block">
                                <input type="hidden" name="share_id" value="<?= $share['id'] ?>">
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
