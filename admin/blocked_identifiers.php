<?php
$title = 'Blocked Identifiers';
require_once('includes/header.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'block':
                try {
                    $stmt = $pdo->prepare("INSERT INTO blocked_identifiers (identifier_type, identifier_value, reason, blocked_by) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_POST['identifier_type'], $_POST['identifier_value'], $_POST['reason'], $_SESSION['admin_id']]);
                    $success = "Identifier blocked successfully.";
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = "This identifier is already blocked.";
                    } else {
                        $error = "Error blocking identifier: " . $e->getMessage();
                    }
                }
                break;
            case 'unblock':
                $stmt = $pdo->prepare("DELETE FROM blocked_identifiers WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $success = "Identifier unblocked successfully.";
                break;
        }
    }
}

// Fetch all blocked identifiers
$stmt = $pdo->query("
    SELECT bi.*, a.name as blocked_by_name 
    FROM blocked_identifiers bi 
    LEFT JOIN admins a ON bi.blocked_by = a.id 
    ORDER BY bi.blocked_at DESC
");
$blockedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Blocked Identifiers Management</h2>
        <button onclick="showBlockModal()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
            Block Identifier
        </button>
    </div>

    <div class="table-container">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Type</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Identifier</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Reason</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Blocked By</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Blocked At</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($blockedItems as $item): ?>
                    <tr>
                        <td class="text-left py-3 px-4">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['identifier_type']))) ?>
                            </span>
                        </td>
                        <td class="text-left py-3 px-4 font-mono">
                            <?= htmlspecialchars($item['identifier_value']) ?>
                        </td>
                        <td class="text-left py-3 px-4">
                            <?= htmlspecialchars($item['reason']) ?>
                        </td>
                        <td class="text-left py-3 px-4"><?= htmlspecialchars($item['blocked_by_name'] ?? 'Unknown') ?></td>
                        <td class="text-left py-3 px-4"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($item['blocked_at']))) ?></td>
                        <td class="text-left py-3 px-4">
                            <button onclick="unblockIdentifier(<?= $item['id'] ?>)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Unblock
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($blockedItems)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No blocked identifiers yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Block Modal -->
<div id="blockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-lg font-bold mb-4">Block Identifier</h3>
        <form method="POST">
            <input type="hidden" name="action" value="block">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Identifier Type</label>
                <select name="identifier_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">Select Type</option>
                    <option value="phone">Phone Number</option>
                    <option value="meter_number">Electricity Meter Number</option>
                    <option value="smartcard_number">Cable TV Smart Card Number</option>
                    <option value="betting_id">Betting ID</option>
                    <option value="sms_sender_id">SMS Sender ID</option>
                    <option value="sms_keyword">SMS Keyword</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Identifier Value</label>
                <input type="text" name="identifier_value" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" placeholder="e.g., 08012345678" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Reason</label>
                <textarea name="reason" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" placeholder="Reason for blocking this identifier"></textarea>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeBlockModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </button>
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Block
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showBlockModal() {
    document.getElementById('blockModal').classList.remove('hidden');
    document.getElementById('blockModal').classList.add('flex');
}

function unblockIdentifier(id) {
    if (confirm('Are you sure you want to unblock this identifier?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="unblock">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeBlockModal() {
    document.getElementById('blockModal').classList.add('hidden');
    document.getElementById('blockModal').classList.remove('flex');
}
</script>

<?php require_once('includes/footer.php'); ?>