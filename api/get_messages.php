<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

$is_admin_session = isset($_SESSION['admin_id']);
$user_id_for_query = null;

if ($is_admin_session) {
    // Admin is viewing a chat with a specific user
    if (!isset($_GET['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User ID not specified.']);
        exit();
    }
    $user_id_for_query = (int)$_GET['user_id'];
} else {
    // A regular user is viewing their own chat with the admin
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit();
    }
    $user_id_for_query = (int)$_SESSION['user_id'];
}

try {
    // The recipient_id for an admin is their admin_id, not a user_id.
    // Let's assume the admin's user_id for chat purposes is a special value, e.g., 1,
    // or we can check against the admins table. A simpler way is to check sender's admin status.

    // A message is from the admin if its sender_id is 1.
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.sender_id,
            c.recipient_id,
            c.message,
            c.created_at,
            (c.sender_id = 1) as is_admin_sender
        FROM chat c
        WHERE (c.sender_id = ? AND c.recipient_id = 1) OR (c.sender_id = 1 AND c.recipient_id = ?)
        ORDER BY c.created_at ASC
    ");

    // We assume admin user_id is 1 for simplicity in chat logic
    $stmt->execute([$user_id_for_query, $user_id_for_query]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'is_admin_session' => $is_admin_session
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve messages.']);
}
?>
