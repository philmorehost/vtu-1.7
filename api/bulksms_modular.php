<?php
/**
 * Bulk SMS API Endpoint - Enhanced with Modular API System
 */
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/ModularApiGateway.php');
require_once('../includes/AdminControls.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$modularGateway = new ModularApiGateway($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? null;
    $recipients = $_POST['recipients'] ?? null;
    $senderId = $_POST['sender_id'] ?? 'VTU Platform';
    $source = $_POST['source'] ?? 'Website';
    $batchId = ($source === 'API') ? uniqid('batch_') : null;

    if (!$message || !$recipients) {
        echo json_encode(['success' => false, 'message' => 'Message and recipients are required.']);
        exit();
    }

    // Parse recipients
    $recipientList = [];
    if (is_string($recipients)) {
        $recipientList = array_map('trim', explode(',', $recipients));
    } elseif (is_array($recipients)) {
        $recipientList = $recipients;
    }

    $recipientCount = count($recipientList);
    if ($recipientCount == 0) {
        echo json_encode(['success' => false, 'message' => 'No valid recipients provided.']);
        exit();
    }

    // Check admin controls
    $adminControls = new AdminControls($pdo);
    
    // Check if any recipient numbers are blocked
    foreach ($recipientList as $recipient) {
        $blockCheck = $adminControls->isIdentifierBlocked('phone', $recipient);
        if ($blockCheck['blocked']) {
            echo json_encode(['success' => false, 'message' => "Recipient $recipient is blocked: " . $blockCheck['reason']]);
            exit();
        }
    }

    try {
        $pdo->beginTransaction();

        // Get bulk SMS service pricing
        $stmt = $pdo->prepare("
            SELECT * FROM service_products 
            WHERE service_type = 'bulksms' 
            AND status = 'active' 
            ORDER BY selling_price ASC
            LIMIT 1
        ");
        $stmt->execute();
        $smsProduct = $stmt->fetch();

        if (!$smsProduct) {
            echo json_encode(['success' => false, 'message' => 'Bulk SMS service not available.']);
            $pdo->rollBack();
            exit();
        }

        // Calculate cost (price per SMS * number of recipients)
        $costPerSms = $smsProduct['selling_price'];
        $totalCost = $costPerSms * $recipientCount;

        // Check user balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $balanceBefore = $user['balance'];
        
        if ($balanceBefore < $totalCost) {
            echo json_encode(['success' => false, 'message' => "Insufficient balance. Required: ₦$totalCost, Available: ₦$balanceBefore"]);
            $pdo->rollBack();
            exit();
        }

        // Deduct amount from wallet
        $balanceAfter = $balanceBefore - $totalCost;
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $success = $stmt->execute([$totalCost, $userId]);
        
        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Failed to update wallet balance.']);
            $pdo->rollBack();
            exit();
        }

        // Record transaction as pending
        $description = "Bulk SMS to $recipientCount recipients";
        $serviceDetails = [
            'message' => $message,
            'recipients' => $recipientList,
            'sender_id' => $senderId,
            'cost_per_sms' => $costPerSms,
            'recipient_count' => $recipientCount,
            'total_cost' => $totalCost
        ];

        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, batch_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $userId, 
            'Bulk SMS', 
            $description, 
            -$totalCost, 
            'Pending',
            json_encode($serviceDetails), 
            $source, 
            $balanceBefore, 
            $balanceAfter, 
            $batchId
        ]);

        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Failed to record transaction.']);
            $pdo->rollBack();
            exit();
        }

        $transactionId = $pdo->lastInsertId();

        // Use modular API gateway to send bulk SMS
        $apiResponse = $modularGateway->sendBulkSms($message, $recipientList, $senderId);

        // Update transaction based on API response
        $finalStatus = $apiResponse['success'] ? 'Completed' : 'Failed';
        $responseMessage = $apiResponse['message'];

        // Update transaction status
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$finalStatus, $transactionId]);

        // If transaction failed, refund the amount
        if ($finalStatus === 'Failed') {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$totalCost, $userId]);
            
            // Update balance in transaction record
            $stmt = $pdo->prepare("UPDATE transactions SET balance_after = balance_before WHERE id = ?");
            $stmt->execute([$transactionId]);
        }

        $pdo->commit();

        // Log API response for debugging
        error_log("Modular Bulk SMS API Response for Transaction $transactionId: " . json_encode($apiResponse));

        echo json_encode([
            'success' => ($finalStatus === 'Completed'),
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus,
            'provider' => $apiResponse['provider'] ?? 'Unknown',
            'sms_details' => [
                'recipients' => $recipientCount,
                'cost_per_sms' => $costPerSms,
                'total_cost' => $totalCost,
                'sender_id' => $senderId
            ],
            'delivery_info' => $apiResponse['data'] ?? null
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Modular Bulk SMS API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>