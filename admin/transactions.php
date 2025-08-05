<?php
$title = 'Manage Transactions';
require_once('includes/header.php');

// Base query
$sql = "SELECT t.*, u.name AS user_name FROM transactions t JOIN users u ON t.user_id = u.id";
$where = [];
$params = [];

// Filtering logic
if (!empty($_GET['filter'])) {
    $filter = $_GET['filter'];
    $today = date('Y-m-d');
    if ($filter === 'today') {
        $where[] = "DATE(t.created_at) = ?";
        $params[] = $today;
    } elseif ($filter === 'week') {
        $start_of_week = date('Y-m-d', strtotime('monday this week'));
        $where[] = "t.created_at >= ?";
        $params[] = $start_of_week;
    } elseif ($filter === 'month') {
        $start_of_month = date('Y-m-01');
        $where[] = "t.created_at >= ?";
        $params[] = $start_of_month;
    }
}

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $where[] = "DATE(t.created_at) BETWEEN ? AND ?";
    $params[] = $_GET['start_date'];
    $params[] = $_GET['end_date'];
}

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where[] = "(u.name LIKE ? OR t.type LIKE ? OR t.status LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch batch transactions
$batchSql = "SELECT batch_id, user_id, type,
                    COUNT(CASE WHEN status = 'Completed' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'Failed' THEN 1 END) as failed_count,
                    COUNT(CASE WHEN status = 'Processing' THEN 1 END) as processing_count,
                    MAX(created_at) as date
             FROM transactions
             WHERE batch_id IS NOT NULL
             GROUP BY batch_id, user_id, type
             ORDER BY date DESC";
$batchStmt = $pdo->query($batchSql);
$batches = $batchStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Filter and Export Controls -->
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form action="transactions.php" method="GET">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div class="flex-grow">
                <!-- Search Box -->
                <input type="text" name="search" placeholder="Search transactions..." class="border rounded py-2 px-4 w-full" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="flex flex-wrap gap-2">
                <!-- Date Filters -->
                <a href="transactions.php?filter=today" class="bg-blue-500 text-white py-2 px-4 rounded">Today</a>
                <a href="transactions.php?filter=week" class="bg-blue-500 text-white py-2 px-4 rounded">This Week</a>
                <a href="transactions.php?filter=month" class="bg-blue-500 text-white py-2 px-4 rounded">This Month</a>
            </div>
            <div class="flex flex-wrap gap-2">
                <input type="date" name="start_date" class="border rounded py-2 px-4" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                <input type="date" name="end_date" class="border rounded py-2 px-4" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Filter</button>
            </div>
            <div>
                <!-- Export/Import -->
                <a href="export_transactions.php?<?= http_build_query($_GET) ?>" class="bg-green-500 text-white py-2 px-4 rounded">Export CSV</a>
            </div>
        </div>
    </form>
</div>

<div class="flex items-center justify-end mb-4">
    <span class="text-sm font-medium text-gray-900 mr-3">Batch View</span>
    <label for="toggle-batch" class="inline-flex relative items-center cursor-pointer">
        <input type="checkbox" value="" id="toggle-batch" class="sr-only peer">
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
    </label>
</div>

