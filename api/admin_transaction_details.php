<?php
// api/admin_transaction_details.php

header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../admin/auth_check.php'); // Ensure admin is logged in

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required.']);
    exit();
}

$transactionId = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT t.*, u.name as user_name, u.email as user_email FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        // Decode the service_details JSON string
        $transaction['service_details'] = json_decode($transaction['service_details'], true);
        echo json_encode(['success' => true, 'data' => $transaction]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found.']);
    }
} catch (Exception $e) {
    error_log("Admin Transaction Details Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred.']);
}
