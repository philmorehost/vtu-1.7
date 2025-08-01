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
    $meterNumber = $_POST['meterNumber'] ?? null;
    $disco = $_POST['disco'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $serviceType = $_POST['serviceType'] ?? null;
    $source = $_POST['source'] ?? 'Website';

    if ($meterNumber && $disco && $amount && is_numeric($amount) && $amount > 0) {
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
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $balance_before = $user['wallet_balance'];

            if ($balance_before < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
                $pdo->rollBack();
                exit();
            }

            $balance_after = $balance_before - $amount;
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$balance_after, $userId]);

            $description = "{$disco} ({$serviceType}) payment of N{$amount} for {$meterNumber}";
            $token = ($serviceType === 'prepaid') ? '1234-5678-9012-3456-7890' : null;
            $serviceDetails = json_encode(['meterNumber' => $meterNumber, 'disco' => $disco, 'amount' => $amount, 'token' => $token]);
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, service_details, source, balance_before, balance_after) VALUES (?, 'Electricity', ?, ?, 'Completed', ?, ?, ?, ?)");
            $stmt->execute([$userId, $description, -$amount, $serviceDetails, $source, $balance_before, $balance_after]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Electricity payment successful.',
                'token' => $token
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
