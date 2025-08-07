<?php
/**
 * Modular Exam Card API Endpoint
 * Handles exam card purchases through modular providers
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session_config.php';
require_once '../includes/AdminControls.php';
require_once '../apis/ApiProviderRegistry.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $cardTypeId = $_POST['card_type_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    $source = $_POST['source'] ?? 'Website';

    // Validate required fields
    if (!$cardTypeId || !is_numeric($quantity) || $quantity <= 0) {
        throw new Exception('All fields are required and quantity must be greater than 0.');
    }

    $quantity = intval($quantity);

    // Get the best provider for exam card service
    $stmt = $pdo->prepare("
        SELECT ap.*, apr.priority
        FROM api_providers ap
        JOIN api_provider_routes apr ON ap.id = apr.api_provider_id
        WHERE apr.service_type = 'exam_card'
        AND ap.status = 'active'
        AND apr.status = 'active'
        AND ap.provider_module IS NOT NULL
        ORDER BY apr.priority DESC, ap.priority DESC
        LIMIT 1
    ");
    $stmt->execute();
    $providerConfig = $stmt->fetch();

    if (!$providerConfig) {
        throw new Exception('No active exam card provider available');
    }

    // Load provider
    $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
        'api_key' => $providerConfig['api_key'],
        'secret_key' => $providerConfig['secret_key'],
        'base_url' => $providerConfig['base_url']
    ]);

    // Check if provider supports exam card
    if (!in_array('exam_card', $provider->getSupportedServices())) {
        throw new Exception('Provider does not support exam card service');
    }

    // Get product details to calculate total amount
    $products = $provider->getExamCardProducts();
    $product = null;
    foreach ($products as $p) {
        if ($p['card_type_id'] == $cardTypeId) {
            $product = $p;
            break;
        }
    }

    if (!$product) {
        throw new Exception('Invalid card type ID.');
    }

    $totalAmount = floatval($product['unit_amount']) * $quantity;

    // Check admin controls
    $adminControls = new AdminControls($pdo);

    // Check if exam card type is blocked
    $blockCheck = $adminControls->isIdentifierBlocked('exam_card_type', $cardTypeId);
    if ($blockCheck['blocked']) {
        throw new Exception($blockCheck['reason']);
    }

    // Check transaction limit
    $limitCheck = $adminControls->checkTransactionLimit('user_id', $userId);
    if ($limitCheck['exceeded']) {
        throw new Exception("Transaction limit exceeded. Maximum {$limitCheck['limit']} transactions per {$limitCheck['period']}. Current: {$limitCheck['current']}");
    }

    // Check for duplicate transaction
    $recipient_identifier = "{$cardTypeId}_{$quantity}";
    if ($adminControls->isDuplicateTransaction($userId, 'Exam Card', $totalAmount, $recipient_identifier)) {
        throw new Exception('Duplicate transaction detected. Please wait 2 minutes before trying again.');
    }

    // Get user balance
    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $balance_before = $user['wallet_balance'];

    if ($balance_before < $totalAmount) {
        throw new Exception('Insufficient balance.');
    }

    // Start database transaction
    $pdo->beginTransaction();

    try {
        // Purchase exam card through provider
        $result = $provider->purchaseExamCard($cardTypeId, $quantity);

        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        // Deduct amount from user wallet
        $balance_after = $balance_before - $totalAmount;
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $stmt->execute([$balance_after, $userId]);

        // Record transaction
        $description = "Purchased {$quantity} x {$product['card_name']} via {$providerConfig['display_name']}";
        $recipient_identifier = "{$cardTypeId}_{$quantity}";
        $serviceDetails = json_encode([
            'recipient' => $recipient_identifier,
            'card_type_id' => $cardTypeId,
            'card_name' => $product['card_name'],
            'quantity' => $quantity,
            'provider' => $providerConfig['display_name'],
            'transaction_id' => $result['transaction_id'],
            'cards' => $result['cards']
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO transactions
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after)
            VALUES (?, ?, ?, ?, 'Completed', ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $product['card_name'] . ' Exam Card', $description, -$totalAmount, $serviceDetails, $source, $balance_before, $balance_after]);

        // Log API transaction
        $logStmt = $pdo->prepare("
            INSERT INTO api_transaction_logs
            (service_type, provider_id, success, response_message, request_data, response_data)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            'exam_card',
            $providerConfig['id'],
            $result['success'],
            $result['message'],
            json_encode(['card_type_id' => $cardTypeId, 'quantity' => $quantity]),
            json_encode($result)
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Exam card(s) purchased successfully via ' . $providerConfig['display_name'],
            'data' => $result['cards'],
            'transaction_id' => $result['transaction_id'],
            'provider' => $providerConfig['display_name'],
            'balance_after' => $balance_after
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
