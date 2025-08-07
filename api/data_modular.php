<?php
/**
 * Data API Endpoint - Enhanced with Modular API System
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
    $phoneNumber = $_POST['phoneNumber'] ?? null;
    $productId = $_POST['product_id'] ?? null;
    $source = $_POST['source'] ?? 'Website';
    $batchId = ($source === 'API') ? uniqid('batch_') : null;

    if (!$phoneNumber || !$productId) {
        echo json_encode(['success' => false, 'message' => 'Phone number and product are required.']);
        exit();
    }

    // Check admin controls
    $adminControls = new AdminControls($pdo);
    
    // Check if phone number is blocked
    $blockCheck = $adminControls->isIdentifierBlocked('phone', $phoneNumber);
    if ($blockCheck['blocked']) {
        echo json_encode(['success' => false, 'message' => $blockCheck['reason']]);
        exit();
    }
    
    // Check transaction limit for this phone number
    $limitCheck = $adminControls->checkTransactionLimit('phone', $phoneNumber);
    if ($limitCheck['exceeded']) {
        echo json_encode([
            'success' => false, 
            'message' => "Transaction limit exceeded. Maximum {$limitCheck['limit']} transactions per {$limitCheck['period']} for this phone number. Current: {$limitCheck['current']}"
        ]);
        exit();
    }

    try {
        // Get data plan details first to get the cost
        $stmt = $pdo->prepare("SELECT selling_price FROM service_products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
            exit();
        }
        $cost = $product['selling_price'];

        // Check for duplicate transaction
        if ($adminControls->isDuplicateTransaction($userId, 'Data', $cost, $phoneNumber)) {
            echo json_encode(['success' => false, 'message' => 'Duplicate transaction detected. Please wait 2 minutes before trying again.']);
            exit();
        }

        $pdo->beginTransaction();

        // Get full data plan details
        $stmt = $pdo->prepare("
            SELECT sp.*, n.name as network_name, n.code as network_code 
            FROM service_products sp 
            LEFT JOIN networks n ON sp.network_id = n.id 
            WHERE sp.id = ? AND sp.service_type = 'data' AND sp.status = 'active'
        ");
        $stmt->execute([$productId]);
        $dataPlan = $stmt->fetch();

        if (!$dataPlan) {
            echo json_encode(['success' => false, 'message' => 'Data plan not found or inactive.']);
            $pdo->rollBack();
            exit();
        }

        $cost = $dataPlan['selling_price'];
        $network = $network ?: $dataPlan['network_name'];

        // Check user balance
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $balanceBefore = $user['wallet_balance'];
        
        if ($balanceBefore < $cost) {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
            $pdo->rollBack();
            exit();
        }

        // Deduct amount from wallet
        $balanceAfter = $balanceBefore - $cost;
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $success = $stmt->execute([$cost, $userId]);
        
        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Failed to update wallet balance.']);
            $pdo->rollBack();
            exit();
        }

        // Record transaction as pending
        $description = "Data purchase: {$dataPlan['name']} ({$dataPlan['data_size']}) for {$phoneNumber}";
        $serviceDetails = [
            'recipient' => $phoneNumber,
            'plan_id' => $productId,
            'plan_name' => $dataPlan['name'],
            'data_size' => $dataPlan['data_size'],
            'validity' => $dataPlan['validity'],
            'cost' => $cost,
            'network' => $network
        ];

        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, batch_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $userId, 
            $dataPlan['network_name'] . ' Data',
            $description, 
            -$cost, 
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

        // Use modular API gateway to process data purchase
        $apiResponse = $modularGateway->purchaseData($phoneNumber, $dataPlan['plan_code'], $dataPlan['network_name']);

        // Update transaction based on API response
        $finalStatus = $apiResponse['success'] ? 'Completed' : 'Failed';
        $responseMessage = $apiResponse['message'];

        // Update transaction status
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$finalStatus, $transactionId]);

        // If transaction failed, refund the amount
        if ($finalStatus === 'Failed') {
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([$cost, $userId]);
            
            // Update balance in transaction record
            $stmt = $pdo->prepare("UPDATE transactions SET balance_after = balance_before WHERE id = ?");
            $stmt->execute([$transactionId]);
        }

        $pdo->commit();

        // Log API response for debugging
        error_log("Modular Data API Response for Transaction $transactionId: " . json_encode($apiResponse));

        echo json_encode([
            'success' => ($finalStatus === 'Completed'),
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus,
            'provider' => $apiResponse['provider'] ?? 'Unknown',
            'plan_details' => [
                'name' => $dataPlan['name'],
                'size' => $dataPlan['data_size'],
                'validity' => $dataPlan['validity']
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Modular Data API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>