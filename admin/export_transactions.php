<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

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

// Export to CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transactions.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('User', 'Type', 'Amount', 'Balance Before', 'Balance After', 'Source', 'Date', 'Status'));

foreach ($transactions as $transaction) {
    fputcsv($output, [
        $transaction['user_name'],
        $transaction['type'],
        $transaction['amount'],
        $transaction['balance_before'],
        $transaction['balance_after'],
        $transaction['source'],
        $transaction['created_at'],
        $transaction['status']
    ]);
}
fclose($output);
exit();
?>
