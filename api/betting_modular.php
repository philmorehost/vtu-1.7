<?php
/**
 * Modular Betting API Endpoint
 * Handles betting account funding through modular providers
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
    $platform = $_POST['platform'] ?? null;
    $bettingUserId = $_POST['userId'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $source = $_POST['source'] ?? 'Website';
    
    // Validate required fields
    if (!$platform || !$bettingUserId || !$amount || !is_numeric($amount) || $amount <= 0) {
        throw new Exception('All fields are required and amount must be greater than 0');
    }
    
    $amount = floatval($amount);
    
    // Check admin controls
    $adminControls = new AdminControls($pdo);
    
    // Check if betting ID is blocked
    $blockCheck = $adminControls->isIdentifierBlocked('betting_id', $bettingUserId);
    if ($blockCheck['blocked']) {
        throw new Exception($blockCheck['reason']);
    }
    
    // Check transaction limit for this betting ID
    $limitCheck = $adminControls->checkTransactionLimit('betting_id', $bettingUserId);
    if ($limitCheck['exceeded']) {
        throw new Exception("Transaction limit exceeded. Maximum {$limitCheck['limit']} transactions per {$limitCheck['period']} for this betting ID. Current: {$limitCheck['current']}");
    }

    // Check for duplicate transaction
    if ($adminControls->isDuplicateTransaction($userId, 'Betting', $amount, $bettingUserId)) {
        throw new Exception('Duplicate transaction detected. Please wait 2 minutes before trying again.');
    }
    
    // Get user balance
    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $balance_before = $user['wallet_balance'];
    
    if ($balance_before < $amount) {
        throw new Exception('Insufficient balance.');
    }
    
    // Get the best provider for betting service
    $stmt = $pdo->prepare("
        SELECT ap.*, apr.priority 
        FROM api_providers ap
        JOIN api_provider_routes apr ON ap.id = apr.api_provider_id
        WHERE apr.service_type = 'betting' 
        AND ap.status = 'active' 
        AND apr.status = 'active'
        AND ap.provider_module IS NOT NULL
        ORDER BY apr.priority DESC, ap.priority DESC
        LIMIT 1
    ");
    $stmt->execute();
    $providerConfig = $stmt->fetch();
    
    if (!$providerConfig) {
        throw new Exception('No active betting provider available');
    }
    
    // Load provider
    $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
        'api_key' => $providerConfig['api_key'],
        'secret_key' => $providerConfig['secret_key'],
        'base_url' => $providerConfig['base_url']
    ]);
    
    // Check if provider supports betting
    if (!in_array('betting', $provider->getSupportedServices())) {
        throw new Exception('Provider does not support betting service');
    }
    
    // Start database transaction
    $pdo->beginTransaction();
    
    try {
        // Process betting account funding through provider
        $result = $provider->fundBetting($bettingUserId, $amount, $platform);
        
        if (!$result['success']) {
            throw new Exception($result['message']);
        }
        
        // Deduct amount from user wallet
        $balance_after = $balance_before - $amount;
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $stmt->execute([$balance_after, $userId]);
        
        // Record transaction
        $description = "Fund {$platform} wallet for {$bettingUserId} via {$providerConfig['display_name']}";
        $serviceDetails = json_encode([
            'recipient' => $bettingUserId,
            'platform' => $platform,
            'amount' => $amount,
            'provider' => $providerConfig['display_name'],
            'transaction_id' => $result['transaction_id']
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after) 
            VALUES (?, ?, ?, ?, 'Completed', ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $platform . ' Betting', $description, -$amount, $serviceDetails, $source, $balance_before, $balance_after]);
        
        // Log API transaction
        $logStmt = $pdo->prepare("
            INSERT INTO api_transaction_logs 
            (service_type, provider_id, success, response_message, request_data, response_data) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            'betting',
            $providerConfig['id'],
            $result['success'],
            $result['message'],
            json_encode(['platform' => $platform, 'bettingUserId' => $bettingUserId, 'amount' => $amount]),
            json_encode($result)
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Betting wallet funded successfully via ' . $providerConfig['display_name'],
            'data' => $result['data'],
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