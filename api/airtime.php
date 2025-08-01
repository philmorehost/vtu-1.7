<?php
/**
 * Airtime API Endpoint - Dynamic routing with API Gateway
 */
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/ApiGateway.php');
require_once('../includes/AdminControls.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$apiGateway = new ApiGateway($pdo);

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
            $response = file_get_contents("http://localhost/api/services.php?action=detect_network&phone=" . urlencode($phoneNumber));
            $detectionResult = json_decode($response, true);
            $networkId = null;
            
            if ($detectionResult && $detectionResult['success']) {
                $networkId = $detectionResult['network']['id'];
            }
        } else {
            // Get network ID from network code
            $stmt = $pdo->prepare("SELECT id FROM networks WHERE name = ? OR code = ?");
            $stmt->execute([$network, $network]);
            $networkData = $stmt->fetch();
            $networkId = $networkData ? $networkData['id'] : null;
        }

        // Get airtime service product for the network (or general airtime)
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
        $balanceBefore = $apiGateway->getUserBalance($userId);
        if ($balanceBefore < $cost) {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
            $pdo->rollBack();
            exit();
        }

        // Get API route for airtime service
        $route = $apiGateway->getRoute('airtime', $networkId);
        if (!$route) {
            echo json_encode(['success' => false, 'message' => 'Airtime service temporarily unavailable. No API provider configured.']);
            $pdo->rollBack();
            exit();
        }

        // Deduct amount from wallet
        $balanceAfter = $balanceBefore - $cost;
        if (!$apiGateway->updateWalletBalance($userId, -$cost)) {
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
            'network_id' => $networkId,
            'discount_percentage' => $discountPercentage
        ];

        $transactionId = $apiGateway->recordTransaction(
            $userId, 'Airtime', $description, -$cost, 'Pending',
            $serviceDetails, $source, $balanceBefore, $balanceAfter, $batchId
        );

        if (!$transactionId) {
            echo json_encode(['success' => false, 'message' => 'Failed to record transaction.']);
            $pdo->rollBack();
            exit();
        }

        // Prepare API request data
        $requestData = [
            'phoneNumber' => $phoneNumber,
            'amount' => $amount,
            'network' => $network,
            'transaction_id' => $transactionId
        ];

        // Make API request
        $apiResponse = $apiGateway->makeApiRequest($route, $requestData);

        // Update transaction based on API response
        $finalStatus = 'Failed';
        $responseMessage = 'Transaction failed';

        if ($apiResponse['success']) {
            $parsedResponse = $apiResponse['parsed_response'];
            
            if (isset($parsedResponse['success']) && $parsedResponse['success']) {
                $finalStatus = 'Completed';
                $responseMessage = $parsedResponse['message'] ?? "Successfully sent ₦{$amount} airtime to {$phoneNumber}";
            } elseif (isset($parsedResponse['status']) && strtolower($parsedResponse['status']) === 'success') {
                $finalStatus = 'Completed';
                $responseMessage = $parsedResponse['message'] ?? "Successfully sent ₦{$amount} airtime to {$phoneNumber}";
            } else {
                $responseMessage = $parsedResponse['message'] ?? 'Transaction failed at provider';
            }
        } else {
            $responseMessage = 'API connection failed: ' . ($apiResponse['message'] ?? 'Unknown error');
        }

        // Update transaction status
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$finalStatus, $transactionId]);

        // If transaction failed, refund the amount
        if ($finalStatus === 'Failed') {
            $apiGateway->updateWalletBalance($userId, $cost);
            
            // Update balance in transaction record
            $stmt = $pdo->prepare("UPDATE transactions SET balance_after = balance_before WHERE id = ?");
            $stmt->execute([$transactionId]);
        }

        $pdo->commit();

        // Log API response for debugging
        error_log("Airtime API Response for Transaction $transactionId: " . json_encode($apiResponse));

        echo json_encode([
            'success' => ($finalStatus === 'Completed'),
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus,
            'discount_applied' => $discountPercentage > 0 ? "{$discountPercentage}%" : null
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Airtime API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle requery requests
    if (isset($_GET['requery']) && isset($_GET['transaction_id'])) {
        $transactionId = (int)$_GET['transaction_id'];
        $result = $apiGateway->requeryTransaction($transactionId);
        echo json_encode($result);
        exit();
    }

    // Return available networks for airtime
    try {
        $stmt = $pdo->query("
            SELECT sp.*, n.display_name as network_name, n.name as network_code 
            FROM service_products sp 
            LEFT JOIN networks n ON sp.network_id = n.id 
            WHERE sp.service_type = 'airtime' AND sp.status = 'active' 
            ORDER BY n.name ASC
        ");
        $airtimeServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by network
        $groupedServices = [];
        foreach ($airtimeServices as $service) {
            $networkName = $service['network_name'] ?: 'All Networks';
            $groupedServices[$networkName][] = [
                'network_code' => $service['network_code'],
                'discount_percentage' => $service['discount_percentage'],
                'min_amount' => 50, // Default minimum
                'max_amount' => 10000 // Default maximum
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $groupedServices
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching airtime services.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
