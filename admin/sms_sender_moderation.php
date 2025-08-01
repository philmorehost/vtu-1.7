<?php
$title = 'SMS Sender ID Moderation';
require_once('includes/header.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'moderate':
                $stmt = $pdo->prepare("UPDATE sms_sender_ids SET status = ?, reviewed_by = ?, review_notes = ? WHERE id = ?");
                $stmt->execute([$_POST['status'], $_SESSION['admin_id'], $_POST['review_notes'], $_POST['id']]);
                $success = "Sender ID " . $_POST['status'] . " successfully.";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM sms_sender_ids WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $success = "Sender ID deleted successfully.";
                break;
        }
    }
}

// Fetch all SMS sender IDs
$filter = $_GET['filter'] ?? 'all';
$whereClause = '';
if ($filter !== 'all') {
    $whereClause = "WHERE s.status = ?";
}

$query = "
    SELECT s.*, u.name as user_name, u.email as user_email, a.name as reviewed_by_name 
    FROM sms_sender_ids s 
    LEFT JOIN users u ON s.user_id = u.id 
    LEFT JOIN admins a ON s.reviewed_by = a.id 
    $whereClause
    ORDER BY s.created_at DESC
";

$stmt = $pdo->prepare($query);
if ($filter !== 'all') {
    $stmt->execute([$filter]);
} else {
    $stmt->execute();
}
$senderIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">SMS Sender ID Moderation</h2>
        <div class="flex space-x-2">
            <select onchange="filterStatus(this.value)" class="px-3 py-2 border rounded-lg">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="disapproved" <?= $filter === 'disapproved' ? 'selected' : '' ?>>Disapproved</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Sender ID</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Sample Message</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Submitted</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($senderIds as $senderId): ?>
                    <tr>
                        <td class="text-left py-3 px-4">
                            <div><?= htmlspecialchars($senderId['user_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($senderId['user_email']) ?></div>
                        </td>
                        <td class="text-left py-3 px-4 font-mono font-bold">
                            <?= htmlspecialchars($senderId['sender_id']) ?>
                        </td>
                        <td class="text-left py-3 px-4 max-w-xs">
                            <div class="truncate" title="<?= htmlspecialchars($senderId['sample_message']) ?>">
                                <?= htmlspecialchars(substr($senderId['sample_message'], 0, 100)) . (strlen($senderId['sample_message']) > 100 ? '...' : '') ?>
                            </div>
                            <button onclick="showMessage('<?= htmlspecialchars(addslashes($senderId['sample_message'])) ?>')" class="text-blue-500 text-xs hover:underline mt-1">
                                View Full Message
                            </button>
                        </td>
                        <td class="text-left py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                <?php 
                                switch($senderId['status']) {
                                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'approved': echo 'bg-green-100 text-green-800'; break;
                                    case 'disapproved': echo 'bg-red-100 text-red-800'; break;
                                }
                                ?>">
                                <?= htmlspecialchars(ucfirst($senderId['status'])) ?>
                            </span>
                            <?php if ($senderId['status'] !== 'pending' && $senderId['reviewed_by_name']): ?>
                                <div class="text-xs text-gray-500 mt-1">by <?= htmlspecialchars($senderId['reviewed_by_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-left py-3 px-4"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($senderId['created_at']))) ?></td>
                        <td class="text-left py-3 px-4">
                            <?php if ($senderId['status'] === 'pending'): ?>
                                <button onclick="moderateModal(<?= htmlspecialchars(json_encode($senderId)) ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm mr-2">
                                    Review
                                </button>
                            <?php else: ?>
                                <button onclick="moderateModal(<?= htmlspecialchars(json_encode($senderId)) ?>)" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-sm mr-2">
                                    Edit Review
                                </button>
                            <?php endif; ?>
                            <button onclick="deleteSenderId(<?= $senderId['id'] ?>)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($senderIds)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No SMS sender IDs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Moderation Modal -->
<div id="moderationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-lg font-bold mb-4">Review Sender ID</h3>
        <form method="POST">
            <input type="hidden" name="action" value="moderate">
            <input type="hidden" name="id" id="modId">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Sender ID</label>
                <div id="modSenderId" class="px-3 py-2 bg-gray-100 rounded font-mono"></div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Sample Message</label>
                <div id="modSampleMessage" class="px-3 py-2 bg-gray-100 rounded h-20 overflow-y-auto text-sm"></div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                <select name="status" id="modStatus" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="approved">Approve</option>
                    <option value="disapproved">Disapprove</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Review Notes</label>
                <textarea name="review_notes" id="modNotes" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" placeholder="Optional notes about this decision"></textarea>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModerationModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Save Review
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96 max-w-lg">
        <h3 class="text-lg font-bold mb-4">Full Sample Message</h3>
        <div id="fullMessage" class="px-3 py-2 bg-gray-100 rounded h-40 overflow-y-auto"></div>
        <div class="flex justify-end mt-4">
            <button onclick="closeMessageModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function filterStatus(status) {
    window.location.href = '?filter=' + status;
}

function moderateModal(senderId) {
    document.getElementById('modId').value = senderId.id;
    document.getElementById('modSenderId').textContent = senderId.sender_id;
    document.getElementById('modSampleMessage').textContent = senderId.sample_message;
    document.getElementById('modStatus').value = senderId.status === 'pending' ? 'approved' : senderId.status;
    document.getElementById('modNotes').value = senderId.review_notes || '';
    document.getElementById('moderationModal').classList.remove('hidden');
    document.getElementById('moderationModal').classList.add('flex');
}

function showMessage(message) {
    document.getElementById('fullMessage').textContent = message;
    document.getElementById('messageModal').classList.remove('hidden');
    document.getElementById('messageModal').classList.add('flex');
}

function deleteSenderId(id) {
    if (confirm('Are you sure you want to delete this sender ID? This action cannot be undone.')) {
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

function closeModerationModal() {
    document.getElementById('moderationModal').classList.add('hidden');
    document.getElementById('moderationModal').classList.remove('flex');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageModal').classList.remove('flex');
}
</script>

<?php require_once('includes/footer.php'); ?>