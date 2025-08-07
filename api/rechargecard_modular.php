<?php
/**
 * Modular Recharge Card API Endpoint
 * Handles recharge card printing through modular providers
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
    $network = $_POST['network'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $quantity = intval($_POST['quantity'] ?? 1);
    $source = $_POST['source'] ?? 'Website';
    
    // Validate required fields
    if (!$network || !$amount || !is_numeric($amount) || $amount <= 0 || $quantity <= 0) {
        throw new Exception('All fields are required and amount/quantity must be greater than 0');
    }
    
    $amount = floatval($amount);
    $totalAmount = $amount * $quantity;
    
    // Check admin controls
    $adminControls = new AdminControls($pdo);
    
    // Get user balance
    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $balance_before = $user['wallet_balance'];
    
    if ($balance_before < $totalAmount) {
        throw new Exception('Insufficient balance.');
    }
    
    // Get the best provider for recharge card service
    $stmt = $pdo->prepare("
        SELECT ap.*, apr.priority 
        FROM api_providers ap
        JOIN api_provider_routes apr ON ap.id = apr.api_provider_id
        WHERE apr.service_type = 'recharge_card' 
        AND ap.status = 'active' 
        AND apr.status = 'active'
        AND ap.provider_module IS NOT NULL
        ORDER BY apr.priority DESC, ap.priority DESC
        LIMIT 1
    ");
    $stmt->execute();
    $providerConfig = $stmt->fetch();
    
    if (!$providerConfig) {
        throw new Exception('No active recharge card provider available');
    }
    
    // Load provider
    $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
        'api_key' => $providerConfig['api_key'],
        'secret_key' => $providerConfig['secret_key'],
        'base_url' => $providerConfig['base_url']
    ]);
    
    // Check if provider supports recharge cards
    if (!in_array('recharge_card', $provider->getSupportedServices())) {
        throw new Exception('Provider does not support recharge card service');
    }
    
    // Start database transaction
    $pdo->beginTransaction();
    
    try {
        // Process recharge card purchase through provider
        $result = $provider->purchaseRechargeCard($network, $amount, $quantity);
        
        if (!$result['success']) {
            throw new Exception($result['message']);
        }
        
        // Deduct amount from user wallet
        $balance_after = $balance_before - $totalAmount;
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $stmt->execute([$balance_after, $userId]);
        
        // Record transaction
        $description = "Purchase {$quantity}x {$network} recharge card(s) of ₦{$amount} each via {$providerConfig['display_name']}";
        $serviceDetails = json_encode([
            'network' => $network, 
            'amount' => $amount,
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'provider' => $providerConfig['display_name'],
            'transaction_id' => $result['transaction_id'],
            'cards' => $result['data']['cards'] ?? []
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after) 
            VALUES (?, ?, ?, ?, 'Completed', ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $network . ' Recharge Card', $description, -$totalAmount, $serviceDetails, $source, $balance_before, $balance_after]);
        
        // Log API transaction
        $logStmt = $pdo->prepare("
            INSERT INTO api_transaction_logs 
            (service_type, provider_id, success, response_message, request_data, response_data) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            'recharge_card',
            $providerConfig['id'],
            $result['success'],
            $result['message'],
            json_encode(['network' => $network, 'amount' => $amount, 'quantity' => $quantity]),
            json_encode($result)
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "{$network} recharge card(s) purchased successfully via {$providerConfig['display_name']}",
            'data' => $result['data'],
            'transaction_id' => $result['transaction_id'],
            'provider' => $providerConfig['display_name'],
            'balance_after' => $balance_after,
            'cards_purchased' => $quantity,
            'total_cost' => $totalAmount,
            'network' => $network
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