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
    $phoneNumbers = isset($_POST['phoneNumbers']) ? explode(',', $_POST['phoneNumbers']) : [$_POST['phoneNumber'] ?? null];
    $amount = $_POST['amount'] ?? null;
    $network = $_POST['network'] ?? null;
    $source = $_POST['source'] ?? 'Website';
    $batchId = $_POST['batch_id'] ?? uniqid('batch_');

    if (empty($phoneNumbers) || !$amount || !is_numeric($amount) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Phone number(s) and valid amount are required.']);
        exit();
    }

    $amount = floatval($amount);

    $adminControls = new AdminControls($pdo);
    $allResponses = [];
    $totalCost = 0;

    foreach ($phoneNumbers as $phoneNumber) {
        $phoneNumber = trim($phoneNumber);
        if (empty($phoneNumber)) continue;

        // Calculate cost for this single transaction
        $networkData = detectNetworkByPhone($phoneNumber, $pdo);
        $currentNetwork = $networkData ? $networkData['name'] : $network;
        $networkId = $networkData ? $networkData['id'] : null;

        $stmt = $pdo->prepare("SELECT discount_percentage FROM service_products WHERE service_type = 'airtime' AND (network_id = ? OR network_id IS NULL) AND status = 'active' ORDER BY network_id DESC LIMIT 1");
        $stmt->execute([$networkId]);
        $airtimeProduct = $stmt->fetch();
        $discountPercentage = $airtimeProduct['discount_percentage'] ?? 0;
        $cost = $amount * (1 - ($discountPercentage / 100));
        $totalCost += $cost;
    }

    try {
        $pdo->beginTransaction();

        // Check total balance first
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $balanceBefore = $user['wallet_balance'];

        if ($balanceBefore < $totalCost) {
            throw new Exception('Insufficient balance for the entire batch.');
        }

        foreach ($phoneNumbers as $phoneNumber) {
            $phoneNumber = trim($phoneNumber);
            if (empty($phoneNumber)) continue;

            $response = processSingleAirtime($pdo, $userId, $phoneNumber, $amount, $network, $source, $batchId, $adminControls, $modularGateway);
            $allResponses[] = $response;
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Batch airtime request processed.',
            'responses' => $allResponses
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Modular Airtime API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

function processSingleAirtime($pdo, $userId, $phoneNumber, $amount, $network, $source, $batchId, $adminControls, $modularGateway) {
    // Check admin controls
    $blockCheck = $adminControls->isIdentifierBlocked('phone', $phoneNumber);
    if ($blockCheck['blocked']) {
        return ['success' => false, 'message' => $blockCheck['reason'], 'phoneNumber' => $phoneNumber];
    }

    $limitCheck = $adminControls->checkTransactionLimit('phone', $phoneNumber);
    if ($limitCheck['exceeded']) {
        return ['success' => false, 'message' => "Transaction limit exceeded.", 'phoneNumber' => $phoneNumber];
    }

    if ($adminControls->isDuplicateTransaction($userId, 'Airtime', $amount, $phoneNumber)) {
        return ['success' => false, 'message' => 'Duplicate transaction detected.', 'phoneNumber' => $phoneNumber];
    }

    // Deduct cost from balance for this transaction
    $networkData = detectNetworkByPhone($phoneNumber, $pdo);
    $currentNetwork = $networkData ? $networkData['name'] : $network;
    if (!$currentNetwork) {
        $currentNetwork = 'MTN'; // Default if not detected
    }
    $networkId = $gateway->getNetworkId($currentNetwork);

    $stmt = $pdo->prepare("SELECT discount_percentage FROM service_products WHERE service_type = 'airtime' AND (network_id = ? OR network_id IS NULL) AND status = 'active' ORDER BY network_id DESC LIMIT 1");
    $stmt->execute([$networkId]);
    $airtimeProduct = $stmt->fetch();
    $discountPercentage = $airtimeProduct['discount_percentage'] ?? 0;
    $cost = $amount * (1 - ($discountPercentage / 100));

    $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
    $stmt->execute([$cost, $userId]);

    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $balanceAfter = $stmt->fetchColumn();
    $balanceBefore = $balanceAfter + $cost;

    // Record transaction
    $description = "Airtime purchase of ₦{$amount} for {$phoneNumber}";
    $serviceDetails = [
        'recipient' => $phoneNumber,
        'amount' => $amount,
        'cost' => $cost,
        'network' => $currentNetwork,
        'discount_percentage' => $discountPercentage
    ];

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, batch_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, 'Airtime', $description, -$cost, 'Pending', json_encode($serviceDetails), $source, $balanceBefore, $balanceAfter, $batchId]);
    $transactionId = $pdo->lastInsertId();

    // Process with gateway
    $apiResponse = $modularGateway->purchaseAirtime($phoneNumber, $amount, $currentNetwork);

    $finalStatus = $apiResponse['success'] ? 'Completed' : 'Pending';
    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $stmt->execute([$finalStatus, $transactionId]);

    if (!$apiResponse['success']) {
        // Do not refund here; cron job will handle it
    }

    return [
        'success' => $apiResponse['success'],
        'message' => $apiResponse['message'],
        'transaction_id' => $transactionId,
        'status' => $finalStatus,
        'phoneNumber' => $phoneNumber
    ];

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
