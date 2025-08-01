<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/AdminControls.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderId = $_POST['senderId'] ?? null;
    $recipients = json_decode($_POST['recipients'] ?? '[]', true);
    $message = $_POST['message'] ?? null;
    $source = $_POST['source'] ?? 'Website';
    $batchId = ($source === 'API') ? uniqid('batch_') : null;


    if ($senderId && !empty($recipients) && $message) {
        // Check admin controls
        $adminControls = new AdminControls($pdo);
        
        // Check if sender ID is blocked
        $blockCheck = $adminControls->isIdentifierBlocked('sms_sender_id', $senderId);
        if ($blockCheck['blocked']) {
            echo json_encode(['success' => false, 'message' => $blockCheck['reason']]);
            exit();
        }
        
        // Check if sender ID is approved
        $senderIdCheck = $adminControls->isSenderIdApproved($userId, $senderId);
        if (!$senderIdCheck['approved']) {
            echo json_encode(['success' => false, 'message' => $senderIdCheck['message']]);
            exit();
        }
        
        // Check for blocked keywords in message
        $keywordCheck = $adminControls->checkBlockedKeywords($message);
        if ($keywordCheck['blocked']) {
            echo json_encode(['success' => false, 'message' => "Message contains blocked keyword: {$keywordCheck['keyword']}"]);
            exit();
        }

        $smsCostPerUnit = 5;
        $charCount = strlen($message);
        $unitsPerSms = ceil($charCount / 153);
        $totalSmsUnits = $unitsPerSms * count($recipients);
        $totalCost = $totalSmsUnits * $smsCostPerUnit;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $balance_before = $user['wallet_balance'];

            if ($balance_before < $totalCost) {
                echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
                $pdo->rollBack();
                exit();
            }

            $balance_after = $balance_before - $totalCost;
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$balance_after, $userId]);

            $description = "Bulk SMS to " . count($recipients) . " recipients";
            $serviceDetails = json_encode(['senderId' => $senderId, 'recipients_count' => count($recipients), 'message' => $message]);
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, batch_id) VALUES (?, 'Bulk SMS', ?, ?, 'Completed', ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $description, -$totalCost, $serviceDetails, $source, $balance_before, $balance_after, $batchId]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Bulk SMS sent successfully.'
            ]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
