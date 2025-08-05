<?php
/**
 * Airtime API Endpoint - Enhanced with Modular API System
 */
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/helpers.php');
require_once('../includes/ModularApiGateway.php');
require_once('../includes/AdminControls.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$modularGateway = new ModularApiGateway($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneNumber = $_POST['phoneNumber'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $network = $_POST['network'] ?? null;
    $source = $_POST['source'] ?? 'Website';
    $batchId = ($source === 'API') ? uniqid('batch_') : null;

    if (!$phoneNumber || !$amount || !is_numeric($amount) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Phone number and valid amount are required.']);
        exit();
    }

    $amount = floatval($amount);

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
        $pdo->beginTransaction();

        // Detect network if not provided
        if (!$network) {
            $networkData = detectNetworkByPhone($phoneNumber, $pdo);
            if ($networkData) {
                $network = $networkData['name'];
            }
        }

        // Get airtime service product for the network (or general airtime)
        $networkId = null;
        if ($network) {
            $stmt = $pdo->prepare("SELECT id FROM networks WHERE name = ? OR code = ?");
            $stmt->execute([strtoupper($network), strtoupper($network)]);
            $networkData = $stmt->fetch();
            $networkId = $networkData ? $networkData['id'] : null;
        }

        $stmt = $pdo->prepare("
            SELECT * FROM service_products 
            WHERE service_type = 'airtime' 
            AND (network_id = ? OR network_id IS NULL) 
            AND status = 'active' 
            ORDER BY network_id DESC
            LIMIT 1
        ");
        $stmt->execute([$networkId]);
        $airtimeProduct = $stmt->fetch();

        if (!$airtimeProduct) {
            echo json_encode(['success' => false, 'message' => 'Airtime service not available for this network.']);
            $pdo->rollBack();
            exit();
        }

        // Calculate cost with discount
        $discountPercentage = $airtimeProduct['discount_percentage'] ?: 0;
        $cost = $amount * (1 - ($discountPercentage / 100));

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
        $description = "Airtime purchase of ₦{$amount} for {$phoneNumber}";
        $serviceDetails = [
            'phoneNumber' => $phoneNumber,
            'amount' => $amount,
            'cost' => $cost,
            'network' => $network,
            'discount_percentage' => $discountPercentage
        ];

        $stmt = $pdo->prepare("
            INSERT INTO transactions
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, batch_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $success = $stmt->execute([
            $userId,
            'Airtime',
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

        // Use modular API gateway to process airtime
        $apiResponse = $modularGateway->purchaseAirtime($phoneNumber, $amount, $network);

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
        error_log("Modular Airtime API Response for Transaction $transactionId: " . json_encode($apiResponse));

        echo json_encode([
            'success' => ($finalStatus === 'Completed'),
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus,
            'provider' => $apiResponse['provider'] ?? 'Unknown',
            'discount_applied' => $discountPercentage > 0 ? "{$discountPercentage}%" : null
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Modular Airtime API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>