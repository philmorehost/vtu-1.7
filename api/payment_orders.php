<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

$user_id = $_SESSION['user_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($admin_id) {
        // Admin fetches all payment orders
        $stmt = $pdo->query("SELECT po.*, u.username FROM payment_orders po JOIN users u ON po.user_id = u.id ORDER BY po.created_at DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $orders]);
    } elseif ($user_id) {
        // User fetches their own payment orders
        $stmt = $pdo->prepare("SELECT * FROM payment_orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $orders]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This part is for creating a new payment order, submitted by the user.
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit();
    }

    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_proof = filter_input(INPUT_POST, 'payment_proof', FILTER_SANITIZE_STRING);

    if (!$amount || !$payment_proof) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payment_orders (user_id, amount, payment_proof, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $amount, $payment_proof]);
        $order_id = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'message' => 'Payment notification submitted successfully.', 'order_id' => $order_id]);
    } catch (PDOException $e) {
        error_log("Payment order creation failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your notification.']);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
