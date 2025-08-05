<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/ModularApiGateway.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$modularGateway = new ModularApiGateway($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if ($action === 'get_cards') {
        $response = $modularGateway->getAvailableExamCards();
        echo json_encode($response);
    } elseif ($action === 'get_account_info') {
        $response = $modularGateway->getAccountInfo();
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardTypeId = $_POST['card_type_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $source = $_POST['source'] ?? 'Website';

    if (!$cardTypeId || !$quantity || !is_numeric($quantity) || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Card type ID and quantity are required.']);
        exit();
    }

    try {
        // Get card details to find the price
        $cardsResponse = $modularGateway->getAvailableExamCards();
        if (!$cardsResponse['success']) {
            echo json_encode(['success' => false, 'message' => 'Could not retrieve exam card list.']);
            exit();
        }

        $card = null;
        foreach ($cardsResponse['data'] as $c) {
            if ($c['card_type_id'] == $cardTypeId) {
                $card = $c;
                break;
            }
        }

        if (!$card) {
            echo json_encode(['success' => false, 'message' => 'Invalid card type ID.']);
            exit();
        }

        $cost = $card['unit_amount'] * $quantity;

        $pdo->beginTransaction();

        // Check user balance
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
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
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $stmt->execute([$balanceAfter, $userId]);

        // Record transaction as pending
        $description = "Exam card purchase: {$quantity} x {$card['card_name']}";
        $serviceDetails = [
            'card_type_id' => $cardTypeId,
            'quantity' => $quantity,
            'card_name' => $card['card_name'],
            'cost' => $cost,
        ];

        $stmt = $pdo->prepare("
            INSERT INTO transactions
            (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, created_at)
            VALUES (?, 'Exam', ?, ?, 'Pending', ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $description, -$cost, json_encode($serviceDetails), $source, $balanceBefore, $balanceAfter]);
        $transactionId = $pdo->lastInsertId();

        // Use modular API gateway to process purchase
        $apiResponse = $modularGateway->purchaseExamCard($cardTypeId, $quantity);

        // Update transaction based on API response
        $finalStatus = $apiResponse['success'] ? 'Completed' : 'Failed';
        $responseMessage = $apiResponse['message'];

        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$finalStatus, $transactionId]);

        if ($finalStatus === 'Failed') {
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$balanceBefore, $userId]);

            $stmt = $pdo->prepare("UPDATE transactions SET balance_after = ? WHERE id = ?");
            $stmt->execute([$balanceBefore, $transactionId]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => ($finalStatus === 'Completed'),
            'message' => $responseMessage,
            'transaction_id' => $transactionId,
            'status' => $finalStatus,
            'provider' => $apiResponse['provider'] ?? 'Unknown',
            'details' => $apiResponse['data'] ?? null
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Exam Card Purchase Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
