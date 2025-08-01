<?php
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    // Fetch transactions
    $stmt = $pdo->prepare("
        SELECT * FROM transactions
        WHERE user_id = :user_id
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of transactions for pagination
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_transactions = $stmt->fetchColumn();

    echo json_encode([
        'transactions' => $transactions,
        'total' => $total_transactions,
        'limit' => $limit,
        'offset' => $offset
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>