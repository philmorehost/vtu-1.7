<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'fetch';

if ($action === 'fetch') {
    try {
        $stmt = $pdo->prepare("
            SELECT n.*, urn.id IS NOT NULL AS is_read
            FROM notifications n
            LEFT JOIN user_read_notifications urn ON n.id = urn.notification_id AND urn.user_id = ?
            WHERE n.user_id = ? OR n.user_id IS NULL
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $notifications]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'mark_all_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit();
    }
    $user_id = $_SESSION['user_id'];

    try {
        // Get all notification IDs
        $stmt = $pdo->prepare("SELECT id FROM notifications WHERE user_id = ? OR user_id IS NULL");
        $stmt->execute([$user_id]);
        $notification_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Mark all as read
        if (!empty($notification_ids)) {
            $sql = "INSERT INTO user_read_notifications (user_id, notification_id) VALUES ";
            $values = [];
            foreach ($notification_ids as $notification_id) {
                $values[] = "($user_id, $notification_id)";
            }
            $sql .= implode(',', $values) . " ON DUPLICATE KEY UPDATE user_id = user_id";
            $pdo->exec($sql);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
