<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'register':
            $senderId = trim($_POST['sender_id'] ?? '');
            $sampleMessage = trim($_POST['sample_message'] ?? '');
            
            if (!$senderId || !$sampleMessage) {
                echo json_encode(['success' => false, 'message' => 'Sender ID and sample message are required.']);
                exit();
            }
            
            if (strlen($senderId) > 11) {
                echo json_encode(['success' => false, 'message' => 'Sender ID cannot be longer than 11 characters.']);
                exit();
            }
            
            if (strlen($sampleMessage) < 10) {
                echo json_encode(['success' => false, 'message' => 'Sample message must be at least 10 characters long.']);
                exit();
            }
            
            try {
                // Check if user already has a pending/approved sender ID with the same name
                $stmt = $pdo->prepare("SELECT id, status FROM sms_sender_ids WHERE user_id = ? AND sender_id = ? AND status IN ('pending', 'approved')");
                $stmt->execute([$userId, $senderId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $statusText = $existing['status'] === 'pending' ? 'pending approval' : 'already approved';
                    echo json_encode(['success' => false, 'message' => "This sender ID is $statusText."]);
                    exit();
                }
                
                // Register new sender ID
                $stmt = $pdo->prepare("INSERT INTO sms_sender_ids (user_id, sender_id, sample_message) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $senderId, $sampleMessage]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Sender ID registered successfully. It will be reviewed by an administrator.'
                ]);
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error registering sender ID.']);
            }
            break;
            
        case 'edit':
            $id = (int)($_POST['id'] ?? 0);
            $senderId = trim($_POST['sender_id'] ?? '');
            $sampleMessage = trim($_POST['sample_message'] ?? '');
            
            if (!$id || !$senderId || !$sampleMessage) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                exit();
            }
            
            try {
                // Check if the sender ID belongs to the user and is disapproved
                $stmt = $pdo->prepare("SELECT id FROM sms_sender_ids WHERE id = ? AND user_id = ? AND status = 'disapproved'");
                $stmt->execute([$id, $userId]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'You can only edit disapproved sender IDs.']);
                    exit();
                }
                
                // Update the sender ID and set status back to pending
                $stmt = $pdo->prepare("UPDATE sms_sender_ids SET sender_id = ?, sample_message = ?, status = 'pending', reviewed_by = NULL, review_notes = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$senderId, $sampleMessage, $id]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Sender ID updated and resubmitted for review.'
                ]);
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error updating sender ID.']);
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Invalid sender ID.']);
                exit();
            }
            
            try {
                // Check if the sender ID belongs to the user
                $stmt = $pdo->prepare("DELETE FROM sms_sender_ids WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $userId]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Sender ID deleted successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Sender ID not found or you do not have permission to delete it.']);
                }
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error deleting sender ID.']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get user's sender IDs
    try {
        $stmt = $pdo->prepare("SELECT * FROM sms_sender_ids WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $senderIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'sender_ids' => $senderIds
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching sender IDs.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>