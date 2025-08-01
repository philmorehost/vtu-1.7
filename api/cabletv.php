<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/AdminControls.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider = $_POST['provider'] ?? null;
    $smartCardNumber = $_POST['smartCardNumber'] ?? null;
    $planId = $_POST['plan'] ?? null;
    $source = $_POST['source'] ?? 'Website';

    if ($provider && $smartCardNumber && $planId) {
        $plans = [
            'dstv-padi' => ['price' => 2950, 'description' => 'DSTV Padi'],
            // Add other plans here
        ];
        $plan = $plans[$planId] ?? null;

        if (!$plan) {
            echo json_encode(['success' => false, 'message' => 'Invalid plan.']);
            exit();
        }

        // Check admin controls
        $adminControls = new AdminControls($pdo);
        
        // Check if smart card number is blocked
        $blockCheck = $adminControls->isIdentifierBlocked('smartcard_number', $smartCardNumber);
        if ($blockCheck['blocked']) {
            echo json_encode(['success' => false, 'message' => $blockCheck['reason']]);
            exit();
        }
        
        // Check transaction limit for this smart card number
        $limitCheck = $adminControls->checkTransactionLimit('smartcard_number', $smartCardNumber);
        if ($limitCheck['exceeded']) {
            echo json_encode([
                'success' => false, 
                'message' => "Transaction limit exceeded. Maximum {$limitCheck['limit']} transactions per {$limitCheck['period']} for this smart card number. Current: {$limitCheck['current']}"
            ]);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $balance_before = $user['wallet_balance'];

            if ($balance_before < $plan['price']) {
                echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
                $pdo->rollBack();
                exit();
            }

            $balance_after = $balance_before - $plan['price'];
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$balance_after, $userId]);

            $description = "{$plan['description']} subscription for {$smartCardNumber}";
            $serviceDetails = json_encode(['provider' => $provider, 'smartCardNumber' => $smartCardNumber, 'plan' => $planId]);
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, service_details, source, balance_before, balance_after) VALUES (?, 'Cable TV', ?, ?, 'Completed', ?, ?, ?, ?)");
            $stmt->execute([$userId, $description, -$plan['price'], $serviceDetails, $source, $balance_before, $balance_after]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Cable TV subscription successful.'
            ]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