<div id="individual-view" class="bg-white p-6 rounded-lg shadow-md table-container">
    <table class="min-w-full bg-white" id="transactionsTable">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Type</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Balances (Before/After)</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Source</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($transaction['user_name']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($transaction['type']) ?></td>
                    <td class="text-left py-3 px-4">
                        <span class="<?= $transaction['amount'] < 0 ? 'text-red-500' : 'text-green-500' ?>">
                            ₦<?= htmlspecialchars(number_format(abs($transaction['amount']), 2)) ?>
                        </span>
                    </td>
                    <td class="text-left py-3 px-4">
                        ₦<?= htmlspecialchars(number_format($transaction['balance_before'], 2)) ?> /
                        ₦<?= htmlspecialchars(number_format($transaction['balance_after'], 2)) ?>
                    </td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($transaction['source']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($transaction['created_at']) ?></td>
                    <td class="text-left py-3 px-4">
                        <span class="px-2 py-1 font-semibold leading-tight rounded-full
                            <?= $transaction['status'] === 'Completed' ? 'text-green-700 bg-green-100' : '' ?>
                            <?= $transaction['status'] === 'Processing' ? 'text-yellow-700 bg-yellow-100' : '' ?>
                            <?= $transaction['status'] === 'Failed' ? 'text-red-700 bg-red-100' : '' ?>
                            <?= $transaction['status'] === 'Cancelled' ? 'text-gray-700 bg-gray-100' : '' ?>
                        ">
                            <?= htmlspecialchars($transaction['status']) ?>
                        </span>
                    </td>
                    <td class="text-left py-3 px-4">
                        <button class="details-btn bg-purple-500 text-white px-2 py-1 rounded hover:bg-purple-600" data-id="<?= $transaction['id'] ?>">Details</button>
                        <?php if ($transaction['status'] === 'Processing'): ?>
                            <a href="transaction_actions.php?action=cancel&id=<?= $transaction['id'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to cancel this transaction?');">Cancel</a>
                        <?php elseif ($transaction['status'] === 'Completed'): ?>
                            <a href="transaction_actions.php?action=fail&id=<?= $transaction['id'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to fail this transaction? This will refund the user.');">Fail</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Details Modal -->
<div id="details-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Transaction Details</h3>
            <div class="mt-2 px-7 py-3">
                <div id="modal-content" class="text-sm text-gray-500 text-left">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <button id="close-modal" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<div id="batch-view" class="hidden bg-white p-6 rounded-lg shadow-md table-container">
    <h2 class="text-2xl font-bold mb-4">Batch Transactions</h2>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Batch ID</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Type</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Counts (Success/Failed/Processing)</th>
                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php foreach ($batches as $batch): ?>
                <tr>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($batch['batch_id']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($batch['user_id']) ?></td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($batch['type']) ?></td>
                    <td class="text-left py-3 px-4">
                        <span class="text-green-500"><?= $batch['success_count'] ?></span> /
                        <span class="text-red-500"><?= $batch['failed_count'] ?></span> /
                        <span class="text-yellow-500"><?= $batch['processing_count'] ?></span>
                    </td>
                    <td class="text-left py-3 px-4"><?= htmlspecialchars($batch['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Toggle between individual and batch view
    const toggleBatch = document.getElementById('toggle-batch');
    const individualView = document.getElementById('individual-view');
    const batchView = document.getElementById('batch-view');

    toggleBatch.addEventListener('change', function() {
        if (this.checked) {
            individualView.style.display = 'none';
            batchView.style.display = 'block';
        } else {
            individualView.style.display = 'block';
            batchView.style.display = 'none';
        }
    });

    // Modal handling
    const modal = document.getElementById('details-modal');
    const closeModalBtn = document.getElementById('close-modal');
    const modalContent = document.getElementById('modal-content');
    const detailsButtons = document.querySelectorAll('.details-btn');

    detailsButtons.forEach(button => {
        button.addEventListener('click', () => {
            const transactionId = button.dataset.id;
            modalContent.innerHTML = '<p>Loading...</p>';
            modal.classList.remove('hidden');

            fetch(`../api/admin_transaction_details.php?id=${transactionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const txn = data.data;
                        let detailsHtml = '<dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">';
                        for (const key in txn) {
                            if (key === 'service_details' && typeof txn[key] === 'object') {
                                detailsHtml += `<dt class="font-bold col-span-2 mt-2">Service Details:</dt>`;
                                for (const serviceKey in txn[key]) {
                                    detailsHtml += `<dt class="font-medium text-gray-500">${serviceKey}</dt><dd class="text-gray-900">${txn[key][serviceKey]}</dd>`;
                                }
                            } else {
                                detailsHtml += `<dt class="font-medium text-gray-500">${key}</dt><dd class="text-gray-900">${txn[key]}</dd>`;
                            }
                        }
                        detailsHtml += '</dl>';
                        modalContent.innerHTML = detailsHtml;
                    } else {
                        modalContent.innerHTML = `<p class="text-red-500">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching transaction details:', error);
                    modalContent.innerHTML = '<p class="text-red-500">An error occurred while fetching details.</p>';
                });
        });
    });

    closeModalBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Close modal if clicking outside of it
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.classList.add('hidden');
        }
    });
</script>

<?php require_once('includes/footer.php'); ?>
