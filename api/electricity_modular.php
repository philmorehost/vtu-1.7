<?php
/**
 * Electricity API Endpoint - Enhanced with Modular API System
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
    $meterNumber = $_POST['meterNumber'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $disco = $_POST['disco'] ?? null;
    $serviceType = $_POST['serviceType'] ?? 'prepaid';
    $source = $_POST['source'] ?? 'Website';

    if (!$meterNumber || !$amount || !$disco || !is_numeric($amount) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Meter number, valid amount, and disco are required.']);
        exit();
    }

    $amount = floatval($amount);

    // Check admin controls
    $adminControls = new AdminControls($pdo);

    // Check if meter number is blocked
    $blockCheck = $adminControls->isIdentifierBlocked('meter_number', $meterNumber);
    if ($blockCheck['blocked']) {
        echo json_encode(['success' => false, 'message' => $blockCheck['reason']]);
        exit();
    }

    // Check transaction limit for this meter number
    $limitCheck = $adminControls->checkTransactionLimit('meter_number', $meterNumber);
    if ($limitCheck['exceeded']) {
        echo json_encode([
            'success' => false,
            'message' => "Transaction limit exceeded. Maximum {$limitCheck['limit']} transactions per {$limitCheck['period']} for this meter number. Current: {$limitCheck['current']}"
        ]);
        exit();
    }

    try {
        // Check for duplicate transaction
        if ($adminControls->isDuplicateTransaction($userId, 'Electricity', $amount, $meterNumber)) {
            echo json_encode(['success' => false, 'message' => 'Duplicate transaction detected. Please wait 2 minutes before trying again.']);
            exit();
        }

        $pdo->beginTransaction();

        // Get electricity service product
        $stmt = $pdo->prepare("
            SELECT * FROM service_products
            WHERE service_type = 'electricity'
            AND plan_code = ?
            AND status = 'active'
        ");
        $stmt->execute([$disco]);
        $electricityProduct = $stmt->fetch();

        if (!$electricityProduct) {
            echo json_encode(['success' => false, 'message' => 'Electricity service not available for this disco.']);
            $pdo->rollBack();
            exit();
        }

        // Calculate cost with discount
        $discountPercentage = $electricityProduct['discount_percentage'] ?: 0;
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
        $description = "Electricity purchase of ₦{$amount} for meter {$meterNumber} ({$disco})";
        $serviceDetails = [
            'recipient' => $meterNumber,
            'amount' => $amount,
            'cost' => $cost,
            'disco' => $disco,
            'serviceType' => $serviceType,
            'discount_percentage' => $discountPercentage
        ];

        $stmt = $pdo->prepare("
            INSERT INTO transactions
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $success = $stmt->execute([
            $userId,
            $disco . ' Electricity',
            $description,
            -$cost,
            'Pending',
            json_encode($serviceDetails),
            $source,
            $balanceBefore,
            $balanceAfter
        ]);

        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Failed to record transaction.']);
            $pdo->rollBack();
            exit();
        }

        $transactionId = $pdo->lastInsertId();

        // Use modular API gateway to process electricity
        $apiResponse = $modularGateway->payElectricity($meterNumber, $amount, $disco, $serviceType);

        // Update transaction based on API response
        $responseMessage = $apiResponse['message'];
        if ($apiResponse['success']) {
            $finalStatus = 'Completed';
        } else {
            // Treat non-success as Pending to allow for requery
            $finalStatus = 'Pending';
        }

        // Update transaction status
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$finalStatus, $transactionId]);

        $pdo->commit();

        // Log API response for debugging
        error_log("Modular Electricity API Response for Transaction $transactionId: " . json_encode($apiResponse));

        echo json_encode([
            'success' => true,
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus,
            'token' => $apiResponse['data']['token'] ?? null,
            'provider' => $apiResponse['provider'] ?? 'Unknown'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Modular Electricity API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
