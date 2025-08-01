<?php
$title = 'Transaction Limits';
require_once('includes/header.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $stmt = $pdo->prepare("INSERT INTO transaction_limits (identifier_type, max_transactions, period_type, created_by) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['identifier_type'], $_POST['max_transactions'], $_POST['period_type'], $_SESSION['admin_id']]);
                $success = "Transaction limit created successfully.";
                break;
            case 'update':
                $stmt = $pdo->prepare("UPDATE transaction_limits SET max_transactions = ?, period_type = ? WHERE id = ?");
                $stmt->execute([$_POST['max_transactions'], $_POST['period_type'], $_POST['id']]);
                $success = "Transaction limit updated successfully.";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM transaction_limits WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $success = "Transaction limit deleted successfully.";
                break;
        }
    }
}

// Fetch all transaction limits
$stmt = $pdo->query("
    SELECT tl.*, a.name as created_by_name 
    FROM transaction_limits tl 
    LEFT JOIN admins a ON tl.created_by = a.id 
    ORDER BY tl.identifier_type, tl.period_type
");
$limits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Transaction Limits Management</h2>
        <button onclick="showCreateModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Add New Limit
        </button>
    </div>

    <div class="table-container">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Identifier Type</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Max Transactions</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Period</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Created By</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Created At</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($limits as $limit): ?>
                    <tr>
                        <td class="text-left py-3 px-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $limit['identifier_type']))) ?>
                            </span>
                        </td>
                        <td class="text-left py-3 px-4 font-semibold"><?= htmlspecialchars($limit['max_transactions']) ?></td>
                        <td class="text-left py-3 px-4"><?= htmlspecialchars(ucfirst($limit['period_type'])) ?></td>
                        <td class="text-left py-3 px-4"><?= htmlspecialchars($limit['created_by_name'] ?? 'Unknown') ?></td>
                        <td class="text-left py-3 px-4"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($limit['created_at']))) ?></td>
                        <td class="text-left py-3 px-4">
                            <button onclick="editLimit(<?= htmlspecialchars(json_encode($limit)) ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm mr-2">
                                Edit
                            </button>
                            <button onclick="deleteLimit(<?= $limit['id'] ?>)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($limits)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No transaction limits configured yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="limitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 id="modalTitle" class="text-lg font-bold mb-4">Add Transaction Limit</h3>
        <form id="limitForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="limitId">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Identifier Type</label>
                <select name="identifier_type" id="identifierType" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">Select Type</option>
                    <option value="phone">Phone Number</option>
                    <option value="meter_number">Electricity Meter Number</option>
                    <option value="smartcard_number">Cable TV Smart Card Number</option>
                    <option value="betting_id">Betting ID</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Maximum Transactions</label>
                <input type="number" name="max_transactions" id="maxTransactions" min="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Period</label>
                <select name="period_type" id="periodType" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">Select Period</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add Transaction Limit';
    document.getElementById('formAction').value = 'create';
    document.getElementById('limitId').value = '';
    document.getElementById('identifierType').value = '';
    document.getElementById('maxTransactions').value = '';
    document.getElementById('periodType').value = '';
    document.getElementById('limitModal').classList.remove('hidden');
    document.getElementById('limitModal').classList.add('flex');
}

function editLimit(limit) {
    document.getElementById('modalTitle').textContent = 'Edit Transaction Limit';
    document.getElementById('formAction').value = 'update';
    document.getElementById('limitId').value = limit.id;
    document.getElementById('identifierType').value = limit.identifier_type;
    document.getElementById('maxTransactions').value = limit.max_transactions;
    document.getElementById('periodType').value = limit.period_type;
    document.getElementById('limitModal').classList.remove('hidden');
    document.getElementById('limitModal').classList.add('flex');
}

function deleteLimit(id) {
    if (confirm('Are you sure you want to delete this transaction limit?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeModal() {
    document.getElementById('limitModal').classList.add('hidden');
    document.getElementById('limitModal').classList.remove('flex');
}
</script>

<?php require_once('includes/footer.php'); ?>