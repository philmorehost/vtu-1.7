<?php
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$transaction_id = $_GET['id'] ?? null;

if (!$transaction_id) {
    echo json_encode(['error' => 'Transaction ID not provided']);
    exit();
}

try {
    if (isset($_GET['requery']) && $_GET['requery'] === 'true') {
        require_once('../includes/ModularApiGateway.php');
        $gateway = new ModularApiGateway($pdo);
        $result = $gateway->requeryTransaction($transaction_id);
        echo json_encode($result);
    } else {
        $stmt = $pdo->prepare("
            SELECT t.*, u.name AS user_name, u.email AS user_email
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = ? AND t.user_id = ?
        ");
        $stmt->execute([$transaction_id, $user_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            echo json_encode(['success' => true, 'data' => $transaction]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
