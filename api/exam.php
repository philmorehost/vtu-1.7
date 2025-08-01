<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examType = $_POST['examType'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $source = $_POST['source'] ?? 'Website';

    if ($examType && $quantity && is_numeric($quantity) && $quantity > 0) {
        $quantity = intval($quantity);
        $prices = ['waec' => 5000, 'neco' => 4500, 'jamb' => 3000];
        $price = $prices[$examType] ?? 0;
        $totalCost = $price * $quantity;

        if ($totalCost == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid exam type.']);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $balance_before = $user['wallet_balance'];

            if ($balance_before < $totalCost) {
                echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
                $pdo->rollBack();
                exit();
            }

            $balance_after = $balance_before - $totalCost;
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$balance_after, $userId]);

            $description = "Purchase of {$quantity} {$examType} pin(s)";
            $serviceDetails = json_encode(['examType' => $examType, 'quantity' => $quantity, 'totalCost' => $totalCost]);
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, service_details, source, balance_before, balance_after) VALUES (?, 'Exam Pin', ?, ?, 'Completed', ?, ?, ?, ?)");
            $stmt->execute([$userId, $description, -$totalCost, $serviceDetails, $source, $balance_before, $balance_after]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Exam pin purchased successfully.'
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
