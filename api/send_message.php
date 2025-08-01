<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/send_email.php');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (empty($message)) {
    echo json_encode(['error' => 'Message is empty.']);
    exit();
}

try {
    file_put_contents('api.log', "------------------\n", FILE_APPEND);
    file_put_contents('api.log', "Sending message: " . print_r($data, true) . "\n", FILE_APPEND);
    if (isset($_SESSION['admin_id'])) {
        $sender_id = 1;
        $recipient_id = $data['recipient_id'];
    } else {
        $sender_id = $_SESSION['user_id'];
        $recipient_id = 1;
    }

    $stmt = $pdo->prepare("INSERT INTO chat (sender_id, recipient_id, message) VALUES (?, ?, ?)");
    file_put_contents('api.log', "Executing query: " . $stmt->queryString . "\n", FILE_APPEND);
    $stmt->execute([$sender_id, $recipient_id, $message]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to send message.']);
}
?>
