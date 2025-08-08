<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardType = $_POST['cardType'] ?? null;
    $denomination = $_POST['denomination'] ?? null;
    $mode = $_POST['mode'] ?? null; // 'buy' or 'sell'
    $source = $_POST['source'] ?? 'Website';

    if ($cardType && $denomination && is_numeric($denomination) && $denomination > 0 && $mode) {
        $denomination = floatval($denomination);

        // Fetch gift card product from the database
        $stmt = $pdo->prepare("SELECT * FROM service_products WHERE service_type = 'giftcard' AND plan_code = ? AND status = 'active'");
        $stmt->execute([$cardType]);
        $giftCardProduct = $stmt->fetch();

        if (!$giftCardProduct) {
            echo json_encode(['success' => false, 'message' => 'Gift card type not available.']);
            exit();
        }

        // amount is the buy rate, selling_price is the sell rate
        $buy_rate = $giftCardProduct['amount'];
        $sell_rate = $giftCardProduct['selling_price'];

        $rate = ($mode === 'buy') ? $buy_rate : $sell_rate;
        $value = $denomination * $rate;

        if ($value == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid card type or mode.']);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $balance_before = $user['wallet_balance'];

            if ($mode === 'buy' && $balance_before < $value) {
                echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
                $pdo->rollBack();
                exit();
            }

            $balance_after = ($mode === 'buy') ? $balance_before - $value : $balance_before + $value;
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$balance_after, $userId]);

            $description = ucfirst($mode) . " {$denomination} {$cardType} gift card";
            $serviceDetails = json_encode(['cardType' => $cardType, 'denomination' => $denomination, 'mode' => $mode]);
            $amount = ($mode === 'buy') ? -$value : $value;
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, service_details, source, balance_before, balance_after) VALUES (?, 'Gift Card', ?, ?, 'Completed', ?, ?, ?, ?)");
            $stmt->execute([$userId, $description, $amount, $serviceDetails, $source, $balance_before, $balance_after]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Gift card transaction successful.'
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
