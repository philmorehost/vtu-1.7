<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// For simplicity, we're reading from the log file.
// In a real application, you would have a 'transactions' table in your database.
$log_file = '../webhook.log';
$transactions = [];
if (file_exists($log_file)) {
    $raw_logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($raw_logs as $log) {
        $data = json_decode($log, true);
        if ($data && isset($data['event']) && $data['event'] === 'charge.success') {
            $transactions[] = $data['data'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #111827; color: #fff; display: flex; flex-direction: column; min-height: 100vh; }
        .sidebar h1 { font-size: 1.5rem; padding: 1.5rem; text-align: center; background: #1f2937; margin: 0; }
        .sidebar nav a { display: block; padding: 1rem 1.5rem; color: #d1d5db; text-decoration: none; transition: background-color 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: #374151; color: #fff; }
        .main-content { flex-grow: 1; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header h2 { font-size: 1.875rem; color: #111827; margin: 0; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
        .card-body { padding: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>License Manager</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="licenses.php">Licenses</a>
            <a href="transactions.php" class="active">Transactions</a>
            <a href="settings.php">Settings</a>
            <a href="#">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="header">
            <h2>Transaction History</h2>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>All Transactions</h3>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Domain</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($transactions) as $tx): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($tx['paid_at']))) ?></td>
                                <td><?= htmlspecialchars($tx['customer']['email']) ?></td>
                                <td><?= htmlspecialchars($tx['amount'] / 100) ?> <?= htmlspecialchars($tx['currency']) ?></td>
                                <td><?= htmlspecialchars($tx['reference']) ?></td>
                                <td><?= htmlspecialchars($tx['metadata']['domain'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
