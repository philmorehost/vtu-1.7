<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

if (isset($_SESSION['admin_id'])) {
    $user_id = $_GET['user_id'];
    $current_user_id = 1; // Admin is always user ID 1 for chat prefix logic
} else {
    $user_id = $_SESSION['user_id'];
    $current_user_id = $_SESSION['user_id'];
}

try {
    file_put_contents('api.log', "------------------\n", FILE_APPEND);
    file_put_contents('api.log', "Getting messages for user: $user_id\n", FILE_APPEND);
    $stmt = $pdo->prepare("SELECT * FROM chat WHERE (sender_id = ? AND recipient_id = 1) OR (sender_id = 1 AND recipient_id = ?) ORDER BY created_at ASC");
    file_put_contents('api.log', "Executing query: " . $stmt->queryString . "\n", FILE_APPEND);
    $stmt->execute([$user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['messages' => $messages, 'user_id' => $current_user_id]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to retrieve messages.']);
}
?>
