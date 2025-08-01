<?php
$title = 'Bank Settings';
require_once('includes/header.php');

// Fetch bank details
$stmt = $pdo->query("SELECT * FROM bank_details");
$bank_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch payment orders
$stmt = $pdo->query("SELECT po.*, u.name FROM payment_orders po JOIN users u ON po.user_id = u.id ORDER BY po.created_at DESC");
$payment_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Add New Bank -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold mb-4">Add New Bank</h2>
    <form action="bank_actions.php?action=add" method="POST">
        <?php require_once('csrf_token.php'); ?>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="bank_name" placeholder="Bank Name" class="border rounded py-2 px-3" required>
            <input type="text" name="account_name" placeholder="Account Name" class="border rounded py-2 px-3" required>
            <input type="text" name="account_number" placeholder="Account Number" class="border rounded py-2 px-3" required>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Charge</label>
            <input type="number" step="0.01" name="charge" placeholder="Charge" class="border rounded py-2 px-3" required>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Instructions</label>
            <textarea name="instructions" placeholder="Payment instructions..." class="border rounded py-2 px-3 w-full"></textarea>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Bank</button>
        </div>
    </form>
</div>

<!-- Existing Banks -->
<div class="bg-white p-6 rounded-lg shadow-md table-container">
    <h2 class="text-2xl font-bold mb-4">Existing Banks</h2>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Bank Name</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Account Name</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Account Number</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Charge</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($bank_details as $bank): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($bank['bank_name']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($bank['account_name']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($bank['account_number']) ?></td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($bank['charge'], 2)) ?></td>
                    <td class="text-left py-3 px-4">
                        <a href="bank_actions.php?action=delete&id=<?= $bank['id'] ?>" class="text-red-500 hover:text-red-700">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Payment Orders -->
<div class="bg-white p-6 rounded-lg shadow-md mt-6 table-container">
    <h2 class="text-2xl font-bold mb-4">Payment Orders</h2>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Proof</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($payment_orders as $order): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['name']) ?></td>
                    <td class="text-left py-3 px-4">₦<?= htmlspecialchars(number_format($order['amount'], 2)) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['payment_proof']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['status']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($order['created_at']) ?></td>
                    <td class="text-left py-3 px-4">
                        <?php if ($order['status'] === 'pending'): ?>
                            <form action="payment_order_actions.php" method="POST" class="inline">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">Approve</button>
                            </form>
                            <form action="payment_order_actions.php" method="POST" class="inline">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once('includes/footer.php'); ?>
