<?php
/**
 * Data API Endpoint - Dynamic routing with API Gateway
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
    $planId = $_POST['plan'] ?? null;
    $source = $_POST['source'] ?? 'Website';
    $batchId = ($source === 'API') ? uniqid('batch_') : null;

    if (!$phoneNumber || !$planId) {
        echo json_encode(['success' => false, 'message' => 'Phone number and plan are required.']);
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
        $pdo->beginTransaction();

        // Get service product details
        $product = $apiGateway->getServiceProductByCode($planId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Invalid data plan.']);
            $pdo->rollBack();
            exit();
        }

        // Check user balance
        $balanceBefore = $apiGateway->getUserBalance($userId);
        if ($balanceBefore < $product['selling_price']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
            $pdo->rollBack();
            exit();
        }

        // Get API route for this service and network
        $route = $apiGateway->getRoute('data', $product['network_id']);
        if (!$route) {
            echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable. No API provider configured.']);
            $pdo->rollBack();
            exit();
        }

        // Deduct amount from wallet
        $balanceAfter = $balanceBefore - $product['selling_price'];
        if (!$apiGateway->updateWalletBalance($userId, -$product['selling_price'])) {
            echo json_encode(['success' => false, 'message' => 'Failed to update wallet balance.']);
            $pdo->rollBack();
            exit();
        }

        // Record transaction as pending initially
        $description = "{$product['name']} for {$phoneNumber}";
        $serviceDetails = [
            'phoneNumber' => $phoneNumber,
            'plan' => $planId,
            'product_id' => $product['id'],
            'network_id' => $product['network_id'],
            'network_name' => $product['network_name'],
            'data_size' => $product['data_size'],
            'validity' => $product['validity']
        ];

        $transactionId = $apiGateway->recordTransaction(
            $userId, 'Data', $description, -$product['selling_price'], 'Pending',
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
            'planId' => $product['plan_code'],
            'amount' => $product['amount'],
            'network' => $product['network_code'],
            'transaction_id' => $transactionId
        ];

        // Make API request
        $apiResponse = $apiGateway->makeApiRequest($route, $requestData);

        // Update transaction based on API response
        $finalStatus = 'Failed';
        $responseMessage = 'Transaction failed';

        if ($apiResponse['success']) {
            $parsedResponse = $apiResponse['parsed_response'];
            
            // Check if the API returned success
            if (isset($parsedResponse['success']) && $parsedResponse['success']) {
                $finalStatus = 'Completed';
                $responseMessage = $parsedResponse['message'] ?? "Successfully purchased {$product['name']} for {$phoneNumber}";
            } elseif (isset($parsedResponse['status']) && strtolower($parsedResponse['status']) === 'success') {
                $finalStatus = 'Completed';
                $responseMessage = $parsedResponse['message'] ?? "Successfully purchased {$product['name']} for {$phoneNumber}";
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
            $apiGateway->updateWalletBalance($userId, $product['selling_price']);
            
            // Update balance in transaction record
            $stmt = $pdo->prepare("UPDATE transactions SET balance_after = balance_before WHERE id = ?");
            $stmt->execute([$transactionId]);
        }

        $pdo->commit();

        // Log API response for debugging
        error_log("Data API Response for Transaction $transactionId: " . json_encode($apiResponse));

        echo json_encode([
            'success' => ($finalStatus === 'Completed'),
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Data API Error: " . $e->getMessage());
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

    // Return available data plans
    try {
        $stmt = $pdo->query("
            SELECT sp.*, n.display_name as network_name, n.name as network_code 
            FROM service_products sp 
            LEFT JOIN networks n ON sp.network_id = n.id 
            WHERE sp.service_type = 'data' AND sp.status = 'active' 
            ORDER BY n.name, sp.selling_price ASC
        ");
        $dataPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by network
        $groupedPlans = [];
        foreach ($dataPlans as $plan) {
            $networkName = $plan['network_name'] ?: 'All Networks';
            $groupedPlans[$networkName][] = [
                'id' => $plan['id'],
                'plan_code' => $plan['plan_code'],
                'name' => $plan['name'],
                'data_size' => $plan['data_size'],
                'validity' => $plan['validity'],
                'price' => $plan['selling_price'],
                'discount' => $plan['discount_percentage']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $groupedPlans
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching data plans.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
